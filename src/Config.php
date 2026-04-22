<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Ocolin\GlobalType\TypedArray;
use Ocolin\RouterOS\Exceptions\ConfigException;

readonly class Config
{
    /**
     * @var string Hostname of IP of Mikrotik device.
     */
    public string $host;

    /**
     * @var string Username to log in with.
     */
    public string $username;

    /**
     * @var string Password to log in with.
     */
    public string $password;

    /**
     * @var bool Send commands over SSL.
     */
    public bool $ssl;

    /**
     * @var int Port to make unsecure connection on.
     */
    public int $port;

    /**
     * @var int Port to make secure connection on.
     */
    public int $sslPort;

    /**
     * @var int Seconds to timeout connection attempt.
     */
    public int $timeout;

    /**
     * @var int Seconds to timeout waiting for response.
     */
    public int $socketTimeout;

    /**
     * @var bool Verify validity of SSL connection.
     */
    public bool $sslVerify;


/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param array<string, string|int|float|bool>|object $params List of parameters.
     * @param string $envPrefix Optional prefix for environment variables.
     * @throws ConfigException Missing host.
     */
    public function __construct(
        array|object $params = [],
              string $envPrefix = ''
    )
    {
        if( is_object( $params )) { $params = (array) $params; }
        TypedArray::load( data: $params );
        $prefix = $envPrefix
            ? 'ROUTEROS_' . strtoupper( $envPrefix ) . '_'
            : 'ROUTEROS_';

        $this->host = TypedArray::getStringNull( name: 'host' )
            ?? $this->getEnvString( name: $prefix . 'HOST' )
            ?? throw new ConfigException( message: 'Host is required.' );

        $this->username = TypedArray::getStringNull( name: 'username' )
            ?? $this->getEnvString( name: $prefix . 'USERNAME' )
            ?? TypedArray::getStringNull( name: 'user' )
            ?? 'admin';

        $this->password = TypedArray::getStringNull( name: 'password' )
            ?? $this->getEnvString( name: $prefix . 'PASSWORD' )
            ?? TypedArray::getStringNull( name: 'pass' )
            ?? '';

        $this->ssl = TypedArray::getBoolNull( name: 'ssl' )
            ?? $this->getEnvBool( name: $prefix . 'SSL' )
            ?? false;

        $this->port = TypedArray::getIntNull( name: 'port' )
            ?? $this->getEnvInt( name: $prefix . 'PORT' )
            ?? 8728;

        $this->sslPort = TypedArray::getIntNull( name: 'sslPort' )
            ?? $this->getEnvInt( name: $prefix . 'SSL_PORT' )
            ?? 8729;

        $this->timeout = TypedArray::getIntNull( name: 'timeout' )
            ?? $this->getEnvInt( name: $prefix . 'TIMEOUT' )
            ?? 10;

        $this->socketTimeout = TypedArray::getIntNull( name: 'socketTimeout' )
            ?? $this->getEnvInt( name: $prefix . 'SOCKET_TIMEOUT' )
            ?? 30;

        $this->sslVerify = TypedArray::getBoolNull( name: 'sslVerify' )
            ?? $this->getEnvBool( name: $prefix . 'SSL_VERIFY' )
            ?? false;
    }



/* GET ENVIRONMENT STRING VALUE
----------------------------------------------------------------------------- */

    /**
     * Get string value from an environment value.
     *
     * @param string $name Name of string value.
     * @return ?string String or null.
     */
    private function getEnvString( string $name ) : ?string
    {
        $value = getenv( $name );
        return is_string( $value ) ? $value : null;
    }



/* GET ENVIRONMENT INTEGER VALUE
----------------------------------------------------------------------------- */

    /**
     * Get integer value of an environment variable.
     *
     * @param string $name Name of integer value.
     * @return ?int Integer or null.
     */
    private function getEnvInt( string $name ) : ?int
    {
        $value = getenv( $name );
        return is_string( $value ) ? (int)$value : null;
    }



/* GET BOOLEAN ENVIRONMENT VALUE
----------------------------------------------------------------------------- */

    /**
     * Get boolean value of and environment variable.
     *
     * @param string $name Name of boolean value.
     * @return ?bool Boolean or null.
     */
    private function getEnvBool( string $name ) : ?bool
    {
        $value = getenv( $name );
        return is_string( $value )
            ? filter_var(
                  value: $value,
                 filter: FILTER_VALIDATE_BOOLEAN,
                options: FILTER_NULL_ON_FAILURE
              )
            : null;
    }
}