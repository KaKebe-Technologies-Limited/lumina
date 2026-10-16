<?php
/**
 * Gallery manager: upload (drag and drop, progress), album filter, bulk actions, re-ordering. OWNER: B4.
 * Vars from Content\CrudController::index() plus $albums (name => count), $perPageOptions, $maxBytes.
 * JS: assets/js/admin-gallery.js (upload queue, drag/keyboard ordering, bulk selection).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-gallery.js')) ?>" defer></script>
<?php $this->end();
$albumOptions = ['__none' => 'No album'] + array_combine(array_keys($albums), array_map(static fn($n, $c) => $n . ' (' . $c . ')', array_keys($albums), $albums));
$maxMb = max(1, (int) round($maxBytes / 1048576));
?>
<?= partial('partials/admin/page-header', [
    'title' => 'Gallery',
    'subtitle' => 'Pictures for the Gallery page. Add a short description to every picture so it can be understood by people who use screen readers.',
    'actions' => [['label' => 'Upload pictures', 'url' => '#upload', 'icon' => 'upload']],
]) ?>

<section class="card gallery-upload-card" id="upload" aria-labelledby="up-title">
    <div class="card__header"><h2 class="card__title" id="up-title">Add pictures</h2></div>
    <div class="card__body">
        <form method="post" action="<?= e(url('admin/gallery/upload')) ?>" enctype="multipart/form-data" data-gallery-upload data-upload-url="<?= e(url('admin/gallery/upload')) ?>"
              data-quick-url="<?= e(url('admin/gallery/__ID__/quick')) ?>" data-max-bytes="<?= (int) $maxBytes ?>" data-list-url="<?= e($listUrl(['status' => 'noalt'])) ?>">
            <?= csrf_field() ?>
            <div class="gallery-upload">
                <div class="field gallery-upload__album">
                    <label class="field__label" for="up-album">Add to album (optional)</label>
                    <input class="input" type="text" id="up-album" name="album" maxlength="100" list="album-options" autocomplete="off" placeholder="For example: Digital skills training 2026">
                    <datalist id="album-options"><?php foreach (array_keys($albums) as $a): ?><option value="<?= e($a) ?>"><?php endforeach; ?></datalist>
                    <div class="field__help">Albums let visitors browse related pictures together.</div>
                </div>
                <div class="dropzone" data-dropzone>
                    <input class="dropzone__input" type="file" id="up-files" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp" data-dropzone-input>
                    <label class="dropzone__label" for="up-files">
                        <?= icon('upload', 'icon--xl') ?>
                        <span class="dropzone__title"><strong>Choose pictures</strong> or drag them here</span>
                        <span class="dropzone__hint">JPG, PNG, GIF or WebP, up to <?= (int) $maxMb ?> MB each. You can select many at once.</span>
                    </label>
                </div>
            </div>
            <div class="form-actions form-actions--plain" data-upload-fallback>
                <button class="btn btn--primary" type="submit"><?= icon('upload', 'icon--sm') ?> Upload the chosen pictures</button>
            </div>
            <ul class="upload-queue" data-upload-queue aria-label="Upload progress"></ul>
            <p class="upload-summary" data-upload-summary role="status" hidden></p>
        </form>
    </div>
</section>

<?= partial('admin/_content/tabs', ['tabs' => $tabs, 'listUrl' => $listUrl, 'param' => $tabParam]) ?>
<?= partial('admin/_content/filters', [
    'path' => $c['path'], 'q' => $q, 'placeholder' => 'Search descriptions, captions or albums', 'perPage' => $perPage, 'perPageOptions' => $perPageOptions, 'hasFilters' => $hasFilters,
    'total' => $total, 'keep' => $keep,
    'selects' => [['name' => 'album', 'label' => 'Album', 'options' => $albumOptions, 'value' => $filters['album'] ?? '', 'empty' => 'All albums']],
]) ?>

<?php if ($rows === []): ?>
    <?= partial('admin/_content/empty', ['c' => $c, 'hasFilters' => $hasFilters, 'createUrl' => '#upload', 'createLabel' => 'Upload pictures', 'icon' => 'image',
        'hint' => 'Pictures you upload are listed here and shown on the Gallery page.']) ?>
<?php else: ?>
    <form id="bulk-form" class="bulk-bar" method="post" action="<?= e(url('admin/gallery/bulk')) ?>" data-bulk-form>
        <?= csrf_field() ?>
        <input type="hidden" name="return" value="<?= e($returnQs) ?>">
        <label class="check bulk-bar__all"><input type="checkbox" data-select-all><span>Select all on this page</span></label>
        <span class="bulk-bar__count" data-bulk-count role="status" aria-live="polite">0 selected</span>
        <div class="bulk-bar__actions">
            <button class="btn btn--outline btn--sm" type="submit" name="action" value="show" data-bulk-btn disabled><?= icon('eye', 'icon--sm') ?> Show</button>
            <button class="btn btn--outline btn--sm" type="submit" name="action" value="hide" data-bulk-btn disabled><?= icon('eye-off', 'icon--sm') ?> Hide</button>
            <span class="bulk-bar__album">
                <label class="visually-hidden" for="bulk-album">Album for the selected pictures</label>
                <input class="input" type="text" id="bulk-album" name="album" maxlength="100" list="album-options" placeholder="Album name" autocomplete="off">
                <button class="btn btn--outline btn--sm" type="submit" name="action" value="album" data-bulk-btn disabled>Move to album</button>
            </span>
            <?php if (can('content.delete')): ?>
                <button class="btn btn--danger btn--sm" type="submit" name="action" value="delete" data-bulk-btn disabled data-confirm="Delete the selected pictures? This cannot be undone."><?= icon('trash', 'icon--sm') ?> Delete</button>
            <?php endif; ?>
        </div>
    </form>

    <p class="form-note" id="gallery-order-note">
        <?= $canReorder
            ? 'Drag a picture by its handle, or use the arrow buttons, to change the order. The order is saved straight away' . ($hasFilters || $pagination->hasPages() ? ' (it applies to the pictures shown here; the others keep their places).' : '.')
            : 'To change the order of the pictures, switch the sorting back to the default order first.' ?>
    </p>
    <p class="visually-hidden" role="status" aria-live="polite" data-order-status></p>

    <ul class="gallery-admin" data-gallery-grid data-reorder-url="<?= e(url('admin/gallery/reorder')) ?>" data-reorderable="<?= $canReorder ? '1' : '0' ?>" aria-describedby="gallery-order-note">
        <?php foreach ($rows as $r):
            $id = (int) $r['id'];
            $label = $r['caption'] ?: ($r['alt_text'] ?: 'Picture ' . $id);
            $missing = trim((string) $r['alt_text']) === '';
        ?>
            <li class="gallery-tile<?= empty($r['is_active']) ? ' is-hidden' : '' ?><?= $missing ? ' needs-alt' : '' ?>" data-gallery-item data-id="<?= $id ?>">
                <label class="gallery-tile__check">
                    <input type="checkbox" name="ids[]" value="<?= $id ?>" form="bulk-form" data-bulk-item>
                    <span class="visually-hidden">Select: <?= e($label) ?></span>
                </label>
                <a class="gallery-tile__media" href="<?= e(url('admin/gallery/' . $id . '/edit')) ?>" tabindex="-1" aria-hidden="true">
                    <img src="<?= e(img_url($r['image'])) ?>" alt="" loading="lazy">
                </a>
                <div class="gallery-tile__body">
                    <p class="gallery-tile__alt">
                        <?php if ($missing): ?>
                            <a href="<?= e(url('admin/gallery/' . $id . '/edit')) ?>" class="badge badge--warning">Needs a description</a>
                        <?php else: ?>
                            <?= e(excerpt($r['alt_text'], 70)) ?>
                        <?php endif; ?>
                    </p>
                    <p class="gallery-tile__meta">
                        <?php if (!empty($r['album'])): ?><span class="badge badge--outline"><?= e($r['album']) ?></span><?php endif; ?>
                        <?php if (empty($r['is_active'])): ?><span class="badge badge--neutral">Hidden</span><?php endif; ?>
                    </p>
                </div>
                <div class="gallery-tile__tools">
                    <?php if ($canReorder): ?>
                        <span class="gallery-tile__handle" data-drag-handle title="Drag to reorder" aria-hidden="true"><?= icon('grip', 'icon--sm') ?></span>
                        <button class="btn btn--ghost btn--icon js-only" type="button" data-move="-1" title="Move earlier" aria-label="<?= e('Move earlier: ' . $label) ?>"><?= icon('arrow-left', 'icon--sm') ?></button>
                        <button class="btn btn--ghost btn--icon js-only" type="button" data-move="1" title="Move later" aria-label="<?= e('Move later: ' . $label) ?>"><?= icon('arrow-right', 'icon--sm') ?></button>
                    <?php endif; ?>
                    <form method="post" action="<?= e(url('admin/gallery/' . $id . '/toggle')) ?>" class="inline-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="return" value="<?= e($returnQs) ?>">
                        <button class="btn btn--ghost btn--icon" type="submit" title="<?= empty($r['is_active']) ? 'Show on the website' : 'Hide from the website' ?>"
                                aria-label="<?= e((empty($r['is_active']) ? 'Show on the website: ' : 'Hide from the website: ') . $label) ?>"><?= icon(empty($r['is_active']) ? 'eye-off' : 'eye', 'icon--sm') ?></button>
                    </form>
                    <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/gallery/' . $id . '/edit')) ?>" title="Edit" aria-label="<?= e('Edit: ' . $label) ?>"><?= icon('edit', 'icon--sm') ?></a>
                    <?php if (can('content.delete')): ?>
                        <form method="post" action="<?= e(url('admin/gallery/' . $id . '/delete')) ?>" class="inline-form" data-confirm="Delete this picture? This cannot be undone.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="return" value="<?= e($returnQs) ?>">
                            <button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="<?= e('Delete: ' . $label) ?>"><?= icon('trash', 'icon--sm') ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => $c['path']]) ?>
<?php endif; ?>
