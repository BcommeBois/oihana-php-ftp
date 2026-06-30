<?php

namespace oihana\ftp\exceptions ;

/**
 * Thrown when the server rejects the supplied credentials.
 *
 * This is a *terminal* failure: bad credentials will not become valid by retrying,
 * so the client fails fast without further attempts.
 *
 * @package oihana\ftp\exceptions
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpAuthenticationException extends FtpException
{
}
