<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Proof that an uploaded file really is a usable PDF (used by Uploader::pdf(); contract: docs/PARTNERS_NEWSLETTER.md).
 *
 *   $meta = PdfValidator::inspect($tmpPath, $maxBytes);     // ['size' => int, 'version' => '1.7', 'pages' => ?int]  or UploadException
 *
 * A file passes only when ALL of these hold: it is not empty and not above the limit; it STARTS with "%PDF-1.x" / "%PDF-2.x" (no junk before the
 * header, so a polyglot that is also an HTML page or a ZIP is refused); finfo says application/pdf; "%%EOF" appears in the last 2 KB (a truncated
 * upload is refused); it is not password-protected (readers could not open it); it contains no /Launch, /SubmitForm or /ImportData action and no embedded JavaScript.
 * The checks read the raw bytes of the WHOLE file (any size, in overlapping 1 MiB chunks) with "#xx" name escapes decoded, so they also work
 * without any PDF library. Compressed object streams can still hide a /JavaScript name from the scan in the default mode: the PDF is never executed by the
 * server and is shown by the visitor's own PDF viewer, which sandboxes it (right for the admin-only newsletter uploads).
 *
 * STRICT MODE ($strict = true, used for files that ANONYMOUS visitors upload through a form and that staff open): additionally (1) every /ObjStm
 * object stream is inflated (FlateDecode only, size-capped; any other filter, a predictor or a stream that does not inflate is refused) and the SAME scan is run on
 * the decoded objects, so JavaScript hidden in an object stream is found; (2) actions that reach outside the document are refused: /GoToR, /GoToE (remote / embedded
 * targets, which can leak credentials to a network share), embedded files (/EmbeddedFile, /EmbeddedFiles), /XFA forms, /RichMedia, /Movie, /Sound; (3) an /OpenAction that is a
 * /URI action is refused. Plain /URI link annotations stay allowed (every CV with a link has them and they need a click). A parser-less scan can never be
 * complete: downloads are attachments and staff should open applicants' PDFs in a protected / sandboxed viewer.
 */
final class PdfValidator
{
    private const CHUNK = 1048576;
    private const OVERLAP = 64;
    /** Actions that run code or leave the document. /JS is not listed: a /JS key only works inside an /S /JavaScript action, and 3 bytes would match random compressed data. */
    private const DENY = '~/(Launch|JavaScript|SubmitForm|ImportData)\b~';
    /** Extra refusals in strict mode: remote / embedded targets, embedded files, XFA, rich media. */
    private const DENY_STRICT = '~/(GoToR|GoToE|EmbeddedFiles?|XFA|RichMedia|Movie|Sound)\b|/OpenAction\s*<<[^>]{0,400}?/S\s*/URI\b~s';
    private const OBJSTM_MAX = 8388608;              // bytes inflated from one object stream
    private const OBJSTM_TOTAL = 33554432;           // bytes inflated from all object streams of one file

    /**
     * @return array{size:int,version:string,pages:?int}
     * @throws UploadException
     */
    public static function inspect(string $file, int $maxBytes, bool $strict = false): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new UploadException('The PDF could not be read.');
        }
        clearstatcache(true, $file);                 // the size decides how the file is read: never trust a cached value for a path that was just rewritten
        $size = (int) filesize($file);
        if ($size <= 0) {
            throw new UploadException('That file is empty.');
        }
        if ($size > $maxBytes) {
            throw new UploadException('That PDF is too large (' . self::human($size) . '). The limit is ' . self::human($maxBytes) . '. Compress it (for example "Reduce file size" in Acrobat, or export it again at lower image quality) and try again.');
        }
        $h = fopen($file, 'rb');
        if ($h === false) {
            throw new UploadException('The PDF could not be read.');
        }
        $head = (string) fread($h, 1024);
        fseek($h, max(0, $size - 2048));
        $tail = (string) fread($h, 2048);
        if (preg_match('/^%PDF-([12])\.(\d)/', $head, $m) !== 1) {
            throw new UploadException('That file is not a PDF. Export your newsletter as a PDF (File > Save as PDF) and upload that file.');
        }
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $fi ? strtolower((string) finfo_file($fi, $file)) : '';
            if ($mime !== '' && $mime !== 'application/pdf' && $mime !== 'application/x-pdf') {
                throw new UploadException('That file is not a real PDF document.');
            }
        }
        if (!str_contains($tail, '%%EOF')) {
            throw new UploadException('That PDF looks incomplete (it may have been cut off while uploading). Upload it again, or export it again from your program.');
        }
        // The WHOLE file is scanned, whatever its size (the limit is 25 MB by default, so this stays cheap), in 1 MiB steps that overlap by
        // 64 bytes so a name cut by a chunk border is still seen. Names are matched after "#xx" escapes are decoded: the PDF syntax allows
        // /S /Java#53cript, which a literal match would miss.
        $pages = 0;
        $encrypted = false;
        $scripted = false;
        for ($offset = 0; $offset < $size; $offset += self::CHUNK) {
            fseek($h, $offset);
            $buf = (string) fread($h, self::CHUNK + self::OVERLAP);
            if ($buf === '') {
                break;
            }
            $own = min(self::CHUNK, $size - $offset);
            $plain = str_contains($buf, '#')
                ? (string) preg_replace_callback('/#([0-9A-Fa-f]{2})/', static fn(array $mm): string => chr((int) hexdec($mm[1])), $buf)
                : $buf;
            if (preg_match('~/Encrypt\b~', $plain) === 1) {
                $encrypted = true;
            }
            if (preg_match(self::DENY, $plain) === 1 || ($strict && preg_match(self::DENY_STRICT, $plain) === 1)) {
                $scripted = true;
            }
            if (preg_match_all('~/Type\s*/Page(?![a-zA-Z])~', $buf, $mm, PREG_OFFSET_CAPTURE) > 0) {
                foreach ($mm[0] as $hit) {
                    if ($hit[1] < $own) {          // a hit that starts in the overlap belongs to the next chunk
                        $pages++;
                    }
                }
            }
        }
        fclose($h);
        if ($strict && !$scripted && !$encrypted) {
            $scripted = self::objectStreamsHostile($file, $size);
        }
        if ($encrypted) {
            throw new UploadException('That PDF is password-protected, so visitors could not open it. Save an unprotected copy and upload that.');
        }
        if ($scripted) {
            throw new UploadException('That PDF contains scripts or "launch" actions, which are not allowed on a public website. Export a plain copy (print to PDF) and upload that.');
        }
        $pages = $pages > 0 ? $pages : null;
        return ['size' => $size, 'version' => $m[1] . '.' . $m[2], 'pages' => $pages];
    }

    /**
     * Strict mode: inflate EVERY stream that is zlib data (not only the ones that call themselves /ObjStm: without a parser a dictionary cannot be located
     * reliably, since comments and names may contain any text) and run the same scan on the decoded bytes (also when the decoded bytes are zlib data again: a
     * filter chain). Every "stream" keyword is examined, none is skipped because an earlier fake keyword "swallowed" it, and the dictionary text of a stream is
     * everything since the end of the last stream that had already ENDED before it. Returns true when something hostile is found OR a stream cannot be proven
     * harmless: an /ObjStm that decodes to more than the cap, more than 3000 streams, zlib data of which nothing decodes next to /ObjStm (or more than 10 times elsewhere), a predictor /
     * /Crypt on an /ObjStm (the scan would see the bytes before the predictor is undone), and an /ObjStm that is not plain zlib data (an ASCII-hex / ASCII-85 /
     * LZW chain that could hide the objects).
     */
    private static function objectStreamsHostile(string $file, int $size): bool
    {
        $all = @file_get_contents($file);
        if ($all === false) {
            return true;
        }
        $pos = 0;
        $ends = [];                                  // where the streams examined so far end
        $count = 0;
        $bad = 0;
        $total = 0;
        while (($p = strpos($all, 'stream', $pos)) !== false) {
            $pos = $p + 6;
            if ($p >= 3 && substr($all, $p - 3, 3) === 'end') {
                continue;
            }
            if (++$count > 3000) {
                return true;
            }
            $start = $p + 6;
            if (isset($all[$start]) && $all[$start] === "\r") {
                $start++;
            }
            if (isset($all[$start]) && $all[$start] === "\n") {
                $start++;
            }
            $end = strpos($all, 'endstream', $start);
            $raw = $end === false ? substr($all, $start) : substr($all, $start, $end - $start);
            $prev = 0;
            foreach ($ends as $e) {
                if ($e <= $p && $e > $prev) {
                    $prev = $e;
                }
            }
            $region = substr($all, $prev, $p - $prev);
            $ends[] = $end === false ? $size : $end + 9;
            if (str_contains($region, '#')) {
                $region = (string) preg_replace_callback('/#([0-9A-Fa-f]{2})/', static fn(array $mm): string => chr((int) hexdec($mm[1])), $region);
            }
            $isObjStm = str_contains($region, '/ObjStm');
            if ($isObjStm && preg_match('~/(Predictor|Crypt)\b~', $region) === 1) {
                return true;
            }
            if (!self::looksLikeZlib($raw)) {
                // only an object stream can hold dictionaries (actions): one that is not plain zlib data (an ASCII-hex / ASCII-85 / LZW chain, which this scan does not decode)
                // cannot be proven harmless. ASCII-85 + Flate is normal for IMAGES and page content (ReportLab, older generators) and is fine there.
                if ($isObjStm || preg_match('~/Crypt\b~', $region) === 1) {
                    return true;
                }
                continue;
            }
            if (!$isObjStm && $total >= self::OBJSTM_TOTAL) {
                continue;                                         // the budget for scanning ordinary streams is spent; a stream that says /ObjStm is always scanned
            }
            $over = false;
            $data = self::inflate($raw, $over);
            if ($data === false && $end !== false) {
                $data = self::inflate(substr($all, $start), $over);      // a fake "endstream" inside the compressed bytes cut the data short
            }
            for ($level = 0; is_string($data) && $data !== '' && $level < 4; $level++) {
                if ($over && $isObjStm) {
                    return true;                                  // an object stream larger than the cap: a decompression bomb, or not provably harmless
                }
                $total += strlen($data);
                if ($total > self::OBJSTM_TOTAL && $isObjStm) {
                    return true;
                }
                $plain = str_contains($data, '#')
                    ? (string) preg_replace_callback('/#([0-9A-Fa-f]{2})/', static fn(array $mm): string => chr((int) hexdec($mm[1])), $data)
                    : $data;
                if (preg_match(self::DENY, $plain) === 1 || preg_match(self::DENY_STRICT, $plain) === 1) {
                    return true;
                }
                // (an object stream's own decoded bytes start with "<number> <number> ...", which can look like a zlib header by chance: not a filter chain)
                if ($over || !self::looksLikeZlib($data) || preg_match('/^\s*\d+\s+\d+\s/', substr($data, 0, 64)) === 1) {
                    break;
                }
                $inner = self::inflate($data, $over);             // a chain of filters: look inside again
                if ($inner === false || $inner === '') {
                    if ($isObjStm) {
                        return true;                              // an object stream whose inner layer cannot be decoded: not provably harmless
                    }
                    break;                                        // pixel data that only looks like zlib
                }
                $data = $inner;
            }
            if ($data === false) {
                // looks like zlib but nothing decodes: a stream of some other kind that starts with two lucky bytes (tolerated a few times), or damaged data that a lenient viewer might still read
                if (++$bad > 10 || $isObjStm) {
                    return true;
                }
            }
        }
        return false;
    }

    /** Do these bytes start like a zlib stream (deflate, 32 KB window, valid header checksum)? */
    private static function looksLikeZlib(string $b): bool
    {
        return strlen($b) >= 2 && ((ord($b[0]) & 0x0F) === 8) && ((ord($b[0]) * 256 + ord($b[1])) % 31 === 0);
    }

    /**
     * Inflate zlib data in small steps and keep what was decoded even when the data is damaged further on (a lenient PDF viewer reads such a stream too, so what it
     * would read must be scanned). At most OBJSTM_MAX bytes are decoded: $over tells the caller that there was more. @return string|false the decoded bytes (possibly
     * partial; '' for a complete stream with no content), false when nothing decodes
     */
    private static function inflate(string $raw, bool &$over = false): string|false
    {
        if (function_exists('inflate_init') && function_exists('inflate_get_status')) {
            $ctx = @inflate_init(ZLIB_ENCODING_DEFLATE);
            if ($ctx !== false) {
                $out = '';
                $ended = false;
                $n = strlen($raw);
                for ($i = 0; $i < $n; $i += 512) {
                    $part = @inflate_add($ctx, substr($raw, $i, 512), ZLIB_SYNC_FLUSH);
                    if ($part === false) {
                        break;
                    }
                    $out .= $part;
                    if (strlen($out) > self::OBJSTM_MAX) {
                        $over = true;
                        break;
                    }
                    if (inflate_get_status($ctx) === ZLIB_STREAM_END) {
                        $ended = true;
                        break;
                    }
                }
                return $out !== '' || $ended ? $out : false;          // an empty but complete stream (a blank page) is fine; nothing decoding at all is not
            }
        }
        $data = @gzuncompress($raw, self::OBJSTM_MAX);
        if ($data === false) {
            $data = @gzuncompress(rtrim($raw, "\r\n"), self::OBJSTM_MAX);
        }
        if ($data === false) {
            $data = @gzinflate(substr($raw, 2), self::OBJSTM_MAX);
        }
        return $data;
    }

    private static function human(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
