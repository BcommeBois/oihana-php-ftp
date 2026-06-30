<?php

namespace tests\oihana\ftp\enums ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpTransferMode ;

/**
 * @package tests\oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpTransferModeTest extends TestCase
{
    public function testGetDefaultIsBinary() : void
    {
        $this->assertSame( FtpTransferMode::BINARY , FtpTransferMode::getDefault() ) ;
    }

    public function testToResource() : void
    {
        $this->assertSame( FTP_ASCII , FtpTransferMode::toResource( FtpTransferMode::ASCII ) ) ;
        $this->assertSame( FTP_BINARY , FtpTransferMode::toResource( FtpTransferMode::BINARY ) ) ;
        $this->assertSame( FTP_BINARY , FtpTransferMode::toResource( 'unknown' ) ) ;
    }
}
