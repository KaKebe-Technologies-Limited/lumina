<?php /** My account. Vars: $me, $portrait (photo_path, photo_consent_at), $portraitReady. */ ?>
<?php $this->start('head'); ?><link rel="stylesheet" href="<?= e(asset('css/portal.min.css')) ?>"><?php $this->end(); ?>
<?= partial('partials/page-hero', ['title' => 'My profile', 'eyebrow' => 'LAF Academy | My portal', 'lead' => 'Your details, your bio, your contact numbers and your picture.']) ?>
<?php if (\App\Services\Academy\LearnerPortrait::mustAdd((int) $me['id'])): ?>
    <div class="container"><p class="opp-notice ac-must-photo" role="alert"><?= icon('camera', 'icon--sm') ?> <strong>Add your picture to continue.</strong> Every LAF Academy learner has a picture on their account (also when you sign in with Google). Take a selfie or attach a clear photo of your face below. Only you and the Academy team can see it. Your courses open as soon as it is saved.</p></div>
<?php endif; ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <?= partial('web/academy/portal/_nav', ['me' => $me, 'tab' => 'profile', 'unread' => $unread ?? 0, 'portrait' => $portrait]) ?>
        <form class="ac-form card" method="post" action="<?= e(url('academy/account/profile')) ?>" novalidate>
            <?= csrf_field() ?>
            <div class="card__header" id="details"><h2 class="card__title">My details</h2></div>
            <div class="card__body">
                <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120, 'value' => old('name', $me['name']), 'help' => 'Used on new certificates. Certificates you already earned keep their name.']) ?>
                <div class="field"><label class="field__label" for="acc-email">E-mail address</label><input class="input" id="acc-email" type="email" value="<?= e($me['email']) ?>" disabled><div class="field__help">To change your e-mail address, write to us and we will help.</div></div>
                <?php $acCountry = \App\Services\Academy\LearnerAuth::countryChoices((string) old('country', (string) $me['country'])); ?>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'country', 'label' => 'Country', 'type' => 'select', 'required' => true, 'options' => $acCountry['options'], 'empty' => 'Choose your country', 'value' => old('country', (string) $me['country']), 'attrs' => ['data-country-dial' => $acCountry['json']], 'help' => 'Choose it first: your phone code is filled in for you.']) ?>
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'maxlength' => 40, 'value' => old('phone', (string) $me['phone']), 'attrs' => ['data-dial-input' => '1']]) ?>
                </div>
                <?= partial('partials/form-field', ['name' => 'whatsapp', 'label' => 'WhatsApp number', 'type' => 'tel', 'required' => true, 'maxlength' => 24, 'value' => old('whatsapp', !empty($me['whatsapp']) ? '+' . $me['whatsapp'] : ''), 'attrs' => ['data-dial-input' => '1'], 'help' => 'It starts with the code of your country. We use it to support you.']) ?>
                <?php if (\App\Services\Academy\LearnerAuth::bioReady()): ?>
                <?= partial('partials/form-field', ['name' => 'bio', 'label' => 'About me (bio)', 'type' => 'textarea', 'rows' => 4, 'maxlength' => 600, 'value' => old('bio', (string) ($me['bio'] ?? '')), 'help' => 'A few lines about you: your school or work, what you want to learn. Only you and the Academy team can see it.']) ?>
                <?php endif; ?>
                <button class="btn btn--cta btn--caps" type="submit">Save my details</button>
            </div>
        </form>

        <?php if (!empty($portraitReady)): $hasPhoto = !empty($portrait['photo_path']); ?>
        <form class="ac-form card" id="portrait" method="post" action="<?= e(url('academy/account/photo')) ?>" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title" id="picture">My picture</h2></div>
            <div class="card__body">
                <div class="ac-portrait">
                    <?php if ($hasPhoto): ?><img class="ac-portrait__img" src="<?= e(url('academy/account/photo')) ?>?v=<?= e(substr(md5((string) $portrait['photo_path']), 0, 8)) ?>" alt="My picture" width="120" height="120"><?php else: ?><span class="ac-portrait__img ac-portrait__img--empty" aria-hidden="true"><?= icon('user', 'icon--lg') ?></span><?php endif; ?>
                    <div class="ac-portrait__body">
                        <div class="field<?= error('photo') ? ' field--error' : '' ?>">
                            <label class="field__label" for="acc-photo"><?= $hasPhoto ? 'Replace my picture' : 'Add my picture' ?></label>
                            <input class="input" type="file" id="acc-photo" name="photo" accept="image/jpeg,image/png,image/webp">
                            <div class="field__help">A clear head-and-shoulders photo of you, JPG, PNG or WebP, at least 300 x 300 pixels. It is cropped to a square.</div>
                            <?php if (error('photo')): ?><div class="field__error" role="alert"><?= e((string) error('photo')) ?></div><?php endif; ?>
                        </div>
                        <label class="check"><input type="checkbox" name="consent" value="1"<?= !empty($portrait['photo_consent_at']) ? ' checked' : '' ?>><span>You may show my picture and first name on the public LAF Academy page. Leave this unticked to keep it private: then only I and the Academy team see it.</span></label>
                    </div>
                </div>
                <button class="btn btn--cta btn--caps" type="submit">Save my picture</button>
            </div>
        </form>
        <?php if ($hasPhoto): ?>
        <form method="post" action="<?= e(url('academy/account/photo/remove')) ?>" class="ac-portrait__remove" data-confirm="Remove your picture?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Remove my picture</button></form>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php $this->start('scripts'); ?><script src="<?= e(asset('js/academy.js')) ?>" defer></script><?php $this->end(); ?>
