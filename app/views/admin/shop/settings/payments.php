<?php
/**
 * Admin > Shop > Payments (PAYMENT panel). OWNER: PAYMENTS. Vars: $methods (PaymentMethods::all(), already in display order), $secrets (name => set/source/readable/text), $secretInputs, $canEncrypt,
 * $mode ('test'|'live'), $modeFromEnv, $publicKey, $publicFromEnv, $keyMode ('test'|'live'|'unknown'), $options (selected Flutterwave options), $allOptions, $title, $siteName, $currency,
 * $webhookUrl, $webhookLocal, $webhookInsecure, $mock, $events, $shopOpen.
 * SECRETS ARE WRITE-ONLY: the secret key and the webhook hash are never printed back (only "Set (hidden)"); typing a new value replaces it, an empty box keeps it.
 * The "Clear" and "Test connection" buttons belong to small forms at the bottom of the page (a form cannot sit inside a form).
 */
use App\Services\Shop\PaymentMethods;
use App\Services\Shop\ShopSettings;

$byKey = [];
foreach ($methods as $m) { $byKey[$m['key']] = $m; }
$fl = $byKey['flutterwave'] ?? null;
$flReady = $fl !== null && $fl['ready'];
$has = static fn(string $k): string => (string) old($k, ShopSettings::str($k));
$on = static fn(string $k, bool $default): bool => (string) old($k, ShopSettings::bool($k) ? '1' : '0') === '1';
$selOptions = old('shop_flw_options', null);
$selOptions = is_array($selOptions) ? $selOptions : $options;
$modeNow = (string) old('shop_flw_mode', $mode);
$mismatch = $keyMode !== 'unknown' && $keyMode !== $mode;
$momoDetail = static fn(string $k): string => (string) old($k, ShopSettings::str($k));
$icons = ['book' => 'calendar', 'cod' => 'hand-heart', 'momo' => 'smartphone', 'bank' => 'banknote', 'flutterwave' => 'credit-card'];
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-shop-payments.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="ash ash-pay-panel">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Payments',
        'subtitle' => 'Choose how customers can pay: book and pay later, pay on pickup, mobile money, bank transfer, or card and mobile money online with Flutterwave.',
        'actions'  => [['label' => 'Shop settings', 'url' => url('admin/shop/settings'), 'class' => 'btn--outline', 'icon' => 'settings'], ['label' => 'View the checkout', 'url' => url('shop/checkout'), 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank']],
    ]) ?>

    <div class="alert alert--warning ash-testnote" role="note">
        <?= icon('alert-triangle') ?>
        <div class="alert__body"><strong class="alert__title">Try online payments in TEST mode first.</strong>
            The online payment connection was written from Flutterwave's documentation and was only checked against a practice copy on a developer's computer, never against the real Flutterwave. Before you take real money: use your Flutterwave TEST keys, place a test order with Flutterwave's test card or test mobile-money numbers, and check that the order turns Paid. Only then switch to LIVE and replace the keys with the live ones.</div>
    </div>
    <?php if ($mock): ?><div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body"><strong class="alert__title">Developer mode.</strong> The online payment is pointed at a local practice server (FLW_DEV_BASE_URL), not at Flutterwave. This only works on a local computer.</div></div><?php endif; ?>
    <?php if ($shopOpen): ?>
        <?php $any = array_filter($methods, static fn(array $m): bool => $m['enabled'] && $m['ready']); if ($any === []): ?>
            <div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">No payment method is ready.</strong> The shop is open but nobody can check out. Switch on and complete at least one way to pay below.</div></div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="post" action="<?= e(url('admin/shop/payments')) ?>" novalidate data-unsaved-guard data-ash-payments>
        <?= csrf_field() ?>
        <button class="visually-hidden" type="submit" tabindex="-1" aria-hidden="true">Save</button>

        <section class="card" aria-labelledby="pm-title">
            <div class="card__header"><h2 class="card__title" id="pm-title">Ways to pay</h2><span class="required-note">Customers see the ones that are on and ready, in this order</span></div>
            <div class="card__body">
                <?php foreach ($methods as $m): $k = $m['key']; $label = (string) old("shop_pay_{$k}_label", ShopSettings::str("shop_pay_{$k}_label")); $enabled = $on("shop_pay_{$k}_enabled", false); ?>
                    <article class="ash-method<?= $enabled ? ' is-on' : '' ?>" data-method="<?= e($k) ?>">
                        <header class="ash-method__head">
                            <span class="ash-method__icon"><?= icon($icons[$k] ?? 'banknote') ?></span>
                            <div class="ash-method__title">
                                <h3><?= e($m['label']) ?></h3>
                                <p class="ash-method__status">
                                    <?php if ($enabled && $m['ready']): ?><span class="badge badge--success badge--dot">On and ready</span>
                                    <?php elseif ($enabled): ?><span class="badge badge--warning badge--dot">On, but not ready: customers do not see it</span>
                                    <?php else: ?><span class="badge badge--neutral badge--dot">Off</span><?php endif; ?>
                                </p>
                            </div>
                            <div class="ash-method__switch">
                                <input type="hidden" name="shop_pay_<?= e($k) ?>_enabled" value="0">
                                <label class="switch"><input type="checkbox" name="shop_pay_<?= e($k) ?>_enabled" value="1"<?= $enabled ? ' checked' : '' ?> data-method-toggle><span class="switch__track" aria-hidden="true"></span><span class="switch__text">Offer this</span></label>
                            </div>
                        </header>
                        <?php if ($enabled && !$m['ready'] && $m['problems'] !== []): ?>
                            <ul class="ash-problems"><?php foreach ($m['problems'] as $pr): ?><li><?= icon('alert-circle', 'icon--sm') ?> <?= e($pr) ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                        <div class="ash-method__body" data-method-body>
                            <div class="form-grid">
                                <?= partial('partials/form-field', ['name' => "shop_pay_{$k}_label", 'label' => 'Name customers see', 'value' => $label, 'maxlength' => 60, 'placeholder' => PaymentMethods::label($k), 'help' => 'Empty = ' . PaymentMethods::label($k)]) ?>
                                <?= partial('partials/form-field', ['name' => "shop_pay_{$k}_sort", 'label' => 'Position', 'type' => 'number', 'value' => (string) old("shop_pay_{$k}_sort", ShopSettings::str("shop_pay_{$k}_sort")), 'min' => 1, 'max' => 999, 'step' => 1, 'help' => '1 is shown first.']) ?>
                                <?= partial('partials/form-field', ['name' => "shop_pay_{$k}_instructions", 'label' => 'What the customer is told', 'type' => 'textarea', 'rows' => 3, 'value' => (string) old("shop_pay_{$k}_instructions", ShopSettings::str("shop_pay_{$k}_instructions")), 'maxlength' => 1500, 'class' => 'field--full', 'placeholder' => PaymentMethods::instructions($k), 'help' => 'Shown at checkout and on the order page. Empty = the wording shown in grey.']) ?>
                            </div>

                            <?php if ($k === 'momo'): ?>
                                <h4 class="ash-subtitle">Mobile-money accounts that receive the money</h4>
                                <div class="form-grid">
                                    <?= partial('partials/form-field', ['name' => 'shop_momo_mtn_name', 'label' => 'MTN: account name', 'value' => $momoDetail('shop_momo_mtn_name'), 'maxlength' => 80, 'autocomplete' => 'off', 'help' => 'The name that appears when someone sends money.']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_momo_mtn_number', 'label' => 'MTN: number', 'type' => 'tel', 'value' => $momoDetail('shop_momo_mtn_number'), 'maxlength' => 30, 'autocomplete' => 'off', 'placeholder' => '0771 234 567']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_momo_airtel_name', 'label' => 'Airtel: account name', 'value' => $momoDetail('shop_momo_airtel_name'), 'maxlength' => 80, 'autocomplete' => 'off']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_momo_airtel_number', 'label' => 'Airtel: number', 'type' => 'tel', 'value' => $momoDetail('shop_momo_airtel_number'), 'maxlength' => 30, 'autocomplete' => 'off', 'placeholder' => '0701 234 567']) ?>
                                </div>
                                <p class="ash-hint">With this method the customer sends the money themselves, then types the transaction ID on their order page. <strong>You then check your mobile-money statement and mark the order paid</strong> (Orders &gt; open the order &gt; Mark as paid). Nothing is confirmed automatically.</p>
                            <?php elseif ($k === 'bank'): ?>
                                <h4 class="ash-subtitle">Bank account that receives the money</h4>
                                <div class="form-grid">
                                    <?= partial('partials/form-field', ['name' => 'shop_bank_name', 'label' => 'Bank', 'value' => $momoDetail('shop_bank_name'), 'maxlength' => 80, 'autocomplete' => 'off']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_bank_account_name', 'label' => 'Account name', 'value' => $momoDetail('shop_bank_account_name'), 'maxlength' => 80, 'autocomplete' => 'off']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_bank_account_number', 'label' => 'Account number', 'value' => $momoDetail('shop_bank_account_number'), 'maxlength' => 40, 'autocomplete' => 'off']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_bank_branch', 'label' => 'Branch (optional)', 'value' => $momoDetail('shop_bank_branch'), 'maxlength' => 80, 'autocomplete' => 'off']) ?>
                                    <?= partial('partials/form-field', ['name' => 'shop_bank_swift', 'label' => 'SWIFT / BIC code (optional)', 'value' => $momoDetail('shop_bank_swift'), 'maxlength' => 20, 'autocomplete' => 'off', 'help' => 'Only needed for transfers from other countries.']) ?>
                                </div>
                                <p class="ash-hint"><strong>You check your bank statement and mark the order paid.</strong> The customer types their transfer reference on the order page to help you find it.</p>
                            <?php elseif ($k === 'book'): ?>
                                <p class="ash-hint">The customer reserves the items and pays later (when collecting, or as you agree). It is offered only when every item in the cart has "Can be booked" switched on. Confirm each booking in Orders; unconfirmed bookings are released after the number of days in Shop settings.</p>
                            <?php elseif ($k === 'cod'): ?>
                                <p class="ash-hint">The customer pays in cash or by mobile money when they collect or receive the order. Mark it paid in Orders when the money is in your hand.</p>
                            <?php else: ?>
                                <p class="ash-hint">Card and mobile money are paid on Flutterwave's secure page. See "Flutterwave" below for the keys and the test button. This site never sees or stores card numbers.</p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="card ash-flw" id="flutterwave" aria-labelledby="fl-title">
            <div class="card__header">
                <h2 class="card__title" id="fl-title">Flutterwave (card and mobile money online)</h2>
                <span class="ash-modebadge ash-modebadge--<?= e($mode) ?>"><?= $mode === 'live' ? 'LIVE: real money' : 'TEST mode' ?></span>
            </div>
            <div class="card__body">
                <?php if ($mismatch): ?>
                    <div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">The key and the mode do not match.</strong> The saved secret key is a <?= e(strtoupper($keyMode)) ?> key but the mode is <?= e(strtoupper($mode)) ?>. Use a key of the same kind or change the mode.</div></div>
                <?php endif; ?>
                <?php if (!$canEncrypt): ?>
                    <div class="alert alert--warning" role="alert"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">This server cannot keep secrets safely.</strong> Put the secret key and the hash in the <code>.env</code> file instead (FLW_SECRET_KEY, FLW_SECRET_HASH).</div></div>
                <?php endif; ?>

                <ol class="ash-steps">
                    <li><strong>Create a Flutterwave account</strong> at flutterwave.com and finish their business checks. Use the TEST keys first.</li>
                    <li><strong>Copy the keys</strong> from Flutterwave (Settings &gt; API keys): the public key and the secret key. Paste them below.</li>
                    <li><strong>Set up the webhook.</strong> In Flutterwave (Settings &gt; Webhooks) paste the address below, and type a long secret hash. Paste the same hash below. It lets Flutterwave tell us about payments even when the customer closes the page.</li>
                    <li><strong>Press "Test connection".</strong> It checks that the secret key is accepted.</li>
                    <li><strong>Place a test order</strong> with Flutterwave's test card or test mobile-money numbers (listed in Flutterwave's help) and check that the order turns Paid.</li>
                    <li><strong>Only then</strong> choose LIVE, replace the keys with the live keys and set the live webhook.</li>
                </ol>

                <div class="form-grid">
                    <fieldset class="field field--full ash-mode">
                        <legend class="field__label">Mode</legend>
                        <?php if ($modeFromEnv): ?>
                            <p class="ash-hint">Set in the <code>.env</code> file (FLW_MODE): <strong><?= e(strtoupper($mode)) ?></strong>. Change it there.</p>
                        <?php else: ?>
                            <label class="check ash-check"><input type="radio" name="shop_flw_mode" value="test"<?= $modeNow === 'test' ? ' checked' : '' ?>> <span><strong>TEST</strong>: no real money moves. Use your test keys.</span></label>
                            <label class="check ash-check"><input type="radio" name="shop_flw_mode" value="live"<?= $modeNow === 'live' ? ' checked' : '' ?>> <span><strong>LIVE</strong>: real money moves. Only after a successful test order.</span></label>
                        <?php endif; ?>
                    </fieldset>

                    <?php if ($publicFromEnv): ?>
                        <div class="field"><span class="field__label">Public key</span><p class="ash-secret__state">Set in the .env file</p></div>
                    <?php else: ?>
                        <?= partial('partials/form-field', ['name' => 'shop_flw_public_key', 'label' => 'Public key', 'value' => $has('shop_flw_public_key'), 'maxlength' => 200, 'autocomplete' => 'off', 'attrs' => ['spellcheck' => 'false'], 'help' => 'Starts with FLWPUBK. Not secret, but keep it tidy.']) ?>
                    <?php endif; ?>

                    <?php foreach (['flw_secret_key' => ['Secret key', 'Starts with FLWSECK. Never share it.', 'flw_secret_key_new'], 'flw_secret_hash' => ['Webhook secret hash', 'A long random value that you also type in Flutterwave.', 'flw_secret_hash_new']] as $name => [$lbl, $hint, $input]): $s = $secrets[$name]; ?>
                        <div class="field ash-secret<?= error($input) ? ' field--error' : '' ?>">
                            <label class="field__label" for="<?= e($input) ?>"><?= e($lbl) ?></label>
                            <p class="ash-secret__state"><span class="badge badge--<?= $s['set'] && $s['readable'] ? 'success' : ($s['set'] ? 'danger' : 'neutral') ?> badge--dot"><?= e($s['text']) ?></span></p>
                            <?php if ($s['source'] === 'env'): ?>
                                <div class="field__help">It comes from the <code>.env</code> file, which always wins. Change or remove it there.</div>
                            <?php else: ?>
                                <div class="input-group">
                                    <input class="input input--code" id="<?= e($input) ?>" name="<?= e($input) ?>" type="password" autocomplete="new-password" spellcheck="false" maxlength="200" placeholder="<?= $s['set'] ? 'Type to replace it' : 'Paste it here' ?>" aria-describedby="<?= e($input) ?>-help"<?= error($input) ? ' aria-invalid="true"' : '' ?>>
                                    <?php if ($name === 'flw_secret_hash'): ?><button class="btn btn--outline" type="button" data-make-hash="#<?= e($input) ?>">Make a random hash</button><?php endif; ?>
                                    <button class="btn btn--ghost" type="button" data-secret-toggle="#<?= e($input) ?>" aria-pressed="false">Show</button>
                                </div>
                                <div class="field__help" id="<?= e($input) ?>-help"><?= e($hint) ?> It is stored encrypted and never shown again.<?php if ($name === 'flw_secret_hash'): ?> After "Make a random hash" the value is shown once: copy it into Flutterwave before saving.<?php endif; ?></div>
                                <?php if ($s['set']): ?><button class="btn btn--ghost btn--sm ash-danger" type="submit" form="clear-<?= e($name) ?>" data-confirm="Remove the <?= e(strtolower($lbl)) ?>? <?= $name === 'flw_secret_key' ? 'Online payments stop until you add a key.' : 'Webhooks are refused until you set a hash.' ?>"><?= icon('trash', 'icon--sm') ?> Remove the saved <?= e(strtolower($lbl)) ?></button><?php endif; ?>
                            <?php endif; ?>
                            <?php if (error($input)): ?><div class="field__error" role="alert"><?= e(error($input)) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="field field--full">
                        <span class="field__label">Webhook address (paste into Flutterwave)</span>
                        <div class="input-group"><input class="input input--code" type="text" readonly value="<?= e($webhookUrl) ?>" aria-label="Webhook address" data-webhook-url><button class="btn btn--outline" type="button" data-copy="<?= e($webhookUrl) ?>" data-copied="Copied"><?= icon('copy', 'icon--sm') ?> Copy</button></div>
                        <?php if ($webhookLocal): ?><div class="field__help ash-warn"><?= icon('alert-triangle', 'icon--sm') ?> This is a local address: Flutterwave cannot reach it. On the live website it shows your real address.</div>
                        <?php elseif ($webhookInsecure): ?><div class="field__help ash-warn"><?= icon('alert-triangle', 'icon--sm') ?> This address does not start with https. Flutterwave needs a secure address: check that APP_URL in the .env file starts with https://.</div><?php endif; ?>
                    </div>

                    <fieldset class="field field--full">
                        <legend class="field__label">What customers can use on the Flutterwave page</legend>
                        <?php foreach ($allOptions as $ok => $ol): ?>
                            <label class="check ash-check"><input type="checkbox" name="shop_flw_options[]" value="<?= e($ok) ?>"<?= in_array($ok, array_map('strval', $selOptions), true) ? ' checked' : '' ?>> <span><?= e($ol) ?></span></label>
                        <?php endforeach; ?>
                        <?php if (error('shop_flw_options')): ?><div class="field__error" role="alert"><?= e(error('shop_flw_options')) ?></div><?php endif; ?>
                    </fieldset>
                    <?= partial('partials/form-field', ['name' => 'shop_flw_title', 'label' => 'Name shown on the payment page', 'value' => $has('shop_flw_title'), 'maxlength' => 60, 'placeholder' => $siteName, 'help' => 'Empty = ' . $siteName . '.']) ?>
                    <?= partial('partials/form-field', ['name' => 'shop_currency', 'label' => 'Shop currency', 'value' => (string) old('shop_currency', $currency), 'maxlength' => 3, 'autocomplete' => 'off', 'attrs' => ['style' => 'text-transform:uppercase'], 'help' => 'Three letters, for example UGX. Changing it does NOT convert product prices.']) ?>
                </div>

                <div class="ash-test" data-test-box>
                    <button class="btn btn--primary" type="submit" form="test-connection" data-test-btn><?= icon('refresh', 'icon--sm') ?> Test connection</button>
                    <p class="ash-test__result" role="status" aria-live="polite" data-test-result></p>
                </div>
                <p class="ash-hint">"Test connection" asks Flutterwave about a made-up payment. If your secret key is accepted you see "connected". It moves no money. Save your changes first so it tests the key you just typed.</p>
            </div>
        </section>

        <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save payment settings</button></div>
    </form>

    <?php // the small forms the buttons above point at (they cannot sit inside the big form) ?>
    <form id="test-connection" method="post" action="<?= e(url('admin/shop/payments/test-connection')) ?>"><?= csrf_field() ?></form>
    <?php foreach (['flw_secret_key', 'flw_secret_hash'] as $sn): ?>
        <form id="clear-<?= e($sn) ?>" method="post" action="<?= e(url('admin/shop/payments/clear-secret')) ?>"><?= csrf_field() ?><input type="hidden" name="name" value="<?= e($sn) ?>"></form>
    <?php endforeach; ?>

    <section class="card" aria-labelledby="ev-title">
        <div class="card__header"><h2 class="card__title" id="ev-title">Latest online payment activity</h2><span class="required-note">Only references and results: never names, e-mails or keys</span></div>
        <?php if ($events === []): ?>
            <div class="card__body"><p class="ash-hint">Nothing yet. Activity appears here when a customer starts an online payment or Flutterwave sends a message.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table table--stack">
                    <thead><tr><th>When</th><th>What</th><th>Reference</th><th>Result</th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td data-label="When"><?= e(format_datetime($ev['created_at'], 'j M, H:i:s')) ?></td>
                            <td data-label="What"><?= e(str_replace('_', ' ', (string) $ev['kind'])) ?></td>
                            <td data-label="Reference"><?php if (!empty($ev['order_id']) && !empty($ev['order_number']) && can('shop.orders')): ?><a href="<?= e(url('admin/shop/orders/' . (int) $ev['order_id'])) ?>"><?= e((string) $ev['ref']) ?></a><?php else: ?><span class="ash-ref"><?= e((string) ($ev['ref'] ?? '')) ?></span><?php endif; ?></td>
                            <td data-label="Result"><?= e((string) ($ev['status'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
