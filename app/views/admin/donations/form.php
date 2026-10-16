<?php
/**
 * Admin > record / edit a donation. Vars: $d (row|null), $currencies, $methods, $statusLabels, $receivedAt, $canDelete.
 */
use App\Core\FormState;

$editing = $d !== null;
$isOld = FormState::old() !== [];                       // true when the form is shown again after a validation error
$val = static fn(string $key, mixed $default = '') => old($key, $d[$key] ?? $default);
$amountDefault = '';
if ($editing && $d['amount'] !== null && $d['amount'] !== '') {
    $amountDefault = rtrim(rtrim(number_format((float) $d['amount'], 2, '.', ''), '0'), '.');
}
$action = $editing ? url('admin/donations/' . (int) $d['id']) : url('admin/donations');
$headerActions = [['label' => 'All donations', 'url' => url('admin/donations'), 'class' => 'btn--ghost', 'icon' => 'arrow-left']];
if ($editing && $d['status'] === 'received' && !empty($d['receipt_no'])) {
    array_unshift($headerActions, ['label' => 'Print receipt', 'url' => url('admin/donations/' . (int) $d['id'] . '/receipt'), 'class' => 'btn--outline', 'icon' => 'file-text', 'target' => '_blank']);
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', [
        'title'    => $editing ? 'Edit donation' : 'Record a donation',
        'subtitle' => $editing
            ? 'Recorded ' . format_datetime($d['created_at']) . ($d['source'] === 'pledge_form' ? ' from the pledge form' : ' by the team')
            : 'Add a gift that arrived outside the website: bank transfer, mobile money, cash, PayPal or GoFundMe.',
        'actions'  => $headerActions,
    ]) ?>

    <form method="post" action="<?= e($action) ?>" novalidate data-unsaved-guard>
        <?= csrf_field() ?>

        <section class="card">
            <div class="card__header"><h2 class="card__title">Donor</h2></div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'donor_name', 'label' => 'Name', 'value' => $val('donor_name'), 'maxlength' => 150, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_email', 'label' => 'Email', 'type' => 'email', 'value' => $val('donor_email'), 'maxlength' => 190, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_phone', 'label' => 'Phone', 'type' => 'tel', 'value' => $val('donor_phone'), 'maxlength' => 40, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'is_anonymous', 'label' => 'Donor wants to stay anonymous in public thank-yous', 'type' => 'checkbox', 'value' => $isOld ? old('is_anonymous', '') : ($d['is_anonymous'] ?? 0), 'class' => 'field--full']) ?>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header"><h2 class="card__title">Gift</h2></div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'amount', 'label' => 'Amount', 'value' => old('amount', $amountDefault),'attrs' => ['inputmode' => 'decimal'], 'help' => 'Numbers only, for example 150000 or 25.50. Required once the gift is received.']) ?>
                    <?= partial('partials/form-field', ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => $currencies, 'value' => strtoupper((string) old('currency', $d['currency'] ?? 'UGX')), 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'method', 'label' => 'How it was paid', 'type' => 'select', 'options' => $methods, 'value' => $val('method', 'other'), 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => $statusLabels, 'value' => $val('status', 'received'), 'required' => true, 'help' => 'Choosing "Received" stamps the date and issues a receipt number.']) ?>
                    <?= partial('partials/form-field', ['name' => 'received_at', 'label' => 'Date and time received', 'type' => 'datetime-local', 'value' => $receivedAt, 'help' => 'Leave empty to use the current time when the status is Received.']) ?>
                    <?= partial('partials/form-field', ['name' => 'reference', 'label' => 'Reference', 'value' => $val('reference'), 'maxlength' => 120, 'help' => 'Bank reference, mobile money transaction ID or similar.']) ?>
                    <?php if ($editing && !empty($d['receipt_no'])): ?>
                        <div class="field">
                            <span class="field__label">Receipt number</span>
                            <div class="inbox-readonly"><?= e($d['receipt_no']) ?></div>
                            <div class="field__help">Issued automatically. It never changes.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header"><h2 class="card__title">Notes</h2></div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => 'Message from the donor', 'type' => 'textarea', 'rows' => 4, 'value' => $val('message'), 'maxlength' => 5000, 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'admin_notes', 'label' => 'Private notes', 'type' => 'textarea', 'rows' => 4, 'value' => $val('admin_notes'), 'maxlength' => 5000, 'class' => 'field--full', 'help' => 'Only the team can see these notes.']) ?>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $editing ? 'Save changes' : 'Record donation' ?></button>
            <a class="btn btn--ghost" href="<?= e(url('admin/donations')) ?>">Cancel</a>
        </div>
    </form>

    <?php if ($editing && $canDelete): ?>
        <section class="card inbox-danger">
            <div class="card__body">
                <form method="post" action="<?= e(url('admin/donations/' . (int) $d['id'] . '/delete')) ?>" data-confirm="Delete this donation permanently? This cannot be undone.">
                    <?= csrf_field() ?>
                    <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete donation</button>
                </form>
            </div>
        </section>
    <?php endif; ?>
</div>
