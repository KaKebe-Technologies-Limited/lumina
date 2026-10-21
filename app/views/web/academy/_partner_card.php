<?php
/** Partner course card (a course on another platform). Vars: $p (academy_partner_courses row). Labels: content('academy', 'partners.*'). */
use App\Services\Academy\CourseService;
use App\Services\Academy\PartnerCourses;

$level = CourseService::LEVELS[$p['level']] ?? ucfirst((string) $p['level']);
$cat = CourseService::CATEGORIES[$p['category']] ?? ['Course', 'book'];
$cost = PartnerCourses::COSTS[$p['cost']][1] ?? 'Free';
?>
<article class="card ac-card ac-card--partner" data-reveal>
    <div class="ac-card__icon ac-tone-<?= e((string) $p['category']) ?>" aria-hidden="true"><?= icon(safe_icon($cat[1], 'book'), 'icon--xl') ?></div>
    <div class="card__body">
        <div class="ac-card__tags">
            <span class="opp-chip"><?= e((string) $p['provider']) ?></span>
            <span class="opp-badge"><?= e($level) ?></span>
            <span class="opp-badge ac-badge--free"><?= e($cost) ?></span>
        </div>
        <h3 class="card__title"><a href="<?= e($p['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($p['title']) ?><span class="visually-hidden"> (opens on <?= e((string) $p['provider']) ?>)</span></a></h3>
        <?php if (trim((string) $p['summary']) !== ''): ?><p class="card__text"><?= e($p['summary']) ?></p><?php endif; ?>
        <ul class="ac-card__facts">
            <li><?= icon('layers', 'icon--sm') ?> <?= e($cat[0]) ?></li>
            <?php if (!empty($p['duration'])): ?><li><?= icon('clock', 'icon--sm') ?> <?= e((string) $p['duration']) ?></li><?php endif; ?>
            <?php if (!empty($p['certificate'])): ?><li><?= icon('award', 'icon--sm') ?> <?= e((string) $p['certificate']) ?></li><?php endif; ?>
            <?php if (($p['language'] ?? 'English') !== 'English'): ?><li><?= icon('globe', 'icon--sm') ?> <?= e((string) $p['language']) ?></li><?php endif; ?>
        </ul>
        <div class="card__footer"><a class="btn btn--outline btn--caps btn--sm" href="<?= e($p['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e(content('academy', 'partners.open_button')) ?> <?= icon('external-link', 'icon--sm') ?><span class="visually-hidden">: <?= e($p['title']) ?></span></a></div>
    </div>
</article>
