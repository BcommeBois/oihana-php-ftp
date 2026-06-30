<?php

namespace oihana\ftp\enums ;

use oihana\reflect\traits\ConstantsTrait ;

/**
 * Enumerates the runtime options accepted by {@see \ftp_set_option()} / {@see \ftp_get_option()}.
 *
 * The constants alias the matching `ext-ftp` predefined constants, so callers never
 * have to reach for the global `FTP_*` magic values directly.
 *
 * @package oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpConnectionOption
{
    use ConstantsTrait ;

    /**
     * The timeout, in seconds, used for network operations. Mirrors `FTP_TIMEOUT_SEC`.
     */
    public const int TIMEOUT_SEC = FTP_TIMEOUT_SEC ;

    /**
     * Whether to seek to a restart point on resumed transfers. Mirrors `FTP_AUTOSEEK`.
     */
    public const int AUTOSEEK = FTP_AUTOSEEK ;

    /**
     * Whether to use the IP address returned by the server for passive transfers.
     * Mirrors `FTP_USEPASVADDRESS`.
     */
    public const int USE_PASV_ADDRESS = FTP_USEPASVADDRESS ;
}
