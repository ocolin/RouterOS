<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS\tests\Unit;

use Ocolin\RouterOS\Command;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testBasicEndpoint() : void
    {
        $command = new Command( endpoint: '/interface/print' );
        $this->assertSame( ['/interface/print'], $command->toWords() );
    }

    public function testAttributes() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->attribute( key: 'keyword', value: 'valueword');
        $this->assertSame(
            ['/interface/print', '=keyword=valueword'], $command->toWords()
        );
    }

    public function testQueries() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->query( key: 'keyword', value: 'valueword', operator: '<' );
        $this->assertSame(
            ['/interface/print', '?<keyword=valueword'], $command->toWords()
        );
    }

    public function testExtraSlash() : void
    {
        $command = new Command( endpoint: '//interface/print' );
        $this->assertSame( ['/interface/print'], $command->toWords() );
    }

    public function testNoSlash() : void
    {
        $command = new Command( endpoint: 'interface/print' );
        $this->assertSame( ['/interface/print'], $command->toWords() );
    }

    public function testQueryFluent() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->query( key: 'keyword' )->equals( 'valueword' );
        $this->assertSame(
            ['/interface/print', '?keyword=valueword'], $command->toWords()
        );
    }

    public function testQueryExists() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->query( key: 'keyword' )->exists();
        $this->assertSame(
            ['/interface/print', '?keyword'], $command->toWords()
        );
    }

    public function testQueryNotExists() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->query( key: 'keyword' )->notExists();
        $this->assertSame(
            ['/interface/print', '?-keyword'], $command->toWords()
        );
    }

    public function testLogicalOr() : void
    {
        $command = new Command( endpoint: '/interface/print' )->or();
        $this->assertSame(
            ['/interface/print', '?#|'], $command->toWords()
        );
    }

    public function testLogicalAnd() : void
    {
        $command = new Command( endpoint: '/interface/print' )->and();
        $this->assertSame(
            ['/interface/print', '?#&'], $command->toWords()
        );
    }

    public function testLogicalNot() : void
    {
        $command = new Command( endpoint: '/interface/print' )->not();
        $this->assertSame(
            ['/interface/print', '?#!'], $command->toWords()
        );
    }

    public function testBoolAttribute() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->attribute( key: 'keyword', value: true );
        $this->assertSame(
            ['/interface/print', '=keyword=yes'], $command->toWords()
        );
    }

    public function testAliasWhere() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->where( key: 'keyword' )->equals( 'valueword' );
        $this->assertSame(
            ['/interface/print', '?keyword=valueword'], $command->toWords()
        );
    }

    public function testAliasEqual() : void
    {
        $command = new Command( endpoint: '/interface/print' )
            ->equal( key: 'keyword', value: 'valueword');
        $this->assertSame(
            ['/interface/print', '=keyword=valueword'], $command->toWords()
        );
    }

    public function testMultipleAttributes() : void
    {
        $command = new Command( endpoint: 'interface/print' )
            ->attribute( key: 'keyword1', value: 'valueword1' )
            ->attribute( key: 'keyword2', value: 'valueword2' );
        $this->assertSame(
            ['/interface/print', '=keyword1=valueword1', '=keyword2=valueword2'],
            $command->toWords()
        );
    }

    public function testProplist() : void
    {
        $command = new Command('/interface/print')
            ->proplist(['name', 'type', 'disabled']);

        $this->assertSame(
            ['/interface/print', '=.proplist=name,type,disabled'],
            $command->toWords()
        );
    }
}