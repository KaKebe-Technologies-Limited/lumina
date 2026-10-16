<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Slug;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\Event;

/**
 * Admin > Events (/admin/events). OWNER: B8 "org-admin". Registrations live in EventRegistrationController.
 * List with upcoming/past tabs, search, filters, sort, pagination and bulk actions; create/edit with rich-text body,
 * image + alt text, datetime pickers, capacity and a registration on/off switch; delete (registrations cascade).
 * Public output: /events and /events/{slug} (B7).
 */
final class EventController extends OrgAdminController
{
    protected function area(): string
    {
        return 'admin/events';
    }

    /** @return array<string,string[]> */
    private function sortable(): array
    {
        return [
            'date'          => ['start_at'],
            'title'         => ['title'],
            'registrations' => ['reg_count', 'start_at'],
            'status'        => ['is_active', 'start_at'],
            'added'         => ['created_at'],
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $when = $request->string('when');
        $when = in_array($when, ['upcoming', 'past'], true) ? $when : '';
        $defaultDir = $when === 'upcoming' ? 'asc' : 'desc';
        $st = $this->listState($this->sortable(), 'date', $defaultDir);

        $where = [];
        $params = [];
        [$sql, $sp] = $this->searchSql(['title', 'location', 'summary'], $st['q']);
        if ($sql !== '') {
            $where[] = $sql;
            $params = array_merge($params, $sp);
        }
        if ($when === 'upcoming') {
            $where[] = 'COALESCE(end_at, start_at) >= NOW()';
        } elseif ($when === 'past') {
            $where[] = 'COALESCE(end_at, start_at) < NOW()';
        }
        $status = $request->string('status');
        if ($status === 'active' || $status === 'inactive') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = '';
        }
        $reg = $request->string('reg');
        if ($reg === 'open' || $reg === 'closed') {
            $where[] = 'registration_enabled = ?';
            $params[] = $reg === 'open' ? 1 : 0;
        } else {
            $reg = '';
        }

        $select = 'events.*, '
            . "(SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = events.id AND r.status <> 'cancelled') AS reg_count, "
            . '(SELECT COUNT(*) FROM event_registrations r2 WHERE r2.event_id = events.id) AS reg_total';
        $pager = $this->paged('events' . $this->whereSql($where), $params, $select, $this->orderSql($this->sortable(), $st['sort'], $st['dir']));

        $counts = Database::one(
            'SELECT COUNT(*) AS total, COALESCE(SUM(COALESCE(end_at, start_at) >= NOW()), 0) AS upcoming, COALESCE(SUM(COALESCE(end_at, start_at) < NOW()), 0) AS past FROM events'
        ) ?? ['total' => 0, 'upcoming' => 0, 'past' => 0];

        return $this->render('admin/events/index', [
            'pager'    => $pager,
            'q'        => $st['q'],
            'sort'     => $st['sort'],
            'dir'      => $st['dir'],
            'when'     => $when,
            'status'   => $status,
            'reg'      => $reg,
            'filtered' => $st['q'] !== '' || $status !== '' || $reg !== '',
            'counts'   => array_map('intval', $counts),
            'now'      => date('Y-m-d H:i:s'),
        ], ['title' => 'Events', 'nav' => 'events', 'breadcrumbs' => [['Events', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        return $this->render('admin/events/create', [
            'event' => ['is_active' => 1, 'registration_enabled' => 0, 'start_at' => '', 'end_at' => '', 'capacity' => ''],
        ], ['title' => 'Add event', 'nav' => 'events', 'breadcrumbs' => [['Events', url('admin/events')], ['Add', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $to = 'admin/events/create';
        $data = $this->collect(null, $to);
        try {
            $data['image'] = Uploader::resolveImageField($request, 'image', null);
        } catch (UploadException $e) {
            $this->fail(['image' => $e->getMessage()], $to);
        }
        if ($data['image'] === null) {
            $data['image_alt'] = null;
        }
        $id = Event::create($data);
        $this->audit('event.create', 'event', $id, 'Created event "' . $data['title'] . '"', ['start' => $data['start_at'], 'active' => (bool) $data['is_active']]);
        Flash::success('Event "' . $data['title'] . '" was created.' . ($data['is_active'] ? '' : ' It is hidden from the website until you activate it.'));
        return $this->redirect('admin/events');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $event = Event::findOrFail($id);
        return $this->render('admin/events/edit', [
            'event' => $event,
            'stats' => $this->registrationStats($id),
        ], ['title' => 'Edit event', 'nav' => 'events', 'breadcrumbs' => [['Events', url('admin/events')], [$event['title'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Event::findOrFail($id);
        $to = 'admin/events/' . $id . '/edit';
        $data = $this->collect($row, $to);
        try {
            $data['image'] = Uploader::resolveImageField($request, 'image', $row['image'] ?? null);
        } catch (UploadException $e) {
            $this->fail(['image' => $e->getMessage()], $to);
        }
        if ($data['image'] === null) {
            $data['image_alt'] = null;
        }
        Event::update($id, $data);

        $changed = [];
        foreach (['title', 'slug', 'summary', 'body', 'image', 'image_alt', 'start_at', 'end_at', 'location', 'registration_url', 'registration_enabled', 'capacity', 'is_active', 'seo_title', 'seo_description'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $this->audit('event.update', 'event', $id, 'Edited event "' . $data['title'] . '"', ['fields' => $changed]);
        Flash::success('Event "' . $data['title'] . '" was saved.');
        if ($data['capacity'] !== null) {
            $active = (int) Database::value("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND status <> 'cancelled'", [$id], 0);
            if ($active > (int) $data['capacity']) {
                Flash::warning('This event now has ' . $active . ' registrations but a capacity of ' . (int) $data['capacity'] . '. Existing registrations are kept; new ones should be blocked.');
            }
        }
        return $this->redirect('admin/events');
    }

    /** Show / hide an event on the website (POST). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Event::findOrFail($id);
        $new = (int) $row['is_active'] === 1 ? 0 : 1;
        Event::update($id, ['is_active' => $new]);
        $this->audit('event.' . ($new ? 'activate' : 'deactivate'), 'event', $id, ($new ? 'Showed' : 'Hid') . ' event "' . $row['title'] . '"');
        Flash::success('"' . $row['title'] . '" is now ' . ($new ? 'shown on' : 'hidden from') . ' the website.');
        return Response::redirect($this->returnUrl());
    }

    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl());
        if ($ids === []) {
            Flash::warning('Select at least one event first.');
            return $back;
        }
        $map = [
            'activate'   => ['is_active', 1, 'shown on the website'],
            'deactivate' => ['is_active', 0, 'hidden from the website'],
            'open'       => ['registration_enabled', 1, 'opened for registration'],
            'close'      => ['registration_enabled', 0, 'closed for registration'],
        ];
        if (isset($map[$action])) {
            [$col, $val, $word] = $map[$action];
            $this->setFlag('events', $col, $val, $ids);
            $this->audit('event.bulk', 'event', null, 'Bulk "' . $action . '" on ' . count($ids) . ' events', ['ids' => $ids, 'action' => $action]);
            Flash::success($this->plural(count($ids), 'event', 'events') . ' ' . $word . '.');
        } elseif ($action === 'delete') {
            $this->authorize('content.delete');
            $n = $this->deleteMany($ids);
            Flash::success($this->plural($n, 'event', 'events') . ' deleted.');
        } else {
            Flash::error('Choose an action to apply to the selected events.');
        }
        return $back;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Event::findOrFail($id);
        $this->deleteMany([$id]);
        Flash::success('Event "' . $row['title'] . '" was deleted.');
        return $this->redirect('admin/events');
    }

    // ------------------------------------------------------------------ internals

    /** @return array{registered:int,attended:int,cancelled:int,active:int,total:int} */
    private function registrationStats(int $eventId): array
    {
        $out = ['registered' => 0, 'attended' => 0, 'cancelled' => 0];
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM event_registrations WHERE event_id = ? GROUP BY status', [$eventId]) as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
        }
        $out['active'] = $out['registered'] + $out['attended'];
        $out['total'] = $out['active'] + $out['cancelled'];
        return $out;
    }

    /** @param int[] $ids */
    private function deleteMany(array $ids): int
    {
        $n = 0;
        foreach ($ids as $id) {
            $row = Event::find($id);
            if ($row === null) {
                continue;
            }
            $regs = (int) Database::value('SELECT COUNT(*) FROM event_registrations WHERE event_id = ?', [$id], 0);
            Event::delete($id);                                   // registrations are removed by the foreign key (ON DELETE CASCADE)
            Uploader::delete($row['image'] ?? null);
            $this->audit('event.delete', 'event', $id, 'Deleted event "' . $row['title'] . '"', ['registrations_deleted' => $regs]);
            $n++;
        }
        return $n;
    }

    /**
     * Validate the form. Returns the columns to save (the image is resolved by the caller after validation).
     * @param array<string,mixed>|null $row current row when editing
     * @return array<string,mixed>
     */
    private function collect(?array $row, string $redirectTo): array
    {
        $r = $this->request;
        $id = $row !== null ? (int) $row['id'] : null;
        $v = Validator::make($r->all(), [
            'title'           => 'required|max:220',
            'summary'         => 'nullable|max:500',
            'start_at'        => 'required|datetime',
            'end_at'          => 'nullable|datetime',
            'location'        => 'nullable|max:255',
            'capacity'        => 'nullable|integer|between:1,100000',
            'image_alt'       => 'nullable|max:250',
            'seo_title'       => 'nullable|max:190',
            'seo_description' => 'nullable|max:300',
        ], [
            'start_at.required' => 'Choose when the event starts.',
            'start_at.datetime' => 'Choose a valid start date and time.',
            'end_at.datetime'   => 'Choose a valid end date and time, or leave it empty.',
            'capacity.integer'  => 'Capacity must be a whole number, or leave it empty for unlimited places.',
            'capacity.between'  => 'Capacity must be at least 1.',
        ], ['title' => 'Title', 'summary' => 'Summary', 'location' => 'Location', 'image_alt' => 'Image description', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description']);
        $errors = $v->errors();
        $this->requireText($errors, 'title', $this->clean($r->string('title')), 'Title is required.');

        $start = $this->dbDateTime($r->string('start_at'));
        $end = $this->dbDateTime($r->string('end_at'));
        if (!isset($errors['start_at']) && !isset($errors['end_at']) && $start !== null && $end !== null && strtotime($end) < strtotime($start)) {
            $errors['end_at'] = 'The end must be after the start.';
        }

        $regUrl = $this->webAddress($r->string('registration_url'), 500);
        if ($regUrl === null) {
            $errors['registration_url'] = 'Enter a full web address, for example https://forms.example.org/register.';
        }

        $typedSlug = $r->string('slug');
        $slug = Slug::make($typedSlug);
        if ($typedSlug !== '' && $slug === '') {
            $errors['slug'] = 'The web address may only contain letters, numbers and hyphens.';
        } elseif ($slug !== '') {
            if ((int) Database::value('SELECT COUNT(*) FROM events WHERE slug = ?' . ($id !== null ? ' AND id <> ?' : ''), $id !== null ? [$slug, $id] : [$slug], 0) > 0) {
                $errors['slug'] = 'That web address is already used by another event.';
            }
        }

        if (!isset($errors['image_alt']) && $this->imageWillExist('image', $row['image'] ?? null) && $this->clean($r->string('image_alt')) === '') {
            $errors['image_alt'] = 'Describe the image for people who cannot see it (alt text).';
        }
        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }
        if ($slug === '') {
            $slug = Slug::unique('events', $r->string('title'), $id, 'slug', [], 'event');
        }

        $capacity = $r->string('capacity');
        $body = $r->post('body', '');
        return [
            'slug'                 => $slug,
            'title'                => $this->clean($r->string('title')),
            'summary'              => $this->clean($r->string('summary'), true) ?: null,
            'body'                 => Sanitizer::html(is_string($body) ? $body : '') ?: null,
            'image_alt'            => $this->clean($r->string('image_alt')) ?: null,
            'start_at'             => $start,
            'end_at'               => $end,
            'location'             => $this->clean($r->string('location')) ?: null,
            'registration_enabled' => $this->flag('registration_enabled'),
            'registration_url'     => ($regUrl ?? '') ?: null,
            'capacity'             => $capacity === '' ? null : (int) $capacity,
            'is_active'            => $this->flag('is_active'),
            'seo_title'            => $this->clean($r->string('seo_title')) ?: null,
            'seo_description'      => $this->clean($r->string('seo_description')) ?: null,
        ];
    }
}
