/*!
 * Admin inbox behaviour (messages, subscribers, involvement, applications, donations). OWNER: B5 "admin-inbox".
 * Vanilla ES2020, CSP-safe (no inline handlers). Hooks:
 *   [data-select-all]            header checkbox: ticks / unticks every [data-select-row] in the same table
 *   [data-select-row]            row checkbox (name="ids[]" form="<bulk form id>")
 *   form[data-bulk-form]         bulk-action bar: needs at least one ticked row and an action; asks for confirmation when the chosen
 *                                action is listed in data-bulk-danger (comma separated); shows "N selected" in [data-selected-count]
 *   [data-print]                 button: window.print() (donation receipt)
 */
(() => {
    'use strict';

    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const rowBoxes = () => $$('input[data-select-row]');
    const ticked = () => rowBoxes().filter((c) => c.checked);

    const refresh = () => {
        const all = rowBoxes();
        const on = ticked();
        $$('[data-select-all]').forEach((master) => {
            master.checked = all.length > 0 && on.length === all.length;
            master.indeterminate = on.length > 0 && on.length < all.length;
        });
        $$('[data-selected-count]').forEach((out) => {
            out.textContent = on.length > 0 ? `${on.length} selected` : '';
        });
        $$('tr').forEach((tr) => {
            const box = tr.querySelector('input[data-select-row]');
            if (box) tr.classList.toggle('is-selected', box.checked);
        });
    };

    document.addEventListener('change', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLInputElement)) return;
        if (t.matches('[data-select-all]')) {
            rowBoxes().forEach((c) => { c.checked = t.checked; });
            refresh();
        } else if (t.matches('[data-select-row]')) {
            refresh();
        }
    });

    $$('form[data-bulk-form]').forEach((form) => {
        const danger = (form.dataset.bulkDanger || '').split(',').filter(Boolean);
        const noun = form.dataset.bulkNoun || 'items';
        form.addEventListener('submit', (e) => {
            const action = form.querySelector('select[name="action"]');
            const count = ticked().length;
            if (count === 0) {
                e.preventDefault();
                window.alert(`Tick at least one of the ${noun} first.`);
                return;
            }
            if (!action || action.value === '') {
                e.preventDefault();
                window.alert('Choose what to do with the selected items first.');
                if (action) action.focus();
                return;
            }
            if (danger.includes(action.value)) {
                const label = action.options[action.selectedIndex].text.replace(/\s*\(.*\)\s*$/, '');
                if (!window.confirm(`${label}: ${count} ${noun}? This affects all the ticked rows.`)) e.preventDefault();
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-print]')) {
            e.preventDefault();
            window.print();
        }
    });

    refresh();
})();
