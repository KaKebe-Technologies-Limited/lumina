<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Newsletter;
use App\Services\Newsletter\NewsletterMailer;
use App\Services\Newsletter\NewsletterService;

/**
 * E-mailing an issue to the subscribers: the panel page and its JSON endpoints. ADMIN ONLY (ability newsletter.send, checked by the routes
 * AND in every method). OWNER: MAILING agent. Contract: docs/NEWSLETTER.md sections 7, 9, 10.
 *
 * All the rules (state machine, queue, batch sender, lock, retries) live in App\Services\Newsletter\NewsletterMailer; this controller only
 * authorises, validates the request, calls the service, writes the audit entry (ids and counts only, never an address) and shapes the JSON.
 *
 * Errors: {"error":"<code>","message":"<human text>", ...}   validation 422 (+errors) | conflict 409 | not_found 404 | not_ready 422 | bad_request 400.
 * Every response is private/no-store (admin area).
 */
final class NewsletterMailController extends AdminController
{
    /** GET /admin/newsletters/{id}/email - the e-mail panel of an issue. The page loads its state from the server and then drives itself with the JSON endpoints. */
    public function panel(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        $row = Newsletter::findOrFail($id);
        NewsletterService::resolveScheduled();
        $row = Newsletter::find($id) ?? $row;
        $user = current_user();
        return $this->render('admin/newsletters/mail/panel', [
            'issue'       => $row,
            'status'      => NewsletterMailer::status($row),
            'adminEmail'  => (string) ($user['email'] ?? ''),
            'publicUrl'   => NewsletterService::url($row, false),
            'editUrl'     => url('admin/newsletters/' . $id . '/edit'),
            'settingsUrl' => url('admin/settings') . '#tab-newsletter',
            'maxTest'     => NewsletterMailer::MAX_TEST_ADDRESSES,
        ], ['title' => 'E-mail this issue', 'nav' => 'newsletters', 'breadcrumbs' => [['Newsletter', url('admin/newsletters')], [(string) $row['title'], url('admin/newsletters/' . $id . '/edit')], ['E-mail', null]]]);
    }

    /**
     * GET /admin/api/newsletters/{id}/email  -> {email:{enabled,subject,preheader,status,total,sent,failed,queued,skipped,started_at,completed_at,...},
     *   recipients, subscribed, live, driver, transport, sender, batch_size, can_start, cannot_start_reason, can_edit}
     * GET ...?preview=1 -> {preview:{subject,preheader,html,text,bytes}} (what subscribers will receive, with a sample unsubscribe link)
     */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        $row = $this->issue($id);
        if ($row === null) {
            return $this->notFound();
        }
        if ($request->query('preview') !== null) {
            $p = NewsletterMailer::preview($row);
            // links in the preview open in a new tab instead of replacing the preview
            $p['html'] = (string) preg_replace('~<head>~i', '<head><base target="_blank">', $p['html'], 1);
            return $this->json(['preview' => $p])->header('Cache-Control', 'private, no-store');
        }
        return $this->json(NewsletterMailer::status($row));
    }

    /** PUT /admin/api/newsletters/{id}/email   body: email_enabled, email_subject, email_preheader (only the keys present change). Locked while a campaign runs and after it is sent. */
    public function saveSettings(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        $res = NewsletterMailer::saveSettings($id, $request->post());
        if (!$res['ok']) {
            return $this->failure($res);
        }
        if (($res['changed'] ?? []) !== []) {
            $this->audit('newsletter.email_settings', 'newsletter', $id, 'Changed e-mail settings of newsletter issue', ['fields' => $res['changed']]);
        }
        return $this->json(NewsletterMailer::status($res['newsletter']) + ['changed' => $res['changed'] ?? []]);
    }

    /** POST /admin/api/newsletters/{id}/email/test   body: emails (1 to 5 addresses, array or comma / line separated text). Subject is prefixed "[TEST]". Does not touch the campaign. */
    public function test(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        $row = $this->issue($id);
        if ($row === null) {
            return $this->notFound();
        }
        $raw = $request->post('emails');
        if (!is_string($raw) && !is_array($raw)) {
            return $this->error('validation', 'Enter at least one e-mail address.', 422, ['errors' => ['emails' => 'Enter at least one e-mail address.']]);
        }
        $parsed = NewsletterMailer::parseAddresses($raw);
        if ($parsed['too_many']) {
            return $this->error('validation', 'You can send a test to at most ' . NewsletterMailer::MAX_TEST_ADDRESSES . ' addresses at a time.', 422, ['errors' => ['emails' => 'At most ' . NewsletterMailer::MAX_TEST_ADDRESSES . ' addresses.']]);
        }
        if ($parsed['invalid'] > 0) {
            $m = $parsed['invalid'] === 1 ? 'One of the addresses is not a valid e-mail address.' : $parsed['invalid'] . ' of the addresses are not valid e-mail addresses.';
            return $this->error('validation', $m, 422, ['errors' => ['emails' => $m]]);
        }
        if ($parsed['emails'] === []) {
            return $this->error('validation', 'Enter at least one e-mail address.', 422, ['errors' => ['emails' => 'Enter at least one e-mail address.']]);
        }
        $results = NewsletterMailer::sendTest($row, $parsed['emails'], $this->userId());
        $ok = count(array_filter($results, static fn(array $r): bool => $r['ok']));
        $this->audit('newsletter.email_test', 'newsletter', $id, 'Sent a test e-mail of a newsletter issue', ['addresses' => count($results), 'sent' => $ok]);
        return $this->json(['results' => $results, 'sent' => $ok, 'failed' => count($results) - $ok]);
    }

    /** POST /admin/api/newsletters/{id}/email/queue   body: confirm_count (must equal the current recipient count). Creates the deliveries; state queued. */
    public function queue(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        $confirm = $request->post('confirm_count');
        if (!is_int($confirm) && !(is_string($confirm) && ctype_digit($confirm))) {
            return $this->error('bad_request', 'Confirm the number of recipients before sending.', 400);
        }
        $res = NewsletterMailer::queue($id, (int) $confirm);
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $this->audit('newsletter.email_queue', 'newsletter', $id, 'Started the e-mail campaign of a newsletter issue', ['recipients' => (int) $res['recipients']]);
        return $this->json(NewsletterMailer::status($res['newsletter']), 202);
    }

    /** POST /admin/api/newsletters/{id}/email/batch - sends the next batch (size from setting newsletter_batch_size); returns progress. busy:true when another sender holds the lock. */
    public function batch(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        // The session file stays locked for the whole request in PHP: release it now so Pause / Cancel clicked in the same browser are not queued behind a slow batch.
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $res = NewsletterMailer::processBatch($id);
        if (empty($res['ok'])) {
            return $this->failure($res);
        }
        return $this->json($res);
    }

    /** POST /admin/api/newsletters/{id}/email/pause - stop after the message in progress; state paused. */
    public function pause(Request $request, int $id): Response
    {
        return $this->control($id, 'pause', 'newsletter.email_pause', 'Paused the e-mail campaign of a newsletter issue');
    }

    /** POST /admin/api/newsletters/{id}/email/resume - paused -> sending. */
    public function resume(Request $request, int $id): Response
    {
        return $this->control($id, 'resume', 'newsletter.email_resume', 'Resumed the e-mail campaign of a newsletter issue');
    }

    /** POST /admin/api/newsletters/{id}/email/cancel - queued / sending / paused -> cancelled; nothing more is sent. */
    public function cancel(Request $request, int $id): Response
    {
        return $this->control($id, 'cancel', 'newsletter.email_cancel', 'Cancelled the e-mail campaign of a newsletter issue');
    }

    /** GET /admin/api/newsletters/{id}/email/deliveries?status=&q=&page=&per_page= - per-recipient results (admin only; addresses are personal data). */
    public function deliveries(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        $out = NewsletterMailer::deliveries($id, [
            'status' => $request->string('status'),
            'q'      => $request->string('q'),
        ], $request->page(), $request->int('per_page', 25));
        return $this->json($out);
    }

    /** POST /admin/api/newsletters/{id}/email/retry - puts failed deliveries back in the queue (attempts reset). */
    public function retry(Request $request, int $id): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        $res = NewsletterMailer::retryFailed($id);
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $this->audit('newsletter.email_retry', 'newsletter', $id, 'Retried failed e-mails of a newsletter issue', ['requeued' => (int) $res['requeued']]);
        return $this->json(NewsletterMailer::status($res['newsletter']) + ['requeued' => (int) $res['requeued']]);
    }

    // ------------------------------------------------------------------------------------------------ helpers

    private function control(int $id, string $action, string $auditAction, string $summary): Response
    {
        $this->authorize('newsletter.send');
        if ($this->issue($id) === null) {
            return $this->notFound();
        }
        $res = NewsletterMailer::$action($id);
        if (!$res['ok']) {
            return $this->failure($res);
        }
        $c = NewsletterMailer::counts($id);
        $this->audit($auditAction, 'newsletter', $id, $summary, ['sent' => $c['sent'], 'failed' => $c['failed'], 'queued' => $c['queued']]);
        return $this->json(NewsletterMailer::status($res['newsletter']));
    }

    /** The issue with scheduled issues resolved (so "live" is accurate), or null. */
    private function issue(int $id): ?array
    {
        NewsletterService::resolveScheduled();
        return Newsletter::find($id);
    }

    /** Map a service failure to the JSON error format. @param array<string,mixed> $res */
    private function failure(array $res): Response
    {
        $code = (string) ($res['code'] ?? 'conflict');
        $message = (string) ($res['message'] ?? 'That could not be done.');
        $extra = [];
        if (isset($res['recipients'])) {
            $extra['recipients'] = (int) $res['recipients'];
        }
        if (isset($res['errors'])) {
            $extra['errors'] = (object) $res['errors'];
        }
        return match ($code) {
            'not_found'   => $this->notFound(),
            'validation'  => $this->error('validation', $message, 422, $extra),
            'not_live', 'not_enabled', 'no_recipients', 'transport', 'incomplete', 'page_unpublished' => $this->error('not_ready', $message, 422, $extra + ['reason' => $code]),
            'nothing'     => $this->error('conflict', $message, 409, $extra + ['reason' => $code]),
            'count_changed' => $this->error('conflict', $message, 409, $extra + ['reason' => 'count_changed']),
            default       => $this->error('conflict', $message, 409, $extra),
        };
    }

    private function notFound(): Response
    {
        return $this->error('not_found', 'That issue no longer exists.', 404);
    }

    /** @param array<string,mixed> $extra */
    private function error(string $code, string $message, int $status, array $extra = []): Response
    {
        return $this->json(['error' => $code, 'message' => $message] + $extra, $status);
    }
}
