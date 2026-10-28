<?php
/** Learner portal > My courses. Vars: $me, $tab, $unread, $portrait, $courses. */
$active = array_values(array_filter($courses, static fn(array $c): bool => $c['completed_at'] === null));
$finished = array_values(array_filter($courses, static fn(array $c): bool => $c['completed_at'] !== null));
?>
<?= partial('partials/page-hero', ['title' => 'My courses', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Track your progress in every course and pick up where you stopped.',
    'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>
<section class="section ac-dash">
    <div class="container">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>
        <?php if ($courses === []): ?>
            <?= partial('partials/empty-state', ['title' => content('academy', 'learn.dashboard_empty'), 'icon' => 'book', 'actions' => [['label' => 'Browse the courses', 'url' => url('academy') . '#courses', 'class' => 'btn--cta btn--caps']]]) ?>
        <?php endif; ?>
        <?php if ($active !== []): ?>
            <h2 class="ac-block__title">In progress (<?= count($active) ?>)</h2>
            <?php foreach ($active as $c): ?><?= partial('web/academy/portal/_course', ['c' => $c]) ?><?php endforeach; ?>
        <?php endif; ?>
        <?php if ($finished !== []): ?>
            <h2 class="ac-block__title">Completed (<?= count($finished) ?>)</h2>
            <?php foreach ($finished as $c): ?><?= partial('web/academy/portal/_course', ['c' => $c]) ?><?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
