<?php

namespace oihana\ftp\traits ;

use oihana\files\openssl\OpenSSLFileEncryption ;

use oihana\ftp\enums\FtpTransferMode ;
use oihana\ftp\exceptions\FtpTransferException ;

use function oihana\files\makeDirectory ;

/**
 * Adds convenience helpers that combine FTP transfers with OpenSSL file encryption
 * (reusing {@see OpenSSLFileEncryption} from `oihana/php-files`).
 *
 * A local file is encrypted to a temporary file before upload, and a downloaded file
 * is decrypted from a temporary file after transfer; the temporary file is always
 * removed. Because the payload is ciphertext, these transfers are forced to binary mode.
 *
 * @package oihana\ftp\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait FtpCryptoTrait
{
    /**
     * Encrypts a local file and uploads the ciphertext to the server.
     *
     * @param string $localFile  The plaintext source on the local filesystem.
     * @param string $remoteFile The destination path on the server.
     * @param string $passphrase The passphrase used to derive the encryption key.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the local file is missing, or the transfer fails.
     */
    public function uploadEncrypted( string $localFile , string $remoteFile , string $passphrase ) : static
    {
        $this->ensureConnected() ;

        if ( !is_file( $localFile ) || !is_readable( $localFile ) )
        {
            throw new FtpTransferException( sprintf( 'Local file "%s" does not exist or is not readable.' , $localFile ) ) ;
        }

        $cipherFile = $this->temporaryCryptoFile() ;

        try
        {
            ( new OpenSSLFileEncryption( $passphrase ) )->encrypt( $localFile , $cipherFile ) ;
            $this->upload( $cipherFile , $remoteFile , FtpTransferMode::BINARY ) ;
        }
        finally
        {
            if ( is_file( $cipherFile ) )
            {
                @unlink( $cipherFile ) ;
            }
        }

        return $this ;
    }

    /**
     * Downloads an encrypted remote file and decrypts it to the local filesystem.
     *
     * @param string $remoteFile The ciphertext source on the server.
     * @param string $localFile  The plaintext destination on the local filesystem.
     * @param string $passphrase The passphrase used to derive the decryption key.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpTransferException When the transfer fails.
     */
    public function downloadDecrypted( string $remoteFile , string $localFile , string $passphrase ) : static
    {
        $this->ensureConnected() ;

        $cipherFile = $this->temporaryCryptoFile() ;

        try
        {
            $this->download( $remoteFile , $cipherFile , FtpTransferMode::BINARY , false ) ;
            makeDirectory( dirname( $localFile ) ) ;
            ( new OpenSSLFileEncryption( $passphrase ) )->decrypt( $cipherFile , $localFile ) ;
        }
        finally
        {
            if ( is_file( $cipherFile ) )
            {
                @unlink( $cipherFile ) ;
            }
        }

        return $this ;
    }

    // ----------- Private

    /**
     * Creates a temporary file used to hold ciphertext during a secure transfer.
     *
     * @return string The temporary file path.
     *
     * @throws FtpTransferException When the temporary file cannot be created.
     */
    private function temporaryCryptoFile() : string
    {
        $path = tempnam( sys_get_temp_dir() , 'oihana-ftp-' ) ;

        // @codeCoverageIgnoreStart
        if ( $path === false )
        {
            throw new FtpTransferException( 'Unable to create a temporary file for the secure transfer.' ) ;
        }
        // @codeCoverageIgnoreEnd

        return $path ;
    }
}
