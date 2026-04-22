<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

/**
 * @internal Returned by Command::query() for fluent interface chaining.
 */
class QueryWord
{

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param Command $command Command to update.
     * @param string $key Command key to update.
     */
    public function __construct( private Command $command,  private string $key ) {}



/* EQUALS
----------------------------------------------------------------------------- */

    /**
     * @param string|int|float|bool $value Value of query.
     * @return Command Updated command object.
     */
    public function equals( string|int|float|bool $value ) : Command
    {
        $this->command->query( key: $this->key, value: $value );

        return $this->command;
    }



/* LESS THAN
----------------------------------------------------------------------------- */

    /**
     * @param string|int|float|bool $value Value of query.
     * @return Command Updated command object.
     */
    public function lessThan( string|int|float|bool $value ) : Command
    {
        $this->command->query( key: $this->key, value: $value, operator: '<' );

        return $this->command;
    }



/* GREATER THAN
----------------------------------------------------------------------------- */

    /**
     * @param string|int|float|bool $value Value of query.
     * @return Command Updated command object.
     */
    public function greaterThan( string|int|float|bool $value ) : Command
    {
        $this->command->query( key: $this->key, value: $value, operator: '>' );

        return $this->command;
    }



/* EXISTS
----------------------------------------------------------------------------- */

    /**
     * @return Command Updated command object.
     */
    public function exists() : Command
    {
        $this->command->addQuery( word: "?{$this->key}" );

        return $this->command;
    }



/* DOES NOT EXIST
----------------------------------------------------------------------------- */

    /**
     * @return Command Updated command object.
     */
    public function notExists() : Command
    {
        $this->command->addQuery( word: "?-{$this->key}" );

        return $this->command;
    }
}
