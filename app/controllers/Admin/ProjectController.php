<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Database;
use App\Core\Icons;
use App\Core\Request;
use App\Models\Project;

/** Admin CRUD for `projects` (/admin/projects), each optionally linked to a program. OWNER: B4. Engine: Content\CrudController. */
final class ProjectController extends CrudController
{
    private const STATUS_LABELS = ['planned' => 'Planned', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];

    protected function config(): array
    {
        return [
            'entity' => 'project', 'table' => 'projects', 'singular' => 'project', 'plural' => 'projects', 'title' => 'Projects',
            'path' => 'admin/projects', 'views' => 'admin/projects', 'nav' => 'projects',
            'search' => ['title', 'tagline', 'summary', 'slug', 'location'],
            'sorts' => [
                'order'   => 't.sort_order {dir}, t.id {dir}',
                'title'   => 't.title {dir}, t.id ASC',
                'status'  => "FIELD(t.status, 'ongoing', 'planned', 'completed') {dir}, t.sort_order ASC",
                'program' => 'p.title {dir}, t.sort_order ASC',
                'updated' => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['order', 'asc'],
            'order_sql' => 'sort_order ASC, id ASC',
            'reorderable' => true,
            'public' => static fn(array $r): ?string => !empty($r['is_active']) ? 'projects/' . $r['slug'] : null,
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
        return ['is_active' => 1, 'is_featured' => 0, 'status' => 'planned', 'program_id' => (int) request()?->int('program', 0) ?: null, 'sort_order' => $this->nextSortOrder()];
    }

    protected function formData(?array $row): array
    {
        return [
            'programs' => $this->programOptions(),
            'statuses' => self::STATUS_LABELS,
        ];
    }

    protected function listData(): array
    {
        return ['programs' => $this->programOptions(), 'statuses' => self::STATUS_LABELS];
    }

    protected function listSelect(): string
    {
        return 't.*, p.title AS program_title';
    }

    protected function listJoin(): string
    {
        return 'LEFT JOIN programs p ON p.id = t.program_id';
    }

    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        $active = [];
        $status = $request->string('project_status');
        if (isset(self::STATUS_LABELS[$status])) {
            $where[] = 't.status = ?';
            $params[] = $status;
            $active['project_status'] = $status;
        }
        $program = $request->string('program');
        if ($program === 'none') {
            $where[] = 't.program_id IS NULL';
            $active['program'] = 'none';
        } elseif ($program !== '' && ctype_digit($program)) {
            $where[] = 't.program_id = ?';
            $params[] = (int) $program;
            $active['program'] = $program;
        }
        return $active;
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'title'      => 'required|max:190',
                'slug'       => 'nullable|max:160',
                'tagline'    => 'nullable|max:190',
                'summary'    => 'nullable|max:5000',
                'body'       => 'nullable|max:400000',
                'program_id' => 'nullable|integer|exists:programs,id',
                'status'     => 'required|in:' . implode(',', Project::STATUSES),
                'location'   => 'nullable|max:190',
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date',
            ] + $this->commonRules(),
            'labels' => ['title' => 'Title', 'program_id' => 'Program', 'start_date' => 'Start date', 'end_date' => 'End date', 'seo_title' => 'SEO title',
                'seo_description' => 'SEO description', 'sort_order' => 'Display order'],
            'messages' => ['program_id.exists' => 'Please choose a program from the list.', 'status.in' => 'Please choose a status from the list.'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $start = $this->dateOrNull($valid['start_date']);
        $end = $this->dateOrNull($valid['end_date']);
        if ($start !== null && $end !== null && $end < $start) {
            $this->fail(['end_date' => 'The end date cannot be before the start date.']);
        }
        $title = (string) $this->text($valid['title']);
        return [
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'program_id'      => ($valid['program_id'] ?? '') !== '' ? (int) $valid['program_id'] : null,
            'tagline'         => $this->text($valid['tagline']),
            'summary'         => $this->plain($valid['summary']),
            'body'            => $this->html($request->post('body')),
            'status'          => (string) $valid['status'],
            'location'        => $this->text($valid['location']),
            'start_date'      => $start,
            'end_date'        => $end,
            'is_featured'     => $request->bool('is_featured') ? 1 : 0,
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'sort_order'      => $request->int('sort_order', 0),
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
    }

    /** @return array<int,string> program id => title */
    private function programOptions(): array
    {
        $out = [];
        foreach (Database::all('SELECT id, title FROM programs ORDER BY sort_order ASC, id ASC') as $p) {
            $out[(int) $p['id']] = (string) $p['title'];
        }
        return $out;
    }
}
