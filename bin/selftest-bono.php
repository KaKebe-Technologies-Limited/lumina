<?php
declare(strict_types=1);

/**
 * Bono AI, the support assistant (docs/BONO.md). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-bono.php
 * The website is replaced by small fixture pages and Claude by a fake transport: no network. Needs the local web server for the HTTP part. NOTE: it empties the Bono tables
 * (what Bono learned locally): run bin/bono-learn.php afterwards to teach it the local site again.
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
use App\Services\Bono\BonoAi;
use App\Services\Bono\BonoEngine;
use App\Services\Bono\BonoIndexer;
use App\Services\Bono\BonoText;

echo "-- bono --\n";
check('bono: the tables exist after the schema migration', BonoIndexer::ready());

// ------------------------------------------------------------------------------------------------------------- text
check('text: forms of a word meet (donate, donating, donated, donations)', count(array_unique(array_map([BonoText::class, 'stem'], ['donate', 'donating', 'donated', 'donations', 'donation']))) === 1);
check('text: stop words and question frames are dropped', BonoText::stems('How can I donate to the foundation?') === ['donat', 'foundat'] && BonoText::stems('hello') === []);
check('text: synonyms link "office" and "address"', in_array(BonoText::stem('address'), BonoText::synonyms()[BonoText::stem('office')] ?? [], true));
check('text: sentences and clipping', count(BonoText::sentences('We help youth. We run classes! Join us today?')) === 3 && mb_strlen(BonoText::clip(str_repeat('word ', 80), 60)) <= 64);

// ------------------------------------------------------------------------------------------------------------- reading a page
$html = '<html><head><title>Our Programs | Lumina</title><meta name="description" content="Programs that open pathways for young people."></head><body>'
    . '<header><nav>MENU ITEM ALPHA</nav></header><main id="main"><h1>Our Programs</h1><p>We run a youth skills program in Gulu every Saturday for young people aged 15 to 25.</p>'
    . '<h2 id="fees">What does it cost?</h2><p>All our programs are free of charge. Bring a notebook and a pen.</p><form><label>SECRET FORM LABEL</label></form><script>var hidden = "SCRIPT TEXT";</script>'
    . '<div hidden>HIDDEN TEXT</div></main><footer>FOOTER LINKS BETA</footer></body></html>';
$x = BonoIndexer::extract($html);
$all = implode(' ', array_map(static fn(array $s): string => $s['heading'] . ' ' . $s['text'], $x['sections']));
check('extract: the title and the passages under each heading', $x['title'] === 'Our Programs' && count($x['sections']) === 2 && $x['sections'][0]['heading'] === 'Our Programs' && str_contains($x['sections'][0]['text'], 'pathways') && $x['sections'][1]['heading'] === 'What does it cost?' && $x['sections'][1]['anchor'] === 'fees');
check('extract: menus, footer, forms, scripts and hidden text are left out', !str_contains($all, 'MENU ITEM') && !str_contains($all, 'FOOTER LINKS') && !str_contains($all, 'SECRET FORM') && !str_contains($all, 'SCRIPT TEXT') && !str_contains($all, 'HIDDEN TEXT') && str_contains($all, 'free of charge'));
check('extract: a page marked noindex is recognised', BonoIndexer::extract('<html><head><meta name="robots" content="noindex,nofollow"></head><body><main><h1>X</h1><p>Private page text that is long enough.</p></main></body></html>')['noindex'] === true);
check('extract: long text is cut into passages of whole sentences', count(BonoIndexer::passages(str_repeat('This is a sentence of moderate length. ', 60))) >= 3);
check('private pages are never learned', BonoIndexer::private('/admin/settings') && BonoIndexer::private('/academy/dashboard') && BonoIndexer::private('/academy/certificate/LAF-26-ABCDEFGH') && BonoIndexer::private('/shop/checkout') && !BonoIndexer::private('/academy') && !BonoIndexer::private('/about'));

// ------------------------------------------------------------------------------------------------------------- learning (fake website)
Database::execute('DELETE FROM bono_pages');
Database::execute('DELETE FROM bono_taught');
Database::execute('DELETE FROM bono_log');
$siteBase = rtrim(abs_url(''), '/');
$base = rtrim(trim((string) (\App\Core\Env::get('BONO_BASE_URL', '') ?? '')) ?: $siteBase, '/');        // where the learner reads the pages from (a local copy lives in a sub-folder)
$pages = [
    $base . '/' => '<html><head><title>Home | Lumina</title></head><body><main><h1>Lumina Africa Foundation</h1><p>A community foundation in Gulu, Northern Uganda, building pathways to opportunity.</p></main></body></html>',
    $base . '/programs' => $html,
    $base . '/support-us' => '<html><head><title>Support Us | Lumina</title></head><body><main><h1>Support Us</h1><h2>Donate online</h2><p>You can donate by card or mobile money on the donate page. Every donation funds classes for young people.</p><h2>Volunteer</h2><p>Volunteers help us run workshops on weekends. Write to us to volunteer.</p></main></body></html>',
    $base . '/old-page' => '<html><body><main><h1>Gone</h1></main></body></html>',
    $base . '/secret' => '<html><head><meta name="robots" content="noindex"></head><body><main><h1>Secret</h1><p>This private page text should never be learned by anyone.</p></main></body></html>',
];
$calls = [];
$siteUrls = static fn(array $keys): array => array_map(static fn(string $u): string => $siteBase . substr($u, strlen($base)), $keys);
BonoIndexer::$urls = array_merge($siteUrls(array_keys($pages)), [$siteBase . '/admin/settings', 'https://other-site.example/about']);
BonoIndexer::$fetch = static function (string $url) use (&$pages, &$calls): ?array {
    $calls[] = $url;
    if ($url === $GLOBALS['__bono_base'] . '/old-page') {
        return ['status' => 404, 'body' => ''];
    }
    return isset($pages[$url]) ? ['status' => 200, 'body' => $pages[$url]] : null;
};
$GLOBALS['__bono_base'] = $base;
$r = BonoIndexer::run(30);
check('learn: reads the public pages of this site only (not the admin, not another website)', !in_array($siteBase . '/admin/settings', $calls, true) && !in_array($base . '/admin/settings', $calls, true) && !in_array('https://other-site.example/about', $calls, true) && count($calls) === 5);
check('learn: pages with text are stored; a noindex page and a 404 are not', (int) Database::value('SELECT COUNT(*) FROM bono_pages WHERE url = ?', [$base . '/programs'], 0) === 1 && (int) Database::value('SELECT COUNT(*) FROM bono_pages WHERE url IN (?, ?)', [$base . '/secret', $base . '/old-page'], 0) === 0 && $r['done']);
check('learn: the contact details and the Academy rules come from the settings', (int) Database::value('SELECT COUNT(*) FROM bono_pages WHERE url = ?', [BonoIndexer::FACTS_URL], 0) === 1 && (int) Database::value("SELECT COUNT(*) FROM bono_chunks WHERE body LIKE '%approved by the Academy team%' OR body LIKE '%e-mail address%'", [], 0) > 0);
$chunks1 = (int) Database::value('SELECT COUNT(*) FROM bono_chunks', [], 0);
$calls = [];
$r2 = BonoIndexer::run(30);
check('learn: pages read in the last day are not read again (only the two that have nothing to learn are tried again)', count($calls) === 2 && (int) Database::value('SELECT COUNT(*) FROM bono_chunks', [], 0) === $chunks1);
$calls = [];
$r3 = BonoIndexer::run(30, true);
check('learn: "read everything again" re-reads every page but changes nothing that did not change', count($calls) === 5 && (int) Database::value('SELECT COUNT(*) FROM bono_chunks', [], 0) === $chunks1);
$pages[$base . '/programs'] = str_replace('free of charge', 'free of charge, and lunch is provided on Saturdays', $html);
BonoIndexer::run(30, true);
check('learn: a changed page is learned again (new text is found)', (int) Database::value("SELECT COUNT(*) FROM bono_chunks WHERE body LIKE '%lunch is provided%'", [], 0) === 1);
BonoIndexer::$urls = $siteUrls([$base . '/', $base . '/support-us']);
BonoIndexer::run(30, true);
check('learn: a page that left the sitemap is forgotten', (int) Database::value('SELECT COUNT(*) FROM bono_pages WHERE url = ?', [$base . '/programs'], 0) === 0 && (int) Database::value('SELECT COUNT(*) FROM bono_pages WHERE url = ?', [$base . '/support-us'], 0) === 1);
BonoIndexer::$urls = $siteUrls(array_keys($pages));
BonoIndexer::run(30, true);

// ------------------------------------------------------------------------------------------------------------- answers
$a = BonoEngine::ask('How can I donate?');
check('answer: a question the website covers is answered from the page, with its link', $a['outcome'] === BonoEngine::OUT_SITE && str_contains($a['answer'], 'donate') && $a['sources'] !== [] && str_contains($a['sources'][0]['url'], '/support-us'));
$a2 = BonoEngine::ask('Is the youth program free?');
check('answer: another question finds another page (programs, free of charge)', $a2['outcome'] === BonoEngine::OUT_SITE && str_contains($a2['answer'], 'free of charge') && str_contains($a2['sources'][0]['url'], '/programs'));
$none = BonoEngine::ask('What is the price of bitcoin today?');
check('answer: when the website does not cover it Bono says so, offers the team and keeps the question', $none['outcome'] === BonoEngine::OUT_NONE && str_contains($none['answer'], "not sure") && str_contains($none['answer'], '+256779795662')
    && (int) Database::value('SELECT COUNT(*) FROM bono_log WHERE outcome = 0 AND question LIKE ?', ['%bitcoin%'], 0) === 1);
check('answer: small talk gets a greeting with suggestions, thanks gets a reply', BonoEngine::ask('hello')['suggestions'] !== [] && BonoEngine::ask('Thank you')['outcome'] === BonoEngine::OUT_TALK && BonoEngine::ask('who are you?')['outcome'] === BonoEngine::OUT_TALK);
check('answer: where the office is comes from the contact facts (synonyms)', str_contains(BonoEngine::ask('where is your office located')['answer'], 'Address') || str_contains(BonoEngine::ask('where is your office located')['answer'], 'Gulu'));
$f1 = BonoEngine::ask('and volunteering?', [['role' => 'user', 'text' => 'How can I donate?'], ['role' => 'bot', 'text' => 'x']]);
check('answer: a short follow-up is understood', $f1['outcome'] === BonoEngine::OUT_SITE && str_contains($f1['answer'], 'olunteer'));

// taught answers always win
Database::insert('bono_taught', ['question' => 'How do I donate by mobile money?', 'answer' => 'Send mobile money to the number on the donate page and WhatsApp us the receipt.', 'stems' => ' ' . implode(' ', array_unique(BonoText::stems('How do I donate by mobile money?'))) . ' ']);
$t = BonoEngine::ask('how to donate with mobile money');
check('teach: a taught answer beats the website for the same question and counts its use', $t['outcome'] === BonoEngine::OUT_TAUGHT && str_contains($t['answer'], 'Send mobile money') && (int) Database::value('SELECT hits FROM bono_taught LIMIT 1', [], 0) === 1);
check('teach: a different question is not hijacked by it', BonoEngine::ask('Do you need volunteers?')['outcome'] !== BonoEngine::OUT_TAUGHT);
Database::execute('UPDATE bono_taught SET is_active = 0');
check('teach: a switched-off answer is not used', BonoEngine::ask('how to donate with mobile money')['outcome'] === BonoEngine::OUT_SITE);

// ------------------------------------------------------------------------------------------------------------- Claude (fake)
check('ai: without ANTHROPIC_API_KEY Bono does not call Claude', !BonoAi::enabled());
putenv('ANTHROPIC_API_KEY=sk-ant-test-key');
check('ai: with the key it is on, and the default model is claude-opus-5-5', BonoAi::enabled() && BonoAi::model() === 'claude-opus-5-5');
$seen = [];
BonoAi::$transport = static function (string $url, array $headers, string $json) use (&$seen): array {
    $seen[] = ['url' => $url, 'headers' => $headers, 'body' => json_decode($json, true)];
    return ['status' => 200, 'body' => json_encode(['stop_reason' => 'end_turn', 'content' => [['type' => 'thinking', 'thinking' => '', 'signature' => 'x'], ['type' => 'text', 'text' => 'You can donate by card or mobile money on the <b>donate</b> page.']]])];
};
$ai = BonoEngine::ask('How can I donate?');
$req = $seen[0] ?? ['url' => '', 'headers' => [], 'body' => []];
check('ai: the question and the matching website passages are sent to the Messages API with the key, only the text of the answer is used, tags are stripped', $ai['outcome'] === BonoEngine::OUT_AI && $ai['answer'] === 'You can donate by card or mobile money on the donate page.'
    && $req['url'] === 'https://api.anthropic.com/v1/messages' && in_array('x-api-key: sk-ant-test-key', $req['headers'], true) && in_array('anthropic-version: 2023-06-01', $req['headers'], true));
$body = $req['body'];
check('ai: the system prompt limits it to the passages, the passages are data inside <context>, effort is low, the refusal fallback is on', str_contains($body['system'], 'ONLY') && str_contains($body['system'], 'untrusted') && str_contains($body['messages'][0]['content'], '<context>') && str_contains($body['messages'][0]['content'], 'donate by card')
    && ($body['output_config']['effort'] ?? '') === 'low' && ($body['fallbacks'] ?? '') === 'default' && in_array('anthropic-beta: server-side-fallback-2026-07-01', $req['headers'], true) && $body['model'] === 'claude-opus-5-5' && !isset($body['temperature']));
check('ai: the key never reaches the visitor and no personal data is in the request', !str_contains(json_encode($ai), 'sk-ant') && !str_contains($req['body']['messages'][0]['content'], '@'));
$n = 0;
BonoAi::$transport = static function (string $url, array $headers, string $json) use (&$n, &$seen): array {
    $n++;
    $seen[] = ['body' => json_decode($json, true), 'headers' => $headers];
    return $n === 1 ? ['status' => 400, 'body' => '{"error":{"message":"unknown parameter fallbacks"}}'] : ['status' => 200, 'body' => json_encode(['stop_reason' => 'end_turn', 'content' => [['type' => 'text', 'text' => 'Plain answer.']]])];
};
$retry = BonoEngine::ask('How can I donate?');
check('ai: an account without the fallback beta gets a second, plain request', $n === 2 && $retry['answer'] === 'Plain answer.' && !isset($seen[count($seen) - 1]['body']['fallbacks']));
BonoAi::$transport = static fn(string $u, array $h, string $j): array => ['status' => 200, 'body' => json_encode(['stop_reason' => 'refusal', 'content' => []])];
$ref = BonoEngine::ask('How can I donate?');
check('ai: a refusal falls back to the sentences of the website', $ref['outcome'] === BonoEngine::OUT_SITE && str_contains($ref['answer'], 'donate'));
BonoAi::$transport = static fn(string $u, array $h, string $j): array => ['status' => 500, 'body' => 'oops'];
check('ai: an error or timeout falls back too, so the assistant always answers', BonoEngine::ask('How can I donate?')['outcome'] === BonoEngine::OUT_SITE);
BonoAi::$transport = static function (string $u, array $h, string $j) use (&$seen): array {
    $seen[] = ['called' => true];
    return ['status' => 200, 'body' => '{}'];
};
$before = count($seen);
BonoEngine::ask('What is the price of bitcoin today?');
check('ai: nothing is sent to Claude when the website has nothing about the question', count($seen) === $before);
Settings::set('bono_ai', '0');
Settings::flush();
check('ai: Claude can be switched off in the settings', !BonoAi::enabled());
Settings::reset('bono_ai');
Settings::flush();
BonoAi::$transport = null;
putenv('ANTHROPIC_API_KEY');

// ------------------------------------------------------------------------------------------------------------- over HTTP
$site = 'http://localhost/' . basename(dirname(__DIR__));
$jar = null;
$req = static function (string $method, string $path, array $data = [], ?string &$jar = null, bool $json = false, array $headers = []) use ($site): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lbj');
    $ch = curl_init($site . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => $headers]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json ? json_encode($data) : http_build_query($data));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'head' => substr($raw, 0, $hs), 'body' => substr($raw, $hs)];
};
$tokenOf = static fn(string $html): string => preg_match('/name="csrf-token" content="([^"]+)"/', $html, $m) === 1 ? $m[1] : (preg_match('/name="_token" value="([^"]+)"/', $html, $m2) === 1 ? $m2[1] : '');
Database::execute('DELETE FROM rate_limits');
$home = $req('GET', '/', [], $jar);
check('http: every public page carries the Bono AI window and its script', str_contains($home['body'], 'data-bono') && str_contains($home['body'], 'js/bono.js') && str_contains($home['body'], 'Ask Bono AI'));
$tok = $tokenOf($home['body']);
$ask = static fn(array $d, ?string $t = null) => $req('POST', '/bono/ask', $d, $jar, true, ['Content-Type: application/json', 'X-CSRF-TOKEN: ' . ($t ?? $tok)]);
$rr = $ask(['question' => 'hello there']);
$j = json_decode($rr['body'], true);
check('http: a question gets a JSON answer', $rr['code'] === 200 && is_array($j) && $j['ok'] === true && str_contains($j['answer'], 'Bono AI'));
check('http: without the CSRF token the question is refused', $ask(['question' => 'hello'], 'wrong')['code'] === 419 || $ask(['question' => 'hello'], 'wrong')['code'] === 403);
check('http: an empty question is refused', $ask(['question' => '   '])['code'] === 422);
$xss = json_decode($ask(['question' => '<script>alert(1)</script> hello'])['body'], true);
check('http: markup in a question is stripped before it is stored', !str_contains(json_encode(Database::all('SELECT question FROM bono_log ORDER BY id DESC LIMIT 3')), '<script') && is_array($xss));
$fb = $req('POST', '/bono/feedback', ['id' => (int) $j['id'], 'helpful' => 1], $jar, true, ['Content-Type: application/json', 'X-CSRF-TOKEN: ' . $tok]);
check('http: the visitor can say the answer helped (once)', $fb['code'] === 200 && (int) Database::value('SELECT helpful FROM bono_log WHERE id = ?', [(int) $j['id']], -1) === 1);
Database::execute('DELETE FROM rate_limits');
$codes = [];
for ($i = 0; $i < 26; $i++) {
    $codes[] = $ask(['question' => 'hello'])['code'];
}
check('http: too many questions in a short time are slowed down (429) with a friendly message', in_array(429, $codes, true));
Database::execute('DELETE FROM rate_limits');
Settings::set('bono_enabled', '0');
Settings::flush();
$off = $req('GET', '/', [], $jar);
check('http: switched off, the window disappears and the endpoint answers 404', !str_contains($off['body'], 'data-bono') && $ask(['question' => 'hello'])['code'] === 404);
Settings::reset('bono_enabled');
Settings::flush();

// the administrator
Database::execute("DELETE FROM users WHERE email LIKE 'zz-lbo-%'");
$pwA = 'Admin lbo pass 91';
$pwE = 'Editor lbo pass 94';
\App\Models\User::create(['name' => 'ZZ Lbo Admin', 'email' => 'zz-lbo-admin@example.test', 'password_hash' => Auth::hashPassword($pwA), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
\App\Models\User::create(['name' => 'ZZ Lbo Editor', 'email' => 'zz-lbo-editor@example.test', 'password_hash' => Auth::hashPassword($pwE), 'role' => 'editor', 'is_active' => 1, 'must_change_password' => 0]);
$login = static function (string $email, string $pw, ?string &$jr) use ($req, $tokenOf): void {
    $p = $req('GET', '/admin/login', [], $jr);
    $req('POST', '/admin/login', ['_token' => $tokenOf($p['body']), 'email' => $email, 'password' => $pw], $jr);
};
$jA = $jE = null;
$login('zz-lbo-admin@example.test', $pwA, $jA);
$login('zz-lbo-editor@example.test', $pwE, $jE);
$page = $req('GET', '/admin/bono', [], $jA);
check('admin: the Bono AI page shows what it learned and the questions it could not answer', $page['code'] === 200 && str_contains($page['body'], 'Learn the website now') && str_contains($page['body'], 'bitcoin') && str_contains($page['body'], 'Teach'));
check('admin: an editor cannot open it (403)', $req('GET', '/admin/bono', [], $jE)['code'] === 403);
$adminPost = static function (string $path, array $data) use ($req, $tokenOf, &$jA): array {
    $p = $req('GET', '/admin/bono', [], $jA);
    return $req('POST', $path, $data + ['_token' => $tokenOf($p['body'])], $jA);
};
$logId = (int) Database::value("SELECT id FROM bono_log WHERE outcome = 0 AND question LIKE '%bitcoin%' LIMIT 1", [], 0);
$ts = $adminPost('/admin/bono/teach', ['question' => 'What is the price of bitcoin today?', 'answer' => 'We are a foundation, not an exchange: we cannot advise on bitcoin.', 'log' => (string) $logId]);
$taught = Database::one("SELECT * FROM bono_taught WHERE question LIKE '%bitcoin%'");
check('admin: teaching an answer stores it and clears the question from the waiting list', $taught !== null && (int) Database::value("SELECT COUNT(*) FROM bono_log WHERE outcome = 0 AND handled = 0 AND question LIKE '%bitcoin%'", [], 0) === 0);
check('admin: Bono now gives the taught answer', str_contains(BonoEngine::ask('What is the price of bitcoin today?')['answer'], 'not an exchange'));
$nTaught = (int) Database::value('SELECT COUNT(*) FROM bono_taught', [], 0);
$bad = $adminPost('/admin/bono/teach', ['question' => 'a', 'answer' => '']);
check('admin: an empty question or answer is refused', (int) Database::value('SELECT COUNT(*) FROM bono_taught', [], 0) === $nTaught);
$adminPost('/admin/bono/taught/' . (int) $taught['id'] . '/toggle', []);
check('admin: a taught answer can be switched off', (int) Database::value('SELECT is_active FROM bono_taught WHERE id = ?', [(int) $taught['id']], 1) === 0);
$adminPost('/admin/bono/taught/' . (int) $taught['id'] . '/delete', []);
check('admin: and removed', (int) Database::value('SELECT COUNT(*) FROM bono_taught', [], 0) === $nTaught - 1);
$q2 = BonoEngine::ask('Tell me about quantum teleportation please');
$adminPost('/admin/bono/dismiss/' . $q2['id'], []);
check('admin: an unanswered question can be dismissed', (int) Database::value('SELECT handled FROM bono_log WHERE id = ?', [$q2['id']], 0) === 1);
check('admin: the settings page has the Bono AI switches', str_contains($req('GET', '/admin/settings', [], $jA)['body'], 'bono_enabled'));
check('robots: the chat endpoints are not for search engines', str_contains($req('GET', '/robots.txt', [], $jar)['body'], '/bono/'));

// ------------------------------------------------------------------------------------------------------------- clean up
BonoIndexer::$fetch = null;
BonoIndexer::$urls = null;
Database::execute('DELETE FROM bono_pages');
Database::execute('DELETE FROM bono_taught');
Database::execute('DELETE FROM bono_log');
Database::execute("DELETE FROM users WHERE email LIKE 'zz-lbo-%'");
Database::execute('DELETE FROM rate_limits');
Database::execute("DELETE FROM audit_log WHERE action LIKE 'bono.%'");

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
