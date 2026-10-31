<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\CsvExport;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\AcademyFees;
use App\Services\Academy\PaymentsReport;

/**
 * Admin > LAF Academy > Payments: sign-up and certificate fees as they come in (Pesapal and payments confirmed by hand), what is reported and waiting for you, what is still
 * owed, and a daily chart. The page refreshes itself every 30 seconds. Routes: GET admin/academy/payments, GET admin/academy/payments-export. Needs academy.manage.
 */
final class AcademyPaymentsController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $f = PaymentsReport::filters($request->string('period'), $request->string('type'), $request->string('status'));
        $events = PaymentsReport::events($f, 150);
        return $this->render('admin/academy/payments', [
            'f' => $f,
            'periods' => PaymentsReport::PERIODS,
            'types' => PaymentsReport::TYPES,
            'statuses' => PaymentsReport::STATUSES,
            'events' => $events,
            'summary' => PaymentsReport::summary($events),
            'daily' => PaymentsReport::daily($events, $f),
            'ready' => AcademyFees::ready() && (AcademyFees::configured('signup') !== null || AcademyFees::configured('cert') !== null),
            'signupFee' => AcademyFees::configured('signup'),
            'certFee' => AcademyFees::configured('cert'),
        ], ['title' => 'Academy payments', 'nav' => 'academy_payments', 'breadcrumbs' => [['LAF Academy', url('admin/academy')], ['Payments', null]]]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        $f = PaymentsReport::filters($request->string('period'), $request->string('type'), $request->string('status'));
        $events = PaymentsReport::events($f, 5000);
        $this->audit('academy.payments.export', 'academy_payment', null, 'Downloaded the Academy payments list', ['rows' => count($events)]);
        return CsvExport::response('academy-payments-' . date('Y-m-d') . '.csv', ['Time', 'Learner', 'Fee', 'Certificate for', 'Status', 'Amount', 'Currency', 'Amount is an estimate', 'Method', 'Reference'],
            array_map(static fn(array $e): array => [$e['at'], $e['learner'], $e['type'] === 'cert' ? 'Certificate fee' : 'Sign-up fee', $e['item'], PaymentsReport::STATUSES[$e['status']] ?? $e['status'],
                number_format($e['minor'] / 100, 2, '.', ''), $e['currency'], $e['estimated'] ? 'yes' : 'no', $e['method'], $e['reference']], $events));
    }
}
