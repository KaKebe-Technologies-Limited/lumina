<?php
/**
 * MEMBERSHIP TAB (/membership). OWNER: WEB agent. Copy: content('membership', ...) (Admin > Pages > Membership); the FEES come from the database
 * (Admin > Membership types) and are always the CURRENT ones. Vars: $types (active types with fee_state / fee_cur / fee_num / fee_text / fee_period /
 * joining_label / full), $open (bool), $instructions (plain text), $faqs.
 * Order: hero -> (closed notice) -> why join -> who can join -> types and fees -> how it works -> how to pay -> FAQ -> call to action.
 * Every block except the types can be switched off in the admin; the registration form itself lives at /membership/register (anchor #register here).
 */

use App\Services\Membership\MembershipService as MS;

$heroImg = content_img('membership', 'hero.image');
$heroBtn = content_btn('membership', 'hero.cta');
$why = array_values(array_filter(content_list('membership', 'why.items'), static fn(array $r): bool => trim((string) ($r['title'] ?? '')) !== ''));
$steps = array_values(array_filter(content_list('membership', 'how.steps'), static fn(array $r): bool => trim((string) ($r['title'] ?? '')) !== ''));
$eligibility = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', trim(str_replace("\r", '', (string) content('membership', 'eligibility.text')))) ?: []), static fn(string $s): bool => $s !== ''));
$closedBtn = content_btn('membership', 'closed.button');
$cta = content_btn('membership', 'cta.button');
$registerHref = link_url((string) content('membership', 'hero.cta_url') ?: '/membership/register');

// the first block under the hero decides the colour of the rounded "sheet" that cuts into the hero photo
$canApply = $open && empty($allFull);        // registration is on and at least one type has a free place
$sheet = !$open ? 'white' : (section_on('membership', 'why') && $why ? 'white' : (section_on('membership', 'eligibility') && $eligibility ? 'tint' : 'cool'));
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('membership', 'hero.eyebrow'),
    'title'   => (string) content('membership', 'hero.title'),
    'lead'    => $open ? (string) content('membership', 'hero.lead') : (string) content('membership', 'closed.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 35%'] : null,
    'actions' => $canApply && $heroBtn['has'] ? [['label' => $heroBtn['label'], 'url' => $heroBtn['url'], 'class' => 'btn--cta btn--caps']] : [],
    'sheet'   => $sheet,
]) ?>

<?php /* ------------------------------------------------------------------ CLOSED NOTICE (switch in Site settings > Membership) */ ?>
<?php if (!$open): ?>
<section class="section section--sm mb-closed-wrap" aria-labelledby="mb-closed-title">
    <div class="container container--narrow">
        <div class="mb-closed" role="status">
            <span class="mb-closed__icon"><?= icon('lock', 'icon--lg') ?></span>
            <div class="mb-closed__body">
                <h2 class="mb-closed__title" id="mb-closed-title"><?= e(content('membership', 'closed.title')) ?></h2>
                <p><?= e(content('membership', 'closed.text')) ?></p>
                <?php if ($closedBtn['has']): ?><p><a class="btn btn--outline btn--caps" href="<?= e($closedBtn['url']) ?>"><?= e($closedBtn['label']) ?></a></p><?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ WHY JOIN / BENEFITS */ ?>
<?php if ($why): section_begin('membership', 'why', ['class' => 'mb-why', 'labelledby' => 'mb-why-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('membership', 'why.eyebrow'), 'title' => (string) content('membership', 'why.title'), 'lead' => (string) content('membership', 'why.intro'), 'align' => 'center', 'id' => 'mb-why-title']) ?>
    <div class="mb-cards">
        <?php foreach ($why as $r): ?>
            <?= partial('partials/feature-card', ['icon' => (string) ($r['icon'] ?? ''), 'title' => (string) $r['title'], 'text' => (string) ($r['text'] ?? ''), 'class' => 'mb-benefit']) ?>
        <?php endforeach; ?>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ WHO CAN JOIN */ ?>
<?php if ($eligibility): section_begin('membership', 'eligibility', ['class' => 'section--tint mb-eligibility', 'labelledby' => 'mb-elig-title']); ?>
    <div class="split split--start mb-eligibility__split">
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('membership', 'eligibility.eyebrow'), 'title' => (string) content('membership', 'eligibility.title'), 'id' => 'mb-elig-title', 'class' => 'mb-0']) ?>
        </div>
        <ul class="mb-checklist" data-reveal>
            <?php foreach ($eligibility as $line): ?>
                <li><span class="mb-checklist__tick"><?= icon('check') ?></span><span><?= nl2br(e($line), false) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ MEMBERSHIP TYPES AND CURRENT FEES (from the database) */ ?>
<?php section_begin('membership', 'types', ['class' => 'section--cool mb-types-section', 'id' => 'types', 'labelledby' => 'mb-types-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('membership', 'types.eyebrow'), 'title' => (string) content('membership', 'types.title'), 'lead' => (string) content('membership', 'types.intro'), 'align' => 'center', 'id' => 'mb-types-title']) ?>
    <?php if ($types === []): ?>
        <?= partial('partials/empty-state', ['title' => content('membership', 'types.empty_title'), 'text' => content('membership', 'types.empty_text'), 'icon' => 'users']) ?>
    <?php else: ?>
        <?php if (!empty($allFull)): ?>
            <div class="mb-closed mb-closed--inline" role="status">
                <span class="mb-closed__icon"><?= icon('users', 'icon--lg') ?></span>
                <div class="mb-closed__body">
                    <h3 class="mb-closed__title"><?= e(content('membership', 'types.full_title')) ?></h3>
                    <p><?= e(content('membership', 'types.full_text')) ?></p>
                </div>
            </div>
        <?php endif; ?>
        <div class="mb-types">
            <?php foreach ($types as $t): $eligNote = trim((string) $t['eligibility']); $desc = trim((string) $t['description']); ?>
                <article class="mb-type" data-reveal>
                    <h3 class="mb-type__name"><?= e($t['name']) ?></h3>
                    <p class="mb-type__fee mb-type__fee--<?= e($t['fee_state']) ?>">
                        <?php if ($t['fee_state'] === 'amount'): ?>
                            <span class="mb-type__cur"><?= e($t['fee_cur']) ?></span> <span class="mb-type__num"><?= e($t['fee_num']) ?></span><?php if ($t['fee_period'] !== ''): ?> <span class="mb-type__per"><?= e($t['fee_period']) ?></span><?php endif; ?>
                        <?php else: ?>
                            <span class="mb-type__state"><?= e($t['fee_text']) ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if ($t['joining_label'] !== ''): ?><p class="mb-type__joining"><?= e($t['joining_label']) ?></p><?php endif; ?>
                    <?php if ($desc !== ''): ?><div class="mb-type__text"><?= nl2p($desc) ?></div><?php endif; ?>
                    <?php if ($eligNote !== ''): ?>
                        <div class="mb-type__elig">
                            <span class="mb-type__elig-label"><?= e(content('membership', 'types.eligibility_label')) ?></span>
                            <?= nl2p($eligNote) ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-type__cta">
                        <?php if ($t['full']): ?>
                            <p class="mb-type__full"><?= e(content('membership', 'types.full_label')) ?></p>
                        <?php elseif ($open): ?>
                            <a class="btn btn--cta btn--caps btn--sm" href="<?= e(url('membership/register', ['type' => $t['id']])) ?>"><?= e(content('membership', 'types.apply_label')) ?><span class="visually-hidden">: <?= e($t['name']) ?></span></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ HOW IT WORKS */ ?>
<?php if ($steps): section_begin('membership', 'how', ['class' => 'section--navy mb-how', 'labelledby' => 'mb-how-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('membership', 'how.eyebrow'), 'title' => (string) content('membership', 'how.title'), 'align' => 'center', 'id' => 'mb-how-title']) ?>
    <ol class="mb-steps" style="--mb-n: <?= count($steps) ?>">
        <?php foreach ($steps as $i => $s): ?>
            <li class="mb-step" data-reveal>
                <span class="mb-step__num" aria-hidden="true"><?= $i + 1 ?></span>
                <h3 class="mb-step__title"><?= e($s['title']) ?></h3>
                <?php if (trim((string) ($s['text'] ?? '')) !== ''): ?><p class="mb-step__text"><?= e($s['text']) ?></p><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ HOW TO PAY (hidden while there are no payment details at all) */ ?>
<?php if ($instructions !== ''): section_begin('membership', 'payment', ['class' => 'mb-payment', 'labelledby' => 'mb-pay-title']); ?>
    <div class="split split--start">
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('membership', 'payment.eyebrow'), 'title' => (string) content('membership', 'payment.title'), 'lead' => (string) content('membership', 'payment.intro'), 'id' => 'mb-pay-title', 'class' => 'mb-0']) ?>
        </div>
        <div class="mb-pay" data-reveal>
            <span class="mb-pay__icon"><?= icon('banknote', 'icon--lg') ?></span>
            <div class="mb-pay__body"><?= nl2p($instructions) ?></div>
        </div>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ FAQ (rows of Admin > FAQs group "membership", else the page's own list; hidden when empty) */ ?>
<?php if ($faqs): section_begin('membership', 'faq', ['class' => 'section--tint mb-faq', 'attrs' => ['aria-label' => strip_highlight((string) content('membership', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', ['faqs' => $faqs, 'image' => [], 'eyebrow' => (string) content('membership', 'faq.eyebrow'), 'title' => (string) content('membership', 'faq.title'), 'prefix' => 'mb-faq']) ?>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ CALL TO ACTION + the #register anchor (the form is /membership/register) */ ?>
<div id="register" class="mb-anchor"></div>
<?php if ($canApply && section_on('membership', 'cta')): ?>
<div class="cta-wrap mb-cta">
    <div class="container">
        <div class="cta" data-reveal>
            <h2 class="cta__title"><?= e(content('membership', 'cta.title')) ?></h2>
            <?php if (trim((string) content('membership', 'cta.text')) !== ''): ?><p class="cta__text"><?= e(content('membership', 'cta.text')) ?></p><?php endif; ?>
            <div class="cta__actions">
                <a class="btn btn--cta btn--caps btn--lg" href="<?= e($cta['has'] ? $cta['url'] : $registerHref) ?>"><?= e($cta['has'] ? $cta['label'] : content('membership', 'hero.cta_label')) ?></a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
