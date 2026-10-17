<?php
/**
 * MEMBERSHIP REGISTRATION FORM  (/membership/register). OWNER: WEB agent. Contract: docs/MEMBERSHIP.md section 4.
 * Vars: $open, $types (active types with places left, each with fee_label / joining_label / eligibility), $selected (type id),
 * $collectId, $instructions (plain text), $age, $minor, $interestsOld, $dobMin, $dobMax, $options (list => key => label).
 * Every visible word comes from content('membership', ...) (Admin > Pages > Membership). The server validates everything
 * (MembershipApplication::process); assets/js/membership.js only adds comfort (age, guardian block, "other" answers, fee line,
 * counters, inline checks). WITHOUT JavaScript every conditional field stays visible and the page still works.
 */

use App\Services\Membership\MembershipService as MS;

$c = static fn(string $key, string $fallback = ''): string => MS::copy($key, $fallback);
$lab = static fn(string $key): string => MS::copy('fields.' . $key . '_label', ucfirst(str_replace('_', ' ', $key)));
$hlp = static fn(string $key): string => trim((string) content('membership', 'fields.' . $key . '_help'));
$F = static fn(string $name, array $o = []): string => partial('web/membership/_field', ['name' => $name] + $o);

$closedBtn = content_btn('membership', 'closed.button');
?>
<?= partial('partials/page-hero', [
    'title'   => (string) content('membership', 'register.title'),
    'eyebrow' => (string) content('membership', 'register.eyebrow'),
    'lead'    => $open ? (string) content('membership', 'register.lead') : (string) content('membership', 'closed.lead'),
    'sheet'   => 'tint',
]) ?>

<?php if (!$open || $types === []): ?>
<section class="section section--tint mb-register">
    <div class="container container--narrow">
        <?php if (!$open): ?>
            <?= partial('partials/empty-state', ['title' => content('membership', 'closed.title'), 'text' => content('membership', 'closed.text'), 'icon' => 'lock',
                'actions' => $closedBtn['has'] ? [['label' => $closedBtn['label'], 'url' => $closedBtn['url'], 'class' => 'btn--cta btn--caps']] : []]) ?>
        <?php elseif (!empty($allFull)): ?>
            <?= partial('partials/empty-state', ['title' => content('membership', 'types.full_title'), 'text' => content('membership', 'types.full_text'), 'icon' => 'users']) ?>
        <?php else: ?>
            <?= partial('partials/empty-state', ['title' => content('membership', 'types.empty_title'), 'text' => content('membership', 'types.empty_text')]) ?>
        <?php endif; ?>
    </div>
</section>
<?php return; endif; ?>

<?php
$this->start('scripts'); ?><script src="<?= e(asset('js/membership.js')) ?>" defer></script><?php $this->end();

$termsHref = url('membership/terms');
$privacyHref = url('privacy');
$termsText = $c('consent.terms_link_text', 'membership terms');
$privacyText = $c('consent.privacy_link_text', 'privacy notice');
$consentHtml = static function (string $label, array $links): string {          // escape the whole text, then turn the {placeholders} into links
    $html = e($label);
    foreach ($links as $token => [$href, $text]) {
        $html = str_replace($token, '<a href="' . e($href) . '" target="_blank" rel="noopener">' . e($text) . '</a>', $html);
    }
    return $html;
};
$plainConsent = static fn(string $label, array $texts): string => rtrim(trim(str_replace(array_keys($texts), array_values($texts), $label)), '.:');
$consentTerms = $c('consent.terms_label', 'I have read and agree to the {terms_link}.');
$consentPrivacy = $c('consent.privacy_label', 'I have read the {privacy_link} and agree that my personal data may be processed to handle my membership.');

// ---- the selected type (fee reminder) ----------------------------------------------------------------------------
$sel = null;
foreach ($types as $t) {
    if ((int) $t['id'] === (int) $selected) {
        $sel = $t;
    }
}
$feeTpl = $c('form.fee_line', '{fee}');
$ageTpl = $c('form.age_line', 'Age: {age}');
$typeOptions = [];
$typeAttrs = [];
foreach ($types as $t) {
    $typeOptions[(string) $t['id']] = $t['name'] . ' - ' . $t['fee_label'];
    $typeAttrs[(string) $t['id']] = ['data-fee' => $t['fee_label'], 'data-joining' => $t['joining_label'], 'data-elig' => trim((string) $t['eligibility'])];
}
$eligLabel = $c('types.eligibility_label', 'Who it is for');

// ---- the groups (also the numbered links of the side list) ---------------------------------------------------------
$groups = [
    ['type', 'form.legend_type', false], ['personal', 'form.legend_personal', false], ['guardian', 'form.legend_guardian', true],
    ['contact', 'form.legend_contact', false], ['livelihood', 'form.legend_livelihood', false], ['participation', 'form.legend_participation', false],
    ['emergency', 'form.legend_emergency', false], ['payment', 'form.legend_payment', false], ['consent', 'form.legend_consent', false],
];
$legend = [];
foreach ($groups as [$gid, $gkey]) {
    $legend[$gid] = $c($gkey, ucfirst($gid));
}

// ---- error summary (server side; membership.js fills the same box for its own checks) --------------------------------
$sumLabels = [
    'type_id' => $lab('type'), 'full_name' => $lab('name'), 'gender' => $lab('gender'), 'date_of_birth' => $lab('dob'), 'nationality' => $lab('nationality'),
    'id_number' => $lab('id_number'), 'email' => $lab('email'), 'phone' => $lab('phone'), 'whatsapp' => $lab('whatsapp'), 'country' => $lab('country'),
    'region' => $lab('region'), 'city' => $lab('city'), 'address' => $lab('address'), 'occupation_status' => $lab('occupation'),
    'occupation_other' => $lab('occupation_other'), 'education_level' => $lab('education'), 'organisation' => $lab('organisation'),
    'interests' => $lab('interests'), 'skills' => $lab('skills'), 'availability' => $lab('availability'), 'heard_about' => $lab('heard'),
    'heard_about_other' => $lab('heard_other'), 'motivation' => $lab('motivation'), 'guardian_name' => $lab('guardian_name'),
    'guardian_phone' => $lab('guardian_phone'), 'guardian_relationship' => $legend['guardian'] . ': ' . $lab('guardian_relationship'), 'emergency_name' => $lab('emergency_name'),
    'emergency_phone' => $lab('emergency_phone'), 'emergency_relationship' => $legend['emergency'] . ': ' . $lab('emergency_relationship'), 'payment_method' => $lab('payment_method'),
    'payment_reference' => $lab('payment_reference'),
    'consent_terms' => $plainConsent($consentTerms, ['{terms_link}' => $termsText]),
    'consent_privacy' => $plainConsent($consentPrivacy, ['{privacy_link}' => $privacyText]),
    'declaration' => $plainConsent($c('consent.declaration_label'), []),
    'consent_guardian' => $plainConsent($c('consent.guardian_consent_label'), []),
];
$summaryRows = [];
foreach (errors() as $field => $message) {
    $field = (string) $field;
    if ($field !== '_form') {
        $summaryRows[] = ['id' => 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $field), 'label' => trim(rtrim((string) ($sumLabels[$field] ?? ''), ' *')), 'message' => (string) $message];
    }
}
$oldOcc = old('occupation_status', '');
$oldHeard = old('heard_about', '');
$otherOcc = is_scalar($oldOcc) && (string) $oldOcc === MS::OTHER_KEY;
$otherHeard = is_scalar($oldHeard) && (string) $oldHeard === MS::OTHER_KEY;
$minor = !empty($minor);
?>
<section class="section section--tint mb-register" aria-label="<?= e(strip_highlight((string) content('membership', 'register.title'))) ?>">
    <div class="container">
        <div class="mb-layout">
            <aside class="mb-aside">
                <nav aria-label="<?= e($c('form.sections_label', 'Form sections')) ?>">
                    <ol class="mb-aside__list">
                        <?php foreach ($groups as [$gid, , $cond]): ?>
                            <li class="mb-aside__item<?= $cond ? ' mb-aside__item--cond' . ($minor ? ' is-active' : '') : '' ?>"<?= $cond ? ' data-minor-link' : '' ?>><a href="#g-<?= e($gid) ?>"><?= e($legend[$gid]) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <p class="mb-aside__links"><a href="<?= e($termsHref) ?>" target="_blank" rel="noopener"><?= e(ucfirst($termsText)) ?></a> &middot; <a href="<?= e($privacyHref) ?>" target="_blank" rel="noopener"><?= e(ucfirst($privacyText)) ?></a></p>
            </aside>

            <form class="mb-form form-card" method="post" action="<?= e(url('membership/register')) ?>" novalidate data-mb-form
                  data-adult-age="<?= (int) MS::ADULT_AGE ?>"
                  data-msg-required="<?= e($c('form.err_required')) ?>" data-msg-email="<?= e($c('form.err_email')) ?>" data-msg-phone="<?= e($c('form.err_phone')) ?>"
                  data-msg-min="<?= e($c('form.err_min')) ?>" data-msg-accept="<?= e($c('form.err_accept')) ?>" data-msg-dob="<?= e($c('form.err_dob')) ?>"
                  data-msg-date="<?= e($c('form.err_date')) ?>">
                <div class="mb-summary" id="mb-summary" role="alert" tabindex="-1" aria-labelledby="mb-summary-title" data-mb-summary<?= $summaryRows ? ' data-server="1"' : ' hidden' ?>>
                    <h2 class="mb-summary__title" id="mb-summary-title"><?= icon('alert-circle') ?> <span><?= e($c('form.error_title')) ?></span></h2>
                    <ul data-mb-summary-list>
                        <?php foreach ($summaryRows as $r): ?>
                            <li><a href="#<?= e($r['id']) ?>" data-error-link><?= $r['label'] !== '' ? e($r['label']) . ': ' : '' ?><?= e($r['message']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <p class="form-note mb-form__note"><?= e($c('form.required_note')) ?></p>

                <?php /* ------------------------------------------------------------------ 1. membership type */ ?>
                <fieldset class="mb-group" id="g-type">
                    <legend class="mb-group__legend"><span><?= e($legend['type']) ?></span></legend>
                    <div class="form-grid">
                        <?= $F('type_id', ['label' => $lab('type'), 'type' => 'select', 'required' => true, 'class' => 'field--full', 'help' => $hlp('type'),
                            'options' => $typeOptions, 'optionAttrs' => $typeAttrs, 'empty' => $c('form.choose', 'Choose an option'), 'value' => $selected > 0 ? (string) $selected : '']) ?>
                        <div class="field--full">
                            <div class="mb-feebox" data-feebox aria-live="polite"<?= $sel === null ? ' hidden' : '' ?>>
                                <p class="mb-feebox__fee" data-fee-line data-template="<?= e($feeTpl) ?>"><?= $sel !== null ? e(str_replace('{fee}', $sel['fee_label'], $feeTpl)) : '' ?></p>
                                <p class="mb-feebox__joining" data-joining-line<?= $sel === null || $sel['joining_label'] === '' ? ' hidden' : '' ?>><?= $sel !== null ? e($sel['joining_label']) : '' ?></p>
                                <p class="mb-feebox__elig" data-elig-line<?= $sel === null || trim((string) $sel['eligibility']) === '' ? ' hidden' : '' ?>><strong><?= e($eligLabel) ?>:</strong> <span data-elig-text><?= $sel !== null ? e(trim((string) $sel['eligibility'])) : '' ?></span></p>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 2. about you */ ?>
                <fieldset class="mb-group" id="g-personal">
                    <legend class="mb-group__legend"><span><?= e($legend['personal']) ?></span></legend>
                    <div class="form-grid">
                        <?= $F('full_name', ['label' => $lab('name'), 'required' => true, 'class' => 'field--full', 'help' => $hlp('name'), 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('web/membership/_choice', ['name' => 'gender', 'label' => $lab('gender'), 'options' => $options['gender'], 'required' => true, 'help' => $hlp('gender')]) ?>
                        <?= $F('date_of_birth', [
                            'label' => $lab('dob'), 'type' => 'date', 'required' => true, 'help' => $hlp('dob'), 'autocomplete' => 'bday',
                            'attrs' => ['min' => $dobMin, 'max' => $dobMax, 'data-dob' => '1'], 'describedby' => ['mb-age'],
                            'after' => '<p class="mb-age" id="mb-age" data-age-line data-template="' . e($ageTpl) . '" aria-live="polite"' . ($age >= 0 ? '' : ' hidden') . '>' . ($age >= 0 ? e(str_replace('{age}', (string) $age, $ageTpl)) : '') . '</p>',
                        ]) ?>
                        <?= $F('nationality', ['label' => $lab('nationality'), 'required' => true, 'help' => $hlp('nationality'), 'maxlength' => 80]) ?>
                        <?php if ($collectId): ?>
                            <?= $F('id_number', ['label' => $lab('id_number'), 'class' => 'field--full', 'help' => $hlp('id_number'), 'maxlength' => 60, 'autocomplete' => 'off']) ?>
                        <?php endif; ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 3. parent or guardian (under 18; always visible without JS) */ ?>
                <fieldset class="mb-group mb-group--cond<?= $minor ? ' is-active' : '' ?>" id="g-guardian" data-minor-group>
                    <legend class="mb-group__legend"><span><?= e($legend['guardian']) ?></span></legend>
                    <p class="mb-note" role="note"><?= icon('info') ?> <span><?= e($c('form.minor_note')) ?></span></p>
                    <div class="form-grid">
                        <?= $F('guardian_name', ['label' => $lab('guardian_name'), 'condRequired' => true, 'required' => $minor, 'help' => $hlp('guardian_name'), 'autocomplete' => 'off', 'maxlength' => 150, 'attrs' => ['data-minor-required' => '1']]) ?>
                        <?= $F('guardian_phone', ['label' => $lab('guardian_phone'), 'type' => 'tel', 'condRequired' => true, 'required' => $minor, 'help' => $hlp('guardian_phone'), 'autocomplete' => 'off', 'inputmode' => 'tel', 'maxlength' => 40, 'attrs' => ['data-minor-required' => '1', 'data-kind' => 'phone']]) ?>
                        <?= $F('guardian_relationship', ['label' => $lab('guardian_relationship'), 'condRequired' => true, 'required' => $minor, 'help' => $hlp('guardian_relationship'), 'summaryLabel' => $legend['guardian'] . ': ' . $lab('guardian_relationship'), 'autocomplete' => 'off', 'maxlength' => 80, 'attrs' => ['data-minor-required' => '1']]) ?>
                        <?= partial('web/membership/_consent', ['name' => 'consent_guardian', 'condRequired' => true, 'required' => $minor, 'labelHtml' => e($c('consent.guardian_consent_label'))]) ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 4. contact details */ ?>
                <fieldset class="mb-group" id="g-contact">
                    <legend class="mb-group__legend"><span><?= e($legend['contact']) ?></span></legend>
                    <div class="form-grid">
                        <?= $F('email', ['label' => $lab('email'), 'type' => 'email', 'required' => true, 'help' => $hlp('email'), 'autocomplete' => 'email', 'maxlength' => 190, 'attrs' => ['autocapitalize' => 'none', 'spellcheck' => 'false']]) ?>
                        <?= $F('phone', ['label' => $lab('phone'), 'type' => 'tel', 'required' => true, 'help' => $hlp('phone'), 'autocomplete' => 'tel', 'inputmode' => 'tel', 'maxlength' => 40, 'attrs' => ['data-kind' => 'phone']]) ?>
                        <?= $F('whatsapp', ['label' => $lab('whatsapp'), 'type' => 'tel', 'help' => $hlp('whatsapp'), 'autocomplete' => 'tel', 'inputmode' => 'tel', 'maxlength' => 40, 'attrs' => ['data-kind' => 'phone']]) ?>
                        <?= $F('country', ['label' => $lab('country'), 'required' => true, 'help' => $hlp('country'), 'autocomplete' => 'country-name', 'maxlength' => 80]) ?>
                        <?= $F('region', ['label' => $lab('region'), 'required' => true, 'help' => $hlp('region'), 'autocomplete' => 'address-level1', 'maxlength' => 120]) ?>
                        <?= $F('city', ['label' => $lab('city'), 'required' => true, 'help' => $hlp('city'), 'autocomplete' => 'address-level2', 'maxlength' => 120]) ?>
                        <?= $F('address', ['label' => $lab('address'), 'class' => 'field--full', 'help' => $hlp('address'), 'autocomplete' => 'street-address', 'maxlength' => 255]) ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 5. occupation and education */ ?>
                <fieldset class="mb-group" id="g-livelihood">
                    <legend class="mb-group__legend"><span><?= e($legend['livelihood']) ?></span></legend>
                    <div class="form-grid">
                        <?= $F('occupation_status', ['label' => $lab('occupation'), 'type' => 'select', 'required' => true, 'help' => $hlp('occupation'), 'options' => $options['occupation'], 'empty' => $c('form.choose', 'Choose an option'), 'attrs' => ['data-other-trigger' => 'occupation_other']]) ?>
                        <?= $F('education_level', ['label' => $lab('education'), 'type' => 'select', 'required' => true, 'help' => $hlp('education'), 'options' => $options['education'], 'empty' => $c('form.choose', 'Choose an option')]) ?>
                        <div class="field--full mb-cond<?= $otherOcc ? ' is-active' : '' ?>" data-cond="occupation_other">
                            <?= $F('occupation_other', ['label' => $lab('occupation_other'), 'condRequired' => true, 'required' => $otherOcc, 'help' => $hlp('occupation_other'), 'maxlength' => 190, 'attrs' => ['data-cond-required' => '1']]) ?>
                        </div>
                        <?= $F('organisation', ['label' => $lab('organisation'), 'class' => 'field--full', 'help' => $hlp('organisation'), 'autocomplete' => 'organization', 'maxlength' => 190]) ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 6. participation */ ?>
                <fieldset class="mb-group" id="g-participation">
                    <legend class="mb-group__legend"><span><?= e($legend['participation']) ?></span></legend>
                    <div class="form-grid">
                        <?php if ($options['interests']): ?>
                            <?= partial('web/membership/_choice', ['name' => 'interests', 'type' => 'checkbox', 'layout' => 'cards', 'label' => $lab('interests'), 'options' => $options['interests'], 'selected' => $interestsOld, 'help' => $hlp('interests')]) ?>
                        <?php endif; ?>
                        <?= $F('skills', ['label' => $lab('skills'), 'type' => 'textarea', 'rows' => 4, 'class' => 'field--full', 'help' => $hlp('skills'), 'maxlength' => 2000, 'counter' => true]) ?>
                        <?= $F('availability', ['label' => $lab('availability'), 'type' => 'select', 'required' => true, 'help' => $hlp('availability'), 'options' => $options['availability'], 'empty' => $c('form.choose', 'Choose an option')]) ?>
                        <?= $F('heard_about', ['label' => $lab('heard'), 'type' => 'select', 'required' => true, 'help' => $hlp('heard'), 'options' => $options['heard'], 'empty' => $c('form.choose', 'Choose an option'), 'attrs' => ['data-other-trigger' => 'heard_about_other']]) ?>
                        <div class="field--full mb-cond<?= $otherHeard ? ' is-active' : '' ?>" data-cond="heard_about_other">
                            <?= $F('heard_about_other', ['label' => $lab('heard_other'), 'condRequired' => true, 'required' => $otherHeard, 'help' => $hlp('heard_other'), 'maxlength' => 190, 'attrs' => ['data-cond-required' => '1']]) ?>
                        </div>
                        <?= $F('motivation', ['label' => $lab('motivation'), 'type' => 'textarea', 'rows' => 5, 'required' => true, 'class' => 'field--full', 'help' => $hlp('motivation'), 'maxlength' => 3000, 'minlength' => MS::MOTIVATION_MIN, 'counter' => true]) ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 7. emergency contact */ ?>
                <fieldset class="mb-group" id="g-emergency">
                    <legend class="mb-group__legend"><span><?= e($legend['emergency']) ?></span></legend>
                    <div class="form-grid">
                        <?= $F('emergency_name', ['label' => $lab('emergency_name'), 'required' => true, 'help' => $hlp('emergency_name'), 'autocomplete' => 'off', 'maxlength' => 150]) ?>
                        <?= $F('emergency_phone', ['label' => $lab('emergency_phone'), 'type' => 'tel', 'required' => true, 'help' => $hlp('emergency_phone'), 'autocomplete' => 'off', 'inputmode' => 'tel', 'maxlength' => 40, 'attrs' => ['data-kind' => 'phone']]) ?>
                        <?= $F('emergency_relationship', ['label' => $lab('emergency_relationship'), 'required' => true, 'help' => $hlp('emergency_relationship'), 'summaryLabel' => $legend['emergency'] . ': ' . $lab('emergency_relationship'), 'autocomplete' => 'off', 'maxlength' => 80]) ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 8. payment (nothing is paid here) */ ?>
                <fieldset class="mb-group" id="g-payment">
                    <legend class="mb-group__legend"><span><?= e($legend['payment']) ?></span></legend>
                    <p class="mb-note" role="note"><?= icon('banknote') ?> <span><?= e($c('form.payment_note')) ?></span></p>
                    <div class="form-grid">
                        <?= partial('web/membership/_choice', ['name' => 'payment_method', 'label' => $lab('payment_method'), 'options' => $options['payment_methods'], 'required' => true, 'help' => $hlp('payment_method')]) ?>
                        <div class="field--full">
                            <p class="mb-feeline" data-fee-line data-template="<?= e($feeTpl) ?>"<?= $sel === null ? ' hidden' : '' ?>><?= $sel !== null ? e(str_replace('{fee}', $sel['fee_label'], $feeTpl)) : '' ?></p>
                        </div>
                        <?= $F('payment_reference', ['label' => $lab('payment_reference'), 'class' => 'field--full', 'help' => $hlp('payment_reference'), 'autocomplete' => 'off', 'maxlength' => 120]) ?>
                        <?php if ($instructions !== ''): ?>
                            <details class="mb-details field--full">
                                <summary><?= e(strip_highlight((string) content('membership', 'payment.title'))) ?></summary>
                                <div class="mb-details__body"><?= nl2p($instructions) ?></div>
                            </details>
                        <?php endif; ?>
                    </div>
                </fieldset>

                <?php /* ------------------------------------------------------------------ 9. consent and declaration */ ?>
                <fieldset class="mb-group" id="g-consent">
                    <legend class="mb-group__legend"><span><?= e($legend['consent']) ?></span></legend>
                    <div class="form-grid mb-consents">
                        <?= partial('web/membership/_consent', ['name' => 'consent_terms', 'required' => true, 'labelHtml' => $consentHtml($consentTerms, ['{terms_link}' => [$termsHref, $termsText]])]) ?>
                        <?= partial('web/membership/_consent', ['name' => 'consent_privacy', 'required' => true, 'labelHtml' => $consentHtml($consentPrivacy, ['{privacy_link}' => [$privacyHref, $privacyText]])]) ?>
                        <?= partial('web/membership/_consent', ['name' => 'consent_updates', 'labelHtml' => e($c('consent.updates_label'))]) ?>
                        <?= partial('web/membership/_consent', ['name' => 'declaration', 'required' => true, 'labelHtml' => e($c('consent.declaration_label'))]) ?>
                    </div>
                </fieldset>

                <div class="form-actions mb-form__actions">
                    <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e($c('form.sending_label')) ?>"><?= e($c('form.submit_label')) ?></button>
                </div>
            </form>
        </div>
    </div>
</section>
