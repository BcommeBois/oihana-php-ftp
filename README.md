# Oihana PHP - FTP

![Oihana PHP FTP](https://raw.githubusercontent.com/BcommeBois/oihana-php-ftp/main/assets/images/oihana-php-ftp-logo-inline-512x160.png)

A modern, strongly-typed **FTP / FTPS** client for PHP 8.4+, built in the spirit of the `oihana/php-*` libraries.

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-ftp.svg?style=flat-square)](https://packagist.org/packages/oihana/php-ftp)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-ftp.svg?style=flat-square)](https://packagist.org/packages/oihana/php-ftp)  
[![License](https://img.shields.io/packagist/l/oihana/php-ftp.svg?style=flat-square)](LICENSE)

## 🚀 Features

- 🔌 **FTP & FTPS** (explicit TLS) over the native `ext-ftp` extension — zero runtime dependencies.
- 🧩 **Trait-composed client** with a clean, mockable **driver layer** (`FtpDriverInterface`) — SFTP-ready by design.
- 🔑 **Authentication & security** as first-class concerns: credentials are wiped from memory, secrets are never logged.
- 📁 **File & directory operations**: upload, download, append, delete, rename, listing (MLSD/raw), `chmod`, recursive helpers.
- 🧱 **Constant enums everywhere** — no magic strings for config keys, transfer modes or connection options.
- 🪵 **PSR-3 logging** (optional) with retry + exponential backoff on transient failures.
- 🔐 Seamless reuse of **`oihana/php-files`** path/MIME helpers and OpenSSL file encryption.
- 🧪 Full unit-test coverage through the mockable driver — no live server required.

💡 Designed to be lightweight, testable, and compatible with any PHP 8.4+ project.

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)** with the `ext-ftp` extension.

Install via [Composer](https://getcomposer.org):
```bash
composer require oihana/php-ftp
```

## ⚡ Usage

```php
use oihana\ftp\FtpClient ;
use oihana\ftp\enums\Ftp ;
use oihana\ftp\enums\FtpSecurity ;

$client = new FtpClient
([
    Ftp::HOST     => 'ftp.example.org' ,
    Ftp::USERNAME => 'alice' ,
    Ftp::PASSWORD => 's3cr3t' ,
    Ftp::SECURITY => FtpSecurity::SSL , // FTPS over TLS
    Ftp::ROOT     => '/public' ,        // chdir right after login
]) ;

$client->connect() ;

// Transfers
$client->upload( '/local/report.pdf' , 'report.pdf' ) ;
$client->download( 'archive.tar.gz' , '/local/archive.tar.gz' ) ;

// Encrypted transfer (OpenSSL, via oihana/php-files)
$client->uploadEncrypted( '/local/secret.txt' , 'secret.enc' , 'pass-phrase' ) ;

// Directory listing (structured, MLSD with ls -l fallback)
foreach ( $client->listFiles( '/public' ) as $file )
{
    echo $file->name , $file->isDirectory() ? '/' : '' , PHP_EOL ;
}

$client->disconnect() ;
```

> All transport calls go through `FtpDriverInterface`. Inject your own driver as the
> second constructor argument to unit-test against an in-memory fake, or to plug in an
> alternative transport later.

## ✅ Tests & coverage

Run the full unit-test suite (PHPUnit, strict mode):
```bash
composer test
```

Measure coverage (requires Xdebug or PCOV):
```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

All transport calls go through `FtpDriverInterface`, so the suite reaches full
coverage by mocking an in-memory driver — no real FTP server is needed. See
[CONTRIBUTING.md](CONTRIBUTING.md) for the testing philosophy.

## 🧾 License

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr

## 🛠️ Generate the Documentation

We use [phpDocumentor](https://phpdoc.org/) to generate the API documentation into the `./docs` folder.

```bash
composer doc
```

## 🔗 Related packages

- `oihana/php-files` – file, path and OpenSSL helpers reused by this library: `https://github.com/BcommeBois/oihana-php-files`
- `oihana/php-core` – core helpers and utilities: `https://github.com/BcommeBois/oihana-php-core`
- `oihana/php-reflect` – reflection and hydration utilities: `https://github.com/BcommeBois/oihana-php-reflect`
- `oihana/php-enums` – strongly-typed constant enumerations for PHP: `https://github.com/BcommeBois/oihana-php-enums`
