<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\SitePages;

/**
 * GET /privacy - the privacy notice. Every section is an editable repeater row (Pages > Privacy Policy); the membership and shop sections are separate blocks appended after them. The registry default is a DRAFT
 * written for the data this website really collects; the foundation must review it before launch (see the page help in the admin).
 */
final class PrivacyController extends Controller
{
    public function index(Request $request): Response
    {
        $items = array_values(array_filter(
            content_list('privacy', 'notice.items'),
            static fn(array $i): bool => trim((string) ($i['title'] ?? '')) !== '' || trim((string) ($i['body'] ?? '')) !== ''
        ));
        // the membership section is its own block (not a row of the list above), so an edited list can never lose it
        if (section_on('privacy', 'membership')) {
            $items[] = ['title' => (string) content('privacy', 'membership.title'), 'body' => (string) content('privacy', 'membership.body')];
        }
        if (section_on('privacy', 'shop')) {
            $items[] = ['title' => (string) content('privacy', 'shop.title'), 'body' => (string) content('privacy', 'shop.body')];
        }
        return $this->renderPage('privacy', 'web/legal/privacy', [
            'items' => $items,
        ], [
            'bodyClass'   => 'is-legal',
            'breadcrumbs' => [[(string) (SitePages::get('privacy')['nav_label'] ?? 'Privacy Policy'), null]],
        ]);
    }
}
