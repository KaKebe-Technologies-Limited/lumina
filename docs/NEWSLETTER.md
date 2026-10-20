# Newsletter - CONTRACT

> Read `docs/ARCHITECTURE.md` and `docs/DESIGN.md` first; this file only adds the newsletter feature (read `docs/MEMBERSHIP.md` for a worked example of the same conventions).
> Three agents build on it in parallel: the **WEB agent** (public pages), the **EDITOR agent** (admin issues list, editor, JSON API) and the
> **MAILING agent** (e-mailing: panel, queue, cron). The **architect** files (schema, seed, models, services, renderer, registry, settings, routes, nav
> fragment, Mailer / SMTP driver, selftest) are **frozen**: call them, do not copy or edit them. Missing something? Add your own file and list the gap in your report.

Owner request: *a non-technical admin can create, edit and publish newsletters at any time; a public `/newsletter` page shows the current issue; an archive
lists past issues; optional e-mailing of an issue to all subscribers.* Chosen output: **web page + e-mail to all subscribers**. Design: modern, clean, minimal;
Fraunces serif headings + DM Sans body; responsive; SEO friendly.

The owner's generic spec named TipTap, Quill, Supabase, Cloudinary and Next.js. This site has none of them (no Node, no Composer): the equivalents are the
site's own stack. WYSIWYG = `assets/js/admin-editor.js` (dependency-free), images = `Uploader` into `/uploads`, database = MySQL / MariaDB, API = JSON routes
under `/admin/api/newsletters`.

Everything is **additive and idempotent**: three new tables, no change to existing ones, so re-importing `database/lumina-live-import.sql`
(`php bin/build-import-sql.php`) upgrades the live database without data loss (tested: old live schema, then the new file twice).

---------------------------------------------------------------------------------------------------------------------

## 1. What exists (architect, done and tested)

| Area | Files |
|---|---|
| Schema / seed | `database/schema.sql` (end): `newsletters`, `newsletter_revisions`, `newsletter_deliveries`. `database/seed.sql` (end): six `newsletter_*` settings, pages row `newsletter` (footer, not header), ONE sample DRAFT issue (only while `newsletters` is empty) |
| Models | `app/models/Newsletter.php`, `NewsletterRevision.php`, `NewsletterDelivery.php` |
| Services | `app/services/Newsletter/NewsletterContent.php` (schema), `NewsletterService.php` (rules), `NewsletterRenderer.php` (the ONE renderer) |
| Core additions | `Request::bodyInput()` (JSON bodies), middleware marker `csrf.exempt`, `App\Core\Smtp`, `App\Core\MailException`, `Mailer` (smtp driver, `attempt()`, `lastError()`, `closeConnection()`, `headers`, RFC 2047, generated text part) |
| Settings | `app/config/settings_schema.php` group **Newsletter** (`newsletter_*`) |
| Abilities | `app/config/app.php`: `newsletter.manage` (admin, editor), `newsletter.delete_published` (admin), `newsletter.send` (admin) |
| Registry | `app/config/pages/newsletter.php` (all page copy, labels, subscribe box, empty states, share, e-mail fixed words) |
| Routes | `app/routes/web_newsletter.php`, `app/routes/admin_newsletter.php`; sidebar fragment `app/config/admin_nav/newsletter.php` (group "Newsletter", order 37) |
| Reference implementations | `Web/NewsletterController` (complete), `Admin/NewsletterApiController` (complete JSON API), `Admin/NewslettersController` (list, create, preview, duplicate, delete working; `edit` is a STUB), `Admin/NewsletterMailController` (routes + 501 STUBS) |
| Views | `views/web/newsletter/*` (page, empty, archive, card, subscribe box, `_issue`, `_featured`, `blocks/*`), `views/newsletter/email/*` (layout + blocks), `views/admin/newsletters/{index,create,edit}.php`, `.../mail/panel.php` (stub) |
| Styles / JS | `scss/pages/_newsletter.scss` (complete baseline), `scss/admin/_newsletter.scss` and `_newsletter-mail.scss` (empty), `assets/js/newsletter.js`, `admin-newsletter.js`, `admin-newsletter-mail.js` (stubs) |
| Dev tools | `bin/dev/mock-smtp.php` (throw-away SMTP server), `bin/selftest-newsletter.php` (about 130 checks, run by `bin/selftest.php`) |
| Unsubscribe | `SubscribeController::confirmUnsubscribe` also takes the RFC 8058 one-click POST |

Dev database state after the build: one sample DRAFT issue ("Sample issue - edit or delete me"), no deliveries. The selftest creates and removes its own data.

## 2. JSON content model (version 1)

Stored in `newsletters.content` (LONGTEXT with a `JSON_VALID` CHECK; MariaDB 10.4 and MySQL 8). **All validation lives in `NewsletterContent`**; never write content yourself.

```json
{
  "version": 1,
  "featured": {
    "heading": "Building Pathways to Opportunity",
    "body_html": "<p>Sanitised rich text</p>",
    "image": { "path": "2026/10/ab12cd.jpg", "alt": "What the picture shows", "caption": "Optional caption" },
    "link":  { "label": "Explore Our Planned Programs", "url": "/programs" }
  },
  "blocks": [
    { "id": "b3f9a1c2", "type": "article", "heading": "Visit to Wii Got Quarry Site", "body_html": "<p>..</p>",
      "image": { "path": "", "alt": "", "caption": "" }, "link": { "label": "Read more", "url": "/blog/some-post" } },
    { "id": "b0aa11bb", "type": "image",   "image": { "path": "2026/10/x.jpg", "alt": "..", "caption": ".." } },
    { "id": "b77c2d90", "type": "quote",   "body_html": "<p>The quote</p>", "cite": "Moses Komakech, Founder" },
    { "id": "b19e4f01", "type": "button",  "button": { "label": "Partner With Us", "url": "/involvement", "style": "primary" } },
    { "id": "b5d2c8a3", "type": "divider" }
  ]
}
```

| Rule | Value |
|---|---|
| Block types | `article`, `image`, `quote`, `button`, `divider`. Each type is normalised to exactly its own keys (article: `heading body_html image link`; image: `image`; quote: `body_html cite`; button: `button`; divider: none). The `quote` type's `cite` is an addition to the owner's generic list |
| Block `id` | `^[a-z][a-z0-9_-]{1,31}$`, unique in the issue; missing or duplicated ids are regenerated (`b` + 8 hex). The editor uses ids for drag and drop, "click in preview to focus", diffs |
| Limits | at most 60 blocks; heading 200, alt 300, caption 300, link / button label 80, cite 160, URL 2000 characters; each `body_html` 20 000 characters (after sanitising); whole JSON 600 000 bytes |
| Plain text fields | tags stripped, whitespace collapsed (`Sanitizer::text`) |
| HTML fields | `Sanitizer::html()` (allowed tags: p br strong em u ul ol li a h2 h3 h4 blockquote img figure figcaption hr code pre table...; no script / style / iframe / on* / javascript: ; `img src` site-local only). Stored root-relative, output through `rich()` |
| URLs | `http`, `https`, `mailto`, `tel`, or a site path (`/about`; a bare `about` becomes `/about`; an own-host absolute URL becomes root-relative when a request exists). Refused: `javascript:`, `data:`, `vbscript:`, `//host`, `#anchor`, other schemes |
| Image paths | upload `YYYY/MM/file.ext` (jpg jpeg png gif webp) or a bundled photo `assets/img/...`; **must exist** on disk; no `..`, no URLs, no other folders |
| Unknown keys | dropped silently |
| Version | `1` only; anything else is an error |

**Strict vs lenient.** `NewsletterContent::normalise($input, ['strict' => bool, 'check_files' => bool])` returns `{ok, content, errors, warnings}`.
Strict (manual save, publish, create): the first problem per field is an **error** keyed by its path (`blocks.2.button.url`, `featured.image.path`, `content`, `blocks`)
and nothing is saved. Lenient (autosave, reading, preview): the bad value is cleared (block skipped) and reported as a **warning**; a half-typed URL never blocks an autosave.
`NewsletterContent::decode()` is the tolerant reader (never throws, always a complete safe structure).

**Readiness before publishing** (`NewsletterContent::readiness($content, $issueRow)` -> `{blocking, warnings}`; `publish` / `schedule` refuse while `blocking` is not empty):
title required; featured text or at least one block; no empty article / image / quote blocks; button needs label and URL; a link needs label and address together.
Warnings (do not block): image without alt text, missing summary, cover without alt.

## 3. Data model

### `newsletters`
`id, slug (unique), issue_number (NULL ok; UNIQUE index, the service answers with a friendly error and picks the next number again when two creators collide), title, summary (600), issue_date (DATE), cover_image, cover_alt, content (JSON), seo_title, seo_description, status ENUM(draft, scheduled, published, archived), publish_at (DATETIME, application time zone Africa/Kampala), published_at (first time it went live), version (optimistic locking), email_enabled, email_subject, email_preheader, email_status ENUM(none, queued, sending, paused, sent, cancelled), email_total, email_sent, email_failed, email_started_at, email_completed_at, created_by, updated_by, created_at, updated_at`.
Indexes: `(status, publish_at)`, `issue_date`, **UNIQUE `ux_newsletters_number (issue_number)`** (NULLs allowed; the first build had a plain index, the guarded upgrade in `schema.sql` renumbers earlier duplicates to NULL and swaps the index), `email_status`. **Date-times are stored in the application time zone and compared against a timestamp bound by PHP (`NewsletterService::now()`), never against the database server's `NOW()`.**

### `newsletter_revisions`
`id, newsletter_id (FK cascade), version, title, content (JSON), source ENUM-like (save | autosave | publish | restore), saved_by, created_at`. The latest 30 per issue are kept (`NewsletterRevision::KEEP`).
Snapshots identical to the newest one only relabel it; consecutive autosaves by the same author within five minutes replace each other. Only the **content** is restored (not the issue details).

### `newsletter_deliveries`
`id, newsletter_id (FK cascade), subscriber_id (FK set null), email, status ENUM(queued, sent, failed, skipped), attempts, last_error (255), sent_at, created_at, updated_at`, **UNIQUE (newsletter_id, email)**: the database itself guarantees an issue is never queued twice for an address.
Addresses are personal data: admin only, never in logs or audit details. `last_error` is a short reason without credentials or addresses (the SMTP driver already scrubs its messages).

### Existing `subscribers`
Used as it is (`status subscribed|unsubscribed`, 40-hex `token`). No column was added. Only `status = 'subscribed'` ever receives mail.

## 4. Status workflow, visibility, scheduling

| From | Allowed next | Notes |
|---|---|---|
| `draft` | `scheduled`, `published`, `archived` | publishing and scheduling need `readiness.blocking` empty |
| `scheduled` | `draft` (cancel the schedule), `published` (publish now), `archived` | `publish_at` must be in the future when scheduling |
| `published` | `draft` (unpublish), `archived` | refused while the e-mail campaign is queued / sending / paused |
| `archived` | `draft` | archived = kept but not public |

* **Public predicate** (the only one; every public query uses it): `status = 'published' OR (status = 'scheduled' AND publish_at <= now)`. SQL: `[$sql, $params] = NewsletterService::visibleSql('n')`; in PHP: `NewsletterService::isVisible($row)`.
  Drafts, archived and not-yet-due scheduled issues are therefore never reachable, with no cron job needed.
* **Lazy updater**: `NewsletterService::resolveScheduled()` flips due scheduled issues to `published` (`published_at = publish_at`) once per request (race-safe single UPDATE). It is called by the public readers and the admin list.
* `published_at` keeps the **first** time an issue went public (re-publishing does not change it); the slug is locked once `published_at` is set (`NewsletterService::save` refuses a slug change).
* **Status changes do not bump `version`.** `version` counts saves of details or content. Because of that the server itself refuses what a stale editor tab could do after a status change: **autosave is accepted for `draft` and `archived` issues only** (a stale tab autosaving into an issue that was published or scheduled meanwhile gets 409 `status_changed` and writes nothing).
* **A public or scheduled issue must stay complete**: `save()` runs `NewsletterContent::readiness` on every save of a `published` / `scheduled` issue and refuses blocking problems (422 `not_ready`), so an issue can no longer be saved empty while it is live. Drafts may stay incomplete.
* **The e-mail campaign freezes the issue**: while `email_status` is `queued`, `sending` or `paused` NO save is accepted (409 `campaign_running`, also for admins and for restoring a revision), because the message is rendered from the live row on every batch and all subscribers must get the same text. Cancel the campaign to edit again.
* Only a live issue may be e-mailed: `NewsletterService::isLive($row)`.
* Delete: refused while an e-mail campaign is queued / sending / paused. Who may delete a published (or once public) issue is a controller decision: `newsletter.delete_published` (admin).

## 5. Service API (`App\Services\Newsletter\...`, all static)

### `NewsletterContent`
| Signature | Purpose |
|---|---|
| `emptyContent(): array`, `newBlock(string $type): array`, `newId(): string` | defaults, "Add block" |
| `normalise(mixed $input, array $opts = []): array{ok,content,errors,warnings}` | validation (section 2). A JSON number / boolean (anything but string, array, null) is the error "The content must be an object.", never a TypeError |
| `decode(string\|array\|null): array`, `encode(array): string` | tolerant read / compact JSON |
| `readiness(array $content, array $issue = []): array{blocking,warnings}` | what is missing before publishing |
| `cleanUrl(string): string`, `validImagePath(string): bool`, `imageExists(string): bool` | field helpers |
| `firstImage(array): ?array`, `plainText(array, int $max = 0): string`, `htmlToText(string, bool $links = true): string`, `clip(string, int): string` | SEO / e-mail text helpers |

### `NewsletterService`
| Signature | Purpose |
|---|---|
| `visibleSql(string $alias = 'n', ?string $now = null): array{0:string,1:array}`, `isVisible(array): bool`, `isLive(array): bool`, `resolveScheduled(bool $force = false): int`, `now(): string`, `parseLocalDateTime(?string): ?string` | visibility and time |
| `latestPublished(): ?array`, `findPublishedBySlug(string): ?array`, `countPublished(): int`, `archive(int $page, int $perPage = 12, ?int $excludeId = null): Pagination`, `adjacent(array $issue): array{prev,next}`, `url(array $issue, bool $absolute = false): string`, `seo(array $issue): array{title,description,og_image,canonical,published,modified}` | public reads (never drafts) |
| `find(int)`, `adminList(array $filters, int $page, int $perPage): Pagination`, `statusCounts(): array` | admin reads (filters: `status`, `email_status`, `q`) |
| `nextIssueNumber(): int`, `issueNumberTaken(int, ?int): ?array`, `uniqueSlug(string, ?int): string` | numbering and slugs |
| `create(array $input, ?int $userId): array{ok, errors?, newsletter?, warnings?}` | new DRAFT (title required; number defaults to next; date to today) |
| `save(int $id, array $input, int $expectedVersion, ?int $userId, array $opts = []): array` | **optimistic locking**. Result: `{ok:true, changed, newsletter, warnings}` / `{ok:false, conflict:true, current:{version,updated_at,updated_by}, newsletter}` / `{ok:false, errors:{path:message}}` / `{ok:false, not_found:true}` / `{ok:false, locked:true, error}` (campaign running) / `{ok:false, status_changed:true, error}` (autosave on a non-draft) / `{ok:false, not_ready:true, errors}` (live issue left incomplete). `$opts['autosave']` = lenient content + revision merging (drafts and archived issues only); `$opts['restore']` = used by `restoreRevision()` |
| `uploadPaths(string $json): list<string>`, `imagePaths(array $row): list<string>` | the uploaded pictures a content text / an issue row uses (cover + content). After a save, a delete and the revision pruning, pictures that left the issue are passed to `ImageCleanup::releaseMany()`, which keeps any file that the issue, a saved version or another page still uses |
| `transition(int $id, string $to, array $opts)`, `publish`, `schedule(id, $publishAt, user)`, `unpublish`, `archiveIssue`, `canTransition`, `allowedTransitions` | workflow. Result `{ok, error?, errors?, warnings?, newsletter?}`; `errors` = readiness problems keyed by path |
| `duplicate(int, ?int): array`, `delete(int): array{ok,error?}` | copy as draft / delete with revisions and deliveries |
| `snapshot(...)`, `revisions(int): list`, `revision(int, int): ?array`, `restoreRevision(int $id, int $rid, int $expectedVersion, ?int $user): array`, `diffLite(array $old, array $new): list{kind,where,from,to}` | revisions |
| `defaultSubject(array $issue): string` | prefix setting + title |
| constants `TRANSITIONS`, `STATUS_LABELS`, `STATUS_TONES`, `EMAIL_LABELS`, `EMAIL_TONES`, `RESERVED_SLUGS`, `PUBLIC_ORDER`, `LIST_COLUMNS` | display and queries |

The service writes the audit entries (`newsletter.create|publish|schedule|unpublish|archive|duplicate|delete|restore`, ids and statuses only). Controllers add `newsletter.update` for manual saves (autosaves are not audited).

### `NewsletterRenderer`
`renderIssue(array $row, string $context = 'public', array $opts = []): string`, contexts `public | preview | email`; `emailHtml()`, `emailText()`, `subject()`, `preheader()`, `previewDocument($row, ['banner' => ...])`, `previewRow(?array $savedRow, array $editorInput): array` (an unsaved editor state as a displayable row, nothing written), `prepare()`, `issueLabel()`, `href()`, `imageUrl()`, `imageSize()`, `richHtml()`, `absolute()`, `absoluteUrl()`, `emailStyles()`. See section 8.

## 6. Public pages, routes, SEO (WEB agent)

| Method | Path | Handler | Notes |
|---|---|---|---|
| GET | `/newsletter` | `index` | the newest public issue (by issue date), or the friendly empty state (`noindex,follow`); canonical = that issue's own URL |
| GET | `/newsletter/archive` | `archive` | paginated grid (12), `?page=`; page 2+ canonicalises to itself; a page beyond the last is 404 |
| GET | `/newsletter/{slug}` | `show` | any public issue; everything else 404 |
| GET | `/api/newsletters` | `apiIndex` | `{"data":[{slug, issue_number, title, summary, issue_date, published_at, url, cover:{url,alt}\|null}], "meta":{page, per_page, total, pages}}`, `?page=&per_page=` (max 50), `Cache-Control: public, max-age=60` |
| GET | `/api/newsletters/{slug}` | `apiShow` | `{"data":{...same..., "content":{structured}, "html":"<article>...", "seo":{...}}}` or 404 `{"error":"Not found"}` |

All behind `page.published:newsletter` (an unpublished Newsletter page hides them) and `throttle:120,60` for the API. Delivered and working; the WEB agent refines markup and styles.
**WEB agent to do**: polish `views/web/newsletter/*` and `scss/pages/_newsletter.scss` (mobile 375 px and desktop 1360 px), add the sitemap entries in `SitemapBuilder` (`/newsletter`, `/newsletter/archive`, and `/newsletter/{slug}` for every PUBLIC issue using `NewsletterService::visibleSql`, `lastmod` = `updated_at`, only when the newsletter page is published; the archive only when at least one issue exists), `robots.txt` needs no change.
JSON-LD `NewsArticle` is in `show.php` (headline, dates, image, author / publisher organisation, `mainEntityOfPage`); keep it, extend if useful. Open Graph / Twitter come from the layout through the `meta` array the controller passes (`og_type = article`).
**Keep**: `previewResponse(array $issue, array $opts = []): Response` (used by the admin "Preview in new tab": banner, noindex, no-store, works for any status), the rule that the issue is rendered ONLY by `NewsletterRenderer::renderIssue($row, 'public')`, and the `data-nl-block` hooks of the `preview` context in the block views.

## 7. Admin: pages and JSON API (EDITOR agent; e-mail endpoints: MAILING agent)

Everything is under the `auth` middleware. `newsletter.manage` = admin + editor; **all e-mailing is `newsletter.send` = admin only** (routes AND controller methods check it). Deleting a published or once-public issue also needs `newsletter.delete_published` (admin).

### Pages
| Method | Path | Handler | State |
|---|---|---|---|
| GET | `/admin/newsletters` | `NewslettersController::index` | working baseline (status tabs, search, columns number / date / status / e-mail status, duplicate, delete with `data-confirm`) |
| GET / POST | `/admin/newsletters/create`, `/admin/newsletters` | `create`, `store` | working (title, number, date; then the editor opens) |
| GET | `/admin/newsletters/{id}/edit` | `edit` | **STUB: the EDITOR agent builds the two-pane editor** |
| GET | `/admin/newsletters/{id}/preview` | `preview` | working full-page preview (banner by status: "Preview of the live issue" / "Preview - scheduled, not published yet" / "Preview - not published"; noindex, no-store) |
| POST | `/admin/newsletters/{id}/duplicate`, `/delete` | `duplicate`, `destroy` | working |
| GET | `/admin/newsletters/{id}/email` | `NewsletterMailController::panel` | **STUB (MAILING agent), admin only** |

### JSON API
**Auth and CSRF**: session cookie; every non-GET sends the header **`X-CSRF-Token`** (value of `<meta name="csrf-token">`, `LuminaAdmin.csrf()`); send `Accept: application/json` and `Content-Type: application/json` (the core decodes JSON bodies into `$request->post()`); a missing token answers 403 `{"error":"Page expired","status":419}`, a lost session 401 `{"error":"Unauthenticated"}`.
**Error format of the newsletter controllers**: `{"error":"<code>","message":"<human text>", ...}` with codes `validation` (422, `"errors":{path:message}`), `conflict` (409 version conflict or a refused status change), `campaign_running` (409, the issue is frozen while its e-mail campaign runs), `status_changed` (409, an autosave reached an issue that is no longer a draft: the editor keeps the text, stops autosaving and tells the user to reload), `not_found` (404), `forbidden` (403), `bad_request` (400), `not_ready` (422, `errors` = what is missing: before publishing, and when a live issue is saved incomplete). Only `conflict` carries `current` / `data` and opens the "keep mine / take theirs" dialog. Framework-level failures (401, 403 CSRF, unknown route 404, 405) use the core shape `{"error":"<Title>","status":N}`.

**Issue item** (list rows omit `seo_*`, `content`, `readiness`): `{id, slug, issue_number, title, summary, issue_date, cover_image, cover_alt, cover_url, status, is_live, publish_at, published_at, version, updated_at, url, allowed:[next statuses], email:{enabled,status,total,sent,failed}, seo_title, seo_description, content:{...}, readiness:{blocking:{path:msg}, warnings:{path:msg}}}`.

| Method | Path | Role | Request | Response |
|---|---|---|---|---|
| GET | `/admin/api/newsletters` | manage | `?status=&email_status=&q=&page=&per_page=` | `{data:[item], meta:{page,per_page,total,pages,counts:{all,draft,scheduled,published,archived}}}` |
| POST | `/admin/api/newsletters` | manage | `{title*, issue_number, issue_date, summary, content}` | 201 `{data:item, warnings}` / 422 |
| GET | `/admin/api/newsletters/{id}` | manage | | `{data:item}` / 404 |
| PUT | `/admin/api/newsletters/{id}` | manage | `{version*, autosave?:bool, title, slug, issue_number, issue_date, summary, cover_image, cover_alt, seo_title, seo_description, content}` (only the keys present change) | 200 `{data:item, changed, warnings}` / **409 `{error:"conflict", current:{version,updated_at,updated_by}, data:item}`** / 422 |
| DELETE | `/admin/api/newsletters/{id}` | manage (+ `delete_published`) | | `{ok:true}` / 403 / 409 (campaign running) |
| POST | `/admin/api/newsletters/preview` | manage | `{id?, ...details, content}` (UNSAVED state) | `{html:"<!doctype html>...", readiness}` (nothing written; put it in `iframe.srcdoc`) |
| POST | `/admin/api/newsletters/upload` | manage | multipart, field `file` (jpg png gif webp, 5 MB) | 201 `{path, url, name}` / 422 |
| GET | `/admin/api/newsletters/{id}/revisions` | manage | | `{data:[{id,version,title,source,saved_by_name,created_at,bytes}]}` (latest 30) |
| GET | `/admin/api/newsletters/{id}/revisions/{rid}` | manage | | `{data:{id,version,title,source,created_at,content}, diff:[{kind,where,from,to}]}` (diff = current to that revision) |
| POST | `/admin/api/newsletters/{id}/revisions/{rid}/restore` | manage | `{version*}` | 200 `{data:item}` / 409 |
| POST | `/admin/api/newsletters/{id}/publish` | manage | | 200 `{data, warnings}` / 422 `not_ready` / 409 |
| POST | `/admin/api/newsletters/{id}/schedule` | manage | `{publish_at*:"2026-11-03T09:30"}` (Africa/Kampala time) | same |
| POST | `/admin/api/newsletters/{id}/unpublish`, `/archive` | manage | | same |
| POST | `/admin/api/newsletters/{id}/duplicate` | manage | | 201 `{data:item}` |
| GET | `/admin/api/newsletters/{id}/email` | **send** | | status: `{email:{enabled,subject,preheader,status,total,sent,failed,started_at,completed_at}, recipients:N, live:bool, driver}` |
| PUT | `/admin/api/newsletters/{id}/email` | send | `{email_enabled, email_subject, email_preheader}` | status |
| POST | `/admin/api/newsletters/{id}/email/test` | send | `{emails:[1..5 addresses]}` | `{results:[{ok,error}]}` (subject prefixed `[TEST]`, test notice in the mail, campaign untouched) |
| POST | `/admin/api/newsletters/{id}/email/queue` | send | `{confirm_count*}` (must equal the current recipient count) | status (queued) / 409 count changed / 422 not live or not enabled |
| POST | `/admin/api/newsletters/{id}/email/batch` | send | | `{processed, sent, failed, remaining, status, busy?:true}` |
| POST | `/admin/api/newsletters/{id}/email/pause`, `/resume`, `/cancel` | send | | status |
| GET | `/admin/api/newsletters/{id}/email/deliveries` | send | `?status=&page=` | `{data:[{id,email,status,attempts,last_error,sent_at}], meta}` |
| POST | `/admin/api/newsletters/{id}/email/retry` | send | | status (failed rows back to queued, attempts reset) |

The e-mail endpoints answer 501 `{"error":"not_implemented"}` until the MAILING agent builds them.

**EDITOR agent acceptance checklist** (`/admin/newsletters/{id}/edit`): two panes (edit left, **server-rendered live preview** right: debounced `POST /preview`, iframe `srcdoc`, desktop / mobile width toggle, click a block in the preview to focus it in the editor via `[data-nl-block]` read from `iframe.contentDocument`); issue details (title, number with suggestion, date, summary, cover with alt, SEO); featured article; block list with Add (Article, Image, Quote, Button, Divider), remove with confirm, **drag and drop (HTML5) plus Move up / Move down buttons**; article blocks: heading, rich text (`data-richtext` / `admin-editor.js`), optional image upload with alt and a progress indicator, optional link; toolbar Save draft, Preview in new tab, Publish now (confirm), Schedule (date-time picker, Africa/Kampala, confirm), Unpublish, Delete; **autosave every 30 s when dirty** (`PUT` with `version` and `autosave:true`; status text "Saving..." / "Saved at 12:03" / "Offline"; conflict dialog on 409 with reload / keep-mine choices; `beforeunload` guard); revisions drawer (list, diff-lite, restore); labels, focus management, `aria-live` regions, keyboard-operable everything. Editors may edit and publish; delete of a published issue and all e-mail UI are admin only.

## 8. The renderer and its views (single source of truth)

`NewsletterRenderer::renderIssue($row, $context)` is the only code that turns an issue into HTML, for the public page, the admin preview and the e-mail, so they cannot drift. It reads the normalised content (`$issue['content_data']`) and dispatches per block type:

| Context | Views (variables) | Owner |
|---|---|---|
| `public`, `preview` | `web/newsletter/_issue.php` (`$issue $content $context $featured $blocksHtml $label`), `_featured.php` (`$featured $issue $context`), `blocks/{article,image,quote,button,divider}.php` (`$block $issue $context`). `preview` adds `data-nl-block="<id>" tabindex="0"` (`featured` and `details` for the featured article and the head) | WEB agent |
| `email` | `newsletter/email/layout.php` (full document: `$issue $content $subject $preheader $browser_url $unsubscribe_url $is_test $label $org_name $org_address $footer_text $reason $logo_url $labels $featuredHtml $blocksHtml`), `newsletter/email/blocks/{featured,article,image,quote,button,divider}.php` (table rows) | MAILING agent |

Helpers the views must use (so URLs and labels stay identical): `R::href($stored, $context)`, `R::imageUrl($image, $context)`, `R::imageSize($path)`, `R::richHtml($html, $context)`, `R::isExternal($stored)`, `R::issueLabel($issue)`.
The page chrome (header / footer / subscribe box / pager / share) is NOT part of the renderer: it is in `web/newsletter/show.php`.
`previewDocument()` builds a standalone page (site fonts, `main.min.css`, body classes `page-newsletter page-newsletter--preview`, noindex) for the editor's iframe.

## 9. E-mail transport: SMTP driver

`.env` (documented in `.env.example` and the README): `MAIL_DRIVER=smtp`, `MAIL_HOST`, `MAIL_PORT` (587 / 465), `MAIL_USERNAME`, `MAIL_PASSWORD` (quote it when it contains `#`), `MAIL_ENCRYPTION=ssl|tls|none`, `MAIL_TIMEOUT` (20), optional `MAIL_CAFILE`. **Credentials live only in `.env`.** Local development keeps `MAIL_DRIVER=file` (the default when `APP_ENV=local`).

`App\Core\Smtp` (pure PHP sockets): implicit TLS (`ssl`), STARTTLS (`tls`; refuses when the server does not offer it), `none` (a login is only sent unencrypted to 127.0.0.1 / localhost), TLS 1.2+ with peer and host-name verification ALWAYS on, EHLO / HELO fallback, `AUTH PLAIN` or `AUTH LOGIN`, SIZE check, RSET between messages and one connection reused for a batch (reconnects once when the server dropped an idle connection), dot-stuffing, CRLF normalisation, timeouts on every read and write, addresses re-validated. `MailException` messages never contain the password (nor its base64 form) nor e-mail addresses; `->smtpCode`, `->transient`.

`Mailer` (frozen API for you to call):
* `Mailer::attempt($to, $subject, $text, $opts): array{ok:bool, error:?string, transient:bool, config:bool}` (`config` = the set-up is wrong for every message); `Mailer::send()` returns the bool; `Mailer::lastError()`; `Mailer::closeConnection()` (call at the end of a batch); `Mailer::driver()`.
* `$opts`: `reply_to`, `from`, `from_name`, `html`, `headers` (`['List-Unsubscribe' => '<...>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click']`; From / To / Subject / Date / Message-ID / MIME / Content-* cannot be overridden), `return_path`.
* Every message gets Date, Message-ID (`<24 hex>@from-domain>`), MIME-Version, `multipart/alternative` (text generated from the HTML when `$text` is empty), RFC 2047 UTF-8 subjects and display names (words of at most 75 characters, folded), strict CR / LF removal from every value.

**E-mail layout**: the 600 px card is `table-layout:fixed` and every text cell, heading, paragraph, list item, link and table cell carries `word-break:break-word; overflow-wrap:anywhere`, so a pasted 400-character URL wraps instead of stretching the layout (checked at 600, 375 and 320 px). Headings an editor types inside an article (19 / 17 px) stay smaller than the article title (21 px) and the featured title (24 px).

**Mock server (no credentials needed)**: `php bin/dev/mock-smtp.php [--port=2525] [--tls=none|starttls|implicit] [--user=U --pass=P] [--max-size=N] [--out=DIR] [--make-cert]`. Listens on 127.0.0.1 only, one connection at a time (the site closes its connection at the end of each request), stores every message as `storage/mock-smtp/NNNN-xxxx.eml` with `X-Mock-Mail-From` / `X-Mock-Rcpt-To` headers. Recipients whose local part starts with `reject5` get 550, `reject4` 451, `slow` a stalled answer, `dropconn` a closed connection. `--make-cert` writes a self-signed certificate (127.0.0.1 and localhost); point `MAIL_CAFILE` at it to test TLS with verification ON. To try the whole flow in the browser: start the mock, set `MAIL_DRIVER=smtp MAIL_HOST=127.0.0.1 MAIL_PORT=2525 MAIL_ENCRYPTION=none` in the local `.env`, and restore `MAIL_DRIVER=file` afterwards.

## 10. E-mailing an issue (MAILING agent)

### Messages
* HTML: `NewsletterRenderer::renderIssue($row, 'email', ['subject', 'preheader', 'unsubscribe_url', 'browser_url', 'is_test'])`; text: `NewsletterRenderer::emailText(...)`. Table layout, inline CSS, Georgia / serif fallbacks, brand colours, **absolute URLs** (built from `APP_URL`, required in production and for cron), hidden preheader, "View in browser" link, footer with the organisation address (settings `address`, `city_country`), `newsletter_footer_text`, the reason line and the **per-recipient unsubscribe link** `abs_url('unsubscribe/' . $subscriber['token'])`.
* Headers per recipient: `List-Unsubscribe: <https://site/unsubscribe/{token}>, <mailto:{reply-to or primary e-mail}?subject=Unsubscribe>` and `List-Unsubscribe-Post: List-Unsubscribe=One-Click`. The one-click POST is handled by the existing route (section 11). A `mailto:` unsubscribe request lands in the organisation's inbox and is handled by hand (Admin > Subscribers): there is no inbound mail processing.
* From: setting `newsletter_from_email` (else `MAIL_FROM`), name `newsletter_from_name` (else the site name); reply-to `newsletter_reply_to` (else `email_primary`). Subject: `email_subject` (else `NewsletterService::defaultSubject`); test sends prefix `[TEST] `.

### Queue and batch algorithm (state machine on `newsletters.email_status`)
```
none --queue--> queued --first batch--> sending --last delivery done--> sent
                  |                        |  \--pause--> paused --resume--> sending
                  \--cancel--> cancelled <-/--cancel (from queued, sending, paused)      cancelled --queue--> queued (only adds missing addresses)
```
* **Preconditions to queue**: the issue is live (`isLive`), **complete** (`NewsletterContent::readiness` has nothing blocking: an e-mailed issue can never be sent again), **the Newsletter page is published** (otherwise every "View in browser" link would be a 404; codes `incomplete`, `page_unpublished`, reported by `NewsletterMailer::contentProblem()` in `status()` and `queue()`), `email_enabled = 1` (off by default), `email_status` is `none` or `cancelled`, `confirm_count` equals the current number of subscribed addresses (so the admin confirms the exact number), the ability `newsletter.send`. `sent` is final: an issue is never e-mailed twice.
* **Queue**: `INSERT IGNORE INTO newsletter_deliveries (newsletter_id, subscriber_id, email, status) SELECT :id, id, email, 'queued' FROM subscribers WHERE status = 'subscribed'`; `email_total` = rows of the issue; status `queued`. UNIQUE(newsletter_id, email) makes it idempotent.
* **Batch** (called repeatedly by the admin page's AJAX loop, and by `bin/newsletter-send.php`): take the DB named lock `GET_LOCK('lumina_newsletter_sender', 0)` (answer `busy` when another sender holds it; released with `RELEASE_LOCK` and automatically when the connection ends), re-read the issue (stop when paused / cancelled / not live), select up to `newsletter_batch_size` (default 30) `queued` rows ordered by id; for each row: `UPDATE ... SET attempts = attempts + 1 WHERE id = ? AND status = 'queued'` (skip when 0 rows), **re-check the subscriber is still `subscribed`** (else `skipped`), build the message with that subscriber's token, `Mailer::attempt()`; success -> `sent` + `sent_at`; **permanent** failure (`transient=false`) -> `failed`; **transient** failure -> back to `queued` until `attempts` reaches 3, then `failed` (`NewsletterDelivery::MAX_ATTEMPTS`). **Configuration-level SMTP failures** (`MailException::config`: wrong or missing login 530/534/535/538, TLS or STARTTLS problems, sender refused, message larger than the server's SIZE limit) fail for EVERY recipient, so the batch stops at the first one: the recipient stays `queued` (the attempt is given back), the campaign is set to `paused`, `halted = "mail_server"` and `halt_reason` carry the (password-free) reason, and the admin page tells the owner to fix the `.env` and press Resume. Nobody is marked failed and the issue never ends as "sent" by accident. Per-recipient problems (550 unknown mailbox) still fail only that recipient; temporary ones (451, timeouts) keep the 5-in-a-row breaker. Stop early after about 20 seconds of work. Recompute `email_sent`, `email_failed` from the table, set `sending`, `email_started_at`; when no `queued` row remains set `sent` (even with some `failed`) and `email_completed_at`. `Mailer::closeConnection()` at the end.
* **Pause** (stop taking batches), **resume**, **cancel** (queued rows become `skipped`; sent rows stay). **Retry**: failed rows go back to `queued` with `attempts = 0`, campaign `sending` again (only allowed after `sent` or while paused / sending).
* **Test send**: up to 5 addresses, same renderer with `is_test`, subject `[TEST] ...`, not recorded in deliveries, does not change the campaign state.
* **Cron** `bin/newsletter-send.php` (hPanel cron every 5 minutes): continues campaigns in `queued` / `sending` (never `paused` / `cancelled`) with the same batch function and the same lock, a time budget below the host limit, no output unless `--verbose`. Needs `APP_URL` in `.env`.
* **Known limit**: if PHP dies after the SMTP server accepted a message but before the row is marked `sent`, that one recipient is retried and may get a duplicate. Marking `sent` immediately after acceptance keeps the window tiny.
* Logs and audit: ids and counts only (`newsletter.email_test`, `newsletter.email_queue`, `newsletter.email_pause|resume|cancel|retry`), never addresses or message text.

## 11. One-click unsubscribe (done)

`POST /unsubscribe/{token}` with body `List-Unsubscribe=One-Click` (RFC 8058, sent by Gmail / Outlook / Apple Mail for the `List-Unsubscribe-Post` header) unsubscribes at once: 200 `OK`, no redirect, no cookie, idempotent, unknown token 404. The route is marked `csrf.exempt` (the router skips its automatic check; the 40-hex token is the credential); the page form on the same URL is CSRF-verified inside `SubscribeController::confirmUnsubscribe`. `GET /unsubscribe/{token}` still shows the confirmation page. `robots.txt` already disallows `/unsubscribe/`.

## 12. Settings (Admin > Site settings > Newsletter)

| Key | Default | Meaning |
|---|---|---|
| `newsletter_from_name` | empty | sender name (empty = site name) |
| `newsletter_from_email` | empty | sender address (empty = `MAIL_FROM`) |
| `newsletter_reply_to` | empty | reply-to (empty = `email_primary`) |
| `newsletter_footer_text` | empty | extra footer line in every e-mail |
| `newsletter_batch_size` | `30` | recipients per batch (1 to 200) |
| `newsletter_default_subject_prefix` | empty | prefix before the issue title in the subject |

The SMTP login is NOT a setting (section 9).

## 13. Registry keys (`content('newsletter', '<section>.<key>')`)

`hero` (eyebrow title lead; **shown only while no issue is published yet**, the published issue page uses the issue's own title and summary) - `issue` (label `{number}` `{date}`, featured_label, read_more, latest_badge, contents_label) - `subscribe` (toggle; eyebrow title text placeholder button privacy_note) - `pager` (prev next archive_link latest_link) - `share` (toggle; title on copy copied) - `empty` (title text archive_title archive_text) - `archive` (eyebrow title lead read_label page_label seo_title seo_description) - `email` (view_in_browser read_online reason `{site}` unsubscribe test_notice). Heading fields accept `[[double brackets]]` for the underline. The e-mail fixed words `view_in_browser`, `read_online`, `reason`, `unsubscribe` are `required` in the Pages editor, and when one is blank anyway the e-mail uses the registry default (the visible unsubscribe link can never disappear; the `List-Unsubscribe` header is always sent). The subscribe box posts to the existing `POST /subscribe` with `source = newsletter`; the confirmation message after subscribing is `site_newsletter.messages.success` ("...You will receive our latest news and updates.").

## 14. Privacy and compliance

* Only subscribers with `status = subscribed` receive mail; unsubscribing is one click and immediate (also from the mail client's own button); the status is re-checked right before every send.
* `newsletter_deliveries` holds addresses only as the campaign record, admin only; never in logs, audit details or the public API. Audit entries carry ids and counts only.
* **Erasure**: deleting a subscriber (Admin > Subscribers, single or bulk) and erasing a member (`MembershipApplication::anonymise`) first calls `NewsletterDelivery::erase()`: every delivery row of that person gets the unique placeholder `erased-d{id}@invalid.invalid`, loses `last_error` and its link, a row still waiting becomes `skipped`; the rows themselves stay, so counters and the "never twice" guard are unchanged. The subscriber delete no longer writes the address into the activity log either. Old audit rows written before this change may still hold an address.
* SMTP credentials only in `.env`; error messages and logs never contain them.
* The subscribe box tells people what they sign up for (registry `subscribe.privacy_note`); the footer subscribe box and the `/subscribe` confirmation already say "news and updates". The owner reviews this wording.
* Uganda's Data Protection and Privacy Act, 2019 may require the foundation to register with the Personal Data Protection Office, keep records of consent and provide specific notices. **The owner must confirm this with counsel**; the site makes no legal claim (documentation only, nothing on the public pages).
* Nothing is invented: the seeded sample issue is a DRAFT built only from existing wording (planned-programs introduction, the Wii Got Quarry activity, the About founder quote, gallery captions) and stays non-public until the owner edits and publishes it, or deletes it.

## 15. File ownership

| Owner | Owns exactly |
|---|---|
| **WEB agent** | `app/controllers/Web/NewsletterController.php`, `app/views/web/newsletter/**`, `scss/pages/_newsletter.scss`, `assets/js/newsletter.js`, the newsletter additions to `app/services/Web/SitemapBuilder.php` (only that block), public API behaviour, JSON-LD |
| **EDITOR agent** | `app/controllers/Admin/NewslettersController.php`, `Admin/NewsletterApiController.php`, `app/views/admin/newsletters/*` (NOT `mail/`), `scss/admin/_newsletter.scss`, `assets/js/admin-newsletter.js` |
| **MAILING agent** | `app/controllers/Admin/NewsletterMailController.php`, `app/views/admin/newsletters/mail/**`, `app/views/newsletter/email/**` (layout + blocks), a new `app/services/Newsletter/NewsletterMailer.php` (queue / batch / test; new file), `bin/newsletter-send.php`, `assets/js/admin-newsletter-mail.js`, `scss/admin/_newsletter-mail.scss` (already imported) |
| **ARCHITECT (frozen)** | schema, seed, models, `app/services/Newsletter/{NewsletterContent,NewsletterService,NewsletterRenderer}.php`, `app/core/*` (Request, Router, Middleware, Mailer, Smtp, MailException), `SubscribeController`, registry file, `settings_schema.php`, `config/app.php`, routes, `admin_nav/newsletter.php`, `scss/main.scss` + `scss/admin.scss`, `bin/dev/mock-smtp.php`, `bin/selftest*.php`, `.env.example`, README, this file |

Rebuild CSS after any `.scss` edit: `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py`. If two agents need the same new helper each writes its own inside its own files; the architect merges afterwards.

## 16. Commands

| Task | Command |
|---|---|
| Apply schema + seed to the dev DB (idempotent) | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` |
| All tests (starts / stops its own mock SMTP server) | `/c/xampp/php/php.exe bin/selftest.php` (must print `ALL CHECKS PASSED`); newsletter part alone: `/c/xampp/php/php.exe bin/selftest-newsletter.php` |
| Mock SMTP server | `/c/xampp/php/php.exe bin/dev/mock-smtp.php --user=u --pass=p` |
| SCSS | `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py` |
| JSON API with curl | log in as in `docs/ARCHITECTURE.md` section 18, read the token from `<meta name="csrf-token">`, then `curl -b jar -H "Accept: application/json" -H "Content-Type: application/json" -H "X-CSRF-Token: $T" -X PUT --data '{"version":1,"title":"x"}' .../admin/api/newsletters/1` |

## 17. Owner to-do before the first real issue

1. Edit or delete the sample draft; write the first issue (Admin > Newsletter > Write an issue), preview it, publish it.
2. To e-mail issues: create a mailbox at the host, put its SMTP login in the server's `.env` (README), set sender name / address / reply-to / footer text under Site settings > Newsletter, send a test to yourself first (the e-mail panel of the issue; admin only).
3. Check the wording of the subscribe box (Admin > Pages > Newsletter) and confirm the legal position with counsel (section 14).
4. Deploy order: import `database/lumina-live-import.sql` first (or together with the code upload); new code against the old schema answers 500 on `/newsletter` (the footer link hides itself while the table is missing).

## 18. Changes after the audit (fixer round)

| Area | Change |
|---|---|
| Frozen campaign | `NewsletterService::save()` / `restoreRevision()` refuse every change while `email_status` is queued / sending / paused (409 `campaign_running`); the editor shows a banner and keeps the text safe in the browser |
| Stale editor tab | Autosave only for `draft` / `archived` (409 `status_changed` otherwise); live / scheduled issues are re-checked with `readiness` on every save (422 `not_ready`) |
| Campaign guards | `queue()` / `status()` refuse an incomplete issue and an unpublished Newsletter page; configuration-level SMTP errors pause the campaign at the first message |
| Privacy | `NewsletterDelivery::erase()` on subscriber delete (single, bulk) and member erasure; no address in the activity log for subscriber deletes |
| Pictures | replaced, removed, cover-cleared, pruned-from-History and deleted-with-the-issue uploads are released through `ImageCleanup` (never while a saved version, another page or setting still uses them). Pictures uploaded but never saved into any issue are not tracked (same as everywhere else on the site) |
| Numbers | UNIQUE index on `issue_number`, retry on collision, suggestion capped at 99999 |
| API | scalar `content` is a 422, not a 500; clearer refusal wording; "That version no longer exists." for a missing revision |
| E-mail | wrapping inline on every cell (long URLs), blank fixed words fall back to defaults and are `required` in the Pages editor |
| Admin UI | issues list fits 768-1279 px (date moves under the title, last-change column and thumbnail hidden, actions in two columns), the editor bar is sticky from 1280 px only and the scroll padding follows the real sticky heights, the preview starts in Phone width when its pane is narrow (never scaled below 60 %), block cards wrap their tool buttons on phones, picture replace clears the old description / caption and moves focus, field errors are linked with `aria-describedby`, no device announcement at load, the issue action bar is a `group` (not a toolbar), contrast fixes for the link in alerts and for placeholders |
| Public | table cells keep their width in rich text (the table scrolls), printed pages show the address of site-relative links (`data-print-url`), the subscribe landmark name no longer shows `[[ ]]` markers, the preview banner depends on the status, the hero fields are labelled as "only while no issue is published" |
| Tests | `bin/selftest-newsletter.php` (+ guards, pictures, erasure, layout), `bin/dev/test-newsletter-mailing.php` section J (login / size errors, frozen issue, preconditions) |

## 19. Addendum: PDF issues, live status, menu entry (see `docs/PARTNERS_NEWSLETTER.md`)

`newsletters` gained `kind` (`online` | `pdf`), `pdf_path`, `pdf_name`, `pdf_size`, `pdf_downloads`, `pdf_updated_at`; the PDF is stored in `storage/private/newsletters` and served by `/newsletter/{slug}/pdf` (inline) and `/newsletter/{slug}/download` (attachment, counter). New: `NewsletterPdf`, `NewsletterService::liveStatus()/annotate()/pageVisible()`, `kind` on create, `readiness()` for PDF issues, the Newsletter page as `Blog > Newsletter` in the header menu, Home teaser, Blog CTA. Ownership of the agents' files is in that document; the "architect files are frozen" rule of section 15 is unchanged for the files it lists, except where `PARTNERS_NEWSLETTER.md` section 8 grants the NEWSLETTER agent the renderer / mailer PDF branch.

## 20. Addendum: social media hook (see `docs/SOCIAL.md`)

`NewsletterService::transition(.. 'published')` and the lazy `resolveScheduled()` call `App\Services\Social\ShareHook::onWentLive($id, $source, $userId, $firstTime)` right after the issue became public (never throws, never delays the response: the posts run after the response through `App::afterResponse()`; with no channel configured nothing happens). `newsletters` gained `share_choices` (TEXT, the admin's per-channel choices saved with the issue, used at go-live) and `share_version` (INT, rises only on an explicit "Post again"); saving the choices changes neither `version` nor `updated_at`. The full issue item of the JSON API has `social`; `GET/PUT /admin/api/newsletters/{id}/share` (+ `/count`, `/now`, `/again`, `/dispatch`, `/status`) is the share panel API (ability `social.share`); the editor mounts the panel at `[data-social-panel]` and `admin-newsletter.js` fires `lumina:newsletter-item` and `lumina:newsletter-publish-dialog`. Unpublishing and republishing never posts again; drafts, unpublished and archived issues are never posted.
