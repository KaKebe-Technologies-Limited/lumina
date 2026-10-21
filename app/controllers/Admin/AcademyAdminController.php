<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\ClassService;
use App\Services\Academy\ContentInstaller;
use App\Services\Academy\CourseService;

/**
 * Admin > LAF Academy: the overview (numbers, what needs attention, shortcuts) and the STARTER COURSES installer (administrators only). Also the base class of the
 * other Academy admin controllers: ability checks (academy.teach = facilitators, academy.manage = administrators) and the ownership rule
 * (a facilitator reaches only the courses they own). Docs: docs/ACADEMY.md.
 */
class AcademyAdminController extends AdminController
{
    /** The course, or 404; and 403 unless the signed-in staff member may edit it. @return array<string,mixed> */
    protected function editableCourse(int $id): array
    {
        $this->authorize('academy.teach');
        $course = CourseService::find($id) ?? throw new HttpException(404, 'Not found');
        if (!AcademyAccess::canEdit($course)) {
            throw new HttpException(403, 'Forbidden');
        }
        return $course;
    }

    protected function crumbs(string $label, ?array ...$more): array
    {
        return array_merge([['LAF Academy', url('admin/academy')], [$label, null]], $more);
    }

    // ------------------------------------------------------------------------------------------------------------- overview

    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        [$scopeSql, $scopeParams] = AcademyAccess::courseScope('c');
        $ids = AcademyAccess::courseIds();
        $in = $ids === null ? null : ($ids === [] ? '0' : implode(',', $ids));
        $stats = [
            'courses' => (int) Database::value('SELECT COUNT(*) FROM academy_courses c WHERE ' . $scopeSql, $scopeParams, 0),
            'published' => (int) Database::value("SELECT COUNT(*) FROM academy_courses c WHERE c.status = 'published' AND " . $scopeSql, $scopeParams, 0),
            'learners' => (int) Database::value($in === null ? 'SELECT COUNT(*) FROM academy_learners' : 'SELECT COUNT(DISTINCT learner_id) FROM academy_enrollments WHERE course_id IN (' . $in . ')', [], 0),
            'enrollments' => (int) Database::value('SELECT COUNT(*) FROM academy_enrollments' . ($in === null ? '' : ' WHERE course_id IN (' . $in . ')'), [], 0),
            'completed' => (int) Database::value('SELECT COUNT(*) FROM academy_enrollments WHERE completed_at IS NOT NULL' . ($in === null ? '' : ' AND course_id IN (' . $in . ')'), [], 0),
            'certificates' => (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE revoked_at IS NULL' . ($in === null ? '' : ' AND course_id IN (' . $in . ')'), [], 0),
            'attempts7' => (int) Database::value('SELECT COUNT(*) FROM academy_attempts WHERE submitted_at >= (NOW() - INTERVAL 7 DAY)' . ($in === null ? '' : ' AND course_id IN (' . $in . ')'), [], 0),
            'new30' => AcademyAccess::manager() ? (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE created_at >= (NOW() - INTERVAL 30 DAY)', [], 0) : 0,
            'unverified' => AcademyAccess::manager() ? (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE email_verified_at IS NULL AND is_active = 1', [], 0) : 0,
        ];
        ClassService::settle();
        $classes = Database::all(
            "SELECT cl.*, c.title AS course_title FROM academy_classes cl LEFT JOIN academy_courses c ON c.id = cl.course_id WHERE cl.status = 'scheduled' AND cl.end_at >= NOW()"
            . (AcademyAccess::manager() ? '' : ' AND (cl.facilitator_id = ' . AcademyAccess::userId() . ' OR cl.course_id IN (' . ($ids === [] || $ids === null ? '0' : implode(',', $ids)) . '))')
            . ' ORDER BY cl.start_at ASC LIMIT 5'
        );
        $top = Database::all(
            'SELECT c.id, c.title, c.status, (SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = c.id) AS learners, (SELECT COUNT(*) FROM academy_enrollments e2 WHERE e2.course_id = c.id AND e2.completed_at IS NOT NULL) AS completed
             FROM academy_courses c WHERE ' . $scopeSql . ' ORDER BY learners DESC, c.title ASC LIMIT 6',
            $scopeParams
        );
        $recent = AcademyAccess::manager() ? Database::all('SELECT id, name, email, email_verified_at, created_at FROM academy_learners ORDER BY id DESC LIMIT 6') : [];
        return $this->render('admin/academy/index', [
            'stats' => $stats, 'classes' => $classes, 'top' => $top, 'recent' => $recent,
            'starter' => AcademyAccess::manager() ? ContentInstaller::status() : [], 'manager' => AcademyAccess::manager(),
        ], ['title' => 'LAF Academy', 'nav' => 'academy', 'breadcrumbs' => [['LAF Academy', null]]]);
    }

    // ------------------------------------------------------------------------------------------------------------- starter courses

    public function installStarter(Request $request, string $slug): Response
    {
        $this->authorize('academy.manage');
        $r = ContentInstaller::install($slug, $this->userId(), $request->bool('publish'));
        if ($r['ok']) {
            $this->audit('academy.starter.install', 'academy_course', (int) ($r['id'] ?? 0), 'Installed starter course ' . $slug);
            Flash::success($r['message'] . ($request->bool('publish') ? ' It is published.' : ' It is a draft: publish it when you are ready.'));
        } else {
            Flash::warning($r['message']);
        }
        return $this->redirect('admin/academy#starter');
    }

    public function installAllStarter(Request $request): Response
    {
        $this->authorize('academy.manage');
        $r = ContentInstaller::installAll($this->userId(), $request->bool('publish'));
        $this->audit('academy.starter.install_all', 'academy_course', null, 'Installed ' . $r['installed'] . ' starter courses');
        Flash::success($r['installed'] === 0 ? 'Every starter course is already installed.' : $r['installed'] . ' starter course' . ($r['installed'] === 1 ? '' : 's') . ' installed' . ($request->bool('publish') ? ' and published.' : ' as drafts.'));
        foreach ($r['messages'] as $m) {
            Flash::warning($m);
        }
        return $this->redirect('admin/academy#starter');
    }

    public function reinstallStarter(Request $request, string $slug): Response
    {
        $this->authorize('academy.manage');
        $r = ContentInstaller::reinstall($slug, $this->userId());
        $r['ok'] ? Flash::success('Reset to the starter version. ' . $r['message']) : Flash::error($r['message']);
        if ($r['ok']) {
            $this->audit('academy.starter.reinstall', 'academy_course', null, 'Reinstalled starter course ' . $slug);
        }
        return $this->redirect('admin/academy#starter');
    }
}
