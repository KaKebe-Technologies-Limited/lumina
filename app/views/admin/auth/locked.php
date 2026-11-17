<?php
/** Admin screen lock. Vars: $name, $email. Shown after the idle time: the person is still signed in and only types the password again. Layout: layouts/admin_login. */
?>
<h1 class="auth__title"><?= icon('lock', 'icon--sm') ?> Screen locked</h1>
<p class="auth__lead">You were away for a while, so we locked the screen to protect the website. Type your password to carry on<?= $name !== '' ? ', ' . e($name) : '' ?>.</p>
<form class="auth__form" method="post" action="<?= e(url('admin/locked')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label class="field__label" for="f-password">Password</label>
        <div class="input-group" data-password-field>
            <input class="input" id="f-password" type="password" name="password" required autofocus autocomplete="current-password">
            <button class="btn btn--outline btn--sm" type="button" data-password-toggle aria-controls="f-password" aria-pressed="false">Show</button>
        </div>
    </div>
    <button class="btn btn--primary btn--lg btn--block" type="submit" data-loading-text="Unlocking...">Unlock</button>
</form>
<form class="auth__footer" method="post" action="<?= e(url('admin/logout')) ?>"><?= csrf_field() ?>
    <button class="btn btn--ghost btn--sm" type="submit">Sign out instead</button>
</form>
<script src="<?= e(asset('js/pages/admin-pages.js')) ?>" defer></script>
