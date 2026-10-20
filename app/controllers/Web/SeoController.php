<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\Web\SitemapBuilder;

/**
 * GET /sitemap.xml  every published public page (registry + owner-created), programs, projects, blog/news/press posts, activities,
 *                   events, consented stories and open job vacancies - always current, built from the database.
 * GET /robots.txt   allows the site, disallows /admin and the unsubscribe pages, points to the sitemap.
 * Neither route starts a session (no cookie, no session file): they send plain Cache-Control only.
 */
final class SeoController extends Controller
{
    public function sitemap(Request $request): Response
    {
        return Response::text(SitemapBuilder::xml(), 200, 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=0, must-revalidate');     // always current: a newly published issue is listed at once
    }

    public function robots(Request $request): Response
    {
        $base = base_path();
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: ' . $base . '/admin',
            'Disallow: ' . $base . '/unsubscribe/',
            'Disallow: ' . $base . '/meeting/',
            'Disallow: ' . $base . '/shop/cart',
            'Disallow: ' . $base . '/shop/checkout',
            'Disallow: ' . $base . '/shop/order/',
            'Disallow: ' . $base . '/shop/pay/',
            'Disallow: ' . $base . '/shop/webhook/',
            '',
            'Sitemap: ' . abs_url('sitemap.xml'),
            '',
        ];
        return Response::text(implode("\n", $lines), 200, 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
