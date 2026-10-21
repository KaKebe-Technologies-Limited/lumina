<?php
declare(strict_types=1);

namespace App\Services\Web;

use App\Core\Database;
use App\Core\Log;
use App\Core\SitePages;
use App\Services\Calendar\CalendarService;
use App\Services\Forms\FormService;
use App\Services\Newsletter\NewsletterService;
use App\Services\Shop\ProductService;

/**
 * Builds /sitemap.xml from the database so it is always current:
 *   - every PUBLISHED public page (registry pages + owner-created custom pages) whose menu requirement is met
 *     (an empty "Team"/"Partners"/... page is left out until it has content), never the virtual site-wide pages;
 *   - published detail pages: programs, projects, blog/news/press posts, activities, events, consented stories, open job vacancies.
 * Every query is wrapped so a missing table (e.g. a fresh install) only drops that group instead of breaking the file.
 */
final class SitemapBuilder
{
    public static function xml(): string
    {
        $entries = [];
        $add = static function (string $path, ?string $lastmod, string $priority) use (&$entries): void {
            $loc = abs_url(ltrim($path, '/'));
            if (!isset($entries[$loc])) {
                $entries[$loc] = ['loc' => $loc, 'lastmod' => self::date($lastmod), 'priority' => $priority];
            }
        };

        // ---- pages ---------------------------------------------------------------------------------------------
        $modified = self::pageModified();
        foreach (SitePages::all() as $slug => $p) {
            if ($p['virtual'] || !$p['is_published'] || $p['route'] === '' || !SitePages::requirementMet($p['requires'])) {
                continue;
            }
            $add($p['route'], $modified[$slug] ?? null, $slug === 'home' ? '1.0' : '0.8');
        }

        // ---- detail pages ----------------------------------------------------------------------------------------
        $groups = [
            ['programs',   'SELECT slug, updated_at FROM programs WHERE is_active = 1 ORDER BY sort_order, id',                                            '/programs/'],
            // projects that mirror their program (same slug) are duplicates of the program page and stay out of the sitemap
            ['projects',   'SELECT p.slug, p.updated_at FROM projects p LEFT JOIN programs g ON g.id = p.program_id AND g.is_active = 1 WHERE p.is_active = 1 AND (g.id IS NULL OR g.slug <> p.slug) ORDER BY p.sort_order, p.id', '/projects/'],
            ['blog',       "SELECT slug, updated_at FROM posts WHERE post_type = 'blog' AND status = 'published' AND published_at <= NOW() ORDER BY published_at DESC", '/blog/'],
            ['news',       "SELECT slug, updated_at FROM posts WHERE post_type = 'news' AND status = 'published' AND published_at <= NOW() ORDER BY published_at DESC", '/news/'],
            ['press',      "SELECT slug, updated_at FROM posts WHERE post_type = 'press' AND status = 'published' AND published_at <= NOW() AND (external_url IS NULL OR external_url = '') ORDER BY published_at DESC", '/press/'],
            ['activities', 'SELECT slug, updated_at FROM activities WHERE is_active = 1 ORDER BY activity_date DESC, id DESC',                              '/activities/'],
            ['events',     'SELECT slug, updated_at FROM events WHERE is_active = 1 ORDER BY start_at DESC',                                               '/events/'],
            ['stories',    'SELECT slug, updated_at FROM stories WHERE is_active = 1 AND consent_confirmed = 1 AND (published_at IS NULL OR published_at <= NOW()) ORDER BY published_at DESC', '/stories/'],
            // shop: published products (never SAMPLE products: their price is a placeholder) and the categories that hold one; never the cart, checkout or an order
            ['shop',       'SELECT p.slug, p.updated_at FROM shop_products p WHERE ' . ProductService::visibleSql('p') . ' AND p.is_sample = 0 ORDER BY p.sort_order, p.id', '/shop/'],
            ['shop',       "SELECT c.slug, c.created_at AS updated_at FROM shop_categories c WHERE c.is_active = 1 AND EXISTS (SELECT 1 FROM shop_products p WHERE p.category_id = c.id AND p.status = 'published' AND p.is_sample = 0) ORDER BY c.sort_order, c.id", '/shop/category/'],
            ['careers',    'SELECT slug, updated_at FROM jobs WHERE is_active = 1 AND (closing_date IS NULL OR closing_date >= CURDATE()) ORDER BY sort_order, id DESC', '/careers/'],
            ['opportunities', 'SELECT slug, updated_at FROM opportunities WHERE is_active = 1 AND needs_review = 0 AND (deadline IS NULL OR deadline >= CURDATE()) ORDER BY posted_at DESC LIMIT 500', '/opportunities/'],
            ['academy', "SELECT slug, updated_at FROM academy_courses WHERE status = 'published' ORDER BY sort_order, id", '/academy/courses/'],
        ];
        $latest = [];                       // group => newest updated_at among its detail pages (the list page changes with them)
        foreach ($groups as [$parentSlug, $sql, $prefix]) {
            $parent = SitePages::get($parentSlug);
            if ($parent !== null && (!$parent['is_published'])) {
                continue;                       // the section itself is switched off
            }
            try {
                foreach (Database::all($sql) as $row) {
                    $add($prefix . $row['slug'], (string) ($row['updated_at'] ?? ''), '0.6');
                    if ((string) ($row['updated_at'] ?? '') > ($latest[$parentSlug] ?? '')) {
                        $latest[$parentSlug] = (string) $row['updated_at'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Sitemap: group "' . $parentSlug . '" skipped - ' . $e->getMessage());
            }
        }
        // the /press listing (a view of the news section) exists as soon as there is a press item
        if (isset($latest['press']) && ($news = SitePages::get('news')) !== null && $news['is_published']) {
            $add('/press', $latest['press'], '0.6');
        }
        // list pages: lastmod = their own edit or the newest item they list, whichever is later
        foreach ($latest as $group => $stamp) {
            $parent = SitePages::get($group);
            $loc = $parent !== null && (string) $parent['route'] !== '' ? abs_url(ltrim((string) $parent['route'], '/')) : '';
            if ($loc !== '' && isset($entries[$loc]) && self::date($stamp) > $entries[$loc]['lastmod']) {
                $entries[$loc]['lastmod'] = self::date($stamp);
            }
        }

        // ---- newsletter (WEB agent; docs/NEWSLETTER.md section 6) ------------------------------------------------------
        // /newsletter and /newsletter/archive are listed only while at least one issue is public (without one both pages answer noindex),
        // plus one entry per PUBLIC issue (NewsletterService::visibleSql: published, or scheduled and due). Drafts and archived issues never appear.
        try {
            $nlPage = SitePages::get('newsletter');
            unset($entries[abs_url('newsletter')]);           // the generic page loop above added it unconditionally
            if ($nlPage !== null && $nlPage['is_published'] && SitePages::requirementMet($nlPage['requires'])) {
                NewsletterService::resolveScheduled();
                [$nlSql, $nlParams] = NewsletterService::visibleSql('n');
                $issues = Database::all("SELECT n.slug, n.updated_at FROM newsletters n WHERE {$nlSql} ORDER BY " . NewsletterService::PUBLIC_ORDER, $nlParams);
                if ($issues !== []) {
                    $newest = '';
                    foreach ($issues as $row) {
                        $newest = max($newest, (string) ($row['updated_at'] ?? ''));
                    }
                    $add('/newsletter', $newest, '0.8');
                    $add('/newsletter/archive', $newest, '0.6');
                    foreach ($issues as $row) {
                        $add('/newsletter/' . $row['slug'], (string) ($row['updated_at'] ?? ''), '0.6');
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Sitemap: newsletter skipped - ' . $e->getMessage());
        }

        // ---- calendar (WEB agent; docs/CALENDAR.md section 10) --------------------------------------------------------------
        // /calendar is listed by the page loop above; one entry per PUBLIC, active entry of a visible type (Uganda public holidays, international days,
        // programme sessions ...), never a private one, a hidden one or an entry of a type that is hidden from visitors. Events keep their /events pages.
        try {
            $calPage = SitePages::get('calendar');
            if ($calPage !== null && $calPage['is_published'] && SitePages::requirementMet($calPage['requires']) && CalendarService::available()) {
                $newest = '';
                // an entry that a MEETING owns (docs/MEETINGS.md) is a one-off listing that may carry a join link: it stays out of the sitemap. Decided by ownership,
                // not by type: an ordinary entry the owner filed under "Meeting / outreach" is still listed.
                $notMeeting = Database::tableExists('meetings') ? ' AND NOT EXISTS (SELECT 1 FROM meetings mt WHERE mt.calendar_entry_id = e.id)' : '';
                $rows = Database::all(
                    'SELECT e.slug, e.updated_at FROM calendar_entries e JOIN calendar_types t ON t.id = e.type_id'
                    . ' WHERE e.is_active = 1 AND e.is_public = 1 AND t.is_active = 1 AND t.show_on_public = 1' . $notMeeting . ' ORDER BY e.id ASC LIMIT 500'
                );
                foreach ($rows as $row) {
                    $add('/calendar/' . $row['slug'], (string) ($row['updated_at'] ?? ''), '0.5');
                    $newest = max($newest, (string) ($row['updated_at'] ?? ''));
                }
                $loc = abs_url('calendar');
                if ($newest !== '' && isset($entries[$loc]) && self::date($newest) > $entries[$loc]['lastmod']) {
                    $entries[$loc]['lastmod'] = self::date($newest);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Sitemap: calendar skipped - ' . $e->getMessage());
        }

        // ---- forms (docs/FORMS.md section 14) -------------------------------------------------------------------------------
        // The /forms list is a public page only when Site settings > Forms switches it on; a form is listed only when it is published, OPEN, flagged "show in the
        // public list" and not set to noindex. Submissions, files and the thank-you pages never appear.
        try {
            unset($entries[abs_url('forms')]);                          // the generic page loop added it unconditionally
            $formsPage = SitePages::get('forms');
            if ($formsPage !== null && $formsPage['is_published'] && FormService::available() && FormService::directoryEnabled()) {
                $newest = '';
                foreach (Database::all("SELECT * FROM forms WHERE status = 'published' AND listed = 1 ORDER BY title ASC LIMIT 200") as $row) {
                    $form = FormService::hydrate($row);
                    if (!FormService::availability($form)['can_submit'] || !empty($form['cfg']['seo']['noindex'])) {
                        continue;
                    }
                    $add('/forms/' . $form['slug'], (string) ($form['updated_at'] ?? ''), '0.5');
                    $newest = max($newest, (string) ($form['updated_at'] ?? ''));
                }
                $add('/forms', $newest !== '' ? $newest : null, '0.5');
            }
        } catch (\Throwable $e) {
            Log::warning('Sitemap: forms skipped - ' . $e->getMessage());
        }

        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($entries as $e) {
            $out .= "  <url>\n    <loc>" . self::x($e['loc']) . "</loc>\n";
            if ($e['lastmod'] !== '') {
                $out .= '    <lastmod>' . $e['lastmod'] . "</lastmod>\n";
            }
            $out .= '    <priority>' . $e['priority'] . "</priority>\n  </url>\n";
        }
        return $out . "</urlset>\n";
    }

    /** @return array<string,string> page slug => most recent edit of the page row or any of its content blocks */
    private static function pageModified(): array
    {
        $out = [];
        try {
            foreach (Database::all('SELECT slug, updated_at FROM pages') as $r) {
                $out[(string) $r['slug']] = (string) $r['updated_at'];
            }
            foreach (Database::all('SELECT page_slug, MAX(updated_at) AS m FROM content_blocks GROUP BY page_slug') as $r) {
                $slug = (string) $r['page_slug'];
                if (!isset($out[$slug]) || (string) $r['m'] > $out[$slug]) {
                    $out[$slug] = (string) $r['m'];
                }
            }
        } catch (\Throwable) {
            // no database yet
        }
        return $out;
    }

    private static function date(?string $value): string
    {
        $dt = to_datetime($value);
        return $dt ? $dt->format('Y-m-d') : '';
    }

    private static function x(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
