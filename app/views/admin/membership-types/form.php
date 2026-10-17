<?php
/**
 * Admin > create / edit a membership type = the FEE CONFIGURATION. OWNER: ADMIN agent. Contract: docs/MEMBERSHIP.md. Admin only (members.types).
 * Vars: $type (row; id null when new), $isNew, $members (members of this type), $taken (capacity), $feeMode (tba | free | amount), $groups (fees existing
 * members are on), $periods, $currencies, $preview (wording for the live preview).
 * The fee is a three-way choice, so nobody has to know that "empty means to be announced": the controller turns it into the service's convention.
 * The preview below the fee reads exactly like the website (MembershipService::feeLabel); admin-members.js keeps it live while you type.
 */
use App\Services\Membership\MembershipService as MS;

$t = $type;
$plain = static fn($v): string => $v === null || $v === '' ? '' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
$name = (string) old('name', (string) $t['name']);
$period = (string) old('period', (string) $t['period']);
$mode = (string) old('fee_mode', $feeMode);
$mode = in_array($mode, ['tba', 'free', 'amount'], true) ? $mode : 'tba';
$amount = (string) old('fee_amount', $mode === 'amount' ? $plain($t['fee_amount']) : '');
$joiningRaw = (string) old('joining_fee', $plain($t['joining_fee']));
$currency = strtoupper((string) old('currency', (string) $t['currency']));
$eligibility = (string) old('eligibility', (string) ($t['eligibility'] ?? ''));
$active = (string) old('is_active', (string) $t['is_active']) !== '0';

// what the preview shows at first (the script keeps it live; without JavaScript this is what you get on load)
$parsed = MS::parseMoney($amount);
$feeText = $mode === 'tba' ? $preview['tba'] : ($mode === 'free' || ($parsed !== null && (float) $parsed <= 0) ? $preview['free'] : ($parsed === null ? $preview['tba'] : MS::feeLabel($parsed, $currency, $period)));
$joinParsed = MS::parseMoney($joiningRaw);
$joiningText = $joinParsed !== null && (float) $joinParsed > 0 ? str_replace('{amount}', MS::formatMoney($joinParsed, $currency), $preview['joining']) : '';
$termText = static fn(string $p): string => match ($p) {
    'monthly'   => 'A membership lasts 1 month from the day it is approved, then it is due for renewal.',
    'quarterly' => 'A membership lasts 3 months from the day it is approved, then it is due for renewal.',
    'annual'    => 'A membership lasts 1 year from the day it is approved, then it is due for renewal.',
    'lifetime'  => 'Paid once. A lifetime membership never expires.',
    default     => 'Paid once. The membership does not have an expiry date.',
};
$periodHints = [];
foreach (array_keys($periods) as $p) {
    $periodHints[$p] = $termText($p);
}
$action = $isNew ? url('admin/membership-types') : url('admin/membership-types/' . (int) $t['id']);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-members admin-membership-types">
    <?= partial('partials/admin/page-header', [
        'title'    => $isNew ? 'New membership type' : 'Edit membership type',
        'subtitle' => $isNew ? 'Choose a name, say what it costs and how often, and decide whether it is offered on the website.' : (string) $t['name'],
        'actions'  => [['label' => 'All types', 'url' => url('admin/membership-types'), 'class' => 'btn--ghost', 'icon' => 'arrow-left']],
    ]) ?>

    <div class="members-typelayout">
        <form method="post" action="<?= e($action) ?>" class="card members-edit" data-unsaved-guard data-fee-form
              data-fee-config="<?= e((string) json_encode($preview)) ?>" data-period-hints="<?= e((string) json_encode($periodHints)) ?>">
            <?= csrf_field() ?>
            <?php if (errors() !== []): ?>
                <div class="alert alert--error" role="alert">
                    <?= icon('alert-circle') ?>
                    <div class="alert__body"><strong class="alert__title">Please check the fields marked below.</strong></div>
                </div>
            <?php endif; ?>
            <div class="card__body">
                <section class="form-section" aria-labelledby="sec-basics">
                    <h2 class="form-section__title" id="sec-basics">Name and description</h2>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'maxlength' => 150, 'value' => $name,
                            'help' => 'What visitors see, for example "Individual Member".', 'attrs' => ['data-fee-name' => true]]) ?>
                        <?= partial('partials/form-field', ['name' => 'slug', 'label' => 'Short name for links (optional)', 'maxlength' => 120, 'value' => old('slug', (string) $t['slug']),
                            'help' => 'Lower case letters and dashes. Filled in from the name; you rarely need to touch it.', 'attrs' => ['data-slug-source' => '#f-name']]) ?>
                        <?= partial('partials/form-field', ['name' => 'description', 'label' => 'Description (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'class' => 'field--full',
                            'value' => old('description', (string) ($t['description'] ?? '')), 'help' => 'Shown on the type\'s card on the Membership page.']) ?>
                        <?= partial('partials/form-field', ['name' => 'eligibility', 'label' => 'Who can join (optional)', 'type' => 'textarea', 'rows' => 2, 'maxlength' => 1000, 'class' => 'field--full',
                            'value' => $eligibility, 'help' => 'A short eligibility note, for example an age range. Write only what the foundation has decided.', 'attrs' => ['data-fee-eligibility' => true]]) ?>
                    </div>
                </section>

                <section class="form-section" id="fee" aria-labelledby="sec-fee">
                    <h2 class="form-section__title" id="sec-fee">Fee and billing</h2>
                    <p class="form-section__help">This is the fee visitors see and the amount a new application is charged. <strong>Changing it later only affects new applications</strong>: every member keeps the fee they were quoted.</p>

                    <div class="form-grid">
                        <div class="field<?= has_error('period') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-period">Billing period <span class="field__req" aria-hidden="true">*</span></label>
                            <select class="select" id="f-period" name="period" required data-fee-period aria-describedby="f-period-help<?= has_error('period') ? ' f-period-error' : '' ?>">
                                <?php foreach ($periods as $k => $label): ?><option value="<?= e($k) ?>"<?= $period === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                            </select>
                            <div class="field__help" id="f-period-help" data-period-hint><?= e($termText($period)) ?></div>
                            <?php if (has_error('period')): ?><div class="field__error" id="f-period-error" role="alert"><?= e((string) error('period')) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <fieldset class="members-choice members-feemode<?= has_error('fee_amount') ? ' members-choice--error' : '' ?>">
                        <legend class="field__label">What does this membership cost?</legend>
                        <div class="members-choice__list">
                            <label class="members-choice__item">
                                <input type="radio" name="fee_mode" value="tba"<?= $mode === 'tba' ? ' checked' : '' ?> data-fee-mode>
                                <span class="members-choice__label"><?= e($preview['tba']) ?></span>
                                <span class="members-choice__hint">People can already apply. Use this until the fee has been decided.</span>
                            </label>
                            <label class="members-choice__item">
                                <input type="radio" name="fee_mode" value="free"<?= $mode === 'free' ? ' checked' : '' ?> data-fee-mode>
                                <span class="members-choice__label"><?= e($preview['free']) ?></span>
                                <span class="members-choice__hint">No payment is expected.</span>
                            </label>
                            <label class="members-choice__item">
                                <input type="radio" name="fee_mode" value="amount"<?= $mode === 'amount' ? ' checked' : '' ?> data-fee-mode>
                                <span class="members-choice__label">A set amount</span>
                                <span class="members-choice__hint">Enter the fee below.</span>
                            </label>
                        </div>
                        <?php if (has_error('fee_amount')): ?><div class="field__error" role="alert"><?= e((string) error('fee_amount')) ?></div><?php endif; ?>
                    </fieldset>

                    <div class="form-grid members-feegrid" data-fee-amount-box>
                        <?= partial('partials/form-field', ['name' => 'fee_amount', 'label' => 'Fee amount', 'value' => $amount, 'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off', 'data-fee-amount' => true],
                            'help' => 'Numbers only, for example 50000 or 1500.50. No currency sign.']) ?>
                        <div class="field<?= has_error('currency') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-currency">Currency</label>
                            <input class="input members-currency" id="f-currency" name="currency" value="<?= e($currency) ?>" maxlength="3" list="members-currencies" autocomplete="off"
                                   data-fee-currency aria-describedby="f-currency-help<?= has_error('currency') ? ' f-currency-error' : '' ?>"<?= has_error('currency') ? ' aria-invalid="true"' : '' ?>>
                            <datalist id="members-currencies"><?php foreach ($currencies as $c): ?><option value="<?= e($c) ?>"></option><?php endforeach; ?></datalist>
                            <div class="field__help" id="f-currency-help">Three letters. New types start with <?= e(MS::defaultCurrency()) ?> (change it in the Membership settings).</div>
                            <?php if (has_error('currency')): ?><div class="field__error" id="f-currency-error" role="alert"><?= e((string) error('currency')) ?></div><?php endif; ?>
                        </div>
                        <?= partial('partials/form-field', ['name' => 'joining_fee', 'label' => 'Joining fee (optional)', 'value' => $joiningRaw, 'class' => 'field--full',
                            'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off', 'data-fee-joining' => true],
                            'help' => 'A one-off registration fee charged on top of the fee above. Leave empty or 0 for none.']) ?>
                    </div>

                    <div class="members-preview" data-fee-preview aria-live="polite">
                        <p class="members-preview__label">How visitors will read it</p>
                        <div class="members-preview__card">
                            <h3 class="members-preview__name" data-preview-name><?= e($name !== '' ? $name : 'Membership type') ?></h3>
                            <p class="members-preview__fee<?= $mode === 'tba' ? ' is-tba' : '' ?>" data-preview-fee><?= e($feeText) ?></p>
                            <p class="members-preview__joining" data-preview-joining<?= $joiningText === '' ? ' hidden' : '' ?>><?= e($joiningText) ?></p>
                            <p class="members-preview__elig" data-preview-eligibility<?= trim($eligibility) === '' ? ' hidden' : '' ?>><?= e($eligibility) ?></p>
                            <p class="members-preview__term" data-preview-term><?= e($termText($period)) ?></p>
                        </div>
                    </div>
                </section>

                <section class="form-section" aria-labelledby="sec-avail">
                    <h2 class="form-section__title" id="sec-avail">Availability</h2>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'max_members', 'label' => 'Maximum places (optional)', 'type' => 'number', 'value' => old('max_members', (string) ($t['max_members'] ?? '')), 'min' => 1, 'step' => 1,
                            'help' => $isNew ? 'Leave empty for no limit. Pending and approved members use a place.' : 'Leave empty for no limit. ' . (int) $taken['taken'] . ' place' . ($taken['taken'] === 1 ? ' is' : 's are') . ' used now (pending and approved members).']) ?>
                        <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Order on the website', 'type' => 'number', 'value' => old('sort_order', (string) $t['sort_order']), 'step' => 1,
                            'help' => 'Smaller numbers come first.']) ?>
                        <div class="field--full">
                            <?= partial('admin/_content/switch', ['name' => 'is_active', 'id' => 'f-is_active', 'label' => 'Offer this type on the website and in the registration form', 'checked' => $active,
                                'help' => 'Switch it off to hide the type without deleting it. Existing members are not affected.']) ?>
                        </div>
                    </div>
                </section>
            </div>
            <div class="form-actions">
                <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Create type' : 'Save changes' ?></button>
                <a class="btn btn--ghost" href="<?= e(url('admin/membership-types')) ?>">Cancel</a>
                <span class="required-note">Fields marked <span aria-hidden="true">*</span><span class="visually-hidden">with an asterisk</span> are required.</span>
            </div>
        </form>

        <aside class="members-typeaside" aria-label="About fee changes">
            <section class="card">
                <div class="card__header"><h2 class="card__title">When you change the fee</h2></div>
                <div class="card__body">
                    <ul class="members-list">
                        <li>The website and the registration form show the new fee straight away.</li>
                        <li>Only <strong>new applications</strong> are charged the new fee.</li>
                        <li>Members who already applied keep the fee they were quoted, so past records and receipts stay correct.</li>
                        <li>An administrator can still correct one member's fee on that member's "Edit details" screen.</li>
                    </ul>
                </div>
            </section>
            <?php if (!$isNew): ?>
                <section class="card">
                    <div class="card__header"><h2 class="card__title">Members of this type</h2></div>
                    <div class="card__body">
                        <?php if ($members === 0): ?>
                            <p class="text-muted">Nobody has applied for this type yet, so you can change anything or delete it.</p>
                        <?php else: ?>
                            <p><a href="<?= e(url('admin/members', ['type' => (int) $t['id']])) ?>"><?= $members ?> member<?= $members === 1 ? '' : 's' ?></a> belong to this type, on these fees:</p>
                            <ul class="members-list">
                                <?php foreach ($groups as $g): $j = MS::joiningFeeLabel($g); ?>
                                    <li><strong><?= (int) $g['n'] ?></strong> on <?= e(MS::feeLabel($g['fee_amount'], (string) $g['fee_currency'], (string) $g['fee_period'])) ?><?= $j !== '' ? ' (' . e($j) . ')' : '' ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </section>
                <section class="card card--danger">
                    <div class="card__header"><h2 class="card__title">Delete this type</h2></div>
                    <div class="card__body">
                        <?php if ($members > 0): ?>
                            <p class="text-muted">A type with members cannot be deleted. Switch it off above instead: visitors stop seeing it and nobody new can choose it.</p>
                        <?php else: ?>
                            <p class="text-muted">This removes the type for good. It cannot be undone.</p>
                            <form method="post" action="<?= e(url('admin/membership-types/' . (int) $t['id'] . '/delete')) ?>" data-confirm="<?= e('Delete the membership type "' . $t['name'] . '"? This cannot be undone.') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete type</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</div>
