<?php
/**
 * Course menu beside the lesson player. Vars: $course, $tree, $progress, $quizState (quiz id => ['best','passed']), $current (lesson id|null), $currentQuiz (quiz id|null), $examUnlocked.
 */
$base = 'academy/learn/' . $course['slug'];
$current = $current ?? null;
$currentQuiz = $currentQuiz ?? null;
$examUnlocked = $examUnlocked ?? true;
?>
<nav class="ac-side" aria-label="Course menu">
    <details class="ac-side__box" open>
        <summary>
            <span class="ac-side__title"><?= e($course['title']) ?></span>
            <span class="ac-side__pct"><?= (int) $progress['pct'] ?>%</span>
        </summary>
        <div class="ac-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $progress['pct'] ?>" aria-label="Your progress"><span style="width:<?= (int) $progress['pct'] ?>%"></span></div>
        <p class="ac-side__count"><?= (int) $progress['done'] ?> of <?= (int) $progress['total'] ?> lessons done</p>
        <?php foreach ($tree['modules'] as $m):
            $openModule = false;
            foreach ($m['lessons'] as $l) {
                if ((int) $l['id'] === (int) $current) { $openModule = true; }
            }
            if ($m['quiz'] !== null && (int) $m['quiz']['id'] === (int) $currentQuiz) { $openModule = true; } ?>
            <details class="ac-side__module"<?= $openModule ? ' open' : '' ?>>
                <summary><?= e($m['title']) ?></summary>
                <ol>
                    <?php foreach ($m['lessons'] as $l):
                        $done = in_array((int) $l['id'], $progress['done_ids'], true); ?>
                        <li class="<?= $done ? 'is-done ' : '' ?><?= (int) $l['id'] === (int) $current ? 'is-current' : '' ?>">
                            <a href="<?= e(url($base . '/lesson/' . (int) $l['id'])) ?>"<?= (int) $l['id'] === (int) $current ? ' aria-current="page"' : '' ?>>
                                <span class="ac-side__mark" aria-hidden="true"><?= $done ? icon('check-circle') : icon('circle') ?></span>
                                <span><?= e($l['title']) ?></span>
                                <?php if ($done): ?><span class="visually-hidden">(done)</span><?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($m['quiz'] !== null): $qs = $quizState[(int) $m['quiz']['id']] ?? ['best' => null, 'passed' => false]; ?>
                        <li class="ac-side__quiz <?= (int) $m['quiz']['id'] === (int) $currentQuiz ? 'is-current' : '' ?>">
                            <a href="<?= e(url($base . '/quiz/' . (int) $m['quiz']['id'])) ?>">
                                <span class="ac-side__mark" aria-hidden="true"><?= icon('help-circle') ?></span>
                                <span>Quiz<?= $qs['best'] !== null ? ' &middot; best ' . (int) round($qs['best']) . '%' : '' ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ol>
            </details>
        <?php endforeach; ?>
        <?php if ($tree['exam'] !== null): $es = $quizState[(int) $tree['exam']['id']] ?? ['best' => null, 'passed' => false]; ?>
            <p class="ac-side__exam<?= (int) $tree['exam']['id'] === (int) $currentQuiz ? ' is-current' : '' ?>">
                <a href="<?= e(url($base . '/quiz/' . (int) $tree['exam']['id'])) ?>">
                    <?= icon($examUnlocked ? 'award' : 'lock', 'icon--sm') ?> Final exam
                    <?php if ($es['passed']): ?><span class="opp-badge">Passed</span><?php elseif (!$examUnlocked): ?><span class="opp-badge">Locked</span><?php elseif ($es['best'] !== null): ?><span class="opp-badge">Best <?= (int) round($es['best']) ?>%</span><?php endif; ?>
                </a>
            </p>
        <?php endif; ?>
    </details>
</nav>
