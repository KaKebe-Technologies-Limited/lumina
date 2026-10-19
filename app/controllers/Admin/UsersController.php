<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

/**
 * Admin USERS (ability users.manage = admins only). OWNER: B3 admin-core.
 *   GET admin/users   GET admin/users/create   POST admin/users   GET admin/users/{id}/edit   POST admin/users/{id}
 *   POST admin/users/{id}/reset-password   POST admin/users/{id}/delete
 * Roles: admin | editor. New accounts and password resets get a random TEMPORARY password that is shown exactly once (session flash,
 * never stored or logged) and the user must replace it at first sign-in. The last active admin can never be removed, demoted or
 * deactivated, and nobody can lock themselves out from this screen.
 */
final class UsersController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('users.manage');
        $rows = Database::all('SELECT id, name, email, role, is_active, must_change_password, last_login_at, created_at FROM users ORDER BY is_active DESC, role ASC, name ASC');
        $creds = Session::getFlash('temp_credentials');
        return $this->render('admin/users/index', [
            'rows'   => $rows,
            'creds'  => is_array($creds) ? $creds : null,
            'meId'   => Auth::id(),
            'admins' => $this->activeAdminCount(),
        ], ['title' => 'Users', 'nav' => 'users', 'breadcrumbs' => [['Users', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('users.manage');
        return $this->form('create', ['name' => '', 'email' => '', 'role' => 'editor', 'is_active' => 1]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('users.manage');
        $d = $this->validate([
            'name'  => 'required|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'role'  => 'required|in:' . implode(',', \App\Core\Roles::slugs()),
        ], ['email.unique' => 'There is already a user with this email address.'], ['name' => 'Name', 'email' => 'Email address', 'role' => 'Role'], 'admin/users/create');

        $password = Auth::generatePassword(16);
        $email = strtolower((string) $d['email']);
        $id = User::create([
            'name'                 => strip_tags((string) $d['name']),
            'email'                => $email,
            'password_hash'        => Auth::hashPassword($password),
            'role'                 => $d['role'],
            'is_active'            => 1,
            'must_change_password' => 1,
        ]);
        $this->audit('user.create', 'user', $id, 'Created ' . $d['role'] . ' account for ' . $email);
        Session::flash('temp_credentials', ['name' => strip_tags((string) $d['name']), 'email' => $email, 'password' => $password, 'reason' => 'created']);
        Flash::success('Account created for ' . $email . '.');
        return $this->redirect('admin/users');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('users.manage');
        return $this->form('edit', User::findOrFail($id));
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('users.manage');
        $row = User::findOrFail($id);
        $back = 'admin/users/' . $id . '/edit';
        $d = $this->validate([
            'name'  => 'required|max:120',
            'email' => 'required|email|max:190|unique:users,email,' . $id,
            'role'  => 'required|in:' . implode(',', \App\Core\Roles::slugs()),
        ], ['email.unique' => 'There is already a user with this email address.'], ['name' => 'Name', 'email' => 'Email address', 'role' => 'Role'], $back);

        $self = $id === Auth::id();
        $role = $self ? (string) $row['role'] : (string) $d['role'];
        $active = $self ? 1 : ($request->string('is_active') === '1' ? 1 : 0);

        $stillActiveAdmin = $role === 'admin' && $active === 1;
        $wasActiveAdmin = $row['role'] === 'admin' && (int) $row['is_active'] === 1;
        if ($wasActiveAdmin && !$stillActiveAdmin && $this->activeAdminCount($id) === 0) {
            $this->fail(['role' => 'There must always be at least one active administrator.'], $back);
        }

        User::update($id, [
            'name'      => strip_tags((string) $d['name']),
            'email'     => strtolower((string) $d['email']),
            'role'      => $role,
            'is_active' => $active,
        ]);
        if ($self) {
            Auth::refresh();
        }
        $changes = [];
        foreach (['name', 'email', 'role', 'is_active'] as $k) {
            $new = match ($k) { 'role' => $role, 'is_active' => $active, 'email' => strtolower((string) $d['email']), default => strip_tags((string) $d['name']) };
            if ((string) $row[$k] !== (string) $new) {
                $changes[] = $k;
            }
        }
        $this->audit('user.update', 'user', $id, 'Edited user ' . strtolower((string) $d['email']), ['fields' => $changes]);
        Flash::success('Saved ' . strip_tags((string) $d['name']) . '.');
        return $this->redirect('admin/users');
    }

    public function resetPassword(Request $request, int $id): Response
    {
        $this->authorize('users.manage');
        $row = User::findOrFail($id);
        if ($id === Auth::id()) {
            Flash::error('Use "My profile" to change your own password.');
            return $this->redirect('admin/users');
        }
        $password = Auth::generatePassword(16);
        Auth::changePassword($id, $password, true);
        $this->audit('user.password_reset', 'user', $id, 'Reset the password of ' . $row['email']);
        Session::flash('temp_credentials', ['name' => (string) $row['name'], 'email' => (string) $row['email'], 'password' => $password, 'reason' => 'reset']);
        Flash::success('A new temporary password was created for ' . $row['email'] . '.');
        return $this->redirect('admin/users');
    }

    public function delete(Request $request, int $id): Response
    {
        $this->authorize('users.manage');
        $row = User::findOrFail($id);
        if ($id === Auth::id()) {
            Flash::error('You cannot delete your own account.');
            return $this->redirect('admin/users');
        }
        if ($row['role'] === 'admin' && (int) $row['is_active'] === 1 && $this->activeAdminCount($id) === 0) {
            Flash::error('You cannot delete the last active administrator.');
            return $this->redirect('admin/users');
        }
        User::delete($id);
        $this->audit('user.delete', 'user', $id, 'Deleted user ' . $row['email']);
        Flash::success('The account of ' . $row['email'] . ' was deleted. Their past edits stay in the activity log.');
        return $this->redirect('admin/users');
    }

    // ------------------------------------------------------------------ internals

    private function activeAdminCount(?int $excludeId = null): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1";
        $params = [];
        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }
        return (int) Database::value($sql, $params, 0);
    }

    private function form(string $mode, array $row): Response
    {
        $self = $mode === 'edit' && (int) ($row['id'] ?? 0) === Auth::id();
        $lastAdmin = $mode === 'edit' && $row['role'] === 'admin' && (int) $row['is_active'] === 1 && $this->activeAdminCount((int) $row['id']) === 0;
        return $this->render('admin/users/form', [
            'mode'      => $mode,
            'row'       => $row,
            'self'      => $self,
            'lastAdmin' => $lastAdmin,
            'roles'     => \App\Core\Roles::options(),
        ], [
            'title'       => $mode === 'create' ? 'Add a user' : 'Edit user',
            'nav'         => 'users',
            'breadcrumbs' => [['Users', url('admin/users')], [$mode === 'create' ? 'Add' : 'Edit', null]],
        ]);
    }
}
