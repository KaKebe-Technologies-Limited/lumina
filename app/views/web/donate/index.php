<?php
/**
 * DONATE (B1). Copy: content('donate', ...). Account details come from Settings > Donations (each hidden while empty).
 * Vars: $faqs, $hasMethod, $currencies (code => code), $selectedCurrency, $methods (method => label).
 * The pledge form posts to /donate/pledge and records a PLEDGE (status "pledged"); it never takes a payment.
 */

use App\Controllers\Web\HomeSupport as H;

$heroImg = content_img('donate', 'hero.image');
$donateUrl = trim((string) setting('donate_url', ''));
$heroUrl = H::href('donate', 'hero.cta_url', $donateUrl);
$heroLabel = trim((string) content('donate', 'hero.cta_label'));
$actions = $heroUrl !== '' && $heroLabel !== '' ? [['label' => $heroLabel, 'url' => $heroUrl, 'class' => 'btn--accent']] : [];

$intro = trim((string) setting('donate_intro', ''));
$bank = trim((string) setting('donate_bank_details', ''));
$mobile = trim((string) setting('donate_mobile_money', ''));
$paypal = trim((string) setting('donate_paypal_email', ''));
$copy = (string) content('donate', 'ways.copy_label');
$copied = (string) content('donate', 'ways.copied_label');
$emptyBtn = content_btn('donate', 'ways.empty_cta');
$needJs = $bank !== '' || $mobile !== '' || $paypal !== '';
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('donate', 'hero.eyebrow'),
    'title'   => (string) content('donate', 'hero.title'),
    'lead'    => (string) content('donate', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt']] : null,
    'actions' => $actions,
]) ?>

<?php /* ------------------------------------------------------------------ WAYS TO GIVE */ ?>
<section class="section donate-ways" id="ways" aria-labelledby="donate-ways-title">
    <div class="container">
        <div class="section__header section__header--center" data-reveal>
            <h2 id="donate-ways-title"><?= e(content('donate', 'ways.title')) ?></h2>
            <?php if ($intro !== ''): ?><div class="lead"><?= nl2p($intro) ?></div><?php endif; ?>
        </div>
        <?php if ($hasMethod): ?>
            <div class="cards-center donate-ways__grid">
                <?php if ($donateUrl !== ''): ?>
                    <article class="donate-way" data-reveal>
                        <span class="icon-tile" aria-hidden="true"><?= icon('heart', 'icon--lg') ?></span>
                        <h3><?= e(content('donate', 'ways.online_title')) ?></h3>
                        <p class="donate-way__cta"><a class="btn btn--accent" href="<?= e(link_url($donateUrl)) ?>"<?= is_external($donateUrl) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($heroLabel !== '' ? $heroLabel : (string) content('donate', 'ways.online_title')) ?> <?= icon(is_external($donateUrl) ? 'external-link' : 'arrow-right', is_external($donateUrl) ? '' : 'icon--arrow') ?></a></p>
                    </article>
                <?php endif; ?>
                <?php if ($bank !== ''): ?>
                    <article class="donate-way" data-reveal>
                        <span class="icon-tile" aria-hidden="true"><?= icon('banknote', 'icon--lg') ?></span>
                        <h3><?= e(content('donate', 'ways.bank_title')) ?></h3>
                        <div class="donate-details" id="donate-bank"><?= nl2br(e($bank), false) ?></div>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--sm" type="button" data-copy="<?= e($bank) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
                <?php if ($mobile !== ''): ?>
                    <article class="donate-way" data-reveal>
                        <span class="icon-tile" aria-hidden="true"><?= icon('phone', 'icon--lg') ?></span>
                        <h3><?= e(content('donate', 'ways.mobile_title')) ?></h3>
                        <div class="donate-details" id="donate-mobile"><?= nl2br(e($mobile), false) ?></div>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--sm" type="button" data-copy="<?= e($mobile) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
                <?php if ($paypal !== ''): ?>
                    <article class="donate-way" data-reveal>
                        <span class="icon-tile" aria-hidden="true"><?= icon('globe', 'icon--lg') ?></span>
                        <h3><?= e(content('donate', 'ways.paypal_title')) ?></h3>
                        <p class="donate-details"><?= e(content('donate', 'ways.paypal_text')) ?> <a href="mailto:<?= e($paypal) ?>"><?= e($paypal) ?></a></p>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--sm" type="button" data-copy="<?= e($paypal) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'icon'    => 'heart',
                'title'   => (string) content('donate', 'ways.title'),
                'text'    => (string) content('donate', 'ways.empty_text'),
                'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        <?php endif; ?>
    </div>
</section>
<?php if ($needJs): ?>
    <?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/donate.js')) ?>" defer></script><?php $this->end(); ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ WHAT YOUR SUPPORT DOES */ ?>
<?php section_begin('donate', 'support', ['class' => 'section--tint donate-support', 'labelledby' => 'donate-support-title']); $cards = content_list('donate', 'support.items'); $supportUrl = H::href('donate', 'support.cta_url', $donateUrl); $supportLabel = trim((string) content('donate', 'support.cta_label')); ?>
    <div class="section__header section__header--center" data-reveal>
        <h2 id="donate-support-title"><?= e(content('donate', 'support.title')) ?></h2>
        <?php if (trim((string) content('donate', 'support.text')) !== ''): ?><div class="lead"><?= nl2p((string) content('donate', 'support.text')) ?></div><?php endif; ?>
    </div>
    <?php if ($cards): ?>
        <div class="donate-support__grid">
            <?php foreach ($cards as $c): if (trim((string) ($c['title'] ?? '')) === '') { continue; } $cimg = img_url((string) ($c['image'] ?? '')); ?>
                <article class="donate-card" data-reveal>
                    <?php if ($cimg !== ''): ?><div class="donate-card__media"><img src="<?= e($cimg) ?>" alt="<?= e($c['image_alt'] ?? '') ?>" loading="lazy"></div><?php endif; ?>
                    <div class="donate-card__body">
                        <h3><?= e($c['title']) ?></h3>
                        <?php if (trim((string) ($c['text'] ?? '')) !== ''): ?><p><?= e($c['text']) ?></p><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($supportUrl !== '' && $supportLabel !== ''): ?>
        <div class="section__footer"><a class="btn btn--accent btn--lg" href="<?= e($supportUrl) ?>"<?= is_external($supportUrl) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon('heart') ?> <?= e($supportLabel) ?></a></div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ PLEDGE FORM */ ?>
<?php section_begin('donate', 'pledge', ['class' => 'donate-pledge', 'labelledby' => 'donate-pledge-title']); ?>
    <div class="form-split">
        <div data-reveal>
            <h2 id="donate-pledge-title"><?= e(content('donate', 'pledge.title')) ?></h2>
            <?php if (trim((string) content('donate', 'pledge.text')) !== ''): ?><div class="lead mt-4"><?= nl2p((string) content('donate', 'pledge.text')) ?></div><?php endif; ?>
            <?php if (trim((string) content('donate', 'pledge.note')) !== ''): ?>
                <div class="alert alert--info mt-5" role="note"><?= icon('info') ?><div class="alert__body"><?= e(content('donate', 'pledge.note')) ?></div></div>
            <?php endif; ?>
        </div>
        <div class="form-card" data-reveal>
            <form method="post" action="<?= e(url('donate/pledge')) ?>" novalidate>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'donor_name', 'label' => (string) content('donate', 'pledge.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_email', 'label' => (string) content('donate', 'pledge.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_phone', 'label' => (string) content('donate', 'pledge.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                    <?= partial('partials/form-field', ['name' => 'method', 'label' => (string) content('donate', 'pledge.method_label'), 'type' => 'select', 'required' => true, 'options' => $methods, 'value' => (string) old('method', 'bank')]) ?>
                    <?= partial('partials/form-field', ['name' => 'amount', 'label' => (string) content('donate', 'pledge.amount_label'), 'required' => true, 'attrs' => ['inputmode' => 'decimal'], 'maxlength' => 15]) ?>
                    <?= partial('partials/form-field', ['name' => 'currency', 'label' => (string) content('donate', 'pledge.currency_label'), 'type' => 'select', 'required' => true, 'options' => $currencies, 'value' => $selectedCurrency]) ?>
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => (string) content('donate', 'pledge.message_label'), 'type' => 'textarea', 'rows' => 4, 'maxlength' => 2000, 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'is_anonymous', 'label' => (string) content('donate', 'pledge.anonymous_label'), 'type' => 'checkbox', 'class' => 'field--full']) ?>
                </div>
                <div class="form-actions">
                    <button class="btn btn--accent btn--lg" type="submit" data-loading-text="<?= e(content('donate', 'pledge.sending_label')) ?>"><?= icon('heart') ?> <?= e(content('donate', 'pledge.submit_label')) ?></button>
                    <?php if (trim((string) content('donate', 'pledge.required_note')) !== ''): ?><span class="form-note"><?= e(content('donate', 'pledge.required_note')) ?></span><?php endif; ?>
                </div>
            </form>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ */ ?>
<?php if ($faqs): ?>
<?php section_begin('donate', 'faq', ['class' => 'section--tint donate-faq', 'container' => 'container--narrow', 'labelledby' => 'donate-faq-title']); ?>
    <div class="section__header section__header--center" data-reveal><h2 id="donate-faq-title"><?= e(content('donate', 'faq.title')) ?></h2></div>
    <?= partial('web/home/_faq', ['faqs' => $faqs, 'prefix' => 'donate-faq']) ?>
<?php section_end(); ?>
<?php endif; ?>
