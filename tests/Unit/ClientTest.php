<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Test\Unit;

use Ocolin\RouterOS\Client;
use Ocolin\RouterOS\Command;
use Ocolin\RouterOS\DTO\Sentence;
use Ocolin\RouterOS\Interfaces\SessionInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testQuery() : void
    {
        $session = $this->createStub(SessionInterface::class );
        $session->method('sendCommand' )
            ->willReturnCallback( function() {
                yield new Sentence(['!re', '=name=ether1', '=type=ether']);
                yield new Sentence(['!re', '=name=ether2', '=type=ether']);
            });
        $client = new Client( config: [ 'host' => 'localhost' ], session: $session );
        $result = $client->query( '/interface/print' );

        $this->assertCount( 2, $result );
        $this->assertArrayHasKey( 'name', $result[0] );
        $this->assertArrayHasKey( 'type', $result[0] );
        $this->assertArrayHasKey( 'name', $result[1] );
        $this->assertArrayHasKey( 'type', $result[1] );
        $this->assertSame( 'ether1', $result[0]['name'] );
        $this->assertSame( 'ether2', $result[1]['name'] );
        $this->assertSame( 'ether', $result[0]['type'] );
        $this->assertSame( 'ether', $result[1]['type'] );
    }

    public function testQueryWithCommand() : void
    {
        $session = $this->createStub( SessionInterface::class );
        $session->method('sendCommand' )
            ->willReturnCallback( function() {
                yield new Sentence(['!re', '=name=ether1']);
            });

        $client = new Client( config: ['host' => 'localhost'], session: $session );
        $result = $client->query(
            new Command('/interface/print')->query('type')->equals('ether')
        );

        $this->assertCount( 1, $result );
        $this->assertSame( 'ether1', $result[0]['name'] );
    }


    public function testStream() : void
    {
        $session = $this->createStub( SessionInterface::class );
        $session->method('sendCommand' )
            ->willReturnCallback( function() {
                yield new Sentence(['!re', '=name=ether1', '=type=ether']);
                yield new Sentence(['!re', '=name=ether2', '=type=ether']);
            });
        $client = new Client( config: [ 'host' => 'localhost' ], session: $session );
        $result = $client->stream( '/interface/print' );
        $items = iterator_to_array( $result );
        $this->assertCount( 2, $items );
    }
}