# Exceptions

Every error raised by the component derives from `FtpException`, itself a child of `\Exception`.

```
FtpException
├── FtpConnectionException      (retryable)
├── FtpAuthenticationException  (terminal)
└── FtpTransferException        (operation on an open connection)
```

| Exception | Raised when… | Retried? |
|---|---|---|
| `FtpConnectionException` | the control connection cannot be established (or the root directory cannot be entered). | **Yes** — exponential backoff up to `Ftp::MAX_RETRIES`. |
| `FtpAuthenticationException` | the server rejects the credentials. | **No** — immediate terminal failure. |
| `FtpTransferException` | a transfer / directory operation fails, or no connection is open. | n/a |

```php
use oihana\ftp\exceptions\FtpAuthenticationException;
use oihana\ftp\exceptions\FtpConnectionException;
use oihana\ftp\exceptions\FtpException;
use oihana\ftp\exceptions\FtpTransferException;

try
{
    $client->connect();
    $client->download( 'data.bin', '/local/data.bin' );
}
catch ( FtpAuthenticationException $e ) { /* credentials */ }
catch ( FtpConnectionException $e )     { /* unreachable */ }
catch ( FtpTransferException $e )       { /* transfer */ }
catch ( FtpException $e )               { /* safety net */ }
```

Catching `FtpException` captures the whole component in a single `catch`.
