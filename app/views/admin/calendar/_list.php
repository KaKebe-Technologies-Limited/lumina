<?php
/**
 * List view: ENTRIES (one row per schedule, not per date) with search and filters, the next date of each, and bulk actions.
 * Vars: $f (filters), $pagination (items = rows: entry, type, next, rule), $types, $years, $counts, $filtered, $canDelete, $today.
 */
use App\Controllers\Admin\CalendarController as C;
use App\Services\Calendar\CalendarService;
use App\Services\Calendar\CivilDate;

$rows = $pagination->items;
$rel = static function (string $date) use ($today): string {
    $d = CivilDate::diffDays($today, $date);
    return $d === 0 ? 'Today' : ($d === 1 ? 'Tomorrow' : ($d > 1 && $d <= 60 ? 'In ' . $d . ' days' : ''));
};
?>
<form class="filters" method="get" action="<?= e(url('admin/calendar')) ?>" role="search">
    <input type="hidden" name="view" value="list">
    <div class="field field--grow">
        <label class="field__label" for="l-q">Search</label>
        <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="l-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Title, place or source" maxlength="100"></div>
    </div>
    <div class="field"><label class="field__label" for="l-type">Type</label>
        <select class="select" id="l-type" name="type" data-autosubmit>
            <option value="">All types</option>
            <?php foreach ($types as $t): if ($t['slug'] === CalendarService::SLUG_EVENT) { continue; } ?><option value="<?= e($t['slug']) ?>"<?= $f['type'] === $t['slug'] ? ' selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select></div>
    <div class="field"><label class="field__label" for="l-year">Year</label>
        <select class="select" id="l-year" name="year" data-autosubmit>
            <option value="">Any year</option>
            <?php foreach ($years as $y): ?><option value="<?= (int) $y ?>"<?= $f['year'] === (string) $y ? ' selected' : '' ?>><?= (int) $y ?></option><?php endforeach; ?>
        </select></div>
    <div class="field"><label class="field__label" for="l-status">Status</label>
        <select class="select" id="l-status" name="status" data-autosubmit><?php foreach (C::STATUSES as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="l-source">Where from</label>
        <select class="select" id="l-source" name="source" data-autosubmit><?php foreach (C::SOURCES as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['source'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="l-sort">Order</label>
        <select class="select" id="l-sort" name="sort" data-autosubmit><?php foreach (C::SORTS as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['sort'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="field cal-filter-check"><label class="check"><input type="checkbox" name="confirm" value="1"<?= $f['confirm'] === '1' ? ' checked' : '' ?> data-autosubmit-check><span>Only dates to be confirmed</span></label></div>
    <div class="filters__actions">
        <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
        <?php if ($filtered || $f['sort'] !== 'next'): ?><a class="btn btn--ghost" href="<?= e(url('admin/calendar', ['view' => 'list'])) ?>">Reset</a><?php endif; ?>
    </div>
</form>

<p class="cal-summary" role="status">
    <?= (int) $pagination->total ?> <?= $pagination->total === 1 ? 'entry' : 'entries' ?><?= $filtered ? ' match your filters' : '' ?>.
    <?php if (!$filtered): ?>
        <?= (int) ($counts['own'] ?? 0) ?> added by your team, <?= (int) ($counts['hidden'] ?? 0) ?> hidden<?= (int) ($counts['confirm'] ?? 0) > 0 ? ', ' . (int) $counts['confirm'] . ' with a date to be confirmed' : '' ?>.
    <?php endif; ?>
    Events from Admin &gt; Events also show on the calendar, but are changed there.
</p>

<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', [
        'icon' => 'calendar', 'title' => $filtered ? 'Nothing matches these filters' : 'No entries yet',
        'text' => $filtered ? 'Try a different search or clear the filters.' : 'Add the first date with the "Add a date" button.',
        'actions' => $filtered ? [['label' => 'Clear filters', 'url' => url('admin/calendar', ['view' => 'list']), 'class' => 'btn--outline']] : [],
    ]) ?>
<?php else: ?>
    <form id="cal-bulk" class="cal-bulk" method="post" action="<?= e(url('admin/calendar/bulk')) ?>" data-cal-bulk data-can-delete="<?= $canDelete ? '1' : '0' ?>">
        <?= csrf_field() ?>
        <label class="visually-hidden" for="bulk-action">What to do with the ticked entries</label>
        <select class="select" id="bulk-action" name="action" data-bulk-action>
            <option value="">With the ticked entries...</option>
            <option value="activate">Show on the calendar</option>
            <option value="deactivate">Hide from the calendar</option>
            <?php if ($canDelete): ?><option value="delete">Delete (cannot be undone)</option><?php endif; ?>
        </select>
        <button class="btn btn--outline" type="submit">Apply</button>
        <span class="cal-bulk__count" data-selected-count aria-live="polite"></span>
        <?php if (!$canDelete): ?><span class="cal-bulk__hint">Only administrators can delete in bulk.</span><?php endif; ?>
    </form>

    <div class="table-wrap">
        <table class="table table--stack cal-list" id="cal-list">
            <thead>
            <tr>
                <th scope="col" class="col-narrow"><input type="checkbox" data-select-all aria-label="Tick all entries on this page"></th>
                <th scope="col">What</th><th scope="col">When</th><th scope="col">Type</th><th scope="col">Status</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $e = $r['entry'];
                $t = $r['type'];
                $n = $r['next'];
                $id = (int) $e['id'];
                $off = (int) $e['is_active'] !== 1;
                $past = $n === null || $n['end_date'] < $today;
                $typeShown = $t !== null && $t['is_active'] && $t['show_on_public'];
                $meetingId = (int) (($meetingMap ?? [])[$id] ?? 0);      // a meeting owns this entry: changed on the meeting page, never copied / hidden / bulk-edited here
                $editUrl = $meetingId > 0 ? url('admin/meetings/' . $meetingId) : url('admin/calendar/' . $id . '/edit');
                ?>
                <tr class="<?= $off ? 'is-off' : '' ?>">
                    <td class="col-narrow" data-label=""><?php if ($meetingId === 0): ?><input type="checkbox" name="ids[]" value="<?= $id ?>" form="cal-bulk" data-select-row aria-label="Tick <?= e($e['title']) ?>"><?php endif; ?></td>
                    <td data-label="What">
                        <span class="cell-title"><a href="<?= e($editUrl) ?>"><?= e($e['title']) ?></a></span>
                        <span class="cell-sub"><?= e($r['rule']) ?><?= !empty($e['location']) ? ' &middot; ' . e($e['location']) : '' ?></span>
                    </td>
                    <td data-label="When" class="nowrap">
                        <?php if ($n !== null && !$past): ?>
                            <strong><?= e(CivilDate::fmt($n['start_date'], 'D j M Y')) ?></strong>
                            <?php $label = $rel($n['start_date']); if ($label !== ''): ?><span class="cell-sub"><?= e($label) ?></span><?php endif; ?>
                            <?php if (!empty($n['start_time'])): ?><span class="cell-sub"><?= e(CalendarService::timeLabel($n)) ?></span><?php endif; ?>
                        <?php elseif ($n !== null): ?>
                            <?= e(CivilDate::fmt($n['start_date'], 'D j M Y')) ?><span class="cell-sub">Over</span>
                        <?php else: ?>
                            <?= e(CivilDate::fmt((string) $e['start_date'], 'D j M Y')) ?><span class="cell-sub"><?= (string) $e['recurrence'] === 'none' ? 'Over' : 'No more dates' ?></span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Type">
                        <?php if ($t): $p = $t['pair']; ?><span class="cal-typechip" style="--cal-solid:<?= e($p['solid']) ?>;--cal-tint:<?= e($p['tint']) ?>;--cal-text:<?= e($p['text']) ?>"><?= icon((string) $t['icon'], 'icon--sm') ?><?= e($t['name']) ?></span><?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <?php if ($off): ?><span class="badge badge--neutral">Hidden</span><?php elseif (!(int) $e['is_public'] || !$typeShown): ?><span class="badge badge--neutral"<?= (int) $e['is_public'] ? ' title="Its type is hidden from visitors"' : '' ?>>Team only</span><?php else: ?><span class="badge badge--success">On the website</span><?php endif; ?>
                        <?php if ($n !== null && $n['needs_confirmation']): ?><span class="badge badge--warning">Date to be confirmed</span><?php endif; ?>
                        <?php if ((int) $e['is_system'] === 1): ?><span class="badge badge--outline" title="Came with the site. It can be edited or hidden, but not deleted.">Seeded</span><?php endif; ?>
                        <?php if ($meetingId > 0): ?><span class="badge badge--info" title="This date belongs to a meeting. Change it on the meeting page.">Meeting</span><?php endif; ?>
                    </td>
                    <td class="col-actions" data-label="">
                        <div class="table__actions">
                            <a class="btn btn--outline btn--sm" href="<?= e($editUrl) ?>" aria-label="<?= $meetingId > 0 ? 'Open the meeting' : 'Edit' ?> <?= e($e['title']) ?>"><?= icon($meetingId > 0 ? 'users' : 'edit', 'icon--sm') ?> <span class="cal-btn-text"><?= $meetingId > 0 ? 'Open meeting' : 'Edit' ?></span></a>
                            <?php if ($meetingId === 0): ?>
                            <form method="post" action="<?= e(url('admin/calendar/' . $id . '/duplicate')) ?>" class="inline-form"><?= csrf_field() ?>
                                <button class="btn btn--ghost btn--sm" type="submit" aria-label="Make a copy of <?= e($e['title']) ?>"><?= icon('copy', 'icon--sm') ?> <span class="cal-btn-text">Copy</span></button></form>
                            <form method="post" action="<?= e(url('admin/calendar/' . $id . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?>
                                <button class="btn btn--ghost btn--sm" type="submit" aria-label="<?= $off ? 'Show' : 'Hide' ?> <?= e($e['title']) ?>"><?= icon($off ? 'eye' : 'eye-off', 'icon--sm') ?> <span class="cal-btn-text"><?= $off ? 'Show' : 'Hide' ?></span></button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
<?php endif; ?>
