<?php

namespace oihana\ftp ;

use oihana\ftp\traits\FtpConnectionTrait ;
use oihana\ftp\traits\FtpFileTrait ;

/**
 * A modern, strongly-typed FTP / FTPS client.
 *
 * The client is a thin façade that composes its behaviour from focused traits.
 * It never touches `ext-ftp` directly: every transport call goes through an
 * injected {@see FtpDriverInterface}, which keeps the whole surface testable and
 * leaves room for alternative transports (e.g. a future SFTP driver).
 *
 * ```php
 * use oihana\ftp\FtpClient ;
 * use oihana\ftp\enums\Ftp ;
 * use oihana\ftp\enums\FtpSecurity ;
 *
 * $client = new FtpClient
 * ([
 *     Ftp::HOST     => 'ftp.example.org' ,
 *     Ftp::USERNAME => 'alice' ,
 *     Ftp::PASSWORD => 's3cr3t' ,
 *     Ftp::SECURITY => FtpSecurity::SSL ,
 * ]) ;
 *
 * $client->connect() ;
 * // ... transfers ...
 * $client->disconnect() ;
 * ```
 *
 * @package oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpClient
{
    use FtpConnectionTrait ,
        FtpFileTrait ;
}
