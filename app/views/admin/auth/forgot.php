<?php
/**
 * Admin "forgot password": asks for the account e-mail and sends a reset link. Layout: layouts/admin_login.
 * The answer is the same whether or not the address belongs to an account (see AuthController::sendReset).
 */
?>
<h1 class="auth__title">Forgot your password?</h1>
<p class="auth__lead">Enter the e-mail address of your admin account and we will send you a link to choose a new password.</p>
<form class="auth__form" method="post" action="<?= e(url('admin/forgot-password')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'autocomplete' => 'username', 'attrs' => ['autofocus' => true, 'inputmode' => 'email', 'autocapitalize' => 'none', 'spellcheck' => 'false']]) ?>
    <button class="btn btn--primary btn--lg btn--block" type="submit" data-loading-text="Sending...">Send the reset link</button>
</form>
<p class="auth__footer"><a href="<?= e(url('admin/login')) ?>">&larr; Back to sign in</a></p>
