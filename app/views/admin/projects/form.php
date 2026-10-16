<?php
/**
 * Project create/edit form. OWNER: B4. Vars: $c $row $isNew $id $action $listUrl $publicUrl $programs (id => title) $statuses.
 */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'New project' : 'Edit project',
    'subtitle' => $isNew ? 'Describe the project; it appears on the website as soon as it is shown.' : (string) $row['title'],
]) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">Project details</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title'), 'class' => 'field--full']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => 'projects/', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'tagline', 'label' => 'Tagline', 'maxlength' => 190, 'value' => $v('tagline'), 'class' => 'field--full',
                            'help' => 'One short line shown under the title.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('summary'), 'class' => 'field--full',
                            'help' => 'A few sentences used on cards and lists. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Full description', 'value' => $v('body'), 'rows' => 14,
                        'help' => 'The full text shown on the project page.', 'placeholder' => 'Write the full description here...']) ?>
                </div>
            </section>
            <?= partial('admin/_content/seo-panel', ['row' => $row, 'titleHint' => 'the project title']) ?>
        </div>

        <aside class="content-aside" aria-label="Project settings">
            <section class="card" aria-labelledby="c-vis">
                <div class="card__header"><h2 class="card__title" id="c-vis">Visibility</h2></div>
                <div class="card__body">
                    <?= partial('admin/_content/switch', ['name' => 'is_active', 'label' => 'Shown on the website', 'checked' => (bool) $v('is_active', 1)]) ?>
                    <?= partial('admin/_content/switch', ['name' => 'is_featured', 'label' => 'Featured', 'checked' => (bool) $v('is_featured', 0), 'help' => 'Featured projects are highlighted where the website supports it.']) ?>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Display order', 'type' => 'number', 'value' => $v('sort_order', 0), 'step' => 1,
                        'help' => 'Lower numbers come first. You can also use the arrows on the list.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="c-meta">
                <div class="card__header"><h2 class="card__title" id="c-meta">Program and progress</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'program_id', 'label' => 'Program', 'type' => 'select', 'value' => $v('program_id'), 'empty' => 'Not linked to a program', 'options' => $programs,
                        'help' => $programs === [] ? 'Add a program first if you want to link projects to it.' : 'The program this project belongs to.']) ?>
                    <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Stage', 'type' => 'select', 'value' => $v('status', 'planned'), 'options' => $statuses, 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Location', 'maxlength' => 190, 'value' => $v('location'), 'help' => 'For example a town or district.']) ?>
                    <?= partial('partials/form-field', ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'value' => $v('start_date')]) ?>
                    <?= partial('partials/form-field', ['name' => 'end_date', 'label' => 'End date', 'type' => 'date', 'value' => $v('end_date'), 'help' => 'Leave empty if the project has no end date yet.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="c-img">
                <div class="card__header"><h2 class="card__title" id="c-img">Picture</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', ['name' => 'image', 'label' => 'Project picture', 'value' => $v('image'), 'alt_name' => 'image_alt', 'alt_value' => $v('image_alt'),
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
