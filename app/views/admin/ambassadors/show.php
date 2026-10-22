<?php
/** Admin > one ambassador application. Vars: $r, $regions, $canDelete, $reviewer. */
use App\Models\AmbassadorApplication as Amb;
use App\Services\Inbox\Support;

$mailto = Support::mailto($r['email']);
$actions = [];
if ($mailto !== '') {
    $actions[] = ['label' => 'E-mail ' . $r['name'], 'url' => $mailto, 'class' => 'btn--outline', 'icon' => 'mail'];
}
$actions[] = ['label' => 'All applications', 'url' => url('admin/ambassadors'), 'class' => 'btn--ghost', 'icon' => 'arrow-left'];
$roles = array_map(static fn(string $k): string => Amb::ROLES[$k] ?? $k, Amb::jsonList($r['roles']));
$pledge = Amb::pledgeLines($r);
$id = (int) $r['id'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox">
    <?= partial('partials/admin/page-header', ['title' => $r['name'], 'subtitle' => $r['country'] . ' (' . ($regions[$r['region']] ?? $r['region']) . ') - received ' . format_datetime($r['created_at']), 'actions' => $actions]) ?>
    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card">
                <div class="card__header"><h2 class="card__title">Yearly pledge</h2><?= partial('partials/admin/status-badge', ['status' => $r['status'], 'label' => Amb::LABELS[$r['status']] ?? null]) ?></div>
                <div class="card__body">
                    <?php if ($pledge): ?><ul class="ac-list"><?php foreach ($pledge as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul>
                    <?php else: ?><p class="text-muted">No pledge details.</p><?php endif; ?>
                    <?php if ($r['annual_amount'] !== null): ?><p class="text-muted">This is what they could raise or give in a year, as they entered it. It is an intention, not a payment.</p><?php endif; ?>
                </div>
            </section>
            <section class="card">
                <div class="card__header"><h2 class="card__title">About the applicant</h2></div>
                <div class="card__body">
                    <dl class="definition-list">
                        <dt>Name</dt><dd><?= e($r['name']) ?></dd>
                        <dt>Email</dt><dd><?php if ($mailto !== ''): ?><a href="<?= e($mailto) ?>"><?= e($r['email']) ?></a><?php else: ?><?= e($r['email']) ?><?php endif; ?></dd>
                        <?php if (!empty($r['phone'])): ?><dt>Phone</dt><dd><a href="<?= e(phone_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></dd><?php endif; ?>
                        <dt>Location</dt><dd><?= e(($r['city'] ? $r['city'] . ', ' : '') . $r['country']) ?> <span class="text-muted">(<?= e($regions[$r['region']] ?? $r['region']) ?>)</span></dd>
                        <?php if (!empty($r['occupation'])): ?><dt>Occupation</dt><dd><?= e($r['occupation']) ?></dd><?php endif; ?>
                        <?php if (!empty($r['organisation'])): ?><dt>Organisation</dt><dd><?= e($r['organisation']) ?></dd><?php endif; ?>
                        <?php if (!empty($r['profile_url']) && preg_match('~^https?://~i', (string) $r['profile_url']) === 1): ?><dt>Profile</dt><dd><a href="<?= e($r['profile_url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($r['profile_url']) ?></a></dd><?php endif; ?>
                        <?php if (!empty($r['availability'])): ?><dt>Time</dt><dd><?= e(Amb::AVAILABILITY[$r['availability']] ?? $r['availability']) ?></dd><?php endif; ?>
                        <dt>May be shown</dt><dd><?= $r['public_ok'] ? 'Yes, name and country on the Ambassadors page once approved' : 'No' ?></dd>
                    </dl>
                    <?php if ($roles): ?><h3 class="inbox-subheading">Could help by</h3><ul class="ac-list"><?php foreach ($roles as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if (trim((string) $r['network']) !== ''): ?><h3 class="inbox-subheading">Who they could reach</h3><div class="inbox-message"><?= nl2p((string) $r['network']) ?></div><?php endif; ?>
                    <?php if (trim((string) $r['motivation']) !== ''): ?><h3 class="inbox-subheading">Why they would like to be an ambassador</h3><div class="inbox-message"><?= nl2p((string) $r['motivation']) ?></div><?php endif; ?>
                </div>
            </section>
        </div>
        <aside class="inbox-aside" aria-label="Follow-up">
            <section class="card">
                <div class="card__header"><h2 class="card__title">Decision and notes</h2></div>
                <form method="post" action="<?= e(url('admin/ambassadors/' . $id)) ?>" data-unsaved-guard>
                    <?= csrf_field() ?>
                    <div class="card__body">
                        <?php if ($r['reviewed_at']): ?><p class="text-muted">Last changed <?= e(format_datetime($r['reviewed_at'], 'j M Y, H:i')) ?><?= $reviewer !== '' ? ' by ' . e($reviewer) : '' ?>.</p><?php endif; ?>
                        <?= partial('partials/form-field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => Amb::LABELS, 'value' => old('status', $r['status']), 'required' => true]) ?>
                        <?= partial('partials/form-field', ['name' => 'admin_notes', 'label' => 'Private notes', 'type' => 'textarea', 'rows' => 5, 'value' => old('admin_notes', (string) ($r['admin_notes'] ?? '')), 'maxlength' => 5000, 'help' => 'Who spoke to them and what was agreed. Only the team sees this.']) ?>
                        <?= partial('partials/form-field', ['name' => 'email_applicant', 'label' => 'E-mail the applicant when I approve, decline or put it on hold', 'type' => 'checkbox']) ?>
                        <?= partial('partials/form-field', ['name' => 'applicant_note', 'label' => 'Message to the applicant (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 1000, 'help' => 'Added to the e-mail.']) ?>
                    </div>
                    <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save</button></div>
                </form>
            </section>
            <?= partial('admin/inbox/_meta', ['row' => $r, 'title' => 'Technical details']) ?>
            <?php if ($canDelete): ?>
                <section class="card inbox-danger"><div class="card__body">
                    <form method="post" action="<?= e(url('admin/ambassadors/' . $id . '/delete')) ?>" data-confirm="Delete this application permanently? This cannot be undone.">
                        <?= csrf_field() ?><button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete application</button>
                    </form>
                </div></section>
            <?php endif; ?>
        </aside>
    </div>
</div>
