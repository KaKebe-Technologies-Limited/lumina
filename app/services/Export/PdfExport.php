<?php
declare(strict_types=1);

namespace App\Services\Export;

use App\Services\Membership\Card\PdfWriter;

/**
 * PDF of an ExportDocument with the dependency-free PdfWriter: A4 (landscape when the table has more than five columns), the standard letterhead on the first page,
 * a compact running header on the others, a table whose header row repeats on every page, zebra rows, wrapped cells, and "Page n of N" in the footer.
 */
final class PdfExport
{
    private const NAVY = '#122040';
    private const GOLD = '#C9973F';
    private const GREY = '#5B6478';
    private const INK = '#1D1E20';
    private const CREAM = '#F7F3EC';
    private const LINE = '#E4E1DA';
    private const MAX_LINES = 14;

    public static function render(ExportDocument $d): string
    {
        $cols = max(1, $d->columns());
        $landscape = $cols > 5;
        $W = $landscape ? 841.89 : 595.28;
        $H = $landscape ? 595.28 : 841.89;
        $M = 34.0;
        $usable = $W - 2 * $M;
        $fs = $cols > 12 ? 6.2 : ($cols > 9 ? 6.8 : ($cols > 6 ? 7.6 : 8.4));
        $pad = 3.0;
        $lead = $fs * 1.28;
        $letter = $d->letterhead;

        // ---- column widths from the content (first rows are enough to judge)
        $natural = [];
        $min = [];
        $sample = array_slice($d->rows, 0, 250);
        for ($c = 0; $c < $cols; $c++) {
            $nat = PdfWriter::textWidth((string) ($d->header[$c] ?? ''), 'helvetica-bold', $fs);
            $mn = 0.0;
            foreach (array_merge([(string) ($d->header[$c] ?? '')], array_column($sample, $c)) as $cell) {
                $cell = (string) $cell;
                $nat = max($nat, min(240.0, PdfWriter::textWidth(str_replace(["\r", "\n"], ' ', $cell), 'helvetica', $fs)));
                foreach (preg_split('/\s+/u', $cell, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $w) {
                    $mn = max($mn, min(70.0, PdfWriter::textWidth($w, 'helvetica', $fs)));
                }
            }
            $natural[$c] = $nat + 2 * $pad;
            $min[$c] = max(22.0, $mn + 2 * $pad);
        }
        $sumNat = array_sum($natural);
        $width = [];
        if ($sumNat <= $usable) {
            foreach ($natural as $c => $n) {
                $width[$c] = $n * $usable / $sumNat;
            }
        } else {
            $sumMin = array_sum($min);
            if ($sumMin >= $usable) {
                foreach ($min as $c => $n) {
                    $width[$c] = $n * $usable / $sumMin;
                }
            } else {
                $extra = $usable - $sumMin;
                $room = 0.0;
                foreach ($natural as $c => $n) {
                    $room += max(0.0, $n - $min[$c]);
                }
                foreach ($natural as $c => $n) {
                    $width[$c] = $min[$c] + ($room > 0 ? $extra * max(0.0, $n - $min[$c]) / $room : 0.0);
                }
            }
        }

        // ---- wrap every cell, measure every row
        $headLines = [];
        for ($c = 0; $c < $cols; $c++) {
            $headLines[$c] = self::wrap((string) ($d->header[$c] ?? ''), $width[$c] - 2 * $pad, $fs, 'helvetica-bold');
        }
        $headH = max(array_map('count', $headLines)) * $lead + 2 * $pad;
        $rows = [];
        foreach ($d->rows as $r) {
            $cells = [];
            $maxLines = 1;
            for ($c = 0; $c < $cols; $c++) {
                $lines = self::wrap((string) ($r[$c] ?? ''), $width[$c] - 2 * $pad, $fs, 'helvetica');
                if (count($lines) > self::MAX_LINES) {
                    $lines = array_slice($lines, 0, self::MAX_LINES);
                    $lines[self::MAX_LINES - 1] = rtrim($lines[self::MAX_LINES - 1]) . ' ...';
                }
                $cells[$c] = $lines;
                $maxLines = max($maxLines, count($lines));
            }
            $rows[] = ['cells' => $cells, 'h' => $maxLines * $lead + 2 * $pad];
        }

        // ---- first-page header block
        $firstBlock = self::headerBlock($letter, $d, $W - 2 * $M - 66, $fs);
        $firstTop = self::tableTop($firstBlock, $M);
        $nextTop = $M + 34.0;
        $bottom = $H - 40.0;

        // ---- paginate
        $pages = [[]];
        $y = $firstTop + $headH;
        foreach ($rows as $i => $row) {
            $limit = $bottom;
            if ($y + $row['h'] > $limit && $pages[count($pages) - 1] !== []) {
                $pages[] = [];
                $y = $nextTop + $headH;
            }
            $pages[count($pages) - 1][] = $i;
            $y += $row['h'];
        }
        $totalPages = count($pages);

        // ---- draw
        $pdf = new PdfWriter(['title' => $d->title, 'author' => $letter['org'], 'subject' => $d->title . ' - export', 'creator' => $letter['org']]);
        $logoName = null;
        if ($letter['logo'] !== null) {
            try {
                $logoName = $letter['logo']['ext'] === 'png' ? $pdf->addPng($letter['logo']['bytes']) : $pdf->addJpeg($letter['logo']['bytes']);
            } catch (\Throwable) {
                $logoName = null;
            }
        }
        foreach ($pages as $pi => $idxs) {
            $pdf->addPage($W, $H);
            if ($pi === 0) {
                $y = self::drawFirstHeader($pdf, $letter, $d, $firstBlock, $logoName, $W, $M);
                $y += 10.0;
            } else {
                self::drawRunningHeader($pdf, $letter, $d, $logoName, $W, $M);
                $y = $nextTop;
            }
            // table header
            $pdf->fill(self::NAVY);
            $pdf->rect($M, $y, $usable, $headH, 'f');
            $x = $M;
            for ($c = 0; $c < $cols; $c++) {
                $ty = $y + $pad + $fs;
                foreach ($headLines[$c] as $line) {
                    $pdf->text($x + $pad, $ty, $line, 'helvetica-bold', $fs, '#FFFFFF', 'l');
                    $ty += $lead;
                }
                $x += $width[$c];
            }
            $y += $headH;
            foreach ($idxs as $k => $ri) {
                $row = $rows[$ri];
                if ($k % 2 === 1) {
                    $pdf->fill(self::CREAM);
                    $pdf->rect($M, $y, $usable, $row['h'], 'f');
                }
                $x = $M;
                for ($c = 0; $c < $cols; $c++) {
                    $ty = $y + $pad + $fs;
                    foreach ($row['cells'][$c] as $line) {
                        if ($line !== '') {
                            $pdf->text($x + $pad, $ty, $line, 'helvetica', $fs, self::INK, 'l');
                        }
                        $ty += $lead;
                    }
                    $x += $width[$c];
                }
                $pdf->save();
                $pdf->stroke(self::LINE);
                $pdf->lineWidth(0.4);
                $pdf->line($M, $y + $row['h'], $M + $usable, $y + $row['h']);
                $pdf->restore();
                $y += $row['h'];
            }
            if ($pi === $totalPages - 1) {
                $note = $d->truncated ? 'Only the first ' . count($d->rows) . ' of ' . $d->total . ' records are shown. Use the CSV export for the full list.' : count($d->rows) . ' record' . (count($d->rows) === 1 ? '' : 's');
                $pdf->text($M, min($y + 14.0, $H - 46.0), $note, 'helvetica', 7.5, self::GREY, 'l');
            }
            self::drawFooter($pdf, $letter, $W, $H, $M, $pi + 1, $totalPages);
        }
        return $pdf->output();
    }

    // ------------------------------------------------------------------------------------------------------------- pieces

    /** @return array{lines:list<array{0:string,1:string,2:float,3:string}>,notes:list<string>,h:float} */
    private static function headerBlock(array $l, ExportDocument $d, float $maxW, float $fs): array
    {
        $lines = [[$l['org'], 'times-bold', 17.0, self::NAVY]];
        if ($l['tagline'] !== '') {
            $lines[] = [$l['tagline'], 'helvetica', 8.2, self::GOLD];
        }
        foreach (self::wrap($l['address'], $maxW, 8.2, 'helvetica') as $a) {
            $lines[] = [$a, 'helvetica', 8.2, self::GREY];
        }
        foreach (self::wrap($l['contact_line'], $maxW, 8.2, 'helvetica') as $a) {
            $lines[] = [$a, 'helvetica', 8.2, self::GREY];
        }
        if ($l['reg'] !== '') {
            $lines[] = [$l['reg'], 'helvetica', 7.6, self::GREY];
        }
        $h = 0.0;
        foreach ($lines as $ln) {
            $h += $ln[2] >= 12 ? 20.0 : 11.0;
        }
        $notes = $d->notes;
        if ($d->generated !== '') {
            array_unshift($notes, $d->generated);
        }
        return ['lines' => $lines, 'notes' => $notes, 'h' => $h];
    }

    /** The y where the table starts on the first page (must follow drawFirstHeader exactly). */
    private static function tableTop(array $block, float $M): float
    {
        $y = $M + 2.0;
        foreach ($block['lines'] as [$text, $font, $size, $color]) {
            $y += $size >= 12 ? 20.0 : 11.0;
        }
        $y = max($y, $M + 56.0) + 8.0 + 24.0 + count($block['notes']) * 11.5 + 6.0;
        return $y + 10.0;
    }

    /** @return float the y where the first-page header ends */
    private static function drawFirstHeader(PdfWriter $pdf, array $l, ExportDocument $d, array $block, ?string $logo, float $W, float $M): float
    {
        if ($logo !== null) {
            $pdf->image($logo, $M, $M, 56, 56);
        }
        $x = $M + ($logo !== null ? 66.0 : 0.0);
        $y = $M + 2.0;
        foreach ($block['lines'] as [$text, $font, $size, $color]) {
            $y += $size >= 12 ? 16.0 : 11.0;
            $pdf->text($x, $y, $text, $font, $size, $color, 'l');
            if ($size >= 12) {
                $y += 4.0;
            }
        }
        $y = max($y, $M + 56.0) + 8.0;
        $pdf->save();
        $pdf->stroke(self::GOLD);
        $pdf->lineWidth(1.4);
        $pdf->line($M, $y, $W - $M, $y);
        $pdf->stroke(self::NAVY);
        $pdf->lineWidth(0.5);
        $pdf->line($M, $y + 3, $W - $M, $y + 3);
        $pdf->restore();
        $y += 24.0;
        $pdf->text($M, $y, $d->title, 'times-bold', 16, self::NAVY, 'l');
        foreach ($block['notes'] as $n) {
            $y += 11.5;
            $pdf->text($M, $y, $n, 'helvetica', 8.2, self::GREY, 'l');
        }
        return $y + 6.0;
    }

    private static function drawRunningHeader(PdfWriter $pdf, array $l, ExportDocument $d, ?string $logo, float $W, float $M): void
    {
        $x = $M;
        if ($logo !== null) {
            $pdf->image($logo, $M, $M - 6, 22, 22);
            $x += 28.0;
        }
        $pdf->text($x, $M + 9.0, $l['org'], 'helvetica-bold', 9, self::NAVY, 'l');
        $pdf->text($W - $M, $M + 9.0, $d->title, 'helvetica', 8, self::GREY, 'r');
        $pdf->save();
        $pdf->stroke(self::GOLD);
        $pdf->lineWidth(1);
        $pdf->line($M, $M + 18.0, $W - $M, $M + 18.0);
        $pdf->restore();
    }

    private static function drawFooter(PdfWriter $pdf, array $l, float $W, float $H, float $M, int $page, int $pages): void
    {
        $pdf->save();
        $pdf->stroke(self::LINE);
        $pdf->lineWidth(0.6);
        $pdf->line($M, $H - 30.0, $W - $M, $H - 30.0);
        $pdf->restore();
        $left = $l['org'] . ($l['website'] !== '' ? '  |  ' . $l['website'] : '') . ($l['email'] !== '' ? '  |  ' . explode(', ', $l['email'])[0] : '');
        $pdf->text($M, $H - 18.0, $left, 'helvetica', 7, self::GREY, 'l');
        $pdf->text($W - $M, $H - 18.0, 'Page ' . $page . ' of ' . $pages, 'helvetica', 7, self::GREY, 'r');
    }

    /** @return list<string> */
    private static function wrap(string $text, float $max, float $size, string $font): array
    {
        $out = [];
        foreach (preg_split('/\R/u', $text) ?: [''] as $para) {
            $line = '';
            foreach (preg_split('/\s+/u', trim($para), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
                while (PdfWriter::textWidth($word, $font, $size) > $max && mb_strlen($word) > 1) {
                    // a word wider than the cell: cut it where it still fits
                    $cut = mb_strlen($word) - 1;
                    while ($cut > 1 && PdfWriter::textWidth(mb_substr($word, 0, $cut), $font, $size) > $max) {
                        $cut--;
                    }
                    if ($line !== '') {
                        $out[] = $line;
                        $line = '';
                    }
                    $out[] = mb_substr($word, 0, $cut);
                    $word = mb_substr($word, $cut);
                }
                $try = $line === '' ? $word : $line . ' ' . $word;
                if ($line !== '' && PdfWriter::textWidth($try, $font, $size) > $max) {
                    $out[] = $line;
                    $line = $word;
                } else {
                    $line = $try;
                }
            }
            $out[] = $line;
        }
        return $out === [] ? [''] : $out;
    }
}
