<?php
/**
 * DONATE. Copy: content('donate', ...). Account details come from Settings > Donations (each hidden while empty).
 * Vars: $faqs, $hasMethod, $currencies (code => code), $selectedCurrency, $methods (method => label).
 * The pledge form posts to /donate/pledge and records a PLEDGE (status "pledged"); it never takes a payment. It is pre-filled from
 * ?amount=&currency= (donate_prefill(): validated, bounded, escaped when printed) so the home-page donation panel can hand over its amount.
 */

use App\Controllers\Web\HomeSupport as H;

$heroImg = content_img('donate', 'hero.image');
$donateUrl = trim((string) setting('donate_url', ''));
$heroUrl = H::href('donate', 'hero.cta_url', $donateUrl);
$heroLabel = trim((string) content('donate', 'hero.cta_label'));
$actions = $heroUrl !== '' && $heroLabel !== '' ? [['label' => $heroLabel, 'url' => $heroUrl, 'class' => 'btn--cta btn--caps']] : [];
$cta2 = content_btn('donate', 'hero.cta2');
if ($cta2['has'] && (section_on('donate', 'pledge') || $cta2['url'] !== '#pledge')) {
    $actions[] = ['label' => $cta2['label'], 'url' => $cta2['url'], 'class' => 'btn--photo btn--caps'];
}

$intro = trim((string) setting('donate_intro', ''));
$bank = trim((string) setting('donate_bank_details', ''));
$mobile = trim((string) setting('donate_mobile_money', ''));
$paypal = trim((string) setting('donate_paypal_email', ''));
$copy = (string) content('donate', 'ways.copy_label');
$copied = (string) content('donate', 'ways.copied_label');
$emptyBtn = content_btn('donate', 'ways.empty_cta');
$onlineExternal = $donateUrl !== '' && is_external($donateUrl);

// pre-fill of the pledge form: ?amount=50&currency=USD (never overrides what the visitor typed before a failed submission)
$pre = donate_prefill(array_keys($currencies));
$preAmount = ($pre['amount'] !== '' && (float) $pre['amount'] >= 1 && (float) $pre['amount'] <= 1000000000) ? $pre['amount'] : '';
$formCurrency = old('currency', '') !== '' ? $selectedCurrency : ($pre['currency'] !== '' ? $pre['currency'] : $selectedCurrency);

// quick-amount buttons: the (editable) amounts of the home donation panel, in that panel's currency; suggestions only
$chipCurrency = in_array((string) content('global', 'donate_panel.currency'), array_keys($currencies), true) ? (string) content('global', 'donate_panel.currency') : 'USD';
$chips = [];
foreach (content_list('global', 'donate_panel.amounts') as $row) {
    $n = (float) ($row['amount'] ?? 0);
    if ($n >= 1 && $n <= 1000000000) {
        $chips[] = rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
$chipsLabel = trim((string) content('donate', 'pledge.chips_label'));
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('donate', 'hero.eyebrow'),
    'title'   => (string) content('donate', 'hero.title'),
    'lead'    => (string) content('donate', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 30%'] : null,
    'actions' => $actions,
]) ?>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/donate.js')) ?>" defer></script><?php $this->end(); ?>

<?php /* ------------------------------------------------------------------ WAYS TO GIVE */ ?>
<section class="section donate-ways" id="ways" aria-labelledby="donate-ways-title">
    <div class="container">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('donate', 'ways.eyebrow'), 'title' => (string) content('donate', 'ways.title'), 'lead' => $intro, 'align' => 'center', 'id' => 'donate-ways-title']) ?>
        <?php if ($hasMethod): ?>
            <div class="ia-cards donate-ways__grid">
                <?php if ($donateUrl !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('heart') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.online_title')) ?></h3></div>
                        <p class="donate-way__cta"><a class="btn btn--cta btn--caps" href="<?= e(link_url($donateUrl)) ?>"<?= $onlineExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($heroLabel !== '' ? $heroLabel : (string) content('donate', 'ways.online_title')) ?> <?= icon($onlineExternal ? 'external-link' : 'arrow-right', 'icon--sm') ?></a></p>
                    </article>
                <?php endif; ?>
                <?php if ($bank !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('banknote') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.bank_title')) ?></h3></div>
                        <div class="donate-details" id="donate-bank"><?= nl2br(e($bank), false) ?></div>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--caps btn--sm" type="button" data-copy="<?= e($bank) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
                <?php if ($mobile !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('phone') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.mobile_title')) ?></h3></div>
                        <div class="donate-details" id="donate-mobile"><?= nl2br(e($mobile), false) ?></div>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--caps btn--sm" type="button" data-copy="<?= e($mobile) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
                <?php if ($paypal !== ''): ?>
                    <article class="feature-card donate-way" data-reveal>
                        <span class="feature-card__icon"><?= icon('globe') ?></span>
                        <div class="feature-card__body"><h3 class="feature-card__title"><?= e(content('donate', 'ways.paypal_title')) ?></h3></div>
                        <p class="donate-details"><?= e(content('donate', 'ways.paypal_text')) ?> <a href="mailto:<?= e($paypal) ?>"><?= e($paypal) ?></a></p>
                        <p class="donate-way__cta"><button class="btn btn--outline btn--caps btn--sm" type="button" data-copy="<?= e($paypal) ?>" data-copied="<?= e($copied) ?>" hidden><?= icon('copy', 'icon--sm') ?> <span><?= e($copy) ?></span></button></p>
                    </article>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?= partial('partials/empty-state', [
                'icon'    => 'heart',
                'title'   => strip_highlight((string) content('donate', 'ways.title')),
                'text'    => (string) content('donate', 'ways.empty_text'),
                'actions' => $emptyBtn['has'] ? [['label' => $emptyBtn['label'], 'url' => $emptyBtn['url'], 'class' => 'btn--primary']] : [],
            ]) ?>
        <?php endif; ?>
    </div>
</section>

<?php /* ------------------------------------------------------------------ WHAT YOUR SUPPORT DOES */ ?>
<?php
$supTitle = trim((string) content('donate', 'support.title'));
// the live wording repeats the page title ("Support Us") as this heading: show it once, the section keeps an accessible name
$supDup = mb_strtolower(strip_highlight($supTitle)) === mb_strtolower(trim(strip_highlight((string) content('donate', 'hero.title'))));
section_begin('donate', 'support', ['class' => 'section--tint donate-support'] + ($supDup ? ['attrs' => ['aria-label' => strip_highlight($supTitle)]] : ['labelledby' => 'donate-support-title']));
$cards = content_list('donate', 'support.items'); $supportUrl = H::href('donate', 'support.cta_url', $donateUrl); $supportLabel = trim((string) content('donate', 'support.cta_label')); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('donate', 'support.eyebrow'), 'title' => $supDup ? '' : $supTitle, 'lead' => (string) content('donate', 'support.text'), 'align' => 'center', 'id' => 'donate-support-title']) ?>
    <?php if ($cards): ?>
        <div class="ia-cards donate-support__grid">
            <?php foreach ($cards as $c): if (trim((string) ($c['title'] ?? '')) === '') { continue; } $cimg = img_url((string) ($c['image'] ?? '')); ?>
                <?php if ($cimg !== ''): ?>
                    <article class="card donate-card" data-reveal>
                        <div class="card__media"><img src="<?= e($cimg) ?>" alt="<?= e($c['image_alt'] ?? '') ?>" loading="lazy" decoding="async"></div>
                        <div class="card__body card__body--chip">
                            <span class="card__chip"><?= icon(safe_icon((string) ($c['icon'] ?? ''), 'heart'), 'icon--lg') ?></span>
                            <h3 class="card__title"><?= e($c['title']) ?></h3>
                            <?php if (trim((string) ($c['text'] ?? '')) !== ''): ?><p class="card__text"><?= e($c['text']) ?></p><?php endif; ?>
                        </div>
                    </article>
                <?php else: ?>
                    <?= partial('partials/feature-card', ['icon' => (string) ($c['icon'] ?? ''), 'title' => (string) $c['title'], 'text' => (string) ($c['text'] ?? ''), 'class' => 'donate-card']) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($supportUrl !== '' && $supportLabel !== ''): ?>
        <div class="section__footer"><a class="btn btn--cta btn--caps btn--lg" href="<?= e($supportUrl) ?>"<?= is_external($supportUrl) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= icon('heart', 'icon--sm') ?> <?= e($supportLabel) ?></a></div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ PLEDGE FORM */ ?>
<?php
$fieldMeta = [
    'donor_name'  => ['id' => 'f-donor_name', 'label' => (string) content('donate', 'pledge.name_label')],
    'donor_email' => ['id' => 'f-donor_email', 'label' => (string) content('donate', 'pledge.email_label')],
    'donor_phone' => ['id' => 'f-donor_phone', 'label' => (string) content('donate', 'pledge.phone_label')],
    'method'      => ['id' => 'f-method', 'label' => (string) content('donate', 'pledge.method_label')],
    'amount'      => ['id' => 'f-amount', 'label' => (string) content('donate', 'pledge.amount_label')],
    'currency'    => ['id' => 'f-currency', 'label' => (string) content('donate', 'pledge.currency_label')],
    'message'     => ['id' => 'f-message', 'label' => (string) content('donate', 'pledge.message_label')],
];
$pledgeEyebrow = trim((string) content('donate', 'pledge.eyebrow'));
$pledgeText = trim((string) content('donate', 'pledge.text'));
$pledgeNote = trim((string) content('donate', 'pledge.note'));
section_begin('donate', 'pledge', ['class' => 'donate-pledge', 'labelledby' => 'donate-pledge-title']); ?>
    <div class="ia-panel ia-panel--split">
        <div class="ia-panel__intro" data-reveal>
            <?php if ($pledgeEyebrow !== ''): ?><span class="eyebrow"><?= e($pledgeEyebrow) ?></span><?php endif; ?>
            <h2 id="donate-pledge-title"><?= highlight((string) content('donate', 'pledge.title')) ?></h2>
            <?php if ($pledgeText !== ''): ?><div class="lead"><?= nl2p($pledgeText) ?></div><?php endif; ?>
            <?php if ($pledgeNote !== ''): ?>
                <div class="donate-note" role="note"><?= icon('info') ?><p><?= e($pledgeNote) ?></p></div>
            <?php endif; ?>
            <?php $onlineLabel = trim((string) content('global', 'donate_panel.online_label')); if ($donateUrl !== '' && $onlineLabel !== ''): ?>
                <p><a class="btn btn--outline-light btn--caps" href="<?= e(link_url($donateUrl)) ?>"<?= $onlineExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($onlineLabel) ?> <?= icon($onlineExternal ? 'external-link' : 'arrow-right', 'icon--sm') ?></a></p>
            <?php endif; ?>
        </div>
        <div data-reveal>
            <form class="form-card ia-form" method="post" action="<?= e(url('donate/pledge')) ?>" novalidate>
                <?= partial('web/inner-a/_error_summary', ['title' => (string) content('donate', 'pledge.error_title'), 'fields' => $fieldMeta, 'sid' => 'pledge']) ?>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'donor_name', 'label' => $fieldMeta['donor_name']['label'], 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_email', 'label' => $fieldMeta['donor_email']['label'], 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_phone', 'label' => $fieldMeta['donor_phone']['label'], 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                    <?= partial('partials/form-field', ['name' => 'method', 'label' => $fieldMeta['method']['label'], 'type' => 'select', 'required' => true, 'options' => $methods, 'value' => (string) old('method', 'bank')]) ?>
                    <?= partial('partials/form-field', ['name' => 'amount', 'label' => $fieldMeta['amount']['label'], 'required' => true, 'attrs' => ['inputmode' => 'decimal', 'data-pledge-amount' => true], 'maxlength' => 15, 'value' => (string) old('amount', $preAmount)]) ?>
                    <?= partial('partials/form-field', ['name' => 'currency', 'label' => $fieldMeta['currency']['label'], 'type' => 'select', 'required' => true, 'options' => $currencies, 'value' => $formCurrency, 'attrs' => ['data-pledge-currency' => true]]) ?>
                    <?php if ($chips): ?>
                        <div class="field field--full donate-chips" data-pledge-chips hidden>
                            <?php if ($chipsLabel !== ''): ?><span class="field__label" id="pledge-chips-label"><?= e($chipsLabel) ?></span><?php endif; ?>
                            <div class="donate-chips__list" role="group"<?= $chipsLabel !== '' ? ' aria-labelledby="pledge-chips-label"' : '' ?>>
                                <?php foreach ($chips as $amt): ?>
                                    <button class="donate-chip" type="button" data-chip-amount="<?= e($amt) ?>" data-chip-currency="<?= e($chipCurrency) ?>" aria-pressed="false"><?= e($chipCurrency . ' ' . number_format((float) $amt, str_contains($amt, '.') ? 2 : 0)) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => $fieldMeta['message']['label'], 'type' => 'textarea', 'rows' => 4, 'maxlength' => 2000, 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'is_anonymous', 'label' => (string) content('donate', 'pledge.anonymous_label'), 'type' => 'checkbox', 'class' => 'field--full']) ?>
                </div>
                <div class="form-actions">
                    <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e(content('donate', 'pledge.sending_label')) ?>"><?= icon('heart', 'icon--sm') ?> <?= e(content('donate', 'pledge.submit_label')) ?></button>
                    <?php if (trim((string) content('donate', 'pledge.required_note')) !== ''): ?><span class="form-note"><?= e(content('donate', 'pledge.required_note')) ?></span><?php endif; ?>
                </div>
            </form>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ with photo */ ?>
<?php if ($faqs): ?>
<?php section_begin('donate', 'faq', ['class' => 'section--tint donate-faq', 'attrs' => ['aria-label' => strip_highlight((string) content('donate', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('donate', 'faq.image'),
        'eyebrow' => (string) content('donate', 'faq.eyebrow'),
        'title'   => (string) content('donate', 'faq.title'),
        'prefix'  => 'donate-faq',
    ]) ?>
<?php section_end(); ?>
<?php endif; ?>
