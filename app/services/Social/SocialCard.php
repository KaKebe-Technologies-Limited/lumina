<?php
declare(strict_types=1);

namespace App\Services\Social;

use App\Core\Database;
use App\Models\Newsletter;
use App\Models\SocialImage;
use App\Services\Newsletter\NewsletterContent;
use App\Services\Newsletter\NewsletterService;

/**
 * SHARE IMAGES (docs/SOCIAL.md section 8). OWNER: IMAGES agent.
 *
 * Branded pictures drawn with GD and the bundled Liberation fonts (assets/fonts/card, SIL OFL, see docs/MEMBERSHIP_CARD.md):
 *   square    1080 x 1080   Instagram, Facebook, X, Telegram
 *   portrait  1080 x 1920   TikTok, stories, reels covers
 *   landscape 1200 x  630   link previews (LinkedIn, WhatsApp, messengers)
 * Content: the issue cover photo cropped to fill (or a navy / gold / orange brand background), a dark gradient, the logo, issue number and date, the title in
 * large type (word-wrap, shrink-to-fit, characters the font lacks are dropped), a short summary and the site address. Designer settings: SocialConfig social_card_*.
 *
 * Storage: storage/private/social/<issue id>/<variant>.jpg (outside the web root; the root .htaccess serves nothing but assets/ and uploads/). The picture is public ONLY
 * through GET /social/image/{token}.jpg: the token (64 hex = 256 random bits, table social_images, UNIQUE (issue, variant) so a re-render keeps the address a platform
 * already holds) exists ONLY for issues that are public, and resolve() checks again on every request that the issue still is.
 *
 * Without GD / FreeType / the font files available() is false: ensure() returns null and publicUrl() falls back to the cover photo, else the bundled logo.
 * Rendering never throws: any failure is logged (no data) and reported as null.
 */
final class SocialCard
{
    public const VARIANTS = ['square', 'portrait', 'landscape'];
    /** bump to re-render every stored picture after a change of the drawing code */
    public const RENDER_VERSION = 2;                 // 2: contrast veils behind the text on light photos, issue line and PDF word from the page registry
    public const QUALITY = 88;
    private const FONT_SANS = 'LiberationSans-Regular.ttf';
    private const FONT_SANS_BOLD = 'LiberationSans-Bold.ttf';
    private const FONT_SERIF_BOLD = 'LiberationSerif-Bold.ttf';
    /** @var array<string,string> best use of each picture (the kit page says it) */
    public const USE = [
        'square'    => 'Instagram, Facebook, X, Telegram',
        'portrait'  => 'TikTok, Instagram and Facebook stories, reels covers',
        'landscape' => 'LinkedIn, WhatsApp and any link preview',
    ];

    /** @var ?bool test hook: force GD "off" (null = detect) */
    public static ?bool $forceUnavailable = null;

    /** Can images be drawn on this server (GD with FreeType, JPEG and the bundled fonts)? */
    public static function available(): bool
    {
        if (self::$forceUnavailable === true) {
            return false;
        }
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagettftext') || !function_exists('imagettfbbox') || !function_exists('imagejpeg')) {
            return false;
        }
        foreach ([self::FONT_SANS, self::FONT_SANS_BOLD, self::FONT_SERIF_BOLD] as $f) {
            if (!is_file(self::fontPath($f))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Absolute public address of the share image of a PUBLIC issue. Never creates a token for an issue that is not public.
     */
    public static function publicUrl(int $newsletterId, string $variant = 'square'): ?string
    {
        if (!in_array($variant, self::VARIANTS, true)) {
            $variant = 'square';
        }
        $row = Newsletter::find($newsletterId);
        if ($row === null) {
            return null;
        }
        if (NewsletterService::isLive($row)) {
            $made = self::ensure($newsletterId, $variant);
            if ($made !== null) {
                return $made['url'];
            }
        }
        return self::fallbackUrl($row);
    }

    /**
     * Render (when missing, or when what it is drawn from changed) and return the token record of a stored image. Only for a PUBLIC issue.
     * @return ?array{token:string,path:string,url:string}
     */
    public static function ensure(int $newsletterId, string $variant): ?array
    {
        if (!in_array($variant, self::VARIANTS, true) || !self::available()) {
            return null;
        }
        try {
            $row = Newsletter::find($newsletterId);
            if ($row === null || !NewsletterService::isLive($row)) {
                return null;
            }
            $hash = self::sourceHash($row, $variant);
            $rec = Database::one('SELECT token, path, src_hash FROM social_images WHERE newsletter_id = ? AND variant = ?', [$newsletterId, $variant]);
            $rel = $newsletterId . '/' . $variant . '.jpg';
            $abs = self::baseDir() . '/' . $rel;
            if ($rec !== null && (string) $rec['src_hash'] === $hash && (string) $rec['path'] === $rel && is_file($abs)) {
                return ['token' => (string) $rec['token'], 'path' => $rel, 'url' => self::tokenUrl((string) $rec['token'])];
            }
            $bytes = self::draw($row, $variant);
            if ($bytes === null) {
                return null;
            }
            if (!self::writeFile($abs, $bytes)) {
                return null;
            }
            Database::run(
                'INSERT INTO social_images (token, newsletter_id, variant, path, src_hash) VALUES (?, ?, ?, ?, ?) '
                . 'ON DUPLICATE KEY UPDATE path = VALUES(path), src_hash = VALUES(src_hash)',
                [bin2hex(random_bytes(32)), $newsletterId, $variant, $rel, $hash]
            );
            $token = (string) Database::value('SELECT token FROM social_images WHERE newsletter_id = ? AND variant = ?', [$newsletterId, $variant], '');
            if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
                return null;
            }
            return ['token' => $token, 'path' => $rel, 'url' => self::tokenUrl($token)];
        } catch (\Throwable $e) {
            self::logFail('ensure', $e);
            return null;
        }
    }

    /**
     * Resolve a token for the public route. Null (= 404) for an unknown token, an issue that is not (or no longer) public, a path outside the store, a missing file
     * that cannot be redrawn.
     * @return ?array{file:string,mime:string,mtime:int,etag:string}
     */
    public static function resolve(string $token): ?array
    {
        if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
            return null;
        }
        try {
            $rec = Database::one('SELECT token, newsletter_id, variant, path, src_hash FROM social_images WHERE token = ?', [$token]);
            if ($rec === null) {
                return null;
            }
            $id = (int) $rec['newsletter_id'];
            $variant = (string) $rec['variant'];
            $row = Newsletter::find($id);
            if ($row === null || !NewsletterService::isLive($row) || !in_array($variant, self::VARIANTS, true)) {
                return null;
            }
            if ((string) $rec['path'] !== $id . '/' . $variant . '.jpg') {
                return null;
            }
            $file = self::baseDir() . '/' . $id . '/' . $variant . '.jpg';
            if (!is_file($file)) {
                $again = self::ensure($id, $variant);                      // the file was lost (cleaned storage): redraw, the token stays
                if ($again === null || $again['token'] !== $token || !is_file($file)) {
                    return null;
                }
            }
            $real = realpath($file);
            $base = realpath(self::baseDir());
            if ($real === false || $base === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
                return null;
            }
            $mtime = (int) filemtime($real);
            $size = (int) filesize($real);
            return ['file' => $real, 'mime' => 'image/jpeg', 'mtime' => $mtime, 'etag' => '"' . substr(sha1((string) $rec['src_hash'] . '|' . $mtime . '|' . $size), 0, 32) . '"'];
        } catch (\Throwable $e) {
            self::logFail('resolve', $e);
            return null;
        }
    }

    public static function adminUrl(int $newsletterId, string $variant = 'square'): string
    {
        return url('admin/social/preview/' . $newsletterId . '/' . (in_array($variant, self::VARIANTS, true) ? $variant : 'square'));
    }

    /**
     * Cover photo of the issue (or the first picture in its content), else the bundled logo: an absolute URL.
     * @param array<string,mixed> $issue a newsletters row
     */
    public static function fallbackUrl(array $issue): ?string
    {
        $cover = trim((string) ($issue['cover_image'] ?? ''));
        if ($cover === '' && isset($issue['content'])) {
            $cover = (string) (NewsletterContent::firstImage(NewsletterContent::decode((string) $issue['content']))['path'] ?? '');
        }
        if ($cover !== '') {
            $u = abs_url_for_image($cover);
            if ($u !== '') {
                return $u;
            }
        }
        $logo = abs_url_for_image((string) setting('logo_path', 'assets/img/brand/logo-512.png'));
        return $logo !== '' ? $logo : null;
    }

    /** @return int number of images stored for an issue (the share kit page shows it) */
    public static function count(int $newsletterId): int
    {
        try {
            return (int) Database::value('SELECT COUNT(*) FROM social_images WHERE newsletter_id = ?', [$newsletterId], 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    // ================================================================================== admin side (any status)

    /**
     * The picture for the Share kit page / panel thumbnail / Download button, for an issue of ANY status. Public issue: the stored file (redrawn when stale).
     * Not public: drawn on the fly, never stored, no token. Without GD: the cover photo file, else the logo file (as they are).
     * @param array<string,mixed> $row a newsletters row
     * @return ?array{bytes:string,mime:string,ext:string,drawn:bool}
     */
    public static function adminImage(array $row, string $variant): ?array
    {
        if (!in_array($variant, self::VARIANTS, true)) {
            return null;
        }
        if (self::available()) {
            $bytes = null;
            if (NewsletterService::isLive($row)) {
                $made = self::ensure((int) $row['id'], $variant);
                if ($made !== null) {
                    $bytes = @file_get_contents(self::baseDir() . '/' . $made['path']);
                }
            }
            if (!is_string($bytes) || $bytes === '') {
                $bytes = self::draw($row, $variant);
            }
            if (is_string($bytes) && $bytes !== '') {
                return ['bytes' => $bytes, 'mime' => 'image/jpeg', 'ext' => 'jpg', 'drawn' => true];
            }
        }
        $fb = self::fallbackFile($row);
        if ($fb === null) {
            return null;
        }
        $bytes = @file_get_contents($fb['file']);
        return is_string($bytes) && $bytes !== '' ? ['bytes' => $bytes, 'mime' => $fb['mime'], 'ext' => $fb['ext'], 'drawn' => false] : null;
    }

    /**
     * Local file of the cover photo, else of the logo (used without GD).
     * @param array<string,mixed> $row
     * @return ?array{file:string,mime:string,ext:string}
     */
    public static function fallbackFile(array $row): ?array
    {
        $cover = trim((string) ($row['cover_image'] ?? ''));
        if ($cover === '' && isset($row['content'])) {
            $cover = (string) (NewsletterContent::firstImage(NewsletterContent::decode((string) $row['content']))['path'] ?? '');
        }
        foreach ([$cover, (string) setting('logo_path', 'assets/img/brand/logo-512.png'), 'assets/img/brand/logo-512.png'] as $path) {
            $file = self::localImage($path);
            if ($file === null) {
                continue;
            }
            $info = @getimagesize($file);
            $mime = is_array($info) ? (string) ($info['mime'] ?? '') : '';
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? '';
            if ($ext !== '') {
                return ['file' => $file, 'mime' => $mime, 'ext' => $ext];
            }
        }
        return null;
    }

    // ================================================================================== drawing

    /**
     * Draw one picture. Never throws.
     * @param array<string,mixed> $row a newsletters row
     * @return ?string JPEG bytes
     */
    public static function draw(array $row, string $variant): ?string
    {
        if (!self::available() || !in_array($variant, self::VARIANTS, true)) {
            return null;
        }
        $img = null;
        try {
            [$W, $H] = SocialImage::SIZES[$variant];
            $d = self::designer();
            $issue = ShareHook::issueForShare($row);
            $img = imagecreatetruecolor($W, $H);
            if ($img === false) {
                return null;
            }
            imagealphablending($img, true);
            imagesavealpha($img, false);
            $c = static fn(string $hex, int $alpha = 0) => self::color($img, $hex, $alpha);

            // 1. background: the cover photo cropped to fill, else the brand background with soft shapes
            $cover = self::loadCover($row);
            $hasCover = false;
            if ($cover !== null) {
                $hasCover = self::coverFill($img, $cover, $W, $H);
                imagedestroy($cover);
            }
            if (!$hasCover) {
                self::brandBackground($img, $W, $H, $d);
            }
            // 2. dark gradient (always: it makes the text readable on any photo)
            self::gradient($img, $W, $H, $d['bg_dark'], $hasCover);

            // 3. geometry
            $m = $variant === 'landscape' ? 52 : 76;
            $logoD = $variant === 'landscape' ? 96 : ($variant === 'portrait' ? 150 : 136);
            $white = $c($d['text']);
            $soft = $c($d['text'], 24);
            $gold = $c($d['accent']);
            $fSans = self::fontPath(self::FONT_SANS);
            $fBold = self::fontPath(self::FONT_SANS_BOLD);
            $fSerif = self::fontPath(self::FONT_SERIF_BOLD);

            // 4. top row: logo (left), issue number + date (right)
            $topY = $m;
            if ($d['show_logo']) {
                self::drawLogo($img, $m, $topY, $logoD, $d);
            }
            $rx = $W - $m;
            $line1 = $issue['issue_number'] !== null ? self::clean(str_replace('{number}', (string) $issue['issue_number'], self::issueWord())) : '';
            $dateText = self::clean(CaptionBuilder::formatDate($issue['issue_date']));
            $s1 = $variant === 'landscape' ? 22 : 28;
            $s2 = $variant === 'landscape' ? 22 : 28;
            $ty = $topY + ($variant === 'landscape' ? 28 : 38);
            // the issue line and the date sit on the photo: make sure they can be read whatever the photo is (a light cover would swallow the gold)
            $w1 = $line1 !== '' ? self::trackedWidth($s1, $fBold, $line1, 3) : 0;
            $w2 = $dateText !== '' ? self::textWidth($s2, $fSans, $dateText) : 0;
            if ($w1 + $w2 > 0) {
                $yTop = $line1 !== '' ? $ty - $s1 - 6 : $ty - $s2 - 6;
                $yEnd = $ty + ($line1 !== '' && $dateText !== '' ? (int) round($s2 * 1.7) : 0) + 14;
                self::ensureContrast($img, $rx - max($w1, $w2) - 24, $rx + 24, $yTop, $yEnd, [$d['accent'], $d['text']], 4.6, $d['bg_dark']);
            }
            if ($line1 !== '') {
                self::tracked($img, $s1, $gold, $fBold, $rx - $w1, $ty, $line1, 3);
                $ty += (int) round($s2 * 1.7);
            }
            if ($dateText !== '') {
                imagettftext($img, $s2, 0, $rx - $w2, $ty, $white, $fSans, $dateText);
            }
            $topEnd = $topY + ($d['show_logo'] ? $logoD : 90);

            // 5. footer: gold rule, site address (left), tagline (right)
            $footSize = $variant === 'landscape' ? 24 : 30;
            $ruleY = $H - $m - $footSize - 34;
            self::ensureContrast($img, $m - 12, $W - $m + 12, $ruleY - 6, $H - $m + 14, [$d['accent'], $d['text']], 4.6, $d['bg_dark']);
            imagefilledrectangle($img, $m, $ruleY, $W - $m, $ruleY + 2, $c($d['accent'], 60));
            $baseY = $H - $m;
            $addr = $d['show_url'] ? self::siteAddress() : '';
            $tag = self::clean($d['tagline']);
            $addrW = 0;
            if ($addr !== '') {
                $addrW = self::textWidth($footSize, $fBold, $addr);
                imagettftext($img, $footSize, 0, $m, $baseY, $gold, $fBold, $addr);
            }
            if ($tag !== '') {
                $ts = $footSize - ($variant === 'landscape' ? 2 : 4);
                $room = $W - 2 * $m - $addrW - 40;
                while ($ts > 16 && self::textWidth($ts, $fSans, $tag) > $room) {
                    $ts -= 1;
                }
                if (self::textWidth($ts, $fSans, $tag) <= $room && $room > 120) {
                    imagettftext($img, $ts, 0, $W - $m - self::textWidth($ts, $fSans, $tag), $baseY, $soft, $fSans, $tag);
                } elseif ($addr === '' && $room > 120) {
                    $tag = self::ellipsize($ts, $fSans, $tag, $room);
                    imagettftext($img, $ts, 0, $W - $m - self::textWidth($ts, $fSans, $tag), $baseY, $soft, $fSans, $tag);
                }
            }

            // 6. text block, anchored above the footer: eyebrow, title, summary
            $textW = $W - 2 * $m;
            $gap = $variant === 'landscape' ? 22 : 34;
            $bottom = $ruleY - $gap;
            $word = '';
            try {
                $word = trim((string) content('newsletter', 'pdf.card_fallback', 'Newsletter'));
            } catch (\Throwable) {
            }
            $eyebrow = strtoupper(self::clean($word !== '' ? $word : 'Newsletter'));
            if (($issue['kind'] ?? '') === 'pdf') {
                $pdfWord = strtoupper(self::clean(self::pdfWord()));
                $eyebrow .= $pdfWord !== '' ? '  -  ' . $pdfWord : '';
            }
            $eyeSize = $variant === 'landscape' ? 22 : 28;
            $eyeH = $eyeSize + 18;                                                  // bar + label row
            // summary
            $sumSize = $variant === 'landscape' ? 26 : ($variant === 'portrait' ? 38 : 34);
            $sumMax = $variant === 'landscape' ? 2 : ($variant === 'portrait' ? 5 : 3);
            $summary = self::clean(mb_substr((string) $issue['summary'], 0, 420));
            $sumLines = $summary === '' ? [] : self::wrap($sumSize, $fSans, $summary, $textW, $sumMax);
            $sumLh = (int) round($sumSize * 1.38);
            $sumH = $sumLines === [] ? 0 : count($sumLines) * $sumLh;
            // title: the biggest size that fits what is left
            $title = self::clean((string) $issue['title']);
            if ($title === '') {
                $title = $word !== '' ? self::clean($word) : 'Newsletter';
            }
            $titleGap = $sumH > 0 ? ($variant === 'landscape' ? 16 : 26) : 0;
            $availTitle = $bottom - $sumH - $titleGap - $eyeH - ($variant === 'landscape' ? 16 : 26) - ($topEnd + $gap);
            [$tMax, $tMin, $tLines] = $variant === 'landscape' ? [64, 32, 3] : ($variant === 'portrait' ? [112, 54, 8] : [96, 46, 5]);
            $fit = self::fitTitle($fSerif, $title, $textW, max(120, $availTitle), $tMax, $tMin, $tLines);
            $titleH = count($fit['lines']) * $fit['lh'];

            $y = $bottom - $sumH;                                                    // top of the summary block
            $titleTop = $y - $titleGap - $titleH;
            $eyeTop = $titleTop - ($variant === 'landscape' ? 16 : 26) - $eyeH;
            // each text group gets its own veil where the photo is too light for it (gold label 4.6:1, the large title 3.6:1, the summary 4.9:1)
            if ($hasCover) {
                self::ensureContrast($img, $m - 14, $W - $m + 14, $eyeTop - 4, $eyeTop + $eyeH + 6, [$d['accent']], 4.6, $d['bg_dark']);
                self::ensureContrast($img, $m - 14, $W - $m + 14, $titleTop - 6, $titleTop + $titleH + 10, [$d['text']], 3.6, $d['bg_dark']);
                if ($sumH > 0) {
                    self::ensureContrast($img, $m - 14, $W - $m + 14, $y - 4, $y + $sumH + 8, [$d['text']], 4.9, $d['bg_dark']);
                }
            }
            foreach ($sumLines as $i => $ln) {
                imagettftext($img, $sumSize, 0, $m, $y + (int) round($sumSize * 1.0) + $i * $sumLh, $soft, $fSans, $ln);
            }
            $shadow = $hasCover ? imagecolorallocatealpha($img, 0, 0, 0, 72) : null;
            foreach ($fit['lines'] as $i => $ln) {
                $by = $titleTop + (int) round($fit['size'] * 0.98) + $i * $fit['lh'];
                if ($shadow !== null) {
                    imagettftext($img, $fit['size'], 0, $m + 2, $by + 3, $shadow, $fSerif, $ln);
                }
                imagettftext($img, $fit['size'], 0, $m, $by, $white, $fSerif, $ln);
            }
            imagefilledrectangle($img, $m, $eyeTop, $m + 72, $eyeTop + 6, $c($d['accent2']));
            self::tracked($img, $eyeSize, $gold, $fBold, $m, $eyeTop + 18 + $eyeSize - 4, $eyebrow, 4);

            ob_start();
            $ok = imagejpeg($img, null, self::QUALITY);
            $bytes = (string) ob_get_clean();
            imagedestroy($img);
            $img = null;
            return $ok && strlen($bytes) > 1000 ? $bytes : null;
        } catch (\Throwable $e) {
            if (is_object($img) || is_resource($img)) {
                @imagedestroy($img);
            }
            self::logFail('draw', $e);
            return null;
        }
    }

    /** @return array{bg:string,bg_dark:string,accent:string,accent2:string,text:string,tagline:string,show_logo:bool,show_url:bool} */
    public static function designer(): array
    {
        $col = static function (string $key, string $fallback): string {
            $v = trim(SocialConfig::str($key));
            return preg_match('/^#[0-9A-Fa-f]{6}$/', $v) === 1 ? strtoupper($v) : $fallback;
        };
        $tag = trim(SocialConfig::str('social_card_tagline'));
        if ($tag === '') {
            $tag = trim((string) setting('tagline', ''));
        }
        return [
            'bg'        => $col('social_card_bg', '#2C3950'),
            'bg_dark'   => $col('social_card_bg_dark', '#172036'),
            'accent'    => $col('social_card_accent', '#D6A151'),
            'accent2'   => $col('social_card_accent2', '#CC651A'),
            'text'      => $col('social_card_text', '#FFFFFF'),
            'tagline'   => mb_substr($tag, 0, 80),
            'show_logo' => SocialConfig::bool('social_card_show_logo'),
            'show_url'  => SocialConfig::bool('social_card_show_url'),
        ];
    }

    /** The issue number line of the pictures ("ISSUE No. {number}"): the page registry (Admin > Pages > Newsletter > Social media pictures), default = the original wording. */
    public static function issueWord(): string
    {
        $v = '';
        try {
            $v = trim((string) content('newsletter', 'sharecard.issue', 'ISSUE No. {number}'));
        } catch (\Throwable) {
        }
        return $v !== '' && str_contains($v, '{number}') ? $v : 'ISSUE No. {number}';
    }

    /** The word after "Newsletter" on the picture of a PDF issue (registry; may be emptied to show none). */
    public static function pdfWord(): string
    {
        try {
            return trim((string) content('newsletter', 'sharecard.pdf', 'PDF'));
        } catch (\Throwable) {
            return 'PDF';
        }
    }

    /** Hash of everything a picture is drawn from: a change re-renders it, nothing else does. */
    public static function sourceHash(array $row, string $variant): string
    {
        $issue = ShareHook::issueForShare($row);
        $files = [];
        foreach ([self::coverPath($row), (string) setting('logo_path', 'assets/img/brand/logo-512.png')] as $p) {
            $f = self::localImage($p);
            $files[] = $f === null ? '-' : $p . ':' . (int) @filemtime($f) . ':' . (int) @filesize($f);
        }
        return hash('sha256', json_encode([
            self::RENDER_VERSION, $variant, $issue['title'], $issue['summary'], $issue['issue_number'], $issue['issue_date'], $issue['kind'], $files,
            self::designer(), self::siteAddress(), (string) setting('tagline', ''),
            function_exists('content') ? (string) content('newsletter', 'pdf.card_fallback', '') : '',
            self::issueWord(), self::pdfWord(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    // ================================================================================== drawing helpers

    private static function coverPath(array $row): string
    {
        $cover = trim((string) ($row['cover_image'] ?? ''));
        if ($cover === '' && isset($row['content'])) {
            $cover = (string) (NewsletterContent::firstImage(NewsletterContent::decode((string) $row['content']))['path'] ?? '');
        }
        return $cover;
    }

    /** @return ?\GdImage */
    private static function loadCover(array $row)
    {
        $file = self::localImage(self::coverPath($row));
        if ($file === null) {
            return null;
        }
        $info = @getimagesize($file);
        if (!is_array($info) || $info[0] < 16 || $info[1] < 16) {
            return null;
        }
        // memory guard: truecolor GD needs about 5 bytes per pixel while resampling
        $need = (int) ($info[0] * $info[1] * 5.2);
        $limit = self::memoryLimit();
        if ($limit > 0 && $need > ($limit - memory_get_usage(true)) * 0.6) {
            return null;
        }
        if ($info[0] * $info[1] > 40_000_000) {
            return null;
        }
        $bytes = @file_get_contents($file);
        if (!is_string($bytes) || $bytes === '') {
            return null;
        }
        $im = @imagecreatefromstring($bytes);
        return $im === false ? null : $im;
    }

    private static function memoryLimit(): int
    {
        $v = trim((string) ini_get('memory_limit'));
        if ($v === '' || $v === '-1') {
            return 0;
        }
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) {
            'g' => $n * 1073741824,
            'm' => $n * 1048576,
            'k' => $n * 1024,
            default => $n,
        };
    }

    /** Crop-to-fill (focus a little above the centre: faces and headlines sit high). */
    private static function coverFill($img, $src, int $W, int $H): bool
    {
        $sw = imagesx($src);
        $sh = imagesy($src);
        if ($sw < 1 || $sh < 1) {
            return false;
        }
        $scale = max($W / $sw, $H / $sh);
        $cw = (int) min($sw, max(1, round($W / $scale)));
        $ch = (int) min($sh, max(1, round($H / $scale)));
        $cx = (int) max(0, min($sw - $cw, round(($sw - $cw) * 0.5)));
        $cy = (int) max(0, min($sh - $ch, round(($sh - $ch) * 0.38)));
        // flatten transparency over the brand colour (a PNG cover with alpha)
        $flat = imagecreatetruecolor($sw, $sh);
        if ($flat === false) {
            return false;
        }
        imagefill($flat, 0, 0, imagecolorallocate($flat, 23, 32, 54));
        imagealphablending($flat, true);
        imagecopy($flat, $src, 0, 0, 0, 0, $sw, $sh);
        $ok = imagecopyresampled($img, $flat, 0, 0, $cx, $cy, $W, $H, $cw, $ch);
        imagedestroy($flat);
        return $ok;
    }

    private static function brandBackground($img, int $W, int $H, array $d): void
    {
        [$r1, $g1, $b1] = self::rgb($d['bg']);
        [$r2, $g2, $b2] = self::rgb($d['bg_dark']);
        for ($y = 0; $y < $H; $y++) {
            $t = $y / max(1, $H - 1);
            $col = imagecolorallocate($img, (int) round($r1 + ($r2 - $r1) * $t), (int) round($g1 + ($g2 - $g1) * $t), (int) round($b1 + ($b2 - $b1) * $t));
            imageline($img, 0, $y, $W - 1, $y, $col);
        }
        // soft shapes on a double-size layer, scaled down once (smooth edges), when memory allows
        $limit = self::memoryLimit();
        $ss = ($limit === 0 || $limit - memory_get_usage(true) > $W * $H * 4 * 4 * 3) ? 2 : 1;
        $lw = $W * $ss;
        $lh = $H * $ss;
        $layer = imagecreatetruecolor($lw, $lh);
        if ($layer === false) {
            return;
        }
        imagealphablending($layer, false);
        imagesavealpha($layer, true);
        imagefill($layer, 0, 0, imagecolorallocatealpha($layer, 0, 0, 0, 127));
        imagealphablending($layer, true);
        $u = min($W, $H) * $ss;
        $gold = self::color($layer, '#FFFFFF', 119);
        $goldRing = self::color($layer, $d['accent'], 84);
        $orange = self::color($layer, $d['accent2'], 110);
        // big gold disc top right, orange disc bottom left, thin rings around them
        imagefilledellipse($layer, (int) ($lw * 0.92), (int) ($lh * 0.12), (int) ($u * 0.9), (int) ($u * 0.9), $gold);
        imagefilledellipse($layer, (int) ($lw * 0.04), (int) ($lh * 0.9), (int) ($u * 0.8), (int) ($u * 0.8), $orange);
        imagesetthickness($layer, max(2, 3 * $ss));
        foreach ([1.15, 1.4, 1.65] as $k) {
            imagearc($layer, (int) ($lw * 0.92), (int) ($lh * 0.12), (int) ($u * 0.9 * $k), (int) ($u * 0.9 * $k), 0, 360, $goldRing);
        }
        imagecopyresampled($img, $layer, 0, 0, 0, 0, $W, $H, $lw, $lh);
        imagedestroy($layer);
    }

    /** Dark gradient: a light veil at the top (logo), clear in the middle, strong at the bottom where the text sits. */
    private static function gradient($img, int $W, int $H, string $hex, bool $photo): void
    {
        [$r, $g, $b] = self::rgb($hex);
        imagealphablending($img, true);
        for ($y = 0; $y < $H; $y++) {
            $t = $y / max(1, $H - 1);
            // opacity 0..1
            $top = max(0.0, 1.0 - $t / 0.22) * ($photo ? 0.55 : 0.0);
            $mid = $photo ? 0.18 : 0.0;
            $bt = max(0.0, ($t - 0.28) / 0.72);
            $bot = ($photo ? 0.93 : 0.55) * ($bt * $bt * (3 - 2 * $bt));
            $op = min(0.96, max($top, $mid + $bot * (1 - $mid)));
            $alpha = (int) round(127 - 127 * $op);
            imageline($img, 0, $y, $W - 1, $y, imagecolorallocatealpha($img, $r, $g, $b, max(0, min(127, $alpha))));
        }
    }

    /** WCAG relative luminance of an sRGB colour. */
    private static function lum(int $r, int $g, int $b): float
    {
        $f = static function (int $v): float {
            $c = $v / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        return 0.2126 * $f($r) + 0.7152 * $f($g) + 0.0722 * $f($b);
    }

    /**
     * Make sure LIGHT text drawn in the box x0..x1 / y0..y1 reaches the contrast ratio $ratio on ANY photo. The pixels behind the text are sampled (what the gradient left
     * of the photo); when the 95th-percentile background is too light for the weakest text colour, a veil of the dark brand colour is laid over the band (full width, soft
     * edges, so it never looks like a box) with the smallest opacity that gets there. A dark photo or the brand background gets no veil at all.
     * @param list<string> $textHexes
     */
    private static function ensureContrast($img, int $x0, int $x1, int $y0, int $y1, array $textHexes, float $ratio, string $veilHex): void
    {
        $W = imagesx($img);
        $H = imagesy($img);
        $x0 = max(0, $x0);
        $x1 = min($W - 1, $x1);
        $y0 = max(0, $y0);
        $y1 = min($H - 1, $y1);
        if ($x1 <= $x0 || $y1 <= $y0 || $textHexes === []) {
            return;
        }
        $lt = 1.0;
        foreach ($textHexes as $hx) {
            [$r, $g, $b] = self::rgb($hx);
            $lt = min($lt, self::lum($r, $g, $b));
        }
        $need = max(0.0, ($lt + 0.05) / $ratio - 0.05);                         // the brightest background luminance the text can stand
        $samples = [];
        $sx = max(6, intdiv($x1 - $x0, 48));
        $sy = max(4, intdiv($y1 - $y0, 16));
        for ($y = $y0; $y <= $y1; $y += $sy) {
            for ($x = $x0; $x <= $x1; $x += $sx) {
                $p = imagecolorat($img, $x, $y);
                $samples[] = [($p >> 16) & 255, ($p >> 8) & 255, $p & 255];
            }
        }
        if ($samples === []) {
            return;
        }
        [$vr, $vg, $vb] = self::rgb($veilHex);
        $worst = static function (float $a) use ($samples, $vr, $vg, $vb): float {
            $l = [];
            foreach ($samples as [$r, $g, $b]) {
                $l[] = self::lum((int) round($r * (1 - $a) + $vr * $a), (int) round($g * (1 - $a) + $vg * $a), (int) round($b * (1 - $a) + $vb * $a));
            }
            sort($l);
            return $l[(int) min(count($l) - 1, floor(count($l) * 0.95))];
        };
        $alpha = 0.0;
        while ($alpha < 0.94 && $worst($alpha) > $need) {
            $alpha += 0.03;
        }
        $alpha = min(0.94, $alpha);
        if ($alpha < 0.03) {
            return;
        }
        $feather = 46;
        imagealphablending($img, true);
        for ($y = $y0 - $feather; $y <= $y1 + $feather; $y++) {
            if ($y < 0 || $y >= $H) {
                continue;
            }
            $k = $y < $y0 ? 1 - ($y0 - $y) / $feather : ($y > $y1 ? 1 - ($y - $y1) / $feather : 1.0);
            $a = $alpha * ($k * $k * (3 - 2 * $k));
            imageline($img, 0, $y, $W - 1, $y, imagecolorallocatealpha($img, $vr, $vg, $vb, max(0, min(127, (int) round(127 - 127 * $a)))));
        }
    }

    private static function drawLogo($img, int $x, int $y, int $d, array $dsn): void
    {
        $path = (string) setting('logo_path', 'assets/img/brand/logo-512.png');
        $file = self::localImage($path) ?? self::localImage('assets/img/brand/logo-512.png');
        if ($file === null) {
            return;
        }
        $bytes = @file_get_contents($file);
        $logo = is_string($bytes) ? @imagecreatefromstring($bytes) : false;
        if ($logo === false) {
            return;
        }
        // soft white disc behind the badge keeps it readable on any photo
        $pad = (int) round($d * 0.05);
        imagefilledellipse($img, $x + intdiv($d, 2), $y + intdiv($d, 2), $d + 2 * $pad, $d + 2 * $pad, self::color($img, '#FFFFFF', 96));
        imagealphablending($img, true);
        imagecopyresampled($img, $logo, $x, $y, 0, 0, $d, $d, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }

    /**
     * Drop what the bundled fonts cannot show (emoji, CJK, symbols) and what must never be drawn (controls, bidi tricks), fold white space.
     */
    public static function clean(string $text): string
    {
        $text = CaptionBuilder::sanitize($text, false);
        $out = '';
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $cp = mb_ord($ch, 'UTF-8');
            if ($cp === false) {
                continue;
            }
            $ok = ($cp >= 0x20 && $cp <= 0x7E) || ($cp >= 0xA0 && $cp <= 0x24F) || ($cp >= 0x370 && $cp <= 0x52F) || ($cp >= 0x1E00 && $cp <= 0x1EFF)
                || ($cp >= 0x2010 && $cp <= 0x2027) || ($cp >= 0x2030 && $cp <= 0x205E) || ($cp >= 0x20A0 && $cp <= 0x20BF) || $cp === 0x2122 || $cp === 0x2116;
            if ($ok && $cp !== 0xAD) {
                $out .= $ch;
            } elseif ($cp === 0x9 || $cp === 0xA || $cp === 0xD) {
                $out .= ' ';
            }
        }
        return trim((string) preg_replace('/\s+/u', ' ', $out));
    }

    /** @return array{size:int,lh:int,lines:list<string>} */
    private static function fitTitle(string $font, string $title, int $width, int $maxH, int $maxSize, int $minSize, int $maxLines): array
    {
        for ($size = $maxSize; $size >= $minSize; $size -= 2) {
            $lines = self::wrap($size, $font, $title, $width, $maxLines + 1);
            $lh = (int) round($size * 1.2);
            if (count($lines) <= $maxLines && count($lines) * $lh <= $maxH) {
                return ['size' => $size, 'lh' => $lh, 'lines' => $lines];
            }
        }
        // does not fit even at the smallest size: cut it with an ellipsis
        $size = $minSize;
        $lh = (int) round($size * 1.2);
        $fits = max(1, min($maxLines, intdiv($maxH, $lh)));
        $lines = self::wrap($size, $font, $title, $width, $fits, true);
        return ['size' => $size, 'lh' => $lh, 'lines' => $lines];
    }

    /**
     * Greedy word wrap by measured width; a word wider than the line is split by characters. With $max lines the last one ends in an ellipsis when text is left over
     * ($force or more lines than $max).
     * @return list<string>
     */
    private static function wrap(int $size, string $font, string $text, int $width, int $max, bool $force = false): array
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $cur = '';
        foreach ($words as $w) {
            $try = $cur === '' ? $w : $cur . ' ' . $w;
            if (self::textWidth($size, $font, $try) <= $width) {
                $cur = $try;
                continue;
            }
            if ($cur !== '') {
                $lines[] = $cur;
                $cur = '';
            }
            while (self::textWidth($size, $font, $w) > $width) {
                $chars = preg_split('//u', $w, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $piece = '';
                $n = 0;
                foreach ($chars as $ch) {
                    if (self::textWidth($size, $font, $piece . $ch) > $width && $piece !== '') {
                        break;
                    }
                    $piece .= $ch;
                    $n++;
                }
                $lines[] = $piece;
                $w = implode('', array_slice($chars, $n));
                if ($w === '') {
                    break;
                }
            }
            $cur = $w;
        }
        if ($cur !== '') {
            $lines[] = $cur;
        }
        if (count($lines) > $max) {
            $lines = array_slice($lines, 0, $max);
            $force = true;
        }
        if ($force && $lines !== [] && count($words) > 0) {
            $joined = implode(' ', $lines);
            if (mb_strlen($joined) < mb_strlen(implode(' ', $words))) {
                $lines[count($lines) - 1] = self::ellipsize($size, $font, rtrim($lines[count($lines) - 1], " ,;:.-") . '...', $width, true);
            }
        }
        return $lines;
    }

    private static function ellipsize(int $size, string $font, string $text, int $width, bool $hasDots = false): string
    {
        $base = $hasDots ? preg_replace('/\.\.\.$/', '', $text) : $text;
        $base = (string) $base;
        $cut = false;
        while ($base !== '' && self::textWidth($size, $font, $base . '...') > $width) {
            $base = rtrim(mb_substr($base, 0, mb_strlen($base) - 1));
            $cut = true;
        }
        if ($cut && ($sp = mb_strrpos($base, ' ')) !== false && $sp > 0) {
            $base = rtrim(mb_substr($base, 0, $sp), " ,;:.-");               // end on a whole word when there is one
        }
        return $base . '...';
    }

    private static function textWidth(int $size, string $font, string $text): int
    {
        $b = @imagettfbbox($size, 0, $font, $text);
        return is_array($b) ? (int) abs($b[2] - $b[0]) : 0;
    }

    private static function trackedWidth(int $size, string $font, string $text, int $track): int
    {
        $w = 0;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $w += self::textWidth($size, $font, $ch === ' ' ? 'i' : $ch) + $track;
            if ($ch === ' ') {
                $w += (int) round($size * 0.2);
            }
        }
        return max(0, $w - $track);
    }

    private static function tracked($img, int $size, int $color, string $font, int $x, int $y, string $text, int $track): void
    {
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            if ($ch === ' ') {
                $x += (int) round($size * 0.2) + self::textWidth($size, $font, 'i') + $track;
                continue;
            }
            imagettftext($img, $size, 0, $x, $y, $color, $font, $ch);
            $x += self::textWidth($size, $font, $ch) + $track;
        }
    }

    private static function siteAddress(): string
    {
        $u = rtrim(url('', [], true), '/');
        $u = (string) preg_replace('~^https?://~i', '', $u);
        return self::clean($u);
    }

    /** @return array{0:int,1:int,2:int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [(int) hexdec(substr($hex, 0, 2)), (int) hexdec(substr($hex, 2, 2)), (int) hexdec(substr($hex, 4, 2))];
    }

    private static function color($img, string $hex, int $alpha = 0): int
    {
        [$r, $g, $b] = self::rgb($hex);
        return (int) imagecolorallocatealpha($img, $r, $g, $b, max(0, min(127, $alpha)));
    }

    // ================================================================================== files

    private static function fontPath(string $file): string
    {
        return (defined('ASSET_PATH') ? ASSET_PATH : dirname(__DIR__, 3) . '/assets') . '/fonts/card/' . $file;
    }

    public static function baseDir(): string
    {
        return (defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 3) . '/storage') . '/private/social';
    }

    private static function tokenUrl(string $token): string
    {
        return url('social/image/' . $token . '.jpg', [], true);
    }

    /**
     * The local file of a stored image path (an upload or a bundled asset), or null: remote URLs, traversal, anything outside uploads/ and assets/.
     */
    private static function localImage(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || preg_match('~^[a-z][a-z0-9+.-]*:|^[/\\\\]|\.\.|[\x00-\x1f]~i', $path) === 1) {
            return null;
        }
        if (str_starts_with($path, 'assets/')) {
            $root = defined('ASSET_PATH') ? ASSET_PATH : dirname(__DIR__, 3) . '/assets';
            $abs = $root . '/' . substr($path, 7);
        } else {
            $root = defined('UPLOAD_PATH') ? UPLOAD_PATH : dirname(__DIR__, 3) . '/uploads';
            $abs = $root . '/' . $path;
        }
        $real = realpath($abs);
        $rootReal = realpath($root);
        if ($real === false || $rootReal === false || !is_file($real) || !str_starts_with($real, $rootReal . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $real;
    }

    /** Write atomically (temp file, then rename): a platform fetching the picture never sees half a file. */
    private static function writeFile(string $abs, string $bytes): bool
    {
        $dir = dirname($abs);
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            return false;
        }
        $base = self::baseDir();
        if (!is_file($base . '/.htaccess')) {
            @file_put_contents($base . '/.htaccess', "# private: share pictures are served only through /social/image/{token}.jpg\nRequire all denied\n");
            @file_put_contents($base . '/index.html', '');
        }
        $tmp = $dir . '/.tmp-' . bin2hex(random_bytes(6));
        if (@file_put_contents($tmp, $bytes, LOCK_EX) === false) {
            @unlink($tmp);
            return false;
        }
        @chmod($tmp, 0640);
        if (!@rename($tmp, $abs)) {
            @unlink($abs);
            if (!@rename($tmp, $abs)) {
                @unlink($tmp);
                return false;
            }
        }
        return true;
    }

    private static function logFail(string $where, \Throwable $e): void
    {
        try {
            \App\Core\Log::warning('social card ' . $where . ' failed: ' . get_class($e));
        } catch (\Throwable) {
        }
    }
}
