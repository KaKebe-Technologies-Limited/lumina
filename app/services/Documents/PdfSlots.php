<?php
declare(strict_types=1);

namespace App\Services\Documents;

/**
 * Reads the "Approval and signatures" page of a policy PDF and finds who must sign it.
 *
 * The policy pack is laid out by one generator: the last page holds one block per signer. A block starts with a coordinate shift (`1 0 0 1 X Y cm`), prints the role in capitals
 * (CHAIRPERSON, BOARD OF DIRECTORS), then three captioned lines (Full name, Title / position, Date) and a captioned Signature line. This class decompresses the content stream of the
 * last page and returns, for every block, the printed role and the absolute position of each line in PDF points (origin bottom left), so a signature, a name, a title and a date can
 * be drawn exactly on those lines. Anything it cannot read with confidence returns an empty list (the caller then keeps the plain approval page).
 *
 * Read only: nothing is written. Docs: docs/DOCUMENTS-SIGNATURES.md.
 */
final class PdfSlots
{
    /**
     * @return array{page:int,pageBody:string,width:float,height:float,slots:list<array<string,mixed>>}|null  slots: index, role, name|title|date => [x, y, w] (the writing line), sig => [x, y, w, h]
     */
    public static function read(string $pdf): ?array
    {
        $o = PdfSigner::open($pdf);
        if ($o === null) {
            return null;
        }
        $body = $o['body'];
        if (preg_match('/\/Kids\s*\[(.*?)\]/s', (string) $o['pagesBody'], $k) !== 1 || preg_match_all('/(\d+)\s+0\s+R/', $k[1], $refs) < 1) {
            return null;
        }
        $pageId = (int) end($refs[1]);
        $page = $body($pageId);
        if ($page === null || !str_starts_with($page, '<<') || preg_match('~/Type\s*/Page\b(?!s)~', $page) !== 1) {
            return null;
        }
        if (preg_match('/\/MediaBox\s*\[\s*([\d.\-]+)\s+([\d.\-]+)\s+([\d.\-]+)\s+([\d.\-]+)\s*\]/', $page, $mb) !== 1) {
            return null;
        }
        if (preg_match('/\/Contents\s*(\[[^\]]*\]|\d+\s+0\s+R)/', $page, $cm) !== 1 || preg_match_all('/(\d+)\s+0\s+R/', $cm[1], $cr) < 1) {
            return null;
        }
        $text = '';
        foreach ($cr[1] as $cid) {
            $part = self::stream($pdf, $o['offsets'], (int) $cid);
            if ($part === null) {
                return null;
            }
            $text .= $part . "\n";
        }
        if (!str_contains($text, 'Approval and signatures')) {
            return ['page' => $pageId, 'pageBody' => $page, 'width' => (float) $mb[3] - (float) $mb[1], 'height' => (float) $mb[4] - (float) $mb[2], 'slots' => []];
        }
        return ['page' => $pageId, 'pageBody' => $page, 'width' => (float) $mb[3] - (float) $mb[1], 'height' => (float) $mb[4] - (float) $mb[2], 'slots' => self::blocks($text)];
    }

    /** @return list<array<string,mixed>> */
    private static function blocks(string $c): array
    {
        $parts = preg_split('/\bq\s+1 0 0 1 ([\d.\-]+) ([\d.\-]+) cm\s/', $c, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return [];
        }
        $slots = [];
        for ($i = 1; $i + 2 < count($parts) + 1; $i += 3) {
            $ox = (float) $parts[$i];
            $oy = (float) ($parts[$i + 1] ?? 0);
            $seg = (string) ($parts[$i + 2] ?? '');
            if (!str_contains($seg, '(Signature) Tj')) {
                continue;
            }
            // text runs: position, size, string
            preg_match_all('/BT\s+1 0 0 1 ([\d.\-]+) ([\d.\-]+) Tm\s+\/\S+ ([\d.]+) Tf\s+[\d.]+ TL\s+\(((?:\\\\.|[^\\\\)])*)\)\s*Tj/', $seg, $t, PREG_SET_ORDER);
            $role = '';
            $labels = [];
            foreach ($t as $m) {
                $str = self::unescape($m[4]);
                if ($role === '' && (float) $m[3] >= 10.0 && $str !== '' && $str === strtoupper($str)) {
                    $role = trim($str);
                    continue;
                }
                $key = ['Full name' => 'name', 'Title / position' => 'title', 'Date' => 'date', 'Signature' => 'sig'][$str] ?? null;
                if ($key !== null && !isset($labels[$key])) {
                    $labels[$key] = [(float) $m[1], (float) $m[2]];
                }
            }
            if ($role === '' || !isset($labels['sig'], $labels['name'], $labels['title'], $labels['date'])) {
                continue;
            }
            // writing lines: "n x1 y m x2 y l S"
            preg_match_all('/n\s+([\d.\-]+) ([\d.\-]+) m\s+([\d.\-]+) \2 l S/', $seg, $ln, PREG_SET_ORDER);
            $lineFor = static function (array $label) use ($ln): ?array {
                $best = null;
                foreach ($ln as $l) {
                    $y = (float) $l[2];
                    if ($y > $label[1] && $y - $label[1] <= 14.0 && abs((float) $l[1] - $label[0]) < 1.0 && ($best === null || $y < $best[1])) {
                        $best = [(float) $l[1], $y, (float) $l[3] - (float) $l[1]];
                    }
                }
                return $best;
            };
            $slot = ['index' => count($slots), 'role' => mb_substr($role, 0, 80)];
            foreach (['name', 'title', 'date', 'sig'] as $key) {
                $line = $lineFor($labels[$key]);
                if ($line === null || $line[2] < 30.0) {
                    continue 2;
                }
                $slot[$key] = [$ox + $line[0], $oy + $line[1], $line[2]];
            }
            // the signature has the room between the signature line and the name line above it
            $room = $slot['name'][1] - $slot['sig'][1] - 3.0;
            $slot['sig'][] = max(14.0, min(40.0, $room));
            $slots[] = $slot;
        }
        return $slots;
    }

    /** ASCII85 (base-85) decoding; null on bad input. */
    private static function ascii85(string $in): ?string
    {
        $in = preg_replace('/\s+/', '', $in) ?? '';
        if (($p = strpos($in, '~>')) !== false) {
            $in = substr($in, 0, $p);
        }
        if (str_starts_with($in, '<~')) {
            $in = substr($in, 2);
        }
        $out = '';
        $group = [];
        $n = strlen($in);
        for ($i = 0; $i < $n; $i++) {
            $ch = $in[$i];
            if ($ch === 'z' && $group === []) {
                $out .= "\0\0\0\0";
                continue;
            }
            $v = ord($ch) - 33;
            if ($v < 0 || $v > 84) {
                return null;
            }
            $group[] = $v;
            if (count($group) === 5) {
                $num = 0;
                foreach ($group as $d) {
                    $num = $num * 85 + $d;
                }
                $out .= pack('N', $num & 0xFFFFFFFF);
                $group = [];
            }
        }
        if ($group !== []) {
            $k = count($group);
            if ($k === 1) {
                return null;
            }
            for ($j = $k; $j < 5; $j++) {
                $group[] = 84;
            }
            $num = 0;
            foreach ($group as $d) {
                $num = $num * 85 + $d;
            }
            $out .= substr(pack('N', $num & 0xFFFFFFFF), 0, $k - 1);
        }
        return $out;
    }

    private static function unescape(string $s): string
    {
        return (string) preg_replace_callback('/\\\\(.)/s', static fn(array $m): string => $m[1], $s);
    }

    /** The decoded content stream of object $id (Flate or none), null when it is something else. */
    private static function stream(string $pdf, array $offsets, int $id): ?string
    {
        if (!isset($offsets[$id]) || preg_match('/\G' . $id . '\s+0\s+obj\s*/', $pdf, $m, 0, $offsets[$id]) !== 1) {
            return null;
        }
        $start = $offsets[$id] + strlen($m[0]);
        $sp = strpos($pdf, 'stream', $start);
        if ($sp === false) {
            return null;
        }
        $dict = substr($pdf, $start, $sp - $start);
        if (preg_match('/\/Length\s+(\d+)(?!\s+\d+\s+R)/', $dict, $lm) !== 1) {
            return null;
        }
        $data = $sp + 6;
        if (substr($pdf, $data, 2) === "\r\n") {
            $data += 2;
        } elseif (($pdf[$data] ?? '') === "\n" || ($pdf[$data] ?? '') === "\r") {
            $data += 1;
        }
        $raw = substr($pdf, $data, (int) $lm[1]);
        if (!str_contains($dict, '/Filter')) {
            return $raw;
        }
        if (str_contains($dict, '/DecodeParms') || preg_match('/\/Filter\s*(?:\[\s*)?(\/ASCII85Decode\s*)?\/FlateDecode\s*\]?/', $dict, $fm) !== 1) {
            return null;
        }
        if (($fm[1] ?? '') !== '') {
            $raw = self::ascii85($raw);
            if ($raw === null) {
                return null;
            }
        }
        $out = @gzuncompress($raw);
        if ($out === false) {
            $out = @gzinflate(substr($raw, 2));
        }
        return $out === false ? null : $out;
    }
}
