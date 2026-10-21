<?php
/**
 * The lesson player. Vars: $course, $tree, $progress, $lesson, $module, $done, $prev, $next, $embed, $quizState, $examUnlocked, $resources, $learner.
 */
$base = 'academy/learn/' . $course['slug'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'current' => (int) $lesson['id'], 'examUnlocked' => $examUnlocked]) ?>

            <article class="ac-lesson" aria-labelledby="ac-lesson-title">
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a><?php if ($module): ?> &middot; <?= e($module['title']) ?><?php endif; ?></p>
                    <h1 class="ac-lesson__title" id="ac-lesson-title"><?= e($lesson['title']) ?></h1>
                    <p class="ac-lesson__meta"><?= icon('clock', 'icon--sm') ?> <?= (int) $lesson['minutes'] ?> min<?php if ($done): ?> &middot; <span class="ac-done"><?= icon('check-circle', 'icon--sm') ?> Done</span><?php endif; ?></p>
                </header>

                <?php if ($embed !== null): ?>
                    <div class="ac-video"><iframe src="<?= e($embed) ?>" title="<?= e($lesson['title']) ?>" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
                <?php elseif (trim((string) $lesson['video_url']) !== ''): ?>
                    <p><a class="btn btn--outline btn--sm" href="<?= e($lesson['video_url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('play', 'icon--sm') ?> Watch the video</a></p>
                <?php endif; ?>

                <?php if (trim((string) $lesson['body']) !== ''): ?><div class="prose ac-prose"><?= rich($lesson['body']) ?></div><?php endif; ?>

                <?php if ($lesson['kind'] === 'link' && trim((string) $lesson['link_url']) !== ''): ?>
                    <p><a class="btn btn--cta btn--caps" href="<?= e($lesson['link_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">Open the page <?= icon('external-link', 'icon--sm') ?></a></p>
                <?php endif; ?>
                <?php if (!empty($lesson['file_path'])): ?>
                    <div class="ac-file"><span aria-hidden="true"><?= icon('file-text', 'icon--lg') ?></span>
                        <div><strong><?= e((string) $lesson['file_name']) ?></strong><small><?= e(number_format(((int) $lesson['file_size']) / 1048576, 1)) ?> MB handout</small></div>
                        <a class="btn btn--outline btn--sm" href="<?= e(url($base . '/lesson/' . (int) $lesson['id'] . '/file')) ?>"><?= icon('download', 'icon--sm') ?> Download</a></div>
                <?php endif; ?>

                <nav class="ac-lesson__nav" aria-label="Lesson navigation">
                    <?php if ($prev !== null): ?><a class="btn btn--ghost btn--caps" href="<?= e(url($base . '/lesson/' . $prev)) ?>"><?= icon('arrow-left', 'icon--sm') ?> Previous</a><?php else: ?><span></span><?php endif; ?>
                    <?php if (!$done): ?>
                        <form method="post" action="<?= e(url($base . '/lesson/' . (int) $lesson['id'] . '/complete')) ?>"><?= csrf_field() ?>
                            <button class="btn btn--cta btn--caps" type="submit"><?= $next !== null ? 'Mark as done and continue' : 'Mark as done' ?> <?= icon('arrow-right', 'icon--sm') ?></button></form>
                    <?php else: ?>
                        <div class="ac-lesson__done">
                            <?php if ($next !== null): ?><a class="btn btn--cta btn--caps" href="<?= e(url($base . '/lesson/' . $next)) ?>">Next lesson <?= icon('arrow-right', 'icon--sm') ?></a>
                            <?php elseif ($tree['exam'] !== null): ?><a class="btn btn--cta btn--caps" href="<?= e(url($base . '/quiz/' . (int) $tree['exam']['id'])) ?>">Go to the final exam <?= icon('award', 'icon--sm') ?></a>
                            <?php else: ?><a class="btn btn--cta btn--caps" href="<?= e(url('academy/dashboard')) ?>">Back to my learning</a><?php endif; ?>
                            <form method="post" action="<?= e(url($base . '/lesson/' . (int) $lesson['id'] . '/reopen')) ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Mark as not done</button></form>
                        </div>
                    <?php endif; ?>
                </nav>

                <?php if ($module && $module['quiz'] !== null && $next !== null && (int) $module['lessons'][array_key_last($module['lessons'])]['id'] === (int) $lesson['id']): ?>
                    <div class="ac-callout"><?= icon('help-circle', 'icon--lg') ?><div><strong>Module quiz</strong><p>Test yourself on this module before you move on.</p></div>
                        <a class="btn btn--outline btn--sm" href="<?= e(url($base . '/quiz/' . (int) $module['quiz']['id'])) ?>">Take the quiz</a></div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
