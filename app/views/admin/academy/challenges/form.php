<?php /** Challenge form. Vars: $row, $c, $isNew, $action, $kinds, $categories, $quizLocked, $criteriaDefault, $questionCount. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$kind = (string) $v('kind', 'quiz');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => $isNew ? 'New challenge' : 'Edit challenge', 'subtitle' => $isNew ? 'Set the dates, the rules and how the winners are chosen. It stays a draft until you publish it.' : (string) $c['title']]) ?>
<?php if (!$isNew && !empty($c['finalized_at'])): ?><p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> The results are final (<?= e(format_datetime($c['finalized_at'], 'j M Y, H:i')) ?>). Dates and scoring are locked: reopen the results from the Entries page to change them.</p><?php endif; ?>
<form method="post" action="<?= e($action) ?>" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main"><div class="card__header"><h2 class="card__title" id="c-main">The challenge</h2></div>
                <div class="card__body"><div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title'), 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => $kinds, 'value' => $kind, 'required' => true, 'help' => 'A quiz is marked by the system. Innovation challenges and hackathons are scored by judges and ranked by the system.']) ?>
                    <?= partial('partials/form-field', ['name' => 'category', 'label' => 'Topic', 'type' => 'select', 'options' => $categories, 'value' => $v('category', 'basics'), 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'slug', 'label' => 'Web address (optional)', 'maxlength' => 120, 'value' => $v('slug'), 'help' => 'Made from the title when left empty.']) ?>
                    <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 2, 'maxlength' => 320, 'value' => $v('summary'), 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 6, 'maxlength' => 4000, 'value' => $v('description'), 'class' => 'field--full', 'help' => 'Plain text. Leave a blank line between paragraphs.']) ?>
                    <?= partial('partials/form-field', ['name' => 'rules', 'label' => 'Rules', 'type' => 'textarea', 'rows' => 8, 'maxlength' => 3000, 'value' => $v('rules'), 'class' => 'field--full', 'help' => 'One rule per line.']) ?>
                    <?= partial('partials/form-field', ['name' => 'prize', 'label' => 'Prize and recognition', 'maxlength' => 400, 'value' => $v('prize'), 'class' => 'field--full']) ?>
                </div></div></section>
            <section class="card" aria-labelledby="c-when"><div class="card__header"><h2 class="card__title" id="c-when">Dates (East Africa Time)</h2></div>
                <div class="card__body"><div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'apply_opens_at', 'label' => 'Applications open', 'type' => 'datetime-local', 'value' => $v('apply_opens_at'), 'help' => 'Empty = open as soon as it is published.']) ?>
                    <?= partial('partials/form-field', ['name' => 'apply_closes_at', 'label' => 'Applications close', 'type' => 'datetime-local', 'value' => $v('apply_closes_at'), 'help' => 'Empty = open until the end.']) ?>
                    <?= partial('partials/form-field', ['name' => 'starts_at', 'label' => 'Starts', 'type' => 'datetime-local', 'required' => true, 'value' => $v('starts_at'), 'help' => 'The quiz can be taken, or projects submitted, from this moment.']) ?>
                    <?= partial('partials/form-field', ['name' => 'ends_at', 'label' => 'Ends', 'type' => 'datetime-local', 'required' => true, 'value' => $v('ends_at')]) ?>
                    <?= partial('partials/form-field', ['name' => 'judging_ends_at', 'label' => 'Results final (judged challenges)', 'type' => 'datetime-local', 'value' => $v('judging_ends_at'), 'help' => 'Judges can score until this moment, then the system marks the winners. Empty = right at the end (if at least one project is scored).']) ?>
                </div></div></section>
            <section class="card" aria-labelledby="c-quiz"><div class="card__header"><h2 class="card__title" id="c-quiz">Quiz (quiz challenges)</h2></div>
                <div class="card__body">
                    <?php if ($quizLocked): ?><p class="opp-notice"><?= icon('lock', 'icon--sm') ?> Entrants have started the quiz, so the questions are locked.</p><?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'quiz_minutes', 'label' => 'Time allowed (minutes)', 'type' => 'number', 'min' => 5, 'max' => 180, 'value' => $v('quiz_minutes', 30)]) ?>
                    <?php $qs = (string) old('quiz_script', $row['quiz_script'] ?? ''); ?>
                    <div class="field<?= error('quiz_script') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-quiz_script">Questions <span class="text-muted">(<span data-aac-qcount>0</span> questions)</span></label>
                        <textarea class="textarea" id="f-quiz_script" name="quiz_script" rows="18" data-aac-script<?= $quizLocked ? ' readonly' : '' ?> spellcheck="true"><?= e($qs) ?></textarea>
                        <div class="field__help">One question per block, blocks separated by a blank line. <code>Q:</code> the question, <code>*</code> a correct answer, <code>-</code> a wrong answer, <code>=</code> the explanation. Several <code>*</code> make a "choose all that apply" question.</div>
                        <?php if (error('quiz_script')): ?><div class="field__error"><?= e((string) error('quiz_script')) ?></div><?php endif; ?>
                    </div>
                </div></section>
            <section class="card" aria-labelledby="c-crit"><div class="card__header"><h2 class="card__title" id="c-crit">Judging criteria (innovation challenges and hackathons)</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'criteria_text', 'label' => 'Criteria and weights', 'type' => 'textarea', 'rows' => 6, 'value' => $v('criteria_text', $criteriaDefault), 'help' => 'One per line as: Name | weight. The weights must add up to 100. Judges give 0 to 10 points for each.']) ?>
                </div></section>
        </div>
        <aside class="content-aside" aria-label="Settings">
            <section class="card" aria-labelledby="c-set"><div class="card__header"><h2 class="card__title" id="c-set">Entry and winners</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'approval', 'label' => 'Entries are approved', 'type' => 'select', 'options' => ['auto' => 'Automatically', 'manual' => 'By staff'], 'value' => $v('approval', 'auto')]) ?>
                    <?= partial('partials/form-field', ['name' => 'team_max', 'label' => 'Largest team (1 = individuals)', 'type' => 'number', 'min' => 1, 'max' => 10, 'value' => $v('team_max', 1), 'help' => 'Ignored for quiz challenges.']) ?>
                    <?= partial('partials/form-field', ['name' => 'max_entries', 'label' => 'Maximum entries (optional)', 'type' => 'number', 'min' => 1, 'value' => $v('max_entries')]) ?>
                    <?= partial('partials/form-field', ['name' => 'winners_count', 'label' => 'Number of winners', 'type' => 'number', 'min' => 1, 'max' => 10, 'value' => $v('winners_count', 3), 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'pass_score', 'label' => 'Minimum score to qualify (%)', 'type' => 'number', 'min' => 0, 'max' => 100, 'value' => $v('pass_score', 0), 'required' => true, 'help' => 'Winners need at least this score, and the participation certificate goes to entrants at or above it.']) ?>
                    <?= partial('partials/form-field', ['name' => 'certify_participants', 'label' => 'Give a certificate of participation to qualifying entrants', 'type' => 'checkbox', 'value' => (int) $v('certify_participants', 1) === 1]) ?>
                </div></section>
        </aside>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Save the challenge' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/challenges')) ?>">Back to the challenges</a>
    </div>
</form>
