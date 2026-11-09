<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Mailer;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\CertificateService;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerRegistry;
use App\Services\Academy\LearnerPortrait;
use App\Services\Academy\QuizService;

/**
 * Admin > LAF Academy > Learners. Administrators: every learner (search, filter, add one by hand, confirm the address, switch the account off, send a password-reset
 * link, enrol / remove from a course, issue a certificate, delete, download CSV). Facilitators: only the learners enrolled in their own courses, read-only apart from
 * enrolling / removing them in those courses. Learner accounts hold personal data (name, e-mail, phone): exports and deletions are recorded in the activity log.
 */
final class AcademyLearnersController extends AcademyAdminController
{
    /** SQL limiting learners to those a facilitator may see. @return array{0:string,1:list<mixed>} */
    private function scope(): array
    {
        $ids = AcademyAccess::courseIds();
        if ($ids === null) {
            return ['1 = 1', []];
        }
        return ['l.id IN (SELECT learner_id FROM academy_enrollments WHERE course_id IN (' . ($ids === [] ? '0' : implode(',', $ids)) . '))', []];
    }

    private function learner(int $id): array
    {
        $this->authorize('academy.teach');
        [$sc, $sp] = $this->scope();
        return Database::one('SELECT l.* FROM academy_learners l WHERE l.id = ? AND ' . $sc, array_merge([$id], $sp)) ?? throw new HttpException(404, 'Not found');
    }

    /** @return array{0:string,1:list<mixed>,2:array<string,mixed>} */
    private function filters(Request $request): array
    {
        [$sc, $sp] = $this->scope();
        $where = [$sc];
        $params = $sp;
        $q = mb_substr(trim($request->string('q')), 0, 80);
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $reg = LearnerRegistry::ready();
            $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?' . ($reg ? ' OR l.reg_no LIKE ?' : '') . ')';
            array_push($params, $like, $like, $like);
            if ($reg) {
                $params[] = $like;
            }
        }
        $status = $request->string('status');
        // the same three groups as the badges in the list: Active (can sign in), Awaiting approval (e-mail not confirmed, not approved), Switched off
        if ($status === 'active') {
            $where[] = 'l.is_active = 1 AND l.email_verified_at IS NOT NULL';
        } elseif ($status === 'inactive') {
            $where[] = 'l.is_active = 0' . (LearnerRegistry::rejectReady() ? ' AND l.rejected_at IS NULL' : '');
        } elseif ($status === 'rejected' && LearnerRegistry::rejectReady()) {
            $where[] = 'l.rejected_at IS NOT NULL';
        } elseif ($status === 'featured' && \App\Services\Academy\FeaturedLearners::ready()) {
            $where[] = 'l.featured_at IS NOT NULL';
        } elseif ($status === 'notstarted') {
            $where[] = \App\Services\Academy\NotStarted::WHERE;
        } elseif ($status === 'nopicture' && \App\Services\Academy\LearnerPortrait::ready()) {
            $where[] = 'l.is_active = 1 AND l.email_verified_at IS NOT NULL AND l.photo_path IS NULL';
        } elseif ($status === 'unverified') {
            $where[] = 'l.is_active = 1 AND l.email_verified_at IS NULL';
        } else {
            $status = '';
        }
        $login = LearnerRegistry::loginMode($request->string('login'));
        if (($lc = LearnerRegistry::loginSql($login)) !== null) {
            $where[] = $lc;
        }
        $course = $request->int('course');
        if ($course > 0) {
            $where[] = 'l.id IN (SELECT learner_id FROM academy_enrollments WHERE course_id = ?)';
            $params[] = $course;
        }
        return [' WHERE ' . implode(' AND ', $where), $params, ['q' => $q, 'status' => $status, 'course' => $course, 'login' => $login]];
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        [$where, $params, $f] = $this->filters($request);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_learners l' . $where, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $extra = (LearnerRegistry::rejectReady() ? ', l.rejected_at, l.reject_reason' : '') . (\App\Services\Academy\LearnerMessages::ready() ? ", (SELECT COUNT(*) FROM academy_messages mu WHERE mu.learner_id = l.id AND mu.sender = 'learner' AND mu.read_at IS NULL) AS msg_unread" : '')
            . (\App\Services\Academy\AcademyFees::ready() ? ', l.fee_status' . (\App\Services\Academy\LearnerMessages::ready() ? ", (SELECT COUNT(*) FROM academy_messages mw WHERE mw.learner_id = l.id AND mw.sender = 'admin' AND mw.body LIKE '" . \App\Services\Academy\AcademyFees::WELCOME_MARK . "%') AS welcomed" : '') : '') . (LearnerRegistry::ready() ? ', l.reg_no, l.approved_at' : '') . (LearnerAuth::mailColumns() ? ', l.mail_sent_at, l.mail_error' : '') . (LearnerPortrait::ready() ? ', l.photo_path' : '')
            . (CertificateService::approvalReady() ? ', (SELECT COUNT(*) FROM academy_certificates cw WHERE cw.learner_id = l.id AND cw.approved_at IS NULL AND cw.revoked_at IS NULL) AS certs_waiting' : '');
        $rows = Database::all(
            'SELECT l.id, l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.last_login_at, l.created_at' . (\App\Services\Academy\LearnerPortrait::ready() ? ', (l.photo_path IS NULL) AS no_photo' : '') . (\App\Services\Academy\FeaturedLearners::ready() ? ', l.featured_at' : '') . $extra . ', '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, '
            . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL) AS certificates '
            . 'FROM academy_learners l' . $where . ' ORDER BY l.id DESC LIMIT ? OFFSET ?',
            array_merge($params, [$pagination->perPage, $pagination->offset])
        );
        [$sc, $sp] = AcademyAccess::courseScope('c');
        return $this->render('admin/academy/learners/index', [
            'rows' => $rows, 'pagination' => $pagination, 'f' => $f, 'total' => $total, 'manager' => AcademyAccess::manager(),
            'waiting' => AcademyAccess::manager() ? LearnerRegistry::waiting() : 0,
            'courses' => Database::all('SELECT c.id, c.title FROM academy_courses c WHERE ' . $sc . ' ORDER BY c.title', $sp),
        ], ['title' => 'Learners', 'nav' => 'academy_learners', 'breadcrumbs' => $this->crumbs('Learners')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        [$where, $params] = $this->filters($request);
        $rows = Database::all(
            'SELECT ' . (LearnerRegistry::ready() ? 'l.reg_no' : 'NULL AS reg_no') . ', ' . (\App\Services\Academy\VerificationCode::ready() ? 'l.whatsapp' : 'NULL AS whatsapp') . ', l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.created_at, l.last_login_at, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, (SELECT COUNT(*) FROM academy_enrollments e2 WHERE e2.learner_id = l.id AND e2.completed_at IS NOT NULL) AS completed, '
            . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL) AS certificates FROM academy_learners l' . $where . ' ORDER BY l.id',
            $params
        );
        $this->audit('academy.learners.export', 'academy_learner', null, 'Downloaded the learner list', ['rows' => count($rows)]);
        return CsvExport::response('academy-learners-' . date('Y-m-d') . '.csv', ['Reg no', 'Name', 'Email', 'Country', 'Country code', 'Phone', 'WhatsApp', 'Email confirmed / approved', 'Active', 'Joined', 'Last sign-in', 'Courses', 'Completed', 'Certificates'],
            array_map(static fn(array $r): array => [$r['reg_no'], $r['name'], $r['email'], $r['country'], \App\Services\Academy\LearnerPhones::dialCode($r['country']), \App\Services\Academy\LearnerPhones::display($r['phone'], $r['country']), $r['whatsapp'] !== null && $r['whatsapp'] !== '' ? '+' . $r['whatsapp'] : '', $r['email_verified_at'] ? 'yes' : 'no', $r['is_active'] ? 'yes' : 'no', $r['created_at'], $r['last_login_at'], $r['courses'], $r['completed'], $r['certificates']], $rows));
    }

    public function show(Request $request, int $id): Response
    {
        $l = $this->learner($id);
        $ids = AcademyAccess::courseIds();
        $enr = Database::all('SELECT e.*, c.title, c.slug, c.facilitator_id FROM academy_enrollments e JOIN academy_courses c ON c.id = e.course_id WHERE e.learner_id = ? ORDER BY e.enrolled_at DESC', [$id]);
        $enr = array_values(array_filter($enr, static fn(array $e): bool => $ids === null || in_array((int) $e['course_id'], $ids, true)));
        foreach ($enr as &$e) {
            $tree = CourseService::tree((int) $e['course_id']);
            $e['progress'] = CourseService::progress($id, (int) $e['course_id'], $tree);
            $e['certificate'] = CertificateService::activeFor($id, (int) $e['course_id']);
        }
        unset($e);
        $cond = $ids === null ? '' : ' AND a.course_id IN (' . ($ids === [] ? '0' : implode(',', $ids)) . ')';
        $attempts = Database::all(
            'SELECT a.*, q.title AS quiz_title, q.module_id, c.title AS course_title FROM academy_attempts a JOIN academy_quizzes q ON q.id = a.quiz_id JOIN academy_courses c ON c.id = a.course_id WHERE a.learner_id = ? AND a.submitted_at IS NOT NULL' . $cond . ' ORDER BY a.id DESC LIMIT 30',
            [$id]
        );
        $certs = AcademyAccess::manager() ? CertificateService::forLearner($id, false) : [];
        [$sc, $sp] = AcademyAccess::courseScope('c');
        $enrolledIds = array_map(static fn(array $e): int => (int) $e['course_id'], Database::all('SELECT course_id FROM academy_enrollments WHERE learner_id = ?', [$id]));
        $available = array_values(array_filter(Database::all("SELECT c.id, c.title FROM academy_courses c WHERE c.status <> 'archived' AND " . $sc . ' ORDER BY c.title', $sp), static fn(array $c): bool => !in_array((int) $c['id'], $enrolledIds, true)));
        return $this->render('admin/academy/learners/show', [
            'l' => $l, 'enrolments' => $enr, 'attempts' => $attempts, 'certs' => $certs, 'available' => $available, 'manager' => AcademyAccess::manager(),
        ], ['title' => (string) $l['name'], 'nav' => 'academy_learners', 'breadcrumbs' => $this->crumbs('Learners', [(string) $l['name'], null])]);
    }

    // ------------------------------------------------------------------------------------------------------------- add / change (administrators)

    public function create(Request $request): Response
    {
        $this->authorize('academy.manage');
        return $this->render('admin/academy/learners/form', [], ['title' => 'Add a learner', 'nav' => 'academy_learners', 'breadcrumbs' => $this->crumbs('Learners', ['Add a learner', null])]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.manage');
        $valid = $this->validate(['name' => 'required|max:120', 'email' => 'required|email|max:190', 'phone' => 'nullable|max:40', 'country' => 'nullable|max:80'], [], ['name' => 'Full name', 'email' => 'E-mail address'], 'admin/academy/learners/create');
        $email = LearnerAuth::normaliseEmail((string) $valid['email']);
        if (Database::value('SELECT 1 FROM academy_learners WHERE email = ?', [$email]) !== null) {
            $this->fail(['email' => 'There is already a learner with this e-mail address.'], 'admin/academy/learners/create');
        }
        $id = Database::insert('academy_learners', [
            'name' => trim(strip_tags((string) $valid['name'])), 'email' => $email, 'password_hash' => Auth::hashPassword(Auth::generatePassword(24)),
            'phone' => trim((string) $valid['phone']) ?: null, 'country' => trim(strip_tags((string) $valid['country'])) ?: null, 'email_verified_at' => date('Y-m-d H:i:s'), 'is_active' => 1,
        ]);
        LearnerRegistry::assign($id);
        $this->audit('academy.learner.create', 'academy_learner', $id, 'Added learner ' . $email);
        $sent = $request->bool('send_link') ? $this->sendResetLink($id) : false;
        Flash::success('The learner account was created.' . ($request->bool('send_link') ? ($sent ? ' An e-mail with a link to choose a password was sent.' : ' The e-mail could not be sent: use "Send a password link" on the next page when e-mail works.') : ''));
        return $this->redirect('admin/academy/learners/' . $id);
    }

    private function sendResetLink(int $id): bool
    {
        $l = Database::one('SELECT * FROM academy_learners WHERE id = ?', [$id]);
        if ($l === null) {
            return false;
        }
        $token = bin2hex(random_bytes(32));
        Database::update('academy_learners', ['token_hash' => hash('sha256', $token), 'token_kind' => 'reset', 'token_expires_at' => date('Y-m-d H:i:s', time() + 7 * 86400)], 'id = ?', [$id]);
        $ok = \App\Services\Academy\AcademyMail::passwordReset($l, abs_url('academy/reset/' . $token));
        LearnerAuth::recordMail($id, $ok);
        return $ok;
    }

    public function resetLink(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $ok = $this->sendResetLink($id);
        $this->audit('academy.learner.reset_link', 'academy_learner', $id, 'Sent a password link to ' . $l['email']);
        $ok ? Flash::success('A link to choose a new password was e-mailed to ' . $l['email'] . '. It works for 7 days.') : Flash::error('The e-mail could not be sent' . $this->why() . '. Check the e-mail settings (Settings > E-mail).');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    /** " (reason)" for a flash message, from the last failed e-mail. */
    private function why(): string
    {
        $e = \App\Services\Academy\AcademyMail::lastError();
        return $e !== null && $e !== '' ? ' (' . $e . ')' : '';
    }

    /** Button on the learner page: e-mail a fresh confirmation link (valid 7 days). */
    public function sendConfirmation(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $res = LearnerAuth::sendConfirmation($id);
        $this->audit('academy.learner.send_confirmation', 'academy_learner', $id, ($res['ok'] ? 'Sent' : 'Could not send') . ' a confirmation e-mail to ' . $l['email']);
        $res['ok']
            ? Flash::success('A confirmation e-mail was sent to ' . $l['email'] . '. The link works for 7 days. If it does not arrive, ask the learner to look in the spam folder, or use "Mark e-mail as confirmed".')
            : Flash::error('The confirmation e-mail could not be sent: ' . (string) $res['error']);
        return $this->redirect('admin/academy/learners/' . $id);
    }

    /** Button on the list: send the confirmation e-mail to the next 25 learners who still have to confirm (those never sent one first). */
    public function sendConfirmations(Request $request): Response
    {
        $this->authorize('academy.manage');
        $order = LearnerAuth::mailColumns() ? 'ORDER BY (mail_sent_at IS NULL) DESC, id ASC' : 'ORDER BY id ASC';
        $skip = LearnerAuth::mailColumns() ? ' AND (mail_sent_at IS NULL OR mail_sent_at < (NOW() - INTERVAL 1 DAY))' : '';
        $rows = Database::all('SELECT id FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NULL' . $skip . ' ' . $order . ' LIMIT 25');
        $sent = 0;
        $failed = 0;
        $row = 0;
        $firstError = null;
        foreach ($rows as $r) {
            $res = LearnerAuth::sendConfirmation((int) $r['id']);
            if ($res['ok']) {
                $sent++;
                $row = 0;
            } else {
                $failed++;
                $firstError ??= (string) $res['error'];
                if (++$row >= 3) {
                    break;                       // the mail server is refusing everything: stop instead of trying all of them
                }
            }
        }
        Mailer::closeConnection();
        $left = (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NULL', [], 0);
        $this->audit('academy.learner.send_confirmations', 'academy_learner', null, 'Sent confirmation e-mails in bulk', ['sent' => $sent, 'failed' => $failed]);
        if ($rows === []) {
            Flash::info('Nobody needs a confirmation e-mail right now (everyone was e-mailed in the last day, or has confirmed).');
        } elseif ($failed === 0) {
            Flash::success($sent . ' confirmation e-mail' . ($sent === 1 ? '' : 's') . ' sent. ' . $left . ' learner' . ($left === 1 ? ' is' : 's are') . ' still not confirmed: press the button again for the next batch.');
        } else {
            Flash::error($sent . ' sent, ' . $failed . ' could not be sent' . ($firstError ? ' (' . $firstError . ')' : '') . '. Check Settings > E-mail, wait a little (mail hosts limit how many e-mails an hour) and try again.');
        }
        return $this->redirect('admin/academy/learners?status=unverified');
    }

    /** The button on a learner: approve the certificate(s) of this learner that are waiting (optionally e-mailing them). */
    public function approveCertificates(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $n = CertificateService::approveForLearner($id, Auth::id(), $request->bool('email'));
        $this->audit('academy.certificate.approve_learner', 'academy_learner', $id, 'Approved ' . $n . ' certificate(s) of ' . $l['email'] . ($request->bool('email') ? ' and e-mailed them' : ''));
        if ($n === 0) {
            Flash::info('This learner has no certificate waiting for approval.');
        } else {
            Flash::success($n . ' certificate' . ($n === 1 ? ' was' : 's were') . ' approved' . ($request->bool('email') ? ' and e-mailed to the learner.' : ': the learner can download ' . ($n === 1 ? 'it' : 'them') . ' now.'));
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** Send a fresh confirmation code to the learner's WhatsApp number (saved at sign-up or typed by the learner). */
    public function sendWhatsApp(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $number = (string) ($l['whatsapp'] ?? '');
        if (!\App\Services\Academy\WhatsAppSender::enabled()) {
            Flash::error('WhatsApp is not set up yet. Add the WhatsApp details to the .env file first (docs/ACADEMY.md).');
        } elseif ($number === '') {
            Flash::error('This learner has not given a WhatsApp number.');
        } else {
            $res = \App\Services\Academy\VerificationCode::deliver($id, $number);
            $this->audit('academy.learner.send_whatsapp', 'academy_learner', $id, ($res['ok'] ? 'Sent' : 'Could not send') . ' a WhatsApp confirmation code to ' . $l['email']);
            $res['ok'] ? Flash::success('A confirmation code was sent to the learner on WhatsApp. They type it on the "Check your e-mail" page of the Academy.') : Flash::error('The WhatsApp message could not be sent: ' . (string) $res['error']);
        }
        return $this->redirect('admin/academy/learners/' . $id);
    }

    /** For when e-mail cannot be delivered at all: approve every waiting learner so they can sign in without waiting for an e-mail. */
    public function confirmAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = LearnerRegistry::approveAll(Auth::id());
        $unpaid = \App\Services\Academy\AcademyFees::ready() ? (int) Database::value("SELECT COUNT(*) FROM academy_learners WHERE is_active = 1 AND email_verified_at IS NULL AND fee_status = 'due'", [], 0) : 0;
        $this->audit('academy.learner.approve_all', 'academy_learner', null, 'Approved every learner who was waiting', ['count' => $n, 'unpaid_left' => $unpaid]);
        Flash::success($n . ' learner' . ($n === 1 ? ' was' : 's were') . ' approved. They can sign in now.' . ($unpaid > 0 ? ' ' . $unpaid . ' more ' . ($unpaid === 1 ? 'is' : 'are') . ' waiting for their sign-up fee and cannot be approved until it is paid.' : ''));
        return $this->redirect('admin/academy/learners');
    }

    /** POST learners/{id}/acknowledge: thank the learner for paying, welcome them to the class and tell them they can ask for support (portal message, and e-mail when ticked). */
    public function acknowledge(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::acknowledge($id, Auth::id(), $request->bool('email'))) {
            $this->audit('academy.learner.payment_acknowledged', 'academy_learner', $id, 'Acknowledged the payment of ' . $l['email'] . ' and welcomed them to the class');
            Flash::success('The thank-you and welcome message was sent to ' . $l['name'] . ($request->bool('email') ? ' (also by e-mail).' : '.'));
        } else {
            Flash::info($l['name'] . ' has not paid the sign-up fee (or the message could not be sent), so nothing was sent.');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** POST learners-acknowledge-all: the same message to everyone who paid and has not been welcomed yet. */
    public function acknowledgeAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = \App\Services\Academy\AcademyFees::acknowledgeAll(Auth::id(), $request->bool('email'));
        $this->audit('academy.learner.payment_acknowledged_all', 'academy_learner', null, 'Sent the thank-you and welcome message to every learner who paid and was not welcomed yet', ['count' => $n]);
        Flash::success($n === 0 ? 'Everybody who paid has already been welcomed.' : 'The thank-you and welcome message was sent to ' . $n . ' learner' . ($n === 1 ? '' : 's') . '.');
        return $this->redirect('admin/academy/learners');
    }

    /** GET learners-welcome-whatsapp: one WhatsApp button per learner who paid and has not been welcomed (opens WhatsApp with the message typed). */
    public function welcomeWhatsApp(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = [];
        foreach (\App\Services\Academy\AcademyFees::paidLearners(true) as $row) {
            $row['wa'] = \App\Services\Academy\AcademyFees::welcomeWhatsAppUrl($row);
            $rows[] = $row;
        }
        return $this->render('admin/academy/learners/welcome-whatsapp', ['rows' => $rows]);
    }
    /** POST learners/{id}/approve-waive: the administrator approves the account and waives the unpaid sign-up fee in one step. */
    public function approveWaive(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::signupDue($l)) {
            \App\Services\Academy\AcademyFees::waiveSignup($id);
        }
        if (LearnerRegistry::approve($id, Auth::id())) {
            $this->audit('academy.learner.approve_waive', 'academy_learner', $id, 'Approved the account of ' . $l['email'] . ' and waived the sign-up fee');
            \App\Services\Academy\AcademyMail::approved($l);        // best effort: the approval does not depend on it
            Flash::success($l['name'] . ' is approved (sign-up fee waived) and can sign in now.');
        } else {
            Flash::info('This account is already active.');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** POST learners-approve-all-waive: approve everyone who is waiting and waive the sign-up fee of those who have not paid. */
    public function approveAllWaive(Request $request): Response
    {
        $this->authorize('academy.manage');
        $waived = \App\Services\Academy\AcademyFees::ready()
            ? Database::execute("UPDATE academy_learners SET fee_status = 'waived' WHERE is_active = 1 AND email_verified_at IS NULL AND fee_status = 'due'")
            : 0;
        $n = LearnerRegistry::approveAll(Auth::id());
        $this->audit('academy.learner.approve_all_waive', 'academy_learner', null, 'Approved every learner who was waiting and waived their unpaid sign-up fee', ['approved' => $n, 'waived' => $waived]);
        Flash::success($n . ' learner' . ($n === 1 ? ' was' : 's were') . ' approved' . ($waived > 0 ? ' and the sign-up fee of ' . $waived . ' ' . ($waived === 1 ? 'was' : 'were') . ' waived' : '') . '. They can sign in now.');
        return $this->redirect('admin/academy/learners');
    }
    /** The "Reject" button: the application is declined, the learner cannot sign in and is told why (and can appeal through the contact form). */
    public function reject(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $reason = $request->string('reason');
        if (LearnerRegistry::reject($id, Auth::id(), $reason)) {
            $this->audit('academy.learner.reject', 'academy_learner', $id, 'Rejected the application of ' . $l['email'] . ((string) ($l['reg_no'] ?? '') !== '' ? ' (' . $l['reg_no'] . ')' : ''), ['reason' => mb_substr(trim($reason), 0, 300)]);
            $mailed = $request->bool('email') ? \App\Services\Academy\AcademyMail::rejected($l, trim($reason)) : null;
            Flash::success('The application of ' . $l['name'] . ' was rejected. They cannot sign in; if they try, they are told' . (trim($reason) !== '' ? ' the reason you gave' : '') . ' and can message the team.' . ($mailed === null ? '' : ($mailed ? ' They were also e-mailed.' : ' The e-mail could not be sent.')));
        } else {
            Flash::info('This application cannot be rejected (it is already approved or rejected).');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** The "Approve" button: the learner can sign in now, whether or not the e-mail ever arrives. */
    public function approve(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::signupDue($l)) {
            Flash::error($l['name'] . ' has not paid the sign-up fee yet, so the account cannot be approved. Mark the fee as paid after you have checked the money, or waive it.');
            return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
        }
        if (LearnerRegistry::approve($id, Auth::id())) {
            $this->audit('academy.learner.approve', 'academy_learner', $id, 'Approved the account of ' . $l['email'] . ((string) ($l['reg_no'] ?? '') !== '' ? ' (' . $l['reg_no'] . ')' : ''));
            \App\Services\Academy\AcademyMail::approved($l);        // best effort: the approval does not depend on it
            Flash::success($l['name'] . ' is approved and can sign in now.');
        } else {
            Flash::info('This account is already active.');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : ($request->string('back') === 'thread' ? 'admin/academy/learners/' . $id . '/messages' : 'admin/academy/learners/' . $id));
    }

    /** The learner's portrait (administrators and facilitators who may see this learner). */
    public function photo(Request $request, int $id): Response
    {
        $this->learner($id);
        return LearnerPortrait::download($id) ?? throw new HttpException(404, 'Not found');
    }

    /**
     * POST admin/academy/learners-phone-codes: give the numbers of older accounts the country code of their own country (LearnerPhones::repair).
     * mode=preview only counts and shows examples; mode=apply saves. Safe to repeat.
     */
    public function phoneCodes(Request $request): Response
    {
        $this->authorize('academy.manage');
        $apply = $request->string('mode') === 'apply';
        $r = \App\Services\Academy\LearnerPhones::repair($apply);
        $msg = ($apply ? 'Done. ' : 'Preview (nothing was changed yet). ') . $r['checked'] . ' accounts checked: ' . $r['phones'] . ' phone number(s) ' . ($apply ? 'updated' : 'would be updated')
            . ' and ' . $r['whatsapp'] . ' WhatsApp number(s) ' . ($apply ? 'corrected' : 'would be corrected') . '.';
        if ($r['examples'] !== []) {
            $msg .= ' Examples: ' . implode('; ', array_map(static fn(array $e): string => $e[0] . ' becomes ' . $e[1], array_slice($r['examples'], 0, 4))) . '.';
        }
        if ($r['unknown_country'] !== []) {
            $msg .= ' Countries we do not recognise (left as they are): ' . implode(', ', array_map(static fn(string $k, int $n): string => $k . ' (' . $n . ')', array_keys($r['unknown_country']), $r['unknown_country'])) . '.';
        }
        if ($apply) {
            $this->audit('academy.learner.phone_codes', 'academy_learner', null, 'Added country codes to older numbers: ' . $r['phones'] . ' phones, ' . $r['whatsapp'] . ' WhatsApp numbers');
        }
        Flash::success($msg);
        return $this->redirect('admin/academy/learners');
    }

    /** GET admin/academy/learners/{id}/fee-proof: the receipt the applicant attached to their payment report (private). */
    public function feeProof(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $p = (string) ($l['fee_proof_path'] ?? '');
        if ($p === '' || !\App\Core\Uploader::privateExists($p)) {
            throw new HttpException(404, 'Not found');
        }
        return \App\Core\Uploader::download($p, 'sign-up-payment-' . $id . '.' . pathinfo($p, PATHINFO_EXTENSION), true)->header('Cache-Control', 'private, no-store')->header('X-Robots-Tag', 'noindex');
    }

    /** POST admin/academy/learners/{id}/mark-fee-paid: the money was checked (Daraza dashboard, cash...): the sign-up fee counts as paid. */
    public function markFeePaid(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::markPaidByAdmin('signup', $id)) {
            $this->audit('academy.learner.fee_marked_paid', 'academy_learner', $id, 'Marked the sign-up fee of ' . $l['email'] . ' as paid');
            Flash::success('The sign-up fee is marked as paid. You can approve the account now.');
        } else {
            Flash::info('This account has no unpaid sign-up fee.');
        }
        return $this->redirect($request->string('back') === 'payments' ? 'admin/academy/payments' : 'admin/academy/learners/' . $id);
    }

    /** POST admin/academy/learners/{id}/remind-fee: the payment reminder goes into the learner's messages (and by e-mail when ticked). WhatsApp is a link: see the learner page. */
    public function remindFee(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::remind($id, Auth::id(), $request->bool('email'))) {
            $this->audit('academy.learner.fee_reminder', 'academy_learner', $id, 'Sent a sign-up fee reminder to ' . $l['email']);
            Flash::success('The payment reminder was sent to ' . $l['name'] . '. They see it in their messages' . ($request->bool('email') ? ' and by e-mail.' : '.'));
        } else {
            Flash::info('Nothing was sent: this account has no unpaid sign-up fee.');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** POST admin/academy/learners-remind-not-started: portal message (and e-mail) to every approved learner who has not started a course. */
    public function remindNotStarted(Request $request): Response
    {
        $this->authorize('academy.manage');
        $text = trim((string) $request->post('message', ''));
        if (mb_strlen($text) < 10) {
            Flash::error('Write the reminder first (at least a short sentence).');
            return $this->redirect('admin/academy/learners');
        }
        $n = \App\Services\Academy\NotStarted::remind(Auth::id(), mb_substr($text, 0, 1500), $request->bool('email'));
        $this->audit('academy.learner.remind_not_started', 'academy_learner', null, 'Reminded the approved learners who have not started a course', ['count' => $n, 'email' => $request->bool('email')]);
        Flash::success($n === 0 ? 'Every approved learner has started a course, so nobody needed a reminder.' : 'The reminder was sent to ' . $n . ' learner' . ($n === 1 ? '' : 's') . ' who have not started a course.');
        return $this->redirect('admin/academy/learners');
    }

    /** POST admin/academy/learners-not-started-whatsapp-all: the approved WhatsApp template to everyone who has not started (needs the WhatsApp API); otherwise the guided list opens. */
    public function notStartedWhatsAppAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        if (!\App\Services\Academy\NotStarted::whatsAppApiReady()) {
            Flash::info('This site is not connected to the WhatsApp Business API with a template for this reminder, so it cannot send by itself. Use "Start sending to all" below: it opens each chat with the message typed.');
            return $this->redirect('admin/academy/learners-not-started-whatsapp');
        }
        $r = \App\Services\Academy\NotStarted::whatsAppAll();
        $this->audit('academy.learner.not_started_whatsapp_all', 'academy_learner', null, 'Sent the WhatsApp reminder to the learners who have not started a course', ['sent' => $r['sent'], 'failed' => $r['failed']]);
        $msg = $r['sent'] . ' WhatsApp reminder' . ($r['sent'] === 1 ? '' : 's') . ' sent.' . ($r['nonumber'] > 0 ? ' ' . $r['nonumber'] . ' learner' . ($r['nonumber'] === 1 ? ' has' : 's have') . ' no usable number.' : '') . ($r['failed'] > 0 ? ' ' . $r['failed'] . ' could not be sent' . ($r['error'] ? ' (' . $r['error'] . ')' : '') . '.' : '');
        $r['failed'] > 0 && $r['sent'] === 0 ? Flash::error($msg) : Flash::success($msg);
        return $this->redirect('admin/academy/learners');
    }

    /** GET admin/academy/learners-not-started-whatsapp: one WhatsApp button per learner who has not started (opens WhatsApp with the reminder typed), and "start sending to all". */
    public function notStartedWhatsApp(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = [];
        foreach (\App\Services\Academy\NotStarted::learners() as $row) {
            $row['wa'] = \App\Services\Academy\NotStarted::whatsAppUrl($row);
            $rows[] = $row;
        }
        return $this->render('admin/academy/learners/not-started-whatsapp', ['rows' => $rows], ['title' => 'WhatsApp reminders', 'nav' => 'academy_learners', 'breadcrumbs' => $this->crumbs('Learners', ['WhatsApp reminders', null])]);
    }

    /** POST admin/academy/learners-remind-no-picture: remind every approved learner without a picture and start the countdown (days). */
    public function remindNoPicture(Request $request): Response
    {
        $this->authorize('academy.manage');
        $text = trim((string) $request->post('message', ''));
        if (mb_strlen($text) < 10) {
            Flash::error('Write the reminder first (at least a short sentence).');
            return $this->redirect('admin/academy/learners');
        }
        $r = \App\Services\Academy\LearnerPortrait::remindWithoutPicture(Auth::id(), mb_substr($text, 0, 1500), $request->bool('email'), $request->int('days') ?: 7);
        $this->audit('academy.learner.remind_no_picture', 'academy_learner', null, 'Reminded the learners without a picture, deadline ' . $r['deadline'], ['count' => $r['sent']]);
        Flash::success($r['sent'] === 0 ? 'Every approved learner has a picture, so nobody needed a reminder.' : 'The reminder was sent to ' . $r['sent'] . ' learner' . ($r['sent'] === 1 ? '' : 's') . '. They have until ' . date('j M Y', strtotime($r['deadline'])) . ' to add a picture.');
        return $this->redirect('admin/academy/learners');
    }

    /** POST admin/academy/learners-switch-off-no-picture: after the deadline, switch off the accounts that still have no picture. */
    public function switchOffNoPicture(Request $request): Response
    {
        $this->authorize('academy.manage');
        if (!\App\Services\Academy\LearnerPortrait::deadlinePassed()) {
            Flash::error('The deadline has not passed yet. Send the reminder first and wait until the deadline is over.');
            return $this->redirect('admin/academy/learners');
        }
        $n = \App\Services\Academy\LearnerPortrait::switchOffWithoutPicture(Auth::id(), $request->bool('email'));
        $this->audit('academy.learner.switch_off_no_picture', 'academy_learner', null, 'Switched off the accounts that still had no picture after the deadline', ['count' => $n]);
        Flash::success($n === 0 ? 'Nobody is left without a picture.' : $n . ' account' . ($n === 1 ? ' was' : 's were') . ' switched off. You can switch any of them on again from the learner\'s page.');
        return $this->redirect('admin/academy/learners');
    }

    /** POST admin/academy/learners-remind-approved: one reminder message to every approved learner (portal message, plus e-mail when ticked). */
    public function remindApproved(Request $request): Response
    {
        $this->authorize('academy.manage');
        $text = trim((string) $request->post('message', ''));
        if (mb_strlen($text) < 10) {
            Flash::error('Write the reminder first (at least a short sentence).');
            return $this->redirect('admin/academy/learners');
        }
        $text = mb_substr($text, 0, 1500);
        $login = LearnerRegistry::loginMode($request->string('login'));
        $n = \App\Services\Academy\LearnerMessages::remindApproved(Auth::id(), $text, $request->bool('email'), $login);
        $this->audit('academy.learner.remind_approved', 'academy_learner', null, 'Sent a reminder to approved learners (' . strtolower(LearnerRegistry::LOGIN_FILTER[$login]) . ')', ['count' => $n, 'email' => $request->bool('email'), 'sign_in' => $login]);
        Flash::success($n === 0 ? 'There is no approved learner to remind.' : 'The reminder was sent to ' . $n . ' approved learner' . ($n === 1 ? '' : 's') . '.');
        return $this->redirect('admin/academy/learners');
    }

    /**
     * POST admin/academy/learners-signin-guides: e-mail every approved learner the numbered "how to sign in" guide, a batch at a time (setting academy_bulk_mail_per_run,
     * default 100, so a mail account with a daily limit is never emptied in one go). Press again to continue with those still waiting.
     */
    public function signInGuides(Request $request): Response
    {
        $this->authorize('academy.manage');
        $per = max(10, min(400, (int) setting('academy_bulk_mail_per_run', '100')));
        $login = LearnerRegistry::loginMode($request->string('login'));
        $r = \App\Services\Academy\LearnerMessages::sendSignInGuides(Auth::id(), $login, $request->bool('again'), $per);
        $this->audit('academy.learner.signin_guides', 'academy_learner', null, 'Sent the sign-in guide to approved learners (' . strtolower(LearnerRegistry::LOGIN_FILTER[$login]) . ')', ['sent' => $r['sent'], 'failed' => $r['failed'], 'remaining' => $r['remaining']]);
        if ($r['total'] === 0) {
            Flash::info('Every approved learner in this group has already been sent the sign-in guide. Tick "send again" to send it to them once more.');
        } elseif ($r['stopped'] !== null && $r['stopped'] !== 'time') {
            Flash::error('Sent to ' . $r['sent'] . ' learner' . ($r['sent'] === 1 ? '' : 's') . ', then the mail server stopped accepting messages: ' . $r['stopped'] . ' (a free Gmail account allows about 500 a day). ' . $r['remaining'] . ' still waiting: press the button again later.');
        } elseif ($r['remaining'] > 0) {
            Flash::success('Sent to ' . $r['sent'] . ' learner' . ($r['sent'] === 1 ? '' : 's') . '. ' . $r['remaining'] . ' still waiting: press the button again to send the next batch.' . ($r['failed'] > 0 ? ' ' . $r['failed'] . ' could not be e-mailed and will be tried again.' : ''));
        } else {
            Flash::success('The sign-in guide was sent to ' . $r['sent'] . ' approved learner' . ($r['sent'] === 1 ? '' : 's') . '. Everyone in this group has it now.' . ($r['failed'] > 0 ? ' ' . $r['failed'] . ' could not be e-mailed and will be tried again next time.' : ''));
        }
        return $this->redirect('admin/academy/learners');
    }

    /** POST admin/academy/learners-remind-fee-all: the same reminder to every learner whose sign-up fee is still unpaid. */
    public function remindFeeAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $sent = 0;
        foreach (\App\Services\Academy\AcademyFees::unpaidLearners() as $row) {
            if (\App\Services\Academy\AcademyFees::remind((int) $row['id'], Auth::id(), $request->bool('email'))) {
                $sent++;
            }
        }
        $this->audit('academy.learner.fee_reminder_all', 'academy_learner', null, 'Sent the sign-up fee reminder to every learner who has not paid', ['count' => $sent]);
        Flash::success($sent === 0 ? 'Nobody has an unpaid sign-up fee, so nothing was sent.' : 'The payment reminder was sent to ' . $sent . ' learner' . ($sent === 1 ? '' : 's') . '.');
        return $this->redirect('admin/academy/learners');
    }

    /** POST admin/academy/learners-remind-fee-whatsapp-all: one click sends the WhatsApp reminder to everyone who has not paid. Without the WhatsApp Business API it opens the guided list instead. */
    public function remindFeeWhatsAppAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        if (!\App\Services\Academy\WhatsAppSender::reminderEnabled()) {
            Flash::info('This site is not connected to the WhatsApp Business API yet, so WhatsApp cannot send by itself. Use "Start sending to all" below: it opens each chat with the message typed, one after the other.');
            return $this->redirect('admin/academy/learners-fee-reminders');
        }
        $r = \App\Services\Academy\AcademyFees::remindAllWhatsApp();
        $this->audit('academy.learner.fee_whatsapp_all', 'academy_learner', null, 'Sent the sign-up fee reminder on WhatsApp to every learner who has not paid', ['sent' => $r['sent'], 'failed' => $r['failed']]);
        $msg = $r['sent'] . ' WhatsApp reminder' . ($r['sent'] === 1 ? '' : 's') . ' sent.' . ($r['nonumber'] > 0 ? ' ' . $r['nonumber'] . ' learner' . ($r['nonumber'] === 1 ? ' has' : 's have') . ' no usable number.' : '') . ($r['failed'] > 0 ? ' ' . $r['failed'] . ' could not be sent' . ($r['error'] ? ' (' . $r['error'] . ')' : '') . '.' : '');
        $r['failed'] > 0 && $r['sent'] === 0 ? Flash::error($msg) : Flash::success($msg);
        return $this->redirect('admin/academy/learners');
    }

    /** GET admin/academy/learners-fee-reminders: one WhatsApp button per learner who has not paid (opens WhatsApp with the reminder typed). */
    public function feeReminders(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = [];
        foreach (\App\Services\Academy\AcademyFees::unpaidLearners() as $row) {
            $row['wa'] = \App\Services\Academy\AcademyFees::reminderWhatsAppUrl($row);
            $rows[] = $row;
        }
        return $this->render('admin/academy/learners/fee-reminders', ['rows' => $rows]);
    }

    /** POST admin/academy/learners/{id}/waive-fee: the sign-up fee is not asked of this learner (they can then be approved and sign in). */
    public function waiveFee(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::waiveSignup($id)) {
            $this->audit('academy.learner.fee_waived', 'academy_learner', $id, 'Waived the sign-up fee of ' . $l['email']);
            Flash::success('The sign-up fee was waived. You can approve the account now.');
        } else {
            Flash::info('This account has no unpaid sign-up fee.');
        }
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function photoRemove(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        LearnerPortrait::remove($id);
        $this->audit('academy.learner.photo_remove', 'academy_learner', $id, 'Removed the portrait of ' . $l['email']);
        Flash::success('The portrait was removed.');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function verify(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (\App\Services\Academy\AcademyFees::signupDue($l)) {
            Flash::error('The sign-up fee is not paid yet, so the account cannot be confirmed. Mark the fee as paid (or waive it) first.');
            return $this->redirect('admin/academy/learners/' . $id);
        }
        LearnerRegistry::approve($id, Auth::id());
        $this->audit('academy.learner.verify', 'academy_learner', $id, 'Confirmed the e-mail address of ' . $l['email']);
        Flash::success('The e-mail address was marked as confirmed.');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    /** POST admin/academy/learners/{id}/feature: show this learner on the public Academy page (needs the "learner agreed" tick). */
    public function feature(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (!\App\Services\Academy\FeaturedLearners::ready()) {
            Flash::error('Featured students need a database update: in phpMyAdmin import database/updates/2026-11-academy-featured-learners.sql, then try again.');
        } elseif (!$request->bool('agreed')) {
            Flash::error('Tick the box to confirm that ' . $l['name'] . ' agreed to be featured.');
        } elseif ((int) $l['is_active'] !== 1 || $l['email_verified_at'] === null) {
            Flash::error('Only an approved, active learner can be featured.');
        } elseif (\App\Services\Academy\FeaturedLearners::feature($id, $request->string('note'), Auth::id())) {
            $this->audit('academy.learner.feature', 'academy_learner', $id, 'Featured ' . $l['name'] . ' on the public Academy page');
            Flash::success($l['name'] . ' is featured on the Academy page.');
        } else {
            Flash::error('The learner could not be featured.');
        }
        return $this->redirect('admin/academy/learners/' . $id);
    }

    /** POST admin/academy/learners/{id}/unfeature: take the learner off the public page. */
    public function unfeature(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        \App\Services\Academy\FeaturedLearners::unfeature($id);
        $this->audit('academy.learner.unfeature', 'academy_learner', $id, 'Removed ' . $l['name'] . ' from the featured students');
        Flash::success($l['name'] . ' is no longer featured.');
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $new = (int) $l['is_active'] === 1 ? 0 : 1;
        Database::update('academy_learners', ['is_active' => $new], 'id = ?', [$id]);
        $this->audit('academy.learner.' . ($new ? 'activate' : 'deactivate'), 'academy_learner', $id, ($new ? 'Switched on' : 'Switched off') . ' the account of ' . $l['email']);
        Flash::success($new ? 'The account is active again.' : 'The account is switched off: the learner cannot sign in. Progress and certificates are kept.');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        Database::execute('DELETE FROM academy_learners WHERE id = ?', [$id]);
        $this->audit('academy.learner.delete', 'academy_learner', $id, 'Deleted the learner account ' . $l['email']);
        Flash::success('The learner account and its progress were deleted. Certificates already issued stay valid.');
        return $this->redirect('admin/academy/learners');
    }

    // ------------------------------------------------------------------------------------------------------------- enrolment / certificate

    public function enroll(Request $request, int $id): Response
    {
        $l = $this->learner($id);
        $course = CourseService::find($request->int('course_id'));
        if ($course === null || !AcademyAccess::canEdit($course)) {
            Flash::error('Choose one of your courses.');
            return $this->redirect('admin/academy/learners/' . $id);
        }
        CourseService::enroll($id, (int) $course['id']);
        $this->audit('academy.learner.enroll', 'academy_learner', $id, 'Enrolled ' . $l['email'] . ' in "' . $course['title'] . '"');
        Flash::success('Enrolled in "' . $course['title'] . '".');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function unenroll(Request $request, int $id, int $courseId): Response
    {
        $l = $this->learner($id);
        $course = CourseService::find($courseId) ?? throw new HttpException(404, 'Not found');
        if (!AcademyAccess::canEdit($course)) {
            throw new HttpException(403, 'Forbidden');
        }
        CourseService::unenroll($id, $courseId);
        $this->audit('academy.learner.unenroll', 'academy_learner', $id, 'Removed ' . $l['email'] . ' from "' . $course['title'] . '" (progress cleared)');
        Flash::success('Removed from "' . $course['title'] . '". Their progress in it was cleared.');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function issueCertificate(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $course = CourseService::find($request->int('course_id')) ?? throw new HttpException(404, 'Not found');
        $existing = CertificateService::existingFor($id, (int) $course['id']);
        if ($existing !== null && CertificateService::isApproved($existing)) {
            Flash::warning('This learner already has a certificate for this course.');
            return $this->redirect('admin/academy/learners/' . $id);
        }
        $tree = CourseService::tree((int) $course['id']);
        $best = $tree['exam'] !== null ? QuizService::passedBest($id, (int) $tree['exam']['id']) : null;
        $cert = CertificateService::issue($l, $course, $best !== null ? (float) $best['score_pct'] : null, $tree['minutes'], true);
        if (!CertificateService::isApproved($cert)) {
            CertificateService::approve((int) $cert['id'], \App\Core\Auth::id(), false);        // issuing it by hand is the approval
            $cert = (array) Database::one('SELECT * FROM academy_certificates WHERE id = ?', [(int) $cert['id']]);
        }
        CourseService::enroll($id, (int) $course['id']);
        Database::execute('UPDATE academy_enrollments SET completed_at = COALESCE(completed_at, NOW()) WHERE learner_id = ? AND course_id = ?', [$id, (int) $course['id']]);
        if ($request->bool('notify')) {
            CertificateService::email($cert);
        }
        $this->audit('academy.certificate.issue', 'academy_certificate', (int) $cert['id'], 'Issued certificate ' . $cert['code'] . ' to ' . $l['email'] . ' by hand');
        Flash::success('Certificate ' . $cert['code'] . ' was issued.');
        return $this->redirect('admin/academy/learners/' . $id);
    }
}
