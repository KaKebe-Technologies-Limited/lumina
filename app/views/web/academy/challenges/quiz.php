<?php
/** Timed quiz of a challenge. Vars: $c, $entry, $questions, $secondsLeft (int|null). */
?>
<?= partial('partials/page-hero', ['title' => $c['title'], 'eyebrow' => 'Quiz challenge', 'lead' => 'Answer every question, then press Submit. You have one attempt.']) ?>
<section class="section">
    <div class="container container--narrow">
        <?php if ($secondsLeft !== null): ?>
            <p class="ac-timer" role="timer" aria-live="off" data-ac-timer="<?= max(0, (int) $secondsLeft) ?>"><?= icon('clock', 'icon--sm') ?> Time left: <b data-ac-time><?= e(sprintf('%02d:%02d', intdiv(max(0, (int) $secondsLeft), 60), max(0, (int) $secondsLeft) % 60)) ?></b></p>
        <?php endif; ?>
        <form class="ac-quiz" method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/quiz/submit')) ?>" data-ac-quiz data-confirm-unanswered>
            <?= csrf_field() ?>
            <?php foreach ($questions as $i => $q): ?>
                <fieldset class="ac-q" data-ac-question>
                    <legend><span class="ac-q__num"><?= (int) $i + 1 ?></span> <?= e($q['prompt']) ?></legend>
                    <?php if ($q['multiple']): ?><p class="ac-q__hint">Choose all that apply.</p><?php endif; ?>
                    <?php foreach ($q['options'] as $o): $id = 'q' . (int) $q['n'] . '-' . (int) $o['n']; ?>
                        <label class="ac-opt" for="<?= e($id) ?>">
                            <input id="<?= e($id) ?>" type="<?= $q['multiple'] ? 'checkbox' : 'radio' ?>" name="a[<?= (int) $q['n'] ?>][]" value="<?= (int) $o['n'] ?>">
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
    </div>
</section>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
