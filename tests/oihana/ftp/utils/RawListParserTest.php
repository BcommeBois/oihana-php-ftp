<?php

namespace tests\oihana\ftp\utils ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpFileType ;
use oihana\ftp\utils\RawListParser ;

/**
 * @package tests\oihana\ftp\utils
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class RawListParserTest extends TestCase
{
    public function testParsesAFileLine() : void
    {
        $file = RawListParser::parseLine( '-rw-r--r-- 1 alice users 1234 Jan 01 12:00 report.pdf' ) ;

        $this->assertNotNull( $file ) ;
        $this->assertSame( 'report.pdf' , $file->name ) ;
        $this->assertSame( FtpFileType::FILE , $file->type ) ;
        $this->assertSame( 1234 , $file->size ) ;
        $this->assertSame( 'alice' , $file->owner ) ;
        $this->assertSame( 'users' , $file->group ) ;
        $this->assertSame( '-rw-r--r--' , $file->permissions ) ;
        $this->assertNull( $file->target ) ;
    }

    public function testParsesADirectoryLine() : void
    {
        $file = RawListParser::parseLine( 'drwxr-xr-x 2 alice users 4096 Feb 10 09:30 images' ) ;

        $this->assertNotNull( $file ) ;
        $this->assertSame( 'images' , $file->name ) ;
        $this->assertTrue( $file->isDirectory() ) ;
    }

    public function testParsesASymbolicLinkLine() : void
    {
        $file = RawListParser::parseLine( 'lrwxrwxrwx 1 alice users 7 Mar 03 08:00 current -> release' ) ;

        $this->assertNotNull( $file ) ;
        $this->assertTrue( $file->isLink() ) ;
        $this->assertSame( 'current' , $file->name ) ;
        $this->assertSame( 'release' , $file->target ) ;
    }

    public function testSkipsTotalHeader() : void
    {
        $this->assertNull( RawListParser::parseLine( 'total 16' ) ) ;
    }

    public function testSkipsEmptyAndUnparseableLines() : void
    {
        $this->assertNull( RawListParser::parseLine( '   ' ) ) ;
        $this->assertNull( RawListParser::parseLine( 'this is not a listing line' ) ) ;
    }

    public function testParsesMultipleLinesAndSkipsNoise() : void
    {
        $files = RawListParser::parse(
        [
            'total 8' ,
            '-rw-r--r-- 1 u g 10 Jan 01 12:00 a.txt' ,
            '' ,
            'drwxr-xr-x 2 u g 4096 Jan 02 13:00 b' ,
            'garbage' ,
        ]) ;

        $this->assertCount( 2 , $files ) ;
        $this->assertSame( 'a.txt' , $files[ 0 ]->name ) ;
        $this->assertSame( 'b' , $files[ 1 ]->name ) ;
    }
}
