<?php
/**
 * Learner portal > a certificate that is not issued yet: the BLURRED preview. Vars: $me, $tab, $unread, $portrait, $cert (own row), $locked (the redacted SVG: no name, course, score,
 * date, number or QR code of the real certificate), $fee (the certificate fee while it is due, else null), $canPay (online payment is available).
 * The blur is decoration only: the picture itself holds no real detail. The clear view and the PDF open when the fee is paid AND the administrator has approved.
 */
?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Your certificate preview', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'This is how your certificate will look. Your name, the course, the score, the date and the certificate number stay hidden until it is paid for and approved.']) ?>
<section class="section ac-dash">
    <div class="container">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>

        <div class="ac-locked" data-reveal>
            <div class="ac-locked__frame">
                <div class="ac-cert-svg ac-cert-svg--locked" aria-label="A blurred preview of your certificate; the details are hidden"><?= $locked ?></div>
                <div class="ac-locked__veil">
                    <span class="ac-locked__lock"><?= icon('lock', 'icon--xl') ?></span>
                    <strong><?= $fee !== null ? 'Locked: the certificate fee is not paid yet' : 'Locked: waiting for approval' ?></strong>
                    <span>Details hidden</span>
                </div>
            </div>

            <div class="ac-locked__side card"><div class="card__body">
                <h2 class="ac-panel__title"><?= icon('award', 'icon--sm') ?> <?= e($cert['course_title']) ?></h2>
                <ol class="ac-locked__steps">
                    <li class="<?= $fee === null ? 'is-done' : 'is-todo' ?>"><span class="ac-locked__n"><?= $fee === null ? icon('check', 'icon--sm') : '1' ?></span>
                        <div><strong>Certificate fee</strong>
                            <p><?= $fee !== null ? 'Pay <b>' . e($fee['display']) . '</b> to release your certificate. You pay securely online.' : 'Nothing to pay (it is paid or free).' ?></p></div></li>
                    <li class="is-todo"><span class="ac-locked__n">2</span>
                        <div><strong>Approved and signed by the Academy</strong>
                            <p><?= e(\App\Services\Academy\CertificateService::waitingNotice()) ?></p></div></li>
                    <li class="is-todo"><span class="ac-locked__n"><?= icon('award', 'icon--sm') ?></span>
                        <div><strong>View it clearly and download the PDF</strong>
                            <p>When both steps are done, your certificate opens here in full and you can download it.</p></div></li>
                </ol>
                <div class="ac-locked__actions">
                    <?php if (!empty($canPay)): ?>
                        <form method="post" action="<?= e(url('academy/portal/certificates/' . (int) $cert['id'] . '/pay')) ?>"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('credit-card', 'icon--sm') ?> Pay <?= e((string) ($fee['display'] ?? '')) ?></button></form>
                    <?php elseif ($fee !== null): ?>
                        <a class="btn btn--cta btn--caps" href="<?= e(url('academy/portal/certificates')) ?>"><?= icon('credit-card', 'icon--sm') ?> How to pay</a>
                    <?php endif; ?>
                    <a class="btn btn--outline btn--caps" href="<?= e(url('academy/portal/certificates')) ?>"><?= icon('arrow-left', 'icon--sm') ?> My certificates</a>
                </div>
            </div></div>
        </div>
    </div>
</section>
