<?php
/**
 * Application form (used for a vacancy and for the general "talent network" application).
 * Vars: $action (form URL), $idPrefix (unique id prefix), $messageHelp, $submit (button label).
 * All labels come from content('careers', 'form.*'). The optional CV is validated again on the server (Uploader::privateFile).
 * Failed submissions show an accessible error summary above the form (partial web/inner-a/_error_summary).
 */
$cvMax = (int) config('upload.max_cv_bytes');
$cvExts = (array) config('upload.cv_exts');
$idPrefix = (string) ($idPrefix ?? 'apply');
$fieldMeta = [
    'name'         => ['id' => $idPrefix . '-name', 'label' => (string) content('careers', 'form.name_label')],
    'email'        => ['id' => $idPrefix . '-email', 'label' => (string) content('careers', 'form.email_label')],
    'phone'        => ['id' => $idPrefix . '-phone', 'label' => (string) content('careers', 'form.phone_label')],
    'linkedin_url' => ['id' => $idPrefix . '-linkedin', 'label' => (string) content('careers', 'form.linkedin_label')],
    'cover_letter' => ['id' => $idPrefix . '-message', 'label' => (string) content('careers', 'form.message_label')],
    'cv'           => ['id' => $idPrefix . '-cv', 'label' => (string) content('careers', 'form.cv_label')],
    'consent'      => ['id' => $idPrefix . '-consent', 'label' => ''],
];
?>
<form class="form-card ia-form apply-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-apply-form>
    <?= partial('web/inner-a/_error_summary', ['title' => (string) content('careers', 'form.error_title'), 'fields' => $fieldMeta, 'sid' => $idPrefix]) ?>
    <?= csrf_field() ?>
    <?= honeypot_field() ?>
    <div class="form-grid">
        <?= partial('partials/form-field', ['name' => 'name', 'id' => $fieldMeta['name']['id'], 'label' => $fieldMeta['name']['label'], 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
        <?= partial('partials/form-field', ['name' => 'email', 'id' => $fieldMeta['email']['id'], 'label' => $fieldMeta['email']['label'], 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
        <?= partial('partials/form-field', ['name' => 'phone', 'id' => $fieldMeta['phone']['id'], 'label' => $fieldMeta['phone']['label'], 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
        <?= partial('partials/form-field', ['name' => 'linkedin_url', 'id' => $fieldMeta['linkedin_url']['id'], 'label' => $fieldMeta['linkedin_url']['label'], 'type' => 'url', 'maxlength' => 255, 'placeholder' => 'https://www.linkedin.com/in/…', 'autocomplete' => 'url']) ?>
        <?= partial('partials/form-field', ['name' => 'cover_letter', 'id' => $fieldMeta['cover_letter']['id'], 'label' => $fieldMeta['cover_letter']['label'], 'type' => 'textarea', 'rows' => 6, 'maxlength' => 5000, 'help' => $messageHelp ?? '', 'class' => 'field--full']) ?>
        <?= partial('partials/form-field', [
            'name' => 'cv', 'id' => $fieldMeta['cv']['id'], 'label' => $fieldMeta['cv']['label'], 'type' => 'file', 'value' => '', 'class' => 'field--full',
            'help' => content('careers', 'form.cv_help'),
            'attrs' => [
                'accept'          => '.' . implode(',.', $cvExts),
                'data-max-bytes'  => $cvMax,
                'data-error-size' => content('careers', 'form.cv_error_size'),
                'data-error-type' => content('careers', 'form.cv_error_type'),
                'data-exts'       => implode(',', $cvExts),
            ],
        ]) ?>
        <div class="field field--full<?= has_error('consent') ? ' field--error' : '' ?>">
            <label class="check" for="<?= e($idPrefix) ?>-consent">
                <input type="checkbox" id="<?= e($idPrefix) ?>-consent" name="consent" value="1" required<?= old('consent') ? ' checked' : '' ?><?= has_error('consent') ? ' aria-invalid="true" aria-describedby="' . e($idPrefix) . '-consent-error"' : '' ?>>
                <span><?= e(content('careers', 'form.consent_label')) ?> <a href="<?= e(url('privacy')) ?>" target="_blank" rel="noopener"><?= e(content('careers', 'form.privacy_link_label')) ?></a></span>
            </label>
            <?php if (has_error('consent')): ?><div class="field__error" id="<?= e($idPrefix) ?>-consent-error" role="alert"><?= e(error('consent')) ?></div><?php endif; ?>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e(content('careers', 'form.sending')) ?>"><?= e($submit) ?></button>
    </div>
</form>
