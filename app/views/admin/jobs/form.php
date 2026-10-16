<?php
/** Job (vacancy) create/edit form. OWNER: B4. Vars: $c $row $isNew $id $action $listUrl $publicUrl $types. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$close = (string) $v('closing_date', '');
$today = date('Y-m-d');
$noteState = '';
if ($close !== '') {
    $noteState = $close < $today ? 'The closing date has passed: the vacancy is closed on the website.' : 'Open until ' . format_date($close, 'j F Y') . ', then it closes automatically.';
}
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'New vacancy' : 'Edit vacancy',
    'subtitle' => $isNew ? 'Describe the role. It closes by itself on its closing date.' : (string) $row['title'],
]) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">The role</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Job title', 'required' => true, 'maxlength' => 220, 'value' => $v('title'), 'class' => 'field--full']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => 'careers/', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'department', 'label' => 'Department or team', 'maxlength' => 120, 'value' => $v('department')]) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Place of work', 'maxlength' => 190, 'value' => $v('location'), 'help' => 'For example Gulu City, or Remote.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 5000, 'value' => $v('summary'), 'class' => 'field--full',
                            'help' => 'One or two sentences shown on the list of vacancies. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'Description of the role', 'value' => $v('description'), 'rows' => 10,
                        'help' => 'Duties and responsibilities.', 'placeholder' => 'What will this person do?']) ?>
                    <?= partial('partials/admin/richtext-field', ['name' => 'requirements', 'label' => 'Requirements', 'value' => $v('requirements'), 'rows' => 10,
                        'help' => 'Skills, experience and qualifications. A bulleted list works well.', 'placeholder' => 'What should applicants have?']) ?>
                    <?= partial('partials/form-field', ['name' => 'how_to_apply', 'label' => 'How to apply', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('how_to_apply'),
                        'help' => 'Extra instructions for applicants. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                </div>
            </section>
            <?= partial('admin/_content/seo-panel', ['row' => $row, 'titleHint' => 'the job title']) ?>
        </div>

        <aside class="content-aside" aria-label="Vacancy settings">
            <section class="card" aria-labelledby="c-vis">
                <div class="card__header"><h2 class="card__title" id="c-vis">Visibility and deadline</h2></div>
                <div class="card__body">
                    <?= partial('admin/_content/switch', ['name' => 'is_active', 'label' => 'Shown on the website', 'checked' => (bool) $v('is_active', 1),
                        'help' => 'Switch off to hide the vacancy without deleting it.']) ?>
                    <?= partial('partials/form-field', ['name' => 'closing_date', 'label' => 'Closing date', 'type' => 'date', 'value' => $close,
                        'help' => 'The last day people can apply. Leave empty if there is no deadline.']) ?>
                    <?php if ($noteState !== ''): ?><p class="form-note" role="status"><?= e($noteState) ?></p><?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'employment_type', 'label' => 'Type of work', 'type' => 'select', 'value' => $v('employment_type', 'full_time'), 'options' => $types, 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'apply_email', 'label' => 'Application email', 'type' => 'email', 'maxlength' => 190, 'value' => $v('apply_email'),
                        'help' => 'Optional. Shown to applicants if they should write to a specific address.']) ?>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Display order', 'type' => 'number', 'value' => $v('sort_order', 0), 'step' => 1,
                        'help' => 'Lower numbers come first. You can also use the arrows on the list.']) ?>
                </div>
            </section>
        </aside>
    </div>
    <?= partial('admin/_content/form-actions', ['listUrl' => $listUrl, 'isNew' => $isNew, 'publicUrl' => $publicUrl]) ?>
</form>

<?php if (!$isNew): ?>
    <?= partial('admin/_content/danger-zone', ['c' => $c, 'row' => $row, 'warning' => 'Applications already received are kept']) ?>
<?php endif; ?>
