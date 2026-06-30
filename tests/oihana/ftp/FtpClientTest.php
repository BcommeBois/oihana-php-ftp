<?php

namespace tests\oihana\ftp ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\auth\FtpCredentials ;
use oihana\ftp\enums\Ftp ;
use oihana\ftp\enums\FtpConnectionOption ;
use oihana\ftp\enums\FtpSecurity ;
use oihana\ftp\exceptions\FtpAuthenticationException ;
use oihana\ftp\exceptions\FtpConnectionException ;
use oihana\ftp\FtpClient ;
use oihana\ftp\options\FtpOptions ;

use tests\oihana\ftp\support\FakeFtpDriver ;
use tests\oihana\ftp\support\TestableFtpClient ;

/**
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpClientTest extends TestCase
{
    public function testDefaultConfigurationWithoutDriver() : void
    {
        $client = new FtpClient( [ Ftp::HOST => 'ftp.example.org' ] ) ;

        $this->assertSame( 'ftp.example.org' , $client->getHost() ) ;
        $this->assertSame( 21 , $client->getPort() ) ;
        $this->assertSame( 90 , $client->getTimeout() ) ;
        $this->assertSame( 3 , $client->getMaxRetries() ) ;
        $this->assertTrue( $client->isPassive() ) ;
        $this->assertFalse( $client->isSecure() ) ;
        $this->assertFalse( $client->isConnected() ) ;
    }

    public function testCustomConfiguration() : void
    {
        $client = new FtpClient
        (
            [
                Ftp::HOST        => 'host' ,
                Ftp::PORT        => 2121 ,
                Ftp::USERNAME    => 'alice' ,
                Ftp::PASSWORD    => 's3cret' ,
                Ftp::SECURITY    => FtpSecurity::SSL ,
                Ftp::PASSIVE     => false ,
                Ftp::TIMEOUT     => 30 ,
                Ftp::MAX_RETRIES => 5 ,
            ] ,
            new FakeFtpDriver()
        ) ;

        $this->assertSame( 2121 , $client->getPort() ) ;
        $this->assertSame( 30 , $client->getTimeout() ) ;
        $this->assertSame( 5 , $client->getMaxRetries() ) ;
        $this->assertFalse( $client->isPassive() ) ;
        $this->assertTrue( $client->isSecure() ) ;
        $this->assertInstanceOf( FtpCredentials::class , $client->getCredentials() ) ;
        $this->assertSame( 'alice' , $client->getCredentials()->username ) ;
    }

    public function testMaxRetriesIsFlooredToOne() : void
    {
        $client = new FtpClient( [ Ftp::MAX_RETRIES => 0 ] , new FakeFtpDriver() ) ;
        $this->assertSame( 1 , $client->getMaxRetries() ) ;
    }

    public function testConnectFromFtpOptions() : void
    {
        $options = new FtpOptions
        ([
            'host'     => 'opt-host' ,
            'username' => 'bob' ,
            'security' => FtpSecurity::SSL ,
        ]) ;

        $driver = new FakeFtpDriver() ;
        $client = new FtpClient( $options , $driver ) ;

        $this->assertSame( 'opt-host' , $client->getHost() ) ;
        $this->assertTrue( $client->isSecure() ) ;

        $client->connect() ;

        $this->assertTrue( $driver->connectArgs[ 'secure' ] ) ;
    }

    public function testConnectSuccessRunsTheFullHandshake() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = new FtpClient
        (
            [ Ftp::HOST => 'host' , Ftp::USERNAME => 'u' , Ftp::PASSWORD => 'p' , Ftp::TIMEOUT => 42 ] ,
            $driver
        ) ;

        $same = $client->connect() ;

        $this->assertSame( $client , $same ) ;
        $this->assertTrue( $client->isConnected() ) ;
        $this->assertSame( [ 'connect' , 'setOption' , 'login' , 'setPassive' ] , $driver->calls ) ;
        $this->assertSame( 42 , $driver->options[ FtpConnectionOption::TIMEOUT_SEC ] ) ;
        $this->assertTrue( $driver->passive ) ;
        $this->assertFalse( $driver->connectArgs[ 'secure' ] ) ;
    }

    public function testConnectIsIdempotentWhenAlreadyConnected() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , $driver ) ;

        $client->connect() ;
        $callCount = count( $driver->calls ) ;

        $client->connect() ;

        $this->assertSame( $callCount , count( $driver->calls ) ) ;
    }

    public function testConnectRetriesTransientFailureThenSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->connectQueue = [ false , true ] ;

        $client = new TestableFtpClient( [ Ftp::HOST => 'host' , Ftp::MAX_RETRIES => 3 ] , $driver ) ;

        $client->connect() ;

        $this->assertTrue( $client->isConnected() ) ;
        $this->assertSame( [ 2 ] , $client->waits ) ;
    }

    public function testConnectThrowsAfterExhaustingRetries() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->connectResult = false ;

        $client = new TestableFtpClient( [ Ftp::HOST => 'host' , Ftp::MAX_RETRIES => 3 ] , $driver ) ;

        try
        {
            $client->connect() ;
            $this->fail( 'Expected FtpConnectionException.' ) ;
        }
        catch ( FtpConnectionException $exception )
        {
            $this->assertStringContainsString( 'Unable to connect' , $exception->getMessage() ) ;
        }

        $this->assertFalse( $client->isConnected() ) ;
        $this->assertSame( [ 2 , 4 ] , $client->waits ) ;
    }

    public function testConnectThrowsAuthenticationErrorWithoutRetry() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->loginResult = false ;

        $client = new TestableFtpClient( [ Ftp::HOST => 'host' , Ftp::USERNAME => 'u' , Ftp::MAX_RETRIES => 3 ] , $driver ) ;

        try
        {
            $client->connect() ;
            $this->fail( 'Expected FtpAuthenticationException.' ) ;
        }
        catch ( FtpAuthenticationException $exception )
        {
            $this->assertStringContainsString( 'authentication failed' , $exception->getMessage() ) ;
        }

        $this->assertFalse( $client->isConnected() ) ;
        $this->assertSame( [] , $client->waits ) ;
        $this->assertContains( 'disconnect' , $driver->calls ) ;
    }

    public function testDisconnectClosesAnOpenConnection() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , $driver ) ;

        $client->connect() ;
        $this->assertTrue( $client->isConnected() ) ;

        $same = $client->disconnect() ;

        $this->assertSame( $client , $same ) ;
        $this->assertFalse( $client->isConnected() ) ;
    }

    public function testDisconnectWhenNotConnectedIsANoOp() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , $driver ) ;

        $client->disconnect() ;

        $this->assertSame( [] , $driver->calls ) ;
        $this->assertFalse( $client->isConnected() ) ;
    }
}
