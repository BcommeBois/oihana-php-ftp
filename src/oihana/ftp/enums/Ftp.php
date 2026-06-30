<?php

namespace oihana\ftp\enums ;

use oihana\reflect\traits\ConstantsTrait ;

/**
 * Enumerates the configuration keys understood by the {@see \oihana\ftp\FtpClient}.
 *
 * The values double as the property names of {@see \oihana\ftp\options\FtpOptions},
 * so a configuration array and an `FtpOptions` instance are interchangeable.
 *
 * @package oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class Ftp
{
    use ConstantsTrait ;

    /**
     * The remote host name or IP address.
     */
    public const string HOST = 'host' ;

    /**
     * The remote control-channel port. Defaults to 21.
     */
    public const string PORT = 'port' ;

    /**
     * The login user name. Use `anonymous` for anonymous access.
     */
    public const string USERNAME = 'username' ;

    /**
     * The login password.
     */
    public const string PASSWORD = 'password' ;

    /**
     * The transport security mode, one of the {@see FtpSecurity} constants.
     */
    public const string SECURITY = 'security' ;

    /**
     * Whether to use passive mode (recommended behind NAT/firewalls). Defaults to true.
     */
    public const string PASSIVE = 'passive' ;

    /**
     * The connection timeout, in seconds. Defaults to 90.
     */
    public const string TIMEOUT = 'timeout' ;

    /**
     * An optional remote base directory the client changes into right after login.
     */
    public const string ROOT = 'root' ;

    /**
     * The default transfer mode, one of the {@see FtpTransferMode} constants.
     */
    public const string TRANSFER_MODE = 'transferMode' ;

    /**
     * The maximum number of attempts for a transient connection failure. Defaults to 3.
     */
    public const string MAX_RETRIES = 'maxRetries' ;
}
