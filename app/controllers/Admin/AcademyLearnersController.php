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
            $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.phone LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        $status = $request->string('status');
        if ($status === 'active') {
            $where[] = 'l.is_active = 1';
        } elseif ($status === 'inactive') {
            $where[] = 'l.is_active = 0';
        } elseif ($status === 'unverified') {
            $where[] = 'l.email_verified_at IS NULL';
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
        $rows = Database::all(
            'SELECT l.id, l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.last_login_at, l.created_at, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, '
            . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL) AS certificates '
            . 'FROM academy_learners l' . $where . ' ORDER BY l.id DESC LIMIT ? OFFSET ?',
            array_merge($params, [$pagination->perPage, $pagination->offset])
        );
        [$sc, $sp] = AcademyAccess::courseScope('c');
        return $this->render('admin/academy/learners/index', [
            'rows' => $rows, 'pagination' => $pagination, 'f' => $f, 'total' => $total, 'manager' => AcademyAccess::manager(),
            'courses' => Database::all('SELECT c.id, c.title FROM academy_courses c WHERE ' . $sc . ' ORDER BY c.title', $sp),
        ], ['title' => 'Learners', 'nav' => 'academy_learners', 'breadcrumbs' => $this->crumbs('Learners')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        [$where, $params] = $this->filters($request);
        $rows = Database::all(
            'SELECT l.name, l.email, l.phone, l.country, l.email_verified_at, l.is_active, l.created_at, l.last_login_at, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.learner_id = l.id) AS courses, (SELECT COUNT(*) FROM academy_enrollments e2 WHERE e2.learner_id = l.id AND e2.completed_at IS NOT NULL) AS completed, '
            . '(SELECT COUNT(*) FROM academy_certificates c WHERE c.learner_id = l.id AND c.revoked_at IS NULL) AS certificates FROM academy_learners l' . $where . ' ORDER BY l.id',
            $params
        );
        $this->audit('academy.learners.export', 'academy_learner', null, 'Downloaded the learner list', ['rows' => count($rows)]);
        return CsvExport::response('academy-learners-' . date('Y-m-d') . '.csv', ['Name', 'Email', 'Phone', 'Country', 'Email confirmed', 'Active', 'Joined', 'Last sign-in', 'Courses', 'Completed', 'Certificates'],
            array_map(static fn(array $r): array => [$r['name'], $r['email'], $r['phone'], $r['country'], $r['email_verified_at'] ? 'yes' : 'no', $r['is_active'] ? 'yes' : 'no', $r['created_at'], $r['last_login_at'], $r['courses'], $r['completed'], $r['certificates']], $rows));
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
        $certs = AcademyAccess::manager() ? CertificateService::forLearner($id) : [];
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
        return \App\Services\Academy\AcademyMail::passwordReset($l, abs_url('academy/reset/' . $token));
    }

    public function resetLink(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        $ok = $this->sendResetLink($id);
        $this->audit('academy.learner.reset_link', 'academy_learner', $id, 'Sent a password link to ' . $l['email']);
        $ok ? Flash::success('A link to choose a new password was e-mailed to ' . $l['email'] . '. It works for 7 days.') : Flash::error('The e-mail could not be sent. Check the e-mail settings (Settings > E-mail).');
        return $this->redirect('admin/academy/learners/' . $id);
    }

    public function verify(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $l = $this->learner($id);
        Database::update('academy_learners', ['email_verified_at' => date('Y-m-d H:i:s'), 'token_hash' => null, 'token_kind' => null, 'token_expires_at' => null], 'id = ?', [$id]);
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
        if (CertificateService::activeFor($id, (int) $course['id']) !== null) {
            Flash::warning('This learner already has a certificate for this course.');
            return $this->redirect('admin/academy/learners/' . $id);
        }
        $tree = CourseService::tree((int) $course['id']);
        $best = $tree['exam'] !== null ? QuizService::passedBest($id, (int) $tree['exam']['id']) : null;
        $cert = CertificateService::issue($l, $course, $best !== null ? (float) $best['score_pct'] : null, $tree['minutes']);
        CourseService::enroll($id, (int) $course['id']);
        Database::execute('UPDATE academy_enrollments SET completed_at = COALESCE(completed_at, NOW()) WHERE learner_id = ? AND course_id = ?', [$id, (int) $course['id']]);
        if ($request->bool('notify')) {
            \App\Services\Academy\AcademyMail::certificate($cert, $l);
        }
        $this->audit('academy.certificate.issue', 'academy_certificate', (int) $cert['id'], 'Issued certificate ' . $cert['code'] . ' to ' . $l['email'] . ' by hand');
        Flash::success('Certificate ' . $cert['code'] . ' was issued.');
        return $this->redirect('admin/academy/learners/' . $id);
    }
}
