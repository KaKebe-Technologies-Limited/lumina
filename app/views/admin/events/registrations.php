<?php
/**
 * Admin > Events > Registrations manager. OWNER: B8.
 * Vars: $event, $pager, $q, $status, $sort, $dir, $filtered, $stats, $flow (status => allowed next statuses), $labels (status => label).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$eid = (int) $event['id'];
$base = 'admin/events/' . $eid . '/registrations';
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$exportQuery = array_filter(['q' => $q, 'status' => $status, 'sort' => $sort, 'dir' => $dir], static fn($v): bool => $v !== '');
$actions = [['label' => 'Back to the event', 'url' => url('admin/events/' . $eid . '/edit'), 'class' => 'btn--outline', 'icon' => 'arrow-left']];
if (can('export.csv') && $stats['total'] > 0) {
    array_unshift($actions, ['label' => 'Export CSV', 'url' => url($base . '/export', $exportQuery), 'class' => 'btn--outline', 'icon' => 'download']);
}
$startTs = strtotime((string) $event['start_at']);
$stepIcons = ['attended' => 'check', 'cancelled' => 'x', 'registered' => 'refresh'];
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Registrations',
        'subtitle' => $event['title'] . ' - ' . date('D j M Y, H:i', $startTs),
        'actions'  => $actions,
    ]) ?>

    <div class="stat-grid">
        <div class="stat-tile"><span class="stat-tile__icon"><?= icon('user-plus') ?></span><span><span class="stat-tile__value"><?= (int) $stats['registered'] ?></span><span class="stat-tile__label">Registered</span></span></div>
        <div class="stat-tile"><span class="stat-tile__icon"><?= icon('check-circle') ?></span><span><span class="stat-tile__value"><?= (int) $stats['attended'] ?></span><span class="stat-tile__label">Attended</span></span></div>
        <div class="stat-tile"><span class="stat-tile__icon"><?= icon('x') ?></span><span><span class="stat-tile__value"><?= (int) $stats['cancelled'] ?></span><span class="stat-tile__label">Cancelled</span></span></div>
        <div class="stat-tile"><span class="stat-tile__icon"><?= icon('users') ?></span><span><span class="stat-tile__value"><?= $stats['capacity'] === null ? '&infin;' : (int) $stats['left'] ?></span><span class="stat-tile__label"><?= $stats['capacity'] === null ? 'Places (no limit)' : 'Places left of ' . (int) $stats['capacity'] ?></span></span></div>
    </div>

    <?php if ((int) $event['registration_enabled'] !== 1): ?>
        <div class="alert alert--info" role="note"><div class="alert__body">Online registration is switched off for this event, so visitors cannot sign up on the website. You can still add people by hand below.</div></div>
    <?php endif; ?>

    <details class="card admin-org__details"<?= errors() !== [] ? ' open' : '' ?>>
        <summary class="card__header"><span class="card__title"><?= icon('plus', 'icon--sm') ?> Add a registration by hand</span></summary>
        <div class="card__body">
            <form method="post" action="<?= e(url($base)) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'maxlength' => 150, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone (optional)', 'type' => 'tel', 'maxlength' => 40, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'organisation', 'label' => 'Organisation (optional)', 'maxlength' => 190, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'notes', 'label' => 'Notes (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 2000, 'class' => 'field--full', 'help' => 'Internal notes. Never shown to the public.']) ?>
                </div>
                <div class="form-actions form-actions--inline">
                    <button class="btn btn--primary btn--sm" type="submit"><?= icon('plus', 'icon--sm') ?> Add registration</button>
                </div>
            </form>
        </div>
    </details>

    <?php if ($stats['total'] === 0): ?>
        <?= partial('partials/empty-state', [
            'title' => 'No registrations yet',
            'text'  => (int) $event['registration_enabled'] === 1
                ? 'People who sign up on the event page will appear here, where you can mark who attended and export the list.'
                : 'Switch on registration in the event settings, or add people by hand above.',
            'icon'  => 'users',
        ]) ?>
    <?php else: ?>
        <form class="filters" method="get" action="<?= e(url($base)) ?>" role="search" aria-label="Filter registrations">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Name, email, phone or organisation']) ?>
            <div class="field">
                <label class="field__label" for="status">Status</label>
                <select class="select" id="status" name="status" data-autosubmit>
                    <option value="">Any status</option>
                    <?php foreach ($labels as $sv => $sl): ?><option value="<?= e($sv) ?>"<?= $status === $sv ? ' selected' : '' ?>><?= e($sl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered, 'resetUrl' => url($base)]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No registrations match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url($base), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [
                ['value' => 'attended', 'label' => 'Mark as attended'],
                ['value' => 'registered', 'label' => 'Mark as registered (undo / reinstate)'],
                ['value' => 'cancelled', 'label' => 'Cancel registration'],
            ];
            if (can('inbox.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected registration(s)? Their personal details are removed and this cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url($base . '/bulk'), 'options' => $bulk, 'noun' => 'registrations']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="registrations-table">
                    <caption class="visually-hidden">Registrations for <?= e($event['title']) ?></caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all registrations on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('name')) ?>"><?= partial('admin/org/_sort', ['label' => 'Name', 'col' => 'name', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('email')) ?>"><?= partial('admin/org/_sort', ['label' => 'Contact', 'col' => 'email', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Status', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('registered')) ?>"><?= partial('admin/org/_sort', ['label' => 'Registered', 'col' => 'registered', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $r):
                            $rid = (int) $r['id'];
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= $rid ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($r['name']) ?>"></td>
                                <td data-label="Name"><div class="org-stack">
                                    <a class="cell-title" href="<?= e(url($base . '/' . $rid)) ?>"><?= e($r['name']) ?></a>
                                    <?php if (!empty($r['organisation'])): ?><span class="cell-sub"><?= e($r['organisation']) ?></span><?php endif; ?>
                                    <?php if (!empty($r['notes'])): ?><span class="cell-sub"><?= icon('file-text', 'icon--sm') ?> Has notes</span><?php endif; ?></div></td>
                                <td data-label="Contact"><div class="org-stack">
                                    <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a>
                                    <?php if (!empty($r['phone'])): ?><span class="cell-sub"><?= e($r['phone']) ?></span><?php endif; ?></div></td>
                                <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => $labels[$r['status']] ?? label_from($r['status'])]) ?></td>
                                <td data-label="Registered"><time datetime="<?= e(date('c', (int) strtotime((string) $r['created_at']))) ?>"><?= e(date('j M Y, H:i', (int) strtotime((string) $r['created_at']))) ?></time></td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <?php foreach ($flow[$r['status']] ?? [] as $next):
                                            $stepText = $next === 'attended' ? 'Mark attended' : ($next === 'cancelled' ? 'Cancel' : ($r['status'] === 'attended' ? 'Undo attended' : 'Reinstate'));
                                        ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action' => url($base . '/' . $rid . '/status'),
                                                'label'  => $stepText . ': ' . $r['name'],
                                                'text'   => $stepText,
                                                'icon'   => $stepIcons[$next],
                                                'class'  => 'btn--outline btn--sm',
                                                'fields' => ['status' => $next],
                                            ]) ?>
                                        <?php endforeach; ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url($base . '/' . $rid)) ?>" title="Open <?= e($r['name']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Open registration of <?= e($r['name']) ?></span></a>
                                        <?php if (can('inbox.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url($base . '/' . $rid . '/delete'),
                                                'label'   => 'Delete registration of ' . $r['name'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete the registration of ' . $r['name'] . '? Their personal details are removed and this cannot be undone.',
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= partial('admin/org/_list-footer', ['pager' => $pager]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
