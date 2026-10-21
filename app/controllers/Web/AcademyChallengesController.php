<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Uploader;
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;

/**
 * Public: the CHALLENGES of the LAF Academy (quiz challenges, innovation challenges, hackathons) and the Winners of the month. Learners apply, take part
 * (a timed quiz, or a project submission) and see the results; the system ranks the entries and marks the winners (ChallengeService::settle).
 * Everything that changes data needs a signed-in learner; the pages are open to visitors. Docs: docs/ACADEMY.md.
 */
final class AcademyChallengesController extends AcademyBaseController
{
    /** The challenge visible to the public, or 404 (drafts are never shown). @return array<string,mixed> */
    private function challenge(string $slug): array
    {
        $this->gate();
        ChallengeService::settle();
        return ChallengeService::bySlug($slug) ?? throw new HttpException(404, 'Not found');
    }

    /** The signed-in learner and their entry, or a redirect response. @return array{0:array<string,mixed>,1:?array<string,mixed>}|Response */
    private function mine(array $c): array|Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect('Please sign in or create a free account to take part.');
        }
        return [$l, ChallengeService::entryOf((int) $c['id'], (int) $l['id'])];
    }

    private function backTo(array $c, string $anchor = ''): Response
    {
        return $this->redirect('academy/challenges/' . $c['slug'] . $anchor);
    }

    // ------------------------------------------------------------------------------------------------------------- pages

    public function index(Request $request): Response
    {
        $this->gate();
        ChallengeService::settle();
        $cat = $request->string('category');
        $kind = $request->string('kind');
        $rows = ChallengeService::publicList($cat, $kind);
        foreach ($rows as &$r) {
            $r['phase'] = ChallengeService::phase($r);
        }
        unset($r);
        $running = array_values(array_filter($rows, static fn(array $r): bool => $r['phase'] !== 'finished'));
        $done = array_values(array_filter($rows, static fn(array $r): bool => $r['phase'] === 'finished'));
        return $this->renderAcademy('web/academy/challenges/index', [
            'running' => $running, 'done' => $done, 'categories' => CourseService::categoryLabels(), 'kinds' => ChallengeService::KINDS, 'filters' => ['category' => $cat, 'kind' => $kind],
            'filtered' => $cat !== '' || $kind !== '',
        ], [
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Challenges', null]],
            'meta' => ['title' => 'Challenges and hackathons', 'description' => 'Quiz challenges, innovation challenges and hackathons at the LAF Academy: apply, take part and win a certificate.'] + ($cat !== '' || $kind !== '' ? ['robots' => 'noindex,follow'] : []),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $l = $this->learner();
        $entry = $l !== null ? ChallengeService::entryOf((int) $c['id'], (int) $l['id']) : null;
        $phase = ChallengeService::phase($c);
        return $this->renderAcademy('web/academy/challenges/show', [
            'c' => $c, 'phase' => $phase, 'entry' => $entry, 'canApply' => ChallengeService::canApply($c), 'board' => ChallengeService::leaderboard($c),
            'criteria' => $c['scoring'] === 'judged' ? ChallengeService::criteria($c) : [], 'entries' => ChallengeService::entryCount((int) $c['id']),
            'questionCount' => $c['kind'] === 'quiz' ? count(ChallengeService::questions($c)) : 0,
            'cert' => $entry !== null && !empty($entry['certificate_id']) ? Database::one('SELECT * FROM academy_certificates WHERE id = ? AND revoked_at IS NULL', [(int) $entry['certificate_id']]) : null,
            'canQuiz' => $entry !== null && ChallengeService::canTakeQuiz($c, $entry), 'canProject' => $entry !== null && ChallengeService::canSubmitProject($c, $entry),
        ], [
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Challenges', url('academy/challenges')], [(string) $c['title'], null]],
            'meta' => ['title' => (string) $c['title'], 'description' => (string) ($c['summary'] ?? '')],
        ]);
    }

    // ------------------------------------------------------------------------------------------------------------- applying and entry

    public function apply(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [$l] = $m;
        $res = ChallengeService::apply($c, $l, $request->all(), $request->file('photo'));
        if (!$res['ok']) {
            $this->fail($res['errors'], 'academy/challenges/' . $slug . '#apply');
        }
        $entry = (array) $res['entry'];
        if (isset($res['errors']['photo'])) {
            Flash::warning('Your entry was saved, but the picture could not be used: ' . $res['errors']['photo']);
        }
        Flash::success($entry['status'] === 'approved' ? 'You are in! Come back when the challenge starts.' : 'Your application was sent. We will e-mail you when it has been reviewed.');
        return $this->backTo($c, '#entry');
    }

    public function withdraw(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        if ($entry !== null && in_array($entry['status'], ['pending', 'approved'], true) && empty($c['finalized_at']) && $entry['quiz_started_at'] === null) {
            ChallengeService::withdraw($entry);
            Flash::success('Your entry was withdrawn.');
        } else {
            Flash::warning('This entry cannot be withdrawn any more.');
        }
        return $this->backTo($c);
    }

    public function photo(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        if ($entry === null || !empty($c['finalized_at'])) {
            throw new HttpException(404, 'Not found');
        }
        $consent = $request->bool('consent');
        $file = $request->file('photo');
        if ($file !== null && !$consent && empty($entry['photo_consent_at'])) {
            $this->fail(['consent' => 'Tick the box to agree that the picture may be published if you win.'], 'academy/challenges/' . $slug . '#entry');
        }
        if ($file !== null) {
            $r = ChallengeService::savePhoto($entry, $file);
            if (!$r['ok']) {
                $this->fail(['photo' => $r['error']], 'academy/challenges/' . $slug . '#entry');
            }
        }
        ChallengeService::setConsent($entry, $consent);
        Flash::success('Your picture and consent were saved.');
        return $this->backTo($c, '#entry');
    }

    // ------------------------------------------------------------------------------------------------------------- quiz

    public function quizStart(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        $started = $entry !== null ? ChallengeService::startQuiz($c, $entry) : null;
        if ($started === null) {
            Flash::warning('You cannot start this quiz now.');
            return $this->backTo($c);
        }
        return $this->redirect('academy/challenges/' . $slug . '/quiz');
    }

    public function quiz(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        if ($entry === null || $entry['quiz_started_at'] === null || $entry['quiz_submitted_at'] !== null || !ChallengeService::canTakeQuiz($c, $entry)) {
            return $this->backTo($c);
        }
        $deadline = ChallengeService::quizDeadline($c, $entry);
        return $this->renderAcademy('web/academy/challenges/quiz', [
            'c' => $c, 'entry' => $entry, 'questions' => ChallengeService::shownQuestions($c, $entry), 'secondsLeft' => $deadline !== null ? max(0, $deadline - time()) : null,
        ], [
            'bodyClass' => 'is-academy is-academy-quiz',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Challenges', url('academy/challenges')], [(string) $c['title'], url('academy/challenges/' . $slug)], ['Quiz', null]],
            'meta' => ['title' => (string) $c['title'] . ': quiz'],
        ], true);
    }

    public function quizSubmit(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        $answers = $request->input('a', []);
        $done = $entry !== null ? ChallengeService::submitQuiz($c, $entry, is_array($answers) ? $answers : []) : null;
        if ($done === null) {
            Flash::warning('This quiz cannot be submitted.');
            return $this->backTo($c);
        }
        Flash::success('Your answers were submitted: ' . (int) $done['quiz_correct'] . ' of ' . (int) $done['quiz_total'] . ' correct. The final ranking is published when the challenge ends.');
        return $this->backTo($c, '#entry');
    }

    // ------------------------------------------------------------------------------------------------------------- project

    public function project(Request $request, string $slug): Response
    {
        $c = $this->challenge($slug);
        $m = $this->mine($c);
        if ($m instanceof Response) {
            return $m;
        }
        [, $entry] = $m;
        if ($entry === null) {
            throw new HttpException(404, 'Not found');
        }
        $res = ChallengeService::submitProject($c, $entry, $request->all(), $request->file('sub_file'));
        if (!$res['ok']) {
            $this->fail($res['errors'], 'academy/challenges/' . $slug . '#entry');
        }
        Flash::success('Your project was submitted. You can update it until the challenge ends.');
        return $this->backTo($c, '#entry');
    }

    // ------------------------------------------------------------------------------------------------------------- winners

    public function winners(Request $request): Response
    {
        $this->gate();
        ChallengeService::settle();
        return $this->renderAcademy('web/academy/winners', ['months' => ChallengeService::winnersByMonth(12)], [
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Winners', null]],
            'meta' => ['title' => 'Winners of the month', 'description' => 'The learners who topped the LAF Academy challenges and hackathons.'],
        ]);
    }

    public function winnerPhoto(Request $request, int $id): Response
    {
        $this->gate();
        $e = ChallengeService::entry($id);
        $c = $e !== null ? ChallengeService::find((int) $e['challenge_id']) : null;
        if ($e === null || $c === null || $c['status'] !== 'published' || empty($c['finalized_at']) || !ChallengeService::photoPublic($e)) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download((string) $e['photo_path'], 'winner-' . $id . '.jpg', true)->header('Cache-Control', 'public, max-age=3600');
    }
}
