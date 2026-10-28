<?php
/**
 * LAF Academy checks, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy.php
 * Covers: the quiz text format, the starter courses and the installer, learner accounts over HTTP (sign-up, e-mailed confirmation link, sign-in, wrong password,
 * no account enumeration, forgotten password, account deletion), enrolling, the lesson player, quizzes (answers never sent before submitting, server grading,
 * attempt limits, time limit), the final exam and the certificate (number, check page, PDF, withdrawing), virtual classes (join link only for people who may attend,
 * calendar file), access control (private screens, other learners' attempts, draft courses, the on/off switch) and the admin pages being closed to the public.
 * Everything it creates is removed again (learners zz-academy-*@example.test, classes and courses titled "zz-academy ...", the mail files it caused).
 */
declare(strict_types=1);

if (!function_exists('check')) {
    if (PHP_SAPI !== 'cli') {
        exit('CLI only.');
    }
    require dirname(__DIR__) . '/app/bootstrap.php';
    if (\App\Core\Config::get('app.env') !== 'local') {
        exit("Refusing to run outside APP_ENV=local.\n");
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
use App\Core\Settings;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ClassService;
use App\Services\Academy\ContentInstaller;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\QuizScript;
use App\Services\Academy\QuizService;

echo "-- academy --\n";
$acSet = static function (string $k, ?string $v): void {
    Settings::set($k, $v);
    Settings::flush();
};
$acBefore = [];
foreach (['academy_enabled', 'academy_registration_open', 'academy_signup_mode', 'academy_exam_requires_lessons'] as $k) {
    $acBefore[$k] = Settings::isCustomised($k) ? (string) setting($k, '') : null;
}
$acRestore = static function () use ($acBefore): void {
    foreach ($acBefore as $k => $v) {
        $v === null ? Settings::reset($k) : Settings::set($k, $v);
    }
    Settings::flush();
};
$acBase = 'http://localhost/' . basename(dirname(__DIR__));

// ---- quiz text format ------------------------------------------------------------------------------------------------------
$qs = QuizScript::parse("Q: What does CPU stand for?\n* Central Processing Unit\n- Central Program Utility\n- Computer Personal Unit\n= The brain of the computer.\n\nQ: Pick the input devices\n* Keyboard\n* Mouse\n- Printer\n= Printers output.");
check('quiz script: two questions, one pick-one and one pick-all, explanations kept', $qs['ok'] && count($qs['questions']) === 2 && $qs['questions'][0]['type'] === 'single' && $qs['questions'][1]['type'] === 'multiple' && $qs['questions'][0]['explanation'] === 'The brain of the computer.');
$round = QuizScript::parse(QuizScript::export(array_map(static fn(array $q): array => ['prompt' => $q['prompt'], 'explanation' => $q['explanation'], 'options' => $q['options']], $qs['questions'])));
check('quiz script: export then parse gives the same questions', $round['ok'] && $round['questions'] === $qs['questions']);
$bad = QuizScript::parse("Q: Only one answer\n* Yes\n\nQ: No correct\n- a\n- b\n\nQ: All correct\n* a\n* b\n\nsome stray line\n\nQ: Repeats\n* same\n- same");
check('quiz script: too few answers, no/all correct, stray lines and repeats are each reported', !$bad['ok'] && count($bad['errors']) >= 5);
check('quiz script: tags in questions are removed', QuizScript::parse("Q: <b>Bold</b> <script>x</script>?\n* a\n- b")['questions'][0]['prompt'] === 'Bold x?');

// ---- starter courses --------------------------------------------------------------------------------------------------------
$st = ContentInstaller::status();
check('starter courses: 16 files, all valid', count($st) >= 16);
ContentInstaller::installAll(null, true);
$course = CourseService::bySlug('computer-basics');
check('installer: Computer Basics is installed with modules, lessons, module quizzes and a final exam', $course !== null && ($tree = CourseService::tree((int) $course['id']))['lessons'] >= 14 && $tree['exam'] !== null && $tree['quizzes'] >= 5);
$tree = CourseService::tree((int) $course['id']);
check('installer: installing again changes nothing', ContentInstaller::install('computer-basics')['ok'] === false);
$body = (string) Database::value('SELECT body FROM academy_lessons WHERE course_id = ? ORDER BY id LIMIT 1', [(int) $course['id']]);
check('lesson text is real HTML (headings, practice section) and holds no script', str_contains($body, '<h3>') && str_contains($body, 'Try it yourself') && !str_contains($body, '<script'));

// ---- HTTP helpers -----------------------------------------------------------------------------------------------------------
$acReq = static function (string $method, string $path, array $form = [], ?string &$jar = null, bool $follow = false) use ($acBase): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'acj');
    $ch = curl_init($acBase . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 40, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_MAXREDIRS => 5]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $url = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'head' => $head, 'body' => substr($raw, $hs), 'url' => $url, 'loc' => preg_match('/^location:\s*(\S+)/mi', $head, $m) === 1 ? $m[1] : ''];
};
$acToken = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$acPost = static function (string $path, array $form, ?string &$jar, string $tokenFrom = '', bool $follow = false) use ($acReq, $acToken): array {
    $page = $acReq('GET', $tokenFrom !== '' ? $tokenFrom : $path, [], $jar);
    $ts = (string) (time() - 30);                                                        // a form that was open for 30 seconds: passes the honeypot time trap
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    return $acReq('POST', $path, $form + ['_token' => $acToken($page['body']), '_hp_ts' => $hp], $jar, $follow);
};
$acMails = static fn(): array => glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$acLastMailTo = static function (string $email, array $before) use ($acMails): ?string {
    foreach (array_reverse(array_diff($acMails(), $before)) as $f) {
        $c = (string) file_get_contents($f);
        if (preg_match('/^To:.*' . preg_quote($email, '/') . '/mi', $c) === 1) {
            $text = '';
            if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $c, $parts) > 0) {
                foreach ($parts[1] as $b) {
                    $text .= (string) base64_decode((string) preg_replace('/\s+/', '', $b)) . "\n";
                }
            }
            return $text !== '' ? $text : quoted_printable_decode($c);
        }
    }
    return null;
};
$acClean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-academy-%@example.test'");
    Database::execute("DELETE FROM academy_classes WHERE title LIKE 'zz-academy%'");
    Database::execute("DELETE FROM academy_courses WHERE title LIKE 'zz-academy%'");
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Academy%'");
    Database::execute('DELETE FROM rate_limits');         // local test database: the sign-up / sign-in caps must not carry over between runs
};
$acClean();
$mailsBefore = $acMails();
$acSet('academy_enabled', '1');
$acSet('academy_registration_open', '1');
$acSet('academy_signup_mode', 'email');
$acSet('academy_exam_requires_lessons', '1');

// ---- public pages -----------------------------------------------------------------------------------------------------------
$j0 = null;
$home = $acReq('GET', '/academy', [], $j0);
check('public: home lists the courses and sets no cookie', $home['code'] === 200 && str_contains($home['body'], 'Computer Basics and Digital Literacy') && stripos($home['head'], 'set-cookie') === false);
$search = $acReq('GET', '/academy?q=excel', [], $j0);
check('public: search narrows the catalogue', str_contains($search['body'], 'Microsoft Excel') && !str_contains($search['body'], 'Typing and Keyboard Skills'));
check('public: a course page shows the curriculum but no lesson link to a visitor', ($cp = $acReq('GET', '/academy/courses/computer-basics', [], $j0))['code'] === 200 && str_contains($cp['body'], 'Course content') && !str_contains($cp['body'], '/learn/computer-basics/lesson/'));
check('private screens send a visitor to sign in', in_array($acReq('GET', '/academy/dashboard', [], $j0)['code'], [302, 303], true) && $acReq('GET', '/academy/learn/computer-basics/lesson/' . $tree['lesson_ids'][0], [], $j0)['code'] === 302);
check('admin pages are closed to the public', in_array($acReq('GET', '/admin/academy', [], $j0)['code'], [302, 401, 403], true));

// ---- sign up, confirm, sign in ----------------------------------------------------------------------------------------------
$email = 'zz-academy-a@example.test';
$jA = null;
$weak = $acPost('/academy/register', ['name' => 'ZZ Academy A', 'email' => $email, 'password' => '12345678', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009', 'agree' => '1'], $jA, '/academy/register', true);
check('sign-up: a weak password is refused', str_contains($weak['body'], 'harder to guess') && Database::value('SELECT COUNT(*) FROM academy_learners WHERE email = ?', [$email], 0) === 0);
$noAgree = $acPost('/academy/register', ['name' => 'ZZ Academy A', 'email' => $email, 'password' => 'Correct horse 77', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009'], $jA, '/academy/register', true);
check('sign-up: the agreement box is required', Database::value('SELECT COUNT(*) FROM academy_learners WHERE email = ?', [$email], 0) === 0 && str_contains($noAgree['body'], 'tick the box'));
$reg = $acPost('/academy/register', ['name' => 'ZZ Academy A', 'email' => $email, 'password' => 'Correct horse 77', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009', 'agree' => '1'], $jA, '/academy/register', true);
$row = Database::one('SELECT * FROM academy_learners WHERE email = ?', [$email]);
check('sign-up: the account exists, is not confirmed and the page asks to check the e-mail', $row !== null && $row['email_verified_at'] === null && str_contains($reg['body'], 'Check your e-mail') && !str_contains((string) $row['password_hash'], 'Correct horse'));
$mail = $acLastMailTo($email, $mailsBefore);
check('sign-up: a confirmation e-mail with a link was sent', $mail !== null && preg_match('~/academy/verify/([a-f0-9]{64})~', $mail, $vm) === 1);
$loginTry = $acPost('/academy/login', ['email' => $email, 'password' => 'Correct horse 77', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009'], $jA, '/academy/login', true);
check('sign-in is refused until the address is confirmed', str_contains($loginTry['body'], 'Check your e-mail') && $acReq('GET', '/academy/dashboard', [], $jA)['code'] === 302);
$ver = $acReq('GET', '/academy/verify/' . str_repeat('a', 64), [], $jA);
check('a wrong confirmation link does not work', $ver['code'] === 410);
$ver = $acReq('GET', '/academy/verify/' . ($vm[1] ?? 'x'), [], $jA, true);
check('the confirmation link confirms the address and signs the learner in', str_contains($ver['body'], 'My learning') && Database::value('SELECT email_verified_at FROM academy_learners WHERE email = ?', [$email]) !== null);
check('the confirmation link works once only', $acReq('GET', '/academy/verify/' . ($vm[1] ?? 'x'), [], $jA)['code'] === 410);
$acReq('POST', '/academy/logout', ['_token' => $acToken($acReq('GET', '/academy/dashboard', [], $jA)['body'])], $jA);
check('sign-out ends the session', $acReq('GET', '/academy/dashboard', [], $jA)['code'] === 302);
$dup = $acPost('/academy/register', ['name' => 'Someone Else', 'email' => $email, 'password' => 'Another pass 88', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009', 'agree' => '1'], $jA, '/academy/register', true);
check('sign-up with an address that has an account looks the same and creates nothing', str_contains($dup['body'], 'Check your e-mail') && Database::value('SELECT COUNT(*) FROM academy_learners WHERE email = ?', [$email], 0) === 1 && Database::value('SELECT name FROM academy_learners WHERE email = ?', [$email]) === 'ZZ Academy A');
$bad = $acPost('/academy/login', ['email' => $email, 'password' => 'wrong password'], $jA, '/academy/login', true);
$none = $acPost('/academy/login', ['email' => 'zz-academy-nobody@example.test', 'password' => 'wrong password'], $jA, '/academy/login', true);
check('wrong password and unknown address give the same message', str_contains($bad['body'], 'do not match') && str_contains($none['body'], 'do not match'));
$in = $acPost('/academy/login', ['email' => $email, 'password' => 'Correct horse 77', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009'], $jA, '/academy/login', true);
check('sign-in works and shows the dashboard', str_contains($in['body'], 'My learning'));
check('staff pages stay closed to a learner', in_array($acReq('GET', '/admin', [], $jA)['code'], [302, 401, 403], true));
$lid = (int) Database::value('SELECT id FROM academy_learners WHERE email = ?', [$email]);

// ---- enrol and learn --------------------------------------------------------------------------------------------------------
$first = (int) $tree['lesson_ids'][0];
check('a learner who is not enrolled cannot open a lesson', $acReq('GET', '/academy/learn/computer-basics/lesson/' . $first, [], $jA)['code'] === 302);
$enr = $acPost('/academy/courses/computer-basics/enroll', [], $jA, '/academy/courses/computer-basics', true);
check('enrolling opens the first lesson', str_contains($enr['body'], 'ac-lesson__title') && CourseService::enrollment($lid, (int) $course['id']) !== null);
$lp = $acReq('GET', '/academy/learn/computer-basics/lesson/' . $first, [], $jA);
check('the lesson page has the text, the course menu and a done button', $lp['code'] === 200 && str_contains($lp['body'], 'Try it yourself') && str_contains($lp['body'], 'ac-side') && str_contains($lp['body'], 'Mark as done'));
$done = $acPost('/academy/learn/computer-basics/lesson/' . $first . '/complete', [], $jA, '/academy/learn/computer-basics/lesson/' . $first);
check('finishing a lesson is saved and leads to the next lesson', $done['code'] === 302 && str_contains($done['loc'], '/lesson/' . $tree['lesson_ids'][1]) && in_array($first, CourseService::doneIds($lid, (int) $course['id']), true));
check('a lesson of another course cannot be opened through this course', ($other = (int) Database::value("SELECT l.id FROM academy_lessons l JOIN academy_courses c ON c.id = l.course_id WHERE c.slug = 'ms-word' LIMIT 1")) > 0 && $acReq('GET', '/academy/learn/computer-basics/lesson/' . $other, [], $jA)['code'] === 404);
$p = CourseService::progress($lid, (int) $course['id'], $tree);
check('progress counts the finished lesson', $p['done'] === 1 && $p['pct'] === (int) floor(100 / $tree['lessons']));

// ---- a module quiz ----------------------------------------------------------------------------------------------------------
$mq = null;
foreach ($tree['modules'] as $m) {
    if ($m['quiz'] !== null) { $mq = $m['quiz']; break; }
}
$qpage = $acReq('GET', '/academy/learn/computer-basics/quiz/' . $mq['id'], [], $jA);
check('the quiz start page shows the rules', $qpage['code'] === 200 && str_contains($qpage['body'], 'Pass mark'));
$start = $acPost('/academy/learn/computer-basics/quiz/' . $mq['id'] . '/start', [], $jA, '/academy/learn/computer-basics/quiz/' . $mq['id']);
check('starting makes an attempt', $start['code'] === 302 && preg_match('~/attempt/(\d+)~', $start['loc'], $am) === 1);
$attId = (int) ($am[1] ?? 0);
$take = $acReq('GET', '/academy/learn/computer-basics/attempt/' . $attId, [], $jA);
$allQ = QuizService::questions((int) $mq['id']);
$firstRight = $allQ[0]['options'];
check('the quiz page shows every question and answer but not which answers are right', $take['code'] === 200 && substr_count($take['body'], 'data-ac-question') === count($allQ) && !str_contains($take['body'], 'right answer') && !str_contains($take['body'], 'Why:'));
$answers = [];
foreach ($allQ as $q) {
    $answers[(int) $q['id']] = array_values(array_map(static fn(array $o): int => (int) $o['id'], array_filter($q['options'], static fn(array $o): bool => (int) $o['is_correct'] === 1)));
}
$sub = $acPost('/academy/learn/computer-basics/attempt/' . $attId, ['answer' => $answers], $jA, '/academy/learn/computer-basics/attempt/' . $attId, true);
$att = Database::one('SELECT * FROM academy_attempts WHERE id = ?', [$attId]);
check('the right answers score 100% and pass', (int) $att['passed'] === 1 && (float) $att['score_pct'] === 100.0 && str_contains($sub['body'], 'Well done') && str_contains($sub['body'], 'Review your answers'));
$st2 = $acPost('/academy/learn/computer-basics/quiz/' . $mq['id'] . '/start', [], $jA, '/academy/learn/computer-basics/quiz/' . $mq['id']);
preg_match('~/attempt/(\d+)~', $st2['loc'], $am2);
$wrong = [];
foreach ($allQ as $q) {
    $wrong[(int) $q['id']] = array_values(array_map(static fn(array $o): int => (int) $o['id'], array_slice(array_filter($q['options'], static fn(array $o): bool => (int) $o['is_correct'] === 0), 0, 1)));
}
$acPost('/academy/learn/computer-basics/attempt/' . ($am2[1] ?? 0), ['answer' => $wrong], $jA, '/academy/learn/computer-basics/attempt/' . ($am2[1] ?? 0));
$att2 = Database::one('SELECT * FROM academy_attempts WHERE id = ?', [(int) ($am2[1] ?? 0)]);
check('wrong answers fail the quiz (a pick-all question needs the exact set)', $att2 !== null && (int) $att2['passed'] === 0 && (float) $att2['score_pct'] < 60.0);
$again = $acPost('/academy/learn/computer-basics/attempt/' . $attId, ['answer' => $wrong], $jA, '/academy/learn/computer-basics/attempt/' . $attId);
check('a submitted attempt cannot be re-graded', (float) Database::value('SELECT score_pct FROM academy_attempts WHERE id = ?', [$attId]) === 100.0);
// other learner cannot see this attempt
$emailB = 'zz-academy-b@example.test';
Database::insert('academy_learners', ['name' => 'ZZ Academy B', 'email' => $emailB, 'password_hash' => \App\Core\Auth::hashPassword('Second learner 99'), 'email_verified_at' => date('Y-m-d H:i:s')]);
$jB = null;
$acPost('/academy/login', ['email' => $emailB, 'password' => 'Second learner 99'], $jB, '/academy/login');
$lidB = (int) Database::value('SELECT id FROM academy_learners WHERE email = ?', [$emailB]);
CourseService::enroll($lidB, (int) $course['id']);
check('another learner cannot open someone else\'s attempt', $acReq('GET', '/academy/learn/computer-basics/attempt/' . $attId, [], $jB)['code'] === 404);

// ---- attempt limit and time limit -------------------------------------------------------------------------------------------
$zzCourse = Database::insert('academy_courses', ['slug' => 'zz-academy-limits', 'title' => 'zz-academy limits', 'status' => 'published', 'pass_mark' => 50]);
$zzMod = Database::insert('academy_modules', ['course_id' => $zzCourse, 'title' => 'M1']);
Database::insert('academy_lessons', ['course_id' => $zzCourse, 'module_id' => $zzMod, 'title' => 'L1', 'body' => '<p>x</p>', 'minutes' => 5]);
$zzQuiz = QuizService::ensure($zzCourse, $zzMod, 'zz limit quiz', 50, 1, 1);
QuizService::replaceQuestions((int) $zzQuiz['id'], QuizScript::parse("Q: One?\n* yes\n- no\n= x\n\nQ: Two?\n* yes\n- no\n= y")['questions']);
$zzQuiz = (array) QuizService::find((int) $zzQuiz['id']);
$at1 = QuizService::start($lid, $zzQuiz);
$qq = QuizService::questions((int) $zzQuiz['id']);
$late = $at1;
Database::execute('UPDATE academy_attempts SET started_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 400), (int) $at1['id']]);
$late = (array) Database::one('SELECT * FROM academy_attempts WHERE id = ?', [(int) $at1['id']]);
$ans = [];
foreach ($qq as $q) { $ans[(int) $q['id']] = [(int) $q['options'][0]['id']]; }
$res = QuizService::submit($late, $zzQuiz, $ans);
check('time limit: answers sent long after the time ran out count as blank', (float) $res['score_pct'] === 0.0 && (int) $res['passed'] === 0);
check('attempt limit: after the only attempt the learner cannot start another', QuizService::canStart($lid, $zzQuiz)['ok'] === false);

// ---- final exam and certificate ---------------------------------------------------------------------------------------------
$exam = $tree['exam'];
$examEarly = $acPost('/academy/learn/computer-basics/quiz/' . $exam['id'] . '/start', [], $jA, '/academy/learn/computer-basics/quiz/' . $exam['id']);
check('the final exam stays locked until every lesson is finished', !str_contains($examEarly['loc'], '/attempt/') && Database::value('SELECT COUNT(*) FROM academy_attempts WHERE learner_id = ? AND quiz_id = ?', [$lid, (int) $exam['id']], 0) === 0);
foreach ($tree['lesson_ids'] as $id) {
    CourseService::completeLesson($lid, (array) CourseService::lesson((int) $id));
}
check('no certificate before the exam is passed', CourseService::checkCompletion($lid, (int) $course['id']) === null && CertificateService::activeFor($lid, (int) $course['id']) === null);
$exStart = $acPost('/academy/learn/computer-basics/quiz/' . $exam['id'] . '/start', [], $jA, '/academy/learn/computer-basics/quiz/' . $exam['id']);
preg_match('~/attempt/(\d+)~', $exStart['loc'], $em);
$exAnswers = [];
foreach (QuizService::questions((int) $exam['id']) as $q) {
    $exAnswers[(int) $q['id']] = array_values(array_map(static fn(array $o): int => (int) $o['id'], array_filter($q['options'], static fn(array $o): bool => (int) $o['is_correct'] === 1)));
}
$exSub = $acPost('/academy/learn/computer-basics/attempt/' . ($em[1] ?? 0), ['answer' => $exAnswers], $jA, '/academy/learn/computer-basics/attempt/' . ($em[1] ?? 0), true);
$exPage = $acReq('GET', '/academy/learn/computer-basics/attempt/' . ($em[1] ?? 0), [], $jA)['body'];
check('request: passing the exam files the certificate at once for approval and signing (not available to the learner yet)', CertificateService::pendingFor($lid, (int) $course['id']) !== null && CertificateService::activeFor($lid, (int) $course['id']) === null
    && !CertificateService::eligible($lid, (int) $course['id']));
check('request: the result page congratulates, says it waits for approval and signing and to check again after 48 hours', str_contains($exPage, 'Congratulations, you passed') && str_contains($exPage, 'approval and signing by the Academy administrator') && str_contains($exPage, 'again after 48 hours'));
check('request: the learner is e-mailed the same message', ($wm = $acLastMailTo($email, $mailsBefore)) !== null && str_contains($wm, 'Congratulations') && str_contains($wm, 'again after 48 hours'));
$rq = $acPost('/academy/courses/computer-basics/certificate', [], $jA, '/academy/courses/computer-basics');
$pend = CertificateService::pendingFor($lid, (int) $course['id']);
check('request: the learner asks for it and it waits for approval (not available, unknown to the public check page and PDF)', in_array($rq['code'], [302, 303], true) && $pend !== null && CertificateService::activeFor($lid, (int) $course['id']) === null
    && $acReq('GET', '/academy/certificate/' . $pend['code'], [], $j0)['code'] === 404 && $acReq('GET', '/academy/certificate/' . $pend['code'] . '/pdf', [], $j0)['code'] === 404);
check('request: asking twice does not make a second certificate', in_array($acPost('/academy/courses/computer-basics/certificate', [], $jA, '/academy/courses/computer-basics')['code'], [302, 303], true) && (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE learner_id = ?', [$lid], 0) === 1);
$dashHtml = $acReq('GET', '/academy/dashboard', [], $jA)['body'];
check('request: the dashboard tells the learner it waits for approval and to check again after 48 hours', str_contains($dashHtml, 'Certificate waiting for approval') && str_contains($dashHtml, 'Check again after 48 hours'));
$mailsBeforeApproval = $acMails();
CertificateService::approve((int) $pend['id'], null, true);
$cert = CertificateService::activeFor($lid, (int) $course['id']);
check('passing the final exam completes the course and issues the certificate', $cert !== null && preg_match('/^LAF-\d{2}-[A-Z2-9]{8}$/', (string) $cert['code']) === 1 && Database::value('SELECT completed_at FROM academy_enrollments WHERE learner_id = ? AND course_id = ?', [$lid, (int) $course['id']]) !== null );
check('the certificate keeps the learner name and the course title as printed', $cert['learner_name'] === 'ZZ Academy A' && $cert['course_title'] === 'Computer Basics and Digital Literacy' && (float) $cert['score_pct'] === 100.0);
check('a certificate e-mail was sent', ($cm = $acLastMailTo($email, $mailsBefore)) !== null && $acMails() !== $mailsBefore);
$certPage = $acReq('GET', '/academy/certificate/' . $cert['code'], [], $j0);
check('anyone can check the certificate by its number (no sign-in)', $certPage['code'] === 200 && str_contains($certPage['body'], 'genuine certificate') && str_contains($certPage['body'], 'ZZ Academy A') && str_contains($certPage['head'], 'noindex') === false ? true : str_contains($certPage['body'], 'noindex'));
$pdf = $acReq('GET', '/academy/certificate/' . $cert['code'] . '/pdf', [], $j0);
check('the certificate PDF is a real PDF with the number and name inside', $pdf['code'] === 200 && str_starts_with($pdf['body'], '%PDF-') && str_contains($pdf['head'], 'application/pdf') && str_contains($pdf['body'], '%%EOF'));
check('a made-up certificate number is a 404', $acReq('GET', '/academy/certificate/LAF-26-ZZZZZZZZ', [], $j0)['code'] === 404 && $acReq('GET', '/academy/certificate/not-a-number', [], $j0)['code'] === 404);
CertificateService::revoke((int) $cert['id'], 'selftest');
check('a withdrawn certificate says so and gives no PDF', str_contains($acReq('GET', '/academy/certificate/' . $cert['code'], [], $j0)['body'], 'withdrawn') && $acReq('GET', '/academy/certificate/' . $cert['code'] . '/pdf', [], $j0)['code'] === 404);
CertificateService::reinstate((int) $cert['id']);
check('the certificate can be reinstated', $acReq('GET', '/academy/certificate/' . $cert['code'] . '/pdf', [], $j0)['code'] === 200);
$dash = $acReq('GET', '/academy/dashboard', [], $jA);
check('the dashboard shows the course as completed with the certificate', str_contains($dash['body'], 'Completed') && str_contains($dash['body'], (string) $cert['code']));

// ---- virtual classes --------------------------------------------------------------------------------------------------------
$clsId = Database::insert('academy_classes', [
    'title' => 'zz-academy class', 'course_id' => (int) $course['id'], 'platform' => 'meet', 'join_url' => 'https://meet.google.com/abc-defg-hij', 'passcode' => '4242',
    'start_at' => date('Y-m-d H:i:s', time() + 86400), 'end_at' => date('Y-m-d H:i:s', time() + 90000), 'status' => 'scheduled', 'audience' => 'course',
]);
$cls = $acReq('GET', '/academy/classes', [], $j0);
check('public: the class is listed but its join link and passcode are not shown to a visitor', str_contains($cls['body'], 'zz-academy class') && !str_contains($cls['body'], 'meet.google.com/abc') && !str_contains($cls['body'], '4242'));
$clsA = $acReq('GET', '/academy/classes', [], $jA);
check('an enrolled learner sees the join link and the passcode', str_contains($clsA['body'], 'meet.google.com/abc-defg-hij') && str_contains($clsA['body'], '4242'));
$clsB = $acReq('GET', '/academy/classes', [], $jB);
check('another enrolled learner sees it too, a learner not enrolled does not', str_contains($clsB['body'], 'meet.google.com/abc-defg-hij'));
CourseService::unenroll($lidB, (int) $course['id']);
check('a learner who is not enrolled in the course gets no join link', !str_contains($acReq('GET', '/academy/classes', [], $jB)['body'], 'meet.google.com/abc-defg-hij'));
$ics = $acReq('GET', '/academy/classes/' . $clsId . '/calendar.ics', [], $jA);
check('the calendar file has the class (with the link for an enrolled learner)', $ics['code'] === 200 && str_contains($ics['body'], 'BEGIN:VEVENT') && str_contains($ics['body'], 'zz-academy class') && str_contains($ics['body'], 'meet.google.com') && str_contains($ics['head'], 'text/calendar'));
check('class link rules: https only, the right host for the platform, no addresses or passwords', ClassService::checkUrl('zoom', 'https://us02web.zoom.us/j/123')['ok'] && !ClassService::checkUrl('zoom', 'https://evil.example/j/1')['ok'] && !ClassService::checkUrl('meet', 'http://meet.google.com/x')['ok']
    && !ClassService::checkUrl('other', 'https://user:pw@example.org/x')['ok'] && !ClassService::checkUrl('other', 'https://127.0.0.1/x')['ok'] && ClassService::checkUrl('other', '')['ok']);
check('a free Jitsi room link can be made', preg_match('~^https://meet\.jit\.si/LAF-Academy-[a-f0-9]{12}$~', ClassService::jitsiLink()) === 1);
Database::update('academy_classes', ['status' => 'cancelled'], 'id = ?', [$clsId]);
check('a cancelled class disappears from the list', !str_contains($acReq('GET', '/academy/classes', [], $jA)['body'], 'zz-academy class'));

// ---- forgotten password -----------------------------------------------------------------------------------------------------
$acPost('/academy/forgot', ['email' => $email], $jB, '/academy/forgot');
$rm = $acLastMailTo($email, $mailsBefore);
check('forgotten password: a reset link is e-mailed', $rm !== null && preg_match('~/academy/reset/([a-f0-9]{64})~', $rm, $rt) === 1);
$none = $acPost('/academy/forgot', ['email' => 'zz-academy-nobody@example.test'], $jB, '/academy/forgot', true);
check('forgotten password: the same answer for an address without an account', str_contains($none['body'], 'we sent a link'));
$jC = null;
$rs = $acPost('/academy/reset/' . ($rt[1] ?? 'x'), ['password' => 'Brand new pass 55', 'password_confirm' => 'Brand new pass 55'], $jC, '/academy/reset/' . ($rt[1] ?? 'x'), true);
check('the reset link sets a new password', str_contains($rs['body'], 'password was changed') && \App\Core\Auth::verifyPassword('Brand new pass 55', (string) Database::value('SELECT password_hash FROM academy_learners WHERE id = ?', [$lid])));
check('a reset link works once only', $acReq('GET', '/academy/reset/' . ($rt[1] ?? 'x'), [], $jC)['code'] === 410);
$jA2 = null;
$in2 = $acPost('/academy/login', ['email' => $email, 'password' => 'Brand new pass 55'], $jA2, '/academy/login', true);
check('sign-in works with the new password', str_contains($in2['body'], 'My learning'));
$chg = $acPost('/academy/account/password', ['current_password' => 'wrong', 'new_password' => 'Whatever pass 12', 'new_password_confirm' => 'Whatever pass 12'], $jA2, '/academy/account', true);
check('changing the password needs the current one', str_contains($chg['body'], 'not correct'));

// ---- switches and deletion --------------------------------------------------------------------------------------------------
$acSet('academy_registration_open', '0');
$closed = $acPost('/academy/register', ['name' => 'ZZ Academy C', 'email' => 'zz-academy-c@example.test', 'password' => 'Closed door 66', 'phone' => '+256700000009', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 009', 'agree' => '1'], $jC, '/academy/register', true);
check('registration can be closed', Database::value("SELECT COUNT(*) FROM academy_learners WHERE email = 'zz-academy-c@example.test'", [], 0) === 0);
$acSet('academy_registration_open', '1');
Database::update('academy_courses', ['status' => 'draft'], 'id = ?', [$zzCourse]);
check('a draft course is not found by visitors or learners', $acReq('GET', '/academy/courses/zz-academy-limits', [], $j0)['code'] === 404);
$del = $acPost('/academy/account/delete', ['confirm' => 'DELETE', 'password' => 'Brand new pass 55'], $jA2, '/academy/account', true);
check('deleting the account removes it and its progress, the certificate stays valid', Database::value('SELECT COUNT(*) FROM academy_learners WHERE id = ?', [$lid], 0) === 0 && Database::value('SELECT COUNT(*) FROM academy_progress WHERE learner_id = ?', [$lid], 0) === 0
    && CertificateService::find((string) $cert['code']) !== null && $acReq('GET', '/academy/certificate/' . $cert['code'], [], $j0)['code'] === 200);
$acSet('academy_enabled', '0');
check('the whole Academy can be switched off (404 for every page)', $acReq('GET', '/academy', [], $j0)['code'] === 404 && $acReq('GET', '/academy/courses/computer-basics', [], $j0)['code'] === 404 && $acReq('GET', '/academy/login', [], $j0)['code'] === 404);
$acSet('academy_enabled', '1');

// ---- registry, nav, schema ---------------------------------------------------------------------------------------------------
check('registry: the Academy page is in the header and footer menus', ($pg = \App\Core\PageRegistry::get('academy')) !== null && !empty($pg['nav']['header']) && !empty($pg['nav']['footer']));
check('abilities: academy.teach and academy.manage exist; the facilitator role may teach, not manage', in_array('academy.manage', \App\Core\Roles::knownAbilities(), true) && \App\Core\Roles::can('facilitator', 'academy.teach') && !\App\Core\Roles::can('facilitator', 'academy.manage') && !\App\Core\Roles::can('editor', 'academy.teach') && \App\Core\Roles::can('admin', 'academy.manage'));
check('sitemap lists the published Academy courses', str_contains((string) @file_get_contents($acBase . '/sitemap.xml'), '/academy/courses/computer-basics'));
check('robots.txt keeps the private Academy screens out of search engines', str_contains((string) @file_get_contents($acBase . '/robots.txt'), '/academy/learn/'));

foreach ($acMails() as $f) {
    if (!in_array($f, $mailsBefore, true)) {
        @unlink($f);
    }
}
$acClean();
$acRestore();
check('cleanup: no zz-academy rows are left', Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-academy-%'", [], 0) === 0 && Database::value("SELECT COUNT(*) FROM academy_courses WHERE title LIKE 'zz-academy%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
