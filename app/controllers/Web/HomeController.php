<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Opportunity;
use App\Models\Program;
use App\Models\Project;
use App\Models\Stat;
use App\Models\Story;

/**
 * GET /  - the home page: a page of HIGHLIGHTS that link to the detail pages.
 * All copy comes from the "home" registry page (content()); the data blocks below come from the database and every one of them
 * is an empty list when its table has no rows (the view then hides the section - nothing is ever invented):
 *   stats (table stats, registry list as fallback) - programs and projects (registry cards as fallback) - the LAF Academy numbers
 *   - the newest open opportunities - stories (consented only)
 */
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $programs = $this->programs();
        $projects = $this->projects($programs);
        return $this->renderPage('home', 'web/home/index', [
            'stats'         => $this->stats(),
            'programs'      => $programs,
            'projects'      => $projects,
            'fallbackCards' => $programs === [] && $projects === [] ? $this->fallbackCards() : [],
            'stories'       => $this->safe(static fn(): array => Story::published(3)),
            'academy'       => $this->academy(),
            'opportunities' => $this->opportunities(),
        ], ['bodyClass' => 'is-home']);
    }

    /** Run a data query; a missing table or any database error simply means "no rows". */
    private function safe(callable $query): array
    {
        try {
            return (array) $query();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array{label:string,value:int,prefix:string,suffix:string}> */
    private function stats(): array
    {
        $out = [];
        foreach ($this->safe(static fn(): array => Stat::forGroup('home')) as $s) {
            $out[] = ['label' => (string) $s['label'], 'value' => (int) $s['value_number'], 'prefix' => (string) $s['prefix'], 'suffix' => (string) $s['suffix']];
        }
        if ($out !== []) {
            return $out;
        }
        foreach (content_list('home', 'stats.items') as $row) {
            if (trim((string) ($row['label'] ?? '')) === '' || $row['value'] === '') {
                continue;
            }
            $out[] = ['label' => (string) $row['label'], 'value' => (int) $row['value'], 'prefix' => (string) ($row['prefix'] ?? ''), 'suffix' => (string) ($row['suffix'] ?? '')];
        }
        return $out;
    }

    /** The active programs, limited to "programs.limit". @return list<array> */
    private function programs(): array
    {
        $limit = max(1, min(12, (int) content('home', 'programs.limit', 6)));
        $rows = array_slice($this->safe(static fn(): array => Program::active()), 0, $limit);
        // a program that has no summary yet shows the first focus areas the owner entered for it (its projects), never invented text
        foreach ($rows as &$r) {
            if (trim((string) ($r['summary'] ?? '')) === '') {
                $r['focus'] = array_map(static fn(array $p): string => (string) $p['title'], $this->safe(static fn(): array => Database::all('SELECT title FROM projects WHERE program_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 3', [(int) $r['id']])));
            }
        }
        unset($r);
        return $rows;
    }

    /**
     * Projects in focus: the active projects that are NOT just a mirror of a program (a project with the same slug as a program is the same initiative: its public
     * page is the program page), taken round-robin across the programs (featured and first in each program's order) so that every program is represented,
     * limited to "projects.limit". Each row also carries its program title.
     * @param list<array> $programs
     * @return list<array>
     */
    private function projects(array $programs): array
    {
        $limit = max(1, min(8, (int) content('home', 'projects.limit', 6)));
        $slugs = [];
        $titles = [];
        $order = [];
        foreach ($this->safe(static fn(): array => Program::active()) as $p) {
            $slugs[(string) $p['slug']] = true;
            $titles[(int) $p['id']] = (string) $p['title'];
            $order[] = (int) $p['id'];
        }
        $byProgram = [];
        foreach ($this->safe(static fn(): array => Project::where(['is_active' => 1], 'is_featured DESC, sort_order ASC, id ASC')) as $p) {
            if (isset($slugs[(string) $p['slug']])) {
                continue;
            }
            $p['program_title'] = $titles[(int) ($p['program_id'] ?? 0)] ?? '';
            $byProgram[(int) ($p['program_id'] ?? 0)][] = $p;
        }
        $keys = array_values(array_unique(array_merge($order, array_keys($byProgram))));
        $out = [];
        for ($round = 0; count($out) < $limit; $round++) {
            $added = false;
            foreach ($keys as $k) {
                if (isset($byProgram[$k][$round]) && count($out) < $limit) {
                    $out[] = $byProgram[$k][$round];
                    $added = true;
                }
            }
            if (!$added) {
                break;
            }
        }
        return $out;
    }

    /** Editable registry cards, used only while the programs and projects tables are both empty. @return list<array> */
    private function fallbackCards(): array
    {
        $cards = [];
        foreach (content_list('home', 'projects.items') as $row) {
            if (trim((string) ($row['title'] ?? '')) === '') {
                continue;
            }
            $cards[] = [
                'title'     => (string) $row['title'],
                'tagline'   => (string) ($row['subtitle'] ?? ''),
                'summary'   => (string) ($row['text'] ?? ''),
                'href'      => link_url((string) ($row['url'] ?? '')),
                'image'     => img_url((string) ($row['image'] ?? '')),
                'image_alt' => (string) ($row['image_alt'] ?? ''),
            ];
        }
        return array_slice($cards, 0, 8);
    }

    /** Live numbers for the LAF Academy band, or null while the Academy is off or its tables are missing. @return array{courses:int,lessons:int,challenges:int}|null */
    private function academy(): ?array
    {
        if ((string) setting('academy_enabled', '1') !== '1') {
            return null;
        }
        try {
            if (!Database::tableExists('academy_courses')) {
                return null;
            }
            $courses = (int) Database::value("SELECT COUNT(*) FROM academy_courses WHERE status = 'published'", [], 0);
            $lessons = (int) Database::value("SELECT COUNT(*) FROM academy_lessons l JOIN academy_courses c ON c.id = l.course_id WHERE c.status = 'published' AND l.is_published = 1", [], 0);
            $challenges = Database::tableExists('academy_challenges')
                ? (int) Database::value("SELECT COUNT(*) FROM academy_challenges WHERE status = 'published' AND finalized_at IS NULL", [], 0) : 0;
            return ['courses' => $courses, 'lessons' => $lessons, 'challenges' => $challenges];
        } catch (\Throwable) {
            return null;
        }
    }

    /** The newest open opportunities (featured first). @return list<array> */
    private function opportunities(): array
    {
        $limit = max(1, min(6, (int) content('home', 'opportunities.limit', 3)));
        return $this->safe(static fn(): array => Database::all(
            'SELECT t.* FROM opportunities t WHERE ' . Opportunity::SHOWN . ' AND ' . Opportunity::OPEN . ' ORDER BY t.is_featured DESC, t.posted_at DESC, t.id DESC LIMIT ' . $limit
        ));
    }
}
