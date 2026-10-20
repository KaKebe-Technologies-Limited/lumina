<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\Money;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentService;

/**
 * ORDER PAGE: GET /shop/order/{token} (+ /receipt). OWNER: STOREFRONT agent (views order.php, receipt.php). Contract: docs/SHOP.md.
 * Final implementation. The token (40 hex = 160 bits) is the ONLY credential: an unknown or malformed token is a plain 404
 * (the route already throttles). The page is noindex + no-store, shows the customer's own order, never anything about other orders, never a secret.
 *
 * View data: order (row), items (rows), pay (OrderService::paymentInfo), totals texts, status labels, flash notes from the payment return.
 * Forms on the page post to: route('shop.pay') (Pay now / Pay again, a POST) and route('shop.order.reference') (field `reference`).
 */
final class ShopOrderController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        return $this->page($token, 'web/shop/order', false);
    }

    public function receipt(Request $request, string $token): Response
    {
        return $this->page($token, 'web/shop/receipt', true);
    }

    private function page(string $token, string $view, bool $print): Response
    {
        $order = OrderService::findByToken($token);
        if ($order === null) {
            $this->abort(404);
        }
        $cur = (string) $order['currency'];
        $items = OrderService::items((int) $order['id']);
        $text = static fn(mixed $v): string => Money::format(Money::fromDb($v), $cur);
        // render(), not renderPage(): a customer can ALWAYS reach their order and receipt (and the payment return, the e-mail links) even when the owner has
        // unpublished the Shop page. Only the catalogue, cart and checkout follow the publish switch (docs/SHOP.md section 8).
        return $this->render($view, [
            'order'  => $order,
            'items'  => $items,
            'pay'    => OrderService::paymentInfo($order),
            'text'   => ['subtotal' => $text($order['subtotal']), 'delivery' => $text($order['delivery_fee']), 'total' => $text($order['total']),
                'balance' => $text(Money::toDb(PaymentService::balanceMinor($order)))],
            'status' => OrderService::statusLabel((string) $order['status']),
            'paystatus' => OrderService::paymentStatusLabel((string) $order['payment_status']),
            'print'  => $print,
            'pageSlug' => 'shop',
        ], [
            'page'        => 'shop',
            'bodyClass'   => $print ? 'is-shop-receipt' : 'is-shop-order',
            'meta'        => ['robots' => 'noindex,nofollow', 'canonical' => '/shop', 'title' => ($print ? (string) content('shop', 'order.receipt_title') . ' ' : '') . $order['order_number']],
            'breadcrumbs' => [[(string) content('shop', 'hero.eyebrow'), url('shop')], [(string) $order['order_number'], null]],
        ])->header('Cache-Control', 'private, no-store');
    }
}
