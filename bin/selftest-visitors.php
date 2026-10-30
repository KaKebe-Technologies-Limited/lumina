<?php
declare(strict_types=1);

/**
 * Admin > Visitors: which requests are counted (pages only, no robots / admin / Do-Not-Track), what is stored (no IP address), the country header, and the report filters
 * (period, country, page). Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-visitors.php
 * Removes its rows (paths starting /zz-visits).
 */

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit('Refusing to run outside APP_ENV=local.' . PHP_EOL);
    }
    $fail = 0;
    function check(string $name, bool $ok, string $extra = ''): void
    {
        global $fail;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ($ok || $extra === '' ? '' : "  -> {$extra}") . PHP_EOL;
        if (!$ok) {
            $fail++;
        }
    }
    $standalone = true;
}

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\Analytics\Countries;
use App\Services\Analytics\VisitLogger;
use App\Services\Analytics\VisitReport;

echo "-- visitors report --\n";
VisitLogger::ensureTables();
check('the visit tables exist', Database::tableExists('site_visits') && Database::tableExists('geo_cache'));
$clean = static function (): void {
    Database::execute("DELETE FROM site_visits WHERE path LIKE '/zz-visits%'");
};
$clean();

$saveServer = $_SERVER;
$req = static function (string $path, array $server = []) use ($saveServer): Request {
    $_SERVER = array_merge($saveServer, ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $path, 'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120 Safari/537.36', 'REMOTE_ADDR' => '10.1.2.3'], $server);
    unset($_SERVER['HTTP_DNT'], $_SERVER['HTTP_SEC_GPC'], $_SERVER['HTTP_X_REQUESTED_WITH']);
    $_SERVER = array_merge($_SERVER, $server);
    return Request::capture();
};
$page = Response::html('<html></html>');
$notFound = Response::html('x', 404);
$json = Response::json(['a' => 1]);

check('a normal page view is counted', VisitLogger::shouldLog($req('/zz-visits/a'), $page));
check('a missing page (404) is not counted', !VisitLogger::shouldLog($req('/zz-visits/a'), $notFound));
check('a JSON answer is not counted', !VisitLogger::shouldLog($req('/zz-visits/a'), $json));
check('the admin area is not counted', !VisitLogger::shouldLog($req('/admin/visitors'), $page));
check('files and the service worker are not counted', !VisitLogger::shouldLog($req('/assets/css/main.css'), $page) && !VisitLogger::shouldLog($req('/sw.js'), $page));
check('a robot is not counted', !VisitLogger::shouldLog($req('/zz-visits/a', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']), $page));
check('"Do Not Track" is respected', !VisitLogger::shouldLog($req('/zz-visits/a', ['HTTP_DNT' => '1']), $page));
check('a POST is not counted', !VisitLogger::shouldLog($req('/zz-visits/a', ['REQUEST_METHOD' => 'POST']), $page));
check('device types', VisitLogger::device('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile') === 'mobile' && VisitLogger::device('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X)') === 'tablet' && VisitLogger::device('Mozilla/5.0 (Windows NT 10.0) Chrome/120') === 'desktop');

$r = $req('/zz-visits/home', ['HTTP_CF_IPCOUNTRY' => 'ug', 'HTTP_REFERER' => 'https://www.google.com/search?q=lumina']);
VisitLogger::record($r);
$row = Database::one("SELECT * FROM site_visits WHERE path = '/zz-visits/home' ORDER BY id DESC LIMIT 1");
check('a visit is stored with the page, the country from the network header, the device and the referring website', $row !== null && $row['country'] === 'UG' && $row['device'] === 'desktop' && $row['referrer_host'] === 'google.com');
check('no IP address is stored: the one-day code is only a keyed hash', $row !== null && strlen((string) $row['visitor_code']) === 20 && !str_contains(json_encode($row), '10.1.2.3'));
check('a private address with no country header is stored as an unknown country', (function () use ($req): bool {
    VisitLogger::record($req('/zz-visits/home'));
    return Database::value("SELECT COUNT(*) FROM site_visits WHERE path = '/zz-visits/home' AND country IS NULL", [], 0) >= 1;
})());

// more visits to test the filters: Kenya 3x /zz-visits/shop, Uganda 1x /zz-visits/shop, Kenya 1x /zz-visits/about
foreach ([['KE', '/zz-visits/shop'], ['KE', '/zz-visits/shop'], ['KE', '/zz-visits/shop'], ['UG', '/zz-visits/shop'], ['KE', '/zz-visits/about']] as $i => [$cc, $p]) {
    VisitLogger::record($req($p, ['HTTP_CF_IPCOUNTRY' => $cc, 'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/12' . $i . ' Safari']));
}
$f = VisitReport::filters('30', '', '/zz-visits');
$s = VisitReport::summary($f);
check('summary counts the views and the different pages', $s['views'] === 7 && $s['pages'] === 3, json_encode($s));
$top = VisitReport::topPages($f);
check('the most visited page comes first', ($top[0]['path'] ?? '') === '/zz-visits/shop' && $top[0]['views'] === 4, json_encode($top[0] ?? null));
$fk = VisitReport::filters('30', 'ke', '/zz-visits');
$topK = VisitReport::topPages($fk);
check('filtering by country shows only that country\'s pages', count($topK) === 2 && $topK[0]['path'] === '/zz-visits/shop' && $topK[0]['views'] === 3 && VisitReport::summary($fk)['views'] === 4);
$byC = VisitReport::byCountry($fk);
check('the country table ignores the country filter, so any country can be picked', count(array_filter($byC, static fn(array $c): bool => $c['code'] === 'UG')) === 1 && count(array_filter($byC, static fn(array $c): bool => $c['code'] === 'KE')) === 1);
check('country names', Countries::name('KE') === 'Kenya' && Countries::name(null) === 'Unknown' && Countries::clean('xx') === null && Countries::clean('ug') === 'UG');
$fu = VisitReport::filters('30', 'unknown', '/zz-visits');
check('the "unknown" country filter works', VisitReport::summary($fu)['views'] === 1);
$fp = VisitReport::filters('7', '', 'about');
check('filtering by page text works', array_sum(array_column(VisitReport::topPages(VisitReport::filters('30', '', 'zz-visits/about')), 'views')) === 1 && $fp['page'] === 'about');
check('the daily series has one entry per day', count(VisitReport::daily(VisitReport::filters('7', '', '/zz-visits'))) === 7);
check('the CSV export lists the filtered visits with country names', count(VisitReport::export($fk)) === 4 && VisitReport::export($fk)[0]['Country'] === 'Kenya');
check('a bad period or country filter falls back safely', VisitReport::filters('abc', '<script>', '')['period'] === '30' && VisitReport::filters('30', '<script>', '')['country'] === '');

$_SERVER = $saveServer;
$clean();
check('cleanup: no test visits are left', (int) Database::value("SELECT COUNT(*) FROM site_visits WHERE path LIKE '/zz-visits%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
