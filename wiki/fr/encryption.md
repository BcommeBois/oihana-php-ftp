# Transferts chiffrés

`FtpCryptoTrait` combine les transferts avec le chiffrement de fichiers OpenSSL fourni par
`oihana\files\openssl\OpenSSLFileEncryption` (AES-256-GCM authentifié).

| Méthode | Description |
|---|---|
| `uploadEncrypted( $local, $remote, $passphrase )` | Chiffre le fichier local, puis envoie le chiffré. |
| `downloadDecrypted( $remote, $local, $passphrase )` | Télécharge le chiffré, puis le déchiffre localement. |

```php
$client->connect();

// Envoi : chiffrement local → upload
$client->uploadEncrypted( '/local/secret.txt', 'secret.enc', 'pass-phrase' );

// Réception : download → déchiffrement local
$client->downloadDecrypted( 'secret.enc', '/local/secret.txt', 'pass-phrase' );
```

## Fonctionnement

- À l'envoi : le fichier est chiffré vers un **fichier temporaire**, lequel est envoyé puis
  supprimé.
- À la réception : le fichier distant est téléchargé dans un **fichier temporaire**, déchiffré vers
  la destination, puis le temporaire est supprimé.
- La charge utile étant du chiffré binaire, ces transferts sont **forcés en mode binaire**.

## Bonnes pratiques

- La passphrase n'est jamais journalisée ni écrite sur disque.
- Ne confondez pas ce chiffrement **au repos** (fichier chiffré côté serveur) avec le chiffrement
  **en transit** de FTPS — les deux sont complémentaires : voir [Sécurité](security.md).
