<?php
declare(strict_types=1);

namespace App\Services\Forms;

/**
 * STRUCTURAL CHECK OF OFFICE FILES uploaded by anonymous visitors (and opened later by staff). No extension is needed: ZIP and OLE are read by hand.
 * Contract: docs/FORMS.md section 9. Every method returns null when the file is acceptable, otherwise a short reason code (for tests and the log; the
 * visitor only sees the editable "not a valid file" message).
 *
 *   zip($path, 'docx'|'xlsx')  a real ZIP: end-of-directory record, sane entries (no `..`, no encryption, no zip bombs), [Content_Types].xml and the main part of
 *                              the right kind, NO macros (vbaProject.bin, macro-enabled content types, macro / dialog sheets), NO ActiveX, embedded OLE
 *                              objects, executables or external workbook links, and no relationship that fetches something from outside except plain hyperlinks
 *                              (a remote template or linked object is how a harmless-looking .docx phones home).
 *   ole($path, 'doc'|'xls')    a real OLE2 compound file: the directory is walked and refused when it holds a VBA / Macros storage, embedded objects
 *                              (ObjectPool, Ole10Native) or encryption; an .xls is also refused when its workbook declares an Excel 4.0 macro sheet or a VBA module sheet.
 *
 * What this cannot prove: that a document is free of an exploit for a bug in Word or Excel. Downloads are attachments (nosniff, no-store), and staff
 * should open applicants' files in a protected view. See docs/FORMS.md section 9.
 */
final class OfficeInspector
{
    private const MAX_ENTRIES = 3000;
    private const MAX_UNPACKED = 262144000;            // 250 MB declared in total
    private const FREESECT = 0xFFFFFFFF;
    private const ENDOFCHAIN = 0xFFFFFFFE;

    private const ZIP_DENY = [
        '~(^|/)vbaproject\.bin$~', '~(^|/)vbadata\.xml$~', '~(^|/)activex/~', '~^(word|xl|ppt)/embeddings/~', '~^xl/(macrosheets|dialogsheets)/~', '~^customui/~',
        '~oleobject~', '~^xl/externallinks/~', '~\.(exe|dll|js|vbs|bat|cmd|ps1|scr|com|jar|hta|lnk|msi|sh)$~',
    ];

    /** Names of OLE storages / streams that mean macros, embedded objects or encryption (compared lower-case, control characters removed). */
    private const OLE_DENY = ['vba', '_vba_project', '_vba_project_cur', 'macros', 'projectwm', 'objectpool', 'ole10native', 'encryptedpackage', 'encryptioninfo'];

    // ------------------------------------------------------------------------------------------------------------------------------ ZIP (docx, xlsx)

    public static function zip(string $path, string $ext): ?string
    {
        $size = (int) @filesize($path);
        $h = @fopen($path, 'rb');
        if ($h === false || $size < 22) {
            return 'unreadable';
        }
        try {
            $tailLen = min($size, 65557);
            fseek($h, $size - $tailLen);
            $tail = (string) fread($h, $tailLen);
            $p = strrpos($tail, "PK\x05\x06");
            if ($p === false || strlen($tail) - $p < 22) {
                return 'no_directory';
            }
            /** @var array<string,int>|false $e */
            $e = unpack('vdisk/vcddisk/vn_disk/vn_total/Vcdsize/Vcdoff/vcomlen', substr($tail, $p + 4, 18));
            if ($e === false || $e['disk'] !== 0 || $e['cddisk'] !== 0 || $e['n_disk'] !== $e['n_total'] || $e['n_total'] < 1 || $e['n_total'] > self::MAX_ENTRIES) {
                return 'bad_directory';
            }
            if ($e['cdsize'] > 4194304 || $e['cdoff'] + $e['cdsize'] > $size || $e['cdsize'] < 46) {
                return 'bad_directory';
            }
            fseek($h, $e['cdoff']);
            $cd = (string) fread($h, $e['cdsize']);
            if (strlen($cd) !== $e['cdsize']) {
                return 'bad_directory';
            }
            $entries = [];
            $off = 0;
            $total = 0;
            for ($i = 0; $i < $e['n_total']; $i++) {
                if ($off + 46 > strlen($cd) || substr($cd, $off, 4) !== "PK\x01\x02") {
                    return 'bad_directory';
                }
                /** @var array<string,int> $r */
                $r = unpack('vmade/vneed/vflags/vmethod/vtime/vdate/Vcrc/Vcsize/Vusize/vnlen/velen/vclen/vdisk/viattr/Vxattr/Vloff', substr($cd, $off + 4, 42));
                $name = substr($cd, $off + 46, $r['nlen']);
                $off += 46 + $r['nlen'] + $r['elen'] + $r['clen'];
                if ($off > strlen($cd)) {
                    return 'bad_directory';
                }
                $lname = strtolower($name);
                if ($name === '' || strlen($name) > 260 || str_contains($name, "\0") || str_contains($name, '\\') || $name[0] === '/'
                    || preg_match('~(^|/)\.\.(/|$)~', $name) === 1 || isset($entries[$lname])) {
                    return 'bad_name';
                }
                if (($r['flags'] & 1) !== 0) {
                    return 'encrypted';
                }
                if ($r['csize'] === 0xFFFFFFFF || $r['usize'] === 0xFFFFFFFF || $r['loff'] === 0xFFFFFFFF || $r['loff'] + 30 + $r['nlen'] > $size) {
                    return 'bad_entry';
                }
                if (!in_array($r['method'], [0, 8], true)) {
                    return 'bad_method';
                }
                $total += $r['usize'];
                if ($total > self::MAX_UNPACKED || ($r['usize'] > 10485760 && $r['csize'] > 0 && intdiv($r['usize'], $r['csize']) > 1000)) {
                    return 'too_big_unpacked';
                }
                fseek($h, $r['loff']);
                if ((string) fread($h, 4) !== "PK\x03\x04") {
                    return 'bad_entry';
                }
                $entries[$lname] = $r + ['name' => $name];
            }
            if (!isset($entries['[content_types].xml'])) {
                return 'no_content_types';
            }
            $main = $ext === 'docx' ? 'word/document.xml' : 'xl/workbook.xml';
            $other = $ext === 'docx' ? 'xl/workbook.xml' : 'word/document.xml';
            if (!isset($entries[$main]) || isset($entries[$other])) {
                return 'wrong_type';
            }
            foreach (array_keys($entries) as $n) {
                foreach (self::ZIP_DENY as $rx) {
                    if (preg_match($rx, $n) === 1) {
                        return 'forbidden_part';
                    }
                }
            }
            $ct = self::zipEntry($h, $entries['[content_types].xml'], 1048576);
            if ($ct === null) {
                return 'bad_content_types';
            }
            $lct = strtolower($ct);
            if (str_contains($lct, 'macroenabled') || str_contains($lct, 'vbaproject') || str_contains($lct, 'ms-office.activex')) {
                return 'macro_content_type';
            }
            if (!str_contains($lct, $ext === 'docx' ? 'wordprocessingml.document.main+xml' : 'spreadsheetml.sheet.main+xml')) {
                return 'wrong_content_type';
            }
            $rels = 0;
            foreach ($entries as $n => $ent) {
                if (!str_ends_with($n, '.rels')) {
                    continue;
                }
                if (++$rels > 80 || $ent['usize'] > 262144) {
                    return 'too_many_rels';
                }
                $xml = self::zipEntry($h, $ent, 262144);
                if ($xml === null) {
                    return 'bad_rels';
                }
                if (preg_match_all('~<Relationship\b[^>]*>~i', $xml, $m) > 0) {
                    foreach ($m[0] as $tag) {
                        if (preg_match('~TargetMode\s*=\s*["\']External["\']~i', $tag) === 1 && preg_match('~Type\s*=\s*["\'][^"\']*/hyperlink["\']~i', $tag) !== 1) {
                            return 'external_relationship';
                        }
                    }
                }
            }
            return null;
        } catch (\Throwable) {
            return 'bad_structure';
        } finally {
            fclose($h);
        }
    }

    /** Contents of one ZIP entry (stored or deflated), at most $cap bytes; null when it cannot be read or is larger. @param array<string,int|string> $ent @param resource $h */
    private static function zipEntry($h, array $ent, int $cap): ?string
    {
        if ((int) $ent['usize'] > $cap || (int) $ent['csize'] > $cap) {
            return null;
        }
        fseek($h, (int) $ent['loff']);
        $lh = (string) fread($h, 30);
        if (strlen($lh) !== 30 || substr($lh, 0, 4) !== "PK\x03\x04") {
            return null;
        }
        /** @var array<string,int> $l */
        $l = unpack('vnlen/velen', substr($lh, 26, 4));
        fseek($h, (int) $ent['loff'] + 30 + $l['nlen'] + $l['elen']);
        $raw = (string) fread($h, (int) $ent['csize']);
        if (strlen($raw) !== (int) $ent['csize']) {
            return null;
        }
        if ((int) $ent['method'] === 0) {
            return $raw;
        }
        $out = @gzinflate($raw, $cap);
        return is_string($out) ? $out : null;
    }

    // ------------------------------------------------------------------------------------------------------------------------------ OLE (doc, xls)

    public static function ole(string $path, string $ext): ?string
    {
        $size = (int) @filesize($path);
        $h = @fopen($path, 'rb');
        if ($h === false || $size < 1024) {
            return 'unreadable';
        }
        try {
            $hd = (string) fread($h, 512);
            if (strlen($hd) !== 512 || !str_starts_with($hd, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")) {
                return 'not_ole';
            }
            /** @var array<string,int> $g */
            $g = unpack('vbyteorder/vsecshift/vminishift', substr($hd, 0x1C, 6));
            if ($g['byteorder'] !== 0xFFFE || !in_array($g['secshift'], [9, 12], true) || $g['minishift'] !== 6) {
                return 'bad_header';
            }
            $ss = 1 << $g['secshift'];
            /** @var array<string,int> $f */
            $f = unpack('Vnfat/Vdirstart/Vtx/Vcutoff/Vminifat/Vnminifat/Vdifat/Vndifat', substr($hd, 0x2C, 32));
            if ($f['nfat'] < 1 || $f['nfat'] > 4096 || $f['ndifat'] > 200 || $size < $ss * 2) {
                return 'bad_header';
            }
            $sectorCount = intdiv($size, $ss) - 1;

            $read = static function (int $n) use ($h, $ss, $size): string {
                $o = ($n + 1) * $ss;
                if ($n < 0 || $o >= $size) {
                    throw new \RuntimeException('sector out of range');
                }
                fseek($h, $o);
                $b = (string) fread($h, $ss);
                if ($b === '') {
                    throw new \RuntimeException('short read');
                }
                return str_pad($b, $ss, "\0");
            };

            // FAT sector ids: 109 in the header, then the DIFAT chain
            $fatIds = [];
            foreach (array_values((array) unpack('V109', substr($hd, 0x4C, 436))) as $id) {
                if ($id < 0xFFFFFFFA) {
                    $fatIds[] = $id;
                }
            }
            $d = $f['difat'];
            for ($i = 0; $i < $f['ndifat'] && $d < 0xFFFFFFFA; $i++) {
                $vals = array_values((array) unpack('V' . ($ss / 4), $read($d)));
                $d = (int) array_pop($vals);
                foreach ($vals as $id) {
                    if ($id < 0xFFFFFFFA) {
                        $fatIds[] = $id;
                    }
                }
            }
            if ($fatIds === [] || count($fatIds) > 4096) {
                return 'bad_fat';
            }
            $fat = [];
            foreach (array_slice($fatIds, 0, $f['nfat']) as $id) {
                foreach (array_values((array) unpack('V' . ($ss / 4), $read($id))) as $v) {
                    $fat[] = $v;
                }
            }
            $chain = static function (int $start, array $table) use ($sectorCount): array {
                $out = [];
                $n = $start;
                while ($n < 0xFFFFFFFA) {
                    if (!isset($table[$n]) || isset($out[$n]) || count($out) > $sectorCount + 1) {
                        throw new \RuntimeException('bad chain');
                    }
                    $out[$n] = true;
                    $n = (int) $table[$n];
                }
                return array_keys($out);
            };

            // the directory
            $dirIds = $chain($f['dirstart'], $fat);
            if (count($dirIds) > 1000) {
                return 'bad_directory';
            }
            $dir = '';
            foreach ($dirIds as $id) {
                $dir .= $read($id);
            }
            $entries = [];
            for ($o = 0; $o + 128 <= strlen($dir); $o += 128) {
                /** @var array<string,int> $q */
                $q = unpack('vnlen/Ctype', substr($dir, $o + 64, 3));
                if ($q['type'] === 0) {
                    continue;
                }
                $raw = substr($dir, $o, max(0, min(64, $q['nlen'] - 2)));
                $name = strtolower((string) preg_replace('/[\x00-\x1F]/', '', (string) mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE')));
                /** @var array<string,int> $s */
                $s = unpack('Vstart/Vsizelo/Vsizehi', substr($dir, $o + 116, 12));
                $entries[] = ['name' => $name, 'type' => $q['type'], 'start' => $s['start'], 'size' => $s['sizelo']];
                if (in_array($name, self::OLE_DENY, true)) {
                    return 'forbidden_storage';
                }
                if (count($entries) > 5000) {
                    return 'bad_directory';
                }
            }
            $want = $ext === 'doc' ? ['worddocument'] : ['workbook', 'book'];
            $stream = null;
            foreach ($entries as $en) {
                if ($en['type'] === 2 && in_array($en['name'], $want, true)) {
                    $stream = $en;
                    break;
                }
            }
            if ($stream === null) {
                return 'wrong_type';
            }
            if ($ext === 'doc') {
                return null;
            }

            // .xls: read the start of the Workbook stream and look at its BOUNDSHEET records (sheet type 1 = Excel 4.0 macro sheet, 6 = VBA module)
            $cap = 4194304;
            $data = '';
            if ($stream['size'] >= $f['cutoff']) {
                foreach ($chain($stream['start'], $fat) as $id) {
                    $data .= $read($id);
                    if (strlen($data) >= $cap) {
                        break;
                    }
                }
            } else {
                $root = null;
                foreach ($entries as $en) {
                    if ($en['type'] === 5) {
                        $root = $en;
                        break;
                    }
                }
                if ($root === null || $f['minifat'] >= 0xFFFFFFFA) {
                    return 'bad_ministream';
                }
                $container = '';
                foreach ($chain($root['start'], $fat) as $id) {
                    $container .= $read($id);
                }
                $miniTable = [];
                foreach ($chain($f['minifat'], $fat) as $id) {
                    foreach (array_values((array) unpack('V' . ($ss / 4), $read($id))) as $v) {
                        $miniTable[] = $v;
                    }
                }
                foreach ($chain($stream['start'], $miniTable) as $m) {
                    $data .= substr($container, $m * 64, 64);
                }
                $data = substr($data, 0, $stream['size']);
            }
            for ($p = 0, $len = strlen($data); $p + 4 <= $len; ) {
                /** @var array<string,int> $rec */
                $rec = unpack('vid/vlen', substr($data, $p, 4));
                if ($rec['id'] === 0x0085 && $rec['len'] >= 8 && $p + 4 + 6 <= $len) {
                    $dt = ord($data[$p + 4 + 5]);
                    if ($dt === 1 || $dt === 6) {
                        return 'macro_sheet';
                    }
                }
                if ($rec['id'] === 0x000A) {                     // end of the workbook globals: every sheet is declared before it
                    break;
                }
                $p += 4 + $rec['len'];
            }
            return null;
        } catch (\Throwable) {
            return 'bad_structure';
        } finally {
            fclose($h);
        }
    }
}
