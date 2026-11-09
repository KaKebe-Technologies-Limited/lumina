<?php
declare(strict_types=1);

/**
 * LAF Academy: the BLURRED certificate preview. A learner whose certificate is not issued yet (waiting for the fee and/or the administrator's approval) can see how it will look
 * with every detail hidden; the clear view and the PDF only open once it is approved. The picture is REDACTED on the server (placeholders instead of the name, course, score, date,
 * number and QR code), so the blur is only decoration. Included by bin/selftest.php or run alone:   /c/xampp/php/php.exe bin/selftest-academy-cert-locked.php
 * Needs the local web server. Removes its rows (zz-lk-*) and puts the settings back.
 */

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\CertificateService;

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

echo "-- academy: blurred certificate preview --\n";
$cleanup = static function (): void {
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'Zz Lk%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lk-%'");
    Database::execute('DELETE FROM rate_limits');
};
$cleanup();
$old = Settings::get('academy_cert_approval', null);
Settings::set('academy_cert_approval', '1');
Settings::set('academy_enabled', '1');
Settings::flush();

$course = Database::one("SELECT * FROM academy_courses WHERE status = 'published' ORDER BY id LIMIT 1");
check('there is a published course to test with', $course !== null);
if ($course === null) {
    return;
}
$mk = static fn(string $tag): array => (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) Database::insert('academy_learners', [
    'reg_no' => 'LAF-AC-LK-' . $tag, 'name' => 'Zz Lk Person ' . $tag, 'email' => 'zz-lk-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'),
    'email_verified_at' => date('Y-m-d H:i:s'), 'approved_at' => date('Y-m-d H:i:s'), 'is_active' => 1, 'country' => 'Uganda',
])]);
$a = $mk('a');
$b = $mk('b');
$cert = CertificateService::issue($a, $course, 93.7, 300);
$certB = CertificateService::issue($b, $course, 71.2, 200);
check('a certificate earned by passing waits for approval', !CertificateService::isApproved($cert) && !CertificateService::isApproved($certB));

// ---- the redacted picture: nothing real in it
$svg = CertificateService::lockedSvg($cert);
check('redacted picture: it is the certificate layout (wording, signature)', str_contains($svg, '<svg') && stripos($svg, 'certificate') !== false);
check('redacted picture: it holds NO real detail (name, course, number, score)', !str_contains($svg, 'Zz Lk') && !str_contains($svg, strtoupper('Zz Lk')) && !str_contains($svg, (string) $cert['code']) && !str_contains($svg, mb_strtoupper((string) $course['title'])) && !str_contains($svg, '>93.7%<') && !str_contains($svg, '>94%<'), 'leak');
check('redacted picture: the QR code is of a placeholder, not of the real check page', !str_contains(CertificateService::lockedSvg($cert), rawurlencode((string) $cert['code'])));
$real = CertificateService::svg($cert);
check('control: the real picture does hold them (so the test above means something)', str_contains($real, mb_strtoupper('Zz Lk Person a')) && str_contains($real, (string) $cert['code']));

// ---- the pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string $j = null) use ($base): array {
    $j ??= tempnam(sys_get_temp_dir(), 'lkj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$hp = static function (): string {
    $ts = (string) (time() - 30);
    return $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
};
$login = static function (string $email) use ($req, $tok, $hp): string {
    $j = tempnam(sys_get_temp_dir(), 'lkl');
    $p = $req('GET', '/academy/login', [], $j);
    $req('POST', '/academy/login', ['_token' => $tok($p['body']), '_hp_ts' => $hp(), 'email' => $email, 'password' => 'Secret-12345'], $j);
    return $j;
};
$ja = $login('zz-lk-a@example.test');
$prevUrl = '/academy/portal/certificates/' . (int) $cert['id'] . '/preview';

$anon = $req('GET', $prevUrl);
check('a visitor who is not signed in is sent to the sign-in page', $anon['code'] === 302 && str_contains($anon['loc'], 'academy/login'), $anon['code'] . ' ' . $anon['loc']);

$list = $req('GET', '/academy/portal/certificates', [], $ja);
check('the learner list offers "View a preview (blurred)" for the waiting certificate', $list['code'] === 200 && str_contains($list['body'], 'View a preview (blurred)') && str_contains($list['body'], $prevUrl), (string) $list['code']);

$pv = $req('GET', $prevUrl, [], $ja);
check('the preview page shows the blurred certificate, the "Locked" veil and the steps', $pv['code'] === 200 && str_contains($pv['body'], 'ac-cert-svg--locked') && str_contains($pv['body'], 'Locked') && str_contains($pv['body'], 'Details hidden') && str_contains($pv['body'], 'View it clearly and download the PDF'), (string) $pv['code']);
preg_match('~ac-cert-svg--locked".*?</svg>~s', $pv['body'], $pic);
check('the picture on the preview page holds none of the real details (name, number, score); the real number is nowhere on the page', $pic !== [] && !str_contains(mb_strtoupper($pic[0]), 'ZZ LK PERSON') && !str_contains($pic[0], '>93.7%<') && !str_contains($pv['body'], (string) $cert['code']));
check('the preview page has no download link and no PDF link', !str_contains($pv['body'], '/pdf') && !str_contains($pv['body'], 'Download PDF'));
check('the public check page and PDF do not know the waiting certificate (404)', $req('GET', '/academy/certificate/' . $cert['code'])['code'] === 404 && $req('GET', '/academy/certificate/' . $cert['code'] . '/pdf')['code'] === 404);

$other = $req('GET', '/academy/portal/certificates/' . (int) $certB['id'] . '/preview', [], $ja);
check('another learner\'s certificate cannot be previewed (404)', $other['code'] === 404, (string) $other['code']);

// ---- after the administrator approves it: the clear view opens, the preview goes there
Database::execute('UPDATE academy_certificates SET approved_at = NOW() WHERE id = ?', [(int) $cert['id']]);
$after = $req('GET', $prevUrl, [], $ja);
check('once approved, the preview address sends the learner to the clear certificate page', $after['code'] === 302 && str_contains($after['loc'], 'academy/certificate/' . $cert['code']), $after['code'] . ' ' . $after['loc']);
$clear = $req('GET', '/academy/certificate/' . $cert['code']);
check('once approved, the clear certificate and its PDF open', $clear['code'] === 200 && str_contains($clear['body'], 'genuine certificate') && $req('GET', '/academy/certificate/' . $cert['code'] . '/pdf')['code'] === 200);
$list2 = $req('GET', '/academy/portal/certificates', [], $ja);
check('once approved, the learner list offers Open and Download PDF', $list2['code'] === 200 && str_contains($list2['body'], 'Download PDF'));

foreach ([$ja] as $f) {
    @unlink($f);
}
$cleanup();
$old === null ? Settings::reset('academy_cert_approval') : Settings::set('academy_cert_approval', (string) $old);
Settings::flush();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
