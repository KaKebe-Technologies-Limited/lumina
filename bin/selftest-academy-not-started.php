<?php
declare(strict_types=1);

/**
 * LAF Academy: remind the approved learners who have NOT STARTED A COURSE (portal message + e-mail, WhatsApp links or the WhatsApp template). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-not-started.php
 * Needs the local web server. Removes its rows (zz-ns-*).
 */

use App\Core\Database;
use App\Services\Academy\NotStarted;

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

echo "-- academy: learners who have not started a course --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_progress WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-ns-%')");
    Database::execute("DELETE FROM academy_enrollments WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-ns-%')");
    Database::execute("DELETE FROM academy_messages WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-ns-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-ns-%'");
    Database::execute("DELETE FROM users WHERE email = 'zz-ns-admin@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$now = date('Y-m-d H:i:s');
$hash = password_hash('x', PASSWORD_DEFAULT);
$mk = static fn(string $tag, array $extra): int => (int) Database::insert('academy_learners', ['name' => 'Zed Ns ' . $tag, 'email' => 'zz-ns-' . $tag . '@example.test', 'password_hash' => $hash, 'phone' => '0772123456', 'country' => 'Uganda'] + $extra + ['is_active' => 1]);
$fresh = $mk('fresh', ['email_verified_at' => $now, 'whatsapp' => '256772123456']);
$enrolled = $mk('enrolled', ['email_verified_at' => $now]);
$started = $mk('started', ['email_verified_at' => $now]);
$waiting = $mk('waiting', []);
$off = $mk('off', ['email_verified_at' => $now, 'is_active' => 0]);
$course = (int) Database::value('SELECT id FROM academy_courses ORDER BY id LIMIT 1', [], 0);
$lesson = (int) Database::value('SELECT id FROM academy_lessons WHERE course_id = ? ORDER BY id LIMIT 1', [$course], 0);
Database::insert('academy_enrollments', ['learner_id' => $enrolled, 'course_id' => $course]);
Database::insert('academy_enrollments', ['learner_id' => $started, 'course_id' => $course]);
Database::execute('INSERT INTO academy_progress (learner_id, lesson_id, course_id, completed_at) VALUES (?, ?, ?, NOW())', [$started, $lesson, $course]);

$ids = array_map('intval', array_column(NotStarted::learners(), 'id'));
check('not started = approved and active with no lesson done and no quiz taken (enrolled only counts as not started)', in_array($fresh, $ids, true) && in_array($enrolled, $ids, true));
check('a learner who did a lesson, one waiting for approval and one switched off are NOT listed', !in_array($started, $ids, true) && !in_array($waiting, $ids, true) && !in_array($off, $ids, true));

$row = ['id' => $fresh, 'name' => 'Zed Ns fresh', 'phone' => '0772123456', 'whatsapp' => '256772123456'];
$url = NotStarted::whatsAppUrl($row);
check('the WhatsApp link opens the learner\'s chat with the message typed (first name and sign-in link)', $url !== null && str_starts_with($url, 'https://wa.me/256772123456?text=') && str_contains(rawurldecode($url), 'Hello Zed,') && str_contains(rawurldecode($url), 'academy/login'), (string) $url);
check('without a WhatsApp number the phone is used; with no usable number there is no link', str_contains((string) NotStarted::whatsAppUrl(['name' => 'A B', 'phone' => '+256 772 123 456', 'whatsapp' => '']), 'wa.me/256772123456') && NotStarted::whatsAppUrl(['name' => 'A B', 'phone' => '', 'whatsapp' => '']) === null);
check('the default message uses {name} and the sign-in link', str_contains(NotStarted::defaultMessage(), '{name}') && str_contains(NotStarted::defaultMessage(), 'academy/login'));

$n = NotStarted::remind(null, 'Hello {name}, please start your first course.', false);
$body = (string) Database::value("SELECT body FROM academy_messages WHERE learner_id = ? AND sender = 'admin' ORDER BY id DESC LIMIT 1", [$fresh], '');
check('remind: the portal message reaches the learner who has not started, with the first name filled in', $n >= 2 && str_contains($body, 'Hello Zed,'), $body);
check('remind: nobody who started, waits or is switched off was messaged', (int) Database::value('SELECT COUNT(*) FROM academy_messages WHERE learner_id IN (?, ?, ?)', [$started, $waiting, $off], 0) === 0);
check('the WhatsApp API is not set up here, so the site would use the guided list', !NotStarted::whatsAppApiReady());

// ---- the admin pages
$base = 'http://localhost/' . basename(dirname(__DIR__));
$apw = 'Not started pass 81';
\App\Models\User::create(['name' => 'ZZ Ns Admin', 'email' => 'zz-ns-admin@example.test', 'password_hash' => \App\Core\Auth::hashPassword($apw), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
Database::execute('DELETE FROM rate_limits');
$jar = tempnam(sys_get_temp_dir(), 'nsj');
$req = static function (string $method, string $path, array $form = []) use ($base, $jar): array {
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
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'body' => substr($raw, $hs), 'loc' => preg_match('/^Location:\s*(.+)$/mi', $head, $m) === 1 ? trim($m[1]) : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$page = $req('GET', '/admin/login');
$req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => 'zz-ns-admin@example.test', 'password' => $apw]);
$r = $req('GET', '/admin/academy/learners?q=zz-ns-');
check('admin: the Learners page shows the "have not started a course" panel with the e-mail and WhatsApp buttons', $r['code'] === 200 && str_contains($r['body'], 'not started a course yet') && str_contains($r['body'], 'Remind on WhatsApp') && str_contains($r['body'], 'learners-not-started-whatsapp'), (string) $r['code']);
$r = $req('GET', '/admin/academy/learners?status=notstarted&q=zz-ns-');
check('admin: the filter "Approved, no course started" lists them and not the learner who started', $r['code'] === 200 && str_contains($r['body'], 'zz-ns-fresh@') && !str_contains($r['body'], 'zz-ns-started@'), (string) $r['code']);
$r = $req('GET', '/admin/academy/learners-not-started-whatsapp');
check('admin: the WhatsApp list has one "Open WhatsApp" link per learner with a number and "start sending to all"', $r['code'] === 200 && str_contains($r['body'], 'data-wa-link') && str_contains($r['body'], 'wa.me/256772123456') && str_contains($r['body'], 'Start sending to all'), (string) $r['code']);
$r = $req('GET', '/admin/academy/learners');
$post = $req('POST', '/admin/academy/learners-not-started-whatsapp-all', ['_token' => $tok($r['body'])]);
check('admin: without the WhatsApp API, "Remind on WhatsApp" opens the guided list', $post['code'] === 302 && str_contains($post['loc'], 'learners-not-started-whatsapp'), $post['code'] . ' ' . $post['loc']);
$r = $req('GET', '/admin/academy/learners');
$req('POST', '/admin/academy/learners-remind-not-started', ['_token' => $tok($r['body']), 'message' => 'Hello {name}, start your first course today please.', 'email' => '1']);
check('admin: the send button writes the portal message (and e-mail) to the learners', (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND body LIKE '%start your first course today%'", [$fresh], 0) === 1);
$rep = \App\Services\Reports\ReportCatalog::all()['academy-not-started'] ?? null;
$res = $rep !== null ? \App\Services\Reports\ReportCatalog::run($rep, ['from' => null, 'to' => null], 300) : ['error' => 'missing', 'rows' => []];
check('the report "Approved learners who have not started a course" runs and lists the learner', $res['error'] === null && in_array('zz-ns-fresh@example.test', array_column($res['rows'], 'email'), true), (string) ($res['error'] ?? ''));
@unlink($jar);
Database::execute("DELETE FROM audit_log WHERE user_name = 'ZZ Ns Admin'");
$clean();

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL OK' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
