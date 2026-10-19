<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\FormSubmission;
use App\Services\Forms\FormExport;
use App\Services\Forms\FormFileStore;
use App\Services\Forms\FormRetention;
use App\Services\Forms\FormService;
use App\Services\Forms\FormSettings;
use App\Services\Forms\FormStats;
use App\Services\Forms\SubmissionService;

/**
 * Admin > Forms > answers (the inbox of one form): list, search, filters, bulk actions, detail, status workflow, notes, files, print, CSV export, statistics.
 * Contract: docs/FORMS.md sections 7 and 16. PERSONAL DATA: every route needs forms.submissions (routes file); export needs forms.export; deleting needs
 * forms.delete. The audit log receives ids and counts ONLY, never answers, names or addresses.
 *
 * Screens (views in app/views/admin/forms/submissions/): overview (every form, for roles that only read answers), index (inbox), show (one answer),
 * print (standalone page), stats (summary tab). Forms on those screens keep the inbox position (tab, search, dates, order, page) in ONE hidden field
 * `back` holding a query string; it is parsed and whitelisted again on every request (never used as a URL).
 */
final class FormSubmissionsController extends AdminController
{
    /** Inbox tabs: key => statuses shown. `all` excludes archived and spam. */
    public const TABS = ['all', 'new', 'read', 'starred', 'shortlisted', 'accepted', 'rejected', 'archived', 'spam'];

    /** Statuses a staff member can set from the screens (the order of the buttons). */
    public const WORKFLOW = ['starred', 'shortlisted', 'accepted', 'rejected', 'read', 'new', 'archived', 'spam'];

    /** Days offered by the summary chart. */
    public const RANGES = [7, 30, 90];

    /** All forms with their counts, for people who only have forms.submissions. */
    public function overview(Request $request): Response
    {
        $this->authorize('forms.submissions');
        FormRetention::lazy();
        $rows = Database::all(
            "SELECT f.id, f.title, f.slug, f.status, f.submissions_count,
                    (SELECT COUNT(*) FROM form_submissions s WHERE s.form_id = f.id AND s.status = 'new') AS unread,
                    (SELECT MAX(s.created_at) FROM form_submissions s WHERE s.form_id = f.id) AS last_at
             FROM forms f ORDER BY unread DESC, last_at DESC, f.updated_at DESC LIMIT 300"
        );
        return $this->render('admin/forms/submissions/overview', ['forms' => $rows, 'canManage' => can('forms.manage')], ['title' => 'Form answers', 'nav' => 'form_answers', 'breadcrumbs' => [['Form answers', null]]]);
    }

    public function index(Request $request, int $id): Response
    {
        $this->authorize('forms.submissions');
        $form = $this->form($id);
        FormRetention::lazy();
        $f = $this->filtersFrom($request->query());
        $sql = $this->sqlFilters($f);
        $pagination = SubmissionService::listFor($id, $sql, $request->page(), $this->perPage());
        $counts = SubmissionService::statusCounts($id);
        $counts['all'] -= $counts['archived'] + $counts['spam'];             // the "All" tab hides archived and spam
        return $this->render('admin/forms/submissions/index', [
            'form'       => $form,
            'pagination' => $pagination,
            'f'          => $f,
            'back'       => $this->backQuery($f, $pagination->page),
            'counts'     => $counts,
            'columns'    => SubmissionService::listColumns($form),
            'labels'     => FormSettings::statusLabels((array) $form['cfg']),
            'tabs'       => self::TABS,
            'contexts'   => $this->contexts($id),
            'retention'  => (int) ($form['cfg']['retention_days'] ?? 0),
            'canExport'  => can('forms.export'),
            'canDelete'  => can('forms.delete'),
            'canManage'  => can('forms.manage'),
            'perPage'    => $pagination->perPage,
        ], ['title' => (string) $form['title'], 'nav' => $this->nav(), 'breadcrumbs' => $this->crumbs($form, 'Answers')]);
    }

    public function show(Request $request, int $id, int|string $sid): Response
    {
        $this->authorize('forms.submissions');
        $form = $this->form($id);
        $sub = $this->submission($id, (int) $sid);
        if ($sub['status'] === 'new') {
            SubmissionService::markRead($id, (int) $sid);
            $sub['status'] = 'read';
        }
        $f = $this->filtersFrom($request->query());
        $sql = $this->sqlFilters($f);
        $retention = (int) ($form['cfg']['retention_days'] ?? 0);
        return $this->render('admin/forms/submissions/show', [
            'form'       => $form,
            'sub'        => $sub,
            'ref'        => SubmissionService::reference($id, (int) $sid),
            'labels'     => FormSettings::statusLabels((array) $form['cfg']),
            'workflow'   => self::WORKFLOW,
            'neighbours' => SubmissionService::neighbours($id, (int) $sid, $sql),
            'position'   => $this->position($id, $sub, $sql),
            'f'          => $f,
            'back'       => $this->backQuery($f, max(1, $request->page())),
            'same'       => $this->sameSubmitter($id, $sub),
            'history'    => can('audit.view') ? $this->history((int) $sid) : [],
            'deleteOn'   => $retention > 0 ? date('Y-m-d', strtotime((string) $sub['created_at'] . ' +' . $retention . ' days') ?: time()) : null,
            'canDelete'  => can('forms.delete'),
            'canManage'  => can('forms.manage'),
        ], ['title' => SubmissionService::reference($id, (int) $sid), 'nav' => $this->nav(), 'breadcrumbs' => $this->crumbs($form, SubmissionService::reference($id, (int) $sid), true)]);
    }

    /** Standalone printable page (no admin chrome). ?notes=1 adds the internal notes. */
    public function print(Request $request, int $id, int|string $sid): Response
    {
        $this->authorize('forms.submissions');
        $form = $this->form($id);
        $sub = $this->submission($id, (int) $sid);
        $this->audit('form.submission.print', 'form_submission', (int) $sid, 'Opened the print view of a submission', ['form' => $id]);
        $f = $this->filtersFrom($request->query());
        return $this->render('admin/forms/submissions/print', [
            'form'      => $form,
            'sub'       => $sub,
            'ref'       => SubmissionService::reference($id, (int) $sid),
            'labels'    => FormSettings::statusLabels((array) $form['cfg']),
            'withNotes' => $request->string('notes') === '1',
            'backUrl'   => url('admin/forms/' . $id . '/submissions/' . (int) $sid, $this->keepQuery($f)),
        ], ['title' => SubmissionService::reference($id, (int) $sid), 'layout' => null]);
    }

    /** POST status = new | read | starred | shortlisted | rejected | accepted | archived | spam */
    public function status(Request $request, int $id, int|string $sid): Response
    {
        $this->authorize('forms.submissions');
        $this->form($id);
        $this->submission($id, (int) $sid);
        $to = 'admin/forms/' . $id . '/submissions/' . (int) $sid;
        $keep = $this->keepFromBack($request);
        $status = $request->string('status');
        if (!in_array($status, FormSubmission::STATUSES, true)) {
            Flash::error('Choose a valid status.');
            return $this->redirect($to . $this->qs($keep));
        }
        if (SubmissionService::setStatus($id, (int) $sid, $status)) {
            $this->audit('form.submission.status', 'form_submission', (int) $sid, 'Changed the status of a submission', ['form' => $id, 'status' => $status]);
        }
        Flash::success('The status was updated.');
        return $this->redirect($to . $this->qs($keep));
    }

    public function notes(Request $request, int $id, int|string $sid): Response
    {
        $this->authorize('forms.submissions');
        $this->form($id);
        $this->submission($id, (int) $sid);
        SubmissionService::saveNotes($id, (int) $sid, $request->string('notes'));
        $this->audit('form.submission.notes', 'form_submission', (int) $sid, 'Saved internal notes on a submission', ['form' => $id]);
        Flash::success('The notes were saved.');
        return $this->redirect('admin/forms/' . $id . '/submissions/' . (int) $sid . $this->qs($this->keepFromBack($request)));
    }

    public function destroy(Request $request, int $id, int|string $sid): Response
    {
        $this->authorize('forms.submissions', 'forms.delete');
        $this->form($id);
        $this->submission($id, (int) $sid);
        SubmissionService::delete($id, (int) $sid);
        $this->audit('form.submission.delete', 'form_submission', (int) $sid, 'Deleted a submission', ['form' => $id]);
        Flash::success('The submission was deleted.');
        return $this->redirect('admin/forms/' . $id . '/submissions' . $this->qs($this->keepFromBack($request)));
    }

    /** POST action = read | unread | star | archive | spam | accept | reject | shortlist | status:<key> | delete (forms.delete); ids[] */
    public function bulk(Request $request, int $id): Response
    {
        $this->authorize('forms.submissions');
        $this->form($id);
        $ids = array_values(array_filter(array_map('intval', (array) $request->post('ids', [])), static fn(int $i): bool => $i > 0));
        $back = 'admin/forms/' . $id . '/submissions' . $this->qs($this->keepFromBack($request));
        if ($ids === []) {
            Flash::error('Select at least one submission first.');
            return $this->redirect($back);
        }
        $action = $request->string('action');
        $map = ['read' => 'read', 'unread' => 'new', 'star' => 'starred', 'archive' => 'archived', 'spam' => 'spam', 'shortlist' => 'shortlisted', 'accept' => 'accepted', 'reject' => 'rejected'];
        if (str_starts_with($action, 'status:')) {
            $map[$action] = substr($action, 7);
        }
        if ($action === 'delete') {
            $this->authorize('forms.delete');
            $n = SubmissionService::deleteMany($id, $ids);
            $this->audit('form.submission.bulk', 'form', $id, 'Deleted submissions', ['action' => 'delete', 'count' => $n]);
            Flash::success($n === 1 ? '1 submission was deleted.' : $n . ' submissions were deleted.');
            return $this->redirect($back);
        }
        if (!isset($map[$action]) || !in_array($map[$action], FormSubmission::STATUSES, true)) {
            Flash::error('Choose an action.');
            return $this->redirect($back);
        }
        $n = SubmissionService::bulkStatus($id, $ids, $map[$action]);
        $this->audit('form.submission.bulk', 'form', $id, 'Changed the status of submissions', ['action' => $map[$action], 'count' => $n]);
        Flash::success($n === 1 ? '1 submission was updated.' : $n . ' submissions were updated.');
        return $this->redirect($back);
    }

    /** Authenticated download of one uploaded file. Always an attachment; the three ids must belong together. */
    public function file(Request $request, int $id, int|string $sid, int|string $fid): Response
    {
        $this->authorize('forms.submissions');
        $this->form($id);
        $row = SubmissionService::file($id, (int) $sid, (int) $fid) ?? throw new HttpException(404, 'Not found');
        $this->audit('form.submission.file', 'form_submission', (int) $sid, 'Downloaded an uploaded file', ['form' => $id, 'file' => (int) $fid]);
        return FormFileStore::stream($row);
    }

    public function export(Request $request, int $id): Response
    {
        $this->authorize('forms.submissions', 'forms.export');
        $form = $this->form($id);
        $f = $this->filtersFrom($request->query());
        $sql = $this->sqlFilters($f);
        if ($f['status'] === 'all') {
            unset($sql['statuses']);                       // an export holds EVERY answer (archived and spam too) unless a status tab was chosen
        }
        [$where, $params] = SubmissionService::where($id, $sql);
        $rows = (int) Database::value("SELECT COUNT(*) FROM form_submissions s WHERE {$where}", $params, 0);
        $this->audit('form.export', 'form', $id, 'Exported form submissions to CSV', ['rows' => $rows]);
        return FormExport::response($form, $sql);
    }

    public function stats(Request $request, int $id): Response
    {
        $this->authorize('forms.submissions');
        $form = $this->form($id);
        $days = $request->int('days', 30);
        $days = in_array($days, self::RANGES, true) ? $days : 30;
        return $this->render('admin/forms/submissions/stats', [
            'form'     => $form,
            'days'     => $days,
            'ranges'   => self::RANGES,
            'overview' => FormStats::overview($form),
            'perDay'   => FormStats::perDay($id, $days),
            'tallies'  => FormStats::choiceTallies($form),
            'contexts' => $this->contextBreakdown($id),
            'labels'   => FormSettings::statusLabels((array) $form['cfg']),
            'canExport' => can('forms.export'),
            'canManage' => can('forms.manage'),
        ], ['title' => (string) $form['title'], 'nav' => $this->nav(), 'breadcrumbs' => $this->crumbs($form, 'Summary', true)]);
    }

    /** GET /admin/api/forms/{id}/stats?days=30 -> {overview, per_day, tallies}  (counts only) */
    public function statsJson(Request $request, int $id): Response
    {
        $this->authorize('forms.submissions');
        $form = $this->form($id);
        return $this->json(['overview' => FormStats::overview($form), 'per_day' => FormStats::perDay($id, max(7, min(180, $request->int('days', 30)))), 'tallies' => FormStats::choiceTallies($form)]);
    }

    // ------------------------------------------------------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function form(int $id): array
    {
        return FormService::find($id) ?? throw new HttpException(404, 'Not found');
    }

    /** @return array<string,mixed> hydrated submission of THIS form (a submission of another form is a 404) */
    private function submission(int $formId, int $sid): array
    {
        $sub = SubmissionService::find($sid);
        if ($sub === null || (int) $sub['form_id'] !== $formId) {
            throw new HttpException(404, 'Not found');
        }
        return $sub;
    }

    private function nav(): string
    {
        return can('forms.manage') ? 'forms' : 'form_answers';
    }

    /** @return list<array{0:string,1:?string}> */
    private function crumbs(array $form, string $last, bool $withList = false): array
    {
        $c = [[can('forms.manage') ? 'Forms' : 'Form answers', url(can('forms.manage') ? 'admin/forms' : 'admin/form-answers')]];
        if (!$withList) {
            $c[] = [(string) $form['title'], null];
            return $c;
        }
        $c[] = [(string) $form['title'], url('admin/forms/' . (int) $form['id'] . '/submissions')];
        $c[] = [$last, null];
        return $c;
    }

    // ---- filters --------------------------------------------------------------------------------------------------

    /**
     * Inbox filters from a query array (a request's query string, or a parsed `back` field): status (tab), q, from, to, sort, dir, ref (context).
     * Everything is whitelisted / trimmed; nothing here reaches SQL except through SubmissionService::where() (prepared statements).
     * @param array<string,mixed> $src
     * @return array<string,string>
     */
    private function filtersFrom(array $src): array
    {
        $str = static fn(string $k): string => isset($src[$k]) && is_string($src[$k]) ? $src[$k] : '';
        $date = static function (string $v): string {
            $d = $v !== '' ? \DateTimeImmutable::createFromFormat('!Y-m-d', $v) : false;
            return $d !== false && $d->format('Y-m-d') === $v ? $v : '';
        };
        $status = $str('status');
        $sort = $str('sort');
        return [
            'status' => in_array($status, self::TABS, true) ? $status : 'all',
            'q'      => mb_substr(trim($str('q')), 0, 200),
            'from'   => $date($str('from')),
            'to'     => $date($str('to')),
            'sort'   => in_array($sort, ['created_at', 'status', 'submitter_email'], true) ? $sort : 'created_at',
            'dir'    => $str('dir') === 'asc' ? 'asc' : 'desc',
            'ref'    => mb_substr(trim($str('ref')), 0, 190),
            'per'    => in_array($str('per_page'), ['10', '50', '100'], true) ? $str('per_page') : '',     // '' = the default (20)
        ];
    }

    /** Translate the screen filters to SubmissionService::where() filters (the "all" tab hides archived and spam). @param array<string,string> $f @return array<string,mixed> */
    private function sqlFilters(array $f): array
    {
        $out = ['q' => $f['q'], 'from' => $f['from'], 'to' => $f['to'], 'sort' => $f['sort'], 'dir' => $f['dir'], 'context_ref' => $f['ref']];
        if ($f['status'] === 'all') {
            $out['statuses'] = array_values(array_diff(FormSubmission::STATUSES, ['archived', 'spam']));
        } else {
            $out['status'] = $f['status'];
        }
        return $out;
    }

    /** Non-default filters as query parameters (short URLs). @param array<string,string> $f @return array<string,string> */
    private function keepQuery(array $f, int $page = 1): array
    {
        $q = [];
        foreach (['q', 'from', 'to', 'ref'] as $k) {
            if ($f[$k] !== '') {
                $q[$k] = $f[$k];
            }
        }
        if ($f['per'] !== '') {
            $q['per_page'] = $f['per'];
        }
        if ($f['status'] !== 'all') {
            $q['status'] = $f['status'];
        }
        if ($f['sort'] !== 'created_at') {
            $q['sort'] = $f['sort'];
        }
        if ($f['dir'] !== 'desc') {
            $q['dir'] = $f['dir'];
        }
        if ($page > 1) {
            $q['page'] = (string) $page;
        }
        return $q;
    }

    /** The value of the hidden `back` field of the forms on the screens. */
    private function backQuery(array $f, int $page = 1): string
    {
        return http_build_query($this->keepQuery($f, $page), '', '&', PHP_QUERY_RFC3986);
    }

    /** Query parameters from a posted `back` field, whitelisted again. @return array<string,string> */
    private function keepFromBack(Request $request): array
    {
        $raw = mb_substr($request->string('back'), 0, 800);
        parse_str($raw, $parsed);
        $f = $this->filtersFrom(is_array($parsed) ? $parsed : []);
        $page = isset($parsed['page']) && is_string($parsed['page']) && ctype_digit($parsed['page']) ? min(9999, (int) $parsed['page']) : 1;
        return $this->keepQuery($f, $page);
    }

    /** @param array<string,string> $q */
    private function qs(array $q): string
    {
        return $q === [] ? '' : '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986);
    }

    // ---- data helpers (counts and positions only; no answers) -------------------------------------------------------

    /** The jobs / events this inbox received answers for (forms opened with ?job=...). @return list<array{ref:string,label:string,n:int}> */
    private function contexts(int $formId): array
    {
        $rows = Database::all(
            "SELECT context_ref, MAX(context_label) AS label, COUNT(*) AS n FROM form_submissions WHERE form_id = ? AND context_ref IS NOT NULL AND context_ref <> '' GROUP BY context_ref ORDER BY n DESC, label ASC LIMIT 100",
            [$formId]
        );
        return array_map(static fn(array $r): array => ['ref' => (string) $r['context_ref'], 'label' => (string) ($r['label'] ?: $r['context_ref']), 'n' => (int) $r['n']], $rows);
    }

    /** Summary tab: answers per job, without spam. @return list<array{ref:string,label:string,n:int}> */
    private function contextBreakdown(int $formId): array
    {
        $rows = Database::all(
            "SELECT context_ref, MAX(context_label) AS label, COUNT(*) AS n FROM form_submissions WHERE form_id = ? AND context_ref IS NOT NULL AND context_ref <> '' AND status <> 'spam' GROUP BY context_ref ORDER BY n DESC, label ASC LIMIT 15",
            [$formId]
        );
        return array_map(static fn(array $r): array => ['ref' => (string) $r['context_ref'], 'label' => (string) ($r['label'] ?: $r['context_ref']), 'n' => (int) $r['n']], $rows);
    }

    /** "3 of 41" under the current filters, in the default order (newest first). @return array{index:int,total:int} */
    private function position(int $formId, array $sub, array $sql): array
    {
        [$where, $params] = SubmissionService::where($formId, $sql);
        $total = (int) Database::value("SELECT COUNT(*) FROM form_submissions s WHERE {$where}", $params, 0);
        $newer = (int) Database::value("SELECT COUNT(*) FROM form_submissions s WHERE {$where} AND (s.created_at > ? OR (s.created_at = ? AND s.id > ?))", array_merge($params, [$sub['created_at'], $sub['created_at'], (int) $sub['id']]), 0);
        return ['index' => $newer + 1, 'total' => max($total, $newer + 1)];
    }

    /** How many OTHER non-spam answers the same e-mail address sent to this form (a count; never the answers). */
    private function sameSubmitter(int $formId, array $sub): int
    {
        $email = (string) ($sub['submitter_email'] ?? '');
        if ($email === '') {
            return 0;
        }
        return (int) Database::value("SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND submitter_email = ? AND id <> ? AND status <> 'spam'", [$formId, $email, (int) $sub['id']], 0);
    }

    /**
     * Who did what to one submission (audit log: action, staff name, date, new status). Only for people who may open the audit log.
     * @return list<array{action:string,who:string,at:string,status:?string}>
     */
    private function history(int $sid): array
    {
        $rows = Database::all(
            "SELECT action, user_name, details, created_at FROM audit_log WHERE entity_type = 'form_submission' AND entity_id = ? AND action IN ('form.submission.status','form.submission.notes','form.submission.file','form.submission.print') ORDER BY id DESC LIMIT 12",
            [(string) $sid]
        );
        $out = [];
        foreach ($rows as $r) {
            $d = json_decode((string) ($r['details'] ?? ''), true);
            $st = is_array($d) && isset($d['status']) && in_array($d['status'], FormSubmission::STATUSES, true) ? (string) $d['status'] : null;
            $out[] = ['action' => (string) $r['action'], 'who' => preg_replace('/\s*<[^>]*>\s*$/', '', (string) $r['user_name']) ?: 'Someone', 'at' => (string) $r['created_at'], 'status' => $st];
        }
        return $out;
    }
}
