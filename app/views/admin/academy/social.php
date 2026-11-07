<?php /** Admin > Academy > Social follow check. Vars: $ready, $rows, $counts, $status, $q, $pages. */
$tabs = ['' => 'All', 'unverified' => 'To check', 'verified' => 'Verified'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Social follow check', 'subtitle' => 'New learners must follow and subscribe to our pages and give their usernames. Open each username, look it up in the followers of the page, then mark the learner as verified.']) ?>
<div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin:0 0 1rem;padding:1rem;border:1px solid var(--border);border-radius:12px;background:var(--surface)">
    <div style="flex:1 1 18rem">
        <strong>Follow and subscribe at sign-up</strong>
        <div><?php if ($followSwitch && $hasPages): ?><span class="badge badge--success badge--dot">ON: new learners must follow our pages</span><?php elseif ($followSwitch): ?><span class="badge badge--warning badge--dot">ON, but no social media pages are set (nothing to follow)</span><?php else: ?><span class="badge badge--warning badge--dot">OFF: new learners sign up without this step</span><?php endif; ?></div>
        <span class="text-muted">Switching it off hides the follow step for everyone who signs up from now on. The usernames already collected are kept. Switch it on again at any time.</span>
    </div>
    <form method="post" action="<?= e(url('admin/academy/social/switch')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="state" value="<?= $followSwitch ? 'off' : 'on' ?>">
        <button class="btn <?= $followSwitch ? 'btn--outline' : 'btn--primary' ?>" type="submit"><?= $followSwitch ? 'Switch follow and subscribe OFF' : 'Switch follow and subscribe ON' ?></button>
    </form>
</div>
<?php if (!$ready): ?>
    <div class="alert alert--warning" role="status"><strong>The database update is missing.</strong> In phpMyAdmin import <code>database/updates/2026-11-academy-social-follow.sql</code> (safe to repeat). Until then only the opened pages are required at sign-up and no username is kept.</div>
<?php else: ?>
    <div class="vis-cards">
        <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= (int) $counts['total'] ?></span><span class="vis-card__l">Learners to follow up</span></div></div>
        <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= (int) $counts['unverified'] ?></span><span class="vis-card__l">Still to check</span></div></div>
        <div class="card vis-card"><div class="card__body"><span class="vis-card__n"><?= (int) $counts['verified'] ?></span><span class="vis-card__l">Verified</span></div></div>
    </div>
    <form class="filters" method="get" action="<?= e(url('admin/academy/social')) ?>" role="search">
        <div class="field"><label class="field__label" for="sc-status">Show</label><select class="select" id="sc-status" name="status"><?php foreach ($tabs as $k => $l): ?><option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
        <div class="field field--grow"><label class="field__label" for="sc-q">Search</label><input class="input" id="sc-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, e-mail, registration number or username" maxlength="80"></div>
        <button class="btn btn--outline" type="submit">Show</button>
    </form>
    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => 'Nobody here yet', 'icon' => 'heart', 'text' => 'Learners who sign up from now on appear here with the usernames they gave.']) ?>
    <?php else: ?>
        <div class="table-wrap"><table class="table table--stack">
            <caption class="visually-hidden">Social follow check</caption>
            <thead><tr><th scope="col">Learner</th><th scope="col">Usernames (open to look up)</th><th scope="col">Pages opened</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): $ok = $r['verified_at'] !== null; ?>
                <tr class="<?= $ok ? 'is-muted' : '' ?>">
                    <td class="cell-main" data-label="Learner"><span class="cell-title"><a href="<?= e(url('admin/academy/learners/' . (int) $r['learner_id'])) ?>"><?= e($r['name']) ?></a></span><span class="cell-sub"><?= e((string) $r['reg_no']) ?> &middot; <?= e($r['country']) ?> &middot; joined <?= e(format_date($r['created_at'], 'j M')) ?></span></td>
                    <td data-label="Usernames">
                        <?php foreach ($r['handles'] as $plat => $h): $u = \App\Services\Academy\AcademySocial::profileUrl((string) $plat, (string) $h); $label = \App\Services\Academy\AcademySocial::HANDLES[$plat][0] ?? $plat; ?>
                            <div><strong><?= e($label) ?>:</strong> <?php if ($u !== ''): ?><a href="<?= e($u) ?>" target="_blank" rel="noopener noreferrer">@<?= e($h) ?></a><?php else: ?>@<?= e($h) ?><?php endif; ?></div>
                        <?php endforeach; ?>
                        <?php if ($r['handles'] === []): ?><span class="text-muted">none given</span><?php endif; ?>
                    </td>
                    <td data-label="Pages opened"><?= count($r['opened']) ?> of <?= count($pages) ?></td>
                    <td data-label="Status"><?= $ok ? '<span class="badge badge--success">Verified ' . e(format_date($r['verified_at'], 'j M')) . '</span>' : '<span class="badge badge--warning">To check</span>' ?></td>
                    <td data-label="Actions" class="nowrap">
                        <?php if ($ok): ?>
                            <form method="post" action="<?= e(url('admin/academy/social/' . (int) $r['learner_id'] . '/unverify')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($status) ?>"><button class="btn btn--outline btn--sm" type="submit">Remove mark</button></form>
                        <?php else: ?>
                            <form method="post" action="<?= e(url('admin/academy/social/' . (int) $r['learner_id'] . '/verify')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($status) ?>"><button class="btn btn--primary btn--sm" type="submit"><?= icon('check-circle', 'icon--sm') ?> Mark verified</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
<?php endif; ?>
