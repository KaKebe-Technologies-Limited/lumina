<?php
/**
 * Post create/edit form (blog / news / press). OWNER: B4.
 * Vars: $c $row $isNew $id $action $listUrl $publicUrl $typeLabels $stateLabels $categories $state $previewUrl.
 * JS (admin-content.js, [data-post-form]): shows the press fields only for press, the date field only when publishing / scheduling,
 * and keeps the public-address preview in step with the chosen type.
 */
$v = static fn(string $k, mixed $d = '') => old($k, $row[$k] ?? $d);
$type = (string) $v('post_type', 'blog');
$stateNow = (string) old('status', $state);
$at = (string) $v('published_at', '');
if ($at !== '' && strlen($at) >= 16) {
    $at = str_replace(' ', 'T', substr($at, 0, 16));          // datetime-local format
}
$errStatus = error('status');
$errAt = error('published_at');
$errType = error('post_type');
$stateHelp = [
    'draft'     => 'Only people who are signed in to the admin can see a draft.',
    'published' => 'Visible on the website now (or at the date below if it is in the future).',
    'scheduled' => 'Hidden until the date and time below, then it appears automatically.',
];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-content.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/admin/page-header', [
    'title' => $isNew ? 'New post' : 'Edit post',
    'subtitle' => $isNew ? 'Write a blog post, a news item or a press mention. Save it as a draft until it is ready.' : (string) $row['title'],
]) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" data-unsaved-guard data-post-form novalidate>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div class="content-main">
            <section class="card" aria-labelledby="c-main">
                <div class="card__header"><h2 class="card__title" id="c-main">Post</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Title', 'required' => true, 'maxlength' => 220, 'value' => $v('title'), 'class' => 'field--full']) ?>
                        <?= partial('admin/_content/slug-field', ['value' => (string) $v('slug'), 'source' => '#f-title', 'prefix' => $type . '/', 'prefixFrom' => '#f-post_type', 'isNew' => $isNew]) ?>
                        <?= partial('partials/form-field', ['name' => 'excerpt', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 500, 'value' => $v('excerpt'), 'class' => 'field--full',
                            'help' => 'One or two sentences shown on cards and in lists. Plain text.', 'attrs' => ['data-maxlength-counter' => true]]) ?>
                    </div>
                    <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Post text', 'value' => $v('body'), 'rows' => 18,
                        'help' => 'Required before the post can be published (for press coverage the link below can replace the text).', 'placeholder' => 'Write the post here...']) ?>
                </div>
            </section>

            <section class="card" aria-labelledby="c-press" data-show-for="press"<?= $type === 'press' ? '' : ' hidden' ?>>
                <div class="card__header"><h2 class="card__title" id="c-press">Press details</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'source_name', 'label' => 'Published by', 'maxlength' => 190, 'value' => $v('source_name'), 'help' => 'The newspaper, radio station or website that covered the foundation.']) ?>
                        <?= partial('partials/form-field', ['name' => 'external_url', 'label' => 'Link to the original article', 'type' => 'url', 'maxlength' => 500, 'value' => $v('external_url'), 'placeholder' => 'https://',
                            'help' => 'When set, the post card on the website opens this address.']) ?>
                    </div>
                </div>
            </section>

            <?= partial('admin/_content/seo-panel', ['row' => $row, 'titleHint' => 'the post title']) ?>
        </div>

        <aside class="content-aside" aria-label="Post settings">
            <section class="card" aria-labelledby="c-pub">
                <div class="card__header"><h2 class="card__title" id="c-pub">Publishing</h2></div>
                <div class="card__body">
                    <div class="field<?= $errStatus ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-status">Status</label>
                        <select class="select" id="f-status" name="status" data-status-select aria-describedby="f-status-help">
                            <?php foreach ($stateLabels as $sv => $sl): ?>
                                <option value="<?= e($sv) ?>" data-help="<?= e($stateHelp[$sv]) ?>"<?= $sv === $stateNow ? ' selected' : '' ?>><?= e($sl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field__help" id="f-status-help" data-status-help><?= e($stateHelp[$stateNow] ?? '') ?></div>
                        <?php if ($errStatus): ?><div class="field__error" role="alert"><?= e($errStatus) ?></div><?php endif; ?>
                    </div>
                    <div class="field<?= $errAt ? ' field--error' : '' ?>" data-date-wrap<?= $stateNow === 'draft' ? ' hidden' : '' ?>>
                        <label class="field__label" for="f-published_at">Publish date and time</label>
                        <input class="input" type="datetime-local" id="f-published_at" name="published_at" value="<?= e($at) ?>" aria-describedby="f-published_at-help"<?= $errAt ? ' aria-invalid="true"' : '' ?>>
                        <div class="field__help" id="f-published_at-help">Leave empty to publish now. Choose a future date to schedule the post.</div>
                        <?php if ($errAt): ?><div class="field__error" role="alert"><?= e($errAt) ?></div><?php endif; ?>
                    </div>
                    <?php if (!$isNew): ?>
                        <p class="form-note">Created <?= e(format_date($row['created_at'], 'j M Y, H:i')) ?> · last saved <?= e(format_date($row['updated_at'], 'j M Y, H:i')) ?></p>
                    <?php endif; ?>
                    <?= partial('admin/_content/switch', ['name' => 'is_featured', 'label' => 'Featured', 'checked' => (bool) $v('is_featured', 0), 'help' => 'Featured posts are highlighted where the website supports it.']) ?>
                </div>
            </section>

            <section class="card" aria-labelledby="c-det">
                <div class="card__header"><h2 class="card__title" id="c-det">Details</h2></div>
                <div class="card__body">
                    <div class="field<?= $errType ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-post_type">Type <span class="field__req" aria-hidden="true">*</span></label>
                        <select class="select" id="f-post_type" name="post_type" required data-type-select>
                            <?php foreach ($typeLabels as $tv => $tl): ?>
                                <option value="<?= e($tv) ?>" data-prefix="<?= e($tv) ?>/"<?= $tv === $type ? ' selected' : '' ?>><?= e($tl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field__help">Blog posts and news appear on their own pages; press is coverage of the foundation by others.</div>
                        <?php if ($errType): ?><div class="field__error" role="alert"><?= e($errType) ?></div><?php endif; ?>
                    </div>
                    <div class="field<?= has_error('category') ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-category">Category</label>
                        <input class="input" type="text" id="f-category" name="category" maxlength="80" value="<?= e($v('category')) ?>" list="category-options" autocomplete="off">
                        <datalist id="category-options"><?php foreach ($categories as $cat): ?><option value="<?= e($cat) ?>"><?php endforeach; ?></datalist>
                        <div class="field__help">Pick an existing category or type a new one.</div>
                        <?php if (has_error('category')): ?><div class="field__error" role="alert"><?= e(error('category')) ?></div><?php endif; ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'author_name', 'label' => 'Author', 'maxlength' => 120, 'value' => $v('author_name'), 'help' => 'Shown as the writer of the post.']) ?>
                </div>
            </section>

            <section class="card" aria-labelledby="c-img">
                <div class="card__header"><h2 class="card__title" id="c-img">Cover picture</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', ['name' => 'cover_image', 'label' => 'Cover picture', 'value' => $v('cover_image'), 'alt_name' => 'cover_alt', 'alt_value' => $v('cover_alt'),
                        'help' => 'JPG, PNG or WebP up to 5 MB. Describe what the picture shows in the box below it, for people who use screen readers.']) ?>
                </div>
            </section>
        </aside>
    </div>
    <?= partial('admin/_content/form-actions', ['listUrl' => $listUrl, 'isNew' => $isNew, 'publicUrl' => $publicUrl, 'previewUrl' => $previewUrl]) ?>
</form>

<?php if (!$isNew): ?>
    <?= partial('admin/_content/danger-zone', ['c' => $c, 'row' => $row]) ?>
<?php endif; ?>
