<?php /** Admin > Academy > Challenge entries. Vars: $c, $rows, $status, $phase. */
use App\Services\Academy\ChallengeService;

$cid = (int) $c['id'];
$finalized = !empty($c['finalized_at']);
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => 'Entries: ' . (string) $c['title'], 'subtitle' => ChallengeService::KINDS[$c['kind']] . '. Status: ' . (ChallengeService::PHASES[$phase] ?? ''),
    'actions' => [['label' => 'Edit the challenge', 'url' => url('admin/academy/challenges/' . $cid . '/edit'), 'icon' => 'edit']] + ($c['scoring'] === 'judged' ? [['label' => 'Score projects', 'url' => url('admin/academy/challenges/' . $cid . '/judge'), 'icon' => 'check']] : [])]) ?>
<div class="card"><div class="card__body">
    <?php if ($finalized): ?>
        <p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> Results made <?= e(format_datetime($c['finalized_at'], 'j M Y, H:i')) ?>. The winners are on the <a href="<?= e(url('academy/winners')) ?>">winners page</a>.</p>
        <form method="post" action="<?= e(url('admin/academy/challenges/' . $cid . '/reopen')) ?>" class="inline-form" data-confirm="Reopen the results? The winners are cleared and the certificates of this challenge are withdrawn until you make the results again."><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Reopen the results</button></form>
    <?php else: ?>
        <p>The system marks the winners by itself <?= $c['scoring'] === 'quiz' ? 'when the quiz challenge ends' : 'when judging ends (' . e(format_datetime($c['judging_ends_at'] ?: $c['ends_at'], 'j M Y, H:i')) . ') and at least one project is scored' ?>. You can also make the results now.</p>
        <form method="post" action="<?= e(url('admin/academy/challenges/' . $cid . '/finalize')) ?>" class="inline-form" data-confirm="Make the results now? The entries are ranked, winners marked and certificates issued."><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit"><?= icon('award', 'icon--sm') ?> Make the results now</button></form>
    <?php endif; ?>
</div></div>
<nav class="tabs content-tabs" aria-label="Entry status">
    <a class="tabs__item<?= $status === '' ? ' is-active' : '' ?>" href="<?= e(url('admin/academy/challenges/' . $cid . '/entries')) ?>">All</a>
    <?php foreach (ChallengeService::ENTRY_STATUS as $k => $label): ?><a class="tabs__item<?= $status === $k ? ' is-active' : '' ?>" href="<?= e(url('admin/academy/challenges/' . $cid . '/entries', ['status' => $k])) ?>"><?= e($label) ?></a><?php endforeach; ?>
</nav>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No entries', 'icon' => 'users', 'text' => 'Entries appear here as learners apply.']) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Entries</caption>
        <thead><tr><th scope="col">Entrant</th><th scope="col">Entry</th><th scope="col">Score</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $e): $eid = (int) $e['id']; ?>
            <tr<?= !empty($e['is_winner']) ? ' class="is-winner"' : '' ?>>
                <td class="cell-main" data-label="Entrant"><span class="cell-title"><?= e($e['team_name'] ?: $e['learner_name']) ?></span><span class="cell-sub"><?= e($e['learner_name']) ?> &middot; <?= e($e['learner_email']) ?></span>
                    <?php if ($e['members']): ?><span class="cell-sub">With: <?= e($e['members']) ?></span><?php endif; ?></td>
                <td data-label="Entry">
                    <?php if ($c['kind'] === 'quiz'): ?>
                        <?= $e['quiz_submitted_at'] ? (int) $e['quiz_correct'] . ' of ' . (int) $e['quiz_total'] . ' correct<span class="cell-sub">' . e(academy_duration((int) ceil(((int) $e['quiz_seconds']) / 60))) . '</span>' : ($e['quiz_started_at'] ? 'Started, not submitted' : '<span class="text-muted">Not started</span>') ?>
                    <?php else: ?>
                        <strong><?= e($e['sub_title'] ?: $e['idea_title'] ?: '-') ?></strong>
                        <?php if ($e['sub_url']): ?><span class="cell-sub"><a href="<?= e($e['sub_url']) ?>" target="_blank" rel="noopener noreferrer nofollow">Project link</a></span><?php endif; ?>
                        <?php if ($e['sub_file_path']): ?><span class="cell-sub"><a href="<?= e(url('admin/academy/challenges/entries/' . $eid . '/file/project')) ?>">Project file</a></span><?php endif; ?>
                        <?= $e['submitted_at'] ? '' : '<span class="cell-sub text-muted">Nothing submitted yet</span>' ?>
                        <?php if ((int) $e['judgments'] > 0): ?><span class="cell-sub"><?= (int) $e['judgments'] ?> judge<?= (int) $e['judgments'] === 1 ? '' : 's' ?>, average <?= e(number_format((float) $e['average'], 1)) ?></span><?php endif; ?>
                    <?php endif; ?>
                    <?php if ($e['photo_path']): ?><span class="cell-sub"><a href="<?= e(url('admin/academy/challenges/entries/' . $eid . '/file/photo')) ?>" target="_blank" rel="noopener">Photo</a><?= $e['photo_consent_at'] ? '' : ' (no consent)' ?><?= (int) $e['publish_photo'] === 0 ? ' (hidden)' : '' ?></span><?php endif; ?>
                </td>
                <td data-label="Score"><?= $e['rank_no'] !== null ? '<strong>' . e(ChallengeService::ordinal((int) $e['rank_no'])) . '</strong> &middot; ' . e(rtrim(rtrim(number_format((float) $e['score'], 1), '0'), '.')) . '%' . (!empty($e['is_winner']) ? '<span class="cell-sub">' . icon('award', 'icon--sm') . ' ' . e((string) $e['award']) . '</span>' : '') : '-' ?></td>
                <td data-label="Status"><span class="badge"><?= e(ChallengeService::ENTRY_STATUS[$e['status']] ?? $e['status']) ?></span><?= $e['admin_note'] ? '<span class="cell-sub">' . e($e['admin_note']) . '</span>' : '' ?></td>
                <td class="col-actions"><div class="table__actions">
                    <?php if ($e['status'] === 'pending' || $e['status'] === 'rejected'): ?>
                        <form method="post" action="<?= e(url('admin/academy/challenges/entries/' . $eid . '/decision')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="to" value="approved"><button class="btn btn--primary btn--sm" type="submit">Approve</button></form>
                    <?php endif; ?>
                    <?php if ($e['status'] === 'pending' || $e['status'] === 'approved'): ?>
                        <form method="post" action="<?= e(url('admin/academy/challenges/entries/' . $eid . '/decision')) ?>" class="inline-form" data-confirm="Decline this entry? The learner is e-mailed."><?= csrf_field() ?><input type="hidden" name="to" value="rejected"><button class="btn btn--outline btn--sm" type="submit">Decline</button></form>
                    <?php endif; ?>
                    <?php if ($e['photo_path']): ?>
                        <form method="post" action="<?= e(url('admin/academy/challenges/entries/' . $eid . '/photo')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="to" value="toggle"><button class="btn btn--ghost btn--sm" type="submit"><?= (int) $e['publish_photo'] === 1 ? 'Hide photo' : 'Allow photo' ?></button></form>
                    <?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
<?php endif; ?>
