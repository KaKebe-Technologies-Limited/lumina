<?php
declare(strict_types=1);

/**
 * LAF Academy: the learner portal (tabs, profile with bio, messages between the team and each learner, yellow notifications, the link to the portal). Included by
 * bin/selftest.php or run alone:
 *   /c/xampp/php/php.exe bin/selftest-academy-portal.php
 * Needs the local web server. Removes its own rows (zz-lpo-*).
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
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerMessages;

echo "-- academy portal --\n";
$clean = static function (): void {
    Database::execute("DELETE FROM academy_learners WHERE email LIKE 'zz-lpo-%'");
    Database::execute("DELETE FROM academy_inquiries WHERE email LIKE 'zz-lpo-%'");
    Database::execute("DELETE FROM users WHERE email LIKE 'zz-lpo-%'");
    Database::execute("DELETE FROM audit_log WHERE summary LIKE '%zz-lpo-%'");
    Database::execute('DELETE FROM rate_limits');
};
$clean();
$pw = 'Sentence-for-test-88';
$mk = static fn(string $tag): int => (int) Database::insert('academy_learners', [
    'name' => 'Zed Lpo ' . $tag, 'email' => 'zz-lpo-' . $tag . '@example.test', 'password_hash' => Auth::hashPassword($pw), 'phone' => '+256700000010', 'country' => 'Uganda', 'whatsapp' => '256772000010',
    'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
]);
$a = $mk('a');
$b = $mk('b');
\App\Services\Academy\LearnerRegistry::assign($a);
\App\Services\Academy\LearnerRegistry::assign($b);

check('portal: the messages table and the bio column exist after the schema migration', LearnerMessages::ready() && LearnerAuth::bioReady());

// ------------------------------------------------------------------------------------------------------------- the messages service
check('messages: text is cleaned (tags removed, line breaks kept, cut at 2000 characters)', LearnerMessages::clean("Hello <b>there</b><script>alert(1)</script>\n\n\n\nBye") === "Hello therealert(1)\n\nBye" && mb_strlen(LearnerMessages::clean(str_repeat('x', 3000))) === 2000);
check('messages: an empty message and an unknown learner are refused', !LearnerMessages::send($a, 'learner', '  ')['ok'] && !LearnerMessages::send(99999999, 'admin', 'Hello there')['ok'] && !LearnerMessages::send($a, 'robot', 'Hello there')['ok']);
$m1 = LearnerMessages::send($a, 'admin', 'Welcome to the Academy!', 1);
$m2 = LearnerMessages::send($a, 'learner', 'Thank you, when does my class start?');
$m3 = LearnerMessages::send($b, 'learner', 'I cannot open my lesson');
check('messages: both sides can write', $m1['ok'] && $m2['ok'] && $m3['ok']);
check('messages: unread counts: the learner sees the team\'s message, the team sees the learners\' ones', LearnerMessages::unreadForLearner($a) === 1 && LearnerMessages::unreadForLearner($b) === 0 && LearnerMessages::unreadFrom($a) === 1 && LearnerMessages::unreadFrom($b) === 1 && LearnerMessages::unreadForAdmin() >= 2);
check('messages: the conversation is in order and belongs to one learner only', array_column(LearnerMessages::thread($a), 'id') === [$m1['id'], $m2['id']] && array_column(LearnerMessages::thread($b), 'id') === [$m3['id']]);
$inbox = LearnerMessages::inbox(50, 'zz-lpo');
check('messages: the inbox lists one row per learner with the unread number, newest first', count($inbox) === 2 && (int) $inbox[0]['id'] === $b && (int) $inbox[0]['unread'] === 1 && (int) $inbox[1]['total'] === 2);
check('messages: the inbox can be searched by name, e-mail or registration number', count(LearnerMessages::inbox(50, 'zz-lpo-b@')) === 1 && count(LearnerMessages::inbox(50, (string) Database::value('SELECT reg_no FROM academy_learners WHERE id = ?', [$a]))) === 1);
LearnerMessages::markRead($a, 'learner');
check('messages: reading marks only what the other side wrote', LearnerMessages::unreadForLearner($a) === 0 && LearnerMessages::unreadFrom($a) === 1);
LearnerMessages::markRead($a, 'admin');
check('messages: and the team reading clears the learner\'s messages', LearnerMessages::unreadFrom($a) === 0);

// ------------------------------------------------------------------------------------------------------------- over HTTP
$site = 'http://localhost/' . basename(dirname(__DIR__));
$req = static function (string $method, string $path, array $form = [], ?string &$jar = null, array $headers = []) use ($site): array {
    $jar ??= tempnam(sys_get_temp_dir(), 'lpj');
    $ch = curl_init($site . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => 60, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => $headers]);
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
$tok = static fn(string $html): string => preg_match('/name="_token" value="([^"]+)"/', $html, $m) === 1 ? $m[1] : '';
$post = static function (string $path, array $form, ?string &$jar, string $from) use ($req, $tok): array {
    $page = $req('GET', $from, [], $jar);
    $ts = (string) (time() - 30);
    $hp = $ts . '.' . hash_hmac('sha256', $ts, \App\Core\Config::appKey());
    return $req('POST', $path, $form + ['_token' => $tok($page['body']), '_hp_ts' => $hp], $jar);
};
Database::execute('DELETE FROM rate_limits');

// anonymous visitors
$j0 = null;
$tabs = ['/academy/dashboard', '/academy/portal/courses', '/academy/portal/certificates', '/academy/portal/messages', '/academy/account', '/academy/portal/security'];
$redirected = true;
foreach ($tabs as $t) {
    $r = $req('GET', $t, [], $j0);
    $redirected = $redirected && in_array($r['code'], [302, 303], true) && str_contains($r['head'], 'academy/login');
}
check('portal: every tab sends a visitor who is not signed in to the sign-in page', $redirected);
$home = $req('GET', '/', [], $j0);
check('link: every visitor sees "Learner login" in the top bar and no cookie is set for a plain visit', str_contains($home['body'], 'Learner login') && stripos($home['head'], 'set-cookie') === false);

// the learner
$jA = null;
$login = $post('/academy/login', ['email' => 'zz-lpo-a@example.test', 'password' => $pw], $jA, '/academy/login');
check('link: signing in leaves a small marker cookie (no personal data) and goes to the portal', in_array($login['code'], [302, 303], true) && str_contains($login['head'], 'academy/dashboard') && stripos($login['head'], 'laf_portal=1') !== false);
$ov = $req('GET', '/academy/dashboard', [], $jA);
check('portal: the overview greets the learner, shows the numbers, the registration number and the tabs', $ov['code'] === 200 && str_contains($ov['body'], 'Welcome back, Zed') && str_contains($ov['body'], 'Courses completed') && str_contains($ov['body'], 'Reg no. LAF-AC-')
    && str_contains($ov['body'], 'aria-label="My portal"') && str_contains($ov['body'], 'ac-tabs__link is-active') && str_contains($ov['body'], 'Messages'));
check('portal: the overview shows how complete the profile is', str_contains($ov['body'], 'ac-meter') && str_contains($ov['body'], '% complete'));
// the dashboard: big picture button, course picker with Enrol buttons, its own stylesheet
check('dashboard: the portal loads its own stylesheet (assets/css/portal.min.css), so it never depends on main.css being the newest copy', str_contains($ov['body'], 'css/portal.min.css') && is_file(dirname(__DIR__) . '/assets/css/portal.min.css'));
check('dashboard: a big profile picture area with an "Add your photo" button that sends the picture at once', str_contains($ov['body'], 'pt-avatar__btn') && str_contains($ov['body'], 'Add your photo') && str_contains($ov['body'], 'data-autosubmit') && str_contains($ov['body'], 'name="back" value="dashboard"'));
check('dashboard: "Choose a course and enrol" lists the available courses with an Enrol now button and a Details link, plus a search', str_contains($ov['body'], 'Choose a course and enrol') && substr_count($ov['body'], 'Enrol now') >= 3 && preg_match('~action="[^"]*academy/courses/[a-z0-9-]+/enroll"~', $ov['body']) === 1 && str_contains($ov['body'], 'name="q"'));
$searched = $req('GET', '/academy/dashboard?q=excel', [], $jA)['body'];
check('dashboard: the course search narrows the list (Excel found, a typing course not)', str_contains($searched, 'Microsoft Excel') && !str_contains($searched, 'Typing and Keyboard Skills'));
$first = preg_match('~academy/courses/([a-z0-9-]+)/enroll~', $ov['body'], $mc) === 1 ? $mc[1] : '';
$en = $post('/academy/courses/' . $first . '/enroll', [], $jA, '/academy/dashboard');
$ov3 = $req('GET', '/academy/dashboard', [], $jA)['body'];
check('dashboard: Enrol now enrols the learner, who lands in the first lesson, and the course leaves the "choose" list and shows under My courses', in_array($en['code'], [302, 303], true) && str_contains($en['head'], '/lesson/') && !str_contains($ov3, 'courses/' . $first . '/enroll') && str_contains($ov3, 'Continue learning') && preg_match('~class="ac-pstats".*?<strong>1</strong>~s', $ov3) === 1);
// the picture, from the dashboard
$img = sys_get_temp_dir() . '/zz-lpo-' . bin2hex(random_bytes(4)) . '.jpg';
copy(dirname(__DIR__) . '/assets/img/site/photo-02.jpg', $img);
$pg = $req('GET', '/academy/dashboard', [], $jA);
$ch = curl_init($site . '/academy/account/photo');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_COOKIEJAR => $jA, CURLOPT_COOKIEFILE => $jA, CURLOPT_FOLLOWLOCATION => false, CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => ['_token' => $tok($pg['body']), 'back' => 'dashboard', 'photo' => new CURLFile($img, 'image/jpeg', 'me.jpg')]]);
$raw = (string) curl_exec($ch);
$hs = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);
$pRow = Database::one('SELECT photo_path, photo_consent_at FROM academy_learners WHERE id = ?', [$a]);
$ovPic = $req('GET', '/academy/dashboard', [], $jA)['body'];
check('dashboard: choosing a picture saves it (private) and comes back to the dashboard, where the button now says "Change photo"', str_contains(substr($raw, 0, $hs), 'academy/dashboard') && $pRow['photo_path'] !== null && $pRow['photo_consent_at'] === null && str_contains($ovPic, 'Change photo') && str_contains($ovPic, 'academy/account/photo?v='));
@unlink($img);
if ((string) ($pRow['photo_path'] ?? '') !== '') {
    @unlink(STORAGE_PATH . '/private/' . $pRow['photo_path']);
}
$compact = $req('GET', '/academy/portal/courses', [], $jA)['body'];
check('portal: the other tabs load the same portal stylesheet and keep the compact header', str_contains($compact, 'css/portal.min.css') && str_contains($compact, 'ac-portal__head'));

$allOk = true;
foreach (array_slice($tabs, 1) as $t) {
    $r = $req('GET', $t, [], $jA);
    $allOk = $allOk && $r['code'] === 200 && str_contains($r['body'], 'noindex') && str_contains($r['body'], 'aria-label="My portal"');
}
check('portal: My courses, Certificates, Messages, Profile and Security all open inside the same portal frame (private, noindex)', $allOk);
$cp = $req('GET', '/academy/portal/courses', [], $jA)['body'];
check('portal: My courses explains there are no courses yet and offers the catalogue', str_contains($cp, 'Browse the courses'));
check('portal: the Certificates tab explains how certificates are approved', str_contains($req('GET', '/academy/portal/certificates', [], $jA)['body'], 'approved and signed by the Academy administrator'));

// the home page and the Academy page now invite a returning visitor
$jBack = null;
$homeBack = $req('GET', '/', [], $jBack, ['Cookie: laf_portal=1']);
$acadBack = $req('GET', '/academy', [], $jBack, ['Cookie: laf_portal=1']);
check('link: a browser that had an account sees "My learner portal" in the top bar and "Welcome back" on the Academy page', str_contains($homeBack['body'], 'My learner portal') && str_contains($acadBack['body'], 'Welcome back') && str_contains($acadBack['body'], 'Go to my learner portal'));

// profile
$sv = $post('/academy/account/profile', ['name' => 'Zed Lpo a', 'bio' => "I am a <b>student</b> in Gulu.\nI want to learn web design.", 'phone' => '+256700000010', 'country' => 'Uganda', 'whatsapp' => '+256 772 000 010'], $jA, '/academy/account');
$row = Database::one('SELECT bio FROM academy_learners WHERE id = ?', [$a]);
check('profile: the bio is saved as plain text (one line, tags removed)', in_array($sv['code'], [302, 303], true) && $row['bio'] === 'I am a student in Gulu. I want to learn web design.');
$prof = $req('GET', '/academy/account', [], $jA)['body'];
check('profile: the Profile tab shows the bio field with the saved text and the picture block, and no password form (it moved to Security)', str_contains($prof, 'name="bio"') && str_contains($prof, 'student in Gulu') && str_contains($prof, 'name="photo"') && !str_contains($prof, 'name="new_password"'));
$sec = $req('GET', '/academy/portal/security', [], $jA)['body'];
check('profile: the Security tab has the password form, sign out and delete', str_contains($sec, 'name="new_password"') && str_contains($sec, 'Sign out') && str_contains($sec, 'Delete my account'));
$pwChange = $post('/academy/account/password', ['current_password' => 'wrong-password', 'new_password' => 'Another-sentence-91', 'new_password_confirm' => 'Another-sentence-91'], $jA, '/academy/portal/security');
check('profile: a wrong current password goes back to the Security tab', in_array($pwChange['code'], [302, 303], true) && str_contains($pwChange['head'], 'portal/security'));

// messages: the team writes
$jAdmin = null;
$pwA = 'Admin lpo pass 91';
$pwE = 'Editor lpo pass 94';
\App\Models\User::create(['name' => 'ZZ Lpo Admin', 'email' => 'zz-lpo-admin@example.test', 'password_hash' => Auth::hashPassword($pwA), 'role' => 'admin', 'is_active' => 1, 'must_change_password' => 0]);
\App\Models\User::create(['name' => 'ZZ Lpo Editor', 'email' => 'zz-lpo-editor@example.test', 'password_hash' => Auth::hashPassword($pwE), 'role' => 'editor', 'is_active' => 1, 'must_change_password' => 0]);
$adminLogin = static function (string $email, string $p, ?string &$jr) use ($req, $tok): void {
    $pg = $req('GET', '/admin/login', [], $jr);
    $req('POST', '/admin/login', ['_token' => $tok($pg['body']), 'email' => $email, 'password' => $p], $jr);
};
$adminLogin('zz-lpo-admin@example.test', $pwA, $jAdmin);
$jEd = null;
$adminLogin('zz-lpo-editor@example.test', $pwE, $jEd);
$adminPost = static function (string $path, array $form, string $from) use ($req, $tok, &$jAdmin): array {
    $pg = $req('GET', $from, [], $jAdmin);
    return $req('POST', $path, $form + ['_token' => $tok($pg['body'])], $jAdmin);
};
$list = $req('GET', '/admin/academy/learners?q=zz-lpo', [], $jAdmin)['body'];
check('admin: every learner in the list has a Message button, with the number of new messages from them', str_contains($list, 'admin/academy/learners/' . $a . '/messages') && str_contains($list, 'admin/academy/learners/' . $b . '/messages') && str_contains($list, '1 new'));
$show = $req('GET', '/admin/academy/learners/' . $a, [], $jAdmin)['body'];
check('admin: the learner page has a Message button and shows the bio', str_contains($show, 'Message Zed Lpo a') && str_contains($show, 'student in Gulu'));
$side = $req('GET', '/admin/academy/messages', [], $jAdmin);
check('admin: the sidebar has Messages with a yellow number of unread learner messages; the inbox lists the conversations', preg_match('~admin/academy/messages"[^>]*>.*?admin-nav__badge admin-nav__badge--yellow"[^>]*><span class="visually-hidden">New: </span>(\d+)</span>~s', $side['body'], $mm) === 1 && (int) $mm[1] === LearnerMessages::unreadForAdmin()
    && $side['code'] === 200 && str_contains($side['body'], 'Zed Lpo b') && str_contains($side['body'], 'Read and reply'));
check('admin: an editor cannot open the messages (403) and a learner cannot open the admin', $req('GET', '/admin/academy/messages', [], $jEd)['code'] === 403 && in_array($req('GET', '/admin/academy/messages', [], $jA)['code'], [302, 303, 401, 403], true));
$thread = $req('GET', '/admin/academy/learners/' . $b . '/messages', [], $jAdmin);
check('admin: a conversation opens, shows the learner\'s message as new and reading it clears the number', $thread['code'] === 200 && str_contains($thread['body'], 'I cannot open my lesson') && str_contains($thread['body'], 'New messages') && LearnerMessages::unreadFrom($b) === 0);
$send = $adminPost('/admin/academy/learners/' . $a . '/messages', ['body' => "Your class starts on Saturday at 10:00.\nSee you <b>there</b>!"], '/admin/academy/learners/' . $a . '/messages');
check('admin: the team sends a message to a learner (plain text) and it is recorded in the activity log without its text', in_array($send['code'], [302, 303], true) && Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body = ?", [$a, "Your class starts on Saturday at 10:00.\nSee you there!"], 0) === 1
    && Database::value("SELECT COUNT(*) FROM audit_log WHERE action = 'academy.message.send' AND summary LIKE '%zz-lpo-a@%'", [], 0) > 0 && !str_contains((string) Database::value("SELECT details FROM audit_log WHERE action = 'academy.message.send' ORDER BY id DESC LIMIT 1", [], ''), 'Saturday'));
$empty = $adminPost('/admin/academy/learners/' . $a . '/messages', ['body' => ' '], '/admin/academy/learners/' . $a . '/messages');

// the learner receives it
$ov2 = $req('GET', '/academy/dashboard', [], $jA)['body'];
check('portal: the learner sees a yellow notice and a number on the Messages tab', str_contains($ov2, 'ac-tabs__badge') && str_contains($ov2, 'new message') && str_contains($ov2, 'ac-notice--yellow'));
$mp = $req('GET', '/academy/portal/messages', [], $jA)['body'];
check('portal: the Messages tab shows the conversation, the new message from the team and marks it read', str_contains($mp, 'Your class starts on Saturday') && str_contains($mp, 'Academy team') && str_contains($mp, 'New messages') && !str_contains($mp, 'See you <b>') && LearnerMessages::unreadForLearner($a) === 0);
check('portal: a learner never sees another learner\'s messages', !str_contains($mp, 'I cannot open my lesson'));
$after = $req('GET', '/academy/dashboard', [], $jA)['body'];
check('portal: once read the number and the notice are gone', !str_contains($after, 'ac-tabs__badge') && !str_contains($after, 'ac-notice--yellow'));

// the learner writes back
$sentBack = $post('/academy/portal/messages', ['body' => 'Thank you! <script>alert(1)</script>I will be there.'], $jA, '/academy/portal/messages');
$last = LearnerMessages::latest($a);
check('portal: the learner answers the team (plain text) and the team gets a new unread message', in_array($sentBack['code'], [302, 303], true) && $last !== null && $last['sender'] === 'learner' && $last['body'] === 'Thank you! alert(1)I will be there.' && LearnerMessages::unreadFrom($a) === 1);
$badEmpty = $post('/academy/portal/messages', ['body' => '   '], $jA, '/academy/portal/messages');
check('portal: an empty message is refused with a message', in_array($badEmpty['code'], [302, 303], true) && (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'learner'", [$a], 0) === 2);
$dash = $req('GET', '/admin', [], $jAdmin)['body'];
check('admin: the dashboard has a tile for new messages from learners', str_contains($dash, 'New messages from Academy learners'));
Database::execute('DELETE FROM rate_limits');
$flood = 0;
for ($i = 0; $i < 17; $i++) {
    $r = $post('/academy/portal/messages', ['body' => 'Message number ' . $i . ' to the team'], $jA, '/academy/portal/messages');
    $flood = (int) Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'learner'", [$a], 0);
}
check('portal: a learner who floods the team is slowed down (15 messages an hour)', $flood <= 2 + 15 && $flood >= 10);

// ------------------------------------------------------------------------------------------------------------- "Message the Academy team": the public form for new and waiting learners
Database::execute("DELETE FROM academy_inquiries WHERE email LIKE 'zz-lpo-%'");
Database::execute('DELETE FROM rate_limits');
check('contact: the inquiries table exists after the schema migration', LearnerMessages::inquiriesReady());
$jN = null;
$form = $req('GET', '/academy/contact', [], $jN);
check('contact: anybody can open the form (name, e-mail, phone, message) and see the support WhatsApp line', $form['code'] === 200 && str_contains($form['body'], 'name="message"') && str_contains($form['body'], 'name="email"') && str_contains($form['body'], 'https://wa.me/256779795662'));
check('contact: the sign-up, sign-in and "application received" pages link to it', str_contains($req('GET', '/academy/register', [], $jN)['body'], 'academy/contact') && str_contains($req('GET', '/academy/login', [], $jN)['body'], 'academy/contact') && str_contains($req('GET', '/academy/pending', [], $jN)['body'], 'academy/contact'));
$inqBefore = LearnerMessages::unreadForAdmin();
$sent = $post('/academy/contact', ['name' => 'Visitor Lpo', 'email' => 'zz-lpo-visitor@example.test', 'phone' => '+256 701 555 666', 'message' => "I want to join the <b>Academy</b>.\nHow long does approval take?"], $jN, '/academy/contact');
$iq = Database::one("SELECT * FROM academy_inquiries WHERE email = 'zz-lpo-visitor@example.test'");
check('contact: a visitor with no account becomes an inquiry (plain text), and the page says thank you', in_array($sent['code'], [302, 303], true) && str_contains($sent['head'], 'sent=1') && $iq !== null && $iq['message'] === "I want to join the Academy.\nHow long does approval take?" && $iq['phone'] === '+256 701 555 666' && str_contains($req('GET', '/academy/contact?sent=1', [], $jN)['body'], 'your message was sent'));
$sentB = $post('/academy/contact', ['name' => 'Zed Lpo a', 'email' => 'ZZ-LPO-A@example.test', 'phone' => '', 'message' => 'I registered yesterday, is my account approved yet?'], $jN, '/academy/contact');
$inThread = Database::one("SELECT * FROM academy_messages WHERE learner_id = ? AND body LIKE '%registered yesterday%' ORDER BY id DESC LIMIT 1", [$a]);
check('contact: when the e-mail belongs to a learner the message goes into that learner\'s own conversation (the answer shown to the visitor is the same)', in_array($sentB['code'], [302, 303], true) && str_contains($sentB['head'], 'sent=1') && $inThread !== null && $inThread['sender'] === 'learner' && str_starts_with((string) $inThread['body'], '[Sent from the contact form]')
    && (int) Database::value("SELECT COUNT(*) FROM academy_inquiries WHERE email = 'zz-lpo-a@example.test'", [], 0) === 0);
check('contact: both count in the yellow number of the admin menu', LearnerMessages::unreadForAdmin() === $inqBefore + 2 && LearnerMessages::openInquiries() >= 1);
foreach ([['name' => '', 'email' => 'zz-lpo-x@example.test', 'message' => 'A real question here'], ['name' => 'Name', 'email' => 'not-an-email', 'message' => 'A real question here'], ['name' => 'Name', 'email' => 'zz-lpo-x@example.test', 'message' => 'hi']] as $badForm) {
    $post('/academy/contact', $badForm + ['phone' => ''], $jN, '/academy/contact');
}
check('contact: a missing name, a wrong e-mail address and a too short message are refused', (int) Database::value("SELECT COUNT(*) FROM academy_inquiries WHERE email = 'zz-lpo-x@example.test'", [], 0) === 0);
$ib = $req('GET', '/admin/academy/messages', [], $jAdmin)['body'];
check('admin: the Messages page lists the inquiry with E-mail and WhatsApp buttons and "Mark as dealt with"', str_contains($ib, 'Inquiries from visitors') && str_contains($ib, 'Visitor Lpo') && str_contains($ib, 'mailto:zz-lpo-visitor@example.test') && str_contains($ib, 'https://wa.me/256701555666') && str_contains($ib, 'Mark as dealt with'));
$done = $adminPost('/admin/academy/inquiries/' . (int) $iq['id'] . '/done', [], '/admin/academy/messages');
check('admin: an inquiry can be marked as dealt with, and the yellow number goes down', Database::value('SELECT handled_at FROM academy_inquiries WHERE id = ?', [(int) $iq['id']]) !== null && LearnerMessages::unreadForAdmin() === $inqBefore + 1);
Database::execute('DELETE FROM rate_limits');
for ($i = 0; $i < 7; $i++) {
    $post('/academy/contact', ['name' => 'Flood Lpo', 'email' => 'zz-lpo-flood@example.test', 'phone' => '', 'message' => 'Question number ' . $i . ' to the team'], $jN, '/academy/contact');
}
check('contact: sending many messages in an hour is slowed down (5 an hour)', (int) Database::value("SELECT COUNT(*) FROM academy_inquiries WHERE email = 'zz-lpo-flood@example.test'", [], 0) <= 5);
check('contact: a signed-in learner is sent to the Messages tab instead', in_array($req('GET', '/academy/contact', [], $jA)['code'], [302, 303], true) && str_contains($req('GET', '/academy/contact', [], $jA)['head'], 'portal/messages'));
Database::execute("DELETE FROM academy_inquiries WHERE email LIKE 'zz-lpo-%'");

// the number tiles: the portal's and the home page band no longer share a class (they broke each other)
$homeStats = $req('GET', '/academy', [], $jN)['body'];
check('stats: the Academy home band keeps its own markup and the portal numbers have their own class', str_contains($homeStats, 'ac-stats__list') && str_contains($req('GET', '/academy/dashboard', [], $jA)['body'], 'class="ac-pstats"') && !str_contains($req('GET', '/academy/dashboard', [], $jA)['body'], 'class="ac-stats"'));

// deleting a learner removes the conversation
Database::execute('DELETE FROM academy_learners WHERE id = ?', [$b]);
check('messages: deleting a learner deletes the conversation with the team', (int) Database::value('SELECT COUNT(*) FROM academy_messages WHERE learner_id = ?', [$b], 0) === 0);

$clean();
if (!empty($standalone)) {
    echo PHP_EOL . ($fail === 0 ? 'ALL CHECKS PASSED' : "{$fail} check(s) FAILED.") . PHP_EOL;
    exit($fail === 0 ? 0 : 1);
}
