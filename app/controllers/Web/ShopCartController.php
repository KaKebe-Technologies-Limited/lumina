<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\CartService;

/**
 * CART: /shop/cart and its POST endpoints. OWNER: STOREFRONT agent (views in app/views/web/shop/). Contract: docs/SHOP.md.
 * Final implementation: the session is created only by add(); every check is CartService's. The cart page works without JavaScript; shop.js sends changes in the background.
 *
 * add():    product_id, qty, opt[<key>]=<value>, mode = cart | book. JSON when the request wants JSON (AJAX: {"ok","units","message","error"}), otherwise PRG:
 *           back to the product (error) / to the cart (ok) - or to the checkout for mode=book.
 * update(): qty[<lineKey>] = n (0 removes).   remove(): line = <lineKey>.   delivery(): delivery = pickup | delivery.
 */
final class ShopCartController extends Controller
{
    public function show(Request $request): Response
    {
        $summary = CartService::summary();
        return $this->renderPage('shop', 'web/shop/cart', ['cart' => $summary], [
            'bodyClass'   => 'is-shop-cart',
            'meta'        => ['robots' => 'noindex,follow', 'title' => strip_highlight((string) content('shop', 'cart.title'))],
            'breadcrumbs' => [[(string) content('shop', 'hero.eyebrow'), url('shop')], [strip_highlight((string) content('shop', 'cart.title')), null]],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function add(Request $request): Response
    {
        $mode = $request->string('mode') === 'book' ? 'book' : 'cart';
        $opt = $request->post('opt', []);
        $res = CartService::add($request->int('product_id'), is_array($opt) ? $opt : [], max(1, $request->int('qty', 1)), $mode);
        if ($request->expectsJson()) {
            $msg = $res['ok'] ? (string) content('shop', $res['clamped'] ? 'cart.clamped' : 'catalogue.added_live') : (string) content('shop', 'cart.err_' . $res['error']);
            $msg = str_replace('{n}', (string) $res['qty'], $msg);
            return $this->json(['ok' => $res['ok'], 'error' => $res['error'], 'errors' => $res['errors'], 'units' => CartService::units(), 'message' => $msg], $res['ok'] ? 200 : 422);
        }
        if (!$res['ok']) {
            Flash::error((string) content('shop', 'cart.err_' . $res['error'], (string) content('shop', 'cart.err_not_found')));
            return $this->back('shop');
        }
        if ($res['clamped']) {
            Flash::info(str_replace('{n}', (string) $res['qty'], (string) content('shop', 'cart.clamped')));
        }
        return $this->redirect($mode === 'book' ? 'shop/checkout' : 'shop/cart');
    }

    public function update(Request $request): Response
    {
        $qty = $request->post('qty', []);
        foreach (is_array($qty) ? $qty : [] as $key => $n) {
            if (is_string($key) && preg_match('/^[a-f0-9]{12}$/', $key) === 1 && is_scalar($n)) {
                CartService::setQty($key, (int) $n);
            }
        }
        return $request->expectsJson() ? $this->json(['ok' => true, 'units' => CartService::units()]) : $this->redirect('shop/cart');
    }

    public function remove(Request $request): Response
    {
        $key = $request->string('line');
        if (preg_match('/^[a-f0-9]{12}$/', $key) === 1) {
            CartService::remove($key);
        }
        return $request->expectsJson() ? $this->json(['ok' => true, 'units' => CartService::units()]) : $this->redirect('shop/cart');
    }

    public function delivery(Request $request): Response
    {
        CartService::setDelivery($request->string('delivery'));
        return $request->expectsJson() ? $this->json(['ok' => true]) : $this->redirect('shop/cart');
    }
}
