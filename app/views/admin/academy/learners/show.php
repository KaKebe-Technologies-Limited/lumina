<?php /** One learner. Vars: $l, $enrolments (with progress, certificate), $attempts, $certs, $available (courses to enrol in), $manager. */
$lid = (int) $l['id']; ?>
<?= partial('partials/admin/page-header', ['title' => (string) $l['name'], 'subtitle' => $manager ? (string) $l['email'] : 'Learner in your courses', 'actions' => [['label' => 'All learners', 'url' => url('admin/academy/learners'), 'icon' => 'users', 'class' => 'btn--outline']]]) ?>

<div class="aac-grid">
    <section class="card" aria-labelledby="ln-info">
        <div class="card__header"><h2 class="card__title" id="ln-info">Details</h2></div>
        <div class="card__body">
            <?php $port = \App\Services\Academy\LearnerPortrait::of($lid); if (!empty($port['photo_path'])): ?>
                <div class="aac-portrait">
                    <img class="aac-portrait__img" src="<?= e(url('admin/academy/learners/' . $lid . '/photo')) ?>" alt="Portrait of <?= e($l['name']) ?>" width="120" height="120">
                    <p class="text-muted"><?= !empty($port['photo_consent_at']) ? 'The learner agreed to show it on the public Academy page.' : 'Private: only the learner and the Academy team can see it.' ?></p>
                    <?php if ($manager): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/photo-remove')) ?>" class="inline-form" data-confirm="Remove this portrait?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Remove the portrait</button></form><?php endif; ?>
                </div>
            <?php endif; ?>
            <dl class="aac-facts">
                <div><dt>E-mail</dt><dd><?= e($l['email']) ?> <?= $l['email_verified_at'] ? '<span class="badge badge--success">confirmed</span>' : '<span class="badge badge--warning">not confirmed</span>' ?></dd></div>
                <?php if ($manager): ?><div><dt>Phone</dt><dd><?= e((string) ($l['phone'] ?: '-')) ?></dd></div><div><dt>Country</dt><dd><?= e((string) ($l['country'] ?: '-')) ?></dd></div><?php endif; ?>
                <?php if ($manager && $l['email_verified_at'] === null): ?><div><dt>Confirmation e-mail</dt><dd><?php if (!empty($l['mail_error'])): ?><span class="badge badge--danger">Not delivered</span> <?= e($l['mail_error']) ?><?php elseif (!empty($l['mail_sent_at'])): ?>Sent <?= e(format_datetime($l['mail_sent_at'], 'j M Y, H:i')) ?> <span class="text-muted">(ask the learner to check the spam folder)</span><?php else: ?>No e-mail was sent yet<?php endif; ?></dd></div><?php endif; ?>
                <?php if ($manager && !empty($l['whatsapp'])): ?><div><dt>WhatsApp</dt><dd>+<?= e((string) $l['whatsapp']) ?></dd></div><?php endif; ?>
                <div><dt>Joined</dt><dd><?= e(format_datetime($l['created_at'], 'j M Y, H:i')) ?></dd></div>
                <div><dt>Last sign-in</dt><dd><?= $l['last_login_at'] ? e(format_datetime($l['last_login_at'], 'j M Y, H:i')) : 'Never' ?></dd></div>
                <div><dt>Account</dt><dd><?= (int) $l['is_active'] === 1 ? 'Active' : 'Switched off' ?></dd></div>
            </dl>
            <?php if ($manager): ?>
                <div class="aac-actions">
                    <?php if ($l['email_verified_at'] === null): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/send-confirmation')) ?>" class="inline-form" data-confirm="E-mail a new confirmation link to <?= e($l['email']) ?>?"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Send confirmation e-mail</button></form><?php if (!empty($l['whatsapp']) && \App\Services\Academy\WhatsAppSender::enabled()): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/send-whatsapp')) ?>" class="inline-form" data-confirm="Send a confirmation code to this learner on WhatsApp?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Send code on WhatsApp</button></form><?php endif; ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/verify')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Mark e-mail as confirmed</button></form><?php endif; ?>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/reset-link')) ?>" class="inline-form" data-confirm="E-mail a link to choose a new password to <?= e($l['email']) ?>?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('mail', 'icon--sm') ?> Send a password link</button></form>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/toggle')) ?>" class="inline-form" data-confirm="<?= (int) $l['is_active'] === 1 ? 'Switch this account off? The learner can no longer sign in (progress and certificates are kept).' : 'Switch this account on again?' ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= (int) $l['is_active'] === 1 ? 'Switch the account off' : 'Switch the account on' ?></button></form>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/delete')) ?>" class="inline-form" data-confirm="Delete this learner account and all progress? Certificates already issued stay valid. This cannot be undone."><?= csrf_field() ?><button class="btn btn--ghost btn--sm btn-danger-hover" type="submit"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card" aria-labelledby="ln-enr">
        <div class="card__header"><h2 class="card__title" id="ln-enr">Courses</h2></div>
        <div class="card__body">
            <?php if ($enrolments === []): ?><p class="text-muted">Not enrolled in any of the courses you can see.</p><?php endif; ?>
            <ul class="aac-list">
                <?php foreach ($enrolments as $e): $p = $e['progress']; ?>
                    <li>
                        <strong><?= e($e['title']) ?></strong>
                        <span><?= (int) $p['done'] ?>/<?= (int) $p['total'] ?> lessons (<?= (int) $p['pct'] ?>%)<?= $e['completed_at'] ? ' &middot; completed ' . e(format_date($e['completed_at'], 'j M Y')) : '' ?><?= $e['certificate'] ? ' &middot; certificate ' . e($e['certificate']['code']) : '' ?></span>
                        <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/unenroll/' . (int) $e['course_id'])) ?>" class="inline-form" data-confirm="Remove this learner from the course? Their progress in it is cleared."><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Remove</button></form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($available !== []): ?>
                <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/enroll')) ?>" class="aac-inline"><?= csrf_field() ?>
                    <div class="field"><label class="field__label" for="en-course">Enrol in a course</label><select class="select" id="en-course" name="course_id"><?php foreach ($available as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
                    <button class="btn btn--outline btn--sm" type="submit">Enrol</button></form>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($manager): ?>
<section class="card" aria-labelledby="ln-cert">
    <div class="card__header"><h2 class="card__title" id="ln-cert">Certificates</h2></div>
    <div class="card__body">
        <?php if ($certs === []): ?><p class="text-muted">No certificate yet.</p><?php endif; ?>
        <ul class="aac-list"><?php foreach ($certs as $c): ?>
            <?php $waitingCert = !\App\Services\Academy\CertificateService::isApproved($c); ?>
            <li><a href="<?= e($waitingCert ? url('admin/academy/certificates/' . (int) $c['id'] . '/preview') : url('academy/certificate/' . $c['code'])) ?>" target="_blank" rel="noopener"><?= e($c['code']) ?></a> <span><?= e($c['course_title']) ?> &middot; <?= e(format_date($c['issued_at'], 'j M Y')) ?><?= $c['revoked_at'] ? ' &middot; withdrawn' : ($waitingCert ? ' &middot; <a href="' . e(url('admin/academy/certificates', ['status' => 'waiting'])) . '">waiting for approval</a>' : '') ?></span></li>
        <?php endforeach; ?></ul>
        <?php if ($available !== [] || $enrolments !== []): $all = \App\Core\Database::all("SELECT id, title FROM academy_courses WHERE status <> 'archived' ORDER BY title"); ?>
            <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/certificate')) ?>" class="aac-inline" data-confirm="Issue a certificate for this course to <?= e($l['name']) ?> without an exam?"><?= csrf_field() ?>
                <div class="field"><label class="field__label" for="ce-course">Issue a certificate by hand</label><select class="select" id="ce-course" name="course_id"><?php foreach ($all as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?></select></div>
                <label class="check"><input type="checkbox" name="notify" value="1"><span>E-mail the certificate to the learner</span></label>
                <button class="btn btn--outline btn--sm" type="submit">Issue the certificate</button></form>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<section class="card" aria-labelledby="ln-att">
    <div class="card__header"><h2 class="card__title" id="ln-att">Recent quiz results</h2></div>
    <div class="card__body">
        <?php if ($attempts === []): ?><p class="text-muted">No quiz taken yet.</p><?php else: ?>
        <div class="table-wrap"><table class="table table--stack"><thead><tr><th scope="col">Quiz</th><th scope="col">Course</th><th scope="col">Score</th><th scope="col">Result</th><th scope="col">When</th></tr></thead><tbody>
            <?php foreach ($attempts as $a): ?>
                <tr><td data-label="Quiz"><?= e($a['quiz_title']) ?></td><td data-label="Course"><?= e($a['course_title']) ?></td>
                    <td data-label="Score"><?= (int) $a['correct'] ?>/<?= (int) $a['total'] ?> (<?= (int) round((float) $a['score_pct']) ?>%)</td>
                    <td data-label="Result"><span class="badge badge--<?= (int) $a['passed'] === 1 ? 'success' : 'warning' ?>"><?= (int) $a['passed'] === 1 ? 'Passed' : 'Not yet' ?></span></td>
                    <td data-label="When"><?= e(format_datetime($a['submitted_at'], 'j M, H:i')) ?></td></tr>
            <?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </div>
</section>
