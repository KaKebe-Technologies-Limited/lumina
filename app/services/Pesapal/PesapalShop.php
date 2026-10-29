<?php
declare(strict_types=1);

namespace App\Services\Pesapal;

use App\Core\Database;
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;

/**
 * The shop's "pay with Pesapal" button. Thin on purpose: the order is the shop's, the payment is PesapalPayments'.
 *
 *   begin($order)   customer pressed "Pay now" (checkout, or later from the order page): opens a Pesapal payment for the order's total and returns the hosted page link.
 *                   An earlier unfinished try is asked about FIRST, so a customer who already paid is never invited to pay twice.
 */
final class PesapalShop
{
    /**
     * @param array<string,mixed> $order
     * @return array{ok:bool,link?:string,error?:string}  errors: unavailable | not_found | not_payable | already_paid | under_review | too_many_attempts | the client's codes
     */
    public static function begin(array $order): array
    {
        if (!PaymentMethods::enabled('pesapal') || !PaymentMethods::ready('pesapal')) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        $fresh = OrderService::find((int) $order['id']);
        if ($fresh === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if ($fresh['payment_method'] !== 'pesapal' || $fresh['status'] !== 'pending' || !in_array($fresh['payment_status'], ['unpaid', 'pending', 'failed'], true)) {
            return ['ok' => false, 'error' => $fresh['payment_status'] === 'paid' ? 'already_paid' : ($fresh['payment_status'] === 'review' ? 'under_review' : 'not_payable')];
        }
        $open = Database::all("SELECT order_tracking_id FROM pesapal_payments WHERE purpose = 'shop' AND ref_id = ? AND status = 'pending' AND order_tracking_id IS NOT NULL", [(int) $fresh['id']]);
        foreach ($open as $o) {
            $res = PesapalPayments::settle((string) $o['order_tracking_id']);
            if ($res['state'] === 'paid') {
                return ['ok' => false, 'error' => 'already_paid'];
            }
            if ($res['state'] === 'review') {
                return ['ok' => false, 'error' => 'under_review'];
            }
        }
        if ((int) Database::value("SELECT COUNT(*) FROM pesapal_payments WHERE purpose = 'shop' AND ref_id = ?", [(int) $fresh['id']], 0) >= 8) {
            return ['ok' => false, 'error' => 'too_many_attempts'];
        }
        Database::execute("UPDATE shop_orders SET payment_status = 'pending', hold_until = ? WHERE id = ? AND payment_status IN ('unpaid','failed')", [OrderService::holdUntil('pesapal'), (int) $fresh['id']]);
        return PesapalPayments::begin(
            'shop',
            (int) $fresh['id'],
            Money::fromDb($fresh['total']),
            (string) $fresh['currency'],
            'Order ' . $fresh['order_number'],
            ['name' => (string) $fresh['customer_name'], 'email' => (string) $fresh['customer_email'], 'phone' => (string) $fresh['customer_phone']]
        );
    }
}
