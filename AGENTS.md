# Agents

## Cursor Cloud specific instructions

This is a PHP Composer package (Symfony Bundle) — not a standalone application. No databases, caches, or external services required.

### Quick reference

All dev commands are Composer scripts defined in `composer.json`:

| Command | Purpose |
|---|---|
| `composer run dev-checks` | Full suite: validate + phpstan + phpcs + phpunit |
| `composer run phpunit` | PHPUnit tests only (no coverage) |
| `composer run phpstan` | Static analysis (max level) |
| `composer run code-style` | PHP CodeSniffer lint |
| `composer run code-style-fix` | Auto-fix coding standard violations |

See `CONTRIBUTING.md` for full contribution guidelines.

### Gotchas

- The `phpcs.xml.dist` uses `ignore_errors_on_exit=1`, so `composer run code-style` returns exit code 0 even with errors. Check output text for `ERROR` lines.
- PHPUnit config (`phpunit.xml.dist`) uses some deprecated attributes for PHPUnit 10 — the deprecation warnings are expected and harmless.
- The repo's `docker-compose.yaml` targets PHP 8.0 (outdated vs the 8.1+ requirement). Use a local PHP 8.1+ install instead of the Docker setup.
