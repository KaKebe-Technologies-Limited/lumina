<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalPayments;

/**
 * Admin > Pesapal payments: is the gateway connected, test the keys, and the list of every Pesapal payment (donations and shop orders) with a
 * "Check with Pesapal" button for one that is still waiting. The keys themselves are never shown or stored here: they live in the server .env.
 */
final class PesapalController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        return $this->render('admin/pesapal/index', [
            'hasKeys'   => PesapalClient::hasKeys(),
            'mode'      => PesapalClient::mode(),
            'payments'  => PesapalPayments::recent(100),
            'ipnUrl'    => abs_url('pay/pesapal/ipn'),
            'returnUrl' => abs_url('pay/pesapal/return'),
        ], ['title' => 'Pesapal payments', 'nav' => 'pesapal', 'breadcrumbs' => [['Pesapal payments', null]]]);
    }

    public function test(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $r = PesapalClient::testConnection();
        if ($r['ok']) {
            Flash::success($r['message']);
        } else {
            Flash::error($r['message']);
        }
        return $this->redirect('admin/pesapal');
    }

    public function check(Request $request, string $id): Response
    {
        $this->authorize('inbox.manage');
        $row = Database::one('SELECT * FROM pesapal_payments WHERE id = ?', [(int) $id]);
        if ($row === null || (string) ($row['order_tracking_id'] ?? '') === '') {
            Flash::error('That payment never reached the Pesapal page, so there is nothing to check.');
            return $this->redirect('admin/pesapal');
        }
        $res = PesapalPayments::settle((string) $row['order_tracking_id']);
        AuditLog::record('pesapal.check', 'pesapal_payment', (string) $row['id'], 'Checked ' . $row['merchant_reference'] . ' with Pesapal: ' . $res['state']);
        $msg = [
            'paid' => 'Pesapal confirms this payment: it is recorded as paid.', 'pending' => 'Pesapal says it is still waiting for the customer to pay.',
            'failed' => 'Pesapal says the payment failed.', 'reversed' => 'Pesapal says the payment was reversed.', 'review' => 'The payment needs a manual look: its amount or currency does not match.',
            'verify_failed' => 'Pesapal could not be reached. Try again in a minute.', 'unknown' => 'Pesapal does not know this payment.',
        ];
        Flash::info($msg[$res['state']] ?? 'Checked.');
        return $this->redirect('admin/pesapal');
    }
}
