/*!
 * admin-meetings.js - schedule form (platform hints, Jitsi button, duration, invitee chips and pickers, send confirmation), the meeting page
 * (attendee filter, add and remove people, dialogs) and the request inbox dialogs.
 * OWNER: ADMIN agent. Contract: docs/MEETINGS.md sections 10, 11 and 12.1. No inline JS (CSP). Personal data (names, addresses) is only ever written with textContent.
 *
 * Hand-over to the sending panel (assets/js/admin-meetings-send.js, MAIL agent): the schedule form posts to the controller, which queues the messages and redirects
 * to the meeting page with ?send=1#sending; there the panel [data-meeting-send] (data-api, data-id, data-auto="1") runs the batch loop. The meeting page's own sending
 * buttons are plain hooks that the panel handles: [data-ma-queue="invite_new|update_all|retry_failed|one"], [data-ma-test], form[data-ma-message], [data-ma-reminder].
 * Only the schedule form's "Send me a test invitation" is handled here (the panel script is not loaded on that page).
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var csrf = function () { var m = $('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; };
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function plural(n, one, many) { return n === 1 ? one : many; }
    function people(n) { return n + ' ' + plural(n, 'person', 'people'); }
    function sleep(ms) { return new Promise(function (res) { window.setTimeout(res, ms); }); }
    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined && text !== null) { n.textContent = text; }
        return n;
    }
    function lower(s) { return String(s || '').trim().toLowerCase(); }

    // ------------------------------------------------------------------ JSON API
    function api(method, url, body) {
        var opts = { method: method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf(), 'X-Requested-With': 'XMLHttpRequest' } };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (res) {
            return res.text().then(function (t) {
                var data = {};
                try { data = t ? JSON.parse(t) : {}; } catch (e) { data = {}; }
                return { ok: res.ok, status: res.status, data: data };
            });
        }, function () {
            return { ok: false, status: 0, data: { message: 'The connection failed. Check your internet and try again.' } };
        });
    }
    function errText(r, fallback) {
        if (r && r.data) {
            if (r.data.message) { return String(r.data.message); }
            if (r.data.error && r.data.error !== 'not_implemented') { return String(r.data.error); }
        }
        if (r && r.status === 501) { return 'Sending is not available yet on this website.'; }
        if (r && (r.status === 403 || r.status === 419)) { return 'Your session has expired. Reload the page and sign in again.'; }
        return fallback || 'Something went wrong. Please try again.';
    }

    // ------------------------------------------------------------------ announcements
    function say(msg, target) {
        var live = target || $('[data-ma-live]');
        if (!live) { return; }
        live.textContent = '';
        window.setTimeout(function () { live.textContent = msg; }, 30);
    }

    // ------------------------------------------------------------------ confirm dialog (one shared <dialog>, falls back to window.confirm)
    function confirmDialog(o) {
        var d = $('[data-ma-confirm]');
        if (!d || typeof d.showModal !== 'function') {
            var lines = [o.title, o.text || ''].concat((o.facts || []).map(function (f) { return f[0] + ': ' + f[1]; })).filter(Boolean);
            return Promise.resolve(window.confirm(lines.join('\n')));
        }
        var set = function (name, value) {
            var n = $('[data-ma-fill="' + name + '"]', d);
            if (!n) { return; }
            n.textContent = value || '';
            n.hidden = !value;
        };
        set('title', o.title);
        set('text', o.text);
        set('warn', o.warn);
        var dl = $('[data-ma-fill="facts"]', d);
        dl.textContent = '';
        (o.facts || []).forEach(function (f) {
            if (!f[1]) { return; }
            var row = el('div');
            row.appendChild(el('dt', null, f[0]));
            row.appendChild(el('dd', null, f[1]));
            dl.appendChild(row);
        });
        dl.hidden = !dl.firstChild;
        var ok = $('[data-ma-dialog-ok]', d);
        var cancel = $('[data-ma-dialog-cancel]', d);
        ok.textContent = o.ok || 'OK';
        ok.className = 'btn ' + (o.tone === 'danger' ? 'btn--danger' : 'btn--primary');
        cancel.textContent = o.cancel || 'Not yet';
        return new Promise(function (resolve) {
            var done = false;
            function finish(v) {
                if (done) { return; }
                done = true;
                ok.removeEventListener('click', onOk);
                cancel.removeEventListener('click', onCancel);
                d.removeEventListener('cancel', onEsc);
                d.removeEventListener('close', onClose);
                if (d.open) { d.close(); }
                resolve(v);
            }
            function onOk() { finish(true); }
            function onCancel() { finish(false); }
            function onEsc(ev) { ev.preventDefault(); finish(false); }
            function onClose() { finish(false); }
            ok.addEventListener('click', onOk);
            cancel.addEventListener('click', onCancel);
            d.addEventListener('cancel', onEsc);
            d.addEventListener('close', onClose);
            d.showModal();
            // always open at the title: the first focusable control (the button row) is at the bottom, and the browser would scroll it into view
            var body = $('.ma-dialog__body', d);
            if (body) { body.scrollTop = 0; }
            try { cancel.focus({ preventScroll: true }); } catch (e) { cancel.focus(); }
            d.scrollTop = 0;
        });
    }

    // generic dialogs: [data-ma-open="id"] opens <dialog id>, [data-ma-close] closes the one around it, a click on the backdrop closes it
    document.addEventListener('click', function (ev) {
        var t = ev.target;
        if (!(t instanceof Element)) { return; }
        var opener = t.closest('[data-ma-open]');
        if (opener) {
            var d = document.getElementById(opener.getAttribute('data-ma-open'));
            if (d && typeof d.showModal === 'function') { ev.preventDefault(); d.showModal(); }
            return;
        }
        var closer = t.closest('[data-ma-close]');
        if (closer) {
            var dd = closer.closest('dialog');
            if (dd) { ev.preventDefault(); dd.close(); }
            return;
        }
        if (t.tagName === 'DIALOG' && t.classList.contains('ma-dialog') && t.open && !t.hasAttribute('data-ma-confirm')) {
            var r = t.getBoundingClientRect();
            if (ev.clientX < r.left || ev.clientX > r.right || ev.clientY < r.top || ev.clientY > r.bottom) { t.close(); }
        }
    });

    // ------------------------------------------------------------------ copy to the clipboard
    document.addEventListener('click', function (ev) {
        var b = ev.target instanceof Element ? ev.target.closest('[data-ma-copy]') : null;
        if (!b) { return; }
        ev.preventDefault();
        var text = b.getAttribute('data-ma-copy') || '';
        var done = function (ok) { say(ok ? 'Copied to the clipboard.' : 'Could not copy. Select the link and copy it by hand.'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () { done(true); }, function () { done(false); });
            return;
        }
        var ta = el('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        ta.remove();
        done(ok);
    });

    // ------------------------------------------------------------------ the invitee box
    function initInvitees(root) {
        var apiBase = (root.getAttribute('data-api') || '').replace(/\/$/, '');
        var maxPeople = parseInt(root.getAttribute('data-max') || '200', 10);
        var threshold = parseInt(root.getAttribute('data-threshold') || '50', 10);
        var canBulk = root.getAttribute('data-can-bulk') === '1';
        var parseData = function (name) {
            try { var v = JSON.parse(root.getAttribute(name) || '[]'); return Array.isArray(v) ? v : []; } catch (e) { return []; }
        };
        var persons = new Map();                 // lower-case address -> {name,email,source,member_id,user_id}
        var groups = [];                          // {spec,label,count}
        var existing = parseData('data-existing');
        var chips = $('[data-inv-chips]', root);
        var fields = $('[data-inv-fields]', root);
        var countEl = $('[data-inv-count]', root);
        var countLabel = $('[data-inv-count-label]', root);
        var empty = $('[data-inv-empty]', root);
        var textEl = $('[data-inv-text]', root);
        var msgEl = $('[data-inv-msg]', root);
        var badEl = $('[data-inv-bad]', root);
        var tablist = $('[data-inv-tablist]', root);
        var tpl = $('template[data-inv-tpl-x]', root);
        var membersLoaded = false;
        var staffLoaded = false;

        function say2(msg) { if (msgEl) { msgEl.textContent = ''; window.setTimeout(function () { msgEl.textContent = msg; }, 30); } }
        function key(e) { return lower(e); }
        function total() {
            var n = persons.size + existing.length;
            groups.forEach(function (g) { n += g.count; });
            return n;
        }
        function addPerson(p) {
            var k = key(p.email);
            if (!k || persons.has(k)) { return false; }
            if (existing.some(function (x) { return key(x.email) === k; })) { return false; }
            if (total() + 1 > maxPeople) { say2('A meeting can have at most ' + maxPeople + ' people.'); return false; }
            persons.set(k, { name: String(p.name || ''), email: k, source: p.source || 'manual', member_id: p.member_id || null, user_id: p.user_id || null });
            return true;
        }
        function has(email) {
            var k = key(email);
            return persons.has(k) || existing.some(function (x) { return key(x.email) === k; });
        }

        // ---- render
        function render(focusKey) {
            chips.textContent = '';
            existing.forEach(function (x) {
                var li = el('li', 'ma-chip ma-chip--locked');
                var who = el('span', 'ma-chip__who');
                who.appendChild(el('span', 'ma-chip__name', x.name || x.email));
                if (x.name) { who.appendChild(el('span', 'ma-chip__mail', x.email)); }
                li.appendChild(who);
                li.appendChild(el('span', 'ma-chip__tag', x.state || 'On the list'));
                chips.appendChild(li);
            });
            groups.forEach(function (g, i) {
                var li = el('li', 'ma-chip ma-chip--group');
                var who = el('span', 'ma-chip__who');
                who.appendChild(el('span', 'ma-chip__name', g.label));
                who.appendChild(el('span', 'ma-chip__mail', people(g.count) + ' (looked up when you save)'));
                li.appendChild(who);
                li.appendChild(removeButton(g.label, function () { groups.splice(i, 1); render(); say2(g.label + ' removed.'); }));
                chips.appendChild(li);
            });
            persons.forEach(function (p, k) {
                var li = el('li', 'ma-chip ma-chip--' + (p.source || 'manual'));
                var who = el('span', 'ma-chip__who');
                who.appendChild(el('span', 'ma-chip__name', p.name || p.email));
                if (p.name) { who.appendChild(el('span', 'ma-chip__mail', p.email)); }
                li.appendChild(who);
                if (p.source === 'member') { li.appendChild(el('span', 'ma-chip__tag', 'Member')); }
                else if (p.source === 'user') { li.appendChild(el('span', 'ma-chip__tag', 'Team')); }
                else if (p.source === 'request') { li.appendChild(el('span', 'ma-chip__tag', 'Requester')); }
                var rb = removeButton(p.name || p.email, function () {
                    var idx = $$('.ma-chip__x', chips).indexOf(rb);
                    persons.delete(k);
                    render();
                    say2((p.name || p.email) + ' removed.');
                    var left = $$('.ma-chip__x', chips);
                    var target = left.length ? left[Math.min(Math.max(idx, 0), left.length - 1)] : null;
                    if (target) { target.focus(); } else if (textEl) { textEl.focus(); }
                });
                li.appendChild(rb);
                chips.appendChild(li);
                if (focusKey === k) { li.classList.add('is-new'); }
            });
            // hidden fields that are posted
            fields.textContent = '';
            var list = [];
            persons.forEach(function (p) { list.push({ source: p.source, member_id: p.member_id, user_id: p.user_id, name: p.name, email: p.email }); });
            var items = el('input');
            items.type = 'hidden';
            items.name = 'items';
            items.value = JSON.stringify(list);
            fields.appendChild(items);
            groups.forEach(function (g) {
                var b = el('input');
                b.type = 'hidden';
                b.name = 'bulk[]';
                b.value = g.spec;
                fields.appendChild(b);
            });
            var n = total();
            countEl.textContent = String(n);
            countLabel.textContent = plural(n, 'person', 'people');
            empty.hidden = n > 0;
            root.dispatchEvent(new CustomEvent('invitees:change', { bubbles: true, detail: { total: n } }));
            refreshButtons();
        }
        function removeButton(name, fn) {
            var b = el('button', 'ma-chip__x');
            b.type = 'button';
            b.setAttribute('aria-label', 'Remove ' + name);
            b.title = 'Remove';
            if (tpl && tpl.content.firstElementChild) { b.appendChild(tpl.content.firstElementChild.cloneNode(true)); } else { b.textContent = '×'; }
            b.addEventListener('click', fn);
            return b;
        }
        function refreshButtons() {
            $$('[data-inv-pick]', root).forEach(function (b) {
                var already = has(b.getAttribute('data-inv-pick'));
                b.disabled = already;
                b.textContent = already ? 'Added' : 'Add';
            });
        }

        // ---- progressive enhancement: tabs and js-only bits appear
        $$('.ma-js-only', root).forEach(function (n) { n.hidden = false; });
        $$('.ma-nojs', root).forEach(function (n) { n.hidden = true; });
        if (tablist) { tablist.hidden = false; }
        var tabs = $$('[data-inv-tab]', root);
        // viaKeyboard = the arrow keys moved between the tabs: the focus stays on the tab; a click moves it into the panel
        function openTab(name, viaKeyboard) {
            tabs.forEach(function (t) {
                var on = t.getAttribute('data-inv-tab') === name;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                if (on && viaKeyboard) { t.focus(); }
            });
            $$('[data-inv-panel]', root).forEach(function (p) { p.hidden = p.getAttribute('data-inv-panel') !== name; });
            if (name === 'members') { loadMemberTypes(); var s = $('[data-inv-msearch]', root); if (s && !viaKeyboard) { s.focus(); } }
            if (name === 'staff') { loadStaff(); }
            if (name === 'previous') { var p = $('[data-inv-psearch]', root); if (p && !viaKeyboard) { p.focus(); } }
            if (name === 'paste' && !viaKeyboard && textEl) { textEl.focus(); }
        }
        tabs.forEach(function (t, i) {
            t.addEventListener('click', function () { openTab(t.getAttribute('data-inv-tab'), false); });
            t.addEventListener('keydown', function (ev) {
                var to = -1;
                if (ev.key === 'ArrowRight') { to = (i + 1) % tabs.length; }
                else if (ev.key === 'ArrowLeft') { to = (i - 1 + tabs.length) % tabs.length; }
                else if (ev.key === 'Home') { to = 0; }
                else if (ev.key === 'End') { to = tabs.length - 1; }
                if (to >= 0) { ev.preventDefault(); openTab(tabs[to].getAttribute('data-inv-tab'), true); }
            });
        });
        // the panels other than "paste" start hidden in the markup

        // ---- type or paste
        function showBad(list) {
            badEl.textContent = '';
            list.forEach(function (b) {
                var li = el('li');
                li.appendChild(el('code', null, String(b.input).slice(0, 80)));
                li.appendChild(document.createTextNode(' - ' + b.reason));
                badEl.appendChild(li);
            });
            badEl.hidden = list.length === 0;
        }
        var parsing = false;
        function addFromText() {
            if (!textEl || parsing) { return Promise.resolve(); }
            var text = textEl.value.trim();
            if (text === '') {
                say2('Type or paste at least one e-mail address first.');
                textEl.focus();
                return Promise.resolve();
            }
            parsing = true;
            var exclude = [];
            persons.forEach(function (p, k) { exclude.push(k); });
            existing.forEach(function (x) { exclude.push(key(x.email)); });
            return api('POST', apiBase + '/parse', { text: text, exclude: exclude }).then(function (r) {
                parsing = false;
                if (!r.ok) { say2(errText(r, 'The list could not be read. Try again.')); return; }
                var added = 0;
                (r.data.valid || []).forEach(function (v) { if (addPerson({ name: v.name, email: v.email, source: 'manual' })) { added++; } });
                var invalid = r.data.invalid || [];
                showBad(invalid);
                textEl.value = invalid.map(function (b) { return b.raw || b.input; }).join('\n');
                var bits = [];
                bits.push(added === 0 ? 'Nobody new was added' : 'Added ' + people(added));
                if (r.data.duplicates) { bits.push(r.data.duplicates + ' repeated'); }
                if (r.data.skipped) { bits.push(r.data.skipped + ' already on the list'); }
                if (invalid.length) { bits.push(invalid.length + ' could not be used (see below)'); }
                if (r.data.truncated) { bits.push('only the first 1000 were read'); }
                say2(bits.join('; ') + '.');
                render();
            });
        }
        var addBtn = $('[data-inv-add]', root);
        if (addBtn) { addBtn.addEventListener('click', addFromText); }
        if (textEl) {
            textEl.addEventListener('keydown', function (ev) {
                if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) { ev.preventDefault(); addFromText(); }
            });
            textEl.addEventListener('paste', function () {
                window.setTimeout(function () { if (/@/.test(textEl.value) && /[\s,;]/.test(textEl.value.trim())) { addFromText(); } }, 20);
            });
        }

        // ---- pickers
        function debounce(fn, ms) {
            var t = 0;
            return function () { var a = arguments; window.clearTimeout(t); t = window.setTimeout(function () { fn.apply(null, a); }, ms); };
        }
        function resultRow(p, meta, emailForPick) {
            var li = el('li', 'ma-result');
            var who = el('span', 'ma-result__who');
            who.appendChild(el('strong', 'ma-result__name', p.name || p.email));
            if (meta) { who.appendChild(el('span', 'ma-result__meta', meta)); }
            who.appendChild(el('span', 'ma-result__mail', p.email));
            li.appendChild(who);
            var b = el('button', 'btn btn--outline btn--sm', 'Add');
            b.type = 'button';
            b.setAttribute('data-inv-pick', emailForPick);
            b.setAttribute('aria-label', 'Add ' + (p.name || p.email));
            b.addEventListener('click', function () {
                if (addPerson(p)) { render(key(p.email)); say2((p.name || p.email) + ' added.'); }
            });
            li.appendChild(b);
            return li;
        }
        function fillResults(ul, items, build, emptyText) {
            ul.textContent = '';
            if (!items.length) { var li = el('li', 'ma-result ma-result--empty', emptyText); ul.appendChild(li); return; }
            items.forEach(function (it) { ul.appendChild(build(it)); });
            refreshButtons();
        }
        // Enter inside a search box must not submit the whole schedule form: it adds the first result instead
        function enterPicks(input, list) {
            input.addEventListener('keydown', function (ev) {
                if (ev.key !== 'Enter') { return; }
                ev.preventDefault();
                var first = $('[data-inv-pick]:not([disabled])', list);
                if (first) { first.click(); }
            });
        }
        var mSearch = $('[data-inv-msearch]', root);
        var mResults = $('[data-inv-mresults]', root);
        var mSeq = 0;
        if (mSearch) { enterPicks(mSearch, mResults); }
        if (mSearch) {
            mSearch.addEventListener('input', debounce(function () {
                var q = mSearch.value.trim();
                var seq = ++mSeq;
                if (q.length < 2) { mResults.textContent = ''; return; }
                api('GET', apiBase + '/pickers/members?q=' + encodeURIComponent(q)).then(function (r) {
                    if (seq !== mSeq) { return; }
                    if (!r.ok) { fillResults(mResults, [], null, errText(r)); return; }
                    fillResults(mResults, r.data.items || [], function (m) {
                        var meta = [m.number, m.type].filter(Boolean).join(' - ');
                        return resultRow({ name: m.name, email: m.email, source: 'member', member_id: m.id }, meta, m.email);
                    }, 'No approved member matches "' + q + '".');
                });
            }, 250));
        }
        var typeSel = $('[data-inv-mtype]', root);
        var typeBtn = $('[data-inv-bulk-type]', root);
        function loadMemberTypes() {
            if (membersLoaded || !typeSel) { return; }
            membersLoaded = true;
            api('GET', apiBase + '/pickers/member-types').then(function (r) {
                if (!r.ok) { membersLoaded = false; return; }
                (r.data.items || []).forEach(function (t) {
                    var o = el('option', null, t.name + ' (' + t.count + ')');
                    o.value = String(t.id);
                    o.disabled = t.count === 0;
                    typeSel.appendChild(o);
                });
                var all = $('[data-inv-bulk-all]', root);
                if (all && r.data.total !== undefined) { all.textContent = 'Add all approved members (' + r.data.total + ')'; }
            });
        }
        if (typeSel) { typeSel.addEventListener('change', function () { typeBtn.disabled = typeSel.value === ''; }); }
        function addGroup(typeId, labelBase) {
            var spec = typeId ? 'members_type:' + typeId : 'members_all';
            if (groups.some(function (g) { return g.spec === spec; })) { say2('That group is already on the list.'); return; }
            api('GET', apiBase + '/pickers/members-bulk' + (typeId ? '?type=' + encodeURIComponent(typeId) : '')).then(function (r) {
                if (!r.ok) { say2(errText(r)); return; }
                var d = r.data;
                if (d.count === 0) { say2('There are no approved members with an e-mail address in this group yet.'); return; }
                if (d.needs_admin || d.allowed === false) {
                    say2('That would add ' + people(d.count) + '. Adding more than ' + threshold + ' people at once needs an administrator.');
                    return;
                }
                if (total() + d.count > maxPeople) { say2('That would make ' + (total() + d.count) + ' people. A meeting can have at most ' + maxPeople + '.'); return; }
                var label = labelBase;
                confirmDialog({
                    title: 'Add ' + people(d.count) + '?',
                    text: label + ' will be invited. Members\' e-mail addresses are used only to send this invitation. You can remove the whole group from the list again before you save.',
                    warn: d.capped ? 'There are ' + d.total + ' in this group. Only the first ' + d.max + ' are added.' : '',
                    ok: 'Add ' + people(d.count), cancel: 'Not now'
                }).then(function (yes) {
                    if (!yes) { return; }
                    groups.push({ spec: spec, label: label, count: d.count });
                    render();
                    say2(label + ' added (' + people(d.count) + ').');
                });
            });
        }
        var allBtn = $('[data-inv-bulk-all]', root);
        if (allBtn) { allBtn.addEventListener('click', function () { addGroup('', 'All approved members'); }); }
        if (typeBtn) {
            typeBtn.addEventListener('click', function () {
                if (typeSel.value === '') { return; }
                var name = typeSel.options[typeSel.selectedIndex].textContent.replace(/\s*\(\d+\)$/, '');
                addGroup(typeSel.value, 'Members: ' + name);
            });
        }
        var staffList = $('[data-inv-staff]', root);
        function loadStaff() {
            if (staffLoaded || !staffList) { return; }
            staffLoaded = true;
            api('GET', apiBase + '/pickers/staff').then(function (r) {
                if (!r.ok) { staffLoaded = false; fillResults(staffList, [], null, errText(r)); return; }
                fillResults(staffList, r.data.items || [], function (s) {
                    return resultRow({ name: s.name, email: s.email, source: 'user', user_id: s.id }, s.role === 'admin' ? 'Administrator' : 'Editor', s.email);
                }, 'No team members found.');
            });
        }
        var pSearch = $('[data-inv-psearch]', root);
        var pResults = $('[data-inv-presults]', root);
        var pSeq = 0;
        if (pSearch) { enterPicks(pSearch, pResults); }
        if (pSearch) {
            pSearch.addEventListener('input', debounce(function () {
                var q = pSearch.value.trim();
                var seq = ++pSeq;
                if (q.length < 2) { pResults.textContent = ''; return; }
                api('GET', apiBase + '/pickers/previous?q=' + encodeURIComponent(q)).then(function (r) {
                    if (seq !== pSeq) { return; }
                    if (!r.ok) { fillResults(pResults, [], null, errText(r)); return; }
                    fillResults(pResults, r.data.items || [], function (p) {
                        return resultRow({ name: p.name, email: p.email, source: 'previous' }, '', p.email);
                    }, 'Nobody from earlier meetings matches "' + q + '".');
                });
            }, 250));
        }

        // ---- start
        parseData('data-people').forEach(function (p) { addPerson(p); });
        parseData('data-groups').forEach(function (g) { if (g && g.spec) { groups.push({ spec: String(g.spec), label: String(g.label || 'Members'), count: parseInt(g.count || 0, 10) }); } });
        render();

        root.maInvitees = {
            /** people who will be on the list after saving: chips + groups (an upper bound when groups exist) + people already there */
            total: total,
            hasGroups: function () { return groups.length > 0; },
            newPeople: function () { var n = persons.size; groups.forEach(function (g) { n += g.count; }); return n; },
            existing: function () { return existing.slice(); },
            textPending: function () { return textEl ? textEl.value.trim() !== '' : false; },
            addFromText: addFromText,
            // the box that needs fixing lives in the "Type or paste" panel: show that tab first (a hidden textarea cannot take the focus)
            focus: function () { if (tabs.length) { openTab('paste', false); } else if (textEl) { textEl.focus(); } }
        };
    }

    // ------------------------------------------------------------------ the schedule form
    function initForm(form) {
        var plat = {};
        try { plat = JSON.parse(form.getAttribute('data-platforms') || '{}'); } catch (e) { plat = {}; }
        var apiBase = (form.getAttribute('data-api') || '').replace(/\/$/, '');
        var mode = form.getAttribute('data-mode') || 'create';
        var radios = $$('[data-ma-platform]', form);
        var link = $('[data-ma-link]', form);
        var help = $('[data-ma-help]', form);
        var steps = $('[data-ma-steps]', form);
        var jitsiBtn = $('[data-ma-jitsi]', form);
        var jitsiNote = $('[data-ma-jitsi-note]', form);
        var fieldBox = {
            link: $('[data-ma-field="link"]', form), passcode: $('[data-ma-field="passcode"]', form),
            dial: $('[data-ma-field="dial"]', form), place: $('[data-ma-field="place"]', form)
        };
        var dialLabel = $('[data-ma-dial-label]', form);
        var dirty = false;
        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('change', function () { dirty = true; });

        function currentPlatform() {
            var r = radios.filter(function (x) { return x.checked; })[0];
            return r ? r.value : 'other';
        }
        function setFieldMode(name, modeName) {
            var box = fieldBox[name];
            if (!box) { return; }
            var show = modeName !== 'hide';
            var inputs = $$('input', box);
            inputs.forEach(function (inp) {
                if (!show) {
                    if (inp.value !== '') { inp.setAttribute('data-stash', inp.value); }
                    inp.value = '';
                    inp.disabled = true;
                } else {
                    inp.disabled = false;
                    if (inp.value === '' && inp.getAttribute('data-stash')) { inp.value = inp.getAttribute('data-stash'); }
                }
            });
            box.hidden = !show;
            var req = $('[data-ma-req]', box);
            if (req) { req.hidden = modeName !== 'required'; }
            var opt = $('[data-ma-opt]', box);
            if (opt) { opt.hidden = modeName === 'required'; }
        }
        function applyPlatform() {
            var key = currentPlatform();
            var p = plat[key];
            if (!p) { return; }
            link.placeholder = p.placeholder || '';
            help.textContent = p.help || '';
            steps.textContent = '';
            (p.steps || []).forEach(function (s) { steps.appendChild(el('li', null, s)); });
            ['link', 'passcode', 'dial', 'place'].forEach(function (n) { setFieldMode(n, p[n]); });
            if (dialLabel) {
                dialLabel.textContent = '';
                dialLabel.appendChild(document.createTextNode(key === 'phone' ? 'Phone number people must call ' : (key === 'whatsapp' ? 'WhatsApp number ' : 'Dial-in phone number ')));
                dialLabel.appendChild(el('span', 'field__opt', p.dial === 'required' ? '(needed before you send)' : '(optional)'));
            }
            if (jitsiBtn) {
                var online = ['zoom', 'google_meet', 'teams', 'jitsi', 'other'].indexOf(key) >= 0;
                jitsiBtn.hidden = !online;
                jitsiBtn.lastChild.textContent = key === 'jitsi' ? ' Generate a free private video room' : ' No link yet? Generate a free private video room';
            }
            if (jitsiNote && key !== 'jitsi') { jitsiNote.hidden = true; }
            form.dispatchEvent(new CustomEvent('platform:change', { detail: { platform: key } }));
        }
        radios.forEach(function (r) { r.addEventListener('change', applyPlatform); });
        if (jitsiBtn) {
            // the icon svg is the first child; the text node is the last child
            if (jitsiBtn.lastChild.nodeType !== 3) { jitsiBtn.appendChild(document.createTextNode(' Generate a free private video room')); }
            jitsiBtn.addEventListener('click', function () {
                jitsiBtn.disabled = true;
                api('POST', apiBase + '/jitsi', {}).then(function (r) {
                    jitsiBtn.disabled = false;
                    if (!r.ok || !r.data.url) { jitsiNote.textContent = errText(r, 'A video room could not be made. Try again.'); jitsiNote.hidden = false; return; }
                    var jr = radios.filter(function (x) { return x.value === 'jitsi'; })[0];
                    if (jr && !jr.checked) { jr.checked = true; applyPlatform(); }
                    link.value = r.data.url;
                    link.dispatchEvent(new Event('input', { bubbles: true }));
                    jitsiNote.textContent = 'Done. The link is in the box above (' + (r.data.service || 'Jitsi Meet') + '). ' + (r.data.notice || '');
                    jitsiNote.hidden = false;
                    link.focus();
                });
            });
        }

        // ---- date, time, length
        var dateIn = $('[data-ma-date]', form);
        var startIn = $('[data-ma-start]', form);
        var endIn = $('[data-ma-end]', form);
        var endWrap = $('[data-ma-endwrap]', form);
        var durs = $$('[data-ma-dur]', form);
        var when = $('[data-ma-when]', form);
        function minutes(s) { var m = /^(\d{1,2}):(\d{2})/.exec(s || ''); return m ? parseInt(m[1], 10) * 60 + parseInt(m[2], 10) : null; }
        function hm(mins) { return pad(Math.floor(mins / 60) % 24) + ':' + pad(mins % 60); }
        function dateLabel(iso) {
            var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
            if (!m) { return ''; }
            var d = new Date(Date.UTC(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10)));
            return DAYS[d.getUTCDay()] + ' ' + parseInt(m[3], 10) + ' ' + MONTHS[parseInt(m[2], 10) - 1] + ' ' + m[1];
        }
        function durLabel(n) {
            var h = Math.floor(n / 60), m = n % 60, out = [];
            if (h) { out.push(h + (h === 1 ? ' hour' : ' hours')); }
            if (m || !h) { out.push(m + (m === 1 ? ' minute' : ' minutes')); }
            return out.join(' ');
        }
        function durationChoice() {
            var c = durs.filter(function (x) { return x.checked; })[0];
            return c ? c.value : '60';
        }
        function updateWhen() {
            var custom = durationChoice() === 'custom';
            if (endIn) {
                endIn.disabled = !custom;
                if (!custom) { endIn.value = ''; }
                endWrap.hidden = !custom;
            }
            var start = minutes(startIn.value);
            var d = dateLabel(dateIn.value);
            var end = null;
            if (start !== null) {
                end = custom ? minutes(endIn.value) : start + parseInt(durationChoice(), 10);
            }
            when.classList.remove('is-bad');
            if (!d || start === null) { when.textContent = 'Choose the date and the start time to see the meeting time here.'; return; }
            if (end === null) { when.textContent = d + ', from ' + hm(start) + ' EAT. Choose the end time.'; return; }
            if (end <= start) { when.textContent = 'The end time must be after the start time.'; when.classList.add('is-bad'); return; }
            if (end >= 24 * 60) { when.textContent = 'A meeting must end on the day it starts (before midnight).'; when.classList.add('is-bad'); return; }
            when.textContent = d + ', ' + hm(start) + '–' + hm(end) + ' EAT (' + durLabel(end - start) + ').';
        }
        [dateIn, startIn, endIn].forEach(function (n) { if (n) { n.addEventListener('input', updateWhen); n.addEventListener('change', updateWhen); } });
        durs.forEach(function (r) {
            r.addEventListener('change', function () {
                updateWhen();
                if (durationChoice() === 'custom' && endIn) {
                    if (!endIn.value) {
                        var s = minutes(startIn.value);
                        if (s !== null && s + 60 < 24 * 60) { endIn.value = hm(s + 60); updateWhen(); }
                    }
                    endIn.focus();
                }
            });
        });

        // ---- visibility
        var pub = $('[data-ma-public]', form);
        $$('[data-ma-vis]', form).forEach(function (r) {
            r.addEventListener('change', function () { pub.hidden = !$('[data-ma-vis][value="public"]', form).checked; });
        });

        // ---- the box of invitees
        var inv = $('[data-invitees]', form);
        if (inv) { initInvitees(inv); }

        // ---- send
        function sendFacts() {
            var key = currentPlatform();
            var where = link.value.trim();
            if (where === '') { where = ($('#f-dial_in', form).value.trim()) || ($('#f-location', form).value.trim()) || 'No link or address yet'; }
            return [
                ['Meeting', $('#f-title', form).value.trim()],
                ['When', when.textContent.replace(/\.$/, '')],
                ['How', (plat[key] ? plat[key].label : '') + ' - ' + where],
                ['Organiser', $('#f-organiser_name', form).value.trim() + ' <' + $('#f-organiser_email', form).value.trim() + '>']
            ];
        }
        function missingForSend() {
            var key = currentPlatform();
            var p = plat[key] || {};
            if (p.link === 'required' && link.value.trim() === '') { return { el: link, msg: 'Add the meeting link before you send the invitations (or press "Generate a free private video room").' }; }
            if (p.dial === 'required' && $('#f-dial_in', form).value.trim() === '') { return { el: $('#f-dial_in', form), msg: 'Enter the phone number people must call before you send.' }; }
            if (p.place === 'required' && $('#f-location', form).value.trim() === '') { return { el: $('#f-location', form), msg: 'Enter the address before you send the invitations.' }; }
            if (key === 'whatsapp' && link.value.trim() === '' && $('#f-dial_in', form).value.trim() === '') { return { el: link, msg: 'Add the WhatsApp link or number before you send.' }; }
            if (key === 'other' && link.value.trim() === '' && $('#f-dial_in', form).value.trim() === '' && $('#f-location', form).value.trim() === '') { return { el: link, msg: 'Add a link, a phone number or an address so people know how to join.' }; }
            return null;
        }
        var statusEl = $('[data-ma-test-status]', form);
        function formSay(msg) { if (statusEl) { statusEl.textContent = ''; window.setTimeout(function () { statusEl.textContent = msg; }, 30); } }
        form.addEventListener('click', function (ev) {
            var btn = ev.target instanceof Element ? ev.target.closest('[data-ma-send]') : null;
            if (!btn) { return; }
            ev.preventDefault();
            if (statusEl) { statusEl.textContent = ''; }                // an older message (an error fixed since) must not stay on screen
            if (!form.reportValidity()) { return; }
            if (inv && inv.maInvitees.textPending()) {
                // pasted text that was not turned into chips yet: do it now so the number in the dialog is right
                inv.maInvitees.addFromText().then(function () {
                    if (inv.maInvitees.textPending()) { inv.maInvitees.focus(); formSay('Some of the addresses you typed are not valid. Fix them or clear the box, then press the button again.'); return; }
                    btn.click();
                });
                return;
            }
            var miss = missingForSend();
            if (miss) { formSay(miss.msg); say(miss.msg); miss.el.focus(); return; }
            var kind = btn.getAttribute('data-ma-send');
            var tot = inv ? inv.maInvitees.total() : 0;
            var existing = inv ? inv.maInvitees.existing() : [];
            var invitable = tot - existing.filter(function (x) { return x.state === 'Declined'; }).length;
            if (invitable <= 0) {
                var m = 'Add at least one person to invite first.';
                formSay(m); say(m);
                if (inv) { inv.maInvitees.focus(); }
                return;
            }
            var upper = inv.maInvitees.hasGroups() || kind === 'update';
            var count = invitable;
            var o = {
                facts: sendFacts(), ok: 'Send to ' + (upper ? 'up to ' : '') + people(count), cancel: 'Not yet',
                warn: inv.maInvitees.hasGroups() ? 'Members who appear twice are only e-mailed once, so the real number may be a little lower.' : ''
            };
            if (kind === 'update') {
                o.title = 'Save and send the update to ' + (upper ? 'up to ' : '') + people(count) + '?';
                o.text = 'Your changes are saved first. Everyone who has not declined gets the new details and their calendar updates. People who were not invited yet get their first invitation. Each person gets their own e-mail.';
            } else {
                o.title = 'Send the invitation to ' + (upper ? 'up to ' : '') + people(count) + '?';
                o.text = 'The meeting is saved and scheduled, and every person gets their own e-mail with the details, a calendar file and answer buttons. Nobody sees who else is invited.';
            }
            confirmDialog(o).then(function (yes) {
                if (!yes) { return; }
                $('[data-ma-confirm-count]', form).value = String(count);
                dirty = false;
                if (typeof form.requestSubmit === 'function') { form.requestSubmit(btn); }
                else {
                    var h = el('input'); h.type = 'hidden'; h.name = 'intent'; h.value = 'send'; form.appendChild(h);
                    form.submit();
                }
            });
        });

        // ---- test invitation (edit mode: the meeting is saved)
        var testBtn = $('[data-ma-test]', form);
        if (testBtn) {
            testBtn.addEventListener('click', function () {
                if (statusEl) { statusEl.textContent = ''; }
                sendTest(testBtn, form.getAttribute('data-mail-api'), form.getAttribute('data-user-email'), formSay, dirty);
            });
        }

        applyPlatform();
        updateWhen();
        // a form that came back with errors: scroll to the summary
        var errs = $('#form-errors', form);
        if (errs) { errs.setAttribute('tabindex', '-1'); window.setTimeout(function () { errs.focus({ preventScroll: false }); }, 50); }
    }

    function sendTest(btn, mailApi, email, sayFn, dirty) {
        if (!mailApi) { return; }
        // aria-busy + a guard instead of `disabled`: disabling the focused button would throw keyboard focus to the top of the page
        if (btn.getAttribute('aria-busy') === 'true') { return; }
        btn.setAttribute('aria-busy', 'true');
        sayFn('Sending a test to ' + (email || 'you') + '...');
        api('POST', mailApi.replace(/\/$/, '') + '/test', { kind: 'invite' }).then(function (r) {
            btn.removeAttribute('aria-busy');
            var res = r.ok && r.data && Array.isArray(r.data.results) ? r.data.results[0] : null;
            if (res && res.ok) {
                sayFn('A test invitation was sent to ' + (email || 'your address') + '.' + (dirty ? ' It shows the last saved version, not the changes you just made.' : ''));
            } else if (res) {
                sayFn('The test could not be sent: ' + (res.error || 'the mail server refused it') + '.');
            } else {
                sayFn(errText(r, 'The test could not be sent.'));
            }
        });
    }

    // ------------------------------------------------------------------ the meeting page
    function initPage(page) {
        // ---- people: filter
        var table = $('#ma-people-table', page);
        var rows = table ? $$('tbody tr[data-ma-row]', table) : [];
        var filterIn = $('#ma-people-filter', page);
        var pills = $$('[data-ma-filter]', page);
        var statusNote = $('[data-ma-filter-status]', page);
        var pill = 'all';
        var LIMIT = 25;                                 // a long list starts with its first rows only (64 people are ~17,000 px of cards on a phone)
        var showAll = false;
        var moreBox = null;
        var moreBtn = null;
        if (table && rows.length > LIMIT + 5) {
            moreBox = el('p', 'ma-showall');
            moreBtn = el('button', 'btn btn--outline');
            moreBtn.type = 'button';
            moreBtn.addEventListener('click', function () {
                showAll = true;
                applyFilter();
                var first = rows[LIMIT];
                var f = first ? first.querySelector('button, a') : null;
                if (f) { f.focus(); }
            });
            moreBox.appendChild(moreBtn);
            table.closest('.table-wrap').insertAdjacentElement('afterend', moreBox);
        }
        function applyFilter() {
            var q = lower(filterIn ? filterIn.value : '');
            var filtering = q !== '' || pill !== 'all';
            var matched = 0;
            var shown = 0;
            rows.forEach(function (r) {
                var okPill = pill === 'all' || r.getAttribute('data-rsvp') === pill;
                if (pill === 'failed') { okPill = r.getAttribute('data-invite') === 'failed'; }
                var okText = q === '' || lower(r.textContent).indexOf(q) >= 0;
                var ok = okPill && okText;
                if (ok) { matched++; }
                r.hidden = !ok || (!filtering && !showAll && moreBtn !== null && matched > LIMIT);
                if (!r.hidden) { shown++; }
            });
            var more = moreBtn !== null && !filtering && !showAll && matched > LIMIT;
            if (moreBox) {
                moreBox.hidden = !more;
                moreBtn.textContent = 'Show all ' + matched + ' people';
            }
            if (statusNote) {
                if (rows.length === 0) { statusNote.textContent = ''; }
                else if (matched === 0) { statusNote.textContent = 'Nobody matches that.'; }
                else if (filtering) { statusNote.textContent = people(matched) + ' match' + (matched === 1 ? 'es' : '') + '.'; }
                else if (more) { statusNote.textContent = 'Showing the first ' + shown + ' of ' + people(matched) + '.'; }
                else { statusNote.textContent = ''; }
            }
        }
        if (filterIn) { filterIn.addEventListener('input', applyFilter); }
        pills.forEach(function (b) {
            b.addEventListener('click', function () {
                pill = b.getAttribute('data-ma-filter');
                pills.forEach(function (x) { var on = x === b; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
                applyFilter();
            });
        });
        applyFilter();

        // ---- remove a person
        var rd = document.getElementById('ma-remove');
        document.addEventListener('click', function (ev) {
            var b = ev.target instanceof Element ? ev.target.closest('[data-ma-remove]') : null;
            if (!b || !rd) { return; }
            ev.preventDefault();
            var f = $('[data-ma-remove-form]', rd);
            f.action = b.getAttribute('data-action');
            $('[data-ma-fill="person"]', rd).textContent = b.getAttribute('data-person') || 'this person';
            var invited = b.getAttribute('data-invited') === '1';
            var wrap = $('[data-ma-remove-notify]', rd);
            wrap.hidden = !invited;
            $('input[type="checkbox"]', wrap).checked = invited;
            if (typeof rd.showModal === 'function') { rd.showModal(); }
        });

        // The sending buttons of this page ([data-ma-queue], [data-ma-test], form[data-ma-message], [data-ma-reminder]) belong to the sending panel,
        // assets/js/admin-meetings-send.js (MAIL agent): confirm dialog with the exact count, queue, batch loop, test, message in chunks of 30, reminders.
    }

    // ------------------------------------------------------------------ request inbox: the decline dialog
    function initRequest() {
        var cb = $('[data-ma-notify]');
        var note = $('[data-ma-notify-note]');
        if (cb && note) {
            var apply = function () { note.hidden = !cb.checked; };
            cb.addEventListener('change', apply);
            apply();
        }
    }

    // ------------------------------------------------------------------ boot
    var f = $('[data-meeting-form]');
    if (f) { initForm(f); }
    var pg = $('[data-meeting-page]');
    if (pg) {
        initPage(pg);
        $$('[data-invitees]', pg).forEach(initInvitees);
    }
    initRequest();
    if (reduced) { document.documentElement.classList.add('reduce-motion'); }
})();
