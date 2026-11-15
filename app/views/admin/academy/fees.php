<?php
/**
 * Admin > LAF Academy > Fees & payments. Vars: $ready, $signupOn, $signup, $cert (amount, currency, display), $autoApprove, $autoRelease, $currencies, $mobile, $bank, $manualOn,
 * $ways (name, sign-up on, certificate on, settings link, help), $messages (key => title, where, default, text, custom, placeholders_help, rows), $sample, $waitingCerts, $unpaidLearners.
 */
$this->start('scripts'); ?><script src="<?= e(asset('js/academy-fees.js')) ?>" defer></script><?php $this->end();
$yesNo = static fn(bool $on): string => $on ? '<span class="af-pill af-pill--on">On</span>' : '<span class="af-pill">Off</span>';
$waysOn = count(array_filter($ways, static fn(array $w): bool => $w[1] || $w[2]));
?>
<?= partial('partials/admin/page-header', ['title' => 'Fees & payments', 'subtitle' => 'Set what the Academy charges, where learners pay, and the words they read. Everything here updates the website and the learner portal at once.',
    'actions' => [['label' => 'Academy payments', 'url' => url('admin/academy/payments'), 'icon' => 'credit-card', 'class' => 'btn--outline'], ['label' => 'Certificates', 'url' => url('admin/academy/certificates'), 'icon' => 'award', 'class' => 'btn--ghost']]]) ?>
<?php if (!$ready): ?><div class="alert alert--warning" role="status"><strong>The fee columns are not in the database yet.</strong> Import <code>database/updates/2026-10-academy-fees.sql</code> in phpMyAdmin, then come back.</div><?php endif; ?>

<div class="af-tiles">
    <div class="af-tile"><span class="af-tile__icon"><?= icon('user-plus') ?></span><div><span class="af-tile__label">Sign-up fee</span><strong class="af-tile__value"><?= $signupOn && $signup['display'] !== '' ? e($signup['display']) : 'Free' ?></strong><span class="af-tile__sub"><?= $signupOn ? ($signup['display'] !== '' ? 'Learners pay to join' : 'No amount set') : 'Switched off' ?></span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--gold"><?= icon('award') ?></span><div><span class="af-tile__label">Certificate fee</span><strong class="af-tile__value"><?= $cert['display'] !== '' ? e($cert['display']) : 'Free' ?></strong><span class="af-tile__sub"><?= $cert['display'] !== '' ? 'Paid before a certificate is released' : 'Certificates are free' ?></span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--green"><?= icon('credit-card') ?></span><div><span class="af-tile__label">Ways to pay on</span><strong class="af-tile__value"><?= (int) $waysOn ?> of <?= count($ways) ?></strong><span class="af-tile__sub"><?= $waysOn > 0 ? 'Learners can pay' : 'Learners cannot pay yet' ?></span></div></div>
    <a class="af-tile af-tile--link" href="<?= e(url('admin/academy/certificates?status=waiting')) ?>"><span class="af-tile__icon af-tile__icon--red"><?= icon('clock') ?></span><div><span class="af-tile__label">Waiting to pay</span><strong class="af-tile__value"><?= (int) $waitingCerts ?></strong><span class="af-tile__sub">certificate<?= (int) $waitingCerts === 1 ? '' : 's' ?>, <?= (int) $unpaidLearners ?> sign-up<?= (int) $unpaidLearners === 1 ? '' : 's' ?> unpaid</span></div></a>
</div>

<nav class="af-jump" aria-label="Sections">
    <a href="#fees">1. Fees</a><a href="#ways">2. Ways to pay</a><a href="#details">3. Payment details</a><a href="#messages">4. Messages to learners</a>
</nav>

<section class="card af-card" id="fees"><div class="card__body">
    <h2 class="card__title">1. Fees</h2>
    <p class="text-muted">What a learner pays. Use digits only, for example <code>20000</code>. Type <code>0</code> for free. Certificates that already exist keep their status; new ones follow these amounts.</p>
    <form method="post" action="<?= e(url('admin/academy/fees/fees')) ?>"><?= csrf_field() ?>
        <div class="af-fees">
            <fieldset class="af-fee">
                <legend><?= icon('user-plus', 'icon--sm') ?> Sign-up fee</legend>
                <label class="af-switch"><input type="checkbox" name="signup_on" value="1" <?= $signupOn ? 'checked' : '' ?>><span class="af-switch__track"></span><span>Charge a sign-up fee</span></label>
                <div class="af-amount">
                    <div class="field"><label class="field__label" for="f-signup">Amount</label><input class="input" id="f-signup" name="signup_amount" inputmode="decimal" value="<?= e($signup['amount']) ?>"></div>
                    <div class="field"><label class="field__label" for="f-signup-cur">Currency</label><select class="select" id="f-signup-cur" name="signup_currency"><?php foreach ($currencies as $c): ?><option<?= $c === $signup['currency'] ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                </div>
                <label class="af-switch"><input type="checkbox" name="auto_approve" value="1" <?= $autoApprove ? 'checked' : '' ?>><span class="af-switch__track"></span><span>Approve the account automatically when the online payment is confirmed</span></label>
            </fieldset>
            <fieldset class="af-fee">
                <legend><?= icon('award', 'icon--sm') ?> Certificate fee</legend>
                <div class="af-amount">
                    <div class="field"><label class="field__label" for="f-cert">Amount</label><input class="input" id="f-cert" name="cert_amount" inputmode="decimal" value="<?= e($cert['amount']) ?>"></div>
                    <div class="field"><label class="field__label" for="f-cert-cur">Currency</label><select class="select" id="f-cert-cur" name="cert_currency"><?php foreach ($currencies as $c): ?><option<?= $c === $cert['currency'] ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                </div>
                <label class="af-switch"><input type="checkbox" name="auto_release" value="1" <?= $autoRelease ? 'checked' : '' ?>><span class="af-switch__track"></span><span>Release the certificate automatically when the online payment is confirmed</span></label>
                <p class="text-muted af-note">Payments by bank, mobile-money number or payment link are always confirmed by you first (Academy &gt; Certificates &gt; Mark fee paid).</p>
            </fieldset>
        </div>
        <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save the fees</button>
    </form>
</div></section>

<section class="card af-card" id="ways"><div class="card__body">
    <h2 class="card__title">2. Ways to pay</h2>
    <p class="text-muted">What learners can use right now. The fee only works when at least one way is on. To change a way, open its page.</p>
    <div class="af-ways">
        <?php foreach ($ways as [$name, $onSignup, $onCert, $link, $help]): ?>
            <div class="af-way<?= ($onSignup || $onCert) ? ' is-on' : '' ?>">
                <div class="af-way__head"><strong><?= e($name) ?></strong><?= $yesNo($onSignup || $onCert) ?></div>
                <p class="text-muted"><?= e($help) ?></p>
                <?php if ($onSignup || $onCert): ?><p class="af-way__for"><?= $onSignup ? 'Sign-up fee' : '' ?><?= $onSignup && $onCert ? ' and ' : '' ?><?= $onCert ? ($onSignup ? 'certificate fee' : 'Certificate fee') : '' ?></p><?php endif; ?>
                <?php if ($link !== null): ?><a class="btn btn--outline btn--sm" href="<?= e(url($link)) ?>">Change</a><?php else: ?><a class="btn btn--outline btn--sm" href="#details">Edit below</a><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div></section>

<section class="card af-card" id="details"><div class="card__body">
    <h2 class="card__title">3. Payment details</h2>
    <p class="text-muted">For learners who cannot use the online link. They appear on the learner portal when someone must pay a fee or asks for a certificate, and in the reminder message. The learner then types the transaction ID and you confirm it.</p>
    <form method="post" action="<?= e(url('admin/academy/fees/details')) ?>"><?= csrf_field() ?>
        <div class="af-split">
            <div>
                <div class="field"><label class="field__label" for="d-mobile">Mobile money</label><textarea class="textarea" id="d-mobile" name="mobile" rows="4" maxlength="1000" placeholder="MTN: 0772 000 000&#10;Airtel: 0700 000 000&#10;Name: Lumina Africa Foundation" data-af-source="mobile"><?= e($mobile) ?></textarea></div>
                <div class="field"><label class="field__label" for="d-bank">Bank</label><textarea class="textarea" id="d-bank" name="bank" rows="4" maxlength="1000" placeholder="Bank: Stanbic Bank&#10;Account name: Lumina Africa Foundation&#10;Account number: 0000000000" data-af-source="bank"><?= e($bank) ?></textarea></div>
                <label class="af-switch"><input type="checkbox" name="manual_on" value="1" <?= $manualOn ? 'checked' : '' ?>><span class="af-switch__track"></span><span>Show these details to learners</span></label>
                <p class="text-muted af-note">This is the same switch as "Bank and manual mobile-money details" in Payment options.</p>
                <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save the details</button>
            </div>
            <div class="af-phone" aria-label="Preview of what a learner sees">
                <span class="af-phone__tag">What the learner sees</span>
                <div class="af-phone__screen">
                    <p class="af-phone__title"><?= icon('clock', 'icon--sm') ?> <span>Pay <?= e($cert['display'] !== '' ? $cert['display'] : 'the certificate fee') ?> to receive your certificate</span></p>
                    <div class="af-phone__block" data-af-block="mobile"><h4>Mobile money</h4><div data-af-out="mobile"></div></div>
                    <div class="af-phone__block" data-af-block="bank"><h4>Bank transfer</h4><div data-af-out="bank"></div></div>
                    <p class="af-phone__empty" data-af-empty>No details yet: learners will see no payment details.</p>
                    <p class="af-phone__after">After paying, enter the transaction ID and we confirm it.</p>
                </div>
            </div>
        </div>
    </form>
</div></section>

<section class="card af-card" id="messages"><div class="card__body">
    <h2 class="card__title">4. Messages to learners</h2>
    <p class="text-muted">Change the words learners read. Put a <code>{placeholder}</code> where the real value should appear. Leave a box empty, or tick "Use the built-in wording", to go back to the default. The preview uses example values.</p>
    <form method="post" action="<?= e(url('admin/academy/fees/messages')) ?>" data-af-messages data-af-sample="<?= e((string) json_encode($sample, JSON_UNESCAPED_UNICODE)) ?>"><?= csrf_field() ?>
        <?php foreach ($messages as $k => $m): ?>
            <div class="af-msg">
                <div class="af-msg__head"><h3><?= e($m['title']) ?></h3><?= $m['custom'] ? '<span class="af-pill af-pill--on">Customised</span>' : '<span class="af-pill">Built-in wording</span>' ?></div>
                <p class="text-muted"><?= e($m['where']) ?></p>
                <div class="af-split">
                    <div>
                        <label class="field__label" for="m-<?= e($k) ?>">Your wording</label>
                        <textarea class="textarea" id="m-<?= e($k) ?>" name="msg_<?= e($k) ?>" rows="<?= (int) $m['rows'] ?>" maxlength="1500" placeholder="<?= e($m['default']) ?>" data-af-msg="<?= e($k) ?>" data-af-default="<?= e($m['default']) ?>"><?= e($m['text']) ?></textarea>
                        <div class="af-chips" aria-label="Placeholders you can insert"><?php foreach ($m['placeholders_help'] as $ph => $what): ?><button type="button" class="af-chip" data-af-insert="{<?= e($ph) ?>}" data-af-for="<?= e($k) ?>" title="<?= e($what) ?>">{<?= e($ph) ?>}</button><?php endforeach; ?></div>
                        <label class="check"><input type="checkbox" name="reset_<?= e($k) ?>" value="1"> Use the built-in wording</label>
                    </div>
                    <div class="af-bubble"><span class="af-bubble__tag">Preview</span><p data-af-preview="<?= e($k) ?>"></p></div>
                </div>
            </div>
        <?php endforeach; ?>
        <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save the messages</button>
    </form>
</div></section>
