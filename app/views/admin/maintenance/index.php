<?php
/**
 * Website on / off. Vars: $on (bool: visitors currently see the maintenance page), $forced (bool: APP_MAINTENANCE=1 in .env),
 * $enabled (bool: switch in the database), $title, $message, $returnAt, $autoOpen, $contact, $social.
 */
$v = static fn(string $k, string $d = ''): string => (string) old($k, $d);
?>
<div class="admin-pages-editor">
<?= partial('partials/admin/page-header', ['title' => 'Website on / off', 'subtitle' => 'Close the website to visitors while you work on it, and open it again with one click.']) ?>

<section class="card mt-card <?= $on ? 'mt-card--off' : 'mt-card--on' ?>" aria-labelledby="mt-status">
    <div class="card__body mt-card__body">
        <div class="mt-card__state">
            <span class="mt-card__lamp" aria-hidden="true"></span>
            <div>
                <h2 class="card__title" id="mt-status"><?= $on ? 'Your website is OFF for visitors' : 'Your website is ON and live' ?></h2>
                <p class="mt-card__hint"><?= $on
                    ? 'Visitors see the maintenance page with your logo and contact details. You, and anyone signed in to this admin, still see the real site.'
                    : 'Everyone can see your website. Switch it off when you want to work on it, then switch it back on.' ?></p>
                <?php if ($forced): ?><p class="mt-card__hint"><strong>Note:</strong> <code>APP_MAINTENANCE=1</code> is set in the server .env file, so the site stays closed until that line is removed.</p><?php endif; ?>
            </div>
        </div>
        <div class="mt-card__actions">
            <form method="post" action="<?= e(url('admin/maintenance/toggle')) ?>">
                <?= csrf_field() ?>
                <?php if ($on && $enabled): ?>
                    <input type="hidden" name="mode" value="on">
                    <button class="btn btn--primary btn--lg" type="submit"><?= icon('check', 'icon--sm') ?> Turn the website back ON</button>
                <?php elseif (!$on): ?>
                    <input type="hidden" name="mode" value="off">
                    <button class="btn btn--danger btn--lg" type="submit" data-confirm="Switch the website OFF? Visitors will see the maintenance page until you switch it back on."><?= icon('clock', 'icon--sm') ?> Turn the website OFF</button>
                <?php endif; ?>
            </form>
            <a class="btn btn--outline btn--lg" href="<?= e(url('admin/maintenance/preview')) ?>" target="_blank" rel="noopener"><?= icon('external-link', 'icon--sm') ?> Preview the visitor page</a>
        </div>
    </div>
</section>

<form class="card" method="post" action="<?= e(url('admin/maintenance/save')) ?>" data-unsaved-guard novalidate>
    <?= csrf_field() ?>
    <div class="card__header"><h2 class="card__title">What visitors see</h2></div>
    <div class="card__body">
        <div class="form-grid">
            <?= partial('partials/form-field', ['name' => 'title', 'label' => 'Headline', 'value' => $v('title', $title), 'maxlength' => 120, 'help' => 'For example: We will be back soon']) ?>
            <?= partial('partials/form-field', ['name' => 'message', 'label' => 'Message to visitors', 'type' => 'textarea', 'rows' => 5, 'value' => $v('message', $message), 'maxlength' => 800, 'help' => 'Tell people what is happening and when to come back.']) ?>
            <?= partial('partials/form-field', ['name' => 'return_at', 'label' => 'Expected back (date and time, East Africa Time)', 'value' => $v('return_at', $returnAt), 'maxlength' => 20, 'placeholder' => '2026-10-25 09:00', 'help' => 'Optional. Shows a live countdown on the page. Leave empty for no countdown.']) ?>
        </div>
        <fieldset class="mt-checks">
            <legend class="visually-hidden">Options</legend>
            <label class="check"><input type="checkbox" name="auto_open" value="1" <?= (old('title') !== null ? (bool) old('auto_open') : $autoOpen) ? 'checked' : '' ?>> <span>Open the website automatically when the expected-back time arrives</span></label>
            <label class="check"><input type="checkbox" name="show_contact" value="1" <?= (old('title') !== null ? (bool) old('show_contact') : $contact) ? 'checked' : '' ?>> <span>Show our email, phone, WhatsApp and address</span></label>
            <label class="check"><input type="checkbox" name="show_social" value="1" <?= (old('title') !== null ? (bool) old('show_social') : $social) ? 'checked' : '' ?>> <span>Show our social media links</span></label>
        </fieldset>
        <p class="form-hint">The logo, contact details and social links come from Site settings. Saving here does not switch the website off or on; use the big button above.</p>
    </div>
    <div class="card__footer"><button class="btn btn--primary" type="submit"><?= icon('save', 'icon--sm') ?> Save the maintenance page</button></div>
</form>
</div>
