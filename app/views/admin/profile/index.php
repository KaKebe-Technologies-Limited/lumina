<?php
/** My profile. OWNER: B3. Vars: $row (users row), $activity (own recent audit rows). */
$v = static fn(string $k): string => (string) old($k, (string) ($row[$k] ?? ''));
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => 'My profile', 'subtitle' => 'Your name and sign-in details.']) ?>
<div class="card-grid card-grid--main-aside">
    <form class="card" method="post" action="<?= e(url('admin/profile')) ?>" data-unsaved-guard novalidate>
        <?= csrf_field() ?>
        <div class="card__header"><h2 class="card__title">Details</h2></div>
        <div class="card__body">
            <div class="form-grid">
                <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'value' => $v('name'), 'required' => true, 'maxlength' => 120, 'autocomplete' => 'name']) ?>
                <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'value' => $v('email'), 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email', 'help' => 'Changing it asks for your current password below.']) ?>
                <?= partial('partials/form-field', ['name' => 'current_password', 'label' => 'Current password (only to change your email)', 'type' => 'password', 'autocomplete' => 'current-password', 'class' => 'field--full']) ?>
            </div>
        </div>
        <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save profile</button></div>
    </form>
    <aside class="dash-aside">
        <section class="card">
            <div class="card__header"><h2 class="card__title">Account</h2></div>
            <div class="card__body">
                <dl class="definition-list">
                    <dt>Role</dt><dd><?= e($row['role'] === 'admin' ? 'Administrator' : 'Editor') ?></dd>
                    <dt>Last sign-in</dt><dd><?= $row['last_login_at'] ? e(format_datetime($row['last_login_at'], 'j M Y, H:i')) : 'First sign-in' ?></dd>
                    <dt>Member since</dt><dd><?= e(format_date($row['created_at'], 'j M Y')) ?></dd>
                </dl>
                <a class="btn btn--outline btn--block" href="<?= e(url('admin/profile/password')) ?>"><?= icon('lock', 'icon--sm') ?> Change my password</a>
            </div>
        </section>
        <section class="card">
            <div class="card__header"><h2 class="card__title">Your recent activity</h2></div>
            <?php if ($activity === []): ?>
                <p class="dash-empty">Nothing yet.</p>
            <?php else: ?>
                <ul class="dash-activity">
                    <?php foreach ($activity as $a): ?>
                        <li><span class="dash-activity__text"><?= e($a['summary'] !== '' ? $a['summary'] : $a['action']) ?></span><span class="dash-activity__meta"><time datetime="<?= e($a['created_at']) ?>"><?= e(format_datetime($a['created_at'], 'j M Y, H:i')) ?></time></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </aside>
</div>
</div>
