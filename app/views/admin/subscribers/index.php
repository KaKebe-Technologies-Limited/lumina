<?php
/**
 * Admin > Subscribers. Vars: $pagination, $f, $tabs, $canDelete, $canExport.
 */
$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['status'] !== 'all';
$keep = ['q' => $f['q'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$actions = ['unsubscribe' => 'Unsubscribe', 'resubscribe' => 'Resubscribe (they asked to rejoin)'];
if ($canDelete) {
    $actions['delete'] = 'Delete permanently';
}
$headerActions = [];
if ($canExport) {
    $headerActions[] = ['label' => 'Export active (CSV)', 'url' => url('admin/subscribers/export'), 'class' => 'btn--outline', 'icon' => 'download'];
    $headerActions[] = ['label' => 'Export everyone', 'url' => url('admin/subscribers/export', ['status' => 'all']), 'class' => 'btn--ghost', 'icon' => 'download'];
}
$addHasErrors = has_error('emails') || has_error('consent');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Subscribers', 'subtitle' => 'People who signed up to the newsletter from the website footer.', 'actions' => $headerActions]) ?>

    <section class="card inbox-add">
        <details<?= $addHasErrors ? ' open' : '' ?>>
            <summary class="card__header"><span class="card__title"><?= icon('plus', 'icon--sm') ?> Add subscribers</span></summary>
            <form method="post" action="<?= e(url('admin/subscribers')) ?>">
                <?= csrf_field() ?>
                <div class="card__body">
                    <?= partial('partials/form-field', [
                        'name' => 'emails', 'label' => 'Email addresses', 'type' => 'textarea', 'rows' => 5, 'required' => true,
                        'value' => old('emails', ''),
                        'help' => 'One per line. You can write "name@example.org" or "Jane Doe <name@example.org>". Up to 500 at a time. Addresses that are already on the list, or that unsubscribed earlier, are skipped.',
                    ]) ?>
                    <?= partial('partials/form-field', [
                        'name' => 'consent', 'type' => 'checkbox', 'value' => old('consent', ''), 'required' => true,
                        'label' => 'I confirm that these people agreed to receive the newsletter.',
                    ]) ?>
                </div>
                <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('plus', 'icon--sm') ?> Add to the list</button></div>
            </form>
        </details>
    </section>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/subscribers', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/subscribers')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Email or name" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>Newest first</option>
                <option value="oldest"<?= $f['sort'] === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
                <option value="email"<?= $f['sort'] === 'email' ? ' selected' : '' ?>>Email A to Z</option>
            </select>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/subscribers')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'user-plus',
            'title'   => $filtered ? 'No subscribers match these filters' : 'No subscribers yet',
            'text'    => $filtered ? 'Try a different search or clear the filters.' : 'People who sign up with the newsletter form in the website footer appear here.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/subscribers'), 'class' => 'btn--outline']] : [],
        ]) ?>
    <?php else: ?>
        <div class="toolbar">
            <?= partial('admin/inbox/_bulk', ['formId' => 'bulk-subscribers', 'action' => url('admin/subscribers/bulk'), 'actions' => $actions, 'danger' => ['delete', 'unsubscribe', 'resubscribe'], 'noun' => 'subscribers']) ?>
            <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        </div>
        <div class="table-wrap">
            <table class="table table--stack" id="subscribers-table">
                <thead>
                <tr>
                    <th class="col-narrow"><input type="checkbox" data-select-all aria-label="Select all subscribers on this page"></th>
                    <th scope="col">Email</th>
                    <th scope="col">Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Subscribed</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $s): $id = (int) $s['id']; $on = $s['status'] === 'subscribed'; ?>
                    <tr>
                        <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= $id ?>" form="bulk-subscribers" data-select-row aria-label="Select <?= e($s['email']) ?>"></td>
                        <td data-label="Email"><span class="cell-title"><?= e($s['email']) ?></span></td>
                        <td data-label="Name"><?= e($s['name'] ?: '-') ?></td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $s['status']]) ?>
                            <?php if (!$on && !empty($s['unsubscribed_at'])): ?><span class="cell-sub"><?= e(format_date($s['unsubscribed_at'], 'j M Y')) ?></span><?php endif; ?></td>
                        <td data-label="Subscribed" class="nowrap"><?= e(format_date($s['subscribed_at'], 'j M Y')) ?><?php if (!empty($s['source'])): ?><span class="cell-sub">via <?= e(label_from($s['source'])) ?></span><?php endif; ?></td>
                        <td data-label="" class="col-actions">
                            <div class="table__actions">
                                <?php if ($on): ?>
                                    <form method="post" action="<?= e(url('admin/subscribers/' . $id . '/unsubscribe')) ?>" data-confirm="Unsubscribe <?= e($s['email']) ?>? They will no longer be on the active list.">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--outline btn--sm" type="submit" title="Unsubscribe" aria-label="Unsubscribe <?= e($s['email']) ?>"><?= icon('x', 'icon--sm') ?><span class="inbox-btn-text">Unsubscribe</span></button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="<?= e(url('admin/subscribers/' . $id . '/resubscribe')) ?>" data-confirm="Resubscribe <?= e($s['email']) ?>? Only do this if the person asked to receive the newsletter again.">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--outline btn--sm" type="submit" title="Resubscribe" aria-label="Resubscribe <?= e($s['email']) ?>"><?= icon('user-plus', 'icon--sm') ?><span class="inbox-btn-text">Resubscribe</span></button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <form method="post" action="<?= e(url('admin/subscribers/' . $id . '/delete')) ?>" data-confirm="Delete <?= e($s['email']) ?> permanently? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Delete <?= e($s['email']) ?>"><?= icon('trash', 'icon--sm') ?><span class="inbox-btn-text">Delete</span></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
