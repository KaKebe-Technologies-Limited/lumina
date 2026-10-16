<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\TeamMember;

/**
 * GET /about  - OWNER: B1 "web-core". Copy from the "about" registry page; the board of directors comes from Admin > Team
 * (group board) and simply does not appear while that table is empty.
 */
final class AboutController extends Controller
{
    public function index(Request $request): Response
    {
        $board = [];
        $hasTeam = false;
        try {
            $board = TeamMember::active('board');
            $hasTeam = TeamMember::count(['is_active' => 1]) > 0;
        } catch (\Throwable) {
            $board = [];
        }
        return $this->renderPage('about', 'web/about/index', ['board' => $board, 'hasTeam' => $hasTeam], [
            'breadcrumbs' => [[HomeSupport::navLabel('about', 'About'), null]],
        ]);
    }
}
