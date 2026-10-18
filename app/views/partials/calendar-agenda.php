<?php
/**
 * Agenda list grouped by date (list view, and the mobile layout of the month). Vars: $days* (CalendarService::agenda() shape),
 * $emptyText, $summary (bool: one-line summaries, list view only). assets/js/calendar.js builds the same markup after a month change.
 */
use App\Services\Calendar\CivilDate;
?>
<?php if (empty($days)): ?>
    <p class="cal-empty"><?= e($emptyText ?? content('calendar', 'upcoming.empty_text')) ?></p>
<?php else: ?>
    <ol class="cal-agenda">
        <?php foreach ($days as $d): ?>
            <li class="cal-agenda__day<?= $d['is_today'] ? ' is-today' : '' ?>" id="d-<?= e($d['date']) ?>"<?= $d['is_today'] ? ' aria-current="date"' : '' ?>>
                <h3 class="cal-agenda__date">
                    <span class="cal-agenda__badge" aria-hidden="true"><span class="cal-agenda__dow"><?= e(CivilDate::fmt($d['date'], 'D')) ?></span><span class="cal-agenda__num"><?= (int) substr($d['date'], 8, 2) ?></span><span class="cal-agenda__mon"><?= e(CivilDate::fmt($d['date'], 'M')) ?></span></span>
                    <span class="visually-hidden"><?= $d['is_today'] ? e(content('calendar', 'views.today_label')) . ': ' : '' ?><?= e($d['label']) ?></span>
                </h3>
                <ul class="cal-agenda__list">
                    <?php foreach ($d['items'] as $o): ?>
                        <li><?= partial('partials/calendar-item', ['o' => $o, 'summary' => !empty($summary)]) ?></li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
