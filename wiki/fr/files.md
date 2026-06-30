# Transferts de fichiers

Fournis par `FtpFileTrait`. Toutes ces méthodes exigent une connexion ouverte (sinon
`FtpTransferException`) et lèvent une `FtpTransferException` en cas d'échec.

| Méthode | Description |
|---|---|
| `download( $remote, $local, $mode = null, $createDir = true )` | Télécharge un fichier ; crée au besoin le répertoire local parent. |
| `upload( $local, $remote, $mode = null )` | Envoie un fichier local. |
| `append( $local, $remote, $mode = null )` | Ajoute le contenu d'un fichier local à un fichier distant. |
| `delete( $remote )` | Supprime un fichier distant. |
| `rename( $from, $to )` | Renomme ou déplace. |
| `size( $remote ) : int` | Taille en octets (lève si indéterminable). |
| `lastModified( $remote ) : int` | Horodatage Unix de dernière modification. |
| `chmod( $remote, $permissions )` | Change les permissions (valeur octale). |
| `exists( $remote ) : bool` | Indique si le fichier existe (taille lisible). |

```php
$client->connect();

$client->upload( '/local/rapport.pdf', 'rapport.pdf' );
$client->download( 'archive.tar.gz', '/local/archive.tar.gz' );

if ( $client->exists( 'rapport.pdf' ) )
{
    echo $client->size( 'rapport.pdf' ), ' octets', PHP_EOL;
}

$client->rename( 'rapport.pdf', 'archives/rapport-2026.pdf' );
$client->chmod( 'archives/rapport-2026.pdf', 0644 );
```

## Mode de transfert

Par défaut, les transferts sont en **binaire** (`FtpTransferMode::BINARY`) — le choix sûr. Le mode
peut être configuré globalement (`Ftp::TRANSFER_MODE`) ou par appel :

```php
use oihana\ftp\enums\FtpTransferMode;

$client->download( 'lisez-moi.txt', '/local/lisez-moi.txt', FtpTransferMode::ASCII );
```

## Création du répertoire local

`download()` crée par défaut le répertoire local parent manquant (en réutilisant
`oihana\files\makeDirectory`). Passez `false` en quatrième argument pour le désactiver.

## Chaînage

Toutes les méthodes mutatrices retournent `$this`, donc on peut chaîner :

```php
$client->upload( 'a.txt', 'a.txt' )
       ->upload( 'b.txt', 'b.txt' )
       ->chmod( 'b.txt', 0600 );
```
