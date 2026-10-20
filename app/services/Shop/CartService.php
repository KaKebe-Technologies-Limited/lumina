<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Session;

/**
 * The visitor's CART, kept in the PHP session. The session (and its cookie) is created ONLY when the first item is added: reading the cart
 * (header badge, cart page) never starts a session, so anonymous page views still set no cookie.
 *
 * The session stores only   ['lines' => [lineKey => ['p' => productId, 'o' => [optionKey => value], 'q' => qty]], 'delivery' => 'pickup'|'delivery',
 * 'prefer' => null|'book']   - never a price, a name or a stock level. Everything is looked up in the database again every time lines() runs,
 * and OrderService::create() checks it a last time under a row lock. A line is identified by sha1(productId + sorted options).
 *
 * add() / setQty() / remove() return arrays; error codes (the storefront maps them to the editable wording `cart.err_*`):
 *   closed (shop closed)   not_found (hidden / deleted product)   options (bad or missing choice; `errors` lists option => required|invalid|out_of_stock)
 *   out_of_stock           not_bookable (mode=book on a product without booking)   limit (too many different items)   qty (not a number)
 */
final class CartService
{
    public const SESSION_KEY = 'shop_cart';
    public const MAX_LINES = 30;

    // ------------------------------------------------------------------------------------------------ reading (never starts a session)

    /** @return array{lines:array<string,array{p:int,o:array<string,string>,q:int}>,delivery:string,prefer:?string} */
    public static function raw(): array
    {
        $c = Session::get(self::SESSION_KEY);
        $out = ['lines' => [], 'delivery' => 'pickup', 'prefer' => null];
        if (!is_array($c)) {
            return $out;
        }
        foreach ((array) ($c['lines'] ?? []) as $k => $l) {
            if (!is_string($k) || !is_array($l) || !isset($l['p'], $l['q'])) {
                continue;
            }
            $opts = [];
            foreach ((array) ($l['o'] ?? []) as $ok => $ov) {
                if (is_string($ok) && is_string($ov)) {
                    $opts[$ok] = $ov;
                }
            }
            $out['lines'][$k] = ['p' => (int) $l['p'], 'o' => $opts, 'q' => max(1, (int) $l['q'])];
        }
        $out['delivery'] = ($c['delivery'] ?? '') === 'delivery' ? 'delivery' : 'pickup';
        $out['prefer'] = ($c['prefer'] ?? null) === 'book' ? 'book' : null;
        return $out;
    }

    public static function isEmpty(): bool
    {
        return self::raw()['lines'] === [];
    }

    /** Total units in the cart (for the header badge). 0 without a session. */
    public static function units(): int
    {
        $n = 0;
        foreach (self::raw()['lines'] as $l) {
            $n += $l['q'];
        }
        return $n;
    }

    public static function lineKey(int $productId, array $selection): string
    {
        ksort($selection);
        return substr(sha1($productId . '|' . json_encode($selection)), 0, 12);
    }

    // ------------------------------------------------------------------------------------------------ writing

    /**
     * Add a product. $selection = posted option choices (array key => value). $mode 'book' = the customer wants to RESERVE it (product must allow booking).
     * @return array{ok:bool,error:?string,errors:array<string,string>,key:?string,qty:int,clamped:bool}
     */
    public static function add(int $productId, mixed $selection, int $qty = 1, string $mode = 'cart'): array
    {
        $fail = static fn(string $code, array $errors = []): array => ['ok' => false, 'error' => $code, 'errors' => $errors, 'key' => null, 'qty' => 0, 'clamped' => false];
        if (!ShopSettings::isOpen()) {
            return $fail('closed');
        }
        if ($productId < 1) {
            return $fail('not_found');
        }
        $product = ProductService::find($productId, true);
        if ($product === null) {
            return $fail('not_found');
        }
        $check = ProductService::validateSelection($product, $selection);
        if (!$check['ok']) {
            return $fail('options', $check['errors']);
        }
        if ($mode === 'book' && (int) $product['allow_booking'] !== 1) {
            return $fail('not_bookable');
        }
        $max = ProductService::maxOrderable($product, $check['selection']);
        if ($max !== null && $max < 1) {
            return $fail('out_of_stock');
        }
        $cap = $max === null ? ShopSettings::maxQty() : min(ShopSettings::maxQty(), $max);
        $cart = self::raw();
        $key = self::lineKey($productId, $check['selection']);
        if (!isset($cart['lines'][$key]) && count($cart['lines']) >= self::MAX_LINES) {
            return $fail('limit');
        }
        $room = self::productRoom($product, $cart['lines'], $key);           // the shelf is shared by every size / colour line of the same product
        if ($room !== null) {
            if ($room < 1) {
                return $fail('out_of_stock');
            }
            $cap = min($cap, $room);
        }
        $have = $cart['lines'][$key]['q'] ?? 0;
        $want = $have + max(1, $qty);
        $final = min($want, $cap);
        $cart['lines'][$key] = ['p' => $productId, 'o' => $check['selection'], 'q' => $final];
        if ($mode === 'book') {
            $cart['prefer'] = 'book';
        }
        self::save($cart);
        return ['ok' => true, 'error' => null, 'errors' => [], 'key' => $key, 'qty' => $final, 'clamped' => $final < $want];
    }

    /** Set the quantity of a line (0 removes it). Clamped to the limits; never starts a session when there is no cart. @return array{ok:bool,error:?string,qty:int,clamped:bool} */
    public static function setQty(string $lineKey, int $qty): array
    {
        $cart = self::raw();
        if (!isset($cart['lines'][$lineKey])) {
            return ['ok' => false, 'error' => 'not_found', 'qty' => 0, 'clamped' => false];
        }
        if ($qty <= 0) {
            unset($cart['lines'][$lineKey]);
            self::save($cart);
            return ['ok' => true, 'error' => null, 'qty' => 0, 'clamped' => false];
        }
        $l = $cart['lines'][$lineKey];
        $product = ProductService::find($l['p'], true);
        $cap = ShopSettings::maxQty();
        if ($product !== null) {
            $max = ProductService::maxOrderable($product, $l['o']);
            if ($max !== null) {
                $cap = min($cap, $max);
            }
            $room = self::productRoom($product, $cart['lines'], $lineKey);
            if ($room !== null) {
                $cap = min($cap, $room);
            }
        }
        if ($cap < 1) {
            unset($cart['lines'][$lineKey]);
            self::save($cart);
            return ['ok' => false, 'error' => 'out_of_stock', 'qty' => 0, 'clamped' => false];
        }
        $final = min($qty, $cap);
        $cart['lines'][$lineKey]['q'] = $final;
        self::save($cart);
        return ['ok' => true, 'error' => null, 'qty' => $final, 'clamped' => $final < $qty];
    }

    /**
     * Units of a stock-tracked product that the OTHER lines of the cart have not claimed yet (null = the product has no stock limit). Every size / colour line of one
     * product draws on the same shelf, so each line is capped against what is left after the others, not against the whole shelf.
     * @param array<string,mixed> $product hydrated product  @param array<string,array{p:int,o:array<string,string>,q:int}> $lines raw cart lines
     */
    private static function productRoom(array $product, array $lines, string $exceptKey): ?int
    {
        $stock = ProductService::stockInfo($product);
        if (!$stock['tracked']) {
            return null;
        }
        $others = 0;
        foreach ($lines as $k => $l) {
            if ($k !== $exceptKey && (int) $l['p'] === (int) $product['id']) {
                $others += (int) $l['q'];
            }
        }
        return max(0, (int) $stock['qty'] - $others);
    }

    public static function remove(string $lineKey): bool
    {
        $cart = self::raw();
        if (!isset($cart['lines'][$lineKey])) {
            return false;
        }
        unset($cart['lines'][$lineKey]);
        self::save($cart);
        return true;
    }

    public static function clear(): void
    {
        if (Session::has(self::SESSION_KEY)) {
            Session::forget(self::SESSION_KEY);
        }
    }

    /** The delivery method chosen in the cart (only the methods the shop offers). */
    public static function delivery(): string
    {
        $d = self::raw()['delivery'];
        return in_array($d, ShopSettings::deliveryMethods(), true) ? $d : ShopSettings::deliveryMethods()[0];
    }

    public static function setDelivery(string $method): void
    {
        if (!in_array($method, ShopSettings::deliveryMethods(), true)) {
            return;
        }
        $cart = self::raw();
        if ($cart['lines'] === [] && !Session::has(self::SESSION_KEY)) {
            return;                                     // nothing in the cart: do not create a session just for this
        }
        $cart['delivery'] = $method;
        self::save($cart);
    }

    /** The payment method the visitor leaned towards ("Book / reserve this item" sets 'book'). */
    public static function prefer(): ?string
    {
        return self::raw()['prefer'];
    }

    private static function save(array $cart): void
    {
        if ($cart['lines'] === []) {
            if (Session::has(self::SESSION_KEY)) {
                Session::forget(self::SESSION_KEY);
            }
            return;
        }
        Session::put(self::SESSION_KEY, $cart);
    }

    // ------------------------------------------------------------------------------------------------ resolved view

    /**
     * The cart as the cart page, the checkout and OrderService need it: every line looked up again in the database.
     * `problem`: null | 'removed' (product hidden / deleted / choice no longer valid: NOT part of the quote) | 'out_of_stock' (not part of the quote)
     * | 'reduced' (more than the shelf holds: shown with the reduced quantity and part of the quote). Reading never writes the session.
     * @return list<array<string,mixed>>
     */
    public static function lines(): array
    {
        $out = [];
        $cur = ShopSettings::currency();
        foreach (self::raw()['lines'] as $key => $l) {
            $product = ProductService::find($l['p'], true);
            $base = ['key' => $key, 'product_id' => $l['p'], 'selection' => $l['o'], 'qty' => $l['q'], 'problem' => null, 'product' => $product,
                'name' => '', 'url' => '', 'image' => null, 'options_text' => '', 'unit_minor' => 0, 'line_minor' => 0, 'unit_text' => '', 'line_text' => '',
                'max' => null, 'bookable' => false, 'sku' => ''];
            if ($product === null) {
                $out[] = ['problem' => 'removed'] + $base;
                continue;
            }
            $check = ProductService::validateSelection($product, $l['o']);
            $base['name'] = (string) $product['name'];
            $base['url'] = (string) $product['url'];
            $base['image'] = $product['cover'];
            $base['sku'] = (string) ($product['sku'] ?? '');
            $base['bookable'] = (int) $product['allow_booking'] === 1;
            if (!$check['ok']) {
                $out[] = ['problem' => $check['errors'] === ['_' => 'unknown'] ? 'removed' : (in_array('out_of_stock', $check['errors'], true) ? 'out_of_stock' : 'removed')] + $base;
                continue;
            }
            $base['options_text'] = $check['text'];
            $max = ProductService::maxOrderable($product, $check['selection']);
            $base['max'] = $max;
            if ($max !== null && $max < 1) {
                $out[] = ['problem' => 'out_of_stock'] + $base;
                continue;
            }
            $qty = $l['q'];
            $cap = $max === null ? ShopSettings::maxQty() : min($max, ShopSettings::maxQty());
            if ($qty > $cap) {
                $qty = $cap;
                $base['problem'] = 'reduced';
            }
            $unit = Money::roundTo((int) $product['unit_minor'], $cur);
            $base['qty'] = $qty;
            $base['unit_minor'] = $unit;
            $base['line_minor'] = Money::mul($unit, $qty);
            $base['unit_text'] = Money::format($unit, $cur);
            $base['line_text'] = Money::format($base['line_minor'], $cur);
            $out[] = $base;
        }
        return self::shareShelf($out, $cur);
    }

    /**
     * Lines of ONE tracked product (different sizes / colours) together may not take more than the shelf holds: allocate the stock to the lines in cart order, mark
     * the ones that got less as `reduced` (or `out_of_stock` when nothing is left) and set each line's `max` to what it may still be raised to. Single-line products
     * were already capped on their own above.
     * @param list<array<string,mixed>> $out @return list<array<string,mixed>>
     */
    private static function shareShelf(array $out, string $cur): array
    {
        $groups = [];
        foreach ($out as $i => $row) {
            if (($row['problem'] === null || $row['problem'] === 'reduced') && $row['product'] !== null) {
                $groups[(int) $row['product_id']][] = $i;
            }
        }
        foreach ($groups as $idxs) {
            if (count($idxs) < 2) {
                continue;
            }
            $stock = ProductService::stockInfo($out[$idxs[0]]['product']);
            if (!$stock['tracked']) {
                continue;
            }
            $left = max(0, (int) $stock['qty']);
            foreach ($idxs as $i) {
                $want = (int) $out[$i]['qty'];
                $give = min($want, $left);
                $left -= $give;
                if ($give < $want) {
                    $out[$i]['qty'] = $give;
                    $out[$i]['problem'] = $give < 1 ? 'out_of_stock' : 'reduced';
                    $out[$i]['line_minor'] = Money::mul((int) $out[$i]['unit_minor'], $give);
                    $out[$i]['line_text'] = Money::format($out[$i]['line_minor'], $cur);
                }
            }
            $taken = 0;
            foreach ($idxs as $i) {
                $taken += (int) $out[$i]['qty'];
            }
            foreach ($idxs as $i) {
                $room = max(0, (int) $stock['qty'] - ($taken - (int) $out[$i]['qty']));          // what this line may be raised to
                $out[$i]['max'] = $out[$i]['max'] === null ? $room : min((int) $out[$i]['max'], $room);
            }
        }
        return $out;
    }

    /** Lines that count towards the total (no 'removed' / 'out_of_stock'). @param list<array<string,mixed>> $lines @return list<array<string,mixed>> */
    public static function purchasable(array $lines): array
    {
        return array_values(array_filter($lines, static fn(array $l): bool => $l['problem'] === null || $l['problem'] === 'reduced'));
    }

    /**
     * Everything the cart and checkout pages show.
     * @return array{lines:list<array<string,mixed>>,quote:array<string,mixed>,texts:array<string,string>,units:int,has_problems:bool,all_bookable:bool,open:bool,delivery:string,prefer:?string}
     */
    public static function summary(): array
    {
        $lines = self::lines();
        $buy = self::purchasable($lines);
        $delivery = self::delivery();
        $quote = PricingService::quote($buy, $delivery);
        $problems = false;
        foreach ($lines as $l) {
            if ($l['problem'] !== null) {
                $problems = true;
            }
        }
        $bookable = $buy !== [];
        foreach ($buy as $l) {
            if (!$l['bookable']) {
                $bookable = false;
            }
        }
        return [
            'lines'        => $lines,
            'quote'        => $quote,
            'texts'        => PricingService::texts($quote),
            'units'        => $quote['units'],
            'has_problems' => $problems,
            'all_bookable' => $bookable,
            'open'         => ShopSettings::isOpen(),
            'delivery'     => $quote['delivery_method'],
            'prefer'       => self::prefer(),
        ];
    }
}
