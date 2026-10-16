/*!
 * Blog + activity pages (OWNER: B2). Progressive enhancement only: the share links are plain <a> tags.
 *   [data-copy-link="URL"]   "Copy link" button (hidden until the Clipboard API is available). Labels come from data attributes
 *                            (data-copied-text) and from the button text, both rendered from editable copy on the server.
 */
(() => {
    'use strict';

    const buttons = Array.from(document.querySelectorAll('[data-copy-link]'));
    if (!buttons.length || !navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') return;

    buttons.forEach((btn) => {
        btn.hidden = false;
        const label = btn.querySelector('[data-copy-text]');
        const root = btn.closest('[data-share]');
        const status = root ? root.querySelector('[data-copy-status]') : null;
        const original = label ? label.textContent : '';
        let timer = null;

        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copyLink || window.location.href);
            } catch (err) {
                return;
            }
            const done = btn.dataset.copiedText || original;
            if (label) label.textContent = done;
            if (status) status.textContent = done;
            btn.classList.add('is-copied');
            window.clearTimeout(timer);
            timer = window.setTimeout(() => {
                if (label) label.textContent = original;
                if (status) status.textContent = '';
                btn.classList.remove('is-copied');
            }, 2500);
        });
    });
})();
