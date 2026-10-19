<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Form;
use App\Services\Forms\FormDefinition;
use App\Services\Forms\FormService;
use App\Services\Forms\FormSettings;
use App\Services\Forms\FormTemplates;
use App\Services\Forms\FormValidator;

/**
 * JSON API of the form builder: /admin/api/forms. Contract (request / response shapes, error format): docs/FORMS.md section 16.
 *
 * Auth: session cookie + header X-CSRF-TOKEN (value of <meta name="csrf-token">) on every non-GET; send "Accept: application/json" so a lost session answers
 * 401 JSON instead of a redirect. Ability: forms.manage on every route (routes file); DELETE also needs forms.delete.
 *
 * Error format:  {"error":"<code>","message":"<human text>", ...extras}   codes: validation (422, "errors":{path:message}), not_ready (422, "errors" + "checklist"),
 * conflict (409, "current":{version,updated_at,updated_by} and "data": the newest form), not_found (404), forbidden (403), bad_request (400).
 * Framework-level failures (401, 403 CSRF, 404 unknown route, 405) keep the core shape {"error":"<Title>","status":N}.
 *
 * REFERENCE implementation by the architect (every endpoint works and is covered by bin/selftest-forms.php); the BUILDER agent owns this class and may
 * refine it but must keep URLs, verbs, JSON shapes and status codes: assets/js/admin-form-builder.js depends on them.
 */
final class FormBuilderApiController extends AdminController
{
    /** GET /admin/api/forms?status=&q=&page=&per_page= -> {data:[summary], meta:{page,per_page,total,pages,counts}} */
    public function index(Request $request): Response
    {
        $this->authorize('forms.manage');
        $p = FormService::adminList(['status' => $request->string('status'), 'q' => mb_substr($request->string('q'), 0, 100), 'with_archived' => $request->bool('with_archived')], $request->page(), $this->perPage());
        return $this->json([
            'data' => array_map(fn(array $f): array => $this->item($f, false), $p->items),
            'meta' => ['page' => $p->page, 'per_page' => $p->perPage, 'total' => $p->total, 'pages' => $p->pages, 'counts' => FormService::statusCounts()],
        ]);
    }

    /** GET /admin/api/forms/catalog -> field types, widths, operators, presets, file types, limits, templates, status labels. Static: cache it in the page. */
    public function catalog(Request $request): Response
    {
        $this->authorize('forms.manage');
        return $this->json(['data' => FormDefinition::catalog() + [
            'templates'      => FormTemplates::listing(),
            'status_labels'  => FormSettings::defaultStatusLabels('general'),
            'job_status_labels' => FormSettings::defaultStatusLabels('job'),
            'kinds'          => FormSettings::KINDS,
            'accents'        => FormSettings::ACCENTS,
            'spam_levels'    => FormSettings::SPAM_LEVELS,
            'layouts'        => FormSettings::LAYOUTS,
            'settings_defaults' => FormSettings::defaults('general'),
            'timezone'       => FormService::TZ,
        ]]);
    }

    /** GET /admin/api/forms/{id} -> {data: full form incl. definition, settings, checklist, embed codes, urls} */
    public function show(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $form = FormService::find($id);
        return $form === null ? $this->notFound() : $this->json(['data' => $this->item($form, true), 'checklist' => FormService::checklist($form)]);
    }

    /**
     * PUT /admin/api/forms/{id}   body: version* (the version the builder loaded), autosave (bool), and any of
     * title, description, slug, listed, opens_at, closes_at, max_submissions, definition (object), settings (object).
     * 200 {data, changed, warnings, checklist} | 409 conflict | 422 validation | 422 not_ready (the form is live and the change would break it).
     * Nothing is saved when there is an error. A save that changes nothing answers 200 with changed=false and the same version.
     */
    public function save(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $version = $request->post('version');
        if (!is_int($version) && !(is_string($version) && ctype_digit($version))) {
            return $this->error('bad_request', 'The version of the form is missing. Reload the page.', 400);
        }
        $autosave = $request->bool('autosave');
        $input = $request->post();
        unset($input['version'], $input['autosave'], $input['_method'], $input['_token']);
        $before = FormService::find($id);
        $res = FormService::save($id, $input, (int) $version, $this->userId(), [
            'autosave'  => $autosave,
            'abilities' => ['can_delete' => can('forms.delete'), 'can_submissions' => can('forms.submissions')],
        ]);
        if (!empty($res['not_found'])) {
            return $this->notFound();
        }
        if (!empty($res['forbidden'])) {
            return $this->json(['error' => 'forbidden', 'message' => (string) reset($res['errors']), 'errors' => (object) $res['errors']], 403);
        }
        if (!empty($res['conflict'])) {
            return $this->json(['error' => 'conflict', 'message' => 'Somebody else saved a newer version of this form.', 'current' => $res['current'], 'data' => $this->item($res['form'], true)], 409);
        }
        if (!empty($res['not_ready'])) {
            return $this->json(['error' => 'not_ready', 'message' => 'This form is live, so it has to stay complete. Fix the items below, or switch the form to draft first.', 'errors' => (object) $res['errors'], 'checklist' => $res['checklist']], 422);
        }
        if (!$res['ok']) {
            return $this->validation($res['errors'] ?? []);
        }
        if (!$autosave && !empty($res['changed'])) {
            $this->audit('form.update', 'form', $id, 'Edited form "' . $res['form']['title'] . '"', ['version' => (int) $res['form']['version']]);
        }
        if (!empty($res['changed']) && $before !== null) {
            // sensitive settings get their own audit entries (ids and counts only: never the addresses)
            $o = (array) $before['cfg'];
            $n = (array) $res['form']['cfg'];
            if ((int) ($o['retention_days'] ?? 0) !== (int) ($n['retention_days'] ?? 0)) {
                $this->audit('form.retention', 'form', $id, 'Changed the retention period', ['from_days' => (int) ($o['retention_days'] ?? 0), 'to_days' => (int) ($n['retention_days'] ?? 0)]);
            }
            $oldTo = array_values((array) ($o['notify']['emails'] ?? []));
            $newTo = array_values((array) ($n['notify']['emails'] ?? []));
            sort($oldTo);
            sort($newTo);
            if ($oldTo !== $newTo || !empty($o['notify']['include_answers']) !== !empty($n['notify']['include_answers'])) {
                $this->audit('form.notify', 'form', $id, 'Changed who is e-mailed about new answers', ['recipients' => count($newTo), 'include_answers' => !empty($n['notify']['include_answers'])]);
            }
        }
        return $this->json(['data' => $this->item($res['form'], true), 'changed' => (bool) ($res['changed'] ?? false), 'warnings' => $res['warnings'] ?? [], 'checklist' => $res['checklist'] ?? []]);
    }

    /**
     * POST /admin/api/forms/validate   body: definition (object|JSON), settings (object|JSON, optional), slug, opens_at, closes_at, max_submissions, id (optional, for the slug check)
     * -> {definition, settings, errors:{path:msg}, warnings, checklist}. Nothing is written. The builder calls it to show the "ready to publish?" checklist.
     */
    public function validateDefinition(Request $request): Response
    {
        $this->authorize('forms.manage');
        $errors = [];
        $warnings = [];
        $def = null;
        $cfg = null;
        $n = FormDefinition::normalise($request->post('definition'));
        if ($n['ok']) {
            $def = $n['definition'];
            $warnings = $n['warnings'];
        } else {
            $errors += $n['errors'];
        }
        if ($request->post('settings') !== null) {
            $s = FormSettings::normalise($request->post('settings'));
            $cfg = $s['settings'];
            foreach ($s['errors'] as $path => $msg) {
                $errors['settings.' . $path] = $msg;
            }
        }
        $meta = [];
        foreach (['slug', 'opens_at', 'closes_at'] as $k) {
            if ($request->has($k)) {
                $meta[$k] = $k === 'slug' ? strtolower(trim((string) $request->post($k))) : FormService::parseLocal($request->post($k));
                if ($k !== 'slug' && $meta[$k] === false) {
                    $errors[$k] = 'Enter a valid date and time.';
                    $meta[$k] = null;
                }
            }
        }
        if (isset($meta['slug'])) {
            $p = FormService::slugProblem($meta['slug'], $request->int('id') ?: null);
            if ($p !== null) {
                $errors['slug'] = $p;
            }
        }
        $maxRaw = $request->post('max_submissions');
        $maxOk = $maxRaw === null || $maxRaw === '' || $maxRaw === false
            || ((is_int($maxRaw) || (is_string($maxRaw) && ctype_digit(trim($maxRaw)))) && (int) $maxRaw >= 1 && (int) $maxRaw <= 1000000);      // the same rule as FormService::save
        if ($request->has('max_submissions') && !$maxOk) {
            $errors['max_submissions'] = 'Enter a whole number of 1 or more, or leave it empty for no limit.';
        }
        $meta['max_submissions'] = $request->has('max_submissions') && $maxOk && $maxRaw !== '' && $maxRaw !== null && $maxRaw !== false ? (int) $maxRaw : null;
        $checklist = $def !== null ? FormDefinition::publishChecklist($def, $cfg ?? [], $meta) : [];
        return $this->json(['definition' => $def, 'settings' => $cfg, 'errors' => (object) $errors, 'warnings' => $warnings, 'checklist' => $checklist]);
    }

    /**
     * POST /admin/api/forms/preview   body: definition, settings, title, description, id (optional)
     * -> {html: the <form> markup, document: a complete HTML page (public CSS + forms.js) for an iframe srcdoc, checklist}. Nothing is written or sent.
     * The markup is the PUBLIC renderer (views web/forms/_form) in mode "preview": the submit button is disabled and nothing posts anywhere.
     */
    public function preview(Request $request): Response
    {
        $this->authorize('forms.manage');
        $n = FormDefinition::normalise($request->post('definition'));
        if (!$n['ok']) {
            return $this->validation($n['errors']);
        }
        $s = FormSettings::normalise($request->post('settings') ?? []);
        $form = [
            'id' => $request->int('id'), 'slug' => 'preview', 'version' => 1,
            'title' => FormDefinition::plain($request->post('title') ?? '', 190),
            'description' => FormDefinition::plain($request->post('description') ?? '', 600, true),
            'status' => 'draft', 'opens_at' => null, 'closes_at' => null, 'max_submissions' => null, 'submissions_count' => 0,
            'def' => $n['definition'], 'cfg' => $s['settings'],
        ];
        $html = partial('web/forms/_form', ['form' => $form, 'input' => [], 'errors' => [], 'context' => null, 'mode' => 'preview']);
        $cfg = $s['settings'];
        $document = '<!doctype html><html lang="en" class="js"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<base target="_blank"><link rel="stylesheet" href="' . e(asset('css/main.min.css')) . '"></head>'
            . '<body class="is-form theme-' . e((string) ($cfg['theme']['accent'] ?? 'orange')) . ' is-form-preview"><main class="lf-section"><div class="lf-card">'
            . '<h1 class="lf-embed__title">' . e($form['title']) . '</h1>'
            . (trim((string) ($cfg['intro'] ?? '')) !== '' ? '<div class="lf-intro prose">' . rich((string) $cfg['intro']) . '</div>' : '')
            . $html . '</div></main><script src="' . e(asset('js/forms.js')) . '" defer></script></body></html>';
        return $this->json(['html' => $html, 'document' => $document, 'checklist' => FormDefinition::publishChecklist($n['definition'], $cfg, [])]);
    }

    /** POST /admin/api/forms/{id}/status   body: action (publish|unpublish|close|reopen|archive|restore), version (optional) -> {data} | 409 | 422 not_ready */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $action = $request->string('action');
        $v = $request->post('version');
        $res = FormService::transition($id, $action, (is_int($v) || (is_string($v) && ctype_digit($v))) ? (int) $v : null, $this->userId());
        if (!empty($res['not_found'])) {
            return $this->notFound();
        }
        if (!empty($res['conflict'])) {
            return $this->json(['error' => 'conflict', 'message' => 'Somebody else changed this form. Reload to see the newest version.', 'data' => isset($res['form']) ? $this->item($res['form'], true) : null], 409);
        }
        if (!$res['ok']) {
            $blocked = !empty($res['errors']);
            return $this->json(['error' => $blocked ? 'not_ready' : 'bad_request', 'message' => (string) ($res['error'] ?? 'The change could not be made.'), 'errors' => (object) ($res['errors'] ?? []), 'checklist' => $res['checklist'] ?? []], $blocked ? 422 : 400);
        }
        $this->audit('form.' . $action, 'form', $id, ucfirst($action) . ' form "' . $res['form']['title'] . '"');
        return $this->json(['data' => $this->item($res['form'], true), 'checklist' => FormService::checklist($res['form'])]);
    }

    /**
     * POST /admin/api/forms/{id}/test-submit   body (JSON or multipart): the answers as a visitor's browser would post them (f_<fieldId>, f_<fieldId>[], f_<fieldId>_other,
     * form_consent), files as f_<fieldId>; optionally definition + settings to test UNSAVED changes.
     * -> {ok, errors:{fieldId:msg}, answers:[snapshot entries], visible:{fieldId:bool}, pages_visible, error_pages}. NOTHING is stored and no e-mail is sent.
     */
    public function testSubmit(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $form = FormService::find($id);
        if ($form === null) {
            return $this->notFound();
        }
        if ($request->post('definition') !== null) {
            $n = FormDefinition::normalise($request->post('definition'));
            if (!$n['ok']) {
                return $this->validation($n['errors']);
            }
            $form['def'] = $n['definition'];
        }
        if ($request->post('settings') !== null) {
            $form['cfg'] = FormSettings::normalise($request->post('settings'), (string) $form['cfg']['kind'])['settings'];
        }
        $files = FormValidator::collectFiles((array) $form['def'], $request);
        $res = FormService::testSubmit($form, $request->all(), $files);
        return $this->json($res + ['stored' => false]);
    }

    /** POST /admin/api/forms/{id}/duplicate -> 201 {data} (a new draft) */
    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $res = FormService::duplicate($id, $this->userId());
        if (!$res['ok']) {
            return $this->notFound();
        }
        $this->audit('form.duplicate', 'form', (int) $res['form']['id'], 'Duplicated form "' . $res['form']['title'] . '"', ['from' => $id]);
        return $this->json(['data' => $this->item($res['form'], true)], 201);
    }

    /** DELETE /admin/api/forms/{id} -> {ok:true, submissions, files}. Needs forms.delete as well. */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('forms.manage', 'forms.delete');
        $row = Form::find($id);
        if ($row === null) {
            return $this->notFound();
        }
        $res = FormService::delete($id);
        $this->audit('form.delete', 'form', $id, 'Deleted form "' . $row['title'] . '"', ['submissions' => $res['submissions'], 'files' => $res['files']]);
        return $this->json(['ok' => true, 'submissions' => $res['submissions'], 'files' => $res['files']]);
    }

    // ------------------------------------------------------------------------------------------------------------------

    /**
     * The JSON shape of a form. $full adds definition, settings, checklist-ready fields, embed codes and permissions.
     * Dates are in the format of <input type="datetime-local"> ("2026-10-19T14:30", EAT) so the builder can put them straight into the inputs.
     * @return array<string,mixed>
     */
    private function item(array $f, bool $full): array
    {
        // minutes only when the seconds are :00 (what the datetime-local inputs produce); otherwise the seconds too, so a round trip never moves the time
        $local = static function (?string $d): ?string {
            if ($d === null || $d === '') {
                return null;
            }
            $t = str_replace(' ', 'T', substr($d, 0, 19));
            return strlen($t) === 19 && str_ends_with($t, ':00') ? substr($t, 0, 16) : $t;
        };
        $out = [
            'id'                => (int) $f['id'],
            'slug'              => (string) $f['slug'],
            'title'             => (string) $f['title'],
            'status'            => (string) $f['status'],
            'version'           => (int) $f['version'],
            'listed'            => (bool) $f['listed'],
            'submissions_count' => (int) $f['submissions_count'],
            'unread'            => isset($f['unread']) ? (int) $f['unread'] : \App\Services\Forms\SubmissionService::newCount((int) $f['id']),
            'opens_at'          => $local($f['opens_at'] ?? null),
            'closes_at'         => $local($f['closes_at'] ?? null),
            'max_submissions'   => $f['max_submissions'],
            'updated_at'        => (string) $f['updated_at'],
            'availability'      => FormService::availability($f),
            'urls'              => [
                'public'      => abs_url('forms/' . $f['slug']),
                'edit'        => url('admin/forms/' . (int) $f['id'] . '/edit'),
                'submissions' => url('admin/forms/' . (int) $f['id'] . '/submissions'),
            ],
        ];
        if ($full) {
            $out += [
                'description'   => (string) ($f['description'] ?? ''),
                'template_key'  => $f['template_key'] ?? null,
                'published_at'  => $f['published_at'] ?? null,
                'definition'    => $f['def'],
                'settings'      => $f['cfg'],
                'embed'         => FormService::embedCode($f),
                'can'           => ['delete' => can('forms.delete'), 'submissions' => can('forms.submissions'), 'export' => can('forms.export')],
                'jobs'          => $this->jobsUsing((int) $f['id']),
            ];
        }
        return $out;
    }

    /** Vacancies that use this form as their application form (titles only): the builder warns before the form is archived or deleted. @return list<string> */
    private function jobsUsing(int $formId): array
    {
        try {
            return array_map(static fn(array $r): string => (string) $r['title'], Database::all('SELECT title FROM jobs WHERE form_id = ? ORDER BY title ASC LIMIT 20', [$formId]));
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param array<string,string> $errors */
    private function validation(array $errors): Response
    {
        return $this->json(['error' => 'validation', 'message' => $errors !== [] ? (string) reset($errors) : 'Please check the highlighted fields.', 'errors' => (object) $errors], 422);
    }

    private function notFound(): Response
    {
        return $this->error('not_found', 'That form no longer exists.', 404);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return $this->json(['error' => $code, 'message' => $message], $status);
    }
}
