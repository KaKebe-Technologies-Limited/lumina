<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Services\Calendar\IcsBuilder;

/**
 * iCalendar (RFC 5545 / iTIP RFC 5546) writer for ONE meeting and ONE recipient, built on the helpers of the calendar's IcsBuilder
 * (fold at 75 octets, TEXT escaping, VTIMEZONE Africa/Kampala). Pure PHP, deterministic when opts.now is given.
 *
 *   METHOD:REQUEST  invitation or update (same UID, higher SEQUENCE for an update); ONE ATTENDEE = the recipient, PARTSTAT=NEEDS-ACTION, RSVP=FALSE (answers go through the site's buttons)
 *   METHOD:CANCEL   cancellation (STATUS:CANCELLED, SEQUENCE >= the last REQUEST)
 *   METHOD:PUBLISH  the "Add to calendar" file of the invitee page (no RSVP flow; PARTSTAT shows the invitee's own answer)
 *
 * Meeting row keys used: uid, title, start_at, end_at, platform, join_url, passcode, dial_in, location, agenda, organiser_name, organiser_email,
 * sequence, allow_rsvp. Attendee keys: name, email, rsvp. Docs: docs/MEETINGS.md section 8.
 */
final class MeetingIcs
{
    public const PRODID = '-//Lumina Africa Foundation//Meetings//EN';
    public const METHODS = ['REQUEST', 'CANCEL', 'PUBLISH'];

    /**
     * @param array<string,mixed>      $meeting
     * @param array<string,mixed>|null $attendee the recipient (null = no ATTENDEE line)
     * @param array{now?:string,alarm_minutes?:?int,page_url?:string,cancel_reason?:string} $opts now = 'YYYYMMDDTHHMMSSZ' (tests)
     */
    public static function build(array $meeting, ?array $attendee, string $method = 'REQUEST', array $opts = []): string
    {
        $method = strtoupper($method);
        if (!in_array($method, self::METHODS, true)) {
            throw new \InvalidArgumentException('Unknown iCalendar method.');
        }
        $start = MeetingTime::normalise($meeting['start_at'] ?? null) ?? throw new \InvalidArgumentException('Meeting has no valid start.');
        $end = MeetingTime::normalise($meeting['end_at'] ?? null) ?? throw new \InvalidArgumentException('Meeting has no valid end.');
        $now = (string) ($opts['now'] ?? gmdate('Ymd\THis\Z'));
        $cancelled = $method === 'CANCEL';
        $tz = IcsBuilder::TZID;
        $compact = static fn(string $dt): string => str_replace(['-', ':', ' '], ['', '', 'T'], $dt);

        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:' . self::PRODID, 'CALSCALE:GREGORIAN', 'METHOD:' . $method];
        array_push($lines, ...IcsBuilder::vtimezone());
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . self::cleanLine((string) $meeting['uid']);
        $lines[] = 'DTSTAMP:' . $now;
        $lines[] = 'SEQUENCE:' . max(0, (int) ($meeting['sequence'] ?? 0));
        $lines[] = 'DTSTART;TZID=' . $tz . ':' . $compact($start);
        $lines[] = 'DTEND;TZID=' . $tz . ':' . $compact($end);
        $lines[] = 'SUMMARY:' . IcsBuilder::escapeText(($cancelled ? self::word('cancelled_prefix', 'Cancelled:') . ' ' : '') . (string) ($meeting['title'] ?? ''));

        $desc = self::description($meeting, $opts);
        if ($desc !== '') {
            $lines[] = 'DESCRIPTION:' . IcsBuilder::escapeText($desc);
        }
        $location = self::location($meeting);
        if ($location !== '') {
            $lines[] = 'LOCATION:' . IcsBuilder::escapeText($location);
        }
        $url = (string) ($meeting['join_url'] ?? '');
        if ($url === '' || preg_match('~^https?://~i', $url) !== 1) {
            $url = (string) ($opts['page_url'] ?? '');
        }
        if ($url !== '' && preg_match('~^https?://~i', $url) === 1) {
            $lines[] = 'URL:' . self::cleanLine($url);
        }
        $oEmail = self::mailto((string) ($meeting['organiser_email'] ?? ''));
        if ($oEmail !== '') {
            $lines[] = 'ORGANIZER;CN=' . self::param((string) ($meeting['organiser_name'] ?? '')) . ':mailto:' . $oEmail;
        }
        if ($attendee !== null && self::mailto((string) ($attendee['email'] ?? '')) !== '') {
            $part = match ((string) ($attendee['rsvp'] ?? 'pending')) {
                'accepted' => 'ACCEPTED', 'declined' => 'DECLINED', 'tentative' => 'TENTATIVE', default => 'NEEDS-ACTION',
            };
            if ($method === 'REQUEST') {
                $part = 'NEEDS-ACTION';            // an invitation always asks for an answer; a reminder of the earlier answer is the invitee page's job
            }
            // RSVP=FALSE on purpose: a native Accept / Decline in a mail app is an iTIP REPLY mailed to the ORGANIZER's own mailbox, never to this site, so the
            // answer would not be recorded here. The site's own Accept / Maybe / Decline buttons (in the e-mail and on the invitee page, linked from DESCRIPTION)
            // are the only answer path, which keeps the guest list, the reminders and the organiser's inbox consistent.
            $rsvp = 'FALSE';
            $cn = trim((string) ($attendee['name'] ?? ''));
            $lines[] = 'ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=' . $part . ';RSVP=' . $rsvp
                . ($cn !== '' ? ';CN=' . self::param($cn) : '') . ':mailto:' . self::mailto((string) $attendee['email']);
        }
        $lines[] = 'STATUS:' . ($cancelled ? 'CANCELLED' : 'CONFIRMED');
        $lines[] = 'TRANSP:' . ($cancelled ? 'TRANSPARENT' : 'OPAQUE');
        $lines[] = 'CLASS:PUBLIC';
        $alarm = $opts['alarm_minutes'] ?? null;
        if (!$cancelled && is_int($alarm) && $alarm > 0 && $alarm <= 10080) {
            array_push($lines, 'BEGIN:VALARM', 'ACTION:DISPLAY', 'DESCRIPTION:' . IcsBuilder::escapeText(MeetingRenderer::fill(self::word('alarm_text', 'Reminder: {title}'), ['title' => (string) ($meeting['title'] ?? '')])), 'TRIGGER:-PT' . $alarm . 'M', 'END:VALARM');
        }
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", array_map([IcsBuilder::class, 'fold'], $lines)) . "\r\n";
    }

    /** A new UID for a new meeting: stable for its whole life. */
    public static function newUid(?string $host = null): string
    {
        $h = strtolower((string) preg_replace('/[^A-Za-z0-9.\-]/', '', $host ?? self::host()));
        return 'lumina-meet-' . bin2hex(random_bytes(12)) . '@' . ($h !== '' ? $h : IcsBuilder::DEFAULT_HOST);
    }

    /** Host part of the UIDs: the APP_URL host, else the live domain. */
    public static function host(): string
    {
        $h = (string) parse_url((string) \App\Core\Config::get('app.url', ''), PHP_URL_HOST);
        return $h !== '' ? $h : IcsBuilder::DEFAULT_HOST;
    }

    /** What the calendar shows as the place: the address for an in-person meeting, otherwise the join link (else any address). */
    public static function location(array $m): string
    {
        $loc = trim((string) ($m['location'] ?? ''));
        $url = trim((string) ($m['join_url'] ?? ''));
        if (($m['platform'] ?? '') === 'in_person') {
            return $loc !== '' ? $loc : $url;
        }
        $dial = in_array($m['platform'] ?? '', ['phone', 'whatsapp'], true) ? trim((string) ($m['dial_in'] ?? '')) : '';       // a phone meeting: the number is the "place"
        return $url !== '' ? $url : ($loc !== '' ? $loc : $dial);
    }

    /** Plain-text DESCRIPTION: agenda, then the join details. */
    public static function description(array $m, array $opts = []): string
    {
        $parts = [];
        $agenda = MeetingRenderer::agendaPlain($m);
        if ($agenda !== '') {
            $parts[] = $agenda;
        }
        $join = [];
        $platform = MeetingService::platformLabel((string) ($m['platform'] ?? 'other'));
        if (($m['join_url'] ?? '') !== '') {
            $join[] = MeetingRenderer::fill(self::word('join_label', 'Join ({platform})'), ['platform' => $platform]) . ': ' . $m['join_url'];
        }
        if (($m['passcode'] ?? '') !== '') {
            $join[] = self::word('passcode_label', 'Passcode') . ': ' . $m['passcode'];
        }
        if (($m['dial_in'] ?? '') !== '') {
            $join[] = self::word('dial_label', 'Dial-in') . ': ' . $m['dial_in'];
        }
        if (($m['location'] ?? '') !== '' && ($m['platform'] ?? '') === 'in_person') {
            $join[] = self::word('location_label', 'Location') . ': ' . $m['location'];
        }
        if ($join !== []) {
            $parts[] = implode("\n", $join);
        }
        if (($opts['cancel_reason'] ?? '') !== '') {
            array_unshift($parts, self::word('cancelled_note', 'This meeting was cancelled.') . ' ' . $opts['cancel_reason']);
        }
        if (($opts['page_url'] ?? '') !== '') {
            $parts[] = self::word('details_label', 'Answer and details') . ': ' . $opts['page_url'];
        }
        return implode("\n\n", $parts);
    }

    /** A word of the calendar file from the registry (Admin > Pages > Meetings > calendar file); the default when it is blank. */
    private static function word(string $key, string $fallback): string
    {
        $t = trim(MeetingRenderer::t('ics.' . $key));
        return $t !== '' ? $t : $fallback;
    }

    /** A parameter value (CN): always quoted, no quotes or control characters inside. */
    public static function param(string $v): string
    {
        $v = trim((string) preg_replace('/[\x00-\x1F\x7F"]+/u', ' ', $v));
        return '"' . ($v !== '' ? mb_substr($v, 0, 100) : 'Guest') . '"';
    }

    /** An address safe for a mailto: URI ('' when it is not a plain address). */
    public static function mailto(string $email): string
    {
        $e = trim($email);
        return preg_match('/^[^\s@<>,;:"]+@[A-Za-z0-9.\-]+$/', $e) === 1 ? $e : '';
    }

    private static function cleanLine(string $s): string
    {
        return (string) preg_replace('/[\x00-\x1F\x7F]/', '', $s);
    }
}
