<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Flash;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerMessages;

/**
 * "Message the Academy team" (docs/ACADEMY.md): the public form for anybody who is not signed in: a new learner, one waiting for approval, or a visitor with a question.
 * The message reaches Admin > LAF Academy > Messages (in the learner's own conversation when the e-mail address has an account, otherwise as an inquiry). A signed-in learner
 * is sent to the Messages tab of the portal instead.
 */
final class AcademyContactController extends AcademyBaseController
{
    public function form(Request $request): Response
    {
        $this->gate();
        if ($this->learner() !== null) {
            return $this->redirect('academy/portal/messages');
        }
        return $this->renderAcademy('web/academy/contact', [
            'sent' => $request->bool('sent'), 'email' => (string) Session::get('academy_check_email', ''), 'sw' => LearnerAuth::supportWhatsapp(), 'hours' => LearnerAuth::approvalText(),
        ], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Message the Academy team', null]],
            'meta' => ['title' => 'Message the Academy team', 'description' => 'Ask the LAF Academy team about your registration, your approval or the courses.'],
        ]);
    }

    public function send(Request $request): Response
    {
        $this->gate();
        if ($this->learner() !== null) {
            return $this->redirect('academy/portal/messages');
        }
        if (!RateLimit::attempt('academy-inquiry:' . $request->ip(), 5, 3600)) {
            Flash::error('You have sent several messages in the last hour. Please wait a little, or use the WhatsApp line.');
            return $this->redirect('academy/contact');
        }
        $r = LearnerMessages::inquire($request->string('name'), $request->string('email'), $request->string('phone'), $request->string('message'));
        if (!$r['ok']) {
            $this->fail(['message' => (string) $r['error']], 'academy/contact');
        }
        Flash::success('Your message was sent to the Academy team. We will answer you soon.');
        return $this->redirect('academy/contact?sent=1');
    }
}
