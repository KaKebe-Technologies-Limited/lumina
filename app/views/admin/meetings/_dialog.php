<?php
/**
 * The one shared confirmation dialog of the meetings screens (native <dialog>, opened by admin-meetings.js through confirmDialog()).
 * The script fills the title, text, facts (label / value rows), an optional warning and the button labels. Without <dialog> support it falls back to window.confirm().
 */
?>
<dialog class="ma-dialog" data-ma-confirm aria-labelledby="ma-confirm-title" aria-describedby="ma-confirm-text">
    <form method="dialog" class="ma-dialog__form" data-ma-confirm-form>
        <h2 class="ma-dialog__title" id="ma-confirm-title" data-ma-fill="title"></h2>
        <div class="ma-dialog__body" id="ma-confirm-text">
            <p data-ma-fill="text"></p>
            <dl class="ma-dialog__facts" data-ma-fill="facts" hidden></dl>
            <p class="ma-dialog__warn" data-ma-fill="warn" hidden></p>
        </div>
        <div class="ma-dialog__actions">
            <button type="button" class="btn btn--outline" data-ma-dialog-cancel autofocus>Not yet</button>
            <button type="button" class="btn btn--primary" data-ma-dialog-ok>OK</button>
        </div>
    </form>
</dialog>
