<?php
/** Lesson form. Vars: $row, $lesson, $module, $course, $isNew, $action, $back. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => $isNew ? 'New lesson' : 'Edit lesson', 'subtitle' => $course['title'] . ' - ' . $module['title']]) ?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="l-main">
                <div class="card__header"><h2 class="card__title" id="l-main">The lesson</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 190, 'value' => $v('title')]) ?>
                    <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Lesson text', 'value' => $v('body'), 'rows' => 16,
                        'help' => 'Headings, lists, tables, links and pictures work. Tip: end with a "Try it yourself" activity and a "Key points" list.', 'placeholder' => 'Write the lesson here']) ?>
                </div>
            </section>
        </div>
        <aside class="content-aside" aria-label="Lesson settings">
            <section class="card" aria-labelledby="l-set">
                <div class="card__header"><h2 class="card__title" id="l-set">Settings</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => ['text' => 'Reading lesson', 'video' => 'Video lesson', 'file' => 'Handout to download', 'link' => 'Outside web page'], 'value' => $v('kind', 'text'), 'required' => true,
                        'help' => 'Only changes the little icon: a lesson can have text, a video, a handout and a link together.']) ?>
                    <?= partial('partials/form-field', ['name' => 'minutes', 'label' => 'Time it takes (minutes)', 'type' => 'number', 'min' => 1, 'max' => 240, 'value' => $v('minutes', 10), 'required' => true]) ?>
                    <?= partial('admin/_content/switch', ['name' => 'is_published', 'label' => 'Shown to learners', 'checked' => (bool) $v('is_published', 1), 'help' => 'Switch off to keep the lesson as a draft.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="l-video">
                <div class="card__header"><h2 class="card__title" id="l-video">Video and links</h2></div>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'video_url', 'label' => 'YouTube link', 'type' => 'url', 'maxlength' => 255, 'value' => $v('video_url'), 'help' => 'The video plays inside the lesson page.']) ?>
                    <?= partial('partials/form-field', ['name' => 'link_url', 'label' => 'Outside page (optional)', 'type' => 'url', 'maxlength' => 500, 'value' => $v('link_url'), 'help' => 'A button that opens this page in a new tab. https:// only.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="l-file">
                <div class="card__header"><h2 class="card__title" id="l-file">Handout</h2></div>
                <div class="card__body">
                    <?php if (!empty($lesson['file_path'])): ?>
                        <p class="form-note"><?= icon('file-text', 'icon--sm') ?> <a href="<?= e(url('admin/academy/lessons/' . (int) $lesson['id'] . '/file')) ?>"><?= e((string) $lesson['file_name']) ?></a> (<?= e(number_format(((int) $lesson['file_size']) / 1048576, 1)) ?> MB)</p>
                        <?= partial('admin/_content/switch', ['name' => 'remove_attachment', 'label' => 'Remove this handout', 'checked' => false, 'id' => 'f-remove_attachment']) ?>
                    <?php endif; ?>
                    <?php $attErr = error('attachment'); ?>
                    <div class="field<?= $attErr ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-attachment"><?= !empty($lesson['file_path']) ? 'Replace the handout' : 'Upload a handout' ?></label>
                        <input class="input" type="file" id="f-attachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt" aria-describedby="f-attachment-help">
                        <div class="field__help" id="f-attachment-help">PDF, Word, Excel, PowerPoint, CSV or text, up to 20 MB. Learners download it from the lesson page after they sign in and enrol.</div>
                        <?php if ($attErr): ?><div class="field__error"><?= e((string) $attErr) ?></div><?php endif; ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <div class="form-actions">
        <button class="btn btn--primary" type="submit" name="after" value="<?= $isNew ? 'close' : 'stay' ?>"><?= icon('save', 'icon--sm') ?> <?= $isNew ? 'Add the lesson' : 'Save changes' ?></button>
        <?php if (!$isNew): ?><button class="btn btn--outline" type="submit" name="after" value="close">Save and close</button><?php endif; ?>
        <a class="btn btn--ghost" href="<?= e($back) ?>">Back to the curriculum</a>
    </div>
</form>
