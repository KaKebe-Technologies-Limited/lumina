<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Settings;

/**
 * "Pay with Daraza": payment LINKS created on the Daraza dashboard (paylink.daraza.net). The administrator pastes a link in Admin > Daraza and ticks the places where
 * the button appears. There is no connection to Daraza behind it (no keys, no automatic confirmation): the payer pays on Daraza's own page, and the payment is
 * CONFIRMED BY STAFF (shop orders: the customer types the transaction ID and staff mark the order paid; Academy fees: an administrator marks the fee paid;
 * donations: the money shows in the Daraza dashboard).
 *
 * Places:  donate (every Donate button and the Donate page)  |  shop (the checkout method)  |  signup (Academy sign-up fee)  |  cert (Academy certificate fee)  |
 *           membership (the payment options shown after applying and on the member's private page)
 * Settings (settings table): daraza_label, daraza_url (the main link) and an optional link of its own per place: daraza_donate_url, daraza_shop_url, daraza_signup_url,
 * daraza_cert_url, daraza_membership_url; the switches daraza_donate_on, daraza_signup_on, daraza_cert_on, daraza_membership_on (the shop switch is the shop payment method "daraza": Admin > Shop > Payments).
 * Only https links on daraza.net are accepted, so a wrong paste can never send visitors somewhere else.
 */
final class Daraza
{
    public const PLACES = ['donate' => 'Donate page and every Donate button', 'shop' => 'Shop checkout', 'signup' => 'Academy sign-up fee', 'cert' => 'Academy certificate fee', 'membership' => 'Membership payments'];

    public static function label(): string
    {
        $l = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) Settings::get('daraza_label', 'Pay with Daraza'))));
        return $l !== '' ? mb_substr($l, 0, 40) : 'Pay with Daraza';
    }

    /** Is this a link we are willing to send people to? https, no credentials, and a daraza.net host. */
    public static function validUrl(string $url): bool
    {
        $p = parse_url(trim($url));
        if ($p === false || ($p['scheme'] ?? '') !== 'https' || isset($p['user']) || isset($p['pass']) || strlen($url) > 500) {
            return false;
        }
        $host = strtolower((string) ($p['host'] ?? ''));
        return $host === 'daraza.net' || str_ends_with($host, '.daraza.net');
    }

    /** The link of a place: its own link, else the main link, else ''. */
    public static function url(string $place): string
    {
        if (!isset(self::PLACES[$place])) {
            return '';
        }
        foreach (['daraza_' . $place . '_url', 'daraza_url'] as $key) {
            $u = trim((string) Settings::get($key, ''));
            if ($u !== '' && self::validUrl($u)) {
                return $u;
            }
        }
        return '';
    }

    /** Is the switch of a place on? (the shop one is the shop payment method) */
    public static function on(string $place): bool
    {
        if ($place === 'shop') {
            return (string) Settings::get('shop_pay_daraza_enabled', '0') === '1';
        }
        return isset(self::PLACES[$place]) && (string) Settings::get('daraza_' . $place . '_on', '0') === '1';
    }

    /** Offered to visitors: switched on AND a usable link. */
    public static function enabled(string $place): bool
    {
        return \App\Services\Payments\PaymentOptions::link() && self::on($place) && self::url($place) !== '';
    }
}
