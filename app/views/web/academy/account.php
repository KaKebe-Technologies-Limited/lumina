<?php /** My account. Vars: $me. */ ?>
<?= partial('partials/page-hero', ['title' => 'My account', 'eyebrow' => 'LAF Academy', 'lead' => 'Your details, your password, and the option to delete your account.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <form class="ac-form card" method="post" action="<?= e(url('academy/account/profile')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title">My details</h2></div>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120, 'value' => old('name', $me['name']), 'help' => 'Used on new certificates. Certificates you already earned keep their name.']) ?>
                <div class="field"><label class="field__label" for="acc-email">E-mail address</label><input class="input" id="acc-email" type="email" value="<?= e($me['email']) ?>" disabled><div class="field__help">To change your e-mail address, write to us and we will help.</div></div>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'maxlength' => 40, 'value' => old('phone', (string) $me['phone'])]) ?>
                    <?= partial('partials/form-field', ['name' => 'country', 'label' => 'Country', 'maxlength' => 80, 'value' => old('country', (string) $me['country'])]) ?>
                </div>
                <button class="btn btn--cta btn--caps" type="submit">Save my details</button>
            </div>
        </form>

        <form class="ac-form card" method="post" action="<?= e(url('academy/account/password')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title">Change my password</h2></div>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'current_password', 'label' => 'Current password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'current-password']) ?>
                <?= partial('partials/form-field', ['name' => 'new_password', 'label' => 'New password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'new-password', 'help' => 'At least 8 characters.']) ?>
                <?= partial('partials/form-field', ['name' => 'new_password_confirm', 'label' => 'Repeat the new password', 'type' => 'password', 'required' => true, 'maxlength' => 72, 'autocomplete' => 'new-password']) ?>
                <button class="btn btn--outline btn--caps" type="submit">Change password</button>
            </div>
        </form>

        <form class="ac-form card ac-danger" method="post" action="<?= e(url('academy/account/delete')) ?>" data-confirm="Delete your LAF Academy account and all your progress? This cannot be undone." novalidate>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title">Delete my account</h2></div>
            <div class="card__body">
                <p>This removes your account, your progress and your quiz results. Certificates you earned stay valid and can still be checked by their number.</p>
                <?= partial('partials/form-field', ['name' => 'confirm', 'label' => 'Type DELETE to confirm', 'maxlength' => 10, 'autocomplete' => 'off']) ?>
                <?= partial('partials/form-field', ['name' => 'password', 'label' => 'Your password', 'type' => 'password', 'maxlength' => 72, 'autocomplete' => 'current-password']) ?>
                <button class="btn btn--outline btn--caps btn-danger-hover" type="submit">Delete my account</button>
            </div>
        </form>
    </div>
</section>

<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
