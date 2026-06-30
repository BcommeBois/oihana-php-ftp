# Installation

## Requirements

- **PHP 8.4+**
- The **`ext-ftp`** extension (required) and **`ext-openssl`** (for FTPS and encrypted transfers).
- **`ext-sodium`** (suggested) — for reliable in-memory password wiping.

## Via Composer

```bash
composer require oihana/php-ftp
```

## Dependencies

| Package | Role |
|---|---|
| `oihana/php-files` | path/MIME helpers and `OpenSSLFileEncryption` reused by the client. |
| `oihana/php-core` | core utilities. |
| `oihana/php-reflect` | `ConstantsTrait` for the enumerations. |
| `oihana/php-enums` | cross-cutting enumerations. |
| `oihana/php-logging` | `LoggerTrait` (PSR-3 logging). |

## Verification

```php
var_dump( extension_loaded( 'ftp' ) ); // must print true
```
