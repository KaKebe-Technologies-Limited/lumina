# Testing

The project has no Composer and no PHPUnit. Tests are plain PHP scripts in `bin/selftest-*.php`, each printing one line per check
(`ok` / `FAIL`) and exiting non-zero if anything failed. `bin/test.php` is the one command that runs them.

## Run them

Needs the local setup from the README (XAMPP, `.env`, `php bin/setup.php`). Commands from the project root:

```bash
/c/xampp/php/php.exe bin/test.php --group=quick     # 1 second, no database, no web server
/c/xampp/php/php.exe bin/test.php --group=smoke     # about 3 minutes: the money and sign-up paths. RUN THIS BEFORE EVERY UPLOAD
/c/xampp/php/php.exe bin/test.php                   # every stand-alone test (about 70 files)
/c/xampp/php/php.exe bin/test.php --list            # what exists, and which group each test is in
/c/xampp/php/php.exe bin/test.php payments shop     # every test whose name contains "payments" or "shop"
/c/xampp/php/php.exe bin/test.php --stop            # stop at the first failing file
/c/xampp/php/php.exe bin/test.php --verbose         # also print the output of passing tests
/c/xampp/php/php.exe bin/test.php --group=full      # bin/selftest.php: the older suites in one process (20+ minutes)
```

One test alone: `/c/xampp/php/php.exe bin/selftest-payment-options.php` (prints `ALL OK` / `ALL PASSED` / `ALL CHECKS PASSED`).

What the environment must provide:

| Need | Used by |
|---|---|
| `APP_ENV=local` in `.env` | **every test**: they refuse to run anywhere else, because they write temporary rows and clean up |
| MySQL running, schema installed | everything except `core-units` |
| Apache serving `http://localhost/lumina/` | tests that make real HTTP requests (admin pages, shop, payments dashboard, forms). Most skip those parts when the site does not answer |
| `-d extension=gd` | picture checks in a few suites (they skip or are marked in their header comment) |

**The tests use your local database.** They create rows with obvious names (`zz-*@example.test`, references `ZZ...`, titles `zz-selftest-*`)
and delete them at the end, and they restore any setting they change. A test that crashes half-way can leave such rows or a changed setting
behind: delete them by hand (`WHERE email LIKE 'zz-%'`) and re-run. Never point `.env` at the live database.
Mail is written to files in `storage/mail/` (`MAIL_DRIVER=file`), and the Daraza / Flutterwave / Pesapal / social gateways are replaced by fakes or
local mock servers (`bin/dev/mock-*.php`): **no test sends a real e-mail or moves real money.**

## Critical flows and the test that guards each

| Flow | What is checked | Test |
|---|---|---|
| Money arithmetic | parsing typed amounts, UGX has no cents, rounding, overflow, gateway number formats | `core-units` |
| Safe HTML and links | scripts, `onclick`, `javascript:` and `data:` links removed; path traversal refused | `core-units` |
| Form validation | required / email / in / between / url / nullable / accepted / confirmed; arrays refused | `core-units`, `forms` |
| Mobile-money payment (Daraza) | request, approve, decline, timeout, lost answer, "check again", confirm by hand, close, payouts, amount limits | `daraza`, `daraza-api` |
| Which ways to pay are shown | switches, ranking, "mobile money only", per-purpose switches | `payment-options` |
| Payments dashboard and audit trail | ledger union, totals per category, filters, Resolve menu, remind, delete, thank-you e-mail once | `payments-dashboard` |
| Card / online payment (Pesapal) | order, IPN, status check, reversal, amount and currency mismatch | `pesapal` |
| Shop | cart, stock race, orders, status and payment state machines, 60% pay-on-delivery deposit, e-mails | `shop`, `shop-laf` |
| Donations and project giving | "Give now", amount cap, thanks page | `fund` |
| Membership | application, fees, payment, renewal, card, staff review | `membership-pay`, `membership-card-designer`, `membership-card-fixes`, `full` (membership, card) |
| Academy | sign-up, e-mail confirmation, enrolling, quizzes, fees, certificates, challenges | `academy*` |
| Admin access | roles and abilities, password reset | `full` (roles), `admin-reset` |
| Newsletter, calendar, meetings, social | queue, sending, iCal, scheduling, posting | `newsletter*`, `calendar`, `meetings`, `social*` |

`bin/test.php` lists the smoke group in its `FLOWS` constant. If you add a feature that touches money or sign-up, add its test there.

## Write a new test

1. Copy the header of `bin/selftest-core-units.php` (it shows the stand-alone preamble: bootstrap, refuse unless `APP_ENV=local`, the
   `check($name, $bool, $extra)` helper, and the `ALL PASSED` footer). Name the file `bin/selftest-<feature>.php`; the runner finds it by itself.
2. One `check()` per behaviour, named as a sentence a non-developer can read: `'delete: a PAID payment is never deleted'`.
   Put the data that explains a failure in the third argument (`json_encode($result)`).
3. Test through the service layer (`app/services/...`) first; add real HTTP requests only for what the service cannot show (routes, permissions, CSRF, redirects).
4. Use throw-away data (`zz-<feature>-*@example.test`), delete it at the end **and** at the start (a crashed earlier run), and restore every setting with `Settings::setMany`.
5. A bug fix gets a regression check that fails without the fix.
6. Do not make a check depend on content the owner may edit (a page title, an opportunity slug): that gives false failures on a real database.
   (Example fixed in this repo: the forms sitemap check searched for the word "submissions" anywhere and failed when an imported opportunity
   had it in its title; it now looks at URL path segments only.)

Some `bin/selftest-*.php` files (for example `roles`, `membership`, `membership-card`, `*-http`, `social-flow`) have no bootstrap of their own: they are
`require`-d by `bin/selftest.php` and share its `check()` helper. `bin/test.php` skips them automatically (it only runs files that load `bootstrap.php`);
run them through `--group=full`.

## Known behaviours worth knowing before you add a form rule

* `Validator`: `accepted` on its own lets an **empty** value through. Always write `required|accepted` (every form already does).
* Money is stored and passed as **minor units** (`App\Services\Shop\Money`, scale 100: UGX 35,000 = 3500000). Never use floats for amounts.
