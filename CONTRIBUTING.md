# Contributing

Thanks for helping improve **oihana/php-ftp**.

## Requirements

- **PHP 8.4+** with the **`ext-ftp`** extension.
- **Composer**
- **Xdebug** or **PCOV** — only needed to measure test coverage (see below).

## Setup

```shell
git clone https://github.com/BcommeBois/oihana-php-ftp.git
cd oihana-php-ftp
composer install
```

## Tests & coverage

```shell
composer test            # run the unit suite (PHPUnit, strict mode)
composer coverage        # suite + coverage report (text + Clover + HTML under build/coverage/)
composer coverage:md     # regenerate build/coverage/COVERAGE.md, a readable Markdown summary
```

The suite runs in **strict mode**: warnings, risky tests (no assertion), and
skipped tests all fail the run. A test that checks nothing protects nothing.

Coverage output lives under `build/coverage/` and is **gitignored** — it is a
snapshot that goes stale at the next commit, so we regenerate it on demand
rather than committing it. `composer coverage:md` also keeps a small local
trend log (`build/coverage/history.json`) so each run shows the delta since the
previous one.

## Testing philosophy

- **The transport is mocked.** Every `ftp_*` call goes through `FtpDriverInterface`,
  so the entire client logic (connection lifecycle, transfers, listing, retries)
  is tested against an in-memory fake driver. No real FTP server is needed to
  reach full coverage of the library's behaviour.
- `NativeFtpDriver` is the thin I/O boundary over `ext-ftp`. Its pass-through
  methods are annotated `@codeCoverageIgnore`: they cannot run without a live
  server and carry no logic worth asserting.
- An optional integration suite (`@group integration`) can exercise
  `NativeFtpDriver` against a real server (e.g. a Docker `vsftpd`/`pure-ftpd`).
  It is excluded from the default CI run.
- Coverage measures which lines ran, **not** which behaviours are verified —
  100% coverage is not zero bugs. When you discover a surprising behaviour,
  **freeze it in a test** before changing it.
