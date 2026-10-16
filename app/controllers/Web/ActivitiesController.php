<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\Activity;
use App\Services\Web\ShareLinks;

/**
 * GET /activities (paginated active activities + the editable "About Our Activities" story) and GET /activities/{slug}.
 * Activities are written in the admin under Content > Activities; the copy around them comes from the "activities" registry page.
 */
final class ActivitiesController extends Controller
{
    private const PER_PAGE = 9;
    private const ORDER = 'sort_order ASC, activity_date DESC, id DESC';

    public function index(Request $request): Response
    {
        $pagination = Activity::paginate($request->page(), self::PER_PAGE, ['is_active' => 1], self::ORDER);
        return $this->renderPage('activities', 'web/activities/index', [
            'pagination' => $pagination,
            'activities' => $pagination->items,
        ], [
            'bodyClass'   => 'is-activities-index',
            'breadcrumbs' => [[$this->navLabel(), null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        if (!SitePages::isPublished('activities')) {
            throw new HttpException(404, 'Not found');
        }
        $activity = Activity::first(['slug' => $slug, 'is_active' => 1]);
        if ($activity === null) {
            throw new HttpException(404, 'Not found');
        }
        $more = Database::all(
            'SELECT * FROM activities WHERE is_active = 1 AND id <> ? ORDER BY ' . self::ORDER . ' LIMIT 3',
            [(int) $activity['id']]
        );
        $absUrl = abs_url('activities/' . $activity['slug']);
        $title = trim((string) ($activity['seo_title'] ?? '')) !== '' ? (string) $activity['seo_title'] : (string) $activity['title'];
        $description = trim((string) ($activity['seo_description'] ?? ''));
        if ($description === '') {
            $description = trim((string) ($activity['summary'] ?? ''));
        }
        if ($description === '') {
            $description = excerpt((string) ($activity['body'] ?? ''), 175);
        }

        return $this->renderPage('activities', 'web/activities/show', [
            'activity' => $activity,
            'more'     => $more,
            'absUrl'   => $absUrl,
            'share'    => ShareLinks::for($absUrl, (string) $activity['title']),
        ], [
            'bodyClass'   => 'is-activity',
            'meta'        => [
                'title'       => $title,
                'description' => $description,
                'og_image'    => (string) ($activity['image'] ?? ''),
                'og_type'     => 'article',
                'canonical'   => '/activities/' . $activity['slug'],
            ],
            'breadcrumbs' => [[$this->navLabel(), url('activities')], [(string) $activity['title'], null]],
        ]);
    }

    private function navLabel(): string
    {
        return (string) (SitePages::get('activities')['nav_label'] ?? 'Activities');
    }
}
