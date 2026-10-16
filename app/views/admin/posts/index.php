<?php
/**
 * Posts list (blog / news / press). OWNER: B4. Vars from Content\CrudController::index() plus $typeLabels, $stateLabels.
 * The "tabs" are the post types (?type=), the status filter is ?state=draft|published|scheduled.
 */
$th = static fn(string $label, string $key, string $class = ''): string => partial('admin/_content/th-sort', [
    'label' => $label, 'key' => $key, 'sort' => $sort, 'dir' => $dir, 'defaultSort' => $defaultSort, 'listUrl' => $listUrl, 'class' => $class,
]);
$newUrl = url('admin/posts/create', isset($typeLabels[$typeNow]) ? ['type' => $typeNow] : []);
$stateTone = ['draft' => 'warning', 'published' => 'success', 'scheduled' => 'info'];
?>
<?= partial('partials/admin/page-header', [
    'title' => 'Posts',
    'subtitle' => 'Blog posts, news items and press coverage. Drafts and scheduled posts stay hidden until their publish date.',
    'actions' => [['label' => 'New post', 'url' => $newUrl, 'icon' => 'plus']],
]) ?>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl, 'param' => $tabParam, 'label' => 'Filter by post type']) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search by title, author, category or text', 'perPage' => $perPage, 'hasFilters' => $hasFilters, 'total' => $total, 'keep' => $keep,
    'selects' => [
        ['name' => 'state', 'label' => 'Status', 'options' => $stateLabels, 'value' => $filters['state'] ?? '', 'empty' => 'Any status'],
    ],
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => $newUrl, 'icon' => 'file-text', 'createLabel' => 'Write the first post']) ?>
<?php else: ?>
    <div class="table-wrap">
        <table class="table table--stack">
            <caption class="visually-hidden">Posts</caption>
            <thead>
            <tr>
                <th scope="col" class="col-narrow"><span class="visually-hidden">Cover picture</span></th>
                <?= $th('Post', 'title') ?>
                <?= $th('Type', 'type') ?>
                <th scope="col">Status</th>
                <?= $th('Date', 'date') ?>
                <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $state = $r['status'] !== 'published' ? 'draft' : (!empty($r['is_scheduled']) ? 'scheduled' : 'published');
                $pub = $state === 'published' ? ($r['post_type'] === 'press' && !empty($r['external_url']) ? $r['external_url'] : url($r['post_type'] . '/' . $r['slug'])) : null;
            ?>
                <tr>
                    <td class="col-narrow" data-label="Cover">
                        <?php if (!empty($r['cover_image'])): ?><img class="cell-thumb" src="<?= e(img_url($r['cover_image'])) ?>" alt="" loading="lazy"><?php else: ?><span class="cell-thumb cell-thumb--empty" aria-hidden="true"><?= icon('image', 'icon--sm') ?></span><?php endif; ?>
                    </td>
                    <td class="cell-main" data-label="Post">
                        <span class="cell-title"><a href="<?= e(url('admin/posts/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['title']) ?></a>
                            <?php if (!empty($r['is_featured'])): ?><span class="badge badge--gold">Featured</span><?php endif; ?></span>
                        <span class="cell-sub"><?= e(trim(($r['category'] ? $r['category'] . ' · ' : '') . ($r['author_name'] ? 'by ' . $r['author_name'] : ''), ' ·') ?: '/' . $r['post_type'] . '/' . $r['slug']) ?></span>
                    </td>
                    <td data-label="Type"><span class="badge badge--outline"><?= e($typeLabels[$r['post_type']] ?? $r['post_type']) ?></span></td>
                    <td data-label="Status">
                        <div class="status-cell">
                            <?= partial('partials/admin/status-badge', ['status' => $state, 'label' => $stateLabels[$state], 'tone' => $stateTone[$state]]) ?>
                            <form method="post" action="<?= e(url('admin/posts/' . (int) $r['id'] . '/toggle')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return" value="<?= e($returnQs) ?>">
                                <button class="link-button" type="submit"><?= $state === 'draft' ? 'Publish now' : 'Unpublish' ?><span class="visually-hidden">: <?= e($r['title']) ?></span></button>
                            </form>
                        </div>
                    </td>
                    <td data-label="Date"><span class="nowrap"><?= $r['published_at'] ? e(format_date($r['published_at'], 'j M Y, H:i')) : '<span class="text-muted">Not set</span>' ?></span></td>
                    <td class="col-actions">
                        <?= partial('admin/_content/row-actions', [
                            'c' => $c, 'row' => $r, 'returnQs' => $returnQs, 'canReorder' => false, 'publicUrl' => $pub,
                            'viewLabel' => ($r['post_type'] === 'press' && !empty($r['external_url'])) ? 'Open original article' : 'View on website',
                            'extra' => [['label' => 'Preview', 'url' => url('admin/posts/' . (int) $r['id'] . '/preview'), 'icon' => 'eye', 'blank' => true]],
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => $c['path']]) ?>
<?php endif; ?>
