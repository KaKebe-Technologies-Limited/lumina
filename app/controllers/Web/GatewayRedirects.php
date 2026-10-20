<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Response;

/**
 * Sends the customer to the hosted payment page WITHOUT changing the Content-Security-Policy.
 *
 * Why not a plain 302: both entry points (the checkout form and the "Pay now" button) are form POSTs, and the policy says `form-action 'self'`.
 * Chromium browsers (Chrome, Edge) apply form-action to the redirect that follows a form post, so a 302 to checkout.flutterwave.com is BLOCKED there.
 * A 200 page with a meta refresh and a visible button is an ordinary navigation, which the policy does not restrict, and it works in every browser.
 * The link has already been checked by OnlinePayment (https on flutterwave.com, or the local mock while APP_ENV=local).
 */
trait GatewayRedirects
{
    protected function sendToGateway(string $link): Response
    {
        return $this->render('web/shop/redirecting', ['link' => $link], [
            'page'      => 'shop',
            'bodyClass' => 'is-shop-redirecting',
            'meta'      => ['robots' => 'noindex,nofollow', 'title' => (string) content('shop', 'payment.redirect_title')],
        ])->header('Cache-Control', 'private, no-store');
    }

    /** The neutral page after the hosted payment page when the return cannot be tied to the customer's own payment: identical for every reference, no order data, never cached. */
    protected function returnThanks(): Response
    {
        return $this->render('web/shop/return', [], [
            'page'      => 'shop',
            'bodyClass' => 'is-shop-redirecting',
            'meta'      => ['robots' => 'noindex,nofollow', 'canonical' => '/shop', 'title' => (string) content('shop', 'payment.return_title')],
        ])->header('Cache-Control', 'private, no-store');
    }
}
