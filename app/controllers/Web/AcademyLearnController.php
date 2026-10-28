<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ClassService;
use App\Services\Academy\CourseService;
use App\Services\Academy\QuizService;

/**
 * The signed-in learner side of the LAF Academy: dashboard, enrolling, the lesson player, marking lessons done, handouts, quizzes and the final exam.
 * Every screen needs a signed-in learner who is ENROLLED in the course (else: sign in, or back to the course page). Draft courses are not reachable.
 * Grading happens on the server (App\Services\Academy\QuizService); finishing the last lesson and passing the final exam complete the course and issue the certificate.
 */
final class AcademyLearnController extends AcademyBaseController
{
    // ------------------------------------------------------------------------------------------------------------- dashboard / enrol

    public function dashboard(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $courses = CourseService::dashboard((int) $l['id']);
        return $this->renderAcademy('web/academy/dashboard', [
            'me' => $l, 'courses' => $courses, 'classes' => ClassService::nextForLearner((int) $l['id']), 'certificates' => CertificateService::forLearner((int) $l['id']),
        ], [
            'bodyClass' => 'is-academy is-academy-dashboard',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['My learning', null]],
            'meta' => ['title' => 'My learning'],
        ], true);
    }

    public function enroll(Request $request, string $slug): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect('Please sign in or create a free account to start this course.');
        }
        $course = CourseService::bySlug($slug) ?? throw new HttpException(404, 'Not found');
        $tree = CourseService::tree((int) $course['id']);
        if ($tree['lessons'] === 0) {
            Flash::warning('This course has no lessons yet. Please check back soon.');
            return $this->redirect('academy/courses/' . $course['slug']);
        }
        CourseService::enroll((int) $l['id'], (int) $course['id']);
        $enr = CourseService::enrollment((int) $l['id'], (int) $course['id']);
        $resume = CourseService::resumeLessonId($tree, [], $enr !== null && $enr['last_lesson_id'] !== null ? (int) $enr['last_lesson_id'] : null);
        Flash::success('You are enrolled in "' . $course['title'] . '". Good luck!');
        return $this->redirect('academy/learn/' . $course['slug'] . '/lesson/' . $resume);
    }

    /** After finishing the course and passing the exam: ask for the certificate (the Academy team approves it; with approval off it is issued at once). */
    public function requestCertificate(Request $request, string $slug): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $course = CourseService::bySlug($slug, true) ?? throw new HttpException(404, 'Not found');
        $res = CertificateService::request($l, $course);
        if (!$res['ok']) {
            $res['waiting'] ? Flash::info((string) $res['error']) : Flash::error((string) $res['error']);
        } elseif ($res['waiting']) {
            Flash::success('Your certificate request was sent. The Academy team will approve it soon, and you will be able to download it here.');
        } else {
            Flash::success('Your certificate is ready!');
            return $this->redirect('academy/certificate/' . $res['cert']['code']);
        }
        return $this->redirect('academy/courses/' . $course['slug']);
    }

    public function unenroll(Request $request, string $slug): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $course = CourseService::bySlug($slug, true) ?? throw new HttpException(404, 'Not found');
        CourseService::unenroll((int) $l['id'], (int) $course['id']);
        Flash::success('You left "' . $course['title'] . '" and its progress was cleared. Certificates you earned stay valid.');
        return $this->redirect('academy/dashboard');
    }

    // ------------------------------------------------------------------------------------------------------------- context

    /**
     * Course + tree + progress for a learner, or a Response when they must sign in / enrol.
     * @return array{course:array<string,mixed>,tree:array<string,mixed>,progress:array<string,mixed>,learner:array<string,mixed>,enrolment:array<string,mixed>}|Response
     */
    private function context(string $slug)
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $course = CourseService::bySlug($slug) ?? throw new HttpException(404, 'Not found');
        $enr = CourseService::enrollment((int) $l['id'], (int) $course['id']);
        if ($enr === null) {
            Flash::info('Enrol in the course to start learning.');
            return $this->redirect('academy/courses/' . $course['slug']);
        }
        $tree = CourseService::tree((int) $course['id']);
        return ['course' => $course, 'tree' => $tree, 'progress' => CourseService::progress((int) $l['id'], (int) $course['id'], $tree), 'learner' => $l, 'enrolment' => $enr];
    }

    // ------------------------------------------------------------------------------------------------------------- lessons

    public function resume(Request $request, string $slug): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $id = CourseService::resumeLessonId($c['tree'], $c['progress']['done_ids'], $c['enrolment']['last_lesson_id'] !== null ? (int) $c['enrolment']['last_lesson_id'] : null);
        return $id === null ? $this->redirect('academy/courses/' . $c['course']['slug']) : $this->redirect('academy/learn/' . $c['course']['slug'] . '/lesson/' . $id);
    }

    public function lesson(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lesson = CourseService::lesson($id);
        if ($lesson === null || (int) $lesson['course_id'] !== (int) $c['course']['id'] || (int) $lesson['is_published'] !== 1) {
            throw new HttpException(404, 'Not found');
        }
        CourseService::touch((int) $c['learner']['id'], (int) $c['course']['id'], $id);
        $done = in_array($id, $c['progress']['done_ids'], true);
        $module = null;
        foreach ($c['tree']['modules'] as $m) {
            if ((int) $m['id'] === (int) $lesson['module_id']) {
                $module = $m;
            }
        }
        $quizState = $this->quizStates($c);
        return $this->renderAcademy('web/academy/lesson', [
            'course' => $c['course'], 'tree' => $c['tree'], 'progress' => $c['progress'], 'lesson' => $lesson, 'module' => $module, 'done' => $done,
            'prev' => CourseService::previousLessonId($c['tree'], $id), 'next' => CourseService::nextLessonId($c['tree'], $id),
            'embed' => CourseService::youtubeEmbed((string) $lesson['video_url']), 'quizState' => $quizState,
            'examUnlocked' => CourseService::examUnlocked($c['progress']), 'resources' => CourseService::resources($c['course']['resources']),
        ], [
            'bodyClass' => 'is-academy is-academy-learn',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], [(string) $c['course']['title'], url('academy/courses/' . $c['course']['slug'])], [(string) $lesson['title'], null]],
            'meta' => ['title' => $lesson['title'] . ' - ' . $c['course']['title']],
        ], true);
    }

    public function complete(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lesson = CourseService::lesson($id);
        if ($lesson === null || (int) $lesson['course_id'] !== (int) $c['course']['id'] || (int) $lesson['is_published'] !== 1) {
            throw new HttpException(404, 'Not found');
        }
        CourseService::completeLesson((int) $c['learner']['id'], $lesson);
        $cert = CourseService::checkCompletion((int) $c['learner']['id'], (int) $c['course']['id']);
        if ($cert !== null && !empty($cert['requestable'])) {
            Flash::success('Congratulations! You completed the course. You can now request your certificate.');
            return $this->redirect('academy/courses/' . $c['course']['slug']);
        }
        if ($cert !== null) {
            Flash::success('Congratulations! You completed the course and your certificate is ready.');
            return $this->redirect('academy/certificate/' . $cert['code']);
        }
        $next = CourseService::nextLessonId($c['tree'], $id);
        if ($next !== null) {
            return $this->redirect('academy/learn/' . $c['course']['slug'] . '/lesson/' . $next);
        }
        // last lesson: lead to the final exam when there is one
        $progress = CourseService::progress((int) $c['learner']['id'], (int) $c['course']['id'], $c['tree']);
        if ($c['tree']['exam'] !== null && CourseService::examUnlocked($progress)) {
            Flash::success('You finished all the lessons. The final exam is open: pass it to earn your certificate.');
            return $this->redirect('academy/learn/' . $c['course']['slug'] . '/quiz/' . $c['tree']['exam']['id']);
        }
        Flash::success('Lesson completed.');
        return $this->redirect('academy/learn/' . $c['course']['slug'] . '/lesson/' . $id);
    }

    public function reopen(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lesson = CourseService::lesson($id);
        if ($lesson === null || (int) $lesson['course_id'] !== (int) $c['course']['id']) {
            throw new HttpException(404, 'Not found');
        }
        CourseService::uncompleteLesson((int) $c['learner']['id'], $lesson);
        return $this->redirect('academy/learn/' . $c['course']['slug'] . '/lesson/' . $id);
    }

    public function file(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lesson = CourseService::lesson($id);
        if ($lesson === null || (int) $lesson['course_id'] !== (int) $c['course']['id'] || (int) $lesson['is_published'] !== 1 || empty($lesson['file_path'])) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download((string) $lesson['file_path'], (string) ($lesson['file_name'] ?: 'handout'));
    }

    // ------------------------------------------------------------------------------------------------------------- quizzes

    /** Status of every quiz of the course for this learner (for the side menu and the lesson page). @return array<int,array<string,mixed>> quiz id => status */
    private function quizStates(array $c): array
    {
        $out = [];
        $lid = (int) $c['learner']['id'];
        foreach ($c['tree']['modules'] as $m) {
            if ($m['quiz'] !== null) {
                $out[(int) $m['quiz']['id']] = $this->stateOf($lid, $m['quiz']);
            }
        }
        if ($c['tree']['exam'] !== null) {
            $out[(int) $c['tree']['exam']['id']] = $this->stateOf($lid, $c['tree']['exam']);
        }
        return $out;
    }

    private function stateOf(int $learnerId, array $quiz): array
    {
        $best = QuizService::best($learnerId, (int) $quiz['id']);
        return ['best' => $best !== null ? (float) $best['score_pct'] : null, 'passed' => QuizService::passedBest($learnerId, (int) $quiz['id']) !== null];
    }

    private function quizOf(array $c, int $quizId): array
    {
        $quiz = QuizService::find($quizId);
        if ($quiz === null || (int) $quiz['course_id'] !== (int) $c['course']['id']) {
            throw new HttpException(404, 'Not found');
        }
        return $quiz;
    }

    public function quiz(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $quiz = $this->quizOf($c, $id);
        $isExam = $quiz['module_id'] === null;
        $lid = (int) $c['learner']['id'];
        $can = QuizService::canStart($lid, $quiz);
        $locked = $isExam && !CourseService::examUnlocked($c['progress']);
        $open = \App\Core\Database::one('SELECT id FROM academy_attempts WHERE learner_id = ? AND quiz_id = ? AND submitted_at IS NULL ORDER BY id DESC LIMIT 1', [$lid, $id]);
        return $this->renderAcademy('web/academy/quiz', [
            'course' => $c['course'], 'tree' => $c['tree'], 'progress' => $c['progress'], 'quiz' => $quiz, 'isExam' => $isExam, 'can' => $can, 'locked' => $locked,
            'questions' => QuizService::questionCount($id), 'history' => QuizService::history($lid, $id), 'best' => QuizService::best($lid, $id),
            'passed' => QuizService::passedBest($lid, $id) !== null, 'openAttempt' => $open['id'] ?? null,
            'certificate' => CertificateService::activeFor($lid, (int) $c['course']['id']), 'certificatePending' => CertificateService::pendingFor($lid, (int) $c['course']['id']),
            'certificateRequestable' => CertificateService::eligible($lid, (int) $c['course']['id']), 'quizState' => $this->quizStates($c),
        ], [
            'bodyClass' => 'is-academy is-academy-learn is-academy-quiz',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], [(string) $c['course']['title'], url('academy/courses/' . $c['course']['slug'])], [(string) $quiz['title'], null]],
            'meta' => ['title' => $quiz['title']],
        ], true);
    }

    public function startQuiz(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $quiz = $this->quizOf($c, $id);
        $lid = (int) $c['learner']['id'];
        if ($quiz['module_id'] === null && !CourseService::examUnlocked($c['progress'])) {
            Flash::warning('Finish every lesson first, then the final exam opens.');
            return $this->redirect('academy/learn/' . $c['course']['slug'] . '/quiz/' . $id);
        }
        $can = QuizService::canStart($lid, $quiz);
        if (!$can['ok']) {
            Flash::error((string) $can['reason']);
            return $this->redirect('academy/learn/' . $c['course']['slug'] . '/quiz/' . $id);
        }
        $attempt = QuizService::start($lid, $quiz);
        return $this->redirect('academy/learn/' . $c['course']['slug'] . '/attempt/' . $attempt['id']);
    }

    public function attempt(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lid = (int) $c['learner']['id'];
        $attempt = QuizService::attempt($id, $lid);
        if ($attempt === null || (int) $attempt['course_id'] !== (int) $c['course']['id']) {
            throw new HttpException(404, 'Not found');
        }
        $quiz = $this->quizOf($c, (int) $attempt['quiz_id']);
        $crumbs = [[$this->academyLabel(), url('academy')], [(string) $c['course']['title'], url('academy/courses/' . $c['course']['slug'])], [(string) $quiz['title'], url('academy/learn/' . $c['course']['slug'] . '/quiz/' . $quiz['id'])], [$attempt['submitted_at'] === null ? 'Attempt' : 'Result', null]];
        if ($attempt['submitted_at'] === null) {
            $left = QuizService::secondsLeft($attempt, $quiz);
            return $this->renderAcademy('web/academy/attempt', [
                'course' => $c['course'], 'tree' => $c['tree'], 'progress' => $c['progress'], 'quiz' => $quiz, 'attempt' => $attempt,
                'questions' => QuizService::present($attempt, (int) $quiz['id']), 'secondsLeft' => $left, 'isExam' => $quiz['module_id'] === null, 'quizState' => $this->quizStates($c),
            ], ['bodyClass' => 'is-academy is-academy-learn is-academy-attempt', 'breadcrumbs' => $crumbs, 'meta' => ['title' => $quiz['title']]], true);
        }
        $next = $quiz['module_id'] !== null ? $this->nextAfterModuleQuiz($c, (int) $quiz['module_id']) : null;
        return $this->renderAcademy('web/academy/result', [
            'course' => $c['course'], 'tree' => $c['tree'], 'progress' => $c['progress'], 'quiz' => $quiz, 'attempt' => $attempt, 'isExam' => $quiz['module_id'] === null,
            'review' => QuizService::review($attempt, (int) $quiz['id']), 'can' => QuizService::canStart($lid, $quiz), 'next' => $next,
            'certificate' => CertificateService::activeFor($lid, (int) $c['course']['id']), 'certificatePending' => CertificateService::pendingFor($lid, (int) $c['course']['id']),
            'certificateRequestable' => CertificateService::eligible($lid, (int) $c['course']['id']), 'quizState' => $this->quizStates($c),
        ], ['bodyClass' => 'is-academy is-academy-learn is-academy-result', 'breadcrumbs' => $crumbs, 'meta' => ['title' => 'Result: ' . $quiz['title']]], true);
    }

    /** The first lesson of the module after this one (null at the end). */
    private function nextAfterModuleQuiz(array $c, int $moduleId): ?int
    {
        $found = false;
        foreach ($c['tree']['modules'] as $m) {
            if ($found && $m['lessons'] !== []) {
                return (int) $m['lessons'][0]['id'];
            }
            if ((int) $m['id'] === $moduleId) {
                $found = true;
            }
        }
        return null;
    }

    public function submit(Request $request, string $slug, int $id): Response
    {
        $c = $this->context($slug);
        if ($c instanceof Response) {
            return $c;
        }
        $lid = (int) $c['learner']['id'];
        $attempt = QuizService::attempt($id, $lid);
        if ($attempt === null || (int) $attempt['course_id'] !== (int) $c['course']['id']) {
            throw new HttpException(404, 'Not found');
        }
        $quiz = $this->quizOf($c, (int) $attempt['quiz_id']);
        if ($attempt['submitted_at'] === null) {
            $posted = (array) $request->post('answer', []);
            $attempt = QuizService::submit($attempt, $quiz, $posted);
            if ((int) $attempt['passed'] === 1) {
                $cert = CourseService::checkCompletion($lid, (int) $c['course']['id']);
                if ($cert !== null) {
                    Flash::success(!empty($cert['requestable']) ? 'You passed and completed the course. You can now request your certificate.' : 'You passed and completed the course. Your certificate is ready!');
                }
            }
        }
        return $this->redirect('academy/learn/' . $c['course']['slug'] . '/attempt/' . $attempt['id']);
    }
}
