<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Services\Opportunities\FeedClient;

/**
 * The PARTNER COURSES catalogue of the LAF Academy: a curated list of free courses that run on other learning platforms. The Academy shows a short summary written
 * in its own words, the level, the cost note and a button that opens the course on the provider site; it never copies a provider course. The starter list is the data
 * file app/config/academy/partners.php (installed idempotently by address); administrators add, edit, hide and delete entries and can check the links
 * (a link that answers 404 or 410 is hidden from visitors until it is fixed).
 */
final class PartnerCourses
{
    /** value => [label, short label] */
    public const COSTS = [
        'free'         => ['Free', 'Free'],
        'free_audit'   => ['Free to learn (a certificate may cost)', 'Free to learn'],
        'free_account' => ['Free with a free account', 'Free account'],
    ];

    public static function file(): string
    {
        return APP_PATH . '/config/academy/partners.php';
    }

    /** @return list<array<string,mixed>> the starter rows from the data file */
    public static function starter(): array
    {
        try {
            $rows = include self::file();
        } catch (\Throwable) {
            return [];
        }
        return is_array($rows) ? array_values(array_filter($rows, 'is_array')) : [];
    }

    public static function hash(string $url): string
    {
        return sha1(strtolower(rtrim(trim($url), '/')));
    }

    /**
     * Visible entries for visitors.
     * @param array{q?:string,category?:string,level?:string,provider?:string,cost?:string} $f
     * @return list<array<string,mixed>>
     */
    public static function visible(array $f = [], int $limit = 400): array
    {
        $where = ['p.is_active = 1', '(p.link_status IS NULL OR p.link_status NOT IN (404, 410))'];
        $params = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(p.title LIKE ? OR p.summary LIKE ? OR p.provider LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        if (isset(CourseService::CATEGORIES[(string) ($f['category'] ?? '')])) {
            $where[] = 'p.category = ?';
            $params[] = $f['category'];
        }
        if (isset(CourseService::LEVELS[(string) ($f['level'] ?? '')])) {
            $where[] = 'p.level = ?';
            $params[] = $f['level'];
        }
        $prov = trim((string) ($f['provider'] ?? ''));
        if ($prov !== '') {
            $where[] = 'p.provider = ?';
            $params[] = $prov;
        }
        if (isset(self::COSTS[(string) ($f['cost'] ?? '')])) {
            $where[] = 'p.cost = ?';
            $params[] = $f['cost'];
        }
        return Database::all(
            'SELECT p.* FROM academy_partner_courses p WHERE ' . implode(' AND ', $where)
            . " ORDER BY p.is_featured DESC, FIELD(p.level, 'beginner', 'intermediate', 'advanced'), p.sort_order ASC, p.title ASC LIMIT " . (int) $limit,
            $params
        );
    }

    /** @return list<string> providers that have a visible course */
    public static function providers(): array
    {
        return array_map(static fn(array $r): string => (string) $r['provider'], Database::all(
            'SELECT provider, COUNT(*) n FROM academy_partner_courses WHERE is_active = 1 AND (link_status IS NULL OR link_status NOT IN (404, 410)) GROUP BY provider ORDER BY n DESC, provider ASC'
        ));
    }

    /** @return array<string,int> category => visible partner courses */
    public static function categoryCounts(): array
    {
        $out = [];
        foreach (Database::all('SELECT category, COUNT(*) n FROM academy_partner_courses WHERE is_active = 1 AND (link_status IS NULL OR link_status NOT IN (404, 410)) GROUP BY category') as $r) {
            $out[(string) $r['category']] = (int) $r['n'];
        }
        return $out;
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_partner_courses WHERE id = ?', [$id]);
    }

    public static function count(): int
    {
        try {
            return (int) Database::value('SELECT COUNT(*) FROM academy_partner_courses', [], 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Insert the starter rows that are not there yet (by address). @return int rows added */
    public static function installStarter(): int
    {
        $n = 0;
        foreach (self::starter() as $r) {
            $url = trim((string) ($r['url'] ?? ''));
            if (preg_match('~^https://[^\s"<>]{4,480}$~', $url) !== 1 || trim((string) ($r['title'] ?? '')) === '') {
                continue;
            }
            $cat = isset(CourseService::CATEGORIES[(string) ($r['category'] ?? '')]) ? (string) $r['category'] : 'basics';
            $lvl = isset(CourseService::LEVELS[(string) ($r['level'] ?? '')]) ? (string) $r['level'] : 'beginner';
            $cost = isset(self::COSTS[(string) ($r['cost'] ?? '')]) ? (string) $r['cost'] : 'free';
            $added = Database::execute(
                'INSERT IGNORE INTO academy_partner_courses (title, provider, url, url_hash, category, level, duration, summary, language, cost, certificate, is_featured, sort_order, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [mb_substr((string) $r['title'], 0, 190), mb_substr((string) ($r['provider'] ?? ''), 0, 80), $url, self::hash($url), $cat, $lvl, mb_substr((string) ($r['duration'] ?? ''), 0, 60) ?: null,
                    mb_substr((string) ($r['summary'] ?? ''), 0, 320) ?: null, mb_substr((string) ($r['language'] ?? 'English'), 0, 40), $cost, mb_substr((string) ($r['certificate'] ?? ''), 0, 80) ?: null,
                    !empty($r['featured']) ? 1 : 0, (int) ($r['sort'] ?? 100), 'starter']
            );
            $n += $added > 0 ? 1 : 0;
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------------------- link check

    /**
     * The HTTP status a web address answers (a HEAD request, falling back to GET; redirects followed up to 5 times and each hop checked as a public address).
     * 0 = could not be reached. 401, 403, 405, 429 and 999 mean "the site is there but refuses robots" and count as alive.
     */
    public static function probe(string $url, ?\Closure $transport = null): int
    {
        $current = $url;
        for ($hop = 0; $hop < 6; $hop++) {
            $g = FeedClient::guard($current);
            if (!$g['ok']) {
                return 0;
            }
            $r = $transport !== null ? $transport(['url' => $g['url'], 'host' => $g['host'], 'port' => $g['port'], 'ip' => $g['ips'][0]]) : self::request($g);
            $status = (int) ($r['status'] ?? 0);
            if ($status >= 300 && $status < 400 && !empty($r['location'])) {
                $loc = (string) $r['location'];
                if (preg_match('~^https?://~i', $loc) !== 1) {
                    $p = parse_url($g['url']);
                    $loc = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (str_starts_with($loc, '/') ? $loc : '/' . $loc);
                }
                $current = $loc;
                continue;
            }
            return $status;
        }
        return 0;
    }

    /** @param array{host?:string,port?:int,ips?:list<string>,url?:string} $g @return array{status:int,location?:string} */
    private static function request(array $g): array
    {
        if (!function_exists('curl_init')) {
            return ['status' => 0];
        }
        $ip = (string) $g['ips'][0];
        $resolve = $g['host'] . ':' . $g['port'] . ':' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip);
        $location = '';
        foreach ([true, false] as $head) {
            $ch = curl_init((string) $g['url']);
            curl_setopt_array($ch, [
                CURLOPT_RESOLVE => [$resolve], CURLOPT_FOLLOWLOCATION => false, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_TIMEOUT => 12, CURLOPT_NOBODY => $head, CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS, CURLOPT_ENCODING => '',
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; LuminaAfricaFoundationBot/1.0; link check; +' . abs_url('') . ')',
                CURLOPT_HEADERFUNCTION => static function ($c, string $line) use (&$location): int {
                    if (stripos($line, 'location:') === 0) {
                        $location = trim(substr($line, 9));
                    }
                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION => static fn($c, string $chunk): int => 0,       // never keep the body: only the status matters
            ]);
            $location = '';
            curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            if ($status > 0 && !($head && in_array($status, [400, 403, 405, 501], true))) {
                return ['status' => $status, 'location' => $location];
            }
        }
        return ['status' => $status ?? 0, 'location' => $location];
    }

    /** Check one entry and store the answer. @return int the status */
    public static function check(array $row): int
    {
        $status = self::probe((string) $row['url']);
        Database::update('academy_partner_courses', ['link_status' => $status, 'link_checked_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $row['id']]);
        return $status;
    }

    /** Check the entries not checked for the longest time. @return array{checked:int,broken:int} */
    public static function checkSome(int $limit = 20, int $budgetSeconds = 40): array
    {
        $started = time();
        $rows = Database::all('SELECT * FROM academy_partner_courses WHERE is_active = 1 ORDER BY link_checked_at IS NULL DESC, link_checked_at ASC, id ASC LIMIT ' . max(1, min(60, $limit)));
        $checked = 0;
        $broken = 0;
        foreach ($rows as $r) {
            if (time() - $started >= $budgetSeconds) {
                break;
            }
            $s = self::check($r);
            $checked++;
            $broken += in_array($s, [0, 404, 410], true) || $s >= 500 ? 1 : 0;
        }
        return ['checked' => $checked, 'broken' => $broken];
    }

    public static function statusLabel(?int $s): string
    {
        if ($s === null) {
            return 'Not checked';
        }
        if ($s === 0) {
            return 'Could not be reached';
        }
        if (in_array($s, [404, 410], true)) {
            return 'Page not found (' . $s . ')';
        }
        if ($s >= 500) {
            return 'Server problem (' . $s . ')';
        }
        return 'Works (' . $s . ')';
    }
}
