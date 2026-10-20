<?php
/**
 * Admin > correct a member's details (same rules as the public form, minus the consents). OWNER: ADMIN agent.
 * Saved by MembershipApplication::update(): the status, the dates and the payments have their own actions on the member screen.
 * Vars: $member, $lists (option lists incl. an answer the owner removed later), $interests (key => label), $chosen (stored interest keys),
 * $collectsId, $canFee (administrators may change this member's own fee), $type (current type row | null), $periods.
 * PRIVACY: personal data; behind members.manage.
 */
use App\Services\Membership\MembershipService as MS;

$m = $member;
$id = (int) $m['id'];
$plain = static fn($v): string => $v === null || $v === '' ? '' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
$f = static fn(string $name, string $label, array $extra = []): string => partial('partials/form-field', [
    'name' => $name, 'label' => $label, 'value' => old($name, (string) ($m[$name] ?? '')),
] + $extra);
$selected = old('interests', $chosen);
$selected = is_array($selected) ? array_map('strval', $selected) : [];
$age = MS::ageOn(old('date_of_birth', (string) $m['date_of_birth']));
$typeFee = null;
if ($type !== null) {
    $snap = MS::feeSnapshot($type);
    $typeFee = [
        'fee_amount' => $plain($snap['fee_amount']), 'joining_fee' => $plain($snap['joining_fee']), 'fee_currency' => $snap['fee_currency'], 'fee_period' => $snap['fee_period'],
    ];
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-members">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Edit details',
        'subtitle' => $m['full_name'] . ' - ' . $m['application_ref'] . '. The same rules as the public form apply. Status, dates and payments are changed on the member screen.',
        'actions'  => [['label' => 'Back to the member', 'url' => url('admin/members/' . $id), 'class' => 'btn--ghost', 'icon' => 'arrow-left']],
    ]) ?>

    <form method="post" action="<?= e(url('admin/members/' . $id)) ?>" class="card members-edit" data-unsaved-guard>
        <?= csrf_field() ?>
        <?php if (errors() !== []): ?>
            <div class="alert alert--error" role="alert" id="form-errors">
                <?= icon('alert-circle') ?>
                <div class="alert__body"><strong class="alert__title">Some details need attention.</strong> Please check the fields marked below.</div>
            </div>
        <?php endif; ?>
        <?php if (($m['card_status'] ?? 'none') === 'issued'): ?>
            <div class="card__body members-card-note">
                <p class="members-note"><?= icon('credit-card', 'icon--sm') ?> This member has an issued membership card. Changing the <strong>name</strong> or the <strong>place</strong> marks the card as out of date: the printed card keeps the old words until you choose "Re-issue (new version)" in the card panel.</p>
            </div>
        <?php endif; ?>
        <div class="card__body">
            <section class="form-section" aria-labelledby="sec-personal">
                <h2 class="form-section__title" id="sec-personal">Personal details</h2>
                <div class="form-grid">
                    <?= $f('full_name', 'Full name', ['required' => true, 'maxlength' => 150, 'autocomplete' => 'off']) ?>
                    <?= $f('gender', 'Gender', ['type' => 'select', 'options' => $lists['gender'], 'required' => true]) ?>
                    <?= $f('date_of_birth', 'Date of birth', ['type' => 'date', 'required' => true, 'max' => date('Y-m-d'),
                        'help' => $age >= 0 ? 'Age ' . $age . ($age < MS::ADULT_AGE ? ': under 18, so the parent or guardian details below are required.' : '.') : 'Use the format YYYY-MM-DD.',
                        'attrs' => ['data-dob' => true]]) ?>
                    <?= $f('nationality', 'Nationality', ['required' => true, 'maxlength' => 80]) ?>
                    <?php if ($collectsId): ?>
                        <?= $f('id_number', 'National ID or passport number', ['maxlength' => 60, 'help' => 'Optional. Only collected while the setting "Ask for a national ID or passport number" is on.']) ?>
                    <?php elseif (!empty($m['id_number'])): ?>
                        <div class="field"><span class="field__label">National ID or passport number</span><p class="text-muted small">One was collected earlier. It is kept as it is while the setting is off.</p></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-contact">
                <h2 class="form-section__title" id="sec-contact">Contact</h2>
                <div class="form-grid">
                    <?= $f('email', 'E-mail address', ['type' => 'email', 'required' => true, 'maxlength' => 190, 'help' => 'Changing it does not re-check for another application with the same address. It also resets the private page link that was sent to the old address, so the old link stops working.']) ?>
                    <?= $f('phone', 'Phone', ['type' => 'tel', 'required' => true, 'maxlength' => 40]) ?>
                    <?= $f('whatsapp', 'WhatsApp (optional)', ['type' => 'tel', 'maxlength' => 40]) ?>
                    <?= $f('country', 'Country', ['required' => true, 'maxlength' => 80]) ?>
                    <?= $f('region', 'District or region', ['required' => true, 'maxlength' => 120]) ?>
                    <?= $f('city', 'Sub-county or city', ['required' => true, 'maxlength' => 120]) ?>
                    <?= $f('address', 'Village or address (optional)', ['maxlength' => 255, 'class' => 'field--full']) ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-livelihood">
                <h2 class="form-section__title" id="sec-livelihood">Livelihood</h2>
                <div class="form-grid">
                    <?= $f('occupation_status', 'Occupation or status', ['type' => 'select', 'options' => $lists['occupation'], 'required' => true]) ?>
                    <?= $f('occupation_other', 'Occupation, in their words', ['maxlength' => 190, 'help' => 'Required when the occupation is "Other".']) ?>
                    <?= $f('education_level', 'Highest education level', ['type' => 'select', 'options' => $lists['education'], 'required' => true]) ?>
                    <?= $f('organisation', 'Organisation or school (optional)', ['maxlength' => 190]) ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-participation">
                <h2 class="form-section__title" id="sec-participation">Participation</h2>
                <fieldset class="members-interests<?= has_error('interests') ? ' field--error' : '' ?>">
                    <legend class="field__label">Areas of interest</legend>
                    <div class="members-interests__list">
                        <?php foreach ($interests as $key => $label): ?>
                            <label class="check"><input type="checkbox" name="interests[]" value="<?= e($key) ?>"<?= in_array((string) $key, $selected, true) ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (has_error('interests')): ?><div class="field__error" role="alert"><?= e((string) error('interests')) ?></div><?php endif; ?>
                </fieldset>
                <div class="form-grid">
                    <?= $f('availability', 'Availability', ['type' => 'select', 'options' => $lists['availability'], 'required' => true]) ?>
                    <?= $f('heard_about', 'How they heard about us', ['type' => 'select', 'options' => $lists['heard'], 'required' => true]) ?>
                    <?= $f('heard_about_other', 'How they heard, in their words', ['maxlength' => 190, 'help' => 'Required when the answer is "Other".']) ?>
                    <?= $f('skills', 'Skills and experience (optional)', ['type' => 'textarea', 'rows' => 4, 'maxlength' => 2000, 'class' => 'field--full']) ?>
                    <?= $f('motivation', 'Why they want to join', ['type' => 'textarea', 'rows' => 5, 'maxlength' => 3000, 'required' => true, 'class' => 'field--full',
                        'help' => 'At least ' . MS::MOTIVATION_MIN . ' characters.']) ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-guardian">
                <h2 class="form-section__title" id="sec-guardian">Parent or guardian</h2>
                <p class="form-section__help">Required when the member is under 18; ignored for adults.</p>
                <div class="form-grid form-grid--3">
                    <?= $f('guardian_name', 'Full name', ['maxlength' => 150]) ?>
                    <?= $f('guardian_phone', 'Phone', ['type' => 'tel', 'maxlength' => 40]) ?>
                    <?= $f('guardian_relationship', 'Relationship', ['maxlength' => 80]) ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-emergency">
                <h2 class="form-section__title" id="sec-emergency">Emergency contact</h2>
                <div class="form-grid form-grid--3">
                    <?= $f('emergency_name', 'Full name', ['required' => true, 'maxlength' => 150]) ?>
                    <?= $f('emergency_phone', 'Phone', ['type' => 'tel', 'required' => true, 'maxlength' => 40]) ?>
                    <?= $f('emergency_relationship', 'Relationship', ['required' => true, 'maxlength' => 80]) ?>
                </div>
            </section>

            <section class="form-section" aria-labelledby="sec-payment">
                <h2 class="form-section__title" id="sec-payment">Payment preference</h2>
                <div class="form-grid">
                    <?= $f('payment_method', 'Preferred payment method', ['type' => 'select', 'options' => $lists['payment_methods'], 'required' => true]) ?>
                    <?= $f('payment_reference', 'Payment reference (optional)', ['maxlength' => 120, 'help' => 'Only if they already paid. Recorded payments are managed on the member screen.']) ?>
                </div>
            </section>

            <section class="form-section" id="fee" aria-labelledby="sec-fee">
                <h2 class="form-section__title" id="sec-fee">Fee for this member</h2>
                <?php if ($canFee): ?>
                    <p class="form-section__help">This is the fee that was quoted to the person when they applied. Changing it here changes <strong>only this member</strong>, never the membership type or other members.
                        Leave the fee empty for "Fee to be announced"; enter 0 for free.</p>
                    <div class="form-grid members-feegrid">
                        <?= partial('partials/form-field', ['name' => 'fee_amount', 'label' => 'Membership fee', 'value' => old('fee_amount', $plain($m['fee_amount'])), 'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off'],
                            'help' => 'Numbers only, for example 50000.']) ?>
                        <?= partial('partials/form-field', ['name' => 'joining_fee', 'label' => 'Joining fee (optional)', 'value' => old('joining_fee', $plain($m['joining_fee'])), 'attrs' => ['inputmode' => 'decimal', 'autocomplete' => 'off'],
                            'help' => 'Charged once, on top of the membership fee.']) ?>
                        <?= partial('partials/form-field', ['name' => 'fee_currency', 'label' => 'Currency', 'value' => old('fee_currency', (string) $m['fee_currency']), 'maxlength' => 3, 'help' => 'Three letters, for example UGX.']) ?>
                        <?= partial('partials/form-field', ['name' => 'fee_period', 'label' => 'Billing period', 'type' => 'select', 'options' => $periods, 'value' => old('fee_period', (string) $m['fee_period']),
                            'help' => 'Decides when the membership expires. It does not move dates that are already set.']) ?>
                    </div>
                    <?php if ($typeFee !== null): ?>
                        <p class="members-feehelper">
                            <button class="btn btn--outline btn--sm" type="button" data-use-type-fee='<?= e((string) json_encode($typeFee)) ?>' hidden><?= icon('refresh', 'icon--sm') ?> Use the current fee of "<?= e($type['name']) ?>"</button>
                            <span class="form-note">Currently: <?= e(MS::typeFeeLabel($type)) ?><?= MS::joiningFeeLabel($type, true) !== '' ? ' - ' . e(MS::joiningFeeLabel($type, true)) : '' ?>.</span>
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><?= e(MS::memberFeeLabel($m)) ?><?= MS::joiningFeeLabel($m) !== '' ? ' - ' . e(MS::joiningFeeLabel($m)) : '' ?></p>
                    <p class="form-note">Only an administrator can change the fee of a single member.</p>
                <?php endif; ?>
            </section>

            <section class="form-section" aria-labelledby="sec-notes">
                <h2 class="form-section__title" id="sec-notes">Internal notes</h2>
                <?= $f('internal_notes', 'Notes for the team', ['type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'help' => 'Only staff can see these notes.']) ?>
            </section>
        </div>
        <div class="form-actions">
            <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save changes</button>
            <a class="btn btn--ghost" href="<?= e(url('admin/members/' . $id)) ?>">Cancel</a>
        </div>
    </form>
</div>
