# Personal data inventory - lumina.africa website

**DRAFT for the Data Protection Focal Person and the Executive Director. Not legal advice.**

* Built by reading the code (state of the working copy on 2026-11-23, including uncommitted work). Nothing here was measured on the live server: where the live Hostinger database, cron panel or `.env` decide the answer it says "check live".
* The wording of every notice, the legal bases suggested below, and every retention period must be **confirmed by counsel**. Suggested legal bases are a starting point under the Uganda Data Protection and Privacy Act, 2019 (DPPA), nothing more.
* The owner must also check the foundation's registration as a data controller with the **Personal Data Protection Office (PDPO), Uganda**. No PDPO registration number appears anywhere in the code or in the policy PDF, and none is invented here. (The NGO registration number is a different thing and is inconsistent, see Gaps G20.)
* Another session was adding a data-rights request page while this was written (`/privacy/request`, `app/controllers/Web/DataRightsController.php`, `app/services/Privacy/DataRights.php`, `database/updates/2026-11-data-policy.sql`, all uncommitted). It is listed in section 5 but not audited.
* Abilities named below are the ones in `app/config/app.php` (`abilities`). Built-in roles: `admin` (everything) and `editor` (see map); custom roles come from the `roles` table. `users.manage` and `roles.manage` are admin-only.

---

## 1. Summary table

Legal basis column = **suggestion**: C consent, K contract, LI legitimate interest, LO legal obligation. Retention column = what the code actually does ("none implemented" = no purge, no cron, nothing found).

| # | Activity | Data collected | Purpose | Legal basis (suggested) | Stored | Who sees (ability) | Retention in code | Third parties |
|---|---|---|---|---|---|---|---|---|
| 1 | Contact form | name, e-mail, phone (opt), subject (opt), message, IP, user agent | reply to enquiry | LI / C | `contact_messages` | `inbox.manage` (admin, editor); delete `inbox.delete` (admin) | none implemented | SMTP provider; staff mailbox (see 4.18) |
| 2 | Meeting request | name, e-mail, phone, organisation, topic, message, preferred date/time/duration/platform, consent time, hashed IP | arrange a meeting | C | `meeting_requests` (+ `meeting_attendees`, `meetings` when scheduled) | `meetings.manage` (admin, editor); delete `meetings.delete` | none for requests; `MeetingService::erase()` exists (called when a member is erased) | SMTP; optional Jitsi link (public service) |
| 3 | Newsletter | e-mail, source, raw IP, subscribed time, unsubscribe token; per-send delivery record (e-mail, status, error) | send newsletters | C | `subscribers`, `newsletter_deliveries` | `inbox.manage`, `newsletter.manage`, `newsletter.send` | none implemented; erased on admin delete (`NewsletterDelivery::erase`) | SMTP provider |
| 4 | Donations and pledges | donor name, e-mail, phone, amount, currency, method, reference, message, anonymous flag, receipt no., IP; payer name/e-mail/phone passed to gateway | record gifts, receipts, thanks, reminders | K / LO (receipts, accounts) / C (public thanks) | `donations`, `pesapal_payments`, `daraza_payments` | `inbox.manage`; delete `inbox.delete`; export `export.csv`; Pesapal/Daraza admin pages `inbox.manage` | none implemented | Pesapal, Daraza (MTN/Airtel), GoFundMe link, bank/PayPal details shown as text only (no PayPal API found) |
| 5 | Fund-a-project | pledge: name, e-mail, phone, amount, project, message, IP; proposal request: name, e-mail, phone, organisation, message | pledges, send proposal PDF | C / LI | `donations` (pledge), `contact_messages` (proposal) | `inbox.manage` | none implemented | SMTP; Daraza/Pesapal if mobile-money gift |
| 6 | Shop orders | name, e-mail, phone, delivery method + address, note, items, totals, payment method + reference, consent time, hashed IP; private order link token | fulfil order, accounts | K / LO | `shop_orders`, `shop_order_items`, `shop_payments`, `shop_order_log`, `shop_events` | `shop.orders` (admin, editor); export `shop.export`; delete `shop.delete`; settings/keys `shop.settings` | unpaid holds released by `bin/shop-maintenance.php`; orders themselves none; `shop_events` trimmed to ~2000 rows | Flutterwave (online pay), Pesapal/Daraza if enabled, SMTP |
| 7 | Event registration | name, e-mail, phone, organisation, notes, IP | manage attendance | C / K | `event_registrations` | `inbox.manage` | none implemented | SMTP |
| 8 | Job applications | name, e-mail, phone, cover letter, LinkedIn, CV (file), portrait (file), IP, user agent, tracking no. (JOB-), scores, stage history | recruitment | C / K (pre-contract) | `applications`; files `storage/private/cv/`, `storage/private/recruit/portraits/`; `recruit_scores`, `recruit_log`, `recruit_feedback` | `inbox.manage`; delete `inbox.delete` | none implemented (policy: 12 months for unsuccessful candidates) | SMTP |
| 9 | Volunteer sign-up (Involvement form) | name, e-mail, phone, type, organisation, message, IP, user agent; volunteers also CV + portrait; tracking no. (VOL-) | volunteer/partner/advocate onboarding | C | `involvement_requests`; same private folders | `inbox.manage`; delete `inbox.delete` | none implemented; admin delete leaves the files (G5) | SMTP |
| 10 | Ambassador applications | name, e-mail, phone, region, country, city, occupation, organisation, profile URL, roles, pledge (money/in-kind/other), network, motivation, availability, `public_ok`, consent time, IP, user agent, CV, portrait; tracking no. (AMB-); private profile token | recruit ambassadors; optional public listing (name, country, occupation, organisation) | C | `ambassador_applications`; same private folders | `inbox.manage`; delete `inbox.delete` | none implemented; admin delete leaves the files (G5) | SMTP |
| 11 | Membership | full name, gender, DOB, nationality, ID no. (only if setting on), e-mail, phone, WhatsApp, country/region/city/address, occupation, education, organisation, interests, skills, availability, how heard, motivation, guardian name/phone/relationship (under 18), emergency contact, payment method + reference, consent times (terms, privacy, guardian, photo), consent IP, user agent, photo, proof of payment, card data | membership, card, fees | K / C (photo, updates) | `members`, `member_payments`; files `storage/private/members/photos`, `.../proofs`; card photo copy | `members.manage` (admin, editor); delete `members.delete`; card design `members.card`; export `export.csv` | `bin/membership-maintenance.php`: photo + proof of rejected/cancelled members deleted after N days (`membership_proof_retention_days`, default 90, 0 = never); erase-on-request (`anonymise`) | SMTP; Daraza (fees) |
| 12 | Membership verification page | public page `/membership/verify/{token}` shows name, type, number, since, valid-until, status | verify card | LI / C | token in `members.card_verify_token` | anyone with the QR link (throttled) | token blanked on erasure | none |
| 13 | Academy learners | name, e-mail, password hash, phone, country, WhatsApp, bio, portrait (file) + `photo_consent_at`, Google `sub` (if Google sign-in), social handles, reg. no. (LAF-AC-...), fee status/reference/proof, e-mail/WhatsApp confirmation codes (hashed), messages, progress, quiz/exam attempts, certificates | run learning accounts, certificates | K / C (photo, public display) | `academy_learners`, `academy_enrollments`, `academy_progress`, `academy_attempts`, `academy_messages`, `academy_inquiries`, `academy_certificates`, `academy_social`, `academy_challenge_entries`; files `storage/private/academy/learners`, `/proofs`, `/winners` | `academy.manage` (admin); facilitators `academy.teach` see learners of own courses | only learner self-delete / admin delete (immediate, G4); nothing automatic | SMTP, Meta WhatsApp Cloud API, Google OAuth, Pesapal, Daraza |
| 14 | Academy exam monitoring | per attempt: start/submit time, deadline, one-window token, heartbeat, saved answers, `leave_count`, `flagged`, review; per event in `academy_exam_events`: `kind`, `counted`, `seconds_away`, `detail` (<=190 chars), time; no IP | exam integrity | LI (+ learner agrees to rules) | `academy_attempts`, `academy_exam_events`, `academy_exam_grants` (in `database/schema.sql` and `database/updates/2026-11-exam-security.sql`) | `academy.manage` | cascade on learner delete; max 200 events per attempt; none by age | none |
| 15 | Facilitator applications | name, e-mail, phone, country, topic, headline, experience, motivation, availability, links, CV + papers (files), consent time, hashed IP | recruit volunteer facilitators | C | `academy_facilitator_applications`; files `storage/private/academy/...` | `academy.manage` | none implemented | SMTP |
| 16 | Custom forms (form builder) | whatever fields the form defines (answers JSON), submitter e-mail, file uploads, consent time, hashed IP, user agent, status, notes | per form | C / form-specific | `form_submissions`, `form_files`; files `storage/private/forms/YYYY/MM/` | `forms.submissions` (admin, editor); export `forms.export`; delete `forms.delete` (admin) | per-form `retention_days` (default 0 = keep); lazy + `bin/forms-maintenance.php` | SMTP |
| 17 | Bono AI assistant | visitor's question (<=500 chars) in `bono_log`, outcome, page URL, helpful flag; last chat turns sent to the AI provider | answer visitors | LI / C | `bono_log` (no IP) | `settings.manage` (admin) | `bono_log` older than 180 days deleted when the indexer runs; rate-limit keys hashed | Anthropic (Claude API) only when `ANTHROPIC_API_KEY` set and `bono_ai` on |
| 18 | Admin / staff accounts and security | name, e-mail, password hash, role, last login; audit log (user, e-mail, action, summary, details, IP); login attempts (e-mail, IP); two-step codes (hashed, IP); trusted devices (token hash, user agent label, IP); session file | access control, security | LI / LO | `users`, `roles`, `audit_log`, `login_attempts`, `rate_limits`, `admin_login_codes`, `admin_trusted_devices`; `storage/sessions` | `users.manage`, `audit.view` (admin) | `login_attempts` 2 days; `rate_limits` expired rows; audit log, codes, expired devices: none implemented | SMTP (codes e-mailed) |
| 19 | Visitor analytics | per visit: path, country (2 letters), device, referrer host, one-day keyed hash code; no IP, no cookie; IP sent to ipwho.is for country lookup | traffic statistics | LI | `site_visits`, `geo_cache` (hashed IP) | `settings.manage` (Admin > Visitors) | `site_visits` 400 days, `geo_cache` 90 days (daily lazy purge) | ipwho.is; Google Analytics only if `analytics_id` is set |
| 20 | Data-rights requests (in progress) | name, e-mail, request type, details (check file) | handle requests | LO | `contact_messages` kind `dsr` | `inbox.manage` | not audited | SMTP |
| 21 | Official letters / documents | `letter_contacts` (name, e-mail, phone), `letter_recipients`, `letter_sends`; document signatories + signature images | letters, signed policies | LI | tables named; `storage/private/documents` | `letters.manage`, `documents.sign` (admin) | none implemented | SMTP |
| 22 | Cloudflare (website shield: firewall, bot filter, HTTPS front; only once the owner switches it on, docs/CLOUDFLARE.md) | every request passes through Cloudflare, so it processes visitors' IP address, browser details, the page asked for and, with the optional Turnstile check, a bot-check token; the site itself stores nothing more | protect the site from attacks and bots, deliver pages | LI | nothing in the database; the owner's ticks in `settings` (`security_cf_*`) hold no personal data | Admin > Security (`settings.manage`) | none (Cloudflare keeps its own short-lived logs, check the account's plan) | Cloudflare, Inc. (processor, servers worldwide: cross-border transfer, policy 12.3; sign the Cloudflare data-processing addendum and list Cloudflare in Annex G) |

---

## 2. Cookies, browser storage, third-party requests

**Cookies actually set (from code):**

| Cookie | Set by | Purpose | Lifetime | Essential? |
|---|---|---|---|---|
| `lumina_session` | `app/core/Session.php:94` (name from `app/config/app.php`) | staff sign-in, learner sign-in, flash messages, CSRF | browser session; HttpOnly, SameSite=Lax, Secure on HTTPS. Anonymous page views do NOT start a session (`Csrf` uses a stateless token); it starts when a form flash, login or admin page needs it | essential |
| `lm_td` | `app/services/Security/TwoFactor.php:250` | "remember this browser" for the admin e-mail code | N days (setting), HttpOnly | essential to the feature, only if staff tick the box |
| `laf_portal` | `app/services/Academy/LearnerAuth.php:496` | marks a browser that has a learner account (shows "My learner portal") | 400 days, HttpOnly, value `1` | convenience, not strictly necessary (functional) |
| `googtrans` | `assets/js/translate.js:27` (and Google's translator itself) | remembers the chosen language, switches on Google Translate | 1 year; only after a visitor picks a non-English language | **non-essential** (functional preference + loads Google scripts) |
| `_ga*` (Google Analytics) | `assets/js/main.js:560-571` | analytics | Google defaults | **non-essential; only if Settings `analytics_id` is filled** (default empty, `app/config/settings_schema.php:274`) |

**localStorage / sessionStorage / Cache API (all first-party, no personal data sent anywhere):**

* `sessionStorage` form draft (key from `FormService::clientConfig`), `assets/js/forms.js:799-806`: form answers typed so far, never files, cleared on send. Contains personal data typed by the visitor, stays in the browser.
* `sessionStorage` `bono-chat-v1` (`assets/js/bono.js:14`): last 20 chat turns of the Bono assistant.
* `localStorage` `lumina_pwa_banner_closed` (`assets/js/pwa.js:9`): install banner dismissed, 30 days.
* `localStorage` `laf-lesson:<path>` (`assets/js/academy.js:139`): learner's ticked lesson steps.
* Admin only: `localStorage` in `admin-academy.js`, `admin-form-submissions.js`, `admin-membership-card.js`, `admin-newsletter.js` (unsaved draft backup of a newsletter), `pages/admin-pages.js`.
* Service worker `app/views/pwa/sw-body.js`: caches only `/assets/` public static files; pages are never stored; private paths skipped.
* There is **no cookie banner** and no cookie page anywhere (the footer allows a `cookies` slug but no page exists).

**Third-party requests made by the browser (public site):**

* Google Fonts (`app/views/layouts/main.php:90-92`; also admin layouts, receipts, launch/maintenance pages): visitor IP sent to Google on every page. Not conditional, no notice. Non-essential.
* Google Maps embed iframe (`app/views/web/about/index.php:155`, `web/contact/index.php:143`; `Response.php` CSP allows `google.com/maps`): loads when the page is opened (lazy-loaded iframe).
* Google Translate (`assets/js/translate.js`): loaded only after the visitor picks a language (code comment says nothing is sent before).
* YouTube (`partials/video-button.php`): only `youtube-nocookie.com`, only after click.
* Google Analytics (`gtag`): only if `analytics_id` set, `anonymize_ip: true`.
* Server-side: ipwho.is receives the visitor IP (`VisitLogger::lookup`, setting `visits_country_lookup`, default on, skipped when a CDN country header exists; visitors sending Do-Not-Track or Sec-GPC are not logged at all).

**Raw IP addresses stored** (not hashed): `contact_messages`, `subscribers`, `applications`, `involvement_requests`, `ambassador_applications`, `donations`, `event_registrations`, `members.consent_ip`, `audit_log`, `login_attempts`, `admin_login_codes`, `admin_trusted_devices`. **Hashed (keyed) IP:** `meeting_requests.ip_hash`, `form_submissions.ip_hash`, `shop_orders.ip_hash`, `academy_facilitator_applications.ip_hash`, `rate_limits`, `geo_cache`, `site_visits.visitor_code`. **User agent stored:** `contact_messages`, `applications`, `involvement_requests`, `ambassador_applications`, `members`, `form_submissions`, `admin_trusted_devices.label`.

---

## 3. Retention that is really implemented

| What | Mechanism | Period |
|---|---|---|
| Form-builder submissions + files | `app/services/Forms/FormRetention.php`, `bin/forms-maintenance.php` (cron, daily advised), lazy every 6 h when an admin opens forms | per form `retention_days`; default `forms_retention_default_days` = 0 (keep) |
| Membership photo + proof of rejected/cancelled | `bin/membership-maintenance.php` (cron daily) -> `MemberFiles::purgeStale()`; also stray private files > 1 day | setting `membership_proof_retention_days`, default 90, 0 = never. Application record, number, receipts stay |
| Membership erase on request | admin `POST /admin/members/{id}/anonymise` (`members.delete`), `MembershipApplication::anonymise()` | immediate; blanks personal fields, deletes files, tokens, newsletter row, meeting rows (only for rejected/cancelled/expired etc., not pending/approved/suspended) |
| Visits | `VisitLogger::purgeOld()` | 400 days; `geo_cache` 90 days |
| Admin login attempts | `Auth::record()` (1 in 50 requests) | 2 days |
| Rate-limit keys | `RateLimit.php:43` | until `expires_at` |
| Bono log | `BonoIndexer.php:352` | 180 days |
| Shop unpaid holds | `bin/shop-maintenance.php` | hold times from settings; releases stock/cancels, does not delete orders |
| Shop event log | `PaymentService.php:534` | last ~2000 rows |
| PHP sessions | `Session.php` gc 1% | max(7200 s, idle setting) |
| Newsletter erase | `NewsletterDelivery::erase()` when a subscriber is deleted | immediate |
| Job application delete | `ApplicationController::destroy` deletes CV + portrait files | immediate (needs `inbox.delete`) |
| Local DB backup | `bin/backup-db.php` (APP_ENV=local only) | newest 30 dumps (`--keep`) in `C:\xampp\backups\lumina`; live backups are Hostinger hPanel backups (retention set by Hostinger, check) |

**Nothing implemented** for: contact messages, subscribers, donations/pledges, event registrations, job/volunteer/ambassador applications, facilitator applications, shop orders, meeting requests, academy learners/attempts/exam events/messages/inquiries, audit log, `admin_login_codes`, expired `admin_trusted_devices`, `newsletter_deliveries`, `pesapal_payments`, `daraza_payments`, letters, `storage/logs/app-YYYY-MM-DD.log` files (no rotation found), `storage/mail`, e-mail delivery logs.

---

## 4. Detail by activity

### 4.1 Contact form and meeting requests
* Form: `app/views/web/contact/index.php:110`; `POST /contact` -> `Web\ContactController::send` (line 36), route `app/routes/web_content.php:46`. Notice: `privacy_note` + link at `contact/index.php:131-133` (default text `app/config/pages/contact.php:53`). No tick box.
* Meeting request: `app/views/partials/meeting-request-form.php:70` (included at `contact/index.php:161`); `POST /contact/meeting-request` -> `MeetingRequestController::submit` (line 32) -> `MeetingRequestService::create`; route `app/routes/web_meetings.php:21`. Tick box `mr_consent` line 101, note line 106. Admin: `MeetingRequestsController` (`meetings.manage`). Invitees: `meeting_attendees` (name, e-mail, RSVP, per-person token, send status), RSVP page `/meeting/{token}` (`web/meetings/show.php:92`).
* Staff notification: `Mailer::notify()` mails the whole submission to Settings `notify_email` (else `email_primary`; the schema default is a Gmail address, `settings_schema.php:30`). Check live.

### 4.2 Newsletter
* `POST /subscribe` -> `SubscribeController::store` (line 35, `web_content.php:51`). Single opt-in: no confirmation e-mail, no consent text stored, no consent timestamp other than `subscribed_at`. Raw IP stored. Unsubscribe link in every mail (`/unsubscribe/{token}`, `web/legal/unsubscribe.php:15`). Membership registration also creates a subscriber row (source `membership`) when the box is ticked.
* Mail queue: `bin/newsletter-send.php` cron; SMTP via `.env` (`MAIL_DRIVER`, host not named in code).

### 4.3 Donations, pledges, payments
* Pledge: `donate/index.php:247` -> `DonateController::pledge` (line 54), route `web_core.php:52`. Online: `donate/index.php:164` -> `donate/online` (line 121, Pesapal) or `donate/mobile-money` (line 192, Daraza API `https://daraza.net`). No notice on any of the three forms.
* Pesapal (`https://pay.pesapal.com/v3`): ledger `pesapal_payments` (payer name, e-mail, phone, tracking id, method). Purposes: donation, shop, academy_signup, academy_cert. IPN `POST /pay/pesapal/ipn`.
* Daraza: `daraza_payments` (phone, payer name/e-mail, request id, status). Purposes: donation, project, shop, academy_signup, academy_cert, membership. Payer types the phone on `/pay/mobile-money/{reference}`.
* Flutterwave (`https://api.flutterwave.com`): shop only (`FlutterwaveClient`, webhook `/shop/webhook/flutterwave`). Card details never touch the site.
* PayPal: only an e-mail address shown as text from Settings (`donate_paypal_email`); no PayPal API code found. GoFundMe: link only.
* Admin: `DonationController` (`inbox.manage`, `inbox.delete`, `export.csv`), receipts (`admin/donations/receipt.php`, loads Google Fonts).
* **Bank-transfer details (new)**: the foundation's bank account (account name, number, bank, branch) is **public organisation text** from the settings `official_bank_*` (built-in defaults in `settings_schema.php`), shown on the Donate page, project pages, the shop order page and in e-mails; it holds no personal data and showing it stores nothing about the visitor. After a pledge the session key `donate_thanks` (name, amount, currency, method, reference `DON-<id>`, time) is kept in the server-side session file for up to one hour (never in the URL, ignored after an hour); `donations.reference` holds `DON-<id>` for donation pledges. A shop customer who types a bank-transfer reference on the order page creates a `shop_payments` row (method `bank`, source `customer`, status `pending`) and a line in `shop_order_log`. **No e-mail is sent automatically to the address typed on a pledge form.**

### 4.4 Shop
* Form `web/shop/checkout.php:66` -> `ShopCheckoutController::submit` (line 51). Tick box with terms + privacy links (lines 35-40, default `app/config/pages/shop.php:235`). Customer's private order page `/shop/order/{token}` (40-hex token). Staff: `admin/shop/orders/*` (`shop.orders`). Staff may WhatsApp the buyer via `wa.me` link (`admin/shop/orders/show.php:263`).

### 4.5 Events
* `web/events/show.php:139` -> `EventsController::register` (line 89, route `web_org.php:33`); unique per event+e-mail. Mail to registrant and staff. No notice.

### 4.6 Recruitment: jobs, volunteers, ambassadors
* Job: `web/careers/_apply_form.php:22` -> `CareersController::apply` (63) / `applyGeneral` (74). Tick box + privacy link lines 48-53 (text `config/pages/careers.php:186`).
* Volunteer: `web/involvement/index.php:101` -> `InvolvementController::submit` (42). **No notice at all**; volunteers must upload CV + portrait.
* Ambassador: `web/ambassadors/index.php:189` -> `Web\AmbassadorsController::submit` (60). Tick box lines 304-305, note 310, public-listing box 306. A 60-day duplicate check by e-mail.
* All three: tracking number via `Recruitment::assignRef`; applicant follows the stage at `/track-application` (`web/recruitment/track.php:5`, number + e-mail) and e-mails at each stage; ambassador private profile `/ambassador/profile/{token}` (`AmbassadorProfileController`). CV in `storage/private/cv/`, portrait in `storage/private/recruit/portraits/` (random names, re-encoded square 600 px). Admin streams files via `admin/recruitment/{scope}/{id}/{part}`. Auto-scoring (`AutoScore`) against KPIs; 70% invites to interview automatically (`recruit_interview_pct`): this is automated assessment support, while the privacy notice says "we do not use it for automated decision-making" (G14).
* The tables for tracking/scoring are created on first use (`Recruitment::install`) and in `database/updates/2026-11-recruitment.sql`; they are NOT in `database/schema.sql`.

### 4.7 Membership
* Form `web/membership/register.php:140` -> `MembershipController::submit` (99). Consent group: terms + privacy tick boxes with links (lines 51-64), guardian consent under 18, photo consent `photo_consent_at`. After registration: private dashboard `/membership/me/{token}` (`web/membership/me/show.php`: proof upload line 84, photo upload line 109 with fresh consent box lines 119-125). Card with QR -> `/membership/verify/{token}`. Retention and erasure see section 3. Documented in `docs/MEMBERSHIP_CARD.md` section 11.

### 4.8 Academy
* Register `web/academy/register.php:11` -> `AcademyAccountController::register` (41): name, e-mail, password, phone, WhatsApp, country, portrait (when required by setting), social handles (follow check), one tick box (`agree`, line 47, text `content('academy','auth.consent')`, default in `config/pages/academy.php:163`, no privacy link). Confirmation code by e-mail or WhatsApp (Meta Cloud API, `WhatsAppSender`). Google sign-in (`GoogleSignIn`): e-mail, verified flag, Google `sub` kept in `academy_learners.google_sub`.
* Account: `web/academy/account.php:10` (profile), `:30` (portrait + consent, line 43 "show my picture and first name on the public page"). Public display: featured learners (first name + initial, country, picture only if consent) and challenge winners (`web/academy/winners.php`, consent on entry `challenges/show.php:91,123`). Certificates are public by code (`/academy/certificate/{code}`, name, course, snapshot kept even after account deletion; `fk_academy_certificates_learner` ON DELETE SET NULL).
* Fees: `web/academy/pending.php:36` fee report with proof upload (`storage/private/academy/proofs`), Pesapal/Daraza payments.
* Messages: `portal/messages.php:33`, public inquiries `web/academy/contact.php:15` (`academy_inquiries`), WhatsApp reminders (template send; numbers and codes not logged).
* Exam: `web/academy/quiz.php:49` says "Every time you leave the page is recorded with the time"; agree box line 58. `academy_exam_events` **exists in `database/schema.sql` (line 2053) and in `database/updates/2026-11-exam-security.sql`**. Code: `app/services/Academy/ExamSecurity.php`, browser `assets/js/academy-exam.js`.
* Account deletion: learner self-service `POST /academy/account/delete` (`AcademyAccountController::deleteAccount`, line 630) and admin delete (`AcademyLearnersController::delete`, line 743) run a bare `DELETE FROM academy_learners`: DB rows cascade, but portrait/fee-proof/winner-photo files are not removed (G4).
* Facilitators: `web/academy/facilitate.php:13` -> `AcademyFacilitateController::submit` (32); consent tick line 52; CV and papers private; approval creates a staff `users` row.
* Bono assistant: see 4.10. Learner data is not sent to it; only what the visitor types plus site passages.

### 4.9 Form builder (custom forms)
* `web/forms/_form.php:49`, fields `_field.php`, pages `forms/show.php`, embeds `_embed.php`; `POST /forms/{slug}` -> `FormController::submit` (74). Optional form-wide consent tick (lines 97-100; default text `FormText ui.consent_default`), per-form `privacy_note` default (`config/pages/forms.php:82`). Files `storage/private/forms/YYYY/MM/`. Spam: honeypot, timing, `ip_hash`.

### 4.10 Bono AI assistant
* Widget `app/views/partials/bono.php` (every public page via `layouts/main.php`), `POST /bono/ask` -> `BonoController::ask` (21); rate limits keyed by IP. If `ANTHROPIC_API_KEY` is in `.env` and setting `bono_ai` is on, the visitor's question, the last 4 chat turns (<=300 chars each) and the matching site passages go to `https://api.anthropic.com/v1/messages`. No notice in the widget or privacy page. Question text is stored in `bono_log`.

### 4.11 Admin and staff security
* Sign-in `AuthController` (`/admin/login`): password (bcrypt), optional e-mail two-step code (`TwoFactor`, tables `admin_login_codes`, `admin_trusted_devices`, created on first use), idle screen lock (`AdminLock`, default 15 min), session bound to browser family (`Auth.php:53`) and password hash fingerprint, throttle by IP + e-mail. Audit log (`AuditLog::record`) writes "Name <e-mail>", action, summary (often contains an applicant/learner name or e-mail), details JSON, IP. Admin pages: `/admin/security`, `/admin/audit-log` (`audit.view`), `/admin/error-log` (`settings.manage`, reads `storage/logs`), `/admin/visitors` (`settings.manage`).
* Password reset by signed e-mailed link (no table).

### 4.12 Visitor analytics
* `app/services/Analytics/VisitLogger.php` (see table). Skips admin, API, robots, signed-in staff, DNT / Sec-GPC. Viewed in `VisitorsController`, CSV export `/admin/visitors/export`.

### 4.13 Language switcher, PWA, logs, e-mail, backups
* Switcher: Google website translator, see section 2. PWA: manifest + service worker (`PwaController`), static files only.
* Logs: `Log` -> `storage/logs/app-YYYY-MM-DD.log` (links with secrets and tokens are redacted; mail failures log subject/SMTP code, addresses masked in some paths). No rotation. `ErrorHandler`: debug output only on local sites.
* E-mail: `Mailer` driver from `.env` (`file` locally writes `storage/mail/*.eml`; `smtp` live). Sent-mail copies are not stored on live. Delivery state kept per recipient in `newsletter_deliveries`, `meeting_attendees`, `academy_learners.mail_sent_at/mail_error`.
* Backups: section 3. The dump contains all personal data (documented in `bin/backup-db.php`, `docs/BACKUPS.md`); the local backup folder is outside the repository.
* Hosting: Hostinger (`docs/DEPLOY.md`): data (database, `uploads/`, `.env`, `storage/`) lives on the server; server country not stated in the repo (check live, relevant to cross-border transfer, policy 12.3).

---

## 5. Where privacy text exists today

| Location | What it is |
|---|---|
| `app/config/pages/privacy.php` (route `/privacy`, controller `Web\PrivacyController`, view `app/views/web/legal/privacy.php`) | DRAFT privacy notice: 12 numbered sections (lines 46-105) + editable Membership and Shop blocks. Editable in Admin > Pages |
| `app/config/pages/data-rights.php`, `app/views/web/legal/data-rights.php`, `Web\DataRightsController` | In progress: request form `/privacy/request`, default response target 30 days (`response_days`), mention of the PDPO |
| `app/config/pages/membership-terms.php` (`/membership/terms`) | membership terms; one line points to the privacy notice (line 39) |
| `app/config/pages/membership.php` | consent labels (lines 260-268), photo consent texts (`uploads.*`), verify page `privacy_note` |
| `app/config/pages/shop.php:235` | checkout consent label; shop terms page `/shop/terms` |
| `app/config/pages/contact.php:53,139`, `ambassadors.php:158-160`, `careers.php:186-187`, `academy.php:96,163`, `forms.php:82`, `newsletter` `subscribe.privacy_note` (`web/newsletter/_subscribe.php:18`) | form-level notes |
| `app/config/pages/stories.php:44` | consent note for published stories |
| `app/views/web/academy/quiz.php:49,58` | exam rules notice |
| `docs/MEMBERSHIP_CARD.md` section 11, `docs/FORMS.md` section 13, `docs/SECURITY.md`, `docs/BACKUPS.md` | developer notes on privacy, retention, security |
| `storage/private/policy-pack/LAF_Data_Protection_Privacy_Policy.pdf` (LAF-DP-001) | the policy, summarised in section 7. The policy pack is used by `DocumentSigning` (policies are signed by officers and downloadable) |

Cookie notice: none. Footer legal links: `app/views/partials/site-footer.php:36` (`privacy`, `data-rights`, `terms`, `cookies` slugs; only `privacy` and `data-rights` pages exist).

---

## 6. Form templates and routes (for adding notices)

| Form | Template | Controller::method (line) | Route file | Notice today |
|---|---|---|---|---|
| Contact | `app/views/web/contact/index.php:110` | `Web\ContactController::send` (36) | `routes/web_content.php:46` | note + link, no tick |
| Meeting request | `app/views/partials/meeting-request-form.php:70` | `MeetingRequestController::submit` (32) | `routes/web_meetings.php:21` | tick + note |
| Newsletter (footer, all pages) | `app/views/partials/newsletter-row.php:19` (via `site-footer.php:64`) | `SubscribeController::store` (35) | `routes/web_content.php:51` | **none** |
| Newsletter (blog/activities) | `app/views/web/blog/_subscribe.php:16` | same | same | **none** |
| Newsletter (newsletter pages) | `app/views/web/newsletter/_subscribe.php` | same | same | note (line 18) |
| Donate pledge | `app/views/web/donate/index.php:247` | `DonateController::pledge` (54) | `routes/web_core.php:52` | **none** |
| Donate online (Pesapal) / mobile money | `app/views/web/donate/index.php:164` | `DonateController::online` (121) / `mobileMoney` (192) | `web_core.php:56-58` | **none** |
| Fund a project: pledge / proposal | `app/views/web/fund/show.php:112` / `:236` | `FundController::pledge` (71) / `proposal` (186) | `web_core.php:46-47` | **none** |
| Shop checkout | `app/views/web/shop/checkout.php:66` | `ShopCheckoutController::submit` (51) | `web_shop.php:47` | tick + terms/privacy links |
| Event registration | `app/views/web/events/show.php:139` | `EventsController::register` (89) | `web_org.php:33` | **none** |
| Job / general application | `app/views/web/careers/_apply_form.php:22` | `CareersController::apply` (63) / `applyGeneral` (74) | `web_content.php:39-41` | tick + link |
| Volunteer / partner / advocate | `app/views/web/involvement/index.php:101` | `InvolvementController::submit` (42) | `web_core.php:34` | **none** (CV + portrait asked) |
| Ambassador | `app/views/web/ambassadors/index.php:189` | `Web\AmbassadorsController::submit` (60) | `web_core.php:39` | tick + note |
| Track application / ambassador link | `app/views/web/recruitment/track.php:5`, `profile-request.php:9` | `TrackApplicationController::show` (14), `AmbassadorProfileController::sendLink` (61) | `web_recruitment.php` | lookup only |
| Membership | `app/views/web/membership/register.php:140` (+ `_consent.php`, `_upload.php`) | `MembershipController::submit` (99) | `web_membership.php:19` | terms + privacy ticks, photo consent |
| Membership dashboard uploads | `app/views/web/membership/me/show.php:84,109` | `Membership\Me::uploadProof/uploadPhoto` | `web_membership.php:27-28` | photo consent tick |
| Academy register | `app/views/web/academy/register.php:11` | `AcademyAccountController::register` (41) | `web_academy.php:29` | one tick, no privacy link |
| Academy account / portrait | `app/views/web/academy/account.php:10,30` | `updateProfile` (528) / `savePhoto` (563) | `web_academy.php:58-62` | portrait consent tick |
| Academy fee report | `app/views/web/academy/pending.php:36` | `reportFee` (318) | `web_academy.php:45` | none |
| Academy follow check | `app/views/web/academy/follow.php:5` | `follow` (164) | `web_academy.php:41` | none |
| Academy contact | `app/views/web/academy/contact.php:15` | `AcademyContactController::send` (37) | `web_academy.php:36` | none |
| Academy portal messages | `app/views/web/academy/portal/messages.php:33` | `AcademyPortalController::sendMessage` (180) | `web_academy.php:74` | none (logged in) |
| Facilitator application | `app/views/web/academy/facilitate.php:13` | `AcademyFacilitateController::submit` (32) | `web_academy.php:92` | tick (line 52) |
| Challenge entry / photo / project | `app/views/web/academy/challenges/show.php:106,82,55` | `AcademyChallengesController::apply` (98), `photo`, `project` | `web_academy.php:97-103` | photo consent ticks (lines 91,123) |
| Exam / quiz | `app/views/web/academy/quiz.php:49-58`, `attempt.php` | `AcademyLearnController::startQuiz/submit/sync` | `web_academy.php:85-88` | rules + agree, mentions recording leaves |
| Custom forms | `app/views/web/forms/_form.php:49` | `FormController::submit` (74) | `web_forms.php:19` | optional per form |
| Bono chat | `app/views/partials/bono.php` | `BonoController::ask` (21) | `web_bono.php:9` | **none** |
| Data-rights request | `app/views/web/legal/data-rights.php` | `DataRightsController::send` | `web_content.php:59-60` | in progress |
| Meeting RSVP | `app/views/web/meetings/show.php:92` | `MeetingController::rsvp` | `web_meetings.php:19` | token link |
| Admin sign-in | `app/views/layouts/admin_login.php` | `Admin\AuthController` | `admin_core.php:35-37` | n/a (staff) |

---

## 7. Policy PDF summary (LAF-DP-001)

File `storage/private/policy-pack/LAF_Data_Protection_Privacy_Policy.pdf`; full text saved to `...\side-session-notes\local_7fb0295c-313a-4003-b404-0797ec4f81fe\dp-policy-text.txt` (text is Windows-1252 and contains some `?` characters).

* **Document control:** "Data Protection and Privacy Policy", number LAF-DP-001, version 1.0, status "For Board approval", effective date "To be confirmed", next review "On adoption + every 2 years", policy owner "Executive Director, with the Data Protection Focal Person", approving authority Board of Directors, registration number printed in the PDF "GSD/R 1234 - Gulu City". Cover: Laroo-Pece Division, Gulu City; info@luminaafricafoundation.com; +256 779 712 990.
* **Sections:** 1 Introduction; 2 Legal and Regulatory Framework; 3 Definitions; 4 Policy Statement; 5 Scope; 6 Data Protection Principles; 7 Roles and Responsibilities; 8 Lawful Basis and Consent; 9 Photographs, Videos and Stories; 10 Collecting and Using Data; 11 Data Security; 12 Sharing Data and Transfers; 13 Retention and Disposal; 14 Rights of Data Subjects; 15 Data Breaches; 16 Data Protection by Design, Impact Assessment and Registration; 17 Training, Monitoring and Review; 18 Related Policies and References. Annexes A consent form (photos/videos/stories), B breach report form, C DPIA template, D data inventory template, E data subject request form and response letter, F data sharing agreement, G processor and vendor checklist, H scenarios, I audit checklist, J FAQ, K quick reference card.
* **Officer title as written:** "**Data Protection Focal Person**" ("The person appointed to oversee compliance with this policy"; the ED designates the person, "this may be the MEL Officer or another trained person"). Related titles: "ICT Focal Person", "Safeguarding Focal Person", "Executive Director", "Program Manager", "MEL Officer". There is no "Data Protection Officer" in the policy. Duties (7.1): keeps the data inventory (Annex D) and reviews it with each project lead twice a year; reviews new forms, surveys, apps, vendors and DPIAs; logs and answers data subject requests; receives breach reports; training; quarterly checks (Annex I) and reports to the ED; "contact point for the Personal Data Protection Office, donors and data subjects".
* **Legal references (section 2):** Constitution of Uganda 1995 (Article 27); "Data Protection and Privacy Act, 2019 and its Regulations"; Access to Information Act, 2005; Computer Misuse Act, 2011 (as amended); Children Act; HIV and AIDS Prevention and Control Act, 2014; donor requirements. The policy says the table "is a working summary and not legal advice" and the Board may ask a lawyer to confirm "the time limits for notifying the Personal Data Protection Office". It requires registering as a data controller with the PDPO and "displays the registration number on the privacy notice" (2.1, 16).
* **Principles (6):** accountability; lawful, fair and transparent; purpose limitation and minimisation; accuracy; retention limitation; security and confidentiality; rights of data subjects.
* **Lawful bases (8, 8.1):** consent, a contract, a legal duty, vital interests, or a legitimate interest. Members: contract; "Newsletters need separate consent". Staff: contract and legal duty. Volunteers: consent and legitimate interest. Donors: contract and legal duty; "Public thanks need consent". Photos, videos, stories: consent, written, renewable (proposal: every 2 years). Consent must be "free, informed, specific and recorded" with date, notice version and purposes; withdrawable at any time; not a condition of service unless necessary.
* **Children:** "For children under 18, consent comes from a parent or guardian, and the child's views are respected." Only minimum child data, "Children's data is not used for marketing" (10.3). Photos of children: parent or guardian signs in writing and the child agrees; never publish a child's name, school or home area (9). A DPIA is required for children's data (16.1).
* **Forms and notices (2.1, 10):** "Every form and survey is checked against the data inventory (Annex D) before use"; "Forms and data collection tools state the purpose and include a short privacy notice"; "Privacy notice read out or handed over at every point of collection"; marketing/newsletter only to people who agreed, with opt-out; donor information not sold.
* **Retention (13, 13.1; "proposals ... must be confirmed"):** personnel files 7 years after the person leaves; financial and payroll records 7 years; beneficiary and MEL data 7 years after the project ends or as the donor requires; member records "For the period of membership and 3 years after"; safeguarding and case records 7 years (longer for child cases); consent forms and photographs while the image/story is used plus consent record as long as needed; recruitment records of unsuccessful candidates 12 months ("Unsuccessful candidates' data is deleted after 12 months", 10.4). Detailed schedule: attendance sheets and activity registers project end + 7 years; survey raw data identifiers removed after analysis, remainder 7 years; volunteer records "3 years after volunteering ends (Board to confirm)"; donor and grant records 7 years; photographs and stories "Life of the publication; review every 2 years"; requests and breach registers 7 years; backups "Rolling 12 months". Longer of policy and donor rule applies; legal hold overrides; disposal list each January, ED approves, two people sign the disposal log.
* **Data subject requests (14, 14.1):** rights to confirm what is held, copy, correct, delete data not needed, stop processing, object (e.g. marketing), withdraw consent. Requests go to the Data Protection Focal Person; identity verified; answered "within thirty (30) days, free of charge unless excessive"; may complain to the Personal Data Protection Office. Internal steps: acknowledge in 3 working days (proposal), verify in 5, search in 15 days, decide in 20, respond by day 30, close in 5. Possible limits: other people's data, legal retention duty, donor audit, open safeguarding case, printed or public material.
* **Breaches (15, 15.1):** report "immediately" to the Focal Person and Executive Director; Focal Person records, contains, assesses; where there is a risk, LAF informs the PDPO and the people affected "within the time set by law" (no number of hours or days stated). Internal targets "for Board to confirm": low = same day; medium = report within 2 hours, assess within 24 hours; high (children's, health, safeguarding data) = report at once, contain within 24 hours, ED and Board Chairperson informed. KPI: breaches reported to the Focal Person within 24 hours. Third-party data sharing agreements: processor reports a breach to LAF within 24 hours; help with requests within 10 working days (proposals).
* **Security and sharing (11, 12):** role-based access, individual passwords, "multi-factor sign-in where available", daily backups with restore test twice a year, no personal data on open groups or personal e-mail. Transfer outside Uganda only if adequate protection or consent (cloud services whose servers are abroad included); processors need a written agreement, vendor checklist Annex G; "Free third-party survey tools are used only if approved by the Focal Person".
* **Other items the website must stay consistent with:** photo and story consent per Annex A (name use yes/no, renewable); staff photos and contact details published only with consent; "LAF does not rely on surveillance cameras"; DPIA before new technology (apps, biometrics, new platforms) or new third party or transfer outside Uganda (16.1); registration number on the privacy notice; policy owner reviews every 2 years.

---

## 8. Gaps and recommendations

Numbers are referenced above. Severity guess: H high, M medium, L low.

**Notices and consent**

* G1 (H) No notice at all on: newsletter footer (`app/views/partials/newsletter-row.php:19`, on every page) and blog/activities box (`app/views/web/blog/_subscribe.php:16`); volunteer/Involvement form that also takes CV + portrait (`app/views/web/involvement/index.php:101`); donate pledge/online/mobile money (`web/donate/index.php:164,247`); fund pledge and proposal (`web/fund/show.php:112,236`); event registration (`web/events/show.php:139`); Bono chat (`app/views/partials/bono.php`); academy contact, follow, fee report, messages (`web/academy/contact.php:15`, `follow.php:5`, `pending.php:36`, `portal/messages.php:33`). Policy 2.1 and 10 ask for a short notice at every point of collection.
* G2 (M) Newsletter is single opt-in with no stored consent text or notice version; policy 8.3 asks for date, version and purposes. Consider a consent timestamp column (the in-progress `2026-11-data-policy.sql` mentions consent times) and a confirmation e-mail.
* G3 (M) Contact form has a note but no tick; the privacy notice itself says consent is the main basis ("Our legal basis", `privacy.php:73`). Align: either tick box or state legitimate interest.
* G14 (M) Privacy notice (`app/config/pages/privacy.php`) is silent on: LAF Academy (learner accounts, portrait, certificates, exam monitoring, WhatsApp, Google sign-in), Bono and Anthropic, Google Fonts / Maps / Translate, ipwho.is, Pesapal and Daraza (only Flutterwave appears, in the shop block), volunteer and ambassador CV + portrait + tracking + scoring (lines 50-60 list only a CV), visitor statistics, the `laf_portal` / `googtrans` cookies. "Cookies and analytics" (line 77) says only essential cookies and that Google Analytics "may" be used; no cookie notice or choice exists. The text "we do not use it for automated decision-making" (line 62) sits next to the KPI auto-score that auto-invites to interview at 70%. Section "How long we keep it" (line 85) gives no periods and the membership/shop blocks carry "[The foundation will confirm the exact retention period here.]".
* G15 (M) Academy sign-up consent text (`config/pages/academy.php:163`) has no link to the privacy notice, no mention of WhatsApp, Google or exam monitoring. No age check or guardian step for learners (policy: under 18 needs parent or guardian consent; the notice says no under-18 data without it). Only Membership has guardian fields. The notice (`privacy.php:93`) and policy should be reconciled with who the Academy accepts.
* G16 (L) Exam notice (`web/academy/quiz.php:49`) says leaving is recorded; add legal basis, who sees it, how long it is kept.
* G17 (M) Google Fonts load on every public page (`layouts/main.php:90-92`) and the Maps iframe loads on About/Contact without consent: both reveal the visitor IP to Google. Options: self-host fonts, click-to-load map, cookie/third-party notice.

**Retention and deletion**

* G4 (H) Learner deletion (self `AcademyAccountController.php:630-643` -> `LearnerAuth::deleteAccount` `LearnerAuth.php:579`; admin `AcademyLearnersController.php:743`) removes DB rows only: portrait (`storage/private/academy/learners`), fee proof (`academy/proofs`), challenge photos/files (`academy/winners`) stay on disk; no orphan cleaner exists for the academy folders (only members and forms have one). The audit log keeps the deleted learner's e-mail in its summary (`AcademyLearnersController.php:747`). Certificates keep the name by design (public verification): state this in the notice.
* G5 (H) Admin delete of volunteer (`InvolvementController::destroy`, line 78, and its bulk delete) and ambassador applications (`Admin\AmbassadorsController::destroy`, line 108) does not delete the private CV and portrait files (job applications do, `ApplicationController.php:99-110`). Orphans stay in `storage/private/cv` and `recruit/portraits`.
* G6 (H) No retention is implemented for most personal data (list at end of section 3). Policy proposals to implement or confirm: unsuccessful candidates 12 months (jobs, volunteers, ambassadors, facilitators); member records membership + 3 years (the code keeps approved/expired members forever); volunteer records 3 years after volunteering; donor records 7 years; backups rolling 12 months.
* G7 (M) `admin_login_codes` (used/expired) and expired `admin_trusted_devices` rows are never deleted; `audit_log` and `storage/logs/app-*.log` have no purge or rotation; logs and audit entries can carry names, e-mails and IPs.
* G8 (M) Shop orders and `shop_orders.customer_*`, donations, `pesapal_payments`, `daraza_payments` keep payer data indefinitely; accounting need (7 years in the policy) should be written into the notice and an erase-after step planned. No erase-on-request tool exists except for members and newsletter subscribers/meeting attendees; a deletion request for a contact message, donor, buyer, learner file or job applicant is manual SQL or manual admin delete.
* G9 (L) Forms: default retention is 0 (keep forever); `forms_retention_default_days` could be set once counsel confirms a period.

**Security / operations**

* G10 (M) Raw IP addresses are stored in 12 tables (list in section 2) although the privacy notice says an IP is kept only for security/spam; others use keyed hashes. Consider hashing everywhere or stating the period.
* G11 (M) `Mailer::notify()` mails the full contents of forms (including phone numbers, CV links, messages) to `notify_email`/`email_primary`; the schema default is a Gmail address (`settings_schema.php:30`): that is processing by a third-party mailbox provider; check live value and mention in the notice/Annex G.
* G12 (M) Third-party processors lacking vendor checks in the repo (policy Annex G / 12.2): Pesapal, Daraza, Flutterwave, Meta (WhatsApp Cloud API), Google (OAuth, Fonts, Maps, Translate, Analytics if on), Anthropic, ipwho.is, SMTP provider, Hostinger. Server location and cross-border transfer basis (policy 12.3) are not recorded anywhere in the code.
* G13 (L) `storage/dev-admin-credentials.txt` exists in the working copy (git-ignored via `storage/*`); make sure it never reaches the live server (deploy excludes `storage/`, `docs/DEPLOY.md:39`).
* G18 (L) `bin/backup-db.php` dumps live-like personal data to a local folder with no encryption (outside the repo); the live backups are Hostinger's.

**Consistency**

* G19 (M) Policy PDF effective date is "To be confirmed" and status "For Board approval"; the site should not state the policy is in force until adopted. The site has no link to the policy PDF (it is served only inside the signing/policy-pack feature, `DocumentSigning`).
* G20 (M) Registration number mismatch: the PDF prints "GSD/R 1234 - Gulu City"; Settings default and live import use "GSD/R 1545" (`app/config/settings_schema.php:19`, `database/seed.sql:31`, `database/lumina-live-import.sql:2689`). The Settings help text already warns that the old site showed two numbers (`settings_schema.php:20`, `DashboardController.php:165-168`). Owner to decide the correct one. The policy wants the PDPO registration number on the privacy notice: no field exists for it.
* G21 (L) Roles: the policy says access to personnel files is limited to the ED and HR; the website's `editor` role by default sees all inbox, applications, donations, members, shop orders, forms (`inbox.manage`, `members.manage`, `shop.orders`, `forms.submissions`). Review role design.
* G22 (L) Policy title "Data Protection Focal Person" is not used anywhere on the site; the privacy page contact block uses the general e-mail (`{email_link}`). Add the role and a dedicated address once the owner names one.

---

## 9. Owner checklist - to be confirmed

1. Counsel to confirm the wording of every notice and the legal basis per activity (section 1), and the response/breach time limits under the DPPA 2019 and its Regulations (the policy says "within the time set by law" without numbers).
2. Confirm foundation registration with the Personal Data Protection Office; enter the real PDPO registration number (none in the code or policy).
3. Decide which NGO registration number is correct (G20) and update Settings or the PDF.
4. Board to adopt LAF-DP-001 (status "For Board approval", effective date "To be confirmed"); appoint the "Data Protection Focal Person" by name, with a contact address.
5. Confirm retention periods for each table in section 3 (policy proposals marked "Board to confirm"), then ask the developer to implement the purges.
6. Confirm where the live server and mailbox providers are located and whether data goes outside Uganda (policy 12.3); complete Annex G for Hostinger, SMTP provider, Pesapal, Daraza, Flutterwave, Meta, Google, Anthropic, ipwho.is.
7. Decide: keep Google Fonts, Maps, Translate and Analytics (and their cookie/notice), or self-host/disable. Check whether `analytics_id` is set on the live site.
8. Decide whether `ANTHROPIC_API_KEY` stays on (Bono AI) and add a notice; decide the visitor-question log period.
9. Decide the age rules for Academy learners and the parental-consent step (G15).
10. Decide what is public: learner first name + initial + photo, challenge winners, ambassador list, certificates by code, membership verify page; confirm each has consent wording.
11. Check live `notify_email` / `email_primary` and who can read that mailbox; check live roles and users (who has `inbox.manage`, `members.manage`, `academy.manage`, `shop.orders`).
12. Confirm Hostinger backup retention and who can download backups; confirm cron jobs are really scheduled on the live site (`forms-maintenance`, `membership-maintenance`, `shop-maintenance`, `newsletter-send`, `meetings-send`, `academy-reminders`, `bono-learn`).
13. Check the in-progress data-rights request page and its tables once finished, and set the response target to the number the Focal Person can really meet (policy: 30 days).

## Framework added on 2026-11-23 (what now exists on the site)

> **Draft framework, not legal advice.** The owner must have the wording confirmed by counsel and check the foundation's registration with the Personal Data Protection Office (PDPO) of Uganda. No registration number, officer name or address has been invented; retention aims other than those proposed in LAF-DP-001 are suggested defaults to be confirmed (see Admin > Data protection).

* Public: `/privacy` (Privacy Policy: "in brief" box, table of contents, 10 sections, table of what is collected per activity, rows linkable as `#collect-<key>`), `/privacy/request` (Your data rights: request form, reference `DPR-YYMMDD-XXXXX`, 30-day target, acknowledgement e-mail). Both are registry pages (Admin > Pages), in the footer legal links and the sitemap.
* Requests are stored in `contact_messages` (new columns `kind`, `reference`, `request_type`, `due_at`; `database/updates/2026-11-data-policy.sql`) and appear in Admin > Messages and Admin > Data protection.
* "Why we ask for this" notes (`partials/privacy-note`, wording in Pages > Privacy Policy > Notes next to forms) on 16 form templates; newsletter forms have a consent tick box (`partials/newsletter-consent`), enforced when the form sends `consent_notice=1`; the consent time is `subscribers.subscribed_at`.
* Admin > Data protection (`settings.manage`): request counters and list, retention reminders (nothing is deleted automatically), owner checklist.
* Cookies: no consent banner. The policy lists the strictly necessary cookies, the language cookie, `laf_portal`, third-party services (Google Fonts, Maps, ipwho.is) and, only while a Google Analytics ID is set in Settings, the analytics cookies (flagged in the admin checklist).
* Exam attempts record focus-loss events (`academy_exam_events`); the Academy row of the public table says exam records include the times a learner leaves the exam page.
* Test: `bin/selftest-data-policy.php` (registered in `bin/selftest.php`).
