<?php
declare(strict_types=1);

namespace App\Services\Payments;

use App\Core\Settings;

/**
 * Which WAYS TO PAY the website offers, as a whole. Mobile money (MTN and Airtel, through the Daraza API) has its own switch for every place (Admin > Payment options). The EARLIER ways
 * can each be switched off for the whole site and back on whenever you like (nothing is deleted: the settings, links and keys stay):
 *   online  - Pesapal and the other online gateways (card, wallets)
 *   link    - the Daraza payment link ("Pay with Daraza" opens a page on daraza.net)
 *   manual  - bank details and mobile-money numbers shown for the payer to pay by hand, then report the transaction
 * They are on by default, so a website that does not use this page behaves exactly as before.
 */
final class PaymentOptions
{
    public const OPTIONS = [
        'online' => ['Pesapal and other online gateways', 'Card and wallet payments on a hosted page.'],
        'link'   => ['Daraza payment link', 'The "Pay with Daraza" button that opens a payment link on daraza.net.'],
        'manual' => ['Bank and manual mobile-money details', 'Account numbers shown for the payer to pay by hand, then send the transaction ID.'],
    ];

    public static function on(string $option): bool
    {
        return isset(self::OPTIONS[$option]) && (string) Settings::get('pay_opt_' . $option, '1') !== '0';
    }

    public static function online(): bool
    {
        return self::on('online');
    }

    public static function link(): bool
    {
        return self::on('link');
    }

    public static function manual(): bool
    {
        return self::on('manual');
    }

    /** @param array<string,bool> $values option => on */
    public static function save(array $values): void
    {
        $save = [];
        foreach (self::OPTIONS as $k => $_) {
            if (array_key_exists($k, $values)) {
                $save['pay_opt_' . $k] = $values[$k] ? '1' : '0';
            }
        }
        Settings::setMany($save);
        Settings::flush();
    }
}
