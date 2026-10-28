<?php
/** Learner portal > Password and security. Vars: $me, $tab, $unread, $portrait. */
?>
<?= partial('partials/page-hero', ['title' => 'Password and security', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Change your password, sign out, or delete your account.']) ?>
<section class="section ac-dash">
    <div class="container container--narrow">
        <?= partial('web/academy/portal/_nav', compact('me', 'tab', 'unread', 'portrait')) ?>

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

        <section class="ac-form card">
            <div class="card__header"><h2 class="card__title">Sign out</h2></div>
            <div class="card__body">
                <p>Signed in as <strong><?= e((string) $me['email']) ?></strong>.</p>
                <form method="post" action="<?= e(url('academy/logout')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--caps" type="submit"><?= icon('log-out', 'icon--sm') ?> Sign out</button></form>
            </div>
        </section>

        <form class="ac-form card ac-danger" method="post" action="<?= e(url('academy/account/delete')) ?>" data-confirm="Delete your LAF Academy account and all your progress? This cannot be undone." novalidate>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title">Delete my account</h2></div>
            <div class="card__body">
                <p>This removes your account, your progress, your messages and your quiz results. Certificates you earned stay valid and can still be checked by their number.</p>
                <?= partial('partials/form-field', ['name' => 'confirm', 'label' => 'Type DELETE to confirm', 'maxlength' => 10, 'autocomplete' => 'off']) ?>
                <?= partial('partials/form-field', ['name' => 'password', 'label' => 'Your password', 'type' => 'password', 'maxlength' => 72, 'autocomplete' => 'current-password']) ?>
                <button class="btn btn--outline btn--caps btn-danger-hover" type="submit">Delete my account</button>
            </div>
        </form>
    </div>
</section>
<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
