<?php
/**
 * Tiny RFC 5545 (iCalendar) checker for the calendar feeds. Development tool, no dependencies.
 *
 *   php bin/dev/ics-validate.php http://localhost/lumina/calendar.ics [more URLs or files ...]
 *   php bin/dev/ics-validate.php --selftest        (feeds the checker deliberately broken files and expects it to complain)
 *
 * Checks (RFC 5545 section references in brackets): CRLF line ends and no bare LF / CR [3.1]; physical lines at most 75 octets [3.1]; valid UTF-8, and no
 * fold inside a multi-byte character [3.1.4]; content-line grammar and parameter quoting [3.1]; BEGIN / END nesting; VCALENDAR needs VERSION:2.0 and PRODID
 * once each [3.6]; VEVENT needs UID (unique in the file) and DTSTAMP (UTC form) and DTSTART [3.6.1]; DATE and DATE-TIME values are real dates and
 * times [3.3.4, 3.3.5]; DTEND is after DTSTART, has the same value type, and an all-day DTEND is exclusive [3.8.2.2]; no DTEND together with DURATION;
 * every TZID used is defined by a VTIMEZONE of the file with TZOFFSETFROM / TZOFFSETTO [3.6.5]; TEXT values escape backslash, semicolon, comma and
 * newline and use no other backslash sequence [3.3.11]; STATUS / TRANSP values; SEQUENCE is an integer; URL is an absolute http(s) URI; no control
 * characters. Prints one line per problem; exit code 1 when anything is wrong.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

/** @return array{errors:list<string>,warnings:list<string>,events:int,timezones:list<string>} */
function ics_check(string $raw): array
{
    $err = [];
    $warn = [];
    $events = 0;
    $tzids = [];

    // ---- physical layer
    if ($raw === '') {
        return ['errors' => ['empty file'], 'warnings' => [], 'events' => 0, 'timezones' => []];
    }
    if (!mb_check_encoding($raw, 'UTF-8')) {
        $err[] = 'the file is not valid UTF-8';
    }
    if (preg_match('/(?<!\r)\n|\r(?!\n)/', $raw)) {
        $err[] = 'a bare LF or CR was found: every line must end with CRLF';
    }
    if (!str_ends_with($raw, "\r\n")) {
        $err[] = 'the file does not end with CRLF';
    }
    $physical = explode("\r\n", $raw);
    array_pop($physical);                       // text after the last CRLF (empty when the file ends correctly)
    foreach ($physical as $i => $line) {
        if (strlen($line) > 75) {
            $err[] = 'line ' . ($i + 1) . ' is ' . strlen($line) . ' octets (the limit is 75): ' . substr($line, 0, 40) . '...';
        }
        if (!mb_check_encoding($line, 'UTF-8')) {
            $err[] = 'line ' . ($i + 1) . ' is not valid UTF-8 on its own: a fold splits a multi-byte character';
        }
        if (preg_match('/[\x00-\x08\x0A-\x1F\x7F]/', $line)) {
            $err[] = 'line ' . ($i + 1) . ' contains a control character';
        }
        if ($i > 0 && $line === '') {
            $err[] = 'line ' . ($i + 1) . ' is empty';
        }
    }

    // ---- unfold
    $logical = [];
    foreach ($physical as $line) {
        if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t") && $logical !== []) {
            $logical[count($logical) - 1] .= substr($line, 1);
        } else {
            $logical[] = $line;
        }
    }

    // ---- content lines
    $stack = [];
    $cal = ['VERSION' => 0, 'PRODID' => 0];
    $uids = [];
    $ev = null;
    $tz = null;
    $used = [];
    $tzNeeds = [];
    $textProps = ['SUMMARY', 'DESCRIPTION', 'LOCATION', 'COMMENT', 'X-WR-CALNAME', 'X-WR-CALDESC'];
    foreach ($logical as $n => $line) {
        $ln = 'content line ' . ($n + 1);
        if (!preg_match('/^([A-Za-z0-9-]+)((?:;[A-Za-z0-9-]+=(?:"[^"]*"|[^";:,]*)(?:,(?:"[^"]*"|[^";:,]*))*)*):(.*)$/s', $line, $m)) {
            $err[] = "$ln does not match name[;param=value]:value: " . substr($line, 0, 50);
            continue;
        }
        $name = strtoupper($m[1]);
        $params = [];
        if ($m[2] !== '') {
            foreach (explode(';', substr($m[2], 1)) as $p) {
                [$k, $v] = explode('=', $p, 2);
                $params[strtoupper($k)] = trim($v, '"');
            }
        }
        $val = $m[3];

        if ($name === 'BEGIN') {
            $comp = strtoupper($val);
            if ($stack === [] && $comp !== 'VCALENDAR') {
                $err[] = "$ln: the file must start with BEGIN:VCALENDAR";
            }
            if ($stack === [] && $n !== 0) {
                $err[] = "$ln: BEGIN:VCALENDAR must be the first line";
            }
            $stack[] = $comp;
            if ($comp === 'VEVENT') {
                $ev = ['props' => [], 'line' => $n + 1];
                $events++;
            } elseif ($comp === 'VTIMEZONE') {
                $tz = ['props' => [], 'subs' => []];
            } elseif (($comp === 'STANDARD' || $comp === 'DAYLIGHT') && $tz !== null) {
                $tz['subs'][] = ['name' => $comp, 'props' => []];
            }
            continue;
        }
        if ($name === 'END') {
            $comp = strtoupper($val);
            $top = array_pop($stack);
            if ($top !== $comp) {
                $err[] = "$ln: END:$comp does not close BEGIN:" . ($top ?? 'nothing');
            }
            if ($comp === 'VEVENT' && $ev !== null) {
                foreach (['UID', 'DTSTAMP', 'DTSTART', 'SUMMARY'] as $req) {
                    if (!isset($ev['props'][$req])) {
                        $err[] = 'VEVENT at line ' . $ev['line'] . " has no $req";
                    }
                }
                if (isset($ev['props']['DTEND'], $ev['props']['DURATION'])) {
                    $err[] = 'VEVENT at line ' . $ev['line'] . ' has both DTEND and DURATION';
                }
                $s = $ev['props']['DTSTART'] ?? null;
                $e = $ev['props']['DTEND'] ?? null;
                if ($s !== null && $e !== null) {
                    $sd = ($s['params']['VALUE'] ?? '') === 'DATE';
                    $ed = ($e['params']['VALUE'] ?? '') === 'DATE';
                    if ($sd !== $ed) {
                        $err[] = 'VEVENT at line ' . $ev['line'] . ': DTSTART and DTEND use different value types';
                    } elseif ($e['value'] <= $s['value']) {
                        $err[] = 'VEVENT at line ' . $ev['line'] . ': DTEND (' . $e['value'] . ') is not after DTSTART (' . $s['value'] . ')' . ($sd ? ' (an all-day DTEND is exclusive: last day + 1)' : '');
                    }
                }
                $ev = null;
            }
            if ($comp === 'VTIMEZONE' && $tz !== null) {
                if (!isset($tz['props']['TZID'])) {
                    $err[] = 'VTIMEZONE without TZID';
                } else {
                    $tzids[$tz['props']['TZID']] = true;
                }
                if ($tz['subs'] === []) {
                    $err[] = 'VTIMEZONE without STANDARD or DAYLIGHT';
                }
                foreach ($tz['subs'] as $sub) {
                    foreach (['DTSTART', 'TZOFFSETFROM', 'TZOFFSETTO'] as $req) {
                        if (!isset($sub['props'][$req])) {
                            $err[] = 'VTIMEZONE ' . $sub['name'] . " has no $req";
                        }
                    }
                    foreach (['TZOFFSETFROM', 'TZOFFSETTO'] as $o) {
                        if (isset($sub['props'][$o]) && !preg_match('/^[+-]\d{4}(\d{2})?$/', $sub['props'][$o])) {
                            $err[] = "VTIMEZONE $o is not +HHMM: " . $sub['props'][$o];
                        }
                    }
                }
                $tz = null;
            }
            continue;
        }
        $in = end($stack) ?: '';
        if ($in === 'VCALENDAR') {
            if (isset($cal[$name])) {
                $cal[$name]++;
            }
            if ($name === 'VERSION' && $val !== '2.0') {
                $err[] = "$ln: VERSION must be 2.0";
            }
            if ($name === 'CALSCALE' && strtoupper($val) !== 'GREGORIAN') {
                $err[] = "$ln: CALSCALE must be GREGORIAN";
            }
            if ($name === 'METHOD' && !preg_match('/^[A-Z]+$/', $val)) {
                $err[] = "$ln: bad METHOD";
            }
        }
        if ($in === 'VTIMEZONE' && $tz !== null) {
            $tz['props'][$name] = $val;
        } elseif (($in === 'STANDARD' || $in === 'DAYLIGHT') && $tz !== null) {
            $tz['subs'][count($tz['subs']) - 1]['props'][$name] = $val;
        }
        if ($in === 'VEVENT' && $ev !== null) {
            if (isset($ev['props'][$name]) && in_array($name, ['UID', 'DTSTAMP', 'DTSTART', 'DTEND', 'SUMMARY', 'DESCRIPTION', 'LOCATION', 'STATUS', 'TRANSP', 'URL', 'SEQUENCE'], true)) {
                $err[] = "$ln: $name appears twice in one VEVENT";
            }
            $ev['props'][$name] = ['value' => $val, 'params' => $params];
            if ($name === 'UID') {
                if (isset($uids[$val])) {
                    $err[] = "$ln: duplicate UID $val";
                }
                $uids[$val] = true;
                if ($val === '' || preg_match('/\s/', $val)) {
                    $err[] = "$ln: UID is empty or contains whitespace";
                }
            } elseif ($name === 'DTSTAMP') {
                if (!preg_match('/^\d{8}T\d{6}Z$/', $val) || !ics_valid_datetime($val)) {
                    $err[] = "$ln: DTSTAMP must be a UTC date-time like 20260101T000000Z, found $val";
                }
            } elseif ($name === 'DTSTART' || $name === 'DTEND') {
                $isDate = ($params['VALUE'] ?? '') === 'DATE';
                if ($isDate) {
                    if (!preg_match('/^\d{8}$/', $val) || !ics_valid_date($val)) {
                        $err[] = "$ln: $name;VALUE=DATE needs a real date YYYYMMDD, found $val";
                    }
                    if (isset($params['TZID'])) {
                        $err[] = "$ln: a DATE value must not carry TZID";
                    }
                } else {
                    if (!preg_match('/^\d{8}T\d{6}Z?$/', $val) || !ics_valid_datetime($val)) {
                        $err[] = "$ln: $name needs a real date-time, found $val";
                    }
                    if (isset($params['TZID'])) {
                        $used[$params['TZID']] = true;
                        if (str_ends_with($val, 'Z')) {
                            $err[] = "$ln: a UTC value (Z) must not carry TZID";
                        }
                    } elseif (!str_ends_with($val, 'Z')) {
                        $warn[] = "$ln: floating time (no TZID, no Z) in $name";
                    }
                }
            } elseif (in_array($name, $textProps, true)) {
                $bad = ics_text_problem($val);
                if ($bad !== null) {
                    $err[] = "$ln: $name $bad";
                }
            } elseif ($name === 'CATEGORIES') {                // a list of TEXT values separated by unescaped commas
                foreach (preg_split('/(?<!\\\\),/', $val) ?: [] as $one) {
                    $bad = ics_text_problem($one);
                    if ($bad !== null) {
                        $err[] = "$ln: CATEGORIES value $bad";
                    }
                }
            } elseif ($name === 'STATUS') {
                if (!in_array($val, ['TENTATIVE', 'CONFIRMED', 'CANCELLED'], true)) {
                    $err[] = "$ln: STATUS $val is not TENTATIVE, CONFIRMED or CANCELLED";
                }
            } elseif ($name === 'TRANSP') {
                if (!in_array($val, ['OPAQUE', 'TRANSPARENT'], true)) {
                    $err[] = "$ln: TRANSP $val is not OPAQUE or TRANSPARENT";
                }
            } elseif ($name === 'SEQUENCE') {
                if (!preg_match('/^\d+$/', $val)) {
                    $err[] = "$ln: SEQUENCE must be an integer";
                }
            } elseif ($name === 'URL') {
                if (!preg_match('~^https?://[^\s]+$~i', $val)) {
                    $err[] = "$ln: URL must be an absolute http(s) address, found $val";
                }
            }
        }
    }
    if ($stack !== []) {
        $err[] = 'unclosed component(s): ' . implode(', ', $stack);
    }
    foreach ($cal as $k => $c) {
        if ($c !== 1) {
            $err[] = "VCALENDAR must contain exactly one $k (found $c)";
        }
    }
    foreach (array_keys($used) as $t) {
        if (!isset($tzids[$t])) {
            $err[] = "TZID $t is used but no VTIMEZONE defines it";
        }
    }
    return ['errors' => $err, 'warnings' => $warn, 'events' => $events, 'timezones' => array_keys($tzids)];
}

function ics_valid_date(string $d): bool
{
    return checkdate((int) substr($d, 4, 2), (int) substr($d, 6, 2), (int) substr($d, 0, 4));
}

function ics_valid_datetime(string $v): bool
{
    return ics_valid_date(substr($v, 0, 8)) && (int) substr($v, 9, 2) <= 23 && (int) substr($v, 11, 2) <= 59 && (int) substr($v, 13, 2) <= 60;
}

/** RFC 5545 TEXT: backslash, semicolon and comma must be escaped; only \\ \; \, \n \N are escape sequences. null = fine. */
function ics_text_problem(string $v): ?string
{
    $plain = (string) preg_replace('/\\\\[\\\\;,nN]/', '', $v);         // drop the valid escape pairs
    if (str_contains($plain, '\\')) {
        return 'has a backslash that is not part of an escape sequence (\\\\ \\; \\, \\n)';
    }
    if (str_contains($plain, ';')) {
        return 'has an unescaped semicolon';
    }
    if (str_contains($plain, ',')) {
        return 'has an unescaped comma';
    }
    return null;
}

function ics_report(string $label, string $raw): int
{
    $r = ics_check($raw);
    echo $label . ': ' . strlen($raw) . ' bytes, ' . $r['events'] . ' events, time zones: ' . ($r['timezones'] ? implode(',', $r['timezones']) : 'none') . "\n";
    foreach ($r['errors'] as $e) {
        echo "  ERROR   $e\n";
    }
    foreach ($r['warnings'] as $w) {
        echo "  warning $w\n";
    }
    echo $r['errors'] === [] ? "  ok: valid\n" : '  ' . count($r['errors']) . " problem(s)\n";
    return count($r['errors']);
}

if (isset($argv[0]) && realpath($argv[0]) !== __FILE__) {
    return;                                   // required by another script (bin/dev/test-calendar-web.php): only the functions above are wanted
}
$args = array_slice($argv, 1);
if ($args === ['--selftest']) {
    $good = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//t//t//EN\r\nBEGIN:VTIMEZONE\r\nTZID:Africa/Kampala\r\nBEGIN:STANDARD\r\nDTSTART:19700101T000000\r\nTZOFFSETFROM:+0300\r\nTZOFFSETTO:+0300\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n"
        . "BEGIN:VEVENT\r\nUID:a@x\r\nDTSTAMP:20260101T000000Z\r\nDTSTART;VALUE=DATE:20260105\r\nDTEND;VALUE=DATE:20260106\r\nSUMMARY:One\\, two\\; three\\nfour\r\nEND:VEVENT\r\n"
        . "BEGIN:VEVENT\r\nUID:b@x\r\nDTSTAMP:20260101T000000Z\r\nDTSTART;TZID=Africa/Kampala:20260105T100000\r\nDTEND;TZID=Africa/Kampala:20260105T120000\r\nSUMMARY:Two\r\nURL:https://example.org/x\r\nSTATUS:TENTATIVE\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
    $cases = [
        'good file' => [$good, 0],
        'bare LF' => [str_replace("SUMMARY:Two\r\n", "SUMMARY:Two\n", $good), 1],
        'line over 75 octets' => [str_replace('SUMMARY:Two', 'SUMMARY:' . str_repeat('x', 90), $good), 1],
        'missing UID' => [str_replace("UID:b@x\r\n", '', $good), 1],
        'duplicate UID' => [str_replace('UID:b@x', 'UID:a@x', $good), 1],
        'all-day DTEND not after DTSTART' => [str_replace('DTEND;VALUE=DATE:20260106', 'DTEND;VALUE=DATE:20260105', $good), 1],
        'unescaped comma in SUMMARY' => [str_replace('SUMMARY:Two', 'SUMMARY:Two, three', $good), 1],
        'unknown TZID' => [str_replace('TZID=Africa/Kampala:20260105T100000', 'TZID=Mars/Olympus:20260105T100000', $good), 1],
        'bad date 30 February' => [str_replace('20260105T100000', '20260230T100000', $good), 1],
        'unclosed VEVENT' => [str_replace("END:VEVENT\r\nEND:VCALENDAR", 'END:VCALENDAR', $good), 1],
        'fold inside a UTF-8 character' => [str_replace('SUMMARY:Two', "SUMMARY:" . str_repeat('x', 66) . "\xC3\r\n \xA9", $good), 1],
        'escaped backslash is fine' => [str_replace('SUMMARY:Two', 'SUMMARY:Two \\\\ end', $good), 0],
        'lone backslash in SUMMARY' => [str_replace('SUMMARY:Two', 'SUMMARY:Two \\x end', $good), 1],
        'bad STATUS' => [str_replace('STATUS:TENTATIVE', 'STATUS:MAYBE', $good), 1],
    ];
    $fail = 0;
    foreach ($cases as $name => [$raw, $minErrors]) {
        $n = count(ics_check($raw)['errors']);
        $ok = $minErrors === 0 ? $n === 0 : $n >= $minErrors;
        echo ($ok ? '  ok   ' : '  FAIL ') . $name . ' (' . $n . " problem(s))\n";
        $fail += $ok ? 0 : 1;
    }
    echo $fail === 0 ? "SELFTEST PASSED\n" : "SELFTEST FAILED\n";
    exit($fail === 0 ? 0 : 1);
}
if ($args === []) {
    fwrite(STDERR, "usage: php bin/dev/ics-validate.php <url|file> [...]  |  --selftest\n");
    exit(2);
}
$bad = 0;
foreach ($args as $src) {
    $raw = preg_match('~^https?://~i', $src)
        ? @file_get_contents($src, false, stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true, 'header' => "Accept: text/calendar\r\n"]]))
        : @file_get_contents($src);
    if ($raw === false) {
        echo "$src: cannot be read\n";
        $bad++;
        continue;
    }
    $bad += ics_report($src, $raw) > 0 ? 1 : 0;
}
exit($bad === 0 ? 0 : 1);
