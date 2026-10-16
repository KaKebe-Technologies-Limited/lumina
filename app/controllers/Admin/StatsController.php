<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Icons;
use App\Core\Request;
use App\Core\Response;
use App\Models\Stat;

/**
 * Impact figures ("150+ Youth Trained") shown on the public pages. Table `stats`, grouped by group_key (the home page reads 'home').
 * OWNER: B3 admin-core. Ability content.manage (delete: content.delete).
 *   GET admin/stats[?group=]   list (+ re-order)     GET admin/stats/create     POST admin/stats
 *   GET admin/stats/{id}/edit  POST admin/stats/{id} POST admin/stats/{id}/delete
 *   POST admin/stats/reorder   POST admin/stats/{id}/toggle
 */
final class StatsController extends AdminController
{
    /** Where each group is shown, for the help text. */
    private const GROUP_HINTS = ['home' => 'These figures are shown on the home page.'];

    public function index(Request $request): Response
    {
        $this->authorize('content.manage');
        $groups = $this->groups();
        $group = $request->string('group');
        if ($group === '' || !in_array($group, $groups, true)) {
            $group = in_array('home', $groups, true) ? 'home' : ($groups[0] ?? 'home');
        }
        return $this->render('admin/stats/index', [
            'groups'    => $groups,
            'group'     => $group,
            'rows'      => Stat::where(['group_key' => $group], 'sort_order ASC, id ASC'),
            'hint'      => self::GROUP_HINTS[$group] ?? 'Groups let the website show different sets of figures in different places.',
            'canDelete' => can('content.delete'),
        ], ['title' => 'Impact figures', 'nav' => 'stats', 'breadcrumbs' => [['Impact figures', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('content.manage');
        $group = $request->string('group', 'home');
        $group = preg_match('/^[a-z0-9_-]{1,50}$/', $group) ? $group : 'home';
        $max = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM stats WHERE group_key = ?', [$group], 0);
        return $this->form('create', [
            'group_key' => $group, 'label' => '', 'value_number' => '', 'prefix' => '', 'suffix' => '', 'description' => '',
            'icon' => '', 'sort_order' => $max + 10, 'is_active' => 1,
        ]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('content.manage');
        $data = $this->validated('admin/stats/create');
        $id = Stat::create($data);
        $this->audit('stat.create', 'stat', $id, 'Added figure "' . $data['label'] . '"');
        Flash::success('Figure "' . $data['label'] . '" added.');
        return $this->redirect('admin/stats?group=' . rawurlencode($data['group_key']));
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        return $this->form('edit', Stat::findOrFail($id));
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        Stat::findOrFail($id);
        $data = $this->validated('admin/stats/' . $id . '/edit');
        Stat::update($id, $data);
        $this->audit('stat.update', 'stat', $id, 'Edited figure "' . $data['label'] . '"');
        Flash::success('Figure "' . $data['label'] . '" saved.');
        return $this->redirect('admin/stats?group=' . rawurlencode($data['group_key']));
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('content.delete');
        $row = Stat::findOrFail($id);
        Stat::delete($id);
        $this->audit('stat.delete', 'stat', $id, 'Deleted figure "' . $row['label'] . '"');
        Flash::success('Figure "' . $row['label'] . '" deleted.');
        return $this->redirect('admin/stats?group=' . rawurlencode((string) $row['group_key']));
    }

    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = Stat::findOrFail($id);
        $on = (int) $row['is_active'] === 1 ? 0 : 1;
        Stat::update($id, ['is_active' => $on]);
        $this->audit('stat.update', 'stat', $id, ($on ? 'Showed' : 'Hid') . ' figure "' . $row['label'] . '"');
        Flash::success('"' . $row['label'] . '" is now ' . ($on ? 'shown.' : 'hidden.'));
        return $this->redirect('admin/stats?group=' . rawurlencode((string) $row['group_key']));
    }

    public function reorder(Request $request): Response
    {
        $this->authorize('content.manage');
        $ids = $request->post('ids', []);
        $group = $request->string('group');
        $ids = is_array($ids) ? array_values(array_filter(array_map('intval', $ids))) : [];
        Database::transaction(static function () use ($ids, $group): void {
            $pos = 10;
            foreach ($ids as $id) {
                Database::execute('UPDATE stats SET sort_order = ? WHERE id = ? AND group_key = ?', [$pos, $id, $group]);
                $pos += 10;
            }
        });
        $this->audit('stat.reorder', 'stat', null, 'Re-ordered the figures of group "' . $group . '"');
        Flash::success('The new order is saved.');
        return $this->redirect('admin/stats?group=' . rawurlencode($group));
    }

    // ------------------------------------------------------------------ internals

    /** @return list<string> groups in use ('home' always first). */
    private function groups(): array
    {
        $groups = array_map('strval', array_column(Database::all('SELECT DISTINCT group_key FROM stats ORDER BY group_key'), 'group_key'));
        $groups = array_values(array_diff($groups, ['home']));
        array_unshift($groups, 'home');
        return $groups;
    }

    private function form(string $mode, array $row): Response
    {
        return $this->render('admin/stats/form', [
            'mode'   => $mode,
            'row'    => $row,
            'groups' => $this->groups(),
            'icons'  => Icons::names(),
        ], [
            'title'       => $mode === 'create' ? 'Add an impact figure' : 'Edit impact figure',
            'nav'         => 'stats',
            'breadcrumbs' => [['Impact figures', url('admin/stats')], [$mode === 'create' ? 'Add' : 'Edit', null]],
        ]);
    }

    /** @return array<string,mixed> */
    private function validated(string $redirect): array
    {
        $d = $this->validate([
            'group_key'    => 'required|regex:/^[a-z0-9_-]{1,50}$/',
            'label'        => 'required|max:120',
            'value_number' => 'required|integer|between:0,2000000000',
            'prefix'       => 'nullable|max:10',
            'suffix'       => 'nullable|max:10',
            'description'  => 'nullable|max:255',
            'icon'         => 'nullable|in:' . implode(',', Icons::names()),
            'sort_order'   => 'nullable|integer|between:0,100000',
        ], [
            'group_key.regex'      => 'Use lowercase letters, numbers, hyphens or underscores (for example "home").',
            'value_number.integer' => 'Enter a whole number, for example 150.',
            'value_number.between' => 'Enter a whole number between 0 and 2,000,000,000.',
        ], [
            'group_key' => 'Group', 'label' => 'Label', 'value_number' => 'Number', 'prefix' => 'Prefix', 'suffix' => 'Suffix',
            'description' => 'Description', 'icon' => 'Icon',
        ], $redirect);
        return [
            'group_key'    => (string) $d['group_key'],
            'label'        => strip_tags((string) $d['label']),
            'value_number' => (int) $d['value_number'],
            'prefix'       => strip_tags((string) ($d['prefix'] ?? '')),
            'suffix'       => strip_tags((string) ($d['suffix'] ?? '')),
            'description'  => strip_tags((string) ($d['description'] ?? '')),
            'icon'         => (string) ($d['icon'] ?? ''),
            'sort_order'   => ($d['sort_order'] ?? '') !== '' ? (int) $d['sort_order'] : 0,
            'is_active'    => $this->request->string('is_active') === '1' ? 1 : 0,
        ];
    }
}
