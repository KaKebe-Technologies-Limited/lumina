# Lumina Africa Foundation - website + admin CMS

Organisation website for **Lumina Africa Foundation** (Gulu City, Northern Uganda) with a full admin CMS.
PHP 8.2 - MySQL/MariaDB - SCSS - vanilla JS. No Composer, no Node.

* **Every public page is editable from the admin** (headings, paragraphs, buttons, pictures and alt text, section on/off, SEO text,
  menu entry), and the owner can create new pages. Nothing is hard-coded: with an empty database the site still renders the
  built-in wording; the admin only stores what the owner changes.
* **All organisation pages are part of the site and the menus**: About, Our Team, Partners, Reports & Downloads, Programs, Projects,
  Involvement, **Membership** (online registration for people and organisations who want to join), Careers, Activities, Events, Gallery, Blog, News (and `/press`), Impact Stories, Contact (with the **Calendar** underneath), Donate, Privacy.
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
| Calendar | **Calendar & schedule** (month view, click a day to add a date, list and search, bulk show/hide), **Holidays & days** (confirm the Eid dates, change one year), **Types & colours**, Import / export (CSV), Settings (admin role only) |
| Administration (admin role only) | Site settings, Users, Activity log |

* **Edit a page's text**: Admin > Pages > *Edit*. Each section is a card; change a field and Save. "Reset to default" restores the built-in wording.
  Sections with a switch can be hidden. Repeating lists (values, FAQ, steps...) can be added to, reordered and trimmed. The Pages list also has
  a shortcut to the screen that holds each page's items (for example Team page -> Manage team members).
* **Add the people and files an organisation page shows**: Team, Partners, Documents, Events and Impact stories each have their own screen.
  Impact stories are published only after you tick that the person's consent is confirmed.
* **Membership**: people apply at `/membership/register`. Staff review them under Admin > Members (approve, reject with a reason, record a payment, print a receipt, renew). The **fee of each membership type is set in Admin > Membership types & fees** and only affects new applications (each member keeps the fee agreed when they applied). Until a fee is set the website says "Fee to be announced". The page can be switched off with **Site settings > Membership > Accept applications**. Registration ships **closed**: switch it on after setting the fees, replacing the placeholder terms and reviewing the privacy section. Details: `docs/MEMBERSHIP.md`.
* **Calendar and schedule**: the public page is `/calendar` (also shown on the Contact page, with an "Add to my calendar" file for every date). It ships with the Uganda public holidays and the main international and UN days; **Eid al-Fitr, Eid al-Adha and Safer Internet Day show "Date to be confirmed" until you confirm the day** (the Eid days already announced for 2024-2026 come pre-confirmed with their source; a predicted Eid day is usually the day shown or one day earlier; Admin > Calendar > Holidays & days; the sidebar badge and the dashboard card count dates to confirm). Add your own programme sessions, meetings and deadlines with **Add a date** (or click a day; repeating dates and a CSV import are supported; a repeating date cannot last as long as the gap between repeats). Unpublishing the Calendar page also closes its JSON feed and the .ics downloads. No programme session is pre-filled: the owner adds them. Details: `docs/CALENDAR.md`.
* **Meeting scheduler** (Admin > Calendar & schedule > Meetings, or the button **Schedule a meeting** on the Calendar manager and in the Inbox): create a meeting with its date and time (East Africa Time), a link and an agenda, invite people by typing or pasting e-mail addresses, searching your members, choosing staff or picking people from earlier meetings, then press **Save and send invitations**. Each person gets their own e-mail with a calendar file and **Accept / Maybe / Decline buttons that open their personal meeting page** `/meeting/<private link>` (answers are recorded there; a reply made inside Gmail or Outlook itself goes to the organiser's mailbox and is not recorded on the site), and later updates, a cancellation or reminders. **The site cannot create Zoom, Google Meet or Teams rooms:** paste the link you made there. The one built-in option is "Generate a free private video room", a link on the free external service Jitsi Meet (meet.jit.si, run by someone else); nothing is created until somebody opens the link. Meetings are **private by default**; a public meeting shows only "Meeting", the title, date and time on the public calendar (the join link only if you tick that box, which lets strangers join). Visitors can ask for a meeting from the Contact page; requests arrive in Admin > Calendar & schedule > Meeting requests, where "Schedule this meeting" pre-fills the form. No sample meeting is pre-filled. **Invitations need working SMTP** (see the mail notes below): with `MAIL_DRIVER=mail` many mailboxes (Gmail, Outlook) reject or hide calendar invitations, so set `MAIL_DRIVER=smtp` and send yourself a test invitation first ("Send me a test invitation" on the meeting page). Wording of the invitee page and of every e-mail: Admin > Pages > Meetings; request topics and defaults: Site settings > Meetings. Details: `docs/MEETINGS.md`.
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
   **Upgrading a live database**: re-importing `database/lumina-live-import.sql` (rebuilt with `php bin/build-import-sql.php`) is safe, and **must be done first (or together with the code upload)**: new code against the old schema answers 500 on `/membership`, Admin > Members and `/newsletter` (the footer link to the newsletter hides itself while its table is missing). It only adds missing tables (`membership_types`, `members`, `member_payments`, `newsletters`, `newsletter_revisions`, `newsletter_deliveries`), the three guarded columns of `members`, settings and page rows, never overwrites anything the owner edited, and re-creates the three placeholder membership types only while that table is empty, and adds the one sample **draft** newsletter issue only while the `newsletters` table is empty (it is not public until the owner publishes it; edit or delete it under Admin > Newsletter). The calendar needs the tables `calendar_types`, `calendar_entries` and `calendar_overrides` (added by the same import, together with the 14 Uganda public holidays and the international days, only while they are missing or unedited; a holiday you renamed, hid or changed is never overwritten). Until the import is done `/calendar` answers 404 and the Contact page simply omits the calendar block.
   **Upgrade steps (live site)**: (1) back up the database; (2) import `database/lumina-live-import.sql` in phpMyAdmin (repeating it is harmless); (3) upload the new code; (4) only if you want to e-mail issues: add the SMTP lines to the server `.env` (next paragraph), set the sender under Site settings > Newsletter, send a test from the issue's E-mail page; (5) check `/newsletter` (friendly empty page until the first issue is published) and Admin > Newsletter.
   **Newsletter e-mail (optional, needs SMTP)**: the newsletter can be e-mailed to the subscribers (Admin > Newsletter > an issue > E-mail). For that set
   `MAIL_DRIVER=smtp` and the SMTP login of your mailbox in `.env` (Hostinger: hPanel > Emails > the mailbox > Configuration):
   `MAIL_HOST` (e.g. `smtp.hostinger.com`), `MAIL_PORT` (`465` with `MAIL_ENCRYPTION=ssl`, or `587` with `MAIL_ENCRYPTION=tls`), `MAIL_USERNAME`, `MAIL_PASSWORD`,
   `MAIL_FROM` (an address that mailbox may send from), optional `MAIL_TIMEOUT` (seconds, default 20) and `MAIL_CAFILE` (a CA bundle, only if the server has none).
   Put the password in double quotes if it contains a `#`. The login lives ONLY in `.env`: never in the database, the admin or the logs. Certificates are always
   verified and a login is never sent without encryption. Sender name, reply-to, footer text, batch size and subject prefix are under **Admin > Site settings > Newsletter**.
   A wrong SMTP login, a TLS problem or a message above the server's size limit pauses a sending at the very first message (nothing is marked failed or "sent"): fix the `.env`, then press Resume. While a sending runs or is paused the issue cannot be edited (cancel the sending first), so every subscriber receives the same message. Deleting a subscriber also removes the address from the sending records of every issue.
   Keep `MAIL_DRIVER=mail` (or `file` locally) if you do not e-mail issues: the web page of the newsletter does not need SMTP. Optional cron job (hPanel > Advanced > Cron jobs)
   that finishes interrupted sendings: `php /path/to/lumina/bin/newsletter-send.php` every 5 minutes (see `docs/NEWSLETTER.md`). It needs `APP_URL` in `.env` (links in the mails are built from it), only runs from the command line, and prints nothing unless you add `--verbose`. Without the cron job a sending simply continues the next time someone opens the issue's E-mail page and presses "Continue sending".
   **Meeting scheduler e-mail (Admin > Calendar > Meetings, needs SMTP too)**: invitations, updates, cancellations and reminders use the same `.env` mail login and the sender set under Site settings > Newsletter (each person gets their own e-mail; the organiser's address is only the Reply-To, never the From address). Second optional cron job, **it also sends the 24-hour and 1-hour reminders**, so add it if you switch reminders on:
   `0-59/5 * * * *  /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/meetings-send.php` (every 5 minutes, same `APP_URL` rule, prints nothing unless `--verbose`, see `docs/MEETINGS.md`). It finishes queued sendings and sends only reminders that are due and not yet sent; it shares one database lock with the "Send" buttons, so nobody is e-mailed twice. Without it, invitations still go out from the meeting page, and reminders only when somebody presses "Send reminder now". A wrong mail login pauses a meeting's sending at the first message (nobody is marked failed): fix `.env`, then press Resume on the meeting page.
   **Try it locally without credentials**: `php bin/dev/mock-smtp.php` (a throw-away SMTP server on 127.0.0.1:2525 that stores every message in `storage/mock-smtp/`), then
   `MAIL_DRIVER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=2525`, `MAIL_ENCRYPTION=none` in your local `.env`.
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
* `docs/NEWSLETTER.md` - the newsletter: JSON content model, issue workflow, admin editor and JSON API, public pages, e-mailing queue, SMTP driver, mock SMTP server, privacy notes.
* `docs/CALENDAR.md` - the calendar: data model, repeat rules, date engine, public and admin routes, JSON and iCal shapes, the sources of every seeded date and what could not be verified.
* `docs/CONTENT_NOTES.md` - where the starter wording comes from and what the owner still has to confirm or supply.
* `docs/live-snapshot/` - text of the live site pages used as the content source.
* To add a page that is editable in the admin: add `app/config/pages/<slug>.php` (see the architecture file), a controller, view and route, then run `php bin/setup.php`.
