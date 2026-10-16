<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;

/**
 * GET /privacy - the privacy notice. Every section is an editable repeater row (Pages > Privacy Policy). The registry default is a DRAFT
 * written for the data this website really collects; the foundation must review it before launch (see the page help in the admin).
 */
final class PrivacyController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderPage('privacy', 'web/legal/privacy', [
            'items' => array_values(array_filter(
                content_list('privacy', 'notice.items'),
                static fn(array $i): bool => trim((string) ($i['title'] ?? '')) !== '' || trim((string) ($i['body'] ?? '')) !== ''
            )),
        ], [
            'bodyClass'   => 'is-legal',
            'breadcrumbs' => [[(string) (SitePages::get('privacy')['nav_label'] ?? 'Privacy Policy'), null]],
        ]);
    }
}
