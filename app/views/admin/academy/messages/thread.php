<?php
/** Admin > Academy > one conversation. Vars: $l (the learner), $thread, $firstNew, $ready. */
use App\Services\Academy\LearnerMessages;

$lid = (int) $l['id'];
$day = '';
?>
<?= partial('partials/admin/page-header', ['title' => 'Messages: ' . $l['name'], 'subtitle' => (string) $l['email'] . (!empty($l['reg_no']) ? ' | ' . (string) $l['reg_no'] : ''),
    'actions' => [['label' => 'The learner', 'url' => url('admin/academy/learners/' . $lid), 'icon' => 'user', 'class' => 'btn--outline'], ['label' => 'All messages', 'url' => url('admin/academy/messages'), 'icon' => 'inbox', 'class' => 'btn--outline']]]) ?>
<?php if (empty($ready)): ?>
    <div class="alert alert--warning" role="status"><strong>Messages need the database update.</strong> Import database/lumina-live-import.sql once.</div>
<?php else: ?>
<?php
$canSee = $l['email_verified_at'] !== null && (int) $l['is_active'] === 1;
$waNo = (string) preg_replace('/\D+/', '', (string) ($l['whatsapp'] ?? ''));
$first = explode(' ', trim((string) $l['name']))[0];
$waText = 'Hello ' . $first . ', the LAF Academy team sent you a message. ' . ($canSee ? 'Please sign in to your learner portal to read it: ' : 'Your account is still waiting for approval. Try to sign in and you will see the message on the waiting page: ') . ($login ?? '');
?>
<?php if (!$canSee): ?>
    <div class="alert alert--warning" role="status">
        <strong><?= !empty($l['rejected_at']) ? 'This application was rejected.' : 'This learner is not approved yet, so they cannot open the portal.' ?></strong>
        <?= empty($l['rejected_at']) ? 'They will see your message on the "waiting for approval" page the next time they try to sign in with their password. To let them know now, notify them below.' : 'They will see the reason when they try to sign in.' ?>
    </div>
<?php endif; ?>
<div class="aac-actions">
    <?php if (strlen($waNo) >= 8): ?><a class="btn btn--outline btn--sm" href="https://wa.me/<?= e($waNo) ?>?text=<?= e(rawurlencode($waText)) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon--sm') ?> Notify <?= e($first) ?> on WhatsApp</a><?php endif; ?>
    <?php if (!$canSee && empty($l['rejected_at'])): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/approve')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="back" value="thread"><button class="btn btn--primary btn--sm" type="submit">Approve now</button></form><?php endif; ?>
</div>
<div class="card">
    <div class="card__body">
        <div class="aac-chat" role="log" aria-label="Messages with <?= e($l['name']) ?>" tabindex="0">
            <?php if ($thread === []): ?><p class="text-muted">No message yet. Write the first one below: <?= e($l['name']) ?> will see it in the learner portal.</p><?php endif; ?>
            <?php foreach ($thread as $m): $d = format_date($m['created_at'], 'j F Y'); ?>
                <?php if ($d !== $day): $day = $d; ?><p class="aac-chat__day"><?= e($d) ?></p><?php endif; ?>
                <?php if ($firstNew !== null && (int) $m['id'] === $firstNew): ?><p class="aac-chat__new">New messages</p><?php endif; ?>
                <div class="aac-chat__msg aac-chat__msg--<?= $m['sender'] === 'admin' ? 'team' : 'learner' ?>">
                    <p class="aac-chat__bubble"><?= nl2br(e((string) $m['body'])) ?></p>
                    <span class="aac-chat__meta"><?= $m['sender'] === 'admin' ? e((string) ($m['admin_name'] ?: 'Academy team')) : e((string) $l['name']) ?> &middot; <?= e(format_datetime($m['created_at'], 'j M, H:i')) ?><?php if ($m['sender'] === 'admin'): ?> &middot; <?= $m['read_at'] !== null ? 'read' : 'not read yet' ?><?php endif; ?></span>
                </div>
            <?php endforeach; ?>
            <span id="end"></span>
        </div>
    </div>
</div>
<div class="card">
    <div class="card__header"><h2 class="card__title">Write to <?= e($l['name']) ?></h2></div>
    <div class="card__body">
        <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/messages')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="field<?= error('body') ? ' field--error' : '' ?>">
                <label class="field__label" for="am-body">Message</label>
                <textarea class="textarea" id="am-body" name="body" rows="4" maxlength="<?= LearnerMessages::MAX ?>" required><?= e((string) old('body')) ?></textarea>
                <?php if (error('body')): ?><div class="field__error" role="alert"><?= e((string) error('body')) ?></div><?php endif; ?>
                <div class="field__help">Plain text. The learner sees it in the portal (Messages) with a number on the tab.</div>
            </div>
            <label class="check"><input type="checkbox" name="email" value="1"><span>Also send it by e-mail (uses your e-mail allowance: leave it off when e-mail is limited)<?= !$canSee ? '. Recommended: this learner cannot see portal messages yet' : '' ?></span></label>
            <p><button class="btn btn--primary" type="submit"><?= icon('send', 'icon--sm') ?> Send message</button></p>
        </form>
    </div>
</div>
<?php endif; ?>
