<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\FormState;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingRequest;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\InviteeParser;
use App\Services\Meetings\MeetingRenderer;
use App\Services\Meetings\MeetingRequestService;
use App\Services\Meetings\MeetingService;
use App\Services\Meetings\MeetingTime;

/**
 * Admin > Meetings: the list, the one-page schedule form, the meeting page, edit / reschedule / cancel / reopen / duplicate / delete and the attendee table.
 * OWNER: ADMIN agent. Contract: docs/MEETINGS.md (sections 4, 5, 7, 10, 11, 12.1). ALL rules live in App\Services\Meetings\MeetingService: this class only turns
 * a request into the service's input, checks the ability, shows the result and hands queued messages over to the sending panel (MeetingMailController + admin-meetings-send.js:
 * after queueing we redirect to the meeting page with ?send=1, where the batch loop starts). It never writes the tables or the calendar entry itself.
 *
 * Abilities: meetings.manage on every method (also in the route group); meetings.bulk (administrators) when more than MeetingService::BULK_THRESHOLD people are added at once.
 * Personal data: attendee addresses are shown only here; audit entries carry ids and counts only (the service writes them).
 */
final class MeetingsController extends AdminController
{
    /**
     * Per platform: icon, how prominent each joining field is, and the help the schedule form shows. The one-line `help` is the wording of the contract (section 11).
     * link / passcode / dial / place: 'required' (needed before invitations go out), 'show' (relevant) or 'hide' (not offered).
     */
    public const PLATFORM_HELP = [
        'zoom' => ['icon' => 'video', 'link' => 'required', 'passcode' => 'show', 'dial' => 'show', 'place' => 'hide', 'placeholder' => 'https://zoom.us/j/1234567890',
            'help' => 'Paste the Zoom meeting link (Meetings > Schedule in Zoom, then Copy invitation link).',
            'steps' => ['Open Zoom and choose Meetings, then Schedule.', 'Save the meeting, then press Copy invitation link.', 'Paste the link here.']],
        'google_meet' => ['icon' => 'video', 'link' => 'required', 'passcode' => 'hide', 'dial' => 'show', 'place' => 'hide', 'placeholder' => 'https://meet.google.com/abc-defg-hij',
            'help' => 'In Google Meet choose New meeting > Create a meeting for later and paste the link.',
            'steps' => ['Open Google Meet and choose New meeting.', 'Choose Create a meeting for later.', 'Copy the link and paste it here.']],
        'teams' => ['icon' => 'video', 'link' => 'required', 'passcode' => 'show', 'dial' => 'show', 'place' => 'hide', 'placeholder' => 'https://teams.microsoft.com/l/meetup-join/...',
            'help' => 'In Teams open Calendar > New meeting, then Copy link.',
            'steps' => ['Open Teams and choose Calendar, then New meeting.', 'Save it, open it again and press Copy link.', 'Paste the link here.']],
        'jitsi' => ['icon' => 'globe', 'link' => 'required', 'passcode' => 'hide', 'dial' => 'hide', 'place' => 'hide', 'placeholder' => 'https://meet.jit.si/...',
            'help' => 'Press Generate a free private video room.',
            'steps' => ['Press the Generate button below.', 'The link appears here. Nothing is created until the first person opens it.', 'Jitsi Meet is a free service run by someone else; no account is needed.']],
        'whatsapp' => ['icon' => 'message-circle', 'link' => 'show', 'passcode' => 'hide', 'dial' => 'show', 'place' => 'hide', 'placeholder' => 'https://wa.me/256700000000 or a WhatsApp group call link',
            'help' => 'Paste the group call link or a https://wa.me/ link, or give the number in Dial-in.',
            'steps' => ['Create the call or group in WhatsApp and copy its link, or', 'type the WhatsApp number people should call in the box below.']],
        'phone' => ['icon' => 'phone', 'link' => 'hide', 'passcode' => 'show', 'dial' => 'required', 'place' => 'hide', 'placeholder' => '',
            'help' => 'Enter the number people must call in Dial-in.',
            'steps' => ['Type the phone number (with the country code, for example +256...) in Dial-in.', 'Add a PIN or access code as the passcode if the line needs one.']],
        'in_person' => ['icon' => 'map-pin', 'link' => 'hide', 'passcode' => 'hide', 'dial' => 'hide', 'place' => 'required', 'placeholder' => '',
            'help' => 'Enter the address.',
            'steps' => ['Type the address or place where people should come.', 'Add the room, floor or a landmark so nobody gets lost.']],
        'other' => ['icon' => 'link', 'link' => 'show', 'passcode' => 'show', 'dial' => 'show', 'place' => 'show', 'placeholder' => 'https://...',
            'help' => 'Paste any https link, or a phone number or address.',
            'steps' => ['Use this for any other way of meeting.', 'Give at least one of: a link, a phone number or an address.']],
    ];

    // ------------------------------------------------------------------ list

    public function index(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $available = MeetingService::available();
        $status = $request->string('status');
        $status = in_array($status, Meeting::STATUSES, true) ? $status : '';
        $q = mb_substr($request->string('q'), 0, 100);
        $when = $request->string('when');
        if (!in_array($when, ['upcoming', 'past', 'all'], true)) {
            $when = ($status === '' && $q === '') ? 'upcoming' : 'all';
        }
        $list = ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => 20];
        $counts = ['all' => 0, 'draft' => 0, 'scheduled' => 0, 'cancelled' => 0, 'completed' => 0];
        $newRequests = 0;
        if ($available) {
            $list = MeetingService::listMeetings(['status' => $status, 'q' => $q, 'when' => $when], max(1, $request->int('page', 1)), $this->perPage());
            $counts = MeetingService::statusCounts();
            $newRequests = MeetingService::newRequestCount();
        }
        return $this->render('admin/meetings/index', [
            'available' => $available, 'list' => $list, 'counts' => $counts, 'f' => ['status' => $status, 'q' => $q, 'when' => $when], 'newRequests' => $newRequests,
            'today' => MeetingTime::today(),
        ], ['title' => 'Meetings', 'nav' => 'meetings', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Meetings', null]]]);
    }

    // ------------------------------------------------------------------ the schedule form

    /** GET /admin/meetings/create  (?request=ID prefill from a meeting request, ?date=YYYY-MM-DD) */
    public function create(Request $request): Response
    {
        $this->authorize('meetings.manage');
        if (!$this->ready()) {
            return $this->redirect('admin/meetings');
        }
        $user = Auth::user();
        $rid = $request->int('request');
        $people = [];
        $ctx = null;
        if ($rid > 0) {
            $d = MeetingRequestService::scheduleDefaults($rid, $user);
            if ($d === null) {
                Flash::error('That meeting request no longer exists.');
                return $this->redirect('admin/meeting-requests');
            }
            $form = $d['form'];
            $people = $d['attendees'];
            $ctx = MeetingRequest::find($rid);
        } else {
            $date = $request->string('date');
            $form = MeetingService::defaults($user, (CivilDate::valid($date) && $date > MeetingTime::today()) ? ['date' => $date] : []);
        }
        return $this->formView('create', $form, null, $people, $ctx);
    }

    /** GET /admin/meetings/{id}/edit */
    public function edit(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->find($id);
        if (!in_array($m['status'], ['draft', 'scheduled'], true)) {
            Flash::error($m['status'] === 'cancelled' ? 'This meeting is cancelled. Reopen it as a draft to change it.' : 'A meeting that has taken place cannot be changed. Duplicate it instead.');
            return $this->redirect('admin/meetings/' . $id);
        }
        return $this->formView('edit', MeetingService::formData($m), $m, [], null);
    }

    /** @param array<string,mixed> $form  @param list<array<string,mixed>> $people */
    private function formView(string $mode, array $form, ?array $meeting, array $people, ?array $requestCtx): Response
    {
        $oldItems = old('items', null);
        if (is_string($oldItems) && $oldItems !== '') {
            $d = json_decode($oldItems, true);
            if (is_array($d)) {
                $people = $this->cleanChips($d);
            }
        }
        $groups = [];
        $oldBulk = old('bulk', null);
        if (is_array($oldBulk)) {
            $groups = $this->groupLabels(array_values(array_filter($oldBulk, 'is_string')));
        }
        $existing = [];
        if ($meeting !== null) {
            foreach (MeetingService::attendees((int) $meeting['id']) as $a) {
                $existing[] = ['name' => (string) $a['name'], 'email' => (string) $a['email'], 'source' => (string) $a['source'], 'state' => $this->inviteState($a)];
            }
        }
        $title = $mode === 'create' ? 'Schedule a meeting' : 'Edit meeting';
        $crumbs = [['Calendar & schedule', url('admin/calendar')], ['Meetings', url('admin/meetings')]];
        if ($meeting !== null) {
            $crumbs[] = [(string) $meeting['title'], url('admin/meetings/' . (int) $meeting['id'])];
            $crumbs[] = ['Edit', null];
        } else {
            $crumbs[] = ['Schedule a meeting', null];
        }
        return $this->render('admin/meetings/form', [
            'mode' => $mode, 'form' => $form, 'meeting' => $meeting, 'platforms' => self::PLATFORM_HELP, 'people' => $people, 'groups' => $groups, 'existing' => $existing,
            'requestCtx' => $requestCtx, 'canBulk' => can('meetings.bulk'), 'user' => Auth::user() ?? [], 'today' => MeetingTime::today(),
            'ownDraft' => $meeting === null || $meeting['status'] === 'draft', 'sentBefore' => $meeting !== null && $meeting['invitations_sent_at'] !== null,
        ], ['title' => $title, 'nav' => $mode === 'create' ? 'meeting_create' : 'meetings', 'breadcrumbs' => $crumbs]);
    }

    /** POST /admin/meetings  (intent = draft | send) */
    public function store(Request $request): Response
    {
        $this->authorize('meetings.manage');
        if (!$this->ready()) {
            return $this->redirect('admin/meetings');
        }
        $uid = $this->userId();
        $intent = $request->string('intent') === 'send' ? 'send' : 'draft';
        $rid = $request->int('request_id');
        $req = $rid > 0 ? MeetingRequest::find($rid) : null;
        $back = 'admin/meetings/create' . ($req !== null ? '?request=' . $rid : '');

        $in = $this->formInput($request);
        $p = $this->people($request);
        $errors = MeetingService::validate($in, null)['errors'] + $this->peopleErrors($p, 0);
        if ($errors !== []) {
            $this->fail($errors, $back);
        }
        $res = MeetingService::create($in, $uid);
        if (!$res['ok']) {
            $this->fail($res['errors'], $back);
        }
        $id = (int) $res['id'];
        if ($req !== null) {
            MeetingRequestService::attachMeeting($rid, $id, $uid);
        }
        $problem = $this->addPeople($id, $p, $uid);
        if ($problem !== '') {
            Flash::error($problem);
        }
        if ($intent === 'draft') {
            Flash::success('Draft saved. Nobody has been told about it yet.');
            return $this->redirect('admin/meetings/' . $id);
        }
        return $this->sendNow($request, $id, $p);
    }

    /** POST /admin/meetings/{id}  (intent = save | send; send on a scheduled meeting also queues the update for everyone) */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->find($id);
        $uid = $this->userId();
        $intent = $request->string('intent') === 'send' ? 'send' : 'save';
        $back = 'admin/meetings/' . $id . '/edit';
        if (!in_array($m['status'], ['draft', 'scheduled'], true)) {
            Flash::error('This meeting can no longer be changed.');
            return $this->redirect('admin/meetings/' . $id);
        }
        $in = $this->formInput($request);
        $existingEmails = array_map(static fn(array $a): string => (string) $a['email'], MeetingService::attendees($id));
        $p = $this->people($request, $existingEmails);
        $errors = MeetingService::validate($in, $m)['errors'] + $this->peopleErrors($p, count($existingEmails));
        if ($errors !== []) {
            $this->fail($errors, $back);
        }
        $res = MeetingService::update($id, $in, $uid);
        if (!$res['ok']) {
            $this->fail($res['errors'], $back);
        }
        $problem = $this->addPeople($id, $p, $uid);
        if ($problem !== '') {
            Flash::error($problem);
        }
        $fresh = (array) MeetingService::find($id);
        if ($intent === 'send') {
            if ($fresh['status'] === 'draft') {
                return $this->sendNow($request, $id, $p);
            }
            $q = MeetingService::queue($id, 'update_all');
            if ($q['ok'] && $q['queued'] > 0) {
                Flash::success('Saved. ' . $this->peopleLabel($q['queued']) . ' will get the updated invitation.');
                return $this->redirect('admin/meetings/' . $id . '?send=1#sending');
            }
            Flash::success('Saved. Nobody needed a new message.');
            return $this->redirect('admin/meetings/' . $id);
        }
        Flash::success('Saved.' . (!empty($res['update_suggested']) ? ' The people who already have the invitation still have the old details; use "Send the update" below to tell them.' : ''));
        return $this->redirect('admin/meetings/' . $id);
    }

    /**
     * "Save and send invitations" after the meeting and its people are saved. The meeting stays a draft (nothing is scheduled, nothing is sent) unless the number of people
     * the browser confirmed matches the number that will really be invited (with "all members" groups the browser can only count an upper bound).
     * @param array{items:list<array<string,mixed>>,invalid:list<array<string,string>>,groups:array<string,int>} $p
     */
    private function sendNow(Request $request, int $id, array $p): Response
    {
        $uid = $this->userId();
        // a draft that was cancelled and reopened has people who were invited before: they all get the invitation again (an update with a higher SEQUENCE)
        $again = MeetingService::find($id)['invitations_sent_at'] ?? null;
        $will = 0;
        foreach (MeetingService::attendees($id) as $a) {
            if ($a['rsvp'] === 'declined') {
                continue;
            }
            if ($again !== null || ($a['invite_sent_at'] === null && $a['invite_status'] === 'not_sent')) {
                $will++;
            }
        }
        $confirm = $request->post('confirm_count');
        $confirmed = is_string($confirm) && ctype_digit($confirm) ? (int) $confirm : null;
        $show = 'admin/meetings/' . $id;
        if ($will === 0) {
            Flash::warning('Saved as a draft. Add at least one person before you send invitations.');
            return $this->redirect($show);
        }
        $matches = $confirmed !== null && ($p['groups'] === [] ? $confirmed === $will : $confirmed >= $will);
        if (!$matches) {
            Flash::warning($confirmed === null
                ? 'Saved as a draft. To send, press "Send invitations" on this page so you can confirm how many people will be e-mailed.'
                : 'Saved as a draft. The list now has ' . $this->peopleLabel($will) . ', not the ' . $this->peopleLabel($confirmed) . ' you confirmed. Check the list, then press "Send invitations".');
            return $this->redirect($show);
        }
        $s = MeetingService::schedule($id, $uid);
        if (!$s['ok']) {
            FormState::flash(array_diff_key($s['errors'], ['_' => 1]), []);
            Flash::error('Saved as a draft. Before the invitations can go out: ' . implode(' ', array_values($s['errors'])));
            return $this->redirect('admin/meetings/' . $id . '/edit');
        }
        $q = MeetingService::queue($id, $again !== null ? 'update_all' : 'invite_new');
        if (!$q['ok'] || $q['queued'] === 0) {
            Flash::warning('The meeting is scheduled, but there was nobody to invite.');
            return $this->redirect($show);
        }
        Flash::success('The meeting is scheduled. Invitations are being sent to ' . $this->peopleLabel($q['queued']) . '.');
        return $this->redirect($show . '?send=1#sending');
    }

    // ------------------------------------------------------------------ the meeting page

    /** GET /admin/meetings/{id} */
    public function show(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        MeetingService::completeDue();
        $m = $this->find($id);
        $attendees = MeetingService::attendees($id);
        $seq = (int) $m['sequence'];
        $n = ['stale' => 0, 'unsent' => 0, 'failed' => 0, 'queued' => 0, 'invited' => 0, 'active' => 0, 'untold' => 0];
        foreach ($attendees as $a) {
            $n['active']++;
            if ($a['invite_sent_at'] !== null) {
                $n['invited']++;
                // a cancelled meeting bumped SEQUENCE: people whose last message is older have not got the cancellation yet (declined people get it too)
                if ($a['invite_status'] !== 'queued' && ($a['invite_sequence'] === null || (int) $a['invite_sequence'] < $seq)) {
                    $n['untold']++;
                }
            }
            if ($a['invite_status'] === 'failed') {
                $n['failed']++;
            }
            if ($a['invite_status'] === 'queued') {
                $n['queued']++;
            }
            if ($a['rsvp'] === 'declined') {
                continue;
            }
            if ($a['invite_sent_at'] === null && $a['invite_status'] === 'not_sent') {
                $n['unsent']++;
            } elseif ($a['invite_sent_at'] !== null && in_array($a['invite_status'], ['sent', 'failed'], true) && ($a['invite_sequence'] === null || (int) $a['invite_sequence'] < $seq)) {
                $n['stale']++;
            }
        }
        // a removed person's "no longer invited" notice that is still waiting counts as a message in progress too (it is not in the list above)
        $n['queued'] += (int) Database::value("SELECT COUNT(*) FROM meeting_attendees WHERE meeting_id = ? AND removed_at IS NOT NULL AND invite_status = 'queued'", [$id], 0);
        $entry = Database::one('SELECT id, start_date FROM calendar_entries WHERE id = ?', [(int) $m['calendar_entry_id']]);
        $fromRequest = MeetingRequest::findBy('meeting_id', $id);
        $detailRows = MeetingRenderer::details($m, 'page', true);
        return $this->render('admin/meetings/show', [
            'm' => $m, 'attendees' => $attendees, 'summary' => MeetingService::summary($id), 'n' => $n, 'entry' => $entry, 'fromRequest' => $fromRequest, 'detailRows' => $detailRows,
            'readiness' => $m['status'] === 'draft' ? MeetingService::readiness($m) : [], 'canBulk' => can('meetings.bulk'), 'user' => Auth::user() ?? [],
            'autoSend' => $request->string('send') === '1', 'today' => MeetingTime::today(),
            'reopened' => $m['status'] === 'draft' && $m['invitations_sent_at'] !== null,
            'canDelete' => $m['status'] === 'draft' && $m['invitations_sent_at'] === null,
        ], ['title' => (string) $m['title'], 'nav' => 'meetings', 'breadcrumbs' => [['Calendar & schedule', url('admin/calendar')], ['Meetings', url('admin/meetings')], [(string) $m['title'], null]]]);
    }

    // ------------------------------------------------------------------ status changes

    /** POST /admin/meetings/{id}/cancel  (reason, send_cancellation) */
    public function cancel(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $this->find($id);
        $res = MeetingService::cancel($id, $request->string('reason'), $this->userId());
        if (!$res['ok']) {
            Flash::error(implode(' ', $res['errors']));
            return $this->redirect('admin/meetings/' . $id);
        }
        $notify = (int) ($res['notify'] ?? 0);
        $queued = 0;
        if ($request->bool('send_cancellation') && $notify > 0) {
            $q = MeetingService::queue($id, 'cancel_all');
            $queued = $q['ok'] ? $q['queued'] : 0;
        }
        if ($queued > 0) {
            Flash::success('The meeting is cancelled. The cancellation is being sent to ' . $this->peopleLabel($queued) . '.');
            return $this->redirect('admin/meetings/' . $id . '?send=1#sending');
        }
        Flash::success('The meeting is cancelled.' . ($notify > 0 ? ' Nobody was e-mailed.' : ''));
        return $this->redirect('admin/meetings/' . $id);
    }

    /** POST /admin/meetings/{id}/reopen */
    public function reopen(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $this->find($id);
        $res = MeetingService::reopen($id, $this->userId());
        if ($res['ok']) {
            Flash::success('The meeting is a draft again. Change what you need, then send the invitations.');
            return $this->redirect('admin/meetings/' . $id . '/edit');
        }
        Flash::error(implode(' ', $res['errors']));
        return $this->redirect('admin/meetings/' . $id);
    }

    /** POST /admin/meetings/{id}/duplicate  (copy_attendees) */
    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $this->find($id);
        $res = MeetingService::duplicate($id, $this->userId(), $request->bool('copy_attendees'));
        if (!$res['ok']) {
            Flash::error(implode(' ', $res['errors']));
            return $this->redirect('admin/meetings/' . $id);
        }
        Flash::success('A copy was saved as a draft. Choose its date and time, then send the invitations.');
        return $this->redirect('admin/meetings/' . (int) $res['id'] . '/edit');
    }

    /** POST /admin/meetings/{id}/delete  (a draft that was never sent) */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $this->find($id);
        $res = MeetingService::delete($id);
        if (!$res['ok']) {
            Flash::error((string) ($res['error'] ?? 'The meeting could not be deleted.'));
            return $this->redirect('admin/meetings/' . $id);
        }
        Flash::success('The draft was deleted.');
        return $this->redirect('admin/meetings');
    }

    // ------------------------------------------------------------------ attendees

    /** GET /admin/meetings/{id}/attendees.csv */
    public function exportAttendees(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage', 'export.csv');
        $this->find($id);
        AuditLog::record('meeting.export', 'meeting', $id, 'Exported the attendee list', ['count' => count(MeetingService::attendees($id))]);
        return CsvExport::response('meeting-' . $id . '-attendees-' . date('Y-m-d') . '.csv', MeetingService::CSV_HEADER, MeetingService::csvRows($id));
    }

    /**
     * POST /admin/meetings/{id}/attendees  (emails = pasted text, items = JSON chips, bulk[] = members_all | members_type:ID, invite_now = 1)
     * A normal form post redirects with a flash; JSON clients get {ok, added, reactivated, existing, invalid[], total, queued}.
     */
    public function addAttendees(Request $request, int $id): Response
    {
        $this->authorize('meetings.manage');
        $m = $this->find($id);
        $uid = $this->userId();
        $json = $request->expectsJson();
        $existingEmails = array_map(static fn(array $a): string => (string) $a['email'], MeetingService::attendees($id));
        $p = $this->people($request, $existingEmails);
        $errors = $this->peopleErrors($p, count($existingEmails));
        $fail = function (string $msg) use ($json, $id): Response {
            if ($json) {
                return $this->json(['ok' => false, 'error' => 'validation', 'message' => $msg], 422);
            }
            Flash::error($msg);
            return $this->redirect('admin/meetings/' . $id . '#people');
        };
        if ($errors !== []) {
            return $fail(implode(' ', array_values($errors)));
        }
        if ($p['items'] === []) {
            return $fail($p['invalid'] !== [] ? 'None of those addresses could be used.' : 'Type or choose at least one person to add.');
        }
        $res = MeetingService::addAttendees($id, $p['items'], $uid, ['allow_bulk' => can('meetings.bulk')]);
        if (!$res['ok']) {
            return $fail(implode(' ', $res['errors']));
        }
        $queued = 0;
        if ($request->bool('invite_now') && $m['status'] === 'scheduled' && ($res['added'] + $res['reactivated']) > 0) {
            $q = MeetingService::queue($id, 'invite_new');
            $queued = $q['ok'] ? $q['queued'] : 0;
        }
        $new = $res['added'] + $res['reactivated'];
        $msg = $new === 0 ? 'Nobody new: everyone was already on the list.' : 'Added ' . $this->peopleLabel($new) . '.';
        if ($res['existing'] > 0 && $new > 0) {
            $msg .= ' ' . $this->peopleLabel($res['existing']) . ' already on the list.';
        }
        if ($queued > 0) {
            $msg .= ' The invitation is being sent to ' . ($queued === 1 ? 'them' : 'the new people') . '.';
        } elseif ($new > 0 && $m['status'] === 'scheduled') {
            $msg .= ' They have not been invited yet.';
        }
        if ($json) {
            return $this->json(['ok' => true, 'added' => $res['added'], 'reactivated' => $res['reactivated'], 'existing' => $res['existing'], 'invalid' => $p['invalid'],
                'total' => count(MeetingService::attendees($id)), 'queued' => $queued, 'message' => $msg]);
        }
        Flash::success($msg);
        if ($p['invalid'] !== []) {
            Flash::warning(count($p['invalid']) . ' typed ' . (count($p['invalid']) === 1 ? 'address was' : 'addresses were') . ' not valid and left out.');
        }
        return $this->redirect('admin/meetings/' . $id . ($queued > 0 ? '?send=1#sending' : '#people'));
    }

    /** POST /admin/meetings/{id}/attendees/{aid}/remove  (send_cancellation) */
    public function removeAttendee(Request $request, int $id, int|string $aid): Response
    {
        $this->authorize('meetings.manage');
        $this->find($id);
        $aid = (int) $aid;                       // {aid} is a custom placeholder: the router passes it as a string
        $res = MeetingService::removeAttendee($id, $aid, $request->bool('send_cancellation'), $this->userId());
        if (!$res['ok']) {
            Flash::error((string) ($res['error'] ?? 'That person could not be removed.'));
            return $this->redirect('admin/meetings/' . $id . '#people');
        }
        if (!empty($res['queued_cancel'])) {
            Flash::success('The person was removed. They are being told they are no longer invited.');
            return $this->redirect('admin/meetings/' . $id . '?send=1#sending');
        }
        Flash::success('The person was removed from the meeting.');
        return $this->redirect('admin/meetings/' . $id . '#people');
    }

    // ------------------------------------------------------------------ helpers

    /** The meeting or a 404. @return array<string,mixed> */
    private function find(int $id): array
    {
        $m = MeetingService::available() ? MeetingService::find($id) : null;
        if ($m === null) {
            $this->abort(404, 'Meeting not found');
        }
        return $m;
    }

    /** Are the tables there? (A live database imported late must not 500.) */
    private function ready(): bool
    {
        if (!MeetingService::available()) {
            Flash::error('The meeting tables are missing from the database. Import database/lumina-live-import.sql first.');
            return false;
        }
        return true;
    }

    private function peopleLabel(int $n): string
    {
        return $n . ($n === 1 ? ' person' : ' people');
    }

    /** What the posted schedule form says, limited to the fields the service knows. @return array<string,mixed> */
    private function formInput(Request $request): array
    {
        $out = [];
        foreach (['title', 'date', 'start_time', 'duration', 'end_time', 'platform', 'join_url', 'passcode', 'dial_in', 'location', 'description', 'organiser_name', 'organiser_email',
                     'visibility', 'show_join_link_public', 'reminder_24h', 'reminder_1h', 'allow_rsvp'] as $f) {
            $v = $request->post($f);
            $out[$f] = is_scalar($v) ? trim((string) $v) : '';
        }
        // an unchecked box posts nothing: the form always posts a 0 first, so a missing key means "keep the default" only for API-style callers
        foreach (['show_join_link_public', 'reminder_24h', 'reminder_1h', 'allow_rsvp'] as $f) {
            if (!$request->has($f)) {
                $out[$f] = '0';
            }
        }
        return $out;
    }

    /**
     * The people of a post: JSON chips (items), leftover pasted text (emails) and "all members" groups (bulk[]), de-duplicated by address.
     * @param list<string> $skip addresses already on the meeting (not counted again)
     * @return array{items:list<array<string,mixed>>,invalid:list<array{input:string,reason:string}>,groups:array<string,int>,too_big:bool}
     */
    private function people(Request $request, array $skip = []): array
    {
        $skipSet = array_fill_keys(array_map('strtolower', $skip), true);
        $items = [];
        $raw = $request->post('items', '');
        $list = [];
        if (is_string($raw) && $raw !== '' && strlen($raw) < 800000) {
            $d = json_decode($raw, true);
            $list = is_array($d) ? $d : [];
        } elseif (is_array($raw)) {
            $list = $raw;
        }
        foreach ($this->cleanChips($list) as $c) {
            if (!isset($skipSet[$c['email']])) {
                $items[$c['email']] ??= $c;
            }
        }
        $invalid = [];
        $tooBig = false;
        $text = $request->post('emails', '');
        if (is_string($text) && trim($text) !== '') {
            if (strlen($text) > 200000) {
                $tooBig = true;
            } else {
                $parsed = InviteeParser::parse($text, array_keys($skipSet + $items));
                foreach ($parsed['valid'] as $v) {
                    $items[$v['email']] ??= ['email' => $v['email'], 'name' => $v['name'], 'source' => 'manual', 'member_id' => null, 'user_id' => null];
                }
                $invalid = $parsed['invalid'];
            }
        }
        $groups = [];
        foreach ((array) $request->post('bulk', []) as $spec) {
            if (!is_string($spec)) {
                continue;
            }
            $b = MeetingService::bulkItems($spec);
            if ($b === null) {
                continue;
            }
            $groups[$spec] = count($b['items']);
            foreach ($b['items'] as $i) {
                if (!isset($skipSet[$i['email']])) {
                    $items[$i['email']] ??= ['email' => $i['email'], 'name' => $i['name'], 'source' => 'member', 'member_id' => (int) $i['member_id'], 'user_id' => null];
                }
            }
        }
        return ['items' => array_values($items), 'invalid' => $invalid, 'groups' => $groups, 'too_big' => $tooBig];
    }

    /** Errors about the people of a post, keyed like form fields. @param array<string,mixed> $p */
    private function peopleErrors(array $p, int $already): array
    {
        $e = [];
        if ($p['too_big']) {
            $e['emails'] = 'That is too much text to paste at once (at most 200 KB).';
        } elseif ($p['invalid'] !== []) {
            $bad = array_map(static fn(array $i): string => mb_substr((string) $i['input'], 0, 60), array_slice($p['invalid'], 0, 5));
            $e['emails'] = 'These do not look like e-mail addresses: ' . implode(', ', $bad) . (count($p['invalid']) > 5 ? ' and ' . (count($p['invalid']) - 5) . ' more' : '') . '. Fix or remove them.';
        }
        $new = count($p['items']);
        if ($already + $new > MeetingService::MAX_ATTENDEES) {
            $e['invitees'] = 'A meeting can have at most ' . MeetingService::MAX_ATTENDEES . ' people (' . $already . ' already added, ' . $new . ' more chosen).';
        } elseif ($new > MeetingService::BULK_THRESHOLD && !can('meetings.bulk')) {
            $e['invitees'] = 'Adding more than ' . MeetingService::BULK_THRESHOLD . ' people at once needs an administrator.';
        }
        return $e;
    }

    /** Save the people of a post on a meeting. @return string an error message ('' = fine) */
    private function addPeople(int $id, array $p, ?int $uid): string
    {
        if ($p['items'] === []) {
            return '';
        }
        $res = MeetingService::addAttendees($id, $p['items'], $uid, ['allow_bulk' => can('meetings.bulk')]);
        return $res['ok'] ? '' : 'The meeting was saved, but the people could not be added: ' . implode(' ', $res['errors']);
    }

    /**
     * Chips from JSON (browser) into clean items. Only the shape is trusted; for members and staff the service reads the real name and address by id.
     * @param array<int|string,mixed> $list
     * @return list<array{email:string,name:string,source:string,member_id:?int,user_id:?int}>
     */
    private function cleanChips(array $list): array
    {
        $out = [];
        foreach (array_slice($list, 0, 2000) as $it) {
            if (!is_array($it)) {
                continue;
            }
            $email = InviteeParser::cleanAddress(is_scalar($it['email'] ?? null) ? (string) $it['email'] : '');
            if ($email === '' || !Mailer::validEmail($email) || strlen($email) > InviteeParser::MAX_EMAIL) {
                continue;
            }
            $source = is_string($it['source'] ?? null) && in_array($it['source'], MeetingAttendee::SOURCES, true) ? $it['source'] : 'manual';
            $out[] = [
                'email' => $email, 'name' => InviteeParser::cleanName(is_scalar($it['name'] ?? null) ? (string) $it['name'] : ''), 'source' => $source,
                'member_id' => $source === 'member' && !empty($it['member_id']) ? (int) $it['member_id'] : null,
                'user_id' => $source === 'user' && !empty($it['user_id']) ? (int) $it['user_id'] : null,
            ];
        }
        return $out;
    }

    /** @param list<string> $specs @return list<array{spec:string,label:string,count:int}> */
    private function groupLabels(array $specs): array
    {
        $types = [];
        foreach (MeetingService::memberTypeCounts() as $t) {
            $types[$t['id']] = $t['name'];
        }
        $out = [];
        foreach (array_unique($specs) as $spec) {
            $b = MeetingService::bulkItems($spec);
            if ($b === null) {
                continue;
            }
            $label = $spec === 'members_all' ? 'All approved members' : 'Members: ' . ($types[(int) substr($spec, strlen('members_type:'))] ?? 'one type');
            $out[] = ['spec' => $spec, 'label' => $label, 'count' => count($b['items'])];
        }
        return $out;
    }

    /** Short state of an attendee for the "already on the list" chips of the edit form. */
    private function inviteState(array $a): string
    {
        if ($a['rsvp'] === 'declined') {
            return 'Declined';
        }
        return match ((string) $a['invite_status']) {
            'sent' => 'Invited', 'queued' => 'Waiting to send', 'failed' => 'Could not be sent', default => 'Not invited yet',
        };
    }
}
