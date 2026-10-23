<?php
/**
 * Admin sign-in form. OWNER: B3. Layout: layouts/admin_login. Generic error messages only (never reveals whether an email exists).
 * The email is repopulated from the flashed old input; the password never is.
 */
?>
<h1 class="auth__title">Sign in</h1>
<p class="auth__lead">Manage the website, messages and content.</p>
<form class="auth__form" method="post" action="<?= e(url('admin/login')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'autocomplete' => 'username', 'attrs' => ['autofocus' => true, 'inputmode' => 'email', 'autocapitalize' => 'none', 'spellcheck' => 'false']]) ?>
    <div class="field">
        <label class="field__label" for="f-password">Password<span class="field__req" aria-hidden="true"> *</span></label>
        <div class="input-group" data-password-field>
            <input class="input" id="f-password" type="password" name="password" required autocomplete="current-password">
            <button class="btn btn--outline btn--sm" type="button" data-password-toggle aria-controls="f-password" aria-pressed="false">Show</button>
        </div>
    </div>
    <button class="btn btn--primary btn--lg btn--block" type="submit" data-loading-text="Signing in...">Sign in</button>
</form>
<p class="auth__footer"><a href="<?= e(url('admin/forgot-password')) ?>">Forgot your password?</a></p>
<p class="auth__footer"><a href="<?= e(url('')) ?>">&larr; Back to the website</a></p>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
