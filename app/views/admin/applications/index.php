<?php
/**
 * Admin > Job applications list. Vars: $pagination, $f, $tabs, $jobs, $labels, $canDelete, $canExport.
 */
$rows = $pagination->items;
$filtered = $f['q'] !== '' || $f['job'] !== '' || $f['from'] !== '' || $f['to'] !== '' || $f['status'] !== 'all';
$keep = ['q' => $f['q'], 'job' => $f['job'], 'from' => $f['from'], 'to' => $f['to'], 'sort' => $f['sort'] === 'newest' ? '' : $f['sort']];
$actions = ['mark_new' => 'Mark as new', 'mark_reviewing' => 'Mark as reviewing', 'mark_shortlisted' => 'Mark as shortlisted', 'mark_rejected' => 'Mark as rejected', 'mark_hired' => 'Mark as hired'];
if ($canDelete) {
    $actions['delete'] = 'Delete permanently (with CV)';
}
$headerActions = [];
if ($canExport) {
    $headerActions[] = ['label' => 'Export CSV', 'url' => url('admin/applications/export', $keep + ['status' => $f['status'] === 'all' ? '' : $f['status']]), 'class' => 'btn--outline', 'icon' => 'download'];
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => 'Job applications', 'subtitle' => 'Applications sent through the Careers page. CVs are private: only signed-in staff can download them.', 'actions' => $headerActions]) ?>

    <?= partial('admin/inbox/_tabs', ['tabs' => $tabs, 'current' => $f['status'], 'base' => 'admin/applications', 'keep' => $keep]) ?>

    <form class="filters" method="get" action="<?= e(url('admin/applications')) ?>" role="search">
        <?php if ($f['status'] !== 'all'): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
        <div class="field field--grow">
            <label class="field__label" for="f-q">Search</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, email, job or cover letter" maxlength="100"></div>
        </div>
        <div class="field"><label class="field__label" for="f-job">Job</label>
            <select class="select" id="f-job" name="job" data-autosubmit>
                <option value="">All jobs</option>
                <option value="0"<?= $f['job'] === '0' ? ' selected' : '' ?>>No job (general / removed)</option>
                <?php foreach ($jobs as $j): ?><option value="<?= (int) $j['id'] ?>"<?= $f['job'] === (string) $j['id'] ? ' selected' : '' ?>><?= e($j['title']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label class="field__label" for="f-from">Received from</label><input class="input" id="f-from" type="date" name="from" value="<?= e($f['from']) ?>"></div>
        <div class="field"><label class="field__label" for="f-to">Received until</label><input class="input" id="f-to" type="date" name="to" value="<?= e($f['to']) ?>"></div>
        <div class="field"><label class="field__label" for="f-sort">Order</label>
            <select class="select" id="f-sort" name="sort" data-autosubmit>
                <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>Newest first</option>
                <option value="oldest"<?= $f['sort'] === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
                <option value="name"<?= $f['sort'] === 'name' ? ' selected' : '' ?>>Name A to Z</option>
            </select>
        </div>
        <div class="filters__actions">
            <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
            <?php if ($filtered || $f['sort'] !== 'newest'): ?><a class="btn btn--ghost" href="<?= e(url('admin/applications')) ?>">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon'    => 'briefcase',
            'title'   => $filtered ? 'No applications match these filters' : 'No applications yet',
            'text'    => $filtered ? 'Try a different search or clear the filters.' : 'When someone applies for a job on the Careers page, the application appears here.',
            'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/applications'), 'class' => 'btn--outline']] : [],
        ]) ?>
    <?php else: ?>
        <div class="toolbar">
            <?= partial('admin/inbox/_bulk', ['formId' => 'bulk-applications', 'action' => url('admin/applications/bulk'), 'actions' => $actions, 'danger' => ['delete'], 'noun' => 'applications']) ?>
            <p class="result-count">Showing <?= (int) $pagination->from() ?>-<?= (int) $pagination->to() ?> of <?= (int) $pagination->total ?></p>
        </div>
        <div class="table-wrap">
            <table class="table table--stack" id="applications-table">
                <thead>
                <tr>
                    <th class="col-narrow"><input type="checkbox" data-select-all aria-label="Select all applications on this page"></th>
                    <th scope="col">Applicant</th>
                    <th scope="col">Job</th>
                    <th scope="col">CV</th>
                    <th scope="col">Status</th>
                    <th scope="col">Received</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $a): $href = url('admin/applications/' . (int) $a['id']); $hasCv = trim((string) ($a['cv_path'] ?? '')) !== ''; ?>
                    <tr class="<?= $a['status'] === 'new' ? 'is-unread' : '' ?>">
                        <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $a['id'] ?>" form="bulk-applications" data-select-row aria-label="Select the application from <?= e($a['name']) ?>"></td>
                        <td data-label="Applicant">
                            <span class="cell-title"><a href="<?= e($href) ?>"><?= e($a['name']) ?></a></span>
                            <span class="cell-sub"><?= e($a['email']) ?></span>
                        </td>
                        <td data-label="Job"><?= e($a['job_title'] ?: '-') ?></td>
                        <td data-label="CV">
                            <?php if ($hasCv): ?>
                                <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/applications/' . (int) $a['id'] . '/cv')) ?>"><?= icon('download', 'icon--sm') ?> Download<?= !empty($a['cv_size']) ? ' <span class="cell-sub">' . e(human_filesize((int) $a['cv_size'])) . '</span>' : '' ?></a>
                            <?php else: ?><span class="text-muted">None</span><?php endif; ?>
                        </td>
                        <td data-label="Status"><?= partial('partials/admin/status-badge', ['status' => $a['status'], 'label' => $labels[$a['status']] ?? null]) ?></td>
                        <td data-label="Received" class="nowrap"><?= e(format_date($a['created_at'], 'j M Y')) ?></td>
                        <td data-label="" class="col-actions"><a class="btn btn--outline btn--sm" href="<?= e($href) ?>"><?= icon('eye', 'icon--sm') ?> Open</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
</div>
