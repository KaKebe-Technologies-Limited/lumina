<?php
/** Quiz / final exam start screen. Vars: $course, $tree, $progress, $quiz, $isExam, $can, $locked, $questions, $history, $best, $passed, $openAttempt, $certificate, $quizState. */
$base = 'academy/learn/' . $course['slug'];
$secured = !empty($secured);
$minutes = $secured ? (int) ($limitMinutes ?? 0) : (int) $quiz['time_limit_min'];
$maxLeaves = (int) ($maxLeaves ?? 3);
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
                    <?php if ($minutes > 0): ?><li><?= icon('clock', 'icon--sm') ?> <?= $minutes ?> minutes<?= !empty($extraMinutes) ? ' + ' . (int) $extraMinutes . ' extra' : '' ?></li><?php endif; ?>
                    <li><?= icon('refresh', 'icon--sm') ?> <?= (int) $quiz['max_attempts'] > 0 ? (int) $quiz['max_attempts'] . ' attempts' : 'As many attempts as you like' ?></li>
                </ul>

                <?php if ($isExam): ?><p class="ac-note"><?= e(content('academy', 'learn.pass_note')) ?></p><?php else: ?>
                    <p class="ac-note">This quiz helps you check what you learned in this module. The questions are shuffled each time, and you see the right answers with explanations after you finish.</p><?php endif; ?>

                <?php $review = false; foreach ((array) $history as $h0) { if ((int) $h0['passed'] === 1 && \App\Services\Academy\ExamSecurity::blocksCertificate($h0) && ($h0['flag_review'] ?? null) === null) { $review = true; } } ?>
                <?php if ($review && !$passed): ?><p class="opp-notice ac-cert-wait" role="status"><?= icon('clock', 'icon--sm') ?> Your last exam was stopped because you left the exam page too often. The Academy team will review it; until then it does not count towards your certificate.</p><?php endif; ?>
                <?php if ($passed): ?>
                    <p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> You passed this <?= $isExam ? 'exam' : 'quiz' ?><?= $best !== null ? ' with ' . (int) round((float) $best['score_pct']) . '%' : '' ?>.</p>
                    <?php if ($isExam && $certificate !== null): ?><p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/certificate/' . $certificate['code'])) ?>"><?= icon('award', 'icon--sm') ?> Open my certificate</a></p><?php endif; ?>
                    <?php if ($isExam && $certificate === null && empty($certificatePending) && !empty($certificateRequestable)): ?><p><form method="post" action="<?= e(url('academy/courses/' . $course['slug'] . '/certificate')) ?>" class="ac-cert-request"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit"><?= icon('award', 'icon--sm') ?> Request my certificate</button></form></p><?php endif; ?>
                    <?php if ($isExam && $certificate === null && !empty($certificatePending)): ?><p class="opp-notice ac-cert-wait" role="status"><?= icon('clock', 'icon--sm') ?> <?= e(\App\Services\Academy\CertificateService::waitingNotice()) ?></p><?php endif; ?>
                <?php endif; ?>

                <?php if ($locked): ?>
                    <p class="opp-notice opp-notice--closed"><?= icon('lock', 'icon--sm') ?> Finish all <?= (int) $progress['total'] ?> lessons to open the final exam. You have done <?= (int) $progress['done'] ?>.</p>
                    <p><a class="btn btn--outline btn--caps" href="<?= e(url($base)) ?>">Continue learning</a></p>
                <?php elseif (!$can['ok']): ?>
                    <p class="opp-notice opp-notice--closed"><?= icon('alert-triangle', 'icon--sm') ?> <?= e((string) $can['reason']) ?></p>
                <?php else: ?>
                    <?php if ($secured): ?>
                        <section class="ac-rules" aria-labelledby="ac-rules-title">
                            <h2 id="ac-rules-title" class="ac-rules__title"><?= icon('shield', 'icon--sm') ?> Exam rules - please read</h2>
                            <ul class="ac-rules__list">
                                <li><b>Time limit: <?= $minutes ?> minutes.</b> The clock starts when you press Start and keeps running if you refresh the page, close it or lose your connection. When it reaches zero, the answers you have given so far are sent and marked automatically.</li>
                                <li><b>No copying and pasting.</b> Copy, cut, paste, select-all, right-click menu and printing are switched off during the exam, and you may not copy the questions out or paste answers in.</li>
                                <li><b>Stay on the exam page.</b> Do not open other tabs, windows or apps. The exam can be open in one window only. Every time you leave the page is recorded with the time.</li>
                                <li><b>If you leave the page:</b> the first time you get a warning and the clock keeps running<?= $maxLeaves > 2 ? '; the next time is a final warning' : '' ?>. After you have left <b><?= $maxLeaves ?></b> time<?= $maxLeaves === 1 ? '' : 's' ?>, the exam is submitted automatically with your answers so far and marked <b>flagged</b>. A flagged exam gives no certificate until the Academy team has reviewed it.</li>
                                <li>Your answers are saved automatically every few seconds, so a dropped connection does not lose your work.</li>
                            </ul>
                            <p class="ac-note">These rules are here so every certificate is earned fairly. Good luck!</p>
                        </section>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url($base . '/quiz/' . (int) $quiz['id'] . '/start')) ?>"><?= csrf_field() ?>
                        <?php if ($secured && $openAttempt === null): ?>
                            <p class="ac-agree"><label class="ac-agree__label" for="ac-agree"><input type="checkbox" id="ac-agree" name="agree" value="1" required> <span>I understand and agree to these rules.</span></label></p>
                        <?php endif; ?>
                        <button class="btn btn--cta btn--caps btn--lg" type="submit"><?= $openAttempt !== null ? 'Resume my attempt' : ($history ? 'Try again' : 'Start') ?> <?= icon('arrow-right', 'icon--sm') ?></button>
                        <?php if ($can['left'] !== null): ?><span class="ac-note ac-note--inline"><?= (int) $can['left'] ?> attempt<?= (int) $can['left'] === 1 ? '' : 's' ?> left</span><?php endif; ?>
                    </form>
                <?php endif; ?>

                <?php if ($history): ?>
                    <h2 class="ac-block__title ac-block__title--sm">Your attempts</h2>
                    <div class="table-wrap"><table class="table table--stack"><thead><tr><th scope="col">Date</th><th scope="col">Score</th><th scope="col">Result</th><th scope="col"><span class="visually-hidden">Review</span></th></tr></thead><tbody>
                        <?php foreach ($history as $h): ?>
                            <tr><td data-label="Date"><?= e(format_datetime($h['submitted_at'], 'j M Y, H:i')) ?></td><td data-label="Score"><?= (int) $h['correct'] ?>/<?= (int) $h['total'] ?> (<?= (int) round((float) $h['score_pct']) ?>%)</td>
                                <td data-label="Result"><?php if (\App\Services\Academy\ExamSecurity::blocksCertificate($h)): ?><span class="opp-badge opp-badge--soon">Under review</span><?php else: ?><span class="opp-badge<?= (int) $h['passed'] === 1 ? '' : ' opp-badge--soon' ?>"><?= (int) $h['passed'] === 1 ? 'Passed' : 'Not yet' ?></span><?php endif; ?></td>
                                <td><a href="<?= e(url($base . '/attempt/' . (int) $h['id'])) ?>">Review</a></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>
