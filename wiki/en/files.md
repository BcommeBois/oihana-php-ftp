# File transfers

Provided by `FtpFileTrait`. All these methods require an open connection (otherwise
`FtpTransferException`) and raise an `FtpTransferException` on failure.

| Method | Description |
|---|---|
| `download( $remote, $local, $mode = null, $createDir = true )` | Downloads a file; creates the local parent directory if needed. |
| `upload( $local, $remote, $mode = null )` | Uploads a local file. |
| `append( $local, $remote, $mode = null )` | Appends a local file's contents to a remote file. |
| `delete( $remote )` | Deletes a remote file. |
| `rename( $from, $to )` | Renames or moves. |
| `size( $remote ) : int` | Size in bytes (throws when undeterminable). |
| `lastModified( $remote ) : int` | Last-modified Unix timestamp. |
| `chmod( $remote, $permissions )` | Changes permissions (octal value). |
| `exists( $remote ) : bool` | Whether the file exists (readable size). |

```php
$client->connect();

$client->upload( '/local/report.pdf', 'report.pdf' );
$client->download( 'archive.tar.gz', '/local/archive.tar.gz' );

if ( $client->exists( 'report.pdf' ) )
{
    echo $client->size( 'report.pdf' ), ' bytes', PHP_EOL;
}

$client->rename( 'report.pdf', 'archives/report-2026.pdf' );
$client->chmod( 'archives/report-2026.pdf', 0644 );
```

## Transfer mode

By default, transfers are **binary** (`FtpTransferMode::BINARY`) — the safe choice. The mode can be
configured globally (`Ftp::TRANSFER_MODE`) or per call:

```php
use oihana\ftp\enums\FtpTransferMode;

$client->download( 'readme.txt', '/local/readme.txt', FtpTransferMode::ASCII );
```

## Local directory creation

`download()` creates the missing local parent directory by default (reusing
`oihana\files\makeDirectory`). Pass `false` as the fourth argument to disable it.

## Chaining

Every mutating method returns `$this`, so calls can be chained:

```php
$client->upload( 'a.txt', 'a.txt' )
       ->upload( 'b.txt', 'b.txt' )
       ->chmod( 'b.txt', 0600 );
```
