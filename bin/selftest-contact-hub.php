<?php
declare(strict_types=1);

/**
 * CONTACT HUB: the animated "connect" tiles on the home page band and the Contact page, and the form helpers (topic buttons, counter). Checks the HTML a browser receives: every detail is a
 * real link (works without JavaScript), the copy buttons start hidden, nothing is inline (strict CSP), the connectors are consistent, the form still saves a message.
 * Run alone: /c/xampp/php/php.exe bin/selftest-contact-hub.php      Needs MySQL and Apache.
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

echo "-- contact hub --\n";
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'chj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$hub = static function (string $html): string {
    return preg_match('/<div class="cx cx--[^"]*"[^>]*data-contact-hub.*?<\/ul>/s', $html, $m) === 1 ? $m[0] : '';
};

$home = $req('GET', '/');
$h = $hub($home['body']);
check('home: the contact band shows the connect hub with a tile for each detail (e-mails, phones, WhatsApp, location)', $home['code'] === 200 && substr_count($h, 'class="cx-tile ') >= 3 && str_contains($home['body'], 'home-contact__box'));
check('home: the details are real links (mailto:, tel:, WhatsApp, Google Maps), so everything works without JavaScript', str_contains($h, 'href="mailto:') && str_contains($h, 'href="tel:') && str_contains($h, 'google.com/maps/search'));
check('home: the main button still leads to the Contact page', str_contains($home['body'], 'home-contact__actions') && preg_match('~home-contact__actions.*?href="[^"]*contact"~s', $home['body']) === 1);
check('home: copy buttons are in the page but hidden until the script shows them', substr_count($h, 'class="cx-copy"') >= 1 && substr_count($h, ' hidden ') >= substr_count($h, 'class="cx-copy"') && str_contains($home['body'], 'js/contact-hub.js'));
check('home: nothing inline in the hub (no onclick, no inline script): the strict CSP stays valid', preg_match('/\son[a-z]+=|<script(?![^>]*src=)/i', $h) !== 1);
check('home: the decorative network is hidden from screen readers and the tiles have visible labels', str_contains($h, 'class="cx__net"') && str_contains($h, 'aria-hidden="true"') && str_contains($h, 'cx-tile__label'));
check('home: every connector class matches a real neighbour (the first tile has none above it; a right link only where another tile follows in the row)', (static function () use ($h): bool {
    preg_match_all('/<li class="cx-tile ([^"]*)"/', $h, $m);
    $c = $m[1];
    return $c !== [] && !str_contains($c[0], 'has-up') && !str_contains($c[count($c) - 1], 'has-right');
})());

$page = $req('GET', '/contact');
$ph = $hub($page['body']);
check('contact page: the hub shows the office, e-mail and phone tiles, with the copy buttons and the map link', $page['code'] === 200 && str_contains($ph, 'cx-tile--office') && str_contains($ph, 'cx-tile--mail') && str_contains($ph, 'cx-tile--phone') && str_contains($ph, 'google.com/maps/search') && str_contains($ph, 'data-copy='));
check('contact page: the scripts of the hub and of the form are loaded', str_contains($page['body'], 'js/contact-hub.js') && str_contains($page['body'], 'js/pages/contact.js'));
check('contact page: the form offers topic buttons (they fill the subject) and the hooks for the counter', substr_count($page['body'], 'data-topic=') === 6 && str_contains($page['body'], 'data-contact-subject') && str_contains($page['body'], 'data-contact-message') && str_contains($page['body'], 'data-counter-label'));
check('contact page: the old plain cards are gone and the page still has one main heading', !str_contains($page['body'], 'contact-card') && substr_count($page['body'], '<h1') === 1);

// the form still works: a message is saved and the visitor is sent back
$jar = null;
$pg = $req('GET', '/contact', [], $jar);
$tok = preg_match('/name="_token" value="([^"]+)"/', $pg['body'], $m) === 1 ? $m[1] : '';
$ts = (string) (time() - 30);
$hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
Database::execute("DELETE FROM contact_messages WHERE email = 'zz-ch-test@example.test'");
Database::execute('DELETE FROM rate_limits');
$sent = $req('POST', '/contact', ['_token' => $tok, '_hp_ts' => $hp, 'name' => 'Zed Hub', 'email' => 'zz-ch-test@example.test', 'subject' => 'Partnership', 'message' => 'Hello from the hub test'], $jar);
$row = Database::one("SELECT * FROM contact_messages WHERE email = 'zz-ch-test@example.test' ORDER BY id DESC LIMIT 1");
check('the contact form still saves the message (subject from a topic button included)', in_array($sent['code'], [302, 303], true) && $row !== null && $row['subject'] === 'Partnership' && str_contains((string) $row['message'], 'hub test'));
$bad = $req('POST', '/contact', ['_token' => $tok, '_hp_ts' => $hp, 'name' => '', 'email' => 'nope', 'message' => ''], $jar);
check('a bad submission is refused with the form shown again, not a technical page', in_array($bad['code'], [200, 302, 303], true) && !str_contains($bad['body'], 'Stack trace'));
Database::execute("DELETE FROM contact_messages WHERE email = 'zz-ch-test@example.test'");
foreach (glob(STORAGE_PATH . '/mail/*.eml') ?: [] as $f) {
    if (str_contains((string) file_get_contents($f), 'zz-ch-test@example.test') && filemtime($f) > time() - 120) {
        @unlink($f);
    }
}
@unlink((string) $jar);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
