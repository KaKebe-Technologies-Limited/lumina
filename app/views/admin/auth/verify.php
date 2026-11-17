<?php
/**
 * Admin sign-in, step two: the 6-digit code that was e-mailed. Vars: $masked (a***@example.org), $minutes (code lifetime), $trustDays (0 = no "remember this browser"), $wait (seconds until a new code may be asked).
 * Layout: layouts/admin_login. Messages stay short and never say more than needed.
 */
?>
<h1 class="auth__title">Check your e-mail</h1>
<p class="auth__lead">We sent a 6-digit code to <strong><?= e($masked) ?></strong>. It works for <?= (int) $minutes ?> minutes.</p>
<form class="auth__form" method="post" action="<?= e(url('admin/verify')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label class="field__label" for="f-code">Sign-in code</label>
        <input class="input input--code" id="f-code" name="code" inputmode="numeric" pattern="[0-9 ]*" maxlength="7" autocomplete="one-time-code" autofocus required placeholder="123456" aria-describedby="code-help">
        <p class="field__help" id="code-help">Open the newest e-mail from the website and type the code here.</p>
    </div>
    <?php if ((int) $trustDays > 0): ?>
        <label class="check"><input type="checkbox" name="trust" value="1"><span>Remember this browser for <?= (int) $trustDays ?> days (do not use on a shared computer)</span></label>
    <?php endif; ?>
    <button class="btn btn--primary btn--lg btn--block" type="submit" data-loading-text="Checking...">Sign in</button>
</form>
<form class="auth__footer" method="post" action="<?= e(url('admin/verify/resend')) ?>"><?= csrf_field() ?>
    <button class="btn btn--ghost btn--sm" type="submit"><?= (int) $wait > 0 ? 'Send a new code (wait ' . (int) $wait . ' s)' : 'Send a new code' ?></button>
</form>
<form class="auth__footer" method="post" action="<?= e(url('admin/verify/cancel')) ?>"><?= csrf_field() ?>
    <button class="btn btn--ghost btn--sm" type="submit">&larr; Start again</button>
</form>
