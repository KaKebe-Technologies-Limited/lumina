<?php
/**
 * THE FORM (markup CONTRACT for assets/js/forms.js; docs/FORMS.md section 8.3). Used by show.php (the page), _embed.php (the [form] shortcode), print.php
 * (blank copy) and the admin preview endpoint. Works WITHOUT JavaScript (every page is shown, the server validates); forms.js enhances it.
 *
 * Vars: $form (hydrated form), $input (posted answers to refill: f_<id> => string|list), $errors (field id | _form | _consent => message),
 *       $hpTs (signed timestamp of the page the visitor came from, kept when the form is re-shown after an error), $context (verified job context or null), $mode ('live' | 'preview' (staff, nothing is sent) | 'print' | 'embed'), $formId (DOM id prefix).
 *
 * data-* hooks on <form data-lf>: data-lf-config (JSON from FormService::clientConfig), data-lf-text (the registry wording the script needs: error messages,
 * step text, file texts), data-lf-layout (stepped|single), data-lf-progress (1|0), data-lf-restore (0 when the server sent the answers back).
 * Each page is <section class="lf__page" data-lf-page="<page id>" data-lf-logic="...">. Buttons: [data-lf-prev] [data-lf-next] [data-lf-submit].
 * Error summary: [data-lf-summary]. Progress: [data-lf-progress-bar]. Draft notice: [data-lf-draft]. Print: [data-lf-print]. data-no-justify keeps the
 * site-wide justified-text script away from labels, help and notes (they stay left-aligned).
 */
use App\Services\Forms\FormService;
use App\Services\Forms\FormText;

$cfg = (array) $form['cfg'];
$def = (array) $form['def'];
$input = $input ?? [];
$errors = $errors ?? [];
$mode = $mode ?? 'live';
$context = $context ?? null;
$readonly = $mode === 'print';
$pagesTotal = count($def['pages']);
$stepped = ($cfg['theme']['layout'] ?? 'stepped') === 'stepped' && $pagesTotal > 1;
$fieldTitles = [];
foreach ($def['pages'] as $pg) {
    foreach ($pg['fields'] as $ff) {
        $fieldTitles[(string) $ff['id']] = (string) $ff['label'];
    }
}
$submitLabel = trim((string) ($cfg['submit_label'] ?? '')) !== '' ? (string) $cfg['submit_label'] : FormText::get('ui.submit');
$action = $mode === 'live' || $mode === 'embed' ? url('forms/' . $form['slug']) : '';
$formDomId = 'lf-' . $form['id'] . ($mode === 'embed' ? '-embed' : '');

// the wording the script needs (raw templates: it fills {placeholders} itself); everything comes from the editable registry page "forms"
$lfText = ['errors' => [], 'ui' => []];
foreach (['required', 'min_length', 'max_length', 'pattern', 'email', 'phone', 'number', 'min_number', 'max_number', 'date', 'min_date', 'max_date', 'time',
    'min_time', 'max_time', 'option', 'other_required', 'min_select', 'max_select', 'yes_no', 'rating', 'consent', 'file_required', 'file_too_many', 'file_type',
    'file_size', 'file_empty', 'throttled', 'expired', 'generic'] as $code) {
    $lfText['errors'][$code] = FormText::get('errors.' . $code);
}
foreach (['step_of', 'submitting', 'char_count', 'error_summary_title', 'error_summary_intro', 'error_go', 'file_none', 'file_remove', 'file_choose', 'file_choose_many', 'yes', 'no'] as $k) {
    $lfText['ui'][$k] = FormText::get('ui.' . $k);
}
$restore = $errors === [] && $input === [];
?>
<form class="lf lf--<?= e($mode) ?>" id="<?= e($formDomId) ?>" method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-no-justify
      data-lf data-lf-layout="<?= $stepped ? 'stepped' : 'single' ?>" data-lf-progress="<?= !empty($cfg['theme']['progress']) ? '1' : '0' ?>" data-lf-mode="<?= e($mode) ?>" data-lf-restore="<?= $restore ? '1' : '0' ?>"
      data-lf-config="<?= e((string) json_encode(FormService::clientConfig($form), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>"
      data-lf-text="<?= e((string) json_encode($lfText, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
    <?php if ($mode === 'live' || $mode === 'embed'): ?>
        <?= csrf_field() ?>
        <?= honeypot_field(isset($hpTs) && is_string($hpTs) ? $hpTs : null) ?>
        <?php if ($context !== null && !empty($context['ref'])): ?><input type="hidden" name="job" value="<?= e((string) $context['ref']) ?>"><?php endif; ?>
    <?php endif; ?>

    <div class="lf__summary" data-lf-summary role="alert" tabindex="-1"<?= $errors === [] ? ' hidden' : '' ?>>
        <h2 class="lf__summary-title"><?= icon('alert-circle', 'icon--sm') ?> <?= e(FormText::get('ui.error_summary_title')) ?></h2>
        <p><?= e(FormText::get('ui.error_summary_intro')) ?></p>
        <ul class="lf__summary-list" data-lf-summary-list>
            <?php foreach ($errors as $fid => $msg): ?>
                <?php
                $name = $fid === '_consent' ? (trim((string) ($cfg['consent']['text'] ?? '')) !== '' ? (string) $cfg['consent']['text'] : FormText::get('ui.consent_default')) : (string) ($fieldTitles[$fid] ?? '');
                $anchor = $fid === '_consent' ? '#q-form-consent' : '#q-' . $fid;
                ?>
                <li><?php if ($name === '' || $fid === '_form'): ?><?= e((string) $msg) ?><?php elseif (mb_stripos((string) $msg, $name) !== false): ?><a href="<?= e($anchor) ?>"><?= e((string) $msg) ?></a><?php else: ?><a href="<?= e($anchor) ?>"><?= e($name) ?></a>: <?= e((string) $msg) ?><?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($stepped && !empty($cfg['theme']['progress']) && $mode !== 'print'): ?>
        <div class="lf__progress" data-lf-progress-bar hidden>
            <div class="lf__progress-head">
                <p class="lf__step" data-lf-step-label aria-live="polite"></p>
                <p class="lf__pct" data-lf-pct aria-hidden="true"></p>
            </div>
            <div class="lf__bar" role="progressbar" aria-label="<?= e(FormText::get('ui.progress_label')) ?>" aria-valuemin="1" aria-valuemax="<?= (int) $pagesTotal ?>" aria-valuenow="1"><span class="lf__bar-fill"></span></div>
        </div>
    <?php endif; ?>

    <?php foreach ($def['pages'] as $pi => $page): ?>
        <section class="lf__page" data-lf-page="<?= e((string) $page['id']) ?>"<?= $page['logic'] !== null ? ' data-lf-logic="' . e((string) json_encode($page['logic'])) . '"' : '' ?> aria-labelledby="pt-<?= e((string) $page['id']) ?>">
            <?php if (trim((string) $page['title']) !== '' || $stepped || $pagesTotal > 1): ?>
                <h2 class="lf__page-title" id="pt-<?= e((string) $page['id']) ?>" tabindex="-1" data-lf-page-title<?= trim((string) $page['title']) === '' ? ' data-lf-auto' : '' ?>><?= e(trim((string) $page['title']) !== '' ? (string) $page['title'] : FormText::get('ui.step_of', ['current' => $pi + 1, 'total' => $pagesTotal])) ?></h2>
            <?php endif; ?>
            <?php if (trim((string) $page['description']) !== ''): ?><p class="lf__page-text"><?= e((string) $page['description']) ?></p><?php endif; ?>
            <div class="lf__grid">
                <?php foreach ($page['fields'] as $f): ?>
                    <?= partial('web/forms/_field', ['f' => $f, 'input' => $input, 'errors' => $errors, 'readonly' => $readonly]) ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if (!empty($cfg['consent']['required'])): ?>
        <div class="lf__field lf__field--full lf__formconsent<?= isset($errors['_consent']) ? ' has-error' : '' ?>" data-lf-formconsent>
            <div class="lf__consent"><input class="lf__check" type="checkbox" id="q-form-consent" name="form_consent" value="1"<?= in_array(is_string($input['form_consent'] ?? null) ? $input['form_consent'] : '', ['1', 'on', 'yes', 'true'], true) ? ' checked' : '' ?> required aria-describedby="e-form-consent"<?= isset($errors['_consent']) ? ' aria-invalid="true"' : '' ?><?= $readonly ? ' disabled' : '' ?>>
                <label for="q-form-consent"><?= e(trim((string) ($cfg['consent']['text'] ?? '')) !== '' ? (string) $cfg['consent']['text'] : FormText::get('ui.consent_default')) ?> <span class="lf__req" aria-hidden="true">*</span></label></div>
            <p class="lf__error" id="e-form-consent" data-lf-error<?= isset($errors['_consent']) ? ' role="alert"' : ' hidden' ?>><?= isset($errors['_consent']) ? e((string) $errors['_consent']) : '' ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['_form'])): ?><p class="lf__error lf__error--form" role="alert"><?= e((string) $errors['_form']) ?></p><?php endif; ?>

    <?php if ($mode !== 'print'): ?>
        <p class="lf__note"><?= e(FormText::get('ui.required_note')) ?></p>
        <?php
        $note = trim((string) ($cfg['privacy_note'] ?? ''));
        $link = '<a href="' . e(url('privacy')) . '">' . e(FormText::get('ui.privacy_link')) . '</a>';
        ?>
        <p class="lf__privacy"><?= icon('lock', 'icon--sm') ?> <span><?= $note !== '' ? e($note) : str_replace('{link}', $link, e(FormText::get('ui.privacy_note'))) ?></span></p>
        <p class="lf__draft" data-lf-draft hidden><?= e(FormText::get('ui.draft_notice')) ?> <button class="btn btn--link" type="button" data-lf-draft-clear><?= e(FormText::get('ui.draft_clear')) ?></button></p>
        <p class="visually-hidden" role="status" aria-live="polite" data-lf-live></p>
        <div class="lf__actions">
            <button class="btn btn--ghost lf__prev" type="button" data-lf-prev hidden><?= icon('arrow-left', 'icon--sm') ?> <?= e(FormText::get('ui.back')) ?></button>
            <?php if ($stepped): ?><button class="btn btn--primary lf__next" type="button" data-lf-next hidden><?= e(FormText::get('ui.next')) ?> <?= icon('arrow-right', 'icon--sm') ?></button><?php endif; ?>
            <button class="btn btn--primary btn--lg lf__submit" type="submit" data-lf-submit data-loading-text="<?= e(FormText::get('ui.submitting')) ?>"<?= $mode === 'preview' ? ' disabled' : '' ?>><?= e($submitLabel) ?></button>
            <?php if (!empty($cfg['allow_print']) && $mode !== 'preview'): ?><button class="btn btn--link lf__print" type="button" data-lf-print hidden><?= e(FormText::get('ui.print_button')) ?></button><?php endif; ?>
        </div>
    <?php endif; ?>
</form>
