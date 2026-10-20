<?php
declare(strict_types=1);

namespace App\Services\Shop;

/**
 * MONEY for the shop. All arithmetic is done in INTEGER MINOR UNITS (1 unit = 100 minor units, for every currency, so a price is stored as
 * DECIMAL(12,2) in the database). There is no floating point in a calculation: floats are only ever turned into text first (parse()).
 *
 *   Money::parse('35,000') === 3500000        Money::format(3500000) === 'UGX 35,000'      Money::toDb(3500000) === '35000.00'
 *   Money::mul(2500000, 3) === 7500000        Money::fromDb('35000.00') === 3500000        Money::gateway(3500000, 'UGX') === 35000
 *
 * Zero-decimal currencies (UGX, RWF, JPY ...) are shown and charged in whole units; a price with cents typed for such a currency is rounded
 * HALF UP to the whole unit by roundTo(). Every other currency keeps two decimals. Rounding is always half up (away from zero for ties).
 * Overflow is refused (MAX = 9,999,999,999.99, the largest value of the DECIMAL(12,2) columns), never wrapped.
 */
final class Money
{
    public const SCALE = 100;
    /** Largest amount accepted anywhere (minor units): 9,999,999,999.99 - exactly what the DECIMAL(12,2) money columns can hold, so nothing accepted here can fail in the database. */
    public const MAX = 999999999999;
    /**
     * Largest UNIT price an owner may set (minor units): 3,000,000.00. An order may hold 30 lines of 99 units each, so 2,970 x this price plus a delivery fee still
     * fits under MAX - a cart or an order total can never overflow.
     */
    public const MAX_PRICE = 300000000;

    /** Currencies without cents (ISO 4217 exponent 0 that a Ugandan shop might meet). */
    private const ZERO_DECIMAL = ['UGX', 'RWF', 'BIF', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'VND', 'VUV', 'XAF', 'XOF', 'XPF', 'CLP'];

    public static function validCurrency(string $code): bool
    {
        return preg_match('/^[A-Z]{3}$/', $code) === 1;
    }

    /** Digits shown after the decimal point for this currency (0 or 2). */
    public static function decimals(string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    /**
     * Text / number typed by a person (or a decimal string from the database) -> minor units. null when it is not a plain amount.
     * Accepts "35000", "35,000", "35000.5", "35 000" is NOT accepted (ambiguous), "1e3" is not accepted, a leading "-" is accepted (callers decide).
     */
    public static function parse(mixed $value): ?int
    {
        if (is_int($value)) {
            return abs($value) > intdiv(self::MAX, self::SCALE) ? null : $value * self::SCALE;
        }
        if (is_float($value)) {
            if (!is_finite($value)) {
                return null;
            }
            $value = number_format($value, 3, '.', '');
        }
        if (!is_string($value)) {
            return null;
        }
        $s = trim($value);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^-?\d{1,3}(?:,\d{3})+(?:\.\d+)?$/', $s) === 1) {
            $s = str_replace(',', '', $s);
        }
        if (preg_match('/^(-?)(\d{1,13})(?:\.(\d+))?$/', $s, $m) !== 1) {
            return null;
        }
        $neg = $m[1] === '-';
        $frac = $m[3] ?? '';
        $minor = (int) $m[2] * self::SCALE + (int) str_pad(substr($frac, 0, 2), 2, '0');
        if (strlen($frac) > 2 && (int) $frac[2] >= 5) {
            $minor++;                                   // half up on the third decimal
        }
        if ($minor > self::MAX) {
            return null;
        }
        return $neg ? -$minor : $minor;
    }

    /** Decimal text from the database (or a float from JSON) -> minor units. Unparseable input counts as 0. */
    public static function fromDb(mixed $value): int
    {
        return self::parse($value) ?? 0;
    }

    /** Minor units -> "35000.00" for a DECIMAL(12,2) column. */
    public static function toDb(int $minor): string
    {
        $neg = $minor < 0;
        $a = abs($minor);
        return ($neg ? '-' : '') . intdiv($a, self::SCALE) . '.' . str_pad((string) ($a % self::SCALE), 2, '0', STR_PAD_LEFT);
    }

    /** Round to what the currency can charge (whole units for UGX), half up. */
    public static function roundTo(int $minor, string $currency): int
    {
        if (self::decimals($currency) !== 0) {
            return $minor;
        }
        $a = abs($minor);
        $units = intdiv($a, self::SCALE) + (($a % self::SCALE) >= 50 ? 1 : 0);
        return ($minor < 0 ? -1 : 1) * $units * self::SCALE;
    }

    /** "UGX 35,000" / "USD 12.50" ($withCode false: "35,000"). Negative amounts get a leading minus. */
    public static function format(int $minor, string $currency = 'UGX', bool $withCode = true): string
    {
        $currency = strtoupper($currency);
        $dec = self::decimals($currency);
        $minor = self::roundTo($minor, $currency);
        $a = abs($minor);
        $whole = number_format(intdiv($a, self::SCALE), 0, '.', ',');
        $text = ($minor < 0 ? '-' : '') . ($dec === 0 ? $whole : $whole . '.' . str_pad((string) ($a % self::SCALE), 2, '0', STR_PAD_LEFT));
        return ($withCode ? $currency . ' ' : '') . $text;
    }

    /** Multiply a unit price by a quantity. @throws \OverflowException when the result exceeds MAX. */
    public static function mul(int $minor, int $qty): int
    {
        if ($qty < 0 || $minor < 0) {
            throw new \InvalidArgumentException('Negative amount or quantity.');
        }
        if ($qty !== 0 && $minor > intdiv(self::MAX, $qty)) {
            throw new \OverflowException('Amount too large.');
        }
        return $minor * $qty;
    }

    /** Sum of amounts. @throws \OverflowException */
    public static function add(int ...$amounts): int
    {
        $sum = 0;
        foreach ($amounts as $a) {
            $sum += $a;
            if ($sum > self::MAX || $sum < -self::MAX) {
                throw new \OverflowException('Amount too large.');
            }
        }
        return $sum;
    }

    /** The number sent to / compared with the payment gateway: an int for whole-unit currencies, else a float with two decimals. */
    public static function gateway(int $minor, string $currency): int|float
    {
        $minor = self::roundTo($minor, $currency);
        return self::decimals($currency) === 0 ? intdiv($minor, self::SCALE) : round($minor / self::SCALE, 2);
    }

    /** A number received from the gateway (JSON int / float / string) -> minor units, or null when it is not a usable amount. */
    public static function fromGateway(mixed $amount): ?int
    {
        if (is_bool($amount) || is_array($amount) || $amount === null) {
            return null;
        }
        return self::parse(is_float($amount) ? number_format($amount, 3, '.', '') : $amount);
    }
}
