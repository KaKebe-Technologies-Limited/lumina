<?php
declare(strict_types=1);

/**
 * Admin > File check: finds missing and outdated files from app/config/manifest.php. Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-file-check.php
 * Needs the local web server. Puts the manifest back as it found it.
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

echo "-- file check --\n";
$root = dirname(__DIR__);
$mfPath = $root . '/app/config/manifest.php';
$original = is_file($mfPath) ? (string) file_get_contents($mfPath) : null;
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/bin/build-manifest.php') . ' > ' . (PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null'));
$mf = require $mfPath;
check('the manifest lists the site files (hundreds) with a fingerprint each', count($mf['files']) > 300 && preg_match('/^[a-f0-9]{40}$/', (string) reset($mf['files'])) === 1);
check('the manifest lists the files the Ambassadors page needs', isset($mf['files']['app/views/admin/ambassadors/index.php']) && isset($mf['files']['app/controllers/Admin/FileCheckController.php']));

$base = 'http://localhost/' . basename($root);
Database::execute("DELETE FROM users WHERE email = 'zz-fc-admin@example.test'");
$apw = 'File check pass 12';
\App\Models\User::create(['name' => 'ZZ Fc Admin', 'email' => 'zz-fc-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'fcj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
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
$page = $req('GET', '/admin/login');
$req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-fc-admin@example.test', 'password' => $apw]);
$r = $req('GET', '/admin/file-check');
check('admin: File check opens (200) and says every file matches when nothing is wrong', $r['code'] === 200 && str_contains($r['body'], 'Every file matches'), (string) $r['code'] . ' ' . substr(strip_tags($r['body']), 0, 0));

// a file the live site "forgot" and a file with an older version: add two fake entries to the manifest
$mf['files']['app/views/admin/zz-not-uploaded/index.php'] = str_repeat('a', 40);
$mf['files']['app/config/app.php'] = str_repeat('b', 40);
file_put_contents($mfPath, "<?php\nreturn " . var_export($mf, true) . ";\n");
$r = $req('GET', '/admin/file-check');
check('a file that is not on the server is listed as MISSING with its folder', str_contains($r['body'], 'Missing: upload these') && str_contains($r['body'], 'zz-not-uploaded') && str_contains($r['body'], 'app/views/admin/'));
check('a file with another content is listed as DIFFERENT (older version)', str_contains($r['body'], 'Different: an older version') && str_contains($r['body'], 'app/config'));
check('the list to send to the developer is offered', str_contains($r['body'], 'MISSING:') && str_contains($r['body'], 'DIFFERENT:'));

// without a manifest the page explains what to upload
unlink($mfPath);
$r = $req('GET', '/admin/file-check');
check('without the manifest the page says to upload app/config/manifest.php', $r['code'] === 200 && str_contains($r['body'], 'file list is missing'));

if ($original !== null) {
    file_put_contents($mfPath, $original);
}
@unlink($jar);
Database::execute("DELETE FROM users WHERE email = 'zz-fc-admin@example.test'");
Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Fc Admin'");

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
