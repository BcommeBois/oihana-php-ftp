<?php

namespace tests\oihana\ftp ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\Ftp ;
use oihana\ftp\enums\FtpTransferMode ;
use oihana\ftp\exceptions\FtpTransferException ;
use oihana\ftp\FtpClient ;

use tests\oihana\ftp\support\FakeFtpDriver ;

use function oihana\files\deleteDirectory ;

/**
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpFileTest extends TestCase
{
    private string $tmp ;

    protected function setUp() : void
    {
        $this->tmp = sys_get_temp_dir() . '/oihana-ftp-' . uniqid() ;
        mkdir( $this->tmp ) ;
    }

    protected function tearDown() : void
    {
        if ( is_dir( $this->tmp ) )
        {
            deleteDirectory( $this->tmp ) ;
        }
    }

    /**
     * Builds a connected client backed by the given fake driver.
     */
    private function connectedClient( FakeFtpDriver $driver , array $config = [] ) : FtpClient
    {
        $client = new FtpClient( array_merge( [ Ftp::HOST => 'host' ] , $config ) , $driver ) ;
        $client->connect() ;

        return $client ;
    }

    /**
     * Creates a local file with some content and returns its path.
     */
    private function localFile( string $name = 'source.txt' ) : string
    {
        $path = $this->tmp . '/' . $name ;
        file_put_contents( $path , 'payload' ) ;

        return $path ;
    }

    public function testDownloadSucceedsAndCreatesLocalDirectory() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $local = $this->tmp . '/nested/file.bin' ;
        $same  = $client->download( '/remote/file.bin' , $local ) ;

        $this->assertSame( $client , $same ) ;
        $this->assertTrue( is_dir( $this->tmp . '/nested' ) ) ;
        $this->assertContains( 'get' , $driver->calls ) ;
        $this->assertSame( '/remote/file.bin' , $driver->getArgs[ 'remoteFile' ] ) ;
        $this->assertSame( $local , $driver->getArgs[ 'localFile' ] ) ;
        $this->assertSame( FTP_BINARY , $driver->getArgs[ 'mode' ] ) ;
    }

    public function testDownloadWithoutCreatingDirectory() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->download( '/remote/file.bin' , $this->tmp . '/file.bin' , null , false ) ;

        $this->assertContains( 'get' , $driver->calls ) ;
    }

    public function testDownloadUsesExplicitAsciiMode() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->download( '/remote/file.txt' , $this->tmp . '/file.txt' , FtpTransferMode::ASCII ) ;

        $this->assertSame( FTP_ASCII , $driver->getArgs[ 'mode' ] ) ;
    }

    public function testDownloadFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->getResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->download( '/remote/file.bin' , $this->tmp . '/file.bin' ) ;
    }

    public function testOperationRequiresAnOpenConnection() : void
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , new FakeFtpDriver() ) ;

        $this->expectException( FtpTransferException::class ) ;
        $this->expectExceptionMessage( 'No active FTP connection' ) ;
        $client->download( '/remote/file.bin' , $this->tmp . '/file.bin' ) ;
    }

    public function testUploadSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;
        $local  = $this->localFile() ;

        $client->upload( $local , '/remote/source.txt' ) ;

        $this->assertSame( '/remote/source.txt' , $driver->putArgs[ 'remoteFile' ] ) ;
        $this->assertSame( $local , $driver->putArgs[ 'localFile' ] ) ;
        $this->assertSame( FTP_BINARY , $driver->putArgs[ 'mode' ] ) ;
    }

    public function testUploadMissingLocalFileThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $this->expectExceptionMessage( 'does not exist' ) ;
        $client->upload( $this->tmp . '/missing.txt' , '/remote/x.txt' ) ;
    }

    public function testUploadFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->putResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->upload( $this->localFile() , '/remote/source.txt' ) ;
    }

    public function testAppendSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;
        $local  = $this->localFile() ;

        $client->append( $local , '/remote/log.txt' ) ;

        $this->assertSame( '/remote/log.txt' , $driver->appendArgs[ 'remoteFile' ] ) ;
        $this->assertSame( $local , $driver->appendArgs[ 'localFile' ] ) ;
    }

    public function testAppendFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->appendResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->append( $this->localFile() , '/remote/log.txt' ) ;
    }

    public function testDeleteSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $same = $client->delete( '/remote/file.bin' ) ;

        $this->assertSame( $client , $same ) ;
        $this->assertContains( 'delete' , $driver->calls ) ;
    }

    public function testDeleteFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->deleteResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->delete( '/remote/file.bin' ) ;
    }

    public function testRenameSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->rename( '/remote/a.txt' , '/remote/b.txt' ) ;

        $this->assertSame( [ 'from' => '/remote/a.txt' , 'to' => '/remote/b.txt' ] , $driver->renameArgs ) ;
    }

    public function testRenameFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->renameResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->rename( '/remote/a.txt' , '/remote/b.txt' ) ;
    }

    public function testSizeReturnsValue() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->sizeResult = 4096 ;
        $client = $this->connectedClient( $driver ) ;

        $this->assertSame( 4096 , $client->size( '/remote/file.bin' ) ) ;
    }

    public function testSizeFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->sizeResult = -1 ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->size( '/remote/file.bin' ) ;
    }

    public function testLastModifiedReturnsValue() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->lastModifiedResult = 1_700_000_000 ;
        $client = $this->connectedClient( $driver ) ;

        $this->assertSame( 1_700_000_000 , $client->lastModified( '/remote/file.bin' ) ) ;
    }

    public function testLastModifiedFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->lastModifiedResult = -1 ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->lastModified( '/remote/file.bin' ) ;
    }

    public function testChmodSucceeds() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $client->chmod( '/remote/file.bin' , 0644 ) ;

        $this->assertSame( 0644 , $driver->chmodArgs[ 'mode' ] ) ;
        $this->assertSame( '/remote/file.bin' , $driver->chmodArgs[ 'remoteFile' ] ) ;
    }

    public function testChmodFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->chmodResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->chmod( '/remote/file.bin' , 0644 ) ;
    }

    public function testExists() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver ) ;

        $driver->sizeResult = 10 ;
        $this->assertTrue( $client->exists( '/remote/file.bin' ) ) ;

        $driver->sizeResult = -1 ;
        $this->assertFalse( $client->exists( '/remote/missing.bin' ) ) ;
    }

    public function testConfiguredTransferModeIsUsed() : void
    {
        $driver = new FakeFtpDriver() ;
        $client = $this->connectedClient( $driver , [ Ftp::TRANSFER_MODE => FtpTransferMode::ASCII ] ) ;

        $this->assertSame( FtpTransferMode::ASCII , $client->getTransferMode() ) ;

        $client->download( '/remote/file.txt' , $this->tmp . '/file.txt' , null , false ) ;

        $this->assertSame( FTP_ASCII , $driver->getArgs[ 'mode' ] ) ;
    }

    public function testDefaultTransferModeIsBinary() : void
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , new FakeFtpDriver() ) ;

        $this->assertSame( FtpTransferMode::BINARY , $client->getTransferMode() ) ;
    }
}
