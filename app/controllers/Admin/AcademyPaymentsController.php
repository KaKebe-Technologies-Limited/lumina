<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\CertificatePayments;
use App\Services\Academy\CourseService;
use App\Services\Payments\PesapalClient;

/**
 * Admin > LAF Academy > Payments (administrators): every certificate payment, its status and the Pesapal reference; re-check a payment with Pesapal, accept one that
 * needs review, let a learner have a certificate without paying (waive), test the Pesapal connection, download the list. Docs: docs/PESAPAL.md.
 */
final class AcademyPaymentsController extends AcademyAdminController
{
    private const STATUSES = ['pending' => 'Waiting', 'paid' => 'Paid', 'review' => 'Needs review', 'failed' => 'Failed', 'waived' => 'Waived'];

    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $ready = CertificatePayments::ready();
        $where = ['1 = 1'];
        $params = [];
        $q = mb_substr(trim($request->string('q')), 0, 80);
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(p.merchant_ref LIKE ? OR p.learner_name LIKE ? OR p.learner_email LIKE ? OR p.course_title LIKE ? OR p.confirmation_code LIKE ?)';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $st = $request->string('status');
        if (isset(self::STATUSES[$st])) {
            $where[] = 'p.status = ?';
            $params[] = $st;
        } else {
            $st = '';
        }
        $rows = [];
        $total = 0;
        $pagination = Pagination::make(0, 1, $this->perPage());
        $sums = ['paid' => 0.0, 'count' => 0, 'review' => 0, 'pending' => 0];
        if ($ready) {
            $whereSql = ' WHERE ' . implode(' AND ', $where);
            $total = (int) Database::value('SELECT COUNT(*) FROM academy_payments p' . $whereSql, $params, 0);
            $pagination = Pagination::make($total, $request->page(), $this->perPage());
            $rows = Database::all('SELECT p.* FROM academy_payments p' . $whereSql . ' ORDER BY p.id DESC LIMIT ? OFFSET ?', array_merge($params, [$pagination->perPage, $pagination->offset]));
            $sums = [
                'paid'    => (float) Database::value("SELECT COALESCE(SUM(amount), 0) FROM academy_payments WHERE status = 'paid'", [], 0),
                'count'   => (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE status = 'paid'", [], 0),
                'review'  => (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE status = 'review'", [], 0),
                'pending' => (int) Database::value("SELECT COUNT(*) FROM academy_payments WHERE status = 'pending'", [], 0),
            ];
        }
        $courses = Database::all("SELECT id, title FROM academy_courses WHERE status <> 'archived' ORDER BY title");
        return $this->render('admin/academy/payments', [
            'rows' => $rows, 'pagination' => $pagination, 'q' => $q, 'status' => $st, 'statuses' => self::STATUSES, 'total' => $total, 'sums' => $sums, 'ready' => $ready,
            'configured' => PesapalClient::configured(), 'mode' => PesapalClient::mode(), 'mock' => PesapalClient::usingDevBase(), 'fee' => (int) setting('academy_cert_fee', '20000'),
            'publicBase' => PesapalClient::publicBase(), 'courses' => $courses,
        ], ['title' => 'Certificate payments', 'nav' => 'academy_payments', 'breadcrumbs' => $this->crumbs('Payments')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = CertificatePayments::ready() ? Database::all('SELECT * FROM academy_payments ORDER BY id') : [];
        $this->audit('academy.payments.export', 'academy_payment', null, 'Downloaded the certificate payments list', ['rows' => count($rows)]);
        return CsvExport::response('academy-payments-' . date('Y-m-d') . '.csv', ['Reference', 'Learner', 'Email', 'Course', 'Amount', 'Currency', 'Status', 'Source', 'Method', 'Pesapal confirmation', 'Started', 'Paid'],
            array_map(static fn(array $r): array => [$r['merchant_ref'], $r['learner_name'], $r['learner_email'], $r['course_title'], $r['amount'], $r['currency'], $r['status'], $r['source'], $r['method'], $r['confirmation_code'], $r['created_at'], $r['paid_at']], $rows));
    }

    public function testConnection(Request $request): Response
    {
        $this->authorize('academy.manage');
        $r = PesapalClient::testConnection();
        $this->audit('academy.payments.test', 'academy_payment', null, 'Tested the Pesapal connection: ' . $r['state']);
        $r['ok'] ? Flash::success($r['message']) : Flash::error($r['message']);
        return $this->redirect('admin/academy/payments');
    }

    public function recheck(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $p = CertificatePayments::find($id) ?? throw new HttpException(404, 'Not found');
        $state = CertificatePayments::recheck($p, 'admin');
        $this->audit('academy.payment.recheck', 'academy_payment', $id, 'Re-checked payment ' . $p['merchant_ref'] . ' with Pesapal: ' . $state);
        match ($state) {
            'paid'          => Flash::success('Pesapal confirms this payment. The learner\'s certificate request was filed.'),
            'failed'        => Flash::warning('Pesapal says this payment did not go through.'),
            'review'        => Flash::warning('This payment needs your decision (see the note).'),
            'verify_failed' => Flash::error('Pesapal could not be asked just now. Try again in a minute.'),
            default         => Flash::info('Pesapal has not confirmed this payment yet.'),
        };
        return $this->redirect('admin/academy/payments');
    }

    public function accept(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $p = CertificatePayments::find($id) ?? throw new HttpException(404, 'Not found');
        if (CertificatePayments::accept($id, Auth::id())) {
            $this->audit('academy.payment.accept', 'academy_payment', $id, 'Accepted the payment ' . $p['merchant_ref'] . ' after review');
            Flash::success('The payment was accepted and the learner\'s certificate request was filed.');
        } else {
            Flash::warning('Only a payment that needs review can be accepted.');
        }
        return $this->redirect('admin/academy/payments');
    }

    /** Let a learner (by the e-mail of the account) have the certificate of a course without paying. */
    public function waive(Request $request): Response
    {
        $this->authorize('academy.manage');
        $email = mb_strtolower(trim($request->string('email')));
        $learner = $email !== '' ? Database::one('SELECT * FROM academy_learners WHERE email = ?', [$email]) : null;
        $course = CourseService::find($request->int('course_id'));
        if ($learner === null || $course === null) {
            Flash::error('There is no learner with that e-mail address, or the course was not chosen.');
            return $this->redirect('admin/academy/payments');
        }
        if (CertificatePayments::paid((int) $learner['id'], (int) $course['id'])) {
            Flash::info('This learner has already paid for (or been excused from) this certificate.');
            return $this->redirect('admin/academy/payments');
        }
        $id = CertificatePayments::waive($learner, $course, $request->string('note'), Auth::id());
        $this->audit('academy.payment.waive', 'academy_payment', $id, 'Waived the certificate fee of ' . $course['title'] . ' for learner ' . (int) $learner['id']);
        Flash::success('Done: ' . $learner['name'] . ' does not have to pay for the certificate of "' . $course['title'] . '".');
        return $this->redirect('admin/academy/payments');
    }
}
