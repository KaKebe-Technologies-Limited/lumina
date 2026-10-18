<?php
/**
 * One entry as a small coloured pill in the month grid. Var: $o (an occurrence). The colour pair comes from ColorContrast (AA); the shape (icon),
 * the title and a short word for anything unusual carry the meaning too, so colour is never the only cue.
 */
$p = $o['pair'];
$t = \App\Services\Calendar\CalendarService::typeBySlug((string) ($o['type_slug'] ?? ''));
$teamOnly = empty($o['is_public']) || ($t !== null && (!$t['is_active'] || !$t['show_on_public']));
$edit = !empty($o['entry_id']) ? url('admin/calendar/' . (int) $o['entry_id'] . '/edit') : url('admin/events/' . (int) $o['event_id'] . '/edit');
$flag = !empty($o['cancelled']) ? 'Cancelled' : (empty($o['is_active']) ? 'Hidden' : ($teamOnly ? 'Team only' : (!empty($o['needs_confirmation']) ? 'To confirm' : (!empty($o['moved']) ? 'Moved' : ''))));
$time = !empty($o['all_day']) ? '' : (string) $o['start_time'];
$tip = $o['type_name'] . ': ' . $o['title'] . ($time !== '' ? ' at ' . $time : '') . ($flag !== '' ? ' (' . $flag . ')' : '');
?>
<a class="cal-pill<?= !empty($o['cancelled']) ? ' is-cancelled' : '' ?><?= empty($o['is_active']) || $teamOnly ? ' is-private' : '' ?><?= !empty($o['is_holiday']) ? ' is-holiday' : '' ?>"
   style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>" href="<?= e($edit) ?>" title="<?= e($tip) ?>">
    <?= icon((string) $o['type_icon'], 'icon--sm') ?>
    <span class="cal-pill__text"><?= $time !== '' ? '<span class="cal-pill__time">' . e($time) . '</span> ' : '' ?><?= e($o['title']) ?></span>
    <?php if ($flag !== ''): ?><span class="cal-pill__flag"><?= e($flag) ?></span><?php endif; ?>
</a>
