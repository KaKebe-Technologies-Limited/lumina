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
                <?php if (!empty($l['reg_no'])): ?><div><dt>Registration no.</dt><dd><strong><?= e($l['reg_no']) ?></strong></dd></div><?php endif; ?>
                <div><dt>E-mail</dt><dd><?= e($l['email']) ?> <?= $l['email_verified_at'] ? '<span class="badge badge--success">' . (!empty($l['approved_at']) ? 'approved' : 'confirmed') . '</span>' : '<span class="badge badge--warning">awaiting approval</span>' ?></dd></div>
                <?php if (!empty($l['approved_at'])): ?><div><dt>Approved</dt><dd>By an administrator on <?= e(format_datetime($l['approved_at'], 'j M Y, H:i')) ?></dd></div><?php endif; ?>
                <?php if ($manager): ?><div><dt>Phone</dt><dd><?= e(\App\Services\Academy\LearnerPhones::display($l['phone'], $l['country']) ?: '-') ?></dd></div><div><dt>Country</dt><dd><?= e((string) ($l['country'] ?: '-')) ?><?= \App\Services\Academy\LearnerPhones::dialCode($l['country']) !== '' ? ' (' . e(\App\Services\Academy\LearnerPhones::dialCode($l['country'])) . ')' : '' ?></dd></div><?php endif; ?>
                <?php if ($manager && $l['email_verified_at'] === null): ?><div><dt>Confirmation e-mail</dt><dd><?php if (!empty($l['mail_error'])): ?><span class="badge badge--danger">Not delivered</span> <?= e($l['mail_error']) ?><?php elseif (!empty($l['mail_sent_at'])): ?>Sent <?= e(format_datetime($l['mail_sent_at'], 'j M Y, H:i')) ?> <span class="text-muted">(ask the learner to check the spam folder)</span><?php else: ?>No e-mail was sent yet<?php endif; ?></dd></div><?php endif; ?>
                <?php if ($manager && !empty($l['bio'])): ?><div><dt>Bio</dt><dd><?= e((string) $l['bio']) ?></dd></div><?php endif; ?>
                <?php if ($manager && !empty($l['whatsapp'])): ?><div><dt>WhatsApp</dt><dd><a href="https://wa.me/<?= e((string) $l['whatsapp']) ?>" target="_blank" rel="noopener">+<?= e((string) $l['whatsapp']) ?></a></dd></div><?php endif; ?>
                <div><dt>Joined</dt><dd><?= e(format_datetime($l['created_at'], 'j M Y, H:i')) ?></dd></div>
                <div><dt>Last sign-in</dt><dd><?= $l['last_login_at'] ? e(format_datetime($l['last_login_at'], 'j M Y, H:i')) : 'Never' ?></dd></div>
                <div><dt>Account</dt><dd><?= !empty($l['rejected_at']) ? '<span class="badge badge--danger">Rejected</span> ' . e(format_datetime($l['rejected_at'], 'j M Y, H:i')) : ((int) $l['is_active'] === 1 ? 'Active' : 'Switched off') ?></dd></div>
                <?php if (!empty($l['rejected_at']) && !empty($l['reject_reason'])): ?><div><dt>Reason given</dt><dd><?= e((string) $l['reject_reason']) ?></dd></div><?php endif; ?>
            </dl>
            <?php if ($manager): ?>
                <div class="aac-actions">
                    <?php if (\App\Services\Academy\LearnerMessages::ready()): $mu = \App\Services\Academy\LearnerMessages::unreadFrom($lid); ?><a class="btn btn--primary btn--sm" href="<?= e(url('admin/academy/learners/' . $lid . '/messages')) ?>"><?= icon('message-circle', 'icon--sm') ?> Message <?= e((string) $l['name']) ?><?php if ($mu > 0): ?> (<?= $mu ?> new)<?php endif; ?></a><?php endif; ?>
                    <?php if (($l['fee_status'] ?? '') === 'paid' && \App\Services\Academy\LearnerMessages::ready()): $welcomedN = (int) \App\Core\Database::value("SELECT COUNT(*) FROM academy_messages WHERE learner_id = ? AND sender = 'admin' AND body LIKE ?", [$lid, \App\Services\Academy\AcademyFees::WELCOME_MARK . '%'], 0); $welUrl = \App\Services\Academy\AcademyFees::welcomeWhatsAppUrl($l); ?><span class="badge badge--success">Sign-up fee paid</span> <?= $welcomedN > 0 ? '<span class="badge badge--neutral">Welcome sent</span> ' : '' ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/acknowledge')) ?>" class="inline-form" data-confirm="Send <?= e($l['name']) ?> a thank-you and welcome message?"><?= csrf_field() ?><label class="text-muted" style="font-size:.85rem"><input type="checkbox" name="email" value="1"> also e-mail</label> <button class="btn btn--primary btn--sm" type="submit"><?= icon('check-circle', 'icon--sm') ?> <?= $welcomedN > 0 ? 'Send the welcome again' : 'Acknowledge payment and welcome' ?></button></form> <?php if ($welUrl !== null): ?><a class="btn btn--outline btn--sm" href="<?= e($welUrl) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon--sm') ?> Welcome on WhatsApp</a><?php endif; ?><?php endif; ?>
                    <?php if (\App\Services\Academy\AcademyFees::signupDue($l)): ?><span class="badge badge--warning">Sign-up fee unpaid</span> <?php $waUrl = \App\Services\Academy\AcademyFees::reminderWhatsAppUrl($l); ?><?php if (\App\Services\Academy\LearnerMessages::ready()): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/remind-fee')) ?>" class="inline-form" data-confirm="Send the payment reminder to <?= e($l['name']) ?> as a message in the learner portal?"><?= csrf_field() ?><label class="text-muted" style="font-size:.85rem"><input type="checkbox" name="email" value="1"> also e-mail</label> <button class="btn btn--outline btn--sm" type="submit"><?= icon('bell', 'icon--sm') ?> Remind: message</button></form> <?php endif; ?><?php if ($waUrl !== null): ?><a class="btn btn--outline btn--sm" href="<?= e($waUrl) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon--sm') ?> Remind: WhatsApp</a> <?php endif; ?><?php if (!empty($l['fee_reported_at'])): ?> <span class="badge badge--info">Payment reported</span> <span class="text-muted">ID <strong><?= e((string) $l['fee_reference']) ?></strong>, <?= e(format_datetime($l['fee_reported_at'], 'j M, H:i')) ?><?php if (!empty($l['fee_proof_path'])): ?> &middot; <a href="<?= e(url('admin/academy/learners/' . $lid . '/fee-proof')) ?>" target="_blank" rel="noopener">see the receipt</a><?php endif; ?></span><?php endif; ?> <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/mark-fee-paid')) ?>" class="inline-form" data-confirm="Mark the sign-up fee of <?= e($l['name']) ?> as PAID? Do this after you have seen the money (for example in the Daraza dashboard)."><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Mark the fee as paid</button></form> <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/waive-fee')) ?>" class="inline-form" data-confirm="Waive the sign-up fee of <?= e($l['name']) ?>? They can then be approved and sign in without paying."><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Waive the fee</button></form><?php elseif (($l['fee_status'] ?? 'none') !== 'none'): ?><span class="badge badge--neutral"><?= e(\App\Services\Academy\AcademyFees::label($l['fee_status'] ?? null)) ?></span><?php endif; ?>
<?php if ($l['email_verified_at'] === null): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/approve')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Approve (no e-mail needed)</button></form><?php if (\App\Services\Academy\AcademyFees::signupDue($l)): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/approve-waive')) ?>" class="inline-form" data-confirm="Approve this account and waive the sign-up fee? The learner can sign in without paying."><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('check-circle', 'icon--sm') ?> Approve and waive the fee</button></form><?php endif; ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/send-confirmation')) ?>" class="inline-form" data-confirm="E-mail a new confirmation link to <?= e($l['email']) ?>?"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Send confirmation e-mail</button></form><?php if (!empty($l['whatsapp']) && \App\Services\Academy\WhatsAppSender::enabled()): ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/send-whatsapp')) ?>" class="inline-form" data-confirm="Send a confirmation code to this learner on WhatsApp?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Send code on WhatsApp</button></form><?php endif; ?><form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/verify')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Mark e-mail as confirmed</button></form><?php endif; ?>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/reset-link')) ?>" class="inline-form" data-confirm="E-mail a link to choose a new password to <?= e($l['email']) ?>?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('mail', 'icon--sm') ?> Send a password link</button></form>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/toggle')) ?>" class="inline-form" data-confirm="<?= (int) $l['is_active'] === 1 ? 'Switch this account off? The learner can no longer sign in (progress and certificates are kept).' : 'Switch this account on again?' ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= (int) $l['is_active'] === 1 ? 'Switch the account off' : 'Switch the account on' ?></button></form>
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/delete')) ?>" class="inline-form" data-confirm="Delete this learner account and all progress? Certificates already issued stay valid. This cannot be undone."><?= csrf_field() ?><button class="btn btn--ghost btn--sm btn-danger-hover" type="submit"><?= icon('trash', 'icon--sm') ?> Delete</button></form>
                </div>
                <?php if (\App\Services\Academy\FeaturedLearners::ready() && (int) $l['is_active'] === 1 && $l['email_verified_at'] !== null): $feat = \App\Services\Academy\FeaturedLearners::isFeatured($l); ?>
                    <div class="aac-note" style="margin-top:1rem;padding:1rem;border:1px solid var(--border);border-radius:12px;background:var(--surface)">
                        <strong><?= icon('star', 'icon--sm') ?> Featured student</strong>
                        <?php if ($feat): ?>
                            <p class="text-muted">Featured on the public Academy page since <?= e(format_date($l['featured_at'], 'j M Y')) ?>. Visitors see: <strong><?= e(trim((string) strtok(trim((string) $l['name']), ' '))) ?></strong>, the country<?= !empty(\App\Services\Academy\LearnerPortrait::of($lid)['photo_consent_at']) ? ', the picture' : '' ?> and the highlight below.</p>
                        <?php else: ?>
                            <p class="text-muted">Show this learner on the public LAF Academy page, with the first name and the first letter of the surname, the country, the courses completed<?= !empty(\App\Services\Academy\LearnerPortrait::of($lid)['photo_consent_at']) ? ', the picture (the learner agreed to show it)' : ' (no picture: the learner has not agreed to show one)' ?> and a short highlight. Nothing else is shown.</p>
                        <?php endif; ?>
                        <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/feature')) ?>">
                            <?= csrf_field() ?>
                            <div class="field"><label class="field__label" for="feat-note">Highlight (shown on the website, up to 400 characters)</label><textarea class="textarea" id="feat-note" name="note" rows="3" maxlength="400" placeholder="Example: Completed Computer Basics and now helps her family business go online."><?= e((string) ($l['featured_note'] ?? ($l['bio'] ?? ''))) ?></textarea></div>
                            <label class="aac-remind__check"><input type="checkbox" name="agreed" value="1"<?= $feat ? ' checked' : '' ?>> I have the learner's permission to feature them on the website</label>
                            <button class="btn btn--primary btn--sm" type="submit"><?= icon('star', 'icon--sm') ?> <?= $feat ? 'Save the highlight' : 'Feature on the website' ?></button>
                        </form>
                        <?php if ($feat): ?>
                            <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/unfeature')) ?>" class="inline-form" data-confirm="Take <?= e($l['name']) ?> off the Academy page?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Remove from featured</button></form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (\App\Services\Academy\LearnerRegistry::rejectReady()): ?>
                    <?php if (!empty($l['rejected_at'])): ?>
                        <div class="aac-reject">
                            <p>This application was <strong>rejected</strong>. The learner cannot sign in; if they try, they see the reason and can message the team.</p>
                            <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/approve')) ?>" class="inline-form" data-confirm="Approve <?= e($l['name']) ?> after all? The rejection is removed and they can sign in."><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Approve after all</button></form>
                        </div>
                    <?php elseif ($l['email_verified_at'] === null && (int) $l['is_active'] === 1): ?>
                        <details class="aac-reject">
                            <summary class="btn btn--ghost btn--sm btn-danger-hover">Reject this application</summary>
                            <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/reject')) ?>" novalidate>
                                <?= csrf_field() ?>
                                <div class="field">
                                    <label class="field__label" for="rj-reason">Reason (shown to the applicant when they try to sign in)</label>
                                    <textarea class="textarea" id="rj-reason" name="reason" rows="3" maxlength="300" placeholder="For example: we could not verify your details."></textarea>
                                </div>
                                <label class="check"><input type="checkbox" name="email" value="1"><span>Also e-mail them (uses your e-mail allowance: leave it off when e-mail is limited)</span></label>
                                <p><button class="btn btn--primary btn--sm" type="submit" data-confirm="Reject this application? The learner cannot sign in. You can approve them again later.">Reject the application</button></p>
                            </form>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>
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
        <?php $waitingHere = array_values(array_filter($certs, static fn(array $x): bool => !\App\Services\Academy\CertificateService::isApproved($x) && $x['revoked_at'] === null)); ?>
        <?php if ($waitingHere !== []): ?>
            <div class="alert alert--warning" role="status"><strong>Waiting for approval: <?= count($waitingHere) ?> certificate<?= count($waitingHere) === 1 ? '' : 's' ?>.</strong> The learner asked for <?= count($waitingHere) === 1 ? 'it' : 'them' ?> after passing.
                <div class="aac-actions">
                    <form method="post" action="<?= e(url('admin/academy/learners/' . $lid . '/approve-certificates')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Approve</button><button class="btn btn--outline btn--sm" type="submit" name="email" value="1">Approve and e-mail</button></form>
                    <?php foreach ($waitingHere as $wc): ?>
                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/certificates/' . (int) $wc['id'] . '/preview')) ?>" target="_blank" rel="noopener">Preview <?= e($wc['code']) ?></a>
                        <details class="aac-edit"><summary class="btn btn--outline btn--sm">Decline</summary>
                            <form method="post" action="<?= e(url('admin/academy/certificates/' . (int) $wc['id'] . '/decline')) ?>" class="aac-edit__form" data-confirm="Decline this request?"><?= csrf_field() ?><input type="hidden" name="back" value="learner:<?= $lid ?>">
                                <div class="field"><label class="field__label" for="dc-<?= (int) $wc['id'] ?>">Message to the learner (optional)</label><input class="input" id="dc-<?= (int) $wc['id'] ?>" name="reason" maxlength="300"></div>
                                <button class="btn btn--primary btn--sm" type="submit">Decline and tell the learner</button></form></details>
                    <?php endforeach; ?>
                </div></div>
        <?php endif; ?>
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
