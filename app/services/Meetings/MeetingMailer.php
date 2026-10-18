<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Services\Newsletter\NewsletterMailer;

/**
 * Sending for the meeting scheduler: the delivery queue (invitations, updates, cancellations), the batch sender, pause / resume / retry, the test invitation,
 * the free-text message, "send reminder now" and the due reminders of the cron job. OWNER: MAIL agent. Contract: docs/MEETINGS.md sections 7, 10 and 12.3.
 * Used by Admin\MeetingMailController (admin AJAX loop) and bin/meetings-send.php (cron).
 *
 * WHAT THIS CLASS DOES NOT DO: it never decides a meeting's rules. State lives in MeetingService (queue scopes, claim / markSent / markFailed, reminder marks,
 * pause flag) and every word / MIME decision lives in MeetingRenderer + Mailer. This class adds the loop, the lock, the retry / breaker policy and the JSON-ready results.
 *
 * GUARANTEES
 *  - ONE message per recipient (To: only that person); the organiser's address is only ever Reply-To (MeetingRenderer builds the options).
 *  - Nobody is mailed twice by accident: UNIQUE (meeting, address); the queue scopes only pick people who still need the message; the named database lock lets
 *    one sender work at a time (browser loop, second administrator, cron); MeetingService::claim() is a compare-and-swap on `attempts`; the row is marked
 *    sent right after the mail server accepted the message; reminders are claimed (atomic UPDATE) BEFORE they are sent (at most once).
 *  - The row is re-read AFTER the claim and the meeting is re-read before every message, so the SEQUENCE recorded is the SEQUENCE of the content that was sent:
 *    a change made while a batch runs is picked up by the next "send the updated invitation" and never sent twice.
 *  - At most MeetingAttendee::MAX_ATTEMPTS (3) attempts per message, with a wait of $retryDelay seconds per attempt already made. A permanent refusal (550)
 *    fails only that person; BREAKER temporary failures in a row (the mail server is down or throttling) pause the meeting's queue; a configuration-level
 *    failure (wrong login, TLS, sender refused, message too large) stops at the FIRST message with nobody marked failed.
 *  - Nothing sensitive is stored or logged: last_error is a short reason without credentials or addresses, audit entries and the log carry ids and counts only.
 * KNOWN LIMIT: if PHP dies after the mail server accepted a message but before the row is marked sent, that one person is tried again (a duplicate is possible).
 *   A reminder claimed but never sent (a crash in between) is not retried: a missed reminder is preferred to a double one.
 */
final class MeetingMailer
{
    /** Scopes a caller may ask for (the first five are the queue scopes of the contract, `one` = resend to a single person). */
    public const SCOPES = ['invite_new', 'invite_pending', 'update_all', 'cancel_all', 'retry_failed', 'one'];
    /** Message kinds the "send me a test" button may use. */
    public const TEST_KINDS = ['invite', 'update', 'cancel', 'reminder_24h', 'reminder_1h'];
    /** Temporary failures in a row (counted across batches, pages and the cron job) before the queue pauses itself. */
    public const BREAKER = 5;
    /** Attendee ids per free-text message request. */
    public const MESSAGE_CHUNK = 30;
    public const MAX_MESSAGE_CHARS = 8000;
    public const MAX_SUBJECT_CHARS = 150;
    /** A queued row that used up its attempts and was not finished for this long (PHP died while sending) is marked failed. */
    private const STUCK_SECONDS = 300;

    /** Seconds to wait before attempt n+1 (multiplied by the attempts already made). CLI tests set it to 0. */
    public static int $retryDelay = 20;

    private static string $lastConfigError = '';

    // ------------------------------------------------------------------------------------------------ configuration

    /** Recipients per batch (the newsletter setting `newsletter_batch_size` is shared: one knob for "how many e-mails per request"). */
    public static function batchSize(): int
    {
        $n = (int) setting('newsletter_batch_size', '30');
        return $n < 1 ? 30 : min(200, $n);
    }

    /**
     * Who the messages come from: the configured sender ADDRESS (never the organiser's), the display name "Organiser via Site" and the reply-to (the organiser).
     * @return array{from:string,from_name:string,reply_to:string}
     */
    public static function sender(?array $meeting = null): array
    {
        $s = NewsletterMailer::sender();
        $site = trim((string) setting('site_name', ''));
        $site = $site !== '' ? $site : $s['from_name'];
        return [
            'from'      => $s['from'],
            'from_name' => $meeting !== null ? MeetingRenderer::fromName((string) $meeting['organiser_name'], $site) : $site,
            'reply_to'  => $meeting !== null ? (string) $meeting['organiser_email'] : '',
        ];
    }

    /** @return array{driver:string,level:string,message:string,blocking:bool} */
    public static function transport(): array
    {
        return NewsletterMailer::transport();
    }

    /** A short, address-free, credential-free reason. */
    public static function safeError(string $message): string
    {
        $m = (string) preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+/', '[address]', $message);
        $m = (string) preg_replace('/\s+/', ' ', strip_tags($m));
        return mb_substr(trim(mb_scrub($m, 'UTF-8')), 0, 250);
    }

    // ------------------------------------------------------------------------------------------------ the named database lock

    private static function lockName(): string
    {
        return 'lumina_meeting_sender_' . substr(md5((string) Config::get('db.name', '') . '@' . (string) Config::get('db.host', '')), 0, 8);
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

    private static function streakKey(int $meetingId): string
    {
        return 'meeting-mail-streak:' . $meetingId;
    }

    private const REMINDER_STREAK_KEY = 'meeting-mail-streak:reminders';

    // ------------------------------------------------------------------------------------------------ what a scope would send (the confirm dialog)

    /** Attendees still waiting for a message (queued), removed ones included: a removed person can have a cancellation waiting. */
    public static function waiting(int $meetingId): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND invite_status = 'queued' AND attempts < ?", [$meetingId, MeetingAttendee::MAX_ATTEMPTS], 0);
    }

    /**
     * How many people each scope would queue RIGHT NOW (the numbers of the confirm dialogs; send() refuses a confirm_count that differs). Each query repeats the
     * WHERE of MeetingService::queue() for its scope, except `cancel_all` (nobody who already holds the cancellation) and `retry_failed` (see retryWhere()).
     * @return array{invite_new:int,invite_pending:int,update_all:int,cancel_all:int,retry_failed:int}
     */
    public static function available(array $m): array
    {
        $id = (int) $m['id'];
        $seq = (int) $m['sequence'];
        $status = (string) $m['status'];
        $ended = MeetingTime::hasEnded($m);
        $out = ['invite_new' => 0, 'invite_pending' => 0, 'update_all' => 0, 'cancel_all' => 0, 'retry_failed' => 0];
        $count = static fn(string $where, array $params = []): int => (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND {$where}", array_merge([$id], $params), 0);

        if (in_array($status, ['draft', 'scheduled'], true) && !$ended) {
            $out['invite_new'] = $count("removed_at IS NULL AND invite_sent_at IS NULL AND invite_status = 'not_sent' AND rsvp <> 'declined'");
        }
        if ($status === 'scheduled' && !$ended) {
            $out['invite_pending'] = $count("removed_at IS NULL AND invite_status IN ('not_sent','failed') AND rsvp <> 'declined' AND (invite_sent_at IS NULL OR invite_sequence IS NULL OR invite_sequence < ?)", [$seq]);
            $out['update_all'] = $count("removed_at IS NULL AND invite_status IN ('not_sent','sent','failed') AND rsvp <> 'declined' AND (invite_sent_at IS NULL OR invite_sequence IS NULL OR invite_sequence < ?)", [$seq]);
        }
        if ($status === 'cancelled') {
            $out['cancel_all'] = $count(self::cancelWhere(), [$seq]);
        }
        if (in_array($status, ['scheduled', 'cancelled'], true)) {
            $out['retry_failed'] = $count(self::retryWhere($status));
        }
        return $out;
    }

    /** People who were invited earlier and hold an older version of the meeting (their last message is older than the current SEQUENCE), declined people left out. */
    public static function staleCount(array $m): int
    {
        if ((string) $m['status'] !== 'scheduled') {
            return 0;
        }
        return (int) Database::value(
            "SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND rsvp <> 'declined' AND invite_sent_at IS NOT NULL AND invite_status IN ('sent','failed')
                AND (invite_sequence IS NULL OR invite_sequence < ?)",
            [(int) $m['id'], (int) $m['sequence']],
            0
        );
    }

    /** Who still needs the cancellation: holds an invitation, is not removed, is not already waiting for it and has not received it (their last message is older than the cancelled SEQUENCE). */
    private static function cancelWhere(): string
    {
        return "removed_at IS NULL AND invite_sent_at IS NOT NULL AND (queue_kind IS NULL OR queue_kind <> 'cancel' OR invite_status <> 'queued') AND (invite_sequence IS NULL OR invite_sequence < ?)";
    }

    /**
     * Failed messages worth another try: in a scheduled meeting every failed invitation / update of a person who is still on the list plus a failed removal notice;
     * in a cancelled meeting the failed cancellations. (A removed person whose invitation failed is not retried, there is nothing to send.)
     */
    private static function retryWhere(string $status): string
    {
        if ($status === 'cancelled') {
            return "invite_status = 'failed' AND queue_kind = 'cancel' AND invite_sent_at IS NOT NULL";
        }
        return "invite_status = 'failed' AND ((removed_at IS NULL AND (queue_kind IS NULL OR queue_kind <> 'cancel')) OR (removed_at IS NOT NULL AND queue_kind = 'cancel'))";
    }

    /** People a "Send reminder now" would reach: invited, still on the list, not declined, not yet reminded about this one. 0 unless the meeting is scheduled and has not started. */
    public static function reminderCandidates(array $m, string $which): array
    {
        if ((string) $m['status'] !== 'scheduled' || MeetingTime::hasStarted($m)) {
            return [];
        }
        $col = $which === '1h' ? 'reminder_1h_sent_at' : 'reminder_24h_sent_at';
        return array_map('intval', array_column(Database::all(
            "SELECT id FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND rsvp <> 'declined' AND invite_sent_at IS NOT NULL AND invite_status IN ('sent','queued') AND {$col} IS NULL ORDER BY id",
            [(int) $m['id']]
        ), 'id'));
    }

    // ------------------------------------------------------------------------------------------------ status (GET .../mail)

    /**
     * Everything the sending panel shows. With $withAttendees every active attendee is listed with the state of their messages (admin only: addresses are personal data).
     * @return array<string,mixed>
     */
    public static function status(array $m, bool $withAttendees = false): array
    {
        $id = (int) $m['id'];
        $avail = self::available($m);
        $summary = MeetingService::summary($id);
        $waiting = self::waiting($id);
        $transport = self::transport();
        $out = [
            'meeting' => [
                'id'             => $id,
                'title'          => (string) $m['title'],
                'status'         => (string) $m['status'],
                'status_label'   => MeetingService::STATUS_LABELS[$m['status']] ?? (string) $m['status'],
                'sequence'       => (int) $m['sequence'],
                'send_paused'    => (int) $m['send_paused'] === 1,
                'halt_reason'    => (string) ($m['send_halt_reason'] ?? ''),
                'when'           => MeetingTime::rangeLabel((string) $m['start_at'], (string) $m['end_at']),
                'ended'          => MeetingTime::hasEnded($m),
                'organiser_name' => (string) $m['organiser_name'],
                'organiser_email' => (string) $m['organiser_email'],
                'platform'       => MeetingService::platformLabel((string) $m['platform']),
                'join_url'       => (string) ($m['join_url'] ?? ''),
                'invited'        => $m['invitations_sent_at'] !== null,
            ],
            'counts'    => $summary,
            'queue'     => ['waiting' => $waiting, 'failed' => $avail['retry_failed']],
            'stale'     => self::staleCount($m),
            'transport' => $transport,
            'sender'    => self::sender($m),
            'available' => $avail,
            'reminders' => [
                'h24' => count(self::reminderCandidates($m, '24h')), 'h1' => count(self::reminderCandidates($m, '1h')),
                'enabled_24h' => (int) $m['reminder_24h'] === 1, 'enabled_1h' => (int) $m['reminder_1h'] === 1,
            ],
            'batch_size' => self::batchSize(),
        ];
        if ($m['status'] === 'draft') {
            $out['readiness'] = (object) MeetingService::readiness($m);
        }
        if ($withAttendees) {
            $out['attendees'] = array_map(static fn(array $a): array => [
                'id' => (int) $a['id'], 'name' => (string) $a['name'], 'email' => (string) $a['email'], 'source' => (string) $a['source'], 'rsvp' => (string) $a['rsvp'],
                'rsvp_at' => $a['rsvp_at'], 'invite_status' => (string) $a['invite_status'], 'queue_kind' => $a['queue_kind'], 'attempts' => (int) $a['attempts'],
                'last_error' => (string) ($a['last_error'] ?? ''), 'invite_sent_at' => $a['invite_sent_at'], 'invite_sequence' => $a['invite_sequence'] === null ? null : (int) $a['invite_sequence'],
                'reminder_24h_sent_at' => $a['reminder_24h_sent_at'], 'reminder_1h_sent_at' => $a['reminder_1h_sent_at'],
            ], MeetingService::attendees($id));
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ queue (POST .../mail/send)

    /**
     * Put messages in the queue. $confirm = the number of people the administrator confirmed (must equal available()[$scope]); `one` needs $attendeeId instead.
     * A DRAFT with scope invite_new is scheduled first (its readiness is checked before anything changes).
     *
     * @return array{ok:bool,code?:string,message?:string,queued?:int,expected?:int,errors?:array<string,string>,meeting?:array<string,mixed>,reason?:string}
     *   codes: bad_request | not_found | conflict | ended | transport | count_changed | nothing | not_ready
     */
    public static function send(array $m, string $scope, ?int $confirm, ?int $attendeeId, ?int $userId): array
    {
        $id = (int) $m['id'];
        $status = (string) $m['status'];
        if (!in_array($scope, self::SCOPES, true)) {
            return self::fail('bad_request', 'Unknown sending option.');
        }
        $allowed = match ($scope) {
            'invite_new'                           => ['draft', 'scheduled'],
            'invite_pending', 'update_all', 'one'  => ['scheduled'],
            'cancel_all'                           => ['cancelled'],
            default                                => ['scheduled', 'cancelled'],       // retry_failed
        };
        if (!in_array($status, $allowed, true)) {
            return self::fail('conflict', match (true) {
                $scope === 'cancel_all'            => 'The cancellation can only be sent for a cancelled meeting.',
                $status === 'completed'            => 'This meeting has already taken place, so nothing more is sent about it.',
                $status === 'cancelled'            => 'This meeting is cancelled. Reopen it as a draft to invite people again.',
                default                            => 'Save and schedule the meeting first.',
            });
        }
        if ($scope !== 'cancel_all' && $scope !== 'retry_failed' && MeetingTime::hasEnded($m)) {
            return self::fail('ended', 'This meeting has already taken place, so nothing more is sent about it.');
        }
        $transport = self::transport();
        if ($transport['blocking']) {
            return self::fail('transport', $transport['message'], ['reason' => 'transport']);
        }

        if ($scope === 'one') {
            $a = $attendeeId === null ? null : Database::one('SELECT * FROM meeting_attendees WHERE id = ? AND meeting_id = ?', [$attendeeId, $id]);    // IDOR guard
            if ($a === null || $a['removed_at'] !== null) {
                return self::fail('not_found', 'That person is not on this meeting.');
            }
            if ($a['invite_status'] === 'queued') {
                return self::fail('conflict', 'A message to this person is already waiting to be sent.');
            }
            $q = MeetingService::queue($id, 'one', ['attendee_id' => (int) $a['id']]);
            if (!$q['ok'] || $q['queued'] < 1) {
                return self::fail('conflict', $q['error'] ?? 'Nothing was queued.');
            }
            return ['ok' => true, 'queued' => $q['queued'], 'meeting' => Meeting::find($id) ?? $m];
        }

        $expected = self::available($m)[$scope];
        if ($confirm === null) {
            return self::fail('bad_request', 'Confirm the number of people before sending.');
        }
        if ($confirm !== $expected) {
            return self::fail('count_changed', $expected === 0
                ? 'There is nobody left to send this to. The page was out of date.'
                : 'The number of people changed while you were deciding. It is now ' . $expected . '. Please confirm again.', ['expected' => $expected, 'reason' => 'count_changed']);
        }
        if ($expected < 1) {
            return self::fail('nothing', 'There is nobody to send this to.', ['reason' => 'nothing']);
        }

        if ($scope === 'invite_new' && $status === 'draft') {
            $problems = MeetingService::readiness($m);
            if ($problems !== []) {
                return self::fail('not_ready', (string) reset($problems), ['errors' => $problems, 'reason' => 'readiness']);
            }
            $s = MeetingService::schedule($id, $userId);
            if (!$s['ok']) {
                return self::fail('not_ready', (string) (reset($s['errors']) ?: 'The meeting could not be scheduled.'), ['errors' => $s['errors'], 'reason' => 'readiness']);
            }
            $m = (array) $s['meeting'];
        }

        $queued = match ($scope) {
            'cancel_all'   => self::queueLocal($id, 'cancel_all', $m),
            'retry_failed' => self::queueLocal($id, 'retry_failed', $m),
            default        => (static function () use ($id, $scope): int {
                $q = MeetingService::queue($id, $scope);
                return $q['ok'] ? $q['queued'] : 0;
            })(),
        };
        if ($queued < 1) {
            return self::fail('conflict', 'Nothing was queued: somebody else may have just sent it.', ['reason' => 'nothing']);
        }
        return ['ok' => true, 'queued' => $queued, 'meeting' => Meeting::find($id) ?? $m];
    }

    /**
     * Queue scopes MeetingService::queue() cannot express (see the report): cancel_all that skips people who already hold the cancellation, and retry_failed for
     * cancelled meetings and without dead rows of removed people. Same state change as the service (queued + kind + attempts reset, auto-pause cleared, audit).
     */
    private static function queueLocal(int $id, string $scope, array $m): int
    {
        if ($scope === 'cancel_all') {
            $n = Database::execute(
                "UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = 'cancel', attempts = 0, last_error = NULL WHERE meeting_id = ? AND " . self::cancelWhere(),
                [$id, (int) $m['sequence']]
            );
        } else {
            $n = Database::execute(
                "UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = COALESCE(queue_kind, IF(invite_sent_at IS NULL, 'invite', 'update')), attempts = 0, last_error = NULL WHERE meeting_id = ? AND "
                . self::retryWhere((string) $m['status']),
                [$id]
            );
        }
        if ($n > 0) {
            Database::execute('UPDATE meetings SET send_paused = 0, send_halt_reason = NULL WHERE id = ? AND send_paused = 1 AND send_halt_reason IS NOT NULL', [$id]);
            AuditLog::record('meeting.queue', 'meeting', $id, 'Queued ' . $n . ' message(s)', ['scope' => $scope, 'count' => $n]);
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------ pause / resume / retry

    /** @return array{ok:bool,code?:string,message?:string} */
    public static function pause(array $m): array
    {
        if (self::waiting((int) $m['id']) < 1) {
            return self::fail('conflict', 'Nothing is waiting to be sent, so there is nothing to pause.');
        }
        MeetingService::pause((int) $m['id'], '');
        return ['ok' => true];
    }

    /** @return array{ok:bool,code?:string,message?:string} */
    public static function resume(array $m): array
    {
        if ((int) $m['send_paused'] !== 1) {
            return self::fail('conflict', 'Sending is not paused.');
        }
        MeetingService::resume((int) $m['id']);
        RateLimit::clear(self::streakKey((int) $m['id']));
        return ['ok' => true];
    }

    /** Failed messages go back in the queue (attempts reset). Also lifts a pause the system set itself. @return array{ok:bool,code?:string,message?:string,queued?:int} */
    public static function retry(array $m): array
    {
        $status = (string) $m['status'];
        if (!in_array($status, ['scheduled', 'cancelled'], true)) {
            return self::fail('conflict', 'There is nothing to retry for this meeting.');
        }
        $n = self::queueLocal((int) $m['id'], 'retry_failed', $m);
        if ($n < 1) {
            return self::fail('nothing', 'There are no failed e-mails to retry.', ['reason' => 'nothing']);
        }
        RateLimit::clear(self::streakKey((int) $m['id']));
        return ['ok' => true, 'queued' => $n];
    }

    // ------------------------------------------------------------------------------------------------ the batch sender

    /**
     * Send the next batch of one meeting's queue. Safe to call from several places at once: only one caller sends, the others get busy = true.
     * $opts: limit (recipients, default the batch-size setting), max_seconds (stop taking recipients after this long, default 20).
     *
     * Result: ok, busy, paused, halted (null | {code, reason}), processed, sent, failed, retried, dropped, remaining, done (this call emptied the queue),
     * retry_in (seconds until waiting retries are due), counts (MeetingService::summary), results (one entry per recipient handled: id, name, email, kind, status
     * sent | failed | retry | dropped, error).
     *
     * @return array<string,mixed>
     */
    public static function processBatch(int $meetingId, array $opts = []): array
    {
        $m = Meeting::find($meetingId);
        if ($m === null) {
            return ['ok' => false, 'code' => 'not_found', 'message' => 'That meeting no longer exists.'];
        }
        if (!self::lock()) {
            return self::batchResult($m, ['busy' => true]);
        }
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'retried' => 0, 'dropped' => 0, 'halted' => null, 'paused' => false, 'results' => []];
        $startedAt = microtime(true);
        try {
            $maxSeconds = self::timeBudget((int) ($opts['max_seconds'] ?? 20));
            $limit = max(1, min(200, (int) ($opts['limit'] ?? self::batchSize())));
            @ignore_user_abort(true);                    // a closed browser tab must not leave a message half-recorded

            $m = Meeting::find($meetingId) ?? $m;        // state may have changed while we waited for the lock
            self::failStuck($meetingId);
            if ((int) $m['send_paused'] === 1) {
                $stats['paused'] = true;
                return self::batchResult($m, $stats);
            }
            $hasStreak = RateLimit::tooMany(self::streakKey($meetingId), 1);
            foreach (self::dueRows($meetingId, $limit) as $seen) {
                if (microtime(true) - $startedAt > $maxSeconds) {
                    break;
                }
                $r = self::sendOne($meetingId, $seen);
                if ($r['outcome'] === 'skip') {
                    continue;
                }
                if ($r['outcome'] === 'paused') {
                    $stats['paused'] = true;
                    break;
                }
                if ($r['outcome'] === 'config' || $r['outcome'] === 'prepare') {
                    // the set-up of the mail server is wrong (login, TLS, sender, size limit) or the message cannot be built: it would fail for EVERY person,
                    // so stop here with the recipient still queued and let an administrator fix it and press Resume
                    $reason = $r['outcome'] === 'config'
                        ? (self::$lastConfigError !== '' ? self::$lastConfigError : 'The mail server settings need attention.')
                        : 'The invitation could not be prepared. Check the meeting details and the error log, then press Resume.';
                    MeetingService::pause($meetingId, $reason);
                    $stats['halted'] = ['code' => $r['outcome'] === 'config' ? 'mail_server' : 'prepare', 'reason' => $reason];
                    $stats['paused'] = true;
                    Log::warning('Meetings: sending paused because of a ' . $r['outcome'] . ' problem', ['meeting' => $meetingId]);
                    break;
                }
                $stats['processed']++;
                $stats[$r['outcome'] === 'sent' ? 'sent' : ($r['outcome'] === 'failed' ? 'failed' : ($r['outcome'] === 'retry' ? 'retried' : 'dropped'))]++;
                $stats['results'][] = $r['row'];
                if ($r['outcome'] === 'sent') {
                    if ($hasStreak) {
                        RateLimit::clear(self::streakKey($meetingId));
                        $hasStreak = false;
                    }
                } elseif ($r['outcome'] === 'retry' || ($r['outcome'] === 'failed' && !empty($r['transient']))) {
                    $hasStreak = true;
                    if (RateLimit::hit(self::streakKey($meetingId), 1800) >= self::BREAKER) {
                        $reason = 'The mail server kept refusing messages for the moment (it may be down or limiting how fast you can send). Check the mail settings, then press Resume.';
                        MeetingService::pause($meetingId, $reason);
                        $stats['halted'] = ['code' => 'mail_server', 'reason' => $reason];
                        $stats['paused'] = true;
                        Log::warning('Meetings: sending paused after repeated temporary failures', ['meeting' => $meetingId]);
                        break;
                    }
                } elseif ($r['outcome'] === 'failed' && $hasStreak) {
                    RateLimit::clear(self::streakKey($meetingId));        // the server answered (it refused this ONE address): it is up
                    $hasStreak = false;
                }
            }
            return self::finish($meetingId, $stats);
        } finally {
            Mailer::closeConnection();
            self::unlock();
        }
    }

    /**
     * The seconds this request may keep taking recipients. Asks PHP for more time (a slow mail server must not kill the request half-way); when the host does not
     * allow that (set_time_limit disabled, max_execution_time stays low) the budget shrinks so that one slow message after the budget still fits.
     */
    private static function timeBudget(int $wanted): int
    {
        $wanted = max(1, $wanted);
        if (function_exists('set_time_limit')) {
            @set_time_limit($wanted + 60);
        }
        $limit = (int) ini_get('max_execution_time');
        if ($limit > 0 && $limit < $wanted + 15) {
            return max(3, $limit - 15);
        }
        return $wanted;
    }

    /** Queued rows that are due now: fewest attempts first, a retry only after its waiting time. @return list<array<string,mixed>> */
    private static function dueRows(int $meetingId, int $limit): array
    {
        return Database::all(
            "SELECT * FROM meeting_attendees
              WHERE meeting_id = ? AND invite_status = 'queued' AND attempts < ? AND (attempts = 0 OR updated_at <= DATE_SUB(NOW(), INTERVAL (? * attempts) SECOND))
              ORDER BY attempts ASC, id ASC LIMIT ?",
            [$meetingId, MeetingAttendee::MAX_ATTEMPTS, max(0, self::$retryDelay), max(1, $limit)]
        );
    }

    /** A row that used up its attempts but was never finished (PHP died while sending) would wait forever: mark it failed so the administrator can retry it. */
    private static function failStuck(int $meetingId): void
    {
        Database::execute(
            "UPDATE meeting_attendees SET invite_status = 'failed', last_error = 'Sending was interrupted. Press Retry to try again.'
              WHERE meeting_id = ? AND invite_status = 'queued' AND attempts >= ? AND updated_at < DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$meetingId, MeetingAttendee::MAX_ATTEMPTS, self::STUCK_SECONDS]
        );
    }

    /**
     * One recipient. Outcome: skip (not claimable) | paused | config | prepare | sent | failed | retry | dropped.
     * @param array<string,mixed> $seen the row as read for the batch
     * @return array{outcome:string,row?:array<string,mixed>,transient?:bool}
     */
    private static function sendOne(int $meetingId, array $seen): array
    {
        $aid = (int) $seen['id'];
        if (!MeetingService::claim($aid, (int) $seen['attempts'])) {
            return ['outcome' => 'skip'];
        }
        // everything that matters is read AGAIN after the claim: the person may have been removed, the meeting changed, cancelled or paused meanwhile
        $m = Meeting::find($meetingId);
        $a = MeetingAttendee::find($aid);
        if ($m === null || $a === null || (int) $a['meeting_id'] !== $meetingId || $a['invite_status'] !== 'queued') {
            if ($a !== null && $a['invite_status'] === 'queued') {
                MeetingService::release($aid);
            }
            return ['outcome' => 'skip'];
        }
        if ((int) $m['send_paused'] === 1) {
            MeetingService::release($aid);
            return ['outcome' => 'paused'];
        }
        $plan = self::plan($m, $a);
        $row = ['id' => $aid, 'name' => (string) $a['name'], 'email' => (string) $a['email'], 'kind' => $plan['message'] ?? (string) $a['queue_kind'], 'status' => '', 'error' => ''];
        if ($plan['action'] === 'drop') {
            self::drop($aid);
            return ['outcome' => 'dropped', 'row' => ['status' => 'dropped', 'error' => $plan['reason']] + $row];
        }
        $email = (string) $a['email'];
        if (!Mailer::validEmail($email) || preg_match('/^erased-[a-z]\d+@invalid\.invalid$/', $email) === 1) {
            MeetingService::markFailed($aid, 'There is no valid e-mail address for this person.', true);
            return ['outcome' => 'failed', 'row' => ['status' => 'failed', 'error' => 'There is no valid e-mail address for this person.'] + $row];
        }
        try {
            $msg = MeetingRenderer::message($m, $a, (string) $plan['message']);
        } catch (\Throwable $e) {
            Log::error('Meetings: a message could not be built - ' . self::safeError($e->getMessage()), ['meeting' => $meetingId, 'attendee' => $aid]);
            MeetingService::release($aid);
            return ['outcome' => 'prepare'];
        }
        try {
            $res = Mailer::attempt($msg['to'], $msg['subject'], $msg['text'], $msg['opts']);
        } catch (\Throwable $e) {
            Log::error('Meetings: unexpected error while sending - ' . self::safeError($e->getMessage()), ['meeting' => $meetingId, 'attendee' => $aid]);
            $res = ['ok' => false, 'error' => 'Unexpected error while sending.', 'transient' => true];
        }
        if ($res['ok']) {
            MeetingService::markSent($aid, (int) $m['sequence']);          // the SEQUENCE of the content that was just sent
            return ['outcome' => 'sent', 'row' => ['status' => 'sent'] + $row];
        }
        $error = self::safeError((string) $res['error']);
        if (!empty($res['config'])) {
            self::$lastConfigError = $error;
            MeetingService::release($aid);                                 // not this person's fault: give the attempt back
            return ['outcome' => 'config'];
        }
        $transient = !empty($res['transient']);
        $state = MeetingService::markFailed($aid, $error, !$transient);
        return ['outcome' => $state === 'failed' ? 'failed' : 'retry', 'transient' => $transient, 'row' => ['status' => $state === 'failed' ? 'failed' : 'retry', 'error' => $error] + $row];
    }

    /**
     * What to do with a claimed row now that the meeting and the person are known.
     * @return array{action:string,message?:string,reason?:string}
     */
    private static function plan(array $m, array $a): array
    {
        $status = (string) $m['status'];
        $kind = (string) ($a['queue_kind'] ?? '');
        if (!in_array($kind, MeetingAttendee::QUEUE_KINDS, true)) {
            $kind = $a['invite_sent_at'] === null ? 'invite' : 'update';
        }
        $removed = $a['removed_at'] !== null;
        if ($kind === 'cancel') {
            if ($status === 'completed') {
                return ['action' => 'drop', 'reason' => 'The meeting has already taken place.'];
            }
            if ($removed) {
                return ['action' => 'send', 'message' => 'removed'];
            }
            return $status === 'cancelled' ? ['action' => 'send', 'message' => 'cancel'] : ['action' => 'drop', 'reason' => 'The meeting is no longer cancelled.'];
        }
        if ($removed) {
            return ['action' => 'drop', 'reason' => 'Removed from the meeting.'];
        }
        if ($status !== 'scheduled') {
            return ['action' => 'drop', 'reason' => $status === 'cancelled' ? 'The meeting was cancelled before this was sent.' : 'The meeting is not scheduled.'];
        }
        if (MeetingTime::hasEnded($m)) {
            return ['action' => 'drop', 'reason' => 'The meeting has already taken place.'];
        }
        // "Resend to one person" for somebody who already holds the CURRENT version: it is the invitation again, not an update
        if ($kind === 'update' && $a['invite_sequence'] !== null && (int) $a['invite_sequence'] >= (int) $m['sequence']) {
            $kind = 'invite';
        }
        return ['action' => 'send', 'message' => $kind];
    }

    /** Put a queued row back where it was before it was queued (nothing is sent). */
    private static function drop(int $attendeeId): void
    {
        Database::execute(
            "UPDATE meeting_attendees SET invite_status = IF(invite_sent_at IS NULL, 'not_sent', 'sent'), queue_kind = NULL, attempts = 0 WHERE id = ? AND invite_status = 'queued'",
            [$attendeeId]
        );
    }

    /** @return array<string,mixed> */
    private static function finish(int $meetingId, array $stats): array
    {
        $m = Meeting::find($meetingId) ?? [];
        $res = self::batchResult($m, $stats);
        if ($res['done']) {
            $c = $res['counts'];
            AuditLog::record('meeting.mail_complete', 'meeting', $meetingId, 'A sending finished', ['sent' => $c['sent'], 'failed' => $c['failed']]);
        }
        return $res;
    }

    /** @return array<string,mixed> */
    private static function batchResult(array $m, array $extra = []): array
    {
        $id = (int) ($m['id'] ?? 0);
        $remaining = $id > 0 ? self::waiting($id) : 0;
        $retryIn = null;
        if ($remaining > 0 && (int) ($m['send_paused'] ?? 0) !== 1) {
            $wait = Database::value(
                "SELECT MIN(GREATEST(0, CAST(? * attempts AS SIGNED) - TIMESTAMPDIFF(SECOND, updated_at, NOW()))) FROM meeting_attendees WHERE meeting_id = ? AND invite_status = 'queued' AND attempts < ?",
                [max(0, self::$retryDelay), $id, MeetingAttendee::MAX_ATTEMPTS]
            );
            $retryIn = $wait !== null && (int) $wait > 0 ? min(60, (int) $wait) : null;
        }
        $res = $extra + ['processed' => 0, 'sent' => 0, 'failed' => 0, 'retried' => 0, 'dropped' => 0, 'halted' => null, 'paused' => false, 'results' => [], 'busy' => false];
        $res['ok'] = true;
        $res['remaining'] = $remaining;
        $res['retry_in'] = $retryIn;
        $res['done'] = $res['processed'] > 0 && $remaining === 0 && $res['halted'] === null;
        $res['paused'] = $res['paused'] || (int) ($m['send_paused'] ?? 0) === 1;
        $res['counts'] = $id > 0 ? MeetingService::summary($id) : [];
        $res['status'] = (string) ($m['status'] ?? '');
        return $res;
    }

    // ------------------------------------------------------------------------------------------------ test invitation (own address only)

    /**
     * Send ONE test message to the signed-in user's own address: real UID and SEQUENCE, "[TEST]" in the subject, the buttons do not record an answer, nothing is saved.
     * @param array{name?:string,email?:string,id?:int} $user
     * @return array{ok:bool,code?:string,message?:string,results?:list<array{ok:bool,error:?string}>,sent?:int,to?:string}
     */
    public static function sendTest(array $m, array $user, string $kind = 'invite'): array
    {
        $kind = in_array($kind, self::TEST_KINDS, true) ? $kind : 'invite';
        $to = trim((string) ($user['email'] ?? ''));
        if (!Mailer::validEmail($to)) {
            return self::fail('validation', 'Your own account has no valid e-mail address. Add one under your profile first.');
        }
        if (!RateLimit::attempt('meeting-test:' . (int) ($user['id'] ?? 0), 30, 3600)) {
            return self::fail('rate_limited', 'Too many test e-mails in the last hour. Please wait a while.');
        }
        $attendee = ['id' => 0, 'name' => (string) ($user['name'] ?? ''), 'email' => $to, 'token' => str_repeat('0', 48), 'rsvp' => 'pending'];
        try {
            $msg = MeetingRenderer::message($m, $attendee, $kind, ['test' => true]);
            $res = Mailer::attempt($msg['to'], $msg['subject'], $msg['text'], $msg['opts']);
        } catch (\Throwable $e) {
            Log::error('Meetings: the test message could not be built - ' . self::safeError($e->getMessage()), ['meeting' => (int) $m['id']]);
            $res = ['ok' => false, 'error' => 'The test message could not be prepared. Check the meeting details.', 'transient' => false];
        } finally {
            Mailer::closeConnection();
        }
        return ['ok' => true, 'results' => [['ok' => (bool) $res['ok'], 'error' => $res['ok'] ? null : self::safeError((string) $res['error'])]], 'sent' => $res['ok'] ? 1 : 0, 'to' => $to];
    }

    // ------------------------------------------------------------------------------------------------ free-text message to attendees

    /**
     * "Send an update": plain text from the organiser to chosen attendees. NOT queued and not stored (the browser sends chunks of at most MESSAGE_CHUNK ids).
     * Ids that are not on this meeting, or whose owner was removed, are skipped. A configuration-level mail failure stops the chunk (the rest is "not sent").
     *
     * @param list<int> $ids
     * @return array{ok:bool,code?:string,message?:string,errors?:array<string,string>,results?:list<array{id:int,ok:bool,error:?string}>,sent?:int,failed?:int,halted?:?string}
     */
    public static function sendMessage(array $m, string $subject, string $text, array $ids, ?int $userId = null): array
    {
        if ($m['status'] === 'draft') {
            return self::fail('conflict', 'Nobody has been invited to a draft yet. Schedule the meeting first.');
        }
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        $text = (string) preg_replace('/[^\P{C}\n\t]+/u', '', $text);
        if ($text === '') {
            return self::fail('validation', 'Write the message first.', ['errors' => ['message' => 'Write the message first.']]);
        }
        if (mb_strlen($text) > self::MAX_MESSAGE_CHARS) {
            return self::fail('validation', 'The message is too long (at most ' . self::MAX_MESSAGE_CHARS . ' characters).', ['errors' => ['message' => 'Too long.']]);
        }
        $subject = trim((string) preg_replace('/\s+/u', ' ', strip_tags($subject)));
        $subject = trim((string) preg_replace('/[^\P{C}]+/u', '', $subject));
        if (mb_strlen($subject) > self::MAX_SUBJECT_CHARS) {
            return self::fail('validation', 'The subject is too long (at most ' . self::MAX_SUBJECT_CHARS . ' characters).', ['errors' => ['subject' => 'Too long.']]);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $i): bool => $i > 0)));
        if ($ids === []) {
            return self::fail('validation', 'Choose who should receive the message.', ['errors' => ['attendee_ids' => 'Choose at least one person.']]);
        }
        if (count($ids) > self::MESSAGE_CHUNK) {
            return self::fail('validation', 'At most ' . self::MESSAGE_CHUNK . ' people per request.', ['errors' => ['attendee_ids' => 'Too many people in one request.']]);
        }
        $rows = Database::all(
            'SELECT * FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id',
            array_merge([(int) $m['id']], $ids)
        );
        $results = [];
        $sent = 0;
        $halted = null;
        try {
            foreach ($rows as $a) {
                if ($halted !== null) {
                    $results[] = ['id' => (int) $a['id'], 'ok' => false, 'error' => 'Not sent: ' . $halted];
                    continue;
                }
                if (!RateLimit::attempt('meeting-msg:' . (int) $userId, 600, 3600)) {
                    $results[] = ['id' => (int) $a['id'], 'ok' => false, 'error' => 'Too many messages in the last hour. Please wait a while.'];
                    continue;
                }
                try {
                    $msg = MeetingRenderer::message($m, $a, 'message', ['message' => $text]);
                    if ($subject !== '') {
                        $msg['subject'] = $subject;
                    }
                    $res = Mailer::attempt($msg['to'], $msg['subject'], $msg['text'], $msg['opts']);
                } catch (\Throwable $e) {
                    Log::error('Meetings: a message could not be built - ' . self::safeError($e->getMessage()), ['meeting' => (int) $m['id']]);
                    $res = ['ok' => false, 'error' => 'The message could not be prepared.', 'transient' => false];
                }
                if ($res['ok']) {
                    $sent++;
                    $results[] = ['id' => (int) $a['id'], 'ok' => true, 'error' => null];
                    continue;
                }
                $error = self::safeError((string) $res['error']);
                $results[] = ['id' => (int) $a['id'], 'ok' => false, 'error' => $error];
                if (!empty($res['config'])) {
                    $halted = $error;
                }
            }
        } finally {
            Mailer::closeConnection();
        }
        $seen = array_column($rows, 'id');
        foreach ($ids as $i) {
            if (!in_array((string) $i, array_map('strval', $seen), true)) {
                $results[] = ['id' => $i, 'ok' => false, 'error' => 'That person is not on this meeting.'];
            }
        }
        return ['ok' => true, 'results' => $results, 'sent' => $sent, 'failed' => count($results) - $sent, 'halted' => $halted];
    }

    // ------------------------------------------------------------------------------------------------ reminders

    /**
     * One reminder to one person. Outcome: sent | failed | retry | skipped | config. The claim (an atomic UPDATE of the sent-at mark) comes right before the
     * send, so two senders can never both win; a temporary failure releases the claim and counts one try (max 3), a permanent one keeps it.
     * @return array{outcome:string}
     */
    private static function remindOne(int $meetingId, int $attendeeId, string $which): array
    {
        $m = Meeting::find($meetingId);
        $a = MeetingAttendee::find($attendeeId);
        if ($m === null || $a === null || (int) $a['meeting_id'] !== $meetingId || $m['status'] !== 'scheduled' || MeetingTime::hasStarted($m)
            || $a['removed_at'] !== null || $a['rsvp'] === 'declined' || $a['invite_sent_at'] === null || !Mailer::validEmail((string) $a['email'])) {
            return ['outcome' => 'skipped'];
        }
        if (!MeetingService::claimReminder($attendeeId, $which)) {
            return ['outcome' => 'skipped'];
        }
        try {
            $msg = MeetingRenderer::message($m, $a, 'reminder_' . $which);
            $res = Mailer::attempt($msg['to'], $msg['subject'], $msg['text'], $msg['opts']);
        } catch (\Throwable $e) {
            Log::error('Meetings: a reminder could not be sent - ' . self::safeError($e->getMessage()), ['meeting' => $meetingId, 'attendee' => $attendeeId]);
            $res = ['ok' => false, 'error' => 'Unexpected error while sending.', 'transient' => true];
        }
        if ($res['ok']) {
            return ['outcome' => 'sent'];
        }
        if (!empty($res['config'])) {
            self::$lastConfigError = self::safeError((string) $res['error']);
            self::unclaimReminder($attendeeId, $which);                    // not this person's fault and not a try
            return ['outcome' => 'config'];
        }
        MeetingService::reminderFailed($attendeeId, $which, !empty($res['transient']));
        return ['outcome' => !empty($res['transient']) ? 'retry' : 'failed'];
    }

    private static function unclaimReminder(int $attendeeId, string $which): void
    {
        $col = $which === '1h' ? 'reminder_1h_sent_at' : 'reminder_24h_sent_at';
        Database::execute("UPDATE meeting_attendees SET {$col} = NULL WHERE id = ?", [$attendeeId]);
    }

    /**
     * The reminders that are due now (MeetingService::dueReminders): the cron job calls this. Same lock as the batch sender. Temporary failures in a row (the
     * mail server is down) stop the run and cool down for ten minutes instead of using up every person's three tries.
     * $opts: limit (default 200), max_seconds (default 60). The clock is MeetingTime::now() (tests freeze it with MeetingTime::freeze()).
     * @return array{sent:int,failed:int,retried:int,skipped:int,busy:bool,cooling:bool,halted:?string,out_of_time:bool}
     */
    public static function processDueReminders(array $opts = []): array
    {
        $stats = ['sent' => 0, 'failed' => 0, 'retried' => 0, 'skipped' => 0, 'busy' => false, 'cooling' => false, 'halted' => null, 'out_of_time' => false];
        if (RateLimit::tooMany(self::REMINDER_STREAK_KEY, self::BREAKER)) {
            $stats['cooling'] = true;
            return $stats;
        }
        $due = MeetingService::dueReminders(null, max(1, min(500, (int) ($opts['limit'] ?? 200))));
        if ($due === []) {
            return $stats;
        }
        if (!self::lock()) {
            $stats['busy'] = true;
            return $stats;
        }
        $deadline = microtime(true) + max(5, (int) ($opts['max_seconds'] ?? 60));
        try {
            @ignore_user_abort(true);
            $hasStreak = false;
            foreach ($due as $d) {
                if (microtime(true) > $deadline) {
                    $stats['out_of_time'] = true;
                    break;
                }
                $r = self::remindOne((int) $d['meeting']['id'], (int) $d['attendee']['id'], (string) $d['which']);
                if ($r['outcome'] === 'config') {
                    $stats['halted'] = self::$lastConfigError;
                    for ($i = 0; $i < self::BREAKER; $i++) {
                        RateLimit::hit(self::REMINDER_STREAK_KEY, 600);    // a set-up problem fails for everybody: wait the full cool-down
                    }
                    break;
                }
                if ($r['outcome'] === 'retry') {
                    $stats['retried']++;
                    if (RateLimit::hit(self::REMINDER_STREAK_KEY, 600) >= self::BREAKER) {
                        $stats['halted'] = 'The mail server kept refusing messages for the moment.';
                        break;
                    }
                    $hasStreak = true;
                    continue;
                }
                $stats[$r['outcome']]++;
                if ($r['outcome'] === 'sent' && $hasStreak) {
                    RateLimit::clear(self::REMINDER_STREAK_KEY);
                    $hasStreak = false;
                }
            }
        } finally {
            Mailer::closeConnection();
            self::unlock();
        }
        return $stats;
    }

    /**
     * "Send reminder now": the reminder ($which = '24h' | '1h') to every invited person who has not had it, whatever the clock says and whether or not the automatic
     * reminder is switched on. Works in slices (time budget) so a long list never times out: call again while `remaining` > 0.
     * @return array{ok:bool,code?:string,message?:string,sent?:int,failed?:int,skipped?:int,remaining?:int,busy?:bool,halted?:?string}
     */
    public static function sendRemindersNow(array $m, string $which, int $limit = 40, int $maxSeconds = 20): array
    {
        if (!in_array($which, ['24h', '1h'], true)) {
            return self::fail('bad_request', 'Choose the 24-hour or the 1-hour reminder.');
        }
        if ($m['status'] !== 'scheduled') {
            return self::fail('conflict', 'Reminders are only sent for a scheduled meeting.');
        }
        if (MeetingTime::hasStarted($m)) {
            return self::fail('ended', 'This meeting has already started, so a reminder would be too late.');
        }
        if (!self::lock()) {
            return ['ok' => true, 'busy' => true, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'remaining' => count(self::reminderCandidates($m, $which)), 'halted' => null];
        }
        $stats = ['ok' => true, 'busy' => false, 'sent' => 0, 'failed' => 0, 'skipped' => 0, 'halted' => null];
        $startedAt = microtime(true);
        try {
            $maxSeconds = self::timeBudget($maxSeconds);
            @ignore_user_abort(true);
            foreach (array_slice(self::reminderCandidates($m, $which), 0, max(1, $limit)) as $aid) {
                if (microtime(true) - $startedAt > $maxSeconds) {
                    break;
                }
                $r = self::remindOne((int) $m['id'], $aid, $which);
                if ($r['outcome'] === 'config') {
                    $stats['halted'] = self::$lastConfigError;
                    break;
                }
                $stats[$r['outcome'] === 'retry' ? 'failed' : $r['outcome']]++;
            }
        } finally {
            Mailer::closeConnection();
            self::unlock();
        }
        $stats['remaining'] = count(self::reminderCandidates((array) (Meeting::find((int) $m['id']) ?? $m), $which));
        if ($stats['sent'] > 0) {
            AuditLog::record('meeting.reminders_now', 'meeting', (int) $m['id'], 'Sent reminders now', ['which' => $which, 'sent' => $stats['sent'], 'failed' => $stats['failed']]);
        }
        return $stats;
    }

    // ------------------------------------------------------------------------------------------------ cron

    /**
     * The cron job: finish the meetings' queues and send the reminders that are due, within a time budget. Uses the same lock as the admin loop, so running both
     * at once is harmless. Returns counts only.
     * @return array{meetings:int,processed:int,sent:int,failed:int,reminders_sent:int,reminders_failed:int,completed:int,busy:bool,out_of_time:bool,halted:int}
     */
    public static function runDue(int $maxSeconds = 240): array
    {
        $deadline = microtime(true) + max(5, $maxSeconds);
        $sum = ['meetings' => 0, 'processed' => 0, 'sent' => 0, 'failed' => 0, 'reminders_sent' => 0, 'reminders_failed' => 0, 'completed' => 0, 'busy' => false, 'out_of_time' => false, 'halted' => 0];
        if (!MeetingService::available()) {
            return $sum;
        }
        $sum['completed'] = MeetingService::completeDue();
        // reminders are time-critical: they go first
        $rem = self::processDueReminders(['max_seconds' => (int) max(5, min(60, $deadline - microtime(true) - 2))]);
        $sum['reminders_sent'] = $rem['sent'];
        $sum['reminders_failed'] = $rem['failed'] + $rem['retried'];
        $sum['busy'] = $rem['busy'];
        foreach (MeetingService::meetingsWithQueue() as $id) {
            $sum['meetings']++;
            while (true) {
                $left = $deadline - microtime(true);
                if ($left < 3) {
                    $sum['out_of_time'] = true;
                    return $sum;
                }
                $res = self::processBatch($id, ['max_seconds' => (int) min(25, $left - 1)]);
                if (empty($res['ok'])) {
                    break;
                }
                if ($res['busy']) {
                    $sum['busy'] = true;
                    return $sum;
                }
                $sum['processed'] += (int) $res['processed'];
                $sum['sent'] += (int) $res['sent'];
                $sum['failed'] += (int) $res['failed'];
                if ($res['halted'] !== null) {
                    $sum['halted']++;
                    break;
                }
                if ($res['paused'] || $res['remaining'] === 0 || (int) $res['processed'] === 0) {
                    break;       // finished, paused, or only retries that are not due yet are left: look again on the next run
                }
            }
        }
        return $sum;
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** @param array<string,mixed> $extra @return array<string,mixed> */
    private static function fail(string $code, string $message, array $extra = []): array
    {
        return ['ok' => false, 'code' => $code, 'message' => $message] + $extra;
    }
}
