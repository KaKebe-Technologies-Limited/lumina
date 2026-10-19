# Forms (form builder, public forms, submissions) - CONTRACT

> Owner request: "add a form feature that allows one to create forms and modernise forms to collect various data, job vacancies or any need; make it easily
> customisable in the admin." This file is the contract three parallel agents (PUBLIC, BUILDER, SUBMISSIONS) build on. The foundation (schema, models,
> services, registry, routes, reference controllers, selftests) is DONE and tested: `php bin/selftest-forms.php` (also run by `bin/selftest.php`).
> Read `docs/ARCHITECTURE.md` (conventions), `docs/DESIGN.md` (design system) and this file before writing code.

## 1. What exists

| Area | What | Where |
|---|---|---|
| Schema | `forms`, `form_submissions`, `form_files`, guarded `jobs.form_id` (additive, idempotent) | `database/schema.sql` (end of file), `database/lumina-live-import.sql` (rebuilt) |
| Models | `Form`, `FormSubmission`, `FormFile` (thin gateways) | `app/models/` |
| Services | `FormDefinition FormSettings ConditionEvaluator FormValidator FormService SubmissionService FormFileStore FormMailer FormTemplates FormExport FormStats FormRetention FormSpam FormShortcode FormText` | `app/services/Forms/` |
| Registry | all fixed public wording (buttons, errors, notices, e-mails, directory) | `app/config/pages/forms.php` (page `forms`) |
| Settings | group "Forms" (5 keys) | `app/config/settings_schema.php`, seeded in `database/seed.sql` |
| Abilities | `forms.manage forms.submissions forms.export forms.delete` | `app/config/app.php`, `app/config/ability_catalog.php` (group "Forms") |
| Routes | public + admin (pages and JSON API) | `app/routes/web_forms.php`, `app/routes/admin_forms.php` |
| Reference controllers | working, plain views; the agents own and refine them | `Web\FormController`, `Admin\FormsController`, `Admin\FormBuilderApiController`, `Admin\FormSubmissionsController` |
| Reference views | `views/web/forms/*` (`_form`, `_field`, `show`, `state`, `thanks`, `index`, `_embed`), `views/admin/forms/*` | |
| Assets | `assets/js/forms.js` (logic engine FINAL + wiring), `assets/js/admin-form-builder.js` (API helper), `assets/js/admin-form-submissions.js`; SCSS `scss/pages/_forms.scss`, `scss/admin/_forms-builder.scss`, `scss/admin/_forms-submissions.scss` | |
| CLI | `bin/forms-maintenance.php` (cron: retention + orphan files), `bin/selftest-forms.php`, `bin/selftest-forms-http.php`, `bin/dev/forms-truth.php` (shared truth table) | |
| Config | limits: `config('form_builder.*')` in `app/config/app.php` | |

NOT done by the foundation (agent work): the final public design and JS behaviour, the real builder UI, the final admin inbox UI, the shortcode hook in
the custom-page view, the Apply-button hook on the public job page, the job admin selector, the dashboard card. See section 20.

## 2. Data model (additive, idempotent)

`forms`: `id slug(unique) title description status(draft|published|closed|archived) definition(LONGTEXT JSON) settings(LONGTEXT JSON) version opens_at closes_at
max_submissions listed submissions_count template_key published_at created_by updated_by created_at updated_at`. Date-times are EAT (Africa/Kampala, UTC+3, no DST), plain DATETIME.
`form_submissions`: `id form_id(FK CASCADE) form_version answers(LONGTEXT JSON snapshot) search_text(MEDIUMTEXT) submitter_email context_type context_ref context_label
status(VARCHAR 30, default new) notes consent_at ip_hash(CHAR 64, keyed HMAC) user_agent created_at updated_at`; indexes `(form_id,status,created_at) (form_id,submitter_email) (status,created_at) (created_at)`.
`form_files`: `id submission_id(FK CASCADE) field_id original_name stored_path(relative to storage/private) mime size created_at updated_at`.
`jobs.form_id` INT UNSIGNED NULL (guarded ALTER, index + FK `ON DELETE SET NULL`): the optional custom application form of a job.

JSON is stored as text and validated in PHP only (works on MariaDB 10.4 and MySQL 8). Nothing is seeded except settings defaults, the `forms` page row and the
one-time upgrade of the built-in Editor role (section 18). `database/lumina-live-import.sql` was rebuilt (`php bin/build-import-sql.php`) and verified to upgrade a
database imported from the previous version without data loss and without overwriting owner edits.

## 3. The definition (`forms.definition`, JSON version 1)

```json
{"version":1,"pages":[{"id":"p3k9x2","title":"About you","description":"","logic":null,
  "fields":[
   {"id":"f8k2m9xa","key":"full_name","type":"text","label":"Full name","help":"","placeholder":"","required":true,"default":"","width":"half",
    "validation":{"min_length":2,"max_length":120,"pattern":"letters"},"logic":null},
   {"id":"fq1w2e3r","key":"guests","type":"number","label":"Guests","help":"","placeholder":"","required":true,"default":"0","width":"third",
    "validation":{"min":0,"max":10},"logic":{"match":"all","rules":[{"field":"f1a2b3c4","op":"equals","value":"yes"}]}},
   {"id":"fzz9yy8x","key":"cv","type":"file","label":"CV","help":"","required":true,"width":"full","file":{"types":["pdf","docx"],"max_mb":5,"max_files":1},"logic":null}
  ]}]}
```

* `FormDefinition::normalise($arrayOrJson)` is the ONLY gate (builder, templates, duplicate, decode): returns `['ok','definition','errors','warnings']`. It drops unknown keys, strips tags and `< >`
  from every text (labels are plain text), enforces the caps, repairs missing / duplicate ids, keys and option values, never throws. `errors` (path => message) only for what cannot be repaired:
  not an object, too large, > 12 pages, > 150 fields, > 200 options, unknown field type, unknown condition operator, more than 2 conditions.
* **Ids are stable**: `^[a-z][a-z0-9]{1,23}$` (new ones: `f` + 8 random chars, pages `p` + 6). A field keeps its id for life; answers are stored by id with a snapshot of label + type, so editing or deleting
  fields of a live form never breaks old answers. The browser creates ids with the same pattern (`FormDefinition::catalog()['id_pattern']`).
* **Keys** (`^[a-z][a-z0-9_]{0,39}$`, unique per form, never `form_title site date reference link submission_id`) are generated from the label when missing. They name e-mail placeholders (`{full_name}`) and CSV helpers; the builder may let admins edit them.
* Layout fields (`heading paragraph divider`) have `key:""`, are never required and always full width. `paragraph` stores its text in `text` (plain text, paragraphs by blank lines, <= 2000 chars).
* Page breaks: a page IS a "page break". The palette item "Page break" splits the current page: the fields after the break move to a new page (`FormDefinition` has no `page_break` field type).
* Caps (config `form_builder.*`): pages 12, fields 150, options 200, conditions 2, title 190, label 200, help 600, option label 120, definition JSON 512 KB, default 500, placeholder 160, rule value 200.

### 3.1 Field types (17) and their properties

| type | label in palette | answer value (normalised) | extra properties |
|---|---|---|---|
| `text` | Short text | string, one line (<= 500 default) | `placeholder default validation{min_length max_length pattern}` |
| `textarea` | Long text | string with line breaks (<= 5000 default, cap 8000) | same |
| `email` | E-mail address | lower-case string | `placeholder default validation{max_length}` |
| `phone` | Phone number | `+E.164` (`+256700123456`); accepts `0700 123456`, `+256 700-123456`, `256700123456`, `(0414) 123456`, foreign `+...` / `00...` | `placeholder default` |
| `number` | Number | string, `.` decimal (comma accepted) | `placeholder default validation{min max}` |
| `date` | Date | `YYYY-MM-DD` | `default validation{min max}` (`YYYY-MM-DD` or `"today"`) |
| `time` | Time | `HH:MM` | `default validation{min max}` |
| `select` | Dropdown | option value or `__other` | `options[{value,label}] allow_other other_label placeholder default` |
| `radio` | Single choice | option value or `__other` | `options allow_other other_label default` |
| `checkboxes` | Multiple choice | list of option values (may contain `__other`) | `options allow_other other_label default(list) validation{min max}` (number of picks) |
| `yes_no` | Yes / No | `"yes"` / `"no"` | `default` |
| `rating` | Rating 1-5 | `"1"`..`"5"` | - |
| `file` | File upload | list of cleaned names; the files go to private storage | `file{types max_mb max_files}` |
| `consent` | Consent box | `"yes"` or `""` (+ timestamp in the snapshot / `consent_at`) | - (`required` normally true) |
| `heading` `paragraph` `divider` | layout | none | `paragraph`: `text` |

Common: `id key type label help width(full|half|third) required logic`. "Other: please specify": `allow_other:true` adds a choice with value `__other` (constant `FormDefinition::OTHER`) and a text box posted as `f_<id>_other` (<= 200 chars, required when chosen).

### 3.2 Safe validation presets (NO free-form regular expressions)

`validation.pattern` is the NAME of a fixed, anchored, linear-time preset (`FormDefinition::PATTERNS`; the browser uses the same source via `clientPatterns()`): `letters` (names), `digits`, `letters_digits`,
`ug_phone` (after removing spaces, dots, hyphens, brackets: `^(?:\+?256|0)[347][0-9]{8}$`), `url` (`http(s)://host.tld...`, case-insensitive: `Https://` from a phone keyboard is fine), `nin` (NIN-like: `CM|CF` + 8 digits + 4 letters, spaces / case ignored), `year` (1900-2099), `no_links`
(refuses `http://`, `https://`, `www.` in any letter case). Unknown names are dropped. The sentence shown for a preset (`hint`) is editable in Admin > Pages > Forms > "Form: explanations of answer formats" (`pattern_hints.<name>`; `FormDefinition::patternHint()`), the built-in text is the default. Ids and option values named like members of `Object.prototype` (`constructor`, `__proto__`, `toString` ...) are never accepted (`FormDefinition::isReservedName`, replaced on normalise); `forms.js` also keys its maps by `Object.create(null)`. File types come from the safe list `pdf doc docx xls xlsx jpg png` (`jpeg` -> `jpg`); `max_mb` is capped by
the lowest of `form_builder.file_max_mb` (25), Site settings > Forms (default 5) and the server's `upload_max_filesize / post_max_size`; `max_files` <= 5.

### 3.3 Publish checklist

`FormDefinition::publishChecklist($definition, $settings, $meta)` -> `[{level:'error'|'warning', code, message, page, field}]`. Errors block publishing (and saving a form that is already live):
`no_fields empty_label empty_text empty_options range dangling_condition incomplete_condition condition_cycle condition_order(page-level) confirmation_field one_per_email redirect dates max`.
Warnings: `duplicate_label one_option empty_page condition_order(field-level) no_consent no_recipient no_retention`. A condition is dangling when its field is missing, empty, itself, or not a question.

## 4. Settings (`forms.settings`, JSON) and `FormService`

`FormSettings::normalise()` (same gate rules) - keys, defaults in `FormSettings::defaults($kind)`:

`kind` (general|job|intake|survey; picks default status labels / noindex), `intro` (sanitised rich text), `submit_label`, `success{mode:message|redirect, message_html, redirect_path}` (redirect = internal path only, never `/admin`, `/assets`, `/uploads`, `/storage`),
`closed_message full_message not_open_message unavailable_message` (plain text; empty = registry default), `consent{required,text}` (a form-level tick box, posted as `form_consent`), `privacy_note`, `one_per_email`, `spam` (light|standard|strict),
`throttle_form` (extra per-form limit per 10 minutes, 0 = off), `confirmation{enabled,email_field(field id),subject,body}`, `notify{enabled,emails[<=10],include_answers}` (**default link only**),
`theme{accent:orange|navy|teal|gold, layout:single|stepped, progress}`, `retention_days` (0 = keep), `seo{title,description,noindex}` (default noindex for job/intake), `status_labels{status:label}` (only differences), `allow_print`.
Row columns (not in the JSON): `title description slug status opens_at closes_at max_submissions listed`.

`FormService` (all static, arrays in / arrays out; a HYDRATED form = row + `def` + `cfg`, raw JSON columns removed, ints cast):

```
tz(): DateTimeZone   now(): DateTimeImmutable (EAT)   parseLocal(mixed): string|false|null   ('2026-10-19T14:30' | '2026-10-19 14:30:00' | '' -> null)
hydrate(array $row): array   find(int): ?array   findBySlug(string): ?array
slugProblem(string $slug, ?int $ignoreId): ?string     uniqueSlug(string $text, ?int $ignoreId): string
create(array $input, ?int $userId): {ok, form?, errors?}     // input: source blank|<template key>|duplicate:<id>, title, description?, slug?
duplicate(int $id, ?int $userId): {ok, form?, errors?}       delete(int $id): {ok, submissions, files}     // removes files first; jobs.form_id -> NULL
save(int $id, array $input, int $expectedVersion, ?int $userId, array $opts): {ok, changed?, form?, errors?, warnings?, checklist?, conflict?, not_found?, not_ready?, current?}
transition(int $id, string $action, ?int $expectedVersion, ?int $userId): {ok, form?, error?, errors?, checklist?, conflict?, not_found?}
checklist(array $form): list    availability(array $form, ?DateTimeImmutable $now, ?int $taken): {state, can_submit, visible, opens_at, closes_at, remaining}
adminList(array $filters, int $page, int $perPage): Pagination (items: hydrated + unread + availability)   statusCounts()   directory()   directoryEnabled()
jobForm(array $job): ?array   jobApplyUrl(array $job, array $form): string   resolveContext(array $form, ?string $jobSlug): ?{type,ref,label,ok,reason}   formChoices(): id => title
embedCode(array $form): {link, shortcode, button_html}   clientConfig(array $form): array   testSubmit(array $form, array $input, array $files): array   available(): bool   summary(): {new, forms, published, week}
```

**Sensitive settings need a second ability** (`FormService::deniedSettings`, enforced by `FormService::save` when the caller passes `opts['abilities'] = ['can_delete'=>..,'can_submissions'=>..]`; `FormBuilderApiController::save` always does, trusted internal callers such as selftests omit it; refusal = `forbidden`, HTTP 403 `{error:'forbidden'}`, nothing saved): (1) **`retention_days`**: setting it or LOWERING it deletes answers permanently, so it needs `forms.delete` (raising it or switching it off is free); (2) **`notify.include_answers`** can only be switched ON by a holder of `forms.submissions`, and while answers are e-mailed the recipient list can only be changed by such a holder (the answers stay behind the ability that guards them; the link-only mail may be re-addressed by any manager). Audit entries (ids and counts only): `form.retention` (from/to days; also written by the retention run itself with the number removed), `form.notify` (number of recipients, include_answers).

**Optimistic locking**: `save()` requires the version the editor loaded; a stale version returns `conflict` with the newest form and changes nothing; every successful change does `version = version + 1`; a save that changes nothing keeps the version.
`save()` refuses everything (nothing is written) when any part has errors, so autosave never half-saves. **A LIVE (published) form must stay complete**: a change that would raise a checklist error is refused with `not_ready`.
Editing a live form is allowed and applies at once (answers are safe because of the snapshot); autosave on a live form therefore only saves valid states.

**Status machine** (`transition()`): `draft -publish-> published -close-> closed -reopen-> published`; `published|closed -unpublish-> draft`; `draft|published|closed -archive-> archived -restore-> draft`.
`publish` / `reopen` need an error-free checklist. Availability (`availability()`): `draft`, `archived` (not visible: 404 for visitors); `not_open` (published, before `opens_at`), `closed` (status closed or at / after `closes_at`), `full` (`submissions_count` >= `max_submissions`; `SubmissionService::store` re-decides under a row lock). **`forms.submissions_count` counts every status EXCEPT spam** (`SubmissionService::recount`, called after a delete and after every `setStatus` / `bulkStatus` that changes a row), so the public page, the POST pre-check and `store()` use one rule and staff free places by marking junk as spam, else `open`.

## 5. Conditional logic (identical in PHP and JavaScript)

`logic = {"match":"all"|"any","rules":[{"field":"<field id>","op":"equals|not_equals|contains|filled|empty|gt|lt","value":"text"}]}` on a field or a page; `null` = always visible; 1 or 2 rules.

### 5.1 Semantics

* `compare` = trim + case-insensitive. `filled` = non-empty string (not only spaces) or list with a non-empty item (`"no"` of a yes/no IS filled). `empty` = not filled.
* `equals`: string: equal after compare, numbers compared as numbers when both sides are numeric (`5` equals `5.0`); list: exactly one item and it equals; an empty value equals `""`. `not_equals` = not equals.
* `contains`: string: the (non-empty) rule value occurs in the text; list: some item equals the rule value.
* `gt` / `lt`: strings only; both numeric -> numbers; else both `YYYY-MM-DD` or both `HH:MM` -> string order; otherwise false.
* A rule sees the **effective** value of its field: a field that is hidden (own logic fails or its page is hidden) has no value, so rules that refer to it see an empty answer. A hidden field is never required, never validated, and its posted value is **dropped** by the server. A page is visible when its own logic passes; its fields are hidden with it.
* A dependency cycle cannot be published (checklist error). If one exists anyway the fields are evaluated in document order with memoisation; the back-reference sees a hidden field. PHP and JS use the same algorithm.
* The browser evaluates for UX; the server evaluates AGAIN from the posted values (`ConditionEvaluator::resolve`) and ignores anything the browser claimed.

### 5.2 Values compared

As **entered** (trimmed, before cleaning): text-like fields: string; `checkboxes`: list of option values; `consent`: `"yes"`/`""`; `file`: list of file names; `null` (unknown / hidden) behaves like empty.

### 5.3 Truth table and browser mirror

`bin/dev/forms-truth.php` holds 49 cases (`[op, value, rule value, expected]`), run by `bin/selftest-forms.php` against `ConditionEvaluator::ruleMatches`. `php bin/dev/forms-truth.php` prints a snippet that runs the same cases against `window.LuminaFormsLogic.ruleMatches` in the browser console (0 failures was verified in the built-in browser; the `resolve()` chain / page / cycle cases too). `assets/js/forms.js` PART 1 is final: do not change the semantics.

## 6. Validation (`FormValidator`)

```
FormValidator::validate(array $definition, array $input, array $files = [], array $opts = []): {ok, errors, values, others, files, visible, pages_visible, error_pages, consent_at}
FormValidator::collectFiles(array $definition, Request $request): array<fieldId, list<upload array>>
```

Request field names: `f_<fieldId>` (checkboxes `f_<fieldId>[]`; file fields `f_<fieldId>` or `f_<fieldId>[]`), `f_<fieldId>_other`, `form_consent`, `job` (context), plus `_token`, `_hp_ts`, `website` (honeypot). `$opts`: `settings` (for the form-level consent), `messages` (override codes), `inspect_files` (false = names / sizes only), `now`.
`errors` = fieldId => first message, plus `_form` (whole-form problems: > 256 KB of answers, > 40 MB of files) and `_consent`. `values` only has VISIBLE input fields. Messages come from the registry (`errors.*`, editable): `required invalid min_length max_length pattern email phone number min_number max_number date min_date max_date time min_time max_time option other_required min_select max_select yes_no rating consent file_required file_too_many file_type file_size file_empty file_invalid file_upload form_too_big files_too_big one_per_email throttled expired generic`. Messages never echo hostile input.

## 7. Submissions

`SubmissionService` (static): `buildSnapshot searchText submitterEmail reference store hydrate find files file listColumns answerText where listFor each neighbours statusCounts newCount setStatus markRead bulkStatus saveNotes delete deleteMany recount flush`.

* `store($form, $validatorResult, $storedFiles, $meta)` does everything in ONE transaction holding `SELECT ... FOR UPDATE` on the form row: re-checks status / dates / maximum (spam rows do not count: the same rule as `forms.submissions_count`), enforces "one submission per e-mail" (spam rows do not block), inserts the submission (+ file rows), bumps `submissions_count`. Returns `{ok, id, reference, snapshot, email}` or `{ok:false, error: unavailable|draft|archived|not_open|closed|full|duplicate, field?}`. Proven with 7 parallel PHP processes racing for 3 places (exactly 3 win).
* **Snapshot** (`form_submissions.answers`): one entry per VISIBLE question in form order: `{id,key,type,label,page,value,text}` (+ `other` for "Other", `at` for consent). `text` is human readable (option LABELS at that time, `Yes`/`No`, `4 / 5`, file names). Files: `value=[{name,size}]`; the bytes are in `form_files` matched by `field_id`. History survives later edits of the form.
* `search_text` = the `text` of all answers + the context label (used by the inbox search; wildcards `% _` escaped; every word must occur).
* **Statuses**: `new read starred shortlisted rejected accepted archived spam` (`FormSubmission::STATUSES`). `new` is set on creation; staff move freely between all of them (no strict machine). Labels are editable per form (`settings.status_labels`); job forms say "Hired" for `accepted`. Opening a NEW submission marks it READ (`markRead`, never touches other statuses). Counts/badges: unread = status `new`.
* **Inbox** (`listFor($formId, $filters, $page, $perPage)`): filters `status|statuses[] q from to context_ref sort(created_at|id|status|submitter_email) dir`; columns = first five non-file questions (`listColumns`) + date + status; `neighbours()` gives prev (newer) / next (older); bulk: `bulkStatus`, `deleteMany` (forms.delete). The reference controller's "All" tab hides archived and spam.
* **Detail**: every answer with its label from the snapshot, files (authenticated download), internal notes (plain text <= 5000), status, prev / next, print view, delete. Context line "Applying for: ...".
* **Summary** (`FormStats`): `overview` (total, week, month, by_status, unread, first/last), `perDay($formId, 30)` (zero days included, EAT), `choiceTallies($form)` per dropdown / radio / checkboxes / yes-no / rating (counts, share, rating average; options removed later still counted with their old label).
* **Privacy**: personal data. Only admin screens guarded by `forms.submissions` read it. The audit log gets ids and counts only (`form.submission.status`, `.notes`, `.delete`, `.bulk`, `.file`, `.print`, `form.export` ...); never answers, names or addresses; never in the application log, the sitemap or any public JSON. The IP is stored only as a keyed HMAC-SHA256 (64 hex).

## 8. Public forms

Routes (`app/routes/web_forms.php`): `GET /forms` (404 unless Site settings > Forms > "Show a public list" is on), `GET /forms/{slug}`, `POST /forms/{slug}` (CSRF stateless token, `throttle:60,600`), `GET /forms/{slug}/thanks`.

### 8.1 Page states
* `draft` / `archived` / unknown slug: **404** (signed-in staff with `forms.manage` can preview a not-live form: banner + disabled submit).
* `not_open` / `closed` / `full`: HTTP 200 + noindex + the friendly notice (`views/web/forms/state.php`): the form's own message (settings) or the registry default (`state.*`, `{opens}`/`{closes}`/`{title}`).
* `?job=<slug>` (forms attached to a vacancy): shown as "Applying for: <title>" and carried in a hidden `job` input; an unknown / closed / unlinked job shows the `job.closed_text` notice instead of the form.
* SEO: title / description from the form (`seo.title`, `seo.description`, form description); robots `noindex,nofollow` when `seo.noindex`, when the form is not open, after errors, for the thank-you page and for previews; canonical `/forms/{slug}`; indexable otherwise. Notice pages (closed / full / not open) use the form's own description as meta description. Posted values are re-shown as plain strings: only multiple-choice questions keep a list, a list posted for any other name becomes empty (never cast to a string).

### 8.2 Submit flow (`Web\FormController::submit`, order is final)
1. 404 when not visible; not open -> notice (JSON 409). 2. `FormSpam::check` (section 8.4): silent failures answer with a normal success (thanks page / `{ok:true}`) and store nothing; `expired` / `throttled` are visible errors. 3. Verify the `job` context (a failure answers AJAX clients with 409 JSON `{error:'job'}`, `forms.js` reloads to show the closed-vacancy notice; browsers get the notice page). 4. `FormValidator::validate` (conditions re-evaluated server-side); errors re-render the page with HTTP 422 (values kept, files cannot be kept) or JSON `{ok:false,error:'validation',errors:{fieldId:msg},error_pages:[pageId],summary}` for `Accept: application/json` / `X-Requested-With`. 5. `FormFileStore::storeAll` (all or nothing). 6. `SubmissionService::store` (transaction); on failure the stored files are deleted; `duplicate` is a field error on the e-mail question. 7. `FormMailer::notifyStaff` + `confirmSubmitter` (never block the submission). 8. Redirect to `/forms/{slug}/thanks` (flash holds the reference once) or the configured internal redirect path; JSON clients get `{ok:true,redirect,reference}`.
Hostile input is never echoed inside error messages and is escaped everywhere it is shown again.

### 8.3 Markup contract (`views/web/forms/_form.php`, `_field.php`; JS hooks in `forms.js`)
`<form class="lf" method="post" enctype="multipart/form-data" novalidate data-lf data-lf-layout="stepped|single" data-lf-progress="1|0" data-lf-mode="live|preview|embed|print" data-lf-config='{FormService::clientConfig}'>` containing `csrf_field()`, `honeypot_field()`, hidden `job`, the error summary `[data-lf-summary] role=alert tabindex=-1`, the progress block `[data-lf-progress-bar]` (`role=progressbar aria-valuenow`), one `<section class="lf__page" data-lf-page="<page id>" data-lf-logic='{...}'>` per page, each question wrapped in `<div class="lf__field lf__field--full|half|third" data-lf-field="<id>" data-lf-type="<type>" [data-lf-required] [data-lf-logic='{...}']>` with `label for="q-<id>"` (groups: `fieldset` + `legend`), help `id="h-<id>"`, error `<p id="e-<id>" data-lf-error>`, `aria-describedby`, `aria-invalid`, `required`; buttons `[data-lf-prev] [data-lf-next] [data-lf-submit] [data-lf-print]`, draft note `[data-lf-draft]` + `[data-lf-draft-clear]`. Input names as in section 6. The page works WITHOUT JavaScript (all steps visible, server validation).
PUBLIC agent builds: multi-step navigation with a progress bar (semantics + live step text), Back / Next with validation of the current page, inline validation on blur + error summary focus on submit, keep answers in the page while stepping (no data in URLs), the draft in `sessionStorage` (key `config.draftKey`, cleared on submit, never for file fields), conditional logic via `LuminaFormsLogic`, "Other" text box toggling, file hints / size checks, print-a-blank-copy, AJAX submit with the JSON contract, reduced-motion respect, 44 px targets, WCAG AA, mobile first. No inline JS (CSP `script-src 'self'`).

### 8.4 Spam protection (`FormSpam`, no CAPTCHA)
Honeypot field (`config forms.honeypot_field`, must stay empty), signed timestamp `_hp_ts` (minimum fill time 2 / 3 / 6 s for light / standard / strict; too fast = **visible** HTTP 422 `too_fast` with the editable `errors.too_fast` text, never a fake success: a form re-shown after a validation error keeps the visitor's ORIGINAL timestamp - `Honeypot::field($keep)` / `honeypot_field($keep)`, view var `hpTs` - so fixing one field and sending at once works; a list posted for `_hp_ts` or the honeypot name is a silent failure, not a 500), stale page (> 7 h: `expired`, HTTP 422), per-visitor flood limit (Site settings > Forms, default 10 per 10 minutes, x2 light, /2 strict) and optional per-form limit -> `throttled` (429), strict: more than 3 links in the answers -> silent. Stateless CSRF tokens last 6 hours (a longer-open page is refused by the router with 403 "Page expired": the JS should offer to reload; the draft in `sessionStorage` survives).

### 8.5 Careers integration
`jobs.form_id` (NULL = built-in form, unchanged). `FormService::jobForm($job)` returns the form only while it is published, open and not full - otherwise the job page keeps showing the built-in application form ("falls back gracefully"). The Apply button of a job with a custom form links to `FormService::jobApplyUrl($job, $form)` = `/forms/{form slug}?job={job slug}`. The context is server-verified (`resolveContext`: job exists, active, not past its closing date AND `jobs.form_id` = this form), stored in `context_type='job'`, `context_ref`, `context_label` and shown in the inbox ("Applying for: ..."). Registry: `job.applying_for`, `job.apply_button`, `job.closed_text`, `job.careers_button`.

## 9. Files

Private storage only: `storage/private/forms/YYYY/MM/<32 hex>.<ext>` (0640; `storage/` is denied by Apache and by the router's allow-list). Accepted: `pdf doc docx xls xlsx jpg png` (per field a subset). Checks (`FormFileStore::inspect`, content not name): upload error codes, size (field limit <= server limit), safe name (no NUL, no dangerous part such as `.php.pdf`, `.exe`, `.svg`, macro formats), extension allow-list, finfo MIME (tolerates libmagic failures), magic bytes, `<?php` / `<script` in the head, `PdfValidator::inspect($path, $max, true)` for PDFs (STRICT mode: no JavaScript / launch actions / submit-form, not encrypted, `%%EOF`; every `/ObjStm` object stream is inflated - FlateDecode only, size-capped, anything else or a predictor or a stream that does not inflate is refused - and scanned the same way; `/GoToR`, `/GoToE`, embedded files, `/XFA`, `/RichMedia`, `/Movie`, `/Sound` and an `/OpenAction` that is a `/URI` action are refused; plain `/URI` link annotations stay allowed because every CV with a link has them), `getimagesize` + pixel limit for pictures (re-written without EXIF / GPS / XMP / text chunks by `ImageScrubber`, trailing bytes cut), Office files go through `OfficeInspector` (no `zip` extension needed): **.docx / .xlsx** = a real ZIP (end-of-directory record, sane entries: no `..`, no encryption, no zip bomb, no duplicate names), `[Content_Types].xml` of the right main part, and NO `vbaProject.bin`, macro-enabled content type, macro / dialog sheet, ActiveX, embedded OLE object, executable part or external workbook link, and no relationship that leaves the document except plain hyperlinks (remote templates); **.doc / .xls** = a real OLE2 file whose directory is walked: refused when it holds a `VBA` / `Macros` / `_VBA_PROJECT*` storage, `ObjectPool` / `Ole10Native` or encryption streams; an .xls is also refused when its workbook globals declare an Excel 4.0 macro sheet or a VBA module sheet. A scan can never prove a file free of an exploit for a bug in the viewer: downloads are attachments, and **staff should open applicants' files in a protected / sandboxed viewer**. `storeAll()` is all-or-nothing. `FormFileStore::stream($row)` is the only way out: attachment, `nosniff`, `no-store`, path must match `^forms/YYYY/MM/<hex>.<ext>`; the controller checks that form id, submission id and file id belong together. Files are deleted with their submission, with their form and by retention; `FormRetention::orphanFiles()` finds strays.

## 10. E-mail (`FormMailer`)

* **Staff notification**: to `notify.emails`, else Site settings > Forms > default list, else `notify_email`. Subject / body from the registry (`email.staff_*`). **Default = a link to the submission, never the answers** (`notify.include_answers` opts in). At most 60 per form per hour (config), Reply-To = the submitter's address.
* **Confirmation to the submitter**: only when `confirmation.enabled`, only to the address typed in `confirmation.email_field`, at most 3 per MAILBOX per hour (the limit key is `FormMailer::canonicalAddress`: lower case, `+tag` removed, Gmail dots removed, `googlemail.com` = `gmail.com`, so `name+1@` ... `name+9@` are one mailbox) and 120 per form per hour (config `form_builder.confirm_per_*`), so a form cannot be used as a relay. Subject / body = the form's own text or the registry defaults (`email.confirm_*`); plain text + simple HTML; Reply-To = the site e-mail.
* **Placeholders** `{name}` (single pass, unknown stay as typed): `{form_title} {site} {date} {reference} {submission_id}` and one per question key (`{full_name}`); `{link}` in the staff mail. Values from visitors are inserted as ONE line, <= 100 characters, web links removed. Every message goes through `Mailer` (header-injection safe). Failures are logged with ids only and never lose the submission.

## 11. Templates (`FormTemplates`)

Nine code-defined templates; "New form" copies one into an ordinary editable form (fresh ids, conditions and the confirmation e-mail question remapped): `job_application` (details page + application page: name, e-mail, phone, location, CV upload, cover letter, profile link, availability, referees, consent; job status labels; noindex), `volunteer_registration` (under-18 guardian fields by condition), `event_rsvp` (guests / food only if attending = yes), `contact_enquiry`, `partnership_enquiry`, `feedback_survey` (anonymous, indexable, e-mail only if a reply is wanted), `scholarship_application` (3 steps, optional NIN-like id, guardian by condition, supporting files), `beneficiary_intake` (sensitive, noindex), `training_registration`. `FormTemplates::keys() listing() get() instantiate()`. All pass `normalise()` and the checklist (no errors); wording is neutral (no organisation facts).

## 12. CSV export (`FormExport`, ability `forms.export`)
One row per submission: Reference, Submitted (EAT), Status (label), [Applying for], one column per question in form order (equal labels become `Label`, `Label (2)`), questions deleted from the form keep their own column from the snapshot, [Consent given at (EAT)]. Files = names only. Formula injection neutralised by `CsvExport`. `FormExport::response($form, $filters)` streams (two passes over `SubmissionService::each`), `table()` for tests. The reference controller exports every status unless a status tab is chosen.

## 13. Retention and maintenance (`FormRetention`)
`settings.retention_days` > 0 deletes submissions (and files) older than N days. Runs lazily when an admin opens the forms or answers screens (at most every 6 hours, stamp `storage/cache/forms-retention.stamp`) and from `bin/forms-maintenance.php` (cron, daily; `--dry-run`, `--quiet`; also removes orphan files older than a day; counts only in the output). Deletion is permanent, so **only a holder of `forms.delete` can set or shorten a period** (section 4) and every run that deletes something writes a `form.retention` audit entry (form id, number removed, days). **Uganda's Data Protection and Privacy Act, 2019**: forms collect personal data (and, for intake forms, potentially sensitive data about children and vulnerable people). The owner and their legal adviser must confirm the lawful basis, the retention period of each form, who may see the answers (roles), and the wording of the consent text and privacy note (`ui.privacy_note`, per-form `privacy_note`, link to `/privacy`). The module provides: consent field with timestamp, per-form retention, role-gated access, private files, no personal data in logs, deletion on request (delete a submission), and a checklist warning when personal data has no retention period.

## 14. Shortcode, directory, sitemap
* `[form slug="my-form"]` in a custom page body: `FormShortcode::expand($html)` AFTER sanitising (`echo FormShortcode::expand(rich($row['body']))`). A lone shortcode replaces its `<p>`; unknown / draft / archived forms and extra attributes render nothing / stay as text; a form that is closed (by status OR by date), full or not open yet shows its editable notice; at most 5 per page; markup = partial `web/forms/_embed`. **PUBLIC agent hooks it into `views/web/custom/show.php`.**
* `/forms` directory: Site settings > Forms > "Show a public list" (default OFF) + per form "Show in the public list" (`forms.listed`). `FormService::directory()`.
* `sitemap.xml` (`SitemapBuilder`, done): `/forms` and a form are listed only when the directory is on, the form is published, open, listed and not noindex; never submissions, thank-you pages or files.

## 15. Registry and settings keys
Registry page `forms` (Admin > Pages > Forms), `content('forms', '<section>.<key>')`: `hero.{eyebrow,title,lead}`, `directory.{open_button,closed_badge,full_badge,soon_badge,empty_title,empty_text,empty_button}`, `ui.*` (buttons, step counter, required / optional marks, other, select prompt, yes / no, rating, character counter, error summary, file texts, consent default, privacy note + link, draft notice, print texts, banners), `errors.*` (section 6, plus `too_fast`), `pattern_hints.*` (the sentence for each validation preset), `state.*` (`closed_title closed_text full_* not_open_* unavailable_* back_button contact_button closes_note remaining_note`), `success.*` (`title text reference_label reference_help another_button home_button`), `job.*`, `email.*`. `FormText::get('errors.required', ['label' => 'Email'])` reads them and fills `{placeholders}` in one pass. Settings (group "Forms"): `forms_directory_enabled`, `forms_default_notify`, `forms_max_upload_mb` (1-25, default 5), `forms_submit_limit` (default 10 per 10 min), `forms_retention_default_days` (default for NEW forms).

## 16. Routes and JSON API

Sidebar group "Forms" (order 38, `app/config/admin_nav/forms.php`): Forms (badge = new submissions of all forms), New form, Form answers (for roles that only hold `forms.submissions`). Every route is in the `auth` group with `can:` middleware AND the controller calls `authorize()`; CSRF is automatic (JSON: header `X-CSRF-TOKEN`).

| Method path | Ability | Controller |
|---|---|---|
| GET `/admin/forms` list, GET `/admin/forms/new` template picker, POST `/admin/forms` create, GET `/admin/forms/{id}/edit` builder, POST `/admin/forms/{id}/duplicate`, POST `/admin/forms/{id}/status` | `forms.manage` | `FormsController` |
| POST `/admin/forms/{id}/delete` | `forms.manage` + `forms.delete` | `FormsController` |
| GET `/admin/api/forms`, `/catalog`, `/{id}`; PUT `/{id}`; POST `/validate`, `/preview`, `/{id}/status`, `/{id}/test-submit`, `/{id}/duplicate` | `forms.manage` | `FormBuilderApiController` |
| DELETE `/admin/api/forms/{id}` | `forms.manage` + `forms.delete` | `FormBuilderApiController` |
| GET `/admin/form-answers`, `/admin/forms/{id}/submissions` (+ `/stats`, `/{sid}`, `/{sid}/print`, `/{sid}/files/{fid}`), POST `.../bulk`, `.../{sid}/status`, `.../{sid}/notes`, GET `/admin/api/forms/{id}/stats` | `forms.submissions` | `FormSubmissionsController` |
| GET `/admin/forms/{id}/submissions/export` | `forms.submissions` + `forms.export` | `FormSubmissionsController` |
| POST `/admin/forms/{id}/submissions/{sid}/delete`, bulk action `delete` | `forms.submissions` + `forms.delete` | `FormSubmissionsController` |

**JSON error format** (builder API): `{"error":"<code>","message":"...", ...}`; codes `validation` (422, `errors:{path:message}`), `not_ready` (422, `errors` + `checklist`), `conflict` (409, `current:{version,updated_at,updated_by}` + `data` = the newest form), `not_found` (404), `forbidden` (403), `bad_request` (400). Framework failures (401 not signed in, 403 CSRF, 404, 405) keep `{"error":"<Title>","status":N}`.

* `GET /api/forms/catalog` -> `{data:{version, types[{type,label,icon,input,group,flags}], widths, ops[{op,label}], patterns[{name,label,hint}], file_types[{ext,label}], other, limits{pages,fields,options,rules,label,help,text,option_label,title,file_max_mb,file_max_files,key,default,placeholder,rule_value}, id_pattern, key_pattern, templates[], status_labels, job_status_labels, kinds, accents, spam_levels, layouts, settings_defaults, timezone}}`.
* `GET /api/forms/{id}` -> `{data:{id,slug,title,description,status,version,listed,submissions_count,unread,opens_at,closes_at (datetime-local "2026-10-19T14:30", EAT),max_submissions,updated_at,availability{state,can_submit,visible,opens_at,closes_at,remaining},urls{public,edit,submissions},template_key,published_at,definition,settings,embed{link,shortcode,button_html},can{delete,submissions,export}}, checklist:[...]}`.
* `PUT /api/forms/{id}` body `{version*, autosave?, title?, description?, slug?, listed?, opens_at?, closes_at?, max_submissions?, definition?, settings?}` -> 200 `{data, changed, warnings[], checklist[]}` | 409 | 422. Only keys present are changed. Nothing is saved when anything has an error.
* `POST /api/forms/validate` body `{definition, settings?, slug?, opens_at?, closes_at?, max_submissions?, id?}` -> `{definition (normalised), settings, errors, warnings, checklist}`; writes nothing.
* `POST /api/forms/preview` body `{definition, settings?, title?, description?, id?}` -> `{html, document, checklist}`: `html` is the public `<form>` in mode `preview` (no CSRF field, submit disabled), `document` a complete HTML page (public CSS + `forms.js`) for an iframe `srcdoc`.
* `POST /api/forms/{id}/status` body `{action, version?}` -> `{data, checklist}` | 422 `not_ready` | 409 | 400.
* `POST /api/forms/{id}/test-submit` (JSON or multipart): the answers a browser would post (`f_<id>`, `f_<id>[]`, `f_<id>_other`, `form_consent`, files) and optionally `definition` / `settings` to test unsaved changes -> `{ok, errors, answers[], visible{}, pages_visible{}, error_pages[], stored:false}`. **Never stored, no e-mail.**
* `GET /api/forms/{id}/stats?days=30` -> `{overview, per_day[], tallies[]}` (counts only).

## 17. Builder requirements (BUILDER agent; `assets/js/admin-form-builder.js`, ES2020, no dependencies)
Single page, no reloads: left palette of the 16 field types + "Page break" (click to add, or drag); centre canvas with drag-and-drop reordering AND keyboard Move up / down buttons, duplicate, delete with confirm, ARIA live announcements (`role=status aria-live=polite`); right settings panel per field (label, help, placeholder, required, default, width, options editor with add / remove / reorder, bulk paste one per line, "Other: please specify", validation presets, file rules, conditions with one or two rules and AND / OR, page-level conditions); tabs for form settings (title, description, slug, status, open / close date-time EAT, maximum submissions with friendly closed / full messages, submit label, success message or redirect, consent text, one per e-mail, spam level, confirmation e-mail with the e-mail field picker and `{field}` placeholders, staff notification list, theme accent / layout / progress, retention, SEO, status labels, "show in the public list", noindex); live preview next to the canvas via `POST /api/forms/preview` (iframe `srcdoc`) with desktop / tablet / phone widths; autosave every 30 s with a clear status ("Saved 10:42", "Saving...", "Not saved: <reason>", "Conflict") using the version; on 409 offer "Reload the newest" / "Keep my changes and overwrite" (re-send with the new version); undo / redo of the last 30 actions (client side); the publish checklist from `/validate` (friendly list, links to the field); Publish / Unpublish / Close / Reopen / Archive; "Test submit" against `/test-submit`; copy link and embed code. A live form autosaves only valid states (a 422 `not_ready` is shown as "Not saved: fix ..."). Generate ids in the browser with `f` + 8 chars `[a-z0-9]` and keep them for life.

## 18. Abilities, roles, seed
`forms.manage` (admin, editor), `forms.submissions` (admin, editor), `forms.export` (admin), `forms.delete` (admin; always IN ADDITION to the ability of its screen; also needed to set or shorten a retention period). `forms.submissions` is also what lets a person e-mail the answers to staff (section 4). All four are in the catalogue (group "Forms", plain-language labels) so an admin can tick them for a custom role; none is admin-only. The seed upgrades the built-in Editor role (adds `forms.manage` and `forms.submissions`) ONLY while its stored list is still exactly the previous default, once (marker setting `migration_forms_editor_role_v1`); an owner-edited list is never touched (verified on an imported copy). Reserved slugs now include `forms`. Form slugs additionally refuse `thanks embed print preview submit form forms list index api static`.

## 19. Ownership (nobody edits outside their list; shared files are the architect's)
* **PUBLIC agent**: `Web\FormController` (refine), `views/web/forms/*`, `partials/form-*` (new names only; never edit `partials/form-field.php`), `scss/pages/_forms.scss`, `assets/js/forms.js` (PART 2), shortcode hook in `views/web/custom/show.php` (additive: `FormShortcode::expand`), Apply-button hook on `views/web/careers/show.php` (additive; `FormService::jobForm` / `jobApplyUrl`; keep the built-in form when null), the sitemap is done.
* **BUILDER agent**: `Admin\FormsController`, `Admin\FormBuilderApiController` (keep URLs / shapes), `views/admin/forms/*` (not `submissions/`), `scss/admin/_forms-builder.scss`, `assets/js/admin-form-builder.js`, the optional "Application form" selector in the job admin form (`views/admin/jobs/form.php` + `JobController` saving `jobs.form_id`, validated with `FormService::formChoices()`).
* **SUBMISSIONS agent**: `Admin\FormSubmissionsController`, `views/admin/forms/submissions/*`, `scss/admin/_forms-submissions.scss`, `assets/js/admin-form-submissions.js`, the dashboard card "New form submissions" (`FormService::summary()`), badge hook (done in the nav fragment), bin integration.
* **FROZEN** (architect): schema, seed, models, `app/services/Forms/*`, registry `forms.php`, settings schema, abilities / catalogue, `scss/main.scss`, `scss/admin.scss`, routes files, `admin_nav/forms.php`, `docs/FORMS.md`, selftests, `bin/forms-maintenance.php`. Need a change there? Report the exact file and change.

## 20. Commands
```
/c/xampp/php/php.exe bin/setup.php                      # apply schema + seed (idempotent, no --fresh)
/c/xampp/php/php.exe bin/selftest-forms.php             # 400+ checks incl. HTTP (needs the site at http://localhost/lumina/)
/c/xampp/php/php.exe bin/selftest.php                   # everything
/c/xampp/php/php.exe bin/dev/forms-truth.php            # JS snippet for the browser truth-table check
/c/xampp/php/php.exe bin/forms-maintenance.php --dry-run
/c/xampp/php/php.exe bin/build-import-sql.php           # rebuild database/lumina-live-import.sql after schema / seed changes
```
Tests create `zz-selftest-forms ...` forms, throw-away users `zz-forms-*@example.test` and roles `zz-forms-*` with random passwords, and delete them (and their mails, audit rows, login attempts, files) at the end.

## 21. Known notes / risks
* Production hosts: `upload_max_filesize` / `post_max_size` limit uploads (the builder shows the effective maximum); a POST larger than `post_max_size` arrives empty and is refused as a CSRF failure (403) - the JS should check file sizes before sending.
* No GD locally: pictures are scrubbed in pure PHP (not resized). `zip` extension is not required (Office packages are checked structurally).
* Mail: nothing is queued; confirmation / staff mails are sent during the request through `Mailer` (SMTP driver on production). A slow SMTP server slows the submit request.
* Known limits of the file checks: a PDF `/OpenAction` that points (by reference) to a URI action inside a compressed object stream is not told apart from a normal link annotation; an Office document can still carry an exploit for a bug in Word / Excel (open applicants' files in protected view). `submissions_count` leaves spam out, so the admin forms list shows the answers that count.
* A live form edited by autosave changes at once (by design). Duplicate / template forms start as drafts.
* A classic web-shell one-liner in an uploaded file can be reset by some anti-virus tools while it travels over localhost (the HTTP selftest uses a harmless payload for that reason).
* Integration notes (final pass): the staff mail carries `Reply-To: <submitter>` by design, so checks that look for the confirmation must anchor on the `To:` header; the confirmation limit (3 per address per hour) survives between test runs, so the HTTP selftest clears it for its own addresses; the automatic page description of a custom page strips `[form ...]` shortcodes.
