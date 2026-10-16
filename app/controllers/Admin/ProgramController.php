<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Database;
use App\Core\Icons;
use App\Core\Request;
use App\Core\Response;

/** Admin CRUD for `programs` (/admin/programs). OWNER: B4. Engine: Content\CrudController. */
final class ProgramController extends CrudController
{
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
        return ['is_active' => 1, 'is_featured' => 0, 'sort_order' => $this->nextSortOrder()];
    }

    protected function formData(?array $row): array
    {
        return ['icons' => Icons::names()];
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
            ] + $this->commonRules(),
            'labels' => ['title' => 'Title', 'tagline' => 'Tagline', 'summary' => 'Summary', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'sort_order' => 'Display order'],
            'messages' => ['icon.in' => 'Please choose an icon from the list.'],
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
            'is_featured'     => $request->bool('is_featured') ? 1 : 0,
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'sort_order'      => $request->int('sort_order', 0),
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
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
