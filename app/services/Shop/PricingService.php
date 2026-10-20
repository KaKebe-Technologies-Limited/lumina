<?php
declare(strict_types=1);

namespace App\Services\Shop;

/**
 * THE ONLY PLACE WHERE AN ORDER TOTAL IS CALCULATED. The cart page, the checkout page, the order creation and the payment amount all call
 * quote(); nothing else adds, multiplies or compares prices. Inputs are always taken from the DATABASE (ProductService::priceInfo), never
 * from the browser.
 *
 *   subtotal      = sum(unit price now x quantity) of the lines
 *   delivery fee  = 0 for pickup; for delivery: the fee of Admin > Shop > Settings, or 0 when the subtotal reaches "free delivery for orders of at least"
 *   total         = subtotal + delivery fee
 *
 * All amounts are integer minor units (Money). A quote for an empty list of lines is a zero quote.
 */
final class PricingService
{
    /**
     * @param list<array<string,mixed>> $lines each needs 'unit_minor' (int) and 'qty' (int >= 1)
     * @return array{currency:string,units:int,subtotal:int,delivery_method:string,delivery_fee:int,total:int,free_delivery:bool,free_over:?int,free_gap:?int,delivery_available:bool,pickup_available:bool,line_totals:list<int>}
     * @throws \OverflowException when the amounts are absurdly large
     */
    public static function quote(array $lines, string $deliveryMethod = 'pickup'): array
    {
        $currency = ShopSettings::currency();
        $subtotal = 0;
        $units = 0;
        $lineTotals = [];
        foreach ($lines as $l) {
            $qty = max(0, (int) ($l['qty'] ?? 0));
            $unit = max(0, (int) ($l['unit_minor'] ?? 0));
            $lt = Money::mul(Money::roundTo($unit, $currency), $qty);
            $lineTotals[] = $lt;
            $subtotal = Money::add($subtotal, $lt);
            $units += $qty;
        }
        $methods = ShopSettings::deliveryMethods();
        $method = in_array($deliveryMethod, $methods, true) ? $deliveryMethod : $methods[0];
        $fee = 0;
        $freeOver = null;
        $free = false;
        $gap = null;
        if ($method === 'delivery') {
            $freeThreshold = ShopSettings::money('shop_free_delivery_over');
            $freeOver = $freeThreshold > 0 ? $freeThreshold : null;
            if ($freeOver !== null && $subtotal >= $freeOver) {
                $free = true;
            } else {
                $fee = Money::roundTo(ShopSettings::money('shop_delivery_fee'), $currency);
                $gap = $freeOver !== null ? $freeOver - $subtotal : null;
            }
        }
        return [
            'currency'           => $currency,
            'units'              => $units,
            'subtotal'           => $subtotal,
            'delivery_method'    => $method,
            'delivery_fee'       => $fee,
            'total'              => Money::add($subtotal, $fee),
            'free_delivery'      => $free,
            'free_over'          => $freeOver,
            'free_gap'           => $gap,
            'delivery_available' => ShopSettings::deliveryEnabled(),
            'pickup_available'   => ShopSettings::pickupEnabled() || !ShopSettings::deliveryEnabled(),
            'line_totals'        => $lineTotals,
        ];
    }

    /**
     * Text versions of a quote's amounts, for views and e-mails: subtotal_text, fee_text (empty when there is no fee), fee_label (what the "Delivery fee" row shows:
     * "No charge" for pick up, "Free" for delivery that costs nothing - because of the free-delivery limit OR because the fee is 0 - else the amount), total_text.
     * @return array<string,string>
     */
    public static function texts(array $quote): array
    {
        $cur = (string) $quote['currency'];
        return [
            'subtotal_text' => Money::format((int) $quote['subtotal'], $cur),
            'fee_text'      => (int) $quote['delivery_fee'] > 0 ? Money::format((int) $quote['delivery_fee'], $cur) : '',
            'fee_label'     => $quote['delivery_method'] === 'pickup' ? ShopSettings::registryText('cart.delivery_none')
                : ((int) $quote['delivery_fee'] > 0 ? Money::format((int) $quote['delivery_fee'], $cur) : ShopSettings::registryText('cart.delivery_free')),
            'total_text'    => Money::format((int) $quote['total'], $cur),
            'free_gap_text' => $quote['free_gap'] !== null ? Money::format((int) $quote['free_gap'], $cur) : '',
        ];
    }
}
