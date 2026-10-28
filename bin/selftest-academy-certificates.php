<?php
declare(strict_types=1);

/**
 * Academy certificates: the new design (PDF), approval by an administrator, e-mail and download. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-certificates.php
 * Removes its own rows (zz-lce-*, LAF certificates of "ZZ Lce" learners) and restores the setting.
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

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Academy\CertificateService;

$cleanup = static function (): void {
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Lce%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lce-%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-lce-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();
$oldSetting = Settings::get('academy_cert_approval', null);
Settings::set('academy_cert_approval', '1');
Settings::flush();

$course = Database::one("SELECT * FROM academy_courses ORDER BY id LIMIT 1");
if ($course === null) {
    \App\Services\Academy\ContentInstaller::installAll(null, true);
    $course = Database::one('SELECT * FROM academy_courses ORDER BY id LIMIT 1');
}
$course2 = Database::one('SELECT * FROM academy_courses WHERE id <> ? ORDER BY id LIMIT 1', [(int) $course['id']]);
$mk = static fn(string $tag): array => (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) Database::insert('academy_learners', [
    'name' => 'ZZ Lce ' . $tag, 'email' => 'zz-lce-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'), 'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
])]);
$la = $mk('a');
check('approval: the columns exist after the schema migration', CertificateService::approvalReady() && CertificateService::approvalRequired());

// ------------------------------------------------------------------------------------------------------------- waiting certificate
$c = CertificateService::issue($la, $course, 96.4, 270);
check('approval: a certificate earned by passing the exam waits (no approval time)', $c['approved_at'] === null && !CertificateService::isApproved($c));
check('approval: the learner cannot use it (not "active"), but it is found as waiting', CertificateService::activeFor((int) $la['id'], (int) $course['id']) === null && CertificateService::pendingFor((int) $la['id'], (int) $course['id'])['id'] === $c['id']);
check('approval: the same one is returned again, never a duplicate', CertificateService::issue($la, $course, 90.0, 60)['id'] === $c['id'] && (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE learner_id = ?', [(int) $la['id']], 0) === 1);
check('approval: the learner list hides it, the administrator list shows it', CertificateService::forLearner((int) $la['id']) === [] && count(CertificateService::forLearner((int) $la['id'], false)) === 1 && count(CertificateService::pendingForLearner((int) $la['id'])) === 1 && CertificateService::pendingCount() >= 1);

// ------------------------------------------------------------------------------------------------------------- the PDF design
$pdf = CertificateService::pdf($c + ['revoked_at' => null]);
check('pdf: a one-page A4 landscape PDF', str_starts_with($pdf, '%PDF-') && str_contains($pdf, '/MediaBox [0 0 841.89 595.28]') && substr_count($pdf, '/Type /Page ') + substr_count($pdf, '/Type /Page' . "\n") >= 1 && str_contains($pdf, '%%EOF'));
check('pdf: the Info title carries the certificate number', str_contains($pdf, 'Certificate ' . $c['code']));
check('pdf: it includes the logo image and is a reasonable size', str_contains($pdf, '/Subtype /Image') && strlen($pdf) > 20000 && strlen($pdf) < 1500000, (string) strlen($pdf));
$long = CertificateService::pdf(['code' => 'LAF-26-ABCDEFGH', 'kind' => 'course', 'learner_name' => 'Muhammad Abdul-Rahman Oluwaseun Chukwuemeka Nwachukwu', 'course_title' => 'Computer Basics and Digital Literacy: a complete beginner course for everyone',
    'score_pct' => null, 'hours' => null, 'issued_at' => date('Y-m-d H:i:s'), 'approved_at' => null, 'revoked_at' => null]);
check('pdf: a very long name and course title, without score or hours, still make a valid PDF', str_starts_with($long, '%PDF-') && str_contains($long, '%%EOF'));
$ch = CertificateService::pdf(['code' => 'LAF-26-CHALLENG', 'kind' => 'challenge', 'award' => 'Winner, 1st place', 'learner_name' => 'ZZ Lce Challenge', 'course_title' => 'Build a poster', 'score_pct' => '88.0', 'hours' => null,
    'issued_at' => date('Y-m-d H:i:s'), 'approved_at' => date('Y-m-d H:i:s'), 'revoked_at' => null]);
check('pdf: a challenge certificate uses the same design', str_starts_with($ch, '%PDF-') && str_contains($ch, '%%EOF'));

// ------------------------------------------------------------------------------------------------------------- over HTTP
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lcj');
    $ch2 = curl_init($base . $path);
    curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch2);
    $code = (int) curl_getinfo($ch2, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
    curl_close($ch2);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    return $req('POST', $path, $form + ['_token' => $tok($page['body'])], $jar);
};
$mkU = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ Lce ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$pwA = 'Admin lce pass 91';
$pwE = 'Editor lce pass 94';
$adminId = $mkU('zz-lce-admin@example.test', 'admin', $pwA);
$mkU('zz-lce-editor@example.test', 'editor', $pwE);
$jA = $jE = $jP = null;
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    $r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
    return in_array($r['code'], [302, 303], true);
};
check('http: the test administrator and editor can sign in', $login('zz-lce-admin@example.test', $pwA, $jA) && $login('zz-lce-editor@example.test', $pwE, $jE));
check('http: a waiting certificate is unknown to the public check page and PDF (404)', $req('GET', '/academy/certificate/' . $c['code'], [], $jP)['code'] === 404 && $req('GET', '/academy/certificate/' . $c['code'] . '/pdf', [], $jP)['code'] === 404);

$list = $req('GET', '/admin/academy/certificates?status=waiting', [], $jA);
check('http: the administrator list shows it as waiting for approval with the Approve buttons', $list['code'] === 200 && str_contains($list['body'], $c['code']) && str_contains($list['body'], 'Waiting for approval') && str_contains($list['body'], 'Approve and e-mail') && str_contains($list['body'], 'Approve all'));
$prev = $req('GET', '/admin/academy/certificates/' . $c['id'] . '/preview', [], $jA);
check('http: the administrator can look at the PDF before approving; an editor cannot (403)', $prev['code'] === 200 && str_starts_with($prev['body'], '%PDF-') && $req('GET', '/admin/academy/certificates/' . $c['id'] . '/preview', [], $jE)['code'] === 403);
check('http: an editor cannot approve (403)', $post('/admin/academy/certificates/' . $c['id'] . '/approve', [], $jE, '/admin')['code'] === 403 && !CertificateService::isApproved((array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [(int) $c['id']])));

$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$ap = $post('/admin/academy/certificates/' . $c['id'] . '/approve', [], $jA, '/admin/academy/certificates');
$row = (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [(int) $c['id']]);
check('http: "Approve" makes it available, records who approved it, and sends no e-mail', in_array($ap['code'], [302, 303], true) && $row['approved_at'] !== null && (int) $row['approved_by'] === $adminId && $row['emailed_at'] === null && (glob(STORAGE_PATH . '/mail/*.eml') ?: []) === $mailsBefore);
$pub = $req('GET', '/academy/certificate/' . $c['code'], [], $jP);
$pubPdf = $req('GET', '/academy/certificate/' . $c['code'] . '/pdf', [], $jP);
check('http: once approved, the check page and the PDF work for everyone', $pub['code'] === 200 && str_contains($pub['body'], 'genuine certificate') && $pubPdf['code'] === 200 && str_starts_with($pubPdf['body'], '%PDF-'));
check('model: it is now active for the learner and counted as approved', CertificateService::activeFor((int) $la['id'], (int) $course['id']) !== null && CertificateService::pendingFor((int) $la['id'], (int) $course['id']) === null);

$em = $post('/admin/academy/certificates/' . $c['id'] . '/email', [], $jA, '/admin/academy/certificates');
$new = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore));
$mailText = static function (string $file): string {
    $raw = (string) file_get_contents($file);
    $out = $raw;
    if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $raw, $parts) > 0) {
        foreach ($parts[1] as $b) {
            $out .= "\n" . (string) base64_decode((string) preg_replace('/\s+/', '', $b));
        }
    }
    return $out;
};
check('http: "E-mail it" sends the learner the link to the certificate and records the time', in_array($em['code'], [302, 303], true) && count($new) === 1 && str_contains($mailText($new[0]), 'zz-lce-a@example.test') && str_contains($mailText($new[0]), $c['code'])
    && Database::value('SELECT emailed_at FROM academy_certificates WHERE id = ?', [(int) $c['id']]) !== null);

// approve all (two more waiting ones, e-mailed)
$lb = $mk('b');
$lc = $mk('c');
$cb = CertificateService::issue($lb, $course, 80.0, 60);
$cc = CertificateService::issue($lc, $course, 85.0, 60);
$mailsBefore2 = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$all = $post('/admin/academy/certificates-approve-all', ['email' => '1'], $jA, '/admin/academy/certificates');
$stillWaiting = (int) Database::value("SELECT COUNT(*) FROM academy_certificates WHERE learner_name LIKE 'ZZ Lce%' AND approved_at IS NULL", [], 9);
$mails2 = array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore2);
check('http: "Approve all" approves every waiting certificate and e-mails each learner', in_array($all['code'], [302, 303], true) && $stillWaiting === 0 && count($mails2) >= 2);

// a withdrawn certificate is never e-mailed
CertificateService::revoke((int) $cb['id'], 'selftest');
check('email: a withdrawn certificate is not e-mailed', CertificateService::email((array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [(int) $cb['id']])) === false);
CertificateService::reinstate((int) $cb['id']);

// ------------------------------------------------------------------------------------------------------------- approval switched off, and by hand
Settings::set('academy_cert_approval', '0');
Settings::flush();
$ld = $mk('d');
$cd = CertificateService::issue($ld, $course, 70.0, 30);
check('setting off: a certificate is available at once (approved when issued)', !CertificateService::approvalRequired() && CertificateService::isApproved($cd) && CertificateService::activeFor((int) $ld['id'], (int) $course['id']) !== null);
Settings::set('academy_cert_approval', '1');
Settings::flush();
if ($course2 !== null) {
    $le = $mk('e');
    $ce = CertificateService::issue($le, $course2, null, 0, true);
    check('by hand: a certificate issued by an administrator is approved at once, even with approval on', CertificateService::isApproved($ce));
}
$ctx = ['kind' => 'challenge', 'award' => 'Participant'];
$chal = Database::one('SELECT * FROM academy_challenges ORDER BY id LIMIT 1');
if ($chal !== null) {
    $lf = $mk('f');
    $cf = CertificateService::issueForChallenge($lf, $chal, 'Participant', 50.0);
    check('challenge: a challenge certificate (issued when the team finalises the results) is approved at once', CertificateService::isApproved($cf));
}

// ------------------------------------------------------------------------------------------------------------- restore
if ($oldSetting === null) {
    Database::execute("DELETE FROM settings WHERE setting_key = 'academy_cert_approval'");
} else {
    Settings::set('academy_cert_approval', (string) $oldSetting);
}
Settings::flush();
$cleanup();
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
    if (str_contains($mailText($f), 'zz-lce-')) {
        @unlink($f);
    }
}
check('cleanup: no zz-lce rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_certificates WHERE learner_name LIKE 'ZZ Lce%'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-lce-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
