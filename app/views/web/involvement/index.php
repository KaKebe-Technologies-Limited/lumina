<?php
/**
 * INVOLVEMENT. Copy: content('involvement', ...). Vars: $types (type => label), $selectedType, $faqs.
 * The form posts to /involvement (honeypot + throttle + CSRF) and is stored in Admin > Involvement. Order: hero -> how (photos + text) ->
 * ways (cards) -> join form on a deep navy panel -> FAQ with photo -> donate band.
 */

$heroImg = content_img('involvement', 'hero.image');
$heroBtn = content_btn('involvement', 'hero.cta');
$ways = content_list('involvement', 'ways.items');
$typeValue = $selectedType !== '' ? $selectedType : array_key_first($types);
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('involvement', 'hero.eyebrow'),
    'title'   => (string) content('involvement', 'hero.title'),
    'lead'    => (string) content('involvement', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 42%'] : null,
    'actions' => $heroBtn['has'] ? [['label' => $heroBtn['label'], 'url' => $heroBtn['url'], 'class' => 'btn--cta btn--caps']] : [],
]) ?>

<?php /* ------------------------------------------------------------------ HOW TO GET INVOLVED */ ?>
<?php
$howImgs = [content_img('involvement', 'how.image'), content_img('involvement', 'how.image2')];
$howHasMedia = (bool) array_filter($howImgs, static fn(array $i): bool => $i['has']);
section_begin('involvement', 'how', ['class' => 'involvement-how', 'labelledby' => 'inv-how-title']); ?>
    <div class="split split--start<?= $howHasMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $howImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('involvement', 'how.eyebrow'), 'title' => (string) content('involvement', 'how.title'), 'id' => 'inv-how-title']) ?>
            <div class="lead"><?= nl2p((string) content('involvement', 'how.text')) ?></div>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WAYS TO TAKE PART */ ?>
<?php $waysTitle = trim((string) content('involvement', 'ways.title')); section_begin('involvement', 'ways', ['class' => 'section--tint involvement-ways', 'labelledby' => $waysTitle !== '' ? 'inv-ways-title' : null]); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('involvement', 'ways.eyebrow'), 'title' => $waysTitle, 'align' => 'center', 'id' => 'inv-ways-title']) ?>
    <?php if ($ways): ?>
        <div class="ia-cards">
            <?php foreach ($ways as $w): if (trim((string) ($w['title'] ?? '')) === '') { continue; }
                $href = link_url((string) ($w['cta_url'] ?? '')); $label = trim((string) ($w['cta_label'] ?? '')); ?>
                <article class="feature-card ia-way" data-reveal>
                    <span class="feature-card__icon"><?= icon(safe_icon((string) ($w['icon'] ?? ''), 'heart')) ?></span>
                    <div class="feature-card__body">
                        <h3 class="feature-card__title"><?= e($w['title']) ?></h3>
                        <?php if (trim((string) ($w['tagline'] ?? '')) !== ''): ?><p class="ia-way__tagline"><?= e($w['tagline']) ?></p><?php endif; ?>
                        <?php if (trim((string) ($w['text'] ?? '')) !== ''): ?><p class="feature-card__text"><?= e($w['text']) ?></p><?php endif; ?>
                    </div>
                    <?php if ($href !== '' && $label !== ''): ?><p class="ia-way__cta"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e($label) ?></a></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ JOIN FORM (always on) */ ?>
<?php
$fieldMeta = [
    'name'             => ['id' => 'f-name', 'label' => (string) content('involvement', 'form.name_label')],
    'email'            => ['id' => 'f-email', 'label' => (string) content('involvement', 'form.email_label')],
    'phone'            => ['id' => 'f-phone', 'label' => (string) content('involvement', 'form.phone_label')],
    'involvement_type' => ['id' => 'f-involvement_type', 'label' => (string) content('involvement', 'form.type_label')],
    'organisation'     => ['id' => 'f-organisation', 'label' => (string) content('involvement', 'form.org_label')],
    'message'          => ['id' => 'f-message', 'label' => (string) content('involvement', 'form.message_label')],
];
$joinSub = trim((string) content('involvement', 'form.subtitle'));
$joinText = trim((string) content('involvement', 'form.text'));
$joinEyebrow = trim((string) content('involvement', 'form.eyebrow'));
section_begin('involvement', 'form', ['id' => 'join', 'class' => 'involvement-join', 'labelledby' => 'inv-join-title']); ?>
    <div class="ia-panel ia-panel--split">
        <div class="ia-panel__intro" data-reveal>
            <?php if ($joinEyebrow !== ''): ?><span class="eyebrow"><?= e($joinEyebrow) ?></span><?php endif; ?>
            <h2 id="inv-join-title"><?= highlight((string) content('involvement', 'form.title')) ?></h2>
            <?php if ($joinSub !== ''): ?><p class="involvement-join__sub"><?= e($joinSub) ?></p><?php endif; ?>
            <?php if ($joinText !== ''): ?><div><?= nl2p($joinText) ?></div><?php endif; ?>
        </div>
        <div data-reveal>
            <form class="form-card ia-form" method="post" action="<?= e(url('involvement')) ?>" novalidate>
                <?= partial('web/inner-a/_error_summary', ['title' => (string) content('involvement', 'form.error_title'), 'fields' => $fieldMeta, 'sid' => 'inv']) ?>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => $fieldMeta['name']['label'], 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => $fieldMeta['email']['label'], 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => $fieldMeta['phone']['label'], 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                    <?= partial('partials/form-field', ['name' => 'involvement_type', 'label' => $fieldMeta['involvement_type']['label'], 'type' => 'select', 'required' => true, 'options' => $types, 'value' => $typeValue]) ?>
                    <?= partial('partials/form-field', ['name' => 'organisation', 'label' => $fieldMeta['organisation']['label'], 'autocomplete' => 'organization', 'maxlength' => 190, 'help' => (string) content('involvement', 'form.org_help'), 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => $fieldMeta['message']['label'], 'type' => 'textarea', 'rows' => 5, 'maxlength' => 3000, 'class' => 'field--full']) ?>
                </div>
                <div class="form-actions">
                    <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e(content('involvement', 'form.sending_label')) ?>"><?= e(content('involvement', 'form.submit_label')) ?></button>
                    <?php if (trim((string) content('involvement', 'form.required_note')) !== ''): ?><span class="form-note"><?= e(content('involvement', 'form.required_note')) ?></span><?php endif; ?>
                </div>
            </form>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ with photo */ ?>
<?php if ($faqs): ?>
<?php section_begin('involvement', 'faq', ['class' => 'section--tint involvement-faq', 'attrs' => ['aria-label' => strip_highlight((string) content('involvement', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('involvement', 'faq.image'),
        'eyebrow' => (string) content('involvement', 'faq.eyebrow'),
        'title'   => (string) content('involvement', 'faq.title'),
        'prefix'  => 'inv-faq',
    ]) ?>
<?php section_end(); ?>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
