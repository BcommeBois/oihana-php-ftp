# Tests & coverage

```bash
composer test            # PHPUnit suite (strict mode)
composer coverage        # coverage (text + Clover + HTML under build/coverage/)
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

The suite runs in **strict mode**: warnings, risky tests (no assertion) and skipped tests all fail
the run.

## The driver is mocked

Every `ftp_*` call goes through `FtpDriverInterface`. The suite injects an **in-memory driver**
(`FakeFtpDriver`) that simulates success, failures, retries and listings. As a result **all of the
client's logic** is tested — connection, transfers, directories, listing parser — **without a live
server**, and source coverage reaches 100%.

`NativeFtpDriver` is the thin I/O boundary over `ext-ftp`: its pass-through methods are annotated
`@codeCoverageIgnore`, because they carry no logic and cannot run without a server.

## E2E tests (optional)

E2E is **not required** for coverage. For extra confidence, you can exercise `NativeFtpDriver`
against a real server (e.g. a Docker `vsftpd` / `pure-ftpd` container) in a suite marked
`@group integration`, **excluded from the fast CI run**.

## Philosophy

- Coverage measures **executed lines**, not **verified behaviours** — 100% ≠ zero bugs.
- When you discover a surprising behaviour, **freeze it in a test** before changing it.
