<?php
/** Admin > Ambassadors list. Vars: $pagination, $f, $tabs, $regions, $canExport. */
use App\Models\AmbassadorApplication as Amb;

$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['region'] !== '' || $f['pledge'] !== '' || $f['status'] !== 'all';
$keep = ['q' => $f['q'], 'region' => $f['region'], 'pledge' => $f['pledge'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$headerActions = [];
if ($canExport) {
    $headerActions[] = ['label' => 'Export CSV', 'url' => url('admin/ambassadors/export', $keep + ['status' => $f['status'] === 'all' ? '' : $f['status']]), 'class' => 'btn--outline', 'icon' => 'download'];
}
$headerActions[] = ['label' => 'View the public page', 'url' => url('involvement/ambassadors'), 'class' => 'btn--ghost', 'icon' => 'external-link'];
?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Ambassadors', 'subtitle' => 'People anywhere in the world who offered to raise funds and lobby for the foundation, with the money or in-kind support they can pledge each year.', 'actions' => $headerActions]) ?>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/ambassadors', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/ambassadors')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, e-mail, country, occupation" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-region">Region</label>
            <select class="select" id="f-region" name="region" data-autosubmit><option value="">All regions</option>
                <?php foreach ($regions as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['region'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label class="field__label" for="f-pledge">Pledge</label>
            <select class="select" id="f-pledge" name="pledge" data-autosubmit><option value="">Any pledge</option>
                <?php foreach (Amb::PLEDGES as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['pledge'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>Newest first</option>
                <option value="oldest"<?= $f['sort'] === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
                <option value="amount"<?= $f['sort'] === 'amount' ? ' selected' : '' ?>>Largest amount first</option>
            </select></div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/ambassadors')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon' => 'globe', 'title' => $filtered ? 'No applications match these filters' : 'No ambassador applications yet',
            'text' => $filtered ? 'Try a different search or clear the filters.' : 'When someone applies on the Ambassadors page, the application appears here.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/ambassadors'), 'class' => 'btn--outline']] : [],
        ]) ?>
    <?php else: ?>
        <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        <div class="table-wrap">
            <table class="table table--stack">
                <thead><tr>
                    <th scope="col">Person</th><th scope="col">Where</th><th scope="col">Yearly pledge</th><th scope="col">Status</th><th scope="col">Received</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r): $href = url('admin/ambassadors/' . (int) $r['id']); ?>
                    <tr class="<?= $r['status'] === 'new' ? 'is-unread' : '' ?>">
                        <td data-label="Person"><span class="cell-title"><a href="<?= e($href) ?>"><?= e($r['name']) ?></a></span><span class="cell-sub"><?= e($r['email']) ?></span>
                            <?php if (!empty($r['occupation'])): ?><span class="cell-sub"><?= e($r['occupation']) ?></span><?php endif; ?></td>
                        <td data-label="Where"><?= e($r['country']) ?><span class="cell-sub"><?= e($regions[$r['region']] ?? $r['region']) ?></span></td>
                        <td data-label="Yearly pledge">
                            <?php if ($r['annual_amount'] !== null): ?><strong><?= e(number_format((float) $r['annual_amount'], 0)) ?> <?= e((string) $r['currency']) ?></strong><?php endif; ?>
                            <span class="cell-sub"><?= e(implode(' + ', array_map(static fn(string $k): string => Amb::PLEDGES[$k] ?? $k, Amb::pledgeList($r)))) ?></span>
                        </td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => Amb::LABELS[$r['status']] ?? null]) ?></td>
                        <td data-label="Received" class="nowrap"><?= e(format_date($r['created_at'], 'j M Y')) ?></td>
                        <td class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('eye', 'icon--sm') ?> Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
