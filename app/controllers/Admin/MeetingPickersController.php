<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Services\Meetings\InviteeParser;
use App\Services\Meetings\JitsiLink;
use App\Services\Meetings\MeetingService;

/**
 * JSON helpers of the schedule form: the invitee "smart box" parser, the member / staff / earlier-attendee pickers and the Jitsi room button.
 * OWNER: ARCHITECT (complete, tested). Ability meetings.manage (routes AND every method). These endpoints return personal data (names and
 * addresses of members and staff): admin area only, never cached. Contract: docs/MEETINGS.md section 10.
 *
 * Errors: {"error":"<code>","message":"..."} with 422 / 400. Success shapes are documented per method.
 */
final class MeetingPickersController extends AdminController
{
    private function reply(array $data, int $status = 200): Response
    {
        return $this->json($data, $status)->header('Cache-Control', 'private, no-store');
    }

    /**
     * POST /admin/api/meetings/parse   {text: "pasted addresses", exclude?: ["a@b.org", ...]}
     * -> {valid:[{name,email}], invalid:[{input,reason}], duplicates:int, skipped:int, truncated:bool, count:int}
     * `exclude` = addresses already in the box (counted as skipped, not added twice). Nothing is stored.
     */
    public function parse(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $text = $request->post('text', '');
        if (!is_string($text) || strlen($text) > 200000) {
            return $this->reply(['error' => 'bad_request', 'message' => 'Paste the addresses as text (at most 200 KB).'], 400);
        }
        $exclude = array_values(array_filter((array) $request->post('exclude', []), 'is_string'));
        $r = InviteeParser::parse($text, array_slice($exclude, 0, 1000));
        return $this->reply($r + ['count' => count($r['valid'])]);
    }

    /** POST /admin/api/meetings/jitsi -> {url, service, notice}. An unguessable https://meet.jit.si/<room> link; nothing is stored. */
    public function jitsi(Request $request): Response
    {
        $this->authorize('meetings.manage');
        return $this->reply([
            'url' => JitsiLink::generate(), 'service' => 'Jitsi Meet (meet.jit.si)',
            'notice' => 'Jitsi Meet is a free service run by someone else (8x8). Nothing is created until the first person opens the link, and anyone who has the link can join, so share it only with the people you invite.',
        ]);
    }

    /** GET /admin/api/meetings/pickers/members?q=ann  -> {items:[{id,name,email,number,type}]}  (approved members only; needs 2+ characters) */
    public function members(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $q = $request->query('q', '');
        return $this->reply(['items' => MeetingService::searchMembers(is_string($q) ? $q : '', 20)]);
    }

    /** GET /admin/api/meetings/pickers/member-types -> {items:[{id,name,count}], total:int, can_bulk:bool}  (count = approved members with an address) */
    public function memberTypes(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $items = MeetingService::memberTypeCounts();
        return $this->reply(['items' => $items, 'total' => array_sum(array_column($items, 'count')), 'can_bulk' => can('meetings.bulk')]);
    }

    /**
     * GET /admin/api/meetings/pickers/members-bulk?type=ID (omit type = every approved member)
     * -> {spec:"members_all"|"members_type:ID", count:int, capped:bool, max:500, allowed:bool, needs_admin:bool}
     * The confirmation dialog of "add all approved members / members of type X" shows `count`. Anything over 50 needs an administrator (`allowed:false`).
     */
    public function membersBulk(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $type = $request->query('type', '');
        $spec = (is_string($type) && ctype_digit($type) && $type !== '') ? 'members_type:' . (int) $type : 'members_all';
        $r = MeetingService::bulkItems($spec);
        if ($r === null) {
            return $this->reply(['error' => 'bad_request', 'message' => 'Unknown selection.'], 400);
        }
        $count = count($r['items']);
        $needsAdmin = $count > MeetingService::BULK_THRESHOLD;
        return $this->reply([
            'spec' => $spec, 'count' => $count, 'total' => $r['total'], 'capped' => $r['capped'], 'max' => MeetingService::MAX_BULK_MEMBERS,
            'allowed' => !$needsAdmin || can('meetings.bulk'), 'needs_admin' => $needsAdmin && !can('meetings.bulk'),
        ]);
    }

    /** GET /admin/api/meetings/pickers/staff -> {items:[{id,name,email,role}]} (active administrators and editors) */
    public function staff(Request $request): Response
    {
        $this->authorize('meetings.manage');
        return $this->reply(['items' => MeetingService::staff()]);
    }

    /** GET /admin/api/meetings/pickers/previous?q=ann -> {items:[{name,email}]} (people invited to earlier meetings; needs 2+ characters) */
    public function previous(Request $request): Response
    {
        $this->authorize('meetings.manage');
        $q = $request->query('q', '');
        return $this->reply(['items' => MeetingService::previousAttendees(is_string($q) ? $q : '', 20)]);
    }
}
