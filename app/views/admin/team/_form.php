<?php
/**
 * Team member form (create + edit). OWNER: B8.
 * Vars: $member (row or defaults), $groups (value => label), $action (POST url), $isEdit (bool).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$val = static fn(string $key, mixed $default = '') => old($key, $member[$key] ?? $default);
$isActive = (int) old('is_active', $member['is_active'] ?? 1) === 1;
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <section class="card" aria-labelledby="team-details">
            <div class="card__header">
                <h2 class="card__title" id="team-details">Details</h2>
                <span class="required-note"><span aria-hidden="true">*</span> required</span>
            </div>
            <div class="card__body">
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'value' => $val('name'), 'maxlength' => 150, 'autocomplete' => 'off']) ?>
                    <?= partial('partials/form-field', ['name' => 'role_title', 'label' => 'Role or title', 'value' => $val('role_title'), 'maxlength' => 150, 'placeholder' => 'For example: Programs Coordinator', 'help' => 'Shown under the name on the Our Team page.']) ?>
                    <?= partial('partials/form-field', ['name' => 'team_group', 'label' => 'Group', 'type' => 'select', 'required' => true, 'value' => $val('team_group', 'staff'), 'options' => $groups, 'help' => 'Visitors see people grouped like this on the Our Team page.']) ?>
                    <?= partial('partials/form-field', ['name' => 'sort_order', 'label' => 'Position', 'type' => 'number', 'value' => $val('sort_order'), 'min' => 0, 'step' => 1, 'help' => 'Lower numbers come first inside the group. Leave empty to add the person last, or use Reorder on the list page.']) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Public email (optional)', 'type' => 'email', 'value' => $val('email'), 'maxlength' => 190, 'autocomplete' => 'off', 'class' => 'field--full', 'help' => 'Only fill this in if the person wants an email icon on their card.']) ?>
                    <?= partial('partials/form-field', ['name' => 'linkedin', 'label' => 'LinkedIn (optional)', 'value' => $val('linkedin'), 'maxlength' => 255, 'autocomplete' => 'off', 'placeholder' => 'https://www.linkedin.com/in/...', 'attrs' => ['inputmode' => 'url']]) ?>
                    <?= partial('partials/form-field', ['name' => 'x', 'label' => 'X / Twitter (optional)', 'value' => $val('x'), 'maxlength' => 255, 'autocomplete' => 'off', 'placeholder' => 'https://x.com/...', 'attrs' => ['inputmode' => 'url']]) ?>
                    <?= partial('partials/form-field', ['name' => 'bio', 'label' => 'Short biography (optional)', 'type' => 'textarea', 'rows' => 6, 'value' => $val('bio'), 'maxlength' => 2000, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Plain text. A few sentences are enough.']) ?>
                </div>
            </div>
        </section>

        <div class="admin-org__aside">
            <section class="card" aria-labelledby="team-photo">
                <div class="card__header"><h2 class="card__title" id="team-photo">Photo</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', [
                        'name'  => 'photo',
                        'label' => 'Profile photo',
                        'value' => (string) $val('photo'),
                        'help'  => 'Optional. Square or portrait photos work best (JPG, PNG or WebP, up to 5 MB). Only add photos of people who have agreed to appear on the website; without a photo the card shows the person\'s initials.',
                    ]) ?>
                </div>
            </section>
            <section class="card" aria-labelledby="team-visibility">
                <div class="card__header"><h2 class="card__title" id="team-visibility">Visibility</h2></div>
                <div class="card__body">
                    <input type="hidden" name="is_active" value="0">
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__text">Show on the website</span>
                    </label>
                    <p class="form-note">Switch off to keep the person in the list without showing them publicly.</p>
                </div>
            </section>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Add team member' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/team')) ?>">Cancel</a>
        <?php if ($isEdit && can('content.delete')): ?>
            <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
        <?php endif; ?>
    </div>
</form>
