# Architecture

## The `FtpClient` façade

`FtpClient` carries no logic of its own: it **composes traits**, each responsible for one domain.

```php
class FtpClient
{
    use FtpConnectionTrait,  // lifecycle, retries, root, logging
        FtpCryptoTrait,      // encrypted transfers
        FtpDirectoryTrait,   // directories and listing
        FtpFileTrait;        // file transfers
}
```

This is the same pattern as `MagentoClient` in `oihana/php-magento`.

## The transport seam: `FtpDriverInterface`

The heart of the design. The client **never** touches `ext-ftp` directly: every operation goes
through `FtpDriverInterface`.

```
FtpClient ── calls ──▶ FtpDriverInterface ──▶ NativeFtpDriver ──▶ ext-ftp
                                          └──▶ (in tests) in-memory driver
```

Two benefits:

1. **Testability** — an in-memory fake driver drives all of the client's logic (connection,
   transfers, listing, retries), making 100% coverage reachable **without a live server**.
2. **Extensibility** — `NativeFtpDriver` wraps `ext-ftp`; a future `Sftp…Driver` could implement
   the same interface **without modifying the client**. This is the equivalent of the injectable
   `handler` of `MagentoClient`.

`NativeFtpDriver` is deliberately reduced to thin `ftp_*` calls; since it cannot run without a
server, it is excluded from coverage (`@codeCoverageIgnore`).

## The `HasFtpClientTrait` mixin

To inject an `FtpClient` into your own services, the `oihana` way:

```php
use oihana\ftp\traits\HasFtpClientTrait;

class MyService
{
    use HasFtpClientTrait;
}

$service = new MyService();
$service->initializeFtp( [ 'ftp' => 'ftpService' ], $container ); // PSR-11 resolution
$service->assertFtp();
$service->ftp->connect();
```

`initializeFtp()` accepts either an `FtpClient` instance or a service name resolved from a PSR-11
container. `assertFtp()` guarantees the client is ready.

## What about SFTP?

Not included today — but the **extension point** (the interface, already needed for tests) is in
place. We provided the *seam*, not the feature: adding SFTP will mean writing a `phpseclib` driver
implementing `FtpDriverInterface`, with no change to `FtpClient`.
