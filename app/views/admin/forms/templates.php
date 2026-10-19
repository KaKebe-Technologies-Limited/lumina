<?php
/**
 * Admin > Forms > New form: the template picker. Vars: $templates (FormTemplates::listing()), $existing (id => title of forms that can be duplicated).
 * One form: the admin chooses a card (radio) and names the form. POSTs to admin/forms with source = blank | <template key> | duplicate (+ copy_id) and title.
 * Works without JavaScript; assets/js/admin-form-builder.js only fills in the suggested name and highlights the choice.
 */
$kindText = ['general' => 'General', 'job' => 'Job vacancies', 'intake' => 'Private intake', 'survey' => 'Survey'];
$cards = array_merge([['key' => 'blank', 'title' => 'Blank form', 'description' => 'Start with an empty page and add your own questions one by one.', 'icon' => 'plus', 'kind' => 'general', 'pages' => 1, 'fields' => 0]], $templates);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-form-builder.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-forms">
    <?= partial('partials/admin/page-header', [
        'title' => 'New form',
        'subtitle' => 'Pick a starting point. Every question can be changed, added or removed afterwards.',
        'actions' => [['label' => 'Back to forms', 'url' => url('admin/forms'), 'class' => 'btn--outline', 'icon' => 'arrow-left']],
    ]) ?>

    <form class="tp" method="post" action="<?= e(url('admin/forms')) ?>" data-template-picker>
        <?= csrf_field() ?>
        <fieldset class="tp__fieldset">
            <legend class="tp__legend">1. Choose a template</legend>
            <div class="tp__grid">
                <?php foreach ($cards as $i => $t): $kind = (string) ($t['kind'] ?? 'general'); ?>
                    <div class="tp__card">
                        <input class="tp__radio" type="radio" name="source" id="tp-<?= e($t['key']) ?>" value="<?= e($t['key']) ?>" data-title="<?= e($t['key'] === 'blank' ? '' : $t['title']) ?>"<?= $i === 0 ? ' checked' : '' ?>>
                        <label class="tp__label" for="tp-<?= e($t['key']) ?>">
                            <span class="tp__icon"><?= icon((string) $t['icon']) ?></span>
                            <span class="tp__name"><?= e($t['title']) ?></span>
                            <span class="tp__text"><?= e($t['description']) ?></span>
                            <span class="tp__meta">
                                <?php if ((int) $t['fields'] > 0): ?><span><?= (int) $t['fields'] ?> questions<?= (int) $t['pages'] > 1 ? ' in ' . (int) $t['pages'] . ' steps' : '' ?></span><?php else: ?><span>No questions yet</span><?php endif; ?>
                                <?php if ($kind !== 'general'): ?><span class="tp__tag"><?= e($kindText[$kind] ?? $kind) ?></span><?php endif; ?>
                            </span>
                            <span class="tp__tick" aria-hidden="true"><?= icon('check') ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
                <?php if ($existing !== []): ?>
                    <div class="tp__card tp__card--copy">
                        <input class="tp__radio" type="radio" name="source" id="tp-copy" value="duplicate" data-title="Copy of <?= e((string) reset($existing)) ?>" data-copy-of>
                        <label class="tp__label" for="tp-copy">
                            <span class="tp__icon"><?= icon('copy') ?></span>
                            <span class="tp__name">Copy one of your forms</span>
                            <span class="tp__text">Start from a form you already built. Its answers are not copied.</span>
                            <span class="tp__meta"><span><?= count($existing) ?> form<?= count($existing) === 1 ? '' : 's' ?> to copy</span></span>
                            <span class="tp__tick" aria-hidden="true"><?= icon('check') ?></span>
                        </label>
                        <div class="tp__copy-select field">
                            <label class="field__label" for="tp-copy-which">Which form?</label>
                            <select class="select" id="tp-copy-which" name="copy_id" data-copy-select>
                                <?php foreach ($existing as $fid => $title): ?><option value="<?= (int) $fid ?>" data-title="Copy of <?= e($title) ?>"><?= e($title) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </fieldset>

        <div class="tp__bar">
            <div class="field tp__name-field">
                <label class="field__label" for="tp-title">2. Name your form</label>
                <input class="input" id="tp-title" type="text" name="title" maxlength="190" required placeholder="For example: Volunteer sign-up 2027" autocomplete="off" data-template-title>
                <span class="field__help">Visitors see this as the title. You can change it any time.</span>
            </div>
            <button class="btn btn--cta btn--lg" type="submit"><?= icon('arrow-right', 'icon--sm') ?> Create the form</button>
        </div>
    </form>
</div>
