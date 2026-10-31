<?php $this->start('scripts'); ?><script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script><?php $this->end(); ?>
<?php /** Admin > Academy > Learners > WhatsApp welcome. Vars: $rows (learners who paid and were not welcomed yet, each with 'wa' = click-to-chat link or null). */
$waReady = array_values(array_filter($rows, static fn(array $r): bool => $r['wa'] !== null)); ?>
<?= partial('partials/admin/page-header', ['title' => 'WhatsApp welcome messages', 'subtitle' => 'Learners who paid the sign-up fee and have not been welcomed yet. WhatsApp opens with the thank-you and welcome message already typed: you press Send there.', 'actions' => [['label' => 'Back to learners', 'url' => url('admin/academy/learners'), 'icon' => 'arrow-left', 'class' => 'btn--outline']]]) ?>
<?php if ($waReady !== []): ?>
<div class="card aac-wa" data-wa-queue data-wa-key="laf-wa-welcome-opened"><div class="card__body">
    <div class="aac-wa__head">
        <div>
            <strong>Welcome everyone in one go</strong>
            <p class="text-muted" data-wa-status>Press the button: each press opens the next learner's chat with the message typed. In WhatsApp press Send, come back here and press the button again. <?= count($waReady) ?> to welcome.</p>
        </div>
        <button class="btn btn--whatsapp" type="button" data-wa-next><?= icon('whatsapp', 'icon--sm') ?> <span>Start welcoming all (<?= count($waReady) ?>)</span></button>
    </div>
    <div class="aac-wa__bar" aria-hidden="true"><span data-wa-fill></span></div>
    <p class="text-muted aac-wa__note">Your browser asks to allow pop-ups the first time: allow them for this site. <button class="btn btn--ghost btn--sm" type="button" data-wa-reset hidden>Start again</button></p>
</div></div>
<?php endif; ?>
<div class="card"><div class="card__body">
    <?php if ($rows === []): ?>
        <p class="text-muted">Everybody who paid has already been welcomed.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Learner</th><th>Registration no.</th><th>Phone</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="<?= e(url('admin/academy/learners/' . (int) $r['id'])) ?>"><?= e((string) $r['name']) ?></a><br><span class="text-muted"><?= e((string) $r['email']) ?></span></td>
                    <td><?= e((string) ($r['reg_no'] ?? '-')) ?></td>
                    <td><?= e(\App\Services\Academy\LearnerPhones::display($r['phone'], $r['country']) ?: '-') ?></td>
                    <td><?php if ($r['wa'] !== null): ?><a class="btn btn--primary btn--sm" href="<?= e($r['wa']) ?>" target="_blank" rel="noopener" data-wa-link="<?= (int) $r['id'] ?>"><?= icon('whatsapp', 'icon--sm') ?> Open WhatsApp</a> <span class="aac-wa__sent" data-wa-sent hidden>Opened</span><?php else: ?><span class="text-muted">No WhatsApp number</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</div></div>
