# Directories & listing

Provided by `FtpDirectoryTrait`. Like transfers, these methods require an open connection and raise
an `FtpTransferException` on failure.

## Operations

| Method | Description |
|---|---|
| `createDirectory( $dir )` | Creates a directory. |
| `createDirectories( $path )` | Creates the path and every missing parent (`mkdir -p` style, best effort). |
| `removeDirectory( $dir )` | Removes a directory. |
| `changeDirectory( $dir )` | Changes the current directory. |
| `parentDirectory()` | Moves up to the parent directory. |
| `currentDirectory() : string` | Returns the current directory. |
| `listNames( $dir = '.' ) : string[]` | Raw list of names. |
| `rawList( $dir = '.', $recursive = false ) : string[]` | Raw server lines. |
| `listFiles( $dir = '.' ) : FtpFile[]` | **Structured** listing (see below). |

```php
$client->createDirectories( '/public/incoming/2026' );
$client->changeDirectory( '/public/incoming/2026' );
echo $client->currentDirectory(), PHP_EOL;
```

## Structured listing

`listFiles()` prefers the **MLSD** command (`ftp_mlsd`), which returns structured metadata. If the
server does not support it, the client automatically **falls back** to parsing the raw `ls -l`
output via `RawListParser`.

Each entry is an [`FtpFile`](#the-ftpfile-model) object:

```php
foreach ( $client->listFiles( '/public' ) as $file )
{
    printf(
        "%-30s %s %10d %s\n",
        $file->name,
        $file->isDirectory() ? 'DIR ' : 'FILE',
        $file->size,
        $file->modifiedTime ? date( 'Y-m-d H:i', $file->modifiedTime ) : '—'
    );
}
```

## The `FtpFile` model

`oihana\ftp\schema\FtpFile` describes a listing entry:

| Property | Type | Description |
|---|---|---|
| `name` | `string` | Entry name. |
| `type` | `string` | An [`FtpFileType`](enums.md) constant (`file`, `dir`, `link`, `unknown`). |
| `size` | `int` | Size in bytes. |
| `modifiedTime` | `?int` | Unix timestamp (from MLSD; `null` on `ls -l` fallback). |
| `permissions` | `?string` | Permission string or mode. |
| `owner`, `group` | `?string` | Owner / group when known. |
| `target` | `?string` | Symbolic-link target. |

Predicates: `isFile()`, `isDirectory()`, `isLink()`.

> `RawListParser` only handles the Unix `ls -l` format and does not interpret the date (ambiguous
> across locales and year boundaries). Prefer MLSD when the server supports it.
