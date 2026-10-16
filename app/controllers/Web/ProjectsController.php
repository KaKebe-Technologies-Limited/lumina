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
 * Projects come from Admin > Projects (the "proposed areas of intervention"); the copy around them from the "projects" registry page.
 *
 * The list shows EVERY active project grouped by its program pillar (a grouped list is never paginated, because a page break would hide
 * items of a group). Filtering works without JavaScript through the query string (?pillar=<program slug>&status=<status>): the view
 * renders all groups and marks the ones that do not match with `hidden`, so assets/js/projects-filter.js can switch them in place.
 */
final class ProjectsController extends Controller
{
    /** Safety cap on the number of projects loaded for the grouped list. */
    private const MAX_LIST = 500;

    public function index(Request $request): Response
    {
        $filtersOn = section_on('projects', 'filters');
        $projects = Project::active(self::MAX_LIST);

        // pillar buttons: every active program, label from the registry repeater (matched by slug) or the program title
        $chipLabels = [];
        foreach (content_list('projects', 'filters.pillars') as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $label = trim((string) ($row['label'] ?? ''));
            if ($slug !== '' && $label !== '') {
                $chipLabels[$slug] = $label;
            }
        }
        $pillars = [];
        $slugById = [];
        foreach (array_values(Program::active()) as $i => $program) {
            $program['number'] = $i + 1;
            $program['chip_label'] = $chipLabels[(string) $program['slug']] ?? (string) $program['title'];
            $program['projects'] = [];
            $pillars[(string) $program['slug']] = $program;
            $slugById[(int) $program['id']] = (string) $program['slug'];
        }
        $loose = [];   // projects without an active pillar (kept visible under "All")
        foreach ($projects as $project) {
            $slug = $slugById[(int) ($project['program_id'] ?? 0)] ?? null;
            if ($slug === null) {
                $loose[] = $project;
            } else {
                $pillars[$slug]['projects'][] = $project;
            }
        }

        // status buttons only exist when the list really holds more than one status
        $present = array_unique(array_map(static fn(array $p): string => (string) $p['status'], $projects));
        $statusChips = count($present) > 1 ? array_values(array_intersect(Project::STATUSES, $present)) : [];

        $pillar = $filtersOn ? $request->string('pillar') : '';
        $status = $filtersOn ? $request->string('status') : '';
        if (!isset($pillars[$pillar])) {
            $pillar = '';
        }
        if (!in_array($status, $statusChips, true)) {
            $status = '';
        }

        $meta = [];
        if ($pillar !== '' || $status !== '') {
            $meta['canonical'] = 'projects';   // filtered views point search engines at the full list
        }

        return $this->renderPage('projects', 'web/projects/index', [
            'pillars'      => $pillars,
            'loose'        => $loose,
            'total'        => count($projects),
            'statuses'     => $this->statusLabels(),
            'statusChips'  => $statusChips,
            'filtersOn'    => $filtersOn,
            'pillar'       => $pillar,
            'status'       => $status,
        ], [
            'meta'        => $meta,
            'breadcrumbs' => [[HomeSupport::navLabel('projects', 'Projects'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $project = Project::first(['slug' => $slug, 'is_active' => 1]) ?? throw new HttpException(404, 'Not found');

        $program = null;
        $number = 0;   // position of the pillar among the active programs (drives the colour tone)
        if (!empty($project['program_id'])) {
            foreach (array_values(Program::active()) as $i => $candidate) {
                if ((int) $candidate['id'] === (int) $project['program_id']) {
                    $program = $candidate;
                    $number = $i + 1;
                    break;
                }
            }
        }

        // related cards: the other interventions of the same pillar (or a few others when the project has no pillar)
        $others = $program !== null
            ? Project::where(['is_active' => 1, 'program_id' => (int) $program['id'], 'id !=' => (int) $project['id']], 'sort_order ASC, id ASC')
            : Project::where(['is_active' => 1, 'id !=' => (int) $project['id']], 'sort_order ASC, id ASC', 3);

        $label = (string) (SitePages::get('projects')['nav_label'] ?? $project['title']);
        $crumbs = [[$label, url('projects')]];
        if ($program !== null) {
            $crumbs[] = [(string) $program['title'], url('projects', ['pillar' => (string) $program['slug']]) . '#projects-list'];
        }
        $crumbs[] = [(string) $project['title'], null];

        return $this->renderPage('projects', 'web/projects/show', [
            'project'  => $project,
            'program'  => $program,
            'number'   => $number,
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
            'breadcrumbs' => $crumbs,
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
