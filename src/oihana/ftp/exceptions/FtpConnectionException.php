<?php

namespace oihana\ftp\exceptions ;

/**
 * Thrown when the control connection to the FTP server cannot be established.
 *
 * This is the *retryable* failure: the client retries it with exponential backoff
 * up to its configured maximum before giving up.
 *
 * @package oihana\ftp\exceptions
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpConnectionException extends FtpException
{
}
