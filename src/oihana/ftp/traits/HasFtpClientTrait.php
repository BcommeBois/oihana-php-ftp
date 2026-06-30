<?php

namespace oihana\ftp\traits ;

use UnexpectedValueException ;

use Psr\Container\ContainerExceptionInterface ;
use Psr\Container\ContainerInterface ;
use Psr\Container\NotFoundExceptionInterface ;

use oihana\ftp\FtpClient ;

/**
 * Provides a standardized way to hold and initialize an {@see FtpClient} within a class.
 *
 * Responsibilities:
 * - storing a reference to an `FtpClient`;
 * - asserting that the client has been initialized before use;
 * - initializing the client from an array of parameters and an optional PSR-11 container.
 *
 * ```php
 * $this->initializeFtp( [ 'ftp' => 'ftpServiceName' ] , $container ) ;
 * $this->assertFtp() ;
 * $this->ftp->connect() ;
 * ```
 *
 * @package oihana\ftp\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait HasFtpClientTrait
{
    /**
     * The FTP client reference.
     * @var FtpClient|null
     */
    public ?FtpClient $ftp = null ;

    /**
     * The 'ftp' parameter key.
     */
    public const string FTP = 'ftp' ;

    /**
     * Asserts that the 'ftp' client property has been initialized.
     *
     * @return void
     *
     * @throws UnexpectedValueException When the `ftp` client is not set.
     */
    public function assertFtp() : void
    {
        if ( !isset( $this->ftp ) )
        {
            throw new UnexpectedValueException( 'The `ftp` client is not set.' ) ;
        }
    }

    /**
     * Initializes the 'ftp' property.
     *
     * Accepts an `FtpClient` instance directly, or a service name resolved from the
     * given PSR-11 container.
     *
     * @param array                   $init      The initialization parameters (expects an `ftp` entry).
     * @param ContainerInterface|null $container An optional PSR-11 container for service resolution.
     *
     * @return static This instance, for chaining.
     *
     * @throws ContainerExceptionInterface When the container fails to retrieve the entry.
     * @throws NotFoundExceptionInterface  When no entry is found for the given service name.
     */
    public function initializeFtp( array $init = [] , ?ContainerInterface $container = null ) : static
    {
        $ftp = $init[ self::FTP ] ?? null ;

        if ( is_string( $ftp ) && $ftp !== '' && $container?->has( $ftp ) )
        {
            $ftp = $container->get( $ftp ) ;
        }

        $this->ftp = $ftp instanceof FtpClient ? $ftp : null ;

        return $this ;
    }
}
