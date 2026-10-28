<?php
/**
 * Event form (create + edit). OWNER: B8.
 * Vars: $event (row or defaults), $action (POST url), $isEdit (bool), $stats (registration counts, edit only).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$val = static fn(string $key, mixed $default = '') => old($key, $event[$key] ?? $default);
$dt = static function (string $key) use ($event): string {
    $posted = old($key, null);
    if ($posted !== null) {
        return (string) $posted;
    }
    $v = $event[$key] ?? '';
    return $v ? date('Y-m-d\TH:i', (int) strtotime((string) $v)) : '';
};
$isActive = (int) old('is_active', $event['is_active'] ?? 1) === 1;
$regOn = (int) old('registration_enabled', $event['registration_enabled'] ?? 0) === 1;
$tz = date_default_timezone_get();
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate data-unsaved-guard>
    <?= csrf_field() ?>
    <div class="card-grid card-grid--main-aside">
        <div>
            <section class="card" aria-labelledby="ev-details">
                <div class="card__header">
                    <h2 class="card__title" id="ev-details">Event details</h2>
                    <span class="required-note"><span aria-hidden="true">*</span> required</span>
                </div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Event title', 'required' => true, 'value' => $val('title'), 'maxlength' => 220, 'class' => 'field--full', 'autocomplete' => 'off']) ?>
                        <?= partial('partials/form-field', ['name' => 'slug', 'label' => 'Web address', 'value' => $val('slug'), 'maxlength' => 190, 'class' => 'field--full', 'autocomplete' => 'off', 'attrs' => ['data-slug-source' => '#f-title'], 'help' => 'Filled in from the title. The event page will be at /events/your-web-address. Leave as is unless you need to change it.']) ?>
                        <?= partial('partials/form-field', ['name' => 'summary', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'value' => $val('summary'), 'maxlength' => 500, 'class' => 'field--full', 'attrs' => ['data-maxlength-counter' => true], 'help' => 'One or two sentences shown on the events list.']) ?>
                        <div class="field--full">
                            <?= partial('partials/admin/richtext-field', ['name' => 'body', 'label' => 'Full description', 'value' => (string) $val('body'), 'rows' => 12, 'help' => 'Programme, what to bring, who it is for. Shown on the event page.']) ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="ev-when">
                <div class="card__header"><h2 class="card__title" id="ev-when">When and where</h2></div>
                <div class="card__body">
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'start_at', 'label' => 'Starts', 'type' => 'datetime-local', 'required' => true, 'value' => $dt('start_at'), 'help' => 'Times use the website timezone (' . $tz . ').']) ?>
                        <?= partial('partials/form-field', ['name' => 'end_at', 'label' => 'Ends (optional)', 'type' => 'datetime-local', 'value' => $dt('end_at'), 'attrs' => ['data-end-after' => '#f-start_at'], 'help' => 'Leave empty for events without a fixed end.']) ?>
                        <?= partial('partials/form-field', ['name' => 'location', 'label' => 'Location', 'value' => $val('location'), 'maxlength' => 255, 'class' => 'field--full', 'placeholder' => 'Venue and town, or "Online"']) ?>
                    </div>
                </div>
            </section>

            <section class="card" aria-labelledby="ev-reg">
                <div class="card__header"><h2 class="card__title" id="ev-reg">Registration</h2></div>
                <div class="card__body">
                    <div>
                        <input type="hidden" name="registration_enabled" value="0">
                        <label class="switch">
                            <input type="checkbox" name="registration_enabled" value="1"<?= $regOn ? ' checked' : '' ?>>
                            <span class="switch__track" aria-hidden="true"></span>
                            <span class="switch__text">Accept registrations on the website</span>
                        </label>
                        <p class="form-note">When on, visitors can sign up from the event page. You manage the sign-ups under Registrations.</p>
                    </div>
                    <div id="reg-options" class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'capacity', 'label' => 'Capacity (optional)', 'type' => 'number', 'value' => $val('capacity'), 'min' => 1, 'step' => 1, 'help' => 'Maximum number of people (registered + attended). Leave empty for unlimited places.']) ?>
                        <?= partial('partials/form-field', ['name' => 'registration_url', 'label' => 'External sign-up link (optional)', 'value' => $val('registration_url'), 'maxlength' => 500, 'autocomplete' => 'off', 'placeholder' => 'https://...', 'attrs' => ['inputmode' => 'url'], 'help' => 'Only if sign-ups happen on another website, for example an online form.']) ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="admin-org__aside">
            <section class="card" aria-labelledby="ev-visibility">
                <div class="card__header"><h2 class="card__title" id="ev-visibility">Visibility</h2></div>
                <div class="card__body">
                    <input type="hidden" name="is_active" value="0">
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__text">Show on the website</span>
                    </label>
                    <p class="form-note">Switch off to keep the event as a draft.</p>
                </div>
            </section>

            <?php if ($isEdit && isset($stats)): ?>
                <section class="card" aria-labelledby="ev-regstats">
                    <div class="card__header"><h2 class="card__title" id="ev-regstats">Registrations</h2></div>
                    <div class="card__body">
                        <p class="org-figure"><span class="org-figure__value"><?= (int) $stats['active'] ?></span> <span class="text-muted"><?= $event['capacity'] !== null ? 'of ' . (int) $event['capacity'] . ' places taken' : 'registered' ?></span></p>
                        <p class="form-note"><?= (int) $stats['registered'] ?> registered &middot; <?= (int) $stats['attended'] ?> attended &middot; <?= (int) $stats['cancelled'] ?> cancelled</p>
                        <?php if (can('inbox.manage')): ?>
                            <div class="btn-row">
                                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/events/' . (int) $event['id'] . '/registrations')) ?>"><?= icon('users', 'icon--sm') ?> Manage registrations</a>
                                <?php if (can('export.csv') && $stats['total'] > 0): ?>
                                    <?= partial('partials/admin/export-menu', ['url' => url('admin/events/' . (int) $event['id'] . '/registrations/export'), 'label' => 'Export registrations', 'class' => 'btn--ghost', 'small' => true]) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="card" aria-labelledby="ev-image">
                <div class="card__header"><h2 class="card__title" id="ev-image">Image</h2></div>
                <div class="card__body">
                    <?= partial('partials/admin/image-field', [
                        'name'      => 'image',
                        'label'     => 'Event image',
                        'value'     => (string) $val('image'),
                        'alt_name'  => 'image_alt',
                        'alt_value' => (string) $val('image_alt'),
                        'help'      => 'Optional (JPG, PNG or WebP, up to 5 MB). The description is required when an image is set: say what is visible, for people who cannot see the picture.',
                    ]) ?>
                </div>
            </section>

            <details class="card admin-org__details">
                <summary class="card__header"><span class="card__title">Search engine details (optional)</span></summary>
                <div class="card__body">
                    <?= partial('partials/form-field', ['name' => 'seo_title', 'label' => 'SEO title', 'value' => $val('seo_title'), 'maxlength' => 190, 'help' => 'Defaults to the event title.']) ?>
                    <?= partial('partials/form-field', ['name' => 'seo_description', 'label' => 'SEO description', 'type' => 'textarea', 'rows' => 3, 'value' => $val('seo_description'), 'maxlength' => 300, 'attrs' => ['data-maxlength-counter' => true], 'help' => 'Defaults to the short summary.']) ?>
                </div>
            </details>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> <?= $isEdit ? 'Save changes' : 'Create event' ?></button>
        <a class="btn btn--ghost" href="<?= e(url('admin/events')) ?>">Cancel</a>
        <?php if ($isEdit && can('content.delete')): ?>
            <button class="btn btn--ghost org-danger org-push-end" type="submit" form="delete-form"><?= icon('trash', 'icon--sm') ?> Delete</button>
        <?php endif; ?>
    </div>
</form>
