<?php /** Admin > Academy > Virtual classes. Vars: $rows, $tab, $counts, $platforms. */
$tabs = ['upcoming' => 'Upcoming', 'past' => 'Past', 'cancelled' => 'Cancelled']; ?>
<?= partial('partials/admin/page-header', ['title' => 'Virtual classes', 'subtitle' => 'Live online sessions for the learners of a course or for everyone. Learners see the join link after they sign in.',
    'actions' => [['label' => 'Schedule a class', 'url' => url('admin/academy/classes/create'), 'icon' => 'plus']]]) ?>
<nav class="tabs content-tabs" aria-label="Classes">
    <?php foreach ($tabs as $k => $label): ?>
        <a class="tabs__item<?= $tab === $k ? ' is-active' : '' ?>" href="<?= e(url('admin/academy/classes', ['when' => $k])) ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span class="content-tabs__count"><?= (int) $counts[$k] ?></span></a>
    <?php endforeach; ?>
</nav>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No class here', 'icon' => 'video', 'text' => 'Schedule a class and invite your learners by e-mail.', 'actions' => [['label' => 'Schedule a class', 'url' => url('admin/academy/classes/create'), 'class' => 'btn--primary']]]) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Virtual classes</caption>
        <thead><tr><th scope="col">Class</th><th scope="col">When (EAT)</th><th scope="col">For</th><th scope="col">Platform</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $c): $id = (int) $c['id']; ?>
            <tr>
                <td class="cell-main" data-label="Class"><span class="cell-title"><a href="<?= e(url('admin/academy/classes/' . $id . '/edit')) ?>"><?= e($c['title']) ?></a></span><span class="cell-sub"><?= e((string) ($c['facilitator_name'] ?? '')) ?></span></td>
                <td data-label="When"><span class="nowrap"><?= e(format_datetime($c['start_at'], 'D j M Y, H:i')) ?></span><span class="cell-sub">until <?= e(format_datetime($c['end_at'], 'H:i')) ?></span></td>
                <td data-label="For"><?= $c['audience'] === 'all' ? 'All learners' : e((string) ($c['course_title'] ?? 'A course')) ?></td>
                <td data-label="Platform"><?= e($platforms[$c['platform']] ?? 'Online') ?></td>
                <td class="col-actions"><div class="table__actions">
                    <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/classes/' . $id . '/edit')) ?>" title="Edit" aria-label="Edit: <?= e($c['title']) ?>"><?= icon('edit', 'icon--sm') ?></a>
                    <?php if ($c['status'] === 'scheduled'): ?>
                        <form method="post" action="<?= e(url('admin/academy/classes/' . $id . '/status')) ?>" class="inline-form" data-confirm="Cancel this class? Learners see that it is cancelled."><?= csrf_field() ?><input type="hidden" name="to" value="cancelled"><button class="btn btn--outline btn--sm" type="submit">Cancel</button></form>
                    <?php elseif ($c['status'] === 'cancelled'): ?>
                        <form method="post" action="<?= e(url('admin/academy/classes/' . $id . '/status')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="to" value="scheduled"><button class="btn btn--outline btn--sm" type="submit">Reopen</button></form>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('admin/academy/classes/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete the class "' . $c['title'] . '"?') ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="Delete: <?= e($c['title']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
<?php endif; ?>
