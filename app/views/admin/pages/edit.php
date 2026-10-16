<?php
/**
 * THE PAGES EDITOR for a built-in (registry) page. OWNER: B3.
 * Vars: $slug, $pg (SitePages row), $reg (registry definition), $row (pages table row), $sections (PageForm::sections), $links, $last, $stamp,
 *       $parents, $hasChildren, $canMenus, $canReset, $editorJs.
 * Form contract: docs/ARCHITECTURE.md 8.7 (blocks[...], visible[...], reset[...] + page fields). Failed saves repopulate from old().
 */
use App\Controllers\Admin\Support\PageForm;

$siteName = (string) setting('site_name', 'Lumina Africa Foundation');
$oldBlocks = old('blocks', null);
$oldBlocks = is_array($oldBlocks) ? $oldBlocks : null;
$oldVisible = old('visible', []);
$oldVisible = is_array($oldVisible) ? $oldVisible : [];
$virtual = (bool) $pg['virtual'];
$isHome = $slug === 'home';
$flag = static fn(string $key, bool $default): bool => (string) old($key, $default ? '1' : '0') === '1';
$published = $flag('is_published', (bool) $pg['is_published']);
$totalEdits = array_sum(array_column($sections, 'edited'));
$many = count($sections) > 5;
$viewUrl = $virtual ? '' : (string) $pg['url'];
$formError = error('_form');
$count = count($sections);
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title'    => $pg['title'],
    'subtitle' => $reg['help'] !== '' ? $reg['help'] : ($virtual ? 'Text that appears on every page of the website.' : 'Edit the words, pictures and buttons on this page.'),
    'actions'  => array_values(array_filter([
        $virtual ? null : ['label' => 'View page', 'url' => $viewUrl, 'class' => 'btn--outline', 'icon' => 'external-link', 'target' => '_blank'],
        ($canReset && $totalEdits > 0) ? ['label' => 'Reset page to original', 'url' => url('admin/pages/' . $slug . '/reset'), 'class' => 'btn--ghost', 'icon' => 'refresh', 'method' => 'post',
            'confirm' => 'Remove ALL edits made to the content of "' . $pg['title'] . '" and show the original wording again? Title, search-engine text and menu settings are kept. This cannot be undone.'] : null,
    ])),
]) ?>

<?php if ($formError): ?>
    <div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e($formError) ?></div></div>
<?php endif; ?>

<form method="post" action="<?= e(url('admin/pages/' . $slug)) ?>" class="pe-form" data-unsaved-guard data-pe-form data-pe-slug="<?= e($slug) ?>" data-pe-many="<?= $many ? '1' : '0' ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="_loaded" value="<?= (int) $stamp ?>">
    <datalist id="pe-links">
        <?php foreach ($links as $route => $label): ?><option value="<?= e($route) ?>"><?= e($label) ?></option><?php endforeach; ?>
    </datalist>

    <div class="pe-layout">
        <div class="pe-main">
            <div class="pe-toolbar" role="group" aria-label="Sections">
                <span class="pe-toolbar__text"><?= (int) $count ?> <?= $count === 1 ? 'section' : 'sections' ?> on this page<?= $totalEdits > 0 ? ' &middot; ' . (int) $totalEdits . ' edited' : '' ?></span>
                <span class="pe-toolbar__buttons">
                    <button class="btn btn--ghost btn--sm" type="button" data-pe-expand-all>Open all</button>
                    <button class="btn btn--ghost btn--sm" type="button" data-pe-collapse-all>Close all</button>
                </span>
            </div>

            <?php foreach ($sections as $s):
                $sk = $s['key'];
                $visible = $s['toggle'] ? (isset($oldVisible[$sk]) ? (string) $oldVisible[$sk] === '1' : (bool) $s['visible']) : true;
                $bodyId = 'sec-' . $sk . '-body';
                ?>
                <section class="card pe-section<?= $visible ? '' : ' is-section-hidden' ?>" id="sec-<?= e($sk) ?>" data-pe-section data-section="<?= e($sk) ?>">
                    <div class="card__header pe-section__head">
                        <h2 class="card__title"><button class="pe-section__toggle" type="button" data-pe-toggle aria-expanded="true" aria-controls="<?= e($bodyId) ?>"><?= icon('chevron-down', 'icon--sm') ?><span class="pe-section__label"><?= e($s['label']) ?></span></button></h2>
                        <span class="pe-section__badges">
                            <?php if ($s['edited'] > 0): ?><span class="pe-edited"><?= (int) $s['edited'] ?> edited</span><?php endif; ?>
                            <?php if ($s['toggle']): ?><span class="badge badge--neutral pe-hidden-badge"<?= $visible ? ' hidden' : '' ?> data-pe-hidden-badge>Hidden on the website</span><?php endif; ?>
                        </span>
                        <?php if ($s['toggle']): ?>
                            <span class="pe-section__switch">
                                <input type="hidden" name="visible[<?= e($sk) ?>]" value="0">
                                <label class="switch"><input type="checkbox" name="visible[<?= e($sk) ?>]" value="1" data-pe-visible data-default="<?= $s['visible_default'] ? '1' : '0' ?>"<?= $visible ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Show on the website</span></label>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card__body pe-section__body" id="<?= e($bodyId) ?>">
                        <?php if ($s['toggle']): ?><p class="pe-section__off" data-pe-off-note<?= $visible ? ' hidden' : '' ?>>This section is switched off, so visitors do not see it. You can still edit it; nothing is lost.</p><?php endif; ?>
                        <?php if ($s['help'] !== ''): ?><p class="pe-section__help"><?= e($s['help']) ?></p><?php endif; ?>
                        <div class="pe-fields">
                            <?php foreach ($s['fields'] as $fk => $entry): ?>
                                <?= partial('admin/pages/_field', ['fk' => $fk, 'entry' => $entry, 'oldBlocks' => $oldBlocks]) ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($s['edited'] > 0): ?>
                            <div class="pe-section__foot"><button class="btn btn--link btn--sm" type="button" data-pe-reset-section>Reset this whole section to the original wording</button></div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endforeach; ?>

            <?php if ($sections === []): ?>
                <?= partial('partials/empty-state', ['title' => 'Nothing to edit here yet', 'text' => 'This page has no editable sections.', 'icon' => 'file']) ?>
            <?php endif; ?>

            <?php if (!$virtual): ?>
                <?= partial('admin/pages/_seo', [
                    'seoTitle' => (string) old('seo_title', $pg['seo_title']), 'seoDesc' => (string) old('seo_description', $pg['seo_description']),
                    'ogImage'  => (string) old('og_image', $pg['og_image']),
                    'defTitle' => (string) ($reg['seo']['title'] ?? $pg['title']), 'defDesc' => (string) ($reg['seo']['description'] ?? '') !== '' ? (string) $reg['seo']['description'] : (string) setting('meta_description', ''),
                    'defOg'    => (string) ($reg['seo']['og_image'] ?? ''), 'pageUrl' => preg_replace('~^https?://~', '', abs_url(ltrim((string) $pg['route'], '/'))), 'siteName' => $siteName,
                ]) ?>
            <?php endif; ?>

            <?= partial('admin/pages/_menu_box', [
                'titleField' => true, 'title' => (string) old('title', $pg['title']),
                'navLabel' => (string) old('nav_label', (string) ($row['nav_label'] ?? '')),
                'defaultLabel' => (string) ($reg['nav']['label'] ?? $pg['title']),
                'showNav' => !$virtual && $flag('show_in_nav', (bool) $pg['show_in_nav']),
                'showFooter' => !$virtual && $flag('show_in_footer', (bool) $pg['show_in_footer']),
                'parentSlug' => (string) old('parent_slug', (string) ($pg['parent_slug'] ?? '')),
                'navOrder' => (string) old('nav_order', (string) $pg['nav_order']),
                'parents' => $parents, 'canMenus' => $canMenus && !$virtual, 'hasChildren' => $hasChildren, 'labelField' => !$virtual,
                'titleHelp' => $virtual ? 'The name of this entry in the admin.' : 'The name of this page in the admin lists and the default browser-tab title.',
            ]) ?>
        </div>

        <aside class="pe-aside" aria-label="Publishing and navigation">
            <div class="card pe-publish">
                <div class="card__header"><h2 class="card__title">Publish</h2></div>
                <div class="card__body">
                    <?php if (!$virtual): ?>
                        <?php if ($isHome): ?>
                            <p class="pe-publish__status"><?= partial('partials/admin/status-badge', ['status' => 'published', 'label' => 'Published']) ?></p>
                            <p class="field__help">The home page is always published.</p>
                        <?php else: ?>
                            <input type="hidden" name="is_published" value="0">
                            <label class="switch"><input type="checkbox" name="is_published" value="1" data-pe-published<?= $published ? ' checked' : '' ?>><span class="switch__track" aria-hidden="true"></span><span class="switch__text" data-pe-published-text><?= $published ? 'Published' : 'Not published' ?></span></label>
                            <p class="field__help" data-pe-published-help><?= $published ? 'Visible to everyone and listed in the menus you chose.' : 'Visitors see a "not found" page and the menu entry is hidden.' ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="field__help">This text is used on every page, so it is always on.</p>
                    <?php endif; ?>
                    <?php if ($last): ?>
                        <p class="pe-publish__last">Last edited <time datetime="<?= e($last['at']) ?>"><?= e(format_datetime($last['at'], 'j M Y, H:i')) ?></time><?= $last['by'] ? ' by ' . e($last['by']) : '' ?></p>
                    <?php else: ?>
                        <p class="pe-publish__last">Not edited yet: the original wording is showing.</p>
                    <?php endif; ?>
                </div>
                <div class="card__footer pe-publish__actions">
                    <button class="btn btn--primary btn--block" type="submit" data-pe-save><?= icon('save', 'icon--sm') ?> Save changes</button>
                    <?php if (!$virtual): ?><button class="btn btn--outline btn--block" type="submit" name="after" value="view">Save and view page</button><?php endif; ?>
                </div>
            </div>

            <nav class="card pe-jump" aria-label="Jump to a section">
                <div class="card__header"><h2 class="card__title">On this page</h2></div>
                <ol class="pe-jump__list">
                    <?php foreach ($sections as $s): ?>
                        <li><a href="#sec-<?= e($s['key']) ?>" data-pe-jump><?= e($s['label']) ?><?php if ($s['edited'] > 0): ?><span class="pe-jump__dot" title="Edited"></span><?php endif; ?></a></li>
                    <?php endforeach; ?>
                    <?php if (!$virtual): ?><li><a href="#sec-seo" data-pe-jump>Search and sharing</a></li><?php endif; ?>
                    <li><a href="#sec-menu" data-pe-jump><?= $virtual ? 'Name' : 'Page title and menu' ?></a></li>
                </ol>
            </nav>
        </aside>
    </div>

    <div class="form-actions pe-actions">
        <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">No unsaved changes</span>
        <span class="pe-actions__buttons">
            <a class="btn btn--ghost" href="<?= e(url('admin/pages')) ?>">Back to pages</a>
            <button class="btn btn--primary" type="submit" data-pe-save><?= icon('save', 'icon--sm') ?> Save changes</button>
        </span>
    </div>
    <input type="hidden" name="_end" value="1">
</form>
</div>
<?php $this->start('scripts'); ?>
<?php if (!empty($editorJs)): ?><script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script><?php endif; ?>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
<?php $this->end(); ?>
