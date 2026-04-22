<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Test\Integration;

use Ocolin\RouterOS\Client;
use Ocolin\RouterOS\Command;
use Ocolin\EasyEnv\Env;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{

    #[Group('integration')]
    public function testQueryWithString() : void
    {
        $client = new Client();
        $result = $client->query( '/interface/print' );
        $this->assertNotEmpty( $result );
        $this->assertIsArray( $result[0] );
        $this->assertArrayHasKey( 'name', $result[0] );
    }

    #[Group('integration')]
    public function testQueryWithArray() : void
    {
        $client = new Client();
        $result = $client->query( ['/interface/print', '?type=ether'] );
        $this->assertNotEmpty( $result );
        $this->assertIsArray( $result[0] );
        $this->assertArrayHasKey( 'name', $result[0] );
    }

    #[Group('integration')]
    public function testQueryWithCommand() : void
    {
        $client = new Client();
        $result = $client->query(
            new Command( endpoint: '/interface/print' )
                ->query( 'type' )
                ->equals( 'ether' )
        );
        $this->assertNotEmpty( $result );
        $this->assertIsArray( $result[0] );
        $this->assertArrayHasKey( 'name', $result[0] );
    }

    #[Group('integration')]
    public function testStream() : void
    {
        $client = new Client();
        $result = $client->stream( '/interface/print' );
        $items = iterator_to_array( $result );
        $this->assertNotEmpty( $items );
        $this->assertIsArray( $items[0] );
        $this->assertArrayHasKey( 'name', $items[0] );
    }

    public static function setUpBeforeClass(): void
    {
        Env::load( files: __DIR__ . '/../../.env' );
    }
}