<?php
/**
 * Admin > Membership types & fees = THE FEE CONFIGURATION. OWNER: ADMIN agent. Contract: docs/MEMBERSHIP.md. Admin only (members.types).
 * Vars: $rows (membership_types), $counts (type id => members), $capacity (type id => [taken,left,full]), $placeholders (starter types still without a fee),
 * $noFee (active types without a fee), $activeCount, $isOpen, $canSettings.
 */
use App\Services\Membership\MembershipService as MS;

$termNote = static function (string $period): string {
    return match ($period) {
        'monthly'   => 'Term ends after 1 month',
        'quarterly' => 'Term ends after 3 months',
        'annual'    => 'Term ends after 1 year',
        'lifetime'  => 'Paid once, never expires',
        default     => 'Paid once, no expiry date',
    };
};
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-members.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-members admin-membership-types">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Membership types & fees',
        'subtitle' => 'Decide what each kind of membership costs. This is where the fee is set: the website, the registration form and the e-mails all read it from here. A change only applies to new applications.',
        'actions'  => [
            ['label' => 'New membership type', 'url' => url('admin/membership-types/create'), 'class' => 'btn--primary', 'icon' => 'plus'],
            ['label' => 'View public page', 'url' => url('membership'), 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank'],
        ],
    ]) ?>

    <?php if ($placeholders !== []): ?>
        <div class="alert alert--info" role="status">
            <?= icon('info') ?>
            <div class="alert__body"><strong class="alert__title">The starter types are placeholders.</strong>
                "<?= e(implode('", "', array_column($placeholders, 'name'))) ?>" were added so the page works from day one. Rename them, set their fees, or delete the ones you do not need.
                Until a fee is set, the website says "<?= e(MS::copy('types.fee_tba', 'Fee to be announced')) ?>".</div>
        </div>
    <?php elseif ($noFee !== []): ?>
        <div class="alert alert--warning" role="status">
            <?= icon('alert-triangle') ?>
            <div class="alert__body"><strong class="alert__title"><?= count($noFee) === 1 ? 'One active type has no fee yet.' : count($noFee) . ' active types have no fee yet.' ?></strong>
                The website says "<?= e(MS::copy('types.fee_tba', 'Fee to be announced')) ?>" for <?= e(implode(', ', array_column($noFee, 'name'))) ?> until you set it.</div>
        </div>
    <?php endif; ?>
    <?php if ($activeCount === 0): ?>
        <div class="alert alert--warning" role="status">
            <?= icon('alert-triangle') ?>
            <div class="alert__body"><strong class="alert__title">No membership type is switched on.</strong> The registration form has nothing to offer. Switch a type on, or add a new one.</div>
        </div>
    <?php endif; ?>
    <?php if (!$isOpen): ?>
        <div class="alert alert--info" role="status">
            <?= icon('info') ?>
            <div class="alert__body">Registration is currently <strong>closed</strong>, so these types are not offered to visitors yet.
                <?php if ($canSettings): ?><a href="<?= e(url('admin/settings#tab-membership')) ?>">Open it in the settings</a>.<?php endif; ?></div>
        </div>
    <?php endif; ?>

    <?php if ($rows === []): ?>
        <?= partial('partials/empty-state', [
            'icon' => 'banknote', 'title' => 'No membership types yet',
            'text' => 'Add the first type to open registration. You choose its name, fee and billing period.',
            'actions' => [['label' => 'New membership type', 'url' => url('admin/membership-types/create'), 'class' => 'btn--primary']],
        ]) ?>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table--stack members-table members-types">
                <thead>
                <tr>
                    <th scope="col">Membership type</th>
                    <th scope="col">Fee on the website</th>
                    <th scope="col">Billing</th>
                    <th scope="col">Members</th>
                    <th scope="col">Offered</th>
                    <th scope="col" class="col-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $t):
                    $id = (int) $t['id'];
                    $n = (int) ($counts[$id] ?? 0);
                    $cap = $capacity[$id] ?? ['taken' => 0, 'left' => null, 'full' => false];
                    $on = (int) $t['is_active'] === 1;
                    $joining = MS::joiningFeeLabel($t, true);
                    ?>
                    <tr class="<?= $on ? '' : 'members-row--off' ?>">
                        <td data-label="Type">
                            <span class="cell-title"><a href="<?= e(url('admin/membership-types/' . $id . '/edit')) ?>"><?= e($t['name']) ?></a></span>
                            <?php if (!empty($t['description'])): ?><span class="cell-sub"><?= e(mb_strimwidth((string) $t['description'], 0, 90, '...')) ?></span><?php endif; ?>
                        </td>
                        <td data-label="Fee">
                            <strong class="members-fee<?= $t['fee_amount'] === null ? ' members-fee--unset' : '' ?>"><?= e(MS::typeFeeLabel($t)) ?></strong>
                            <?php if ($joining !== ''): ?><span class="cell-sub"><?= e($joining) ?></span><?php endif; ?>
                            <?php if ($t['fee_amount'] === null): ?><span class="cell-sub">Not set yet</span><?php endif; ?>
                        </td>
                        <td data-label="Billing"><?= e(MS::PERIOD_LABELS_ADMIN[$t['period']] ?? $t['period']) ?><span class="cell-sub"><?= e($termNote((string) $t['period'])) ?></span></td>
                        <td data-label="Members">
                            <?php if ($n > 0): ?><a href="<?= e(url('admin/members', ['type' => $id])) ?>"><?= $n ?> member<?= $n === 1 ? '' : 's' ?></a><?php else: ?><span class="text-muted">None yet</span><?php endif; ?>
                            <?php if ($cap['left'] !== null): ?><span class="cell-sub"><?= $cap['full'] ? 'Full' : (int) $cap['left'] . ' place' . ($cap['left'] === 1 ? '' : 's') . ' left of ' . (int) $t['max_members'] ?></span><?php endif; ?>
                        </td>
                        <td data-label="Offered">
                            <form method="post" action="<?= e(url('admin/membership-types/' . $id . '/toggle')) ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <button class="members-toggle<?= $on ? ' is-on' : '' ?>" type="submit" aria-pressed="<?= $on ? 'true' : 'false' ?>"
                                        title="<?= $on ? 'Click to stop offering this type' : 'Click to offer this type again' ?>">
                                    <span class="badge badge--<?= $on ? 'success' : 'neutral' ?> badge--dot"><?= $on ? 'On the website' : 'Switched off' ?></span>
                                    <span class="visually-hidden"> - <?= e($t['name']) ?>. <?= $on ? 'Press to switch off' : 'Press to switch on' ?></span>
                                </button>
                            </form>
                        </td>
                        <td data-label="" class="col-actions">
                            <div class="table__actions">
                                <a class="btn btn--outline btn--sm" href="<?= e(url('admin/membership-types/' . $id . '/edit')) ?>" aria-label="Edit <?= e($t['name']) ?>"><?= icon('edit', 'icon--sm') ?><span class="members-btn-text">Edit fee and details</span></a>
                                <?php if ($n === 0): ?>
                                    <form method="post" action="<?= e(url('admin/membership-types/' . $id . '/delete')) ?>" class="inline-form" data-confirm="<?= e('Delete the membership type "' . $t['name'] . '"? This cannot be undone.') ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn--ghost btn--sm" type="submit" aria-label="Delete <?= e($t['name']) ?>"><?= icon('trash', 'icon--sm') ?><span class="members-btn-text">Delete</span></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="members-note"><?= icon('info', 'icon--sm') ?> A type that already has members cannot be deleted. Switch it off instead: visitors stop seeing it, and existing members are not affected.</p>
    <?php endif; ?>
</div>
