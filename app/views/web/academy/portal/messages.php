<?php
/** Learner portal > Messages with the Academy team. Vars: $me, $tab, $unread (0: opening the page reads them), $portrait, $thread, $firstNew (id of the first new message or null), $ready. */
use App\Services\Academy\LearnerAuth;
use App\Services\Academy\LearnerMessages;

$sw = LearnerAuth::supportWhatsapp();
$day = '';
?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Messages', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Write to the Academy team and read their answers here.']) ?>
<section class="section ac-dash">
    <div class="container">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>

        <?php if (empty($ready)): ?>
            <p class="opp-note"><?= icon('info', 'icon--sm') ?> Messages are not available yet. Please try again later.</p>
        <?php else: ?>
        <div class="ac-chat card">
            <div class="ac-chat__log" role="log" aria-label="Messages with the Academy team" tabindex="0">
                <?php if ($thread === []): ?>
                    <p class="ac-chat__empty"><?= icon('message-circle', 'icon--xl') ?><br>No message yet. Ask the Academy team anything about your courses, your approval or your certificate.</p>
                <?php endif; ?>
                <?php foreach ($thread as $m): $d = format_date($m['created_at'], 'j F Y'); ?>
                    <?php if ($d !== $day): $day = $d; ?><p class="ac-chat__day"><span><?= e($d) ?></span></p><?php endif; ?>
                    <?php if ($firstNew !== null && (int) $m['id'] === $firstNew): ?><p class="ac-chat__new"><span>New messages</span></p><?php endif; ?>
                    <div class="ac-chat__msg ac-chat__msg--<?= $m['sender'] === 'admin' ? 'team' : 'me' ?>">
                        <p class="ac-chat__bubble"><?= nl2br(e((string) $m['body'])) ?></p>
                        <span class="ac-chat__meta"><?= $m['sender'] === 'admin' ? 'Academy team' : 'You' ?> &middot; <?= e(format_datetime($m['created_at'], 'H:i')) ?></span>
                    </div>
                <?php endforeach; ?>
                <span id="end"></span>
            </div>
            <form class="ac-chat__form" id="write" method="post" action="<?= e(url('academy/portal/messages')) ?>" novalidate>
                <?= csrf_field() ?>
                <div class="field<?= error('body') ? ' field--error' : '' ?>">
                    <label class="field__label" for="msg-body">Write a message</label>
                    <textarea class="textarea" id="msg-body" name="body" rows="3" maxlength="<?= LearnerMessages::MAX ?>" required placeholder="Type your message to the Academy team..."><?= e((string) old('body')) ?></textarea>
                    <?php if (error('body')): ?><div class="field__error" role="alert"><?= e((string) error('body')) ?></div><?php endif; ?>
                </div>
                <div class="ac-chat__actions">
                    <button class="btn btn--cta btn--caps" type="submit"><?= icon('send', 'icon--sm') ?> Send</button>
                    <?php if ($sw !== null): ?><span class="text-muted">Urgent? WhatsApp <a href="<?= e($sw['url']) ?>" target="_blank" rel="noopener"><?= e($sw['display']) ?></a></span><?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>
