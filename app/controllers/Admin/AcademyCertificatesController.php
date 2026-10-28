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
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_certificates c' . $whereSql, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $rows = Database::all('SELECT c.*, l.email AS learner_email FROM academy_certificates c LEFT JOIN academy_learners l ON l.id = c.learner_id' . $whereSql . ' ORDER BY c.id DESC LIMIT ? OFFSET ?', array_merge($params, [$pagination->perPage, $pagination->offset]));
        return $this->render('admin/academy/certificates', ['rows' => $rows, 'pagination' => $pagination, 'q' => $q, 'status' => $st, 'total' => $total, 'waiting' => CertificateService::pendingCount(), 'ready' => $ready],
            ['title' => 'Certificates', 'nav' => 'academy_certificates', 'breadcrumbs' => $this->crumbs('Certificates')]);
    }

    public function export(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = Database::all('SELECT c.*, l.email AS learner_email FROM academy_certificates c LEFT JOIN academy_learners l ON l.id = c.learner_id ORDER BY c.id');
        $this->audit('academy.certificates.export', 'academy_certificate', null, 'Downloaded the certificate list', ['rows' => count($rows)]);
        return CsvExport::response('academy-certificates-' . date('Y-m-d') . '.csv', ['Number', 'Learner', 'Email', 'Course', 'Score %', 'Hours', 'Issued', 'Status'],
            array_map(static fn(array $r): array => [$r['code'], $r['learner_name'], $r['learner_email'], $r['course_title'], $r['score_pct'], $r['hours'], $r['issued_at'], $r['revoked_at'] ? 'withdrawn' : 'valid'], $rows));
    }

    /** Open the PDF of any certificate, also one that still waits for approval (to look at it before approving). */
    public function preview(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        return Response::text(CertificateService::pdf($c), 200, 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="LAF-Academy-certificate-' . $c['code'] . '.pdf"')
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex');
    }

    /** Approve one waiting certificate; optionally e-mail it to the learner at once. */
    public function approve(Request $request, int $id): Response
    {
        $this->authorize('academy.manage');
        $c = Database::one('SELECT * FROM academy_certificates WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        $res = CertificateService::approve($id, \App\Core\Auth::id(), $request->bool('email'));
        $this->audit('academy.certificate.approve', 'academy_certificate', $id, 'Approved certificate ' . $c['code'] . ($request->bool('email') ? ' and e-mailed it' : ''));
        if (!$res['ok']) {
            Flash::error('The certificate could not be approved.');
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
