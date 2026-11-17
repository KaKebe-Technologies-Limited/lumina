/*!
 * Contact hub (partials/contact-hub.php; styles in scss/components/_contact-hub.scss).
 * Progressive enhancement only: every phone number, e-mail and address already works as a plain link.
 *   - "Copy" buttons (hidden in the HTML) are shown and copy the value to the clipboard; the result is announced to screen readers (role=status) and flashes on the button.
 *   - a soft light follows the pointer over each tile (fine pointers only; skipped for reduced motion).
 */
(() => {
    'use strict';

    const hubs = document.querySelectorAll('[data-contact-hub]');
    if (!hubs.length) return;
    const calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    const copyText = (text) => {
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text);
        return new Promise((resolve, reject) => {              // older browsers / plain http
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy') ? resolve() : reject(new Error('copy')); } catch (e) { reject(e); } finally { document.body.removeChild(ta); }
        });
    };

    hubs.forEach((hub) => {
        const live = hub.querySelector('[data-cx-live]');
        const doneText = hub.getAttribute('data-copied') || 'Copied';

        hub.querySelectorAll('.cx-copy').forEach((btn) => {
            btn.hidden = false;
            const label = btn.querySelector('.cx-copy__text');
            const original = label ? label.textContent : '';
            let timer = 0;
            btn.addEventListener('click', () => {
                const value = btn.getAttribute('data-copy') || '';
                copyText(value).then(() => {
                    btn.classList.add('is-copied');
                    if (label) label.textContent = doneText;
                    if (live) live.textContent = doneText + ': ' + value;
                    window.clearTimeout(timer);
                    timer = window.setTimeout(() => {
                        btn.classList.remove('is-copied');
                        if (label) label.textContent = original;
                    }, 1800);
                }).catch(() => { if (live) live.textContent = value; });
            });
        });

        if (fine && !calm) {
            hub.querySelectorAll('.cx-tile').forEach((tile) => {
                tile.addEventListener('pointermove', (e) => {
                    const r = tile.getBoundingClientRect();
                    tile.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                    tile.style.setProperty('--my', (e.clientY - r.top) + 'px');
                });
            });
        }
    });
})();
