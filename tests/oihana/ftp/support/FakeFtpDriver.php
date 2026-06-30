<?php

namespace tests\oihana\ftp\support ;

use oihana\ftp\FtpDriverInterface ;

/**
 * An in-memory {@see FtpDriverInterface} used to drive the client without a server.
 *
 * Behaviour is configurable through public properties: queue connection results to
 * simulate transient failures, flip the `*Result` flags to force failures, set
 * `emulateStorage` to move real bytes for round-trip transfers, and inspect `$calls`
 * / the `*Args` properties to assert on what the client did.
 *
 * @package tests\oihana\ftp\support
 */
class FakeFtpDriver implements FtpDriverInterface
{
    /** @var array<string,mixed> */
    public array $appendArgs = [] ;

    public bool $appendResult = true ;

    /** @var array<int,string> The ordered list of method names invoked on the driver. */
    public array $calls = [] ;

    public bool $changeDirectoryResult = true ;

    public ?string $changedDirectory = null ;

    /** @var array<string,mixed> */
    public array $chmodArgs = [] ;

    public bool $chmodResult = true ;

    /** @var array<string,mixed> The arguments of the last connect() call. */
    public array $connectArgs = [] ;

    /** @var array<int,bool> A queue of connection results consumed one per connect() call. */
    public array $connectQueue = [] ;

    public bool $connectResult = true ;

    public bool $connected = false ;

    public string|false $currentDirectoryResult = '/home/user' ;

    public bool $deleteResult = true ;

    /** When true, put()/get() actually move bytes through {@see $storage}. */
    public bool $emulateStorage = false ;

    /** @var array<string,mixed> */
    public array $getArgs = [] ;

    public bool $getResult = true ;

    public int $lastModifiedResult = 0 ;

    /** @var array<int,string>|false */
    public array|false $listNamesResult = [] ;

    public bool $loginResult = true ;

    /** @var array<int,string> The directories created, in order. */
    public array $madeDirectories = [] ;

    public bool $makeDirectoryResult = true ;

    /** @var array<int,array<string,mixed>>|false */
    public array|false $mlsdResult = false ;

    /** @var array<int,int|bool> The options applied through setOption(), keyed by option id. */
    public array $options = [] ;

    public bool $parentResult = true ;

    public bool $passive = false ;

    /** @var array<string,mixed> */
    public array $putArgs = [] ;

    public bool $putResult = true ;

    /** @var array<int,string>|false */
    public array|false $rawListResult = [] ;

    public bool $removeDirectoryResult = true ;

    public ?string $removedDirectory = null ;

    /** @var array<string,mixed> */
    public array $renameArgs = [] ;

    public bool $renameResult = true ;

    public int $sizeResult = 0 ;

    /** @var array<string,string> The in-memory remote store, keyed by remote path. */
    public array $storage = [] ;

    public function append( string $remoteFile , string $localFile , int $mode ) : bool
    {
        $this->calls[]     = 'append' ;
        $this->appendArgs  = [ 'remoteFile' => $remoteFile , 'localFile' => $localFile , 'mode' => $mode ] ;

        return $this->appendResult ;
    }

    public function changeDirectory( string $directory ) : bool
    {
        $this->calls[]          = 'changeDirectory' ;
        $this->changedDirectory = $directory ;

        return $this->changeDirectoryResult ;
    }

    public function changeToParentDirectory() : bool
    {
        $this->calls[] = 'changeToParentDirectory' ;

        return $this->parentResult ;
    }

    public function chmod( int $mode , string $remoteFile ) : bool
    {
        $this->calls[]    = 'chmod' ;
        $this->chmodArgs  = [ 'mode' => $mode , 'remoteFile' => $remoteFile ] ;

        return $this->chmodResult ;
    }

    public function connect( string $host , int $port , int $timeout , bool $secure ) : bool
    {
        $this->calls[]     = 'connect' ;
        $this->connectArgs = [ 'host' => $host , 'port' => $port , 'timeout' => $timeout , 'secure' => $secure ] ;

        $result = $this->connectQueue !== [] ? (bool) array_shift( $this->connectQueue ) : $this->connectResult ;

        $this->connected = $result ;

        return $result ;
    }

    public function currentDirectory() : string|false
    {
        $this->calls[] = 'currentDirectory' ;

        return $this->currentDirectoryResult ;
    }

    public function delete( string $remoteFile ) : bool
    {
        $this->calls[] = 'delete' ;

        return $this->deleteResult ;
    }

    public function disconnect() : bool
    {
        $this->calls[]   = 'disconnect' ;
        $this->connected = false ;

        return true ;
    }

    public function get( string $localFile , string $remoteFile , int $mode ) : bool
    {
        $this->calls[]  = 'get' ;
        $this->getArgs  = [ 'localFile' => $localFile , 'remoteFile' => $remoteFile , 'mode' => $mode ] ;

        if ( $this->emulateStorage && isset( $this->storage[ $remoteFile ] ) )
        {
            file_put_contents( $localFile , $this->storage[ $remoteFile ] ) ;
        }

        return $this->getResult ;
    }

    public function isConnected() : bool
    {
        return $this->connected ;
    }

    public function lastModified( string $remoteFile ) : int
    {
        $this->calls[] = 'lastModified' ;

        return $this->lastModifiedResult ;
    }

    public function listNames( string $directory ) : array|false
    {
        $this->calls[] = 'listNames' ;

        return $this->listNamesResult ;
    }

    public function login( string $username , string $password ) : bool
    {
        $this->calls[] = 'login' ;

        return $this->loginResult ;
    }

    public function makeDirectory( string $directory ) : bool
    {
        $this->calls[]           = 'makeDirectory' ;
        $this->madeDirectories[] = $directory ;

        return $this->makeDirectoryResult ;
    }

    public function mlsd( string $directory ) : array|false
    {
        $this->calls[] = 'mlsd' ;

        return $this->mlsdResult ;
    }

    public function put( string $remoteFile , string $localFile , int $mode ) : bool
    {
        $this->calls[]  = 'put' ;
        $this->putArgs  = [ 'remoteFile' => $remoteFile , 'localFile' => $localFile , 'mode' => $mode ] ;

        if ( $this->emulateStorage )
        {
            $this->storage[ $remoteFile ] = (string) file_get_contents( $localFile ) ;
        }

        return $this->putResult ;
    }

    public function rawList( string $directory , bool $recursive ) : array|false
    {
        $this->calls[] = 'rawList' ;

        return $this->rawListResult ;
    }

    public function removeDirectory( string $directory ) : bool
    {
        $this->calls[]          = 'removeDirectory' ;
        $this->removedDirectory = $directory ;

        return $this->removeDirectoryResult ;
    }

    public function rename( string $from , string $to ) : bool
    {
        $this->calls[]     = 'rename' ;
        $this->renameArgs  = [ 'from' => $from , 'to' => $to ] ;

        return $this->renameResult ;
    }

    public function setOption( int $option , int|bool $value ) : bool
    {
        $this->calls[]            = 'setOption' ;
        $this->options[ $option ] = $value ;

        return true ;
    }

    public function setPassive( bool $passive ) : bool
    {
        $this->calls[]  = 'setPassive' ;
        $this->passive  = $passive ;

        return true ;
    }

    public function size( string $remoteFile ) : int
    {
        $this->calls[] = 'size' ;

        return $this->sizeResult ;
    }
}
