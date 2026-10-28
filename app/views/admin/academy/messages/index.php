<?php /** Admin > Academy > Messages (inbox). Vars: $rows, $q, $ready, $unread. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Messages', 'subtitle' => 'Conversations with learners. A message you write appears in the learner portal; the learner answers there. Start a conversation with the Message button on a learner.']) ?>
<?php if (empty($ready)): ?>
    <div class="alert alert--warning" role="status"><strong>Messages need the database update.</strong> Import database/lumina-live-import.sql (phpMyAdmin) once; it is safe to repeat.</div>
<?php else: ?>
    <?php if ((int) $unread > 0): ?><div class="alert alert--warning" role="status"><strong><?= (int) $unread ?> unread message<?= (int) $unread === 1 ? '' : 's' ?> from learners.</strong> The learners with new messages are first.</div><?php endif; ?>
    <form class="filters" method="get" action="<?= e(url('admin/academy/messages')) ?>" role="search">
        <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, e-mail or registration number" maxlength="80"></div>
        <button class="btn btn--outline" type="submit">Search</button>
    </form>
    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => 'No conversation yet', 'icon' => 'message-circle', 'text' => 'Open a learner and press Message to write the first one. When a learner writes to the Academy team from the portal, it appears here.']) ?>
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
