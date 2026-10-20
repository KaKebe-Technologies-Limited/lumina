<?php
declare(strict_types=1);

namespace App\Services\Opportunities;

use App\Core\Database;
use App\Core\Log;
use App\Core\Settings;
use App\Core\Slug;
use App\Models\Opportunity;

/**
 * Reads the opportunity feeds (Admin > Opportunities > Sources) and turns their items into `opportunities` rows.
 * Only a SHORT excerpt and a link back to the source are kept (never the whole article); the source name is always credited on the page.
 * Items already imported are never touched again (so nothing an administrator edited is overwritten). Called from three places:
 *   - the website itself, after a visitor's page was sent, when a feed is due (refreshDue(false)),
 *   - the "Fetch now" buttons in the admin (refreshDue(true) / refreshFeed()),
 *   - the cron job bin/opportunities-fetch.php.
 */
final class OpportunityImporter
{
    /** keyword => category; the first entry that matches the title/categories wins (order matters) */
    private const KEYWORDS = [
        'scholarship' => ['scholarship', 'bursary', 'bursaries', 'tuition'],
        'fellowship'  => ['fellowship', 'fellows'],
        'internship'  => ['internship', 'intern ', 'traineeship', 'apprentice'],
        'competition' => ['competition', 'contest', 'prize', 'challenge', 'awards', 'award ', 'pitch'],
        'grant'       => ['grant', 'funding', 'seed fund', 'call for proposals', 'call for applications', 'fund '],
        'volunteer'   => ['volunteer'],
        'job'         => [' job', 'vacancy', 'vacancies', 'recruit', 'hiring', 'we are hiring', 'consultant', 'careers at'],
        'training'    => ['training', 'course', 'workshop', 'bootcamp', 'webinar', 'masterclass', 'accelerator', 'incubator', 'mentorship'],
        'conference'  => ['conference', 'summit', 'forum', 'symposium', 'congress'],
    ];

    // ---------------------------------------------------------------------------------------------------------------- settings

    public static function enabled(): bool
    {
        return (string) setting('opp_feeds_enabled', '1') === '1';
    }

    public static function intervalHours(): int
    {
        return max(1, min(168, (int) setting('opp_refresh_hours', '12')));
    }

    /** Are there feeds due for a refresh? (cheap: one query) */
    public static function anyDue(): bool
    {
        if (!self::enabled()) {
            return false;
        }
        try {
            return (int) Database::value(
                'SELECT COUNT(*) FROM opportunity_feeds WHERE is_active = 1 AND (last_fetched_at IS NULL OR last_fetched_at < (NOW() - INTERVAL ? HOUR))',
                [self::intervalHours()],
                0
            ) > 0;
        } catch (\Throwable) {
            return false;       // the tables are not there yet (the upgrade has not been imported): the public pages must keep working
        }
    }

    // ---------------------------------------------------------------------------------------------------------------- refresh

    /**
     * Refresh every active feed that is due (all of them when $force). Stops starting new feeds after $budgetSeconds.
     * @return array{feeds:int,new:int,errors:int}
     */
    public static function refreshDue(bool $force = false, int $budgetSeconds = 30, ?\Closure $transport = null): array
    {
        $out = ['feeds' => 0, 'new' => 0, 'errors' => 0];
        if (!$force && !self::enabled()) {
            return $out;
        }
        $started = time();
        $feeds = Database::all(
            'SELECT * FROM opportunity_feeds WHERE is_active = 1'
            . ($force ? '' : ' AND (last_fetched_at IS NULL OR last_fetched_at < (NOW() - INTERVAL ? HOUR))') . ' ORDER BY last_fetched_at IS NULL DESC, last_fetched_at ASC, id ASC',
            $force ? [] : [self::intervalHours()]
        );
        foreach ($feeds as $feed) {
            if (time() - $started >= $budgetSeconds) {
                break;
            }
            $r = self::refreshFeed($feed, $transport);
            $out['feeds']++;
            $out['new'] += $r['new'];
            $out['errors'] += $r['error'] !== null ? 1 : 0;
        }
        self::prune();
        return $out;
    }

    /** @return array{new:int,error:?string} */
    public static function refreshFeed(array $feed, ?\Closure $transport = null, ?\Closure $resolver = null): array
    {
        $id = (int) $feed['id'];
        // mark first: a second visitor (or a crash) must not start the same download again, and a broken feed is not retried on every page view
        Database::execute('UPDATE opportunity_feeds SET last_fetched_at = NOW() WHERE id = ?', [$id]);
        $new = 0;
        $error = null;
        try {
            $got = FeedClient::get((string) $feed['url'], $transport, $resolver);
            if (!$got['ok']) {
                $error = (string) $got['error'];
            } else {
                $parsed = FeedParser::parse($got['body']);
                if (!$parsed['ok']) {
                    $error = (string) $parsed['error'];
                } else {
                    $cap = max(1, min(60, (int) setting('opp_per_feed', '15')));
                    $items = array_slice($parsed['items'], 0, $cap);
                    foreach ($items as $item) {
                        if (self::import($feed, $item)) {
                            $new++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $error = 'Something went wrong while reading this feed.';
            Log::error('Opportunity feed #' . $id . ' failed: ' . $e->getMessage());
        }
        Database::update('opportunity_feeds', [
            'last_status'    => $error === null ? 'ok' : 'error',
            'last_message'   => $error ?? ($new === 0 ? 'Nothing new.' : $new . ' new ' . ($new === 1 ? 'opportunity' : 'opportunities') . '.'),
            'last_new'       => $new,
            'total_imported' => (int) $feed['total_imported'] + $new,
            'fail_count'     => $error === null ? 0 : (int) $feed['fail_count'] + 1,
        ], 'id = ?', [$id]);
        return ['new' => $new, 'error' => $error];
    }

    /** Insert one feed item unless it is known, too old, or already closed. @param array<string,mixed> $item */
    public static function import(array $feed, array $item): bool
    {
        $hash = sha1(self::normalizeLink((string) $item['link']));
        if (self::isNotAnOpportunity((string) $item['title'])) {
            return false;                     // advice articles, sample proposals, templates ...
        }
        if (Database::value('SELECT 1 FROM opportunities WHERE source_hash = ? LIMIT 1', [$hash]) !== null) {
            return false;
        }
        $body = (string) ($item['text'] ?? '') !== '' ? (string) $item['text'] : (string) ($item['content'] ?? '');
        $plain = self::cleanText($body);
        $deadline = self::findDeadline($plain !== '' ? $plain : self::cleanText((string) ($item['content'] ?? '')))
            ?? self::findDeadline(self::cleanText((string) ($item['content'] ?? '')) !== '' ? mb_substr(self::cleanText((string) ($item['content'] ?? '')), 0, 2500) : '');
        $today = date('Y-m-d');
        if ($deadline !== null && $deadline < $today) {
            return false;                     // already closed: not worth listing
        }
        $posted = (int) ($item['date'] ?? 0);
        $keepDays = max(7, min(365, (int) setting('opp_keep_days', '45')));
        if ($posted > 0 && $posted < time() - $keepDays * 86400 && $deadline === null) {
            return false;
        }
        $title = mb_substr((string) $item['title'], 0, 255);
        $review = (string) setting('opp_auto_publish', '1') === '1' ? 0 : 1;
        $data = [
            'title'        => $title,
            'slug'         => Slug::unique('opportunities', $title, null, 'slug', [], 'opportunity'),
            'category'     => self::classify($title, (array) ($item['categories'] ?? []), (string) ($feed['default_category'] ?? 'other')),
            'summary'      => self::summarise($plain, $title),
            'deadline'     => $deadline,
            'apply_url'    => mb_substr((string) $item['link'], 0, 500),
            'source'       => 'feed',
            'feed_id'      => (int) $feed['id'],
            'source_name'  => mb_substr((string) $feed['name'], 0, 190),
            'source_url'   => mb_substr((string) $item['link'], 0, 500),
            'source_hash'  => $hash,
            'posted_at'    => date('Y-m-d H:i:s', $posted > 0 ? min($posted, time()) : time()),
            'is_active'    => 1,
            'needs_review' => $review,
        ];
        try {
            Database::insert('opportunities', $data);
        } catch (\PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                return false;                 // imported a moment ago by another request
            }
            throw $e;
        }
        return true;
    }

    /** Articles that are advice, not an opportunity someone can apply to (some sources mix them in). */
    public static function isNotAnOpportunity(string $title): bool
    {
        return preg_match('/^(?:a\s+)?(?:sample|template|example)s?\b|\bsample\s+(?:grant\s+)?(?:proposal|letter|budget)|^how\s+to\b|^\d+\s+(?:tips|ways|reasons|mistakes)\b|\btips\s+(?:for|to)\b|^(?:the\s+)?(?:ultimate\s+)?guide\s+to\b|\bwebinar\s+recording\b/i', $title) === 1;
    }

    /** Remove old feed items nobody typed in: closed for 14+ days, or without a deadline and older than the keep time. Manual items are never removed. */
    public static function prune(): int
    {
        $keep = max(7, min(365, (int) setting('opp_keep_days', '45')));
        return Database::execute(
            "DELETE FROM opportunities WHERE source = 'feed' AND is_featured = 0 AND (
                (deadline IS NOT NULL AND deadline < (CURDATE() - INTERVAL 14 DAY))
                OR (deadline IS NULL AND posted_at < (NOW() - INTERVAL ? DAY)))",
            [$keep]
        );
    }

    // ---------------------------------------------------------------------------------------------------------------- text helpers (pure)

    /** Plain text of a feed excerpt: tags and entities removed, the "The post ... appeared first on ..." footer dropped, spaces collapsed. */
    public static function cleanText(string $html): string
    {
        $html = (string) preg_replace('~<(script|style)\b.*?</\1>~is', ' ', $html);
        $html = (string) preg_replace('~<\s*(br|/p|/div|/li|/h[1-6])\b[^>]*>~i', ' ', $html);
        $t = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = (string) preg_replace('/The post .{0,400}? appeared first on .{0,200}$/su', '', $t);
        $t = (string) preg_replace('/\[\s*(?:…|\.\.\.|&#8230;)\s*\]|\x{2026}\s*$/u', '…', $t);
        $t = (string) preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x{200b}-\x{200f}\x{feff}]/u', '', $t);
        return trim((string) preg_replace('/\s+/u', ' ', $t));
    }

    /** About 280 characters, cut at a word, without repeating the title. */
    public static function summarise(string $plain, string $title = ''): ?string
    {
        $t = trim($plain);
        if ($t === '') {
            return null;
        }
        // the closing date is shown as a badge, so a leading "Deadline: 13 November 2026" is not repeated in the text
        $t = trim((string) preg_replace('/^(?:application\s+|closing\s+)?deadline\s*[:\-–]\s*(?:\d{1,2}(?:st|nd|rd|th)?\s+[A-Za-z]{3,9}\.?,?\s+\d{4}|[A-Za-z]{3,9}\.?\s+\d{1,2}(?:st|nd|rd|th)?,?\s+\d{4})(?:,?\s*\d{1,2}(?::\d{2})?\s*(?:am|pm)\b)?(?:\s*\((?:[A-Za-z]{2,5})\)|\s+(?:GMT|UTC|EAT|CAT|WAT|EST|EDT|PST|PDT|CET|CEST|BST|IST)\b)?\s*/iu', '', $t));
        if ($title !== '' && stripos($t, $title) === 0) {
            $t = trim(substr($t, strlen($title)), " :-–—\t");
        }
        if (mb_strlen($t) > 300) {
            $t = mb_substr($t, 0, 300);
            $cut = mb_strrpos($t, ' ');
            $t = rtrim($cut !== false && $cut > 180 ? mb_substr($t, 0, $cut) : $t, " ,;:-–—.") . '…';
        }
        return $t === '' ? null : $t;
    }

    /** @param list<string> $cats */
    public static function classify(string $title, array $cats, string $default = 'other'): string
    {
        $hay = ' ' . mb_strtolower($title . ' | ' . implode(' | ', $cats)) . ' ';
        foreach (self::KEYWORDS as $cat => $words) {
            foreach ($words as $w) {
                if (str_contains($hay, $w)) {
                    return $cat;
                }
            }
        }
        return isset(Opportunity::CATEGORIES[$default]) ? $default : 'other';
    }

    /** The closing date written in a sentence such as "Application Deadline: 22 November 2026", or null. Returns Y-m-d. */
    public static function findDeadline(string $text): ?string
    {
        if ($text === '') {
            return null;
        }
        $re = '/(?:application\s+|submission\s+|closing\s+)?(?:deadline|closing\s+date|closes?\s+on|apply\s+(?:by|before)|applications?\s+close|last\s+date)\s*(?:to\s+apply\s*)?(?:is|:|on|by|-|–|—)?\s*[:\-–—]?\s*(?:\w+day,?\s+)?'
            . '((?:\d{1,2}(?:st|nd|rd|th)?\s+(?:of\s+)?[A-Za-z]{3,9}\.?,?\s+\d{4})|(?:[A-Za-z]{3,9}\.?\s+\d{1,2}(?:st|nd|rd|th)?,?\s+\d{4})|(?:\d{4}-\d{2}-\d{2}))/iu';
        if (preg_match($re, $text, $m) !== 1) {
            return null;
        }
        $s = (string) preg_replace('/(\d)(st|nd|rd|th)\b/i', '$1', str_ireplace([' of ', '.'], [' ', ''], $m[1]));
        $ts = strtotime($s);
        if ($ts === false) {
            return null;
        }
        $y = (int) date('Y', $ts);
        return $y >= 2000 && $y <= 2100 ? date('Y-m-d', $ts) : null;
    }

    public static function normalizeLink(string $url): string
    {
        $p = parse_url(trim($url));
        if ($p === false || !isset($p['host'])) {
            return strtolower(trim($url));
        }
        return strtolower((string) $p['host']) . rtrim((string) ($p['path'] ?? '/'), '/') . (isset($p['query']) ? '?' . $p['query'] : '');
    }
}
