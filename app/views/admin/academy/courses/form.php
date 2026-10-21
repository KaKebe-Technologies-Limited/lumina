<?php
/** Course details form. Vars: $row, $course, $isNew, $action, $categories, $levels, $manager, $facilitators, $outcomes, $requirements, $resources, $tree. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'New course' : 'Edit course',
    'subtitle' => $isNew ? 'Start with the basics. You add modules, lessons and quizzes on the next screen.' : (string) $course['title'],
    'actions' => $isNew ? [] : [['label' => 'Lessons and quizzes', 'url' => url('admin/academy/courses/' . (int) $course['id'] . '/curriculum'), 'icon' => 'layers', 'class' => 'btn--outline']],
]) ?>
<form method="post" action="<?= e($action) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">The course</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title'), 'class' => 'field--full', 'help' => 'For example: Microsoft Word: Documents from Zero']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => 'academy/courses/', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'category', 'label' => 'Topic', 'type' => 'select', 'options' => $categories, 'value' => $v('category', 'basics'), 'required' => true]) ?>
                        <?= partial('partials/form-field', ['name' => 'level', 'label' => 'Level', 'type' => 'select', 'options' => $levels, 'value' => $v('level', 'beginner'), 'required' => true]) ?>
                        <?= partial('partials/form-field', ['name' => 'icon', 'label' => 'Icon', 'maxlength' => 40, 'value' => $v('icon', 'book'), 'help' => 'An icon name such as laptop, file-text, mail, globe, megaphone, shield, briefcase, code, star, settings, book.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 2, 'maxlength' => 255, 'value' => $v('summary'), 'class' => 'field--full', 'help' => 'One or two sentences on the course card.']) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'About this course', 'value' => $v('description'), 'rows' => 8, 'help' => 'Who it is for and what it covers.']) ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'outcomes', 'label' => 'What learners will be able to do', 'type' => 'textarea', 'rows' => 6, 'value' => old('outcomes', $outcomes), 'class' => 'field--full', 'help' => 'One point per line, each starting with a verb ("Create a CV in Word").']) ?>
                        <?= partial('partials/form-field', ['name' => 'requirements', 'label' => 'What learners need', 'type' => 'textarea', 'rows' => 3, 'value' => old('requirements', $requirements), 'class' => 'field--full', 'help' => 'One per line. For example: A computer or smartphone. No experience needed.']) ?>
                        <?= partial('partials/form-field', ['name' => 'resources', 'label' => 'Further reading links', 'type' => 'textarea', 'rows' => 3, 'value' => old('resources', $resources), 'class' => 'field--full', 'help' => 'One per line, as: Title | https://address. Only add pages you trust.']) ?>
                    </div>
                </div>
            </section>
        </div>
        <aside class="content-aside" aria-label="Course settings">
            <section class="card" aria-labelledby="c-set">
                <div class="card__header"><h2 class="card__title" id="c-set">Publishing</h2></div>
                <div class="card__body">
                    <?php if ($isNew): ?>
                        <p class="form-note">A new course starts as a draft. Publish it from the course list when it has lessons.</p>
                        <input type="hidden" name="status" value="draft">
                    <?php else: ?>
                        <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draft (hidden)', 'published' => 'Published', 'archived' => 'Archived (hidden, progress kept)'], 'value' => $v('status', 'draft'), 'required' => true]) ?>
                    <?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'pass_mark', 'label' => 'Pass mark of the final exam (%)', 'type' => 'number', 'min' => 40, 'max' => 100, 'value' => $v('pass_mark', 70), 'required' => true]) ?>
                    <?= partial('admin/_content/switch', ['name' => 'certificate_enabled', 'label' => 'Issue a certificate', 'checked' => (bool) $v('certificate_enabled', 1), 'help' => 'Learners who finish every lesson and pass the final exam get a certificate with a number anyone can check.']) ?>
                </div>
            </section>
            <?php if ($manager): ?>
                <section class="card" aria-labelledby="c-adm">
                    <div class="card__header"><h2 class="card__title" id="c-adm">Administration</h2></div>
                    <div class="card__body">
                        <div class="field"><label class="field__label" for="f-facilitator_id">Facilitator</label>
                            <select class="select" id="f-facilitator_id" name="facilitator_id" aria-describedby="f-fac-help"><option value="">No facilitator</option>
                                <?php foreach ($facilitators as $f): ?><option value="<?= (int) $f['id'] ?>"<?= (int) $v('facilitator_id', 0) === (int) $f['id'] ? ' selected' : '' ?>><?= e($f['name']) ?> (<?= e($f['email']) ?>)</option><?php endforeach; ?></select>
                            <div class="field__help" id="f-fac-help">The teacher who owns this course: they can edit its lessons and quizzes, run its classes and see its learners. <a href="<?= e(url('admin/academy/facilitators')) ?>">Create a facilitator account</a>.</div></div>
                        <?= partial('admin/_content/switch', ['name' => 'is_featured', 'label' => 'Feature at the top of the catalogue', 'checked' => (bool) $v('is_featured', 0)]) ?>
                        <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Catalogue order', 'type' => 'number', 'value' => $v('sort_order', 100), 'help' => 'Smaller numbers come first.']) ?>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="after" value="stay"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Create the course' : 'Save changes' ?></button>
        <?php if (!$isNew): ?><button class="btn btn--outline" type="submit" name="after" value="curriculum">Save and open the lessons</button> <?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(url('admin/academy/courses')) ?>">Cancel</a>
    </div>
</form>
<?php if (!$isNew): ?>
    <form method="post" action="<?= e(url('admin/academy/courses/' . (int) $course['id'] . '/duplicate')) ?>" class="aac-inline"><?= csrf_field() ?>
        <button class="btn btn--outline btn--sm" type="submit"><?= icon('layers', 'icon--sm') ?> Make a draft copy of this course</button></form>
<?php endif; ?>
