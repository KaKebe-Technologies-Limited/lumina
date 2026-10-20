<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Log;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\CartService;
use App\Services\Shop\OnlinePayment;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\ShopSettings;

/**
 * CHECKOUT: GET/POST /shop/checkout. OWNER: STOREFRONT agent (view app/views/web/shop/checkout.php). Contract: docs/SHOP.md.
 * Final implementation (view app/views/web/shop/checkout.php). The route already has: page.published, honeypot (+ time trap), throttle 10 per 10 minutes,
 * stateless CSRF. The server NEVER trusts the browser: customer details are re-validated (OrderService::cleanCustomer), the payment method must be one of
 * PaymentMethods::available(), prices / totals / stock are recomputed inside OrderService::create() under row locks.
 *
 * Form fields: name, email, phone, delivery (pickup|delivery), address (when delivery), note, consent (1), payment (method key). Wording: content('shop','checkout.*').
 */
final class ShopCheckoutController extends Controller
{
    use GatewayRedirects;

    public function show(Request $request): Response
    {
        $cart = CartService::summary();
        if ($cart['lines'] === [] || CartService::purchasable($cart['lines']) === []) {
            return $this->redirect('shop/cart');
        }
        $methods = PaymentMethods::available($cart['all_bookable']);
        $prefer = $cart['prefer'];
        return $this->renderPage('shop', 'web/shop/checkout', [
            'cart'     => $cart,
            'methods'  => $methods,
            'selected' => $this->selected($methods, (string) old('payment', $prefer ?? '')),
            'pickup'   => ShopSettings::pickup(),
            'delivery' => $cart['delivery'],
            'open'     => ShopSettings::isOpen(),
        ], [
            'bodyClass'   => 'is-shop-checkout',
            'meta'        => ['robots' => 'noindex,follow', 'title' => strip_highlight((string) content('shop', 'checkout.title'))],
            'breadcrumbs' => [[(string) content('shop', 'hero.eyebrow'), url('shop')], [strip_highlight((string) content('shop', 'cart.title')), url('shop/cart')], [strip_highlight((string) content('shop', 'checkout.title')), null]],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function submit(Request $request): Response
    {
        $cart = CartService::summary();
        $buy = CartService::purchasable($cart['lines']);
        if ($buy === []) {
            Flash::error((string) content('shop', 'checkout.err_empty'));
            return $this->redirect('shop/cart');
        }
        if (!ShopSettings::isOpen()) {
            Flash::error((string) content('shop', 'checkout.err_closed'));
            return $this->redirect('shop/cart');
        }
        if ($cart['has_problems']) {
            Flash::error((string) content('shop', 'checkout.err_stock'));
            return $this->redirect('shop/cart');
        }
        $postedDelivery = $request->string('delivery');
        $delivery = in_array($postedDelivery, ShopSettings::deliveryMethods(), true) ? $postedDelivery : $cart['delivery'];
        $customer = [
            'name' => $request->string('name'), 'email' => $request->string('email'), 'phone' => $request->string('phone'),
            'address' => $request->string('address'), 'note' => $request->string('note'), 'consent' => $request->bool('consent'),
        ];
        $clean = OrderService::cleanCustomer($customer, $delivery);
        $errors = $this->messages($clean['errors']);
        if ($postedDelivery !== '' && !in_array($postedDelivery, ShopSettings::deliveryMethods(), true)) {
            $errors['delivery'] = (string) content('shop', 'checkout.err_delivery');          // the method was switched off after the form was opened: say so, never change it silently
        }
        $method = $request->string('payment');
        if (!PaymentMethods::isAvailable($method, $cart['all_bookable'])) {
            $errors['payment'] = (string) content('shop', 'checkout.err_payment');
        }
        if ($errors !== []) {
            $this->fail($errors, 'shop/checkout');
        }
        $res = OrderService::create($customer, $buy, $delivery, $method, ['ip_hash' => OrderService::ipHash($request->ip())]);
        if (!$res['ok']) {
            $code = (string) ($res['error'] ?? 'generic');
            Flash::error((string) content('shop', 'checkout.err_' . (in_array($code, ['stock', 'closed', 'empty', 'delivery', 'payment'], true) ? $code : 'generic'), (string) content('shop', 'checkout.err_generic')));
            return $this->redirect(in_array($code, ['stock', 'unavailable', 'options', 'qty', 'empty'], true) ? 'shop/cart' : 'shop/checkout');
        }
        $order = (array) $res['order'];
        CartService::clear();
        if ($method === 'flutterwave') {
            $r = OnlinePayment::begin($order);
            if ($r['ok']) {
                return $this->sendToGateway((string) $r['link']);                // off to the hosted payment page (an interstitial, not a 302: see GatewayRedirects); the order page offers "Pay again" if the customer comes back unpaid
            }
            Log::warning('Shop: the payment page could not be opened', ['order' => $order['order_number'], 'error' => (string) ($r['error'] ?? '')]);
        }
        return $this->redirect('shop/order/' . $order['token']);
    }

    /** @param list<array<string,mixed>> $methods */
    private function selected(array $methods, string $want): string
    {
        foreach ($methods as $m) {
            if ($m['key'] === $want) {
                return $want;
            }
        }
        return count($methods) === 1 ? (string) $methods[0]['key'] : '';
    }

    /** customer error codes -> the editable messages (placeholders {field}). @param array<string,string> $codes @return array<string,string> */
    private function messages(array $codes): array
    {
        $labels = ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'address' => 'address', 'note' => 'note', 'consent' => 'consent'];
        $out = [];
        foreach ($codes as $field => $code) {
            $label = strtolower((string) content('shop', 'checkout.' . ($labels[$field] ?? $field) . '_label', $field));
            $key = $code === 'consent' ? 'err_consent' : 'err_' . $code;
            $out[$field] = str_replace('{field}', $label, (string) content('shop', 'checkout.' . $key, (string) content('shop', 'checkout.err_invalid')));
        }
        return $out;
    }
}
