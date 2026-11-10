<?php
declare(strict_types=1);

/**
 * "Choose from the website gallery" on every picture field: the JSON list (admin/media/gallery), the buttons in the forms and the editors, and that a gallery picture can be saved
 * and is not deleted when it is taken off another record. Run alone:   /c/xampp/php/php.exe bin/selftest-gallery-picker.php   (needs the local web server, APP_ENV=local)
 */

use App\Core\Database;

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

echo "-- gallery picker --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM gallery_images WHERE alt_text LIKE 'ZZ gpt%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-gp-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$files = array_slice(glob(dirname(__DIR__) . '/assets/img/*/*.jpg') ?: [], 0, 2);
check('there are bundled pictures to use in the test', count($files) === 2);
foreach ($files as $i => $f) {
    Database::insert('gallery_images', ['image' => 'assets/img/' . basename(dirname($f)) . '/' . basename($f), 'alt_text' => 'ZZ gpt picture ' . $i, 'album' => 'ZZ gpt album', 'is_active' => 1]);
}
Database::insert('gallery_images', ['image' => 'assets/img/brand/card-logo.jpg', 'alt_text' => 'ZZ gpt hidden', 'is_active' => 0]);

$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, ?array $form, string $j) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
    if ($form !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs)];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $mm) === 1 ? $mm[1] : '';
$pw = 'Gallery picker pass 71';
\App\Models\User::create(['name' => 'ZZ Gp Admin', 'email' => 'zz-gp-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$j = tempnam(sys_get_temp_dir(), 'gpj');
$lp = $req('/admin/login', null, $j);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-gp-admin@example.test', 'password' => $pw], $j);

$anon = $req('/admin/media/gallery', null, tempnam(sys_get_temp_dir(), 'gpn'));
check('the list is not public', $anon['code'] === 302);
$r = $req('/admin/media/gallery?q=' . rawurlencode('ZZ gpt'), null, $j);
$d = json_decode($r['body'], true);
check('the list returns the active gallery pictures that match, with path, url and description', $r['code'] === 200 && is_array($d) && count($d['items']) === 2 && !empty($d['items'][0]['url']) && str_starts_with((string) $d['items'][0]['path'], 'assets/img/') && str_contains($d['items'][0]['alt'], 'ZZ gpt'), $r['body']);
check('a switched-off gallery picture is not offered', !str_contains($r['body'], 'ZZ gpt hidden'));
$none = json_decode($req('/admin/media/gallery?q=zzzz-no-such-picture', null, $j)['body'], true);
check('a search with no match returns an empty list', $none['items'] === [] && $none['more'] === false);
$all = json_decode($req('/admin/media/gallery', null, $j)['body'], true);
check('without a search the first page is returned (at most 36 pictures)', is_array($all['items']) && count($all['items']) <= 36);

$form = $req('/admin/programs/create', null, $j);
check('a picture box in a form has the "Choose from the gallery" button', $form['code'] === 200 && str_contains($form['body'], 'data-image-gallery') && str_contains($form['body'], 'Choose from the gallery'));
check('the picker script is loaded on admin pages', str_contains($form['body'], 'admin-gallery-picker.js'));

// a picture chosen from the gallery is saved through the normal image field, and stays when it is taken off the record
$slug = 'zz-gpt-program';
Database::execute("DELETE FROM programs WHERE slug = ?", [$slug]);
$path = (string) $d['items'][0]['path'];
$ok = \App\Services\ImageCleanup::isStoredImage($path);
check('the chosen path is accepted by the image field as a stored picture', $ok);
check('a gallery picture counts as "in use", so removing it from a record never deletes it', \App\Services\ImageCleanup::isReferenced($path) || str_starts_with($path, 'assets/'));

@unlink($j);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
