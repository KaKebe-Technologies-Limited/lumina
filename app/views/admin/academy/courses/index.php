<?php
/** Admin > Academy > Courses. Vars: $rows, $pagination, $tab, $q, $category, $counts, $total, $categories, $manager. */
use App\Services\Academy\CourseService;

$tabs = ['all' => 'All', 'published' => 'Published', 'draft' => 'Drafts', 'archived' => 'Archived'];
$ret = http_build_query(array_filter(['status' => $tab === 'all' ? null : $tab, 'q' => $q, 'category' => $category]));
?>
<?= partial('partials/admin/page-header', ['title' => 'Courses', 'subtitle' => 'Each course has modules, lessons, a quiz per module and a final exam. Learners see only published courses.',
    'actions' => [['label' => 'New course', 'url' => url('admin/academy/courses/create'), 'icon' => 'plus']]]) ?>

<nav class="tabs content-tabs" aria-label="Course status">
    <?php foreach ($tabs as $k => $label): ?>
        <a class="tabs__item<?= $tab === $k ? ' is-active' : '' ?>" href="<?= e(url('admin/academy/courses', $k === 'all' ? [] : ['status' => $k])) ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span class="content-tabs__count"><?= (int) $counts[$k] ?></span></a>
    <?php endforeach; ?>
</nav>
<form class="filters" method="get" action="<?= e(url('admin/academy/courses')) ?>" role="search">
    <?php if ($tab !== 'all'): ?><input type="hidden" name="status" value="<?= e($tab) ?>"><?php endif; ?>
    <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Title or summary" maxlength="80"></div>
    <div class="field"><label class="field__label" for="f-cat">Topic</label>
        <select class="select" id="f-cat" name="category"><option value="">All topics</option><?php foreach ($categories as $k => $label): ?><option value="<?= e($k) ?>"<?= $category === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <button class="btn btn--outline" type="submit">Filter</button>
</form>

<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No course found', 'icon' => 'book', 'text' => $manager ? 'Create a course, or install the starter courses from the Academy overview.' : 'Create your first course.',
        'actions' => [['label' => 'New course', 'url' => url('admin/academy/courses/create'), 'class' => 'btn--primary']]]) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Courses</caption>
            <thead><tr><th scope="col">Course</th><th scope="col">Topic</th><th scope="col">Lessons</th><th scope="col">Learners</th><th scope="col">Facilitator</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): $id = (int) $r['id']; ?>
                <tr>
                    <td class="cell-main" data-label="Course"><span class="cell-title"><a href="<?= e(url('admin/academy/courses/' . $id . '/curriculum')) ?>"><?= e($r['title']) ?></a><?= (int) $r['is_featured'] === 1 ? ' ' . icon('star', 'icon--sm') : '' ?></span>
                        <span class="cell-sub"><?= e(CourseService::LEVELS[$r['level']] ?? $r['level']) ?><?= $r['source'] === 'starter' ? ' &middot; starter course' : '' ?></span></td>
                    <td data-label="Topic"><?= e(CourseService::categoryLabel($r['category'])) ?></td>
                    <td data-label="Lessons"><?= (int) $r['lessons'] ?></td>
                    <td data-label="Learners"><?= (int) $r['learners'] ?></td>
                    <td data-label="Facilitator"><?= $r['facilitator_name'] ? e($r['facilitator_name']) : '<span class="text-muted">None</span>' ?></td>
                    <td data-label="Status"><span class="badge badge--<?= $r['status'] === 'published' ? 'success' : ($r['status'] === 'draft' ? 'warning' : 'neutral') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                    <td class="col-actions">
                        <div class="table__actions">
                            <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/courses/' . $id . '/curriculum')) ?>" title="Lessons and quizzes" aria-label="Lessons and quizzes: <?= e($r['title']) ?>"><?= icon('layers', 'icon--sm') ?></a>
                            <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/courses/' . $id . '/edit')) ?>" title="Edit details" aria-label="Edit: <?= e($r['title']) ?>"><?= icon('edit', 'icon--sm') ?></a>
                            <a class="btn btn--ghost btn--icon" href="<?= e(url('academy/courses/' . $r['slug'])) ?>" target="_blank" rel="noopener" title="View on the website" aria-label="View: <?= e($r['title']) ?>"><?= icon('external-link', 'icon--sm') ?></a>
                            <form method="post" action="<?= e(url('admin/academy/courses/' . $id . '/status')) ?>" class="inline-form"><?= csrf_field() ?>
                                <input type="hidden" name="return" value="admin/academy/courses"><input type="hidden" name="to" value="<?= $r['status'] === 'published' ? 'draft' : 'published' ?>">
                                <button class="btn btn--outline btn--sm" type="submit"><?= $r['status'] === 'published' ? 'Unpublish' : 'Publish' ?><span class="visually-hidden">: <?= e($r['title']) ?></span></button></form>
                            <form method="post" action="<?= e(url('admin/academy/courses/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete "' . $r['title'] . '"' . ((int) $r['learners'] > 0 ? ' and the progress of ' . (int) $r['learners'] . ' learner(s)' : '') . '? This cannot be undone.') ?>"><?= csrf_field() ?>
                                <button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="Delete: <?= e($r['title']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/academy/courses']) ?>
<?php endif; ?>
