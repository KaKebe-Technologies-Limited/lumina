# Working on this codebase

Hand-written PHP 8.2 MVC, MySQL/MariaDB, SCSS and vanilla JS. No Composer, no Node. Read `README.md` (setup) first, then `docs/ARCHITECTURE.md` (the contract).

## Five-minute orientation

| You want to... | Look at |
|---|---|
| Add a page | `app/config/pages/<slug>.php`, a controller in `app/controllers/Web/`, a view in `app/views/web/`, a route in `app/routes/web_*.php`, then `php bin/setup.php` |
| Add an admin screen | controller in `app/controllers/Admin/`, view in `app/views/admin/`, route in `app/routes/admin_*.php`, menu entry in `app/config/admin_nav/*.php`, an ability in `app/config/ability_catalog.php` |
| Add business logic | a class in `app/services/<Area>/` (controllers stay thin; services are what the tests call) |
| Change the database | append an SQL file to `database/updates/`, **and** update `database/schema.sql` so a fresh install matches. Tables a feature owns alone may be created on first use (see `PaymentEvents::install()`) |
| Change styles | edit `scss/`, then `python bin/build-scss.py` (full path to the Python that has `sass`). Never edit `assets/css/*.css` |
| Add a setting | `app/config/settings_schema.php`, read with `Settings::get()` / `setting()` |
| Add JavaScript | a file in `assets/js/`. The Content-Security-Policy forbids inline scripts (inline styles are allowed) |
| Touch money | `docs/PAYMENTS.md` first. Amounts are integers in minor units via `App\Services\Shop\Money`, never floats |

## Conventions that bite if you ignore them

* **Case-sensitive deploy**: production is Linux. Class `App\Services\Payments\PaymentEvents` lives at `app/services/Payments/PaymentEvents.php`; match the case exactly (see `docs/ARCHITECTURE.md`, autoload).
* **Database**: only `App\Core\Database` with prepared statements (`one`, `all`, `value`, `execute`, `insert`, `update`, `transaction`). No string-built SQL from user input.
* **Output**: `e()` for every value in a view; HTML from editors goes through `Sanitizer`. Forms use `csrf_field()`; state-changing routes are `POST`.
* **Validation**: `Validator` rules (`required|accepted`, not `accepted` alone, see `docs/TESTING.md`).
* **Transactions**: DDL (`CREATE TABLE`) inside a transaction commits it. Check `Database::tableExists()` and create tables before you begin.
* **Secrets**: only in `.env` on the server (`.env.example` lists the names). Never commit, log, print or paste a key, password or token. Never log a full phone number.
* **Audit**: admin actions that change data call `AuditLog::record()`; payments also write `PaymentEvents::log()`.
* **Permissions**: every admin controller action starts with `$this->authorize('<ability>')`.
* **Text**: plain language for visitors and staff; no jargon in screens.

## The change loop

1. Make the change in small steps; lint each PHP file: `php -l path/to/file.php`.
2. Rebuild CSS if you touched `scss/`; rebuild the asset manifest: `php bin/build-manifest.php`.
3. Run tests (see `docs/TESTING.md`): `php bin/test.php --group=quick` while you work, `php bin/test.php --group=smoke` before you finish, and the single test of the area you changed.
4. **Add or extend a test** for any new behaviour or fixed bug (`bin/selftest-<feature>.php`). A change to money, sign-up, permissions or e-mails without a test is not finished.
5. Update the matching file in `docs/` (or add one and link it from the README list).
6. Commit with a message that says what a person gets, not which file changed. One topic per commit. Push to `main`.
7. Deploy by uploading only the changed files to Hostinger `public_html` (or run the GitHub deploy action, `docs/DEPLOY.md`). Data (`uploads/`, `.env`, `storage/`) is never part of a deploy.

## Where to find things

* Every feature has a document in `docs/` (Shop, Membership, Academy, Newsletter, Calendar, Forms, Payments, ...). Start with the README list.
* `docs/ARCHITECTURE.md` section 5 documents the core classes; the bottom of the file is a generated API reference (`php bin/gen-api.php --write`).
* Local runbook: `docs/TESTING.md`. Production runbook: `docs/DEPLOY.md`, `docs/BACKUPS.md`.
