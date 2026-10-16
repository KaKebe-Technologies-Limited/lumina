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
 * GET /programs and /programs/{slug}  - OWNER: B1 "web-core".
 * Programs come from Admin > Programs; the copy around them (headings, accountability framework, FAQ, how we work) from the
 * "programs" registry page.
 */
final class ProgramsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $pagination = Program::paginate($request->page(), self::PER_PAGE, ['is_active' => 1], 'sort_order ASC, id ASC');
        return $this->renderPage('programs', 'web/programs/index', [
            'pagination' => $pagination,
            'programs'   => $pagination->items,
            'faqs'       => HomeSupport::faqs('programs', null, 'faq.items'),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('programs', 'Programs'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $program = Program::first(['slug' => $slug, 'is_active' => 1]) ?? throw new HttpException(404, 'Not found');
        $projects = Project::where(['program_id' => (int) $program['id'], 'is_active' => 1], 'sort_order ASC, id ASC');
        $others = Program::where(['is_active' => 1, 'id !=' => (int) $program['id']], 'sort_order ASC, id ASC', 3);
        $label = (string) (SitePages::get('programs')['nav_label'] ?? $program['title']);

        return $this->renderPage('programs', 'web/programs/show', [
            'program'  => $program,
            'projects' => $projects,
            'others'   => $others,
        ], [
            'meta' => [
                'title'       => trim((string) ($program['seo_title'] ?? '')) !== '' ? (string) $program['seo_title'] : (string) $program['title'],
                'description' => trim((string) ($program['seo_description'] ?? '')) !== ''
                    ? (string) $program['seo_description']
                    : excerpt((string) (($program['summary'] ?? '') !== '' ? $program['summary'] : ($program['body'] ?? '')), 175),
                'og_image'    => (string) ($program['image'] ?? ''),
                'canonical'   => 'programs/' . $program['slug'],
            ],
            'breadcrumbs' => [[$label, url('programs')], [(string) $program['title'], null]],
        ]);
    }
}
