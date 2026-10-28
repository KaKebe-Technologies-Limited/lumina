<?php
declare(strict_types=1);

/**
 * Academy: confirmation e-mail sent by an administrator (delivery record) and the learner portrait. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-mail-portrait.php
 * Removes its own rows (zz-lmp-*) and files.
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
use App\Services\Academy\AcademyMail;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerPortrait;

Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lmp-%'");
$mk = static fn(string $tag, bool $verified): int => (int) Database::insert('academy_learners', [
    'name' => 'Zed Lmp ' . $tag, 'email' => 'zz-lmp-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'),
    'country' => 'Uganda', 'email_verified_at' => $verified ? date('Y-m-d H:i:s') : null, 'is_active' => 1,
]);
$a = $mk('a', false);
$b = $mk('b', true);

check('delivery record: the columns exist after the schema migration', LearnerAuth::mailColumns() && LearnerPortrait::ready());

// ------------------------------------------------------------------------------------------------------------- confirmation e-mail
$r = LearnerAuth::sendConfirmation($a);
$row = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$a]);
check('confirmation: an administrator can send it to an unconfirmed learner', $r['ok'] === true && $r['error'] === null, (string) $r['error']);
check('confirmation: the delivery is recorded (sent time set, no error)', $row['mail_sent_at'] !== null && $row['mail_error'] === null);
check('confirmation: the link is valid for 7 days', $row['token_kind'] === 'verify' && strtotime((string) $row['token_expires_at']) > time() + 6 * 86400);
$again = LearnerAuth::sendConfirmation($a);
check('confirmation: it can be sent again at once (no per-address limit for the administrator)', $again['ok'] === true);
$done = LearnerAuth::sendConfirmation($b);
check('confirmation: a learner who already confirmed is refused with a clear message', $done['ok'] === false && str_contains((string) $done['error'], 'already confirmed'));
LearnerAuth::recordMail($a, false);
$bad = Database::one('SELECT mail_sent_at, mail_error FROM academy_learners WHERE id = ?', [$a]);
check('confirmation: a failed delivery is recorded with its reason', $bad['mail_sent_at'] === null && $bad['mail_error'] !== null && $bad['mail_error'] !== '');

// ------------------------------------------------------------------------------------------------------------- portrait
$src = dirname(__DIR__) . '/assets/img/site/photo-02.jpg';
$tmp = sys_get_temp_dir() . '/zz-lmp-' . bin2hex(random_bytes(4)) . '.jpg';
copy($src, $tmp);
$file = ['name' => 'me.jpg', 'type' => 'image/jpeg', 'tmp_name' => $tmp, 'error' => 0, 'size' => filesize($tmp)];
$s = LearnerPortrait::save($a, $file, false);
check('portrait: a JPG is accepted', $s['ok'] === true, (string) $s['error']);
$p = LearnerPortrait::of($a);
$abs = STORAGE_PATH . '/private/' . (string) $p['photo_path'];
check('portrait: stored privately under storage/private/academy/learners', str_starts_with((string) $p['photo_path'], 'academy/learners/') && is_file($abs));
check('portrait: private until the learner agrees (not public, not in the showcase)', !LearnerPortrait::isPublic($a) && !in_array($a, array_column(LearnerPortrait::showcase(48), 'id'), true));
check('portrait: an administrator can load the file', LearnerPortrait::download($a) !== null);
LearnerPortrait::setConsent($a, true);
$show = array_values(array_filter(LearnerPortrait::showcase(48), static fn(array $x): bool => $x['id'] === $a));
check('portrait: with consent it is public and shows the first name only', LearnerPortrait::isPublic($a) && ($show[0]['first'] ?? '') === 'Zed' && ($show[0]['country'] ?? '') === 'Uganda');
Database::update('academy_learners', ['is_active' => 0], 'id = ?', [$a]);
check('portrait: a switched-off account is never shown', !LearnerPortrait::isPublic($a));
Database::update('academy_learners', ['is_active' => 1], 'id = ?', [$a]);
$bogus = ['name' => 'x.jpg', 'type' => 'image/jpeg', 'tmp_name' => $tmp . '.nope', 'error' => 0, 'size' => 10];
file_put_contents($tmp . '.txt', 'not a picture');
$bogus['tmp_name'] = $tmp . '.txt';
$bad = LearnerPortrait::save($b, $bogus, true);
check('portrait: a file that is not a picture is refused and nothing is stored', $bad['ok'] === false && LearnerPortrait::of($b)['photo_path'] === null);
$s2 = LearnerPortrait::save($a, $file, true);
$abs2 = STORAGE_PATH . '/private/' . (string) LearnerPortrait::of($a)['photo_path'];
check('portrait: replacing it removes the old file', $s2['ok'] === true && !is_file($abs) && is_file($abs2));
LearnerPortrait::remove($a);
check('portrait: removing it deletes the file and the consent', !is_file($abs2) && LearnerPortrait::of($a) === ['photo_path' => null, 'photo_consent_at' => null]);

// ------------------------------------------------------------------------------------------------------------- over HTTP (admin screens, learner account, public page)
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null, array $files = []) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lmj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        $post = $form;
        foreach ($files as $k => $f) {
            $post[$k] = new CURLFile($f[0], $f[1], $f[2]);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $files === [] ? http_build_query($form) : $post);
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'head' => $head, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from, array $files = []) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $ts = (string) (time() - 30);                                                        // a form open for 30 seconds passes the honeypot time trap
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp], $jar, $files);
};
$mailText = static function (string $file): string {
    $c = (string) file_get_contents($file);
    $out = $c;
    if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $c, $parts) > 0) {
        foreach ($parts[1] as $b) {
            $out .= "\n" . (string) base64_decode(preg_replace('/\s+/', '', $b));
        }
    }
    return $out;
};
Database::execute("DELETE FROM users WHERE email LIKE 'zz-lmp-%'");
Database::execute('DELETE FROM rate_limits');
$pwA = 'Admin lmp pass 91';
$pwE = 'Editor lmp pass 94';
$mkU = static fn(string $name, string $email, string $role, string $pw): int => (int) \App\Models\User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$mkU('ZZ Lmp Admin', 'zz-lmp-admin@example.test', 'admin', $pwA);
$mkU('ZZ Lmp Editor', 'zz-lmp-editor@example.test', 'editor', $pwE);
$jA = $jE = $jL = null;
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    $r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
    return in_array($r['code'], [302, 303], true);
};
check('http: the test administrator and editor can sign in', $login('zz-lmp-admin@example.test', $pwA, $jA) && $login('zz-lmp-editor@example.test', $pwE, $jE));

$c = $mk('c', false);                      // waiting, never e-mailed
$d = $mk('d', false);
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$list = $req('GET', '/admin/academy/learners', [], $jA);
check('http: the list offers to send confirmation e-mails and to approve everyone at once', $list['code'] === 200 && str_contains($list['body'], 'Send confirmation e-mails') && str_contains($list['body'], 'Approve all ') && str_contains($list['body'], 'No e-mail sent yet'));
// the Status filter matches the badges: Active = confirmed and on, E-mail not confirmed = on but unconfirmed, Switched off = off
Database::update('academy_learners', ['is_active' => 0], 'id = ?', [$d]);
$fActive = $req('GET', '/admin/academy/learners?status=active&q=zz-lmp', [], $jA)['body'];
$fUnver = $req('GET', '/admin/academy/learners?status=unverified&q=zz-lmp', [], $jA)['body'];
$fOff = $req('GET', '/admin/academy/learners?status=inactive&q=zz-lmp', [], $jA)['body'];
check('http: the Status filter "Active" lists only confirmed, switched-on learners (no unconfirmed, no switched-off ones)', str_contains($fActive, 'zz-lmp-b@') && !str_contains($fActive, 'zz-lmp-c@') && !str_contains($fActive, 'zz-lmp-d@'));
check('http: "E-mail not confirmed" lists only switched-on learners who have not confirmed', str_contains($fUnver, 'zz-lmp-c@') && !str_contains($fUnver, 'zz-lmp-b@') && !str_contains($fUnver, 'zz-lmp-d@'));
check('http: "Switched off" lists only the switched-off learner', str_contains($fOff, 'zz-lmp-d@') && !str_contains($fOff, 'zz-lmp-b@') && !str_contains($fOff, 'zz-lmp-c@'));
Database::update('academy_learners', ['is_active' => 1], 'id = ?', [$d]);
$one = $post('/admin/academy/learners/' . $c . '/send-confirmation', [], $jA, '/admin/academy/learners/' . $c);
$newMail = array_values(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore));
check('http: the learner page button sends one confirmation e-mail to that learner', in_array($one['code'], [302, 303], true) && count($newMail) === 1 && str_contains($mailText($newMail[0]), 'zz-lmp-c@example.test') && str_contains($mailText($newMail[0]), '/academy/verify/'));
$page = $req('GET', '/admin/academy/learners/' . $c, [], $jA);
check('http: the learner page then shows when it was sent', str_contains($page['body'], 'Confirmation e-mail') && str_contains($page['body'], 'Send confirmation e-mail'));
$bulk = $post('/admin/academy/learners-send-confirmations', [], $jA, '/admin/academy/learners');
check('http: the bulk button e-mails the learners who never received one', in_array($bulk['code'], [302, 303], true) && Database::value('SELECT mail_sent_at FROM academy_learners WHERE id = ?', [$d]) !== null);
check('http: an editor cannot use the buttons (403)', $post('/admin/academy/learners-send-confirmations', [], $jE, '/admin')['code'] === 403 && $post('/admin/academy/learners-confirm-all', [], $jE, '/admin')['code'] === 403);
$all = $post('/admin/academy/learners-confirm-all', [], $jA, '/admin/academy/learners');
check('http: "Mark all as confirmed" confirms every waiting learner', in_array($all['code'], [302, 303], true) && (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-lmp-%' AND email_verified_at IS NULL", [], 1) === 0);

// learner: my account, upload, public page
$pwL = 'Learner lmp pass 77';
Database::update('academy_learners', ['password_hash' => Auth::hashPassword($pwL)], 'id = ?', [$c]);
$lp = $req('GET', '/academy/login', [], $jL);
$ts0 = (string) (time() - 30);
$li = $req('POST', '/academy/login', ['_token' => $tok($lp['body']), '_hp_ts' => $ts0 . '.' . hash_hmac('sha256', $ts0, \App\Core\Config::appKey()), 'email' => 'zz-lmp-c@example.test', 'password' => $pwL], $jL);
check('http: the learner can sign in', in_array($li['code'], [302, 303], true));
$acc = $req('GET', '/academy/account', [], $jL);
check('http: "My account" has a picture card with a consent box', $acc['code'] === 200 && str_contains($acc['body'], 'My picture') && str_contains($acc['body'], 'name="photo"') && str_contains($acc['body'], 'name="consent"'));
$up = $post('/academy/account/photo', ['consent' => '1'], $jL, '/academy/account', ['photo' => [$src, 'image/jpeg', 'me.jpg']]);
check('http: the learner uploads a picture and agrees to show it', in_array($up['code'], [302, 303], true) && LearnerPortrait::isPublic($c));
$mine = $req('GET', '/academy/account/photo', [], $jL);
check('http: the learner sees their picture (private, not cached)', $mine['code'] === 200 && stripos($mine['head'], 'image/jpeg') !== false && stripos($mine['head'], 'no-store') !== false);
check('http: another visitor cannot open the private route', $req('GET', '/academy/account/photo')['code'] === 404);
$pub = $req('GET', '/academy/learners/' . $c . '/photo');
check('http: with consent the picture is public', $pub['code'] === 200 && stripos($pub['head'], 'image/jpeg') !== false);
$home = $req('GET', '/academy');
check('http: the Academy page shows "Meet some of our learners" with the first name', str_contains($home['body'], 'Meet some of our learners') && str_contains($home['body'], '/academy/learners/' . $c . '/photo') && str_contains($home['body'], 'Zed'));
check('http: an administrator opens the learner picture and sees it on the learner page', $req('GET', '/admin/academy/learners/' . $c . '/photo', [], $jA)['code'] === 200 && str_contains($req('GET', '/admin/academy/learners/' . $c, [], $jA)['body'], 'aac-portrait__img'));
$off = $post('/academy/account/photo', [], $jL, '/academy/account');
check('http: unticking the box makes it private again (404 for visitors)', in_array($off['code'], [302, 303], true) && $req('GET', '/academy/learners/' . $c . '/photo')['code'] === 404);
$rm = $post('/academy/account/photo/remove', [], $jL, '/academy/account');
check('http: the learner can remove the picture', in_array($rm['code'], [302, 303], true) && LearnerPortrait::of($c)['photo_path'] === null);

Database::execute("DELETE FROM users WHERE email LIKE 'zz-lmp-%'");
Database::execute('DELETE FROM rate_limits');
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
    if (str_contains($mailText($f), 'zz-lmp-')) {
        @unlink($f);
    }
}

@unlink($tmp);
@unlink($tmp . '.txt');
Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lmp-%'");
check('cleanup: no zz-lmp rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-lmp-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
