<?php
/**
 * Admin > Meeting requests (list): the inbox of the Contact page "Request a meeting" form. OWNER: ADMIN agent.
 * Vars: $available, $list (items = meeting_requests rows, total, page, pages, per_page), $counts (per status + all), $f (status, q), $canDelete.
 */
use App\Controllers\Admin\MeetingRequestsController as C;
use App\Services\Calendar\CivilDate;
use App\Services\Meetings\MeetingRequestService as RS;

$items = $list['items'];
$tabs = [['key' => '', 'label' => 'All', 'count' => $counts['all']], ['key' => 'new', 'label' => 'New', 'count' => $counts['new']], ['key' => 'approved', 'label' => 'Approved', 'count' => $counts['approved']],
    ['key' => 'declined', 'label' => 'Declined', 'count' => $counts['declined']], ['key' => 'closed', 'label' => 'Closed', 'count' => $counts['closed']]];
$actions = [['label' => 'Schedule a meeting', 'url' => url('admin/meetings/create'), 'icon' => 'plus'], ['label' => 'All meetings', 'url' => url('admin/meetings'), 'class' => 'btn--outline', 'icon' => 'calendar']];
if ($available && $counts['all'] > 0) {
    $actions[] = ['label' => 'Export CSV', 'url' => url('admin/meeting-requests/export.csv', array_filter(['status' => $f['status'], 'q' => $f['q']])), 'class' => 'btn--ghost', 'icon' => 'download'];
}
$filtered = $f['q'] !== '' || $f['status'] !== '';
?>
<div class="meetings-admin ma-page ma-page--requests">
    <?= partial('partials/admin/page-header', ['title' => 'Meeting requests', 'subtitle' => 'People who asked to meet the team through the Contact page. Nothing is booked until you schedule it.', 'actions' => $actions]) ?>

    <?php if (!$available): ?>
        <div class="alert alert--warning" role="status"><?= icon('alert-triangle') ?><div class="alert__body">The meeting tables are missing from the database. Import <code>database/lumina-live-import.sql</code> first.</div></div>
    <?php else: ?>
        <nav class="tabs" aria-label="Filter requests by status">
            <?php foreach ($tabs as $t): $on = $t['key'] === $f['status']; ?>
                <a class="tabs__item<?= $on ? ' is-active' : '' ?>" href="<?= e(url('admin/meeting-requests', array_filter(['status' => $t['key'], 'q' => $f['q']]))) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($t['label']) ?> <span class="inbox-count"><?= (int) $t['count'] ?></span></a>
            <?php endforeach; ?>
        </nav>

        <form class="filters" method="get" action="<?= e(url('admin/meeting-requests')) ?>" role="search">
            <?php if ($f['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($f['status']) ?>"><?php endif; ?>
            <div class="field field--grow">
                <label class="field__label" for="r-q">Search</label>
                <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="r-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, e-mail, organisation or topic" maxlength="100"></div>
            </div>
            <div class="filters__actions">
                <button class="btn btn--primary" type="submit"><?= icon('filter', 'icon--sm') ?> Search</button>
                <?php if ($filtered): ?><a class="btn btn--ghost" href="<?= e(url('admin/meeting-requests')) ?>">Reset</a><?php endif; ?>
            </div>
        </form>

        <?php if ($items === []): ?>
            <?= partial('partials/empty-state', [
                'icon' => 'inbox', 'title' => $filtered ? 'No requests match' : 'No requests yet',
                'text' => $filtered ? 'Try a different search or tab.' : 'When a visitor asks to meet the team through the Contact page, the request appears here. You choose whether to schedule it.',
                'actions' => $filtered ? [['label' => 'Show all requests', 'url' => url('admin/meeting-requests'), 'class' => 'btn--outline']] : [],
            ]) ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack ma-table">
                    <caption class="visually-hidden">Meeting requests</caption>
                    <thead><tr><th scope="col">Who</th><th scope="col">Topic</th><th scope="col">Preferred</th><th scope="col">Status</th><th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $r):
                        $rid = (int) $r['id'];
                        $isNew = $r['status'] === 'new';
                        $erased = str_starts_with((string) $r['email'], 'erased-');
                        ?>
                        <tr<?= $isNew ? ' class="is-unread"' : '' ?>>
                            <td data-label="Who"><div class="ma-cell">
                                <span class="cell-title"><a href="<?= e(url('admin/meeting-requests/' . $rid)) ?>"><?= e((string) $r['name']) ?></a></span>
                                <span class="cell-sub"><?= $erased ? 'Personal data erased' : e((string) $r['email']) ?><?= !empty($r['organisation']) ? ' &middot; ' . e((string) $r['organisation']) : '' ?></span>
                                <span class="cell-sub">Received <?= e(format_datetime((string) $r['created_at'], 'j M Y, H:i')) ?></span>
                            </div></td>
                            <td data-label="Topic"><div class="ma-cell"><?= e((string) $r['topic']) ?><span class="cell-sub"><?= e(mb_strimwidth((string) $r['message'], 0, 90, '...')) ?></span></div></td>
                            <td data-label="Preferred"><div class="ma-cell">
                                <?= !empty($r['preferred_date']) ? e(CivilDate::fmt((string) $r['preferred_date'], 'D j M Y')) : '<span class="cell-sub">No date given</span>' ?>
                                <span class="cell-sub"><?= e(C::TIME_LABELS[$r['preferred_time'] ?? 'any'] ?? 'Any time of day') ?><?= !empty($r['duration_minutes']) ? ' &middot; ' . (int) $r['duration_minutes'] . ' min' : '' ?><?= !empty($r['platform_pref']) ? ' &middot; ' . e(C::PREF_LABELS[$r['platform_pref']] ?? '') : '' ?></span>
                            </div></td>
                            <td data-label="Status"><div class="ma-cell"><?= partial('partials/admin/status-badge', ['status' => (string) $r['status'], 'label' => RS::STATUS_LABELS[$r['status']] ?? (string) $r['status'], 'tone' => RS::STATUS_TONES[$r['status']] ?? 'neutral']) ?>
                                <?php if (!empty($r['meeting_id'])): ?><span class="cell-sub"><a href="<?= e(url('admin/meetings/' . (int) $r['meeting_id'])) ?>">Meeting scheduled</a></span><?php endif; ?></div></td>
                            <td class="col-actions" data-label="">
                                <div class="table__actions">
                                    <?php if (!$erased && empty($r['meeting_id']) && in_array($r['status'], ['new', 'approved'], true)): ?>
                                        <a class="btn btn--primary btn--sm" href="<?= e(url('admin/meetings/create', ['request' => $rid])) ?>" aria-label="Schedule a meeting for <?= e((string) $r['name']) ?>"><?= icon('calendar', 'icon--sm') ?> <span class="ma-btn-text">Schedule</span></a>
                                    <?php endif; ?>
                                    <a class="btn btn--outline btn--sm" href="<?= e(url('admin/meeting-requests/' . $rid)) ?>" aria-label="Open the request from <?= e((string) $r['name']) ?>">Open</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="result-count"><?= (int) $list['total'] ?> <?= $list['total'] === 1 ? 'request' : 'requests' ?></p>
            <?php $pg = new \App\Core\Pagination((int) $list['total'], (int) $list['page'], (int) $list['per_page'], (int) $list['pages'], ((int) $list['page'] - 1) * (int) $list['per_page']); ?>
            <?= partial('partials/pagination', ['pagination' => $pg]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
