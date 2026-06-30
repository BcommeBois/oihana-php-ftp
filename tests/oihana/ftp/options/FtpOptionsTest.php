<?php

namespace tests\oihana\ftp\options ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpSecurity ;
use oihana\ftp\options\FtpOptions ;

/**
 * @package tests\oihana\ftp\options
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpOptionsTest extends TestCase
{
    public function testDefaults() : void
    {
        $options = new FtpOptions() ;

        $this->assertNull( $options->host ) ;
        $this->assertSame( 21 , $options->port ) ;
        $this->assertTrue( $options->passive ) ;
        $this->assertSame( 90 , $options->timeout ) ;
        $this->assertSame( 3 , $options->maxRetries ) ;
        $this->assertSame( FtpSecurity::NONE , $options->security ) ;
    }

    public function testHydrationFromArray() : void
    {
        $options = new FtpOptions
        ([
            'host'     => 'ftp.example.org' ,
            'port'     => 2121 ,
            'username' => 'alice' ,
            'security' => FtpSecurity::SSL ,
        ]) ;

        $this->assertSame( 'ftp.example.org' , $options->host ) ;
        $this->assertSame( 2121 , $options->port ) ;
        $this->assertSame( 'alice' , $options->username ) ;
        $this->assertSame( FtpSecurity::SSL , $options->security ) ;
    }

    public function testToArrayRoundTrip() : void
    {
        $options = new FtpOptions( [ 'host' => 'ftp.example.org' , 'port' => 2121 ] ) ;

        $array = $options->toArray() ;

        $this->assertArrayHasKey( 'host' , $array ) ;
        $this->assertSame( 'ftp.example.org' , $array[ 'host' ] ) ;
        $this->assertSame( 2121 , $array[ 'port' ] ) ;
    }

    public function testToStringReturnsHost() : void
    {
        $this->assertSame( 'ftp.example.org' , (string) new FtpOptions( [ 'host' => 'ftp.example.org' ] ) ) ;
        $this->assertSame( '' , (string) new FtpOptions() ) ;
    }
}
