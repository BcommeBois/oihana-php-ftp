<?php

namespace oihana\ftp\traits ;

use oihana\ftp\exceptions\FtpTransferException ;
use oihana\ftp\schema\FtpFile ;
use oihana\ftp\utils\RawListParser ;

/**
 * Provides the directory operations of the {@see \oihana\ftp\FtpClient}: create, remove,
 * navigate, and list remote directories.
 *
 * Listings are returned either as raw names ({@see listNames()}), raw server lines
 * ({@see rawList()}) or as structured {@see FtpFile} entries ({@see listFiles()},
 * which prefers MLSD and falls back to parsing `ls -l` output).
 *
 * @package oihana\ftp\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait FtpDirectoryTrait
{
    /**
     * Changes the current working directory.
     *
     * @param string $directory The target directory.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the change fails or no connection is open.
     */
    public function changeDirectory( string $directory ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->changeDirectory( $directory ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to change to the directory "%s".' , $directory ) ) ;
        }

        return $this ;
    }

    /**
     * Creates a directory and every missing parent along the path (best effort).
     *
     * Each path segment is created in turn; segments that already exist are silently
     * skipped, mirroring `mkdir -p`.
     *
     * @param string $path The directory path to create.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When no connection is open.
     */
    public function createDirectories( string $path ) : static
    {
        $this->ensureConnected() ;

        $absolute    = str_starts_with( $path , '/' ) ;
        $accumulator = '' ;

        foreach ( array_filter( explode( '/' , $path ) , static fn( string $segment ) => $segment !== '' ) as $segment )
        {
            $accumulator = $accumulator === ''
                ? ( $absolute ? '/' . $segment : $segment )
                : $accumulator . '/' . $segment ;

            $this->driver->makeDirectory( $accumulator ) ;
        }

        return $this ;
    }

    /**
     * Creates a remote directory.
     *
     * @param string $directory The directory to create.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the creation fails or no connection is open.
     */
    public function createDirectory( string $directory ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->makeDirectory( $directory ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to create the directory "%s".' , $directory ) ) ;
        }

        return $this ;
    }

    /**
     * Returns the current working directory.
     *
     * @return string The absolute path of the current directory.
     *
     * @throws FtpTransferException When the directory cannot be determined or no connection is open.
     */
    public function currentDirectory() : string
    {
        $this->ensureConnected() ;

        $directory = $this->driver->currentDirectory() ;

        if ( $directory === false )
        {
            throw new FtpTransferException( 'Unable to determine the current directory.' ) ;
        }

        return $directory ;
    }

    /**
     * Returns the structured entries of a directory.
     *
     * Prefers the MLSD listing when the server supports it; otherwise falls back to
     * parsing the raw `ls -l` output.
     *
     * @param string $directory The directory to list (defaults to the current directory).
     *
     * @return array<int,FtpFile> The structured entries.
     *
     * @throws FtpTransferException When the listing fails or no connection is open.
     */
    public function listFiles( string $directory = '.' ) : array
    {
        $this->ensureConnected() ;

        $entries = $this->driver->mlsd( $directory ) ;

        if ( $entries !== false )
        {
            return array_map( static fn( array $entry ) => FtpFile::fromMlsd( $entry ) , $entries ) ;
        }

        $raw = $this->driver->rawList( $directory , false ) ;

        if ( $raw === false )
        {
            throw new FtpTransferException( sprintf( 'Failed to list "%s".' , $directory ) ) ;
        }

        return RawListParser::parse( $raw ) ;
    }

    /**
     * Returns the names of the entries in a directory.
     *
     * @param string $directory The directory to list (defaults to the current directory).
     *
     * @return array<int,string> The entry names.
     *
     * @throws FtpTransferException When the listing fails or no connection is open.
     */
    public function listNames( string $directory = '.' ) : array
    {
        $this->ensureConnected() ;

        $names = $this->driver->listNames( $directory ) ;

        if ( $names === false )
        {
            throw new FtpTransferException( sprintf( 'Failed to list the names in "%s".' , $directory ) ) ;
        }

        return $names ;
    }

    /**
     * Moves up to the parent directory.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the change fails or no connection is open.
     */
    public function parentDirectory() : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->changeToParentDirectory() )
        {
            throw new FtpTransferException( 'Failed to change to the parent directory.' ) ;
        }

        return $this ;
    }

    /**
     * Returns the raw, server-formatted listing of a directory.
     *
     * @param string $directory The directory to list (defaults to the current directory).
     * @param bool   $recursive Whether to recurse into sub-directories.
     *
     * @return array<int,string> The raw listing lines.
     *
     * @throws FtpTransferException When the listing fails or no connection is open.
     */
    public function rawList( string $directory = '.' , bool $recursive = false ) : array
    {
        $this->ensureConnected() ;

        $list = $this->driver->rawList( $directory , $recursive ) ;

        if ( $list === false )
        {
            throw new FtpTransferException( sprintf( 'Failed to list "%s".' , $directory ) ) ;
        }

        return $list ;
    }

    /**
     * Removes a remote directory.
     *
     * @param string $directory The directory to remove.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the removal fails or no connection is open.
     */
    public function removeDirectory( string $directory ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->removeDirectory( $directory ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to remove the directory "%s".' , $directory ) ) ;
        }

        return $this ;
    }
}
