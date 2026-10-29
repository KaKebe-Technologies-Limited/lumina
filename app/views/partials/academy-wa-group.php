<?php
/**
 * "Join our LAF Academy WhatsApp support group" (link: Settings > LAF Academy; nothing is shown while the link is empty or not a WhatsApp group link).
 * Vars: $style = 'line' (a sentence with a link, for forms) | 'panel' (a sidebar panel of the learner portal) | 'band' (a full-width strip of the public Academy page).
 */
$waGroup = \App\Services\Academy\LearnerAuth::whatsappGroup();
if ($waGroup === null) {
    return;
}
$waStyle = $style ?? 'line';
$waLink = 'href="' . e($waGroup) . '" target="_blank" rel="noopener noreferrer"';
?>
<?php if ($waStyle === 'band'): ?>
<section class="section section--sm" aria-labelledby="ac-wagroup-title">
    <div class="container container--narrow">
        <header class="section-head section-head--center">
            <span class="eyebrow">Learn together</span>
            <h2 class="section-head__title" id="ac-wagroup-title">Join our Academy WhatsApp group</h2>
            <p class="section-head__lead">Meet other learners, get quick help from the Academy team and hear about new courses and classes. Everyone is welcome.</p>
        </header>
        <p class="ac-center"><a class="btn btn--cta btn--caps" <?= $waLink ?>><?= icon('whatsapp', 'icon--sm') ?> Join the WhatsApp group</a></p>
    </div>
</section>
<?php elseif ($waStyle === 'panel'): ?>
<section class="ac-panel" data-reveal>
    <h2 class="ac-panel__title"><?= icon('whatsapp', 'icon--sm') ?> Join our WhatsApp group</h2>
    <p>Meet other learners and get quick help and news from the Academy team.</p>
    <p><a class="btn btn--cta btn--sm" <?= $waLink ?>>Join the group</a></p>
</section>
<?php else: ?>
<p class="ac-form__alt"><?= icon('whatsapp', 'icon--sm') ?> New here? <a <?= $waLink ?>>Join our Academy WhatsApp support group</a> to meet other learners and get quick help.</p>
<?php endif; ?>
