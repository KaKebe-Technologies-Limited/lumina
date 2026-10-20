# Membership card, headshot, proof of payment and private page - CONTRACT

> Read `docs/ARCHITECTURE.md`, `docs/DESIGN.md` and `docs/MEMBERSHIP.md` first; this file only adds the membership-card feature.
> Three agents build on it in parallel (**CARD**, **DASHBOARD**, **STAFF**, section 2). The **architect** files (schema, seed, models, services, card engine,
> registry, settings, abilities, routes, nav, `scss/main.scss` + `scss/admin.scss` imports, selftests, this file) are **frozen**: call them, do not copy or edit
> them. Missing something? Add your own file and list the gap in your report.

Owner request: *when a member applies for registration, pays and is approved, generate an automatic membership card which is e-mailed to them; they upload their
headshot and proof of payment as they register and on their membership dashboard; use the sample PDF to choose the card format; make the card customisable in admin.*
The owner's sample is `docs/design-reference/membership-card-reference.pdf` (+ `.png`): **that is the card format** (720 x 414 pt landscape, white panel + navy panel).

Everything is **additive and idempotent** (new nullable columns, guarded `ALTER TABLE`, pristine-only seed), so importing `database/lumina-live-import.sql`
(rebuilt by `php bin/build-import-sql.php`) upgrades a live database without data loss and without overwriting the owner's edits.

---------------------------------------------------------------------------------------------------------------------

## 1. What exists (architect, done and tested: `bin/selftest-membership-card*.php`, about 200 checks, with and without GD)

| Area | Files |
|---|---|
| Card engine (pure PHP, no library) | `app/services/Membership/Card/`: `PdfWriter` (PDF 1.4), `CoreFontMetrics` (generated AFM widths), `QrEncoder` (QR v1-10), `CardLayout` (ONE layout), `CardPdf`, `CardPng` (GD), `CardData`, `CardSettings`, `CardRenderer` (the one door) |
| Services | `app/services/Membership/CardService.php` (issue / re-issue / revoke / e-mail / triggers / verification), `MemberFiles.php` (photo + proof), additions to `MembershipService.php` (number format, type codes, tokens, hooks, counters), `MembershipMail.php` (card mail, proof request, `{dashboard_url}`), `MembershipApplication.php` (token at creation, erasure, edit flag) |
| Core | `ImageOptimizer::portrait()` (headshot) and `::evidence()` (proof pictures); abilities `members.card` |
| Schema / seed | `database/schema.sql` (end of the membership block), `database/seed.sql` (71 settings + type codes), rebuilt `database/lumina-live-import.sql` |
| Registry | `app/config/pages/membership.php`: sections `uploads`, `dashboard`, `verify`, e-mails `proof_*`, `card_*`; `privacy.php` membership block mentions photo and proof |
| Routes | `app/routes/web_membership_card.php`, `app/routes/admin_membership_card.php` |
| Admin nav | `app/config/admin_nav/members.php`: Members, **Needs proof check** (badge), **Membership card**, Membership types & fees |
| Working references (replace freely, keep the rules) | `Web/MembershipDashboardController`, `Web/MembershipVerifyController`, `Admin/MembershipCardController`, `Admin/MembersController` card actions, views `web/membership/me/show.php`, `web/membership/verify/show.php`, `admin/membership-card/index.php`, `admin/members/_card.php`, `assets/js/membership-dashboard.js`, `admin-membership-card.js`, `admin-members-card.js`, SCSS `pages/_membership-dashboard.scss`, `admin/_membership-card.scss`, `admin/_members-card.scss` |
| Maintenance | `bin/membership-maintenance.php` (retention, stray files, expiry; daily cron) |
| Tests | `bin/selftest-membership-card.php` (engines), `-flow.php` (numbers, triggers, files, privacy, e-mail, scratch-database migration), `-http.php` (real HTTP), `-child.php` (parallel processes), `-fixes.php` (regressions of the review, section 15), `-staff.php` and `-designer.php` (all required by `bin/selftest.php`), `bin/selftest-data/qr-vectors.php` |

## 2. File ownership

| Owner | Owns exactly |
|---|---|
| **CARD agent** | `Admin/MembershipCardController.php`, `app/views/admin/membership-card/*`, `assets/js/admin-membership-card.js`, `scss/admin/_membership-card.scss`, the card e-mail templates (`MembershipMail::renderCard()` HTML), the per-type **code** field on the types screen (`admin/membership-types/form.php`, the controller already passes `code`) |
| **DASHBOARD agent** | `Web/MembershipDashboardController.php`, `Web/MembershipVerifyController.php`, `app/views/web/membership/me/*`, `verify/*`, `scss/pages/_membership-dashboard.scss`, `assets/js/membership-dashboard.js`, the **registration form additions** (headshot, proof, consent: section 12) in `Web/MembershipController.php` + `views/web/membership/register.php` + `assets/js/membership.js` (additive edits), the confirmation page link to the private page |
| **STAFF agent** | `Admin/MembersController.php` card / file actions (already working, refine), `app/views/admin/members/*` additions (`_card.php`, list **Card** column, filters `card` and `proof`), `assets/js/admin-members-card.js`, `scss/admin/_members-card.scss` |
| **ARCHITECT (frozen)** | everything else listed in section 1. **The trigger wiring is already done** inside `MembershipService` (approval, payment, renewal call `CardService::event()`), so the STAFF agent has nothing to wire |

Rebuild CSS after any `.scss` edit: `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py`. Tests: `/c/xampp/php/php.exe bin/selftest.php`
(add `-d extension=gd` for the GD paths; the XAMPP `php.ini` has GD commented out, so Apache and the CLI run **without GD** by default, and everything works that way).

## 3. Decisions (and where this differs from a literal reading of the brief)

1. **Format = the owner's reference**, block by block (geometry and colours read from the reference PDF's content stream; section 8). Only the font differs: the reference
   uses Carlito (sans) and Liberation Serif; the PDF core fonts Helvetica / Times are used instead (no font embedding, no licence, tiny files), with sizes scaled
   (`CardLayout::SANS_SCALE`) so each word takes the space it has in the reference.
2. **The card is a SNAPSHOT.** `CardService::issue()` stores what the card says (`members.card_snapshot`, JSON) and a private copy of the photo (`card_photo_path`); downloads and
   e-mails render from that snapshot with the LIVE status pill (active / expired / suspended / revoked). A photo or name change after issue only **flags** a re-issue
   (`card_needs_reissue`), exactly as the brief asks ("not an automatic change"). The brief's "PDF generated at send time" holds: the PDF is rendered when it is sent or downloaded.
3. **Settings are not in `settings_schema.php`** (like the shop): the 71 keys live in `CardSettings::DEFS`, seeded by `seed.sql`, edited only in the designer. The generic Site settings screen never shows them.
4. **Verification token = 32 hex characters (128 bits)** (keeps the QR at version 6; 41 x 41 modules), **private-page token = 64 hex characters (256 bits)**; separate columns, never equal.
5. **"Verify by number" box: NOT added.** A number alone would allow enumeration; a number plus a printed code would change the reference card. The QR link is the only public verification path; staff find members by number in the admin.
6. **Number format**: `{PREFIX}-{TYPE}-{YY}-{SEQ}` default; `{SEQ}` must be last (so a series is "fixed text + counter"), per type and per year as far as the format contains `{TYPE}` / `{YY}` / `{YYYY}`; invalid stored format falls back to the default. The old `LAF-YYYY-NNNN` numbers stay; the equivalent format `{PREFIX}-{YYYY}-{SEQ}` continues them.
7. **"Photo required" exists twice on purpose**: `membership_photo_required` (registration form, default ON) and `membership_card_require_photo` (no card is issued without a photo, default OFF: the card then shows the initials block like the sample).
8. **Trigger wiring is in `MembershipService`** (not in the controllers) so every path (admin, bulk, future importers) behaves the same.
9. **PNG preview** needs GD; text uses the bundled Liberation fonts (`assets/fonts/card/`, SIL OFL, metric-compatible with Helvetica / Times); without FreeType it falls back to GD bitmap fonts; without GD the dashboard simply shows no picture. The **PDF is authoritative**.
10. **"Valid until" is the last valid day** (`MembershipService::lastValidDay()` = expiry date minus one day), because a term counts as expired ON `expiry_date`: start 20 Oct 2026 + 1 year prints 19 Oct 2027, as in the reference. Card, verification page, e-mail and private page all use it; `card_valid_until` still stores the expiry DATE (used to detect renewals).
11. The router tolerates a trailing line break in a URL (`/membership/verify/<token>%0A` serves the valid page, because the captured token is still the right one); the services themselves are strict (`/D` regexes), see the flow tests.

## 4. Data model (additive; `database/schema.sql`)

`members` (all nullable or defaulted; `dashboard_token` and `card_verify_token` UNIQUE; indexes `ix_members_card (card_status, status)`, `ix_members_proof (proof_status)`):

| Column | Meaning |
|---|---|
| `photo_path`, `photo_updated_at`, `photo_consent_at`, `photo_original` | private headshot (`members/photos/<32 hex>.jpg|png`); consent time; 1 = stored as uploaded (no GD: not cropped) |
| `proof_path`, `proof_name`, `proof_mime`, `proof_uploaded_at`, `proof_status` (`none,received,verified,rejected`), `proof_verified_by`, `proof_verified_at` | proof of payment (`members/proofs/<32 hex>.jpg|png|webp|pdf`); `rejected` = "needs a better copy"; by / at = last verified or rejected |
| `dashboard_token` CHAR(64) | private-page token (set at registration; lazily for old rows: `MembershipService::dashboardToken()`) |
| `card_status` (`none,issued,revoked`), `card_version`, `card_issued_at`, `card_emailed_at`, `card_emailed_version`, `card_valid_until`, `card_verify_token` (32 hex), `card_needs_reissue`, `card_reissue_reason` (`photo`, `photo_staff`, `details`, `renewal`, `payment`), `card_revoked_at` | card state; `card_emailed_version` is the compare-and-set that makes one version e-mail once |
| `card_snapshot` MEDIUMTEXT, `card_photo_path` | JSON of what the issued card says; the card's own private photo copy |

`member_payments`: `proof_path`, `proof_name`, `proof_mime` (a private **copy** of the proof attached by staff). `membership_types`: `code` VARCHAR(8) NOT NULL DEFAULT '' (`IND`, `YTH`, `ORG` set for the three seeded types by slug; empty = initials of the name, `MembershipService::typeCode()`).
Abilities: `members.card` (admin only by default; assignable to custom roles; in `ability_catalog.php`). Issuing / re-sending a member's card needs only `members.manage`.

Private files live under `storage/private/members/{photos,proofs}/` (403 from the web server, `.htaccess` allow-list), random names, mode 0640; the database stores the relative path; **only controllers stream them** (`MemberFiles::stream()`).

## 5. Settings (group "Membership card"; `CardSettings`, seeded in `seed.sql`, pristine-only refresh)

Defaults reproduce the reference card. Types: `str:N` one line of N characters, `text:N` several lines, `color` `#rrggbb`, `bool`, `enum`, `int:min:max`, `image` (path below `uploads/`), `format` (member number), `email`.

**Words on the card** (the 21 wording settings added in section 15 - `membership_card_pay_free|partial|tbd`, `_fee_free|tbd|per_year|per_month|per_quarter|for_life|one_off`, `_period_annual|monthly|quarterly|lifetime|one_time`, `_adverb_annual|monthly|quarterly`, `_joining_paid|due|waived` - are in the tables of the designer tabs Blocks & tiles, Words and Footer; their defaults are the reference words)

| Key | Type | Default |
|---|---|---|
| `membership_card_org_name` | str:60 | `LUMINA AFRICA FOUNDATION` |
| `membership_card_tagline` | str:70 | `Igniting Light. Inspiring Change.` |
| `membership_card_label` | str:30 | `MEMBERSHIP CARD` |
| `membership_card_label_member` | str:24 | `MEMBER` |
| `membership_card_label_from` | str:24 | `FROM` |
| `membership_card_label_number` | str:30 | `MEMBER NUMBER` |
| `membership_card_label_valid_until` | str:30 | `VALID UNTIL` |
| `membership_card_label_member_since` | str:24 | `MEMBER SINCE` |
| `membership_card_label_payment` | str:24 | `PAYMENT` |
| `membership_card_label_fee` | str:30 | `{period} MEMBERSHIP` |
| `membership_card_label_type` | str:24 | `MEMBERSHIP TYPE` |
| `membership_card_label_district` | str:24 | `DISTRICT` |
| `membership_card_scan_text` | str:40 | `Scan to verify membership` |
| `membership_card_renews_text` | str:40 | `Renews {period}` |
| `membership_card_no_expiry_text` | str:30 | `No expiry` |
| `membership_card_status_active` | str:24 | `ACTIVE MEMBER` |
| `membership_card_status_expired` | str:24 | `EXPIRED` |
| `membership_card_status_suspended` | str:24 | `SUSPENDED` |
| `membership_card_status_revoked` | str:24 | `NOT VALID` |
| `membership_card_pay_paid` | str:24 | `Confirmed` |
| `membership_card_pay_waived` | str:24 | `Waived` |
| `membership_card_pay_pending` | str:24 | `Pending` |
| `membership_card_pay_free` / `_pay_partial` / `_pay_tbd` | str:24 | `No fee` / `Part paid` / `Fee not set` |
| `membership_card_fee_free` / `_fee_tbd` | str:24 | `Free` / `To be announced` |
| `membership_card_fee_per_year` / `_per_month` / `_per_quarter` / `_for_life` / `_one_off` | str:16 | `/ year` / `/ month` / `/ quarter` / `(for life)` / `(one-off)` |
| `membership_card_period_annual` ... `_period_one_time` | str:16 | `ANNUAL` `MONTHLY` `QUARTERLY` `LIFETIME` `ONE-TIME` (the word that replaces `{period}` in the fee tile label) |
| `membership_card_adverb_annual` / `_monthly` / `_quarterly` | str:16 | `annually` / `monthly` / `quarterly` (replaces `{period}` in the renewal text) |
| `membership_card_joining_paid` / `_due` / `_waived` | str:60 | `Joining fee {amount} paid` / `... due` / `... waived` (what `{joining}` says in the footer) |
| `membership_card_location_format` | str:60 | `{city}, {country}` |
| `membership_card_panel_text` | text:160 | `Present this card for member check-in\nLumina Africa Foundation · G...` |
| `membership_card_footer_1` | str:120 | `{joining} · Issued {issued}` |
| `membership_card_footer_2` | str:120 | `Support: {phone} · {email}` |
| `membership_card_footer_3` | str:120 | `` |
| `membership_card_support_phone` | str:30 | `` |
| `membership_card_support_email` | email | `` |

**Logo, colours, blocks**

| Key | Type | Default |
|---|---|---|
| `membership_card_logo` | image | `` |
| `membership_card_navy` | color | `#0F1B33` |
| `membership_card_accent` | color | `#E0642C` |
| `membership_card_gold` | color | `#C9973F` |
| `membership_card_green` | color | `#22A35A` |
| `membership_card_tile` | color | `#F4F6FA` |
| `membership_card_label_color` | color | `#6A7389` |
| `membership_card_muted` | color | `#B9C2DA` |
| `membership_card_show_photo` | bool | `1` |
| `membership_card_show_qr` | bool | `1` |
| `membership_card_tile_1` | enum:member_since,payment,fee,valid_until,member_number,type,district,none | `member_since` |
| `membership_card_tile_2` | enum:member_since,payment,fee,valid_until,member_number,type,district,none | `payment` |
| `membership_card_tile_3` | enum:member_since,payment,fee,valid_until,member_number,type,district,none | `fee` |

**Numbering and behaviour**

| Key | Type | Default |
|---|---|---|
| `membership_card_number_format` | format | `{PREFIX}-{TYPE}-{YY}-{SEQ}` |
| `membership_card_trigger` | enum:paid_approved,approved,manual | `paid_approved` |
| `membership_card_reissue_renewal` | bool | `1` |
| `membership_card_require_photo` | bool | `0` |

**E-mail**

| Key | Type | Default |
|---|---|---|
| `membership_card_email_subject` | str:150 | `Your {site} membership card ({number})` |
| `membership_card_email_body` | text:3000 | `Hello {first_name},\n\nYour membership is confirmed and your member...` |

**Outside the card**

| Key | Type | Default |
|---|---|---|
| `membership_photo_required` | bool | `1` |
| `membership_proof_retention_days` | int:0:3650 | `90` |

Placeholders (lower case) in the footer lines: `{joining} {issued} {phone} {email} {number} {name} {since} {valid_until}`; location: `{city} {district} {country}`; renewal text and tile label: `{period}`.
Card e-mail subject / body: `{name} {first_name} {site} {number} {type} {valid_until} {start_date} {dashboard_url} {contact_email} {support_phone} {photo_note}`.
Member number format (upper case): `{PREFIX} {TYPE} {YY} {YYYY} {SEQ} {SEQ:3..8}`, characters `A-Z 0-9 -` only, `{SEQ}` last, at most 40 characters when expanded.
Validation / sanitising: `CardSettings::sanitise($input, $form)` (colours must be `#rgb|#rrggbb`, enums from the list, text cut and cleaned of control / zero-width / bidi characters, logo path pattern, number format checked); `CardSettings::save()` stores only values that differ from the default and audits **key names only**; `CardSettings::warnings()` = WCAG contrast warnings for the colour pairs (4.5:1 for small text; the owner's reference colours fall short on four of them and are reported, see section 15).

## 6. Services API (all static; `App\Services\Membership\...`)

### `CardService`
| Signature | Purpose |
|---|---|
| `trigger(): string` | `paid_approved` (default) \| `approved` \| `manual` |
| `eligibility(array $member, bool $force = false): array{eligible,reasons,cleared}` | reasons: `erased, not_approved, no_number, unpaid, photo_missing`; `force` (staff) skips `unpaid` only |
| `event(int $memberId, string $event, array $opts = []): ?array` | the **trigger**; events `approved, payment, renewal, photo`; never throws; returns the issue result or null (section 7) |
| `issue(int $id, array $opts = []): array` | `opts`: `user_id, source, force, send (default true), reason, only_if_renewal_due (re-issue only when the locked row still needs a renewal card)`; result `{ok,error,message,member,issued,already,emailed,version}`; errors `not_found, erased, not_approved, no_number, unpaid, photo_missing`; **idempotent** (row lock) |
| `reissue(int $id, array $opts = []): array` | new version from the member's current data, **new verification token**; same options / errors |
| `revoke(int $id, string $reason = '', ?int $userId = null): array{ok,message,member}` | verification then says NOT VALID |
| `resend(int $id, array $opts = []): array{ok,code,message}` | `opts`: `by` (`member` 3/day, `staff` 10/hour), `user_id`; codes `none, rate, failed` |
| `sendSample(string $to, array $override = [], ?int $userId = null)` | designer test e-mail (sample card, marked `[TEST]`); the controller sends it to the signed-in admin only |
| `pdf(array $member): ?string`, `png(array $member): ?string`, `filename(array $member, string $ext = 'pdf')` | the issued card (snapshot + live status); null when none / erased / no GD |
| `summary(array $member): array` | `state` (`not_issued, issued, revoked`), `version, issued_at, emailed_at, emailed, valid_until, needs_reissue, reissue_reason, live, eligible, waiting[], verify_url, trigger, filename, renewal_due, unprintable` |
| `timeline(array $member): array`, `dashboardState(array $member): string` | the four-step progress (`received, payment, approved, card` = `done|current|todo`) and the status-message key (`pending, approved_unpaid, approved_photo, approved_ready, issued, revoked, rejected, cancelled, suspended, expired`) |
| `verification(string $token): array` | the **only** data the public verification page may show (section 10); `validToken()` = `/^[a-f0-9]{32}$/D` |
| `renewalDue(array $member): bool`, `deleteArtefacts(array $member): int` | helpers |

### `MemberFiles` (private files)
`photoRequired()`, `canUpload($member, 'photo'|'proof')` (status pending / approved, proof until verified), `allowUpload($memberId, $ip)` (8 per hour per member, 30 per hour per address),
`inspectUpload(?array $file, 'photo'|'proof'): {ok,empty,code,message}` (validates, stores nothing), `checkApplication(array $files, array $input): array<field,message>` and
`storeApplication(int $memberId, array $files, array $input): {photo,proof}` (registration form), `storePhoto($id, ?array $file, $opts)` / `storeProof(...)` (`opts`: `consent`, `by_staff`, `user_id`, `source`, `ip`, `skip_rate`, `notify`;
result `{ok,code,message,member,notice}`; codes `none, size, type, small, invalid, failed, consent, closed, rate`; the **message is the editable registry copy** `uploads.err_<kind>_<code>`),
`removePhoto`, `removeProof`, `setProofStatus($id, 'verified'|'rejected'|'received', ?$userId): {ok,message,member,mailed}` (rejected e-mails the request once per 6 hours),
`attachProofToPayment($paymentId, $memberId, ?array $file = null): bool`, `photoForCard`, `hasPhoto`, `hasProof`, `stream($member, 'photo'|'proof', $download = false): Response`, `streamPayment(array $payment, $download)`,
`collectPaths`, `deletePaths`, `deleteAll`, `purgeStale(?int $days)`, `cleanOrphans()`, `displayName()`.

### `MembershipService` additions
`assignMembershipNumber($id, ?$year)` (configurable format; legacy numbers continue; never changes an issued number), `numberSeriesFor(array $type, ?$year): [head, pad]`, `numberPreview(array $type, ?$year)`, `typeCode(array $type)`,
`newToken($bytes = 32)`, `dashboardToken($member)` (lazy), `regenerateDashboardToken($id)`, `dashboardUrl($member)`, `dashboardPath($member)`, `memberByDashboardToken($token): ?array`, `proofCheckCount(): int`.
`saveType()` accepts `code`. Hooks (already wired): `changeStatus(... 'approved')`, `recordPayment()`, `updatePayment()`, `renew()` call `CardService::event()`.

### `MembershipMail` additions
`cardIssued(array $member, string $pdf): bool` (HTML + text + PDF attachment `Membership-card-<number>.pdf`), `cardSample($to, $pdf, $override)`, `renderCard($member, $override = [], $sample = false): [subject, text, html]`, `cardVars()`, `proofRejected($member)`; every template can use `{dashboard_url}`.
`MembershipApplication::anonymise()` also deletes the photo, proof, payment proofs and the card photo copy, blanks both tokens and the snapshot, and revokes the card; `update()` flags `card_needs_reissue` when name / location change after issue.

### Card engine (`Card/`)
`CardRenderer::memberPdf/memberPng($member, $ctx, $override)`, `issuedPdf/issuedPng($member)`, `samplePdf/samplePng($override)`, `pdf($data, $settings)`, `png(...)`, `logo($settings)`, `usable($bytes)`.
`CardData::raw($member, $ctx)` / `sampleRaw()` (neutral input) -> `build($raw, $settings): array` (pure; keys `org, tagline, label, type, name, initials, name_label, from_label, location, number, number_label, status, status_label, status_color, valid_label, valid_text, renews_text, scan_text, tiles[], footer[], panel[], sample, show_photo, show_qr, photo, verify_url, colors, title`).
`CardLayout::build($data, ['logo' => bool]): {w,h,ops,meta}`; `fit()`, `nameLines()`, `cover()`, `qrGeometry()`. `PdfWriter` (top-left origin API, `text()` y = baseline, `roundRect`, `clipRoundRect`, `addJpeg`, `addPng`, `output()`), `QrEncoder::encode($text, 'M', $minVersion, $forcedMask)`.

## 7. Issue triggers (state machine)

`members.card_status`: `none` -> `issued` -> `revoked` (-> `issued` again by an explicit issue / re-issue). Evaluated by `CardService::event()` after every approval, payment change, renewal and photo upload (all committed first; a card problem never breaks them):

| State when the event happens | `paid_approved` (default) | `approved` | `manual` |
|---|---|---|---|
| not approved | nothing | nothing | nothing |
| approved, balance not cleared (unpaid or part paid) | nothing (waiting: `unpaid`) | **issue + e-mail** | nothing |
| approved, balance cleared (paid, waived, free type) | **issue + e-mail** | **issue + e-mail** | nothing (staff press "Issue card now") |
| `membership_card_require_photo` on and no photo | nothing (waiting: `photo_missing`), issued when the photo arrives | same | staff are refused too |
| card issued, a renewal moved `expiry_date` beyond `card_valid_until`, setting `membership_card_reissue_renewal` on, trigger condition holds | **re-issue (version + 1) + e-mail** | **re-issue + e-mail** | nothing |
| card issued, photo / name changed | `card_needs_reissue = 1`, nothing else | same | same |
| card revoked | nothing (only an explicit issue replaces it) | nothing | nothing |

"Balance cleared" = `MembershipService::paymentSummary()['status']` is `paid` or `free` (waived rows count as paid; `tbd` = fee not set is NOT cleared). Idempotence: `issue()` locks the member row and re-checks inside; a second call returns `already`;
the e-mail is claimed with `UPDATE ... WHERE card_emailed_version < ?` (a failed send gives the claim back so Re-send or the next event retries). Staff "Issue card now" / "Re-issue" use `force` (skip the payment rule; approval and photo rule still apply).

## 8. The card: layout, fonts, reference comparison

One layout (`CardLayout::build`) is drawn by `CardPdf` (vector) and `CardPng` (GD), so they cannot drift. Primitives (pt, origin top-left, text y = baseline): `rect x y w h r fill`, `line ... dash`, `text x y text font size color align`, `image key x y w h r cover`, `qr x y size quiet modules dark`.
Geometry (from the reference PDF): white panel 520 pt; navy panel `#0F1B33` 200 pt with a 9 pt accent bar `#E0642C`; logo 64 pt at (28, 26); organisation line 12 pt bold at x 102; tagline 11 pt Times-Italic gold `#C9973F`; top right "MEMBERSHIP CARD" 9.5 pt and the type 10.5 pt orange, right-aligned at x 492;
photo block 116 pt at (28, 130); name 30 pt Times-Bold at x 164 with a 40 x 3 pt accent underline; three tiles 52 pt high at y 296 (x 28..492, gap 8, radius 5, fill `#F4F6FA`); footer baselines 370 / 383 / 396 (8.5 pt, red bold for the sample line);
navy panel items centred on x 620: label baseline 40, number 21 pt bold baseline 64, status pill 148 x 24 at (546, 78) green `#22A35A`, white QR box 124 x 118 at (558, 106) with the QR (100 pt incl. a 2-module quiet zone), "Scan to verify" baseline 238, "VALID UNTIL" 270, date 17 pt 291, "Renews annually" 307, panel text 380 / 392.
Text fitting: every line is measured with the AFM widths and shrunk, then cut with an ellipsis; the name goes from 30 pt down to 20 pt on one line, then two balanced lines (22 -> 14 pt), then shortened. Tested with 60-character names, accents, apostrophes, African letters (the eng, open o, epsilon are drawn as n, o, e: WinAnsi has no glyph; see section 15 for the full letter rules), markup and an unbreakable 80-letter word.
Statuses: ACTIVE MEMBER (green), EXPIRED (grey `#6B7280`), SUSPENDED and NOT VALID (red `#C0392B`). Lifetime / one-time memberships print "No expiry" and no renewal line. The sample is watermarked in red exactly like the reference (`SAMPLE CARD - fictional member, not valid for verification`).

**Reference comparison (Inkscape renders at 110 dpi; kept outside the repo):** every block, rectangle, tile, pill, QR box and baseline lands on the reference position (an overlay of the two renders is grey where they agree: only text edges differ); colours are the exact values from the reference stream.
Remaining differences: (1) **fonts**: Helvetica / Helvetica-Bold instead of Carlito, so letter shapes differ slightly (sizes are scaled 0.86 bold / 0.91 regular to match word widths); Times matches the reference serif exactly; (2) **QR**: the reference QR is version 4 (33 modules), ours is version 6 (41 modules, a longer URL with a 128-bit token), so it is denser; (3) footer separators are a single "dot" with one space each side (the reference has two); (4) the logo is the pre-composited Lumina seal JPEG (`assets/img/brand/card-logo.jpg`, regenerate with `bin/dev/make-card-logo.php`); the dotted divider on the panel edge is drawn but almost invisible on navy, as in the reference.
PDF facts: 1 page 720 x 414, ~70 KB, Flate content stream, core fonts with `WinAnsiEncoding` (no embedding), exact xref table, `/Info` (Title "Membership card <number>", Author = organisation), JPEG embedded untouched, PNG logos / photos (with alpha as `/SMask`) decoded in pure PHP, photo clipped by a rounded-rectangle path. Opens in Inkscape, Chrome and Acrobat (structure checked by the selftest; rendered with Inkscape).
Font metrics: `bin/dev/gen-card-font-metrics.py` regenerates `CoreFontMetrics.php` (needs ReportLab); the PNG preview fonts are `assets/fonts/card/*.ttf` (README + OFL licence in the folder).

## 9. Token model

| Token | Column | Size | Where | Rules |
|---|---|---|---|---|
| private page | `members.dashboard_token` | 64 hex = 256 bits | `/membership/me/{token}` (in the confirmation page, the acknowledgement, approval, payment, proof-request and card e-mails) | credential: no login; per application; created at registration (lazily for old rows); `regenerateDashboardToken()` kills the old link; blanked on erasure; a valid token always opens its page; failed lookups counted per IP (15 per 10 minutes, then 429) |
| verification | `members.card_verify_token` | 32 hex = 128 bits | `/membership/verify/{token}` (the QR) | new on every issue / re-issue (the old card's link stops verifying); revoked / erased / replaced -> NOT VALID; the lookup comes FIRST (a genuine card is always verified); only FAILED lookups count: 20 per 10 minutes per IP, then 429 with the real `Retry-After`; the route allows 240 requests per card per address per 10 minutes |

Both are generated with `random_bytes`, matched with strict `/D` regexes, UNIQUE in the database, never logged (audit entries carry ids, references and numbers only).

## 10. Routes and shapes

**Public** (`app/routes/web_membership_card.php`; all responses `Cache-Control: private, no-store`, `Referrer-Policy: no-referrer`, `X-Robots-Tag: noindex, nofollow`; POST needs the usual stateless CSRF token):

| Method | Path | Notes |
|---|---|---|
| GET | `/membership/me/{token}` | the page; wrong token -> friendly 404 page + a failure hit; 15 failures -> 429 (a CORRECT token is never blocked) |
| POST | `/membership/me/{token}/photo` | multipart `photo`, checkbox `photo_consent` (required); flash + redirect `#photo` |
| POST | `/membership/me/{token}/proof` | multipart `proof` |
| GET | `/membership/me/{token}/photo`, `/proof` | stream the member's OWN file (image inline, PDF attachment) |
| GET | `/membership/me/{token}/card.pdf`, `/card.png` | 404 when no card / no GD |
| POST | `/membership/me/{token}/resend` | 3 per day |
| GET | `/membership/verify/{token}` | 200 for VALID / EXPIRED / SUSPENDED, **404 for NOT VALID** |

`CardService::verification()` returns `['state' => 'invalid']` **only** for an unknown, malformed, replaced, revoked, cancelled, rejected or erased card (no member data at all); otherwise exactly
`['state' => 'valid'|'expired'|'suspended', 'name', 'type', 'number', 'since', 'valid_until', 'no_expiry']` (live values). Never a photo, e-mail, phone, address, payment or application reference (tested over HTTP).
View variables: `me/show.php`: `$member $token $card $summary $fee $joining $instructions $contact $timeline $state $renewal $hasPhoto $hasProof $canPhoto $canProof $photoRequired $hasPng $urls`; `verify/show.php`: `$result $contact`.

**Admin** (`app/routes/admin_membership_card.php`): designer (`members.card`): `GET /admin/membership-card`, `POST /admin/membership-card` (save + logo), `POST .../preview` (unsaved values into the session -> `{ok, errors, warnings, pdf, png}`),
`GET .../preview.pdf` (the sample PDF, `Content-Disposition: inline`, `X-Frame-Options: SAMEORIGIN`, shown in an `<iframe>`; the CSP `frame-src 'self'` already exists), `GET .../preview.png`, `POST .../test-email`.
Member actions (`members.manage`): `GET /admin/members/{id}/photo|proof|payments/{pid}/proof`, `POST .../photo` (staff need no consent tick), `.../photo/remove`, `.../proof`, `.../proof/status` (`status=verified|rejected|received`), `.../proof/remove`,
`.../card/issue|resend|reissue|revoke`, `GET .../card.pdf|card.png`, `POST /admin/members/card-bulk` (`ids[]`, at most 50, normal rules). `MembersController::recordPayment` accepts `pay[attach_proof]=1` (attach a copy of the member's proof and mark it verified: who / when recorded) or a file `pay_proof`.
**List filters for the STAFF agent** (`ListQuery`): `proof` = `received` (alias "needs check") | `verified` | `rejected` | `none`; `card` = `none` | `issued` | `revoked` | `reissue` (`card_needs_reissue = 1`); sidebar link `admin/members?proof=received` + badge `MembershipService::proofCheckCount()` already exist.

## 11. Privacy, retention and compliance

* A headshot and a payment proof are personal data: private storage, token- or role-gated streaming, EXIF / GPS stripped (re-encoded with GD, else `ImageScrubber`), consent checkbox **with a timestamp** (`photo_consent_at`), deleted on erasure (`MembershipApplication::anonymise`: files, columns, tokens, snapshot, card photo copy; the card is revoked), deleted with the record (`MembersController::destroy`), and deleted `N` days after a rejection or cancellation (`membership_proof_retention_days`, default 90, 0 = never) by `bin/membership-maintenance.php` - the purge removes the photo, the proof, the proofs on payment rows, the card's own photo copy and snapshot, revokes the card and blanks both tokens (the application record itself, with the name, stays by design) (daily cron: `/usr/local/bin/php /home/USER/public_html/bin/membership-maintenance.php`). They never appear in logs, audit details (ids, references, key names only), sitemaps or the verification page.
* The QR code exposes only the verification URL (a random 128-bit token). The private page shows only the member's own data.
* **Uganda's Data Protection and Privacy Act, 2019** may require registration with the Personal Data Protection Office, a lawful basis, a stated retention period and specific notices for a photograph and a payment document. **The owner and a legal adviser must confirm** the lawful basis, the retention period (90 days after rejection is a placeholder), the consent wording (`uploads.photo_consent_label`) and the privacy text; `pages/privacy.php` (membership block) was extended with a DRAFT paragraph flagged for that review.
* Uploads: real type by magic bytes + finfo + `getimagesize`, JPEG / PNG / WebP (photos) and JPEG / PNG / WebP / PDF (proofs), 5 MB, headshot at least 300 x 300, at most 6000 px per side, decompression-bomb guard, polyglots (`<?php`, `<script`, `<svg`, `<html`) refused, PDFs through `PdfValidator` strict mode (no JavaScript, `/Launch`, remote actions, embedded files, encryption; EVERY zlib stream is inflated and scanned, whatever its dictionary says, and zlib data that does not inflate is refused); without GD a validated JPEG / PNG original is kept (flagged `photo_original`), WebP is refused.
* Accessibility of the working references: labelled file inputs with preview, `role="status"` messages, 44 px targets, error summary through the existing form flow, camera `capture` only as a hint on phones (button sets it; never forced).

## 12. Contract for the registration form (DASHBOARD agent)

Add to `/membership/register` (inside the existing form; set `enctype="multipart/form-data"`; section copy `uploads.*`): file input **`photo`** (`accept="image/jpeg,image/png,image/webp"`, label `uploads.photo_label` or `photo_label_optional` depending on `MemberFiles::photoRequired()`, help `photo_help`, `camera_hint`), checkbox **`photo_consent`** (`uploads.photo_consent_label`, required when a photo is chosen), file input **`proof`** (optional, JPG / PNG / WebP / PDF), the existing `payment_reference`.
In `MembershipController::submit()`:
```php
$files = ['photo' => $request->file('photo'), 'proof' => $request->file('proof')];
$fileErrors = MemberFiles::checkApplication($files, $request->all());          // BEFORE process(): nothing is stored, nothing created
// merge into the errors of MembershipApplication::process(); on any error $this->fail($errors, 'membership/register') (a browser cannot refill file inputs: say so in the hint)
$res = MembershipApplication::process(...);                                       // unchanged
$stored = MemberFiles::storeApplication((int) $res['member']['id'], $files, $request->all());   // AFTER creation; a failure never undoes the application
// if ($stored['photo'] !== null && !$stored['photo']['ok']) or the same for proof: Flash::warning(content('membership','uploads.apply_files_failed'))
```
Then redirect to the confirmation page as today and show a link to `MembershipService::dashboardPath($member)` there ("add your photo / proof later, follow your application"). The acknowledgement e-mail already carries `{dashboard_url}`.

## 13. Registry keys (`content('membership', '<section>.<key>')`)

`uploads.*` (registration form + dashboard upload wording + all `err_<kind>_<code>` messages used by `MemberFiles`), `dashboard.*` (about 85 keys: titles, progress steps, status messages `state_*`, photo / proof / card panels, renewal, help), `verify.*` (labels `label_valid|expired|suspended|invalid`, texts `text_*`, field labels, privacy note), `emails.proof_subject|proof_body|card_button|card_photo_note` (the card e-mail subject and body are **settings**, not registry). A selftest checks that every key the views, controllers and `MemberFiles` use exists in the registry. Placeholders: `{first_name} {amount} {date} {n} {contact_email} {min}`.
Existing e-mail templates (`ack`, `approved`, `payment`) now mention `{dashboard_url}` in their DEFAULT text; an owner who already edited those texts keeps their wording (add the line by hand in Pages > Membership > E-mail templates).

## 14. Commands, deploy, risks

| Task | Command |
|---|---|
| Apply schema + seed to the dev DB (idempotent) | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` (the selftest checks it contains the card columns and settings, and imports it twice into a scratch database) |
| Tests | `/c/xampp/php/php.exe bin/selftest.php` (+ `-d extension=gd`) or one file via a small runner that defines `check()` |
| Daily cron (live) | `bin/membership-maintenance.php` |

**Deploy order**: import `database/lumina-live-import.sql` first (or with the code upload): new code against an old schema fails on the members screens (the Membership menu hides itself while the table is missing, the routes do not). Then open Admin > Membership > Membership card, check the preview, and set the logo, trigger and photo rules. `APP_URL` must be set in production (the QR code and e-mail links use it).
**Risks / open items** (section 15 lists what the review changed): (1) Helvetica instead of Carlito (section 8); (2) the XAMPP Apache has no GD, so no PNG preview there and a photo uploaded without GD is kept uncropped (and its EXIF orientation is NOT applied on the card: ask applicants for upright photos, or enable `extension=gd` in `php.ini`: Hostinger has it); (3) cards are e-mailed synchronously (an SMTP outage makes `Issue` report "e-mail could not be sent": use Re-send); (4) the retention period, consent wording and lawful basis need the owner's legal review; (5) `MembershipService::recordPayment()` e-mails the card inside the request, so a payment form can take a second longer when it issues a card; (6) owner-edited e-mail templates will not contain `{dashboard_url}` until edited; (7) the generic router accepts a trailing line break in `/membership/verify/<token>` (harmless, noted in section 3); (8) the dashboard, verification page, designer, member panels and the registration form additions of section 12 are built and integrated (end-to-end checked with and without GD); (9) a card issued BEFORE the balance cleared (trigger `approved`, or a staff override) shows PAYMENT "Pending" and "Joining fee ... due" until it is re-issued: when the payment clears the card is flagged `card_needs_reissue` (reason `payment`, "Update due" on the member screen) and nothing else happens automatically; (10) without GD a kept (uncropped) JPEG is embedded as it is and its EXIF orientation is not applied on the card (the stored file keeps an orientation-only EXIF block); (11) `CardSettings::cleanText()` keeps `max(6, length / 40)` lines (6 for short fields, 75 for the 3000-character e-mail message); the designer's own `CardPreview::cleanMessage()` is a second, equivalent cleaner for the preview.

## 15. Review fixes (security, functional, accessibility, editability) - what changed and where it is tested

All covered by `bin/selftest-membership-card-fixes.php` unless another file is named.

| Topic | Behaviour now |
|---|---|
| Strict PDF scan | `PdfValidator` (strict) no longer looks for the text "obj" to find a stream dictionary: it inflates **every** stream that starts like zlib data and scans the result (JavaScript, `/GoToR` ... in an object stream cannot hide behind a padded or disguised `/ObjStm` dictionary). Zlib data that does not inflate (also after trying "to the end of the file", so a fake `endstream` inside the compressed bytes does not help), more than 32 MB of decoded data, more than 3000 streams, and non-zlib streams next to `/ObjStm`, `/Crypt` or an ASCII-hex / ASCII-85 / LZW / RunLength filter are refused. Staff still download proofs as attachments: open them in a sandboxed viewer. |
| No-GD card writer | `PdfWriter` inflates PNG pixel data with a hard limit taken from the header (`(row bytes + 1) x height`), and embeds a PNG that needs decoding in PHP (transparency / alpha) only up to 1.2 megapixels (about 1.6 s); larger ones are left out of the card (GD converts them when it exists). Without GD the upload form refuses such a photo up front (`err_photo_unsupported`); with GD a photo that cannot be processed in memory is refused before the application is created (`err_photo_dimensions`, which is also what "too large in pixels" now says: the real reason, no longer "we could not read that picture"). |
| E-mail claim | `CardService::deliver()` renders the PDF **first** and claims `card_emailed_version` afterwards, so a fatal error while drawing cannot leave "E-mailed" recorded for a card that never left. |
| Renewal race | Automatic re-issues (`event()`) pass `only_if_renewal_due`: `doIssue()` re-checks `renewalDue()` on the LOCKED row and returns `already` when the card is current, so four parallel payment events make one new version, one e-mail and one valid QR link. Staff "Re-issue" is still unconditional. |
| Retention / erasure | `MemberFiles::collectPaths()` includes the card's photo copy; `deleteAll()` (used by the retention purge) also blanks `card_photo_path`, `card_snapshot`, both tokens and revokes the card; the purge query matches any such leftover. `CardService::pdf()/png()` and a re-send are refused for rejected / cancelled members, and their private page shows the card as no longer valid. `anonymise()` also clears `status_reason` (free text typed by staff). Deleting a payment row deletes its proof file. |
| Analytics | `analytics_id()` is switched off on the server for `/meeting/`, `/membership/me/`, `/membership/verify/` and `/membership/confirmation/` (layout, CSP and script agree, error pages included); `robots.txt` disallows the three membership paths. `assets/js/membership-private.js` was removed. |
| Publish switch | The verification page and the private page use `render()` with the membership page record (SEO defaults, wording), not `renderPage()`, and the private-page routes have no `page.published` middleware: hiding the marketing page "Membership" no longer kills QR codes and e-mail links (the registration page is still hidden). |
| Rate limits | Verification and private-page lookups look the token up first; only failed lookups are counted (see section 9). |
| Letters | `PdfWriter::toWinAnsi()`: capital open O -> O, open E -> E, schwa -> e (the table was misaligned); decomposed letters (e + U+0301) compose to the WinAnsi letter (also with `Normalizer` when intl exists), other combining marks are dropped; a generated table (Unicode decompositions of Latin Extended-A/-B/Additional plus Hausa hooked letters, barred I, eng ...) maps Yoruba dotted letters and Vietnamese letters to plain letters. Arabic, Cyrillic, Chinese and emoji still print `?`: the staff card panel says so (`CardService::summary()['unprintable']`), the initials use the printable words ("王小明 Wang" -> WA, nothing printable -> M). Bidi controls (U+200E/F, 202A-E, 2066-9) are removed from names at registration and again when a name is shown on the verification and private pages. |
| Card words | 21 new designer settings (section 5) make every word the card prints editable: payment tile (free, part paid, fee not set), fee tile (free, to be announced, per year / month / quarter, for life, one-off), period words, renewal words, and the joining-fee line (paid / due / **waived**: a waived joining fee no longer reads "paid"). Defaults equal the old hard-coded text. |
| Contrast | Small text is held to 4.5:1 (only the 46 pt initials to 3:1). The reference colours stay the default but four pairs are below 4.5 (tagline 2.6, type line 3.5, status pill 3.3, tile labels 4.4); the designer lists them as warnings ("large text only" / "hard to read") and no longer says "good". Nothing that reaches 2:1 / 3:1 (QR navy) is refused differently. The card e-mail darkens the button background, links, tagline and labels until they reach 4.5:1 (`MembershipMail::readable()`), whatever the card colours are. |
| Settings and the import | `Settings::set()/setMany()` write `created_at = 2000-01-01` on rows they INSERT, so a setting the owner reset to its default (row deleted) and then edited again no longer looks "pristine" to `seed.sql` (`updated_at = created_at`) and is not overwritten by the next import of `lumina-live-import.sql`. |
| Number formats | `{TYPE}`, `{YY}` or `{YYYY}` directly before `{SEQ}` is refused (type codes `CDA` and `CDA1` would share one series). The designer calls the numbers it shows "Example number (running number 1)", not "the next member would get". |
| Staff lists | The "without a photo" chip and the `photo=none` filter share one condition (pending or approved, no photo): the number on the chip equals the rows. A change of the fee, joining fee, currency or period flags an issued card for a new version (reason `details`). |
| Registry | New: `uploads.err_photo_dimensions|err_photo_unsupported|err_proof_dimensions|remove_label|reselect_note`, `dashboard.state_revoked`, `dashboard.status_value_<status>` (the Status fact is editable). Removed: `dashboard.balance_label`, `err_generic`, `photo_closed_note` (nothing read them). The registry coverage check in `-flow.php` now also scans `MembershipService::copy()` and `$u()` calls. |
| Accessibility | A file rejected on choosing it in the registration form (too small, wrong type, too big) is announced with `role="alert"`; the proof viewer's grid has `minmax(0, 1fr)` so zoom and rotation scroll inside the stage and Reset recovers; the member number wraps instead of overflowing at 320 px; `forced-colors` rules give the active PDF / Picture switch, the chosen proof status, the active quick link and the current progress step an outline. |
| Designer logo | Without GD a logo the card writer cannot embed (WebP, GIF, interlaced / 16-bit PNG) is refused on upload with a clear message instead of silently showing the seal. |
