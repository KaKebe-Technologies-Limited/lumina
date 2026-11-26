<?php
/**
 * The pop-up that holds a learner on the dashboard until the phone, country and WhatsApp number are on the account (LearnerAuth::contactGate is the real lock;
 * this is the friendly face of it). It cannot be closed: there is no close button and Escape does nothing (assets/js/academy.js, [data-ac-gate]).
 * Without JavaScript it is still a full-screen layer (scss/pages/_portal.scss .ac-gate). Vars: $me.
 * The form posts to the normal profile handler with gate=1, which saves only these three details and then opens the page the learner wanted.
 */
use App\Services\Academy\LearnerAuth;

$gcCountry = LearnerAuth::countryChoices((string) old('country', (string) ($me['country'] ?? '')));
$gcWhatsapp = old('whatsapp', !empty($me['whatsapp']) ? '+' . $me['whatsapp'] : '');
$gcFirst = trim(explode(' ', trim((string) ($me['name'] ?? '')))[0]);
?>
<div class="ac-gate" data-ac-gate>
    <div class="ac-gate__dialog" role="dialog" aria-modal="true" aria-labelledby="ac-gate-title" aria-describedby="ac-gate-desc" tabindex="-1">
        <span class="ac-gate__icon" aria-hidden="true"><?= icon('message-circle', 'icon--lg') ?></span>
        <h2 class="ac-gate__title" id="ac-gate-title">Add your WhatsApp number to continue</h2>
        <p class="ac-gate__lead" id="ac-gate-desc"><?= $gcFirst !== '' ? e($gcFirst) . ', e' : 'E' ?>very LAF Academy learner needs a WhatsApp number, a phone number and a country on their account. The Academy team uses them to support you with your courses, class links and certificates. Your portal opens as soon as they are saved.</p>
        <form class="ac-gate__form" method="post" action="<?= e(url('academy/account/profile')) ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="gate" value="1">
            <?= partial('partials/form-field', ['name' => 'country', 'id' => 'gate-country', 'label' => 'Country', 'type' => 'select', 'required' => true, 'options' => $gcCountry['options'], 'autocomplete' => 'country-name', 'empty' => 'Choose your country', 'value' => old('country', (string) ($me['country'] ?? '')), 'attrs' => ['data-country-dial' => $gcCountry['json']], 'help' => 'Choose it first: your phone code is filled in for you.']) ?>
            <?= partial('partials/form-field', ['name' => 'whatsapp', 'id' => 'gate-whatsapp', 'label' => 'WhatsApp number', 'type' => 'tel', 'required' => true, 'maxlength' => 24, 'autocomplete' => 'tel', 'value' => $gcWhatsapp, 'attrs' => ['data-dial-input' => '1', 'inputmode' => 'tel'], 'help' => 'Start with the code of your country, for example +256 772 123 456.']) ?>
            <?= partial('partials/form-field', ['name' => 'phone', 'id' => 'gate-phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'maxlength' => 40, 'autocomplete' => 'tel', 'value' => old('phone', (string) ($me['phone'] ?? '')), 'attrs' => ['data-dial-input' => '1', 'inputmode' => 'tel'], 'help' => 'It can be the same number.']) ?>
            <button class="btn btn--cta btn--caps ac-gate__save" type="submit">Save and continue</button>
        </form>
        <form class="ac-gate__out" method="post" action="<?= e(url('academy/logout')) ?>"><?= csrf_field() ?><button class="ac-gate__link" type="submit">Not now? Sign out</button></form>
        <p class="ac-gate__out ac-gate__out--note">Prefer not to share your numbers? <a class="ac-gate__link" href="<?= e(url('academy/portal/security')) ?>">Delete my account</a></p>
    </div>
</div>
