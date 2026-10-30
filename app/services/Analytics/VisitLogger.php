<?php
declare(strict_types=1);

namespace App\Services\Analytics;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;

/**
 * Counts the public pages people open, for Admin > Visitors. Privacy first: no IP address and no cookie is stored. Each visit keeps the page path, the country (two letters),
 * the device type, the site the visitor came from and a one-day anonymous code (a keyed hash of the IP address and browser that changes every day, so it can count
 * visitors but never identify or follow anyone). Never logged: admin and API pages, files, search-engine and other robots, signed-in administrators, visitors who send
 * "Do Not Track", and anything that is not a normal page. The country comes from the hosting network's header when there is one (Cloudflare and others), else from a
 * free IP lookup service (ipwho.is) done AFTER the page was sent and remembered per visitor (keyed hash) so the service is asked once. The tables are created on first use.
 */
final class VisitLogger
{
    private const BOT = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|embedly|monitor|uptime|curl\/|wget|python|java\/|go-http|headless|lighthouse|pingdom|gtmetrix|semrush|ahrefs|mj12|dotbot|petalbot|yandex|baidu|duckduck|whatsapp|telegram|discord|skype|slack|preview|scanner|httpclient|okhttp|node-fetch|axios/i';
    private const SKIP_PATH = '#^/(admin|api|assets|uploads|storage|pay|webhook|ipn|bono|sw\.js|manifest|robots\.txt|sitemap|favicon|\.well-known|academy/(certificate|verify)/[^/]+/pdf)#i';
    private const MARKER = 'visits_tables_v1';

    /** Should this finished request be counted? */
    public static function shouldLog(Request $request, Response $response): bool
    {
        if ($request->method() !== 'GET' || (int) $response->status() !== 200 || $request->isAjax()) {
            return false;
        }
        $type = '';
        foreach ($response->headers() as $k => $v) {
            if (strtolower((string) $k) === 'content-type') {
                $type = strtolower((string) (is_array($v) ? ($v[0] ?? '') : $v));
            }
        }
        if (!str_starts_with($type, 'text/html')) {
            return false;
        }
        $path = '/' . ltrim($request->path(), '/');
        if (preg_match(self::SKIP_PATH, $path) === 1 || strlen($path) > 255) {
            return false;
        }
        $ua = $request->userAgent();
        if ($ua === '' || preg_match(self::BOT, $ua) === 1) {
            return false;
        }
        if ((string) ($_SERVER['HTTP_DNT'] ?? '') === '1' || (string) ($_SERVER['HTTP_SEC_GPC'] ?? '') === '1') {
            return false;
        }
        try {
            if (Auth::check()) {
                return false;                  // the staff looking at their own website are not visitors
            }
        } catch (\Throwable) {
            // no session: an ordinary visitor
        }
        return true;
    }

    /** Called after the page was sent (App::afterResponse). Never throws. */
    public static function record(Request $request): void
    {
        try {
            self::ensureTables();
            $ip = $request->ip();
            $ua = $request->userAgent();
            $ref = '';
            $refHost = strtolower((string) parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
            $ownHost = (string) preg_replace('/^www\./', '', strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0]));
            $refBare = (string) preg_replace('/^www\./', '', $refHost);
            if ($refHost !== '' && $refBare !== $ownHost && !str_ends_with($refBare, '.' . $ownHost)) {
                $ref = mb_substr(preg_replace('/^www\./', '', $refHost) ?? $refHost, 0, 120);
            }
            Database::insert('site_visits', [
                'path' => mb_substr('/' . trim($request->path(), '/'), 0, 255),
                'country' => self::country($request, $ip),
                'visitor_code' => substr(hash_hmac('sha256', $ip . '|' . $ua . '|' . date('Y-m-d'), Config::appKey()), 0, 20),
                'device' => self::device($ua),
                'referrer_host' => $ref !== '' ? $ref : null,
            ]);
            self::purgeOld();
        } catch (\Throwable $e) {
            Log::warning('Visit not recorded: ' . $e->getMessage());
        }
    }

    public static function device(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $ua) === 1 || (stripos($ua, 'Android') !== false && stripos($ua, 'Mobile') === false)) {
            return 'tablet';
        }
        return preg_match('/Mobi|iPhone|iPod|Android|Opera Mini|IEMobile/i', $ua) === 1 ? 'mobile' : 'desktop';
    }

    /** Two-letter country code or null (unknown, or a private / local address). */
    public static function country(Request $request, string $ip): ?string
    {
        foreach (['HTTP_CF_IPCOUNTRY', 'HTTP_X_COUNTRY_CODE', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY', 'HTTP_X_VERCEL_IP_COUNTRY', 'GEOIP_COUNTRY_CODE'] as $h) {
            $c = Countries::clean((string) ($_SERVER[$h] ?? ''));
            if ($c !== null) {
                return $c;
            }
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }
        $key = substr(hash_hmac('sha256', $ip, Config::appKey()), 0, 40);
        $row = Database::one('SELECT country, checked_at FROM geo_cache WHERE ip_hash = ?', [$key]);
        if ($row !== null && ($row['country'] !== null || strtotime((string) $row['checked_at']) > time() - 86400)) {
            return $row['country'] !== null ? (string) $row['country'] : null;
        }
        $code = self::lookup($ip);
        Database::execute('REPLACE INTO geo_cache (ip_hash, country, checked_at) VALUES (?, ?, NOW())', [$key, $code]);
        return $code;
    }

    /** Ask the free ipwho.is service (HTTPS, no key) once; the IP address is sent to it, nothing else. */
    private static function lookup(string $ip): ?string
    {
        if (Settings::get('visits_country_lookup', '1') !== '1' || !function_exists('curl_init')) {
            return null;
        }
        $ch = curl_init('https://ipwho.is/' . rawurlencode($ip) . '?fields=success,country_code');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_USERAGENT => 'LAF-site-visits']);
        $raw = curl_exec($ch);
        curl_close($ch);
        $j = is_string($raw) ? json_decode($raw, true) : null;
        return is_array($j) && !empty($j['success']) ? Countries::clean((string) ($j['country_code'] ?? '')) : null;
    }

    /** Create the two tables the first time (the same SQL is in database/schema.sql and database/updates/2026-10-site-visits.sql). */
    public static function ensureTables(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        if (Database::tableExists('site_visits') && Database::tableExists('geo_cache')) {
            return;
        }
        Database::execute("CREATE TABLE IF NOT EXISTS `site_visits` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `visited_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `path` VARCHAR(255) NOT NULL,
  `country` CHAR(2) NULL,
  `visitor_code` CHAR(20) NOT NULL,
  `device` VARCHAR(10) NOT NULL DEFAULT 'desktop',
  `referrer_host` VARCHAR(120) NULL,
  PRIMARY KEY (`id`),
  KEY `ix_site_visits_time` (`visited_at`),
  KEY `ix_site_visits_country` (`country`, `visited_at`),
  KEY `ix_site_visits_path` (`path`(120), `visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        Database::execute("CREATE TABLE IF NOT EXISTS `geo_cache` (
  `ip_hash` CHAR(40) NOT NULL,
  `country` CHAR(2) NULL,
  `checked_at` DATETIME NOT NULL,
  PRIMARY KEY (`ip_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /** Keep 400 days of visits; checked once a day. */
    private static function purgeOld(): void
    {
        $today = date('Y-m-d');
        if ((string) Settings::get('visits_purged_on', '') === $today) {
            return;
        }
        Settings::set('visits_purged_on', $today);
        Database::execute('DELETE FROM site_visits WHERE visited_at < (NOW() - INTERVAL 400 DAY)');
        Database::execute('DELETE FROM geo_cache WHERE checked_at < (NOW() - INTERVAL 90 DAY)');
    }
}
