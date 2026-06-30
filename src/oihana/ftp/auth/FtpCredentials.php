<?php

namespace oihana\ftp\auth ;

use SodiumException;
use Stringable ;

/**
 * An immutable-by-intent holder for the FTP login credentials.
 *
 * The password is treated as a secret: it is never rendered by {@see __toString()}
 * (only the user name is) and it is wiped by {@see clear()} — invoked automatically on
 * destruction. When `ext-sodium` is available, `sodium_memzero()` performs a genuine
 * in-place memory wipe (the only reliable one in PHP userland); otherwise the property
 * is simply reset to an empty string.
 *
 * @package oihana\ftp\auth
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpCredentials implements Stringable
{
    /**
     * Creates a new credentials holder.
     *
     * @param string $username The login user name.
     * @param string $password The login password (kept secret).
     */
    public function __construct( string $username = '' , string $password = '' )
    {
        $this->username = $username ;
        $this->password = $password ;
    }

    /**
     * Wipes the password when the instance is destroyed.
     *
     * @throws SodiumException
     */
    public function __destruct()
    {
        $this->clear() ;
    }

    /**
     * The login password.
     * @var string
     */
    public string $password ;

    /**
     * The login user name.
     * @var string
     */
    public string $username ;

    /**
     * Returns the user name. The password is never exposed this way.
     *
     * @return string The login user name.
     */
    public function __toString() : string
    {
        return $this->username ;
    }

    /**
     * Wipes the password from memory and resets it to an empty string.
     *
     * Idempotent: calling it more than once is harmless. When `ext-sodium` is present,
     * the secret buffer is zeroed in place with `sodium_memzero()`; otherwise PHP cannot
     * reliably scrub the original buffer, so the property is just reset.
     *
     * @return void
     *
     * @throws SodiumException
     */
    public function clear() : void
    {
        if ( $this->password !== '' && function_exists( 'sodium_memzero' ) )
        {
            sodium_memzero( $this->password ) ;
        }

        $this->password = '' ;
    }

    /**
     * Indicates whether these credentials describe an anonymous login.
     *
     * @return bool True when the user name is empty or `anonymous` (case-insensitive).
     */
    public function isAnonymous() : bool
    {
        return $this->username === '' || strtolower( $this->username ) === 'anonymous' ;
    }
}
