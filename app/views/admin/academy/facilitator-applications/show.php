<?php /** Admin > Academy > one facilitator application. Vars: $app, $docs, $creds, $topic, $reviewer. */
use App\Services\Academy\FacilitatorApplications;

$id = (int) $app['id'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-academy.js')) ?>" defer></script>
<?php $this->end();
$text = static fn(?string $t): string => nl2br(e((string) $t));
$links = array_values(array_filter(preg_split('/\s+/', (string) $app['links']) ?: []));
?>
<?= partial('partials/admin/page-header', ['title' => (string) $app['name'], 'subtitle' => $topic . ': ' . (string) $app['topic_detail'] . '. Status: ' . (FacilitatorApplications::STATUSES[$app['status']] ?? '')]) ?>
<?php if ($creds): ?>
    <section class="cred-card" aria-labelledby="cred-title" role="region">
        <h2 id="cred-title"><?= icon('lock', 'icon--sm') ?> Account created for <?= e($creds['name']) ?></h2>
        <p>The temporary password is shown now only. <strong>It will not be shown again.</strong> It must be replaced at the first sign-in.</p>
        <dl class="cred-card__list">
            <div><dt>Sign-in page</dt><dd><a href="<?= e(abs_url('admin/login')) ?>"><?= e(abs_url('admin/login')) ?></a></dd></div>
            <div><dt>Email</dt><dd><?= e($creds['email']) ?></dd></div>
            <div><dt>Temporary password</dt><dd><code class="cred-card__pw" id="cred-pw"><?= e($creds['password']) ?></code></dd></div>
        </dl>
        <button class="btn btn--primary btn--sm" type="button" data-copy="#cred-pw" data-copied-label="Copied">Copy the password</button>
    </section>
<?php endif; ?>
<div class="card-grid card-grid--main-aside">
    <div class="content-main">
        <section class="card" aria-labelledby="a-about"><div class="card__header"><h2 class="card__title" id="a-about">The applicant</h2></div>
            <div class="card__body"><dl class="dl">
                <div><dt>Name</dt><dd><?= e($app['name']) ?></dd></div>
                <div><dt>E-mail</dt><dd><a href="mailto:<?= e($app['email']) ?>"><?= e($app['email']) ?></a></dd></div>
                <?php if ($app['phone']): ?><div><dt>Phone</dt><dd><?= e($app['phone']) ?></dd></div><?php endif; ?>
                <?php if ($app['country']): ?><div><dt>Country</dt><dd><?= e($app['country']) ?></dd></div><?php endif; ?>
                <?php if ($app['headline']): ?><div><dt>Role</dt><dd><?= e($app['headline']) ?></dd></div><?php endif; ?>
                <?php if ($app['availability']): ?><div><dt>Availability</dt><dd><?= e($app['availability']) ?></dd></div><?php endif; ?>
                <div><dt>Sent</dt><dd><?= e(format_datetime($app['created_at'], 'j M Y, H:i')) ?></dd></div>
            </dl></div></section>
        <section class="card" aria-labelledby="a-exp"><div class="card__header"><h2 class="card__title" id="a-exp">Experience and motivation</h2></div>
            <div class="card__body"><h3>Experience and qualifications</h3><p><?= $text($app['experience']) ?></p>
                <h3>Why they want to volunteer</h3><p><?= $text($app['motivation']) ?></p>
                <?php if ($links): ?><h3>Links</h3><ul><?php foreach ($links as $l): ?><li><?= preg_match('~^https?://~i', $l) === 1 ? '<a href="' . e($l) . '" target="_blank" rel="noopener noreferrer nofollow">' . e($l) . '</a>' : e($l) ?></li><?php endforeach; ?></ul><?php endif; ?></div></section>
        <section class="card" aria-labelledby="a-files"><div class="card__header"><h2 class="card__title" id="a-files">CV and papers</h2></div>
            <div class="card__body"><ul class="ac-list">
                <?php if ($app['cv_path']): ?><li><a href="<?= e(url('admin/academy/facilitator-applications/' . $id . '/file/cv')) ?>"><?= icon('download', 'icon--sm') ?> CV: <?= e((string) $app['cv_name']) ?></a></li><?php endif; ?>
                <?php foreach ($docs as $i => $d): ?><li><a href="<?= e(url('admin/academy/facilitator-applications/' . $id . '/file/' . $i)) ?>"><?= icon('download', 'icon--sm') ?> <?= e($d['name']) ?></a> <span class="text-muted">(<?= e(number_format($d['size'] / 1024, 0)) ?> KB)</span></li><?php endforeach; ?>
                <?php if (!$app['cv_path'] && !$docs): ?><li class="text-muted">No files.</li><?php endif; ?>
            </ul><p class="text-muted">Files are private and open only for signed-in administrators.</p></div></section>
    </div>
    <aside class="content-aside" aria-label="Decision">
        <section class="card" aria-labelledby="a-dec"><div class="card__header"><h2 class="card__title" id="a-dec">Decision</h2></div>
            <form class="card__body" method="post" action="<?= e(url('admin/academy/facilitator-applications/' . $id)) ?>" novalidate>
                <?= csrf_field() ?>
                <?php if ($app['reviewed_at']): ?><p class="text-muted">Last reviewed <?= e(format_datetime($app['reviewed_at'], 'j M Y, H:i')) ?><?= $reviewer !== '' ? ' by ' . e($reviewer) : '' ?>.</p><?php endif; ?>
                <?= partial('partials/form-field', ['name' => 'applicant_note', 'label' => 'Message to the applicant (optional)', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 500, 'value' => (string) $app['applicant_note'], 'help' => 'Included in the e-mail that tells them the decision.']) ?>
                <?= partial('partials/form-field', ['name' => 'admin_note', 'label' => 'Internal note (never sent)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'value' => (string) $app['admin_note']]) ?>
                <?php if ($app['status'] !== 'approved'): ?>
                    <?= partial('partials/form-field', ['name' => 'email_credentials', 'label' => 'When approving, e-mail the temporary sign-in details to the applicant', 'type' => 'checkbox', 'value' => true]) ?>
                    <p><button class="btn btn--primary btn--block" type="submit" name="action" value="approve" data-confirm="Approve this application and create the facilitator account?"><?= icon('check', 'icon--sm') ?> Approve as facilitator</button></p>
                    <p><button class="btn btn--outline btn--block" type="submit" name="action" value="more_info">Ask for more information</button></p>
                    <p><button class="btn btn--outline btn--block btn-danger-hover" type="submit" name="action" value="reject" data-confirm="Decline this application? The applicant is e-mailed.">Decline</button></p>
                <?php else: ?>
                    <p class="opp-notice ac-ok"><?= icon('check-circle', 'icon--sm') ?> Approved. The account is under <a href="<?= e(url('admin/academy/facilitators')) ?>">Facilitators</a>.</p>
                <?php endif; ?>
                <p><button class="btn btn--ghost btn--block" type="submit" name="action" value="note">Save the notes only</button></p>
            </form>
        </section>
        <form method="post" action="<?= e(url('admin/academy/facilitator-applications/' . $id . '/delete')) ?>" data-confirm="Delete this application and its files for good?"><?= csrf_field() ?><button class="btn btn--ghost btn-danger-hover btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete the application</button></form>
    </aside>
</div>
