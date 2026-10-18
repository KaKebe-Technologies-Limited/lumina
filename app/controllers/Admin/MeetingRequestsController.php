<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\CsvExport;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\Meeting;
use App\Models\MeetingRequest;
use App\Services\Meetings\MeetingRequestService;
use App\Services\Meetings\MeetingService;
use App\Services\Meetings\MeetingTime;

/**
 * Admin > Meeting requests: the inbox of the Contact page "Request a meeting" form (list + filter, detail, status workflow new -> approved | declined | closed,
 * "Schedule this meeting" = GET /admin/meetings/create?request=ID, decline with an optional message, CSV export, delete for administrators).
 * OWNER: ADMIN agent. Contract: docs/MEETINGS.md sections 6, 10 and 12.1. Rules and e-mails: App\Services\Meetings\MeetingRequestService.
 * Personal data (names, addresses, phone numbers, messages): admin + editor only; audit entries carry ids and statuses only (the service writes them).
 */
final class MeetingRequestsController extends AdminController
{
    public const TIME_LABELS = ['any' => 'Any time of day', 'morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'];
    public const PREF_LABELS = ['video' => 'Video call', 'phone' => 'Phone call', 'in_person' => 'In person'];

    public function index(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $available = MeetingService::available();
        $status = $request->string('status');
        $status = in_array($status, MeetingRequest::STATUSES, true) ? $status : '';
        $q = mb_substr($request->string('q'), 0, 100);
        $list = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20];
        $counts = ['all' => 0, 'new' => 0, 'approved' => 0, 'declined' => 0, 'closed' => 0];
        if ($available) {
            $list = MeetingRequestService::listRequests(['status' => $status, 'q' => $q], max(1, $request->int('page', 1)), $this->perPage());
            $counts = MeetingRequestService::statusCounts();
        }
        return $this->render('admin/meetings/requests', [
            'available' => $available, 'list' => $list, 'counts' => $counts, 'f' => ['status' => $status, 'q' => $q], 'canDelete' => can('meetings.delete'),
        ], ['title' => 'Meeting requests', 'nav' => 'meeting_requests', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Meeting requests', null]]]);
    }

    /** GET /admin/meeting-requests/export.csv  (the same ?status= and ?q= filters as the list) */
    public function export(Request $request): Response
    {
        $this->authorize('meetings.manage', 'export.csv');
        $status = $request->string('status');
        $filters = ['status' => in_array($status, MeetingRequest::STATUSES, true) ? $status : '', 'q' => mb_substr($request->string('q'), 0, 100)];
        AuditLog::record('meeting_request.export', 'meeting_request', null, 'Exported the meeting requests', ['status' => $filters['status']]);
        return CsvExport::response('meeting-requests-' . date('Y-m-d') . '.csv', MeetingRequestService::CSV_HEADER, MeetingRequestService::csvRows($filters));
    }

    /** GET /admin/meeting-requests/{id} */
    public function show(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $r = $this->find($id);
        $meeting = $r['meeting_id'] !== null ? Meeting::find((int) $r['meeting_id']) : null;
        return $this->render('admin/meetings/request', [
            'r' => $r, 'meeting' => $meeting, 'canDelete' => can('meetings.delete'), 'today' => MeetingTime::today(),
            'erased' => str_starts_with((string) $r['email'], 'erased-'),
        ], ['title' => 'Meeting request from ' . (string) $r['name'], 'nav' => 'meeting_requests', 'breadcrumbs' => [
            ['Calendar & schedule', url('admin/calendar')], ['Meeting requests', url('admin/meeting-requests')], [(string) $r['name'], null],
        ]]);
    }

    /** POST /admin/meeting-requests/{id}/status  (status, notes, notify + note for a decline) */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $r = $this->find($id);
        $status = $request->string('status', (string) $r['status']);
        if (!in_array($status, MeetingRequest::STATUSES, true)) {
            Flash::error('Choose one of the statuses.');
            return $this->redirect('admin/meeting-requests/' . $id);
        }
        $opts = [];
        if ($request->has('notes')) {
            $opts['notes'] = $request->string('notes');
        }
        $declining = $status === 'declined' && $r['status'] !== 'declined';
        if ($declining && $request->bool('notify')) {
            $opts['notify'] = true;
            $opts['note'] = mb_substr($request->string('note'), 0, 1500);
        }
        $res = MeetingRequestService::setStatus($id, $status, $this->userId(), $opts);
        if (!$res['ok']) {
            Flash::error((string) ($res['error'] ?? 'The request could not be updated.'));
            return $this->redirect('admin/meeting-requests/' . $id);
        }
        if ($declining && !empty($opts['notify'])) {
            $res['mailed'] ? Flash::success('The request is marked as declined and the visitor was e-mailed.') : Flash::warning('The request is marked as declined, but the e-mail to the visitor could not be sent.');
        } elseif ($request->has('notes') && $status === (string) $r['status']) {
            Flash::success('Your notes were saved.');
        } else {
            Flash::success('The request is marked as ' . strtolower(MeetingRequestService::STATUS_LABELS[$status]) . '.');
        }
        $to = $request->string('return') === 'list' ? 'admin/meeting-requests' : 'admin/meeting-requests/' . $id;
        return $this->redirect($to);
    }

    /** POST /admin/meeting-requests/{id}/delete  (administrators: meetings.delete) */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage', 'meetings.delete');
        $this->find($id);
        if (MeetingRequestService::delete($id)) {
            Flash::success('The request was deleted.');
        } else {
            Flash::error('The request could not be deleted.');
        }
        return $this->redirect('admin/meeting-requests');
    }

    /** @return array<string,mixed> */
    private function find(int $id): array
    {
        $r = MeetingService::available() ? MeetingRequest::find($id) : null;
        if ($r === null) {
            $this->abort(404, 'Request not found');
        }
        return $r;
    }
}
