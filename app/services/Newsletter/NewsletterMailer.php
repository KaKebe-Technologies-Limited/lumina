<?php
declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\MailException;
use App\Core\RateLimit;
use App\Core\SitePages;
use App\Core\Smtp;
use App\Models\Newsletter;
use App\Models\NewsletterDelivery;

/**
 * E-mailing an issue to the subscribers: message building, test sends, the delivery queue and the batch sender.
 * OWNER: MAILING agent. Contract: docs/NEWSLETTER.md section 10. Used by Admin\NewsletterMailController (admin AJAX loop) and bin/newsletter-send.php (cron).
 *
 * STATE MACHINE on newsletters.email_status
 *   none --queue--> queued --first batch--> sending --last delivery done--> sent          (sent is FINAL: an issue is never e-mailed twice)
 *                     |                       |  \--pause--> paused --resume--> sending
 *                     \--cancel--> cancelled <-/--cancel (from queued, sending, paused)     cancelled --queue--> queued (sends to whoever is still due)
 *   retry: failed deliveries go back to queued (only after sent, or while paused / sending)
 *
 * GUARANTEES
 *  - Only addresses with subscribers.status = 'subscribed' are queued, and the status is read AGAIN right before every single message
 *    (an unsubscribe in the middle of a campaign takes effect for every message not yet handed to the mail server).
 *  - One row per (issue, address): UNIQUE(newsletter_id, email) + INSERT IGNORE + an atomic state change make queueing idempotent and a double click harmless.
 *  - One sender at a time: the named database lock (GET_LOCK, taken without waiting) covers the web loop, a second admin and the cron job.
 *  - At most NewsletterDelivery::MAX_ATTEMPTS (3) attempts per address; a transient failure waits $retryDelay seconds per attempt already made.
 *  - Nothing sensitive is stored or logged: last_error is a short reason (the SMTP driver already scrubs passwords and addresses; this class masks
 *    anything that still looks like an address), audit entries and the log carry ids and counts only.
 *  - Only a LIVE issue is ever mailed (NewsletterService::isLive), checked when queueing and before every batch.
 * KNOWN LIMIT: if PHP dies after the SMTP server accepted a message but before the row is marked 'sent', that one address is tried again (a duplicate is possible).
 */
final class NewsletterMailer
{
    /** Marker in the pre-rendered message that is replaced by each recipient's own unsubscribe URL. */
    private const UNSUB_PLACEHOLDER = 'LUMINA_UNSUBSCRIBE_URL_PLACEHOLDER';
    /** After this many temporary failures since the last successful send (counted across batches, see failureStreak()) the campaign pauses: the mail server is down or throttling us. */
    private const BREAKER = 5;
    public const MAX_TEST_ADDRESSES = 5;
    public const STATES_ACTIVE = ['queued', 'sending', 'paused'];
    /** Reasons stored in newsletter_deliveries.last_error for skipped rows (the cancel reason lets "send to the remaining" pick them up again). */
    public const REASON_CANCELLED = 'Cancelled';
    public const REASON_UNSUBSCRIBED = 'Unsubscribed before sending';
    public const REASON_REMOVED = 'No longer on the list';

    /** The reason of the last configuration-level failure (shown to the administrator when a campaign pauses itself). */
    private static string $lastConfigError = '';

    /** Seconds to wait before attempt n+1 (n attempts made so far, multiplied). Tests set it to 0. */
    public static int $retryDelay = 20;

    // ------------------------------------------------------------------------------------------------ configuration

    /** Recipients per batch (setting newsletter_batch_size, 1 to 200, default 30). */
    public static function batchSize(): int
    {
        $n = (int) setting('newsletter_batch_size', '30');
        return $n < 1 ? 30 : min(200, $n);
    }

    /**
     * Sender identity of the newsletter e-mails.
     * @return array{from:string,from_name:string,reply_to:string,unsub_mailto:string}
     */
    public static function sender(): array
    {
        $from = trim((string) setting('newsletter_from_email', ''));
        if (!Mailer::validEmail($from)) {
            $from = (string) Config::get('mail.from', '');
        }
        $name = trim((string) setting('newsletter_from_name', ''));
        if ($name === '') {
            $name = trim((string) setting('site_name', ''));
        }
        $reply = trim((string) setting('newsletter_reply_to', ''));
        if (!Mailer::validEmail($reply)) {
            $reply = trim((string) setting('email_primary', ''));
        }
        $reply = Mailer::validEmail($reply) ? $reply : '';
        return ['from' => $from, 'from_name' => $name, 'reply_to' => $reply, 'unsub_mailto' => $reply];
    }

    /**
     * Is the mail transport ready? driver: file | mail | smtp. level: ok | info | warning | error ("error" blocks nothing by itself; the queue refuses to
     * start only when the sender address or the SMTP host is missing).
     * @return array{driver:string,level:string,message:string,blocking:bool}
     */
    public static function transport(): array
    {
        $driver = Mailer::driver();
        $sender = self::sender();
        if (!Mailer::validEmail($sender['from'])) {
            return ['driver' => $driver, 'level' => 'error', 'blocking' => true, 'message' => 'There is no valid sender address. Set one in Site settings > Newsletter (or MAIL_FROM in the .env file).'];
        }
        if ($driver === 'smtp') {
            try {
                Smtp::fromConfig();
            } catch (MailException $e) {
                return ['driver' => $driver, 'level' => 'error', 'blocking' => true, 'message' => $e->getMessage()];
            }
            return ['driver' => $driver, 'level' => 'ok', 'blocking' => false, 'message' => 'Sending through your SMTP mail server.'];
        }
        if ($driver === 'file') {
            return ['driver' => $driver, 'level' => 'info', 'blocking' => false, 'message' => 'Test mode: the mail driver is "file", so messages are saved in storage/mail instead of being sent. Set MAIL_DRIVER=smtp in the .env file to send for real.'];
        }
        return ['driver' => $driver, 'level' => 'warning', 'blocking' => false, 'message' => 'The mail driver is PHP mail(). Many hosts limit or filter it; for a newsletter an SMTP mailbox (MAIL_DRIVER=smtp) is much more reliable.'];
    }

    /**
     * Anything about the ISSUE that makes a mass mailing pointless or harmful: it is incomplete (an e-mailed issue can never be sent again), or the
     * Newsletter page is switched off, so the "View in browser" link in every message would lead to a 404.
     * @return array{code:string,message:string}|null
     */
    public static function contentProblem(array $issue): ?array
    {
        $ready = NewsletterContent::readiness(NewsletterContent::decode((string) ($issue['content'] ?? '')), $issue);
        if ($ready['blocking'] !== []) {
            return ['code' => 'incomplete', 'message' => 'The issue is not complete: ' . (string) reset($ready['blocking']) . ' Fix it in the editor before e-mailing it.'];
        }
        if (!SitePages::isPublished('newsletter')) {
            return ['code' => 'page_unpublished', 'message' => 'The Newsletter page is switched off (Admin > Pages), so the "View in browser" link in the e-mail would lead nowhere. Switch the page on first.'];
        }
        return null;
    }

    // ------------------------------------------------------------------------------------------------ recipients

    /** Everybody who is subscribed right now. */
    public static function subscribedCount(): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM subscribers WHERE status = 'subscribed'", [], 0);
    }

    /**
     * How many addresses a "send" started right now would e-mail for this issue: subscribed addresses with no delivery row yet, still queued,
     * or skipped only because an earlier campaign was cancelled. Sent, failed (use Retry) and unsubscribed-skipped rows never count.
     * With no campaign this equals subscribedCount().
     */
    public static function recipientCount(int $issueId): int
    {
        return (int) Database::value(
            "SELECT COUNT(*) FROM subscribers s LEFT JOIN newsletter_deliveries d ON d.newsletter_id = ? AND d.email = s.email
              WHERE s.status = 'subscribed' AND (d.id IS NULL OR d.status = 'queued' OR (d.status = 'skipped' AND d.last_error = ?))",
            [$issueId, self::REASON_CANCELLED],
            0
        );
    }

    // ------------------------------------------------------------------------------------------------ status

    /** @return array{queued:int,sent:int,failed:int,skipped:int,total:int} */
    public static function counts(int $issueId): array
    {
        $c = ['queued' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS c FROM newsletter_deliveries WHERE newsletter_id = ? GROUP BY status', [$issueId]) as $r) {
            if (isset($c[$r['status']])) {
                $c[$r['status']] = (int) $r['c'];
            }
        }
        $c['total'] = array_sum($c);
        return $c;
    }

    /**
     * Everything the e-mail panel shows, as a JSON-ready array (contract: GET /admin/api/newsletters/{id}/email).
     * @return array<string,mixed>
     */
    public static function status(array $issue): array
    {
        $id = (int) $issue['id'];
        $state = (string) ($issue['email_status'] ?? 'none');
        $c = self::counts($id);
        $sender = self::sender();
        $live = NewsletterService::isLive($issue);
        $enabled = (bool) ($issue['email_enabled'] ?? false);
        $recipients = self::recipientCount($id);
        $transport = self::transport();

        $why = null;
        if (in_array($state, self::STATES_ACTIVE, true)) {
            $why = 'The campaign is already running.';
        } elseif ($state === 'sent') {
            $why = 'This issue has already been e-mailed. An issue is never sent twice.';
        } elseif (!$live) {
            $why = 'Publish the issue first. Only a published issue can be e-mailed.';
        } elseif (!$enabled) {
            $why = 'Switch on "E-mail this issue to subscribers" and save.';
        } elseif (($block = self::contentProblem($issue)) !== null) {
            $why = $block['message'];
        } elseif ($transport['blocking']) {
            $why = $transport['message'];
        } elseif ($recipients < 1) {
            $why = $c['total'] > 0 ? 'There is nobody left to send to.' : 'There are no subscribers to send to yet.';
        }

        return [
            'id'       => $id,
            'email'    => [
                'enabled'         => $enabled,
                'subject'         => (string) ($issue['email_subject'] ?? ''),
                'preheader'       => (string) ($issue['email_preheader'] ?? ''),
                'default_subject' => NewsletterService::defaultSubject($issue),
                'subject_used'    => NewsletterRenderer::subject($issue),
                'status'          => $state,
                'status_label'    => NewsletterService::EMAIL_LABELS[$state] ?? $state,
                'total'           => $c['total'],
                'sent'            => $c['sent'],
                'failed'          => $c['failed'],
                'queued'          => $c['queued'],
                'skipped'         => $c['skipped'],
                'started_at'      => $issue['email_started_at'] ?? null,
                'completed_at'    => $issue['email_completed_at'] ?? null,
            ],
            'recipients' => $recipients,
            'subscribed' => self::subscribedCount(),
            'live'       => $live,
            'driver'     => $transport['driver'],
            'transport'  => $transport,
            'sender'     => ['from' => $sender['from'], 'from_name' => $sender['from_name'], 'reply_to' => $sender['reply_to']],
            'batch_size' => self::batchSize(),
            'can_start'  => $why === null,
            'cannot_start_reason' => $why,
            'can_edit'   => !in_array($state, self::STATES_ACTIVE, true) && $state !== 'sent',
        ];
    }

    // ------------------------------------------------------------------------------------------------ e-mail settings of an issue

    /**
     * Save subject / preheader / "e-mail this issue" switch. Only the keys present change. Locked while a campaign runs and after it is sent.
     * @return array{ok:bool,code?:string,message?:string,errors?:array<string,string>,newsletter?:array,changed?:list<string>}
     */
    public static function saveSettings(int $id, array $input): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That issue no longer exists.'];
        }
        $state = (string) $row['email_status'];
        if (in_array($state, self::STATES_ACTIVE, true)) {
            return ['ok' => false, 'code' => 'conflict', 'message' => 'The campaign is running. Pause or cancel it before changing the e-mail settings.'];
        }
        if ($state === 'sent') {
            return ['ok' => false, 'code' => 'conflict', 'message' => 'This issue has already been e-mailed, so its e-mail settings are locked.'];
        }
        $set = [];
        $errors = [];
        if (array_key_exists('email_enabled', $input)) {
            $v = $input['email_enabled'];
            $set['email_enabled'] = ($v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'on') ? 1 : 0;
        }
        if (array_key_exists('email_subject', $input)) {
            $v = self::oneLine($input['email_subject']);
            if ($v === null) {
                $errors['email_subject'] = 'The subject must be plain text.';
            } elseif (mb_strlen($v) > 190) {
                $errors['email_subject'] = 'Keep the subject under 190 characters (about 60 show up in most inboxes).';
            } else {
                $set['email_subject'] = $v === '' ? null : $v;
            }
        }
        if (array_key_exists('email_preheader', $input)) {
            $v = self::oneLine($input['email_preheader']);
            if ($v === null) {
                $errors['email_preheader'] = 'The preview line must be plain text.';
            } elseif (mb_strlen($v) > 150) {
                $errors['email_preheader'] = 'Keep the preview line under 150 characters.';
            } else {
                $set['email_preheader'] = $v === '' ? null : $v;
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'code' => 'validation', 'message' => (string) reset($errors), 'errors' => $errors];
        }
        $changed = [];
        foreach ($set as $k => $v) {
            if ((string) ($row[$k] ?? '') !== (string) ($v ?? '')) {
                $changed[] = $k;
            }
        }
        if ($changed !== []) {
            $set['updated_at'] = $row['updated_at'];          // campaign settings are not an edit of the issue
            Database::update('newsletters', $set, 'id = ?', [$id]);
        }
        return ['ok' => true, 'newsletter' => Newsletter::find($id) ?? $row, 'changed' => $changed];
    }

    /** One line of plain text: tags removed, control characters and line breaks collapsed. null when it is not a string. */
    private static function oneLine(mixed $v): ?string
    {
        if ($v === null) {
            return '';
        }
        if (!is_scalar($v)) {
            return null;
        }
        $s = strip_tags((string) $v);
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s);
        $s = (string) preg_replace('/\s+/u', ' ', $s);
        return trim(mb_scrub($s, 'UTF-8'));
    }

    // ------------------------------------------------------------------------------------------------ messages

    /**
     * Render the e-mail ONCE for an issue: subject, HTML and text with the per-recipient unsubscribe URL left as a marker.
     * @return array{subject:string,html:string,text:string}
     */
    public static function template(array $issue, bool $test = false, bool $withUnsubscribe = true): array
    {
        $subject = NewsletterRenderer::subject($issue);
        $opts = [
            'subject'         => $test ? '[TEST] ' . $subject : $subject,
            'preheader'       => NewsletterRenderer::preheader($issue),
            'browser_url'     => NewsletterService::url($issue, true),
            'unsubscribe_url' => $withUnsubscribe ? self::UNSUB_PLACEHOLDER : '',
            'is_test'         => $test,
        ];
        return [
            'subject' => $opts['subject'],
            'html'    => NewsletterRenderer::renderIssue($issue, 'email', $opts),
            'text'    => NewsletterRenderer::emailText($issue, $opts),
        ];
    }

    /** The unsubscribe URL of one subscriber. */
    public static function unsubscribeUrl(string $token): string
    {
        return abs_url('unsubscribe/' . $token);
    }

    /**
     * Send one prepared message to one address.
     * @param array{subject:string,html:string,text:string} $tpl
     * @return array{ok:bool,error:?string,transient:bool}
     */
    private static function deliver(array $tpl, string $to, ?string $token): array
    {
        $sender = self::sender();
        $html = $tpl['html'];
        $text = $tpl['text'];
        $headers = [];
        if ($token !== null) {
            $url = self::unsubscribeUrl($token);
            $html = str_replace(self::UNSUB_PLACEHOLDER, $url, $html);
            $text = str_replace(self::UNSUB_PLACEHOLDER, $url, $text);
            $links = '<' . $url . '>';
            if ($sender['unsub_mailto'] !== '') {
                $links .= ', <mailto:' . $sender['unsub_mailto'] . '?subject=Unsubscribe>';
            }
            $headers = ['List-Unsubscribe' => $links, 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'];
        }
        $opts = ['html' => $html, 'from' => $sender['from'], 'from_name' => $sender['from_name'], 'headers' => $headers];
        if ($sender['reply_to'] !== '') {
            $opts['reply_to'] = $sender['reply_to'];
        }
        return Mailer::attempt($to, $tpl['subject'], $text, $opts);
    }

    /**
     * What the e-mail looks like (for the panel's preview): HTML with a harmless sample unsubscribe link.
     * @return array{subject:string,preheader:string,html:string,text:string,bytes:int}
     */
    public static function preview(array $issue): array
    {
        $tpl = self::template($issue, false, true);
        $sample = abs_url('unsubscribe');
        $html = str_replace(self::UNSUB_PLACEHOLDER, $sample, $tpl['html']);
        $text = str_replace(self::UNSUB_PLACEHOLDER, $sample, $tpl['text']);
        return [
            'subject'   => $tpl['subject'],
            'preheader' => NewsletterRenderer::preheader($issue),
            'html'      => $html,
            'text'      => $text,
            'bytes'     => strlen($html),
        ];
    }

    // ------------------------------------------------------------------------------------------------ test sends

    /**
     * Parse "a@x.org, b@y.org\nc@z.org" into at most MAX_TEST_ADDRESSES distinct valid addresses.
     * @param string|array<int,mixed> $input
     * @return array{emails:list<string>,invalid:int,too_many:bool}
     */
    public static function parseAddresses(string|array $input): array
    {
        $parts = is_array($input) ? $input : (preg_split('/[\s,;]+/', $input) ?: []);
        $emails = [];
        $invalid = 0;
        foreach ($parts as $p) {
            $p = is_string($p) ? trim($p) : '';
            if ($p === '') {
                continue;
            }
            if (!Mailer::validEmail($p) || strlen($p) > 190) {
                $invalid++;
                continue;
            }
            $emails[strtolower($p)] = $p;
        }
        $list = array_values($emails);
        return ['emails' => array_slice($list, 0, self::MAX_TEST_ADDRESSES), 'invalid' => $invalid, 'too_many' => count($list) > self::MAX_TEST_ADDRESSES];
    }

    /**
     * Send the issue as a TEST to up to five addresses: subject "[TEST] ...", a notice in the mail, never recorded in the deliveries, the campaign
     * is not touched. Works for any status (that is the point: look at a draft). An address that happens to be a subscriber gets its real
     * unsubscribe link; any other address gets none. $userId only keys the rate limit.
     *
     * @param list<string> $emails
     * @return list<array{email:string,ok:bool,error:?string}>
     */
    public static function sendTest(array $issue, array $emails, ?int $userId = null): array
    {
        $emails = array_slice(array_values($emails), 0, self::MAX_TEST_ADDRESSES);
        $withLink = self::template($issue, true, true);
        $withoutLink = null;
        $out = [];
        try {
            foreach ($emails as $to) {
                if (!RateLimit::attempt('nl-test-addr:' . ($userId ?? 0), 25, 3600)) {
                    $out[] = ['email' => $to, 'ok' => false, 'error' => 'Too many test e-mails in the last hour. Please wait a while.'];
                    continue;
                }
                $token = self::tokenForSubscribed($to);
                if ($token === null && $withoutLink === null) {
                    $withoutLink = self::template($issue, true, false);
                }
                $res = self::deliver($token !== null ? $withLink : (array) $withoutLink, $to, $token);
                $out[] = ['email' => $to, 'ok' => $res['ok'], 'error' => $res['ok'] ? null : self::safeError((string) $res['error'])];
            }
        } finally {
            Mailer::closeConnection();
        }
        return $out;
    }

    private static function tokenForSubscribed(string $email): ?string
    {
        $t = Database::value("SELECT token FROM subscribers WHERE email = ? AND status = 'subscribed'", [strtolower($email)]);
        return is_string($t) && preg_match('/^[a-f0-9]{40}$/', $t) === 1 ? $t : null;
    }

    // ------------------------------------------------------------------------------------------------ queue

    /**
     * Start a campaign: snapshot the subscribed addresses into newsletter_deliveries and move the issue to "queued".
     * $confirmCount must equal the number of addresses that will be e-mailed (recipientCount); a mismatch changes nothing.
     *
     * @return array{ok:bool,code?:string,message?:string,recipients?:int,newsletter?:array}
     *   codes: not_found | conflict (wrong state / already started) | not_live | not_enabled | transport | no_recipients | count_changed
     */
    public static function queue(int $id, int $confirmCount): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That issue no longer exists.'];
        }
        $state = (string) $row['email_status'];
        if (!in_array($state, ['none', 'cancelled'], true)) {
            return ['ok' => false, 'code' => 'conflict', 'message' => $state === 'sent' ? 'This issue has already been e-mailed. An issue is never sent twice.' : 'The campaign has already been started.'];
        }
        if (!NewsletterService::isLive($row)) {
            return ['ok' => false, 'code' => 'not_live', 'message' => 'Publish the issue first. Only a published issue can be e-mailed.'];
        }
        if (empty($row['email_enabled'])) {
            return ['ok' => false, 'code' => 'not_enabled', 'message' => 'Switch on "E-mail this issue to subscribers" and save before sending.'];
        }
        if (($problem = self::contentProblem($row)) !== null) {
            return ['ok' => false, 'code' => $problem['code'], 'message' => $problem['message']];
        }
        $transport = self::transport();
        if ($transport['blocking']) {
            return ['ok' => false, 'code' => 'transport', 'message' => $transport['message']];
        }
        $recipients = self::recipientCount($id);
        if ($recipients < 1) {
            return ['ok' => false, 'code' => 'no_recipients', 'message' => 'There is nobody to send to.', 'recipients' => 0];
        }
        if ($confirmCount !== $recipients) {
            return ['ok' => false, 'code' => 'count_changed', 'message' => 'The number of subscribers changed while you were deciding. It is now ' . $recipients . '. Please confirm again.', 'recipients' => $recipients];
        }

        $result = null;
        try {
            $result = Database::transaction(static function () use ($id, $state, $confirmCount): array {
                [$visible, $visibleParams] = NewsletterService::visibleSql('');
                // the atomic claim: only ONE request can move none/cancelled -> queued
                $claimed = Database::execute(
                    "UPDATE newsletters SET email_status = 'queued', email_started_at = NULL, email_completed_at = NULL, updated_at = updated_at
                      WHERE id = ? AND email_status IN ('none', 'cancelled') AND email_enabled = 1 AND {$visible}",
                    array_merge([$id], $visibleParams)
                );
                if ($claimed !== 1) {
                    return ['ok' => false, 'code' => 'conflict', 'message' => 'The campaign has already been started.'];
                }
                Database::execute(
                    "INSERT IGNORE INTO newsletter_deliveries (newsletter_id, subscriber_id, email, status)
                     SELECT ?, id, email, 'queued' FROM subscribers WHERE status = 'subscribed'",
                    [$id]
                );
                if ($state === 'cancelled') {
                    Database::execute(
                        "UPDATE newsletter_deliveries d JOIN subscribers s ON s.id = d.subscriber_id
                            SET d.status = 'queued', d.attempts = 0, d.last_error = NULL
                          WHERE d.newsletter_id = ? AND d.status = 'skipped' AND d.last_error = ? AND s.status = 'subscribed'",
                        [$id, self::REASON_CANCELLED]
                    );
                }
                $queued = (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'queued'", [$id], 0);
                if ($queued !== $confirmCount) {
                    throw new \RuntimeException('count_changed:' . $queued);
                }
                self::syncCounters($id);
                return ['ok' => true, 'recipients' => $queued];
            });
        } catch (\RuntimeException $e) {
            if (str_starts_with($e->getMessage(), 'count_changed:')) {
                $now = (int) substr($e->getMessage(), 14);
                return ['ok' => false, 'code' => 'count_changed', 'message' => 'The number of subscribers changed while you were deciding. It is now ' . $now . '. Please confirm again.', 'recipients' => $now];
            }
            throw $e;
        }
        if (!$result['ok']) {
            return $result;
        }
        return $result + ['newsletter' => Newsletter::find($id) ?? $row];
    }

    // ------------------------------------------------------------------------------------------------ campaign control

    /** queued / sending -> paused. @return array{ok:bool,code?:string,message?:string,newsletter?:array} */
    public static function pause(int $id): array
    {
        $n = Database::execute("UPDATE newsletters SET email_status = 'paused', updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')", [$id]);
        return self::controlResult($id, $n, 'Only a running campaign can be paused.');
    }

    /** paused -> sending (or queued when no batch has run yet). */
    public static function resume(int $id): array
    {
        $row = Newsletter::find($id);
        if ($row !== null && !NewsletterService::isLive($row) && $row['email_status'] === 'paused') {
            return ['ok' => false, 'code' => 'not_live', 'message' => 'The issue is not published any more. Publish it again before resuming.'];
        }
        $n = Database::execute(
            "UPDATE newsletters SET email_status = IF(email_started_at IS NULL, 'queued', 'sending'), updated_at = updated_at WHERE id = ? AND email_status = 'paused'",
            [$id]
        );
        return self::controlResult($id, $n, 'Only a paused campaign can be resumed.');
    }

    /** queued / sending / paused -> cancelled: nothing more is sent; waiting addresses are marked skipped, sent ones stay. */
    public static function cancel(int $id): array
    {
        $n = Database::transaction(static function () use ($id): int {
            $changed = Database::execute(
                "UPDATE newsletters SET email_status = 'cancelled', email_completed_at = ?, updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending', 'paused')",
                [NewsletterService::now(), $id]
            );
            if ($changed === 1) {
                Database::execute(
                    "UPDATE newsletter_deliveries SET status = 'skipped', last_error = ? WHERE newsletter_id = ? AND status = 'queued'",
                    [self::REASON_CANCELLED, $id]
                );
                self::syncCounters($id);
            }
            return $changed;
        });
        return self::controlResult($id, (int) $n, 'Only a running or paused campaign can be cancelled.');
    }

    /**
     * Failed deliveries go back to the queue (attempts reset). Allowed after the campaign is sent, or while it is paused / sending.
     * @return array{ok:bool,code?:string,message?:string,requeued?:int,newsletter?:array}
     */
    public static function retryFailed(int $id): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That issue no longer exists.'];
        }
        $state = (string) $row['email_status'];
        if (!in_array($state, ['sent', 'paused', 'sending', 'queued'], true)) {
            return ['ok' => false, 'code' => 'conflict', 'message' => 'There is no campaign to retry.'];
        }
        if (!NewsletterService::isLive($row)) {
            return ['ok' => false, 'code' => 'not_live', 'message' => 'The issue is not published any more. Publish it again before retrying.'];
        }
        $requeued = Database::transaction(static function () use ($id, $state): int {
            $n = Database::execute("UPDATE newsletter_deliveries SET status = 'queued', attempts = 0, last_error = NULL WHERE newsletter_id = ? AND status = 'failed'", [$id]);
            if ($n > 0 && $state === 'sent') {
                Database::execute("UPDATE newsletters SET email_status = 'sending', email_completed_at = NULL, updated_at = updated_at WHERE id = ? AND email_status = 'sent'", [$id]);
            }
            if ($n > 0) {
                self::syncCounters($id);
            }
            return $n;
        });
        if ((int) $requeued < 1) {
            return ['ok' => false, 'code' => 'nothing', 'message' => 'There are no failed e-mails to retry.'];
        }
        return ['ok' => true, 'requeued' => (int) $requeued, 'newsletter' => Newsletter::find($id) ?? $row];
    }

    /** @return array{ok:bool,code?:string,message?:string,newsletter?:array} */
    private static function controlResult(int $id, int $changed, string $wrongState): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That issue no longer exists.'];
        }
        if ($changed < 1) {
            return ['ok' => false, 'code' => 'conflict', 'message' => $wrongState, 'newsletter' => $row];
        }
        return ['ok' => true, 'newsletter' => $row];
    }

    // ------------------------------------------------------------------------------------------------ batch sender

    private static function lockName(): string
    {
        return 'lumina_newsletter_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8);
    }

    private static function lock(): bool
    {
        return (int) Database::value('SELECT GET_LOCK(?, 0)', [self::lockName()], 0) === 1;
    }

    private static function unlock(): void
    {
        try {
            Database::value('SELECT RELEASE_LOCK(?)', [self::lockName()]);
        } catch (\Throwable) {
            // the lock disappears with the connection anyway
        }
    }

    /**
     * Send the next batch of one campaign. Safe to call from several places at once: only one caller sends, the others get busy=true.
     *
     * $opts: limit (recipients, default the batch-size setting), max_seconds (stop taking recipients after this long, default 20).
     * Result: ok, busy, halted (null | not_live | mail_server), halt_reason (the mail server problem, when halted = mail_server), idle (nothing to do in this state), processed, sent, failed, skipped, retried,
     * remaining (still queued), status, retry_in (seconds until waiting retries are due), done (campaign finished), totals{queued,sent,failed,skipped,total}.
     *
     * @return array<string,mixed>
     */
    public static function processBatch(int $id, array $opts = []): array
    {
        $row = Newsletter::find($id);
        if ($row === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That issue no longer exists.'];
        }
        if (!in_array($row['email_status'], ['queued', 'sending'], true)) {
            return self::batchResult($row, ['idle' => true]);
        }
        if (!self::lock()) {
            return self::batchResult($row, ['busy' => true]);
        }
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'retried' => 0, 'halted' => null];
        $startedAt = microtime(true);
        try {
            $maxSeconds = max(1, (int) ($opts['max_seconds'] ?? 20));
            $limit = max(1, min(200, (int) ($opts['limit'] ?? self::batchSize())));
            if (function_exists('set_time_limit')) {
                @set_time_limit($maxSeconds + 60);       // best effort; a slow mail server must not kill the request half-way
            }
            @ignore_user_abort(true);                    // a closed browser tab must not leave a message half-recorded

            $row = Newsletter::find($id) ?? $row;        // state may have changed while we waited for the lock
            if (!in_array($row['email_status'], ['queued', 'sending'], true)) {
                return self::batchResult($row, ['idle' => true]);
            }
            if (!NewsletterService::isLive($row)) {
                Database::execute("UPDATE newsletters SET email_status = 'paused', updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')", [$id]);
                return self::batchResult(Newsletter::find($id) ?? $row, ['halted' => 'not_live']);
            }
            // first batch: queued -> sending
            Database::execute(
                "UPDATE newsletters SET email_status = 'sending', email_started_at = COALESCE(email_started_at, ?), updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')",
                [NewsletterService::now(), $id]
            );

            $todo = Database::all(
                "SELECT id, email, subscriber_id, attempts FROM newsletter_deliveries
                  WHERE newsletter_id = ? AND status = 'queued' AND (attempts = 0 OR updated_at <= DATE_SUB(NOW(), INTERVAL (? * attempts) SECOND))
                  ORDER BY attempts ASC, id ASC LIMIT ?",
                [$id, max(0, self::$retryDelay), $limit]
            );
            if ($todo !== []) {
                $row = Newsletter::find($id) ?? $row;
                $tpl = self::template($row, false, true);
                foreach ($todo as $d) {
                    if (microtime(true) - $startedAt > $maxSeconds) {
                        break;
                    }
                    $live = (string) Database::value('SELECT email_status FROM newsletters WHERE id = ?', [$id], '');
                    if (!in_array($live, ['queued', 'sending'], true)) {
                        break;                          // paused or cancelled by an admin while we were sending
                    }
                    $outcome = self::sendOne($id, $d, $tpl);
                    if ($outcome === null) {
                        continue;                       // already taken by something else
                    }
                    if ($outcome === 'config') {
                        // the set-up of the mail server is wrong (login, TLS, sender, size limit): it would fail for EVERY address, so stop
                        // here with the recipient still queued and let an administrator fix the .env and resume
                        Database::execute("UPDATE newsletters SET email_status = 'paused', updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')", [$id]);
                        $stats['halted'] = 'mail_server';
                        $stats['halt_reason'] = self::$lastConfigError;
                        Log::warning('Newsletter: campaign paused because of a mail server configuration problem', ['newsletter' => $id]);
                        break;
                    }
                    $stats['processed']++;
                    $stats[$outcome]++;
                    if ($outcome === 'retried' && self::failureStreak($id) >= self::BREAKER) {
                        Database::execute("UPDATE newsletters SET email_status = 'paused', updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')", [$id]);
                        $stats['halted'] = 'mail_server';
                        Log::warning('Newsletter: campaign paused after repeated temporary failures', ['newsletter' => $id]);
                        break;
                    }
                }
            }
            return self::finish($id, $stats);
        } finally {
            Mailer::closeConnection();
            self::unlock();
        }
    }

    /**
     * One recipient. Returns the outcome key (sent | failed | skipped | retried) or null when the row was not claimable.
     * @param array{id:int|string,email:string,subscriber_id:int|string|null,attempts:int|string} $d
     * @param array{subject:string,html:string,text:string} $tpl
     */
    private static function sendOne(int $issueId, array $d, array $tpl): ?string
    {
        $rowId = (int) $d['id'];
        // claim: attempts + 1, only while still queued (a concurrent cancel or pause cannot be overtaken)
        if (Database::execute("UPDATE newsletter_deliveries SET attempts = attempts + 1 WHERE id = ? AND status = 'queued'", [$rowId]) !== 1) {
            return null;
        }
        $attempts = (int) $d['attempts'] + 1;

        // the subscriber must still be subscribed RIGHT NOW
        $sub = null;
        if ($d['subscriber_id'] !== null) {
            $sub = Database::one('SELECT id, status, token FROM subscribers WHERE id = ?', [(int) $d['subscriber_id']]);
        }
        if ($sub === null) {
            $sub = Database::one('SELECT id, status, token FROM subscribers WHERE email = ?', [(string) $d['email']]);
        }
        if ($sub === null) {
            self::mark($rowId, 'skipped', self::REASON_REMOVED);
            return 'skipped';
        }
        if ($sub['status'] !== 'subscribed') {
            self::mark($rowId, 'skipped', self::REASON_UNSUBSCRIBED);
            return 'skipped';
        }
        if (preg_match('/^[a-f0-9]{40}$/', (string) $sub['token']) !== 1) {
            self::mark($rowId, 'failed', 'This subscriber has no unsubscribe token, so no compliant e-mail can be sent.');
            return 'failed';
        }

        try {
            $res = self::deliver($tpl, (string) $d['email'], (string) $sub['token']);
        } catch (\Throwable $e) {
            Log::error('Newsletter: unexpected error while sending - ' . self::safeError($e->getMessage()), ['newsletter' => $issueId, 'delivery' => $rowId]);
            $res = ['ok' => false, 'error' => 'Unexpected error while sending.', 'transient' => true];
        }
        if ($res['ok']) {
            Database::execute("UPDATE newsletter_deliveries SET status = 'sent', sent_at = ?, last_error = NULL WHERE id = ? AND status IN ('queued', 'skipped')", [NewsletterService::now(), $rowId]);
            return 'sent';
        }
        $error = self::safeError((string) $res['error']);
        if (!empty($res['config'])) {
            // not this recipient's fault: give the attempt back and keep the address queued
            self::$lastConfigError = $error;
            Database::execute("UPDATE newsletter_deliveries SET attempts = GREATEST(attempts - 1, 0), last_error = ? WHERE id = ? AND status = 'queued'", [mb_substr($error, 0, 255), $rowId]);
            return 'config';
        }
        if (!$res['transient'] || $attempts >= NewsletterDelivery::MAX_ATTEMPTS) {
            self::mark($rowId, 'failed', $error);
            return 'failed';
        }
        Database::execute("UPDATE newsletter_deliveries SET last_error = ? WHERE id = ? AND status = 'queued'", [$error, $rowId]);     // stays queued: tried again later
        return 'retried';
    }

    /**
     * How many addresses of this campaign failed temporarily since the last successful send (counted from the table, so it works across batches,
     * browser pages, the cron job and small batch sizes). Any success starts the count again.
     */
    private static function failureStreak(int $id): int
    {
        $since = Database::value("SELECT MAX(sent_at) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'sent'", [$id]);
        return (int) Database::value(
            "SELECT COUNT(*) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'queued' AND attempts > 0 AND updated_at > ?",
            [$id, is_string($since) && $since !== '' ? $since : '1970-01-01 00:00:00'],
            0
        );
    }

    /** Final state of a row while it is still queued (a row an admin cancelled meanwhile keeps its cancelled state). */
    private static function mark(int $rowId, string $status, string $error): void
    {
        Database::execute("UPDATE newsletter_deliveries SET status = ?, last_error = ? WHERE id = ? AND status = 'queued'", [$status, mb_substr($error, 0, 255), $rowId]);
    }

    /** A short, address-free, credential-free reason. */
    public static function safeError(string $message): string
    {
        $m = (string) preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+/', '[address]', $message);
        $m = (string) preg_replace('/\s+/', ' ', strip_tags($m));
        return mb_substr(trim(mb_scrub($m, 'UTF-8')), 0, 250);
    }

    /** Recompute the denormalised counters of the issue from the delivery rows. @return array{queued:int,sent:int,failed:int,skipped:int,total:int} */
    public static function syncCounters(int $id): array
    {
        $c = self::counts($id);
        Database::execute(
            'UPDATE newsletters SET email_total = ?, email_sent = ?, email_failed = ?, updated_at = updated_at WHERE id = ?',
            [$c['total'], $c['sent'], $c['failed'], $id]
        );
        return $c;
    }

    /** End of a batch: counters, completion when nothing is queued any more, the result array. */
    private static function finish(int $id, array $stats): array
    {
        $c = self::syncCounters($id);
        $done = false;
        if ($c['queued'] === 0) {
            $n = Database::execute(
                "UPDATE newsletters SET email_status = 'sent', email_completed_at = ?, updated_at = updated_at WHERE id = ? AND email_status IN ('queued', 'sending')",
                [NewsletterService::now(), $id]
            );
            if ($n === 1) {
                $done = true;
                AuditLog::record('newsletter.email_complete', 'newsletter', $id, 'Newsletter e-mail campaign finished', ['sent' => $c['sent'], 'failed' => $c['failed'], 'skipped' => $c['skipped']]);
                Log::info('Newsletter: campaign finished', ['newsletter' => $id, 'sent' => $c['sent'], 'failed' => $c['failed'], 'skipped' => $c['skipped']]);
            }
        }
        $row = Newsletter::find($id) ?? [];
        return self::batchResult($row, $stats + ['done' => $done], $c);
    }

    /**
     * @param array<string,mixed> $extra
     * @param array{queued:int,sent:int,failed:int,skipped:int,total:int}|null $c
     * @return array<string,mixed>
     */
    private static function batchResult(array $row, array $extra = [], ?array $c = null): array
    {
        $id = (int) ($row['id'] ?? 0);
        $c ??= self::counts($id);
        $retryIn = null;
        if ($c['queued'] > 0 && in_array($row['email_status'] ?? '', ['queued', 'sending'], true)) {
            $wait = Database::value(
                "SELECT MIN(GREATEST(0, CAST(? * attempts AS SIGNED) - TIMESTAMPDIFF(SECOND, updated_at, NOW()))) FROM newsletter_deliveries WHERE newsletter_id = ? AND status = 'queued'",
                [max(0, self::$retryDelay), $id]
            );
            $retryIn = $wait !== null && (int) $wait > 0 ? min(60, (int) $wait) : null;
        }
        return $extra + [
            'ok'        => true,
            'busy'      => false,
            'idle'      => false,
            'halted'    => null,
            'done'      => false,
            'processed' => 0,
            'sent'      => 0,
            'failed'    => 0,
            'skipped'   => 0,
            'retried'   => 0,
        ] + [
            'remaining' => $c['queued'],
            'status'    => (string) ($row['email_status'] ?? 'none'),
            'retry_in'  => $retryIn,
            'totals'    => $c,
        ];
    }

    // ------------------------------------------------------------------------------------------------ cron

    /**
     * Continue every campaign that is queued or sending (never paused or cancelled), batch after batch, within a time budget.
     * Returns a summary for the cron script. Uses the same lock as the admin loop, so running both at once is harmless.
     *
     * @return array{campaigns:int,processed:int,sent:int,failed:int,skipped:int,busy:bool,out_of_time:bool}
     */
    public static function runDue(int $maxSeconds = 240): array
    {
        $deadline = microtime(true) + max(5, $maxSeconds);
        $sum = ['campaigns' => 0, 'processed' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'busy' => false, 'out_of_time' => false];
        foreach (Database::all("SELECT id FROM newsletters WHERE email_status IN ('queued', 'sending') ORDER BY COALESCE(email_started_at, created_at), id") as $r) {
            $sum['campaigns']++;
            while (true) {
                $left = $deadline - microtime(true);
                if ($left < 3) {
                    $sum['out_of_time'] = true;
                    return $sum;
                }
                $res = self::processBatch((int) $r['id'], ['max_seconds' => (int) min(25, $left - 1)]);
                if (!$res['ok']) {
                    break;
                }
                if ($res['busy']) {
                    $sum['busy'] = true;
                    return $sum;
                }
                foreach (['processed', 'sent', 'failed', 'skipped'] as $k) {
                    $sum[$k] += (int) $res[$k];
                }
                if ($res['done'] || $res['idle'] || $res['halted'] !== null || !in_array($res['status'], ['queued', 'sending'], true)) {
                    break;
                }
                if ((int) $res['processed'] === 0) {
                    // everything left is a retry that is not due yet: look again on the next run
                    break;
                }
            }
        }
        return $sum;
    }

    // ------------------------------------------------------------------------------------------------ results table

    /**
     * Per-recipient results (admin only).
     * @param array{status?:string,q?:string} $filters
     * @return array{data:list<array<string,mixed>>,meta:array<string,mixed>}
     */
    public static function deliveries(int $id, array $filters, int $page, int $perPage): array
    {
        $where = 'newsletter_id = ?';
        $params = [$id];
        $status = (string) ($filters['status'] ?? '');
        if (in_array($status, NewsletterDelivery::STATUSES, true)) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where .= ' AND email LIKE ?';
            $params[] = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM newsletter_deliveries WHERE {$where}", $params, 0);
        $perPage = max(5, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $rows = Database::all(
            "SELECT id, email, status, attempts, last_error, sent_at FROM newsletter_deliveries WHERE {$where} ORDER BY id ASC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, ($page - 1) * $perPage])
        );
        $data = array_map(static fn(array $r): array => [
            'id'         => (int) $r['id'],
            'email'      => (string) $r['email'],
            'status'     => (string) $r['status'],
            'attempts'   => (int) $r['attempts'],
            'last_error' => (string) ($r['last_error'] ?? ''),
            'sent_at'    => $r['sent_at'],
        ], $rows);
        $counts = self::counts($id);
        return ['data' => $data, 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'pages' => $pages, 'counts' => $counts + ['all' => $counts['total']]]];
    }
}
