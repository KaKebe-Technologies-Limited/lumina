<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Models\Partner;

/**
 * GET /partners - organisations from `partners` (active rows only), grouped by partner_type.
 * OWNED BY B7 "org-public". Copy comes from the registry page "partners" (app/config/pages/partners.php).
 */
final class PartnersController extends OrgPublicController
{
    public function index(Request $request): Response
    {
        $groups = ['partner' => [], 'funder' => [], 'donor' => [], 'collaborator' => []];
        foreach (Partner::active() as $partner) {
            $type = (string) $partner['partner_type'];
            if (isset($groups[$type])) {
                $partner['website'] = $this->safeUrl($partner['website'] ?? '');
                $groups[$type][] = $partner;
            }
        }
        $total = 0;
        foreach ($groups as $list) {
            $total += count($list);
        }

        return $this->renderPage('partners', 'web/partners/index', [
            'groups' => $groups,
            'total'  => $total,
        ], [
            'breadcrumbs' => $this->crumbs('partners'),
        ]);
    }
}
