<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AuditLog;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\FormState;
use App\Core\Request;
use App\Core\Response;
use App\Core\RateLimit;
use App\Core\Session;
use App\Models\User;
use App\Services\AdminPasswordReset;
use App\Services\Security\AdminLock;
use App\Services\Security\TwoFactor;

/**
 * Admin sign-in / sign-out / password change. OWNER: B3 admin-core.
 * Routes: GET/POST admin/login (guest, throttled), POST admin/logout (auth), GET/POST admin/profile/password (auth).
 * Errors never reveal whether an email exists ("Invalid email or password."); repeated failures lock the account+IP pair (Auth::attempt).
 */
final class AuthController extends Controller
{
    protected string $layout = 'layouts/admin_login';

    private const NO_STORE = ['Cache-Control' => 'no-store, max-age=0', 'X-Robots-Tag' => 'noindex, nofollow'];

    public function showLogin(Request $request): Response
    {
        return $this->render('admin/auth/login', [], ['title' => 'Sign in', 'meta' => ['robots' => 'noindex,nofollow']])->withHeaders(self::NO_STORE);
    }

    public function login(Request $request): Response
    {
        $email = $request->string('email');
        $password = (string) $request->post('password', '');
        // password only: with two-step sign-in on, the e-mailed code comes next and nobody is signed in before it
        if (!Auth::attempt($request, $email, $password, false)) {
            // log the address only when it belongs to a real account; anything else (typos, pasted passwords, junk) is recorded as a short hash
            $known = $email !== '' && User::findBy('email', strtolower(trim($email))) !== null;
            AuditLog::record('auth.login_failed', 'user', null, $known
                ? 'Failed sign-in for ' . mb_substr(strtolower(trim($email)), 0, 120)
                : 'Failed sign-in for an unknown address (ref ' . substr(sha1(strtolower(trim($email))), 0, 8) . ')');
            Flash::error(Auth::error() ?? 'Invalid email or password.');
            FormState::flash([], ['email' => mb_substr($email, 0, 190)]);
            return $this->redirect('admin/login');
        }
        $user = (array) Auth::verifiedUser();
        if (TwoFactor::enabled() && !TwoFactor::isTrusted($user, $request)) {
            $r = TwoFactor::startLogin($user, $request);
            if (!$r['ok']) {
                AuditLog::record('auth.2fa_unavailable', 'user', (int) $user['id'], 'The sign-in code could not be sent (' . (string) ($r['error'] ?? '') . ')');
                Flash::error(($r['error'] ?? '') === 'too_many'
                    ? 'Too many sign-in codes were requested for this account. Please wait an hour and try again.'
                    : 'We could not e-mail your sign-in code right now. Please try again in a minute.');
                return $this->redirect('admin/login');
            }
            AuditLog::record('auth.2fa_sent', 'user', (int) $user['id'], 'A sign-in code was e-mailed');
            return $this->redirect('admin/verify');
        }
        Auth::login($user);
        AuditLog::record('auth.login', 'user', Auth::id(), 'Signed in');
        return $this->afterLogin();
    }

    /** Where a signed-in person goes next: the password page when it must change, the page they were heading for, or the dashboard. */
    private function afterLogin(): Response
    {
        if (Auth::mustChangePassword()) {
            return $this->redirect('admin/profile/password');
        }
        $intended = (string) Session::pull('intended', '');
        if ($intended !== '' && preg_match('~^/admin(?:/|$|\?)~', $intended) && !str_starts_with($intended, '/admin/login') && !str_starts_with($intended, '/admin/logout') && !str_starts_with($intended, '/admin/verify')) {
            return $this->redirect(ltrim($intended, '/'));
        }
        return $this->redirect('admin');
    }

    // ------------------------------------------------------------------------------------------------------------- step two: the e-mailed code

    public function showVerify(Request $request): Response
    {
        $p = TwoFactor::pending();
        if ($p === null) {
            return $this->redirect('admin/login');
        }
        $u = User::find((int) $p['uid']);
        return $this->render('admin/auth/verify', [
            'masked' => TwoFactor::maskedEmail((string) ($u['email'] ?? '')), 'minutes' => TwoFactor::CODE_MINUTES, 'trustDays' => TwoFactor::trustDays(),
            'wait' => max(0, TwoFactor::RESEND_SECONDS - (time() - (int) $p['sent'])),
        ], ['title' => 'Check your e-mail', 'meta' => ['robots' => 'noindex,nofollow']])->withHeaders(self::NO_STORE);
    }

    public function verify(Request $request): Response
    {
        $r = TwoFactor::verify((string) $request->post('code', ''), $request);
        if (!$r['ok']) {
            $err = (string) ($r['error'] ?? '');
            AuditLog::record('auth.2fa_failed', 'user', null, 'A wrong or expired sign-in code was typed');
            if ($err === 'wrong') {
                Flash::error('That code is not right. Check the newest e-mail and try again.');
                return $this->redirect('admin/verify');
            }
            Flash::error($err === 'too_many' ? 'Too many tries. Please wait a few minutes.' : 'This sign-in has ended. Please sign in again.');
            return $this->redirect('admin/login');
        }
        $user = (array) $r['user'];
        Auth::login($user);
        if ($request->bool('trust')) {
            TwoFactor::trust($user, $request);
        }
        AuditLog::record('auth.login', 'user', (int) $user['id'], 'Signed in with an e-mailed code' . ($request->bool('trust') && TwoFactor::trustDays() > 0 ? ' and remembered this browser' : ''));
        return $this->afterLogin();
    }

    public function resend(Request $request): Response
    {
        $r = TwoFactor::resend($request);
        if ($r['ok']) {
            Flash::success('A new code was sent. Use the newest e-mail.');
        } elseif (($r['error'] ?? '') === 'wait') {
            Flash::info('Please wait ' . (int) ($r['wait'] ?? 30) . ' seconds before asking for another code.');
        } elseif (($r['error'] ?? '') === 'expired') {
            Flash::error('This sign-in has ended. Please sign in again.');
            return $this->redirect('admin/login');
        } else {
            Flash::error('A new code could not be sent right now. Please try again in a minute.');
        }
        return $this->redirect('admin/verify');
    }

    public function cancelVerify(Request $request): Response
    {
        TwoFactor::cancel();
        return $this->redirect('admin/login');
    }

    // ------------------------------------------------------------------------------------------------------------- the lock screen

    public function showLocked(Request $request): Response
    {
        if (!AdminLock::isLocked()) {
            return $this->redirect('admin');
        }
        return $this->render('admin/auth/locked', ['name' => (string) (Auth::user()['name'] ?? ''), 'email' => (string) (Auth::user()['email'] ?? '')], ['title' => 'Screen locked', 'meta' => ['robots' => 'noindex,nofollow']])->withHeaders(self::NO_STORE);
    }

    public function unlock(Request $request): Response
    {
        $uid = (int) Auth::id();
        if (!RateLimit::attempt('unlock:' . $uid, 5, 900)) {
            AuditLog::record('auth.unlock_blocked', 'user', $uid, 'Too many wrong passwords on the lock screen: signed out');
            Auth::logout();
            Session::start(true);
            Flash::error('Too many wrong passwords. You have been signed out.');
            return $this->redirect('admin/login');
        }
        $row = User::find($uid);
        if ($row === null || !Auth::verifyPassword((string) $request->post('password', ''), (string) $row['password_hash'])) {
            AuditLog::record('auth.unlock_failed', 'user', $uid, 'Wrong password on the lock screen');
            Flash::error('That password is not right.');
            return $this->redirect('admin/locked');
        }
        AdminLock::unlock();
        $back = (string) Session::pull('intended_unlock', '');
        if ($back !== '' && preg_match('~^/admin(?:/|$|\?)~', $back) && !str_starts_with($back, '/admin/locked') && !str_starts_with($back, '/admin/logout')) {
            return $this->redirect(ltrim($back, '/'));
        }
        return $this->redirect('admin');
    }

    // ------------------------------------------------------------------------------------------------------------- forgotten password

    public function showForgot(Request $request): Response
    {
        return $this->render('admin/auth/forgot', [], ['title' => 'Forgot password', 'meta' => ['robots' => 'noindex,nofollow']])->withHeaders(self::NO_STORE);
    }

    /** Same answer whether or not the address belongs to an account. */
    public function sendReset(Request $request): Response
    {
        AdminPasswordReset::request($request->string('email'), $request->ip());
        Flash::success('If that address belongs to an admin account, we have sent a link to choose a new password. It works for ' . AdminPasswordReset::RESET_MINUTES . ' minutes.');
        return $this->redirect('admin/login');
    }

    public function showReset(Request $request, string $token): Response
    {
        $valid = AdminPasswordReset::userForToken($token) !== null;
        return $this->render('admin/auth/reset', ['token' => $token, 'valid' => $valid, 'min' => (int) Config::get('auth.min_password', 10)],
            ['title' => 'Choose a new password', 'meta' => ['robots' => 'noindex,nofollow']])->withHeaders(self::NO_STORE + ['Referrer-Policy' => 'no-referrer']);
    }

    public function doReset(Request $request, string $token): Response
    {
        $back = 'admin/reset-password/' . $token;
        $data = $this->validate(['password' => 'required|max:200|confirmed'], ['password.confirmed' => 'The new passwords do not match.'], ['password' => 'New password'], $back);
        $res = AdminPasswordReset::reset($token, (string) $data['password']);
        if (!$res['ok']) {
            $this->fail(['password' => (string) $res['error']], $back);
        }
        Flash::success('Your password has been changed. Sign in with the new password.');
        return $this->redirect('admin/login');
    }

    public function logout(Request $request): Response
    {
        AuditLog::record('auth.logout', 'user', Auth::id(), 'Signed out');
        Auth::logout();
        Session::start(true);                // fresh session so the flash message below survives
        Flash::success('You have been signed out.');
        return $this->redirect('admin/login');
    }

    public function showPassword(Request $request): Response
    {
        $forced = Auth::mustChangePassword();
        $opts = ['title' => 'Change password', 'meta' => ['robots' => 'noindex,nofollow']];
        if (!$forced) {
            // a voluntary change happens inside the normal admin layout
            $opts += ['layout' => 'layouts/admin', 'nav' => 'profile', 'breadcrumbs' => [['My profile', url('admin/profile')], ['Change password', null]]];
        }
        return $this->render('admin/auth/password', [
            'forced' => $forced,
            'min'    => (int) Config::get('auth.min_password', 10),
        ], $opts)->withHeaders(self::NO_STORE);
    }

    public function updatePassword(Request $request): Response
    {
        $user = Auth::user();
        $min = (int) Config::get('auth.min_password', 10);
        $data = $this->validate([
            'current_password' => 'required',
            'password'         => 'required|min:' . $min . '|max:200|confirmed',
        ], ['password.confirmed' => 'The new passwords do not match.'], ['current_password' => 'Current password', 'password' => 'New password'], 'admin/profile/password');

        $row = User::find((int) $user['id']);
        if (!$row || !Auth::verifyPassword($data['current_password'], (string) $row['password_hash'])) {
            AuditLog::record('auth.password_failed', 'user', (int) $user['id'], 'Wrong current password while changing the password');
            $this->fail(['current_password' => 'Your current password is not correct.'], 'admin/profile/password');
        }
        if (hash_equals((string) $data['current_password'], (string) $data['password'])) {
            $this->fail(['password' => 'Choose a password that is different from the current one.'], 'admin/profile/password');
        }
        if (($problem = Auth::passwordProblem($data['password'])) !== null) {
            $this->fail(['password' => $problem], 'admin/profile/password');
        }
        Auth::changePassword((int) $user['id'], $data['password'], false);
        AuditLog::record('user.password_change', 'user', (int) $user['id'], 'Changed own password');
        Session::regenerate();
        Flash::success('Your password has been updated.');
        return $this->redirect('admin');
    }
}
