<?php
/**
 * Registry entry: CALENDAR & PROGRAMME SCHEDULER (/calendar, /calendar/{slug}). Contract: docs/CALENDAR.md.
 * The entries themselves (holidays, international days, programme sessions ...) live in the calendar tables (Admin > Calendar);
 * this file holds ALL the copy around them: headings, labels, buttons, legends, empty states, the disclaimer, accessibility strings
 * and the fixed words of the iCal files. Nothing in the views is hard-coded.
 *
 * Placeholders usable in the texts that mention them: {n} (number), {month} ("March 2026"), {date}, {title}, {type}, {count}, {site}.
 * Heading fields accept [[double brackets]] for the hand-drawn underline.
 */
$hlHelp = 'Put [[double brackets]] around the words that get the hand-drawn underline.';
return [
    'slug'  => 'calendar',
    'title' => 'Calendar',
    'route' => '/calendar',
    'help'  => 'The public Calendar: Uganda public holidays, important international and UN days, and our own program sessions and events. Add and change the dates themselves under Calendar in the admin menu; the headings, labels and notes are edited here.',
    'nav'   => ['label' => 'Calendar', 'order' => 91, 'header' => true, 'footer' => true, 'parent' => 'contact'],
    'seo'   => [
        'title'       => 'Calendar of Holidays, International Days and Programs',
        'description' => 'Uganda public holidays, important international and UN humanitarian days, and the dates of Lumina Africa Foundation programs and events.',
        'og_image'    => 'assets/img/site/photo-04.jpg',
    ],
    'sections' => [
        [
            'key' => 'hero', 'label' => 'Page banner',
            'fields' => [
                ['key' => 'eyebrow', 'label' => 'Small heading above the title', 'type' => 'text', 'default' => 'Dates to remember', 'maxlength' => 80],
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Our [[Calendar]]', 'maxlength' => 140, 'help' => $hlHelp],
                ['key' => 'lead', 'label' => 'Intro', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 400,
                    'default' => 'Uganda public holidays, important international and United Nations days, and the dates of our own programs and events, all in one place.'],
                ['key' => 'image', 'label' => 'Background photo (optional)', 'type' => 'image', 'default' => 'assets/img/site/photo-04.jpg',
                    'alt' => 'Young people seated in the audience of an event', 'help' => 'Remove it for the plain navy banner.'],
            ],
        ],
        [
            'key' => 'views', 'label' => 'Month view and controls', 'help' => 'Buttons and labels of the month grid and the list view.',
            'fields' => [
                ['key' => 'month_tab', 'label' => 'Tab: month grid', 'type' => 'text', 'default' => 'Month'],
                ['key' => 'agenda_tab', 'label' => 'Tab: list of dates', 'type' => 'text', 'default' => 'List'],
                ['key' => 'upcoming_tab', 'label' => 'Tab: coming up', 'type' => 'text', 'default' => 'Coming up'],
                ['key' => 'today_label', 'label' => 'Button: go to today', 'type' => 'text', 'default' => 'Today'],
                ['key' => 'prev_label', 'label' => 'Button: previous month (accessible name)', 'type' => 'text', 'default' => 'Previous month'],
                ['key' => 'next_label', 'label' => 'Button: next month (accessible name)', 'type' => 'text', 'default' => 'Next month'],
                ['key' => 'jump_label', 'label' => 'Jump to a month: label', 'type' => 'text', 'default' => 'Jump to'],
                ['key' => 'month_label', 'label' => 'Jump: month field', 'type' => 'text', 'default' => 'Month'],
                ['key' => 'year_label', 'label' => 'Jump: year field', 'type' => 'text', 'default' => 'Year'],
                ['key' => 'go_label', 'label' => 'Jump: button', 'type' => 'text', 'default' => 'Show'],
                ['key' => 'more_label', 'label' => 'Day cell: "more" link ({n} = how many more)', 'type' => 'text', 'default' => '+{n} more'],
                ['key' => 'all_day', 'label' => 'Word for an all-day entry', 'type' => 'text', 'default' => 'All day'],
                ['key' => 'print_label', 'label' => 'Button: print', 'type' => 'text', 'default' => 'Print this month'],
                ['key' => 'time_note', 'label' => 'Note about times', 'type' => 'text', 'default' => 'Times are East Africa Time (Kampala).'],
            ],
        ],
        [
            'key' => 'filters', 'label' => 'Filters and key', 'help' => 'The colour key (legend) and the filter chips. Each type also has a shape (icon) and a text label, so colour is never the only clue.',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading above the filters', 'type' => 'text', 'default' => 'Show'],
                ['key' => 'group_label', 'label' => 'Accessible name of the filter group', 'type' => 'text', 'default' => 'Filter the calendar by type'],
                ['key' => 'all_label', 'label' => 'Button: show every type', 'type' => 'text', 'default' => 'Show all'],
                ['key' => 'holiday_badge', 'label' => 'Badge on public holidays', 'type' => 'text', 'default' => 'Public holiday'],
                ['key' => 'tbc_badge', 'label' => 'Badge on a date that is not confirmed yet', 'type' => 'text', 'default' => 'Date to be confirmed'],
                ['key' => 'moved_badge', 'label' => 'Badge on a date that was changed', 'type' => 'text', 'default' => 'Date changed'],
            ],
        ],
        [
            'key' => 'upcoming', 'label' => 'Coming up list',
            'fields' => [
                ['key' => 'title', 'label' => 'Heading', 'type' => 'text', 'default' => 'Coming [[up]]', 'help' => $hlHelp],
                ['key' => 'count', 'label' => 'How many dates to list', 'type' => 'number', 'default' => 12, 'min' => 3, 'max' => 50],
                ['key' => 'empty_title', 'label' => 'Empty state: heading', 'type' => 'text', 'default' => 'No dates coming up'],
                ['key' => 'empty_text', 'label' => 'Empty state: text', 'type' => 'textarea', 'rows' => 2, 'default' => 'Nothing is scheduled for the selected types yet. Please check back soon.'],
            ],
        ],
        [
            'key' => 'day', 'label' => 'Day panel and entry details',
            'fields' => [
                ['key' => 'panel_title', 'label' => 'Day panel: heading ({date})', 'type' => 'text', 'default' => '{date}'],
                ['key' => 'panel_empty', 'label' => 'Day panel: nothing on this day', 'type' => 'text', 'default' => 'Nothing is scheduled on this day.'],
                ['key' => 'close_label', 'label' => 'Close button', 'type' => 'text', 'default' => 'Close'],
                ['key' => 'when_label', 'label' => 'Detail: when', 'type' => 'text', 'default' => 'When'],
                ['key' => 'where_label', 'label' => 'Detail: where', 'type' => 'text', 'default' => 'Where'],
                ['key' => 'source_label', 'label' => 'Detail: source', 'type' => 'text', 'default' => 'Source'],
                ['key' => 'about_label', 'label' => 'Detail: about', 'type' => 'text', 'default' => 'About this date'],
                ['key' => 'link_default', 'label' => 'Detail: more information link (when the entry has no link text)', 'type' => 'text', 'default' => 'More information'],
                ['key' => 'program_label', 'label' => 'Detail: related program', 'type' => 'text', 'default' => 'Related program'],
                ['key' => 'project_label', 'label' => 'Detail: related project', 'type' => 'text', 'default' => 'Related project'],
                ['key' => 'event_label', 'label' => 'Detail: related event', 'type' => 'text', 'default' => 'Event page'],
                ['key' => 'next_dates', 'label' => 'Detail: heading of the next dates', 'type' => 'text', 'default' => 'Next dates'],
                ['key' => 'back_label', 'label' => 'Detail: back to the calendar', 'type' => 'text', 'default' => 'Back to the calendar'],
                ['key' => 'cancelled_note', 'label' => 'Detail: shown when this date was cancelled', 'type' => 'text', 'default' => 'This date has been cancelled.'],
                ['key' => 'not_found_title', 'label' => 'Detail: no such date (heading)', 'type' => 'text', 'default' => 'That date is not on the calendar'],
                ['key' => 'not_found_text', 'label' => 'Detail: no such date (text)', 'type' => 'text', 'default' => 'The entry may have moved or been removed. Please go back to the calendar.'],
            ],
        ],
        [
            'key' => 'share', 'label' => 'Add to calendar and share', 'toggle' => true,
            'fields' => [
                ['key' => 'add_label', 'label' => 'Button: add to my calendar', 'type' => 'text', 'default' => 'Add to my calendar'],
                ['key' => 'share_label', 'label' => 'Button: share', 'type' => 'text', 'default' => 'Share'],
                ['key' => 'copy_label', 'label' => 'Button: copy link', 'type' => 'text', 'default' => 'Copy link'],
                ['key' => 'copied', 'label' => 'Message after copying the link', 'type' => 'text', 'default' => 'Link copied'],
                ['key' => 'subscribe_title', 'label' => 'Subscribe: heading', 'type' => 'text', 'default' => 'Keep the dates in your own calendar'],
                ['key' => 'subscribe_text', 'label' => 'Subscribe: text', 'type' => 'textarea', 'rows' => 3,
                    'default' => 'Download the whole calendar as an iCal (.ics) file for Google Calendar, Outlook or Apple Calendar, or subscribe to its address to receive updates.'],
                ['key' => 'subscribe_label', 'label' => 'Subscribe: download button', 'type' => 'text', 'default' => 'Download the calendar (.ics)'],
            ],
        ],
        [
            'key' => 'empty', 'label' => 'Empty month',
            'fields' => [
                ['key' => 'month_title', 'label' => 'Heading when a month has no dates', 'type' => 'text', 'default' => 'Nothing scheduled in {month}'],
                ['key' => 'month_text', 'label' => 'Text when a month has no dates', 'type' => 'text', 'default' => 'Use the arrows to look at other months.'],
                ['key' => 'filtered_text', 'label' => 'Text when the filters hide everything', 'type' => 'text', 'default' => 'No dates match the selected types. Choose more types to see them.'],
            ],
        ],
        [
            'key' => 'notes', 'label' => 'Notes under the calendar',
            'fields' => [
                ['key' => 'disclaimer', 'label' => 'Disclaimer', 'type' => 'textarea', 'rows' => 4,
                    'default' => 'Public holiday dates follow the Public Holidays Act of Uganda. The government can declare additional or changed holidays by notice, and moveable dates such as Eid al-Fitr and Eid al-Adha are confirmed by official announcement after the moon is sighted; until then they are marked "Date to be confirmed" (the real day is usually the day shown or one day earlier). A few international days, such as Safer Internet Day, are announced afresh each year.',
                    'help' => 'Shown under the calendar and on the Contact page. Change it if the rules change.'],
                ['key' => 'sources_label', 'label' => 'Heading of the sources note', 'type' => 'text', 'default' => 'Where the dates come from'],
                ['key' => 'sources_text', 'label' => 'Sources note', 'type' => 'textarea', 'rows' => 3,
                    'default' => 'Each international day shows its source, such as the United Nations or the World Health Organization. Open a date to see it.'],
            ],
        ],
        [
            'key' => 'ics', 'label' => 'Fixed words in the iCal (.ics) files', 'help' => 'Used inside the calendar files people add to Google, Outlook or Apple Calendar.',
            'fields' => [
                ['key' => 'calendar_name', 'label' => 'Name of the calendar ({site} = organisation name)', 'type' => 'text', 'default' => '{site} calendar'],
                ['key' => 'tbc_note', 'label' => 'Note on a date that is not confirmed', 'type' => 'text', 'default' => 'Date to be confirmed.'],
                ['key' => 'source_prefix', 'label' => 'Word before the source', 'type' => 'text', 'default' => 'Source:'],
            ],
        ],
        [
            'key' => 'aria', 'label' => 'Screen-reader text', 'help' => 'Text read out by screen readers. It is not shown on screen.',
            'fields' => [
                ['key' => 'nav_label', 'label' => 'Month navigation: accessible name', 'type' => 'text', 'default' => 'Calendar navigation'],
                ['key' => 'grid_label', 'label' => 'Month grid: accessible name ({month})', 'type' => 'text', 'default' => 'Calendar for {month}'],
                ['key' => 'month_announce', 'label' => 'Announced after changing month, 2 or more entries ({month}, {count})', 'type' => 'text', 'default' => '{month}: {count} entries'],
                ['key' => 'month_announce_one', 'label' => 'Announced after changing month, when there is one entry ({month})', 'type' => 'text', 'default' => '{month}: 1 entry'],
                ['key' => 'day_with_items', 'label' => 'Day with 2 or more entries ({date}, {count})', 'type' => 'text', 'default' => '{date}, {count} entries'],
                ['key' => 'day_with_one', 'label' => 'Day with one entry ({date})', 'type' => 'text', 'default' => '{date}, 1 entry'],
                ['key' => 'loading', 'label' => 'While loading', 'type' => 'text', 'default' => 'Loading the calendar'],
                ['key' => 'load_error', 'label' => 'Message when the calendar cannot be loaded', 'type' => 'text', 'default' => 'The calendar could not be loaded. Please reload the page.'],
                ['key' => 'filter_hint', 'label' => 'Filter chips: hint', 'type' => 'text', 'default' => 'Tick or untick a type to show or hide it.'],
                ['key' => 'keyboard_hint', 'label' => 'Grid: keyboard hint', 'type' => 'text', 'default' => 'Use the arrow keys to move between days and Enter to open a day.'],
            ],
        ],
        [
            'key' => 'seo', 'label' => 'Titles of month pages (search engines)',
            'fields' => [
                ['key' => 'month_title', 'label' => 'Page title of a month ({month})', 'type' => 'text', 'default' => '{month} Calendar'],
                ['key' => 'month_description', 'label' => 'Description of a month ({month}, {count})', 'type' => 'text', 'default' => 'Uganda public holidays, international days and program dates for {month}.'],
            ],
        ],
    ],
];
