<?php
declare(strict_types=1);

namespace App\Services\Documents;

use App\Services\Membership\Card\PdfWriter;

/**
 * SIGNED COPIES of PDF documents. Two steps, both dependency free:
 *
 *   1. approvalPdf(): draws an "Approval and signatures" page (A4) with PdfWriter: the document, its version and approval authority, and one block per signatory (role, name, title,
 *      the signature image, the date signed or "Awaiting signature"), plus a fingerprint (SHA-256) of the ORIGINAL file.
 *   2. appendPages(): adds those pages to the end of the original PDF with an INCREMENTAL UPDATE (the original bytes stay exactly as they were; new objects, a new version of the
 *      page tree node and a new cross-reference section are appended). That works for the common PDF layout (a classic cross-reference table and a page tree whose root lists its
 *      pages), which is what ReportLab, LibreOffice and most office exports produce. For anything else (compressed cross-reference streams, a /Prev chain, an indirect /Kids)
 *      appendPages() returns null and the caller keeps the approval page as a separate PDF.
 *
 * The original file is never modified. Docs: docs/DOCUMENTS-SIGNATURES.md.
 */
final class PdfSigner
{
    private const NAVY = '#1F2A44';
    private const GOLD = '#B7852F';
    private const INK = '#2B2F36';
    private const GREY = '#5A6470';
    private const PAGE_W = 595.28;
    private const PAGE_H = 841.89;
    private const MARGIN = 56.0;

    /** @return list<string> $text wrapped to $max points */
    private static function wrap(string $text, string $font, float $size, float $max): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $w) {
            $try = $line === '' ? $w : $line . ' ' . $w;
            if ($line !== '' && PdfWriter::textWidth($try, $font, $size) > $max) {
                $lines[] = $line;
                $line = $w;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * The approval page(s) as a stand-alone PDF.
     * @param array{title:string,reference:string,version:?string,authority:?string,adoption:?string,org:string,fingerprint:string,generated:int} $doc
     * @param list<array{role:string,name:string,title:?string,signed_at:?string,image:?string}> $rows image = the signature picture bytes (PNG or JPEG) or null
     */
    public static function approvalPdf(array $doc, array $rows): string
    {
        $pdf = new PdfWriter(['title' => 'Approval and signatures: ' . $doc['title'], 'author' => $doc['org'], 'subject' => 'Approval page of ' . $doc['reference'], 'creator' => 'Lumina documents']);
        $W = self::PAGE_W;
        $H = self::PAGE_H;
        $M = self::MARGIN;
        $logo = null;
        try {
            $file = ASSET_PATH . '/img/brand/logo-192.png';
            if (is_file($file)) {
                $logo = $pdf->addPng((string) file_get_contents($file));
            }
        } catch (\Throwable) {
            $logo = null;
        }
        $images = [];
        foreach ($rows as $i => $r) {
            if (!empty($r['image'])) {
                $info = PdfWriter::imageInfo((string) $r['image']);
                if ($info !== null) {
                    try {
                        $images[$i] = ['name' => $info['mime'] === 'image/png' ? $pdf->addPng((string) $r['image']) : $pdf->addJpeg((string) $r['image']), 'w' => $info['w'], 'h' => $info['h']];
                    } catch (\Throwable) {
                        // an image the writer cannot place: the block shows an empty line (the controller checks pictures when they are uploaded)
                    }
                }
            }
        }
        $perFirst = 4;
        $perNext = 7;
        $blocks = array_keys($rows);
        $pageNo = 0;
        $pos = 0;
        do {
            $pdf->addPage($W, $H);
            $first = $pageNo === 0;
            self::header($pdf, $doc, $logo, $first);
            $y = $first ? self::summary($pdf, $doc) : 128.0;
            $cap = $first ? $perFirst : $perNext;
            for ($n = 0; $n < $cap && $pos < count($blocks); $n++, $pos++) {
                $i = $blocks[$pos];
                self::block($pdf, $rows[$i], $images[$i] ?? null, $y);
                $y += 96;
            }
            self::footer($pdf, $doc, $pageNo + 1);
            $pageNo++;
        } while ($pos < count($blocks));
        return $pdf->output();
    }

    private static function header(PdfWriter $pdf, array $doc, ?string $logo, bool $first): void
    {
        $W = self::PAGE_W;
        $pdf->fill(self::NAVY);
        $pdf->rect(0, 0, $W, $first ? 96 : 70, 'f');
        $pdf->fill(self::GOLD);
        $pdf->rect(0, $first ? 96 : 70, $W, 3, 'f');
        $x = self::MARGIN;
        if ($logo !== null) {
            $pdf->image($logo, $x, $first ? 20 : 12, $first ? 56 : 46, $first ? 56 : 46);
            $x += $first ? 70 : 60;
        }
        $pdf->text($x, $first ? 46 : 36, mb_strtoupper($doc['org']), 'helvetica-bold', 13, '#FFFFFF');
        $pdf->text($x, $first ? 66 : 54, 'APPROVAL AND SIGNATURES', 'helvetica-bold', 9.5, '#D6A151');
    }

    /** Title, document facts and statement; returns the y where the signature blocks start. */
    private static function summary(PdfWriter $pdf, array $doc): float
    {
        $M = self::MARGIN;
        $max = self::PAGE_W - 2 * $M;
        $y = 152.0;
        $pdf->text($M, $y, 'Approval and signatures', 'times-bold', 26, self::NAVY);
        $y += 30;
        $size = 15.0;
        $lines = self::wrap($doc['title'], 'helvetica-bold', $size, $max);
        foreach (array_slice($lines, 0, 3) as $l) {
            $pdf->text($M, $y, $l, 'helvetica-bold', $size, self::INK);
            $y += 20;
        }
        $y += 6;
        $facts = array_filter([
            'Reference' => $doc['reference'],
            'Version' => $doc['version'],
            'Approval authority' => $doc['authority'],
            'Date of adoption' => $doc['adoption'],
        ], static fn($v): bool => $v !== null && trim((string) $v) !== '');
        foreach ($facts as $k => $v) {
            $pdf->text($M, $y, $k . ':', 'helvetica-bold', 10.5, self::GREY);
            $pdf->text($M + 118, $y, (string) $v, 'helvetica', 10.5, self::INK);
            $y += 16;
        }
        $y += 8;
        $statement = 'This document was approved on behalf of ' . $doc['org'] . ' by the persons named below. Each signature was added by the foundation\'s administrator from the '
            . 'signature image provided by the signatory. This page was added to the approved text; the pages before it are unchanged.';
        foreach (self::wrap($statement, 'times-roman', 11, $max) as $l) {
            $pdf->text($M, $y, $l, 'times-roman', 11, self::INK);
            $y += 15;
        }
        $pdf->save();
        $pdf->stroke(self::GOLD);
        $pdf->lineWidth(1.2);
        $pdf->line($M, $y + 6, self::PAGE_W - $M, $y + 6);
        $pdf->restore();
        return $y + 26;
    }

    /** @param array{role:string,name:string,title:?string,signed_at:?string,image:?string} $r @param array{name:string,w:int,h:int}|null $img */
    private static function block(PdfWriter $pdf, array $r, ?array $img, float $y): void
    {
        $M = self::MARGIN;
        $W = self::PAGE_W;
        $pdf->text($M, $y + 12, mb_strtoupper($r['role']), 'helvetica-bold', 8.5, self::GOLD);
        $pdf->text($M, $y + 30, $r['name'], 'helvetica-bold', 12.5, self::NAVY);
        $ty = $y + 45;
        foreach (array_slice(self::wrap((string) ($r['title'] ?? ''), 'helvetica', 10, 210), 0, 2) as $l) {
            $pdf->text($M, $ty, $l, 'helvetica', 10, self::GREY);
            $ty += 13;
        }
        // signature box
        $bx = 290.0;
        $bw = 150.0;
        $bh = 54.0;
        $by = $y + 6;
        if ($img !== null) {
            $scale = min($bw / max(1, $img['w']), $bh / max(1, $img['h']));
            $w = $img['w'] * $scale;
            $h = $img['h'] * $scale;
            $pdf->image($img['name'], $bx + ($bw - $w) / 2, $by + ($bh - $h), $w, $h);
        }
        $pdf->save();
        $pdf->stroke(self::INK);
        $pdf->lineWidth(0.7);
        $pdf->line($bx, $by + $bh + 2, $bx + $bw, $by + $bh + 2);
        $pdf->restore();
        $pdf->text($bx, $by + $bh + 14, 'Signature', 'helvetica', 8, self::GREY);
        if ($r['signed_at'] !== null && $r['signed_at'] !== '') {
            $pdf->text($W - $M, $by + $bh - 2, 'Signed ' . date('j F Y', (int) strtotime($r['signed_at'])), 'helvetica-bold', 9.5, self::INK, 'r');
        } else {
            $pdf->text($W - $M, $by + $bh - 2, 'Awaiting signature', 'times-italic', 10, self::GREY, 'r');
        }
        $pdf->save();
        $pdf->stroke('#D9D5CC');
        $pdf->lineWidth(0.6);
        $pdf->line($M, $y + 84, $W - $M, $y + 84);
        $pdf->restore();
    }

    private static function footer(PdfWriter $pdf, array $doc, int $page): void
    {
        $M = self::MARGIN;
        $H = self::PAGE_H;
        $pdf->text($M, $H - 56, 'Document fingerprint (SHA-256 of the original file):', 'helvetica', 7.5, self::GREY);
        $pdf->text($M, $H - 45, $doc['fingerprint'], 'helvetica', 7.5, self::INK);
        $pdf->text($M, $H - 31, $doc['reference'] . '  |  approval page generated ' . date('j F Y', $doc['generated']), 'helvetica', 7.5, self::GREY);
        $pdf->text(self::PAGE_W - $M, $H - 31, 'Approval page ' . $page, 'helvetica', 7.5, self::GREY, 'r');
    }

    // ------------------------------------------------------------------------------------------------ merging

    /**
     * Append the pages of $pagesPdf (made by PdfWriter) to $original with an incremental update. Returns the new file, or null when the original cannot be extended safely.
     */
    public static function appendPages(string $original, string $pagesPdf): ?string
    {
        return self::build($original, null, null, $pagesPdf);
    }

    /**
     * Draw signatures ON the signature lines of the original's last page: for every entry of $draws a signature picture, the name, the title and the date at the positions
     * PdfSlots found. Returns a one-page PdfWriter file (same page size) that build() turns into an overlay.
     * @param array{width:float,height:float} $info
     * @param list<array{slot:array<string,mixed>,name:string,title:?string,date:string,image:?string}> $draws
     */
    public static function overlayPdf(array $info, array $draws): string
    {
        $pdf = new PdfWriter(['title' => 'Signature overlay', 'creator' => 'Lumina documents']);
        $W = (float) $info['width'];
        $H = (float) $info['height'];
        $pdf->addPage($W, $H);
        $fit = static function (string $text, string $font, float $size, float $max): float {
            while ($size > 6.0 && PdfWriter::textWidth($text, $font, $size) > $max) {
                $size -= 0.5;
            }
            return $size;
        };
        foreach ($draws as $d) {
            $slot = $d['slot'];
            if (!empty($d['image'])) {
                $inf = PdfWriter::imageInfo((string) $d['image']);
                if ($inf !== null) {
                    try {
                        $name = $inf['mime'] === 'image/png' ? $pdf->addPng((string) $d['image']) : $pdf->addJpeg((string) $d['image']);
                        // the signature goes above the signature line, right of the "Full name" caption and left of the "Title / position" caption (the captions stay readable)
                        [$sx, $sy, $sw, $sh] = $slot['sig'];
                        $left = $sx + 56.0;
                        $maxW = max(40.0, min(110.0, (float) $slot['title'][0] - 6.0 - $left));
                        $maxH = min(26.0, $sh);
                        $scale = min($maxW / max(1, $inf['w']), $maxH / max(1, $inf['h']));
                        $w = $inf['w'] * $scale;
                        $h = $inf['h'] * $scale;
                        $pdf->image($name, $left, $H - ($sy + 2 + $h), $w, $h);
                    } catch (\Throwable) {
                        // a picture the writer cannot place: the line stays empty
                    }
                }
            }
            // the title line is already printed in the document (the role), so only the name and the date are written
            foreach (['name' => ['helvetica-bold', 10.0, $d['name']], 'date' => ['helvetica', 9.0, $d['date']]] as $key => [$font, $size, $text]) {
                if ($text === '') {
                    continue;
                }
                [$lx, $ly, $lw] = $slot[$key];
                $pdf->text($lx + 2, $H - ($ly + 3), $text, $font, $fit($text, $font, $size, $lw - 4), self::INK);
            }
        }
        return $pdf->output();
    }

    /**
     * One incremental update that can do both: lay $overlayPdf (made by overlayPdf()) over the last page of the original, and append the pages of $pagesPdf.
     * Returns the new file, or null when the original cannot be extended safely (compressed cross-reference stream, a /Prev chain, an indirect /Kids, resources that are a
     * reference ...): the caller then falls back to the separate approval page.
     * @param ?array{page:int,pageBody:string,width:float,height:float} $slotInfo the last page as read by PdfSlots (needed with $overlayPdf)
     */
    public static function build(string $original, ?array $slotInfo, ?string $overlayPdf, ?string $pagesPdf): ?string
    {
        $o = self::readOriginal($original);
        if ($o === null || ($overlayPdf === null && $pagesPdf === null) || ($overlayPdf !== null && $slotInfo === null)) {
            return null;
        }
        $next = $o['size'];                                         // first new object number
        $emit = [];                                                 // new id => ['dict' => text, 'stream' => ?bytes]

        // ---- the overlay: its fonts and images are imported, its content becomes a Form XObject the last page draws
        $pageObject = null;
        if ($overlayPdf !== null) {
            $ov = self::readWriterObjects($overlayPdf);
            if ($ov === null || count($ov['pages']) !== 1) {
                return null;
            }
            $ovPage = $ov['pages'][0];
            $pageDict = (string) $ov['objects'][$ovPage]['dict'];
            if (preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $pageDict, $cm) !== 1) {
                return null;
            }
            $ovContent = (int) $cm[1];
            $resPos = strpos($pageDict, '/Resources');
            $resStart = $resPos === false ? false : strpos($pageDict, '<<', $resPos);
            if ($resStart === false) {
                return null;
            }
            $depth = 0;
            $resEnd = null;
            for ($i = $resStart, $n = strlen($pageDict); $i < $n - 1; $i++) {
                $two = $pageDict[$i] . $pageDict[$i + 1];
                if ($two === '<<') {
                    $depth++;
                    $i++;
                } elseif ($two === '>>') {
                    $depth--;
                    $i++;
                    if ($depth === 0) {
                        $resEnd = $i + 1;
                        break;
                    }
                }
            }
            if ($resEnd === null) {
                return null;
            }
            $resources = substr($pageDict, $resStart, $resEnd - $resStart);
            $map = [];
            foreach (array_keys($ov['objects']) as $id) {
                if ($id >= 4 && $id !== $ovPage) {
                    $map[$id] = $next++;
                }
            }
            $remap = static fn(string $t): string => (string) preg_replace_callback('/(\d+) 0 R/', static fn(array $m): string => ((int) $m[1] === 2 || !isset($map[(int) $m[1]]) ? $m[0] : $map[(int) $m[1]] . ' 0 R'), $t);
            foreach ($ov['objects'] as $id => $obj) {
                if (!isset($map[$id])) {
                    continue;
                }
                if ($id === $ovContent) {
                    $inner = trim((string) preg_replace('/^<<|>>$/', '', trim($obj['dict'])));
                    $obj['dict'] = '<< /Type /XObject /Subtype /Form /BBox [0 0 ' . self::num($slotInfo['width']) . ' ' . self::num($slotInfo['height']) . '] /Resources ' . $remap($resources) . ' ' . $inner . ' >>';
                    $emit[$map[$id]] = $obj;
                } else {
                    $emit[$map[$id]] = ['dict' => $remap($obj['dict']), 'stream' => $obj['stream']];
                }
            }
            $formId = $map[$ovContent];
            $callId = $next++;
            $call = "q /LAFSig Do Q
";
            $emit[$callId] = ['dict' => '<< /Length ' . strlen($call) . ' >>', 'stream' => $call];
            // the last page: one more content stream and one more XObject resource
            $page = $slotInfo['pageBody'];
            if (preg_match('/\/Contents\s+(\d+)\s+0\s+R/', $page) === 1) {
                $page = (string) preg_replace('/\/Contents\s+(\d+)\s+0\s+R/', '/Contents [ $1 0 R ' . $callId . ' 0 R ]', $page, 1);
            } elseif (preg_match('/\/Contents\s*\[/', $page) === 1) {
                $page = (string) preg_replace('/(\/Contents\s*\[[^\]]*)\]/', '$1 ' . $callId . ' 0 R ]', $page, 1);
            } else {
                return null;
            }
            if (preg_match('/\/Resources\s*<</', $page) !== 1) {
                return null;                                        // resources held in another object: not touched
            }
            if (preg_match('/\/Resources\s*<<.*?\/XObject\s*<</s', $page) === 1) {
                $page = (string) preg_replace('/(\/Resources\s*<<.*?\/XObject\s*<<)/s', '$1 /LAFSig ' . $formId . ' 0 R ', $page, 1);
            } else {
                $page = (string) preg_replace('/(\/Resources\s*<<)/', '$1 /XObject << /LAFSig ' . $formId . ' 0 R >> ', $page, 1);
            }
            $pageObject = [(int) $slotInfo['page'], $page];
        }

        // ---- appended pages
        $pagesNew = null;
        $kids = '';
        if ($pagesPdf !== null) {
            $pagesNew = self::readWriterObjects($pagesPdf);
            if ($pagesNew === null || $pagesNew['pages'] === []) {
                return null;
            }
            $base = $next;
            $pmap = static function (int $id) use ($base, $o): int {
                return $id === 2 ? $o['pagesId'] : $base + ($id - 4);
            };
            $remap = static fn(string $dict): string => (string) preg_replace_callback('/(\d+) 0 R/', static fn(array $m): string => $pmap((int) $m[1]) . ' 0 R', $dict);
            foreach ($pagesNew['objects'] as $id => $obj) {
                if ($id < 4) {
                    continue;                                       // catalog, page tree and info of the helper file are not used
                }
                $nid = $pmap($id);
                $next = max($next, $nid + 1);
                $emit[$nid] = ['dict' => $remap($obj['dict']), 'stream' => $obj['stream']];
            }
            foreach ($pagesNew['pages'] as $pid) {
                $kids .= ' ' . $pmap($pid) . ' 0 R';
            }
        }

        // ---- write
        $out = rtrim($original, "\r\n") . "\n";
        $offsets = [];
        $maxId = $o['size'] - 1;
        ksort($emit);
        foreach ($emit as $nid => $obj) {
            $maxId = max($maxId, $nid);
            $offsets[$nid] = strlen($out);
            $out .= $nid . " 0 obj\n" . $obj['dict'] . ($obj['stream'] !== null ? "\nstream\n" . $obj['stream'] . "\nendstream" : '') . "\nendobj\n";
        }
        if ($pageObject !== null) {
            $offsets[$pageObject[0]] = strlen($out);
            $out .= $pageObject[0] . " 0 obj\n" . $pageObject[1] . "\nendobj\n";
        }
        if ($pagesNew !== null) {
            $tree = $o['pagesBody'];
            $tree = (string) preg_replace('/\/Kids\s*\[(.*?)\]/s', '/Kids [$1' . $kids . ' ]', $tree, 1);
            $tree = (string) preg_replace_callback('/\/Count\s+(\d+)/', static fn(array $m): string => '/Count ' . ((int) $m[1] + count($pagesNew['pages'])), $tree, 1);
            $offsets[$o['pagesId']] = strlen($out);
            $out .= $o['pagesId'] . " 0 obj\n" . $tree . "\nendobj\n";
        }

        ksort($offsets);
        $xref = strlen($out);
        $out .= "xref\n";
        $ids = array_keys($offsets);
        $i = 0;
        while ($i < count($ids)) {
            $j = $i;
            while ($j + 1 < count($ids) && $ids[$j + 1] === $ids[$j] + 1) {
                $j++;
            }
            $out .= $ids[$i] . ' ' . ($j - $i + 1) . "\n";
            for ($k = $i; $k <= $j; $k++) {
                $out .= sprintf("%010d 00000 n \n", $offsets[$ids[$k]]);
            }
            $i = $j + 1;
        }
        $idHex = md5($out . (string) microtime(true));
        $out .= 'trailer' . "\n<< /Size " . ($maxId + 1) . ' /Root ' . $o['root'] . ' 0 R' . ($o['info'] !== null ? ' /Info ' . $o['info'] . ' 0 R' : '')
            . ' /ID [<' . ($o['id1'] ?? $idHex) . '> <' . $idHex . '>] /Prev ' . $o['xref'] . " >>\nstartxref\n" . $xref . "\n%%EOF\n";
        return $out;
    }

    private static function num(float $v): string
    {
        return rtrim(rtrim(number_format($v, 4, '.', ''), '0'), '.');
    }
    /**
     * What an incremental update needs from the original: the cross-reference offset, /Size, /Root, /Info, the first /ID and the body of the root page tree node.
     * @return array{xref:int,size:int,root:int,info:?int,id1:?string,pagesId:int,pagesBody:string,body:\Closure,offsets:array<int,int>}|null
     */
    private static function readOriginal(string $pdf): ?array
    {
        $len = strlen($pdf);
        if ($len < 100 || !str_starts_with($pdf, '%PDF-')) {
            return null;
        }
        $tail = substr($pdf, max(0, $len - 2048));
        if (preg_match_all('/startxref\s+(\d+)\s+%%EOF/', $tail, $m) < 1) {
            return null;
        }
        $xref = (int) end($m[1]);
        if ($xref <= 0 || $xref >= $len || substr($pdf, $xref, 4) !== 'xref') {
            return null;                                            // a cross-reference STREAM (compressed) or damage
        }
        // the table
        $pos = $xref + 4;
        $offsets = [];
        $pos += strspn($pdf, "\r\n ", $pos);
        while (preg_match('/\G(\d+) (\d+)\s*?\r?\n/', $pdf, $s, 0, $pos) === 1) {
            $start = (int) $s[1];
            $count = (int) $s[2];
            $pos += strlen($s[0]);
            for ($i = 0; $i < $count; $i++) {
                $entry = substr($pdf, $pos, 20);
                if (preg_match('/^(\d{10}) (\d{5}) ([nf])/', $entry, $e) !== 1) {
                    return null;
                }
                if ($e[3] === 'n') {
                    $offsets[$start + $i] = (int) $e[1];
                }
                $pos += 20;
            }
        }
        if (preg_match('/\Gtrailer\s*(.*?)startxref/s', $pdf, $t, 0, $pos) !== 1) {
            return null;
        }
        $trailer = $t[1];
        if (str_contains($trailer, '/Prev') || preg_match('/\/Size\s+(\d+)/', $trailer, $sz) !== 1 || preg_match('/\/Root\s+(\d+)\s+0\s+R/', $trailer, $rt) !== 1) {
            return null;
        }
        $info = preg_match('/\/Info\s+(\d+)\s+0\s+R/', $trailer, $in) === 1 ? (int) $in[1] : null;
        $id1 = preg_match('/\/ID\s*\[\s*<([0-9a-fA-F]+)>/', $trailer, $idm) === 1 ? $idm[1] : null;
        $body = static function (int $id) use ($pdf, $offsets): ?string {
            if (!isset($offsets[$id])) {
                return null;
            }
            $off = $offsets[$id];
            if (preg_match('/\G' . $id . '\s+0\s+obj\s*/', $pdf, $m, 0, $off) !== 1) {
                return null;
            }
            $start = $off + strlen($m[0]);
            $end = strpos($pdf, 'endobj', $start);
            return $end === false ? null : trim(substr($pdf, $start, $end - $start));
        };
        $catalog = $body((int) $rt[1]);
        if ($catalog === null || preg_match('/\/Pages\s+(\d+)\s+0\s+R/', $catalog, $pm) !== 1) {
            return null;
        }
        $pagesId = (int) $pm[1];
        $tree = $body($pagesId);
        if ($tree === null || preg_match('/\/Kids\s*\[/', $tree) !== 1 || preg_match('/\/Count\s+\d+/', $tree) !== 1 || str_contains($tree, '/Kids ' . 'obj')) {
            return null;
        }
        return ['xref' => $xref, 'size' => (int) $sz[1], 'root' => (int) $rt[1], 'info' => $info, 'id1' => $id1, 'pagesId' => $pagesId, 'pagesBody' => $tree, 'body' => $body, 'offsets' => $offsets];
    }

    /** The parts of an original PDF that signing needs (see readOriginal), plus `body(id)` (the text of an object) and `offsets`; null when it cannot be extended safely. */
    public static function open(string $pdf): ?array
    {
        return self::readOriginal($pdf);
    }

    /**
     * The objects of a PDF written by PdfWriter (simple, known layout): id => ['dict' => text, 'stream' => bytes|null]; and the ids of its page objects in order.
     * @return array{objects:array<int,array{dict:string,stream:?string}>,pages:list<int>}|null
     */
    private static function readWriterObjects(string $pdf): ?array
    {
        $objects = [];
        $pages = [];
        $pos = 0;
        $len = strlen($pdf);
        while (preg_match('/(\d+) 0 obj\n/', $pdf, $m, PREG_OFFSET_CAPTURE, $pos) === 1) {
            $id = (int) $m[1][0];
            $start = $m[0][1] + strlen($m[0][0]);
            $s = strpos($pdf, "\nstream\n", $start);
            $e = strpos($pdf, "\nendobj\n", $start);
            if ($e === false) {
                return null;
            }
            if ($s !== false && $s < $e) {
                $dict = substr($pdf, $start, $s - $start);
                if (preg_match('/\/Length (\d+)/', $dict, $lm) !== 1) {
                    return null;
                }
                $data = substr($pdf, $s + 8, (int) $lm[1]);
                $objects[$id] = ['dict' => $dict, 'stream' => $data];
                $pos = $s + 8 + (int) $lm[1];
                $endobj = strpos($pdf, "\nendobj\n", $pos);
                $pos = $endobj === false ? $len : $endobj + 8;
            } else {
                $dict = substr($pdf, $start, $e - $start);
                $objects[$id] = ['dict' => $dict, 'stream' => null];
                if (str_contains($dict, '/Type /Page ') || preg_match('~/Type /Page\b(?!s)~', $dict) === 1) {
                    $pages[] = $id;
                }
                $pos = $e + 8;
            }
        }
        $pages = array_values(array_filter($pages, static fn(int $id): bool => $id >= 4));
        return $objects === [] ? null : ['objects' => $objects, 'pages' => $pages];
    }
}
