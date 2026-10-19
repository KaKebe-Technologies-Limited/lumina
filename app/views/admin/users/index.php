<?php
/** Users list. OWNER: B3. Vars: $rows, $creds (one-time temporary password or null), $meId, $admins (active administrator count). */
?>
<div class="admin-pages-editor admin-users">
<?= partial('partials/admin/page-header', [
    'title'    => 'Users',
    'subtitle' => 'People who can sign in to this admin area. Administrators can do everything; editors can edit pages and content and handle messages.',
    'actions'  => [['label' => 'Add a user', 'url' => url('admin/users/create'), 'icon' => 'user-plus']],
]) ?>

<?php if ($creds): ?>
    <section class="cred-card" aria-labelledby="cred-title" role="region">
        <h2 id="cred-title"><?= icon('lock', 'icon--sm') ?> <?= $creds['reason'] === 'reset' ? 'New temporary password' : 'Account created' ?> for <?= e($creds['name']) ?></h2>
        <p>Copy this password now. <strong>It will not be shown again.</strong> Give it to <?= e($creds['name']) ?> personally or through a private message, not in a group chat. They must choose their own password the first time they sign in.</p>
        <dl class="cred-card__list">
            <div><dt>Sign-in page</dt><dd><a href="<?= e(abs_url('admin/login')) ?>"><?= e(abs_url('admin/login')) ?></a></dd></div>
            <div><dt>Email</dt><dd><?= e($creds['email']) ?></dd></div>
            <div><dt>Temporary password</dt><dd><code class="cred-card__pw" id="cred-pw"><?= e($creds['password']) ?></code></dd></div>
        </dl>
        <button class="btn btn--primary btn--sm" type="button" data-copy="#cred-pw" data-copied-label="Copied">Copy the password</button>
    </section>
<?php endif; ?>

<div class="table-wrap">
    <table class="table table--stack">
        <thead><tr><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Last sign-in</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $u):
            $me = (int) $u['id'] === (int) $meId;
            $isLastAdmin = $u['role'] === 'admin' && (int) $u['is_active'] === 1 && $admins <= 1;
            ?>
            <tr<?= (int) $u['is_active'] === 1 ? '' : ' class="is-muted"' ?>>
                <td data-label="Name" class="cell-title">
                    <a href="<?= e(url('admin/users/' . (int) $u['id'] . '/edit')) ?>"><?= e($u['name']) ?></a><?= $me ? ' <span class="badge badge--info">You</span>' : '' ?>
                    <span class="cell-sub"><?= e($u['email']) ?></span>
                </td>
                <td data-label="Role"><?= partial('partials/admin/status-badge', ['status' => $u['role'], 'label' => \App\Core\Roles::name((string) $u['role']), 'tone' => $u['role'] === 'admin' ? 'primary' : 'info']) ?></td>
                <td data-label="Status">
                    <?= partial('partials/admin/status-badge', ['status' => (int) $u['is_active'] === 1 ? 'active' : 'inactive', 'label' => (int) $u['is_active'] === 1 ? 'Active' : 'Disabled']) ?>
                    <?php if ((int) $u['must_change_password'] === 1): ?><span class="cell-sub">Must choose a new password</span><?php endif; ?>
                </td>
                <td data-label="Last sign-in"><?= $u['last_login_at'] ? e(format_datetime($u['last_login_at'], 'j M Y, H:i')) : '<span class="text-muted">Never</span>' ?></td>
                <td class="col-actions">
                    <span class="table__actions">
                        <a class="btn btn--outline btn--sm" href="<?= e(url('admin/users/' . (int) $u['id'] . '/edit')) ?>"><?= icon('edit', 'icon--sm') ?> Edit</a>
                        <?php if (!$me): ?>
                            <form method="post" action="<?= e(url('admin/users/' . (int) $u['id'] . '/reset-password')) ?>" class="inline-form" data-confirm="Create a new temporary password for <?= e($u['name']) ?>? Their current password stops working immediately.">
                                <?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Reset password</button>
                            </form>
                            <?php if (!$isLastAdmin): ?>
                                <form method="post" action="<?= e(url('admin/users/' . (int) $u['id'] . '/delete')) ?>" class="inline-form" data-confirm="Delete the account of <?= e($u['name']) ?>? Their past edits stay in the activity log. This cannot be undone.">
                                    <?= csrf_field() ?><button class="btn btn--ghost btn--sm pe-danger" type="submit" aria-label="Delete <?= e($u['name']) ?>"><?= icon('trash', 'icon--sm') ?></button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="field__help">There must always be at least one active administrator, and you cannot delete or demote your own account here.</p>
</div>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script><?php $this->end(); ?>
