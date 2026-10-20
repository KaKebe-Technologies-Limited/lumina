<?php
declare(strict_types=1);

namespace App\Services\Shop;

/**
 * The payment methods of the shop, built from the settings the owner edits in Admin > Shop > Payments.
 *
 *   key          kind      what happens
 *   book         deferred  BOOK / PRE-ORDER: reserve now, pay later (on collection or by an agreed method). Stock is reserved; staff confirm.
 *   cod          deferred  PAY ON PICKUP / DELIVERY: cash or mobile money when the goods are handed over.
 *   momo         manual    MOBILE MONEY (MTN / Airtel): the customer sends the money to the shown number quoting the order number, then enters
 *                          the transaction ID on the confirmation page; staff VERIFY and mark the order paid.
 *   bank         manual    BANK TRANSFER: same idea with the bank details and an optional transfer reference.
 *   flutterwave  online    CARD (Visa / Mastercard) + MOBILE MONEY through the Flutterwave hosted checkout (a full-page redirect).
 *
 * A method is OFFERED at checkout only when it is enabled AND usable (ready): momo needs a number, bank an account number, flutterwave a
 * secret key, book needs every cart item to allow booking. list() explains what is missing (`problems`) for the admin checklist.
 */
final class PaymentMethods
{
    public const KEYS = ['book', 'cod', 'momo', 'bank', 'flutterwave'];
    public const KINDS = ['book' => 'deferred', 'cod' => 'deferred', 'momo' => 'manual', 'bank' => 'manual', 'flutterwave' => 'online'];

    public static function isKey(string $key): bool
    {
        return in_array($key, self::KEYS, true);
    }

    /** Customer-facing name of a method (settings label, else registry wording). */
    public static function label(string $key): string
    {
        if (!self::isKey($key)) {
            return $key;
        }
        $own = ShopSettings::str('shop_pay_' . $key . '_label');
        if ($own !== '') {
            return $own;
        }
        $reg = ShopSettings::registryText('payment.' . $key . '_label');
        return $reg !== '' ? $reg : ucfirst($key);
    }

    /** Instructions of a method (plain text, blank-line paragraphs): settings text, else registry wording. */
    public static function instructions(string $key): string
    {
        if (!self::isKey($key)) {
            return '';
        }
        $own = ShopSettings::str('shop_pay_' . $key . '_instructions');
        return $own !== '' ? $own : ShopSettings::registryText('payment.' . $key . '_instructions');
    }

    /**
     * Account details to show a customer, only the filled-in ones.
     * momo: list of ['network' => 'mtn'|'airtel', 'label', 'name', 'number'] ; bank: list of ['label', 'value'] ; others: [].
     * @return list<array<string,string>>
     */
    public static function details(string $key): array
    {
        $out = [];
        if ($key === 'momo') {
            foreach (['mtn' => self::word('label_mtn', 'MTN Mobile Money'), 'airtel' => self::word('label_airtel', 'Airtel Money')] as $net => $label) {
                $number = ShopSettings::str('shop_momo_' . $net . '_number');
                if ($number !== '') {
                    $out[] = ['network' => $net, 'label' => $label, 'name' => ShopSettings::str('shop_momo_' . $net . '_name'), 'number' => $number];
                }
            }
        } elseif ($key === 'bank') {
            foreach ([
                self::word('label_bank', 'Bank') => 'shop_bank_name', self::word('label_account_name', 'Account name') => 'shop_bank_account_name',
                self::word('label_account_number', 'Account number') => 'shop_bank_account_number', self::word('label_branch', 'Branch') => 'shop_bank_branch',
                self::word('label_swift', 'SWIFT / BIC') => 'shop_bank_swift',
            ] as $label => $k) {
                $v = ShopSettings::str($k);
                if ($v !== '') {
                    $out[] = ['label' => $label, 'value' => $v];
                }
            }
        }
        return $out;
    }

    /** A label shown to customers next to the payment details: the editable wording (Admin > Pages > Shop > Order page), else the plain English default. */
    private static function word(string $key, string $default): string
    {
        $t = ShopSettings::registryText('order.' . $key);
        return $t !== '' ? $t : $default;
    }

    /** What is still missing for a method to be usable. @return list<string> plain-language problems (empty = ready). */
    public static function problems(string $key): array
    {
        $p = [];
        switch ($key) {
            case 'momo':
                if (self::details('momo') === []) {
                    $p[] = 'Add at least one mobile-money number (MTN or Airtel).';
                }
                break;
            case 'bank':
                if (ShopSettings::str('shop_bank_account_number') === '') {
                    $p[] = 'Add the bank account number.';
                }
                break;
            case 'flutterwave':
                if (SecretStore::get('flw_secret_key') === null) {
                    $p[] = 'Add the Flutterwave secret key.';
                }
                if (SecretStore::get('flw_secret_hash') === null) {
                    $p[] = 'Add the webhook secret hash (payments are still checked when the customer comes back, but the webhook catches customers who close the page).';
                }
                if (!SecretStore::available()) {
                    $p[] = 'This server cannot encrypt secrets (the PHP openssl extension with AES-256-GCM is missing); put the keys in the .env file instead.';
                }
                if (self::flwOptions() === []) {
                    $p[] = 'Choose at least one payment option (card or mobile money).';
                }
                break;
        }
        return $p;
    }

    /** Is the method usable right now (ignoring the cart)? Flutterwave needs only the secret key to take payments. */
    public static function ready(string $key): bool
    {
        if ($key === 'flutterwave') {
            return SecretStore::get('flw_secret_key') !== null && self::flwOptions() !== [];
        }
        return self::problems($key) === [];
    }

    public static function enabled(string $key): bool
    {
        return self::isKey($key) && ShopSettings::bool('shop_pay_' . $key . '_enabled');
    }

    /**
     * Every method, in the order the owner chose.
     * @return list<array{key:string,kind:string,label:string,instructions:string,enabled:bool,ready:bool,problems:list<string>,sort:int,details:list<array<string,string>>}>
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::KEYS as $key) {
            $problems = self::problems($key);
            $out[] = [
                'key'          => $key,
                'kind'         => self::KINDS[$key],
                'label'        => self::label($key),
                'instructions' => self::instructions($key),
                'enabled'      => self::enabled($key),
                'ready'        => self::ready($key),
                'problems'     => $problems,
                'sort'         => ShopSettings::int('shop_pay_' . $key . '_sort'),
                'details'      => self::details($key),
            ];
        }
        usort($out, static fn(array $a, array $b): int => [$a['sort'], array_search($a['key'], self::KEYS, true)] <=> [$b['sort'], array_search($b['key'], self::KEYS, true)]);
        return $out;
    }

    /**
     * Methods a customer may choose at checkout for this cart: enabled and ready, in the owner's order.
     * @param bool $allBookable true when every cart item allows booking (the booking method is only offered then)
     * @return list<array<string,mixed>>
     */
    public static function available(bool $allBookable = false): array
    {
        $out = [];
        foreach (self::all() as $m) {
            if (!$m['enabled'] || !$m['ready']) {
                continue;
            }
            if ($m['key'] === 'book' && !$allBookable) {
                continue;
            }
            $out[] = $m;
        }
        return $out;
    }

    /** Is $key one of the methods offered for this cart? (The checkout re-checks this on the server; the browser list is never trusted.) */
    public static function isAvailable(string $key, bool $allBookable): bool
    {
        foreach (self::available($allBookable) as $m) {
            if ($m['key'] === $key) {
                return true;
            }
        }
        return false;
    }

    /** Payment options sent to Flutterwave, in a fixed safe order. @return list<string> */
    public static function flwOptions(): array
    {
        $chosen = array_map('trim', explode(',', strtolower(ShopSettings::str('shop_flw_options'))));
        return array_values(array_filter(array_keys(ShopSettings::FLW_OPTIONS), static fn(string $o): bool => in_array($o, $chosen, true)));
    }

    /** test | live. FLW_MODE in .env wins. */
    public static function flwMode(): string
    {
        $env = strtolower(ShopSettings::env('FLW_MODE'));
        if (in_array($env, ['test', 'live'], true)) {
            return $env;
        }
        return ShopSettings::str('shop_flw_mode') === 'live' ? 'live' : 'test';
    }

    /** The public key: .env FLW_PUBLIC_KEY first, then the setting. Not a secret, but never needed by the hosted checkout. */
    public static function flwPublicKey(): string
    {
        $env = ShopSettings::env('FLW_PUBLIC_KEY');
        return $env !== '' ? $env : ShopSettings::str('shop_flw_public_key');
    }
}
