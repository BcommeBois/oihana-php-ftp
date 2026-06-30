<?php

namespace tests\oihana\ftp\enums ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpSecurity ;

/**
 * @package tests\oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpSecurityTest extends TestCase
{
    public function testGetDefaultIsNone() : void
    {
        $this->assertSame( FtpSecurity::NONE , FtpSecurity::getDefault() ) ;
    }

    public function testIsSecure() : void
    {
        $this->assertTrue( FtpSecurity::isSecure( FtpSecurity::SSL ) ) ;
        $this->assertFalse( FtpSecurity::isSecure( FtpSecurity::NONE ) ) ;
        $this->assertFalse( FtpSecurity::isSecure( 'unknown' ) ) ;
    }
}
