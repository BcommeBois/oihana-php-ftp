# Connexion

Le client s'instancie avec un tableau de configuration (clés de l'énumération [`Ftp`](enums.md))
ou un objet `FtpOptions`, puis on appelle `connect()`.

```php
use oihana\ftp\FtpClient;
use oihana\ftp\enums\Ftp;
use oihana\ftp\enums\FtpSecurity;

$client = new FtpClient
([
    Ftp::HOST        => 'ftp.example.org',
    Ftp::PORT        => 21,
    Ftp::USERNAME    => 'alice',
    Ftp::PASSWORD    => 's3cr3t',
    Ftp::SECURITY    => FtpSecurity::SSL,
    Ftp::PASSIVE     => true,
    Ftp::TIMEOUT     => 90,
    Ftp::ROOT        => '/public',
    Ftp::MAX_RETRIES => 3,
]);

$client->connect();
// ...
$client->disconnect();
```

## Cycle de vie

- `connect()` ouvre la connexion, applique le timeout, s'authentifie, règle le mode passif, puis
  entre dans le répertoire racine (`Ftp::ROOT`) s'il est défini. **Idempotent** : un second appel
  ne fait rien si la connexion est déjà ouverte. Retourne `$this`.
- `disconnect()` ferme la connexion si elle est ouverte. Appelé automatiquement à la destruction de
  l'objet.
- `isConnected()` indique l'état courant.

## FTPS

`Ftp::SECURITY => FtpSecurity::SSL` ouvre une connexion TLS via `ftp_ssl_connect`. Voir
[Sécurité](security.md) pour les limites de FTPS sous `ext-ftp`.

## Mode passif

Activé par défaut (`Ftp::PASSIVE => true`) — recommandé derrière un NAT ou un pare-feu.

## Reprises sur incident

Une **erreur de connexion** (`FtpConnectionException`) est réessayée avec un *backoff exponentiel*
(2, 4, … secondes) jusqu'à `Ftp::MAX_RETRIES` tentatives. Une **erreur d'authentification**
(`FtpAuthenticationException`) est **terminale** : aucune reprise, car de mauvais identifiants ne
deviendront pas valides.

```php
use oihana\ftp\exceptions\FtpAuthenticationException;
use oihana\ftp\exceptions\FtpConnectionException;

try
{
    $client->connect();
}
catch ( FtpAuthenticationException $e ) { /* identifiants invalides */ }
catch ( FtpConnectionException $e )     { /* serveur injoignable après reprises */ }
```

## Configuration typée

Au lieu d'un tableau, on peut passer un objet `FtpOptions` (mêmes clés, mais typées) :

```php
use oihana\ftp\options\FtpOptions;

$options = new FtpOptions([ 'host' => 'ftp.example.org', 'username' => 'alice' ]);
$client  = new FtpClient( $options );
```

## Journalisation

Fournir un logger PSR-3 (clé `logger` dans la configuration, ou via un conteneur PSR-11) active la
trace des événements de connexion. Sans logger, le client reste silencieux.
