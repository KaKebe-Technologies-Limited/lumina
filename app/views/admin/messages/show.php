<?php
/**
 * Admin > one message. Vars: $m, $replies, $siblings, $statuses, $replySubject, $canDelete.
 */
use App\Services\Inbox\Support;

$subject = trim((string) $m['subject']);
$mailto = Support::mailto($m['email'], (string) $replySubject, '');
$replyOpen = has_error('reply_subject') || has_error('reply_body');
$actions = [];
if ($mailto !== '') {
    $actions[] = ['label' => 'Reply in my email program', 'url' => $mailto, 'class' => 'btn--outline', 'icon' => 'mail'];
}
$actions[] = ['label' => 'All messages', 'url' => url('admin/messages'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', [
        'title'    => $subject !== '' ? $subject : 'Message from ' . $m['name'],
        'subtitle' => 'From ' . $m['name'] . ' - received ' . format_datetime($m['created_at']),
        'actions'  => $actions,
    ]) ?>

    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card">
                <div class="card__header">
                    <h2 class="card__title">Message</h2>
                    <?= partial('partials/admin/status-badge', ['status' => $m['status'], 'label' => $statuses[$m['status']] ?? null]) ?>
                </div>
                <div class="card__body">
                    <div class="inbox-message"><?= nl2p((string) $m['message']) ?></div>
                </div>
            </section>

            <section class="card" id="reply">
                <div class="card__header"><h2 class="card__title">Reply</h2></div>
                <div class="card__body">
                    <?php if ($mailto === ''): ?>
                        <p class="text-muted">This message has no valid email address, so it cannot be answered from here.</p>
                    <?php else: ?>
                        <details class="inbox-reply"<?= $replyOpen ? ' open' : '' ?>>
                            <summary class="btn btn--primary"><?= icon('mail', 'icon--sm') ?> Write a reply here</summary>
                            <form method="post" action="<?= e(url('admin/messages/' . (int) $m['id'] . '/reply')) ?>" data-unsaved-guard>
                                <?= csrf_field() ?>
                                <p class="small text-muted">The email is sent to <strong><?= e($m['email']) ?></strong> from the website. Their answer will reach <?= e(setting('email_primary') ?: 'your reply-to address') ?>. A copy of what you send is kept on this page.</p>
                                <?= partial('partials/form-field', ['name' => 'reply_subject', 'label' => 'Subject', 'value' => $replySubject, 'required' => true, 'maxlength' => 200]) ?>
                                <?= partial('partials/form-field', ['name' => 'reply_body', 'label' => 'Your reply', 'type' => 'textarea', 'rows' => 9, 'value' => old('reply_body', ''), 'required' => true, 'maxlength' => 10000, 'help' => 'Your name and the foundation contact details are added below your text, followed by their original message.']) ?>
                                <div class="btn-row">
                                    <button class="btn btn--primary" type="submit" data-confirm="Send this email to <?= e($m['email']) ?>?"><?= icon('mail', 'icon--sm') ?> Send reply</button>
                                </div>
                            </form>
                        </details>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($replies !== []): ?>
                <section class="card">
                    <div class="card__header"><h2 class="card__title">Replies sent from here</h2></div>
                    <div class="card__body">
                        <ol class="inbox-thread">
                            <?php foreach ($replies as $r): ?>
                                <li class="inbox-thread__item">
                                    <div class="inbox-thread__meta"><strong><?= e($r['by']) ?></strong> wrote to <?= e($r['to']) ?> on <?= e(format_datetime($r['at'])) ?></div>
                                    <?php if ($r['subject'] !== ''): ?><div class="inbox-thread__subject"><?= e($r['subject']) ?></div><?php endif; ?>
                                    <div class="inbox-thread__body"><?= nl2p($r['body']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <aside class="inbox-aside" aria-label="Message details">
            <?= partial('admin/inbox/_status-form', [
                'action'   => url('admin/messages/' . (int) $m['id']),
                'statuses' => $statuses,
                'current'  => $m['status'],
                'notes'    => (string) ($m['admin_notes'] ?? ''),
                'title'    => 'Status and notes',
                'help'     => 'Only the team can see these notes.',
            ]) ?>

            <section class="card">
                <div class="card__header"><h2 class="card__title">Sender</h2></div>
                <div class="card__body">
                    <dl class="definition-list inbox-dl">
                        <dt>Name</dt><dd><?= e($m['name']) ?></dd>
                        <dt>Email</dt><dd><?php if (Support::mailto($m['email']) !== ''): ?><a href="<?= e(Support::mailto($m['email'])) ?>"><?= e($m['email']) ?></a><?php else: ?><?= e($m['email']) ?><?php endif; ?></dd>
                        <?php if (!empty($m['phone'])): ?><dt>Phone</dt><dd><a href="<?= e(phone_href($m['phone'])) ?>"><?= e($m['phone']) ?></a></dd><?php endif; ?>
                        <?php if (!empty($m['replied_at'])): ?><dt>Replied</dt><dd><?= e(format_datetime($m['replied_at'])) ?></dd><?php endif; ?>
                    </dl>
                </div>
            </section>

            <?= partial('admin/inbox/_meta', ['row' => $m, 'title' => 'Technical details']) ?>

            <?php if ($siblings['prev'] || $siblings['next']): ?>
                <nav class="inbox-siblings" aria-label="Other messages">
                    <?php if ($siblings['prev']): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/messages/' . (int) $siblings['prev'])) ?>"><?= icon('arrow-left', 'icon--sm') ?> Older</a><?php endif; ?>
                    <?php if ($siblings['next']): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/messages/' . (int) $siblings['next'])) ?>">Newer <?= icon('arrow-right', 'icon--sm') ?></a><?php endif; ?>
                </nav>
            <?php endif; ?>

            <?php if ($canDelete): ?>
                <section class="card inbox-danger">
                    <div class="card__body">
                        <form method="post" action="<?= e(url('admin/messages/' . (int) $m['id'] . '/delete')) ?>" data-confirm="Delete this message permanently? This cannot be undone.">
                            <?= csrf_field() ?>
                            <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete message</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</div>
