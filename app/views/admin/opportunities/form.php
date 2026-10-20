<?php
/** Opportunity create/edit form. Vars: $c $row $isNew $id $action $listUrl $publicUrl $categories $pdfMaxMb. */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$close = (string) $v('deadline', '');
$today = date('Y-m-d');
$noteState = '';
if ($close !== '') {
    $noteState = $close < $today ? 'The closing date has passed: this opportunity is shown as closed on the website.' : 'Open until ' . format_date($close, 'j F Y') . ', then it shows as closed automatically.';
}
$fromFeed = !$isNew && ($row['source'] ?? '') === 'feed';
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'Add an opportunity' : 'Edit opportunity',
    'subtitle' => $isNew ? 'Tell young people what is on offer, who can apply and by when.' : (string) $row['title'],
]) ?>

<?php if ($fromFeed): ?>
    <div class="alert alert--info" role="note">
        This was read from <strong><?= e((string) $row['source_name']) ?></strong>. Visitors see a short excerpt, your changes, and a link to the original.
        <?php if (!empty($row['source_url'])): ?><a href="<?= e($row['source_url']) ?>" target="_blank" rel="noopener noreferrer">Open the original</a><?php endif; ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">The opportunity</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 255, 'value' => $v('title'), 'class' => 'field--full',
                            'help' => 'For example: Mastercard Foundation Scholars Program 2027']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => 'opportunities/', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'category', 'label' => 'Type', 'type' => 'select', 'value' => $v('category', 'scholarship'), 'options' => $categories, 'required' => true]) ?>
                        <?= partial('partials/form-field', ['name' => 'organisation', 'label' => 'Offered by', 'maxlength' => 190, 'value' => $v('organisation'), 'help' => 'The organisation, university or funder.']) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Where', 'maxlength' => 190, 'value' => $v('location'), 'help' => 'A country or city, or "Online" or "Open to all African countries".']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 1000, 'value' => $v('summary'), 'class' => 'field--full',
                            'help' => 'One or two sentences shown on the list. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'description', 'label' => 'Full details', 'value' => $v('description'), 'rows' => 10,
                        'help' => 'What it is, who it is for, what it covers. Headings, lists and links work here.', 'placeholder' => 'Describe the opportunity']) ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'eligibility', 'label' => 'Who can apply', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('eligibility'), 'class' => 'field--full',
                            'help' => 'Age, country, level of study and other conditions. Plain text, one per line works well.']) ?>
                        <?= partial('partials/form-field', ['name' => 'benefits', 'label' => 'What you get', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('benefits'), 'class' => 'field--full',
                            'help' => 'Funding, travel, mentoring, certificate ... Plain text.']) ?>
                        <?= partial('partials/form-field', ['name' => 'how_to_apply', 'label' => 'How to apply', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 5000, 'value' => $v('how_to_apply'), 'class' => 'field--full',
                            'help' => 'Steps or documents needed. Plain text.']) ?>
                        <?= partial('partials/form-field', ['name' => 'apply_url', 'label' => 'Application link', 'type' => 'url', 'maxlength' => 500, 'value' => $v('apply_url'), 'class' => 'field--full',
                            'help' => 'Where to apply, starting with https://']) ?>
                    </div>
                </div>
            </section>
            <?= partial('admin/_content/seo-panel', ['row' => $row, 'titleHint' => 'the title']) ?>
        </div>

        <aside class="content-aside" aria-label="Opportunity settings">
            <section class="card" aria-labelledby="c-vis">
                <div class="card__header"><h2 class="card__title" id="c-vis">Visibility and deadline</h2></div>
                <div class="card__body">
                    <?php if (!empty($row['needs_review'])): ?><p class="form-note" role="status">Waiting for approval. Saving with "Shown on the website" ticked approves it.</p><?php endif; ?>
                    <?= partial('admin/_content/switch', ['name' => 'is_active', 'label' => 'Shown on the website', 'checked' => (bool) $v('is_active', 1), 'help' => 'Switch off to hide it without deleting it.']) ?>
                    <?= partial('admin/_content/switch', ['name' => 'is_featured', 'label' => 'Feature at the top', 'checked' => (bool) $v('is_featured', 0), 'help' => 'Featured opportunities are listed first.']) ?>
                    <?= partial('partials/form-field', ['name' => 'deadline', 'label' => 'Closing date', 'type' => 'date', 'value' => $close, 'help' => 'The last day to apply. Leave empty for "rolling" (no fixed date).']) ?>
                    <?php if ($noteState !== ''): ?><p class="form-note" role="status"><?= e($noteState) ?></p><?php endif; ?>
                </div>
            </section>
            <section class="card" aria-labelledby="c-img">
                <div class="card__header"><h2 class="card__title" id="c-img">Picture</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', ['name' => 'image', 'label' => 'Picture or logo', 'value' => $v('image'), 'alt_name' => 'image_alt', 'alt_value' => $v('image_alt'),
                        'help' => 'Optional. JPG, PNG or WebP up to 5 MB. Describe it in the box below for screen-reader users.']) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="c-file">
                <div class="card__header"><h2 class="card__title" id="c-file">Attach a PDF</h2></div>
                <div class="card__body">
                    <?php if (!empty($row['file_path'])): ?>
                        <p class="form-note"><?= icon('file-text', 'icon--sm') ?> <strong><?= e((string) $row['file_name']) ?></strong> (<?= e(number_format(((int) $row['file_size']) / 1048576, 1)) ?> MB)</p>
                        <?= partial('admin/_content/switch', ['name' => 'remove_attachment', 'label' => 'Remove this PDF', 'checked' => false, 'id' => 'f-remove_attachment']) ?>
                    <?php endif; ?>
                    <?php $attErr = error('attachment'); ?>
                    <div class="field<?= $attErr ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-attachment"><?= !empty($row['file_path']) ? 'Replace the PDF' : 'Upload a PDF' ?></label>
                        <input class="input" type="file" id="f-attachment" name="attachment" accept="application/pdf,.pdf" aria-describedby="f-attachment-help">
                        <div class="field__help" id="f-attachment-help">The call text, flyer or application guide. PDF only, up to <?= (int) $pdfMaxMb ?> MB. Visitors can download it from the opportunity page.</div>
                        <?php if ($attErr): ?><div class="field__error" id="f-attachment-error"><?= e((string) $attErr) ?></div><?php endif; ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <?= partial('admin/_content/form-actions', ['listUrl' => $listUrl, 'isNew' => $isNew, 'publicUrl' => $publicUrl]) ?>
</form>

<?php if (!$isNew): ?>
    <?= partial('admin/_content/danger-zone', ['c' => $c, 'row' => $row, 'warning' => 'The uploaded PDF is deleted too']) ?>
<?php endif; ?>
