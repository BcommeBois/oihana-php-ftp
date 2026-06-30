<?php

namespace tests\oihana\ftp ;

use UnexpectedValueException ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\Ftp ;
use oihana\ftp\FtpClient ;

use tests\oihana\ftp\support\FakeContainer ;
use tests\oihana\ftp\support\FakeFtpDriver ;
use tests\oihana\ftp\support\HasFtpClientFixture ;

/**
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class HasFtpClientTraitTest extends TestCase
{
    private function client() : FtpClient
    {
        return new FtpClient( [ Ftp::HOST => 'host' ] , new FakeFtpDriver() ) ;
    }

    public function testAssertThrowsWhenNotSet() : void
    {
        $this->expectException( UnexpectedValueException::class ) ;
        ( new HasFtpClientFixture() )->assertFtp() ;
    }

    public function testInitializeWithInstance() : void
    {
        $client  = $this->client() ;
        $fixture = new HasFtpClientFixture() ;

        $same = $fixture->initializeFtp( [ HasFtpClientFixture::FTP => $client ] ) ;

        $this->assertSame( $fixture , $same ) ;
        $this->assertSame( $client , $fixture->ftp ) ;
        $fixture->assertFtp() ;
    }

    public function testInitializeResolvesFromContainer() : void
    {
        $client    = $this->client() ;
        $container = new FakeContainer( [ 'ftpService' => $client ] ) ;
        $fixture   = new HasFtpClientFixture() ;

        $fixture->initializeFtp( [ HasFtpClientFixture::FTP => 'ftpService' ] , $container ) ;

        $this->assertSame( $client , $fixture->ftp ) ;
    }

    public function testInitializeWithUnknownServiceLeavesNull() : void
    {
        $fixture = new HasFtpClientFixture() ;

        $fixture->initializeFtp( [ HasFtpClientFixture::FTP => 'missing' ] , new FakeContainer() ) ;

        $this->assertNull( $fixture->ftp ) ;
    }

    public function testInitializeWithStringButNoContainerLeavesNull() : void
    {
        $fixture = new HasFtpClientFixture() ;

        $fixture->initializeFtp( [ HasFtpClientFixture::FTP => 'ftpService' ] ) ;

        $this->assertNull( $fixture->ftp ) ;
    }

    public function testInitializeWithNonClientValueLeavesNull() : void
    {
        $fixture = new HasFtpClientFixture() ;

        $fixture->initializeFtp( [ HasFtpClientFixture::FTP => 1234 ] ) ;

        $this->assertNull( $fixture->ftp ) ;
    }
}
