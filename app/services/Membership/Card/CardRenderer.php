<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * The one door to the card engine: settings + member -> data -> layout -> PDF / PNG bytes. Everything else (CardService, the e-mail, the dashboard,
 * the designer preview) calls this class, so the card is built in exactly one way.
 *
 *   CardRenderer::memberPdf($memberRow, ['verify_token' => $t, 'valid_until' => '2027-10-19', 'issued' => '2026-10-20'])   string (PDF bytes)
 *   CardRenderer::memberPng($memberRow, $ctx)                                                                           ?string (PNG bytes; null when GD is missing)
 *   CardRenderer::samplePdf($unsavedSettings = [])                  the fictional, watermarked sample card (designer preview, test e-mail)
 *   CardRenderer::samplePng($unsavedSettings = [])
 *
 * $override = designer form values that are NOT saved yet; they are sanitised by CardSettings::resolve(). Nothing here writes to the database.
 * Pictures: the logo is the one chosen in the designer (a file below uploads/) or the bundled assets/img/brand/card-logo.jpg; the photo is the
 * member's private headshot (MemberFiles::photoForCard). A picture the PDF writer cannot embed (WebP, GIF, interlaced PNG) is converted with GD
 * when GD exists, otherwise left out (the card then shows the initials block, or no logo) rather than failing.
 */
final class CardRenderer
{
    public const BUNDLED_LOGO = 'img/brand/card-logo.jpg';

    // ------------------------------------------------------------------------------------------------ high level

    /** @param array<string,mixed> $member @param array<string,mixed> $ctx see CardData::raw() @param array<string,mixed> $override */
    public static function memberPdf(array $member, array $ctx = [], array $override = []): string
    {
        $s = CardSettings::resolve($override);
        return self::pdf(CardData::build(CardData::raw($member, $ctx), $s), $s);
    }

    /** @param array<string,mixed> $member @param array<string,mixed> $ctx @param array<string,mixed> $override */
    public static function memberPng(array $member, array $ctx = [], array $override = [], ?float $scale = null): ?string
    {
        $s = CardSettings::resolve($override);
        return self::png(CardData::build(CardData::raw($member, $ctx), $s), $s, $scale);
    }

    /**
     * The card of an ISSUED member: the snapshot taken at issue (name, type, dates, fee, photo copy) with the live status and the card's verification link.
     * Members without a snapshot fall back to their current data. @param array<string,mixed> $member @param array<string,mixed> $override
     */
    public static function issuedPdf(array $member, array $override = []): string
    {
        $s = CardSettings::resolve($override);
        $raw = CardData::rawFromSnapshot($member) ?? CardData::raw($member, ['verify_token' => $member['card_verify_token'] ?? '', 'valid_until' => $member['card_valid_until'] ?? null]);
        return self::pdf(CardData::build($raw, $s), $s);
    }

    /** @param array<string,mixed> $member @param array<string,mixed> $override */
    public static function issuedPng(array $member, array $override = [], ?float $scale = null): ?string
    {
        $s = CardSettings::resolve($override);
        $raw = CardData::rawFromSnapshot($member) ?? CardData::raw($member, ['verify_token' => $member['card_verify_token'] ?? '', 'valid_until' => $member['card_valid_until'] ?? null]);
        return self::png(CardData::build($raw, $s), $s, $scale);
    }

    /** @param array<string,mixed> $override */
    public static function samplePdf(array $override = []): string
    {
        $s = CardSettings::resolve($override);
        return self::pdf(CardData::sample($s), $s);
    }

    /** @param array<string,mixed> $override */
    public static function samplePng(array $override = [], ?float $scale = null): ?string
    {
        $s = CardSettings::resolve($override);
        return self::png(CardData::sample($s), $s, $scale);
    }

    // ------------------------------------------------------------------------------------------------ data -> bytes

    /**
     * @param array<string,mixed> $data CardData::build() output @param array<string,string> $settings resolved settings (for the logo)
     * @return string PDF bytes
     */
    public static function pdf(array $data, array $settings): string
    {
        [$layout, $images] = self::prepare($data, $settings);
        return CardPdf::render($layout, $images, [
            'title'   => (string) $data['title'],
            'author'  => (string) ($data['org'] !== '' ? $data['org'] : setting('site_name', 'Membership')),
            'subject' => 'Membership card',
        ]);
    }

    /** @param array<string,mixed> $data @param array<string,string> $settings */
    public static function png(array $data, array $settings, ?float $scale = null): ?string
    {
        if (!CardPng::available()) {
            return null;
        }
        [$layout, $images] = self::prepare($data, $settings);
        return CardPng::render($layout, $images, $scale);
    }

    /**
     * The layout and the usable pictures for a data array. A photo that cannot be used is dropped from the data BEFORE the layout, so the
     * layout draws the initials block instead of an empty photo box.
     * @param array<string,mixed> $data @param array<string,string> $settings
     * @return array{0:array<string,mixed>,1:array<string,array{bytes:string,mime:string}|null>}
     */
    public static function prepare(array $data, array $settings): array
    {
        $logo = self::logo($settings);
        $photo = is_array($data['photo'] ?? null) ? self::usable($data['photo']['bytes'] ?? '') : null;
        $data['photo'] = $photo;
        $layout = CardLayout::build($data, ['logo' => $logo !== null]);
        return [$layout, ['logo' => $logo, 'photo' => $photo]];
    }

    // ------------------------------------------------------------------------------------------------ pictures

    /** The card's logo: the designer's upload, else the bundled seal. @param array<string,string> $settings @return array{bytes:string,mime:string}|null */
    public static function logo(array $settings): ?array
    {
        $path = trim((string) ($settings['membership_card_logo'] ?? ''));
        if ($path !== '' && preg_match('~^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-]+\.(png|jpg|jpeg|webp|gif)$~D', $path) === 1 && !str_contains($path, '..')) {
            $file = UPLOAD_PATH . '/' . $path;
            if (is_file($file) && (int) filesize($file) < 8 * 1024 * 1024) {
                $img = self::usable((string) file_get_contents($file));
                if ($img !== null) {
                    return $img;
                }
            }
        }
        $bundled = ASSET_PATH . '/' . self::BUNDLED_LOGO;
        return is_file($bundled) ? self::usable((string) file_get_contents($bundled)) : null;
    }

    /**
     * Picture bytes the PDF writer can embed: used as they are, or converted to a white-backed JPEG with GD (WebP, GIF, interlaced / 16-bit PNG).
     * Returns null when the picture is unusable and cannot be converted.
     * @return array{bytes:string,mime:string}|null
     */
    public static function usable(string $bytes): ?array
    {
        if ($bytes === '') {
            return null;
        }
        $info = PdfWriter::imageInfo($bytes);
        if ($info !== null) {
            return ['bytes' => $bytes, 'mime' => $info['mime']];
        }
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return null;
        }
        $src = @imagecreatefromstring($bytes);
        if (!$src instanceof \GdImage) {
            return null;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $canvas = imagecreatetruecolor($w, $h);
        imagefill($canvas, 0, 0, (int) imagecolorallocate($canvas, 255, 255, 255));
        imagealphablending($canvas, true);
        imagecopy($canvas, $src, 0, 0, 0, 0, $w, $h);
        ob_start();
        $ok = imagejpeg($canvas, null, 90);
        $out = (string) ob_get_clean();
        return $ok && $out !== '' ? ['bytes' => $out, 'mime' => 'image/jpeg'] : null;
    }
}
