<?php
/**
 * Admin > Members (applications and members). OWNER: ADMIN agent. Contract: docs/MEMBERSHIP.md.
 * Vars: $pagination (items = member rows + pay_balance / pay_paid / pay_status), $query (ListQuery), $f (filters), $counts (per status + all),
 * $summary (tiles + proofs to check + cards waiting / out of date), $types, $canExport, $canTypes, $canSettings, $isOpen.
 * PRIVACY: a list shows only what staff need to find and judge a record: name, reference, e-mail, type, status, payment, dates.
 */
use App\Controllers\Admin\Members\ListQuery;
use App\Models\Member;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\CardService;
use App\Services\Membership\MembershipService as MS;

$rows = $pagination->items;
// the automatic card rule, read once (the Card column says why a card is not issued yet)
$cardTrigger = CardService::trigger();
$cardNeedsPhoto = CardSettings::bool('membership_card_require_photo');
$proofBadge = ['received' => ['Proof to check', 'warning'], 'verified' => ['Proof verified', 'success'], 'rejected' => ['Better proof asked', 'danger']];
$filtered = $query->isFiltered();
$keepNoStatus = $query->keep(false);
$today = new DateTimeImmutable('today');

$headerActions = [];
if ($canExport) {
    $headerActions[] = [
        'label' => 'Export CSV', 'url' => url('admin/members/export', $query->keep()), 'class' => 'btn--outline', 'icon' => 'download',
        'confirm' => 'This file contains personal data: dates of birth, phone numbers, addresses and guardians of minors. Export only what you need, keep the file somewhere safe and delete it when you are done. Download it now?',
    ];
}
if ($canTypes) {
    $headerActions[] = ['label' => 'Fees and types', 'url' => url('admin/membership-types'), 'class' => 'btn--outline', 'icon' => 'banknote'];
}
$headerActions[] = ['label' => 'View public page', 'url' => url('membership'), 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank'];

// money owing by active members, one figure per currency
$owing = [];
foreach ($summary['owing'] as $cur => $amount) {
    $owing[] = MS::formatMoney($amount, (string) $cur);
}

$tabs = [['key' => 'all', 'label' => 'All']];
foreach (Member::STATUSES as $s) {
    $tabs[] = ['key' => $s, 'label' => MS::STATUS_LABELS[$s] ?? label_from($s)];
}

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-members-card.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-members">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Members',
        'subtitle' => 'Membership applications and members. Review new applications, record the fees people pay and keep an eye on who is due to renew.',
        'actions'  => $headerActions,
    ]) ?>

    <?php if (!$isOpen): ?>
        <div class="alert alert--warning" role="status">
            <?= icon('alert-triangle') ?>
            <div class="alert__body"><strong class="alert__title">Registration is closed.</strong>
                The public form shows a "closed" notice and new applications are refused.
                <?php if ($canSettings): ?><a href="<?= e(url('admin/settings#tab-membership')) ?>">Open registration in the settings</a>.<?php endif; ?></div>
        </div>
    <?php endif; ?>

    <section class="members-kpis" aria-label="Membership at a glance">
        <a class="members-kpi<?= $summary['pending'] > 0 ? ' members-kpi--alert' : '' ?>" href="<?= e(url('admin/members', ['status' => 'pending'])) ?>">
            <span class="members-kpi__icon"><?= icon('user-plus') ?></span>
            <span><strong class="members-kpi__value"><?= number_format($summary['pending']) ?></strong><span class="members-kpi__label">Waiting for review</span></span>
        </a>
        <a class="members-kpi" href="<?= e(url('admin/members', ['status' => 'approved'])) ?>">
            <span class="members-kpi__icon"><?= icon('users') ?></span>
            <span><strong class="members-kpi__value"><?= number_format($summary['approved']) ?></strong><span class="members-kpi__label">Active members</span></span>
        </a>
        <a class="members-kpi" href="<?= e(url('admin/members', ['term' => 'expiring', 'sort' => 'expiry'])) ?>">
            <span class="members-kpi__icon"><?= icon('calendar') ?></span>
            <span><strong class="members-kpi__value"><?= number_format($summary['expiring']) ?></strong><span class="members-kpi__label">Expiring in 30 days</span></span>
        </a>
        <a class="members-kpi" href="<?= e(url('admin/members', ['status' => 'approved', 'payment' => 'unpaid', 'sort' => 'balance'])) ?>">
            <span class="members-kpi__icon"><?= icon('banknote') ?></span>
            <span>
                <strong class="members-kpi__value members-kpi__value--text"><?= $owing === [] ? '0' : e(implode(' + ', $owing)) ?></strong>
                <span class="members-kpi__label">Owing<?= $summary['owing_count'] > 0 ? ' by ' . (int) $summary['owing_count'] . ' active member' . ($summary['owing_count'] === 1 ? '' : 's') : '' ?></span>
            </span>
        </a>
    </section>

    <?php if ($summary['proofs'] > 0 || $summary['card_waiting'] > 0 || $summary['card_reissue'] > 0 || $summary['photo_missing'] > 0): ?>
        <nav class="members-quick" aria-label="Proofs of payment, cards and photos">
            <?php if ($summary['proofs'] > 0): ?>
                <a class="members-quick__item members-quick__item--alert<?= $f['proof'] === 'received' ? ' is-active' : '' ?>" href="<?= e(url('admin/members', ['proof' => 'received', 'sort' => 'proof'])) ?>"><?= icon('check-circle', 'icon--sm') ?> <strong><?= (int) $summary['proofs'] ?></strong> proof<?= $summary['proofs'] === 1 ? '' : 's' ?> of payment to check</a>
            <?php endif; ?>
            <?php if ($summary['card_waiting'] > 0): ?>
                <a class="members-quick__item<?= $f['card'] === 'none' && $f['status'] === 'approved' ? ' is-active' : '' ?>" href="<?= e(url('admin/members', ['status' => 'approved', 'card' => 'none'])) ?>"><?= icon('credit-card', 'icon--sm') ?> <strong><?= (int) $summary['card_waiting'] ?></strong> approved without a card</a>
            <?php endif; ?>
            <?php if ($summary['card_reissue'] > 0): ?>
                <a class="members-quick__item members-quick__item--alert<?= $f['card'] === 'reissue' ? ' is-active' : '' ?>" href="<?= e(url('admin/members', ['card' => 'reissue'])) ?>"><?= icon('refresh', 'icon--sm') ?> <strong><?= (int) $summary['card_reissue'] ?></strong> card<?= $summary['card_reissue'] === 1 ? '' : 's' ?> to update</a>
            <?php endif; ?>
            <?php if ($summary['photo_missing'] > 0): ?>
                <a class="members-quick__item<?= $f['photo'] === 'none' ? ' is-active' : '' ?>" href="<?= e(url('admin/members', ['photo' => 'none'])) ?>"><?= icon('camera', 'icon--sm') ?> <strong><?= (int) $summary['photo_missing'] ?></strong> without a photo</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <nav class="tabs members-tabs" aria-label="Filter by status">
        <?php foreach ($tabs as $t): $on = $t['key'] === $f['status']; ?>
            <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('admin/members', $keepNoStatus + ($t['key'] === 'all' ? [] : ['status' => $t['key']]))) ?>"<?= $on ? ' aria-current="page"' : '' ?>>
                <?= e($t['label']) ?> <span class="members-count"><?= (int) ($counts[$t['key']] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <form class="filters" method="get" action="<?= e(url('admin/members')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, e-mail, phone, reference or membership number" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-type">Membership type</label>
            <select class="select" id="f-type" name="type" data-autosubmit>
                <option value="">All types</option>
                <?php foreach ($types as $t): ?><option value="<?= (int) $t['id'] ?>"<?= $f['type'] === (int) $t['id'] ? ' selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-payment">Payment</label>
            <select class="select" id="f-payment" name="payment" data-autosubmit>
                <option value="">Any payment status</option>
                <?php foreach (MS::PAYMENT_SUMMARY_LABELS as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['payment'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-term">Membership term</label>
            <select class="select" id="f-term" name="term" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ListQuery::TERMS as $k => [$label]): ?><option value="<?= e($k) ?>"<?= $f['term'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-proof">Proof of payment</label>
            <select class="select" id="f-proof" name="proof" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ListQuery::PROOFS as $k => [$label]): ?><option value="<?= e($k) ?>"<?= $f['proof'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-card">Membership card</label>
            <select class="select" id="f-card" name="card" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ListQuery::CARDS as $k => [$label]): ?><option value="<?= e($k) ?>"<?= $f['card'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-photo">Photo</label>
            <select class="select" id="f-photo" name="photo" data-autosubmit>
                <option value="">Any</option>
                <?php foreach (ListQuery::PHOTOS as $k => [$label]): ?><option value="<?= e($k) ?>"<?= $f['photo'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-from">Applied from</label><input class="input" id="f-from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
        <div class="field"><label class="field__label" for="f-to">Applied until</label><input class="input" id="f-to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <?php foreach (ListQuery::SORTS as $k => [$label]): ?><option value="<?= e($k) ?>"<?= $f['sort'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/members')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($canExport): ?>
        <p class="members-note"><?= icon('lock', 'icon--sm') ?> Members are personal data. Only administrators and editors can see this list or download the CSV, and every export is recorded in the activity log.</p>
    <?php endif; ?>

    <?php if ($rows === []): ?>
        <?php $queueEmpty = $f['proof'] === 'received' && $f['q'] === '' && $f['status'] === 'all' && $f['type'] === 0 && $f['payment'] === '' && $f['card'] === '' && $f['photo'] === '' && $f['term'] === '' && $f['from'] === '' && $f['to'] === ''; ?>
        <?= partial('partials/empty-state', [
            'icon'    => $queueEmpty ? 'check-circle' : 'users',
            'title'   => $queueEmpty ? 'No proofs of payment are waiting for a check' : ($filtered ? 'No members match these filters' : 'No applications yet'),
            'text'    => $queueEmpty ? 'When a member uploads a proof of payment it appears here until someone marks it as verified or asks for a better copy.' : ($filtered ? 'Try a different search or clear the filters.' : 'Applications sent from the Membership page on the website appear here for review.'),
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/members'), 'class' => 'btn--outline']] : [['label' => 'View the Membership page', 'url' => url('membership'), 'class' => 'btn--outline']],
        ]) ?>
    <?php else: ?>
        <form id="members-bulk" class="members-bulk" method="post" action="<?= e(url('admin/members/bulk')) ?>" data-members-bulk>
            <?= csrf_field() ?>
            <label class="visually-hidden" for="bulk-action">Action for the selected members</label>
            <select class="select" id="bulk-action" name="action" data-bulk-action>
                <option value="">With the selected members...</option>
                <option value="approve">Approve</option>
                <option value="reject">Reject (a reason is needed)</option>
                <option value="card">Issue membership cards (approved members)</option>
                <?php if ($canExport): ?><option value="export">Export selected (CSV)</option><?php endif; ?>
            </select>
            <label class="check members-bulk__notify" data-bulk-for="approve reject"><input type="checkbox" name="notify" value="1"><span>E-mail the members</span></label>
            <label class="visually-hidden" for="bulk-reason">Reason for rejecting</label>
            <input class="input members-bulk__reason" id="bulk-reason" type="text" name="reason" maxlength="500" placeholder="Reason (kept on each record)" data-bulk-for="reject">
            <button class="btn btn--outline btn--sm" type="submit">Apply</button>
            <span class="members-bulk__count" data-selected-count role="status" aria-live="polite"></span>
        </form>

        <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        <div class="table-wrap" role="region" aria-label="Members" tabindex="0">
            <table class="table table--stack members-table">
                <thead>
                <tr>
                    <th scope="col" class="col-narrow"><input type="checkbox" data-select-all aria-label="Select every member on this page"></th>
                    <th scope="col">Applicant</th>
                    <th scope="col">Membership type</th>
                    <th scope="col">Status and term</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Card</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $m):
                    $id = (int) $m['id'];
                    $sb = MS::statusBadge((string) $m['status']);
                    $pb = MS::summaryBadge((string) $m['pay_status']);
                    $balance = (float) $m['pay_balance'];
                    $minor = MS::isMinor((string) $m['date_of_birth']);
                    $expiry = !empty($m['expiry_date']) ? DateTimeImmutable::createFromFormat('!Y-m-d', (string) $m['expiry_date']) : false;
                    $days = $expiry ? (int) $today->diff($expiry)->format('%r%a') : null;
                    ?>
                    <tr class="members-row<?= $m['status'] === 'pending' ? ' members-row--pending' : '' ?>">
                        <td class="col-narrow" data-label=""><input type="checkbox" name="ids[]" value="<?= $id ?>" form="members-bulk" data-select-row aria-label="Select <?= e($m['full_name']) ?>"></td>
                        <td data-label="Applicant">
                            <span class="cell-title"><a href="<?= e(url('admin/members/' . $id)) ?>"><?= e($m['full_name']) ?></a></span>
                            <span class="cell-sub members-ref"><?= e($m['application_ref']) ?></span>
                            <span class="cell-sub"><?= e($m['email']) ?></span>
                            <span class="cell-sub">Applied <?= e(format_date($m['created_at'], 'j M Y')) ?></span>
                            <?php if ($minor && empty($m['anonymised_at'])): ?><span class="members-tags"><span class="badge badge--outline">Under 18</span></span><?php endif; ?>
                            <?php if (!empty($m['anonymised_at'])): ?><span class="members-tags"><span class="badge badge--outline">Data erased</span></span><?php endif; ?>
                        </td>
                        <td data-label="Type"><?= e($m['type_name']) ?><span class="cell-sub"><?= e(MS::memberFeeLabel($m)) ?></span></td>
                        <td data-label="Status">
                            <?= partial('partials/admin/status-badge', ['status' => $m['status'], 'label' => $sb['label'], 'tone' => $sb['tone']]) ?>
                            <?php if (!empty($m['membership_number'])): ?><span class="cell-sub members-ref"><?= e($m['membership_number']) ?></span><?php endif; ?>
                            <?php if ($expiry): ?>
                                <span class="cell-sub">Until <?= e(format_date($m['expiry_date'], 'j M Y')) ?></span>
                                <?php if ($m['status'] === 'approved' && $days !== null && $days <= 30): ?><span class="cell-sub members-due"><?= $days <= 0 ? 'Due now' : 'In ' . $days . ' day' . ($days === 1 ? '' : 's') ?></span><?php endif; ?>
                            <?php elseif ($m['status'] === 'approved'): ?>
                                <span class="cell-sub">No expiry</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Payment">
                            <?= partial('partials/admin/status-badge', ['status' => $m['pay_status'], 'label' => $pb['label'], 'tone' => $pb['tone']]) ?>
                            <?php if ($balance > 0): ?><span class="cell-sub">Owes <?= e(MS::formatMoney($balance, (string) $m['fee_currency'])) ?></span><?php endif; ?>
                            <?php $ps = (string) ($m['proof_status'] ?? 'none'); if (empty($m['anonymised_at']) && isset($proofBadge[$ps])): ?>
                                <span class="members-tags"><a class="badge badge--<?= e($proofBadge[$ps][1]) ?>" href="<?= e(url('admin/members/' . $id . '#proof')) ?>"><?= e($proofBadge[$ps][0]) ?></a></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Card">
                            <?php
                            $cs = (string) ($m['card_status'] ?? 'none');
                            $stale = $cs === 'issued' && ((int) $m['card_needs_reissue'] === 1 || (!empty($m['expiry_date']) && (empty($m['card_valid_until']) || $m['expiry_date'] > $m['card_valid_until'])));
                            if (!empty($m['anonymised_at'])) {
                                echo '<span class="cell-sub">Erased</span>';
                            } elseif ($cs === 'issued') {
                                echo partial('partials/admin/status-badge', ['status' => 'issued', 'label' => $stale ? 'Update due' : 'Issued', 'tone' => $stale ? 'warning' : 'success']);
                                echo '<span class="cell-sub">Version ' . (int) $m['card_version'] . ', ' . e(format_date((string) $m['card_issued_at'], 'j M Y')) . '</span>';
                                $live = \App\Services\Membership\Card\CardData::state($m, 'issued');          // what the QR code shows today
                                if ($live !== 'active') {
                                    echo '<span class="cell-sub members-due">Shows as ' . e(['expired' => 'expired', 'suspended' => 'suspended', 'revoked' => 'not valid'][$live] ?? $live) . '</span>';
                                }
                                if ((int) $m['card_emailed_version'] < (int) $m['card_version']) {
                                    echo '<span class="cell-sub members-due">Not e-mailed yet</span>';
                                }
                            } elseif ($cs === 'revoked') {
                                echo partial('partials/admin/status-badge', ['status' => 'revoked', 'label' => 'Revoked', 'tone' => 'danger']);
                            } elseif ($m['status'] !== 'approved') {
                                echo '<span class="cell-sub">After approval</span>';
                            } else {
                                $why = $cardTrigger === 'manual' ? 'Issue it by hand'
                                    : ($cardTrigger === 'paid_approved' && !in_array((string) $m['pay_status'], ['paid', 'free'], true) ? 'Waiting for payment'
                                    : ($cardNeedsPhoto && empty($m['photo_path']) ? 'Waiting for a photo' : 'Ready to issue'));
                                echo partial('partials/admin/status-badge', ['status' => 'none', 'label' => 'Not issued', 'tone' => $why === 'Ready to issue' ? 'warning' : 'neutral']);
                                echo '<span class="cell-sub">' . e($why) . '</span>';
                            }
                            if (empty($m['anonymised_at']) && empty($m['photo_path']) && in_array($m['status'], ['pending', 'approved'], true)) {
                                echo '<span class="members-tags"><span class="badge badge--outline">No photo</span></span>';
                            }
                            ?>
                        </td>
                        <td data-label="" class="col-actions">
                            <div class="table__actions">
                                <?php if ($m['status'] === 'pending'): ?>
                                    <form method="post" action="<?= e(url('admin/members/' . $id . '/status')) ?>" data-confirm="Approve this application and issue a membership number?">
                                        <?= csrf_field() ?><input type="hidden" name="st[to]" value="approved"><input type="hidden" name="st[notify]" value="1">
                                        <button class="btn btn--accent btn--sm" type="submit" aria-label="Approve <?= e($m['full_name']) ?>"><?= icon('check', 'icon--sm') ?><span class="members-btn-text">Approve</span></button>
                                    </form>
                                <?php endif; ?>
                                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/members/' . $id)) ?>" aria-label="Open the record of <?= e($m['full_name']) ?>"><?= icon('eye', 'icon--sm') ?><span class="members-btn-text">Open</span></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
