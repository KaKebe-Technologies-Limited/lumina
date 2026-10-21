<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Uploader;
use App\Services\Academy\AcademyMail;
use App\Services\Academy\CourseService;
use App\Services\Academy\FacilitatorApplications;

/**
 * Admin > LAF Academy > Facilitator applications (administrators): read the application with the CV and the papers, then approve (creates the facilitator account),
 * ask for more information, or decline; the applicant is told by e-mail. The files are private: they open only here, for a signed-in administrator.
 */
final class AcademyFacilitatorApplicationsController extends AcademyAdminController
{
    private function load(int $id): array
    {
        $this->authorize('academy.manage');
        if (!FacilitatorApplications::ready()) {
            throw new HttpException(404, 'Not found');
        }
        return FacilitatorApplications::find($id) ?? throw new HttpException(404, 'Not found');
    }

    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        if (!FacilitatorApplications::ready()) {
            Flash::warning('The database update has not been imported yet. In phpMyAdmin, import lumina-live-import.sql into your database (it is safe to run again), then open this page again.');
            return $this->redirect('admin/academy');
        }
        $status = $request->string('status');
        $status = isset(FacilitatorApplications::STATUSES[$status]) ? $status : '';
        $q = trim($request->string('q'));
        $where = ['1 = 1'];
        $params = [];
        if ($status !== '') {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($q, 0, 80)) . '%';
            $where[] = '(name LIKE ? OR email LIKE ? OR topic_detail LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        $rows = Database::all('SELECT * FROM academy_facilitator_applications WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(status, \'pending\', \'more_info\', \'approved\', \'rejected\'), created_at DESC LIMIT 300', $params);
        return $this->render('admin/academy/facilitator-applications/index', ['rows' => $rows, 'status' => $status, 'q' => $q, 'counts' => FacilitatorApplications::counts()],
            ['title' => 'Facilitator applications', 'nav' => 'academy_applications', 'breadcrumbs' => $this->crumbs('Facilitator applications')]);
    }

    public function show(Request $request, int $id): Response
    {
        $app = $this->load($id);
        $creds = Session::getFlash('temp_credentials');
        return $this->render('admin/academy/facilitator-applications/show', [
            'app' => $app, 'docs' => FacilitatorApplications::docs($app), 'creds' => is_array($creds) ? $creds : null,
            'topic' => CourseService::categoryLabel((string) $app['topic']),
            'reviewer' => $app['reviewed_by'] ? (string) Database::value('SELECT name FROM users WHERE id = ?', [(int) $app['reviewed_by']], '') : '',
        ], ['title' => (string) $app['name'], 'nav' => 'academy_applications', 'breadcrumbs' => $this->crumbs('Facilitator applications', [(string) $app['name'], null])]);
    }

    public function decide(Request $request, int $id): Response
    {
        $app = $this->load($id);
        $action = $request->string('action');
        $note = trim(strip_tags($request->string('applicant_note')));
        $internal = trim(strip_tags($request->string('admin_note')));
        if ($internal !== '' || $request->has('admin_note')) {
            Database::update('academy_facilitator_applications', ['admin_note' => mb_substr($internal, 0, 3000) ?: null], 'id = ?', [$id]);
        }
        $back = 'admin/academy/facilitator-applications/' . $id;
        if ($action === 'note') {
            Flash::success('The note was saved.');
            return $this->redirect($back);
        }
        if (!in_array($action, ['approve', 'more_info', 'reject'], true)) {
            throw new HttpException(422, 'Unknown action');
        }
        if ($action === 'approve') {
            $r = FacilitatorApplications::approve($app, $this->userId(), $note);
            if (!$r['ok']) {
                Flash::warning($r['message']);
                return $this->redirect($back);
            }
            $creds = $r['credentials'] ?? null;
            $send = $request->bool('email_credentials');
            $mailed = AcademyMail::facilitatorDecision($app, 'approved', $note, $send ? $creds : null);
            $this->audit('academy.facilitator_application.approve', 'academy_facilitator_application', $id, 'Approved ' . $app['email'] . ' for ' . $app['topic']);
            if ($creds !== null) {
                Session::flash('temp_credentials', $creds + ['reason' => 'created']);
            }
            Flash::success($r['message'] . ($mailed ? ' The applicant was e-mailed.' : ' The e-mail could not be sent: tell the applicant yourself.')
                . ($creds !== null && $send ? ' The temporary sign-in details were included in the e-mail.' : ''));
            return $this->redirect($back);
        }
        if ($action === 'reject' && $app['status'] === 'approved') {
            Flash::warning('An approved application cannot be declined. Switch off the account under Facilitators instead.');
            return $this->redirect($back);
        }
        $status = $action === 'reject' ? 'rejected' : 'more_info';
        FacilitatorApplications::setStatus($app, $status, $this->userId(), $note);
        $mailed = AcademyMail::facilitatorDecision($app, $status, $note);
        $this->audit('academy.facilitator_application.' . $status, 'academy_facilitator_application', $id, ucfirst($status) . ' for ' . $app['email']);
        Flash::success('Saved.' . ($mailed ? ' The applicant was e-mailed.' : ' The e-mail could not be sent.'));
        return $this->redirect($back);
    }

    /** Download the CV (part = cv) or a supporting paper (part = the position in the list). */
    public function file(Request $request, int $id, string $part): Response
    {
        $app = $this->load($id);
        if ($part === 'cv') {
            if (empty($app['cv_path'])) {
                throw new HttpException(404, 'Not found');
            }
            return Uploader::download((string) $app['cv_path'], (string) ($app['cv_name'] ?: 'cv'));
        }
        $docs = FacilitatorApplications::docs($app);
        $d = $docs[(int) $part] ?? throw new HttpException(404, 'Not found');
        return Uploader::download($d['path'], $d['name']);
    }

    public function delete(Request $request, int $id): Response
    {
        $app = $this->load($id);
        FacilitatorApplications::delete($app);
        $this->audit('academy.facilitator_application.delete', 'academy_facilitator_application', $id, 'Deleted the application of ' . $app['email']);
        Flash::success('The application and its files were deleted.');
        return $this->redirect('admin/academy/facilitator-applications');
    }
}
