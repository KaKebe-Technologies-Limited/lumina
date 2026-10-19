<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Models\Job;
use App\Services\Forms\FormService;

/**
 * Admin CRUD for `jobs` (/admin/jobs), the vacancies on the Careers page. A vacancy closes by itself when its closing date has passed
 * (the public query Job::open() already hides it); "hidden" means the owner switched it off. OWNER: B4. Engine: Content\CrudController.
 */
final class JobController extends CrudController
{
    protected function config(): array
    {
        return [
            'entity' => 'job', 'table' => 'jobs', 'singular' => 'vacancy', 'plural' => 'vacancies', 'title' => 'Jobs',
            'path' => 'admin/jobs', 'views' => 'admin/jobs', 'nav' => 'jobs',
            'search' => ['title', 'department', 'location', 'summary', 'slug'],
            'sorts' => [
                'order'    => 't.sort_order {dir}, t.id DESC',
                'title'    => 't.title {dir}, t.id DESC',
                'deadline' => 't.closing_date {dir}, t.id DESC',
                'updated'  => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['order', 'asc'],
            'order_sql' => 'sort_order ASC, id DESC',
            'reorderable' => true,
            'public' => static fn(array $r): ?string => (!empty($r['is_active']) && ($r['closing_date'] === null || $r['closing_date'] >= date('Y-m-d'))) ? 'careers/' . $r['slug'] : null,
        ];
    }

    public static function typeLabels(): array
    {
        $out = [];
        foreach (Job::TYPES as $t) {
            $out[$t] = label_from($t);
        }
        return $out;
    }

    protected function richColumns(): array
    {
        return ['description', 'requirements'];
    }

    protected function defaults(): array
    {
        return ['is_active' => 1, 'employment_type' => 'full_time', 'closing_date' => null, 'sort_order' => 0];
    }

    protected function formData(?array $row): array
    {
        return ['types' => self::typeLabels(), 'appForms' => $this->applicationForms($row), 'canPickForm' => $this->canPickForm()];
    }

    /**
     * The custom application forms a vacancy can use (Admin > Forms): id => title. The form already attached to this vacancy stays in the list even
     * when it was archived since, so saving the vacancy never silently detaches it. Empty when the Forms module is not installed.
     * @return array<int,string>
     */
    private function applicationForms(?array $row): array
    {
        if (!$this->hasFormColumn()) {
            return [];
        }
        $choices = FormService::formChoices();
        $current = (int) ($row['form_id'] ?? 0);
        if ($current > 0 && !isset($choices[$current])) {
            $f = FormService::find($current);
            if ($f !== null) {
                $choices[$current] = (string) $f['title'] . ' (' . $f['status'] . ')';
            }
        }
        return $choices;
    }

    /** Only people who can manage forms may change which form a vacancy uses; others never touch the field. */
    private function canPickForm(): bool
    {
        return can('forms.manage') && $this->hasFormColumn();
    }

    /** Is `jobs.form_id` there (an old database without the Forms upgrade must keep working)? */
    private function hasFormColumn(): bool
    {
        static $has = null;
        if ($has === null) {
            try {
                $has = Database::tableExists('forms') && Database::one("SHOW COLUMNS FROM jobs LIKE 'form_id'") !== null;
            } catch (\Throwable) {
                $has = false;
            }
        }
        return $has;
    }

    protected function listData(): array
    {
        return ['types' => self::typeLabels()];
    }

    protected function tabs(): array
    {
        $open = 't.is_active = 1 AND (t.closing_date IS NULL OR t.closing_date >= CURDATE())';
        return [
            'all'    => ['label' => 'All', 'sql' => null, 'params' => []],
            'open'   => ['label' => 'Open', 'sql' => $open, 'params' => []],
            'closed' => ['label' => 'Closed (deadline passed)', 'sql' => 't.is_active = 1 AND t.closing_date < CURDATE()', 'params' => []],
            'hidden' => ['label' => 'Hidden', 'sql' => 't.is_active = 0', 'params' => []],
        ];
    }

    protected function listSelect(): string
    {
        return 't.*, DATEDIFF(t.closing_date, CURDATE()) AS days_left';
    }

    protected function applyFilters(Request $request, array &$where, array &$params): array
    {
        $type = $request->string('employment_type');
        if (in_array($type, Job::TYPES, true)) {
            $where[] = 't.employment_type = ?';
            $params[] = $type;
            return ['employment_type' => $type];
        }
        return [];
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'title'           => 'required|max:220',
                'slug'            => 'nullable|max:190',
                'department'      => 'nullable|max:120',
                'location'        => 'nullable|max:190',
                'employment_type' => 'required|in:' . implode(',', Job::TYPES),
                'summary'         => 'nullable|max:5000',
                'description'     => 'nullable|max:400000',
                'requirements'    => 'nullable|max:400000',
                'how_to_apply'    => 'nullable|max:5000',
                'apply_email'     => 'nullable|email|max:190',
                'closing_date'    => 'nullable|date',
            ] + array_diff_key($this->commonRules(), ['is_featured' => 1]),
            'labels' => ['title' => 'Job title', 'employment_type' => 'Type of work', 'apply_email' => 'Application email', 'closing_date' => 'Closing date',
                'how_to_apply' => 'How to apply', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'sort_order' => 'Display order'],
            'messages' => ['employment_type.in' => 'Please choose a type of work from the list.'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $title = (string) $this->text($valid['title']);
        $extra = [];
        if ($this->canPickForm()) {
            $formId = $request->int('form_id');
            $extra['form_id'] = $formId > 0 && isset($this->applicationForms($row)[$formId]) ? $formId : null;
        }
        return $extra + [
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'department'      => $this->text($valid['department']),
            'location'        => $this->text($valid['location']),
            'employment_type' => (string) $valid['employment_type'],
            'summary'         => $this->plain($valid['summary']),
            'description'     => $this->html($request->post('description')),
            'requirements'    => $this->html($request->post('requirements')),
            'how_to_apply'    => $this->plain($valid['how_to_apply']),
            'apply_email'     => $this->text($valid['apply_email']),
            'closing_date'    => $this->dateOrNull($valid['closing_date']),
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'sort_order'      => $request->int('sort_order', 0),
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
    }

    protected function saved(string $mode, int $id, array $row, ?array $before): void
    {
        $close = (string) ($row['closing_date'] ?? '');
        if ($close !== '' && $close < date('Y-m-d') && !empty($row['is_active'])) {
            Flash::warning('The closing date has already passed, so this vacancy is shown as closed on the website. Change the date to open it again.');
        }
    }
}
