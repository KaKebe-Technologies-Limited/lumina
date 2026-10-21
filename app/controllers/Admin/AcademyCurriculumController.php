<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Services\Academy\CourseService;
use App\Services\Academy\QuizScript;
use App\Services\Academy\QuizService;

/**
 * Admin > LAF Academy > a course > Curriculum: modules (add, rename, reorder, delete), lessons (text, YouTube video, handout upload, outside link; reorder, show / hide,
 * delete) and the quizzes (the plain-text quiz editor; one quiz per module plus the course's final exam). Only the course owner (facilitator) or an administrator.
 * A handout is stored privately (storage/private/academy) and reaches learners only through the signed-in, enrolled lesson page.
 */
final class AcademyCurriculumController extends AcademyAdminController
{
    private const MAX_FILE = 20 * 1048576;

    public function show(Request $request, int $id): Response
    {
        $course = $this->editableCourse($id);
        $tree = CourseService::tree($id, false);
        $learners = (int) Database::value('SELECT COUNT(*) FROM academy_enrollments WHERE course_id = ?', [$id], 0);
        return $this->render('admin/academy/curriculum', ['course' => $course, 'tree' => $tree, 'learners' => $learners, 'examRow' => QuizService::exam($id)],
            ['title' => 'Curriculum', 'nav' => 'academy_courses', 'breadcrumbs' => $this->crumbs('Courses', ['Curriculum: ' . mb_strimwidth((string) $course['title'], 0, 40, '...'), null])]);
    }

    // ------------------------------------------------------------------------------------------------------------- modules

    private function module(int $mid): array
    {
        $m = Database::one('SELECT * FROM academy_modules WHERE id = ?', [$mid]) ?? throw new HttpException(404, 'Not found');
        $this->editableCourse((int) $m['course_id']);
        return $m;
    }

    public function storeModule(Request $request, int $id): Response
    {
        $this->editableCourse($id);
        $title = trim(strip_tags($request->string('title')));
        if ($title === '' || mb_strlen($title) > 190) {
            Flash::error('Give the module a title of up to 190 characters.');
            return $this->redirect('admin/academy/courses/' . $id . '/curriculum');
        }
        $next = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM academy_modules WHERE course_id = ?', [$id], 10);
        $mid = Database::insert('academy_modules', ['course_id' => $id, 'title' => $title, 'summary' => mb_substr(trim(strip_tags($request->string('summary'))), 0, 255) ?: null, 'sort_order' => $next]);
        $this->audit('academy.module.create', 'academy_module', $mid, 'Added module "' . $title . '"');
        Flash::success('The module was added. Add its first lesson now.');
        return $this->redirect('admin/academy/courses/' . $id . '/curriculum#module-' . $mid);
    }

    public function updateModule(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        $title = trim(strip_tags($request->string('title')));
        if ($title === '' || mb_strlen($title) > 190) {
            Flash::error('Give the module a title of up to 190 characters.');
        } else {
            Database::update('academy_modules', ['title' => $title, 'summary' => mb_substr(trim(strip_tags($request->string('summary'))), 0, 255) ?: null], 'id = ?', [$mid]);
            Flash::success('The module was saved.');
        }
        return $this->redirect('admin/academy/courses/' . $m['course_id'] . '/curriculum#module-' . $mid);
    }

    public function deleteModule(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        foreach (Database::all('SELECT file_path FROM academy_lessons WHERE module_id = ? AND file_path IS NOT NULL', [$mid]) as $f) {
            Uploader::deletePrivate((string) $f['file_path']);
        }
        Database::execute('DELETE FROM academy_modules WHERE id = ?', [$mid]);
        $this->audit('academy.module.delete', 'academy_module', $mid, 'Deleted module "' . $m['title'] . '" with its lessons and quiz');
        Flash::success('The module, its lessons and its quiz were deleted.');
        return $this->redirect('admin/academy/courses/' . $m['course_id'] . '/curriculum');
    }

    public function moveModule(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        $this->swap('academy_modules', 'course_id', (int) $m['course_id'], $mid, $request->string('direction') === 'up' ? -1 : 1);
        return $this->redirect('admin/academy/courses/' . $m['course_id'] . '/curriculum#module-' . $mid);
    }

    /** Move a row one place within its group and renumber 10, 20, 30 ... */
    private function swap(string $table, string $groupCol, int $groupId, int $id, int $dir): void
    {
        Database::transaction(function () use ($table, $groupCol, $groupId, $id, $dir): void {
            $ids = array_map('intval', array_column(Database::all('SELECT id FROM ' . $table . ' WHERE ' . $groupCol . ' = ? ORDER BY sort_order ASC, id ASC', [$groupId]), 'id'));
            $pos = array_search($id, $ids, true);
            if ($pos === false) {
                return;
            }
            $to = $pos + $dir;
            if (isset($ids[$to])) {
                [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];
            }
            $n = 10;
            foreach ($ids as $rowId) {
                Database::execute('UPDATE ' . $table . ' SET sort_order = ? WHERE id = ?', [$n, $rowId]);
                $n += 10;
            }
        });
    }

    // ------------------------------------------------------------------------------------------------------------- lessons

    private function lesson(int $lid): array
    {
        $l = Database::one('SELECT * FROM academy_lessons WHERE id = ?', [$lid]) ?? throw new HttpException(404, 'Not found');
        $this->editableCourse((int) $l['course_id']);
        return $l;
    }

    public function createLesson(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        return $this->lessonForm(null, ['kind' => 'text', 'minutes' => 10, 'is_published' => 1], $m);
    }

    public function editLesson(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        return $this->lessonForm($l, $l, (array) Database::one('SELECT * FROM academy_modules WHERE id = ?', [(int) $l['module_id']]));
    }

    private function lessonForm(?array $lesson, array $row, array $module): Response
    {
        $course = (array) CourseService::find((int) $module['course_id']);
        $isNew = $lesson === null;
        return $this->render('admin/academy/lesson-form', [
            'row' => $row, 'lesson' => $lesson, 'module' => $module, 'course' => $course, 'isNew' => $isNew,
            'action' => url($isNew ? 'admin/academy/modules/' . $module['id'] . '/lessons' : 'admin/academy/lessons/' . $lesson['id']),
            'back' => url('admin/academy/courses/' . $course['id'] . '/curriculum#module-' . $module['id']),
        ], ['title' => $isNew ? 'New lesson' : 'Edit lesson', 'nav' => 'academy_courses', 'breadcrumbs' => $this->crumbs('Courses', ['Curriculum', url('admin/academy/courses/' . $course['id'] . '/curriculum')], [$isNew ? 'New lesson' : (string) $lesson['title'], null])]);
    }

    /** @return array<string,mixed> */
    private function collectLesson(?array $lesson, string $redirect): array
    {
        $r = $this->request;
        $valid = $this->validate(['title' => 'required|max:190', 'kind' => 'required|in:text,video,file,link', 'minutes' => 'required|integer|between:1,240', 'video_url' => 'nullable|max:255', 'link_url' => 'nullable|url|max:500'],
            [], ['title' => 'Title', 'minutes' => 'Time', 'video_url' => 'Video link', 'link_url' => 'Outside page'], $redirect);
        $video = trim((string) $valid['video_url']);
        if ($video !== '' && CourseService::youtubeEmbed($video) === null) {
            $this->fail(['video_url' => 'Paste a YouTube link (youtube.com/watch?v=... or youtu.be/...). Other video sites are not supported here: use "Outside page" instead.'], $redirect);
        }
        $link = trim((string) $valid['link_url']);
        if ($link !== '' && preg_match('~^https://~i', $link) !== 1) {
            $this->fail(['link_url' => 'The outside page must start with https://'], $redirect);
        }
        $data = [
            'title' => trim(strip_tags((string) $valid['title'])), 'kind' => (string) $valid['kind'], 'minutes' => (int) $valid['minutes'],
            'body' => Sanitizer::html($r->string('body')) ?: null, 'video_url' => $video !== '' ? $video : null, 'link_url' => $link !== '' ? $link : null,
            'is_published' => $r->bool('is_published') ? 1 : 0,
        ];
        $file = $r->file('attachment');
        if ($file !== null && (int) ($file['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            try {
                $up = Uploader::privateFile($file, 'academy', null, self::MAX_FILE);
            } catch (UploadException $e) {
                $this->fail(['attachment' => $e->getMessage()], $redirect);
            }
            $data['file_path'] = $up['path'];
            $data['file_name'] = $up['name'];
            $data['file_size'] = $up['size'];
        } elseif ($lesson !== null && $r->bool('remove_attachment')) {
            $data['file_path'] = null;
            $data['file_name'] = null;
            $data['file_size'] = null;
        }
        if ($data['kind'] === 'text' && $data['body'] === null && !isset($data['file_path']) && ($lesson === null || empty($lesson['file_path']))) {
            $this->fail(['body' => 'Write the lesson text (or choose another lesson type).'], $redirect);
        }
        return $data;
    }

    public function storeLesson(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        $data = $this->collectLesson(null, 'admin/academy/modules/' . $mid . '/lessons/create');
        $data += ['course_id' => (int) $m['course_id'], 'module_id' => $mid, 'sort_order' => (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM academy_lessons WHERE module_id = ?', [$mid], 10)];
        $lid = Database::insert('academy_lessons', $data);
        $this->audit('academy.lesson.create', 'academy_lesson', $lid, 'Added lesson "' . $data['title'] . '"');
        Flash::success('The lesson was added.');
        return $request->string('after') === 'edit' ? $this->redirect('admin/academy/lessons/' . $lid . '/edit') : $this->redirect('admin/academy/courses/' . $m['course_id'] . '/curriculum#module-' . $mid);
    }

    public function updateLesson(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        $data = $this->collectLesson($l, 'admin/academy/lessons/' . $lid . '/edit');
        Database::update('academy_lessons', $data, 'id = ?', [$lid]);
        if (array_key_exists('file_path', $data) && !empty($l['file_path']) && $data['file_path'] !== $l['file_path']) {
            Uploader::deletePrivate((string) $l['file_path']);
        }
        $this->audit('academy.lesson.update', 'academy_lesson', $lid, 'Edited lesson "' . $data['title'] . '"');
        Flash::success('The lesson was saved.');
        return $request->string('after') === 'close' ? $this->redirect('admin/academy/courses/' . $l['course_id'] . '/curriculum#module-' . $l['module_id']) : $this->redirect('admin/academy/lessons/' . $lid . '/edit');
    }

    public function deleteLesson(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        Database::execute('DELETE FROM academy_lessons WHERE id = ?', [$lid]);
        if (!empty($l['file_path'])) {
            Uploader::deletePrivate((string) $l['file_path']);
        }
        $this->audit('academy.lesson.delete', 'academy_lesson', $lid, 'Deleted lesson "' . $l['title'] . '"');
        Flash::success('The lesson was deleted. Learners who finished it keep the rest of their progress.');
        return $this->redirect('admin/academy/courses/' . $l['course_id'] . '/curriculum#module-' . $l['module_id']);
    }

    public function moveLesson(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        $this->swap('academy_lessons', 'module_id', (int) $l['module_id'], $lid, $request->string('direction') === 'up' ? -1 : 1);
        return $this->redirect('admin/academy/courses/' . $l['course_id'] . '/curriculum#module-' . $l['module_id']);
    }

    public function toggleLesson(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        Database::update('academy_lessons', ['is_published' => (int) $l['is_published'] === 1 ? 0 : 1], 'id = ?', [$lid]);
        return $this->redirect('admin/academy/courses/' . $l['course_id'] . '/curriculum#module-' . $l['module_id']);
    }

    public function lessonFile(Request $request, int $lid): Response
    {
        $l = $this->lesson($lid);
        if (empty($l['file_path'])) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download((string) $l['file_path'], (string) ($l['file_name'] ?: 'handout'));
    }

    // ------------------------------------------------------------------------------------------------------------- quizzes

    /** Open (creating it when needed) the quiz of a module. */
    public function moduleQuiz(Request $request, int $mid): Response
    {
        $m = $this->module($mid);
        $q = QuizService::ensure((int) $m['course_id'], $mid, 'Quiz: ' . mb_substr((string) $m['title'], 0, 150), 60, null, 0);
        return $this->redirect('admin/academy/quizzes/' . $q['id'] . '/edit');
    }

    public function examQuiz(Request $request, int $id): Response
    {
        $course = $this->editableCourse($id);
        $q = QuizService::ensure($id, null, 'Final exam: ' . mb_substr((string) $course['title'], 0, 150), (int) $course['pass_mark'], null, 5);
        return $this->redirect('admin/academy/quizzes/' . $q['id'] . '/edit');
    }

    private function quiz(int $qid): array
    {
        $q = QuizService::find($qid) ?? throw new HttpException(404, 'Not found');
        $this->editableCourse((int) $q['course_id']);
        return $q;
    }

    public function editQuiz(Request $request, int $qid): Response
    {
        $q = $this->quiz($qid);
        $course = (array) CourseService::find((int) $q['course_id']);
        $module = $q['module_id'] !== null ? Database::one('SELECT * FROM academy_modules WHERE id = ?', [(int) $q['module_id']]) : null;
        $stats = Database::one('SELECT COUNT(*) AS attempts, COUNT(DISTINCT learner_id) AS learners, AVG(score_pct) AS avg_score, SUM(passed = 1) AS passed FROM academy_attempts WHERE quiz_id = ? AND submitted_at IS NOT NULL', [$qid]);
        $draft = $request->has('script') ? (string) $request->post('script') : null;
        return $this->render('admin/academy/quiz', [
            'quiz' => $q, 'course' => $course, 'module' => $module, 'isExam' => $q['module_id'] === null, 'script' => old('script', QuizService::script($qid)), 'count' => QuizService::questionCount($qid), 'stats' => $stats,
        ], ['title' => 'Quiz editor', 'nav' => 'academy_courses', 'breadcrumbs' => $this->crumbs('Courses', ['Curriculum', url('admin/academy/courses/' . $course['id'] . '/curriculum')], [$q['module_id'] === null ? 'Final exam' : 'Quiz', null])]);
    }

    public function saveQuiz(Request $request, int $qid): Response
    {
        $q = $this->quiz($qid);
        $back = 'admin/academy/quizzes/' . $qid . '/edit';
        $valid = $this->validate(['title' => 'required|max:190', 'pass_mark' => 'required|integer|between:1,100', 'time_limit_min' => 'nullable|integer|between:1,240', 'max_attempts' => 'nullable|integer|between:0,100'],
            [], ['title' => 'Title', 'pass_mark' => 'Pass mark', 'time_limit_min' => 'Time limit', 'max_attempts' => 'Attempts'], $back);
        $parsed = QuizScript::parse((string) $request->post('script', ''));
        if (!$parsed['ok']) {
            $this->fail(['script' => 'The quiz text has problems: ' . implode(' ', array_slice($parsed['errors'], 0, 6)) . (count($parsed['errors']) > 6 ? ' (and ' . (count($parsed['errors']) - 6) . ' more)' : '')], $back);
        }
        $tl = trim((string) ($valid['time_limit_min'] ?? ''));
        Database::update('academy_quizzes', [
            'title' => trim(strip_tags((string) $valid['title'])), 'pass_mark' => (int) $valid['pass_mark'], 'time_limit_min' => $tl !== '' ? (int) $tl : null, 'max_attempts' => (int) ($valid['max_attempts'] ?? 0),
        ], 'id = ?', [$qid]);
        QuizService::replaceQuestions($qid, $parsed['questions']);
        if ($q['module_id'] === null) {
            Database::update('academy_courses', ['pass_mark' => max(40, (int) $valid['pass_mark'])], 'id = ?', [(int) $q['course_id']]);
        }
        $this->audit('academy.quiz.save', 'academy_quiz', $qid, 'Saved quiz "' . $valid['title'] . '" with ' . count($parsed['questions']) . ' questions');
        Flash::success('The quiz was saved with ' . count($parsed['questions']) . ' question' . (count($parsed['questions']) === 1 ? '' : 's') . '.');
        return $this->redirect('admin/academy/courses/' . $q['course_id'] . '/curriculum#quiz-' . $qid);
    }

    public function deleteQuiz(Request $request, int $qid): Response
    {
        $q = $this->quiz($qid);
        Database::execute('DELETE FROM academy_quizzes WHERE id = ?', [$qid]);
        $this->audit('academy.quiz.delete', 'academy_quiz', $qid, 'Deleted quiz "' . $q['title'] . '"');
        Flash::success('The quiz was deleted.');
        return $this->redirect('admin/academy/courses/' . $q['course_id'] . '/curriculum');
    }
}
