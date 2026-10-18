<?php
declare(strict_types=1);

namespace App\Services\Calendar;

/**
 * WCAG 2.x contrast maths for the types manager and for the colours the calendar prints. Pure PHP.
 *
 * A type has ONE stored colour (#RRGGBB). pair() derives the accessible set used everywhere:
 *   solid      the stored colour (dots, borders, solid badges)
 *   on_solid   white, near-black or black: the first that reaches >= 4.5:1 on `solid` (AA for normal text)
 *   tint       a light wash of the colour (pill background)
 *   text       the colour darkened until it reaches >= 4.5:1 on `tint` (pill text)
 * Colour is never the only cue: types also carry an icon and a text label.
 */
final class ColorContrast
{
    public const WHITE = '#ffffff';
    public const INK = '#1d1e20';
    public const AA = 4.5;

    /** '#abc' or '#aabbcc' (any case) to '#aabbcc', else null. */
    public static function normalise(mixed $hex): ?string
    {
        if (!is_string($hex)) {
            return null;
        }
        $h = ltrim(trim($hex), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/', $h)) {
            $h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        return preg_match('/^[0-9a-fA-F]{6}$/', $h) ? '#' . strtolower($h) : null;
    }

    /** @return array{0:int,1:int,2:int} */
    public static function rgb(string $hex): array
    {
        $h = ltrim(self::normalise($hex) ?? '#000000', '#');
        return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
    }

    public static function hex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }

    /** Relative luminance 0..1. */
    public static function luminance(string $hex): float
    {
        $ch = array_map(static function (int $v): float {
            $c = $v / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, self::rgb($hex));
        return 0.2126 * $ch[0] + 0.7152 * $ch[1] + 0.0722 * $ch[2];
    }

    /** Contrast ratio 1..21 (rounded to 2 decimals). */
    public static function ratio(string $a, string $b): float
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        return round((max($la, $lb) + 0.05) / (min($la, $lb) + 0.05), 2);
    }

    public static function passesAA(string $fg, string $bg, bool $large = false): bool
    {
        return self::ratio($fg, $bg) >= ($large ? 3.0 : self::AA);
    }

    /** Text colour for a solid background: white, else near-black, else black (the first that reaches AA 4.5:1; mid-tone greys need black). */
    public static function textOn(string $bg): string
    {
        foreach ([self::WHITE, self::INK, '#000000'] as $c) {
            if (self::ratio($c, $bg) >= self::AA) {
                return $c;
            }
        }
        return self::ratio(self::WHITE, $bg) >= self::ratio('#000000', $bg) ? self::WHITE : '#000000';
    }

    /** Mix $hex towards white by $amount (0..1). */
    public static function tint(string $hex, float $amount = 0.88): string
    {
        [$r, $g, $b] = self::rgb($hex);
        return self::hex((int) round($r + (255 - $r) * $amount), (int) round($g + (255 - $g) * $amount), (int) round($b + (255 - $b) * $amount));
    }

    /** Darken $hex (towards black) until it has at least $min contrast on $bg. */
    public static function darkenToContrast(string $hex, string $bg, float $min = self::AA): string
    {
        $h = self::normalise($hex) ?? '#000000';
        for ($i = 0; $i < 40 && self::ratio($h, $bg) < $min; $i++) {
            [$r, $g, $b] = self::rgb($h);
            $h = self::hex((int) round($r * 0.92), (int) round($g * 0.92), (int) round($b * 0.92));
        }
        return $h;
    }

    /**
     * The accessible colour set of a stored colour.
     * @return array{solid:string,on_solid:string,tint:string,text:string,ratio_solid:float,ratio_text:float,aa_solid:bool,aa_text:bool}
     */
    public static function pair(?string $color): array
    {
        $solid = self::normalise($color) ?? '#5a6470';
        $onSolid = self::textOn($solid);
        $tint = self::tint($solid);
        $text = self::darkenToContrast($solid, $tint);
        return [
            'solid' => $solid, 'on_solid' => $onSolid, 'tint' => $tint, 'text' => $text,
            'ratio_solid' => self::ratio($onSolid, $solid), 'ratio_text' => self::ratio($text, $tint),
            'aa_solid' => self::passesAA($onSolid, $solid), 'aa_text' => self::passesAA($text, $tint),
        ];
    }
}
