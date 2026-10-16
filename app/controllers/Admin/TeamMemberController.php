<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Core\Validator;
use App\Models\TeamMember;

/**
 * Admin > Team members (/admin/team). OWNER: B8 "org-admin".
 * List (search, group/status filter, sort, pagination, bulk), create/edit with photo upload, quick show/hide, up/down
 * arrows, a drag-and-drop reorder screen, delete. Public output: /team (B7) shows active members grouped by team_group.
 */
final class TeamMemberController extends OrgAdminController
{
    private const GROUP_ORDER = "FIELD(team_group, 'board', 'staff', 'advisor', 'volunteer')";

    protected function area(): string
    {
        return 'admin/team';
    }

    /** @return array<string,string[]> */
    private function sortable(): array
    {
        return [
            'order'  => [self::GROUP_ORDER, 'sort_order'],
            'name'   => ['name'],
            'role'   => ['role_title', 'name'],
            'group'  => [self::GROUP_ORDER, 'name'],
            'status' => ['is_active', 'name'],
            'added'  => ['created_at'],
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $st = $this->listState($this->sortable(), 'order', 'asc');

        $where = [];
        $params = [];
        [$sql, $sp] = $this->searchSql(['name', 'role_title', 'email', 'bio'], $st['q']);
        if ($sql !== '') {
            $where[] = $sql;
            $params = array_merge($params, $sp);
        }
        $group = $request->string('group');
        if (isset(TeamMember::GROUPS[$group])) {
            $where[] = 'team_group = ?';
            $params[] = $group;
        } else {
            $group = '';
        }
        $status = $request->string('status');
        if ($status === 'active' || $status === 'inactive') {
            $where[] = 'is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = '';
        }

        $pager = $this->paged('team_members' . $this->whereSql($where), $params, '*', $this->orderSql($this->sortable(), $st['sort'], $st['dir']));
        $filtered = $st['q'] !== '' || $group !== '' || $status !== '';

        return $this->render('admin/team/index', [
            'pager'    => $pager,
            'q'        => $st['q'],
            'sort'     => $st['sort'],
            'dir'      => $st['dir'],
            'group'    => $group,
            'status'   => $status,
            'filtered' => $filtered,
            'total'    => TeamMember::count(),
            'canOrder' => $st['sort'] === 'order' && $st['dir'] === 'asc' && !$filtered,
            'groups'   => TeamMember::GROUPS,
        ], ['title' => 'Team members', 'nav' => 'team', 'breadcrumbs' => [['Team members', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        $group = $request->string('group');
        return $this->render('admin/team/create', [
            'member' => ['is_active' => 1, 'team_group' => isset(TeamMember::GROUPS[$group]) ? $group : 'staff', 'sort_order' => ''],
            'groups' => TeamMember::GROUPS,
        ], ['title' => 'Add team member', 'nav' => 'team', 'breadcrumbs' => [['Team members', url('admin/team')], ['Add', null]]]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $data = $this->collect(null, 'admin/team/create');
        try {
            $data['photo'] = Uploader::resolveImageField($request, 'photo', null);
        } catch (UploadException $e) {
            $this->fail(['photo' => $e->getMessage()], 'admin/team/create');
        }
        if ($data['sort_order'] === null) {
            $data['sort_order'] = $this->nextSortOrder('team_members', 'team_group = ?', [$data['team_group']]);
        }
        $id = TeamMember::create($data);
        $this->audit('team.create', 'team_member', $id, 'Added team member "' . $data['name'] . '"', ['group' => $data['team_group'], 'active' => (bool) $data['is_active']]);
        Flash::success('Team member "' . $data['name'] . '" was added.' . ($data['is_active'] ? '' : ' It is hidden from the website until you activate it.'));
        return $this->redirect('admin/team');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $member = TeamMember::findOrFail($id);
        return $this->render('admin/team/edit', [
            'member' => $member,
            'groups' => TeamMember::GROUPS,
        ], ['title' => 'Edit team member', 'nav' => 'team', 'breadcrumbs' => [['Team members', url('admin/team')], [$member['name'], null]]]);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = TeamMember::findOrFail($id);
        $data = $this->collect($row, 'admin/team/' . $id . '/edit');
        try {
            $data['photo'] = Uploader::resolveImageField($request, 'photo', $row['photo'] ?? null);
        } catch (UploadException $e) {
            $this->fail(['photo' => $e->getMessage()], 'admin/team/' . $id . '/edit');
        }
        if ($data['sort_order'] === null) {
            $data['sort_order'] = $this->nextSortOrder('team_members', 'team_group = ?', [$data['team_group']]);
        }
        TeamMember::update($id, $data);

        $changed = [];
        foreach (['name', 'role_title', 'team_group', 'bio', 'photo', 'email', 'linkedin', 'x', 'sort_order', 'is_active'] as $f) {
            if ((string) ($row[$f] ?? '') !== (string) ($data[$f] ?? '')) {
                $changed[] = $f;
            }
        }
        $this->audit('team.update', 'team_member', $id, 'Edited team member "' . $data['name'] . '"', ['fields' => $changed]);
        Flash::success('Team member "' . $data['name'] . '" was saved.');
        return $this->redirect('admin/team');
    }

    /** Show / hide a member on the website (POST). */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = TeamMember::findOrFail($id);
        $new = (int) $row['is_active'] === 1 ? 0 : 1;
        TeamMember::update($id, ['is_active' => $new]);
        $this->audit('team.' . ($new ? 'activate' : 'deactivate'), 'team_member', $id, ($new ? 'Showed' : 'Hid') . ' team member "' . $row['name'] . '"');
        Flash::success('"' . $row['name'] . '" is now ' . ($new ? 'shown on' : 'hidden from') . ' the website.');
        return Response::redirect($this->returnUrl());
    }

    /** One step up/down within the member's group (POST direction=up|down). */
    public function move(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = TeamMember::findOrFail($id);
        $dir = $request->string('direction') === 'up' ? 'up' : 'down';
        if ($this->moveWithin('team_members', $id, $dir, 'team_group')) {
            $this->audit('team.reorder', 'team_member', $id, 'Moved team member "' . $row['name'] . '" ' . $dir);
            Flash::success('"' . $row['name'] . '" moved ' . $dir . '.');
        }
        return Response::redirect($this->returnUrl());
    }

    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        $members = Database::all('SELECT * FROM team_members ORDER BY ' . self::GROUP_ORDER . ', sort_order ASC, id ASC');
        $byGroup = array_fill_keys(array_keys(TeamMember::GROUPS), []);
        foreach ($members as $m) {
            $byGroup[$m['team_group']][] = $m;
        }
        return $this->render('admin/team/reorder', [
            'byGroup' => $byGroup,
            'groups'  => TeamMember::GROUPS,
        ], ['title' => 'Reorder team', 'nav' => 'team', 'breadcrumbs' => [['Team members', url('admin/team')], ['Reorder', null]]]);
    }

    public function saveOrder(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $n = $this->applyOrder('team_members', $ids);
        if ($n > 0) {
            $this->audit('team.reorder', 'team_member', null, 'Reordered the team (' . $n . ' members)');
            Flash::success('The new order was saved.');
        } else {
            Flash::info('Nothing to reorder.');
        }
        return $this->redirect('admin/team');
    }

    public function bulk(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $this->postedIds();
        $action = $request->string('bulk_action');
        $back = Response::redirect($this->returnUrl());
        if ($ids === []) {
            Flash::warning('Select at least one team member first.');
            return $back;
        }
        switch ($action) {
            case 'activate':
            case 'deactivate':
                $this->setFlag('team_members', 'is_active', $action === 'activate' ? 1 : 0, $ids);
                $this->audit('team.bulk', 'team_member', null, ucfirst($action) . 'd ' . count($ids) . ' team members', ['ids' => $ids, 'action' => $action]);
                Flash::success($this->plural(count($ids), 'team member', 'team members') . ($action === 'activate' ? ' shown on the website.' : ' hidden from the website.'));
                break;
            case 'delete':
                $this->authorize('content.delete');
                $n = $this->deleteMany($ids);
                Flash::success($this->plural($n, 'team member', 'team members') . ' deleted.');
                break;
            default:
                Flash::error('Choose an action to apply to the selected team members.');
        }
        return $back;
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = TeamMember::findOrFail($id);
        $this->deleteMany([$id]);
        Flash::success('Team member "' . $row['name'] . '" was deleted.');
        return Response::redirect($this->returnUrl());
    }

    // ------------------------------------------------------------------ internals

    /** @param int[] $ids */
    private function deleteMany(array $ids): int
    {
        $n = 0;
        foreach ($ids as $id) {
            $row = TeamMember::find($id);
            if ($row === null) {
                continue;
            }
            TeamMember::delete($id);
            Uploader::delete($row['photo'] ?? null);
            $this->audit('team.delete', 'team_member', $id, 'Deleted team member "' . $row['name'] . '"');
            $n++;
        }
        return $n;
    }

    /**
     * Validate the form. Returns the columns to save (photo is added by the caller after validation).
     * @param array<string,mixed>|null $row current row when editing
     * @return array<string,mixed>
     */
    private function collect(?array $row, string $redirectTo): array
    {
        $r = $this->request;
        $v = Validator::make($r->all(), [
            'name'       => 'required|max:150',
            'role_title' => 'nullable|max:150',
            'team_group' => 'required|in:' . implode(',', array_keys(TeamMember::GROUPS)),
            'bio'        => 'nullable|max:2000',
            'email'      => 'nullable|email|max:190',
            'sort_order' => 'nullable|integer|between:0,100000',
        ], [
            'team_group.required' => 'Choose the group this person belongs to.',
            'team_group.in'       => 'Choose one of the listed groups.',
            'sort_order.integer'  => 'The position must be a whole number, or leave it empty to put the person last.',
            'sort_order.between'  => 'The position must be between 0 and 100000.',
        ], ['name' => 'Name', 'role_title' => 'Role / title', 'bio' => 'Biography', 'email' => 'Email', 'team_group' => 'Group']);
        $errors = $v->errors();
        $this->requireText($errors, 'name', $this->clean($r->string('name')), 'Full name is required.');

        $linkedin = $this->webAddress($r->string('linkedin'));
        $x = $this->webAddress($r->string('x'));
        if ($linkedin === null) {
            $errors['linkedin'] ??= 'Enter a full web address, for example https://www.linkedin.com/in/name.';
        }
        if ($x === null) {
            $errors['x'] ??= 'Enter a full web address, for example https://x.com/name.';
        }
        if ($errors !== []) {
            $this->fail($errors, $redirectTo);
        }

        $sort = $r->string('sort_order');
        return [
            'name'       => $this->clean($r->string('name')),
            'role_title' => $this->clean($r->string('role_title')),
            'team_group' => $r->string('team_group'),
            'bio'        => $this->clean($r->string('bio'), true) ?: null,
            'email'      => $r->string('email') ?: null,
            'linkedin'   => ($linkedin ?? '') ?: null,
            'x'          => ($x ?? '') ?: null,
            'sort_order' => $sort === '' ? null : (int) $sort,
            'is_active'  => $this->flag('is_active'),
        ];
    }
}
