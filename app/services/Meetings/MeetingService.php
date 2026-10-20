<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Sanitizer;
use App\Models\CalendarEntry;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CalendarWriter;
use App\Services\Calendar\CivilDate;
use App\Services\Calendar\IcsBuilder;

/**
 * THE rules of the meeting scheduler (docs/MEETINGS.md). Controllers, the mail queue, the cron job and the public pages call this class; nobody
 * writes `meetings`, `meeting_attendees` or the meeting's calendar entry directly.
 *
 * Result convention: ['ok' => bool, 'errors' => array<field,message>, ...]. Audit entries carry ids and counts only (never addresses).
 *
 * STATUS machine (MeetingService::TRANSITIONS):
 *   draft --schedule--> scheduled --(end time passes: completeDue)--> completed
 *   draft|scheduled --cancel--> cancelled --reopen--> draft         (scheduled --> draft only while nobody was invited yet)
 * SEQUENCE: +1 whenever a field the invitee sees changes AFTER the first invitation was handed to the mail server (INVITE_FIELDS), and +1 on cancel.
 * INVITE state per attendee: not_sent --queue--> queued --markSent--> sent;  queued --markFailed(permanent or 3rd try)--> failed --retry--> queued.
 *   queue_kind (invite | update | cancel) says what the queued message is. See queue().
 */
final class MeetingService
{
    public const MAX_ATTENDEES = 200;
    public const MAX_BULK_MEMBERS = 500;
    /** More than this many recipients added in one go needs the ability meetings.bulk (administrators). */
    public const BULK_THRESHOLD = 50;
    public const MAX_DESCRIPTION = 20000;
    public const MAX_AGENDA = 4000;
    public const MAX_TITLE = 190;
    /** Fields whose change makes the invitation different for the invitee: they bump SEQUENCE (after the first invitation) and suggest an update e-mail. */
    public const INVITE_FIELDS = ['title', 'start_at', 'end_at', 'platform', 'join_url', 'passcode', 'dial_in', 'location', 'description', 'organiser_name', 'organiser_email'];
    /** Platforms that need a join link before the meeting can be scheduled. */
    public const ONLINE_PLATFORMS = ['zoom', 'google_meet', 'teams', 'jitsi'];
    public const TRANSITIONS = [
        'draft'     => ['scheduled', 'cancelled'],
        'scheduled' => ['draft', 'cancelled', 'completed'],
        'cancelled' => ['draft'],
        'completed' => [],
    ];
    public const STATUS_LABELS = ['draft' => 'Draft', 'scheduled' => 'Scheduled', 'cancelled' => 'Cancelled', 'completed' => 'Completed'];
    public const STATUS_TONES = ['draft' => 'neutral', 'scheduled' => 'success', 'cancelled' => 'danger', 'completed' => 'info'];
    public const RSVP_LABELS = ['pending' => 'No answer yet', 'accepted' => 'Accepted', 'declined' => 'Declined', 'tentative' => 'Maybe'];
    public const INVITE_LABELS = ['not_sent' => 'Not sent', 'queued' => 'Waiting to send', 'sent' => 'Sent', 'failed' => 'Failed'];
    public const CSV_HEADER = ['Name', 'Email', 'Source', 'Invitation', 'Invited at', 'Answer', 'Answered at', 'Reminder 24h sent', 'Reminder 1h sent'];
    public const SOURCE_LABELS = ['manual' => 'Typed', 'member' => 'Member', 'user' => 'Staff', 'previous' => 'Earlier meeting', 'request' => 'Meeting request'];

    private static ?bool $available = null;

    // ------------------------------------------------------------------------------------------------ availability and small helpers

    /** Are the meeting tables there? (A live database imported late must not 500 the Contact page.) */
    public static function available(): bool
    {
        return self::$available ??= Database::tableExists('meetings') && Database::tableExists('meeting_attendees') && Database::tableExists('meeting_requests');
    }

    public static function flush(): void
    {
        self::$available = null;
    }

    /** The token format check (48 lower-case hex characters = 192 bits). */
    public static function validToken(mixed $token): bool
    {
        return is_string($token) && preg_match('/^[a-f0-9]{48}$/', $token) === 1;
    }

    public static function newToken(): string
    {
        return bin2hex(random_bytes(24));
    }

    public static function platformLabel(string $key): string
    {
        // the short name for e-mails and lists: "Jitsi (free video room)" reads "Jitsi"
        return trim((string) preg_replace('/\s*\(.*\)$/', '', Meeting::PLATFORMS[$key] ?? 'Other'));
    }

    /** Absolute URL of an invitee's page (the credential is in the URL: send it only to that invitee). */
    public static function inviteeUrl(array $attendee): string
    {
        return abs_url('meeting/' . $attendee['token']);
    }

    public static function icsUrl(array $attendee): string
    {
        return abs_url('meeting/' . $attendee['token'] . '.ics');
    }

    public static function adminUrl(int $meetingId, bool $absolute = false): string
    {
        return $absolute ? abs_url('admin/meetings/' . $meetingId) : url('admin/meetings/' . $meetingId);
    }

    /** Defaults of the "Schedule a meeting" form (organiser = the signed-in user; duration and platform from the settings). @return array<string,mixed> */
    public static function defaults(?array $user = null, array $prefill = []): array
    {
        $user ??= \App\Core\Auth::user() ?? [];
        $dur = (int) setting('meeting_default_duration', '60');
        $platform = (string) setting('meeting_default_platform', 'google_meet');
        return $prefill + [
            'title' => '', 'date' => '', 'start_time' => '10:00', 'duration' => in_array($dur, MeetingTime::DURATIONS, true) ? $dur : 60, 'end_time' => '',
            'platform' => isset(Meeting::PLATFORMS[$platform]) ? $platform : 'google_meet', 'join_url' => '', 'passcode' => '', 'dial_in' => '', 'location' => '',
            'description' => '', 'organiser_name' => (string) ($user['name'] ?? ''), 'organiser_email' => (string) ($user['email'] ?? ''),
            'visibility' => 'private', 'show_join_link_public' => 0, 'reminder_24h' => 1, 'reminder_1h' => 1, 'allow_rsvp' => 1,
        ];
    }

    // ------------------------------------------------------------------------------------------------ reading

    public static function find(int $id): ?array
    {
        return Meeting::find($id);
    }

    public static function findByUid(string $uid): ?array
    {
        return Meeting::findBy('uid', $uid);
    }

    /** Edit form data of a stored meeting (the inverse of validate()). @return array<string,mixed> */
    public static function formData(array $m): array
    {
        return [
            'title' => $m['title'], 'date' => MeetingTime::date((string) $m['start_at']), 'start_time' => MeetingTime::time((string) $m['start_at']),
            'duration' => MeetingTime::minutesBetween((string) $m['start_at'], (string) $m['end_at']), 'end_time' => MeetingTime::time((string) $m['end_at']),
            'platform' => $m['platform'], 'join_url' => (string) $m['join_url'], 'passcode' => (string) $m['passcode'], 'dial_in' => (string) $m['dial_in'],
            'location' => (string) $m['location'], 'description' => (string) $m['description'], 'organiser_name' => $m['organiser_name'],
            'organiser_email' => $m['organiser_email'], 'visibility' => $m['visibility'], 'show_join_link_public' => (int) $m['show_join_link_public'],
            'reminder_24h' => (int) $m['reminder_24h'], 'reminder_1h' => (int) $m['reminder_1h'], 'allow_rsvp' => (int) $m['allow_rsvp'],
        ];
    }

    /**
     * Admin list. Filters: status, q (title), when (upcoming|past|all; default all), page, per_page. Newest start first (upcoming: soonest first).
     * Each item carries counts: attendees, accepted, declined, tentative, pending, sent, failed, queued.
     * @return array{items:list<array<string,mixed>>,total:int,page:int,pages:int,per_page:int}
     */
    public static function listMeetings(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        self::completeDue();
        $where = ['1 = 1'];
        $params = [];
        $status = (string) ($filters['status'] ?? '');
        if (in_array($status, Meeting::STATUSES, true)) {
            $where[] = 'm.status = ?';
            $params[] = $status;
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = 'm.title LIKE ?';
            $params[] = '%' . addcslashes(mb_substr($q, 0, 100), '%_\\') . '%';
        }
        $when = (string) ($filters['when'] ?? 'all');
        $order = 'm.start_at DESC, m.id DESC';
        if ($when === 'upcoming') {
            $where[] = 'm.end_at > ?';
            $params[] = MeetingTime::now();
            $order = 'm.start_at ASC, m.id ASC';
        } elseif ($when === 'past') {
            $where[] = 'm.end_at <= ?';
            $params[] = MeetingTime::now();
        }
        $w = implode(' AND ', $where);
        $total = (int) Database::value("SELECT COUNT(*) FROM meetings m WHERE {$w}", $params, 0);
        $perPage = max(1, min(100, $perPage));
        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $rows = Database::all(
            "SELECT m.*, (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL) AS n_attendees,
                    (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.rsvp = 'accepted') AS n_accepted,
                    (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.rsvp = 'declined') AS n_declined,
                    (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.rsvp = 'tentative') AS n_tentative,
                    (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.invite_status = 'failed') AS n_failed,
                    (SELECT COUNT(*) FROM meeting_attendees a WHERE a.meeting_id = m.id AND a.removed_at IS NULL AND a.invite_status = 'queued') AS n_queued
             FROM meetings m WHERE {$w} ORDER BY {$order} LIMIT ? OFFSET ?",
            [...$params, $perPage, ($page - 1) * $perPage]
        );
        return ['items' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    /** @return array<string,int> counts per status plus 'all' */
    public static function statusCounts(): array
    {
        $out = ['all' => 0, 'draft' => 0, 'scheduled' => 0, 'cancelled' => 0, 'completed' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM meetings GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
            $out['all'] += (int) $r['n'];
        }
        return $out;
    }

    /** Attendees of a meeting (removed ones only on request). @return list<array<string,mixed>> */
    public static function attendees(int $meetingId, bool $includeRemoved = false): array
    {
        return Database::all('SELECT * FROM meeting_attendees WHERE meeting_id = ?' . ($includeRemoved ? '' : ' AND removed_at IS NULL') . ' ORDER BY id ASC', [$meetingId]);
    }

    /** @return array<string,int> */
    public static function summary(int $meetingId): array
    {
        $r = Database::one(
            "SELECT COUNT(*) AS total,
                SUM(invite_status = 'sent') AS sent, SUM(invite_status = 'queued') AS queued, SUM(invite_status = 'failed') AS failed, SUM(invite_status = 'not_sent') AS not_sent,
                SUM(rsvp = 'accepted') AS accepted, SUM(rsvp = 'declined') AS declined, SUM(rsvp = 'tentative') AS tentative, SUM(rsvp = 'pending') AS pending
             FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL",
            [$meetingId]
        ) ?? [];
        $out = [];
        foreach (['total', 'sent', 'queued', 'failed', 'not_sent', 'accepted', 'declined', 'tentative', 'pending'] as $k) {
            $out[$k] = (int) ($r[$k] ?? 0);
        }
        $out['removed'] = (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NOT NULL', [$meetingId], 0);
        return $out;
    }

    /** The invitee behind a token, with their meeting; null for a malformed or unknown token and for a REMOVED attendee. @return array{attendee:array,meeting:array}|null */
    public static function attendeeByToken(mixed $token): ?array
    {
        if (!self::validToken($token)) {
            return null;
        }
        $a = Database::one('SELECT * FROM meeting_attendees WHERE token = ? AND removed_at IS NULL', [$token]);
        if ($a === null) {
            return null;
        }
        $m = Meeting::find((int) $a['meeting_id']);
        return $m === null ? null : ['attendee' => $a, 'meeting' => $m];
    }

    /** calendar entry id => meeting id (the admin calendar links meeting entries to their meeting page). @return array<int,int> */
    public static function entryMeetingMap(array $entryIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $entryIds), static fn(int $i): bool => $i > 0)));
        if ($ids === [] || !self::available()) {
            return [];
        }
        $out = [];
        foreach (Database::all('SELECT id, calendar_entry_id FROM meetings WHERE calendar_entry_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids) as $r) {
            $out[(int) $r['calendar_entry_id']] = (int) $r['id'];
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ validation

    /**
     * Validate the schedule form. Input keys: title*, date*, start_time*, duration | end_time, platform, join_url, passcode, dial_in, location,
     * description (HTML), organiser_name*, organiser_email*, visibility, show_join_link_public, reminder_24h, reminder_1h, allow_rsvp.
     * @param array<string,mixed>|null $existing the stored row when editing
     * @return array{ok:bool,errors:array<string,string>,row:array<string,mixed>}
     */
    public static function validate(array $in, ?array $existing = null): array
    {
        $errors = [];
        $row = [];
        $str = static fn(mixed $v): string => is_scalar($v) ? trim((string) $v) : '';
        $bool = static fn(mixed $v, bool $default): bool => $v === null || $v === '' ? $default : in_array($v, [1, '1', true, 'true', 'on', 'yes'], true);

        $title = Sanitizer::text($str($in['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Enter a title for the meeting.';
        } elseif (mb_strlen($title) > self::MAX_TITLE) {
            $errors['title'] = 'The title can have at most ' . self::MAX_TITLE . ' characters.';
        }
        $row['title'] = $title;

        $sched = MeetingTime::fromForm($in['date'] ?? '', $in['start_time'] ?? '', $in['duration'] ?? '', $in['end_time'] ?? '');
        foreach ($sched['errors'] as $f => $m) {
            $errors[$f] = $m;
        }
        $row['start_at'] = $sched['start_at'];
        $row['end_at'] = $sched['end_at'];
        if ($sched['ok'] && ($existing === null || (string) $existing['start_at'] !== $sched['start_at']) && (string) $sched['start_at'] <= MeetingTime::now()) {
            $errors['date'] = 'The meeting must start in the future (East Africa Time).';
        }

        $platform = $str($in['platform'] ?? '') ?: (string) ($existing['platform'] ?? setting('meeting_default_platform', 'google_meet'));
        if (!isset(Meeting::PLATFORMS[$platform])) {
            $errors['platform'] = 'Choose how people will meet.';
            $platform = 'other';
        }
        $row['platform'] = $platform;

        $url = $str($in['join_url'] ?? '');
        if ($url !== '') {
            $ok = strlen($url) <= 500 && preg_match('~^https://[^\s\x00-\x1F\x7F]+$~i', $url) === 1 && filter_var($url, FILTER_VALIDATE_URL) !== false
                && parse_url($url, PHP_URL_USER) === null && parse_url($url, PHP_URL_PASS) === null;
            if (!$ok && in_array($platform, ['phone', 'whatsapp'], true)) {
                $ok = preg_match('~^tel:\+?[0-9 ()\-]{5,30}$~', $url) === 1;
            }
            if (!$ok) {
                $errors['join_url'] = 'Paste the full link, starting with https://' . (in_array($platform, ['phone', 'whatsapp'], true) ? ' (or tel:+256...)' : '') . '.';
            }
        }
        $row['join_url'] = $url !== '' && !isset($errors['join_url']) ? $url : null;

        foreach (['passcode' => 100, 'dial_in' => 190, 'location' => 255] as $f => $max) {
            $v = Sanitizer::text($str($in[$f] ?? ''));
            if (mb_strlen($v) > $max) {
                $errors[$f] = 'That can have at most ' . $max . ' characters.';
            }
            $row[$f] = $v !== '' ? $v : null;
        }

        $desc = Sanitizer::html($str($in['description'] ?? ''));
        if (mb_strlen($desc) > self::MAX_DESCRIPTION) {
            $errors['description'] = 'The description is too long (at most ' . self::MAX_DESCRIPTION . ' characters).';
        }
        $hasText = trim(strip_tags($desc, '<img>')) !== '';
        $row['description'] = $hasText ? $desc : null;
        $row['agenda'] = $hasText ? mb_substr(IcsBuilder::plainText($desc), 0, self::MAX_AGENDA) : null;

        $name = InviteeParser::cleanName($str($in['organiser_name'] ?? ''));
        if ($name === '') {
            $errors['organiser_name'] = 'Enter the organiser\'s name.';
        }
        $row['organiser_name'] = $name;
        $oe = strtolower($str($in['organiser_email'] ?? ''));
        if ($oe === '' || strlen($oe) > 190 || !\App\Core\Mailer::validEmail($oe)) {
            $errors['organiser_email'] = 'Enter a valid e-mail address for the organiser (replies go there).';
        }
        $row['organiser_email'] = $oe;

        $vis = $str($in['visibility'] ?? '') ?: (string) ($existing['visibility'] ?? 'private');
        if (!in_array($vis, Meeting::VISIBILITIES, true)) {
            $errors['visibility'] = 'Choose who can see this meeting.';
            $vis = 'private';
        }
        $row['visibility'] = $vis;
        $row['show_join_link_public'] = ($vis === 'public' && $bool($in['show_join_link_public'] ?? null, false)) ? 1 : 0;
        $row['reminder_24h'] = $bool($in['reminder_24h'] ?? null, false) ? 1 : 0;
        $row['reminder_1h'] = $bool($in['reminder_1h'] ?? null, false) ? 1 : 0;
        $row['allow_rsvp'] = $bool($in['allow_rsvp'] ?? null, true) ? 1 : 0;
        return ['ok' => $errors === [], 'errors' => $errors, 'row' => $row];
    }

    /**
     * What is still missing before a meeting can be scheduled (invitations sent): a join link or address that fits the platform, a future start.
     * @return array<string,string> field => message (empty = ready)
     */
    public static function readiness(array $m): array
    {
        $p = [];
        $platform = (string) ($m['platform'] ?? 'other');
        $url = trim((string) ($m['join_url'] ?? ''));
        $dial = trim((string) ($m['dial_in'] ?? ''));
        $loc = trim((string) ($m['location'] ?? ''));
        if (in_array($platform, self::ONLINE_PLATFORMS, true) && $url === '') {
            $p['join_url'] = 'Add the meeting link for ' . self::platformLabel($platform) . ' (or press "Generate a free private video room").';
        } elseif ($platform === 'in_person' && $loc === '') {
            $p['location'] = 'Enter the address where people should come.';
        } elseif ($platform === 'phone' && $url === '' && $dial === '') {
            $p['dial_in'] = 'Enter the phone number to call.';
        } elseif ($platform === 'whatsapp' && $url === '' && $dial === '') {
            $p['join_url'] = 'Add the WhatsApp link or number.';
        } elseif ($platform === 'other' && $url === '' && $dial === '' && $loc === '') {
            $p['join_url'] = 'Add a link, a phone number or an address so people know how to join.';
        }
        if ((string) ($m['start_at'] ?? '') <= MeetingTime::now()) {
            $p['date'] = 'The meeting must start in the future (East Africa Time).';
        }
        if (!\App\Core\Mailer::validEmail((string) ($m['organiser_email'] ?? ''))) {
            $p['organiser_email'] = 'Enter a valid organiser e-mail address.';
        }
        return $p;
    }

    // ------------------------------------------------------------------------------------------------ calendar entry (derived, owned by the meeting)

    /** The id of the calendar type "Meeting / outreach" (slug `meeting`); recreated when the owner deleted it. */
    public static function calendarTypeId(): int
    {
        $t = CalendarService::typeBySlug('meeting');
        if ($t !== null) {
            return (int) $t['id'];
        }
        Database::execute("INSERT IGNORE INTO calendar_types (slug, name, color, icon, sort_order, is_active, show_on_public, is_system) VALUES ('meeting', 'Meeting / outreach', '#6b4e9b', 'users', 50, 1, 1, 0)");
        CalendarService::flush();
        $t = CalendarService::typeBySlug('meeting');
        if ($t === null) {
            throw new \RuntimeException('The calendar type "meeting" could not be created.');
        }
        return (int) $t['id'];
    }

    /**
     * The calendar entry input for a meeting row. Public only when the meeting is public AND scheduled / completed; cancelled meetings are
     * inactive; a draft is never public. The entry carries NO description, location or attendee data: title, date and time only, plus the join
     * link when (and only when) the organiser ticked "also show the join link publicly".
     * @return array<string,mixed>
     */
    public static function entryInput(array $m): array
    {
        $public = $m['visibility'] === 'public' && in_array($m['status'], ['scheduled', 'completed'], true);
        // the join link is advertised only while the meeting is still to come: a finished meeting must not keep a (possibly reusable) room on the public calendar, feed and .ics
        $link = ($public && $m['status'] === 'scheduled' && (int) $m['show_join_link_public'] === 1 && preg_match('~^https://~i', (string) $m['join_url']) === 1) ? (string) $m['join_url'] : '';
        return [
            'title' => $m['title'], 'type_id' => self::calendarTypeId(), 'description' => '', 'location' => '', 'all_day' => '0',
            'start_date' => MeetingTime::date((string) $m['start_at']), 'start_time' => MeetingTime::time((string) $m['start_at']), 'end_time' => MeetingTime::time((string) $m['end_at']),
            // a meeting that ends after midnight is one entry that runs into the next day (without this the calendar refuses it: "end before start")
            'end_date' => MeetingTime::date((string) $m['end_at']) !== MeetingTime::date((string) $m['start_at']) ? MeetingTime::date((string) $m['end_at']) : '',
            'recurrence' => 'none', 'is_public' => $public ? '1' : '0', 'is_active' => $m['status'] === 'cancelled' ? '0' : '1',
            'link_url' => $link, 'link_label' => $link !== '' ? self::JOIN_LABEL_TOKEN : '',
        ];
    }

    /**
     * What a meeting's entry stores as its link text. It is NOT the words themselves: OccurrenceExpander swaps it for the editable registry text
     * (Admin > Pages > Meetings > "Button on the public calendar") every time the entry is shown, so changing the wording never leaves stale text in the database.
     */
    public const JOIN_LABEL_TOKEN = '{meeting_join}';

    /** The wording that replaces JOIN_LABEL_TOKEN. */
    public static function calendarJoinLabel(): string
    {
        $t = MeetingRenderer::t('page.calendar_join');
        return $t !== '' ? $t : 'Join the meeting';
    }

    /** Write the meeting's calendar entry (create when $m has no entry yet). @return array{ok:bool,errors:array<string,string>,id?:int} */
    public static function syncCalendar(array $m, ?int $userId = null): array
    {
        return CalendarWriter::saveEntry(isset($m['calendar_entry_id']) ? (int) $m['calendar_entry_id'] : null, self::entryInput($m), $userId, ['meeting' => true]);
    }

    // ------------------------------------------------------------------------------------------------ create / update

    /**
     * Create a DRAFT meeting (and its calendar entry). Attendees are added separately (addAttendees).
     * @return array{ok:bool,errors:array<string,string>,id?:int,meeting?:array<string,mixed>}
     */
    public static function create(array $in, ?int $userId): array
    {
        $v = self::validate($in, null);
        if (!$v['ok']) {
            return ['ok' => false, 'errors' => $v['errors']];
        }
        $row = $v['row'] + ['status' => 'draft', 'sequence' => 0, 'uid' => MeetingIcs::newUid(), 'created_by' => $userId, 'updated_by' => $userId];
        $entry = self::syncCalendar($row + ['calendar_entry_id' => null], $userId);
        if (!$entry['ok']) {
            return ['ok' => false, 'errors' => $entry['errors'] + ['_' => 'The calendar entry could not be created.']];
        }
        $row['calendar_entry_id'] = (int) $entry['id'];
        try {
            $id = Meeting::create($row);
        } catch (\Throwable $e) {
            Database::execute('DELETE FROM calendar_entries WHERE id = ?', [(int) $entry['id']]);
            CalendarService::flush();
            throw $e;
        }
        AuditLog::record('meeting.create', 'meeting', $id, 'Created a meeting');
        return ['ok' => true, 'errors' => [], 'id' => $id, 'meeting' => Meeting::find($id)];
    }

    /**
     * Save the schedule form of an existing DRAFT or SCHEDULED meeting.
     * - SEQUENCE +1 when an INVITE_FIELD changed and an invitation was already sent.
     * - A new date or time resets every answer to "no answer yet" and clears the sent-reminder marks (the reminders restart for the new time).
     * - `update_suggested` = the admin screen should offer "Send updated invitation to everyone" (scheduled, invited people exist, something visible changed).
     * @return array{ok:bool,errors:array<string,string>,meeting?:array<string,mixed>,changed?:list<string>,sequence_bumped?:bool,update_suggested?:bool}
     */
    public static function update(int $id, array $in, ?int $userId): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']];
        }
        if (!in_array($m['status'], ['draft', 'scheduled'], true)) {
            return ['ok' => false, 'errors' => ['_' => $m['status'] === 'cancelled' ? 'This meeting is cancelled. Reopen it as a draft to change it.' : 'A meeting that has taken place cannot be changed. Duplicate it instead.']];
        }
        $v = self::validate($in, $m);
        if (!$v['ok']) {
            return ['ok' => false, 'errors' => $v['errors']];
        }
        $row = $v['row'];
        if ($m['status'] === 'scheduled') {
            $problems = self::readiness($row);
            unset($problems['date']);                                  // an unchanged start in the past is a problem for validate(), not here
            if ($problems !== []) {
                return ['ok' => false, 'errors' => $problems];
            }
        }
        $changed = [];
        foreach (['title', 'start_at', 'end_at', 'platform', 'join_url', 'passcode', 'dial_in', 'location', 'description', 'organiser_name', 'organiser_email', 'visibility', 'show_join_link_public', 'reminder_24h', 'reminder_1h', 'allow_rsvp'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($m[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $inviteChanged = array_values(array_intersect($changed, self::INVITE_FIELDS));
        $invited = $m['invitations_sent_at'] !== null;
        $bump = $invited && $inviteChanged !== [];
        $row['sequence'] = (int) $m['sequence'] + ($bump ? 1 : 0);
        $row['updated_by'] = $userId;
        $startChanged = in_array('start_at', $changed, true);
        $timeChanged = $startChanged || in_array('end_at', $changed, true);
        Database::transaction(function () use ($id, $row, $startChanged, $timeChanged): void {
            Meeting::update($id, $row);
            if ($startChanged) {
                // the meeting moved: every answer is void and the reminders restart for the new start
                Database::execute("UPDATE meeting_attendees SET rsvp = 'pending', rsvp_at = NULL, reminder_24h_sent_at = NULL, reminder_1h_sent_at = NULL, reminder_attempts = 0 WHERE meeting_id = ?", [$id]);
            } elseif ($timeChanged) {
                // only the length changed: the start (and so the reminder moments) did not move. Accepted / maybe answers are asked again, a decline stays
                // a decline, and nobody is reminded a second time about the same start.
                Database::execute("UPDATE meeting_attendees SET rsvp = 'pending', rsvp_at = NULL WHERE meeting_id = ? AND rsvp IN ('accepted','tentative')", [$id]);
            }
        });
        $fresh = (array) Meeting::find($id);
        $sync = self::syncCalendar($fresh, $userId);
        AuditLog::record('meeting.update', 'meeting', $id, 'Edited a meeting', ['fields' => $changed, 'sequence' => (int) $fresh['sequence']]);
        $update = $bump && $fresh['status'] === 'scheduled' && (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND invite_sent_at IS NOT NULL", [$id], 0) > 0;
        return ['ok' => true, 'errors' => $sync['ok'] ? [] : $sync['errors'], 'meeting' => $fresh, 'changed' => $changed, 'sequence_bumped' => $bump, 'update_suggested' => $update];
    }

    // ------------------------------------------------------------------------------------------------ status

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * draft -> scheduled. Requires readiness() to be empty. Makes a public meeting visible in the public calendar. Does NOT send anything: the
     * caller queues the invitations afterwards (queue('invite_new')).
     * @return array{ok:bool,errors:array<string,string>,meeting?:array<string,mixed>}
     */
    public static function schedule(int $id, ?int $userId): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']];
        }
        if ($m['status'] === 'scheduled') {
            return ['ok' => true, 'errors' => [], 'meeting' => $m];
        }
        if (!self::canTransition((string) $m['status'], 'scheduled')) {
            return ['ok' => false, 'errors' => ['_' => 'Only a draft can be scheduled.']];
        }
        $problems = self::readiness($m);
        if ($problems !== []) {
            return ['ok' => false, 'errors' => $problems];
        }
        Meeting::update($id, ['status' => 'scheduled', 'updated_by' => $userId, 'send_paused' => 0, 'send_halt_reason' => null]);
        $fresh = (array) Meeting::find($id);
        self::syncCalendar($fresh, $userId);
        AuditLog::record('meeting.schedule', 'meeting', $id, 'Scheduled a meeting');
        return ['ok' => true, 'errors' => [], 'meeting' => $fresh];
    }

    /**
     * Cancel a draft or scheduled meeting. After the first invitation SEQUENCE goes +1 (the METHOD:CANCEL message must outrank the last REQUEST).
     * `notify` = how many people hold an invitation (queue('cancel_all') sends them the cancellation).
     * @return array{ok:bool,errors:array<string,string>,meeting?:array<string,mixed>,notify?:int}
     */
    public static function cancel(int $id, string $reason, ?int $userId): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']];
        }
        if (!self::canTransition((string) $m['status'], 'cancelled')) {
            return ['ok' => false, 'errors' => ['_' => $m['status'] === 'cancelled' ? 'This meeting is already cancelled.' : 'A meeting that has taken place cannot be cancelled.']];
        }
        $reason = mb_substr(Sanitizer::text($reason), 0, 500);
        $invited = $m['invitations_sent_at'] !== null;
        Database::transaction(function () use ($id, $m, $reason, $userId, $invited): void {
            Meeting::update($id, [
                'status' => 'cancelled', 'cancelled_reason' => $reason !== '' ? $reason : null, 'cancelled_at' => MeetingTime::now(), 'updated_by' => $userId,
                'sequence' => (int) $m['sequence'] + ($invited ? 1 : 0),
            ]);
            // anything still waiting to be sent about the old plan is dropped (the cancellation is queued separately)
            // (a removed person's pending "you are no longer invited" notice is NOT about the old plan: it still has to go out)
            Database::execute("UPDATE meeting_attendees SET invite_status = IF(invite_sent_at IS NULL, 'not_sent', 'sent'), queue_kind = NULL, attempts = 0 WHERE meeting_id = ? AND invite_status = 'queued' AND removed_at IS NULL", [$id]);
        });
        $fresh = (array) Meeting::find($id);
        self::syncCalendar($fresh, $userId);
        AuditLog::record('meeting.cancel', 'meeting', $id, 'Cancelled a meeting');
        $notify = (int) Database::value('SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NULL AND invite_sent_at IS NOT NULL', [$id], 0);
        return ['ok' => true, 'errors' => [], 'meeting' => $fresh, 'notify' => $notify];
    }

    /** cancelled -> draft (to change and schedule it again) or scheduled -> draft while nobody was invited yet. */
    public static function reopen(int $id, ?int $userId): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']];
        }
        if (!self::canTransition((string) $m['status'], 'draft') || ($m['status'] === 'scheduled' && $m['invitations_sent_at'] !== null)) {
            return ['ok' => false, 'errors' => ['_' => 'This meeting cannot go back to a draft.']];
        }
        Meeting::update($id, ['status' => 'draft', 'cancelled_reason' => null, 'cancelled_at' => null, 'updated_by' => $userId, 'sequence' => (int) $m['sequence'] + ($m['status'] === 'cancelled' && $m['invitations_sent_at'] !== null ? 1 : 0)]);
        self::syncCalendar((array) Meeting::find($id), $userId);
        AuditLog::record('meeting.reopen', 'meeting', $id, 'Reopened a meeting as a draft');
        return ['ok' => true, 'errors' => [], 'meeting' => Meeting::find($id)];
    }

    /** scheduled -> completed for every meeting whose end time has passed (lazy: called by the lists, the invitee page and the cron job). @return int meetings completed */
    public static function completeDue(): int
    {
        if (!self::available()) {
            return 0;
        }
        $ids = array_column(Database::all("SELECT id FROM meetings WHERE status = 'scheduled' AND end_at <= ?", [MeetingTime::now()]), 'id');
        if ($ids === []) {
            return 0;
        }
        Database::execute("UPDATE meetings SET status = 'completed' WHERE status = 'scheduled' AND end_at <= ?", [MeetingTime::now()]);
        foreach ($ids as $id) {
            self::syncCalendar((array) Meeting::find((int) $id), null);
        }
        return count($ids);
    }

    /** Copy a meeting as a new DRAFT "... (copy)" one week later (or tomorrow if that is past), without answers. Attendees are copied on request (fresh tokens, nothing sent). */
    public static function duplicate(int $id, ?int $userId, bool $copyAttendees = true): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']];
        }
        $form = self::formData($m);
        $form['title'] = mb_substr((string) $m['title'], 0, self::MAX_TITLE - 7) . ' (copy)';
        $date = CivilDate::addDays(MeetingTime::date((string) $m['start_at']), 7);
        $tomorrow = CivilDate::addDays(MeetingTime::today(), 1);
        $form['date'] = $date >= $tomorrow ? $date : $tomorrow;
        $form['end_time'] = '';                                      // keep the same length
        $res = self::create($form, $userId);
        if (!$res['ok']) {
            return $res;
        }
        if ($copyAttendees) {
            $list = [];
            foreach (self::attendees($id) as $a) {
                $list[] = ['name' => $a['name'], 'email' => $a['email'], 'source' => $a['source'], 'member_id' => $a['member_id'], 'user_id' => $a['user_id']];
            }
            self::addAttendees((int) $res['id'], $list, $userId, ['allow_bulk' => true]);
        }
        AuditLog::record('meeting.duplicate', 'meeting', (int) $res['id'], 'Duplicated meeting #' . $id);
        return $res;
    }

    /** Delete a DRAFT that was never sent. Anything else is cancelled instead. @return array{ok:bool,error?:string} */
    public static function delete(int $id): array
    {
        $m = Meeting::find($id);
        if ($m === null) {
            return ['ok' => false, 'error' => 'That meeting no longer exists.'];
        }
        if ($m['status'] !== 'draft' || $m['invitations_sent_at'] !== null) {
            return ['ok' => false, 'error' => 'Only a draft that was never sent can be deleted. Cancel the meeting instead.'];
        }
        Database::execute('DELETE FROM calendar_entries WHERE id = ?', [(int) $m['calendar_entry_id']]);       // the meeting and its attendees go with it (ON DELETE CASCADE)
        CalendarService::flush();
        AuditLog::record('meeting.delete', 'meeting', $id, 'Deleted a draft meeting');
        return ['ok' => true];
    }

    // ------------------------------------------------------------------------------------------------ attendees

    /**
     * Add invitees to a draft or scheduled meeting. Each item: email*, name, source (manual|member|user|previous|request), member_id, user_id.
     * Existing addresses are skipped; a REMOVED attendee who is added again is reactivated with a NEW token and a clean invite state.
     * Nothing is sent: call queue('invite_new') afterwards to invite only the new people.
     * opts: allow_bulk (the user holds meetings.bulk; needed for more than BULK_THRESHOLD new recipients at once).
     * @param list<array<string,mixed>> $list
     * @return array{ok:bool,errors:array<string,string>,added:int,reactivated:int,existing:int,ids:list<int>}
     */
    public static function addAttendees(int $meetingId, array $list, ?int $userId, array $opts = []): array
    {
        $empty = ['added' => 0, 'reactivated' => 0, 'existing' => 0, 'ids' => []];
        $m = Meeting::find($meetingId);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_' => 'That meeting no longer exists.']] + $empty;
        }
        if (!in_array($m['status'], ['draft', 'scheduled'], true)) {
            return ['ok' => false, 'errors' => ['_' => 'People can only be added to a draft or a scheduled meeting.']] + $empty;
        }
        $existing = [];
        foreach (Database::all('SELECT id, email, removed_at, invite_sent_at FROM meeting_attendees WHERE meeting_id = ?', [$meetingId]) as $r) {
            $existing[(string) $r['email']] = $r;
        }
        $active = count(array_filter($existing, static fn(array $r): bool => $r['removed_at'] === null));
        $todo = [];
        $already = 0;
        foreach ($list as $item) {
            $item = self::resolveItem((array) $item);
            if ($item === null) {
                continue;
            }
            $email = InviteeParser::cleanAddress((string) ($item['email'] ?? ''));
            if ($email === '' || !\App\Core\Mailer::validEmail($email) || strlen($email) > InviteeParser::MAX_EMAIL) {
                continue;
            }
            if (isset($todo[$email])) {
                continue;
            }
            if (isset($existing[$email]) && $existing[$email]['removed_at'] === null) {
                $already++;
                continue;
            }
            $source = in_array($item['source'] ?? '', MeetingAttendee::SOURCES, true) ? (string) $item['source'] : 'manual';
            $todo[$email] = [
                'name' => InviteeParser::cleanName((string) ($item['name'] ?? '')), 'source' => $source,
                'member_id' => !empty($item['member_id']) ? (int) $item['member_id'] : null, 'user_id' => !empty($item['user_id']) ? (int) $item['user_id'] : null,
            ];
        }
        if ($active + count($todo) > self::MAX_ATTENDEES) {
            return ['ok' => false, 'errors' => ['_' => 'A meeting can have at most ' . self::MAX_ATTENDEES . ' attendees (' . $active . ' already added).'], 'existing' => $already] + $empty;
        }
        if (count($todo) > self::BULK_THRESHOLD && empty($opts['allow_bulk'])) {
            return ['ok' => false, 'errors' => ['_' => 'Adding more than ' . self::BULK_THRESHOLD . ' people at once needs an administrator.'], 'existing' => $already] + $empty;
        }
        $added = 0;
        $re = 0;
        $ids = [];
        Database::transaction(function () use ($meetingId, $todo, $existing, &$added, &$re, &$ids): void {
            $reinvited = false;
            foreach ($todo as $email => $it) {
                if (isset($existing[$email])) {                         // a removed attendee comes back
                    $reinvited = $reinvited || $existing[$email]['invite_sent_at'] !== null;
                    Database::execute(
                        "UPDATE meeting_attendees SET removed_at = NULL, name = ?, source = ?, member_id = ?, user_id = ?, token = ?, rsvp = 'pending', rsvp_at = NULL,
                            invite_status = 'not_sent', queue_kind = NULL, invite_sequence = NULL, attempts = 0, last_error = NULL, invite_sent_at = NULL,
                            reminder_24h_sent_at = NULL, reminder_1h_sent_at = NULL, reminder_attempts = 0 WHERE id = ?",
                        [$it['name'], $it['source'], $it['member_id'], $it['user_id'], self::newToken(), (int) $existing[$email]['id']]
                    );
                    $ids[] = (int) $existing[$email]['id'];
                    $re++;
                    continue;
                }
                $ids[] = MeetingAttendee::create([
                    'meeting_id' => $meetingId, 'name' => $it['name'], 'email' => $email, 'source' => $it['source'], 'member_id' => $it['member_id'], 'user_id' => $it['user_id'],
                    'token' => self::newToken(),
                ]);
                $added++;
            }
            // Somebody who already held (and may have been told to drop) this meeting is invited again: iTIP wants the new REQUEST to outrank the earlier
            // CANCEL of the same UID, so SEQUENCE moves up one step (everybody who is up to date moves with it, nothing becomes "stale").
            if ($reinvited) {
                self::bumpSequence($meetingId);
            }
        });
        if ($added + $re > 0) {
            AuditLog::record('meeting.attendees_add', 'meeting', $meetingId, 'Added ' . ($added + $re) . ' attendee(s)', ['count' => $added + $re, 'user_id' => $userId]);
        }
        return ['ok' => true, 'errors' => [], 'added' => $added, 'reactivated' => $re, 'existing' => $already, 'ids' => $ids];
    }

    /**
     * Never trust a posted address for a picked person: for source `member` / `user` the name and address are read from the database by id
     * (an approved member who was not anonymised; an active user). Unknown ids are dropped (null). Typed / earlier-meeting / request items pass through.
     * @param array<string,mixed> $item
     * @return array<string,mixed>|null
     */
    private static function resolveItem(array $item): ?array
    {
        $source = (string) ($item['source'] ?? 'manual');
        if ($source === 'member') {
            $r = Database::one("SELECT id, full_name, email FROM members WHERE id = ? AND status = 'approved' AND anonymised_at IS NULL AND email <> ''", [(int) ($item['member_id'] ?? 0)]);
            return $r === null ? null : ['name' => (string) $r['full_name'], 'email' => (string) $r['email'], 'source' => 'member', 'member_id' => (int) $r['id'], 'user_id' => null];
        }
        if ($source === 'user') {
            $r = Database::one('SELECT id, name, email FROM users WHERE id = ? AND is_active = 1', [(int) ($item['user_id'] ?? 0)]);
            return $r === null ? null : ['name' => (string) $r['name'], 'email' => (string) $r['email'], 'source' => 'user', 'member_id' => null, 'user_id' => (int) $r['id']];
        }
        $item['member_id'] = null;
        $item['user_id'] = null;
        return $item;
    }

    /**
     * Expand a bulk spec chosen in the form: `members_all` (every approved member with an address) or `members_type:ID` (one membership type).
     * Capped at MAX_BULK_MEMBERS; the caller needs the ability meetings.bulk when the result is larger than BULK_THRESHOLD.
     * @return array{items:list<array{name:string,email:string,source:string,member_id:int}>,total:int,capped:bool}|null null = unknown spec
     */
    public static function bulkItems(string $spec): ?array
    {
        if ($spec === 'members_all') {
            $r = self::membersForBulk(null);
        } elseif (preg_match('/^members_type:(\d{1,9})$/', $spec, $m) === 1) {
            $r = self::membersForBulk((int) $m[1]);
        } else {
            return null;
        }
        $r['items'] = array_map(static fn(array $i): array => $i + ['source' => 'member'], $r['items']);
        return $r;
    }

    /**
     * Remove an attendee (their token stops working at once). With $sendCancellation and an invitation already sent, the person is queued a
     * METHOD:CANCEL message (queue_kind cancel); otherwise anything waiting for them is dropped.
     * @return array{ok:bool,error?:string,queued_cancel?:bool}
     */
    public static function removeAttendee(int $meetingId, int $attendeeId, bool $sendCancellation, ?int $userId): array
    {
        $a = Database::one('SELECT * FROM meeting_attendees WHERE id = ? AND meeting_id = ?', [$attendeeId, $meetingId]);      // IDOR guard: the attendee must belong to the meeting
        $m = Meeting::find($meetingId);
        if ($a === null || $m === null || $a['removed_at'] !== null) {
            return ['ok' => false, 'error' => 'That person is not on this meeting.'];
        }
        $cancel = $sendCancellation && $a['invite_sent_at'] !== null && $m['status'] === 'scheduled';
        if ($cancel) {
            Database::transaction(function () use ($attendeeId, $meetingId): void {
                Database::execute("UPDATE meeting_attendees SET removed_at = ?, invite_status = 'queued', queue_kind = 'cancel', attempts = 0, last_error = NULL WHERE id = ?", [MeetingTime::now(), $attendeeId]);
                self::bumpSequence($meetingId);                    // a METHOD:CANCEL must outrank the last REQUEST this person received
            });
        } else {
            Database::execute("UPDATE meeting_attendees SET removed_at = ?, invite_status = IF(invite_status = 'queued', IF(invite_sent_at IS NULL, 'not_sent', 'sent'), invite_status), queue_kind = NULL WHERE id = ?", [MeetingTime::now(), $attendeeId]);
        }
        AuditLog::record('meeting.attendee_remove', 'meeting', $meetingId, 'Removed an attendee', ['attendee_id' => $attendeeId, 'cancellation' => $cancel, 'user_id' => $userId]);
        return ['ok' => true, 'queued_cancel' => $cancel];
    }

    /**
     * SEQUENCE +1 without changing what the meeting says: used when a CANCEL (a person was removed) or a re-invitation must outrank an earlier message of the
     * same UID. Everybody who is up to date (last message = the old SEQUENCE) moves up with it, so the bump never makes them "stale" or earns them an update mail;
     * people who were already behind stay behind. Run inside the caller's transaction when it needs one.
     */
    private static function bumpSequence(int $meetingId): int
    {
        $old = (int) Database::value('SELECT sequence FROM meetings WHERE id = ?', [$meetingId], 0);
        Database::execute('UPDATE meetings SET sequence = sequence + 1 WHERE id = ?', [$meetingId]);
        Database::execute('UPDATE meeting_attendees SET invite_sequence = ? WHERE meeting_id = ? AND removed_at IS NULL AND invite_sequence = ?', [$old + 1, $meetingId, $old]);
        return $old + 1;
    }

    /**
     * An invitee answers on their own page. Allowed while the meeting is scheduled, RSVP is switched on and it has not ended. Idempotent.
     * @return array{ok:bool,error:?string,code:string} code: ok | not_found | rsvp_off | cancelled | ended | invalid
     */
    public static function setRsvp(mixed $token, mixed $answer): array
    {
        $found = self::attendeeByToken($token);
        if ($found === null) {
            return ['ok' => false, 'error' => 'This link is not valid any more.', 'code' => 'not_found'];
        }
        ['attendee' => $a, 'meeting' => $m] = $found;
        $answer = is_string($answer) ? $answer : '';
        if (!in_array($answer, ['accepted', 'declined', 'tentative'], true)) {
            return ['ok' => false, 'error' => 'Choose Accept, Maybe or Decline.', 'code' => 'invalid'];
        }
        if ($m['status'] === 'cancelled') {
            return ['ok' => false, 'error' => 'This meeting has been cancelled.', 'code' => 'cancelled'];
        }
        if ($m['status'] === 'completed' || MeetingTime::hasEnded($m)) {
            return ['ok' => false, 'error' => 'This meeting has already taken place.', 'code' => 'ended'];
        }
        if ($m['status'] !== 'scheduled' || (int) $m['allow_rsvp'] !== 1) {
            return ['ok' => false, 'error' => 'Answers are not being collected for this meeting.', 'code' => 'rsvp_off'];
        }
        if ($a['rsvp'] !== $answer) {
            Database::execute('UPDATE meeting_attendees SET rsvp = ?, rsvp_at = ? WHERE id = ?', [$answer, MeetingTime::now(), (int) $a['id']]);
            AuditLog::record('meeting.rsvp', 'meeting', (int) $m['id'], 'An invitee answered', ['attendee_id' => (int) $a['id'], 'answer' => $answer]);
        }
        return ['ok' => true, 'error' => null, 'code' => 'ok'];
    }

    // ------------------------------------------------------------------------------------------------ the mail queue (state only; sending = MeetingMailer)

    /**
     * Mark attendees as "queued" for the batch sender. Scopes:
     *   invite_new      never-invited people (not removed, not declined) -> kind invite           (needs status scheduled)
     *   invite_pending  invite_new + people whose last attempt failed                              (scheduled)
     *   update_all      everyone not removed / declined whose last message is older than the current SEQUENCE: invited people get kind update,
     *                   never-invited people get kind invite                                       (scheduled)
     *   cancel_all      everyone who holds an invitation and is not removed -> kind cancel          (needs status cancelled)
     *   one             opts.attendee_id: resend to ONE person whatever their answer (invite / update / cancel by state)
     *   retry_failed    failed rows back to queued with the kind they had
     * Idempotent: a row already queued is never queued twice; attempts restart at 0.
     * @return array{ok:bool,queued:int,error?:string}
     */
    public static function queue(int $meetingId, string $scope, array $opts = []): array
    {
        $m = Meeting::find($meetingId);
        if ($m === null) {
            return ['ok' => false, 'queued' => 0, 'error' => 'That meeting no longer exists.'];
        }
        $seq = (int) $m['sequence'];
        $base = "meeting_id = ? AND removed_at IS NULL";
        $reset = "attempts = 0, last_error = NULL";
        $n = 0;
        if ($scope === 'cancel_all') {
            if ($m['status'] !== 'cancelled') {
                return ['ok' => false, 'queued' => 0, 'error' => 'The meeting is not cancelled.'];
            }
            $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = 'cancel', {$reset} WHERE {$base} AND invite_sent_at IS NOT NULL AND (queue_kind IS NULL OR queue_kind <> 'cancel' OR invite_status <> 'queued')", [$meetingId]);
        } elseif (in_array($scope, ['invite_new', 'invite_pending', 'update_all', 'one', 'retry_failed'], true)) {
            if ($m['status'] !== 'scheduled') {
                return ['ok' => false, 'queued' => 0, 'error' => 'Invitations can only be sent for a scheduled meeting.'];
            }
            if ($scope === 'invite_new') {
                $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = 'invite', {$reset} WHERE {$base} AND invite_sent_at IS NULL AND invite_status = 'not_sent' AND rsvp <> 'declined'", [$meetingId]);
            } elseif ($scope === 'invite_pending') {
                $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = IF(invite_sent_at IS NULL, 'invite', 'update'), {$reset} WHERE {$base} AND invite_status IN ('not_sent','failed') AND rsvp <> 'declined' AND (invite_sent_at IS NULL OR invite_sequence IS NULL OR invite_sequence < ?)", [$meetingId, $seq]);
            } elseif ($scope === 'update_all') {
                $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = IF(invite_sent_at IS NULL, 'invite', 'update'), {$reset} WHERE {$base} AND invite_status IN ('not_sent','sent','failed') AND rsvp <> 'declined' AND (invite_sent_at IS NULL OR invite_sequence IS NULL OR invite_sequence < ?)", [$meetingId, $seq]);
            } elseif ($scope === 'one') {
                $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = IF(invite_sent_at IS NULL, 'invite', 'update'), {$reset} WHERE {$base} AND id = ? AND invite_status <> 'queued'", [$meetingId, (int) ($opts['attendee_id'] ?? 0)]);
            } else {
                $n = Database::execute("UPDATE meeting_attendees SET invite_status = 'queued', queue_kind = COALESCE(queue_kind, IF(invite_sent_at IS NULL, 'invite', 'update')), {$reset} WHERE meeting_id = ? AND invite_status = 'failed'", [$meetingId]);
            }
        } else {
            return ['ok' => false, 'queued' => 0, 'error' => 'Unknown sending option.'];
        }
        if ($n > 0) {
            Database::execute('UPDATE meetings SET send_paused = 0, send_halt_reason = NULL WHERE id = ? AND send_paused = 1 AND send_halt_reason IS NOT NULL', [$meetingId]);
            AuditLog::record('meeting.queue', 'meeting', $meetingId, 'Queued ' . $n . ' message(s)', ['scope' => $scope, 'count' => $n]);
        }
        return ['ok' => true, 'queued' => $n];
    }

    /**
     * Take ownership of one queued row for this attempt: a compare-and-swap on `attempts` (the value you saw when you read the row; omitted = the current one),
     * so two senders that read the same row cannot both win. False = somebody else already handled it or it is no longer queued. (The named database lock
     * of the batch sender is the first line of defence; this is the second.)
     */
    public static function claim(int $attendeeId, ?int $seenAttempts = null): bool
    {
        $seenAttempts ??= (int) Database::value("SELECT attempts FROM meeting_attendees WHERE id = ?", [$attendeeId], 0);
        return Database::execute("UPDATE meeting_attendees SET attempts = attempts + 1 WHERE id = ? AND invite_status = 'queued' AND attempts = ?", [$attendeeId, $seenAttempts]) === 1;
    }

    /** The message reached the mail server. $sequence = the SEQUENCE that was sent. */
    public static function markSent(int $attendeeId, int $sequence): void
    {
        $a = Database::one('SELECT meeting_id, queue_kind FROM meeting_attendees WHERE id = ?', [$attendeeId]);
        Database::execute(
            "UPDATE meeting_attendees SET invite_status = 'sent', invite_sequence = ?, invite_sent_at = COALESCE(invite_sent_at, ?), last_error = NULL, queue_kind = NULL WHERE id = ?",
            [$sequence, MeetingTime::now(), $attendeeId]
        );
        if ($a !== null && in_array($a['queue_kind'], ['invite', 'update'], true)) {
            Database::execute('UPDATE meetings SET invitations_sent_at = COALESCE(invitations_sent_at, ?) WHERE id = ?', [MeetingTime::now(), (int) $a['meeting_id']]);
        }
    }

    /**
     * The attempt failed. A permanent failure, or the 3rd attempt, ends in `failed`; a transient one goes back to `queued` for the next batch.
     * @return string the new invite_status
     */
    public static function markFailed(int $attendeeId, string $error, bool $permanent): string
    {
        $error = mb_substr(trim((string) preg_replace('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+/', '[address]', $error)), 0, 250);
        $attempts = (int) Database::value('SELECT attempts FROM meeting_attendees WHERE id = ?', [$attendeeId], 0);
        $final = $permanent || $attempts >= MeetingAttendee::MAX_ATTEMPTS;
        Database::execute('UPDATE meeting_attendees SET invite_status = ?, last_error = ? WHERE id = ?', [$final ? 'failed' : 'queued', $error, $attendeeId]);
        return $final ? 'failed' : 'queued';
    }

    /** Give the attempt back (a configuration-level failure stops the whole batch; the row stays queued). */
    public static function release(int $attendeeId): void
    {
        Database::execute("UPDATE meeting_attendees SET attempts = GREATEST(attempts - 1, 0) WHERE id = ? AND invite_status = 'queued'", [$attendeeId]);
    }

    public static function pause(int $meetingId, string $reason = ''): void
    {
        Database::execute('UPDATE meetings SET send_paused = 1, send_halt_reason = ? WHERE id = ?', [$reason !== '' ? mb_substr($reason, 0, 250) : null, $meetingId]);
    }

    public static function resume(int $meetingId): void
    {
        Database::execute('UPDATE meetings SET send_paused = 0, send_halt_reason = NULL WHERE id = ?', [$meetingId]);
    }

    /** Meeting ids that have queued messages and are not paused (the cron job finishes these). @return list<int> */
    public static function meetingsWithQueue(): array
    {
        return array_map('intval', array_column(Database::all("SELECT DISTINCT a.meeting_id FROM meeting_attendees a JOIN meetings m ON m.id = a.meeting_id WHERE a.invite_status = 'queued' AND m.send_paused = 0 ORDER BY a.meeting_id"), 'meeting_id'));
    }

    /** Next queued rows of a meeting (oldest first). @return list<array<string,mixed>> */
    public static function queuedRows(int $meetingId, int $limit): array
    {
        return Database::all("SELECT * FROM meeting_attendees WHERE meeting_id = ? AND invite_status = 'queued' AND attempts < ? ORDER BY id ASC LIMIT ?", [$meetingId, MeetingAttendee::MAX_ATTEMPTS, max(1, $limit)]);
    }

    // ------------------------------------------------------------------------------------------------ reminders (selection and marks; the e-mail is MeetingMailer's)

    /**
     * Reminders that are due at $now: scheduled meetings with the option on, invitees who hold an invitation (not removed, not declined), nobody
     * reminded twice. The 24-hour reminder is sent while more than 2 hours remain; the 1-hour reminder until the meeting starts; a person invited
     * AFTER the reminder moment gets no reminder for it (they were not invited 24 hours ahead).
     * @return list<array{meeting:array<string,mixed>,attendee:array<string,mixed>,which:string}> which = '24h' | '1h'
     */
    public static function dueReminders(?string $now = null, int $limit = 200): array
    {
        $now ??= MeetingTime::now();
        $out = [];
        foreach (['24h' => 24, '1h' => 1] as $which => $hours) {
            $col = 'reminder_' . $which;
            $latest = $which === '24h' ? MeetingTime::addMinutes($now, 120) : MeetingTime::addMinutes($now, 10);          // start must be later than this (a 1-hour reminder 5 minutes before the start would only annoy)
            $rows = Database::all(
                "SELECT a.id AS attendee_id, m.id AS meeting_id FROM meeting_attendees a JOIN meetings m ON m.id = a.meeting_id
                 WHERE m.status = 'scheduled' AND m.{$col} = 1 AND m.start_at > ? AND DATE_SUB(m.start_at, INTERVAL {$hours} HOUR) <= ?
                   AND a.removed_at IS NULL AND a.rsvp <> 'declined' AND a.invite_status IN ('sent','queued') AND a.invite_sent_at IS NOT NULL
                   AND a.invite_sent_at <= DATE_SUB(m.start_at, INTERVAL {$hours} HOUR) AND a.{$col}_sent_at IS NULL AND a.reminder_attempts < ?
                 ORDER BY m.start_at, a.id LIMIT ?",
                [$latest, $now, MeetingAttendee::MAX_ATTEMPTS, $limit]
            );
            foreach ($rows as $r) {
                $m = Meeting::find((int) $r['meeting_id']);
                $a = MeetingAttendee::find((int) $r['attendee_id']);
                if ($m !== null && $a !== null) {
                    $out[] = ['meeting' => $m, 'attendee' => $a, 'which' => $which];
                }
            }
        }
        return $out;
    }

    /** Claim a reminder before sending it (atomic: two senders cannot both win). */
    public static function claimReminder(int $attendeeId, string $which): bool
    {
        $col = $which === '1h' ? 'reminder_1h_sent_at' : 'reminder_24h_sent_at';
        return Database::execute("UPDATE meeting_attendees SET {$col} = ? WHERE id = ? AND {$col} IS NULL", [MeetingTime::now(), $attendeeId]) === 1;
    }

    /** The reminder could not be sent: a transient failure releases the claim (next cron run retries, up to 3 tries); a permanent one keeps it. */
    public static function reminderFailed(int $attendeeId, string $which, bool $transient): void
    {
        $col = $which === '1h' ? 'reminder_1h_sent_at' : 'reminder_24h_sent_at';
        if ($transient) {
            Database::execute("UPDATE meeting_attendees SET {$col} = NULL, reminder_attempts = reminder_attempts + 1 WHERE id = ?", [$attendeeId]);
        }
    }

    // ------------------------------------------------------------------------------------------------ pickers (admin JSON)

    /** Approved members by name, e-mail or membership number. @return list<array{id:int,name:string,email:string,number:string,type:string}> */
    public static function searchMembers(string $q, int $limit = 20): array
    {
        $q = trim($q);
        if ($q === '' || mb_strlen($q) < 2) {
            return [];
        }
        $like = '%' . addcslashes(mb_substr($q, 0, 80), '%_\\') . '%';
        $rows = Database::all(
            "SELECT id, full_name, email, membership_number, type_name FROM members
             WHERE status = 'approved' AND anonymised_at IS NULL AND email <> '' AND (full_name LIKE ? OR email LIKE ? OR membership_number LIKE ?) ORDER BY full_name LIMIT ?",
            [$like, $like, $like, max(1, min(50, $limit))]
        );
        return array_map(static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['full_name'], 'email' => (string) $r['email'], 'number' => (string) $r['membership_number'], 'type' => (string) $r['type_name']], $rows);
    }

    /** Membership types with their number of APPROVED members that have an e-mail address ("add members of type X"). @return list<array{id:int,name:string,count:int}> */
    public static function memberTypeCounts(): array
    {
        $rows = Database::all("SELECT t.id, t.name, (SELECT COUNT(*) FROM members m WHERE m.type_id = t.id AND m.status = 'approved' AND m.anonymised_at IS NULL AND m.email <> '') AS n FROM membership_types t ORDER BY t.sort_order, t.name");
        return array_map(static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'count' => (int) $r['n']], $rows);
    }

    /** All approved members (optionally of one type) with an address, for "add all approved members". Capped at MAX_BULK_MEMBERS. @return array{items:list<array{name:string,email:string,member_id:int}>,total:int,capped:bool} */
    public static function membersForBulk(?int $typeId = null): array
    {
        $where = "status = 'approved' AND anonymised_at IS NULL AND email <> ''" . ($typeId !== null ? ' AND type_id = ?' : '');
        $params = $typeId !== null ? [$typeId] : [];
        $total = (int) Database::value("SELECT COUNT(*) FROM members WHERE {$where}", $params, 0);
        $rows = Database::all("SELECT id, full_name, email FROM members WHERE {$where} ORDER BY full_name LIMIT " . self::MAX_BULK_MEMBERS, $params);
        return ['items' => array_map(static fn(array $r): array => ['name' => (string) $r['full_name'], 'email' => strtolower((string) $r['email']), 'member_id' => (int) $r['id']], $rows), 'total' => $total, 'capped' => $total > self::MAX_BULK_MEMBERS];
    }

    /** Staff (active administrators and editors). @return list<array{id:int,name:string,email:string,role:string}> */
    public static function staff(): array
    {
        $rows = Database::all('SELECT id, name, email, role FROM users WHERE is_active = 1 ORDER BY name');
        return array_map(static fn(array $r): array => ['id' => (int) $r['id'], 'name' => (string) $r['name'], 'email' => strtolower((string) $r['email']), 'role' => (string) $r['role']], $rows);
    }

    /** People invited to earlier meetings (name or address contains $q), newest first. @return list<array{name:string,email:string}> */
    public static function previousAttendees(string $q, int $limit = 20): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $like = '%' . addcslashes(mb_substr($q, 0, 80), '%_\\') . '%';
        $rows = Database::all(
            "SELECT email, MAX(name) AS name, MAX(id) AS last_id FROM meeting_attendees WHERE email NOT LIKE 'erased-%@invalid.invalid' AND (email LIKE ? OR name LIKE ?) GROUP BY email ORDER BY last_id DESC LIMIT ?",
            [$like, $like, max(1, min(50, $limit))]
        );
        return array_map(static fn(array $r): array => ['name' => (string) $r['name'], 'email' => (string) $r['email']], $rows);
    }

    // ------------------------------------------------------------------------------------------------ export, dashboard, privacy

    /** Rows for the attendee CSV (header = CSV_HEADER); pass them to CsvExport::response (formula-safe). @return list<list<string>> */
    public static function csvRows(int $meetingId): array
    {
        $rows = [];
        foreach (self::attendees($meetingId) as $a) {
            $rows[] = [$a['name'], $a['email'], self::SOURCE_LABELS[$a['source']] ?? $a['source'], self::INVITE_LABELS[$a['invite_status']] ?? $a['invite_status'], (string) $a['invite_sent_at'],
                self::RSVP_LABELS[$a['rsvp']] ?? $a['rsvp'], (string) $a['rsvp_at'], (string) $a['reminder_24h_sent_at'], (string) $a['reminder_1h_sent_at']];
        }
        return $rows;
    }

    /** Dashboard tiles: "Meetings this week" and "New meeting requests" (null entries removed; empty when the user may not manage meetings). @return list<array<string,mixed>> */
    public static function dashboardTiles(): array
    {
        if (!self::available() || !can('meetings.manage')) {
            return [];
        }
        $today = MeetingTime::today();
        $from = CivilDate::weekStartOf($today, 1);
        $to = CivilDate::addDays($from, 6);
        $week = (int) Database::value("SELECT COUNT(*) FROM meetings WHERE status = 'scheduled' AND start_at >= ? AND start_at < ?", [$from . ' 00:00:00', CivilDate::addDays($to, 1) . ' 00:00:00'], 0);
        $next = Database::one("SELECT id, title, start_at, end_at FROM meetings WHERE status = 'scheduled' AND end_at > ? ORDER BY start_at ASC LIMIT 1", [MeetingTime::now()]);
        $new = (int) Database::value("SELECT COUNT(*) FROM meeting_requests WHERE status = 'new'", [], 0);
        return [
            ['icon' => 'calendar', 'value' => number_format($week), 'label' => 'Meetings this week', 'sub' => $next ? 'Next: ' . MeetingTime::rangeLabel((string) $next['start_at'], (string) $next['end_at']) : 'Nothing scheduled', 'url' => 'admin/meetings'],
            ['icon' => 'inbox', 'value' => number_format($new), 'label' => 'New meeting requests', 'url' => 'admin/meeting-requests', 'alert' => $new > 0],
        ];
    }

    public static function newRequestCount(): int
    {
        return self::available() ? (int) Database::value("SELECT COUNT(*) FROM meeting_requests WHERE status = 'new'", [], 0) : 0;
    }

    /**
     * Erase one person's address from meeting data (called when a member's personal data is erased; also usable on request). Attendee rows keep
     * their place (counts and the "never twice" guard stay) but lose name, address and token and are removed; a request row loses its personal data.
     * @return int rows changed
     */
    public static function erase(string $email, ?int $memberId = null): int
    {
        if (!self::available()) {
            return 0;
        }
        $email = strtolower(trim($email));
        $n = 0;
        $rows = Database::all('SELECT id FROM meeting_attendees WHERE email = ? OR (? IS NOT NULL AND member_id = ?)', [$email, $memberId, $memberId]);
        foreach ($rows as $r) {
            $n += Database::execute(
                "UPDATE meeting_attendees SET name = 'Erased', email = ?, token = ?, removed_at = COALESCE(removed_at, ?), invite_status = IF(invite_status = 'queued', 'not_sent', invite_status), queue_kind = NULL, last_error = NULL, member_id = NULL WHERE id = ?",
                ['erased-a' . (int) $r['id'] . '@invalid.invalid', self::newToken(), MeetingTime::now(), (int) $r['id']]
            );
        }
        foreach (Database::all('SELECT id FROM meeting_requests WHERE email = ?', [$email]) as $r) {
            $n += Database::execute(
                "UPDATE meeting_requests SET name = 'Erased on request', email = ?, phone = NULL, organisation = NULL, message = '(erased)', admin_notes = NULL, ip_hash = NULL WHERE id = ?",
                ['erased-r' . (int) $r['id'] . '@invalid.invalid', (int) $r['id']]
            );
        }
        if ($n > 0) {
            AuditLog::record('meeting.erase', 'meeting', null, 'Erased personal data from meeting records', ['rows' => $n, 'member_id' => $memberId]);
        }
        return $n;
    }
}
