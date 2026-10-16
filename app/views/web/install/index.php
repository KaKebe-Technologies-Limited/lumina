<?php
/**
 * One-time "create the first administrator" form (InstallController). Layout: layouts/admin_login.
 * Setup screen for the site owner, shown only while INSTALL_TOKEN is set and no user exists: not visitor copy.
 */
?>
<h1 class="auth__title">Create the administrator</h1>
<p class="auth__lead">This page works only once, with the INSTALL_TOKEN from your .env file, and disappears as soon as an account exists.</p>
<form class="auth__form" method="post" action="<?= e(url('install')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= partial('partials/form-field', ['name' => 'install_token', 'label' => 'Install token', 'type' => 'password', 'required' => true, 'autocomplete' => 'off', 'value' => '']) ?>
    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Your name', 'required' => true, 'autocomplete' => 'name']) ?>
    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'autocomplete' => 'username']) ?>
    <?= partial('partials/form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'value' => '', 'help' => 'At least 10 characters with a letter and a number.']) ?>
    <?= partial('partials/form-field', ['name' => 'password_confirmation', 'label' => 'Repeat the password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'value' => '']) ?>
    <button class="btn btn--primary btn--lg btn--block" type="submit" data-loading-text="Creating...">Create the administrator</button>
</form>
