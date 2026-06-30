# Installation

## Prérequis

- **PHP 8.4+**
- L'extension **`ext-ftp`** (requise) et **`ext-openssl`** (pour FTPS et les transferts chiffrés).
- **`ext-sodium`** (suggérée) — pour un effacement fiable du mot de passe en mémoire.

## Via Composer

```bash
composer require oihana/php-ftp
```

## Dépendances

| Package | Rôle |
|---|---|
| `oihana/php-files` | helpers de chemins, MIME, et `OpenSSLFileEncryption` réutilisés par le client. |
| `oihana/php-core` | utilitaires de base. |
| `oihana/php-reflect` | `ConstantsTrait` pour les énumérations. |
| `oihana/php-enums` | énumérations transverses. |
| `oihana/php-logging` | `LoggerTrait` (journalisation PSR-3). |

## Vérification

```php
var_dump( extension_loaded( 'ftp' ) ); // doit afficher true
```
