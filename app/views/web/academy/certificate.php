<?php
/** Certificate check page. Vars: $cert (row|null), $code, $wording (org, statement, signatory, title), $learner. */
?>
<?= partial('partials/page-hero', ['title' => 'Certificate check', 'eyebrow' => 'LAF Academy', 'lead' => (string) content('academy', 'learn.certificate_note')]) ?>
<section class="section ac-cert-page">
    <div class="container container--narrow">
        <?php if ($cert === null): ?>
            <div class="ac-notice card"><div class="card__body">
                <span class="ac-notice__icon is-bad" aria-hidden="true"><?= icon('alert-triangle', 'icon--xl') ?></span>
                <h2>No certificate with this number</h2>
                <p>We could not find a certificate numbered <b><?= e($code) ?></b>. Please check the number for typing mistakes.</p>
            </div></div>
        <?php else: $revoked = $cert['revoked_at'] !== null; ?>
            <?php if (!empty($staffView) && !empty($notReleased)): ?>
                <p class="opp-notice opp-notice--closed" role="status"><?= icon('lock', 'icon--sm') ?> <strong>Administrator view.</strong> This certificate has not been released to the learner yet (<?= \App\Services\Academy\AcademyFees::certDue($cert) ? 'the fee is unpaid' : 'it is waiting for approval' ?>). Only administrators can see it; the learner and visitors cannot.</p>
            <?php elseif (!empty($staffView)): ?>
                <p class="text-muted ac-center"><?= icon('lock', 'icon--sm') ?> You are seeing this as an administrator.</p>
            <?php endif; ?>
            <p class="opp-notice <?= $revoked ? 'opp-notice--closed' : 'ac-ok' ?>" role="status"><?= icon($revoked ? 'alert-triangle' : 'check-circle', 'icon--sm') ?>
                <?= $revoked ? 'This certificate was withdrawn on ' . e(format_date($cert['revoked_at'])) . '.' : 'This is a genuine certificate issued by the LAF Academy.' ?></p>
            <div class="ac-cert-svg" aria-label="Certificate"><?= \App\Services\Academy\CertificateService::svg($cert) ?></div>
            <?php if (!$revoked || !empty($staffView)): ?>
                <p class="ac-center">
                    <a class="btn btn--cta btn--caps" href="<?= e(url('academy/certificate/' . $cert['code'] . '/pdf')) ?>" target="_blank" rel="noopener"><?= icon('download', 'icon--sm') ?> Download the PDF</a>
                    <button class="btn btn--outline btn--caps" type="button" data-ac-print><?= icon('printer', 'icon--sm') ?> Print</button>
                </p>
            <?php elseif (!empty($cert['revoked_reason'])): ?>
                <p class="text-muted ac-center">Reason: <?= e($cert['revoked_reason']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
