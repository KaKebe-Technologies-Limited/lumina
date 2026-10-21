<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ClassService;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;

/**
 * PUBLIC LAF Academy pages (no sign-in needed): home with the course catalogue, one course, virtual classes, and the check page / PDF of a certificate.
 *   GET /academy                       home + catalogue (?q= ?category= ?level=)
 *   GET /academy/courses/{slug}        course page (curriculum, outcomes) with the enrol / continue button
 *   GET /academy/classes               virtual classes (the join link only for signed-in learners who may attend)
 *   GET /academy/classes/{id}/calendar.ics
 *   GET /academy/certificate/{code}    public check page of a certificate;  /pdf = the PDF
 */
final class AcademyController extends AcademyBaseController
{
    public function index(Request $request): Response
    {
        $this->gate();
        $filters = [
            'q' => mb_substr(trim($request->string('q')), 0, 80),
            'category' => $request->string('category'),
            'level' => $request->string('level'),
        ];
        $courses = [];
        $counts = [];
        $stats = ['courses' => 0, 'lessons' => 0, 'learners' => 0, 'certificates' => 0];
        $upcoming = [];
        try {
            $courses = CourseService::published($filters);
            $counts = CourseService::categoryCounts();
            $stats = [
                'courses' => (int) Database::value("SELECT COUNT(*) FROM academy_courses WHERE status = 'published'", [], 0),
                'lessons' => (int) Database::value("SELECT COUNT(*) FROM academy_lessons l JOIN academy_courses c ON c.id = l.course_id WHERE c.status = 'published' AND l.is_published = 1", [], 0),
                'learners' => (int) Database::value('SELECT COUNT(*) FROM academy_learners WHERE is_active = 1', [], 0),
                'certificates' => (int) Database::value('SELECT COUNT(*) FROM academy_certificates WHERE revoked_at IS NULL', [], 0),
            ];
            $upcoming = array_slice(ClassService::listing()['upcoming'], 0, 3);
        } catch (\Throwable) {
            // the Academy tables are not imported yet: show the empty home page
        }
        $filtered = $filters['q'] !== '' || $filters['category'] !== '' || $filters['level'] !== '';
        return $this->renderAcademy('web/academy/index', [
            'courses' => $courses, 'counts' => $counts, 'filters' => $filters, 'filtered' => $filtered, 'stats' => $stats, 'upcoming' => $upcoming,
            'categories' => CourseService::categoryLabels(), 'levels' => CourseService::LEVELS,
        ], [
            'bodyClass' => 'is-academy is-academy-home',
            'breadcrumbs' => [[$this->academyLabel(), null]],
            'meta' => $filtered ? ['robots' => 'noindex,follow', 'canonical' => '/academy'] : [],
        ]);
    }

    public function course(Request $request, string $slug): Response
    {
        $this->gate();
        $staff = Auth::check() && AcademyAccess::teacher();
        $course = CourseService::bySlug($slug, $staff);
        if ($course === null) {
            throw new HttpException(404, 'Not found');
        }
        $draft = $course['status'] !== 'published';
        $tree = CourseService::tree((int) $course['id'], !$draft);
        $learner = $this->learner();
        $enrolled = null;
        $progress = null;
        $resume = null;
        $cert = null;
        if ($learner !== null) {
            $enrolled = CourseService::enrollment((int) $learner['id'], (int) $course['id']);
            if ($enrolled !== null) {
                $progress = CourseService::progress((int) $learner['id'], (int) $course['id'], $tree);
                $resume = CourseService::resumeLessonId($tree, $progress['done_ids'], $enrolled['last_lesson_id'] !== null ? (int) $enrolled['last_lesson_id'] : null);
                $cert = CertificateService::activeFor((int) $learner['id'], (int) $course['id']);
            }
        }
        $facilitator = $course['facilitator_id'] !== null ? Database::one('SELECT name FROM users WHERE id = ?', [(int) $course['facilitator_id']]) : null;
        $related = array_slice(array_values(array_filter(CourseService::published(['category' => (string) $course['category']]), static fn(array $c): bool => (int) $c['id'] !== (int) $course['id'])), 0, 3);
        $desc = trim((string) $course['summary']) !== '' ? (string) $course['summary'] : excerpt((string) $course['description'], 175);
        return $this->renderAcademy('web/academy/course', [
            'course' => $course, 'tree' => $tree, 'enrolled' => $enrolled, 'progress' => $progress, 'resume' => $resume, 'certificate' => $cert,
            'draft' => $draft, 'facilitator' => $facilitator['name'] ?? null, 'related' => $related,
            'outcomes' => CourseService::jsonList($course['outcomes']), 'requirements' => CourseService::jsonList($course['requirements']),
            'resources' => CourseService::resources($course['resources']),
        ], [
            'bodyClass' => 'is-academy is-academy-course',
            'meta' => ['title' => (string) $course['title'], 'description' => $desc, 'canonical' => '/academy/courses/' . $course['slug'], 'robots' => $draft ? 'noindex,nofollow' : ''],
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], [(string) $course['title'], null]],
        ]);
    }

    public function classes(Request $request): Response
    {
        $this->gate();
        $list = ['upcoming' => [], 'recorded' => []];
        try {
            $list = ClassService::listing();
        } catch (\Throwable) {
        }
        $learner = $this->learner();
        $lid = $learner !== null ? (int) $learner['id'] : null;
        foreach (['upcoming', 'recorded'] as $k) {
            foreach ($list[$k] as &$c) {
                $c['may_join'] = ClassService::mayJoin($lid, $c);
            }
            unset($c);
        }
        return $this->renderAcademy('web/academy/classes', ['list' => $list, 'platforms' => ClassService::PLATFORMS], [
            'bodyClass' => 'is-academy is-academy-classes',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Virtual classes', null]],
            'meta' => ['title' => 'Virtual classes', 'description' => 'Live online classes with LAF Academy facilitators: dates, times and how to join.'],
        ]);
    }

    public function calendar(Request $request, int $id): Response
    {
        $this->gate();
        $class = ClassService::find($id);
        if ($class === null || $class['status'] === 'cancelled') {
            throw new HttpException(404, 'Not found');
        }
        $learner = $this->learner();
        $with = ClassService::mayJoin($learner !== null ? (int) $learner['id'] : null, $class);
        return Response::text(ClassService::ics($class, $with), 200, 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="laf-academy-class-' . $id . '.ics"')
            ->header('Cache-Control', 'private, no-store');
    }

    public function certificate(Request $request, string $code): Response
    {
        $this->gate();
        $cert = CertificateService::find(strtoupper($code));
        if ($cert === null) {
            return $this->renderAcademy('web/academy/certificate', ['cert' => null, 'code' => mb_substr($code, 0, 30), 'wording' => CertificateService::wording()], [
                'bodyClass' => 'is-academy is-academy-certificate', 'status' => 404,
                'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Certificate check', null]],
                'meta' => ['title' => 'Certificate check', 'robots' => 'noindex,nofollow'],
            ], true);
        }
        return $this->renderAcademy('web/academy/certificate', ['cert' => $cert, 'code' => $cert['code'], 'wording' => CertificateService::wording()], [
            'bodyClass' => 'is-academy is-academy-certificate',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Certificate check', null]],
            'meta' => ['title' => 'Certificate ' . $cert['code'], 'description' => 'Verification of a LAF Academy certificate.', 'robots' => 'noindex,nofollow'],
        ], true);
    }

    public function certificatePdf(Request $request, string $code): Response
    {
        $this->gate();
        $cert = CertificateService::find(strtoupper($code));
        if ($cert === null || $cert['revoked_at'] !== null) {
            throw new HttpException(404, 'Not found');
        }
        $pdf = CertificateService::pdf($cert);
        $name = 'LAF-Academy-certificate-' . $cert['code'] . '.pdf';
        return Response::text($pdf, 200, 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $name . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('X-Robots-Tag', 'noindex');
    }
}
