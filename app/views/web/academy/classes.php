<?php
/** Virtual classes. Vars: $list ['upcoming','recorded'] (rows with may_join), $platforms, $learner. */
$card = static function (array $cl, bool $recorded) use ($platforms, $learner): string {
    ob_start(); ?>
    <li class="ac-class ac-class--full" data-reveal>
        <time class="ac-class__date" datetime="<?= e(format_date($cl['start_at'], 'Y-m-d')) ?>"><b><?= e(format_date($cl['start_at'], 'j')) ?></b><span><?= e(format_date($cl['start_at'], 'M')) ?></span></time>
        <div class="ac-class__main">
            <h3 class="ac-class__title"><?= e($cl['title']) ?> <span class="opp-chip"><?= e($platforms[$cl['platform']] ?? 'Online') ?></span></h3>
            <p class="ac-class__meta"><?= icon('clock', 'icon--sm') ?> <?= e(format_datetime($cl['start_at'], 'l j F Y, H:i')) ?> - <?= e(format_datetime($cl['end_at'], 'H:i')) ?> EAT (East Africa Time)</p>
            <p class="ac-class__meta"><?php if (!empty($cl['course_title'])): ?><?= icon('book', 'icon--sm') ?> <a href="<?= e(url('academy/courses/' . $cl['course_slug'])) ?>"><?= e($cl['course_title']) ?></a> &nbsp;<?php else: ?><?= icon('users', 'icon--sm') ?> Open to all learners &nbsp;<?php endif; ?>
                <?php if (!empty($cl['facilitator_name'])): ?><?= icon('user', 'icon--sm') ?> <?= e($cl['facilitator_name']) ?><?php endif; ?></p>
            <?php if (trim((string) $cl['description']) !== ''): ?><p class="ac-class__text"><?= e($cl['description']) ?></p><?php endif; ?>
            <div class="ac-class__actions">
                <?php if ($recorded): ?>
                    <?php if ($cl['may_join'] && !empty($cl['recording_url'])): ?><a class="btn btn--outline btn--sm" href="<?= e($cl['recording_url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('play', 'icon--sm') ?> Watch the recording</a>
                    <?php else: ?><span class="text-muted">Sign in as a learner to watch the recording.</span><?php endif; ?>
                <?php elseif ($cl['may_join'] && !empty($cl['join_url'])): ?>
                    <a class="btn btn--cta btn--caps btn--sm" href="<?= e($cl['join_url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('video', 'icon--sm') ?> Join the class</a>
                    <?php if (!empty($cl['passcode'])): ?><span class="ac-code">Passcode: <b><?= e($cl['passcode']) ?></b></span><?php endif; ?>
                    <a class="btn btn--ghost btn--sm" href="<?= e(url('academy/classes/' . (int) $cl['id'] . '/calendar.ics')) ?>"><?= icon('calendar', 'icon--sm') ?> Add to calendar</a>
                <?php elseif ($learner === null): ?>
                    <a class="btn btn--outline btn--sm" href="<?= e(url('academy/login', ['next' => 'academy/classes'])) ?>">Sign in to see the join link</a>
                <?php else: ?>
                    <span class="text-muted">Enrol in <?= !empty($cl['course_title']) ? 'the course "' . e($cl['course_title']) . '"' : 'a course' ?> to join this class.</span>
                <?php endif; ?>
            </div>
        </div>
    </li>
    <?php
    return (string) ob_get_clean();
};
?>
<?= partial('partials/page-hero', ['title' => 'Virtual [[classes]]', 'eyebrow' => 'LAF Academy', 'lead' => 'Live online sessions with our facilitators. Sign in to see the join link of a class you may attend. All times are East Africa Time.']) ?>
<section class="section">
    <div class="container container--narrow">
        <h2 class="ac-block__title">Upcoming classes</h2>
        <?php if ($list['upcoming'] === []): ?>
            <?= partial('partials/empty-state', ['title' => 'No class is scheduled right now', 'text' => 'New classes are announced here and by e-mail to enrolled learners. Meanwhile, keep learning at your own pace.', 'icon' => 'video']) ?>
        <?php else: ?>
            <ul class="ac-classlist"><?php foreach ($list['upcoming'] as $cl) { echo $card($cl, false); } ?></ul>
        <?php endif; ?>

        <?php if ($list['recorded']): ?>
            <h2 class="ac-block__title">Recorded classes</h2>
            <ul class="ac-classlist"><?php foreach ($list['recorded'] as $cl) { echo $card($cl, true); } ?></ul>
        <?php endif; ?>
    </div>
</section>
