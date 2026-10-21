<?php /** Admin > Academy > Partner courses. Vars: $rows, $q, $state, $total, $starterPending, $broken. */
use App\Services\Academy\CourseService;
use App\Services\Academy\PartnerCourses;
$states = ['hidden' => 'Hidden', 'broken' => 'Broken links', 'unchecked' => 'Not checked']; ?>
<?= partial('partials/admin/page-header', ['title' => 'Partner courses', 'subtitle' => 'Free courses on other learning platforms. Visitors see a short summary and a button that opens the course on the provider site.',
    'actions' => [['label' => 'Add a course', 'url' => url('admin/academy/partner-courses/create'), 'icon' => 'plus']]]) ?>
<div class="card"><div class="card__body">
    <p><strong><?= (int) $total ?></strong> courses<?= $broken > 0 ? ', <strong>' . (int) $broken . '</strong> with a link that needs attention' : '' ?>.</p>
    <div class="table__actions">
        <form method="post" action="<?= e(url('admin/academy/partner-courses/check')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('check', 'icon--sm') ?> Check links (next 25)</button></form>
        <?php if ($starterPending > 0): ?>
            <form method="post" action="<?= e(url('admin/academy/partner-courses/starter')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('plus', 'icon--sm') ?> Add the <?= (int) $starterPending ?> missing starter courses</button></form>
        <?php endif; ?>
    </div>
    <p class="text-muted">A course whose address answers "not found" is hidden from visitors automatically until you fix or delete it. Free access and certificate rules are set by each platform and can change.</p>
</div></div>
<form class="filter-bar" method="get" action="<?= e(url('admin/academy/partner-courses')) ?>" role="search">
    <?= partial('partials/form-field', ['name' => 'q', 'id' => 'pq', 'label' => 'Search', 'value' => $q, 'maxlength' => 80]) ?>
    <?= partial('partials/form-field', ['name' => 'state', 'id' => 'ps', 'label' => 'Show', 'type' => 'select', 'options' => $states, 'empty' => 'All', 'value' => $state]) ?>
    <button class="btn btn--primary" type="submit">Filter</button>
</form>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No partner courses', 'icon' => 'book', 'text' => 'Add one, or load the starter list.']) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Partner courses</caption>
        <thead><tr><th scope="col">Course</th><th scope="col">Level</th><th scope="col">Topic</th><th scope="col">Link</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $id = (int) $r['id']; $st = $r['link_status'] === null ? null : (int) $r['link_status']; ?>
            <tr>
                <td class="cell-main" data-label="Course"><span class="cell-title"><a href="<?= e(url('admin/academy/partner-courses/' . $id . '/edit')) ?>"><?= e($r['title']) ?></a><?= (int) $r['is_active'] === 0 ? ' <span class="badge">Hidden</span>' : '' ?><?= (int) $r['is_featured'] === 1 ? ' <span class="badge">Featured</span>' : '' ?></span><span class="cell-sub"><?= e($r['provider']) ?></span></td>
                <td data-label="Level"><?= e(CourseService::LEVELS[$r['level']] ?? $r['level']) ?></td>
                <td data-label="Topic"><?= e(CourseService::categoryLabel($r['category'])) ?></td>
                <td data-label="Link"><a href="<?= e($r['url']) ?>" target="_blank" rel="noopener noreferrer nofollow">Open</a><span class="cell-sub"><?= e(PartnerCourses::statusLabel($st)) ?></span></td>
                <td class="col-actions"><div class="table__actions">
                    <form method="post" action="<?= e(url('admin/academy/partner-courses/' . $id . '/check')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--icon" type="submit" title="Check the link" aria-label="Check the link: <?= e($r['title']) ?>"><?= icon('check', 'icon--sm') ?></button></form>
                    <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/partner-courses/' . $id . '/edit')) ?>" title="Edit" aria-label="Edit: <?= e($r['title']) ?>"><?= icon('edit', 'icon--sm') ?></a>
                    <form method="post" action="<?= e(url('admin/academy/partner-courses/' . $id . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= (int) $r['is_active'] === 1 ? 'Hide' : 'Show' ?></button></form>
                    <form method="post" action="<?= e(url('admin/academy/partner-courses/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete "' . $r['title'] . '" from the list?') ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="Delete: <?= e($r['title']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
<?php endif; ?>
