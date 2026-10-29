# Backups: keeping a copy of the database (and the pictures)

Two things make up the website: the **database** (all content, learners, messages, settings) and the **uploads folder** (the pictures and files added through the admin). Both must be copied: the pictures are NOT in the database (the live site lost its October pictures when the `uploads` folder was replaced).

## 1. Local copy of the local database (automatic dump)

`bin/backup-db.php` writes a dated SQL dump of the database in `.env` (DB_NAME) to `C:\xampp\backups\lumina\` (outside the web root, newest 30 kept):

    C:\xampp\php\php.exe C:\xampp\htdocs\lumina\bin\backup-db.php

Double-click `BACKUP-LAF-database.bat` on the Desktop to run it. To run it every day: Windows **Task Scheduler** > Create Basic Task > Daily > Start a program: `C:\xampp\php\php.exe`, arguments `C:\xampp\htdocs\lumina\bin\backup-db.php`. Restore a dump: phpMyAdmin (local) > the database > Import, or `C:\xampp\mysql\bin\mysql.exe -uroot lumina < C:\xampp\backups\lumina\lumina-YYYYMMDD-HHMMSS.sql`. The dump holds personal data (learners, messages): keep it private, never upload it.

## 2. A copy of the LIVE database on the local server

1. Live Hostinger **phpMyAdmin** > your database > **Export** > Quick, format SQL > Export: a `.sql` file downloads.
2. Local phpMyAdmin (http://localhost/phpmyadmin) > **New** > name `lumina_live` > utf8mb4_unicode_ci > Create > **Import** the file. (Or tell Claude where the file is and it imports it.)
3. To look at the live data in the local site, set `DB_NAME=lumina_live` in the local `.env` (switch back to `lumina` for development). Run `database/schema.sql` on it once to get the newest columns.

Hostinger also keeps its own backups: hPanel > **Backups** (files and database, restorable per folder).

## 3. The pictures (uploads)

File Manager > `public_html` > select the **uploads** folder > Compress (zip) > download the zip to this computer. Do it after adding many pictures. Never replace or delete `uploads` when updating the site (the update zips leave it alone).
