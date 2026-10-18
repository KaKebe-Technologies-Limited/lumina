<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Core\AuditLog;
use App\Core\Config;
use App\Core\Database;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Services\Calendar\CivilDate;

/**
 * "Request a meeting" (Contact page): validation, storage, the two e-mails, and the admin workflow (new -> approved | declined | closed).
 * Nothing is booked automatically. Personal data: admin + editor only; audit entries carry ids only. Docs: docs/MEETINGS.md sections 6 and 10.
 */
final class MeetingRequestService
{
    public const STATUS_LABELS = ['new' => 'New', 'approved' => 'Approved', 'declined' => 'Declined', 'closed' => 'Closed'];
    public const STATUS_TONES = ['new' => 'warning', 'approved' => 'success', 'declined' => 'danger', 'closed' => 'neutral'];
    /** Per visitor (keyed IP hash) and hour. The route also throttles; this survives a restarted rate limiter. */
    public const MAX_PER_HOUR = 5;
    /** Acknowledgement e-mails to ONE visitor-typed address per day, and in total per hour (the address is unverified). */
    public const ACK_PER_ADDRESS = 2;
    public const ACK_PER_HOUR = 120;
    public const CSV_HEADER = ['Received', 'Name', 'Email', 'Phone', 'Organisation', 'Topic', 'Message', 'Preferred date', 'Time of day', 'Minutes', 'Type', 'Status', 'Meeting id'];
    public const TIME_START = ['any' => '10:00', 'morning' => '09:00', 'afternoon' => '14:00', 'evening' => '17:30'];

    /** Is the form on? (setting + tables.) The registry switch and the tables are checked by the caller's view as for the calendar block. */
    public static function enabled(): bool
    {
        return MeetingService::available() && (string) setting('meeting_request_enabled', '1') === '1';
    }

    /** Topics people can choose (setting meeting_request_topics, one per line, at most 12 of 60 characters). @return list<string> */
    public static function topics(): array
    {
        $out = [];
        foreach (preg_split('/\R/u', (string) setting('meeting_request_topics', '')) ?: [] as $line) {
            $t = mb_substr(Sanitizer::text($line), 0, 60);
            if ($t !== '' && !in_array($t, $out, true)) {
                $out[] = $t;
            }
        }
        $out = array_slice($out, 0, 12);
        return $out !== [] ? $out : ['Partnership', 'Membership', 'Volunteering', 'Programmes', 'Media', 'Other'];
    }

    /** Time-of-day windows: key => label (registry labels). @return array<string,string> */
    public static function windows(): array
    {
        $out = [];
        foreach (MeetingRequest::TIME_WINDOWS as $k) {
            $out[$k] = (string) content('contact', 'meeting_request.window_' . $k);
        }
        return $out;
    }

    /** Meeting-type preferences: key => label. @return array<string,string> */
    public static function platformPrefs(): array
    {
        $out = [];
        foreach (MeetingRequest::PLATFORM_PREFS as $k) {
            $out[$k] = (string) content('contact', 'meeting_request.platform_' . $k);
        }
        return $out;
    }

    /** The registry error text 'err_*' (editable), with a fallback. */
    private static function err(string $key): string
    {
        return (string) content('contact', 'meeting_request.' . $key);
    }

    /**
     * Validate and store a request, then send the acknowledgement (to the visitor) and the notification (to the organisation).
     * Input names: name, email, phone, organisation, topic, message, preferred_date (Y-m-d), preferred_time (any|morning|afternoon|evening),
     * duration_minutes (15|30|45|60|90), platform_pref (video|phone|in_person), consent (ticked).
     * ctx: ip, user_agent (never stored raw), mail (default true).
     * A repeat of the same request within a day (same e-mail, topic and message) is accepted silently: nothing new is stored or e-mailed.
     * @return array{ok:bool,errors:array<string,string>,id?:int,duplicate?:bool,throttled?:bool}
     */
    public static function create(array $in, array $ctx = []): array
    {
        if (!self::enabled()) {
            return ['ok' => false, 'errors' => ['_' => 'Meeting requests are closed.']];
        }
        $in = array_map(static fn(mixed $v): mixed => is_scalar($v) ? trim((string) $v) : '', $in);
        $topics = self::topics();
        $v = Validator::make($in, [
            'name' => 'required|min:2|max:150', 'email' => 'required|email|max:190', 'phone' => 'nullable|phone|max:40', 'organisation' => 'nullable|max:190',
            'topic' => 'required|max:120', 'message' => 'required|min:10|max:3000',
            'preferred_date' => 'nullable|date', 'preferred_time' => 'nullable|in:' . implode(',', MeetingRequest::TIME_WINDOWS),
            'duration_minutes' => 'nullable|in:' . implode(',', MeetingRequest::DURATIONS), 'platform_pref' => 'nullable|in:' . implode(',', MeetingRequest::PLATFORM_PREFS),
        ]);
        $errors = [];
        foreach ($v->errors() as $f => $msg) {
            $errors[$f] = match (true) {
                in_array($f, ['email'], true) => self::err('err_email'), $f === 'phone' => self::err('err_phone'),
                str_contains((string) $msg, 'at most') || str_contains((string) $msg, 'greater') => self::err('err_max'),
                $f === 'preferred_date' => self::err('err_date'), default => self::err('err_required'),
            };
        }
        if (!isset($errors['topic']) && !in_array((string) ($in['topic'] ?? ''), $topics, true)) {
            $errors['topic'] = self::err('err_topic');
        }
        $date = (string) ($in['preferred_date'] ?? '');
        if ($date !== '' && !isset($errors['preferred_date']) && (!CivilDate::valid($date) || $date <= MeetingTime::today() || $date > CivilDate::addDays(MeetingTime::today(), 366))) {
            $errors['preferred_date'] = self::err('err_date');
        }
        if (empty($in['consent']) || !in_array($in['consent'], ['1', 'on', 'yes', 'true'], true)) {
            $errors['consent'] = self::err('err_consent');
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $email = strtolower((string) $in['email']);
        $ipHash = substr(hash_hmac('sha256', 'meeting_request:' . self::ipKey((string) ($ctx['ip'] ?? '')), Config::appKey()), 0, 40);
        if (isset($ctx['ip']) && (int) Database::value('SELECT COUNT(*) FROM meeting_requests WHERE ip_hash = ? AND created_at > ?', [$ipHash, MeetingTime::addMinutes(MeetingTime::now(), -60)], 0) >= self::MAX_PER_HOUR) {
            return ['ok' => false, 'errors' => ['_' => self::err('err_throttle')], 'throttled' => true];
        }
        $message = self::multiline((string) $in['message'], 3000);
        $topic = (string) $in['topic'];
        $dup = Database::one('SELECT id FROM meeting_requests WHERE email = ? AND topic = ? AND message = ? AND created_at > ? LIMIT 1', [$email, $topic, $message, MeetingTime::addMinutes(MeetingTime::now(), -1440)]);
        if ($dup !== null) {
            return ['ok' => true, 'errors' => [], 'id' => (int) $dup['id'], 'duplicate' => true];
        }
        $id = MeetingRequest::create([
            'name' => mb_substr(Sanitizer::text((string) $in['name']), 0, 150), 'email' => $email,
            'phone' => ($in['phone'] ?? '') !== '' ? mb_substr(Sanitizer::text((string) $in['phone']), 0, 40) : null,
            'organisation' => ($in['organisation'] ?? '') !== '' ? mb_substr(Sanitizer::text((string) $in['organisation']), 0, 190) : null,
            'topic' => $topic, 'message' => $message,
            'preferred_date' => $date !== '' ? $date : null,
            'preferred_time' => ($in['preferred_time'] ?? '') !== '' ? (string) $in['preferred_time'] : 'any',
            'duration_minutes' => ($in['duration_minutes'] ?? '') !== '' ? (int) $in['duration_minutes'] : null,
            'platform_pref' => ($in['platform_pref'] ?? '') !== '' ? (string) $in['platform_pref'] : null,
            'status' => 'new', 'consent_at' => MeetingTime::now(), 'ip_hash' => $ipHash,
        ]);
        AuditLog::record('meeting_request.create', 'meeting_request', $id, 'Received a meeting request');
        if (($ctx['mail'] ?? true) !== false) {
            $req = (array) MeetingRequest::find($id);
            // The acknowledgement goes to an address nobody has proven to own, from the foundation's own sender: it is a FIXED text (no visitor words in it, see
            // MeetingRenderer::requestMessage) and what one address gets per day, and what the whole site sends per hour, is capped. The visitor sees the normal
            // success screen when a cap is hit; the staff notification below is not affected.
            if (RateLimit::attempt('mreq-ack:' . sha1($email), self::ACK_PER_ADDRESS, 86400) && RateLimit::attempt('mreq-ack-all', self::ACK_PER_HOUR, 3600)) {
                $ack = MeetingRenderer::requestMessage('ack', $req);
                Mailer::attempt($ack['to'], $ack['subject'], $ack['text'], $ack['opts']);
            } else {
                Log::warning('Meeting request acknowledgement skipped (send cap reached)', ['request' => $id]);
            }
            $note = MeetingRenderer::requestMessage('notify', $req);
            if ($note['to'] !== '') {
                Mailer::attempt($note['to'], $note['subject'], $note['text'], $note['opts']);
            }
        }
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    /**
     * The address a visitor throttle is keyed on: an IPv4 address as it is, an IPv6 address reduced to its /64 (one subscriber usually owns the whole /64,
     * so otherwise one machine could change address on every request).
     */
    public static function ipKey(string $ip): string
    {
        $bin = @inet_pton($ip);
        if ($bin !== false && strlen($bin) === 16) {
            return bin2hex(substr($bin, 0, 8)) . '::/64';
        }
        return $ip;
    }

    /**
     * Free text WITH its line breaks: tags removed, entities decoded, control characters dropped, CRLF -> LF, runs of spaces / tabs collapsed, at most one blank
     * line in a row. (Sanitizer::text() flattens everything to one line, which turns paragraphs into a run-on.) Escape on output.
     */
    public static function multiline(string $text, int $max): string
    {
        $t = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = str_replace(["\r\n", "\r"], "\n", $t);
        $t = (string) preg_replace('/[^\P{C}\n\t]+/u', '', $t);
        $t = (string) preg_replace('/[ \t\x{00A0}]+/u', ' ', $t);
        $t = (string) preg_replace('/ *\n */', "\n", $t);
        $t = trim((string) preg_replace("/\n{3,}/", "\n\n", $t));
        return mb_substr($t, 0, $max);
    }

    // ------------------------------------------------------------------------------------------------ admin

    /** @return array{items:list<array<string,mixed>>,total:int,page:int,pages:int,per_page:int} filters: status, q (name, e-mail, topic), page */
    public static function listRequests(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (in_array($filters['status'] ?? '', MeetingRequest::STATUSES, true)) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
            $where[] = '(name LIKE ? OR email LIKE ? OR topic LIKE ? OR organisation LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        $w = implode(' AND ', $where);
        $total = (int) Database::value("SELECT COUNT(*) FROM meeting_requests WHERE {$w}", $params, 0);
        $perPage = max(1, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $items = Database::all("SELECT * FROM meeting_requests WHERE {$w} ORDER BY FIELD(status, 'new') DESC, id DESC LIMIT ? OFFSET ?", [...$params, $perPage, ($page - 1) * $perPage]);
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    /** @return array<string,int> counts per status plus 'all' */
    public static function statusCounts(): array
    {
        $out = ['all' => 0, 'new' => 0, 'approved' => 0, 'declined' => 0, 'closed' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM meeting_requests GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
            $out['all'] += (int) $r['n'];
        }
        return $out;
    }

    /** Change the status (and the private notes). `declined` with $notify sends the visitor the decline e-mail (with the optional note). @return array{ok:bool,error?:string,mailed?:bool} */
    public static function setStatus(int $id, string $status, ?int $userId, array $opts = []): array
    {
        $r = MeetingRequest::find($id);
        if ($r === null) {
            return ['ok' => false, 'error' => 'That request no longer exists.'];
        }
        if (!in_array($status, MeetingRequest::STATUSES, true)) {
            return ['ok' => false, 'error' => 'Unknown status.'];
        }
        $data = ['status' => $status, 'handled_by' => $userId];
        if (array_key_exists('notes', $opts)) {
            $data['admin_notes'] = self::multiline((string) $opts['notes'], 3000) ?: null;
        }
        MeetingRequest::update($id, $data);
        $mailed = false;
        if ($status === 'declined' && !empty($opts['notify']) && $r['status'] !== 'declined' && !str_starts_with((string) $r['email'], 'erased-')) {
            $m = MeetingRenderer::requestMessage('decline', (array) MeetingRequest::find($id), ['note' => (string) ($opts['note'] ?? '')]);
            $mailed = Mailer::attempt($m['to'], $m['subject'], $m['text'], $m['opts'])['ok'];
        }
        AuditLog::record('meeting_request.status', 'meeting_request', $id, 'Request marked ' . $status, ['status' => $status, 'mailed' => $mailed]);
        return ['ok' => true, 'mailed' => $mailed];
    }

    /** Link a scheduled meeting to the request and mark it approved. */
    public static function attachMeeting(int $requestId, int $meetingId, ?int $userId): void
    {
        MeetingRequest::update($requestId, ['meeting_id' => $meetingId, 'status' => 'approved', 'handled_by' => $userId]);
        AuditLog::record('meeting_request.schedule', 'meeting_request', $requestId, 'Scheduled a meeting from a request', ['meeting_id' => $meetingId]);
    }

    /**
     * "Schedule this meeting": the schedule form prefilled from a request. The requester becomes the first attendee (source `request`).
     * @return array{form:array<string,mixed>,attendees:list<array<string,mixed>>,request_id:int}|null
     */
    public static function scheduleDefaults(int $requestId, ?array $user = null): ?array
    {
        $r = MeetingRequest::find($requestId);
        if ($r === null) {
            return null;
        }
        $pref = (string) ($r['platform_pref'] ?? '');
        $platform = match ($pref) { 'phone' => 'phone', 'in_person' => 'in_person', default => (string) setting('meeting_default_platform', 'google_meet') };
        if (in_array($platform, ['phone', 'in_person'], true) === false && !in_array($platform, MeetingService::ONLINE_PLATFORMS, true)) {
            $platform = 'google_meet';
        }
        $dur = (int) ($r['duration_minutes'] ?? 0);
        $form = MeetingService::defaults($user, [
            'title' => (string) $r['topic'], 'date' => (string) ($r['preferred_date'] ?? ''),
            'start_time' => self::TIME_START[(string) ($r['preferred_time'] ?? 'any')] ?? '10:00', 'duration' => in_array($dur, MeetingTime::DURATIONS, true) ? $dur : (int) setting('meeting_default_duration', '60'),
            'platform' => $platform,
        ]);
        return ['form' => $form, 'attendees' => [['name' => (string) $r['name'], 'email' => (string) $r['email'], 'source' => 'request']], 'request_id' => $requestId];
    }

    /** Rows for the CSV export (header = CSV_HEADER); pass them to CsvExport::response. @return list<list<string>> */
    public static function csvRows(array $filters = []): array
    {
        $rows = [];
        $list = self::listRequests($filters, 1, 100);
        for ($p = 1; $p <= $list['pages'] && $p <= 50; $p++) {
            $list = $p === 1 ? $list : self::listRequests($filters, $p, 100);
            foreach ($list['items'] as $r) {
                $rows[] = [(string) $r['created_at'], $r['name'], $r['email'], (string) $r['phone'], (string) $r['organisation'], $r['topic'], $r['message'], (string) $r['preferred_date'],
                    (string) $r['preferred_time'], (string) $r['duration_minutes'], (string) $r['platform_pref'], self::STATUS_LABELS[$r['status']] ?? $r['status'], (string) $r['meeting_id']];
            }
        }
        return $rows;
    }

    /** Admin only: delete a request (the meeting stays). */
    public static function delete(int $id): bool
    {
        $n = MeetingRequest::delete($id);
        if ($n > 0) {
            AuditLog::record('meeting_request.delete', 'meeting_request', $id, 'Deleted a meeting request');
        }
        return $n > 0;
    }
}
