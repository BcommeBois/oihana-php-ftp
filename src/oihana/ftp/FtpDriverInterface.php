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

    /**
     * Downloads a remote file to the local filesystem.
     *
     * @param string $localFile  The destination path on the local filesystem.
     * @param string $remoteFile The source path on the server.
     * @param int    $mode       The transfer mode (`FTP_ASCII` or `FTP_BINARY`).
     *
     * @return bool True on success, false on failure.
     */
    public function get( string $localFile , string $remoteFile , int $mode ) : bool ;

    /**
     * Uploads a local file to the server.
     *
     * @param string $remoteFile The destination path on the server.
     * @param string $localFile  The source path on the local filesystem.
     * @param int    $mode       The transfer mode (`FTP_ASCII` or `FTP_BINARY`).
     *
     * @return bool True on success, false on failure.
     */
    public function put( string $remoteFile , string $localFile , int $mode ) : bool ;

    /**
     * Appends the contents of a local file to a remote file.
     *
     * @param string $remoteFile The destination path on the server.
     * @param string $localFile  The source path on the local filesystem.
     * @param int    $mode       The transfer mode (`FTP_ASCII` or `FTP_BINARY`).
     *
     * @return bool True on success, false on failure.
     */
    public function append( string $remoteFile , string $localFile , int $mode ) : bool ;

    /**
     * Deletes a remote file.
     *
     * @param string $remoteFile The path of the file to delete.
     *
     * @return bool True on success, false on failure.
     */
    public function delete( string $remoteFile ) : bool ;

    /**
     * Renames or moves a remote file or directory.
     *
     * @param string $from The current path.
     * @param string $to   The new path.
     *
     * @return bool True on success, false on failure.
     */
    public function rename( string $from , string $to ) : bool ;

    /**
     * Returns the size of a remote file, in bytes.
     *
     * @param string $remoteFile The remote path.
     *
     * @return int The size in bytes, or -1 when it cannot be determined.
     */
    public function size( string $remoteFile ) : int ;

    /**
     * Returns the last-modified time of a remote file, as a Unix timestamp.
     *
     * @param string $remoteFile The remote path.
     *
     * @return int The Unix timestamp, or -1 when it cannot be determined.
     */
    public function lastModified( string $remoteFile ) : int ;

    /**
     * Changes the permissions of a remote file.
     *
     * @param int    $mode       The new permissions, as an octal value.
     * @param string $remoteFile The remote path.
     *
     * @return bool True on success, false on failure.
     */
    public function chmod( int $mode , string $remoteFile ) : bool ;
}
