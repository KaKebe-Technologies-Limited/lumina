<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Request;
use App\Core\Response;
use App\Models\TeamMember;

/**
 * GET /team - board, staff, advisors and volunteers from `team_members` (active rows only), grouped.
 * OWNED BY B7 "org-public". Copy comes from the registry page "team" (app/config/pages/team.php).
 */
final class TeamController extends OrgPublicController
{
    public function index(Request $request): Response
    {
        $groups = ['board' => [], 'staff' => [], 'advisor' => [], 'volunteer' => []];
        foreach (TeamMember::active() as $member) {
            $group = (string) $member['team_group'];
            if (isset($groups[$group])) {
                // only http(s) profile links and a syntactically valid address are ever printed
                $member['linkedin'] = $this->safeUrl($member['linkedin'] ?? '');
                $member['x'] = $this->safeUrl($member['x'] ?? '');
                $email = trim((string) ($member['email'] ?? ''));
                $member['email'] = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
                $groups[$group][] = $member;
            }
        }
        $total = 0;
        foreach ($groups as $list) {
            $total += count($list);
        }

        return $this->renderPage('team', 'web/team/index', [
            'groups' => $groups,
            'total'  => $total,
        ], [
            'breadcrumbs' => $this->crumbs('team'),
        ]);
    }
}
