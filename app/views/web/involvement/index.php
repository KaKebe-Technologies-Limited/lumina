<?php
/**
 * INVOLVEMENT (B1). Copy: content('involvement', ...). Vars: $types (type => label), $selectedType, $faqs.
 * The form posts to /involvement (honeypot + throttle + CSRF) and is stored in Admin > Involvement.
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
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
    'actions' => $heroBtn['has'] ? [['label' => $heroBtn['label'], 'url' => $heroBtn['url'], 'class' => 'btn--accent']] : [],
]) ?>

<?php /* ------------------------------------------------------------------ HOW TO GET INVOLVED */ ?>
<?php section_begin('involvement', 'how', ['class' => 'involvement-how', 'labelledby' => 'inv-how-title']); $img = content_img('involvement', 'how.image'); ?>
    <div class="split">
        <div data-reveal>
            <h2 id="inv-how-title"><?= e(content('involvement', 'how.title')) ?></h2>
            <div class="lead mt-4"><?= nl2p((string) content('involvement', 'how.text')) ?></div>
        </div>
        <?php if ($img['has']): ?><div class="split__media home-photo" data-reveal><img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>" loading="lazy"></div><?php endif; ?>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WAYS TO TAKE PART */ ?>
<?php section_begin('involvement', 'ways', ['class' => 'section--tint involvement-ways']); ?>
    <?php if ($ways): ?>
        <div class="involvement-ways__grid">
            <?php foreach ($ways as $w): if (trim((string) ($w['title'] ?? '')) === '') { continue; }
                $href = link_url((string) ($w['cta_url'] ?? '')); $label = trim((string) ($w['cta_label'] ?? '')); ?>
                <article class="involvement-way" data-reveal>
                    <span class="icon-tile" aria-hidden="true"><?= icon((string) ($w['icon'] ?: 'heart'), 'icon--lg') ?></span>
                    <h3><?= e($w['title']) ?></h3>
                    <?php if (trim((string) ($w['tagline'] ?? '')) !== ''): ?><p class="involvement-way__tagline"><?= e($w['tagline']) ?></p><?php endif; ?>
                    <?php if (trim((string) ($w['text'] ?? '')) !== ''): ?><p class="involvement-way__text"><?= e($w['text']) ?></p><?php endif; ?>
                    <?php if ($href !== '' && $label !== ''): ?><p class="involvement-way__cta"><a class="btn btn--primary btn--sm" href="<?= e($href) ?>"><?= e($label) ?> <?= icon('arrow-right', 'icon--arrow') ?></a></p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ JOIN FORM (always on) */ ?>
<section class="section involvement-join" id="join" aria-labelledby="inv-join-title">
    <div class="container">
        <div class="form-split">
            <div data-reveal>
                <h2 id="inv-join-title"><?= e(content('involvement', 'form.title')) ?></h2>
                <?php if (trim((string) content('involvement', 'form.subtitle')) !== ''): ?><p class="lead mt-3 involvement-join__sub"><?= e(content('involvement', 'form.subtitle')) ?></p><?php endif; ?>
                <?php if (trim((string) content('involvement', 'form.text')) !== ''): ?><div class="mt-4 text-muted"><?= nl2p((string) content('involvement', 'form.text')) ?></div><?php endif; ?>
            </div>
            <div class="form-card" data-reveal>
                <form method="post" action="<?= e(url('involvement')) ?>" novalidate>
                    <?= csrf_field() ?>
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => (string) content('involvement', 'form.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'label' => (string) content('involvement', 'form.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'label' => (string) content('involvement', 'form.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                        <?= partial('partials/form-field', ['name' => 'involvement_type', 'label' => (string) content('involvement', 'form.type_label'), 'type' => 'select', 'required' => true, 'options' => $types, 'value' => $typeValue]) ?>
                        <?= partial('partials/form-field', ['name' => 'organisation', 'label' => (string) content('involvement', 'form.org_label'), 'autocomplete' => 'organization', 'maxlength' => 190, 'help' => (string) content('involvement', 'form.org_help'), 'class' => 'field--full']) ?>
                        <?= partial('partials/form-field', ['name' => 'message', 'label' => (string) content('involvement', 'form.message_label'), 'type' => 'textarea', 'rows' => 5, 'maxlength' => 3000, 'class' => 'field--full']) ?>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--accent btn--lg" type="submit" data-loading-text="<?= e(content('involvement', 'form.sending_label')) ?>"><?= e(content('involvement', 'form.submit_label')) ?> <?= icon('arrow-right', 'icon--arrow') ?></button>
                        <?php if (trim((string) content('involvement', 'form.required_note')) !== ''): ?><span class="form-note"><?= e(content('involvement', 'form.required_note')) ?></span><?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php /* ------------------------------------------------------------------ FAQ */ ?>
<?php if ($faqs): ?>
<?php section_begin('involvement', 'faq', ['class' => 'section--tint involvement-faq', 'container' => 'container--narrow', 'labelledby' => 'inv-faq-title']); ?>
    <div class="section__header section__header--center" data-reveal><h2 id="inv-faq-title"><?= e(content('involvement', 'faq.title')) ?></h2></div>
    <?= partial('web/home/_faq', ['faqs' => $faqs, 'prefix' => 'inv-faq']) ?>
<?php section_end(); ?>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
