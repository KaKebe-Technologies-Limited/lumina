<?php
/**
 * Admin > Forms > the BUILDER page (single page, no reloads). Vars: $form (hydrated; the builder script loads the full state itself from the JSON API).
 * The script (assets/js/admin-form-builder.js) builds the whole interface inside #form-builder: palette, canvas, settings panel, live preview, form settings.
 * Contract: docs/FORMS.md section 16 (endpoints) and 17 (builder behaviour). Styles: scss/admin/_forms-builder.scss.
 *
 * Hooks the script reads from the root element:  data-form-id, data-api (base URL of the JSON API), data-list-url, data-public-url, data-submissions-url,
 * data-can-delete, data-can-export. The icon sprite below gives the script the same SVG icons the rest of the admin uses (the script cannot call icon()).
 */
$iconNames = ['plus', 'minus', 'x', 'check', 'check-circle', 'edit', 'trash', 'eye', 'search', 'arrow-up', 'arrow-down', 'arrow-left', 'chevron-down', 'chevron-up', 'chevron-right', 'copy',
    'save', 'refresh', 'more', 'grip', 'settings', 'alert-triangle', 'alert-circle', 'info', 'help-circle', 'link', 'external-link', 'send', 'lock', 'monitor', 'smartphone', 'file-text',
    'mail', 'phone', 'bar-chart', 'calendar', 'clock', 'circle', 'star', 'upload', 'shield', 'menu', 'users', 'inbox', 'download', 'layers', 'layout', 'code', 'list', 'globe', 'history',
    'briefcase', 'heart', 'message-circle', 'handshake', 'graduation-cap', 'hand-heart', 'user', 'user-plus', 'book-open', 'target', 'tag', 'file', 'folder', 'building', 'award'];
$custom = [
    'undo'       => '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>',
    'redo'       => '<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>',
    'tablet'     => '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
    'page-break' => '<path d="M5 8V4h14v4"/><path d="M5 16v4h14v-4"/><line x1="3" y1="12" x2="6" y2="12"/><line x1="10" y1="12" x2="14" y2="12"/><line x1="18" y1="12" x2="21" y2="12"/>',
    'expand'     => '<polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/>',
    'play'       => '<polygon points="6 4 20 12 6 20 6 4"/>',
];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-form-builder.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-forms admin-forms--builder">
    <div id="form-builder" class="form-builder"
         data-form-id="<?= (int) $form['id'] ?>"
         data-api="<?= e(url('admin/api/forms')) ?>"
         data-list-url="<?= e(url('admin/forms')) ?>"
         data-public-url="<?= e(abs_url('forms/' . $form['slug'])) ?>"
         data-submissions-url="<?= can('forms.submissions') ? e(url('admin/forms/' . (int) $form['id'] . '/submissions')) : '' ?>"
         data-can-delete="<?= can('forms.delete') ? '1' : '0' ?>"
         data-can-export="<?= can('forms.export') ? '1' : '0' ?>">
        <div hidden id="fb-icons" aria-hidden="true">
            <?php foreach ($iconNames as $n): ?><span data-icon="<?= e($n) ?>"><?= icon($n) ?></span><?php endforeach; ?>
            <?php foreach ($custom as $n => $inner): ?><span data-icon="<?= e($n) ?>"><svg class="icon icon--<?= e($n) ?>" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?= $inner ?></svg></span><?php endforeach; ?>
        </div>
        <noscript><p class="alert alert--warning">The form builder needs JavaScript. Please turn it on in your browser.</p></noscript>
        <div class="fb-loading" data-builder-loading>
            <p class="fb-loading__title"><?= e((string) $form['title']) ?></p>
            <p class="fb-loading__text" data-builder-status role="status" aria-live="polite">Opening the form builder...</p>
        </div>
    </div>
</div>
