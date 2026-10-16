/*!
 * Donate page behaviour (B1 web-core). Vanilla ES2020.
 * [data-copy="text"] buttons are hidden in the HTML and revealed only when the Clipboard API is available. A click copies the text
 * and briefly swaps the button label for data-copied (both labels are editable in Admin > Pages > Donate).
 */
(() => {
    'use strict';

    if (!navigator.clipboard || !window.isSecureContext) {
        return; // the details stay visible on the page; the copy buttons simply never appear
    }

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        const label = btn.querySelector('span');
        const original = label ? label.textContent : '';
        btn.hidden = false;
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy || '');
            } catch (e) {
                return;
            }
            if (label && btn.dataset.copied) {
                label.textContent = btn.dataset.copied;
                btn.setAttribute('aria-live', 'polite');
                window.setTimeout(() => { label.textContent = original; }, 2000);
            }
        });
    });
})();
