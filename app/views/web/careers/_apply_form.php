<?php
/**
 * Application form (used for a vacancy and for the general "talent network" application).
 * Vars: $action (form URL), $idPrefix (unique id prefix), $messageHelp, $submit (button label).
 * All labels come from content('careers', 'form.*'). The optional CV is validated again on the server (Uploader::privateFile).
 */
$cvMax = (int) config('upload.max_cv_bytes');
$cvExts = (array) config('upload.cv_exts');
$idPrefix = (string) ($idPrefix ?? 'apply');
?>
<form class="form-card apply-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-apply-form>
    <?= csrf_field() ?>
    <?= honeypot_field() ?>
    <div class="form-grid">
        <?= partial('partials/form-field', ['name' => 'name', 'id' => $idPrefix . '-name', 'label' => content('careers', 'form.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
        <?= partial('partials/form-field', ['name' => 'email', 'id' => $idPrefix . '-email', 'label' => content('careers', 'form.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
        <?= partial('partials/form-field', ['name' => 'phone', 'id' => $idPrefix . '-phone', 'label' => content('careers', 'form.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
        <?= partial('partials/form-field', ['name' => 'linkedin_url', 'id' => $idPrefix . '-linkedin', 'label' => content('careers', 'form.linkedin_label'), 'type' => 'url', 'maxlength' => 255, 'placeholder' => 'https://www.linkedin.com/in/…', 'autocomplete' => 'url']) ?>
        <?= partial('partials/form-field', ['name' => 'cover_letter', 'id' => $idPrefix . '-message', 'label' => content('careers', 'form.message_label'), 'type' => 'textarea', 'rows' => 6, 'maxlength' => 5000, 'help' => $messageHelp ?? '', 'class' => 'field--full']) ?>
        <?= partial('partials/form-field', [
            'name' => 'cv', 'id' => $idPrefix . '-cv', 'label' => content('careers', 'form.cv_label'), 'type' => 'file', 'value' => '', 'class' => 'field--full',
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
        <button class="btn btn--primary btn--lg" type="submit" data-loading-text="<?= e(content('careers', 'form.sending')) ?>"><?= e($submit) ?></button>
    </div>
</form>
