<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Interfaces;

use Ocolin\RouterOS\DTO\Sentence;

/**
 * Interface for transport layer communication with RouterOS devices.
 * Implement this interface to create custom transport handlers (e.g. SSH).
 */
interface TransportInterface
{
    /**
     * @param string[] $words List of words to send.
     * @return void
     */
    public function sendSentence( array $words ) : void;

    public function readSentence() : Sentence;
}