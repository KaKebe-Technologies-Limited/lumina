<?php /** Admin > Academy > Learners. Vars: $rows, $pagination, $f (q, status, course), $total, $manager, $courses. */
$exportQs = array_filter(['q' => $f['q'], 'status' => $f['status'], 'course' => $f['course'] ?: null]);
$actions = [];
if ($manager) {
    $actions[] = ['label' => 'Add a learner', 'url' => url('admin/academy/learners/create/new'), 'icon' => 'user-plus'];
    $actions[] = ['label' => 'Download CSV', 'url' => url('admin/academy/learners-export', $exportQs), 'icon' => 'download', 'class' => 'btn--outline'];
}
?>
<?= partial('partials/admin/page-header', ['title' => 'Learners', 'subtitle' => $manager ? 'Everyone who signed up to the LAF Academy. Personal data: handle with care.' : 'The learners enrolled in your courses.', 'actions' => $actions]) ?>
<?php if ($manager && !empty($waiting)): ?>
    <div class="alert alert--warning" role="status">
        <strong><?= (int) $waiting ?> learner<?= (int) $waiting === 1 ? ' is' : 's are' ?> waiting for approval.</strong>
        <?php if (\App\Services\Academy\LearnerAuth::emailVerificationOn()): ?>They can start as soon as they confirm their e-mail address, or as soon as you approve them (no e-mail needed). Use <em>Approve</em> on a row, <em>Approve all</em> below, or send the confirmation e-mail again (25 at a time; those who never received one go first).<?php else: ?>New accounts are approved by you (e-mail codes are switched off: Settings > LAF Academy > How new learners get in). They were told to sign in after <?= e(\App\Services\Academy\LearnerAuth::approvalText()) ?>: use <em>Approve</em> on a row or <em>Approve all</em> below.<?php endif; ?>
        <div class="aac-actions">
            <?php if (\App\Services\Academy\LearnerAuth::emailVerificationOn()): ?><form method="post" action="<?= e(url('admin/academy/learners-send-confirmations')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit" data-loading-text="Sending...">Send confirmation e-mails</button></form><?php endif; ?>
            <form method="post" action="<?= e(url('admin/academy/learners-confirm-all')) ?>" class="inline-form" data-confirm="Approve all <?= (int) $waiting ?> waiting learners? They can then sign in without opening an e-mail."><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Approve all <?= (int) $waiting ?></button></form>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/learners', ['status' => 'unverified'])) ?>">Show only these</a>
        </div>
    </div>
<?php endif; ?>
<form class="filters" method="get" action="<?= e(url('admin/academy/learners')) ?>" role="search">
    <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Name, reg no, e-mail or phone" maxlength="80"></div>
    <div class="field"><label class="field__label" for="f-status">Status</label><select class="select" id="f-status" name="status"><option value="">Any</option>
        <?php foreach (['active' => 'Active', 'inactive' => 'Switched off', 'unverified' => 'Awaiting approval'] + (\App\Services\Academy\LearnerRegistry::rejectReady() ? ['rejected' => 'Rejected'] : []) as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['status'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="field__label" for="f-course">Course</label><select class="select" id="f-course" name="course"><option value="0">Any course</option>
        <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (int) $f['course'] === (int) $c['id'] ? ' selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
    <button class="btn btn--outline" type="submit">Filter</button>
</form>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No learner found', 'icon' => 'users', 'text' => 'Learners appear here after they sign up on the public Academy page.']) ?>
<?php else: ?>
    <p class="result-count"><?= (int) $total ?> learner<?= (int) $total === 1 ? '' : 's' ?></p>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Learners</caption>
        <thead><tr><th scope="col">Learner</th><th scope="col">Courses</th><th scope="col">Certificates</th><th scope="col">Status</th><th scope="col">Joined</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $l): ?>
            <tr class="<?= (int) $l['is_active'] === 1 ? '' : 'is-muted' ?>">
                <td class="cell-main" data-label="Learner"><?php if (!empty($l['photo_path'])): ?><img class="aac-avatar" src="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/photo')) ?>" alt="" width="40" height="40" loading="lazy"><?php endif; ?><span class="cell-title"><a href="<?= e(url('admin/academy/learners/' . (int) $l['id'])) ?>"><?= e($l['name']) ?></a></span><span class="cell-sub"><?= e($l['email']) ?></span><?php if ($manager && array_key_exists('msg_unread', $l)): ?><span class="cell-sub"><a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/messages')) ?>"><?= icon('message-circle', 'icon--sm') ?> Message<?php if ((int) $l['msg_unread'] > 0): ?> <span class="badge badge--warning"><?= (int) $l['msg_unread'] ?> new</span><?php endif; ?></a></span><?php endif; ?><?php if (!empty($l['reg_no'])): ?><span class="cell-sub"><?= e($l['reg_no']) ?></span><?php endif; ?></td>
                <td data-label="Courses"><?= (int) $l['courses'] ?></td>
                <td data-label="Certificates"><?= (int) $l['certificates'] ?><?php if (!empty($l['certs_waiting']) && $manager): ?>
                    <span class="cell-sub"><span class="badge badge--warning"><?= (int) $l['certs_waiting'] ?> waiting</span></span>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/approve-certificates')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="back" value="list"><button class="btn btn--primary btn--sm" type="submit">Approve certificate</button></form><?php endif; ?></td>
                <td data-label="Status"><?php if ((int) $l['is_active'] !== 1 && !empty($l['rejected_at'])): ?><span class="badge badge--danger">Rejected</span><?php if (!empty($l['reject_reason'])): ?><span class="cell-sub" title="<?= e($l['reject_reason']) ?>"><?= e(mb_strimwidth((string) $l['reject_reason'], 0, 40, '...')) ?></span><?php endif; ?><?php if ($manager): ?><form method="post" action="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/approve')) ?>" class="inline-form" data-confirm="Approve <?= e($l['name']) ?> after all? The rejection is removed and they can sign in."><?= csrf_field() ?><input type="hidden" name="back" value="list"><button class="btn btn--outline btn--sm" type="submit">Approve after all</button></form><?php endif; ?><?php elseif ((int) $l['is_active'] !== 1): ?><span class="badge badge--neutral">Switched off</span><?php elseif ($l['email_verified_at'] === null): ?><span class="badge badge--warning">Awaiting approval</span><?php if ($manager): ?><form method="post" action="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/approve')) ?>" class="inline-form"><?= csrf_field() ?><input type="hidden" name="back" value="list"><button class="btn btn--primary btn--sm" type="submit">Approve</button></form><?php if (array_key_exists('rejected_at', $l)): ?><form method="post" action="<?= e(url('admin/academy/learners/' . (int) $l['id'] . '/reject')) ?>" class="inline-form" data-confirm="Reject the application of <?= e($l['name']) ?>? They cannot sign in. You can add a reason on the learner page, and approve them again later."><?= csrf_field() ?><input type="hidden" name="back" value="list"><button class="btn btn--ghost btn--sm btn-danger-hover" type="submit">Reject</button></form><?php endif; ?><?php endif; ?><?php if (!empty($l['mail_error'])): ?><span class="cell-sub" title="<?= e($l['mail_error']) ?>">Could not send: <?= e(mb_strimwidth((string) $l['mail_error'], 0, 40, '...')) ?></span><?php elseif (!empty($l['mail_sent_at'])): ?><span class="cell-sub">Link sent <?= e(format_date($l['mail_sent_at'], 'j M')) ?></span><?php elseif (array_key_exists('mail_sent_at', $l)): ?><span class="cell-sub">No e-mail sent yet</span><?php endif; ?><?php else: ?><span class="badge badge--success">Active</span><?php if (!empty($l['approved_at'])): ?><span class="cell-sub">Approved <?= e(format_date($l['approved_at'], 'j M')) ?></span><?php endif; ?><?php endif; ?></td>
                <td data-label="Joined"><span class="nowrap"><?= e(format_date($l['created_at'], 'j M Y')) ?></span><span class="cell-sub"><?= $l['last_login_at'] ? 'Last seen ' . e(format_date($l['last_login_at'], 'j M')) : 'Never signed in' ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/academy/learners']) ?>
<?php endif; ?>
