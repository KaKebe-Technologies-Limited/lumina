<?php
/**
 * Site settings, generated from app/config/settings_schema.php. OWNER: B3.
 * Vars: $groups (schema groups), $values (resolved values), $custom (key => bool: differs from the default), $checks (system check rows).
 * One form; the groups are shown as tabs by assets/js/pages/admin-pages.js (without JavaScript every group is simply listed in turn).
 */
$errorGroups = [];
foreach ($groups as $gk => $g) {
    foreach (array_keys((array) ($g['fields'] ?? [])) as $fk) {
        if (has_error((string) $fk)) {
            $errorGroups[$gk] = true;
        }
    }
}
$previewDefault = static function (array $def): string {
    $d = (string) ($def['default'] ?? '');
    if (($def['type'] ?? '') === 'image') {
        return $d === '' ? 'no image' : basename($d);
    }
    return $d === '' ? 'empty' : excerpt($d, 60);
};
?>
<div class="admin-pages-editor admin-settings">
<?= partial('partials/admin/page-header', [
    'title'    => 'Site settings',
    'subtitle' => 'Details that belong to the whole organisation: contact information, social media, donation details and branding. Page wording is edited under Pages.',
]) ?>

<form method="post" action="<?= e(url('admin/settings')) ?>" enctype="multipart/form-data" data-unsaved-guard data-settings-form novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="tab" value="" data-settings-tab-input>
    <div class="tabs" role="tablist" aria-label="Settings groups" data-settings-tabs>
        <?php $first = true; foreach ($groups as $gk => $g): ?>
            <a class="tabs__item<?= $first ? ' is-active' : '' ?>" role="tab" id="tabbtn-<?= e($gk) ?>" href="#tab-<?= e($gk) ?>" aria-controls="tab-<?= e($gk) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>" data-settings-tab="<?= e($gk) ?>">
                <?= icon((string) ($g['icon'] ?? 'settings'), 'icon--sm') ?> <?= e($g['label'] ?? $gk) ?><?php if (isset($errorGroups[$gk])): ?> <span class="pe-tab-error" title="This tab has fields that need attention"><?= icon('alert-circle', 'icon--sm') ?><span class="visually-hidden">needs attention</span></span><?php endif; ?>
            </a>
        <?php $first = false; endforeach; ?>
    </div>

    <?php $first = true; foreach ($groups as $gk => $g): ?>
        <section class="card pe-panel" id="tab-<?= e($gk) ?>" role="tabpanel" aria-labelledby="tabbtn-<?= e($gk) ?>" data-settings-panel="<?= e($gk) ?>"<?= $first ? '' : '' ?>>
            <div class="card__header"><div><h2 class="card__title"><?= e($g['label'] ?? $gk) ?></h2><?php if (!empty($g['help'])): ?><p class="pe-section__help"><?= e($g['help']) ?></p><?php endif; ?></div></div>
            <div class="card__body">
                <div class="form-grid">
                    <?php foreach ((array) ($g['fields'] ?? []) as $key => $def):
                        $type = (string) ($def['type'] ?? 'text');
                        $cur = old((string) $key, $values[$key] ?? '');
                        $wide = in_array($type, ['textarea', 'image'], true);
                        $rules = (string) ($def['rules'] ?? '');
                        $required = str_contains($rules, 'required');
                        ?>
                        <div class="pe-setting<?= $wide ? ' field--full' : '' ?>">
                            <?php if ($type === 'image'): ?>
                                <?= partial('partials/admin/image-field', ['name' => (string) $key, 'label' => (string) ($def['label'] ?? $key), 'value' => (string) $cur, 'help' => $def['help'] ?? null, 'id' => 'f-' . $key]) ?>
                            <?php elseif ($type === 'boolean'): ?>
                                <?= partial('partials/form-field', ['name' => (string) $key, 'label' => (string) ($def['label'] ?? $key), 'type' => 'checkbox', 'value' => (string) $cur === '1', 'help' => $def['help'] ?? null]) ?>
                            <?php elseif ($type === 'select'): ?>
                                <?= partial('partials/form-field', ['name' => (string) $key, 'label' => (string) ($def['label'] ?? $key), 'type' => 'select', 'value' => (string) $cur, 'options' => (array) ($def['options'] ?? []), 'help' => $def['help'] ?? null]) ?>
                            <?php else: ?>
                                <?= partial('partials/form-field', [
                                    'name' => (string) $key, 'label' => (string) ($def['label'] ?? $key),
                                    'type' => $type === 'textarea' ? 'textarea' : (in_array($type, ['email', 'url', 'tel', 'number'], true) ? $type : 'text'),
                                    'value' => (string) $cur, 'help' => $def['help'] ?? null, 'placeholder' => $def['placeholder'] ?? null,
                                    'required' => $required, 'rows' => 4, 'autocomplete' => 'off',
                                    'attrs' => $type === 'url' ? ['inputmode' => 'url', 'spellcheck' => 'false'] : ($type === 'email' ? ['inputmode' => 'email'] : []),
                                ]) ?>
                            <?php endif; ?>
                            <?php if (!empty($custom[$key])): ?>
                                <label class="check pe-setting__reset"><input type="checkbox" name="reset[<?= e((string) $key) ?>]" value="1"><span>Go back to the original (<?= e($previewDefault((array) $def)) ?>)</span></label>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php $first = false; endforeach; ?>

    <section class="card pe-panel pe-system" aria-labelledby="sys-title">
        <div class="card__header"><div><h2 class="card__title" id="sys-title">System check</h2><p class="pe-section__help">Read-only information about this installation. Ask your developer if something here is marked as a problem.</p></div></div>
        <ul class="pe-checks">
            <?php foreach ($checks as $c): ?>
                <li class="pe-check pe-check--<?= $c['ok'] === true ? 'ok' : ($c['ok'] === false ? 'bad' : 'info') ?>">
                    <span class="pe-check__icon"><?= icon($c['ok'] === true ? 'check-circle' : ($c['ok'] === false ? 'alert-triangle' : 'info'), 'icon--sm') ?></span>
                    <span class="pe-check__text"><strong><?= e($c['label']) ?></strong><span><?= e($c['detail']) ?></span></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <div class="form-actions pe-actions">
        <span class="pe-actions__state" data-pe-state role="status" aria-live="polite">No unsaved changes</span>
        <span class="pe-actions__buttons"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save settings</button></span>
    </div>
</form>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
