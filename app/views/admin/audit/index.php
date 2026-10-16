<?php
/** Activity log (read-only). OWNER: B3. Vars: $pagination, $users, $entities, $q, $user, $entity, $from, $to, $exportQuery. */
$p = $pagination;
$tone = static function (string $action): string {
    return match (true) {
        str_contains($action, 'delete') => 'danger',
        str_contains($action, 'create') => 'success',
        str_contains($action, 'failed') => 'warning',
        str_starts_with($action, 'auth.') => 'neutral',
        default => 'info',
    };
};
?>
<div class="admin-pages-editor admin-audit">
<?= partial('partials/admin/page-header', [
    'title'    => 'Activity log',
    'subtitle' => 'Who changed what, and when. The log is read-only and cannot be edited or erased from this screen.',
    'actions'  => can('export.csv') ? [['label' => 'Download as CSV', 'url' => url('admin/audit-log/export') . ($exportQuery !== '' ? '?' . $exportQuery : ''), 'class' => 'btn--outline', 'icon' => 'download']] : [],
]) ?>

<form class="filters" method="get" action="<?= e(url('admin/audit-log')) ?>">
    <div class="field field--grow"><label class="field__label" for="a-q">Search</label><div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" type="search" id="a-q" name="q" value="<?= e($q) ?>" placeholder="Words in the description"></div></div>
    <div class="field"><label class="field__label" for="a-user">Person</label>
        <select class="select" id="a-user" name="user"><option value="">Everyone</option><?php foreach ($users as $u): ?><option value="<?= e($u['user_name']) ?>"<?= $user === $u['user_name'] ? ' selected' : '' ?>><?= e($u['user_name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="a-entity">About</label>
        <select class="select" id="a-entity" name="entity"><option value="">Anything</option><?php foreach ($entities as $en): ?><option value="<?= e($en) ?>"<?= $entity === $en ? ' selected' : '' ?>><?= e(label_from((string) $en)) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="a-from">From</label><input class="input" type="date" id="a-from" name="from" value="<?= e($from) ?>"></div>
    <div class="field"><label class="field__label" for="a-to">To</label><input class="input" type="date" id="a-to" name="to" value="<?= e($to) ?>"></div>
    <div class="filters__actions"><button class="btn btn--primary" type="submit">Filter</button><?php if ($q . $user . $entity . $from . $to !== ''): ?><a class="btn btn--ghost" href="<?= e(url('admin/audit-log')) ?>">Clear</a><?php endif; ?></div>
</form>

<p class="result-count"><?= $p->total === 0 ? 'No entries found.' : 'Showing ' . $p->from() . ' to ' . $p->to() . ' of ' . number_format($p->total) ?></p>

<?php if ($p->items === []): ?>
    <?= partial('partials/empty-state', ['title' => 'Nothing to show', 'text' => 'No activity matches these filters.', 'icon' => 'history']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--striped table--stack">
            <thead><tr><th scope="col">When</th><th scope="col">Who</th><th scope="col">What</th><th scope="col">IP address</th></tr></thead>
            <tbody>
            <?php foreach ($p->items as $r):
                $details = $r['details'] ? json_decode((string) $r['details'], true) : null;
                ?>
                <tr>
                    <td data-label="When" class="nowrap"><time datetime="<?= e($r['created_at']) ?>"><?= e(format_datetime($r['created_at'], 'j M Y, H:i')) ?></time></td>
                    <td data-label="Who"><?= e($r['user_name']) ?></td>
                    <td data-label="What">
                        <span class="badge badge--<?= e($tone((string) $r['action'])) ?>"><?= e($r['action']) ?></span>
                        <?= e($r['summary']) ?>
                        <?php if (is_array($details) && $details !== []): ?>
                            <details class="pe-details"><summary>Details</summary><pre><?= e(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre></details>
                        <?php endif; ?>
                    </td>
                    <td data-label="IP address" class="nowrap"><?= e($r['ip']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('partials/pagination', ['pagination' => $p]) ?>
<?php endif; ?>
</div>
