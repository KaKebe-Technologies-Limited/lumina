<?php
/**
 * Admin > Security. Vars (Cloudflare card: $cf, $cfSteps, $cfTicks, $cfCache, $turnstile): $checks, $activity (failed, ok, locked in 24 h), $recent, $twoFactor, $forced (on|off|null: .env override), $ready, $email, $masked, $lockMinutes, $lockChoices, $trustDays, $trustChoices, $devices.
 */
$tone = ['ok' => 'ok', 'warn' => 'warn', 'bad' => 'bad'];
$icons = ['ok' => 'check-circle', 'warn' => 'alert-triangle', 'bad' => 'alert-circle'];
$bad = count(array_filter($checks, static fn(array $c): bool => $c['status'] === 'bad'));
$warn = count(array_filter($checks, static fn(array $c): bool => $c['status'] === 'warn'));
?>
<?= partial('partials/admin/page-header', ['title' => 'Security', 'subtitle' => 'Keep strangers out of the admin: two-step sign-in, a screen lock, and a plain-language checklist. Nothing here changes your content.',
    'actions' => [['label' => 'Activity log', 'url' => url('admin/audit-log'), 'icon' => 'history', 'class' => 'btn--outline'], ['label' => 'Users', 'url' => url('admin/users'), 'icon' => 'users', 'class' => 'btn--ghost']]]) ?>

<div class="af-tiles">
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $twoFactor ? 'green' : 'red' ?>"><?= icon('shield') ?></span><div><span class="af-tile__label">Two-step sign-in</span><strong class="af-tile__value"><?= $twoFactor ? 'On' : 'Off' ?></strong><span class="af-tile__sub"><?= $twoFactor ? 'E-mailed code at every sign-in' : 'Only a password protects the admin' ?></span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $lockMinutes > 0 ? 'green' : 'gold' ?>"><?= icon('lock') ?></span><div><span class="af-tile__label">Screen lock</span><strong class="af-tile__value"><?= $lockMinutes > 0 ? (int) $lockMinutes . ' min' : 'Off' ?></strong><span class="af-tile__sub">Locks when nobody is working</span></div></div>
    <div class="af-tile"><span class="af-tile__icon"><?= icon('smartphone') ?></span><div><span class="af-tile__label">Remembered browsers</span><strong class="af-tile__value"><?= (int) $devices ?></strong><span class="af-tile__sub">Skip the code for <?= (int) $trustDays ?> days</span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $activity['failed'] > 10 ? 'red' : 'gold' ?>"><?= icon('alert-triangle') ?></span><div><span class="af-tile__label">Failed sign-ins, 24 h</span><strong class="af-tile__value"><?= (int) $activity['failed'] ?></strong><span class="af-tile__sub"><?= (int) $activity['ok'] ?> successful</span></div></div>
</div>

<section class="card af-card" id="checklist"><div class="card__body">
    <h2 class="card__title">Safety checklist <?= $bad > 0 ? '<span class="sec-badge sec-badge--bad">' . $bad . ' to fix</span>' : ($warn > 0 ? '<span class="sec-badge sec-badge--warn">' . $warn . ' to look at</span>' : '<span class="sec-badge sec-badge--ok">All good</span>') ?></h2>
    <ul class="sec-list">
        <?php foreach ($checks as $c): ?>
            <li class="sec-item sec-item--<?= e($tone[$c['status']] ?? 'ok') ?>">
                <span class="sec-item__icon"><?= icon($icons[$c['status']] ?? 'check-circle') ?></span>
                <div><strong><?= e($c['title']) ?></strong><?php if ($c['detail'] !== ''): ?><p><?= e($c['detail']) ?></p><?php endif; ?><?php if ($c['fix'] !== ''): ?><p class="sec-item__fix"><b>What to do:</b> <?= e($c['fix']) ?></p><?php endif; ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
</div></section>

<section class="card af-card" id="two-step"><div class="card__body">
    <h2 class="card__title">Two-step sign-in by e-mail <span class="af-pill<?= $twoFactor ? ' af-pill--on' : '' ?>"><?= $twoFactor ? 'On' : 'Off' ?></span></h2>
    <p class="text-muted">After the password, a 6-digit code is e-mailed to the person signing in. It works once and for 10 minutes. Even if a password is stolen, nobody can get in without that person's mailbox.</p>
    <?php if ($forced !== null): ?>
        <div class="alert alert--info" role="status">The server <code>.env</code> file contains <code>AUTH_2FA=<?= e($forced) ?></code>, which forces two-step sign-in <strong><?= e($forced) ?></strong>. Remove that line to control it from here.</div>
    <?php elseif (!$ready): ?>
        <div class="alert alert--warning" role="status">The tables for two-step sign-in could not be created. Ask your developer to check the database permissions.</div>
    <?php elseif (!$twoFactor): ?>
        <div class="sec-steps">
            <form method="post" action="<?= e(url('admin/security/2fa-test')) ?>" class="sec-step"><?= csrf_field() ?>
                <span class="sec-step__n">1</span>
                <div><strong>Send yourself a test code</strong><p class="text-muted">It goes to <b><?= e($masked) ?></b>. This proves the website can really send you e-mail, so you cannot lock yourself out.</p>
                    <button class="btn btn--outline" type="submit"><?= icon('mail', 'icon--sm') ?> Send a test code</button></div>
            </form>
            <form method="post" action="<?= e(url('admin/security/2fa-on')) ?>" class="sec-step"><?= csrf_field() ?>
                <span class="sec-step__n">2</span>
                <div><strong>Type the code to turn it on</strong>
                    <div class="sec-inline"><input class="input input--code" name="code" inputmode="numeric" maxlength="7" autocomplete="one-time-code" placeholder="123456" aria-label="Test code"><button class="btn btn--primary" type="submit"><?= icon('shield', 'icon--sm') ?> Turn on two-step sign-in</button></div></div>
            </form>
        </div>
    <?php else: ?>
        <p>Every administrator now receives a code. If the mail server ever stops working and you cannot sign in, add the line <code>AUTH_2FA=off</code> to the server <code>.env</code> file (file manager), sign in, fix the e-mail, and remove the line.</p>
        <form method="post" action="<?= e(url('admin/security/2fa-off')) ?>" class="sec-inline" data-confirm="Turn two-step sign-in off? Only a password will protect the admin."><?= csrf_field() ?>
            <input class="input" type="password" name="password" autocomplete="current-password" placeholder="Your password" aria-label="Your password" required>
            <button class="btn btn--outline" type="submit">Turn off</button>
        </form>
    <?php endif; ?>
</div></section>

<section class="card af-card" id="settings"><div class="card__body">
    <h2 class="card__title">Screen lock and remembered browsers</h2>
    <form method="post" action="<?= e(url('admin/security/settings')) ?>"><?= csrf_field() ?>
        <div class="sec-grid">
            <div class="field"><label class="field__label" for="s-lock">Lock the screen after</label>
                <select class="select" id="s-lock" name="lock_minutes"><?php foreach ($lockChoices as $m => $l): ?><option value="<?= (int) $m ?>"<?= (int) $m === (int) $lockMinutes ? ' selected' : '' ?>><?= e($l) ?> without activity</option><?php endforeach; ?></select>
                <p class="field__help">Whoever is signed in must type their password again. The page they were on is kept.</p></div>
            <div class="field"><label class="field__label" for="s-trust">"Remember this browser"</label>
                <select class="select" id="s-trust" name="trust_days"><?php foreach ($trustChoices as $d => $l): ?><option value="<?= (int) $d ?>"<?= (int) $d === (int) $trustDays ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
                <p class="field__help">A browser that was remembered skips the e-mailed code until the time is over. Choose "Do not offer it" on shared computers.</p></div>
        </div>
        <button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Save</button>
    </form>
    <hr class="sec-rule">
    <form method="post" action="<?= e(url('admin/security/devices-forget')) ?>" class="sec-inline" data-confirm="Forget every remembered browser? Everybody will type a code at their next sign-in."><?= csrf_field() ?>
        <span><strong><?= (int) $devices ?></strong> remembered browser<?= (int) $devices === 1 ? '' : 's' ?> right now.</span>
        <button class="btn btn--outline btn--sm" type="submit">Forget all remembered browsers</button>
    </form>
</div></section>

<?php
$cfRows = is_array($cfCache) ? (array) $cfCache['rows'] : [];
$cfOk = ['ok' => 'ok', 'warn' => 'warn', 'bad' => 'bad', 'unknown' => 'warn'];
$cfDone = count(array_filter($cfTicks));
?>
<section class="card af-card" id="cloudflare"><div class="card__body">
    <h2 class="card__title">Cloudflare protection <span class="af-pill<?= $cf['through'] ? ' af-pill--on' : '' ?>"><?= $cf['through'] ? 'Active' : 'Not in front yet' ?></span></h2>
    <p class="text-muted">Cloudflare is a free shield that stands in front of the website: it filters attacks and bots before they reach the hosting. It reduces risk; no tool removes every threat. Step by step guide: <code>docs/CLOUDFLARE.md</code>. Nothing on this card changes anything at Cloudflare.</p>

    <ul class="sec-list">
        <li class="sec-item sec-item--<?= $cf['through'] ? 'ok' : 'warn' ?>"><span class="sec-item__icon"><?= icon($cf['through'] ? 'check-circle' : 'alert-triangle') ?></span>
            <div><strong><?= $cf['through'] ? 'This request arrived through Cloudflare' : 'This request did not arrive through Cloudflare' ?></strong>
                <p><?= $cf['through'] ? 'A Cloudflare ray ID is present and the connection came from a Cloudflare address' . ($cf['country'] !== '' ? ' (visitor country: ' . e($cf['country']) . ')' : '') . '.' : ($cf['ray'] ? 'A Cloudflare header is present but the connection did not come from a Cloudflare address, so it is ignored.' : 'Normal until you switch Cloudflare on (the name servers are changed at the end of the guide).') ?></p></div></li>
        <li class="sec-item sec-item--<?= ($cf['through'] && !$cf['proxy_enabled']) ? 'bad' : 'ok' ?>"><span class="sec-item__icon"><?= icon(($cf['through'] && !$cf['proxy_enabled']) ? 'alert-circle' : 'check-circle') ?></span>
            <div><strong>Visitor address seen by the site: <code><?= e($cf['visitor']) ?></code> (connection from <code><?= e($cf['peer']) ?></code>)</strong>
                <p><?= $cf['proxy_enabled'] ? 'CLOUDFLARE_PROXY=1 is set: the real visitor address is read from Cloudflare only when the connection really comes from a Cloudflare address.' : 'CLOUDFLARE_PROXY is not set, so only your own TRUSTED_PROXIES setting is used.' ?></p>
                <?php if ($cf['through'] && !$cf['proxy_enabled']): ?><p class="sec-item__fix"><b>What to do:</b> add the line <code>CLOUDFLARE_PROXY=1</code> to the server .env file. Until then every visitor looks like the same Cloudflare address and shares one sign-in and form limit.</p><?php endif; ?></div></li>
        <li class="sec-item sec-item--<?= $cf['https'] ? 'ok' : 'warn' ?>"><span class="sec-item__icon"><?= icon($cf['https'] ? 'check-circle' : 'alert-triangle') ?></span>
            <div><strong><?= $cf['https'] ? 'This page was opened over HTTPS' : 'This page was NOT opened over HTTPS' ?></strong>
                <p><?= $cf['https'] ? 'HSTS is sent. In Cloudflare keep SSL/TLS on Full (strict); "Flexible" would make visitors loop between http and https.' : 'Local test sites are fine. On the live site this must be https.' ?></p></div></li>
        <li class="sec-item sec-item--<?= $turnstile['enabled'] ? 'ok' : 'warn' ?>"><span class="sec-item__icon"><?= icon($turnstile['enabled'] ? 'check-circle' : 'alert-triangle') ?></span>
            <div><strong>Turnstile bot check: <?= $turnstile['enabled'] ? 'ON' : 'off (optional)' ?></strong>
                <p>Site key: <?= $turnstile['site_key_set'] ? 'set' : 'not set' ?>. Secret key: <?= $turnstile['secret_set'] ? 'Set (hidden)' : 'not set' ?>. If Cloudflare cannot be asked: <?= $turnstile['fail'] === 'closed' ? 'the form is refused' : 'the form still works (logged)' ?>.
                <?= $turnstile['enabled'] ? 'Applied to: admin sign-in, learner sign-in and sign-up, membership, contact, newsletter, donate, volunteer, ambassador, job applications and the data-rights request.' : 'Add TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY to the server .env to switch it on (guide section 10).' ?></p></div></li>
    </ul>

    <h3 class="sec-sub">Cache check</h3>
    <p class="text-muted">The private areas (admin, academy, membership, shop, donate, payments, api) must never be stored by Cloudflare. <?= $cfRows !== [] ? 'Last check: ' . e((string) $cfCache['at']) . '.' : 'Not run yet.' ?></p>
    <form method="post" action="<?= e(url('admin/security/cloudflare-check')) ?>"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= icon('refresh', 'icon--sm') ?> Check the cache headers now</button></form>
    <?php if ($cfRows !== []): ?>
        <div class="table-wrap"><table class="table"><thead><tr><th scope="col">Page</th><th scope="col">Cache-Control sent</th><th scope="col">Cloudflare cache</th><th scope="col">Result</th></tr></thead><tbody>
            <?php foreach ($cfRows as $r): ?><tr><td><code><?= e((string) $r['path']) ?></code></td><td><code><?= e((string) $r['cache_control']) !== '' ? e((string) $r['cache_control']) : '-' ?></code></td><td><?= e((string) $r['cf_cache']) !== '' ? e((string) $r['cf_cache']) : '-' ?></td><td><span class="sec-badge sec-badge--<?= e($cfOk[$r['status']] ?? 'warn') ?>"><?= e((string) $r['note']) ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>

    <h3 class="sec-sub">Your set-up checklist <span class="text-muted">(<?= (int) $cfDone ?> of <?= count($cfSteps) ?>)</span></h3>
    <form method="post" action="<?= e(url('admin/security/cloudflare-steps')) ?>"><?= csrf_field() ?>
        <ul class="sec-list">
            <?php foreach ($cfSteps as $k => [$t, $hint]): ?>
                <li class="sec-item sec-item--<?= !empty($cfTicks[$k]) ? 'ok' : 'warn' ?>"><div><label class="sec-item__label"><input type="checkbox" name="steps[]" value="<?= e((string) $k) ?>"<?= !empty($cfTicks[$k]) ? ' checked' : '' ?>> <strong><?= e($t) ?></strong></label><p class="text-muted"><?= e($hint) ?></p></div></li>
            <?php endforeach; ?>
        </ul>
        <button class="btn btn--primary btn--sm" type="submit"><?= icon('check', 'icon--sm') ?> Save my ticks</button>
        <p class="text-muted">The ticks are only your own notes. The menus in Cloudflare may look slightly different from the guide: Cloudflare changes its screens from time to time.</p>
    </form>
</div></section>

<section class="card af-card" id="activity"><div class="card__body">
    <h2 class="card__title">Latest sign-in activity</h2>
    <?php if ($recent === []): ?><p class="text-muted">Nothing yet.</p><?php else: ?>
        <div class="table-wrap"><table class="table"><thead><tr><th scope="col">When</th><th scope="col">Who</th><th scope="col">What</th><th scope="col">From</th></tr></thead><tbody>
            <?php foreach ($recent as $r): ?><tr><td class="nowrap"><?= e(format_date((string) $r['created_at'], 'j M, H:i')) ?></td><td><?= e((string) $r['user_name']) ?></td><td><?= e((string) $r['summary']) ?></td><td class="nowrap"><?= e((string) $r['ip']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
    <p class="text-muted">Anything you do not recognise, or many failed sign-ins in a row: change the passwords of the accounts involved (Users) and turn two-step sign-in on.</p>
</div></section>
