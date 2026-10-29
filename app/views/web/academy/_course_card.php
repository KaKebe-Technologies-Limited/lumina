<?php
/**
 * Course card of the LAF Academy catalogue. Vars: $course (row with modules, lessons, minutes, learners, highlights = the first module titles). Styles: assets/css/catalog.css (the
 * pages that show cards load it). Labels: content('academy', 'courses.*').
 */
use App\Services\Academy\CourseService;

$href = url('academy/courses/' . $course['slug']);
$icon = CourseService::icon($course);
$level = CourseService::LEVELS[$course['level']] ?? ucfirst((string) $course['level']);
$dots = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3][(string) $course['level']] ?? 1;
$learners = (int) ($course['learners'] ?? 0);
$learn = array_slice((array) ($course['highlights'] ?? []), 0, 3);
$cat = CourseService::categoryLabel($course['category']);
?>
<article class="cc-card cc-tone-<?= e((string) $course['category']) ?>" data-reveal>
    <a class="cc-card__banner" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true">
        <span class="cc-card__bgicon"><?= icon($icon, 'icon--xl') ?></span>
        <span class="cc-card__pills">
            <span class="cc-pill cc-pill--free">Free</span>
            <?php if ((int) $course['certificate_enabled'] === 1): ?><span class="cc-pill"><?= icon('award', 'icon--sm') ?> Certificate</span><?php endif; ?>
        </span>
        <?php if ((int) ($course['is_featured'] ?? 0) === 1): ?><span class="cc-card__ribbon"><?= icon('star', 'icon--sm') ?> Popular</span><?php endif; ?>
        <span class="cc-card__icon"><?= icon($icon, 'icon--xl') ?></span>
        <span class="cc-card__cat"><?= e($cat) ?></span>
    </a>
    <div class="cc-card__body">
        <p class="cc-card__meta">
            <span class="cc-level" title="<?= e($level) ?> level"><span class="cc-level__dots" aria-hidden="true"><?php for ($i = 1; $i <= 3; $i++): ?><i class="<?= $i <= $dots ? 'is-on' : '' ?>"></i><?php endfor; ?></span><?= e($level) ?></span>
            <span class="cc-card__learners"><?= icon('users', 'icon--sm') ?> <?= $learners > 0 ? number_format($learners) . ' learner' . ($learners === 1 ? '' : 's') : 'Be the first to join' ?></span>
        </p>
        <h3 class="cc-card__title"><a href="<?= e($href) ?>"><?= e($course['title']) ?></a></h3>
        <?php if (trim((string) $course['summary']) !== ''): ?><p class="cc-card__text"><?= e($course['summary']) ?></p><?php endif; ?>
        <?php if ($learn !== []): ?>
            <div class="cc-card__learn">
                <p class="cc-card__learn-title">What you will learn</p>
                <ul><?php foreach ($learn as $t): ?><li><?= icon('check-circle', 'icon--sm') ?> <span><?= e((string) $t) ?></span></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
        <ul class="cc-card__facts">
            <li><?= icon('layers', 'icon--sm') ?> <?= (int) $course['lessons'] ?> lessons</li>
            <li><?= icon('clock', 'icon--sm') ?> <?= e(academy_duration((int) $course['minutes'])) ?></li>
            <li><?= icon('globe', 'icon--sm') ?> Online, at your pace</li>
        </ul>
    </div>
    <div class="cc-card__foot"><a class="cc-btn" href="<?= e($href) ?>"><?= e(content('academy', 'courses.card_button')) ?> <?= icon('arrow-right', 'icon--sm') ?><span class="visually-hidden">: <?= e($course['title']) ?></span></a></div>
</article>
