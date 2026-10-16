<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Safe file uploads. All methods throw UploadException (getMessage() is safe to show to users).
 *
 * PUBLIC images -> uploads/YYYY/MM/<random>.<ext>   (served by Apache; store the RELATIVE path "YYYY/MM/x.jpg" in the DB;
 *                                                     render with upload_url($path) / img_url($path))
 *   $path = Uploader::image($request->file('photo'));                   // 5 MB max, jpg/png/gif/webp only
 *   $paths = Uploader::images($request->files('images'));               // multi-upload; stops at the first bad file
 *   Uploader::delete($oldPath);
 *
 * PRIVATE documents -> storage/private/<folder>/YYYY/MM/<random>.<ext>   (NEVER web accessible; stream via an auth-gated controller)
 *   $info = Uploader::privateFile($request->file('cv'), 'cv', config('upload.cv_exts'));   // ['path','name','size','mime']
 *   $info = Uploader::privateFile($request->file('file'), 'documents');                     // reports (pdf/doc(x)/xls(x)/ppt(x)/csv/txt)
 *   return Uploader::download($row['file_path'], 'Annual-report-2024.pdf');                 // after YOU checked auth / is_public
 *   Uploader::deletePrivate($row['file_path']);
 *
 * Safety: finfo MIME sniffing + magic bytes + getimagesize(), extension allow-list, random server-side names, size and
 * pixel limits, rejects php/phtml/phar/html/svg/js/exe... ANYWHERE in the client file name (double extensions).
 * Privacy: with GD the image is downsized to upload.max_image_width and re-encoded (JPEG/PNG/WebP), which drops EXIF/GPS.
 * Without GD (or when GD cannot handle the file) ImageScrubber removes EXIF/GPS/XMP/IPTC/text chunks in pure PHP; a file that
 * cannot be cleaned is REJECTED rather than published with its location data. SVG is rejected on purpose (scriptable).
 */
final class Uploader
{
    private const BAD_PARTS = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar', 'html', 'htm', 'xhtml', 'shtml', 'svg',
        'svgz', 'js', 'mjs', 'exe', 'dll', 'bat', 'cmd', 'com', 'sh', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'htaccess', 'htpasswd', 'ini', 'swf', 'jar', 'vbs', 'ps1'];

    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    private const DOC_MIME = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/cdfv2', 'application/x-ole-storage', 'application/vnd.ms-office', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/cdfv2', 'application/x-ole-storage', 'application/vnd.ms-office', 'application/octet-stream'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/cdfv2', 'application/x-ole-storage', 'application/vnd.ms-office', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'],
        'txt'  => ['text/plain'],
    ];

    /** Download Content-Type per extension. */
    private const DOWNLOAD_MIME = [
        'pdf' => 'application/pdf', 'doc' => 'application/msword', 'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'csv' => 'text/csv', 'txt' => 'text/plain',
    ];

    // ------------------------------------------------------------------ public images

    /** Store an uploaded image under uploads/YYYY/MM/. Returns the path relative to uploads/. */
    public static function image(?array $file, ?int $maxBytes = null): string
    {
        $file = self::requireFile($file);
        $max = $maxBytes ?? (int) Config::get('upload.max_image_bytes', 5 * 1024 * 1024);
        self::guard($file, $max);
        $name = (string) $file['name'];
        $ext = self::checkName($name, (array) Config::get('upload.image_exts'));

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new UploadException('That file is not a valid image.');
        }
        $mime = (string) ($info['mime'] ?? '');
        $sniffed = self::sniff($file['tmp_name']);
        if (!isset(self::IMAGE_TYPES[$mime]) || ($sniffed !== '' && $sniffed !== $mime)) {
            throw new UploadException('Only JPG, PNG, GIF or WebP images are allowed.');
        }
        if (((int) $info[0]) * ((int) $info[1]) > (int) Config::get('upload.max_pixels', 40000000) || $info[0] < 1 || $info[1] < 1) {
            throw new UploadException('That image is too large in pixels. Please resize it and try again.');
        }
        $finalExt = self::IMAGE_TYPES[$mime];

        $rel = date('Y') . '/' . date('m');
        $dir = UPLOAD_PATH . '/' . $rel;
        self::ensureDir($dir);
        $stored = bin2hex(random_bytes(16)) . '.' . $finalExt;
        $dest = $dir . '/' . $stored;

        if (!self::optimise($file['tmp_name'], $dest, $mime, (int) $info[0], (int) $info[1])) {
            // no GD / GD could not re-encode it: strip location and camera metadata ourselves, never publish the original bytes
            $clean = ImageScrubber::scrub((string) file_get_contents($file['tmp_name']), $mime);
            if ($clean === null || @file_put_contents($dest, $clean) === false) {
                @unlink($dest);
                throw new UploadException(self::msg('image_unprocessable', 'That image could not be processed. Please re-save it from a photo editor and try again.'));
            }
        }
        @chmod($dest, 0644);
        return $rel . '/' . $stored;
    }

    /**
     * @param array<int,array> $files normalised list from Request::files()
     * @return string[] relative paths (already-stored files are removed again if a later file fails)
     */
    public static function images(array $files, ?int $maxBytes = null): array
    {
        $done = [];
        try {
            foreach ($files as $f) {
                $done[] = self::image($f, $maxBytes);
            }
        } catch (UploadException $e) {
            foreach ($done as $p) {
                self::delete($p);
            }
            throw $e;
        }
        return $done;
    }

    /**
     * One call for an image form field (partials/admin/image-field.php): handles a multipart file "<name>_file", a posted
     * path "<name>" (uploaded via AJAX by admin.js) and a removal flag "<name>_remove".
     * Returns the value to store (relative path, 'assets/...' path or null). Throws UploadException on a bad file.
     *   $data['image'] = Uploader::resolveImageField($request, 'image', $row['image'] ?? null);
     *
     * The replaced/removed upload is NOT deleted here: once the request has finished (the row is saved by then) it is handed to
     * ImageCleanup::release(), which deletes it only when nothing else (another row, a page block, a rich-text body, a
     * duplicated page) still uses it. A failed save therefore never loses a file, and a posted path must be a real stored image.
     */
    public static function resolveImageField(Request $request, string $name, ?string $current): ?string
    {
        $r = \App\Services\ImageCleanup::resolve($request, $name, $current);
        if ($r['changed'] && $current !== null && $current !== '') {
            $old = $current;
            register_shutdown_function(static function () use ($old): void {
                try {
                    \App\Services\ImageCleanup::release($old);
                } catch (\Throwable) {
                    // keep the file when unsure
                }
            });
        }
        return $r['value'];
    }

    /** Delete a public upload (relative path as stored). Silently ignores bad paths. */
    public static function delete(?string $rel): bool
    {
        $rel = (string) $rel;
        if ($rel === '' || str_starts_with($rel, 'assets/') || preg_match('~^https?://~i', $rel)) {
            return false;                              // bundled assets and external URLs are never deleted
        }
        if (!preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-]+\.[a-z0-9]+$~', $rel) || str_contains($rel, '..')) {
            return false;
        }
        $abs = UPLOAD_PATH . '/' . $rel;
        return is_file($abs) ? @unlink($abs) : false;
    }

    // ------------------------------------------------------------------ private documents

    /**
     * Store a document OUTSIDE the web root. Returns ['path' (relative to storage/private), 'name' (clean original name), 'size', 'mime'].
     * @param string[]|null $exts allowed extensions (default: upload.document_exts)
     * @return array{path:string,name:string,size:int,mime:string}
     */
    public static function privateFile(?array $file, string $folder = 'documents', ?array $exts = null, ?int $maxBytes = null): array
    {
        $file = self::requireFile($file);
        if (!preg_match('/^[a-z0-9_\-]+$/', $folder)) {
            throw new \InvalidArgumentException('Bad private folder name.');
        }
        $max = $maxBytes ?? (int) ($folder === 'cv' ? Config::get('upload.max_cv_bytes') : Config::get('upload.max_document_bytes'));
        self::guard($file, $max);
        $exts = $exts ?? (array) Config::get('upload.document_exts');
        $name = (string) $file['name'];
        $ext = self::checkName($name, $exts);

        $sniffed = strtolower(self::sniff($file['tmp_name']));
        $allowedMimes = self::DOC_MIME[$ext] ?? [];
        if ($sniffed === '' || !in_array($sniffed, $allowedMimes, true) || !self::magicOk($file['tmp_name'], $ext)) {
            throw new UploadException(self::msg('upload_invalid', 'That file does not look like a valid .{ext} document.', ['ext' => $ext]));
        }

        $rel = $folder . '/' . date('Y') . '/' . date('m');
        $dir = STORAGE_PATH . '/private/' . $rel;
        self::ensureDir($dir);
        $stored = bin2hex(random_bytes(16)) . '.' . $ext;
        self::move($file['tmp_name'], $dir . '/' . $stored);
        @chmod($dir . '/' . $stored, 0640);

        $clean = trim((string) preg_replace('/[^A-Za-z0-9._ \-()]+/', '_', basename($name)));
        return [
            'path' => $rel . '/' . $stored,
            'name' => mb_substr($clean !== '' ? $clean : 'document.' . $ext, 0, 190),
            'size' => (int) $file['size'],
            'mime' => self::DOWNLOAD_MIME[$ext] ?? 'application/octet-stream',
        ];
    }

    /** Absolute path of a private file; throws if $rel is not a well-formed path inside storage/private. */
    public static function privatePath(string $rel): string
    {
        if (!preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)+\.[a-z0-9]+$~', $rel) || str_contains($rel, '..')) {
            throw new HttpException(404);
        }
        $abs = STORAGE_PATH . '/private/' . $rel;
        $real = realpath($abs);
        $root = realpath(STORAGE_PATH . '/private');
        if ($real === false || $root === false || !str_starts_with(str_replace(chr(92), '/', $real), str_replace(chr(92), '/', $root) . '/')) {
            throw new HttpException(404);
        }
        return $real;
    }

    /**
     * Stream a private file. The CALLER must already have authorised the user (admin session, or is_public document).
     */
    public static function download(string $rel, ?string $downloadName = null, bool $inline = false): Response
    {
        $abs = self::privatePath($rel);
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        return Response::download($abs, $downloadName ?: basename($abs), self::DOWNLOAD_MIME[$ext] ?? 'application/octet-stream', $inline);
    }

    public static function deletePrivate(?string $rel): bool
    {
        try {
            return $rel ? @unlink(self::privatePath((string) $rel)) : false;
        } catch (HttpException) {
            return false;
        }
    }

    // ------------------------------------------------------------------ internals

    /**
     * Visitor-facing upload message from the editable "Error pages" registry (Admin > Pages > Error pages > Form messages), with
     * {placeholders} filled in. Falls back to the built-in wording when the database is unavailable.
     * @param array<string,string> $vars
     */
    private static function msg(string $key, string $default, array $vars = []): string
    {
        $text = $default;
        try {
            $v = content('site_errors', 'form.' . $key, $default);
            if (is_string($v) && trim($v) !== '') {
                $text = trim($v);
            }
        } catch (\Throwable) {
        }
        $repl = [];
        foreach ($vars as $k => $val) {
            $repl['{' . $k . '}'] = $val;
        }
        return strtr($text, $repl);
    }

    private static function requireFile(?array $file): array
    {
        if ($file === null || !isset($file['tmp_name'], $file['error'])) {
            throw new UploadException('Please choose a file to upload.');
        }
        return $file;
    }

    private static function guard(array $file, int $max): void
    {
        $err = (int) $file['error'];
        if ($err !== UPLOAD_ERR_OK) {
            throw new UploadException(match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => self::msg('upload_server_limit', 'That file is larger than the server allows.'),
                UPLOAD_ERR_PARTIAL                        => 'The upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE                        => 'Please choose a file to upload.',
                default                                   => 'The file could not be uploaded (error ' . $err . ').',
            });
        }
        $tmp = (string) $file['tmp_name'];
        if (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp)) {
            throw new UploadException('Invalid upload.');
        }
        if (!is_file($tmp)) {
            throw new UploadException('Invalid upload.');
        }
        $size = (int) filesize($tmp);
        if ($size <= 0) {
            throw new UploadException('That file is empty.');
        }
        if ($size > $max) {
            throw new UploadException(self::msg('upload_too_large', 'That file is too large (maximum {max}).', ['max' => self::human($max)]));
        }
    }

    /** Validates the client file name and returns the lower-cased extension. */
    private static function checkName(string $name, array $allowedExts): string
    {
        if ($name === '' || str_contains($name, "\0")) {
            throw new UploadException('Invalid file name.');
        }
        $parts = explode('.', strtolower(basename(str_replace(chr(92), '/', $name))));
        if (count($parts) < 2) {
            throw new UploadException('The file must have an extension.');
        }
        $ext = array_pop($parts);
        foreach ($parts as $p) {
            if (in_array(trim($p), self::BAD_PARTS, true)) {
                throw new UploadException('That file name is not allowed.');
            }
        }
        if (in_array($ext, self::BAD_PARTS, true) || !in_array($ext, array_map('strtolower', $allowedExts), true)) {
            throw new UploadException(self::msg('upload_type', 'That file type is not allowed. Allowed: {types}.', ['types' => strtoupper(implode(', ', $allowedExts))]));
        }
        return $ext;
    }

    private static function sniff(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $fi ? (string) finfo_file($fi, $path) : '';
        return strtolower($mime);
    }

    private static function magicOk(string $path, string $ext): bool
    {
        $h = fopen($path, 'rb');
        if ($h === false) {
            return false;
        }
        $head = (string) fread($h, 8192);
        fclose($h);
        switch ($ext) {
            case 'pdf':
                return str_starts_with(ltrim($head, "\xEF\xBB\xBF \t\r\n"), '%PDF-') || str_contains(substr($head, 0, 1024), '%PDF-');
            case 'doc':
            case 'xls':
            case 'ppt':
                return str_starts_with($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1");
            case 'docx':
            case 'xlsx':
            case 'pptx':
                return str_starts_with($head, "PK\x03\x04");
            case 'csv':
            case 'txt':
                return !str_contains($head, "\0") && (mb_check_encoding($head, 'UTF-8') || mb_check_encoding($head, 'Windows-1252'));
        }
        return false;
    }

    private static function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new UploadException('The upload folder is not writable.');
        }
    }

    private static function move(string $tmp, string $dest): void
    {
        $ok = PHP_SAPI === 'cli' ? @copy($tmp, $dest) : @move_uploaded_file($tmp, $dest);
        if (!$ok) {
            throw new UploadException('The file could not be saved.');
        }
    }

    /** GD downscale + re-encode when GD is available. Returns true if $dest was written. */
    private static function optimise(string $src, string $dest, string $mime, int $w, int $h): bool
    {
        if (!function_exists('imagecreatetruecolor') || $mime === 'image/gif') {
            return false;
        }
        // GD needs about 5 bytes per pixel (twice when the photo has to be rotated): skip it, instead of dying with a fatal
        // "memory exhausted" that no try/catch can stop, when the image would not fit into memory_limit. The scrubber takes over.
        if (!self::fitsInMemory($w, $h, $mime === 'image/jpeg' ? 2 : 1)) {
            return false;
        }
        $maxW = (int) Config::get('upload.max_image_width', 2000);
        $quality = (int) Config::get('upload.quality', 85);
        $img = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($src) : false,
            'image/png'  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($src) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
            default      => false,
        };
        if (!$img) {
            return false;
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $o = (int) ($exif['Orientation'] ?? 1);
            $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
            if ($rot !== 0 && function_exists('imagerotate')) {
                $rotated = imagerotate($img, $rot, 0);
                if ($rotated) {
                    $img = $rotated;
                    $w = imagesx($img);
                    $h = imagesy($img);
                }
            }
        }
        if ($w > $maxW) {
            $nh = (int) round($h * ($maxW / $w));
            $resized = imagecreatetruecolor($maxW, $nh);
            if ($mime !== 'image/jpeg') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxW, $nh, $w, $h);
            $img = $resized;
        }
        if ($mime === 'image/png') {
            imagesavealpha($img, true);
        }
        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($img, $dest, $quality),
            'image/png'  => imagepng($img, $dest, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($img, $dest, $quality) : false,
            default      => false,
        };
        return (bool) $ok;
    }

    private static function fitsInMemory(int $w, int $h, int $copies): bool
    {
        $limit = trim((string) ini_get('memory_limit'));
        if ($limit === '' || $limit === '-1') {
            return true;
        }
        $n = (float) $limit;
        $n *= match (strtolower(substr($limit, -1))) { 'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1 };
        $free = $n - memory_get_usage(true) - 8 * 1048576;           // keep 8 MB of head-room
        return ($w * $h * 5 * $copies) < $free;
    }

    private static function human(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : round($bytes / 1024) . ' KB';
    }
}
