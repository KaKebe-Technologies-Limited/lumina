<?php
/**
 * Dialogs of the e-mail panel (native <dialog>, opened by admin-newsletter-mail.js): confirm sending, confirm cancelling, e-mail preview.
 * Texts that depend on numbers are filled in by the script ([data-nl-fill="..."]).
 */
?>
<dialog class="nl-dialog" data-nl-dialog="send" aria-labelledby="nl-d-send-title" aria-describedby="nl-d-send-text">
    <form method="dialog" class="nl-dialog__form" data-nl-dialog-form="send">
        <h2 class="nl-dialog__title" id="nl-d-send-title">Send this issue to <span data-nl-fill="count">0</span> <span data-nl-fill="people">subscribers</span>?</h2>
        <div class="nl-dialog__body" id="nl-d-send-text">
            <p>Every address that is subscribed right now (<strong data-nl-fill="count">0</strong>) will get this e-mail. This cannot be undone, and an issue is only ever e-mailed once.</p>
            <dl class="nl-dialog__facts">
                <div><dt>Issue</dt><dd data-nl-fill="issue"></dd></div>
                <div><dt>Subject</dt><dd data-nl-fill="subject"></dd></div>
                <div><dt>From</dt><dd data-nl-fill="from"></dd></div>
            </dl>
            <p class="nl-dialog__warn" data-nl-fill-warn hidden></p>
        </div>
        <div class="nl-dialog__actions">
            <button type="button" class="btn btn--outline" data-nl-dialog-close autofocus>Not yet</button>
            <button type="button" class="btn btn--primary" data-nl-dialog-ok="send"><?= icon('send', 'icon--sm') ?> <span>Send to <span data-nl-fill="count">0</span></span></button>
        </div>
    </form>
</dialog>

<dialog class="nl-dialog" data-nl-dialog="cancel" aria-labelledby="nl-d-cancel-title" aria-describedby="nl-d-cancel-text">
    <form method="dialog" class="nl-dialog__form">
        <h2 class="nl-dialog__title" id="nl-d-cancel-title">Cancel sending?</h2>
        <div class="nl-dialog__body" id="nl-d-cancel-text">
            <p>The e-mails that have already gone out cannot be taken back. The <strong data-nl-fill="waiting">0</strong> still waiting will not be sent. You can start again later and it will only send to the people who have not received it yet.</p>
        </div>
        <div class="nl-dialog__actions">
            <button type="button" class="btn btn--outline" data-nl-dialog-close autofocus>Keep sending</button>
            <button type="button" class="btn btn--danger" data-nl-dialog-ok="cancel">Cancel sending</button>
        </div>
    </form>
</dialog>

<dialog class="nl-dialog nl-dialog--wide" data-nl-dialog="preview" aria-labelledby="nl-d-preview-title">
    <div class="nl-dialog__form">
        <div class="nl-preview__head">
            <h2 class="nl-dialog__title" id="nl-d-preview-title">E-mail preview</h2>
            <div class="nl-preview__tools" role="group" aria-label="Preview width">
                <button type="button" class="pills__item is-active" data-nl-width="desktop" aria-pressed="true"><?= icon('monitor', 'icon--sm') ?> Desktop</button>
                <button type="button" class="pills__item" data-nl-width="mobile" aria-pressed="false"><?= icon('smartphone', 'icon--sm') ?> Phone</button>
            </div>
            <button type="button" class="btn btn--sm btn--outline" data-nl-dialog-close aria-label="Close the preview"><?= icon('x', 'icon--sm') ?> Close</button>
        </div>
        <dl class="nl-preview__meta">
            <div><dt>Subject</dt><dd data-nl-fill="p-subject"></dd></div>
            <div><dt>Preview line</dt><dd data-nl-fill="p-preheader"></dd></div>
        </dl>
        <p class="nl-dialog__warn" data-nl-fill="p-warn" hidden></p>
        <div class="nl-preview__stage" data-nl-stage>
            <iframe class="nl-preview__frame" title="How the e-mail looks" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" data-nl-frame></iframe>
        </div>
        <p class="form-note">The unsubscribe link in this preview is only an example; every subscriber gets their own.</p>
    </div>
</dialog>
