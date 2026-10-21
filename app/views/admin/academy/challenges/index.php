<?php /** Admin > Academy > Challenges. Vars: $rows, $manager, $starterPending. */
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;

$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => 'Challenges', 'subtitle' => 'Quiz challenges, innovation challenges and hackathons. You set the dates; learners apply and take part; the system ranks the entries and marks the winners, who get a certificate and are featured as winners of the month.',
    'actions' => $manager ? [['label' => 'New quiz challenge', 'url' => url('admin/academy/challenges/create', ['kind' => 'quiz']), 'icon' => 'plus'], ['label' => 'New innovation challenge', 'url' => url('admin/academy/challenges/create', ['kind' => 'innovation']), 'icon' => 'plus'], ['label' => 'New hackathon', 'url' => url('admin/academy/challenges/create', ['kind' => 'hackathon']), 'icon' => 'plus']] : []]) ?>
<?php if ($manager && $starterPending > 0): ?>
    <div class="card"><div class="card__body">
        <p><strong><?= (int) $starterPending ?> ready-made challenges</strong> (quizzes, an innovation challenge and a hackathon) can be added as drafts. Then open one, set its dates and publish it.</p>
        <form method="post" action="<?= e(url('admin/academy/challenges/starter')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit"><?= icon('plus', 'icon--sm') ?> Add the starter challenges</button></form>
    </div></div>
<?php endif; ?>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No challenges yet', 'icon' => 'target', 'text' => 'Create one, or add the starter challenges.']) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Challenges</caption>
        <thead><tr><th scope="col">Challenge</th><th scope="col">When (EAT)</th><th scope="col">Entries</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $c): $id = (int) $c['id']; $phase = $c['phase']; ?>
            <tr>
                <td class="cell-main" data-label="Challenge"><span class="cell-title"><a href="<?= e(url('admin/academy/challenges/' . $id . ($manager ? '/edit' : '/judge'))) ?>"><?= e($c['title']) ?></a></span>
                    <span class="cell-sub"><?= e(ChallengeService::KINDS[$c['kind']] ?? '') ?> &middot; <?= e(CourseService::categoryLabel($c['category'])) ?></span></td>
                <td data-label="When"><?php if ($c['starts_at']): ?><span class="nowrap"><?= e(format_datetime($c['starts_at'], 'j M Y, H:i')) ?></span><span class="cell-sub">to <?= e(format_datetime($c['ends_at'], 'j M, H:i')) ?></span><?php else: ?><span class="text-muted">Not set</span><?php endif; ?></td>
                <td data-label="Entries"><?= (int) $c['entries'] ?><?= (int) $c['waiting'] > 0 ? '<span class="cell-sub">' . (int) $c['waiting'] . ' waiting</span>' : '' ?></td>
                <td data-label="Status"><span class="badge"><?= e(ChallengeService::PHASES[$phase] ?? $phase) ?></span></td>
                <td class="col-actions"><div class="table__actions">
                    <?php if ($manager): ?>
                        <a class="btn btn--ghost btn--icon" href="<?= e(url('admin/academy/challenges/' . $id . '/edit')) ?>" title="Edit" aria-label="Edit: <?= e($c['title']) ?>"><?= icon('edit', 'icon--sm') ?></a>
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/challenges/' . $id . '/entries')) ?>">Entries</a>
                    <?php endif; ?>
                    <?php if ($c['scoring'] === 'judged' && $c['status'] === 'published'): ?><a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/challenges/' . $id . '/judge')) ?>">Score</a><?php endif; ?>
                    <?php if ($c['status'] === 'published'): ?><a class="btn btn--ghost btn--icon" href="<?= e(url('academy/challenges/' . $c['slug'])) ?>" target="_blank" rel="noopener" title="View on the site" aria-label="View on the site: <?= e($c['title']) ?>"><?= icon('external-link', 'icon--sm') ?></a><?php endif; ?>
                    <?php if ($manager): ?>
                        <?php if ($c['status'] === 'draft'): ?>
                            <form method="post" action="<?= e(url('admin/academy/challenges/' . $id . '/status')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="to" value="published"><button class="btn btn--primary btn--sm" type="submit">Publish</button></form>
                        <?php elseif ($c['status'] === 'published' && empty($c['finalized_at'])): ?>
                            <form method="post" action="<?= e(url('admin/academy/challenges/' . $id . '/status')) ?>" class="inline-form" data-confirm="Cancel this challenge? Visitors see that it is cancelled."><?= csrf_field() ?><input type="hidden" name="to" value="cancelled"><button class="btn btn--outline btn--sm" type="submit">Cancel</button></form>
                        <?php elseif ($c['status'] === 'cancelled'): ?>
                            <form method="post" action="<?= e(url('admin/academy/challenges/' . $id . '/status')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="to" value="published"><button class="btn btn--outline btn--sm" type="submit">Publish again</button></form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('admin/academy/challenges/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete "' . $c['title'] . '" and its entries?') ?>"><?= csrf_field() ?><button class="btn btn--ghost btn--icon btn-danger-hover" type="submit" title="Delete" aria-label="Delete: <?= e($c['title']) ?>"><?= icon('trash', 'icon--sm') ?></button></form>
                    <?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
<?php endif; ?>
