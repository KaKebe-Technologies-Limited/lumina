<?php
/**
 * Create / edit a CUSTOM page (served at /{slug}). OWNER: B3.
 * Vars: $mode ('create'|'edit'), $row (pages row or defaults), $templates, $parents, $hasChildren, $canMenus, $canDelete, $links, $editorJs.
 */
$creating = $mode === 'create';
$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$flag = static fn(string $key, bool $default): bool => (string) old($key, $default ? '1' : '0') === '1';
$val = static fn(string $key): string => (string) old($key, (string) ($row[$key] ?? ''));
$published = $flag('is_published', (bool) ($row['is_published'] ?? false));
$slug = (string) ($row['slug'] ?? '');
$action = $creating ? url('admin/pages') : url('admin/pages/' . $slug);
$formError = error('_form');
$publicUrl = $creating ? '' : url($slug);
$template = $val('template') !== '' ? $val('template') : 'default';
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title'    => $creating ? 'New page' : (string) $row['title'],
    'subtitle' => $creating ? 'Write a page, choose how it looks, and decide whether it appears in the menus.' : 'Custom page · /' . $slug,
    'actions'  => array_values(array_filter([
        (!$creating && $published) ? ['label' => 'View page', 'url' => $publicUrl, 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'] : null,
        (!$creating && $canDelete) ? ['label' => 'Delete page', 'url' => url('admin/pages/' . $slug . '/delete'), 'class' => 'btn--ghost pe-danger', 'icon' => 'trash', 'method' => 'post',
            'confirm' => 'Delete the page "' . $row['title'] . '" permanently? This cannot be undone.'] : null,
    ])),
]) ?>
<?php if ($formError): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e($formError) ?></div></div><?php endif; ?>

<form method="post" action="<?= e($action) ?>" class="pe-form" data-unsaved-guard data-pe-form data-pe-slug="<?= e($slug !== '' ? $slug : '_new') ?>" novalidate>
    <?= csrf_field() ?>
    <datalist id="pe-links">
        <?php foreach ($links as $route => $label): ?><option value="<?= e($route) ?>"><?= e($label) ?></option><?php endforeach; ?>
    </datalist>
    <div class="pe-layout">
        <div class="pe-main">
            <section class="card pe-section" id="sec-content" data-pe-section data-section="content">
                <div class="card__header pe-section__head">
                    <h2 class="card__title"><button class="pe-section__toggle" type="button" data-pe-toggle aria-expanded="true" aria-controls="sec-content-body"><?= icon('chevron-down', 'icon--sm') ?><span class="pe-section__label">Page content</span></button></h2>
                </div>
                <div class="card__body pe-section__body" id="sec-content-body">
                    <div class="pe-fields">
                        <div class="field pe-field--wide<?= has_error('title') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-title">Title <span class="field__req" aria-hidden="true">*</span></label>
                            <input class="input" type="text" id="f-title" name="title" maxlength="190" required value="<?= e($val('title')) ?>"<?= $creating ? ' autofocus' : '' ?>>
                            <div class="field__help">Shown as the big heading at the top of the page.</div>
                            <?php if (has_error('title')): ?><div class="field__error" role="alert"><?= e(error('title')) ?></div><?php endif; ?>
                        </div>
                        <div class="field pe-field--wide<?= has_error('slug') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-slug">Web address</label>
                            <div class="input-group pe-slug">
                                <span class="pe-slug__prefix" aria-hidden="true"><?= e(preg_replace('~^https?://~', '', abs_url(''))) ?></span>
                                <input class="input" type="text" id="f-slug" name="slug" maxlength="120" value="<?= e($val('slug')) ?>" placeholder="<?= $creating ? 'filled in from the title' : '' ?>" autocomplete="off" spellcheck="false" data-slug-source="#f-title" aria-describedby="f-slug-help">
                            </div>
                            <div class="field__help" id="f-slug-help"><?= $creating ? 'Made from the title; you can change it. Lowercase letters, numbers and hyphens only.' : 'Changing the address breaks any link or bookmark that uses the old one.' ?></div>
                            <?php if (has_error('slug')): ?><div class="field__error" role="alert"><?= e(error('slug')) ?></div><?php endif; ?>
                        </div>
                        <div class="field pe-field--wide<?= has_error('subtitle') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-subtitle">Introduction line (optional)</label>
                            <input class="input" type="text" id="f-subtitle" name="subtitle" maxlength="255" value="<?= e($val('subtitle')) ?>">
                            <div class="field__help">A short sentence shown under the title.</div>
                            <?php if (has_error('subtitle')): ?><div class="field__error" role="alert"><?= e(error('subtitle')) ?></div><?php endif; ?>
                        </div>
                        <div class="pe-field--wide">
                            <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Page text', 'value' => $val('body'), 'rows' => 16, 'help' => 'Headings, paragraphs, lists, links, quotes, tables and pictures are supported.', 'id' => 'f-body']) ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card pe-section" id="sec-look" data-pe-section data-section="look">
                <div class="card__header pe-section__head">
                    <h2 class="card__title"><button class="pe-section__toggle" type="button" data-pe-toggle aria-expanded="true" aria-controls="sec-look-body"><?= icon('chevron-down', 'icon--sm') ?><span class="pe-section__label">Layout and picture</span></button></h2>
                </div>
                <div class="card__body pe-section__body" id="sec-look-body">
                    <div class="pe-fields">
                        <div class="field pe-field--wide<?= has_error('template') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-template">Layout</label>
                            <select class="select" id="f-template" name="template">
                                <?php foreach ($templates as $tv => $tl): ?><option value="<?= e($tv) ?>"<?= $template === $tv ? ' selected' : '' ?>><?= e($tl) ?></option><?php endforeach; ?>
                            </select>
                            <?php if (has_error('template')): ?><div class="field__error" role="alert"><?= e(error('template')) ?></div><?php endif; ?>
                        </div>
                        <div class="field pe-field--wide<?= has_error('hero_image') ? ' field--error' : '' ?>">
                            <span class="field__label" id="f-hero-label">Top picture (optional)</span>
                            <?= partial('admin/pages/_image', ['name' => 'hero_image', 'altName' => 'hero_image_alt', 'value' => $val('hero_image'), 'alt' => $val('hero_image_alt'), 'id' => 'f-hero', 'label' => 'Top picture']) ?>
                            <?php if (has_error('hero_image')): ?><div class="field__error" role="alert"><?= e(error('hero_image')) ?></div><?php endif; ?>
                            <?php if (has_error('hero_image_alt')): ?><div class="field__error" role="alert"><?= e(error('hero_image_alt')) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <?= partial('admin/pages/_seo', [
                'seoTitle' => $val('seo_title'), 'seoDesc' => $val('seo_description'), 'ogImage' => $val('og_image'),
                'defTitle' => $val('title') !== '' ? $val('title') : 'The page title', 'defDesc' => (string) setting('meta_description', ''),
                'defOg' => (string) setting('og_image', ''), 'pageUrl' => preg_replace('~^https?://~', '', abs_url($slug)), 'siteName' => $siteName,
            ]) ?>

            <?= partial('admin/pages/_menu_box', [
                'titleField' => false, 'navLabel' => $val('nav_label'), 'defaultLabel' => $val('title') !== '' ? $val('title') : 'The page title',
                'showNav' => $flag('show_in_nav', (bool) ($row['show_in_nav'] ?? false)), 'showFooter' => $flag('show_in_footer', (bool) ($row['show_in_footer'] ?? false)),
                'parentSlug' => (string) old('parent_slug', (string) ($row['parent_slug'] ?? '')), 'navOrder' => (string) old('nav_order', (string) ($row['nav_order'] ?? 500)),
                'parents' => $parents, 'canMenus' => $canMenus, 'hasChildren' => $hasChildren,
            ]) ?>
        </div>

        <aside class="pe-aside" aria-label="Publishing">
            <div class="card pe-publish">
                <div class="card__header"><h2 class="card__title">Publish</h2></div>
                <div class="card__body">
                    <input type="hidden" name="is_published" value="0">
                    <label class="switch"><input type="checkbox" name="is_published" value="1" data-pe-published<?= $published ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text" data-pe-published-text><?= $published ? 'Published' : 'Draft' ?></span></label>
                    <p class="field__help" data-pe-published-help><?= $published ? 'Visible to everyone at the address above.' : 'Only you can see it in this admin area. Visitors get a "not found" page.' ?></p>
                    <?php if (!$creating): ?>
                        <p class="pe-publish__last">Last saved <time datetime="<?= e((string) $row['updated_at']) ?>"><?= e(format_datetime($row['updated_at'], 'j M Y, H:i')) ?></time></p>
                    <?php endif; ?>
                </div>
                <div class="card__footer pe-publish__actions">
                    <button class="btn btn--primary btn--block" type="submit" data-pe-save><?= icon('save', 'icon--sm') ?> <?= $creating ? 'Create page' : 'Save changes' ?></button>
                    <?php if (!$creating): ?><button class="btn btn--outline btn--block" type="submit" name="after" value="view">Save and view page</button><?php endif; ?>
                </div>
            </div>
            <nav class="card pe-jump" aria-label="Jump to a section">
                <div class="card__header"><h2 class="card__title">On this page</h2></div>
                <ol class="pe-jump__list">
                    <li><a href="#sec-content" data-pe-jump>Page content</a></li>
                    <li><a href="#sec-look" data-pe-jump>Layout and picture</a></li>
                    <li><a href="#sec-seo" data-pe-jump>Search and sharing</a></li>
                    <li><a href="#sec-menu" data-pe-jump>Menu</a></li>
                </ol>
            </nav>
        </aside>
    </div>
    <div class="form-actions pe-actions">
        <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">No unsaved changes</span>
        <span class="pe-actions__buttons">
            <a class="btn btn--ghost" href="<?= e(url('admin/pages')) ?>">Back to pages</a>
            <button class="btn btn--primary" type="submit" data-pe-save><?= icon('save', 'icon--sm') ?> <?= $creating ? 'Create page' : 'Save changes' ?></button>
        </span>
    </div>
</form>
</div>
<?php $this->start('scripts'); ?>
<?php if (!empty($editorJs)): ?><script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script><?php endif; ?>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
<?php $this->end(); ?>
