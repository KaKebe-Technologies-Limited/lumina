<?php
/**
 * Change-password form. OWNER: B3. Vars: $forced (bool: first sign-in / reset by an admin -> login layout), $min (minimum length).
 * When not forced the page is rendered inside the normal admin layout.
 */
$pwForm = static function (bool $forced, int $min): string {
    ob_start(); ?>
    <form class="<?= $forced ? 'auth__form' : 'stack' ?>" method="post" action="<?= e(url('admin/profile/password')) ?>" novalidate data-password-form>
        <?= csrf_field() ?>
        <?= partial('partials/form-field', ['name' => 'current_password', 'label' => $forced ? 'Temporary or current password' : 'Current password', 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
        <div class="field<?= has_error('password') ? ' field--error' : '' ?>">
            <label class="field__label" for="f-password">New password<span class="field__req" aria-hidden="true"> *</span></label>
            <div class="input-group" data-password-field>
                <input class="input" id="f-password" type="password" name="password" required autocomplete="new-password" minlength="<?= (int) $min ?>" aria-describedby="f-password-help<?= has_error('password') ? ' f-password-error' : '' ?>" data-password-strength="#pw-meter">
                <button class="btn btn--outline btn--sm" type="button" data-password-toggle aria-controls="f-password" aria-pressed="false">Show</button>
            </div>
            <div class="pw-meter" id="pw-meter" aria-live="polite" hidden><span class="pw-meter__bar"><span></span></span><span class="pw-meter__text"></span></div>
            <div class="field__help" id="f-password-help">At least <?= (int) $min ?> characters, with letters and numbers. Several random words make a strong, memorable password.</div>
            <?php if (has_error('password')): ?><div class="field__error" id="f-password-error" role="alert"><?= e(error('password')) ?></div><?php endif; ?>
        </div>
        <?= partial('partials/form-field', ['name' => 'password_confirmation', 'label' => 'Repeat the new password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
        <button class="btn btn--primary<?= $forced ? ' btn--lg btn--block' : '' ?>" type="submit">Save new password</button>
    </form>
    <?php return (string) ob_get_clean();
};
?>
<?php if (!empty($forced)): ?>
    <h1 class="auth__title">Choose a new password</h1>
    <p class="alert alert--warning" role="status">Before you continue, please replace your temporary password with one only you know.</p>
    <?= $pwForm(true, (int) $min) ?>
    <form class="auth__footer" method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?><button class="btn btn--link" type="submit">Sign out instead</button></form>
<?php else: ?>
    <?= partial('partials/admin/page-header', ['title' => 'Change password', 'subtitle' => 'Use a password you do not use anywhere else.']) ?>
    <div class="card pe-narrow">
        <div class="card__body"><?= $pwForm(false, (int) $min) ?></div>
    </div>
<?php endif; ?>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
