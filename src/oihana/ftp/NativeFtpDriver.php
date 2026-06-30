<?php

namespace oihana\ftp ;

use FTP\Connection ;

/**
 * The production {@see FtpDriverInterface} implementation, backed by `ext-ftp`.
 *
 * Each method is a thin pass-through to the matching `ftp_*` function: the driver
 * holds the {@see Connection} handle and carries no business logic. Because these
 * calls cannot run without a live server, the class is excluded from coverage —
 * the client logic that drives it is fully tested against an in-memory fake. An
 * optional integration suite can exercise this driver against a real server.
 *
 * @package oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 *
 * @codeCoverageIgnore
 */
class NativeFtpDriver implements FtpDriverInterface
{
    /**
     * The underlying ext-ftp connection handle, or null when disconnected.
     * @var Connection|null
     */
    private ?Connection $connection = null ;

    /**
     * @inheritDoc
     */
    public function connect( string $host , int $port , int $timeout , bool $secure ) : bool
    {
        $connection = $secure
            ? @ftp_ssl_connect( $host , $port , $timeout )
            : @ftp_connect( $host , $port , $timeout ) ;

        if ( $connection === false )
        {
            return false ;
        }

        $this->connection = $connection ;

        return true ;
    }

    /**
     * @inheritDoc
     */
    public function disconnect() : bool
    {
        if ( $this->connection !== null )
        {
            ftp_close( $this->connection ) ;
            $this->connection = null ;
        }

        return true ;
    }

    /**
     * @inheritDoc
     */
    public function isConnected() : bool
    {
        return $this->connection !== null ;
    }

    /**
     * @inheritDoc
     */
    public function login( string $username , string $password ) : bool
    {
        return $this->connection !== null && @ftp_login( $this->connection , $username , $password ) ;
    }

    /**
     * @inheritDoc
     */
    public function setOption( int $option , int|bool $value ) : bool
    {
        return $this->connection !== null && ftp_set_option( $this->connection , $option , $value ) ;
    }

    /**
     * @inheritDoc
     */
    public function setPassive( bool $passive ) : bool
    {
        return $this->connection !== null && ftp_pasv( $this->connection , $passive ) ;
    }

    /**
     * @inheritDoc
     */
    public function get( string $localFile , string $remoteFile , int $mode ) : bool
    {
        return $this->connection !== null && ftp_get( $this->connection , $localFile , $remoteFile , $mode ) ;
    }

    /**
     * @inheritDoc
     */
    public function put( string $remoteFile , string $localFile , int $mode ) : bool
    {
        return $this->connection !== null && ftp_put( $this->connection , $remoteFile , $localFile , $mode ) ;
    }

    /**
     * @inheritDoc
     */
    public function append( string $remoteFile , string $localFile , int $mode ) : bool
    {
        return $this->connection !== null && ftp_append( $this->connection , $remoteFile , $localFile , $mode ) ;
    }

    /**
     * @inheritDoc
     */
    public function delete( string $remoteFile ) : bool
    {
        return $this->connection !== null && ftp_delete( $this->connection , $remoteFile ) ;
    }

    /**
     * @inheritDoc
     */
    public function rename( string $from , string $to ) : bool
    {
        return $this->connection !== null && ftp_rename( $this->connection , $from , $to ) ;
    }

    /**
     * @inheritDoc
     */
    public function size( string $remoteFile ) : int
    {
        return $this->connection !== null ? ftp_size( $this->connection , $remoteFile ) : -1 ;
    }

    /**
     * @inheritDoc
     */
    public function lastModified( string $remoteFile ) : int
    {
        return $this->connection !== null ? ftp_mdtm( $this->connection , $remoteFile ) : -1 ;
    }

    /**
     * @inheritDoc
     */
    public function chmod( int $mode , string $remoteFile ) : bool
    {
        return $this->connection !== null && ftp_chmod( $this->connection , $mode , $remoteFile ) !== false ;
    }

    /**
     * @inheritDoc
     */
    public function makeDirectory( string $directory ) : bool
    {
        return $this->connection !== null && ftp_mkdir( $this->connection , $directory ) !== false ;
    }

    /**
     * @inheritDoc
     */
    public function removeDirectory( string $directory ) : bool
    {
        return $this->connection !== null && ftp_rmdir( $this->connection , $directory ) ;
    }

    /**
     * @inheritDoc
     */
    public function changeDirectory( string $directory ) : bool
    {
        return $this->connection !== null && ftp_chdir( $this->connection , $directory ) ;
    }

    /**
     * @inheritDoc
     */
    public function changeToParentDirectory() : bool
    {
        return $this->connection !== null && ftp_cdup( $this->connection ) ;
    }

    /**
     * @inheritDoc
     */
    public function currentDirectory() : string|false
    {
        return $this->connection !== null ? ftp_pwd( $this->connection ) : false ;
    }

    /**
     * @inheritDoc
     */
    public function listNames( string $directory ) : array|false
    {
        return $this->connection !== null ? ftp_nlist( $this->connection , $directory ) : false ;
    }

    /**
     * @inheritDoc
     */
    public function rawList( string $directory , bool $recursive ) : array|false
    {
        return $this->connection !== null ? ftp_rawlist( $this->connection , $directory , $recursive ) : false ;
    }

    /**
     * @inheritDoc
     */
    public function mlsd( string $directory ) : array|false
    {
        return $this->connection !== null ? ftp_mlsd( $this->connection , $directory ) : false ;
    }
}
