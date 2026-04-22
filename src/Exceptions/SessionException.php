<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Exceptions;

class SessionException extends RouterOSException
{
    public function __construct( string $message, int $code = 0 )
    {
        parent::__construct( message: $message, code: $code );
    }
}