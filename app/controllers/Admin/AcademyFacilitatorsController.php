<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Roles;
use App\Core\Session;
use App\Services\Academy\AcademyAccess;

/**
 * Admin > LAF Academy > Facilitators (administrators): create the sign-in account of a facilitator (role "Facilitator" or any role that holds "Teach in the LAF Academy"),
 * give a new temporary password, switch the account off and on. A facilitator signs in at the usual admin address and sees only the Academy: their own courses,
 * virtual classes and learners. The temporary password is shown ONCE and must be replaced at the first sign-in.
 */
final class AcademyFacilitatorsController extends AcademyAdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('academy.manage');
        $rows = Database::all('SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY is_active DESC, name ASC');
        $rows = array_values(array_filter($rows, static fn(array $u): bool => Roles::can((string) $u['role'], 'academy.teach')));
        foreach ($rows as &$u) {
            $u['courses'] = (int) Database::value('SELECT COUNT(*) FROM academy_courses WHERE facilitator_id = ?', [(int) $u['id']], 0);
            $u['classes'] = (int) Database::value("SELECT COUNT(*) FROM academy_classes WHERE facilitator_id = ? AND status = 'scheduled' AND end_at >= NOW()", [(int) $u['id']], 0);
            $u['role_name'] = Roles::name((string) $u['role']);
        }
        unset($u);
        $creds = Session::getFlash('temp_credentials');
        $roles = [];
        foreach (Roles::slugs() as $slug) {
            if ($slug !== 'admin' && Roles::can($slug, 'academy.teach')) {
                $roles[$slug] = Roles::name($slug);
            }
        }
        return $this->render('admin/academy/facilitators', ['rows' => $rows, 'creds' => is_array($creds) ? $creds : null, 'roles' => $roles, 'meId' => Auth::id()],
            ['title' => 'Facilitators', 'nav' => 'academy_facilitators', 'breadcrumbs' => $this->crumbs('Facilitators')]);
    }

    public function store(Request $request): Response
    {
        $this->authorize('academy.manage');
        $roles = [];
        foreach (Roles::slugs() as $slug) {
            if ($slug !== 'admin' && Roles::can($slug, 'academy.teach')) {
                $roles[] = $slug;
            }
        }
        $d = $this->validate([
            'name' => 'required|max:120', 'email' => 'required|email|max:190|unique:users,email', 'role' => 'required|in:' . implode(',', $roles ?: ['facilitator']),
        ], ['email.unique' => 'There is already a user with this email address.'], ['name' => 'Name', 'email' => 'E-mail address', 'role' => 'Role'], 'admin/academy/facilitators');
        $password = Auth::generatePassword(16);
        $email = strtolower((string) $d['email']);
        $id = \App\Models\User::create([
            'name' => strip_tags((string) $d['name']), 'email' => $email, 'password_hash' => Auth::hashPassword($password), 'role' => $d['role'], 'is_active' => 1, 'must_change_password' => 1,
        ]);
        $this->audit('academy.facilitator.create', 'user', $id, 'Created ' . $d['role'] . ' account for ' . $email);
        Session::flash('temp_credentials', ['name' => strip_tags((string) $d['name']), 'email' => $email, 'password' => $password, 'reason' => 'created']);
        Flash::success('The facilitator account was created for ' . $email . '.');
        return $this->redirect('admin/academy/facilitators');
    }

    /** The user, only when it is a non-administrator teaching account other than the signed-in user. @return array<string,mixed> */
    private function target(int $id): array
    {
        $this->authorize('academy.manage');
        $u = Database::one('SELECT * FROM users WHERE id = ?', [$id]) ?? throw new HttpException(404, 'Not found');
        if ($u['role'] === 'admin' || !Roles::can((string) $u['role'], 'academy.teach') || $id === Auth::id()) {
            throw new HttpException(403, 'Forbidden');
        }
        return $u;
    }

    public function resetPassword(Request $request, int $id): Response
    {
        $u = $this->target($id);
        $password = Auth::generatePassword(16);
        Auth::changePassword($id, $password, true);
        $this->audit('academy.facilitator.password_reset', 'user', $id, 'Reset the password of ' . $u['email']);
        Session::flash('temp_credentials', ['name' => (string) $u['name'], 'email' => (string) $u['email'], 'password' => $password, 'reason' => 'reset']);
        Flash::success('A new temporary password was created for ' . $u['email'] . '.');
        return $this->redirect('admin/academy/facilitators');
    }

    public function toggle(Request $request, int $id): Response
    {
        $u = $this->target($id);
        $new = (int) $u['is_active'] === 1 ? 0 : 1;
        Database::update('users', ['is_active' => $new], 'id = ?', [$id]);
        $this->audit('academy.facilitator.' . ($new ? 'activate' : 'deactivate'), 'user', $id, ($new ? 'Switched on' : 'Switched off') . ' the account of ' . $u['email']);
        Flash::success($new ? 'The account is active again.' : 'The account is switched off: this person can no longer sign in. Their courses stay as they are.');
        return $this->redirect('admin/academy/facilitators');
    }
}
