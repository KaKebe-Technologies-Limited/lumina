<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Roles;

/**
 * ROLES AND PERMISSIONS (ability roles.manage = administrators only).
 *   GET  admin/roles               all roles, how many people hold each, what they may do
 *   GET  admin/roles/create        new role (optionally copy another role's permissions)
 *   POST admin/roles               save the new role
 *   GET  admin/roles/{id}/edit     edit name, description and permissions
 *   POST admin/roles/{id}          save
 *   POST admin/roles/{id}/delete   delete a role nobody holds (system roles cannot be deleted)
 * The Administrator role always holds every ability and cannot be edited. users.manage and roles.manage can never be given to another role,
 * so a custom role can never create administrators or widen its own rights.
 */
final class RolesController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('roles.manage');
        $rows = array_values(Roles::all());
        foreach ($rows as &$r) {
            $r['users'] = Roles::usersCount((string) $r['slug']);
            $r['count'] = $r['slug'] === 'admin' ? count(Roles::knownAbilities()) : count(Roles::abilitiesFor((string) $r['slug']));
        }
        unset($r);
        return $this->render('admin/roles/index', ['rows' => $rows, 'missingTable' => Roles::all() === []],
            ['title' => 'Roles and permissions', 'nav' => 'roles', 'breadcrumbs' => [['Roles', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('roles.manage');
        $copy = (string) $request->query('copy', '');
        $from = Roles::all()[$copy] ?? null;
        return $this->form('create', [
            'name' => $from ? (string) $from['name'] . ' (copy)' : '',
            'description' => $from ? (string) $from['description'] : '',
            'abilities' => $from ? Roles::abilitiesFor($copy) : ['dashboard.view'],
            'is_system' => 0, 'slug' => '',
        ]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('roles.manage');
        $d = $this->validate(['name' => 'required|max:80', 'description' => 'nullable|max:255'], [], ['name' => 'Role name', 'description' => 'Description'], 'admin/roles/create');
        $name = trim(strip_tags((string) $d['name']));
        $this->assertNameFree($name, null, 'admin/roles/create');
        $abilities = $this->abilitiesFromPost($request);
        $slug = Roles::makeSlug($name);
        $id = Database::insert('roles', [
            'slug' => $slug, 'name' => $name, 'description' => trim(strip_tags((string) ($d['description'] ?? ''))),
            'abilities' => json_encode($abilities, JSON_UNESCAPED_SLASHES), 'is_system' => 0,
        ]);
        Roles::flush();
        $this->audit('role.create', 'role', $id, 'Created role ' . $slug . ' with ' . count($abilities) . ' abilities');
        Flash::success('Role "' . $name . '" created. You can now give it to users.');
        return $this->redirect('admin/roles');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('roles.manage');
        $row = $this->row($id);
        $row['abilities'] = Roles::abilitiesFor((string) $row['slug']);
        return $this->form('edit', $row);
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('roles.manage');
        $row = $this->row($id);
        $back = 'admin/roles/' . (int) $row['id'] . '/edit';
        if ($row['slug'] === 'admin') {
            $this->fail(['name' => 'The Administrator role always has every right and cannot be changed.'], $back);
        }
        $d = $this->validate(['name' => 'required|max:80', 'description' => 'nullable|max:255'], [], ['name' => 'Role name', 'description' => 'Description'], $back);
        $name = trim(strip_tags((string) $d['name']));
        $this->assertNameFree($name, (int) $row['id'], $back);
        $abilities = $this->abilitiesFromPost($request);
        Database::execute('UPDATE roles SET name = ?, description = ?, abilities = ? WHERE id = ?', [
            $name, trim(strip_tags((string) ($d['description'] ?? ''))), json_encode($abilities, JSON_UNESCAPED_SLASHES), (int) $row['id'],
        ]);
        Roles::flush();
        $this->audit('role.update', 'role', (int) $row['id'], 'Updated role ' . $row['slug'] . ' (' . count($abilities) . ' abilities)');
        Flash::success('Role saved. The change applies to everyone with this role straight away.');
        return $this->redirect('admin/roles');
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('roles.manage');
        $row = $this->row($id);
        if ((int) $row['is_system'] === 1 || $row['slug'] === 'admin') {
            Flash::error('Built-in roles cannot be deleted.');
            return $this->redirect('admin/roles');
        }
        $n = Roles::usersCount((string) $row['slug']);
        if ($n > 0) {
            Flash::error($n . ($n === 1 ? ' person still has' : ' people still have') . ' the role "' . $row['name'] . '". Change their role first (Admin > Users), then delete it.');
            return $this->redirect('admin/roles');
        }
        Database::execute('DELETE FROM roles WHERE id = ?', [(int) $row['id']]);
        Roles::flush();
        $this->audit('role.delete', 'role', (int) $row['id'], 'Deleted role ' . $row['slug']);
        Flash::success('Role "' . $row['name'] . '" deleted.');
        return $this->redirect('admin/roles');
    }

    private function form(string $mode, array $row): Response
    {
        return $this->render('admin/roles/form', [
            'mode' => $mode, 'row' => $row, 'catalog' => (array) require APP_PATH . '/config/ability_catalog.php', 'users' => $mode === 'edit' ? Roles::usersCount((string) $row['slug']) : 0,
        ], ['title' => $mode === 'create' ? 'New role' : 'Edit role', 'nav' => 'roles', 'breadcrumbs' => [['Roles', url('admin/roles')], [$mode === 'create' ? 'New' : 'Edit', null]]]);
    }

    private function row(int $id): array
    {
        $r = Database::one('SELECT * FROM roles WHERE id = ?', [$id]);
        if (!$r) {
            abort(404);
        }
        return $r;
    }

    /** @return list<string> only known, assignable abilities, plus the always-on dashboard.view */
    private function abilitiesFromPost(Request $request): array
    {
        $in = $request->post('abilities', []);
        $list = Roles::clean(is_array($in) ? array_values($in) : []);
        return in_array('dashboard.view', $list, true) ? $list : array_merge(['dashboard.view'], $list);
    }

    private function assertNameFree(string $name, ?int $exceptId, string $back): void
    {
        if (mb_strtolower($name) === 'administrator' || Database::value('SELECT COUNT(*) FROM roles WHERE LOWER(name) = LOWER(?) AND id <> ?', [$name, $exceptId ?? 0]) > 0) {
            $this->fail(['name' => 'There is already a role with this name.'], $back);
        }
    }
}
