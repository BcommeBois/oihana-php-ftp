<?php

namespace tests\oihana\ftp\auth ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\auth\FtpCredentials ;

/**
 * @package tests\oihana\ftp\auth
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpCredentialsTest extends TestCase
{
    public function testHoldsUsernameAndPassword() : void
    {
        $credentials = new FtpCredentials( 'alice' , 's3cret' ) ;

        $this->assertSame( 'alice' , $credentials->username ) ;
        $this->assertSame( 's3cret' , $credentials->password ) ;
    }

    public function testDefaultsAreEmpty() : void
    {
        $credentials = new FtpCredentials() ;

        $this->assertSame( '' , $credentials->username ) ;
        $this->assertSame( '' , $credentials->password ) ;
    }

    public function testToStringExposesOnlyTheUsername() : void
    {
        $credentials = new FtpCredentials( 'alice' , 's3cret' ) ;

        $this->assertSame( 'alice' , (string) $credentials ) ;
    }

    public function testIsAnonymous() : void
    {
        $this->assertTrue( ( new FtpCredentials() )->isAnonymous() ) ;
        $this->assertTrue( ( new FtpCredentials( 'anonymous' ) )->isAnonymous() ) ;
        $this->assertTrue( ( new FtpCredentials( 'ANONYMOUS' ) )->isAnonymous() ) ;
        $this->assertFalse( ( new FtpCredentials( 'alice' ) )->isAnonymous() ) ;
    }

    public function testClearWipesThePassword() : void
    {
        $credentials = new FtpCredentials( 'alice' , 's3cret' ) ;

        $credentials->clear() ;

        $this->assertSame( '' , $credentials->password ) ;
        $this->assertSame( 'alice' , $credentials->username ) ;
    }

    public function testClearIsIdempotent() : void
    {
        $credentials = new FtpCredentials( 'alice' , 's3cret' ) ;

        $credentials->clear() ;
        $credentials->clear() ;

        $this->assertSame( '' , $credentials->password ) ;
    }

    public function testDestructWipesThePassword() : void
    {
        $credentials = new FtpCredentials( 'alice' , 's3cret' ) ;

        $credentials->__destruct() ;

        $this->assertSame( '' , $credentials->password ) ;
    }
}
