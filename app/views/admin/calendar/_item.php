<?php
/**
 * One occurrence as a row (day panel and the phone agenda). Var: $o. Shows the type (shape + name), the time, the badges and the Edit button.
 */
use App\Services\Calendar\CalendarService;

$p = $o['pair'];
$isEntry = !empty($o['entry_id']);
$edit = $isEntry ? url('admin/calendar/' . (int) $o['entry_id'] . '/edit') : url('admin/events/' . (int) $o['event_id'] . '/edit');
$when = CalendarService::timeLabel($o);
?>
<li class="cal-item<?= !empty($o['cancelled']) ? ' is-cancelled' : '' ?>" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>">
    <span class="cal-item__type"><?= icon((string) $o['type_icon'], 'icon--sm') ?><span><?= e($o['type_name']) ?></span></span>
    <div class="cal-item__main">
        <a class="cal-item__title" href="<?= e($edit) ?>"><?= e($o['title']) ?></a>
        <span class="cal-item__meta">
            <?= $when !== '' ? e($when) : 'All day' ?><?= $o['start_date'] !== $o['end_date'] ? ' &middot; until ' . e(\App\Services\Calendar\CivilDate::fmt($o['end_date'], 'D j M')) : '' ?><?= $o['location'] !== '' ? ' &middot; ' . e($o['location']) : '' ?>
        </span>
        <span class="cal-item__badges"><?= partial('admin/calendar/_badges', ['o' => $o]) ?></span>
        <?php if (!empty($o['note'])): ?><span class="cal-item__note">Note: <?= e($o['note']) ?></span><?php endif; ?>
    </div>
    <a class="btn btn--outline btn--sm" href="<?= e($edit) ?>"><?= icon('edit', 'icon--sm') ?> <?= $isEntry ? 'Edit' : 'Open event' ?></a>
</li>
