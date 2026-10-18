<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * RFC 5545 (iCalendar) writer. Pure PHP, deterministic when `now` is given.
 *
 *  - CRLF line endings, lines folded at 75 octets (never inside a UTF-8 sequence), TEXT values escaped (\\ \; \, \n).
 *  - All-day occurrences: DTSTART;VALUE=DATE and an EXCLUSIVE DTEND;VALUE=DATE (last day + 1).
 *  - Timed occurrences: DTSTART;TZID=Africa/Kampala with a VTIMEZONE (fixed +03:00, no daylight saving) in the calendar.
 *    No end time = no DTEND (a zero-length event); a multi-day timed occurrence without end time ends at 23:59 on its last day.
 *  - UID is stable per entry + original occurrence date (moving an occurrence keeps its UID, so subscribers update instead of duplicate).
 *  - A date that still needs confirmation is STATUS:TENTATIVE and says so in DESCRIPTION.
 *
 * Occurrence shape: OccurrenceExpander (+ type_name, abs_url added by CalendarService).
 */
final class IcsBuilder
{
    public const PRODID = '-//Lumina Africa Foundation//Calendar//EN';
    public const TZID = 'Africa/Kampala';
    public const DEFAULT_HOST = 'luminaafricafoundation.com';

    /**
     * @param list<array<string,mixed>> $occurrences
     * @param array{name?:string,description?:string,host?:string,now?:string,labels?:array{tbc?:string,source?:string}} $opts  now = 'YYYYMMDDTHHMMSSZ' (tests); labels = fixed words (registry calendar > ics)
     */
    public static function calendar(array $occurrences, array $opts = []): string
    {
        $host = self::host((string) ($opts['host'] ?? self::DEFAULT_HOST));
        $now = (string) ($opts['now'] ?? gmdate('Ymd\THis\Z'));
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:' . self::PRODID, 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH'];
        if (($opts['name'] ?? '') !== '') {
            $lines[] = 'X-WR-CALNAME:' . self::escapeText((string) $opts['name']);
        }
        if (($opts['description'] ?? '') !== '') {
            $lines[] = 'X-WR-CALDESC:' . self::escapeText((string) $opts['description']);
        }
        $lines[] = 'X-WR-TIMEZONE:' . self::TZID;
        $events = [];
        $timed = false;
        foreach ($occurrences as $occ) {
            $ev = self::eventLines($occ, $host, $now, (array) ($opts['labels'] ?? []));
            if ($ev !== []) {
                $events[] = $ev;
                $timed = $timed || empty($occ['all_day']);
            }
        }
        if ($timed) {
            array_push($lines, ...self::vtimezone());
        }
        foreach ($events as $ev) {
            array_push($lines, ...$ev);
        }
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    /** @return list<string> */
    public static function vtimezone(): array
    {
        return ['BEGIN:VTIMEZONE', 'TZID:' . self::TZID, 'BEGIN:STANDARD', 'DTSTART:19700101T000000', 'TZOFFSETFROM:+0300', 'TZOFFSETTO:+0300', 'TZNAME:EAT', 'END:STANDARD', 'END:VTIMEZONE'];
    }

    /** Unfolded content lines of one VEVENT ([] when the occurrence is unusable). @return list<string> */
    public static function eventLines(array $occ, string $host, string $dtstamp, array $labels = []): array
    {
        $sd = (string) ($occ['start_date'] ?? '');
        $ed = (string) ($occ['end_date'] ?? $sd);
        if (!CivilDate::valid($sd) || !CivilDate::valid($ed) || $ed < $sd) {
            return [];
        }
        $lines = ['BEGIN:VEVENT', 'UID:' . self::uid($occ, $host), 'DTSTAMP:' . $dtstamp];
        $compact = static fn(string $d): string => str_replace('-', '', $d);
        $st = CivilDate::time($occ['start_time'] ?? null);
        if (!empty($occ['all_day']) || $st === null) {
            $lines[] = 'DTSTART;VALUE=DATE:' . $compact($sd);
            $lines[] = 'DTEND;VALUE=DATE:' . $compact(CivilDate::addDays($ed, 1));
            $lines[] = 'TRANSP:TRANSPARENT';
        } else {
            $et = CivilDate::time($occ['end_time'] ?? null);
            $lines[] = 'DTSTART;TZID=' . self::TZID . ':' . $compact($sd) . 'T' . str_replace(':', '', $st) . '00';
            if ($ed > $sd) {
                $lines[] = 'DTEND;TZID=' . self::TZID . ':' . $compact($ed) . 'T' . str_replace(':', '', $et ?? '23:59') . '00';
            } elseif ($et !== null && $et > $st) {
                $lines[] = 'DTEND;TZID=' . self::TZID . ':' . $compact($sd) . 'T' . str_replace(':', '', $et) . '00';
            }
        }
        $lines[] = 'SUMMARY:' . self::escapeText((string) ($occ['title'] ?? ''));
        $desc = self::description($occ, $labels);
        if ($desc !== '') {
            $lines[] = 'DESCRIPTION:' . self::escapeText($desc);
        }
        if (($occ['location'] ?? '') !== '') {
            $lines[] = 'LOCATION:' . self::escapeText((string) $occ['location']);
        }
        if (($occ['abs_url'] ?? '') !== '' && preg_match('~^https?://~i', (string) $occ['abs_url'])) {
            $lines[] = 'URL:' . self::cleanLine((string) $occ['abs_url']);
        }
        if (($occ['type_name'] ?? '') !== '') {
            $lines[] = 'CATEGORIES:' . self::escapeText((string) $occ['type_name']);
        }
        $lines[] = !empty($occ['needs_confirmation']) ? 'STATUS:TENTATIVE' : 'STATUS:CONFIRMED';
        $lines[] = 'SEQUENCE:0';
        $lines[] = 'END:VEVENT';
        return $lines;
    }

    /** Plain-text description: summary text, "date to be confirmed" notice, source, link. */
    public static function description(array $occ, array $labels = []): string
    {
        $parts = [];
        $text = self::plainText((string) ($occ['description'] ?? ''));
        if ($text !== '') {
            $parts[] = $text;
        }
        if (!empty($occ['needs_confirmation'])) {
            $parts[] = (string) ($labels['tbc'] ?? 'Date to be confirmed.');
        }
        if (($occ['note'] ?? '') !== '') {
            $parts[] = (string) $occ['note'];
        }
        if (($occ['source_label'] ?? '') !== '') {
            $parts[] = ((string) ($labels['source'] ?? 'Source:')) . ' ' . $occ['source_label'];
        }
        if (($occ['link_url'] ?? '') !== '' && preg_match('~^https?://~i', (string) $occ['link_url'])) {
            $parts[] = (string) $occ['link_url'];
        }
        return implode("\n\n", $parts);
    }

    public static function uid(array $occ, string $host): string
    {
        $key = preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($occ['key'] ?? ($occ['entry_id'] ?? 'x') . '-' . ($occ['occurrence_date'] ?? ''))) ?? 'x';
        return 'lumina-cal-' . trim($key, '-') . '@' . self::host($host);
    }

    private static function host(string $host): string
    {
        $h = strtolower((string) preg_replace('/[^A-Za-z0-9.\-]/', '', $host));
        return $h !== '' ? $h : self::DEFAULT_HOST;
    }

    /** TEXT escaping of RFC 5545 section 3.3.11 (also removes control characters). */
    public static function escapeText(string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $s);
        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $s);
    }

    private static function cleanLine(string $s): string
    {
        return (string) preg_replace('/[\x00-\x1F\x7F]/', '', $s);
    }

    /** Fold one content line at 75 octets (RFC 5545 section 3.1), keeping UTF-8 sequences whole. */
    public static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = '';
        $limit = 75;
        $rest = $line;
        while (strlen($rest) > $limit) {
            $cut = $limit;
            while ($cut > 0 && (ord($rest[$cut]) & 0xC0) === 0x80) {      // do not cut inside a multi-byte character
                $cut--;
            }
            $out .= substr($rest, 0, $cut) . "\r\n ";
            $rest = substr($rest, $cut);
            $limit = 74;                                                   // continuation lines start with one space
        }
        return $out . $rest;
    }

    /** Sanitised HTML (or plain text) to readable plain text. */
    public static function plainText(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $t = (string) preg_replace('~<\s*(br|/p|/li|/h[1-6]|/blockquote|/tr)\b[^>]*>~i', "\n", $html);
        $t = (string) preg_replace('~<\s*li\b[^>]*>~i', '- ', $t);
        $t = html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = (string) preg_replace("/[ \t]+/", ' ', $t);
        $t = (string) preg_replace("/\n[ \t]+/", "\n", $t);
        return trim((string) preg_replace("/\n{3,}/", "\n\n", $t));
    }
}
