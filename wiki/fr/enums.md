# Énumérations

Toutes les énumérations sont des **classes de constantes** (avec `ConstantsTrait`), pas des `enum`
natifs — la convention `oihana`. Elles bannissent les *magic strings*.

## `Ftp` — clés de configuration

`HOST`, `PORT`, `USERNAME`, `PASSWORD`, `SECURITY`, `PASSIVE`, `TIMEOUT`, `ROOT`, `TRANSFER_MODE`,
`MAX_RETRIES`. Leurs valeurs correspondent aux noms de propriétés de `FtpOptions`, ce qui rend
tableau et objet interchangeables.

## `FtpSecurity` — mode de transport

| Constante | Valeur | Description |
|---|---|---|
| `NONE` | `none` | FTP en clair. |
| `SSL` | `ssl` | FTPS sur TLS (`ftp_ssl_connect`). |

Helpers : `getDefault()` (→ `NONE`), `isSecure( $mode )`.

## `FtpTransferMode` — mode de transfert

| Constante | Valeur | Mappe vers |
|---|---|---|
| `ASCII` | `ascii` | `FTP_ASCII` |
| `BINARY` | `binary` | `FTP_BINARY` |

Helpers : `getDefault()` (→ `BINARY`), `toResource( $mode )`.

## `FtpConnectionOption` — options `ftp_set_option`

`TIMEOUT_SEC`, `AUTOSEEK`, `USE_PASV_ADDRESS` — alias typés des constantes `FTP_*` correspondantes.

## `FtpFileType` — type d'entrée de listing

| Constante | Valeur |
|---|---|
| `FILE` | `file` |
| `DIRECTORY` | `dir` |
| `LINK` | `link` |
| `UNKNOWN` | `unknown` |

Helpers : `fromMlsd( $type )` (mappe les faits MLSD, `cdir`/`pdir` → répertoire) et
`fromUnixChar( $char )` (premier caractère d'une ligne `ls -l`).
