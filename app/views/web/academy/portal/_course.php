<?php
/** One course card of the portal. Vars: $c (a row of CourseService::dashboard). */
use App\Services\Academy\CourseService;

$p = $c['progress'];
$done = $c['completed_at'] !== null;
?>
<article class="ac-mine card" data-reveal>
    <div class="ac-mine__icon ac-tone-<?= e((string) $c['category']) ?>" aria-hidden="true"><?= icon(CourseService::icon($c), 'icon--lg') ?></div>
    <div class="ac-mine__body">
        <h3 class="ac-mine__title"><a href="<?= e(url('academy/courses/' . $c['slug'])) ?>"><?= e($c['title']) ?></a>
            <?php if ($done): ?><span class="opp-badge">Completed</span><?php endif; ?>
            <?php if ($c['status'] !== 'published'): ?><span class="opp-badge opp-badge--soon">Not available now</span><?php endif; ?></h3>
        <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $p['pct'] ?>" aria-label="Progress in <?= e($c['title']) ?>"><span style="width:<?= (int) $p['pct'] ?>%"></span></div>
        <p class="ac-mine__meta"><?= (int) $p['done'] ?> of <?= (int) $p['total'] ?> lessons (<?= (int) $p['pct'] ?>%)<?= !empty($c['enrolled_at']) ? ' &middot; started ' . e(format_date($c['enrolled_at'], 'j M Y')) : '' ?><?= $done && !empty($c['completed_at']) ? ' &middot; finished ' . e(format_date($c['completed_at'], 'j M Y')) : '' ?></p>
        <div class="ac-mine__actions">
            <?php if ($c['status'] === 'published' && $c['resume_id'] !== null): ?><a class="btn btn--cta btn--caps btn--sm" href="<?= e(url('academy/learn/' . $c['slug'] . '/lesson/' . (int) $c['resume_id'])) ?>"><?= $p['done'] > 0 ? 'Continue' : 'Start' ?></a><?php endif; ?>
            <?php if ($c['certificate'] !== null): ?><a class="btn btn--outline btn--caps btn--sm" href="<?= e(url('academy/certificate/' . $c['certificate']['code'])) ?>"><?= icon('award', 'icon--sm') ?> Certificate</a>
            <?php elseif (!empty($c['cert_pending'])): ?><span class="opp-badge opp-badge--soon"><?= icon('clock', 'icon--sm') ?> Certificate waiting for approval</span>
            <?php elseif (!empty($c['cert_requestable'])): ?><form method="post" action="<?= e(url('academy/courses/' . $c['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps btn--sm" type="submit"><?= icon('award', 'icon--sm') ?> <?= e(\App\Services\Academy\CertificatePayments::label($c['slug'])) ?></button></form><?php endif; ?>
        </div>
    </div>
</article>
