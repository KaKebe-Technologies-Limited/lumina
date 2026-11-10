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
 *   other   - shop only: cash on delivery and booking
 * They are on by default, so a website that does not use this page behaves exactly as before.
 */
final class PaymentOptions
{
    public const OPTIONS = [
        'online' => ['Pesapal and other online gateways', 'Card and wallet payments on a hosted page.'],
        'link'   => ['Payment links (Daraza, GoFundMe)', 'Buttons that send the visitor to an outside page: the "Pay with Daraza" link and the online donation link (GoFundMe).'],
        'manual' => ['Bank and manual mobile-money details', 'Account numbers shown for the payer to pay by hand, then send the transaction ID.'],
        'other'  => ['Pay on delivery and booking (shop)', 'Cash on delivery and "reserve and pay later" at the shop checkout.'],
    ];

    /** The ways to pay as visitors meet them, with the default order (Admin > Payment options changes the order). */
    public const WAYS = [
        'mobile' => ['MTN and Airtel mobile money', 'Pay now with a phone: the payer approves on the phone (Daraza API). Switched on per place below.'],
        'online' => ['Card and online (Pesapal, Flutterwave)', 'Visa, Mastercard and wallets on a secure hosted page.'],
        'link'   => ['Payment links (Daraza, GoFundMe)', 'The "Pay with Daraza" button and the online donation link.'],
        'manual' => ['Bank and manual mobile-money details', 'Account numbers shown for the payer to pay by hand.'],
        'other'  => ['Pay on delivery and booking (shop)', 'Cash on delivery and reserve-and-pay-later at the shop checkout.'],
    ];

    /** Position of a way in the list shown to visitors (1 = first). */
    public static function rank(string $way): int
    {
        $d = array_search($way, array_keys(self::WAYS), true);
        $v = (int) Settings::get('pay_rank_' . $way, '');
        return $v >= 1 && $v <= 9 ? $v : (int) $d + 1;
    }

    /** The ways in the order visitors see them. @return list<string> */
    public static function ranked(): array
    {
        $ways = array_keys(self::WAYS);
        usort($ways, static fn(string $a, string $b): int => [self::rank($a), array_search($a, array_keys(self::WAYS), true)] <=> [self::rank($b), array_search($b, array_keys(self::WAYS), true)]);
        return $ways;
    }

    /** @param array<string,int> $ranks way => position (1-9) */
    public static function saveRanks(array $ranks): void
    {
        $save = [];
        foreach (self::WAYS as $k => $_) {
            if (isset($ranks[$k])) {
                $save['pay_rank_' . $k] = (string) max(1, min(9, (int) $ranks[$k]));
            }
        }
        Settings::setMany($save);
        Settings::flush();
    }

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
