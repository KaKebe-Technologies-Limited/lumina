<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Shop\Money;

/**
 * The mobile-money payment page (MTN / Airtel through the Daraza API). The address /pay/mobile-money/{reference} is created by the page where the payer pressed
 * "Pay with mobile money" (the Academy waiting page, the certificates page, the Donate form); the reference is random, so only that payer knows it. The amount and what it is for
 * are read from the ledger row, never from the browser.
 *   GET  /pay/mobile-money/{reference}          the page: phone number, network, the Pay button, the waiting message
 *   POST /pay/mobile-money/{reference}/run      JSON: asks Daraza to collect and WAITS for the answer (up to 5 minutes)
 *   GET  /pay/mobile-money/{reference}/status   JSON: where the payment stands (the page asks every few seconds)
 */
final class DarazaPayController extends Controller
{
    private const NEXT = ['academy_signup' => 'academy/login', 'academy_cert' => 'academy/portal/certificates', 'donation' => 'donate'];

    /** @return array<string,mixed> */
    private function row(string $reference): array
    {
        DarazaPayments::install();
        $row = DarazaPayments::byReference($reference);
        if ($row === null || $row['direction'] !== 'in') {
            throw new \App\Core\HttpException(404, 'Not found');
        }
        return $row;
    }

    public function show(Request $request, string $reference): Response
    {
        $row = $this->row($reference);
        $what = DarazaPayments::PURPOSES[$row['purpose']] ?? 'Payment';
        $orig = (string) $row['currency'] !== 'UGX' ? Money::format((int) $row['amount_minor'], (string) $row['currency']) : '';
        return $this->render('web/pay/mobile-money', [
            'row' => $row, 'what' => $what, 'orig' => $orig, 'phone' => (string) ($row['phone'] ?? ''), 'ready' => DarazaApi::hasKey() && DarazaPayments::on((string) $row['purpose']),
            'next' => url(self::NEXT[$row['purpose']] ?? ''),
        ], ['title' => 'Pay with mobile money', 'meta' => ['robots' => 'noindex,nofollow']]);
    }

    public function run(Request $request, string $reference): Response
    {
        $row = $this->row($reference);
        if (!DarazaPayments::enabled((string) $row['purpose'])) {
            return Response::json(['state' => 'unavailable', 'message' => 'Mobile-money payment is not available right now. Please try another way to pay.'], 503);
        }
        $r = DarazaPayments::collect((string) $row['reference'], $request->string('phone'), $request->int('method'));
        return Response::json(['state' => $r['state'], 'message' => $r['message']]);
    }

    public function status(Request $request, string $reference): Response
    {
        $row = $this->row($reference);
        return Response::json(['state' => (string) $row['status'], 'message' => (string) ($row['message'] ?? '')]);
    }
}
