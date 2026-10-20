<?php
/**
 * The neutral page after the hosted payment page when the return is not tied to the customer's own payment (no key, a wrong key, an old link). It is identical for
 * every reference and shows no order data (see App\Controllers\Web\GatewayRedirects::returnThanks).
 */
?>
<section class="section sh-redirect">
    <div class="container container--narrow sh-redirect__box" data-no-justify>
        <span class="sh-redirect__icon"><?= icon('shield', 'icon--xl') ?></span>
        <h1 class="sh-redirect__title"><?= e(content('shop', 'payment.return_title')) ?></h1>
        <p><?= e(content('shop', 'payment.return_text')) ?></p>
        <p><a class="btn btn--cta btn--caps btn--lg" href="<?= e(url('shop')) ?>"><?= e(content('shop', 'payment.return_button')) ?></a></p>
    </div>
</section>
