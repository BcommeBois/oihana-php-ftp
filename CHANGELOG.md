# Oihana PHP FTP OpenSource library - Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.0] - 2026-06-30

First public release: a modern, strongly-typed FTP/FTPS client built on the native
`ext-ftp` extension, mirroring the `oihana/php-*` architecture (trait-composed client,
constant enumerations, optional PSR-3 logging) with a mockable transport layer that
makes the whole surface testable without a live server (100% line coverage).

### Added

- **Project scaffolding** — Composer manifest (`oihana/php-ftp`, PHP 8.4+, `ext-ftp`),
  PHPUnit 13 strict configuration, phpDocumentor configuration, GitHub Actions
  workflows for CI (tests) and documentation (GitHub Pages), and the portable
  `tools/clover-to-markdown.php` coverage reporter shared across the
  `oihana/php-*` libraries.
- **Connection core** — the `oihana\ftp\FtpClient` façade with FTP/FTPS connect,
  authentication and clean teardown, retrying transient connection failures with
  exponential backoff (terminal vs. retryable error handling) and optional PSR-3
  logging.
  - `FtpDriverInterface` with the production `NativeFtpDriver` (a thin `ext-ftp`
    wrapper) — the transport seam that makes the whole client testable without a
    live server and leaves room for a future SFTP driver.
  - `oihana\ftp\options\FtpOptions`, a typed configuration object interchangeable
    with a plain `Ftp`-keyed array.
  - `oihana\ftp\auth\FtpCredentials`, a credentials holder that wipes the password
    from memory on destruction.
  - Enumerations `Ftp`, `FtpSecurity` and `FtpConnectionOption`, and the exception
    hierarchy `FtpException` › `FtpConnectionException` / `FtpAuthenticationException`
    / `FtpTransferException`.
- **File operations** — `FtpFileTrait` adds single-file transfers to the client:
  `download()` (creates the local parent directory on demand, reusing
  `oihana\files\makeDirectory`), `upload()`, `append()`, `delete()`, `rename()`,
  `size()`, `lastModified()`, `chmod()` and `exists()`. Each raises an
  `FtpTransferException` on failure and on a missing connection.
  - `FtpTransferMode` enumeration (ASCII / binary) mapped to the `ext-ftp` resource
    constants, with a configurable per-client default (`Ftp::TRANSFER_MODE`) and a
    per-call override.
- **Directory operations & listing** — `FtpDirectoryTrait` adds `createDirectory()`,
  `createDirectories()` (recursive, `mkdir -p` style), `removeDirectory()`,
  `changeDirectory()`, `parentDirectory()`, `currentDirectory()`, `listNames()`,
  `rawList()` and `listFiles()`. `listFiles()` prefers the structured MLSD listing
  and transparently falls back to parsing raw `ls -l` output.
  - `oihana\ftp\schema\FtpFile`, a typed listing entry (name, type, size, modified
    time, permissions, owner, group, link target) hydrated from MLSD facts.
  - `oihana\ftp\utils\RawListParser`, a Unix long-listing parser, and the
    `FtpFileType` enumeration mapping MLSD facts and `ls -l` type characters.
  - The client now changes into the configured remote base directory (`Ftp::ROOT`)
    right after login.
- **Client mixin & secure transfers** — `HasFtpClientTrait`, a mixin to hold and
  resolve an `FtpClient` from a PSR-11 container (the `oihana` injection convention),
  and `FtpCryptoTrait` with `uploadEncrypted()` / `downloadDecrypted()`, combining
  transfers with `oihana\files\openssl\OpenSSLFileEncryption` (encrypt-then-upload,
  download-then-decrypt, via a transient temporary file).
