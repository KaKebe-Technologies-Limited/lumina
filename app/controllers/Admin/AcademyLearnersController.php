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
            $where[] = 'l.is_active = 0';
        } elseif ($status === 'unverified') {
            $where[] = 'l.is_active = 1 AND l.email_verified_at IS NULL';
        } else {
            $status = '';
        }
        $course = $request->int('course');
        if ($course > 0) {
            $where[] = 'l.id IN (SELECT learner_id FROM academy_enrollments WHERE course_id = ?)';
            $params[] = $course;
        }
        return [' WHERE ' . implode(' AND ', $where), $params, ['q' => $q, 'status' => $status, 'course' => $course]];
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        [$where, $params, $f] = $this->filters($request);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_learners l' . $where, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $extra = (LearnerRegistry::ready() ? ', l.reg_no, l.approved_at' : '') . (LearnerAuth::mailColumns() ? ', l.mail_sent_at, l.mail_error' : '') . (LearnerPortrait::ready() ? ', l.photo_path' : '')
            . (CertificateService::approvalReady() ? ', (SELECT COUNT(*) FROM academy_certificates cw WHERE cw.learner_id = l.id AND cw.approved_at IS NULL AND cw.revoked_at IS NULL) AS certs_waiting' : '');
        $rows = Database::all(
            'SELECT l.id, l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.last_login_at, l.created_at' . $extra . ', '
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
            'SELECT ' . (LearnerRegistry::ready() ? 'l.reg_no' : 'NULL AS reg_no') . ', l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.created_at, l.last_login_at, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, (SELECT COUNT(*) FROM academy_enrollments e2 WHERE e2.learner_id = l.id AND e2.completed_at IS NOT NULL) AS completed, '
            . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL) AS certificates FROM academy_learners l' . $where . ' ORDER BY l.id',
            $params
        );
        $this->audit('academy.learners.export', 'academy_learner', null, 'Downloaded the learner list', ['rows' => count($rows)]);
        return CsvExport::response('academy-learners-' . date('Y-m-d') . '.csv', ['Reg no', 'Name', 'Email', 'Phone', 'Country', 'Email confirmed / approved', 'Active', 'Joined', 'Last sign-in', 'Courses', 'Completed', 'Certificates'],
            array_map(static fn(array $r): array => [$r['reg_no'], $r['name'], $r['email'], $r['phone'], $r['country'], $r['email_verified_at'] ? 'yes' : 'no', $r['is_active'] ? 'yes' : 'no', $r['created_at'], $r['last_login_at'], $r['courses'], $r['completed'], $r['certificates']], $rows));
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
        $this->audit('academy.learner.approve_all', 'academy_learner', null, 'Approved every learner who was waiting', ['count' => $n]);
        Flash::success($n . ' learner' . ($n === 1 ? ' was' : 's were') . ' approved. They can sign in now.');
        return $this->redirect('admin/academy/learners');
    }

    /** The "Approve" button: the learner can sign in now, whether or not the e-mail ever arrives. */
    public function approve(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        if (LearnerRegistry::approve($id, Auth::id())) {
            $this->audit('academy.learner.approve', 'academy_learner', $id, 'Approved the account of ' . $l['email'] . ((string) ($l['reg_no'] ?? '') !== '' ? ' (' . $l['reg_no'] . ')' : ''));
            \App\Services\Academy\AcademyMail::approved($l);        // best effort: the approval does not depend on it
            Flash::success($l['name'] . ' is approved and can sign in now.');
        } else {
            Flash::info('This account is already active.');
        }
        return $this->redirect($request->string('back') === 'list' ? 'admin/academy/learners' : 'admin/academy/learners/' . $id);
    }

    /** The learner's portrait (administrators and facilitators who may see this learner). */
    public function photo(Request $request, int $id): Response
    {
        $this->learner($id);
        return LearnerPortrait::download($id) ?? throw new HttpException(404, 'Not found');
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
        LearnerRegistry::approve($id, Auth::id());
        $this->audit('academy.learner.verify', 'academy_learner', $id, 'Confirmed the e-mail address of ' . $l['email']);
        Flash::success('The e-mail address was marked as confirmed.');
        return $this->redirect('admin/academy/learners/' . $id);
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
