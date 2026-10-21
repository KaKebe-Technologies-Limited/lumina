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
            <p class="opp-notice <?= $revoked ? 'opp-notice--closed' : 'ac-ok' ?>" role="status"><?= icon($revoked ? 'alert-triangle' : 'check-circle', 'icon--sm') ?>
                <?= $revoked ? 'This certificate was withdrawn on ' . e(format_date($cert['revoked_at'])) . '.' : 'This is a genuine certificate issued by the LAF Academy.' ?></p>
            <div class="ac-cert" aria-label="Certificate">
                <p class="ac-cert__org"><?= e($wording['org']) ?> &middot; LAF Academy</p>
                <?php $tx = \App\Services\Academy\CertificateService::texts($cert); ?><h2 class="ac-cert__title"><?= e($tx['heading']) ?></h2>
                <p class="ac-cert__small"><?= e($tx['intro']) ?></p>
                <p class="ac-cert__name"><?= e($cert['learner_name']) ?></p>
                <p class="ac-cert__text"><?= e($tx['statement']) ?></p>
                <p class="ac-cert__course"><?= e($cert['course_title']) ?></p>
                <p class="ac-cert__facts">
                    <?php if ($cert['score_pct'] !== null): ?><?= e($tx['scoreLabel']) ?> <?= e(rtrim(rtrim(number_format((float) $cert['score_pct'], 1), '0'), '.')) ?>% &nbsp;|&nbsp; <?php endif; ?>
                    <?php if ($cert['hours'] !== null): ?><?= e(rtrim(rtrim(number_format((float) $cert['hours'], 1), '0'), '.')) ?> learning hours &nbsp;|&nbsp; <?php endif; ?>
                    Issued <?= e(format_date($cert['issued_at'])) ?></p>
                <p class="ac-cert__no">Certificate no. <b><?= e($cert['code']) ?></b></p>
            </div>
            <?php if (!$revoked): ?>
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
