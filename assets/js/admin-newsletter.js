/*!
 * admin-newsletter.js - Admin > Newsletter > editor (OWNER: EDITOR agent). Contract: docs/NEWSLETTER.md section 7.
 * Dependency-free ES2020, no inline code (CSP). Talks to the JSON API under /admin/api/newsletters (session cookie + X-CSRF-TOKEN header).
 *
 * The page (views/admin/newsletters/edit.php) embeds the issue as a JSON data block (#nl-boot) and the markup of the five block types as
 * <template data-block-template="article|image|quote|button|divider">. This script builds the block list from them. The DOM is the single
 * source of truth: collect() reads the fields in their on-screen order, so re-ordering is just moving elements.
 *
 * Features: structured blocks (add / remove / duplicate / drag and drop / Move up + down), rich text through window.LuminaEditor, picture
 * upload with progress, live preview rendered by the SERVER (debounced POST /preview into an iframe srcdoc; desktop and phone width; click a
 * part of the preview to edit it), autosave every 30 s for drafts (PUT with the version number = optimistic locking, conflict dialog),
 * a safety copy in this browser for live issues and when offline, publish / schedule / unpublish / archive / delete / duplicate with
 * confirmation, a History drawer (revisions, differences, restore), error messages next to the fields, keyboard and screen reader support.
 *
 * Sections: 1 helpers + API, 2 state and field access, 3 images and links, 4 blocks, 5 saving, 6 preview, 7 status and actions,
 * 8 history, 9 start-up.
 */
(function () {
    'use strict';

    const doc = document;
    const root = doc.querySelector('[data-nl-editor]');
    if (!root) {
        return;                                   // other newsletter admin pages need no script
    }
    const $ = (sel, scope = root) => scope.querySelector(sel);
    const $$ = (sel, scope = root) => Array.from(scope.querySelectorAll(sel));

    let boot;
    try {
        boot = JSON.parse(doc.getElementById('nl-boot').textContent);
    } catch (err) {
        root.insertAdjacentHTML('afterbegin', '<div class="alert alert--error" role="alert"><div class="alert__body">The editor could not start. Please reload the page.</div></div>');
        return;
    }

    const AUTOSAVE_MS = 30000;
    const PREVIEW_MS = 700;
    const DIRTY_MS = 250;
    const BLOCK_TYPES = { article: 'Article', image: 'Image', quote: 'Quote', button: 'Button', divider: 'Divider' };
    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const smooth = reduced ? 'auto' : 'smooth';
    const baseUrl = () => String((doc.querySelector('meta[name="base-url"]') || {}).content || '').replace(/\/+$/, '');

    // ------------------------------------------------------------------------------------------------ 1. helpers
    function el(tag, attrs, ...kids) {
        const n = doc.createElement(tag);
        for (const [k, v] of Object.entries(attrs || {})) {
            if (v === false || v === null || v === undefined) continue;
            if (k === 'text') n.textContent = String(v);
            else n.setAttribute(k, v === true ? '' : String(v));
        }
        for (const kid of kids.flat()) {
            if (kid === null || kid === undefined || kid === false) continue;
            n.appendChild(typeof kid === 'string' ? doc.createTextNode(kid) : kid);
        }
        return n;
    }
    const debounce = (fn, ms) => { let t = 0; const d = (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; d.cancel = () => clearTimeout(t); return d; };
    const uid = () => {
        const b = new Uint8Array(4);
        (window.crypto || window.msCrypto).getRandomValues(b);
        return 'b' + Array.from(b, (x) => x.toString(16).padStart(2, '0')).join('');
    };
    const pad = (n) => String(n).padStart(2, '0');
    const clock = (d) => pad(d.getHours()) + ':' + pad(d.getMinutes());
    const plain = (html) => String(html || '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/\s+/g, ' ').trim();
    const clip = (s, n) => (s.length > n ? s.slice(0, n - 1).trimEnd() + '…' : s);

    /** "2026-11-03 09:30:00" or "2026-11-03T09:30" -> Date built from the parts (no time zone shifting: the values are Kampala wall-clock times). */
    function parseWall(v) {
        const m = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}))?/.exec(String(v || ''));
        return m ? new Date(+m[1], +m[2] - 1, +m[3], +(m[4] || 0), +(m[5] || 0)) : null;
    }
    function fmtWall(v, withTime = true) {
        const d = parseWall(v);
        if (!d) return '';
        const day = d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        return withTime ? day + ', ' + clock(d) : day;
    }
    function ago(v) {
        const d = parseWall(v);
        if (!d) return '';
        const today = new Date();
        const sameDay = d.toDateString() === today.toDateString();
        const yest = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 1);
        if (sameDay) return 'Today, ' + clock(d);
        if (d.toDateString() === yest.toDateString()) return 'Yesterday, ' + clock(d);
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + clock(d);
    }

    const announceEl = $('[data-announce]');
    function announce(msg) {
        if (!announceEl) return;
        announceEl.textContent = '';
        setTimeout(() => { announceEl.textContent = msg; }, 40);
    }
    const toastsEl = $('[data-toasts]');
    function toast(msg, opts = {}) {
        const t = el('div', { class: 'nl-toast nl-toast--' + (opts.tone || 'info') });
        t.appendChild(el('span', { class: 'nl-toast__text', text: msg }));
        if (opts.href || opts.action) {
            const a = opts.href ? el('a', { class: 'nl-toast__action', href: opts.href, text: opts.actionLabel || 'Open' }) : el('button', { type: 'button', class: 'nl-toast__action', text: opts.actionLabel || 'Undo' });
            if (opts.action) a.addEventListener('click', () => { opts.action(); t.remove(); });
            if (opts.href && opts.target) { a.target = opts.target; a.rel = 'noopener'; }
            t.appendChild(a);
        }
        const close = el('button', { type: 'button', class: 'nl-toast__close', 'aria-label': 'Dismiss message', text: '×' });
        close.addEventListener('click', () => t.remove());
        t.appendChild(close);
        toastsEl.appendChild(t);
        const ms = opts.timeout === undefined ? (opts.tone === 'error' ? 12000 : 6000) : opts.timeout;
        if (ms > 0) setTimeout(() => t.remove(), ms);
        while (toastsEl.children.length > 4) toastsEl.firstElementChild.remove();
        return t;
    }

    // ---- API: JSON in, JSON out, CSRF header, one automatic token refresh
    const csrf = () => (window.LuminaAdmin && window.LuminaAdmin.csrf ? window.LuminaAdmin.csrf() : String((doc.querySelector('meta[name="csrf-token"]') || {}).content || ''));
    async function refreshCsrf() {
        try {
            const res = await fetch(boot.urls.edit || window.location.href, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
            if (!res.ok || res.redirected && /\/admin\/login/.test(res.url)) return false;
            const html = await res.text();
            const m = /<meta name="csrf-token" content="([^"]+)"/.exec(html);
            if (!m) return false;
            const meta = doc.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', m[1].replace(/&amp;/g, '&'));
            return true;
        } catch (_) { return false; }
    }
    /** @returns {Promise<{status?:number, ok?:boolean, data?:any, network?:boolean, aborted?:boolean, login?:boolean}>} */
    async function api(method, path, body, opts = {}) {
        const send = async () => {
            const payload = body === undefined ? undefined : JSON.stringify(body);
            const res = await fetch(boot.api + path, {
                method, credentials: 'same-origin', signal: opts.signal,
                keepalive: !!opts.keepalive && payload !== undefined && payload.length < 50000,
                headers: Object.assign({ Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' }, payload !== undefined ? { 'Content-Type': 'application/json' } : {}),
                body: payload,
            });
            let data = null;
            try { data = await res.json(); } catch (_) { data = null; }
            return { status: res.status, ok: res.ok, data };
        };
        try {
            let r = await send();
            if (r.status === 403 && r.data && (r.data.status === 419 || /expired/i.test(String(r.data.error || ''))) && !opts.noRetry) {
                if (await refreshCsrf()) r = await send(); else r.login = true;
            }
            if (r.status === 401) r.login = true;
            return r;
        } catch (err) {
            if (err && err.name === 'AbortError') return { aborted: true };
            return { network: true };
        }
    }

    // ---- dialogs (native <dialog>)
    let lastFocus = null;
    const dialog = (name) => $('dialog[data-dialog="' + name + '"]');
    function showDialog(d) {
        lastFocus = doc.activeElement;
        if (typeof d.showModal === 'function') d.showModal(); else d.setAttribute('open', '');
        d.addEventListener('close', () => { if (lastFocus && lastFocus.isConnected && typeof lastFocus.focus === 'function') lastFocus.focus({ preventScroll: true }); }, { once: true });
    }
    /** Yes / no question. body = string (paragraphs split on blank lines) or a Node. Resolves true when confirmed. */
    function confirmDialog({ title, body, okLabel = 'OK', cancelLabel = 'Cancel', tone = 'primary' }) {
        const d = dialog('confirm');
        if (typeof d.showModal !== 'function') return Promise.resolve(window.confirm(title + '\n\n' + (typeof body === 'string' ? body : (body && body.textContent) || '')));
        $('[data-confirm-title]', d).textContent = title;
        const b = $('[data-confirm-body]', d);
        b.textContent = '';
        if (typeof body === 'string') String(body).split(/\n\n+/).forEach((p) => b.appendChild(el('p', { text: p })));
        else if (body) b.appendChild(body);
        const ok = $('[data-confirm-ok]', d);
        ok.textContent = okLabel;
        ok.className = 'btn ' + ({ danger: 'btn--danger', cta: 'btn--cta', primary: 'btn--primary' }[tone] || 'btn--primary');
        $('[data-confirm-cancel]', d).textContent = cancelLabel;
        return new Promise((resolve) => {
            d.returnValue = '';
            d.addEventListener('close', () => resolve(d.returnValue === 'ok'), { once: true });
            showDialog(d);
        });
    }
    // closing a dialog by a click on its backdrop
    root.addEventListener('click', (e) => {
        const d = e.target;
        if (d instanceof HTMLDialogElement && d.open && d.hasAttribute('data-backdrop-close')) {
            const r = d.getBoundingClientRect();
            if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) d.close('backdrop');
        }
    });

    // ------------------------------------------------------------------------------------------------ 2. state and field access
    const S = {
        id: boot.item.id,
        version: boot.item.version,
        item: boot.item,
        saved: { d: {}, c: '' },       // what the server holds: details by key + the content JSON string
        dirty: false,
        saving: false,
        chain: Promise.resolve(),
        needsStrict: false,            // the last autosave had to clear some values: a manual save must send the content again
        autoBlocked: false,            // the last autosave failed validation; wait for another edit
        conflict: null,
        offline: false,
        lastSavedAt: null,
        lastError: '',
        guard: true,
        device: window.innerWidth < 768 ? 'mobile' : 'desktop',
        previewSeq: 0,
        previewAbort: null,
        previewStale: true,
        readiness: boot.item.readiness || { blocking: {}, warnings: {} },
        active: '',
        warned: {},
    };
    const E = {
        bar: $('[data-bar]'),
        barTitle: $('[data-bar-title]'),
        badge: $('[data-status-badge]'),
        save: $('[data-save]'),
        saveText: $('[data-save-text]'),
        banner: $('[data-banner]'),
        errors: $('[data-errors]'),
        split: $('[data-split]'),
        blocks: $('[data-blocks]'),
        empty: $('[data-blocks-empty]'),
        count: $('[data-block-count]'),
        addLimit: $('[data-add-limit]'),
        check: $('[data-check]'),
        checkTitle: $('[data-check-title]'),
        checkList: $('[data-check-list]'),
        stage: $('[data-stage]'),
        device: $('[data-device-box]'),
        previewState: $('[data-preview-state]'),
    };
    const D = {
        title: $('#nl-title'), number: $('#nl-number'), date: $('#nl-date'), summary: $('#nl-summary'),
        slug: $('#nl-slug'), seoTitle: $('#nl-seo-title'), seoDesc: $('#nl-seo-desc'),
        cover: $('[data-cover] [data-image]'),
    };
    const FEAT = $('[data-featured]');
    const FEAT_F = {
        heading: $('[data-f="heading"]', FEAT), body: $('[data-f="body_html"]', FEAT),
        image: $('[data-image]', FEAT), link: $('[data-link]', FEAT),
    };
    const blockCards = () => $$('[data-block]', E.blocks);
    const cardById = (id) => blockCards().find((c) => c.dataset.blockId === id) || null;

    const val = (input) => (input ? String(input.value || '').trim() : '');
    function setRich(ta, html) {
        ta.value = html || '';
        if (window.LuminaEditor && window.LuminaEditor.get(ta)) window.LuminaEditor.setValue(ta, ta.value);
    }
    const syncEditors = () => { if (window.LuminaEditor) window.LuminaEditor.sync(); };
    const imgBase = () => baseUrl();
    function imageUrl(path) {
        if (!path) return '';
        return path.indexOf('assets/') === 0 ? imgBase() + '/' + path : imgBase() + '/uploads/' + path;
    }

    // ---- readers (DOM -> data)
    function readImage(box) {
        const cap = $('[data-image-caption]', box);
        const path = String(($('[data-image-path]', box) || {}).value || '');
        return { path, alt: path ? val($('[data-image-alt]', box)) : '', caption: path && cap ? val(cap) : '' };
    }
    function readLink(box) {
        return { label: val($('[data-link-label]', box)), url: val($('[data-link-url]', box)) };
    }
    function collectDetails() {
        const cover = readImage(D.cover);
        const d = {
            title: val(D.title), issue_number: val(D.number), issue_date: String(D.date.value || ''), summary: val(D.summary),
            cover_image: cover.path, cover_alt: cover.alt, slug: val(D.slug), seo_title: val(D.seoTitle), seo_description: val(D.seoDesc),
        };
        return d;
    }
    function collectBlock(card) {
        const t = card.dataset.type;
        const f = (name) => val($('[data-f="' + name + '"]', card));
        const rich = (name) => String(($('[data-f="' + name + '"]', card) || {}).value || '');
        const base = { id: card.dataset.blockId, type: t };
        switch (t) {
            case 'article': return Object.assign(base, { heading: f('heading'), body_html: rich('body_html'), image: readImage($('[data-image]', card)), link: readLink($('[data-link]', card)) });
            case 'image': return Object.assign(base, { image: readImage($('[data-image]', card)) });
            case 'quote': return Object.assign(base, { body_html: rich('body_html'), cite: f('cite') });
            case 'button': return Object.assign(base, { button: { label: f('button.label'), url: f('button.url'), style: ($('[data-f="button.style"]', card) || {}).value || 'primary' } });
            default: return base;
        }
    }
    function collectContent() {
        return {
            version: 1,
            featured: { heading: val(FEAT_F.heading), body_html: String(FEAT_F.body.value || ''), image: readImage(FEAT_F.image), link: readLink(FEAT_F.link) },
            blocks: blockCards().map(collectBlock),
        };
    }
    const detailsOfItem = (item) => ({
        title: String(item.title || ''), issue_number: item.issue_number == null ? '' : String(item.issue_number), issue_date: String(item.issue_date || ''),
        summary: String(item.summary || ''), cover_image: String(item.cover_image || ''), cover_alt: item.cover_image ? String(item.cover_alt || '') : '',
        slug: String(item.slug || ''), seo_title: String(item.seo_title || ''), seo_description: String(item.seo_description || ''),
    });
    /** Everything in the form as data. sync = write the rich-text editors into their textareas first (before a save; edits do it themselves otherwise). */
    function collect(sync) {
        if (sync) syncEditors();
        const c = collectContent();
        return { d: collectDetails(), c, cj: JSON.stringify(c) };
    }

    // ---- what changed since the last save (only these keys are sent)
    function changes(cur, strict) {
        const out = {};
        let any = false;
        for (const [k, v] of Object.entries(cur.d)) {
            if (v === S.saved.d[k]) continue;
            if (k === 'slug' && v === '') continue;                    // an empty slug box means "unchanged"
            if (!strict && k === 'title' && v === '') continue;       // autosave never sends an empty title: the content is still kept
            out[k] = v;
            any = true;
        }
        if (cur.cj !== S.saved.c || S.needsStrict) {
            out.content = cur.c;
            any = true;
        }
        return any ? out : null;
    }
    function isDirty() {
        return changes(collect(), true) !== null;
    }
    function setDirty(on) {
        if (S.dirty !== on) {
            S.dirty = on;
            renderSave();
        }
    }
    const checkDirty = debounce(() => { setDirty(isDirty()); }, DIRTY_MS);

    // ---- the status line next to the title
    function autoServer() {
        return S.item.status === 'draft' || S.item.status === 'archived';
    }
    function renderSave() {
        let state = 'saved';
        let text;
        if (S.saving) { state = 'saving'; text = 'Saving...'; }
        else if (S.conflict) { state = 'error'; text = 'Not saved: somebody else changed this issue. Press Save to choose.'; }
        else if (S.offline) { state = 'error'; text = 'Offline. Your changes are kept in this browser and will be saved when you are back online.'; }
        else if (S.lastError && S.dirty) { state = 'error'; text = 'Not saved: ' + S.lastError; }
        else if (S.dirty) { state = 'dirty'; text = autoServer() ? 'Unsaved changes. They will be saved automatically in a moment.' : 'Unsaved changes. Press "Save changes" to keep them.'; }
        else if (S.lastSavedAt) { text = 'Saved at ' + clock(S.lastSavedAt); }
        else { text = 'All changes saved'; }
        E.save.dataset.state = state;
        if (E.saveText.textContent !== text) E.saveText.textContent = text;
        document.title = (S.dirty ? '• ' : '') + document.title.replace(/^• /, '');
    }

    // ---- error messages next to the fields
    function fieldWrap(input) {
        return input.closest('.field') || input.closest('.nl-image') || input.parentElement;
    }
    function showFieldError(input, msg) {
        if (!input) return;
        if (input.matches('[data-image-file]')) {
            const box = input.closest('[data-image]');
            const p = $('[data-image-error]', box);
            p.textContent = msg;
            p.hidden = false;
            return;
        }
        const wrap = fieldWrap(input);
        wrap.classList.add('field--error');
        let p = wrap.querySelector(':scope > [data-nl-error]');
        if (!p) {
            p = el('p', { class: 'field__error', 'data-nl-error': '', role: 'alert' });
            wrap.appendChild(p);
        }
        p.textContent = msg;
        input.setAttribute('aria-invalid', 'true');
        // tie the message to the field, so it is read again whenever the field is focused (not only once when it appears)
        if (!p.id) p.id = 'nl-err-' + (++errSeq);
        const ids = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        if (ids.indexOf(p.id) < 0) { ids.push(p.id); input.setAttribute('aria-describedby', ids.join(' ')); }
    }
    let errSeq = 0;
    function clearFieldError(input) {
        if (!input || !input.closest) return;
        const wrap = fieldWrap(input);
        if (!wrap) return;
        wrap.classList.remove('field--error');
        const p = wrap.querySelector(':scope > [data-nl-error]');
        if (p) {
            const rest = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter((i) => i && i !== p.id);
            if (rest.length) input.setAttribute('aria-describedby', rest.join(' ')); else input.removeAttribute('aria-describedby');
            p.remove();
        }
        input.removeAttribute('aria-invalid');
        // the red summary at the top goes away once every marked field has been corrected
        if (!E.errors.hidden && !$('[data-nl-error]')) { E.errors.hidden = true; E.errors.textContent = ''; }
    }
    function clearAllErrors() {
        $$('[aria-describedby*="nl-err-"]').forEach((i) => {
            const rest = (i.getAttribute('aria-describedby') || '').split(/\s+/).filter((t) => t && t.indexOf('nl-err-') !== 0);
            if (rest.length) i.setAttribute('aria-describedby', rest.join(' ')); else i.removeAttribute('aria-describedby');
        });
        $$('[data-nl-error]').forEach((p) => p.remove());
        $$('.field--error').forEach((f) => f.classList.remove('field--error'));
        $$('[aria-invalid]').forEach((i) => i.removeAttribute('aria-invalid'));
        $$('[data-image-error]').forEach((p) => { if (!p.dataset.upload) { p.hidden = true; p.textContent = ''; } });
        E.errors.hidden = true;
        E.errors.textContent = '';
    }

    /** Where does a server path like "blocks.2.button.url" live on screen? -> {el, card, where} */
    function fieldIn(scope, rest) {
        if (!rest) return null;
        if (rest === 'image' || rest.indexOf('image.') === 0) {
            const box = $('[data-image]', scope);
            const sub = rest.split('.')[1];
            if (!box) return null;
            return sub === 'alt' ? $('[data-image-alt]', box) : sub === 'caption' ? $('[data-image-caption]', box) : $('[data-image-file]', box);
        }
        if (rest === 'link' || rest.indexOf('link.') === 0) {
            const box = $('[data-link]', scope);
            const sub = rest.split('.')[1];
            if (!box) return null;
            return sub === 'url' ? $('[data-link-url]', box) : $('[data-link-label]', box);
        }
        return $('[data-f="' + rest + '"]', scope);
    }
    function locate(path) {
        const parts = String(path).split('.');
        const detail = { title: D.title, issue_number: D.number, issue_date: D.date, summary: D.summary, slug: D.slug, seo_title: D.seoTitle, seo_description: D.seoDesc };
        if (parts[0] === 'blocks' && /^\d+$/.test(parts[1] || '')) {
            const cards = blockCards();
            const card = cards[+parts[1]];
            if (!card) return { el: null, card: null, where: 'Sections' };
            return { el: fieldIn(card, parts.slice(2).join('.')) || $('[data-collapse-toggle]', card), card, where: 'Section ' + (+parts[1] + 1) + ' (' + BLOCK_TYPES[card.dataset.type] + ')' };
        }
        if (parts[0] === 'featured') {
            return { el: fieldIn(FEAT, parts.slice(1).join('.')) || FEAT_F.heading, card: FEAT, where: 'Featured article' };
        }
        if (parts[0] === 'cover_image') return { el: $('[data-image-file]', D.cover), card: $('[data-card="details"]'), where: 'Issue details' };
        if (parts[0] === 'cover_alt') return { el: $('[data-image-alt]', D.cover), card: $('[data-card="details"]'), where: 'Issue details' };
        if (detail[parts[0]]) return { el: detail[parts[0]], card: detail[parts[0]].closest('[data-card]'), where: parts[0].indexOf('seo') === 0 || parts[0] === 'slug' ? 'Search engines and sharing' : 'Issue details' };
        if (parts[0] === 'content') return { el: FEAT_F.heading, card: FEAT, where: 'Featured article' };
        if (parts[0] === 'blocks') return { el: $('[data-add-block]'), card: null, where: 'Sections' };
        return { el: null, card: null, where: '' };
    }
    function openCard(card) {
        if (!card) return;
        const toggle = $(':scope > .nl-card__head [data-collapse-toggle], :scope > .nl-bc__head [data-collapse-toggle]', card);
        if (toggle && toggle.getAttribute('aria-expanded') !== 'true') setOpen(card, true);
    }
    function focusEl(node) {
        if (!node) return;
        const target = node.matches('textarea[data-richtext]') && window.LuminaEditor && window.LuminaEditor.get(node) ? window.LuminaEditor.get(node) : null;
        if (target) { target.focus(); return; }
        node.focus({ preventScroll: true });
        node.scrollIntoView({ block: 'center', behavior: smooth });
    }
    /** Fill the red summary at the top, mark the fields, open the cards that hold a problem. */
    function showProblems(errors, heading) {
        clearAllErrors();
        const entries = Object.entries(errors || {});
        if (!entries.length) return;
        const list = el('ul', { class: 'nl-errors__list' });
        entries.forEach(([path, msg]) => {
            const loc = locate(path);
            if (loc.card) openCard(loc.card);
            if (loc.el && path !== 'content' && path !== 'blocks') showFieldError(loc.el, msg);
            const btn = el('button', { type: 'button', class: 'nl-linkbtn', 'data-goto': path, text: (loc.where ? loc.where + ': ' : '') + msg });
            list.appendChild(el('li', {}, btn));
        });
        E.errors.textContent = '';
        E.errors.append(el('strong', { text: heading || (entries.length === 1 ? 'One thing needs your attention' : entries.length + ' things need your attention') }), list);
        E.errors.hidden = false;
        E.errors.focus({ preventScroll: true });
        E.errors.scrollIntoView({ block: 'nearest', behavior: smooth });
    }

    // ------------------------------------------------------------------------------------------------ 3. pictures, links, counters
    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    function uploadFile(file, onProgress) {
        return new Promise((resolve, reject) => {
            const fd = new FormData();
            fd.append('file', file);
            const send = (retry) => {
                const x = new XMLHttpRequest();
                x.open('POST', boot.api + '/upload');
                x.setRequestHeader('X-CSRF-TOKEN', csrf());
                x.setRequestHeader('Accept', 'application/json');
                x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                x.upload.onprogress = (e) => { if (e.lengthComputable) onProgress(Math.round((e.loaded / e.total) * 100)); };
                x.onload = async () => {
                    let d = null;
                    try { d = JSON.parse(x.responseText); } catch (_) { d = null; }
                    if (x.status === 201 && d && d.path) { resolve(d); return; }
                    if (x.status === 403 && retry && await refreshCsrf()) { send(false); return; }
                    let msg = (d && d.errors && d.errors.file) || (d && d.message) || '';
                    if (x.status === 401 || x.status === 403) msg = 'Your session has ended. Sign in again, then upload the picture again.';
                    else if (x.status === 413) msg = 'That picture is too large (the limit is 5 MB).';
                    else if (x.status === 429) msg = 'Too many uploads at once. Wait a moment and try again.';
                    else if (!msg) msg = 'The picture could not be uploaded (error ' + x.status + '). Please try again.';
                    reject(new Error(msg));
                };
                x.onerror = () => reject(new Error('The picture could not be sent. Check your internet connection and try again.'));
                x.send(fd);
            };
            send(true);
        });
    }

    /** Set a picture component from {path, alt, caption}. url = a ready-made address for the preview picture. */
    function setImage(box, img, url) {
        const path = (img && img.path) || '';
        $('[data-image-path]', box).value = path;
        const prev = $('[data-image-preview]', box);
        const pic = $('img', prev);
        if (path) { pic.src = url || imageUrl(path); pic.hidden = false; prev.classList.remove('is-empty'); }
        else { pic.removeAttribute('src'); pic.hidden = true; prev.classList.add('is-empty'); }
        const alt = $('[data-image-alt]', box);
        const cap = $('[data-image-caption]', box);
        if (alt) alt.value = path ? (img.alt || '') : '';
        if (cap) cap.value = path ? (img.caption || '') : '';
        $('[data-image-meta]', box).hidden = !path;
        $('[data-image-remove]', box).hidden = !path;
        $('[data-image-pick-label]', box).textContent = path ? 'Replace picture' : 'Choose a picture';
        const err = $('[data-image-error]', box);
        err.hidden = true;
        err.textContent = '';
        delete err.dataset.upload;
    }
    function initImage(box) {
        if (box.dataset.ready) return;
        box.dataset.ready = '1';
        const file = $('[data-image-file]', box);
        const prog = $('[data-image-progress]', box);
        const bar = $('progress', prog);
        const ptext = $('[data-image-progress-text]', box);
        const err = $('[data-image-error]', box);
        if (!err.id) err.id = 'nl-imgerr-' + (++errSeq);                  // the picture's problem is read with the "Choose a picture" control
        { const ids = (file.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean); if (ids.indexOf(err.id) < 0) { ids.push(err.id); file.setAttribute('aria-describedby', ids.join(' ')); } }
        const showErr = (msg) => { err.textContent = msg; err.hidden = false; err.dataset.upload = '1'; };
        let busy = false;
        async function upload(f) {
            if (busy || !f) return;
            err.hidden = true;
            if (IMAGE_TYPES.indexOf(f.type) < 0) { showErr('Only JPG, PNG, GIF or WebP pictures can be used.'); return; }
            if (f.size > 5 * 1024 * 1024) { showErr('That picture is larger than 5 MB. Choose a smaller one or make it smaller first.'); return; }
            busy = true;
            const before = readImage(box);
            const local = URL.createObjectURL(f);
            const pic = $('img', $('[data-image-preview]', box));
            pic.src = local; pic.hidden = false; $('[data-image-preview]', box).classList.remove('is-empty');
            box.classList.add('is-busy');
            box.setAttribute('aria-busy', 'true');                  // (the input stays enabled: disabling the focused input would throw keyboard focus away)
            prog.hidden = false; bar.value = 0; ptext.textContent = 'Uploading... 0%';
            const replacing = before.path !== '';
            try {
                const out = await uploadFile(f, (pct) => { bar.value = pct; ptext.textContent = pct < 100 ? 'Uploading... ' + pct + '%' : 'Processing the picture...'; });
                // a description and caption written for the OLD photo would be wrong for the new one: start empty and ask for a new description
                setImage(box, { path: out.path, alt: '', caption: '' }, out.url);
                ptext.textContent = 'Picture uploaded.';
                announce(replacing
                    ? 'Picture replaced. The old description and caption were cleared. Please describe the new picture.'
                    : 'Picture uploaded. Please describe it for people who cannot see it.');
                const alt = $('[data-image-alt]', box);
                if (alt) alt.focus();
                onChanged(box);
            } catch (e) {
                setImage(box, before);
                showErr(e.message);
                file.focus();
            } finally {
                URL.revokeObjectURL(local);
                busy = false;
                box.removeAttribute('aria-busy');
                box.classList.remove('is-busy');
                setTimeout(() => { prog.hidden = true; }, 600);
            }
        }
        file.addEventListener('change', () => { const f = file.files && file.files[0]; file.value = ''; upload(f); });
        $('[data-image-remove]', box).addEventListener('click', () => {
            setImage(box, { path: '', alt: '', caption: '' });
            file.focus();
            announce('Picture removed');
            onChanged(box);
        });
        // drop a picture on the box
        const hasFiles = (e) => e.dataTransfer && Array.from(e.dataTransfer.types || []).indexOf('Files') >= 0;
        box.addEventListener('dragover', (e) => { if (hasFiles(e)) { e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; box.classList.add('is-drop'); } });
        box.addEventListener('dragleave', (e) => { if (!box.contains(e.relatedTarget)) box.classList.remove('is-drop'); });
        box.addEventListener('drop', (e) => {
            if (!hasFiles(e)) return;
            e.preventDefault();
            box.classList.remove('is-drop');
            upload(e.dataTransfer.files[0]);
        });
    }

    /** A link address typed by hand: "www.example.org" becomes "https://www.example.org". Same rules as the server (docs/NEWSLETTER.md). */
    function fixUrl(v) {
        v = String(v || '').trim();
        if (!v) return '';
        if (/^(https?:|mailto:|tel:)/i.test(v) || v[0] === '/' || v[0] === '#') return v;
        if (/^[a-z][a-z0-9+.-]*:/i.test(v)) return v;                          // some other scheme: the server refuses it
        if (/^(www\.|[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,})(\/|$|\?|#)/i.test(v)) return 'https://' + v;
        if (/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(v)) return 'mailto:' + v;
        return '/' + v.replace(/^\/+/, '');
    }
    const URL_PROBLEM = 'Use a full web address (https://...), an e-mail link (mailto:...), a phone link (tel:...) or a page of this site (/about).';
    function urlProblem(v) {
        if (!v) return '';
        if (/^(https?:\/\/[^\s]+|mailto:[^\s]+|tel:[^\s]+)$/i.test(v)) return '';
        if (v[0] === '/' && v.indexOf('//') !== 0 && !/\s/.test(v)) return '';
        return URL_PROBLEM;
    }
    function tidyUrlInput(input) {
        const fixed = fixUrl(input.value);
        if (fixed !== input.value.trim()) { input.value = fixed; onChanged(input); }
        const problem = urlProblem(fixed);
        clearFieldError(input);
        if (problem) showFieldError(input, problem);
    }
    root.addEventListener('focusout', (e) => {
        const t = e.target;
        if (t instanceof HTMLInputElement && (t.matches('[data-link-url]') || t.matches('[data-f="button.url"]'))) tidyUrlInput(t);
        if (t === D.slug && !t.readOnly) {
            const v = t.value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            if (v !== t.value) { t.value = v; onChanged(t); }
        }
    });

    function setLink(box, link) {
        $('[data-link-label]', box).value = (link && link.label) || '';
        $('[data-link-url]', box).value = (link && link.url) || '';
    }

    // ---- character counters ("123 / 600"): always for long fields, near the limit for short ones
    function initCounters(scope) {
        $$('[data-count]', scope).forEach((input) => {
            if (input.dataset.counted) return;
            input.dataset.counted = '1';
            const max = parseInt(input.dataset.count, 10) || input.maxLength;
            const out = el('div', { class: 'char-count', 'aria-hidden': 'true' });
            (input.closest('.field') || input.parentElement).insertBefore(out, input.nextSibling);
            const always = input.tagName === 'TEXTAREA' || max >= 300;
            const upd = () => {
                const n = input.value.length;
                out.textContent = n + ' / ' + max;
                out.hidden = !always && n < max * 0.7;
                out.classList.toggle('is-over', n > max);
            };
            input.addEventListener('input', upd);
            input.__nlCount = upd;
            upd();
        });
    }
    const refreshCounters = (scope) => $$('[data-count]', scope || root).forEach((i) => { if (i.__nlCount) i.__nlCount(); });

    // ------------------------------------------------------------------------------------------------ 4. blocks
    const newBlockData = (type) => {
        const id = uid();
        const img = { path: '', alt: '', caption: '' };
        const link = { label: '', url: '' };
        switch (type) {
            case 'article': return { id, type, heading: '', body_html: '', image: img, link };
            case 'image': return { id, type, image: img };
            case 'quote': return { id, type, body_html: '', cite: '' };
            case 'button': return { id, type, button: { label: '', url: '', style: 'primary' } };
            default: return { id, type: 'divider' };
        }
    };

    function fillBlock(card, b) {
        const set = (name, v) => { const i = $('[data-f="' + name + '"]', card); if (i) i.value = v || ''; };
        switch (b.type) {
            case 'article':
                set('heading', b.heading);
                setRich($('[data-f="body_html"]', card), b.body_html);
                setImage($('[data-image]', card), b.image);
                setLink($('[data-link]', card), b.link);
                break;
            case 'image':
                setImage($('[data-image]', card), b.image);
                break;
            case 'quote':
                setRich($('[data-f="body_html"]', card), b.body_html);
                set('cite', b.cite);
                break;
            case 'button':
                set('button.label', b.button.label);
                set('button.url', b.button.url);
                set('button.style', b.button.style || 'primary');
                break;
            default:
        }
    }
    function buildBlock(b) {
        const tpl = $('template[data-block-template="' + b.type + '"]');
        if (!tpl) return null;
        b.id = /^[a-z][a-z0-9_-]{1,31}$/.test(String(b.id || '')) && !cardById(b.id) ? String(b.id) : uid();      // the id ends up in attribute values: only safe characters
        const holder = el('div');
        holder.innerHTML = tpl.innerHTML.replace(/__ID__/g, b.id).trim();
        const card = holder.firstElementChild;
        fillBlock(card, b);
        return card;
    }
    /** Put a built card in the list (before $before, else at the end) and wake up its pieces. */
    function mountBlock(card, before) {
        E.blocks.insertBefore(card, before || null);
        $$('[data-image]', card).forEach(initImage);
        $$('textarea[data-richtext]', card).forEach((ta) => { if (window.LuminaEditor) window.LuminaEditor.init(ta); });
        initCounters(card);
        updateSummary(card);
    }
    function summaryOf(card) {
        const t = card.dataset.type;
        const f = (n) => val($('[data-f="' + n + '"]', card));
        const text = (n) => plain(($('[data-f="' + n + '"]', card) || {}).value || '');
        let s = '';
        if (t === 'article') s = f('heading') || text('body_html') || (readImage($('[data-image]', card)).path ? 'Picture' : '');
        else if (t === 'image') { const i = readImage($('[data-image]', card)); s = i.caption || i.alt || (i.path ? 'Picture' : ''); }
        else if (t === 'quote') s = text('body_html');
        else if (t === 'button') s = f('button.label');
        else s = 'A thin line';
        return clip(s, 80);
    }
    function updateSummary(card) {
        const s = summaryOf(card);
        const node = $('[data-summary]', card);
        node.textContent = s || '(empty)';
        node.classList.toggle('is-empty', !s);
    }
    /** Numbers, button states, labels and the empty state: call after every add, move or remove. */
    function refreshBlocks() {
        const cards = blockCards();
        cards.forEach((card, i) => {
            const first = i === 0;
            const last = i === cards.length - 1;
            const up = $('[data-act="block-up"]', card);
            const down = $('[data-act="block-down"]', card);
            up.disabled = first;
            down.disabled = last;
            card.setAttribute('aria-label', 'Section ' + (i + 1) + ' of ' + cards.length + ': ' + BLOCK_TYPES[card.dataset.type] + ', ' + ($('[data-summary]', card).textContent || 'empty'));
            card.removeAttribute('aria-labelledby');
        });
        E.count.textContent = String(cards.length);
        E.empty.hidden = cards.length > 0;
        const full = cards.length >= boot.limits.blocks;
        E.addLimit.hidden = !full;
        $$('[data-add-block]').forEach((b) => { b.disabled = full; });
    }
    function setOpen(card, open) {
        const toggle = $(':scope > .nl-card__head [data-collapse-toggle], :scope > .nl-bc__head [data-collapse-toggle]', card);
        const body = $(':scope > .nl-card__body, :scope > .nl-bc__body', card);
        if (!toggle || !body) return;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        body.hidden = !open;
        card.classList.toggle('is-open', open);
    }

    function addBlock(type, opts = {}) {
        if (blockCards().length >= boot.limits.blocks) { toast('An issue can have at most ' + boot.limits.blocks + ' sections.', { tone: 'error' }); return null; }
        const data = opts.data || newBlockData(type);
        const card = buildBlock(data);
        if (!card) return null;
        mountBlock(card, opts.before || null);
        setOpen(card, true);
        refreshBlocks();
        if (!opts.silent) {
            onChanged(card, { structure: true });
            card.scrollIntoView({ block: 'center', behavior: smooth });
            const first = card.querySelector('input:not([type=hidden]):not([type=file]), [contenteditable="true"], select');
            if (first) first.focus({ preventScroll: true });
            else card.focus({ preventScroll: true });
            announce(BLOCK_TYPES[data.type] + ' section added at position ' + (blockCards().indexOf(card) + 1) + '. Fill it in below.');
            flash(card);
        }
        return card;
    }
    function flash(node) {
        node.classList.remove('is-flash');
        void node.offsetWidth;
        node.classList.add('is-flash');
        setTimeout(() => node.classList.remove('is-flash'), 1600);
    }
    function moveBlock(card, dir, focusAct) {
        const sib = dir < 0 ? card.previousElementSibling : card.nextElementSibling;
        if (!sib) return;
        if (dir < 0) E.blocks.insertBefore(card, sib); else E.blocks.insertBefore(sib, card);
        refreshBlocks();
        onChanged(card, { structure: true });
        const n = blockCards().indexOf(card) + 1;
        announce(BLOCK_TYPES[card.dataset.type] + ' section moved to position ' + n + ' of ' + blockCards().length);
        let btn = $('[data-act="' + focusAct + '"]', card);
        if (btn.disabled) btn = $('[data-act="' + (focusAct === 'block-up' ? 'block-down' : 'block-up') + '"]', card);
        btn.focus();
        flash(card);
    }
    function duplicateBlock(card) {
        const data = JSON.parse(JSON.stringify(collectBlock(card)));
        data.id = uid();
        const copy = addBlock(data.type, { data, before: card.nextElementSibling, silent: true });
        if (!copy) return;
        refreshBlocks();
        onChanged(copy, { structure: true });
        copy.scrollIntoView({ block: 'center', behavior: smooth });
        const first = copy.querySelector('input:not([type=hidden]):not([type=file]), [contenteditable="true"], select');
        if (first) first.focus({ preventScroll: true });
        flash(copy);
        announce('Section duplicated. The copy is at position ' + (blockCards().indexOf(copy) + 1) + '.');
    }
    async function removeBlock(card) {
        const name = BLOCK_TYPES[card.dataset.type];
        const sum = $('[data-summary]', card).textContent;
        const ok = await confirmDialog({
            title: 'Remove this section?',
            body: 'The ' + name.toLowerCase() + ' section' + (sum && sum !== '(empty)' && card.dataset.type !== 'divider' ? ' "' + sum + '"' : '') + ' will be taken out of the issue.\n\nIt stays in History until it drops out of the last 30 saved versions, so it can be brought back.',
            okLabel: 'Remove section', tone: 'danger',
        });
        if (!ok) return;
        const next = card.nextElementSibling || card.previousElementSibling;
        $$('textarea[data-richtext]', card).forEach((ta) => { if (window.LuminaEditor) window.LuminaEditor.destroy(ta); });
        card.remove();
        refreshBlocks();
        onChanged(E.blocks, { structure: true });
        announce('Section removed');
        if (next) { const b = $('[data-collapse-toggle]', next); if (b) b.focus(); } else $('[data-add-block]').focus();
    }

    // ---- drag and drop (HTML5). The grip starts a drag; Move up / Move down are the keyboard way.
    let drag = null;
    E.blocks.addEventListener('dragstart', (e) => {
        const grip = e.target.closest ? e.target.closest('[data-grip]') : null;
        if (!grip) { return; }
        const card = grip.closest('[data-block]');
        drag = { card };
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', card.dataset.blockId); e.dataTransfer.setDragImage(card, 24, 24); } catch (_) { /* old browsers */ }
        setTimeout(() => card.classList.add('is-dragging'), 0);
    });
    function dropTarget(y) {
        const cards = blockCards().filter((c) => c !== drag.card);
        for (const c of cards) {
            const r = c.getBoundingClientRect();
            if (y < r.top + r.height / 2) return c;
        }
        return null;
    }
    function clearDropMarks() { $$('.is-drop-before, .is-drop-end', E.blocks.parentElement || root).forEach((n) => n.classList.remove('is-drop-before', 'is-drop-end')); }
    E.blocks.addEventListener('dragover', (e) => {
        if (!drag) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        clearDropMarks();
        const t = dropTarget(e.clientY);
        if (t) t.classList.add('is-drop-before'); else E.blocks.classList.add('is-drop-end');
    });
    E.blocks.addEventListener('drop', (e) => {
        if (!drag) return;
        e.preventDefault();
        const t = dropTarget(e.clientY);
        const before = t || null;
        const card = drag.card;
        clearDropMarks();
        if (before !== card.nextElementSibling) {
            E.blocks.insertBefore(card, before);
            refreshBlocks();
            onChanged(card, { structure: true });
            announce(BLOCK_TYPES[card.dataset.type] + ' section moved to position ' + (blockCards().indexOf(card) + 1) + ' of ' + blockCards().length);
            flash(card);
        }
    });
    E.blocks.addEventListener('dragend', () => {
        if (!drag) return;
        drag.card.classList.remove('is-dragging');
        clearDropMarks();
        drag = null;
    });
    E.blocks.addEventListener('dragleave', (e) => { if (drag && !E.blocks.contains(e.relatedTarget)) clearDropMarks(); });
    // the grip is a plain element: make the whole handle draggable
    E.blocks.addEventListener('mousedown', (e) => { const g = e.target.closest ? e.target.closest('[data-grip]') : null; if (g) g.draggable = true; });

    // ---- clicks inside the block list and the card headers
    root.addEventListener('click', (e) => {
        const t = e.target.closest('button, a');
        if (!t) return;
        const card = t.closest('[data-block]');
        if (t.matches('[data-collapse-toggle]')) {
            const c = t.closest('[data-block], [data-card]');
            setOpen(c, t.getAttribute('aria-expanded') !== 'true');
            return;
        }
        if (card && t.matches('[data-act="block-up"]')) { moveBlock(card, -1, 'block-up'); return; }
        if (card && t.matches('[data-act="block-down"]')) { moveBlock(card, 1, 'block-down'); return; }
        if (card && t.matches('[data-act="block-dup"]')) { duplicateBlock(card); return; }
        if (card && t.matches('[data-act="block-remove"]')) { removeBlock(card); return; }
        if (t.matches('[data-add-block]')) { addBlock(t.dataset.addBlock); return; }
        if (t.matches('[data-act="collapse-all"]')) { blockCards().forEach((c) => setOpen(c, false)); return; }
        if (t.matches('[data-act="expand-all"]')) { blockCards().forEach((c) => setOpen(c, true)); return; }
        if (t.matches('[data-goto]')) { goTo(t.dataset.goto); return; }
        if (t.matches('[data-check-more]')) { S.checkAll = t.dataset.checkMore === 'more'; renderCheck(); return; }
        if (t.matches('[data-use-number]')) { D.number.value = t.dataset.useNumber; onChanged(D.number); D.number.focus(); return; }
    });
    function goTo(path) {
        const loc = locate(path);
        if (loc.card) openCard(loc.card);
        setView('edit');
        if (loc.el) { focusEl(loc.el); if (loc.card && loc.card.matches('[data-block]')) flash(loc.card); }
    }

    // ---- an edit happened somewhere (typing, picture, structure): keep every derived thing up to date
    function onChanged(node, o = {}) {
        if (node && node.nodeType === 1) {
            if (node.matches('input, textarea, select')) clearFieldError(node);
            const card = node.closest ? node.closest('[data-block]') : null;
            if (card) updateSummary(card);
            if (card || o.structure) refreshBlocks();
        }
        S.autoBlocked = false;
        S.lastError = '';
        checkDirty();
        schedulePreview();
        updateDetailsUi();
    }
    root.addEventListener('input', (e) => {
        const t = e.target;
        if (!(t instanceof HTMLInputElement || t instanceof HTMLTextAreaElement || t instanceof HTMLSelectElement) || t.closest('dialog')) return;
        onChanged(t);
    });
    root.addEventListener('change', (e) => {
        const t = e.target;
        if (t instanceof HTMLSelectElement && !t.closest('dialog')) onChanged(t);
    });
    root.addEventListener('lumina:editor-change', (e) => {
        const ta = e.target;
        if (window.LuminaEditor && ta instanceof HTMLTextAreaElement) window.LuminaEditor.sync(ta);
        onChanged(ta);
    });
    root.addEventListener('lumina:editor-mode', () => checkDirty());

    // ---- a block was focused: show it in the preview
    root.addEventListener('focusin', (e) => {
        const t = e.target;
        if (!t.closest || t.closest('dialog')) return;
        const card = t.closest('[data-block]');
        if (card) { selectInPreview(card.dataset.blockId); return; }
        if (t.closest('[data-featured]')) { selectInPreview('featured'); return; }
        if (t.closest('[data-card="details"]')) selectInPreview('details');
    });

    /** Called from the preview: bring the matching part of the form into view. */
    function focusBlock(id) {
        setView('edit');
        if (id === 'details') { openCard($('[data-card="details"]')); focusEl(D.title); }
        else if (id === 'featured') { openCard(FEAT); focusEl(FEAT_F.heading); flash(FEAT); }
        else {
            const card = cardById(id);
            if (!card) return;
            setOpen(card, true);
            card.scrollIntoView({ block: 'center', behavior: smooth });
            const first = card.querySelector('input:not([type=hidden]):not([type=file]), [contenteditable="true"], select');
            if (first) first.focus({ preventScroll: true }); else card.focus({ preventScroll: true });
            flash(card);
        }
        selectInPreview(id, false);
    }

    // ---- the header texts that mirror the form: bar title, number hint, web address and search snippet
    function updateDetailsUi() {
        const d = collectDetails();
        const label = (d.issue_number ? 'Issue No. ' + d.issue_number + (d.title ? ' · ' : '') : '') + (d.title || (d.issue_number ? '' : 'Untitled issue'));
        E.barTitle.textContent = label;
        const hint = $('[data-number-hint]');
        if (hint) {
            hint.textContent = '';
            if (!d.issue_number) {
                hint.append('Each issue needs its own number. The next free one is ' + boot.next + '. ');
                hint.appendChild(el('button', { type: 'button', class: 'nl-linkbtn', 'data-use-number': boot.next, text: 'Use ' + boot.next }));
            } else hint.append('Each issue needs its own number. The next free one is ' + boot.next + '.');
        }
        $('[data-slug-base]').textContent = boot.urls.slug_base.replace(/^https?:\/\//, '');
        const site = boot.site || '';
        const title = d.seo_title || d.title || 'Untitled issue';
        $('[data-serp-title]').textContent = clip(title + (site && title.indexOf(site) < 0 ? ' | ' + site : ''), 70);
        $('[data-serp-url]').textContent = boot.urls.slug_base + (d.slug || S.item.slug || '');
        let desc = d.seo_description || d.summary;
        if (!desc) {
            const c = collectContent();
            desc = [c.featured.heading, plain(c.featured.body_html), ...c.blocks.map((b) => [b.heading || '', plain(b.body_html || '')].join(' '))].join(' ').replace(/\s+/g, ' ').trim();
        }
        $('[data-serp-desc]').textContent = clip(desc || 'No description yet. Search engines will pick some text from the issue.', 160);
    }

    // ------------------------------------------------------------------------------------------------ 5. saving
    // A safety copy of unsaved work in THIS browser (localStorage): for live issues (no server autosave), when offline, when a save fails.
    const BK = 'lumina.nl.backup.' + S.id;
    function backupLocal() {
        try {
            const cur = collect(true);
            if (!changes(cur, true)) return;                         // nothing unsaved: no copy needed
            window.localStorage.setItem(BK, JSON.stringify({ v: S.version, t: Date.now(), d: cur.d, c: cur.c }));
        } catch (_) { /* private window, full disk: nothing to do */ }
    }
    function clearBackup() { try { window.localStorage.removeItem(BK); } catch (_) { /* ignore */ } }
    function readBackup() { try { const r = window.localStorage.getItem(BK); return r ? JSON.parse(r) : null; } catch (_) { return null; } }

    /** What the server told us about the issue after a save / status change: keep the parts the toolbar needs. */
    function takeItem(item) {
        S.version = item.version;
        S.item = Object.assign({}, S.item, {
            status: item.status, is_live: item.is_live, allowed: item.allowed, publish_at: item.publish_at, published_at: item.published_at,
            updated_at: item.updated_at, url: item.url, slug: item.slug, slug_locked: item.slug_locked, email: item.email, version: item.version, readiness: item.readiness,
        });
        if (item.readiness) S.readiness = item.readiness;
    }

    function saveNow(mode) {
        const run = () => doSave(mode);
        S.chain = S.chain.then(run, run);
        return S.chain;
    }
    /**
     * mode 'auto'   : lenient content check on the server (a half-typed address never blocks it), no messages on the page
     *      'manual' : strict, shows every problem next to its field
     *      'beacon' : like 'auto', sent while the page is being hidden or closed
     * @returns {Promise<{ok:boolean, nothing?:boolean}>}
     */
    async function doSave(mode) {
        const strict = mode === 'manual';
        if (S.conflict) {
            if (!strict) return { ok: false, skipped: true };
            openConflict();
            return { ok: false, conflict: true };
        }
        const cur = collect(true);
        const payload = changes(cur, strict);
        if (!payload) {
            if (strict) clearAllErrors();
            setDirty(isDirty());
            return { ok: true, nothing: true };
        }
        S.saving = true;
        renderSave();
        let r;
        let partial = '';
        for (let attempt = 0; attempt < 2; attempt++) {
            const body = Object.assign({ version: S.version }, payload);
            if (!strict) body.autosave = true;
            r = await api('PUT', '/' + S.id, body, { keepalive: mode === 'beacon' });
            if (r.status === 422 && !strict && attempt === 0 && r.data && r.data.errors) {
                // an invalid detail (an empty date, a number already taken) must not stop the text from being saved: leave that field out
                const bad = Object.keys(r.data.errors).filter((k) => k !== 'content' && Object.prototype.hasOwnProperty.call(payload, k));
                if (bad.length && Object.keys(payload).length > bad.length) {
                    partial = String(r.data.errors[bad[0]]);
                    bad.forEach((k) => { delete payload[k]; });
                    continue;
                }
            }
            break;
        }
        S.saving = false;
        if (r.aborted) { renderSave(); return { ok: false }; }
        if (r.network) {
            S.offline = true;
            backupLocal();
            renderSave();
            if (strict) toast('You are offline, so the issue could not be saved. Your changes are safe in this browser.', { tone: 'error' });
            return { ok: false, offline: true };
        }
        S.offline = false;
        if (r.status === 200 && r.data && r.data.data) {
            const item = r.data.data;
            takeItem(item);
            Object.keys(payload).forEach((k) => { if (k !== 'content') S.saved.d[k] = cur.d[k]; });
            if ('content' in payload) S.saved.c = cur.cj;
            const warns = r.data.warnings ? Object.keys(r.data.warnings) : [];
            S.needsStrict = !strict && warns.length > 0;
            S.warned = r.data.warnings || {};
            S.lastSavedAt = new Date();
            S.lastError = partial;                      // a detail that could not be saved (autosave only) stays flagged
            S.autoBlocked = partial !== '';
            if (strict) clearAllErrors();
            setDirty(isDirty());
            if (partial) backupLocal(); else clearBackup();
            renderSave();
            applyStatus();
            renderCheck();
            if (S.needsStrict && !strict) {
                const first = locate(warns[0]);
                toast('Saved, but ' + (first.where || 'one entry') + ' has something that is not valid yet: ' + Object.values(r.data.warnings)[0], { tone: 'warning', timeout: 9000 });
            }
            return { ok: true };
        }
        if (r.status === 409 && r.data && r.data.error && r.data.error !== 'conflict') {
            // campaign_running (an e-mail campaign freezes the issue) or status_changed (an autosave reached an issue that is no longer a draft):
            // not a version conflict, so no "keep mine / take theirs" dialog; the text stays safe in this browser
            S.lastError = String(r.data.message || 'the issue cannot be changed right now.');
            S.autoBlocked = true;
            backupLocal();
            renderSave();
            toast(S.lastError, { tone: 'error', timeout: 0 });
            return { ok: false, blocked: true };
        }
        if (r.status === 409 && r.data) {
            S.conflict = r.data;
            backupLocal();
            renderSave();
            openConflict();
            return { ok: false, conflict: true };
        }
        if (r.status === 422 && r.data) {
            const errors = r.data.errors || {};
            if (strict) {
                showProblems(errors, 'The issue could not be saved yet');
                S.lastError = '';
            } else {
                S.autoBlocked = true;
                S.lastError = String(r.data.message || 'Please check the fields.');
            }
            backupLocal();
            renderSave();
            return { ok: false, invalid: true, errors };
        }
        if (r.login) {
            backupLocal();
            S.lastError = 'your session has ended. Sign in again.';
            renderSave();
            if (strict || !S.warnedSession) { S.warnedSession = true; showDialog(dialog('session')); }
            return { ok: false, login: true };
        }
        if (r.status === 404) {
            S.lastError = 'this issue no longer exists (somebody deleted it).';
            renderSave();
            toast('This issue was deleted by somebody else. Your text is kept in this browser.', { tone: 'error', timeout: 0 });
            backupLocal();
            return { ok: false };
        }
        S.lastError = (r.data && r.data.message) || 'the server had a problem (code ' + (r.status || '?') + '). Please try again.';
        backupLocal();
        renderSave();
        if (strict) toast('The issue could not be saved: ' + S.lastError, { tone: 'error' });
        return { ok: false };
    }

    // ---- conflict: somebody else saved a newer version
    function openConflict() {
        const c = S.conflict;
        if (!c) return;
        const who = c.current && c.current.updated_by ? c.current.updated_by : 'Somebody else';
        const when = c.current && c.current.updated_at ? ' at ' + clock(parseWall(c.current.updated_at) || new Date()) : '';
        const d = dialog('conflict');
        $('[data-conflict-text]', d).textContent = who + ' saved a newer version of this issue' + when + ', after you opened it. Your changes on this page have not been saved yet.';
        if (d.open) return;
        d.returnValue = '';
        d.addEventListener('close', async () => {
            const choice = d.returnValue;
            if (choice === 'theirs') {
                const item = S.conflict && S.conflict.data;
                S.conflict = null;
                if (item) { loadIssue(item); toast('Showing their version.', { tone: 'info' }); }
            } else if (choice === 'mine') {
                const theirs = S.conflict && S.conflict.data;
                if (theirs) {
                    takeItem(theirs);                                  // their version number, status and the rest
                    adoptTheirDetails(theirs);                         // details I did not touch follow theirs; mine stay mine
                    S.saved.c = '\u0000';                              // my content replaces theirs
                }
                S.conflict = null;
                renderSave();
                const res = await saveNow('manual');
                if (res.ok) toast('Your version was saved. Theirs stays in History.', { tone: 'success' });
            } else {
                renderSave();
            }
        }, { once: true });
        showDialog(d);
    }

    // ---- timers
    setInterval(() => {
        if (!S.dirty || S.saving || S.conflict) return;
        if (autoServer()) {
            if (!S.autoBlocked && navigator.onLine !== false) saveNow('auto');
            else backupLocal();
        } else backupLocal();
    }, AUTOSAVE_MS);
    window.addEventListener('online', () => { S.offline = false; renderSave(); if (S.dirty && autoServer()) saveNow('auto'); });
    window.addEventListener('offline', () => { if (S.dirty) { S.offline = true; backupLocal(); renderSave(); } });
    doc.addEventListener('visibilitychange', () => {
        if (doc.visibilityState !== 'hidden' || !S.dirty) return;
        backupLocal();
        if (autoServer() && !S.saving && !S.conflict && !S.autoBlocked) saveNow('beacon');
    });
    window.addEventListener('pagehide', () => { if (S.dirty) backupLocal(); });
    window.addEventListener('beforeunload', (e) => {
        if (S.guard && (S.dirty || S.saving)) {
            backupLocal();
            e.preventDefault();
            e.returnValue = '';
        }
    });
    doc.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 's') {
            e.preventDefault();
            actSave();
        }
    });

    // ------------------------------------------------------------------------------------------------ 6. live preview (rendered by the server)
    const PREVIEW_CSS = '[data-nl-block]{cursor:pointer;outline:2px solid transparent;outline-offset:6px;border-radius:6px;transition:outline-color .15s ease}'
        + '[data-nl-block]:hover{outline-color:rgba(214,161,81,.85)}[data-nl-block]:focus-visible{outline-color:#B85A14}[data-nl-block].nl-is-selected{outline:3px solid #B85A14}'
        + 'a{cursor:pointer}html{scroll-behavior:auto!important}';

    function setPreviewState(text, tone) {
        E.previewState.textContent = text;
        E.previewState.dataset.tone = tone || '';
    }
    const currentFrame = () => E.device.querySelector('iframe.is-current');

    function layoutPreview() {
        const w = E.stage.clientWidth;
        const h = E.stage.clientHeight;
        if (!w || !h) return;
        const room = Math.max(160, w - 24);
        // until the editor picks Computer or Phone, a narrow preview pane (laptop screens) shows the Phone width: the 1040 px page shrunk
        // into ~350 px would make the text 5-7 px tall and unreadable
        if (!S.deviceChosen) {
            const auto = room < 700 ? 'mobile' : 'desktop';
            if (auto !== S.device) setDevice(auto, { silent: true });
        }
        const W = S.device === 'mobile' ? 390 : 1040;
        const scale = Math.max(0.6, Math.min(1, room / W));               // never smaller than 60 %: the pane scrolls sideways instead
        E.device.dataset.deviceType = S.device;
        E.device.style.width = Math.round(W * scale) + 'px';
        E.stage.classList.toggle('is-wide', Math.round(W * scale) > room);
        E.device.style.height = Math.max(200, h - 24) + 'px';
        E.device.style.setProperty('--nl-w', W + 'px');
        E.device.style.setProperty('--nl-scale', String(scale));
        E.device.style.setProperty('--nl-h', Math.round(Math.max(200, h - 24) / scale) + 'px');
        // the pane became visible (window made wider, "Preview" tab opened): show the latest preview without waiting for an edit
        if (S.pendingHtml) { const html = S.pendingHtml; S.pendingHtml = null; swapFrame(html, S.previewSeq); }
        else if (!currentFrame() && !S.previewBusy) refreshPreview();
    }
    if (window.ResizeObserver) new ResizeObserver(debounce(layoutPreview, 60)).observe(E.stage);
    window.addEventListener('resize', debounce(layoutPreview, 100));

    /** opts.user = the editor pressed Computer / Phone (remembered, announced); opts.silent = an automatic choice (never announced). */
    function setDevice(kind, opts) {
        opts = opts || {};
        S.device = kind;
        if (opts.user) S.deviceChosen = true;
        $$('[data-device]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.device === kind)));
        if (!opts.silent) layoutPreview();
        if (opts.user) announce(kind === 'mobile' ? 'Preview shows a phone screen' : 'Preview shows a computer screen');
    }

    const schedulePreview = (() => {
        const run = debounce(() => refreshPreview(), PREVIEW_MS);
        const runSlow = debounce(() => refreshPreview(), 2500);       // the preview is hidden (narrow screen): only the checklist needs refreshing
        return () => {
            S.previewStale = true;
            if (previewVisible()) { setPreviewState('Updating...', 'busy'); run(); } else runSlow();
        };
    })();
    function previewVisible() {
        return E.stage.offsetParent !== null;
    }

    async function refreshPreview() {
        if (S.previewAbort) S.previewAbort.abort();
        const seq = ++S.previewSeq;
        const ctl = typeof AbortController !== 'undefined' ? new AbortController() : null;
        S.previewAbort = ctl;
        const cur = collect();
        const d = Object.assign({}, cur.d);
        delete d.slug;
        setPreviewState('Updating...', 'busy');
        S.previewBusy = true;
        const r = await api('POST', '/preview', Object.assign({ id: S.id }, d, { content: cur.c }), { signal: ctl ? ctl.signal : undefined });
        if (seq !== S.previewSeq || r.aborted) return;
        S.previewBusy = false;
        if (r.network) { setPreviewState('Preview paused: you are offline', 'warn'); setTimeout(() => { if (seq === S.previewSeq) refreshPreview(); }, 8000); return; }
        if (r.status === 429) { setPreviewState('Preview paused for a moment...', 'warn'); setTimeout(() => { if (seq === S.previewSeq) refreshPreview(); }, 6000); return; }
        if (r.login) { setPreviewState('Preview stopped: please sign in again', 'error'); return; }
        if (r.status !== 200 || !r.data || typeof r.data.html !== 'string') { setPreviewState('The preview could not be updated', 'error'); return; }
        S.previewStale = false;
        if (r.data.readiness) { S.readiness = r.data.readiness; renderCheck(); }
        if (previewVisible()) { S.pendingHtml = null; swapFrame(r.data.html, seq); } else { S.pendingHtml = r.data.html; setPreviewState('Up to date', 'ok'); }
    }

    /** Two iframes: the new one loads out of sight and replaces the old one when ready, so the preview never flashes and keeps its scroll position. */
    function swapFrame(html, seq) {
        const old = currentFrame();
        const y = old && old.contentWindow ? old.contentWindow.scrollY || 0 : 0;
        // sandbox without allow-scripts: the preview can show the issue but nothing inside it can run; allow-same-origin lets this page read it for click-to-edit
        const fr = el('iframe', { class: 'nl-frame', title: 'Live preview of this issue', loading: 'eager', sandbox: 'allow-same-origin' });
        fr.addEventListener('load', () => {
            if (seq !== S.previewSeq) { fr.remove(); return; }
            const d = fr.contentDocument;
            if (!d || !d.body) { return; }
            const st = d.createElement('style');
            st.textContent = PREVIEW_CSS;
            d.head.appendChild(st);
            d.addEventListener('click', (e) => {
                const a = e.target.closest ? e.target.closest('a') : null;
                if (a) e.preventDefault();
                const b = e.target.closest ? e.target.closest('[data-nl-block]') : null;
                if (b) focusBlock(b.getAttribute('data-nl-block'));
            });
            d.addEventListener('keydown', (e) => {
                if ((e.key === 'Enter' || e.key === ' ') && e.target.hasAttribute && e.target.hasAttribute('data-nl-block')) {
                    e.preventDefault();
                    focusBlock(e.target.getAttribute('data-nl-block'));
                }
            });
            fr.contentWindow.scrollTo(0, y);
            fr.classList.add('is-current');
            if (old) old.remove();
            E.device.querySelectorAll('iframe:not(.is-current)').forEach((f) => { if (f !== fr) f.remove(); });
            setPreviewState('Up to date', 'ok');
            if (S.active) markSelected(S.active, false);
        });
        fr.srcdoc = html;
        E.device.appendChild(fr);
    }
    function markSelected(id, scroll) {
        const fr = currentFrame();
        if (!fr || !fr.contentDocument) return;
        const d = fr.contentDocument;
        d.querySelectorAll('.nl-is-selected').forEach((n) => n.classList.remove('nl-is-selected'));
        let target = null;
        try { target = d.querySelector('[data-nl-block="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]'); } catch (_) { target = null; }
        if (!target) return;
        target.classList.add('nl-is-selected');
        if (scroll !== false) {
            const r = target.getBoundingClientRect();
            const vh = fr.contentWindow.innerHeight;
            if (r.top < 40 || r.bottom > vh - 20) {
                fr.contentWindow.scrollTo({ top: Math.max(0, fr.contentWindow.scrollY + r.top - 80), behavior: smooth });
            }
        }
    }
    const selectInPreviewSoon = debounce((id, scroll) => markSelected(id, scroll), 120);
    function selectInPreview(id, scroll) {
        S.active = id;
        selectInPreviewSoon(id, scroll);
    }

    // ---- narrow screens: Write / Preview switch
    function setView(v) {
        E.split.dataset.view = v;
        $$('[data-view]', root).filter((b) => b.tagName === 'BUTTON').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.view === v)));
        if (v === 'preview') {
            layoutPreview();
            if (S.pendingHtml) { const h = S.pendingHtml; S.pendingHtml = null; swapFrame(h, S.previewSeq); }
            else if (S.previewStale || !currentFrame()) refreshPreview();
        }
    }

    // ------------------------------------------------------------------------------------------------ 7. status, checklist, actions
    const acts = (name) => $$('[data-act="' + name + '"]');
    function showAct(name, on) { acts(name).forEach((b) => { b.hidden = !on; }); }
    const everPublic = () => S.item.status === 'published' || !!S.item.published_at || S.item.slug_locked;

    function applyStatus() {
        const it = S.item;
        const st = it.status;
        const allowed = it.allowed || [];
        root.dataset.status = st;
        root.dataset.live = it.is_live ? '1' : '0';
        E.badge.textContent = boot.labels.status[st] || st;
        E.badge.className = 'badge badge--' + (boot.labels.tones[st] || 'neutral') + ' badge--dot';
        showAct('publish', allowed.indexOf('published') >= 0);
        showAct('schedule', allowed.indexOf('scheduled') >= 0 || st === 'scheduled');
        showAct('unpublish', allowed.indexOf('draft') >= 0);
        $('[data-schedule-label]').textContent = st === 'scheduled' ? 'Change time...' : 'Schedule...';
        $('[data-unpublish-label]').textContent = st === 'published' ? 'Unpublish' : st === 'scheduled' ? 'Cancel schedule' : 'Move to drafts';
        $('[data-save-label]').textContent = st === 'draft' ? 'Save draft' : 'Save changes';
        const arch = $('[data-archive-row]');
        arch.hidden = allowed.indexOf('archived') < 0;
        const blocked = everPublic() && !boot.can.delete_published;
        acts('delete').forEach((b) => {
            b.disabled = blocked;
            b.title = blocked ? 'Only an administrator can delete an issue that has been published. You can unpublish it instead.' : '';
        });
        $('[data-delete-text]').textContent = blocked
            ? 'An issue that has been published can only be deleted by an administrator. You can unpublish it instead.'
            : 'Removes the issue and its history for good. This cannot be undone.';
        const slugLocked = !!it.slug_locked;
        D.slug.readOnly = slugLocked;
        renderBanner();
        renderSave();
    }

    function renderBanner() {
        const b = E.banner;
        const it = S.item;
        b.textContent = '';
        b.className = 'nl-banner';
        const offer = S.backupOffer;
        if (offer) {
            b.classList.add('nl-banner--warn');
            b.append(el('span', { text: 'We found unsaved changes from ' + clock(new Date(offer.t)) + ' that were kept in this browser.' }));
            b.append(el('button', { type: 'button', class: 'btn btn--outline btn--sm', 'data-backup': 'restore', text: 'Bring them back' }));
            b.append(el('button', { type: 'button', class: 'btn btn--ghost btn--sm', 'data-backup': 'discard', text: 'Discard them' }));
            b.hidden = false;
            return;
        }
        if (it.email && ['queued', 'sending', 'paused'].indexOf(it.email.status) >= 0) {
            b.classList.add('nl-banner--warn');
            b.append(el('span', { text: 'The e-mail campaign of this issue is ' + (it.email.status === 'paused' ? 'paused' : 'running') + '. The issue cannot be changed until the campaign is finished or cancelled, so that every subscriber receives the same message.' }));
            const mailLink = $('a[data-act="email"]');
            if (mailLink) b.append(el('a', { class: 'btn btn--outline btn--sm', href: mailLink.getAttribute('href'), text: 'Open the e-mail page' }));
        } else if (it.status === 'published' && it.is_live) {
            b.classList.add('nl-banner--live');
            b.append(el('span', { text: 'This issue is live on the website. What you change reaches visitors when you press "Save changes". Nothing is saved automatically while an issue is live.' }));
            b.append(el('a', { class: 'btn btn--outline btn--sm', href: it.url, target: '_blank', rel: 'noopener', text: 'View on the website' }));
        } else if (it.status === 'scheduled') {
            b.classList.add('nl-banner--info');
            b.append(el('span', { text: 'Scheduled: this issue goes live by itself on ' + fmtWall(it.publish_at) + ' (Kampala time). Until then it is hidden from visitors.' }));
        } else if (it.status === 'archived') {
            b.classList.add('nl-banner--muted');
            b.append(el('span', { text: 'Archived: the issue is kept here but not shown on the website.' }));
        } else {
            b.hidden = true;
            return;
        }
        b.hidden = false;
    }
    E.banner.addEventListener('click', (e) => {
        const t = e.target.closest('[data-backup]');
        if (!t) return;
        const offer = S.backupOffer;
        S.backupOffer = null;
        if (t.dataset.backup === 'restore' && offer) applyBackup(offer); else clearBackup();
        renderBanner();
    });
    function applyBackup(b) {
        const c = b.c;
        const it = Object.assign({}, S.item, {
            title: b.d.title, issue_number: b.d.issue_number === '' ? null : +b.d.issue_number, issue_date: b.d.issue_date, summary: b.d.summary,
            cover_image: b.d.cover_image, cover_alt: b.d.cover_alt, seo_title: b.d.seo_title, seo_description: b.d.seo_description, content: c,
        });
        const keepSaved = JSON.parse(JSON.stringify(S.saved));
        loadIssue(it, { keepSaved });
        toast('Your unsaved changes are back. Press Save to keep them.', { tone: 'success' });
    }

    function renderCheck() {
        const r = S.readiness || { blocking: {}, warnings: {} };
        const block = Object.entries(r.blocking || {});
        const tips = Object.entries(r.warnings || {});
        const live = !!S.item.is_live;
        E.checkList.textContent = '';
        E.check.dataset.state = block.length ? 'blocked' : (tips.length ? 'tips' : 'ready');
        if (block.length) E.checkTitle.textContent = block.length === 1 ? 'One thing to finish before you can publish' : block.length + ' things to finish before you can publish';
        else if (tips.length) E.checkTitle.textContent = (live ? 'This issue is complete. ' : 'Ready to publish. ') + (tips.length === 1 ? 'One suggestion:' : tips.length + ' suggestions:');
        else E.checkTitle.textContent = live ? 'This issue is complete' : 'Ready to publish: everything needed is filled in';
        const add = (path, msg, kind) => {
            const loc = locate(path);
            const li = el('li', { class: 'nl-check__item nl-check__item--' + kind });
            li.append(el('span', { class: 'nl-check__mark', 'aria-hidden': 'true', text: kind === 'block' ? '!' : 'i' }));
            li.append(el('span', { class: 'nl-check__text', text: (loc.where ? loc.where + ': ' : '') + msg }));
            li.append(el('button', { type: 'button', class: 'nl-linkbtn', 'data-goto': path, text: 'Go there' }));
            E.checkList.appendChild(li);
        };
        // a long list is cut after five lines ("Show all 58")
        const all = block.map(([p, m]) => [p, m, 'block']).concat(tips.map(([p, m]) => [p, m, 'tip']));
        const shown = S.checkAll ? all : all.slice(0, 5);
        shown.forEach(([p, m, kind]) => add(p, m, kind));
        if (all.length > 5) {
            const li = el('li', { class: 'nl-check__more' });
            li.appendChild(el('button', { type: 'button', class: 'nl-linkbtn', 'data-check-more': S.checkAll ? 'less' : 'more', text: S.checkAll ? 'Show fewer' : 'Show all ' + all.length }));
            E.checkList.appendChild(li);
        }
        E.checkList.hidden = !all.length;
    }

    // ---- clock of the site (Kampala): the page knows the time it was built, the browser clock keeps it moving
    const loadedAt = Date.now();
    function siteNow() {
        const base = parseWall(boot.now) || new Date();
        return new Date(base.getTime() + (Date.now() - loadedAt));
    }
    const toLocalInput = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());

    // ---- transitions
    async function transition(path, body, done) {
        const r = await api('POST', '/' + S.id + path, body || {});
        if (r.status === 200 && r.data && r.data.data) {
            takeItem(r.data.data);
            applyStatus();
            renderCheck();
            if (done) toast(done.text, Object.assign({ tone: 'success' }, done.opts || {}));
            announce(done ? done.text : 'Status changed');
            watchSchedule();
            schedulePreview();
            return { ok: true, data: r.data };
        }
        if (r.status === 422 && r.data && r.data.errors && Object.keys(r.data.errors).length) {
            showProblems(r.data.errors, r.data.message || 'This issue is not ready yet');
            return { ok: false, errors: r.data.errors };
        }
        if (r.login) { showDialog(dialog('session')); return { ok: false }; }
        if (r.network) { toast('You are offline. Nothing was changed.', { tone: 'error' }); return { ok: false }; }
        toast((r.data && r.data.message) || 'That change could not be made. Reload the page and try again.', { tone: 'error', timeout: 12000 });
        return { ok: false };
    }

    async function actSave() {
        const r = await saveNow('manual');
        if (r.ok) announce(r.nothing ? 'Everything is already saved' : 'Saved');
    }
    async function actPublish() {
        const blocking = Object.entries((S.readiness && S.readiness.blocking) || {});
        if (blocking.length) {
            const o = {};
            blocking.forEach(([p, m]) => { o[p] = m; });
            showProblems(o, 'This issue is not ready to publish yet');
            return;
        }
        const body = el('div');
        const d = collectDetails();
        body.append(el('p', { text: '"' + (d.title || 'This issue') + '" will appear on the website right away, at ' + S.item.url + '.' }));
        body.append(el('p', { text: boot.can.send ? 'Subscribers are not e-mailed automatically. After publishing you can send it from the E-mail button.' : 'Subscribers are not e-mailed automatically. An administrator can send it by e-mail after it is published.' }));
        const tips = Object.entries((S.readiness && S.readiness.warnings) || {});
        if (tips.length) {
            body.append(el('p', { class: 'nl-dialog__lead', text: 'You can publish now, or fix these suggestions first:' }));
            body.append(el('ul', { class: 'nl-dialog__list' }, tips.map(([p, m]) => el('li', { text: (locate(p).where ? locate(p).where + ': ' : '') + m }))));
        }
        const ok = await confirmDialog({ title: 'Publish this issue now?', body, okLabel: 'Publish now', tone: 'cta' });
        if (!ok) return;
        const sv = await saveNow('manual');
        if (!sv.ok) return;
        const label = S.item.status === 'scheduled' ? 'Published. The schedule was cancelled.' : 'Published. The issue is live on the website.';
        const t = await transition('/publish', {}, { text: label, opts: { href: S.item.url, actionLabel: 'View it', target: '_blank', timeout: 10000 } });
        if (t.ok && boot.can.send && boot.urls.email) {
            toast('Want subscribers to get it too?', { tone: 'info', href: boot.urls.email, actionLabel: 'Open the e-mail page', timeout: 12000 });
        }
    }

    // ---- scheduling
    const schedDlg = dialog('schedule');
    const schedIn = $('[data-schedule-input]', schedDlg);
    const schedSummary = $('[data-schedule-summary]', schedDlg);
    const schedErr = $('[data-schedule-error]', schedDlg);
    function schedCheck() {
        const v = schedIn.value;
        schedErr.hidden = true;
        if (!v) { schedSummary.textContent = ''; return ''; }
        const d = parseWall(v);
        if (d && d.getTime() <= siteNow().getTime()) { schedErr.textContent = 'Choose a time in the future, or publish now.'; schedErr.hidden = false; schedSummary.textContent = ''; return 'past'; }
        schedSummary.textContent = 'The issue will go live on ' + fmtWall(v) + ' (Kampala time).';
        return '';
    }
    schedIn.addEventListener('input', schedCheck);
    schedIn.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); $('[data-schedule-ok]', schedDlg).click(); } });
    async function actSchedule() {
        const blocking = Object.entries((S.readiness && S.readiness.blocking) || {});
        if (blocking.length) {
            const o = {};
            blocking.forEach(([p, m]) => { o[p] = m; });
            showProblems(o, 'This issue is not ready to schedule yet');
            return;
        }
        const now = siteNow();
        schedIn.min = toLocalInput(now);
        if (S.item.status === 'scheduled' && S.item.publish_at) schedIn.value = String(S.item.publish_at).slice(0, 16).replace(' ', 'T');
        else { const t = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 9, 0); schedIn.value = toLocalInput(t); }
        $('[data-schedule-now]', schedDlg).textContent = fmtWall(toLocalInput(now));
        $('[data-tz-name]', schedDlg).textContent = boot.tz || 'Africa/Kampala';
        $('[data-schedule-ok]', schedDlg).textContent = S.item.status === 'scheduled' ? 'Change the time' : 'Schedule issue';
        schedCheck();
        schedDlg.returnValue = '';
        showDialog(schedDlg);
    }
    $('[data-schedule-form]', schedDlg).addEventListener('submit', async (e) => {
        if (e.submitter && e.submitter.value === 'cancel') return;
        e.preventDefault();
        if (!schedIn.value) { schedErr.textContent = 'Choose a date and a time.'; schedErr.hidden = false; schedIn.focus(); return; }
        if (schedCheck()) { schedIn.focus(); return; }
        const okBtn = $('[data-schedule-ok]', schedDlg);
        okBtn.disabled = true;
        try {
            const sv = await saveNow('manual');
            if (!sv.ok) { schedDlg.close('cancel'); return; }
            if (S.item.status === 'scheduled') {
                const back = await transition('/unpublish', {}, null);          // a schedule is changed by cancelling it and setting a new one
                if (!back.ok) { schedDlg.close('cancel'); return; }
            }
            const r = await api('POST', '/' + S.id + '/schedule', { publish_at: schedIn.value });
            if (r.status === 200 && r.data && r.data.data) {
                takeItem(r.data.data);
                applyStatus();
                renderCheck();
                watchSchedule();
                schedDlg.close('ok');
                toast('Scheduled for ' + fmtWall(S.item.publish_at) + ' (Kampala time).', { tone: 'success' });
                announce('Issue scheduled');
            } else if (r.status === 422 && r.data && r.data.errors && r.data.errors.publish_at) {
                schedErr.textContent = r.data.errors.publish_at;
                schedErr.hidden = false;
                schedIn.focus();
            } else if (r.status === 422 && r.data && r.data.errors) {
                schedDlg.close('cancel');
                showProblems(r.data.errors, 'This issue is not ready to schedule yet');
            } else {
                schedErr.textContent = (r.data && r.data.message) || (r.network ? 'You are offline. Try again when you are back online.' : 'The issue could not be scheduled. Please try again.');
                schedErr.hidden = false;
            }
        } finally {
            okBtn.disabled = false;
        }
    });

    // a scheduled issue goes live by itself: when its time comes, ask the server for the new status
    let scheduleTimer = 0;
    function watchSchedule() {
        clearTimeout(scheduleTimer);
        if (S.item.status !== 'scheduled' || !S.item.publish_at) return;
        const due = parseWall(S.item.publish_at);
        if (!due) return;
        const ms = due.getTime() - siteNow().getTime() + 2500;
        if (ms > 2147000000) return;
        scheduleTimer = setTimeout(async () => {
            const r = await api('GET', '/' + S.id);
            if (r.status === 200 && r.data && r.data.data) {
                takeItem(r.data.data);
                applyStatus();
                if (S.item.is_live) toast('This issue has just gone live on the website.', { tone: 'success', href: S.item.url, actionLabel: 'View it', target: '_blank' });
                else watchSchedule();
            }
        }, Math.max(ms, 3000));
    }

    async function actUnpublish() {
        const st = S.item.status;
        const mailed = S.item.email && (S.item.email.status === 'sent' || S.item.email.sent > 0);
        let title; let text; let ok; let done;
        if (st === 'published') {
            title = 'Unpublish this issue?';
            text = 'The issue disappears from the website and from the archive, and goes back to being a draft. Anyone with a link will see a "page not found" message until you publish it again.' + (mailed ? '\n\nThis issue has already been e-mailed. The "view in browser" link in those e-mails will not work while it is unpublished.' : '');
            ok = 'Unpublish'; done = 'Unpublished. The issue is a draft again.';
        } else if (st === 'scheduled') {
            title = 'Cancel the schedule?'; text = 'The issue will not go live by itself. It goes back to being a draft.'; ok = 'Cancel schedule'; done = 'The schedule was cancelled. The issue is a draft.';
        } else {
            title = 'Move this issue to drafts?'; text = 'The issue becomes a draft again, so you can edit and publish it.'; ok = 'Move to drafts'; done = 'The issue is a draft again.';
        }
        const yes = await confirmDialog({ title, body: text, okLabel: ok, tone: st === 'published' ? 'danger' : 'primary' });
        if (!yes) return;
        if (S.dirty) { const sv = await saveNow('manual'); if (!sv.ok) return; }
        await transition('/unpublish', {}, { text: done });
    }
    async function actArchive() {
        const yes = await confirmDialog({
            title: 'Archive this issue?',
            body: 'The issue is kept here but taken off the website.' + (S.item.status === 'published' ? '\n\nIt will no longer be visible to visitors.' : ''),
            okLabel: 'Archive issue',
        });
        if (!yes) return;
        await transition('/archive', {}, { text: 'Archived. The issue is no longer on the website.' });
    }
    async function actDelete() {
        if (everPublic() && !boot.can.delete_published) { toast('Only an administrator can delete an issue that has been published. You can unpublish it instead.', { tone: 'error' }); return; }
        const em = S.item.email && ['queued', 'sending', 'paused'].indexOf(S.item.email.status) >= 0;
        if (em) { toast('The e-mail campaign of this issue is still running. Cancel it first (E-mail button).', { tone: 'error' }); return; }
        const d = collectDetails();
        const yes = await confirmDialog({
            title: 'Delete this issue for good?',
            body: '"' + (d.title || 'This issue') + '" and all its saved versions will be deleted. This cannot be undone.' + (S.item.is_live ? '\n\nThe issue is live, so the page will disappear from the website straight away.' : '') + (S.item.is_live || everPublic() ? '\n\nTo keep it but hide it, use Unpublish or Archive instead.' : ''),
            okLabel: 'Delete issue', tone: 'danger',
        });
        if (!yes) return;
        S.guard = false;
        clearBackup();
        $('[data-delete-form]').submit();
    }
    async function actDuplicate() {
        if (S.dirty && autoServer()) { const sv = await saveNow('manual'); if (!sv.ok) return; }
        else if (S.dirty) {
            const yes = await confirmDialog({ title: 'Make a copy of the saved version?', body: 'You have changes that are not saved yet. The copy is made from the last saved version, without them.', okLabel: 'Make the copy' });
            if (!yes) return;
        }
        S.guard = false;
        $('[data-duplicate-form]').submit();
    }
    async function actPreview() {
        const w = window.open('', '_blank');
        if (w) { try { w.opener = null; } catch (_) { /* ignore */ } try { w.document.title = 'Preview'; w.document.body.textContent = 'Opening the preview...'; } catch (_) { /* ignore */ } }
        if (S.dirty && autoServer()) {
            const sv = await saveNow('manual');
            if (!sv.ok) { if (w) w.close(); return; }
        } else if (S.dirty) {
            toast('This tab shows the last saved version. Your unsaved changes are visible in the live preview here.', { tone: 'info', timeout: 8000 });
        }
        if (w) w.location.href = boot.urls.preview;
        else toast('Your browser blocked the new tab.', { tone: 'warning', href: boot.urls.preview, actionLabel: 'Open the preview', target: '_blank', timeout: 15000 });
    }

    root.addEventListener('click', (e) => {
        const b = e.target.closest('button[data-act], a[data-act]');
        if (!b || b.disabled) return;
        switch (b.dataset.act) {
            case 'save': actSave(); break;
            case 'publish': actPublish(); break;
            case 'schedule': actSchedule(); break;
            case 'unpublish': actUnpublish(); break;
            case 'archive': actArchive(); break;
            case 'delete': actDelete(); break;
            case 'duplicate': actDuplicate(); break;
            case 'preview': actPreview(); break;
            case 'history': openHistory(); break;
            default:
        }
    });
    root.addEventListener('click', (e) => {
        const b = e.target.closest('[data-view]');
        if (b && b.tagName === 'BUTTON') { setView(b.dataset.view); return; }
        const dv = e.target.closest('[data-device]');
        if (dv) setDevice(dv.dataset.device, { user: true });
    });
    // the e-mail page is another page: a draft is saved first (a live issue keeps its safety copy and the browser asks before leaving)
    root.addEventListener('click', async (e) => {
        const a = e.target.closest('a[data-act="email"]');
        if (!a || !S.dirty) return;
        backupLocal();
        if (!autoServer() || e.ctrlKey || e.metaKey || e.shiftKey) return;
        e.preventDefault();
        const sv = await saveNow('manual');
        if (sv.ok) window.location.assign(a.href);
    });

    // ---- reload the whole form from a server item (start-up, "use their version", restore)
    function adoptTheirDetails(item) {
        const theirs = detailsOfItem(item);
        const cur = collectDetails();
        const inputs = { title: D.title, issue_number: D.number, issue_date: D.date, summary: D.summary, slug: D.slug, seo_title: D.seoTitle, seo_description: D.seoDesc };
        Object.keys(inputs).forEach((k) => { if (cur[k] === S.saved.d[k]) inputs[k].value = theirs[k]; });
        if (cur.cover_image === S.saved.d.cover_image && cur.cover_alt === S.saved.d.cover_alt) setImage(D.cover, { path: theirs.cover_image, alt: theirs.cover_alt });
        S.saved.d = theirs;
        refreshCounters();
        updateDetailsUi();
    }
    function loadIssue(item, o = {}) {
        blockCards().forEach((c) => $$('textarea[data-richtext]', c).forEach((ta) => { if (window.LuminaEditor) window.LuminaEditor.destroy(ta); }));
        E.blocks.textContent = '';
        const c = item.content;
        if (!o.contentOnly) {
            D.title.value = item.title || '';
            D.number.value = item.issue_number == null ? '' : String(item.issue_number);
            D.date.value = item.issue_date || '';
            D.summary.value = item.summary || '';
            D.slug.value = item.slug || '';
            D.seoTitle.value = item.seo_title || '';
            D.seoDesc.value = item.seo_description || '';
            setImage(D.cover, { path: item.cover_image || '', alt: item.cover_alt || '' });
        }
        FEAT_F.heading.value = c.featured.heading || '';
        setRich(FEAT_F.body, c.featured.body_html);
        setImage(FEAT_F.image, c.featured.image);
        setLink(FEAT_F.link, c.featured.link);
        c.blocks.forEach((b) => addBlock(b.type, { data: b, silent: true }));
        const cards = blockCards();
        cards.forEach((card, i) => setOpen(card, cards.length <= 5 || i === 0));
        if (!o.keepSaved) {
            takeItem(item);
            S.saved = { d: detailsOfItem(item), c: JSON.stringify(collectContent()) };
            S.needsStrict = false;
        } else {
            S.saved = o.keepSaved;
        }
        S.conflict = null;
        clearAllErrors();
        refreshBlocks();
        refreshCounters();
        updateDetailsUi();
        applyStatus();
        renderCheck();
        setDirty(isDirty());
        refreshPreview();
    }

    // ------------------------------------------------------------------------------------------------ 8. history (revisions drawer)
    const SOURCE = { save: 'Saved', autosave: 'Autosaved', publish: 'Published', restore: 'Restored' };
    const histDlg = dialog('history');
    const histBody = $('[data-history-body]', histDlg);
    $('[data-history-close]', histDlg).addEventListener('click', () => histDlg.close());
    histDlg.addEventListener('click', (e) => {
        if (e.target === histDlg) histDlg.close();                  // click on the dimmed area
    });

    async function openHistory() {
        histBody.textContent = 'Loading the saved versions...';
        showDialog(histDlg);
        if (S.dirty) {
            // the list shows what the SERVER holds; remind people that their unsaved work is not in it
        }
        const r = await api('GET', '/' + S.id + '/revisions');
        if (r.status !== 200 || !r.data) {
            histBody.textContent = r.network ? 'You are offline, so the history cannot be loaded.' : 'The history could not be loaded. Please close this panel and try again.';
            return;
        }
        renderHistory(r.data.data || []);
    }
    function renderHistory(rows) {
        histBody.textContent = '';
        if (S.dirty) histBody.appendChild(el('p', { class: 'nl-note', text: 'You have changes that are not saved yet. They are not in this list.' }));
        if (!rows.length) { histBody.appendChild(el('p', { text: 'There are no saved versions yet.' })); return; }
        const ul = el('ul', { class: 'nl-history' });
        rows.forEach((row, i) => {
            const li = el('li', { class: 'nl-history__item' });
            const btn = el('button', { type: 'button', class: 'nl-history__btn', 'aria-expanded': 'false', 'data-rev': row.id });
            btn.append(
                el('span', { class: 'nl-history__when', text: ago(row.created_at) }),
                el('span', { class: 'nl-history__meta', text: (SOURCE[row.source] || 'Saved') + (row.saved_by_name ? ' by ' + row.saved_by_name : '') + (i === 0 ? ' · latest' : '') }),
            );
            li.appendChild(btn);
            li.appendChild(el('div', { class: 'nl-history__detail', hidden: true }));
            ul.appendChild(li);
        });
        histBody.appendChild(ul);
    }
    histBody.addEventListener('click', async (e) => {
        const btn = e.target.closest('.nl-history__btn');
        if (btn) {
            const detail = btn.nextElementSibling;
            const open = btn.getAttribute('aria-expanded') === 'true';
            $$('.nl-history__btn', histBody).forEach((b) => { b.setAttribute('aria-expanded', 'false'); b.nextElementSibling.hidden = true; });
            if (open) return;
            btn.setAttribute('aria-expanded', 'true');
            detail.hidden = false;
            detail.textContent = 'Comparing...';
            const r = await api('GET', '/' + S.id + '/revisions/' + btn.dataset.rev);
            if (r.status !== 200 || !r.data) { detail.textContent = 'This version could not be loaded.'; return; }
            renderDiff(detail, r.data, +btn.dataset.rev);
            return;
        }
        const restore = e.target.closest('[data-restore]');
        if (restore) doRestore(+restore.dataset.restore);
    });
    function renderDiff(box, payload, rid) {
        box.textContent = '';
        const diff = payload.diff || [];
        if (!diff.length) box.appendChild(el('p', { class: 'nl-history__same', text: 'This is the same text as the version saved now.' }));
        else {
            box.appendChild(el('p', { class: 'nl-history__lead', text: 'If you bring this version back:' }));
            const ul = el('ul', { class: 'nl-diff' });
            diff.forEach((d) => {
                const verb = d.kind === 'added' ? 'Comes back' : d.kind === 'removed' ? 'Goes away' : 'Changes';
                const li = el('li', { class: 'nl-diff__item nl-diff__item--' + d.kind });
                li.append(el('strong', { text: verb + ': ' }), el('span', { text: d.where }));
                if (d.kind === 'changed' && (d.from || d.to)) {
                    const t = el('span', { class: 'nl-diff__text' });
                    t.append(el('span', { class: 'nl-diff__from', text: 'now: ' + (d.from || '(empty)') }));
                    t.append(el('span', { class: 'nl-diff__to', text: 'then: ' + (d.to || '(empty)') }));
                    li.appendChild(t);
                }
                ul.appendChild(li);
            });
            box.appendChild(ul);
        }
        box.appendChild(el('button', { type: 'button', class: 'btn btn--outline btn--sm', 'data-restore': String(rid), text: 'Bring this version back' }));
    }
    async function doRestore(rid) {
        const warn = S.dirty ? '\n\nYou also have changes that are not saved yet. The text and sections you are editing now will be replaced.' : '';
        const yes = await confirmDialog({
            title: 'Bring this version back?',
            body: 'The text and sections of the issue are replaced with this saved version. The version you have now stays in History, so you can undo this.' + warn,
            okLabel: 'Bring it back',
        });
        if (!yes) return;
        const r = await api('POST', '/' + S.id + '/revisions/' + rid + '/restore', { version: S.version });
        if (r.status === 200 && r.data && r.data.data) {
            histDlg.close();
            loadIssue(r.data.data, { contentOnly: true });
            S.lastSavedAt = new Date();
            clearBackup();
            renderSave();
            toast('The earlier version is back. The one you had before is still in History.', { tone: 'success' });
            return;
        }
        if (r.status === 409 && r.data && (!r.data.error || r.data.error === 'conflict')) { histDlg.close(); S.conflict = r.data; renderSave(); openConflict(); return; }
        if (r.status === 422 && r.data && r.data.error === 'not_ready') {
            toast('That version is not complete, so it cannot replace the live issue. Unpublish the issue first, or pick a complete version.', { tone: 'error', timeout: 10000 });
            return;
        }
        toast((r.data && r.data.message) || 'That version could not be brought back.', { tone: 'error' });
    }

    // ------------------------------------------------------------------------------------------------ 9. start-up
    // keyboard: Left / Right between the two view buttons is not needed (they are plain toggle buttons)
    (function stickyHeights() {
        if (!window.ResizeObserver) return;
        const topbar = doc.querySelector('.admin-topbar');
        const set = () => {
            const h = Math.round(E.bar.getBoundingClientRect().height);
            root.style.setProperty('--nl-bar-h', h + 'px');
            // what sticks to the top of the screen (the admin top bar, plus this bar when it is sticky): a focused field must scroll below it
            const stuck = (topbar ? topbar.getBoundingClientRect().height : 64) + (window.getComputedStyle(E.bar).position === 'sticky' ? h : 0);
            document.documentElement.style.setProperty('--nl-scroll-pad', Math.round(stuck + 16) + 'px');
        };
        new ResizeObserver(set).observe(E.bar);
        window.addEventListener('resize', debounce(set, 100));
        set();
    }());
    const content = doc.querySelector('.admin-content');
    if (content) content.classList.add('admin-content--wide');

    initImage(D.cover);
    initImage(FEAT_F.image);
    initCounters(root);
    $$('input[type="number"]', root).forEach((i) => i.addEventListener('wheel', () => i.blur(), { passive: true }));
    loadIssue(S.item);
    setDevice(S.device, { silent: true });
    layoutPreview();
    watchSchedule();
    renderSave();

    // a safety copy from an earlier session that was never saved?
    (function offerBackup() {
        const b = readBackup();
        if (!b || !b.d || !b.c) return;
        if (b.v !== S.version || (JSON.stringify(b.c) === S.saved.c && JSON.stringify(b.d) === JSON.stringify(S.saved.d))) { clearBackup(); return; }
        S.backupOffer = b;
        renderBanner();
    }());

    root.classList.add('is-ready');
    // expose a tiny hook for tests and for the e-mail panel (read only)
    window.LuminaNewsletterEditor = { state: S, collect, save: saveNow, refreshPreview };
}());
