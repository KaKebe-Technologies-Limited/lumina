<?php
/** Edit one gallery picture. OWNER: B4. Vars: $c $row $isNew $id $action $listUrl $albums (name => count). */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
?>
<?= partial('partials/admin/page-header', [
    'title' => 'Edit picture',
    'subtitle' => 'Change the description, caption or album, or replace the picture.',
]) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-pic">
                <div class="card__header"><h2 class="card__title" id="c-pic">Picture</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', ['name' => 'image', 'label' => 'Picture', 'required' => true, 'value' => $v('image'), 'alt_name' => 'alt_text', 'alt_value' => $v('alt_text'),
                        'help' => 'JPG, PNG, GIF or WebP up to 5 MB. A picture can be replaced but not left empty. The description box is required: describe what the picture shows.']) ?>
                    <?= partial('partials/form-field', ['name' => 'caption', 'label' => 'Caption', 'maxlength' => 250, 'value' => $v('caption'),
                        'help' => 'Optional text shown under the picture on the website.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                </div>
            </section>
        </div>
        <aside class="content-aside" aria-label="Picture settings">
            <section class="card" aria-labelledby="c-vis">
                <div class="card__header"><h2 class="card__title" id="c-vis">Visibility and album</h2></div>
                <div class="card__body">
                    <?= partial('admin/_content/switch', ['name' => 'is_active', 'label' => 'Shown on the website', 'checked' => (bool) $v('is_active', 1)]) ?>
                    <div class="field<?= has_error('album') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-album">Album</label>
                        <input class="input" type="text" id="f-album" name="album" maxlength="100" value="<?= e($v('album')) ?>" list="album-options" autocomplete="off">
                        <datalist id="album-options"><?php foreach (array_keys($albums) as $a): ?><option value="<?= e($a) ?>"><?php endforeach; ?></datalist>
                        <div class="field__help">Pick an existing album or type a new name. Leave empty for no album.</div>
                        <?php if (has_error('album')): ?><div class="field__error" role="alert"><?= e(error('album')) ?></div><?php endif; ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Display order', 'type' => 'number', 'value' => $v('sort_order', 0), 'step' => 1,
                        'help' => 'Lower numbers come first. Dragging on the gallery list is easier.']) ?>
                </div>
            </section>
        </aside>
    </div>
    <?= partial('admin/_content/form-actions', ['listUrl' => $listUrl, 'isNew' => false]) ?>
</form>

<?= partial('admin/_content/danger-zone', ['c' => $c, 'row' => $row]) ?>
