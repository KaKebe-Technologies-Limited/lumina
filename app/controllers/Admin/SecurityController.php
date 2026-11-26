<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Auth;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Settings;
use App\Models\User;
use App\Services\Security\AdminLock;
use App\Services\Security\CloudflareStatus;
use App\Services\Security\Turnstile;
use App\Services\Security\SecurityStatus;
use App\Services\Security\TwoFactor;

/**
 * Admin > Security (administrators with settings.manage): the safety checklist, the e-mail two-step sign-in (turned ON only after the person has received and typed a test code, so a broken
 * mail set-up can never lock the owner out), the screen-lock time, the "remember this browser" days, and the latest sign-in activity.
 * Routes: GET admin/security, POST admin/security/{2fa-test|2fa-on|2fa-off|settings|devices-forget}.
 */
final class SecurityController extends AdminController
{
    public const LOCK_CHOICES = [0 => 'Never', 5 => '5 minutes', 10 => '10 minutes', 15 => '15 minutes', 30 => '30 minutes', 60 => '1 hour'];
    public const TRUST_CHOICES = [0 => 'Do not offer it', 7 => '7 days', 14 => '14 days', 30 => '30 days', 60 => '60 days', 90 => '90 days'];

    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        $me = (array) Auth::user();
        return $this->render('admin/security/index', [
            'checks' => SecurityStatus::checks($request),
            'activity' => SecurityStatus::activity(),
            'recent' => SecurityStatus::recent(15),
            'twoFactor' => TwoFactor::enabled(), 'forced' => TwoFactor::forced(), 'ready' => TwoFactor::install(),
            'email' => (string) ($me['email'] ?? ''), 'masked' => TwoFactor::maskedEmail((string) ($me['email'] ?? '')),
            'lockMinutes' => AdminLock::minutes(), 'lockChoices' => self::LOCK_CHOICES,
            'cf' => CloudflareStatus::request($request), 'cfSteps' => CloudflareStatus::STEPS, 'cfTicks' => CloudflareStatus::ticks(), 'cfCache' => CloudflareStatus::lastCheck(),
            'turnstile' => Turnstile::status(),
            'trustDays' => TwoFactor::trustDays(), 'trustChoices' => self::TRUST_CHOICES, 'devices' => TwoFactor::deviceCount(),
        ], ['title' => 'Security', 'nav' => 'security', 'breadcrumbs' => [['Security', null]]]);
    }

    /** Step 1 of turning two-step sign-in on: a test code goes to the signed-in administrator's own address. */
    public function sendTest(Request $request): Response
    {
        $this->authorize('settings.manage');
        $r = TwoFactor::sendCode((array) Auth::user(), $request->ip(), 'enable');
        $r['ok'] ? Flash::success('A test code was sent to ' . TwoFactor::maskedEmail((string) Auth::user()['email']) . '. Type it below to turn two-step sign-in on.')
            : Flash::error(($r['error'] ?? '') === 'too_many' ? 'Too many codes were requested. Please wait an hour.' : 'The e-mail could not be sent. Fix the e-mail settings first (Administration > E-mail and sign-in), then try again.');
        return $this->redirect('admin/security#two-step');
    }

    /** Step 2: the typed code proves the mail works; only then the switch is turned on. */
    public function turnOn(Request $request): Response
    {
        $this->authorize('settings.manage');
        if (!TwoFactor::checkCode((int) Auth::id(), (string) $request->post('code', ''), 'enable')) {
            $this->audit('security.2fa_on_failed', 'settings', null, 'A wrong test code was typed while turning two-step sign-in on');
            Flash::error('That code is not right or has expired. Send a new test code and try again.');
            return $this->redirect('admin/security#two-step');
        }
        Settings::setMany(['security_2fa' => '1']);
        $this->audit('security.2fa_on', 'settings', null, 'Two-step sign-in by e-mail turned ON');
        Flash::success('Two-step sign-in is ON. From the next sign-in every administrator receives a code by e-mail.');
        return $this->redirect('admin/security#two-step');
    }

    public function turnOff(Request $request): Response
    {
        $this->authorize('settings.manage');
        $row = User::find((int) Auth::id());
        if ($row === null || !Auth::verifyPassword((string) $request->post('password', ''), (string) $row['password_hash'])) {
            $this->audit('security.2fa_off_failed', 'settings', null, 'Wrong password while turning two-step sign-in off');
            Flash::error('That password is not right.');
            return $this->redirect('admin/security#two-step');
        }
        Settings::setMany(['security_2fa' => '0']);
        TwoFactor::forgetDevices();
        $this->audit('security.2fa_off', 'settings', null, 'Two-step sign-in by e-mail turned OFF');
        Flash::warning('Two-step sign-in is OFF. Turn it back on as soon as you can.');
        return $this->redirect('admin/security#two-step');
    }

    public function saveSettings(Request $request): Response
    {
        $this->authorize('settings.manage');
        $lock = (int) $request->post('lock_minutes', '15');
        $trust = (int) $request->post('trust_days', '30');
        if (!isset(self::LOCK_CHOICES[$lock]) || !isset(self::TRUST_CHOICES[$trust])) {
            Flash::error('Please choose from the lists.');
            return $this->redirect('admin/security#settings');
        }
        Settings::setMany(['security_lock_minutes' => (string) $lock, 'security_trust_days' => (string) $trust]);
        if ($trust === 0) {
            TwoFactor::forgetDevices();
        }
        $this->audit('security.settings', 'settings', null, 'Screen lock ' . ($lock > 0 ? $lock . ' minutes' : 'off') . ', remember-browser ' . ($trust > 0 ? $trust . ' days' : 'off'));
        Flash::success('Saved.');
        return $this->redirect('admin/security#settings');
    }

    /** Cloudflare card: the owner's own ticks (a record only; nothing is changed at Cloudflare). */
    public function cloudflareSteps(Request $request): Response
    {
        $this->authorize('settings.manage');
        $posted = $request->post('steps', []);
        CloudflareStatus::saveTicks(is_array($posted) ? array_values(array_filter($posted, 'is_string')) : []);
        $this->audit('security.cloudflare_steps', 'settings', null, 'Cloudflare checklist updated');
        Flash::success('Saved.');
        return $this->redirect('admin/security#cloudflare');
    }

    /** Cloudflare card: fetch the site's own key pages without a cookie and report their cache headers. */
    public function cloudflareCheck(Request $request): Response
    {
        $this->authorize('settings.manage');
        $rows = CloudflareStatus::runCacheCheck(rtrim((string) abs_url(''), '/'));
        $bad = count(array_filter($rows, static fn(array $r): bool => $r['status'] === 'bad'));
        $this->audit('security.cloudflare_cache_check', 'settings', null, 'Cache-header check: ' . $bad . ' problem' . ($bad === 1 ? '' : 's'));
        $bad > 0 ? Flash::error($bad . ' page' . ($bad === 1 ? '' : 's') . ' could be stored by a shared cache. See the list below.') : Flash::success('Cache headers look right.');
        return $this->redirect('admin/security#cloudflare');
    }

    public function forgetDevices(Request $request): Response
    {
        $this->authorize('settings.manage');
        $n = TwoFactor::forgetDevices();
        $this->audit('security.devices_forgotten', 'settings', null, 'Forgot every remembered browser (' . $n . ')');
        Flash::success($n > 0 ? $n . ' remembered browser' . ($n === 1 ? '' : 's') . ' forgotten: everyone types a code at the next sign-in.' : 'No browser was remembered.');
        return $this->redirect('admin/security#settings');
    }
}
