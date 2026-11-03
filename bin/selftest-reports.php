<?php
declare(strict_types=1);

/**
 * Admin > Reports: every report of App\Services\Reports\ReportCatalog runs without a database error (for "all time" and for a custom range),
 * the hub and a report open for an administrator, and the CSV download works. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-reports.php
 * Needs the local web server for the page checks.
 */

use App\Core\Database;
use App\Services\Reports\ReportCatalog;

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

echo "-- reports --\n";
$all = ReportCatalog::all();
check('the catalogue holds at least 60 reports', count($all) >= 60, (string) count($all));
$groupsOk = true;
foreach ($all as $k => $rep) {
    $groupsOk = $groupsOk && isset(ReportCatalog::GROUPS[$rep['group']]) && $rep['key'] === $k && $rep['cols'] !== [] && is_string($rep['ability']) && $rep['ability'] !== '';
}
check('every report has a key, a known group, columns and an ability', $groupsOk);
$ranges = ['all' => ReportCatalog::range('all', '', ''), 'custom' => ReportCatalog::range('custom', '2020-01-01', '2030-12-31'), '30' => ReportCatalog::range('30', '', '')];
$bad = [];
$colMismatch = [];
foreach ($all as $k => $rep) {
    foreach ($ranges as $rn => $rg) {
        $res = ReportCatalog::run($rep, $rg, 20);
        if ($res['error'] !== null) {
            $bad[] = $k . '(' . $rn . ')';
            continue;
        }
        if ($rn === 'all' && $res['rows'] !== []) {
            $missing = array_diff(array_keys($rep['cols']), array_keys($res['rows'][0]));
            if ($missing !== []) {
                $colMismatch[] = $k . ': ' . implode(',', $missing);
            }
        }
    }
}
check('every report runs without a database error (all time, custom range, last 30 days)', $bad === [], implode(', ', $bad));
check('every report that returns rows returns all its declared columns', $colMismatch === [], implode(' | ', $colMismatch));
check('range helper: custom dates are made inclusive, junk falls back to 30 days', $ranges['custom']['to'] === '2031-01-01 00:00:00' && ReportCatalog::range('zzz', 'x', 'y')['period'] === '30' && ReportCatalog::range('custom', 'x', 'y')['from'] === null);

// ---- the pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
Database::execute("DELETE FROM users WHERE email = 'zz-report-admin@example.test'");
$apw = 'Report admin pass 55';
\App\Models\User::create(['name' => 'ZZ Report Admin', 'email' => 'zz-report-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'rpj');
$req = static function (string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== []) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$page = $req('/admin/login');
$req('/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-report-admin@example.test', 'password' => $apw]);
$r = $req('/admin/reports');
check('hub: opens (200) and lists the groups and reports', $r['code'] === 200 && str_contains($r['body'], 'Donations') && str_contains($r['body'], 'LAF Academy') && str_contains($r['body'], 'admin/reports/academy-learners'), (string) $r['code']);
$opened = 0;
$failedPages = [];
foreach (['donations-list', 'academy-learners', 'academy-never-signed-in', 'shop-orders', 'members-list', 'traffic-daily', 'audit-log', 'newsletter-issues', 'content-posts-summary', 'meetings-list'] as $k) {
    $r = $req('/admin/reports/' . $k . '?period=all');
    if ($r['code'] === 200 && str_contains($r['body'], ReportCatalog::all()[$k]['title'])) {
        $opened++;
    } else {
        $failedPages[] = $k . ':' . $r['code'];
    }
}
check('ten different reports open (200) with their title', $opened === 10, implode(', ', $failedPages));
$r = $req('/admin/reports/audit-log?period=all');
check('a report with data shows rows and the download button', str_contains($r['body'], 'reports/audit-log/export') && str_contains($r['body'], '<tbody'));
$r = $req('/admin/reports/audit-log/export?period=all');
check('CSV download works and starts with the column headings', $r['code'] === 200 && str_starts_with(ltrim($r['body'], "\xEF\xBB\xBF"), 'When,'), substr($r['body'], 0, 60));
$r = $req('/admin/reports/no-such-report');
check('an unknown report is a 404', $r['code'] === 404, (string) $r['code']);
$r = $req('/admin/error-log');
check('Error log page opens (200) for an administrator', $r['code'] === 200 && str_contains($r['body'], 'Error log'), (string) $r['code']);
$r = $req('/admin/error-log?level=all&days=7&q=zzz-nothing-matches');
check('Error log: filters work (nothing matches a nonsense word)', $r['code'] === 200 && (str_contains($r['body'], 'Nothing to show') || str_contains($r['body'], 'No log file')), (string) $r['code']);
@unlink($jar);
Database::execute("DELETE FROM users WHERE email = 'zz-report-admin@example.test'");
Database::execute("DELETE FROM audit_log WHERE action = 'reports.export' AND user_name = 'ZZ Report Admin'");

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
