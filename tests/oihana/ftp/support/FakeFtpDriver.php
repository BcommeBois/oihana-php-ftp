<?php

namespace tests\oihana\ftp\support ;

use oihana\ftp\FtpDriverInterface ;

/**
 * An in-memory {@see FtpDriverInterface} used to drive the client without a server.
 *
 * Behaviour is configurable through public properties: queue connection results to
 * simulate transient failures, flip {@see $loginResult} to reject credentials, and
 * inspect {@see $calls}/{@see $options} to assert on what the client did.
 *
 * @package tests\oihana\ftp\support
 */
class FakeFtpDriver implements FtpDriverInterface
{
    /**
     * A queue of connection results consumed one per {@see connect()} call.
     * When empty, {@see $connectResult} is used instead.
     * @var array<int,bool>
     */
    public array $connectQueue = [] ;

    /**
     * The default connection result when {@see $connectQueue} is empty.
     */
    public bool $connectResult = true ;

    /**
     * Whether authentication succeeds.
     */
    public bool $loginResult = true ;

    /**
     * Whether a session is currently open.
     */
    public bool $connected = false ;

    /**
     * The last passive-mode value applied.
     */
    public bool $passive = false ;

    /**
     * The options applied through {@see setOption()}, keyed by option id.
     * @var array<int,int|bool>
     */
    public array $options = [] ;

    /**
     * The ordered list of method names invoked on the driver.
     * @var array<int,string>
     */
    public array $calls = [] ;

    /**
     * The arguments of the last {@see connect()} call.
     * @var array<string,mixed>
     */
    public array $connectArgs = [] ;

    public function connect( string $host , int $port , int $timeout , bool $secure ) : bool
    {
        $this->calls[]     = 'connect' ;
        $this->connectArgs = [ 'host' => $host , 'port' => $port , 'timeout' => $timeout , 'secure' => $secure ] ;

        $result = $this->connectQueue !== [] ? (bool) array_shift( $this->connectQueue ) : $this->connectResult ;

        $this->connected = $result ;

        return $result ;
    }

    public function login( string $username , string $password ) : bool
    {
        $this->calls[] = 'login' ;

        return $this->loginResult ;
    }

    public function setPassive( bool $passive ) : bool
    {
        $this->calls[]  = 'setPassive' ;
        $this->passive  = $passive ;

        return true ;
    }

    public function setOption( int $option , int|bool $value ) : bool
    {
        $this->calls[]            = 'setOption' ;
        $this->options[ $option ] = $value ;

        return true ;
    }

    public function isConnected() : bool
    {
        return $this->connected ;
    }

    public function disconnect() : bool
    {
        $this->calls[]   = 'disconnect' ;
        $this->connected = false ;

        return true ;
    }

    // ----------- File operations

    /**
     * Configurable results for the file operations.
     */
    public bool $getResult    = true ;
    public bool $putResult    = true ;
    public bool $appendResult = true ;
    public bool $deleteResult = true ;
    public bool $renameResult = true ;
    public bool $chmodResult  = true ;

    /**
     * Configurable values returned by the metadata operations.
     */
    public int $sizeResult         = 0 ;
    public int $lastModifiedResult = 0 ;

    /**
     * The arguments captured for the last call of each operation.
     * @var array<string,mixed>
     */
    public array $getArgs    = [] ;
    public array $putArgs    = [] ;
    public array $appendArgs = [] ;
    public array $renameArgs = [] ;
    public array $chmodArgs  = [] ;

    public function get( string $localFile , string $remoteFile , int $mode ) : bool
    {
        $this->calls[]  = 'get' ;
        $this->getArgs  = [ 'localFile' => $localFile , 'remoteFile' => $remoteFile , 'mode' => $mode ] ;

        return $this->getResult ;
    }

    public function put( string $remoteFile , string $localFile , int $mode ) : bool
    {
        $this->calls[]  = 'put' ;
        $this->putArgs  = [ 'remoteFile' => $remoteFile , 'localFile' => $localFile , 'mode' => $mode ] ;

        return $this->putResult ;
    }

    public function append( string $remoteFile , string $localFile , int $mode ) : bool
    {
        $this->calls[]     = 'append' ;
        $this->appendArgs  = [ 'remoteFile' => $remoteFile , 'localFile' => $localFile , 'mode' => $mode ] ;

        return $this->appendResult ;
    }

    public function delete( string $remoteFile ) : bool
    {
        $this->calls[] = 'delete' ;

        return $this->deleteResult ;
    }

    public function rename( string $from , string $to ) : bool
    {
        $this->calls[]     = 'rename' ;
        $this->renameArgs  = [ 'from' => $from , 'to' => $to ] ;

        return $this->renameResult ;
    }

    public function size( string $remoteFile ) : int
    {
        $this->calls[] = 'size' ;

        return $this->sizeResult ;
    }

    public function lastModified( string $remoteFile ) : int
    {
        $this->calls[] = 'lastModified' ;

        return $this->lastModifiedResult ;
    }

    public function chmod( int $mode , string $remoteFile ) : bool
    {
        $this->calls[]    = 'chmod' ;
        $this->chmodArgs  = [ 'mode' => $mode , 'remoteFile' => $remoteFile ] ;

        return $this->chmodResult ;
    }

    // ----------- Directory operations

    /**
     * Configurable results for the directory operations.
     */
    public bool $makeDirectoryResult   = true ;
    public bool $removeDirectoryResult = true ;
    public bool $changeDirectoryResult = true ;
    public bool $parentResult          = true ;

    /**
     * Configurable return values for the listing operations.
     */
    public string|false $currentDirectoryResult = '/home/user' ;

    /** @var array<int,string>|false */
    public array|false $listNamesResult = [] ;

    /** @var array<int,string>|false */
    public array|false $rawListResult = [] ;

    /** @var array<int,array<string,mixed>>|false */
    public array|false $mlsdResult = false ;

    /**
     * The directories created, in order.
     * @var array<int,string>
     */
    public array $madeDirectories = [] ;

    public ?string $removedDirectory = null ;
    public ?string $changedDirectory = null ;

    public function makeDirectory( string $directory ) : bool
    {
        $this->calls[]           = 'makeDirectory' ;
        $this->madeDirectories[] = $directory ;

        return $this->makeDirectoryResult ;
    }

    public function removeDirectory( string $directory ) : bool
    {
        $this->calls[]          = 'removeDirectory' ;
        $this->removedDirectory = $directory ;

        return $this->removeDirectoryResult ;
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

    public function currentDirectory() : string|false
    {
        $this->calls[] = 'currentDirectory' ;

        return $this->currentDirectoryResult ;
    }

    public function listNames( string $directory ) : array|false
    {
        $this->calls[] = 'listNames' ;

        return $this->listNamesResult ;
    }

    public function rawList( string $directory , bool $recursive ) : array|false
    {
        $this->calls[] = 'rawList' ;

        return $this->rawListResult ;
    }

    public function mlsd( string $directory ) : array|false
    {
        $this->calls[] = 'mlsd' ;

        return $this->mlsdResult ;
    }
}
