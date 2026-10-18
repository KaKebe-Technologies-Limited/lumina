<?php
/**
 * JSON for the data-cal-config attribute of a calendar root (the page, the Contact block, the detail page): every label assets/js/calendar.js
 * shows (all from the registry, Admin > Pages > Calendar), the month and weekday names, and whatever the caller adds in $extra.
 * Echoes the JSON only; the caller escapes it: data-cal-config="<?= e(trim(partial('partials/calendar-config', [...]))) ?>"
 * The one literal word is "to" (joins two dates or two times); the registry has no field for it and the PHP service (CalendarService::dateLabel)
 * uses the same default.
 */
use App\Services\Calendar\CivilDate;

$c = static fn(string $key): string => (string) content('calendar', $key);
$cfg = [
    'months'   => array_values(CivilDate::MONTHS),
    'weekdays' => array_values(CivilDate::WEEKDAYS),
    'share'    => section_on('calendar', 'share'),
    'labels'   => [
        'close' => $c('day.close_label'), 'when' => $c('day.when_label'), 'where' => $c('day.where_label'), 'source' => $c('day.source_label'),
        'about' => $c('day.about_label'), 'link' => $c('day.link_default'), 'cancelled' => $c('day.cancelled_note'),
        'panel_title' => $c('day.panel_title'), 'panel_empty' => $c('day.panel_empty'),
        'holiday' => $c('filters.holiday_badge'), 'tbc' => $c('filters.tbc_badge'), 'moved' => $c('filters.moved_badge'),
        'all_day' => $c('views.all_day'), 'time_note' => $c('views.time_note'), 'more' => $c('views.more_label'),
        'add' => $c('share.add_label'), 'share' => $c('share.share_label'), 'copy' => $c('share.copy_label'), 'copied' => $c('share.copied'),
        'loading' => $c('aria.loading'), 'error' => $c('aria.load_error'), 'announce' => $c('aria.month_announce'), 'announce_one' => $c('aria.month_announce_one'), 'day_items' => $c('aria.day_with_items'), 'day_one' => $c('aria.day_with_one'),
        'grid' => $c('aria.grid_label'), 'empty_month' => $c('empty.month_title'), 'filtered' => $c('empty.filtered_text'), 'month_empty_text' => $c('empty.month_text'),
        'today' => $c('views.today_label'), 'title_month' => $c('seo.month_title'),
        'to' => 'to',
    ],
] + ($extra ?? []);
echo json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
