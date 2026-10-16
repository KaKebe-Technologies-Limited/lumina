<?php
/**
 * Menus manager: one ordered list of every page; choose for each whether it appears in the header and/or footer, rename it and
 * place it under a dropdown. OWNER: B3. Vars: $items (SitePages rows + menu_hidden), in current menu order, sub-pages after their parent.
 * Header and footer share this order (pages.nav_order).
 */
$oldMenu = old('menu', []);
$oldMenu = is_array($oldMenu) ? $oldMenu : [];
$choices = [];
foreach ($items as $it) {
    $choices[$it['slug']] = $it['nav_label'];
}
?>
<div class="admin-pages-editor admin-menus">
<?= partial('partials/admin/page-header', [
    'title'    => 'Menus',
    'subtitle' => 'Decide which pages appear in the header and the footer, in what order, and under which dropdown. The website updates the moment you save.',
]) ?>
<?php if (error('_form')): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e(error('_form')) ?></div></div><?php endif; ?>

<form method="post" action="<?= e(url('admin/menus')) ?>" data-unsaved-guard data-menus-form novalidate>
    <?= csrf_field() ?>
    <div class="pe-layout">
        <div class="pe-menus__list">
            <p class="pe-menus__hint" id="menus-help">Drag the handle, or use the arrow buttons, to change the order. Pages with "Header" switched on appear in the top menu; "Footer" adds them to the footer links.</p>
            <div class="reorder-list" data-reorder aria-describedby="menus-help" data-menu-list>
                <?php foreach ($items as $it):
                    $slug = $it['slug'];
                    $om = isset($oldMenu[$slug]) && is_array($oldMenu[$slug]) ? $oldMenu[$slug] : null;
                    $curLabel = $om !== null ? (string) ($om['label'] ?? $it['nav_label']) : $it['nav_label'];
                    $curHeader = $om !== null ? (string) ($om['header'] ?? '0') === '1' : $it['show_in_nav'];
                    $curFooter = $om !== null ? (string) ($om['footer'] ?? '0') === '1' : $it['show_in_footer'];
                    $curParent = $om !== null ? (string) ($om['parent'] ?? '') : (string) $it['parent_slug'];
                    $sub = $curParent !== '';
                    $labelErr = error('menu[' . $slug . '][label]');
                    $parentErr = error('menu[' . $slug . '][parent]');
                    ?>
                    <div class="reorder-item pe-menu-item<?= $sub ? ' is-child' : '' ?><?= !$it['is_published'] ? ' is-unpublished' : '' ?>" data-reorder-item data-slug="<?= e($slug) ?>">
                        <span class="reorder-item__handle" data-drag-handle title="Drag to change the order" aria-hidden="true"><?= icon('grip') ?></span>
                        <input type="hidden" name="order[]" value="<?= e($slug) ?>">
                        <div class="reorder-item__body">
                            <div class="pe-menu-item__top">
                                <div class="field<?= $labelErr ? ' field--error' : '' ?>">
                                    <label class="visually-hidden" for="m-<?= e($slug) ?>-label">Menu label for <?= e($it['title']) ?></label>
                                    <input class="input" type="text" id="m-<?= e($slug) ?>-label" name="menu[<?= e($slug) ?>][label]" maxlength="80" value="<?= e($curLabel) ?>" data-menu-label>
                                    <?php if ($labelErr): ?><div class="field__error" role="alert"><?= e($labelErr) ?></div><?php endif; ?>
                                </div>
                                <span class="pe-menu-item__meta">
                                    <a href="<?= e(url('admin/pages/' . $slug . '/edit')) ?>" title="Edit this page"><?= e($it['route'] === '/' ? 'Home page' : $it['route']) ?></a>
                                    <?php if (!$it['is_published']): ?><span class="badge badge--warning">Unpublished</span><?php endif; ?>
                                    <?php if ($it['menu_hidden']): ?><span class="badge badge--neutral" title="This entry stays out of the menu until its section has content (for example a first team member).">Waits for content</span><?php endif; ?>
                                    <?php if (!empty($it['button'])): ?><span class="badge badge--orange">Button</span><?php endif; ?>
                                    <?php if (!$it['registry']): ?><span class="badge badge--info">Custom</span><?php endif; ?>
                                </span>
                            </div>
                            <div class="pe-menu-item__controls">
                                <input type="hidden" name="menu[<?= e($slug) ?>][header]" value="0">
                                <label class="switch"><input type="checkbox" name="menu[<?= e($slug) ?>][header]" value="1" data-menu-header<?= $curHeader ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Header</span></label>
                                <input type="hidden" name="menu[<?= e($slug) ?>][footer]" value="0">
                                <label class="switch"><input type="checkbox" name="menu[<?= e($slug) ?>][footer]" value="1" data-menu-footer<?= $curFooter ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Footer</span></label>
                                <div class="pe-menu-item__parent<?= $parentErr ? ' field--error' : '' ?>">
                                    <label for="m-<?= e($slug) ?>-parent">Dropdown under</label>
                                    <select class="select" id="m-<?= e($slug) ?>-parent" name="menu[<?= e($slug) ?>][parent]" data-menu-parent>
                                        <option value="">Nothing (top level)</option>
                                        <?php foreach ($choices as $cs => $cl): if ($cs === $slug) { continue; } ?>
                                            <option value="<?= e($cs) ?>"<?= $curParent === (string) $cs ? ' selected' : '' ?>><?= e($cl) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($parentErr): ?><div class="field__error" role="alert"><?= e($parentErr) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <span class="reorder-item__tools">
                            <button class="repeater__tool" type="button" data-move-up aria-label="Move <?= e($it['title']) ?> up"><?= icon('arrow-up', 'icon--sm') ?></button>
                            <button class="repeater__tool" type="button" data-move-down aria-label="Move <?= e($it['title']) ?> down"><?= icon('arrow-down', 'icon--sm') ?></button>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="pe-menus__preview" aria-label="Menu preview">
            <div class="card">
                <div class="card__header"><h2 class="card__title">Preview</h2></div>
                <div class="card__body">
                    <h3 class="pe-menus__previewtitle">Header</h3>
                    <ul class="pe-tree" data-menu-preview-header></ul>
                    <h3 class="pe-menus__previewtitle">Footer</h3>
                    <ul class="pe-tree pe-tree--flat" data-menu-preview-footer></ul>
                    <p class="field__help">A simplified preview. Entries for unpublished pages, or pages still waiting for content, are skipped automatically on the website.</p>
                </div>
            </div>
        </aside>
    </div>
    <div class="form-actions pe-actions">
        <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">No unsaved changes</span>
        <span class="pe-actions__buttons"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save menus</button></span>
    </div>
</form>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
