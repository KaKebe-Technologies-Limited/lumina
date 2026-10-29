<?php
/**
 * Certificate payment page (docs/PESAPAL.md). Vars: $course, $fee (whole UGX), $currency, $available (Pesapal keys present), $last (the learner's latest payment row or null), $approval.
 * The amount shown is only information: the amount charged is read from the settings again on the server when the button is pressed.
 */
?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Pay for your certificate', 'eyebrow' => 'LAF Academy | ' . $course['title'], 'lead' => 'You finished the course. One small payment unlocks your certificate.']) ?>
<section class="section ac-dash">
    <div class="container container--narrow">
        <article class="ac-panel card" data-reveal>
            <h2 class="ac-panel__title"><?= icon('award', 'icon--sm') ?> <?= e($course['title']) ?></h2>
            <p>Certificate fee: <strong><?= e($currency) ?> <?= e(number_format($fee)) ?></strong><br>
                Pay with MTN or Airtel mobile money, or a bank card, on the secure Pesapal page.</p>
            <?php if ($last !== null && $last['status'] === 'pending'): ?>
                <p class="opp-notice" role="status"><?= icon('clock', 'icon--sm') ?> A payment you started is still waiting for confirmation. If money already left your account, wait a minute and
                    <a href="<?= e(url('academy/courses/' . $course['slug'] . '/checkout')) ?>">refresh this page</a>; do not pay twice. Otherwise you can start again below.</p>
            <?php elseif ($last !== null && $last['status'] === 'review'): ?>
                <p class="opp-notice" role="status"><?= icon('clock', 'icon--sm') ?> Your last payment is being checked by the Academy team. We will write to you shortly.</p>
            <?php elseif ($last !== null && $last['status'] === 'failed'): ?>
                <p class="opp-notice" role="status"><?= icon('alert-circle', 'icon--sm') ?> Your last payment did not go through. Nothing was charged. You can try again.</p>
            <?php endif; ?>
            <?php if ($available): ?>
                <form method="post" action="<?= e(url('academy/courses/' . $course['slug'] . '/checkout')) ?>" class="ac-cert-request"><?= csrf_field() ?>
                    <button class="btn btn--cta btn--caps btn--lg" type="submit"><?= icon('lock', 'icon--sm') ?> Pay <?= e($currency) ?> <?= e(number_format($fee)) ?> with Pesapal</button>
                </form>
                <p class="text-muted"><?= $approval ? 'After the payment your certificate request goes to the Academy team, who approve and sign it.' : 'After the payment your certificate is issued and e-mailed to you.' ?>
                    You are taken to Pesapal to pay; we never see your card or mobile money PIN.</p>
            <?php else: ?>
                <p class="opp-notice" role="alert"><?= icon('alert-circle', 'icon--sm') ?> Online payment is not available at the moment. Please <a href="<?= e(url('academy/contact')) ?>">write to the Academy team</a> and we will help you get your certificate.</p>
            <?php endif; ?>
            <p><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('academy/courses/' . $course['slug'])) ?>">Back to the course</a></p>
        </article>
    </div>
</section>
