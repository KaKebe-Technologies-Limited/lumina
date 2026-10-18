<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Meetings\MeetingRequestService;

/**
 * POST /contact/meeting-request: the "Request a meeting" form of the Contact page. OWNER: WEB agent. Contract: docs/MEETINGS.md sections 6 and 10.
 * Route middleware: page.published:contact, honeypot, throttle:5,600 (+ automatic CSRF). The rules are NOT repeated here: MeetingRequestService::create()
 * validates (with the editable registry error texts), applies the per-visitor limit and the duplicate guard, stores the request and sends BOTH e-mails.
 * Nothing is booked: staff answer from Admin > Calendar > Meeting requests.
 *
 * The form's inputs are prefixed `mr_` (mr_name, mr_email ...) because the Contact page already has a form with `name`, `email`, `phone` and `message`.
 * Its errors and old input travel in their OWN flash keys (not through ValidationException), so the main contact form never shows this form's errors
 * in its summary and vice versa. Both keys live for the next request only.
 */
final class MeetingRequestController extends Controller
{
    /** posted name => the name MeetingRequestService::create() reads */
    private const FIELDS = [
        'mr_name' => 'name', 'mr_email' => 'email', 'mr_phone' => 'phone', 'mr_organisation' => 'organisation', 'mr_topic' => 'topic',
        'mr_message' => 'message', 'mr_date' => 'preferred_date', 'mr_time' => 'preferred_time', 'mr_duration' => 'duration_minutes',
        'mr_platform' => 'platform_pref', 'mr_consent' => 'consent',
    ];
    private const BACK = 'contact#meeting-request';

    public function submit(Request $request): Response
    {
        // the form is only on the page when the setting, the registry switch and the tables all allow it; a stray POST is a plain 404
        if (!MeetingRequestService::enabled() || !section_on('contact', 'meeting_request')) {
            $this->abort(404);
        }
        $in = [];
        $old = [];
        foreach (self::FIELDS as $posted => $key) {
            $v = $request->post($posted, '');
            $v = is_string($v) ? trim($v) : '';           // an array value (mr_name[]=) is treated as empty, never cast
            $in[$key] = $v;
            $old[$posted] = mb_substr($v, 0, 3000);
        }

        $res = MeetingRequestService::create($in, ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]);
        if ($res['ok']) {
            // the same screen for a repeated request (the service accepted it silently): nobody learns that it was a duplicate
            Session::flash('meeting_request_sent', ['name' => $in['name']]);
            return $this->redirect(self::BACK);
        }

        $flip = array_flip(self::FIELDS);
        $errors = [];
        foreach ((array) $res['errors'] as $key => $message) {
            $errors[$flip[$key] ?? '_'] = (string) $message;
        }
        Session::flash('meeting_request_errors', $errors);
        Session::flash('meeting_request_old', $old);
        return $this->redirect(self::BACK);
    }
}
