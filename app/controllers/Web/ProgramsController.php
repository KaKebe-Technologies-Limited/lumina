<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\Program;

/**
 * GET /programs and /programs/{slug}  - OWNER: B1 "web-core".
 * The three PLANNED program pillars come from Admin > Programs, their "proposed areas of intervention" from Admin > Projects
 * (projects.program_id); the copy around them (hero, priorities, approach, audience, partnerships, commitment, labels) from the
 * "programs" registry page. See docs/PROGRAMS_CONTENT_MAP.md.
 */
final class ProgramsController extends Controller
{
    public function index(Request $request): Response
    {
        $programs = Program::active();

        return $this->renderPage('programs', 'web/programs/index', [
            'programs'      => $programs,
            'interventions' => self::interventionsByProgram(),
        ], [
            'breadcrumbs' => [[HomeSupport::navLabel('programs', 'Programs'), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $program = Program::first(['slug' => $slug, 'is_active' => 1]) ?? throw new HttpException(404, 'Not found');

        // The proposed areas of intervention of THIS pillar (every active linked project, in the owner's order).
        $projects = Database::all(
            'SELECT slug, title, summary FROM projects WHERE program_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC',
            [(int) $program['id']]
        );

        // Position among the active pillars -> "01", previous / next pillar.
        $all = Program::active();
        $index = 0;
        foreach ($all as $i => $row) {
            if ((int) $row['id'] === (int) $program['id']) {
                $index = $i;
                break;
            }
        }
        $prev = $all[$index - 1] ?? null;
        $next = $all[$index + 1] ?? null;

        $bodyHtml = trim((string) ($program['body'] ?? '')) !== '' ? HomeSupport::answerHtml((string) $program['body']) : '';
        [$intro, $contribution, $after] = self::splitBody($bodyHtml);

        $label = (string) (SitePages::get('programs')['nav_label'] ?? $program['title']);

        return $this->renderPage('programs', 'web/programs/show', [
            'program'      => $program,
            'projects'     => $projects,
            'number'       => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'prev'         => $prev,
            'next'         => $next,
            'bodyIntro'    => $intro,
            'bodyCallout'  => $contribution,
            'bodyAfter'    => $after,
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

    /**
     * Active projects grouped by program_id: [program_id => [['slug','title'], ...]] in the owner's order. Used for the
     * intervention chips on the pillar cards.
     * @return array<int, list<array{slug:string,title:string}>>
     */
    private static function interventionsByProgram(): array
    {
        $out = [];
        foreach (Database::all('SELECT program_id, slug, title FROM projects WHERE program_id IS NOT NULL AND is_active = 1 ORDER BY sort_order ASC, id ASC') as $r) {
            $out[(int) $r['program_id']][] = ['slug' => (string) $r['slug'], 'title' => (string) $r['title']];
        }
        return $out;
    }

    /**
     * Splits the (already sanitised) pillar body into [text before the first <h3>, the first <h3> block ("Our intended contribution"),
     * anything after the next heading]. The callout's <h3> becomes an <h2> because on the page it is a top-level block (heading order).
     * @return array{0:string,1:string,2:string}
     */
    private static function splitBody(string $html): array
    {
        if ($html === '' || !preg_match('~<h3\b~i', $html, $m, PREG_OFFSET_CAPTURE)) {
            return [$html, '', ''];
        }
        $start = (int) $m[0][1];
        $intro = substr($html, 0, $start);
        $rest = substr($html, $start);
        $afterHeading = preg_match('~</h3>~i', $rest, $c, PREG_OFFSET_CAPTURE) ? (int) $c[0][1] + 5 : strlen($rest);
        $after = '';
        if (preg_match('~<h[23]\b~i', $rest, $n, PREG_OFFSET_CAPTURE, $afterHeading)) {
            $after = substr($rest, (int) $n[0][1]);
            $rest = substr($rest, 0, (int) $n[0][1]);
        }
        $rest = (string) preg_replace('~<(/?)h3\b~i', '<$1h2', $rest);
        return [$intro, $rest, $after];
    }
}
