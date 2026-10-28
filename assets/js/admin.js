/*!
 * Lumina admin behaviour. Vanilla ES2020, no dependencies. FOUNDATION-OWNED (agents add their own files, e.g. admin-editor.js).
 * Hooks are data-attributes (documented in docs/ARCHITECTURE.md "JS hooks"):
 *   [data-sidebar-toggle]                      open/close the sidebar on mobile
 *   [data-confirm="Message?"]                  on a form, button or link: window.confirm() before acting
 *   [data-dismiss]                             closes the nearest .alert
 *   [data-image-field]                         image picker: file input uploads via POST admin/media/upload and fills the hidden path input
 *       children: input[type=hidden][data-image-path], input[type=file][data-image-input], [data-image-preview] (+ img), [data-image-clear]
 *   [data-repeater]                            add/remove/reorder rows (see scss/admin/_repeater.scss for the markup)
 *       data-repeater-prefix="blocks[values.items]"  data-max-rows="12"  data-min-rows="0"
 *       [data-repeater-list] [data-repeater-row] <template data-repeater-template> [data-repeater-add]
 *       [data-repeater-remove] [data-repeater-up] [data-repeater-down] [data-repeater-handle] (drag) [data-repeater-index] (row number)
 *   [data-reorder] > [data-reorder-item]       re-orderable list; buttons [data-move-up] [data-move-down]; hidden input[data-order-input] gets 1..n
 *   [data-table-filter="#tableId"]             live text filter for table rows / list items
 *   form[data-unsaved-guard]                   warns before leaving with unsaved changes
 *   input[data-slug-source="#title"]           the SLUG input: auto-filled from the #title input until edited by hand
 *   [data-maxlength-counter]                   character counter for inputs/textareas with maxlength
 *   [data-toggle-target="#id"]                 checkbox shows/hides (or enables/disables with data-toggle-mode="disable") a target
 *   [data-reset-field]                         "reset to default": sets input[data-reset-flag] in the nearest .field/[data-field] to 1 and dims it
 *   select[data-autosubmit]                    submits its form on change
 * Global helper: window.LuminaAdmin.uploadImage(file) -> Promise<{path, url, name}>; LuminaAdmin.csrf() -> token
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const csrf = () => { const m = $('meta[name="csrf-token"]'); return m ? m.content : ''; };
    const baseUrl = () => { const m = $('meta[name="base-url"]'); return m ? m.content.replace(/\/$/, '') : ''; };

    // ------------------------------------------------------------------ sidebar
    const body = document.body;
    const sidebar = $('#admin-sidebar');
    const sidebarToggle = $('button[data-sidebar-toggle]');
    const wide = window.matchMedia('(min-width: 992px)');      // lg: the sidebar is always visible
    // one place that opens/closes the drawer and keeps the toggle's aria-expanded, the focus and the hidden links in step
    const setNav = (open, returnFocus) => {
        body.classList.toggle('admin-nav-open', open);
        if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (sidebar) {
            // a closed off-canvas drawer must not be reachable with Tab (it is always on screen from lg up)
            const hidden = !open && !wide.matches;
            if (hidden) sidebar.setAttribute('inert', ''); else sidebar.removeAttribute('inert');
        }
        if (open && sidebar && !wide.matches) { const first = $('a, button', sidebar); if (first) first.focus(); }
        if (!open && returnFocus && sidebarToggle && !wide.matches) sidebarToggle.focus();
    };
    setNav(false, false);
    if (wide.addEventListener) wide.addEventListener('change', () => setNav(false, false));
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-sidebar-toggle]')) { setNav(!body.classList.contains('admin-nav-open'), true); return; }
        if (e.target.closest('.admin-backdrop')) setNav(false, true);
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && body.classList.contains('admin-nav-open')) setNav(false, true); });

    // ------------------------------------------------------------------ confirm + dismiss
    document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-confirm]');
        if (el && el.tagName !== 'FORM' && !window.confirm(el.dataset.confirm)) { e.preventDefault(); e.stopPropagation(); return; }
        const d = e.target.closest('[data-dismiss]');
        if (d) { const box = d.closest('.alert'); if (box) box.remove(); }
    }, true);
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form instanceof HTMLFormElement && form.dataset.confirm && !window.confirm(form.dataset.confirm)) e.preventDefault();
    }, true);

    // ------------------------------------------------------------------ double-submit guard (a second click on a POST form must not post it again; opt-out: data-no-guard)
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented || form.hasAttribute('data-no-guard') || (form.method || '').toLowerCase() !== 'post' || form.target === '_blank') return;
        if (form.dataset.submitting === '1') { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        const release = () => {
            delete form.dataset.submitting;
            $$('button[type="submit"][aria-busy], input[type="submit"][aria-busy]', form).forEach((b) => { b.disabled = false; b.removeAttribute('aria-busy'); });
        };
        // disable AFTER the browser has collected the form data; a response that stays on the page (a CSV download) frees the form again
        window.setTimeout(() => {
            $$('button[type="submit"], input[type="submit"]', form).forEach((btn) => { btn.disabled = true; btn.setAttribute('aria-busy', 'true'); });
            window.setTimeout(release, 5000);
        }, 0);
    });
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        $$('form[data-submitting]').forEach((f) => {
            delete f.dataset.submitting;
            $$('button[type="submit"][aria-busy], input[type="submit"][aria-busy]', f).forEach((b) => { b.disabled = false; b.removeAttribute('aria-busy'); });
        });
    });

    // ------------------------------------------------------------------ AJAX image upload (shared by image fields, repeaters and the rich-text editor)
    async function uploadImage(file) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', csrf());
        const res = await fetch(`${baseUrl()}/admin/media/upload`, {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' },
        });
        let data = {};
        try { data = await res.json(); } catch (_) { /* ignore */ }
        if (!res.ok) throw new Error(data.error || data.message || 'Upload failed.');
        return data;
    }
    window.LuminaAdmin = Object.assign(window.LuminaAdmin || {}, { uploadImage, csrf, baseUrl });

    $$('[data-image-field]').forEach((box) => {
        const input = $('[data-image-input]', box);
        const pathInput = $('[data-image-path]', box);
        const preview = $('[data-image-preview]', box);
        const img = preview ? $('img', preview) : null;
        const clear = $('[data-image-clear]', box);
        const status = $('[data-image-status]', box);
        const setPreview = (src) => {
            if (!preview || !img) return;
            if (src) { img.src = src; preview.classList.remove('is-empty'); } else { img.removeAttribute('src'); preview.classList.add('is-empty'); }
        };
        if (input) input.addEventListener('change', async () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const max = parseInt(box.dataset.maxBytes || '5242880', 10);
            if (file.size > max) { if (status) status.textContent = 'That file is too large.'; input.value = ''; return; }
            setPreview(URL.createObjectURL(file));
            if (!pathInput || box.hasAttribute('data-no-ajax')) return;     // classic multipart fallback: the controller reads <name>_file
            if (status) status.textContent = 'Uploading...';
            try {
                const out = await uploadImage(file);
                pathInput.value = out.path;
                pathInput.dispatchEvent(new Event('input', { bubbles: true }));
                setPreview(out.url);
                input.value = '';
                if (status) status.textContent = 'Uploaded.';
            } catch (err) {
                if (status) status.textContent = err.message;
                setPreview(pathInput.dataset.currentUrl || '');
            }
        });
        if (clear) clear.addEventListener('click', () => {
            if (pathInput) { pathInput.value = ''; pathInput.dispatchEvent(new Event('input', { bubbles: true })); }
            if (input) input.value = '';
            const removeFlag = $('[data-image-remove]', box);
            if (removeFlag) removeFlag.value = '1';
            setPreview('');
        });
    });

    // ------------------------------------------------------------------ repeaters
    const esc = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    $$('[data-repeater]').forEach((rep) => {
        const list = $('[data-repeater-list]', rep);
        const tpl = $('template[data-repeater-template]', rep);
        const prefix = rep.dataset.repeaterPrefix || '';
        const max = parseInt(rep.dataset.maxRows || '0', 10);
        const min = parseInt(rep.dataset.minRows || '0', 10);
        if (!list || !tpl) return;
        const rows = () => $$(':scope > [data-repeater-row]', list);
        const re = prefix ? new RegExp('^(' + esc(prefix) + ')\\[(?:\\d+|__INDEX__)\\]') : null;

        const reindex = () => {
            rows().forEach((row, i) => {
                if (re) {
                    $$('[name], [id], [for], [data-name]', row).forEach((el) => {
                        ['name', 'id', 'for', 'data-name'].forEach((attr) => {
                            const v = el.getAttribute(attr);
                            if (v && re.test(v)) el.setAttribute(attr, v.replace(re, `$1[${i}]`));
                        });
                    });
                }
                $$('[data-repeater-index]', row).forEach((n) => { n.textContent = String(i + 1); });
                const up = $('[data-repeater-up]', row); const down = $('[data-repeater-down]', row);
                if (up) up.disabled = i === 0;
                if (down) down.disabled = i === rows().length - 1;
                const rm = $('[data-repeater-remove]', row);
                if (rm) rm.disabled = rows().length <= min;
            });
            const add = $('[data-repeater-add]', rep);
            if (add) add.disabled = max > 0 && rows().length >= max;
            rep.dispatchEvent(new CustomEvent('repeater:change', { bubbles: true, detail: { count: rows().length } }));
        };

        const addRow = () => {
            if (max > 0 && rows().length >= max) return null;
            const html = tpl.innerHTML.replace(/__INDEX__/g, String(rows().length));
            const holder = document.createElement('div');
            holder.innerHTML = html.trim();
            const row = holder.firstElementChild;
            list.appendChild(row);
            reindex();
            const first = $('input:not([type=hidden]), textarea, select', row);
            if (first) first.focus();
            rep.dispatchEvent(new CustomEvent('repeater:add', { bubbles: true, detail: { row } }));
            return row;
        };

        rep.addEventListener('click', (e) => {
            const t = e.target.closest('button, a');
            if (!t || !rep.contains(t)) return;
            const row = t.closest('[data-repeater-row]');
            if (t.matches('[data-repeater-add]')) { e.preventDefault(); addRow(); }
            else if (row && t.matches('[data-repeater-remove]')) { e.preventDefault(); if (rows().length > min) { row.remove(); reindex(); } }
            else if (row && t.matches('[data-repeater-up]')) { e.preventDefault(); if (row.previousElementSibling) { list.insertBefore(row, row.previousElementSibling); reindex(); row.querySelector('[data-repeater-up]')?.focus(); } }
            else if (row && t.matches('[data-repeater-down]')) { e.preventDefault(); if (row.nextElementSibling) { list.insertBefore(row.nextElementSibling, row); reindex(); row.querySelector('[data-repeater-down]')?.focus(); } }
        });

        // drag & drop reorder (handle-initiated)
        let dragging = null;
        rep.addEventListener('mousedown', (e) => {
            const h = e.target.closest('[data-repeater-handle]');
            if (h) { const r = h.closest('[data-repeater-row]'); if (r) r.draggable = true; }
        });
        rep.addEventListener('dragstart', (e) => {
            const r = e.target.closest ? e.target.closest('[data-repeater-row]') : null;
            if (!r || !r.draggable) return;
            dragging = r; r.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', 'row'); } catch (_) { /* old browsers */ }
        });
        rep.addEventListener('dragover', (e) => {
            if (!dragging) return;
            e.preventDefault();
            const over = e.target.closest('[data-repeater-row]');
            if (!over || over === dragging) return;
            const rect = over.getBoundingClientRect();
            list.insertBefore(dragging, (e.clientY - rect.top) > rect.height / 2 ? over.nextSibling : over);
        });
        rep.addEventListener('dragend', () => {
            if (!dragging) return;
            dragging.classList.remove('is-dragging'); dragging.draggable = false; dragging = null; reindex();
        });

        reindex();
    });

    // ------------------------------------------------------------------ simple re-orderable lists
    $$('[data-reorder]').forEach((box) => {
        const items = () => $$(':scope > [data-reorder-item]', box);
        const renumber = () => items().forEach((it, i) => { const o = $('[data-order-input]', it); if (o) o.value = String(i + 1); });
        box.addEventListener('click', (e) => {
            const up = e.target.closest('[data-move-up]'); const down = e.target.closest('[data-move-down]');
            const it = e.target.closest('[data-reorder-item]');
            if (!it || (!up && !down)) return;
            e.preventDefault();
            if (up && it.previousElementSibling) box.insertBefore(it, it.previousElementSibling);
            if (down && it.nextElementSibling) box.insertBefore(it.nextElementSibling, it);
            renumber();
            (up || down).focus();
            box.dispatchEvent(new CustomEvent('reorder:change', { bubbles: true }));
        });
        let drag = null;
        box.addEventListener('mousedown', (e) => { const h = e.target.closest('[data-drag-handle]'); if (h) h.closest('[data-reorder-item]').draggable = true; });
        box.addEventListener('dragstart', (e) => { const it = e.target.closest ? e.target.closest('[data-reorder-item]') : null; if (it && it.draggable) { drag = it; it.classList.add('is-dragging'); try { e.dataTransfer.setData('text/plain', 'item'); } catch (_) { /* noop */ } } });
        box.addEventListener('dragover', (e) => {
            if (!drag) return; e.preventDefault();
            const over = e.target.closest('[data-reorder-item]');
            if (over && over !== drag) { const r = over.getBoundingClientRect(); box.insertBefore(drag, (e.clientY - r.top) > r.height / 2 ? over.nextSibling : over); }
        });
        box.addEventListener('dragend', () => { if (drag) { drag.classList.remove('is-dragging'); drag.draggable = false; drag = null; renumber(); box.dispatchEvent(new CustomEvent('reorder:change', { bubbles: true })); } });
        renumber();
    });

    // ------------------------------------------------------------------ table / list filter
    $$('[data-table-filter]').forEach((input) => {
        const target = $(input.dataset.tableFilter);
        if (!target) return;
        const rows = () => $$('tbody tr, [data-filter-item]', target);
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            rows().forEach((r) => { r.hidden = q !== '' && !r.textContent.toLowerCase().includes(q); });
        });
    });

    // ------------------------------------------------------------------ unsaved-changes guard
    $$('form[data-unsaved-guard]').forEach((form) => {
        let dirty = false; let submitting = false;
        const mark = () => { dirty = true; };
        form.addEventListener('input', mark);
        form.addEventListener('change', mark);
        form.addEventListener('submit', () => { submitting = true; });
        form.addEventListener('repeater:change', mark);
        form.addEventListener('reorder:change', mark);
        window.addEventListener('beforeunload', (e) => {
            if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; }
        });
        form.addEventListener('lumina:clean', () => { dirty = false; });
    });

    // ------------------------------------------------------------------ slug helper, counters, toggles, reset, autosubmit
    $$('[data-slug-source]').forEach((src) => {
        const src$ = $(src.dataset.slugSource);
        if (!src$) return;
        const target = src; // the element carrying the attribute IS the slug input
        let touched = target.value !== '';
        target.addEventListener('input', () => { touched = true; });
        src$.addEventListener('input', () => {
            if (touched) return;
            target.value = src$.value.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 120);
        });
    });

    $$('[data-maxlength-counter]').forEach((el) => {
        const max = parseInt(el.getAttribute('maxlength') || el.dataset.maxlength || '0', 10);
        if (!max) return;
        const out = document.createElement('div');
        out.className = 'char-count';
        el.insertAdjacentElement('afterend', out);
        const upd = () => { out.textContent = `${el.value.length} / ${max}`; out.classList.toggle('is-over', el.value.length > max); };
        el.addEventListener('input', upd); upd();
    });

    $$('[data-toggle-target]').forEach((cb) => {
        const target = $(cb.dataset.toggleTarget);
        if (!target) return;
        const apply = () => {
            const on = cb.type === 'checkbox' ? cb.checked : cb.value === '1';
            if (cb.dataset.toggleMode === 'disable') {
                target.classList.toggle('is-off', !on);
                $$('input, select, textarea, button', target).forEach((c) => { c.disabled = !on; });
            } else { target.hidden = !on; }
        };
        cb.addEventListener('change', apply); apply();
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-reset-field]');
        if (!btn) return;
        e.preventDefault();
        const wrap = btn.closest('.field, [data-field]');
        if (!wrap) return;
        const flag = $('input[data-reset-flag]', wrap);
        if (flag) flag.value = '1';
        wrap.classList.add('is-reset');
        $$('input:not([data-reset-flag]), textarea, select', wrap).forEach((c) => { if (c.dataset.default !== undefined) c.value = c.dataset.default; c.disabled = true; });
        btn.hidden = true;
        const note = $('[data-reset-note]', wrap); if (note) note.hidden = false;
        const form = btn.closest('form'); if (form) form.dispatchEvent(new Event('change', { bubbles: true }));
    });

    document.addEventListener('change', (e) => {
        const el = e.target;
        if (el instanceof HTMLSelectElement && el.hasAttribute('data-autosubmit') && el.form) el.form.submit();
    });

    // keep long forms' success feedback visible: scroll the first error into view
    const firstError = $('.field--error');
    if (firstError) firstError.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });

    // "Export" menus close when the page is clicked elsewhere or Escape is pressed
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.export-menu[open]').forEach(function (d) { if (!d.contains(e.target)) { d.removeAttribute('open'); } });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { document.querySelectorAll('details.export-menu[open]').forEach(function (d) { d.removeAttribute('open'); }); }
    });
})();
