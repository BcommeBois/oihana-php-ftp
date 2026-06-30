# Encrypted transfers

`FtpCryptoTrait` combines transfers with the OpenSSL file encryption provided by
`oihana\files\openssl\OpenSSLFileEncryption` (authenticated AES-256-GCM).

| Method | Description |
|---|---|
| `uploadEncrypted( $local, $remote, $passphrase )` | Encrypts the local file, then uploads the ciphertext. |
| `downloadDecrypted( $remote, $local, $passphrase )` | Downloads the ciphertext, then decrypts it locally. |

```php
$client->connect();

// Upload: local encryption → upload
$client->uploadEncrypted( '/local/secret.txt', 'secret.enc', 'pass-phrase' );

// Download: download → local decryption
$client->downloadDecrypted( 'secret.enc', '/local/secret.txt', 'pass-phrase' );
```

## How it works

- On upload: the file is encrypted to a **temporary file**, which is uploaded then deleted.
- On download: the remote file is downloaded into a **temporary file**, decrypted to the
  destination, then the temporary file is deleted.
- Since the payload is binary ciphertext, these transfers are **forced to binary mode**.

## Best practices

- The passphrase is never logged nor written to disk.
- Do not confuse this **at-rest** encryption (ciphertext stored on the server) with the
  **in-transit** encryption of FTPS — the two are complementary: see [Security](security.md).
