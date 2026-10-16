<?php
/** Vacancy card. Vars: $job (row). Labels: content('careers', 'openings.*'). */
$href = url('careers/' . $job['slug']);
$closing = !empty($job['closing_date']) ? format_date($job['closing_date']) : '';
?>
<article class="job-card" data-reveal>
    <div class="job-card__main">
        <div class="job-card__tags">
            <span class="badge badge--gold"><?= e(job_type_label($job['employment_type'])) ?></span>
            <?php if (!empty($job['department'])): ?><span class="badge badge--outline"><?= e($job['department']) ?></span><?php endif; ?>
        </div>
        <h3 class="job-card__title"><a href="<?= e($href) ?>"><?= e($job['title']) ?></a></h3>
        <?php if (trim((string) ($job['summary'] ?? '')) !== ''): ?><p class="job-card__text"><?= e($job['summary']) ?></p><?php endif; ?>
        <ul class="job-card__facts">
            <?php if (!empty($job['location'])): ?><li><?= icon('map-pin', 'icon--sm') ?> <?= e($job['location']) ?></li><?php endif; ?>
            <?php if ($closing !== ''): ?><li><?= icon('calendar', 'icon--sm') ?> <?= e(content('careers', 'openings.closing_label')) ?>: <time datetime="<?= e(format_date($job['closing_date'], 'Y-m-d')) ?>"><?= e($closing) ?></time></li><?php endif; ?>
        </ul>
    </div>
    <div class="job-card__action">
        <a class="btn btn--primary" href="<?= e($href) ?>"><?= e(content('careers', 'openings.card_button')) ?> <?= icon('arrow-right', 'icon--arrow') ?></a>
    </div>
</article>
