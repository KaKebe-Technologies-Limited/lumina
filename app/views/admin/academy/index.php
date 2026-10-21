<?php
/** Admin > LAF Academy overview. Vars: $stats, $classes, $top, $recent, $starter (starter course status rows), $manager. */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end();
$actions = [['label' => 'New course', 'url' => url('admin/academy/courses/create'), 'icon' => 'plus'], ['label' => 'Schedule a class', 'url' => url('admin/academy/classes/create'), 'icon' => 'video', 'class' => 'btn--outline']];
if ($manager) {
    $actions[] = ['label' => 'Public Academy', 'url' => url('academy'), 'icon' => 'external-link', 'class' => 'btn--ghost', 'target' => '_blank'];
}
$missing = array_filter($starter, static fn(array $s): bool => !$s['installed']);
?>
<?= partial('partials/admin/page-header', ['title' => 'LAF Academy', 'subtitle' => 'Free ICT courses with lessons, quizzes, virtual classes and certificates.', 'actions' => $actions]) ?>

<div class="aac-stats">
    <div class="aac-stat"><b><?= (int) $stats['published'] ?></b><span>Published courses<small><?= (int) $stats['courses'] ?> in total</small></span></div>
    <div class="aac-stat"><b><?= (int) $stats['learners'] ?></b><span>Learners<?php if ($manager): ?><small><?= (int) $stats['new30'] ?> new in 30 days</small><?php endif; ?></span></div>
    <div class="aac-stat"><b><?= (int) $stats['enrollments'] ?></b><span>Enrolments<small><?= (int) $stats['completed'] ?> completed</small></span></div>
    <div class="aac-stat"><b><?= (int) $stats['certificates'] ?></b><span>Certificates</span></div>
    <div class="aac-stat"><b><?= (int) $stats['attempts7'] ?></b><span>Quiz attempts<small>last 7 days</small></span></div>
    <?php if ($manager): ?><div class="aac-stat"><b><?= (int) $stats['unverified'] ?></b><span>Waiting to confirm e-mail</span></div><?php endif; ?>
    <div class="aac-stat"><b><?= (int) $stats['challenges'] ?></b><span><a href="<?= e(url('admin/academy/challenges')) ?>">Open challenges</a></span></div>
    <?php if ($manager): ?><div class="aac-stat"><b><?= (int) $stats['applications'] ?></b><span><a href="<?= e(url('admin/academy/facilitator-applications', ['status' => 'pending'])) ?>">Facilitator applications waiting</a></span></div><?php endif; ?>
</div>

<div class="aac-grid">
    <section class="card" aria-labelledby="ac-top">
        <div class="card__header"><h2 class="card__title" id="ac-top">Courses</h2><a href="<?= e(url('admin/academy/courses')) ?>">All courses</a></div>
        <div class="card__body">
            <?php if ($top === []): ?><p class="text-muted">No course yet. <?= $manager ? 'Install the starter courses below, or create your own.' : 'Create your first course.' ?></p><?php endif; ?>
            <ul class="aac-list">
                <?php foreach ($top as $c): ?>
                    <li><a href="<?= e(url('admin/academy/courses/' . (int) $c['id'] . '/curriculum')) ?>"><?= e($c['title']) ?></a>
                        <span><?= (int) $c['learners'] ?> learners &middot; <?= (int) $c['completed'] ?> completed &middot; <?= e($c['status']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <section class="card" aria-labelledby="ac-next">
        <div class="card__header"><h2 class="card__title" id="ac-next">Next virtual classes</h2><a href="<?= e(url('admin/academy/classes')) ?>">All classes</a></div>
        <div class="card__body">
            <?php if ($classes === []): ?><p class="text-muted">No class is scheduled. <a href="<?= e(url('admin/academy/classes/create')) ?>">Schedule one</a>.</p><?php endif; ?>
            <ul class="aac-list">
                <?php foreach ($classes as $cl): ?>
                    <li><a href="<?= e(url('admin/academy/classes/' . (int) $cl['id'] . '/edit')) ?>"><?= e($cl['title']) ?></a>
                        <span><?= e(format_datetime($cl['start_at'], 'D j M, H:i')) ?> EAT<?= !empty($cl['course_title']) ? ' &middot; ' . e($cl['course_title']) : '' ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
    <?php if ($manager): ?>
        <section class="card" aria-labelledby="ac-recent">
            <div class="card__header"><h2 class="card__title" id="ac-recent">New learners</h2><a href="<?= e(url('admin/academy/learners')) ?>">All learners</a></div>
            <div class="card__body">
                <?php if ($recent === []): ?><p class="text-muted">Nobody has signed up yet.</p><?php endif; ?>
                <ul class="aac-list">
                    <?php foreach ($recent as $l): ?>
                        <li><a href="<?= e(url('admin/academy/learners/' . (int) $l['id'])) ?>"><?= e($l['name']) ?></a>
                            <span><?= e($l['email']) ?> &middot; <?= $l['email_verified_at'] ? 'confirmed' : 'not confirmed' ?> &middot; <?= e(format_date($l['created_at'], 'j M')) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <section class="card" aria-labelledby="ac-quick">
            <div class="card__header"><h2 class="card__title" id="ac-quick">Quick links</h2></div>
            <div class="card__body">
                <ul class="aac-list aac-list--links">
                    <li><a href="<?= e(url('admin/academy/facilitators')) ?>">Facilitators: create teacher accounts</a></li>
                    <li><a href="<?= e(url('admin/academy/certificates')) ?>">Certificates: check, withdraw, download</a></li>
                    <li><a href="<?= e(url('admin/academy/learners/create/new')) ?>">Add a learner by hand</a></li>
                    <?php if (can('settings.manage')): ?><li><a href="<?= e(url('admin/settings')) ?>">Settings &gt; LAF Academy (sign-ups, certificate wording)</a></li><?php endif; ?>
                    <li><a href="<?= e(url('admin/pages')) ?>">Pages &gt; LAF Academy (the words on the public pages)</a></li>
                </ul>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php if ($manager): ?>
<section class="card" id="starter" aria-labelledby="ac-starter">
    <div class="card__header">
        <h2 class="card__title" id="ac-starter">Starter courses</h2>
        <span class="text-muted"><?= count($starter) - count($missing) ?> of <?= count($starter) ?> installed</span>
    </div>
    <div class="card__body">
        <p>Ready-made ICT courses written for beginners: lessons with practice activities, a quiz after each module and a final exam. Installing copies a course into the Academy so you can edit it like your own. A course you already installed is never overwritten.</p>
        <?php if ($missing !== []): ?>
            <form method="post" action="<?= e(url('admin/academy/starter/install-all')) ?>" class="aac-inline">
                <?= csrf_field() ?>
                <label class="check"><input type="checkbox" name="publish" value="1" checked><span>Publish them straight away</span></label>
                <button class="btn btn--primary" type="submit"><?= icon('download', 'icon--sm') ?> Install all <?= count($missing) ?> missing courses</button>
            </form>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="table table--stack">
                <caption class="visually-hidden">Starter courses</caption>
                <thead><tr><th scope="col">Course</th><th scope="col">Topic</th><th scope="col">Size</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($starter as $s): ?>
                    <tr>
                        <td class="cell-main" data-label="Course"><span class="cell-title"><?= e($s['title']) ?></span></td>
                        <td data-label="Topic"><?= e(\App\Services\Academy\CourseService::categoryLabel($s['category'])) ?></td>
                        <td data-label="Size"><?= (int) $s['modules'] ?> modules, <?= (int) $s['lessons'] ?> lessons</td>
                        <td data-label="Status"><?= $s['installed'] ? '<span class="badge badge--success">Installed (' . e($s['status']) . ')</span>' : '<span class="badge badge--neutral">Not installed</span>' ?></td>
                        <td class="col-actions">
                            <?php if (!$s['installed']): ?>
                                <form method="post" action="<?= e(url('admin/academy/starter/' . $s['slug'] . '/install')) ?>" class="inline-form"><?= csrf_field() ?>
                                    <label class="check check--sm"><input type="checkbox" name="publish" value="1" checked><span>Publish</span></label>
                                    <button class="btn btn--outline btn--sm" type="submit">Install</button></form>
                            <?php else: ?>
                                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/courses/' . (int) $s['course_id'] . '/curriculum')) ?>">Open</a>
                                <?php if ((int) $s['learners'] === 0): ?>
                                    <form method="post" action="<?= e(url('admin/academy/starter/' . $s['slug'] . '/reinstall')) ?>" class="inline-form" data-confirm="Reset this course to the starter version? Your changes to it will be lost.">
                                        <?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Reset</button></form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>
