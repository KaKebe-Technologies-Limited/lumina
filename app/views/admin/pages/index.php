<?php
/**
 * Pages list: every page of the website with status, menu placement and quick actions. OWNER: B3.
 * Vars: $registry (built-in pages), $custom (owner-created pages), $virtual (site-wide text entries).
 */
$canDelete = can('pages.delete');
// Pages whose people / files / items live in another admin screen: shortcut next to Edit.
$manageLinks = [
    'team' => ['admin/team', 'Manage team members'], 'partners' => ['admin/partners', 'Manage partners'], 'reports' => ['admin/documents', 'Manage reports'],
    'events' => ['admin/events', 'Manage events'], 'stories' => ['admin/stories', 'Manage impact stories'], 'news' => ['admin/posts?type=news', 'Manage news'],
    'gallery' => ['admin/gallery', 'Manage photos'], 'programs' => ['admin/programs', 'Manage programs'], 'projects' => ['admin/projects', 'Manage projects'],
    'blog' => ['admin/posts?type=blog', 'Manage posts'], 'activities' => ['admin/activities', 'Manage activities'], 'careers' => ['admin/jobs', 'Manage jobs'],
];
$row = static function (array $p, bool $isCustom) use ($canDelete, $manageLinks): void {
    $label = $p['title'];
    ?>
    <tr data-filter-item>
        <td data-label="Page" class="cell-title">
            <a href="<?= e(url('admin/pages/' . $p['slug'] . '/edit')) ?>"><?= e($label) ?></a>
            <span class="cell-sub"><?= $p['virtual'] ? 'Used on every page' : e($p['route'] === '/' ? '/ (home)' : $p['route']) ?>
                <?php if (!$isCustom && $p['edits'] > 0): ?> &middot; <span class="pe-edited" title="Last edited <?= e(format_datetime($p['edited_at'], 'j M Y')) ?>"><?= (int) $p['edits'] ?> <?= $p['edits'] === 1 ? 'edit' : 'edits' ?></span><?php endif; ?></span>
        </td>
        <td data-label="Status">
            <?php if ($p['virtual']): ?><span class="text-muted">Always on</span>
            <?php elseif ($p['is_published']): ?><?= partial('partials/admin/status-badge', ['status' => 'published', 'label' => 'Published']) ?>
            <?php else: ?><?= partial('partials/admin/status-badge', ['status' => $isCustom ? 'draft' : 'hidden', 'label' => $isCustom ? 'Draft' : 'Unpublished', 'tone' => 'warning']) ?><?php endif; ?>
        </td>
        <td data-label="In menus">
            <?php if ($p['virtual']): ?><span class="text-muted">-</span>
            <?php else: ?>
                <span class="pe-chips">
                    <?php if ($p['show_in_nav']): ?><span class="badge badge--outline">Header<?= $p['parent_slug'] ? ' (dropdown)' : '' ?></span><?php endif; ?>
                    <?php if ($p['show_in_footer']): ?><span class="badge badge--outline">Footer</span><?php endif; ?>
                    <?php if (!$p['show_in_nav'] && !$p['show_in_footer']): ?><span class="text-muted">Not in a menu</span><?php endif; ?>
                </span>
                <?php if ($p['menu_hidden'] && ($p['show_in_nav'] || $p['show_in_footer'])): ?><span class="cell-sub">Hidden until it has content</span><?php endif; ?>
            <?php endif; ?>
        </td>
        <td class="col-actions">
            <span class="table__actions">
                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/pages/' . $p['slug'] . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                <?php if (!$isCustom && isset($manageLinks[$p['slug']]) && can('content.manage')): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= e(url($manageLinks[$p['slug']][0])) ?>"><?= e($manageLinks[$p['slug']][1]) ?></a>
                <?php endif; ?>
                <?php if (!$p['virtual'] && $p['is_published']): ?>
                    <a class="btn btn--ghost btn--sm" href="<?= e($p['url']) ?>" target="_blank" rel="noopener" aria-label="View <?= e($label) ?> on the website" title="View on the website"><?= icon('external-link', 'icon--sm') ?></a>
                <?php endif; ?>
                <?php if (!$p['virtual'] && $p['slug'] !== 'home'): ?>
                    <form method="post" action="<?= e(url('admin/pages/' . $p['slug'] . '/publish')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="publish" value="<?= $p['is_published'] ? '0' : '1' ?>">
                        <button class="btn btn--ghost btn--sm" type="submit"<?= $p['is_published'] ? ' data-confirm="Unpublish &quot;' . e($label) . '&quot;? Visitors will see a &quot;not found&quot; page and it disappears from the menus."' : '' ?>><?= $p['is_published'] ? 'Unpublish' : 'Publish' ?></button>
                    </form>
                <?php endif; ?>
                <?php if ($isCustom): ?>
                    <form method="post" action="<?= e(url('admin/pages/' . $p['slug'] . '/duplicate')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Duplicate</button></form>
                    <?php if ($canDelete): ?>
                        <form method="post" action="<?= e(url('admin/pages/' . $p['slug'] . '/delete')) ?>" class="inline-form" data-confirm="Delete the page &quot;<?= e($label) ?>&quot; permanently? This cannot be undone.">
                            <?= csrf_field() ?><button class="btn btn--ghost btn--sm pe-danger" type="submit" aria-label="Delete <?= e($label) ?>"><?= icon('trash', 'icon--sm') ?></button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
        </td>
    </tr>
<?php };
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title'    => 'Pages',
    'subtitle' => 'Every page of the website. Open a page to change its words, pictures, buttons, search-engine text and menu entry, or create a brand-new page.',
    'actions'  => [['label' => 'New page', 'url' => url('admin/pages/create'), 'icon' => 'plus']],
]) ?>

<div class="filters">
    <div class="field field--grow">
        <label class="field__label" for="pages-filter">Find a page</label>
        <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" type="search" id="pages-filter" placeholder="Type a page name..." data-table-filter="#pages-tables" autocomplete="off"></div>
    </div>
</div>

<div id="pages-tables">
    <section class="pe-group" aria-labelledby="g-site">
        <h2 class="pe-group__title" id="g-site">Website pages <span class="pe-group__count"><?= count($registry) ?></span></h2>
        <div class="table-wrap">
            <table class="table table--stack">
                <thead><tr><th scope="col">Page</th><th scope="col">Status</th><th scope="col">In menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody><?php foreach ($registry as $p) { $row($p, false); } ?></tbody>
            </table>
        </div>
    </section>

    <section class="pe-group" aria-labelledby="g-custom">
        <h2 class="pe-group__title" id="g-custom">Your own pages <span class="pe-group__count"><?= count($custom) ?></span></h2>
        <?php if ($custom === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No custom pages yet',
                'text'    => 'Need a page that does not exist, such as "Safeguarding policy" or "Volunteer handbook"? Create it here with a simple editor; it can appear in the menus straight away.',
                'icon'    => 'file-text',
                'actions' => [['label' => 'Create a page', 'url' => url('admin/pages/create')]],
            ]) ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead><tr><th scope="col">Page</th><th scope="col">Status</th><th scope="col">In menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody><?php foreach ($custom as $p) { $row($p, true); } ?></tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($virtual !== []): ?>
        <section class="pe-group" aria-labelledby="g-global">
            <h2 class="pe-group__title" id="g-global">Text shared by every page</h2>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead><tr><th scope="col">Entry</th><th scope="col">Status</th><th scope="col">In menus</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody><?php foreach ($virtual as $p) { $row($p, false); } ?></tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
</div>
