# E2E tests

Automated tests for the Planzer WooCommerce plugin. What they cover and why: `docs/testing-concept.md`.
They run on every pull request against `master` (`.github/workflows/tests.yml`), once with HPOS and once with legacy post storage.

## Run locally

Needs Docker and Node 20. Nothing else: the plugin build, WordPress, the SFTP server and the mail catcher run in containers.

```bash
cd tests/e2e
npm ci && npx playwright install chromium

bin/build-plugin.sh      # build the plugin like a release into .build/
bin/setup.sh             # start WordPress + WooCommerce (latest) on http://localhost:8190
npx playwright test      # all scenarios, about 5 minutes
```

Legacy post storage instead of HPOS:

```bash
docker compose down -v
PLANZER_E2E_HPOS=0 bin/setup.sh
PLANZER_E2E_HPOS=0 npx playwright test
```

After a code change: `bin/build-plugin.sh && docker compose restart wordpress`.

Useful while debugging:

| | |
|---|---|
| WordPress admin | http://localhost:8190/wp-admin (`admin` / `password`) |
| Mailpit (PDF mails) | http://localhost:8195 |
| CSV files sent to "Planzer" | `docker compose exec sftp ls /home/WooCommerce/Eingang` |
| WP-CLI | `docker compose exec -u www-data wordpress wp …` |
| One scenario | `npx playwright test -g S41` |
| Step through a test | `npx playwright test -g S41 --headed --debug` |
| Stop everything | `docker compose down -v` |

Ports can be changed with `PLANZER_E2E_PORT` and `PLANZER_E2E_MAILPIT_PORT`.

## How it is built

| Path | |
|---|---|
| `docker-compose.yml` | WordPress (PHP 8.2), MariaDB, Mailpit, `atmoz/sftp` as the Planzer SFTP server |
| `mu-plugins/planzer-test-hooks.php` | Test environment only: mail to Mailpit, stubs for the Planzer address API and the heartbeat, test clock |
| `fixtures/php/` | WP-CLI scripts: seed data, settings set A, creating orders, reset |
| `fixtures/golden/` | Reference CSVs, reviewed against PAKET Standard V17 |
| `lib/env.ts` | Helpers used by the specs (WP-CLI, SFTP, Mailpit, PDF checks) |
| `specs/` | One file per section of the concept; every test name starts with its scenario ID |

`specs/known-defects.spec.ts` and a few single tests describe the **correct** behaviour of known defects (concept §7) and are marked `test.fail()`. They run and must fail. When a defect is fixed, the test turns red: remove the marker and the test then guards the fix.
