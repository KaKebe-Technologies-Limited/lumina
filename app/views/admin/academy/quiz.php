<?php
/** Quiz editor. Vars: $quiz, $course, $module, $isExam, $script (text), $count, $stats. */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end();
$qid = (int) $quiz['id'];
$v = static fn(string $k, mixed $d = '') => old($k, $quiz[$k] ?? $d);
?>
<?= partial('partials/admin/page-header', ['title' => $isExam ? 'Final exam' : 'Module quiz', 'subtitle' => $course['title'] . ($module ? ' - ' . $module['title'] : '')]) ?>
<form method="post" action="<?= e(url('admin/academy/quizzes/' . $qid)) ?>" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="q-main">
                <div class="card__header"><h2 class="card__title" id="q-main">Questions</h2><span class="text-muted"><span data-aac-qcount><?= (int) $count ?></span> questions</span></div>
                <div class="card__body">
                    <?php $sErr = error('script'); ?>
                    <div class="field<?= $sErr ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-script">Write the quiz as plain text</label>
                        <textarea class="textarea aac-script" id="f-script" name="script" rows="26" spellcheck="true" data-aac-script aria-describedby="f-script-help<?= $sErr ? ' f-script-error' : '' ?>"><?= e((string) $script) ?></textarea>
                        <?php if ($sErr): ?><div class="field__error" id="f-script-error"><?= e((string) $sErr) ?></div><?php endif; ?>
                        <div class="field__help" id="f-script-help">Saving replaces all the questions of this quiz with the text above.</div>
                    </div>
                </div>
            </section>
            <section class="card" aria-labelledby="q-help">
                <div class="card__header"><h2 class="card__title" id="q-help">How to write a question</h2></div>
                <div class="card__body">
<pre class="aac-example">Q: What does CPU stand for?
* Central Processing Unit
- Central Program Utility
- Computer Personal Unit
= The CPU is the "brain" of the computer.

Q: Which of these are input devices? (choose all that apply)
* Keyboard
* Mouse
- Printer
= A printer sends information out of the computer.</pre>
                    <ul class="ac-list">
                        <li><code>Q:</code> starts a question. A blank line separates questions.</li>
                        <li><code>*</code> before a right answer, <code>-</code> before a wrong one. One right answer = "pick one"; several = "pick all that apply" (say so in the question).</li>
                        <li><code>=</code> is the explanation learners read after the quiz. Always write one.</li>
                        <li>2 to 8 answers per question, plain text only. Answers are shuffled for every learner.</li>
                    </ul>
                </div>
            </section>
        </div>
        <aside class="content-aside" aria-label="Quiz settings">
            <section class="card" aria-labelledby="q-set">
                <div class="card__header"><h2 class="card__title" id="q-set">Settings</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title')]) ?>
                    <?= partial('partials/form-field', ['name' => 'pass_mark', 'label' => 'Pass mark (%)', 'type' => 'number', 'min' => 1, 'max' => 100, 'value' => $v('pass_mark', 70), 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'time_limit_min', 'label' => 'Time limit (minutes)', 'type' => 'number', 'min' => 1, 'max' => 240, 'value' => $v('time_limit_min', ''), 'help' => 'Leave empty for no time limit.']) ?>
                    <?= partial('partials/form-field', ['name' => 'max_attempts', 'label' => 'Attempts allowed', 'type' => 'number', 'min' => 0, 'max' => 100, 'value' => $v('max_attempts', 0), 'help' => '0 = as many as the learner likes.']) ?>
                </div>
            </section>
            <?php if (!empty($stats) && (int) $stats['attempts'] > 0): ?>
                <section class="card" aria-labelledby="q-stat">
                    <div class="card__header"><h2 class="card__title" id="q-stat">Results so far</h2></div>
                    <div class="card__body"><ul class="aac-list">
                        <li><?= (int) $stats['attempts'] ?> attempts by <?= (int) $stats['learners'] ?> learners</li>
                        <li>Average score <?= (int) round((float) $stats['avg_score']) ?>%</li>
                        <li><?= (int) $stats['passed'] ?> passed</li>
                    </ul></div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save the quiz</button>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/courses/' . (int) $course['id'] . '/curriculum')) ?>">Back to the curriculum</a>
    </div>
</form>
<form method="post" action="<?= e(url('admin/academy/quizzes/' . $qid . '/delete')) ?>" class="aac-inline" data-confirm="Delete this quiz and all its questions? Learners' past results are removed too."><?= csrf_field() ?>
    <button class="btn btn--ghost btn--sm btn-danger-hover" type="submit"><?= icon('trash', 'icon--sm') ?> Delete this quiz</button></form>
