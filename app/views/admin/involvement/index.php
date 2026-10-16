<?php
/**
 * Admin > Involvement requests list. Vars: $pagination, $f, $tabs, $types, $labels, $canDelete, $canExport.
 */
$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['type'] !== '' || $f['from'] !== '' || $f['to'] !== '' || $f['status'] !== 'all';
$keep = ['q' => $f['q'], 'type' => $f['type'], 'from' => $f['from'], 'to' => $f['to'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$actions = ['mark_new' => 'Mark as new', 'mark_contacted' => 'Mark as contacted', 'mark_in_progress' => 'Mark as in progress', 'mark_closed' => 'Mark as closed'];
if ($canDelete) {
    $actions['delete'] = 'Delete permanently';
}
$headerActions = [];
if ($canExport) {
    $headerActions[] = ['label' => 'Export CSV', 'url' => url('admin/involvement/export', $keep + ['status' => $f['status'] === 'all' ? '' : $f['status']]), 'class' => 'btn--outline', 'icon' => 'download'];
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Involvement requests', 'subtitle' => 'People who offered to volunteer, partner or help, using the form on the Involvement page.', 'actions' => $headerActions]) ?>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/involvement', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/involvement')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, email, organisation or message" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-type">Type</label>
            <select class="select" id="f-type" name="type" data-autosubmit>
                <option value="">All types</option>
                <?php foreach ($types as $value => $label): ?><option value="<?= e($value) ?>"<?= $f['type'] === (string) $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                <?php if ($f['type'] !== '' && !isset($types[$f['type']])): ?><option value="<?= e($f['type']) ?>" selected><?= e(label_from($f['type'])) ?></option><?php endif; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-from">Received from</label><input class="input" id="f-from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
        <div class="field"><label class="field__label" for="f-to">Received until</label><input class="input" id="f-to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>Newest first</option>
                <option value="oldest"<?= $f['sort'] === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
            </select>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/involvement')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'heart',
            'title'   => $filtered ? 'No requests match these filters' : 'No involvement requests yet',
            'text'    => $filtered ? 'Try a different search or clear the filters.' : 'When someone offers to get involved through the website, their request appears here.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/involvement'), 'class' => 'btn--outline']] : [],
        ]) ?>
    <?php else: ?>
        <div class="toolbar">
            <?= partial('admin/inbox/_bulk', ['formId' => 'bulk-involvement', 'action' => url('admin/involvement/bulk'), 'actions' => $actions, 'danger' => ['delete'], 'noun' => 'requests']) ?>
            <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        </div>
        <div class="table-wrap">
            <table class="table table--stack" id="involvement-table">
                <thead>
                <tr>
                    <th class="col-narrow"><input type="checkbox" data-select-all aria-label="Select all requests on this page"></th>
                    <th scope="col">Person</th>
                    <th scope="col">Type</th>
                    <th scope="col">Message</th>
                    <th scope="col">Status</th>
                    <th scope="col">Received</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): $href = url('admin/involvement/' . (int) $r['id']); ?>
                    <tr class="<?= $r['status'] === 'new' ? 'is-unread' : '' ?>">
                        <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" form="bulk-involvement" data-select-row aria-label="Select the request from <?= e($r['name']) ?>"></td>
                        <td data-label="Person">
                            <span class="cell-title"><a href="<?= e($href) ?>"><?= e($r['name']) ?></a></span>
                            <span class="cell-sub"><?= e($r['email']) ?></span>
                            <?php if (!empty($r['organisation'])): ?><span class="cell-sub"><?= e($r['organisation']) ?></span><?php endif; ?>
                        </td>
                        <td data-label="Type"><span class="badge badge--outline"><?= e(label_from((string) $r['involvement_type'])) ?></span></td>
                        <td data-label="Message" class="inbox-preview"><span class="cell-sub"><?= e(excerpt((string) $r['message'], 110)) ?></span></td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => $labels[$r['status']] ?? null]) ?></td>
                        <td data-label="Received" class="nowrap"><?= e(format_date($r['created_at'], 'j M Y')) ?></td>
                        <td data-label="" class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('eye', 'icon--sm') ?> Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
