<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Flash;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\CertificateService;
use App\Services\Academy\ClassService;
use App\Services\Academy\CourseService;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerMessages;
use App\Services\Academy\LearnerPortrait;

/**
 * The learner portal (docs/ACADEMY.md): one place with tabs for everything a learner needs.
 *   /academy/dashboard           Overview: numbers, continue learning, next classes, latest message, profile completeness
 *   /academy/portal/courses      My courses: progress in each, resume, certificate status
 *   /academy/portal/certificates My certificates: ready, waiting for approval and signing, withdrawn
 *   /academy/portal/messages     Messages with the Academy team (read and write)
 *   /academy/account             Profile: name, bio, phone, WhatsApp, country, picture   (AcademyAccountController)
 *   /academy/portal/security     Password, sign out, delete the account
 * Everything is private to the signed-in learner (noindex); anybody else is sent to sign in.
 */
final class AcademyPortalController extends AcademyBaseController
{
    /** @return array{0:?array<string,mixed>,1:?Response} the learner, or the sign-in redirect */
    private function who(): array
    {
        $l = $this->learner();
        return $l === null ? [null, $this->loginRedirect()] : [$l, null];
    }

    /** @param array<string,mixed> $data */
    private function page(string $view, string $tab, string $title, array $l, array $data = []): Response
    {
        $data += ['me' => $l, 'tab' => $tab, 'unread' => LearnerMessages::unreadForLearner((int) $l['id']), 'portrait' => LearnerPortrait::of((int) $l['id']), 'title' => $title];
        return $this->renderAcademy('web/academy/portal/' . $view, $data, [
            'bodyClass' => 'is-academy is-academy-dashboard is-academy-portal',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['My portal', url('academy/dashboard')], [$title, null]],
            'meta' => ['title' => $title . ' | My portal'],
        ], true);
    }

    public function overview(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        $id = (int) $l['id'];
        $courses = CourseService::dashboard($id);
        $certs = CertificateService::forLearner($id);
        $done = 0;
        $lessons = 0;
        foreach ($courses as $c) {
            $done += $c['completed_at'] !== null ? 1 : 0;
            $lessons += (int) $c['progress']['done'];
        }
        $port = LearnerPortrait::of($id);
        // the courses the learner can enrol in (published, with lessons, not enrolled yet), optionally searched
        $q = mb_substr(trim($request->string('q')), 0, 60);
        $mine = array_map(static fn(array $c): int => (int) $c['course_id'], $courses);
        $avail = array_values(array_filter(CourseService::published($q !== '' ? ['q' => $q] : []), static fn(array $c): bool => (int) $c['lessons'] > 0 && !in_array((int) $c['id'], $mine, true)));
        return $this->page('overview', 'overview', 'Overview', $l, [
            'available' => array_slice($avail, 0, 6), 'availableTotal' => count($avail), 'q' => $q,
            'courses' => $courses, 'classes' => ClassService::nextForLearner($id), 'certificates' => $certs, 'latest' => LearnerMessages::latest($id),
            'stats' => ['courses' => count($courses), 'completed' => $done, 'lessons' => $lessons, 'certificates' => count($certs)],
            'complete' => LearnerAuth::completeness($l, !empty($port['photo_path'])),
        ]);
    }

    public function courses(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        return $this->page('courses', 'courses', 'My courses', $l, ['courses' => CourseService::dashboard((int) $l['id'])]);
    }

    public function certificates(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        $id = (int) $l['id'];
        return $this->page('certificates', 'certificates', 'My certificates', $l, [
            'certificates' => CertificateService::forLearner($id), 'waiting' => CertificateService::pendingForLearner($id),
            'requestable' => array_values(array_filter(CourseService::dashboard($id), static fn(array $c): bool => !empty($c['cert_requestable']))),
        ]);
    }

    public function messages(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        $id = (int) $l['id'];
        $thread = LearnerMessages::thread($id);
        $firstNew = null;
        foreach ($thread as $m) {
            if ($m['sender'] === 'admin' && $m['read_at'] === null) {
                $firstNew = (int) $m['id'];
                break;
            }
        }
        LearnerMessages::markRead($id, 'learner');                // opening the conversation reads the team's messages
        return $this->page('messages', 'messages', 'Messages', $l, ['thread' => $thread, 'firstNew' => $firstNew, 'unread' => 0, 'ready' => LearnerMessages::ready()]);
    }

    public function sendMessage(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        if (!RateLimit::attempt('academy-msg:' . (int) $l['id'], 15, 3600)) {
            Flash::error('You have sent many messages in the last hour. Please wait a little: the Academy team will answer you.');
            return $this->redirect('academy/portal/messages');
        }
        $res = LearnerMessages::send((int) $l['id'], 'learner', $request->string('body'));
        if (!$res['ok']) {
            $this->fail(['body' => (string) $res['error']], 'academy/portal/messages#write');
        }
        Flash::success('Your message was sent to the Academy team. You will see the answer here.');
        return $this->redirect('academy/portal/messages#end');
    }

    public function security(Request $request): Response
    {
        [$l, $redirect] = $this->who();
        if ($redirect !== null) {
            return $redirect;
        }
        return $this->page('security', 'security', 'Password and security', $l);
    }
}
