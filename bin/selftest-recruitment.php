<?php
declare(strict_types=1);

/**
 * RECRUITMENT (ambassadors, jobs, volunteers): tracking numbers, required CV + portrait, KPI scoring with the 70% interview rule, stage e-mails, the public tracking page and the admin panels.
 * Run alone:   /c/xampp/php/php.exe bin/selftest-recruitment.php        Needs the local web server and APP_ENV=local (mail driver "file").
 */

use App\Core\Database;
use App\Services\Recruitment\Recruitment;

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

echo "-- recruitment --\n";
$clean = static function (): void {
    foreach (['ambassador_applications', 'applications', 'involvement_requests'] as $t) {
        $rows = Database::all("SELECT id, cv_path" . ($t === 'applications' ? '' : '') . ", photo_path FROM `{$t}` WHERE email LIKE 'zz-rc-%@example.test'");
        foreach ($rows as $r) {
            foreach (['cv_path', 'photo_path'] as $c) {
                if (!empty($r[$c])) {
                    \App\Core\Uploader::deletePrivate((string) $r[$c]);
                }
            }
        }
        Database::execute("DELETE FROM `{$t}` WHERE email LIKE 'zz-rc-%@example.test'");
    }
    Database::execute("DELETE FROM recruit_log WHERE scope IN ('ambassador','job','volunteer') AND application_id NOT IN (SELECT id FROM ambassador_applications UNION SELECT id FROM applications UNION SELECT id FROM involvement_requests)");
    Database::execute("DELETE FROM recruit_scores WHERE application_id NOT IN (SELECT id FROM ambassador_applications UNION SELECT id FROM applications UNION SELECT id FROM involvement_requests)");
    Database::execute("DELETE FROM users WHERE email = 'zz-rc-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
check('the recruitment tables and columns are created on first use', Recruitment::install());
$clean();
foreach (Recruitment::SCOPES as $scope => $cfg) {
    check("default criteria exist for $scope", count(Recruitment::kpis($scope)) >= 4);
}
check('old rows got a tracking number', (int) Database::value('SELECT COUNT(*) FROM ambassador_applications WHERE ref_no IS NULL', [], 0) === 0);

// ---- helpers for files
$pngPath = sys_get_temp_dir() . '/zz-rc-portrait.png';
$w = 320;
$raw = '';
for ($y = 0; $y < $w; $y++) {
    $raw .= "\0";
    for ($x = 0; $x < $w; $x++) {
        $raw .= chr(($x * 3 + $y) % 256) . chr(($x + $y * 2) % 256) . chr(($x * $y) % 256);
    }
}
$chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
file_put_contents($pngPath, "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $w, 8, 2, 0, 0, 0)) . $chunk('IDAT', gzcompress($raw)) . $chunk('IEND', ''));
$pdfPath = sys_get_temp_dir() . '/zz-rc-cv.pdf';
file_put_contents($pdfPath, \App\Services\Letters\LetterPdf::build(array_merge(\App\Services\Letters\LetterService::blank(), ['subject' => 'CV test', 'body' => 'A short CV for the test applicant.', 'from_name' => 'Test']), ['compress' => false]));

// ---- the public pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, array|string|null $form, string $j) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $type = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'type' => $type, 'head' => substr($raw, 0, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $mm) === 1 ? $mm[1] : '';
$hp = static function (string $html): array {
    preg_match('/name="_hp_ts" value="([^"]+)"/', $html, $m);
    return ['_hp_ts' => $m[1] ?? '', 'website' => ''];
};
$file = static fn(string $p, string $mime, string $name): \CURLFile => new \CURLFile($p, $mime, $name);
$mails = static function (): int {
    return count(glob(dirname(__DIR__) . '/storage/mail/*.eml') ?: []);
};

$jp = tempnam(sys_get_temp_dir(), 'rcp');
$ambPage = $req('/involvement/ambassadors', null, $jp);
check('the ambassador form asks for a CV and a portrait', $ambPage['code'] === 200 && str_contains($ambPage['body'], 'name="cv"') && str_contains($ambPage['body'], 'name="portrait"') && str_contains($ambPage['body'], 'multipart/form-data'), (string) $ambPage['code']);
sleep(4);
$base1 = ['_token' => $tok($ambPage['body']), 'name' => 'Zz Rc Amb', 'email' => 'zz-rc-amb@example.test', 'region' => 'africa', 'country' => 'Uganda', 'motivation' => 'I want to help the foundation.',
    'roles[0]' => 'fundraise', 'pledge_types[0]' => 'other', 'other_text' => 'Introductions to donors', 'consent' => '1'] + $hp($ambPage['body']);
$noFiles = $req('/involvement/ambassadors', $base1, $jp);
check('ambassador: without CV and portrait nothing is saved', (int) Database::value("SELECT COUNT(*) FROM ambassador_applications WHERE email = 'zz-rc-amb@example.test'", [], 0) === 0 && $noFiles['code'] === 302);
$ambPage = $req('/involvement/ambassadors', null, $jp);
sleep(4);
$m0 = $mails();
$full = array_merge($base1, ['_token' => $tok($ambPage['body']), 'cv' => $file($pdfPath, 'application/pdf', 'cv.pdf'), 'portrait' => $file($pngPath, 'image/png', 'me.png')], $hp($ambPage['body']));
$ok = $req('/involvement/ambassadors', $full, $jp);
$amb = Database::one("SELECT * FROM ambassador_applications WHERE email = 'zz-rc-amb@example.test'");
check('ambassador: saved with a tracking number, the CV and the portrait', $amb !== null && preg_match('/^AMB-\d{4}-\d{4}$/', (string) $amb['ref_no']) === 1 && !empty($amb['cv_path']) && !empty($amb['photo_path']), json_encode([$ok['code'], $amb['ref_no'] ?? null]));
check('ambassador: the applicant was e-mailed the number', $mails() > $m0);
$ambId = (int) ($amb['id'] ?? 0);

// job application
Database::execute("DELETE FROM jobs WHERE slug = 'zz-rc-vacancy'");
Database::execute("INSERT INTO jobs (slug, title, employment_type, is_active) VALUES ('zz-rc-vacancy', 'ZZ Test Vacancy', 'volunteer', 1)");
$jobId = (int) Database::value('SELECT LAST_INSERT_ID()');
$jobSlug = 'zz-rc-vacancy';
if ($jobSlug === '') {
    check('(skipped) no open vacancy locally to apply to', true);
} else {
    $cp = $req('/careers/' . $jobSlug, null, $jp);
    sleep(4);
    $jobForm = ['_token' => $tok($cp['body']), 'name' => 'Zz Rc Job', 'email' => 'zz-rc-job@example.test', 'consent' => '1'] + $hp($cp['body']);
    $req('/careers/' . $jobSlug . '/apply', $jobForm, $jp);
    check('job: without a CV and portrait nothing is saved', (int) Database::value("SELECT COUNT(*) FROM applications WHERE email = 'zz-rc-job@example.test'", [], 0) === 0);
    $cp = $req('/careers/' . $jobSlug, null, $jp);
    sleep(4);
    $req('/careers/' . $jobSlug . '/apply', array_merge($jobForm, ['_token' => $tok($cp['body']), 'cv' => $file($pdfPath, 'application/pdf', 'cv.pdf'), 'portrait' => $file($pngPath, 'image/png', 'me.png')], $hp($cp['body'])), $jp);
    $job = Database::one("SELECT * FROM applications WHERE email = 'zz-rc-job@example.test'");
    check('job: saved with JOB- number, CV and portrait', $job !== null && str_starts_with((string) $job['ref_no'], 'JOB-') && !empty($job['cv_path']) && !empty($job['photo_path']), json_encode($job ? [$job['ref_no']] : null));
}

// volunteer
$ip = $req('/involvement', null, $jp);
check('the involvement form has the volunteer CV and portrait fields', str_contains($ip['body'], 'name="portrait"') && str_contains($ip['body'], 'data-inv-volunteer'));
sleep(4);
$volForm = ['_token' => $tok($ip['body']), 'name' => 'Zz Rc Vol', 'email' => 'zz-rc-vol@example.test', 'involvement_type' => 'volunteer', 'message' => 'I can help on weekends.'] + $hp($ip['body']);
$req('/involvement', $volForm, $jp);
check('volunteer: without CV and portrait nothing is saved', (int) Database::value("SELECT COUNT(*) FROM involvement_requests WHERE email = 'zz-rc-vol@example.test'", [], 0) === 0);
$ip = $req('/involvement', null, $jp);
sleep(4);
$req('/involvement', array_merge($volForm, ['_token' => $tok($ip['body']), 'cv' => $file($pdfPath, 'application/pdf', 'cv.pdf'), 'portrait' => $file($pngPath, 'image/png', 'me.png')], $hp($ip['body'])), $jp);
$vol = Database::one("SELECT * FROM involvement_requests WHERE email = 'zz-rc-vol@example.test'");
check('volunteer: saved with VOL- number, CV and portrait', $vol !== null && str_starts_with((string) $vol['ref_no'], 'VOL-') && !empty($vol['cv_path']) && !empty($vol['photo_path']));
$ip = $req('/involvement', null, $jp);
sleep(4);
$req('/involvement', ['_token' => $tok($ip['body']), 'name' => 'Zz Rc Partner', 'email' => 'zz-rc-partner@example.test', 'involvement_type' => 'partner'] + $hp($ip['body']), $jp);
check('a partner needs no CV or portrait', (int) Database::value("SELECT COUNT(*) FROM involvement_requests WHERE email = 'zz-rc-partner@example.test'", [], 0) === 1);
$volId = (int) ($vol['id'] ?? 0);

// ---- scoring and the 70% rule
$kpis = Recruitment::kpis('ambassador');
$low = [];
foreach ($kpis as $k) {
    $low[(int) $k['id']] = 2;
}
$m0 = $mails();
$r = Recruitment::judge('ambassador', $ambId, $low, null);
check('judge: all criteria at 2/5 = 40%, below the level, no interview invitation', $r['complete'] && abs((float) $r['pct'] - 40.0) < 0.1 && !$r['invited'] && Database::value('SELECT status FROM ambassador_applications WHERE id = ?', [$ambId]) === 'new', json_encode($r));
$half = array_slice($kpis, 0, 1);
$high = [];
foreach ($kpis as $k) {
    $high[(int) $k['id']] = 4;
}
$r = Recruitment::judge('ambassador', $ambId, $high, null);
check('judge: all criteria at 4/5 = 80% reaches 70% and invites the applicant for an interview', $r['invited'] && abs((float) $r['pct'] - 80.0) < 0.1 && Database::value('SELECT status FROM ambassador_applications WHERE id = ?', [$ambId]) === 'interview', json_encode($r));
check('judge: the invitation e-mail was sent and the history records it', $mails() > $m0 && Recruitment::history('ambassador', $ambId) !== [] && (int) Database::value("SELECT mailed FROM recruit_log WHERE scope = 'ambassador' AND application_id = ? AND status = 'interview' ORDER BY id DESC LIMIT 1", [$ambId], 0) === 1);
$r2 = Recruitment::judge('ambassador', $ambId, $high, null);
check('judge: scoring again does not invite twice', !$r2['invited']);
$res = Recruitment::setStatus('ambassador', $ambId, 'approved', 'Onboarding anyway', null, 'Welcome!');
check('admin can still accept (onboard) the applicant, who is e-mailed', $res['ok'] && $res['changed'] && $res['mailed'] === true && Database::value('SELECT status FROM ambassador_applications WHERE id = ?', [$ambId]) === 'approved');
if (!empty($job['id'])) {
    $jk = Recruitment::kpis('job');
    $p = [];
    foreach ($jk as $k) {
        $p[(int) $k['id']] = 1;
    }
    $r = Recruitment::judge('job', (int) $job['id'], $p, null);
    check('a low score leaves a job applicant where they are (the admin decides)', !$r['invited'] && Database::value('SELECT status FROM applications WHERE id = ?', [(int) $job['id']]) === 'new');
    $res = Recruitment::setStatus('job', (int) $job['id'], 'rejected', '', null, '');
    check('declining a job applicant e-mails them', $res['changed'] && $res['mailed'] === true);
}
$vk = Recruitment::kpis('volunteer');
$p = [];
foreach ($vk as $k) {
    $p[(int) $k['id']] = 5;
}
$r = Recruitment::judge('volunteer', $volId, $p, null);
check('volunteer: a top score invites them for an interview', $r['invited'] && Database::value('SELECT status FROM involvement_requests WHERE id = ?', [$volId]) === 'interview');

// ---- tracking page
$t = Recruitment::track((string) $amb['ref_no'], 'zz-rc-amb@example.test');
check('track: the number and e-mail show the stage', $t['found'] && $t['label'] !== '' && $t['tone'] === 'good', json_encode($t));
check('track: a wrong e-mail address shows nothing', !Recruitment::track((string) $amb['ref_no'], 'someone@else.test')['found']);
$tp = $req('/track-application', null, $jp);
check('the public tracking page opens', $tp['code'] === 200 && str_contains($tp['body'], 'Application number'), (string) $tp['code']);

// ---- admin pages
$pw = 'Recruit admin pass 71';
\App\Models\User::create(['name' => 'ZZ Rc Admin', 'email' => 'zz-rc-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$ja = tempnam(sys_get_temp_dir(), 'rca');
$lp = $req('/admin/login', null, $ja);
$req('/admin/login', http_build_query(['_token' => $tok($lp['body']), 'email' => 'zz-rc-admin@example.test', 'password' => $pw]), $ja);
$show = $req('/admin/ambassadors/' . $ambId, null, $ja);
check('admin: the ambassador page shows the number, the judging panel and the stage history', $show['code'] === 200 && str_contains($show['body'], (string) $amb['ref_no']) && str_contains($show['body'], 'Judge this application') && str_contains($show['body'], 'Stages and e-mails'), (string) $show['code']);
$pt = $req('/admin/recruitment/ambassador/' . $ambId . '/portrait', null, $ja);
check('admin: the portrait is served to the team', $pt['code'] === 200 && str_contains($pt['type'], 'image/'), $pt['type']);
$cvr = $req('/admin/recruitment/ambassador/' . $ambId . '/cv', null, $ja);
check('admin: the CV downloads', $cvr['code'] === 200, (string) $cvr['code']);
$anon = $req('/admin/recruitment/ambassador/' . $ambId . '/portrait', null, tempnam(sys_get_temp_dir(), 'rcn'));
check('the portrait is not public', $anon['code'] === 302);
$cr = $req('/admin/recruitment/criteria?scope=job', null, $ja);
check('admin: the criteria page lists the KPIs and the interview level', $cr['code'] === 200 && str_contains($cr['body'], 'Interview level') && str_contains($cr['body'], 'Relevant experience'), (string) $cr['code']);
$jd = $req('/admin/recruitment/ambassador/' . $ambId . '/judge', http_build_query(['_token' => $tok($show['body']), 'points' => $low]), $ja);
check('admin: the judge form saves the clicks', $jd['code'] === 302 && abs((float) Database::value('SELECT score_pct FROM ambassador_applications WHERE id = ?', [$ambId]) - 40.0) < 0.1);
$vshow = $req('/admin/involvement/' . $volId, null, $ja);
check('admin: a volunteer request shows the judging panel too', $vshow['code'] === 200 && str_contains($vshow['body'], 'Judge this application'));
if (!empty($job['id'])) {
    $jshow = $req('/admin/applications/' . (int) $job['id'], null, $ja);
    check('admin: a job application shows the judging panel too', $jshow['code'] === 200 && str_contains($jshow['body'], 'Judge this application'));
}

Database::execute("DELETE FROM jobs WHERE slug = 'zz-rc-vacancy'");
@unlink($pngPath);
@unlink($pdfPath);
@unlink($jp);
@unlink($ja);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
