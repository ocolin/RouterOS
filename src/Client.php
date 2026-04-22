<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Generator;
use Ocolin\RouterOS\DTO\Sentence;
use Ocolin\RouterOS\Interfaces\CommandInterface;
use Ocolin\RouterOS\Interfaces\SessionInterface;
use Ocolin\RouterOS\Exceptions\ConfigException;
use Ocolin\RouterOS\Exceptions\SessionException;
use Ocolin\RouterOS\Exceptions\LoginException;

class Client
{
    /**
     * @var Config Configuration data object.
     */
    readonly private Config $config;

    /**
     * @var SessionInterface Connection session object.
     */
    private SessionInterface $session;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param array<string, string|int|float|bool>|Config $config Data
     *  Configuration object.
     * @param ?SessionInterface $session Optional session for mocking.
     * @throws ConfigException Issue with configuration settings.
     */
    public function __construct(
             array|Config $config = [],
        ?SessionInterface $session = null
    )
    {
        if( is_array( $config )) { $config = new Config( $config ); }
        $this->config  = $config;
        $this->session = $session ?? new Session( config: $this->config );
    }


/* QUERY
----------------------------------------------------------------------------- */

    /**
     * Run a query on the device.
     *
     * @param string|string[]|CommandInterface $command Either a raw
     * string command, a pre-build word array command, or a command
     * building object.
     * @param string[] $params Optional parameters such as attributes,
     * queries, etc.
     * @return array<int, array<string, string>> Output of the device.
     * Formatting as an array of arrays.
     * @throws SessionException|LoginException
     */
    public function query(
        string|array|CommandInterface $command,
        array $params = []
    ) : array
    {
        $output = [];

        $words = self::normalizeCommand( command: $command, params: $params );

        /** @var Generator<int, Sentence, mixed, void> $sentences */
        $sentences = $this->session->sendCommand( words: $words );
        foreach( $sentences as $sentence )
        {
            $output[] = self::parseSentence( sentence: $sentence );
        }

        return $output;
    }



/* STREAM
----------------------------------------------------------------------------- */

    /**
     * For queries that stream an output until canceled.
     *
     * @param string|string[]|CommandInterface $command Either a raw
     *  string command, a pre-build word array command, or a command
     *  building object.
     * @param string[] $params Optional parameters such as attributes,
     *  queries, etc.
     * @return Generator<int, array<string, string>, mixed, void>
     *     Output of the device.
     * @throws SessionException|LoginException
     */
    public function stream(
        string|array|CommandInterface $command,
        array $params = []
    ): Generator
    {
        $words = self::normalizeCommand( command: $command, params: $params );

        /** @var Sentence $sentence */
        foreach( $this->session->sendCommand( words: $words ) as $sentence ) {
            yield self::parseSentence( sentence: $sentence );
        }
    }



/* PARSE SENTENCE
----------------------------------------------------------------------------- */

    /**
     * Parse the output of the device into a readable array.
     *
     * @param Sentence $sentence Raw sentence object from device.
     * @return array<string, string> Readable array data.
     */
    private static function parseSentence( Sentence $sentence ) : array
    {
        $output = [];
        foreach( $sentence->words as $word )
        {
            if( !str_starts_with( haystack: $word, needle: '=' )) { continue; }
            $word  = ltrim( string: $word, characters: '=' );
            $parts = explode( separator: '=', string: $word, limit: 2 );
            if( count( $parts ) < 2 ) { continue; }
            $output[ $parts[0] ] = $parts[1];
        }

        return $output;
    }



/* NORMALIZE COMMAND
----------------------------------------------------------------------------- */

    /**
     * @param string|string[]|CommandInterface $command Either a raw
     *   string command, a pre-build word array command, or a command
     *   building object.
     * @param string[] $params Optional parameters such as attributes,
     *   queries, etc.
     * @return string[] Normalized command words.
     */
    private static function normalizeCommand(
        string|array|CommandInterface $command,
        array $params = []
    ) : array
    {
        return match( true ) {
            $command instanceof CommandInterface => $command->toWords(),
            is_string( $command ) => array_merge(
                ['/' . ltrim( string: $command, characters: '/' )], $params
            ),
            default => array_merge( $command, $params )
        };
    }
}