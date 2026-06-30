<?php

namespace tests\oihana\ftp\schema ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\FtpFileType ;
use oihana\ftp\schema\FtpFile ;

/**
 * @package tests\oihana\ftp\schema
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpFileTest extends TestCase
{
    public function testTypePredicates() : void
    {
        $this->assertTrue( ( new FtpFile( type: FtpFileType::FILE ) )->isFile() ) ;
        $this->assertTrue( ( new FtpFile( type: FtpFileType::DIRECTORY ) )->isDirectory() ) ;
        $this->assertTrue( ( new FtpFile( type: FtpFileType::LINK ) )->isLink() ) ;
        $this->assertFalse( ( new FtpFile( type: FtpFileType::FILE ) )->isDirectory() ) ;
    }

    public function testFromMlsdHydratesAllFacts() : void
    {
        $file = FtpFile::fromMlsd(
        [
            'name'       => 'report.pdf' ,
            'type'       => 'file' ,
            'size'       => '2048' ,
            'modify'     => '20240115093000' ,
            'UNIX.mode'  => '0644' ,
            'UNIX.owner' => 'alice' ,
            'UNIX.group' => 'users' ,
        ]) ;

        $this->assertSame( 'report.pdf' , $file->name ) ;
        $this->assertSame( FtpFileType::FILE , $file->type ) ;
        $this->assertSame( 2048 , $file->size ) ;
        $this->assertSame( '0644' , $file->permissions ) ;
        $this->assertSame( 'alice' , $file->owner ) ;
        $this->assertSame( 'users' , $file->group ) ;

        $expected = ( new \DateTimeImmutable( '2024-01-15 09:30:00' , new \DateTimeZone( 'UTC' ) ) )->getTimestamp() ;
        $this->assertSame( $expected , $file->modifiedTime ) ;
    }

    public function testFromMlsdMapsDirectoryTypesAndFallsBackToPerm() : void
    {
        $file = FtpFile::fromMlsd( [ 'name' => 'sub' , 'type' => 'cdir' , 'perm' => 'el' ] ) ;

        $this->assertTrue( $file->isDirectory() ) ;
        $this->assertSame( 'el' , $file->permissions ) ;
        $this->assertNull( $file->modifiedTime ) ;
        $this->assertNull( $file->owner ) ;
    }

    public function testFromMlsdHandlesUnparseableModifyTime() : void
    {
        $file = FtpFile::fromMlsd( [ 'name' => 'x' , 'type' => 'file' , 'modify' => 'not-a-date' ] ) ;

        $this->assertNull( $file->modifiedTime ) ;
    }
}
