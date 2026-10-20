<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * Pure-PHP QR Code encoder (ISO/IEC 18004, Model 2) for the membership card's verification link. No library, no extension.
 *
 *   $qr = QrEncoder::encode('https://example.org/membership/verify/abc...', 'M');
 *   $qr['size']      // modules per side (21 for version 1 ... 57 for version 10), WITHOUT the quiet zone
 *   $qr['modules']   // list of rows, each a list of bool (true = dark); row 0 = top
 *   $qr['version'], $qr['ecc'], $qr['mask']
 *
 * Scope: BYTE mode (the text is used as raw UTF-8 bytes; verification URLs are ASCII), versions 1 to 10, error correction levels L, M, Q, H
 * (the card uses M, or Q for short links), the smallest version that fits is chosen, all eight masks are scored with the four ISO penalty
 * rules and the lowest wins (ties: the lower mask number), format information (BCH 15,5 with the 0x5412 mask) and version information
 * (BCH 18,6, versions 7 and up) are written in both places, the dark module is set. The quiet zone is NOT part of the matrix: the renderer
 * adds four modules of light around it.
 *
 * Verified in bin/selftest-membership-card.php against the ISO 18004 worked example (01234567, version 1-M data and error codewords),
 * the format and version information tables, and matrices produced by an independent implementation (ReportLab) for several payloads,
 * versions, levels and every mask. Capacity (bytes): v1 L17 M14 Q11 H7 ... v10 L271 M213 Q151 H119.
 */
final class QrEncoder
{
    public const LEVELS = ['L', 'M', 'Q', 'H'];
    /** Two-bit level indicator in the format information. */
    private const LEVEL_BITS = ['L' => 1, 'M' => 0, 'Q' => 3, 'H' => 2];
    public const MAX_VERSION = 10;

    /** Blocks per version 1..10 and level: [EC codewords per block, [[count, data codewords per block], ...]] (ISO 18004 table 9). */
    private const BLOCKS = [
        1  => ['L' => [7, [[1, 19]]], 'M' => [10, [[1, 16]]], 'Q' => [13, [[1, 13]]], 'H' => [17, [[1, 9]]]],
        2  => ['L' => [10, [[1, 34]]], 'M' => [16, [[1, 28]]], 'Q' => [22, [[1, 22]]], 'H' => [28, [[1, 16]]]],
        3  => ['L' => [15, [[1, 55]]], 'M' => [26, [[1, 44]]], 'Q' => [18, [[2, 17]]], 'H' => [22, [[2, 13]]]],
        4  => ['L' => [20, [[1, 80]]], 'M' => [18, [[2, 32]]], 'Q' => [26, [[2, 24]]], 'H' => [16, [[4, 9]]]],
        5  => ['L' => [26, [[1, 108]]], 'M' => [24, [[2, 43]]], 'Q' => [18, [[2, 15], [2, 16]]], 'H' => [22, [[2, 11], [2, 12]]]],
        6  => ['L' => [18, [[2, 68]]], 'M' => [16, [[4, 27]]], 'Q' => [24, [[4, 19]]], 'H' => [28, [[4, 15]]]],
        7  => ['L' => [20, [[2, 78]]], 'M' => [18, [[4, 31]]], 'Q' => [18, [[2, 14], [4, 15]]], 'H' => [26, [[4, 13], [1, 14]]]],
        8  => ['L' => [24, [[2, 97]]], 'M' => [22, [[2, 38], [2, 39]]], 'Q' => [22, [[4, 18], [2, 19]]], 'H' => [26, [[4, 14], [2, 15]]]],
        9  => ['L' => [30, [[2, 116]]], 'M' => [22, [[3, 36], [2, 37]]], 'Q' => [20, [[4, 16], [4, 17]]], 'H' => [24, [[4, 12], [4, 13]]]],
        10 => ['L' => [18, [[2, 68], [2, 69]]], 'M' => [26, [[4, 43], [1, 44]]], 'Q' => [24, [[6, 19], [2, 20]]], 'H' => [28, [[6, 15], [2, 16]]]],
    ];
    /** Alignment pattern centre coordinates per version. */
    private const ALIGN = [1 => [], 2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30], 6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50]];

    /** @var array{0:list<int>,1:list<int>}|null exp and log tables of GF(256), primitive polynomial 0x11D */
    private static ?array $gf = null;

    // ------------------------------------------------------------------------------------------------ public API

    /**
     * @param int|null $mask force one mask (0..7) instead of choosing by penalty score (tests use it)
     * @return array{version:int,size:int,ecc:string,mask:int,modules:list<list<bool>>,penalty:int}
     * @throws \InvalidArgumentException when the text does not fit version 10 at that level
     */
    public static function encode(string $text, string $ecc = 'M', int $minVersion = 1, ?int $mask = null): array
    {
        $ecc = strtoupper($ecc);
        if (!in_array($ecc, self::LEVELS, true)) {
            throw new \InvalidArgumentException('Unknown error correction level.');
        }
        $len = strlen($text);
        $version = 0;
        for ($v = max(1, $minVersion); $v <= self::MAX_VERSION; $v++) {
            if ($len <= self::byteCapacity($v, $ecc)) {
                $version = $v;
                break;
            }
        }
        if ($version === 0) {
            throw new \InvalidArgumentException('The text is too long for a QR code of version ' . self::MAX_VERSION . ' at level ' . $ecc . ' (' . $len . ' bytes).');
        }
        $data = self::dataCodewords($text, $version, $ecc);
        $codewords = self::interleave($data, $version, $ecc);
        $size = self::sizeOf($version);

        // function patterns + data placement are mask independent: build once, then apply each mask to a copy
        [$base, $reserved] = self::functionPatterns($version);
        self::placeData($base, $reserved, $codewords, $size);

        $best = null;
        $bestMask = 0;
        $bestScore = PHP_INT_MAX;
        $masks = $mask !== null ? [max(0, min(7, $mask))] : range(0, 7);
        foreach ($masks as $m) {
            $grid = self::applyMask($base, $reserved, $m, $size);
            self::placeFormat($grid, $ecc, $m, $size);
            if ($version >= 7) {
                self::placeVersion($grid, $version, $size);
            }
            $score = self::penalty($grid, $size);
            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $grid;
                $bestMask = $m;
            }
        }
        $modules = [];
        foreach ($best as $row) {
            $modules[] = array_map(static fn(int $c): bool => $c === 1, $row);
        }
        return ['version' => $version, 'size' => $size, 'ecc' => $ecc, 'mask' => $bestMask, 'modules' => $modules, 'penalty' => $bestScore];
    }

    public static function sizeOf(int $version): int
    {
        return 17 + 4 * $version;
    }

    /** Most bytes a QR of this version and level can hold in byte mode. */
    public static function byteCapacity(int $version, string $ecc): int
    {
        [$ec, $groups] = self::BLOCKS[$version][strtoupper($ecc)];
        $data = 0;
        foreach ($groups as [$count, $cw]) {
            $data += $count * $cw;
        }
        $header = 4 + ($version >= 10 ? 16 : 8);                       // mode indicator + character count
        return intdiv($data * 8 - $header, 8);
    }

    /** The 15-bit format information (level + mask, BCH protected, XOR 0x5412) as an int. */
    public static function formatBits(string $ecc, int $mask): int
    {
        $data = (self::LEVEL_BITS[strtoupper($ecc)] << 3) | ($mask & 7);
        $rem = $data << 10;
        for ($i = 14; $i >= 10; $i--) {
            if (($rem >> $i) & 1) {
                $rem ^= 0x537 << ($i - 10);
            }
        }
        return ((($data << 10) | $rem) ^ 0x5412) & 0x7FFF;
    }

    /** The 18-bit version information (BCH 18,6) as an int (versions 7 and up). */
    public static function versionBits(int $version): int
    {
        $rem = $version << 12;
        for ($i = 17; $i >= 12; $i--) {
            if (($rem >> $i) & 1) {
                $rem ^= 0x1F25 << ($i - 12);
            }
        }
        return ($version << 12) | $rem;
    }

    /**
     * Data codewords of the message (before error correction): mode, count, bytes, terminator, padding. Public for the ISO example test.
     * @return list<int>
     */
    public static function dataCodewords(string $text, int $version, string $ecc): array
    {
        [, $groups] = self::BLOCKS[$version][strtoupper($ecc)];
        $capacity = 0;
        foreach ($groups as [$count, $cw]) {
            $capacity += $count * $cw;
        }
        $bits = '0100' . str_pad(decbin(strlen($text)), $version >= 10 ? 16 : 8, '0', STR_PAD_LEFT);
        $n = strlen($text);
        for ($i = 0; $i < $n; $i++) {
            $bits .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }
        $bits .= str_repeat('0', min(4, $capacity * 8 - strlen($bits)));          // terminator
        $bits .= str_repeat('0', (8 - strlen($bits) % 8) % 8);
        $out = [];
        for ($i = 0, $l = strlen($bits); $i < $l; $i += 8) {
            $out[] = bindec(substr($bits, $i, 8));
        }
        for ($pad = 0xEC; count($out) < $capacity; $pad = $pad === 0xEC ? 0x11 : 0xEC) {
            $out[] = $pad;
        }
        return $out;
    }

    /**
     * Reed-Solomon error correction codewords for one block of data codewords (GF(256), generator with roots a^0 .. a^(n-1)).
     * @param list<int> $data @return list<int>
     */
    public static function eccCodewords(array $data, int $ecLen): array
    {
        [$exp, $log] = self::gf();
        $gen = [1];
        for ($i = 0; $i < $ecLen; $i++) {                       // multiply by (x - a^i)
            $next = array_fill(0, count($gen) + 1, 0);
            foreach ($gen as $j => $c) {
                $next[$j] ^= $c;
                $next[$j + 1] ^= $c === 0 ? 0 : $exp[($log[$c] + $i) % 255];
            }
            $gen = $next;
        }
        // $gen is high-degree first: [1, g1, g2 ... g_n]
        $rem = array_fill(0, $ecLen, 0);
        foreach ($data as $d) {
            $factor = $d ^ $rem[0];
            array_shift($rem);
            $rem[] = 0;
            if ($factor !== 0) {
                $lf = $log[$factor];
                for ($i = 0; $i < $ecLen; $i++) {
                    $g = $gen[$i + 1];
                    if ($g !== 0) {
                        $rem[$i] ^= $exp[($log[$g] + $lf) % 255];
                    }
                }
            }
        }
        return $rem;
    }

    /** One text line of '0' and '1' per matrix row (for tests and debugging). @param list<list<bool>> $modules */
    public static function toText(array $modules): string
    {
        return implode("\n", array_map(static fn(array $r): string => implode('', array_map(static fn(bool $b): string => $b ? '1' : '0', $r)), $modules));
    }

    /**
     * Horizontal runs of dark modules, for vector drawing: list of [row, firstColumn, length]. Fewer shapes than one square per module.
     * @param list<list<bool>> $modules @return list<array{0:int,1:int,2:int}>
     */
    public static function runs(array $modules): array
    {
        $out = [];
        foreach ($modules as $r => $row) {
            $start = null;
            $n = count($row);
            for ($c = 0; $c <= $n; $c++) {
                $dark = $c < $n && $row[$c];
                if ($dark && $start === null) {
                    $start = $c;
                } elseif (!$dark && $start !== null) {
                    $out[] = [$r, $start, $c - $start];
                    $start = null;
                }
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ internals

    /** @return array{0:list<int>,1:list<int>} */
    private static function gf(): array
    {
        if (self::$gf === null) {
            $exp = array_fill(0, 512, 0);
            $log = array_fill(0, 256, 0);
            $x = 1;
            for ($i = 0; $i < 255; $i++) {
                $exp[$i] = $x;
                $log[$x] = $i;
                $x <<= 1;
                if ($x & 0x100) {
                    $x ^= 0x11D;
                }
            }
            for ($i = 255; $i < 512; $i++) {
                $exp[$i] = $exp[$i - 255];
            }
            self::$gf = [$exp, $log];
        }
        return self::$gf;
    }

    /**
     * Split the data into blocks, compute each block's ECC, interleave (data column by column, then ECC column by column).
     * @param list<int> $data @return list<int>
     */
    private static function interleave(array $data, int $version, string $ecc): array
    {
        [$ecLen, $groups] = self::BLOCKS[$version][$ecc];
        $blocks = [];
        $pos = 0;
        foreach ($groups as [$count, $cw]) {
            for ($i = 0; $i < $count; $i++) {
                $chunk = array_slice($data, $pos, $cw);
                $pos += $cw;
                $blocks[] = ['data' => $chunk, 'ecc' => self::eccCodewords($chunk, $ecLen)];
            }
        }
        $out = [];
        $maxData = max(array_map(static fn(array $b): int => count($b['data']), $blocks));
        for ($i = 0; $i < $maxData; $i++) {
            foreach ($blocks as $b) {
                if (isset($b['data'][$i])) {
                    $out[] = $b['data'][$i];
                }
            }
        }
        for ($i = 0; $i < $ecLen; $i++) {
            foreach ($blocks as $b) {
                $out[] = $b['ecc'][$i];
            }
        }
        return $out;
    }

    /**
     * Finder, separators, timing, alignment, dark module, and the areas reserved for format / version information.
     * @return array{0:list<list<int>>,1:list<list<bool>>} [grid (0/1, dark = 1), reserved map]
     */
    private static function functionPatterns(int $version): array
    {
        $size = self::sizeOf($version);
        $grid = array_fill(0, $size, array_fill(0, $size, 0));
        $res = array_fill(0, $size, array_fill(0, $size, false));
        $set = static function (int $r, int $c, int $v) use (&$grid, &$res, $size): void {
            if ($r >= 0 && $r < $size && $c >= 0 && $c < $size) {
                $grid[$r][$c] = $v;
                $res[$r][$c] = true;
            }
        };
        // finder patterns + separators
        foreach ([[0, 0], [0, $size - 7], [$size - 7, 0]] as [$r0, $c0]) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $inside = $r >= 0 && $r <= 6 && $c >= 0 && $c <= 6;
                    $dark = $inside && ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                    $set($r0 + $r, $c0 + $c, $dark ? 1 : 0);
                }
            }
        }
        // timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $set(6, $i, $i % 2 === 0 ? 1 : 0);
            $set($i, 6, $i % 2 === 0 ? 1 : 0);
        }
        // alignment patterns (not where they would touch a finder)
        $centres = self::ALIGN[$version];
        $last = count($centres) - 1;
        foreach ($centres as $i => $cr) {
            foreach ($centres as $j => $cc) {
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $last) || ($i === $last && $j === 0)) {
                    continue;
                }
                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        $set($cr + $r, $cc + $c, (max(abs($r), abs($c)) !== 1) ? 1 : 0);
                    }
                }
            }
        }
        // reserved format information areas (written later) and the always-dark module
        for ($i = 0; $i < 9; $i++) {
            if (!$res[8][$i]) {
                $res[8][$i] = true;
            }
            if (!$res[$i][8]) {
                $res[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $res[8][$size - 1 - $i] = true;
            $res[$size - 1 - $i][8] = true;
        }
        $set($size - 8, 8, 1);
        if ($version >= 7) {
            for ($i = 0; $i < 6; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    $res[$i][$size - 11 + $j] = true;
                    $res[$size - 11 + $j][$i] = true;
                }
            }
        }
        return [$grid, $res];
    }

    /** Write the codeword bits into the free modules: two-column zigzag from the bottom right, skipping the vertical timing column. */
    private static function placeData(array &$grid, array $reserved, array $codewords, int $size): void
    {
        $bits = [];
        foreach ($codewords as $cw) {
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($cw >> $b) & 1;
            }
        }
        $i = 0;
        $total = count($bits);
        $up = true;
        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col = 5;
            }
            for ($n = 0; $n < $size; $n++) {
                $row = $up ? $size - 1 - $n : $n;
                for ($k = 0; $k < 2; $k++) {
                    $c = $col - $k;
                    if (!$reserved[$row][$c]) {
                        $grid[$row][$c] = $i < $total ? $bits[$i] : 0;            // remainder bits are 0
                        $i++;
                    }
                }
            }
            $up = !$up;
        }
    }

    /** @return list<list<int>> copy of the grid with the mask applied to the DATA modules only */
    private static function applyMask(array $grid, array $reserved, int $mask, int $size): array
    {
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($reserved[$r][$c]) {
                    continue;
                }
                $flip = match ($mask) {
                    0 => ($r + $c) % 2 === 0,
                    1 => $r % 2 === 0,
                    2 => $c % 3 === 0,
                    3 => ($r + $c) % 3 === 0,
                    4 => (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0,
                    5 => ($r * $c) % 2 + ($r * $c) % 3 === 0,
                    6 => (($r * $c) % 2 + ($r * $c) % 3) % 2 === 0,
                    default => (($r + $c) % 2 + ($r * $c) % 3) % 2 === 0,
                };
                if ($flip) {
                    $grid[$r][$c] ^= 1;
                }
            }
        }
        return $grid;
    }

    /** Format information, MSB first: bits 14..9 along the top-left finder, 8..0 split between the other two corners (ISO 18004 figure 25). */
    private static function placeFormat(array &$grid, string $ecc, int $mask, int $size): void
    {
        $bits = self::formatBits($ecc, $mask);
        $bit = static fn(int $i): int => ($bits >> $i) & 1;           // i = 0 is the least significant bit
        // around the top-left finder
        for ($i = 0; $i <= 5; $i++) {
            $grid[$i][8] = $bit($i);
        }
        $grid[7][8] = $bit(6);
        $grid[8][8] = $bit(7);
        $grid[8][7] = $bit(8);
        for ($i = 9; $i <= 14; $i++) {
            $grid[8][14 - $i] = $bit($i);
        }
        // the second copy: bottom-left (bits 0..6) and top-right (bits 7..14)
        for ($i = 0; $i <= 7; $i++) {
            $grid[8][$size - 1 - $i] = $bit($i);
        }
        for ($i = 8; $i <= 14; $i++) {
            $grid[$size - 15 + $i][8] = $bit($i);
        }
        $grid[$size - 8][8] = 1;                                      // the dark module
    }

    private static function placeVersion(array &$grid, int $version, int $size): void
    {
        $bits = self::versionBits($version);
        for ($i = 0; $i < 18; $i++) {
            $v = ($bits >> $i) & 1;
            $a = intdiv($i, 3);
            $b = $i % 3 + $size - 11;
            $grid[$a][$b] = $v;
            $grid[$b][$a] = $v;
        }
    }

    /** ISO 18004 section 7.8.3 penalty score of a finished matrix. */
    private static function penalty(array $g, int $size): int
    {
        $score = 0;
        // N1: runs of five or more same-coloured modules in a row / column
        for ($pass = 0; $pass < 2; $pass++) {
            for ($a = 0; $a < $size; $a++) {
                $run = 1;
                for ($b = 1; $b < $size; $b++) {
                    $cur = $pass === 0 ? $g[$a][$b] : $g[$b][$a];
                    $prev = $pass === 0 ? $g[$a][$b - 1] : $g[$b - 1][$a];
                    if ($cur === $prev) {
                        $run++;
                    } else {
                        if ($run >= 5) {
                            $score += 3 + ($run - 5);
                        }
                        $run = 1;
                    }
                }
                if ($run >= 5) {
                    $score += 3 + ($run - 5);
                }
            }
        }
        // N2: 2x2 blocks of one colour
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $v = $g[$r][$c];
                if ($v === $g[$r][$c + 1] && $v === $g[$r + 1][$c] && $v === $g[$r + 1][$c + 1]) {
                    $score += 3;
                }
            }
        }
        // N3: 1:1:3:1:1 finder-like patterns with four light modules on a side
        $pat1 = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
        $pat2 = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];
        for ($pass = 0; $pass < 2; $pass++) {
            for ($a = 0; $a < $size; $a++) {
                for ($b = 0; $b <= $size - 11; $b++) {
                    $m1 = true;
                    $m2 = true;
                    for ($k = 0; $k < 11 && ($m1 || $m2); $k++) {
                        $v = $pass === 0 ? $g[$a][$b + $k] : $g[$b + $k][$a];
                        if ($v !== $pat1[$k]) {
                            $m1 = false;
                        }
                        if ($v !== $pat2[$k]) {
                            $m2 = false;
                        }
                    }
                    if ($m1) {
                        $score += 40;
                    }
                    if ($m2) {
                        $score += 40;
                    }
                }
            }
        }
        // N4: balance of dark modules
        $dark = 0;
        foreach ($g as $row) {
            $dark += array_sum($row);
        }
        // smallest k >= 0 with 45 - 5k <= dark% <= 55 + 5k, ten points per step (exact integer arithmetic)
        $total = $size * $size;
        $k = max(0, intdiv(abs($dark * 20 - $total * 10) + $total - 1, $total) - 1);
        $score += 10 * $k;
        return $score;
    }
}
