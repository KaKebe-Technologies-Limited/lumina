<?php
/**
 * The head of every learner-portal page: who is signed in, and the tabs. Vars: $me, $tab (overview|courses|certificates|messages|profile|security), $unread (new messages from the team),
 * $portrait (photo_path).
 */
$tabs = [
    'overview'     => ['Overview', 'academy/dashboard', 'home'],
    'courses'      => ['My courses', 'academy/portal/courses', 'layers'],
    'certificates' => ['Certificates', 'academy/portal/certificates', 'award'],
    'messages'     => ['Messages', 'academy/portal/messages', 'message-circle'],
    'profile'      => ['Profile', 'academy/account', 'user'],
    'security'     => ['Security', 'academy/portal/security', 'lock'],
];
$hasPhoto = !empty($portrait['photo_path']);
$first = explode(' ', trim((string) $me['name']))[0];
?>
<div class="ac-portal__head">
    <?php if ($hasPhoto): ?><img class="ac-portal__avatar" src="<?= e(url('academy/account/photo')) ?>?v=<?= e(substr(md5((string) $portrait['photo_path']), 0, 8)) ?>" alt="My picture" width="64" height="64"><?php else: ?><span class="ac-portal__avatar ac-portal__avatar--initial" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($first, 0, 1))) ?></span><?php endif; ?>
    <div class="ac-portal__who">
        <strong><?= e((string) $me['name']) ?></strong>
        <span><?= !empty($me['reg_no']) ? 'Reg no. ' . e((string) $me['reg_no']) . ' &middot; ' : '' ?><?= e((string) $me['email']) ?></span>
    </div>
    <form method="post" action="<?= e(url('academy/logout')) ?>" class="ac-portal__out"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit"><?= icon('log-out', 'icon--sm') ?> Sign out</button></form>
</div>
<nav class="ac-tabs" aria-label="My portal">
    <ul>
        <?php foreach ($tabs as $key => [$label, $path, $ic]): ?>
            <li><a class="ac-tabs__link<?= $tab === $key ? ' is-active' : '' ?>" href="<?= e(url($path)) ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>><?= icon($ic, 'icon--sm') ?><span><?= e($label) ?></span><?php if ($key === 'messages' && (int) ($unread ?? 0) > 0): ?><span class="ac-tabs__badge"><span class="visually-hidden">New: </span><?= (int) $unread ?></span><?php endif; ?></a></li>
        <?php endforeach; ?>
    </ul>
</nav>
