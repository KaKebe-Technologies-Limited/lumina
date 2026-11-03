<?php /** After sign-up: the registration number warning screen. Vars: $regNo, $label (text of the continue button). */ ?>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'Your account is created', 'eyebrow' => 'LAF Academy']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <div class="card"><div class="card__body">
            <?= partial('partials/academy-regno', ['regNo' => $regNo]) ?>
            <ul class="ac-regwarn__list">
                <li>Sign in with your e-mail address <em>or</em> this registration number, and your password.</li>
                <li>Check whether your account is approved at any time on the <a href="<?= e(url('academy/track')) ?>">Track my application</a> page: paste this number there.</li>
                <li>If you lose it, message the Academy team and we will help you.</li>
            </ul>
            <div class="ac-regwarn__go">
                <label class="ac-regwarn__check"><input type="checkbox" data-regno-saved> I have copied or written down my registration number</label>
                <a class="btn btn--cta btn--caps" href="<?= e(url('academy/registered/continue')) ?>" data-regno-continue><?= e($label) ?></a>
            </div>
        </div></div>
    </div>
</section>
