<?php
declare(strict_types=1);

namespace App\Services\Shop;

use App\Core\Config;
use App\Core\Log;
use App\Core\Settings;

/**
 * The ONLINE (Flutterwave) payment flow, as plain functions the controllers only call (so it can be tested without a browser):
 *
 *   begin($order)                  customer clicks "Pay now": new attempt (tx_ref) -> POST /v3/payments -> the hosted-page link to redirect to
 *   handleReturn($query)           the customer comes back to /shop/pay/return: the query string is NOT trusted, the payment is VERIFIED server-side
 *   handleWebhook($hash, $body)    POST /shop/webhook/flutterwave: authenticated by the verif-hash header, then VERIFIED server-side, idempotent
 *
 * Money is only ever marked paid by PaymentService::settleGateway(), which needs: verified status "successful", a tx_ref that belongs to the order,
 * amount >= order total and the same currency. A mismatch sets the order to `review`. Failed / abandoned payments leave the order unpaid with a
 * "Pay again" button (a new attempt = a new tx_ref).
 */
final class OnlinePayment
{
    /**
     * @param array<string,mixed> $order
     * @return array{ok:bool,link?:string,tx_ref?:string,error?:string}
     *   errors: unavailable (method off / not configured) | not_payable | too_many_attempts | already_paid | under_review | not_found | no_key | invalid_key | rejected | unavailable_gateway | bad_response
     */
    public static function begin(array $order): array
    {
        if (!PaymentMethods::enabled('flutterwave') || !PaymentMethods::ready('flutterwave')) {
            return ['ok' => false, 'error' => 'unavailable'];
        }
        $fresh = OrderService::find((int) $order['id']);
        if ($fresh === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if ($fresh['payment_status'] === 'pending' && PaymentService::openGatewayRefs((int) $fresh['id']) !== []) {
            // an earlier try is still open: ask the gateway BEFORE opening another payable link, so a customer who already paid (a slow mobile-money confirmation, a
            // webhook that has not arrived yet) is never invited to pay twice. If the gateway cannot be reached we go on: the later settlement flags any double payment.
            $chk = self::recheck((int) $fresh['id']);
            if (in_array($chk['state'], ['paid', 'duplicate'], true)) {
                return ['ok' => false, 'error' => 'already_paid'];
            }
            if ($chk['state'] === 'review') {
                return ['ok' => false, 'error' => 'under_review'];
            }
            $fresh = OrderService::find((int) $fresh['id']) ?? $fresh;
        }
        $start = PaymentService::startAttempt((int) $fresh['id']);
        if (!$start['ok']) {
            return ['ok' => false, 'error' => (string) $start['error']];
        }
        $title = ShopSettings::str('shop_flw_title');
        $site = (string) Settings::get('site_name', 'Lumina Africa Foundation');
        $logo = '';
        try {
            $logo = abs_url_for_image((string) Settings::get('logo_path', 'assets/img/brand/logo-512.png'));
        } catch (\Throwable) {
        }
        $r = FlutterwaveClient::createPayment([
            'tx_ref'       => (string) $start['tx_ref'],
            'amount_minor' => (int) $start['amount_minor'],
            'currency'     => (string) $start['currency'],
            'redirect_url' => self::returnUrl((string) $start['tx_ref']),
            'customer'     => ['name' => (string) $fresh['customer_name'], 'email' => (string) $fresh['customer_email'], 'phone' => (string) $fresh['customer_phone']],
            'title'        => $title !== '' ? $title : $site,
            'description'  => trim(str_replace('{order_number}', (string) $fresh['order_number'], ShopSettings::registryText('payment.flw_description'))) ?: 'Order ' . $fresh['order_number'],
            'logo'         => $logo,
            'options'      => PaymentMethods::flwOptions(),
            'meta'         => ['order_number' => (string) $fresh['order_number']],
        ]);
        if (!$r['ok']) {
            PaymentService::recordEvent('create_failed', (string) $start['tx_ref'], (int) $fresh['id'], (string) ($r['error'] ?? 'error'));
            PaymentService::markFailed((int) $fresh['id'], (string) $start['tx_ref'], 'The payment page could not be opened.', 'gateway');
            return ['ok' => false, 'error' => ($r['error'] ?? '') === 'unavailable' ? 'unavailable_gateway' : (string) ($r['error'] ?? 'bad_response')];
        }
        return ['ok' => true, 'link' => (string) $r['link'], 'tx_ref' => (string) $start['tx_ref']];
    }

    /**
     * The key that BINDS the return URL to the customer who started the payment. The order number is not a secret (it is on receipts, in e-mails and in the
     * gateway dashboard), so it must never be enough to be taken to the private order page: the redirect_url we give the gateway carries this keyed hash of the
     * tx_ref, which only our server can compute. 32 hex characters; a path segment, so the gateway can append its own query string.
     */
    public static function returnKey(string $txRef): string
    {
        return substr(hash_hmac('sha256', 'shop-return|' . $txRef, Config::appKey()), 0, 32);
    }

    public static function returnUrl(string $txRef): string
    {
        return abs_url('shop/pay/return/' . self::returnKey($txRef));
    }

    /**
     * The customer is back from the hosted page. Only tx_ref is read from the query (format-checked); status / transaction_id are ignored for the decision
     * (status only picks the wording when the gateway has not decided yet).
     *
     * The return is acted on ONLY when $rid is the key of that tx_ref (see returnKey()). Without it (an old link, a guessed order number, a bot) nothing is looked
     * up and nothing is sent to the gateway: the answer is the same for a real and an invented reference, so the page cannot be used to find order numbers or
     * to reach an order page. The payment is then settled by the webhook, the cron re-check or the customer's own order link.
     * @param array<string,mixed> $query
     * @return array{state:string,order:?array<string,mixed>}  state: paid | duplicate | pending | cancelled | failed | review | unknown_order | verify_failed | unbound
     */
    public static function handleReturn(array $query, string $rid = ''): array
    {
        $txRef = is_string($query['tx_ref'] ?? null) ? trim((string) $query['tx_ref']) : '';
        if (preg_match('/^[A-Za-z0-9._-]{3,120}$/', $txRef) !== 1) {
            return ['state' => 'unknown_order', 'order' => null];
        }
        if (preg_match('/^[a-f0-9]{32}$/', $rid) !== 1 || !hash_equals(self::returnKey($txRef), $rid)) {
            return ['state' => 'unbound', 'order' => null];
        }
        $order = PaymentService::orderForTxRef($txRef);
        if ($order === null) {
            PaymentService::recordEvent('return_unknown', mb_substr($txRef, 0, 120), null, 'ignored');
            return ['state' => 'unknown_order', 'order' => null];
        }
        $v = FlutterwaveClient::verifyByReference($txRef);
        $res = PaymentService::settleGateway($txRef, $v, 'gateway');
        $state = (string) $res['state'];
        if ($state === 'pending' && strtolower((string) ($query['status'] ?? '')) === 'cancelled') {
            $state = 'cancelled';
        }
        return ['state' => $state, 'order' => $res['order'] ?? $order];
    }

    /** How the states of several checked attempts are combined: the first one in this list wins (money received beats everything else). */
    private const STATE_PRIORITY = ['paid', 'review', 'duplicate', 'pending', 'failed', 'superseded', 'verify_failed', 'unknown_order'];

    /**
     * Staff "check this payment with Flutterwave again" (e.g. the webhook never arrived and the customer never came back): verifies EVERY try of the order that
     * may still turn into money (all pending gateway ledger rows, not only the latest tx_ref: the customer may have paid on an earlier try and then pressed
     * "Pay again"). Works on a cancelled order too: money that arrives for a closed order is flagged `review`, never hidden.
     * @return array{state:string,order:?array<string,mixed>}  same states as handleReturn(); no_attempt when the order never started an online payment
     */
    public static function recheck(int $orderId): array
    {
        $order = OrderService::find($orderId);
        if ($order === null || (string) ($order['gateway_tx_ref'] ?? '') === '') {
            return ['state' => 'no_attempt', 'order' => $order];
        }
        $states = [];
        $refs = PaymentService::openGatewayRefs($orderId);
        if ($refs === []) {
            return ['state' => $order['payment_status'] === 'paid' ? 'paid' : 'no_attempt', 'order' => $order];
        }
        foreach ($refs as $txRef) {
            $res = PaymentService::settleGateway($txRef, FlutterwaveClient::verifyByReference($txRef), 'gateway');
            $states[] = (string) $res['state'];
        }
        $state = 'verify_failed';
        foreach (self::STATE_PRIORITY as $s) {
            if (in_array($s, $states, true)) {
                $state = $s;
                break;
            }
        }
        return ['state' => $state, 'order' => OrderService::find($orderId) ?? $order];
    }

    /** True when the order has a gateway try that may still turn into money (so cancelling it blindly could hide a payment). */
    public static function hasOpenAttempt(int $orderId): bool
    {
        return PaymentService::openGatewayRefs($orderId) !== [];
    }

    /**
     * @return array{http:int,state:string}  http 401 = not authentic (nothing processed); 200 = authentic: processed or deliberately ignored, never an error
     */
    public static function handleWebhook(?string $hashHeader, string $rawBody): array
    {
        if (!FlutterwaveClient::webhookAuthentic($hashHeader)) {
            PaymentService::recordEvent('webhook_rejected', null, null, 'rejected');
            return ['http' => 401, 'state' => 'rejected'];
        }
        if (strlen($rawBody) > 262144) {
            return ['http' => 200, 'state' => 'ignored'];
        }
        $txRef = FlutterwaveClient::webhookTxRef(json_decode($rawBody, true, 16));
        if ($txRef === null) {
            PaymentService::recordEvent('webhook_ok', null, null, 'no_tx_ref');
            return ['http' => 200, 'state' => 'ignored'];
        }
        try {
            $v = FlutterwaveClient::verifyByReference($txRef);
            $res = PaymentService::settleGateway($txRef, $v, 'webhook');
            return ['http' => 200, 'state' => (string) $res['state']];
        } catch (\Throwable $e) {
            Log::error('Shop webhook processing failed', ['error' => $e::class]);
            return ['http' => 200, 'state' => 'error'];                // authentic but we failed: the return page / a staff check still settles it; never ask for retries storms
        }
    }
}
