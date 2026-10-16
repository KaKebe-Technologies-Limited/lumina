<?php
/** Activity create/edit form. OWNER: B4. Vars: $c $row $isNew $id $action $listUrl $publicUrl. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'New activity' : 'Edit activity',
    'subtitle' => $isNew ? 'Tell visitors about something the foundation has done or is planning.' : (string) $row['title'],
]) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">Activity details</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 220, 'value' => $v('title'), 'class' => 'field--full']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => 'activities/', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'activity_date', 'label' => 'Date', 'type' => 'date', 'value' => $v('activity_date'), 'help' => 'When it took place (or will take place).']) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Place', 'maxlength' => 190, 'value' => $v('location'), 'help' => 'For example a town, school or venue.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('summary'), 'class' => 'field--full',
                            'help' => 'A few sentences used on cards and lists. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Full story', 'value' => $v('body'), 'rows' => 14,
                        'help' => 'The full account shown on the activity page. You can add pictures with the picture button.', 'placeholder' => 'Describe the activity here...']) ?>
                </div>
            </section>
            <?= partial('admin/_content/seo-panel', ['row' => $row, 'titleHint' => 'the activity title']) ?>
        </div>

        <aside class="content-aside" aria-label="Activity settings">
            <section class="card" aria-labelledby="c-vis">
                <div class="card__header"><h2 class="card__title" id="c-vis">Visibility</h2></div>
                <div class="card__body">
                    <?= partial('admin/_content/switch', ['name' => 'is_active', 'label' => 'Shown on the website', 'checked' => (bool) $v('is_active', 1)]) ?>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Display order', 'type' => 'number', 'value' => $v('sort_order', 0), 'step' => 1,
                        'help' => 'Lower numbers come first; activities with the same number are shown newest first. You can also use the arrows on the list.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="c-img">
                <div class="card__header"><h2 class="card__title" id="c-img">Picture</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', ['name' => 'image', 'label' => 'Main picture', 'value' => $v('image'), 'alt_name' => 'image_alt', 'alt_value' => $v('image_alt'),
                        'help' => 'JPG, PNG or WebP up to 5 MB. Describe what the picture shows in the box below it, for people who use screen readers.']) ?>
                </div>
            </section>
        </aside>
    </div>
    <?= partial('admin/_content/form-actions', ['listUrl' => $listUrl, 'isNew' => $isNew, 'publicUrl' => $publicUrl]) ?>
</form>

<?php if (!$isNew): ?>
    <?= partial('admin/_content/danger-zone', ['c' => $c, 'row' => $row]) ?>
<?php endif; ?>
