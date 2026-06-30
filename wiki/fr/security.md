# Sécurité

## Périmètre

`oihana/php-ftp` couvre **FTP** et **FTPS explicite** (TLS sur le canal de contrôle, via
`ftp_ssl_connect`). Le **SFTP** (SSH) n'est pas pris en charge.

## ⚠️ Limites de FTPS sous `ext-ftp`

À connaître et à documenter auprès de vos utilisateurs :

- `ext-ftp` **ne permet pas de configurer la vérification du certificat** serveur.
- Le chiffrement du **canal de données** est partiel / non garanti selon les serveurs.

En pratique, FTPS via `ext-ftp` **protège surtout les identifiants en transit**, mais ne constitue
pas une garantie de confidentialité de niveau bancaire. Pour une confidentialité forte, privilégiez
**SFTP** (prévu via un futur pilote) ou combinez avec le **chiffrement au repos**.

## Chiffrement au repos

`uploadEncrypted()` / `downloadDecrypted()` chiffrent le contenu **avant** l'envoi (AES-256-GCM
authentifié, via `oihana/php-files`). Le fichier reste chiffré sur le serveur, indépendamment du
transport. Voir [Transferts chiffrés](encryption.md).

## Gestion des secrets

- Le mot de passe est porté par `FtpCredentials` et **effacé de la mémoire** à la destruction
  (`sodium_memzero`, avec repli par écrasement NUL).
- Les identifiants et passphrases ne sont **jamais journalisés** ni inclus dans les messages
  d'exception.

## Bonnes pratiques

- Préférez **FTPS** à FTP en clair dès que le serveur le permet.
- Utilisez le **mode passif** derrière un NAT/pare-feu (activé par défaut).
- Définissez un **timeout** raisonnable (défaut : 90 s).
- Validez/neutralisez les **chemins distants** issus d'entrées utilisateur avant toute opération.
