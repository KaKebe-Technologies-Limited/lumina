<?php
/**
 * PUBLIC SHOP routes. Contract: docs/SHOP.md section 8. $router (App\Core\Router) is in scope.
 * Owners: STOREFRONT agent = ShopController, ShopCartController, ShopCheckoutController, ShopOrderController;
 *         PAYMENTS agent   = ShopPaymentController (start / reference / return / webhook).
 *
 *   GET  /shop                             the catalogue (?category=slug &sort=featured|newest|price_asc|price_desc|name &q= &page=)
 *   GET  /shop/category/{slug}             the catalogue limited to one category (same view)
 *   GET  /shop/terms                       terms of sale, delivery and returns (the checkout consent box links here)
 *   GET  /shop/cart                        the cart
 *   POST /shop/cart/add                    product_id, qty, opt[<key>]=<value>, mode=cart|book   (creates the session on the FIRST item only)
 *   POST /shop/cart/update                 qty[<lineKey>]=n (0 removes)    POST /shop/cart/remove (line)    POST /shop/cart/delivery (delivery=pickup|delivery)
 *   GET  /shop/checkout                    checkout form + summary         POST /shop/checkout   (honeypot + time trap + throttle + stateless CSRF)
 *   GET  /shop/order/{token}               the order page / confirmation (noindex, no-store; token = 40 hex)
 *   GET  /shop/order/{token}/receipt       printable receipt
 *   POST /shop/order/{token}/reference     the customer enters the mobile-money transaction ID / bank reference (momo / bank orders)
 *   POST /shop/pay/{token}                 start (or restart) the Flutterwave payment of the order -> redirect to the hosted page
 *   GET  /shop/pay/{token}                 no side effect: redirects to the order page, where the "Pay now" button (a POST) is
 *   GET  /shop/pay/return/{rid}            where Flutterwave sends the customer back (query: status, tx_ref, transaction_id): NEVER trusted, the payment is verified server-side;
 *                                          {rid} = OnlinePayment::returnKey(tx_ref), the keyed proof that this browser started the payment
 *   GET  /shop/pay/return                  no key: a neutral thank-you page, identical for every reference (no lookup, no redirect, no gateway call)
 *   POST /shop/webhook/flutterwave         Flutterwave webhook (header verif-hash): CSRF-exempt, authenticated by the secret hash, always 200 once authentic
 *   GET  /shop/{slug}                      one product
 *
 * Static segments (cart, checkout, order, pay, webhook, category) are matched before /shop/{slug}; ProductService::RESERVED_SLUGS keeps product slugs away from them.
 * The catalogue, product, cart and checkout pages need the Shop page to be published; the order pages and the payment endpoints work even if it is not,
 * so a customer can always reach an order they placed.
 */

use App\Controllers\Web\ShopCartController;
use App\Controllers\Web\ShopCheckoutController;
use App\Controllers\Web\ShopController;
use App\Controllers\Web\ShopOrderController;
use App\Controllers\Web\ShopPaymentController;

$router->get('/shop', [ShopController::class, 'index'])->name('shop')->middleware('page.published:shop');
$router->get('/shop/category/{slug}', [ShopController::class, 'category'])->name('shop.category')->middleware('page.published:shop');

$router->get('/shop/terms', [ShopController::class, 'terms'])->name('shop.terms')->middleware('page.published:shop');
$router->get('/shop/cart', [ShopCartController::class, 'show'])->name('shop.cart')->middleware('page.published:shop');
$router->post('/shop/cart/add', [ShopCartController::class, 'add'])->name('shop.cart.add')->middleware(['page.published:shop', 'throttle:60,60']);
$router->post('/shop/cart/update', [ShopCartController::class, 'update'])->name('shop.cart.update')->middleware(['page.published:shop', 'throttle:60,60']);
$router->post('/shop/cart/remove', [ShopCartController::class, 'remove'])->name('shop.cart.remove')->middleware(['page.published:shop', 'throttle:60,60']);
$router->post('/shop/cart/delivery', [ShopCartController::class, 'delivery'])->name('shop.cart.delivery')->middleware(['page.published:shop', 'throttle:60,60']);

$router->get('/shop/checkout', [ShopCheckoutController::class, 'show'])->name('shop.checkout')->middleware('page.published:shop');
$router->post('/shop/checkout', [ShopCheckoutController::class, 'submit'])->name('shop.checkout.submit')->middleware(['page.published:shop', 'honeypot', 'throttle:10,600']);

$router->get('/shop/order/{token}', [ShopOrderController::class, 'show'])->name('shop.order')->where('token', '[a-f0-9]{40}')->middleware('throttle:60,600');
$router->get('/shop/order/{token}/receipt', [ShopOrderController::class, 'receipt'])->name('shop.order.receipt')->where('token', '[a-f0-9]{40}')->middleware('throttle:60,600');
$router->post('/shop/order/{token}/reference', [ShopPaymentController::class, 'reference'])->name('shop.order.reference')->where('token', '[a-f0-9]{40}')->middleware(['honeypot', 'throttle:10,600']);

$router->get('/shop/pay/return', [ShopPaymentController::class, 'return'])->name('shop.pay.return')->middleware('throttle:60,600');
$router->get('/shop/pay/return/{rid}', [ShopPaymentController::class, 'return'])->name('shop.pay.return.key')->where('rid', '[a-f0-9]{32}')->middleware('throttle:60,600');
$router->post('/shop/pay/{token}', [ShopPaymentController::class, 'start'])->name('shop.pay')->where('token', '[a-f0-9]{40}')->middleware('throttle:10,600');
$router->get('/shop/pay/{token}', [ShopPaymentController::class, 'startGet'])->name('shop.pay.get')->where('token', '[a-f0-9]{40}')->middleware('throttle:30,600');
$router->post('/shop/webhook/flutterwave', [ShopPaymentController::class, 'webhook'])->name('shop.webhook.flutterwave')->middleware(['csrf.exempt', 'throttle:240,60']);

$router->get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.product')->middleware('page.published:shop');
