<?php
/**
 * Admin > Donations. Vars: $pagination, $f, $tabs, $summary, $methods, $statusLabels, $canDelete, $canExport.
 */
use App\Models\Donation;
use App\Services\Inbox\Support;

$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['method'] !== '' || $f['from'] !== '' || $f['to'] !== '' || $f['status'] !== 'all';
$summaryFiltered = $f['q'] !== '' || $f['method'] !== '' || $f['from'] !== '' || $f['to'] !== '';
$keep = ['q' => $f['q'], 'method' => $f['method'], 'from' => $f['from'], 'to' => $f['to'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$tones = ['pledged' => 'warning', 'received' => 'success', 'failed' => 'danger', 'cancelled' => 'neutral'];

// one line per currency: "UGX 1,500,000"
$amounts = static function (array $perCurrency): string {
    if ($perCurrency === []) {
        return '<span class="text-muted">-</span>';
    }
    ksort($perCurrency);
    $out = [];
    foreach ($perCurrency as $cur => $cell) {
        if ($cell['sum'] > 0) {
            $out[] = '<span class="inbox-amount">' . e(Support::money($cell['sum'], (string) $cur)) . '</span>';
        }
    }
    return $out === [] ? '<span class="text-muted">-</span>' : implode('', $out);
};
$countOf = static fn(array $perCurrency): int => (int) array_sum(array_column($perCurrency, 'count'));

$headerActions = [['label' => 'Record a donation', 'url' => url('admin/donations/create'), 'class' => 'btn--primary', 'icon' => 'plus']];
if ($canExport) {
    $headerActions[] = ['label' => 'Export CSV', 'url' => url('admin/donations/export', $keep + ['status' => $f['status'] === 'all' ? '' : $f['status']]), 'class' => 'btn--outline', 'icon' => 'download'];
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Donations', 'subtitle' => 'Pledges from the website and gifts recorded by the team. Amounts in different currencies are never added together.', 'actions' => $headerActions]) ?>

    <section aria-labelledby="donation-totals">
        <h2 id="donation-totals" class="visually-hidden">Totals</h2>
        <?php if ($summaryFiltered): ?><p class="inbox-notice">Totals below follow your search, method and date filters.</p><?php endif; ?>
        <div class="inbox-stats">
            <div class="stat-tile"><span class="stat-tile__icon"><?= icon('check-circle') ?></span><div><span class="stat-tile__value inbox-stack"><?= $amounts($summary['received']) ?></span><span class="stat-tile__label">Received (<?= $countOf($summary['received']) ?>)</span></div></div>
            <div class="stat-tile"><span class="stat-tile__icon"><?= icon('clock') ?></span><div><span class="stat-tile__value inbox-stack"><?= $amounts($summary['pledged']) ?></span><span class="stat-tile__label">Pledged, not yet received (<?= $countOf($summary['pledged']) ?>)</span></div></div>
            <div class="stat-tile"><span class="stat-tile__icon"><?= icon('banknote') ?></span><div><span class="stat-tile__value"><?= (int) $summary['count'] ?></span><span class="stat-tile__label">Donations in total</span></div></div>
            <div class="stat-tile"><span class="stat-tile__icon"><?= icon('x') ?></span><div><span class="stat-tile__value"><?= $countOf($summary['byStatus']['cancelled'] ?? []) ?></span><span class="stat-tile__label">Cancelled</span></div></div>
        </div>

        <?php if ($summary['count'] > 0): ?>
            <div class="inbox-breakdown">
                <section class="card">
                    <div class="card__header"><h3 class="card__title">By status</h3></div>
                    <div class="table-wrap"><table class="table"><thead><tr><th scope="col">Status</th><th scope="col" class="col-num">Count</th><th scope="col" class="col-num">Amount</th></tr></thead><tbody>
                        <?php foreach (Donation::STATUSES as $s): $cells = $summary['byStatus'][$s] ?? []; if ($cells === [] && $s !== 'received' && $s !== 'pledged') { continue; } ?>
                            <tr><td><?= partial('partials/admin/status-badge', ['status' => $s, 'label' => $statusLabels[$s] ?? null, 'tone' => $tones[$s] ?? 'neutral']) ?></td><td class="col-num"><?= $countOf($cells) ?></td><td class="col-num"><?= $amounts($cells) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                </section>
                <section class="card">
                    <div class="card__header"><h3 class="card__title">By method (received)</h3></div>
                    <?php if ($summary['byMethod'] === []): ?><div class="card__body"><p class="text-muted">Nothing received yet.</p></div><?php else: ?>
                    <div class="table-wrap"><table class="table"><thead><tr><th scope="col">Method</th><th scope="col" class="col-num">Count</th><th scope="col" class="col-num">Amount</th></tr></thead><tbody>
                        <?php foreach ($methods as $mv => $ml): if (!isset($summary['byMethod'][$mv])) { continue; } ?>
                            <tr><td><?= e($ml) ?></td><td class="col-num"><?= $countOf($summary['byMethod'][$mv]) ?></td><td class="col-num"><?= $amounts($summary['byMethod'][$mv]) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <?php endif; ?>
                </section>
                <section class="card">
                    <div class="card__header"><h3 class="card__title">By month (received)</h3></div>
                    <?php if ($summary['byMonth'] === []): ?><div class="card__body"><p class="text-muted">Nothing received yet.</p></div><?php else: ?>
                    <div class="table-wrap"><table class="table"><thead><tr><th scope="col">Month</th><th scope="col" class="col-num">Count</th><th scope="col" class="col-num">Amount</th></tr></thead><tbody>
                        <?php foreach ($summary['byMonth'] as $month => $cells): ?>
                            <tr><td class="nowrap"><?= e(format_date($month . '-01', 'M Y')) ?></td><td class="col-num"><?= $countOf($cells) ?></td><td class="col-num"><?= $amounts($cells) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></div>
                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
    </section>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/donations', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/donations')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Donor, email, reference or receipt number" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-method">Method</label>
            <select class="select" id="f-method" name="method" data-autosubmit>
                <option value="">All methods</option>
                <?php foreach ($methods as $mv => $ml): ?><option value="<?= e($mv) ?>"<?= $f['method'] === $mv ? ' selected' : '' ?>><?= e($ml) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-from">Date from</label><input class="input" id="f-from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
        <div class="field"><label class="field__label" for="f-to">Date until</label><input class="input" id="f-to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>Newest first</option>
                <option value="oldest"<?= $f['sort'] === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
                <option value="amount_high"<?= $f['sort'] === 'amount_high' ? ' selected' : '' ?>>Largest amount</option>
                <option value="amount_low"<?= $f['sort'] === 'amount_low' ? ' selected' : '' ?>>Smallest amount</option>
            </select>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/donations')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'banknote',
            'title'   => $filtered ? 'No donations match these filters' : 'No donations recorded yet',
            'text'    => $filtered ? 'Try a different search or clear the filters.' : 'Pledges made on the Donate page appear here. Use "Record a donation" to add gifts that arrived by bank, mobile money or cash.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/donations'), 'class' => 'btn--outline']] : [['label' => 'Record a donation', 'url' => url('admin/donations/create'), 'class' => 'btn--primary']],
        ]) ?>
    <?php else: ?>
        <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        <div class="table-wrap">
            <table class="table table--stack" id="donations-table">
                <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Donor</th>
                    <th scope="col" class="col-num">Amount</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $d): $id = (int) $d['id']; $when = ($d['status'] === 'received' && !empty($d['received_at'])) ? $d['received_at'] : $d['created_at']; ?>
                    <tr class="<?= $d['status'] === 'pledged' ? 'is-unread' : '' ?>">
                        <td data-label="Date" class="nowrap"><?= e(format_date($when, 'j M Y')) ?></td>
                        <td data-label="Donor">
                            <span class="cell-title"><a href="<?= e(url('admin/donations/' . $id . '/edit')) ?>"><?= e(trim((string) $d['donor_name']) !== '' ? $d['donor_name'] : 'No name given') ?></a></span>
                            <?php if (!empty($d['donor_email'])): ?><span class="cell-sub"><?= e($d['donor_email']) ?></span><?php endif; ?>
                            <?php if (!empty($d['is_anonymous']) || $d['source'] === 'pledge_form'): ?>
                                <span class="inbox-tags">
                                    <?php if (!empty($d['is_anonymous'])): ?><span class="badge badge--outline">Anonymous publicly</span><?php endif; ?>
                                    <?php if ($d['source'] === 'pledge_form'): ?><span class="badge badge--outline">Pledge form</span><?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Amount" class="col-num nowrap"><strong><?= e(Support::money($d['amount'], (string) $d['currency'])) ?></strong>
                            <span class="cell-sub"><?= e($methods[$d['method']] ?? label_from((string) $d['method'])) ?><?= !empty($d['reference']) ? ' - ' . e($d['reference']) : '' ?></span></td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $d['status'], 'label' => $statusLabels[$d['status']] ?? null, 'tone' => $tones[$d['status']] ?? 'neutral']) ?>
                            <?php if (!empty($d['receipt_no'])): ?><span class="cell-sub nowrap"><?= e($d['receipt_no']) ?></span><?php endif; ?></td>
                        <td data-label="" class="col-actions">
                            <div class="table__actions">
                                <?php if ($d['status'] === 'pledged'): ?>
                                    <form method="post" action="<?= e(url('admin/donations/' . $id . '/status')) ?>" data-confirm="Mark this donation as received? A receipt number will be issued.">
                                        <?= csrf_field() ?><input type="hidden" name="status" value="received">
                                        <button class="btn btn--accent btn--sm" type="submit" title="Mark as received" aria-label="Mark donation #<?= $id ?> as received"><?= icon('check', 'icon--sm') ?><span class="inbox-btn-text">Mark received</span></button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($d['status'] === 'received' && !empty($d['receipt_no'])): ?>
                                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/donations/' . $id . '/receipt')) ?>" target="_blank" rel="noopener" title="Print receipt" aria-label="Print the receipt for donation #<?= $id ?>"><?= icon('file-text', 'icon--sm') ?><span class="inbox-btn-text">Receipt</span></a>
                                <?php endif; ?>
                                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/donations/' . $id . '/edit')) ?>" title="Edit" aria-label="Edit donation #<?= $id ?>"><?= icon('edit', 'icon--sm') ?><span class="inbox-btn-text">Edit</span></a>
                                <?php if ($canDelete): ?>
                                    <form method="post" action="<?= e(url('admin/donations/' . $id . '/delete')) ?>" data-confirm="Delete this donation permanently? This cannot be undone.">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Delete donation #<?= $id ?>"><?= icon('trash', 'icon--sm') ?><span class="inbox-btn-text">Delete</span></button>
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
