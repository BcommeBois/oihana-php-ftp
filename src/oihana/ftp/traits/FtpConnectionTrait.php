<?php

namespace oihana\ftp\traits ;

use DI\DependencyException;
use DI\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface ;

use oihana\logging\LoggerTrait ;

use oihana\ftp\auth\FtpCredentials ;
use oihana\ftp\enums\Ftp ;
use oihana\ftp\enums\FtpConnectionOption ;
use oihana\ftp\enums\FtpSecurity ;
use oihana\ftp\enums\FtpTransferMode ;
use oihana\ftp\exceptions\FtpAuthenticationException ;
use oihana\ftp\exceptions\FtpConnectionException ;
use oihana\ftp\exceptions\FtpTransferException ;
use oihana\ftp\FtpDriverInterface ;
use oihana\ftp\NativeFtpDriver ;
use oihana\ftp\options\FtpOptions ;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Provides the connection lifecycle of the {@see \oihana\ftp\FtpClient}: configuration,
 * connect/login with retry, passive mode, secure transport and clean teardown.
 *
 * All transport calls go through an injected {@see FtpDriverInterface}, so the whole
 * lifecycle is testable without a live server.
 *
 * @package oihana\ftp\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait FtpConnectionTrait
{
    use LoggerTrait ;

    /**
     * Creates a new FTP client.
     *
     * @param array|FtpOptions $init Configuration keyed by {@see Ftp} constants
     *                                            (or an {@see FtpOptions} instance): `host`, `port`,
     *                                            `username`, `password`, `security`, `passive`,
     *                                            `timeout`, `maxRetries`, plus any logger options.
     * @param FtpDriverInterface|null $driver The transport driver. Defaults to a {@see NativeFtpDriver}.
     * @param ContainerInterface|null $container Optional PSR-11 container used to resolve a logger service.
     *
     * @throws DependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct( array|FtpOptions $init = [] , ?FtpDriverInterface $driver = null , ?ContainerInterface $container = null )
    {
        if ( $init instanceof FtpOptions )
        {
            $init = $init->toArray() ;
        }

        $this->driver = $driver ?? new NativeFtpDriver() ;

        $this->initializeLogger( $init , $container , false ) ;
        $this->initializeConfig( $init ) ;
    }

    /**
     * The maximum number of attempts for a transient (connection) failure.
     * @var int
     */
    public int $maxRetries = 3 ;

    /**
     * Opens the connection and authenticates, retrying transient failures with
     * exponential backoff. A no-op when already connected.
     *
     * @return static This instance, for chaining.
     *
     * @throws FtpConnectionException     When the connection cannot be established after every retry.
     * @throws FtpAuthenticationException When the server rejects the credentials (no retry).
     */
    public function connect() : static
    {
        if ( $this->isConnected() )
        {
            return $this ;
        }

        $attempts = 0 ;

        while ( true )
        {
            $attempts++ ;

            try
            {
                $this->establish() ;
                break ;
            }
            catch ( FtpConnectionException $exception )
            {
                $this->warning( sprintf( 'FTP connection attempt %d/%d failed: %s' , $attempts , $this->maxRetries , $exception->getMessage() ) ) ;

                $this->driver->disconnect() ;

                if ( $attempts >= $this->maxRetries )
                {
                    throw $exception ;
                }

                $this->waitBeforeRetry( 2 ** $attempts ) ;
            }
        }

        $this->applyRoot() ;

        return $this ;
    }

    /**
     * Closes the connection if it is open.
     *
     * @return static This instance, for chaining.
     */
    public function disconnect() : static
    {
        if ( $this->connected )
        {
            $this->driver->disconnect() ;
            $this->connected = false ;
            $this->notice( sprintf( 'Disconnected from FTP server "%s".' , $this->host ) ) ;
        }

        return $this ;
    }

    /**
     * Returns the configured login credentials.
     *
     * @return FtpCredentials The credentials holder.
     */
    public function getCredentials() : FtpCredentials
    {
        return $this->credentials ;
    }

    /**
     * Returns the configured remote host.
     *
     * @return string The host name or IP address.
     */
    public function getHost() : string
    {
        return $this->host ;
    }

    /**
     * Returns the configured maximum number of connection attempts.
     *
     * @return int The retry ceiling.
     */
    public function getMaxRetries() : int
    {
        return $this->maxRetries ;
    }

    /**
     * Returns the configured control-channel port.
     *
     * @return int The port number.
     */
    public function getPort() : int
    {
        return $this->port ;
    }

    /**
     * Returns the configured remote root directory.
     *
     * @return string The root directory, or an empty string when none is set.
     */
    public function getRoot() : string
    {
        return $this->root ;
    }

    /**
     * Returns the configured connection timeout.
     *
     * @return int The timeout, in seconds.
     */
    public function getTimeout() : int
    {
        return $this->timeout ;
    }

    /**
     * Returns the default transfer mode applied to file operations.
     *
     * @return string One of the {@see FtpTransferMode} constants.
     */
    public function getTransferMode() : string
    {
        return $this->transferMode ;
    }

    /**
     * Indicates whether a connection is currently open.
     *
     * @return bool True when the client is connected.
     */
    public function isConnected() : bool
    {
        return $this->connected && $this->driver->isConnected() ;
    }

    /**
     * Indicates whether passive mode is enabled.
     *
     * @return bool True when passive mode is used.
     */
    public function isPassive() : bool
    {
        return $this->passive ;
    }

    /**
     * Indicates whether the transport is secured (FTPS).
     *
     * @return bool True when TLS is used.
     */
    public function isSecure() : bool
    {
        return $this->secure ;
    }

    /**
     * Closes the connection on destruction.
     */
    public function __destruct()
    {
        $this->disconnect() ;
    }

    // ----------- Protected

    /**
     * Pauses between two connection attempts. Isolated so tests can override it
     * without changing the retry logic.
     *
     * @param int $seconds The number of seconds to wait.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    protected function waitBeforeRetry( int $seconds ) : void
    {
        sleep( $seconds ) ;
    }

    // ----------- Private

    /**
     * Asserts that the client holds an open connection.
     *
     * Shared by the file and directory operations.
     *
     * @return void
     *
     * @throws FtpTransferException When no connection is open.
     */
    private function ensureConnected() : void
    {
        if ( !$this->isConnected() )
        {
            throw new FtpTransferException( 'No active FTP connection. Call connect() first.' ) ;
        }
    }

    /**
     * The transport driver.
     * @var FtpDriverInterface
     */
    private FtpDriverInterface $driver ;

    /**
     * The login credentials.
     * @var FtpCredentials
     */
    private FtpCredentials $credentials ;

    /**
     * Whether the client is currently connected.
     * @var bool
     */
    private bool $connected = false ;

    /**
     * The remote host.
     * @var string
     */
    private string $host = '' ;

    /**
     * Whether passive mode is enabled.
     * @var bool
     */
    private bool $passive = true ;

    /**
     * The remote base directory entered right after login (empty to stay in the default directory).
     * @var string
     */
    private string $root = '' ;

    /**
     * The control-channel port.
     * @var int
     */
    private int $port = 21 ;

    /**
     * Whether the transport is secured (FTPS).
     * @var bool
     */
    private bool $secure = false ;

    /**
     * The transport security mode.
     * @var string
     */
    private string $security = FtpSecurity::NONE ;

    /**
     * The connection timeout, in seconds.
     * @var int
     */
    private int $timeout = 90 ;

    /**
     * The default transfer mode applied to file operations.
     * @var string
     */
    private string $transferMode = FtpTransferMode::BINARY ;

    /**
     * Performs a single connect + authenticate sequence.
     *
     * @return void
     *
     * @throws FtpConnectionException     When the connection cannot be opened.
     * @throws FtpAuthenticationException When the credentials are rejected.
     */
    private function establish() : void
    {
        if ( !$this->driver->connect( $this->host , $this->port , $this->timeout , $this->secure ) )
        {
            throw new FtpConnectionException( sprintf( 'Unable to connect to FTP server "%s:%d".' , $this->host , $this->port ) ) ;
        }

        $this->driver->setOption( FtpConnectionOption::TIMEOUT_SEC , $this->timeout ) ;

        if ( !$this->driver->login( $this->credentials->username , $this->credentials->password ) )
        {
            $this->driver->disconnect() ;
            throw new FtpAuthenticationException( sprintf( 'FTP authentication failed for user "%s" on "%s".' , $this->credentials->username , $this->host ) ) ;
        }

        $this->driver->setPassive( $this->passive ) ;

        $this->connected = true ;

        $this->info( sprintf( 'Connected to FTP server "%s:%d" (secure: %s, passive: %s).' ,
            $this->host , $this->port , $this->secure ? 'yes' : 'no' , $this->passive ? 'yes' : 'no' ) ) ;
    }

    /**
     * Changes into the configured root directory, if any, right after connecting.
     *
     * @return void
     *
     * @throws FtpConnectionException When the root directory cannot be entered.
     */
    private function applyRoot() : void
    {
        if ( $this->root === '' )
        {
            return ;
        }

        if ( !$this->driver->changeDirectory( $this->root ) )
        {
            $this->disconnect() ;
            throw new FtpConnectionException( sprintf( 'Unable to change to the root directory "%s".' , $this->root ) ) ;
        }
    }

    /**
     * Reads the configuration array into the connection state.
     *
     * @param array $init The configuration keyed by {@see Ftp} constants.
     *
     * @return void
     */
    private function initializeConfig( array $init ) : void
    {
        $this->host       = (string) ( $init[ Ftp::HOST ] ?? '' ) ;
        $this->port       = (int) ( $init[ Ftp::PORT ] ?? 21 ) ;
        $this->timeout    = (int) ( $init[ Ftp::TIMEOUT ] ?? 90 ) ;
        $this->passive    = (bool) ( $init[ Ftp::PASSIVE ] ?? true ) ;
        $this->maxRetries = max( 1 , (int) ( $init[ Ftp::MAX_RETRIES ] ?? 3 ) ) ;

        $security       = $init[ Ftp::SECURITY ] ?? FtpSecurity::getDefault() ;
        $this->security = is_string( $security ) ? $security : FtpSecurity::NONE ;
        $this->secure   = FtpSecurity::isSecure( $this->security ) ;

        $mode               = $init[ Ftp::TRANSFER_MODE ] ?? FtpTransferMode::getDefault() ;
        $this->transferMode = is_string( $mode ) ? $mode : FtpTransferMode::BINARY ;

        $this->root = (string) ( $init[ Ftp::ROOT ] ?? '' ) ;

        $this->credentials = new FtpCredentials
        (
            (string) ( $init[ Ftp::USERNAME ] ?? '' ) ,
            (string) ( $init[ Ftp::PASSWORD ] ?? '' )
        ) ;
    }
}
