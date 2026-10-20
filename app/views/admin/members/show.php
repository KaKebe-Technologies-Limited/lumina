<?php
/**
 * Admin > one member (application, status workflow, payments, renewal, e-mails, notes). OWNER: ADMIN agent. Contract: docs/MEMBERSHIP.md.
 * Vars: $member, $summary (MembershipService::paymentSummary), $payments (newest first), $received (received payments), $type (current type row | null),
 * $transitions (allowed next statuses), $methods (payment methods), $activity (audit rows), $canDelete, $canTypes, $hasPayments,
 * $card (CardService::summary), $checks (what a card needs), $proofBy (name of the staff member who last checked the proof), $gd (image library present).
 * PRIVACY: this screen shows everything the member submitted, so it is behind members.manage (admin + editor). Nothing here is logged.
 */
use App\Services\Membership\MembershipService as MS;

$id = (int) $member['id'];
$sb = MS::statusBadge((string) $member['status']);
$pb = MS::summaryBadge((string) $summary['status']);
$cur = (string) $summary['currency'];
$today = new DateTimeImmutable('today');
$expiry = !empty($member['expiry_date']) ? DateTimeImmutable::createFromFormat('!Y-m-d', (string) $member['expiry_date']) : false;
$days = $expiry ? (int) $today->diff($expiry)->format('%r%a') : null;
$balance = (float) $summary['balance'];

// KPI: membership card + the things staff still have to do for this member
$erased = !empty($member['anonymised_at']);
$cardState = (string) $card['state'];
$cardStale = $cardState === 'issued' && ($card['needs_reissue'] || $card['renewal_due']);
$waitShort = ['erased' => 'Personal data erased', 'not_approved' => 'Waiting for approval', 'unpaid' => 'Waiting for payment', 'photo_missing' => 'Waiting for a photo', 'no_number' => 'No membership number yet'];
if ($erased) {
    [$cardValue, $cardSub] = [$cardState === 'none' ? 'No card' : 'Revoked', 'Personal data erased'];
} elseif ($cardState === 'issued') {
    [$cardValue, $cardSub] = ['Issued, version ' . (int) $card['version'], $cardStale ? 'A new version is due' : ($card['emailed'] ? 'E-mailed ' . format_date((string) $card['emailed_at'], 'j M Y') : 'Not e-mailed yet')];
} elseif ($cardState === 'revoked') {
    [$cardValue, $cardSub] = ['Revoked', 'The QR code shows not valid'];
} else {
    [$cardValue, $cardSub] = ['Not issued', (string) $member['status'] === 'approved' ? ($waitShort[$card['waiting'][0] ?? ''] ?? 'Ready to issue') : 'Issued after approval'];
}
$todo = [];
if (!$erased && (string) ($member['proof_status'] ?? 'none') === 'received' && in_array((string) $member['status'], ['pending', 'approved'], true)) {
    $todo[] = ['#proof', 'A proof of payment is waiting for your check.'];
}
if (!$erased && (string) ($member['proof_status'] ?? 'none') === 'rejected') {
    $todo[] = ['#proof', 'You asked the member for a better copy of the proof of payment.'];
}
if ($cardStale) {
    $todo[] = ['#card', 'The issued card is out of date. Re-issue it (new version) to update it.'];
}
if (!$erased && $cardState === 'issued' && !$card['emailed']) {
    $todo[] = ['#card', 'The card was issued but not e-mailed to the member yet. Use "Re-send card e-mail".'];
}

// KPI: term
if (!empty($member['start_date'])) {
    $termValue = format_date($member['start_date'], 'j M Y') . ($expiry ? ' to ' . format_date($member['expiry_date'], 'j M Y') : ' onwards');
    if (!$expiry) {
        $termSub = 'No expiry date';
    } elseif ($member['status'] === 'approved') {
        $termSub = $days <= 0 ? 'Due for renewal now' : 'Expires in ' . $days . ' day' . ($days === 1 ? '' : 's');
    } elseif ($member['status'] === 'expired') {
        $termSub = 'Expired ' . abs((int) $days) . ' day' . (abs((int) $days) === 1 ? '' : 's') . ' ago';
    } else {
        $termSub = '';
    }
} else {
    $termValue = 'Not started';
    $termSub = 'The term begins when the application is approved';
}
// KPI: money
if ($summary['status'] === 'tbd') {
    $moneyValue = 'Fee not set';
    $moneySub = 'Set it under "Edit details"';
} elseif ($summary['status'] === 'free') {
    $moneyValue = 'No fee';
    $moneySub = 'Nothing to pay';
} elseif ($balance > 0) {
    $moneyValue = MS::formatMoney($balance, $cur);
    $moneySub = 'Still owing';
} else {
    $moneyValue = 'Paid in full';
    $moneySub = $balance < 0 ? 'Credit of ' . MS::formatMoney(-$balance, $cur) : '';
}

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-members-card.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-members">
    <?= partial('partials/admin/page-header', [
        'title'    => (string) $member['full_name'],
        'subtitle' => $member['application_ref'] . ' - ' . $member['type_name'] . ' - applied ' . format_date($member['created_at'], 'j F Y'),
        'actions'  => array_values(array_filter([
            empty($member['anonymised_at']) ? ['label' => 'Edit details', 'url' => url('admin/members/' . $id, ['edit' => 1]), 'class' => 'btn--outline', 'icon' => 'edit'] : null,
            ['label' => 'All members', 'url' => url('admin/members'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'],
        ])),
    ]) ?>

    <section class="members-summary" aria-label="Summary">
        <div class="members-stat">
            <span class="members-stat__label">Status</span>
            <span class="members-stat__value"><?= partial('partials/admin/status-badge', ['status' => $member['status'], 'label' => $sb['label'], 'tone' => $sb['tone']]) ?></span>
            <?php if (!empty($member['status_changed_at'])): ?><span class="members-stat__sub">since <?= e(format_date($member['status_changed_at'], 'j M Y')) ?></span><?php endif; ?>
        </div>
        <div class="members-stat">
            <span class="members-stat__label">Membership number</span>
            <?php if (!empty($member['membership_number'])): ?>
                <span class="members-stat__value members-ref"><?= e($member['membership_number']) ?></span>
            <?php else: ?>
                <span class="members-stat__value members-stat__value--muted">Not issued yet</span>
                <span class="members-stat__sub">Issued when the application is approved</span>
            <?php endif; ?>
        </div>
        <div class="members-stat">
            <span class="members-stat__label">Membership term</span>
            <span class="members-stat__value members-stat__value--small"><?= e($termValue) ?></span>
            <?php if ($termSub !== ''): ?><span class="members-stat__sub<?= $member['status'] === 'approved' && $days !== null && $days <= 30 ? ' members-due' : '' ?>"><?= e($termSub) ?></span><?php endif; ?>
        </div>
        <a class="members-stat members-stat--link" href="#payments">
            <span class="members-stat__label">Fees</span>
            <span class="members-stat__value members-stat__value--small"><?= e($moneyValue) ?></span>
            <span class="members-stat__sub"><?= e($moneySub) ?> <span class="visually-hidden">(go to the payments)</span></span>
        </a>
        <a class="members-stat members-stat--link" href="#card">
            <span class="members-stat__label">Membership card</span>
            <span class="members-stat__value members-stat__value--small"><?= e($cardValue) ?></span>
            <span class="members-stat__sub"><?= e($cardSub) ?> <span class="visually-hidden">(go to the card)</span></span>
        </a>
    </section>

    <?php if ($todo !== []): ?>
        <ul class="members-todo" aria-label="Things to do for this member">
            <?php foreach ($todo as [$href, $text]): ?><li><?= icon('alert-circle', 'icon--sm') ?> <a href="<?= e($href) ?>"><?= e($text) ?></a></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <nav class="members-jump" aria-label="On this page">
        <a href="#status">Review and status</a>
        <a href="#details">Application</a>
        <a href="#proof">Photo and proof of payment</a>
        <a href="#payments">Fees and payments</a>
        <a href="#card">Membership card</a>
        <a href="#activity">Activity</a>
    </nav>

    <?= partial('admin/members/_status', ['member' => $member, 'transitions' => $transitions, 'type' => $type]) ?>

    <div class="members-layout">
        <div class="members-main">
            <?= partial('admin/members/_details', ['member' => $member]) ?>
            <?= partial('admin/members/_proof', ['member' => $member, 'summary' => $summary, 'proofBy' => $proofBy]) ?>
            <?= partial('admin/members/_payments', [
                'member' => $member, 'summary' => $summary, 'payments' => $payments, 'methods' => $methods, 'type' => $type, 'canTypes' => $canTypes, 'pb' => $pb,
            ]) ?>
            <?= partial('admin/members/_renewal', ['member' => $member, 'summary' => $summary]) ?>
            <?= partial('admin/members/_photo', ['member' => $member, 'card' => $card, 'gd' => $gd]) ?>
            <?= partial('admin/members/_card', ['member' => $member, 'card' => $card, 'checks' => $checks, 'gd' => $gd]) ?>
            <section class="card" id="activity">
                <div class="card__header"><h2 class="card__title">Activity</h2></div>
                <div class="card__body">
                    <?php if ($activity === []): ?>
                        <p class="text-muted">No changes have been recorded for this member yet.</p>
                    <?php else: ?>
                        <ol class="members-activity">
                            <?php foreach ($activity as $a): $who = trim((string) preg_replace('/\s*<[^>]*>\s*$/', '', (string) $a['user_name'])); ?>
                                <li>
                                    <span class="members-activity__what"><?= e($a['summary']) ?></span>
                                    <span class="members-activity__when"><?= e(format_datetime($a['created_at'], 'j M Y, H:i')) ?><?= $who !== '' && $who !== 'system' ? ' - ' . e($who) : '' ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="members-aside" aria-label="Contact, notes and e-mails">
            <?= partial('admin/members/_aside', ['member' => $member, 'received' => $received, 'methods' => $methods, 'canDelete' => $canDelete, 'hasPayments' => $hasPayments, 'erasable' => $erasable]) ?>
        </aside>
    </div>
</div>
