<?php
/** Quiz / final exam start screen. Vars: $course, $tree, $progress, $quiz, $isExam, $can, $locked, $questions, $history, $best, $passed, $openAttempt, $certificate, $quizState. */
$base = 'academy/learn/' . $course['slug'];
?>
<section class="ac-player">
    <div class="container">
        <div class="ac-player__layout">
            <?= partial('web/academy/_sidebar', ['course' => $course, 'tree' => $tree, 'progress' => $progress, 'quizState' => $quizState, 'currentQuiz' => (int) $quiz['id'], 'examUnlocked' => !$locked]) ?>
            <article class="ac-lesson">
                <header class="ac-lesson__head">
                    <p class="ac-lesson__crumb"><a href="<?= e(url('academy/courses/' . $course['slug'])) ?>"><?= e($course['title']) ?></a></p>
                    <h1 class="ac-lesson__title"><?= e($quiz['title']) ?></h1>
                </header>

                <ul class="ac-quizfacts">
                    <li><?= icon('help-circle', 'icon--sm') ?> <?= (int) $questions ?> questions</li>
                    <li><?= icon('check-circle', 'icon--sm') ?> Pass mark <?= (int) $quiz['pass_mark'] ?>%</li>
                    <?php if ((int) $quiz['time_limit_min'] > 0): ?><li><?= icon('clock', 'icon--sm') ?> <?= (int) $quiz['time_limit_min'] ?> minutes</li><?php endif; ?>
                    <li><?= icon('refresh', 'icon--sm') ?> <?= (int) $quiz['max_attempts'] > 0 ? (int) $quiz['max_attempts'] . ' attempts' : 'As many attempts as you like' ?></li>
                </ul>

                <?php if ($isExam): ?><p class="ac-note"><?= e(content('academy', 'learn.pass_note')) ?></p><?php else: ?>
                    <p class="ac-note">This quiz helps you check what you learned in this module. The questions are shuffled each time, and you see the right answers with explanations after you finish.</p><?php endif; ?>

                <?php if ($passed): ?>
                    <p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> You passed this <?= $isExam ? 'exam' : 'quiz' ?><?= $best !== null ? ' with ' . (int) round((float) $best['score_pct']) . '%' : '' ?>.</p>
                    <?php if ($isExam && $certificate !== null): ?><p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/certificate/' . $certificate['code'])) ?>"><?= icon('award', 'icon--sm') ?> Open my certificate</a></p><?php endif; ?>
                    <?php if ($isExam && $certificate === null && empty($certificatePending) && !empty($certificateRequestable)): ?><p><form method="post" action="<?= e(url('academy/courses/' . $course['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('award', 'icon--sm') ?> <?= e(\App\Services\Academy\CertificatePayments::label($course['slug'])) ?></button></form></p><?php endif; ?>
                    <?php if ($isExam && $certificate === null && !empty($certificatePending)): ?><p class="opp-notice ac-cert-wait" role="status"><?= icon('clock', 'icon--sm') ?> <?= e(\App\Services\Academy\CertificateService::waitingNotice()) ?></p><?php endif; ?>
                <?php endif; ?>

                <?php if ($locked): ?>
                    <p class="opp-notice opp-notice--closed"><?= icon('lock', 'icon--sm') ?> Finish all <?= (int) $progress['total'] ?> lessons to open the final exam. You have done <?= (int) $progress['done'] ?>.</p>
                    <p><a class="btn btn--outline btn--caps" href="<?= e(url($base)) ?>">Continue learning</a></p>
                <?php elseif (!$can['ok']): ?>
                    <p class="opp-notice opp-notice--closed"><?= icon('alert-triangle', 'icon--sm') ?> <?= e((string) $can['reason']) ?></p>
                <?php else: ?>
                    <form method="post" action="<?= e(url($base . '/quiz/' . (int) $quiz['id'] . '/start')) ?>"><?= csrf_field() ?>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit"><?= $openAttempt !== null ? 'Resume my attempt' : ($history ? 'Try again' : 'Start') ?> <?= icon('arrow-right', 'icon--sm') ?></button>
                        <?php if ($can['left'] !== null): ?><span class="ac-note ac-note--inline"><?= (int) $can['left'] ?> attempt<?= (int) $can['left'] === 1 ? '' : 's' ?> left</span><?php endif; ?>
                    </form>
                <?php endif; ?>

                <?php if ($history): ?>
                    <h2 class="ac-block__title ac-block__title--sm">Your attempts</h2>
                    <div class="table-wrap"><table class="table table--stack"><thead><tr><th scope="col">Date</th><th scope="col">Score</th><th scope="col">Result</th><th scope="col"><span class="visually-hidden">Review</span></th></tr></thead><tbody>
                        <?php foreach ($history as $h): ?>
                            <tr><td data-label="Date"><?= e(format_datetime($h['submitted_at'], 'j M Y, H:i')) ?></td><td data-label="Score"><?= (int) $h['correct'] ?>/<?= (int) $h['total'] ?> (<?= (int) round((float) $h['score_pct']) ?>%)</td>
                                <td data-label="Result"><span class="opp-badge<?= (int) $h['passed'] === 1 ? '' : ' opp-badge--soon' ?>"><?= (int) $h['passed'] === 1 ? 'Passed' : 'Not yet' ?></span></td>
                                <td><a href="<?= e(url($base . '/attempt/' . (int) $h['id'])) ?>">Review</a></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
