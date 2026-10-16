<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Inbox\InboxController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\InvolvementRequest;
use App\Services\Inbox\Support;
use App\Services\Inbox\Where;

/**
 * Admin > Involvement requests (volunteer / partner / mentor ... sign-ups from the public Involvement form). OWNER: B5 "admin-inbox".
 * Workflow: new -> contacted -> in progress -> closed, with private notes.
 */
final class InvolvementController extends InboxController
{
    private const FROM = 'involvement_requests r';
    private const LABELS = ['new' => 'New', 'contacted' => 'Contacted', 'in_progress' => 'In progress', 'closed' => 'Closed'];
    private const SORTS = ['newest' => 'r.created_at DESC, r.id DESC', 'oldest' => 'r.created_at ASC, r.id ASC'];

    public function index(Request $request): Response
    {
        $this->authorize('inbox.manage');
        [$where, $f] = $this->filters();
        $page = $this->paged(self::FROM, 'r.*', $where, self::SORTS[$f['sort']]);

        return $this->render('admin/involvement/index', [
            'pagination' => $page,
            'f'          => $f,
            'tabs'       => $this->tabs($this->statusCounts('involvement_requests', InvolvementRequest::STATUSES), InvolvementRequest::STATUSES, self::LABELS),
            'types'      => $this->types(),
            'labels'     => self::LABELS,
            'canDelete'  => can('inbox.delete'),
            'canExport'  => can('export.csv'),
        ], ['title' => 'Involvement requests', 'nav' => 'involvement', 'breadcrumbs' => [['Involvement requests', null]]]);
    }

    public function show(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $r = InvolvementRequest::findOrFail($id);
        return $this->render('admin/involvement/show', [
            'r'         => $r,
            'statuses'  => self::LABELS,
            'canDelete' => can('inbox.delete'),
        ], ['title' => 'Request from ' . (string) $r['name'], 'nav' => 'involvement', 'breadcrumbs' => [['Involvement requests', url('admin/involvement')], [(string) $r['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage');
        $r = InvolvementRequest::findOrFail($id);
        $data = $this->validate([
            'status'      => 'required|in:' . implode(',', InvolvementRequest::STATUSES),
            'admin_notes' => 'nullable|max:5000',
        ], [], ['status' => 'Status', 'admin_notes' => 'Notes']);
        InvolvementRequest::update($id, ['status' => $data['status'], 'admin_notes' => ($data['admin_notes'] ?? '') !== '' ? $data['admin_notes'] : null]);
        $this->audit('involvement.update', 'involvement', $id, 'Updated request from "' . $r['name'] . '" (' . $data['status'] . ')', ['status' => $data['status']]);
        Flash::success('Request updated.');
        return $this->redirect('admin/involvement/' . $id);
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('inbox.manage', 'inbox.delete');
        $r = InvolvementRequest::findOrFail($id);
        InvolvementRequest::delete($id);
        $this->audit('involvement.delete', 'involvement', $id, 'Deleted request from "' . $r['name'] . '"');
        Flash::success('Request deleted.');
        return $this->redirect('admin/involvement');
    }

    /** Bulk: contacted | in_progress | closed | new | delete (delete needs inbox.delete). */
    public function bulk(Request $request): Response
    {
        $this->authorize('inbox.manage');
        $in = $this->bulkInput(['mark_new', 'mark_contacted', 'mark_in_progress', 'mark_closed', 'delete'], 'admin/involvement');
        if ($in instanceof Response) {
            return $in;
        }
        [$ids, $action] = $in;
        $marks = $this->marks($ids);

        if ($action === 'delete') {
            $this->authorize('inbox.delete');
            $n = Database::execute("DELETE FROM involvement_requests WHERE id IN ({$marks})", $ids);
            $this->audit('involvement.bulk_delete', 'involvement', null, 'Deleted ' . $this->items($n, 'request'), ['ids' => $ids]);
            Flash::success($this->items($n, 'request') . ' deleted.');
            return $this->back('admin/involvement');
        }
        $status = substr($action, 5);                               // mark_in_progress -> in_progress
        $n = Database::execute("UPDATE involvement_requests SET status = ? WHERE id IN ({$marks})", array_merge([$status], $ids));
        $this->audit('involvement.bulk_status', 'involvement', null, 'Marked ' . $this->items($n, 'request') . ' as ' . $status, ['ids' => $ids, 'status' => $status]);
        Flash::success($this->items($n, 'request') . ' marked as ' . (self::LABELS[$status] ?? $status) . '.');
        return $this->back('admin/involvement');
    }

    public function export(Request $request): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        [$where] = $this->filters();
        $rows = $this->exportRows(self::FROM, 'r.*', $where, 'r.created_at DESC, r.id DESC');
        $this->audit('involvement.export', 'involvement', null, 'Exported ' . $this->items(count($rows), 'request') . ' to CSV');
        $out = [];
        foreach ($rows as $r) {
            $out[] = [$r['id'], $r['created_at'], $r['name'], $r['email'], $r['phone'], label_from((string) $r['involvement_type']), $r['organisation'], $r['message'], $r['status'], $r['admin_notes']];
        }
        return CsvExport::response('involvement-requests-' . date('Y-m-d') . '.csv',
            ['ID', 'Received', 'Name', 'Email', 'Phone', 'Type', 'Organisation', 'Message', 'Status', 'Notes'], $out);
    }

    /** Distinct involvement types present in the data: value => label. @return array<string,string> */
    private function types(): array
    {
        $out = [];
        foreach (Database::all('SELECT DISTINCT involvement_type AS t FROM involvement_requests ORDER BY involvement_type') as $r) {
            $t = (string) $r['t'];
            if ($t !== '') {
                $out[$t] = label_from($t);
            }
        }
        return $out;
    }

    /** @return array{0:Where,1:array{q:string,status:string,type:string,from:string,to:string,sort:string}} */
    private function filters(): array
    {
        $q = $this->request->string('q');
        $status = $this->request->string('status', 'all');
        $status = in_array($status, InvolvementRequest::STATUSES, true) ? $status : 'all';
        $type = mb_substr($this->request->string('type'), 0, 60);
        $from = Support::date($this->request->string('from')) ?? '';
        $to = Support::date($this->request->string('to')) ?? '';
        $sort = $this->request->string('sort', 'newest');
        $sort = isset(self::SORTS[$sort]) ? $sort : 'newest';

        $w = new Where();
        $w->addLike(['r.name', 'r.email', 'r.phone', 'r.organisation', 'r.message'], Support::like($q));
        if ($status !== 'all') {
            $w->add('r.status = ?', [$status]);
        }
        if ($type !== '') {
            $w->add('r.involvement_type = ?', [$type]);
        }
        if ($from !== '') {
            $w->add('r.created_at >= ?', [$from . ' 00:00:00']);
        }
        if ($to !== '') {
            $w->add('r.created_at <= ?', [$to . ' 23:59:59']);
        }
        return [$w, ['q' => $q, 'status' => $status, 'type' => $type, 'from' => $from, 'to' => $to, 'sort' => $sort]];
    }
}
