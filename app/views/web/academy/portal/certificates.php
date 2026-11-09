<?php
/** Learner portal > Certificates. Vars: $me, $tab, $unread, $portrait, $certificates (ready or withdrawn), $waiting (waiting for approval), $requestable (courses the learner may ask a certificate for). */
use App\Services\Academy\CertificateService;
?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'My certificates', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Certificates are approved and signed by the Academy administrator before they are shared with you.']) ?>
<section class="section ac-dash">
    <div class="container">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>

        <?php foreach ($requestable as $c): ?>
            <section class="ac-panel card" data-reveal>
                <h2 class="ac-panel__title"><?= icon('award', 'icon--sm') ?> <?= e($c['title']) ?></h2>
                <p>You completed this course. Ask for your certificate: the Academy team approves and signs it.</p>
                <form method="post" action="<?= e(url('academy/courses/' . $c['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('award', 'icon--sm') ?> Request my certificate</button></form>
            </section>
        <?php endforeach; ?>

        <?php if ($waiting !== []): ?>
            <h2 class="ac-block__title">Waiting for payment, approval and signing</h2>
            <?php foreach ($waiting as $w): ?>
                <article class="ac-panel card" data-reveal>
                    <h3 class="ac-panel__title"><?= icon('clock', 'icon--sm') ?> <?= e($w['course_title']) ?></h3>
                    <?php if (\App\Services\Academy\AcademyFees::certDue($w) && ($certFee = \App\Services\Academy\AcademyFees::configured('cert'))): ?>
                        <p class="opp-notice ac-cert-wait"><?= icon('credit-card', 'icon--sm') ?> Your certificate is ready once the certificate fee of <strong><?= e($certFee['display']) ?></strong> is paid. You pay securely online.</p>
                        <?php $certDz = \App\Services\Academy\AcademyFees::daraza('cert'); if (\App\Services\Pesapal\PesapalPayments::ready()): ?><form method="post" action="<?= e(url('academy/portal/certificates/' . (int) $w['id'] . '/pay')) ?>" style="margin-bottom:.75rem"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('lock', 'icon--sm') ?> Pay the certificate fee with Pesapal</button></form><?php endif; ?>
                        <?php if (($certMan = \App\Services\Academy\AcademyFees::manual()) !== null): ?>
                            <div class="ac-paydetails"><?php if ($certMan['mobile'] !== ''): ?><h4>Mobile money</h4><?= nl2p($certMan['mobile']) ?><?php endif; ?><?php if ($certMan['bank'] !== ''): ?><h4>Bank transfer</h4><?= nl2p($certMan['bank']) ?><?php endif; ?><p class="text-muted">After paying, <a href="<?= e(url('academy/portal/messages')) ?>">send us the transaction ID</a>: the Academy team confirms it and releases your certificate.</p></div>
                        <?php endif; ?>
                        <?php if ($certDz !== null): ?><p><a class="btn <?= \App\Services\Pesapal\PesapalPayments::ready() ? 'btn--outline' : 'btn--cta' ?> btn--caps" href="<?= e($certDz['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('external-link', 'icon--sm') ?> <?= e($certDz['label']) ?></a></p><p class="text-muted">After you pay on Daraza, the Academy team checks the payment and releases your certificate. <a href="<?= e(url('academy/portal/messages')) ?>">Send us your Daraza transaction ID</a> to speed it up.</p><?php endif; ?>
                    <?php else: ?>
                    <p class="opp-notice ac-cert-wait"><?= e(CertificateService::waitingNotice()) ?></p>
                    <?php endif; ?>
                    <p><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('academy/portal/certificates/' . (int) $w['id'] . '/preview')) ?>"><?= icon('eye', 'icon--sm') ?> View a preview (blurred)</a></p>
                    <p class="text-muted">Filed on <?= e(format_date($w['issued_at'], 'j M Y')) ?>.</p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2 class="ac-block__title">Ready</h2>
        <?php if ($certificates === []): ?><p class="text-muted">Nothing here yet. Pass a final exam to earn your first certificate.</p><?php endif; ?>
        <div class="ac-certs">
            <?php foreach ($certificates as $ce): ?>
                <article class="ac-panel card" data-reveal>
                    <h3 class="ac-panel__title"><?= icon('award', 'icon--sm') ?> <?= e($ce['course_title']) ?></h3>
                    <p class="text-muted"><?= $ce['revoked_at'] !== null ? 'Withdrawn' : 'Issued ' . e(format_date($ce['issued_at'])) ?><?= isset($ce['score_pct']) && $ce['score_pct'] !== null ? ' &middot; score ' . e(rtrim(rtrim(number_format((float) $ce['score_pct'], 1), '0'), '.')) . '%' : '' ?><br>Number <strong><?= e($ce['code']) ?></strong></p>
                    <?php if ($ce['revoked_at'] === null): ?>
                        <div class="ac-mine__actions">
                            <a class="btn btn--cta btn--caps btn--sm" href="<?= e(url('academy/certificate/' . $ce['code'])) ?>"><?= icon('award', 'icon--sm') ?> Open</a>
                            <a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('academy/certificate/' . $ce['code'] . '/pdf')) ?>"><?= icon('download', 'icon--sm') ?> Download PDF</a>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
