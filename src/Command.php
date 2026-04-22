<?php

declare( strict_types = 1 );

namespace Ocolin\RouterOS;

use Ocolin\RouterOS\Interfaces\CommandInterface;

class Command implements CommandInterface
{
    /**
     * @var string Endpoint command word to send to device.
     */
    private string $endpoint;

    /**
     * @var string[] List of command attributes.
     */
    private array $attributes = [];

    /**
     * @var string[] List of command queries.
     */
    private array $queries = [];

    /**
     * @var string|int|null Tag queries.
     */
    private string|int|null $tag = null;

    use CommandAliases;

/* CONSTRUCTOR
----------------------------------------------------------------------------- */

    /**
     * @param string $endpoint Endpoint command word to execute.
     */
    public function __construct( string $endpoint )
    {
        $this->endpoint = '/' . ltrim( $endpoint, '/' );
    }



/* ATTRIBUTE
----------------------------------------------------------------------------- */

    /**
     * @param string $key Attribute name.
     * @param string|int|float|bool $value Attribute value.
     * @return $this Updated command.
     */
    public function attribute( string $key, string|int|float|bool $value ) : static
    {
        if( is_bool( $value ) ) { $value = $value ? 'yes' : 'no'; }
        $this->attributes[] = "={$key}={$value}";

        return $this;
    }



/* QUERY
----------------------------------------------------------------------------- */

    /**
     * @param string $key Name of query parameter.
     * @param string|int|float|bool|null $value Value of query parameter.
     * @param string $operator Operator of query parameter.
     * @return QueryWord|$this Updated command or query word.
     */
    public function query(
        string $key,
        string|int|float|bool|null $value = null,
        string $operator = ''
    ) : static|QueryWord
    {
        if( is_bool( $value ) ) { $value = $value ? 'yes' : 'no'; }
        if( $value === null ) {
            return new QueryWord( command: $this, key: $key );
        }

        $this->queries[] = "?{$operator}{$key}={$value}";

        return $this;
    }



/* ADD QUERY
----------------------------------------------------------------------------- */

    /**
     * Some helper functions need raw formatting.
     *
     * @internal for QueryWord only.
     * @param string $word Word to add to query.
     * @return static Updated command.
     */
    public function addQuery( string $word ) : static
    {
        $this->queries[] = $word;
        return $this;
    }



/* TO WORDS
----------------------------------------------------------------------------- */

    /**
     * Convert data into word list.
     *
     * @return string[] List of words making up command, attributes, and queries.
     */
    public function toWords() : array
    {
        $words = array_merge(
            [ $this->endpoint ],
            $this->attributes,
            $this->queries
        );

        if( $this->tag !== null ) { $words[] = ".tag={$this->tag}"; }

        return $words;
    }



/* OR OPERATOR
----------------------------------------------------------------------------- */

    /**
     * Logical OR on the queries. This MUST come AFTER your query statements.
     *
     * @return $this Updated command.
     */
    public function or() : static
    {
        $this->queries[] = '?#|';

        return $this;
    }



/* AND OPERATOR
----------------------------------------------------------------------------- */

    /**
     * Logical AND on the queries.
     *
     * @return $this Updated command.
     */
    public function and() : static
    {
        $this->queries[] = '?#&';

        return $this;
    }



/* NOT OPERATOR
----------------------------------------------------------------------------- */

    /**
     * Replace the top value with the opposite.
     *
     * @return $this Updated command.
     */
    public function not() : static
    {
        $this->queries[] = '?#!';

        return $this;
    }



/* PROPLIST
----------------------------------------------------------------------------- */

    /**
     * Add properties to query command query.
     *
     * @param string[] $properties List of properties.
     * @return $this Updated command.
     */
    public function proplist( array $properties ) : static
    {
        $this->attributes[] = '=.proplist=' . implode( ',', $properties );
        return $this;
    }



/* TAG
----------------------------------------------------------------------------- */

    /**
     * Add a tag to the request.
     *
     * @param string|int $tag Tag value to add.
     * @return $this Updated command.
     */
    public function tag( string|int $tag ) : static
    {
        $this->tag = $tag;

        return $this;
    }
}