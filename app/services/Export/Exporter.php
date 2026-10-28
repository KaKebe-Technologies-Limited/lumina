<?php
declare(strict_types=1);

namespace App\Services\Export;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

/**
 * One place that turns "a title, a header and rows" into a download in the format the person chose: CSV (plain data, for importing), Excel (.xlsx), Word (.docx) or
 * PDF. Every admin export goes through CsvExport::response(), which asks this class, so adding "?format=pdf" to any export link is all it takes. Excel, Word and PDF
 * carry the standard letterhead (logo, organisation, address, e-mail, phones, website) and an "exported by / on / filters" block; CSV stays pure data.
 */
final class Exporter
{
    /** format key => [label, extension, mime] */
    public const FORMATS = [
        'csv'  => ['CSV (data)', 'csv', 'text/csv; charset=utf-8'],
        'xlsx' => ['Excel (.xlsx)', 'xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'docx' => ['Word (.docx)', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'pdf'  => ['PDF', 'pdf', 'application/pdf'],
    ];

    /** Rows kept in Excel / Word / PDF (the CSV is never cut). */
    public const MAX_ROWS = 10000;

    /** The format asked for on this request ("format" in the address), defaulting to csv; "xls" and "doc" are accepted as Excel and Word. */
    public static function requested(): string
    {
        $f = strtolower(trim((string) (Request::current()?->query('format') ?? '')));
        $f = ['xls' => 'xlsx', 'excel' => 'xlsx', 'doc' => 'docx', 'word' => 'docx'][$f] ?? $f;
        return isset(self::FORMATS[$f]) ? $f : 'csv';
    }

    /** "academy-learners-2026-10-28.csv" -> "Academy learners" */
    public static function titleFromFilename(string $filename): string
    {
        $base = (string) preg_replace('/\.[A-Za-z0-9]+$/', '', $filename);
        $base = (string) preg_replace('/[-_]\d{4}-\d{2}-\d{2}$/', '', $base);
        $base = trim((string) preg_replace('/[-_.]+/', ' ', $base));
        return $base === '' ? 'Export' : mb_strtoupper(mb_substr($base, 0, 1)) . mb_substr($base, 1);
    }

    /** "status=new, q=john" lines from the address (page, format and similar controls are not filters). @return list<string> */
    public static function filterNotes(): array
    {
        $q = Request::current()?->all() ?? [];
        $skip = ['page', 'format', 'per_page', 'sort', 'dir', '_token', 'kind'];
        $bits = [];
        foreach ($q as $k => $v) {
            if (in_array($k, $skip, true) || !is_scalar($v) || trim((string) $v) === '') {
                continue;
            }
            $bits[] = ucfirst(str_replace('_', ' ', (string) $k)) . ': ' . mb_substr(trim((string) $v), 0, 60);
        }
        return $bits === [] ? [] : ['Filters: ' . implode('; ', $bits)];
    }

    /**
     * @param list<string>     $header
     * @param iterable<array>  $rows
     * @param array{title?:string,csv_only?:bool,notes?:list<string>} $meta
     */
    public static function response(string $filename, array $header, iterable $rows, array $meta = []): Response
    {
        $format = !empty($meta['csv_only']) ? 'csv' : self::requested();
        if ($format === 'csv') {
            return Response::csv($filename, $header, $rows);
        }
        [, $ext, $mime] = self::FORMATS[$format];
        $list = [];
        $total = 0;
        foreach ($rows as $row) {
            $total++;
            if ($total <= self::MAX_ROWS) {
                $list[] = array_map([self::class, 'cell'], array_values((array) $row));
            }
        }
        $user = Auth::user();
        $by = is_array($user) && trim((string) ($user['name'] ?? '')) !== '' ? ' by ' . trim((string) $user['name']) : '';
        $doc = new ExportDocument(
            (string) ($meta['title'] ?? self::titleFromFilename($filename)),
            array_map('strval', array_values($header)),
            $list,
            Letterhead::get(),
            array_merge(self::filterNotes(), $meta['notes'] ?? []),
            'Exported' . $by . ' on ' . ExportDocument::now(),
            $total > self::MAX_ROWS,
            $total,
        );
        $bytes = match ($format) {
            'xlsx' => XlsxExport::render($doc),
            'docx' => DocxExport::render($doc),
            default => PdfExport::render($doc),
        };
        $name = (string) preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) preg_replace('/\.[A-Za-z0-9]+$/', '', $filename)) . '.' . $ext;
        return new Response($bytes, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
            'Cache-Control'       => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private static function cell(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_bool($v)) {
            return $v ? 'Yes' : 'No';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i');
        }
        return trim((string) $v);
    }
}
