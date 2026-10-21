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
        if ($st === 'valid') {
            $where[] = 'c.revoked_at IS NULL';
        } elseif ($st === 'withdrawn') {
            $where[] = 'c.revoked_at IS NOT NULL';
        } else {
            $st = '';
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);
        $total = (int) Database::value('SELECT COUNT(*) FROM academy_certificates c' . $whereSql, $params, 0);
        $pagination = Pagination::make($total, $request->page(), $this->perPage());
        $rows = Database::all('SELECT c.*, l.email AS learner_email FROM academy_certificates c LEFT JOIN academy_learners l ON l.id = c.learner_id' . $whereSql . ' ORDER BY c.id DESC LIMIT ? OFFSET ?', array_merge($params, [$pagination->perPage, $pagination->offset]));
        return $this->render('admin/academy/certificates', ['rows' => $rows, 'pagination' => $pagination, 'q' => $q, 'status' => $st, 'total' => $total],
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
