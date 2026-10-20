<?php
/**
 * Admin > Social media > Share log (ability social.share). OWNER: ADMIN agent.
 * Vars: $pagination, $rows (PostQueue::log rows: id newsletter_id channel share_version status attempts next_attempt_at last_attempt_at remote_id remote_url last_error posted_at
 * created_at caption newsletter_title issue_number), $filters, $counts, $attention, $canManage, $labels, $tones. No token, no payload: only what the platform returned.
 */
use App\Services\Social\Channels;

$total = array_sum(array_map('intval', $counts));
$isFiltered = $filters['channel'] !== '' || $filters['status'] !== '' || (int) ($filters['newsletter_id'] ?? 0) > 0;
$qs = static fn(array $over): string => url('admin/social/log') . ($over !== [] ? '?' . http_build_query(array_filter($over, static fn($v) => $v !== '' && $v !== 0 && $v !== null)) : '');
$actions = [
    ['retry', 'Retry', ['failed', 'skipped'], 'btn--outline'],
    ['skip', 'Skip', ['queued', 'failed', 'review'], 'btn--ghost'],
    ['mark-posted', 'Mark as posted', ['review', 'failed'], 'btn--outline'],
    ['post-anyway', 'Post anyway', ['review'], 'btn--outline'],
];
?>
<div class="asoc">
    <?= partial('partials/admin/page-header', ['title' => 'Share log', 'subtitle' => 'Every post made (or tried) when an issue was shared. Deleting an issue does not delete posts already made on the platforms.',
        'actions' => [['label' => 'Share kit', 'url' => url('admin/social/kit'), 'class' => 'btn--outline', 'icon' => 'image']]]) ?>

    <?php if ($attention['needs_token'] !== []): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">A channel needs a new access token:</strong>
        <?= e(implode(', ', array_map([Channels::class, 'label'], $attention['needs_token']))) ?>.<?php if ($canManage): ?> <a href="<?= e(url('admin/social/channels')) ?>">Open the channels</a><?php endif; ?></div></div><?php endif; ?>
    <?php if (($counts['review'] ?? 0) > 0): ?><div class="alert alert--warning" role="note"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title"><?= (int) $counts['review'] ?> post<?= (int) $counts['review'] === 1 ? '' : 's' ?> need a quick look.</strong>
        The connection broke while posting, so we cannot tell if the post went through. Open the account: if the post is there, press "Mark as posted"; if not, press "Post anyway". Nothing is posted twice by itself.</div></div><?php endif; ?>

    <nav class="asoc-tabs" aria-label="Filter by status">
        <a class="asoc-tab<?= $filters['status'] === '' ? ' is-active' : '' ?>" href="<?= e($qs(['channel' => $filters['channel'], 'issue' => (int) ($filters['newsletter_id'] ?? 0)])) ?>"<?= $filters['status'] === '' ? ' aria-current="page"' : '' ?>>All <span><?= (int) $total ?></span></a>
        <?php foreach ($labels as $k => $l): $n = (int) ($counts[$k] ?? 0); ?>
            <a class="asoc-tab asoc-tab--<?= e($tones[$k] ?? 'neutral') ?><?= $filters['status'] === $k ? ' is-active' : '' ?>" href="<?= e($qs(['status' => $k, 'channel' => $filters['channel'], 'issue' => (int) ($filters['newsletter_id'] ?? 0)])) ?>"<?= $filters['status'] === $k ? ' aria-current="page"' : '' ?>><?= e($l) ?> <span><?= $n ?></span></a>
        <?php endforeach; ?>
    </nav>

    <form method="get" action="<?= e(url('admin/social/log')) ?>" class="filters">
        <div class="field"><label class="field__label" for="f-channel">Channel</label>
            <select class="select" id="f-channel" name="channel"><option value="">All channels</option><?php foreach (Channels::ids() as $id): ?><option value="<?= e($id) ?>"<?= $filters['channel'] === $id ? ' selected' : '' ?>><?= e(Channels::label($id)) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label class="field__label" for="f-status">Status</label>
            <select class="select" id="f-status" name="status"><option value="">All statuses</option><?php foreach ($labels as $k => $l): ?><option value="<?= e($k) ?>"<?= $filters['status'] === $k ? ' selected' : '' ?>><?= e($l) ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option><?php endforeach; ?></select></div>
        <?php if ((int) ($filters['newsletter_id'] ?? 0) > 0): ?><input type="hidden" name="issue" value="<?= (int) $filters['newsletter_id'] ?>"><?php endif; ?>
        <div class="filters__actions"><button class="btn btn--outline" type="submit">Filter</button> <?php if ($isFiltered): ?><a class="btn btn--ghost" href="<?= e(url('admin/social/log')) ?>">Reset</a><?php endif; ?></div>
    </form>
    <?php if ((int) ($filters['newsletter_id'] ?? 0) > 0): ?><p class="asoc-filter-note">Showing the posts of one issue only. <a href="<?= e($qs(['channel' => $filters['channel'], 'status' => $filters['status']])) ?>">Show every issue</a></p><?php endif; ?>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => $isFiltered ? 'No posts match this filter' : 'Nothing has been shared yet', 'text' => $isFiltered ? 'Try another channel or status.' : 'When an issue is published with a channel switched on, its posts appear here.']) ?>
    <?php else: ?>
        <div class="table-wrap asoc-log-wrap"><table class="table asoc-log">
            <thead><tr><th>Issue</th><th>Channel</th><th>Status</th><th>When</th><th>Result</th><th class="col-actions">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): $s = (string) $r['status']; $ch = (string) $r['channel']; ?>
                <tr>
                    <td data-label="Issue"><div class="asoc-log__v"><a href="<?= e(url('admin/newsletters/' . (int) $r['newsletter_id'] . '/edit')) ?>"><?= e((string) ($r['newsletter_title'] ?? 'Issue #' . (int) $r['newsletter_id'])) ?></a>
                        <?= !empty($r['issue_number']) ? '<span class="cell-sub">Issue ' . e((string) $r['issue_number']) . '</span>' : '' ?>
                        <?= (int) $r['share_version'] > 1 ? ' <span class="badge badge--outline">post again #' . (int) $r['share_version'] . '</span>' : '' ?></div></td>
                    <td data-label="Channel"><div class="asoc-log__v"><span class="asoc-log__ch"><?= Channels::has($ch) ? icon(Channels::get($ch)->meta()['icon'], 'icon--sm') : '' ?> <?= e(Channels::label($ch)) ?></span></div></td>
                    <td data-label="Status"><div class="asoc-log__v"><?= partial('partials/admin/status-badge', ['status' => $s, 'label' => $labels[$s] ?? $s, 'tone' => $tones[$s] ?? 'neutral']) ?><?= (int) $r['attempts'] > 0 ? '<span class="cell-sub">' . (int) $r['attempts'] . ' attempt' . ((int) $r['attempts'] === 1 ? '' : 's') . '</span>' : '' ?></div></td>
                    <td data-label="When"><div class="asoc-log__v"><?= e((string) ($r['posted_at'] ?? $r['last_attempt_at'] ?? $r['created_at'])) ?><?= $s === 'queued' && !empty($r['next_attempt_at']) ? '<span class="cell-sub">next try ' . e((string) $r['next_attempt_at']) . '</span>' : '' ?></div></td>
                    <td data-label="Result"><div class="asoc-log__v"><?php if (!empty($r['remote_url'])): ?><a href="<?= e((string) $r['remote_url']) ?>" target="_blank" rel="noopener noreferrer">View post <?= icon('external-link', 'icon--sm') ?></a><?php elseif (!empty($r['remote_id'])): ?><span class="cell-sub">id <?= e((string) $r['remote_id']) ?></span><?php endif; ?>
                        <?php if (!empty($r['last_error'])): ?><span class="cell-sub asoc-log__err"><?= e((string) $r['last_error']) ?></span><?php endif; ?></div></td>
                    <td class="col-actions" data-label="Actions"><div class="asoc-log__v"><div class="table__actions">
                        <?php foreach ($actions as [$act, $lab, $when, $cls]): if (!in_array($s, $when, true)) { continue; } ?>
                            <form method="post" action="<?= e(url('admin/social/log/' . (int) $r['id'] . '/' . $act)) ?>"<?= $act === 'post-anyway' ? ' data-confirm="Only do this if you opened the account and the post is NOT there. Otherwise it may be posted twice. Continue?"' : ($act === 'skip' ? ' data-confirm="Skip this post? It will not be posted unless you retry it later."' : '') ?>><?= csrf_field() ?><button class="btn btn--sm <?= e($cls) ?>" type="submit"><?= e($lab) ?></button></form>
                        <?php endforeach; ?>
                    </div></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?= partial('partials/pagination', ['pagination' => $pagination]) ?>
    <?php endif; ?>
    <p class="asoc-foot">The log keeps only the status, the time, the link the platform returned and a short note. It never keeps a token or the full message.</p>
</div>
