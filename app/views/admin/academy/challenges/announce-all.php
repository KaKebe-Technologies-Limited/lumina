<?php
/** Admin > Academy > Challenges > E-mail learners about ALL open challenges. Vars: $list (open challenges), $pending (learners still to tell), $active (active learners), $perRun. */
use App\Services\Academy\ChallengeService;
?>
<?= partial('partials/admin/page-header', ['title' => 'E-mail learners about the open challenges', 'subtitle' => 'One e-mail to every active learner listing all the challenges that are open now, with the benefits of each.', 'actions' => [['label' => 'All challenges', 'url' => url('admin/academy/challenges'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<?php if ($list === []): ?>
    <div class="alert alert--warning" role="status">No challenge is open at the moment. Publish a challenge (and set its dates) first.</div>
<?php endif; ?>
<div class="ad-letter-grid">
    <section class="card"><div class="card__body">
        <h2 class="card__title">What will be announced</h2>
        <?php if ($list): ?>
            <ul class="text-muted">
                <?php foreach ($list as $c): ?>
                    <li><strong><?= e($c['title']) ?></strong> (<?= e(ChallengeService::KINDS[$c['kind']] ?? '') ?>)<?php if ($c['starts_at']): ?>, starts <?= e(format_datetime($c['starts_at'], 'j M Y, H:i')) ?> EAT<?php endif; ?>
                        <br><small>Benefits: <?= e(implode('; ', array_slice(ChallengeService::benefits($c), 0, 3))) ?></small></li>
                <?php endforeach; ?>
            </ul>
            <p class="text-muted">Edit the benefits of a challenge on its edit page (Benefits and privileges).</p>
        <?php endif; ?>
        <p><strong><?= (int) $active ?></strong> active learners (approved, e-mail confirmed). <strong><?= (int) $pending ?></strong> have not been told about exactly this set of challenges yet.</p>
    </div></section>
    <section class="card"><div class="card__body">
        <h2 class="card__title">Send</h2>
        <form method="post" action="<?= e(url('admin/academy/challenges/announce-all')) ?>"><?= csrf_field() ?>
            <div class="field"><label class="field__label" for="note">A line from the team (optional)</label>
                <textarea class="textarea" id="note" name="note" rows="3" maxlength="600" placeholder="For example: This month's challenges close on Friday."></textarea></div>
            <label class="check"><input type="checkbox" name="again" value="1"> Send again to learners who were already told</label>
            <p class="text-muted">At most <?= (int) $perRun ?> e-mails go out each time you press the button (Gmail allows about 500 a day). If more learners are waiting, press it again: nobody is e-mailed twice. When a new challenge opens, the list changes and everyone can be told about it.</p>
            <button class="btn btn--primary" type="submit" <?= $list ? '' : 'disabled' ?> data-confirm="E-mail <?= (int) min($pending, $perRun) ?> learner(s) about <?= count($list) ?> open challenge(s) now?"><?= icon('send', 'icon--sm') ?> E-mail the learners</button>
        </form>
        <p class="text-muted">You are also e-mailed (at the Academy support address) whenever someone applies to a challenge, and applicants are e-mailed at every stage: application received, approved or declined, challenge started, quiz or project received, and the result.</p>
    </div></section>
</div>
