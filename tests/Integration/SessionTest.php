<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\tests\Integration;

use Ocolin\EasyEnv\Env;
use Ocolin\RouterOS\Exceptions\LoginException;
use Ocolin\RouterOS\Config;
use Ocolin\RouterOS\Session;
use Ocolin\RouterOS\DTO\Sentence;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    #[Group('integration')]
    public function testLoginSuccess() : void
    {
        $config = new Config();
        $session = new Session( config: $config );
        $results = iterator_to_array(
            $session->sendCommand(['/system/resource/print'])
        );
        $this->assertNotEmpty( $results );
        $this->assertInstanceOf( Sentence::class, $results[0] );
        $this->assertSame( 're', $results[0]->reply );
        $this->assertNotEmpty( $results[0]->words );
    }

    #[Group('integration')]
    public function testLoginFailure() : void
    {
        $this->expectException( LoginException::class );
        $config = new Config(['password' => 'wrong']);
        $session = new Session( config: $config );
        iterator_to_array(
            $session->sendCommand(['/system/resource/print'])
        );
    }

    public static function setUpBeforeClass() : void
    {
        Env::load( files: __DIR__ . '/../../.env' );
    }
}
