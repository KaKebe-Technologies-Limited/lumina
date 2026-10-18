<?php
/**
 * One calendar entry as a small labelled pill: icon (shape) + text + colour, so colour is never the only cue.
 * Vars: $o* (occurrence, docs/CALENDAR.md), $showTime (bool: prefix the start time of a timed entry), $compact (bool: the Contact block: only the
 *       icon is drawn, the text stays for screen readers and in the tooltip).
 * Shapes: public holiday = filled pill; our own programme sessions and events = outlined pill; every other day = tinted pill with a thick left bar.
 * A date that is not confirmed yet gets a dashed outline AND the words "Date to be confirmed" (visible in the grid, spoken in the compact form).
 * Per-type colours arrive as --cal-solid / --cal-tint / --cal-text / --cal-on (ColorContrast::pair: the text on the tint is at least 4.5:1).
 */
$p = $o['pair'];
$compact = !empty($compact);
$kind = !empty($o['is_holiday']) ? 'holiday' : (in_array($o['type_slug'], ['programme', 'event'], true) ? 'own' : 'obs');
$cls = 'cal-pill cal-pill--' . $kind . (!empty($o['needs_confirmation']) ? ' cal-pill--tbc' : '') . (!empty($o['cancelled']) ? ' cal-pill--cancelled' : '');
$time = !empty($showTime) && empty($o['all_day']) && !empty($o['start_time']) ? $o['start_time'] : '';
?>
<a class="<?= e($cls) ?>" href="<?= e($o['url']) ?>" data-cal-key="<?= e($o['key']) ?>" data-cal-date="<?= e($o['start_date']) ?>" data-cal-type="<?= e($o['type_slug']) ?>" title="<?= e($o['title']) ?>"
   style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>;--cal-on:<?= e($p['on_solid']) ?>">
    <?= icon($o['type_icon'], 'icon--sm cal-pill__icon') ?>
    <span class="cal-pill__text"><?php if ($time !== ''): ?><span class="cal-pill__time"><?= e($time) ?></span> <?php endif; ?><?= e($o['title']) ?></span>
    <?php if (!empty($o['is_holiday'])): ?><span class="visually-hidden"> (<?= e(content('calendar', 'filters.holiday_badge')) ?>)</span><?php endif; ?>
    <?php if (!empty($o['needs_confirmation'])): ?>
        <?php if ($compact): ?><span class="visually-hidden"> (<?= e(content('calendar', 'filters.tbc_badge')) ?>)</span>
        <?php else: ?><span class="cal-pill__flag"><?= e(content('calendar', 'filters.tbc_badge')) ?></span><?php endif; ?>
    <?php endif; ?>
</a>
