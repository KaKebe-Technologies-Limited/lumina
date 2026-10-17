<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\MembershipType;
use App\Services\Membership\MembershipService;

/**
 * ADMIN membership types = THE FEE CONFIGURATION. OWNER: ADMIN agent. Contract: docs/MEMBERSHIP.md. Ability: members.types (admin only).
 *
 * The owner sets the fee here: a type has a fee that is "to be announced" (NULL), free (0) or an amount, an optional joining fee, a
 * currency and a billing period (which also decides when a membership expires). MembershipService::saveType() validates and stores it and
 * MembershipService::deleteType() refuses a type that has members. A fee change only affects NEW applications: every member keeps the
 * fee snapshot taken when they applied.
 *
 * The fee field of the form is a three-way choice (fee_mode: tba | free | amount) that is turned into the service's convention
 * ('' = to be announced, '0' = free, otherwise the amount) before saving, so nobody has to know that "empty means to be announced".
 */
final class MembershipTypesController extends AdminController
{
    /** Slugs of the three placeholder types the installer adds; the owner is asked to edit or delete them. */
    public const STARTER_SLUGS = ['individual-member', 'youth-student-member', 'organisation-partner-member'];

    /** Set by input() when "a set amount" was chosen but no amount was typed: the service then refuses the form and the fee message is made friendlier. */
    private bool $feeMissing = false;

    public function index(Request $request): Response
    {
        $this->authorize('members.types');
        $rows = MembershipType::all('sort_order ASC, id ASC');
        $capacity = [];
        foreach ($rows as $t) {
            $capacity[(int) $t['id']] = MembershipService::capacity($t);
        }
        $placeholders = array_values(array_filter($rows, static fn(array $t): bool => in_array($t['slug'], self::STARTER_SLUGS, true) && $t['fee_amount'] === null));
        $noFee = array_values(array_filter($rows, static fn(array $t): bool => (int) $t['is_active'] === 1 && $t['fee_amount'] === null));

        return $this->render('admin/membership-types/index', [
            'rows'         => $rows,
            'counts'       => MembershipType::memberCounts(),
            'capacity'     => $capacity,
            'placeholders' => $placeholders,
            'noFee'        => $noFee,
            'activeCount'  => count(array_filter($rows, static fn(array $t): bool => (int) $t['is_active'] === 1)),
            'isOpen'       => MembershipService::isOpen(),
            'canSettings'  => can('settings.manage'),
        ], ['title' => 'Membership types & fees', 'nav' => 'membership_types', 'breadcrumbs' => [['Membership types & fees', null]]]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('members.types');
        $next = (int) Database::value('SELECT COALESCE(MAX(sort_order), 0) FROM membership_types', [], 0) + 10;
        $type = [
            'id' => null, 'slug' => '', 'name' => '', 'description' => '', 'eligibility' => '', 'fee_amount' => null, 'joining_fee' => null,
            'currency' => MembershipService::defaultCurrency(), 'period' => 'annual', 'sort_order' => $next, 'is_active' => 1, 'max_members' => null,
        ];
        return $this->form($type, 0);
    }

    public function store(Request $request): Response
    {
        $this->authorize('members.types');
        $res = MembershipService::saveType(null, $this->input());
        if (!$res['ok']) {
            $this->fail($this->errors($res['errors']), 'admin/membership-types/create');
        }
        Flash::success('Membership type "' . $res['type']['name'] . '" was created.'
            . ((int) $res['type']['is_active'] === 1 ? ' It is now offered on the website and in the registration form.' : ' It is switched off, so it is not offered yet.'));
        return $this->redirect('admin/membership-types');
    }

    public function edit(Request $request, int $id): Response
    {
        $this->authorize('members.types');
        return $this->form(MembershipType::findOrFail($id), (int) (MembershipType::memberCounts()[$id] ?? 0));
    }

    public function update(Request $request, int $id): Response
    {
        $this->authorize('members.types');
        $before = MembershipType::findOrFail($id);
        $res = MembershipService::saveType($id, $this->input());
        if (!$res['ok']) {
            $this->fail($this->errors($res['errors']), 'admin/membership-types/' . $id . '/edit');
        }
        $after = $res['type'];
        Flash::success('"' . $after['name'] . '" was saved.');
        $feeChanged = ($before['fee_amount'] ?? null) !== ($after['fee_amount'] ?? null) || ($before['joining_fee'] ?? null) !== ($after['joining_fee'] ?? null)
            || $before['currency'] !== $after['currency'] || $before['period'] !== $after['period'];
        $members = (int) (MembershipType::memberCounts()[$id] ?? 0);
        if ($feeChanged) {
            Flash::info('The new fee applies to new applications only.' . ($members > 0 ? ' The ' . $members . ' existing member' . ($members === 1 ? '' : 's') . ' of this type keep the fee they applied under.' : ''));
        }
        return $this->redirect('admin/membership-types');
    }

    /** Switch a type on or off. An inactive type disappears from the website and the form; its members keep working. */
    public function toggle(Request $request, int $id): Response
    {
        $this->authorize('members.types');
        $t = MembershipType::findOrFail($id);
        $on = (int) $t['is_active'] !== 1;
        MembershipType::update($id, ['is_active' => $on ? 1 : 0]);
        $this->audit('membership_type.update', 'membership_type', $id, ($on ? 'Activated' : 'Deactivated') . ' membership type "' . $t['name'] . '"', ['active' => $on ? 1 : 0]);
        Flash::success('"' . $t['name'] . '" is now ' . ($on ? 'offered on the website.' : 'switched off. Visitors no longer see it; existing members are not affected.'));
        if (!$on && MembershipType::count(['is_active' => 1]) === 0) {
            Flash::warning('No membership type is active now, so the registration form has nothing to offer. Switch a type on or add a new one.');
        }
        return $this->back('admin/membership-types');
    }

    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('members.types');
        MembershipType::findOrFail($id);
        $res = MembershipService::deleteType($id);
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect($res['ok'] ? 'admin/membership-types' : 'admin/membership-types/' . $id . '/edit');
    }

    // ------------------------------------------------------------------------------------------------ internals

    /** @param array<string,mixed> $type */
    private function form(array $type, int $members): Response
    {
        $isNew = $type['id'] === null;
        $groups = [];
        if (!$isNew) {
            $groups = Database::all(
                'SELECT fee_amount, joining_fee, fee_currency, fee_period, COUNT(*) AS n FROM members WHERE type_id = ?
                 GROUP BY fee_amount, joining_fee, fee_currency, fee_period ORDER BY n DESC LIMIT 8',
                [(int) $type['id']]
            );
        }
        // the three-way fee choice is derived from the stored value (NULL = to be announced, 0 = free, otherwise an amount)
        $stored = $type['fee_amount'] ?? null;
        $mode = $stored === null ? 'tba' : ((float) $stored <= 0 ? 'free' : 'amount');
        $title = $isNew ? 'New membership type' : 'Edit membership type';
        return $this->render('admin/membership-types/form', [
            'type'       => $type,
            'isNew'      => $isNew,
            'members'    => $members,
            'taken'      => $isNew ? ['taken' => 0, 'left' => null, 'full' => false] : MembershipService::capacity($type),
            'feeMode'    => $mode,
            'groups'     => $groups,
            'periods'    => MembershipService::PERIOD_LABELS_ADMIN,
            'currencies' => ['UGX', 'USD', 'KES', 'TZS', 'RWF', 'EUR', 'GBP'],
            'preview'    => $this->previewConfig(),
        ], [
            'title' => $title, 'nav' => 'membership_types',
            'breadcrumbs' => [['Membership types & fees', url('admin/membership-types')], [$isNew ? 'New type' : (string) $type['name'], null]],
        ]);
    }

    /** The wording the website uses for fees, handed to the live preview so it reads exactly like MembershipService::feeLabel(). @return array<string,mixed> */
    private function previewConfig(): array
    {
        $periods = [];
        foreach (MembershipService::PERIODS as $p) {
            $periods[$p] = MembershipService::periodLabel($p);
        }
        return [
            'tba'     => MembershipService::copy('types.fee_tba', 'Fee to be announced'),
            'free'    => MembershipService::copy('types.free', 'Free'),
            'joining' => MembershipService::copy('types.joining_fee', 'Joining fee: {amount}'),
            'periods' => $periods,
            'months'  => MembershipService::PERIOD_MONTHS,
            'zero'    => ['UGX', 'RWF', 'BIF', 'XOF', 'XAF', 'JPY', 'KRW', 'TZS'],
        ];
    }

    /** @param array<string,string> $errors @return array<string,string> */
    private function errors(array $errors): array
    {
        if ($this->feeMissing) {
            $errors['fee_amount'] = 'Enter the fee amount, or choose "Fee to be announced" or "Free".';
        }
        return $errors;
    }

    /**
     * The form as the service expects it. fee_mode (tba | free | amount) decides the fee: "to be announced" is stored as an empty fee, "free" as 0.
     * @return array<string,mixed>
     */
    private function input(): array
    {
        $p = $this->request->post();
        $str = static fn(string $k): string => isset($p[$k]) && is_scalar($p[$k]) ? trim((string) $p[$k]) : '';
        $mode = $str('fee_mode');
        $fee = $str('fee_amount');
        if ($mode === 'tba') {
            $fee = '';
        } elseif ($mode === 'free') {
            $fee = '0';
        } elseif ($fee === '') {
            $this->feeMissing = true;
            $fee = '?';                 // not a valid amount: saveType() refuses the form and still reports every other problem as well
        }
        return [
            'name'        => $str('name'),
            'slug'        => $str('slug'),
            'description' => $str('description'),
            'eligibility' => $str('eligibility'),
            'fee_amount'  => $fee,
            'joining_fee' => $str('joining_fee'),
            'currency'    => $str('currency'),
            'period'      => $str('period'),
            'sort_order'  => $str('sort_order') === '' ? '0' : $str('sort_order'),
            'is_active'   => $str('is_active'),
            'max_members' => $str('max_members'),
        ];
    }
}
