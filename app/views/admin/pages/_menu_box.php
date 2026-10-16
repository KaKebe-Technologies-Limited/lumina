<?php
/**
 * "Menu and page settings" card. OWNER: B3. Shared by registry pages (edit.php) and custom pages (custom_form.php).
 * Vars: $navLabel, $showNav, $showFooter, $parentSlug, $navOrder, $parents (slug => label), $canMenus, $hasChildren, $defaultLabel,
 *       $titleField (bool: also render the page title input - registry pages; custom pages have their own title field), $title,
 *       $titleHelp, $sectionId.
 * Editors may rename the menu label; showing/hiding in the menus, the dropdown parent and the position are administrator-only
 * (ability menus.manage) and are rendered read-only for editors.
 */
$sectionId = $sectionId ?? 'sec-menu';
?>
<section class="card pe-section" id="<?= e($sectionId) ?>" data-pe-section data-section="menu">
    <div class="card__header pe-section__head">
        <h2 class="card__title"><button class="pe-section__toggle" type="button" data-pe-toggle aria-expanded="true" aria-controls="<?= e($sectionId) ?>-body"><?= icon('chevron-down', 'icon--sm') ?><span class="pe-section__label"><?= !empty($titleField) ? (($labelField ?? true) ? 'Page title and menu' : 'Name') : 'Menu' ?></span></button></h2>
    </div>
    <div class="card__body pe-section__body" id="<?= e($sectionId) ?>-body">
        <div class="form-grid">
            <?php if (!empty($titleField)): ?>
                <div class="field field--full<?= has_error('title') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-title">Page name</label>
                    <input class="input" type="text" id="f-title" name="title" maxlength="190" required value="<?= e($title) ?>">
                    <div class="field__help"><?= e($titleHelp ?? 'The name of this page in the admin and the default browser-tab title.') ?></div>
                    <?php if (has_error('title')): ?><div class="field__error" role="alert"><?= e(error('title')) ?></div><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($labelField ?? true): ?>
            <div class="field<?= has_error('nav_label') ? ' field--error' : '' ?>">
                <label class="field__label" for="f-nav-label">Menu label</label>
                <input class="input" type="text" id="f-nav-label" name="nav_label" maxlength="80" value="<?= e($navLabel) ?>" placeholder="<?= e($defaultLabel) ?>">
                <div class="field__help">The word shown in the website menus.</div>
                <?php if (has_error('nav_label')): ?><div class="field__error" role="alert"><?= e(error('nav_label')) ?></div><?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ($canMenus): ?>
                <div class="field<?= has_error('parent_slug') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-parent">Place under (dropdown)</label>
                    <select class="select" id="f-parent" name="parent_slug"<?= !empty($hasChildren) ? ' disabled aria-describedby="f-parent-help"' : '' ?>>
                        <option value="">Top level of the menu</option>
                        <?php foreach ($parents as $ps => $pl): ?>
                            <option value="<?= e($ps) ?>"<?= (string) $parentSlug === (string) $ps ? ' selected' : '' ?>><?= e($pl) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($hasChildren)): ?><input type="hidden" name="parent_slug" value="">
                        <div class="field__help" id="f-parent-help">This page has sub-pages of its own, so it stays at the top level.</div>
                    <?php else: ?><div class="field__help">Pick a page to make this one appear in that page's dropdown.</div><?php endif; ?>
                    <?php if (has_error('parent_slug')): ?><div class="field__error" role="alert"><?= e(error('parent_slug')) ?></div><?php endif; ?>
                </div>
                <div class="field field--full pe-switches">
                    <input type="hidden" name="show_in_nav" value="0">
                    <label class="switch"><input type="checkbox" name="show_in_nav" value="1"<?= $showNav ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Show in the top menu (header)</span></label>
                    <input type="hidden" name="show_in_footer" value="0">
                    <label class="switch"><input type="checkbox" name="show_in_footer" value="1"<?= $showFooter ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Show in the footer links</span></label>
                </div>
                <div class="field<?= has_error('nav_order') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-nav-order">Position in the menus</label>
                    <input class="input" type="number" id="f-nav-order" name="nav_order" min="0" max="9999" step="1" value="<?= e($navOrder) ?>">
                    <div class="field__help">Lower numbers come first. For drag-and-drop ordering of everything at once, use <a href="<?= e(url('admin/menus')) ?>">Menus</a>.</div>
                    <?php if (has_error('nav_order')): ?><div class="field__error" role="alert"><?= e(error('nav_order')) ?></div><?php endif; ?>
                </div>
            <?php elseif ($labelField ?? true): ?>
                <div class="field">
                    <span class="field__label">Where it appears</span>
                    <p class="pe-readonly"><?= $showNav ? 'Top menu' : 'Not in the top menu' ?> &middot; <?= $showFooter ? 'Footer links' : 'Not in the footer' ?></p>
                    <div class="field__help">Only administrators can change where a page appears in the menus.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
