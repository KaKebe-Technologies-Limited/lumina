<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Database;
use App\Core\Log;
use App\Core\RateLimit;

/**
 * Virtual classes of the LAF Academy: a class is a scheduled live session (Google Meet, Zoom, Microsoft Teams, Jitsi or any https link) for the learners
 * of a course, or for every learner. The join link is shown ONLY to signed-in learners who may attend (never on the public list), a calendar file (.ics)
 * can be downloaded, learners can be e-mailed an invitation or a reminder, and a finished class can carry a recording link.
 * All times are stored and shown in the website time zone (East Africa Time).
 */
final class ClassService
{
    /** key => label */
    public const PLATFORMS = ['meet' => 'Google Meet', 'zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'jitsi' => 'Jitsi Meet (free, no account)', 'other' => 'Another website'];
    private const HOSTS = [
        'meet' => ['meet.google.com'], 'zoom' => ['zoom.us', 'zoom.com'], 'teams' => ['teams.microsoft.com', 'teams.live.com', 'teams.microsoft.us'],
        'jitsi' => ['meet.jit.si', '8x8.vc'],
    ];

    /** Mark classes whose end time has passed as completed (cheap; called before listing). */
    public static function settle(): void
    {
        Database::execute("UPDATE academy_classes SET status = 'completed' WHERE status = 'scheduled' AND end_at < NOW()");
    }

    /** A fresh free Jitsi room address (anyone with the address can join: it is only shown to learners who may attend). */
    public static function jitsiLink(): string
    {
        $room = 'LAF-Academy-' . bin2hex(random_bytes(6));
        return 'https://meet.jit.si/' . $room;
    }

    /** @return array{ok:bool,error:?string,url:?string} */
    public static function checkUrl(string $platform, string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return ['ok' => true, 'error' => null, 'url' => null];
        }
        if (strlen($url) > 500 || preg_match('/[\x00-\x20\x7f"<>]/', $url) === 1 || preg_match('~^https://~i', $url) !== 1) {
            return ['ok' => false, 'error' => 'The link must start with https:// and contain no spaces.', 'url' => null];
        }
        $p = parse_url($url);
        $host = strtolower((string) ($p['host'] ?? ''));
        if ($host === '' || isset($p['user']) || isset($p['pass']) || filter_var($host, FILTER_VALIDATE_IP) !== false || !str_contains($host, '.')) {
            return ['ok' => false, 'error' => 'That is not a valid meeting link.', 'url' => null];
        }
        $allowed = self::HOSTS[$platform] ?? null;
        if ($allowed !== null) {
            $okHost = false;
            foreach ($allowed as $h) {
                if ($host === $h || str_ends_with($host, '.' . $h)) {
                    $okHost = true;
                }
            }
            if (!$okHost) {
                return ['ok' => false, 'error' => 'This does not look like a ' . self::PLATFORMS[$platform] . ' link. Choose "Another website" if it is from a different service.', 'url' => null];
            }
        }
        return ['ok' => true, 'error' => null, 'url' => $url];
    }

    public static function find(int $id): ?array
    {
        return Database::one(
            'SELECT cl.*, c.title AS course_title, c.slug AS course_slug, u.name AS facilitator_name FROM academy_classes cl LEFT JOIN academy_courses c ON c.id = cl.course_id LEFT JOIN users u ON u.id = cl.facilitator_id WHERE cl.id = ? LIMIT 1',
            [$id]
        );
    }

    /** Learners that may attend a class (active, address confirmed when required). @return list<array<string,mixed>> */
    public static function audience(array $class, int $limit = 1000): array
    {
        $verified = LearnerAuth::requireVerification() ? ' AND l.email_verified_at IS NOT NULL' : '';
        if ($class['audience'] === 'course' && $class['course_id'] !== null) {
            return Database::all(
                'SELECT l.id, l.name, l.email FROM academy_learners l JOIN academy_enrollments e ON e.learner_id = l.id WHERE e.course_id = ? AND l.is_active = 1' . $verified . ' ORDER BY l.id LIMIT ' . $limit,
                [(int) $class['course_id']]
            );
        }
        return Database::all('SELECT l.id, l.name, l.email FROM academy_learners l WHERE l.is_active = 1' . $verified . ' ORDER BY l.id LIMIT ' . $limit);
    }

    /** May this signed-in learner see the join link of the class? */
    public static function mayJoin(?int $learnerId, array $class): bool
    {
        if ($learnerId === null || $class['status'] === 'cancelled') {
            return false;
        }
        if ($class['audience'] !== 'course' || $class['course_id'] === null) {
            return true;
        }
        return CourseService::enrollment($learnerId, (int) $class['course_id']) !== null;
    }

    /**
     * Classes for the public / learner page: upcoming (not cancelled, not over) and recent ones with a recording.
     * @return array{upcoming:list<array<string,mixed>>,recorded:list<array<string,mixed>>}
     */
    public static function listing(): array
    {
        self::settle();
        $base = 'SELECT cl.*, c.title AS course_title, c.slug AS course_slug, u.name AS facilitator_name FROM academy_classes cl LEFT JOIN academy_courses c ON c.id = cl.course_id LEFT JOIN users u ON u.id = cl.facilitator_id ';
        return [
            'upcoming' => Database::all($base . "WHERE cl.status = 'scheduled' AND cl.end_at >= NOW() AND (cl.course_id IS NULL OR c.status = 'published') ORDER BY cl.start_at ASC LIMIT 60"),
            'recorded' => Database::all($base . "WHERE cl.status = 'completed' AND cl.recording_url IS NOT NULL AND cl.recording_url <> '' AND (cl.course_id IS NULL OR c.status = 'published') ORDER BY cl.start_at DESC LIMIT 24"),
        ];
    }

    /** The next classes for the learner dashboard (classes of enrolled courses and open classes). @return list<array<string,mixed>> */
    public static function nextForLearner(int $learnerId, int $limit = 4): array
    {
        self::settle();
        return Database::all(
            "SELECT cl.*, c.title AS course_title, u.name AS facilitator_name FROM academy_classes cl LEFT JOIN academy_courses c ON c.id = cl.course_id LEFT JOIN users u ON u.id = cl.facilitator_id
             WHERE cl.status = 'scheduled' AND cl.end_at >= NOW() AND (cl.audience = 'all' OR cl.course_id IN (SELECT course_id FROM academy_enrollments WHERE learner_id = ?))
             ORDER BY cl.start_at ASC LIMIT " . (int) $limit,
            [$learnerId]
        );
    }

    // ------------------------------------------------------------------------------------------------------------- e-mails

    /** E-mail every learner who may attend. $reminder = the wording "reminder". Returns [sent, failed]. */
    public static function notify(array $class, bool $reminder = false): array
    {
        if ($class['status'] !== 'scheduled') {
            return [0, 0];
        }
        $sent = 0;
        $failed = 0;
        foreach (self::audience($class, 500) as $l) {
            if (!RateLimit::attempt('academy-class-mail:' . (int) $class['id'] . ':' . (int) $l['id'], 1, 3600)) {
                continue;
            }
            AcademyMail::classNotice($class, $l, $reminder) ? $sent++ : $failed++;
        }
        return [$sent, $failed];
    }

    /** Send the reminder of every class that starts within the configured number of hours. Run by bin/academy-reminders.php. @return int number of classes reminded */
    public static function sendDueReminders(): int
    {
        $hours = (int) setting('academy_class_reminder_hours', '24');
        if ($hours <= 0) {
            return 0;
        }
        $due = Database::all(
            "SELECT id FROM academy_classes WHERE status = 'scheduled' AND reminder_sent_at IS NULL AND start_at > NOW() AND start_at <= (NOW() + INTERVAL ? HOUR) ORDER BY start_at",
            [$hours]
        );
        $n = 0;
        foreach ($due as $d) {
            $class = self::find((int) $d['id']);
            if ($class === null) {
                continue;
            }
            Database::update('academy_classes', ['reminder_sent_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $class['id']]);
            [$sent] = self::notify($class, true);
            Log::info('Academy class reminder', ['class' => (int) $class['id'], 'sent' => $sent]);
            $n++;
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------------------- calendar file

    /** A calendar file for one class (UTC times). */
    public static function ics(array $class, bool $withLink): string
    {
        $tz = new \DateTimeZone('Africa/Kampala');
        $utc = new \DateTimeZone('UTC');
        $fmt = static fn(string $dt): string => (new \DateTimeImmutable($dt, $tz))->setTimezone($utc)->format('Ymd\THis\Z');
        $esc = static fn(string $s): string => str_replace(["\\", ";", ",", "\r\n", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"], $s);
        $desc = trim((string) $class['description']);
        if ($withLink && !empty($class['join_url'])) {
            $desc .= ($desc !== '' ? "\n\n" : '') . 'Join: ' . $class['join_url'] . (!empty($class['passcode']) ? "\nPasscode: " . $class['passcode'] : '');
        }
        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//LAF Academy//EN', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH', 'BEGIN:VEVENT',
            'UID:academy-class-' . (int) $class['id'] . '@' . (parse_url(abs_url(''), PHP_URL_HOST) ?: 'laf-academy'),
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . $fmt((string) $class['start_at']),
            'DTEND:' . $fmt((string) $class['end_at']),
            'SUMMARY:' . $esc('LAF Academy: ' . (string) $class['title']),
            'DESCRIPTION:' . $esc($desc),
            'STATUS:' . ($class['status'] === 'cancelled' ? 'CANCELLED' : 'CONFIRMED'),
        ];
        if ($withLink && !empty($class['join_url'])) {
            $lines[] = 'URL:' . $class['join_url'];
        }
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';
        $out = [];
        foreach ($lines as $l) {
            while (strlen($l) > 73) {                       // fold at 75 octets
                $out[] = substr($l, 0, 73);
                $l = ' ' . substr($l, 73);
            }
            $out[] = $l;
        }
        return implode("\r\n", $out) . "\r\n";
    }
}
