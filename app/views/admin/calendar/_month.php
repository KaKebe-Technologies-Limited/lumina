<?php
/**
 * Month view of EVERYTHING (also hidden and cancelled entries), server-rendered so it works without JavaScript.
 * Click the "+" of a day (or an empty part of it) to add a date on that day; click the day number (or "+N more") to see the whole day.
 * Phones (< 768 px): the grid is replaced by an agenda list of the days that have something (no horizontal scrolling).
 * Vars: $grid (CalendarService::monthGrid, public=false), $typeFilter, $selectedDay, $selectedItems, $agenda, $years, $types, $today.
 */
use App\Services\Calendar\CivilDate;

$m = $grid['month'];
$dayUrl = static fn(string $d): string => url('admin/calendar', ['month' => $m, 'type' => $typeFilter, 'day' => $d]) . '#day-panel';
$addUrl = static fn(string $d): string => url('admin/calendar', ['month' => $m, 'type' => $typeFilter, 'date' => $d]) . '#quick-add';
$maxPills = 3;
$monthNames = CivilDate::MONTHS;
?>
<div class="cal-toolbar">
    <div class="cal-toolbar__nav">
        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar', ['month' => $grid['prev'], 'type' => $typeFilter])) ?>" rel="prev"><?= icon('chevron-left', 'icon--sm') ?> Previous</a>
        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar', ['month' => substr($today, 0, 7), 'type' => $typeFilter])) ?>"<?= $grid['is_current'] ? ' aria-current="date"' : '' ?>>This month</a>
        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar', ['month' => $grid['next'], 'type' => $typeFilter])) ?>" rel="next">Next <?= icon('chevron-right', 'icon--sm') ?></a>
    </div>
    <h2 class="cal-toolbar__title" id="cal-title"><?= e($grid['label']) ?></h2>
    <form class="cal-toolbar__jump" method="get" action="<?= e(url('admin/calendar')) ?>" aria-label="Go to another month">
        <div class="field"><label class="field__label" for="j-m">Month</label>
            <select class="select" id="j-m" name="m"><?php foreach ($monthNames as $i => $name): ?><option value="<?= (int) $i ?>"<?= $i === $grid['month_number'] ? ' selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label class="field__label" for="j-y">Year</label>
            <select class="select" id="j-y" name="y"><?php foreach ($years as $y): ?><option value="<?= (int) $y ?>"<?= $y === $grid['year'] ? ' selected' : '' ?>><?= (int) $y ?></option><?php endforeach; ?></select></div>
        <div class="field"><label class="field__label" for="j-t">Show</label>
            <select class="select" id="j-t" name="type"><option value="">All types</option><?php foreach ($types as $t): ?><option value="<?= e($t['slug']) ?>"<?= $typeFilter === $t['slug'] ? ' selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn--primary btn--sm" type="submit">Go</button>
    </form>
</div>

<div class="cal-summary" role="status">
    <?php $n = (int) $grid['total']; ?>
    <?= $n === 0 ? 'Nothing is scheduled in ' . e($grid['label']) . '.' : e((string) $n) . ($n === 1 ? ' entry' : ' entries') . ' in ' . e($grid['label']) . ($grid['custom_total'] > 0 ? ', ' . (int) $grid['custom_total'] . ' of them added by your team.' : '.') ?>
</div>

<div class="cal-month-wrap">
    <table class="cal-month">
        <caption class="visually-hidden">Calendar for <?= e($grid['label']) ?>. Each day shows what is scheduled; use the plus button of a day to add a date.</caption>
        <thead>
        <tr><?php foreach ($grid['weekdays'] as $wd): ?><th scope="col" abbr="<?= e($wd['long']) ?>"><?= e($wd['short']) ?></th><?php endforeach; ?></tr>
        </thead>
        <tbody>
        <?php foreach ($grid['weeks'] as $week): ?>
            <tr>
                <?php foreach ($week as $c): $cnt = (int) $c['count']; ?>
                    <td class="cal-cell<?= $c['in_month'] ? '' : ' is-out' ?><?= $c['is_today'] ? ' is-today' : '' ?><?= $c['is_weekend'] ? ' is-weekend' : '' ?><?= $c['has_holiday'] ? ' has-holiday' : '' ?><?= $selectedDay === $c['date'] ? ' is-selected' : '' ?>" data-cal-day="<?= e($c['date']) ?>">
                        <div class="cal-cell__head">
                            <a class="cal-cell__num" href="<?= e($dayUrl($c['date'])) ?>"<?= $c['is_today'] ? ' aria-current="date"' : '' ?>
                               aria-label="<?= e($c['label'] . ($cnt > 0 ? ': ' . $cnt . ($cnt === 1 ? ' entry' : ' entries') : ': nothing scheduled') . '. Show this day') ?>"><?= (int) $c['day'] ?></a>
                            <a class="cal-cell__add" href="<?= e($addUrl($c['date'])) ?>" data-cal-add="<?= e($c['date']) ?>" data-cal-label="<?= e($c['label']) ?>"
                               aria-label="Add a date on <?= e($c['label']) ?>"><?= icon('plus', 'icon--sm') ?></a>
                        </div>
                        <?php if ($cnt > 0): ?>
                            <ul class="cal-cell__items">
                                <?php foreach (array_slice($c['items'], 0, $maxPills) as $o): ?><li><?= partial('admin/calendar/_pill', ['o' => $o]) ?></li><?php endforeach; ?>
                                <?php if ($cnt > $maxPills): ?><li><a class="cal-cell__more" href="<?= e($dayUrl($c['date'])) ?>">+<?= $cnt - $maxPills ?> more</a></li><?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cal-agenda" aria-label="Days with something scheduled in <?= e($grid['label']) ?>">
        <?php if ($agenda === []): ?>
            <p class="cal-agenda__empty">Nothing is scheduled in <?= e($grid['label']) ?>. Use "Add a date" to put the first one on the calendar.</p>
        <?php else: ?>
            <?php foreach ($agenda as $day): ?>
                <section class="cal-agenda__day<?= $day['is_today'] ? ' is-today' : '' ?>">
                    <h3 class="cal-agenda__date"><?= e($day['label']) ?><?= $day['is_today'] ? ' <span class="badge badge--gold">Today</span>' : '' ?></h3>
                    <ul class="cal-items"><?php foreach ($day['items'] as $o): ?><?= partial('admin/calendar/_item', ['o' => $o]) ?><?php endforeach; ?></ul>
                    <a class="cal-agenda__add" href="<?= e($addUrl($day['date'])) ?>" data-cal-add="<?= e($day['date']) ?>" data-cal-label="<?= e($day['label']) ?>"><?= icon('plus', 'icon--sm') ?> Add a date on this day</a>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<ul class="cal-legend" aria-label="What the colours and shapes mean">
    <?php foreach ($types as $t): $p = $t['pair']; ?>
        <li class="cal-legend__item<?= $t['is_active'] ? '' : ' is-off' ?>" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>"><?= icon((string) $t['icon'], 'icon--sm') ?><span><?= e($t['name']) ?><?= $t['is_active'] ? '' : ' (off)' ?></span></li>
    <?php endforeach; ?>
    <li class="cal-legend__note">A dashed outline means hidden or team only. A struck-through title means cancelled. "To confirm" means the date still has to be confirmed.</li>
</ul>

<section class="card cal-day-panel" id="day-panel" aria-labelledby="day-panel-title" tabindex="-1">
    <?php if ($selectedDay === ''): ?>
        <div class="card__body cal-day-panel__empty">
            <h2 class="card__title" id="day-panel-title">A day in detail</h2>
            <p>Click a day number to see everything on that day. Click the <strong>+</strong> of a day to add a date on it.</p>
        </div>
    <?php else: ?>
        <div class="card__header">
            <h2 class="card__title" id="day-panel-title"><?= e(CivilDate::fmt($selectedDay, 'l j F Y')) ?><?= $selectedDay === $today ? ' <span class="badge badge--gold">Today</span>' : '' ?></h2>
            <a class="btn btn--primary btn--sm" href="<?= e($addUrl($selectedDay)) ?>" data-cal-add="<?= e($selectedDay) ?>" data-cal-label="<?= e(CivilDate::fmt($selectedDay, 'l j F Y')) ?>"><?= icon('plus', 'icon--sm') ?> Add a date on this day</a>
        </div>
        <div class="card__body">
            <?php if ($selectedItems === []): ?>
                <p class="text-muted">Nothing is scheduled on this day.</p>
            <?php else: ?>
                <ul class="cal-items"><?php foreach ($selectedItems as $o): ?><?= partial('admin/calendar/_item', ['o' => $o]) ?><?php endforeach; ?></ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
