<?php
/**
 * Taking a quiz or the final exam. Vars: $course, $tree, $progress, $quiz, $attempt, $questions (shuffled, no answers), $secondsLeft (?int), $isExam, $quizState,
 * and for attempts with the exam rules: $secured, $token, $savedAnswers, $maxLeaves, $fullscreen, $syncUrl.
 */
$base = 'academy/learn/' . $course['slug'];
$secured = !empty($secured);
$left = $secondsLeft !== null ? max(0, (int) $secondsLeft) : null;
$fmt = static fn(int $s): string => sprintf('%02d:%02d', intdiv($s, 60), $s % 60);
$saved = (array) ($savedAnswers ?? []);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/academy.js')) ?>" defer></script>
<?php if ($secured): ?><script src="<?= e(asset('js/academy-exam.js')) ?>" defer></script><?php endif; ?>
<?php $this->end(); ?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'currentQuiz' => (int) $quiz['id']]) ?>
            <article class="ac-lesson"<?php if ($secured): ?> data-ex data-ex-sync="<?= e($syncUrl) ?>" data-ex-token="<?= e($token) ?>" data-ex-max="<?= (int) $maxLeaves ?>" data-ex-leaves="<?= (int) $attempt['leave_count'] ?>" data-ex-fullscreen="<?= !empty($fullscreen) ? '1' : '0' ?>" data-ex-csrf="<?= e(csrf_token()) ?>"<?php endif; ?>>
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></p>
                    <h1 class="ac-lesson__title"><?= e($quiz['title']) ?></h1>
                    <?php if ($left !== null && $secured): ?>
                        <p class="ac-timer" role="timer" aria-live="off" data-ex-timer="<?= $left ?>"><?= icon('clock', 'icon--sm') ?> Time left: <b data-ex-time><?= e($fmt($left)) ?></b></p>
                        <p class="visually-hidden" role="status" aria-live="assertive" data-ex-announce></p>
                    <?php elseif ($left !== null): ?>
                        <p class="ac-timer" role="timer" aria-live="off" data-ac-timer="<?= $left ?>"><?= icon('clock', 'icon--sm') ?> Time left: <b data-ac-time><?= e($fmt($left)) ?></b></p>
                    <?php endif; ?>
                </header>
                <?php if ($secured): ?>
                    <noscript><p class="opp-notice opp-notice--closed"><?= icon('alert-triangle', 'icon--sm') ?> The exam needs JavaScript to be switched on (for the countdown and the exam rules). Please switch it on and reload this page.</p></noscript>
                    <p class="ac-exam-note" data-ex-status><?= icon('shield', 'icon--sm') ?> Exam in progress: stay on this page, no copying or pasting. Your answers are saved automatically. <span data-ex-saved></span></p>
                <?php endif; ?>
                <form class="ac-quiz<?= $secured ? ' ac-quiz--secure' : '' ?>" method="post" action="<?= e(url($base . '/attempt/' . (int) $attempt['id'])) ?>" data-ac-quiz<?= $secured ? ' data-ex-form' : ' data-confirm-unanswered' ?> autocomplete="off">
                    <?= csrf_field() ?>
                    <?php if ($secured): ?><input type="hidden" name="token" value="<?= e($token) ?>"><input type="hidden" name="reason" value="" data-ex-reason><?php endif; ?>
                    <?php foreach ($questions as $i => $q): $mine = array_map('intval', (array) ($saved[$q['id']] ?? [])); ?>
                        <fieldset class="ac-q" data-ac-question>
                            <legend><span class="ac-q__num"><?= (int) $i + 1 ?></span> <?= e($q['prompt']) ?></legend>
                            <?php if ($q['type'] === 'multiple'): ?><p class="ac-q__hint">Choose all that apply.</p><?php endif; ?>
                            <?php foreach ($q['options'] as $o): $id = 'q' . $q['id'] . '-' . $o['id']; ?>
                                <label class="ac-opt" for="<?= e($id) ?>">
                                    <input id="<?= e($id) ?>" type="<?= $q['type'] === 'multiple' ? 'checkbox' : 'radio' ?>" name="answer[<?= (int) $q['id'] ?>][]" value="<?= (int) $o['id'] ?>"<?= in_array((int) $o['id'], $mine, true) ? ' checked' : '' ?>>
                                    <span><?= e($o['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                    <div class="ac-quiz__bar">
                        <p class="ac-note" data-ac-progress aria-live="polite"><b data-ac-answered>0</b> of <?= count($questions) ?> answered</p>
                        <?php if ($secured && !empty($fullscreen)): ?><button class="btn btn--outline btn--sm" type="button" data-ex-fs hidden>Full screen</button><?php endif; ?>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit" data-ex-submit>Submit my answers</button>
                    </div>
                </form>

                <?php if ($secured): ?>
                    <div class="ac-exam-panel" data-ex-confirm hidden role="alertdialog" aria-modal="true" aria-labelledby="ex-confirm-t">
                        <div class="ac-exam-panel__box">
                            <h2 id="ex-confirm-t" class="ac-exam-panel__title">Submit your exam?</h2>
                            <p data-ex-confirm-text></p>
                            <p class="ac-exam-panel__actions"><button class="btn btn--cta btn--caps" type="button" data-ex-confirm-yes>Yes, submit</button> <button class="btn btn--outline btn--caps" type="button" data-ex-confirm-no>Keep working</button></p>
                        </div>
                    </div>
                    <div class="ac-exam-panel ac-exam-panel--warn" data-ex-warn hidden role="alertdialog" aria-modal="true" aria-labelledby="ex-warn-t">
                        <div class="ac-exam-panel__box">
                            <h2 id="ex-warn-t" class="ac-exam-panel__title" data-ex-warn-title>Please stay on the exam page</h2>
                            <p data-ex-warn-text></p>
                            <p class="ac-exam-panel__time">Time left: <b data-ex-warn-time></b></p>
                            <p class="ac-exam-panel__actions"><button class="btn btn--cta btn--caps" type="button" data-ex-warn-ok>Back to the exam</button></p>
                        </div>
                    </div>
                    <div class="ac-exam-panel ac-exam-panel--warn" data-ex-locked hidden role="alertdialog" aria-modal="true" aria-labelledby="ex-lock-t">
                        <div class="ac-exam-panel__box">
                            <h2 id="ex-lock-t" class="ac-exam-panel__title">This exam is open in another window</h2>
                            <p>Only one window can take the exam. Close this one; the exam continues in the other window with the same clock.</p>
                            <p class="ac-exam-panel__actions"><a class="btn btn--outline btn--caps" href="<?= e(url($base . '/attempt/' . (int) $attempt['id'])) ?>">Try this window again</a></p>
                        </div>
                    </div>
                    <p class="ac-exam-toast" data-ex-toast role="status" aria-live="polite" hidden></p>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
