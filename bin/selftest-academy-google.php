<?php
declare(strict_types=1);

/**
 * Academy: "Continue with Google" (OpenID Connect). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-google.php
 * Google itself is NOT called: the token request is replaced by GoogleSignIn::$transport. Removes its own rows (zz-lgo-*).
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

use App\Core\Database;
use App\Services\Academy\GoogleSignIn;

Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lgo-%'");
$cfg = ['id' => 'test-client.apps.googleusercontent.com', 'secret' => 'test-secret'];
$b64 = static fn(string $s): string => rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
$jwt = static fn(array $claims): string => $b64('{"alg":"RS256"}') . '.' . $b64((string) json_encode($claims)) . '.sig';
$claims = static fn(string $tag, array $over = []): array => $over + [
    'iss' => 'https://accounts.google.com', 'aud' => $cfg['id'], 'exp' => time() + 3000, 'nonce' => 'N0NCE', 'sub' => 'sub-' . $tag,
    'email' => 'zz-lgo-' . $tag . '@example.test', 'email_verified' => true, 'name' => 'Zed Google ' . $tag,
];

// ------------------------------------------------------------------------------------------------------------- start
check('google: the guarded migration added the google_sub column', (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'academy_learners' AND column_name = 'google_sub'", [], 0) === 1);
check('google: without credentials in .env the button is switched off', !GoogleSignIn::enabled());
$s = GoogleSignIn::begin($cfg);
parse_str((string) parse_url($s['url'], PHP_URL_QUERY), $q);
check('google: the address points to Google with code flow, PKCE (S256), state, nonce and the callback',
    str_starts_with($s['url'], 'https://accounts.google.com/o/oauth2/v2/auth?') && $q['response_type'] === 'code' && $q['code_challenge_method'] === 'S256'
    && $q['client_id'] === $cfg['id'] && $q['state'] === $s['session']['state'] && $q['nonce'] === $s['session']['nonce'] && str_ends_with($q['redirect_uri'], '/academy/google/callback') && str_contains($q['scope'], 'email'));
check('google: the PKCE challenge is the hash of the kept verifier', $q['code_challenge'] === rtrim(strtr(base64_encode(hash('sha256', $s['session']['verifier'], true)), '+/', '-_'), '='));

// ------------------------------------------------------------------------------------------------------------- the ID token checks
check('google: a good token passes', GoogleSignIn::checkClaims($claims('a'), 'N0NCE', $cfg['id']) === null);
check('google: wrong issuer / audience / expired / wrong nonce are refused',
    GoogleSignIn::checkClaims($claims('a', ['iss' => 'https://evil.example']), 'N0NCE', $cfg['id']) === 'iss'
    && GoogleSignIn::checkClaims($claims('a', ['aud' => 'other']), 'N0NCE', $cfg['id']) === 'aud'
    && GoogleSignIn::checkClaims($claims('a', ['exp' => time() - 3600]), 'N0NCE', $cfg['id']) === 'exp'
    && GoogleSignIn::checkClaims($claims('a'), 'DIFFERENT', $cfg['id']) === 'nonce');
check('google: an unverified e-mail address is refused', GoogleSignIn::checkClaims($claims('a', ['email_verified' => false]), 'N0NCE', $cfg['id']) === 'email');

// ------------------------------------------------------------------------------------------------------------- callback
$calls = 0;
$reply = null;
GoogleSignIn::$transport = static function (string $url, array $form) use (&$calls, &$reply): array {
    $calls++;
    return ['status' => 200, 'body' => (string) json_encode($reply)];
};
$sess = ['state' => 'ST4TE', 'nonce' => 'N0NCE', 'verifier' => 'VERIFIER'];
$reply = ['id_token' => $jwt($claims('new'))];

$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'WRONG'], $sess, $cfg);
check('google: a wrong state is refused without calling Google', !$r['ok'] && $calls === 0);
$r = GoogleSignIn::finish(['error' => 'access_denied', 'state' => 'ST4TE'], $sess, $cfg);
check('google: "cancel" on Google\'s page gives a friendly message', !$r['ok'] && str_contains((string) $r['error'], 'cancelled') && $calls === 0);
$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], [], $cfg);
check('google: a callback with nothing kept in the session is refused', !$r['ok'] && $calls === 0);

$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
$l = Database::one("SELECT * FROM academy_learners WHERE email = 'zz-lgo-new@example.test'");
check('google: a new learner is created, confirmed at once, with Google\'s name and a locked random password', $r['ok'] && $r['created'] && $l !== null && $l['email_verified_at'] !== null && $l['name'] === 'Zed Google new' && (int) $l['is_active'] === 1 && strlen((string) $l['password_hash']) > 30 && $l['google_sub'] === 'sub-new');

$r2 = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
check('google: coming back again signs in the same learner (no duplicate)', $r2['ok'] && !$r2['created'] && (int) $r2['learner']['id'] === (int) $l['id'] && (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email = 'zz-lgo-new@example.test'", [], 0) === 1);

// an existing, unconfirmed learner (the "no e-mail arrived" case) is confirmed and linked
$u = Database::insert('academy_learners', ['name' => 'Zed Unconfirmed', 'email' => 'zz-lgo-un@example.test', 'password_hash' => \App\Core\Auth::hashPassword('Secret-12345'), 'email_verified_at' => null, 'is_active' => 1]);
$reply = ['id_token' => $jwt($claims('un'))];
$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
$l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$u]);
check('google: an unconfirmed learner who signs in with Google is confirmed and linked, keeping their own name and password', $r['ok'] && !$r['created'] && $l['email_verified_at'] !== null && $l['google_sub'] === 'sub-un' && $l['name'] === 'Zed Unconfirmed');

// a different Google account with the same e-mail address is refused
$reply = ['id_token' => $jwt($claims('un', ['sub' => 'sub-someone-else']))];
$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
check('google: an address already linked to another Google account is refused', !$r['ok'] && str_contains((string) $r['error'], 'different Google account'));

// a switched-off learner cannot get in this way
Database::update('academy_learners', ['is_active' => 0], 'id = ?', [$u]);
$reply = ['id_token' => $jwt($claims('un'))];
$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
check('google: a switched-off account is refused', !$r['ok'] && str_contains((string) $r['error'], 'switched off'));

// closed registration blocks new accounts only
$was = setting('academy_registration_open', null);
Database::execute("DELETE FROM academy_learners WHERE email = 'zz-lgo-closed@example.test'");
if (\App\Services\Academy\LearnerAuth::registrationOpen()) {
    \App\Core\Settings::set('academy_registration_open', '0');
    $reply = ['id_token' => $jwt($claims('closed'))];
    $r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
    check('google: with sign-ups closed, a new Google user is refused', !$r['ok'] && (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email = 'zz-lgo-closed@example.test'", [], 0) === 0);
    \App\Core\Settings::set('academy_registration_open', $was === null ? '1' : (string) $was);
}

// a failed token exchange
$calls = 0;
GoogleSignIn::$transport = static fn(string $url, array $form): array => ['status' => 400, 'body' => '{"error":"invalid_grant"}'];
$r = GoogleSignIn::finish(['code' => 'c', 'state' => 'ST4TE'], $sess, $cfg);
check('google: if Google refuses the code the learner gets a clear message and no account', !$r['ok'] && $r['learner'] === null);
GoogleSignIn::$transport = null;

// ------------------------------------------------------------------------------------------------------------- the pages
$root = dirname(__DIR__);
check('google: the routes and both pages are wired', str_contains((string) file_get_contents($root . '/app/routes/web_academy.php'), "'/academy/google/callback'")
    && str_contains((string) file_get_contents($root . '/app/views/web/academy/login.php'), 'ac-google') && str_contains((string) file_get_contents($root . '/app/views/web/academy/register.php'), 'ac-google'));

Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lgo-%'");

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'All Google sign-in checks passed.' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
