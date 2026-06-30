<?php

namespace oihana\ftp\enums ;

use oihana\reflect\traits\ConstantsTrait ;

/**
 * Enumerates the transport security modes supported by the FTP client.
 *
 * The native `ext-ftp` extension exposes a single secured mode through
 * {@see \ftp_ssl_connect()} (FTPS, TLS over the control channel). Be aware of its
 * limitations: certificate verification is not configurable and data-channel
 * protection is partial — FTPS here mainly shields credentials in transit. For
 * strong confidentiality, SFTP (a future driver) is the recommended path.
 *
 * @package oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpSecurity
{
    use ConstantsTrait ;

    /**
     * Plain FTP — no transport encryption. Credentials travel in clear text.
     */
    public const string NONE = 'none' ;

    /**
     * FTPS over TLS, established with {@see \ftp_ssl_connect()}.
     */
    public const string SSL = 'ssl' ;

    /**
     * Returns the default security mode.
     *
     * @return string The {@see NONE} mode.
     */
    public static function getDefault() : string
    {
        return self::NONE ;
    }

    /**
     * Indicates whether the given mode requires a secured (TLS) connection.
     *
     * @param string $security One of the security constants.
     *
     * @return bool True when the mode is {@see SSL}.
     */
    public static function isSecure( string $security ) : bool
    {
        return $security === self::SSL ;
    }
}
