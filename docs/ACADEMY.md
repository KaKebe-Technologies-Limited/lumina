# LAF Academy

A free ICT learning portal at `/academy`: courses made of modules, lessons and quizzes; learner accounts with e-mail confirmation; progress tracking; a final exam and a
certificate with a public number and a PDF; live virtual classes (Google Meet, Zoom, Microsoft Teams, free Jitsi or any https link); facilitator (teacher) accounts; full
administrator control. 31 starter courses at three levels (over 570 lessons and 2,000 quiz questions) plus a catalogue of free partner courses ship with the site.

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

## Starter courses

`app/config/academy/courses/NN-slug.php` (31 files; format and writing rules in `docs/ACADEMY-CONTENT.md`; check with `php bin/academy-validate-content.php`). Install from
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
`academy_enrollments`, `academy_progress`, `academy_attempts`, `academy_certificates` (keeps a snapshot of the name and the course title), `academy_classes`, `academy_partner_courses`.
Deleting a course removes its lessons, quizzes and the learners' progress in it; certificates stay valid. Deleting a learner removes their progress; their certificates stay valid.

## Code map

`app/services/Academy/` - `LearnerAuth`, `AcademyMail`, `CourseService`, `QuizService`, `QuizScript`, `CertificateService` (PDF via the membership-card `PdfWriter`, QR via `QrEncoder`),
`ClassService`, `ContentInstaller`, `AcademyAccess`. Public controllers `app/controllers/Web/Academy*`, routes `app/routes/web_academy.php`, views `app/views/web/academy/`.
Admin controllers `app/controllers/Admin/Academy*`, routes `app/routes/admin_academy.php`, views `app/views/admin/academy/`. Styles `scss/pages/_academy.scss`, `scss/admin/_academy.scss`.
Tests: `bin/selftest-academy.php` (public + learners) and `bin/selftest-academy-admin.php` (admin + facilitator limits), both part of `bin/selftest.php`.
