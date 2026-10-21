<?php /** Partner course form. Vars: $row, $course, $isNew, $action, $categories, $levels, $costs. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d); ?>
<?= partial('partials/admin/page-header', ['title' => $isNew ? 'Add a partner course' : 'Edit partner course', 'subtitle' => $isNew ? 'Link to a free course on another platform. Write the summary in your own words.' : (string) $course['title']]) ?>
<form method="post" action="<?= e($action) ?>" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <section class="card" aria-labelledby="k-main">
        <div class="card__header"><h2 class="card__title" id="k-main">The course</h2></div>
        <div class="card__body"><div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title'), 'class' => 'field--full']) ?>
            <?= partial('partials/form-field', ['name' => 'provider', 'label' => 'Platform', 'required' => true, 'maxlength' => 80, 'value' => $v('provider'), 'help' => 'For example: Coursera, edX, Google, Microsoft Learn.']) ?>
            <?= partial('partials/form-field', ['name' => 'url', 'label' => 'Course address', 'type' => 'url', 'required' => true, 'maxlength' => 480, 'value' => $v('url'), 'class' => 'field--full', 'help' => 'The full address starting with https://']) ?>
            <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Summary (your own words)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 320, 'value' => $v('summary'), 'class' => 'field--full', 'help' => 'Up to 320 characters. Do not paste text from the platform.']) ?>
            <?= partial('partials/form-field', ['name' => 'category', 'label' => 'Topic', 'type' => 'select', 'options' => $categories, 'value' => $v('category'), 'required' => true]) ?>
            <?= partial('partials/form-field', ['name' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => $levels, 'value' => $v('level'), 'required' => true]) ?>
            <?= partial('partials/form-field', ['name' => 'cost', 'label' => 'Cost', 'type' => 'select', 'options' => $costs, 'value' => $v('cost'), 'required' => true]) ?>
            <?= partial('partials/form-field', ['name' => 'duration', 'label' => 'Length', 'maxlength' => 60, 'value' => $v('duration'), 'help' => 'For example: About 6 hours, 8 weeks, Self-paced.']) ?>
            <?= partial('partials/form-field', ['name' => 'certificate', 'label' => 'Certificate note', 'maxlength' => 80, 'value' => $v('certificate'), 'help' => 'For example: Free certificate, Paid certificate, No certificate.']) ?>
            <?= partial('partials/form-field', ['name' => 'language', 'label' => 'Language', 'maxlength' => 40, 'value' => $v('language', 'English')]) ?>
            <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number', 'min' => 0, 'max' => 9999, 'value' => $v('sort_order', 100), 'help' => 'Lower numbers come first.']) ?>
            <?= partial('partials/form-field', ['name' => 'is_active', 'label' => 'Visible to visitors', 'type' => 'checkbox', 'value' => (int) $v('is_active', 1) === 1]) ?>
            <?= partial('partials/form-field', ['name' => 'is_featured', 'label' => 'Featured (listed first)', 'type' => 'checkbox', 'value' => (int) $v('is_featured', 0) === 1]) ?>
        </div></div>
    </section>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Add the course' : 'Save changes' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/partner-courses')) ?>">Back to the list</a>
    </div>
</form>
