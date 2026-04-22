# RouterOS

A PHP 8.4 client for the MikroTik RouterOS API.

This library provides a clean, modern interface for communicating with MikroTik
RouterOS devices via their API protocol. Built with a layered architecture,
it handles the low-level binary protocol details so you can focus on managing
your network infrastructure. Features include a fluent query builder, SSL
support, environment variable configuration, and a comprehensive test suite.

---

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
  - [Parameters](#parameters)
  - [Using an Array](#using-an-array)
  - [Using a Config Object](#using-a-config-object)
  - [Environment Variables](#environment-variables)
- [Basic Usage](#basic-usage)
  - [String input](#string-input)
  - [Array input](#array-input)
  - [Command Object Input](#command-object-input)
- [Query Builder](#query-builder)
  - [Attribute](#attribute)
  - [Query](#query)
  - [Logical Operators](#logical-operators)
  - [Proplist](#proplist)
  - [Aliases](#Aliases)
- [Streaming](#streaming)
- [SSL](#ssl)
- [Migrating from routeros-api-php](#migrating-from-routeros-api-php)
- [Roadmap](#roadmap)
- [License](#license)

---

## Requirements

- PHP 8.4 or higher
- MikroTik RouterOS device with API service enabled

---

## Installation

```bash
composer require ocolin/routeros
```

---

## Quick Start

```php
use Ocolin\RouterOS\Client;

$client = new Client([
    'host'     => '192.168.88.1',
    'username' => 'admin',
    'password' => 'secret'
]);

$interfaces = $client->query( '/interface/print' );

foreach( $interfaces as $interface ) {
    echo $interface['name'] . "\n";
}
```

---

## Configuration

The client can be configured by passing an associative array, a `Config` object,
or any object whose properties match the configuration parameters. The array
approach is the most common and concise.

### Parameters

| Parameter | Type | Default | ENV Variable | Description |
|-----------|------|---------|--------------|-------------|
| host | string | required | `ROUTEROS_HOST` | IP address or hostname of device |
| username | string | `admin` | `ROUTEROS_USERNAME` | Username to authenticate with |
| password | string | `''` | `ROUTEROS_PASSWORD` | Password to authenticate with |
| ssl | bool | `false` | `ROUTEROS_SSL` | Enable SSL connection |
| port | int | `8728` | `ROUTEROS_PORT` | Port for non-SSL connections |
| sslPort | int | `8729` | `ROUTEROS_SSL_PORT` | Port for SSL connections |
| timeout | int | `10` | `ROUTEROS_TIMEOUT` | Connection timeout in seconds |
| socketTimeout | int | `30` | `ROUTEROS_SOCKET_TIMEOUT` | Response timeout in seconds |
| sslVerify | bool | `false` | `ROUTEROS_SSL_VERIFY` | Verify SSL certificate |

### Using an Array

```php
$client = new Client([
    'host'     => '192.168.88.1',
    'username' => 'admin',
    'password' => 'secret'
]);
```

### Using a Config Object

```php
$config = new Config([
    'host' => '192.168.88.1',
]);

$client = new Client( $config );
```

### Environment Variables

Environment variables can be used instead of passing configuration directly.
The ENV variable names are listed in the parameters table above.

```php
// .env file
ROUTEROS_HOST=192.168.88.1
ROUTEROS_USERNAME=admin
ROUTEROS_PASSWORD=secret
```

#### Prefix Support

To support multiple devices, an optional prefix can be used:

```php
// .env file
ROUTEROS_CORE_HOST=10.0.0.1
ROUTEROS_EDGE_HOST=10.0.0.2
```

```php
$core = new Client( envPrefix: 'CORE' );
$edge = new Client( envPrefix: 'EDGE' );
```

---

## Basic Usage

There are three methods that can be used for inputting data to a device. A simple string command, an array of command words, or a `Command` object which provides methods for more complex queries.

### String input

This is good for simple single command queries that don't require any special conditions.

#### Example

```php
$interfaces = $client->query( command: '/interface/print' );

foreach( $interfaces as $interface )
{
    echo $interface['name'] . PHP_EOL;
}

```

### Array input

Useful for advanced users who prefer working directly with the API syntax,
or for sending commands not yet supported by the Command builder.

#### Example

```php
$interfaces = $client->query( command: [
    '/interface/print',
    '=disabled=no',
    '?type=ether'
]);
foreach( $interfaces as $interface )
{
    echo $interface['name'] . PHP_EOL;
}
```

### Command Object Input

A `Command` object is included for making complex queries easier to type doesn't require that you know the API syntax.

#### Example

```php
use Ocolin\RouterOS\Command;

$interfaces = $client->query(
    command: new Command('/interface/print')
        ->attribute( key: 'disabled', value: false )
        ->query( 'type' )->equals( 'ether' )
);
foreach( $interfaces as $interface )
{
    echo $interface['name'] . PHP_EOL;
}
```

---

## Query Builder

The `Command` class allows you to create complex queries with a simple syntax. The `Query` takes one argument for the constructor, which is the RouterOS command word.

```php
$command = new Command( '/interface/print' );
```

In this example we are sending a simple command with no other parameters. The command word is always required by RouterOS.


### Attribute

The attribute method allows you to specify attributes to your query. This is useful when creating and modifying an object on the Mikrotik device. Each attribute has a name and a value. RouterOS uses "yes" and "no" for boolean attributes, but this client will automatically convert boolean for you.

```php
$command = new Command( '/interface/print' )
    ->attribute( key: 'disabled', value: true );
```

### Query

The query method allows you to filter the results of your query.

#### Query Arguments

One way to add a query is by just using the constructor arguments. This is a means to manually enter the data without any helped functions.

```php
// Get interfaces of type equal to ether
$command = new Command( '/interface/print' )
    ->query( key: 'type', value: 'ether', operator: '=' );
```

```php
// Get interfaces with any link-downs
$command = new Command( '/interface/print' )
    ->query( key: 'link-downs', value: 0, operator: '>' );
```
#### Equals

This function takes a single value and which the key value must be equal to.

```php
// Get interfaces of type equal to ether
$command = new Command( '/interface/print' )
    ->query( key: 'type' )->equals( 'ether' );
```

#### lessThan

This function takes a single value which the key value must be less than.

```php
// Get interfaces with any no link-downs
$command = new Command( '/interface/print' )
    ->query( key: 'link-downs' )->lessThan( 1 );
```
#### greaterThan

This function takes a single value which the key value must be greater than.

```php
// Get interfaces with link-downs
$command = new Command( '/interface/print' )
    ->query( key: 'link-downs' )->greaterThan( 0 );
```

#### exists

Include if a property exists for the return results. 

```php
// Get interfaces with link-downs property
$command = new Command( '/interface/print' )
    ->query( key: 'link-downs' )->exists();
```

#### notExists

Include if a property does not exist.

```php
// Get interfaces without link-downs property
$command = new Command( '/interface/print' )
    ->query( key: 'link-downs' )->notExists();
```

### Logical Operators

When using multiple queries, you can specify a logical operator to indicate if all the queries should be met, or if any should be met, or it they should not be met.

The logical operator must always come after your query parameters for RouterOS to understand it.

#### And Operator

The `and` operator requires that all query parameters must be met. This is also the default operator so does not need to be specified.

```php
// Get interfaces of type equal to ether AND has link-downs
$command = new Command( '/interface/print' )
    ->query( key: 'type' )->equals( 'ether' )
    ->query( key: 'link-downs' )->greaterThan( 0 )
    ->and();
```

#### Or Operator

The `or` operator requires any of the query parameters to be met.

```php
// Get interfaces of type equal to ether OR has link-downs
$command = new Command( '/interface/print' )
    ->query( key: 'type' )->equals( 'ether' )
    ->query( key: 'link-downs' )->greaterThan( 0 )
    ->or();
```

#### Not Operator

This operator says to filter results to not contain the previous query parameter. Unlike AND and OR, this operator can be used prior to the end and can be specified for each query paramater.

```php
// Get interfaces of type not a vlan
$command = new Command( '/interface/print' )
    ->query( key: 'type' )->equals( 'vlan' )
    ->not();
```

### Proplist

The `proplist` allows you to limit the columns that are returned in your results.

```php
$command = new Command( '/interface/print' )->proplist(['name', 'type']);
```

### Aliases

There are a few alias functions which exist for those using method names found in other libraries.

#### Where

The where method is an alias for the `query` method.

```php
// Get interfaces without link-downs property
$command = new Command( '/interface/print' )
    ->where( key: 'link-downs' )->notExists();
```

#### Equal

The equal method is an alias for the attribute method.

```php
$command = new Command( '/interface/print' )
    ->equal( key: 'disabled', value: true );
```

##### Operations

The operations method is an alias for the logical operator methods, using
the same syntax as routeros-api-php.

```php
$command = new Command( '/interface/print' )
    ->query( 'type' )->equals( 'ether' )
    ->query( 'type' )->equals( 'vlan' )
    ->operations( '|' );
```

---

## Streaming

Some RouterOS commands return a continuous stream of data rather than a single
response. The most common example is the `/interface/listen` command which sends
updates whenever an interface changes state. The `stream()` method handles these
commands by returning a Generator that yields results one at a time as they arrive.

Unlike `query()` which collects all results and returns them as an array,
`stream()` never stops on its own — it will keep yielding results until you
break out of the loop or the connection is closed.

```php
foreach( $client->stream( '/interface/listen' ) as $update ) {
    echo $update['name'] . ' changed state' . PHP_EOL;
    
    // Stop listening after first update
    if( $someCondition ) {
        break;
    }
}
```

The `stream()` method accepts the same input types as `query()` — a string
command, an array of words, or a `Command` object.

> **Note:** To gracefully stop a streaming command, break out of the loop.
> Explicit command cancellation using `/cancel` will be available in a future
> release when concurrent command support is added.

---

## SSL

The client supports SSL connections using RouterOS's secure API service.
SSL is disabled by default and must be enabled in your configuration.

### RouterOS Setup

Before enabling SSL in the client, the api-ssl service must be configured
on your RouterOS device. A certificate is required.

Generate a self-signed certificate directly on the router:
```bash
/certificate add name=api-ssl common-name=api-ssl
/certificate sign api-ssl
/ip service set api-ssl certificate=api-ssl
/ip service enable api-ssl
```

Then allow connections by setting the allowed address under
**IP → Services → api-ssl**.

### Client Configuration

```php
$client = new Client([
    'host'      => '192.168.88.1',
    'username'  => 'admin',
    'password'  => 'secret',
    'ssl'       => true,
    'sslVerify' => false  // set to true if using a trusted certificate
]);
```

> **Note:** Most RouterOS devices use self-signed certificates. Set
> `sslVerify` to `false` unless you have installed a trusted certificate
> on your device.

---

## Migrating from routeros-api-php

This library was designed as a maintained alternative to the widely used but
unmaintained [routeros-api-php](https://github.com/EvilFreelancer/routeros-api-php)
library. Several alias methods are included to ease migration.

### Method Aliases

| routeros-api-php     | ocolin/routeros | Notes |
|----------------------|-----------------|-------|
| `->where()`          | `->query()` | Alias included |
| `->equal()`          | `->attribute()` | Alias included |
| `->operations('\|')` | `->or()` | Alias included |
| `->operations('&')`  | `->and()` | Alias included |
| `->operations('!')`  | `->not()` | Alias included |
| `->tag()`            | Coming in v2 | Not yet implemented |

### Configuration Differences

The client is configured using an array or `Config` object rather than
separate setter methods. The parameter names are similar but not identical:

| routeros-api-php | ocolin/routeros |
|-----------------|-----------------|
| `host` | `host` |
| `user` | `username` |
| `pass` | `password` |
| `port` | `port` |
| `ssl` | `ssl` |
| `timeout` | `timeout` |
| `socket_timeout` | `socketTimeout` |

---

## Roadmap

The following features are planned for future releases:

- **Concurrent commands** — support for tagged commands running simultaneously
- **Streaming cancellation** — explicit `/cancel` command support
- **SSH transport** — alternative transport layer using SSH authentication
- **CLI-style input** — parse RouterOS CLI syntax directly as command input
- **Laravel integration** — Service Provider and Facade for Laravel applications

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

