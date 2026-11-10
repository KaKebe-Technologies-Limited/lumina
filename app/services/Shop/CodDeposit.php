<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Settings;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;

/**
 * PAY ON DELIVERY needs a DEPOSIT: the buyer pays a share of the total NOW with one of our payment options (MTN or Airtel mobile money) and the rest when the goods arrive.
 * The share is a setting (Admin > Payment options, default 60%; 0 switches the rule off, 100 means "pay everything now"). The amount is worked out here from the order total,
 * rounded UP to the next whole UGX 100 (or whole unit of another currency), and never more than the total.
 * An order waits (held) until the deposit is paid; then it is confirmed, and staff record the balance when the goods are handed over.
 */
final class CodDeposit
{
    public const DEFAULT_PCT = 60;

    public static function pct(): int
    {
        $v = trim((string) Settings::get('shop_cod_deposit_pct', ''));
        return $v === '' ? self::DEFAULT_PCT : max(0, min(100, (int) $v));
    }

    /** Is a deposit asked for pay-on-delivery orders? */
    public static function active(): bool
    {
        return self::pct() > 0;
    }

    /** Can the deposit be taken right now (mobile money for the shop is on and the API key is on the server)? */
    public static function ready(): bool
    {
        return DarazaPayments::on('shop') && DarazaApi::hasKey();
    }

    /** Deposit for an order total, both in minor units. */
    public static function amountMinor(int $totalMinor, string $currency = 'UGX'): int
    {
        $pct = self::pct();
        if ($pct <= 0 || $totalMinor <= 0) {
            return 0;
        }
        $step = strtoupper($currency) === 'UGX' ? 100 * Money::SCALE : Money::SCALE;       // UGX 100, or one whole unit
        $raw = $totalMinor * $pct;                                                         // x 100 (percent)
        $unit = 100 * $step;
        $dep = intdiv($raw + $unit - 1, $unit) * $step;
        return min($totalMinor, $dep);
    }

    /** The split for an order. @return array{pct:int,deposit:int,balance:int} minor units */
    public static function split(int $totalMinor, string $currency = 'UGX'): array
    {
        $d = self::amountMinor($totalMinor, $currency);
        return ['pct' => self::pct(), 'deposit' => $d, 'balance' => max(0, $totalMinor - $d)];
    }

    /** Does this order owe a deposit before it is confirmed? */
    public static function requiredFor(array $order): bool
    {
        return (string) ($order['payment_method'] ?? '') === 'cod' && self::active();
    }
}
