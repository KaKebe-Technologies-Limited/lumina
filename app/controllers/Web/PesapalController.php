<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Pesapal\PesapalPayments;

/**
 * Where PESAPAL talks to the site.
 *
 *   GET /pay/pesapal/return   the customer's browser is sent back here (OrderTrackingId, OrderMerchantReference in the query). NOTHING in the query is believed:
 *                             the payment is asked of Pesapal (PesapalPayments::settle) and the page says what Pesapal answered.
 *   GET /pay/pesapal/ipn      Pesapal's server-to-server notification (also accepted as POST). Same settle(); the reply is the JSON Pesapal requires.
 *
 * Both are idempotent: whichever arrives first records the payment, the other is a harmless repeat.
 */
final class PesapalController extends Controller
{
    public function returned(Request $request): Response
    {
        $tracking = is_string($request->query('OrderTrackingId')) ? trim((string) $request->query('OrderTrackingId')) : '';
        $res = PesapalPayments::settle($tracking);
        $row = $res['row'];
        $state = $res['state'];
        if ($row !== null && $row['purpose'] === 'shop') {
            $order = PesapalPayments::shopOrder($row);
            if ($order !== null) {
                if ($state === 'paid') {
                    Flash::success((string) content('shop', 'order.return_success'));
                } elseif ($state === 'review') {
                    Flash::info((string) content('shop', 'order.review_note'));
                } elseif ($state === 'failed') {
                    Flash::error((string) content('shop', 'order.return_failed'));
                }
                return $this->redirect('shop/order/' . $order['token']);
            }
        }
        return $this->render('web/pay/pesapal-result', ['state' => $state, 'row' => $row], [
            'page' => 'donate',
            'meta' => ['robots' => 'noindex,nofollow', 'title' => 'Your gift'],
        ])->header('Cache-Control', 'private, no-store');
    }

    public function ipn(Request $request): Response
    {
        $tracking = (string) ($request->query('OrderTrackingId') ?? $request->input('OrderTrackingId') ?? '');
        $reference = (string) ($request->query('OrderMerchantReference') ?? $request->input('OrderMerchantReference') ?? '');
        $type = (string) ($request->query('OrderNotificationType') ?? $request->input('OrderNotificationType') ?? 'IPNCHANGE');
        $res = PesapalPayments::settle(trim($tracking));
        $ok = $res['state'] !== 'unknown';
        return Response::json([
            'orderNotificationType'  => preg_match('/^[A-Z]{3,20}$/', $type) === 1 ? $type : 'IPNCHANGE',
            'orderTrackingId'        => preg_match('/^[A-Za-z0-9-]{8,80}$/', $tracking) === 1 ? $tracking : '',
            'orderMerchantReference' => preg_match('/^[A-Za-z0-9-]{4,60}$/', $reference) === 1 ? $reference : '',
            'status'                 => $ok ? 200 : 500,
        ], $ok ? 200 : 500)->header('Cache-Control', 'no-store');
    }
}
