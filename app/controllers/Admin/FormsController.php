<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Services\Forms\FormRetention;
use App\Services\Forms\FormService;
use App\Services\Forms\FormTemplates;

/**
 * Admin > Forms: list, template picker, create, duplicate, status change, delete, and the page that hosts the builder. Contract: docs/FORMS.md section 16.
 * Ability: forms.manage on every route (routes file); deleting also needs forms.delete. The builder itself talks to Admin\FormBuilderApiController.
 *
 * Owner: BUILDER agent. POST field names of the screens: store = source (blank | <template key> | duplicate [+ copy_id] | duplicate:<id>), title;
 * status = action (publish | unpublish | close | reopen | archive | restore). Audit entries carry ids and titles only, never answers.
 */
final class FormsController extends AdminController
{
    private const TABS = ['all', 'draft', 'published', 'closed', 'archived'];

    public function index(Request $request): Response
    {
        $this->authorize('forms.manage');
        FormRetention::lazy();
        $status = $request->string('status', 'all');
        $status = in_array($status, self::TABS, true) ? $status : 'all';
        $q = mb_substr($request->string('q'), 0, 100);
        $pagination = FormService::adminList(['status' => $status === 'all' ? '' : $status, 'q' => $q], $request->page(), $this->perPage());
        return $this->render('admin/forms/index', [
            'pagination' => $pagination,
            'counts'     => FormService::statusCounts(),
            'status'     => $status,
            'q'          => $q,
            'tabs'       => self::TABS,
            'canDelete'  => can('forms.delete'),
            'canAnswers' => can('forms.submissions'),
            'jobUse'     => $this->jobUse(array_map(static fn(array $f): int => (int) $f['id'], $pagination->items)),
        ], ['title' => 'Forms', 'nav' => 'forms', 'breadcrumbs' => [['Forms', null]]]);
    }

    /** The template picker ("New form"). */
    public function templates(Request $request): Response
    {
        $this->authorize('forms.manage');
        return $this->render('admin/forms/templates', [
            'templates' => FormTemplates::listing(),
            'existing'  => FormService::formChoices(),
        ], ['title' => 'New form', 'nav' => 'form_create', 'breadcrumbs' => [['Forms', url('admin/forms')], ['New form', null]]]);
    }

    /** POST: source = blank | <template key> | duplicate (+ copy_id) | duplicate:<id>; title (optional: the template's name is the default). */
    public function store(Request $request): Response
    {
        $this->authorize('forms.manage');
        $source = $request->string('source', 'blank');
        if ($source === 'duplicate') {
            $source = 'duplicate:' . $request->int('copy_id');
        }
        $res = FormService::create(['source' => $source, 'title' => $request->string('title')], $this->userId());
        if (!$res['ok']) {
            $this->fail($res['errors'] ?? ['title' => 'The form could not be created.'], 'admin/forms/new');
        }
        $form = $res['form'];
        $this->audit('form.create', 'form', (int) $form['id'], 'Created form "' . $form['title'] . '"', ['source' => str_starts_with($source, 'duplicate:') ? 'duplicate' : $source]);
        Flash::success('The form was created as a draft. Add or change the questions below, then publish it when you are ready.');
        return $this->redirect('admin/forms/' . (int) $form['id'] . '/edit');
    }

    /** The builder page. The builder script loads everything from GET /admin/api/forms/{id}. */
    public function edit(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $form = FormService::find($id) ?? throw new HttpException(404, 'Not found');
        return $this->render('admin/forms/builder', [
            'form' => $form,
        ], ['title' => 'Edit form', 'nav' => 'forms', 'breadcrumbs' => [['Forms', url('admin/forms')], [(string) $form['title'], null]]]);
    }

    public function duplicate(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $res = FormService::duplicate($id, $this->userId());
        if (!$res['ok']) {
            Flash::error('That form no longer exists.');
            return $this->redirect('admin/forms');
        }
        $this->audit('form.duplicate', 'form', (int) $res['form']['id'], 'Duplicated form "' . $res['form']['title'] . '"', ['from' => $id]);
        Flash::success('A copy was created as a draft. It has no answers.');
        return $this->redirect('admin/forms/' . (int) $res['form']['id'] . '/edit');
    }

    /** POST action = publish | unpublish | close | reopen | archive | restore. */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('forms.manage');
        $action = $request->string('action');
        $res = FormService::transition($id, $action, null, $this->userId());
        if (!empty($res['not_found'])) {
            throw new HttpException(404, 'Not found');
        }
        if (!$res['ok']) {
            $msg = (string) ($res['error'] ?? 'The status could not be changed.');
            if (!empty($res['errors'])) {
                $msg .= ' ' . implode(' ', array_slice(array_values($res['errors']), 0, 3)) . ' Open the form to fix this.';
            }
            Flash::error($msg);
            return $this->back('admin/forms');
        }
        $this->audit('form.' . $action, 'form', $id, ucfirst($action) . ' form "' . $res['form']['title'] . '"');
        Flash::success(match ($action) {
            'publish', 'reopen' => 'The form is live: visitors can fill it in.',
            'unpublish'         => 'The form is back to draft and no longer public.',
            'close'             => 'The form is closed: visitors see the "closed" notice.',
            'archive'           => 'The form was archived. It is not public any more and its answers are kept.',
            default             => 'The form was restored as a draft.',
        });
        return $this->back('admin/forms');
    }

    /** Delete a form with all its submissions and files. Needs forms.delete. */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('forms.manage', 'forms.delete');
        $form = FormService::find($id) ?? throw new HttpException(404, 'Not found');
        $res = FormService::delete($id);
        $this->audit('form.delete', 'form', $id, 'Deleted form "' . $form['title'] . '"', ['submissions' => $res['submissions'], 'files' => $res['files']]);
        Flash::success('The form' . ($res['submissions'] > 0 ? ' and its ' . $res['submissions'] . ' submission(s)' : '') . ' was deleted.');
        return $this->redirect('admin/forms');
    }

    /**
     * How many vacancies use each form as their application form (form id => count): the list warns before a form that is in use is deleted or archived.
     * @param list<int> $ids
     * @return array<int,int>
     */
    private function jobUse(array $ids): array
    {
        $ids = array_values(array_filter($ids, static fn(int $i): bool => $i > 0));
        if ($ids === []) {
            return [];
        }
        try {
            $rows = Database::all('SELECT form_id, COUNT(*) AS n FROM jobs WHERE form_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') GROUP BY form_id', $ids);
        } catch (\Throwable) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['form_id']] = (int) $r['n'];
        }
        return $out;
    }
}
