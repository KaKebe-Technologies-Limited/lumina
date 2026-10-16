/*!
 * Lumina rich-text editor  -  assets/js/admin-editor.js  (OWNER: B4 "admin-content")
 * Dependency-free, ES2020, no inline code, CSP-safe. Progressive enhancement: every <textarea data-richtext> becomes a visual editor;
 * without JavaScript the plain textarea still works. The server ALWAYS runs Sanitizer::html() on save - this script only helps authors.
 *
 * MARKUP IT UPGRADES (partials/admin/richtext-field.php prints it):
 *     <label for="f-body">Text</label><textarea id="f-body" name="body" data-richtext data-placeholder="Write here...">...stored html...</textarea>
 * It inserts   div.editor[data-editor] > div.editor__toolbar[role=toolbar] + div.editor__panel + div.editor__area[contenteditable] + the textarea
 * (kept as the form field and shown as the HTML source view) + div.editor__status.   Styles: scss/admin/_editor.scss + _content.scss.
 *
 * FEATURES: bold, italic, underline, headings 2/3, bulleted/numbered lists, quote, link (Ctrl+K), picture upload (button, drop or paste;
 *   click a picture to select it, double-click / Enter to edit its description and placement), clear formatting, undo/redo, HTML source toggle,
 *   paste-as-clean (Word / Google Docs / web pages are reduced to the tags the server keeps; Ctrl+Shift+V pastes plain text).
 *   Keyboard: toolbar is one tab stop, arrow keys move between buttons; Esc closes a panel; Tab leaves the editor (it is never trapped).
 *
 * PUBLIC API (window.LuminaEditor) - for the Pages editor (B3) and any other screen:
 *     LuminaEditor.init(target)       target = textarea element | CSS selector | container (upgrades every textarea[data-richtext] inside). Idempotent.
 *     LuminaEditor.initAll(root)      same for a root element (default document). Called automatically on load AND whenever new
 *                                     textarea[data-richtext] elements appear in the page (repeater rows, AJAX), so normally you call nothing.
 *     LuminaEditor.sync(target?)      write the visual content into the textarea(s) NOW (before reading .value from your own JS).
 *     LuminaEditor.refresh(target?)   reload the visual editor from the textarea (after you changed textarea.value from script, e.g. "reset to default").
 *     LuminaEditor.setValue(textarea, html)   shortcut: sets textarea.value and refreshes.
 *     LuminaEditor.get(textarea)      the editor instance (focus(), isEmpty(), getHTML(), setHTML(html), toggleSource(), destroy()).
 *     LuminaEditor.destroy(textarea)  remove the editor and show the plain textarea again.
 * EVENTS (bubble from the textarea): 'lumina:editor-ready', 'lumina:editor-change' (user edited), 'lumina:editor-mode' ({detail:{mode:'visual'|'source'}}).
 * FORMS: content is written to the textarea on every edit (debounced), on blur and on submit; a `required` textarea is validated by the editor
 *   itself (a hidden control cannot be focused by the browser). Inputs inside the editor panels have no name and never reach the server.
 */
(() => {
    'use strict';
    if (window.LuminaEditor && window.LuminaEditor.version) return;      // loaded twice

    const VERSION = '1.0.0';
    const doc = document;
    const $ = (sel, root = doc) => root.querySelector(sel);
    const $$ = (sel, root = doc) => Array.from(root.querySelectorAll(sel));
    let counter = 0;
    const uid = (p) => `${p}-${(++counter).toString(36)}${Math.random().toString(36).slice(2, 6)}`;
    const baseUrl = () => { const m = $('meta[name="base-url"]'); return m ? String(m.content || '').replace(/\/+$/, '') : ''; };
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform || '');
    const MOD = isMac ? 'Cmd' : 'Ctrl';

    /** tiny element builder: h('div', {class:'x', hidden:true}, 'text', childEl) */
    function h(tag, attrs, ...kids) {
        const el = doc.createElement(tag);
        for (const [k, v] of Object.entries(attrs || {})) {
            if (v === false || v === null || v === undefined) continue;
            el.setAttribute(k, v === true ? '' : String(v));
        }
        for (const k of kids.flat()) {
            if (k === null || k === undefined || k === false) continue;
            el.appendChild(typeof k === 'string' ? doc.createTextNode(k) : k);
        }
        return el;
    }

    const svg = (inner) => `<svg class="icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">${inner}</svg>`;
    const ICONS = {
        undo: svg('<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>'),
        redo: svg('<path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 15-6.7L21 13"/>'),
        ul: svg('<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/>'),
        ol: svg('<line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>'),
        quote: svg('<path d="M3 21c3 0 7-1 7-8V5H4v6h4"/><path d="M14 21c3 0 7-1 7-8V5h-6v6h4"/>'),
        link: svg('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'),
        unlink: svg('<path d="M18.84 12.25l1.72-1.71a5 5 0 0 0-7.07-7.07l-1.71 1.71"/><path d="M5.17 11.75l-1.71 1.71a5 5 0 0 0 7.07 7.07l1.71-1.71"/><line x1="8" y1="2" x2="8" y2="5"/><line x1="2" y1="8" x2="5" y2="8"/><line x1="16" y1="19" x2="16" y2="22"/><line x1="19" y1="16" x2="22" y2="16"/>'),
        image: svg('<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'),
        clear: svg('<path d="M4 7V4h16v3"/><path d="M5 20h6"/><path d="M13 4 8 20"/><path d="m15 15 5 5"/><path d="m20 15-5 5"/>'),
        code: svg('<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>'),
    };

    // ------------------------------------------------------------------------------------------------ client-side clean-up
    // Mirrors app/core/Sanitizer.php (allow-list) so a round trip through the editor never loses supported content and pasted junk never
    // reaches the textarea. It is a convenience only: the server sanitises again.
    const DROP = new Set(['script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'input', 'button', 'textarea', 'select', 'option',
        'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template', 'head', 'title', 'video', 'audio', 'canvas', 'source', 'track', 'map', 'area', 'dialog', 'portal']);
    const RENAME = { b: 'strong', i: 'em', h1: 'h2', h5: 'h4', h6: 'h4', div: 'p', strike: 'u', s: 'u', ins: 'u', center: 'p', section: 'p', article: 'p', header: 'p', footer: 'p', main: 'p', aside: 'p' };
    const ALLOWED = {
        p: ['class'], br: [], strong: [], em: [], u: [], ul: [], ol: [], li: [], a: ['href', 'title', 'target', 'rel', 'class'], h2: [], h3: [], h4: [], blockquote: [],
        img: ['src', 'alt', 'title', 'width', 'height', 'class'], figure: ['class'], figcaption: [], hr: [], code: [], pre: [], table: ['class'], thead: [], tbody: [], tfoot: [],
        tr: [], th: ['colspan', 'rowspan', 'scope'], td: ['colspan', 'rowspan'], caption: [],
    };
    const CLASS_ALLOW = new Set(['lead', 'text-center', 'text-right', 'text-left', 'align-left', 'align-center', 'align-right', 'btn', 'btn--primary', 'btn--outline',
        'img-left', 'img-right', 'img-full', 'table', 'table--striped']);
    const BLOCKS = new Set(['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'blockquote', 'figure', 'hr', 'pre', 'table']);

    function collapseLocal(v) {
        const base = baseUrl();
        if (/^https?:\/\//i.test(v)) {
            const o = location.origin.toLowerCase();
            if (v.toLowerCase() === o || v.toLowerCase().startsWith(o + '/')) v = v.slice(location.origin.length) || '/'; else return v;
        }
        if (v[0] !== '/' && v[0] !== '?') v = '/' + v;
        if (base && (v === base || ['/', '?', '#'].some((c) => v.startsWith(base + c)))) v = v.slice(base.length) || '/';
        return v;
    }
    function expandLocal(v) {
        const base = baseUrl();
        if (v[0] !== '/' && v[0] !== '?') v = '/' + v;
        if (v[0] === '/' && base && !(v === base || v.startsWith(base + '/'))) v = base + v;
        return v;
    }
    /** mode 'store' = form stored in the database (root-relative), 'edit' = form shown inside the editor (with the base path). */
    function cleanHref(value, mode) {
        const v = String(value || '').replace(/[\u0000-\u001F\u007F\s]+/g, '');
        if (!v) return '';
        if (v[0] === '#') return v;
        const m = /^([a-z][a-z0-9+.\-]*):/i.exec(v);
        if (m) {
            const scheme = m[1].toLowerCase();
            if (scheme === 'mailto' || scheme === 'tel') return v;
            if (scheme === 'http' || scheme === 'https') return mode === 'edit' ? v : collapseLocal(v);
            return '';
        }
        if (v.startsWith('//')) return '';
        return mode === 'edit' ? expandLocal(v) : collapseLocal(v);
    }
    function cleanImgSrc(value, mode) {
        let v = String(value || '').replace(/[\u0000-\u001F\u007F\s]+/g, '');
        if (!v || v.startsWith('//') || /^(data|javascript|vbscript|blob|file):/i.test(v)) return '';
        if (/^https?:\/\//i.test(v)) {
            const o = location.origin.toLowerCase();
            if (v.toLowerCase().startsWith(o + '/')) v = v.slice(location.origin.length); else return '';   // external pictures are rejected by the server too
        } else if (/^[a-z][a-z0-9+.\-]*:/i.test(v)) return '';
        return mode === 'edit' ? expandLocal(v) : collapseLocal(v);
    }
    const plain = (s) => String(s || '').replace(/\s+/g, ' ').trim();

    function styledWrappers(node) {
        const st = (node.getAttribute && node.getAttribute('style')) || '';
        const wrap = [];
        const weight = /font-weight\s*:\s*([a-z0-9]+)/i.exec(st);
        if (weight && (/^(bold|bolder)$/i.test(weight[1]) || parseInt(weight[1], 10) >= 600)) wrap.push('strong');
        if (/font-style\s*:\s*italic/i.test(st)) wrap.push('em');
        if (/text-decoration[^;]*underline/i.test(st)) wrap.push('u');
        return wrap;
    }

    function cleanChildren(src, dest, mode, opts) {
        for (const n of Array.from(src.childNodes)) cleanNode(n, dest, mode, opts);
    }

    function cleanNode(node, dest, mode, opts) {
        if (node.nodeType === 3) { dest.appendChild(doc.createTextNode(node.nodeValue)); return; }
        if (node.nodeType !== 1) return;
        let tag = node.nodeName.toLowerCase();
        if (DROP.has(tag)) return;
        if (tag === 'span' || tag === 'font') {                                    // formatting carried by styles (Word, Google Docs)
            let target = dest;
            for (const w of styledWrappers(node)) { const el = doc.createElement(w); target.appendChild(el); target = el; }
            cleanChildren(node, target, mode, opts);
            return;
        }
        if (tag === 'b' && /font-weight\s*:\s*(normal|400)/i.test(node.getAttribute('style') || '')) { cleanChildren(node, dest, mode, opts); return; }   // Docs wrapper
        if (RENAME[tag]) tag = RENAME[tag];
        if (!ALLOWED[tag]) { cleanChildren(node, dest, mode, opts); return; }       // unknown wrapper: keep the content
        const el = doc.createElement(tag);
        for (const name of ALLOWED[tag]) {
            const raw = node.getAttribute(name);
            if (raw === null) continue;
            if (name === 'href') { const v = cleanHref(raw, mode); if (v) el.setAttribute('href', v); }
            else if (name === 'src') { const v = cleanImgSrc(raw, mode); if (v) el.setAttribute('src', v); }
            else if (name === 'target') { if (raw === '_blank') el.setAttribute('target', '_blank'); }
            else if (name === 'rel') { /* recomputed below */ }
            else if (name === 'class') { const keep = raw.split(/\s+/).filter((c) => CLASS_ALLOW.has(c)); if (keep.length) el.setAttribute('class', keep.join(' ')); }
            else if (['width', 'height', 'colspan', 'rowspan'].includes(name)) { if (/^\d{1,4}$/.test(raw) && +raw > 0) el.setAttribute(name, raw); }
            else if (name === 'scope') { if (['row', 'col', 'rowgroup', 'colgroup'].includes(raw)) el.setAttribute('scope', raw); }
            else if (name === 'alt' || name === 'title') el.setAttribute(name, plain(raw));
        }
        if (tag === 'img') {
            if (!el.hasAttribute('src')) return;
            if (!el.hasAttribute('alt')) el.setAttribute('alt', '');
        }
        if (tag === 'a') {
            if (!el.hasAttribute('href')) { cleanChildren(node, dest, mode, opts); return; }
            if (/^https?:\/\//i.test(el.getAttribute('href'))) el.setAttribute('rel', 'noopener noreferrer');
        }
        if (tag !== 'br' && tag !== 'img' && tag !== 'hr') cleanChildren(node, el, mode, opts);
        dest.appendChild(el);
    }

    function normaliseBlocks(root) {
        let run = null;
        for (const n of Array.from(root.childNodes)) {
            const isBlock = n.nodeType === 1 && BLOCKS.has(n.nodeName.toLowerCase());
            if (isBlock) { run = null; continue; }
            if (n.nodeType === 3 && !n.nodeValue.trim() && !run) { root.removeChild(n); continue; }
            if (!run) { run = doc.createElement('p'); root.insertBefore(run, n); }
            run.appendChild(n);
        }
    }
    function dropEmptyParagraphs(root) {
        $$('p', root).forEach((p) => {
            if (!p.textContent.replace(/[\s ]+/g, '') && !p.querySelector('img')) p.remove();
        });
    }

    /** Clean an HTML string. mode 'store' (database form) | 'edit' (editor form). opts.paste unwraps Word / Docs fragments. */
    function cleanHtml(html, mode = 'store', opts = {}) {
        html = String(html || '');
        if (opts.paste) {
            const a = html.indexOf('<!--StartFragment-->');
            const b = html.indexOf('<!--EndFragment-->');
            if (a > -1 && b > a) html = html.slice(a + 20, b);
        }
        const parsed = new DOMParser().parseFromString('<!doctype html><html><body>' + html + '</body></html>', 'text/html');   // inert document: nothing runs
        const holder = doc.createElement('div');
        cleanChildren(parsed.body, holder, mode, opts);
        if (!opts.inline) normaliseBlocks(holder);
        if (mode === 'store') dropEmptyParagraphs(holder);
        return holder.innerHTML;
    }

    const plainToHtml = (text) => {
        const paras = String(text || '').replace(/\r\n?/g, '\n').trim().split(/\n{2,}/).filter((p) => p.trim() !== '');
        const e = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return paras.map((p) => '<p>' + e(p.trim()).replace(/\n/g, '<br>') + '</p>').join('');
    };

    // ------------------------------------------------------------------------------------------------ uploads
    async function uploadImage(file) {
        if (window.LuminaAdmin && typeof window.LuminaAdmin.uploadImage === 'function') return window.LuminaAdmin.uploadImage(file);
        const token = ($('meta[name="csrf-token"]') || {}).content || '';
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', token);
        const res = await fetch(`${baseUrl()}/admin/media/upload`, { method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token, Accept: 'application/json' } });
        let data = {};
        try { data = await res.json(); } catch (_) { /* ignore */ }
        if (!res.ok) throw new Error(data.error || data.message || 'Upload failed.');
        return data;
    }
    const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    function checkImageFile(file) {
        if (!file) return 'Choose a picture first.';
        if (!IMAGE_TYPES.includes(file.type)) return 'Only JPG, PNG, GIF or WebP pictures can be added.';
        if (file.size > 5 * 1024 * 1024) return 'That picture is larger than 5 MB.';
        return '';
    }

    // ------------------------------------------------------------------------------------------------ the editor
    const TOOLS = [
        { group: 'History', items: [
            { id: 'undo', label: 'Undo', icon: 'undo', cmd: 'undo' },
            { id: 'redo', label: 'Redo', icon: 'redo', cmd: 'redo' },
        ] },
        { group: 'Text style', items: [
            { id: 'bold', label: 'Bold', text: 'B', cls: 'editor__btn--bold', cmd: 'bold', toggle: true, key: `${MOD}+B` },
            { id: 'italic', label: 'Italic', text: 'I', cls: 'editor__btn--italic', cmd: 'italic', toggle: true, key: `${MOD}+I` },
            { id: 'underline', label: 'Underline', text: 'U', cls: 'editor__btn--underline', cmd: 'underline', toggle: true, key: `${MOD}+U` },
        ] },
        { group: 'Headings and quote', items: [
            { id: 'h2', label: 'Heading', text: 'H2', block: 'h2', toggle: true, cls: 'editor__btn--wide' },
            { id: 'h3', label: 'Subheading', text: 'H3', block: 'h3', toggle: true, cls: 'editor__btn--wide' },
            { id: 'quote', label: 'Quote', icon: 'quote', toggle: true },
        ] },
        { group: 'Lists', items: [
            { id: 'ul', label: 'Bulleted list', icon: 'ul', cmd: 'insertUnorderedList', toggle: true },
            { id: 'ol', label: 'Numbered list', icon: 'ol', cmd: 'insertOrderedList', toggle: true },
        ] },
        { group: 'Insert', items: [
            { id: 'link', label: 'Link', icon: 'link', toggle: true, key: `${MOD}+K` },
            { id: 'unlink', label: 'Remove link', icon: 'unlink', cmd: 'unlink' },
            { id: 'image', label: 'Picture', icon: 'image' },
        ] },
        { group: 'Clean up', items: [
            { id: 'clear', label: 'Clear formatting', icon: 'clear' },
            { id: 'source', label: 'HTML source', text: 'HTML', cls: 'editor__btn--wide', toggle: true },
        ] },
    ];

    const registry = new WeakMap();      // textarea -> Editor
    const byArea = new WeakMap();        // area -> Editor
    const all = new Set();

    class Editor {
        constructor(ta) {
            this.ta = ta;
            this.mode = 'visual';
            this.dirty = false;
            this.range = null;
            this.lastValue = ta.value;
            this.required = ta.required;
            this.disabled = ta.disabled;
            this.selImg = null;
            this.pastePlain = false;
            this.dragInside = false;
            this.timer = 0;
            this.build();
            this.load();
            this.bind();
            registry.set(ta, this);
            all.add(this);
            ta.dispatchEvent(new CustomEvent('lumina:editor-ready', { bubbles: true, detail: { editor: this } }));
        }

        // ---- DOM
        build() {
            const ta = this.ta;
            const id = ta.id || uid('rt');
            if (!ta.id) ta.id = id;
            this.areaId = `${id}-editor`;
            this.root = h('div', { class: 'editor', 'data-editor': '' });
            this.toolbar = h('div', { class: 'editor__toolbar', role: 'toolbar', 'aria-label': 'Text formatting', 'aria-controls': this.areaId });
            this.buttons = {};
            const flat = [];
            TOOLS.forEach((g, gi) => {
                const wrap = h('div', { class: 'editor__group', role: 'group', 'aria-label': g.group });
                g.items.forEach((t) => {
                    const title = t.key ? `${t.label} (${t.key})` : t.label;
                    const b = h('button', { type: 'button', class: 'editor__btn' + (t.cls ? ' ' + t.cls : ''), 'aria-label': t.label, title, 'data-tool': t.id, tabindex: '-1' });
                    if (t.icon) b.innerHTML = ICONS[t.icon]; else b.textContent = t.text;
                    if (t.toggle) b.setAttribute('aria-pressed', 'false');
                    wrap.appendChild(b);
                    this.buttons[t.id] = b;
                    flat.push(b);
                });
                this.toolbar.appendChild(wrap);
                if (gi < TOOLS.length - 1) this.toolbar.appendChild(h('span', { class: 'editor__sep', 'aria-hidden': 'true' }));
            });
            this.flat = flat;
            if (flat[0]) flat[0].setAttribute('tabindex', '0');

            this.area = h('div', { class: 'editor__area', id: this.areaId, contenteditable: 'true', role: 'textbox', 'aria-multiline': 'true', spellcheck: 'true' });
            const ph = ta.getAttribute('data-placeholder') || ta.getAttribute('placeholder');
            if (ph) this.area.setAttribute('data-placeholder', ph);
            const label = ta.id ? $(`label[for="${CSS.escape(ta.id)}"]`) : null;
            if (label) { if (!label.id) label.id = uid('lbl'); this.label = label; this.area.setAttribute('aria-labelledby', label.id); }
            else this.area.setAttribute('aria-label', ta.getAttribute('aria-label') || 'Rich text editor');
            const help = ta.closest('.field') ? $('.field__help', ta.closest('.field')) : null;
            if (help) { if (!help.id) help.id = uid('help'); this.area.setAttribute('aria-describedby', help.id); }

            this.live = h('span', { class: 'editor__live', role: 'status', 'aria-live': 'polite' });
            this.counter = h('span', { class: 'editor__count' });
            this.errorBox = h('span', { class: 'editor__error', role: 'alert' });
            this.status = h('div', { class: 'editor__status' }, h('span', {}, this.counter, ' ', this.errorBox), h('span', { class: 'editor__hint' }, `${MOD}+K adds a link. ${MOD}+Shift+V pastes without formatting.`), this.live);

            ta.parentNode.insertBefore(this.root, ta);
            this.root.append(this.toolbar, this.area, ta, this.status);
            ta.classList.add('editor__source');
            ta.hidden = true;
            ta.setAttribute('spellcheck', 'false');
            ta.setAttribute('aria-label', 'HTML source');
            if (this.required) ta.required = false;                      // a hidden control cannot be focused by the browser: the editor validates itself
            byArea.set(this.area, this);
            if (this.disabled) this.setDisabled(true);
        }

        load() {
            const html = cleanHtml(this.ta.value, 'edit');
            this.area.innerHTML = html || '<p><br></p>';
            this.lastValue = this.ta.value;
            this.dirty = false;
            this.refreshState();
        }

        bind() {
            const area = this.area;
            // toolbar: keep focus/selection in the editor on mouse use, roving tab stop for keyboard use
            this.toolbar.addEventListener('mousedown', (e) => { if (e.target.closest('.editor__btn')) e.preventDefault(); });
            this.toolbar.addEventListener('click', (e) => {
                const b = e.target.closest('.editor__btn');
                if (b && !b.disabled) this.tool(b.getAttribute('data-tool'));
            });
            this.toolbar.addEventListener('focusin', (e) => {
                const b = e.target.closest('.editor__btn');
                if (b) { this.flat.forEach((x) => x.setAttribute('tabindex', x === b ? '0' : '-1')); }
            });
            this.toolbar.addEventListener('keydown', (e) => {
                const keys = ['ArrowRight', 'ArrowLeft', 'Home', 'End'];
                if (!keys.includes(e.key)) return;
                const list = this.flat.filter((b) => !b.disabled);
                const i = list.indexOf(doc.activeElement);
                if (i < 0) return;
                e.preventDefault();
                let n = i;
                if (e.key === 'ArrowRight') n = (i + 1) % list.length;
                if (e.key === 'ArrowLeft') n = (i - 1 + list.length) % list.length;
                if (e.key === 'Home') n = 0;
                if (e.key === 'End') n = list.length - 1;
                list[n].focus();
            });

            area.addEventListener('input', () => { this.dirty = true; this.keepOneBlock(); this.queueSync(); this.ta.dispatchEvent(new CustomEvent('lumina:editor-change', { bubbles: true })); });
            area.addEventListener('blur', () => { this.saveRange(); if (this.dirty) this.sync(); });
            area.addEventListener('keyup', () => { this.saveRange(); this.refreshState(); });
            area.addEventListener('mouseup', () => { this.saveRange(); this.refreshState(); });
            area.addEventListener('keydown', (e) => {
                const mod = e.ctrlKey || e.metaKey;
                if (mod && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 'k') { e.preventDefault(); this.tool('link'); return; }
                if (mod && e.shiftKey && e.key.toLowerCase() === 'v') this.pastePlain = true;
                if (e.key === 'Enter' && this.selImg && !mod) { e.preventDefault(); this.openPanel('image'); return; }
                if (e.key === 'Escape') this.clearImageSelection();
            });
            area.addEventListener('click', (e) => {
                if (e.target.tagName === 'IMG') { this.selectImage(e.target); } else { this.clearImageSelection(); }
            });
            area.addEventListener('dblclick', (e) => { if (e.target.tagName === 'IMG') { this.selectImage(e.target); this.openPanel('image'); } });
            area.addEventListener('paste', (e) => this.onPaste(e));
            area.addEventListener('dragstart', () => { this.dragInside = true; });
            area.addEventListener('dragend', () => { this.dragInside = false; });
            area.addEventListener('dragover', (e) => { if (!this.dragInside) e.preventDefault(); });
            area.addEventListener('drop', (e) => this.onDrop(e));

            // label click focuses the editor (the textarea is hidden)
            if (this.label) this.label.addEventListener('click', (e) => { if (this.mode === 'visual') { e.preventDefault(); this.focus(); } });
            // form integration
            const form = this.ta.form;
            if (form) {
                this.onSubmit = (e) => {
                    if (this.dirty) this.sync();
                    if (this.required && !this.disabled && this.isEmpty()) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        this.showError('This field is required.');
                        if (this.mode === 'visual') this.focus(); else this.ta.focus();
                    }
                };
                form.addEventListener('submit', this.onSubmit, true);
                this.onReset = () => requestAnimationFrame(() => this.refresh());
                form.addEventListener('reset', this.onReset);
            }
            // reload when something else changed the textarea (e.g. "reset to default"), and follow its disabled state
            this.onExternal = () => { if (this.ta.value !== this.lastValue) this.refresh(); };
            doc.addEventListener('change', this.onExternal, true);
            this.mo = new MutationObserver(() => { if (this.ta.disabled !== this.disabled) this.setDisabled(this.ta.disabled); });
            this.mo.observe(this.ta, { attributes: true, attributeFilter: ['disabled'] });
        }

        destroy() {
            if (this.ta.form) { this.ta.form.removeEventListener('submit', this.onSubmit, true); this.ta.form.removeEventListener('reset', this.onReset); }
            doc.removeEventListener('change', this.onExternal, true);
            if (this.mo) this.mo.disconnect();
            if (this.dirty) this.sync();
            this.ta.classList.remove('editor__source');
            this.ta.hidden = false;
            this.ta.required = this.required;
            this.ta.removeAttribute('aria-label');
            this.root.parentNode.insertBefore(this.ta, this.root);
            this.root.remove();
            registry.delete(this.ta);
            byArea.delete(this.area);
            all.delete(this);
        }

        // ---- public-ish API
        focus() { this.mode === 'visual' ? this.focusArea() : this.ta.focus(); }
        isEmpty() {
            if (this.mode === 'source') return cleanHtml(this.ta.value, 'store').replace(/<[^>]*>/g, '').trim() === '' && !/<(img|hr|table)\b/i.test(this.ta.value);
            return !this.area.textContent.replace(/[\s ]+/g, '') && !this.area.querySelector('img,hr,table');
        }
        getHTML() { return this.mode === 'source' ? this.ta.value : this.serialise(); }
        setHTML(html) { this.ta.value = html || ''; this.refresh(); }
        refresh() {
            if (this.mode === 'source') { this.lastValue = this.ta.value; return; }
            this.load();
            this.updateCount();
        }
        setDisabled(on) {
            this.disabled = on;
            this.root.classList.toggle('is-disabled', on);
            this.area.setAttribute('contenteditable', on ? 'false' : 'true');
            this.area.setAttribute('aria-disabled', on ? 'true' : 'false');
            this.flat.forEach((b) => { b.disabled = on; });
        }

        // ---- selection helpers
        selection() { return doc.getSelection(); }
        inside(node) { return !!node && this.area.contains(node); }
        selectionInside() { const s = this.selection(); return !!(s && s.rangeCount && this.inside(s.anchorNode)); }
        saveRange() {
            const s = this.selection();
            if (s && s.rangeCount && this.inside(s.anchorNode) && this.inside(s.focusNode)) this.range = s.getRangeAt(0).cloneRange();
        }
        placeCaretEnd() {
            const r = doc.createRange();
            r.selectNodeContents(this.area);
            r.collapse(false);
            const s = this.selection(); s.removeAllRanges(); s.addRange(r);
            this.range = r.cloneRange();
        }
        focusArea() {
            this.area.focus({ preventScroll: true });
            const s = this.selection();
            if (this.range && !this.selectionInside()) { s.removeAllRanges(); s.addRange(this.range); }
            else if (!this.selectionInside()) this.placeCaretEnd();
        }
        closestInArea(tag, from) {
            let n = from || (this.selection().rangeCount ? this.selection().anchorNode : null);
            while (n && n !== this.area) {
                if (n.nodeType === 1 && n.nodeName.toLowerCase() === tag) return n;
                n = n.parentNode;
            }
            return null;
        }
        topBlock() {
            const s = this.selection();
            let n = s.rangeCount ? s.anchorNode : null;
            while (n && n.parentNode !== this.area) n = n.parentNode;
            return n && n.nodeType === 1 ? n : null;
        }
        keepOneBlock() {
            if (!this.area.firstChild || (this.area.childNodes.length === 1 && this.area.firstChild.nodeName === 'BR')) {
                this.area.innerHTML = '<p><br></p>';
                const r = doc.createRange(); r.setStart(this.area.firstChild, 0); r.collapse(true);
                const s = this.selection(); s.removeAllRanges(); s.addRange(r);
            }
        }

        // ---- tools
        tool(id) {
            if (this.disabled) return;
            if (id === 'source') return this.toggleSource();
            if (this.mode !== 'visual') return;
            const def = TOOLS.flatMap((g) => g.items).find((t) => t.id === id);
            if (!def) return;
            if (id === 'link') return this.togglePanel('link');
            if (id === 'image') return this.togglePanel('image');
            this.closePanels(false);
            this.run(() => {
                if (def.cmd) doc.execCommand(def.cmd, false, null);
                else if (def.block) this.toggleBlock(def.block);
                else if (id === 'quote') this.toggleQuote();
                else if (id === 'clear') this.clearFormatting();
            });
            if (id === 'clear') this.say('Formatting cleared');
        }
        run(fn) {
            if (this.mode !== 'visual' || this.disabled) return;
            this.focusArea();
            try { doc.execCommand('styleWithCSS', false, false); doc.execCommand('defaultParagraphSeparator', false, 'p'); fn(); } catch (_) { /* ignore */ }
            this.dirty = true;
            this.keepOneBlock();
            this.wrapStrayPictures();
            this.saveRange();
            this.sync();
            this.refreshState();
            this.ta.dispatchEvent(new CustomEvent('lumina:editor-change', { bubbles: true }));
        }
        /** A picture inserted next to a paragraph can land at the top level; give it its own paragraph (selection offsets stay valid: same child count). */
        wrapStrayPictures() {
            Array.from(this.area.childNodes).forEach((n) => {
                if (n.nodeType === 1 && n.nodeName === 'IMG') {
                    const p = doc.createElement('p');
                    this.area.insertBefore(p, n);
                    p.appendChild(n);
                }
            });
        }
        toggleBlock(tag) {
            const cur = this.topBlock();
            const same = cur && cur.nodeName.toLowerCase() === tag;
            doc.execCommand('formatBlock', false, `<${same ? 'p' : tag}>`);
        }
        toggleQuote() {
            const q = this.closestInArea('blockquote');
            if (!q) { doc.execCommand('formatBlock', false, '<blockquote>'); return; }
            doc.execCommand('outdent', false, null);
            if (q.isConnected) {                                         // browser did not unwrap it: do it by hand
                const parent = q.parentNode;
                while (q.firstChild) parent.insertBefore(q.firstChild, q);
                parent.removeChild(q);
                normaliseBlocks(this.area);
            } else if (!this.topBlock() || !BLOCKS.has(this.topBlock().nodeName.toLowerCase())) {
                doc.execCommand('formatBlock', false, '<p>');            // outdent leaves bare text at the top level: give it a paragraph again
            }
        }
        clearFormatting() {
            doc.execCommand('removeFormat', false, null);
            doc.execCommand('unlink', false, null);
            const b = this.topBlock();
            if (b && /^(h2|h3|h4|blockquote)$/i.test(b.nodeName)) doc.execCommand('formatBlock', false, '<p>');
            if (this.closestInArea('blockquote')) this.toggleQuote();
        }

        refreshState() {
            if (this.mode !== 'visual' || !this.selectionInside()) { this.updateCount(); this.refreshEmpty(); return; }
            const q = (c) => { try { return doc.queryCommandState(c); } catch (_) { return false; } };
            const block = this.topBlock();
            const bt = block ? block.nodeName.toLowerCase() : '';
            const state = {
                bold: q('bold'), italic: q('italic'), underline: q('underline'), ul: q('insertUnorderedList'), ol: q('insertOrderedList'),
                h2: bt === 'h2', h3: bt === 'h3', quote: !!this.closestInArea('blockquote'), link: !!this.closestInArea('a'),
            };
            for (const [id, on] of Object.entries(state)) {
                const b = this.buttons[id];
                if (!b) continue;
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
                b.classList.toggle('is-active', on);
            }
            this.updateCount();
            this.refreshEmpty();
        }
        refreshEmpty() { this.area.classList.toggle('is-empty', this.isEmpty() && this.mode === 'visual'); }
        updateCount() {
            const text = this.mode === 'source' ? this.ta.value.replace(/<[^>]*>/g, ' ') : this.area.textContent;
            const words = (text.trim().match(/\S+/g) || []).length;
            this.counter.textContent = `${words} word${words === 1 ? '' : 's'}`;
        }
        say(msg) { this.live.textContent = ''; setTimeout(() => { this.live.textContent = msg; }, 40); }
        showError(msg) { this.errorBox.textContent = msg; this.area.setAttribute('aria-invalid', 'true'); setTimeout(() => { this.errorBox.textContent = ''; this.area.removeAttribute('aria-invalid'); }, 6000); }

        // ---- value sync
        serialise() {
            const clone = this.area.cloneNode(true);
            $$('img.is-selected', clone).forEach((i) => { i.classList.remove('is-selected'); if (!i.getAttribute('class')) i.removeAttribute('class'); });
            return cleanHtml(clone.innerHTML, 'store');
        }
        queueSync() { clearTimeout(this.timer); this.timer = setTimeout(() => this.sync(), 200); this.refreshEmpty(); this.updateCount(); }
        sync() {
            clearTimeout(this.timer);
            if (this.mode !== 'visual') { this.lastValue = this.ta.value; return; }
            const html = this.serialise();
            if (html !== this.ta.value) this.ta.value = html;
            this.lastValue = html;
            this.updateCount();
            this.refreshEmpty();
        }

        // ---- source mode
        toggleSource() {
            this.closePanels(false);
            if (this.mode === 'visual') {
                if (this.dirty) this.sync();
                this.mode = 'source';
                this.area.hidden = true;
                this.ta.hidden = false;
                this.root.classList.add('is-source');
                this.flat.forEach((b) => { if (b.getAttribute('data-tool') !== 'source') b.disabled = true; });
                this.buttons.source.setAttribute('aria-pressed', 'true');
                this.buttons.source.classList.add('is-active');
                this.ta.focus();
                this.say('HTML source view');
            } else {
                const edited = this.ta.value !== this.lastValue;
                this.mode = 'visual';
                this.ta.hidden = true;
                this.area.hidden = false;
                this.root.classList.remove('is-source');
                this.flat.forEach((b) => { b.disabled = this.disabled; });
                this.buttons.source.setAttribute('aria-pressed', 'false');
                this.buttons.source.classList.remove('is-active');
                this.load();
                if (edited) { this.dirty = true; this.sync(); this.ta.dispatchEvent(new CustomEvent('lumina:editor-change', { bubbles: true })); }
                this.focusArea();
                this.say('Visual editor');
            }
            this.ta.dispatchEvent(new CustomEvent('lumina:editor-mode', { bubbles: true, detail: { mode: this.mode } }));
            this.updateCount();
        }

        // ---- paste / drop
        insertHtml(html) {
            this.focusArea();
            if (!doc.execCommand('insertHTML', false, html)) {
                const s = this.selection();
                if (s.rangeCount) {
                    const r = s.getRangeAt(0); r.deleteContents();
                    const t = doc.createElement('template'); t.innerHTML = html;
                    r.insertNode(t.content);
                }
            }
            this.afterEdit();
        }
        afterEdit() { this.dirty = true; this.keepOneBlock(); this.saveRange(); this.queueSync(); this.refreshState(); this.ta.dispatchEvent(new CustomEvent('lumina:editor-change', { bubbles: true })); }
        onPaste(e) {
            const cd = e.clipboardData;
            if (!cd) return;
            const plainOnly = this.pastePlain; this.pastePlain = false;
            const html = cd.getData('text/html');
            const text = cd.getData('text/plain');
            const files = Array.from(cd.files || []).filter((f) => f.type.startsWith('image/'));
            if (!html && !text && files.length) { e.preventDefault(); files.forEach((f) => this.uploadAndInsert(f)); return; }
            e.preventDefault();
            this.focusArea();
            if (html && !plainOnly) {
                let clean = cleanHtml(html, 'edit', { paste: true, inline: true });
                if (/<(p|h2|h3|h4|ul|ol|blockquote|figure|hr|pre|table)\b/i.test(clean)) clean = cleanHtml(html, 'edit', { paste: true });   // block content: normalised
                if (clean.replace(/<[^>]*>/g, '').trim() !== '' || /<img\b/i.test(clean)) { this.insertHtml(clean); return; }
            }
            if (!text) return;
            if (!/\n/.test(text.trim())) { doc.execCommand('insertText', false, text); this.afterEdit(); }
            else this.insertHtml(plainToHtml(text));
        }
        onDrop(e) {
            if (this.dragInside) return;                                                   // moving content inside the editor: browser default
            const dt = e.dataTransfer;
            if (!dt) return;
            e.preventDefault();
            this.moveCaretToPoint(e.clientX, e.clientY);
            const files = Array.from(dt.files || []).filter((f) => f.type.startsWith('image/'));
            if (files.length) { files.forEach((f) => this.uploadAndInsert(f)); return; }
            const html = dt.getData('text/html');
            const text = dt.getData('text/plain');
            if (html) { const clean = cleanHtml(html, 'edit', { paste: true }); if (clean) { this.insertHtml(clean); return; } }
            if (text) this.insertHtml(plainToHtml(text));
        }
        moveCaretToPoint(x, y) {
            let r = null;
            if (doc.caretRangeFromPoint) r = doc.caretRangeFromPoint(x, y);
            else if (doc.caretPositionFromPoint) { const p = doc.caretPositionFromPoint(x, y); if (p) { r = doc.createRange(); r.setStart(p.offsetNode, p.offset); r.collapse(true); } }
            if (r && this.inside(r.startContainer)) { const s = this.selection(); s.removeAllRanges(); s.addRange(r); this.range = r.cloneRange(); }
        }
        async uploadAndInsert(file) {
            const bad = checkImageFile(file);
            if (bad) { this.showError(bad); return; }
            this.say('Uploading picture');
            try {
                const out = await uploadImage(file);
                this.insertHtml(`<img src="${cleanImgSrc(out.url, 'edit')}" alt="">`);
                this.say('Picture added. Double-click it to add a description.');
            } catch (err) { this.showError(err.message || 'Upload failed.'); }
        }

        // ---- pictures inside the content
        selectImage(img) {
            this.clearImageSelection();
            this.selImg = img;
            img.classList.add('is-selected');
            const r = doc.createRange(); r.selectNode(img);
            const s = this.selection(); s.removeAllRanges(); s.addRange(r);
            this.range = r.cloneRange();
            this.say('Picture selected. Press Enter to edit its description and placement, or Delete to remove it.');
        }
        clearImageSelection() {
            if (this.selImg) { this.selImg.classList.remove('is-selected'); if (!this.selImg.getAttribute('class')) this.selImg.removeAttribute('class'); }
            this.selImg = null;
        }
        pictureInSelection() {
            if (this.selImg && this.selImg.isConnected) return this.selImg;
            const s = this.selection();
            if (s.rangeCount) {
                const r = s.getRangeAt(0);
                if (r.startContainer === r.endContainer && r.endOffset - r.startOffset === 1) {
                    const n = r.startContainer.childNodes[r.startOffset];
                    if (n && n.nodeName === 'IMG') return n;
                }
            }
            return null;
        }

        // ---- panels (link / picture) - inline, labelled, keyboard friendly
        togglePanel(type) {
            if (this.panel && this.panel.type === type && !this.panel.el.hidden) { this.closePanels(true); return; }
            this.openPanel(type);
        }
        closePanels(returnFocus) {
            if (!this.panel) return;
            this.panel.el.remove();
            const t = this.panel.type;
            this.panel = null;
            ['link', 'image'].forEach((id) => { const b = this.buttons[id]; if (b && id === t) b.setAttribute('aria-expanded', 'false'); });
            if (returnFocus) { const b = this.buttons[t]; if (b) b.focus(); }
        }
        openPanel(type) {
            this.saveRange();
            this.closePanels(false);
            const panel = type === 'link' ? this.buildLinkPanel() : this.buildImagePanel();
            this.toolbar.after(panel.el);
            this.panel = { type, el: panel.el };
            this.buttons[type].setAttribute('aria-expanded', 'true');
            panel.el.addEventListener('input', (e) => e.stopPropagation());
            panel.el.addEventListener('change', (e) => e.stopPropagation());
            panel.el.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); this.closePanels(true); }
                else if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'file' && e.target.type !== 'checkbox') { e.preventDefault(); panel.apply(); }
            });
            panel.focus.focus();
        }
        panelStatus(el, msg, bad) { el.textContent = msg; el.classList.toggle('is-error', !!bad); }

        buildLinkPanel() {
            const id = uid('lk');
            const existing = this.closestInArea('a', this.range ? this.range.startContainer : null);
            const collapsed = !this.range || this.range.collapsed;
            const url = h('input', { class: 'input', id: `${id}-url`, type: 'text', inputmode: 'url', autocomplete: 'off', placeholder: 'https://..., /about, mailto:..., tel:...' });
            const text = h('input', { class: 'input', id: `${id}-text`, type: 'text', autocomplete: 'off' });
            const blank = h('input', { type: 'checkbox', id: `${id}-blank` });
            const status = h('span', { class: 'editor__panel__status', role: 'status', 'aria-live': 'polite' });
            const apply = h('button', { type: 'button', class: 'btn btn--primary btn--sm' }, existing ? 'Update link' : 'Add link');
            const remove = h('button', { type: 'button', class: 'btn btn--outline btn--sm' }, 'Remove link');
            const cancel = h('button', { type: 'button', class: 'btn btn--ghost btn--sm' }, 'Cancel');
            if (existing) {
                url.value = cleanHref(existing.getAttribute('href'), 'store');
                blank.checked = existing.getAttribute('target') === '_blank';
            }
            const showText = !existing && collapsed;
            const el = h('div', { class: 'editor__panel', role: 'group', 'aria-label': existing ? 'Edit link' : 'Add link' },
                h('p', { class: 'editor__panel__title' }, existing ? 'Edit link' : 'Add link'),
                h('div', { class: 'field field--full' }, h('label', { class: 'field__label', for: `${id}-url` }, 'Web address'), url,
                    h('div', { class: 'field__help' }, 'Use /page for a page on this website, or a full address such as https://example.org.')),
                showText ? h('div', { class: 'field field--full' }, h('label', { class: 'field__label', for: `${id}-text` }, 'Link text'), text) : null,
                h('label', { class: 'check field--full', for: `${id}-blank` }, blank, h('span', {}, 'Open in a new tab')),
                h('div', { class: 'editor__panel__actions' }, apply, existing ? remove : null, cancel, status));
            const doApply = () => {
                let v = String(url.value || '').trim();
                if (/^[^\s@/]+@[^\s@/]+\.[^\s@/]+$/.test(v)) v = 'mailto:' + v;
                else if (/^\+?[0-9][0-9 ()\-]{5,}$/.test(v)) v = 'tel:' + v.replace(/[^\d+]/g, '');
                else if (/^(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)+([/?#].*)?$/i.test(v) && !/^[a-z][a-z0-9+.\-]*:/i.test(v)) v = 'https://' + v;
                const stored = cleanHref(v, 'store');
                if (!v || !stored) { this.panelStatus(status, 'That address is not valid. Use https://..., /page, mailto: or tel:.', true); url.focus(); return; }
                const href = cleanHref(v, 'edit');
                const newTab = blank.checked;
                this.closePanels(false);
                this.run(() => {
                    if (this.range) { const s = this.selection(); s.removeAllRanges(); s.addRange(this.range); }
                    if (existing && existing.isConnected) {
                        existing.setAttribute('href', href);
                        if (newTab) existing.setAttribute('target', '_blank'); else existing.removeAttribute('target');
                        return;
                    }
                    const label = (text.value || '').trim() || v;
                    const mk = (a) => { a.setAttribute('href', href); if (newTab) a.setAttribute('target', '_blank'); };
                    if (collapsed) {
                        const a = doc.createElement('a'); mk(a); a.textContent = label;
                        const tmp = doc.createElement('div'); tmp.appendChild(a);
                        doc.execCommand('insertHTML', false, tmp.innerHTML);
                    } else {
                        doc.execCommand('createLink', false, href);
                        const s = this.selection();
                        $$('a[href]', this.area).forEach((a) => { if (s.rangeCount && s.containsNode(a, true) && a.getAttribute('href') === href) mk(a); });
                    }
                });
                this.say('Link added');
                const b = this.buttons.link; if (b) b.focus();
            };
            apply.addEventListener('click', doApply);
            cancel.addEventListener('click', () => this.closePanels(true));
            remove.addEventListener('click', () => {
                this.closePanels(false);
                this.run(() => {
                    if (this.range) { const s = this.selection(); s.removeAllRanges(); s.addRange(this.range); }
                    if (existing && existing.isConnected) {
                        const r = doc.createRange(); r.selectNodeContents(existing);
                        const s = this.selection(); s.removeAllRanges(); s.addRange(r);
                    }
                    doc.execCommand('unlink', false, null);
                });
                this.say('Link removed');
                const b = this.buttons.link; if (b) b.focus();
            });
            return { el, apply: doApply, focus: url };
        }

        buildImagePanel() {
            const id = uid('im');
            const img = this.pictureInSelection();
            const editing = !!img;
            const file = h('input', { class: 'editor__file', id: `${id}-file`, type: 'file', accept: IMAGE_TYPES.join(',') });
            const alt = h('input', { class: 'input', id: `${id}-alt`, type: 'text', maxlength: '250', autocomplete: 'off', placeholder: 'Describe the picture for people who cannot see it' });
            const deco = h('input', { type: 'checkbox', id: `${id}-deco` });
            const place = h('select', { class: 'select', id: `${id}-place` },
                h('option', { value: '' }, 'In the text'), h('option', { value: 'img-left' }, 'Left, text wraps around'),
                h('option', { value: 'img-right' }, 'Right, text wraps around'), h('option', { value: 'img-full' }, 'Full width'));
            const status = h('span', { class: 'editor__panel__status', role: 'status', 'aria-live': 'polite' });
            const apply = h('button', { type: 'button', class: 'btn btn--primary btn--sm' }, editing ? 'Update picture' : 'Add picture');
            const remove = h('button', { type: 'button', class: 'btn btn--outline btn--sm' }, 'Remove picture');
            const cancel = h('button', { type: 'button', class: 'btn btn--ghost btn--sm' }, 'Cancel');
            let uploaded = null;
            let pending = null;
            if (editing) {
                alt.value = img.getAttribute('alt') || '';
                deco.checked = (img.getAttribute('alt') || '') === '';
                alt.disabled = deco.checked;
                const cls = (img.getAttribute('class') || '').split(/\s+/).find((c) => ['img-left', 'img-right', 'img-full'].includes(c));
                place.value = cls || '';
            }
            deco.addEventListener('change', () => { alt.disabled = deco.checked; if (deco.checked) alt.value = ''; });
            file.addEventListener('change', () => {
                const f = file.files && file.files[0];
                const bad = checkImageFile(f);
                if (bad) { this.panelStatus(status, bad, true); file.value = ''; return; }
                this.panelStatus(status, 'Uploading...', false);
                pending = uploadImage(f).then((out) => { uploaded = out; this.panelStatus(status, 'Picture uploaded.', false); }).catch((err) => { uploaded = null; this.panelStatus(status, err.message || 'Upload failed.', true); });
            });
            const el = h('div', { class: 'editor__panel', role: 'group', 'aria-label': editing ? 'Edit picture' : 'Add picture' },
                h('p', { class: 'editor__panel__title' }, editing ? 'Edit picture' : 'Add picture'),
                h('div', { class: 'field field--full' }, h('label', { class: 'field__label', for: `${id}-file` }, editing ? 'Replace the picture (optional)' : 'Picture'), file,
                    h('div', { class: 'field__help' }, 'JPG, PNG, GIF or WebP, up to 5 MB.')),
                h('div', { class: 'field field--full' }, h('label', { class: 'field__label', for: `${id}-alt` }, 'Description (alt text)'), alt,
                    h('div', { class: 'field__help' }, 'Say what the picture shows. Screen readers read this aloud.')),
                h('label', { class: 'check field--full', for: `${id}-deco` }, deco, h('span', {}, 'This picture is only decorative (no description needed)')),
                h('div', { class: 'field' }, h('label', { class: 'field__label', for: `${id}-place` }, 'Placement'), place),
                h('div', { class: 'editor__panel__actions' }, apply, editing ? remove : null, cancel, status));
            const doApply = async () => {
                if (!deco.checked && !alt.value.trim()) { this.panelStatus(status, 'Add a short description, or tick "only decorative".', true); alt.focus(); return; }
                if (pending) { await pending; pending = null; }
                if (!editing && !uploaded) { this.panelStatus(status, 'Choose a picture first.', true); file.focus(); return; }
                const altText = deco.checked ? '' : alt.value.trim();
                const cls = place.value;
                this.closePanels(false);
                this.run(() => {
                    if (editing && img.isConnected) {
                        img.setAttribute('alt', altText);
                        if (uploaded) img.setAttribute('src', cleanImgSrc(uploaded.url, 'edit'));
                        img.classList.remove('img-left', 'img-right', 'img-full');
                        if (cls) img.classList.add(cls);
                        if (!img.getAttribute('class')) img.removeAttribute('class');
                        return;
                    }
                    if (this.range) { const s = this.selection(); s.removeAllRanges(); s.addRange(this.range); }
                    const node = doc.createElement('img');
                    node.setAttribute('src', cleanImgSrc(uploaded.url, 'edit'));
                    node.setAttribute('alt', altText);
                    if (cls) node.setAttribute('class', cls);
                    const tmp = doc.createElement('div'); tmp.appendChild(node);
                    doc.execCommand('insertHTML', false, tmp.innerHTML);
                });
                this.say(editing ? 'Picture updated' : 'Picture added');
                const b = this.buttons.image; if (b) b.focus();
            };
            apply.addEventListener('click', doApply);
            cancel.addEventListener('click', () => this.closePanels(true));
            remove.addEventListener('click', () => {
                this.closePanels(false);
                this.run(() => { if (img && img.isConnected) { img.remove(); this.clearImageSelection(); } });
                this.say('Picture removed');
                this.focus();
            });
            return { el, apply: doApply, focus: editing ? alt : file };
        }
    }

    // ------------------------------------------------------------------------------------------------ one selectionchange listener for all editors
    let sc = 0;
    doc.addEventListener('selectionchange', () => {
        if (sc) return;
        sc = requestAnimationFrame(() => {
            sc = 0;
            const s = doc.getSelection();
            if (!s || !s.rangeCount) return;
            const node = s.anchorNode;
            const el = node ? (node.nodeType === 1 ? node : node.parentElement) : null;
            const area = el ? el.closest('.editor__area') : null;
            const ed = area ? byArea.get(area) : null;
            if (ed) { ed.saveRange(); ed.refreshState(); }
        });
    });

    // ------------------------------------------------------------------------------------------------ public API
    function resolve(target) {
        if (!target) return [];
        if (typeof target === 'string') return $$(target).flatMap((n) => resolve(n));
        if (target instanceof HTMLTextAreaElement) return [target];
        if (target.querySelectorAll) return $$('textarea[data-richtext]', target);
        return [];
    }
    function init(target) {
        const made = [];
        resolve(target).forEach((ta) => {
            if (ta.closest('template')) return;
            if (!registry.has(ta) && ta.isConnected) made.push(new Editor(ta));
            else if (registry.has(ta)) made.push(registry.get(ta));
        });
        return typeof target === 'string' || !(target instanceof HTMLTextAreaElement) ? made : made[0];
    }
    const api = {
        version: VERSION,
        init,
        initAll: (root) => init(root || doc),
        get: (ta) => registry.get(typeof ta === 'string' ? $(ta) : ta) || null,
        sync: (target) => { (target ? resolve(target) : Array.from(all).map((e) => e.ta)).forEach((ta) => { const e = registry.get(ta); if (e) e.sync(); }); },
        refresh: (target) => { (target ? resolve(target) : Array.from(all).map((e) => e.ta)).forEach((ta) => { const e = registry.get(ta); if (e) e.refresh(); }); },
        setValue: (ta, html) => { const e = registry.get(typeof ta === 'string' ? $(ta) : ta); if (e) e.setHTML(html); else if (ta) ta.value = html; },
        destroy: (ta) => { const e = registry.get(typeof ta === 'string' ? $(ta) : ta); if (e) e.destroy(); },
        clean: (html, mode) => cleanHtml(html, mode || 'store'),
    };
    window.LuminaEditor = api;

    const start = () => {
        api.initAll(doc);
        // new editors appearing later (repeater rows cloned from a <template>, AJAX-inserted forms)
        new MutationObserver((records) => {
            for (const r of records) {
                r.addedNodes.forEach((n) => {
                    if (n.nodeType !== 1) return;
                    if (n.matches && n.matches('textarea[data-richtext]')) api.init(n);
                    else if (n.querySelector && n.querySelector('textarea[data-richtext]')) api.initAll(n);
                });
            }
        }).observe(doc.body, { childList: true, subtree: true });
    };
    if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', start); else start();
})();
