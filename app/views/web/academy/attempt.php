<?php
/** Taking a quiz. Vars: $course, $tree, $progress, $quiz, $attempt, $questions (shuffled, no answers), $secondsLeft (?int), $isExam, $quizState. */
$base = 'academy/learn/' . $course['slug'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'currentQuiz' => (int) $quiz['id']]) ?>
            <article class="ac-lesson">
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></p>
                    <h1 class="ac-lesson__title"><?= e($quiz['title']) ?></h1>
                    <?php if ($secondsLeft !== null): ?>
                        <p class="ac-timer" role="timer" aria-live="off" data-ac-timer="<?= max(0, (int) $secondsLeft) ?>"><?= icon('clock', 'icon--sm') ?> Time left: <b data-ac-time><?= e(sprintf('%02d:%02d', intdiv(max(0, (int) $secondsLeft), 60), max(0, (int) $secondsLeft) % 60)) ?></b></p>
                    <?php endif; ?>
                </header>
                <form class="ac-quiz" method="post" action="<?= e(url($base . '/attempt/' . (int) $attempt['id'])) ?>" data-ac-quiz data-confirm-unanswered>
                    <?= csrf_field() ?>
                    <?php foreach ($questions as $i => $q): ?>
                        <fieldset class="ac-q" data-ac-question>
                            <legend><span class="ac-q__num"><?= (int) $i + 1 ?></span> <?= e($q['prompt']) ?></legend>
                            <?php if ($q['type'] === 'multiple'): ?><p class="ac-q__hint">Choose all that apply.</p><?php endif; ?>
                            <?php foreach ($q['options'] as $o): $id = 'q' . $q['id'] . '-' . $o['id']; ?>
                                <label class="ac-opt" for="<?= e($id) ?>">
                                    <input id="<?= e($id) ?>" type="<?= $q['type'] === 'multiple' ? 'checkbox' : 'radio' ?>" name="answer[<?= (int) $q['id'] ?>][]" value="<?= (int) $o['id'] ?>">
                                    <span><?= e($o['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                    <div class="ac-quiz__bar">
                        <p class="ac-note" data-ac-progress aria-live="polite"><b data-ac-answered>0</b> of <?= count($questions) ?> answered</p>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit">Submit my answers</button>
                    </div>
                </form>
            </article>
        </div>
    </div>
</section>
