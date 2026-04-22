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
        $this->words = $raw;
    }
}