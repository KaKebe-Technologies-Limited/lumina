<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;
use App\Models\GalleryImage;

/**
 * GET /gallery[?album=...&page=N]: active gallery images (admin: Content > Gallery), filtered by album, paginated, opened in
 * the lightbox of assets/js/main.js (a[data-lightbox]). Works without JavaScript (the lightbox is only an enhancement).
 */
final class GalleryController extends Controller
{
    private const PER_PAGE = 24;

    public function index(Request $request): Response
    {
        $album = mb_substr($request->string('album'), 0, 100);
        $albums = Database::all(
            "SELECT album, COUNT(*) AS total FROM gallery_images WHERE is_active = 1 AND album IS NOT NULL AND album <> '' GROUP BY album ORDER BY album"
        );
        $known = array_column($albums, 'album');
        if ($album !== '' && !in_array($album, $known, true)) {
            $album = '';            // unknown album names fall back to "all" instead of an empty page
        }
        $conds = ['is_active' => 1];
        if ($album !== '') {
            $conds['album'] = $album;
        }
        $pagination = GalleryImage::paginate($request->page(), self::PER_PAGE, $conds, 'sort_order ASC, id DESC');

        return $this->renderPage('gallery', 'web/gallery/index', [
            'pagination' => $pagination,
            'images'     => $pagination->items,
            'albums'     => $albums,
            'album'      => $album,
        ], [
            'bodyClass'   => 'is-gallery',
            'breadcrumbs' => [[(string) (SitePages::get('gallery')['nav_label'] ?? 'Gallery'), null]],
        ]);
    }
}
