<?php
/** Course card of the LAF Academy catalogue. Vars: $course (row with modules, lessons, minutes, learners). Labels: content('academy', 'courses.*'). */
use App\Services\Academy\CourseService;

$href = url('academy/courses/' . $course['slug']);
$icon = CourseService::icon($course);
$level = CourseService::LEVELS[$course['level']] ?? ucfirst((string) $course['level']);
?>
<article class="card ac-card" data-reveal>
    <a class="ac-card__icon ac-tone-<?= e((string) $course['category']) ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><?= icon($icon, 'icon--xl') ?></a>
    <div class="card__body">
        <div class="ac-card__tags">
            <span class="opp-chip"><?= e(CourseService::categoryLabel($course['category'])) ?></span>
            <span class="opp-badge"><?= e($level) ?></span>
        </div>
        <h3 class="card__title"><a href="<?= e($href) ?>"><?= e($course['title']) ?></a></h3>
        <?php if (trim((string) $course['summary']) !== ''): ?><p class="card__text"><?= e($course['summary']) ?></p><?php endif; ?>
        <ul class="ac-card__facts">
            <li><?= icon('layers', 'icon--sm') ?> <?= (int) $course['lessons'] ?> lessons</li>
            <li><?= icon('clock', 'icon--sm') ?> <?= e(academy_duration((int) $course['minutes'])) ?></li>
            <?php if ((int) $course['certificate_enabled'] === 1): ?><li><?= icon('award', 'icon--sm') ?> Certificate</li><?php endif; ?>
        </ul>
        <div class="card__footer"><a class="btn btn--cta btn--caps btn--sm" href="<?= e($href) ?>"><?= e(content('academy', 'courses.card_button')) ?><span class="visually-hidden">: <?= e($course['title']) ?></span></a></div>
    </div>
</article>
