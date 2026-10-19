<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Server-side image pipeline for LOGOS (partners). Used by Uploader::logo() and App\Services\PartnerLogos; contract: docs/PARTNERS_NEWSLETTER.md.
 *
 *   $r = ImageOptimizer::logo($absolutePathOfAnImage, ['trim' => true]);        // throws UploadException (message safe to show to the admin)
 *   $r['logo'] ......... fallback picture, relative to uploads/ ("partners/2026/10/ab12.png"); ALWAYS set
 *   $r['logo_2x'] ...... same picture at exactly twice the size, or null (the source was too small to give a real 2x: never upscaled)
 *   $r['logo_webp'], $r['logo_webp_2x'] ... WebP versions (quality 85) or null (GD without WebP support)
 *   $r['logo_width'], $r['logo_height'] ... pixel size of the 1x picture (use them as width / height attributes: no layout shift)
 *   $r['optimised'] .... true when the pipeline ran; false when GD is missing and the validated original (metadata removed) was stored
 *   $r['notice'] ....... null, or a sentence for the admin ("GD is not installed, the logo was stored as uploaded")
 *   $r['warnings'] ..... list of codes: low_resolution, trimmed, background_removed, opaque_background, no_webp
 *   $r['files'] ........ every relative path written (delete them if the database save fails)
 *
 * Steps: validate the REAL type (magic bytes + finfo + getimagesize; PNG, JPEG, WebP, GIF only; SVG / HTML / PHP / polyglots refused;
 * at most 6000 x 6000 px and 5 MB; the decode must fit into memory_limit) -> decode -> EXIF orientation -> metadata dropped (re-encoding)
 * -> TRIM transparent or near-white margins -> fit inside 480 x 240 (1x) / 960 x 480 (2x) including a small safe padding, transparency kept,
 * never upscaled, never distorted -> WebP (+ PNG fallback, or JPEG fallback for big opaque pictures).
 *
 * Capability detection happens at run time (GD, WebP, EXIF, finfo): nothing is assumed about the host. Tests can simulate a host without
 * GD or WebP with forceCapabilities().
 */
final class ImageOptimizer
{
    /** Box a logo is fitted into at 1x (the 2x picture is exactly twice as large). */
    public const BOX_W = 480;
    public const BOX_H = 240;
    /** Transparent safe padding around the trimmed logo at 1x (px). */
    public const PADDING = 6;
    /** Largest accepted side / file. */
    public const MAX_SIDE = 6000;
    public const MAX_BYTES = 5 * 1024 * 1024;
    public const WEBP_QUALITY = 85;
    /** Working copy for trimming never exceeds this side (keeps the pixel loops fast). */
    private const WORK_SIDE = 1920;
    /** A 1x picture narrower than this is not worth a 2x pair: the source is used once, at its own size. */
    private const MIN_1X_FOR_PAIR = 200;
    /** An opaque (no transparency) PNG fallback bigger than this becomes a JPEG instead. */
    private const PNG_FALLBACK_LIMIT = 81920;

    private const MIME_EXT = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    /** @var array<string,bool>|null */
    private static ?array $forced = null;

    // ------------------------------------------------------------------ capabilities

    /**
     * What this PHP can do. Keys: gd, png, jpeg, webp (decode AND encode), gif, exif, finfo, rotate.
     * @return array<string,bool>
     */
    public static function capabilities(): array
    {
        $gd = function_exists('imagecreatetruecolor');
        $caps = [
            'gd'     => $gd,
            'png'    => $gd && function_exists('imagecreatefrompng') && function_exists('imagepng'),
            'jpeg'   => $gd && function_exists('imagecreatefromjpeg') && function_exists('imagejpeg'),
            'webp'   => $gd && function_exists('imagecreatefromwebp') && function_exists('imagewebp'),
            'gif'    => $gd && function_exists('imagecreatefromgif'),
            'exif'   => function_exists('exif_read_data'),
            'finfo'  => function_exists('finfo_open'),
            'rotate' => $gd && function_exists('imagerotate'),
        ];
        if (self::$forced !== null) {
            foreach (self::$forced as $k => $v) {
                $caps[$k] = $v;
            }
            if (!$caps['gd']) {
                $caps['png'] = $caps['jpeg'] = $caps['webp'] = $caps['gif'] = $caps['rotate'] = false;
            }
        }
        return $caps;
    }

    /** TESTS ONLY: pretend some capabilities are missing, e.g. ['gd' => false] or ['webp' => false]. null restores the real ones. */
    public static function forceCapabilities(?array $caps): void
    {
        self::$forced = $caps;
    }

    /** Short sentence for the admin: what the server can do with logos (shown in the Partners screen when something is missing). */
    public static function capabilityNotice(): ?string
    {
        $c = self::capabilities();
        if (!$c['gd']) {
            return 'The image library (GD) is not installed on this server, so logos cannot be trimmed, resized or converted. They are stored as uploaded (with location data removed). Ask your host to enable the PHP "gd" extension for sharper, faster logos.';
        }
        if (!$c['webp']) {
            return 'This server cannot create WebP pictures, so logos are saved as PNG only. They still look the same; pages are just a little heavier.';
        }
        return null;
    }

    // ------------------------------------------------------------------ validation

    /**
     * Prove that a file really is a PNG / JPEG / WebP / GIF image of acceptable size. Does not decode it.
     *
     * @param array{max_bytes?:int,max_side?:int} $opts
     * @return array{mime:string,ext:string,width:int,height:int,bytes:int}
     * @throws UploadException
     */
    public static function inspect(string $file, array $opts = []): array
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new UploadException('The picture could not be read.');
        }
        $bytes = (int) filesize($file);
        $maxBytes = (int) ($opts['max_bytes'] ?? self::MAX_BYTES);
        $maxSide = (int) ($opts['max_side'] ?? self::MAX_SIDE);
        if ($bytes <= 0) {
            throw new UploadException('That file is empty.');
        }
        if ($bytes > $maxBytes) {
            throw new UploadException('That picture is too large (' . self::human($bytes) . '). The limit is ' . self::human($maxBytes) . '. Save a smaller copy and try again.');
        }
        $h = fopen($file, 'rb');
        $head = $h !== false ? (string) fread($h, 16) : '';
        if ($h !== false) {
            fclose($h);
        }
        $magic = match (true) {
            str_starts_with($head, "\x89PNG\r\n\x1a\n")                                  => 'image/png',
            str_starts_with($head, "\xFF\xD8\xFF")                                       => 'image/jpeg',
            str_starts_with($head, 'GIF87a'), str_starts_with($head, 'GIF89a')           => 'image/gif',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP'            => 'image/webp',
            default                                                                       => '',
        };
        if ($magic === '') {
            throw new UploadException('Only PNG, JPG, WebP or GIF pictures are allowed. (SVG, PDF and other formats are not accepted: save the logo as a PNG.)');
        }
        if (self::caps('finfo')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $sniffed = $fi ? strtolower((string) finfo_file($fi, $file)) : '';
            if ($sniffed !== '' && $sniffed !== $magic) {
                throw new UploadException('That file is not what its name says it is. Only real PNG, JPG, WebP or GIF pictures are allowed.');
            }
        }
        $info = @getimagesize($file);
        if ($info === false || (string) ($info['mime'] ?? '') !== $magic) {
            throw new UploadException('That file is not a valid picture (it may be damaged). Open it in an image editor and save it again as a PNG.');
        }
        $w = (int) $info[0];
        $hgt = (int) $info[1];
        if ($w < 1 || $hgt < 1) {
            throw new UploadException('That picture has no size.');
        }
        if ($w > $maxSide || $hgt > $maxSide) {
            throw new UploadException('That picture is too large in pixels (' . $w . ' x ' . $hgt . '). The limit is ' . $maxSide . ' x ' . $maxSide . '. A logo needs at most about 1500 px wide.');
        }
        self::refuseHiddenCode($file);
        return ['mime' => $magic, 'ext' => self::MIME_EXT[$magic], 'width' => $w, 'height' => $hgt, 'bytes' => $bytes];
    }

    /** Pictures that carry script or markup inside their bytes (polyglots) are refused outright, whatever the re-encoding would do. */
    private static function refuseHiddenCode(string $file): void
    {
        $h = fopen($file, 'rb');
        if ($h === false) {
            return;
        }
        $tail = '';
        while (!feof($h)) {
            $chunk = (string) fread($h, 262144);
            $buf = $tail . $chunk;
            if (preg_match('/<\?php|<script|<svg|<html|<!doctype/i', $buf) === 1) {
                fclose($h);
                throw new UploadException('That file contains hidden program code or markup and was refused. Export the logo again from your design program as a plain PNG.');
            }
            $tail = substr($buf, -16);
        }
        fclose($h);
    }

    // ------------------------------------------------------------------ the pipeline

    /**
     * Optimise a logo that is already a file on disk (an upload's temporary file, or an existing file below uploads/).
     * See the class comment for the result. Everything written is removed again if a later step fails.
     *
     * @param array{trim?:bool,dir?:string,max_bytes?:int} $opts dir = folder below uploads/ (default partners/YYYY/MM)
     * @return array<string,mixed>
     * @throws UploadException
     */
    public static function logo(string $file, array $opts = []): array
    {
        $info = self::inspect($file, $opts);
        $caps = self::capabilities();
        $dir = self::targetDir((string) ($opts['dir'] ?? ''));
        $canDecode = $caps['gd'] && match ($info['mime']) {
            'image/png'  => $caps['png'],
            'image/jpeg' => $caps['jpeg'],
            'image/webp' => $caps['webp'],
            'image/gif'  => $caps['gif'],
            default      => false,
        };
        if (!$canDecode) {
            return self::storeOriginal($file, $info, $dir, !$caps['gd']
                ? 'The image library (GD) is not installed on this server, so the logo was stored as uploaded (location data removed) and could not be trimmed or resized.'
                : 'This server cannot read ' . strtoupper($info['ext']) . ' pictures with its image library, so the logo was stored as uploaded (location data removed).');
        }
        // a decoded picture needs about 4 bytes per pixel, twice while it is converted: refuse politely instead of dying with "memory exhausted"
        if (!self::fitsInMemory($info['width'], $info['height'], 2.6)) {
            throw new UploadException('That picture (' . $info['width'] . ' x ' . $info['height'] . ' px) is too large for this server to process. Please save a copy that is at most 2000 px wide and upload that.');
        }

        $written = [];
        try {
            return self::process($file, $info, $dir, (bool) ($opts['trim'] ?? true), $caps, $written);
        } catch (\Throwable $e) {
            foreach ($written as $rel) {
                Uploader::delete($rel);
            }
            if ($e instanceof UploadException) {
                throw $e;
            }
            Log::warning('ImageOptimizer: ' . $e->getMessage());
            throw new UploadException('That picture could not be processed. Open it in an image editor, save it again as a PNG and try once more.');
        }
    }

    /**
     * @param array<string,mixed> $info
     * @param array<string,bool> $caps
     * @param list<string> $written filled as files are created
     * @return array<string,mixed>
     */
    private static function process(string $file, array $info, string $dir, bool $trim, array $caps, array &$written): array
    {
        $img = match ($info['mime']) {
            'image/png'  => @imagecreatefrompng($file),
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/webp' => @imagecreatefromwebp($file),
            default      => @imagecreatefromgif($file),
        };
        if (!$img instanceof \GdImage) {
            throw new UploadException('That picture could not be read (it may be damaged). Open it in an image editor and save it again as a PNG.');
        }
        $img = self::toTruecolor($img);
        if ($info['mime'] === 'image/jpeg' && $caps['exif'] && $caps['rotate']) {
            $img = self::applyOrientation($img, $file);
        }
        // working copy: never more than WORK_SIDE on the long side (a 6000 px scan is not needed to make a 928 px logo)
        $img = self::fitWithin($img, self::WORK_SIDE, self::WORK_SIDE);
        $w = imagesx($img);
        $h = imagesy($img);

        $hasAlpha = self::hasTransparency($img);
        $warnings = [];
        // a JPEG logo on "paper white" (#F4F4F0, cream, light grey) would show a tinted rectangle inside the white box: make that flat background transparent
        if (!$hasAlpha && ($bg = self::flatLightBackground($img)) !== null) {
            $img = self::keyOutBackground($img, $bg);
            $w = imagesx($img);
            $h = imagesy($img);
            $hasAlpha = true;
            $warnings[] = 'background_removed';
        }
        $box = $trim ? self::contentBox($img, $hasAlpha) : ['x' => 0, 'y' => 0, 'w' => $w, 'h' => $h];
        if ($box === null) {
            throw new UploadException('That picture looks blank (nothing but empty or white space). Choose the picture of the logo itself.');
        }
        if ($box['w'] < $w || $box['h'] < $h) {
            $warnings[] = 'trimmed';
            $img = self::crop($img, $box['x'], $box['y'], $box['w'], $box['h']);
            $w = $box['w'];
            $h = $box['h'];
        }
        if (!$hasAlpha) {
            $warnings[] = 'opaque_background';
        }

        // ---- sizes. Inner box (padding excluded) at 1x; the 2x picture is EXACTLY twice the 1x picture so srcset="1x, 2x" never changes the shown size.
        $pad = self::PADDING;
        $fit1 = min((self::BOX_W - 2 * $pad) / $w, (self::BOX_H - 2 * $pad) / $h);
        $s2 = min(1.0, 2 * $fit1);                        // best scale a 2x picture can have without upscaling
        $w1 = max(1, (int) round($w * $s2 / 2));
        $h1 = max(1, (int) round($h * $s2 / 2));
        $pair = $w1 + 2 * $pad >= self::MIN_1X_FOR_PAIR && $w1 * 2 <= $w + 1 && $h1 * 2 <= $h + 1;
        if (!$pair) {
            $s1 = min(1.0, $fit1);
            $w1 = max(1, (int) round($w * $s1));
            $h1 = max(1, (int) round($h * $s1));
        }
        if ($w < 320 && $h < 160) {
            $warnings[] = 'low_resolution';            // sharp on a normal screen, soft on a retina one: the admin is told to upload a larger file
        }

        $rand = bin2hex(random_bytes(12));
        $variants = [['1x', $w1, $h1, $pad]];
        if ($pair) {
            $variants[] = ['2x', $w1 * 2, $h1 * 2, $pad * 2];
        }
        $out = ['logo' => null, 'logo_2x' => null, 'logo_webp' => null, 'logo_webp_2x' => null];
        $dirAbs = UPLOAD_PATH . '/' . $dir;
        if (!is_dir($dirAbs) && !@mkdir($dirAbs, 0775, true) && !is_dir($dirAbs)) {
            throw new UploadException('The upload folder is not writable.');
        }
        $sizes = [];
        foreach ($variants as [$tag, $cw, $ch, $p]) {
            $canvas = self::canvas($cw + 2 * $p, $ch + 2 * $p);
            imagecopyresampled($canvas, $img, $p, $p, 0, 0, $cw, $ch, $w, $h);
            $suffix = $tag === '2x' ? '-2x' : '';
            // fallback picture: PNG; an opaque photographic logo that would be a heavy PNG becomes a JPEG
            $png = $dirAbs . '/' . $rand . $suffix . '.png';
            if (!@imagepng($canvas, $png, 9)) {
                throw new UploadException('The server could not save the logo (is the uploads folder writable?).');
            }
            $fallbackRel = $dir . '/' . $rand . $suffix . '.png';
            $written[] = $fallbackRel;
            if (!$hasAlpha && $caps['jpeg'] && (int) filesize($png) > self::PNG_FALLBACK_LIMIT) {
                $white = self::canvas($cw + 2 * $p, $ch + 2 * $p, true);
                imagecopy($white, $canvas, 0, 0, 0, 0, $cw + 2 * $p, $ch + 2 * $p);
                $jpg = $dirAbs . '/' . $rand . $suffix . '.jpg';
                if (@imagejpeg($white, $jpg, 88) && (int) filesize($jpg) < (int) filesize($png)) {
                    @unlink($png);
                    array_pop($written);
                    $fallbackRel = $dir . '/' . $rand . $suffix . '.jpg';
                    $written[] = $fallbackRel;
                } else {
                    @unlink($jpg);
                }
            }
            @chmod(UPLOAD_PATH . '/' . $fallbackRel, 0644);
            $out[$tag === '2x' ? 'logo_2x' : 'logo'] = $fallbackRel;
            $sizes[$fallbackRel] = (int) filesize(UPLOAD_PATH . '/' . $fallbackRel);
            if ($caps['webp']) {
                $webp = $dirAbs . '/' . $rand . $suffix . '.webp';
                if (@imagewebp($canvas, $webp, self::WEBP_QUALITY) && is_file($webp) && filesize($webp) > 0) {
                    @chmod($webp, 0644);
                    $rel = $dir . '/' . $rand . $suffix . '.webp';
                    $written[] = $rel;
                    $out[$tag === '2x' ? 'logo_webp_2x' : 'logo_webp'] = $rel;
                    $sizes[$rel] = (int) filesize($webp);
                } else {
                    @unlink($webp);
                }
            }
        }
        if (!$caps['webp']) {
            $warnings[] = 'no_webp';
        }
        return $out + [
            'logo_width'  => $w1 + 2 * $pad,
            'logo_height' => $h1 + 2 * $pad,
            'optimised'   => true,
            'notice'      => null,
            'warnings'    => $warnings,
            'files'       => $written,
            'sizes'       => $sizes,
            'source'      => ['width' => $info['width'], 'height' => $info['height'], 'mime' => $info['mime'], 'bytes' => $info['bytes']],
        ];
    }

    /**
     * No GD (or it cannot read this format): keep the validated ORIGINAL, with camera / location / text metadata removed by ImageScrubber.
     * A file that cannot be cleaned is refused rather than published.
     *
     * @param array<string,mixed> $info
     * @return array<string,mixed>
     */
    private static function storeOriginal(string $file, array $info, string $dir, string $notice): array
    {
        $clean = ImageScrubber::scrub((string) file_get_contents($file), (string) $info['mime']);
        if ($clean === null) {
            throw new UploadException('That picture could not be cleaned of hidden data. Open it in an image editor, save it again as a PNG and try once more.');
        }
        $dirAbs = UPLOAD_PATH . '/' . $dir;
        if (!is_dir($dirAbs) && !@mkdir($dirAbs, 0775, true) && !is_dir($dirAbs)) {
            throw new UploadException('The upload folder is not writable.');
        }
        $rel = $dir . '/' . bin2hex(random_bytes(12)) . '.' . $info['ext'];
        if (@file_put_contents(UPLOAD_PATH . '/' . $rel, $clean) === false) {
            throw new UploadException('The server could not save the logo (is the uploads folder writable?).');
        }
        @chmod(UPLOAD_PATH . '/' . $rel, 0644);
        return [
            'logo' => $rel, 'logo_2x' => null, 'logo_webp' => null, 'logo_webp_2x' => null,
            'logo_width' => (int) $info['width'], 'logo_height' => (int) $info['height'],
            'optimised' => false, 'notice' => $notice, 'warnings' => ['not_optimised'], 'files' => [$rel],
            'sizes' => [$rel => strlen($clean)],
            'source' => ['width' => $info['width'], 'height' => $info['height'], 'mime' => $info['mime'], 'bytes' => $info['bytes']],
        ];
    }

    // ------------------------------------------------------------------ GD helpers

    /** Folder below uploads/ for a new logo: partners/YYYY/MM unless the caller names a safe one. */
    private static function targetDir(string $dir): string
    {
        $dir = trim($dir, '/');
        if ($dir === '' || preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+){0,3}$~', $dir) !== 1) {
            $dir = 'partners/' . date('Y') . '/' . date('m');
        }
        return $dir;
    }

    private static function caps(string $key): bool
    {
        return self::capabilities()[$key] ?? false;
    }

    /** A fresh truecolor canvas: transparent (alpha channel kept) or opaque white. */
    private static function canvas(int $w, int $h, bool $white = false): \GdImage
    {
        $c = imagecreatetruecolor($w, $h);
        if ($white) {
            imagefill($c, 0, 0, (int) imagecolorallocate($c, 255, 255, 255));
            return $c;
        }
        imagealphablending($c, false);
        imagesavealpha($c, true);
        imagefill($c, 0, 0, (int) imagecolorallocatealpha($c, 255, 255, 255, 127));
        return $c;
    }

    private static function toTruecolor(\GdImage $img): \GdImage
    {
        if (!imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        return $img;
    }

    /** Scale down (never up) so the picture fits inside $maxW x $maxH. */
    private static function fitWithin(\GdImage $img, int $maxW, int $maxH): \GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= $maxW && $h <= $maxH) {
            return $img;
        }
        $s = min($maxW / $w, $maxH / $h);
        $nw = max(1, (int) round($w * $s));
        $nh = max(1, (int) round($h * $s));
        $out = self::canvas($nw, $nh);
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $out;
    }

    private static function crop(\GdImage $img, int $x, int $y, int $w, int $h): \GdImage
    {
        $out = self::canvas($w, $h);
        imagecopy($out, $img, 0, 0, $x, $y, $w, $h);
        return $out;
    }

    /** Honour the camera's rotation flag (EXIF Orientation 2..8). The metadata itself disappears because the picture is re-encoded. */
    private static function applyOrientation(\GdImage $img, string $file): \GdImage
    {
        $exif = @exif_read_data($file, 'IFD0');
        $o = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        if ($o < 2 || $o > 8) {
            return $img;
        }
        $rot = static function (\GdImage $i, int $deg): \GdImage {
            $r = imagerotate($i, $deg, (int) imagecolorallocatealpha($i, 255, 255, 255, 127));
            if (!$r instanceof \GdImage) {
                return $i;
            }
            imagealphablending($r, false);
            imagesavealpha($r, true);
            return $r;
        };
        switch ($o) {
            case 2: imageflip($img, IMG_FLIP_HORIZONTAL); return $img;
            case 3: return $rot($img, 180);
            case 4: imageflip($img, IMG_FLIP_VERTICAL); return $img;
            case 5: $img = $rot($img, -90); imageflip($img, IMG_FLIP_HORIZONTAL); return $img;
            case 6: return $rot($img, -90);
            case 7: imageflip($img, IMG_FLIP_HORIZONTAL); return $rot($img, -90);
            default: return $rot($img, 90);
        }
    }

    /** Does the picture have (mostly) transparent pixels? Sampled on a grid; enough to tell a cut-out logo from a photo on white. */
    private static function hasTransparency(\GdImage $img): bool
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $sx = max(1, intdiv($w, 48));
        $sy = max(1, intdiv($h, 48));
        for ($y = 0; $y < $h; $y += $sy) {
            for ($x = 0; $x < $w; $x += $sx) {
                if ((((int) imagecolorat($img, $x, $y)) >> 24 & 0x7F) >= 100) {
                    return true;
                }
            }
        }
        // corners and edges matter most for cut-outs: check the four corners exactly
        foreach ([[0, 0], [$w - 1, 0], [0, $h - 1], [$w - 1, $h - 1]] as [$x, $y]) {
            if ((((int) imagecolorat($img, $x, $y)) >> 24 & 0x7F) >= 100) {
                return true;
            }
        }
        return false;
    }

    /**
     * The colour of a flat, LIGHT, off-white background (a JPEG logo on #F4F4F0, cream or light grey), or null. It must be one colour in all four
     * corners and along at least 93% of the sampled border, every channel at least 200, and NOT already white enough (>= 244) for the white
     * rules (trim + multiply) to handle. Dark or coloured backgrounds (a deliberate badge) and photos / gradients are never touched.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    private static function flatLightBackground(\GdImage $img): ?array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w < 8 || $h < 8) {
            return null;
        }
        $px = static function (int $x, int $y) use ($img): array {
            $c = (int) imagecolorat($img, $x, $y);
            return [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
        };
        $corners = [$px(0, 0), $px($w - 1, 0), $px(0, $h - 1), $px($w - 1, $h - 1)];
        $avg = [0, 0, 0];
        foreach ($corners as $c) {
            for ($k = 0; $k < 3; $k++) {
                $avg[$k] += $c[$k] / 4;
            }
        }
        $avg = [(int) round($avg[0]), (int) round($avg[1]), (int) round($avg[2])];
        if (min($avg) < 200 || min($avg) >= 244) {
            return null;
        }
        $near = static fn(array $c, int $tol): bool => abs($c[0] - $avg[0]) <= $tol && abs($c[1] - $avg[1]) <= $tol && abs($c[2] - $avg[2]) <= $tol;
        foreach ($corners as $c) {
            if (!$near($c, 12)) {
                return null;
            }
        }
        $total = 0;
        $match = 0;
        $stepX = max(1, intdiv($w, 48));
        $stepY = max(1, intdiv($h, 48));
        for ($x = 0; $x < $w; $x += $stepX) {
            foreach ([0, $h - 1] as $y) {
                $total++;
                $match += $near($px($x, $y), 18) ? 1 : 0;
            }
        }
        for ($y = 0; $y < $h; $y += $stepY) {
            foreach ([0, $w - 1] as $x) {
                $total++;
                $match += $near($px($x, $y), 18) ? 1 : 0;
            }
        }
        return $match / max(1, $total) >= 0.93 ? $avg : null;
    }

    /**
     * Copy of the picture where pixels equal to the flat background become transparent (a soft ramp for the anti-aliased edge, so there is no halo).
     * A very large picture is first scaled down to about one million pixels: the loop below runs once per pixel in PHP, and a logo is shown at most
     * 960 px wide anyway.
     *
     * @param array{0:int,1:int,2:int} $bg
     */
    private static function keyOutBackground(\GdImage $img, array $bg): \GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w * $h > 1000000) {
            $s = sqrt(1000000 / ($w * $h));
            $img = self::fitWithin($img, max(1, (int) floor($w * $s)), max(1, (int) floor($h * $s)));
            $w = imagesx($img);
            $h = imagesy($img);
        }
        $out = self::canvas($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopy($out, $img, 0, 0, 0, 0, $w, $h);
        $t0 = 10;                       // up to this distance from the background colour: fully transparent (JPEG noise)
        $t1 = 32;                       // beyond it: fully opaque; in between a linear ramp
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = (int) imagecolorat($img, $x, $y);
                $d = max(abs((($c >> 16) & 0xFF) - $bg[0]), abs((($c >> 8) & 0xFF) - $bg[1]), abs(($c & 0xFF) - $bg[2]));
                if ($d >= $t1) {
                    continue;
                }
                $a = $d <= $t0 ? 127 : (int) round(127 * ($t1 - $d) / ($t1 - $t0));
                imagesetpixel($out, $x, $y, ($a << 24) | ($c & 0xFFFFFF));
            }
        }
        return $out;
    }

    /**
     * Bounding box of the real content: transparent pixels are empty; in a picture WITHOUT transparency, near-white pixels are empty
     * (a white-background JPG). A white logo on a transparent background is therefore never mistaken for empty space.
     * Null when nothing is left.
     *
     * @return array{x:int,y:int,w:int,h:int}|null
     */
    private static function contentBox(\GdImage $img, bool $hasAlpha): ?array
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $empty = static function (int $c) use ($hasAlpha): bool {
            if ((($c >> 24) & 0x7F) >= 100) {
                return true;
            }
            return !$hasAlpha && (($c >> 16) & 0xFF) >= 244 && (($c >> 8) & 0xFF) >= 244 && ($c & 0xFF) >= 244;
        };
        $top = -1;
        for ($y = 0; $y < $h && $top < 0; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if (!$empty((int) imagecolorat($img, $x, $y))) {
                    $top = $y;
                    break;
                }
            }
        }
        if ($top < 0) {
            return null;
        }
        $bottom = $top;
        for ($y = $h - 1; $y > $top; $y--) {
            for ($x = 0; $x < $w; $x++) {
                if (!$empty((int) imagecolorat($img, $x, $y))) {
                    $bottom = $y;
                    break 2;
                }
            }
        }
        $left = $w;
        $right = -1;
        for ($y = $top; $y <= $bottom; $y++) {
            for ($x = 0; $x < $left; $x++) {
                if (!$empty((int) imagecolorat($img, $x, $y))) {
                    $left = $x;
                    break;
                }
            }
            for ($x = $w - 1; $x > $right; $x--) {
                if (!$empty((int) imagecolorat($img, $x, $y))) {
                    $right = $x;
                    break;
                }
            }
        }
        if ($right < $left) {
            return null;
        }
        return ['x' => $left, 'y' => $top, 'w' => $right - $left + 1, 'h' => $bottom - $top + 1];
    }

    // ------------------------------------------------------------------ memory + misc

    /** Would a $w x $h picture, held $copies times at 4 bytes per pixel, fit into the free memory (8 MB head-room kept)? */
    public static function fitsInMemory(int $w, int $h, float $copies = 2.6): bool
    {
        $limit = trim((string) ini_get('memory_limit'));
        if ($limit === '' || $limit === '-1') {
            return true;
        }
        $n = (float) $limit;
        $n *= match (strtolower(substr($limit, -1))) { 'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1 };
        $free = $n - memory_get_usage(true) - 8 * 1048576;
        return ($w * $h * 4 * $copies) < $free;
    }

    public static function human(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1) . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
