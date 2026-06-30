# Connection

The client is created from a configuration array (keys of the [`Ftp`](enums.md) enumeration) or an
`FtpOptions` object, then `connect()` is called.

```php
use oihana\ftp\FtpClient;
use oihana\ftp\enums\Ftp;
use oihana\ftp\enums\FtpSecurity;

$client = new FtpClient
([
    Ftp::HOST        => 'ftp.example.org',
    Ftp::PORT        => 21,
    Ftp::USERNAME    => 'alice',
    Ftp::PASSWORD    => 's3cr3t',
    Ftp::SECURITY    => FtpSecurity::SSL,
    Ftp::PASSIVE     => true,
    Ftp::TIMEOUT     => 90,
    Ftp::ROOT        => '/public',
    Ftp::MAX_RETRIES => 3,
]);

$client->connect();
// ...
$client->disconnect();
```

## Lifecycle

- `connect()` opens the connection, applies the timeout, authenticates, sets passive mode, then
  changes into the root directory (`Ftp::ROOT`) if defined. **Idempotent**: a second call is a
  no-op when already connected. Returns `$this`.
- `disconnect()` closes the connection if open. Called automatically on object destruction.
- `isConnected()` reports the current state.

## FTPS

`Ftp::SECURITY => FtpSecurity::SSL` opens a TLS connection via `ftp_ssl_connect`. See
[Security](security.md) for the limits of FTPS under `ext-ftp`.

## Passive mode

Enabled by default (`Ftp::PASSIVE => true`) — recommended behind NAT or a firewall.

## Retries

A **connection error** (`FtpConnectionException`) is retried with *exponential backoff* (2, 4, …
seconds) up to `Ftp::MAX_RETRIES` attempts. An **authentication error**
(`FtpAuthenticationException`) is **terminal**: no retry, because bad credentials will not become
valid.

```php
use oihana\ftp\exceptions\FtpAuthenticationException;
use oihana\ftp\exceptions\FtpConnectionException;

try
{
    $client->connect();
}
catch ( FtpAuthenticationException $e ) { /* invalid credentials */ }
catch ( FtpConnectionException $e )     { /* server unreachable after retries */ }
```

## Typed configuration

Instead of an array, you may pass an `FtpOptions` object (same keys, typed):

```php
use oihana\ftp\options\FtpOptions;

$options = new FtpOptions([ 'host' => 'ftp.example.org', 'username' => 'alice' ]);
$client  = new FtpClient( $options );
```

## Logging

Providing a PSR-3 logger (a `logger` configuration key, or via a PSR-11 container) enables tracing
of connection events. Without a logger, the client stays silent.
