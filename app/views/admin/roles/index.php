<?php
/** Roles and permissions. Vars: $rows (roles with users + count), $missingTable. */
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', [
    'title' => 'Roles and permissions',
    'subtitle' => 'A role decides what a person can see and do in this admin. Create the roles you need, then give them to people under Users.',
    'actions' => [['label' => 'New role', 'url' => url('admin/roles/create'), 'icon' => 'plus']],
]) ?>

<?php if (!empty($missingTable)): ?>
    <div class="alert alert--warning" role="alert"><p>The roles table is not in the database yet. Import <code>database/lumina-live-import.sql</code> (phpMyAdmin &gt; Import) to enable custom roles. Until then everyone is either an Administrator or an Editor.</p></div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table table--stack">
        <thead><tr><th scope="col">Role</th><th scope="col">What it is for</th><th scope="col">People</th><th scope="col">Permissions</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $isAdmin = $r['slug'] === 'admin'; ?>
            <tr>
                <td data-label="Role" class="cell-title">
                    <?php if ($isAdmin): ?><?= e($r['name']) ?><?php else: ?><a href="<?= e(url('admin/roles/' . (int) $r['id'] . '/edit')) ?>"><?= e($r['name']) ?></a><?php endif; ?>
                    <?php if ((int) $r['is_system'] === 1): ?><span class="badge badge--info">Built in</span><?php endif; ?>
                </td>
                <td data-label="What it is for"><?= e((string) $r['description']) ?></td>
                <td data-label="People"><a href="<?= e(url('admin/users')) ?>"><?= (int) $r['users'] ?></a></td>
                <td data-label="Permissions"><?= $isAdmin ? 'Everything' : (int) $r['count'] . ' of ' . count(\App\Core\Roles::assignable()) ?></td>
                <td class="col-actions">
                    <span class="table__actions">
                        <?php if (!$isAdmin): ?><a class="btn btn--outline btn--sm" href="<?= e(url('admin/roles/' . (int) $r['id'] . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a><?php endif; ?>
                        <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/roles/create?copy=' . rawurlencode((string) $r['slug']))) ?>">Copy</a>
                        <?php if ((int) $r['is_system'] !== 1 && (int) $r['users'] === 0): ?>
                            <form method="post" action="<?= e(url('admin/roles/' . (int) $r['id'] . '/delete')) ?>" class="inline-form" data-confirm="Delete the role <?= e($r['name']) ?>?">
                                <?= csrf_field() ?><button class="btn btn--ghost btn--sm pe-danger" type="submit" aria-label="Delete <?= e($r['name']) ?>"><?= icon('trash', 'icon--sm') ?></button>
                            </form>
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="form-hint">The Administrator role always has every right and cannot be changed. Creating users and roles is always for administrators only.</p>
</div>
