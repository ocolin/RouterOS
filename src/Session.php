<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Generator;
use Ocolin\RouterOS\Exceptions\ConnectionException;
use Ocolin\RouterOS\Exceptions\LoginException;
use Ocolin\RouterOS\Exceptions\SessionException;
use Ocolin\RouterOS\Interfaces\TransportInterface;
use Ocolin\RouterOS\Interfaces\SessionInterface;
use Ocolin\RouterOS\DTO\Sentence;

class Session implements SessionInterface
{
    /**
     * @var TransportInterface Transport mechanism.
     */
    private TransportInterface $transport;

    /**
     * @var bool Is user logged in to device?
     */
    private bool $isLoggedIn = false;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param Config $config Data configuration object.
     * @param ?TransportInterface $transport Transport class for mocking.
     * @throws ConnectionException Unable to connect.
     */
    public function __construct(
        private readonly Config $config,
           ?TransportInterface $transport = null,
    )
    {
       $this->transport = $transport ?? new Transport( config: $this->config );
    }



/* SEND COMMAND
----------------------------------------------------------------------------- */

    /**
     * Send command request to device.
     *
     * @param string[] $words List of words to send device.
     * @return Generator Handle response words one at a time.
     * @throws SessionException Unexpected reply.
     * @throws LoginException Unable to log in.
     */
    public function sendCommand( array $words ) : Generator
    {
        if( $this->isLoggedIn === false ) { $this->login(); }

        $this->transport->sendSentence( words: $words );
        while( true )
        {
            $sentence = $this->transport->readSentence();

            if( $sentence->reply === 'done' ) { return; }
            match( $sentence->reply ) {
                're'    => yield $sentence,
                'trap'  => self::handleTrap( $sentence ),
                default => throw new SessionException(
                    message: "Unexpected reply: {$sentence->reply}"
                )
            };
        }
    }



/* LOGIN
----------------------------------------------------------------------------- */

    /**
     * Log in to device.
     *
     * @return void
     * @throws SessionException Some kind of unexpected response.
     */
    public function login() : void
    {
        $this->transport->sendSentence([
            '/login',
            '=name=' . $this->config->username,
            '=password=' . $this->config->password,
        ]);

        $response = $this->transport->readSentence();
        try {
            match( $response->reply ) {
                'done'  => $this->handleDoneResponse( $response ),
                'trap'  => self::handleTrap($response),
                default => throw new LoginException(
                    message: "Unexpected reply: {$response->reply}"
                )
            };
        }
        // Throw a login exception so users know it's related to logging in.
        catch( SessionException $e ) {
            throw new LoginException(
                message: $e->getMessage(),
                   code: $e->getCode()
            );
        }
    }



/* HANDLE A TRAP
----------------------------------------------------------------------------- */

    /**
     * A Trap is the Mikrotik's name for an error.
     *
     * @param Sentence $sentence Sentence object to evaluate.
     * @return never We only call this when we know we have a trap, so it
     *  should never return.
     * @throws SessionException Mikrotik responded with an error.
     */
    private static function handleTrap( Sentence $sentence ) : never
    {
        $message = self::parseWord( sentence: $sentence, key: 'message' )
            ?? 'Unknown error';
        $code = (int)(self::parseWord( sentence: $sentence, key: 'category' ) ?? 0);

        throw new SessionException( message: $message,  code: $code );
    }



/* PARSE WORD
----------------------------------------------------------------------------- */

    /**
     * Parse content from a response sentence.
     *
     * @param Sentence $sentence Sentence object to parse.
     * @param string $key Key parameter to look for.
     * @return ?string Get value if it exists.
     */
    private static function parseWord( Sentence $sentence, string $key ) : ?string
    {
        foreach( $sentence->words as $word ) {
            if( str_starts_with( haystack: $word, needle: "={$key}=" ) ) {
                return substr(
                    string: $word, offset: strlen( string: "={$key}=" )
                );
            }
        }
        return null;
    }



/* IS USER LOGGED IN?
----------------------------------------------------------------------------- */

    /**
     * @return bool State of login.
     */
    public function isLoggedIn() : bool
    {
        return $this->isLoggedIn;
    }



/* HANDLE DONE RESPONSE FROM LOGIN
----------------------------------------------------------------------------- */

    /**
     * Check login response for legacy response and handle.
     *
     * @param Sentence $sentence Login response sentence.
     * @return void
     */
    private function handleDoneResponse( Sentence $sentence ) : void
    {
        $challenge = self::parseWord( $sentence, 'ret' );
        if( $challenge !== null ) {
            $this->legacyLogin( $challenge );
            return;
        }
        $this->isLoggedIn = true;
    }



/* LOGIN FOR LEGACY DEVICES
----------------------------------------------------------------------------- */

    /**
     * Handle logging in for legacy devices.
     *
     * @param string $challenge Challenge from a legacy response.
     * @return void
     */
    private function legacyLogin( string $challenge ) : void
    {
        $challengeBytes = pack( 'H*', $challenge );
        $hash = md5( string: "\x00" . $this->config->password . $challengeBytes );

        $this->transport->sendSentence([
            '/login',
            '=name=' . $this->config->username,
            '=response=00' . $hash,
        ]);

        $response = $this->transport->readSentence();
        try {
            match( $response->reply ) {
                'done'  => $this->isLoggedIn = true,
                'trap'  => self::handleTrap( $response ),
                default => throw new LoginException(
                    message: "Unexpected reply: {$response->reply}"
                )
            };
        } catch( SessionException $e ) {
            throw new LoginException(
                message: $e->getMessage(),
                code:    $e->getCode()
            );
        }
    }
}