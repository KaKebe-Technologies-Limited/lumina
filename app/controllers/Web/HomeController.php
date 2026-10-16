<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Project;
use App\Models\Stat;

/**
 * GET /  - the home page. OWNER: B1 "web-core".
 * All copy comes from the "home" registry page (content()); only the statistics (table stats) and the project cards
 * (table projects) are data, each with the registry list as a graceful fallback while the table is empty.
 */
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderPage('home', 'web/home/index', [
            'stats'    => $this->stats(),
            'projects' => $this->projectCards(),
        ], ['bodyClass' => 'is-home']);
    }

    /** @return list<array{label:string,value:int,prefix:string,suffix:string}> */
    private function stats(): array
    {
        $out = [];
        try {
            foreach (Stat::forGroup('home') as $s) {
                $out[] = ['label' => (string) $s['label'], 'value' => (int) $s['value_number'], 'prefix' => (string) $s['prefix'], 'suffix' => (string) $s['suffix']];
            }
        } catch (\Throwable) {
            $out = [];
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

    /** @return list<array{title:string,tagline:string,summary:string,href:string,image:string,image_alt:string}> */
    private function projectCards(): array
    {
        $limit = max(1, min(12, (int) content('home', 'projects.limit', 4)));
        $cards = [];
        try {
            $rows = Project::where(['is_active' => 1], 'is_featured DESC, sort_order ASC, id ASC', $limit);
        } catch (\Throwable) {
            $rows = [];
        }
        foreach ($rows as $p) {
            $cards[] = [
                'title'     => (string) $p['title'],
                'tagline'   => (string) ($p['tagline'] ?? ''),
                'summary'   => (string) ($p['summary'] ?? ''),
                'href'      => url('projects/' . $p['slug']),
                'image'     => (string) ($p['image'] ?? ''),
                'image_alt' => (string) ($p['image_alt'] ?? ''),
            ];
        }
        if ($cards !== []) {
            return $cards;
        }
        foreach (array_slice(content_list('home', 'projects.items'), 0, $limit) as $row) {
            if (trim((string) ($row['title'] ?? '')) === '') {
                continue;
            }
            $cards[] = [
                'title'     => (string) $row['title'],
                'tagline'   => (string) ($row['subtitle'] ?? ''),
                'summary'   => (string) ($row['text'] ?? ''),
                'href'      => link_url((string) ($row['url'] ?? '')),
                'image'     => (string) ($row['image'] ?? ''),
                'image_alt' => (string) ($row['image_alt'] ?? ''),
            ];
        }
        return $cards;
    }
}
