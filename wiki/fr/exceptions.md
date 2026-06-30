# Exceptions

Toutes les erreurs du composant dérivent de `FtpException`, elle-même fille de `\Exception`.

```
FtpException
├── FtpConnectionException      (retryable)
├── FtpAuthenticationException  (terminale)
└── FtpTransferException        (opération sur connexion ouverte)
```

| Exception | Levée quand… | Reprise ? |
|---|---|---|
| `FtpConnectionException` | la connexion de contrôle ne peut être établie (ou le répertoire racine ne peut être atteint). | **Oui** — *backoff* exponentiel jusqu'à `Ftp::MAX_RETRIES`. |
| `FtpAuthenticationException` | le serveur rejette les identifiants. | **Non** — échec terminal immédiat. |
| `FtpTransferException` | un transfert / une opération de répertoire échoue, ou aucune connexion n'est ouverte. | s.o. |

```php
use oihana\ftp\exceptions\FtpAuthenticationException;
use oihana\ftp\exceptions\FtpConnectionException;
use oihana\ftp\exceptions\FtpException;
use oihana\ftp\exceptions\FtpTransferException;

try
{
    $client->connect();
    $client->download( 'data.bin', '/local/data.bin' );
}
catch ( FtpAuthenticationException $e ) { /* identifiants */ }
catch ( FtpConnectionException $e )     { /* injoignable */ }
catch ( FtpTransferException $e )       { /* transfert */ }
catch ( FtpException $e )               { /* filet de sécurité */ }
```

Attraper `FtpException` capture tout le composant en un seul `catch`.
