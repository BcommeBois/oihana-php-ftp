# Tests & couverture

```bash
composer test            # suite PHPUnit (mode strict)
composer coverage        # couverture (texte + Clover + HTML sous build/coverage/)
composer coverage:md     # résumé Markdown lisible (build/coverage/COVERAGE.md)
```

La suite tourne en **mode strict** : avertissements, tests *risky* (sans assertion) et tests
ignorés font échouer le *run*.

## Le pilote est mocké

Tout appel `ftp_*` passe par `FtpDriverInterface`. La suite injecte un **pilote en mémoire**
(`FakeFtpDriver`) qui simule succès, échecs, reprises et listings. Ainsi **toute la logique du
client** est testée — connexion, transferts, répertoires, parser de listing — **sans serveur
réel**, et la couverture des sources atteint 100 %.

`NativeFtpDriver` est la fine frontière d'E/S au-dessus de `ext-ftp` : ses méthodes de passe-plat
sont annotées `@codeCoverageIgnore`, car elles ne portent aucune logique et ne peuvent s'exécuter
sans serveur.

## Tests E2E (optionnels)

L'E2E n'est **pas nécessaire** à la couverture. Pour gagner en confiance, on peut exercer
`NativeFtpDriver` contre un vrai serveur (par ex. un conteneur Docker `vsftpd` / `pure-ftpd`) dans
une suite marquée `@group integration`, **exclue du *run* CI rapide**.

## Philosophie

- La couverture mesure les **lignes exécutées**, pas les **comportements vérifiés** — 100 % ≠ zéro
  bug.
- Quand vous découvrez un comportement surprenant, **figez-le dans un test** avant de le modifier.
