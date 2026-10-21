<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Icons;
use App\Core\Request;
use App\Core\Response;
use App\Models\Program;

/** Admin CRUD for `programs` (/admin/programs). OWNER: B4. Engine: Content\CrudController. */
final class ProgramController extends CrudController
{
    private const STATUS_LABELS = ['planned' => 'Planned program area', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];

    protected function config(): array
    {
        return [
            'entity' => 'program', 'table' => 'programs', 'singular' => 'program', 'plural' => 'programs', 'title' => 'Programs',
            'path' => 'admin/programs', 'views' => 'admin/programs', 'nav' => 'programs',
            'search' => ['title', 'tagline', 'summary', 'slug'],
            'sorts' => [
                'order'   => 't.sort_order {dir}, t.id {dir}',
                'title'   => 't.title {dir}, t.id ASC',
                'updated' => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['order', 'asc'],
            'order_sql' => 'sort_order ASC, id ASC',
            'reorderable' => true,
            'public' => static fn(array $r): ?string => !empty($r['is_active']) ? 'programs/' . $r['slug'] : null,
        ];
    }

    protected function richColumns(): array
    {
        return ['body'];
    }

    protected function imageFields(): array
    {
        return ['image' => 'image_alt'];
    }

    protected function defaults(): array
    {
        return ['is_active' => 1, 'is_featured' => 0, 'status' => 'planned', 'sort_order' => $this->nextSortOrder()];
    }

    protected function formData(?array $row): array
    {
        return ['icons' => Icons::names(), 'statuses' => self::STATUS_LABELS];
    }

    protected function listData(): array
    {
        return ['statuses' => self::STATUS_LABELS];
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'title'   => 'required|max:190',
                'slug'    => 'nullable|max:160',
                'tagline' => 'nullable|max:190',
                'summary' => 'nullable|max:5000',
                'body'    => 'nullable|max:400000',
                'icon'    => 'nullable|in:' . implode(',', Icons::names()),
                'status'  => 'required|in:' . implode(',', Program::STATUSES),
            ] + $this->commonRules(),
            'labels' => ['title' => 'Title', 'status' => 'Program status', 'tagline' => 'Tagline', 'summary' => 'Summary', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'sort_order' => 'Display order'],
            'messages' => ['icon.in' => 'Please choose an icon from the list.', 'status.in' => 'Please choose a status from the list.'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $title = (string) $this->text($valid['title']);
        return [
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'tagline'         => $this->text($valid['tagline']),
            'summary'         => $this->plain($valid['summary']),
            'body'            => $this->html($request->post('body')),
            'icon'            => $valid['icon'] !== '' && $valid['icon'] !== null ? (string) $valid['icon'] : null,
            'status'          => (string) $valid['status'],
            'is_featured'     => $request->bool('is_featured') ? 1 : 0,
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'sort_order'      => $request->int('sort_order', 0),
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
    }

    /**
     * POST /admin/programs/{id}/status (status = planned | ongoing | completed): change the stage of a program straight from the list. The public pages read the
     * status from the database on every request, so the program card, the Programs page and the program page show the new stage at once.
     */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('content.manage');
        $row = $this->find($id);
        $to = $request->string('status');
        if (!in_array($to, Program::STATUSES, true)) {
            throw new HttpException(422, 'Unknown status');
        }
        if ((string) ($row['status'] ?? '') !== $to) {
            Database::update('programs', ['status' => $to], 'id = ?', [$id]);
            $this->audit('program.status', 'program', $id, 'Changed the stage of program "' . $this->rowLabel($row) . '" from ' . ($row['status'] ?? '?') . ' to ' . $to);
        }
        if ($request->expectsJson()) {
            return $this->json(['ok' => true, 'id' => $id, 'status' => $to]);
        }
        Flash::success('"' . $this->rowLabel($row) . '" is now ' . strtolower(self::STATUS_LABELS[$to]) . '. The website shows the new stage straight away.');
        return Response::redirect($this->returnUrl($request, 'admin/programs'));
    }
    /** Projects that belong to a program are kept (program_id is set to NULL by the FK); report how many in the audit trail. */
    public function delete(Request $request, int $id): Response
    {
        $n = (int) Database::value('SELECT COUNT(*) FROM projects WHERE program_id = ?', [$id], 0);
        $response = parent::delete($request, $id);
        if ($n > 0) {
            $this->audit('program.unlink', 'program', $id, $n . ' project(s) are no longer linked to a program');
        }
        return $response;
    }
}
