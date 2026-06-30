<?php

namespace tests\oihana\ftp ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\Ftp ;
use oihana\ftp\enums\FtpFileType ;
use oihana\ftp\exceptions\FtpTransferException ;
use oihana\ftp\FtpClient ;
use oihana\ftp\schema\FtpFile ;

use tests\oihana\ftp\support\FakeFtpDriver ;

/**
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpDirectoryTest extends TestCase
{
    private function connectedClient( FakeFtpDriver $driver ) : FtpClient
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , $driver ) ;
        $client->connect() ;

        return $client ;
    }

    public function testCreateDirectorySucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $same = $client->createDirectory( '/pub/incoming' ) ;

        $this->assertSame( $client , $same ) ;
        $this->assertContains( '/pub/incoming' , $driver->madeDirectories ) ;
    }

    public function testCreateDirectoryFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->makeDirectoryResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->createDirectory( '/pub/incoming' ) ;
    }

    public function testCreateDirectoriesBuildsCumulativeRelativePaths() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->createDirectories( 'a/b/c' ) ;

        $this->assertSame( [ 'a' , 'a/b' , 'a/b/c' ] , $driver->madeDirectories ) ;
    }

    public function testCreateDirectoriesPreservesAbsolutePaths() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->createDirectories( '/a/b' ) ;

        $this->assertSame( [ '/a' , '/a/b' ] , $driver->madeDirectories ) ;
    }

    public function testRemoveDirectorySucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->removeDirectory( '/pub/old' ) ;

        $this->assertSame( '/pub/old' , $driver->removedDirectory ) ;
    }

    public function testRemoveDirectoryFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->removeDirectoryResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->removeDirectory( '/pub/old' ) ;
    }

    public function testChangeDirectorySucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->changeDirectory( '/pub' ) ;

        $this->assertSame( '/pub' , $driver->changedDirectory ) ;
    }

    public function testChangeDirectoryFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->changeDirectoryResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->changeDirectory( '/pub' ) ;
    }

    public function testParentDirectorySucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $same = $client->parentDirectory() ;

        $this->assertSame( $client , $same ) ;
        $this->assertContains( 'changeToParentDirectory' , $driver->calls ) ;
    }

    public function testParentDirectoryFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->parentResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->parentDirectory() ;
    }

    public function testCurrentDirectoryReturnsValue() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->currentDirectoryResult = '/pub/incoming' ;
        $client = $this->connectedClient( $driver ) ;

        $this->assertSame( '/pub/incoming' , $client->currentDirectory() ) ;
    }

    public function testCurrentDirectoryFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->currentDirectoryResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->currentDirectory() ;
    }

    public function testListNamesReturnsArray() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->listNamesResult = [ 'a.txt' , 'b.txt' ] ;
        $client = $this->connectedClient( $driver ) ;

        $this->assertSame( [ 'a.txt' , 'b.txt' ] , $client->listNames( '/pub' ) ) ;
    }

    public function testListNamesFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->listNamesResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->listNames( '/pub' ) ;
    }

    public function testRawListReturnsArray() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->rawListResult = [ 'drwxr-xr-x 2 u g 4096 Jan 01 12:00 dir' ] ;
        $client = $this->connectedClient( $driver ) ;

        $this->assertSame( $driver->rawListResult , $client->rawList( '/pub' ) ) ;
    }

    public function testRawListFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->rawListResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->rawList( '/pub' ) ;
    }

    public function testListFilesUsesMlsdWhenAvailable() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->mlsdResult =
        [
            [ 'name' => 'readme.txt' , 'type' => 'file' , 'size' => '12' ] ,
            [ 'name' => 'images' , 'type' => 'dir' ] ,
        ] ;
        $client = $this->connectedClient( $driver ) ;

        $files = $client->listFiles( '/pub' ) ;

        $this->assertContainsOnlyInstancesOf( FtpFile::class , $files ) ;
        $this->assertCount( 2 , $files ) ;
        $this->assertSame( 'readme.txt' , $files[ 0 ]->name ) ;
        $this->assertSame( FtpFileType::FILE , $files[ 0 ]->type ) ;
        $this->assertSame( 12 , $files[ 0 ]->size ) ;
        $this->assertTrue( $files[ 1 ]->isDirectory() ) ;
        $this->assertNotContains( 'rawList' , $driver->calls ) ;
    }

    public function testListFilesFallsBackToRawListParsing() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->mlsdResult    = false ;
        $driver->rawListResult =
        [
            'total 8' ,
            '-rw-r--r-- 1 user group 123 Jan 01 12:00 file.txt' ,
            'drwxr-xr-x 2 user group 4096 Jan 02 13:00 folder' ,
        ] ;
        $client = $this->connectedClient( $driver ) ;

        $files = $client->listFiles( '/pub' ) ;

        $this->assertCount( 2 , $files ) ;
        $this->assertSame( 'file.txt' , $files[ 0 ]->name ) ;
        $this->assertTrue( $files[ 1 ]->isDirectory() ) ;
    }

    public function testListFilesFallbackFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->mlsdResult    = false ;
        $driver->rawListResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->listFiles( '/pub' ) ;
    }

    public function testDirectoryOperationRequiresConnection() : void
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , new FakeFtpDriver() ) ;

        $this->expectException( FtpTransferException::class ) ;
        $this->expectExceptionMessage( 'No active FTP connection' ) ;
        $client->listNames( '/pub' ) ;
    }
}
