<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\UploadException;
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormFileStore;
use App\Services\Forms\FormMailer;
use App\Services\Forms\FormService;
use App\Services\Forms\FormSpam;
use App\Services\Forms\FormText;
use App\Services\Forms\FormValidator;
use App\Services\Forms\SubmissionService;

/**
 * PUBLIC forms. Contract: docs/FORMS.md section 8.
 *
 * REFERENCE IMPLEMENTATION by the architect: the security-relevant flow of submit() (spam checks -> context -> validation -> private file storage ->
 * transactional store -> mails -> redirect) is final; the PUBLIC agent owns the views (views/web/forms/*), the SCSS and assets/js/forms.js and may
 * refine the controller (error rendering, AJAX responses) but must keep the order of the steps and never echo hostile input back.
 *
 *   GET  /forms                  directory (404 unless switched on)
 *   GET  /forms/{slug}           the form or a friendly notice (+ ?job=<slug> for forms attached to a vacancy)
 *   POST /forms/{slug}           submission. Browser: redirect (PRG). JSON clients (Accept: application/json or X-Requested-With): see jsonOut()
 *   GET  /forms/{slug}/thanks    thank-you page
 */
final class FormController extends Controller
{
    public function index(Request $request): Response
    {
        if (!FormService::directoryEnabled()) {
            throw new HttpException(404, 'Not found');
        }
        return $this->renderPage('forms', 'web/forms/index', ['forms' => FormService::directory()], [
            'bodyClass'   => 'is-forms-index',
            'breadcrumbs' => [[(string) (\App\Core\SitePages::get('forms')['nav_label'] ?? 'Forms'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $form = $this->load($slug);
        $avail = FormService::availability($form);
        $staff = $this->isStaff();
        if (!$avail['visible'] && !$staff) {
            throw new HttpException(404, 'Not found');
        }
        $ctx = FormService::resolveContext($form, $request->string('job'));
        if ($ctx !== null && !$ctx['ok']) {
            return $this->notice($form, 'job', $avail, 200);
        }
        if (!$avail['can_submit'] && !$staff) {
            return $this->notice($form, $avail['state'], $avail, 200);
        }
        if ($request->string('print') === '1' && !empty($form['cfg']['allow_print'])) {
            return $this->printSheet($form);
        }
        return $this->page($form, [
            'values'  => [],
            'errors'  => [],
            'context' => $ctx,
            'preview' => $staff && !$avail['can_submit'],
        ]);
    }

    public function submit(Request $request, string $slug): Response
    {
        $form = $this->load($slug);
        $avail = FormService::availability($form);
        if (!$avail['visible']) {
            throw new HttpException(404, 'Not found');
        }
        if (!$avail['can_submit']) {
            return $this->state($request, $form, $avail['state'], $avail);
        }

        // 1. spam traps and flood limit (nothing is stored when one fires)
        $spam = FormSpam::check($request, $form);
        if (!$spam['ok']) {
            if ($spam['silent']) {
                return $this->done($request, $form, null, true, null);      // pretend success: a bot learns nothing
            }
            $msg = FormText::get('errors.' . ($spam['reason'] === 'throttled' ? 'throttled' : ($spam['reason'] === 'too_fast' ? 'too_fast' : 'expired')));
            return $this->jsonOut($request, ['ok' => false, 'error' => $spam['reason'], 'message' => $msg], $spam['status'])
                ?? $this->page($form, ['values' => $this->posted($request, $form), 'errors' => ['_form' => $msg], 'context' => null, 'preview' => false, 'hpTs' => $spam['reason'] === 'expired' ? null : $this->postedStamp($request)], $spam['status'] === 429 ? 429 : 422);
        }

        // 2. server-verified context (a vacancy)
        $ctx = FormService::resolveContext($form, $request->string('job'));
        if ($ctx !== null && !$ctx['ok']) {
            return $this->jsonOut($request, ['ok' => false, 'error' => 'job', 'message' => ''], 409) ?? $this->notice($form, 'job', $avail, 200);
        }

        // 3. validate every visible field (conditions are re-evaluated here)
        $def = (array) $form['def'];
        $files = FormValidator::collectFiles($def, $request);
        $res = FormValidator::validate($def, $request->all(), $files, ['settings' => $form['cfg']]);
        if (!$res['ok']) {
            $json = $this->jsonOut($request, ['ok' => false, 'error' => 'validation', 'errors' => (object) $res['errors'], 'error_pages' => $res['error_pages'], 'summary' => FormText::get('ui.error_summary_title')], 422);
            return $json ?? $this->page($form, ['values' => $this->posted($request, $form), 'errors' => $res['errors'], 'context' => $ctx, 'preview' => false, 'hpTs' => $this->postedStamp($request)], 422);
        }

        // 4. files into private storage (all or nothing)
        $rules = [];
        foreach (FormDefinition::inputFields($def) as $f) {
            if ($f['type'] === 'file') {
                $rules[(string) $f['id']] = (array) $f['file'];
            }
        }
        $stored = [];
        try {
            $stored = FormFileStore::storeAll($res['files'], $rules);
        } catch (UploadException $e) {
            $err = ['_form' => $e->getMessage()];
            return $this->jsonOut($request, ['ok' => false, 'error' => 'upload', 'errors' => (object) $err], 422)
                ?? $this->page($form, ['values' => $this->posted($request, $form), 'errors' => $err, 'context' => $ctx, 'preview' => false, 'hpTs' => $this->postedStamp($request)], 422);
        }

        // 5. store (one transaction: availability, maximum, one-per-e-mail)
        try {
            $out = SubmissionService::store($form, $res, $stored, [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'context'    => $ctx !== null ? ['type' => $ctx['type'], 'ref' => $ctx['ref'], 'label' => $ctx['label']] : null,
            ]);
        } catch (\Throwable $e) {
            FormFileStore::deleteAll($stored);
            throw $e;
        }
        if (!$out['ok']) {
            FormFileStore::deleteAll($stored);
            if ($out['error'] === 'duplicate') {
                $err = [(string) ($out['field'] ?? '_form') => FormText::get('errors.one_per_email')];
                return $this->jsonOut($request, ['ok' => false, 'error' => 'validation', 'errors' => (object) $err], 422)
                    ?? $this->page($form, ['values' => $this->posted($request, $form), 'errors' => $err, 'context' => $ctx, 'preview' => false, 'hpTs' => $this->postedStamp($request)], 422);
            }
            return $this->state($request, $form, (string) $out['error'], FormService::availability($form));
        }

        // 6. mails (a mail problem never loses the submission)
        try {
            FormMailer::notifyStaff($form, (int) $out['id'], (array) $out['snapshot'], $out['email'] ?? null);
            FormMailer::confirmSubmitter($form, (int) $out['id'], (array) $out['snapshot'], $out['email'] ?? null);
        } catch (\Throwable $e) {
            Log::error('Forms: mail step failed', ['form' => (int) $form['id'], 'submission' => (int) $out['id'], 'error' => get_class($e)]);
        }
        return $this->done($request, $form, (string) $out['reference'], false, $ctx !== null ? (string) $ctx['ref'] : null);
    }

    public function thanks(Request $request, string $slug): Response
    {
        $form = $this->load($slug);
        $avail = FormService::availability($form);
        if (!$avail['visible'] && !$this->isStaff()) {
            throw new HttpException(404, 'Not found');
        }
        $done = Session::getFlash('forms.done');
        $mine = is_array($done) && ($done['slug'] ?? '') === $slug;
        $ref = $mine ? (string) ($done['ref'] ?? '') : '';
        $job = $mine ? (string) ($done['job'] ?? '') : '';
        return $this->render('web/forms/thanks', [
            'form'     => $form,
            'reference' => $ref,
            'job'      => $job,
            'again'    => $job === '' && $avail['can_submit'],
            'draftKey' => (string) FormService::clientConfig($form)['draftKey'],
        ], [
            'page'      => 'forms',
            'bodyClass' => 'is-form-thanks ' . $this->themeClass($form),
            'meta'      => ['title' => (string) $form['title'], 'robots' => 'noindex,nofollow', 'canonical' => '/forms/' . $form['slug'] . '/thanks'],
        ]);
    }

    // ------------------------------------------------------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function load(string $slug): array
    {
        $form = FormService::findBySlug($slug);
        if ($form === null) {
            throw new HttpException(404, 'Not found');
        }
        return $form;
    }

    private function isStaff(): bool
    {
        try {
            return Auth::check() && can('forms.manage');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * The posted answers as plain strings (files cannot be re-filled), used to re-show the form. Only multiple-choice questions keep a list; a list posted
     * for any other name (hand-made request) becomes an empty string, so the views never cast an array. @return array<string,mixed>
     */
    private function posted(Request $request, array $form): array
    {
        $multi = [];
        foreach (FormDefinition::inputFields((array) $form['def']) as $f) {
            if ($f['type'] === 'checkboxes') {
                $multi['f_' . $f['id']] = true;
            }
        }
        $out = [];
        foreach ($request->all() as $k => $v) {
            if (is_string($k) && (str_starts_with($k, 'f_') || $k === 'form_consent' || $k === 'job')) {
                if (is_array($v)) {
                    $out[$k] = isset($multi[$k]) ? array_values(array_filter($v, 'is_string')) : '';
                } else {
                    $out[$k] = is_string($v) ? $v : '';
                }
            }
        }
        return $out;
    }

    /** The signed timestamp (_hp_ts) the visitor posted, so the re-shown form keeps it (Honeypot::field reuses it only while genuine). */
    private function postedStamp(Request $request): ?string
    {
        $raw = $request->post('_hp_ts', '');
        return is_string($raw) && $raw !== '' && strlen($raw) < 120 ? $raw : null;
    }

    /** @param array<string,mixed> $data */
    private function page(array $form, array $data, int $status = 200): Response
    {
        $cfg = (array) $form['cfg'];
        $avail = FormService::availability($form);
        $noindex = !empty($cfg['seo']['noindex']) || $avail['state'] !== 'open' || $status !== 200 || ($data['preview'] ?? false);
        $title = trim((string) ($cfg['seo']['title'] ?? '')) !== '' ? (string) $cfg['seo']['title'] : (string) $form['title'];
        $desc = $this->description($form);
        return $this->render('web/forms/show', ['form' => $form, 'availability' => $avail] + $data, [
            'page'        => 'forms',
            'status'      => $status,
            'bodyClass'   => 'is-form ' . $this->themeClass($form),
            'meta'        => ['title' => $title, 'description' => $desc, 'robots' => $noindex ? 'noindex,nofollow' : 'index,follow', 'canonical' => '/forms/' . $form['slug']],
            'breadcrumbs' => [[(string) $form['title'], null]],
        ]);
    }

    /** The friendly notice instead of the form: closed | full | not_open | draft | archived | unavailable | job. */
    private function notice(array $form, string $state, array $avail, int $status): Response
    {
        return $this->render('web/forms/state', ['form' => $form, 'state' => $state, 'availability' => $avail], [
            'page'        => 'forms',
            'status'      => $status,
            'bodyClass'   => 'is-form-state ' . $this->themeClass($form),
            'meta'        => ['title' => (string) $form['title'], 'description' => $this->description($form), 'robots' => 'noindex,nofollow', 'canonical' => '/forms/' . $form['slug']],
            'breadcrumbs' => [[(string) $form['title'], null]],
        ]);
    }

    /** Meta description of one form: its own SEO text, else its description (never the registry page's text, which describes the /forms list). */
    private function description(array $form): string
    {
        $cfg = (array) $form['cfg'];
        return trim((string) ($cfg['seo']['description'] ?? '')) !== '' ? (string) $cfg['seo']['description'] : (string) ($form['description'] ?? '');
    }

    /** A submission arrived while the form is no longer open (closed meanwhile, full, ...). */
    private function state(Request $request, array $form, string $state, array $avail): Response
    {
        $json = $this->jsonOut($request, ['ok' => false, 'error' => $state, 'message' => ''], 409);
        return $json ?? $this->notice($form, $state, $avail, 200);
    }

    /** Success: thank-you page (PRG) or the form's own redirect path. */
    private function done(Request $request, array $form, ?string $reference, bool $fake, ?string $job): Response
    {
        $cfg = (array) $form['cfg'];
        $redirect = ($cfg['success']['mode'] ?? 'message') === 'redirect' && ($cfg['success']['redirect_path'] ?? '') !== ''
            ? ltrim((string) $cfg['success']['redirect_path'], '/') : null;
        if ($redirect === null) {
            Session::flash('forms.done', ['slug' => (string) $form['slug'], 'ref' => $reference ?? '', 'job' => $job ?? '']);
        }
        $target = $redirect ?? 'forms/' . $form['slug'] . '/thanks';
        return $this->jsonOut($request, ['ok' => true, 'redirect' => url($target), 'reference' => $reference ?? ''], 200) ?? $this->redirect($target);
    }

    /** Body class of the form's accent colour (theme-orange | theme-navy | theme-teal | theme-gold). */
    private function themeClass(array $form): string
    {
        $accent = (string) ($form['cfg']['theme']['accent'] ?? 'orange');
        return 'theme-' . (in_array($accent, ['orange', 'navy', 'teal', 'gold'], true) ? $accent : 'orange');
    }

    /** The "print a blank copy" sheet: every question, nothing filled in, nothing indexable (GET /forms/{slug}?print=1). */
    private function printSheet(array $form): Response
    {
        return $this->render('web/forms/print', ['form' => $form], [
            'page'      => 'forms',
            'bodyClass' => 'is-form-print ' . $this->themeClass($form),
            'meta'      => ['title' => (string) $form['title'], 'robots' => 'noindex,nofollow', 'canonical' => '/forms/' . $form['slug']],
        ]);
    }

    /** JSON answer for AJAX clients, or null when the client wants a normal page. @param array<string,mixed> $data */
    private function jsonOut(Request $request, array $data, int $status): ?Response
    {
        return $request->expectsJson() ? $this->json($data, $status) : null;
    }
}
