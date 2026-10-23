<?php
/**
 * Admin "choose a new password" page, opened from the e-mailed link. Vars: $token, $valid (the link is still good), $min (minimum length).
 * Layout: layouts/admin_login.
 */
?>
<?php if (empty($valid)): ?>
    <h1 class="auth__title">This link no longer works</h1>
    <p class="alert alert--warning" role="status">The link has expired or was already used. Ask for a new one and use it within <?= (int) \App\Services\AdminPasswordReset::RESET_MINUTES ?> minutes.</p>
    <p><a class="btn btn--primary btn--block" href="<?= e(url('admin/forgot-password')) ?>">Send a new link</a></p>
    <p class="auth__footer"><a href="<?= e(url('admin/login')) ?>">&larr; Back to sign in</a></p>
<?php else: ?>
    <h1 class="auth__title">Choose a new password</h1>
    <form class="auth__form" method="post" action="<?= e(url('admin/reset-password/' . $token)) ?>" novalidate data-password-form>
        <?= csrf_field() ?>
        <div class="field<?= has_error('password') ? ' field--error' : '' ?>">
            <label class="field__label" for="f-password">New password<span class="field__req" aria-hidden="true"> *</span></label>
            <div class="input-group" data-password-field>
                <input class="input" id="f-password" type="password" name="password" required autocomplete="new-password" minlength="<?= (int) $min ?>" aria-describedby="f-password-help<?= has_error('password') ? ' f-password-error' : '' ?>" data-password-strength="#pw-meter" autofocus>
                <button class="btn btn--outline btn--sm" type="button" data-password-toggle aria-controls="f-password" aria-pressed="false">Show</button>
            </div>
            <div class="pw-meter" id="pw-meter" aria-live="polite" hidden><span class="pw-meter__bar"><span></span></span><span class="pw-meter__text"></span></div>
            <div class="field__help" id="f-password-help">At least <?= (int) $min ?> characters, with letters and numbers. Several random words make a strong, memorable password.</div>
            <?php if (has_error('password')): ?><div class="field__error" id="f-password-error" role="alert"><?= e(error('password')) ?></div><?php endif; ?>
        </div>
        <?= partial('partials/form-field', ['name' => 'password_confirmation', 'label' => 'Repeat the new password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
        <button class="btn btn--primary btn--lg btn--block" type="submit">Save new password</button>
    </form>
    <p class="auth__footer"><a href="<?= e(url('admin/login')) ?>">&larr; Back to sign in</a></p>
<?php endif; ?>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
