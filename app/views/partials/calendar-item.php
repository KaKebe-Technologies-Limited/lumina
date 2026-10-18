<?php
/**
 * One calendar entry as a readable row (list view, mobile month, coming-up lists). The whole row is one link target (the title link is stretched).
 * Vars: $o* (occurrence), $summary (bool: a one-line summary of the description), $lead ('icon' = type icon chip, default; 'date' = day + month chip,
 *       for the coming-up lists, where the type shows in the meta line).
 * assets/js/calendar.js builds the same markup for the list after a month change: keep the two in step.
 */
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;
use App\Services\Calendar\IcsBuilder;

$p = $o['pair'];
$lead = ($lead ?? 'icon') === 'date' ? 'date' : 'icon';
$kind = !empty($o['is_holiday']) ? 'holiday' : (in_array($o['type_slug'], ['programme', 'event'], true) ? 'own' : 'obs');
$multi = $o['end_date'] !== $o['start_date'];
$time = CalendarService::timeLabel($o);
$when = $multi ? CalendarService::dateLabel($o) : ($time !== '' ? $time : (string) content('calendar', 'views.all_day'));
$text = !empty($summary) ? IcsBuilder::plainText((string) ($o['description'] ?? '')) : '';
if (mb_strlen($text) > 150) {
    $text = rtrim(mb_substr($text, 0, 149)) . "\u{2026}";
}
?>
<div class="cal-item cal-item--<?= $kind ?><?= $o['needs_confirmation'] ? ' cal-item--tbc' : '' ?>" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>;--cal-on:<?= e($p['on_solid']) ?>">
    <?php if ($lead === 'date'): ?>
        <span class="cal-item__date" aria-hidden="true"><span class="cal-item__day"><?= (int) substr($o['start_date'], 8, 2) ?></span><span class="cal-item__mon"><?= e(CivilDate::fmt($o['start_date'], 'M')) ?></span></span>
    <?php else: ?>
        <span class="cal-item__chip" aria-hidden="true"><?= icon($o['type_icon'], 'icon--sm') ?></span>
    <?php endif; ?>
    <div class="cal-item__body">
        <p class="cal-item__title"><a class="cal-item__link" href="<?= e($o['url']) ?>" data-cal-key="<?= e($o['key']) ?>" data-cal-date="<?= e($o['start_date']) ?>" data-cal-type="<?= e($o['type_slug']) ?>"><?= e($o['title']) ?></a></p>
        <p class="cal-item__meta">
            <?php if ($lead === 'date'): ?><span class="cal-item__type"><?= icon($o['type_icon'], 'icon--sm') ?><?= e($o['type_name']) ?></span><?php else: ?><span class="cal-item__type"><?= e($o['type_name']) ?></span><?php endif; ?>
            <?php if ($lead === 'date' && !$multi): ?><span><?= e(CivilDate::fmt($o['start_date'], 'D j M')) ?><?= $time !== '' ? ', ' . e($time) : '' ?></span><?php else: ?><span><?= e($when) ?></span><?php endif; ?>
            <?php if ($o['location'] !== ''): ?><span><?= e($o['location']) ?></span><?php endif; ?>
        </p>
        <?php if (!empty($o['is_holiday']) || $o['needs_confirmation'] || $o['moved']): ?>
            <p class="cal-item__flags">
                <?php if (!empty($o['is_holiday'])): ?><span class="badge badge--primary"><?= e(content('calendar', 'filters.holiday_badge')) ?></span><?php endif; ?>
                <?php if ($o['needs_confirmation']): ?><span class="badge badge--warning"><?= e(content('calendar', 'filters.tbc_badge')) ?></span><?php endif; ?>
                <?php if ($o['moved']): ?><span class="badge badge--info"><?= e(content('calendar', 'filters.moved_badge')) ?></span><?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if ($text !== ''): ?><p class="cal-item__summary"><?= e($text) ?></p><?php endif; ?>
    </div>
</div>
