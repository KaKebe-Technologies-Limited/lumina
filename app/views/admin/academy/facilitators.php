<?php /** Admin > Academy > Facilitators. Vars: $rows (users who may teach), $creds (one-time password or null), $roles (slug => name), $meId. */ ?>
<?= partial('partials/admin/page-header', ['title' => 'Facilitators', 'subtitle' => 'Teachers who sign in at the admin address and see only the Academy: their own courses, virtual classes and learners.']) ?>

<?php if ($creds): ?>
    <section class="cred-card" aria-labelledby="cred-title" role="region">
        <h2 id="cred-title"><?= icon('lock', 'icon--sm') ?> <?= $creds['reason'] === 'reset' ? 'New temporary password' : 'Account created' ?> for <?= e($creds['name']) ?></h2>
        <p>Copy this password now. <strong>It will not be shown again.</strong> Give it to <?= e($creds['name']) ?> personally or through a private message. They must choose their own password at the first sign-in.</p>
        <dl class="cred-card__list">
            <div><dt>Sign-in page</dt><dd><a href="<?= e(abs_url('admin/login')) ?>"><?= e(abs_url('admin/login')) ?></a></dd></div>
            <div><dt>Email</dt><dd><?= e($creds['email']) ?></dd></div>
            <div><dt>Temporary password</dt><dd><code class="cred-card__pw" id="cred-pw"><?= e($creds['password']) ?></code></dd></div>
        </dl>
        <button class="btn btn--primary btn--sm" type="button" data-copy="#cred-pw" data-copied-label="Copied">Copy the password</button>
    </section>
<?php endif; ?>

<section class="card" aria-labelledby="fa-new">
    <div class="card__header"><h2 class="card__title" id="fa-new">Create a facilitator account</h2></div>
    <form class="card__body" method="post" action="<?= e(url('admin/academy/facilitators')) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'name', 'id' => 'fa-name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120]) ?>
            <?= partial('partials/form-field', ['name' => 'email', 'id' => 'fa-email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190]) ?>
            <?= partial('partials/form-field', ['name' => 'role', 'id' => 'fa-role', 'label' => 'Role', 'type' => 'select', 'options' => $roles ?: ['facilitator' => 'Facilitator'], 'value' => 'facilitator', 'required' => true,
                'help' => 'The Facilitator role can teach only. Create more roles under Roles if you need different limits.']) ?>
        </div>
        <p class="form-note">After creating the account, give the facilitator a course: open the course, choose them as the facilitator, and save.</p>
        <button class="btn btn--primary" type="submit"><?= icon('user-plus', 'icon--sm') ?> Create the account</button>
    </form>
</section>

<div class="table-wrap">
    <table class="table table--stack">
        <caption class="visually-hidden">People who can teach</caption>
        <thead><tr><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Courses</th><th scope="col">Upcoming classes</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $u): $self = (int) $u['id'] === (int) $meId; $admin = $u['role'] === 'admin'; ?>
            <tr class="<?= (int) $u['is_active'] === 1 ? '' : 'is-muted' ?>">
                <td class="cell-main" data-label="Name"><span class="cell-title"><?= e($u['name']) ?><?= $self ? ' <span class="badge badge--info">You</span>' : '' ?></span><span class="cell-sub"><?= e($u['email']) ?></span></td>
                <td data-label="Role"><?= e($u['role_name']) ?></td>
                <td data-label="Courses"><?= (int) $u['courses'] ?></td>
                <td data-label="Upcoming classes"><?= (int) $u['classes'] ?></td>
                <td data-label="Status"><span class="badge badge--<?= (int) $u['is_active'] === 1 ? 'success' : 'neutral' ?>"><?= (int) $u['is_active'] === 1 ? 'Active' : 'Switched off' ?></span><span class="cell-sub"><?= $u['last_login_at'] ? 'Last sign-in ' . e(format_date($u['last_login_at'], 'j M')) : 'Never signed in' ?></span></td>
                <td class="col-actions"><?php if (!$self && !$admin): ?><div class="table__actions">
                    <form method="post" action="<?= e(url('admin/academy/facilitators/' . (int) $u['id'] . '/reset-password')) ?>" class="inline-form" data-confirm="Create a new temporary password for <?= e($u['name']) ?>?"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">New password</button></form>
                    <form method="post" action="<?= e(url('admin/academy/facilitators/' . (int) $u['id'] . '/toggle')) ?>" class="inline-form" data-confirm="<?= (int) $u['is_active'] === 1 ? 'Switch this account off? This person can no longer sign in.' : 'Switch this account on again?' ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= (int) $u['is_active'] === 1 ? 'Switch off' : 'Switch on' ?></button></form>
                </div><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
