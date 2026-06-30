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
}
