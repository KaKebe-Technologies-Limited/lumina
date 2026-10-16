<?php
/**
 * Admin > Team members list. OWNER: B8. Vars: $pager, $q, $sort, $dir, $group, $status, $filtered, $total, $canOrder, $groups.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$initials = static function (string $name): string {
    $out = '';
    foreach (array_slice(preg_split('/\s+/', trim($name)) ?: [], 0, 2) as $part) {
        $out .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $out;
};
$statusLabels = ['active' => 'Active', 'inactive' => 'Inactive'];
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Team members',
        'subtitle' => 'The people shown on the public Our Team page, grouped as board, staff, advisors and volunteers.',
        'actions'  => [
            ['label' => 'Reorder', 'url' => url('admin/team/reorder'), 'class' => 'btn--outline', 'icon' => 'arrow-up'],
            ['label' => 'Add team member', 'url' => url('admin/team/create'), 'icon' => 'plus'],
        ],
    ]) ?>

    <?php if ($total === 0): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'No team members yet',
            'text'    => 'Add the people you want visitors to meet. Until at least one active team member exists, the public Our Team page and its menu entry stay hidden.',
            'icon'    => 'users',
            'actions' => [['label' => 'Add the first team member', 'url' => url('admin/team/create')]],
        ]) ?>
    <?php else: ?>
        <form class="filters" method="get" action="<?= e(url('admin/team')) ?>" role="search" aria-label="Filter team members">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Name, role, email or biography']) ?>
            <div class="field">
                <label class="field__label" for="group">Group</label>
                <select class="select" id="group" name="group" data-autosubmit>
                    <option value="">All groups</option>
                    <?php foreach ($groups as $gv => $gl): ?><option value="<?= e($gv) ?>"<?= $group === $gv ? ' selected' : '' ?>><?= e($gl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="status">Status</label>
                <select class="select" id="status" name="status" data-autosubmit>
                    <option value="">Any status</option>
                    <?php foreach ($statusLabels as $sv => $sl): ?><option value="<?= e($sv) ?>"<?= $status === $sv ? ' selected' : '' ?>><?= e($sl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered, 'resetUrl' => url('admin/team')]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No team members match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url('admin/team'), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [['value' => 'activate', 'label' => 'Show on website (activate)'], ['value' => 'deactivate', 'label' => 'Hide from website (deactivate)']];
            if (can('content.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected team member(s) and their photos? This cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url('admin/team/bulk'), 'options' => $bulk, 'noun' => 'team members']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="team-table">
                    <caption class="visually-hidden">Team members</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all team members on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('name')) ?>"><?= partial('admin/org/_sort', ['label' => 'Member', 'col' => 'name', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('group')) ?>"><?= partial('admin/org/_sort', ['label' => 'Group', 'col' => 'group', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Status', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('order')) ?>"><?= partial('admin/org/_sort', ['label' => 'Order', 'col' => 'order', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $m):
                            $photo = img_url((string) ($m['photo'] ?? ''));
                            $isOn = (int) $m['is_active'] === 1;
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($m['name']) ?>"></td>
                                <td data-label="Member">
                                    <div class="org-person">
                                        <?php if ($photo !== ''): ?>
                                            <img class="org-avatar" src="<?= e($photo) ?>" alt="" width="44" height="44" loading="lazy">
                                        <?php else: ?>
                                            <span class="org-avatar org-avatar--initials" aria-hidden="true"><?= e($initials((string) $m['name'])) ?></span>
                                        <?php endif; ?>
                                        <span class="org-person__text">
                                            <a class="cell-title" href="<?= e(url('admin/team/' . (int) $m['id'] . '/edit')) ?>"><?= e($m['name']) ?></a>
                                            <?php if ($m['role_title'] !== ''): ?><span class="cell-sub"><?= e($m['role_title']) ?></span><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Group"><?= e($groups[$m['team_group']] ?? label_from($m['team_group'])) ?></td>
                                <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $isOn ? 'active' : 'inactive', 'label' => $isOn ? 'Active' : 'Inactive']) ?></td>
                                <td data-label="Order">
                                    <?php if ($canOrder): ?>
                                        <span class="org-order">
                                            <?= partial('admin/org/_row-action', ['action' => url('admin/team/' . (int) $m['id'] . '/move'), 'label' => 'Move ' . $m['name'] . ' up', 'icon' => 'arrow-up', 'fields' => ['direction' => 'up']]) ?>
                                            <?= partial('admin/org/_row-action', ['action' => url('admin/team/' . (int) $m['id'] . '/move'), 'label' => 'Move ' . $m['name'] . ' down', 'icon' => 'arrow-down', 'fields' => ['direction' => 'down']]) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= (int) $m['sort_order'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/team/' . (int) $m['id'] . '/edit')) ?>" title="Edit <?= e($m['name']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Edit <?= e($m['name']) ?></span></a>
                                        <?= partial('admin/org/_row-action', [
                                            'action' => url('admin/team/' . (int) $m['id'] . '/toggle'),
                                            'label'  => ($isOn ? 'Hide ' : 'Show ') . $m['name'] . ($isOn ? ' from the website' : ' on the website'),
                                            'icon'   => $isOn ? 'eye-off' : 'eye',
                                        ]) ?>
                                        <?php if (can('content.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url('admin/team/' . (int) $m['id'] . '/delete'),
                                                'label'   => 'Delete ' . $m['name'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete ' . $m['name'] . ' and their photo? This cannot be undone.',
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="form-note"><?= $canOrder
                ? 'Use the arrows to move a person within their group, or open Reorder to drag people into place.'
                : 'To move people up or down, sort the list by Order with no filters, or open Reorder.' ?></p>
            <?= partial('admin/org/_list-footer', ['pager' => $pager]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
