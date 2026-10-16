<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\Program;
use App\Models\Project;

/**
 * GET /projects and /projects/{slug}  - OWNER: B1 "web-core".
 * Projects come from Admin > Projects; the copy around them from the "projects" registry page.
 */
final class ProjectsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $pagination = Project::paginate($request->page(), self::PER_PAGE, ['is_active' => 1], 'sort_order ASC, id ASC');
        return $this->renderPage('projects', 'web/projects/index', [
            'pagination' => $pagination,
            'projects'   => $pagination->items,
            'statuses'   => $this->statusLabels(),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('projects', 'Projects'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $project = Project::first(['slug' => $slug, 'is_active' => 1]) ?? throw new HttpException(404, 'Not found');
        $program = null;
        if (!empty($project['program_id'])) {
            $program = Program::first(['id' => (int) $project['program_id'], 'is_active' => 1]);
        }
        $others = Project::where(['is_active' => 1, 'id !=' => (int) $project['id']], 'sort_order ASC, id ASC', 3);
        $label = (string) (SitePages::get('projects')['nav_label'] ?? $project['title']);

        return $this->renderPage('projects', 'web/projects/show', [
            'project'  => $project,
            'program'  => $program,
            'others'   => $others,
            'statuses' => $this->statusLabels(),
        ], [
            'meta' => [
                'title'       => trim((string) ($project['seo_title'] ?? '')) !== '' ? (string) $project['seo_title'] : (string) $project['title'],
                'description' => trim((string) ($project['seo_description'] ?? '')) !== ''
                    ? (string) $project['seo_description']
                    : excerpt((string) (($project['summary'] ?? '') !== '' ? $project['summary'] : ($project['body'] ?? '')), 175),
                'og_image'    => (string) ($project['image'] ?? ''),
                // a project that mirrors its program (same slug, same wording) points search engines at the program page
                'canonical'   => $program !== null && (string) $program['slug'] === (string) $project['slug']
                    ? 'programs/' . $project['slug'] : 'projects/' . $project['slug'],
            ],
            'breadcrumbs' => [[$label, url('projects')], [(string) $project['title'], null]],
        ]);
    }

    /** @return array<string,string> status => editable label */
    private function statusLabels(): array
    {
        return [
            'planned'   => (string) content('projects', 'list.status_planned'),
            'ongoing'   => (string) content('projects', 'list.status_ongoing'),
            'completed' => (string) content('projects', 'list.status_completed'),
        ];
    }
}
