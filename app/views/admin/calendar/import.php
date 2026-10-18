<?php
/**
 * Admin > Calendar > Import / export (CSV). Vars: $available, $columns (CSV columns, in order), $labels, $result (null, or the dry-run report: rows, summary,
 * notes, payload, converted, filename), $maxRows, $maxBytes, $types (usable types), $ownCount, $allCount.
 * Step 1 "Check the file" validates every row exactly like the entry form and saves nothing; step 2 "Import" saves the valid rows (CalendarTransferController).
 */
use App\Services\Calendar\CivilDate;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-calendar.js')) ?>" defer></script>
<?php $this->end(); ?>
<?php
$help = [
    'title' => ['What is happening (required)', 'Volunteer orientation'],
    'type' => ['One of: ' . implode(', ', array_column($types, 'slug')) . ' (required)', 'meeting'],
    'start_date' => ['First day, year-month-day (required)', '2030-01-15'],
    'end_date' => ['Last day, only if it lasts several days', '2030-01-17'],
    'all_day' => ['yes or no. Empty = yes, unless a start time is given', 'no'],
    'start_time' => ['24-hour time, East Africa Time', '10:00'],
    'end_time' => ['24-hour time', '12:00'],
    'location' => ['Place', 'Gulu City'],
    'repeat' => ['none, daily, weekly, monthly or yearly. Empty = none', 'monthly'],
    'repeat_until' => ['Last day it can start, year-month-day', '2030-12-31'],
    'public' => ['yes or no. Empty = yes (visible to visitors)', 'yes'],
    'description' => ['Plain text, a few lines', 'Welcome session for new volunteers.'],
    'link_url' => ['Web address starting with https://', 'https://example.org/register'],
    'source_label' => ['Where the date comes from', 'United Nations'],
    'source_url' => ['Web address of the source', ''],
];
$s = $result['summary'] ?? null;
?>
<div class="admin-calendar cal-import" data-cal-page="import">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Import / export</h1>
            <p class="page-header__subtitle">Add many dates at once from a spreadsheet, or take a copy of the whole calendar. Nothing is saved until you have seen the check and pressed Import.</p>
        </div>
        <div class="page-header__actions"><a class="btn btn--outline" href="<?= e(url('admin/calendar')) ?>"><?= icon('calendar', 'icon--sm') ?> Back to the calendar</a></div>
    </div>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The calendar tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>

    <?php if ($result !== null): ?>
        <section class="card cal-report" id="report" aria-labelledby="report-title" tabindex="-1">
            <div class="card__header"><h2 class="card__title" id="report-title">Result of the check<?= $result['filename'] !== '' ? ': ' . e($result['filename']) : '' ?></h2></div>
            <div class="card__body">
                <div class="cal-tiles" role="list">
                    <div class="cal-tile cal-tile--ok" role="listitem"><strong><?= (int) $s['valid'] ?></strong><span>ready to import</span></div>
                    <div class="cal-tile<?= $s['errors'] > 0 ? ' cal-tile--bad' : '' ?>" role="listitem"><strong><?= (int) $s['errors'] ?></strong><span>with a problem</span></div>
                    <div class="cal-tile" role="listitem"><strong><?= (int) $s['duplicates'] ?></strong><span>already in the calendar</span></div>
                    <div class="cal-tile" role="listitem"><strong><?= (int) $s['total'] ?></strong><span>rows in the file</span></div>
                </div>
                <?php foreach ($result['notes'] as $note): ?><p class="cal-note-inline"><?= icon('info', 'icon--sm') ?> <?= e($note) ?></p><?php endforeach; ?>
                <?php if (!empty($result['converted'])): ?><p class="cal-note-inline"><?= icon('info', 'icon--sm') ?> The file was not saved as UTF-8, so it was converted from Windows text. Check that names with accents look right below.</p><?php endif; ?>

                <?php if ($s['valid'] > 0): ?>
                    <form method="post" action="<?= e(url('admin/calendar/import')) ?>" class="cal-report__commit">
                        <?= csrf_field() ?>
                        <input type="hidden" name="commit" value="1">
                        <input type="hidden" name="payload" value="<?= e($result['payload']) ?>">
                        <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Import <?= (int) $s['valid'] ?> <?= $s['valid'] === 1 ? 'date' : 'dates' ?></button>
                        <span class="text-muted"><?= $s['errors'] + $s['duplicates'] > 0 ? ((int) ($s['errors'] + $s['duplicates'])) . ' row(s) below will be skipped. ' : '' ?>You can fix the file and check it again instead.</span>
                    </form>
                <?php else: ?>
                    <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">Nothing can be imported yet.</strong> Fix the problems listed below in your spreadsheet, save it again as CSV and check it once more.</div></div>
                <?php endif; ?>

                <div class="table-wrap">
                    <table class="table table--stack cal-report__table">
                        <thead><tr><th scope="col" class="col-narrow">Row</th><th scope="col">Title</th><th scope="col">Date</th><th scope="col">Type</th><th scope="col">Repeat</th><th scope="col">Result</th></tr></thead>
                        <tbody>
                        <?php foreach ($result['rows'] as $r): ?>
                            <tr class="cal-row--<?= e($r['status']) ?>">
                                <td data-label="Row" class="col-narrow"><?= (int) $r['n'] ?></td>
                                <td data-label="Title"><?= e(mb_strimwidth($r['title'], 0, 80, '...')) ?></td>
                                <td data-label="Date" class="nowrap"><?= e($r['start_date']) ?><?= $r['end_date'] !== '' ? ' to ' . e($r['end_date']) : '' ?><?= $r['time'] !== '' ? '<span class="cell-sub">' . e($r['time']) . '</span>' : '' ?></td>
                                <td data-label="Type"><?= e($r['type_name']) ?></td>
                                <td data-label="Repeat"><?= e($r['repeat']) ?></td>
                                <td data-label="Result">
                                    <?php if ($r['status'] === 'ok'): ?><span class="badge badge--success">Ready</span>
                                    <?php elseif ($r['status'] === 'duplicate'): ?><span class="badge badge--neutral">Already there - skipped</span>
                                    <?php else: ?><span class="badge badge--danger">Problem - skipped</span><?php endif; ?>
                                    <?php if ($r['messages'] !== []): ?>
                                        <ul class="cal-report__msgs"><?php foreach ($r['messages'] as $col => $m): ?><li><strong><?= e($labels[$col] ?? $col) ?>:</strong> <?= e($m) ?></li><?php endforeach; ?></ul>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="cal-import__grid">
        <section class="card" aria-labelledby="imp-title">
            <div class="card__header"><h2 class="card__title" id="imp-title">Import dates from a spreadsheet</h2></div>
            <div class="card__body">
                <ol class="cal-steps">
                    <li><strong>Download the template</strong> and open it in Excel or Google Sheets. Delete the two example rows and write one row per date.
                        <div><a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar/import-template.csv')) ?>"><?= icon('download', 'icon--sm') ?> Download the template</a></div></li>
                    <li><strong>Save it as CSV</strong> (File &gt; Save as / Download &gt; CSV, comma separated).</li>
                    <li><strong>Choose the file and check it.</strong> You will see every row and any problem before anything is saved.
                        <form method="post" action="<?= e(url('admin/calendar/import')) ?>" enctype="multipart/form-data" class="cal-upload" data-cal-upload>
                            <?= csrf_field() ?>
                            <input type="hidden" name="dry_run" value="1">
                            <div class="field">
                                <label class="field__label" for="csv-file">CSV file (up to <?= (int) $maxRows ?> rows, <?= e(human_filesize($maxBytes)) ?>)</label>
                                <input class="input" id="csv-file" type="file" name="csv" accept=".csv,.txt,text/csv" required data-cal-file>
                            </div>
                            <button class="btn btn--primary" type="submit"><?= icon('check-circle', 'icon--sm') ?> Check the file</button>
                        </form>
                    </li>
                </ol>
                <p class="cal-note-inline"><?= icon('info', 'icon--sm') ?> Dates must be written year-month-day, for example 2030-01-15. Imported dates are added as your own entries (never the holidays that came with the site) and can repeat daily, weekly, monthly or yearly. Special repeats such as Easter or the 2nd Tuesday are set up in the editor by an administrator.</p>

                <h3 class="cal-subhead">The columns</h3>
                <div class="table-wrap">
                    <table class="table table--stack">
                        <thead><tr><th scope="col">Column</th><th scope="col">What to write</th><th scope="col">Example</th></tr></thead>
                        <tbody>
                        <?php foreach ($columns as $c): ?>
                            <tr><td data-label="Column"><code><?= e($c) ?></code></td><td data-label="What to write"><?= e($help[$c][0] ?? '') ?></td><td data-label="Example"><?= e($help[$c][1] ?? '') ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="card" aria-labelledby="exp-title">
            <div class="card__header"><h2 class="card__title" id="exp-title">Export</h2></div>
            <div class="card__body">
                <p>Download every entry (<?= (int) $allCount ?>, including the holidays and days that came with the site) as a spreadsheet file. Descriptions are plain text. Opening it in Excel is safe: cells that start with =, +, - or @ are protected.</p>
                <p><a class="btn btn--primary" href="<?= e(url('admin/calendar/export.csv')) ?>"><?= icon('download', 'icon--sm') ?> Download all as CSV</a></p>
                <p class="text-muted">The export is recorded in the activity log. You added <?= (int) $ownCount ?> <?= $ownCount === 1 ? 'entry' : 'entries' ?> yourselves.</p>
                <p class="cal-note-inline"><?= icon('info', 'icon--sm') ?> Only the simple repeats (none, daily, weekly, monthly, yearly) are part of the CSV format, and the repeat settings such as "every 2 weeks on Monday" are not saved in it. Importing this file again skips what is already in the calendar (the holidays included) and cannot restore Easter or Islamic holidays or special repeats: keep it as a copy to read, not as a backup to restore.</p>
            </div>
        </section>
    </div>
    <?php endif; ?>
</div>
