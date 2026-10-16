<?php
/**
 * Admin > one job application. Vars: $a (row + job_title), $others, $statuses, $hasCv, $canDelete.
 */
use App\Services\Inbox\Support;

$linkedin = Support::webUrl($a['linkedin_url'] ?? '');
$mailto = Support::mailto($a['email'], $a['job_title'] ? 'Your application: ' . $a['job_title'] : '');
$actions = [];
if ($hasCv) {
    $actions[] = ['label' => 'Download CV', 'url' => url('admin/applications/' . (int) $a['id'] . '/cv'), 'class' => 'btn--primary', 'icon' => 'download'];
}
if ($mailto !== '') {
    $actions[] = ['label' => 'Email applicant', 'url' => $mailto, 'class' => 'btn--outline', 'icon' => 'mail'];
}
$actions[] = ['label' => 'All applications', 'url' => url('admin/applications'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', [
        'title'    => $a['name'],
        'subtitle' => ($a['job_title'] ? 'Applied for ' . $a['job_title'] : 'General application') . ' - received ' . format_datetime($a['created_at']),
        'actions'  => $actions,
    ]) ?>

    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card">
                <div class="card__header">
                    <h2 class="card__title">Applicant</h2>
                    <?= partial('partials/admin/status-badge', ['status' => $a['status'], 'label' => $statuses[$a['status']] ?? null]) ?>
                </div>
                <div class="card__body">
                    <dl class="definition-list">
                        <dt>Name</dt><dd><?= e($a['name']) ?></dd>
                        <dt>Email</dt><dd><?php if (Support::mailto($a['email']) !== ''): ?><a href="<?= e(Support::mailto($a['email'])) ?>"><?= e($a['email']) ?></a><?php else: ?><?= e($a['email']) ?><?php endif; ?></dd>
                        <?php if (!empty($a['phone'])): ?><dt>Phone</dt><dd><a href="<?= e(phone_href($a['phone'])) ?>"><?= e($a['phone']) ?></a></dd><?php endif; ?>
                        <dt>Job</dt>
                        <dd>
                            <?php if (!empty($a['job_id']) && !empty($a['job_title'])): ?>
                                <a href="<?= e(url('admin/applications', ['job' => (int) $a['job_id']])) ?>"><?= e($a['job_title']) ?></a>
                            <?php else: ?>
                                <span class="text-muted">Not linked to a job (general application, or the job was removed)</span>
                            <?php endif; ?>
                        </dd>
                        <?php if ($linkedin !== ''): ?><dt>LinkedIn</dt><dd><a href="<?= e($linkedin) ?>" target="_blank" rel="noopener noreferrer"><?= e($linkedin) ?></a></dd><?php endif; ?>
                        <?php if ($others > 0): ?><dt>Other applications</dt><dd><a href="<?= e(url('admin/applications', ['q' => $a['email']])) ?>"><?= (int) $others ?> more from this email address</a></dd><?php endif; ?>
                    </dl>
                </div>
            </section>

            <section class="card">
                <div class="card__header"><h2 class="card__title">CV</h2></div>
                <div class="card__body">
                    <?php if ($hasCv): ?>
                        <div class="inbox-file">
                            <?= icon('file-text', 'icon--lg') ?>
                            <div>
                                <strong><?= e($a['cv_original_name'] ?: 'CV') ?></strong>
                                <span class="cell-sub"><?= e(strtoupper(pathinfo((string) $a['cv_path'], PATHINFO_EXTENSION))) ?><?= !empty($a['cv_size']) ? ' - ' . e(human_filesize((int) $a['cv_size'])) : '' ?></span>
                            </div>
                            <a class="btn btn--primary" href="<?= e(url('admin/applications/' . (int) $a['id'] . '/cv')) ?>"><?= icon('download', 'icon--sm') ?> Download</a>
                        </div>
                        <p class="small text-muted">Stored privately. Each download is recorded in the audit log.</p>
                    <?php else: ?>
                        <p class="text-muted">No CV was uploaded with this application.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card">
                <div class="card__header"><h2 class="card__title">Cover letter</h2></div>
                <div class="card__body">
                    <?php if (trim((string) ($a['cover_letter'] ?? '')) !== ''): ?>
                        <div class="inbox-message"><?= nl2p((string) $a['cover_letter']) ?></div>
                    <?php else: ?>
                        <p class="text-muted">No cover letter was written.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="inbox-aside" aria-label="Application details">
            <?= partial('admin/inbox/_status-form', [
                'action'   => url('admin/applications/' . (int) $a['id']),
                'statuses' => $statuses,
                'current'  => $a['status'],
                'notes'    => (string) ($a['admin_notes'] ?? ''),
                'title'    => 'Hiring progress',
                'help'     => 'Interview notes, scores, references. Only the team can see these notes.',
            ]) ?>

            <?= partial('admin/inbox/_meta', ['row' => $a, 'title' => 'Technical details']) ?>

            <?php if ($canDelete): ?>
                <section class="card inbox-danger">
                    <div class="card__body">
                        <form method="post" action="<?= e(url('admin/applications/' . (int) $a['id'] . '/delete')) ?>" data-confirm="Delete this application and its CV permanently? This cannot be undone.">
                            <?= csrf_field() ?>
                            <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete application</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</div>
