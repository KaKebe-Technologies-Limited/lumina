/*!
 * Lumina admin - content forms and lists  (OWNER: B4 "admin-content"). Vanilla ES2020, no dependencies, no inline code.
 * Loaded by the programs / projects / posts / activities / jobs views AFTER admin.js. Hooks (all data-attributes):
 *   [data-slug-preview]                 shows the public address while the title / slug are typed.
 *       data-origin, data-base, data-prefix ("blog/"), data-slug-input ("#f-slug"), data-prefix-from ("#f-post_type": option[data-prefix] wins)
 *   form[data-post-form]                posts: press-only fields ([data-show-for="press"]), publish date shown only when publishing/scheduling
 *       select[data-type-select], select[data-status-select] (+ [data-status-help], [data-date-wrap])
 *   form[data-toggle-form]              one-click active/inactive pill in lists: POST via fetch (JSON), falls back to a normal submit
 */
(() => {
    'use strict';
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const csrf = () => { const m = $('meta[name="csrf-token"]'); return m ? m.content : ''; };

    // ------------------------------------------------------------------ public address preview
    $$('[data-slug-preview]').forEach((box) => {
        const code = $('code', box);
        const slugInput = $(box.dataset.slugInput || '#f-slug');
        if (!code || !slugInput) return;
        const titleSel = slugInput.getAttribute('data-slug-source');
        const title = titleSel ? $(titleSel) : null;
        const fromSel = box.dataset.prefixFrom ? $(box.dataset.prefixFrom) : null;
        const render = () => {
            let prefix = box.dataset.prefix || '';
            if (fromSel && fromSel.selectedOptions[0] && fromSel.selectedOptions[0].dataset.prefix) prefix = fromSel.selectedOptions[0].dataset.prefix;
            const slug = (slugInput.value || '').trim() || 'your-title';
            const base = (box.dataset.base || '/').replace(/\/+$/, '');
            code.textContent = `${box.dataset.origin || ''}${base}/${prefix}${slug}`;
        };
        slugInput.addEventListener('input', render);
        if (title) title.addEventListener('input', render);       // admin.js (loaded first) has already refreshed the slug at this point
        if (fromSel) fromSel.addEventListener('change', render);
        render();
    });

    // ------------------------------------------------------------------ posts form
    $$('form[data-post-form]').forEach((form) => {
        const type = $('[data-type-select]', form);
        const status = $('[data-status-select]', form);
        const dateWrap = $('[data-date-wrap]', form);
        const help = $('[data-status-help]', form);
        const date = dateWrap ? $('input', dateWrap) : null;
        const applyType = () => {
            const t = type ? type.value : '';
            $$('[data-show-for]', form).forEach((el) => { el.hidden = !(el.dataset.showFor || '').split(/\s+/).includes(t); });
        };
        const applyStatus = () => {
            if (!status) return;
            const opt = status.selectedOptions[0];
            if (help && opt) help.textContent = opt.dataset.help || '';
            if (dateWrap) dateWrap.hidden = status.value === 'draft';
            if (date && status.value === 'scheduled' && !date.value) date.focus();
        };
        if (type) type.addEventListener('change', applyType);
        if (status) status.addEventListener('change', applyStatus);
        applyType();
        applyStatus();
    });

    // ------------------------------------------------------------------ active / inactive toggle in lists
    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-toggle-form') || e.defaultPrevented) return;
        e.preventDefault();
        const btn = $('.status-toggle', form);
        const badge = $('[data-toggle-badge]', form);
        if (btn) btn.disabled = true;
        try {
            const res = await fetch(form.action, {
                method: 'POST', body: new FormData(form), credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error('failed');
            const on = !!data.active;
            if (btn) {
                btn.classList.toggle('is-on', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                btn.title = on ? 'Click to hide this on the website' : 'Click to show this on the website';
                const sr = $('.visually-hidden', btn);
                if (sr) sr.textContent = sr.textContent.replace(/Press to (de)?activate/, on ? 'Press to deactivate' : 'Press to activate');
            }
            if (badge) {
                badge.textContent = on ? (btn.dataset.onLabel || 'Active') : (btn.dataset.offLabel || 'Inactive');
                badge.classList.toggle('badge--success', on);
                badge.classList.toggle('badge--neutral', !on);
            }
            if (btn) btn.disabled = false;
        } catch (_) {
            form.removeAttribute('data-toggle-form');             // fall back to the classic form post
            form.submit();
        }
    });
})();
