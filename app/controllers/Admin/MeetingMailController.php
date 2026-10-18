<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Meeting;
use App\Services\Meetings\MeetingMailer;
use App\Services\Meetings\MeetingService;

/**
 * Sending for a meeting (JSON): queue invitations / updates / cancellations, the batch loop, pause / resume / retry, the test invitation, the free-text
 * message to attendees and "send reminder now". OWNER: MAIL agent. Contract: docs/MEETINGS.md sections 7, 8, 10 and 12.3; the page that drives these endpoints
 * is assets/js/admin-meetings-send.js (root element `[data-meeting-send]`).
 *
 * All rules (queue scopes, claim / markSent / markFailed, reminders, the batch loop, lock, retries, breaker) live in App\Services\Meetings\MeetingMailer and
 * MeetingService; this controller only authorises (ability meetings.manage, also checked by the route group), validates the request, calls the service, writes the
 * audit entry (ids and counts only, never an address) and shapes the JSON.
 *
 * Errors: {"error":"<code>","message":"..."} + extras. bad_request 400 | validation 422 (+errors) | forbidden 403 | not_found 404 | conflict 409 (+reason: ended |
 * nothing) | count_changed 409 (+expected) | not_ready 422 (+errors / reason: transport | readiness) | rate_limited 429. Every response is private, no-store.
 */
final class MeetingMailController extends AdminController
{
    /**
     * GET /admin/api/meetings/{id}/mail  (?attendees=1 adds the per-person state of every active attendee)
     * -> {meeting:{id,title,status,sequence,send_paused,halt_reason,when,ended,organiser_name,organiser_email,platform,join_url,invited}, counts:{total,not_sent,queued,sent,
     *     failed,accepted,declined,tentative,pending,removed}, queue:{waiting,failed}, transport:{driver,level,message,blocking}, sender:{from,from_name,reply_to},
     *     available:{invite_new,invite_pending,update_all,cancel_all,retry_failed}, reminders:{h24,h1,enabled_24h,enabled_1h}, batch_size, readiness? (drafts), attendees?}
     */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        return $this->reply(MeetingMailer::status($m, $request->query('attendees') !== null));
    }

    /**
     * POST /admin/api/meetings/{id}/mail/send  {scope, confirm_count*, attendee_id? (scope one)}
     * scope: invite_new | invite_pending | update_all | cancel_all | retry_failed | one. confirm_count must equal the number the scope would queue now.
     * -> status JSON + {queued} (202), or 409 count_changed {expected}, 422 not_ready {errors}, 409 conflict.
     */
    public function send(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        $scope = $request->post('scope');
        if (!is_string($scope)) {
            return $this->error('bad_request', 'Choose what to send.', 400);
        }
        $res = MeetingMailer::send($m, $scope, $this->intOrNull($request->post('confirm_count')), $this->intOrNull($request->post('attendee_id')), $this->userId());
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $this->audit('meeting.mail_send', 'meeting', $id, 'Started sending to attendees', ['scope' => $scope, 'queued' => (int) $res['queued']]);
        return $this->reply(MeetingMailer::status((array) $res['meeting']) + ['queued' => (int) $res['queued']], 202);
    }

    /** POST /admin/api/meetings/{id}/mail/batch -> {ok, busy, paused, halted:null|{code,reason}, processed, sent, failed, retried, dropped, remaining, done, retry_in, counts, results[]} */
    public function batch(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        if ($this->meeting($id) === null) {
            return $this->notFound();
        }
        // The session file stays locked for the whole request in PHP: release it now so Pause clicked in the same browser is not queued behind a slow batch.
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $res = MeetingMailer::processBatch($id);
        if (empty($res['ok'])) {
            return $this->failure($res);
        }
        return $this->reply($res);
    }

    /** POST /admin/api/meetings/{id}/mail/pause -> status. Stops after the message in progress. */
    public function pause(Request $request, int $id): Response
    {
        return $this->control($id, 'pause', 'meeting.mail_pause', 'Paused sending');
    }

    /** POST /admin/api/meetings/{id}/mail/resume -> status. */
    public function resume(Request $request, int $id): Response
    {
        return $this->control($id, 'resume', 'meeting.mail_resume', 'Resumed sending');
    }

    /** POST /admin/api/meetings/{id}/mail/retry -> status + {queued}. Failed messages go back in the queue (attempts reset). */
    public function retry(Request $request, int $id): Response
    {
        return $this->control($id, 'retry', 'meeting.mail_retry', 'Retried failed e-mails');
    }

    /**
     * POST /admin/api/meetings/{id}/mail/test  {kind?: invite|update|cancel|reminder_24h|reminder_1h}
     * -> {results:[{ok,error}], sent, to}. Goes to the SIGNED-IN USER's own address only; "[TEST]" in the subject; nothing is recorded.
     */
    public function test(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        $kind = $request->post('kind');
        $res = MeetingMailer::sendTest($m, $this->user(), is_string($kind) ? $kind : 'invite');
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $this->audit('meeting.mail_test', 'meeting', $id, 'Sent a test invitation to the administrator', ['sent' => (int) $res['sent']]);
        return $this->reply($res);
    }

    /**
     * POST /admin/api/meetings/{id}/mail/message  {subject?, message*, attendee_ids*: [<= 30 ids]}
     * -> {results:[{id,ok,error}], sent, failed, halted}. Not queued, not stored: the browser sends it in chunks.
     */
    public function message(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        $text = $request->post('message');
        $subject = $request->post('subject', '');
        $ids = $request->post('attendee_ids');
        if (!is_string($text) || !is_string($subject) || !is_array($ids)) {
            return $this->error('validation', 'Write the message and choose who receives it.', 422, ['errors' => (object) ['message' => 'Write the message and choose who receives it.']]);
        }
        $res = MeetingMailer::sendMessage($m, $subject, $text, array_values(array_filter($ids, static fn(mixed $v): bool => is_int($v) || (is_string($v) && ctype_digit($v)))), $this->userId());
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $this->audit('meeting.mail_message', 'meeting', $id, 'Sent a message to attendees', ['sent' => (int) $res['sent'], 'failed' => (int) $res['failed']]);
        return $this->reply($res);
    }

    /**
     * POST /admin/api/meetings/{id}/mail/reminders  {which: '24h'|'1h'} -> {sent, failed, skipped, remaining, busy, halted}
     * "Send reminder now": everybody invited who has not had that reminder, whatever the clock says. Call again while `remaining` > 0 (works in slices).
     */
    public function reminders(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        $which = $request->post('which');
        $res = MeetingMailer::sendRemindersNow($m, is_string($which) ? $which : '');
        if (!$res['ok']) {
            return $this->failure($res);
        }
        return $this->reply($res);
    }

    // ------------------------------------------------------------------------------------------------ helpers

    private function control(int $id, string $action, string $auditAction, string $summary): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->meeting($id);
        if ($m === null) {
            return $this->notFound();
        }
        $res = MeetingMailer::$action($m);
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $fresh = (array) (Meeting::find($id) ?? $m);
        $c = MeetingService::summary($id);
        $this->audit($auditAction, 'meeting', $id, $summary, ['sent' => $c['sent'], 'failed' => $c['failed'], 'queued' => $c['queued']]);
        return $this->reply(MeetingMailer::status($fresh) + ($action === 'retry' ? ['queued' => (int) $res['queued']] : []));
    }

    /** The meeting with the lazy "completed" step applied, or null. */
    private function meeting(int $id): ?array
    {
        MeetingService::completeDue();
        return Meeting::find($id);
    }

    /** A posted whole number (JSON number or digit string), else null. */
    private function intOrNull(mixed $v): ?int
    {
        if (is_int($v)) {
            return $v;
        }
        return is_string($v) && ctype_digit($v) && strlen($v) < 10 ? (int) $v : null;
    }

    /** @param array<string,mixed> $data */
    private function reply(array $data, int $status = 200): Response
    {
        return $this->json($data, $status)->header('Cache-Control', 'private, no-store');
    }

    /** Map a service failure to the JSON error format. @param array<string,mixed> $res */
    private function failure(array $res): Response
    {
        $code = (string) ($res['code'] ?? 'conflict');
        $message = (string) ($res['message'] ?? 'That could not be done.');
        $extra = [];
        foreach (['expected', 'reason'] as $k) {
            if (isset($res[$k])) {
                $extra[$k] = $res[$k];
            }
        }
        if (isset($res['errors'])) {
            $extra['errors'] = (object) $res['errors'];
        }
        return match ($code) {
            'not_found'     => $this->notFound(),
            'bad_request'   => $this->error('bad_request', $message, 400, $extra),
            'validation'    => $this->error('validation', $message, 422, $extra),
            'transport', 'not_ready' => $this->error('not_ready', $message, 422, $extra + ['reason' => $code]),
            'count_changed' => $this->error('count_changed', $message, 409, $extra),
            'rate_limited'  => $this->error('rate_limited', $message, 429, $extra),
            'ended', 'nothing' => $this->error('conflict', $message, 409, $extra + ['reason' => $code]),
            default         => $this->error('conflict', $message, 409, $extra),
        };
    }

    private function notFound(): Response
    {
        return $this->error('not_found', 'That meeting no longer exists.', 404);
    }

    /** @param array<string,mixed> $extra */
    private function error(string $code, string $message, int $status, array $extra = []): Response
    {
        return $this->json(['error' => $code, 'message' => $message] + $extra, $status)->header('Cache-Control', 'private, no-store');
    }
}
