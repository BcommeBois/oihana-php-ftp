<?php

namespace tests\oihana\ftp\support ;

use Psr\Container\ContainerInterface ;

/**
 * A minimal PSR-11 container backed by an associative array, for the mixin tests.
 *
 * @package tests\oihana\ftp\support
 */
class FakeContainer implements ContainerInterface
{
    /**
     * @param array<string,mixed> $entries
     */
    public function __construct( private array $entries = [] )
    {
    }

    public function get( string $id ) : mixed
    {
        return $this->entries[ $id ] ?? null ;
    }

    public function has( string $id ) : bool
    {
        return isset( $this->entries[ $id ] ) ;
    }
}
