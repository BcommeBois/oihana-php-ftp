# Introduction

`oihana/php-ftp` est un client **FTP / FTPS** pour PHP 8.4+, conçu dans l'esprit des bibliothèques
`oihana/php-*`.

## Pourquoi un package dédié

Contrairement à `oihana/php-files` — une boîte à outils de **fonctions sans état** — un client FTP
est **avec état** : il maintient une connexion, une session, un mode de transfert. C'est la même
nature qu'un client distant comme `oihana/php-magento`. Il a donc sa place dans un **package
séparé**, qui dépend de `oihana/php-files` pour réutiliser ses helpers (chemins, MIME, chiffrement
OpenSSL) sans les dupliquer.

## Principes de conception

- **Façade composée de traits** — `FtpClient` n'a pas de logique propre ; il assemble
  `FtpConnectionTrait`, `FtpFileTrait`, `FtpDirectoryTrait`, `FtpCryptoTrait`. C'est le patron de
  `MagentoClient`.
- **Seam de transport** — toutes les fonctions `ftp_*` passent par `FtpDriverInterface`. Le pilote
  de production `NativeFtpDriver` enveloppe `ext-ftp` ; en test, un pilote en mémoire est injecté.
  C'est ce qui rend la couverture à 100 % atteignable **sans serveur**, et ce qui permettra
  d'ajouter SFTP sans toucher au client.
- **Zéro *magic string*** — toute la configuration et tous les paramètres passent par des classes
  de constantes (`Ftp`, `FtpSecurity`, `FtpTransferMode`, …).
- **Journal PSR-3 optionnel** — le client journalise connexion, reprises et déconnexions si un
  logger est fourni ; sinon il reste silencieux.

## Périmètre

Pris en charge : **FTP** et **FTPS explicite** (TLS via `ftp_ssl_connect`). Le **SFTP** (SSH) n'est
pas inclus, mais l'architecture est prête à l'accueillir via un pilote dédié. Voir
[Sécurité](../security.md) pour les limites de FTPS sous `ext-ftp`.
