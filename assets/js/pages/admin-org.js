/*!
 * Lumina admin - organisation screens (team, partners, documents, events, registrations, stories). OWNER: B8 "org-admin".
 * Progressive enhancement only: every form works without this file (the server validates and enforces everything).
 *
 *   form[data-bulk-form]                 bulk bar. Row checkboxes sit in the table and carry form="<bulk form id>" + [data-bulk-item];
 *                                        [data-bulk-all] selects the page. Shows "N selected", enables Apply only with a selection
 *                                        and an action, asks for confirmation when the option has data-bulk-confirm ("{n}" = count).
 *   input[data-file-input]               document upload: shows name + size, rejects wrong type / too large before uploading
 *                                        (data-allowed="pdf,doc,...", data-max-bytes).
 *   [data-end-after="#start-id"]         "ends" date-time can not be earlier than the start.
 *   [data-consent] + [data-requires-consent]   story consent: the Publish switch stays off and disabled until consent is ticked.
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    // ------------------------------------------------------------------ bulk actions
    $$('form[data-bulk-form]').forEach((form) => {
        const id = form.id;
        const items = () => $$('input[data-bulk-item][form="' + id + '"]');
        const all = $('input[data-bulk-all]');
        const select = $('[data-bulk-action]', form);
        const submit = $('[data-bulk-submit]', form);
        const count = $('[data-bulk-count]', form);
        let last = null;

        const update = () => {
            const list = items();
            const n = list.filter((i) => i.checked).length;
            if (count) count.textContent = n ? n + ' selected' : 'None selected';
            if (submit) submit.disabled = n === 0 || !select || select.value === '';
            if (all) {
                all.checked = n > 0 && n === list.length;
                all.indeterminate = n > 0 && n < list.length;
            }
            list.forEach((i) => { const tr = i.closest('tr'); if (tr) tr.classList.toggle('is-selected', i.checked); });
        };

        if (all) all.addEventListener('change', () => { items().forEach((i) => { i.checked = all.checked; }); update(); });
        items().forEach((i) => {
            i.addEventListener('click', (e) => {
                // shift-click selects the range between the last click and this one
                if (e.shiftKey && last && last !== i) {
                    const list = items();
                    const a = list.indexOf(last);
                    const b = list.indexOf(i);
                    list.slice(Math.min(a, b), Math.max(a, b) + 1).forEach((x) => { x.checked = i.checked; });
                }
                last = i;
            });
            i.addEventListener('change', update);
        });
        if (select) select.addEventListener('change', update);

        form.addEventListener('submit', (e) => {
            const n = items().filter((i) => i.checked).length;
            if (n === 0 || !select || select.value === '') { e.preventDefault(); return; }
            const opt = select.selectedOptions[0];
            const msg = opt ? opt.getAttribute('data-bulk-confirm') : '';
            if (msg && !window.confirm(msg.replace('{n}', String(n)))) e.preventDefault();
        });
        update();
    });

    // ------------------------------------------------------------------ document file picker
    const human = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');
    $$('input[data-file-input]').forEach((input) => {
        const field = input.closest('.field');
        const status = field ? $('[data-file-status]', field) : null;
        const max = parseInt(input.dataset.maxBytes || '0', 10);
        const allowed = (input.dataset.allowed || '').split(',').map((s) => s.trim().toLowerCase()).filter(Boolean);
        const say = (text) => { if (status) status.textContent = text; };
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) { say(''); return; }
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (allowed.length && !allowed.includes(ext)) {
                say('That file type is not allowed. Choose one of: ' + allowed.join(', ').toUpperCase() + '.');
                input.value = '';
                return;
            }
            if (max && file.size > max) {
                say('That file is too large (' + human(file.size) + '). The maximum is ' + human(max) + '.');
                input.value = '';
                return;
            }
            say(file.name + ' (' + human(file.size) + ') is ready to upload.');
        });
    });

    // ------------------------------------------------------------------ event end >= start
    $$('[data-end-after]').forEach((end) => {
        const start = $(end.dataset.endAfter);
        if (!start) return;
        const sync = () => {
            end.min = start.value || '';
            if (start.value && end.value && end.value < start.value) end.value = start.value;
        };
        start.addEventListener('change', sync);
        sync();
    });

    // ------------------------------------------------------------------ story consent gate
    const consent = $('input[data-consent]');
    const publish = $('input[data-requires-consent]');
    if (consent && publish) {
        const note = $('[data-consent-note]');
        const apply = () => {
            if (!consent.checked) {
                publish.checked = false;
                publish.disabled = true;
            } else {
                publish.disabled = false;
            }
            if (note) note.hidden = consent.checked;
        };
        consent.addEventListener('change', apply);
        apply();
    }
})();
