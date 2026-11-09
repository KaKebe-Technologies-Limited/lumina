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
   - `FTP_DIR` the target folder with a trailing slash: `public_html/` (staging: `public_html/staging/`, or the staging subdomain's folder)
3. Optional: **Variables > AUTO_DEPLOY = `true`** to deploy on every push to `main`. Without it, you deploy with the **Run workflow** button.

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
- Restore: re-run the workflow from the previous commit (GitHub > Actions > pick the earlier successful run > *Re-run*), or upload the backup zip.
- Data problem after an SQL import: restore the database export from step 1.

## Rules that keep deploys safe

- Never edit code on the server by hand: the next deploy replaces it. Make the change here, commit, deploy.
- Never commit `.env`, `uploads/` or `storage/` (they are in `.gitignore`).
- Anything that changes the database goes in `database/updates/` as a SQL file, imported by hand once per environment.
