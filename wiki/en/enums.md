# Enumerations

All enumerations are **constant classes** (using `ConstantsTrait`), not native `enum`s — the
`oihana` convention. They ban *magic strings*.

## `Ftp` — configuration keys

`HOST`, `PORT`, `USERNAME`, `PASSWORD`, `SECURITY`, `PASSIVE`, `TIMEOUT`, `ROOT`, `TRANSFER_MODE`,
`MAX_RETRIES`. Their values match the property names of `FtpOptions`, making array and object
interchangeable.

## `FtpSecurity` — transport mode

| Constant | Value | Description |
|---|---|---|
| `NONE` | `none` | Plain FTP. |
| `SSL` | `ssl` | FTPS over TLS (`ftp_ssl_connect`). |

Helpers: `getDefault()` (→ `NONE`), `isSecure( $mode )`.

## `FtpTransferMode` — transfer mode

| Constant | Value | Maps to |
|---|---|---|
| `ASCII` | `ascii` | `FTP_ASCII` |
| `BINARY` | `binary` | `FTP_BINARY` |

Helpers: `getDefault()` (→ `BINARY`), `toResource( $mode )`.

## `FtpConnectionOption` — `ftp_set_option` options

`TIMEOUT_SEC`, `AUTOSEEK`, `USE_PASV_ADDRESS` — typed aliases of the matching `FTP_*` constants.

## `FtpFileType` — listing entry type

| Constant | Value |
|---|---|
| `FILE` | `file` |
| `DIRECTORY` | `dir` |
| `LINK` | `link` |
| `UNKNOWN` | `unknown` |

Helpers: `fromMlsd( $type )` (maps MLSD facts, `cdir`/`pdir` → directory) and
`fromUnixChar( $char )` (first character of an `ls -l` line).
