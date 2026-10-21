<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Response;
use App\Core\Session;
use App\Services\Academy\LearnerAuth;

/**
 * Shared helpers of the public LAF Academy controllers: the on/off switch (Settings > LAF Academy), the signed-in learner, the "sign in first" redirect that
 * brings the learner back, and rendering inside the Academy page (wording from Pages > LAF Academy). Private screens are never indexed.
 */
abstract class AcademyBaseController extends Controller
{
    protected function gate(): void
    {
        if ((string) setting('academy_enabled', '1') !== '1') {
            throw new HttpException(404, 'Not found');
        }
    }

    protected function learner(): ?array
    {
        return LearnerAuth::current();
    }

    /** Where to send a visitor who must sign in: the sign-in page, remembering where they wanted to go (a path inside /academy only). */
    protected function loginRedirect(?string $message = null): Response
    {
        $next = ltrim($this->request->path(), '/');
        $base = trim(base_path(), '/');
        if ($base !== '' && str_starts_with($next, $base . '/')) {
            $next = substr($next, strlen($base) + 1);
        }
        if ($this->request->isGet() && str_starts_with($next, 'academy/')) {
            Session::put('academy_next', $next . ($this->request->uri() !== '' && str_contains($this->request->uri(), '?') ? '?' . explode('?', $this->request->uri(), 2)[1] : ''));
        }
        Flash::info($message ?? 'Please sign in to continue.');
        return $this->redirect('academy/login');
    }

    /** Remember ?next=academy/... (a path inside the Academy only) so sign-in or sign-up brings the learner back to it. */
    protected function rememberNext(string $next): void
    {
        $next = ltrim(trim($next), '/');
        if (preg_match('~^academy/[A-Za-z0-9/_\-.]*$~', $next) === 1 && !str_contains($next, '..')) {
            Session::put('academy_next', $next);
        }
    }

    /** The remembered destination after sign-in (only paths inside /academy are accepted). */
    protected function nextAfterLogin(string $default = 'academy/dashboard'): string
    {
        $next = (string) Session::pull('academy_next', '');
        return preg_match('~^academy/[A-Za-z0-9/_\-.]*(\?[A-Za-z0-9=&_\-.%]*)?$~', $next) === 1 ? $next : $default;
    }

    /** @param array<string,mixed> $data @param array<string,mixed> $opts */
    protected function renderAcademy(string $view, array $data = [], array $opts = [], bool $private = false): Response
    {
        $this->gate();
        $opts += ['bodyClass' => 'is-academy'];
        if ($private) {
            $opts['meta'] = (array) ($opts['meta'] ?? []) + ['robots' => 'noindex,nofollow'];
        }
        $data += ['learner' => $this->learner()];
        return $this->renderPage('academy', $view, $data, $opts);
    }

    protected function academyLabel(): string
    {
        return (string) (\App\Core\SitePages::get('academy')['nav_label'] ?? 'LAF Academy');
    }
}
