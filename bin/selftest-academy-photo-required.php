<?php
declare(strict_types=1);

/**
 * LAF Academy: a PICTURE IS REQUIRED to create an account (Settings > LAF Academy). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-photo-required.php
 * Needs the local web server. Removes its rows (zz-pr-*) and puts the setting back.
 */

use App\Core\Database;
use App\Core\Settings;
use App\Services\Academy\LearnerPortrait;

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

echo "-- academy: picture required at sign-up --\n";
$keys = ['academy_register_photo_required', 'academy_signup_mode', 'academy_registration_open', 'academy_signup_fee', 'academy_enabled'];
$before = [];
foreach ($keys as $k) {
    $before[$k] = Settings::isCustomised($k) ? Settings::get($k) : null;
}
$restore = static function () use ($before): void {
    foreach ($before as $k => $v) {
        $v === null ? Settings::reset($k) : Settings::set($k, (string) $v);
    }
    Settings::flush();
};
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-pr-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
Settings::set('academy_enabled', '1');
Settings::set('academy_registration_open', '1');
Settings::set('academy_signup_mode', 'approval');
Settings::reset('academy_signup_fee');
Settings::reset('academy_register_photo_required');
Settings::flush();

check('the picture is required by default (pictures can be stored)', LearnerPortrait::ready() && LearnerPortrait::requiredAtSignup());
check('problem(): no file is a problem, a missing path is a problem', LearnerPortrait::problem(null) !== '' && LearnerPortrait::problem(['tmp_name' => '/no/such/file', 'error' => 0]) !== '');

$base = 'http://localhost/' . basename(dirname(__DIR__));
$jar = tempnam(sys_get_temp_dir(), 'prj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $form);       // an array = multipart (the picture)
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$register = static function (string $tag, ?array $photo) use ($req, $tok): array {
    $page = $req('GET', '/academy/register');
    $ts = (string) (time() - 30);
    $form = ['_token' => $tok($page['body']), '_hp_ts' => $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey()), 'name' => 'Zed Pr ' . $tag, 'email' => 'zz-pr-' . $tag . '@example.test', 'password' => 'Sentence-for-test-77',
        'phone' => '+256700000' . random_int(100, 999), 'country' => 'Uganda', 'whatsapp' => '+256700000' . random_int(100, 999), 'agree' => '1'];
    if ($photo !== null) {
        $form['photo'] = new CURLFile($photo[0], $photo[1], $photo[2]);
    }
    return ['page' => $page, 'res' => $req('POST', '/academy/register', $form)];
};
$exists = static fn(string $tag): bool => Database::value('SELECT 1 FROM academy_learners WHERE email = ?', ['zz-pr-' . $tag . '@example.test']) !== null;

$page = $req('GET', '/academy/register');
check('the sign-up form marks the picture as required (asterisk and required attribute)', $page['code'] === 200 && str_contains($page['body'], 'name="photo"') && str_contains($page['body'], 'aria-required="true"') && str_contains($page['body'], 'A picture is required to create your account'), (string) $page['code']);

$good = [dirname(__DIR__) . '/assets/img/site/photo-02.jpg', 'image/jpeg', 'me.jpg'];
$tiny = [dirname(__DIR__) . '/assets/img/brand/logo-64.png', 'image/png', 'small.png'];

$r = $register('none', null);
check('no picture: the sign-up is refused (back to the form) and NO account is made', $r['res']['code'] === 302 && str_contains($r['res']['loc'], 'academy/register') && !$exists('none'), $r['res']['code'] . ' ' . $r['res']['loc']);
$form = $req('GET', '/academy/register');
check('...and the form tells the person to add a picture', str_contains($form['body'], 'Please add your picture'));
$r = $register('tiny', $tiny);
check('a picture that is too small is refused and NO account is made', !$exists('tiny'));
$r = $register('good', $good);
check('with a good picture the account is created', $exists('good'), $r['res']['code'] . ' ' . $r['res']['loc']);
$row = Database::one("SELECT id, photo_path, photo_consent_at FROM academy_learners WHERE email = 'zz-pr-good@example.test'");
check('...and the picture is stored (private, not shown publicly: no consent)', $row !== null && (string) $row['photo_path'] !== '' && $row['photo_consent_at'] === null);

Settings::set('academy_register_photo_required', '0');
Settings::flush();
check('switched off: the picture is optional again', !LearnerPortrait::requiredAtSignup());
$r = $register('opt', null);
check('switched off: an account can be made without a picture', $exists('opt'), $r['res']['code'] . ' ' . $r['res']['loc']);
$page = $req('GET', '/academy/register');
check('switched off: the form says (optional)', str_contains($page['body'], 'Your picture (optional)'));

foreach (Database::all("SELECT photo_path FROM academy_learners WHERE email LIKE 'zz-pr-%@example.test' AND photo_path IS NOT NULL") as $x) {
    @unlink(STORAGE_PATH . '/private/' . $x['photo_path']);
}
@unlink($jar);
$clean();
$restore();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
