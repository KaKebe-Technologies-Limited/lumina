/*!
 * admin-membership-card.js - the card designer (Admin > Membership > Membership card) and the type-code example on the membership type form.
 * OWNER: CARD agent. Progressive enhancement only: without JavaScript the form is one ordinary POST (all tabs visible, the tab bar is a list of anchor links) and the
 * preview shows the saved card. No inline JavaScript anywhere (CSP script-src 'self').
 *
 * Hooks (all data attributes):
 *   form[data-mc-form]            data-preview-url (POST: remember the unsaved values, answer JSON), data-stale (1 = the form holds unsaved text after a failed save),
 *                                 data-active-tab, data-mc-contrast (JSON: the colour pairs), data-mc-prefix, data-mc-types (JSON: id, name, code)
 *   [data-mc-tab] / [data-mc-panel]   tabs; the URL hash #tab-<name> (or #f-<field>) opens a tab; arrow keys move between tabs
 *   input[data-mc-hex="navy"] + input[data-mc-swatch]   a colour as hex text (the field that is posted) and the browser's colour picker
 *   [data-mc-theme='{"navy":"#..."}']   a button that sets the colours of a theme
 *   [data-mc-checks] / [data-mc-check] / [data-mc-colorstate] / [data-mc-checks-summary]   live contrast list
 *   [data-mc-chips][data-mc-scope="css selector"] > [data-mc-insert="{placeholder}"]   insert a placeholder into the field used last inside the scope
 *   input[data-mc-format] + [data-mc-format-status] + [data-mc-example="<type id>"] + [data-mc-format-preset="..."]   member number format with live examples
 *   [data-mc-tile] selects + [data-mc-tilelabel="<tile key>"]   dim the labels of tiles that are not used
 *   [data-mc-preview]: [data-mc-frame] (PDF), [data-mc-img] (PNG), [data-mc-scenario], [data-mc-view=pdf|png], [data-mc-refresh], [data-mc-fullsize] + [data-mc-dialog],
 *                      [data-mc-auto], [data-mc-status] (live region), [data-mc-live-text], [data-mc-open], [data-mc-download]
 *   [data-mc-mail] [data-mc-mail-html] [data-mc-mail-text] [data-mc-mail-subject] [data-mc-mail-view]   the rendered e-mail
 *   form[data-mc-test]            "send a test card": the latest values are pushed to the preview first
 *   form[data-mc-reset]           the "back to the reference" buttons (no unsaved-changes prompt)
 *   [data-mc-codeexample] + input[data-mc-code]   (membership type form) the member number the code would give
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var store = {
        get: function (k) { try { return window.localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { window.localStorage.setItem(k, v); } catch (e) { /* private mode: the page works without it */ } }
    };
    function fire(el, type) {
        var ev;
        if (typeof Event === 'function') { ev = new Event(type || 'input', { bubbles: true }); } else { ev = document.createEvent('Event'); ev.initEvent(type || 'input', true, true); }
        el.dispatchEvent(ev);
    }

    // ---------------------------------------------------------------------------------------------------------------- shared maths
    /** "#0f1b33" / "0F1B33" / "#abc" -> "#0F1B33"; null when it is not a colour (same rule as CardSettings::normaliseColor). */
    function normHex(v) {
        var h = String(v || '').trim().replace(/^#/, '');
        if (/^[0-9a-f]{3}$/i.test(h)) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
        return /^[0-9a-f]{6}$/i.test(h) ? '#' + h.toUpperCase() : null;
    }
    /** WCAG relative luminance (same formula as CardSettings::luminance). */
    function luminance(hex) {
        var c = [1, 3, 5].map(function (i) {
            var v = parseInt(hex.substr(i, 2), 16) / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
    }
    function contrast(a, b) {
        var la = luminance(a), lb = luminance(b);
        var hi = Math.max(la, lb), lo = Math.min(la, lb);
        return (hi + 0.05) / (lo + 0.05);
    }

    /** The member number the way the server builds it (CardSettings::expandNumber). */
    function expandNumber(format, prefix, code, year, seq) {
        return format.replace(/\{(PREFIX|TYPE|YYYY|YY|SEQ(?::[3-8])?)\}/g, function (m, t) {
            if (t === 'PREFIX') { return prefix; }
            if (t === 'TYPE') { return code; }
            if (t === 'YYYY') { return String(year); }
            if (t === 'YY') { return String(year).slice(-2); }
            var pad = t.indexOf(':') >= 0 ? parseInt(t.slice(4), 10) : 4;
            var s = String(seq);
            while (s.length < pad) { s = '0' + s; }
            return s;
        });
    }
    /** Why a member number format is not acceptable, or '' (the same rules as CardSettings::numberFormatError; the server decides). */
    function formatError(f) {
        f = f.trim();
        if (f === '') { return 'Enter a number format such as {PREFIX}-{TYPE}-{YY}-{SEQ}.'; }
        if (f.length > 60) { return 'The number format is too long.'; }
        if (!/\{SEQ(?::[3-8])?\}$/.test(f)) { return 'The number format must end with {SEQ} (the running number), for example {PREFIX}-{TYPE}-{YY}-{SEQ}.'; }
        if ((f.match(/\{SEQ(?::[3-8])?\}/g) || []).length !== 1) { return 'Use {SEQ} only once.'; }
        var rest = f.replace(/\{(PREFIX|TYPE|YY|YYYY)\}|\{SEQ(?::[3-8])?\}/g, '');
        if (rest.indexOf('{') >= 0 || rest.indexOf('}') >= 0) { return 'Only {PREFIX}, {TYPE}, {YY}, {YYYY} and {SEQ} may be used inside braces.'; }
        if (!/^[A-Z0-9-]*$/.test(rest)) { return 'Besides the placeholders, use only capital letters, digits and "-".'; }
        if (expandNumber(f, 'PPPPPPPPPP', 'TTTTTTTT', 2099, 99999999).length > 40) { return 'The longest possible number would be more than 40 characters. Shorten the format.'; }
        if (f.charAt(0) === '-' || f.indexOf('--') >= 0) { return 'Do not start the format with "-" or write "--".'; }
        if (/\{(?:TYPE|YY|YYYY)\}\{SEQ/.test(f)) { return 'Put a "-" between {TYPE}, {YY} or {YYYY} and {SEQ}, for example {PREFIX}-{TYPE}-{YY}-{SEQ}.'; }
        return '';
    }

    // ---------------------------------------------------------------------------------------------------------------- membership type form: the code example
    $$('[data-mc-codeexample]').forEach(function (box) {
        var scope = box.closest('form') || document;
        var input = $('[data-mc-code]', scope);
        var out = $('[data-mc-codeexample-value]', box);
        if (!input || !out) { return; }
        var format = box.dataset.format || '';
        var prefix = box.dataset.prefix || '';
        var fallback = box.dataset.fallback || '';
        var year = parseInt(box.dataset.year || String(new Date().getFullYear()), 10);
        var msg = $('[data-mc-codeexample-note]', box);
        function update() {
            var raw = input.value.trim().toUpperCase();
            var ok = raw === '' || /^[A-Z0-9]{2,8}$/.test(raw);
            var code = raw !== '' && ok ? raw : fallback;
            out.textContent = format !== '' && formatError(format) === '' ? expandNumber(format, prefix, code, year, 1) : '';
            box.classList.toggle('is-invalid', !ok);
            if (msg) { msg.textContent = ok ? (raw === '' ? 'No code typed: the initials of the name are used (' + fallback + ').' : '') : 'Use 2 to 8 letters or digits, for example IND.'; }
            if (format.indexOf('{TYPE}') < 0) { box.classList.add('is-unused'); } else { box.classList.remove('is-unused'); }
        }
        input.addEventListener('input', update);
        update();
    });

    var form = $('[data-mc-form]');
    if (!form) { return; }

    // ---------------------------------------------------------------------------------------------------------------- the designer
    var tabs = $$('[data-mc-tab]', form);
    var panels = $$('[data-mc-panel]', form);
    var tabInputs = $$('[data-mc-tab-input]');
    var statusEl = $('[data-mc-status]');
    var live = $('[data-mc-live]');
    var liveText = $('[data-mc-live-text]');
    var dirtyNote = $('[data-mc-dirty-note]');

    // ---- tabs -----------------------------------------------------------------------------------------------------
    function tabOfElement(el) {
        var p = el && el.closest ? el.closest('[data-mc-panel]') : null;
        return p ? p.dataset.mcPanel : '';
    }
    function showTab(name, opts) {
        opts = opts || {};
        if (!name || !tabs.some(function (t) { return t.dataset.mcTab === name; })) { name = tabs.length ? tabs[0].dataset.mcTab : ''; }
        tabs.forEach(function (t) {
            var on = t.dataset.mcTab === name;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
        });
        panels.forEach(function (p) { p.hidden = p.dataset.mcPanel !== name; });
        tabInputs.forEach(function (i) { i.value = name; });
        if (opts.hash && window.history && typeof window.history.replaceState === 'function') {
            try { window.history.replaceState(null, '', '#tab-' + name); } catch (e) { /* a sandboxed frame: the tab still works */ }
        }
        var active = $('[data-mc-tab="' + name + '"]', form);
        if (active && opts.scrollTab && typeof active.scrollIntoView === 'function') { active.scrollIntoView({ block: 'nearest', inline: 'center' }); }
        return name;
    }
    function openFromHash(hash, focus) {
        var h = String(hash || '').replace(/^#/, '');
        if (/^tab-/.test(h)) { showTab(h.slice(4), { scrollTab: true }); return true; }
        if (h) {
            var target = document.getElementById(h);
            var tab = tabOfElement(target);
            if (target && tab) {
                showTab(tab, { hash: false, scrollTab: true });
                if (focus && typeof target.focus === 'function') { target.focus(); }
                if (typeof target.scrollIntoView === 'function') { target.scrollIntoView({ block: 'center' }); }
                return true;
            }
        }
        return false;
    }
    /** After a tab is chosen by hand, bring the top of its panel into view when the page was scrolled past it. */
    function scrollToPanel(name) {
        var p = $('[data-mc-panel="' + name + '"]', form);
        if (!p || typeof p.getBoundingClientRect !== 'function' || typeof p.scrollIntoView !== 'function') { return; }
        if (p.getBoundingClientRect().top < 120) { p.scrollIntoView({ block: 'start' }); }
    }
    tabs.forEach(function (t, i) {
        t.addEventListener('click', function (ev) {
            ev.preventDefault();
            showTab(t.dataset.mcTab, { hash: true, scrollTab: true });
            scrollToPanel(t.dataset.mcTab);
        });
        t.addEventListener('keydown', function (ev) {
            var to = -1;
            if (ev.key === 'ArrowRight') { to = (i + 1) % tabs.length; }
            else if (ev.key === 'ArrowLeft') { to = (i - 1 + tabs.length) % tabs.length; }
            else if (ev.key === 'Home') { to = 0; }
            else if (ev.key === 'End') { to = tabs.length - 1; }
            if (to >= 0) {
                ev.preventDefault();
                showTab(tabs[to].dataset.mcTab, { hash: true, scrollTab: true });
                tabs[to].focus();
                scrollToPanel(tabs[to].dataset.mcTab);
            }
        });
    });
    $$('[data-mc-goto]').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            ev.preventDefault();
            openFromHash(a.getAttribute('href'), true);
        });
    });
    window.addEventListener('hashchange', function () { openFromHash(window.location.hash, false); });
    if (!openFromHash(window.location.hash, false)) { showTab(form.dataset.activeTab || 'look', { scrollTab: true }); }
    var firstError = $('.field--error [aria-invalid="true"], .field--error input, .field--error select, .field--error textarea', form);
    if (firstError && !/^#tab-|^#f-/.test(window.location.hash)) { showTab(tabOfElement(firstError) || form.dataset.activeTab, { scrollTab: true }); }
    var errBox = $('[data-mc-errors]');
    if (errBox) { try { errBox.focus({ preventScroll: false }); } catch (e) { /* older browsers */ } }

    // ---- unsaved changes ------------------------------------------------------------------------------------------
    function controlsOf(root) {
        return $$('input[name], select[name], textarea[name]', root).filter(function (el) { return el.type !== 'file' && el.name !== '_token' && el.name !== 'tab'; });
    }
    function valueOf(el) { return el.type === 'checkbox' || el.type === 'radio' ? (el.checked ? '1' : '0') : el.value; }
    var initial = new Map();
    controlsOf(form).forEach(function (el) { initial.set(el, valueOf(el)); });
    var dirtyCount = 0;
    function updateDirty() {
        dirtyCount = 0;
        panels.forEach(function (p) {
            var n = controlsOf(p).filter(function (el) { return initial.has(el) && initial.get(el) !== valueOf(el); }).length;
            dirtyCount += n;
            var flag = $('[data-mc-tab="' + p.dataset.mcPanel + '"] [data-mc-dirty-flag]', form);
            if (flag) { flag.hidden = n === 0; }
        });
        if (dirtyNote) { dirtyNote.textContent = dirtyCount === 0 ? '' : dirtyCount + (dirtyCount === 1 ? ' change' : ' changes') + ' not saved yet'; }
        if (liveText) { liveText.textContent = dirtyCount === 0 && form.dataset.stale !== '1' ? 'Saved design' : 'Showing unsaved changes'; }
        if (live) { live.classList.toggle('is-unsaved', dirtyCount > 0 || form.dataset.stale === '1'); }
    }

    // ---- colours and contrast -------------------------------------------------------------------------------------
    var contrastDefs = [];
    try { contrastDefs = JSON.parse(form.dataset.mcContrast || '[]'); } catch (e) { contrastDefs = []; }
    function colorOf(name) {
        if (name.charAt(0) === '#') { return name; }
        var inp = $('[data-mc-hex="' + name + '"]', form);
        if (!inp) { return '#000000'; }
        return normHex(inp.value) || normHex(inp.dataset.mcDefault) || '#000000';
    }
    var STATE_TEXT = { ok: 'Easy to read', warn: 'Below the advised contrast for small text', fail: 'Unreadable' };
    function recomputeContrast() {
        var worst = {};
        var count = { ok: 0, warn: 0, fail: 0 };
        contrastDefs.forEach(function (d) {
            var fg = colorOf(d.fg), bg = colorOf(d.bg);
            var r = contrast(fg, bg);
            var st = r < d.fail ? 'fail' : (r < d.min ? 'warn' : 'ok');
            count[st] += 1;
            d.keys.forEach(function (k) {
                var w = worst[k] || 'ok';
                worst[k] = st === 'fail' || w === 'fail' ? 'fail' : (st === 'warn' || w === 'warn' ? 'warn' : 'ok');
            });
            var li = $('[data-mc-check="' + d.id + '"]', form);
            if (li) {
                li.classList.remove('is-ok', 'is-warn', 'is-fail');
                li.classList.add('is-' + st);
                var sample = $('[data-mc-sample]', li);
                if (sample) { sample.style.color = fg; sample.style.background = bg; }
                var ratio = $('[data-mc-ratio]', li);
                if (ratio) { ratio.textContent = (Math.round(r * 10) / 10).toFixed(1); }
                var state = $('[data-mc-checkstate]', li);
                if (state) { state.textContent = st === 'ok' ? 'good' : (st === 'warn' ? (r >= 3 ? 'large text only' : 'hard to read') : 'unreadable'); }
            }
        });
        $$('[data-mc-colorfield]', form).forEach(function (field) {
            var short = field.dataset.mcColorfield;
            var hex = $('[data-mc-hex]', field);
            var valid = normHex(hex.value) !== null;
            var st = valid ? (worst[short] || 'ok') : 'fail';
            var box = $('[data-mc-colorstate]', field);
            if (box) {
                box.classList.remove('is-ok', 'is-warn', 'is-fail');
                box.classList.add('is-' + st);
                var span = $('span', box);
                if (span) { span.textContent = valid ? STATE_TEXT[st] : 'Not a colour yet: use a hex code such as ' + (hex.dataset.mcDefault || '#0F1B33'); }
            }
            if (valid) { hex.removeAttribute('aria-invalid'); } else { hex.setAttribute('aria-invalid', 'true'); }
        });
        var sum = $('[data-mc-checks-summary]', form);
        if (sum) {
            sum.classList.remove('is-ok', 'is-warn', 'is-fail');
            sum.classList.add('is-' + (count.fail ? 'fail' : (count.warn ? 'warn' : 'ok')));
            var textNode = count.fail
                ? count.fail + (count.fail === 1 ? ' combination cannot be read at all' : ' combinations cannot be read at all') + ': change a colour before saving.'
                : (count.warn ? count.warn + (count.warn === 1 ? ' combination is' : ' combinations are') + ' below the advised contrast for small text (see the list).' : 'Every combination meets the advised contrast.');
            var svg = $('svg', sum);
            sum.textContent = '';
            if (svg) { sum.appendChild(svg); sum.appendChild(document.createTextNode(' ')); }
            sum.appendChild(document.createTextNode(textNode));
        }
    }
    $$('[data-mc-colorfield]', form).forEach(function (field) {
        var hex = $('[data-mc-hex]', field);
        var sw = $('[data-mc-swatch]', field);
        var reset = $('[data-mc-color-reset]', field);
        hex.addEventListener('input', function () {
            var n = normHex(hex.value);
            if (n && sw) { sw.value = n.toLowerCase(); }
            recomputeContrast();
        });
        hex.addEventListener('blur', function () { var n = normHex(hex.value); if (n && n !== hex.value) { hex.value = n; } });
        if (sw) { sw.addEventListener('input', function () { hex.value = sw.value.toUpperCase(); fire(hex); }); }
        if (reset) { reset.addEventListener('click', function () { hex.value = hex.dataset.mcDefault || hex.value; fire(hex); hex.focus(); }); }
    });
    $$('[data-mc-theme]', form).forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = {};
            try { theme = JSON.parse(btn.dataset.mcTheme); } catch (e) { return; }
            var last = null;
            Object.keys(theme).forEach(function (short) {
                var hex = $('[data-mc-hex="' + short + '"]', form);
                if (!hex) { return; }
                hex.value = theme[short];
                var sw = $('[data-mc-swatch]', hex.closest('[data-mc-colorfield]'));
                if (sw) { sw.value = String(theme[short]).toLowerCase(); }
                last = hex;
            });
            if (last) { fire(last); }
            if (statusEl) { statusEl.textContent = 'Colours changed. Save to keep them.'; }
        });
    });

    // ---- placeholder chips ----------------------------------------------------------------------------------------
    function insertAt(el, text) {
        if (!el) { return; }
        el.focus();
        var s = typeof el.selectionStart === 'number' ? el.selectionStart : el.value.length;
        var e = typeof el.selectionEnd === 'number' ? el.selectionEnd : s;
        el.value = el.value.slice(0, s) + text + el.value.slice(e);
        var pos = s + text.length;
        if (typeof el.setSelectionRange === 'function') { try { el.setSelectionRange(pos, pos); } catch (err) { /* e-mail inputs refuse selection ranges */ } }
        fire(el);
    }
    $$('[data-mc-chips]', form).forEach(function (group) {
        var fields = $$(group.dataset.mcScope || '', form);
        var last = fields[0] || null;
        fields.forEach(function (f) {
            f.addEventListener('focus', function () { last = f; });
            f.addEventListener('input', function () { last = f; });          // typing proves it is the field in use (a browser window without focus fires no focus event)
        });
        group.addEventListener('click', function (ev) {
            var b = ev.target.closest('[data-mc-insert]');
            if (b) { insertAt(last, b.dataset.mcInsert); }
        });
    });

    // ---- member number format -------------------------------------------------------------------------------------
    var fmt = $('[data-mc-format]', form);
    var fmtStatus = $('[data-mc-format-status]', form);
    var prefix = form.dataset.mcPrefix || '';
    var types = [];
    try { types = JSON.parse(form.dataset.mcTypes || '[]'); } catch (e) { types = []; }
    var serverFormatError = '';
    function updateFormat() {
        if (!fmt) { return; }
        var f = fmt.value.trim().toUpperCase();
        var err = formatError(f) || serverFormatError;
        var year = new Date().getFullYear();
        types.forEach(function (t) {
            var cell = $('[data-mc-example="' + t.id + '"]', form);
            if (cell) { cell.textContent = err ? '-' : expandNumber(f, prefix, t.code, year, 1); }
        });
        if (fmtStatus) {
            fmtStatus.classList.toggle('is-error', err !== '');
            fmtStatus.classList.toggle('is-ok', err === '');
            fmtStatus.textContent = err !== '' ? err : 'This format works' + (types.length ? '. An example for "' + types[0].name + '" (running number 1): ' + expandNumber(f, prefix, types[0].code, year, 1) + '.' : '.');
        }
        if (err) { fmt.setAttribute('aria-invalid', 'true'); } else { fmt.removeAttribute('aria-invalid'); }
    }
    if (fmt) {
        fmt.addEventListener('input', function () { serverFormatError = ''; updateFormat(); });
        fmt.addEventListener('blur', function () { var u = fmt.value.trim().toUpperCase(); if (u !== fmt.value) { fmt.value = u; updateFormat(); } });
        $$('[data-mc-format-preset]', form).forEach(function (b) {
            b.addEventListener('click', function () { fmt.value = b.dataset.mcFormatPreset; fire(fmt); fmt.focus(); });
        });
    }

    // ---- tiles: dim the labels of tiles that are not used ----------------------------------------------------------
    var tileSelects = $$('[data-mc-tile]', form);
    function updateTiles() {
        var used = {};
        tileSelects.forEach(function (s) { used[s.value] = true; });
        $$('[data-mc-tilelabel]', form).forEach(function (box) {
            var on = !!used[box.dataset.mcTilelabel];
            box.classList.toggle('is-unused', !on);
            var note = $('.mc-unused-note', box);
            if (!on && !note) {
                note = document.createElement('p');
                note.className = 'mc-unused-note';
                note.textContent = 'No tile uses this now.';
                box.appendChild(note);
            } else if (on && note) {
                note.parentNode.removeChild(note);
            }
        });
    }
    tileSelects.forEach(function (s) { s.addEventListener('change', updateTiles); });

    // ---------------------------------------------------------------------------------------------------------------- the preview
    var preview = $('[data-mc-preview]');
    var frame = $('[data-mc-frame]');
    var img = $('[data-mc-img]');
    var busyEl = $('[data-mc-busy]');
    var stage = $('[data-mc-stage]');
    var scenarioSel = $('[data-mc-scenario]');
    var autoChk = $('[data-mc-auto]');
    var openLink = $('[data-mc-open]');
    var downloadLink = $('[data-mc-download]');
    var refreshBtn = $('[data-mc-refresh]');
    var fullBtn = $('[data-mc-fullsize]');
    var dialog = $('[data-mc-dialog]');
    var dialogFrame = $('[data-mc-dialog-frame]');
    var hasPng = !!(preview && preview.dataset.hasPng === '1' && img);
    var currentPdf = '';
    var timer = null;
    var busy = false;
    var again = false;
    var waiters = [];
    var retryTimer = null;
    var PDF_HASH = '#toolbar=0&navpanes=0&view=Fit';
    if (frame && frame.getAttribute('src')) { currentPdf = frame.getAttribute('src').split('#')[0]; }
    var lastWidth = window.innerWidth;
    var resizeTimer = null;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () { if (window.innerWidth !== lastWidth) { lastWidth = window.innerWidth; if (view === 'pdf') { reloadFrame(); } } }, 500);
    });

    function say(msg) { if (statusEl) { statusEl.textContent = msg || ''; } }
    function setBusy(on) {
        if (busyEl) { busyEl.hidden = !on; }
        if (stage) { stage.setAttribute('aria-busy', on ? 'true' : 'false'); }
    }

    // view: PDF (exact) or picture
    var view = 'pdf';
    /** The PDF viewer of a browser fits the page to the frame when the PDF loads, not when the frame is resized or shown again: load it again then. */
    function reloadFrame() {
        if (!frame || !currentPdf) { return; }
        frame.src = currentPdf + (currentPdf.indexOf('?') >= 0 ? '&' : '?') + 'r=' + Date.now() + PDF_HASH;
    }
    function setView(v) {
        var was = view;
        view = v === 'png' && hasPng ? 'png' : 'pdf';
        if (frame) { frame.hidden = view !== 'pdf'; }
        if (view === 'pdf' && was === 'png') { reloadFrame(); }
        if (img) { img.hidden = view !== 'png'; }
        $$('[data-mc-view]', preview || document).forEach(function (b) {
            var on = b.dataset.mcView === view;
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        store.set('mc-view', view);
    }
    if (preview) {
        var narrow = window.matchMedia ? window.matchMedia('(max-width: 899px)').matches : false;
        var saved = store.get('mc-view');
        setView(saved === 'png' || saved === 'pdf' ? saved : (narrow && hasPng ? 'png' : 'pdf'));
        $$('[data-mc-view]', preview).forEach(function (b) { b.addEventListener('click', function () { setView(b.dataset.mcView); }); });
        if (autoChk) {
            var a = store.get('mc-auto');
            if (a === '0') { autoChk.checked = false; }
            autoChk.addEventListener('change', function () { store.set('mc-auto', autoChk.checked ? '1' : '0'); if (autoChk.checked) { schedule(0); } });
        }
    }

    function payload() {
        var d = new FormData(form);
        d.delete('membership_card_logo_file');               // the picture field already uploaded the file; the preview only needs its path
        d.set('scenario', scenarioSel ? scenarioSel.value : 'typical');
        return d;
    }
    function csrfToken() {
        var el = form.querySelector('input[name="_token"]');
        return el ? el.value : '';
    }
    function clearLiveErrors() {
        $$('[data-mc-live-error]', form).forEach(function (n) {
            var field = n.closest('.field');
            if (field) { field.classList.remove('field--error'); }
            n.parentNode.removeChild(n);
        });
    }
    function showLiveErrors(errors) {
        clearLiveErrors();
        serverFormatError = '';
        Object.keys(errors || {}).forEach(function (key) {
            var ctrl = document.getElementById('f-' + key) || form.querySelector('[name="' + key + '"]');
            if (key === 'membership_card_number_format') { serverFormatError = String(errors[key]); }
            if (!ctrl) { return; }
            var field = ctrl.closest('.field, fieldset') || ctrl.parentNode;
            if (!field || field.querySelector('.field__error:not([data-mc-live-error])')) { return; }
            var div = document.createElement('div');
            div.className = 'field__error';
            div.setAttribute('data-mc-live-error', '1');
            div.textContent = String(errors[key]);
            field.appendChild(div);
            if (field.classList) { field.classList.add('field--error'); }
        });
        updateFormat();
    }
    function applyResult(j) {
        if (!j || !j.ok) { return; }
        if (j.pdf) {
            currentPdf = j.pdf;
            if (frame) { frame.src = j.pdf + PDF_HASH; }
            if (openLink) { openLink.href = j.pdf; }
            if (downloadLink) { downloadLink.href = j.pdf + (j.pdf.indexOf('?') >= 0 ? '&' : '?') + 'download=1'; }
        }
        if (img && j.png) { img.src = j.png; }
        showLiveErrors(j.errors || {});
        if (j.numbers && !serverFormatError) {
            Object.keys(j.numbers).forEach(function (id) {
                var cell = $('[data-mc-example="' + id + '"]', form);
                if (cell && !formatError((fmt ? fmt.value : '').trim().toUpperCase())) { cell.textContent = j.numbers[id]; }
            });
        }
        if (j.email) {
            var subj = $('[data-mc-mail-subject]');
            if (subj) { subj.textContent = j.email.subject; }
            var h = $('[data-mc-mail-html]');
            if (h) { h.setAttribute('srcdoc', j.email.html); }
            var t = $('[data-mc-mail-text]');
            if (t) { t.textContent = j.email.text; }
        }
        say('');
    }
    function finish() {
        busy = false;
        setBusy(false);
        if (again) { again = false; schedule(0); return; }
        var w = waiters; waiters = [];
        w.forEach(function (fn) { fn(); });
    }
    function refresh() {
        window.clearTimeout(timer);
        if (!preview || !form.dataset.previewUrl) { return; }
        if (busy) { again = true; return; }
        busy = true;
        again = false;
        setBusy(true);
        fetch(form.dataset.previewUrl, {
            method: 'POST', body: payload(), credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        }).then(function (r) {
            if (r.status === 429) {
                var wait = parseInt(r.headers.get('Retry-After') || '30', 10);
                var err = new Error('rate'); err.wait = isNaN(wait) ? 30 : Math.min(Math.max(wait, 5), 120); throw err;
            }
            if (!r.ok) { throw new Error('http ' + r.status); }
            return r.json();
        }).then(applyResult).catch(function (e) {
            if (e && e.message === 'rate') {
                say('The preview is resting for ' + e.wait + ' seconds because it was updated very often. It will refresh by itself. Saving still works.');
                window.clearTimeout(retryTimer);
                retryTimer = window.setTimeout(function () { say(''); schedule(0); }, e.wait * 1000);
            } else {
                say('The preview could not be updated just now. Saving still works; use "Update now" to try again.');
            }
        }).then(finish, finish);
    }
    function schedule(delay) {
        window.clearTimeout(timer);
        timer = window.setTimeout(refresh, typeof delay === 'number' ? delay : 900);
    }
    /** Resolves when the preview shows the latest values (used before the test e-mail is sent). */
    function flush() {
        return new Promise(function (resolve) {
            if (!preview) { resolve(); return; }
            window.clearTimeout(timer);
            var done = function () { resolve(); };
            waiters.push(done);
            if (busy) { again = true; } else { refresh(); }
            window.setTimeout(resolve, 8000);                 // never block the e-mail on a slow preview
        });
    }

    form.addEventListener('input', function (ev) {
        // an error shown after a failed save goes away as soon as the field is edited (the preview reports it again when it still applies)
        var fld = ev.target && ev.target.closest ? ev.target.closest('.field') : null;
        if (fld) {
            $$('.field__error:not([data-mc-live-error])', fld).forEach(function (n) { n.parentNode.removeChild(n); });
            fld.classList.remove('field--error');
        }
        updateDirty();
        if (!autoChk || autoChk.checked) { schedule(); }
    });
    form.addEventListener('change', function () { updateDirty(); if (!autoChk || autoChk.checked) { schedule(); } });
    if (refreshBtn) { refreshBtn.addEventListener('click', function () { refresh(); }); }
    if (scenarioSel) { scenarioSel.addEventListener('change', function () { refresh(); }); }

    // full size
    if (fullBtn && dialog && typeof dialog.showModal === 'function') {
        fullBtn.hidden = false;
        fullBtn.addEventListener('click', function () {
            var url = currentPdf || (frame ? frame.getAttribute('src') : '');
            if (!url) { return; }
            if (dialogFrame) { dialogFrame.src = url.split('#')[0] + '#view=Fit'; }
            dialog.showModal();
        });
        var closeDialog = function () { if (dialog.open) { dialog.close(); } };
        $$('[data-mc-dialog-close]', dialog).forEach(function (b) { b.addEventListener('click', closeDialog); });
        dialog.addEventListener('click', function (ev) { if (ev.target === dialog) { closeDialog(); } });
        dialog.addEventListener('close', function () { if (dialogFrame) { dialogFrame.src = 'about:blank'; } });
    }

    // e-mail preview: formatted / plain text
    $$('[data-mc-mail-view]').forEach(function (b) {
        b.addEventListener('click', function () {
            var html = $('[data-mc-mail-html]'), text = $('[data-mc-mail-text]');
            var wantText = b.dataset.mcMailView === 'text';
            if (html) { html.hidden = wantText; }
            if (text) { text.hidden = !wantText; }
            $$('[data-mc-mail-view]').forEach(function (o) {
                var on = o === b;
                o.classList.toggle('is-active', on);
                o.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
        });
    });

    // test e-mail: show the latest values first
    var testForm = $('[data-mc-test]');
    if (testForm) {
        testForm.addEventListener('submit', function (ev) {
            if (testForm.dataset.ready === '1') { return; }
            ev.preventDefault();
            flush().then(function () { testForm.dataset.ready = '1'; testForm.submit(); });
        });
    }
    // "back to the reference" is its own form: leaving the page must not ask about unsaved changes
    var resetForm = $('[data-mc-reset]');
    if (resetForm) { resetForm.addEventListener('submit', function () { fire(form, 'lumina:clean'); }); }
    form.addEventListener('submit', function () { if (preview) { window.clearTimeout(timer); } });

    // ---- first paint -----------------------------------------------------------------------------------------------
    recomputeContrast();
    updateFormat();
    updateTiles();
    updateDirty();
    if (form.dataset.stale === '1') { schedule(0); }
})();
