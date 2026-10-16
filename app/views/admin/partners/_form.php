<?php
/**
 * Partner form (create + edit). OWNER: B8.
 * Vars: $partner (row or defaults), $types (value => label), $action (POST url), $isEdit (bool).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$val = static fn(string $key, mixed $default = '') => old($key, $partner[$key] ?? $default);
$isActive = (int) old('is_active', $partner['is_active'] ?? 1) === 1;
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <section class="card" aria-labelledby="partner-details">
            <div class="card__header">
                <h2 class="card__title" id="partner-details">Details</h2>
                <span class="required-note"><span aria-hidden="true">*</span> required</span>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Organisation name', 'required' => true, 'value' => $val('name'), 'maxlength' => 190, 'autocomplete' => 'off', 'class' => 'field--full', 'help' => 'Also used as the logo\'s alternative text on the website.']) ?>
                    <?= partial('partials/form-field', ['name' => 'partner_type', 'label' => 'Type', 'type' => 'select', 'required' => true, 'value' => $val('partner_type', 'partner'), 'options' => $types]) ?>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Position', 'type' => 'number', 'value' => $val('sort_order'), 'min' => 0, 'step' => 1, 'help' => 'Lower numbers come first. Leave empty to add the partner last, or use Reorder on the list page.']) ?>
                    <?= partial('partials/form-field', ['name' => 'website', 'label' => 'Website (optional)', 'value' => $val('website'), 'maxlength' => 255, 'autocomplete' => 'off', 'class' => 'field--full', 'placeholder' => 'https://www.example.org', 'attrs' => ['inputmode' => 'url'], 'help' => 'If set, the logo links to this address.']) ?>
                    <?= partial('partials/form-field', ['name' => 'description', 'label' => 'Short description (optional)', 'type' => 'textarea', 'rows' => 5, 'value' => $val('description'), 'maxlength' => 2000, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Plain text. How the organisation supports or works with the foundation.']) ?>
                </div>
            </div>
        </section>

        <div class="admin-org__aside">
            <section class="card" aria-labelledby="partner-logo">
                <div class="card__header"><h2 class="card__title" id="partner-logo">Logo</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', [
                        'name'  => 'logo',
                        'label' => 'Organisation logo',
                        'value' => (string) $val('logo'),
                        'help'  => 'Optional (JPG, PNG or WebP, up to 5 MB). A wide logo on a plain or transparent background works best. Without a logo the organisation\'s name is shown instead. Only upload logos you have permission to use.',
                    ]) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="partner-visibility">
                <div class="card__header"><h2 class="card__title" id="partner-visibility">Visibility</h2></div>
                <div class="card__body">
                    <input type="hidden" name="is_active" value="0">
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__text">Show on the website</span>
                    </label>
                    <p class="form-note">Switch off to keep the partner in the list without showing it publicly.</p>
                </div>
            </section>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Add partner' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/partners')) ?>">Cancel</a>
        <?php if ($isEdit && can('content.delete')): ?>
            <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
        <?php endif; ?>
    </div>
</form>
