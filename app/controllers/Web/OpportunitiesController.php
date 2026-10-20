<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Pagination;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Core\Uploader;
use App\Models\Opportunity;
use App\Services\Opportunities\OpportunityImporter;
use App\Services\Web\ShareLinks;

/**
 * GET /opportunities               open opportunities (search ?q=, type ?type=, ?closed=1 to include closed) + the editable page copy
 * GET /opportunities/{slug}        one opportunity
 * GET /opportunities/{slug}/file   the PDF the team attached (only while the opportunity is shown)
 * Opportunities are managed in the admin under Content > Opportunities (typed or uploaded by the team, or read from other websites).
 * When another website's feed is due, it is read AFTER this page was sent to the visitor (App::afterResponse), so the visitor never waits.
 */
final class OpportunitiesController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $q = mb_substr(trim($request->string('q')), 0, 100);
        $type = $request->string('type');
        $type = isset(Opportunity::CATEGORIES[$type]) ? $type : '';
        $closed = $request->bool('closed');

        $where = [Opportunity::SHOWN];
        $params = [];
        if (!$closed) {
            $where[] = Opportunity::OPEN;
        }
        if ($type !== '') {
            $where[] = 't.category = ?';
            $params[] = $type;
        }
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $where[] = '(t.title LIKE ? OR t.summary LIKE ? OR t.organisation LIKE ? OR t.location LIKE ? OR t.eligibility LIKE ?)';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $total = 0;
        $rows = [];
        $counts = [];
        $pagination = Pagination::make(0, 1, self::PER_PAGE);
        try {
            $total = (int) Database::value('SELECT COUNT(*) FROM opportunities t' . $whereSql, $params, 0);
            $pagination = Pagination::make($total, $request->page(), self::PER_PAGE);
            $rows = Database::all(
                'SELECT t.* FROM opportunities t' . $whereSql
                . ' ORDER BY t.is_featured DESC, (t.deadline IS NOT NULL AND t.deadline < CURDATE()) ASC, t.posted_at DESC, t.id DESC LIMIT ? OFFSET ?',
                array_merge($params, [$pagination->perPage, $pagination->offset])
            );
            foreach (Database::all(
                'SELECT t.category, COUNT(*) AS n FROM opportunities t WHERE ' . Opportunity::SHOWN . ($closed ? '' : ' AND ' . Opportunity::OPEN) . ' GROUP BY t.category'
            ) as $c) {
                $counts[(string) $c['category']] = (int) $c['n'];
            }
        } catch (\Throwable) {
            // the upgrade (new tables) has not been imported yet: show the empty page instead of an error
        }

        $this->refreshLater();
        return $this->renderPage('opportunities', 'web/opportunities/index', [
            'rows' => $rows, 'pagination' => $pagination, 'total' => $total, 'q' => $q, 'type' => $type, 'closed' => $closed, 'counts' => $counts,
            'categories' => Opportunity::categoryLabels(),
        ], [
            'bodyClass'   => 'is-opportunities-index',
            'breadcrumbs' => [[$this->navLabel(), null]],
            'meta'        => $q !== '' || $type !== '' || $closed ? ['robots' => 'noindex,follow', 'canonical' => '/opportunities'] : [],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        if (!SitePages::isPublished('opportunities')) {
            throw new HttpException(404, 'Not found');
        }
        $o = $this->findShown($slug);
        $isClosed = $o['deadline'] !== null && (string) $o['deadline'] < date('Y-m-d');
        $more = Database::all(
            'SELECT t.* FROM opportunities t WHERE ' . Opportunity::SHOWN . ' AND ' . Opportunity::OPEN . ' AND t.id <> ? ORDER BY (t.category = ?) DESC, t.posted_at DESC, t.id DESC LIMIT 3',
            [(int) $o['id'], (string) $o['category']]
        );
        $absUrl = abs_url('opportunities/' . $o['slug']);
        $desc = trim((string) ($o['seo_description'] ?? '')) ?: trim((string) ($o['summary'] ?? '')) ?: excerpt((string) ($o['description'] ?? ''), 175);
        $title = trim((string) ($o['seo_title'] ?? '')) !== '' ? (string) $o['seo_title'] : (string) $o['title'];

        return $this->renderPage('opportunities', 'web/opportunities/show', [
            'o' => $o, 'closed' => $isClosed, 'more' => $more, 'absUrl' => $absUrl, 'share' => ShareLinks::for($absUrl, (string) $o['title']),
        ], [
            'bodyClass'   => 'is-opportunity',
            'meta'        => ['title' => $title, 'description' => $desc, 'og_image' => (string) ($o['image'] ?? ''), 'og_type' => 'article', 'canonical' => '/opportunities/' . $o['slug'],
                'robots' => $isClosed ? 'noindex,follow' : ''],
            'breadcrumbs' => [[$this->navLabel(), url('opportunities')], [(string) $o['title'], null]],
        ]);
    }

    public function file(Request $request, string $slug): Response
    {
        if (!SitePages::isPublished('opportunities')) {
            throw new HttpException(404, 'Not found');
        }
        $o = $this->findShown($slug);
        if (empty($o['file_path'])) {
            throw new HttpException(404, 'Not found');
        }
        return Uploader::download((string) $o['file_path'], (string) ($o['file_name'] ?: 'opportunity.pdf'));
    }

    private function findShown(string $slug): array
    {
        $o = Database::one('SELECT t.* FROM opportunities t WHERE t.slug = ? AND ' . Opportunity::SHOWN . ' LIMIT 1', [$slug]);
        if ($o === null) {
            throw new HttpException(404, 'Not found');
        }
        return $o;
    }

    /** Read the other websites' feeds that are due, once this response has been sent. */
    private function refreshLater(): void
    {
        if (!OpportunityImporter::anyDue()) {
            return;
        }
        App::afterResponse(static function (): void {
            OpportunityImporter::refreshDue(false, 20);
        });
    }

    private function navLabel(): string
    {
        return (string) (SitePages::get('opportunities')['nav_label'] ?? 'Opportunities');
    }
}
