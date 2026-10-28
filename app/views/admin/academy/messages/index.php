<?php /** Admin > Academy > Messages (inbox). Vars: $rows (learner conversations), $q, $ready, $unread (learners' unread messages), $inquiries (from visitors), $openInquiries. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Messages', 'subtitle' => 'Conversations with learners. A message you write appears in the learner portal; the learner answers there. Start a conversation with the Message button on a learner.']) ?>
<?php if (empty($ready)): ?>
    <div class="alert alert--warning" role="status"><strong>Messages need the database update.</strong> Import database/lumina-live-import.sql (phpMyAdmin) once; it is safe to repeat.</div>
<?php else: ?>
    <?php if ((int) $unread > 0): ?><div class="alert alert--warning" role="status"><strong><?= (int) $unread ?> unread message<?= (int) $unread === 1 ? '' : 's' ?> from learners.</strong> The learners with new messages are first.</div><?php endif; ?>
    <?php if (!empty($inquiries)): ?>
        <div class="card">
            <div class="card__header"><h2 class="card__title">Inquiries from visitors (<?= (int) $openInquiries ?> open)</h2></div>
            <div class="card__body">
                <p class="text-muted">Sent from the public "Message the Academy team" form by people who have no portal yet. Answer by e-mail or WhatsApp, then mark it as dealt with. (When the e-mail belongs to a learner, the message is in that learner's conversation below instead.)</p>
                <div class="table-wrap"><table class="table table--stack">
                    <caption class="visually-hidden">Inquiries from visitors</caption>
                    <thead><tr><th scope="col">From</th><th scope="col">Message</th><th scope="col">When</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($inquiries as $iq): $open = $iq['handled_at'] === null; $wa = (string) preg_replace('/\D+/', '', (string) $iq['phone']); ?>
                        <tr class="<?= $open ? 'is-unread' : 'is-muted' ?>">
                            <td class="cell-main" data-label="From"><span class="cell-title"><?= e($iq['name']) ?><?php if ($open): ?> <span class="badge badge--warning">new</span><?php endif; ?></span><span class="cell-sub"><?= e($iq['email']) ?><?= $iq['phone'] ? ' &middot; ' . e($iq['phone']) : '' ?></span></td>
                            <td data-label="Message"><?= nl2br(e((string) $iq['message'])) ?></td>
                            <td data-label="When"><span class="nowrap"><?= e(format_datetime($iq['created_at'], 'j M, H:i')) ?></span><?php if (!$open): ?><span class="cell-sub">Dealt with <?= e(format_date($iq['handled_at'], 'j M')) ?></span><?php endif; ?></td>
                            <td data-label="Actions" class="nowrap">
                                <a class="btn btn--outline btn--sm" href="mailto:<?= e($iq['email']) ?>?subject=<?= e(rawurlencode('Your message to the LAF Academy')) ?>"><?= icon('mail', 'icon--sm') ?> E-mail</a>
                                <?php if (strlen($wa) >= 8): ?><a class="btn btn--outline btn--sm" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon--sm') ?> WhatsApp</a><?php endif; ?>
                                <?php if ($open): ?><form method="post" action="<?= e(url('admin/academy/inquiries/' . (int) $iq['id'] . '/done')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Mark as dealt with</button></form><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
            </div>
        </div>
    <?php endif; ?>
    <form class="filters" method="get" action="<?= e(url('admin/academy/messages')) ?>" role="search">
        <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, e-mail or registration number" maxlength="80"></div>
        <button class="btn btn--outline" type="submit">Search</button>
    </form>
    <?php if ($rows === []): ?>
        <?php if (empty($inquiries)): ?><?= partial('partials/empty-state', ['title' => 'No conversation yet', 'icon' => 'message-circle', 'text' => 'Open a learner and press Message to write the first one. When a learner writes to the Academy team from the portal, or a visitor uses the contact form, it appears here.']) ?><?php endif; ?>
    <?php else: ?>
        <div class="table-wrap"><table class="table table--stack">
            <caption class="visually-hidden">Conversations with learners</caption>
            <thead><tr><th scope="col">Learner</th><th scope="col">Last message</th><th scope="col">When</th><th scope="col"><span class="visually-hidden">Open</span></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): $new = (int) $r['unread']; ?>
                <tr class="<?= $new > 0 ? 'is-unread' : '' ?>">
                    <td class="cell-main" data-label="Learner"><span class="cell-title"><a href="<?= e(url('admin/academy/learners/' . (int) $r['id'] . '/messages')) ?>"><?= e($r['name']) ?></a><?php if ($new > 0): ?> <span class="badge badge--warning"><?= $new ?> new</span><?php endif; ?></span><span class="cell-sub"><?= e($r['email']) ?><?= !empty($r['reg_no']) ? ' &middot; ' . e($r['reg_no']) : '' ?></span></td>
                    <td data-label="Last message"><?= $r['last_sender'] === 'admin' ? '<span class="text-muted">You:</span> ' : '' ?><?= e(mb_strimwidth((string) $r['last_body'], 0, 120, '...')) ?></td>
                    <td data-label="When"><span class="nowrap"><?= e(format_datetime($r['last_at'], 'j M, H:i')) ?></span><span class="cell-sub"><?= (int) $r['total'] ?> message<?= (int) $r['total'] === 1 ? '' : 's' ?></span></td>
                    <td data-label="Open"><a class="btn btn--<?= $new > 0 ? 'primary' : 'outline' ?> btn--sm" href="<?= e(url('admin/academy/learners/' . (int) $r['id'] . '/messages')) ?>"><?= $new > 0 ? 'Read and reply' : 'Open' ?></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
    <?php endif; ?>
<?php endif; ?>
