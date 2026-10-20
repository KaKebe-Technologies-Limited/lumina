<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Website on/off. The site is "closed" when .env APP_MAINTENANCE=1 (hard switch) or when the admin turned it on at
 * Admin > Website on/off (setting maintenance_enabled). With "open automatically" ticked and an expected-back time in the past
 * the site counts as open again (nothing is rewritten in the database).
 * The visitor page is a standalone, branded HTML document (views/maintenance/standalone.php): logo, message, countdown, contact.
 */
final class Maintenance
{
    private const TZ = 'Africa/Kampala';

    public static function isOn(): bool
    {
        if ((bool) Config::get('app.maintenance', false)) {
            return true;
        }
        try {
            if ((string) Settings::get('maintenance_enabled', '0') !== '1') {
                return false;
            }
            $back = self::returnTimestamp();
            return !((string) Settings::get('maintenance_auto_open', '0') === '1' && $back !== null && $back <= time());
        } catch (\Throwable) {
            return false;            // an unreachable database must never make the maintenance check itself fatal
        }
    }

    /** Expected-back time as a unix timestamp (Africa/Kampala), or null. */
    public static function returnTimestamp(): ?int
    {
        $raw = trim((string) Settings::get('maintenance_return_at', ''));
        if ($raw === '' || !preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}$/', $raw)) {
            return null;
        }
        try {
            $d = new \DateTimeImmutable(str_replace('T', ' ', $raw), new \DateTimeZone(self::TZ));
        } catch (\Throwable) {
            return null;
        }
        return $d->getTimestamp();
    }

    /** Data for the visitor page. */
    public static function data(): array
    {
        $back = self::returnTimestamp();
        $contactOn = (string) Settings::get('maintenance_show_contact', '1') !== '0';
        $socials = [];
        try {
            // the admin's links in the admin's order; the maintenance toggle (maintenance_show_social) is checked inside (docs/SOCIAL.md)
            foreach (social_links('maintenance') as $l) {
                $socials[] = ['label' => $l['label'], 'url' => $l['url']];
            }
        } catch (\Throwable) {
            $socials = [];
        }
        $wa = trim((string) Settings::get('whatsapp', ''));
        if ($wa !== '' && !preg_match('#^https?://#i', $wa)) {
            $digits = preg_replace('/\D+/', '', $wa) ?? '';
            $wa = $digits !== '' ? 'https://wa.me/' . $digits : '';
        }
        return [
            'siteName' => (string) Settings::get('site_name', 'Lumina Africa Foundation'),
            'tagline'  => (string) Settings::get('tagline', ''),
            'title'    => trim((string) Settings::get('maintenance_title', '')) ?: 'We will be back soon',
            'message'  => trim((string) Settings::get('maintenance_message', '')),
            'backAt'   => $back,
            'backText' => $back !== null ? (new \DateTimeImmutable('@' . $back))->setTimezone(new \DateTimeZone(self::TZ))->format('l, j F Y \a\t H:i') . ' (EAT)' : '',
            'contact'  => $contactOn ? [
                'email'   => trim((string) Settings::get('email_primary', '')),
                'phone'   => trim((string) Settings::get('phone_1', '')),
                'whatsapp' => $wa,
                'address' => trim(implode(', ', array_filter([trim((string) Settings::get('address', '')), trim((string) Settings::get('city_country', ''))]))),
            ] : [],
            'socials'  => $socials,
            'logo'     => logo_url(120),
        ];
    }

    /** Visitor response: HTTP 503 + Retry-After + no-store + noindex. $preview = true -> HTTP 200 (admin preview). */
    public static function response(bool $preview = false): Response
    {
        $d = self::data();
        $html = View::render('maintenance/standalone', $d + ['preview' => $preview]);
        $res = Response::html($html, $preview ? 200 : 503);
        $retry = ($d['backAt'] !== null && $d['backAt'] > time()) ? min(86400, max(60, $d['backAt'] - time())) : 3600;
        return $res->header('Cache-Control', 'no-store, max-age=0')->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Retry-After', (string) $retry);
    }
}
