<?php
/**
 * LAF Academy: VOLUNTEER FACILITATOR APPLICATIONS and CHALLENGES (quiz challenges, innovation challenges, hackathons, winners of the month), included by bin/selftest.php
 * (shares check() and $fail) or run alone:   /c/xampp/php/php.exe bin/selftest-academy-challenges.php
 * Covers: the public application form (files kept privately, validation, one open application per address), the administrator review (more information, approve = a
 * facilitator account whose temporary password is e-mailed, decline, delete with the files), the challenge engine (apply, approval, timed quiz graded on the server,
 * late answers, project submissions, judges, automatic ranking, tie-break, winners, certificates, reopen), the public pages (list, challenge, winners, winner photo
 * only with consent), the admin screens and their access rules. Everything it creates is removed (zz-chal-* rows).
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
use App\Core\Uploader;
use App\Models\User;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ChallengeInstaller;
use App\Services\Academy\ChallengeService;
use App\Services\Academy\ContentInstaller;
use App\Services\Academy\FacilitatorApplications;

echo "-- academy challenges and volunteer facilitators --\n";
$base = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null, bool $follow = false, array $files = []) use ($base): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'chj');
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
$post = static function (string $path, array $form, ?string &$jar, string $from, bool $follow = false, array $files = [], bool $hp = false) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $extra = ['_token' => $tok($page['body'])];
    if ($hp) {
        $ts = (string) (time() - 30);
        $extra['_hp_ts'] = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    }
    return $req('POST', $path, $form + $extra, $jar, $follow, $files);
};
$staffLogin = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/admin/login', [], $jar);
    $r = $req('POST', '/admin/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw], $jar);
    return in_array($r['code'], [302, 303], true);
};
$learnerLogin = static function (string $email, string $pw, ?string &$jar) use ($req, $tok): bool {
    $page = $req('GET', '/academy/login', [], $jar);
    $ts = (string) (time() - 30);
    $r = $req('POST', '/academy/login', ['_token' => $tok($page['body']), 'email' => $email, 'password' => $pw, '_hp_ts' => $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey())], $jar);
    return in_array($r['code'], [302, 303], true);
};
$png = static function (int $w, int $h): string {
    $raw = '';
    for ($y = 0; $y < $h; $y++) {
        $raw .= "\0" . str_repeat(chr(($y * 3) % 256), $w);
    }
    $chunk = static fn(string $t, string $d): string => pack('N', strlen($d)) . $t . $d . pack('N', crc32($t . $d));
    return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 0, 0, 0, 0)) . $chunk('IDAT', (string) gzcompress($raw)) . $chunk('IEND', '');
};
$pdfBytes = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R/Size 4>>\n%zz-chal\n%%EOF\n";

$clean = static function (): void {
    foreach (Database::all("SELECT e.photo_path, e.sub_file_path FROM academy_challenge_entries e JOIN academy_challenges c ON c.id = e.challenge_id WHERE c.title LIKE 'zz-chal%'") as $e) {
        Uploader::deletePrivate((string) ($e['photo_path'] ?? ''));
        Uploader::deletePrivate((string) ($e['sub_file_path'] ?? ''));
    }
    foreach (Database::all("SELECT cv_path, docs FROM academy_facilitator_applications WHERE email LIKE 'zz-chal-%@example.test'") as $a) {
        Uploader::deletePrivate((string) ($a['cv_path'] ?? ''));
        foreach (FacilitatorApplications::docs($a) as $d) {
            Uploader::deletePrivate($d['path']);
        }
    }
    Database::execute("DELETE FROM academy_challenges WHERE title LIKE 'zz-chal%'");
    Database::execute("DELETE FROM academy_certificates WHERE learner_name LIKE 'ZZ Chal%' OR course_title LIKE 'zz-chal%'");
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-chal-%@example.test'");
    Database::execute("DELETE FROM academy_facilitator_applications WHERE email LIKE 'zz-chal-%@example.test'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-chal-%@example.test'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$mailsBefore = glob(STORAGE_PATH . '/mail/*.eml') ?: [];
$mailText = static function (string $to) use ($mailsBefore): string {
    $out = '';
    foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $f) {
        $c = (string) file_get_contents($f);
        if (preg_match('/^To:.*' . preg_quote($to, '/') . '/mi', $c) === 1) {
            if (preg_match_all('/Content-Transfer-Encoding: base64\r?\n\r?\n(.*?)(?=\r?\n--)/s', $c, $parts) > 0) {
                foreach ($parts[1] as $b) {
                    $out .= (string) base64_decode((string) preg_replace('/\s+/', '', $b)) . "\n";
                }
            } else {
                $out .= quoted_printable_decode($c);
            }
        }
    }
    return $out;
};

$pwA = 'Admin test pass 71';
$pwF = 'Facilitator test pass 72';
$pwE = 'Editor test pass 73';
$mk = static fn(string $name, string $email, string $role, string $pw): int => (int) User::create(['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'role' => $role, 'is_active' => 1, 'must_change_password' => 0]);
$adminId = $mk('ZZ Chal Admin', 'zz-chal-admin@example.test', 'admin', $pwA);
$facId = $mk('ZZ Chal Facilitator', 'zz-chal-fac@example.test', 'facilitator', $pwF);
$mk('ZZ Chal Editor', 'zz-chal-editor@example.test', 'editor', $pwE);
$jA = $jF = $jE = null;
check('admin, facilitator and editor can sign in', $staffLogin('zz-chal-admin@example.test', $pwA, $jA) && $staffLogin('zz-chal-fac@example.test', $pwF, $jF) && $staffLogin('zz-chal-editor@example.test', $pwE, $jE));

// ---- volunteer facilitator application ---------------------------------------------------------------------------------------
$form = $req('GET', '/academy/facilitate');
check('facilitate: the public page explains it is voluntary and has the file fields', $form['code'] === 200 && str_contains($form['body'], 'voluntary') && str_contains($form['body'], 'name="cv"') && str_contains($form['body'], 'name="papers[]"'));
$cvFile = tempnam(sys_get_temp_dir(), 'zzcv');
file_put_contents($cvFile, $pdfBytes);
$app = ['name' => 'ZZ Chal Applicant', 'email' => 'zz-chal-app@example.test', 'topic' => 'office', 'topic_detail' => 'Excel formulas', 'headline' => 'Teacher', 'experience' => str_repeat('Ten years of teaching spreadsheets to adults and school pupils. ', 3),
    'motivation' => 'I want to help young people learn practical skills.', 'availability' => 'Saturdays', 'links' => 'https://example.org/me', 'volunteer' => '1', 'consent' => '1'];
$files = ['cv' => [$cvFile, 'application/pdf', 'my-cv.pdf'], 'papers[0]' => [$cvFile, 'application/pdf', 'degree.pdf'], 'papers[1]' => [$cvFile, 'application/pdf', 'cert.pdf']];
$noCv = $post('/academy/facilitate', $app, $jV, '/academy/facilitate', false, [], true);
check('facilitate: an application without a CV is refused', Database::value("SELECT COUNT(*) FROM academy_facilitator_applications WHERE email = 'zz-chal-app@example.test'", [], 0) === 0);
$noVol = $post('/academy/facilitate', ['volunteer' => ''] + $app, $jV, '/academy/facilitate', false, $files, true);
check('facilitate: the voluntary-role confirmation is required', Database::value("SELECT COUNT(*) FROM academy_facilitator_applications WHERE email = 'zz-chal-app@example.test'", [], 0) === 0);
$fake = tempnam(sys_get_temp_dir(), 'zzfake');
file_put_contents($fake, "MZ this is not a pdf at all");
$post('/academy/facilitate', $app, $jV, '/academy/facilitate', false, ['cv' => [$fake, 'application/pdf', 'cv.pdf']], true);
check('facilitate: a file that is not really a PDF or Word document is refused', Database::value("SELECT COUNT(*) FROM academy_facilitator_applications WHERE email = 'zz-chal-app@example.test'", [], 0) === 0);
$ok = $post('/academy/facilitate', $app, $jV, '/academy/facilitate', false, $files, true);
$row = Database::one("SELECT * FROM academy_facilitator_applications WHERE email = 'zz-chal-app@example.test'");
check('facilitate: a complete application is stored as pending and the visitor is thanked', $ok['code'] === 302 && str_contains($ok['loc'], 'facilitate/thanks') && $row !== null && $row['status'] === 'pending' && $row['topic'] === 'office');
check('facilitate: the CV and the papers are stored privately (not under the web root)', $row !== null && is_file(STORAGE_PATH . '/private/' . $row['cv_path']) && count(FacilitatorApplications::docs($row)) === 2 && !str_contains((string) $row['cv_path'], 'public'));
check('facilitate: the applicant gets a confirmation e-mail', str_contains($mailText('zz-chal-app@example.test'), 'received your application'));
$thanks = $req('GET', '/academy/facilitate/thanks', [], $jV);
check('facilitate: the thank-you page shows once', $thanks['code'] === 200 && str_contains($thanks['body'], 'Application received') && $req('GET', '/academy/facilitate/thanks', [], $jV)['code'] === 302);
$post('/academy/facilitate', ['name' => 'ZZ Chal Applicant'] + $app, $jV2, '/academy/facilitate', false, $files, true);
check('facilitate: a second application from the same address while one is waiting is refused', Database::value("SELECT COUNT(*) FROM academy_facilitator_applications WHERE email = 'zz-chal-app@example.test'", [], 0) === 1);
$aid = (int) $row['id'];
check('facilitator applications: staff without the Academy ability and facilitators get 403, an administrator can read it', $req('GET', '/admin/academy/facilitator-applications', [], $jE)['code'] === 403
    && $req('GET', '/admin/academy/facilitator-applications', [], $jF)['code'] === 403 && ($show = $req('GET', '/admin/academy/facilitator-applications/' . $aid, [], $jA))['code'] === 200 && str_contains($show['body'], 'Excel formulas'));
$cvDl = $req('GET', '/admin/academy/facilitator-applications/' . $aid . '/file/cv', [], $jA);
check('facilitator applications: an administrator can download the CV, an anonymous visitor cannot', $cvDl['code'] === 200 && str_starts_with($cvDl['body'], '%PDF') && $req('GET', '/admin/academy/facilitator-applications/' . $aid . '/file/cv')['code'] !== 200);
check('facilitator applications: a supporting paper downloads by its position', $req('GET', '/admin/academy/facilitator-applications/' . $aid . '/file/1', [], $jA)['code'] === 200 && $req('GET', '/admin/academy/facilitator-applications/' . $aid . '/file/9', [], $jA)['code'] === 404);
$post('/admin/academy/facilitator-applications/' . $aid, ['action' => 'more_info', 'applicant_note' => 'Please send a reference letter.', 'admin_note' => 'internal only'], $jA, '/admin/academy/facilitator-applications/' . $aid);
$mi = Database::one('SELECT * FROM academy_facilitator_applications WHERE id = ?', [$aid]);
check('facilitator applications: "more information" is saved and e-mailed with the message, the internal note stays private', $mi['status'] === 'more_info' && $mi['admin_note'] === 'internal only' && str_contains($mailText('zz-chal-app@example.test'), 'reference letter') && !str_contains($mailText('zz-chal-app@example.test'), 'internal only'));
$appr = $post('/admin/academy/facilitator-applications/' . $aid, ['action' => 'approve', 'applicant_note' => 'Welcome aboard.', 'email_credentials' => '1'], $jA, '/admin/academy/facilitator-applications/' . $aid, true);
$ap = Database::one('SELECT * FROM academy_facilitator_applications WHERE id = ?', [$aid]);
$newUser = Database::one("SELECT * FROM users WHERE email = 'zz-chal-app@example.test'");
check('approve: the application is approved and a facilitator account was created that must change its password', $ap['status'] === 'approved' && $newUser !== null && $newUser['role'] === 'facilitator' && (int) $newUser['must_change_password'] === 1 && (int) $ap['user_id'] === (int) $newUser['id']);
$mail = $mailText('zz-chal-app@example.test');
check('approve: the e-mail carries the sign-in address and a temporary password, and the admin sees it once', str_contains($mail, 'Temporary password:') && str_contains($mail, 'admin/login') && str_contains($appr['body'], 'will not be shown again'));
preg_match('/Temporary password: (\S+)/', $mail, $pm);
$jN = null;
check('approve: the new facilitator can sign in with that password and reaches the Academy', isset($pm[1]) && $staffLogin('zz-chal-app@example.test', $pm[1], $jN) && $req('GET', '/admin/academy', [], $jN)['code'] !== 403);
check('approve: the approved topic of the account is recorded', FacilitatorApplications::topicOfUser((int) $newUser['id']) === 'office');
$post('/admin/academy/facilitator-applications/' . $aid, ['action' => 'reject'], $jA, '/admin/academy/facilitator-applications/' . $aid);
check('approve: an approved application cannot be declined afterwards', Database::value('SELECT status FROM academy_facilitator_applications WHERE id = ?', [$aid]) === 'approved');
$post('/admin/academy/facilitator-applications/' . $aid . '/delete', [], $jA, '/admin/academy/facilitator-applications/' . $aid);
check('delete: the application and its files are removed', Database::value('SELECT COUNT(*) FROM academy_facilitator_applications WHERE id = ?', [$aid], 0) === 0 && !is_file(STORAGE_PATH . '/private/' . $row['cv_path']), (string) Database::value('SELECT COUNT(*) FROM academy_facilitator_applications WHERE id = ?', [$aid], 0) . ' ' . (is_file(STORAGE_PATH . '/private/' . $row['cv_path']) ? 'file-exists' : 'file-gone'));

// a second application is declined
$post('/academy/facilitate', ['email' => 'zz-chal-app2@example.test'] + $app, $jV3, '/academy/facilitate', false, $files, true);
$a2 = Database::one("SELECT * FROM academy_facilitator_applications WHERE email = 'zz-chal-app2@example.test'");
$post('/admin/academy/facilitator-applications/' . $a2['id'], ['action' => 'reject', 'applicant_note' => 'Not enough experience yet.'], $jA, '/admin/academy/facilitator-applications/' . $a2['id']);
check('decline: the application is declined and the applicant e-mailed with the message', Database::value('SELECT status FROM academy_facilitator_applications WHERE id = ?', [(int) $a2['id']]) === 'rejected' && str_contains($mailText('zz-chal-app2@example.test'), 'Not enough experience') && Database::value("SELECT COUNT(*) FROM users WHERE email = 'zz-chal-app2@example.test'", [], 0) === 0);

// ---- challenges: the engine --------------------------------------------------------------------------------------------------
$learner = static function (string $name, string $email, string $pw = 'Learner pass 8271'): array {
    $id = Database::insert('academy_learners', ['name' => $name, 'email' => $email, 'password_hash' => Auth::hashPassword($pw), 'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1]);
    return (array) Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
};
$L1 = $learner('ZZ Chal Amina Kato', 'zz-chal-l1@example.test');
$L2 = $learner('ZZ Chal Brian Opio', 'zz-chal-l2@example.test');
$L3 = $learner('ZZ Chal Carol Namu', 'zz-chal-l3@example.test');
$L4 = $learner('ZZ Chal David Okello', 'zz-chal-l4@example.test');
$script = "Q: Two plus two?\n* 4\n- 3\n- 5\n= Basic sums.\n\nQ: Capital of Uganda?\n- Jinja\n* Kampala\n- Gulu\n= Kampala.\n\nQ: Which are fruits? (choose all that apply)\n* Mango\n* Banana\n- Carrot\n= Carrot is a vegetable.\n\nQ: Color of the sky on a clear day?\n- Green\n- Red\n* Blue\n= Blue.\n\nQ: Freezing point of water in C?\n* 0\n- 10\n- 100\n= Zero.";
$now = time();
$mkChal = static function (array $o) use ($now): array {
    $id = Database::insert('academy_challenges', $o + ['slug' => 'zz-chal-' . bin2hex(random_bytes(3)), 'kind' => 'quiz', 'scoring' => 'quiz', 'title' => 'zz-chal Quiz', 'category' => 'basics', 'status' => 'published',
        'starts_at' => date('Y-m-d H:i:s', $now - 600), 'ends_at' => date('Y-m-d H:i:s', $now + 3600), 'quiz_minutes' => 20, 'winners_count' => 2, 'pass_score' => 40, 'quiz_script' => '']);
    return (array) ChallengeService::find($id);
};
$qc = $mkChal(['quiz_script' => $script, 'title' => 'zz-chal Quiz challenge', 'summary' => 'Test quiz']);
check('phase: a published challenge that has started and not ended is live', ChallengeService::phase($qc) === 'live' && ChallengeService::canApply($qc));
check('phase: draft, upcoming, judging and finished are told apart', ChallengeService::phase(['status' => 'draft'] + $qc) === 'draft'
    && ChallengeService::phase(['starts_at' => date('Y-m-d H:i:s', $now + 7200), 'apply_opens_at' => date('Y-m-d H:i:s', $now + 3600)] + $qc) === 'upcoming'
    && ChallengeService::phase(['ends_at' => date('Y-m-d H:i:s', $now - 5)] + $qc) === 'judging' && ChallengeService::phase(['finalized_at' => date('Y-m-d H:i:s')] + $qc) === 'finished');
check('phase: applications are closed after the closing time and when the challenge is full', !ChallengeService::canApply(['apply_closes_at' => date('Y-m-d H:i:s', $now - 5)] + $qc) && !ChallengeService::canApply(['max_entries' => 0] + $qc));
check('ordinals read well', ChallengeService::ordinal(1) === '1st' && ChallengeService::ordinal(2) === '2nd' && ChallengeService::ordinal(3) === '3rd' && ChallengeService::ordinal(11) === '11th' && ChallengeService::ordinal(22) === '22nd');

$r1 = ChallengeService::apply($qc, $L1, ['consent' => '1'], null);
$r2 = ChallengeService::apply($qc, $L2, [], null);
$r3 = ChallengeService::apply($qc, $L3, [], null);
$r4 = ChallengeService::apply($qc, $L4, [], null);
check('apply: an automatic challenge approves the entry at once, and a learner cannot apply twice', $r1['ok'] && $r1['entry']['status'] === 'approved' && ChallengeService::apply($qc, $L1, [], null)['ok'] === false);
$e1 = (array) $r1['entry'];
$photoTmp = tempnam(sys_get_temp_dir(), 'zzpng');
file_put_contents($photoTmp, $png(320, 320));
$ph = ChallengeService::savePhoto($e1, ['name' => 'me.png', 'tmp_name' => $photoTmp, 'error' => 0, 'size' => filesize($photoTmp), 'type' => 'image/png']);
$e1 = (array) ChallengeService::entry((int) $e1['id']);
check('photo: a valid picture is stored privately and consent is recorded', $ph['ok'] && !empty($e1['photo_path']) && is_file(STORAGE_PATH . '/private/' . $e1['photo_path']) && !empty($e1['photo_consent_at']), (string) ($ph['error'] ?? ''));
$tiny = tempnam(sys_get_temp_dir(), 'zzpng2');
file_put_contents($tiny, $png(40, 40));
check('photo: a picture that is too small is refused', ChallengeService::savePhoto($e1, ['name' => 'x.png', 'tmp_name' => $tiny, 'error' => 0, 'size' => filesize($tiny), 'type' => 'image/png'])['ok'] === false);
check('photo: not public before the results, and only for winners with consent', !ChallengeService::photoPublic($e1));

check('quiz: nobody can answer before starting, and the questions carry no answers', ChallengeService::submitQuiz($qc, $e1, [0 => [0]]) === null);
$e1 = (array) ChallengeService::startQuiz($qc, $e1);
$shown = ChallengeService::shownQuestions($qc, $e1);
check('quiz: starting records the clock and a shuffled layout of the 5 questions', $e1['quiz_started_at'] !== null && count($shown) === 5 && !str_contains(json_encode($shown), 'correct') && ChallengeService::startQuiz($qc, $e1)['quiz_layout'] === $e1['quiz_layout']);
// answer correctly using the script (find each shown question's right options)
$parsed = ChallengeService::questions($qc);
$answerAll = static function (array $c, array $entry, int $wrong) use ($parsed): array {
    $layout = json_decode((string) $entry['quiz_layout'], true);
    $ans = [];
    foreach ($layout as $n => $l) {
        $q = $parsed[$l['i']];
        $pos = [];
        foreach ($l['o'] as $p => $oi) {
            if ($q['options'][$oi]['correct']) {
                $pos[] = $p;
            }
        }
        if ($n < $wrong) {
            $pos = [($pos[0] + 1) % count($l['o'])];             // a different option: wrong
            if ($q['type'] === 'multiple') {
                $pos = [];
            }
        }
        $ans[$n] = $pos;
    }
    return $ans;
};
$d1 = ChallengeService::submitQuiz($qc, $e1, $answerAll($qc, $e1, 0));
check('quiz: a perfect set of answers scores 5 of 5 (100%), including the choose-all question', (int) $d1['quiz_correct'] === 5 && (float) $d1['score'] === 100.0 && $d1['quiz_submitted_at'] !== null);
check('quiz: a second submission is refused (one attempt)', ChallengeService::submitQuiz($qc, $d1, $answerAll($qc, $e1, 0)) === null);
$e2 = (array) ChallengeService::startQuiz($qc, (array) $r2['entry']);
$d2 = ChallengeService::submitQuiz($qc, $e2, $answerAll($qc, $e2, 2));
check('quiz: two wrong answers score 3 of 5 (60%)', (int) $d2['quiz_correct'] === 3 && (float) $d2['score'] === 60.0);
$e4 = (array) ChallengeService::startQuiz($qc, (array) $r4['entry']);
Database::update('academy_challenge_entries', ['quiz_started_at' => date('Y-m-d H:i:s', $now - 3000)], 'id = ?', [(int) $e4['id']]);
$e4 = (array) ChallengeService::entry((int) $e4['id']);
$d4 = ChallengeService::submitQuiz($qc, $e4, $answerAll($qc, $e4, 0));
check('quiz: answers sent long after the time limit count as blank (score 0)', (int) $d4['quiz_correct'] === 0 && (float) $d4['score'] === 0.0);
ChallengeService::startQuiz($qc, (array) $r3['entry']);                      // L3 starts and never submits
check('settle: nothing is final before the end', ChallengeService::settle() === 0 && empty(ChallengeService::find((int) $qc['id'])['finalized_at']));
check('the leaderboard is hidden until the results are final', ChallengeService::leaderboard($qc) === []);

// results
Database::update('academy_challenges', ['starts_at' => date('Y-m-d H:i:s', $now - 4000), 'ends_at' => date('Y-m-d H:i:s', $now - 5)], 'id = ?', [(int) $qc['id']]);
check('settle: when the quiz challenge ends the system makes the results by itself', ChallengeService::settle() >= 1);
$qc = (array) ChallengeService::find((int) $qc['id']);
$board = ChallengeService::leaderboard($qc);
check('results: entrants who finished are ranked by score; the unfinished one is not ranked', count($board) === 3 && $board[0]['learner_id'] == $L1['id'] && (int) $board[0]['rank_no'] === 1 && $board[1]['learner_id'] == $L2['id'] && !array_filter($board, static fn($b) => $b['learner_id'] == $L3['id']));
check('results: the top two (winners_count 2) are winners with their places; a score of 0 never wins', (int) $board[0]['is_winner'] === 1 && $board[0]['award'] === '1st place' && (int) $board[1]['is_winner'] === 1 && $board[1]['award'] === '2nd place' && (int) $board[2]['is_winner'] === 0);
$c1 = Database::one('SELECT * FROM academy_certificates WHERE learner_id = ? AND challenge_id = ?', [(int) $L1['id'], (int) $qc['id']]);
check('certificates: the winner gets a certificate of achievement (kind challenge, award text, score)', $c1 !== null && $c1['kind'] === 'challenge' && $c1['award'] === 'Winner, 1st place' && (float) $c1['score_pct'] === 100.0);
check('certificates: an entrant below the pass score gets none; the settle is not repeated', Database::value('SELECT COUNT(*) FROM academy_certificates WHERE learner_id = ? AND challenge_id = ?', [(int) $L4['id'], (int) $qc['id']], 0) === 0 && ChallengeService::settle() === 0);
$cp = $req('GET', '/academy/certificate/' . $c1['code']);
check('certificates: the public check page says achievement and names the award', $cp['code'] === 200 && str_contains($cp['body'], 'Certificate of achievement') && str_contains($cp['body'], '1st place') && str_contains($cp['body'], 'zz-chal Quiz challenge'));
$pdf = $req('GET', '/academy/certificate/' . $c1['code'] . '/pdf');
check('certificates: the PDF is produced', $pdf['code'] === 200 && str_starts_with($pdf['body'], '%PDF') && str_contains($pdf['head'], 'application/pdf'));
check('certificates: the winner was e-mailed with the certificate number', str_contains($mailText('zz-chal-l1@example.test'), $c1['code']) && str_contains($mailText('zz-chal-l1@example.test'), 'You won'));
$months = ChallengeService::winnersByMonth(3);
$names = array_column($months[0]['winners'] ?? [], 'display_name');
check('winners of the month: this month lists both winners, the one without consent by first name and initial only', in_array('ZZ Chal Amina Kato', $names, true) && in_array('ZZ O.', $names, true) && !in_array('ZZ Chal Brian Opio', $names, true));
$wp = $req('GET', '/academy/winners');
check('winners page: shows the month, the winner with a picture link, and the award', $wp['code'] === 200 && str_contains($wp['body'], date('F Y')) && str_contains($wp['body'], 'ZZ Chal Amina Kato') && str_contains($wp['body'], '1st place'));
$e1 = (array) ChallengeService::entry((int) $e1['id']);
$e2b = (array) ChallengeService::entry((int) $e2['id']);
$ph1 = $req('GET', '/academy/winners/' . $e1['id'] . '/photo');
check('winner photo: served for a winner who agreed to publication, hidden (404) for one who did not or for a non-winner', $ph1['code'] === 200 && $req('GET', '/academy/winners/' . $e2b['id'] . '/photo')['code'] === 404 && $req('GET', '/academy/winners/' . $d4['id'] . '/photo')['code'] === 404);
Database::update('academy_challenge_entries', ['publish_photo' => 0], 'id = ?', [(int) $e1['id']]);
check('winner photo: staff can hide it from the winners page', $req('GET', '/academy/winners/' . $e1['id'] . '/photo')['code'] === 404);
Database::update('academy_challenge_entries', ['publish_photo' => 1], 'id = ?', [(int) $e1['id']]);

// reopen
ChallengeService::reopen($qc);
check('reopen: winners and rankings are cleared and the certificates of the challenge are withdrawn', empty(ChallengeService::find((int) $qc['id'])['finalized_at']) && Database::value('SELECT COUNT(*) FROM academy_challenge_entries WHERE challenge_id = ? AND is_winner = 1', [(int) $qc['id']], 0) === 0
    && Database::value('SELECT COUNT(*) FROM academy_certificates WHERE challenge_id = ? AND revoked_at IS NULL', [(int) $qc['id']], 0) === 0);
$fin = ChallengeService::finalize((array) ChallengeService::find((int) $qc['id']));
check('reopen: making the results again works and issues fresh certificates', $fin['ok'] && $fin['winners'] === 2 && Database::value('SELECT COUNT(*) FROM academy_certificates WHERE challenge_id = ? AND revoked_at IS NULL', [(int) $qc['id']], 0) >= 2);

// tie-break: same score, the faster finisher wins
$tc = $mkChal(['quiz_script' => $script, 'title' => 'zz-chal Tie quiz', 'winners_count' => 1]);
$ta = (array) ChallengeService::apply($tc, $L1, [], null)['entry'];
$tb = (array) ChallengeService::apply($tc, $L2, [], null)['entry'];
foreach ([$ta, $tb] as $i => $e) {
    $e = (array) ChallengeService::startQuiz($tc, $e);
    ChallengeService::submitQuiz($tc, $e, $answerAll($tc, $e, 0));
}
Database::update('academy_challenge_entries', ['quiz_seconds' => 300], 'id = ?', [(int) $ta['id']]);
Database::update('academy_challenge_entries', ['quiz_seconds' => 120], 'id = ?', [(int) $tb['id']]);
Database::update('academy_challenges', ['ends_at' => date('Y-m-d H:i:s', $now - 5)], 'id = ?', [(int) $tc['id']]);
ChallengeService::settle();
check('tie-break: with the same score the faster finishing time wins', (int) Database::value('SELECT is_winner FROM academy_challenge_entries WHERE id = ?', [(int) $tb['id']]) === 1 && (int) Database::value('SELECT is_winner FROM academy_challenge_entries WHERE id = ?', [(int) $ta['id']]) === 0);

// ---- innovation challenge: manual approval, project, judges ------------------------------------------------------------------
$ic = $mkChal(['kind' => 'innovation', 'scoring' => 'judged', 'title' => 'zz-chal Innovation', 'approval' => 'manual', 'team_max' => 3, 'quiz_script' => null, 'quiz_minutes' => null,
    'criteria' => json_encode([['name' => 'Idea', 'weight' => 60], ['name' => 'Delivery', 'weight' => 40]]), 'winners_count' => 1, 'pass_score' => 30]);
$ia = ChallengeService::apply($ic, $L1, ['idea_title' => 'Short', 'idea_summary' => 'too short'], null);
check('innovation: the idea title and summary are required', $ia['ok'] === false && isset($ia['errors']['idea_summary']));
$tm = ChallengeService::apply($ic, $L1, ['idea_title' => 'Water app', 'idea_summary' => str_repeat('An app that helps villages report broken water points quickly. ', 2), 'members' => 'A, B, C, D'], null);
check('innovation: a team larger than the limit is refused', $tm['ok'] === false && isset($tm['errors']['members']));
$i1 = ChallengeService::apply($ic, $L1, ['idea_title' => 'Water app', 'idea_summary' => str_repeat('An app that helps villages report broken water points quickly. ', 2), 'team_name' => 'Aqua', 'members' => 'Joan, Peter'], null);
$i2 = ChallengeService::apply($ic, $L2, ['idea_title' => 'School records', 'idea_summary' => str_repeat('A simple records tool for small schools without internet at all times. ', 2)], null);
check('innovation: with manual approval the entries wait for staff', $i1['ok'] && $i1['entry']['status'] === 'pending' && !ChallengeService::canSubmitProject($ic, (array) $i1['entry']));
$post('/admin/academy/challenges/entries/' . $i1['entry']['id'] . '/decision', ['to' => 'approved'], $jA, '/admin/academy/challenges/' . $ic['id'] . '/entries');
$post('/admin/academy/challenges/entries/' . $i2['entry']['id'] . '/decision', ['to' => 'approved'], $jA, '/admin/academy/challenges/' . $ic['id'] . '/entries');
$ie1 = (array) ChallengeService::entry((int) $i1['entry']['id']);
$ie2 = (array) ChallengeService::entry((int) $i2['entry']['id']);
check('innovation: staff approve the entries from the admin and the learners are e-mailed', $ie1['status'] === 'approved' && str_contains($mailText('zz-chal-l1@example.test'), 'has been approved') && ChallengeService::canSubmitProject($ic, $ie1), $ie1['status']);
$bad = ChallengeService::submitProject($ic, $ie1, ['sub_title' => 'Aqua', 'sub_summary' => 'short'], null);
check('innovation: a project needs a summary and a link or a file', $bad['ok'] === false && isset($bad['errors']['sub_summary']));
$sum = str_repeat('Aqua lets villagers photograph a broken pump and send its location by SMS to the district office, which then plans the repairs. ', 2);
$p1 = ChallengeService::submitProject($ic, $ie1, ['sub_title' => 'Aqua', 'sub_summary' => $sum, 'sub_url' => 'http://insecure.example.org'], null);
check('innovation: a project link must be https', $p1['ok'] === false && isset($p1['errors']['sub_url']));
$pf = tempnam(sys_get_temp_dir(), 'zzpf');
file_put_contents($pf, $pdfBytes);
$p1 = ChallengeService::submitProject($ic, $ie1, ['sub_title' => 'Aqua', 'sub_summary' => $sum, 'sub_url' => 'https://example.org/aqua'], ['name' => 'aqua.pdf', 'tmp_name' => $pf, 'error' => 0, 'size' => filesize($pf), 'type' => 'application/pdf']);
$p2 = ChallengeService::submitProject($ic, $ie2, ['sub_title' => 'Records', 'sub_summary' => $sum, 'sub_url' => 'https://example.org/records'], null);
$ie1 = (array) ChallengeService::entry((int) $ie1['id']);
check('innovation: projects are saved (link and a private file)', $p1['ok'] && $p2['ok'] && $ie1['sub_title'] === 'Aqua' && is_file(STORAGE_PATH . '/private/' . $ie1['sub_file_path']));
$jp = $req('GET', '/admin/academy/challenges/' . $ic['id'] . '/judge', [], $jA);
check('judging: an administrator sees both projects and the criteria', $jp['code'] === 200 && str_contains($jp['body'], 'Aqua') && str_contains($jp['body'], 'Delivery'));
check('judging: an editor (no Academy ability) is refused, a quiz challenge has no judging page', $req('GET', '/admin/academy/challenges/' . $ic['id'] . '/judge', [], $jE)['code'] === 403 && $req('GET', '/admin/academy/challenges/' . $qc['id'] . '/judge', [], $jA)['code'] === 404);
$post('/admin/academy/challenges/' . $ic['id'] . '/judge/' . $ie1['id'], ['points' => [9, 8]], $jA, '/admin/academy/challenges/' . $ic['id'] . '/judge');
$post('/admin/academy/challenges/' . $ic['id'] . '/judge/' . $ie1['id'], ['points' => [7, 7]], $jF, '/admin/academy/challenges/' . $ic['id'] . '/judge');
$post('/admin/academy/challenges/' . $ic['id'] . '/judge/' . $ie2['id'], ['points' => [5, 5]], $jA, '/admin/academy/challenges/' . $ic['id'] . '/judge');
$j1 = Database::all('SELECT * FROM academy_challenge_judgments WHERE entry_id = ? ORDER BY judge_id', [(int) $ie1['id']]);
check('judging: each judge\'s weighted total is stored (9/10 of 60 + 8/10 of 40 = 86; 7 and 7 = 70)', count($j1) === 2 && (float) $j1[0]['total'] + (float) $j1[1]['total'] === 156.0);
$post('/admin/academy/challenges/' . $ic['id'] . '/judge/' . $ie1['id'], ['points' => [10, 10]], $jA, '/admin/academy/challenges/' . $ic['id'] . '/judge');
check('judging: a judge can change their own scores and there is still one row per judge', (int) Database::value('SELECT COUNT(*) FROM academy_challenge_judgments WHERE entry_id = ?', [(int) $ie1['id']], 0) === 2);
$post('/admin/academy/challenges/' . $ic['id'] . '/judge/' . $ie1['id'], ['points' => [9, 8]], $jA, '/admin/academy/challenges/' . $ic['id'] . '/judge');
check('settle: a judged challenge is not made final at its end while judging is still open', (function () use ($ic, $now): bool {
    Database::update('academy_challenges', ['starts_at' => date('Y-m-d H:i:s', $now - 4000), 'ends_at' => date('Y-m-d H:i:s', $now - 100), 'judging_ends_at' => date('Y-m-d H:i:s', $now + 3600)], 'id = ?', [(int) $ic['id']]);
    return ChallengeService::settle() === 0 && empty(ChallengeService::find((int) $ic['id'])['finalized_at']) && ChallengeService::phase((array) ChallengeService::find((int) $ic['id'])) === 'judging';
})());
check('judging: nobody scores after the results are final', (function () use ($ic, $now, $ie1): bool {
    Database::update('academy_challenges', ['judging_ends_at' => date('Y-m-d H:i:s', $now - 5)], 'id = ?', [(int) $ic['id']]);
    $ok = ChallengeService::settle() === 1;
    $c = (array) ChallengeService::find((int) $ic['id']);
    return $ok && ChallengeService::judge($c, (array) ChallengeService::entry((int) $ie1['id']), 1, [0, 0], '') === false;
})());
$w1 = (array) ChallengeService::entry((int) $ie1['id']);
$w2 = (array) ChallengeService::entry((int) $ie2['id']);
check('results: the project with the highest average wins; the other keeps a rank but wins nothing (1 winner)', (int) $w1['is_winner'] === 1 && (int) $w1['rank_no'] === 1 && (int) $w2['rank_no'] === 2 && (int) $w2['is_winner'] === 0);
check('results: scores are the judges\' weighted averages', abs((float) $w1['score'] - (86 + 70) / 2) < 0.01 && abs((float) $w2['score'] - 50.0) < 0.01, (string) $w1['score'] . ' / ' . (string) $w2['score']);
check('certificates: the runner-up at or above the pass score (30) gets a certificate of participation', (int) Database::value("SELECT COUNT(*) FROM academy_certificates WHERE learner_id = ? AND challenge_id = ? AND award = 'Participant'", [(int) $L2['id'], (int) $ic['id']], 0) === 1);
$cp2 = $req('GET', '/academy/certificate/' . Database::value("SELECT code FROM academy_certificates WHERE learner_id = ? AND challenge_id = ? LIMIT 1", [(int) $L2['id'], (int) $ic['id']]));
check('certificates: the participation certificate says participation, and the winner page shows the team name', str_contains($cp2['body'], 'Certificate of participation') && str_contains($req('GET', '/academy/winners')['body'], 'Aqua'));

// ---- public pages and the learner flow over HTTP ------------------------------------------------------------------------------
$open = $mkChal(['title' => 'zz-chal Open quiz', 'quiz_script' => $script, 'summary' => 'An open quiz', 'slug' => 'zz-chal-open']);
$draft = $mkChal(['title' => 'zz-chal Draft quiz', 'status' => 'draft', 'slug' => 'zz-chal-draft', 'quiz_script' => $script]);
$list = $req('GET', '/academy/challenges');
check('public list: shows the published challenge, never a draft', $list['code'] === 200 && str_contains($list['body'], 'zz-chal Open quiz') && !str_contains($list['body'], 'zz-chal Draft quiz'));
check('public challenge page: opens, a draft is a 404, an anonymous visitor is asked to sign in to apply', ($pg = $req('GET', '/academy/challenges/zz-chal-open'))['code'] === 200 && str_contains($pg['body'], 'Sign in to apply') && $req('GET', '/academy/challenges/zz-chal-draft')['code'] === 404);
$jL = null;
check('learner: signs in', $learnerLogin('zz-chal-l3@example.test', 'Learner pass 8271', $jL));
$apply = $post('/academy/challenges/zz-chal-open/apply', ['consent' => '1'], $jL, '/academy/challenges/zz-chal-open', false, ['photo' => [$photoTmp, 'image/png', 'me.png']]);
$en = Database::one('SELECT * FROM academy_challenge_entries WHERE challenge_id = ? AND learner_id = ?', [(int) $open['id'], (int) $L3['id']]);
check('learner: applying over HTTP creates an approved entry with the photo and consent', $apply['code'] === 302 && $en !== null && $en['status'] === 'approved' && !empty($en['photo_path']) && !empty($en['photo_consent_at']));
$pg = $req('GET', '/academy/challenges/zz-chal-open', [], $jL);
check('learner: the page now offers to start the quiz (5 questions, time limit) and has no spoilers', str_contains($pg['body'], 'Start the quiz') && str_contains($pg['body'], '5 questions') && !str_contains($pg['body'], 'Capital of Uganda'));
check('learner: the quiz page needs a started attempt', $req('GET', '/academy/challenges/zz-chal-open/quiz', [], $jL)['code'] === 302);
$st = $post('/academy/challenges/zz-chal-open/quiz/start', [], $jL, '/academy/challenges/zz-chal-open');
$qp = $req('GET', '/academy/challenges/zz-chal-open/quiz', [], $jL);
check('learner: after starting, the quiz page shows the timer and the questions (no right answers marked)', $st['code'] === 302 && $qp['code'] === 200 && str_contains($qp['body'], 'data-ac-timer') && str_contains($qp['body'], 'Kampala') && !str_contains($qp['body'], 'Basic sums'));
$en = (array) Database::one('SELECT * FROM academy_challenge_entries WHERE id = ?', [(int) $en['id']]);
$sub = $post('/academy/challenges/zz-chal-open/quiz/submit', ['a' => $answerAll($open, $en, 0)], $jL, '/academy/challenges/zz-chal-open/quiz');
$en = (array) Database::one('SELECT * FROM academy_challenge_entries WHERE id = ?', [(int) $en['id']]);
check('learner: submitting over HTTP grades on the server (5 of 5)', $sub['code'] === 302 && (int) $en['quiz_correct'] === 5 && $en['quiz_submitted_at'] !== null);
check('learner: after submitting the page shows the score and no quiz page', str_contains($req('GET', '/academy/challenges/zz-chal-open', [], $jL)['body'], '5 of 5') && $req('GET', '/academy/challenges/zz-chal-open/quiz', [], $jL)['code'] === 302);
$jX = null;
check('access: an anonymous visitor cannot apply or start a quiz (sent to sign in)', in_array($req('POST', '/academy/challenges/zz-chal-open/apply', [], $jX)['code'], [302, 403, 419], true));

// ---- admin screens -------------------------------------------------------------------------------------------------------------
$al = $req('GET', '/admin/academy/challenges', [], $jA);
check('admin challenges: the list opens for an administrator and shows the starter challenges offer', $al['code'] === 200 && str_contains($al['body'], 'zz-chal Open quiz'));
check('admin challenges: a facilitator can open the list but not create, edit or finalise', $req('GET', '/admin/academy/challenges', [], $jF)['code'] === 200 && $req('GET', '/admin/academy/challenges/create', [], $jF)['code'] === 403
    && $req('GET', '/admin/academy/challenges/' . $open['id'] . '/edit', [], $jF)['code'] === 403 && $req('GET', '/admin/academy/challenges/' . $open['id'] . '/entries', [], $jF)['code'] === 403);
check('admin challenges: the form and the entries page open for an administrator', $req('GET', '/admin/academy/challenges/create?kind=hackathon', [], $jA)['code'] === 200 && $req('GET', '/admin/academy/challenges/' . $open['id'] . '/edit', [], $jA)['code'] === 200
    && str_contains(($en2 = $req('GET', '/admin/academy/challenges/' . $open['id'] . '/entries', [], $jA))['body'], 'ZZ Chal Carol Namu') && $en2['code'] === 200);
$f = ['title' => 'zz-chal Created', 'kind' => 'quiz', 'category' => 'basics', 'summary' => 's', 'starts_at' => date('Y-m-d\TH:i', $now + 86400), 'ends_at' => date('Y-m-d\TH:i', $now + 90000), 'team_max' => '1', 'approval' => 'auto',
    'winners_count' => '3', 'pass_score' => '50', 'quiz_minutes' => '25', 'quiz_script' => $script, 'certify_participants' => '1'];
$post('/admin/academy/challenges', ['ends_at' => date('Y-m-d\TH:i', $now + 80000)] + $f, $jA, '/admin/academy/challenges/create');
check('admin challenges: an end before the start is refused', Database::value("SELECT COUNT(*) FROM academy_challenges WHERE title = 'zz-chal Created'", [], 0) === 0);
$post('/admin/academy/challenges', ['quiz_script' => "Q: Only one\n* a\n- b"] + $f, $jA, '/admin/academy/challenges/create');
check('admin challenges: a quiz with fewer than 5 questions is refused', Database::value("SELECT COUNT(*) FROM academy_challenges WHERE title = 'zz-chal Created'", [], 0) === 0);
$post('/admin/academy/challenges', $f, $jA, '/admin/academy/challenges/create');
$cr = Database::one("SELECT * FROM academy_challenges WHERE title = 'zz-chal Created'");
check('admin challenges: a valid quiz challenge is saved as a draft with a web address', $cr !== null && $cr['status'] === 'draft' && $cr['scoring'] === 'quiz' && $cr['slug'] !== '' && (int) $cr['winners_count'] === 3);
$post('/admin/academy/challenges/' . $cr['id'] . '/status', ['to' => 'published'], $jA, '/admin/academy/challenges');
check('admin challenges: it can be published, and the public page then opens', Database::value('SELECT status FROM academy_challenges WHERE id = ?', [(int) $cr['id']]) === 'published' && $req('GET', '/academy/challenges/' . $cr['slug'])['code'] === 200);
$jC = null;
check('upcoming: the page of a challenge that has not started says so and offers no quiz', str_contains($req('GET', '/academy/challenges/' . $cr['slug'], [], $jC)['body'], 'Applications open') || str_contains($req('GET', '/academy/challenges/' . $cr['slug'])['body'], 'Sign in to apply'));
$post('/admin/academy/challenges/' . $cr['id'] . '/delete', [], $jA, '/admin/academy/challenges');
check('admin challenges: a challenge without entries can be deleted', Database::value('SELECT COUNT(*) FROM academy_challenges WHERE id = ?', [(int) $cr['id']], 0) === 0);
$post('/admin/academy/challenges/' . $open['id'] . '/delete', [], $jA, '/admin/academy/challenges');
check('admin challenges: one with entries is not deleted until it is cancelled', Database::value('SELECT COUNT(*) FROM academy_challenges WHERE id = ?', [(int) $open['id']], 0) === 1);
$pre = (int) Database::value('SELECT COUNT(*) FROM academy_challenges', [], 0);
$post('/admin/academy/challenges/starter', [], $jA, '/admin/academy/challenges');
$starterDrafts = (int) Database::value("SELECT COUNT(*) FROM academy_challenges WHERE source = 'starter'", [], 0);
check('starter challenges: the ready-made quizzes, innovation challenge and hackathon are added as dateless drafts', $starterDrafts >= 8 && ChallengeInstaller::pending() === 0
    && (int) Database::value("SELECT COUNT(*) FROM academy_challenges WHERE source = 'starter' AND status = 'draft' AND starts_at IS NULL", [], 0) === $starterDrafts);
check('starter challenges: adding them again changes nothing', ChallengeInstaller::installAll() === 0);
$starterQuiz = Database::one("SELECT * FROM academy_challenges WHERE slug = 'ict-basics-challenge'");
check('starter challenges: a starter quiz has 30 questions and parses', $starterQuiz !== null && count(ChallengeService::questions($starterQuiz)) === 30);
$post('/admin/academy/challenges/' . $starterQuiz['id'] . '/status', ['to' => 'published'], $jA, '/admin/academy/challenges');
check('admin challenges: a challenge without dates cannot be published', Database::value('SELECT status FROM academy_challenges WHERE id = ?', [(int) $starterQuiz['id']], '') === 'draft');

// ---- first-run installer marker --------------------------------------------------------------------------------------------------
\App\Core\Settings::set('academy_starter_done', '1');
Database::execute('DELETE FROM rate_limits');
$before = (int) Database::value("SELECT COUNT(*) FROM academy_courses WHERE slug IN ('git-github-collaboration','ux-ui-design-basics')", [], 0);
ContentInstaller::autoInstall();
check('first run: a site with the first starter courses receives the newer ones once, and the marker moves to the new version', (string) setting('academy_starter_done', '') === ContentInstaller::VERSION
    && (int) Database::value("SELECT COUNT(*) FROM academy_courses WHERE slug IN ('git-github-collaboration','ux-ui-design-basics')", [], 0) >= max(2, $before));

// ---- cleanup -----------------------------------------------------------------------------------------------------------------------
foreach (array_diff(glob(STORAGE_PATH . '/mail/*.eml') ?: [], $mailsBefore) as $mf) {
    @unlink($mf);
}
@unlink($cvFile);
@unlink($fake);
@unlink($photoTmp);
@unlink($tiny);
@unlink($pf);
$clean();
check('cleanup: no zz-chal rows are left', Database::value("SELECT COUNT(*) FROM academy_challenges WHERE title LIKE 'zz-chal%'", [], 0) === 0 && Database::value("SELECT COUNT(*) FROM users WHERE email LIKE 'zz-chal-%'", [], 0) === 0
    && Database::value("SELECT COUNT(*) FROM academy_learners WHERE email LIKE 'zz-chal-%'", [], 0) === 0);

if (!empty($standalone)) {
    echo $fail === 0 ? "\nALL CHECKS PASSED\n" : "\n{$fail} CHECK(S) FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
