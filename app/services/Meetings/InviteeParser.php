<?php
declare(strict_types=1);

namespace App\Services\Meetings;

use App\Core\Mailer;

/**
 * The "smart box" parser: turns pasted text into a clean, de-duplicated list of invitees.
 *
 * Accepts addresses separated by commas, semicolons, spaces or new lines, plain ("a@b.org"), with a name ("Jane Doe <jane@b.org>",
 * "\"Doe, Jane\" <jane@b.org>"), with "mailto:" in front or wrapped in <>, ( ), quotes. Addresses are lower-cased, validated (ASCII, filter_var,
 * at most 190 characters, no header-injection characters), and de-duplicated (the first name wins). Names lose control characters, angle
 * brackets, quotes and commas and are cut to 100 characters. Nothing here touches the database.
 */
final class InviteeParser
{
    /** At most this many tokens are looked at in one paste (the rest is reported as truncated). */
    public const MAX_TOKENS = 1000;
    public const MAX_EMAIL = 190;
    public const MAX_NAME = 100;

    /**
     * @param array<int,string> $skip lower-case addresses to ignore silently-but-counted (already in the meeting)
     * @return array{valid:list<array{name:string,email:string}>,invalid:list<array{input:string,reason:string}>,duplicates:int,skipped:int,truncated:bool}
     */
    public static function parse(string $raw, array $skip = []): array
    {
        $raw = str_replace(["\r\n", "\r", "\0", "\u{FEFF}", "\u{00A0}"], ["\n", "\n", '', '', ' '], $raw);
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = (string) mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
        }
        $raw = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $raw);
        $tokens = [];      // each: [name, rawAddress]

        // 1) "Name <address>" forms (the name may be quoted and may contain commas); they are cut out of the text first
        $rest = (string) preg_replace_callback(
            '/(?<![^\s,;])(?:"([^"\n]*)"|((?:[^\s<>,;"@()]+[ \t]+)*[^\s<>,;"@()]+)?)[ \t]*<\s*([^<>\s,;"]*)\s*>/u',
            static function (array $m) use (&$tokens): string {
                $name = trim(($m[1] ?? '') !== '' ? $m[1] : ($m[2] ?? ''));
                $tokens[] = [$name, (string) $m[3]];
                return ' ';
            },
            $raw
        );
        // 2) everything else: bare addresses separated by commas, semicolons, spaces and new lines
        foreach (preg_split('/[\s,;]+/u', $rest, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $piece) {
            $tokens[] = ['', $piece];
        }

        $truncated = count($tokens) > self::MAX_TOKENS;
        if ($truncated) {
            $tokens = array_slice($tokens, 0, self::MAX_TOKENS);
        }
        $skipSet = array_fill_keys(array_map('strtolower', $skip), true);
        $seen = [];
        $valid = [];
        $invalid = [];
        $dups = 0;
        $skipped = 0;
        foreach ($tokens as [$name, $addr]) {
            $input = self::clip((string) ($name !== '' ? $name . ' <' . $addr . '>' : $addr), 80);
            // `raw` = the entry as it can be typed again (a name with a comma goes back in quotes, nothing is cut): the admin box refills with it so a typo can be fixed
            $raw = $name !== '' ? (preg_match('/[,;<>]/', $name) === 1 ? '"' . str_replace('"', '', $name) . '"' : $name) . ' <' . $addr . '>' : $addr;
            $raw = mb_substr($raw, 0, 400);
            $email = self::cleanAddress($addr);
            if ($email === '') {
                $invalid[] = ['input' => $input, 'raw' => $raw, 'reason' => 'Not a valid e-mail address.'];
                continue;
            }
            if (strlen($email) > self::MAX_EMAIL) {
                $invalid[] = ['input' => $input, 'raw' => $raw, 'reason' => 'That address is too long.'];
                continue;
            }
            if (!Mailer::validEmail($email) || !preg_match('/^[\x21-\x7E]+$/', $email) || !preg_match('/@[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z0-9-]{2,}$/', $email)) {
                $invalid[] = ['input' => $input, 'raw' => $raw, 'reason' => 'Not a valid e-mail address.'];
                continue;
            }
            if (isset($seen[$email])) {
                $dups++;
                continue;
            }
            $seen[$email] = true;
            if (isset($skipSet[$email])) {
                $skipped++;
                continue;
            }
            $valid[] = ['name' => self::cleanName($name), 'email' => $email];
        }
        return ['valid' => $valid, 'invalid' => $invalid, 'duplicates' => $dups, 'skipped' => $skipped, 'truncated' => $truncated];
    }

    /** One address from a token: strips mailto:, wrapping brackets and quotes, trailing punctuation; lower-cases. '' = unusable. */
    public static function cleanAddress(string $addr): string
    {
        $a = trim($addr);
        $a = (string) preg_replace('/^mailto:/i', '', $a);
        $a = trim($a, " \t\n\r\"'()[]{}<>.:");
        $a = strtolower($a);
        return preg_match('/^[^@\s]+@[^@\s]+$/', $a) === 1 ? $a : '';
    }

    /** A display name that is safe in e-mail headers, ICS parameters and HTML (still escape on output). */
    public static function cleanName(string $name): string
    {
        $n = (string) preg_replace('/[\x00-\x1F\x7F<>";:\\\\]+/u', ' ', $name);
        $n = trim((string) preg_replace('/\s+/u', ' ', $n));
        return mb_substr($n, 0, self::MAX_NAME);
    }

    private static function clip(string $s, int $max): string
    {
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max - 1) . "\u{2026}" : $s;
    }
}
