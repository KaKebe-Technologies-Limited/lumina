<?php
/**
 * Admin > Messages list. Vars: $pagination, $f (filters), $tabs, $labels, $canDelete, $canExport.
 */
$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['from'] !== '' || $f['to'] !== '' || $f['status'] !== 'all';
$keep = ['q' => $f['q'], 'from' => $f['from'], 'to' => $f['to'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$actions = ['mark_read' => 'Mark as read', 'mark_new' => 'Mark as unread', 'mark_replied' => 'Mark as replied', 'archive' => 'Archive'];
if ($canDelete) {
    $actions['delete'] = 'Delete permanently';
}
$headerActions = [];
if ($canExport) {
    $headerActions[] = ['label' => 'Export CSV', 'url' => url('admin/messages/export', $keep + ['status' => $f['status'] === 'all' ? '' : $f['status']]), 'class' => 'btn--outline', 'icon' => 'download'];
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Messages', 'subtitle' => 'Messages sent through the contact form on the website.', 'actions' => $headerActions]) ?>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/messages', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/messages')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, email, subject or message text" maxlength="100"></div>
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
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/messages')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'inbox',
            'title'   => $filtered ? 'No messages match these filters' : 'No messages yet',
            'text'    => $filtered ? 'Try a different search or clear the filters.' : 'When someone writes to you through the contact form, the message appears here.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/messages'), 'class' => 'btn--outline']] : [],
        ]) ?>
    <?php else: ?>
        <div class="toolbar">
            <?= partial('admin/inbox/_bulk', ['formId' => 'bulk-messages', 'action' => url('admin/messages/bulk'), 'actions' => $actions, 'danger' => ['delete'], 'noun' => 'messages']) ?>
            <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        </div>
        <div class="table-wrap">
            <table class="table table--stack" id="messages-table">
                <thead>
                <tr>
                    <th class="col-narrow"><input type="checkbox" data-select-all aria-label="Select all messages on this page"></th>
                    <th scope="col">From</th>
                    <th scope="col">Message</th>
                    <th scope="col">Status</th>
                    <th scope="col">Received</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $m): $href = url('admin/messages/' . (int) $m['id']); ?>
                    <tr class="<?= $m['status'] === 'new' ? 'is-unread' : '' ?>">
                        <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" form="bulk-messages" data-select-row aria-label="Select the message from <?= e($m['name']) ?>"></td>
                        <td data-label="From">
                            <span class="cell-title"><a href="<?= e($href) ?>"><?= e($m['name']) ?></a></span>
                            <span class="cell-sub"><?= e($m['email']) ?></span>
                        </td>
                        <td data-label="Message" class="inbox-preview">
                            <span class="inbox-subject"><?= e(trim((string) $m['subject']) !== '' ? $m['subject'] : '(no subject)') ?></span>
                            <span class="cell-sub"><?= e(excerpt((string) $m['message'], 110)) ?></span>
                        </td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $m['status'], 'label' => $labels[$m['status']] ?? null]) ?></td>
                        <td data-label="Received" class="nowrap"><?= e(format_date($m['created_at'], 'j M Y')) ?><span class="cell-sub"><?= e(format_date($m['created_at'], 'H:i')) ?></span></td>
                        <td data-label="" class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('eye', 'icon--sm') ?> Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
