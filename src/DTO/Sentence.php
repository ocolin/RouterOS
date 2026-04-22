<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\DTO;

readonly class Sentence
{
    /**
     * @var string Reply word for sentence.
     */
    public string $reply;

    /**
     * @var int|string|null Tag to mark requests with responses.
     */
    public int|string|null $tag;

    /**
     * @var string[] List of words in sentence.
     */
    public array $words;


/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param string[] $raw Original list of words.
     */
    public function __construct( array $raw ) {
        $this->reply = ltrim(
            (string)array_shift($raw ),
            characters: '!'
        );
        $tag = null;
        foreach( $raw as $key => $word ) {
            if( str_starts_with( $word, '.tag=' ) ) {
                $tag = substr( $word, 5 );
                unset( $raw[$key] );
                break;
            }
        }
        $this->tag = $tag;
        $this->words = array_values( $raw );
    }
}