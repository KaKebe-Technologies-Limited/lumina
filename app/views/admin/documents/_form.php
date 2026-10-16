<?php
/**
 * Document form (upload + edit). OWNER: B8.
 * Vars: $doc (row or defaults), $categories (value => label), $maxBytes, $action (POST url), $isEdit (bool).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$val = static fn(string $key, mixed $default = '') => old($key, $doc[$key] ?? $default);
$isActive = (int) old('is_active', $doc['is_active'] ?? 1) === 1;
$isPublic = (int) old('is_public', $doc['is_public'] ?? 1) === 1;
$fileErr = error('document_file');
$exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];
$ext = $isEdit ? strtoupper((string) pathinfo((string) ($doc['file_name'] ?: $doc['file_path']), PATHINFO_EXTENSION)) : '';
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div>
            <section class="card" aria-labelledby="doc-file">
                <div class="card__header">
                    <h2 class="card__title" id="doc-file"><?= $isEdit ? 'File' : 'File to upload' ?></h2>
                    <?php if (!$isEdit): ?><span class="required-note"><span aria-hidden="true">*</span> required</span><?php endif; ?>
                </div>
                <div class="card__body">
                    <?php if ($isEdit): ?>
                        <div class="org-filecur<?= empty($doc['file_ok']) ? ' org-filecur--missing' : '' ?>">
                            <span class="org-fileicon" aria-hidden="true"><?= icon('file-text') ?><b><?= e($ext) ?></b></span>
                            <div class="org-filecur__text">
                                <strong><?= e($doc['file_name'] ?: 'document') ?></strong>
                                <span class="cell-sub"><?= e(human_filesize((int) $doc['file_size'])) ?> &middot; uploaded <?= e(format_date($doc['created_at'], 'j M Y')) ?></span>
                                <?php if (empty($doc['file_ok'])): ?><span class="badge badge--danger">The stored file is missing on the server. Upload it again below.</span><?php endif; ?>
                            </div>
                            <?php if (!empty($doc['file_ok'])): ?>
                                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/documents/' . (int) $doc['id'] . '/download')) ?>"><?= icon('download', 'icon--sm') ?> Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="field<?= $fileErr ? ' field--error' : '' ?>">
                        <label class="field__label" for="document_file"><?= $isEdit ? 'Replace the file (optional)' : 'Choose the file' ?><?= $isEdit ? '' : ' <span class="field__req" aria-hidden="true">*</span>' ?></label>
                        <input class="input" type="file" id="document_file" name="document_file" accept=".<?= e(implode(',.', $exts)) ?>"
                               data-file-input data-max-bytes="<?= (int) $maxBytes ?>" data-allowed="<?= e(implode(',', $exts)) ?>"
                               aria-describedby="document_file-help<?= $fileErr ? ' document_file-error' : '' ?>"<?= $fileErr ? ' aria-invalid="true"' : '' ?>>
                        <div class="field__help" id="document_file-help">
                            <?= e(strtoupper(implode(', ', $exts))) ?> &mdash; up to <?= e(human_filesize((int) $maxBytes)) ?>.
                            <?= $isEdit ? 'The current file is only deleted after the new one has been saved; the download counter is kept.' : 'The file is stored privately and is only downloadable through the website while the document is active and public.' ?>
                        </div>
                        <div class="field__help" data-file-status role="status"></div>
                        <?php if ($fileErr): ?><div class="field__error" id="document_file-error" role="alert"><?= e($fileErr) ?></div><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="doc-details">
                <div class="card__header">
                    <h2 class="card__title" id="doc-details">Details</h2>
                    <span class="required-note"><span aria-hidden="true">*</span> required</span>
                </div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'value' => $val('title'), 'maxlength' => 220, 'class' => 'field--full', 'autocomplete' => 'off', 'help' => 'The name visitors see, for example "Annual Report 2025".']) ?>
                        <?= partial('partials/form-field', ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => true, 'value' => $val('category', 'other'), 'options' => $categories]) ?>
                        <?= partial('partials/form-field', ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'value' => $val('year'), 'min' => 1990, 'max' => (int) date('Y') + 1, 'step' => 1, 'placeholder' => date('Y'), 'help' => 'The year the document covers. Visitors can filter by it.']) ?>
                        <?= partial('partials/form-field', ['name' => 'published_at', 'label' => 'Publication date (optional)', 'type' => 'date', 'value' => $val('published_at'), 'help' => 'Newer documents are listed first within a year.']) ?>
                        <?= partial('partials/form-field', ['name' => 'description', 'label' => 'Short description (optional)', 'type' => 'textarea', 'rows' => 5, 'value' => $val('description'), 'maxlength' => 2000, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Plain text shown under the title on the Reports & Downloads page.']) ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-org__aside">
            <section class="card" aria-labelledby="doc-visibility">
                <div class="card__header"><h2 class="card__title" id="doc-visibility">Visibility</h2></div>
                <div class="card__body">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="switch">
                            <input type="checkbox" name="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                            <span class="switch__track" aria-hidden="true"></span>
                            <span class="switch__text">Active</span>
                        </label>
                        <p class="form-note">Switch off to take the document offline without deleting it.</p>
                    </div>
                    <div>
                        <input type="hidden" name="is_public" value="0">
                        <label class="switch">
                            <input type="checkbox" name="is_public" value="1"<?= $isPublic ? ' checked' : '' ?>>
                            <span class="switch__track" aria-hidden="true"></span>
                            <span class="switch__text">Public download</span>
                        </label>
                        <p class="form-note">Private documents are never listed or downloadable by visitors; only signed-in admins can open them.</p>
                    </div>
                </div>
            </section>
            <?php if ($isEdit): ?>
                <section class="card" aria-labelledby="doc-stats">
                    <div class="card__header"><h2 class="card__title" id="doc-stats">Downloads</h2></div>
                    <div class="card__body">
                        <p class="org-figure"><span class="org-figure__value"><?= number_format((int) $doc['download_count']) ?></span> <span class="text-muted">public download<?= (int) $doc['download_count'] === 1 ? '' : 's' ?></span></p>
                        <p class="form-note">Counted each time a visitor downloads the file from the website. Admin downloads are not counted.</p>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon($isEdit ? 'save' : 'upload', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Upload document' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/documents')) ?>">Cancel</a>
        <?php if ($isEdit && can('content.delete')): ?>
            <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
        <?php endif; ?>
    </div>
</form>
