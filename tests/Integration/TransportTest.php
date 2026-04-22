<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\tests\Integration;

use Ocolin\RouterOS\Transport;
use Ocolin\RouterOS\Config;
use Ocolin\RouterOS\Exceptions\ConnectionException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Ocolin\EasyEnv\Env;

class TransportTest extends TestCase
{
    #[Group('integration')]
    public function test_Instantiation_Success() : void
    {
        $config = new Config();
        $transport = new Transport( $config );
        $this->assertInstanceOf( Transport::class, $transport );
    }

    #[Group('integration')]
    public function test_Instantiation_Failure() : void
    {
        $config = new Config(['host' => '127.0.0.100', 'timeout' => 1 ]);
        $this->expectException( ConnectionException::class );
        $transport = new Transport( $config );
    }

    #[Group('integration')]
    public function testSendAndReadSentence() : void
    {
        $transport = new Transport( new Config());

        $transport->sendSentence(
            ['/login', '=name=admin', '=password=' . $_ENV['ROUTEROS_PASSWORD']]
        );
        $response = $transport->readSentence();

        $this->assertSame( 'done', $response->reply );
    }

    public static function setUpBeforeClass() : void
    {
        Env::load( files: __DIR__ . '/../../.env' );
    }
}