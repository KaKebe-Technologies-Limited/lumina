<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

/**
 * FIRST ADMIN for hosting without a shell (no `php bin/create-admin.php`).
 *
 *   1. Import database/schema.sql (and optionally seed.sql) with phpMyAdmin into your database.
 *   2. Put a long random INSTALL_TOKEN (16+ characters) in .env.
 *   3. Open /install, enter the token and the details of the first administrator.
 *   4. Remove INSTALL_TOKEN from .env.
 *
 * The page does not exist (404) unless INSTALL_TOKEN is set AND the users table is still empty, so it can never be used to add
 * an administrator to a working site. Only someone who can edit .env on the server knows the token. 5 attempts per 10 minutes.
 */
final class InstallController extends Controller
{
    public function show(Request $request): Response
    {
        $this->guard();
        return $this->render('web/install/index', [], [
            'layout' => 'layouts/admin_login',
            'title'  => 'Create the administrator',
            'meta'   => ['robots' => 'noindex,nofollow'],
        ])->withHeaders(['Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function create(Request $request): Response
    {
        $this->guard();
        $given = (string) $request->post('install_token', '');
        if (!hash_equals($this->token(), $given)) {
            $this->fail(['install_token' => 'That install token is not correct.'], 'install');
        }
        $min = (int) Config::get('auth.min_password', 10);
        $d = $this->validate([
            'name'     => 'required|max:120',
            'email'    => 'required|email|max:190',
            'password' => 'required|min:' . $min . '|max:72|confirmed',
        ], ['password.confirmed' => 'The two passwords do not match.'], ['name' => 'Name', 'email' => 'Email address', 'password' => 'Password'], 'install');
        if (($problem = Auth::passwordProblem((string) $d['password'])) !== null) {
            $this->fail(['password' => $problem], 'install');
        }
        User::create([
            'name'                 => strip_tags((string) $d['name']),
            'email'                => strtolower((string) $d['email']),
            'password_hash'        => Auth::hashPassword((string) $d['password']),
            'role'                 => 'admin',
            'is_active'            => 1,
            'must_change_password' => 0,
        ]);
        AuditLog::record('user.install', 'user', null, 'First administrator created through /install');
        Flash::success('The administrator account was created. Remove INSTALL_TOKEN from the .env file now, then sign in.');
        return $this->redirect('admin/login');
    }

    private function token(): string
    {
        return (string) Config::get('app.install_token', '');
    }

    /** 404 unless the install token is configured (16+ characters) and no user exists yet. */
    private function guard(): void
    {
        if (strlen($this->token()) < 16) {
            throw new HttpException(404, 'Not found');
        }
        try {
            $users = (int) Database::value('SELECT COUNT(*) FROM users', [], 0);
        } catch (\Throwable) {
            throw new HttpException(404, 'Not found');       // schema not imported yet
        }
        if ($users > 0) {
            throw new HttpException(404, 'Not found');
        }
    }
}
