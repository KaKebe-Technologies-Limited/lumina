<?php
/** The exam is already open in another window. Vars: $course, $tree, $progress, $quiz, $attempt, $quizState. */
$base = 'academy/learn/' . $course['slug'];
$this->start('scripts'); ?>
<script>
/* Try again every 10 seconds: the exam opens here as soon as the other window is closed. */
window.setTimeout(function () { window.location.reload(); }, 10000);
</script>
<?php $this->end(); ?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'currentQuiz' => (int) $quiz['id']]) ?>
            <article class="ac-lesson">
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></p>
                    <h1 class="ac-lesson__title"><?= e($quiz['title']) ?></h1>
                </header>
                <p class="opp-notice opp-notice--closed" role="alert"><?= icon('lock', 'icon--sm') ?> <span><b>This exam is already open in another window or tab.</b> The exam can be open in one place only, and its clock is not started a second time. Close the other window to continue here, or go back to it. This page tries again by itself every few seconds.</span></p>
                <p><a class="btn btn--outline btn--caps" href="<?= e(url($base . '/attempt/' . (int) $attempt['id'])) ?>">Try again now</a> <a class="btn btn--ghost btn--caps" href="<?= e(url('academy/dashboard')) ?>">Back to my learning</a></p>
            </article>
        </div>
    </div>
</section>
