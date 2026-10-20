<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Shop\OnlinePayment;
use App\Services\Shop\OrderService;
use App\Services\Shop\PaymentService;

/**
 * PAYMENT endpoints of the public shop. OWNER: PAYMENTS agent. Contract: docs/SHOP.md section 9. The logic lives in OnlinePayment / PaymentService (tested
 * without a browser); this class only turns results into responses.
 * Final implementation (works against the local mock; verified in bin/selftest-shop.php and the browser runs of docs/SHOP.md section 16).
 *
 *   start      POST /shop/pay/{token}              new Flutterwave attempt -> a short "taking you to the payment page" page with a meta refresh to the hosted page (or back to the order page with an error note)
 *   startGet   GET  /shop/pay/{token}              no side effect: 302 to the order page
 *   reference  POST /shop/order/{token}/reference  the customer's transaction ID / transfer reference (field `reference`) -> PaymentService::recordCustomerReference
 *   return     GET  /shop/pay/return/{rid}         verify with Flutterwave, then 302 to the order page with a flash note. NEVER trusts the query string; {rid} = OnlinePayment::returnKey(tx_ref),
 *                                                  the keyed proof that this browser started the payment (the order number alone is NOT a credential)
 *              GET  /shop/pay/return               no key: the same neutral "thank you" page for every reference (no lookup, no redirect, no gateway call, no cookie)
 *   webhook    POST /shop/webhook/flutterwave      header verif-hash must equal the secret hash (401 otherwise); then verify + settle; 200 always once authentic
 */
final class ShopPaymentController extends Controller
{
    use GatewayRedirects;

    public function start(Request $request, string $token): Response
    {
        $order = OrderService::findByToken($token);
        if ($order === null) {
            $this->abort(404);
        }
        $r = OnlinePayment::begin($order);
        if ($r['ok']) {
            return $this->sendToGateway((string) $r['link']);        // an interstitial, not a 302: the CSP form-action would block a redirect after a POST
        }
        // say what is really the matter: an order that is already paid must not be told "the payment did not go through"
        switch ((string) ($r['error'] ?? '')) {
            case 'already_paid':
                Flash::success((string) content('shop', 'order.return_success'));
                break;
            case 'under_review':
                Flash::info((string) content('shop', 'order.review_note'));
                break;
            case 'not_payable':
                if ($order['payment_status'] === 'paid') {
                    Flash::success((string) content('shop', 'order.return_success'));
                } else {
                    Flash::info((string) content('shop', 'order.pay_closed_note'));
                }
                break;
            case 'too_many_attempts':
                Flash::error((string) content('shop', 'order.pay_limit_note'));
                break;
            case 'unavailable':
                Flash::error((string) content('shop', 'order.pay_unavailable_note'));
                break;
            default:
                Flash::error((string) content('shop', 'order.failed_note'));
        }
        return $this->redirect('shop/order/' . $order['token']);
    }

    public function startGet(Request $request, string $token): Response
    {
        return $this->redirect('shop/order/' . $token);
    }

    public function reference(Request $request, string $token): Response
    {
        $order = OrderService::findByToken($token);
        if ($order === null) {
            $this->abort(404);
        }
        $r = PaymentService::recordCustomerReference((int) $order['id'], $request->string('reference'));
        if ($r['ok']) {
            Flash::success((string) content('shop', 'order.ref_thanks'));
        } else {
            Flash::error((string) content('shop', 'order.ref_error'));
        }
        return $this->redirect('shop/order/' . $order['token']);
    }

    public function return(Request $request, string $rid = ''): Response
    {
        $res = OnlinePayment::handleReturn($request->query(), $rid);
        $order = $res['order'];
        if ($order === null) {
            return $this->returnThanks();                    // the same page for a real, an invented and an unbound reference: it must not tell them apart (and sets no cookie)
        }
        $note = match ($res['state']) {
            'paid', 'duplicate' => ['success', 'return_success'],
            'cancelled'         => ['info', 'return_cancelled'],
            'failed'            => ['error', 'return_failed'],
            default             => ['info', 'return_pending'],
        };
        Flash::add($note[0], (string) content('shop', 'order.' . $note[1]));
        return $this->redirect('shop/order/' . $order['token'])->header('Cache-Control', 'private, no-store');
    }

    public function webhook(Request $request): Response
    {
        $raw = (string) @file_get_contents('php://input');
        $res = OnlinePayment::handleWebhook($request->header('verif-hash'), $raw);
        return Response::text($res['http'] === 200 ? 'ok' : 'unauthorized', $res['http']);
    }
}
