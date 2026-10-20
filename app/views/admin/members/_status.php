<?php
/**
 * Review and status panel of one member. Only the transitions MembershipService allows from the current status are offered; the service
 * does the work (membership number, term dates, capacity check, audit entry). Vars: $member, $transitions (list of statuses), $type (current type | null).
 * Progressive enhancement: assets/js/admin-members.js reveals the fields that belong to the chosen action; without JavaScript every field is visible.
 */
use App\Services\Membership\MembershipService as MS;

$from = (string) $member['status'];
$id = (int) $member['id'];
$sb = MS::statusBadge($from);
$info = [
    'approved'  => $from === 'suspended'
        ? ['Reinstate', 'Make the membership active again. Not possible once the term has ended: mark it as expired first, then renew it.']
        : ['Approve', 'Accept the application. A membership number is issued and the term starts.'],
    'rejected'  => ['Reject', 'Decline the application. A reason is required and stays on the record.'],
    'cancelled' => $from === 'pending'
        ? ['Cancel the application', 'Close it, for example when the person withdrew. A reason is required.']
        : ['Cancel the membership', 'End the membership for good. A reason is required.'],
    'suspended' => ['Suspend', 'Pause the membership. A reason is required.'],
    'expired'   => ['Mark as expired', 'End the current term now. Renewing later brings the member back.'],
    'pending'   => ['Reopen', 'Put the application back in the review queue.'],
];
$chosen = old('st[to]', '');
$chosen = is_scalar($chosen) ? (string) $chosen : '';
$firstApproval = $from === 'pending';
$reasonStates = implode(' ', MS::REASON_REQUIRED);
$periodMonths = MS::PERIOD_MONTHS[(string) $member['fee_period']] ?? 0;
// what approving does to the membership card (setting membership_card_trigger; rules in CardService)
$cardRule = match (\App\Services\Membership\CardService::trigger()) {
    'paid_approved' => 'Membership card: it is issued and e-mailed automatically once the application is approved and the balance is paid or waived, whichever happens last. If the balance is already cleared, approving sends it now.',
    'approved' => 'Membership card: it is issued and e-mailed automatically as soon as the application is approved.',
    default => 'Membership card: cards are issued by hand on this site. Use "Issue card now" in the card panel after approving.',
};
?>
<section class="card members-status" id="status" aria-labelledby="status-title">
    <div class="card__header">
        <h2 class="card__title" id="status-title">Review and status</h2>
        <?= partial('partials/admin/status-badge', ['status' => $from, 'label' => $sb['label'], 'tone' => $sb['tone']]) ?>
    </div>
    <div class="card__body">
        <?php if (!empty($member['status_reason']) && in_array($from, MS::REASON_REQUIRED, true)): ?>
            <p class="members-reason"><strong>Reason on record:</strong> <?= e($member['status_reason']) ?></p>
        <?php endif; ?>

        <?php if ($transitions === []): ?>
            <p class="text-muted">No further status changes are possible.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/members/' . $id . '/status')) ?>" data-members-status
                  data-from="<?= e($from) ?>" data-reason-for="<?= e($reasonStates) ?>" data-period-months="<?= (int) $periodMonths ?>">
                <?= csrf_field() ?>
                <fieldset class="members-choice<?= has_error('st[to]') ? ' members-choice--error' : '' ?>">
                    <legend class="field__label">What would you like to do?</legend>
                    <div class="members-choice__list">
                        <?php foreach ($transitions as $to): [$label, $hint] = $info[$to] ?? [MS::STATUS_LABELS[$to] ?? label_from($to), '']; ?>
                            <label class="members-choice__item members-choice__item--<?= e($to) ?>">
                                <input type="radio" name="st[to]" value="<?= e($to) ?>"<?= $chosen === $to ? ' checked' : '' ?> data-status-to>
                                <span class="members-choice__label"><?= e($label) ?></span>
                                <span class="members-choice__hint"><?= e($hint) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (has_error('st[to]')): ?><div class="field__error" role="alert"><?= e((string) error('st[to]')) ?></div><?php endif; ?>
                </fieldset>

                <div class="members-status__fields">
                    <?php if ($firstApproval): ?>
                        <div class="members-approve" data-for="approved">
                            <div class="form-grid">
                                <?= partial('partials/form-field', ['name' => 'st[start_date]', 'id' => 'st-start', 'label' => 'Membership starts on', 'type' => 'date', 'value' => old('st[start_date]', ''),
                                    'help' => 'Leave empty for today.', 'attrs' => ['data-start-date' => true]]) ?>
                                <?= partial('partials/form-field', ['name' => 'st[expiry_date]', 'id' => 'st-expiry', 'label' => 'Membership ends on', 'type' => 'date', 'value' => old('st[expiry_date]', ''),
                                    'help' => $periodMonths > 0 ? 'Leave empty to follow the fee period (' . MS::PERIOD_LABELS_ADMIN[$member['fee_period']] . ').' : 'This membership type has no expiry (' . MS::PERIOD_LABELS_ADMIN[$member['fee_period']] . '). Leave empty to keep it that way.',
                                    'attrs' => ['data-expiry-date' => true]]) ?>
                            </div>
                            <p class="members-expiry-preview" data-expiry-preview aria-live="polite"></p>
                            <p class="members-note members-cardrule"><?= icon('credit-card', 'icon--sm') ?> <?= e($cardRule) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="members-reasonfield" data-for="<?= e($reasonStates) ?>">
                        <?= partial('partials/form-field', ['name' => 'st[reason]', 'id' => 'st-reason', 'label' => 'Reason', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 500, 'value' => old('st[reason]', ''),
                            'help' => 'Required when rejecting, suspending or cancelling. It is kept on the record (500 characters at most).', 'attrs' => ['data-reason' => true]]) ?>
                    </div>

                    <label class="check members-notify" data-for="approved rejected">
                        <input type="checkbox" name="st[notify]" value="1"<?= old('st[notify]', '') === '1' ? ' checked' : '' ?> data-notify>
                        <span>E-mail the member about this decision<?= $firstApproval ? '' : ' (only when approving or rejecting an application)' ?></span>
                    </label>
                </div>

                <div class="form-actions members-status__actions">
                    <button class="btn btn--primary" type="submit" data-status-submit data-default-label="Save status"><?= icon('check', 'icon--sm') ?> <span>Save status</span></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
