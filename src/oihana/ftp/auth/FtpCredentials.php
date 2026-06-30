<?php

namespace oihana\ftp\auth ;

use SodiumException;
use Stringable ;

/**
 * An immutable-by-intent holder for the FTP login credentials.
 *
 * The password is treated as a secret: it is never rendered by {@see __toString()}
 * (only the user name is) and it is wiped from memory by {@see clear()} — invoked
 * automatically on destruction — using `sodium_memzero()` when available, with a
 * NUL-overwrite fallback otherwise.
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
     * The login user name.
     * @var string
     */
    public string $username ;

    /**
     * The login password.
     * @var string
     */
    public string $password ;

    /**
     * Wipes the password from memory and resets it to an empty string.
     *
     * Idempotent: calling it more than once is harmless.
     *
     * @return void
     *
     * @throws SodiumException
     */
    public function clear() : void
    {
        if ( $this->password !== '' )
        {
            if ( function_exists( 'sodium_memzero' ) )
            {
                sodium_memzero( $this->password ) ;
            }
            // ext-sodium is loaded, so the fallback overwrite branch is never taken under test.
            // @codeCoverageIgnoreStart
            else
            {
                $this->password = str_repeat( "\0" , strlen( $this->password ) ) ;
            }
            // @codeCoverageIgnoreEnd
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
     * Returns the user name. The password is never exposed this way.
     *
     * @return string The login user name.
     */
    public function __toString() : string
    {
        return $this->username ;
    }
}
