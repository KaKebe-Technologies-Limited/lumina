<?php
/**
 * Admin > Calendar > Holidays & days. The two lists that came with the site (Uganda public holidays, international / UN days) with the NEXT date of each.
 * The important job: CONFIRM the moving dates. Eid al-Fitr and Eid al-Adha follow the moon; the calendar shows a prediction marked "Date to be
 * confirmed" until someone presses "Confirm date" here (one occurrence only, the rule is not touched).
 * Vars: $available, $holidays / $days (CalendarService::systemEntries rows: entry, type, next, unconfirmed, days_until), $toConfirm (occurrences within
 * 60 days that still need confirming), $extra (holidays the team added itself: entry, next), $today, $islamic.
 * "Confirm date" posts to admin/calendar/{id}/occurrence: action=confirm, occurrence_date = the PREDICTED day (the rule's day), new_date = the day announced.
 */
use App\Services\Calendar\CivilDate;
use App\Services\Calendar\RecurrenceRules;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-calendar.js')) ?>" defer></script>
<?php $this->end(); ?>
<?php
$in = static function (?int $days): string {
    return $days === null ? '' : ($days === 0 ? 'today' : ($days === 1 ? 'tomorrow' : 'in ' . $days . ' days'));
};
$confirmForm = static function (array $o, string $idp) use ($today): string {
    ob_start(); ?>
    <form method="post" action="<?= e(url('admin/calendar/' . (int) $o['entry_id'] . '/occurrence')) ?>" class="cal-confirm__form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="confirm"><input type="hidden" name="return" value="holidays">
        <input type="hidden" name="occurrence_date" value="<?= e($o['occurrence_date']) ?>">
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>">Date announced</label>
            <input class="input" id="<?= e($idp) ?>" type="date" name="new_date" value="<?= e($o['start_date']) ?>" required>
        </div>
        <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', 'icon--sm') ?> Confirm date</button>
    </form>
    <?php return (string) ob_get_clean();
};
?>
<div class="admin-calendar cal-holidays">
    <div class="page-header">
        <div>
            <h1 class="page-header__title">Holidays &amp; days</h1>
            <p class="page-header__subtitle">Uganda public holidays follow the Public Holidays Act. Eid al-Fitr and Eid al-Adha depend on the moon, so confirm each date when it is announced. The international days are the ones the foundation highlights; hide any you do not want.</p>
        </div>
        <div class="page-header__actions">
            <a class="btn btn--primary" href="<?= e(url('admin/calendar/create', ['type' => 'uganda-holiday'])) ?>"><?= icon('plus', 'icon--sm') ?> Add an announced holiday</a>
            <a class="btn btn--outline" href="<?= e(url('admin/calendar')) ?>"><?= icon('calendar', 'icon--sm') ?> Back to the calendar</a>
        </div>
    </div>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The calendar tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <?php if (error('_') !== null): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><?= e((string) error('_')) ?></div></div><?php endif; ?>

        <?php if ($toConfirm !== []): ?>
            <section class="card cal-confirm cal-confirm--due" id="confirm" aria-labelledby="confirm-title">
                <div class="card__header">
                    <h2 class="card__title" id="confirm-title"><?= icon('alert-triangle', 'icon--sm') ?> <?= count($toConfirm) ?> <?= count($toConfirm) === 1 ? 'date needs' : 'dates need' ?> confirming in the next 60 days</h2>
                </div>
                <div class="card__body">
                    <p>These dates are only predictions. Visitors see <strong>"Date to be confirmed"</strong> next to them until you confirm. Check the date announced by the Uganda Muslim Supreme Council or the government, type it in if it is different from the prediction, and press <strong>Confirm date</strong>.</p>
                    <ul class="cal-confirm__list">
                        <?php foreach ($toConfirm as $i => $o): $left = CivilDate::diffDays($today, $o['start_date']); ?>
                            <li class="cal-confirm__item">
                                <div>
                                    <strong class="cal-confirm__name"><?= e($o['title']) ?></strong>
                                    <span class="cal-confirm__pred">Predicted <?= e(CivilDate::fmt($o['start_date'], 'l j F Y')) ?> (<?= e($in(max(0, $left))) ?>)</span>
                                </div>
                                <?= $confirmForm($o, 'cf-' . $i) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert--success" role="status"><?= icon('check-circle') ?><div class="alert__body"><strong class="alert__title">Nothing to confirm in the next 60 days.</strong>
                <?php
                $nextTbc = null;
                foreach ($holidays as $h) {
                    if ($h['unconfirmed'] && ($nextTbc === null || $h['next']['start_date'] < $nextTbc['next']['start_date'])) {
                        $nextTbc = $h;
                    }
                }
                ?>
                <?= $nextTbc ? 'The next moon-sighting date is ' . e($nextTbc['entry']['title']) . ' (predicted ' . e(CivilDate::fmt($nextTbc['next']['start_date'], 'D j M Y')) . '). A badge appears in the menu when one is within 60 days.' : 'All upcoming moving dates are confirmed.' ?></div></div>
        <?php endif; ?>

        <section class="card" id="upcoming" aria-labelledby="hol-title">
            <div class="card__header">
                <h2 class="card__title" id="hol-title">Uganda public holidays</h2>
                <span class="text-muted cal-hint"><?= count($holidays) ?> holidays &middot; next date of each</span>
            </div>
            <div class="table-wrap">
                <table class="table table--stack cal-holiday-table">
                    <thead><tr><th scope="col">Holiday</th><th scope="col">Next date</th><th scope="col">Status</th><th scope="col">Confirm or change</th><th scope="col" class="col-actions"><span class="visually-hidden">More</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($holidays as $h):
                        $e = $h['entry'];
                        $n = $h['next'];
                        $id = (int) $e['id'];
                        $off = (int) $e['is_active'] !== 1;
                        $soon = $h['unconfirmed'] && $h['days_until'] !== null && $h['days_until'] <= 60;
                        ?>
                        <tr class="<?= $off ? 'is-off' : '' ?><?= $soon ? ' is-due' : '' ?>">
                            <td data-label="Holiday">
                                <span class="cell-title"><a href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>"><?= e($e['title']) ?></a></span>
                                <span class="cell-sub"><?= e(RecurrenceRules::describe($e)) ?></span>
                                <?php if ($off): ?><span class="badge badge--neutral">Hidden</span><?php endif; ?>
                            </td>
                            <td data-label="Next date" class="nowrap">
                                <?php if ($n): ?>
                                    <strong><?= e(CivilDate::fmt($n['start_date'], 'D j M Y')) ?></strong>
                                    <span class="cell-sub"><?= e($in($h['days_until'])) ?></span>
                                    <?php if ($n['moved']): ?><span class="cell-sub">Moved from <?= e(CivilDate::fmt($n['occurrence_date'], 'D j M')) ?></span><?php endif; ?>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if ($n && $n['needs_confirmation']): ?><span class="badge badge--warning">Date to be confirmed</span>
                                <?php elseif ($n && $n['confirmed']): ?><span class="badge badge--success">Confirmed</span>
                                <?php elseif ((int) $e['needs_confirmation'] === 1): ?><span class="badge badge--neutral">Not set</span>
                                <?php else: ?><span class="badge badge--neutral">Fixed date</span><?php endif; ?>
                                <?php if ($n && !empty($n['note'])): ?><span class="cell-sub">Note: <?= e($n['note']) ?></span><?php endif; ?>
                            </td>
                            <td data-label="Confirm or change" class="cal-holiday-actions">
                                <?php if ($n && $n['needs_confirmation']): ?>
                                    <?= $confirmForm($n, 'cf-h' . $id) ?>
                                <?php elseif ($n): ?>
                                    <details class="cal-change">
                                        <summary>Change this year's date</summary>
                                        <form method="post" action="<?= e(url('admin/calendar/' . $id . '/occurrence')) ?>" class="cal-change__form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="move"><input type="hidden" name="return" value="holidays">
                                            <input type="hidden" name="occurrence_date" value="<?= e($n['occurrence_date']) ?>">
                                            <div class="field"><label class="field__label" for="mv-<?= $id ?>">New date</label>
                                                <input class="input" id="mv-<?= $id ?>" type="date" name="new_date" value="<?= e($n['start_date']) ?>" required></div>
                                            <div class="field"><label class="field__label" for="mn-<?= $id ?>">Note shown with the date (optional)</label>
                                                <input class="input" id="mn-<?= $id ?>" type="text" name="note" maxlength="300" placeholder="For example: moved by a government notice"></div>
                                            <button class="btn btn--outline btn--sm" type="submit">Move this holiday</button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                                <?php if ($n && !empty($n['override_id'])): ?>
                                    <form method="post" action="<?= e(url('admin/calendar/' . $id . '/occurrence')) ?>" class="inline-form">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="return" value="holidays">
                                        <input type="hidden" name="occurrence_date" value="<?= e($n['occurrence_date']) ?>">
                                        <button class="btn btn--ghost btn--sm" type="submit" data-confirm="Go back to the usual date for this holiday? The confirmed or moved date will be forgotten."><?= icon('history', 'icon--sm') ?> Undo the change</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="col-actions" data-label="">
                                <div class="table__actions">
                                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>" aria-label="Edit <?= e($e['title']) ?>"><?= icon('edit', 'icon--sm') ?> <span class="cal-btn-text">Edit</span></a>
                                    <form method="post" action="<?= e(url('admin/calendar/' . $id . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="<?= $off ? 'Show' : 'Hide' ?> <?= e($e['title']) ?>"><?= icon($off ? 'eye' : 'eye-off', 'icon--sm') ?> <span class="cal-btn-text"><?= $off ? 'Show' : 'Hide' ?></span></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card__footer cal-note">
                <?= icon('info', 'icon--sm') ?>
                <p>The calendar shows the date written in the Public Holidays Act. If a holiday falls on a Sunday and the government announces another day off, use <strong>Change this year's date</strong> or <strong>Add an announced holiday</strong> (the president can also declare extra holidays, for example for elections).</p>
            </div>
        </section>

        <?php if ($extra !== []): ?>
        <section class="card" id="extra" aria-labelledby="extra-title">
            <div class="card__header"><h2 class="card__title" id="extra-title">Extra holidays you added</h2><span class="text-muted cal-hint">Days the government announced, added by your team</span></div>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead><tr><th scope="col">Holiday</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($extra as $x):
                        $e = $x['entry'];
                        $n = $x['next'];
                        $id = (int) $e['id'];
                        $off = (int) $e['is_active'] !== 1;
                        ?>
                        <tr class="<?= $off ? 'is-off' : '' ?>">
                            <td data-label="Holiday"><span class="cell-title"><a href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>"><?= e($e['title']) ?></a></span><span class="cell-sub"><?= e(RecurrenceRules::describe($e)) ?></span></td>
                            <td data-label="Date" class="nowrap"><?= e(CivilDate::fmt($n ? $n['start_date'] : (string) $e['start_date'], 'D j M Y')) ?><?= $n ? '' : '<span class="cell-sub">Over</span>' ?></td>
                            <td data-label="Status"><?= $off ? '<span class="badge badge--neutral">Hidden</span>' : ((int) $e['is_public'] === 1 ? '<span class="badge badge--success">On the website</span>' : '<span class="badge badge--neutral">Team only</span>') ?>
                                <?= $n && $n['needs_confirmation'] ? '<span class="badge badge--warning">Date to be confirmed</span>' : '' ?></td>
                            <td class="col-actions" data-label="">
                                <div class="table__actions">
                                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>" aria-label="Edit <?= e($e['title']) ?>"><?= icon('edit', 'icon--sm') ?> <span class="cal-btn-text">Edit</span></a>
                                    <form method="post" action="<?= e(url('admin/calendar/' . $id . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="<?= $off ? 'Show' : 'Hide' ?> <?= e($e['title']) ?>"><?= icon($off ? 'eye' : 'eye-off', 'icon--sm') ?> <span class="cal-btn-text"><?= $off ? 'Show' : 'Hide' ?></span></button></form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <section class="card" id="days" aria-labelledby="days-title">
            <div class="card__header">
                <h2 class="card__title" id="days-title">International and UN days (<?= count($days) ?>)</h2>
                <div class="search-input cal-days-search"><?= icon('search', 'icon--sm') ?><label class="visually-hidden" for="days-filter">Search the days</label>
                    <input class="input" id="days-filter" type="search" placeholder="Search the days" data-table-filter="#cal-days"></div>
            </div>
            <div class="table-wrap">
                <table class="table table--stack" id="cal-days">
                    <thead><tr><th scope="col">Day</th><th scope="col">Next date</th><th scope="col">Source</th><th scope="col">On the calendar</th><th scope="col" class="col-actions"><span class="visually-hidden">Edit</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($days as $d):
                        $e = $d['entry'];
                        $id = (int) $e['id'];
                        $off = (int) $e['is_active'] !== 1;
                        ?>
                        <tr class="<?= $off ? 'is-off' : '' ?>">
                            <td data-label="Day"><span class="cell-title"><a href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>"><?= e($e['title']) ?></a></span><span class="cell-sub"><?= e(RecurrenceRules::describe($e)) ?></span></td>
                            <td data-label="Next date" class="nowrap"><?= $d['next'] ? '<strong>' . e(CivilDate::fmt($d['next']['start_date'], 'D j M Y')) . '</strong><span class="cell-sub">' . e($in($d['days_until'])) . '</span>' : '-' ?></td>
                            <td data-label="Source">
                                <?php if (!empty($e['source_url'])): ?><a href="<?= e($e['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) ($e['source_label'] ?: 'Source')) ?></a>
                                <?php else: ?><?= e((string) $e['source_label']) ?><?php endif; ?>
                            </td>
                            <td data-label="On the calendar">
                                <form method="post" action="<?= e(url('admin/calendar/' . $id . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?>
                                    <button class="cal-toggle<?= $off ? '' : ' is-on' ?>" type="submit" aria-pressed="<?= $off ? 'false' : 'true' ?>" title="<?= $off ? 'Click to show this day again' : 'Click to hide this day' ?>">
                                        <span class="badge badge--<?= $off ? 'neutral' : 'success' ?> badge--dot"><?= $off ? 'Hidden' : 'Shown' ?></span>
                                        <span class="visually-hidden"> - <?= e($e['title']) ?>. <?= $off ? 'Press to show' : 'Press to hide' ?></span>
                                    </button>
                                </form>
                            </td>
                            <td class="col-actions" data-label=""><a class="btn btn--outline btn--sm" href="<?= e(url('admin/calendar/' . $id . '/edit')) ?>" aria-label="Edit <?= e($e['title']) ?>"><?= icon('edit', 'icon--sm') ?> <span class="cal-btn-text">Edit</span></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
