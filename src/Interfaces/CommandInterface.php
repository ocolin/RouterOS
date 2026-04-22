<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Interfaces;

/**
 * Interface for building command word arrays to send to RouterOS devices.
 * Implement this interface to create custom command builders.
 */

interface CommandInterface
{

    /**
     * @return string[] List of words to submit.
     */
    public function toWords() : array;
}