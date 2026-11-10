<?php
declare(strict_types=1);

/**
 * LAF Academy: "E-mail learners about this open challenge" (ChallengeAnnounce + the admin page). Run alone:   /c/xampp/php/php.exe bin/selftest-academy-challenge-announce.php
 * Needs the local web server, APP_ENV=local (mail driver "file"). Adds its own learners (zz-ca-*@example.test) and challenge (zz-ca-challenge), removes them again.
 */

use App\Core\Database;
use App\Services\Academy\ChallengeAnnounce;
use App\Services\Academy\ChallengeService;

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

echo "-- academy: e-mail learners about a challenge --\n";
$clean = static function (): void {
    Database::execute("DELETE m FROM academy_messages m JOIN academy_learners l ON l.id = m.learner_id WHERE l.email LIKE 'zz-ca-%@example.test'");
    Database::execute("DELETE FROM academy_challenge_entries WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-ca-%@example.test')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-ca-%@example.test'");
    Database::execute("DELETE FROM academy_challenges WHERE slug LIKE 'zz-ca-%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-ca-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
check('the challenge tables exist', ChallengeService::ready());
if (!ChallengeService::ready()) {
    return;
}
$now = date('Y-m-d H:i:s');
$mk = static function (string $tag, int $active, ?string $verified) use ($now): int {
    Database::execute('INSERT INTO academy_learners (reg_no, name, email, password_hash, email_verified_at, approved_at, is_active, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)',
        ['LAF-AC-ZCA-' . $tag, 'Zz Ca ' . $tag, 'zz-ca-' . $tag . '@example.test', 'x', $verified, $now, $active, $now, $now]);
    return (int) Database::value('SELECT LAST_INSERT_ID()');
};
$a = $mk('a', 1, $now);
$b = $mk('b', 1, $now);
$off = $mk('off', 0, $now);
$un = $mk('un', 1, null);
Database::execute("INSERT INTO academy_challenges (slug, kind, title, category, summary, prize, status, starts_at, ends_at, created_at) VALUES ('zz-ca-open','quiz','ZZ Open Quiz','basics','A short test summary.','A trophy','published',?,?,?)",
    [date('Y-m-d H:i:s', time() + 86400), date('Y-m-d H:i:s', time() + 3 * 86400), $now]);
$cid = (int) Database::value('SELECT LAST_INSERT_ID()');
$c = ChallengeService::find($cid);
check('an upcoming published challenge is open for announcing', ChallengeAnnounce::isOpen($c));
check('a draft is not', !ChallengeAnnounce::isOpen(array_merge($c, ['status' => 'draft'])));
check('a finished one is not', !ChallengeAnnounce::isOpen(array_merge($c, ['finalized_at' => $now])));

$ids = array_map(static fn($l) => (int) $l['id'], ChallengeAnnounce::pending($c));
check('pending: active and confirmed learners only (not deactivated, not unconfirmed)', in_array($a, $ids, true) && in_array($b, $ids, true) && !in_array($off, $ids, true) && !in_array($un, $ids, true));

Database::execute("INSERT INTO academy_challenge_entries (challenge_id, learner_id, status) VALUES (?,?, 'approved')", [$cid, $a]);
$new = array_map(static fn($l) => (int) $l['id'], ChallengeAnnounce::pending($c, 'new'));
check('scope "new": a learner who already entered is left out', !in_array($a, $new, true) && in_array($b, $new, true));

$dir = dirname(__DIR__) . '/storage/mail';
$count = static fn(): int => count(glob($dir . '/*.eml') ?: []);
$before = $count();
$r = ChallengeAnnounce::send($c, 'all', 'Do not miss it.', false, null, 1000);
check('send: every active learner got one e-mail', $r['failed'] === 0 && $r['sent'] >= 2 && $count() === $before + $r['sent'], json_encode($r));
$files = glob($dir . '/*.eml') ?: [];
usort($files, static fn($x, $y) => filemtime($y) <=> filemtime($x));
$mails = '';
foreach (array_slice($files, 0, $r['sent']) as $f) {
    $raw = (string) file_get_contents($f);
    $mails .= $raw;
    if (preg_match_all('~?
?
([A-Za-z0-9+/=
]{40,})~', $raw, $parts)) {
        foreach ($parts[1] as $b64) {
            $mails .= (string) base64_decode($b64);
        }
    }
}
check('the e-mail names the challenge, has the note, the prize and the join link', str_contains($mails, 'ZZ Open Quiz') && str_contains($mails, 'Do not miss it.') && str_contains($mails, 'A trophy') && str_contains($mails, 'academy/challenges/zz-ca-open'));
check('the portal copy was saved for the learner', (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE 'New challenge: ZZ Open Quiz%'", [$b]) === 1);
$again = ChallengeAnnounce::send($c, 'all', '', false, null, 1000);
check('pressing again tells nobody twice', $again['sent'] === 0 && $again['total'] === 0, json_encode($again));
$forced = ChallengeAnnounce::send($c, 'all', '', true, null, 1);
check('"send again" sends, and the per-run limit holds', $forced['sent'] === 1 && $forced['remaining'] >= 1, json_encode($forced));

// ---- benefits, the digest of ALL open challenges, the stage e-mails
$std = ChallengeService::benefits($c);
check('benefits: the standard ones come from the challenge settings (certificate, winners wall, prize)', count($std) >= 3 && str_contains(implode(' ', $std), 'Winners wall') && str_contains(implode(' ', $std), 'Prize: A trophy'), implode(' | ', $std));
Database::execute('UPDATE academy_challenges SET benefits = ? WHERE id = ?', ["Free mentoring session\nA letter of recommendation", $cid]);
check('benefits: what the administrator writes replaces the standard ones, one per line', ChallengeService::benefits(ChallengeService::find($cid)) === ['Free mentoring session', 'A letter of recommendation']);
Database::execute("INSERT INTO academy_challenges (slug, kind, title, category, summary, status, starts_at, ends_at, created_at) VALUES ('zz-ca-second','innovation','ZZ Second Challenge','basics','Build something.','published',?,?,?)", [date('Y-m-d H:i:s', time() + 2 * 86400), date('Y-m-d H:i:s', time() + 9 * 86400), $now]);
$list = ChallengeAnnounce::openList();
$slugs = array_column($list, 'slug');
check('digest: both open challenges are listed (a draft or finished one is not)', in_array('zz-ca-open', $slugs, true) && in_array('zz-ca-second', $slugs, true));
$mine = array_values(array_filter($list, static fn($x) => str_starts_with((string) $x['slug'], 'zz-ca-')));
$before = $count();
$dr = ChallengeAnnounce::sendDigest($mine, 'Join us!', false, null, 1000);
check('digest: every active learner got one e-mail', $dr['failed'] === 0 && $dr['sent'] >= 2 && $count() === $before + $dr['sent'], json_encode($dr));
$mt = '';
$latest = glob($dir . '/*.eml') ?: [];
usort($latest, static fn($x, $y) => filemtime($y) <=> filemtime($x));
foreach (array_slice($latest, 0, $dr['sent']) as $f) {
    $raw = (string) file_get_contents($f);
    $mt .= $raw;
    if (preg_match_all('~\r?\n\r?\n([A-Za-z0-9+/=\r\n]{40,})~', $raw, $pp)) {
        foreach ($pp[1] as $b64) {
            $mt .= (string) base64_decode($b64);
        }
    }
}
check('digest: the e-mail lists both challenges with "What you get" and the team note', str_contains($mt, 'ZZ Open Quiz') && str_contains($mt, 'ZZ Second Challenge') && str_contains($mt, 'What you get') && str_contains($mt, 'Free mentoring session') && str_contains($mt, 'Join us!'));
$dr2 = ChallengeAnnounce::sendDigest($mine, '', false, null, 1000);
check('digest: pressing again tells nobody twice', $dr2['sent'] === 0 && $dr2['total'] === 0);
$learner = Database::one('SELECT id, name, email FROM academy_learners WHERE id = ?', [$a]);
$entry = ['status' => 'pending', 'quiz_correct' => 7, 'quiz_total' => 10];
$mailsBefore = $count();
\App\Services\Academy\AcademyMail::challengeApplied($c, $learner, $entry);
\App\Services\Academy\AcademyMail::challengeQuizDone($c, $learner, $entry);
\App\Services\Academy\AcademyMail::challengeProjectDone($c, $learner, $entry);
check('stages: application received, quiz received and project received each e-mail the applicant', $count() === $mailsBefore + 3);
// the challenge starts: the people who are in are told once, by settle()
Database::execute('UPDATE academy_challenges SET starts_at = ?, started_notice_at = NULL WHERE id = ?', [date('Y-m-d H:i:s', time() - 60), $cid]);
Database::execute("UPDATE academy_challenge_entries SET status = 'approved' WHERE challenge_id = ?", [$cid]);
$before = $count();
ChallengeService::settle();
check('stages: when the challenge starts, the approved entrants are e-mailed once', $count() === $before + 1 && Database::value('SELECT started_notice_at FROM academy_challenges WHERE id = ?', [$cid]) !== null, (string) ($count() - $before));
$before = $count();
ChallengeService::settle();
check('stages: and not again', $count() === $before);

// ---- the admin page
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $path, ?array $form, string $j) use ($base): array {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 90, CURLOPT_COOKIEJAR => $j, CURLOPT_COOKIEFILE => $j, CURLOPT_FOLLOWLOCATION => false]);
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
$pw = 'Challenge announce pass 71';
\App\Models\User::create(['name' => 'ZZ Ca Admin', 'email' => 'zz-ca-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($pw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$j = tempnam(sys_get_temp_dir(), 'caj');
$lp = $req('/admin/login', null, $j);
$req('/admin/login', ['_token' => $tok($lp['body']), 'email' => 'zz-ca-admin@example.test', 'password' => $pw], $j);
$list = $req('/admin/academy/challenges', null, $j);
check('admin: the challenge list has an "E-mail learners" button for the open challenge', $list['code'] === 200 && str_contains($list['body'], '/announce'), (string) $list['code']);
$pg = $req('/admin/academy/challenges/' . $cid . '/announce', null, $j);
check('admin: the page shows the challenge, the counts and the send button', $pg['code'] === 200 && str_contains($pg['body'], 'ZZ Open Quiz') && str_contains($pg['body'], 'E-mail the learners'), (string) $pg['code']);
$before = $count();
$post = $req('/admin/academy/challenges/' . $cid . '/announce', ['_token' => $tok($pg['body']), 'scope' => 'all', 'note' => 'Hello', 'again' => '1'], $j);
check('admin: pressing the button sends and returns to the page', $post['code'] === 302 && $count() > $before, (string) $post['code']);
$anon = $req('/admin/academy/challenges/' . $cid . '/announce', null, tempnam(sys_get_temp_dir(), 'can'));
check('admin: a visitor who is not signed in is sent to the sign-in page', $anon['code'] === 302);

@unlink($j);
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
