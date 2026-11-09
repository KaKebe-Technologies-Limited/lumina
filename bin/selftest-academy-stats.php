<?php
declare(strict_types=1);

/**
 * LAF Academy DASHBOARDS: the numbers (AcademyStats), the public page /academy/impact, the block on /academy and Admin > LAF Academy > Statistics (with its CSV exports).
 * Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-stats.php
 * Needs the local web server. Adds its own learners (zz-st-*@example.test) with enrolments and certificates, and removes everything again.
 */

use App\Core\Database;
use App\Services\Academy\AcademyStats;

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

echo "-- academy: dashboards --\n";
$clean = static function (): void {
    Database::execute("DELETE c FROM academy_certificates c JOIN academy_learners l ON l.id = c.learner_id WHERE l.email LIKE 'zz-st-%@example.test'");
    Database::execute("DELETE e FROM academy_enrollments e JOIN academy_learners l ON l.id = e.learner_id WHERE l.email LIKE 'zz-st-%@example.test'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-st-%@example.test'");
    Database::execute("DELETE FROM users WHERE email = 'zz-st-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();

$tables = Database::tableExists('academy_learners') && Database::tableExists('academy_enrollments') && Database::tableExists('academy_certificates');
check('the Academy tables exist', $tables);
$before = AcademyStats::overview();
$topicsBefore = array_column(AcademyStats::byTopic(), 'learners', 'key');
$cats = Database::all("SELECT id, category FROM academy_courses WHERE status = 'published' ORDER BY id LIMIT 200");
$c1 = $cats[0] ?? null;
$other = null;
foreach ($cats as $c) {
    if ($c1 !== null && $c['category'] !== $c1['category']) {
        $other = $c;
        break;
    }
}
check('there are published courses in at least two topics to test with', $c1 !== null && $other !== null);
if ($c1 === null || $other === null) {
    return;
}

// 5 learners: Uganda x2 (one written in capitals), Kenya, Rwanda and one without a country
$people = [['Uganda', 'a'], ['UGANDA', 'b'], ['Kenya', 'c'], ['Rwanda', 'd'], ['', 'e']];
$ids = [];
foreach ($people as [$country, $tag]) {
    $now = date('Y-m-d H:i:s');
    Database::execute('INSERT INTO academy_learners (reg_no, name, email, password_hash, country, email_verified_at, approved_at, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,?,1,?,?)',
        ['LAF-AC-ZS-' . $tag, 'Zz Stat ' . $tag, 'zz-st-' . $tag . '@example.test', 'x', $country, $now, $now, $now, $now]);
    $ids[$tag] = (int) Database::value('SELECT LAST_INSERT_ID()');
}
// a, b, c join the first topic; c and d also join the other topic; c finishes the first course and earns a certificate
foreach (['a', 'b', 'c'] as $t) {
    Database::execute('INSERT INTO academy_enrollments (learner_id, course_id, enrolled_at, completed_at) VALUES (?,?,NOW(),?)', [$ids[$t], (int) $c1['id'], $t === 'c' ? date('Y-m-d H:i:s') : null]);
}
foreach (['c', 'd'] as $t) {
    Database::execute('INSERT INTO academy_enrollments (learner_id, course_id, enrolled_at, completed_at) VALUES (?,?,NOW(),NULL)', [$ids[$t], (int) $other['id']]);
}
Database::execute("INSERT INTO academy_certificates (code, kind, learner_id, course_id, learner_name, course_title, award, score_pct, hours, issued_at, approved_at) VALUES ('ZZSTAT1','course',?,?,'Zz Stat c','Test','Pass',90,2,NOW(),NOW())", [$ids['c'], (int) $c1['id']]);

$o = AcademyStats::overview();
check('overview: five more active learners, five more enrolments, one more completion and one more certificate',
    $o['learners'] === $before['learners'] + 5 && $o['enrollments'] === $before['enrollments'] + 5 && $o['completed'] === $before['completed'] + 1 && $o['certificates'] === $before['certificates'] + 1,
    json_encode($o));
check('overview: the completion rate is completions / enrolments', abs($o['completionPct'] - round($o['completed'] * 100 / max(1, $o['enrollments']), 1)) < 0.05);

$topics = array_column(AcademyStats::byTopic(), 'learners', 'key');
check('by topic: a learner counts once per topic (3 joined the first topic, 2 the other)', ($topics[$c1['category']] ?? 0) === ($topicsBefore[$c1['category']] ?? 0) + 3 && ($topics[$other['category']] ?? 0) === ($topicsBefore[$other['category']] ?? 0) + 2);

$cn = AcademyStats::byCountry(50, true);
$byName = array_column($cn['rows'], 'n', 'country');
check('by country: "Uganda" and "UGANDA" are one country', ($byName['Uganda'] ?? 0) >= 2 && !isset($byName['UGANDA']));
check('by country (admin view): the learner without a country is listed as "Not given"', ($byName['Not given'] ?? 0) >= 1);
check('by country (public view): "Not given" is left out', !in_array('Not given', array_column(AcademyStats::byCountry(50)['rows'], 'country'), true));
check('by country: the shares add up to about 100%', abs(array_sum(array_column($cn['rows'], 'pct')) - 100) < 1.5);
$small = AcademyStats::byCountry(1);
check('by country: only the biggest are named, the rest become "Other countries"', count($small['rows']) === 2 && str_starts_with($small['rows'][1]['country'], 'Other countries'));

$m = AcademyStats::byMonth(12);
check('by month: twelve months, oldest first, including this month with the new learners', count($m) === 12 && $m[11]['ym'] === date('Y-m') && $m[11]['signups'] >= 5 && $m[11]['certificates'] >= 1);
$f = AcademyStats::funnel();
check('funnel: it only goes down and ends with the certificate step', $f[0]['n'] >= $f[3]['n'] && $f[3]['n'] >= $f[4]['n'] && $f[4]['n'] >= $f[5]['n'] && $f[5]['label'] === 'Earned a certificate' && $f[0]['pct'] === 100.0);
$mx = AcademyStats::topicByCountry(6);
check('topics by country: the cells are counts per country column', $mx['countries'] !== [] && ($mx['rows'][0]['cells'] ?? null) !== null && count($mx['rows'][0]['cells']) === count($mx['countries']));
$json = json_encode(AcademyStats::publicData());
check('privacy: the public data holds no names or e-mails', !str_contains((string) $json, 'Zz Stat') && !str_contains((string) $json, '@example.test'));

// ---- the pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$jar = tempnam(sys_get_temp_dir(), 'stj');
$req = static function (string $path, ?array $form = null, ?string $j = null) use ($base, $jar): array {
    $j ??= $jar;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'type' => $type];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $mm) === 1 ? $mm[1] : '';
\App\Core\Settings::set('academy_enabled', '1');
\App\Core\Settings::flush();

$pub = $req('/academy/impact');
check('public: /academy/impact answers with the dashboard (numbers, topics, countries, growth, funnel)', $pub['code'] === 200 && str_contains($pub['body'], 'The Academy in numbers') && str_contains($pub['body'], 'ad-kpis') && str_contains($pub['body'], 'ad-bar') && str_contains($pub['body'], 'ad-donut') && str_contains($pub['body'], 'ad-col') && str_contains($pub['body'], 'ad-funnel'), (string) $pub['code']);
check('public: no learner name or e-mail on the page', !str_contains($pub['body'], 'Zz Stat') && !str_contains($pub['body'], 'zz-st-'));
$home = $req('/academy');
check('public: /academy shows "Our learners at a glance" with a link to the full dashboard', $home['code'] === 200 && str_contains($home['body'], 'Our learners at a glance') && str_contains($home['body'], '/academy/impact'));
$sm = $req('/sitemap.xml');
check('the dashboard is in the sitemap', str_contains($sm['body'], 'academy/impact'));

$apw = 'Stats admin pass 71';
\App\Models\User::create(['name' => 'ZZ St Admin', 'email' => 'zz-st-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'sta');
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-st-admin@example.test', 'password' => $apw], $ja);
$ad = $req('/admin/academy/statistics', null, $ja);
check('admin: Statistics shows the numbers, learners by topic and country, the matrix, growth and the funnel', $ad['code'] === 200 && str_contains($ad['body'], 'Academy statistics') && str_contains($ad['body'], 'Learners by topic') && str_contains($ad['body'], 'Learners by country') && str_contains($ad['body'], 'Topics by country') && str_contains($ad['body'], 'From sign-up to certificate'), (string) $ad['code']);
check('admin: the sidebar has the Statistics link', str_contains($ad['body'], 'admin/academy/statistics'));
$dash = $req('/admin', null, $ja);
check('admin: the main Dashboard has the "LAF Academy at a glance" block with a link to the statistics', $dash['code'] === 200 && str_contains($dash['body'], 'LAF Academy at a glance') && str_contains($dash['body'], 'admin/academy/statistics'), (string) $dash['code']);
$csv = $req('/admin/academy/statistics/export?what=countries', null, $ja);
check('admin: the country CSV downloads with the learner counts (and no personal details)', $csv['code'] === 200 && str_contains($csv['type'], 'csv') && str_contains($csv['body'], 'Country,Learners') && str_contains($csv['body'], 'Uganda') && !str_contains($csv['body'], '@example.test'), $csv['type']);
$csvT = $req('/admin/academy/statistics/export?what=topics', null, $ja);
check('admin: the topic CSV downloads', $csvT['code'] === 200 && str_contains($csvT['body'], 'Topic,Courses,Learners'));
$anon = $req('/admin/academy/statistics', null, tempnam(sys_get_temp_dir(), 'stn'));
check('admin: a visitor who is not signed in is sent to the sign-in page', $anon['code'] === 302);

foreach ([$jar, $ja] as $f) {
    @unlink($f);
}
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
