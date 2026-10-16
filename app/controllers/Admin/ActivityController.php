<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Content\CrudController;
use App\Core\Request;

/** Admin CRUD for `activities` (/admin/activities): events and activities the foundation has run, newest first on the site. OWNER: B4. */
final class ActivityController extends CrudController
{
    protected function config(): array
    {
        return [
            'entity' => 'activity', 'table' => 'activities', 'singular' => 'activity', 'plural' => 'activities', 'title' => 'Activities',
            'path' => 'admin/activities', 'views' => 'admin/activities', 'nav' => 'activities',
            'search' => ['title', 'summary', 'location', 'slug'],
            'sorts' => [
                'order'   => 't.sort_order {dir}, t.activity_date DESC, t.id DESC',     // = the order of the public Activities page
                'date'    => 't.activity_date {dir}, t.id {dir}',
                'title'   => 't.title {dir}, t.id ASC',
                'updated' => 't.updated_at {dir}, t.id {dir}',
            ],
            'default_sort' => ['order', 'asc'],
            'order_sql' => 'sort_order ASC, activity_date DESC, id DESC',
            'reorderable' => true,
            'public' => static fn(array $r): ?string => !empty($r['is_active']) ? 'activities/' . $r['slug'] : null,
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
        return ['is_active' => 1, 'activity_date' => date('Y-m-d'), 'sort_order' => 0];
    }

    protected function rules(?array $row): array
    {
        return [
            'rules' => [
                'title'         => 'required|max:220',
                'slug'          => 'nullable|max:190',
                'summary'       => 'nullable|max:5000',
                'body'          => 'nullable|max:400000',
                'activity_date' => 'nullable|date',
                'location'      => 'nullable|max:190',
            ] + array_diff_key($this->commonRules(), ['is_featured' => 1]),
            'labels' => ['title' => 'Title', 'activity_date' => 'Date', 'seo_title' => 'SEO title', 'seo_description' => 'SEO description', 'sort_order' => 'Display order'],
        ];
    }

    protected function payload(Request $request, ?array $row, array $valid): array
    {
        $title = (string) $this->text($valid['title']);
        return [
            'slug'            => $this->slugFor($request, $row, $title),
            'title'           => $title,
            'summary'         => $this->plain($valid['summary']),
            'body'            => $this->html($request->post('body')),
            'activity_date'   => $this->dateOrNull($valid['activity_date']),
            'location'        => $this->text($valid['location']),
            'is_active'       => $request->bool('is_active') ? 1 : 0,
            'sort_order'      => $request->int('sort_order', 0),
            'seo_title'       => $this->text($valid['seo_title']),
            'seo_description' => $this->text($valid['seo_description']),
        ];
    }
}
