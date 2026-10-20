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
use App\Core\ImageOptimizer;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MembershipType;
use App\Services\Inbox\Support;
use App\Services\Membership\CardService;
use App\Services\Membership\MemberFiles;
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
    private const BULK_ACTIONS = ['approve', 'reject', 'export', 'card'];
    /** Bulk approve / reject can send one e-mail per member, so a request is kept small. */
    private const BULK_LIMIT = 100;
    /** Issuing a card renders a PDF and sends an e-mail, so a request is kept small. */
    private const CARD_BULK_LIMIT = 50;
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
        ], ['title' => 'Members', 'nav' => $query->f['proof'] === 'received' ? 'members_proofs' : 'members', 'breadcrumbs' => [['Members', null]]]);
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
        if ($action === 'card') {
            return $this->issueCards($ids, $trimmed);
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
        $cards = 0;
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
            if ($to === 'approved' && (int) (Member::find($id)['card_version'] ?? 0) > (int) $m['card_version']) {
                $cards++;                                  // the automatic card rule (setting membership_card_trigger) issued and e-mailed a card
            }
            if ($notify && $res['member'] !== null) {
                $sent = $to === 'approved' ? MembershipMail::approved($res['member']) : MembershipMail::rejected($res['member']);
                $mailFailed += $sent ? 0 : 1;
            }
        }
        $this->audit('member.bulk', 'member', null, ucfirst($action) . ': ' . count($done) . ' done, ' . array_sum($skipped) . ' skipped',
            ['action' => $action, 'ids' => $done, 'skipped' => array_sum($skipped), 'notified' => $notify, 'cards' => $cards]);

        $verb = $to === 'approved' ? 'Approved' : 'Rejected';
        if ($done !== []) {
            Flash::success($verb . ' ' . count($done) . ' application' . (count($done) === 1 ? '' : 's') . '.' . ($notify ? ($mailFailed > 0 ? ' ' . $mailFailed . ' e-mail(s) could not be sent.' : ' The members were e-mailed.') : ''));
        }
        if ($cards > 0) {
            Flash::info($cards . ' membership card' . ($cards === 1 ? ' was' : 's were') . ' issued and e-mailed automatically.');
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
            'card'        => CardService::summary($m),            // photo, proof of payment and membership card panels (admin/members/_photo.php, _proof.php, _card.php)
            'checks'      => $this->cardChecks($m),
            'proofBy'     => $this->staffName(isset($m['proof_verified_by']) ? (int) $m['proof_verified_by'] : 0),
            'gd'          => ImageOptimizer::capabilities()['gd'],
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
        if (in_array('email', $res['changed'], true) && !empty($res['member']) && empty($res['member']['anonymised_at'])) {
            // The private page link was sent to the OLD address (a typing mistake could mean a stranger holds it): kill it, the new one goes to the new address.
            MembershipService::regenerateDashboardToken($id);
            $this->audit('member.link_reset', 'member', $id, 'Private page link reset for ' . $m['application_ref'] . ' (e-mail address changed)', ['ref' => $m['application_ref']]);
            Flash::info('The e-mail address changed, so the private page link that was sent to the old address no longer works. Use "Send an e-mail again" to send the member the new link.');
        }
        if (array_intersect($res['changed'], ['full_name', 'city', 'region', 'country', 'fee_amount', 'joining_fee', 'fee_currency', 'fee_period']) !== [] && ($res['member']['card_needs_reissue'] ?? 0)) {
            Flash::info('The issued membership card still shows the old details. Open the card panel and choose "Re-issue (new version)".');
        }
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
        if ($to === 'approved') {
            $this->cardNotice($id, $m);                    // what the automatic card rule did (issued and e-mailed, or why it is waiting)
        }
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
        // a proof file posted with the form is checked BEFORE anything is saved, so a bad file never leaves a payment half done
        $posted = $request->file('pay_proof');
        $fileCheck = MemberFiles::inspectUpload($posted, 'proof');
        if (!$fileCheck['ok']) {
            $check['proof'] = (string) $fileCheck['message'];
        }
        if ($check !== []) {
            $this->failBucket('pay', $check, $pay, $back . '#payments');
        }
        if (!OnceToken::consume('pay:' . $id, $this->str($pay, '_once'))) {
            Flash::warning('That payment form was already sent (or has expired). Check the payments below, then record the payment again if it is not there.');
            return $this->redirect($back . '#payments');
        }
        $before = Member::findOrFail($id);
        $res = MembershipService::recordPayment($id, $input + ['user_id' => $this->userId()]);
        if (!$res['ok']) {
            $this->failBucket('pay', $res['errors'], $pay, $back . '#payments');
        }
        Flash::success($res['message']);
        // pay[attach_proof] = 1: a private COPY of the member's uploaded proof is attached to this payment and, when the money is received, the proof is marked
        // verified (who and when are recorded on the member); a file posted as pay_proof is attached instead (a proof that reached staff outside the website)
        if (!empty($pay['attach_proof']) || $posted !== null) {
            $this->attachProof((int) $res['payment']['id'], $id, $posted, ($res['payment']['status'] ?? '') === 'received', 'record');
        }
        $this->cardNotice($id, $before);
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
        $before = Member::findOrFail($id);
        $res = MembershipService::updatePayment($pid, $input + ['user_id' => $this->userId()]);
        if (!$res['ok']) {
            $this->failBucket($bucket, $res['errors'], $posted, $back . '#pay-' . $pid);
        }
        Flash::success($res['message']);
        $this->cardNotice($id, $before);
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
        $files = \App\Services\Membership\MemberFiles::collectPaths($m);              // photo, proof, card photo copy: removed with the record (no payments, so no receipts to keep)
        if (!empty($m['card_photo_path'])) {
            $files[] = (string) $m['card_photo_path'];
        }
        Member::delete($id);
        \App\Services\Membership\MemberFiles::deletePaths($files);
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

    // ------------------------------------------------------------------------------------------------ member files and the membership card (docs/MEMBERSHIP_CARD.md)
    // The STAFF agent owns this block (views: admin/members/_card.php and friends). Every rule is in MemberFiles / CardService; these actions only read the
    // request, call the service and report. Everything needs members.manage; audit entries (written by the services) carry ids and references only.

    /** Stream the member's photo (inline) to staff. */
    public function photo(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        return MemberFiles::stream(Member::findOrFail((int) $id), 'photo');
    }

    /** Stream the proof of payment. Images are shown inline (zoom / rotate in the page); PDFs and ?download=1 are downloads. */
    public function proofFile(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        return MemberFiles::stream(Member::findOrFail((int) $id), 'proof', $request->string('download') === '1');
    }

    /** Stream the proof attached to a payment row. */
    public function paymentProof(Request $request, int|string $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        return MemberFiles::streamPayment($this->paymentOf((int) $id, (int) $pid), $request->string('download') === '1');
    }

    public function replacePhoto(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = MemberFiles::storePhoto($id, $request->file('photo'), ['by_staff' => true, 'source' => 'staff', 'user_id' => $this->userId()]);
        $res['ok'] ? Flash::success('The photo was saved.' . ($res['notice'] ? ' ' . $res['notice'] : '')) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#photo');
    }

    public function removePhoto(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = MemberFiles::removePhoto($id, $this->userId());
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#photo');
    }

    /** Staff upload a proof of payment for the member (for example one received on WhatsApp). */
    public function uploadProof(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = MemberFiles::storeProof($id, $request->file('proof'), ['by_staff' => true, 'source' => 'staff', 'user_id' => $this->userId()]);
        $res['ok'] ? Flash::success('The proof of payment was saved.') : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#proof');
    }

    /** status = verified | rejected ("needs a better copy": the applicant is e-mailed a polite request with the private-page link) | received. */
    public function proofStatus(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = MemberFiles::setProofStatus($id, $request->string('status'), $this->userId());
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#proof');
    }

    public function removeProof(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = MemberFiles::removeProof($id, $this->userId());
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#proof');
    }

    /** "Issue card now": staff may issue before the money arrives (the payment rule is skipped; approval and, if the setting says so, a photo are still needed). */
    public function issueCard(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = CardService::issue($id, ['user_id' => $this->userId(), 'source' => 'manual', 'force' => true]);
        $res['ok'] ? ($res['already'] ? Flash::info($res['message']) : Flash::success($res['message'])) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#card');
    }

    public function resendCard(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = CardService::resend($id, ['by' => 'staff', 'user_id' => $this->userId()]);
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#card');
    }

    /** Re-issue: a new version from the member's current data and a NEW verification token (the old card's QR code stops verifying). */
    public function reissueCard(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = CardService::reissue($id, ['user_id' => $this->userId(), 'source' => 'manual', 'force' => true, 'reason' => 'staff']);
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#card');
    }

    public function revokeCard(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        Member::findOrFail($id);
        $res = CardService::revoke($id, $request->string('reason'), $this->userId());
        $res['ok'] ? Flash::success($res['message']) : Flash::error($res['message']);
        return $this->redirect('admin/members/' . $id . '#card');
    }

    /**
     * The issued card as a PDF: a download, or with ?inline=1 shown in the page's own viewer (an <iframe> on the member screen; the CSP allows frame-src 'self' and
     * the response keeps X-Frame-Options SAMEORIGIN).
     */
    public function cardPdf(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $m = Member::findOrFail((int) $id);
        $pdf = CardService::pdf($m);
        if ($pdf === null) {
            throw new HttpException(404, 'Not found');
        }
        $inline = $request->string('inline') === '1';
        return new Response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Length' => (string) strlen($pdf), 'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . CardService::filename($m) . '"', 'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    public function cardPng(Request $request, int|string $id): Response
    {
        $this->authorize('members.manage');
        $png = CardService::png(Member::findOrFail((int) $id));
        if ($png === null) {
            throw new HttpException(404, 'Not found');
        }
        return new Response($png, 200, ['Content-Type' => 'image/png', 'Content-Length' => (string) strlen($png), 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow']);
    }

    /** Bulk "issue cards" for ids[] (at most 50 per request); the same as the "Issue membership cards" choice of the list's bulk bar. */
    public function bulkCard(Request $request): Response
    {
        $this->authorize('members.manage');
        $all = array_values(array_unique(array_filter(array_map('intval', (array) $request->post('ids', [])), static fn(int $i): bool => $i > 0)));
        if ($all === []) {
            Flash::warning('Tick at least one member first.');
            return $this->redirect('admin/members');
        }
        return $this->issueCards($all, false);
    }

    /**
     * Issue the cards of the ticked members under the NORMAL rules (approved; balance cleared when the trigger says so; a photo when the card settings require one):
     * staff who want to skip the payment rule use "Issue card now" on the member. One result line per reason, not per member. Idempotent: members who already have a card are
     * counted, not touched.
     * @param int[] $ids
     */
    private function issueCards(array $ids, bool $trimmed): Response
    {
        $trimmed = $trimmed || count($ids) > self::CARD_BULK_LIMIT;
        $ids = array_slice($ids, 0, self::CARD_BULK_LIMIT);
        $issued = 0;
        $already = 0;
        $mailFailed = [];
        $skipped = [];                                  // message => [refs]
        foreach ($ids as $mid) {
            $res = CardService::issue($mid, ['user_id' => $this->userId(), 'source' => 'bulk']);
            $m = $res['member'] ?? Member::find($mid);
            $ref = (string) ($m['application_ref'] ?? '#' . $mid);
            if ($res['ok'] && $res['already']) {
                $already++;
            } elseif ($res['ok']) {
                $issued++;
                if ($res['emailed'] === false) {
                    $mailFailed[] = $ref;
                }
            } else {
                $skipped[(string) $res['message']][] = $ref;
            }
        }
        $skippedCount = array_sum(array_map('count', $skipped));
        $this->audit('member.card_bulk', 'member', null, 'Bulk card issue: ' . $issued . ' issued, ' . $skippedCount . ' skipped', ['issued' => $issued, 'skipped' => $skippedCount, 'already' => $already, 'ids' => $ids]);
        if ($issued > 0) {
            Flash::success($issued . ' card' . ($issued === 1 ? '' : 's') . ' issued' . ($mailFailed === [] ? ' and e-mailed to the member' . ($issued === 1 ? '.' : 's.') : '.'));
        }
        if ($mailFailed !== []) {
            Flash::warning('The card e-mail could not be sent for ' . $this->refs($mailFailed) . '. Open the member and use "Re-send card e-mail".');
        }
        if ($already > 0) {
            Flash::info($already . ' member' . ($already === 1 ? ' already has' : 's already have') . ' a card, so nothing was changed.');
        }
        foreach ($skipped as $message => $refs) {
            Flash::warning(count($refs) . ' skipped (' . $message . ') ' . $this->refs($refs));
        }
        if ($issued === 0 && $already === 0 && $skipped === []) {
            Flash::warning('Nothing was changed.');
        }
        if ($trimmed) {
            Flash::info('Only the first ' . self::CARD_BULK_LIMIT . ' ticked members were processed. Run the action again for the rest.');
        }
        return $this->back('admin/members');
    }

    /** "LAF-APP-1, LAF-APP-2 and 4 more" */
    private function refs(array $refs): string
    {
        $shown = array_slice($refs, 0, 4);
        return implode(', ', $shown) . (count($refs) > 4 ? ' and ' . (count($refs) - 4) . ' more' : '');
    }

    // ------------------------------------------------------------------------------------------------ proof attached to a payment

    /**
     * Attach a proof to an EXISTING payment: a private copy of the member's proof (no file posted) or a posted file (pay_proof). The proof of a received payment is marked verified.
     * Routes: app/routes/admin_members_staff.php.
     */
    public function attachPaymentProof(Request $request, int|string $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        $pid = (int) $pid;
        Member::findOrFail($id);
        $payment = $this->paymentOf($id, $pid);
        $file = $request->file('pay_proof');
        $chk = MemberFiles::inspectUpload($file, 'proof');
        if (!$chk['ok']) {
            Flash::error((string) $chk['message']);
        } else {
            $this->attachProof($pid, $id, $file, (string) $payment['status'] === 'received', 'payment');
        }
        return $this->redirect('admin/members/' . $id . '#pay-' . $pid);
    }

    /** Remove the proof copy of one payment (the member's own proof is not touched). */
    public function removePaymentProof(Request $request, int|string $id, int|string $pid): Response
    {
        $this->authorize('members.manage');
        $id = (int) $id;
        $pid = (int) $pid;
        $m = Member::findOrFail($id);
        $payment = $this->paymentOf($id, $pid);
        if (empty($payment['proof_path'])) {
            Flash::warning('This payment has no proof attached.');
        } else {
            $old = (string) $payment['proof_path'];
            MemberPayment::update($pid, ['proof_path' => null, 'proof_name' => null, 'proof_mime' => null]);
            MemberFiles::deletePaths([$old]);
            $this->audit('member.payment_proof_remove', 'member', $id, 'Removed the proof from a payment on ' . $m['application_ref'], ['ref' => $m['application_ref'], 'payment_id' => $pid]);
            Flash::success('The proof was removed from this payment.');
        }
        return $this->redirect('admin/members/' . $id . '#pay-' . $pid);
    }

    /**
     * Give a payment a proof (the member's uploaded one, or $file) and report it. A proof attached to a RECEIVED payment is marked verified on the member (who and when are
     * stored by MemberFiles::setProofStatus). Nothing is logged but ids and the source.
     */
    private function attachProof(int $paymentId, int $memberId, ?array $file, bool $received, string $source): void
    {
        $mine = Member::findOrFail($memberId);
        if ($file === null && ($mine['proof_status'] ?? 'none') === 'rejected') {
            Flash::warning('The member\'s proof is marked "needs a better copy", so it was not attached. Attach a file instead, or wait for the new upload.');
            return;
        }
        if (!MemberFiles::attachProofToPayment($paymentId, $memberId, $file)) {
            Flash::warning($file !== null ? 'The proof file could not be attached to the payment.' : 'There is no usable proof of payment from the member to attach. Upload one first, or choose a file.');
            return;
        }
        $this->audit('member.payment_proof', 'member', $memberId, 'Attached a proof of payment to a payment on ' . $mine['application_ref'],
            ['ref' => $mine['application_ref'], 'payment_id' => $paymentId, 'file' => $file !== null ? 'uploaded' : 'member_proof', 'verified' => $file === null && $received, 'source' => $source]);
        if ($file === null && $received && ($mine['proof_status'] ?? 'none') !== 'verified') {
            MemberFiles::setProofStatus($memberId, 'verified', $this->userId());
            Flash::info('The proof of payment was attached to this payment and marked as verified.');
        } elseif ($file === null && $received) {
            Flash::info('The proof of payment was attached to this payment.');
        } else {
            Flash::info($received ? 'The proof file was attached to this payment.' : 'The proof file was attached to this payment (it is only marked as verified for a received payment).');
        }
    }

    // ------------------------------------------------------------------------------------------------ what the automatic card rule did

    /**
     * After an approval or a payment: say what the automatic card rule (setting membership_card_trigger, evaluated inside MembershipService) did. $before = the member row
     * as it was before the action.
     * @param array<string,mixed> $before
     */
    private function cardNotice(int $id, array $before): void
    {
        $now = Member::find($id);
        if ($now === null || !empty($now['anonymised_at'])) {
            return;
        }
        if ((int) $now['card_version'] > (int) $before['card_version'] && $now['card_status'] === 'issued') {
            $sent = (int) $now['card_emailed_version'] >= (int) $now['card_version'];
            $sent ? Flash::success('Membership card version ' . (int) $now['card_version'] . ' was issued and e-mailed to the member.')
                : Flash::warning('Membership card version ' . (int) $now['card_version'] . ' was issued, but the e-mail could not be sent. Use "Re-send card e-mail" in the card panel.');
            return;
        }
        if ((string) $now['status'] !== 'approved' || $now['card_status'] !== 'none') {
            return;
        }
        $reasons = CardService::eligibility($now)['reasons'];
        if (CardService::trigger() === 'manual') {
            Flash::info('Cards are issued by hand on this site (card settings). Use "Issue card now" when the member is ready.');
        } elseif (in_array('unpaid', $reasons, true)) {
            Flash::info('The membership card is issued and e-mailed automatically as soon as the balance is paid or waived.');
        } elseif (in_array('photo_missing', $reasons, true)) {
            Flash::info('The membership card waits for a photo (the card settings require one). It is issued and e-mailed as soon as a photo is added.');
        }
    }

    /**
     * What stands between this member and a card, as a checklist for the card panel: [key => [done, label, detail]]. "Issue card now" skips only the payment rule.
     * @param array<string,mixed> $m @return array<string,array{ok:bool,label:string,detail:string,optional:bool}>
     */
    private function cardChecks(array $m): array
    {
        $paid = MembershipService::paymentSummary($m);
        $cleared = in_array($paid['status'], ['paid', 'free'], true);
        $photoRequired = \App\Services\Membership\Card\CardSettings::bool('membership_card_require_photo');
        $hasPhoto = MemberFiles::hasPhoto($m);
        $needsPayment = CardService::trigger() === 'paid_approved';
        return [
            'approved' => ['ok' => (string) $m['status'] === 'approved', 'optional' => false, 'label' => 'Application approved',
                'detail' => (string) $m['status'] === 'approved' ? 'Membership number ' . ($m['membership_number'] ?? '') : 'The card is for approved members only.'],
            'paid' => ['ok' => $cleared, 'optional' => !$needsPayment, 'label' => $needsPayment ? 'Balance paid or waived' : 'Payment',
                'detail' => $cleared ? ($paid['status'] === 'free' ? 'No fee to pay.' : 'Nothing owing.') : ($paid['status'] === 'tbd' ? 'The fee is not set yet.' : 'Still owing ' . MembershipService::formatMoney((float) $paid['balance'], (string) $paid['currency']) . '.')
                    . (!$needsPayment ? ' The card settings do not wait for it.' : '')],
            'photo' => ['ok' => $hasPhoto, 'optional' => !$photoRequired, 'label' => $photoRequired ? 'Photo (required)' : 'Photo',
                'detail' => $hasPhoto ? 'Printed on the card.' : ($photoRequired ? 'No card is issued without a photo.' : 'Without one the card shows the initials, like the sample.')],
        ];
    }

    /** Name of a staff member for "verified by" lines; '' when unknown. */
    private function staffName(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }
        $u = \App\Models\User::find($userId);
        return $u === null ? '' : trim((string) preg_replace('/\s*<[^>]*>\s*$/', '', (string) $u['name']));
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
