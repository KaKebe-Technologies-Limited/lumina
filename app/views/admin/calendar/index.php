<?php
/**
 * Admin > Calendar & schedule: the manager. Month view (default) and list view, quick add (button or click a day), links to the other screens.
 * Vars: $available, $view (month|list), $types, $addTypes, $canDelete, $canSettings, $today, $quick, and per view:
 *   month: $grid, $typeFilter, $selectedDay, $selectedItems, $agenda, $years     list: $f, $pagination, $years, $counts, $filtered
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-calendar.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-calendar cal-manager" data-cal-page="manager">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Calendar &amp; schedule</h1>
            <p class="page-header__subtitle">Uganda public holidays, international days and the dates of your own programs. Press <strong>Add a date</strong>, or click a day in the month.</p>
        </div>
        <div class="page-header__actions">
            <?php if (can('meetings.manage')): ?><a class="btn btn--accent" href="<?= e(url('admin/meetings/create')) ?>"><?= icon('users', 'icon--sm') ?> Schedule a meeting</a><?php endif; ?>
            <a class="btn btn--primary" href="<?= e(url('admin/calendar', ['date' => $quick['date']]) . '#quick-add') ?>" data-cal-open-quick><?= icon('plus', 'icon--sm') ?> Add a date</a>
            <a class="btn btn--outline" href="<?= e(url('admin/calendar/holidays')) ?>"><?= icon('star', 'icon--sm') ?> Holidays &amp; days</a>
            <a class="btn btn--ghost" href="<?= e(url('calendar')) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> View public calendar</a>
            <?php if ($canSettings): ?><a class="btn btn--ghost" href="<?= e(url('admin/calendar/settings')) ?>"><?= icon('settings', 'icon--sm') ?> Settings</a><?php endif; ?>
        </div>
    </div>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The calendar tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <?php if (error('_') !== null && old('form') !== 'quick'): ?>
            <div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e((string) error('_')) ?></div></div>
        <?php endif; ?>

        <nav class="cal-tabs" aria-label="Calendar views">
            <a class="cal-tabs__item<?= $view === 'month' ? ' is-active' : '' ?>" href="<?= e(url('admin/calendar')) ?>"<?= $view === 'month' ? ' aria-current="page"' : '' ?>><?= icon('calendar', 'icon--sm') ?> Month</a>
            <a class="cal-tabs__item<?= $view === 'list' ? ' is-active' : '' ?>" href="<?= e(url('admin/calendar', ['view' => 'list'])) ?>"<?= $view === 'list' ? ' aria-current="page"' : '' ?>><?= icon('list', 'icon--sm') ?> List &amp; search</a>
            <?php if (can('meetings.manage')): ?><a class="cal-tabs__item" href="<?= e(url('admin/meetings')) ?>"><?= icon('users', 'icon--sm') ?> Meetings</a><?php endif; ?>
            <span class="cal-tabs__spacer"></span>
            <a class="cal-tabs__link" href="<?= e(url('admin/calendar/export.csv')) ?>"><?= icon('download', 'icon--sm') ?> Export CSV</a>
            <a class="cal-tabs__link" href="<?= e(url('admin/calendar/import')) ?>"><?= icon('upload', 'icon--sm') ?> Import CSV</a>
        </nav>

        <?php if ($view === 'list'): ?>
            <?= partial('admin/calendar/_list', [
                'f' => $f, 'pagination' => $pagination, 'types' => $types, 'years' => $years, 'counts' => $counts, 'filtered' => $filtered, 'canDelete' => $canDelete, 'today' => $today,
                'meetingMap' => $meetingMap ?? [],
            ]) ?>
        <?php else: ?>
            <?= partial('admin/calendar/_month', [
                'grid' => $grid, 'typeFilter' => $typeFilter, 'selectedDay' => $selectedDay, 'selectedItems' => $selectedItems, 'agenda' => $agenda, 'years' => $years,
                'types' => $types, 'today' => $today, 'meetingMap' => $meetingMap ?? [],
            ]) ?>
        <?php endif; ?>

        <?= partial('admin/calendar/_quick', ['quick' => $quick, 'addTypes' => $addTypes, 'returnMonth' => $view === 'month' ? (string) ($grid['month'] ?? '') : '']) ?>
    <?php endif; ?>
</div>
