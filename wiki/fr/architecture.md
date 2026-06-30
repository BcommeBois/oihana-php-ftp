# Architecture

## La façade `FtpClient`

`FtpClient` n'a aucune logique propre : il **compose des traits**, chacun responsable d'un domaine.

```php
class FtpClient
{
    use FtpConnectionTrait,  // cycle de vie, reprises, racine, journalisation
        FtpCryptoTrait,      // transferts chiffrés
        FtpDirectoryTrait,   // répertoires et listing
        FtpFileTrait;        // transferts de fichiers
}
```

C'est le même patron que `MagentoClient` dans `oihana/php-magento`.

## Le seam de transport : `FtpDriverInterface`

Le point central de la conception. Le client ne touche **jamais** `ext-ftp` directement : toute
opération passe par `FtpDriverInterface`.

```
FtpClient ── appelle ──▶ FtpDriverInterface ──▶ NativeFtpDriver ──▶ ext-ftp
                                            └──▶ (en test) pilote en mémoire
```

Deux bénéfices :

1. **Testabilité** — un pilote factice en mémoire pilote toute la logique du client (connexion,
   transferts, listing, reprises), ce qui permet une couverture à 100 % **sans serveur réel**.
2. **Extensibilité** — `NativeFtpDriver` enveloppe `ext-ftp` ; un futur `Sftp…Driver` pourrait
   implémenter la même interface **sans modifier le client**. C'est l'équivalent du `handler`
   injectable de `MagentoClient`.

`NativeFtpDriver` est volontairement réduit à de fins appels `ftp_*` ; comme il ne peut s'exécuter
sans serveur, il est exclu de la couverture (`@codeCoverageIgnore`).

## Le mixin `HasFtpClientTrait`

Pour injecter un `FtpClient` dans vos propres services, à la manière `oihana` :

```php
use oihana\ftp\traits\HasFtpClientTrait;

class MonService
{
    use HasFtpClientTrait;
}

$service = new MonService();
$service->initializeFtp( [ 'ftp' => 'ftpService' ], $container ); // résolution PSR-11
$service->assertFtp();
$service->ftp->connect();
```

`initializeFtp()` accepte soit une instance de `FtpClient`, soit un nom de service résolu depuis un
conteneur PSR-11. `assertFtp()` garantit que le client est prêt.

## Et le SFTP ?

Non inclus aujourd'hui — mais le **point d'extension** (l'interface, déjà nécessaire pour les
tests) est en place. On a prévu la *couture*, pas la fonctionnalité : ajouter SFTP consistera à
écrire un pilote `phpseclib` implémentant `FtpDriverInterface`, sans rien changer à `FtpClient`.
