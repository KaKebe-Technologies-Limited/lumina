<?php
/**
 * One type: the edit card (or the "Add a type" card when $t is null). The readability check below the colour is computed here (ColorContrast) and
 * kept live by admin-calendar.js with the same maths. Vars: $t (type with 'pair', or null), $icons, $used (entries using it), $core (core slugs),
 * $nextOrder (new type), $failed (this card was the one that was submitted).
 */
use App\Services\Calendar\ColorContrast;

$isNew = $t === null;
$id = $isNew ? 0 : (int) $t['id'];
$fid = 'type-' . ($isNew ? 'new' : $id);
$pick = static fn(string $k, mixed $d): mixed => $failed ? old($k, $d) : $d;
$name = (string) $pick('name', $isNew ? '' : (string) $t['name']);
$color = (string) $pick('color', $isNew ? '#3b6ea5' : (string) $t['color']);
$icon = (string) $pick('icon', $isNew ? 'tag' : (string) $t['icon']);
$sort = (string) $pick('sort_order', $isNew ? (string) $nextOrder : (string) $t['sort_order']);
$active = (string) $pick('is_active', $isNew ? '1' : (string) (int) $t['is_active']) !== '0';
$public = (string) $pick('show_on_public', $isNew ? '1' : (string) (int) $t['show_on_public']) !== '0';
$pair = ColorContrast::pair($color);
$marker = ColorContrast::ratio($pair['solid'], '#ffffff');
$isCore = !$isNew && (in_array($t['slug'], $core, true) || $t['is_system']);
$canDelete = !$isNew && !$isCore && $used === 0;
$err = static fn(string $k): ?string => $failed ? error($k) : null;
$verdict = static fn(float $r, float $need): array => $r >= $need ? ['badge--success', 'Good'] : ['badge--warning', 'Too low'];
[$bs, $ls] = $verdict((float) $pair['ratio_solid'], 4.5);
[$bt, $lt] = $verdict((float) $pair['ratio_text'], 4.5);
[$bm, $lm] = $verdict((float) $marker, 3.0);
?>
<article class="card cal-type<?= $isNew ? ' cal-type--new' : '' ?><?= !$isNew && !$t['is_active'] ? ' is-off' : '' ?>" data-cal-type id="<?= e($fid) ?>"
         style="--cal-solid:<?= e($pair['solid']) ?>;--cal-tint:<?= e($pair['tint']) ?>;--cal-text:<?= e($pair['text']) ?>;--cal-on:<?= e($pair['on_solid']) ?>">
    <div class="card__header">
        <h2 class="card__title cal-type__title">
            <span class="cal-typechip cal-typechip--lg" data-cal-chip><span data-cal-chip-icon><?= icon($icon, 'icon--sm') ?></span><span data-cal-chip-name><?= e($name !== '' ? $name : 'New type') ?></span></span>
        </h2>
        <span class="cal-type__meta">
            <?php if ($isNew): ?>Add your own, for example "Training" or "Fundraiser".
            <?php else: ?>
                <code title="Short code used in the settings and in links"><?= e($t['slug']) ?></code>
                <?= $isCore ? '<span class="badge badge--outline">Part of the calendar</span>' : '' ?>
                <?= $used > 0 ? '<span class="badge badge--neutral">' . $used . ($used === 1 ? ' entry' : ' entries') . '</span>' : ($t['slug'] === 'event' ? '<span class="badge badge--neutral">From Events</span>' : '<span class="badge badge--neutral">Not used yet</span>') ?>
            <?php endif; ?>
        </span>
    </div>
    <form id="form-<?= e($fid) ?>" method="post" action="<?= e($isNew ? url('admin/calendar/types') : url('admin/calendar/types/' . $id)) ?>" class="card__body cal-type__form" data-unsaved-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="type_form" value="<?= $isNew ? 'new' : $id ?>">
        <?php if ($failed && error('_') !== null): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e((string) error('_')) ?></div></div><?php endif; ?>
        <div class="cal-type__grid">
            <div class="field<?= $err('name') ? ' field--error' : '' ?>">
                <label class="field__label" for="<?= e($fid) ?>-name">Name</label>
                <input class="input" id="<?= e($fid) ?>-name" type="text" name="name" value="<?= e($name) ?>" maxlength="80" required data-cal-name>
                <?php if ($err('name')): ?><div class="field__error" role="alert"><?= e((string) $err('name')) ?></div><?php endif; ?>
            </div>
            <div class="field<?= $err('color') ? ' field--error' : '' ?>">
                <label class="field__label" for="<?= e($fid) ?>-color">Colour</label>
                <div class="cal-colour">
                    <input class="cal-colour__pick" type="color" value="<?= e($pair['solid']) ?>" data-cal-color-pick aria-label="Pick a colour for <?= e($name !== '' ? $name : 'the new type') ?>">
                    <input class="input input--code" id="<?= e($fid) ?>-color" type="text" name="color" value="<?= e($color) ?>" maxlength="7" required pattern="#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?" autocomplete="off" data-cal-color>
                </div>
                <div class="field__help">Click the square to pick, or type a code such as #2f6f7a.</div>
                <?php if ($err('color')): ?><div class="field__error" role="alert"><?= e((string) $err('color')) ?></div><?php endif; ?>
            </div>
            <div class="field<?= $err('icon') ? ' field--error' : '' ?>">
                <label class="field__label" for="<?= e($fid) ?>-icon">Shape (icon)</label>
                <select class="select" id="<?= e($fid) ?>-icon" name="icon" data-cal-icon>
                    <?php foreach ($icons as $key => $label): ?><option value="<?= e($key) ?>"<?= $key === $icon ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
                <div class="field__help">The shape shows next to the name, so people who cannot tell colours apart still know the type.</div>
                <?php if ($err('icon')): ?><div class="field__error" role="alert"><?= e((string) $err('icon')) ?></div><?php endif; ?>
            </div>
            <div class="field<?= $err('sort_order') ? ' field--error' : '' ?>">
                <label class="field__label" for="<?= e($fid) ?>-sort">Order</label>
                <input class="input cal-narrow" id="<?= e($fid) ?>-sort" type="number" name="sort_order" value="<?= e($sort) ?>" step="1" min="-9999" max="99999" required>
                <div class="field__help">Smaller numbers come first.</div>
                <?php if ($err('sort_order')): ?><div class="field__error" role="alert"><?= e((string) $err('sort_order')) ?></div><?php endif; ?>
            </div>
        </div>

        <div class="cal-type__switches">
            <input type="hidden" name="is_active" value="0">
            <label class="check"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>><span>In use <small>(untick to stop offering it when adding dates)</small></span></label>
            <input type="hidden" name="show_on_public" value="0">
            <label class="check"><input type="checkbox" name="show_on_public" value="1"<?= $public ? ' checked' : '' ?>><span>Show to website visitors <small>(untick for a team-only type)</small></span></label>
        </div>

        <div class="cal-contrast" data-cal-contrast aria-live="polite">
            <h3 class="cal-contrast__title">Readability check</h3>
            <ul class="cal-contrast__list">
                <li data-check="solid">
                    <span class="cal-sample cal-sample--solid" data-cal-sample="solid">Aa</span>
                    <span class="cal-contrast__what">Text on the solid colour</span>
                    <strong data-cal-ratio><?= e(number_format($pair['ratio_solid'], 2)) ?>:1</strong>
                    <span class="badge <?= $bs ?>" data-cal-verdict><?= $ls ?></span>
                </li>
                <li data-check="text">
                    <span class="cal-sample cal-sample--tint" data-cal-sample="text">Aa</span>
                    <span class="cal-contrast__what">Label text on its light background</span>
                    <strong data-cal-ratio><?= e(number_format($pair['ratio_text'], 2)) ?>:1</strong>
                    <span class="badge <?= $bt ?>" data-cal-verdict><?= $lt ?></span>
                </li>
                <li data-check="marker">
                    <span class="cal-sample cal-sample--marker" data-cal-sample="marker"></span>
                    <span class="cal-contrast__what">The colour itself against white (dots, borders)</span>
                    <strong data-cal-ratio><?= e(number_format($marker, 2)) ?>:1</strong>
                    <span class="badge <?= $bm ?>" data-cal-verdict><?= $lm ?></span>
                </li>
            </ul>
            <p class="cal-contrast__msg" data-cal-contrast-msg>
                <?= $marker >= 3 ? 'The calendar picks readable text colours for you. This colour is fine.' : 'This colour is very light: its dots and borders may be hard to see on a white page. Pick a darker colour.' ?>
            </p>
        </div>
    </form>
    <div class="card__footer">
        <button class="btn btn--primary" type="submit" form="form-<?= e($fid) ?>"><?= icon($isNew ? 'plus' : 'check', 'icon--sm') ?> <?= $isNew ? 'Add this type' : 'Save' ?></button>
        <?php if ($canDelete): ?>
            <form method="post" action="<?= e(url('admin/calendar/types/' . $id . '/delete')) ?>" class="inline-form" data-confirm="Delete the type &quot;<?= e($name) ?>&quot;? This cannot be undone."><?= csrf_field() ?>
                <button class="btn btn--ghost" type="submit"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
        <?php elseif (!$isNew): ?>
            <span class="text-muted cal-type__why"><?= $isCore ? 'Part of the calendar: it cannot be deleted. Untick "In use" to stop offering it.' : 'Entries still use this type. Move them to another type first, or untick "In use".' ?></span>
        <?php endif; ?>
    </div>
</article>
