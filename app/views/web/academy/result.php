<?php
/** Quiz result and review. Vars: $course, $tree, $progress, $quiz, $attempt, $isExam, $review, $can, $next (lesson id|null), $certificate, $quizState. */
$base = 'academy/learn/' . $course['slug'];
$passed = (int) $attempt['passed'] === 1;
$pct = (int) round((float) $attempt['score_pct']);
?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'currentQuiz' => (int) $quiz['id']]) ?>
            <article class="ac-lesson">
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></p>
                    <h1 class="ac-lesson__title"><?= e($quiz['title']) ?></h1>
                </header>

                <div class="ac-result <?= $passed ? 'is-pass' : 'is-fail' ?>" role="status">
                    <div class="ac-result__score"><b><?= $pct ?>%</b><span><?= (int) $attempt['correct'] ?> of <?= (int) $attempt['total'] ?> correct</span></div>
                    <div class="ac-result__text">
                        <strong><?= $passed ? 'Well done, you passed!' : 'Not there yet' ?></strong>
                        <p><?= $passed ? ($isExam ? 'You passed the final exam.' : 'You are ready for the next part of the course.') : 'You need ' . (int) $quiz['pass_mark'] . '% to pass. Read the explanations below, review the lessons and try again.' ?></p>
                    </div>
                </div>

                <div class="ac-result__actions">
                    <?php if ($isExam && $passed && $certificate !== null): ?>
                        <a class="btn btn--cta btn--caps" href="<?= e(url('academy/certificate/' . $certificate['code'])) ?>"><?= icon('award', 'icon--sm') ?> Open my certificate</a>
                    <?php elseif ($isExam && $passed && !empty($certificatePending)): ?>
                        <p class="opp-notice" role="status"><?= icon('clock', 'icon--sm') ?> Your certificate is waiting for approval. You will find it here and in your dashboard as soon as the Academy team approves it.</p>
                    <?php elseif ($isExam && $passed): ?>
                        <a class="btn btn--cta btn--caps" href="<?= e(url($base)) ?>">Finish the remaining lessons</a>
                    <?php elseif ($passed && $next !== null): ?>
                        <a class="btn btn--cta btn--caps" href="<?= e(url($base . '/lesson/' . $next)) ?>">Next module <?= icon('arrow-right', 'icon--sm') ?></a>
                    <?php elseif ($passed): ?>
                        <a class="btn btn--cta btn--caps" href="<?= e(url('academy/dashboard')) ?>">Back to my learning</a>
                    <?php endif; ?>
                    <?php if ($can['ok']): ?>
                        <form method="post" action="<?= e(url($base . '/quiz/' . (int) $quiz['id'] . '/start')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--caps" type="submit"><?= icon('refresh', 'icon--sm') ?> Try again</button></form>
                    <?php endif; ?>
                    <a class="btn btn--ghost btn--caps" href="<?= e(url($base)) ?>">Back to the lessons</a>
                </div>

                <h2 class="ac-block__title ac-block__title--sm">Review your answers</h2>
                <?php if ($review === []): ?><p class="text-muted">The questions of this quiz were changed after your attempt, so the review is no longer available. Your score is kept.</p><?php endif; ?>
                <?php foreach ($review as $i => $q): ?>
                    <section class="ac-q ac-q--review <?= $q['right'] ? 'is-right' : 'is-wrong' ?>" aria-label="Question <?= (int) $i + 1 ?>">
                        <h3 class="ac-q__title"><span class="ac-q__num"><?= (int) $i + 1 ?></span> <?= e($q['prompt']) ?>
                            <span class="opp-badge<?= $q['right'] ? '' : ' opp-badge--soon' ?>"><?= $q['right'] ? 'Correct' : ($q['answered'] ? 'Not correct' : 'Not answered') ?></span></h3>
                        <ul class="ac-review">
                            <?php foreach ($q['options'] as $o): ?>
                                <li class="<?= $o['correct'] ? 'is-correct' : '' ?> <?= $o['chosen'] ? 'is-chosen' : '' ?>">
                                    <span aria-hidden="true"><?= $o['correct'] ? icon('check-circle') : ($o['chosen'] ? icon('x') : icon('circle')) ?></span>
                                    <span><?= e($o['label']) ?>
                                        <?php if ($o['chosen']): ?><em class="ac-review__tag">your answer</em><?php endif; ?>
                                        <?php if ($o['correct']): ?><em class="ac-review__tag ac-review__tag--ok">right answer</em><?php endif; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($q['explanation'] !== ''): ?><p class="ac-q__why"><strong>Why:</strong> <?= e($q['explanation']) ?></p><?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </article>
        </div>
    </div>
</section>
