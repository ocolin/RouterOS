<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Ocolin\RouterOS\Exceptions\CommandException;

trait CommandAliases
{


/* WHERE
----------------------------------------------------------------------------- */

    /**
     * Alias for query function.
     *
     * @param string $key Name of query parameter.
     * @param string|int|float|bool|null $value Value of query parameter.
     * @param string $operator Operator of query parameter.
     * @return QueryWord|$this Updated command or query word.
     */
    public function where(
        string $key,
        string|int|float|bool|null $value = null,
        string $operator = '='
    ) : static|QueryWord
    {
        return $this->query( key: $key, value: $value, operator: $operator );
    }



/* EQUAL
----------------------------------------------------------------------------- */

    /**
     * Alias for Attributes function.
     *
     * @param string $key Attribute name.
     * @param string|int|float|bool $value Attribute value.
     * @return $this Updated command.
     */
    public function equal( string $key, string|int|float|bool $value ) : static
    {
        return $this->attribute( key: $key, value: $value );
    }



/* OPERATIONS
----------------------------------------------------------------------------- */

    /**
     * @param string $operator Operator value to implement.
     * @return $this Updated command.
     */
    public function operations( string $operator ) : static
    {
        return match( $operator ) {
            '|' => $this->or(),
            '&' => $this->and(),
            '!' => $this->not(),
            default => throw new CommandException(
                message: "Invalid operator: {$operator}"
            )
        };
    }


}