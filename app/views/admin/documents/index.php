<?php
/**
 * Admin > Documents list. OWNER: B8.
 * Vars: $pager, $q, $sort, $dir, $category, $year, $visibility, $status, $filtered, $total, $years, $categories.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Documents',
        'subtitle' => 'Reports and downloads for the public Reports & Downloads page. Files are stored privately and only served while a document is active and public.',
        'actions'  => [['label' => 'Upload document', 'url' => url('admin/documents/create'), 'icon' => 'upload']],
    ]) ?>

    <?php if ($total === 0): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'No documents yet',
            'text'    => 'Upload annual reports, financial statements, policies, briefs or newsletters (PDF, Word, Excel, PowerPoint or ZIP, up to 20 MB). Until at least one active, public document exists, the public Reports & Downloads page and its menu entry stay hidden.',
            'icon'    => 'file-text',
            'actions' => [['label' => 'Upload the first document', 'url' => url('admin/documents/create')]],
        ]) ?>
    <?php else: ?>
        <form class="filters" method="get" action="<?= e(url('admin/documents')) ?>" role="search" aria-label="Filter documents">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Title, description or file name']) ?>
            <div class="field">
                <label class="field__label" for="category">Category</label>
                <select class="select" id="category" name="category" data-autosubmit>
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cv => $cl): ?><option value="<?= e($cv) ?>"<?= $category === $cv ? ' selected' : '' ?>><?= e($cl) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="year">Year</label>
                <select class="select" id="year" name="year" data-autosubmit>
                    <option value="">Any year</option>
                    <?php foreach ($years as $y): ?><option value="<?= (int) $y ?>"<?= (string) $year === (string) $y ? ' selected' : '' ?>><?= (int) $y ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="visibility">Visibility</label>
                <select class="select" id="visibility" name="visibility" data-autosubmit>
                    <option value="">Public and private</option>
                    <option value="public"<?= $visibility === 'public' ? ' selected' : '' ?>>Public</option>
                    <option value="private"<?= $visibility === 'private' ? ' selected' : '' ?>>Private</option>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="status">Status</label>
                <select class="select" id="status" name="status" data-autosubmit>
                    <option value="">Any status</option>
                    <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
                    <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered, 'resetUrl' => url('admin/documents')]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No documents match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url('admin/documents'), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [
                ['value' => 'activate', 'label' => 'Activate'],
                ['value' => 'deactivate', 'label' => 'Deactivate'],
                ['value' => 'public', 'label' => 'Make public'],
                ['value' => 'private', 'label' => 'Make private (not downloadable)'],
            ];
            if (can('content.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected document(s) and their files? This cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url('admin/documents/bulk'), 'options' => $bulk, 'noun' => 'documents']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="documents-table">
                    <caption class="visually-hidden">Documents</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all documents on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('title')) ?>"><?= partial('admin/org/_sort', ['label' => 'Document', 'col' => 'title', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('category')) ?>"><?= partial('admin/org/_sort', ['label' => 'Category', 'col' => 'category', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('year')) ?>"><?= partial('admin/org/_sort', ['label' => 'Year', 'col' => 'year', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Website', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-num" aria-sort="<?= e($aria('downloads')) ?>"><?= partial('admin/org/_sort', ['label' => 'Downloads', 'col' => 'downloads', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $d):
                            $isOn = (int) $d['is_active'] === 1;
                            $isPublic = (int) $d['is_public'] === 1;
                            $ext = strtoupper((string) pathinfo((string) ($d['file_name'] ?: $d['file_path']), PATHINFO_EXTENSION));
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $d['id'] ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($d['title']) ?>"></td>
                                <td data-label="Document">
                                    <div class="org-person">
                                        <span class="org-fileicon" aria-hidden="true"><?= icon('file-text') ?><b><?= e($ext) ?></b></span>
                                        <span class="org-person__text">
                                            <a class="cell-title" href="<?= e(url('admin/documents/' . (int) $d['id'] . '/edit')) ?>"><?= e($d['title']) ?></a>
                                            <span class="cell-sub"><?= e(implode(' · ', array_filter([(string) ($d['file_name'] ?? ''), human_filesize((int) $d['file_size'])]))) ?></span>
                                            <?php if (empty($d['file_ok'])): ?><span class="badge badge--danger">File missing on the server</span><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Category"><?= e($categories[$d['category']] ?? label_from($d['category'])) ?></td>
                                <td data-label="Year"><?= $d['year'] !== null ? (int) $d['year'] : '<span class="text-muted">&mdash;</span>' ?></td>
                                <td data-label="Website">
                                    <?php if (!$isOn): ?>
                                        <?= partial('partials/admin/status-badge', ['status' => 'inactive', 'label' => 'Inactive']) ?>
                                    <?php elseif ($isPublic): ?>
                                        <?= partial('partials/admin/status-badge', ['status' => 'active', 'label' => 'Live (public)']) ?>
                                    <?php else: ?>
                                        <?= partial('partials/admin/status-badge', ['status' => 'private', 'label' => 'Private', 'tone' => 'warning']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="col-num" data-label="Downloads"><?= number_format((int) $d['download_count']) ?></td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/documents/' . (int) $d['id'] . '/download')) ?>" title="Download <?= e($d['title']) ?> (does not count as a public download)"><?= icon('download', 'icon--sm') ?><span class="visually-hidden">Download <?= e($d['title']) ?></span></a>
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/documents/' . (int) $d['id'] . '/edit')) ?>" title="Edit <?= e($d['title']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Edit <?= e($d['title']) ?></span></a>
                                        <?= partial('admin/org/_row-action', [
                                            'action' => url('admin/documents/' . (int) $d['id'] . '/toggle'),
                                            'label'  => ($isOn ? 'Deactivate ' : 'Activate ') . $d['title'],
                                            'icon'   => $isOn ? 'eye-off' : 'eye',
                                        ]) ?>
                                        <?php if (can('content.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url('admin/documents/' . (int) $d['id'] . '/delete'),
                                                'label'   => 'Delete ' . $d['title'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete "' . $d['title'] . '" and its file? This cannot be undone.',
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
