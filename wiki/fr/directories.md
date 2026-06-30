# Répertoires et listing

Fournis par `FtpDirectoryTrait`. Comme les transferts, ces méthodes exigent une connexion ouverte
et lèvent une `FtpTransferException` en cas d'échec.

## Opérations

| Méthode | Description |
|---|---|
| `createDirectory( $dir )` | Crée un répertoire. |
| `createDirectories( $path )` | Crée le chemin et chaque parent manquant (façon `mkdir -p`, *best effort*). |
| `removeDirectory( $dir )` | Supprime un répertoire. |
| `changeDirectory( $dir )` | Change de répertoire courant. |
| `parentDirectory()` | Remonte au répertoire parent. |
| `currentDirectory() : string` | Retourne le répertoire courant. |
| `listNames( $dir = '.' ) : string[]` | Liste brute des noms. |
| `rawList( $dir = '.', $recursive = false ) : string[]` | Lignes brutes du serveur. |
| `listFiles( $dir = '.' ) : FtpFile[]` | Liste **structurée** (voir ci-dessous). |

```php
$client->createDirectories( '/public/incoming/2026' );
$client->changeDirectory( '/public/incoming/2026' );
echo $client->currentDirectory(), PHP_EOL;
```

## Listing structuré

`listFiles()` privilégie la commande **MLSD** (`ftp_mlsd`), qui renvoie des métadonnées
structurées. Si le serveur ne la prend pas en charge, le client **se rabat** automatiquement sur
l'analyse de la sortie brute `ls -l` via `RawListParser`.

Chaque entrée est un objet [`FtpFile`](#le-modèle-ftpfile) :

```php
foreach ( $client->listFiles( '/public' ) as $file )
{
    printf(
        "%-30s %s %10d %s\n",
        $file->name,
        $file->isDirectory() ? 'DIR ' : 'FILE',
        $file->size,
        $file->modifiedTime ? date( 'Y-m-d H:i', $file->modifiedTime ) : '—'
    );
}
```

## Le modèle `FtpFile`

`oihana\ftp\schema\FtpFile` décrit une entrée de listing :

| Propriété | Type | Description |
|---|---|---|
| `name` | `string` | Nom de l'entrée. |
| `type` | `string` | Une constante de [`FtpFileType`](enums.md) (`file`, `dir`, `link`, `unknown`). |
| `size` | `int` | Taille en octets. |
| `modifiedTime` | `?int` | Horodatage Unix (depuis MLSD ; `null` en repli `ls -l`). |
| `permissions` | `?string` | Chaîne de permissions ou mode. |
| `owner`, `group` | `?string` | Propriétaire / groupe si connus. |
| `target` | `?string` | Cible d'un lien symbolique. |

Prédicats : `isFile()`, `isDirectory()`, `isLink()`.

> Le `RawListParser` ne gère que le format Unix `ls -l` et n'interprète pas la date (ambiguë selon
> la locale et l'année). Préférez MLSD lorsque le serveur le permet.
