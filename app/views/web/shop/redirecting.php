<?php
/**
 * "Taking you to the secure payment page" (see App\Controllers\Web\GatewayRedirects: a meta refresh, because a 302 after a form post is blocked by the CSP form-action
 * in Chrome and Edge). Vars: $link (the hosted payment page, already checked: https on flutterwave.com, or the local mock in development).
 */
$this->start('head'); ?>
<meta http-equiv="refresh" content="0;url=<?= e($link) ?>">
<?php $this->end(); ?>
<section class="section sh-redirect">
    <div class="container container--narrow sh-redirect__box" data-no-justify>
        <span class="sh-redirect__icon"><?= icon('lock', 'icon--xl') ?></span>
        <h1 class="sh-redirect__title"><?= e(content('shop', 'payment.redirect_title')) ?></h1>
        <p><?= e(content('shop', 'payment.redirect_text')) ?></p>
        <p><a class="btn btn--cta btn--caps btn--lg" href="<?= e($link) ?>" rel="noopener"><?= icon('lock', 'icon--sm') ?> <?= e(content('shop', 'payment.redirect_button')) ?></a></p>
        <p class="sh-redirect__note"><?= e(content('shop', 'checkout.secure_note')) ?></p>
    </div>
</section>
