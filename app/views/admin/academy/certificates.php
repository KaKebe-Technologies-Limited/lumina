<?php /** Admin > Academy > Certificates. Vars: $rows, $pagination, $q, $status, $total, $waiting (certificates waiting for approval), $approved (approved ones), $ready (approval columns exist). */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Certificates', 'subtitle' => 'Every certificate issued. Anyone can check one by its number on the public Academy; a withdrawn certificate says so there.',
    'actions' => [['label' => 'Download CSV', 'url' => url('admin/academy/certificates-export'), 'icon' => 'download', 'class' => 'btn--outline']]]) ?>
<?php if (!empty($waiting)): ?>
    <div class="alert alert--warning" role="status">
        <strong><?= (int) $waiting ?> certificate<?= (int) $waiting === 1 ? '' : 's' ?> waiting for your approval.</strong>
        A learner cannot open or download it, and the public check page does not know it, until you approve it. A certificate can be viewed only once it has been issued.
        <div class="aac-actions">
            <form method="post" action="<?= e(url('admin/academy/certificates-approve-all')) ?>" class="inline-form" data-confirm="Approve all <?= (int) $waiting ?> waiting certificates?"><?= csrf_field() ?>
                <label class="check"><input type="checkbox" name="email" value="1" checked><span>E-mail each learner</span></label>
                <button class="btn btn--primary btn--sm" type="submit">Approve all</button></form>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/certificates', ['status' => 'waiting'])) ?>">Show only these</a>
        </div>
        <?php $askN = \App\Services\Academy\CertificatePayReminders::count(); ?>
        <?php if ($askN > 0): ?>
            <div class="aac-actions">
                <form method="post" action="<?= e(url('admin/academy/certificates-remind-pay-all')) ?>" class="inline-form" data-confirm="Ask every learner with a waiting certificate to pay the certificate fee (<?= $askN ?> certificate<?= $askN === 1 ? '' : 's' ?>)? Each learner gets a portal message and an e-mail, and the certificates are held until the fee is paid. A learner already reminded in the last 24 hours is skipped."><?= csrf_field() ?><input type="hidden" name="back" value="waiting">
                    <button class="btn btn--outline btn--sm" type="submit"><?= icon('mail', 'icon--sm') ?> Remind all to pay first (message + e-mail)</button></form>
                <span class="text-muted">Asks the <?= $askN ?> unpaid learner<?= $askN === 1 ? '' : 's' ?> to pay before you approve.</span>
            </div>
        <?php elseif (!empty($ready) && \App\Services\Academy\AcademyFees::cert() === null): ?>
            <p class="text-muted">To ask learners to pay before approval, set the certificate fee and switch a payment way on (Admin &gt; Pesapal payments &gt; LAF Academy fees), then a reminder button appears here.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if (!empty($ready) && \App\Services\Academy\AcademyFees::cert() !== null && ($freeN = \App\Services\Academy\AcademyFees::certificatesWithoutFee()) > 0): ?>
    <div class="alert alert--warning" role="status">
        <strong><?= (int) $freeN ?> certificate<?= (int) $freeN === 1 ? ' is' : 's are' ?> given without the certificate fee.</strong>
        Hold them all until the fee is paid: each learner then pays on the portal and gets the certificate the moment Pesapal confirms it. You can still release a certificate, issue one by hand or waive a fee for anyone.
        <div class="aac-actions">
            <form method="post" action="<?= e(url('admin/academy/certificates-hold-all')) ?>" class="inline-form" data-confirm="Hold all <?= (int) $freeN ?> certificates until their fee is paid? Learners cannot open them (and the public check page will not accept them) until the fee is paid, released or waived."><?= csrf_field() ?>
                <button class="btn btn--primary btn--sm" type="submit"><?= icon('lock', 'icon--sm') ?> Hold all <?= (int) $freeN ?> until the fee is paid</button></form>
        </div>
    </div>
<?php endif; ?>
<?php $unpaidCerts = !empty($ready) && \App\Services\Academy\AcademyFees::cert() !== null ? count(\App\Services\Academy\AcademyFees::unpaidCerts()) : 0; ?>
<?php if ($unpaidCerts > 0): ?>
    <div class="alert alert--warning aac-note" role="status">
        <div class="aac-note__text">
            <strong class="aac-note__title"><?= $unpaidCerts ?> certificate<?= $unpaidCerts === 1 ? ' is' : 's are' ?> waiting for the certificate fee.</strong>
            <p>Remind the learners to pay: they get a message in the learner portal (and an e-mail if you tick the box), or you can write to them on WhatsApp. Each certificate is released the moment its payment is confirmed.</p>
        </div>
        <div class="aac-actions">
            <form method="post" action="<?= e(url('admin/academy/certificates-remind-fee-all')) ?>" class="inline-form aac-remind" data-confirm="Send the payment reminder to all <?= $unpaidCerts ?> learners whose certificate waits for its fee?"><?= csrf_field() ?><label class="aac-remind__check"><input type="checkbox" name="email" value="1"> Also e-mail</label> <button class="btn btn--primary btn--sm" type="submit"><?= icon('bell', 'icon--sm') ?> Remind all <?= $unpaidCerts ?>: message</button></form>
            <form method="post" action="<?= e(url('admin/academy/certificates-remind-fee-whatsapp-all')) ?>" class="inline-form" data-confirm="<?= e(\App\Services\Academy\WhatsAppSender::certReminderEnabled() ? 'Send the WhatsApp payment reminder to all ' . $unpaidCerts . ' learners?' : 'Open the WhatsApp list for all ' . $unpaidCerts . ' learners?') ?>"><?= csrf_field() ?><button class="btn btn--whatsapp btn--sm" type="submit"><?= icon('whatsapp', 'icon--sm') ?> WhatsApp all <?= $unpaidCerts ?></button></form>
            <a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/certificates-fee-reminders')) ?>"><?= icon('whatsapp', 'icon--sm') ?> Remind on WhatsApp</a>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($ready) && !empty($approved)): ?>
    <div class="alert alert--info" role="status">
        <strong><?= (int) $approved ?> certificate<?= (int) $approved === 1 ? ' is' : 's are' ?> approved and available to the learners.</strong>
        To make every certificate wait for your approval before anybody receives it, take them all back: learners lose access and the public check page stops accepting them until you approve each one again (e-mails already sent cannot be recalled).
        <div class="aac-actions">
            <form method="post" action="<?= e(url('admin/academy/certificates-withdraw-all')) ?>" class="inline-form" data-confirm="Take back all <?= (int) $approved ?> approved certificates? Every one waits for your approval again. Learners cannot open them and the public check page will not accept them until you approve."><?= csrf_field() ?>
                <button class="btn btn--outline btn--sm" type="submit">Take back all <?= (int) $approved ?> for approval</button></form>
        </div>
    </div>
<?php endif; ?>
<form class="filters" method="get" action="<?= e(url('admin/academy/certificates')) ?>" role="search">
    <div class="field field--grow"><label class="field__label" for="f-q">Search</label><input class="input" id="f-q" type="search" name="q" value="<?= e($q) ?>" placeholder="Number, name or course" maxlength="80"></div>
    <div class="field"><label class="field__label" for="f-status">Status</label><select class="select" id="f-status" name="status"><option value="">Any</option><option value="waiting"<?= $status === 'waiting' ? ' selected' : '' ?>>Waiting for approval</option><option value="valid"<?= $status === 'valid' ? ' selected' : '' ?>>Valid</option><option value="withdrawn"<?= $status === 'withdrawn' ? ' selected' : '' ?>>Withdrawn</option></select></div>
    <div class="field"><label class="field__label" for="f-login">Sign-in</label><select class="select" id="f-login" name="login"><?php foreach (\App\Services\Academy\LearnerRegistry::LOGIN_FILTER as $k => $l): ?><option value="<?= e($k) ?>"<?= ($login ?? '') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
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
            <?php $isWaiting = !empty($ready) && $c['approved_at'] === null && !$c['revoked_at']; ?>
            <tr class="<?= $c['revoked_at'] ? 'is-muted' : '' ?>">
                <td data-label="Number"><a href="<?= e(url('academy/certificate/' . $c['code'])) ?>" target="_blank" rel="noopener"><code><?= e($c['code']) ?></code></a></td>
                <td class="cell-main" data-label="Learner"><span class="cell-title"><?= e($c['learner_name']) ?></span><span class="cell-sub"><?= e((string) ($c['learner_email'] ?? 'account deleted')) ?></span></td>
                <td data-label="Course"><?= e($c['course_title']) ?><span class="cell-sub"><?= $c['score_pct'] !== null ? e(rtrim(rtrim(number_format((float) $c['score_pct'], 1), '0'), '.')) . '%' : 'no exam' ?></span></td>
                <td data-label="Issued"><span class="nowrap"><?= e(format_date($c['issued_at'], 'j M Y')) ?></span></td>
                <td data-label="Status"><?= $c['revoked_at'] ? '<span class="badge badge--danger">Withdrawn</span>' : ($isWaiting ? (\App\Services\Academy\AcademyFees::certDue($c) ? '<span class="badge badge--warning">Fee unpaid</span>' : '<span class="badge badge--warning">Waiting for approval</span>') : '<span class="badge badge--success">Valid</span>') ?><?php if (!empty($c['emailed_at'])): ?><span class="cell-sub">E-mailed <?= e(format_date($c['emailed_at'], 'j M')) ?></span><?php endif; ?></td>
                <td class="col-actions"><div class="table__actions">
                    <?php if ($isWaiting): ?>
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/certificates/' . $id . '/preview')) ?>" target="_blank" rel="noopener" title="Only administrators can see it until it is released to the learner" aria-label="View certificate <?= e($c['code']) ?> (not released)"><?= icon('eye', 'icon--sm') ?> View (not released)</a>
                        <?php if (\App\Services\Academy\AcademyFees::certDue($c)): ?><form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/mark-fee-paid')) ?>" class="inline-form" data-confirm="Mark the fee of <?= e($c['code']) ?> as PAID? Do this after you have seen the money (for example in the Daraza dashboard)."><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($status) ?>"><button class="btn btn--outline btn--sm" type="submit">Mark fee paid</button></form><?php endif; ?>
                        <?php if (\App\Services\Academy\AcademyFees::cert() !== null && in_array($c['fee_status'] ?? 'none', ['none', 'due'], true)): ?><form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/remind-pay')) ?>" class="inline-form" data-confirm="Ask <?= e($c['learner_name']) ?> to pay the certificate fee before you approve? A portal message and an e-mail are sent."><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($status) ?>"><button class="btn btn--outline btn--sm" type="submit" title="Message and e-mail the learner to pay for this certificate"><?= icon('mail', 'icon--sm') ?> Remind to pay</button></form><?php endif; ?>
                        <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/approve')) ?>" class="inline-form"<?= \App\Services\Academy\AcademyFees::certDue($c) ? ' data-confirm="The certificate fee is not paid. Waive it and approve this certificate?"' : '' ?>><?= csrf_field() ?><?= \App\Services\Academy\AcademyFees::certDue($c) ? '<input type="hidden" name="waive" value="1">' : '' ?><input type="hidden" name="back" value="<?= e($status) ?>"><button class="btn btn--primary btn--sm" type="submit"><?= \App\Services\Academy\AcademyFees::certDue($c) ? 'Release (waive the fee)' : 'Approve' ?></button><button class="btn btn--outline btn--sm" type="submit" name="email" value="1"><?= \App\Services\Academy\AcademyFees::certDue($c) ? 'Release and e-mail' : 'Approve and e-mail' ?></button></form>
                        <details class="aac-edit"><summary class="btn btn--ghost btn--sm">Decline</summary>
                            <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/decline')) ?>" class="aac-edit__form" data-confirm="Decline the request for <?= e($c['code']) ?>?"><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($status) ?>">
                                <div class="field"><label class="field__label" for="dc-<?= $id ?>">Message to the learner (optional)</label><input class="input" id="dc-<?= $id ?>" name="reason" maxlength="300"></div>
                                <button class="btn btn--primary btn--sm" type="submit">Decline and tell the learner</button></form></details>
                    <?php endif; ?>
                    <?php if (!$c['revoked_at'] && !$isWaiting): ?>
                        <a class="btn btn--primary btn--sm" href="<?= e(url('admin/academy/certificates/' . $id . '/preview')) ?>" target="_blank" rel="noopener" title="Open the certificate (issued)" aria-label="View certificate <?= e($c['code']) ?>"><?= icon('eye', 'icon--sm') ?> View certificate</a>
                        <a class="btn btn--ghost btn--icon" href="<?= e(url('academy/certificate/' . $c['code'] . '/pdf')) ?>" target="_blank" rel="noopener" title="Open the PDF" aria-label="PDF: <?= e($c['code']) ?>"><?= icon('download', 'icon--sm') ?></a>
                        <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/email')) ?>" class="inline-form" data-confirm="E-mail certificate <?= e($c['code']) ?> to the learner?"><?= csrf_field() ?><button class="btn btn--ghost btn--icon" type="submit" title="E-mail it to the learner" aria-label="E-mail: <?= e($c['code']) ?>"><?= icon('mail', 'icon--sm') ?></button></form>
                        <details class="aac-edit"><summary class="btn btn--outline btn--sm">Withdraw</summary>
                            <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/revoke')) ?>" class="aac-edit__form" data-confirm="Withdraw certificate <?= e($c['code']) ?>?"><?= csrf_field() ?>
                                <div class="field"><label class="field__label" for="rv-<?= $id ?>">Reason (optional, shown on the check page)</label><input class="input" id="rv-<?= $id ?>" name="reason" maxlength="255"></div>
                                <button class="btn btn--primary btn--sm" type="submit">Withdraw</button></form></details>
                    <?php elseif ($c['revoked_at']): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/certificates/' . $id . '/preview')) ?>" target="_blank" rel="noopener" aria-label="View certificate <?= e($c['code']) ?> (withdrawn)"><?= icon('eye', 'icon--sm') ?> View</a>
                        <form method="post" action="<?= e(url('admin/academy/certificates/' . $id . '/reinstate')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Reinstate</button></form>
                    <?php endif; ?>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?= partial('admin/_content/pager', ['pagination' => $pagination, 'path' => 'admin/academy/certificates']) ?>
<?php endif; ?>
