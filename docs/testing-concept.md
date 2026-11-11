# Automated tests – concept (WooCommerce plugin)

Card: P22031 – AI Testing Implementation. Status: draft for review.

This document is the spec the tests are written from. Every scenario below has an ID; a test carries that ID in its name, so a failing check points back here.

## 1. Goal and scope

Replace the manual regression round before each release with an automated check on every pull request. The check must catch what broke in the past (see the fix commits in §8): orders not transmitted, wrong exclusion behaviour, wrong CSV fields, wrong pickup/delivery dates, missing order notes.

In scope: everything the plugin does inside WordPress, up to the CSV landing on an SFTP server and the PDF landing in a mailbox.

Out of scope (agreed with Ria, 2026-11-11):
- The real Planzer SFTP server and anything Planzer does with the CSV.
- Older WordPress/WooCommerce versions. Tests run against the latest releases only, and the plugin readme says so.
- The Planzer address API (T5) and the Webwirkung heartbeat: both are stubbed, not tested end to end.

## 2. How and where it runs

| | |
|---|---|
| Trigger | Every pull request against `master`, plus manual `workflow_dispatch` |
| Where | GitHub Actions, `.github/workflows/tests.yml`, `ubuntu-latest` |
| Gate | Required status check on `master`: a red run blocks the merge |
| Runtime target | Under 10 minutes |
| Artifacts on failure | Playwright report and trace, captured CSV files, Mailpit messages, `debug.log`, WooCommerce logs |

Steps of the workflow:
1. Build the plugin from the branch the way a release does: `composer install --no-dev`, `npm ci && npm run production` (the admin JS/CSS live in `dist/`, which is not committed).
2. Start the environment (§3) with `docker compose`.
3. Install WordPress + WooCommerce (latest), activate the plugin, load fixtures (§6) with WP-CLI.
4. Run the Playwright suite twice: once with HPOS on, once with legacy post storage (matrix job).
5. Upload artifacts.

The same `docker compose` setup runs locally (`npm run test:e2e`), so a developer can reproduce a red check without CI.

Release safety: the Buddy pipeline "Deploy new release to WordPress" commits only `build/wc-planzer-shipping/`, which `webpack.build.mix.js` fills with the main file, `readme*.txt`, `inc/`, `src/`, `vendor/`, `dist/` and `resources/{views,lang}`. `docs/`, `tests/` and `.github/` never reach SVN. Buddy runs `composer install` **without** `--no-dev` and ships `vendor/` as a whole, so all test tooling goes into `package.json` (Playwright), never into composer `require-dev`.

## 3. Test environment

Location: `tests/e2e/` (compose file, Playwright config, specs, fixtures).

| Service | Image | Purpose |
|---|---|---|
| `wordpress` | `wordpress:php8.2-apache` + WP-CLI | WordPress latest, WooCommerce latest, plugin mounted from the build |
| `db` | `mariadb:11` | Database |
| `mailpit` | `axllent/mailpit` | Catches every `wp_mail`, including the PDF attachment; REST API for assertions |
| `sftp` | `atmoz/sftp` | Stands in for Planzer. User `WooCommerce`, password = the constant in `src/SFTP/SFTP.php`. The plugin's `planzer_ftp_live_server_address` points at `sftp` |
| `playwright` | `mcr.microsoft.com/playwright` | Runs the specs |

How each output is captured, without changing plugin code:

| Output | Capture |
|---|---|
| CSV | Read from the `sftp` container volume, `Eingang/PAKET_*_WP.csv` |
| PDF | Mailpit message attachment |
| Emails | Mailpit API: recipients, subject |
| Order notes, meta, status | WP-CLI (`wp wc shop_order get`, `wp eval`) or the admin UI |
| QR counter | `wp option get planzer_qr_sequence_number` |

A must-use plugin loaded **only in the test environment** (`tests/e2e/mu-plugins/planzer-test-hooks.php`, never shipped):
- `phpmailer_init`: SMTP to Mailpit.
- `pre_http_request`: stubs the Planzer address API (valid/invalid by fixture) and the heartbeat, so CI never calls production services.

Clock: pickup/delivery dates use `new DateTimeImmutable('now')` (`src/CSV/DataLoader.php:162`). The implementation wraps it in a filter, `planzer/csv/now`, and the test mu-plugin sets it per date scenario. This is a one-line change and harmless in production (decided 2026-11-11, instead of libfaketime).

External dependency that stays: mPDF downloads the QR image from `api.qrserver.com` while rendering the PDF. If that becomes flaky in CI, stub it in a follow-up.

## 4. Processes

### P1 – Automatic transmission (status → processing)
Hook `woocommerce_order_status_changed`, `src/WooCommerce/Submodules/OrderStatus.php`. Active only while manual mode is off (`planzer_sender_enable_manual_orders` ≠ `yes`). Every transition into `processing` sends again; there is no "already sent" check.

Pipeline (shared with P2):
1. QR counter `planzer_qr_sequence_number` +1 (always, even if the order is then excluded).
2. Exclusion check (§4 P5). If excluded: blue order note with the reason, stop.
3. Test mode (`planzer_ftp_test_mode` ≠ `no`): tracking meta `TEST_<package number>`, PDF generated and emailed with `[TEST MODE]` in the subject, note "Test mode enabled - data not sent to Planzer. Demo delivery note generated and sent." **No CSV, no SFTP.**
4. Live mode: tracking meta `planzer_tracking_code`, PDF generated and emailed, CSV uploaded to `Eingang/PAKET_<order id padded to 20>_<time>_<rand>_WP.csv`, success note with reference `<order id>_<seq>`, package number and tracking link.
5. SFTP failure: red note "There was an error while sending data to Planzer…", WooCommerce log entry (source `wc-planzer-shipping`). Tracking meta and PDF email have already happened.

### P2 – Manual transmission (status "Transmit to Planzer")
Status `wc-planzer-transmit`. Always triggers the pipeline. It is listed in the status dropdown and the orders list only in manual mode.

### P3 – Bulk action "Transmit to Planzer"
`src/WooCommerce/Submodules/BulkAction.php`. Manual mode only, on both the legacy and the HPOS orders screen. Handles at most the first 5 selected orders:
- excluded → note, status unchanged;
- status not yet "Transmit to Planzer" → status changed, which runs P2;
- status already "Transmit to Planzer" → sent again inline.

Notice afterwards: "The orders with number … have proceeded to the planzer." (≤ 5), or the "maximum limit … is 5" warning (> 5).

### P4 – Order action "Generate Planzer note and create package"
Order edit screen, Actions dropdown, always visible. Same as the live pipeline, with no status or manual-mode check.

### P5 – Exclusions
`src/WooCommerce/Services/ExclusionService.php`. Checked in this order; the first match wins:
1. Products: excluded only if **all** order items are in `planzer_other_excluded_products`.
2. Shipping method: order shipping method title in `planzer_other_excluded_shipping`.
3. Shipping class: only for flat rate. Calculation type "per class": all classes excluded. Type "per order": the most expensive class decides.

`none` saved together with real values is ignored (fix cb791c7, c503050).

### P6 – Customer-facing tracking
"Track your package: Click here" on My Account › order and in customer emails when `planzer_tracking_code` exists.

### P7 – Settings
WooCommerce › Settings › Planzer, sections Sender, FTP, Notifications, Delivery, Other. Values feed the CSV, PDF and exclusions. The Other section loads products by AJAX search (fix eb2fff9: it used to load all products on every request).

## 5. Scenarios

Default fixture state unless the row says otherwise: settings set A (§6), live mode, automatic mode, label note, one recipient in `planzer_delivery_note_email`.

Expected-result shorthand:
- **sent** = CSV on SFTP matches the golden file, one email with a PDF, tracking meta set, success note, counter +1.
- **not sent** = no CSV, no email, a note with the stated reason, counter +1.

### Transmission
| ID | Scenario | Expected |
|---|---|---|
| S01 | Classic checkout, order goes to processing | sent |
| S02 | Same order goes processing → on-hold → processing | sent twice, two CSV files, counter +2 (documents current behaviour) |
| S03 | Test mode on, order to processing | no CSV; email subject has `[TEST MODE]`; meta `TEST_…`; test-mode note |
| S04 | Manual mode on, order to processing | nothing happens, no note |
| S05 | Manual mode on, status set to "Transmit to Planzer" on the order screen | sent |
| S06 | Manual mode off: "Transmit to Planzer" not in the status dropdown, no bulk action | UI check |
| S07 | Order action "Generate Planzer note…" | sent |
| S08 | SFTP container stopped, order to processing | red error note; WC log entry; meta and email exist (documents current behaviour) |
| S09 | `planzer_delivery_generate_note` = delivery note | PDF is A4 with an items table; refunded/excluded lines left out |
| S10 | Two recipients in `planzer_delivery_note_email` | two emails |

### Bulk
| ID | Scenario | Expected |
|---|---|---|
| S20 | Manual mode, 3 orders in processing, bulk "Transmit to Planzer" | all three in "Transmit to Planzer", 3 CSVs, success notice with the numbers |
| S21 | 7 orders selected | only the first 5 handled, limit warning |
| S22 | 3 orders, one excluded | excluded keeps its status and gets a note; others sent |
| S23 | Orders already in "Transmit to Planzer", bulk again | sent again inline |
| S24 | S20 on the HPOS screen and on the legacy screen | same result (matrix) |

### Exclusions
| ID | Scenario | Expected |
|---|---|---|
| S30 | All products excluded | not sent, products reason |
| S31 | One of two products excluded | sent; with a delivery note the excluded line is missing from the PDF |
| S32 | Excluded shipping method | not sent, shipping method reason |
| S33 | Flat rate, per class, all classes excluded | not sent, shipping class reason |
| S34 | Flat rate, per class, one class not excluded | sent |
| S35 | Flat rate, per order, most expensive class excluded | not sent |
| S36 | `none` saved together with products / methods / classes | exclusion still applies (regression cb791c7, c503050) |
| S37 | Only `none` saved | nothing excluded |

### CSV content
Compared against golden files in `tests/e2e/fixtures/golden/`, with the variable parts masked (filename timestamp and random part, sequence number, dates where the scenario is not about dates).

| ID | Scenario | Checked fields |
|---|---|---|
| S40 | Private customer (no company), settings set A, shipping address = billing | full lines A/P/O vs `golden/private.csv`; A1_41 empty, A1_40 = last name |
| S47 | Company customer (company in the address), otherwise as S40 | full lines A/P/O vs `golden/company.csv`; A1_41 = company |
| S41 | Different shipping address | A1_39–A1_47 from the shipping address; A1_51 and A1_54 empty |
| S42 | Street parsing: "Bahnhofstrasse 12a", "Rue du Lac", "Via 5 Maggio 3" | A1_46 / A1_47 |
| S43 | Notification toggles (sender email, receiver email, receiver SMS) yes/no | A1_22, A1_53, A1_55 |
| S44 | Deposit notice 1 / 2 / 3 | O1_1 `Depot` / A1_82 text / both empty |
| S45 | Chargeable delivery time BIS10 / BIS12 | A1_73 |
| S46 | Package number = `91` + customer no. (6) + branch (3) + sequence (9) | P1_8, tracking meta, order note |

### Dates (clock set per scenario, Europe/Zurich, cutoff 15:00)
| ID | Now | Saturday delivery | Pickup A1_34 | Delivery A1_67 |
|---|---|---|---|---|
| S50 | Wed 14:59 | no | Wed | Thu |
| S51 | Wed 15:00 | no | Thu | Fri |
| S52 | Fri 14:00 | no | Fri | Mon |
| S53 | Fri 14:00 | yes | Fri | Sat |
| S54 | Fri 15:00 | no | Mon | Tue |
| S55 | Sat 10:00 | no | Mon | Tue |
| S56 | Cutoff set to 12:00, Wed 13:00 | no | Thu | Fri |

### Customer side and settings
| ID | Scenario | Expected |
|---|---|---|
| S60 | Customer opens the order in My Account after S01 | "Track your package" link with the package number |
| S61 | Customer email after S01 | contains the tracking line |
| S70 | Save every settings section | values persist; required fields marked |
| S71 | Other section: product search finds a product by name; page loads without loading all products | AJAX search works |
| S72 | Activation with WooCommerce deactivated | admin notice, plugin deactivated |

## 6. Reference data

Loaded by `tests/e2e/fixtures/seed.sh` (WP-CLI), identical for every run:
- **Settings set A:** fictional sender (company, address, customer no. `123456`, branch `57`, department, account ID); notifications all `yes`; deposit notice 2 with a text; label note; recipient `notes@example.test`; pickup cutoff `15:00`.
- **Products:** 3 simple products (one of them for exclusion tests), 2 shipping classes with different flat-rate costs.
- **Shipping:** zone Switzerland with flat rate ("per class" and "per order" variants) and free shipping.
- **Customers:** one with the same billing and shipping address, one with a different shipping address, plus addresses for the S42 street parsing cases.
- **Golden CSV files:** two, `golden/private.csv` and `golden/company.csv`. Both are generated once from the current `master` and reviewed against the Planzer specification **PAKET Standard V17 (2021-01-26)** before they are committed. From then on they are the reference. Fields affected by a known defect (D3) are masked in the comparison and covered by a `test.fixme` instead, so the golden files never lock a bug in as correct.

The plugin does not set the receiver customer type (A1_37). Private and company receivers differ only in A1_41 (company), filled from the order address.

### Fields the plugin does not send

Compared with PAKET Standard V17. Fields marked "not used" in the specification itself (A1_24–A1_33, A1_58–A1_66, A1_84, A1_85) are left out of this list.

Deliberately not sent (business decision). The tests expect these empty:

| Field | Meaning |
|---|---|
| A1_42 | Receiver company addition |
| A1_50 | Receiver instructions |
| A1_52 | Receiver mobile number |
| A1_71 | Remarks |
| A1_77–A1_81 | Insurance value, cash on delivery, customs amount and currencies |
| P1_2–P1_5 | Length, width, height (cm), weight (kg). Mandatory in the specification |
| A1_37 / A1_38 | Receiver customer type (G/P) and salutation |
| File name | Specification: `PAKET…` with `.txt`. Plugin: `PAKET_<nr>_<time>_<rand>_WP.csv`, which Planzer accepts |

To be confirmed with Andrea. Until then the tests expect them empty:

| Field | Specification | Plugin |
|---|---|---|
| A1_35 / A1_36 | Pick-up time from / to | commented out (`DataLoader.php:83-84`) |

## 7. Known defects found while writing this

The tests do not lock these in as correct. Each one gets a test marked `test.fixme` with a reference here and becomes its own card:

| # | Defect | Where |
|---|---|---|
| D1 | Order action (P4) ignores test mode: a real CSV is uploaded and the tracking code has no `TEST_` prefix | `OrderAction.php:37-78` |
| D2 | Bulk in test mode returns after the first order already in "Transmit to Planzer"; the rest are skipped and no notice is shown | `BulkAction.php:97` |
| D3 | A1_40 falls back to the shipping **first** name instead of the billing last name | `DataLoader.php:87` |
| D4 | No idempotency: re-entering processing sends the order to Planzer again | `OrderStatus.php` |
| D5 | Bulk increments the counter twice per order when it changes the status (gaps in the sequence) | `BulkAction.php:74` + P2 |
| D6 | Tracking meta and PDF email happen before the SFTP upload, so a failed upload leaves a tracking code Planzer never received | `OrderStatus.php`, `OrderAction.php`, `BulkAction.php` |
| D7 | CSV values are not escaped: a `;` in an address shifts all following columns. PAKET V17 defines `"` as the quoting mark | `CSV.php` |
| D8 | Bulk success notice is shown even when orders were excluded or failed | `BulkAction.php:40-57` |
| D9 | SFTP user and password are hardcoded in the plugin source | `SFTP.php` |
| D10 | With deposit notice 2, the deposit text also goes into A1_76, which PAKET V17 defines as the deposit notice for the **pick-up** at the sender. The server has this mapping commented out | `DataLoader.php:108` |

## 8. Why these scenarios (past fixes)

| Fix | Covered by |
|---|---|
| 38846ce – orders missing after "Transmit to Planzer" (boot limited to admin) | S01, S05 via checkout and REST, not only admin |
| cb791c7, c503050 – `none` broke exclusions | S36, S37 |
| eb2fff9 – settings loaded all products | S71 |
| 3bdb7e2, 180ddc4 – HPOS bulk action and meta | S24, HPOS matrix |
| b38777c – `Eingang` folder missing | S01 on an empty SFTP home |
| f6037cf, c498e6c – date logic rewritten, weekend detection | S50–S56 |
| d34cc4b, fdfc3ec – exclusion service, titles vs IDs | S30–S35, S22 |
| 9399213 – test mode demo note | S03 |
| 24e23eb – sequence number init | S46, fresh install |

## 9. Open points

1. Andrea:
   - Pick-up time from/to (A1_35/A1_36, commented out in the code): deliberate or missing? (§6)
   - Deposit notice option 1 "Nur gegen Unterschrift" sends O1_1 = `Depot` on all three platforms. Is that the code agreed with Planzer for "signature only"? (S44)
2. Readme: state that only the latest WordPress/WooCommerce are supported (Ria, 2026-11-11). Add in the implementation PR.
3. Required check: Ria turns on branch protection on `master` once the workflow is merged.

Decided:
- Clock: `planzer/csv/now` filter (§3).
- Reference for CSV content: PAKET Standard V17 plus golden files generated from `master` (§6).
