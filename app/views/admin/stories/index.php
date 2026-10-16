<?php
/**
 * Admin > Impact stories list. OWNER: B8. Vars: $pager, $q, $sort, $dir, $state, $filtered, $counts (total/published/draft/no_consent).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$tabUrl = static function (string $s) use ($q, $pager): string {
    $query = array_filter(['state' => $s, 'q' => $q, 'per_page' => (int) $pager->perPage !== 20 ? (string) $pager->perPage : ''], static fn($v): bool => $v !== '');
    return url('admin/stories', $query);
};
$tabs = [
    ''           => ['All', (int) $counts['total']],
    'published'  => ['Published', (int) $counts['published']],
    'draft'      => ['Drafts', (int) $counts['draft']],
    'no_consent' => ['Awaiting consent', (int) $counts['no_consent']],
];
$now = time();
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Impact stories',
        'subtitle' => 'Stories of the people the foundation works with. A story is only ever published after consent has been confirmed.',
        'actions'  => [['label' => 'Add story', 'url' => url('admin/stories/create'), 'icon' => 'plus']],
    ]) ?>

    <?php if ($counts['total'] === 0): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'No stories yet',
            'text'    => 'Add a story about the people the foundation works with. Every story needs confirmed consent from the person (or their parent or guardian) before it can be published, and until at least one story is published the public Impact Stories page and its menu entry stay hidden.',
            'icon'    => 'quote',
            'actions' => [['label' => 'Add the first story', 'url' => url('admin/stories/create')]],
        ]) ?>
    <?php else: ?>
        <nav class="tabs" aria-label="Show stories">
            <?php foreach ($tabs as $tv => [$tl, $tn]): ?>
                <a class="tabs__item<?= $state === $tv ? ' is-active' : '' ?>" href="<?= e($tabUrl($tv)) ?>"<?= $state === $tv ? ' aria-current="page"' : '' ?>><?= e($tl) ?> (<?= $tn ?>)</a>
            <?php endforeach; ?>
        </nav>

        <form class="filters" method="get" action="<?= e(url('admin/stories')) ?>" role="search" aria-label="Filter stories">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Title, summary, person or place']) ?>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered || $state !== '', 'resetUrl' => url('admin/stories'), 'keep' => ['state' => $state]]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No stories match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url('admin/stories'), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [
                ['value' => 'publish', 'label' => 'Publish (only stories with consent)'],
                ['value' => 'unpublish', 'label' => 'Unpublish'],
            ];
            if (can('content.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected stories and their images? This cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url('admin/stories/bulk'), 'options' => $bulk, 'noun' => 'stories']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="stories-table">
                    <caption class="visually-hidden">Impact stories</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all stories on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('title')) ?>"><?= partial('admin/org/_sort', ['label' => 'Story', 'col' => 'title', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('person')) ?>"><?= partial('admin/org/_sort', ['label' => 'Person', 'col' => 'person', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col">Consent</th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Status', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('published')) ?>"><?= partial('admin/org/_sort', ['label' => 'Publish date', 'col' => 'published', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $s):
                            $sid = (int) $s['id'];
                            $isOn = (int) $s['is_active'] === 1;
                            $consent = (int) $s['consent_confirmed'] === 1;
                            $pubTs = !empty($s['published_at']) ? (int) strtotime((string) $s['published_at']) : 0;
                            $scheduled = $isOn && $consent && $pubTs > $now;
                            $img = img_url((string) ($s['image'] ?? ''));
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= $sid ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($s['title']) ?>"></td>
                                <td data-label="Story">
                                    <div class="org-person">
                                        <?php if ($img !== ''): ?>
                                            <img class="cell-thumb" src="<?= e($img) ?>" alt="" width="56" height="40" loading="lazy">
                                        <?php else: ?>
                                            <span class="cell-thumb cell-thumb--empty" aria-hidden="true"><?= icon('quote') ?></span>
                                        <?php endif; ?>
                                        <span class="org-person__text">
                                            <a class="cell-title" href="<?= e(url('admin/stories/' . $sid . '/edit')) ?>"><?= e($s['title']) ?></a>
                                            <?php if (!empty($s['location'])): ?><span class="cell-sub"><?= icon('map-pin', 'icon--sm') ?> <?= e($s['location']) ?></span><?php endif; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Person"><div class="org-stack">
                                    <?php if (!empty($s['person_name'])): ?>
                                        <?= e($s['person_name']) ?>
                                        <?php if (!empty($s['person_role'])): ?><span class="cell-sub"><?= e($s['person_role']) ?></span><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?></div></td>
                                <td data-label="Consent">
                                    <?= $consent
                                        ? '<span class="badge badge--success badge--dot">Confirmed</span>'
                                        : '<span class="badge badge--warning badge--dot">Not confirmed</span>' ?>
                                </td>
                                <td data-label="Status">
                                    <?php if ($scheduled): ?>
                                        <span class="badge badge--info badge--dot">Scheduled</span>
                                    <?php elseif ($isOn && $consent): ?>
                                        <span class="badge badge--success badge--dot">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge--neutral badge--dot">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Publish date"><?= $pubTs ? '<time datetime="' . e(date('c', $pubTs)) . '">' . e(date('j M Y', $pubTs)) . '</time>' : '<span class="text-muted">&mdash;</span>' ?></td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <?php if ($isOn && $consent): ?>
                                            <a class="btn btn--ghost btn--sm" href="<?= e(url('stories/' . $s['slug'])) ?>" target="_blank" rel="noopener" title="View <?= e($s['title']) ?> on the website"><?= icon('external-link', 'icon--sm') ?><span class="visually-hidden">View <?= e($s['title']) ?> on the website</span></a>
                                        <?php endif; ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/stories/' . $sid . '/edit')) ?>" title="Edit <?= e($s['title']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Edit <?= e($s['title']) ?></span></a>
                                        <?= partial('admin/org/_row-action', [
                                            'action'   => url('admin/stories/' . $sid . '/toggle'),
                                            'label'    => $isOn ? 'Unpublish ' . $s['title'] : ($consent ? 'Publish ' . $s['title'] : 'Cannot publish ' . $s['title'] . ' until consent is confirmed'),
                                            'icon'     => $isOn ? 'eye-off' : 'eye',
                                            'disabled' => !$isOn && !$consent,
                                        ]) ?>
                                        <?php if (can('content.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url('admin/stories/' . $sid . '/delete'),
                                                'label'   => 'Delete ' . $s['title'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete "' . $s['title'] . '" and its image? This cannot be undone.',
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
