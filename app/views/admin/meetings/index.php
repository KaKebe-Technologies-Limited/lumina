<?php
/**
 * Admin > Meetings (list). OWNER: ADMIN agent. Status tabs with counts, "upcoming / past / all", search, one clear call to action.
 * Vars: $available, $list (items, total, page, pages, per_page; each item = meetings row + n_attendees n_accepted n_declined n_tentative n_failed n_queued), $counts (per status + all),
 *       $f (status, q, when), $newRequests, $today.
 */
use App\Models\Meeting;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\MeetingService as MS;
use App\Services\Meetings\MeetingTime;

$items = $list['items'];
$tabs = [['key' => '', 'label' => 'All', 'count' => $counts['all']], ['key' => 'scheduled', 'label' => 'Scheduled', 'count' => $counts['scheduled']], ['key' => 'draft', 'label' => 'Drafts', 'count' => $counts['draft']],
    ['key' => 'completed', 'label' => 'Completed', 'count' => $counts['completed']], ['key' => 'cancelled', 'label' => 'Cancelled', 'count' => $counts['cancelled']]];
$whens = ['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All dates'];
$rel = static function (string $date) use ($today): string {
    $d = CivilDate::diffDays($today, $date);
    return $d === 0 ? 'Today' : ($d === 1 ? 'Tomorrow' : ($d > 1 && $d <= 60 ? 'In ' . $d . ' days' : ($d === -1 ? 'Yesterday' : '')));
};
$actions = [
    ['label' => 'Schedule a meeting', 'url' => url('admin/meetings/create'), 'icon' => 'plus'],
    ['label' => 'Meeting requests' . ($newRequests > 0 ? ' (' . $newRequests . ' new)' : ''), 'url' => url('admin/meeting-requests'), 'class' => 'btn--outline', 'icon' => 'inbox'],
    ['label' => 'Calendar', 'url' => url('admin/calendar'), 'class' => 'btn--ghost', 'icon' => 'calendar'],
];
$filtered = $f['q'] !== '' || $f['status'] !== '';
?>
<div class="meetings-admin ma-page ma-page--list">
    <?= partial('partials/admin/page-header', ['title' => 'Meetings', 'subtitle' => 'Schedule a meeting, invite people by e-mail and see who is coming. All times are East Africa Time.', 'actions' => $actions]) ?>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The meeting tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <nav class="tabs" aria-label="Filter meetings by status">
            <?php foreach ($tabs as $t): $on = $t['key'] === $f['status']; ?>
                <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('admin/meetings', array_filter(['status' => $t['key'], 'q' => $f['q']]))) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($t['label']) ?> <span class="inbox-count"><?= (int) $t['count'] ?></span></a>
            <?php endforeach; ?>
        </nav>

        <form class="filters" method="get" action="<?= e(url('admin/meetings')) ?>" role="search">
            <?php if ($f['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
            <div class="field field--grow">
                <label class="field__label" for="m-q">Search</label>
                <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="m-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Meeting title" maxlength="100"></div>
            </div>
            <div class="field"><label class="field__label" for="m-when">Dates</label>
                <select class="select" id="m-when" name="when" data-autosubmit><?php foreach ($whens as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['when'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="filters__actions">
                <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Filter</button>
                <?php if ($filtered || $f['when'] !== 'upcoming'): ?><a class="btn btn--ghost" href="<?= e(url('admin/meetings')) ?>">Reset</a><?php endif; ?>
            </div>
        </form>

        <?php if ($items === []): ?>
            <?php if ($counts['all'] === 0): ?>
                <div class="empty-state ma-empty" data-reveal>
                    <span class="empty-state__icon"><?= icon('calendar', 'icon--xl') ?></span>
                    <h2 class="empty-state__title">No meetings yet</h2>
                    <p class="empty-state__text">Scheduling takes about two minutes, and nothing is sent until you press the send button.</p>
                    <ol class="ma-howto">
                        <li><strong>Schedule it.</strong> Give it a title, a date and time, and paste a Zoom, Google Meet or Teams link (or make a free video room).</li>
                        <li><strong>Invite people.</strong> Type or paste e-mail addresses, or pick members and colleagues.</li>
                        <li><strong>See the answers.</strong> Everyone gets their own invitation with Accept, Maybe and Decline buttons, and a reminder before it starts.</li>
                    </ol>
                    <div class="empty-state__actions"><a class="btn btn--primary" href="<?= e(url('admin/meetings/create')) ?>"><?= icon('plus', 'icon--sm') ?> Schedule your first meeting</a></div>
                </div>
            <?php else: ?>
                <?= partial('partials/empty-state', ['icon' => 'calendar', 'title' => 'No meetings match', 'text' => $filtered ? 'Try a different search, or choose another tab.' : ($f['when'] === 'upcoming' ? 'There are no upcoming meetings. Choose "All dates" to see older ones.' : 'Nothing in this period.'),
                    'actions' => [['label' => 'Show all meetings', 'url' => url('admin/meetings', ['when' => 'all']), 'class' => 'btn--outline']]]) ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack ma-table">
                    <caption class="visually-hidden">Meetings</caption>
                    <thead><tr><th scope="col">Meeting</th><th scope="col">When (East Africa Time)</th><th scope="col">Status</th><th scope="col">People</th><th scope="col" class="col-actions"><span class="visually-hidden">Open</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $m):
                        $mid = (int) $m['id'];
                        $date = MeetingTime::date((string) $m['start_at']);
                        $r = $rel($date);
                        $plat = Meeting::PLATFORMS[$m['platform']] ?? 'Other';
                        ?>
                        <tr>
                            <td data-label="Meeting"><div class="ma-cell">
                                <span class="cell-title"><a href="<?= e(MS::adminUrl($mid)) ?>"><?= e((string) $m['title']) ?></a></span>
                                <span class="cell-sub"><?= e($plat) ?> &middot; <?= $m['visibility'] === 'public' ? 'on the public calendar' : 'private' ?></span>
                            </div></td>
                            <td data-label="When"><div class="ma-cell">
                                <strong class="ma-when-cell"><?= e(CivilDate::fmt($date, 'D j M Y')) ?></strong>
                                <span class="cell-sub"><?= e(MeetingTime::timeRange((string) $m['start_at'], (string) $m['end_at'])) ?><?= $r !== '' ? ' &middot; ' . e($r) : '' ?></span>
                            </div></td>
                            <td data-label="Status"><div class="ma-cell">
                                <?= partial('partials/admin/status-badge', ['status' => (string) $m['status'], 'label' => MS::STATUS_LABELS[$m['status']] ?? (string) $m['status'], 'tone' => MS::STATUS_TONES[$m['status']] ?? 'neutral']) ?>
                                <?php if ((int) $m['n_failed'] > 0): ?><span class="badge badge--danger"><?= (int) $m['n_failed'] ?> failed</span><?php endif; ?>
                                <?php if ((int) $m['n_queued'] > 0): ?><span class="badge badge--info"><?= (int) $m['n_queued'] ?> sending</span><?php endif; ?>
                            </div></td>
                            <td data-label="People"><div class="ma-cell">
                                <?php if ((int) $m['n_attendees'] === 0): ?>
                                    <span class="cell-sub">Nobody invited</span>
                                <?php else: ?>
                                    <strong><?= (int) $m['n_attendees'] ?></strong>
                                    <span class="cell-sub"><?= (int) $m['n_accepted'] ?> accepted<?= (int) $m['n_tentative'] > 0 ? ', ' . (int) $m['n_tentative'] . ' maybe' : '' ?><?= (int) $m['n_declined'] > 0 ? ', ' . (int) $m['n_declined'] . ' declined' : '' ?></span>
                                <?php endif; ?>
                            </div></td>
                            <td class="col-actions" data-label="">
                                <a class="btn btn--outline btn--sm" href="<?= e(MS::adminUrl($mid)) ?>" aria-label="Open <?= e((string) $m['title']) ?>">Open</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="result-count"><?= (int) $list['total'] ?> <?= $list['total'] === 1 ? 'meeting' : 'meetings' ?></p>
            <?php $pg = new \App\Core\Pagination((int) $list['total'], (int) $list['page'], (int) $list['per_page'], (int) $list['pages'], ((int) $list['page'] - 1) * (int) $list['per_page']); ?>
            <?= partial('partials/pagination', ['pagination' => $pg]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
