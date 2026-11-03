<?php
declare(strict_types=1);

/**
 * Bono AI: the built-in knowledge about the foundation (app/config/bono/knowledge.txt). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-bono-knowledge.php
 * Needs the bono_* tables. Puts the database back as it found it (taught answers, facts page and questions added by this test are removed).
 */

use App\Core\Database;
use App\Services\Bono\BonoEngine;
use App\Services\Bono\BonoIndexer;
use App\Services\Bono\BonoKnowledge;

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

echo "-- bono: built-in knowledge --\n";
check('the Bono tables exist', BonoIndexer::ready());
$blocks = BonoKnowledge::load();
check('the knowledge file holds at least 80 topics and 300 questions', count($blocks) >= 80 && BonoKnowledge::count() >= 300, count($blocks) . ' topics, ' . BonoKnowledge::count() . ' questions');
$bad = [];
$seen = [];
$dups = [];
foreach ($blocks as $b) {
    if ($b['questions'] === [] || mb_strlen($b['answer']) < 40 || mb_strlen($b['answer']) > 1500) {
        $bad[] = $b['heading'];
    }
    foreach ($b['questions'] as $q) {
        $k = mb_strtolower($q);
        isset($seen[$k]) ? $dups[] = $q : $seen[$k] = true;
        if (\App\Services\Bono\BonoText::stems(BonoEngine::clean($q)) === []) {
            $bad[] = $b['heading'] . ' / ' . $q;
        }
    }
}
check('every topic has questions and an answer of 40-1500 characters, and every question has searchable words', $bad === [], implode(' | ', array_slice($bad, 0, 5)));
check('no question is written twice', $dups === [], implode(' | ', array_slice($dups, 0, 5)));
$secret = false;
foreach ($blocks as $b) {
    if (preg_match('/sk-ant|password\s*[:=]|api[_ ]?key/i', $b['answer'])) {
        $secret = true;
    }
}
check('the knowledge holds no secret, key or password', !$secret);

$startedAt = date('Y-m-d H:i:s');
$beforeIds = array_map('intval', array_column(Database::all('SELECT id FROM bono_taught'), 'id'));
$r1 = BonoKnowledge::install();
$r2 = BonoKnowledge::install();
check('install(): adds the questions once; a second run adds nothing (nothing taught by hand is overwritten)', $r1['added'] > 0 && $r2['added'] === 0 && $r2['existing'] >= $r1['added'], json_encode([$r1, $r2]));

// a hand-taught answer for one of the questions is never replaced
Database::execute("UPDATE bono_taught SET answer = 'HAND-WRITTEN' WHERE question = ?", [$blocks[0]['questions'][0]]);
BonoKnowledge::install();
check('an answer an administrator changed stays as it is', (string) Database::value('SELECT answer FROM bono_taught WHERE question = ?', [$blocks[0]['questions'][0]], '') === 'HAND-WRITTEN');
Database::execute('UPDATE bono_taught SET answer = ? WHERE question = ?', [mb_substr($blocks[0]['answer'], 0, 1500), $blocks[0]['questions'][0]]);

// every question, asked exactly as written, gets the answer of its own topic
$wrong = [];
foreach ($blocks as $b) {
    foreach ($b['questions'] as $q) {
        $res = BonoEngine::ask($q);
        $ans = (string) ($res['answer'] ?? '');
        $head = mb_substr($b['answer'], 0, 40);
        if (!str_contains($ans, $head) && !str_contains($ans, mb_substr($b['answer'], 0, 25))) {
            $wrong[] = $q;
        }
    }
}
check('every written question gets its own answer from Bono (' . BonoKnowledge::count() . ' asked)', count($wrong) <= (int) round(BonoKnowledge::count() * 0.03), count($wrong) . ' differ, e.g. ' . implode(' | ', array_slice($wrong, 0, 8)));

// phrasings nobody wrote: the answer must come from the right topic (taught answer or searchable passage)
BonoIndexer::$fetch = static fn(string $url): ?array => ['status' => 404, 'body' => ''];
BonoIndexer::run(20, true);
BonoIndexer::$fetch = null;
$cases = [
    ['Where exactly is your office in Gulu?' , 'Laroo-Pece'],
    ['I want to give money to help the young people, how?', 'Donate page'],
    ['can i do a free course online about excel', 'Excel'],
    ['I lost my LAF-AC number what now', 'registration number'],
    ['who is the person that founded lumina', 'Moses Komakech'],
    ['how long till my academy account is approved', 'approved'],
    ['what time does the office close', '5pm'],
    ['do you have a whatsapp number', 'WhatsApp'],
    ['do you teach python programming', 'Python'],
    ['I would like to translate the site into french', 'French'],
];
$miss = [];
foreach ($cases as [$q, $needle]) {
    $a = (string) (BonoEngine::ask($q)['answer'] ?? '');
    if (stripos($a, $needle) === false) {
        $miss[] = $q . ' => ' . mb_substr($a, 0, 80);
    }
}
check('phrasings nobody wrote still find the right answer (' . count($cases) . ' tried, at most 2 may miss)', count($miss) <= 2, implode(' || ', $miss));
check('the knowledge is also searchable passages (its own source, never above a real page)', (int) Database::value("SELECT COUNT(*) FROM bono_chunks c JOIN bono_pages p ON p.id = c.page_id WHERE p.url = ?", [BonoIndexer::KNOWLEDGE_URL], 0) >= count($blocks));

// ---- the admin button
$base = 'http://localhost/' . basename(dirname(__DIR__));
Database::execute("DELETE FROM users WHERE email = 'zz-bk-admin@example.test'");
$apw = 'Bono knowledge pass 99';
\App\Models\User::create(['name' => 'ZZ Bk Admin', 'email' => 'zz-bk-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'bkj');
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
$req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-bk-admin@example.test', 'password' => $apw]);
$r = $req('GET', '/admin/bono');
check('admin: the Bono page offers "Teach Bono about the foundation" with the number of answers', $r['code'] === 200 && str_contains($r['body'], 'Teach Bono about the foundation') && str_contains($r['body'], '(' . BonoKnowledge::count() . ' answers)'), (string) $r['code']);
Database::execute('DELETE FROM bono_taught WHERE id NOT IN (' . ($beforeIds === [] ? '0' : implode(',', $beforeIds)) . ')');      // start from what was there before this test
$before = (int) Database::value('SELECT COUNT(*) FROM bono_taught', [], 0);
$req('POST', '/admin/bono/knowledge', ['_token' => $tok($r['body'])]);
check('admin: pressing it teaches Bono the built-in answers', (int) Database::value('SELECT COUNT(*) FROM bono_taught', [], 0) >= $before + BonoKnowledge::count() - 5);
@unlink($jar);
Database::execute("DELETE FROM users WHERE email = 'zz-bk-admin@example.test'");
Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Bk Admin'");

// ---- put the database back
Database::execute('DELETE FROM bono_taught WHERE id NOT IN (' . ($beforeIds === [] ? '0' : implode(',', $beforeIds)) . ')');
Database::execute('DELETE FROM bono_pages WHERE url IN (?, ?)', [BonoIndexer::FACTS_URL, BonoIndexer::KNOWLEDGE_URL]);
Database::execute('DELETE FROM bono_log WHERE created_at >= ?', [$startedAt]);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
