<?php /** Admin > Academy > Learners > WhatsApp payment reminders. Vars: $rows (unpaid learners, each with 'wa' = click-to-chat link or null). */ ?>
<?= partial('partials/admin/page-header', ['title' => 'WhatsApp payment reminders', 'subtitle' => 'Learners who have not paid the sign-up fee. Each button opens WhatsApp with the reminder already typed: you press Send there.', 'actions' => [['label' => 'Back to learners', 'url' => url('admin/academy/learners'), 'icon' => 'arrow-left', 'class' => 'btn--outline']]]) ?>
<div class="card"><div class="card__body">
    <?php if ($rows === []): ?>
        <p class="text-muted">Nobody has an unpaid sign-up fee.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Learner</th><th>Registration no.</th><th>Phone</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="<?= e(url('admin/academy/learners/' . (int) $r['id'])) ?>"><?= e((string) $r['name']) ?></a><br><span class="text-muted"><?= e((string) $r['email']) ?></span></td>
                    <td><?= e((string) ($r['reg_no'] ?? '-')) ?></td>
                    <td><?= e(\App\Services\Academy\LearnerPhones::display($r['phone'], $r['country']) ?: '-') ?></td>
                    <td><?php if ($r['wa'] !== null): ?><a class="btn btn--primary btn--sm" href="<?= e($r['wa']) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon--sm') ?> Open WhatsApp</a><?php else: ?><span class="text-muted">No WhatsApp number</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</div></div>
