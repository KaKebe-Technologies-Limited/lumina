<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\Program;
use App\Models\Project;
use App\Models\Stat;
use App\Models\Story;

/**
 * GET /  - the home page.
 * All copy comes from the "home" registry page (content()); the data blocks below come from the database and every one of them
 * is an empty list when its table has no rows (the view then hides the section - nothing is ever invented):
 *   stats (table stats, registry list as fallback) - featured (programs + separate projects, registry cards as fallback)
 *   stories (consented only) - gallery (active photos for the mosaic) - faqs (group home > general > donate)
 */
final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderPage('home', 'web/home/index', [
            'stats'    => $this->stats(),
            'featured' => $this->featured(),
            'stories'  => $this->safe(static fn(): array => Story::published(6)),
            'gallery'  => $this->gallery(),
            'faqs'     => $this->faqs(),
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

    /**
     * Cards for the carousel, in display order: every active program, then the active projects that are NOT just a mirror of a
     * program (a project with the same slug as a program is the same initiative: its public page is the program page).
     * Falls back to the editable registry cards while both tables are empty.
     * @return list<array{kind:string,row:array}>  kind = program | project | card
     */
    private function featured(): array
    {
        $limit = max(1, min(12, (int) content('home', 'projects.limit', 8)));
        $cards = [];
        $slugs = [];
        foreach ($this->safe(static fn(): array => Program::active()) as $p) {
            $cards[] = ['kind' => 'program', 'row' => $p];
            $slugs[(string) $p['slug']] = true;
        }
        foreach ($this->safe(static fn(): array => Project::where(['is_active' => 1], 'is_featured DESC, sort_order ASC, id ASC')) as $p) {
            if (!isset($slugs[(string) $p['slug']])) {
                $cards[] = ['kind' => 'project', 'row' => $p];
            }
        }
        if ($cards !== []) {
            return array_slice($cards, 0, $limit);
        }
        foreach (content_list('home', 'projects.items') as $row) {
            if (trim((string) ($row['title'] ?? '')) === '') {
                continue;
            }
            $cards[] = ['kind' => 'card', 'row' => [
                'title'     => (string) $row['title'],
                'tagline'   => (string) ($row['subtitle'] ?? ''),
                'summary'   => (string) ($row['text'] ?? ''),
                'href'      => link_url((string) ($row['url'] ?? '')),
                'image'     => img_url((string) ($row['image'] ?? '')),
                'image_alt' => (string) ($row['image_alt'] ?? ''),
            ]];
        }
        return array_slice($cards, 0, $limit);
    }

    /** Active gallery photos (first 40; the view drops the ones the carousel shows and keeps five) as ['src','alt','has'] (the shape of content_img()). @return list<array{src:string,alt:string,has:bool}> */
    private function gallery(): array
    {
        $out = [];
        foreach (array_slice($this->safe(static fn(): array => GalleryImage::active()), 0, 40) as $g) {
            $src = img_url((string) ($g['image'] ?? ''));
            if ($src !== '') {
                $out[] = ['src' => $src, 'alt' => (string) ($g['alt_text'] ?? ''), 'has' => true];
            }
        }
        return $out;
    }

    /** Questions for the home FAQ: group "home" when it has active rows, else "general", else the Donate questions. @return list<array> */
    private function faqs(): array
    {
        foreach (['home', 'general', 'donate'] as $group) {
            $rows = $this->safe(static fn(): array => Faq::forGroup($group));
            if ($rows !== []) {
                return $rows;
            }
        }
        return [];
    }
}
