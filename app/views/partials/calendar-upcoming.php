<?php
/**
 * "Coming up" list: a day + month chip and the entry as a readable row. Used by the Contact block (next 6) and by the Coming up view of the page.
 * Vars: $items* (CalendarService::upcoming()), $emptyText (shown instead of the list), $summary (bool), $class (extra class on the list).
 */
?>
<?php if (empty($items)): ?>
    <?php if (!empty($emptyText)): ?><p class="cal-empty"><?= e($emptyText) ?></p><?php endif; ?>
<?php else: ?>
    <ul class="cal-upcoming<?= !empty($class) ? ' ' . e($class) : '' ?>">
        <?php foreach ($items as $o): ?>
            <li class="cal-upcoming__item"><?= partial('partials/calendar-item', ['o' => $o, 'lead' => 'date', 'summary' => !empty($summary)]) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
