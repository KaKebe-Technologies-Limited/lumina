<?php
/**
 * Fees and payments of one member: the amount due (from the fee snapshot taken at application time), what was received or waived, the balance,
 * every payment row (receipt, edit, delete while it has no receipt) and the "record a payment" form.
 * Vars: $member, $summary (MembershipService::paymentSummary), $payments (newest first), $methods, $type (current type | null), $canTypes, $pb (summary badge).
 * Money rules are in MembershipService (balance = charges - received - waived; a received payment gets its receipt number at once).
 */
use App\Services\Membership\MembershipService as MS;
use App\Services\Membership\OnceToken;

$id = (int) $member['id'];
$cur = (string) $summary['currency'];
$money = static fn(float $n): string => MS::formatMoney($n, $cur);
$joining = MS::cents($member['joining_fee'] ?? 0) / 100;
$fee = MS::cents($member['fee_amount'] ?? 0) / 100;
$balance = (float) $summary['balance'];
$plain = static fn(float $n): string => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');

// is the type's CURRENT fee different from what this member was quoted?
$typeDiffers = false;
if ($type !== null) {
    $snap = MS::feeSnapshot($type);
    foreach (['fee_amount', 'joining_fee', 'fee_currency', 'fee_period'] as $k) {
        if ((string) ($snap[$k] ?? '') !== (string) ($member[$k] ?? '')) {
            $typeDiffers = true;
        }
    }
}
$newValues = [
    'amount' => $balance > 0 ? $plain($balance) : '', 'currency' => $cur, 'status' => 'received', 'purpose' => 'membership', 'method' => (string) $member['payment_method'],
    'reference' => '', 'paid_at' => '', 'term_start' => '', 'term_end' => '', 'notes' => '',
];
$hasPayError = array_filter(array_keys(errors()), static fn(string $k): bool => str_starts_with($k, 'pay['));
?>
<section class="card members-payments" id="payments" aria-labelledby="payments-title">
    <div class="card__header">
        <h2 class="card__title" id="payments-title">Fees and payments</h2>
        <?= partial('partials/admin/status-badge', ['status' => $summary['status'], 'label' => $pb['label'], 'tone' => $pb['tone']]) ?>
    </div>
    <div class="card__body">
        <dl class="members-ledger">
            <?php if ($joining > 0): ?><div><dt>Joining fee</dt><dd><?= e($money($joining)) ?></dd></div><?php endif; ?>
            <div><dt>Membership fee<?= $fee > 0 ? ' (' . e(strtolower(MS::PERIOD_LABELS_ADMIN[$member['fee_period']] ?? '')) . ')' : '' ?></dt>
                <dd><?= $member['fee_amount'] === null ? '<span class="text-muted">Not set</span>' : e($money($fee)) ?></dd></div>
            <?php if ($summary['renewal_due'] > 0): ?><div><dt>Renewals</dt><dd><?= e($money((float) $summary['renewal_due'])) ?></dd></div><?php endif; ?>
            <div class="members-ledger__total"><dt>Total due</dt><dd><?= e($money((float) $summary['charges'])) ?></dd></div>
            <div><dt>Received</dt><dd><?= e($money((float) $summary['paid'])) ?></dd></div>
            <?php if ($summary['waived'] > 0): ?><div><dt>Waived</dt><dd><?= e($money((float) $summary['waived'])) ?></dd></div><?php endif; ?>
            <div class="members-ledger__balance<?= $balance > 0 ? ' is-owing' : '' ?>"><dt><?= $balance < 0 ? 'Credit' : 'Balance' ?></dt><dd><?= e($money(abs($balance))) ?></dd></div>
        </dl>
        <?php foreach ($summary['other_currencies'] as $oc => $amount): ?>
            <p class="members-note"><?= icon('info', 'icon--sm') ?> <?= e(MS::formatMoney($amount, (string) $oc)) ?> was also received in another currency. It is not counted in the balance above.</p>
        <?php endforeach; ?>

        <?php if ($member['fee_amount'] === null && $summary['status'] === 'tbd'): ?>
            <p class="members-note members-note--warn"><?= icon('alert-triangle', 'icon--sm') ?> This member applied while the fee was "to be announced", so no fee is set on their record yet.
                <?php if ($canTypes): ?><a href="<?= e(url('admin/members/' . $id, ['edit' => 1]) . '#fee') ?>">Set the fee for this member</a>.<?php else: ?>An administrator can set it under "Edit details".<?php endif; ?></p>
        <?php elseif ($typeDiffers && $type !== null): ?>
            <p class="members-note"><?= icon('info', 'icon--sm') ?> This member keeps the fee they were quoted when they applied (<?= e(MS::memberFeeLabel($member)) ?>).
                The type "<?= e($type['name']) ?>" now reads "<?= e(MS::typeFeeLabel($type)) ?>". Changing a type never changes existing members.</p>
        <?php endif; ?>

        <h3 class="members-group">Payments</h3>
        <?php if ($payments === []): ?>
            <p class="text-muted">No payments have been recorded yet.</p>
        <?php else: ?>
            <div class="members-paylist">
                <?php foreach ($payments as $p):
                    $pid = (int) $p['id'];
                    $badge = MS::paymentBadge((string) $p['status']);
                    $bucket = 'pe' . $pid;
                    $editError = array_filter(array_keys(errors()), static fn(string $k): bool => str_starts_with($k, $bucket . '['));
                    $methodLabel = (string) $p['method'] !== '' ? MS::optionLabel('payment_methods', (string) $p['method']) : '';
                    $bits = array_filter([
                        MS::PURPOSE_LABELS[$p['purpose']] ?? label_from((string) $p['purpose']),
                        $methodLabel,
                        !empty($p['reference']) ? 'Ref. ' . $p['reference'] : '',
                        !empty($p['paid_at']) ? format_date($p['paid_at'], 'j M Y') : 'Created ' . format_date($p['created_at'], 'j M Y'),
                    ], static fn($s): bool => $s !== '');
                    $values = [
                        'amount' => $plain((float) $p['amount']), 'currency' => (string) $p['currency'], 'status' => (string) $p['status'], 'purpose' => (string) $p['purpose'],
                        'method' => (string) $p['method'], 'reference' => (string) ($p['reference'] ?? ''), 'paid_at' => !empty($p['paid_at']) ? substr((string) $p['paid_at'], 0, 10) : '',
                        'term_start' => (string) ($p['term_start'] ?? ''), 'term_end' => (string) ($p['term_end'] ?? ''), 'notes' => (string) ($p['notes'] ?? ''),
                    ];
                    ?>
                    <article class="members-pay members-pay--<?= e($p['status']) ?>" id="pay-<?= $pid ?>">
                        <div class="members-pay__row">
                            <div class="members-pay__main">
                                <div class="members-pay__top">
                                    <strong class="members-pay__amount"><?= e(MS::formatMoney($p['amount'], (string) $p['currency'])) ?></strong>
                                    <?= partial('partials/admin/status-badge', ['status' => $p['status'], 'label' => $badge['label'], 'tone' => $badge['tone']]) ?>
                                </div>
                                <p class="members-pay__meta"><?= e(implode(' - ', $bits)) ?></p>
                                <?php if (!empty($p['receipt_number']) || !empty($p['term_start'])): ?>
                                    <p class="members-pay__meta">
                                        <?php if (!empty($p['receipt_number'])): ?>Receipt <strong class="members-ref"><?= e($p['receipt_number']) ?></strong><?php endif; ?>
                                        <?php if (!empty($p['term_start']) || !empty($p['term_end'])): ?><?= !empty($p['receipt_number']) ? ' - ' : '' ?>Covers <?= e(format_date($p['term_start'], 'j M Y')) ?><?= !empty($p['term_end']) ? ' to ' . e(format_date($p['term_end'], 'j M Y')) : '' ?><?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($p['notes'])): ?><p class="members-pay__meta members-pay__notes"><?= e($p['notes']) ?></p><?php endif; ?>
                            </div>
                            <div class="members-pay__actions">
                                <?php if ($p['status'] === 'received' && !empty($p['receipt_number'])): ?>
                                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/members/' . $id . '/payments/' . $pid . '/receipt')) ?>" target="_blank" rel="noopener"><?= icon('file-text', 'icon--sm') ?> Receipt</a>
                                <?php endif; ?>
                                <?php if ($p['status'] === 'pending'): ?>
                                    <button class="btn btn--accent btn--sm" type="button" data-pay-receive="<?= $pid ?>" hidden><?= icon('check', 'icon--sm') ?> Mark received</button>
                                <?php endif; ?>
                                <?php if (empty($p['receipt_number'])): ?>
                                    <form method="post" action="<?= e(url('admin/members/' . $id . '/payments/' . $pid . '/delete')) ?>" data-confirm="Delete this payment row? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Delete this <?= e(strtolower($badge['label'])) ?> payment of <?= e(MS::formatMoney($p['amount'], (string) $p['currency'])) ?>"><?= icon('trash', 'icon--sm') ?> Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <details class="members-pay__edit" id="pay-edit-<?= $pid ?>"<?= $editError ? ' open' : '' ?>>
                            <summary>Edit this payment</summary>
                            <form method="post" action="<?= e(url('admin/members/' . $id . '/payments/' . $pid)) ?>">
                                <?= csrf_field() ?>
                                <?= partial('admin/members/_payment-form', ['bucket' => $bucket, 'values' => $values, 'methods' => $methods, 'idp' => 'pe' . $pid, 'notify' => true]) ?>
                                <?php if (!empty($p['receipt_number'])): ?>
                                    <p class="members-note"><?= icon('lock', 'icon--sm') ?> Receipt <?= e($p['receipt_number']) ?> was issued. The number never changes and this payment cannot be deleted; if the amount changes, tell the member who holds the old receipt.</p>
                                <?php endif; ?>
                                <div class="form-actions"><button class="btn btn--primary btn--sm" type="submit"><?= icon('save', 'icon--sm') ?> Save payment</button></div>
                            </form>
                        </details>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <details class="members-add" id="add-payment"<?= $hasPayError ? ' open' : '' ?>>
            <summary class="btn btn--primary btn--sm"><?= icon('plus', 'icon--sm') ?> Record a payment</summary>
            <form method="post" action="<?= e(url('admin/members/' . $id . '/payments')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="pay[_once]" value="<?= e(OnceToken::issue('pay:' . $id)) ?>">
                <p class="members-note"><?= icon('info', 'icon--sm') ?> Use <strong>Received</strong> when the money has arrived (a receipt number is issued), <strong>Pending</strong> for a payment you expect, and <strong>Waived</strong> to forgive an amount.</p>
                <?= partial('admin/members/_payment-form', ['bucket' => 'pay', 'values' => $newValues, 'methods' => $methods, 'idp' => 'pay', 'notify' => true]) ?>
                <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save payment</button></div>
            </form>
        </details>
    </div>
</section>
