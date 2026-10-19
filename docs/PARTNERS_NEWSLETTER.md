# Partners section + newsletter PDF / live reflection - CONTRACT

> Read `docs/ARCHITECTURE.md`, `docs/DESIGN.md` and `docs/NEWSLETTER.md` first. Two agents build on this file in parallel: the **PARTNERS agent** and the **NEWSLETTER agent**.
> Everything marked *architect* below is **done, tested and frozen**: call it, do not copy or edit it. Missing something? Add your own file and list the gap in your report.
> Run the tests: `/c/xampp/php/php.exe -d extension=gd bin/selftest-partners-newsletter.php` (the GD checks need `-d extension=gd`; XAMPP ships `php_gd.dll` but leaves it off).

Owner request: (1) a **Partners section under About**, easy to add / delete from the admin, with logos that are optimised and look good; (2) the **newsletter must be findable on the website and every admin change must show at once**, with both an **uploaded PDF** and a **written-online** format.

---------------------------------------------------------------------------------------------------------------------

## 1. What the architect found (state before the change)

| Area | Finding |
|---|---|
| Partners | A module already existed: table `partners`, `/partners` page, admin CRUD (`Admin/PartnerController`, list + forms + reorder page), footer strip, Partners as a child of About in the menu (row `pages.partners`: `show_in_nav=1`, `parent_slug=about`, registry has no `nav.requires`, so it is always visible). Logos went through `Uploader::image()` (max 2000 px wide, no trimming, no WebP, no sizes in the table). |
| Newsletter | `/newsletter` **worked** and showed the owner-published sample issue. It was simply **not discoverable**: `pages.newsletter` had `show_in_nav=0` (footer only), nothing on Home, About or Blog. The dev row was also **not pristine** (`updated_at <> created_at`), so a pristine-only seed refresh could never move it: see section 11. |
| GD | The local PHP has **no GD** (extension off) and the live host is only *assumed* to have it. The old uploader silently skipped resizing without GD. |
| Uploader | `uploads/.htaccess` serves **images only**; there was no safe place for PDFs apart from `storage/private/` (never served by Apache). |
| CSP | `frame-src` listed Google / YouTube only (no `'self'`) and `object-src 'none'`: a same-origin PDF `<iframe>` was **blocked**. |

### Stale-cache audit (can an admin change appear late?)

| Layer | Result |
|---|---|
| Public HTML (every page, `/newsletter*`, Home, About, Blog) | `Cache-Control: private, max-age=0, must-revalidate`: browsers revalidate, shared caches must not store. **OK.** |
| App-level caches | `Settings`, `Content`, `SitePages`, `Nav`, `PageRegistry`, `NewsletterService::$resolved` are **per-request static arrays only**; nothing is stored between requests (no APCu, no file cache; `storage/cache` is unused). Every admin save is visible on the next request. **OK.** |
| Scheduled issues | Visibility is computed from the application clock on every read (`NewsletterService::visibleSql`); `resolveScheduled()` flips the stored status lazily. No cron needed. **OK.** |
| `GET /api/newsletters*` | `Cache-Control: public, max-age=60` + ETag: **up to 60 s stale** in browsers and shared caches / CDN. **RISK. NEWSLETTER agent must change it to `public, max-age=0, must-revalidate`** (the ETag then gives cheap 304s and no delay). |
| `/sitemap.xml` | was `public, max-age=900` (15 min). **Fixed by the architect**: `public, max-age=0, must-revalidate`. |
| Uploaded images (`uploads/`) | `public, max-age=30 days`, **safe**: every upload and every logo variant has a new random name; a replace never reuses a name. |
| `assets/` CSS / JS | 1 year, **safe**: `asset()` appends `?v=<mtime>`. |
| PDFs | stored privately and streamed with `Cache-Control: private, no-store`; swapping a PDF gets a new random file name; unpublishing returns 404 at once. **OK.** |
| Server-level page cache (LiteSpeed on shared hosts) | Added to `.htaccess`: `RewriteRule ^ index.php [L,E=Cache-Control:no-cache]` (LiteSpeed reads it, Apache ignores it). Defence in depth; the app headers already say "private". |
| OPcache | PHP files (registry, config) are cached by OPcache until the file's mtime is re-checked (`opcache.revalidate_freq`, 2 s by default on shared hosts). If a host sets `opcache.validate_timestamps=0`, a code deploy needs an OPcache reset. Content edited in the admin lives in the database and is **not** affected. |
| Browser back button | Public HTML may come from the back/forward cache; a normal navigation always revalidates. Nothing to fix. |

The real reasons an issue "cannot be seen" are therefore (a) the issue is still a **draft** / scheduled / archived, (b) the **Newsletter page is switched off** (`pages.is_published = 0`: every issue answers 404), (c) there was **no link** to the page. This work removes (c) and makes (a) and (b) visible in the admin (section 8).

---------------------------------------------------------------------------------------------------------------------

## 2. What exists (architect, done and tested)

| Area | Files |
|---|---|
| Schema / seed | `database/schema.sql`: guarded `ALTER`s after `partners` and after the `newsletters` migrations. `database/seed.sql`: settings, the `newsletter` pages row, the one-time menu upgrade. `database/lumina-live-import.sql` rebuilt (`php bin/build-import-sql.php`). |
| Core | `app/core/ImageOptimizer.php` (logo pipeline), `app/core/PdfValidator.php`, `Uploader::logo()`, `Uploader::pdf()`, `pdfMaxBytes()`, `serverUploadLimit()`, `privateExists()`, `copyPrivate()`; `Response` CSP (`frame-src 'self'`); `SeoController` sitemap header; `.htaccess`. |
| Services | `app/services/PartnerLogos.php`, `app/services/Newsletter/NewsletterPdf.php`; `NewsletterService` (kind, `liveStatus()`, `annotate()`, `pageVisible()`, duplicate / delete handle the PDF), `NewsletterContent::readiness()` (PDF issues), `ImageCleanup` (new logo columns). |
| Partials | `app/views/partials/partner-logo-img.php` (the ONE logo markup). |
| Registry | `about.php` section `partners`; `home.php` section `newsletter`; `blog.php` section `newsletter`; `newsletter.php` sections `pdf`, `empty.when`, `email.download_pdf`, nav now `Blog > Newsletter`. |
| Settings | group **Partners** (`partners_logo_hover_grey`, `partners_logo_trim`), `newsletter_pdf_max_mb` in group Newsletter. |
| Routes + stubs | `app/routes/admin_partners.php` + `Admin/PartnerApiController` (stubs, `optimise` works); `app/routes/web_newsletter_pdf.php` + `Web/NewsletterPdfController` (working baseline); `app/routes/admin_newsletter_pdf.php` + `Admin/NewsletterPdfApiController` (working baseline). |
| Include points | `web/about/index.php` -> `web/about/_partners` (stub); `web/home/index.php` -> `web/newsletter/_home-teaser` (stub); `web/blog/index.php` -> `web/newsletter/_blog-cta` (stub). |
| Styles / JS | `scss/components/_partner-logo.scss` (baseline), `scss/admin/_partners.scss` (empty), `assets/js/admin-partners.js` (stub); both SCSS files are already imported. |
| Tests | `bin/selftest-partners-newsletter.php` (about 120 checks, hooked into `bin/selftest.php`). |

---------------------------------------------------------------------------------------------------------------------

## 3. Schema (additive, idempotent; guarded `ALTER` like `programs.status`)

### `partners` (new columns; existing rows untouched)
| Column | Meaning |
|---|---|
| `logo` | the **fallback picture** (PNG, or JPEG for a heavy opaque logo) at 1x, relative to `uploads/`; for rows saved before the pipeline / without GD it is the validated original |
| `logo_2x` | the same picture at exactly twice the size, or NULL (source too small: never upscaled) |
| `logo_webp`, `logo_webp_2x` | WebP versions (quality 85) or NULL (GD without WebP) |
| `logo_width`, `logo_height` | pixel size of the **1x** picture (padding included): print them as `width` / `height` attributes |
| `logo_optimised` | 1 = made by the pipeline; 0 = legacy / stored as uploaded (the admin shows a notice and the "Optimise existing logos" button) |

Decision: individual columns, not JSON, so the admin list can `WHERE logo_optimised = 0` and `ImageCleanup` can check each path with a plain `=`. Existing index `ix_partners_list (is_active, partner_type, sort_order)` serves both the public and the admin ordering; no new index.

### `newsletters` (new columns)
`kind ENUM('online','pdf') NOT NULL DEFAULT 'online'` (all existing issues stay online), `pdf_path VARCHAR(255) NULL` (relative to `storage/private`), `pdf_name VARCHAR(190) NULL` (original file name, for display and download), `pdf_size INT UNSIGNED NOT NULL DEFAULT 0`, `pdf_downloads INT UNSIGNED NOT NULL DEFAULT 0`, `pdf_updated_at DATETIME NULL`; index `ix_newsletters_kind (kind, status)`.
`content` stays `NOT NULL` JSON: a PDF issue stores the empty content structure.

Apply on a dev database with `php bin/setup.php` (idempotent, not `--fresh`). Live: re-import `database/lumina-live-import.sql` (tested on a database built from the previous import: the published sample issue, a real partner row with a legacy logo and an owner-edited `pages` row are untouched; run twice, nothing changes).

---------------------------------------------------------------------------------------------------------------------

## 4. Logo pipeline (architect)

```php
$r = Uploader::logo($request->file('logo'));            // upload: name rules, 5 MB, then ImageOptimizer::logo()
$r = ImageOptimizer::logo('/abs/path/to/image.png', ['trim' => true, 'dir' => 'partners/2026/10']);   // any file on disk
```
Both throw `UploadException` (message safe for the admin) and return:

| Key | Meaning |
|---|---|
| `logo`, `logo_2x`, `logo_webp`, `logo_webp_2x` | relative paths below `uploads/` (`partners/YYYY/MM/<24 hex>[-2x].png|jpg|webp`) or null |
| `logo_width`, `logo_height` | size of the 1x picture |
| `optimised` | false when GD is missing / cannot read the format: the validated original (EXIF / XMP / text chunks removed by `ImageScrubber`) was stored |
| `notice` | null or a sentence for the admin |
| `warnings` | codes: `trimmed`, `background_removed` (a flat light off-white background such as #F4F4F0 / cream / light grey of a JPEG logo was made transparent), `opaque_background`, `low_resolution` (content under 320 x 160 px: tell the admin to upload a larger file), `no_webp`, `not_optimised` |
| `files` | every path written: delete them if the database save fails |
| `sizes`, `source` | bytes per file; source mime / size |

Rules (all tested): real type by magic bytes + finfo + `getimagesize` (PNG, JPEG, WebP, GIF; SVG / HTML / PHP / truncated / zero-size refused), at most 6000 x 6000 px and 5 MB read from the header before any decode, polyglots (`<?php`, `<script`, `<svg`, `<html` anywhere in the bytes) refused (a bare `<?` is NOT in the filter: it occurs by chance in compressed data and would refuse most real images; instead `ImageScrubber` cuts a JPEG at the EOI and a GIF at the trailer found by walking the file structure, PNG stops at IEND, WebP at the RIFF size, so appended payloads never reach the stored file even without GD), the decode must fit in `memory_limit` (otherwise a friendly "too large for this server" error, never a fatal), EXIF orientation 2-8 applied, **all metadata dropped by re-encoding**, **flat light background removal** (only when all four corners and 93% of the border are one colour, every channel >= 200 and not already white enough for the white rules: it becomes transparent with a soft edge, so no tinted rectangle shows inside the white box; dark / coloured badge backgrounds, photos and gradients are never touched), **trim** of transparent margins (or near-white margins when the picture has no transparency; a white logo on a transparent background is kept), work copy at most 1920 px, fit into 480 x 240 with 6 px transparent padding, the **2x picture is exactly twice the 1x one** (so `srcset="a 1x, b 2x"` never changes the shown size) and only made when the source has the pixels (never upscaled; tiny logos are stored once at their own size), PNG compression 9, WebP quality 85 with alpha, an opaque logo whose PNG would exceed 80 KB gets a JPEG fallback. Typical time: 0.3 s for a 1600 x 800 logo.
Capability detection at run time (`ImageOptimizer::capabilities()`: gd, png, jpeg, webp, gif, exif, finfo, rotate; `capabilityNotice()` gives the admin sentence). Tests can simulate a host: `ImageOptimizer::forceCapabilities(['gd' => false])`.

`PartnerLogos` (service, static):
| Method | Use |
|---|---|
| `store(?array $file): array{columns,result}` | run the pipeline; `columns` go straight into `Partner::create()/update()` |
| `emptyColumns()` | columns to remove a logo |
| `columns(array $result)`, `paths(array $row)` | mapping helpers |
| `release(array $oldRow): int` | AFTER the save/delete: removes the files nothing else uses (other partners, pages, settings, rich text are checked by `ImageCleanup`), so **shared files are never deleted** |
| `discard(array $result)` | the save failed after `store()`: remove the new files |
| `view(array $row)` | `{has, alt, src, srcset, webp, webp_srcset, width, height, optimised}` for templates |
| `pendingCount()`, `optimiseExisting(int $limit, int $afterId)` | backfill of legacy logos, a few per call, cursor `last_id`, `remaining`; failures are skipped and reported, without GD nothing is touched (`notice`) |

Partial `partials/partner-logo-img` (vars `partner`, `loading`, `class`, `sizes`) prints `<picture class="partner-logo-pic">` with a WebP `<source>` and an `<img class="partner-logo-img" width height alt loading="lazy" decoding="async">`; alt = partner name; nothing when the partner has no logo.

---------------------------------------------------------------------------------------------------------------------

## 5. PDF newsletters: services and routes (architect + baseline controllers)

**Storage decision (deviation from the brief's `uploads/newsletters`)**: PDFs live in **`storage/private/newsletters/YYYY/MM/<32 hex>.pdf`**, never below the web root. `uploads/.htaccess` serves images only, and a file reachable by URL would keep working after the issue is unpublished, deleted or its PDF swapped. Streaming through the gated route makes every admin change effective immediately, keeps drafts private and allows the download counter. Cost: none for visitors (same URLs).

`Uploader::pdf($file, ['max_bytes'])` -> `{path, name, size, mime, pages}`; limit = `Uploader::pdfMaxBytes()` = min(setting `newsletter_pdf_max_mb` (default 25), the server's `upload_max_filesize` / `post_max_size`); show both numbers to the admin (`Uploader::serverUploadLimit()`).
`PdfValidator::inspect()` accepts only: `%PDF-1.x|2.x` **at byte 0**, finfo `application/pdf`, `%%EOF` in the last 2 KB, not `/Encrypt`ed, no `/JavaScript`, `/Launch`, `/SubmitForm` or `/ImportData`. The WHOLE file is scanned whatever its size (1 MiB chunks overlapping by 64 bytes) with `#xx` name escapes decoded (`/Java#53cript` is caught). `/JS` alone is not listed (3 bytes would match random compressed data; a /JS key only runs inside an /S /JavaScript action) and `/GoToR`, `/GoToE`, `/RichMedia`, `/EmbeddedFile` are not refused because legitimate PDFs use them; client name must end in `.pdf` with no dangerous middle / double extension (`a.php.pdf`, `a.pdf.html`, `a.pdf.exe` refused).

`NewsletterPdf` (static): `has($row)`, `exists($row)`, `attach($id, $file, $userId)`, `remove($id, $userId)`, `copyColumns($row)`, `releaseFile($path, $exceptId)`, `response($row, $inline)`, `recordDownload($id)`, `downloadName($row)`, `urls($row, $abs)`, `info($row, $abs)`.
* `attach` does not bump `newsletters.version` (a file operation like a status change; an open editor keeps saving text without a conflict, the editor must refresh its PDF box from the response). It resets the download counter and deletes the previous file. Refused while an e-mail campaign is queued / sending / paused.
* `remove` is refused for a **PDF-format issue that is published or scheduled** (the page would be empty).
* `NewsletterService::create()` accepts `kind` (`online` default, `pdf`); the format **cannot change** afterwards (422 `kind`). `readiness()` for `kind = pdf`: blocking `pdf` ("Upload the PDF of this issue.") instead of content checks; for an online issue a missing attached file is only a warning. `duplicate()` copies the PDF to its own file; `delete()` releases it.
* `LIST_COLUMNS` now include `kind, pdf_name, pdf_size, pdf_downloads, has_pdf`; `adjacent()` includes `kind`.

Routes (public, `page.published:newsletter`, GET only):
| Path | Handler | Behaviour |
|---|---|---|
| `/newsletter/{slug}/pdf` | `NewsletterPdfController::view` | (throttle 60 requests / 5 min per visitor and issue) inline PDF for the iframe / "Read in a new tab"; visible issues with a PDF only, else 404 |
| `/newsletter/{slug}/download` | `::download` | (same throttle) attachment (`Content-Disposition` with the original name), `pdf_downloads + 1` (not for HEAD) |
Headers: `Content-Type: application/pdf`, `X-Content-Type-Options: nosniff`, `Cache-Control: private, no-store`, `X-Robots-Tag: noindex`, `X-Frame-Options: SAMEORIGIN`. **CSP**: `frame-src 'self'` was added (the minimal change; `object-src 'none'` unchanged, so embed with `<iframe>`, not `<object>`/`<embed>`).

Routes (admin, `auth` + `newsletter.manage`): `POST /admin/api/newsletters/{id}/pdf` (multipart field `pdf`; 201 `{data:{id,kind,pdf,live}}` / 422 `validation` / 409 `campaign_running`), `DELETE /admin/api/newsletters/{id}/pdf`, `GET /admin/api/newsletters/{id}/live`, `GET /admin/newsletters/{id}/pdf` (inline stream for any status: the admin preview). Creating an issue: `POST /admin/api/newsletters` and the form `POST /admin/newsletters` take `kind` (the form controller currently passes only title / number / date / summary: add `kind`).

---------------------------------------------------------------------------------------------------------------------

## 6. Partners agent: admin screens, JSON API, public logo rendering

### Deliver
* **Admin > Partners** (`Admin/PartnerController` index/create/edit views, `assets/js/admin-partners.js`, `scss/admin/_partners.scss`; keep `/admin/partners` and the existing POST routes working):
  * **Quick-add bar** at the top: Name*, Logo (drag-and-drop or click-to-choose, instant preview via `URL.createObjectURL`), Website, Type, one **Add** button (`POST /admin/api/partners`, multipart, no page reload; the new card appears at once; warnings such as `low_resolution` shown as a gentle hint).
  * **Card grid** showing each logo exactly as the website will (use `partials/partner-logo-img` inside the same box class as the public side), name, type badge, website, **Show/Hide switch** (one click), edit, delete.
  * **Delete** with a confirmation dialog and a friendly message ("Removed. The logo file was deleted."); **multi-select** with a bulk bar (show, hide, delete).
  * **Drag-and-drop reordering** (HTML5 drag) **plus Move up / Move down buttons** (keyboard alternative, `aria-live` announcement), saved automatically via `POST /admin/api/partners/reorder`.
  * **Inline edit** of name / website / type; **Replace logo**; **Remove logo**.
  * Help text: "Use a PNG with a transparent background, at least 400 px wide." Empty state. A notice when `ImageOptimizer::capabilityNotice()` is not null and an **"Optimise existing logos"** button when `PartnerLogos::pendingCount() > 0` (loop `POST /admin/api/partners/optimise` with `after = last_id` until `remaining = 0`).
  * Fix `PartnerController::deleteMany()` and `update()` to use `PartnerLogos::release($oldRow)` (not `Uploader::delete`) and `store/update` to use `PartnerLogos::store()`.
* **Public** (`web/about/_partners.php`, `web/partners/*`, `partials/partner-strip.php`, `partials/partner-grid.php`, the SCSS below): all three places print logos with `partials/partner-logo-img` inside a **uniform box** (same height per breakpoint, `object-fit: contain`, `mix-blend-mode: multiply` so white-background JPG logos look clean on light sections, optional grey-to-colour on hover only when setting `partners_logo_hover_grey` is on **and** `(hover: hover)` and not `prefers-reduced-motion`, max heights per breakpoint, no layout shift because `width` / `height` are printed).
  A partner with a website: the logo is a link (`target="_blank" rel="noopener noreferrer"`, accessible name = partner name); without: no link. A partner without a logo: its name as text. Anything that echoes the website goes through `OrgPublicController::safeUrl()`.

### JSON API (`Admin/PartnerApiController`; routes in `app/routes/admin_partners.php`)
Auth: session + header `X-CSRF-TOKEN`, `Accept: application/json`. Abilities `content.manage` (admin, editor) and `content.delete`. Errors `{"error":"validation|not_found|forbidden","message":"...","errors":{field:msg}}`. Audit ids only (`partner.create|update|delete|reorder|bulk|logo|optimise`).

| Method | Path | Request | Response |
|---|---|---|---|
| GET | `/admin/api/partners` | | `{data:[item], meta:{total, active, hidden, pending_logos, missing_logos, notice, about_limit, footer_limit}}` |
| POST | `/admin/api/partners` | multipart `name*, logo?, website?, partner_type?, is_active?` | 201 `{data:item, warnings:[codes]}` / 422 |
| POST | `/admin/api/partners/{id}` | JSON any of `name, website, partner_type, description, is_active` | `{data:item}` |
| POST | `/admin/api/partners/{id}/logo` | multipart `logo` | `{data:item, warnings}` (old files released after the save) |
| POST | `/admin/api/partners/{id}/logo/remove` | | `{data:item}` |
| POST | `/admin/api/partners/{id}/toggle` | optional `{is_active}` | `{data:item}` |
| POST | `/admin/api/partners/{id}/delete` | | `{ok:true, name}` |
| POST | `/admin/api/partners/reorder` | `{ids:[...]}` all ids in the new order | `{ok:true}` (`sort_order` = 10, 20, ...) |
| POST | `/admin/api/partners/bulk` | `{action:'show'|'hide'|'delete', ids:[...]}` | `{ok:true, count}` |
| POST | `/admin/api/partners/optimise` | `{after?, limit?}` | **working**: `{done, failed, remaining, last_id, notice, errors:{id:msg}, capabilities}` |

**Item**: `{id, name, partner_type, website, description, is_active, sort_order, created_at, logo:{has, alt, src, srcset, webp, webp_srcset, width, height, optimised}}` (`logo` = `PartnerLogos::view($row)`).
**Save rule**: `$up = PartnerLogos::store($file)` -> `Partner::create/update($data + $up['columns'])` -> on exception `PartnerLogos::discard($up['result'])` -> after success `PartnerLogos::release($oldRow)`.

### Ownership (PARTNERS agent)
`app/controllers/Admin/PartnerController.php`, `Admin/PartnerApiController.php`, `app/views/admin/partners/**`, `assets/js/admin-partners.js`, `scss/admin/_partners.scss`, `app/controllers/Web/PartnersController.php`, `app/views/web/partners/**`, `app/views/web/about/_partners.php`, `app/views/partials/partner-grid.php`, `partner-strip.php` (logo markup only), `scss/components/_partner-logo.scss`, `_partner-grid.scss`, `_partner-strip.scss`, `scss/pages/_partners.scss`, `app/config/admin_nav/org.php` (only if the screen needs a new item), `assets/js/pages/admin-org.js` (partner part). **Do not edit** the registry, schema, models, services, `Uploader`, `ImageOptimizer`, `main.scss`, `admin.scss`.

---------------------------------------------------------------------------------------------------------------------

## 7. About page: "Our partners" section

Registry (`about.php`, section `partners`, toggle, default on): `eyebrow`, `title` (default "Our Partners", `[[brackets]]` underline), `intro` (the live site's partners wording), `limit` (24; 4-60), `all_cta_label` ("See all partners"; empty hides the link) / `all_cta_url` (`/partners`). Read with `content('about','partners.title')`, `content_btn('about','partners.all_cta')`.
`web/about/_partners.php` (included just above the donate CTA): `section_begin('about','partners', [...])`; `Partner::active()` (already ordered `sort_order, id`); **nothing at all** when empty; group by type **only if more than one type** exists (group headings from `content('partners', $type.'.title')`, otherwise a single grid); at most `limit` logos, the link to `/partners` shown when there are more than `limit` partners **or always** when the label is set (agent's choice, document it); a responsive grid of uniform boxes (2 columns on phones, 3 from 576 px, 4-6 on desktop). The menu already contains Partners under About: keep it (do not add `nav.requires`).

---------------------------------------------------------------------------------------------------------------------

## 8. Newsletter agent: public side, admin side, live reflection

### Discoverability (done by the architect: nav; to build by the agent: teaser and CTA)
* **Menu**: `pages.newsletter` is now `show_in_nav=1`, `parent_slug=blog`, `nav_order=73` (Blog > Newsletter, after Impact Stories) and still in the footer. Upgrade rule in `seed.sql`: the pristine-only refresh handles untouched rows; a **one-time marker** (`settings.migration_newsletter_nav_v1`) moves a row that the owner edited but whose three menu fields still hold the first-build values (`show_in_nav=0, parent NULL, order 75`). Once the marker exists nothing re-applies it: if the owner hides the item in Admin > Menus it stays hidden. The Menus screen still moves / hides it.
* **Home teaser** `web/newsletter/_home-teaser.php` (stub; included between Impact stories and FAQ): `NewsletterService::latestPublished()`; renders nothing when there is none or `!NewsletterService::pageVisible()`; card with cover (`cover_image`, else a branded fallback panel using `content('newsletter','pdf.card_fallback')`), `issueLabel`, title, summary (`excerpt`), badge (`home.newsletter.badge`), PDF badge for `kind = pdf`, button (`home.newsletter.cta_label`) to `NewsletterService::url($issue)`, link to all issues (`archive_cta`). Section toggle `section_begin('home','newsletter')`.
* **Blog CTA** `web/newsletter/_blog-cta.php` (stub; above the subscribe box): hidden while `NewsletterService::countPublished() === 0` or the page is off; words from `content('blog','newsletter.*')`.
* **Newsletter page**: a clear Subscribe box on `/newsletter`, the issue page and the archive (exists: registry `subscribe`, toggle); the empty state shows `content('newsletter','empty.when')` (editable "when the first issue will appear").

### Public rendering of PDF issues (`/newsletter/{slug}`, `web/newsletter/show.php` + `_issue.php`)
Title, number / date label, summary, then: **mobile first**: the Download PDF / Read in a new tab buttons come BEFORE the viewer on small screens; an embedded viewer (`<iframe src="…/newsletter/{slug}/pdf" title="…" loading="lazy">`, height about 80vh, hidden or collapsed below 768 px where most mobile browsers cannot embed a PDF, with `content('newsletter','pdf.fallback')` text inside the iframe element for old browsers); **Download PDF** button (`pdf.download_label`) with the size (`pdf.size_label`, `NewsletterPdf::info()['size_human']`) and, only when `pdf.show_downloads` is on, the counter; "Read in a new tab" (`pdf.open_label`, `target="_blank" rel="noopener"`); share links; print stylesheet: hide the viewer, show title, summary and the download URL. An **online** issue with a PDF shows a "Download this issue as PDF" link (`pdf.attached_label`). The archive card shows the PDF badge (`pdf.badge`) and the cover, or a branded fallback card. `/newsletter` (latest) works for both kinds.
SEO: PDF issues get `Article` JSON-LD (`@type` `Article`/`NewsArticle`, plus `encoding` / `associatedMedia` = `MediaObject` `contentUrl` the absolute `/pdf` URL, `encodingFormat application/pdf`, `contentSize`); sitemap lists PDF issues like online ones (`SitemapBuilder` newsletter block, `lastmod = updated_at`); the PDF routes carry `noindex`.
JSON API (`/api/newsletters*`): add `kind`, `pdf: {url (download), view_url, size, name} | null`; **change `API_CACHE` to `public, max-age=0, must-revalidate`** (section 1).

### E-mail for PDF issues
No attachment (deliverability: 5-25 MB attachments push the message over most providers' size limits and into spam, multiply the sending time, and the PDF could not be swapped afterwards). The e-mail contains the cover, title, summary and two buttons: **Read online** (`email.read_online`, the issue URL) and **Download PDF** (`email.download_pdf`, absolute `/download` URL; counted). Implement in `NewsletterRenderer::emailHtml/emailText` + `views/newsletter/email/*` for `kind = pdf`; online issues are unchanged. `NewsletterMailer` preconditions are unchanged (`readiness` already requires the PDF for a PDF issue).

### Admin (EDITOR work, now NEWSLETTER agent)
* **Create**: a choice "Write online" / "Upload a PDF" (`kind`), then the editor. **PDF issue form**: title, issue number, date, summary, optional cover (existing image field), PDF drop zone (drag-and-drop or choose; show `Uploader::pdfMaxBytes()` and the server limit; progress; uses `POST …/{id}/pdf`; shows name, size, "Replace PDF", "Remove PDF", "Open PDF" -> `/admin/newsletters/{id}/pdf`), same draft / publish / schedule / unpublish / archive / duplicate / delete, autosave of the metadata (`PUT` with `version`), Preview. An online issue gets an optional "Attach a PDF" box with the same component. Add `kind`, `pdf` (`NewsletterPdf::info`), `live` (`NewsletterService::liveStatus`) to `NewsletterApiController::itemFor()`.
* **"Live on the website" box** on every issue (editor + refreshed after every save / publish / unpublish / schedule / PDF change from the response, and on `GET …/live`): `liveStatus($row)` gives `label` ("Live on the website" / "Scheduled for 3 Nov 2026, 09:30" / "Draft — not visible on the website" / "Unpublished — not visible on the website" / "Archived — not visible…" / "Published — but the Newsletter page is switched off"), `tone`, `detail`, `reasons[]` (why it is not visible: draft, future schedule, archived, missing required fields, page switched off, running e-mail campaign), `url` ("View on the website", enabled only when `visible`), `preview_url` ("Preview"), `is_latest`, `in_menu` (warn when the Newsletter page is not in the menu).
* **Confirmations**: after publish "Published — it is now live at /newsletter/{slug}" with the link; after schedule "Scheduled — it will appear on … (Uganda time)"; after unpublish "Taken down — visitors now get “not found”".
* **List** (`NewslettersController::index` -> `NewsletterService::annotate($p->items)`): a Format column (PDF badge), and under each row that is not visible the `live.reasons` as a short warning line; a tab-level hint when the Newsletter page is switched off (`!pageVisible()`).
* Delete of a published issue stays admin-only; PDF upload and replace are `newsletter.manage`.

### Ownership (NEWSLETTER agent)
`app/controllers/Web/NewsletterController.php`, `NewsletterPdfController.php`, `app/views/web/newsletter/**` (incl. the two stubs), `scss/pages/_newsletter.scss`, `assets/js/newsletter.js`, the newsletter block of `app/services/Web/SitemapBuilder.php`, `app/services/Newsletter/NewsletterRenderer.php` and `NewsletterMailer.php` (PDF branch only), `app/views/newsletter/email/**`, `app/controllers/Admin/NewslettersController.php`, `NewsletterApiController.php`, `NewsletterPdfApiController.php`, `app/views/admin/newsletters/**`, `scss/admin/_newsletter.scss`, `assets/js/admin-newsletter.js`. **Do not edit** registry, schema, models, `NewsletterService/Content/Pdf`, core, `main.scss`, `admin.scss`.

---------------------------------------------------------------------------------------------------------------------

## 9. Registry keys (`content('<page>', '<section>.<key>')`)

| Page | Section.keys |
|---|---|
| `about` | `partners._visible`, `partners.eyebrow`, `.title`, `.intro`, `.limit`, `.all_cta_label`, `.all_cta_url` |
| `home` | `newsletter._visible`, `.eyebrow`, `.title`, `.lead`, `.cta_label`, `.archive_cta_label`, `.archive_cta_url`, `.badge` |
| `blog` | `newsletter._visible`, `.eyebrow`, `.title`, `.text`, `.button_label`, `.button_url`, `.archive_label`, `.archive_url` |
| `global` (footer) | `footer.partners_limit` (12; 4-40), `footer.partners_all_label` ("See all partners", links to /partners and appears only when some partners are not in the strip) |
| `newsletter` | `pdf.badge`, `.download_label`, `.open_label`, `.size_label` (`{size}`), `.downloads_label` (`{count}`, used exactly as written), `.downloads_label_one` (`{count}`, used when the count is 1; empty = use the line above), `.show_downloads`, `.viewer_title` (`{title}`), `.fallback`, `.summary_label`, `.attached_label`, `.card_fallback`; `empty.when`; `email.download_pdf` |
| settings | `partners_logo_hover_grey`, `partners_logo_trim`, `newsletter_pdf_max_mb` |

---------------------------------------------------------------------------------------------------------------------

## 10. Live reflection rules (every agent)

1. Never cache newsletter, partner, settings or navigation data between requests (no static caches that outlive the request, no files). Reading the table on every request is the design.
2. Public HTML keeps the default `private, max-age=0, must-revalidate`; JSON / feeds revalidate (`max-age=0, must-revalidate` + ETag); file streams are `private, no-store`.
3. New files always get a new random name (replace = new name), so long-lived caching of `uploads/` is safe.
4. The admin must show the truth: use `NewsletterService::liveStatus()` for the box and the list; after any write return the fresh row / status in the response so the UI never shows a stale state.
5. Partners: a hidden / deleted / reordered partner is visible on the next page load; nothing to flush. After a write call nothing else (the old `SitePages::flush()` / `Nav::flush()` is only needed for page / menu edits).

---------------------------------------------------------------------------------------------------------------------

## 11. Seed, upgrade and live import

* Nothing invented: no partner and no issue is seeded. Settings: `newsletter_pdf_max_mb=25`, `partners_logo_hover_grey=1`, `partners_logo_trim=1` (refreshed only while pristine).
* The `newsletter` pages row uses the `INSERT … SELECT … ON DUPLICATE KEY UPDATE` form with the pristine-only refresh (like `calendar`), parent looked up from `blog`; plus the one-time marker migration described in section 8. Tested on a database imported from the previous `lumina-live-import.sql`: published sample issue kept, real partner row kept (logo path untouched, `logo_optimised = 0`), owner's SEO edit kept, menu entry added once; a third import after the owner hid the entry left it hidden.
* Deploy order: import `database/lumina-live-import.sql` first (new code against the old schema fails on the new columns), upload the code, then Admin > Partners > "Optimise existing logos" (only if GD is enabled on the host).

## 12. Commands

| Task | Command |
|---|---|
| Apply schema + seed to the dev DB | `/c/xampp/php/php.exe bin/setup.php` |
| Rebuild the one-file live import | `/c/xampp/php/php.exe bin/build-import-sql.php` |
| This feature's tests | `/c/xampp/php/php.exe -d extension=gd bin/selftest-partners-newsletter.php` (without `-d extension=gd` the GD checks are skipped; the no-GD path is always tested) |
| Everything | `/c/xampp/php/php.exe -d extension=gd bin/selftest.php` and once without the flag; both end with `ALL CHECKS PASSED`. It now also runs `selftest-partners-ui.php` and `selftest-newsletter-live.php` (the HTTP part of the latter skips itself when `http://localhost/<folder>/` does not answer) |
| Browser testing WITH GD | the XAMPP Apache has no GD: run `/c/xampp/php/php.exe -d extension=gd -S 127.0.0.1:8099 index.php` from the project root and open `http://127.0.0.1:8099/` (or enable `extension=gd` in `C:\xampp\php\php.ini` and restart Apache) |
| SCSS | `PYTHONUTF8=1 "/c/Users/SANTOS JR/AppData/Local/Python/bin/python.exe" bin/build-scss.py` |

## 13. Known limits and risks

* **GD on the live host is an assumption.** Without it logos are stored as uploaded (metadata stripped, not trimmed / resized, no WebP); the admin shows a notice and the cards still look uniform because the CSS box does the fitting. Ask Hostinger to keep the `gd` extension on (it normally is).
* CMYK JPEGs: GD decodes them but colours can shift on some builds; no CMYK sample could be generated here, so it is untested.
* Animated GIF / WebP logos keep their first frame only.
* The PDF check reads raw bytes of the whole file (also past 6 MB, also with `#xx` escapes decoded): scripts hidden inside compressed object streams are still not detected (the server never executes the PDF; the visitor's viewer sandboxes it; `nosniff` + `application/pdf` keep it out of the site's DOM). With `qpdf` / `mutool` on the host the file could be decompressed first; not assumed.
* iOS Safari shows only the first page of an embedded PDF: that is why the buttons come first on small screens.
* Range requests are not supported by the PDF stream (25 MB at most); Chrome and Firefox viewers work without them.
* Download counter: counts a GET of `/download` that is not HEAD, not a resumed `Range: bytes=N-` (N > 0) and not a self-declared robot. It is approximate: a script that fakes a browser User-Agent can still raise it, so the routes are throttled (60 / 5 min per address and issue) and the number is only shown publicly when `pdf.show_downloads` is on (off by default).
* The footer partner strip shows the first `footer.partners_limit` partners (12) in website order and a "See all partners" link; About shows `about.partners.limit` (24); /partners shows all. The admin toast after Quick-add says where a new partner appears.
* A partner whose logo file is missing on disk (FTP deletion, database restored without uploads) shows its name on the website, "Logo file missing: upload it again" on its admin card, and is counted in `meta.missing_logos`, not in the "not optimised yet" banner.
* Admin newsletter list: the compact layout (Format / Date / Last change folded into the Issue cell) applies from 768 px up to 1399 px, because the 264 px sidebar leaves too little room for eight columns at 1280-1399 px.
* `pdf_size` is the stored size; a PDF swapped by hand on the server is not noticed.
