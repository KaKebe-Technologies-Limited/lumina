/*!
 * Lumina admin - Partners screen (Admin > Partners and the full partner form). OWNER: PARTNERS agent (docs/PARTNERS_NEWSLETTER.md section 6).
 * Dependency-free ES2020, no inline code (CSP). Talks to the JSON API under /admin/api/partners (session cookie + X-CSRF-TOKEN header). The server
 * renders every card (admin/partners/_card.php) and answers each change with the fresh card as `html`, so this file never rebuilds card markup.
 *
 *   [data-pa-drop]                 logo drop zone (drag a picture in or click): validates type and size, shows an instant preview in the website's logo box
 *                                  (also used by the full form, where it just fills the file field)
 *   form[data-pa-quick]            quick-add bar: name + logo + website + type, one click, no page reload; the new card appears at once
 *   [data-pa-grid] > [data-pa-card] the card grid: show / hide switch, inline edit, replace / remove logo, delete (confirmation dialog),
 *                                  drag by the handle or Move earlier / later (saved automatically), select for bulk actions
 *   [data-pa-bulk]                 show / hide / delete the selected cards
 *   [data-pa-optimise]             "Optimise existing logos": loops POST /optimise until nothing is left
 *
 * Sections: 1 helpers, 2 drop zones, 3 API + toasts + dialog, 4 grid state, 5 quick add, 6 card actions, 7 order, 8 bulk, 9 optimise, 10 start-up.
 */
(() => {
    'use strict';

    const doc = document;
    const $ = (sel, root = doc) => root.querySelector(sel);
    const $$ = (sel, root = doc) => Array.from(root.querySelectorAll(sel));

    // ------------------------------------------------------------------------------------------------ 1. helpers
    const MAX_BYTES = 5 * 1024 * 1024;
    const TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
    const EXTS = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    const csrf = () => (window.LuminaAdmin && window.LuminaAdmin.csrf ? window.LuminaAdmin.csrf() : String(($('meta[name="csrf-token"]') || {}).content || ''));
    const human = (b) => (b >= 1048576 ? (b / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');
    const plural = (n, one, many) => n + ' ' + (n === 1 ? one : many);
    const hasFiles = (e) => !!(e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files'));
    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /** '' when the file is acceptable, else a friendly sentence. The server checks again (real type, pixels). */
    function checkFile(file) {
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const typeOk = file.type ? TYPES.includes(file.type) : EXTS.includes(ext);
        if (!typeOk || !EXTS.includes(ext)) return 'Only PNG, JPG, WebP or GIF pictures can be used. (SVG and PDF files are not accepted: save the logo as a PNG.)';
        if (file.size > MAX_BYTES) return 'That picture is ' + human(file.size) + '. The limit is 5 MB: save a smaller copy and try again.';
        if (file.size === 0) return 'That file is empty.';
        return '';
    }

    // ------------------------------------------------------------------------------------------------ 2. drop zones
    function initDrop(zone) {
        const input = $('[data-pa-drop-input]', zone);
        const box = $('[data-pa-preview]', zone);
        const status = $('[data-pa-drop-status]', zone);
        const clearBtn = $('[data-pa-drop-clear]', zone);
        if (!input || !box) return null;
        const original = box.innerHTML;
        let url = null;
        let file = null;

        const say = (msg, tone) => {
            if (!status) return;
            status.textContent = msg || '';
            status.classList.toggle('is-bad', tone === 'bad');
            status.classList.toggle('is-warn', tone === 'warn');
        };
        const reset = () => {
            if (url) { URL.revokeObjectURL(url); url = null; }
            file = null;
            try { input.value = ''; } catch (_) { /* old browsers */ }
            box.innerHTML = original;
            zone.classList.remove('has-file');
            if (clearBtn) clearBtn.hidden = true;
        };
        const show = (f) => {
            if (url) URL.revokeObjectURL(url);
            url = URL.createObjectURL(f);
            const img = new Image();
            img.className = 'partner-logo-img';
            img.alt = '';
            img.addEventListener('load', () => {
                if (img.naturalWidth && img.naturalWidth < 400) {
                    say(f.name + ' is only ' + img.naturalWidth + ' px wide. It will work, but a PNG at least 400 px wide looks sharper on big and high-resolution screens.', 'warn');
                }
            });
            img.src = url;
            box.replaceChildren(img);
            zone.classList.add('has-file');
            if (clearBtn) clearBtn.hidden = false;
        };
        const set = (f) => {
            const problem = checkFile(f);
            if (problem) { reset(); say(problem, 'bad'); return false; }
            file = f;
            try {
                const dt = new DataTransfer();
                dt.items.add(f);
                input.files = dt.files;
            } catch (_) { /* the quick-add form sends `file` itself */ }
            show(f);
            say(f.name + ' (' + human(f.size) + '). The website trims empty margins and resizes it for you.');
            zone.dispatchEvent(new CustomEvent('pa:file', { bubbles: true, detail: { file: f } }));
            return true;
        };

        input.addEventListener('change', () => {
            const f = input.files && input.files[0];
            if (f) set(f); else { reset(); say(''); }
        });
        if (clearBtn) clearBtn.addEventListener('click', () => { reset(); say(''); input.focus(); });

        let depth = 0;
        zone.addEventListener('dragenter', (e) => { if (!hasFiles(e)) return; e.preventDefault(); depth++; zone.classList.add('is-over'); });
        zone.addEventListener('dragover', (e) => { if (!hasFiles(e)) return; e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; });
        zone.addEventListener('dragleave', () => { depth = Math.max(0, depth - 1); if (depth === 0) zone.classList.remove('is-over'); });
        zone.addEventListener('drop', (e) => {
            if (!hasFiles(e)) return;
            e.preventDefault();
            depth = 0;
            zone.classList.remove('is-over');
            const f = e.dataTransfer.files && e.dataTransfer.files[0];
            if (f) set(f);
        });

        const api = {
            get file() { return file; },
            set, say,
            clear() { reset(); say(''); },
        };
        zone.paDrop = api;
        return api;
    }

    $$('[data-pa-drop]').forEach(initDrop);

    // the full form: a ticked "remove the logo" box and a newly chosen picture exclude each other
    const removeBox = $('[data-pa-logo-remove]');
    if (removeBox) {
        const zone = $('[data-pa-drop]');
        if (zone) {
            zone.addEventListener('pa:file', () => { removeBox.checked = false; });
            removeBox.addEventListener('change', () => { if (removeBox.checked && zone.paDrop) zone.paDrop.clear(); });
        }
    }

    const root = $('[data-partners-admin]');
    if (!root) return;                                  // the full form needs nothing else

    // ------------------------------------------------------------------------------------------------ 3. API, toasts, dialog, announcements
    const API = String(root.dataset.api || '').replace(/\/+$/, '');
    const canDelete = root.dataset.canDelete === '1';
    const aboutUrl = root.dataset.aboutUrl || '';
    const partnersUrl = root.dataset.partnersUrl || '';
    const toastsEl = $('[data-pa-toasts]', root);
    const announceEl = $('[data-pa-announce]', root);

    function announce(msg) {
        if (!announceEl) return;
        announceEl.textContent = '';
        window.setTimeout(() => { announceEl.textContent = msg; }, 40);
    }

    function toast(msg, opts = {}) {
        const tone = opts.tone || 'info';
        const t = doc.createElement('div');
        t.className = 'pa-toast pa-toast--' + tone;
        t.setAttribute('role', tone === 'error' ? 'alert' : 'status');
        const text = doc.createElement('span');
        text.className = 'pa-toast__text';
        text.textContent = msg;
        t.appendChild(text);
        if (opts.href || opts.action) {
            const a = doc.createElement(opts.href ? 'a' : 'button');
            a.className = 'pa-toast__action';
            a.textContent = opts.actionLabel || 'Open';
            if (opts.href) { a.href = opts.href; a.target = '_blank'; a.rel = 'noopener'; } else { a.type = 'button'; a.addEventListener('click', () => { t.remove(); opts.action(); }); }
            t.appendChild(a);
        }
        const close = doc.createElement('button');
        close.type = 'button';
        close.className = 'pa-toast__close';
        close.setAttribute('aria-label', 'Dismiss message');
        close.textContent = '×';
        close.addEventListener('click', () => t.remove());
        t.appendChild(close);
        toastsEl.appendChild(t);
        const ms = opts.timeout === undefined ? (tone === 'error' ? 12000 : 6500) : opts.timeout;
        if (ms > 0) window.setTimeout(() => t.remove(), ms);
        while (toastsEl.children.length > 4) toastsEl.firstElementChild.remove();
        return t;
    }

    /** One request. Never throws. @returns {{ok:boolean,status:number,data:any}} */
    async function call(method, path, body, opts = {}) {
        const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' };
        let payload;
        if (body instanceof FormData) payload = body;
        else if (body !== undefined) { headers['Content-Type'] = 'application/json'; payload = JSON.stringify(body); }
        let res;
        try {
            res = await fetch(API + path, { method, credentials: 'same-origin', headers, body: payload, keepalive: !!opts.keepalive });
        } catch (_) {
            return { ok: false, status: 0, data: { message: 'The server could not be reached. Check your internet connection and try again.' } };
        }
        let data = null;
        try { data = await res.json(); } catch (_) { data = null; }
        data = data || {};
        if (!res.ok && !data.message) {
            if (res.status === 401 || res.status === 403 || res.status === 419) data.message = 'Your session has expired. Reload the page (you may need to sign in again) and repeat the last step.';
            else if (res.status === 413) data.message = 'The picture is larger than this server accepts. Save a smaller copy and try again.';
            else if (res.status === 429) data.message = 'That was too many requests in a row. Wait a minute and try again.';
            else data.message = 'The server had a problem (' + res.status + '). Please try again.';
        }
        return { ok: res.ok, status: res.status, data };
    }

    /** First message of a failed call. */
    const why = (r) => {
        const errs = r.data && r.data.errors ? Object.values(r.data.errors) : [];
        return String(errs[0] || (r.data && r.data.message) || 'Something went wrong. Please try again.');
    };

    const dlg = $('[data-pa-dialog]', root);
    /** Confirmation dialog -> 'ok' | 'alt' | 'cancel'. Falls back to window.confirm without <dialog>. */
    function ask(o) {
        if (!dlg || typeof dlg.showModal !== 'function') {
            return Promise.resolve(window.confirm(o.title + '\n\n' + (o.text || '')) ? 'ok' : 'cancel');
        }
        $('[data-pa-dialog-title]', dlg).textContent = o.title;
        $('[data-pa-dialog-text]', dlg).textContent = o.text || '';
        const ok = $('[data-pa-dialog-ok]', dlg);
        ok.textContent = o.ok || 'OK';
        ok.className = 'btn ' + (o.okClass || 'btn--danger');
        const alt = $('[data-pa-dialog-alt]', dlg);
        alt.hidden = !o.alt;
        alt.textContent = o.alt || '';
        const pv = $('[data-pa-dialog-preview]', dlg);
        pv.replaceChildren();
        if (o.preview) { pv.appendChild(o.preview); pv.hidden = false; } else { pv.hidden = true; }
        return new Promise((resolve) => {
            const onClose = () => { dlg.removeEventListener('close', onClose); resolve(dlg.returnValue === 'ok' || dlg.returnValue === 'alt' ? dlg.returnValue : 'cancel'); };
            dlg.addEventListener('close', onClose);
            dlg.returnValue = '';
            dlg.showModal();
        });
    }
    if (dlg) dlg.addEventListener('click', (e) => { if (e.target === dlg) dlg.close('cancel'); });

    // ------------------------------------------------------------------------------------------------ 4. grid state
    const grid = $('[data-pa-grid]', root);
    const toolbar = $('[data-pa-toolbar]', root);
    const countsEl = $('[data-pa-counts]', root);
    const emptyEl = $('[data-pa-empty]', root);
    const noMatch = $('[data-pa-nomatch]', root);
    const orderHint = $('[data-pa-order-hint]', root);
    const orderStatus = $('[data-pa-order-status]', root);
    const bulkBar = $('[data-pa-bulk]', root);
    const selectAll = $('[data-pa-select-all]', root);
    const searchEl = $('[data-pa-search]', root);
    const cards = () => $$(':scope > [data-pa-card]', grid);
    const nameOf = (c) => c.dataset.name || '';
    let query = '';
    let statusFilter = 'all';
    let filtered = false;

    function renumber() {
        const list = cards();
        list.forEach((c, i) => {
            const pos = $('[data-pa-pos]', c);
            if (pos) pos.textContent = String(i + 1);
            const up = $('[data-pa-move="earlier"]', c);
            const down = $('[data-pa-move="later"]', c);
            if (up) up.disabled = i === 0;
            if (down) down.disabled = i === list.length - 1;
        });
    }

    function updateCounts() {
        const list = cards();
        const on = list.filter((c) => c.dataset.active === '1').length;
        const off = list.length - on;
        if (countsEl) countsEl.textContent = plural(list.length, 'partner', 'partners') + ': ' + on + ' on the website' + (off ? ', ' + off + ' hidden' : '');
        if (toolbar) toolbar.hidden = list.length === 0;
        if (emptyEl) emptyEl.hidden = list.length > 0;
        if (orderHint) orderHint.hidden = list.length < 2;
        if (bulkBar && list.length === 0) bulkBar.hidden = true;
    }

    function applyFilter() {
        const q = query.trim().toLowerCase();
        let shown = 0;
        cards().forEach((c) => {
            const site = ($('[data-pa-site]', c) || {}).textContent || '';
            const hay = (nameOf(c) + ' ' + (c.dataset.type || '') + ' ' + site).toLowerCase();
            const ok = (!q || hay.includes(q)) && (statusFilter === 'all' || (statusFilter === 'on') === (c.dataset.active === '1'));
            c.hidden = !ok;
            if (ok) shown++; else { const cb = $('[data-pa-select]', c); if (cb) cb.checked = false; }
        });
        filtered = q !== '' || statusFilter !== 'all';
        grid.classList.toggle('is-filtered', filtered);
        if (noMatch) noMatch.hidden = !(filtered && shown === 0 && cards().length > 0);
        updateBulk();
    }

    function refreshAll() {
        renumber();
        updateCounts();
        applyFilter();
    }

    const selected = () => cards().filter((c) => !c.hidden && $('[data-pa-select]', c).checked);
    function updateBulk() {
        const sel = selected();
        const visible = cards().filter((c) => !c.hidden);
        cards().forEach((c) => c.classList.toggle('is-selected', $('[data-pa-select]', c).checked && !c.hidden));
        if (bulkBar) {
            bulkBar.hidden = sel.length === 0;
            const n = $('[data-pa-bulk-count]', bulkBar);
            if (n) n.textContent = sel.length + ' selected';
        }
        if (selectAll) {
            selectAll.checked = visible.length > 0 && sel.length === visible.length;
            selectAll.indeterminate = sel.length > 0 && sel.length < visible.length;
        }
    }

    /** Swap a card for the server's fresh markup (selection kept). */
    function replaceCard(card, html, focusSel) {
        const t = doc.createElement('template');
        t.innerHTML = String(html || '').trim();
        const fresh = t.content.firstElementChild;
        if (!fresh) return card;
        const wasSel = $('[data-pa-select]', card).checked;
        card.replaceWith(fresh);
        if (wasSel) $('[data-pa-select]', fresh).checked = true;
        refreshAll();
        if (focusSel) { const el = $(focusSel, fresh); if (el) el.focus(); }
        return fresh;
    }

    function setActive(card, on) {
        card.dataset.active = on ? '1' : '0';
        card.classList.toggle('is-off', !on);
        const cb = $('[data-pa-toggle]', card);
        if (cb) cb.checked = on;
        const tx = $('[data-pa-switch-text]', card);
        if (tx) tx.textContent = on ? 'On website' : 'Hidden';
        const sw = $('.pa-switch', card);
        if (sw) sw.title = on ? 'Click to hide this partner from the website' : 'Click to show this partner on the website';
    }

    function busy(card, on) {
        const b = $('[data-pa-busy]', card);
        if (b) b.hidden = !on;
        card.classList.toggle('is-busy', on);
        card.setAttribute('aria-busy', on ? 'true' : 'false');
    }

    /** The page was changed by someone else (a card is gone): reload the list without reloading the page. */
    async function refreshGrid(message) {
        try {
            const res = await fetch(window.location.href, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
            if (!res.ok) throw new Error('x');
            const parsed = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = $('[data-pa-grid]', parsed);
            if (!fresh) throw new Error('x');
            grid.replaceChildren(...Array.from(fresh.children).map((n) => doc.importNode(n, true)));
            const po = $('[data-pa-optimise]', parsed);
            const here = $('[data-pa-optimise]', root);
            if (po && here) {
                here.hidden = po.hidden;
                const tt = $('[data-pa-optimise-title]', po);
                const ht = $('[data-pa-optimise-title]', here);
                if (tt && ht) ht.textContent = tt.textContent;
            }
            refreshAll();
            if (message) toast(message, { tone: 'info' });
        } catch (_) {
            toast('Could not refresh the list. Reload the page.', { tone: 'error' });
        }
    }

    function afterMeta(meta) {
        if (!meta) return;
        const box = $('[data-pa-optimise]', root);
        if (box && typeof meta.pending_logos === 'number') {
            box.hidden = meta.pending_logos === 0;
            const t = $('[data-pa-optimise-title]', box);
            if (t) t.textContent = plural(meta.pending_logos, 'logo', 'logos') + ' not optimised yet';
        }
    }

    function logoWarnings(data, name) {
        const w = Array.isArray(data.warnings) ? data.warnings : [];
        if (data.notice) toast(String(data.notice), { tone: 'warning', timeout: 14000 });
        else if (w.includes('low_resolution')) toast('The logo of “' + name + '” is small, so it may look soft on high-resolution screens. A PNG at least 400 px wide gives the sharpest result.', { tone: 'warning', timeout: 12000 });
    }

    // ------------------------------------------------------------------------------------------------ 5. quick add
    const quick = $('form[data-pa-quick]', root);
    if (quick) {
        const zone = $('[data-pa-drop]', quick);
        const drop = zone ? zone.paDrop : null;
        const nameIn = $('[name="name"]', quick);
        const btn = $('[data-pa-add]', quick);
        const btnLabel = $('[data-pa-add-label]', quick);
        const clearErrors = () => {
            $$('[data-pa-error]', quick).forEach((n) => { n.hidden = true; n.textContent = ''; });
            $$('[aria-invalid]', quick).forEach((n) => n.removeAttribute('aria-invalid'));
        };
        const showErrors = (errors) => {
            let first = null;
            Object.entries(errors || {}).forEach(([field, msg]) => {
                if (field === 'logo') { if (drop) drop.say(String(msg), 'bad'); first = first || $('[data-pa-drop-input]', quick); return; }
                const n = $('[data-pa-error="' + field + '"]', quick);
                const input = $('[name="' + field + '"]', quick);
                if (n) { n.textContent = String(msg); n.hidden = false; }
                if (input) { input.setAttribute('aria-invalid', 'true'); first = first || input; }
            });
            if (first) first.focus();
        };
        // a picture can be pasted too (Ctrl+V on the form)
        quick.addEventListener('paste', (e) => {
            const f = e.clipboardData && e.clipboardData.files && e.clipboardData.files[0];
            if (f && drop && f.type.startsWith('image/')) { e.preventDefault(); drop.set(f.name ? f : new File([f], 'pasted-logo.png', { type: f.type })); }
        });
        // typing the name of a partner that is already in the list: gentle warning (not blocking)
        nameIn.addEventListener('input', () => {
            const v = nameIn.value.trim().toLowerCase();
            const dup = v !== '' && cards().some((c) => nameOf(c).trim().toLowerCase() === v);
            const n = $('[data-pa-error="name"]', quick);
            if (dup) { n.textContent = 'A partner with this name is already in the list.'; n.hidden = false; n.classList.add('is-soft'); }
            else if (n.classList.contains('is-soft')) { n.hidden = true; n.textContent = ''; n.classList.remove('is-soft'); }
        });

        quick.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (btn.disabled) return;
            clearErrors();
            const name = nameIn.value.trim();
            if (name === '') { showErrors({ name: 'Enter the organisation’s name.' }); return; }
            const fd = new FormData(quick);
            fd.delete('_token');
            const file = drop ? drop.file : null;
            fd.delete('logo');
            if (file) fd.append('logo', file, file.name);
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
            btnLabel.textContent = file ? 'Optimising the logo…' : 'Adding…';
            const r = await call('POST', '', fd);
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            btnLabel.textContent = 'Add partner';
            if (!r.ok) {
                if (r.status === 422 && r.data.errors) showErrors(r.data.errors); else toast(why(r), { tone: 'error' });
                return;
            }
            // reset the filter so the new card is visible, then add it at the end
            query = ''; statusFilter = 'all';
            if (searchEl) searchEl.value = '';
            $$('[data-pa-filter]', root).forEach((b) => { const on = b.dataset.paFilter === 'all'; b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
            const t = doc.createElement('template');
            t.innerHTML = String(r.data.html || '').trim();
            const card = t.content.firstElementChild;
            if (card) {
                card.classList.add('is-new');
                card.addEventListener('animationend', () => card.classList.remove('is-new'), { once: true });
                grid.appendChild(card);
                refreshAll();
                card.scrollIntoView({ block: 'nearest', behavior: reduced ? 'auto' : 'smooth' });
            }
            const item = r.data.data || {};
            const typeKeep = $('[name="partner_type"]', quick).value;
            quick.reset();
            $('[name="partner_type"]', quick).value = typeKeep;
            if (drop) drop.clear();
            nameIn.focus();
            // a new partner goes to the end of the list: say where it really shows (About and the footer only show the first few)
            const m = r.data.meta || {};
            let where = 'It is on the website now.';
            let href = aboutUrl;
            if (item.is_active && m.active > m.about_limit) {
                where = 'It is on the Partners page. The About page shows the first ' + m.about_limit + (m.active > m.footer_limit ? ' and the footer the first ' + m.footer_limit : '') + ' partners.';
                href = partnersUrl;
            } else if (item.is_active && m.active > m.footer_limit) {
                where = 'It is on the About page and the Partners page. The footer shows the first ' + m.footer_limit + ' partners.';
            }
            toast('Added “' + item.name + '”. ' + (item.is_active ? where : 'It is hidden until you switch it on.'),
                item.is_active && href ? { tone: 'success', href: href, actionLabel: 'See it', timeout: where === 'It is on the website now.' ? undefined : 12000 } : { tone: 'success' });
            announce('Added ' + item.name);
            logoWarnings(r.data, item.name);
            afterMeta(r.data.meta);
        });
    }

    // ------------------------------------------------------------------------------------------------ 6. card actions
    const replaceInput = $('[data-pa-replace-input]', root);
    let replaceTarget = null;

    async function uploadLogo(card, file) {
        const problem = checkFile(file);
        if (problem) { toast(problem, { tone: 'error' }); return; }
        const name = nameOf(card);
        busy(card, true);
        const fd = new FormData();
        fd.append('logo', file, file.name);
        const r = await call('POST', '/' + card.dataset.id + '/logo', fd);
        busy(card, false);
        if (!r.ok) {
            if (r.status === 404) { refreshGrid('That partner no longer exists, so the list was refreshed.'); return; }
            toast(why(r), { tone: 'error' });
            return;
        }
        replaceCard(card, r.data.html, '[data-pa-replace]');
        toast('The logo of “' + name + '” was updated.', { tone: 'success' });
        announce('Logo updated for ' + name);
        logoWarnings(r.data, name);
        afterMeta(r.data.meta);
    }

    async function removeLogo(card) {
        const name = nameOf(card);
        const pv = $('[data-pa-logo]', card).cloneNode(true);
        $$('[data-pa-busy], .pa-card__drop', pv).forEach((n) => n.remove());
        const choice = await ask({ title: 'Remove the logo of “' + name + '”?', text: 'The website will show the name as text instead. The picture file is deleted from the server.', ok: 'Remove logo', okClass: 'btn--primary', preview: pv });
        if (choice !== 'ok') return;
        busy(card, true);
        const r = await call('POST', '/' + card.dataset.id + '/logo/remove');
        busy(card, false);
        if (!r.ok) { toast(why(r), { tone: 'error' }); return; }
        replaceCard(card, r.data.html, '[data-pa-replace]');
        toast('Logo removed. The website now shows “' + name + '” as text.', { tone: 'success' });
        afterMeta(r.data.meta);
    }

    async function toggle(card, on) {
        const name = nameOf(card);
        const before = card.dataset.active === '1';
        setActive(card, on);
        updateCounts();
        const r = await call('POST', '/' + card.dataset.id + '/toggle', { is_active: on });
        if (!r.ok) {
            setActive(card, before);
            updateCounts();
            if (r.status === 404) { refreshGrid('That partner no longer exists, so the list was refreshed.'); return false; }
            toast(why(r), { tone: 'error' });
            return false;
        }
        announce(name + (on ? ' is now on the website.' : ' is now hidden from the website.'));
        if (filtered) applyFilter();
        return true;
    }

    async function remove(card) {
        const name = nameOf(card);
        const isOn = card.dataset.active === '1';
        const pv = $('[data-pa-logo]', card).cloneNode(true);
        $$('[data-pa-busy], .pa-card__drop', pv).forEach((n) => n.remove());
        const choice = await ask({
            title: 'Delete “' + name + '”?',
            text: 'It disappears from the website and its logo file is deleted from the server. This cannot be undone.' + (isOn ? ' Not sure? Hide it instead: it stays here, but visitors no longer see it.' : ''),
            ok: 'Delete partner',
            alt: isOn ? 'Hide instead' : '',
            preview: pv,
        });
        if (choice === 'alt') {
            if (await toggle(card, false)) toast('“' + name + '” is hidden from the website. It is still in the list.', { tone: 'success' });
            return;
        }
        if (choice !== 'ok') return;
        busy(card, true);
        const r = await call('POST', '/' + card.dataset.id + '/delete');
        if (!r.ok && r.status !== 404) { busy(card, false); toast(why(r), { tone: 'error' }); return; }
        const next = card.nextElementSibling || card.previousElementSibling;
        card.remove();
        refreshAll();
        const focusEl = next ? $('[data-pa-name]', next) : $('#pa-quick-name');
        if (focusEl) focusEl.focus();
        const files = r.data && r.data.files_removed;
        toast('Removed “' + name + '”. ' + (files ? 'The logo file was deleted.' : 'Nothing else is left behind.'), { tone: 'success' });
        announce(name + ' was deleted');
        afterMeta(r.data && r.data.meta);
    }

    function openEdit(card, open) {
        const form = $('[data-pa-edit-form]', card);
        const btn = $('[data-pa-edit]', card);
        if (!form) return;
        form.hidden = !open;
        card.classList.toggle('is-editing', open);
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { const f = $('[name="name"]', form); f.focus(); f.select(); }
        else if (btn) btn.focus();
    }

    async function saveEdit(card, form) {
        $$('[data-pa-error]', form).forEach((n) => { n.hidden = true; n.textContent = ''; });
        $$('[aria-invalid]', form).forEach((n) => n.removeAttribute('aria-invalid'));
        const body = { name: $('[name="name"]', form).value, website: $('[name="website"]', form).value, partner_type: $('[name="partner_type"]', form).value };
        const submit = $('button[type="submit"]', form);
        submit.disabled = true;
        const r = await call('POST', '/' + card.dataset.id, body);
        submit.disabled = false;
        if (!r.ok) {
            if (r.status === 422 && r.data.errors) {
                let first = null;
                Object.entries(r.data.errors).forEach(([field, msg]) => {
                    const n = $('[data-pa-error="' + field + '"]', form);
                    const input = $('[name="' + field + '"]', form);
                    if (n) { n.textContent = String(msg); n.hidden = false; }
                    if (input) { input.setAttribute('aria-invalid', 'true'); first = first || input; }
                });
                if (first) first.focus();
            } else if (r.status === 404) { refreshGrid('That partner no longer exists, so the list was refreshed.'); }
            else toast(why(r), { tone: 'error' });
            return;
        }
        replaceCard(card, r.data.html, '[data-pa-edit]');
        toast('Saved “' + (r.data.data || {}).name + '”.', { tone: 'success' });
    }

    grid.addEventListener('click', (e) => {
        const t = e.target;
        const card = t.closest('[data-pa-card]');
        if (!card) return;
        if (t.closest('[data-pa-edit]')) { openEdit(card, $('[data-pa-edit-form]', card).hidden); return; }
        if (t.closest('[data-pa-edit-cancel]')) { openEdit(card, false); return; }
        if (t.closest('[data-pa-replace]')) { replaceTarget = card; replaceInput.value = ''; replaceInput.click(); return; }
        if (t.closest('[data-pa-remove-logo]')) { removeLogo(card); return; }
        if (t.closest('[data-pa-delete]')) { if (canDelete) remove(card); return; }
        const mv = t.closest('[data-pa-move]');
        if (mv) { moveCard(card, mv.dataset.paMove === 'earlier' ? -1 : 1, mv); }
    });
    grid.addEventListener('change', (e) => {
        const t = e.target;
        const card = t.closest('[data-pa-card]');
        if (!card) return;
        if (t.matches('[data-pa-toggle]')) toggle(card, t.checked);
        else if (t.matches('[data-pa-select]')) updateBulk();
    });
    grid.addEventListener('submit', (e) => {
        const form = e.target.closest('[data-pa-edit-form]');
        if (!form) return;
        e.preventDefault();
        saveEdit(form.closest('[data-pa-card]'), form);
    });
    grid.addEventListener('keydown', (e) => {
        const card = e.target.closest ? e.target.closest('[data-pa-card]') : null;
        if (!card) return;
        if (e.key === 'Escape' && card.classList.contains('is-editing')) { e.preventDefault(); openEdit(card, false); return; }
        // the handle is also a keyboard control: arrow keys move the card
        const handle = e.target.closest('[data-pa-handle]');
        if (handle && ['ArrowLeft', 'ArrowUp', 'ArrowRight', 'ArrowDown'].includes(e.key)) {
            e.preventDefault();
            moveCard(card, e.key === 'ArrowLeft' || e.key === 'ArrowUp' ? -1 : 1, handle);
        }
    });
    if (replaceInput) replaceInput.addEventListener('change', () => {
        const f = replaceInput.files && replaceInput.files[0];
        if (f && replaceTarget && replaceTarget.isConnected) uploadLogo(replaceTarget, f);
        replaceInput.value = '';
    });

    // a picture dragged onto a card replaces its logo; a picture dropped anywhere else must not make the browser open it
    let dropCard = null;
    grid.addEventListener('dragover', (e) => {
        if (!hasFiles(e)) return;
        const card = e.target.closest('[data-pa-card]');
        e.preventDefault();
        e.dataTransfer.dropEffect = card ? 'copy' : 'none';
        if (dropCard && dropCard !== card) dropCard.classList.remove('is-drop');
        dropCard = card;
        if (card) card.classList.add('is-drop');
    });
    grid.addEventListener('dragleave', (e) => {
        if (dropCard && !dropCard.contains(e.relatedTarget)) { dropCard.classList.remove('is-drop'); dropCard = null; }
    });
    grid.addEventListener('drop', (e) => {
        if (!hasFiles(e)) return;
        e.preventDefault();
        const card = e.target.closest('[data-pa-card]');
        if (dropCard) { dropCard.classList.remove('is-drop'); dropCard = null; }
        const f = e.dataTransfer.files && e.dataTransfer.files[0];
        if (card && f) uploadLogo(card, f);
    });
    window.addEventListener('dragover', (e) => { if (hasFiles(e) && !e.target.closest('[data-pa-drop], [data-pa-grid]')) { e.preventDefault(); e.dataTransfer.dropEffect = 'none'; } });
    window.addEventListener('drop', (e) => { if (hasFiles(e) && !e.target.closest('[data-pa-drop], [data-pa-grid]')) e.preventDefault(); });

    // ------------------------------------------------------------------------------------------------ 7. order (drag and drop + buttons, saved automatically)
    let saveTimer = 0;
    let saving = false;
    let dirty = false;
    let statusTimer = 0;
    function orderSay(msg, bad) {
        if (!orderStatus) return;
        orderStatus.textContent = msg;
        orderStatus.classList.toggle('is-bad', !!bad);
        window.clearTimeout(statusTimer);
        if (msg && !bad) statusTimer = window.setTimeout(() => { orderStatus.textContent = ''; }, 5000);
    }
    function queueSave() {
        dirty = true;
        orderSay('Saving the new order…');
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(() => flushSave(), 600);
    }
    async function flushSave(opts = {}) {
        window.clearTimeout(saveTimer);
        if (!dirty) return;
        if (saving) { saveTimer = window.setTimeout(() => flushSave(), 300); return; }
        dirty = false;
        saving = true;
        const ids = cards().map((c) => Number(c.dataset.id));
        const r = await call('POST', '/reorder', { ids }, opts);
        saving = false;
        if (r.ok) { orderSay('Order saved. The website shows partners in this order.'); return; }
        dirty = true;
        orderSay('The new order could not be saved.', true);
        toast(why(r), { tone: 'error', actionLabel: 'Try again', action: () => flushSave() });
    }
    // never lose a change when the tab is closed right after a move
    doc.addEventListener('visibilitychange', () => { if (doc.visibilityState === 'hidden' && dirty) flushSave({ keepalive: true }); });
    window.addEventListener('pagehide', () => { if (dirty) flushSave({ keepalive: true }); });

    function moveCard(card, step, btn) {
        if (filtered) { toast('Clear the search or the filter first: the order can only be changed while every partner is shown.', { tone: 'warning' }); return; }
        const list = cards();
        const i = list.indexOf(card);
        const j = i + step;
        if (i < 0 || j < 0 || j >= list.length) return;
        if (step < 0) grid.insertBefore(card, list[j]); else grid.insertBefore(card, list[j].nextSibling);
        renumber();
        // the button that was used may now be disabled (card at the edge): keep the focus on a working one
        let target = btn && !btn.disabled ? btn : $('[data-pa-move="' + (step < 0 ? 'later' : 'earlier') + '"]', card);
        if (btn && btn.matches('[data-pa-handle]')) target = btn;
        if (target) target.focus();
        announce(nameOf(card) + ' moved to position ' + (j + 1) + ' of ' + list.length + '.');
        queueSave();
    }

    // drag and drop: press the handle, drag the card, the others make room as you go
    let dragging = null;
    let startOrder = '';
    let lastKey = '';
    grid.addEventListener('pointerdown', (e) => {
        const h = e.target.closest('[data-pa-handle]');
        if (h && e.pointerType !== 'touch') h.closest('[data-pa-card]').draggable = true;
    });
    grid.addEventListener('pointerup', () => { cards().forEach((c) => { if (!dragging) c.draggable = false; }); });
    grid.addEventListener('dragstart', (e) => {
        const card = e.target.closest ? e.target.closest('[data-pa-card]') : null;
        if (!card) return;
        if (e.target !== card || !card.draggable) { if (!hasFiles(e)) e.preventDefault(); return; }     // a logo picture or a link must not be dragged out of the page
        if (filtered) { e.preventDefault(); toast('Clear the search or the filter first: the order can only be changed while every partner is shown.', { tone: 'warning' }); return; }
        dragging = card;
        startOrder = cards().map((c) => c.dataset.id).join(',');
        lastKey = '';
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', card.dataset.id); } catch (_) { /* IE */ }
        window.setTimeout(() => card.classList.add('is-dragging'), 0);
        grid.classList.add('is-sorting');
    });
    grid.addEventListener('dragover', (e) => {
        if (!dragging || hasFiles(e)) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const over = e.target.closest('[data-pa-card]');
        if (!over || over === dragging || over.hidden) return;
        const r = over.getBoundingClientRect();
        const after = (e.clientX - r.left) > r.width / 2 || (e.clientY - r.top) > r.height * 0.75;
        const key = over.dataset.id + (after ? 'a' : 'b');
        if (key === lastKey) return;
        lastKey = key;
        const ref = after ? over.nextSibling : over;
        if (ref !== dragging && ref !== dragging.nextSibling) grid.insertBefore(dragging, ref);
    });
    grid.addEventListener('drop', (e) => { if (dragging && !hasFiles(e)) e.preventDefault(); });
    grid.addEventListener('dragend', () => {
        if (!dragging) return;
        const card = dragging;
        dragging = null;
        card.classList.remove('is-dragging');
        card.draggable = false;
        grid.classList.remove('is-sorting');
        renumber();
        const now = cards().map((c) => c.dataset.id).join(',');
        if (now !== startOrder) {
            const i = cards().indexOf(card);
            announce(nameOf(card) + ' moved to position ' + (i + 1) + ' of ' + cards().length + '.');
            queueSave();
        }
    });

    // ------------------------------------------------------------------------------------------------ 8. toolbar, selection, bulk actions
    if (searchEl) searchEl.addEventListener('input', () => { query = searchEl.value; applyFilter(); });
    $$('[data-pa-filter]', root).forEach((b) => b.addEventListener('click', () => {
        statusFilter = b.dataset.paFilter;
        $$('[data-pa-filter]', root).forEach((x) => { const on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        applyFilter();
    }));
    const resetFilter = $('[data-pa-filter-reset]', root);
    if (resetFilter) resetFilter.addEventListener('click', () => {
        query = ''; statusFilter = 'all';
        if (searchEl) { searchEl.value = ''; searchEl.focus(); }
        $$('[data-pa-filter]', root).forEach((x) => { const on = x.dataset.paFilter === 'all'; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        applyFilter();
    });
    if (selectAll) selectAll.addEventListener('change', () => {
        cards().filter((c) => !c.hidden).forEach((c) => { $('[data-pa-select]', c).checked = selectAll.checked; });
        updateBulk();
    });
    const clearSel = $('[data-pa-bulk-clear]', root);
    if (clearSel) clearSel.addEventListener('click', () => { cards().forEach((c) => { $('[data-pa-select]', c).checked = false; }); updateBulk(); });

    $$('[data-pa-bulk-action]', root).forEach((b) => b.addEventListener('click', async () => {
        const action = b.dataset.paBulkAction;
        const sel = selected();
        if (!sel.length) return;
        const ids = sel.map((c) => Number(c.dataset.id));
        const n = ids.length;
        if (action === 'delete') {
            if (!canDelete) return;
            const choice = await ask({
                title: 'Delete ' + plural(n, 'partner', 'partners') + '?',
                text: n === 1 ? '“' + nameOf(sel[0]) + '” disappears from the website and its logo file is deleted. This cannot be undone.' : 'They disappear from the website and their logo files are deleted. This cannot be undone. Not sure? Hide them instead.',
                ok: 'Delete ' + plural(n, 'partner', 'partners'),
                alt: 'Hide instead',
            });
            if (choice === 'cancel') return;
            if (choice === 'alt') { bulk('hide', sel, ids); return; }
        }
        bulk(action, sel, ids);
    }));

    async function bulk(action, sel, ids) {
        const n = ids.length;
        sel.forEach((c) => busy(c, true));
        const r = await call('POST', '/bulk', { action, ids });
        sel.forEach((c) => busy(c, false));
        if (!r.ok) {
            if (r.status === 404) { refreshGrid('Those partners no longer exist, so the list was refreshed.'); return; }
            toast(why(r), { tone: 'error' });
            return;
        }
        const done = (r.data.ids || ids).map(String);
        if (action === 'delete') {
            sel.filter((c) => done.includes(c.dataset.id)).forEach((c) => c.remove());
            refreshAll();
            toast('Removed ' + plural(n, 'partner', 'partners') + '. ' + (r.data.files_removed ? 'Their logo files were deleted.' : ''), { tone: 'success' });
            const first = cards()[0];
            const f = first ? $('[data-pa-name]', first) : $('#pa-quick-name');
            if (f) f.focus();
        } else {
            const on = action === 'show';
            sel.filter((c) => done.includes(c.dataset.id)).forEach((c) => { setActive(c, on); $('[data-pa-select]', c).checked = false; });
            refreshAll();
            toast(plural(n, 'partner is', 'partners are') + (on ? ' now on the website.' : ' now hidden from the website.'), { tone: 'success' });
        }
        announce('Done.');
        afterMeta(r.data.meta);
    }

    // ------------------------------------------------------------------------------------------------ 9. optimise existing logos
    const optRun = $('[data-pa-optimise-run]', root);
    if (optRun) optRun.addEventListener('click', async () => {
        const progress = $('[data-pa-optimise-progress]', root);
        optRun.disabled = true;
        optRun.setAttribute('aria-busy', 'true');
        let after = 0;
        let done = 0;
        let failed = 0;
        let notice = null;
        const errors = {};
        for (let guard = 0; guard < 400; guard++) {
            const r = await call('POST', '/optimise', { after, limit: 4 });
            if (!r.ok) { toast(why(r), { tone: 'error' }); break; }
            done += Number(r.data.done || 0);
            failed += Number(r.data.failed || 0);
            Object.assign(errors, r.data.errors || {});
            after = Number(r.data.last_id || after);
            if (r.data.notice) { notice = String(r.data.notice); break; }
            if (progress) progress.textContent = 'Optimised ' + done + (failed ? ', skipped ' + failed : '') + (r.data.remaining ? ' … ' + r.data.remaining + ' to go' : '');
            if (!r.data.remaining) break;
        }
        optRun.disabled = false;
        optRun.removeAttribute('aria-busy');
        if (notice) { toast(notice, { tone: 'warning', timeout: 15000 }); if (progress) progress.textContent = ''; return; }
        const msgs = Object.values(errors).map(String);
        toast('Optimised ' + plural(done, 'logo', 'logos') + (failed ? '. ' + plural(failed, 'logo was', 'logos were') + ' skipped and left as they were' + (msgs.length ? ': ' + msgs.slice(0, 2).join(' ') : '.') : '. All logos now look as sharp and tidy as new uploads.'),
            { tone: failed ? 'warning' : 'success', timeout: failed ? 16000 : 8000 });
        if (progress) progress.textContent = '';
        await refreshGrid();
        announce('Existing logos optimised.');
    });

    // ------------------------------------------------------------------------------------------------ 10. start-up
    refreshAll();
})();
