<?php
/** Admin > Academy > Challenges > E-mail learners. Vars: $c (challenge), $open (bool), $scope (all|new), $counts (todo, active, told), $perRun. */
use App\Services\Academy\ChallengeService;

$id = (int) $c['id'];
$close = ChallengeService::applyDeadline($c);
?>
<?= partial('partials/admin/page-header', ['title' => 'E-mail learners about this challenge', 'subtitle' => $c['title'], 'actions' => [['label' => 'All challenges', 'url' => url('admin/academy/challenges'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<?php if (!$open): ?>
    <div class="alert alert--warning" role="status">This challenge is not open: it must be <strong>published</strong> and not finished before learners can be told about it.</div>
<?php endif; ?>
<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">Who gets it</h2>
        <p><strong><?= (int) $counts['active'] ?></strong> active learners (approved, e-mail confirmed). <strong><?= (int) $counts['todo'] ?></strong> will be e-mailed in this group<?= $counts['told'] > 0 ? ', and ' . (int) $counts['told'] . ' have already been told' : '' ?>.</p>
        <form method="get" action="<?= e(url('admin/academy/challenges/' . $id . '/announce')) ?>" class="inline-form">
            <label class="check"><input type="radio" name="scope" value="all" <?= $scope === 'all' ? 'checked' : '' ?>> Every active learner</label>
            <label class="check"><input type="radio" name="scope" value="new" <?= $scope === 'new' ? 'checked' : '' ?>> Only learners who have not entered yet</label>
            <button class="btn btn--outline btn--sm" type="submit">Update the count</button>
        </form>
        <h2 class="card__title ad-letter-sub">What the e-mail says</h2>
        <p class="text-muted">Subject: <strong>Challenge open: <?= e(mb_substr((string) $c['title'], 0, 90)) ?></strong>. It names the challenge, gives its short description, the closing date for applications, when it starts and ends, the prize, your note below, and a "Join the challenge" button to <a href="<?= e(url('academy/challenges/' . $c['slug'])) ?>" target="_blank" rel="noopener">its page</a>. The same words also appear in each learner's portal messages.</p>
        <ul class="text-muted">
            <li>Kind: <?= e(ChallengeService::KINDS[$c['kind']] ?? '') ?></li>
            <?php if (trim((string) ($c['summary'] ?? '')) !== ''): ?><li><?= e($c['summary']) ?></li><?php endif; ?>
            <?php if ($close !== null): ?><li>Apply before: <?= e(format_datetime(date('Y-m-d H:i:s', $close), 'j F Y, H:i')) ?> (EAT)</li><?php endif; ?>
            <?php if ($c['starts_at']): ?><li>Starts: <?= e(format_datetime($c['starts_at'], 'j F Y, H:i')) ?> &middot; ends: <?= e(format_datetime($c['ends_at'], 'j F Y, H:i')) ?></li><?php endif; ?>
            <?php if (trim((string) ($c['prize'] ?? '')) !== ''): ?><li>Prize: <?= e($c['prize']) ?></li><?php endif; ?>
        </ul>
    </div></section>

    <section class="card"><div class="card__body">
        <h2 class="card__title">Send</h2>
        <form method="post" action="<?= e(url('admin/academy/challenges/' . $id . '/announce')) ?>"><?= csrf_field() ?>
            <input type="hidden" name="scope" value="<?= e($scope) ?>">
            <div class="field"><label class="field__label" for="note">A line from the team (optional)</label>
                <textarea class="textarea" id="note" name="note" rows="3" maxlength="600" placeholder="For example: Do not miss it, the best three entries win a prize."></textarea></div>
            <label class="check"><input type="checkbox" name="again" value="1"> Send again to learners who were already told</label>
            <p class="text-muted">At most <?= (int) $perRun ?> e-mails are sent each time you press the button (Gmail allows about 500 a day). If more learners are waiting, press it again for the next batch: nobody is e-mailed twice.</p>
            <button class="btn btn--primary" type="submit" <?= $open ? '' : 'disabled' ?> data-confirm="E-mail <?= (int) min($counts['todo'], $perRun) ?> learner(s) about this challenge now?"><?= icon('send', 'icon--sm') ?> E-mail the learners</button>
        </form>
    </div></section>
</div>
