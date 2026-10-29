<?php /** Admin > Academy > Payments. Vars: $rows, $pagination, $q, $status, $statuses, $total, $sums, $ready, $configured, $mode, $mock, $fee, $publicBase, $courses. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Certificate payments', 'subtitle' => 'Learners pay the certificate fee through Pesapal before their certificate is requested. A payment counts only when Pesapal itself confirms it.',
    'actions' => [['label' => 'Download CSV', 'url' => url('admin/academy/payments-export'), 'icon' => 'download', 'class' => 'btn--outline']]]) ?>
<?php if (!$ready): ?>
    <div class="alert alert--warning" role="alert"><div><strong>The payments table is not in the database yet.</strong> Import <code>database/updates/2026-10-academy-certificate-payments.sql</code> (phpMyAdmin > Import), or run <code>php bin/setup.php</code>. Until then certificates are issued without a payment.</div></div>
<?php endif; ?>
<div class="alert alert--<?= $configured ? 'info' : 'danger' ?>" role="status">
    <div>
    <?php if ($configured): ?>
        <strong>Pesapal is connected (<?= e($mode) ?><?= $mock ? ', LOCAL MOCK' : '' ?>).</strong>
        Fee: UGX <?= e(number_format($fee)) ?> per certificate (change it under Settings > LAF Academy, or per course). Pesapal returns learners to and notifies <code><?= e($publicBase) ?></code>.
        <?php if (str_contains($publicBase, 'localhost') || str_contains($publicBase, '127.0.0.1')): ?>Pesapal cannot reach a localhost address: payments are still confirmed when the learner comes back, but the background notification needs a public address (<code>PESAPAL_PUBLIC_URL</code> in .env).<?php endif; ?>
    <?php else: ?>
        <strong>Pesapal is not set up.</strong> Add <code>PESAPAL_CONSUMER_KEY</code> and <code>PESAPAL_CONSUMER_SECRET</code> to the .env file (docs/PESAPAL.md). Until then learners cannot pay and certificates that need a fee stay locked.
    <?php endif; ?>
    <div class="aac-actions"><form method="post" action="<?= e(url('admin/academy/payments/test')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Test the connection</button></form></div>
    </div>
</div>
<?php if ($ready): ?>
    <p class="text-muted">Received: <strong>UGX <?= e(number_format($sums['paid'])) ?></strong> from <?= (int) $sums['count'] ?> payment<?= (int) $sums['count'] === 1 ? '' : 's' ?>
        &middot; waiting: <?= (int) $sums['pending'] ?> &middot; needing review: <strong><?= (int) $sums['review'] ?></strong></p>
    <details class="aac-edit"><summary class="btn btn--outline btn--sm">Let a learner have a certificate without paying</summary>
        <form method="post" action="<?= e(url('admin/academy/payments/waive')) ?>" class="aac-edit__form" data-confirm="Let this learner have the certificate without paying?"><?= csrf_field() ?>
            <div class="field"><label class="field__label" for="wv-email">Learner's e-mail address</label><input class="input" id="wv-email" type="email" name="email" required maxlength="190"></div>
            <div class="field"><label class="field__label" for="wv-course">Course</label><select class="select" id="wv-course" name="course_id"><?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label class="field__label" for="wv-note">Why (kept in the list)</label><input class="input" id="wv-note" name="note" maxlength="200" placeholder="Scholarship, paid in cash at the office..."></div>
            <button class="btn btn--primary btn--sm" type="submit">Waive the fee</button></form></details>
    <form class="filters" method="get" action="<?= e(url('admin/academy/payments')) ?>" role="search">
        <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Reference, name, e-mail, course or Pesapal code" maxlength="80"></div>
        <div class="field"><label class="field__label" for="f-status">Status</label><select class="select" id="f-status" name="status"><option value="">Any</option><?php foreach ($statuses as $k => $label): ?><option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn--outline" type="submit">Filter</button>
    </form>
    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', ['title' => 'No payment found', 'icon' => 'award', 'text' => 'Payments appear here when a learner who finished a course pays for the certificate.']) ?>
    <?php else: ?>
        <div class="table-wrap"><table class="table table--stack">
            <caption class="visually-hidden">Certificate payments</caption>
            <thead><tr><th scope="col">Reference</th><th scope="col">Learner</th><th scope="col">Course</th><th scope="col">Amount</th><th scope="col">Status</th><th scope="col">Started</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $p): $id = (int) $p['id']; ?>
                <tr>
                    <td data-label="Reference"><code><?= e($p['merchant_ref']) ?></code><?php if (!empty($p['confirmation_code'])): ?><span class="cell-sub">Pesapal <?= e($p['confirmation_code']) ?></span><?php endif; ?></td>
                    <td class="cell-main" data-label="Learner"><span class="cell-title"><?= e($p['learner_name']) ?></span><span class="cell-sub"><?= e($p['learner_email']) ?></span></td>
                    <td data-label="Course"><?= e($p['course_title']) ?></td>
                    <td data-label="Amount"><span class="nowrap"><?= e($p['currency']) ?> <?= e(number_format((float) $p['amount'])) ?></span><?php if (!empty($p['method'])): ?><span class="cell-sub"><?= e($p['method']) ?></span><?php endif; ?></td>
                    <td data-label="Status"><?php $tone = ['paid' => 'success', 'waived' => 'info', 'pending' => 'warning', 'review' => 'warning', 'failed' => 'danger'][$p['status']] ?? 'info'; ?><span class="badge badge--<?= e($tone) ?>"><?= e($statuses[$p['status']] ?? $p['status']) ?></span><?php if (!empty($p['note'])): ?><span class="cell-sub"><?= e($p['note']) ?></span><?php endif; ?></td>
                    <td data-label="Started"><span class="nowrap"><?= e(format_date($p['created_at'], 'j M Y H:i')) ?></span></td>
                    <td class="col-actions"><div class="table__actions">
                        <?php if (in_array($p['status'], ['pending', 'failed'], true) && $p['tracking_id'] !== null): ?>
                            <form method="post" action="<?= e(url('admin/academy/payments/' . $id . '/recheck')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Check with Pesapal</button></form>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'review'): ?>
                            <form method="post" action="<?= e(url('admin/academy/payments/' . $id . '/accept')) ?>" class="inline-form" data-confirm="Accept this payment and file the learner's certificate request?"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Accept</button></form>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/academy/payments']) ?>
    <?php endif; ?>
<?php endif; ?>
