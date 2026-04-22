<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Interfaces;

use Ocolin\RouterOS\DTO\Sentence;
use Generator;

/**
 * Interface for managing authenticated sessions with RouterOS devices.
 * Implement this interface to create custom session handlers.
 */
interface SessionInterface
{
    /**
     * @param string[] $words List of words to send to device.
     * @return Generator <int, Sentence, mixed, void> Yields Sentence objects.
     */
    public function sendCommand( array $words ) : Generator;
}