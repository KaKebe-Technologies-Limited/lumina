/*!
 * Admin "core" screens: pages editor, menus, settings tabs, stats, users, password forms. OWNER: B3 admin-core.
 * Vanilla ES2020, no dependencies, no inline handlers (CSP). Everything is enabled only when its data-attributes are on the page,
 * so the same file can be included by every screen. Depends on assets/js/admin.js (repeaters, reorder lists, confirm dialogs,
 * unsaved-changes guard and LuminaAdmin.uploadImage), which the admin layout loads first.
 *
 * Hooks (all optional):
 *   form[data-pe-form]        pages editor form (sections, resets, images, SEO preview, state line, Ctrl+S, scroll restore, large-form packing)
 *     [data-pe-section] [data-pe-toggle] [data-pe-jump] [data-pe-expand-all] [data-pe-collapse-all] [data-pe-visible]
 *     [data-pe-field] [data-pe-reset] [data-pe-undo] [data-pe-reset-section] [data-pe-image] [data-pe-count] [data-pe-serp-*]
 *   form[data-menus-form]     live menu preview + indentation
 *   form[data-settings-form]  settings tabs ([data-settings-tab] / [data-settings-panel])
 *   [data-password-field] [data-password-toggle] [data-password-strength="#meter"]   password inputs
 *   [data-copy="#selector"]   copy text to the clipboard
 *   [data-stat-*]             live preview on the impact-figure form
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const store = {
        get(key) { try { return window.localStorage.getItem(key); } catch (_) { return null; } },
        set(key, value) { try { window.localStorage.setItem(key, value); } catch (_) { /* private mode */ } },
        sget(key) { try { return window.sessionStorage.getItem(key); } catch (_) { return null; } },
        sset(key, value) { try { window.sessionStorage.setItem(key, value); } catch (_) { /* ignore */ } },
        sdel(key) { try { window.sessionStorage.removeItem(key); } catch (_) { /* ignore */ } },
    };
    // programmatic edits (reset, section toggles...) must count as "changed" for the unsaved-changes guard in admin.js too
    const mark = (form) => {
        if (!form || form.dataset.peMarking) return;
        form.dataset.peMarking = '1';                      // re-entrancy guard: listeners of "change" must never call mark() again
        try {
            form.dispatchEvent(new CustomEvent('pe:dirty', { bubbles: true }));
            form.dispatchEvent(new Event('change', { bubbles: true }));
        } finally { delete form.dataset.peMarking; }
    };

    // ================================================================ password fields (login, change password)
    $$('[data-password-toggle]').forEach((btn) => {
        const input = document.getElementById(btn.getAttribute('aria-controls') || '');
        if (!input) return;
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });
    $$('[data-password-strength]').forEach((input) => {
        const meter = $(input.dataset.passwordStrength);
        if (!meter) return;
        const bar = $('.pw-meter__bar > span', meter);
        const text = $('.pw-meter__text', meter);
        const labels = ['Too short', 'Weak', 'Fair', 'Good', 'Strong'];
        input.addEventListener('input', () => {
            const v = input.value;
            meter.hidden = v === '';
            let score = 0;
            if (v.length >= 10) score++;
            if (/[A-Za-z]/.test(v) && /\d/.test(v)) score++;
            if (v.length >= 14) score++;
            if (/[^A-Za-z0-9]/.test(v) || (/[a-z]/.test(v) && /[A-Z]/.test(v) && v.length >= 12)) score++;
            if (v.length < 10) score = 0;
            meter.dataset.level = String(score);
            if (bar) bar.style.width = (score === 0 ? 8 : score * 25) + '%';
            if (text) text.textContent = labels[score];
        });
    });

    // ================================================================ copy to clipboard
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        const src = $(btn.dataset.copy);
        if (!src) return;
        const textToCopy = src.textContent.trim();
        let ok = false;
        try { await navigator.clipboard.writeText(textToCopy); ok = true; } catch (_) {
            const range = document.createRange();
            range.selectNodeContents(src);
            const sel = window.getSelection();
            sel.removeAllRanges(); sel.addRange(range);
            try { ok = document.execCommand('copy'); } catch (__) { ok = false; }
        }
        const original = btn.dataset.originalLabel || btn.textContent;
        btn.dataset.originalLabel = original;
        btn.textContent = ok ? (btn.dataset.copiedLabel || 'Copied') : 'Press Ctrl+C to copy';
        window.setTimeout(() => { btn.textContent = original; }, 2500);
    });

    // ================================================================ impact-figure preview
    const statNumber = $('[data-stat-number]');
    if (statNumber) {
        const out = $('[data-stat-preview-value]');
        const lab = $('[data-stat-preview-label]');
        const refresh = () => {
            const n = parseInt(statNumber.value || '0', 10);
            const pre = ($('[data-stat-prefix]') || {}).value || '';
            const suf = ($('[data-stat-suffix]') || {}).value || '';
            if (out) out.textContent = pre + (Number.isFinite(n) ? n.toLocaleString('en-GB') : '0') + suf;
            if (lab) lab.textContent = ($('[data-stat-label]') || {}).value || 'Label';
        };
        ['[data-stat-number]', '[data-stat-prefix]', '[data-stat-suffix]', '[data-stat-label]'].forEach((s) => { const el = $(s); if (el) el.addEventListener('input', refresh); });
        refresh();
    }

    // ================================================================ settings tabs
    const settings = $('form[data-settings-form]');
    if (settings && document.documentElement.classList.contains('js')) {
        const tabs = $$('[data-settings-tab]', settings);
        const panels = $$('[data-settings-panel]', settings);
        const tabInput = $('[data-settings-tab-input]', settings);
        const activate = (key, focus) => {
            const exists = panels.some((p) => p.dataset.settingsPanel === key);
            if (!exists) key = panels.length ? panels[0].dataset.settingsPanel : '';
            tabs.forEach((t) => {
                const on = t.dataset.settingsTab === key;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                if (on && focus) t.focus();
            });
            panels.forEach((p) => { p.hidden = p.dataset.settingsPanel !== key; });
            if (tabInput) tabInput.value = key;
        };
        const fromHash = () => (window.location.hash.match(/^#tab-([a-z_]+)$/) || [])[1];
        const errPanel = $('.field--error', settings);
        activate(errPanel ? errPanel.closest('[data-settings-panel]').dataset.settingsPanel : (fromHash() || (tabs[0] && tabs[0].dataset.settingsTab)));
        tabs.forEach((t, i) => {
            t.addEventListener('click', (e) => { e.preventDefault(); activate(t.dataset.settingsTab, false); try { history.replaceState(null, '', '#tab-' + t.dataset.settingsTab); } catch (_) { /* ignore */ } });
            t.addEventListener('keydown', (e) => {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                e.preventDefault();
                const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
                activate(next.dataset.settingsTab, true);
            });
        });
        window.addEventListener('hashchange', () => { const k = fromHash(); if (k) activate(k, false); });
        // the "use the original" checkboxes grey their field out
        $$('.pe-setting__reset input', settings).forEach((cb) => cb.addEventListener('change', () => cb.closest('.pe-setting').classList.toggle('is-reset', cb.checked)));
    }

    // ================================================================ menus: live preview
    const menus = $('form[data-menus-form]');
    if (menus) {
        const list = $('[data-menu-list]', menus);
        const outHeader = $('[data-menu-preview-header]', menus);
        const outFooter = $('[data-menu-preview-footer]', menus);
        const read = () => $$(':scope > [data-reorder-item]', list).map((it) => ({
            el: it,
            slug: it.dataset.slug,
            label: ($('[data-menu-label]', it).value || '').trim() || it.dataset.slug,
            header: $('[data-menu-header]', it).checked,
            footer: $('[data-menu-footer]', it).checked,
            parent: $('[data-menu-parent]', it).value,
        }));
        const li = (text) => { const n = document.createElement('li'); n.textContent = text; return n; };
        const render = () => {
            const items = read();
            const inHeader = items.filter((i) => i.header);
            const bySlug = Object.fromEntries(inHeader.map((i) => [i.slug, i]));
            outHeader.textContent = '';
            inHeader.forEach((i) => {
                const parent = i.parent && bySlug[i.parent] && !bySlug[i.parent].parent && i.parent !== i.slug ? i.parent : '';
                if (parent) return;
                const node = li(i.label);
                const kids = inHeader.filter((c) => c.parent === i.slug && c.slug !== i.slug);
                if (kids.length) {
                    const ul = document.createElement('ul');
                    kids.forEach((k) => ul.appendChild(li(k.label)));
                    node.appendChild(ul);
                }
                outHeader.appendChild(node);
            });
            if (!outHeader.children.length) outHeader.appendChild(li('Nothing in the header menu'));
            outFooter.textContent = '';
            items.filter((i) => i.footer).forEach((i) => outFooter.appendChild(li(i.label)));
            if (!outFooter.children.length) outFooter.appendChild(li('Nothing in the footer'));
            items.forEach((i) => { i.el.classList.toggle('is-child', !!i.parent); });
        };
        // the dirty state line and the unsaved-changes guard listen to these same events themselves
        ['input', 'change', 'reorder:change'].forEach((ev) => menus.addEventListener(ev, render));
        render();
    }

    // ================================================================ pages editor
    const form = $('form[data-pe-form]');
    const slugKey = form ? (form.dataset.peSlug || 'page') : '';

    // ---- unsaved / saved state line (also used by the menus / stats / settings forms)
    const stateForms = $$('form').filter((f) => $('[data-pe-state]', f));
    stateForms.forEach((f) => {
        const line = $('[data-pe-state]', f);
        const original = line.textContent;
        const dirty = () => { line.textContent = 'You have unsaved changes'; line.classList.add('is-dirty'); const bar = line.closest('.pe-actions'); if (bar) bar.classList.add('is-dirty'); };
        ['input', 'change', 'repeater:change', 'reorder:change', 'pe:dirty'].forEach((ev) => f.addEventListener(ev, () => { if (!f.dataset.peBusy) dirty(); }));
        f.addEventListener('submit', () => { f.dataset.peBusy = '1'; line.textContent = 'Saving...'; });
        f.addEventListener('lumina:clean', () => { line.textContent = original; line.classList.remove('is-dirty'); });
    });

    if (form) {
        // ---- sections: collapse / expand, remembered per page
        const sections = $$('[data-pe-section]', form);
        const key = 'lumina.pe.open.' + slugKey;
        let saved = null;
        try { saved = JSON.parse(store.get(key) || 'null'); } catch (_) { saved = null; }
        const persist = () => {
            const o = {};
            sections.forEach((s) => { o[s.dataset.section] = !s.classList.contains('is-collapsed'); });
            store.set(key, JSON.stringify(o));
        };
        const setOpen = (s, open, save = true) => {
            s.classList.toggle('is-collapsed', !open);
            const t = $('[data-pe-toggle]', s);
            if (t) t.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (save) persist();
        };
        const many = form.dataset.peMany === '1';
        const hasErrors = !!$('.field--error', form);
        sections.forEach((s, i) => {
            let open = true;
            if (saved && Object.prototype.hasOwnProperty.call(saved, s.dataset.section)) open = !!saved[s.dataset.section];
            else if (many && i > 0) open = false;
            setOpen(s, open, false);
        });
        const hashTarget = () => {
            const id = decodeURIComponent((window.location.hash || '').replace(/^#/, ''));
            return id ? document.getElementById(id) : null;
        };
        const revealAndScroll = (el, focus) => {
            const s = el.closest('[data-pe-section]') || (el.matches && el.matches('[data-pe-section]') ? el : null);
            if (s) setOpen(s, true, true);
            window.setTimeout(() => {
                el.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
                if (focus) { const t = $('[data-pe-toggle]', s || el); if (t) t.focus({ preventScroll: true }); }
            }, 20);
        };
        sections.forEach((s) => { if ($('.field--error', s)) setOpen(s, true, false); });
        const ht = hashTarget();
        if (ht && ht.closest('[data-pe-section]')) { setOpen(ht.closest('[data-pe-section]'), true, false); }

        form.addEventListener('click', (e) => {
            const t = e.target.closest('[data-pe-toggle]');
            if (t) { const s = t.closest('[data-pe-section]'); setOpen(s, s.classList.contains('is-collapsed')); return; }
            const j = e.target.closest('[data-pe-jump]');
            if (j) {
                const target = document.getElementById(j.getAttribute('href').slice(1));
                if (target) { e.preventDefault(); revealAndScroll(target, true); try { history.replaceState(null, '', j.getAttribute('href')); } catch (_) { /* ignore */ } }
                return;
            }
            if (e.target.closest('[data-pe-expand-all]')) { sections.forEach((s) => setOpen(s, true, false)); persist(); return; }
            if (e.target.closest('[data-pe-collapse-all]')) { sections.forEach((s) => setOpen(s, false, false)); persist(); }
        });

        // ---- section visibility switch (toggle sections)
        const syncVisible = (cb) => {
            const s = cb.closest('[data-pe-section]');
            const on = cb.checked;
            s.classList.toggle('is-section-hidden', !on);
            const badge = $('[data-pe-hidden-badge]', s); if (badge) badge.hidden = on;
            const note = $('[data-pe-off-note]', s); if (note) note.hidden = on;
        };
        $$('[data-pe-visible]', form).forEach((cb) => { cb.addEventListener('change', () => syncVisible(cb)); });

        // ---- published switch
        $$('[data-pe-published]', form).forEach((cb) => cb.addEventListener('change', () => {
            const t = $('[data-pe-published-text]', form);
            const h = $('[data-pe-published-help]', form);
            const custom = (form.dataset.peSlug || '') !== '' ;
            if (t) t.textContent = cb.checked ? 'Published' : (custom && $('[name="slug"]', form) ? 'Draft' : 'Not published');
            if (h) h.textContent = cb.checked ? 'Visible to everyone and listed in the menus you chose.' : 'Visitors see a "not found" page and the menu entry is hidden.';
        }));

        // ---- reset to default / undo / reset a whole section
        const setReset = (field, on) => {
            const flag = $('[data-pe-reset-flag]', field);
            if (!flag) return;
            flag.value = on ? '1' : '';
            field.classList.toggle('is-reset', on);
            const control = $('[data-pe-control]', field); if (control) control.hidden = on;
            const note = $('[data-pe-reset-note]', field); if (note) note.hidden = !on;
            const btn = $('[data-pe-reset]', field); if (btn) btn.hidden = on;
            mark(form);
        };
        form.addEventListener('click', (e) => {
            const r = e.target.closest('[data-pe-reset]');
            if (r) { setReset(r.closest('[data-pe-field]'), true); const u = $('[data-pe-undo]', r.closest('[data-pe-field]')); if (u) u.focus(); return; }
            const u = e.target.closest('[data-pe-undo]');
            if (u) { const f = u.closest('[data-pe-field]'); setReset(f, false); const b = $('[data-pe-reset]', f); if (b) b.focus(); return; }
            const rs = e.target.closest('[data-pe-reset-section]');
            if (rs) {
                const s = rs.closest('[data-pe-section]');
                $$('[data-pe-field]', s).forEach((f) => { if ($('[data-pe-reset-flag]', f)) setReset(f, true); });
                const vis = $('[data-pe-visible]', s);
                if (vis) { vis.checked = vis.dataset.default === '1'; syncVisible(vis); }
                rs.hidden = true;
                mark(form);
            }
        });

        // ---- image fields (delegated, so repeater rows added later work too)
        const imgBox = (el) => el.closest('[data-pe-image]');
        const setPreview = (box, src) => {
            const prev = $('[data-pe-image-preview]', box);
            const img = prev && $('img', prev);
            const clear = $('[data-pe-image-clear]', box);
            const label = $('[data-pe-image-label]', box);
            if (!prev || !img) return;
            if (src) { img.src = src; prev.classList.remove('is-empty'); } else { img.removeAttribute('src'); prev.classList.add('is-empty'); }
            if (clear) clear.hidden = !src;
            if (label) label.textContent = src ? 'Replace image' : 'Choose an image';
        };
        form.addEventListener('change', async (e) => {
            const input = e.target.closest('[data-pe-image-input]');
            if (!input) return;
            const box = imgBox(input);
            const file = input.files && input.files[0];
            const status = $('[data-pe-image-status]', box);
            const pathInput = $('[data-pe-image-path]', box);
            if (!file || !pathInput) return;
            const say = (msg, bad) => { if (status) { status.textContent = msg; status.classList.toggle('is-bad', !!bad); } };
            if (!/^image\/(jpeg|png|gif|webp)$/.test(file.type)) { say('Please choose a JPG, PNG, GIF or WebP picture.', true); input.value = ''; return; }
            const max = parseInt(box.dataset.maxBytes || '5242880', 10);
            if (file.size > max) { say('That picture is ' + (file.size / 1048576).toFixed(1) + ' MB. The limit is ' + (max / 1048576).toFixed(0) + ' MB: please shrink it first.', true); input.value = ''; return; }
            const prevPath = pathInput.value;
            const prevImg = $('[data-pe-image-preview] img', box);
            const prevSrc = prevImg ? (prevImg.getAttribute('src') || '') : '';
            const local = URL.createObjectURL(file);
            setPreview(box, local);
            box.classList.add('is-busy');
            say('Uploading...');
            try {
                const out = await window.LuminaAdmin.uploadImage(file);
                pathInput.value = out.path;
                setPreview(box, out.url);
                say('Uploaded. Save the page to publish the new picture.');
                pathInput.dispatchEvent(new Event('input', { bubbles: true }));
                const alt = $('[data-pe-image-alt]', box);
                if (alt && alt.value.trim() === '') alt.focus();
            } catch (err) {
                pathInput.value = prevPath;
                setPreview(box, prevSrc);
                say(err && err.message ? err.message : 'The upload failed. Please try again.', true);
            } finally {
                box.classList.remove('is-busy');
                input.value = '';
                URL.revokeObjectURL(local);
            }
        });
        form.addEventListener('click', (e) => {
            const clear = e.target.closest('[data-pe-image-clear]');
            if (!clear) return;
            const box = imgBox(clear);
            const pathInput = $('[data-pe-image-path]', box);
            if (pathInput) { pathInput.value = ''; pathInput.dispatchEvent(new Event('input', { bubbles: true })); }
            setPreview(box, '');
            const status = $('[data-pe-image-status]', box);
            if (status) { status.textContent = 'Removed. Save the page to apply.'; status.classList.remove('is-bad'); }
        });

        // ---- repeaters: row titles, empty message, rich text in new rows
        form.addEventListener('input', (e) => {
            const src = e.target.closest('[data-pe-row-source]');
            if (!src) return;
            const row = src.closest('[data-repeater-row]');
            const title = row && $('[data-pe-row-title]', row);
            if (title) title.textContent = src.value.trim() || title.dataset.peRowFallback || 'New item';
        });
        const syncEmpty = (rep) => {
            const empty = $('[data-pe-empty]', rep);
            if (empty) empty.hidden = $$(':scope > [data-repeater-list] > [data-repeater-row]', rep).length > 0;
        };
        form.addEventListener('repeater:change', (e) => { const rep = e.target.closest('[data-repeater]'); if (rep) syncEmpty(rep); });
        form.addEventListener('repeater:add', (e) => {
            const row = e.detail && e.detail.row;
            if (!row) return;
            // give the rich-text editor (assets/js/admin-editor.js) a chance to upgrade new <textarea data-richtext> rows
            try {
                if (window.LuminaEditor && typeof window.LuminaEditor.init === 'function') window.LuminaEditor.init(row);
                document.dispatchEvent(new CustomEvent('lumina:content-added', { detail: { root: row } }));
            } catch (_) { /* editor is optional */ }
        });
        $$('[data-repeater]', form).forEach(syncEmpty);

        // ---- SEO: counters + search-result preview
        const counters = $$('[data-pe-count]', form);
        counters.forEach((c) => {
            const input = $(c.dataset.peCount);
            if (!input) return;
            const good = parseInt(c.dataset.peGood || '0', 10);
            const upd = () => { const n = input.value.length; c.textContent = n + (good ? ' / ' + good + ' ideal' : ''); c.classList.toggle('is-over', good > 0 && n > good); };
            input.addEventListener('input', upd); upd();
        });
        const sTitle = $('[data-pe-serp-title]', form);
        const sDesc = $('[data-pe-serp-desc]', form);
        const outTitle = $('[data-pe-serp-out-title]', form);
        const outDesc = $('[data-pe-serp-out-desc]', form);
        if (outTitle && outDesc && sTitle && sDesc) {
            const site = outTitle.dataset.peSite || '';
            const serp = () => {
                let t = (sTitle.value || sTitle.placeholder || '').trim();
                if (t && site && !t.toLowerCase().includes(site.toLowerCase()) && outTitle.dataset.peHome !== '1') t += ' | ' + site;
                outTitle.textContent = t;
                const d = (sDesc.value || sDesc.placeholder || '').replace(/\s+/g, ' ').trim();
                outDesc.textContent = d.length > 160 ? d.slice(0, 157).replace(/\s+\S*$/, '') + '...' : d;
            };
            sTitle.addEventListener('input', serp); sDesc.addEventListener('input', serp);
            const titleField = $('#f-title', form);   // custom pages: the placeholder follows the page title
            if (titleField) titleField.addEventListener('input', () => { sTitle.placeholder = titleField.value || 'The page title'; serp(); });
            serp();
        }

        // ---- submit: save scroll position, pack very large forms into one JSON field (PHP max_input_vars), Ctrl/Cmd+S
        const scrollKey = 'lumina.pe.scroll.' + slugKey;
        const unpack = () => {
            $$('[data-pe-packed]', form).forEach((el) => el.remove());
            Array.from(form.elements).forEach((el) => { if (el.dataset && el.dataset.peDisabledForPack) { el.disabled = false; delete el.dataset.peDisabledForPack; } });
        };
        window.addEventListener('pageshow', (ev) => { if (ev.persisted) { unpack(); delete form.dataset.peBusy; } });
        const parseName = (name) => { const toks = []; const first = name.indexOf('['); const re = /\[([^\]]*)\]/g; let m; while ((m = re.exec(name.slice(first))) !== null) toks.push(m[1]); return toks; };
        const assign = (obj, toks, value) => {
            let o = obj;
            toks.forEach((t, i) => {
                if (i === toks.length - 1) { o[t] = value; return; }
                if (typeof o[t] !== 'object' || o[t] === null) o[t] = {};
                o = o[t];
            });
        };
        form.addEventListener('submit', () => {
            store.sset(scrollKey, JSON.stringify({ y: window.scrollY, t: Date.now() }));
            const limit = parseInt(form.dataset.peThreshold || '450', 10);
            const data = new FormData(form);
            let count = 0;
            for (const _k of data.keys()) count++;
            if (count <= limit) return;
            const blocks = {};
            for (const [k, v] of data.entries()) {
                if (!k.startsWith('blocks[') || typeof v !== 'string') continue;
                assign(blocks, parseName(k), v);
            }
            Array.from(form.elements).forEach((el) => {
                if (el.name && el.name.startsWith('blocks[') && !el.disabled) { el.disabled = true; el.dataset.peDisabledForPack = '1'; }
            });
            const packed = document.createElement('input');
            packed.type = 'hidden'; packed.name = 'blocks_json'; packed.value = JSON.stringify(blocks); packed.dataset.pePacked = '1';
            form.appendChild(packed);
        });
        const savedScroll = (() => { try { return JSON.parse(store.sget(scrollKey) || 'null'); } catch (_) { return null; } })();
        if (savedScroll && Date.now() - savedScroll.t < 120000 && !hasErrors && !ht && $('.alert--success')) {
            window.scrollTo(0, savedScroll.y || 0);
        }
        store.sdel(scrollKey);
        if (hasErrors) {
            const first = $('.field--error', form);
            if (first) window.setTimeout(() => first.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' }), 20);
        } else if (ht) {
            window.setTimeout(() => ht.scrollIntoView({ block: 'start' }), 20);
        }
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                const btn = $('[data-pe-save]', form);
                if (typeof form.requestSubmit === 'function') form.requestSubmit(btn || undefined); else form.submit();
            }
        });
    }
})();
