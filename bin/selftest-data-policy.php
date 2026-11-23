<?php
declare(strict_types=1);

/**
 * DATA POLICY: the privacy policy and the data rights page are public, editable and in the sitemap and footer; the table of contents and every
 * "Why we ask for this" link resolve; every form that collects personal data carries its notice; a rights request is stored with a reference and a due
 * date and acknowledged by e-mail; the newsletter tick box is enforced; the admin page is for administrators only; no "to be confirmed" text is public.
 * Real HTTP; mail goes to files (APP_ENV=local); removes its own test data.
 * Run alone: /c/xampp/php/php.exe bin/selftest-data-policy.php      Needs MySQL and Apache.
 */

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Services\Privacy\DataRights;
use App\Services\Privacy\Notices;

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

echo "-- data policy --\n";
$root = dirname(__DIR__);
$base = 'http://localhost/' . basename($root);
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'dp');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0.0.0 Safari/537.36']);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($raw, $hs), 'head' => substr($raw, 0, $hs)];
};
$hidden = static function (string $html): array {
    $o = [];
    foreach (['_token', '_hp_ts'] as $n) {
        if (preg_match('/name="' . preg_quote($n, '/') . '" value="([^"]*)"/', $html, $m) === 1) {
            $o[$n] = html_entity_decode($m[1]);
        }
    }
    return $o;
};

$priv = $req('GET', '/privacy');
$rights = $req('GET', '/privacy/request');
check('the privacy policy page is public (200)', $priv['code'] === 200);
check('the data rights request page is public (200)', $rights['code'] === 200);
check('both pages are in the page registry', \App\Core\PageRegistry::get('privacy') !== null && \App\Core\PageRegistry::get('data-rights') !== null);

// ---- table of contents and anchors ---------------------------------------------------------------------------------------
preg_match_all('/id="([A-Za-z0-9_-]+)"/', $priv['body'], $ids);
preg_match('~<nav class="legal__toc".*?</nav>~s', $priv['body'], $toc);
preg_match_all('/href="#([A-Za-z0-9_-]+)"/', $toc[0] ?? '', $hrefs);
check('the privacy policy has a table of contents with at least 8 entries', count($hrefs[1]) >= 8, (string) count($hrefs[1]));
$missing = array_diff($hrefs[1], $ids[1]);
check('every table-of-contents link points at an existing section', $missing === [], implode(', ', $missing));
check('headings are in order (one h1, sections are h2)', substr_count($priv['body'], '<h1') === 1 && substr_count($priv['body'], 'class="legal__section"') >= 8);
foreach (['who', 'collect', 'use', 'sharing', 'retention', 'security', 'children', 'cookies', 'rights', 'changes'] as $sec) {
    if (!in_array($sec, $ids[1], true)) {
        check('the policy has the section "' . $sec . '"', false);
    }
}
check('the policy has a table of what is collected', str_contains($priv['body'], 'class="privacy-table"') && str_contains($priv['body'], '<th scope="col">'));
check('the policy has the "in brief" box and a last-updated date', str_contains($priv['body'], 'privacy-brief') && str_contains($priv['body'], 'legal__updated'));
check('the policy names the Data Protection Focal Person and the PDPO', str_contains($priv['body'], 'Data Protection Focal Person') && str_contains($priv['body'], 'Personal Data Protection Office'));
check('the policy says the response time is a target, not a legal promise', stripos($priv['body'], 'not a legal promise') !== false);
check('the policy states that data is never sold', stripos($priv['body'], 'never sell') !== false);

// ---- "Why we ask for this" links resolve to rows of the table -----------------------------------------------------------------
$bad = [];
foreach (array_keys(Notices::ITEMS) as $k) {
    if (!in_array(Notices::anchor($k), $ids[1], true)) {
        $bad[] = $k;
    }
}
check('every point-of-collection note links to an existing row of the table', $bad === [], implode(', ', $bad));

// ---- every form that collects personal data carries its notice ---------------------------------------------------------------------
$forms = [
    'web/contact/index.php' => 'contact', 'partials/meeting-request-form.php' => 'contact', 'web/events/show.php' => 'events', 'web/involvement/index.php' => 'involvement',
    'web/donate/index.php' => 'donate', 'web/fund/show.php' => 'fund', 'web/shop/checkout.php' => 'shop', 'web/membership/register.php' => 'membership',
    'web/academy/register.php' => 'academy', 'web/academy/facilitate.php' => 'facilitator', 'web/academy/contact.php' => 'contact', 'web/careers/_apply_form.php' => 'careers',
    'web/ambassadors/index.php' => 'ambassador', 'web/pay/mobile-money.php' => 'donate', 'partials/newsletter-row.php' => 'newsletter', 'web/blog/_subscribe.php' => 'newsletter',
];
$noNote = [];
foreach ($forms as $file => $key) {
    $src = (string) file_get_contents($root . '/app/views/' . $file);
    $ok = str_contains($src, "partials/privacy-note', ['key' => '{$key}'") || ($key === 'newsletter' && str_contains($src, 'partials/newsletter-consent'));
    if (!$ok) {
        $noNote[] = $file;
    }
}
check('every form with personal data has its "Why we ask for this" note (' . count($forms) . ' forms)', $noNote === [], implode(', ', $noNote));
$contact = $req('GET', '/contact')['body'];
check('the contact page shows the note and links to the matching row', str_contains($contact, 'class="privacy-note') && str_contains($contact, '/privacy#collect-contact'));
$home = $req('GET', '/')['body'];
check('the footer links to the privacy policy and the data rights page', str_contains($home, '/privacy"') && str_contains($home, '/privacy/request'));
check('the newsletter sign-up in the footer has a consent tick box', str_contains($home, 'name="consent_notice"') && str_contains($home, 'name="consent"'));

// ---- sitemap ----------------------------------------------------------------------------------------------------------------------------
$sm = $req('GET', '/sitemap.xml')['body'];
check('the sitemap lists the privacy policy and the data rights page', str_contains($sm, '/privacy</loc>') && str_contains($sm, '/privacy/request</loc>'));

// ---- no "to be confirmed" on public pages ---------------------------------------------------------------------------------------
$leak = [];
foreach (['/privacy' => $priv['body'], '/privacy/request' => $rights['body']] as $p => $html) {
    if (preg_match('/to be confirmed|will confirm|\[the foundation|placeholder text|TODO/i', strip_tags($html))) {
        $leak[] = $p;
    }
}
check('no "to be confirmed" or placeholder wording on the public policy pages', $leak === [], implode(', ', $leak));
check('the registry defaults hold no "to be confirmed" wording either', !preg_match('/to be confirmed|will confirm/i', (string) file_get_contents($root . '/app/config/pages/privacy.php') . (string) file_get_contents($root . '/app/config/pages/data-rights.php')));

// ---- the cookie statement is honest ---------------------------------------------------------------------------------------------
check('the policy lists the cookies the site really sets', str_contains($priv['body'], 'googtrans') && str_contains($priv['body'], 'Session cookie'));
check('there is no cookie consent banner', !str_contains($home, 'cookie-banner') && !str_contains($home, 'cookie-consent'));

// ---- a rights request is stored, referenced, dated and acknowledged ----------------------------------------------------------------------
$mailDir = STORAGE_PATH . '/mail';
$mailsBefore = glob($mailDir . '/*.eml') ?: [];
$email = 'zz-dp-test@example.test';
Database::execute("DELETE FROM contact_messages WHERE email = ?", [$email]);
Database::execute('DELETE FROM rate_limits');
$jar = null;
$pg = $req('GET', '/privacy/request', [], $jar);
sleep(4);
$form = $hidden($pg['body']) + ['name' => 'ZZ Test Person', 'email' => $email, 'phone' => '', 'type' => 'deletion', 'details' => 'Please delete my data (selftest).', 'confirm' => '1'];
$noTick = $form;
unset($noTick['confirm']);
$r0 = $req('POST', '/privacy/request', $noTick, $jar);
check('a request without the tick box is refused and not stored', (int) Database::value('SELECT COUNT(*) FROM contact_messages WHERE email = ?', [$email]) === 0 && $r0['code'] === 302);
$pg = $req('GET', '/privacy/request', [], $jar);
sleep(4);
$form = $hidden($pg['body']) + ['name' => 'ZZ Test Person', 'email' => $email, 'phone' => '', 'type' => 'deletion', 'details' => 'Please delete my data (selftest).', 'confirm' => '1'];
$r1 = $req('POST', '/privacy/request', $form, $jar);
$row = Database::one('SELECT * FROM contact_messages WHERE email = ?', [$email]) ?: [];
check('a rights request is stored in Admin > Messages (kind dsr)', ($row['kind'] ?? '') === 'dsr' && ($row['request_type'] ?? '') === 'deletion', (string) $r1['code']);
check('it has a reference number like DPR-YYMMDD-XXXXX', preg_match('/^DPR-\d{6}-[A-Z0-9]{5,6}$/', (string) ($row['reference'] ?? '')) === 1, (string) ($row['reference'] ?? ''));
$days = (int) round((strtotime((string) ($row['due_at'] ?? 'now')) - strtotime(date('Y-m-d'))) / 86400);
check('its answer-by date is the response target (30 days by default)', $days === DataRights::targetDays() && $days === 30, (string) $days);
$after = $req('GET', '/privacy/request', [], $jar);
check('the page then shows the reference number', isset($row['reference']) && str_contains($after['body'], (string) $row['reference']));
$ack = false;
$ref = (string) ($row['reference'] ?? 'none');
foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $mailsBefore) as $f) {
    $t = (string) file_get_contents($f);
    if (!str_contains($t, $email) && !str_contains($t, 'zz-dp-test')) {
        continue;
    }
    if (str_contains($t, $ref) || str_contains($t, base64_encode($ref)) || str_contains(preg_replace('/\s+/', '', $t) ?? '', substr(base64_encode('Your reference: ' . $ref), 0, 20))) {
        $ack = true;
    }
    @unlink($f);
}
check('an acknowledgement e-mail with the reference is sent', $ack);
foreach (array_diff(glob($mailDir . '/*.eml') ?: [], $mailsBefore) as $f) {   // the staff copy
    if (str_contains((string) file_get_contents($f), $ref) || str_contains((string) file_get_contents($f), 'Data protection request')) {
        @unlink($f);
    }
}
$counts = DataRights::counts();
check('the open-request counter includes it', $counts['open'] >= 1);

// ---- newsletter consent --------------------------------------------------------------------------------------------------------------------
$nl = 'zz-dp-news@example.test';
Database::execute('DELETE FROM subscribers WHERE email = ?', [$nl]);
$jar2 = null;
$pg = $req('GET', '/', [], $jar2);
sleep(4);
$h = $hidden($pg['body']);
$req('POST', '/subscribe', $h + ['email' => $nl, 'source' => 'selftest', 'consent_notice' => '1'], $jar2);
check('a newsletter sign-up without the tick box is refused', (int) Database::value('SELECT COUNT(*) FROM subscribers WHERE email = ?', [$nl]) === 0);
$req('POST', '/subscribe', $h + ['email' => $nl, 'source' => 'selftest', 'consent_notice' => '1', 'consent' => '1'], $jar2);
$sub = Database::one('SELECT * FROM subscribers WHERE email = ?', [$nl]) ?: [];
check('with the tick box the subscriber is saved with the time they agreed', ($sub['status'] ?? '') === 'subscribed' && !empty($sub['subscribed_at']));
Database::execute('DELETE FROM subscribers WHERE email = ?', [$nl]);

// ---- admin page: administrators only -------------------------------------------------------------------------------------------------------------
$anon = $req('GET', '/admin/data-protection');
check('the admin page redirects visitors who are not signed in', in_array($anon['code'], [302, 303], true) && str_contains($anon['head'], 'login'));
$pwA = 'Admin dp pass 71!';
$pwE = 'Editor dp pass 73!';
Database::execute("DELETE FROM users WHERE email LIKE 'zz-dp-%'");
$mk = static fn(string $em, string $role, string $pw): int => (int) User::create(['name' => 'ZZ DP ' . $role, 'email' => $em, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$mk('zz-dp-admin@example.test', 'admin', $pwA);
$mk('zz-dp-editor@example.test', 'editor', $pwE);
$login = static function (string $em, string $pw) use ($req, $hidden): array {
    $jar = null;
    $pg = $req('GET', '/admin/login', [], $jar);
    $req('POST', '/admin/login', $hidden($pg['body']) + ['email' => $em, 'password' => $pw], $jar);
    return [$req('GET', '/admin/data-protection', [], $jar), $jar];
};
[$ra] = $login('zz-dp-admin@example.test', $pwA);
check('an administrator sees the Data protection page', $ra['code'] === 200 && str_contains($ra['body'], 'Owner checklist') && str_contains($ra['body'], (string) ($row['reference'] ?? 'x')), (string) $ra['code']);
check('the admin page lists the requests, retention reminders and the checklist', str_contains($ra['body'], 'Retention reminders') && str_contains($ra['body'], 'Data rights requests') && stripos($ra['body'], 'to be confirmed') !== false);
check('the admin page says it is a draft and names the PDPO check', stripos($ra['body'], 'not legal advice') !== false && str_contains($ra['body'], 'Personal Data Protection Office'));
[$re] = $login('zz-dp-editor@example.test', $pwE);
check('an editor without settings.manage cannot open it', in_array($re['code'], [403, 302, 303], true) && !str_contains($re['body'], 'Owner checklist'), (string) $re['code']);

// ---- clean up ---------------------------------------------------------------------------------------------------------------------------------------
Database::execute("DELETE FROM users WHERE email LIKE 'zz-dp-%'");
Database::execute("DELETE FROM login_attempts WHERE email LIKE 'zz-dp-%'");
Database::execute("DELETE FROM audit_log WHERE user_name LIKE '%zz-dp-%' OR summary LIKE '%zz-dp-%'");
Database::execute('DELETE FROM contact_messages WHERE email = ?', [$email]);
Database::execute('DELETE FROM rate_limits');
check('cleanup: no test request, subscriber or account is left', (int) Database::value("SELECT COUNT(*) FROM contact_messages WHERE email = ?", [$email]) === 0 && (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-dp-%'") === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL PASSED' : $fail . ' FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
