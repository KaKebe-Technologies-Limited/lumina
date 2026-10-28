# LAF Academy

A free ICT learning portal at `/academy`: courses made of modules, lessons and quizzes; learner accounts with e-mail confirmation; progress tracking; a final exam and a
certificate with a public number and a PDF; live virtual classes (Google Meet, Zoom, Microsoft Teams, free Jitsi or any https link); facilitator (teacher) accounts; full
administrator control. 41 starter courses at three levels (over 740 lessons and 2,700 quiz questions), challenges and hackathons with automatic winners, plus a catalogue of free partner courses ship with the site.

## For learners (public)

| Address | What |
|---|---|
| `/academy` | home: how it works, search / filter the course catalogue, next virtual classes |
| `/academy/courses/{slug}` | a course: about, what you will learn, the curriculum, what you need, further reading, "Enrol" |
| `/academy/register`, `/academy/login`, `/academy/forgot` | sign up (name, e-mail, password), sign in, forgotten password |
| `/academy/dashboard` | my courses with progress bars, my next classes, my certificates |
| `/academy/learn/{slug}/lesson/{id}` | the lesson player: course menu, lesson text, optional YouTube video, handout download, "mark as done" |
| `/academy/learn/{slug}/quiz/{id}` | a module quiz (practice) or the final exam: rules, attempts, start; then the attempt page and the result with the right answers and explanations |
| `/academy/classes` | virtual classes: upcoming and recorded; the join link is shown only to signed-in learners who may attend; add to calendar (.ics) |
| `/academy/certificate/{number}` | public check page of a certificate (and `/pdf` for the PDF) |
| `/academy/account` | change name / phone / country / password; delete the account |

How a course is completed: finish every lesson (button "Mark as done"), then pass the final exam (pass mark per course, default 70 %). The exam opens only after every lesson
is done (Settings switch). Passing it completes the course and issues the certificate (e-mailed too). A course without a final exam is completed by finishing the lessons.

Sign-up: e-mail + password (8+ characters). By default the address must be confirmed with an e-mailed link before the first sign-in (Settings > LAF Academy > "Learners must
confirm their e-mail address"; an administrator can also confirm a learner by hand). The screens never reveal whether an address has an account (a person who already has one is
e-mailed instead), sign-in is throttled per address + IP and per IP, links are one-time tokens whose SHA-256 only is stored, passwords are hashed with the site password hasher.

Quizzes are graded on the server. The page of a running attempt does not contain which answers are right; question and answer order are shuffled per attempt and remembered.
A "pick one" question is right with exactly the right answer, a "pick all that apply" question needs exactly the right set. Optional time limit (late answers count as blank) and
optional attempt limit per quiz.

## For staff (admin > LAF Academy)

Two abilities (Admin > Roles): **`academy.teach`** (the built-in role **Facilitator**) and **`academy.manage`** (administrators, everything).

* **Overview** - numbers, next classes, newest learners, quick links and the **Starter courses** installer (administrators).
* **Courses** - list, create, edit details (title, address, topic, level, icon, summary, description, outcomes, requirements, further reading links, pass mark, certificate on/off,
  status draft / published / archived), duplicate as a draft, delete. A course needs one published lesson to be published.
* **Curriculum** (per course) - modules (add, rename, reorder, delete), lessons (reading text with the rich editor, YouTube link, outside link, handout upload (PDF, Word, Excel,
  PowerPoint, CSV, text up to 20 MB, stored privately), time, show / hide, reorder, delete), a quiz per module and the final exam.
* **Quiz editor** - the quiz is plain text (see "Quiz format"); settings: title, pass mark, time limit, attempts. Errors are reported in plain words and nothing is lost.
* **Virtual classes** - title, description, course (or "every learner"), facilitator, platform, join link, passcode, date, time (East Africa Time), length, status, recording link;
  a button makes a free Jitsi room link; "E-mail the learners" sends the invitation (time + link to the class page, never the join link); a reminder is e-mailed automatically
  by `bin/academy-reminders.php` (cron) the number of hours set in Settings.
* **Learners** - list (search, status, course), profile (courses with progress, quiz results, certificates), add by hand (they get a link to choose a password), confirm the e-mail
  address, switch the account off / on, send a password link, enrol / remove from a course, issue a certificate by hand, delete, download CSV.
* **Certificates** - all certificates, search, withdraw (the public page says so) / reinstate, open the PDF, download CSV.
* **Facilitators** - create a teacher account (role Facilitator or any role holding "Teach in the LAF Academy"); a temporary password is shown once and must be replaced at first
  sign-in; reset the password, switch the account off / on. Give a facilitator a course by choosing them as the facilitator in the course details.
* **Settings > LAF Academy** - the portal on/off, sign-ups open, e-mail confirmation, exam opens after all lessons, certificate wording (organisation, statement, signatory, title), help
  e-mail, class reminder hours. **Pages > LAF Academy** - all the wording of the public screens.

**What a facilitator can do:** only their own courses (lessons, quizzes, publish, duplicate), the classes they run or that belong to their courses, and the learners enrolled in
their courses (read the progress, enrol / remove in their own courses). They see none of: other courses, the starter courses installer, certificates, facilitator accounts,
learner deletion, exports, settings. Every controller checks ownership (`AcademyAccess`), not only the menu.

## Confirmation e-mails and learner portraits

* **Delivery record.** `academy_learners.mail_sent_at` / `mail_error` remember whether the last confirmation or password e-mail was accepted by the mail server (and why not). The learner list shows "Link sent 27 Oct", "Could not send: ..." or "No e-mail sent yet" next to "E-mail not confirmed".
* **Admin buttons** (Admin > Academy > Learners, administrators only): **Send confirmation e-mail** on a learner (a fresh link, valid 7 days, no per-address limit), **Send confirmation e-mails** on the list (25 learners per press, those never e-mailed first, stops after 3 refusals in a row because mail hosts limit how many e-mails an hour), **Mark all as confirmed** (for when e-mail cannot reach people at all) and the existing "Mark e-mail as confirmed".
* **Portrait.** My account > My picture: JPG / PNG / WebP, at least 300 x 300, cropped square, stored privately in `storage/private/academy/learners/`. Private by default (the learner and the Academy team see it). With the consent box ticked the picture and the FIRST name and country appear in "Meet some of our learners" on the Academy page (`/academy/learners/{id}/photo` answers 404 without consent). Administrators can remove a portrait. Columns `photo_path`, `photo_consent_at` (the code works without them until the database update is imported).

## Certificates: design and approval

* **Design.** Every certificate (course and challenge) is drawn by `CertificateService::pdf()` in the design of the approved template `LAF_Certificate_Jessa_May_Lumintac_Ramo.pdf`: navy border, gold double frame with corner ornaments, logo, "Certificate of Completion", learner name, orange course title, a facts band (score, hours, date of issue, certificate number), the founder's signature, the gold seal and a QR code to the check page. The seal and the signature are copied from the template as raw PDF operators in `app/config/academy/certificate-art.php`; everything else is drawn with the dependency-free `PdfWriter`. Certificates are drawn when they are opened, so changing the design updates every certificate ever issued (old ones included). The wording and signatory come from Settings > Academy (the signature drawing is used while the signatory is empty or the founder).
* **Request and approval.** Setting **"Certificates must be approved before they are available"** (Settings > Academy, default on). Passing the final exam completes the course but does NOT issue anything by itself. The learner sees **"Request my certificate"** on the exam result, the course page and the dashboard. The request creates a certificate that WAITS (not available to the learner, not e-mailed, unknown to the public check page and PDF) and e-mails the Academy team (Settings notify e-mail). Only a learner who finished the course can ask, once per course, at most 10 times an hour.
* **Admin, on each learner.** Admin > Academy > Learners: a learner with a waiting request shows "1 waiting" and an **Approve certificate** button in the list; the learner's page has **Approve**, **Approve and e-mail**, **Preview** (the PDF) and **Decline** (with a message the learner receives; the request is removed and the learner may ask again). Admin > Academy > Certificates shows the same with a yellow banner, the **Waiting for approval** filter, **Approve all** (up to 100, optionally e-mailing each learner) and the envelope button to e-mail an approved certificate (again). The approver and the e-mail time are recorded (`approved_by`, `emailed_at`).
* **Not held for approval:** certificates issued by hand (Learners > Issue a certificate) and challenge certificates (issued when the team finalises the results): that action is the approval. With the setting off, the certificate is issued and e-mailed automatically when the course is finished (and a learner who finished earlier can still request it).
* **Columns** `academy_certificates.approved_at`, `approved_by`, `emailed_at`. When `approved_at` is added, every certificate issued before is approved once (backfilled), so old certificates keep working. Without the columns (code uploaded before the database update) every certificate counts as approved.

## Confirmation code and WhatsApp

* Every confirmation e-mail now also contains a **6-digit code** (valid 24 hours, 5 wrong tries, then a new one is needed). The learner types it on **Academy > Check your e-mail** (`/academy/check-email`) instead of opening the link; the page also appears after a sign-in attempt by someone who has not confirmed yet. Only a keyed hash of the code is stored (`code_hash`, `code_expires_at`, `code_attempts`).
* **WhatsApp (optional).** When the `.env` file holds `WHATSAPP_TOKEN` and `WHATSAPP_PHONE_ID` (+ `WHATSAPP_TEMPLATE`, `WHATSAPP_LANGUAGE`, `WHATSAPP_COUNTRY_CODE`, `WHATSAPP_COPY_BUTTON`, see `.env.example`) the sign-up form asks for a WhatsApp number (the same code is then sent there too), the "Check your e-mail" page offers "Get the code on WhatsApp", and administrators get **Send code on WhatsApp** on a learner who gave a number. The message is sent by the WhatsApp Business Cloud API with an approved **Authentication** message template (one `{{1}}` = the code, plus the standard "Copy code" button). Without the credentials the option is hidden everywhere.
* Limits: 3 WhatsApp codes per learner and 8 per connection per hour; numbers are stored as digits in `academy_learners.whatsapp`; numbers and codes are never written to the logs. Setup at Meta: developers.facebook.com > create an app with WhatsApp > add the number > create the template in WhatsApp Manager (category Authentication) > a permanent token from a system user.

## Starter courses

`app/config/academy/courses/NN-slug.php` (41 files; format and writing rules in `docs/ACADEMY-CONTENT.md`; check with `php bin/academy-validate-content.php`). Install from
Admin > LAF Academy > Starter courses, or `php bin/academy-install-content.php [--draft] [--only=slug]`. A course already installed is never changed; a starter course nobody has
enrolled in can be reset to the starter version. After installing, courses are ordinary courses: edit, hide or delete them.

Topics: computer basics, Windows and files, typing, Word, Excel, PowerPoint, internet and e-mail, Google Workspace, digital marketing, social media and Canva, online safety, job-ready
digital skills, HTML and CSS, Python, AI tools, computer maintenance and networking.

**Automatic first install.** The first time anyone opens `/academy` or the admin overview on a site with no courses, the starter courses (and the partner list) are installed once and
published (marker setting `academy_starter_done`, rate-limited, errors go to the application log). So an empty catalogue after the database import fixes itself on the first visit.
Levels: beginner, intermediate and advanced courses exist in every area (levels and prerequisites are described in `docs/ACADEMY-CONTENT.md`).

## Partner courses

Free courses on other platforms (Coursera, edX, Google, Microsoft Learn, Harvard CS50, freeCodeCamp, Khan Academy, Kaggle, Cisco Networking Academy, HubSpot, AWS, IBM SkillsBuild and
more) are listed at `/academy/partner-courses`, grouped by level, with a teaser on the Academy home page. The Academy only links out: summaries are written in our own words, nothing
is copied, links open in a new tab with `nofollow`, and a disclaimer says free access and certificate rules belong to each provider. Starter list: `app/config/academy/partners.php`
(installed by address, never twice). Admin > LAF Academy > Partner courses (administrators): add, edit, hide, delete, feature, load the starter list, and **Check links** (25 at a
time; the checker only accepts public hosts; an address that answers 404/410 is hidden from visitors until fixed). Table `academy_partner_courses`.

## Volunteer facilitators (applications)

Anyone can apply at `/academy/facilitate` (no account needed) to facilitate **one topic** as a **volunteer** (unpaid): name, e-mail, topic and the particular subject,
experience, motivation, availability, links, a **CV (required)** and up to **four supporting papers** (PDF or Word, 5 MB each; the content is checked, not only the extension).
The files are stored privately (`storage/private/academy`), open only for administrators, and are deleted with the application. One open application per e-mail address; 3 per
connection per day; honeypot and throttle on the form. The applicant and the team address (Settings > LAF Academy > support e-mail) get an e-mail.

Admin > LAF Academy > **Facilitator applications** (administrators): read everything, download the CV and papers, then **Approve** (creates the facilitator account
with a temporary password, shown once and, by default, e-mailed to the applicant, who must change it at first sign-in; an existing teaching account is linked instead),
**Ask for more information**, or **Decline**; each decision e-mails the applicant with your message. Internal notes are never sent. The approved topic is kept on the application and
limits which judged challenges that facilitator may score. Table `academy_facilitator_applications`.

## Challenges, hackathons and winners of the month

Admin > LAF Academy > **Challenges**: three kinds. A **quiz challenge** (timed, marked by the system), an **innovation challenge** and a **hackathon** (teams submit a project,
judges score it against weighted criteria). The administrator sets the title, topic, rules, prize, **dates** (applications open / close, start, end, and for judged challenges when
results are final), team size, maximum entries, whether entries are approved automatically or by staff, the number of winners and a minimum qualifying score, then **publishes**.
Ready-made drafts (six quiz challenges of 30 questions, an innovation challenge and a hackathon, `app/config/academy/challenges/*.php`) can be added with one click.

Flow for learners (signed in): *apply* (optional team, idea for projects, optional **photo** with a consent tick) -> entry approved (automatically or by staff, e-mailed) -> when the
challenge is live: **take the quiz** (one attempt, shuffled questions, the clock starts when pressed, graded on the server, late answers count as blank) or **submit the project**
(title, description, https link and/or PDF/Word/PowerPoint file; editable until the end) -> **results**.

**Winners are marked automatically.** When a quiz challenge ends, or when the judging deadline of a judged challenge passes and at least one project has been scored,
`ChallengeService::settle()` ranks the eligible entries (quiz: highest percentage, then the fastest finishing time; judged: the average of the judges' weighted totals, then the earliest
submission), marks the top N (who reach the minimum score) as winners ("1st place" ...), issues certificates and e-mails the entrants. It runs from the public pages, the admin overview
and `bin/academy-reminders.php` (cron). Administrators can also "Make the results now" or **reopen** the results (winners cleared, the challenge certificates withdrawn) to correct scores.
Judges (any staff member with "Teach in the LAF Academy", limited to their approved topic when they have one) score each project 0-10 per criterion at Challenges > Score.

**Certificates**: winners get a *certificate of achievement* ("Winner, 1st place"), other entrants at or above the pass score a *certificate of participation* (switchable per
challenge). They use the same numbers, PDF and public check page as course certificates (`academy_certificates.kind = challenge`).

**Winners of the month** (`/academy/winners`, teaser on the Academy home): winners grouped by the month the results were made, with their photo. A photo and the full name are shown **only
when the learner ticked the consent box** (otherwise first name and initial, no photo); staff can hide a photo. Photos are cropped square and stripped of metadata
(`ImageOptimizer::portrait`), kept privately and served only for published winners. Tables `academy_challenges`, `academy_challenge_entries`, `academy_challenge_judgments`.

Existing sites: on the first visit after the upgrade the marker `academy_starter_done` moves from 1 to 2 and the newer starter courses (numbers above 310) and the starter challenges
are added once.

## Quiz format

```
Q: What does CPU stand for?
* Central Processing Unit
- Central Program Utility
- Computer Personal Unit
= The CPU is the "brain" of the computer.
```
`Q:` question, `*` right answer, `-` wrong answer, `=` explanation, a blank line between questions. One `*` = pick one, several = pick all that apply. 2-8 answers, plain text.

## Deployment (Hostinger)

1. Upload the files (zip) to `public_html` and extract with overwrite.
2. phpMyAdmin: import `database/lumina-live-import.sql` (adds the Academy tables, the Facilitator role and the Academy page; safe to repeat).
3. Admin > LAF Academy > **Install all missing courses** (about 1 MB of text is written to the database; allow a minute).
4. Settings > LAF Academy: check "Learners must confirm their e-mail address" (needs working e-mail: Settings > E-mail / SMTP) and the certificate wording; fill in the signatory.
5. Optional cron for class reminders: `0,15,30,45 * * * * /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/academy-reminders.php > /dev/null 2>&1`
6. Create facilitators under LAF Academy > Facilitators.

## Data (additive tables, `database/schema.sql`)

`academy_courses`, `academy_modules`, `academy_lessons`, `academy_quizzes` (module_id NULL = the final exam), `academy_questions`, `academy_options`, `academy_learners`,
`academy_enrollments`, `academy_progress`, `academy_attempts`, `academy_certificates` (keeps a snapshot of the name and the course title), `academy_classes`, `academy_partner_courses`, `academy_facilitator_applications`, `academy_challenges`, `academy_challenge_entries`, `academy_challenge_judgments` (and three columns on `academy_certificates`: kind, award, challenge_id).
Deleting a course removes its lessons, quizzes and the learners' progress in it; certificates stay valid. Deleting a learner removes their progress; their certificates stay valid.

## Code map

`app/services/Academy/` - `LearnerAuth`, `AcademyMail`, `CourseService`, `QuizService`, `QuizScript`, `CertificateService` (PDF via the membership-card `PdfWriter`, QR via `QrEncoder`),
`ClassService`, `ContentInstaller`, `AcademyAccess`. Public controllers `app/controllers/Web/Academy*`, routes `app/routes/web_academy.php`, views `app/views/web/academy/`.
Admin controllers `app/controllers/Admin/Academy*`, routes `app/routes/admin_academy.php`, views `app/views/admin/academy/`. Styles `scss/pages/_academy.scss`, `scss/admin/_academy.scss`.
Tests: `bin/selftest-academy.php` (public + learners) and `bin/selftest-academy-admin.php` (admin + facilitator limits), both part of `bin/selftest.php`.

## Continue with Google

Learners can sign in or sign up with a Google account on the Academy sign-in and sign-up pages. No confirmation e-mail is needed (Google has already verified the address), which helps when the mail limit is reached. The button stays hidden until the credentials are in `.env`.

1. https://console.cloud.google.com > create or pick a project > **APIs & Services > OAuth consent screen** (External; app name, support e-mail; publish it to production so any learner can use it).
2. **Credentials > Create credentials > OAuth client ID > Web application**. Authorised redirect URI, exactly: `https://luminaafricafoundation.com/academy/google/callback` (add your local address, e.g. `http://localhost/lumina/academy/google/callback`, to test).
3. Put the two values in the live `.env`: `GOOGLE_CLIENT_ID=...apps.googleusercontent.com` and `GOOGLE_CLIENT_SECRET=...`.
4. Import `database/lumina-live-import.sql` (adds `academy_learners.google_sub`; safe to repeat). Without the column the code still works and matches by e-mail only.

Behaviour: a new Google user gets an account (name from Google, random locked password; "Forgot password" lets them set one later). An existing learner with the same e-mail is confirmed and linked. An address linked to another Google account, a switched-off account, or closed sign-ups are refused. Security: authorization-code flow with PKCE, `state` and `nonce`; issuer, audience, expiry and `email_verified` are checked. Code: `App\Services\Academy\GoogleSignIn`; test: `bin/selftest-academy-google.php`.

## Registration numbers and approval

* **Registration number:** every learner gets one automatically when the account exists, however it was made (sign-up form, Continue with Google, or an administrator): `LAF-AC-<year>-<account number, 6 digits>`, for example `LAF-AC-2026-000123`. Older learners got theirs from the schema migration. It is shown to the learner after sign-up, in the admin list (also searchable and in the CSV / Excel / Word / PDF exports) and on the learner page (`App\Services\Academy\LearnerRegistry`).
* **Acceptance does not depend on e-mail:** a new learner can start when the e-mail address is confirmed (link, 6-digit code, Google) **or** when an administrator approves the account. **Approve** is on every waiting row of Admin > LAF Academy > Learners, on the learner page ("Approve (no e-mail needed)") and, for everyone at once, "Approve all". The approval time and the administrator are recorded (`approved_at`, `approved_by`) and written to the activity log.
* **Every new account is announced:** it is written to the activity log (shown on the dashboard), an e-mail goes to the organisation address (best effort), and the dashboard tile **Academy learners waiting for approval** counts the waiting ones and opens the list filtered to them (Status: Awaiting approval).
* The sign-in message for a waiting learner says the account is waiting for approval and can be confirmed by e-mail or approved by the Academy team.
* Test: `bin/selftest-academy-registry.php`.

## Taking every certificate back for approval

Certificates already approved can all be put back to "waiting for approval" with one button: Admin > LAF Academy > Certificates > **Take back all N for approval** (shown while approved certificates exist). From then on nobody can open, download or check them until an administrator approves each one (or "Approve all"), and the e-mailed mark is cleared so they can be e-mailed again on approval. E-mails already sent cannot be recalled. It is written to the activity log (`academy.certificates.withdraw_all`). New certificates already wait for approval (Settings > LAF Academy). Code: `CertificateService::withdrawAllApprovals()`.

## When a learner passes: the certificate message

* **At once:** passing the final exam completes the course and **files the certificate for approval automatically** (the Academy team is e-mailed; with "certificate approval" off it is issued and e-mailed as before). The learner sees a success message on the result page, the course page and the dashboard: congratulations, the certificate is **waiting for approval and signing by the Academy administrator, and is shared only after that**, and to **check again after 48 hours**. The same message is e-mailed (`AcademyMail::certificateWaiting`).
* The wait shown to the learner is the setting `academy_cert_wait_hours` (default 48). Wording: `CertificateService::waitingNotice()` / `waitingShort()`.
* If filing fails for any reason (very rare), the learner still gets the "Request my certificate" button.

## Picture or selfie at sign-up

The sign-up form has an optional **picture** field: attach a photo, or press **Take a selfie** (camera in the browser; the picture is cropped to a square). It is saved with the new account, **private** (only the learner and the Academy team; no public consent is given at sign-up) and shows in the learner list and the learner page, so the Academy has a record of every learner. A problem with the picture never stops the sign-up (the learner is told and can add it later under My account). The site allows the camera for its own pages (`Permissions-Policy: camera=(self)`); the browser still asks the learner first. Code: `data-selfie` block in `register.php` + the end of `assets/js/academy.js`; storage and checks: `LearnerPortrait`. Tests: `bin/selftest-academy-registry.php`, `bin/selftest-academy.php`.
