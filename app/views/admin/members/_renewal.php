<?php
/**
 * Renewal: start a new term for an approved or expired member. The service moves the expiry date forward (from the current expiry while it is
 * still ahead, otherwise from today or the date given), re-activates an expired member and adds a PENDING renewal charge to the payments.
 * Vars: $member, $summary.
 */
use App\Services\Membership\MembershipService as MS;
use App\Services\Membership\OnceToken;

$id = (int) $member['id'];
$status = (string) $member['status'];
$period = (string) $member['fee_period'];
$renewable = isset(MS::PERIOD_MONTHS[$period]);
$show = in_array($status, ['approved', 'expired'], true);
$cur = (string) $summary['currency'];
$usual = $member['fee_amount'] === null ? 'No fee is set' : MS::formatMoney($member['fee_amount'], $cur);
$unit = ['monthly' => 'month', 'quarterly' => 'quarter (three months)', 'annual' => 'year'][$period] ?? 'term';
if (!$show) {
    return;
}
?>
<section class="card members-renewal" id="renewal" aria-labelledby="renewal-title">
    <div class="card__header"><h2 class="card__title" id="renewal-title">Renewal</h2></div>
    <div class="card__body">
        <?php if (!$renewable): ?>
            <p class="text-muted">This membership is <strong><?= e(strtolower(MS::PERIOD_LABELS_ADMIN[$period] ?? $period)) ?></strong>, so it has no renewal term. Nothing needs to be renewed.</p>
        <?php else: ?>
            <p>
                <?php if (!empty($member['expiry_date'])): ?>
                    <?= $status === 'expired' ? 'The term ended on' : 'The current term runs until' ?> <strong><?= e(format_date($member['expiry_date'], 'j F Y')) ?></strong>.
                <?php endif; ?>
                A new term adds <strong>one <?= e($unit) ?></strong>
                and a <em>pending</em> renewal charge (<?= e($usual) ?> unless you enter another amount). Record the payment when the money arrives.
            </p>
            <details class="members-add"<?= array_filter(array_keys(errors()), static fn(string $k): bool => str_starts_with($k, 'rn[')) ? ' open' : '' ?>>
                <summary class="btn btn--outline btn--sm"><?= icon('refresh', 'icon--sm') ?> Start a new term</summary>
                <form method="post" action="<?= e(url('admin/members/' . $id . '/renew')) ?>" data-confirm="Start a new term for this member and add a pending renewal charge?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="rn[_once]" value="<?= e(OnceToken::issue('renew:' . $id)) ?>">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'rn[start_date]', 'id' => 'rn-start', 'label' => 'New term starts on', 'type' => 'date', 'value' => old('rn[start_date]', ''),
                            'help' => 'Leave empty to continue from the current expiry date (or from today if the term has ended). While the current term is still running the new one cannot start before it ends.']) ?>
                        <?= partial('partials/form-field', ['name' => 'rn[amount]', 'id' => 'rn-amount', 'label' => 'Renewal amount (' . $cur . ')', 'value' => old('rn[amount]', ''),
                            'help' => 'Leave empty for the usual fee: ' . $usual . '.', 'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off']]) ?>
                        <?= partial('partials/form-field', ['name' => 'rn[notes]', 'id' => 'rn-notes', 'label' => 'Note', 'value' => old('rn[notes]', ''), 'maxlength' => 500, 'class' => 'field--full',
                            'help' => 'Optional, visible to staff only.']) ?>
                    </div>
                    <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('refresh', 'icon--sm') ?> Start the new term</button></div>
                </form>
            </details>
        <?php endif; ?>
    </div>
</section>
