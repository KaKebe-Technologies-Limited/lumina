<?php
/**
 * The payment fields, shared by "Record a payment" and "Edit payment". Fields are posted in a bucket (pay[...] for a new payment, pe<ID>[...]
 * for an existing one) so errors and old input of one form never show in another.
 * Vars: $bucket, $values (amount status method reference paid_at purpose currency term_start term_end notes), $methods, $idp (id prefix),
 * $notify (show the "e-mail a confirmation" tick box).
 */
use App\Services\Membership\MembershipService as MS;

$values = (array) $values;
$field = static function (string $key, string $label, array $extra = []) use ($bucket, $values, $idp): string {
    $name = $bucket . '[' . $key . ']';
    return partial('partials/form-field', ['name' => $name, 'id' => $idp . '-' . $key, 'label' => $label, 'value' => old($name, (string) ($values[$key] ?? ''))] + $extra);
};
$methodOptions = (array) $methods;
// "Renewal" rows are the charges added by "Start a new term": only an existing renewal row keeps that purpose, nobody can pick it for new money
$purposes = MS::PURPOSE_LABELS;
if ((string) ($values['purpose'] ?? '') !== 'renewal') {
    unset($purposes['renewal']);
}
$storedMethod = (string) ($values['method'] ?? '');
if ($storedMethod !== '' && !isset($methodOptions[$storedMethod])) {
    $methodOptions[$storedMethod] = MS::optionLabel('payment_methods', $storedMethod);      // a method the owner removed later stays selectable
}
?>
<div class="form-grid members-payform">
    <?= $field('amount', 'Amount', ['required' => true, 'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off'], 'help' => 'Numbers only, for example 50000 or 1500.50.']) ?>
    <?= $field('currency', 'Currency', ['maxlength' => 3, 'attrs' => ['autocomplete' => 'off'], 'help' => 'Three letters. Only amounts in the fee currency count towards the balance.']) ?>
    <?= $field('status', 'Status', ['type' => 'select', 'required' => true, 'options' => MS::PAYMENT_STATUS_LABELS,
        'help' => 'Received issues a receipt number. Waived forgives the amount. Pending is expected but not paid yet.', 'attrs' => ['data-pay-status' => true]]) ?>
    <?= $field('purpose', 'What it pays for', ['type' => 'select', 'options' => $purposes,
        'help' => isset($purposes['renewal']) ? 'This row is the renewal charge: its purpose cannot change.' : 'Money that arrives for a renewal is recorded as a membership fee.', 'attrs' => isset($purposes['renewal']) ? ['disabled' => true] : []]) ?>
    <?= $field('method', 'Payment method', ['type' => 'select', 'options' => $methodOptions, 'empty' => 'Not recorded']) ?>
    <?= $field('reference', 'Reference or transaction ID', ['maxlength' => 120, 'help' => 'Optional. Mobile money or bank reference.']) ?>
    <?= $field('paid_at', 'Date paid', ['type' => 'date', 'help' => 'Leave empty for today when the status is Received.']) ?>
    <?= $field('term_start', 'Covers from', ['type' => 'date', 'help' => 'Optional: the period this payment pays for.']) ?>
    <?= $field('term_end', 'Covers until', ['type' => 'date']) ?>
    <?= $field('notes', 'Note', ['maxlength' => 500, 'class' => 'field--full', 'help' => 'Optional, 500 characters at most. Visible to staff only.']) ?>
    <?php if (!empty($notify)): ?>
        <?= $field('notify', 'E-mail the member a payment confirmation (only for received payments)', ['type' => 'checkbox', 'class' => 'field--full']) ?>
    <?php endif; ?>
</div>
