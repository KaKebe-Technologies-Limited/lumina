# Lumina Africa Foundation - website + admin CMS

Organisation website for **Lumina Africa Foundation** (Gulu City, Northern Uganda) with a full admin CMS.
PHP 8.2 - MySQL/MariaDB - SCSS - vanilla JS. No Composer, no Node.

* **Every public page is editable from the admin** (headings, paragraphs, buttons, pictures and alt text, section on/off, SEO text,
  menu entry), and the owner can create new pages. Nothing is hard-coded: with an empty database the site still renders the
  built-in wording; the admin only stores what the owner changes.
* **All organisation pages are part of the site and the menus**: About, Our Team, Partners, Reports & Downloads, Programs, Projects,
  Involvement, **Membership** (online registration for people and organisations who want to join), Careers, Activities, Events, Gallery, Blog, News (and `/press`), Impact Stories, Contact, Donate, Privacy.
  Pages whose content is not added yet show a friendly empty state instead of disappearing. The owner can hide any page from the
  menus under **Admin > Menus**, or unpublish it under **Admin > Pages**.

**The web root is the project root** (there is no `public/` folder): `index.php` is the front controller; static files live in
`assets/` (css, js, img) and uploaded images in `uploads/`. Everything else (`app/ bin/ database/ docs/ scss/ storage/ tools/ vendor/`,
`.env`, `*.sql`, `*.md`, ...) is blocked from the web by the root `.htaccess` and by a deny-all `.htaccess` inside each folder.
The base URL is computed at runtime, so the same files work at `http://localhost/lumina/`, at a domain root or in any sub-folder.

## Run locally (XAMPP on Windows)

1. Start Apache and MySQL in the XAMPP control panel. Put the project in `C:\xampp\htdocs\lumina`; the site is then at <http://localhost/lumina/>.
2. `copy .env.example .env` (the defaults suit XAMPP: user `root`, empty password, database `lumina`).
3. Create the database, tables, starter content and a development admin account:
   `C:\xampp\php\php.exe bin\setup.php` (add `--fresh` to drop and recreate the local database; `--no-seed` to skip `database/seed.sql`).
   The script is safe to run again: it never deletes content, and it adds a `pages` row for every page file in `app/config/pages/`.
4. Compile the stylesheets (libsass through Python's `sass` module; `assets/css/*.css` are generated, never edit them by hand):
   `python bin\build-scss.py` (use the Python that has the `sass` module; `--watch` rebuilds while you edit `scss/`).
5. Open the site <http://localhost/lumina/> and the admin <http://localhost/lumina/admin>.
   The development admin e-mail and password are in `storage/dev-admin-credentials.txt` (git-ignored; delete the file when done).
   More accounts: `php bin/create-admin.php --email=owner@example.org --name="Site Owner" --role=admin` (roles: `admin`, `editor`).

Local development accepts only dev-looking host names (localhost, 127.x, private-network addresses, single-label names, `*.test` / `*.local` / `*.localhost`)
while `APP_URL` is empty; anything else gets HTTP 400.

Other ways to run it: `php -S 127.0.0.1:8099 index.php` from the project root (built-in server; serves only `assets/` and `uploads/` as
static files, everything else goes through the app). Health check: `php bin/selftest.php` must print `ALL CHECKS PASSED`.

## Using the admin

Sign in at `/admin`. The sidebar has everything:

| Group | Screens |
|---|---|
| Site | Dashboard, **Pages** (edit the wording, pictures, buttons, SEO and menu entry of every page; create new pages), Menus, Impact figures, FAQs |
| Content | Programs, Projects, Posts (blog, news, press), Activities, Gallery, Jobs |
| Organisation | Team, Partners, Documents (reports), Events (with registrations), Impact stories |
| Inbox | Messages, Subscribers, Involvement requests, Job applications, Donations |
| Membership | **Members** (applications, approval, membership numbers, payments and printable receipts, renewals, CSV export), **Membership types & fees** (admin role only: this is where each fee is set) |
| Administration (admin role only) | Site settings, Users, Activity log |

* **Edit a page's text**: Admin > Pages > *Edit*. Each section is a card; change a field and Save. "Reset to default" restores the built-in wording.
  Sections with a switch can be hidden. Repeating lists (values, FAQ, steps...) can be added to, reordered and trimmed. The Pages list also has
  a shortcut to the screen that holds each page's items (for example Team page -> Manage team members).
* **Add the people and files an organisation page shows**: Team, Partners, Documents, Events and Impact stories each have their own screen.
  Impact stories are published only after you tick that the person's consent is confirmed.
* **Membership**: people apply at `/membership/register`. Staff review them under Admin > Members (approve, reject with a reason, record a payment, print a receipt, renew). The **fee of each membership type is set in Admin > Membership types & fees** and only affects new applications (each member keeps the fee agreed when they applied). Until a fee is set the website says "Fee to be announced". The page can be switched off with **Site settings > Membership > Accept applications**. Registration ships **closed**: switch it on after setting the fees, replacing the placeholder terms and reviewing the privacy section. Details: `docs/MEMBERSHIP.md`.
* Editors can edit content and handle the inbox; only admins manage settings, menus structure, users, deletions of inbox data and the activity log.

## Deploy to production (Linux / shared hosting)

0. The host needs **PHP 8.1+ (8.2 recommended)** with the extensions `pdo_mysql`, `mbstring`, `dom`, `fileinfo`, `ctype`, `json` (the front
   controller stops with a readable message when one is missing) and, recommended, `gd` (resizes photos; without it photos are still cleaned of GPS/camera
   data by a built-in cleaner) and `exif`. Admin > Site settings > System check shows what is missing.
1. Upload the project so that the **document root is the project folder** (the folder that contains `index.php`). Do not upload `.git`,
   `storage/*` or a development `.env`.
2. Copy `.env.example` to `.env` and set at least:
   `APP_ENV=production`, **`APP_DEBUG=0`**, **`APP_URL=https://your-domain`** (no trailing slash; REQUIRED outside `local`, the site answers 500 without it:
   links in e-mails, canonical addresses and the sitemap are built from it, never from the Host header; other Host names get HTTP 400 unless listed in
   `ALLOWED_HOSTS`), database `DB_*`, `MAIL_DRIVER=mail`, `MAIL_FROM`, and a long random `APP_KEY` (or leave it empty and keep the generated
   `storage/app.key`; if `storage/` is not writable set `APP_KEY`). **Behind a reverse proxy / CDN / load balancer set `TRUSTED_PROXIES`** (IPs or CIDR ranges):
   without it every visitor looks like one IP and shares the form and login rate limits.
   Keep `.env` out of version control and make sure it is not readable by the web (the root `.htaccess` already denies it).
3. **With a shell**: run `php bin/setup.php` once (creates tables, starter content and page rows), then create the real admin with
   `php bin/create-admin.php --email=... --name=... --role=admin` (without `--password` a random one is generated into
   `storage/dev-admin-credentials.txt` and the account must choose its own password at first sign-in), then **delete
   `storage/dev-admin-credentials.txt`**. `setup.php` also creates a throw-away admin when no user exists: remove or disable it
   once your own account works (Admin > Users).
   **Without a shell (shared hosting / phpMyAdmin)**: create the (empty, utf8mb4) database in the hosting panel, select it in phpMyAdmin and *Import*
   `database/schema.sql`, then `database/seed.sql` (starter content; optional). Put a long random `INSTALL_TOKEN` (16+ characters) in `.env`, open
   `https://your-domain/install`, enter the token and the details of the first administrator, then **remove `INSTALL_TOKEN` from `.env`**. The page answers 404
   unless the token is set and no user exists yet.
4. `storage/` and `uploads/` must be writable by the web server user. Compiled CSS is committed in `assets/css/`; recompile with
   `python bin/build-scss.py` only if you change `scss/`. `storage/sessions` is cleaned by PHP itself; visitors without a session cookie never create a session file.
5. Serve the site over **HTTPS** (the app sends HSTS and secure cookies on HTTPS). Apache needs `mod_rewrite` and `AllowOverride All`
   so that the `.htaccess` files are honoured. On Nginx/other servers, replicate them: deny `app/ bin/ database/ docs/ scss/ storage/ tools/ vendor/`,
   dotfiles, `*.env *.sql *.md *.py *.log`, and send every request that is not an existing file under `assets/` or `uploads/` to `index.php`.
6. After deploying, check that these return 403/404 and never file contents: `/.env`, `/app/config/app.php`, `/storage/app.key`,
   `/database/schema.sql`, `/bin/setup.php`, `/docs/ARCHITECTURE.md`, `/public/`, `/backup.zip` (any file that is not under `assets/` or `uploads/`),
   `/install` (404 once an admin exists). Run `php bin/selftest.php` locally before a release (it refuses to run outside `APP_ENV=local`).
   **Upgrading a live database**: re-importing `database/lumina-live-import.sql` (rebuilt with `php bin/build-import-sql.php`) is safe, and **must be done first (or together with the code upload)**: new code against the old schema answers 500 on `/membership` and Admin > Members. It only adds missing tables (`membership_types`, `members`, `member_payments`), the three guarded columns of `members`, settings and page rows, never overwrites anything the owner edited, and re-creates the three placeholder membership types only while that table is empty.
7. Set the real contact details, social links, donation details and notification e-mail under **Admin > Site settings**. The registration
   number differs between two pages of the live site; confirm the right one there (`reg_number`).
8. Maintenance mode: `APP_MAINTENANCE=1` in `.env` shows a maintenance page to visitors while `/admin` keeps working.
9. Back up the database and `uploads/` + `storage/private/` (CVs, reports) regularly.

## Layout

```
index.php   front controller          app/        code (core, controllers, models, views, config, routes)
assets/     css js img (static)       uploads/    user-uploaded images
storage/    private runtime data      database/   schema.sql, seed.sql
scss/       styles (compiled to assets/css)       bin/  setup, create-admin, build-scss, selftest
docs/       ARCHITECTURE.md, DESIGN.md, CONTENT_NOTES.md, live-snapshot/
```

## Documentation

* `docs/DESIGN.md` - the public design system: tokens, contrast table, component markup, JS hooks, per-section recipes. Read it before touching `scss/` or the public views.
* `docs/ARCHITECTURE.md` - developer contract: structure, APIs, CMS page registry, URL map, security rules.
* `docs/MEMBERSHIP.md` - the membership feature: data model, fee configuration, status workflow, service API, routes, privacy rules and the owner to-do list.
* `docs/CONTENT_NOTES.md` - where the starter wording comes from and what the owner still has to confirm or supply.
* `docs/live-snapshot/` - text of the live site pages used as the content source.
* To add a page that is editable in the admin: add `app/config/pages/<slug>.php` (see the architecture file), a controller, view and route, then run `php bin/setup.php`.
