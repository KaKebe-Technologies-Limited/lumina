<?php
/**
 * Admin > Partners list. OWNER: B8. Vars: $pager, $q, $sort, $dir, $type, $status, $filtered, $total, $canOrder, $types.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$statusLabels = ['active' => 'Active', 'inactive' => 'Inactive'];
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Partners',
        'subtitle' => 'Partners, funders, donors and collaborators shown on the public Partners page.',
        'actions'  => [
            ['label' => 'Reorder', 'url' => url('admin/partners/reorder'), 'class' => 'btn--outline', 'icon' => 'arrow-up'],
            ['label' => 'Add partner', 'url' => url('admin/partners/create'), 'icon' => 'plus'],
        ],
    ]) ?>

    <?php if ($total === 0): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'No partners yet',
            'text'    => 'Add the organisations that support or work with the foundation. Until at least one active partner exists, the public Partners page and its menu entry stay hidden.',
            'icon'    => 'building',
            'actions' => [['label' => 'Add the first partner', 'url' => url('admin/partners/create')]],
        ]) ?>
    <?php else: ?>
        <form class="filters" method="get" action="<?= e(url('admin/partners')) ?>" role="search" aria-label="Filter partners">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Name, website or description']) ?>
            <div class="field">
                <label class="field__label" for="type">Type</label>
                <select class="select" id="type" name="type" data-autosubmit>
                    <option value="">All types</option>
                    <?php foreach ($types as $tv => $tl): ?><option value="<?= e($tv) ?>"<?= $type === $tv ? ' selected' : '' ?>><?= e($tl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="status">Status</label>
                <select class="select" id="status" name="status" data-autosubmit>
                    <option value="">Any status</option>
                    <?php foreach ($statusLabels as $sv => $sl): ?><option value="<?= e($sv) ?>"<?= $status === $sv ? ' selected' : '' ?>><?= e($sl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered, 'resetUrl' => url('admin/partners')]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No partners match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url('admin/partners'), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [['value' => 'activate', 'label' => 'Show on website (activate)'], ['value' => 'deactivate', 'label' => 'Hide from website (deactivate)']];
            if (can('content.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected partner(s) and their logos? This cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url('admin/partners/bulk'), 'options' => $bulk, 'noun' => 'partners']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="partners-table">
                    <caption class="visually-hidden">Partners</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all partners on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('name')) ?>"><?= partial('admin/org/_sort', ['label' => 'Partner', 'col' => 'name', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('type')) ?>"><?= partial('admin/org/_sort', ['label' => 'Type', 'col' => 'type', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Status', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('order')) ?>"><?= partial('admin/org/_sort', ['label' => 'Order', 'col' => 'order', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $p):
                            $logo = img_url((string) ($p['logo'] ?? ''));
                            $isOn = (int) $p['is_active'] === 1;
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $p['id'] ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($p['name']) ?>"></td>
                                <td data-label="Partner">
                                    <div class="org-person">
                                        <?php if ($logo !== ''): ?>
                                            <img class="org-logo" src="<?= e($logo) ?>" alt="" width="64" height="44" loading="lazy">
                                        <?php else: ?>
                                            <span class="org-logo org-logo--empty" aria-hidden="true"><?= icon('building') ?></span>
                                        <?php endif; ?>
                                        <span class="org-person__text">
                                            <a class="cell-title" href="<?= e(url('admin/partners/' . (int) $p['id'] . '/edit')) ?>"><?= e($p['name']) ?></a>
                                            <?php if (!empty($p['website'])): ?><span class="cell-sub"><?= e(preg_replace('~^https?://(www\.)?~i', '', (string) $p['website'])) ?></span><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Type"><?= e($types[$p['partner_type']] ?? label_from($p['partner_type'])) ?></td>
                                <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $isOn ? 'active' : 'inactive', 'label' => $isOn ? 'Active' : 'Inactive']) ?></td>
                                <td data-label="Order">
                                    <?php if ($canOrder): ?>
                                        <span class="org-order">
                                            <?= partial('admin/org/_row-action', ['action' => url('admin/partners/' . (int) $p['id'] . '/move'), 'label' => 'Move ' . $p['name'] . ' up', 'icon' => 'arrow-up', 'fields' => ['direction' => 'up']]) ?>
                                            <?= partial('admin/org/_row-action', ['action' => url('admin/partners/' . (int) $p['id'] . '/move'), 'label' => 'Move ' . $p['name'] . ' down', 'icon' => 'arrow-down', 'fields' => ['direction' => 'down']]) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= (int) $p['sort_order'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/partners/' . (int) $p['id'] . '/edit')) ?>" title="Edit <?= e($p['name']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Edit <?= e($p['name']) ?></span></a>
                                        <?= partial('admin/org/_row-action', [
                                            'action' => url('admin/partners/' . (int) $p['id'] . '/toggle'),
                                            'label'  => ($isOn ? 'Hide ' : 'Show ') . $p['name'] . ($isOn ? ' from the website' : ' on the website'),
                                            'icon'   => $isOn ? 'eye-off' : 'eye',
                                        ]) ?>
                                        <?php if (can('content.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url('admin/partners/' . (int) $p['id'] . '/delete'),
                                                'label'   => 'Delete ' . $p['name'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete ' . $p['name'] . ' and its logo? This cannot be undone.',
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
                ? 'Use the arrows to move a partner up or down, or open Reorder to drag partners into place.'
                : 'To move partners up or down, sort the list by Order with no filters, or open Reorder.' ?></p>
            <?= partial('admin/org/_list-footer', ['pager' => $pager]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
