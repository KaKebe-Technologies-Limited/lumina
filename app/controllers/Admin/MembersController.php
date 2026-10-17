<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Members\ListQuery;
use App\Controllers\Admin\Members\MemberCsv;
use App\Core\AdminController;
use App\Core\CsvExport;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MembershipType;
use App\Services\Inbox\Support;
use App\Services\Membership\MembershipApplication;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService;
use App\Services\Membership\OnceToken;

/**
 * ADMIN members: applications list, detail, status workflow, payments, receipts, renewals, e-mails, export. OWNER: ADMIN agent.
 * Contract: docs/MEMBERSHIP.md. Abilities: members.manage (admin + editor) for everything, export.csv for the CSV, members.delete (admin) for
 * deleting. Every business rule lives in App\Services\Membership\* (changeStatus, renew, recordPayment, updatePayment, deletePayment,
 * MembershipApplication::update); this controller only reads the request, calls the service and reports the result.
 *
 * PRIVACY: members are personal data (date of birth, address, phone numbers, optional ID, guardians of minors). Audit entries written here carry
 * ids, references, statuses and field NAMES only; never names, e-mail addresses, phone numbers, dates of birth or search terms.
 *
 * Forms with several blocks on one page post their fields in a bucket (st[...] status, rn[...] renewal, pay[...] new payment, pe<ID>[...] edit
 * payment, rs[...] resend, nt[...] notes) so that errors and old input of one block never leak into another.
 */
final class MembersController extends AdminController
{
    private const BULK_ACTIONS = ['approve', 'reject', 'export'];
    /** Bulk approve / reject can send one e-mail per member, so a request is kept small. */
    private const BULK_LIMIT = 100;
    private const PAYMENT_KEYS = ['amount', 'status', 'method', 'reference', 'paid_at', 'purpose', 'currency', 'term_start', 'term_end', 'notes'];

    // ------------------------------------------------------------------------------------------------ list, export, bulk

    public function index(Request $request): Response
    {
        $this->authorize('members.manage');
        MembershipService::syncExpired();
        $query = ListQuery::fromRequest($request);
        $pagination = $query->page($request->page(), $this->perPage());

        return $this->render('admin/members/index', [
            'pagination'  => $pagination,
            'query'       => $query,
            'f'           => $query->f,
            'counts'      => $query->counts(),
            'summary'     => ListQuery::summary(),
            'types'       => MembershipType::all(),
            'canExport'   => can('export.csv'),
            'canTypes'    => can('members.types'),
            'canSettings' => can('settings.manage'),
            'isOpen'      => MembershipService::isOpen(),
        ], ['title' => 'Members', 'nav' => 'members', 'breadcrumbs' => [['Members', null]]]);
    }

    /** CSV of the current list (all filters, no paging). The file contains personal data: see MemberCsv and the note on the list page. */
    public function export(Request $request): Response
    {
        $this->authorize('members.manage', 'export.csv');
        MembershipService::syncExpired();
        $query = ListQuery::fromRequest($request);
        $rows = $query->rows();
        $this->audit('member.export', 'member', null, 'Exported ' . count($rows) . ' member record(s) to CSV', ['count' => count($rows), 'status' => $query->f['status']]);
        return $this->csv($rows);
    }

    /** Bulk actions on the ticked rows: approve, reject (one shared reason) or export the selected members. */
    public function bulk(Request $request): Response
    {
        $this->authorize('members.manage');
        $action = $request->string('action');
        $ids = $this->bulkIds();
        if ($ids === []) {
            Flash::warning('Tick at least one member first.');
            return $this->back('admin/members');
        }
        if (!in_array($action, self::BULK_ACTIONS, true)) {
            Flash::error('Choose what to do with the selected members.');
            return $this->back('admin/members');
        }
        $trimmed = count($ids) > self::BULK_LIMIT;
        $ids = array_slice($ids, 0, self::BULK_LIMIT);

        if ($action === 'export') {
            $this->authorize('export.csv');
            $rows = ListQuery::byIds($ids);
            $this->audit('member.export', 'member', null, 'Exported ' . count($rows) . ' selected member record(s) to CSV', ['count' => count($rows), 'selected' => true]);
            return $this->csv($rows);
        }

        $reason = trim((string) preg_replace('/\s+/u', ' ', $request->string('reason')));
        if ($action === 'reject' && $reason === '') {
            Flash::error('Give a reason before rejecting applications. It is kept on each record.');
            return $this->back('admin/members');
        }
        $to = $action === 'approve' ? 'approved' : 'rejected';
        $notify = $request->bool('notify');
        $done = [];
        $skipped = [];
        $mailFailed = 0;
        foreach ($ids as $id) {
            $m = Member::find($id);
            if ($m === null) {
                continue;
            }
            $res = MembershipService::changeStatus($id, $to, ['user_id' => $this->userId(), 'reason' => $reason]);
            if (!$res['ok']) {
                $skipped[$res['message']] = ($skipped[$res['message']] ?? 0) + 1;
                continue;
            }
            $done[] = $id;
            if ($notify && $res['member'] !== null) {
                $sent = $to === 'approved' ? MembershipMail::approved($res['member']) : MembershipMail::rejected($res['member']);
                $mailFailed += $sent ? 0 : 1;
            }
        }
        $this->audit('member.bulk', 'member', null, ucfirst($action) . ': ' . count($done) . ' done, ' . array_sum($skipped) . ' skipped',
            ['action' => $action, 'ids' => $done, 'skipped' => array_sum($skipped), 'notified' => $notify]);

        $verb = $to === 'approved' ? 'Approved' : 'Rejected';
        if ($done !== []) {
            Flash::success($verb . ' ' . count($done) . ' application' . (count($done) === 1 ? '' : 's') . '.' . ($notify ? ($mailFailed > 0 ? ' ' . $mailFailed . ' e-mail(s) could not be sent.' : ' The members were e-mailed.') : ''));
        }
        foreach ($skipped as $message => $n) {
            Flash::warning($n . ' skipped: ' . $message);
        }
        if ($done === [] && $skipped === []) {
            Flash::warning('Nothing was changed.');
        }
        if ($trimmed) {
            Flash::info('Only the first ' . self::BULK_LIMIT . ' ticked members were processed. Run the action again for the rest.');
        }
        return $this->back('admin/members');
    }

    // ------------------------------------------------------------------------------------------------ one member

    public function show(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        MembershipService::syncExpired();
        $m = Member::findOrFail($id);
        if ($request->bool('edit')) {
            if (!empty($m['anonymised_at'])) {
                Flash::warning('The personal data of this record was erased, so there is nothing to edit.');
                return $this->redirect('admin/members/' . $id);
            }
            return $this->editForm($m);
        }
        $summary = MembershipService::paymentSummary($m);
        $type = MembershipType::find((int) $m['type_id']);
        $payments = array_reverse($summary['payments']);
        $received = array_values(array_filter($payments, static fn(array $p): bool => $p['status'] === 'received'));

        return $this->render('admin/members/show', [
            'member'      => $m,
            'summary'     => $summary,
            'payments'    => $payments,
            'received'    => $received,
            'type'        => $type,
            'transitions' => MembershipService::allowedTransitions((string) $m['status']),
            'methods'     => MembershipService::options('payment_methods'),
            'activity'    => $this->activity($id),
            'canDelete'   => can('members.delete'),
            'erasable'    => in_array((string) $m['status'], MembershipApplication::ERASABLE_STATUSES, true),
            'canTypes'    => can('members.types'),
            'hasPayments' => $payments !== [],
        ], [
            'title' => (string) $m['full_name'], 'nav' => 'members',
            'breadcrumbs' => [['Members', url('admin/members')], [(string) $m['application_ref'], null]],
        ]);
    }

    private function editForm(array $m): Response
    {
        $lists = [];
        foreach (['gender' => 'gender', 'occupation' => 'occupation_status', 'education' => 'education_level', 'availability' => 'availability', 'heard' => 'heard_about', 'payment_methods' => 'payment_method'] as $list => $column) {
            $options = MembershipService::options($list);
            $stored = (string) ($m[$column] ?? '');
            if ($stored !== '' && !isset($options[$stored])) {
                $options[$stored] = MembershipService::optionLabel($list, $stored);     // an answer the owner removed later stays selectable
            }
            $lists[$list] = $options;
        }
        return $this->render('admin/members/edit', [
            'member'      => $m,
            'lists'       => $lists,
            'interests'   => MembershipService::options('interests'),
            'chosen'      => MembershipService::decodeInterests($m['interests'] ?? null),
            'collectsId'  => MembershipService::collectsId(),
            'canFee'      => can('members.types'),
            'type'        => MembershipType::find((int) $m['type_id']),
            'periods'     => MembershipService::PERIOD_LABELS_ADMIN,
        ], [
            'title' => 'Edit ' . $m['application_ref'], 'nav' => 'members',
            'breadcrumbs' => [['Members', url('admin/members')], [(string) $m['application_ref'], url('admin/members/' . (int) $m['id'])], ['Edit details', null]],
        ]);
    }

    /** Staff correction of the details (section "details", the default) or only the internal notes (section "notes"). */
    public function update(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        $m = Member::findOrFail($id);
        $back = 'admin/members/' . $id;

        if ($request->string('section') === 'notes') {
            $nt = $this->bucket('nt');
            $notes = MembershipApplication::text($nt['internal_notes'] ?? '');
            if (mb_strlen($notes) > 5000) {
                $this->failBucket('nt', ['internal_notes' => 'The notes are too long (5,000 characters at most).'], $nt, $back . '#notes');
            }
            if ((string) ($m['internal_notes'] ?? '') !== $notes) {
                Member::update($id, ['internal_notes' => $notes === '' ? null : $notes]);
                $this->audit('member.update', 'member', $id, 'Edited the notes of ' . $m['application_ref'], ['ref' => $m['application_ref'], 'fields' => ['internal_notes']]);
            }
            Flash::success('Notes saved.');
            return $this->redirect($back . '#notes');
        }

        $res = MembershipApplication::update($id, $request->post(), $this->userId(), can('members.types'));
        if (!$res['ok']) {
            $this->fail($res['errors'], $back . '?edit=1');
        }
        Flash::success($res['changed'] === [] ? 'Nothing was changed.' : 'The details were saved.');
        return $this->redirect($back);
    }

    /** Move the member along the workflow. Only the transitions of MembershipService::TRANSITIONS exist. */
    public function status(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        $m = Member::findOrFail($id);
        $st = $this->bucket('st');
        $back = 'admin/members/' . $id;
        $to = $this->str($st, 'to');
        if (!in_array($to, Member::STATUSES, true)) {
            $this->failBucket('st', ['to' => 'Choose what you want to do.'], $st, $back . '#status');
        }
        $opts = ['user_id' => $this->userId(), 'reason' => $this->str($st, 'reason')];
        if ($to === 'approved' && $m['status'] === 'pending') {
            $start = $this->str($st, 'start_date');
            $expiry = $this->str($st, 'expiry_date');
            if ($start !== '') {
                if (Support::date($start) === null || !MembershipService::validTermDate($start)) {
                    $this->failBucket('st', ['start_date' => 'Use a real date (YYYY-MM-DD) between ' . format_date(MembershipService::DATE_MIN) . ' and ' . MembershipService::DATE_MAX_YEARS . ' years from now.'], $st, $back . '#status');
                }
                $opts['start_date'] = $start;
            }
            if ($expiry !== '') {
                if (Support::date($expiry) === null || !MembershipService::validTermDate($expiry)) {
                    $this->failBucket('st', ['expiry_date' => 'Use a real date (YYYY-MM-DD) between ' . format_date(MembershipService::DATE_MIN) . ' and ' . MembershipService::DATE_MAX_YEARS . ' years from now.'], $st, $back . '#status');
                }
                if ($expiry <= ($start !== '' ? $start : date('Y-m-d'))) {
                    $this->failBucket('st', ['expiry_date' => 'The expiry date must come after the start date.'], $st, $back . '#status');
                }
                $opts['expiry_date'] = $expiry;
            }
        }
        $res = MembershipService::changeStatus($id, $to, $opts);
        if (!$res['ok']) {
            if (in_array($res['error'], ['reason_required', 'reason_too_long'], true)) {
                $this->failBucket('st', ['reason' => $res['message']], $st, $back . '#status');
            }
            if (in_array($res['error'], ['bad_start', 'bad_expiry'], true)) {
                $this->failBucket('st', [$res['error'] === 'bad_start' ? 'start_date' : 'expiry_date' => $res['message']], $st, $back . '#status');
            }
            Flash::error($res['message']);
            return $this->redirect($back . '#status');
        }
        $message = $res['message'] . ($res['number_assigned'] ? ' Membership number ' . ($res['member']['membership_number'] ?? '') . '.' : '');
        Flash::success($message);
        $first = $to === 'approved' && $m['status'] === 'pending';
        if (!empty($st['notify']) && ($first || $to === 'rejected') && $res['member'] !== null && empty($res['member']['anonymised_at'])) {
            $sent = $to === 'approved' ? MembershipMail::approved($res['member']) : MembershipMail::rejected($res['member']);
            $sent ? Flash::info('The member was e-mailed.') : Flash::warning('The status was saved, but the e-mail could not be sent.');
        }
        return $this->redirect($back);
    }

    /** Start a new term (renewal): a pending renewal charge is added; record the payment when the money arrives. */
    public function renew(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        Member::findOrFail($id);
        $rn = $this->bucket('rn');
        $back = 'admin/members/' . $id;
        $opts = ['user_id' => $this->userId()];
        $start = $this->str($rn, 'start_date');
        if ($start !== '') {
            if (Support::date($start) === null || !MembershipService::validTermDate($start)) {
                $this->failBucket('rn', ['start_date' => 'Use a real date (YYYY-MM-DD) between ' . format_date(MembershipService::DATE_MIN) . ' and ' . MembershipService::DATE_MAX_YEARS . ' years from now.'], $rn, $back . '#renewal');
            }
            $opts['start_date'] = $start;
        }
        $amount = $this->str($rn, 'amount');
        if ($amount !== '') {
            if (MembershipService::parseMoney($amount) === null) {
                $this->failBucket('rn', ['amount' => 'Enter a plain amount such as 50000 or 1500.50, or leave it empty for the usual fee.'], $rn, $back . '#renewal');
            }
            $opts['amount'] = $amount;
        }
        if ($this->str($rn, 'notes') !== '') {
            $opts['notes'] = $this->str($rn, 'notes');
        }
        // the form may be sent only once: a double click or a re-sent request must not add a second term and a second charge
        if (!OnceToken::consume('renew:' . $id, $this->str($rn, '_once'))) {
            Flash::warning('That renewal form was already sent (or has expired). Check the payments below, then use the form again if the new term is not there.');
            return $this->redirect($back . '#renewal');
        }
        $res = MembershipService::renew($id, $opts);
        if (!$res['ok'] && in_array($res['error'], ['bad_start', 'term_overlap', 'term_over'], true)) {
            $this->failBucket('rn', ['start_date' => $res['message']], $rn, $back . '#renewal');
        }
        $res['ok'] ? Flash::success($res['message'] . ' A pending renewal charge was added to the payments.') : Flash::error($res['message']);
        return $this->redirect($back . ($res['ok'] ? '#payments' : '#renewal'));
    }

    /** Send one of the member e-mails again (application received, approved, rejected, payment received). */
    public function resend(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        $m = Member::findOrFail($id);
        $rs = $this->bucket('rs');
        $back = 'admin/members/' . $id . '#emails';
        $template = $this->str($rs, 'template');
        $extra = [];
        $problem = null;
        if (!empty($m['anonymised_at'])) {
            $problem = 'The personal data of this record was erased, so there is no address to write to.';
        } elseif ($template === 'ack') {
            // always allowed
        } elseif ($template === 'approved' && $m['status'] !== 'approved') {
            $problem = 'The approval e-mail can only be sent to an approved member.';
        } elseif ($template === 'rejected' && $m['status'] !== 'rejected') {
            $problem = 'The rejection e-mail can only be sent to a rejected application.';
        } elseif ($template === 'payment') {
            $p = ctype_digit($this->str($rs, 'payment_id')) ? MemberPayment::find((int) $this->str($rs, 'payment_id')) : null;
            if ($p === null || (int) $p['member_id'] !== $id || $p['status'] !== 'received') {
                $problem = 'Choose a received payment to send the confirmation for.';
            } else {
                $extra = ['payment' => $p];
            }
        } elseif (!in_array($template, ['approved', 'rejected'], true)) {
            $problem = 'Choose which e-mail to send.';
        }
        if ($problem !== null) {
            Flash::error($problem);
            return $this->redirect($back);
        }
        $sent = MembershipMail::send($template, $m, $extra);
        $this->audit('member.resend', 'member', $id, 'Re-sent the "' . $template . '" e-mail for ' . $m['application_ref'], ['ref' => $m['application_ref'], 'template' => $template, 'sent' => $sent]);
        $sent ? Flash::success('The e-mail was sent.') : Flash::error('The e-mail could not be sent. Check the mail settings and try again.');
        return $this->redirect($back);
    }

    // ------------------------------------------------------------------------------------------------ payments and receipts

    public function recordPayment(Request $request, int $id): Response
    {
        $this->authorize('members.manage');
        Member::findOrFail($id);
        $pay = $this->bucket('pay');
        $back = 'admin/members/' . $id;
        $input = $this->paymentInput($pay);
        // validate first, so a typo does not use up the token; then accept the form only once (a double click must not issue two receipts)
        $check = MembershipService::checkPayment($id, $input);
        if ($check !== []) {
            $this->failBucket('pay', $check, $pay, $back . '#payments');
        }
        if (!OnceToken::consume('pay:' . $id, $this->str($pay, '_once'))) {
            Flash::warning('That payment form was already sent (or has expired). Check the payments below, then record the payment again if it is not there.');
            return $this->redirect($back . '#payments');
        }
        $res = MembershipService::recordPayment($id, $input + ['user_id' => $this->userId()]);
        if (!$res['ok']) {
            $this->failBucket('pay', $res['errors'], $pay, $back . '#payments');
        }
        Flash::success($res['message']);
        if (!empty($pay['notify']) && ($res['payment']['status'] ?? '') === 'received') {
            $m = Member::findOrFail($id);
            MembershipMail::paymentReceived($m, $res['payment']) ? Flash::info('The member was e-mailed a payment confirmation.') : Flash::warning('The payment was saved, but the e-mail could not be sent.');
        }
        return $this->redirect($back . '#payments');
    }

    public function updatePayment(Request $request, int $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        $pid = (int) $pid;                       // only {id} is cast by the router
        Member::findOrFail($id);
        $stored = $this->paymentOf($id, $pid);
        $bucket = 'pe' . $pid;
        $posted = $this->bucket($bucket);
        $back = 'admin/members/' . $id;
        $input = $this->paymentInput($posted);
        // the form shows the paid DATE only: an unchanged date keeps the exact time already stored
        if (isset($input['paid_at']) && !empty($stored['paid_at']) && $input['paid_at'] === substr((string) $stored['paid_at'], 0, 10)) {
            unset($input['paid_at']);
        }
        $input += [
            'amount' => (string) $stored['amount'], 'status' => (string) $stored['status'], 'method' => (string) $stored['method'], 'reference' => (string) ($stored['reference'] ?? ''),
            'purpose' => (string) $stored['purpose'], 'currency' => (string) $stored['currency'], 'notes' => (string) ($stored['notes'] ?? ''),
        ];
        $res = MembershipService::updatePayment($pid, $input + ['user_id' => $this->userId()]);
        if (!$res['ok']) {
            $this->failBucket($bucket, $res['errors'], $posted, $back . '#pay-' . $pid);
        }
        Flash::success($res['message']);
        if (!empty($posted['notify']) && ($res['payment']['status'] ?? '') === 'received') {
            $m = Member::findOrFail($id);
            MembershipMail::paymentReceived($m, $res['payment']) ? Flash::info('The member was e-mailed a payment confirmation.') : Flash::warning('The payment was saved, but the e-mail could not be sent.');
        }
        return $this->redirect($back . '#payments');
    }

    public function deletePayment(Request $request, int $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        $pid = (int) $pid;
        Member::findOrFail($id);
        $this->paymentOf($id, $pid);
        $res = MembershipService::deletePayment($pid);
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#payments');
    }

    /** Printable receipt of one RECEIVED payment: a standalone page without the admin chrome. */
    public function receipt(Request $request, int $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        $pid = (int) $pid;
        Member::findOrFail($id);
        $this->paymentOf($id, $pid);
        $data = MembershipService::receiptData($pid);
        if ($data === null) {
            Flash::error('A receipt exists only for a payment that has been received.');
            return $this->redirect('admin/members/' . $id . '#payments');
        }
        return $this->render('admin/members/receipt', $data + ['methods' => MembershipService::options('payment_methods')], [
            'title' => 'Receipt ' . $data['payment']['receipt_number'], 'layout' => null,
        ]);
    }

    // ------------------------------------------------------------------------------------------------ delete

    /**
     * Delete an application that was never approved and has no payments (spam, test entries, an erasure request before approval). A member
     * with a membership number or payments is cancelled instead: the number and the receipts must stay on record. Admin only; the reference must
     * be typed to confirm.
     */
    public function destroy(Request $request, int $id): Response
    {
        $this->authorize('members.manage', 'members.delete');
        $m = Member::findOrFail($id);
        $back = 'admin/members/' . $id . '#delete';
        if (strcasecmp(trim($request->string('confirm_ref')), (string) $m['application_ref']) !== 0) {
            Flash::error('Type the application reference exactly as shown to confirm the deletion.');
            return $this->redirect($back);
        }
        if (!empty($m['membership_number'])) {
            Flash::error('This member has membership number ' . $m['membership_number'] . ', which must stay on record. Cancel the membership instead.');
            return $this->redirect($back);
        }
        if (MemberPayment::exists(['member_id' => $id])) {
            Flash::error('This member has payment records, which must stay on record (receipts). Cancel the membership instead.');
            return $this->redirect($back);
        }
        Member::delete($id);
        MembershipService::flush();
        $this->audit('member.delete', 'member', $id, 'Deleted application ' . $m['application_ref'], ['ref' => $m['application_ref'], 'status' => $m['status']]);
        Flash::success('Application ' . $m['application_ref'] . ' was deleted.');
        return $this->redirect('admin/members');
    }

    /**
     * Erase the personal data of a member who cannot be deleted (membership number or receipts): admin only, the reference must be typed.
     * The number, fee and receipts stay. Only for rejected, cancelled and expired members (cancel the membership first).
     */
    public function anonymise(Request $request, int $id): Response
    {
        $this->authorize('members.manage', 'members.delete');
        $m = Member::findOrFail($id);
        $back = 'admin/members/' . $id . '#erase';
        if (strcasecmp(trim($request->string('confirm_ref')), (string) $m['application_ref']) !== 0) {
            Flash::error('Type the application reference exactly as shown to confirm.');
            return $this->redirect($back);
        }
        $res = MembershipApplication::anonymise($id, $this->userId());
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . ($res['ok'] ? '' : '#erase'));
    }

    // ------------------------------------------------------------------------------------------------ internals

    /** A posted value as a trimmed string; '' when it is missing or not a plain scalar (an array posted for a text field). */
    private function str(array $bucket, string $key): string
    {
        return is_scalar($bucket[$key] ?? null) ? trim((string) $bucket[$key]) : '';
    }

    /** Download response for member rows selected with ListQuery. @param array<int,array<string,mixed>> $rows */
    private function csv(array $rows): Response
    {
        $lines = (static function () use ($rows): \Generator {
            foreach ($rows as $r) {
                yield MemberCsv::row($r);
            }
        })();
        return CsvExport::response('members-' . date('Y-m-d') . '.csv', MemberCsv::header(), $lines);
    }

    /** @return int[] the ticked row ids of a bulk form (unique, positive) */
    private function bulkIds(): array
    {
        $raw = $this->request->post('ids', []);
        $ids = [];
        foreach (is_array($raw) ? $raw : [] as $v) {
            if (is_scalar($v) && ctype_digit((string) $v) && (int) $v > 0) {
                $ids[(int) $v] = true;
            }
        }
        return array_keys($ids);
    }

    /** One block of a multi-form page: the posted bucket as an array. @return array<string,mixed> */
    private function bucket(string $name): array
    {
        $v = $this->request->post($name, []);
        return is_array($v) ? $v : [];
    }

    /** @param array<string,mixed> $posted @return array<string,string> only the payment fields, as trimmed strings */
    private function paymentInput(array $posted): array
    {
        $out = [];
        foreach (self::PAYMENT_KEYS as $k) {
            if (array_key_exists($k, $posted)) {
                $out[$k] = is_scalar($posted[$k]) ? trim((string) $posted[$k]) : '';
            }
        }
        return $out;
    }

    /** The payment row, which must belong to this member (404 otherwise, so ids cannot be mixed up between members). @return array<string,mixed> */
    private function paymentOf(int $memberId, int $paymentId): array
    {
        $p = MemberPayment::find($paymentId);
        if ($p === null || (int) $p['member_id'] !== $memberId) {
            throw new HttpException(404, 'Not found');
        }
        return $p;
    }

    /**
     * Fail one block of a multi-form page: errors and old input are stored under "<bucket>[field]", the keys the form fields of that block use.
     * @param array<string,string> $errors
     * @param array<string,mixed> $old
     */
    private function failBucket(string $bucket, array $errors, array $old, string $redirectTo): never
    {
        $e = [];
        foreach ($errors as $k => $msg) {
            $e[$bucket . '[' . $k . ']'] = (string) $msg;
        }
        $o = [];
        foreach ($old as $k => $v) {
            if (is_scalar($v)) {
                $o[$bucket . '[' . $k . ']'] = (string) $v;
            }
        }
        throw new ValidationException($e, $o, $redirectTo);
    }

    /** Recent audit entries about this member (the service writes ids and references only). @return array<int,array<string,mixed>> */
    private function activity(int $id): array
    {
        return Database::all(
            "SELECT created_at, user_name, action, summary FROM audit_log WHERE entity_type = 'member' AND entity_id = ? ORDER BY id DESC LIMIT 15",
            [(string) $id]
        );
    }
}
