<?php
/**
 * Admin > Membership > Membership card: THE DESIGNER. OWNER: CARD agent. Contract: docs/MEMBERSHIP_CARD.md.
 *
 * Layout: a tabbed form on the left (logo and colours, words, blocks and tiles, footer, member numbers, issue rules, e-mail) and the live preview on the right
 * (sticky from 1200 px; stacked above the form below that). Every tab is its own partial (_look, _words, _layout, _footer, _numbers, _rules, _email); the preview and the
 * test e-mail are _preview. Everything works without JavaScript except the live preview: the form is one ordinary POST, the tabs are anchor links.
 *
 * Vars: $values (resolved settings, or the old input after a failed save), $defs, $tabs, $tabKeys, $errorTabs, $themes, $tileOptions, $triggers, $contrast (colour pairs with
 * ratio and status), $contrastDefs, $types (id, type, code, next, active), $prefix, $numberDefault, $emailPlaceholders, $scenarios, $stats, $caps (png, ttf), $stale (the form shows
 * unsaved text), $logoIsDefault, $previewPdf, $previewPng, $adminEmail, $defaults.
 * Posts to admin/membership-card (multipart: the logo upload). The preview posts the same fields to admin/membership-card/preview and shows the real PDF.
 */
$this->start('scripts'); ?><script src="<?= e(asset('js/admin-membership-card.js')) ?>" defer></script><?php $this->end();

$f = require __DIR__ . '/_fields.php';
$errs = errors();
$active = array_key_first($errorTabs) ?? 'look';
$p = compact('values', 'defs', 'tabs', 'tabKeys', 'f', 'themes', 'tileOptions', 'triggers', 'contrast', 'types', 'prefix', 'numberDefault', 'emailPlaceholders', 'emailPreview', 'stats', 'caps', 'logoIsDefault', 'adminEmail', 'defaults');
$tabOf = static function (string $key) use ($tabKeys): string {
    foreach ($tabKeys as $t => $keys) {
        if (in_array($key, $keys, true)) {
            return $t;
        }
    }
    return 'look';
};
?>
<div class="admin-membership-card" data-mc-root>
    <?= partial('partials/admin/page-header', [
        'title'    => 'Membership card',
        'subtitle' => 'Design the card, choose when it is issued, and set how member numbers are built. Everything starts as the reference design: change what you need and watch the card change.',
        'actions'  => [['label' => 'Restore the reference design', 'url' => url('admin/membership-card', ['reset' => 'appearance']), 'method' => 'post', 'class' => 'btn--outline', 'icon' => 'refresh',
            'confirm' => 'Put the whole look of the card (logo, colours, words, blocks and footer) back to the reference design? Your member numbers, issue rules and e-mail wording stay as they are, and cards already issued are not touched.']],
    ]) ?>

    <?php if ($errs !== []): ?>
        <div class="alert alert--error" role="alert" tabindex="-1" data-mc-errors>
            <?= icon('alert-circle') ?>
            <div class="alert__body">
                <strong class="alert__title">Nothing was saved. Please check these <?= count($errs) === 1 ? 'setting' : count($errs) . ' settings' ?>:</strong>
                <ul class="mc-errorlist">
                    <?php foreach ($errs as $k => $msg): ?>
                        <li><a href="#f-<?= e((string) $k) ?>" data-mc-goto><?= e($tabs[$tabOf((string) $k)]['label'] ?? '') ?> &rsaquo; <?= e($f['label']((string) $k)) ?></a>: <?= e((string) $msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="mc-layout">
        <div class="mc-main">
            <form method="post" action="<?= e(url('admin/membership-card')) ?>" enctype="multipart/form-data" class="mc-form" id="mc-form" novalidate data-unsaved-guard data-mc-form
                  data-preview-url="<?= e(url('admin/membership-card/preview')) ?>" data-stale="<?= $stale ? '1' : '0' ?>" data-active-tab="<?= e((string) $active) ?>"
                  data-mc-contrast="<?= e((string) json_encode($contrastDefs, JSON_UNESCAPED_SLASHES)) ?>"
                  data-mc-prefix="<?= e($prefix) ?>" data-mc-types="<?= e((string) json_encode(array_map(static fn(array $t): array => ['id' => $t['id'], 'name' => (string) $t['type']['name'], 'code' => $t['code']], $types), JSON_UNESCAPED_UNICODE)) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="tab" value="<?= e((string) $active) ?>" data-mc-tab-input>

                <div class="mc-tabbar">
                    <div class="tabs mc-tabs" role="tablist" aria-label="Parts of the card" data-mc-tabs>
                        <?php foreach ($tabs as $key => $t): ?>
                            <a class="tabs__item mc-tab<?= $key === $active ? ' is-active' : '' ?>" role="tab" id="mc-tab-<?= e($key) ?>" href="#tab-<?= e($key) ?>" aria-controls="tab-<?= e($key) ?>"
                               aria-selected="<?= $key === $active ? 'true' : 'false' ?>" data-mc-tab="<?= e($key) ?>">
                                <?= icon($t['icon'], 'icon--sm') ?> <span class="mc-tab__label"><?= e($t['label']) ?></span>
                                <?php if (isset($errorTabs[$key])): ?><span class="mc-tab__flag mc-tab__flag--error" title="This tab has a setting that needs attention"><?= icon('alert-circle', 'icon--sm') ?><span class="visually-hidden">needs attention</span></span><?php endif; ?>
                                <span class="mc-tab__flag mc-tab__flag--dirty" data-mc-dirty-flag hidden title="Changed, not saved yet"><span class="visually-hidden">changed, not saved yet</span></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?= partial('admin/membership-card/_look', $p + ['themes' => $themes, 'tab' => $tabs['look']]) ?>
                <?= partial('admin/membership-card/_words', $p + ['tab' => $tabs['words']]) ?>
                <?= partial('admin/membership-card/_layout', $p + ['tab' => $tabs['layout']]) ?>
                <?= partial('admin/membership-card/_footer', $p + ['tab' => $tabs['footer']]) ?>
                <?= partial('admin/membership-card/_numbers', $p + ['tab' => $tabs['numbers']]) ?>
                <?= partial('admin/membership-card/_rules', $p + ['tab' => $tabs['rules']]) ?>
                <?= partial('admin/membership-card/_email', $p + ['tab' => $tabs['email']]) ?>

                <div class="form-actions mc-actions">
                    <button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save the membership card settings</button>
                    <span class="mc-actions__state" role="status" aria-live="polite" data-mc-dirty-note></span>
                </div>
            </form>

            <?php /* the reset buttons live in their own form (they carry form="mc-reset"), so pressing Enter in a field can never trigger one */ ?>
            <form method="post" action="<?= e(url('admin/membership-card')) ?>" id="mc-reset" data-mc-reset>
                <?= csrf_field() ?>
                <input type="hidden" name="tab" value="<?= e((string) $active) ?>" data-mc-tab-input>
            </form>
        </div>

        <?= partial('admin/membership-card/_preview', $p + ['scenarios' => $scenarios, 'previewPdf' => $previewPdf, 'previewPng' => $previewPng]) ?>
    </div>
</div>
