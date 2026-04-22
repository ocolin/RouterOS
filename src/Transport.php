<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Ocolin\RouterOS\Exceptions\TransportException;
use Ocolin\RouterOS\Exceptions\ConnectionException;
use Ocolin\RouterOS\Interfaces\TransportInterface;
use Ocolin\RouterOS\DTO\Sentence;

class Transport implements TransportInterface
{
    /**
     * @var resource $socket Socket connection to device.
     */
    private mixed $socket;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param Config $config Configuration data.
     * @throws ConnectionException Failed connection.
     */
    public function __construct( readonly private Config $config )
    {
        $errCode = 0;
        $errMsg  = '';

        // SSL connection
        if( $this->config->ssl ) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => $this->config->sslVerify,
                    'verify_peer_name' => $this->config->sslVerify,
                ]
            ]);

            $socket = @stream_socket_client(
                     address: "ssl://{$this->config->host}:{$this->config->sslPort}",
               error_code: $errCode,
            error_message: $errMsg,
                     timeout: $this->config->timeout,
                     context: $context
            );
        }
        // Unsecure connection
        else {
            $socket = @fsockopen(
                        hostname: $this->config->host,
                            port: $this->config->port,
                   error_code: $errCode,
                error_message: $errMsg,
                         timeout: $this->config->timeout
            );
        }

        if( $socket === false ) {
            throw new ConnectionException(
                message: "Connection failed: {$errMsg} ({$errCode})"
            );
        }
        $this->socket = $socket;

        stream_set_timeout(
            stream: $this->socket, seconds: $this->config->socketTimeout
        );
    }



/* DESTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * Close the connection when finished.
     */
    public function __destruct()
    {
        if( is_resource( $this->socket ) ) {  fclose( $this->socket ); }
    }



/* READ SENTENCE
----------------------------------------------------------------------------- */

    /**
     * Reading back a group of works from device.
     *
     * @internal Not part of the public API.
     * @return Sentence List of words.
     * @throws TransportException Unable to read sentence.
     */
    public function readSentence() : Sentence
    {
        $output = [];

        while( !empty( $word = $this->readWord())) { $output[] = $word; }

        return new Sentence( raw: $output );
    }



/* SEND SENTENCE OF WORDS TO DEVICE
----------------------------------------------------------------------------- */

    /**
     * @internal Not part of the public API.
     * @param string[] $words List of words to send.
     * @return void
     * @throws TransportException Unable to write sentence to device.
     */
    public function sendSentence( array $words ) : void
    {
        $output = '';
        foreach( $words as $word ) { $output .= $this->writeWord( $word ); }

        $output .= $this->writeWord( '' );
        $response = fwrite( stream: $this->socket, data: $output );

        if( $response === false ) {
            throw new TransportException(
                message: "Error writing to socket."
            );
        }
    }



/* WRITE WORD
----------------------------------------------------------------------------- */

    /**
     * Format a word with its length information appended.
     * @param string $word Word to send.
     * @return string Formatter word.
     * @throws TransportException Unable to encode word.
     */
    private function writeWord( string $word ) : string
    {
        $length = mb_strlen( string: $word, encoding: '8bit' );
        $prefix = self::encodeLength( length: $length );

        return $prefix . $word;
    }



/* READ WORD
----------------------------------------------------------------------------- */

    /**
     * @return string Word from device.
     * @throws TransportException Error reading word.
     */
    private function readWord() : string
    {
        $length = $this->decodeLength();

        if( $length < 0 ) {
            throw new TransportException( message: "Invalid word length: {$length}" );
        }
        if( $length === 0 ) { return ''; }

        $word = fread( stream: $this->socket, length: $length );
        if( $word === false ) {
            throw new TransportException(  message: "Error reading word." );
        }

        return $word;
    }



/* ENCODE LENGTH
----------------------------------------------------------------------------- */

    /**
     *
     * @internal Not part of the public API.
     * @param int $length
     * @return string Encoded length value of a word.
     * @throws TransportException Unable to get length of word.
     */
    public static function encodeLength( int $length ) : string
    {
        if( $length < 0x80 ) {
            return pack( 'C', $length )
                ?: throw new TransportException( message: 'pack() failed' );
        }
        if( $length < 0x4000 ) {
            return pack( 'n', $length  | 0x8000 )
                ?: throw new TransportException( message: 'pack() failed' );
        }
        if( $length < 0x200000 ) {
            $packed = pack( 'N', $length | 0xC00000 )
                ?: throw new TransportException( message: 'pack() failed' );
            return substr( string: $packed, offset: 1 );
        }

        if( $length < 0x10000000 ) {
            return pack( 'N', $length | 0xE0000000 )
                ?: throw new TransportException( message: 'pack() failed' );
        }

        throw new TransportException( message: "Error getting word length." );
    }



/* DECODE LENGTH
----------------------------------------------------------------------------- */

    /**
     * @return int Length of word.
     * @throws TransportException Unable to determine word length.
     */
    private function decodeLength() : int
    {
        $firstByte = $this->readByte();

        if( ($firstByte & 0x80) === 0x00 ) {  return $firstByte; }

        if(( $firstByte & 0xC0 ) === 0x80 ) {
            $secondByte = $this->readByte();

            return ( $firstByte & 0x3F ) << 8 | $secondByte;
        }

        if(( $firstByte & 0xE0 ) === 0xC0 ) {
            $secondByte = $this->readByte();
            $thirdByte  = $this->readByte();

            return ($firstByte & 0x1F ) << 16 | $secondByte << 8 | $thirdByte;
        }

        if(( $firstByte & 0xF0 ) === 0xE0 ) {
            $secondByte = $this->readByte();
            $thirdByte  = $this->readByte();
            $fourthByte = $this->readByte();

            return ( $firstByte & 0x0F ) << 24
                | $secondByte << 16
                | $thirdByte << 8
                | $fourthByte;
        }

        throw new TransportException( message: "Error decoding word length." );
    }



/* READ BYTE
----------------------------------------------------------------------------- */

    /**
     * @return int Get length from byte.
     * @throws TransportException Unable to read a byte.
     */
    private function readByte() : int
    {
        $data = fread( stream: $this->socket, length: 1 );
        if( $data === false ) {
            throw new TransportException( message: 'fread() failed' );
        }
        return ord( character: $data );
    }
}