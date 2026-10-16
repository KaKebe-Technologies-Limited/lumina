<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Formula-injection-safe CSV writer. Cells starting with = + - @ TAB CR are prefixed with an apostrophe
 * (except strictly phone-shaped values such as "+256700123456", which get a leading space).
 *
 *   return CsvExport::response('subscribers-' . date('Y-m-d') . '.csv', ['Email', 'Status'], $rows);   // download
 *   CsvExport::cell('=HYPERLINK(...)')  // "'=HYPERLINK(...)"
 */
final class CsvExport
{
    public static function cell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        $s = (string) $value;
        // A strictly phone-shaped value ("+256 700 123456") is plain text, not a formula, and an apostrophe in front of every
        // international number would show up literally in Excel / Sheets: it gets a leading space instead.
        if (preg_match('/^\+[0-9][0-9 ().\-]{5,24}$/', $s) === 1) {
            return ' ' . $s;
        }
        return ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) ? "'" . $s : $s;
    }

    /** @param string[] $header  @param iterable<array> $rows lists or assoc arrays of scalars */
    public static function response(string $filename, array $header, iterable $rows): Response
    {
        return Response::csv($filename, $header, $rows);
    }

    /** Write a UTF-8 (BOM) CSV to a stream/path; used by Response::csv() and by tests. */
    public static function write(string $target, array $header, iterable $rows): void
    {
        $out = fopen($target, 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array_map([self::class, 'cell'], array_values($header)));
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'cell'], array_values((array) $row)));
        }
        fclose($out);
    }
}
