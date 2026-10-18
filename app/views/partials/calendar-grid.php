<?php
/**
 * Month grid, server-rendered as a table (works without JavaScript); assets/js/calendar.js turns it into a keyboard grid
 * (role="grid", one tab stop, arrow keys), changes month in place and opens a day panel. assets/js/calendar.js builds the same markup again
 * after a month change: keep the two in step.
 * Vars: $grid* (CalendarService::monthGrid()), $max (entries shown per day before "+N more", default 3), $compact (the Contact block: icon markers
 *       instead of text pills), $moreUrl (callable(string $date): string, no-JavaScript link of "+N more").
 * Semantics: column headers with the full weekday name, every day is a cell whose date has an accessible name, today carries aria-current="date".
 */
$max = (int) ($max ?? 3);
$compact = !empty($compact);
$moreUrl = $moreUrl ?? static fn(string $d): string => url('calendar', ['month' => substr($d, 0, 7), 'view' => 'list']) . '#d-' . $d;
$tplc = static fn(string $s, array $v): string => strtr($s, array_combine(array_map(static fn($k) => '{' . $k . '}', array_keys($v)), array_map('strval', array_values($v))));
?>
<table class="cal-grid<?= $compact ? ' cal-grid--compact' : '' ?>" data-cal-grid data-month="<?= e($grid['month']) ?>" data-max="<?= $max ?>"
       aria-label="<?= e($tplc((string) content('calendar', 'aria.grid_label'), ['month' => $grid['label']])) ?>">
    <caption class="visually-hidden" data-hint="<?= e(content('calendar', 'aria.keyboard_hint')) ?>"><?= e($grid['label']) ?>. <?= e(content('calendar', 'aria.keyboard_hint')) ?></caption>
    <thead>
        <tr>
            <?php foreach ($grid['weekdays'] as $wd): ?>
                <th scope="col" class="cal-grid__dow"><abbr title="<?= e($wd['long']) ?>"><span aria-hidden="true"><?= e($wd['short']) ?></span><span class="visually-hidden"><?= e($wd['long']) ?></span></abbr></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($grid['weeks'] as $week): ?>
            <tr>
                <?php foreach ($week as $day):
                    $cls = 'cal-day' . ($day['in_month'] ? '' : ' cal-day--out') . ($day['is_today'] ? ' is-today' : '') . ($day['is_weekend'] ? ' cal-day--weekend' : '') . ($day['has_holiday'] ? ' cal-day--holiday' : '') . ($day['count'] > 0 ? ' has-items' : '');
                    $label = $day['count'] > 0 ? $tplc((string) content('calendar', $day['count'] === 1 ? 'aria.day_with_one' : 'aria.day_with_items'), ['date' => $day['label'], 'count' => $day['count']]) : $day['label'];
                    ?>
                    <td class="<?= e($cls) ?>" data-date="<?= e($day['date']) ?>"<?= $day['is_today'] ? ' aria-current="date"' : '' ?>>
                        <span class="cal-day__num"><span aria-hidden="true"><?= (int) $day['day'] ?></span><span class="visually-hidden"><?= e($label) ?></span></span>
                        <?php if ($day['count'] > 0): ?>
                            <ul class="cal-day__list">
                                <?php foreach (array_slice($day['items'], 0, $max) as $o): ?>
                                    <li><?= partial('partials/calendar-pill', ['o' => $o, 'compact' => $compact, 'showTime' => !$compact]) ?></li>
                                <?php endforeach; ?>
                                <?php if ($day['count'] > $max): $more = $tplc((string) content('calendar', 'views.more_label'), ['n' => $day['count'] - $max]); ?>
                                    <li><a class="cal-more" href="<?= e($moreUrl($day['date'])) ?>" data-cal-more><?php if ($compact): ?><span aria-hidden="true">+<?= (int) ($day['count'] - $max) ?></span><span class="visually-hidden"><?= e($more) ?></span><?php else: ?><?= e($more) ?><?php endif; ?></a></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
