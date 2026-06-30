<?php

namespace oihana\ftp ;

/**
 * The low-level transport contract behind the {@see FtpClient}.
 *
 * Every interaction with the underlying protocol funnels through this interface,
 * which serves two purposes:
 *
 * - **Testability** — the whole client logic (connection lifecycle, transfers,
 *   listing, retries) is exercised against an in-memory fake, so no live server
 *   is required to reach full coverage.
 * - **Extensibility** — the production {@see NativeFtpDriver} wraps `ext-ftp`;
 *   an SFTP driver could implement the same contract later without touching the
 *   client.
 *
 * A driver is *stateful*: it owns the underlying session/handle once {@see connect()}
 * succeeds, and exposes the remaining operations against that session.
 *
 * @package oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
interface FtpDriverInterface
{
    /**
     * Opens the control connection to the server.
     *
     * @param string $host    The remote host name or IP address.
     * @param int    $port    The remote control-channel port.
     * @param int    $timeout The connection timeout, in seconds.
     * @param bool   $secure  Whether to open a TLS-secured (FTPS) connection.
     *
     * @return bool True on success, false on failure.
     */
    public function connect( string $host , int $port , int $timeout , bool $secure ) : bool ;

    /**
     * Closes the connection and releases the underlying handle.
     *
     * @return bool True on success, false on failure.
     */
    public function disconnect() : bool ;

    /**
     * Indicates whether a connection is currently open.
     *
     * @return bool True when a session is established.
     */
    public function isConnected() : bool ;

    /**
     * Authenticates against the currently open connection.
     *
     * @param string $username The login user name.
     * @param string $password The login password.
     *
     * @return bool True on success, false when the server rejects the credentials.
     */
    public function login( string $username , string $password ) : bool ;

    /**
     * Sets a runtime option on the connection.
     *
     * @param int      $option One of the {@see \oihana\ftp\enums\FtpConnectionOption} constants.
     * @param int|bool $value  The value to assign.
     *
     * @return bool True on success, false on failure.
     */
    public function setOption( int $option , int|bool $value ) : bool ;

    /**
     * Toggles passive transfer mode.
     *
     * @param bool $passive Whether passive mode should be enabled.
     *
     * @return bool True on success, false on failure.
     */
    public function setPassive( bool $passive ) : bool ;
}
