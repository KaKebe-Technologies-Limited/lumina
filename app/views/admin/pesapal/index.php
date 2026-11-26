<?php
/** Admin > Pesapal payments. Vars: $places (one row per payment point), $onlineOff, $mobileOnly, $canEverywhere, $canFeeSwitch, $signupOn (the sign-up fee switch), $fees, $feeCurrencies, $feesReady, $canFees (Academy fees), $donationsOn, $shopOn, $shopOpen, $canShop (where Pesapal is offered), $tables (database tables present), $hasKeys, $mode, $payments, $ipnUrl, $returnUrl. */
use App\Services\Shop\Money;

$tones = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'reversed' => 'danger', 'review' => 'warning'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Pesapal payments', 'subtitle' => 'Card, mobile money and bank payments through Pesapal: the second way to pay, next to mobile money.']) ?>

<?php if (!$tables): ?>
<div class="alert alert--error" role="alert"><strong>The Pesapal database tables are missing.</strong> In phpMyAdmin, select the live database, open <b>Import</b> and import the file <code>database/updates/2026-10-pesapal.sql</code> (it only adds two tables and is safe to repeat). Then reload this page.</div>
<?php endif; ?>
<section class="card">
    <div class="card__header"><h2 class="card__title">Connection</h2></div>
    <div class="card__body">
        <p>
            <?php if ($hasKeys): ?><span class="badge badge--success badge--dot">Keys found in .env</span><?php else: ?><span class="badge badge--warning badge--dot">Keys missing</span><?php endif; ?>
            <span class="badge badge--<?= $mode === 'live' ? 'success' : 'neutral' ?>"><?= $mode === 'live' ? 'LIVE: real money' : 'SANDBOX: practice only' ?></span>
        </p>
        <?php if (!$hasKeys): ?>
            <p>Add these three lines to the <code>.env</code> file on the server (never send them in chat or e-mail), then press <strong>Test connection</strong>:</p>
            <pre>PESAPAL_ENV=sandbox
PESAPAL_CONSUMER_KEY=your key from the Pesapal dashboard
PESAPAL_CONSUMER_SECRET=your secret from the Pesapal dashboard</pre>
            <p>Use <code>sandbox</code> with your sandbox keys while testing. When Pesapal has verified your business and you have your live keys, change it to <code>PESAPAL_ENV=live</code>.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url('admin/pesapal/test')) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit">Test connection</button></form>
        <?php endif; ?>
        <p class="text-muted">Pesapal reports payments to <code><?= e($ipnUrl) ?></code> (registered automatically on the first payment). Customers return to <code><?= e($returnUrl) ?></code>.
            Online giving appears on the Donate page, and "Pesapal" appears at the shop checkout once it is switched on under Shop &gt; Payments.</p>
    </div>
</section>

<?php if (!empty($onlineOff)): ?>
<div class="alert alert--warning" role="alert" style="margin-bottom:1.5rem">
    <div>
    <strong><?= !empty($mobileOnly) ? '"Mobile money only" is on, so Pesapal is hidden everywhere.' : 'The online way is switched off, so Pesapal is hidden everywhere.' ?></strong>
    <?= !empty($mobileOnly) ? 'That setting (Admin &gt; Payment options) keeps MTN and Airtel mobile money and switches the other ways off for the whole site: Pesapal, payment links, bank details and pay on delivery.' : 'In Admin &gt; Payment options the way "Pesapal and other online gateways" is off.' ?>
    Mobile money, the first option, is not affected. Nothing here changes it by itself.
    <p style="margin:.75rem 0 0"><a href="<?= e(url('admin/daraza/api')) ?>">Open Payment options</a>, or switch only the online way back on:</p>
    <?php if (!empty($canEverywhere)): ?>
    <form method="post" action="<?= e(url('admin/pesapal/online-on')) ?>" style="margin-top:.5rem"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">Switch the online way (Pesapal) back on</button></form>
    <?php else: ?><p class="text-muted" style="margin:.5rem 0 0">Only a main administrator (who can change the site settings) can switch it back on.</p><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">Where Pesapal is offered on the website</h2></div>
    <div class="card__body">
        <p class="text-muted">Pesapal is the <strong>second</strong> way to pay, next to MTN / Airtel mobile money (the first way, which stays as it is). Payers see them in the order set in <a href="<?= e(url('admin/daraza/api')) ?>">Payment options</a>: mobile money first, Pesapal second by default. Pesapal is labelled <em>Card, mobile money or bank (Pesapal)</em> and the payer is taken to Pesapal's secure page.</p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin:1rem 0;padding:1rem;border:1px solid var(--border);border-radius:12px;background:var(--surface)">
            <div style="flex:1 1 18rem">
                <strong>Offer Pesapal everywhere (as the second option)</strong><br>
                <span class="text-muted">Ticks all six payment points below at once. It does not change mobile money, the keys or the "online" way, and nothing is charged by pressing it.</span>
            </div>
            <?php if (!empty($canEverywhere)): ?>
            <form method="post" action="<?= e(url('admin/pesapal/everywhere')) ?>"><?= csrf_field() ?><button class="btn btn--primary" type="submit">Offer Pesapal everywhere (as the second option)</button></form>
            <?php else: ?><span class="text-muted">Only a main administrator (who can change the site settings) can press this.</span><?php endif; ?>
        </div>
        <form method="post" action="<?= e(url('admin/pesapal/options')) ?>">
            <?= csrf_field() ?>
            <p class="text-muted">Or tick the places one by one, then press <strong>Save</strong>. A place shows Pesapal to visitors only when its box is ticked <em>and</em> the keys are in <code>.env</code>, the database update is imported and the online way is on.</p>
            <div style="display:grid;gap:1rem;margin:1rem 0">
            <?php foreach ($places as $pl):
                $disabled = ($pl['key'] === 'shop' && !$canShop) || (in_array($pl['key'], ['academy_signup', 'academy_cert'], true) && !$canFees);
                $field = ['donate' => 'donations'][$pl['key']] ?? $pl['key'];
                $why = [
                    'donate' => 'Donors pay on the Donate page. Paid gifts become "Received" donations with a receipt number. To make Pesapal REPLACE the external donation link choose "Pesapal only" in Settings &gt; Donations' . ($donateMethod === 'pesapal' ? ' (chosen now)' : '') . '.',
                    'project' => 'The "Give now" form of every project: a paid gift becomes a "Received" donation for that project.',
                    'shop' => 'Customers choose Pesapal when they pay for an order. Paid orders are marked paid automatically. (Also in Shop &gt; Payments.)',
                    'membership' => 'Members pay the balance of their membership fee from the page after applying and from their private page. A paid fee is recorded with a receipt, like the other payments.',
                    'academy_signup' => 'The sign-up fee of the LAF Academy (only while a fee above 0 is set below).',
                    'academy_cert' => 'The certificate fee of the LAF Academy (only while a fee above 0 is set below).',
                ][$pl['key']] ?? ''; ?>
                <label class="check" style="display:flex;gap:.75rem;align-items:flex-start">
                    <input type="checkbox" name="<?= e($field) ?>" value="1"<?= $pl['on'] ? ' checked' : '' ?><?= $disabled ? ' disabled' : '' ?>>
                    <span><strong><?= e($pl['label']) ?></strong>
                        <?= $pl['path'] !== '' ? '<a href="' . e(url(ltrim($pl['path'], '/'))) . '" target="_blank" rel="noopener">(open ' . e($pl['path']) . ')</a>' : '' ?><br>
                        <span class="text-muted"><?= $why ?><?= $disabled ? ' (Only an administrator with the matching ability can change this one.)' : '' ?></span><br>
                        <?php if ($pl['visible']): ?><span class="badge badge--success badge--dot">Visible to visitors</span>
                        <?php else: ?><span class="badge badge--<?= $pl['on'] ? 'warning' : 'neutral' ?> badge--dot"><?= $pl['on'] ? 'Ticked, but hidden' : 'Off' ?></span> <span class="text-muted">Hidden because: <?= e(implode('; ', $pl['problems'])) ?>.</span><?php endif; ?></span>
                </label>
            <?php endforeach; ?>
            </div>
            <button class="btn btn--primary" type="submit">Save</button>
        </form>
    </div>
</section>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">LAF Academy fees</h2></div>
    <div class="card__body">
        <?php if (!$feesReady): ?>
            <div class="alert alert--error" role="alert"><strong>The Academy fee columns are missing in the database.</strong> In phpMyAdmin import <code>database/updates/2026-10-academy-fees.sql</code> (safe to repeat), then reload this page. Until then no fee is charged.</div>
        <?php endif; ?>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;margin:0 0 1rem;padding:1rem;border:1px solid var(--border);border-radius:12px;background:var(--surface)">
            <div style="flex:1 1 18rem">
                <strong>Sign-up fee switch</strong>
                <div><?php if ($signupOn): ?><span class="badge badge--success badge--dot">ON: new learners pay the sign-up fee</span><?php else: ?><span class="badge badge--warning badge--dot">OFF: learners sign up for free</span><?php endif; ?></div>
                <span class="text-muted">Switching it off keeps the amount below; new accounts are free and nobody is asked to pay. Switch it on again at any time.</span>
            </div>
            <?php if ($canFeeSwitch): ?>
            <form method="post" action="<?= e(url('admin/pesapal/signup-fee-switch')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="state" value="<?= $signupOn ? 'off' : 'on' ?>">
                <button class="btn <?= $signupOn ? 'btn--outline' : 'btn--primary' ?>" type="submit"><?= $signupOn ? 'Switch the sign-up fee OFF' : 'Switch the sign-up fee ON' ?></button>
            </form>
            <?php else: ?><span class="text-muted">Only a role with "Switch the Academy sign-up fee on or off" can change this.</span><?php endif; ?>
        </div>
        <form method="post" action="<?= e(url('admin/pesapal/fees')) ?>">
            <?= csrf_field() ?>
            <p class="text-muted">Set what learners pay through Pesapal. <strong>Leave 0 for free.</strong> A fee is only charged while Pesapal is connected (keys in <code>.env</code>). Accounts and certificates that exist already are never charged.</p>
            <?php foreach ([
                'signup' => ['Sign-up fee', 'Paid once by a new learner right after creating the account. The account cannot be used until it is paid (or you waive it on the learner\'s page).'],
                'cert'   => ['Certificate fee', 'Paid by a learner to receive a certificate earned by passing a final exam (the certificate is held until it is paid, and still needs your approval if that setting is on).'],
            ] as $kind => [$label, $help]): $f = $fees[$kind]; ?>
                <div style="display:grid;gap:.25rem;margin:1rem 0">
                    <strong><?= e($label) ?></strong>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
                        <div class="field" style="margin:0"><label class="field__label" for="fee-<?= e($kind) ?>">Amount</label><input class="input" id="fee-<?= e($kind) ?>" name="<?= e($kind) ?>_amount" inputmode="decimal" value="<?= e($f['amount']) ?>" maxlength="15"<?= $canFees ? '' : ' disabled' ?>></div>
                        <div class="field" style="margin:0"><label class="field__label" for="feec-<?= e($kind) ?>">Currency</label><select class="select" id="feec-<?= e($kind) ?>" name="<?= e($kind) ?>_currency"<?= $canFees ? '' : ' disabled' ?>>
                            <?php foreach ($feeCurrencies as $code): ?><option value="<?= e($code) ?>"<?= $f['currency'] === $code ? ' selected' : '' ?>><?= e($code) ?></option><?php endforeach; ?></select></div>
                        <div>
                            <?php if ($f['on'] && $feesReady && ($hasKeys || $f['daraza'] || $f['manual'])): ?><span class="badge badge--success badge--dot">Charged: <?= e($f['display']) ?> (<?= e(implode(', ', array_filter([$hasKeys ? 'Pesapal' : '', $f['daraza'] ? 'Daraza' : '', $f['manual'] ? 'mobile money / bank' : '']))) ?>)</span>
                            <?php elseif ($f['on']): ?><span class="badge badge--warning badge--dot">Set, but not charged yet: <?= !$hasKeys ? 'no way to pay is set up: add the Pesapal keys, switch on Daraza for it (Admin > Pay with Daraza) or fill in the Academy mobile-money / bank details (Settings > LAF Academy)' : 'database update needed' ?></span>
                            <?php else: ?><span class="badge badge--neutral badge--dot">Free</span><?php endif; ?>
                        </div>
                    </div>
                    <span class="text-muted"><?= e($help) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if ($canFees): ?><button class="btn btn--primary" type="submit">Save the fees</button><?php else: ?><p class="text-muted">Only an administrator who manages the Academy can change the fees.</p><?php endif; ?>
        </form>
    </div>
</section>

<section class="card" style="margin-top:1.5rem">
    <div class="card__header"><h2 class="card__title">Payments</h2></div>
    <div class="card__body">
        <?php if ($payments === []): ?>
            <p class="text-muted">No Pesapal payment yet.</p>
        <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Date</th><th>Reference</th><th>For</th><th>Payer</th><th>Amount</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= e(format_datetime((string) $p['created_at'], 'j M Y, H:i')) ?></td>
                    <td><code><?= e($p['merchant_reference']) ?></code><?= $p['mode'] === 'sandbox' ? ' <span class="badge badge--neutral">sandbox</span>' : '' ?></td>
                    <td><?php if ($p['purpose'] === 'donation'): ?><a href="<?= e(url('admin/donations/' . (int) $p['ref_id'] . '/edit')) ?>">Donation #<?= (int) $p['ref_id'] ?></a><?php elseif ($p['purpose'] === 'project'): ?><a href="<?= e(url('admin/donations/' . (int) $p['ref_id'] . '/edit')) ?>">Project gift (donation #<?= (int) $p['ref_id'] ?>)</a><?php elseif ($p['purpose'] === 'membership'): ?><a href="<?= e(url('admin/members/' . (int) $p['ref_id'])) ?>">Membership fee (member #<?= (int) $p['ref_id'] ?>)</a><?php elseif ($p['purpose'] === 'academy_signup'): ?><a href="<?= e(url('admin/academy/learners/' . (int) $p['ref_id'])) ?>">Academy sign-up fee (learner #<?= (int) $p['ref_id'] ?>)</a><?php elseif ($p['purpose'] === 'academy_cert'): ?><a href="<?= e(url('admin/academy/certificates')) ?>">Academy certificate fee (#<?= (int) $p['ref_id'] ?>)</a><?php else: ?>Shop order #<?= (int) $p['ref_id'] ?><?php endif; ?></td>
                    <td><?= e($p['payer_name']) ?><br><small class="text-muted"><?= e($p['payer_email']) ?></small></td>
                    <td><?= e(Money::format((int) $p['amount_minor'], (string) $p['currency'])) ?></td>
                    <td><span class="badge badge--<?= e($tones[$p['status']] ?? 'neutral') ?>"><?= e(ucfirst((string) $p['status'])) ?></span><?= $p['last_error'] ? '<br><small class="text-muted">' . e($p['last_error']) . '</small>' : '' ?></td>
                    <td><?php if (in_array($p['status'], ['pending', 'review'], true) && (string) $p['order_tracking_id'] !== ''): ?>
                        <form method="post" action="<?= e(url('admin/pesapal/' . (int) $p['id'] . '/check')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit">Check with Pesapal</button></form>
                    <?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</section>
