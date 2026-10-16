<?php
/**
 * Admin > one involvement request. Vars: $r, $statuses, $canDelete.
 */
use App\Services\Inbox\Support;

$type = label_from((string) $r['involvement_type']);
$mailto = Support::mailto($r['email']);
$actions = [];
if ($mailto !== '') {
    $actions[] = ['label' => 'Email ' . $r['name'], 'url' => $mailto, 'class' => 'btn--outline', 'icon' => 'mail'];
}
$actions[] = ['label' => 'All requests', 'url' => url('admin/involvement'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', [
        'title'    => $r['name'],
        'subtitle' => $type . ' - received ' . format_datetime($r['created_at']),
        'actions'  => $actions,
    ]) ?>

    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card">
                <div class="card__header">
                    <h2 class="card__title">Request</h2>
                    <?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => $statuses[$r['status']] ?? null]) ?>
                </div>
                <div class="card__body">
                    <dl class="definition-list">
                        <dt>Name</dt><dd><?= e($r['name']) ?></dd>
                        <dt>Wants to</dt><dd><span class="badge badge--outline"><?= e($type) ?></span></dd>
                        <?php if (!empty($r['organisation'])): ?><dt>Organisation</dt><dd><?= e($r['organisation']) ?></dd><?php endif; ?>
                        <dt>Email</dt><dd><?php if (Support::mailto($r['email']) !== ''): ?><a href="<?= e(Support::mailto($r['email'])) ?>"><?= e($r['email']) ?></a><?php else: ?><?= e($r['email']) ?><?php endif; ?></dd>
                        <?php if (!empty($r['phone'])): ?><dt>Phone</dt><dd><a href="<?= e(phone_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></dd><?php endif; ?>
                    </dl>
                    <?php if (trim((string) $r['message']) !== ''): ?>
                        <h3 class="inbox-subheading">Their message</h3>
                        <div class="inbox-message"><?= nl2p((string) $r['message']) ?></div>
                    <?php else: ?>
                        <p class="text-muted">They did not leave a message.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="inbox-aside" aria-label="Request details">
            <?= partial('admin/inbox/_status-form', [
                'action'   => url('admin/involvement/' . (int) $r['id']),
                'statuses' => $statuses,
                'current'  => $r['status'],
                'notes'    => (string) ($r['admin_notes'] ?? ''),
                'title'    => 'Follow-up',
                'help'     => 'Record who spoke to them and what was agreed. Only the team can see these notes.',
            ]) ?>

            <?= partial('admin/inbox/_meta', ['row' => $r, 'title' => 'Technical details']) ?>

            <?php if ($canDelete): ?>
                <section class="card inbox-danger">
                    <div class="card__body">
                        <form method="post" action="<?= e(url('admin/involvement/' . (int) $r['id'] . '/delete')) ?>" data-confirm="Delete this request permanently? This cannot be undone.">
                            <?= csrf_field() ?>
                            <button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete request</button>
                        </form>
                    </div>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</div>
