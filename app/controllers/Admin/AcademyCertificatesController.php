<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Services\Academy\CertificateService;

/**
 * Admin > LAF Academy > Certificates (administrators): every certificate issued, search by number / name / course, withdraw a certificate (the public check page then
 * says so) and reinstate it, open the PDF, download the list. A facilitator does not see this page.
 */
final class AcademyCertificatesController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $where = ['1 = 1'];
        $params = [];
        $q = mb_substr(trim($request->string('q')), 0, 80);
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(c.code LIKE ? OR c.learner_name LIKE ? OR c.course_title LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        $st = $request->string('status');
        $ready = CertificateService::approvalReady();
        if ($st === 'waiting' && $ready) {
            $where[] = 'c.approved_at IS NULL AND c.revoked_at IS NULL';
        } elseif ($st === 'valid') {
            $where[] = 'c.revoked_at IS NULL' . ($ready ? ' AND c.approved_at IS NOT NULL' : '');
        } elseif ($st === 'withdrawn') {
            $where[] = 'c.revoked_at IS NOT NULL';
        } else {
            $st = '';
        }
        $login = \App\Services\Academy\LearnerRegistry::loginMode($request->string('login'));
        if (($lc = \App\Services\Academy\LearnerRegistry::loginSql($login, 'll.last_login_at')) !== null) {
            $where[] = 'c.learner_id IN (SELECT ll.id FROM academy_learners ll WHERE ' . $lc . ')';
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_certificates c' . $whereSql, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $rows = Database::all('SELECT c.*, l.email AS learner_email FROM academy_certificates c LEFT JOIN academy_learners l ON l.id = c.learner_id' . $whereSql . ' ORDER BY c.id DESC LIMIT ? OFFSET ?', array_merge($params, [$pagination->perPage, $pagination->offset]));
        return $this->render('admin/academy/certificates', ['rows' => $rows, 'pagination' => $pagination, 'q' => $q, 'status' => $st, 'login' => $login, 'total' => $total, 'waiting' => CertificateService::pendingCount(), 'approved' => CertificateService::approvedCount(), 'ready' => $ready],
            ['title' => 'Certificates', 'nav' => 'academy_certificates', 'breadcrumbs' => $this->crumbs('Certificates')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        $wa = \App\Services\Academy\VerificationCode::ready() ? 'l.whatsapp' : 'NULL';
        $rows = Database::all('SELECT c.*, l.email AS learner_email, l.phone AS learner_phone, l.country AS learner_country, ' . $wa . ' AS learner_whatsapp FROM academy_certificates c LEFT JOIN academy_learners l ON l.id = c.learner_id ORDER BY c.id');
        $this->audit('academy.certificates.export', 'academy_certificate', null, 'Downloaded the certificate list', ['rows' => count($rows)]);
        return CsvExport::response('academy-certificates-' . date('Y-m-d') . '.csv', ['Number', 'Learner', 'Email', 'Country', 'Country code', 'Phone', 'WhatsApp', 'Course', 'Score %', 'Hours', 'Issued', 'Status'],
            array_map(static fn(array $r): array => [$r['code'], $r['learner_name'], $r['learner_email'], $r['learner_country'], \App\Services\Academy\LearnerPhones::dialCode($r['learner_country']), \App\Services\Academy\LearnerPhones::display($r['learner_phone'], $r['learner_country']), (string) ($r['learner_whatsapp'] ?? '') !== '' ? '+' . $r['learner_whatsapp'] : '', $r['course_title'], $r['score_pct'], $r['hours'], $r['issued_at'], $r['revoked_at'] ? 'withdrawn' : 'valid'], $rows));
    }

    /**
     * Open the PDF of a certificate: ONLY one that has been issued (the learner passed, the certificate is approved and not withdrawn). A certificate that still waits for
     * approval or for its fee, or that was withdrawn, cannot be viewed: approve it first, then view it.
     */
    public function preview(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        // an administrator may view every certificate: released, waiting for the fee or for approval, or withdrawn (the learner and visitors cannot see the unreleased ones)
        return Response::text(CertificateService::pdf($c), 200, 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="LAF-Academy-certificate-' . $c['code'] . '.pdf"')
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex');
    }

    /** POST admin/academy/certificates/{id}/mark-fee-paid: the money was checked (Daraza dashboard...): the certificate fee counts as paid. */
    public function markFeePaid(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        if (\App\Services\Academy\AcademyFees::markPaidByAdmin('cert', $id)) {
            $this->audit('academy.certificate.fee_marked_paid', 'academy_certificate', $id, 'Marked the fee of certificate ' . $c['code'] . ' as paid');
            Flash::success('The certificate fee is marked as paid. The certificate is available once it is approved (or at once if approval is not required).');
        } else {
            Flash::info('This certificate has no unpaid fee.');
        }
        return $this->backTo($request);
    }

    /** Approve one waiting certificate; optionally e-mail it to the learner at once. */
    public function approve(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $res = CertificateService::approve($id, \App\Core\Auth::id(), $request->bool('email'), $request->bool('waive'));
        $this->audit('academy.certificate.approve', 'academy_certificate', $id, 'Approved certificate ' . $c['code'] . ($request->bool('email') ? ' and e-mailed it' : ''));
        if (!$res['ok']) {
            Flash::error(!empty($res['fee']) ? 'The certificate fee is not paid yet. Use "Waive fee and approve" if you want to give it for free.' : 'The certificate could not be approved.');
        } elseif ($request->bool('email')) {
            $res['mailed'] ? Flash::success('Certificate ' . $c['code'] . ' was approved and e-mailed to the learner.') : Flash::warning('Certificate ' . $c['code'] . ' was approved, but the e-mail could not be sent (check Settings > E-mail). The learner can still download it from the Academy.');
        } else {
            Flash::success('Certificate ' . $c['code'] . ' was approved: the learner can download it now.');
        }
        return $this->backTo($request);
    }

    /** Back to the page the button was on: the learner's page ("learner:12"), the waiting list ("waiting") or the certificate list. */
    private function backTo(Request $request): Response
    {
        $b = $request->string('back');
        if (preg_match('/^learner:(\d+)$/', $b, $m) === 1) {
            return $this->redirect('admin/academy/learners/' . (int) $m[1]);
        }
        return $this->redirect('admin/academy/certificates' . ($b === 'waiting' ? '?status=waiting' : ''));
    }

    /** Decline a waiting request (it is removed; the learner may ask again) and tell the learner why. */
    public function decline(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $ok = CertificateService::decline($id, $request->string('reason'));
        $this->audit('academy.certificate.decline', 'academy_certificate', $id, ($ok ? 'Declined' : 'Could not decline') . ' the certificate request ' . $c['code']);
        $ok ? Flash::success('The request was declined and the learner was told. They can request the certificate again later.') : Flash::error('Only a request that is still waiting can be declined.');
        return $this->backTo($request);
    }

    /** Send (or send again) the e-mail with the link and PDF of an approved certificate. */
    public function email(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $ok = CertificateService::email($c);
        $this->audit('academy.certificate.email', 'academy_certificate', $id, ($ok ? 'E-mailed' : 'Could not e-mail') . ' certificate ' . $c['code']);
        $ok ? Flash::success('Certificate ' . $c['code'] . ' was e-mailed to the learner.') : Flash::error('The e-mail could not be sent: the certificate must be approved, valid and the learner account must still exist (and e-mail must work: Settings > E-mail).');
        return $this->redirect('admin/academy/certificates');
    }

    /** Approve every waiting certificate (up to 100 at a time), optionally e-mailing each learner. */
    public function approveAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = CertificateService::approvalReady() ? Database::all('SELECT id FROM academy_certificates WHERE approved_at IS NULL AND revoked_at IS NULL ORDER BY id LIMIT 100') : [];
        $n = 0;
        $mailed = 0;
        foreach ($rows as $r) {
            $res = CertificateService::approve((int) $r['id'], \App\Core\Auth::id(), $request->bool('email'));
            $n += $res['ok'] ? 1 : 0;
            $mailed += $res['mailed'] ? 1 : 0;
        }
        \App\Core\Mailer::closeConnection();
        $this->audit('academy.certificates.approve_all', 'academy_certificate', null, 'Approved all waiting certificates', ['approved' => $n, 'emailed' => $mailed]);
        Flash::success($n . ' certificate' . ($n === 1 ? ' was' : 's were') . ' approved' . ($request->bool('email') ? ' and ' . $mailed . ' e-mailed.' : '.') . (CertificateService::pendingCount() > 0 ? ' More are waiting: press the button again.' : ''));
        return $this->redirect('admin/academy/certificates');
    }

    /** POST admin/academy/certificates-hold-all: every certificate in the system waits for its certificate fee (paid with Pesapal = released at once; or release / waive here). */
    public function holdAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = \App\Services\Academy\AcademyFees::holdAllCertificates();
        $this->audit('academy.certificates.hold_all', 'academy_certificate', null, 'Put every certificate on hold until its fee is paid', ['count' => $n]);
        Flash::success($n === 0 ? 'Nothing to hold: set the certificate fee first (Admin > Pesapal payments > LAF Academy fees), or every certificate already has its fee.' : $n . ' certificate' . ($n === 1 ? ' is' : 's are') . ' on hold now. A learner gets ' . ($n === 1 ? 'it' : 'theirs') . ' as soon as the fee is paid with Pesapal; you can also release a certificate or waive its fee yourself.');
        return $this->redirect('admin/academy/certificates?status=waiting');
    }

    /** POST certificates-remind-fee-all: a portal message (and an e-mail when ticked) to every learner whose certificate waits for its fee. */
    public function remindFeeAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = \App\Services\Academy\AcademyFees::remindAllCerts(\App\Core\Auth::id(), $request->bool('email'));
        $this->audit('academy.certificates.fee_reminder_all', 'academy_certificate', null, 'Sent the certificate fee reminder to every learner whose certificate waits for its fee', ['count' => $n]);
        Flash::success($n === 0 ? 'Nobody has a certificate waiting for its fee, so nothing was sent.' : 'The payment reminder was sent to ' . $n . ' learner' . ($n === 1 ? '' : 's') . '.');
        return $this->redirect('admin/academy/certificates');
    }

    /** POST certificates-remind-fee-whatsapp-all: one click sends the WhatsApp reminder to all of them (needs the WhatsApp Business API); otherwise the guided list opens. */
    public function remindFeeWhatsAppAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        if (!\App\Services\Academy\WhatsAppSender::certReminderEnabled()) {
            Flash::info('This site is not connected to the WhatsApp Business API for certificate reminders yet, so WhatsApp cannot send by itself. Use "Start sending to all" below: it opens each chat with the message typed, one after the other.');
            return $this->redirect('admin/academy/certificates-fee-reminders');
        }
        $r = \App\Services\Academy\AcademyFees::remindAllCertsWhatsApp();
        $this->audit('academy.certificates.fee_whatsapp_all', 'academy_certificate', null, 'Sent the certificate fee reminder on WhatsApp', ['sent' => $r['sent'], 'failed' => $r['failed']]);
        $msg = $r['sent'] . ' WhatsApp reminder' . ($r['sent'] === 1 ? '' : 's') . ' sent.' . ($r['nonumber'] > 0 ? ' ' . $r['nonumber'] . ' learner' . ($r['nonumber'] === 1 ? ' has' : 's have') . ' no usable number.' : '') . ($r['failed'] > 0 ? ' ' . $r['failed'] . ' could not be sent' . ($r['error'] ? ' (' . $r['error'] . ')' : '') . '.' : '');
        $r['failed'] > 0 && $r['sent'] === 0 ? Flash::error($msg) : Flash::success($msg);
        return $this->redirect('admin/academy/certificates');
    }

    /** GET certificates-fee-reminders: one WhatsApp button per learner whose certificate waits for its fee. */
    public function feeReminders(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = [];
        foreach (\App\Services\Academy\AcademyFees::unpaidCerts() as $row) {
            $row['wa'] = \App\Services\Academy\AcademyFees::certReminderWhatsAppUrl($row);
            $rows[] = $row;
        }
        return $this->render('admin/academy/certificate-fee-reminders', ['rows' => $rows]);
    }
    /** Take back every approved certificate: all of them wait for approval again before anybody receives them. */
    public function withdrawAll(Request $request): Response
    {
        $this->authorize('academy.manage');
        $n = CertificateService::withdrawAllApprovals();
        $this->audit('academy.certificates.withdraw_all', 'academy_certificate', null, 'Took back every approved certificate: they wait for approval again', ['count' => $n]);
        Flash::success($n . ' certificate' . ($n === 1 ? ' was' : 's were') . ' taken back. ' . ($n === 1 ? 'It is' : 'They are') . ' waiting for your approval: nobody can open or check ' . ($n === 1 ? 'it' : 'them') . ' until you approve.');
        return $this->redirect('admin/academy/certificates?status=waiting');
    }

    public function revoke(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        CertificateService::revoke($id, $request->string('reason'));
        $this->audit('academy.certificate.revoke', 'academy_certificate', $id, 'Withdrew certificate ' . $c['code']);
        Flash::success('Certificate ' . $c['code'] . ' was withdrawn. The public check page now says so.');
        return $this->redirect('admin/academy/certificates');
    }

    public function reinstate(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        CertificateService::reinstate($id);
        $this->audit('academy.certificate.reinstate', 'academy_certificate', $id, 'Reinstated certificate ' . $c['code']);
        Flash::success('Certificate ' . $c['code'] . ' is valid again.');
        return $this->redirect('admin/academy/certificates');
    }
}
