<?php
declare(strict_types=1);

/**
 * LAF Academy: EXAM SECURITY (timer, no copy/paste, no leaving the page, one window, flagged attempts, retakes). Included by bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-exam-security.php
 * Needs the local web server and database/updates/2026-11-exam-security.sql. Removes everything it creates (zz-exs-* learners and users, the course "zz-exam-security").
 */

use App\Core\Auth;
use App\Core\Database;
use App\Core\Settings;
use App\Models\User;
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;
use App\Services\Academy\ExamSecurity;
use App\Services\Academy\QuizScript;
use App\Services\Academy\QuizService;

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

echo "-- academy exam security --\n";
$exClean = static function (): void {
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Exs%'");
    Database::execute("DELETE FROM academy_attempts WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-exs-%')");
    Database::execute("DELETE FROM academy_enrollments WHERE learner_id IN (SELECT id FROM academy_learners WHERE email LIKE 'zz-exs-%')");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-exs-%'");
    Database::execute("DELETE FROM academy_courses WHERE slug = 'zz-exam-security'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-exs-%'");
    Database::execute('DELETE FROM rate_limits');
};
$exClean();
$exKeys = ['academy_exam_min_per_question', 'academy_exam_max_leaves', 'academy_exam_lesson_quizzes', 'academy_exam_fullscreen', 'academy_cert_approval', 'academy_register_photo_required', 'academy_cert_fee'];
$exOld = [];
foreach ($exKeys as $k) {
    $exOld[$k] = Settings::isCustomised($k) ? (string) setting($k, '') : null;
}
$exSet = static function (string $k, string $v): void {
    Settings::set($k, $v);
    Settings::flush();
};
$exSet('academy_cert_approval', '1');
$exSet('academy_register_photo_required', '0');
$exSet('academy_cert_fee', '0');
$exSet('academy_exam_min_per_question', '1');
$exSet('academy_exam_max_leaves', '3');
$exSet('academy_exam_lesson_quizzes', '0');

check('the database update is imported (exam columns, events and grants tables)', ExamSecurity::ready());

// ------------------------------------------------------------------------------------------------------------- settings
check('settings: the four exam settings exist with validation rules', (function (): bool {
    $schema = require dirname(__DIR__) . '/app/config/settings_schema.php';
    $f = $schema['groups']['academy']['fields'] ?? [];
    return isset($f['academy_exam_min_per_question']['rules'], $f['academy_exam_max_leaves']['rules'], $f['academy_exam_lesson_quizzes'], $f['academy_exam_fullscreen'])
        && str_contains($f['academy_exam_max_leaves']['rules'], 'max:10') && str_contains($f['academy_exam_min_per_question']['rules'], 'min:1');
})());
check('settings: default time = questions x minutes per question, never under 10 or over 120 minutes', ExamSecurity::defaultMinutes(4) === 10 && ExamSecurity::defaultMinutes(30) === 30 && ExamSecurity::defaultMinutes(500) === 120);
$exSet('academy_exam_min_per_question', '2');
check('settings: a different minutes-per-question changes the default', ExamSecurity::defaultMinutes(20) === 40);
$exSet('academy_exam_min_per_question', '1');
$exSet('academy_exam_max_leaves', '99');
$hi = ExamSecurity::maxLeaves();
$exSet('academy_exam_max_leaves', '0');
$lo = ExamSecurity::maxLeaves();
$exSet('academy_exam_max_leaves', '3');
check('settings: the leave limit is kept between 1 and 10', $hi === 10 && $lo === 1 && ExamSecurity::maxLeaves() === 3);

// ------------------------------------------------------------------------------------------------------------- a small course with a 4-question exam
$cid = Database::insert('academy_courses', ['slug' => 'zz-exam-security', 'title' => 'zz exam security', 'status' => 'published', 'pass_mark' => 75, 'certificate_enabled' => 1]);
$mid = Database::insert('academy_modules', ['course_id' => $cid, 'title' => 'M1']);
$lid1 = Database::insert('academy_lessons', ['course_id' => $cid, 'module_id' => $mid, 'title' => 'L1', 'body' => '<p>x</p>', 'minutes' => 5]);
$mq = QuizService::ensure($cid, $mid, 'zz module quiz', 50, null, 0);
$script = "Q: One?\n* yes\n- no\n= a\n\nQ: Two?\n* yes\n- no\n= b\n\nQ: Three?\n* yes\n- no\n= c\n\nQ: Four?\n* yes\n- no\n= d";
QuizService::replaceQuestions((int) $mq['id'], QuizScript::parse($script)['questions']);
$exam = QuizService::ensure($cid, null, 'zz exam', 75, null, 50);
QuizService::replaceQuestions((int) $exam['id'], QuizScript::parse($script)['questions']);
$exam = (array) QuizService::find((int) $exam['id']);
$mq = (array) QuizService::find((int) $mq['id']);
check('timer: an exam without its own limit gets the default (4 questions: the 10 minute minimum); a lesson quiz has none unless switched on', ExamSecurity::limitMinutes($exam) === 10 && ExamSecurity::limitMinutes($mq) === null && ExamSecurity::applies($exam) && !ExamSecurity::applies($mq));
$exSet('academy_exam_lesson_quizzes', '1');
check('settings: "apply to lesson quizzes too" switches the rules on for module quizzes', ExamSecurity::applies($mq) && ExamSecurity::limitMinutes($mq) === 10);
$exSet('academy_exam_lesson_quizzes', '0');
$setLimit = Database::one('SELECT * FROM academy_quizzes WHERE id = ?', [(int) $exam['id']]);
Database::execute('UPDATE academy_quizzes SET time_limit_min = 25 WHERE id = ?', [(int) $exam['id']]);
check('timer: a limit set on the exam (per course) wins over the default', ExamSecurity::limitMinutes((array) QuizService::find((int) $exam['id'])) === 25);
Database::execute('UPDATE academy_quizzes SET time_limit_min = NULL WHERE id = ?', [(int) $exam['id']]);

$mkL = static fn(string $tag): array => (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [(int) Database::insert('academy_learners', [
    'name' => 'ZZ Exs ' . $tag, 'email' => 'zz-exs-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword('Secret-12345'), 'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
    'phone' => '+256700000083', 'country' => 'Uganda', 'whatsapp' => '256700000083',
])]);
$la = $mkL('a');
$lidA = (int) $la['id'];
CourseService::enroll($lidA, $cid);
CourseService::completeLesson($lidA, (array) CourseService::lesson($lid1));
$right = [];
$wrong = [];
foreach (QuizService::questions((int) $exam['id']) as $q) {
    foreach ($q['options'] as $o) {
        if ((int) $o['is_correct'] === 1) {
            $right[(int) $q['id']][] = (int) $o['id'];
        } else {
            $wrong[(int) $q['id']][] = (int) $o['id'];
        }
    }
}
$fresh = static fn(int $id): array => (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [$id]);
$ago = static function (int $id, int $seconds): void {            // move the start of an attempt into the past: that many seconds of the clock are used
    Database::execute('UPDATE academy_attempts SET started_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - $seconds), $id]);
};

// ------------------------------------------------------------------------------------------------------------- over HTTP: rules screen, start, lock
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'exj');
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
    return ['code' => $code, 'head' => $head, 'body' => substr($raw, $hs), 'loc' => preg_match('/^location:\s*(\S+)/mi', $head, $m) === 1 ? $m[1] : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : (preg_match('/name="csrf-token" content="([^"]+)"/', $html, $m2) === 1 ? $m2[1] : '');
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $ts = (string) (time() - 30);
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp], $jar);
};
$jL = null;
$lp = $req('GET', '/academy/login', [], $jL);
$ts0 = (string) (time() - 30);
$li = $req('POST', '/academy/login', ['_token' => $tok($lp['body']), 'email' => 'zz-exs-a@example.test', 'password' => 'Secret-12345', '_hp_ts' => $ts0 . '.' . hash_hmac('sha256', $ts0, \App\Core\Config::appKey())], $jL);
check('http: the test learner signs in', in_array($li['code'], [302, 303], true), (string) $li['code']);

$qUrl = '/academy/learn/zz-exam-security/quiz/' . (int) $exam['id'];
$qp = $req('GET', $qUrl, [], $jL);
check('rules screen: shows the time limit, no copy/paste, no leaving the page, what happens on a violation, and the required tick box', $qp['code'] === 200
    && str_contains($qp['body'], 'Exam rules') && str_contains($qp['body'], '10 minutes') && str_contains($qp['body'], 'No copying and pasting') && str_contains($qp['body'], 'Stay on the exam page')
    && str_contains($qp['body'], 'flagged') && str_contains($qp['body'], 'I understand and agree') && preg_match('/<input type="checkbox" id="ac-agree" name="agree" value="1" required>/', $qp['body']) === 1, (string) $qp['code']);
$noAgree = $post($qUrl . '/start', [], $jL, $qUrl);
check('rules screen: pressing Start without the tick makes no attempt', in_array($noAgree['code'], [302, 303], true) && !str_contains($noAgree['loc'], '/attempt/') && (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE learner_id = ?', [$lidA], 0) === 0);
$st = $post($qUrl . '/start', ['agree' => '1'], $jL, $qUrl);
preg_match('~/attempt/(\d+)~', $st['loc'], $m);
$aid = (int) ($m[1] ?? 0);
$a1 = $fresh($aid);
check('timer: starting stores the start, the 10 minute limit and the deadline on the server, and that the learner agreed', $aid > 0 && (int) $a1['secured'] === 1 && (int) $a1['time_limit_min'] === 10 && $a1['agreed_at'] !== null
    && abs(strtotime((string) $a1['deadline_at']) - (strtotime((string) $a1['started_at']) + 600)) <= 1);
$lay = json_decode((string) $a1['layout'], true);
check('order: the questions and the answers are in a stored random order that holds every question once', count($lay['q']) === 4 && count(array_unique($lay['q'])) === 4 && count($lay['o']) === 4);

$ap = $req('GET', '/academy/learn/zz-exam-security/attempt/' . $aid, [], $jL);
$tk = (string) Database::value('SELECT token FROM academy_attempts WHERE id = ?', [$aid], '');
check('page: the exam page has the countdown (a timer role, a live announcer), the sync address, the token, the secure body class and the exam script', $ap['code'] === 200
    && str_contains($ap['body'], 'role="timer"') && str_contains($ap['body'], 'data-ex-timer="') && str_contains($ap['body'], 'data-ex-announce') && str_contains($ap['body'], 'data-ex-sync="') && $tk !== '' && str_contains($ap['body'], 'data-ex-token="' . $tk . '"')
    && str_contains($ap['body'], 'is-exam-secure') && str_contains($ap['body'], 'js/academy-exam.js') && str_contains($ap['body'], 'ac-quiz--secure') && str_contains($ap['body'], 'data-ex-warn') && str_contains($ap['body'], 'data-ex-locked'));
check('page: the correct answers are not in the page, and the question text is in the secure (no-select) area', !str_contains($ap['body'], 'is_correct') && substr_count($ap['body'], 'data-ac-question') === 4);

// second window
$jL2 = null;
$lp2 = $req('GET', '/academy/login', [], $jL2);
$req('POST', '/academy/login', ['_token' => $tok($lp2['body']), 'email' => 'zz-exs-a@example.test', 'password' => 'Secret-12345', '_hp_ts' => $ts0 . '.' . hash_hmac('sha256', $ts0, \App\Core\Config::appKey())], $jL2);
$dl = (string) $fresh($aid)['deadline_at'];
$second = $req('GET', '/academy/learn/zz-exam-security/attempt/' . $aid, [], $jL2);
check('one window: opening the same exam in a second window says it is already open elsewhere and shows no questions or timer', $second['code'] === 200 && str_contains($second['body'], 'already open in another window') && !str_contains($second['body'], 'data-ac-question') && !str_contains($second['body'], 'data-ex-timer'));
check('one window: the second window does not start a second attempt or restart the clock, and the refusal is logged', (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE learner_id = ?', [$lidA], 0) === 1 && (string) $fresh($aid)['deadline_at'] === $dl
    && (int) Database::value("SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ? AND kind = 'second_tab'", [$aid], 0) >= 1);
$st2 = $post($qUrl . '/start', ['agree' => '1'], $jL2, $qUrl);
check('resume: pressing Start again continues the SAME attempt and the same deadline (nothing resets)', str_contains($st2['loc'], '/attempt/' . $aid) && (string) $fresh($aid)['deadline_at'] === $dl && (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE learner_id = ?', [$lidA], 0) === 1);
$sub2 = $post('/academy/learn/zz-exam-security/attempt/' . $aid, ['answer' => $right, 'token' => 'not-the-token'], $jL2, $qUrl);
check('one window: the second window cannot submit the exam', $fresh($aid)['submitted_at'] === null && in_array($sub2['code'], [302, 303], true));

// sync endpoint
$syncUrl = '/academy/learn/zz-exam-security/attempt/' . $aid . '/sync';
$sync = static function (array $f) use ($post, &$jL, $qUrl, $syncUrl): array {
    $r = $post($syncUrl, $f, $jL, $qUrl);
    return (array) (json_decode($r['body'], true) ?? ['status' => 'bad:' . $r['code']]);
};
$bad = $sync(['token' => 'wrong', 'answers' => '{}']);
check('one window: a call with the wrong token is answered "locked" and saves nothing', $bad['status'] === 'locked' && $fresh($aid)['saved_answers'] === null);
$half = [];
foreach (array_slice($right, 0, 2, true) as $qid => $ids) {
    $half[$qid] = $ids;
}
$ok = $sync(['token' => $tk, 'answers' => json_encode($half)]);
$sa = json_decode((string) $fresh($aid)['saved_answers'], true);
check('autosave: the answers are stored on the server with the time, the clock is returned, and the heartbeat is kept', $ok['status'] === 'open' && is_int($ok['left']) && $ok['left'] > 590 && $ok['left'] <= 600 && count(array_filter((array) $sa)) === 2 && $fresh($aid)['saved_at'] !== null && $fresh($aid)['heartbeat_at'] !== null);
$junk = $sync(['token' => $tk, 'answers' => json_encode([999999 => [1, 2], (string) array_key_first($half) => [987654321]])]);
check('autosave: answers for other questions or options that do not exist are ignored', $junk['status'] === 'open' && !isset(json_decode((string) $fresh($aid)['saved_answers'], true)[999999]) && json_decode((string) $fresh($aid)['saved_answers'], true)[array_key_first($half)] === []);
$sync(['token' => $tk, 'answers' => json_encode($half)]);
$rel = $sync(['token' => $tk, 'event' => 'release']);
$again = $req('GET', '/academy/learn/zz-exam-security/attempt/' . $aid, [], $jL2);
check('one window: when the first window is closed (or reloaded) the exam opens at once in the other window, with the saved answers ticked and the same clock', $rel['status'] === 'open' && $again['code'] === 200 && str_contains($again['body'], 'data-ac-question') && substr_count($again['body'], ' checked') === 2 && (string) $fresh($aid)['deadline_at'] === $dl);
$tk = (string) Database::value('SELECT token FROM academy_attempts WHERE id = ?', [$aid], '');
$jL = $jL2;       // the learner carries on in the window that holds the lock now

// ------------------------------------------------------------------------------------------------------------- the exam JavaScript and CSS (string checks)
$js = (string) file_get_contents(dirname(__DIR__) . '/assets/js/academy-exam.js');
check('script: copy, cut, paste, drop, context menu, select start and drag start are blocked', preg_match("/\\['copy', 'cut', 'paste', 'drop'\\]/", $js) === 1 && str_contains($js, "'contextmenu'") && str_contains($js, "'selectstart'") && str_contains($js, "'dragstart'") && str_contains($js, 'preventDefault'));
check('script: the shortcuts Ctrl/Cmd + C X V A P U S, F12 and Ctrl+Shift+I/J/C are blocked, and print is announced', str_contains($js, "['c', 'x', 'v', 'a', 'p', 'u', 's']") && str_contains($js, "'F12'") && str_contains($js, "['i', 'j', 'c']") && str_contains($js, 'beforeprint'));
check('script: leaving is detected (visibilitychange, window blur, pagehide, full screen exit) and reported to the server', str_contains($js, 'visibilitychange') && str_contains($js, "addEventListener('blur'") && str_contains($js, 'pagehide') && str_contains($js, 'fullscreenchange') && str_contains($js, "'fullscreen_exit'") && str_contains($js, "sendEvent(event || 'leave'"));
check('script: the countdown is announced at 5 minutes and 1 minute, sends the answers at zero, autosaves and keeps a heartbeat', str_contains($js, '5 minutes left') && str_contains($js, '1 minute left') && str_contains($js, "autoSubmit('time')") && str_contains($js, 'JSON.stringify(collect())') && str_contains($js, 'heartbeat'));
$scss = (string) file_get_contents(dirname(__DIR__) . '/scss/pages/_academy.scss');
check('css: question text cannot be selected during an exam and printing shows only a notice', str_contains($scss, 'user-select: none') && str_contains($scss, '@media print') && str_contains($scss, 'Printing is switched off during the exam'));
check('css: the compiled stylesheet has the same rules', str_contains((string) file_get_contents(dirname(__DIR__) . '/assets/css/main.css'), 'ac-exam-panel') || str_contains((string) file_get_contents(dirname(__DIR__) . '/assets/css/main.min.css'), 'ac-exam-panel'));
check('script: the exam page and the script are served', $req('GET', '/assets/js/academy-exam.js', [], $jL)['code'] === 200);

// ------------------------------------------------------------------------------------------------------------- the deadline on the server
$ago($aid, 610);          // 10 s past the deadline, inside the 30 s grace
$a = $fresh($aid);
check('timer: ten seconds past the limit is still inside the grace', QuizService::secondsLeft($a, $exam) < 0 && QuizService::secondsLeft($a, $exam) > -ExamSecurity::GRACE_SECONDS);
$subOk = $post('/academy/learn/zz-exam-security/attempt/' . $aid, ['answer' => $half, 'token' => $tk, 'reason' => 'time'], $jL, $qUrl);
$done = $fresh($aid);
check('timer: when time runs out the page sends the answers and they are graded (inside the grace)', $done['submitted_at'] !== null && (int) $done['correct'] === 2 && $done['submit_reason'] === 'time' && (int) $done['flagged'] === 0);
check('timer: an attempt that is submitted stops the lock and cannot be graded again', $done['heartbeat_at'] === null && QuizService::submit($done, $exam, $wrong)['correct'] === $done['correct']);

// late submit: answers after the grace are ignored, the saved answers are graded
$exam = (array) QuizService::find((int) $exam['id']);
$bLate = QuizService::start($lidA, $exam, true);
$bid = (int) $bLate['id'];
ExamSecurity::claim($bLate);
QuizService::saveAnswers($fresh($bid), $exam, $half);
$ago($bid, 600 + 120);
$res = QuizService::submit($fresh($bid), $exam, $right);
check('timer: answers sent more than 30 seconds after the deadline are ignored; the autosaved answers (2 of 4) are graded', (int) $res['correct'] === 2 && $res['submit_reason'] === 'late' && (int) $res['passed'] === 0);
check('timer: after the grace the server also refuses to autosave', (function () use ($exam, $lidA, $half, $fresh, $ago): bool {
    $c = QuizService::start($lidA, $exam, true);
    $ago((int) $c['id'], 600 + 200);
    $saved = QuizService::saveAnswers($fresh((int) $c['id']), $exam, $half);
    $ok = $saved === false;
    QuizService::submit($fresh((int) $c['id']), $exam, null);
    return $ok;
})());

// a sync after the end grades the saved answers
$cAt = QuizService::start($lidA, $exam, true);
$cid2 = (int) $cAt['id'];
$claim = ExamSecurity::claim($cAt);
QuizService::saveAnswers($fresh($cid2), $exam, $half);
$ago($cid2, 600 + 100);
$late = ExamSecurity::sync($fresh($cid2), $exam, $claim['token'], null);
check('timer: a call after the deadline plus grace closes the attempt and grades what was saved (the learner cannot keep it open)', $late['status'] === 'submitted' && (int) $fresh($cid2)['correct'] === 2 && $fresh($cid2)['submit_reason'] === 'time');

// an abandoned attempt cannot be used to reset the clock
$dAt = QuizService::start($lidA, $exam, true);
$ago((int) $dAt['id'], 600 + 500);
$used = QuizService::attemptsUsed($lidA, (int) $exam['id']);
$newAt = QuizService::start($lidA, $exam, true);
check('timer: an attempt abandoned past its time is graded (it counts as used), and the next attempt gets a new clock', (int) $newAt['id'] !== (int) $dAt['id'] && $fresh((int) $dAt['id'])['submitted_at'] !== null && QuizService::attemptsUsed($lidA, (int) $exam['id']) === $used + 1);
QuizService::submit($fresh((int) $newAt['id']), $exam, null);

// ------------------------------------------------------------------------------------------------------------- leaving the page: warnings and the automatic submit
$vAt = QuizService::start($lidA, $exam, true);
$vid = (int) $vAt['id'];
$cl = ExamSecurity::claim($vAt);
$vt = $cl['token'];
QuizService::saveAnswers($fresh($vid), $exam, $right);
$s1 = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'leave', 0, 'Switched to another tab');
$s1b = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'blur', 0, '');
$s1c = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'leave', 0, 'blur right after');
check('violations: the first leave is counted and recorded with its time, the countdown carries on (first warning)', $s1['status'] === 'open' && $s1['leaves'] === 1 && $s1['max'] === 3 && $fresh($vid)['submitted_at'] === null && (int) Database::value("SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ? AND kind = 'leave' AND counted = 1", [$vid], 0) === 1);
check('violations: blur and visibility events of the same leave (within 3 seconds) are one leave, not several', $s1c['leaves'] === 1 && (int) $fresh($vid)['leave_count'] === 1);
$ret = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'return', 42);
check('violations: coming back records how long the learner was away (not counted)', $ret['status'] === 'open' && (int) Database::value("SELECT seconds_away FROM academy_exam_events WHERE attempt_id = ? AND kind = 'return'", [$vid], 0) === 42);
Database::execute('UPDATE academy_exam_events SET created_at = DATE_SUB(created_at, INTERVAL 20 SECOND) WHERE attempt_id = ?', [$vid]);
$s2 = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'leave', 0, 'again');
check('violations: the second leave is counted (final warning), still not submitted', $s2['status'] === 'open' && $s2['leaves'] === 2 && $fresh($vid)['submitted_at'] === null);
Database::execute('UPDATE academy_exam_events SET created_at = DATE_SUB(created_at, INTERVAL 20 SECOND) WHERE attempt_id = ?', [$vid]);
$s3 = ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'fullscreen_exit', 0, 'Left full screen');
$vd = $fresh($vid);
check('violations: the third leave (the limit) submits the exam automatically with the answers so far and marks it flagged', $s3['status'] === 'submitted' && $vd['submitted_at'] !== null && (int) $vd['flagged'] === 1 && $vd['submit_reason'] === 'violations' && (int) $vd['leave_count'] === 3 && (int) $vd['correct'] === 4
    && (int) Database::value("SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ? AND kind = 'auto_submit'", [$vid], 0) === 1);
check('violations: a call after the auto-submit is answered "submitted"', ExamSecurity::sync($fresh($vid), $exam, $vt, null, 'leave')['status'] === 'submitted');

// ------------------------------------------------------------------------------------------------------------- a flagged attempt does not give a certificate by itself
check('flagged: a passing but flagged attempt does not count (no pass, no completion, no certificate request)', QuizService::passedBest($lidA, (int) $exam['id']) === null && CourseService::completion($lidA, $cid) === null
    && CertificateService::existingFor($lidA, $cid) === null && !CertificateService::eligible($lidA, $cid) && ExamSecurity::blocksCertificate($vd));
$resv = $req('GET', '/academy/learn/zz-exam-security/attempt/' . $vid, [], $jL);
check('flagged: the learner sees that it was stopped, that it is flagged and under review - not "you passed", no request button', $resv['code'] === 200 && str_contains($resv['body'], 'flagged') && str_contains($resv['body'], 'Submitted for review') && !str_contains($resv['body'], 'Well done, you passed') && !str_contains($resv['body'], 'Request my certificate'));
$qv = $req('GET', $qUrl, [], $jL);
check('flagged: the exam page says the last attempt is under review and does not say "You passed"', str_contains($qv['body'], 'will review it') && !str_contains($qv['body'], 'You passed this exam'));

// ------------------------------------------------------------------------------------------------------------- administrator: review, details, retake
$pwA = 'Admin exs pass 91';
$pwE = 'Editor exs pass 94';
$mkU = static fn(string $email, string $role, string $pw): int => (int) User::create(['name' => 'ZZ Exs ' . $role, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$adminId = $mkU('zz-exs-admin@example.test', 'admin', $pwA);
$mkU('zz-exs-editor@example.test', 'editor', $pwE);
$jA = $jE = null;
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    $r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
    return in_array($r['code'], [302, 303], true);
};
check('admin: the test administrator and editor sign in', $login('zz-exs-admin@example.test', $pwA, $jA) && $login('zz-exs-editor@example.test', $pwE, $jE));
$cp = $req('GET', '/admin/academy/certificates', [], $jA);
check('admin: Certificates lists the flagged exam with the score, the time taken, the number of times the learner left and the Clear / Reject buttons', $cp['code'] === 200 && str_contains($cp['body'], 'Flagged exams to review') && str_contains($cp['body'], 'ZZ Exs a') && str_contains($cp['body'], 'Time taken') && str_contains($cp['body'], 'Left the page') && str_contains($cp['body'], 'Clear: it counts') && str_contains($cp['body'], 'exam events'));
$lp = $req('GET', '/admin/academy/learners/' . $lidA, [], $jA);
check('admin: the learner page shows each exam attempt with its duration, the leave count, the flagged state, the event log and the retake form', $lp['code'] === 200 && str_contains($lp['body'], 'Time taken') && str_contains($lp['body'], 'Flagged - to review') && str_contains($lp['body'], 'Left the page') && str_contains($lp['body'], 'Retake or extra time') && str_contains($lp['body'], 'Submitted automatically'));
$ed = $post('/admin/academy/exam-attempts/' . $vid . '/review', ['decision' => 'clear'], $jE, '/admin');
check('admin: an editor (no academy.manage) cannot review a flagged exam (403) and nothing changes', $ed['code'] === 403 && $fresh($vid)['flag_review'] === null);
$edg = $post('/admin/academy/learners/' . $lidA . '/exam-grant', ['quiz_id' => (int) $exam['id'], 'extra_attempts' => 1, 'extra_minutes' => 5], $jE, '/admin');
check('admin: an editor cannot give a retake either (403)', $edg['code'] === 403 && (int) Database::value('SELECT COUNT(*) FROM academy_exam_grants WHERE learner_id = ?', [$lidA], 0) === 0);
$rj = $post('/admin/academy/exam-attempts/' . $vid . '/review', ['decision' => 'reject'], $jA, '/admin/academy/certificates');
check('admin: Reject keeps the attempt out of the count for good (no certificate)', in_array($rj['code'], [302, 303], true) && $fresh($vid)['flag_review'] === 'rejected' && QuizService::passedBest($lidA, (int) $exam['id']) === null && CertificateService::existingFor($lidA, $cid) === null);

// a second flagged attempt that the administrator clears
$exam = (array) QuizService::find((int) $exam['id']);
$wAt = QuizService::start($lidA, $exam, true);
$wid = (int) $wAt['id'];
$wc = ExamSecurity::claim($wAt);
QuizService::saveAnswers($fresh($wid), $exam, $right);
foreach ([1, 2, 3] as $n) {
    Database::execute('UPDATE academy_exam_events SET created_at = DATE_SUB(created_at, INTERVAL 30 SECOND) WHERE attempt_id = ?', [$wid]);
    ExamSecurity::sync($fresh($wid), $exam, $wc['token'], null, 'leave', 0, 'x');
}
check('flagged: a second attempt is stopped and flagged the same way', (int) $fresh($wid)['flagged'] === 1 && $fresh($wid)['flag_review'] === null);
$cl2 = $post('/admin/academy/exam-attempts/' . $wid . '/review', ['decision' => 'clear'], $jA, '/admin/academy/certificates');
$wait = CertificateService::pendingFor($lidA, $cid);
check('admin: Clear makes it count and files the certificate for approval as for any passed exam (waiting, not issued by itself)', in_array($cl2['code'], [302, 303], true) && $fresh($wid)['flag_review'] === 'cleared' && (int) $fresh($wid)['flag_reviewed_by'] === $adminId
    && $wait !== null && CertificateService::activeFor($lidA, $cid) === null);
check('admin: the certificate list shows the exam duration and leave count of the learner next to the certificate', str_contains($req('GET', '/admin/academy/certificates?status=waiting', [], $jA)['body'], 'left the page 3x'));
check('admin: the review is written to the audit log', (int) Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.exam.review'", [], 0) >= 1);

// retake and extra time
$extraBefore = QuizService::canStart($lidA, (array) QuizService::find((int) $exam['id']));
$g = $post('/admin/academy/learners/' . $lidA . '/exam-grant', ['quiz_id' => (int) $exam['id'], 'extra_attempts' => 2, 'extra_minutes' => 7, 'note' => 'power cut'], $jA, '/admin/academy/learners/' . $lidA);
$exam = (array) QuizService::find((int) $exam['id']);
$canNow = QuizService::canStart($lidA, $exam);
check('admin: giving extra attempts raises the learner\'s limit for that exam', in_array($g['code'], [302, 303], true) && ExamSecurity::extraAttempts($lidA, (int) $exam['id']) === 2 && ($canNow['left'] ?? 0) === ($extraBefore['left'] ?? 0) + 2);
$nAt = QuizService::start($lidA, $exam, true);
check('admin: extra minutes are added to the next attempt (10 + 7 = 17 minutes) and used up', (int) $nAt['extra_minutes'] === 7 && abs(strtotime((string) $nAt['deadline_at']) - strtotime((string) $nAt['started_at']) - 17 * 60) <= 1 && ExamSecurity::pendingExtraMinutes($lidA, (int) $exam['id']) === 0
    && QuizService::secondsLeft($nAt, $exam) > 16 * 60);
ExamSecurity::grant($lidA, (int) $exam['id'], 0, 5, null, $adminId);
$ext = $fresh((int) $nAt['id']);
check('admin: extra minutes given while an exam is open extend that exam at once (and keep the deadline on the server)', (int) $ext['extra_minutes'] === 12 && QuizService::secondsLeft($ext, $exam) > 21 * 60 && QuizService::secondsLeft($ext, $exam) <= 22 * 60 && abs(strtotime((string) $ext['deadline_at']) - strtotime((string) $ext['started_at']) - 22 * 60) <= 1);
QuizService::submit($ext, $exam, null);

// ------------------------------------------------------------------------------------------------------------- older attempts and other quizzes keep working
$lq = QuizService::start($lidA, $mq, false);
check('compat: a lesson quiz (rules off) starts without agreeing, has no limit and no lock', (int) $lq['secured'] === 0 && $lq['deadline_at'] === null && $lq['time_limit_min'] === null && QuizService::secondsLeft($lq, $mq) === null);
$resq = QuizService::submit($lq, $mq, $right);
check('compat: it is graded as before', $resq['submitted_at'] !== null && $resq['submit_reason'] === 'manual' && (int) $resq['flagged'] === 0);
Database::execute('INSERT INTO academy_attempts (learner_id, quiz_id, course_id, layout) VALUES (?, ?, ?, ?)', [$lidA, (int) $exam['id'], $cid, json_encode(['q' => array_keys($right), 'o' => []])]);
$oldId = (int) Database::value('SELECT MAX(id) FROM academy_attempts WHERE learner_id = ?', [$lidA], 0);
$oldRow = $fresh($oldId);
check('compat: an attempt made before the update (no stored limit, not secured) still resumes and is graded', (int) $oldRow['secured'] === 0 && QuizService::secondsLeft($oldRow, $exam) === null && QuizService::submit($oldRow, $exam, $right)['submitted_at'] !== null);
$exSet('academy_exam_lesson_quizzes', '1');
$lq2 = QuizService::start($lidA, $mq, true);
check('settings: with "apply to lesson quizzes" on, a lesson quiz attempt is secured and timed', (int) $lq2['secured'] === 1 && (int) $lq2['time_limit_min'] === 10);
QuizService::submit($lq2, $mq, null);
$exSet('academy_exam_lesson_quizzes', '0');
$ev = Database::value('SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ?', [$vid], 0);
check('events: every leave and return was recorded with a time', (int) $ev >= 5 && (int) Database::value('SELECT COUNT(*) FROM academy_exam_events WHERE attempt_id = ? AND created_at IS NOT NULL', [$vid], 0) === (int) $ev);

// ------------------------------------------------------------------------------------------------------------- docs and schema
$docs = (string) file_get_contents(dirname(__DIR__) . '/docs/ACADEMY.md');
check('docs: ACADEMY.md has the exam integrity section with the honest note about limits', str_contains($docs, 'Exam integrity') && str_contains($docs, 'cannot be made 100%') && str_contains($docs, '2026-11-exam-security.sql'));
$schema = (string) file_get_contents(dirname(__DIR__) . '/database/schema.sql');
$import = (string) file_get_contents(dirname(__DIR__) . '/database/lumina-live-import.sql');
check('database: the schema, the update file and the live import all hold the exam tables, and the update is idempotent (guarded ALTER, IF NOT EXISTS)', str_contains($schema, 'academy_exam_events') && str_contains($import, 'academy_exam_events') && str_contains($import, 'academy_exam_grants')
    && str_contains((string) file_get_contents(dirname(__DIR__) . '/database/updates/2026-11-exam-security.sql'), "column_name = 'deadline_at'") && str_contains($import, "column_name = 'deadline_at'"));

// ------------------------------------------------------------------------------------------------------------- clean up
foreach ($exOld as $k => $v) {
    $v === null ? Settings::reset($k) : Settings::set($k, $v);
}
Settings::flush();
$exClean();
check('cleanup: no zz-exs rows are left', (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-exs-%'", [], 0) === 0 && (int) Database::value("SELECT COUNT(*) FROM academy_courses WHERE slug = 'zz-exam-security'", [], 0) === 0
    && (int) Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-exs-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : $fail . ' CHECK(S) FAILED') . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
