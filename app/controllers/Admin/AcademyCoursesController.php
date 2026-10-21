<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\Uploader;
use App\Services\Academy\AcademyAccess;
use App\Services\Academy\CourseService;

/**
 * Admin > LAF Academy > Courses: list, create, edit the details (title, topic, level, wording, pass mark, certificate, status, owner), publish / archive,
 * duplicate and delete. Lessons and quizzes are edited under "Curriculum" (AcademyCurriculumController). A facilitator sees and edits only the courses they own;
 * administrators (academy.manage) see all, choose the facilitator, feature a course and set the catalogue order.
 */
final class AcademyCoursesController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.teach');
        [$scope, $params] = AcademyAccess::courseScope('c');
        $where = [$scope];
        $tab = $request->string('status');
        if (in_array($tab, ['published', 'draft', 'archived'], true)) {
            $where[] = 'c.status = ?';
            $params[] = $tab;
        } else {
            $tab = 'all';
        }
        $q = mb_substr(trim($request->string('q')), 0, 80);
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(c.title LIKE ? OR c.summary LIKE ?)';
            array_push($params, $like, $like);
        }
        $cat = $request->string('category');
        if (isset(CourseService::CATEGORIES[$cat])) {
            $where[] = 'c.category = ?';
            $params[] = $cat;
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_courses c' . $whereSql, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $rows = Database::all(
            'SELECT c.*, u.name AS facilitator_name, '
            . '(SELECT COUNT(*) FROM academy_lessons l WHERE l.course_id = c.id) AS lessons, '
            . '(SELECT COUNT(*) FROM academy_enrollments e WHERE e.course_id = c.id) AS learners '
            . 'FROM academy_courses c LEFT JOIN users u ON u.id = c.facilitator_id' . $whereSql . ' ORDER BY c.sort_order ASC, c.id ASC LIMIT ? OFFSET ?',
            array_merge($params, [$pagination->perPage, $pagination->offset])
        );
        [$sc, $sp] = AcademyAccess::courseScope('c');
        $counts = ['all' => (int) Database::value('SELECT COUNT(*) FROM academy_courses c WHERE ' . $sc, $sp, 0)];
        foreach (['published', 'draft', 'archived'] as $s) {
            $counts[$s] = (int) Database::value('SELECT COUNT(*) FROM academy_courses c WHERE c.status = ? AND ' . $sc, array_merge([$s], $sp), 0);
        }
        return $this->render('admin/academy/courses/index', [
            'rows' => $rows, 'pagination' => $pagination, 'tab' => $tab, 'q' => $q, 'category' => $cat, 'counts' => $counts, 'total' => $total,
            'categories' => CourseService::categoryLabels(), 'manager' => AcademyAccess::manager(),
        ], ['title' => 'Courses', 'nav' => 'academy_courses', 'breadcrumbs' => $this->crumbs('Courses')]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('academy.teach');
        return $this->form(null, ['status' => 'draft', 'category' => 'basics', 'level' => 'beginner', 'pass_mark' => 70, 'certificate_enabled' => 1, 'sort_order' => 100, 'icon' => 'book',
            'facilitator_id' => AcademyAccess::manager() ? null : AcademyAccess::userId()]);
    }

    public function edit(Request $request, int $id): Response
    {
        $course = $this->editableCourse($id);
        return $this->form($course, $course);
    }

    private function form(?array $course, array $row): Response
    {
        $isNew = $course === null;
        return $this->render('admin/academy/courses/form', [
            'row' => $row, 'course' => $course, 'isNew' => $isNew, 'action' => url($isNew ? 'admin/academy/courses' : 'admin/academy/courses/' . $course['id']),
            'categories' => CourseService::categoryLabels(), 'levels' => CourseService::LEVELS, 'manager' => AcademyAccess::manager(), 'facilitators' => AcademyAccess::facilitators(),
            'outcomes' => implode("\n", CourseService::jsonList($row['outcomes'] ?? '')), 'requirements' => implode("\n", CourseService::jsonList($row['requirements'] ?? '')),
            'resources' => implode("\n", array_map(static fn(array $r): string => $r['title'] . ' | ' . $r['url'], CourseService::resources($row['resources'] ?? ''))),
            'tree' => $isNew ? null : CourseService::tree((int) $course['id'], false),
        ], ['title' => $isNew ? 'New course' : 'Edit course', 'nav' => 'academy_courses', 'breadcrumbs' => $this->crumbs('Courses', [$isNew ? 'New course' : (string) $course['title'], null])]);
    }

    /** @return array<string,mixed> the columns to save */
    private function collect(?array $course): array
    {
        $valid = $this->validate([
            'title' => 'required|max:190', 'slug' => 'nullable|max:120', 'category' => 'required|in:' . implode(',', array_keys(CourseService::CATEGORIES)),
            'level' => 'required|in:' . implode(',', array_keys(CourseService::LEVELS)), 'icon' => 'nullable|max:40', 'summary' => 'nullable|max:255',
            'pass_mark' => 'required|integer|between:40,100', 'status' => 'required|in:draft,published,archived',
        ], [], ['title' => 'Title', 'pass_mark' => 'Pass mark'], $course === null ? 'admin/academy/courses/create' : 'admin/academy/courses/' . $course['id'] . '/edit');
        $r = $this->request;
        $title = trim(strip_tags((string) $valid['title']));
        $typed = Slug::make($r->string('slug'));
        $ignore = $course !== null ? (int) $course['id'] : null;
        if ($typed === '') {
            $slug = $course !== null ? (string) $course['slug'] : Slug::unique('academy_courses', $title, null, 'slug', [], 'course');
        } else {
            $dup = Database::value('SELECT COUNT(*) FROM academy_courses WHERE slug = ?' . ($ignore !== null ? ' AND id <> ?' : ''), $ignore !== null ? [$typed, $ignore] : [$typed], 0);
            if ((int) $dup > 0) {
                $this->fail(['slug' => 'Another course already uses this web address.'], $course === null ? 'admin/academy/courses/create' : 'admin/academy/courses/' . $course['id'] . '/edit');
            }
            $slug = $typed;
        }
        $lines = static fn(string $s, int $max): array => array_slice(array_values(array_filter(array_map(static fn(string $l): string => mb_substr(trim(strip_tags($l)), 0, 240), preg_split('/\R/', $s) ?: []), static fn(string $l): bool => $l !== '')), 0, $max);
        $res = [];
        foreach (preg_split('/\R/', $r->string('resources')) ?: [] as $l) {
            $parts = array_map('trim', explode('|', $l, 2));
            if (count($parts) === 2 && $parts[0] !== '' && preg_match('~^https?://[^\s"<>]+$~i', $parts[1]) === 1) {
                $res[] = ['title' => mb_substr(strip_tags($parts[0]), 0, 120), 'url' => mb_substr($parts[1], 0, 300)];
            }
        }
        $icon = trim((string) $valid['icon']);
        $data = [
            'slug' => $slug, 'title' => mb_substr($title, 0, 190), 'category' => (string) $valid['category'], 'level' => (string) $valid['level'],
            'icon' => $icon !== '' && preg_match('/^[a-z0-9-]{1,40}$/', $icon) === 1 ? $icon : 'book', 'summary' => mb_substr(trim(strip_tags((string) $valid['summary'])), 0, 255),
            'description' => Sanitizer::html($r->string('description')), 'outcomes' => json_encode($lines($r->string('outcomes'), 12), JSON_UNESCAPED_UNICODE),
            'requirements' => json_encode($lines($r->string('requirements'), 8), JSON_UNESCAPED_UNICODE), 'resources' => json_encode(array_slice($res, 0, 8), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'pass_mark' => (int) $valid['pass_mark'], 'certificate_enabled' => $r->bool('certificate_enabled') ? 1 : 0, 'status' => (string) $valid['status'],
        ];
        if (AcademyAccess::manager()) {
            $data['is_featured'] = $r->bool('is_featured') ? 1 : 0;
            $data['sort_order'] = max(-100000, min(100000, $r->int('sort_order', 100)));
            $fid = $r->int('facilitator_id');
            $data['facilitator_id'] = $fid > 0 && Database::value('SELECT 1 FROM users WHERE id = ? AND is_active = 1', [$fid]) !== null ? $fid : null;
        }
        return $data;
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.teach');
        $data = $this->collect(null);
        if (!AcademyAccess::manager()) {
            $data['facilitator_id'] = AcademyAccess::userId();
        }
        $data['created_by'] = $this->userId();
        $data['status'] = 'draft';              // a new course starts as a draft; publish it when it has lessons
        $id = Database::insert('academy_courses', $data);
        $this->audit('academy.course.create', 'academy_course', $id, 'Created course "' . $data['title'] . '"');
        Flash::success('The course was created. Now add its modules and lessons.');
        return $this->redirect('admin/academy/courses/' . $id . '/curriculum');
    }

    public function update(Request $request, int $id): Response
    {
        $course = $this->editableCourse($id);
        $data = $this->collect($course);
        if ($data['status'] === 'published' && (int) Database::value('SELECT COUNT(*) FROM academy_lessons WHERE course_id = ? AND is_published = 1', [$id], 0) === 0) {
            $data['status'] = 'draft';
            Flash::warning('The course has no published lesson yet, so it was kept as a draft.');
        }
        Database::update('academy_courses', $data, 'id = ?', [$id]);
        $this->audit('academy.course.update', 'academy_course', $id, 'Edited course "' . $data['title'] . '"');
        Flash::success('The course was saved.');
        return $request->string('after') === 'curriculum' ? $this->redirect('admin/academy/courses/' . $id . '/curriculum') : $this->redirect('admin/academy/courses/' . $id . '/edit');
    }

    public function status(Request $request, int $id): Response
    {
        $course = $this->editableCourse($id);
        $to = $request->string('to');
        if (!in_array($to, ['published', 'draft', 'archived'], true)) {
            throw new HttpException(422, 'Unknown status');
        }
        if ($to === 'published' && (int) Database::value('SELECT COUNT(*) FROM academy_lessons WHERE course_id = ? AND is_published = 1', [$id], 0) === 0) {
            Flash::warning('Add at least one published lesson before publishing the course.');
            return $this->redirect('admin/academy/courses/' . $id . '/curriculum');
        }
        Database::update('academy_courses', ['status' => $to], 'id = ?', [$id]);
        $this->audit('academy.course.' . $to, 'academy_course', $id, ucfirst($to) . ' course "' . $course['title'] . '"');
        Flash::success('"' . $course['title'] . '" is now ' . ($to === 'published' ? 'published.' : ($to === 'draft' ? 'a draft (hidden from learners).' : 'archived (hidden, progress kept).')));
        return $this->redirect($this->returnTo($request, 'admin/academy/courses'));
    }

    public function duplicate(Request $request, int $id): Response
    {
        $c = $this->editableCourse($id);
        $newId = Database::transaction(function () use ($c): int {
            $copy = $c;
            unset($copy['id'], $copy['created_at'], $copy['updated_at']);
            $copy['title'] = mb_substr('Copy of ' . $c['title'], 0, 190);
            $copy['slug'] = Slug::unique('academy_courses', (string) $copy['title'], null, 'slug', [], 'course');
            $copy['status'] = 'draft';
            $copy['source'] = null;
            $copy['created_by'] = $this->userId();
            if (!AcademyAccess::manager()) {
                $copy['facilitator_id'] = AcademyAccess::userId();
            }
            $nid = Database::insert('academy_courses', $copy);
            $map = [];
            foreach (Database::all('SELECT * FROM academy_modules WHERE course_id = ? ORDER BY sort_order, id', [(int) $c['id']]) as $m) {
                $old = (int) $m['id'];
                unset($m['id'], $m['created_at'], $m['updated_at']);
                $m['course_id'] = $nid;
                $map[$old] = Database::insert('academy_modules', $m);
            }
            foreach (Database::all('SELECT * FROM academy_lessons WHERE course_id = ? ORDER BY sort_order, id', [(int) $c['id']]) as $l) {
                unset($l['id'], $l['created_at'], $l['updated_at']);
                $l['course_id'] = $nid;
                $l['module_id'] = $map[(int) $l['module_id']] ?? array_values($map)[0];
                $l['file_path'] = null;
                $l['file_name'] = null;
                $l['file_size'] = null;
                Database::insert('academy_lessons', $l);
            }
            foreach (Database::all('SELECT * FROM academy_quizzes WHERE course_id = ?', [(int) $c['id']]) as $q) {
                $oldQ = (int) $q['id'];
                unset($q['id'], $q['created_at'], $q['updated_at']);
                $q['course_id'] = $nid;
                $q['module_id'] = $q['module_id'] === null ? null : ($map[(int) $q['module_id']] ?? null);
                $nq = Database::insert('academy_quizzes', $q);
                foreach (Database::all('SELECT * FROM academy_questions WHERE quiz_id = ? ORDER BY sort_order, id', [$oldQ]) as $qu) {
                    $oldQu = (int) $qu['id'];
                    unset($qu['id']);
                    $qu['quiz_id'] = $nq;
                    $nqu = Database::insert('academy_questions', $qu);
                    foreach (Database::all('SELECT * FROM academy_options WHERE question_id = ? ORDER BY sort_order, id', [$oldQu]) as $o) {
                        unset($o['id']);
                        $o['question_id'] = $nqu;
                        Database::insert('academy_options', $o);
                    }
                }
            }
            return $nid;
        });
        $this->audit('academy.course.duplicate', 'academy_course', $newId, 'Duplicated course "' . $c['title'] . '"');
        Flash::success('A draft copy was made. Uploaded handouts are not copied: attach them again if needed.');
        return $this->redirect('admin/academy/courses/' . $newId . '/edit');
    }

    public function delete(Request $request, int $id): Response
    {
        $c = $this->editableCourse($id);
        $learners = (int) Database::value('SELECT COUNT(*) FROM academy_enrollments WHERE course_id = ?', [$id], 0);
        if ($learners > 0 && !AcademyAccess::manager()) {
            Flash::error('Learners are enrolled in this course. Ask an administrator, or archive it instead.');
            return $this->redirect('admin/academy/courses');
        }
        foreach (Database::all('SELECT file_path FROM academy_lessons WHERE course_id = ? AND file_path IS NOT NULL', [$id]) as $f) {
            Uploader::deletePrivate((string) $f['file_path']);
        }
        Database::execute('DELETE FROM academy_courses WHERE id = ?', [$id]);
        $this->audit('academy.course.delete', 'academy_course', $id, 'Deleted course "' . $c['title'] . '"', ['learners' => $learners]);
        Flash::success('"' . $c['title'] . '" was deleted' . ($learners > 0 ? ' with the progress of ' . $learners . ' learner' . ($learners === 1 ? '' : 's') . '. Certificates already issued stay valid.' : '.'));
        return $this->redirect('admin/academy/courses');
    }

    private function returnTo(Request $request, string $fallback): string
    {
        $to = ltrim($request->string('return'), '/');
        return preg_match('~^admin/academy/[A-Za-z0-9/_\-]*$~', $to) === 1 ? $to : $fallback;
    }
}
