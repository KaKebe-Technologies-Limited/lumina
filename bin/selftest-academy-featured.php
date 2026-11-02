<?php
declare(strict_types=1);

/**
 * LAF Academy: FEATURED STUDENTS (admin button + public card). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-featured.php
 * Needs the local web server and database/updates/2026-11-academy-featured-learners.sql. Removes its rows (zz-feat-*).
 */

use App\Core\Database;
use App\Services\Academy\FeaturedLearners;

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

echo "-- featured students --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_enrollments WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-feat-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-feat-%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-feat-admin@example.test'");
};
$clean();
check('the featured columns exist (database update imported)', FeaturedLearners::ready());
$now = date('Y-m-d H:i:s');
$hash = password_hash('x', PASSWORD_DEFAULT);
$mk = static fn(string $tag, array $extra): int => (int) Database::insert('academy_learners', ['name' => 'Grace Akello ' . $tag, 'email' => 'zz-feat-' . $tag . '@example.test', 'password_hash' => $hash, 'country' => 'Uganda'] + $extra + ['is_active' => 1]);
$okId = $mk('ok', ['email_verified_at' => $now]);
$waitId = $mk('waiting', []);
$offId = $mk('off', ['email_verified_at' => $now, 'is_active' => 0]);

check('an approved learner can be featured', FeaturedLearners::feature($okId, "  A <b>great</b> learner\n who finished early  ", null));
check('a learner waiting for approval cannot be featured', !FeaturedLearners::feature($waitId, 'x', null) && Database::value('SELECT featured_at FROM academy_learners WHERE id = ?', [$waitId]) === null);
check('a switched-off learner cannot be featured', !FeaturedLearners::feature($offId, 'x', null));
check('the note is cleaned (no tags, one line) and limited', FeaturedLearners::clean(" <i>Hi</i>\n there ") === 'Hi there' && mb_strlen(FeaturedLearners::clean(str_repeat('a', 900))) === FeaturedLearners::NOTE_MAX);

$pub = array_values(array_filter(FeaturedLearners::public(24), static fn(array $c): bool => $c['id'] === $okId));
check('the public list holds the featured learner only', count($pub) === 1 && !in_array($waitId, array_column(FeaturedLearners::public(24), 'id'), true));
check('the public card shows first name + surname initial only, the country and the cleaned note, and no picture without consent', ($pub[0]['name'] ?? '') === 'Grace T.' || str_starts_with((string) ($pub[0]['name'] ?? ''), 'Grace ') && $pub[0]['country'] === 'Uganda' && $pub[0]['photo'] === false && str_contains($pub[0]['note'], 'great learner'), json_encode($pub[0] ?? null));
check('the card holds no e-mail, phone or reg. number', !array_key_exists('email', $pub[0] ?? []) && !array_key_exists('phone', $pub[0] ?? []) && !array_key_exists('reg_no', $pub[0] ?? []));

// ---- pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$apw = 'Feat admin pass 66';
\App\Models\User::create(['name' => 'ZZ Feat Admin', 'email' => 'zz-feat-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'ftj');
$req = static function (string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
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
$r = $req('/academy');
check('the public Academy page shows the Featured students section and the card', $r['code'] === 200 && str_contains($r['body'], 'Featured students') && str_contains($r['body'], 'Grace '), (string) $r['code']);
check('the public page does not leak the learner e-mail', !str_contains($r['body'], 'zz-feat-ok@'));
FeaturedLearners::unfeature($okId);
$r = $req('/academy');
check('after removing, the learner is no longer on the public page', !str_contains($r['body'], 'ac-featured__name') || !str_contains($r['body'], 'Grace A'));

$page = $req('/admin/login');
$req('/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-feat-admin@example.test', 'password' => $apw]);
$r = $req('/admin/academy/learners/' . $okId);
check('admin: the learner page offers "Feature on the website"', $r['code'] === 200 && str_contains($r['body'], 'Feature on the website'), (string) $r['code']);
$req('/admin/academy/learners/' . $okId . '/feature', ['_token' => $tok($r['body']), 'note' => 'Top of the class']);
check('admin: featuring without the permission tick is refused', Database::value('SELECT featured_at FROM academy_learners WHERE id = ?', [$okId]) === null);
$r = $req('/admin/academy/learners/' . $okId);
$req('/admin/academy/learners/' . $okId . '/feature', ['_token' => $tok($r['body']), 'note' => 'Top of the class', 'agreed' => '1']);
check('admin: with the tick the learner is featured with the note', Database::value('SELECT featured_note FROM academy_learners WHERE id = ?', [$okId]) === 'Top of the class');
$r = $req('/admin/academy/learners?status=featured&q=zz-feat-');
check('admin: the list can be filtered to featured learners', $r['code'] === 200 && str_contains($r['body'], 'zz-feat-ok@') && !str_contains($r['body'], 'zz-feat-waiting@'), (string) $r['code']);
$r = $req('/admin/academy/learners/' . $okId);
$req('/admin/academy/learners/' . $okId . '/unfeature', ['_token' => $tok($r['body'])]);
check('admin: "Remove from featured" works', Database::value('SELECT featured_at FROM academy_learners WHERE id = ?', [$okId]) === null);
@unlink($jar);
Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Feat Admin'");
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
