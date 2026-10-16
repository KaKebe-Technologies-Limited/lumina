<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Event;
use App\Models\EventRegistration;

/**
 * Admin > Events > Registrations (/admin/events/{event}/registrations). OWNER: B8 "org-admin".
 *
 * Status workflow (enforced server-side, single and bulk):
 *   registered -> attended | cancelled        attended -> registered (undo)        cancelled -> registered (reinstate)
 * Reinstating or adding a registration is refused when the event is full (capacity counts registered + attended).
 * Abilities: inbox.manage (view/change), export.csv (CSV), inbox.delete (delete personal data; admin only).
 */
final class EventRegistrationController extends OrgAdminController
{
    private const FLOW = [
        'registered' => ['attended', 'cancelled'],
        'attended'   => ['registered'],
        'cancelled'  => ['registered'],
    ];

    public const STATUS_LABELS = ['registered' => 'Registered', 'attended' => 'Attended', 'cancelled' => 'Cancelled'];

    protected function area(): string
    {
        return 'admin/events';
    }

    /** @return array<string,string[]> */
    private function sortable(): array
    {
        return [
            'registered'   => ['created_at'],
            'name'         => ['name'],
            'email'        => ['email'],
            'organisation' => ['organisation', 'name'],
            'status'       => ['status', 'name'],
        ];
    }

    public function index(Request $request, string $event): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        [$where, $params, $q, $status] = $this->filters($ev);
        $st = $this->listState($this->sortable(), 'registered', 'desc');

        $pager = $this->paged('event_registrations' . $this->whereSql($where), $params, '*', $this->orderSql($this->sortable(), $st['sort'], $st['dir']));
        return $this->render('admin/events/registrations', [
            'event'    => $ev,
            'pager'    => $pager,
            'q'        => $q,
            'status'   => $status,
            'sort'     => $st['sort'],
            'dir'      => $st['dir'],
            'filtered' => $q !== '' || $status !== '',
            'stats'    => $this->stats((int) $ev['id']),
            'flow'     => self::FLOW,
            'labels'   => self::STATUS_LABELS,
        ], ['title' => 'Registrations', 'nav' => 'events', 'breadcrumbs' => [['Events', url('admin/events')], [$ev['title'], url('admin/events/' . $ev['id'] . '/edit')], ['Registrations', null]]]);
    }

    public function export(Request $request, string $event): Response
    {
        $this->authorize('inbox.manage', 'export.csv');
        $ev = $this->event($event);
        [$where, $params] = $this->filters($ev);
        $st = $this->listState($this->sortable(), 'registered', 'desc');
        $rows = Database::all(
            'SELECT name, email, phone, organisation, status, created_at, notes FROM event_registrations' . $this->whereSql($where)
            . ' ORDER BY ' . $this->orderSql($this->sortable(), $st['sort'], $st['dir']),
            $params
        );
        $out = [];
        foreach ($rows as $r) {
            $out[] = [$r['name'], $r['email'], $r['phone'], $r['organisation'], self::STATUS_LABELS[$r['status']] ?? $r['status'], $r['created_at'], $r['notes']];
        }
        $this->audit('event_registration.export', 'event', (int) $ev['id'], 'Exported ' . count($out) . ' registrations for "' . $ev['title'] . '"', ['count' => count($out)]);
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower((string) $ev['slug'])) ?: 'event';
        return CsvExport::response($slug . '-registrations-' . date('Y-m-d') . '.csv', ['Name', 'Email', 'Phone', 'Organisation', 'Status', 'Registered on', 'Notes'], $out);
    }

    /** Add an attendee by hand (walk-ins, phone sign-ups). */
    public function store(Request $request, string $event): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        $to = 'admin/events/' . $ev['id'] . '/registrations';
        $errors = [];
        $data = $this->collect((int) $ev['id'], null, $errors);
        if ($errors === [] && ($left = $this->seatsLeft($ev)) !== null && $left < 1) {
            $errors['name'] = 'This event is full (capacity ' . (int) $ev['capacity'] . '). Increase the capacity on the event first.';
        }
        if ($errors !== []) {
            $this->fail($errors, $to);
        }
        $id = EventRegistration::create($data + ['event_id' => (int) $ev['id'], 'status' => 'registered']);
        $this->audit('event_registration.create', 'event_registration', $id, 'Added "' . $data['name'] . '" to "' . $ev['title'] . '"', ['event' => (int) $ev['id']]);
        Flash::success($data['name'] . ' was registered for this event.');
        return $this->redirect($to);
    }

    public function show(Request $request, string $event, int $id): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        $reg = $this->registration($ev, $id);
        return $this->render('admin/events/registration', [
            'event'  => $ev,
            'reg'    => $reg,
            'flow'   => self::FLOW,
            'labels' => self::STATUS_LABELS,
        ], ['title' => 'Registration', 'nav' => 'events', 'breadcrumbs' => [
            ['Events', url('admin/events')], [$ev['title'], url('admin/events/' . $ev['id'] . '/edit')],
            ['Registrations', url('admin/events/' . $ev['id'] . '/registrations')], [$reg['name'], null],
        ]]);
    }

    public function update(Request $request, string $event, int $id): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        $reg = $this->registration($ev, $id);
        $to = 'admin/events/' . $ev['id'] . '/registrations/' . $id;
        $errors = [];
        $data = $this->collect((int) $ev['id'], $id, $errors);
        if ($errors !== []) {
            $this->fail($errors, $to);
        }
        EventRegistration::update($id, $data);
        $changed = [];
        foreach (['name', 'email', 'phone', 'organisation', 'notes'] as $f) {
            if ((string) ($reg[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $this->audit('event_registration.update', 'event_registration', $id, 'Edited registration of "' . $data['name'] . '"', ['event' => (int) $ev['id'], 'fields' => $changed]);
        Flash::success('Registration saved.');
        return $this->redirect('admin/events/' . $ev['id'] . '/registrations');
    }

    /** Change one registration's status (POST status=attended|registered|cancelled). */
    public function status(Request $request, string $event, int $id): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        $reg = $this->registration($ev, $id);
        $to = $request->string('status');
        $left = $this->seatsLeft($ev);
        $problem = $this->transition($reg, $to, $left);
        if ($problem !== null) {
            Flash::error($problem);
        } else {
            $this->audit('event_registration.status', 'event_registration', $id, '"' . $reg['name'] . '" marked ' . $to . ' for "' . $ev['title'] . '"', ['event' => (int) $ev['id'], 'from' => $reg['status'], 'to' => $to]);
            Flash::success($reg['name'] . ' is now marked as ' . strtolower(self::STATUS_LABELS[$to]) . '.');
        }
        return Response::redirect($this->returnUrl('admin/events/' . $ev['id'] . '/registrations'));
    }

    public function bulk(Request $request, string $event): Response
    {
        $this->authorize('inbox.manage');
        $ev = $this->event($event);
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl('admin/events/' . $ev['id'] . '/registrations'));
        if ($ids === []) {
            Flash::warning('Select at least one registration first.');
            return $back;
        }
        if ($action === 'delete') {
            $this->authorize('inbox.delete');
            $n = 0;
            foreach ($ids as $id) {
                $reg = EventRegistration::first(['id' => $id, 'event_id' => (int) $ev['id']]);
                if ($reg === null) {
                    continue;
                }
                EventRegistration::delete($id);
                $this->audit('event_registration.delete', 'event_registration', $id, 'Deleted registration of "' . $reg['name'] . '" for "' . $ev['title'] . '"', ['event' => (int) $ev['id']]);
                $n++;
            }
            Flash::success($this->plural($n, 'registration', 'registrations') . ' deleted.');
            return $back;
        }
        $targets = ['attended' => 'attended', 'registered' => 'registered', 'cancelled' => 'cancelled'];
        if (!isset($targets[$action])) {
            Flash::error('Choose an action to apply to the selected registrations.');
            return $back;
        }
        $left = $this->seatsLeft($ev);
        $done = 0;
        $skipped = 0;
        $full = false;
        foreach ($ids as $id) {
            $reg = EventRegistration::first(['id' => $id, 'event_id' => (int) $ev['id']]);
            if ($reg === null) {
                continue;
            }
            $problem = $this->transition($reg, $action, $left);
            if ($problem === null) {
                $done++;
                if ($reg['status'] === 'cancelled' && $left !== null) {
                    $left--;                                   // a reinstated registration takes a seat
                } elseif ($action === 'cancelled' && $left !== null) {
                    $left++;
                }
            } else {
                $skipped++;
                $full = $full || str_contains($problem, 'full');
            }
        }
        if ($done > 0) {
            $this->audit('event_registration.bulk', 'event', (int) $ev['id'], 'Marked ' . $done . ' registrations as ' . $action . ' for "' . $ev['title'] . '"', ['ids' => $ids, 'to' => $action]);
            Flash::success($this->plural($done, 'registration', 'registrations') . ' marked as ' . $action . '.');
        }
        if ($skipped > 0) {
            Flash::warning($this->plural($skipped, 'registration was', 'registrations were') . ' skipped because that status change is not allowed for them' . ($full ? ' or the event is full.' : '.'));
        }
        return $back;
    }

    public function destroy(Request $request, string $event, int $id): Response
    {
        $this->authorize('inbox.delete');
        $ev = $this->event($event);
        $reg = $this->registration($ev, $id);
        EventRegistration::delete($id);
        $this->audit('event_registration.delete', 'event_registration', $id, 'Deleted registration of "' . $reg['name'] . '" for "' . $ev['title'] . '"', ['event' => (int) $ev['id']]);
        Flash::success('Registration of ' . $reg['name'] . ' was deleted.');
        return $this->redirect('admin/events/' . $ev['id'] . '/registrations');
    }

    // ------------------------------------------------------------------ internals

    /** @return array<string,mixed> */
    private function event(string $event): array
    {
        if (!ctype_digit($event)) {
            throw new HttpException(404, 'Not found');
        }
        return Event::findOrFail((int) $event);
    }

    /** @return array<string,mixed> */
    private function registration(array $event, int $id): array
    {
        return EventRegistration::first(['id' => $id, 'event_id' => (int) $event['id']]) ?? throw new HttpException(404, 'Not found');
    }

    /**
     * WHERE parts for the list / CSV of one event (search + status filter).
     * @return array{0:string[],1:array<int,mixed>,2:string,3:string}
     */
    private function filters(array $event): array
    {
        $where = ['event_id = ?'];
        $params = [(int) $event['id']];
        $q = mb_substr($this->request->string('q'), 0, 100);
        [$sql, $sp] = $this->searchSql(['name', 'email', 'phone', 'organisation'], $q);
        if ($sql !== '') {
            $where[] = $sql;
            $params = array_merge($params, $sp);
        }
        $status = $this->request->string('status');
        if (isset(self::STATUS_LABELS[$status])) {
            $where[] = 'status = ?';
            $params[] = $status;
        } else {
            $status = '';
        }
        return [$where, $params, $q, $status];
    }

    /** @return array{registered:int,attended:int,cancelled:int,active:int,total:int,capacity:?int,left:?int} */
    private function stats(int $eventId): array
    {
        $out = ['registered' => 0, 'attended' => 0, 'cancelled' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM event_registrations WHERE event_id = ? GROUP BY status', [$eventId]) as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
        }
        $out['active'] = $out['registered'] + $out['attended'];
        $out['total'] = $out['active'] + $out['cancelled'];
        $cap = Database::value('SELECT capacity FROM events WHERE id = ?', [$eventId]);
        $out['capacity'] = $cap === null ? null : (int) $cap;
        $out['left'] = $out['capacity'] === null ? null : max(0, $out['capacity'] - $out['active']);
        return $out;
    }

    /** Free seats (null = unlimited). Capacity counts registered + attended. */
    private function seatsLeft(array $event): ?int
    {
        if ($event['capacity'] === null || $event['capacity'] === '') {
            return null;
        }
        $active = (int) Database::value("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND status <> 'cancelled'", [(int) $event['id']], 0);
        return max(0, (int) $event['capacity'] - $active);
    }

    /**
     * Apply a status change if the workflow allows it. Returns null on success or a message explaining why not.
     * @param int|null $seatsLeft free seats before this change (null = unlimited)
     */
    private function transition(array $reg, string $to, ?int $seatsLeft): ?string
    {
        if (!isset(self::STATUS_LABELS[$to])) {
            return 'Unknown status.';
        }
        if (!in_array($to, self::FLOW[$reg['status']] ?? [], true)) {
            return '"' . $reg['name'] . '" is already ' . strtolower(self::STATUS_LABELS[$reg['status']]) . ' or cannot move to ' . strtolower(self::STATUS_LABELS[$to]) . '.';
        }
        if ($reg['status'] === 'cancelled' && $to === 'registered' && $seatsLeft !== null && $seatsLeft < 1) {
            return 'The event is full, so "' . $reg['name'] . '" cannot be reinstated. Increase the capacity on the event first.';
        }
        EventRegistration::update((int) $reg['id'], ['status' => $to]);
        return null;
    }

    /**
     * Validate the attendee form (add + edit). Errors are appended to $errors; returns the columns to save.
     * @param array<string,string> $errors
     * @return array<string,mixed>
     */
    private function collect(int $eventId, ?int $ignoreId, array &$errors): array
    {
        $r = $this->request;
        $v = Validator::make($r->all(), [
            'name'         => 'required|max:150',
            'email'        => 'required|email|max:190',
            'phone'        => 'nullable|phone|max:40',
            'organisation' => 'nullable|max:190',
            'notes'        => 'nullable|max:2000',
        ], [
            'name.required'  => 'Enter the attendee\'s name.',
            'email.required' => 'Enter an email address.',
        ], ['name' => 'Name', 'phone' => 'Phone', 'organisation' => 'Organisation', 'notes' => 'Notes']);
        $errors = $v->errors();
        $this->requireText($errors, 'name', $this->clean($r->string('name')), "Enter the attendee's name.");
        if (!isset($errors['email'])) {
            $dup = (int) Database::value(
                'SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND email = ?' . ($ignoreId !== null ? ' AND id <> ?' : ''),
                $ignoreId !== null ? [$eventId, $r->string('email'), $ignoreId] : [$eventId, $r->string('email')],
                0
            );
            if ($dup > 0) {
                $errors['email'] = 'That email address is already registered for this event.';
            }
        }
        return [
            'name'         => $this->clean($r->string('name')),
            'email'        => $r->string('email'),
            'phone'        => $this->clean($r->string('phone')) ?: null,
            'organisation' => $this->clean($r->string('organisation')) ?: null,
            'notes'        => $this->clean($r->string('notes'), true) ?: null,
        ];
    }
}
