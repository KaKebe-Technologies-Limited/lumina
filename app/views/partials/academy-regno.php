<?php
/**
 * The registration number as a WARNING box: big, with a copy button and the link to track the application. Vars: $regNo (required), $compact (bool, default false).
 * [data-copy-regno] is handled by assets/js/academy.js (the page must load it: $this->start('scripts')).
 */
$compact = !empty($compact);
?>
<div class="ac-regwarn<?= $compact ? ' ac-regwarn--compact' : '' ?>" role="alert">
    <p class="ac-regwarn__title"><?= icon('alert-triangle', 'icon--sm') ?> Important: copy and keep this number</p>
    <p class="ac-regwarn__number" data-regno><?= e($regNo) ?></p>
    <p class="ac-regwarn__text">This is your <strong>registration number</strong>. You will need it to <strong>sign in</strong> and to <strong>track the status of your account</strong> later. We cannot always find your account without it, so write it down or take a screenshot now.</p>
    <p class="ac-regwarn__actions">
        <button class="btn btn--cta btn--sm" type="button" data-copy-regno hidden><?= icon('copy', 'icon--sm') ?> <span data-copy-label>Copy the number</span></button>
        <button class="btn btn--outline btn--sm" type="button" data-print-regno hidden><?= icon('printer', 'icon--sm') ?> Print</button>
        <a class="btn btn--ghost btn--sm" href="<?= e(url('academy/track')) ?>"><?= icon('search', 'icon--sm') ?> Track my application</a>
    </p>
</div>
