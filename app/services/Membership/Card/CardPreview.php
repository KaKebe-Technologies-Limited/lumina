<?php
declare(strict_types=1);

namespace App\Services\Membership\Card;

/**
 * The card DESIGNER's preview scenarios (Admin > Membership > Membership card). OWNER: CARD agent (a new file: the engine files stay untouched).
 *
 * The designer always draws the real engine (CardData::build -> CardLayout -> CardPdf / CardPng) with a FICTIONAL member (the watermarked sample), but
 * the owner can choose WHICH sample to look at, so every awkward case can be checked before a real member meets it: a photo, a very long name and
 * membership type, accents and apostrophes, an expired / suspended / revoked card, a lifetime membership (no expiry), a payment still to pay, a member
 * with no location. Nothing here reads the database or a real member: every value below is invented, and the QR code carries the token that can never
 * exist (CardData::SAMPLE_TOKEN), so the verification page says NOT VALID.
 *
 *   CardPreview::pdf('long', $unsavedSettings)        string  PDF bytes
 *   CardPreview::png('expired', $unsavedSettings)     ?string PNG bytes (null without GD)
 *   CardPreview::photo()                              ['bytes' => PNG, 'mime' => 'image/png']  an anonymous portrait placeholder made in pure PHP (no GD, no file)
 */
final class CardPreview
{
    /** key => label shown in the designer. The first is the default. */
    public const SCENARIOS = [
        'typical'   => 'A typical member (the reference sample)',
        'photo'     => 'A member with a photo',
        'long'      => 'A very long name, type and location',
        'names'     => 'A name with accents and apostrophes',
        'expired'   => 'An expired card',
        'suspended' => 'A suspended card',
        'revoked'   => 'A revoked card (not valid)',
        'lifetime'  => 'A lifetime membership (no expiry date)',
        'unpaid'    => 'A joining fee that is still to pay',
        'bare'      => 'A member with no location and no fee',
    ];

    public const DEFAULT = 'typical';

    /** A scenario key from user input: unknown values fall back to the default. */
    public static function scenario(mixed $key): string
    {
        return is_string($key) && isset(self::SCENARIOS[$key]) ? $key : self::DEFAULT;
    }

    /**
     * The neutral input of CardData::build() for a scenario (fictional values only).
     * @return array<string,mixed>
     */
    public static function raw(string $scenario): array
    {
        $r = CardData::sampleRaw();
        switch (self::scenario($scenario)) {
            case 'photo':
                $r['photo'] = self::photo();
                break;
            case 'long':
                $r['name'] = 'Nabukenya-Namukasa Josephine Mary Rose Bwanika Nakitende Ssemakula';
                $r['type_name'] = 'Organisation, Partner or Community-based Association Member';
                $r['number'] = 'LAF-ORG-26-000142';
                $r['city'] = 'Kampala Central Division';
                $r['district'] = 'Kampala Capital City Authority';
                $r['country'] = 'The United Republic of Tanzania and its islands';
                $r['fee_amount'] = '12500000.00';
                $r['joining_fee'] = '2500000.00';
                $r['photo'] = self::photo();
                break;
            case 'names':
                $r['name'] = "Zo\u{00EB} Ou\u{00E9}draogo-D'Souza";
                $r['type_name'] = 'Youth & Student Member';
                $r['number'] = 'LAF-YTH-26-0017';
                $r['city'] = "N'Djamena";
                $r['country'] = "C\u{00F4}te d'Ivoire";
                $r['fee_amount'] = '20000.00';
                $r['joining_fee'] = null;
                break;
            case 'expired':
                $r['status'] = 'expired';
                $r['since'] = '2024-10-20';
                $r['valid_until'] = '2025-10-19';
                $r['issued'] = '2024-10-20';
                break;
            case 'suspended':
                $r['status'] = 'suspended';
                break;
            case 'revoked':
                $r['status'] = 'revoked';
                break;
            case 'lifetime':
                $r['period'] = 'lifetime';
                $r['valid_until'] = null;
                $r['fee_amount'] = '500000.00';
                $r['joining_fee'] = null;
                $r['type_name'] = 'Lifetime Member';
                $r['number'] = 'LAF-LIF-26-0003';
                break;
            case 'unpaid':
                $r['pay_state'] = 'pending';
                break;
            case 'bare':
                $r['city'] = '';
                $r['district'] = '';
                $r['country'] = '';
                $r['joining_fee'] = null;
                $r['fee_amount'] = '0.00';
                break;
        }
        return $r;
    }

    /** @param array<string,mixed> $override unsaved designer values (sanitised by CardSettings::resolve()) */
    public static function pdf(string $scenario, array $override = []): string
    {
        $s = CardSettings::resolve($override);
        return CardRenderer::pdf(CardData::build(self::raw($scenario), $s), $s);
    }

    /** @param array<string,mixed> $override */
    public static function png(string $scenario, array $override = [], ?float $scale = null): ?string
    {
        $s = CardSettings::resolve($override);
        return CardRenderer::png(CardData::build(self::raw($scenario), $s), $s, $scale);
    }

    // ------------------------------------------------------------------------------------------------ the e-mail message

    /** The longest e-mail message (CardSettings::DEFS: membership_card_email_body is text:3000) and the most lines it may have. */
    public const MESSAGE_MAX = 3000;
    public const MESSAGE_LINES = 80;

    /**
     * Clean the card e-mail MESSAGE: invalid UTF-8 repaired, control / zero-width / bidi characters removed, spaces collapsed inside a line, line breaks kept (at most
     * 80 lines, no more than one blank line in a row), cut to 3000 characters. This exists because CardSettings::cleanText() keeps only the first SIX lines, which is right
     * for the two-line side-panel text but cuts the default 20-line e-mail message after "Member number: ..." (gap reported to the architect; until CardSettings allows more
     * lines for this field, the designer cleans the message with this method, saves it itself, and MembershipMail::renderCard() uses it for unsaved previews).
     */
    public static function cleanMessage(string $s, int $max = self::MESSAGE_MAX): string
    {
        if (!mb_check_encoding($s, 'UTF-8')) {
            $s = (string) mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = (string) preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{FEFF}]+/u', '', $s);
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $s);
        $lines = array_map(static fn(string $l): string => trim((string) preg_replace('/[^\S\n]+/u', ' ', $l)), explode("\n", $s));
        $s = trim(implode("\n", array_slice($lines, 0, self::MESSAGE_LINES)));
        $s = (string) preg_replace("/\n{3,}/", "\n\n", $s);
        return mb_substr($s, 0, max(0, $max));
    }

    // ------------------------------------------------------------------------------------------------ the placeholder portrait

    /**
     * An anonymous portrait (a head and shoulders silhouette on a soft background), 240 x 300 px, written as a plain 8-bit RGB PNG with no library, so the
     * designer can show how a REAL photo sits in the photo block even on a server without GD. Deterministic and cached for the request.
     * @return array{bytes:string,mime:string}
     */
    public static function photo(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $w = 240;
        $h = 300;
        $bgTop = [214, 224, 238];
        $bgBottom = [168, 184, 208];
        $fig = [86, 104, 138];
        $rows = '';
        for ($y = 0; $y < $h; $y++) {
            $t = $y / ($h - 1);
            $bg = [
                $bgTop[0] + ($bgBottom[0] - $bgTop[0]) * $t,
                $bgTop[1] + ($bgBottom[1] - $bgTop[1]) * $t,
                $bgTop[2] + ($bgBottom[2] - $bgTop[2]) * $t,
            ];
            $rows .= "\x00";                                           // PNG filter type 0 (none)
            for ($x = 0; $x < $w; $x++) {
                // head: circle centred (120, 104) radius 46; shoulders: ellipse centred (120, 318) radii 118 x 112 (the neck joins them)
                $dHead = sqrt(($x - 120) ** 2 + ($y - 104) ** 2) - 46.0;
                $dBody = sqrt((($x - 120) / 118.0) ** 2 + (($y - 318) / 112.0) ** 2) - 1.0;
                $dBody *= 112.0 * 0.9;
                $dNeck = max(abs($x - 120) - 17.0, ($y < 140 ? 140.0 - $y : ($y > 180 ? $y - 180.0 : -1.0)));
                $d = min($dHead, $dBody, $dNeck);
                $a = max(0.0, min(1.0, 0.5 - $d));                    // 1 px anti-aliasing
                $rows .= chr((int) round($bg[0] + ($fig[0] - $bg[0]) * $a)) . chr((int) round($bg[1] + ($fig[1] - $bg[1]) * $a)) . chr((int) round($bg[2] + ($fig[2] - $bg[2]) * $a));
            }
        }
        $chunk = static fn(string $type, string $data): string => pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        $png = "\x89PNG\r\n\x1a\n"
            . $chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0))
            . $chunk('IDAT', (string) gzcompress($rows, 6))
            . $chunk('IEND', '');
        return $cache = ['bytes' => $png, 'mime' => 'image/png'];
    }
}
