<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Inbox\InboxController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Slug;
use App\Core\Uploader;
use App\Models\Application;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;

/**
 * Admin > Job applications. OWNER: B5 "admin-inbox".
 * Workflow: new -> reviewing -> shortlisted -> rejected | hired. CVs live in storage/private/cv and are only ever streamed through
 * cv(): authenticated + ability checked, the stored path must be inside cv/ and is re-validated by Uploader::privatePath()
 * (strict pattern, no "..", realpath must stay inside storage/private) - so a tampered database value cannot read another file.
 */
final class ApplicationController extends InboxController
{
    private const FROM = 'applications a LEFT JOIN jobs j ON j.id = a.job_id';
    private const SELECT = 'a.*, j.title AS job_title';
    private const LABELS = ['new' => 'New', 'reviewing' => 'Reviewing', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected', 'hired' => 'Hired'];
    private const SORTS = ['newest' => 'a.created_at DESC, a.id DESC', 'oldest' => 'a.created_at ASC, a.id ASC', 'name' => 'a.name ASC'];

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        [$where, $f] = $this->filters();
        $page = $this->paged(self::FROM, self::SELECT, $where, self::SORTS[$f['sort']]);

        return $this->render('admin/applications/index', [
            'pagination' => $page,
            'f'          => $f,
            'tabs'       => $this->tabs($this->statusCounts('applications', Application::STATUSES), Application::STATUSES, self::LABELS),
            'jobs'       => Database::all('SELECT id, title FROM jobs ORDER BY title ASC'),
            'labels'     => self::LABELS,
            'canDelete'  => can('inbox.delete'),
            'canExport'  => can('export.csv'),
        ], ['title' => 'Job applications', 'nav' => 'applications', 'breadcrumbs' => [['Job applications', null]]]);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $a = Database::one('SELECT ' . self::SELECT . ' FROM ' . self::FROM . ' WHERE a.id = ? LIMIT 1', [$id]) ?? throw new HttpException(404, 'Not found');
        $others = (int) Database::value('SELECT COUNT(*) FROM applications WHERE email = ? AND id <> ?', [$a['email'], $id], 0);

        return $this->render('admin/applications/show', [
            'a'         => $a,
            'others'    => $others,
            'statuses'  => self::LABELS,
            'hasCv'     => $this->cvRel($a) !== null,
            'canDelete' => can('inbox.delete'),
        ], ['title' => 'Application from ' . (string) $a['name'], 'nav' => 'applications', 'breadcrumbs' => [['Job applications', url('admin/applications')], [(string) $a['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $a = Application::findOrFail($id);
        $data = $this->validate([
            'status'      => 'required|in:' . implode(',', Application::STATUSES),
            'admin_notes' => 'nullable|max:5000',
        ], [], ['status' => 'Status', 'admin_notes' => 'Notes']);
        Application::update($id, ['status' => $data['status'], 'admin_notes' => ($data['admin_notes'] ?? '') !== '' ? $data['admin_notes'] : null]);
        $this->audit('application.update', 'application', $id, 'Updated application from "' . $a['name'] . '" (' . $data['status'] . ')', ['status' => $data['status']]);
        Flash::success('Application updated.');
        return $this->redirect('admin/applications/' . $id);
    }

    /** Authenticated CV download (never a public URL). */
    public function cv(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $a = Application::findOrFail($id);
        $rel = $this->cvRel($a);
        if ($rel === null) {
            throw new HttpException(404, 'No CV on file');
        }
        $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
        $nice = 'CV-' . (Slug::make((string) $a['name'], 60) ?: 'applicant-' . $id) . '.' . $ext;
        $response = Uploader::download($rel, $nice);                 // throws 404 on any path problem
        $this->audit('application.cv_download', 'application', $id, 'Downloaded the CV of "' . $a['name'] . '"');
        return $response;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $a = Application::findOrFail($id);
        $rel = $this->cvRel($a);
        Application::delete($id);
        if ($rel !== null) {
            Uploader::deletePrivate($rel);
        }
        $this->audit('application.delete', 'application', $id, 'Deleted application from "' . $a['name'] . '"');
        Flash::success('Application and its CV were deleted.');
        return $this->redirect('admin/applications');
    }

    /** Bulk: mark_new | mark_reviewing | mark_shortlisted | mark_rejected | mark_hired | delete (delete needs inbox.delete). */
    public function bulk(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $in = $this->bulkInput(['mark_new', 'mark_reviewing', 'mark_shortlisted', 'mark_rejected', 'mark_hired', 'delete'], 'admin/applications');
        if ($in instanceof Response) {
            return $in;
        }
        [$ids, $action] = $in;
        $marks = $this->marks($ids);

        if ($action === 'delete') {
            $this->authorize('inbox.delete');
            $rows = Database::all("SELECT id, cv_path FROM applications WHERE id IN ({$marks})", $ids);
            $n = Database::execute("DELETE FROM applications WHERE id IN ({$marks})", $ids);
            foreach ($rows as $r) {
                $rel = $this->cvRel($r);
                if ($rel !== null) {
                    Uploader::deletePrivate($rel);
                }
            }
            $this->audit('application.bulk_delete', 'application', null, 'Deleted ' . $this->items($n, 'application'), ['ids' => $ids]);
            Flash::success($this->items($n, 'application') . ' deleted.');
            return $this->back('admin/applications');
        }
        $status = substr($action, 5);
        $n = Database::execute("UPDATE applications SET status = ? WHERE id IN ({$marks})", array_merge([$status], $ids));
        $this->audit('application.bulk_status', 'application', null, 'Marked ' . $this->items($n, 'application') . ' as ' . $status, ['ids' => $ids, 'status' => $status]);
        Flash::success($this->items($n, 'application') . ' marked as ' . (self::LABELS[$status] ?? $status) . '.');
        return $this->back('admin/applications');
    }

    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        [$where] = $this->filters();
        $rows = $this->exportRows(self::FROM, self::SELECT, $where, 'a.created_at DESC, a.id DESC');
        $this->audit('application.export', 'application', null, 'Exported ' . $this->items(count($rows), 'application') . ' to CSV');
        $out = [];
        foreach ($rows as $r) {
            $out[] = [$r['id'], $r['created_at'], $r['name'], $r['email'], $r['phone'], $r['job_title'], $r['linkedin_url'], $r['cv_original_name'], $r['status'], $r['cover_letter'], $r['admin_notes']];
        }
        return CsvExport::response('applications-' . date('Y-m-d') . '.csv',
            ['ID', 'Received', 'Name', 'Email', 'Phone', 'Job', 'LinkedIn', 'CV file', 'Status', 'Cover letter', 'Notes'], $out);
    }

    /** The stored CV path when it is a plausible path INSIDE storage/private/cv, else null. */
    private function cvRel(array $row): ?string
    {
        $rel = trim((string) ($row['cv_path'] ?? ''));
        if ($rel === '' || !str_starts_with($rel, 'cv/') || str_contains($rel, '..') || str_contains($rel, chr(92))) {
            return null;
        }
        return $rel;
    }

    /** @return array{0:Where,1:array{q:string,status:string,job:string,from:string,to:string,sort:string}} */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, Application::STATUSES, true) ? $status : 'all';
        $job = $this->request->string('job');
        $job = ($job === '0' || ctype_digit($job)) ? $job : '';
        $from = Support::date($this->request->string('from')) ?? '';
        $to = Support::date($this->request->string('to')) ?? '';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';

        $w = new Where();
        $w->addLike(['a.name', 'a.email', 'a.phone', 'a.cover_letter', 'j.title'], Support::like($q));
        if ($status !== 'all') {
            $w->add('a.status = ?', [$status]);
        }
        if ($job === '0') {
            $w->add('a.job_id IS NULL');
        } elseif ($job !== '') {
            $w->add('a.job_id = ?', [(int) $job]);
        }
        if ($from !== '') {
            $w->add('a.created_at >= ?', [$from . ' 00:00:00']);
        }
        if ($to !== '') {
            $w->add('a.created_at <= ?', [$to . ' 23:59:59']);
        }
        return [$w, ['q' => $q, 'status' => $status, 'job' => $job, 'from' => $from, 'to' => $to, 'sort' => $sort]];
    }
}
