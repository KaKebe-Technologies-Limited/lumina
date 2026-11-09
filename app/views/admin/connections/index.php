<?php
/**
 * Admin > E-mail and sign-in. Vars: $driver, $smtpHost, $smtpPort, $smtpEnc, $smtpUser (masked), $smtpSet, $gmail, $from, $fromName, $notify, $dns (spf, dmarc | null), $domain,
 * $googleOn, $googleId (masked), $redirect, $origin, $academyOn. No password or secret is ever passed to this page.
 */
$driverLabel = ['smtp' => 'SMTP (a real mail account, e.g. Gmail)', 'mail' => 'The server\'s built-in mail (PHP mail)', 'file' => 'File (nothing is sent: a computer for testing)'][$driver] ?? $driver;
$mailReady = $driver === 'smtp' && $smtpSet;
?>
<?= partial('partials/admin/page-header', ['title' => 'E-mail and sign-in', 'subtitle' => 'Connect the website to your e-mail account (sign-up codes, contact replies, newsletters, notices) and to "Continue with Google" for the LAF Academy. Passwords and keys stay in the server\'s .env file: they are never shown or saved here.']) ?>

<div class="conn-grid">
    <section class="card conn-card"><div class="card__body">
        <header class="conn-card__head">
            <h2 class="conn-card__title"><?= icon('mail', 'icon--sm') ?> E-mail</h2>
            <?php if ($mailReady): ?><span class="badge badge--success badge--dot">Connected: SMTP</span>
            <?php elseif ($driver === 'file'): ?><span class="badge badge--neutral badge--dot">Test mode: nothing is sent</span>
            <?php else: ?><span class="badge badge--warning badge--dot">Not connected to a mail account yet</span><?php endif; ?>
        </header>
        <dl class="conn-facts">
            <div><dt>How mail is sent</dt><dd><?= e($driverLabel) ?></dd></div>
            <?php if ($mailReady): ?>
                <div><dt>Mail server</dt><dd><?= e($smtpHost) ?>:<?= (int) $smtpPort ?> (<?= e($smtpEnc) ?>)<?= $gmail ? ' <span class="badge badge--info">Gmail / Google</span>' : '' ?></dd></div>
                <div><dt>Account (login)</dt><dd><?= e($smtpUser) ?></dd></div>
            <?php endif; ?>
            <div><dt>Mail is sent from</dt><dd><?= e($fromName) ?> &lt;<?= e($from) ?>&gt;</dd></div>
            <div><dt>Notices to the team go to</dt><dd><?= e($notify !== '' ? $notify : 'not set (Settings > Contact details)') ?></dd></div>
        </dl>

        <form method="post" action="<?= e(url('admin/connections/test-mail')) ?>" class="conn-test"><?= csrf_field() ?>
            <div class="field"><label class="field__label" for="conn-to">Send a test message to</label>
                <input class="input" id="conn-to" type="email" name="to" required maxlength="190" placeholder="your@address.com" value="<?= e($notify) ?>"></div>
            <button class="btn btn--primary" type="submit"><?= icon('send', 'icon--sm') ?> Send a test message</button>
        </form>

        <?php if (!$mailReady && $driver !== 'file'): ?>
            <div class="alert alert--warning" role="status"><strong>To connect Gmail or another mail account:</strong> add these lines to the <code>.env</code> file in <code>public_html</code> (Hostinger File Manager > Edit), save, then press the test button.
<pre class="conn-code">MAIL_DRIVER=smtp
MAIL_FROM=youraddress@gmail.com
MAIL_FROM_NAME="<?= e($fromName !== '' ? $fromName : 'Lumina Africa Foundation') ?>"
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=youraddress@gmail.com
MAIL_PASSWORD="your 16-letter app password"</pre>
                Gmail needs an <strong>App Password</strong> (Google account > Security > 2-Step Verification on, then App passwords). Use that, not your normal password. <code>MAIL_FROM</code> must be that same Gmail address.</div>
        <?php endif; ?>

        <?php if ($dns !== null): ?>
            <h3 class="conn-sub">Keeping the mail out of spam (<?= e($domain) ?>)</h3>
            <ul class="conn-checks">
                <li class="<?= $dns['spf'] !== null ? 'is-ok' : 'is-bad' ?>"><?= icon($dns['spf'] !== null ? 'check-circle' : 'alert-triangle', 'icon--sm') ?>
                    <div><strong>SPF record</strong> <?= $dns['spf'] !== null ? 'found' : 'not found' ?><?php if ($dns['spf'] !== null): ?><code><?= e($dns['spf']) ?></code><?php else: ?><span>Add a TXT record for <?= e($domain) ?>: <code>v=spf1 include:_spf.google.com ~all</code> (for Gmail / Google Workspace; add your other mail services to the same line).</span><?php endif; ?></div></li>
                <li class="<?= $dns['dmarc'] !== null ? 'is-ok' : 'is-bad' ?>"><?= icon($dns['dmarc'] !== null ? 'check-circle' : 'alert-triangle', 'icon--sm') ?>
                    <div><strong>DMARC record</strong> <?= $dns['dmarc'] !== null ? 'found' : 'not found' ?><?php if ($dns['dmarc'] !== null): ?><code><?= e($dns['dmarc']) ?></code><?php else: ?><span>Add a TXT record named <code>_dmarc</code>: <code>v=DMARC1; p=none; rua=mailto:<?= e($from) ?></code></span><?php endif; ?></div></li>
                <li class="is-info"><?= icon('info', 'icon--sm') ?><div><strong>DKIM</strong> <span>The mail provider gives you a DKIM record to add in your domain's DNS (Google Workspace: Admin console > Apps > Gmail > Authenticate email). Free Gmail cannot sign mail for your own domain: send from the Gmail address itself, or use Google Workspace or a sending service.</span></div></li>
            </ul>
        <?php endif; ?>
        <p class="text-muted conn-limit"><?= icon('alert-triangle', 'icon--sm') ?> <strong>Limits:</strong> a free Gmail account sends about 500 messages a day and Google Workspace about 2,000 (Google's figures change). Beyond that Google pauses sending for about a day, which would stop sign-up codes. For a larger list use a sending service (Amazon SES, Brevo, Mailgun, Resend): the same lines, with its server and login.</p>
    </div></section>

    <section class="card conn-card"><div class="card__body">
        <header class="conn-card__head">
            <h2 class="conn-card__title"><?= icon('user', 'icon--sm') ?> Google sign-in (LAF Academy)</h2>
            <?php if ($googleOn): ?><span class="badge badge--success badge--dot">On: learners see "Continue with Google"</span><?php else: ?><span class="badge badge--warning badge--dot">Off: no Google keys yet</span><?php endif; ?>
        </header>
        <p>Learners can create their Academy account, or sign in, with one click using their Google account. Google has already confirmed the e-mail address, so they skip the confirmation e-mail. The button appears on the sign-in and sign-up pages as soon as the two keys below are in the server's <code>.env</code> file.</p>
        <?php if (!$academyOn): ?><div class="alert alert--info" role="status">The Academy is switched off in the settings, so the button is not shown anywhere.</div><?php endif; ?>
        <?php if ($googleOn): ?><p>Client ID in use: <code><?= e($googleId) ?></code></p><?php endif; ?>

        <h3 class="conn-sub">Set it up (about 10 minutes)</h3>
        <ol class="conn-steps">
            <li>Open <a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">console.cloud.google.com</a> and sign in with the Google account that should own this. Create a <strong>New project</strong> (for example "LAF Academy").</li>
            <li><strong>APIs &amp; Services &gt; OAuth consent screen</strong>: choose <em>External</em>, app name "LAF Academy", your support e-mail, and your site address <code><?= e($origin) ?></code>. Under <em>Publishing status</em> press <strong>Publish app</strong> so any learner (not only test users) can sign in.</li>
            <li><strong>APIs &amp; Services &gt; Credentials &gt; Create credentials &gt; OAuth client ID</strong>. Application type: <strong>Web application</strong>.</li>
            <li>Under <strong>Authorised redirect URIs</strong> paste exactly this address (copy it, it must match to the letter):
                <div class="conn-copy"><code id="conn-redirect"><?= e($redirect) ?></code></div></li>
            <li>Press Create. Google shows a <strong>Client ID</strong> and a <strong>Client secret</strong>.</li>
            <li>Add them to the <code>.env</code> file in <code>public_html</code> (Hostinger File Manager > Edit), save:
<pre class="conn-code">GOOGLE_CLIENT_ID=1234567890-abc123.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-your-secret-here</pre></li>
            <li>Refresh this page: the badge turns green. Open <a href="<?= e(url('academy/login')) ?>" target="_blank" rel="noopener">the Academy sign-in page</a> and try "Continue with Google".</li>
        </ol>
        <p class="text-muted">The secret is never shown here or kept in the database. If you ever think it leaked, create a new one in the Google console and replace it in <code>.env</code>.</p>
    </div></section>
</div>
