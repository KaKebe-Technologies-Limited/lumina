<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * MY PROFILE (every signed-in user). OWNER: B3 admin-core.
 *   GET  admin/profile   name, email, role, last sign-in, own recent activity, link to the password form
 *   POST admin/profile   change name / email (changing the email asks for the current password)
 * The password itself is changed at admin/profile/password (AuthController) - the page that also forces a new password at first sign-in.
 */
final class ProfileController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('dashboard.view');
        $user = $this->user();
        return $this->render('admin/profile/index', [
            'row'      => User::findOrFail((int) $user['id']),
            'activity' => Database::all('SELECT action, summary, created_at FROM audit_log WHERE user_id = ? ORDER BY id DESC LIMIT 10', [(int) $user['id']]),
        ], ['title' => 'My profile', 'nav' => 'profile', 'breadcrumbs' => [['My profile', null]]]);
    }

    public function update(Request $request): Response
    {
        $this->authorize('dashboard.view');
        $user = $this->user();
        $id = (int) $user['id'];
        $row = User::findOrFail($id);
        // the current password is checked BEFORE the uniqueness rule runs, so a signed-in user cannot probe which e-mails exist
        $wantsEmail = strtolower($request->string('email'));
        if ($wantsEmail !== '' && $wantsEmail !== strtolower((string) $row['email'])) {
            $current = (string) $request->post('current_password', '');
            if ($current === '' || !Auth::verifyPassword($current, (string) $row['password_hash'])) {
                $this->fail(['current_password' => 'To change your email address, enter your current password.'], 'admin/profile');
            }
        }
        $d = $this->validate([
            'name'  => 'required|max:120',
            'email' => 'required|email|max:190|unique:users,email,' . $id,
        ], ['email.unique' => 'There is already a user with this email address.'], ['name' => 'Name', 'email' => 'Email address'], 'admin/profile');

        $email = strtolower((string) $d['email']);
        $newName = strip_tags((string) $d['name']);
        $notes = [];
        if ($newName !== (string) $row['name']) {
            $notes[] = 'name "' . mb_substr((string) $row['name'], 0, 60) . '" -> "' . mb_substr($newName, 0, 60) . '"';
        }
        if ($email !== strtolower((string) $row['email'])) {
            $notes[] = 'email ' . $row['email'] . ' -> ' . $email;
        }
        User::update($id, ['name' => $newName, 'email' => $email]);
        Auth::refresh();
        $this->audit('user.update', 'user', $id, 'Updated own profile' . ($notes ? ' (' . implode(', ', $notes) . ')' : ''));
        Flash::success('Your profile was saved.');
        return $this->redirect('admin/profile');
    }
}
