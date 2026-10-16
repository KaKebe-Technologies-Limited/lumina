<?php
declare(strict_types=1);

namespace App\Services\Web;

/**
 * Replaces organisation tokens inside editable copy with the CURRENT values from Settings, so that copy written in the
 * admin (e.g. the privacy notice) never needs to hard-code an email address or phone number.
 *
 *   {site_name}   {email}   {email_link}   {phone}   {phone_link}   {address}   {year}
 *
 * html(): for already-sanitised HTML (content_html() / rich()). Values are escaped; *_link tokens become anchors.
 * text(): for plain text (textarea / text content) - returns plain text (escape it yourself when printing).
 */
final class TextTokens
{
    public static function html(string $html): string
    {
        if (!str_contains($html, '{')) {
            return $html;
        }
        $v = self::values();
        $esc = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $map = [
            '{site_name}'  => $esc($v['site_name']),
            '{email}'      => $esc($v['email']),
            '{email_link}' => $v['email'] !== '' ? '<a href="mailto:' . $esc($v['email']) . '">' . $esc($v['email']) . '</a>' : '',
            '{phone}'      => $esc($v['phone']),
            '{phone_link}' => $v['phone'] !== '' ? '<a href="' . $esc(phone_href($v['phone'])) . '">' . $esc($v['phone']) . '</a>' : '',
            '{address}'    => $esc($v['address']),
            '{year}'       => $esc($v['year']),
        ];
        return strtr($html, $map);
    }

    public static function text(string $text): string
    {
        if (!str_contains($text, '{')) {
            return $text;
        }
        $v = self::values();
        return strtr($text, [
            '{site_name}'  => $v['site_name'],
            '{email}'      => $v['email'],
            '{email_link}' => $v['email'],
            '{phone}'      => $v['phone'],
            '{phone_link}' => $v['phone'],
            '{address}'    => $v['address'],
            '{year}'       => $v['year'],
        ]);
    }

    /** @return array<string,string> */
    private static function values(): array
    {
        return [
            'site_name' => (string) setting('site_name', ''),
            'email'     => trim((string) setting('email_primary', '')),
            'phone'     => trim((string) setting('phone_1', '')),
            'address'   => implode(', ', array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))])),
            'year'      => date('Y'),
        ];
    }
}
