<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Flash;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Services\DarazaPay\DarazaApi;
use App\Services\DarazaPay\DarazaPayments;
use App\Services\Payments\PaymentEvents;
use App\Services\Payments\PaymentsDashboard;

/**
 * Admin > Payments: ONE dashboard for every payment on the website (mobile money, Pesapal / Flutterwave, bank, cash and manual), with filters, a CSV download and "Send money"
 * to any MTN or Airtel number (a payout from the Daraza app wallet). Needs settings.manage (administrators).
 */
final class PaymentsController extends AdminController
{
    private function filters(Request $request): array
    {
        $range = $request->string('range');
        return [
            'range' => isset(PaymentsDashboard::RANGES[$range]) ? $range : '30',
            'channel' => $request->string('channel'),
            'category' => $request->string('category'),
            'dir' => $request->string('dir'),
            'status' => $request->string('status'),
            'q' => trim($request->string('q')),
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $ready = DarazaPayments::install();
        $f = $this->filters($request);
        $sum = PaymentsDashboard::summary($f['range']);
        $total = $ready ? PaymentsDashboard::rows($f, 1, 0)['total'] : 0;
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $rows = $ready ? PaymentsDashboard::rows($f, $pagination->perPage, $pagination->offset)['rows'] : [];
        $to = DarazaApi::e164($request->string('to'));
        return $this->render('admin/payments/index', [
            'f' => $f, 'sum' => $sum, 'rows' => $rows, 'pagination' => $pagination, 'ready' => $ready, 'hasKey' => DarazaApi::hasKey(), 'mode' => DarazaApi::mode(),
            'recipients' => PaymentsDashboard::recentRecipients(), 'prefill' => ['phone' => $to !== null ? '0' . substr($to, 4) : '', 'method' => $request->int('network')],
        ], ['title' => 'Payments', 'nav' => 'payments', 'breadcrumbs' => [['Payments', null]]]);
    }

    /** The AUDIT TRAIL of every payment step: attempts, successful and unsuccessful. */
    public function trail(Request $request): Response
    {
        $this->authorize('settings.manage');
        PaymentEvents::install();
        $range = $request->string('range');
        $f = ['range' => isset(PaymentsDashboard::RANGES[$range]) ? $range : '30', 'group' => $request->string('group'), 'category' => $request->string('category'), 'channel' => $request->string('channel'), 'q' => trim($request->string('q'))];
        $total = PaymentEvents::rows($f, 1, 0)['total'];
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        return $this->render('admin/payments/audit', [
            'f' => $f, 'rows' => PaymentEvents::rows($f, $pagination->perPage, $pagination->offset)['rows'], 'pagination' => $pagination, 'counts' => PaymentEvents::counts($f['range']),
        ], ['title' => 'Payment audit trail', 'nav' => 'payments', 'breadcrumbs' => [['Payments', url('admin/payments')], ['Audit trail', null]]]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('settings.manage');
        DarazaPayments::install();
        $f = $this->filters($request);
        $data = PaymentsDashboard::rows($f, 500, 0)['rows'];
        $out = [];
        foreach ($data as $r) {
            $out[] = [$r['at'], $r['dir'] === 'out' ? 'Money out' : 'Money in', $r['what'], $r['who'], $r['detail'], number_format(((int) $r['amt']) / 100, 2, '.', ''), $r['cur'], $r['status'], PaymentsDashboard::CHANNELS[$r['channel']] ?? $r['channel'], $r['ref']];
        }
        $this->audit('payments.export', 'payments', null, 'Payments downloaded (' . count($out) . ' rows)');
        return Response::csv('payments-' . date('Y-m-d') . '.csv', ['When', 'Direction', 'What', 'Who', 'Detail', 'Amount', 'Currency', 'Status', 'Channel', 'Reference'], $out);
    }

    public function balance(Request $request): Response
    {
        $this->authorize('settings.manage');
        $b = DarazaApi::balance();
        $b['ok'] ? Flash::success('Daraza wallet balance (' . DarazaApi::mode() . '): ' . $b['balance'] . '.') : Flash::error('Could not read the wallet balance: ' . $b['error']);
        return $this->redirect('admin/payments');
    }

    public function check(Request $request, int|string $id): Response
    {
        $this->authorize('settings.manage');
        $r = DarazaPayments::check((int) $id);
        $r['state'] === 'paid' ? Flash::success($r['message']) : ($r['state'] === 'failed' ? Flash::error($r['message']) : Flash::info($r['message']));
        return $this->redirect('admin/payments');
    }

    public function payout(Request $request): Response
    {
        $this->authorize('settings.manage');
        if ($request->string('confirm') !== '1') {
            Flash::error('Tick the box to confirm that you want to send this money.');
            return $this->redirect('admin/payments');
        }
        $r = DarazaPayments::payout($this->userId(), $request->string('phone'), $request->int('method'), $request->int('amount'), $request->string('note'));
        $r['ok'] ? Flash::success($r['message']) : Flash::error($r['message']);
        return $this->redirect('admin/payments');
    }
}
