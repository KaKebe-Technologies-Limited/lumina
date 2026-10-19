<?php
declare(strict_types=1);

namespace App\Services\Forms;

use App\Core\HttpException;
use App\Core\ImageScrubber;
use App\Core\PdfValidator;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;

/**
 * FILES UPLOADED THROUGH A FORM. Private storage only: storage/private/forms/YYYY/MM/<32 hex>.<ext> (never web accessible, 0640, random names). Contract:
 * docs/FORMS.md section 9.
 *
 * What is accepted: the extensions in config form_builder.file_types (pdf doc docx xls xlsx jpg png) and, per upload field, a subset of them.
 * Every file is checked by CONTENT, not by the name the browser sent: magic bytes, finfo MIME sniffing, a PDF structure check (PdfValidator: no JavaScript,
 * no launch actions, not encrypted), a decoded-image check with a pixel limit for pictures (which are re-written without EXIF / GPS / text chunks by
 * ImageScrubber), a document-container check (Office files must be a real OLE / ZIP container, macro-enabled files are refused) and a scan for
 * embedded `<?php` / `<script`. SVG, HTML, executables, scripts and double extensions such as `cv.php.pdf` are refused. Sizes are capped per file, per
 * field and per submission.
 *
 * Methods throw App\Core\UploadException; getMessage() is visitor-safe and comes from the editable wording (Admin > Pages > Forms > Error messages).
 */
final class FormFileStore
{
    public const FOLDER = 'forms';

    /** Any of these as a part of the client file name refuses the file (same list as App\Core\Uploader). */
    private const BAD_PARTS = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar', 'html', 'htm', 'xhtml', 'shtml', 'svg',
        'svgz', 'js', 'mjs', 'exe', 'dll', 'bat', 'cmd', 'com', 'sh', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'htaccess', 'htpasswd', 'ini', 'swf', 'jar', 'vbs', 'ps1',
        'docm', 'xlsm', 'pptm', 'dotm', 'xlam', 'xlsb', 'hta', 'msi', 'scr', 'lnk', 'zip', 'rar', '7z'];

    /** extension => MIME types finfo may report for a genuine file of that kind. */
    private const SNIFF = [
        'pdf'  => ['application/pdf', 'application/x-pdf'],
        'doc'  => ['application/msword', 'application/cdfv2', 'application/x-ole-storage', 'application/vnd.ms-office', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/cdfv2', 'application/x-ole-storage', 'application/vnd.ms-office', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
        'jpg'  => ['image/jpeg'],
        'png'  => ['image/png'],
    ];

    /** Content-Type of the download. */
    private const DOWNLOAD = [
        'pdf' => 'application/pdf', 'doc' => 'application/msword', 'xls' => 'application/vnd.ms-excel',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg', 'png' => 'image/png',
    ];

    /** Largest single upload in MB: the lowest of config form_builder.file_max_mb, Site settings > Forms and what the server accepts (at least 1). */
    public static function maxMb(): int
    {
        $mb = (int) config('form_builder.file_max_mb', 25);
        try {
            $set = (int) setting('forms_max_upload_mb', '5');
            if ($set >= 1) {
                $mb = min($mb, $set);
            }
        } catch (\Throwable) {
        }
        $server = Uploader::serverUploadLimit();
        if ($server > 0) {
            $mb = min($mb, max(1, (int) floor($server / 1048576)));
        }
        return max(1, $mb);
    }

    /** MIME type used when the file is downloaded. */
    public static function downloadMime(string $ext): string
    {
        return self::DOWNLOAD[strtolower($ext)] ?? 'application/octet-stream';
    }

    /**
     * Prove that an uploaded file is acceptable, WITHOUT storing it.
     *
     * @param array{name?:string,tmp_name?:string,error?:int,size?:int} $file one entry of Request::files()
     * @param array{types?:list<string>,max_mb?:int} $rules the field's `file` rules
     * @return array{ext:string,mime:string,size:int,name:string}
     * @throws UploadException
     */
    public static function inspect(array $file, array $rules): array
    {
        $allowed = array_values(array_intersect(array_map('strtolower', (array) ($rules['types'] ?? ['pdf'])), FormDefinition::allowedFileTypes()));
        $maxMb = max(1, min(self::maxMb(), (int) ($rules['max_mb'] ?? 5)));
        $max = $maxMb * 1048576;
        $typesText = strtoupper(implode(', ', $allowed));

        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            throw new UploadException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => FormText::get('errors.file_size', ['max' => $maxMb]),
                default                                   => FormText::get('errors.file_upload'),
            });
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp) || (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp))) {
            throw new UploadException(FormText::get('errors.file_upload'));
        }
        clearstatcache(true, $tmp);
        $size = (int) filesize($tmp);
        if ($size <= 0) {
            throw new UploadException(FormText::get('errors.file_empty'));
        }
        if ($size > $max) {
            throw new UploadException(FormText::get('errors.file_size', ['max' => $maxMb]));
        }

        $name = (string) ($file['name'] ?? '');
        if ($name === '' || str_contains($name, "\0")) {
            throw new UploadException(FormText::get('errors.file_type', ['types' => $typesText]));
        }
        $parts = explode('.', strtolower(basename(str_replace(chr(92), '/', $name))));
        if (count($parts) < 2) {
            throw new UploadException(FormText::get('errors.file_type', ['types' => $typesText]));
        }
        $ext = array_pop($parts);
        $ext = $ext === 'jpeg' ? 'jpg' : $ext;
        foreach ($parts as $p) {
            if (in_array(trim($p), self::BAD_PARTS, true)) {
                throw new UploadException(FormText::get('errors.file_type', ['types' => $typesText]));
            }
        }
        if (in_array($ext, self::BAD_PARTS, true) || !in_array($ext, $allowed, true)) {
            throw new UploadException(FormText::get('errors.file_type', ['types' => $typesText]));
        }

        $invalid = static fn(): UploadException => new UploadException(FormText::get('errors.file_invalid', ['types' => strtoupper($ext)]));
        $sniffed = self::sniff($tmp);
        $head = self::read($tmp, 0, 8192);
        if ($head === '') {
            throw $invalid();
        }
        if (preg_match('/<\?php|<\?=|<script\b/i', $head) === 1) {
            throw $invalid();
        }
        switch ($ext) {
            case 'pdf':
                try {
                    PdfValidator::inspect($tmp, $max, true);          // strict: object streams are inflated and scanned, remote / embedded-file actions refused
                } catch (UploadException) {
                    throw $invalid();
                }
                break;
            case 'jpg':
            case 'png':
                $info = @getimagesize($tmp);
                $want = $ext === 'jpg' ? 'image/jpeg' : 'image/png';
                if ($info === false || (string) ($info['mime'] ?? '') !== $want || ($sniffed !== '' && $sniffed !== $want)) {
                    throw $invalid();
                }
                if ((int) $info[0] < 1 || (int) $info[1] < 1 || (int) $info[0] * (int) $info[1] > (int) config('upload.max_pixels', 40000000)) {
                    throw $invalid();
                }
                break;
            case 'doc':
            case 'xls':
                if (!str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") || ($sniffed !== '' && !in_array($sniffed, self::SNIFF[$ext], true))) {
                    throw $invalid();
                }
                // the OLE directory is walked: macros (VBA / Macros storages, Excel 4.0 macro sheets), embedded objects and encryption are refused
                if (OfficeInspector::ole($tmp, $ext) !== null) {
                    throw $invalid();
                }
                break;
            case 'docx':
            case 'xlsx':
                if (!str_starts_with($head, "PK\x03\x04") || ($sniffed !== '' && !in_array($sniffed, self::SNIFF[$ext], true))) {
                    throw $invalid();
                }
                // a real ZIP: its central directory is parsed (main part, content types, no macros / ActiveX / embedded objects / remote relationships)
                if (OfficeInspector::zip($tmp, $ext) !== null) {
                    throw $invalid();
                }
                break;
        }
        return ['ext' => $ext, 'mime' => self::downloadMime($ext), 'size' => $size, 'name' => self::cleanName($name, $ext)];
    }

    /**
     * Inspect and store ONE file privately. Returns what goes into `form_files` (path relative to storage/private).
     *
     * @return array{path:string,name:string,mime:string,size:int,ext:string}
     * @throws UploadException
     */
    public static function store(array $file, array $rules): array
    {
        $meta = self::inspect($file, $rules);
        $tmp = (string) $file['tmp_name'];
        $rel = self::FOLDER . '/' . date('Y') . '/' . date('m');
        $dir = STORAGE_PATH . '/private/' . $rel;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new UploadException(FormText::get('errors.file_upload'));
        }
        $stored = bin2hex(random_bytes(16)) . '.' . $meta['ext'];
        $dest = $dir . '/' . $stored;
        $size = $meta['size'];

        if (in_array($meta['ext'], ['jpg', 'png'], true)) {
            // pictures are re-written without EXIF / GPS / XMP / text chunks; a picture that cannot be cleaned is refused
            $clean = ImageScrubber::scrub((string) file_get_contents($tmp), $meta['ext'] === 'jpg' ? 'image/jpeg' : 'image/png');
            if ($clean === null || preg_match('/<\?php|<\?=|<script\b/i', $clean) === 1 || @file_put_contents($dest, $clean) === false) {
                @unlink($dest);
                throw new UploadException(FormText::get('errors.file_invalid', ['types' => strtoupper($meta['ext'])]));
            }
            $size = strlen($clean);
        } else {
            $ok = PHP_SAPI === 'cli' ? @copy($tmp, $dest) : @move_uploaded_file($tmp, $dest);
            if (!$ok) {
                @unlink($dest);
                throw new UploadException(FormText::get('errors.file_upload'));
            }
        }
        @chmod($dest, 0640);
        return ['path' => $rel . '/' . $stored, 'name' => $meta['name'], 'mime' => $meta['mime'], 'size' => $size, 'ext' => $meta['ext']];
    }

    /**
     * Store the files of every field. All or nothing: when one file fails, the files stored before it are deleted again.
     *
     * @param array<string,list<array>>                 $files fieldId => list of upload arrays
     * @param array<string,array<string,mixed>>         $rules fieldId => `file` rules
     * @return array<string,list<array{path:string,name:string,mime:string,size:int,ext:string}>>
     * @throws UploadException
     */
    public static function storeAll(array $files, array $rules): array
    {
        $done = [];
        try {
            foreach ($files as $fieldId => $list) {
                foreach ($list as $file) {
                    $done[$fieldId][] = self::store($file, (array) ($rules[$fieldId] ?? []));
                }
            }
        } catch (\Throwable $e) {
            self::deleteAll($done);
            throw $e;
        }
        return $done;
    }

    /** @param array<string,list<array{path:string}>> $stored */
    public static function deleteAll(array $stored): void
    {
        foreach ($stored as $list) {
            foreach ($list as $f) {
                self::delete((string) $f['path']);
            }
        }
    }

    /** Delete one stored file (path relative to storage/private). Only files inside forms/ are ever touched. */
    public static function delete(?string $path): bool
    {
        $path = (string) $path;
        if (!self::validPath($path)) {
            return false;
        }
        return Uploader::deletePrivate($path);
    }

    public static function validPath(string $path): bool
    {
        return str_starts_with($path, self::FOLDER . '/') && !str_contains($path, '..')
            && preg_match('~^forms/[0-9]{4}/[0-9]{2}/[a-f0-9]{32}\.(?:pdf|doc|docx|xls|xlsx|jpg|png)$~', $path) === 1;
    }

    /**
     * Stream a stored file to an AUTHENTICATED admin (the caller checked forms.submissions and that the file belongs to the submission of the form
     * in the URL). Always an attachment with X-Content-Type-Options: nosniff and no caching.
     */
    public static function stream(array $fileRow): Response
    {
        $path = (string) ($fileRow['stored_path'] ?? '');
        if (!self::validPath($path)) {
            throw new HttpException(404);
        }
        $abs = Uploader::privatePath($path);                       // 404 unless it is a real file inside storage/private
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $name = self::cleanName((string) ($fileRow['original_name'] ?? ''), $ext);
        return Response::download($abs, $name, self::downloadMime($ext), false);
    }

    /** Delete the stored files of the given form_files rows (rows themselves are removed by the caller / by ON DELETE CASCADE). @param list<array<string,mixed>> $rows */
    public static function purgeRows(array $rows): int
    {
        $n = 0;
        foreach ($rows as $r) {
            if (self::delete((string) ($r['stored_path'] ?? ''))) {
                $n++;
            }
        }
        return $n;
    }

    /**
     * The name a person sees and saves a file under: the client name without path, letters / digits / accents / dashes kept, anything else an
     * underscore, at most 150 characters, always ending in the REAL extension.
     */
    public static function cleanName(string $client, string $ext): string
    {
        $base = basename(str_replace(chr(92), '/', $client));
        $stem = (string) preg_replace('/\.[A-Za-z0-9]{1,5}$/', '', $base);
        $clean = preg_replace('/[^\p{L}\p{N}\p{M}\p{Pd}._ ()\',&+]+/u', '_', $stem);
        if ($clean === null) {
            $clean = (string) preg_replace('/[^A-Za-z0-9._ \-()]+/', '_', $stem);
        }
        $clean = trim((string) preg_replace('/ {2,}/', ' ', $clean));
        $clean = rtrim(mb_substr($clean, 0, 150), ' .');
        return ($clean !== '' && trim($clean, '_') !== '' ? $clean : 'file') . '.' . strtolower($ext);
    }

    private static function sniff(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }
        try {
            $fi = @finfo_open(FILEINFO_MIME_TYPE);
            // libmagic can refuse tiny or odd files ("Invalid argument"): an unknown type is handled by the byte checks below, never by a crash
            return $fi ? strtolower((string) @finfo_file($fi, $path)) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    private static function read(string $path, int $offset, int $length): string
    {
        $h = @fopen($path, 'rb');
        if ($h === false) {
            return '';
        }
        if ($offset > 0) {
            fseek($h, $offset);
        }
        $data = (string) fread($h, max(1, $length));
        fclose($h);
        return $data;
    }
}
