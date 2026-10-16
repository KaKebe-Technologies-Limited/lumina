<?php
/**
 * Admin > Events list. OWNER: B8. Vars: $pager, $q, $sort, $dir, $when, $status, $reg, $filtered, $counts (total/upcoming/past).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-org.js')) ?>" defer></script>
<?php $this->end();
$aria = static fn(string $col): string => $sort === $col ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
$tabUrl = static function (string $w) use ($q, $status, $reg, $pager): string {
    $query = array_filter(['when' => $w, 'q' => $q, 'status' => $status, 'reg' => $reg, 'per_page' => (int) $pager->perPage !== 20 ? (string) $pager->perPage : ''], static fn($v): bool => $v !== '');
    return url('admin/events', $query);
};
$now = time();
?>
<div class="admin-org">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Events',
        'subtitle' => 'Events and workshops shown on the public Events page, with optional online registration.',
        'actions'  => [['label' => 'Add event', 'url' => url('admin/events/create'), 'icon' => 'plus']],
    ]) ?>

    <?php if ($counts['total'] === 0): ?>
        <?= partial('partials/empty-state', [
            'title'   => 'No events yet',
            'text'    => 'Create an event to announce a workshop, training or community day. Until at least one active event exists, the public Events page and its menu entry stay hidden.',
            'icon'    => 'calendar',
            'actions' => [['label' => 'Add the first event', 'url' => url('admin/events/create')]],
        ]) ?>
    <?php else: ?>
        <nav class="tabs" aria-label="Show events">
            <a class="tabs__item<?= $when === '' ? ' is-active' : '' ?>" href="<?= e($tabUrl('')) ?>"<?= $when === '' ? ' aria-current="page"' : '' ?>>All (<?= (int) $counts['total'] ?>)</a>
            <a class="tabs__item<?= $when === 'upcoming' ? ' is-active' : '' ?>" href="<?= e($tabUrl('upcoming')) ?>"<?= $when === 'upcoming' ? ' aria-current="page"' : '' ?>>Upcoming (<?= (int) $counts['upcoming'] ?>)</a>
            <a class="tabs__item<?= $when === 'past' ? ' is-active' : '' ?>" href="<?= e($tabUrl('past')) ?>"<?= $when === 'past' ? ' aria-current="page"' : '' ?>>Past (<?= (int) $counts['past'] ?>)</a>
        </nav>

        <form class="filters" method="get" action="<?= e(url('admin/events')) ?>" role="search" aria-label="Filter events">
            <?= partial('admin/org/_search', ['q' => $q, 'placeholder' => 'Title, location or summary']) ?>
            <div class="field">
                <label class="field__label" for="status">Status</label>
                <select class="select" id="status" name="status" data-autosubmit>
                    <option value="">Any status</option>
                    <option value="active"<?= $status === 'active' ? ' selected' : '' ?>>Active</option>
                    <option value="inactive"<?= $status === 'inactive' ? ' selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="field">
                <label class="field__label" for="reg">Registration</label>
                <select class="select" id="reg" name="reg" data-autosubmit>
                    <option value="">Open or closed</option>
                    <option value="open"<?= $reg === 'open' ? ' selected' : '' ?>>Registration open</option>
                    <option value="closed"<?= $reg === 'closed' ? ' selected' : '' ?>>Registration closed</option>
                </select>
            </div>
            <?= partial('admin/org/_filter-end', ['pager' => $pager, 'sort' => $sort, 'dir' => $dir, 'filtered' => $filtered || $when !== '', 'resetUrl' => url('admin/events'), 'keep' => ['when' => $when]]) ?>
        </form>

        <?php if ($pager->items === []): ?>
            <?= partial('partials/empty-state', [
                'title'   => 'No events match your filters',
                'text'    => 'Try a different search term or clear the filters.',
                'icon'    => 'search',
                'actions' => [['label' => 'Clear filters', 'url' => url('admin/events'), 'class' => 'btn--outline']],
            ]) ?>
        <?php else: ?>
            <?php
            $bulk = [
                ['value' => 'activate', 'label' => 'Show on website (activate)'],
                ['value' => 'deactivate', 'label' => 'Hide from website (deactivate)'],
                ['value' => 'open', 'label' => 'Open registration'],
                ['value' => 'close', 'label' => 'Close registration'],
            ];
            if (can('content.delete')) {
                $bulk[] = ['value' => 'delete', 'label' => 'Delete', 'confirm' => 'Delete the {n} selected event(s)? Their registrations are deleted too. This cannot be undone.'];
            }
            ?>
            <?= partial('admin/org/_bulk-bar', ['action' => url('admin/events/bulk'), 'options' => $bulk, 'noun' => 'events']) ?>

            <div class="table-wrap">
                <table class="table table--stack" id="events-table">
                    <caption class="visually-hidden">Events</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="col-narrow"><input type="checkbox" data-bulk-all aria-label="Select all events on this page"></th>
                            <th scope="col" aria-sort="<?= e($aria('title')) ?>"><?= partial('admin/org/_sort', ['label' => 'Event', 'col' => 'title', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('date')) ?>"><?= partial('admin/org/_sort', ['label' => 'Date', 'col' => 'date', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('registrations')) ?>"><?= partial('admin/org/_sort', ['label' => 'Registrations', 'col' => 'registrations', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" aria-sort="<?= e($aria('status')) ?>"><?= partial('admin/org/_sort', ['label' => 'Status', 'col' => 'status', 'sort' => $sort, 'dir' => $dir]) ?></th>
                            <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pager->items as $ev):
                            $isOn = (int) $ev['is_active'] === 1;
                            $startTs = strtotime((string) $ev['start_at']);
                            $endTs = strtotime((string) ($ev['end_at'] ?: $ev['start_at']));
                            $isPast = $endTs < $now;
                            $regCount = (int) $ev['reg_count'];
                            $cap = $ev['capacity'] !== null ? (int) $ev['capacity'] : null;
                            $full = $cap !== null && $regCount >= $cap;
                            $regOpen = (int) $ev['registration_enabled'] === 1;
                            $regUrl = url('admin/events/' . (int) $ev['id'] . '/registrations');
                        ?>
                            <tr>
                                <td class="col-narrow" data-label="Select"><input type="checkbox" name="ids[]" value="<?= (int) $ev['id'] ?>" form="bulk-form" data-bulk-item aria-label="Select <?= e($ev['title']) ?>"></td>
                                <td data-label="Event"><div class="org-stack">
                                    <a class="cell-title" href="<?= e(url('admin/events/' . (int) $ev['id'] . '/edit')) ?>"><?= e($ev['title']) ?></a>
                                    <?php if (!empty($ev['location'])): ?><span class="cell-sub"><?= icon('map-pin', 'icon--sm') ?> <?= e($ev['location']) ?></span><?php endif; ?></div></td>
                                <td data-label="Date"><div class="org-stack">
                                    <time datetime="<?= e(date('c', $startTs)) ?>"><?= e(date('D j M Y, H:i', $startTs)) ?></time>
                                    <?php if (!empty($ev['end_at'])): ?><span class="cell-sub">until <?= e(date('Y-m-d', $startTs) === date('Y-m-d', $endTs) ? date('H:i', $endTs) : date('D j M Y, H:i', $endTs)) ?></span><?php endif; ?></div></td>
                                <td data-label="Registrations"><div class="org-stack">
                                    <?php if ($regOpen || (int) $ev['reg_total'] > 0): ?>
                                        <?php if (can('inbox.manage')): ?>
                                            <a href="<?= e($regUrl) ?>"><strong><?= $regCount ?></strong><?= $cap !== null ? ' / ' . $cap : '' ?> registered</a>
                                        <?php else: ?>
                                            <strong><?= $regCount ?></strong><?= $cap !== null ? ' / ' . $cap : '' ?> registered
                                        <?php endif; ?>
                                        <span class="cell-sub">
                                            <?= $regOpen ? 'Registration open' : 'Registration closed' ?><?= $full ? ' &middot; full' : '' ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Registration off</span>
                                    <?php endif; ?></div></td>
                                <td data-label="Status">
                                    <span class="org-badges">
                                        <?= partial('partials/admin/status-badge', ['status' => $isOn ? 'active' : 'inactive', 'label' => $isOn ? 'Active' : 'Inactive']) ?>
                                        <span class="badge <?= $isPast ? 'badge--neutral' : 'badge--info' ?>"><?= $isPast ? 'Past' : 'Upcoming' ?></span>
                                    </span>
                                </td>
                                <td class="col-actions" data-label="Actions">
                                    <div class="table__actions">
                                        <?php if (can('inbox.manage')): ?>
                                            <a class="btn btn--ghost btn--sm" href="<?= e($regUrl) ?>" title="Registrations for <?= e($ev['title']) ?>"><?= icon('users', 'icon--sm') ?><span class="visually-hidden">Registrations for <?= e($ev['title']) ?></span></a>
                                        <?php endif; ?>
                                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/events/' . (int) $ev['id'] . '/edit')) ?>" title="Edit <?= e($ev['title']) ?>"><?= icon('edit', 'icon--sm') ?><span class="visually-hidden">Edit <?= e($ev['title']) ?></span></a>
                                        <?= partial('admin/org/_row-action', [
                                            'action' => url('admin/events/' . (int) $ev['id'] . '/toggle'),
                                            'label'  => ($isOn ? 'Hide ' : 'Show ') . $ev['title'] . ($isOn ? ' from the website' : ' on the website'),
                                            'icon'   => $isOn ? 'eye-off' : 'eye',
                                        ]) ?>
                                        <?php if (can('content.delete')): ?>
                                            <?= partial('admin/org/_row-action', [
                                                'action'  => url('admin/events/' . (int) $ev['id'] . '/delete'),
                                                'label'   => 'Delete ' . $ev['title'],
                                                'icon'    => 'trash',
                                                'class'   => 'btn--ghost btn--sm org-danger',
                                                'confirm' => 'Delete "' . $ev['title'] . '"?' . ((int) $ev['reg_total'] > 0 ? ' Its ' . (int) $ev['reg_total'] . ' registration(s) will be deleted too.' : '') . ' This cannot be undone.',
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= partial('admin/org/_list-footer', ['pager' => $pager]) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
