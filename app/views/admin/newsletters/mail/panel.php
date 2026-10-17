<?php
/**
 * Admin > Newsletter > E-mail panel of an issue (ADMIN ONLY, ability newsletter.send). OWNER: MAILING agent.
 * Vars: $issue (row), $status (NewsletterMailer::status()), $adminEmail (default test address), $publicUrl, $editUrl, $settingsUrl, $maxTest.
 *
 * The page is progressive: the settings form is plain HTML, everything else (campaign progress, send, test, results, preview) is driven by
 * assets/js/admin-newsletter-mail.js through the JSON endpoints /admin/api/newsletters/{id}/email/... (docs/NEWSLETTER.md section 7).
 * Hooks: [data-nl-mail] root (data-api, data-state), [data-nl-*] elements, [data-nl-act="..."] buttons. No inline JS (CSP).
 */
$id = (int) $issue['id'];
$email = $status['email'];
$api = url('admin/api/newsletters/' . $id . '/email');
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-newsletter-mail.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="nl-mail" data-nl-mail data-id="<?= $id ?>" data-api="<?= e($api) ?>" data-state="<?= e((string) json_encode($status, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)) ?>">
    <?= partial('partials/admin/page-header', [
        'title'    => 'E-mail this issue',
        'subtitle' => (string) $issue['title'],
        'actions'  => array_values(array_filter([
            ['label' => 'Back to the editor', 'url' => $editUrl, 'class' => 'btn--outline', 'icon' => 'arrow-left'],
            !empty($status['live']) ? ['label' => 'View the issue', 'url' => $publicUrl, 'class' => 'btn--ghost', 'icon' => 'external-link', 'target' => '_blank'] : null,
        ])),
    ]) ?>

    <p class="nl-mail__live visually-hidden" role="status" aria-live="polite" data-nl-live></p>
    <noscript><div class="alert alert--warning"><div class="alert__body">This screen needs JavaScript to send e-mails. Please turn it on in your browser.</div></div></noscript>

    <?php $t = $status['transport']; ?>
    <div class="alert alert--<?= $t['level'] === 'error' ? 'error' : ($t['level'] === 'warning' ? 'warning' : 'info') ?>" data-nl-transport<?= $t['level'] === 'ok' ? ' hidden' : '' ?>>
        <div class="alert__body"><strong>Mail server.</strong> <span data-nl-transport-text><?= e($t['message']) ?></span>
            <?php if ($t['level'] !== 'ok'): ?> <a href="<?= e($settingsUrl) ?>">Sender settings</a><?php endif; ?></div>
    </div>

    <!-- ===== campaign (hidden until a campaign exists) ===== -->
    <section class="card nl-campaign" aria-labelledby="nl-campaign-title" data-nl-campaign hidden>
        <div class="card__header">
            <h2 class="card__title" id="nl-campaign-title">Sending progress</h2>
            <span class="badge badge--neutral badge--dot" data-nl-state>-</span>
        </div>
        <div class="card__body">
            <p class="nl-campaign__headline" data-nl-headline></p>
            <div class="nl-progress" role="progressbar" aria-labelledby="nl-campaign-title" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-nl-progress>
                <span class="nl-progress__bar" data-nl-progress-bar></span>
            </div>
            <dl class="nl-counts">
                <div class="nl-counts__item nl-counts__item--sent"><dt>Sent</dt><dd data-nl-count="sent">0</dd></div>
                <div class="nl-counts__item nl-counts__item--failed"><dt>Failed</dt><dd data-nl-count="failed">0</dd></div>
                <div class="nl-counts__item"><dt>Waiting</dt><dd data-nl-count="queued">0</dd></div>
                <div class="nl-counts__item"><dt>Skipped</dt><dd data-nl-count="skipped">0</dd></div>
            </dl>
            <p class="nl-campaign__note" data-nl-note hidden></p>
            <p class="nl-campaign__times form-note" data-nl-times></p>
            <div class="nl-actions">
                <button type="button" class="btn btn--primary" data-nl-act="continue" hidden><?= icon('send', 'icon--sm') ?> Continue sending</button>
                <button type="button" class="btn btn--outline" data-nl-act="pause" hidden><?= icon('clock', 'icon--sm') ?> Pause</button>
                <button type="button" class="btn btn--primary" data-nl-act="resume" hidden><?= icon('play', 'icon--sm') ?> Resume</button>
                <button type="button" class="btn btn--outline" data-nl-act="retry" hidden><?= icon('refresh', 'icon--sm') ?> <span>Retry failed (<span data-nl-retry-count>0</span>)</span></button>
                <button type="button" class="btn btn--danger" data-nl-act="cancel" hidden><?= icon('x', 'icon--sm') ?> Cancel sending</button>
            </div>
        </div>
    </section>

    <!-- ===== 1. settings ===== -->
    <section class="card" aria-labelledby="nl-settings-title">
        <div class="card__header"><h2 class="card__title" id="nl-settings-title">1. E-mail settings</h2></div>
        <form class="card__body" data-nl-settings novalidate>
            <fieldset class="nl-fieldset" data-nl-settings-fields>
                <legend class="visually-hidden">E-mail settings of this issue</legend>
                <div>
                    <label class="switch">
                        <input type="checkbox" name="email_enabled" value="1"<?= $email['enabled'] ? ' checked' : '' ?> aria-describedby="nl-enabled-help">
                        <span class="switch__track" aria-hidden="true"></span>
                        <span class="switch__text">E-mail this issue to subscribers</span>
                    </label>
                    <p class="form-note" id="nl-enabled-help">Off by default. Switching it on does not send anything yet: you still confirm in step 3.</p>
                </div>
                <div class="field">
                    <label class="field__label" for="nl-subject">Subject</label>
                    <input class="input" id="nl-subject" name="email_subject" type="text" maxlength="190" value="<?= e($email['subject']) ?>" placeholder="<?= e($email['default_subject']) ?>" autocomplete="off" aria-describedby="nl-subject-help">
                    <div class="field__help" id="nl-subject-help">Leave empty to use: <em data-nl-default-subject><?= e($email['default_subject']) ?></em></div>
                    <div class="field__error" data-nl-error="email_subject" role="alert" hidden></div>
                </div>
                <div class="field">
                    <label class="field__label" for="nl-preheader">Preview line</label>
                    <input class="input" id="nl-preheader" name="email_preheader" type="text" maxlength="150" value="<?= e($email['preheader']) ?>" placeholder="<?= e((string) ($issue['summary'] ?? '')) ?>" autocomplete="off" aria-describedby="nl-preheader-help">
                    <div class="field__help" id="nl-preheader-help">The grey line shown next to the subject in most inboxes. Leave empty to use the issue summary.</div>
                    <div class="field__error" data-nl-error="email_preheader" role="alert" hidden></div>
                </div>
            </fieldset>
            <p class="form-note" data-nl-locked hidden>The e-mail settings are locked while a campaign is running and after the issue has been sent.</p>
            <div class="nl-actions">
                <button type="submit" class="btn btn--primary" data-nl-act="save-settings">Save settings</button>
                <button type="button" class="btn btn--outline" data-nl-act="preview"><?= icon('eye', 'icon--sm') ?> Preview the e-mail</button>
                <span class="nl-inline-msg" data-nl-settings-msg role="status" aria-live="polite"></span>
            </div>
        </form>
    </section>

    <!-- ===== 2. test ===== -->
    <section class="card" aria-labelledby="nl-test-title">
        <div class="card__header"><h2 class="card__title" id="nl-test-title">2. Send yourself a test</h2></div>
        <form class="card__body" data-nl-test novalidate>
            <div class="field">
                <label class="field__label" for="nl-test-emails">Send the test to</label>
                <textarea class="textarea" id="nl-test-emails" name="emails" rows="2" autocomplete="off" spellcheck="false" aria-describedby="nl-test-help"><?= e($adminEmail) ?></textarea>
                <div class="field__help" id="nl-test-help">Up to <?= (int) $maxTest ?> addresses, separated by commas or new lines. The subject starts with [TEST]. A test is not recorded and does not count as sending the issue; it works even before the issue is published.</div>
                <div class="field__error" data-nl-error="emails" role="alert" hidden></div>
            </div>
            <div class="nl-actions">
                <button type="submit" class="btn btn--outline" data-nl-act="send-test"><?= icon('mail', 'icon--sm') ?> Send test e-mail</button>
            </div>
            <ul class="nl-test-results" data-nl-test-results aria-live="polite"></ul>
        </form>
    </section>

    <!-- ===== 3. send ===== -->
    <section class="card" aria-labelledby="nl-send-title" data-nl-send>
        <div class="card__header"><h2 class="card__title" id="nl-send-title">3. Send to the subscribers</h2></div>
        <div class="card__body">
            <ul class="nl-check" data-nl-check>
                <li data-nl-check-item="live"><span class="nl-check__icon" aria-hidden="true"></span><span>The issue is published</span></li>
                <li data-nl-check-item="enabled"><span class="nl-check__icon" aria-hidden="true"></span><span>"E-mail this issue to subscribers" is switched on and saved</span></li>
                <li data-nl-check-item="transport"><span class="nl-check__icon" aria-hidden="true"></span><span>The mail server and the sender address are set up</span></li>
                <li data-nl-check-item="recipients"><span class="nl-check__icon" aria-hidden="true"></span><span data-nl-recipients-text></span></li>
            </ul>
            <p class="nl-send__why" data-nl-why hidden></p>
            <div class="nl-actions">
                <button type="button" class="btn btn--primary btn--lg" data-nl-act="send" disabled><?= icon('send', 'icon--sm') ?> <span data-nl-send-label>Send to the subscribers</span></button>
            </div>
            <p class="form-note">An issue can be e-mailed only once. It goes out in batches of <span data-nl-batch-size><?= (int) $status['batch_size'] ?></span> while this page stays open (the batch size is in <a href="<?= e($settingsUrl) ?>">Site settings &gt; Newsletter</a>). You can pause or cancel at any time. People who unsubscribe while it is being sent are skipped.</p>
            <details class="nl-cron">
                <summary>Keep sending when this page is closed (optional)</summary>
                <p class="form-note">Ask your host (hPanel &gt; Cron Jobs) to run this every 5 minutes. It carries on with queued campaigns and never starts new ones:</p>
                <p><code class="nl-code"><?= e('php ' . BASE_PATH . '/bin/newsletter-send.php') ?></code></p>
            </details>
        </div>
    </section>

    <!-- ===== results ===== -->
    <section class="card nl-results" aria-labelledby="nl-results-title" data-nl-results hidden>
        <div class="card__header">
            <h2 class="card__title" id="nl-results-title">Results per address</h2>
            <span class="form-note" data-nl-results-count></span>
        </div>
        <div class="card__body">
            <div class="nl-results__filters">
                <div class="pills" role="group" aria-label="Show">
                    <?php foreach (['' => 'All', 'sent' => 'Sent', 'failed' => 'Failed', 'queued' => 'Waiting', 'skipped' => 'Skipped'] as $k => $label): ?>
                        <button type="button" class="pills__item<?= $k === '' ? ' is-active' : '' ?>" data-nl-filter="<?= e($k) ?>" aria-pressed="<?= $k === '' ? 'true' : 'false' ?>"><?= e($label) ?> <span class="nl-pill-count" data-nl-filter-count="<?= e($k === '' ? 'all' : $k) ?>">0</span></button>
                    <?php endforeach; ?>
                </div>
                <div class="field nl-results__search">
                    <label class="visually-hidden" for="nl-results-q">Search addresses</label>
                    <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" id="nl-results-q" type="search" placeholder="Search an address" maxlength="100" autocomplete="off" data-nl-search></div>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table table--stack nl-results__table">
                    <caption class="visually-hidden">Delivery result of every address</caption>
                    <thead><tr><th scope="col">Address</th><th scope="col">Result</th><th scope="col" class="col-num">Tries</th><th scope="col">Details</th><th scope="col">Time</th></tr></thead>
                    <tbody data-nl-rows></tbody>
                </table>
            </div>
            <p class="nl-results__empty" data-nl-results-empty hidden>Nothing matches.</p>
            <nav class="nl-pager" aria-label="Result pages" data-nl-pager hidden>
                <button type="button" class="btn btn--sm btn--outline" data-nl-act="page-prev"><?= icon('chevron-left', 'icon--sm') ?> Previous</button>
                <span data-nl-page-text></span>
                <button type="button" class="btn btn--sm btn--outline" data-nl-act="page-next">Next <?= icon('chevron-right', 'icon--sm') ?></button>
            </nav>
        </div>
    </section>

    <?= partial('admin/newsletters/mail/_dialogs') ?>
</div>
