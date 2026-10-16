/*!
 * Donate page behaviour. Vanilla ES2020, progressive enhancement only (everything works without it).
 *  1. [data-copy="text"] buttons are hidden in the HTML and revealed only when the Clipboard API is available. A click copies the text
 *     and briefly swaps the button label for data-copied (both labels are editable in Admin > Pages > Donate).
 *  2. Pledge form quick amounts: [data-pledge-chips] is hidden in the HTML and revealed here. A chip fills the amount field
 *     ([data-pledge-amount]) and selects its currency ([data-pledge-currency]); chips mirror the field (aria-pressed).
 *     The server validates the amount again (DonateController::pledge); nothing is trusted from here.
 */
(() => {
    'use strict';

    // ------------------------------------------------------------------ copy buttons
    if (navigator.clipboard && window.isSecureContext) {
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
    }

    // ------------------------------------------------------------------ quick amounts
    const box = document.querySelector('[data-pledge-chips]');
    const amount = document.querySelector('[data-pledge-amount]');
    const currency = document.querySelector('[data-pledge-currency]');
    if (!box || !amount) return;

    const chips = Array.from(box.querySelectorAll('[data-chip-amount]'));
    const clean = (v) => String(v || '').replace(/[\s,]/g, '');
    const sync = () => {
        const value = clean(amount.value);
        chips.forEach((chip) => {
            const sameCurrency = !currency || !chip.dataset.chipCurrency || currency.value === chip.dataset.chipCurrency;
            chip.setAttribute('aria-pressed', String(sameCurrency && value !== '' && chip.dataset.chipAmount === value));
        });
    };

    box.hidden = false;
    chips.forEach((chip) => chip.addEventListener('click', () => {
        amount.value = chip.dataset.chipAmount || '';
        if (currency && chip.dataset.chipCurrency) {
            const known = Array.from(currency.options).some((o) => o.value === chip.dataset.chipCurrency);
            if (known) currency.value = chip.dataset.chipCurrency;
        }
        sync();
        amount.focus();
    }));
    amount.addEventListener('input', sync);
    if (currency) currency.addEventListener('change', sync);
    sync();
})();
