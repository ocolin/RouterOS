<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\Tests\Unit;

use Ocolin\RouterOS\Transport;
use Ocolin\RouterOS\Exceptions\TransportException;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    public function testOneByte() : void
    {
        $result = Transport::encodeLength( 6 );
        $this->assertSame( 1, strlen( $result ));
        $this->assertSame( '06', bin2hex( $result ));
    }

    public function testTwoByte() : void
    {
        $result = Transport::encodeLength( 0x80 );
        $this->assertSame( 2, strlen( $result ) );
        $this->assertSame( '8080', bin2hex( $result ) );
    }

    public function testThreeByte() : void
    {
        $result = Transport::encodeLength( 0x4000 );
        $this->assertSame( 3, strlen( $result ) );
        $this->assertSame( 'c04000', bin2hex( $result ) );
    }

    public function testFourByte() : void
    {
        $result = Transport::encodeLength( 0x200000 );
        $this->assertSame( 4, strlen( $result ) );
        $this->assertSame( 'e0200000', bin2hex( $result ) );
    }

    public function testEncodeLengthException() : void
    {
        $this->expectException( TransportException::class );
        Transport::encodeLength( 0x10000000 );
    }
}