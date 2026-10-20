# Membership registration - CONTRACT

> Read `docs/ARCHITECTURE.md` and `docs/DESIGN.md` first; this file only adds the membership feature. The membership CARD, headshot, proof of payment and private page are in `docs/MEMBERSHIP_CARD.md`. Two agents build on it in parallel:
> the **WEB agent** (public pages) and the **ADMIN agent** (admin screens). The **architect** files (schema, models, services, registry, settings,
> routes, nav fragment) are **frozen**: call them, do not copy or edit them. Missing something? Add your own file and list the gap in your report.

Owner request: *a tab for members registration; people who want to be part of the organisation must register; all key information for
registration; the fee configurable from the admin end.*

Everything is **additive and idempotent**: three new tables (plus three guarded `ADD COLUMN`s on `members`, section 13), no existing data touched, so re-importing `database/lumina-live-import.sql`
(built by `php bin/build-import-sql.php` from `schema.sql` + `seed.sql`) upgrades a live database without data loss.

---------------------------------------------------------------------------------------------------------------------

## 1. What exists (architect, done and tested)

| Area | Files |
|---|---|
| Schema | `database/schema.sql` (end of file): `membership_types`, `members`, `member_payments` |
| Seed | `database/seed.sql` (end of file): membership settings, pages rows `membership` (nav order 52, header + footer) and `membership-terms` (not in menus), three PLACEHOLDER types (only while `membership_types` is empty) |
| Models | `app/models/MembershipType.php`, `Member.php`, `MemberPayment.php` (generic `Model` API + constants) |
| Services | `app/services/Membership/MembershipService.php` (rules), `MembershipApplication.php` (validation + creation + staff edit), `MembershipMail.php` (e-mails) |
| Settings | `app/config/settings_schema.php` group **Membership** (`membership_*`) |
| Abilities | `app/config/app.php`: `members.manage` (admin, editor), `members.delete` (admin), `members.types` (admin), `members.card` (admin: the card designer, see `docs/MEMBERSHIP_CARD.md`) |
| Registry | `app/config/pages/membership.php` (all copy, form labels, option lists, confirmation, e-mail templates), `membership-terms.php` (terms text) |
| Privacy | `app/config/pages/privacy.php`: block "Membership registration" (DRAFT, flagged for owner / legal review; its own toggle-able block appended after the notice list, not a row of the list) |
| Routes | `app/routes/web_membership.php`, `app/routes/admin_members.php` |
| Admin nav | `app/config/admin_nav/members.php` (group "Membership", badge = pending applications) |
| Stubs | `Web/MembershipController.php` (+ `views/web/membership/*`), `Admin/MembersController.php`, `Admin/MembershipTypesController.php` (+ `views/admin/members/*`, `admin/membership-types/*`), `scss/pages/_membership.scss`, `scss/admin/_members.scss`, `assets/js/membership.js`, `assets/js/admin-members.js` |
| Tests | `bin/selftest.php` -> `bin/selftest-membership.php` (about 150 membership checks, including 6 approvals at the same instant in separate processes through `bin/selftest-membership-child.php`; creates and removes its own data) |

The public page already renders (bare). The submit flow already works end to end (`MembershipController::submit` is a correct reference
implementation: validate, duplicate guard, store with fee snapshot, e-mails, redirect to the signed confirmation link). The admin list and
detail screens render so the sidebar works; every other admin action is a stub that redirects with "not built yet".

## 2. File ownership

| Owner | Owns exactly |
|---|---|
| **WEB agent** | `app/controllers/Web/MembershipController.php`, `app/views/web/membership/*`, `scss/pages/_membership.scss`, `assets/js/membership.js` (conditional fields), the public confirmation + terms pages, the public e-mail *sending calls* (the templates and `MembershipMail` are architect-owned) |
| **ADMIN agent** | `app/controllers/Admin/MembersController.php`, `Admin/MembershipTypesController.php`, `app/views/admin/members/*`, `app/views/admin/membership-types/*`, `scss/admin/_members.scss`, `assets/js/admin-members.js`, the CSV export, the printable receipt view, the dashboard tile |
| **ARCHITECT (frozen)** | schema, seed, models, `app/services/Membership/*`, registry files, `settings_schema.php`, `config/app.php`, routes, `admin_nav/members.php`, `scss/main.scss` + `scss/admin.scss` imports, `bin/selftest*.php`, this file |

Rebuild CSS after any `.scss` edit: `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py`.

---------------------------------------------------------------------------------------------------------------------

## 3. Data model

### `membership_types` (the FEE CONFIGURATION, Admin > Membership types & fees)
`id, slug (unique), name, description, fee_amount DECIMAL(12,2) NULL, joining_fee DECIMAL(12,2) NULL, currency CHAR(3), period ENUM(one_time, monthly, quarterly, annual, lifetime), eligibility, sort_order, is_active, max_members NULL, created_at, updated_at`
* `fee_amount`: **NULL = fee to be announced** (public wording "Fee to be announced"), **0 = free**, otherwise the amount. Same for `joining_fee` (NULL or 0 = no joining fee).
* A fee change affects **only new applications**: each member keeps its own snapshot (below).
* A type with members cannot be deleted (deactivate it: it leaves the website and the form but old members keep working).
* Seed: three placeholders ("Individual Member", "Youth & Student Member", "Organisation / Partner Member") with `fee_amount` NULL. They are inserted only while the table is empty, so a re-import never brings back a type the owner deleted.

### `members` (applications and members)
| Column | Notes |
|---|---|
| `application_ref` | unique, `LAF-APP-YYYY-NNNNN` (random 5 digits, collision-retried; the prefix is setting `membership_number_prefix`) |
| `membership_number` | unique, NULL until the first approval, in the configurable format `{PREFIX}-{TYPE}-{YY}-{SEQ}` (default, e.g. `LAF-IND-26-0001`: sequential per type and year; older `LAF-YYYY-NNNN` numbers stay unchanged; see `docs/MEMBERSHIP_CARD.md`) |
| `type_id` | FK `membership_types` (RESTRICT) |
| fee snapshot | `type_name, fee_amount, joining_fee, fee_currency, fee_period` copied from the type **at submit time** (`MembershipService::feeSnapshot()`); staff may set a fee that was NULL |
| `status` | `pending, approved, rejected, suspended, expired, cancelled` |
| personal | `full_name, gender (female, male, prefer_not_to_say), date_of_birth, nationality, id_number NULL` |
| contact | `email (lower-case), phone, whatsapp NULL, country, region (district/region), city (sub-county/city), address NULL (village/address)` |
| livelihood | `occupation_status, occupation_other NULL, education_level, organisation NULL` |
| participation | `interests` (JSON list of option keys), `skills NULL, availability, heard_about, heard_about_other NULL, motivation` |
| guardian (under 18) | `guardian_name, guardian_phone, guardian_relationship, consent_guardian_at` (all NULL for adults) |
| emergency | `emergency_name, emergency_phone, emergency_relationship` |
| payment | `payment_method` (key of the editable list), `payment_reference NULL` (text), plus an optional proof-of-payment file (private, `members.proof_*`, see `docs/MEMBERSHIP_CARD.md`) |
| consent | `consent_terms_at, consent_privacy_at, declaration_at, consent_updates (0/1), consent_ip, user_agent, consent_terms_ver, consent_privacy_ver` (12-character fingerprints of the terms and privacy texts as they read at submit time) |
| admin | `internal_notes, status_reason (500), status_changed_at, status_changed_by, approved_by (FK users), approved_at, start_date, expiry_date, anonymised_at` |
| indexes | `status+created_at`, `email`, `type_id+status`, `expiry_date`, unique `application_ref`, unique `membership_number` |

`expiry_date` NULL = never expires (`one_time`, `lifetime`). **Terms of dates:** `expiry_date = start_date + period` (annual +1 year,
quarterly +3 months, monthly +1 month, month ends clamped). The membership is valid *up to* that date and counts as expired **on** it.

### `member_payments`
`id, member_id (FK RESTRICT), receipt_number (unique, NULL until received), purpose (joining, membership, renewal, other), amount, currency, method, reference, status (received, pending, waived), paid_at, term_start, term_end, recorded_by, notes`
* **Balance** (`MembershipService::paymentSummary()` and the SQL twin `paymentSql()`): `charges = joining_fee + fee_amount (snapshot) + every renewal row` (a `renewal` row is a CHARGE added by `renew()`; money that arrives for it is recorded as a `membership` payment, or by marking the pending renewal row received: nobody can give a new payment the purpose `renewal`, so a charge is never counted twice); `paid` = rows `received`; `waived` = rows `waived`; `balance = charges - paid - waived` (negative = credit). Only rows in the member's `fee_currency` are added up.
* Member payment status: `tbd` (no fee set, nothing paid), `free`, `unpaid`, `partial`, `paid`.
* A `received` payment gets its receipt number (`LAF-RCT-YYYY-NNNN`) immediately; the number is permanent, and a payment that has one cannot be deleted.

---------------------------------------------------------------------------------------------------------------------

## 4. Public form: fields, rules, options

Input names equal column names, except the tick boxes. **Do not re-implement the rules**: `MembershipApplication::rules($input)` builds them for the
given input (the age decides whether the guardian block is required, the answer "other" whether the free text is required, the setting whether
the ID is read) and `MembershipApplication::process()` runs everything.

| Field (input name) | Rule | Notes |
|---|---|---|
| `type_id` | required, integer, an **active** type with a free place | `<select>` of active types; show its fee + period |
| `full_name` | required, 2-150 | |
| `gender` | required, `female|male|prefer_not_to_say` | labels: `options.gender_*` |
| `date_of_birth` | required, `Y-m-d`, not in the future, age <= 120 | show the age; under 18 -> guardian block |
| `nationality` | required, <= 80 | |
| `id_number` | optional, <= 60, **only read and shown when setting `membership_collect_id` = 1** (default off) | `MembershipService::collectsId()` |
| `email` | required, valid, <= 190 | stored lower-case; duplicate guard on open applications |
| `phone` | required, phone, <= 40 | |
| `whatsapp` | optional, phone | |
| `country`, `region`, `city` | required, <= 80 / 120 / 120 | |
| `address` | optional, <= 255 | |
| `occupation_status` | required, key of list `occupation` | key `other` -> `occupation_other` required (<= 190) |
| `education_level` | required, key of list `education` | |
| `organisation` | optional, <= 190 | |
| `interests[]` | optional array of keys of list `interests` | program pillars (`program:<slug>`, from the programs table) + extra keys (`volunteering, mentorship, partnerships`) |
| `skills` | optional, <= 2000 | |
| `availability` | required, key of list `availability` | |
| `heard_about` | required, key of list `heard` | key `other` -> `heard_about_other` required |
| `motivation` | required, 20-3000 characters | |
| `guardian_name`, `guardian_phone`, `guardian_relationship` | **required when under 18** | name <= 150, phone, relationship <= 80 |
| `consent_guardian` | **required (ticked) when under 18** | stored as `consent_guardian_at` |
| `emergency_name`, `emergency_phone`, `emergency_relationship` | required | |
| `payment_method` | required, key of list `payment_methods` | |
| `payment_reference` | optional, <= 120 | "only if you already paid" |
| `consent_terms`, `consent_privacy`, `declaration` | required, ticked | timestamps stored |
| `consent_updates` | optional tick | when ticked **and** the address is new, a `subscribers` row (source `membership`) is created; an address that opted out is never switched back on |

Option lists: `MembershipService::options('gender'|'occupation'|'education'|'availability'|'heard'|'payment_methods'|'interests')` returns `key => label`.
Labels and the editable lists live in Admin > Pages > Membership (section "Registration form: choices"); keys are lower-case `[a-z0-9_]`; the key `other` asks for free text.
`MembershipService::optionLabel($list, $key)` is safe for keys the owner removed later.

**Progressive enhancement (WEB agent, `assets/js/membership.js`):** show the age next to the date of birth; show the guardian fieldset + consent when under 18
(`MembershipService::ADULT_AGE`); show `occupation_other` / `heard_about_other` only for the key `other`; show the fee line of the chosen type
(`form.fee_line`). All of it is optional comfort: the server validates everything, and the form must work without JavaScript (render the guardian block
whenever the old input says "minor", and keep conditional fields visible when JS is off).

**Honeypot / spam:** `honeypot_field()` + route middleware `honeypot`, `throttle:5,600` (per IP). Duplicate guard: an **open** application
(`pending`, `approved`, `suspended`) with the same e-mail creates nothing and gets the generic `form.duplicate` message, which reveals no status or reference.

**Closed:** setting `membership_open` = 0 -> `/membership/register` shows the `closed.*` notice instead of the form and the POST is refused (`process()` returns `closed`).

### The reference flow (what `MembershipController::submit` does; keep it)
```php
$res = MembershipApplication::process($request->all(), ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]);
// ['ok'=>false,'closed'=>true]            -> flash form.err_closed, back to the form
// ['ok'=>false,'errors'=>[field=>msg]]    -> $this->fail($res['errors'], 'membership/register')   (messages are the editable form.err_* copy)
// ['ok'=>true,'duplicate'=>true]          -> flash form.duplicate, redirect /membership (no details, no e-mail)
// ['ok'=>true,'member'=>row]              -> MembershipMail::staffNotice($row); MembershipMail::applicationReceived($row);
//                                            redirect MembershipService::confirmationPath($row['application_ref'])
```

### Confirmation page
`GET /membership/confirmation/{ref}?t=<exp>.<sig>`: the token is an HMAC of the reference (30 days, `MembershipService::confirmationPath/Url/verifyConfirmationToken`).
Without a valid token: 404 (or the `confirmation.invalid_*` copy with status 404). The controller also limits attempts per IP (`RateLimit`, 60 per 10 minutes).
Show only: first name, reference, type, fee (snapshot), payment instructions, next steps. **Nothing else** from the record. `noindex,nofollow`.

### Payment instructions
`MembershipService::paymentInstructions()` = setting `membership_payment_instructions`, else `donate_bank_details` + `donate_mobile_money`; `''` when none (then show `confirmation.payment_none`; hide the `payment` section on `/membership`).

### Terms
`/membership/terms` renders registry page `membership-terms` (`terms.body` is rich text; **the default is a placeholder** for the owner to replace). The form's "I agree" label contains `{terms_link}` and `{privacy_link}` (replace them with links, escaping the link text).

---------------------------------------------------------------------------------------------------------------------

## 5. Status workflow (use `MembershipService`, never write `status` yourself)

| From | Allowed next | Notes |
|---|---|---|
| `pending` | `approved`, `rejected`, `cancelled` | first approval assigns `membership_number`, `start_date` (today or given), `expiry_date` (from `fee_period`), `approved_by/at`; checks `max_members` |
| `approved` | `suspended`, `expired`, `cancelled` | `renew()` keeps it approved |
| `suspended` | `approved`, `expired`, `cancelled` | reinstating needs a free place, and refuses a lapsed term (mark it expired first, then renew) |
| `expired` | `cancelled` | comes back **only** through `renew()` (which needs a free place) |
| `rejected` | `pending` | reopen (needs a free place) |
| `cancelled` | `pending` | reopen (needs a free place) |

A **reason** (`status_reason`, <= 500) is required when moving to `rejected`, `suspended` or `cancelled`. Every transition is audit-logged by the service
(`member.status`, ids/refs/statuses only). `syncExpired()` flips lapsed `approved` members to `expired` (call it when the members list loads; idempotent).

---------------------------------------------------------------------------------------------------------------------

## 6. Service API (`App\Services\Membership\...`, all static)

### `MembershipService`
| Signature | Purpose |
|---|---|
| `isOpen(): bool`, `collectsId(): bool`, `defaultCurrency(): string`, `prefix(): string`, `contactEmail(): string`, `paymentInstructions(): string` | settings |
| `options(string $list): array`, `optionLabel(string $list, ?string $key): string`, `decodeInterests(?string): array`, `interestLabels(?string): array`, `copy(string $blockKey, string $fallback = ''): string` | editable lists + registry copy |
| `statusBadge(string): [label,tone]`, `paymentBadge(string)`, `summaryBadge(string)` ; constants `STATUS_LABELS`, `STATUS_TONES`, `TRANSITIONS`, `PERIOD_LABELS_ADMIN`, `PURPOSE_LABELS`, `PAYMENT_STATUS_LABELS` | admin display (tones work with `partials/admin/status-badge` and `.badge--<tone>`) |
| `periodLabel(string $period): string`, `formatMoney($amount, string $cur): string`, `formatAmount(...)`, `feeLabel($amount, $cur, $period = 'annual', bool $withPeriod = true)`, `typeFeeLabel(array $type)`, `memberFeeLabel(array $member)`, `joiningFeeLabel(array $row, bool $isType = false)` | public + admin money wording ("Fee to be announced" / "Free" / "UGX 50,000 per year") |
| `ageOn(string $dob, ?string $on = null): int`, `isMinor(string $dob, ?string $on = null): bool`, `computeExpiry($start, string $period): ?string` | age / expiry math |
| `generateApplicationRef(?int $year = null)`, `assignMembershipNumber(int $memberId)`, `assignReceiptNumber(int $paymentId)`, `nextSequence(...)` | numbers (collision-safe; unique indexes decide) |
| `confirmationPath(string $ref)`, `confirmationUrl(string $ref)`, `verifyConfirmationToken(string $ref, string $token)` | signed confirmation link |
| `capacity(array $type): [taken,left,full]`, `feeSnapshot(array $type): array` | types |
| `canTransition($from,$to)`, `allowedTransitions($from)`, `changeStatus(int $id, string $to, array $opts = []): array` | workflow. `$opts`: `reason`, `user_id`, `start_date`, `expiry_date` -> `['ok','error','message','member','number_assigned']` |
| `renew(int $id, array $opts = []): array` | new term + pending renewal payment row. `$opts`: `user_id, start_date, amount, notes` -> `['ok','error','message','member','payment']` |
| `syncExpired(): int` | mark lapsed members expired |
| `recordPayment(int $memberId, array $input): array`, `updatePayment(int $paymentId, array $input): array`, `deletePayment(int $paymentId): array`, `receiptData(int $paymentId): ?array` | payments. `$input`: `amount*, status* (received|pending|waived), method (key of payment_methods), reference, paid_at, purpose, currency, term_start, term_end, notes, user_id` -> `['ok','errors'=>[field=>msg],'payment','message']` |
| `paymentSummary(int|array $member): array`, `paymentSql(string $alias = 'm'): array` | balance in PHP and as SQL expressions (`charges, paid, waived, balance, status`) for filters/sorting/columns |
| `parseMoney(string): ?string`, `cents($amount): int` | amounts |
| `saveType(?int $id, array $input): array`, `deleteType(int $id): array` | **fee configuration**. `$input`: `name*, slug, description, fee_amount ('' = to be announced, 0 = free), joining_fee, currency, period*, eligibility, sort_order, is_active, max_members` -> `['ok','errors','id','type']` |
| `pendingCount(): int`, `dashboardTile(): ?array`, `flush()` | counters |

### `MembershipApplication`
`rules(array $input, bool $forPublic = true)`, `process(array $input, array $meta): array` (see 4), `update(int $id, array $input, ?int $userId = null, bool $allowFee = false): array`
(staff correction: same rules without consents; optional `internal_notes`; with `$allowFee` the submitted `fee_amount, joining_fee, fee_currency, fee_period`
change the member's snapshot, e.g. to set a fee that was "to be announced"; fee keys that are **not submitted** are left alone) -> `['ok','errors','member','changed']`.
Also `line()`, `text()` (single/multi-line cleaning).

### `MembershipMail`
`applicationReceived(array $member)` (capped: 2 per address per day, 120 per hour), `staffNotice(array $member)`, `approved($member)`, `rejected($member)`, `paymentReceived($member, $payment)`,
`send(string $template, array $member, array $extra = [])` (`ack|approved|rejected|payment`; admin "resend"), `render(string $template, array $member, array $extra = []): [subject, body]`.

---------------------------------------------------------------------------------------------------------------------

## 7. Routes

### Public (`app/routes/web_membership.php`)
| Method | Path | Handler | Notes |
|---|---|---|---|
| GET | `/membership` | `index` | `page.published:membership`; indexable; in the sitemap |
| GET | `/membership/register` | `register` | `noindex,follow`; `?type=<id>` preselects a type |
| POST | `/membership/register` | `submit` | `honeypot`, `throttle:5,600`, CSRF automatic |
| GET | `/membership/confirmation/{ref}?t=` | `confirmation` | signed, `noindex,nofollow`, not in the sitemap |
| GET | `/membership/terms` | `terms` | indexable; page slug `membership-terms` |

### Admin (`app/routes/admin_members.php`; all under `auth`)
| Method | Path | Ability |
|---|---|---|
| GET | `/admin/members` | `members.manage` (search name/email/phone/ref/number; filters status, type, payment status, date range; sort; pagination; call `syncExpired()`) |
| GET | `/admin/members/export` | `members.manage` + `export.csv` (use `CsvExport`; add a permission note: the file contains personal data) |
| POST | `/admin/members/bulk` | `members.manage` (approve, reject, export selected) |
| GET / POST | `/admin/members/{id}` | detail / update details + notes (`MembershipApplication::update`) |
| POST | `/admin/members/{id}/status` `/renew` `/resend` | workflow / renewal / re-send an e-mail |
| POST | `/admin/members/{id}/payments`, `.../payments/{pid}`, `.../payments/{pid}/delete` | payments |
| GET | `/admin/members/{id}/payments/{pid}/receipt` | printable receipt (`receiptData()`, standalone page) |
| POST | `/admin/members/{id}/delete` | `members.delete` (admin; refuse when the member has payments - FK RESTRICT) |
| GET/POST | `/admin/membership-types` (+ `/create`, `/{id}/edit`, `POST /{id}`, `/{id}/toggle`, `/{id}/delete`) | `members.types` (admin only) |
| GET/POST | `/admin/settings` (tab "Membership") | `settings.manage` (existing screen) |

Sidebar group **Membership** (order 35): *Members* (badge = pending applications) and *Membership types & fees*. Highlight keys for `render(..., ['nav' => ...])`: `members`, `membership_types`.

**ADMIN checklist (members list):** search (name, email, phone, ref, number), filters (status, type, payment status via `paymentSql()['status']`, date range on `created_at`),
whitelisted sort, pagination (`perPage()`), bulk approve / reject (loop `changeStatus()`; reject needs one shared reason) / export, CSV with `CsvExport`.
**Detail:** all submitted data (decode interests with `interestLabels()`, show option labels with `optionLabel()`), status panel (only `allowedTransitions()`; reason field), payments panel
(summary, add payment with the payment-method list, receipt links), renewal, resend e-mail, internal notes, delete (admin).
**Types manager:** list with member counts (`MembershipType::memberCounts()`); form fields as in `saveType`; show "Fee not set: the site says 'Fee to be announced'" while `fee_amount` is NULL; warn that the
three starter types are placeholders to edit or delete; explain "a fee change applies to new applications only". Deleting a type with members is refused by `deleteType()` (offer Deactivate).
**Dashboard card:** add to `DashboardController::index()` after the inbox tiles: `if ($t = \App\Services\Membership\MembershipService::dashboardTile()) { $tiles[] = $t; }` (minimal additive change; the tile array has the same shape as the others).

Audit: the service already writes `member.status`, `member.renew`, `member.payment`, `member.payment_delete`, `member.update`, `member.expire`, `membership_type.create|update|delete`.
Controllers add `member.export`, `member.delete`, `member.resend`, `member.bulk` themselves. **Log ids and references only, never names, e-mails, phones, dates of birth or ID numbers.**

---------------------------------------------------------------------------------------------------------------------

## 8. Settings (Admin > Site settings > Membership)

| Key | Type | Default | Meaning |
|---|---|---|---|
| `membership_open` | boolean | **`0`** | accept applications; **off until the owner switches it on** (no personal data is collected while it is off); off = "closed" notice instead of the form |
| `membership_currency` | text | `UGX` | default currency of new types |
| `membership_number_prefix` | text | `LAF` | the `{PREFIX}` of the member number format (default `LAF-IND-26-0001`), `LAF-APP-...`, `LAF-RCT-...` (letters/digits, upper-cased) |
| `membership_payment_instructions` | textarea | empty | how to pay; falls back to the donation bank/mobile-money details |
| `membership_collect_id` | boolean | `0` | ask for a national ID / passport number (data minimisation: off) |
| `membership_contact_email` | email | empty | shown to applicants, reply-to of membership e-mails; falls back to `email_primary` |

**Deviation from the request:** `membership_terms` (rich text) is **not** a setting, because the existing settings form has no rich-text type.
The terms are rich text in Admin > Pages > **Membership terms** (`terms.body`), editable like every other page.

## 9. Registry keys (`content('membership', '<section>.<key>')`)

Every visible word comes from here (labels, hints, options, errors, e-mails). Sections of `membership`:
`hero` (eyebrow title lead cta_label cta_url image) - `why` (toggle; eyebrow title intro items[icon,title,text]) - `eligibility` (toggle; eyebrow title text) -
`types` (eyebrow title intro fee_tba free joining_fee eligibility_label apply_label full_label period_one_time|monthly|quarterly|annual|lifetime empty_title empty_text full_title full_text) -
`how` (toggle; eyebrow title steps[title,text]) - `payment` (toggle; eyebrow title intro) - `faq` (toggle; eyebrow title items[question,answer]; DB `faqs` group `membership` first, like the other pages) -
`cta` (toggle; title text button_label button_url) - `closed` (lead title text button_label button_url) - `register` (eyebrow title lead breadcrumb) -
`form` (required_note choose sections_label legend_* fee_line age_line minor_note payment_note submit_label sending_label error_title err_* duplicate) -
`fields` (`<name>_label`, `<name>_help` for: type name gender dob nationality id_number email phone whatsapp country region city address occupation occupation_other education organisation interests skills availability heard heard_other motivation guardian_name guardian_phone guardian_relationship emergency_name emergency_phone emergency_relationship payment_method payment_reference) -
`consent` (terms_label terms_link_text privacy_label privacy_link_text updates_label declaration_label guardian_consent_label) -
`options` (gender_female gender_male gender_undisclosed + repeaters occupation education interests_extra availability heard payment_methods) -
`confirmation` (title lead ref_label type_label fee_label payment_title payment_none next_title next_text keep_note home_label invalid_title invalid_text) -
`emails` (ack|staff|approved|rejected|payment `_subject` / `_body`, name_fallback, no_expiry, no_renewal).
Page `membership-terms`: `hero` (eyebrow title lead), `terms` (updated_label updated body privacy_label back_label).
Heading fields accept `[[double brackets]]` for the underline (`highlight()`). `{first_name}` in `confirmation.lead`; `{fee}` in `form.fee_line`; `{age}` in `form.age_line`; `{amount}` in `types.joining_fee`.

**Nothing is invented:** the benefits (`why.items`) reuse the owner's own wording about volunteering, mentorship and partnership; eligibility and terms are neutral placeholders
(the registry `help` and the admin tell the owner to replace them); no fee is invented (types start with `fee_amount` NULL = "Fee to be announced").

### E-mail templates and placeholders
`{name} {first_name} {site} {ref} {type} {fee} {joining_fee} {payment_instructions} {contact_email} {url} {status_url} {membership_number} {start_date} {expiry_date} {renewal_due} {receipt} {amount} {method}`. `{expiry_date}` reads "No expiry" and `{renewal_due}` "None, this membership does not expire" for a lifetime or one-time membership (the date itself is the day the membership counts as expired).
The applicant's name is reduced to a safe form (letters, digits, `' . -`; no links or line breaks), because the mail goes to an address nobody has proven to own.
`{url}` is the admin link (staff template), `{status_url}` the signed confirmation link.

---------------------------------------------------------------------------------------------------------------------

## 10. Privacy and security rules (both agents)

* Personal data: date of birth, address, phone numbers, optional ID, minors' guardian details. **Never** public, never in the sitemap or JSON-LD, never in `storage/logs` or `audit_log.details` (ids and references only).
* Admin access only for roles `admin` and `editor` (`members.manage`); fee configuration, settings and member deletion are admin only. No anonymous access to any member data; the confirmation page needs the signed token.
* `/membership/register` and the confirmation page are `noindex`; `/membership` and `/membership/terms` are indexable.
* Collect the ID number only when `membership_collect_id` = 1. Headshot and proof of payment are PRIVATE files (`storage/private/members`, streamed only through controllers, consent timestamp, deleted on erasure): rules in `docs/MEMBERSHIP_CARD.md` section 11.
* CSRF on every POST, `e()` on every output, prepared statements only (the service already does), CSV only through `CsvExport`.
* Uganda's Data Protection and Privacy Act, 2019 may require the foundation to register with the Personal Data Protection Office and to give specific notices. **The owner must confirm this with counsel**; the privacy page text "Membership registration" and the terms page are DRAFTS flagged for owner / legal review, including the data-retention period placeholder. The public site makes no legal claim.

## 11. Commands

| Task | Command |
|---|---|
| Apply schema + seed to the dev DB (idempotent) | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` |
| Tests | `/c/xampp/php/php.exe bin/selftest.php` (must print `ALL CHECKS PASSED`) |
| SCSS | `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py` |

Dev DB state after testing: 3 placeholder types, 0 members. The selftest cleans up everything it creates.

## 12. Owner to-do before opening registration (nothing below was invented)

1. Set the fee of each membership type (Admin > Membership types & fees) and rename or delete the three placeholder types. Until a fee is set the site says "Fee to be announced".
2. Replace the benefits, eligibility text and the **membership terms** (Admin > Pages > Membership / Membership terms) with the foundation's own wording.
3. Enter the payment instructions (Site settings > Membership), or confirm the bank / mobile-money details on the Donations tab.
4. Review the privacy page section "Membership registration" (Admin > Pages > Privacy Policy, last block "Membership registration": it is its own block, so it survives edits of the notice list) and fill in the data-retention period; confirm with counsel whether the Data Protection and Privacy Act, 2019 requires registration or notices.
5. Decide whether to ask for a national ID / passport number (off by default), then turn on `membership_open` (Site settings > Membership). Registration ships **closed**.

## 13. Review fixes (what changed after the independent review)

**Deploy order:** import `database/lumina-live-import.sql` **first (or together with the code upload)**. New code against an old schema answers 500 on `/membership` and on Admin > Members; the Membership menu entry hides itself while the table is missing (`nav.requires`), but the routes do not.
Upgrading is idempotent: three guarded `ALTER TABLE members ADD COLUMN` (`consent_terms_ver`, `consent_privacy_ver`, `anonymised_at`, same pattern as `programs.status`) and nothing else; tested by importing an older copy of the schema, then the new file twice.

| Area | Rule now |
|---|---|
| Numbers | Approvals are serialised with a named lock (`GET_LOCK`) taken **before** the transaction, and `assignNumber()` always tries a higher candidate after a clash, so parallel approvals no longer fail with a 500. Receipt numbers were already safe (autocommit). |
| Capacity | Moving INTO `pending` or `approved` from a status that holds no place (reopen, reinstate, `renew()` of an expired member) needs a free place; `typeHasRoom()` locks the type row first. |
| Renewal | An active member's new term cannot start before the current expiry; the new term must end in the future (and after the current expiry for an approved member); dates are limited to 2000-01-01 .. 30 years ahead (`validTermDate()`), so nothing overflows to year 10000. A **suspended** member with a lapsed term is marked expired first, then renewed. |
| Payments | `paid_at` is a real calendar date (no roll-over), not before 2000, not in the future for a received payment; a received payment is more than zero; the covered period ends after it starts; the payment's own stored method stays valid after the owner removes it from the list; `purpose = renewal` can only be created by `renew()` and never changed. Amounts accept commas only as proper thousands groups (`1,0,0` is refused). |
| Audit | `member.payment` entries carry amount, currency, status and purpose before and after (amounts are not personal data); `member.update` carries old and new values of the fee fields and of the guardian-consent time; `member.renew` carries the previous expiry. |
| Staff edit | Never creates a guardian-consent time (only the applicant's own tick does; the panel then reads "Not recorded"); clears it when the date of birth makes the member an adult. |
| Input | Free-text answers are cleaned **before** validation (control, zero-width and non-breaking-space-only values count as empty); an array posted for a single-value field is refused without a PHP warning; an e-mail address containing `? & = % # " < > ( ) , ; : [ ] \` or spaces is refused, and `Support::mailto()` percent-encodes the address. |
| Double submit | "Record a payment" and "Start a new term" carry a one-time token (`OnceToken`, kept in the staff member's session): a second post of the same form is refused with a warning. `admin.js` also blocks a second click on any POST form for 5 seconds. |
| Erasure | Admin only (`members.delete`), for rejected / cancelled / expired members (cancel first): `MembershipApplication::anonymise()` blanks identity, contact, guardian, ID, answers, notes, consent IP and browser, removes the newsletter row of this form and the free-text notes of the payments; the reference, number, fee snapshot, status, dates, consent times/versions and every payment row (receipts) stay. `anonymised_at` marks the row; it can no longer be edited, reopened or e-mailed. The retention period itself is still the owner's / counsel's decision. |
| Consent versions | `consent_terms_ver` / `consent_privacy_ver` hold a short fingerprint of the terms text and of the privacy notice at submit time, shown in the Consent record. The texts are editable and not versioned: the fingerprint only tells whether they changed later. |
| Choice lists | A row with a label but no usable "Short code" gets a code made from its label (a clash gets `_2`...); rows without a label are ignored; codes already in use never change. |
| Registry | `form.success_flash` was removed (it was never shown); new keys `closed.lead`, `types.full_title`, `types.full_text`, `form.sections_label`, `emails.no_expiry`, `emails.no_renewal`; the Membership FAQ group is listed in Admin > FAQs. |
| CSV | `CsvExport::cell()` keeps plain numbers (`-17111.00`, a credit) as numbers; everything else starting with `= + - @` is still guarded. |
| Layout | The members list is a card list below 1200 px and a table above (long names wrap); the receipt prints on one page (A4 and Letter) and keeps the signature with the text; text fields keep a visible focus ring (also in forced-colors mode). |

Known and left as designed: back-dating an approval is allowed (a past start date is how existing members are recorded; the member then shows as expired once the term has passed), and the membership terms/privacy texts have no revision table.
