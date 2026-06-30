# oihana/php-ftp — FTP/FTPS client for PHP

![Language](https://img.shields.io/badge/language-English-blue)

`oihana/php-ftp` is a PHP 8.4+ library providing a modern, strongly-typed **FTP and FTPS** client
built on the native `ext-ftp` extension. It follows the `oihana/php-*` architecture: a
**trait-composed façade**, **constant enumerations** to ban *magic strings*, **optional PSR-3
logging**, and — above all — an abstract **transport layer** (`FtpDriverInterface`) that makes the
whole library testable without a live server, and paves the way for a future SFTP driver.

![Oihana PHP FTP](https://raw.githubusercontent.com/BcommeBois/oihana-php-ftp/main/assets/images/oihana-php-ftp-logo-inline-512x160.png)

## Who is this documentation for

PHP developers who want to:

- **connect** to an FTP or FTPS server with retry handling and passive mode;
- **transfer** files (download, upload, append) in ASCII or binary mode;
- **manage** remote directories and **list** them in a structured way (MLSD with `ls -l` fallback);
- **encrypt** transfers with OpenSSL (reusing `oihana/php-files`);
- **inject** the client into their services via a PSR-11 container.

## Quick start

```php
use oihana\ftp\FtpClient;
use oihana\ftp\enums\Ftp;
use oihana\ftp\enums\FtpSecurity;

$client = new FtpClient
([
    Ftp::HOST     => 'ftp.example.org',
    Ftp::USERNAME => 'alice',
    Ftp::PASSWORD => 's3cr3t',
    Ftp::SECURITY => FtpSecurity::SSL, // FTPS over TLS
]);

$client->connect();
$client->upload( '/local/report.pdf', 'report.pdf' );

foreach ( $client->listFiles( '.' ) as $file )
{
    echo $file->name, $file->isDirectory() ? '/' : '', PHP_EOL;
}

$client->disconnect();
```

## Table of contents

### Getting started — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — what the library does, the *oihana* philosophy, and the dedicated-package choice.
- [Installation](getting-started/installation.md) — PHP 8.4+ requirements, the `ext-ftp` extension, `composer require`.

### Guides

- [Connection](connection.md) — `connect`/`disconnect`, FTPS, passive mode, timeouts, root directory, retries.
- [File transfers](files.md) — `download`, `upload`, `append`, `delete`, `rename`, `size`, `lastModified`, `chmod`, `exists`.
- [Directories & listing](directories.md) — creation, navigation, `listNames`/`rawList`/`listFiles`, the `FtpFile` model.
- [Encrypted transfers](encryption.md) — `uploadEncrypted` / `downloadDecrypted` via OpenSSL.

### Cross-cutting

- [Architecture](architecture.md) — the façade, the `FtpDriverInterface` seam, the traits, the mixin and SFTP-readiness.
- [Enumerations](enums.md) — `Ftp`, `FtpSecurity`, `FtpTransferMode`, `FtpConnectionOption`, `FtpFileType`.
- [Exceptions](exceptions.md) — `FtpException` and its subclasses; terminal vs. retryable errors.
- [Security](security.md) — scope, FTPS limits under `ext-ftp`, best practices.
- [Tests & coverage](testing.md) — PHPUnit suite, driver mocking, optional E2E tests.

## Source code

The code lives under [`src/oihana/ftp/`](../../src/oihana/ftp/).

## Also see

- [Packagist `oihana/php-ftp`](https://packagist.org/packages/oihana/php-ftp) — package page.
- [API reference (phpDocumentor)](https://bcommebois.github.io/oihana-php-ftp) — generated reference.
