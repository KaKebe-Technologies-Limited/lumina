<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Env;
use App\Core\Settings;

/**
 * Typed access to the shop settings (settings table, keys `shop_*`). The definitions below are the single source of truth for defaults:
 * database/seed.sql seeds exactly these keys (a selftest compares them), Admin > Shop > Settings / Payments edit them.
 * They are deliberately NOT in config/settings_schema.php (so the generic Site settings screen never shows or saves them).
 * Secrets (Flutterwave secret key, webhook secret hash) are NOT here: see SecretStore. Texts left empty fall back to the page registry
 * (Admin > Pages > Shop), so the owner can either edit the registry wording or type their own text in the settings screen.
 *
 *   ShopSettings::bool('shop_open')   ShopSettings::int('shop_max_qty')   ShopSettings::str('shop_bank_name')   ShopSettings::currency()
 */
final class ShopSettings
{
    /** key => [default, type (bool|int|str|text|money|email_list|enum:a,b), human label]. */
    public const DEFS = [
        // ---- the shop
        'shop_open'               => ['0', 'bool', 'The shop takes orders'],
        'shop_closed_message'     => ['', 'text', 'Message shown while the shop is closed'],
        'shop_currency'           => ['UGX', 'str', 'Currency (3 letters)'],
        'shop_order_prefix'       => ['LAF', 'str', 'Order number prefix'],
        'shop_notify_emails'      => ['', 'email_list', 'Who is e-mailed about new orders'],
        'shop_max_qty'            => ['10', 'int', 'Most units of one item in a cart'],
        'shop_hold_days'          => ['7', 'int', 'Days a booking or pay-on-pickup order keeps its stock'],
        'shop_manual_hold_days'   => ['3', 'int', 'Days a mobile-money / bank order keeps its stock while waiting for payment'],
        'shop_gateway_hold_hours' => ['24', 'int', 'Hours an unpaid card / mobile-money (Flutterwave) order keeps its stock'],
        'shop_restock_on_cancel'  => ['1', 'bool', 'Put the items back on the shelf when an order is cancelled'],
        'shop_restock_on_refund'  => ['0', 'bool', 'Put the items back on the shelf when an order is refunded'],
        'shop_auto_confirm_paid'  => ['1', 'bool', 'Confirm an order automatically once it is paid'],
        // ---- delivery
        'shop_pickup_enabled'     => ['1', 'bool', 'Customers can collect orders'],
        'shop_pickup_address'     => ['', 'text', 'Collection address (empty = the organisation address)'],
        'shop_pickup_hours'       => ['', 'text', 'Collection hours'],
        'shop_delivery_enabled'   => ['0', 'bool', 'We deliver'],
        'shop_delivery_fee'       => ['0', 'money', 'Delivery fee'],
        'shop_free_delivery_over' => ['0', 'money', 'Free delivery for orders of at least (0 = never free)'],
        'shop_delivery_areas'     => ['', 'text', 'Where we deliver'],
        // ---- policy texts (empty = the wording of the page registry, Admin > Pages > Shop)
        'shop_terms_text'         => ['', 'text', 'Terms of sale'],
        'shop_returns_text'       => ['', 'text', 'Returns and exchanges'],
        'shop_delivery_text'      => ['', 'text', 'Delivery information'],
        // ---- payment methods (non-secret part). Labels / instructions empty = registry wording.
        'shop_pay_book_enabled'   => ['1', 'bool', 'Booking (reserve now, pay later)'],
        'shop_pay_book_label'     => ['', 'str', 'Booking: label'],
        'shop_pay_book_instructions' => ['', 'text', 'Booking: instructions'],
        'shop_pay_book_sort'      => ['50', 'int', 'Booking: position'],
        'shop_pay_cod_enabled'    => ['1', 'bool', 'Pay on pickup or delivery'],
        'shop_pay_cod_label'      => ['', 'str', 'Pay on pickup: label'],
        'shop_pay_cod_instructions' => ['', 'text', 'Pay on pickup: instructions'],
        'shop_pay_cod_sort'       => ['40', 'int', 'Pay on pickup: position'],
        'shop_pay_momo_enabled'   => ['0', 'bool', 'Mobile money (manual)'],
        'shop_pay_momo_label'     => ['', 'str', 'Mobile money: label'],
        'shop_pay_momo_instructions' => ['', 'text', 'Mobile money: instructions'],
        'shop_pay_momo_sort'      => ['20', 'int', 'Mobile money: position'],
        'shop_momo_mtn_name'      => ['', 'str', 'MTN Mobile Money: account name'],
        'shop_momo_mtn_number'    => ['', 'str', 'MTN Mobile Money: number'],
        'shop_momo_airtel_name'   => ['', 'str', 'Airtel Money: account name'],
        'shop_momo_airtel_number' => ['', 'str', 'Airtel Money: number'],
        'shop_pay_bank_enabled'   => ['0', 'bool', 'Bank transfer (manual)'],
        'shop_pay_bank_label'     => ['', 'str', 'Bank transfer: label'],
        'shop_pay_bank_instructions' => ['', 'text', 'Bank transfer: instructions'],
        'shop_pay_bank_sort'      => ['30', 'int', 'Bank transfer: position'],
        'shop_bank_name'          => ['', 'str', 'Bank'],
        'shop_bank_account_name'  => ['', 'str', 'Account name'],
        'shop_bank_account_number' => ['', 'str', 'Account number'],
        'shop_bank_branch'        => ['', 'str', 'Branch'],
        'shop_bank_swift'         => ['', 'str', 'SWIFT / BIC'],
        'shop_pay_flutterwave_enabled' => ['0', 'bool', 'Card and mobile money online (Flutterwave)'],
        'shop_pay_flutterwave_label' => ['', 'str', 'Online payment: label'],
        'shop_pay_flutterwave_instructions' => ['', 'text', 'Online payment: instructions'],
        'shop_pay_flutterwave_sort' => ['10', 'int', 'Online payment: position'],
        'shop_flw_public_key'     => ['', 'str', 'Flutterwave public key'],
        'shop_flw_mode'           => ['test', 'enum:test,live', 'Flutterwave mode'],
        'shop_flw_options'        => ['card,mobilemoneyuganda', 'str', 'Payment options offered on the Flutterwave page'],
        'shop_flw_title'          => ['', 'str', 'Title on the Flutterwave page (empty = the site name)'],
    ];

    /** Payment options Flutterwave Standard accepts that this shop offers in the admin (documented values of `payment_options`). */
    public const FLW_OPTIONS = [
        'card'              => 'Visa / Mastercard (card)',
        'mobilemoneyuganda' => 'Mobile money (Uganda: MTN and Airtel)',
    ];

    /** key => default, for seeding and for comparison with database/seed.sql. @return array<string,string> */
    public static function defaults(): array
    {
        return array_map(static fn(array $d): string => $d[0], self::DEFS);
    }

    public static function str(string $key): string
    {
        $default = self::DEFS[$key][0] ?? '';
        return trim((string) Settings::get($key, $default));
    }

    public static function bool(string $key): bool
    {
        return in_array(strtolower(self::str($key)), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, ?int $min = null, ?int $max = null): int
    {
        $v = (int) self::str($key);
        if ($min !== null) {
            $v = max($min, $v);
        }
        return $max !== null ? min($max, $v) : $v;
    }

    /** A money setting in minor units (never negative). */
    public static function money(string $key): int
    {
        return max(0, Money::parse(self::str($key)) ?? 0);
    }

    /** Three capital letters; anything else falls back to UGX. */
    public static function currency(): string
    {
        $c = strtoupper(self::str('shop_currency'));
        return Money::validCurrency($c) ? $c : 'UGX';
    }

    public static function isOpen(): bool
    {
        return self::bool('shop_open');
    }

    /** The editable "we are closed" notice: the setting, else the registry wording. */
    public static function closedMessage(): string
    {
        $m = self::str('shop_closed_message');
        return $m !== '' ? $m : self::registryText('closed.message');
    }

    /** 2-8 capital letters / digits; used in order numbers. */
    public static function orderPrefix(): string
    {
        $p = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', self::str('shop_order_prefix')));
        return strlen($p) >= 2 ? substr($p, 0, 8) : 'LAF';
    }

    /** @return list<string> valid addresses; empty when none is set (callers then use the organisation notify e-mail). */
    public static function notifyEmails(): array
    {
        $out = [];
        foreach (preg_split('/[\s,;]+/', self::str('shop_notify_emails')) ?: [] as $e) {
            $e = strtolower(trim($e));
            if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL) !== false && !in_array($e, $out, true)) {
                $out[] = $e;
            }
            if (count($out) >= 10) {
                break;
            }
        }
        return $out;
    }

    public static function maxQty(): int
    {
        return self::int('shop_max_qty', 1, 99);
    }

    public static function pickupEnabled(): bool
    {
        return self::bool('shop_pickup_enabled');
    }

    public static function deliveryEnabled(): bool
    {
        return self::bool('shop_delivery_enabled');
    }

    /** @return array{address:string,hours:string} */
    public static function pickup(): array
    {
        $address = self::str('shop_pickup_address');
        if ($address === '') {
            $address = trim((string) Settings::get('address', ''));
        }
        return ['address' => $address, 'hours' => self::str('shop_pickup_hours')];
    }

    /** @return list<string> delivery methods the customer may choose now ('pickup', 'delivery'); never empty (pickup is the fallback). */
    public static function deliveryMethods(): array
    {
        $out = [];
        if (self::pickupEnabled()) {
            $out[] = 'pickup';
        }
        if (self::deliveryEnabled()) {
            $out[] = 'delivery';
        }
        return $out === [] ? ['pickup'] : $out;
    }

    /** Policy text: the owner's own setting, else the registry wording. $which = terms | returns | delivery. Plain text with blank-line paragraphs. */
    public static function policy(string $which): string
    {
        $own = self::str('shop_' . $which . '_text');
        return $own !== '' ? $own : self::registryText('policy.' . $which);
    }

    /** Wording from the page registry (Admin > Pages > Shop); '' when the registry is unavailable. */
    public static function registryText(string $key): string
    {
        try {
            return trim((string) content('shop', $key, ''));
        } catch (\Throwable) {
            return '';
        }
    }

    /** True when a variable is set in .env (the .env value wins over the database for secrets and for FLW_MODE). */
    public static function env(string $name): string
    {
        return trim((string) Env::get($name, ''));
    }
}
