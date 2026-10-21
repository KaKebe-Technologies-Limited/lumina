<?php /** Admin > Academy > Certificates. Vars: $rows, $pagination, $q, $status, $total. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Certificates', 'subtitle' => 'Every certificate issued. Anyone can check one by its number on the public Academy; a withdrawn certificate says so there.',
    'actions' => [['label' => 'Download CSV', 'url' => url('admin/academy/certificates-export'), 'icon' => 'download', 'class' => 'btn--outline']]]) ?>
<form class="filters" method="get" action="<?= e(url('admin/academy/certificates')) ?>" role="search">
    <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Number, name or course" maxlength="80"></div>
    <div class="field"><label class="field__label" for="f-status">Status</label><select class="select" id="f-status" name="status"><option value="">Any</option><option value="valid"<?= $status === 'valid' ? ' selected' : '' ?>>Valid</option><option value="withdrawn"<?= $status === 'withdrawn' ? ' selected' : '' ?>>Withdrawn</option></select></div>
    <button class="btn btn--outline" type="submit">Filter</button>
</form>
<?php if ($rows === []): ?>
    <?= partial('partials/empty-state', ['title' => 'No certificate found', 'icon' => 'award', 'text' => 'Certificates are issued automatically when a learner passes a final exam.']) ?>
<?php else: ?>
    <div class="table-wrap"><table class="table table--stack">
        <caption class="visually-hidden">Certificates</caption>
        <thead><tr><th scope="col">Number</th><th scope="col">Learner</th><th scope="col">Course</th><th scope="col">Issued</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $c): $id = (int) $c['id']; ?>
            <tr class="<?= $c['revoked_at'] ? 'is-muted' : '' ?>">
                <td data-label="Number"><a href="<?= e(url('academy/certificate/' . $c['code'])) ?>" target="_blank" rel="noopener"><code><?= e($c['code']) ?></code></a></td>
                <td class="cell-main" data-label="Learner"><span class="cell-title"><?= e($c['learner_name']) ?></span><span class="cell-sub"><?= e((string) ($c['learner_email'] ?? 'account deleted')) ?></span></td>
                <td data-label="Course"><?= e($c['course_title']) ?><span class="cell-sub"><?= $c['score_pct'] !== null ? e(rtrim(rtrim(number_format((float) $c['score_pct'], 1), '0'), '.')) . '%' : 'no exam' ?></span></td>
                <td data-label="Issued"><span class="nowrap"><?= e(format_date($c['issued_at'], 'j M Y')) ?></span></td>
                <td data-label="Status"><?= $c['revoked_at'] ? '<span class="badge badge--danger">Withdrawn</span>' : '<span class="badge badge--success">Valid</span>' ?></td>
                <td class="col-actions"><div class="table__actions">
                    <?php if (!$c['revoked_at']): ?>
                        <a class="btn btn--ghost btn--icon" href="<?= e(url('academy/certificate/' . $c['code'] . '/pdf')) ?>" target="_blank" rel="noopener" title="Open the PDF" aria-label="PDF: <?= e($c['code']) ?>"><?= icon('download', 'icon--sm') ?></a>
                        <details class="aac-edit"><summary class="btn btn--outline btn--sm">Withdraw</summary>
                            <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/revoke')) ?>" class="aac-edit__form" data-confirm="Withdraw certificate <?= e($c['code']) ?>?"><?= csrf_field() ?>
                                <div class="field"><label class="field__label" for="rv-<?= $id ?>">Reason (optional, shown on the check page)</label><input class="input" id="rv-<?= $id ?>" name="reason" maxlength="255"></div>
                                <button class="btn btn--primary btn--sm" type="submit">Withdraw</button></form></details>
                    <?php else: ?>
                        <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/reinstate')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Reinstate</button></form>
                    <?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/academy/certificates']) ?>
<?php endif; ?>
