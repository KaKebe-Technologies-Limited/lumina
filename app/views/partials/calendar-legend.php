<?php
/**
 * Key + type filter chips. Vars: $types* (CalendarService::publicTypes()), $selected (NULL = every chip ticked, else the list of ticked slugs),
 * $form (bool: real checkboxes in the GET form of the page, which works without JavaScript; default false = a plain key), $title (heading text),
 * $label (accessible name of the group).
 * Every type shows icon + name, so colour is never the only cue; a ticked chip is filled and carries a tick, an unticked one is dashed.
 */
$selected = isset($selected) && is_array($selected) ? array_map('strval', $selected) : null;
$asForm = !empty($form);
$groupId = 'cal-legend-' . substr(md5((string) ($title ?? '') . (string) $asForm), 0, 6);
?>
<div class="cal-legend" data-cal-legend<?= $asForm ? ' role="group"' . (!empty($title) ? ' aria-labelledby="' . e($groupId) . '"' : ' aria-label="' . e($label ?? content('calendar', 'filters.group_label')) . '"') : '' ?>>
    <?php if (!empty($title)): ?><p class="cal-legend__title" id="<?= e($groupId) ?>"><?= e($title) ?></p><?php endif; ?>
    <ul class="cal-legend__list"<?= !$asForm ? (!empty($title) ? ' aria-labelledby="' . e($groupId) . '"' : ' aria-label="' . e($label ?? content('calendar', 'filters.group_label')) . '"') : '' ?>>
        <?php foreach ($types as $t):
            $pp = $t['pair'];
            $on = $selected === null || in_array($t['slug'], $selected, true);
            $kind = $t['slug'] === \App\Services\Calendar\CalendarService::SLUG_HOLIDAY ? 'holiday' : (in_array($t['slug'], ['programme', 'event'], true) ? 'own' : 'obs'); ?>
            <li class="cal-legend__item cal-legend__item--<?= $kind ?>" data-type="<?= e($t['slug']) ?>" style="--cal-solid:<?= e($pp['solid']) ?>;--cal-tint:<?= e($pp['tint']) ?>;--cal-text:<?= e($pp['text']) ?>;--cal-on:<?= e($pp['on_solid']) ?>">
                <?php if ($asForm): ?>
                    <label class="cal-chip">
                        <input type="checkbox" name="types[]" value="<?= e($t['slug']) ?>" data-cal-type-toggle<?= $on ? ' checked' : '' ?>>
                        <span class="cal-chip__box"><?= icon($t['icon'], 'icon--sm cal-chip__icon') ?><span class="cal-chip__name"><?= e($t['name']) ?></span><?= icon('check', 'icon--sm cal-chip__tick') ?></span>
                    </label>
                <?php else: ?>
                    <span class="cal-chip cal-chip--static"><span class="cal-chip__box"><?= icon($t['icon'], 'icon--sm cal-chip__icon') ?><span class="cal-chip__name"><?= e($t['name']) ?></span></span></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
