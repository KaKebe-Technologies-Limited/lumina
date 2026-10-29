<?php
/** The page after Pesapal when the payment cannot be tied to the signed-in learner (another browser, an old link). Identical for every reference: no names, no amounts. Vars: $state. */
?>
<section class="section sh-redirect">
    <div class="container container--narrow sh-redirect__box" data-no-justify>
        <span class="sh-redirect__icon"><?= icon('check-circle', 'icon--xl') ?></span>
        <h1 class="sh-redirect__title">Thank you</h1>
        <p>If you paid, Pesapal tells us within a minute or two and your certificate request is filed automatically. Sign in to the Academy to see where it stands.</p>
        <p><a class="btn btn--cta btn--caps btn--lg" href="<?= e(url('academy/portal/certificates')) ?>">My certificates</a></p>
    </div>
</section>
