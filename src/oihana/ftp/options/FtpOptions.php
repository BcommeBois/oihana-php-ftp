<?php

namespace oihana\ftp\options ;

use oihana\options\Options ;

use oihana\ftp\enums\FtpSecurity ;

/**
 * A typed, fluent configuration object for the {@see \oihana\ftp\FtpClient}.
 *
 * Its property names match the {@see \oihana\ftp\enums\Ftp} configuration keys, so an
 * `FtpOptions` instance and a plain configuration array are interchangeable when
 * constructing the client.
 *
 * @package oihana\ftp\options
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpOptions extends Options
{
    /**
     * The remote host name or IP address.
     * @var string|null
     */
    public ?string $host = null ;

    /**
     * The maximum number of attempts for a transient connection failure.
     * @var int
     */
    public int $maxRetries = 3 ;

    /**
     * Whether to use passive mode.
     * @var bool
     */
    public bool $passive = true ;

    /**
     * The login password.
     * @var string|null
     */
    public ?string $password = null ;

    /**
     * The control-channel port.
     * @var int
     */
    public int $port = 21 ;

    /**
     * An optional remote base directory to change into right after login.
     * @var string|null
     */
    public ?string $root = null ;

    /**
     * The transport security mode, one of the {@see FtpSecurity} constants.
     * @var string
     */
    public string $security = FtpSecurity::NONE ;

    /**
     * The connection timeout, in seconds.
     * @var int
     */
    public int $timeout = 90 ;

    /**
     * The login user name.
     * @var string|null
     */
    public ?string $username = null ;

    /**
     * Returns the string expression of the object (the remote host).
     * @return string
     */
    public function __toString() : string
    {
        return $this->host ?? '' ;
    }
}
