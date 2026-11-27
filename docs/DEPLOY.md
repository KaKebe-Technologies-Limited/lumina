# Deploying the Lumina Africa Foundation website

The live site is on Hostinger. **Code** comes from GitHub (this repository, branch `main`). **Data** (database, `uploads/`, `.env`, `storage/`) lives only on the server and is never part of a deploy.

| Lives in | Changed by a deploy? |
|---|---|
| `app/`, `assets/`, `index.php`, `.htaccess` (the code) | Yes, replaced by the new version |
| MySQL database (pages, learners, donations, settings...) | **No.** Only the SQL files you import yourself change it |
| `uploads/` (photos and files uploaded in the admin) | **No** (excluded) |
| `.env` (passwords, keys) and `storage/` (logs, cache) | **No** (excluded) |

## One-time setup

1. Hostinger hPanel > **Files > FTP Accounts**: create (or reuse) an FTP account whose folder is `public_html`. Note the server name, user name and password.
2. GitHub > repo **Settings > Secrets and variables > Actions > New repository secret**:
   - `FTP_SERVER` the FTP host (for example `ftp.luminaafricafoundation.com`)
   - `FTP_USERNAME` and `FTP_PASSWORD`
   - `FTP_DIR` the target folder **relative to the FTP account's own folder**, with a trailing slash. A Hostinger FTP account for the site already starts inside `public_html`, so the live site is `./` (a value of `public_html/` is treated as `./` by the workflow: using it literally creates `public_html/public_html/` and the live site never changes). Staging: the sub-folder only, for example `staging/`.
3. Optional: **Variables > AUTO_DEPLOY = `true`** to deploy on every push to `main`. Without it, you deploy with the **Run workflow** button.

### The check that runs before every deploy

Every push to `main` and every manual run first runs the job **Check the code** (about a minute, no secrets, no database): every PHP file under `app/`, `bin/` and `index.php` must pass `php -l` on PHP 8.2 (the server's version), and `php bin/test.php --group=quick` must pass. **The upload only starts when the check is green.** A red check means the code is not uploaded: open the run in GitHub > Actions, read the failing line, fix it, push again. The check also runs when `AUTO_DEPLOY` is off, so every push gets a green or red mark on GitHub.

Normal day, once everything above is set up: make the change, test it locally (`php bin/test.php --group=smoke`), `git push`. With `AUTO_DEPLOY` on, it is live a minute or two later; without it, press **Run workflow**. The `upload-...` folders in Downloads are not needed any more.

What a deploy does **not** do: it never changes the database. If a change comes with a file in `database/updates/`, import it in phpMyAdmin once (step 5 of "Deploy day"). It also does not send `bin/` (the cron scripts and the self-tests): when a cron script changes, upload that file by hand.

**The first run after the FTP_DIR fix sends every file** (about 1,400, roughly 40 MB), because the sync record `.ftp-deploy-sync-state.json` is kept in the target folder and there is none in the web root yet. It takes several minutes, is not atomic (visitors can briefly meet old and new files), and replaces the live `.htaccess`. Take the hPanel backup and the database export first (step 1 of "Deploy day"). Later runs send only the changed files. After that first run is green and the live site serves the new files, **delete the stray `public_html/public_html` folder** in File Manager (it was created by the wrong FTP_DIR value; do not copy anything out of it).

**Check that a deploy really reached the live site:** open a changed asset on the real address (for example `https://luminaafricafoundation.com/assets/js/academy.js`) and look for the new code. A green run only means the upload finished, not that it landed in the web root.

## Before the first live deploy: rehearse on staging

1. hPanel: add a subdomain `staging.luminaafricafoundation.com` and a **new, empty database** for it.
2. phpMyAdmin: **Export** the live database, **Import** it into the staging database.
3. Copy `uploads/` to the staging folder; put a staging `.env` there pointing to the **staging** database and the staging URL (`APP_URL`).
4. Run the workflow with `FTP_DIR` set to the staging folder. Click through the public site, the admin, the Academy sign-up, the newsletter and the shop. Check **Admin > Error log** and **Admin > File check**.

## Deploy day (live)

1. **Back up**: hPanel > Backups (full backup) **and** phpMyAdmin > Export the database. Keep both off the server.
2. **Dry run**: GitHub > Actions > *Deploy to Hostinger* > Run workflow with *dry run* ticked. Read the list: only code should appear, never `.env`, `uploads/` or `storage/`.
3. Switch the site to maintenance for a few minutes: **Admin > Website on/off**. Staff can still sign in.
4. **Deploy**: run the workflow again with *dry run* unticked.
5. **Database**: in phpMyAdmin import any new files from `database/updates/` that the live database does not have yet (they are written to be safe to repeat). Pages that need an update tell you so ("The database update is missing").
6. **Check**: Admin > **File check** (everything should match; a few files you changed by hand on the server will be listed), Admin > **Error log** (empty), then open the home page, About, Projects, Fund a project, the Academy and the contact form.
7. Switch maintenance **off**.

## If something goes wrong

- Switch maintenance **on** again.
- Restore: `git revert` the bad commit and push (the run then uses the current workflow), or upload the backup zip. Do **not** use *Re-run* on an old GitHub run: a re-run uses the workflow file from that old commit, and runs made before the FTP_DIR fix (see "One-time setup") upload into the wrong folder.
- Data problem after an SQL import: restore the database export from step 1.

## Rules that keep deploys safe

- Never edit code on the server by hand: the next deploy replaces it. Make the change here, commit, deploy.
- Never commit `.env`, `uploads/` or `storage/` (they are in `.gitignore`).
- Anything that changes the database goes in `database/updates/` as a SQL file, imported by hand once per environment.
