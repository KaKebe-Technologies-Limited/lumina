# Opportunities

A section under **Involvement** (`/opportunities`) listing scholarships, fellowships, internships, grants, training, competitions, conferences, volunteering and jobs for young people.
Two kinds of items share one list:

| Kind | How it gets there | Shown as |
|---|---|---|
| **Added by us** (`source = manual`) | Admin > Content > Opportunities > *Add an opportunity* (text, picture, optional PDF) | Full details written by the team |
| **From other websites** (`source = feed`) | The website reads the RSS/Atom feeds listed under *Sources* | A short excerpt, the closing date when one is written in the text, the source credited, and a button to the original |

## Admin

* **Content > Opportunities** - list with tabs (On the website, Waiting for approval, Closed, Hidden, Added by us, From other websites), search, type filter, show/hide switch, approve, delete.
* **Add / edit** - title, type, offered by, where, short summary, full details (rich text), who can apply, what you get, how to apply, application link, closing date, picture, **PDF attachment** (call text or flyer, stored privately in `storage/private/opportunities`, served at `/opportunities/{slug}/file` only while the item is shown), feature at the top.
* **Sources** (`/admin/opportunity-sources`) - add, edit, switch on/off, delete, *Fetch now* for one source or all. The starting list: Opportunities For Africans, Opportunity Desk, Opportunities For Youth, Funds for NGOs (only inserted when the list is empty, so a source you delete never comes back on a re-import).
* **Settings > Opportunities** - read other websites on/off, show new ones straight away or approve first, how often to look (3 h - 2 days), items per source, how long items without a closing date are kept.
* Abilities: everything uses `content.manage` (add, edit, approve, sources) and `content.delete` (delete). Role editors who can manage content can manage opportunities.
* **Pages > Opportunities** - all wording around the list and on each opportunity.

## How the feeds are read

* `App\Services\Opportunities\FeedClient` downloads a feed with the same care as a webhook: http/https only, ports 80/443, no user name or password, a real public host (no `localhost`, no numeric IP spellings, every resolved address must be public), the request is pinned to the checked address, at most 3 redirects and each is checked again, 15 s, 3 MB cap.
* `FeedParser` reads RSS 2.0 and Atom (entity declarations and HTML pages are refused, no external resources are loaded).
* `OpportunityImporter` turns items into rows: type from keywords in the title/categories (scholarship, fellowship, internship, competition, grant, volunteer, job, training, conference; the source's default type otherwise), closing date from text such as "Application Deadline: 22 November 2026", a ~300-character excerpt without the "The post ... appeared first on" footer. It skips items already known (same link), items already closed, advice articles ("How to...", "Sample proposal ...") and old items without a closing date. Items already imported are never touched again, so nothing an editor changed is overwritten.
* When it runs:
  1. **By itself, after a visitor's page was sent** (`App::afterResponse`), when a source is due. A source is marked as read *before* the download, so a slow or broken source is not retried on every page view.
  2. **Fetch now** buttons in the admin.
  3. **Cron** `bin/opportunities-fetch.php` (optional, keeps a quiet website fresh). Hostinger: hPanel > Advanced > Cron Jobs, e.g. every 6 hours:
     `0 0,6,12,18 * * * /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/opportunities-fetch.php > /dev/null 2>&1`
* Old items from other websites are removed after the keep time (closed for 14 days, or no closing date and older than the keep time). Items the team added are never removed.

## Why only an excerpt

Other websites write these announcements. The page shows a short excerpt and always links back to the original, with the source named, instead of copying whole articles. Only add sources that allow their headlines to be shared.

## Database (additive, idempotent; in `database/schema.sql` and `database/lumina-live-import.sql`)

`opportunity_feeds` (name, url, url_hash, default_category, is_active, last_fetched_at, last_status, last_message, last_new, total_imported, fail_count) and `opportunities` (see the column comments). `needs_review = 1` means waiting for approval; visible = `is_active = 1 AND needs_review = 0`; open = no closing date or a date today or later.

## Tests

`php bin/selftest-opportunities.php` (also part of `bin/selftest.php`): parser, helpers, SSRF guard, importer, public pages, sitemap, registry. No network is used; it removes its own data.
