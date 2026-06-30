# Introduction

`oihana/php-ftp` is an **FTP / FTPS** client for PHP 8.4+, designed in the spirit of the
`oihana/php-*` libraries.

## Why a dedicated package

Unlike `oihana/php-files` — a toolkit of **stateless functions** — an FTP client is **stateful**:
it holds a connection, a session, a transfer mode. That is the same nature as a remote client like
`oihana/php-magento`. It therefore belongs in a **separate package**, which depends on
`oihana/php-files` to reuse its helpers (paths, MIME, OpenSSL encryption) without duplicating them.

## Design principles

- **Trait-composed façade** — `FtpClient` carries no logic of its own; it assembles
  `FtpConnectionTrait`, `FtpFileTrait`, `FtpDirectoryTrait`, `FtpCryptoTrait`. This is the
  `MagentoClient` pattern.
- **Transport seam** — every `ftp_*` function goes through `FtpDriverInterface`. The production
  driver `NativeFtpDriver` wraps `ext-ftp`; in tests an in-memory driver is injected. This is what
  makes 100% coverage reachable **without a server**, and what will let SFTP be added without
  touching the client.
- **Zero *magic strings*** — all configuration and parameters go through constant classes (`Ftp`,
  `FtpSecurity`, `FtpTransferMode`, …).
- **Optional PSR-3 logging** — the client logs connection, retries and disconnections when a logger
  is provided; otherwise it stays silent.

## Scope

Supported: **FTP** and **explicit FTPS** (TLS via `ftp_ssl_connect`). **SFTP** (SSH) is not
included, but the architecture is ready to host it through a dedicated driver. See
[Security](../security.md) for the limits of FTPS under `ext-ftp`.
