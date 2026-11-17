# Security

What protects the website and the admin, how to switch each protection on, and what to do when something goes wrong. The owner's checklist is Admin > **Security** (it reads the live site and tells you in plain words what to fix).

## 1. What is in place

| Protection | Where | Notes |
|---|---|---|
| **Two-step sign-in by e-mail** | Admin > Security | after the password, a 6-digit code is e-mailed; one use, 10 minutes, 5 tries |
| **Screen lock** | Admin > Security (default 15 minutes) | idle screen locks, password needed again, the page is kept |
| **Sign-in limits** | `Auth::attempt` | 5 failures per address+e-mail, 20 per address, **30 per e-mail from any address**, in 15 minutes. Wrong password and unknown e-mail give the same message and take the same time |
| **Browser binding** | `Auth::user` | a session cookie replayed from another kind of browser is refused (updates of the same browser are fine) |
| **Password rules** | `Auth::passwordProblem` | 10+ characters, common passwords refused, a change signs the other sessions out |
| **Safe error messages** | `ErrorHandler`, `bootstrap.php` | visitors see a short friendly page; details go to `storage/logs` only. Debug output is possible **only when `APP_ENV=local`**: `APP_DEBUG=1` on a live server is ignored |
| **Protective headers** | `Response::securityHeaders` | Content-Security-Policy (no inline scripts), HSTS on https, no sniffing, no framing, referrer and permissions policy, `X-Powered-By` removed |
| **Secrets never in a browser** | `SecretStore`, `.env` | API keys live in the server `.env`; secrets typed in the admin are stored encrypted (AES-256-GCM, keyed from `APP_KEY`) and are **write-only**: no page ever prints them back. Scripts and public pages contain no key |
| **Files that must not be downloaded** | root `.htaccess` + one in every folder | `.env`, source code, `database/`, `storage/`, logs, `*.sql`, `*.md` are refused |
| **Spam and abuse traps** | `honeypot`, `throttle` middleware, CSRF tokens | every public form |
| **Audit trail** | Admin > Activity log | every sign-in, failure, code, lock and security change (never a password or code) |

## 2. Turn on two-step sign-in (about two minutes)

1. Sign in as an administrator and open Admin > **Security**.
2. Under *Two-step sign-in by e-mail* press **Send a test code**. It goes to your own address.
3. Type the code and press **Turn on two-step sign-in**.

The switch only turns on after a real code was typed, so a broken e-mail set-up can never lock you out. After that every administrator and editor receives a code at each sign-in, except on a browser they chose to *Remember* (default 30 days; set to "Do not offer it" on shared computers; **Forget all remembered browsers** clears them).

**If the mail server stops working and nobody can sign in:** in the hosting file manager add the line `AUTH_2FA=off` to the server `.env`, sign in, fix the e-mail (Administration > E-mail and sign-in), and delete the line. `AUTH_2FA=on` forces it on whatever the setting says. The lock time can be forced the same way with `AUTH_LOCK_MINUTES=15` (0 = off).

An account's code goes to the e-mail address stored on its user record (Admin > Users): keep those addresses real and private to each person.

## 3. Daily and yearly habits

* Give each person their own account (Users). Remove accounts nobody uses. Use the role with the least power that does the job (Roles).
* Long passwords or pass-phrases, different on every site, kept in a password manager.
* Look at Admin > Security once a week: the checklist, the failed sign-in count and the latest sign-in lines. Many failures in a row, or a sign-in you do not recognise: change that person's password at once.
* Keep **backups** (docs/BACKUPS.md) and test a restore once a year.
* Update PHP in hPanel when Hostinger offers a newer supported version.

## 4. Hosting checklist (Hostinger)

1. SSL certificate on and **force HTTPS** in hPanel; set `APP_URL=https://www.luminaafricafoundation.com` in `.env`.
2. `.env`: `APP_ENV=production`, `APP_DEBUG=0`, a long random `APP_KEY`, `INSTALL_TOKEN` empty, `MAIL_DRIVER=smtp` with a working mailbox (two-step sign-in needs it).
3. Delete `storage/dev-admin-credentials.txt` if it exists.
4. File permissions: folders 755, files 644, `.env` 600 where the host allows it.
5. Admin > Security should show every line green (it also tests that `/.env`, the database files and the key file cannot be downloaded).

## 5. If you think someone got in

1. Admin > Security > turn two-step on (if it is off) and **Forget all remembered browsers**.
2. Change the password of every administrator (Users), and of the hosting, e-mail and Daraza / Pesapal accounts.
3. Rotate secrets: new `APP_KEY` (this signs everyone out and the encrypted admin-saved secrets must be typed again), new database password (hPanel, then `.env`), new API keys at Daraza and Pesapal (then `.env`).
4. Read Admin > Activity log around the time; check Admin > Payments for anything you did not do; restore from a backup taken before the incident if content was changed.
5. Tell the people whose data may be affected.

## 6. For developers

* Two-step code: `App\Services\Security\TwoFactor` (tables `admin_login_codes`, `admin_trusted_devices`, created on first use). `Auth::attempt($request, $email, $password, false)` verifies the password only; `AuthController::login` then sends the code and parks the person in the session (`_2fa`) until `verify()`. Never call `Auth::login()` before the code step.
* Lock: `App\Services\Security\AdminLock::enforce()` runs in the `auth` middleware; activity is the session key `_act`. Background requests (JSON, XMLHttpRequest) do not count as activity.
* Checklist: `App\Services\Security\SecurityStatus::checks()`; add a line there to add a check.
* **Rules for new code:** never put a key, token or password in a view, a script, a flash message, a log line or an audit summary; show secrets "Set (hidden)" and replace-only; user-facing errors are one plain sentence (the detail goes to `Log::`); every state-changing route is POST with CSRF and, when public, `honeypot` + `throttle`.
* Tests: `php bin/selftest-security.php` (needs MySQL and Apache). It signs in through the real pages and covers the code (wrong, reused, expired, five wrong, resend), remembered browsers, the lock screen, the spread-out guessing limit, the cookie binding, error pages, protective headers, undownloadable files, and that a secret saved in the settings appears in no admin page, public page or script.

## 7. What is not covered (ideas for later)

* Authenticator-app (TOTP) codes and printed recovery codes (e-mail codes are used because every user already has a mailbox).
* Allow-listing admin IP addresses (awkward when addresses change; a hosting firewall or Cloudflare Access can do it).
* A web-application firewall and DDoS protection (hosting level, for example Cloudflare).
* Automatic scanning of uploaded files for malware (uploads are type-checked and images are scrubbed, see docs/ARCHITECTURE.md).
