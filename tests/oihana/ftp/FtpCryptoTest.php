<?php

namespace tests\oihana\ftp ;

use PHPUnit\Framework\TestCase ;

use oihana\ftp\enums\Ftp ;
use oihana\ftp\exceptions\FtpTransferException ;
use oihana\ftp\FtpClient ;

use tests\oihana\ftp\support\FakeFtpDriver ;

use function oihana\files\deleteDirectory ;

/**
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class FtpCryptoTest extends TestCase
{
    private string $tmp ;

    protected function setUp() : void
    {
        $this->tmp = sys_get_temp_dir() . '/oihana-ftp-crypto-' . uniqid() ;
        mkdir( $this->tmp ) ;
    }

    protected function tearDown() : void
    {
        if ( is_dir( $this->tmp ) )
        {
            deleteDirectory( $this->tmp ) ;
        }
    }

    private function connectedClient( FakeFtpDriver $driver ) : FtpClient
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , $driver ) ;
        $client->connect() ;

        return $client ;
    }

    public function testEncryptedRoundTrip() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->emulateStorage = true ;
        $client = $this->connectedClient( $driver ) ;

        $plain = $this->tmp . '/secret.txt' ;
        file_put_contents( $plain , 'Top secret payload — 42.' ) ;

        $client->uploadEncrypted( $plain , '/remote/secret.enc' , 'pass-phrase' ) ;

        // What landed on the "server" must not be the plaintext.
        $this->assertArrayHasKey( '/remote/secret.enc' , $driver->storage ) ;
        $this->assertNotSame( 'Top secret payload — 42.' , $driver->storage[ '/remote/secret.enc' ] ) ;

        $restored = $this->tmp . '/restored/secret.txt' ;
        $client->downloadDecrypted( '/remote/secret.enc' , $restored , 'pass-phrase' ) ;

        $this->assertSame( 'Top secret payload — 42.' , file_get_contents( $restored ) ) ;
    }

    public function testUploadEncryptedMissingLocalFileThrows() : void
    {
        $client = $this->connectedClient( new FakeFtpDriver() ) ;

        $this->expectException( FtpTransferException::class ) ;
        $this->expectExceptionMessage( 'does not exist' ) ;
        $client->uploadEncrypted( $this->tmp . '/missing.txt' , '/remote/x.enc' , 'pass' ) ;
    }

    public function testUploadEncryptedTransferFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->putResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $plain = $this->tmp . '/secret.txt' ;
        file_put_contents( $plain , 'data' ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->uploadEncrypted( $plain , '/remote/secret.enc' , 'pass' ) ;
    }

    public function testDownloadDecryptedTransferFailureThrows() : void
    {
        $driver = new FakeFtpDriver() ;
        $driver->getResult = false ;
        $client = $this->connectedClient( $driver ) ;

        $this->expectException( FtpTransferException::class ) ;
        $client->downloadDecrypted( '/remote/secret.enc' , $this->tmp . '/out.txt' , 'pass' ) ;
    }

    public function testSecureTransferRequiresConnection() : void
    {
        $client = new FtpClient( [ Ftp::HOST => 'host' ] , new FakeFtpDriver() ) ;

        $this->expectException( FtpTransferException::class ) ;
        $this->expectExceptionMessage( 'No active FTP connection' ) ;
        $client->uploadEncrypted( $this->tmp . '/whatever.txt' , '/remote/x.enc' , 'pass' ) ;
    }
}
