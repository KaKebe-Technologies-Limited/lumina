<?php
declare(strict_types=1);

namespace App\Services\Analytics;

use App\Core\Database;

/** Numbers for Admin > Visitors. Filters: period (today | 7 | 30 | 90 | all), country (two letters, or "unknown"), page (part of the address). */
final class VisitReport
{
    public const PERIODS = ['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', 'all' => 'All time'];

    /** @return array{period:string,country:string,page:string} */
    public static function filters(string $period, string $country, string $page): array
    {
        $period = isset(self::PERIODS[$period]) ? $period : '30';
        $country = strtolower(trim($country)) === 'unknown' ? 'unknown' : (string) Countries::clean($country);
        $page = mb_substr(trim($page), 0, 80);
        return ['period' => $period, 'country' => $country, 'page' => $page];
    }

    /**
     * WHERE clause. $withCountry false leaves the country filter out (the country table always shows every country, so a country can be chosen from it).
     * @param array{period:string,country:string,page:string} $f @return array{0:string,1:list<mixed>}
     */
    private static function where(array $f, bool $withCountry = true, bool $withPage = true): array
    {
        $w = ['1 = 1'];
        $p = [];
        if ($f['period'] === 'today') {
            $w[] = 'visited_at >= CURDATE()';
        } elseif ($f['period'] !== 'all') {
            $w[] = 'visited_at >= (NOW() - INTERVAL ' . (int) $f['period'] . ' DAY)';
        }
        if ($withCountry && $f['country'] !== '') {
            if ($f['country'] === 'unknown') {
                $w[] = 'country IS NULL';
            } else {
                $w[] = 'country = ?';
                $p[] = $f['country'];
            }
        }
        if ($withPage && $f['page'] !== '') {
            $w[] = 'path LIKE ?';
            $p[] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $f['page']) . '%';
        }
        return [implode(' AND ', $w), $p];
    }

    /** @param array{period:string,country:string,page:string} $f @return array{views:int,visitors:int,countries:int,pages:int} */
    public static function summary(array $f): array
    {
        [$w, $p] = self::where($f);
        $r = Database::one("SELECT COUNT(*) AS views, COUNT(DISTINCT visitor_code) AS visitors, COUNT(DISTINCT country) AS countries, COUNT(DISTINCT path) AS pages FROM site_visits WHERE $w", $p) ?? [];
        return ['views' => (int) ($r['views'] ?? 0), 'visitors' => (int) ($r['visitors'] ?? 0), 'countries' => (int) ($r['countries'] ?? 0), 'pages' => (int) ($r['pages'] ?? 0)];
    }

    /** Most visited pages. @param array{period:string,country:string,page:string} $f @return list<array{path:string,views:int,visitors:int}> */
    public static function topPages(array $f, int $limit = 20): array
    {
        [$w, $p] = self::where($f);
        $rows = Database::all("SELECT path, COUNT(*) AS views, COUNT(DISTINCT visitor_code) AS visitors FROM site_visits WHERE $w GROUP BY path ORDER BY views DESC, path ASC LIMIT " . max(1, $limit), $p);
        return array_map(static fn(array $r): array => ['path' => (string) $r['path'], 'views' => (int) $r['views'], 'visitors' => (int) $r['visitors']], $rows);
    }

    /** Visits per country (ignores the country filter so any country can be picked). @param array{period:string,country:string,page:string} $f @return list<array{code:?string,name:string,views:int,visitors:int}> */
    public static function byCountry(array $f, int $limit = 40): array
    {
        [$w, $p] = self::where($f, false);
        $rows = Database::all("SELECT country, COUNT(*) AS views, COUNT(DISTINCT visitor_code) AS visitors FROM site_visits WHERE $w GROUP BY country ORDER BY views DESC LIMIT " . max(1, $limit), $p);
        return array_map(static fn(array $r): array => ['code' => $r['country'] !== null ? (string) $r['country'] : null, 'name' => Countries::name($r['country']), 'views' => (int) $r['views'], 'visitors' => (int) $r['visitors']], $rows);
    }

    /** Visits per day for a small chart. @param array{period:string,country:string,page:string} $f @return list<array{day:string,views:int}> */
    public static function daily(array $f): array
    {
        [$w, $p] = self::where($f);
        $days = $f['period'] === 'today' ? 1 : ($f['period'] === 'all' ? 90 : (int) $f['period']);
        $rows = Database::all("SELECT DATE(visited_at) AS d, COUNT(*) AS n FROM site_visits WHERE $w AND visited_at >= (CURDATE() - INTERVAL " . max(0, $days - 1) . " DAY) GROUP BY d", $p);
        $by = [];
        foreach ($rows as $r) {
            $by[(string) $r['d']] = (int) $r['n'];
        }
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' day'));
            $out[] = ['day' => $d, 'views' => $by[$d] ?? 0];
        }
        return $out;
    }

    /** @param array{period:string,country:string,page:string} $f @return list<array<string,mixed>> */
    public static function recent(array $f, int $limit = 30): array
    {
        [$w, $p] = self::where($f);
        $rows = Database::all("SELECT visited_at, path, country, device, referrer_host FROM site_visits WHERE $w ORDER BY id DESC LIMIT " . max(1, $limit), $p);
        foreach ($rows as &$r) {
            $r['country_name'] = Countries::name($r['country']);
        }
        return $rows;
    }

    /** Where visitors came from (other websites). @param array{period:string,country:string,page:string} $f @return list<array{host:string,views:int}> */
    public static function referrers(array $f, int $limit = 8): array
    {
        [$w, $p] = self::where($f);
        $rows = Database::all("SELECT referrer_host AS host, COUNT(*) AS views FROM site_visits WHERE $w AND referrer_host IS NOT NULL GROUP BY referrer_host ORDER BY views DESC LIMIT " . max(1, $limit), $p);
        return array_map(static fn(array $r): array => ['host' => (string) $r['host'], 'views' => (int) $r['views']], $rows);
    }

    /** @param array{period:string,country:string,page:string} $f @return array{desktop:int,mobile:int,tablet:int} */
    public static function devices(array $f): array
    {
        [$w, $p] = self::where($f);
        $out = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
        foreach (Database::all("SELECT device, COUNT(*) AS n FROM site_visits WHERE $w GROUP BY device", $p) as $r) {
            if (isset($out[(string) $r['device']])) {
                $out[(string) $r['device']] = (int) $r['n'];
            }
        }
        return $out;
    }

    /** CSV rows of the visits matching the filters (newest first, at most 5000). @param array{period:string,country:string,page:string} $f @return list<array<string,mixed>> */
    public static function export(array $f): array
    {
        [$w, $p] = self::where($f);
        $rows = Database::all("SELECT visited_at, path, country, device, referrer_host FROM site_visits WHERE $w ORDER BY id DESC LIMIT 5000", $p);
        return array_map(static fn(array $r): array => ['Time' => $r['visited_at'], 'Page' => $r['path'], 'Country' => Countries::name($r['country']), 'Device' => $r['device'], 'From website' => $r['referrer_host'] ?? ''], $rows);
    }
}
