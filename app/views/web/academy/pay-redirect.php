<?php
/**
 * "Taking you to the secure Pesapal page": a meta refresh plus a visible button, because a 302 after a form post is blocked by the CSP form-action in Chrome and Edge
 * (same reason as web/shop/redirecting.php). Vars: $link (the Pesapal hosted page, already checked by PesapalClient: https on pesapal.com, or the local mock in development).
 */
$this->start('head'); ?>
<meta http-equiv="refresh" content="0;url=<?= e($link) ?>">
<?php $this->end(); ?>
<section class="section sh-redirect">
    <div class="container container--narrow sh-redirect__box" data-no-justify>
        <span class="sh-redirect__icon"><?= icon('lock', 'icon--xl') ?></span>
        <h1 class="sh-redirect__title">Taking you to the secure payment page</h1>
        <p>You will pay on Pesapal with mobile money or a card. You come back here automatically when you are done.</p>
        <p><a class="btn btn--cta btn--caps btn--lg" href="<?= e($link) ?>" rel="noopener"><?= icon('lock', 'icon--sm') ?> Continue to Pesapal</a></p>
        <p class="sh-redirect__note">Never share your PIN or card code with anyone. Lumina Africa Foundation never asks for it.</p>
    </div>
</section>
