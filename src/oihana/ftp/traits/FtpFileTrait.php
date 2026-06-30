<?php

namespace oihana\ftp\traits ;

use oihana\files\exceptions\DirectoryException;
use oihana\ftp\enums\FtpTransferMode ;
use oihana\ftp\exceptions\FtpTransferException ;

use function oihana\files\makeDirectory ;

/**
 * Provides the single-file operations of the {@see \oihana\ftp\FtpClient}: download,
 * upload, append, delete, rename, size, last-modified time and permission changes.
 *
 * Every method requires an open connection (see {@see FtpConnectionTrait::connect()})
 * and funnels through the {@see \oihana\ftp\FtpDriverInterface}, raising an
 * {@see FtpTransferException} when the underlying operation fails.
 *
 * @package oihana\ftp\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait FtpFileTrait
{
    /**
     * Appends the contents of a local file to a remote file.
     *
     * @param string      $localFile  The source path on the local filesystem.
     * @param string      $remoteFile The destination path on the server.
     * @param string|null $mode       The transfer mode; defaults to the client's configured mode.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the local file is missing, the append fails, or no connection is open.
     */
    public function append( string $localFile , string $remoteFile , ?string $mode = null ) : static
    {
        $this->ensureConnected() ;
        $this->assertLocalFile( $localFile ) ;

        if ( !$this->driver->append( $remoteFile , $localFile , $this->resolveTransferMode( $mode ) ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to append "%s" to "%s".' , $localFile , $remoteFile ) ) ;
        }

        return $this ;
    }

    /**
     * Changes the permissions of a remote file.
     *
     * @param string $remoteFile  The remote path.
     * @param int    $permissions The new permissions, as an octal value (e.g. 0644).
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the change fails or no connection is open.
     */
    public function chmod( string $remoteFile , int $permissions ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->chmod( $permissions , $remoteFile ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to change permissions of "%s".' , $remoteFile ) ) ;
        }

        return $this ;
    }
        
    /**
     * Deletes a remote file.
     *
     * @param string $remoteFile The path of the file to delete.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the deletion fails or no connection is open.
     */
    public function delete( string $remoteFile ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->delete( $remoteFile ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to delete "%s".' , $remoteFile ) ) ;
        }

        return $this ;
    }

    /**
     * Downloads a remote file to the local filesystem.
     *
     * @param string $remoteFile The source path on the server.
     * @param string $localFile The destination path on the local filesystem.
     * @param string|null $mode The transfer mode (one of {@see FtpTransferMode});
     *                                      defaults to the client's configured mode.
     * @param bool $createDirectory Whether to create the local parent directory if missing.
     *
     * @return static This instance, for chaining.
     *
     * @throws DirectoryException
     * @throws FtpTransferException When the download fails or no connection is open.
     */
    public function download( string $remoteFile , string $localFile , ?string $mode = null , bool $createDirectory = true ) : static
    {
        $this->ensureConnected() ;

        if ( $createDirectory )
        {
            makeDirectory( dirname( $localFile ) ) ;
        }

        if ( !$this->driver->get( $localFile , $remoteFile , $this->resolveTransferMode( $mode ) ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to download "%s" to "%s".' , $remoteFile , $localFile ) ) ;
        }

        return $this ;
    }

    /**
     * Indicates whether a remote file exists (and exposes a readable size).
     *
     * Directories and files whose size the server refuses to report are treated as absent.
     *
     * @param string $remoteFile The remote path.
     *
     * @return bool True when the file exists with a known size.
     *
     * @throws FtpTransferException When no connection is open.
     */
    public function exists( string $remoteFile ) : bool
    {
        $this->ensureConnected() ;

        return $this->driver->size( $remoteFile ) >= 0 ;
    }

    /**
     * Returns the last-modified time of a remote file, as a Unix timestamp.
     *
     * Not every server supports the underlying `MDTM` command.
     *
     * @param string $remoteFile The remote path.
     *
     * @return int The Unix timestamp.
     *
     * @throws FtpTransferException When the time cannot be determined or no connection is open.
     */
    public function lastModified( string $remoteFile ) : int
    {
        $this->ensureConnected() ;

        $time = $this->driver->lastModified( $remoteFile ) ;

        if ( $time < 0 )
        {
            throw new FtpTransferException( sprintf( 'Unable to determine the last-modified time of "%s".' , $remoteFile ) ) ;
        }

        return $time ;
    }

    /**
     * Renames or moves a remote file or directory.
     *
     * @param string $from The current path.
     * @param string $to   The new path.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the rename fails or no connection is open.
     */
    public function rename( string $from , string $to ) : static
    {
        $this->ensureConnected() ;

        if ( !$this->driver->rename( $from , $to ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to rename "%s" to "%s".' , $from , $to ) ) ;
        }

        return $this ;
    }

    /**
     * Uploads a local file to the server.
     *
     * @param string      $localFile  The source path on the local filesystem.
     * @param string      $remoteFile The destination path on the server.
     * @param string|null $mode       The transfer mode; defaults to the client's configured mode.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the local file is missing, the upload fails, or no connection is open.
     */
    public function upload( string $localFile , string $remoteFile , ?string $mode = null ) : static
    {
        $this->ensureConnected() ;
        $this->assertLocalFile( $localFile ) ;

        if ( !$this->driver->put( $remoteFile , $localFile , $this->resolveTransferMode( $mode ) ) )
        {
            throw new FtpTransferException( sprintf( 'Failed to upload "%s" to "%s".' , $localFile , $remoteFile ) ) ;
        }

        return $this ;
    }

    /**
     * Returns the size of a remote file, in bytes.
     *
     * @param string $remoteFile The remote path.
     *
     * @return int The size in bytes.
     *
     * @throws FtpTransferException When the size cannot be determined or no connection is open.
     */
    public function size( string $remoteFile ) : int
    {
        $this->ensureConnected() ;

        $size = $this->driver->size( $remoteFile ) ;

        if ( $size < 0 )
        {
            throw new FtpTransferException( sprintf( 'Unable to determine the size of "%s".' , $remoteFile ) ) ;
        }

        return $size ;
    }


    // ----------- Private

    /**
     * Asserts that a local file exists and is readable.
     *
     * @param string $localFile The local path.
     *
     * @return void
     *
     * @throws FtpTransferException When the file is missing or unreadable.
     */
    private function assertLocalFile( string $localFile ) : void
    {
        if ( !is_file( $localFile ) || !is_readable( $localFile ) )
        {
            throw new FtpTransferException( sprintf( 'Local file "%s" does not exist or is not readable.' , $localFile ) ) ;
        }
    }

    /**
     * Asserts that the client holds an open connection.
     *
     * @return void
     *
     * @throws FtpTransferException When no connection is open.
     */
    private function ensureConnected() : void
    {
        if ( !$this->isConnected() )
        {
            throw new FtpTransferException( 'No active FTP connection. Call connect() first.' ) ;
        }
    }

    /**
     * Resolves a transfer-mode string to its `ext-ftp` resource value.
     *
     * @param string|null $mode The requested mode, or null to use the configured default.
     *
     * @return int Either `FTP_ASCII` or `FTP_BINARY`.
     */
    private function resolveTransferMode( ?string $mode ) : int
    {
        return FtpTransferMode::toResource( $mode ?? $this->transferMode ) ;
    }
}
