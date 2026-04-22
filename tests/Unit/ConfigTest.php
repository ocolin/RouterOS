<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Tests\Unit;

use Ocolin\RouterOS\Config;
use Ocolin\RouterOS\Exceptions\ConfigException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ConfigTest extends TestCase
{
    public function testAllFromArray() : void
    {
        self::populateEnvVars();
        $config = new Config( params: [
            'host' => 'argHost',
            'username' => 'argUsername',
            'password' => 'argPassword',
            'ssl' => true,
            'port' => 8888,
            'sslPort' => 9999,
            'timeout' => 66,
            'socketTimeout' => 55,
            'sslVerify' => false,
            'throwOnError' => true
        ]);
        $this->assertSame( 'argHost', $config->host );
        $this->assertSame( 'argUsername', $config->username );
        $this->assertSame( 'argPassword', $config->password );
        $this->assertSame( true, $config->ssl );
        $this->assertSame( 8888, $config->port );
        $this->assertSame( 9999, $config->sslPort );
        $this->assertSame( 66, $config->timeout );
        $this->assertSame( 55, $config->socketTimeout );
        $this->assertSame( false, $config->sslVerify );
        $this->assertSame( true, $config->throwOnError );
    }

    public function testAllFromEnv() : void
    {
        self::populateEnvVars();
        $config = new Config();
        $this->assertSame( 'envHost', $config->host );
        $this->assertSame( 'envUser', $config->username );
        $this->assertSame( 'envPass', $config->password );
        $this->assertSame( true, $config->ssl );
        $this->assertSame( 6666, $config->port );
        $this->assertSame( 7777, $config->sslPort );
        $this->assertSame( 99, $config->timeout );
        $this->assertSame( 88, $config->socketTimeout );
        $this->assertSame( true, $config->sslVerify );
        $this->assertSame( false, $config->throwOnError );
    }

    public function testAllDefaults() : void
    {
        $config = new Config( params: ['host' => 'localhost'] );
        $this->assertSame( 'localhost', $config->host );
        $this->assertSame( 'admin', $config->username );
        $this->assertSame( '', $config->password );
        $this->assertSame( false, $config->ssl );
        $this->assertSame( 8728, $config->port );
        $this->assertSame( 8729, $config->sslPort );
        $this->assertSame( 10, $config->timeout );
        $this->assertSame( 30, $config->socketTimeout );
        $this->assertSame( false, $config->sslVerify );
        $this->assertSame( true, $config->throwOnError );
    }

    public function testMissingHostThrows() : void
    {
        $this->expectException( ConfigException::class );
        new Config();
    }

    public function testPrefixedEnv() : void
    {
        putenv('ROUTEROS_PREFIX_HOST=prefixHost');
        putenv('ROUTEROS_PREFIX_USERNAME=prefixUser');
        putenv('ROUTEROS_PREFIX_PASSWORD=prefixPass');
        putenv('ROUTEROS_PREFIX_SSL=true');
        putenv('ROUTEROS_PREFIX_PORT=2222');
        putenv('ROUTEROS_PREFIX_SSL_PORT=3333');
        putenv('ROUTEROS_PREFIX_TIMEOUT=22');
        putenv('ROUTEROS_PREFIX_SOCKET_TIMEOUT=33');
        putenv('ROUTEROS_PREFIX_SSL_VERIFY=true');
        putenv('ROUTEROS_PREFIX_THROW_ON_ERROR=false');

        $config = new Config( envPrefix: 'PREFIX' );
        $this->assertSame( 'prefixHost', $config->host );
        $this->assertSame( 'prefixUser', $config->username );
        $this->assertSame( 'prefixPass', $config->password );
        $this->assertSame( true, $config->ssl );
        $this->assertSame( 2222, $config->port );
        $this->assertSame( 3333, $config->sslPort );
        $this->assertSame( 22, $config->timeout );
        $this->assertSame( 33, $config->socketTimeout );
        $this->assertSame( true, $config->sslVerify );
        $this->assertSame( false, $config->throwOnError );
    }

    public function testObjectInput() : void
    {
        $o = new stdClass();
        $o->host = 'objHost';
        $o->username = 'objUser';
        $o->password = 'objPass';
        $o->ssl = true;
        $o->port = 4444;
        $o->sslPort = 5555;
        $o->timeout = 77;
        $o->socketTimeout = 88;
        $o->sslVerify = false;
        $o->throwOnError = false;

        $config = new Config( $o );
        $this->assertSame( 'objHost', $config->host );
        $this->assertSame( 'objUser', $config->username );
        $this->assertSame( 'objPass', $config->password );
        $this->assertSame( true, $config->ssl );
        $this->assertSame( 4444, $config->port );
        $this->assertSame( 5555, $config->sslPort );
        $this->assertSame( 77, $config->timeout );
        $this->assertSame( 88, $config->socketTimeout );
        $this->assertSame( false, $config->sslVerify );
        $this->assertSame( false, $config->throwOnError );
    }

    public function setUp() : void
    {
        putenv('ROUTEROS_HOST');
        putenv('ROUTEROS_USERNAME');
        putenv('ROUTEROS_PASSWORD');
        putenv('ROUTEROS_SSL');
        putenv('ROUTEROS_PORT');
        putenv('ROUTEROS_SSL_PORT');
        putenv('ROUTEROS_TIMEOUT');
        putenv('ROUTEROS_SOCKET_TIMEOUT');
        putenv('ROUTEROS_SSL_VERIFY');
        putenv('ROUTEROS_THROW_ON_ERROR');
    }

    private static function populateEnvVars() : void
    {
        putenv('ROUTEROS_HOST=envHost');
        putenv('ROUTEROS_USERNAME=envUser');
        putenv('ROUTEROS_PASSWORD=envPass');
        putenv('ROUTEROS_SSL=true');
        putenv('ROUTEROS_PORT=6666');
        putenv('ROUTEROS_SSL_PORT=7777');
        putenv('ROUTEROS_TIMEOUT=99');
        putenv('ROUTEROS_SOCKET_TIMEOUT=88');
        putenv('ROUTEROS_SSL_VERIFY=true');
        putenv('ROUTEROS_THROW_ON_ERROR=false');
    }
}