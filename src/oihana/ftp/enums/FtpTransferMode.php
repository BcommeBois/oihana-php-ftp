<?php

namespace oihana\ftp\enums ;

use oihana\reflect\traits\ConstantsTrait ;

/**
 * Enumerates the FTP transfer modes and maps them to the `ext-ftp` resource constants.
 *
 * Binary is the safe default: ASCII mode rewrites line endings and must only be used
 * for text payloads on platforms that need the translation.
 *
 * @package oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpTransferMode
{
    use ConstantsTrait ;

    /**
     * Text mode — performs end-of-line translation. Maps to `FTP_ASCII`.
     */
    public const string ASCII = 'ascii' ;

    /**
     * Binary mode — byte-for-byte transfer. Maps to `FTP_BINARY`.
     */
    public const string BINARY = 'binary' ;

    /**
     * Returns the default transfer mode.
     *
     * @return string The {@see BINARY} mode.
     */
    public static function getDefault() : string
    {
        return self::BINARY ;
    }

    /**
     * Resolves a transfer-mode constant to its `ext-ftp` resource value.
     *
     * @param string $mode One of the transfer-mode constants. Any unknown value falls
     *                     back to binary.
     *
     * @return int Either `FTP_ASCII` or `FTP_BINARY`.
     */
    public static function toResource( string $mode ) : int
    {
        return $mode === self::ASCII ? FTP_ASCII : FTP_BINARY ;
    }
}
