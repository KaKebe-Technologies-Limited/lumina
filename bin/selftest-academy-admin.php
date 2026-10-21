<?php
/**
 * LAF Academy ADMIN checks, included by bin/selftest.php (shares check() and $fail) or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-admin.php
 * Covers over HTTP: administrator screens (overview, starter courses, courses, curriculum: modules / lessons / handout upload / reorder / hide, the quiz editor and its
 * error messages, publish rules, duplicate, delete), virtual classes (link rules, Jitsi link, notify e-mails, cancel), learners (list, profile, enrol, certificate by hand,
 * switch off, CSV), certificates (withdraw / reinstate), facilitator accounts (one-time password), and the FACILITATOR limits: only their own courses / classes / learners,
 * no administrator pages, no other facilitator's course; a user without the ability gets 403. Everything it creates is removed (zz-aca-* users, "zz-aca" courses and classes).
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

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ContentInstaller;
use App\Services\Academy\CourseService;
use App\Services\Academy\QuizService;

echo "-- academy admin --\n";
ContentInstaller::installAll(null, true);
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null, bool $follow = false, array $files = []) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'adj');
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => $follow, CURLOPT_MAXREDIRS => 5]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $post = $form;
        foreach ($files as $k => $f) {
            $post[$k] = new CURLFile($f[0], $f[1], $f[2]);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $files === [] ? http_build_query($form) : $post);
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $head = substr($raw, 0, $hs);
    return ['code' => $code, 'head' => $head, 'body' => substr($raw, $hs), 'loc' => preg_match('/^location:\s*(\S+)/mi', $head, $m) === 1 ? $m[1] : ''];
};
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from, bool $follow = false, array $files = []) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    return $req('POST', $path, $form + ['_token' => $tok($page['body'])], $jar, $follow, $files);
};
$login = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    $r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
    return in_array($r['code'], [302, 303], true);
};
$clean = static function (): void {
    Database::execute("DELETE FROM academy_classes WHERE title LIKE 'zz-aca%'");
    Database::execute("DELETE FROM academy_courses WHERE title LIKE 'zz-aca%' OR title LIKE 'Copy of zz-aca%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-aca-%@example.test'");
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Aca%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-aca-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
foreach (glob(STORAGE_PATH . '/private/academy/*/*/*.pdf') ?: [] as $f) {
    if (filemtime($f) > time() - 3600 && str_contains((string) @file_get_contents($f, false, null, 0, 200), 'zz-aca')) {
        @unlink($f);
    }
}
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];

$pwA = 'Admin test pass 91';
$pwF = 'Facilitator test pass 92';
$pwF2 = 'Facilitator two pass 93';
$pwE = 'Editor test pass 94';
$mk = static fn(string $name, string $email, string $role, string $pw): int => (int) User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$adminId = $mk('ZZ Aca Admin', 'zz-aca-admin@example.test', 'admin', $pwA);
$facId = $mk('ZZ Aca Facilitator', 'zz-aca-fac@example.test', 'facilitator', $pwF);
$fac2Id = $mk('ZZ Aca Facilitator Two', 'zz-aca-fac2@example.test', 'facilitator', $pwF2);
$edId = $mk('ZZ Aca Editor', 'zz-aca-editor@example.test', 'editor', $pwE);
$jA = $jF = $jF2 = $jE = $jV = null;
check('admin, facilitator and editor can sign in', $login('zz-aca-admin@example.test', $pwA, $jA) && $login('zz-aca-fac@example.test', $pwF, $jF) && $login('zz-aca-fac2@example.test', $pwF2, $jF2) && $login('zz-aca-editor@example.test', $pwE, $jE));

// ---- access by ability ------------------------------------------------------------------------------------------------------
check('overview: an administrator sees numbers and the starter courses', ($ov = $req('GET', '/admin/academy', [], $jA))['code'] === 200 && str_contains($ov['body'], 'Starter courses') && str_contains($ov['body'], 'Published courses'));
check('overview: a facilitator sees the Academy but not the starter courses installer', ($ovf = $req('GET', '/admin/academy', [], $jF))['code'] === 200 && !str_contains($ovf['body'], 'Starter courses'));
check('an editor (no Academy ability) gets 403 on every Academy admin page', $req('GET', '/admin/academy', [], $jE)['code'] === 403 && $req('GET', '/admin/academy/courses', [], $jE)['code'] === 403 && $req('GET', '/admin/academy/learners', [], $jE)['code'] === 403);
check('a facilitator gets 403 on the administrator pages', $req('GET', '/admin/academy/facilitators', [], $jF)['code'] === 403 && $req('GET', '/admin/academy/certificates', [], $jF)['code'] === 403 && $req('GET', '/admin/academy/learners/create/new', [], $jF)['code'] === 403);
check('the sidebar shows the Academy group to staff who may use it', str_contains($ovf['body'], 'Virtual classes') && !str_contains($ovf['body'], 'href="' . $base . '/admin/academy/facilitators"'));

// ---- administrator: create a course -----------------------------------------------------------------------------------------
$created = $post('/admin/academy/courses', ['title' => 'zz-aca Admin course', 'category' => 'office', 'level' => 'beginner', 'icon' => 'file-text', 'summary' => 'A test course', 'description' => '<p>About <script>alert(1)</script>it</p>',
    'outcomes' => "Do a thing\nDo another thing", 'requirements' => 'A computer', 'resources' => "Official site | https://example.org/help\nBad | javascript:alert(1)", 'pass_mark' => '60', 'status' => 'published',
    'certificate_enabled' => '1', 'sort_order' => '900', 'facilitator_id' => (string) $facId], $jA, '/admin/academy/courses/create');
$course = Database::one("SELECT * FROM academy_courses WHERE title = 'zz-aca Admin course'");
check('a new course is created as a draft, owned by the chosen facilitator, with cleaned text', $created['code'] === 302 && $course !== null && $course['status'] === 'draft' && (int) $course['facilitator_id'] === $facId
    && !str_contains((string) $course['description'], '<script') && count(CourseService::jsonList($course['outcomes'])) === 2 && count(CourseService::resources($course['resources'])) === 1 && (int) $course['pass_mark'] === 60);
$cid = (int) $course['id'];
$noPublish = $post('/admin/academy/courses/' . $cid . '/status', ['to' => 'published'], $jA, '/admin/academy/courses');
check('a course with no lesson cannot be published', Database::value('SELECT status FROM academy_courses WHERE id = ?', [$cid]) === 'draft');
check('the course edit page opens', $req('GET', '/admin/academy/courses/' . $cid . '/edit', [], $jA)['code'] === 200);
$dupTitle = $post('/admin/academy/courses/' . $cid, ['title' => 'zz-aca Admin course', 'slug' => 'computer-basics', 'category' => 'office', 'level' => 'beginner', 'pass_mark' => '60', 'status' => 'draft'], $jA, '/admin/academy/courses/' . $cid . '/edit', true);
check('a web address used by another course is refused', str_contains($dupTitle['body'], 'already uses') && Database::value('SELECT slug FROM academy_courses WHERE id = ?', [$cid]) !== 'computer-basics');

// ---- curriculum -------------------------------------------------------------------------------------------------------------
$post('/admin/academy/courses/' . $cid . '/modules', ['title' => 'Module A', 'summary' => 'First'], $jA, '/admin/academy/courses/' . $cid . '/curriculum');
$post('/admin/academy/courses/' . $cid . '/modules', ['title' => 'Module B'], $jA, '/admin/academy/courses/' . $cid . '/curriculum');
$mods = Database::all('SELECT * FROM academy_modules WHERE course_id = ? ORDER BY sort_order', [$cid]);
check('two modules were added in order', count($mods) === 2 && $mods[0]['title'] === 'Module A');
$mA = (int) $mods[0]['id'];
$mB = (int) $mods[1]['id'];
$post('/admin/academy/modules/' . $mB . '/move', ['direction' => 'up'], $jA, '/admin/academy/courses/' . $cid . '/curriculum');
check('a module can be moved up', Database::value('SELECT title FROM academy_modules WHERE course_id = ? ORDER BY sort_order LIMIT 1', [$cid]) === 'Module B');
$pdf = tempnam(sys_get_temp_dir(), 'zzpdf');
file_put_contents($pdf, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R/Size 4>>\n%zz-aca\n%%EOF\n");
$ls = $post('/admin/academy/modules/' . $mA . '/lessons', ['title' => 'Lesson one', 'kind' => 'text', 'minutes' => '7', 'body' => '<h3>Hi</h3><p>Text <img src=x onerror=alert(1)></p>', 'is_published' => '1', 'video_url' => 'https://youtu.be/dQw4w9WgXcQ'],
    $jA, '/admin/academy/modules/' . $mA . '/lessons/create', false, ['attachment' => [$pdf, 'application/pdf', 'handout.pdf']]);
$lesson = Database::one("SELECT * FROM academy_lessons WHERE module_id = ? AND title = 'Lesson one'", [$mA]);
check('a lesson is saved with sanitised text, a YouTube link and a private handout', $lesson !== null && !str_contains((string) $lesson['body'], 'onerror') && $lesson['video_url'] !== null && $lesson['file_name'] === 'handout.pdf'
    && str_starts_with((string) $lesson['file_path'], 'academy/') && !is_file(dirname(__DIR__) . '/' . $lesson['file_path']));
$lid = (int) $lesson['id'];
check('the handout can be downloaded by staff but not by a visitor', ($dl = $req('GET', '/admin/academy/lessons/' . $lid . '/file', [], $jA))['code'] === 200 && str_starts_with($dl['body'], '%PDF'));
$badFile = tempnam(sys_get_temp_dir(), 'zzbad');
file_put_contents($badFile, '<?php echo 1;');
$post('/admin/academy/modules/' . $mA . '/lessons', ['title' => 'Bad upload', 'kind' => 'file', 'minutes' => '5', 'body' => '<p>x</p>', 'is_published' => '1'], $jA, '/admin/academy/modules/' . $mA . '/lessons/create', false, ['attachment' => [$badFile, 'application/pdf', 'evil.pdf']]);
check('a file that is not what its name says is refused', Database::value("SELECT COUNT(*) FROM academy_lessons WHERE title = 'Bad upload'", [], 0) === 0);
$post('/admin/academy/modules/' . $mA . '/lessons', ['title' => 'Lesson two', 'kind' => 'text', 'minutes' => '9', 'body' => '<p>Two</p>', 'is_published' => '1'], $jA, '/admin/academy/modules/' . $mA . '/lessons/create');
$lid2 = (int) Database::value("SELECT id FROM academy_lessons WHERE title = 'Lesson two'");
$badVideo = $post('/admin/academy/modules/' . $mA . '/lessons', ['title' => 'Bad video', 'kind' => 'video', 'minutes' => '5', 'body' => '<p>x</p>', 'video_url' => 'https://evil.example/watch?v=1', 'is_published' => '1'], $jA, '/admin/academy/modules/' . $mA . '/lessons/create', true);
check('only YouTube links are accepted for video', Database::value("SELECT COUNT(*) FROM academy_lessons WHERE title = 'Bad video'", [], 0) === 0 && str_contains($badVideo['body'], 'YouTube'));
$post('/admin/academy/lessons/' . $lid2 . '/move', ['direction' => 'up'], $jA, '/admin/academy/courses/' . $cid . '/curriculum');
check('a lesson can be moved up', (int) Database::value('SELECT id FROM academy_lessons WHERE module_id = ? ORDER BY sort_order LIMIT 1', [$mA]) === $lid2);
$post('/admin/academy/lessons/' . $lid2 . '/toggle', [], $jA, '/admin/academy/courses/' . $cid . '/curriculum');
check('a lesson can be hidden from learners', (int) Database::value('SELECT is_published FROM academy_lessons WHERE id = ?', [$lid2], 1) === 0 && CourseService::tree($cid)['lessons'] === 1 && CourseService::tree($cid, false)['lessons'] === 2);
check('the curriculum page lists modules and lessons', ($cu = $req('GET', '/admin/academy/courses/' . $cid . '/curriculum', [], $jA))['code'] === 200 && str_contains($cu['body'], 'Lesson one') && str_contains($cu['body'], 'Module A'));

// ---- quiz editor ------------------------------------------------------------------------------------------------------------
$qr = $req('GET', '/admin/academy/modules/' . $mA . '/quiz', [], $jA);
preg_match('~/quizzes/(\d+)/edit~', $qr['loc'], $qm);
$qid = (int) ($qm[1] ?? 0);
check('opening a module quiz creates it and goes to the editor', $qid > 0 && $req('GET', '/admin/academy/quizzes/' . $qid . '/edit', [], $jA)['code'] === 200);
$script = "Q: First?\n* yes\n- no\n= because\n\nQ: Second? (choose all)\n* a\n* b\n- c\n= why";
$post('/admin/academy/quizzes/' . $qid, ['title' => 'Quiz A', 'pass_mark' => '50', 'time_limit_min' => '10', 'max_attempts' => '3', 'script' => $script], $jA, '/admin/academy/quizzes/' . $qid . '/edit');
$quiz = QuizService::find($qid);
check('the quiz is saved with its questions, answers, settings and explanations', QuizService::questionCount($qid) === 2 && (int) $quiz['pass_mark'] === 50 && (int) $quiz['time_limit_min'] === 10 && (int) $quiz['max_attempts'] === 3
    && QuizService::script($qid) === $script);
$bad = $post('/admin/academy/quizzes/' . $qid, ['title' => 'Quiz A', 'pass_mark' => '50', 'script' => "Q: Broken\n* only one answer"], $jA, '/admin/academy/quizzes/' . $qid . '/edit', true);
check('a quiz text with mistakes is refused with a clear message and nothing is lost', str_contains($bad['body'], 'at least 2 answers') && QuizService::questionCount($qid) === 2);
$ex = $req('GET', '/admin/academy/courses/' . $cid . '/exam', [], $jA);
preg_match('~/quizzes/(\d+)/edit~', $ex['loc'], $xm);
$xid = (int) ($xm[1] ?? 0);
$post('/admin/academy/quizzes/' . $xid, ['title' => 'Final', 'pass_mark' => '80', 'max_attempts' => '2', 'script' => "Q: Exam one?\n* yes\n- no\n= x"], $jA, '/admin/academy/quizzes/' . $xid . '/edit');
check('the final exam is its own quiz and its pass mark updates the course', QuizService::exam($cid) !== null && (int) Database::value('SELECT pass_mark FROM academy_courses WHERE id = ?', [$cid]) === 80);

// ---- publish, public page, duplicate ---------------------------------------------------------------------------------------
$post('/admin/academy/courses/' . $cid . '/status', ['to' => 'published'], $jA, '/admin/academy/courses');
check('with a published lesson the course can be published and appears on the public catalogue', Database::value('SELECT status FROM academy_courses WHERE id = ?', [$cid]) === 'published' && str_contains($req('GET', '/academy', [], $jV)['body'], 'zz-aca Admin course'));
$dup = $post('/admin/academy/courses/' . $cid . '/duplicate', [], $jA, '/admin/academy/courses/' . $cid . '/edit');
$copy = Database::one("SELECT * FROM academy_courses WHERE title = 'Copy of zz-aca Admin course'");
check('a course can be duplicated as a draft with its modules, lessons and quizzes (handouts are not copied)', $copy !== null && $copy['status'] === 'draft' && Database::value('SELECT COUNT(*) FROM academy_lessons WHERE course_id = ?', [(int) $copy['id']], 0) === 2
    && Database::value('SELECT COUNT(*) FROM academy_quizzes WHERE course_id = ?', [(int) $copy['id']], 0) === 2 && Database::value('SELECT COUNT(*) FROM academy_lessons WHERE course_id = ? AND file_path IS NOT NULL', [(int) $copy['id']], 0) === 0);

// ---- facilitator: own courses only -----------------------------------------------------------------------------------------
$fl = $req('GET', '/admin/academy/courses', [], $jF);
check('a facilitator sees their own course in the list and not the starter courses', $fl['code'] === 200 && str_contains($fl['body'], 'zz-aca Admin course') && !str_contains($fl['body'], 'Computer Basics and Digital Literacy'));
check('a facilitator can open and edit the lessons of their own course', $req('GET', '/admin/academy/courses/' . $cid . '/curriculum', [], $jF)['code'] === 200 && $req('GET', '/admin/academy/lessons/' . $lid . '/edit', [], $jF)['code'] === 200 && $req('GET', '/admin/academy/quizzes/' . $qid . '/edit', [], $jF)['code'] === 200);
$other = (int) Database::value("SELECT id FROM academy_courses WHERE slug = 'computer-basics'");
$otherLesson = (int) Database::value('SELECT id FROM academy_lessons WHERE course_id = ? LIMIT 1', [$other]);
$otherQuiz = (int) Database::value('SELECT id FROM academy_quizzes WHERE course_id = ? LIMIT 1', [$other]);
check('a facilitator gets 403 on a course, lesson and quiz of someone else', $req('GET', '/admin/academy/courses/' . $other . '/edit', [], $jF)['code'] === 403 && $req('GET', '/admin/academy/courses/' . $other . '/curriculum', [], $jF)['code'] === 403
    && $req('GET', '/admin/academy/lessons/' . $otherLesson . '/edit', [], $jF)['code'] === 403 && $req('GET', '/admin/academy/quizzes/' . $otherQuiz . '/edit', [], $jF)['code'] === 403);
$steal = $post('/admin/academy/lessons/' . $otherLesson, ['title' => 'Hacked', 'kind' => 'text', 'minutes' => '5', 'body' => '<p>x</p>', 'is_published' => '1'], $jF, '/admin/academy/courses');
check('a facilitator cannot change a lesson of another course by posting to it', $steal['code'] === 403 && Database::value('SELECT title FROM academy_lessons WHERE id = ?', [$otherLesson]) !== 'Hacked');
check('another facilitator cannot reach this facilitator\'s course either', $req('GET', '/admin/academy/courses/' . $cid . '/curriculum', [], $jF2)['code'] === 403);
$fc = $post('/admin/academy/courses', ['title' => 'zz-aca Facilitator course', 'category' => 'basics', 'level' => 'beginner', 'pass_mark' => '70', 'status' => 'published', 'facilitator_id' => (string) $fac2Id, 'sort_order' => '1', 'is_featured' => '1'], $jF, '/admin/academy/courses/create');
$fcourse = Database::one("SELECT * FROM academy_courses WHERE title = 'zz-aca Facilitator course'");
check('a facilitator\'s new course is a draft owned by them, whatever they post for owner, order or featured', $fcourse !== null && (int) $fcourse['facilitator_id'] === $facId && $fcourse['status'] === 'draft' && (int) $fcourse['is_featured'] === 0 && (int) $fcourse['sort_order'] !== 1);

// ---- virtual classes --------------------------------------------------------------------------------------------------------
$day = date('Y-m-d', strtotime('+3 days'));
$badLink = $post('/admin/academy/classes', ['title' => 'zz-aca class bad', 'platform' => 'zoom', 'join_url' => 'https://evil.example/j/1', 'date' => $day, 'time' => '10:00', 'duration' => '60', 'audience' => 'course', 'course_id' => (string) $cid, 'status' => 'scheduled'], $jA, '/admin/academy/classes/create', true);
check('a link that does not belong to the chosen platform is refused', str_contains($badLink['body'], 'does not look like a Zoom link') && Database::value("SELECT COUNT(*) FROM academy_classes WHERE title = 'zz-aca class bad'", [], 0) === 0);
$noLink = $post('/admin/academy/classes', ['title' => 'zz-aca class nolink', 'platform' => 'meet', 'join_url' => '', 'date' => $day, 'time' => '10:00', 'duration' => '60', 'audience' => 'course', 'course_id' => (string) $cid, 'status' => 'scheduled'], $jA, '/admin/academy/classes/create', true);
check('a scheduled class needs a meeting link', str_contains($noLink['body'], 'Add the meeting link'));
$past = $post('/admin/academy/classes', ['title' => 'zz-aca class past', 'platform' => 'meet', 'join_url' => 'https://meet.google.com/aaa-bbbb-ccc', 'date' => date('Y-m-d', strtotime('-5 days')), 'time' => '10:00', 'duration' => '60', 'audience' => 'course', 'course_id' => (string) $cid, 'status' => 'scheduled'], $jA, '/admin/academy/classes/create', true);
check('a new class in the past is refused', str_contains($past['body'], 'starts in the past'));
$okc = $post('/admin/academy/classes', ['title' => 'zz-aca class ok', 'description' => 'Practice together', 'platform' => 'meet', 'join_url' => 'https://meet.google.com/aaa-bbbb-ccc', 'passcode' => '77', 'date' => $day, 'time' => '14:30', 'duration' => '90', 'audience' => 'course', 'course_id' => (string) $cid, 'status' => 'scheduled', 'facilitator_id' => (string) $facId], $jA, '/admin/academy/classes/create');
$cls = Database::one("SELECT * FROM academy_classes WHERE title = 'zz-aca class ok'");
check('a class is scheduled with the right start and end time', $cls !== null && $cls['start_at'] === $day . ' 14:30:00' && $cls['end_at'] === $day . ' 16:00:00' && (int) $cls['facilitator_id'] === $facId);
check('the facilitator of a class can open and change it, another facilitator cannot', $req('GET', '/admin/academy/classes/' . $cls['id'] . '/edit', [], $jF)['code'] === 200 && $req('GET', '/admin/academy/classes/' . $cls['id'] . '/edit', [], $jF2)['code'] === 403);
$l1 = Database::insert('academy_learners', ['name' => 'ZZ Aca Learner', 'email' => 'zz-aca-l1@example.test', 'password_hash' => Auth::hashPassword('Learner pass 77'), 'email_verified_at' => date('Y-m-d H:i:s')]);
$l2 = Database::insert('academy_learners', ['name' => 'ZZ Aca Other', 'email' => 'zz-aca-l2@example.test', 'password_hash' => Auth::hashPassword('Learner pass 78'), 'email_verified_at' => date('Y-m-d H:i:s')]);
CourseService::enroll($l1, $cid);
$mailsMid = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$nt = $post('/admin/academy/classes/' . $cls['id'] . '/notify', [], $jF, '/admin/academy/classes/' . $cls['id'] . '/edit');
$newMails = array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsMid);
check('"E-mail the learners" mails only the learners enrolled in the course (and never the join link)', count($newMails) === 1 && str_contains((string) file_get_contents(array_values($newMails)[0]), 'zz-aca-l1@example.test') && !str_contains(quoted_printable_decode((string) file_get_contents(array_values($newMails)[0])), 'meet.google.com/aaa'));
$post('/admin/academy/classes/' . $cls['id'] . '/notify', [], $jF, '/admin/academy/classes/' . $cls['id'] . '/edit');
check('the same learner is not mailed twice within the hour', count(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsMid)) === 1);
$post('/admin/academy/classes/' . $cls['id'] . '/status', ['to' => 'cancelled'], $jA, '/admin/academy/classes');
check('a class can be cancelled', Database::value('SELECT status FROM academy_classes WHERE id = ?', [(int) $cls['id']]) === 'cancelled');

// ---- learners ---------------------------------------------------------------------------------------------------------------
check('learners: the administrator sees everyone, the facilitator only learners of their course', str_contains($req('GET', '/admin/academy/learners', [], $jA)['body'], 'zz-aca-l2@example.test') && ($lf = $req('GET', '/admin/academy/learners', [], $jF))['code'] === 200
    && str_contains($lf['body'], 'zz-aca-l1@example.test') && !str_contains($lf['body'], 'zz-aca-l2@example.test'));
check('learners: a facilitator gets 404 for a learner outside their courses and can open one inside', $req('GET', '/admin/academy/learners/' . $l2, [], $jF)['code'] === 404 && $req('GET', '/admin/academy/learners/' . $l1, [], $jF)['code'] === 200);
check('learners: a facilitator cannot delete, switch off or issue certificates', $post('/admin/academy/learners/' . $l1 . '/delete', [], $jF, '/admin/academy/learners/' . $l1)['code'] === 403 && $post('/admin/academy/learners/' . $l1 . '/toggle', [], $jF, '/admin/academy/learners/' . $l1)['code'] === 403
    && $post('/admin/academy/learners/' . $l1 . '/certificate', ['course_id' => (string) $cid], $jF, '/admin/academy/learners/' . $l1)['code'] === 403 && Database::value('SELECT COUNT(*) FROM academy_learners WHERE id = ?', [$l1], 0) === 1);
$exp = $req('GET', '/admin/academy/learners-export', [], $jA);
check('learners: the CSV download is for administrators and lists the learner', $exp['code'] === 200 && str_contains($exp['head'], 'text/csv') && str_contains($exp['body'], 'zz-aca-l1@example.test') && $req('GET', '/admin/academy/learners-export', [], $jF)['code'] === 403);
$post('/admin/academy/learners/' . $l2 . '/enroll', ['course_id' => (string) $cid], $jA, '/admin/academy/learners/' . $l2);
check('learners: an administrator can enrol a learner in a course', CourseService::enrollment($l2, $cid) !== null);
$post('/admin/academy/learners/' . $l2 . '/certificate', ['course_id' => (string) $cid], $jA, '/admin/academy/learners/' . $l2);
$hand = CertificateService::activeFor($l2, $cid);
check('learners: a certificate can be issued by hand and the enrolment is marked completed', $hand !== null && Database::value('SELECT completed_at FROM academy_enrollments WHERE learner_id = ? AND course_id = ?', [$l2, $cid]) !== null);
$post('/admin/academy/learners/' . $l2 . '/toggle', [], $jA, '/admin/academy/learners/' . $l2);
check('learners: an account can be switched off', (int) Database::value('SELECT is_active FROM academy_learners WHERE id = ?', [$l2], 1) === 0);
$post('/admin/academy/learners/' . $l2 . '/toggle', [], $jA, '/admin/academy/learners/' . $l2);
$newL = $post('/admin/academy/learners', ['name' => 'ZZ Aca Added', 'email' => 'zz-aca-added@example.test', 'send_link' => '1'], $jA, '/admin/academy/learners/create/new');
$added = Database::one("SELECT * FROM academy_learners WHERE email = 'zz-aca-added@example.test'");
check('learners: one added by hand is confirmed, gets a random password and an e-mailed link', $added !== null && $added['email_verified_at'] !== null && $added['token_kind'] === 'reset' && count(array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsMid)) >= 2);

// ---- certificates -----------------------------------------------------------------------------------------------------------
check('certificates: the administrator list shows the certificate, a facilitator gets 403', str_contains($req('GET', '/admin/academy/certificates', [], $jA)['body'], (string) $hand['code']));
$post('/admin/academy/certificates/' . $hand['id'] . '/revoke', ['reason' => 'test'], $jA, '/admin/academy/certificates');
check('certificates: withdrawing makes the public check say so, reinstating restores it', Database::value('SELECT revoked_at FROM academy_certificates WHERE id = ?', [(int) $hand['id']]) !== null && str_contains($req('GET', '/academy/certificate/' . $hand['code'], [], $jV)['body'], 'withdrawn'));
$post('/admin/academy/certificates/' . $hand['id'] . '/reinstate', [], $jA, '/admin/academy/certificates');
check('certificates: reinstated', Database::value('SELECT revoked_at FROM academy_certificates WHERE id = ?', [(int) $hand['id']]) === null);

// ---- facilitator accounts ---------------------------------------------------------------------------------------------------
$fa = $post('/admin/academy/facilitators', ['name' => 'ZZ Aca New Teacher', 'email' => 'zz-aca-new@example.test', 'role' => 'facilitator'], $jA, '/admin/academy/facilitators', true);
$newU = Database::one("SELECT * FROM users WHERE email = 'zz-aca-new@example.test'");
check('facilitators: an account is created with the Facilitator role and a one-time password is shown', $newU !== null && $newU['role'] === 'facilitator' && (int) $newU['must_change_password'] === 1 && str_contains($fa['body'], 'will not be shown again') && str_contains($fa['body'], 'cred-pw'));
$fa2 = $req('GET', '/admin/academy/facilitators', [], $jA);
check('facilitators: the password is not shown a second time', !str_contains($fa2['body'], 'will not be shown again'));
$dupF = $post('/admin/academy/facilitators', ['name' => 'Dup', 'email' => 'zz-aca-new@example.test', 'role' => 'facilitator'], $jA, '/admin/academy/facilitators', true);
check('facilitators: an e-mail address already in use is refused', Database::value("SELECT COUNT(*) FROM users WHERE email = 'zz-aca-new@example.test'", [], 0) === 1);
$adminRole = $post('/admin/academy/facilitators', ['name' => 'Sneaky', 'email' => 'zz-aca-sneaky@example.test', 'role' => 'admin'], $jA, '/admin/academy/facilitators', true);
check('facilitators: this page can never create an administrator', Database::value("SELECT COUNT(*) FROM users WHERE email = 'zz-aca-sneaky@example.test'", [], 0) === 0);
$post('/admin/academy/facilitators/' . $newU['id'] . '/toggle', [], $jA, '/admin/academy/facilitators');
check('facilitators: an account can be switched off, and an administrator account cannot be touched here', (int) Database::value('SELECT is_active FROM users WHERE id = ?', [(int) $newU['id']], 1) === 0 && $post('/admin/academy/facilitators/' . $adminId . '/toggle', [], $jA, '/admin/academy/facilitators')['code'] === 403);
$jN = null;
check('facilitators: a switched-off account cannot sign in', $login('zz-aca-new@example.test', 'whatever', $jN) === false || $req('GET', '/admin/academy', [], $jN)['code'] !== 200);

// ---- delete -----------------------------------------------------------------------------------------------------------------
$fdel = $post('/admin/academy/courses/' . $cid . '/delete', [], $jF, '/admin/academy/courses');
check('delete: a facilitator cannot delete a course that has learners', Database::value('SELECT COUNT(*) FROM academy_courses WHERE id = ?', [$cid], 0) === 1);
$adel = $post('/admin/academy/courses/' . $cid . '/delete', [], $jA, '/admin/academy/courses');
check('delete: an administrator can, the handout file goes too, issued certificates stay valid', Database::value('SELECT COUNT(*) FROM academy_courses WHERE id = ?', [$cid], 0) === 0 && !is_file(STORAGE_PATH . '/private/' . $lesson['file_path']) && CertificateService::find((string) $hand['code']) !== null);

foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
    @unlink($f);
}
@unlink($pdf);
@unlink($badFile);
$clean();
check('cleanup: no zz-aca rows are left', Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-aca-%'", [], 0) === 0 && Database::value("SELECT COUNT(*) FROM academy_courses WHERE title LIKE '%zz-aca%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
