<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\HttpException;
use App\Services\Academy\GoogleSignIn;
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerPortrait;
use App\Services\Academy\VerificationCode;

/**
 * LAF Academy learner accounts: sign up, confirm the e-mail address, sign in / out, forgotten password, and "my account" (details, password, delete).
 * Every public POST carries the honeypot + throttle middleware (routes/web_academy.php); CSRF is automatic. Answers never reveal whether an address has an account.
 */
final class AcademyAccountController extends AcademyBaseController
{
    // ------------------------------------------------------------------------------------------------------------- sign up

    public function registerForm(Request $request): Response
    {
        $this->rememberNext($request->string('next'));
        if ($this->learner() !== null) {
            return $this->redirect('academy/dashboard');
        }
        return $this->renderAcademy('web/academy/register', ['open' => LearnerAuth::registrationOpen()], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Create an account', null]],
            'meta' => ['title' => 'Create your free account', 'description' => 'Join the LAF Academy for free ICT courses, virtual classes and certificates.'],
        ]);
    }

    public function register(Request $request): Response
    {
        $this->gate();
        if (!LearnerAuth::registrationOpen()) {
            Flash::error((string) content('academy', 'auth.register_closed'));
            return $this->redirect('academy/register');
        }
        if (!$request->bool('agree')) {
            $this->fail(['agree' => 'Please tick the box to continue.'], 'academy/register');
        }
        $res = LearnerAuth::register([
            'name' => $request->string('name'), 'email' => $request->string('email'), 'password' => $request->string('password'),
            'phone' => $request->string('phone'), 'country' => $request->string('country'), 'whatsapp' => $request->string('whatsapp'),
        ], $request->ip());
        if (!$res['ok']) {
            $this->fail($res['errors'], 'academy/register');
        }
        // the optional picture (attached or a selfie): kept private; a problem with it never stops the sign-up
        $photo = $request->file('photo');
        if ($photo !== null && isset($res['learner']['id'])) {
            $pr = LearnerPortrait::save((int) $res['learner']['id'], $photo, false);
            if (!$pr['ok']) {
                Flash::info('Your account was created, but your picture could not be saved: ' . $pr['error'] . ' You can add it later from your account.');
            }
        }
        if ($res['verify']) {
            \App\Core\Session::put('academy_check_email', LearnerAuth::normaliseEmail($request->string('email')));
            \App\Core\Session::put('academy_reg_no', (string) ($res['learner']['reg_no'] ?? ''));
            return $this->redirect(LearnerAuth::emailVerificationOn() ? 'academy/check-email' : 'academy/pending');
        }
        // no confirmation needed: sign the new learner in at once (an address that already has an account gets no session)
        if (isset($res['learner'])) {
            LearnerAuth::signIn($res['learner']);
            Flash::success('Welcome to the LAF Academy! Choose a course to start.');
            return $this->redirect('academy/dashboard');
        }
        Flash::info('If this address is new, your account is ready. Otherwise sign in or reset your password.');
        return $this->redirect('academy/login');
    }

    /** The learner types the 6-digit code from the e-mail or WhatsApp message. */
    public function verifyCode(Request $request): Response
    {
        $this->gate();
        $email = $request->string('email') !== '' ? $request->string('email') : (string) \App\Core\Session::get('academy_check_email', '');
        $res = VerificationCode::check($email, $request->string('code'), $request->ip());
        if (!$res['ok']) {
            \App\Core\Session::put('academy_check_email', LearnerAuth::normaliseEmail($email));
            $this->fail(['code' => (string) $res['error']], 'academy/check-email#code');
        }
        LearnerAuth::signIn($res['learner']);
        Flash::success('Your account is confirmed. Welcome to the LAF Academy!');
        return $this->redirect('academy/dashboard');
    }

    /** Send a code to the WhatsApp number the learner typed. */
    public function verifyWhatsApp(Request $request): Response
    {
        $this->gate();
        $email = $request->string('email') !== '' ? $request->string('email') : (string) \App\Core\Session::get('academy_check_email', '');
        $res = VerificationCode::sendWhatsApp($email, $request->string('whatsapp'), $request->ip());
        \App\Core\Session::put('academy_check_email', LearnerAuth::normaliseEmail($email));
        if (!$res['ok'] && $res['error'] === 'number') {
            $this->fail(['whatsapp' => $res['shown']], 'academy/check-email#whatsapp');
        }
        $res['ok'] ? Flash::success($res['shown']) : Flash::error($res['shown']);
        return $this->redirect('academy/check-email#code');
    }

    public function checkEmail(Request $request): Response
    {
        return $this->renderAcademy('web/academy/check-email', ['regNo' => (string) \App\Core\Session::get('academy_reg_no', ''), 'email' => (string) \App\Core\Session::get('academy_check_email', ''), 'whatsappOn' => \App\Services\Academy\WhatsAppSender::enabled() && VerificationCode::ready(), 'codeOn' => VerificationCode::ready()], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Check your e-mail', null]],
            'meta' => ['title' => 'Check your e-mail'],
        ], true);
    }

    /** After sign-up while administrators approve accounts (no e-mail code): the account is received, sign in after the approval time. */
    public function pending(Request $request): Response
    {
        return $this->renderAcademy('web/academy/pending', ['regNo' => (string) \App\Core\Session::get('academy_reg_no', ''), 'hours' => LearnerAuth::approvalText()], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Application received', null]],
            'meta' => ['title' => 'Application received'],
        ], true);
    }

    public function resend(Request $request): Response
    {
        $this->gate();
        $email = $request->string('email') !== '' ? $request->string('email') : (string) \App\Core\Session::get('academy_check_email', '');
        if ($email !== '') {
            LearnerAuth::resendVerification($email);
        }
        Flash::success('If that address has an account waiting to be confirmed, we sent the link again.');
        return $this->redirect('academy/check-email');
    }

    public function verify(Request $request, string $token): Response
    {
        $this->gate();
        $l = LearnerAuth::verify($token);
        if ($l === null) {
            return $this->renderAcademy('web/academy/message', [
                'heading' => 'This link does not work',
                'text' => 'The confirmation link has expired or was already used. If you have not confirmed your address yet, ask for a new link below; if you have, just sign in.',
                'actions' => [['label' => 'Sign in', 'url' => url('academy/login')], ['label' => 'Send me a new link', 'url' => url('academy/check-email')]],
            ], ['bodyClass' => 'is-academy is-academy-auth', 'meta' => ['title' => 'Confirmation link'], 'status' => 410], true);
        }
        LearnerAuth::signIn($l);
        Flash::success('Your e-mail address is confirmed. Welcome to the LAF Academy!');
        return $this->redirect('academy/dashboard');
    }

    // ------------------------------------------------------------------------------------------------------------- sign in / out

    public function loginForm(Request $request): Response
    {
        $this->rememberNext($request->string('next'));
        if ($this->learner() !== null) {
            return $this->redirect('academy/dashboard');
        }
        return $this->renderAcademy('web/academy/login', [], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Sign in', null]],
            'meta' => ['title' => 'Sign in to the LAF Academy', 'description' => 'Sign in to continue your courses.'],
        ]);
    }

    public function login(Request $request): Response
    {
        $this->gate();
        $res = LearnerAuth::login($request->string('email'), $request->string('password'), $request->ip());
        if (!$res['ok']) {
            if ($res['unverified']) {
                \App\Core\Session::put('academy_check_email', LearnerAuth::normaliseEmail($request->string('email')));
                \App\Core\Session::put('academy_reg_no', (string) ($res['learner']['reg_no'] ?? ''));
                Flash::warning((string) $res['error']);
                return $this->redirect(LearnerAuth::emailVerificationOn() ? 'academy/check-email' : 'academy/pending');
            }
            if (!empty($res['rejected'])) {
                Flash::error((string) $res['error']);
                return $this->redirect('academy/contact');
            }
            $this->fail(['password' => (string) $res['error']], 'academy/login');
        }
        if (LearnerAuth::missingContact((array) $res['learner'])) {
            Flash::info('Please add your phone number, country and WhatsApp number to continue. They help us to support you.');
            return $this->redirect('academy/account#details');
        }
        return $this->redirect($this->nextAfterLogin());
    }

    // ------------------------------------------------------------------------------------------------------------- Continue with Google

    public function googleStart(Request $request): Response
    {
        $this->gate();
        if (!GoogleSignIn::enabled()) {
            Flash::info('Sign in with Google is not available yet. Please use your e-mail and password.');
            return $this->redirect('academy/login');
        }
        if ($this->learner() !== null) {
            return $this->redirect('academy/dashboard');
        }
        $this->rememberNext($request->string('next'));
        $b = GoogleSignIn::begin();
        \App\Core\Session::put('academy_google', $b['session']);
        return $this->redirect($b['url']);
    }

    public function googleCallback(Request $request): Response
    {
        $this->gate();
        if (!GoogleSignIn::enabled()) {
            return $this->redirect('academy/login');
        }
        $session = (array) \App\Core\Session::pull('academy_google', []);
        $res = GoogleSignIn::finish($request->query(), $session);
        if (!$res['ok']) {
            Flash::error((string) $res['error']);
            return $this->redirect('academy/login');
        }
        LearnerAuth::signIn($res['learner']);
        if (LearnerAuth::missingContact((array) $res['learner'])) {
            Flash::info(($res['created'] ? 'Welcome to the LAF Academy! ' : '') . 'Please add your phone number, country and WhatsApp number to continue. They help us to support you.');
            return $this->redirect('academy/account#details');
        }
        Flash::success($res['created'] ? 'Welcome to the LAF Academy! Your account is ready.' : 'You are signed in with Google.');
        return $this->redirect($this->nextAfterLogin());
    }

    public function logout(Request $request): Response
    {
        LearnerAuth::logout();
        Flash::success('You are signed out.');
        return $this->redirect('academy');
    }

    // ------------------------------------------------------------------------------------------------------------- forgotten password

    public function forgotForm(Request $request): Response
    {
        return $this->renderAcademy('web/academy/forgot', [], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['Forgotten password', null]],
            'meta' => ['title' => 'Forgotten password'],
        ], true);
    }

    public function forgot(Request $request): Response
    {
        $this->gate();
        LearnerAuth::requestReset($request->string('email'), $request->ip());
        Flash::success('If that address has an account, we sent a link to choose a new password. It works for one hour.');
        return $this->redirect('academy/forgot');
    }

    public function resetForm(Request $request, string $token): Response
    {
        $this->gate();
        $ok = LearnerAuth::resetTokenValid($token);
        return $this->renderAcademy('web/academy/reset', ['token' => $token, 'valid' => $ok], [
            'bodyClass' => 'is-academy is-academy-auth',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['New password', null]],
            'meta' => ['title' => 'Choose a new password'], 'status' => $ok ? 200 : 410,
        ], true);
    }

    public function reset(Request $request, string $token): Response
    {
        $this->gate();
        $pw = $request->string('password');
        if ($pw !== $request->string('password_confirm')) {
            $this->fail(['password_confirm' => 'The two passwords are not the same.'], 'academy/reset/' . $token);
        }
        $res = LearnerAuth::resetPassword($token, $pw);
        if (!$res['ok']) {
            $this->fail(['password' => (string) $res['error']], 'academy/reset/' . $token);
        }
        Flash::success('Your password was changed. You can sign in now.');
        return $this->redirect('academy/login');
    }

    // ------------------------------------------------------------------------------------------------------------- my account

    public function account(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        return $this->renderAcademy('web/academy/account', ['me' => $l, 'portrait' => LearnerPortrait::of((int) $l['id']), 'portraitReady' => LearnerPortrait::ready(), 'unread' => \App\Services\Academy\LearnerMessages::unreadForLearner((int) $l['id'])], [
            'bodyClass' => 'is-academy is-academy-account',
            'breadcrumbs' => [[$this->academyLabel(), url('academy')], ['My portal', url('academy/dashboard')], ['Profile', null]],
            'meta' => ['title' => 'My profile | My portal'],
        ], true);
    }

    public function updateProfile(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $name = trim((string) preg_replace('/\s+/u', ' ', strip_tags($request->string('name'))));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $this->fail(['name' => 'Please enter your full name.'], 'academy/account');
        }
        $c = LearnerAuth::contact($request->string('phone'), $request->string('country'), $request->string('whatsapp'));
        if ($c['errors'] !== []) {
            $this->fail($c['errors'], 'academy/account#details');
        }
        $upd = ['name' => $name, 'phone' => mb_substr($c['phone'], 0, 40), 'country' => mb_substr($c['country'], 0, 80)];
        if (LearnerAuth::bioReady()) {
            $bio = trim((string) preg_replace('/\s+/u', ' ', strip_tags($request->string('bio'))));
            $upd['bio'] = $bio !== '' ? mb_substr($bio, 0, 600) : null;
        }
        Database::update('academy_learners', $upd, 'id = ?', [(int) $l['id']]);
        \App\Services\Academy\VerificationCode::saveNumber((int) $l['id'], $c['whatsapp']);
        LearnerAuth::flush();
        Flash::success('Your details were saved. Certificates you already earned keep the name they were issued with.');
        return $this->redirect($this->nextAfterLogin('academy/account'));
    }

    public function photo(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            throw new HttpException(404, 'Not found');
        }
        return (LearnerPortrait::download((int) $l['id']) ?? throw new HttpException(404, 'Not found'))->header('Cache-Control', 'private, no-store');
    }

    public function savePhoto(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $consent = $request->bool('consent');
        $file = $request->file('photo');
        if ($file === null) {
            // no new file: only the consent choice changed
            LearnerPortrait::setConsent((int) $l['id'], $consent);
            Flash::success($consent ? 'Your picture may now be shown on the Academy page.' : 'Your picture is private again.');
            return $this->redirect('academy/account#portrait');
        }
        $r = LearnerPortrait::save((int) $l['id'], $file, $consent);
        if (!$r['ok']) {
            if ($request->string('back') === 'dashboard') {
                Flash::error('Your picture could not be saved: ' . $r['error']);
                return $this->redirect('academy/dashboard');
            }
            $this->fail(['photo' => $r['error']], 'academy/account#portrait');
        }
        if ($request->string('back') === 'dashboard') {
            Flash::success('Your profile picture was saved.');
            return $this->redirect('academy/dashboard');
        }
        Flash::success($consent ? 'Your picture was saved and may be shown on the Academy page.' : 'Your picture was saved. Only you and the Academy team can see it.');
        return $this->redirect('academy/account#portrait');
    }

    public function removePhoto(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        LearnerPortrait::remove((int) $l['id']);
        Flash::success('Your picture was removed.');
        return $this->redirect('academy/account#portrait');
    }

    /** A learner's picture, only when they agreed that it may be shown. */
    public function publicPhoto(Request $request, int $id): Response
    {
        if (!LearnerPortrait::isPublic($id)) {
            throw new HttpException(404, 'Not found');
        }
        return (LearnerPortrait::download($id) ?? throw new HttpException(404, 'Not found'))->header('Cache-Control', 'public, max-age=300');
    }

    public function changePassword(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        if ($request->string('new_password') !== $request->string('new_password_confirm')) {
            $this->fail(['new_password_confirm' => 'The two passwords are not the same.'], 'academy/portal/security');
        }
        $res = \App\Services\Academy\LearnerAuth::changePassword((int) $l['id'], $request->string('current_password'), $request->string('new_password'));
        if (!$res['ok']) {
            $this->fail(['current_password' => (string) $res['error']], 'academy/portal/security');
        }
        Flash::success('Your password was changed.');
        return $this->redirect('academy/portal/security');
    }

    public function deleteAccount(Request $request): Response
    {
        $l = $this->learner();
        if ($l === null) {
            return $this->loginRedirect();
        }
        $row = Database::one('SELECT password_hash FROM academy_learners WHERE id = ?', [(int) $l['id']]);
        if ($row === null || !\App\Core\Auth::verifyPassword($request->string('password'), (string) $row['password_hash']) || strtoupper(trim($request->string('confirm'))) !== 'DELETE') {
            $this->fail(['confirm' => 'Type DELETE and enter your password to delete the account.'], 'academy/portal/security');
        }
        LearnerAuth::deleteAccount((int) $l['id']);
        Flash::success('Your account and your learning records were deleted.');
        return $this->redirect('academy');
    }
}
