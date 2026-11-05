<?php
declare(strict_types=1);

namespace App\Services\Fund;

/**
 * The projects people can fund (app/config/fundraising/projects.php) and the arithmetic of a pledge. A choice string is "package:full|year1|cofund", "item:<key>" or "any";
 * for an item the quantity multiplies the unit cost. Fixed prices exist in UGX and USD only (the proposals' figures, USD at UGX 3,750); a co-funding share or "any amount" is
 * typed by the donor in UGX, USD, EUR or GBP.
 */
final class FundProjects
{
    /** @return array<string,mixed> */
    private static function config(): array
    {
        static $c = null;
        return $c ??= (array) require APP_PATH . '/config/fundraising/projects.php';
    }

    /** @return list<array<string,mixed>> */
    public static function all(): array
    {
        return array_values((array) (self::config()['projects'] ?? []));
    }

    /** @return array<string,mixed>|null */
    public static function find(string $slug): ?array
    {
        foreach (self::all() as $p) {
            if ($p['slug'] === $slug) {
                return $p;
            }
        }
        return null;
    }

    /** @return list<array<string,string>> */
    public static function partnerGets(): array
    {
        return array_values((array) (self::config()['partner_gets'] ?? []));
    }

    /** UGX 1,096,147,200 -> "UGX 1.10 billion"; 398,046,000 -> "UGX 398 million". */
    public static function short(float|int $ugx): string
    {
        $ugx = (float) $ugx;
        if ($ugx >= 1.0e9) {
            return 'UGX ' . number_format($ugx / 1.0e9, 2) . ' billion';
        }
        return $ugx >= 1.0e6 ? 'UGX ' . number_format($ugx / 1.0e6, 0) . ' million' : 'UGX ' . number_format($ugx);
    }

    public static function toUsd(float $amount, string $currency): ?float
    {
        return match ($currency) {
            'USD' => $amount,
            'UGX' => $amount / (float) (self::config()['rate'] ?? 3750),
            default => null,
        };
    }

    public static function fromUsd(float $usd, string $currency): ?float
    {
        return match ($currency) {
            'USD' => $usd,
            'UGX' => $usd * (float) (self::config()['rate'] ?? 3750),
            default => null,
        };
    }

    /**
     * Read a choice. @param array<string,mixed> $p the project
     * @return array{code:string,label:string,detail:string,fixed:bool,ugx:?int,usd:?int,qty:int,min_usd:?int}|null null when it is not one of this project's choices
     */
    public static function parseChoice(array $p, string $choice, string $qty = '1'): ?array
    {
        $choice = trim($choice);
        if ($choice === 'any') {
            return ['code' => 'any', 'label' => 'A gift of my choice to ' . $p['short'], 'detail' => '', 'fixed' => false, 'ugx' => null, 'usd' => null, 'qty' => 1, 'min_usd' => null];
        }
        if (($p['status'] ?? '') !== 'proposal') {
            return null;                                   // a project still in preparation only takes "any amount"
        }
        if (str_starts_with($choice, 'package:')) {
            $key = substr($choice, 8);
            foreach ((array) $p['packages'] as $k) {
                if ($k['key'] !== $key) {
                    continue;
                }
                if ($key === 'cofund') {
                    return ['code' => 'package:cofund', 'label' => (string) $k['title'], 'detail' => 'A co-funding share, from USD ' . number_format((float) $k['min_usd']), 'fixed' => false, 'ugx' => null, 'usd' => null, 'qty' => 1, 'min_usd' => (int) $k['min_usd']];
                }
                return ['code' => 'package:' . $key, 'label' => (string) $k['title'], 'detail' => 'UGX ' . number_format((float) $k['ugx']) . ' (about USD ' . number_format((float) $k['usd']) . ')', 'fixed' => true, 'ugx' => (int) $k['ugx'], 'usd' => (int) $k['usd'], 'qty' => 1, 'min_usd' => null];
            }
            return null;
        }
        if (str_starts_with($choice, 'item:')) {
            $key = substr($choice, 5);
            $n = (int) preg_replace('/\D+/', '', $qty);
            if ($n < 1 || $n > 500) {
                return null;
            }
            foreach ((array) $p['items'] as $i) {
                if ($i['key'] === $key) {
                    return ['code' => 'item:' . $key . 'x' . $n, 'label' => (string) $i['title'], 'detail' => 'Quantity: ' . $n . ' x UGX ' . number_format((float) $i['ugx']) . ' (USD ' . number_format((float) $i['usd']) . ' each)', 'fixed' => true, 'ugx' => (int) $i['ugx'], 'usd' => (int) $i['usd'], 'qty' => $n, 'min_usd' => null];
                }
            }
        }
        return null;
    }

    /** The amount of a fixed choice in UGX or USD, or null (other currencies have no fixed price). @param array<string,mixed> $choice */
    public static function fixedAmount(array $choice, string $currency): ?float
    {
        return match ($currency) {
            'UGX' => (float) $choice['ugx'] * (int) $choice['qty'],
            'USD' => (float) $choice['usd'] * (int) $choice['qty'],
            default => null,
        };
    }
}
