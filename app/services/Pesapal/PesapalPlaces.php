<?php
declare(strict_types=1);

namespace App\Services\Pesapal;

use App\Core\Database;
use App\Core\Settings;
use App\Services\Payments\PaymentOptions;

/**
 * WHERE Pesapal is offered: one switch per payment point, and one question "is Pesapal visible to the payer here?".
 *
 * Pesapal is always the SECOND option: the first one (MTN / Airtel mobile money through the Daraza API, and the other existing ways) is never touched by these
 * switches. The order the payer sees is the one set in Admin > Payment options (PaymentOptions::ranked(): mobile first, Pesapal ("online") second by default).
 *
 *   donate           Donate page, "Give online"                      switch = donate_method (both | pesapal | link; "link" = Pesapal off)
 *   project          Fund a project, "Give now"                      switch = pesapal_place_project      (default off)
 *   shop             Shop checkout                                  switch = shop_pay_pesapal_enabled   (default off)
 *   membership       Membership fee (private page and after applying) switch = pesapal_place_membership   (default off)
 *   academy_signup   Academy sign-up fee                            switch = pesapal_place_academy_signup (default on: it was always offered, the amount is 0 until set)
 *   academy_cert     Academy certificate fee                        switch = pesapal_place_academy_cert   (default on, same reason)
 *
 * Visible = keys in .env + the "online" way is on in Admin > Payment options + database tables (and the purpose) present + the place switched on (+ shop open / fee set).
 * "Mobile money only" (Admin > Payment options) switches the "online" way off for the whole site, so Pesapal is hidden everywhere until it is switched back on.
 */
final class PesapalPlaces
{
    public const PLACES = [
        'donate'         => ['Donate page: "Give online"', '/donate'],
        'project'        => ['Fund a project: "Give now"', '/fund-a-project'],
        'shop'           => ['Shop checkout', '/shop/checkout'],
        'membership'     => ['Membership fee (after applying, renewal, private page)', '/membership'],
        'academy_signup' => ['Academy sign-up fee', '/academy/register'],
        'academy_cert'   => ['Academy certificate fee', '/academy/portal/certificates'],
    ];

    /** Places that were offered before they had a switch of their own: they stay on until someone switches them off. */
    private const DEFAULT_ON = ['academy_signup', 'academy_cert'];

    /** @var array<string,bool> */
    private static array $purposeOk = [];

    public static function valid(string $place): bool
    {
        return isset(self::PLACES[$place]);
    }

    /** The switch of a place (not the same as visible: keys, tables and the site-wide way may still hide it). */
    public static function on(string $place): bool
    {
        return match ($place) {
            'donate' => PesapalPayments::donationsSwitch(),
            'shop'   => (string) Settings::get('shop_pay_pesapal_enabled', '0') === '1',
            default  => self::valid($place) && (string) Settings::get('pesapal_place_' . $place, in_array($place, self::DEFAULT_ON, true) ? '1' : '0') === '1',
        };
    }

    /** Does the ledger table accept this purpose? (the database update 2026-11-pesapal-everywhere.sql adds project and membership) */
    public static function purposeReady(string $place): bool
    {
        $purpose = $place === 'donate' ? 'donation' : $place;
        if (isset(self::$purposeOk[$purpose])) {
            return self::$purposeOk[$purpose];
        }
        try {
            if (!Database::tableExists('pesapal_payments') || !Database::tableExists('pesapal_ipns')) {
                return self::$purposeOk[$purpose] = false;
            }
            $type = (string) Database::value("SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pesapal_payments' AND column_name = 'purpose'", [], '');
            return self::$purposeOk[$purpose] = str_contains($type, "'" . $purpose . "'");
        } catch (\Throwable) {
            return self::$purposeOk[$purpose] = false;
        }
    }

    public static function forgetCache(): void
    {
        self::$purposeOk = [];
    }

    /** What stops Pesapal showing at this place right now, in plain words (empty = visible). @return list<string> */
    public static function problems(string $place): array
    {
        $p = [];
        if (!PesapalClient::hasKeys()) {
            $p[] = 'the Pesapal keys are missing in .env';
        }
        if (!PaymentOptions::online()) {
            $p[] = 'the "Pesapal and other online gateways" way is switched off in Payment options (for example by "Mobile money only")';
        }
        if (!self::purposeReady($place)) {
            $p[] = 'the database update database/updates/2026-11-pesapal-everywhere.sql has not been imported';
        }
        if (!self::on($place)) {
            $p[] = 'the place is switched off';
        }
        if ($place === 'shop' && (string) Settings::get('shop_open', '1') === '0') {
            $p[] = 'the shop is closed';
        }
        if (($place === 'academy_signup' || $place === 'academy_cert') && \App\Services\Academy\AcademyFees::configured($place === 'academy_cert' ? 'cert' : 'signup') === null) {
            $p[] = 'no fee is set (the amount is 0), so there is nothing to pay';
        }
        return $p;
    }

    /** Is Pesapal offered to payers at this place? */
    public static function visible(string $place): bool
    {
        return self::valid($place) && self::problems($place) === [];
    }

    /** "Mobile money only" (or any other reason the whole "online" way is off): Pesapal is hidden everywhere. */
    public static function onlineWayOff(): bool
    {
        return !PaymentOptions::online();
    }

    /** Is the site in the "Mobile money only" state (online, links, manual details and pay-on-delivery all off)? */
    public static function mobileOnly(): bool
    {
        return !PaymentOptions::online() && !PaymentOptions::link() && !PaymentOptions::manual() && !PaymentOptions::on('other');
    }

    /**
     * Tick every place at once (Pesapal as the second option). Does NOT touch the "online" way, the keys, or the first option.
     * @return array<string,string> the settings written
     */
    public static function enableEverywhere(bool $includeShop = true): array
    {
        $save = [];
        $method = PesapalPayments::donateMethod();
        $save['donate_method'] = $method === 'link' ? 'both' : $method;
        foreach (['project', 'membership', 'academy_signup', 'academy_cert'] as $p) {
            $save['pesapal_place_' . $p] = '1';
        }
        if ($includeShop) {
            $save['shop_pay_pesapal_enabled'] = '1';
        }
        Settings::setMany($save);
        return $save;
    }

    /** Pesapal's two payer-facing labels. */
    public const LABEL = 'Card, mobile money or bank (Pesapal)';
    public const HELP = 'Pay with Visa or Mastercard, MTN or Airtel Money, or your bank. You are taken to Pesapal\'s secure page to finish; we never see your card details or PIN.';
}
