# Security

## Scope

`oihana/php-ftp` covers **FTP** and **explicit FTPS** (TLS on the control channel, via
`ftp_ssl_connect`). **SFTP** (SSH) is not supported.

## ⚠️ FTPS limits under `ext-ftp`

Worth knowing — and worth documenting for your users:

- `ext-ftp` **does not allow configuring server certificate verification**.
- **Data-channel** encryption is partial / not guaranteed depending on the server.

In practice, FTPS via `ext-ftp` **mostly protects credentials in transit**, but is not a
bank-grade confidentiality guarantee. For strong confidentiality, prefer **SFTP** (planned via a
future driver) or combine with **at-rest encryption**.

## At-rest encryption

`uploadEncrypted()` / `downloadDecrypted()` encrypt the content **before** upload (authenticated
AES-256-GCM, via `oihana/php-files`). The file stays encrypted on the server, independent of the
transport. See [Encrypted transfers](encryption.md).

## Secret handling

- The password is held by `FtpCredentials` and **wiped from memory** on destruction
  (`sodium_memzero`, with a NUL-overwrite fallback).
- Credentials and passphrases are **never logged** nor included in exception messages.

## Best practices

- Prefer **FTPS** over plain FTP whenever the server allows it.
- Use **passive mode** behind NAT/firewall (enabled by default).
- Set a reasonable **timeout** (default: 90 s).
- Validate/sanitize **remote paths** coming from user input before any operation.
