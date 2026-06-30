<?php

namespace tests\oihana\ftp\enums ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpFileType ;

/**
 * @package tests\oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpFileTypeTest extends TestCase
{
    public function testFromMlsd() : void
    {
        $this->assertSame( FtpFileType::FILE , FtpFileType::fromMlsd( 'file' ) ) ;
        $this->assertSame( FtpFileType::DIRECTORY , FtpFileType::fromMlsd( 'dir' ) ) ;
        $this->assertSame( FtpFileType::DIRECTORY , FtpFileType::fromMlsd( 'cdir' ) ) ;
        $this->assertSame( FtpFileType::DIRECTORY , FtpFileType::fromMlsd( 'pdir' ) ) ;
        $this->assertSame( FtpFileType::UNKNOWN , FtpFileType::fromMlsd( 'whatever' ) ) ;
    }

    public function testFromUnixChar() : void
    {
        $this->assertSame( FtpFileType::DIRECTORY , FtpFileType::fromUnixChar( 'd' ) ) ;
        $this->assertSame( FtpFileType::LINK , FtpFileType::fromUnixChar( 'l' ) ) ;
        $this->assertSame( FtpFileType::FILE , FtpFileType::fromUnixChar( '-' ) ) ;
        $this->assertSame( FtpFileType::UNKNOWN , FtpFileType::fromUnixChar( 'b' ) ) ;
    }
}
