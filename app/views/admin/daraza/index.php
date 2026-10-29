<?php
/** Admin > Pay with Daraza. Vars: $v (label, url, donate_url, shop_url, signup_url, cert_url, membership_url), $places (place => name, on, link, can). */
$fields = [
    'donate' => ['donate_url', 'Link for the Donate page'],
    'shop'   => ['shop_url', 'Link for the shop checkout'],
    'signup' => ['signup_url', 'Link for the Academy sign-up fee'],
    'cert'   => ['cert_url', 'Link for the Academy certificate fee'],
    'membership' => ['membership_url', 'Link for membership payments'],
];
?>
<?= partial('partials/admin/page-header', ['title' => 'Pay with Daraza', 'subtitle' => 'A payment button that opens your Daraza payment link. Payments are confirmed by your team (there is no automatic connection).']) ?>

<form method="post" action="<?= e(url('admin/daraza')) ?>">
    <?= csrf_field() ?>
    <section class="card">
        <div class="card__header"><h2 class="card__title">The link</h2></div>
        <div class="card__body">
            <p class="text-muted">Create a payment link in your Daraza dashboard and paste it here. Only <code>https://</code> links on <code>daraza.net</code> are accepted.</p>
            <div style="display:grid;gap:1rem;max-width:46rem">
                <div class="field"><label class="field__label" for="dz-label">Button text</label><input class="input" id="dz-label" name="label" maxlength="40" value="<?= e($v['label']) ?>"></div>
                <div class="field"><label class="field__label" for="dz-url">Main payment link</label><input class="input" id="dz-url" name="url" type="url" maxlength="500" placeholder="https://paylink.daraza.net/..." value="<?= e($v['url']) ?>"><div class="field__help">Used everywhere below unless a place has a link of its own.</div></div>
            </div>
            <details style="margin-top:1rem"<?= array_filter([$v['donate_url'], $v['shop_url'], $v['signup_url'], $v['cert_url'], $v['membership_url']]) !== [] ? ' open' : '' ?>>
                <summary><strong>A different link for one place (optional)</strong></summary>
                <p class="text-muted">Useful when you made a separate Daraza link for each purpose (for example one for donations and one for the sign-up fee). Leave empty to use the main link.</p>
                <div style="display:grid;gap:1rem;max-width:46rem">
                    <?php foreach ($fields as $place => [$name, $label]): ?>
                        <div class="field"><label class="field__label" for="dz-<?= e($name) ?>"><?= e($label) ?></label><input class="input" id="dz-<?= e($name) ?>" name="<?= e($name) ?>" type="url" maxlength="500" placeholder="https://paylink.daraza.net/..." value="<?= e($v[$name]) ?>"></div>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    </section>

    <section class="card" style="margin-top:1.5rem">
        <div class="card__header"><h2 class="card__title">Where the button appears</h2></div>
        <div class="card__body">
            <p class="text-muted">Tick the places, then press <strong>Save</strong>. A place shows the button only when its box is ticked <em>and</em> it has a link.</p>
            <div style="display:grid;gap:1rem;margin:1rem 0">
                <?php
                $help = [
                    'donate' => 'Every Donate button leads to the "Give online" section of the Donate page, which then offers the Daraza button (next to Pesapal when that is on too). The money appears in your Daraza dashboard.',
                    'shop'   => 'A payment method at the shop checkout: the customer pays on Daraza, types the transaction ID on the order page, and your team marks the order paid (Admin > Shop > Orders).',
                    'signup' => 'A learner can pay the Academy sign-up fee with Daraza (when a sign-up fee is set under Pesapal payments). An administrator marks the fee paid on the learner\'s page after checking Daraza.',
                    'cert'   => 'A learner can pay the certificate fee with Daraza. An administrator marks it paid in Admin > Academy > Certificates after checking Daraza.',
                    'membership' => 'The membership payment details shown right after someone applies (and on their private membership page) get a Daraza button next to the mobile-money and bank details. They then attach their proof of payment, and staff record the payment in Admin > Membership.',
                ];
                foreach ($places as $place => $p): ?>
                    <label class="check" style="display:flex;gap:.75rem;align-items:flex-start">
                        <input type="checkbox" name="on_<?= e($place) ?>" value="1"<?= $p['on'] ? ' checked' : '' ?><?= $p['can'] ? '' : ' disabled' ?>>
                        <span><strong><?= e($p['name']) ?></strong><br><span class="text-muted"><?= e($help[$place]) ?><?= $p['can'] ? '' : ' (Only an administrator with the right access can change this one.)' ?></span><br>
                            <?php if ($p['on'] && $p['link']): ?><span class="badge badge--success badge--dot">Visible to visitors</span>
                            <?php elseif ($p['on']): ?><span class="badge badge--warning badge--dot">Ticked, but hidden: no link yet</span>
                            <?php else: ?><span class="badge badge--neutral badge--dot">Off</span><?php endif; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button class="btn btn--primary" type="submit">Save</button>
        </div>
    </section>
</form>
