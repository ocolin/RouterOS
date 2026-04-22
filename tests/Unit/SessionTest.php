<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Tests\Unit;

use Ocolin\RouterOS\Config;
use Ocolin\RouterOS\Exceptions\LoginException;
use Ocolin\RouterOS\Session;
use Ocolin\RouterOS\Exceptions\SessionException;
use Ocolin\RouterOS\Interfaces\TransportInterface;
use Ocolin\RouterOS\DTO\Sentence;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testLoginSuccess() : void
    {
        $transport = $this->createStub(TransportInterface::class );
        $transport->method('readSentence')->willReturn(
            new Sentence(['!done'])
        );
        $config = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        $session->login();

        $this->assertTrue( $session->isLoggedIn());
    }


    public function testLoginFailure() : void
    {
        $this->expectException( LoginException::class );
        $transport = $this->createStub(TransportInterface::class );
        $transport->method('readSentence')->willReturn(
            new Sentence(['!trap', '=message=invalid user name or password'])
        );
        $config = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        $session->login();

        $this->assertFalse( $session->isLoggedIn()) ;
    }

    public function testSendCommandSuccess() : void
    {
        $transport = $this->createStub(TransportInterface::class );
        $transport->method('readSentence')
            ->willReturnOnConsecutiveCalls(
                new Sentence(['!done']),
                new Sentence(['!re', '=name=ether1']),
                new Sentence(['!re', '=name=ether2']),
                new Sentence(['!done'])
        );
        $config = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        $results = iterator_to_array(
            $session->sendCommand(['/interface/print'])
        );

        $this->assertCount( 2, $results );
        $this->assertSame( 're', $results[0]->reply );
        $this->assertSame( 're', $results[1]->reply );
    }


    public function testSendCommandTrap() : void
    {
        $this->expectException( SessionException::class );
        $transport = $this->createStub(TransportInterface::class );
        $transport->method('readSentence')
            ->willReturnOnConsecutiveCalls(
                new Sentence(['!done']),
                new Sentence(['!trap', '=message=ERROR']),
        );

        $config = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        iterator_to_array(
            $session->sendCommand(['/interface/print'])
        );
    }


    public function testLegacyLogin() : void
    {
        $transport = $this->createStub( TransportInterface::class );
        $transport->method('readSentence' )
            ->willReturnOnConsecutiveCalls(
                new Sentence(['!done', '=ret=abc123def456']),  // challenge
                new Sentence(['!done'])                         // success
            );

        $config  = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        $session->login();

        $this->assertTrue( $session->isLoggedIn() );
    }

    public function testLegacyLoginFailure() : void
    {
        $this->expectException( SessionException::class );
        $transport = $this->createStub( TransportInterface::class );
        $transport->method('readSentence' )
            ->willReturnOnConsecutiveCalls(
                new Sentence(['!done', '=ret=abc123def456']),  // challenge
                new Sentence(['!trap', '=message=invalid user name or password'])                         // success
            );

        $config  = new Config(['host' => 'localhost']);
        $session = new Session( config: $config, transport: $transport );
        $session->login();
    }
}