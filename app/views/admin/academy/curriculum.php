<?php
/** Curriculum of a course. Vars: $course, $tree (modules with lessons and quiz, unpublished included), $learners, $examRow. */
$cid = (int) $course['id'];
$status = $course['status'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => (string) $course['title'],
    'subtitle' => 'Modules, lessons and quizzes. Learners see published lessons in this order.',
    'actions' => [
        ['label' => 'Course details', 'url' => url('admin/academy/courses/' . $cid . '/edit'), 'icon' => 'edit', 'class' => 'btn--outline'],
        ['label' => 'View on the website', 'url' => url('academy/courses/' . $course['slug']), 'icon' => 'external-link', 'class' => 'btn--ghost', 'target' => '_blank'],
    ],
]) ?>

<div class="aac-statusbar">
    <span class="badge badge--<?= $status === 'published' ? 'success' : ($status === 'draft' ? 'warning' : 'neutral') ?>"><?= e(ucfirst($status)) ?></span>
    <span><?= (int) $tree['lessons'] ?> published lessons &middot; <?= e(academy_duration((int) $tree['minutes'])) ?> &middot; <?= (int) $tree['quizzes'] ?> quizzes<?= $learners > 0 ? ' &middot; ' . (int) $learners . ' learners enrolled' : '' ?></span>
    <form method="post" action="<?= e(url('admin/academy/courses/' . $cid . '/status')) ?>" class="inline-form"><?= csrf_field() ?>
        <input type="hidden" name="return" value="admin/academy/courses/<?= $cid ?>/curriculum">
        <?php if ($status === 'published'): ?><input type="hidden" name="to" value="draft"><button class="btn btn--outline btn--sm" type="submit">Unpublish</button>
        <?php else: ?><input type="hidden" name="to" value="published"><button class="btn btn--primary btn--sm" type="submit"><?= $status === 'archived' ? 'Publish again' : 'Publish the course' ?></button><?php endif; ?></form>
</div>

<?php if ($learners > 0): ?><p class="form-note">Learners are already taking this course. Deleting a lesson removes it from their progress; adding lessons lowers their percentage until they finish them.</p><?php endif; ?>

<?php foreach ($tree['modules'] as $mi => $m): $mid = (int) $m['id']; ?>
    <section class="card aac-module" id="module-<?= $mid ?>" aria-labelledby="mod-t-<?= $mid ?>">
        <div class="card__header">
            <h2 class="card__title" id="mod-t-<?= $mid ?>"><span class="aac-num"><?= $mi + 1 ?></span> <?= e($m['title']) ?></h2>
            <div class="table__actions">
                <?php foreach (['up' => ['arrow-up', 'Move module up'], 'down' => ['arrow-down', 'Move module down']] as $dir => [$ic, $lab]): ?>
                    <form method="post" action="<?= e(url('admin/academy/modules/' . $mid . '/move')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="direction" value="<?= $dir ?>">
                        <button class="btn btn--ghost btn--icon" type="submit" title="<?= e($lab) ?>" aria-label="<?= e($lab . ': ' . $m['title']) ?>"><?= icon($ic, 'icon--sm') ?></button></form>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card__body">
            <?php if (trim((string) $m['summary']) !== ''): ?><p class="text-muted"><?= e($m['summary']) ?></p><?php endif; ?>
            <?php if ($m['lessons'] === []): ?><p class="text-muted">No lesson yet.</p><?php endif; ?>
            <ol class="aac-lessons">
                <?php foreach ($m['lessons'] as $l): $lid = (int) $l['id']; ?>
                    <li class="<?= (int) $l['is_published'] === 1 ? '' : 'is-hidden' ?>">
                        <span class="aac-lessons__title"><a href="<?= e(url('admin/academy/lessons/' . $lid . '/edit')) ?>"><?= e($l['title']) ?></a>
                            <small><?= e(ucfirst((string) $l['kind'])) ?> &middot; <?= (int) $l['minutes'] ?> min<?= (int) $l['has_file'] === 1 ? ' &middot; handout' : '' ?><?= (int) $l['is_published'] === 1 ? '' : ' &middot; hidden' ?></small></span>
                        <span class="table__actions">
                            <?php foreach (['up' => ['arrow-up', 'Move up'], 'down' => ['arrow-down', 'Move down']] as $dir => [$ic, $lab]): ?>
                                <form method="post" action="<?= e(url('admin/academy/lessons/' . $lid . '/move')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="direction" value="<?= $dir ?>">
                                    <button class="btn btn--ghost btn--icon" type="submit" title="<?= e($lab) ?>" aria-label="<?= e($lab . ': ' . $l['title']) ?>"><?= icon($ic, 'icon--sm') ?></button></form>
                            <?php endforeach; ?>
                            <form method="post" action="<?= e(url('admin/academy/lessons/' . $lid . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?>
                                <button class="btn btn--ghost btn--icon" type="submit" title="<?= (int) $l['is_published'] === 1 ? 'Hide from learners' : 'Show to learners' ?>" aria-label="<?= (int) $l['is_published'] === 1 ? 'Hide' : 'Show' ?>: <?= e($l['title']) ?>"><?= icon((int) $l['is_published'] === 1 ? 'eye' : 'lock', 'icon--sm') ?></button></form>
                            <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/lessons/' . $lid . '/edit')) ?>" title="Edit" aria-label="Edit: <?= e($l['title']) ?>"><?= icon('edit', 'icon--sm') ?></a>
                            <form method="post" action="<?= e(url('admin/academy/lessons/' . $lid . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete the lesson "' . $l['title'] . '"?') ?>"><?= csrf_field() ?>
                                <button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="Delete: <?= e($l['title']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
            <div class="aac-actions">
                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/modules/' . $mid . '/lessons/create')) ?>"><?= icon('plus', 'icon--sm') ?> Add a lesson</a>
                <?php $qid = $m['quiz'] !== null ? (int) $m['quiz']['id'] : null; ?>
                <a class="btn btn--outline btn--sm" id="<?= $qid ? 'quiz-' . $qid : 'quiz-new-' . $mid ?>" href="<?= e(url('admin/academy/modules/' . $mid . '/quiz')) ?>"><?= icon('help-circle', 'icon--sm') ?> <?= $m['quiz'] !== null ? 'Edit the quiz (' . (int) $m['quiz']['questions'] . ' questions)' : 'Add a quiz' ?></a>
                <details class="aac-edit"><summary class="btn btn--ghost btn--sm"><?= icon('edit', 'icon--sm') ?> Rename the module</summary>
                    <form method="post" action="<?= e(url('admin/academy/modules/' . $mid)) ?>" class="aac-edit__form"><?= csrf_field() ?>
                        <div class="field"><label class="field__label" for="mt-<?= $mid ?>">Title</label><input class="input" id="mt-<?= $mid ?>" name="title" value="<?= e($m['title']) ?>" maxlength="190" required></div>
                        <div class="field"><label class="field__label" for="ms-<?= $mid ?>">One-line summary</label><input class="input" id="ms-<?= $mid ?>" name="summary" value="<?= e((string) $m['summary']) ?>" maxlength="255"></div>
                        <button class="btn btn--primary btn--sm" type="submit">Save</button></form></details>
                <form method="post" action="<?= e(url('admin/academy/modules/' . $mid . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete the module "' . $m['title'] . '" with all its lessons and its quiz?') ?>"><?= csrf_field() ?>
                    <button class="btn btn--ghost btn--sm btn-danger-hover" type="submit"><?= icon('trash', 'icon--sm') ?> Delete the module</button></form>
            </div>
        </div>
    </section>
<?php endforeach; ?>

<section class="card" aria-labelledby="mod-new">
    <div class="card__header"><h2 class="card__title" id="mod-new">Add a module</h2></div>
    <form class="card__body" method="post" action="<?= e(url('admin/academy/courses/' . $cid . '/modules')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'title', 'id' => 'new-mod-title', 'label' => 'Module title', 'required' => true, 'maxlength' => 190, 'value' => '', 'help' => 'For example: Module 3: Tables and pictures']) ?>
            <?= partial('partials/form-field', ['name' => 'summary', 'id' => 'new-mod-sum', 'label' => 'One-line summary (optional)', 'maxlength' => 255, 'value' => '']) ?>
        </div>
        <button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add the module</button>
    </form>
</section>

<section class="card" aria-labelledby="exam-t">
    <div class="card__header"><h2 class="card__title" id="exam-t"><?= icon('award', 'icon--sm') ?> Final exam</h2></div>
    <div class="card__body">
        <?php if ($tree['exam'] !== null): ?>
            <p>The final exam has <b><?= (int) $tree['exam']['questions'] ?></b> questions; learners need <b><?= (int) $tree['exam']['pass_mark'] ?>%</b> to pass and earn the certificate.</p>
        <?php else: ?>
            <p class="text-muted">There is no final exam yet<?= $examRow !== null ? ' (the exam has no questions)' : '' ?>. Without one, learners get the certificate as soon as they finish every lesson.</p>
        <?php endif; ?>
        <p><a class="btn btn--outline" id="quiz-exam" href="<?= e(url('admin/academy/courses/' . $cid . '/exam')) ?>"><?= icon('help-circle', 'icon--sm') ?> <?= $examRow !== null ? 'Edit the final exam' : 'Create the final exam' ?></a></p>
    </div>
</section>
