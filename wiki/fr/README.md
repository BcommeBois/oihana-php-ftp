# oihana/php-ftp — Client FTP/FTPS pour PHP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`oihana/php-ftp` est une bibliothèque PHP 8.4+ qui fournit un **client FTP et FTPS** moderne et
fortement typé, bâti sur l'extension native `ext-ftp`. Il suit l'architecture des bibliothèques
`oihana/php-*` : une **façade composée de traits**, des **énumérations de constantes** pour bannir
les *magic strings*, un **journal PSR-3 optionnel**, et surtout une **couche de transport
abstraite** (`FtpDriverInterface`) qui rend l'ensemble testable sans serveur réel — et ouvre la
voie à un futur pilote SFTP.

![Oihana PHP FTP](https://raw.githubusercontent.com/BcommeBois/oihana-php-ftp/main/assets/images/oihana-php-ftp-logo-inline-512x160.png)

## À qui s'adresse cette documentation

Aux développeurs PHP qui veulent :

- se **connecter** à un serveur FTP ou FTPS avec gestion des reprises et du mode passif ;
- **transférer** des fichiers (téléchargement, envoi, ajout) en mode ASCII ou binaire ;
- **gérer** les répertoires distants et **lister** leur contenu de manière structurée (MLSD avec repli `ls -l`) ;
- **chiffrer** les transferts via OpenSSL (réutilisation de `oihana/php-files`) ;
- **injecter** le client dans leurs services via un conteneur PSR-11.

## Démarrage rapide

```php
use oihana\ftp\FtpClient;
use oihana\ftp\enums\Ftp;
use oihana\ftp\enums\FtpSecurity;

$client = new FtpClient
([
    Ftp::HOST     => 'ftp.example.org',
    Ftp::USERNAME => 'alice',
    Ftp::PASSWORD => 's3cr3t',
    Ftp::SECURITY => FtpSecurity::SSL, // FTPS sur TLS
]);

$client->connect();
$client->upload( '/local/rapport.pdf', 'rapport.pdf' );

foreach ( $client->listFiles( '.' ) as $file )
{
    echo $file->name, $file->isDirectory() ? '/' : '', PHP_EOL;
}

$client->disconnect();
```

## Table des matières

### Démarrage — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — ce que fait la librairie, la philosophie *oihana* et le choix d'un package dédié.
- [Installation](getting-started/installation.md) — prérequis PHP 8.4+, extension `ext-ftp`, `composer require`.

### Guides

- [Connexion](connection.md) — `connect`/`disconnect`, FTPS, mode passif, timeouts, répertoire racine, reprises.
- [Transferts de fichiers](files.md) — `download`, `upload`, `append`, `delete`, `rename`, `size`, `lastModified`, `chmod`, `exists`.
- [Répertoires et listing](directories.md) — création, navigation, `listNames`/`rawList`/`listFiles`, modèle `FtpFile`.
- [Transferts chiffrés](encryption.md) — `uploadEncrypted` / `downloadDecrypted` via OpenSSL.

### Transverse

- [Architecture](architecture.md) — la façade, le seam `FtpDriverInterface`, les traits, le mixin et l'ouverture SFTP.
- [Énumérations](enums.md) — `Ftp`, `FtpSecurity`, `FtpTransferMode`, `FtpConnectionOption`, `FtpFileType`.
- [Exceptions](exceptions.md) — `FtpException` et ses sous-classes ; erreurs terminales vs *retryable*.
- [Sécurité](security.md) — périmètre, limites de FTPS via `ext-ftp`, bonnes pratiques.
- [Tests & couverture](testing.md) — suite PHPUnit, mock du pilote, tests E2E optionnels.

## Code source

Le code vit sous [`src/oihana/ftp/`](../../src/oihana/ftp/).

## Voir aussi

- [Packagist `oihana/php-ftp`](https://packagist.org/packages/oihana/php-ftp) — page du package.
- [Référence API (phpDocumentor)](https://bcommebois.github.io/oihana-php-ftp) — référence générée.
