<?php
/**
 * Impact story form (create + edit). OWNER: B8.
 * Vars: $story (row or defaults), $action (POST url), $isEdit (bool).
 * CONSENT: the box is required before publishing. The server forces a draft when it is not ticked (see StoryController);
 * admin-org.js additionally disables the Publish switch until consent is ticked.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$val = static fn(string $key, mixed $default = '') => old($key, $story[$key] ?? $default);
$dt = static function (string $key) use ($story): string {
    $posted = old($key, null);
    if ($posted !== null) {
        return (string) $posted;
    }
    $v = $story[$key] ?? '';
    return $v ? date('Y-m-d\TH:i', (int) strtotime((string) $v)) : '';
};
$consent = (int) old('consent_confirmed', $story['consent_confirmed'] ?? 0) === 1;
$isActive = (int) old('is_active', $story['is_active'] ?? 0) === 1 && $consent;
$tz = date_default_timezone_get();
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div>
            <section class="card" aria-labelledby="st-story">
                <div class="card__header">
                    <h2 class="card__title" id="st-story">The story</h2>
                    <span class="required-note"><span aria-hidden="true">*</span> required</span>
                </div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Story title', 'required' => true, 'value' => $val('title'), 'maxlength' => 220, 'class' => 'field--full', 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'slug', 'label' => 'Web address', 'value' => $val('slug'), 'maxlength' => 190, 'class' => 'field--full', 'autocomplete' => 'off', 'attrs' => ['data-slug-source' => '#f-title'], 'help' => 'Filled in from the title. The story page will be at /stories/your-web-address.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'value' => $val('summary'), 'maxlength' => 500, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'One or two sentences shown on the stories list.']) ?>
                        <div class="field--full">
                            <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Full story', 'value' => (string) $val('body'), 'rows' => 14, 'help' => 'Only include what the person has agreed to share. Leave out exact addresses, phone numbers and anything that could put someone at risk.']) ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="st-person">
                <div class="card__header"><h2 class="card__title" id="st-person">About the person</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'person_name', 'label' => 'Name as it may be shown (optional)', 'value' => $val('person_name'), 'maxlength' => 150, 'autocomplete' => 'off', 'help' => 'A first name or an agreed alias is fine.']) ?>
                        <?= partial('partials/form-field', ['name' => 'person_role', 'label' => 'Role or description (optional)', 'value' => $val('person_role'), 'maxlength' => 150, 'autocomplete' => 'off', 'placeholder' => 'For example: Programme participant']) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Location (optional)', 'value' => $val('location'), 'maxlength' => 190, 'class' => 'field--full', 'autocomplete' => 'off', 'help' => 'A town or district is enough.']) ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-org__aside">
            <section class="card consent-card" aria-labelledby="st-consent">
                <div class="card__header"><h2 class="card__title" id="st-consent"><?= icon('shield', 'icon--sm') ?> Consent</h2></div>
                <div class="card__body">
                    <p>Impact stories can include a person's name, words and photo, so they are only published with permission.</p>
                    <input type="hidden" name="consent_confirmed" value="0">
                    <label class="check consent-check" for="consent_confirmed">
                        <input type="checkbox" id="consent_confirmed" name="consent_confirmed" value="1" data-consent aria-describedby="consent-help"<?= $consent ? ' checked' : '' ?>>
                        <span><strong>I confirm that consent has been given.</strong> The person in this story (or their parent or guardian, if they are under 18) understands how their story, name and photos will be used on this website, has agreed to it, and the foundation keeps a record of that agreement.</span>
                    </label>
                    <p class="form-note" id="consent-help">A story cannot be published until this box is ticked. If consent is withdrawn later, untick it and save: the story is taken offline straight away. Who confirmed or withdrew consent, and when, is written to the audit log.</p>
                </div>
            </section>

            <section class="card" aria-labelledby="st-publish">
                <div class="card__header"><h2 class="card__title" id="st-publish">Publishing</h2></div>
                <div class="card__body">
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="switch">
                            <input type="checkbox" name="is_active" value="1" data-requires-consent="#consent_confirmed"<?= $isActive ? ' checked' : '' ?><?= $consent ? '' : ' disabled' ?>>
                            <span class="switch__track" aria-hidden="true"></span>
                            <span class="switch__text">Published on the website</span>
                        </label>
                        <p class="form-note" data-consent-note<?= $consent ? ' hidden' : '' ?>>Confirm consent above to enable publishing. Until then the story is saved as a draft.</p>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'published_at', 'label' => 'Publish date (optional)', 'type' => 'datetime-local', 'value' => $dt('published_at'), 'help' => 'Leave empty to publish immediately. A future date schedules the story. Times use the website timezone (' . $tz . ').']) ?>
                </div>
            </section>

            <section class="card" aria-labelledby="st-image">
                <div class="card__header"><h2 class="card__title" id="st-image">Image</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', [
                        'name'      => 'image',
                        'label'     => 'Story image',
                        'value'     => (string) $val('image'),
                        'alt_name'  => 'image_alt',
                        'alt_value' => (string) $val('image_alt'),
                        'help'      => 'Optional (JPG, PNG or WebP, up to 5 MB). Only use a photo of a person if consent covers it. The description is required when an image is set: say what is visible, and do not name the person unless they agreed to it.',
                    ]) ?>
                </div>
            </section>

            <details class="card admin-org__details">
                <summary class="card__header"><span class="card__title">Search engine details (optional)</span></summary>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'seo_title', 'label' => 'SEO title', 'value' => $val('seo_title'), 'maxlength' => 190, 'help' => 'Defaults to the story title.']) ?>
                    <?= partial('partials/form-field', ['name' => 'seo_description', 'label' => 'SEO description', 'type' => 'textarea', 'rows' => 3, 'value' => $val('seo_description'), 'maxlength' => 300, 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Defaults to the short summary.']) ?>
                </div>
            </details>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Save story' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/stories')) ?>">Cancel</a>
        <?php if ($isEdit && can('content.delete')): ?>
            <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
        <?php endif; ?>
    </div>
</form>
