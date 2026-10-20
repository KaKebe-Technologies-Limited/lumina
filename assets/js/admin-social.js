/*
 * Admin > Social media (docs/SOCIAL.md). OWNER: ADMIN agent. Progressive enhancement only: every admin screen works without this script.
 *   1. Profile links (Admin > Social media): live address check, arrows / drag order, live footer preview.
 *   2. Auto-share channels: write-only secret boxes ("Set (hidden)" + Replace), fields that depend on a choice (X: OAuth 1.0a or bearer), TEST CONNECTION and SEND A TEST POST
 *      (the second asks first) posted in the background, the answer shown under the buttons.
 *   3. Post templates: placeholder buttons, live counter and live preview built by the server (the same builder that writes the real captions).
 *   4. The "Share on social media" panel of the newsletter editor ([data-social-panel], built from GET /admin/api/newsletters/{id}/share): per-channel tick, editable caption with
 *      the live counter, the picture, per-channel status ("Posted - view post", "Failed - retry", "Queued"), Share now / Post again / Retry. Choices are saved with the issue as
 *      they change (PUT, debounced), also for a scheduled issue. It listens to two events of assets/js/admin-newsletter.js:
 *        lumina:newsletter-item            {id, status, is_live, item, social}   the issue changed (saved, published, scheduled ...): the panel refreshes and follows the posts
 *        lumina:newsletter-publish-dialog  {id, body}                            the publish dialog is about to open: a summary of where the issue will be posted is appended
 * No inline JavaScript anywhere (CSP script-src 'self'). Secrets are never read or written by this script: the secret inputs are plain password fields the browser posts as they are.
 * Everything the server sends is put on the page with textContent (captions are user text), never as HTML.
 */
(function () {
    'use strict';

    function csrf() {
        if (window.LuminaAdmin && typeof window.LuminaAdmin.csrf === 'function') {
            return window.LuminaAdmin.csrf();
        }
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function h(tag, attrs, kids) {
        var n = document.createElement(tag);
        Object.keys(attrs || {}).forEach(function (k) {
            if (k === 'text') { n.textContent = attrs[k]; } else if (attrs[k] !== null && attrs[k] !== undefined && attrs[k] !== false) { n.setAttribute(k, attrs[k] === true ? '' : attrs[k]); }
        });
        (kids || []).forEach(function (c) { if (c) { n.appendChild(typeof c === 'string' ? document.createTextNode(c) : c); } });
        return n;
    }

    function api(method, url, data) {
        var opts = {method: method, credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf()}};
        if (data !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(data);
        }
        return fetch(url, opts).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) { return {status: r.status, data: j}; });
        });
    }

    function debounce(fn, ms) {
        var t = null;
        return function () {
            var args = arguments, self = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(self, args); }, ms);
        };
    }

    // ================================================================================================ 1. profile links
    function initProfiles(form) {
        var list = form.querySelector('[data-asoc-prof-list]');
        if (!list) { return; }
        var preview = form.querySelector('[data-asoc-preview]');
        var announceBox = form.querySelector('[data-asoc-announce]');
        var countBox = form.querySelector('[data-asoc-prof-count]');
        form.classList.add('asoc-prof--js');
        form.querySelectorAll('[hidden][data-grip], [data-arrows], .asoc-prof__js-tip').forEach(function (e) { e.hidden = false; });
        // with JavaScript the number boxes are visually hidden: they must not be keyboard stops with no visible focus (the labelled Move up / Move down buttons do the job)
        form.querySelectorAll('[data-asoc-pos]').forEach(function (i) { i.tabIndex = -1; i.setAttribute('aria-hidden', 'true'); });

        function rows() { return Array.prototype.slice.call(list.children); }

        function say(text) { if (announceBox) { announceBox.textContent = ''; setTimeout(function () { announceBox.textContent = text; }, 30); } }

        function hostMatches(host, hosts) {
            return hosts.some(function (x) { return host === x || host.slice(-(x.length + 1)) === '.' + x; });
        }

        // the same rules as SocialProfiles::normalise (the server has the last word)
        function check(row) {
            var input = row.querySelector('[data-asoc-url]');
            var box = row.querySelector('[data-asoc-check]');
            var name = row.getAttribute('data-name');
            var hosts = (row.getAttribute('data-hosts') || '').split(',').filter(Boolean);
            var handle = row.getAttribute('data-handle') || '';
            var v = input.value.trim();
            var state = 'empty', msg = '';
            var isWa = row.getAttribute('data-key') === 'whatsapp';
            if (v === '') {
                msg = 'Empty: ' + name + ' will not be shown.';
            } else if (isWa && /^\+?[\d\s().\-]{7,30}$/.test(v) && /\d/.test(v)) {
                var digits = v.replace(/\D/g, '');
                if (digits.slice(0, 2) === '00') { digits = digits.slice(2); }
                if (digits === '' || digits.charAt(0) === '0') {
                    state = 'bad'; msg = 'Write the number with its country code and without a leading 0, for example 256700000000 (Uganda).';
                } else if (digits.length < 8 || digits.length > 15) {
                    state = 'bad'; msg = 'A phone number with its country code has 8 to 15 digits, for example 256700000000.';
                } else {
                    state = 'info'; msg = 'Will be saved as https://wa.me/' + digits + '.';
                }
            } else if (/[\s\u0000-\u001f]/.test(v) || v.length > 300) {
                state = 'bad'; msg = 'The address must be one line without spaces (at most 300 characters).';
            } else if (/["'<>`\\]/.test(v)) {
                state = 'bad'; msg = 'The address contains a character that cannot be in a web address (a quote, < or >, a backslash or a backtick).';
            } else if (/^[a-z][a-z0-9+.\-]*:/i.test(v)) {
                var u = null;
                try { u = new URL(v); } catch (e) { u = null; }
                if (!u || u.protocol !== 'https:') {
                    state = 'bad'; msg = 'The address must start with https://';
                } else if (u.username || u.password) {
                    state = 'bad'; msg = 'An address with a user name or password is not allowed.';
                } else if (hostMatches(u.hostname.toLowerCase(), hosts)) {
                    state = 'ok'; msg = 'Looks right.';
                } else {
                    state = 'warn'; msg = 'This is not on ' + hosts[0] + '. It will be accepted: check that it is right.';
                }
            } else if (handle !== '' && /^@?[A-Za-z0-9._]{2,60}$/.test(v)) {
                state = 'info'; msg = 'Will be saved as ' + handle.replace('%s', v.replace(/^@/, '')) + '.';
            } else if (/^(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)+(\/\S*)?$/i.test(v)) {
                state = 'info'; msg = 'https:// will be added in front.';
            } else {
                state = 'bad'; msg = 'Write the full address, for example ' + row.getAttribute('data-example') + '.';
            }
            box.textContent = msg;
            box.className = 'asoc-prof__check' + (state === 'empty' ? '' : ' asoc-prof__check--' + state);
            row.classList.toggle('is-bad', state === 'bad');
            row.setAttribute('data-state', state);
            return state;
        }

        function renumber() {
            var all = rows();
            all.forEach(function (row, i) {
                var pos = row.querySelector('[data-asoc-pos]');
                if (pos) { pos.value = String(i + 1); }
                var up = row.querySelector('[data-move="up"]'), down = row.querySelector('[data-move="down"]');
                if (up) { up.disabled = i === 0; }
                if (down) { down.disabled = i === all.length - 1; }
            });
        }

        function refresh() {
            var shown = 0;
            if (preview) { preview.textContent = ''; }
            rows().forEach(function (row) {
                var st = check(row);
                if (st !== 'empty' && st !== 'bad') {
                    shown++;
                    var ic = row.querySelector('.asoc-prof__icon svg');
                    if (preview && ic) {
                        var pv = h('span', {class: 'asoc-prof__pv', title: row.getAttribute('data-name')});
                        pv.appendChild(ic.cloneNode(true));
                        preview.appendChild(pv);
                    }
                }
            });
            if (preview && shown === 0) { preview.appendChild(h('span', {class: 'asoc-prof__empty', text: 'No account added yet.'})); }
            if (countBox) { countBox.textContent = shown + ' of ' + rows().length + ' added'; }
        }

        function move(row, dir) {
            var sib = dir === 'up' ? row.previousElementSibling : row.nextElementSibling;
            if (!sib) { return; }
            if (dir === 'up') { list.insertBefore(row, sib); } else { list.insertBefore(sib, row); }
            renumber(); refresh();
            row.classList.add('is-moved');
            setTimeout(function () { row.classList.remove('is-moved'); }, 1000);
            say(row.getAttribute('data-name') + ' is now number ' + (rows().indexOf(row) + 1) + ' of ' + rows().length + '.');
            var btn = row.querySelector('[data-move="' + dir + '"]');
            if (btn && !btn.disabled) { btn.focus(); } else { var other = row.querySelector('[data-move]:not([disabled])'); if (other) { other.focus(); } }
        }

        list.addEventListener('click', function (ev) {
            var b = ev.target.closest('[data-move]');
            if (b) { ev.preventDefault(); move(b.closest('.asoc-prof__row'), b.getAttribute('data-move')); }
        });
        list.addEventListener('input', function (ev) { if (ev.target.matches('[data-asoc-url]')) { check(ev.target.closest('.asoc-prof__row')); refresh(); } });
        // typing a position number (no-script fallback box is hidden but stays in sync)

        // drag with the handle (mouse / pen); touch uses the arrows
        var dragging = null;
        list.addEventListener('mousedown', function (ev) {
            var g = ev.target.closest('[data-grip]');
            if (g) { g.closest('.asoc-prof__row').setAttribute('draggable', 'true'); }
        });
        list.addEventListener('dragstart', function (ev) {
            var row = ev.target.closest('.asoc-prof__row');
            if (!row || row.getAttribute('draggable') !== 'true') { return; }
            dragging = row;
            row.classList.add('is-dragging');
            try { ev.dataTransfer.effectAllowed = 'move'; ev.dataTransfer.setData('text/plain', row.getAttribute('data-key')); } catch (e) { /* old browser */ }
        });
        list.addEventListener('dragover', function (ev) {
            if (!dragging) { return; }
            ev.preventDefault();
            var over = ev.target.closest('.asoc-prof__row');
            if (!over || over === dragging) { return; }
            var r = over.getBoundingClientRect();
            list.insertBefore(dragging, ev.clientY < r.top + r.height / 2 ? over : over.nextElementSibling);
        });
        list.addEventListener('drop', function (ev) { if (dragging) { ev.preventDefault(); } });
        list.addEventListener('dragend', function () {
            if (!dragging) { return; }
            dragging.classList.remove('is-dragging');
            dragging.removeAttribute('draggable');
            say(dragging.getAttribute('data-name') + ' moved to number ' + (rows().indexOf(dragging) + 1) + '.');
            dragging = null;
            renumber(); refresh();
        });

        // a position typed in the (visually hidden) number boxes by an assistive tool: re-sort when the form is submitted
        form.addEventListener('submit', renumber);
        renumber();
        refresh();
    }

    // ================================================================================================ 2. channels
    function resultBox(form) {
        var scope = form.closest('[data-asoc-tests]');
        return scope ? scope.querySelector('[data-asoc-result]') : null;
    }

    function announce(form, text, kind) {
        var box = resultBox(form);
        if (!box) { return; }
        box.hidden = false;
        box.className = 'asoc-result asoc-result--' + kind;
        box.textContent = text;
    }

    function updateHealth(form, j, label) {
        var card = form.closest('[data-asoc-card]');
        var line = card ? card.querySelector('[data-asoc-health]') : null;
        if (!line || !j || typeof j.message !== 'string') { return; }
        var state = j.ok ? 'ok' : (j.state === 'auth' ? 'needs_token' : 'error');
        line.className = 'asoc-health asoc-health--' + state;
        line.textContent = '';
        line.appendChild(document.createTextNode(label + ': '));
        line.appendChild(h('strong', {text: j.ok ? 'Works' : (state === 'needs_token' ? 'Needs a new token' : 'Problem')}));
        line.appendChild(document.createTextNode((j.at ? ' (' + j.at + ')' : '') + ': ' + j.message));
    }

    function wireTest(form, isSend) {
        form.addEventListener('submit', function (ev) {
            if (!window.fetch || ev.defaultPrevented) {
                return;                       // cancelled in the confirm dialog, or no fetch: the plain form post (flash + redirect) is the fallback
            }
            ev.preventDefault();
            var buttons = form.closest('[data-asoc-tests]').querySelectorAll('button[type="submit"]');
            buttons.forEach(function (b) { b.disabled = true; });
            announce(form, isSend ? 'Sending the test post...' : 'Testing the connection...', 'wait');
            fetch(form.action, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams(new FormData(form)).toString(),
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); }).then(function (j) {
                var text = (j && j.message) ? j.message : 'Done.';
                if (isSend && j && j.ok && j.remote_url) { text += ' (' + j.remote_url + ')'; }
                announce(form, text, j && j.ok ? 'ok' : 'bad');
                updateHealth(form, j, isSend ? 'Test post' : 'Last check');
            }).catch(function () {
                announce(form, 'The request could not be completed. Check your connection and try again.', 'bad');
            }).then(function () {
                buttons.forEach(function (b) { b.disabled = false; });
            });
        });
    }

    function initChannelForm(form) {
        // write-only secret boxes: "Set (hidden)" with Replace
        form.querySelectorAll('[data-asoc-secret][data-set="1"]').forEach(function (box) {
            var input = box.querySelector('[data-secret-input]');
            var btn = box.querySelector('[data-secret-replace]');
            var remove = box.querySelector('input[type="checkbox"]');
            if (!input || !btn) { return; }
            input.hidden = true;
            btn.hidden = false;
            btn.addEventListener('click', function () {
                if (input.hidden) {
                    input.hidden = false; input.focus(); btn.textContent = 'Keep the stored value';
                    if (remove) { remove.checked = false; }
                } else {
                    input.value = ''; input.hidden = true; btn.textContent = 'Replace';
                }
            });
            if (remove) {
                remove.addEventListener('change', function () { if (remove.checked) { input.value = ''; input.hidden = true; btn.textContent = 'Replace'; } });
            }
        });
        // fields that depend on another field (X: the OAuth 1.0a keys or the bearer token)
        var conditional = form.querySelectorAll('[data-when]');
        function applyWhen() {
            conditional.forEach(function (el) {
                var cond = {};
                try { cond = JSON.parse(el.getAttribute('data-when')) || {}; } catch (e) { cond = {}; }
                var ok = Object.keys(cond).every(function (name) {
                    var ctl = form.elements[name];
                    return !ctl || String(ctl.value) === String(cond[name]);
                });
                el.hidden = !ok;
            });
        }
        if (conditional.length) {
            form.addEventListener('change', applyWhen);
            applyWhen();
        }
    }

    document.querySelectorAll('form[data-asoc-profiles]').forEach(initProfiles);
    document.querySelectorAll('form[data-asoc-channel-form]').forEach(initChannelForm);
    document.querySelectorAll('form[data-asoc-test]').forEach(function (f) { wireTest(f, false); });
    document.querySelectorAll('form[data-asoc-sendtest]').forEach(function (f) { wireTest(f, true); });

    // ================================================================================================ 3. templates
    function initTemplates(form) {
        var url = form.getAttribute('data-preview-url');
        form.querySelectorAll('[data-asoc-tpl]').forEach(function (card) {
            var ch = card.getAttribute('data-asoc-tpl');
            var ta = card.querySelector('[data-asoc-template]');
            var sample = card.querySelector('[data-asoc-sample]');
            var meter = card.querySelector('[data-asoc-meter]');
            var fill = card.querySelector('[data-fill]');
            var txt = card.querySelector('[data-asoc-limit]');
            var cnt = card.querySelector('[data-asoc-sample-count]');
            var warn = card.querySelector('[data-asoc-warn]');
            var tools = card.querySelector('[data-asoc-tools]');
            var announcer = form.querySelector('[data-asoc-announce-tpl]');
            var wasOver = null;
            var tellTimer = null;
            if (!ta || !url) { return; }
            if (tools) { tools.hidden = false; }

            function paint(j) {
                if (!j || typeof j.length !== 'number') { return; }
                sample.textContent = j.text;
                var pct = j.limit > 0 ? Math.min(100, Math.round(j.length * 100 / j.limit)) : 0;
                if (fill) { fill.style.setProperty('--w', String(pct)); }
                if (txt) { txt.textContent = j.length + ' / ' + j.limit; }
                if (cnt) { cnt.textContent = j.length + ' of ' + j.limit; }
                if (meter) {
                    meter.classList.toggle('is-over', !!j.over);
                    meter.classList.toggle('is-soft', !j.over && j.soft > 0 && j.length > j.soft);
                }
                ta.setAttribute('aria-invalid', j.over ? 'true' : 'false');
                if (wasOver !== null && wasOver !== !!j.over && announcer) {            // one short message when the state changes, never one per keystroke
                    clearTimeout(tellTimer);
                    var msgText = j.over ? 'The sample for this platform is ' + (j.length - j.limit) + ' character' + (j.length - j.limit === 1 ? '' : 's') + ' over the limit.' : 'The sample for this platform is within the limit again.';
                    tellTimer = setTimeout(function () { announcer.textContent = ''; setTimeout(function () { announcer.textContent = msgText; }, 30); }, 700);
                }
                wasOver = !!j.over;
                var errs = [], notes = [];
                if (j.unknown && j.unknown.length) { errs.push('Unknown placeholder: ' + j.unknown.map(function (x) { return /^\{.*\}$/.test(x) ? x : '{' + x + '}'; }).join(' ') + '. It stays as typed.'); }
                if (j.over) { errs.push('Still too long for this platform after shortening. Remove some fixed words.'); }
                else if (j.trimmed) { notes.push('For a long issue the summary is shortened automatically so the post fits.'); }
                if (warn) {
                    warn.hidden = errs.length + notes.length === 0;
                    warn.textContent = errs.concat(notes).join(' ');
                    warn.classList.toggle('field__error', errs.length > 0);
                    warn.classList.toggle('field__help', errs.length === 0);
                }
            }

            var run = debounce(function () {
                fetch(url, {
                    method: 'POST', credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({channel: ch, template: ta.value}).toString()
                }).then(function (r) { return r.json(); }).then(paint).catch(function () { /* keep the last preview */ });
            }, 350);

            ta.addEventListener('input', run);
            run();
            card.querySelectorAll('[data-insert]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var tok = b.getAttribute('data-insert');
                    var s = ta.selectionStart || 0, e = ta.selectionEnd || 0;
                    ta.value = ta.value.slice(0, s) + tok + ta.value.slice(e);
                    ta.focus(); ta.selectionStart = ta.selectionEnd = s + tok.length;
                    ta.dispatchEvent(new Event('input', {bubbles: true}));
                });
            });
            var reset = card.querySelector('[data-reset]');
            if (reset) {
                reset.addEventListener('click', function () {
                    ta.value = card.getAttribute('data-default') || '';
                    ta.dispatchEvent(new Event('input', {bubbles: true}));
                    ta.focus();
                });
            }
        });
    }
    document.querySelectorAll('form[data-asoc-templates]').forEach(initTemplates);

    // ================================================================================================ 4. the share panel
    function initPanel(root) {
        var base = root.getAttribute('data-api');
        var body = root.querySelector('[data-social-panel-body]');
        var panel = null;
        var saveTimer = null;
        var pollTimer = null;
        var polls = 0;
        var statusLine = null;
        var announcer = null;
        var dirty = false;
        var lastErrors = {};                 // channel -> why the last save() refused it (empty after a successful save)

        function setStatus(text, kind) {
            if (statusLine) {
                statusLine.textContent = text;
                statusLine.className = 'asoc-share__status' + (kind ? ' asoc-share__status--' + kind : '');
            }
        }

        function collect() {
            var out = {};
            body.querySelectorAll('[data-share-channel]').forEach(function (box) {
                var id = box.getAttribute('data-share-channel');
                var on = box.querySelector('input[type="checkbox"]');
                var cap = box.querySelector('textarea');
                if (box.getAttribute('data-locked') === '1') { return; }                // queued, being posted or posted: the text is frozen in the post, nothing to save
                if (box.getAttribute('data-has-post') === '1') {                          // failed / skipped / needs review: the edited text is what a retry sends
                    if (cap && !cap.readOnly) { out[id] = {on: true, caption: cap.value}; }
                    return;
                }
                if (on && !on.disabled) {
                    out[id] = {on: on.checked, caption: cap ? cap.value : ''};
                }
            });
            return out;
        }

        function save() {
            clearTimeout(saveTimer);
            setStatus('Saving...', '');
            return api('PUT', base, {channels: collect()}).then(function (r) {
                body.querySelectorAll('.asoc-share__error').forEach(function (e) { e.textContent = ''; });
                if (r.status === 200 && r.data.ok) {
                    dirty = false;
                    lastErrors = {};
                    setStatus('Saved with the issue.', 'ok');
                    if (r.data.panel) { panel = r.data.panel; }
                } else if (r.status === 422 && r.data.errors) {
                    lastErrors = {};
                    Object.keys(r.data.errors).forEach(function (id) {
                        lastErrors[id] = String(r.data.errors[id]);
                        var box = body.querySelector('[data-share-channel="' + id + '"] .asoc-share__error');
                        if (box) { box.textContent = r.data.errors[id]; }
                    });
                    setStatus((r.data.message || 'Please check the highlighted channels.') + ' Nothing was saved.', 'bad');
                } else {
                    lastErrors = {'_': 'The choices could not be saved.'};
                    setStatus('The choices could not be saved. Reload the page and try again.', 'bad');
                }
            }).catch(function () { lastErrors = {'_': 'You are offline: the choices were not saved.'}; setStatus('You are offline. The choices were not saved.', 'bad'); });
        }

        function later() {
            dirty = true;
            clearTimeout(saveTimer);
            saveTimer = setTimeout(save, 900);
        }

        function tell(text) {
            if (!announcer) { return; }
            announcer.textContent = '';
            setTimeout(function () { announcer.textContent = text; }, 30);
        }

        function paintCount(box, d) {
            var out = box.querySelector('.asoc-share__count');
            if (!out) { return; }
            var soft = !d.over && d.soft > 0 && d.length > d.soft;
            out.textContent = d.length + ' / ' + d.limit + (d.counting === 'x' ? ' (a link counts 23)' : '') + (d.over ? ' - too long' : (soft ? ' - long for readers' : ''));
            out.className = 'asoc-share__count' + (d.over ? ' asoc-share__count--over' : (soft ? ' asoc-share__count--soft' : ''));
            var cap = box.querySelector('textarea');
            if (cap && !cap.readOnly) { cap.setAttribute('aria-invalid', d.over ? 'true' : 'false'); }
            var was = box.getAttribute('data-over');
            box.setAttribute('data-over', d.over ? '1' : '0');
            if (was !== null && was !== (d.over ? '1' : '0')) {                       // one message when the state changes (not one per keystroke)
                clearTimeout(box._tellTimer);
                var name = box.getAttribute('data-share-name') || 'This caption';
                box._tellTimer = setTimeout(function () {
                    tell(d.over ? name + ': ' + (d.length - d.limit) + ' character' + (d.length - d.limit === 1 ? '' : 's') + ' over the limit. It will not be saved until it is shorter.' : name + ': back within the limit.');
                }, 600);
            }
        }

        // a refreshed answer for one caption; resolves with {over: bool|null}. A text far too long for any platform is refused by the server (422) and counts as over.
        function count(box, id) {
            var cap = box.querySelector('textarea');
            if (!cap) { return Promise.resolve({over: false}); }
            return api('POST', base + '/count', {channel: id, text: cap.value}).then(function (r) {
                if (r.status === 200) { paintCount(box, r.data); return {over: !!r.data.over}; }
                if (r.status === 422 && r.data && r.data.over) {
                    paintCount(box, {length: cap.value.length, limit: r.data.limit || 0, over: true, soft: 0, counting: 'plain'});
                    return {over: true};
                }
                return {over: null};
            }).catch(function () { return {over: null}; });
        }

        function action(path, payload, label) {
            setStatus(label, '');
            return api('POST', base + path, payload || {}).then(function (r) {
                if (r.data && r.data.panel) { panel = r.data.panel; render(); }
                if (r.status >= 400) { setStatus((r.data && r.data.message) || 'That did not work.', 'bad'); }
                else if (r.data && r.data.errors && Object.keys(r.data.errors).length) { setStatus(Object.keys(r.data.errors).map(function (k) { return r.data.errors[k]; }).join(' '), 'bad'); }
                else { setStatus('Done. The result appears next to each channel.', 'ok'); }
                poll(true);
            });
        }

        function rowAction(postId, what) {
            return api('POST', base.replace(/newsletters\/\d+\/share$/, 'social/posts/' + postId + '/' + what)).then(function (r) {
                if (r.status >= 400) { setStatus((r.data && r.data.message) || 'That did not work.', 'bad'); }
                return load(true).then(function () { poll(true); });
            });
        }

        function flowBanner() {
            var i = panel.issue || {};
            var scheduled = i.status === 'scheduled' || (!i.is_live && i.scheduled_for);
            var cls = 'asoc-share__flow' + (i.is_live ? ' asoc-share__flow--live' : (scheduled ? ' asoc-share__flow--sched' : ''));
            var text = panel.note;
            if (scheduled && i.scheduled_for) { text = 'This issue is scheduled for ' + (i.scheduled_label || i.scheduled_for) + '. Your choices below are saved with it and used the moment it goes public. ' + 'Deleting the issue later does not delete posts that were already made.'; }
            return h('div', {class: cls}, [h('p', {text: text})]);
        }

        function channelBox(c) {
            var st = c.status;
            var cb = h('input', {type: 'checkbox', id: 'share-' + c.id, checked: c.on && c.available, disabled: !c.available || c.has_post});
            var cls = 'asoc-share__channel ' + (!c.available ? 'is-na' : (c.on ? 'is-on' : 'is-off'));
            var pill;
            if (st.state === 'posted' && st.remote_url) {
                pill = h('span', {class: 'asoc-share__post asoc-share__post--posted'}, [h('a', {href: st.remote_url, target: '_blank', rel: 'noopener noreferrer', text: st.label})]);
            } else {
                pill = h('span', {class: 'asoc-share__post asoc-share__post--' + st.state, text: st.label});
            }
            var box = h('fieldset', {class: cls, 'data-share-channel': c.id, 'data-share-name': c.label, 'data-locked': c.locked ? '1' : null, 'data-has-post': c.has_post ? '1' : null}, [
                h('legend', {class: 'visually-hidden', text: c.label}),
                h('div', {class: 'asoc-share__head'}, [h('label', {class: 'asoc-share__tick', for: 'share-' + c.id}, [cb, h('span', {text: c.label})]), pill])
            ]);
            if (st.error) { box.appendChild(h('p', {class: 'asoc-share__err', text: st.error})); }
            if (!c.available) {
                box.appendChild(h('p', {class: 'asoc-share__na', text: c.reason}));
            } else {
                var counter = h('span', {class: 'asoc-share__count', id: 'share-count-' + c.id});
                var cap = h('textarea', {class: 'textarea asoc-share__cap', rows: '5', 'aria-label': 'Caption for ' + c.label, text: c.caption, readonly: c.locked, 'aria-readonly': c.locked ? 'true' : null,
                    'aria-describedby': 'share-count-' + c.id + ' share-err-' + c.id});
                var reset = h('button', {type: 'button', class: 'asoc-share__reset', text: 'Use the template text', hidden: !c.edited || c.locked});
                var bodyEl = h('div', {class: 'asoc-share__body'}, [
                    cap,
                    h('div', {class: 'asoc-share__meta'}, [counter, reset]),
                    c.locked ? h('p', {class: 'asoc-share__hint', text: st.state === 'posted' ? 'This is the text that was posted. A post that was already made cannot be edited here.' : 'This is the text that is being posted. It was fixed when the post was queued.'}) : null,
                    !c.locked && c.has_post ? h('p', {class: 'asoc-share__hint', text: 'Change the text if you like, then press "Retry": the retry uses the text saved here.'}) : null,
                    c.links === 'none' ? h('p', {class: 'asoc-share__hint', text: 'Links in a caption are not clickable on this platform, so the text says "link in bio".'}) : null,
                    c.uses_image ? h('p', {class: 'asoc-share__hint', text: 'Posted together with the picture shown above.'}) : null
                ]);
                box.appendChild(bodyEl);
                paintCount(box, {length: c.length, limit: c.limit, over: c.over, soft: c.soft, counting: c.counting});
                cb.addEventListener('change', function () {
                    box.classList.toggle('is-on', cb.checked);
                    box.classList.toggle('is-off', !cb.checked);
                    later();
                });
                if (!c.locked) {
                    cap.addEventListener('input', function () {
                        later();
                        reset.hidden = cap.value.trim() === c.template.trim();
                        clearTimeout(cap._t);
                        cap._t = setTimeout(function () { count(box, c.id); }, 400);
                    });
                    reset.addEventListener('click', function () { cap.value = c.template; reset.hidden = true; count(box, c.id); later(); cap.focus(); });
                }
            }
            box.appendChild(h('p', {class: 'asoc-share__error field__error', role: 'alert', id: 'share-err-' + c.id}));
            if (c.again_wait > 0) {
                box.appendChild(h('p', {class: 'asoc-share__hint', text: 'Posted a moment ago. "Post again" is available in about ' + c.again_wait + ' second' + (c.again_wait === 1 ? '' : 's') + ' (reload this panel): until then a second click is most likely a double click.'}));
            }
            if (c.can_post_again && c.again_note) { box.appendChild(h('p', {class: 'asoc-share__hint', text: c.again_note})); }
            var acts = h('div', {class: 'asoc-share__acts'});
            if (c.can_share_now) { acts.appendChild(h('button', {type: 'button', class: 'btn btn--outline btn--sm', 'data-share-now': c.id, text: 'Share now'})); }
            if (c.can_post_again) { acts.appendChild(h('button', {type: 'button', class: 'btn btn--ghost btn--sm', 'data-share-again': c.id, text: 'Post again'})); }
            if (c.can_retry && st.post_id) { acts.appendChild(h('button', {type: 'button', class: 'btn btn--outline btn--sm', 'data-share-retry': st.post_id, text: 'Retry'})); }
            if (st.state === 'review' && st.post_id) {
                acts.appendChild(h('button', {type: 'button', class: 'btn btn--outline btn--sm', 'data-share-posted': st.post_id, text: 'Mark as posted'}));
                acts.appendChild(h('button', {type: 'button', class: 'btn btn--ghost btn--sm', 'data-share-skip': st.post_id, text: 'Skip'}));
            }
            if (acts.children.length) { box.appendChild(acts); }
            return box;
        }

        function render() {
            body.textContent = '';
            if (!panel || !panel.ok) {
                body.appendChild(h('p', {text: 'The share options could not be loaded.'}));
                return;
            }
            body.appendChild(flowBanner());
            (panel.warnings || []).forEach(function (w) { body.appendChild(h('p', {class: 'asoc-share__warn', text: w})); });
            if (!panel.global_on) {
                body.appendChild(h('p', {class: 'asoc-share__warn', text: 'Auto-share is switched off for the whole site, so nothing is posted.'}));
            }
            if (!panel.configured) {
                var p = h('div', {class: 'asoc-share__empty'}, [h('p', {text: 'No social media channel is set up yet, so nothing will be posted automatically.'})]);
                var line = h('p', {});
                if (root.getAttribute('data-channels-url')) { line.appendChild(h('a', {href: root.getAttribute('data-channels-url'), text: 'Set up a channel'})); line.appendChild(document.createTextNode(' or ')); }
                line.appendChild(h('a', {href: root.getAttribute('data-kit-url'), text: 'open the Share kit'}));
                line.appendChild(document.createTextNode(' to post by hand in a minute (TikTok and anything else).'));
                p.appendChild(line);
                body.appendChild(p);
            }
            var layout = h('div', {class: 'asoc-share__layout'});
            if (panel.image && panel.image.thumb_url && panel.channels.length) {
                var side = h('div', {class: 'asoc-share__side'});
                var img = h('img', {src: panel.image.thumb_url, alt: 'The picture that goes with the posts', width: '88', height: '88', class: 'asoc-share__thumb'});
                img.addEventListener('error', function () { side.hidden = true; });
                side.appendChild(img);
                side.appendChild(h('p', {class: 'asoc-share__thumb-cap', text: (panel.image.has_cover ? 'The picture made from the issue cover photo.' : 'No cover photo: a brand picture is used.') + ' It goes with the Instagram and Telegram posts.'}));
                layout.appendChild(side);
            }
            var chans = h('div', {class: 'asoc-share__channels'});
            panel.channels.forEach(function (c) { chans.appendChild(channelBox(c)); });
            if (panel.channels.length) { layout.appendChild(chans); }
            if (layout.children.length) { body.appendChild(layout); }
            if (panel.hidden && panel.hidden.length && panel.configured) {
                body.appendChild(h('p', {class: 'asoc-share__hint', text: 'Not shown because they are not set up or are switched off: ' + panel.hidden.map(function (x) { return x.label; }).join(', ') + '.'}));
            }
            statusLine = h('p', {class: 'asoc-share__status', role: 'status'});
            body.appendChild(statusLine);
            announcer = h('p', {class: 'visually-hidden', role: 'status', 'aria-live': 'polite'});      // the ONE live region for "N characters over" (the panel itself is not live)
            body.appendChild(announcer);
            body.appendChild(h('p', {class: 'asoc-share__links'}, [h('a', {href: root.getAttribute('data-kit-url'), text: 'Share kit (pictures and captions to post by hand)'}), h('a', {href: root.getAttribute('data-log-url'), text: 'Share log for this issue'})]));
        }

        function anyWaiting() {
            return panel && panel.channels && panel.channels.some(function (c) { return c.status.state === 'queued' || c.status.state === 'posting'; });
        }

        function poll(reset) {
            clearTimeout(pollTimer);
            if (reset) { polls = 0; }
            if (!anyWaiting() && !reset) { return; }
            pollTimer = setTimeout(function () {
                polls++;
                var kick = polls === 2 && anyWaiting() ? api('POST', base + '/dispatch', {}) : Promise.resolve();      // hosts that cannot post after the response: ask the server to try now
                kick.then(function () { return load(true); }).then(function () { if (anyWaiting() && polls < 20) { poll(false); } });
            }, 3000);
        }

        function load(quiet) {
            return api('GET', base).then(function (r) {
                if (r.status === 200) {
                    var typed = quiet && dirty ? collect() : null;      // keep what the admin is typing while only the statuses refresh
                    panel = r.data;
                    render();
                    if (typed) {
                        Object.keys(typed).forEach(function (id) {
                            var box = body.querySelector('[data-share-channel="' + id + '"]');
                            var ch = panel.channels.filter(function (c) { return c.id === id; })[0];
                            if (box && ch && !ch.status.post_id) {
                                var on = box.querySelector('input[type="checkbox"]'); var cap = box.querySelector('textarea');
                                if (on && !on.disabled) { on.checked = typed[id].on; box.classList.toggle('is-on', on.checked); box.classList.toggle('is-off', !on.checked); }
                                if (cap) { cap.value = typed[id].caption; count(box, id); }
                            }
                        });
                    }
                    if (anyWaiting()) { poll(false); }
                } else if (!quiet) {
                    body.textContent = 'The share options could not be loaded.';
                }
            }).catch(function () { if (!quiet) { body.textContent = 'You are offline: the share options could not be loaded.'; } });
        }

        body.addEventListener('click', function (ev) {
            var t = ev.target.closest('button');
            if (!t) { return; }
            if (t.hasAttribute('data-share-now')) { save().then(function () { action('/now', {channels: [t.getAttribute('data-share-now')]}, 'Posting...'); }); }
            if (t.hasAttribute('data-share-again')) {
                if (window.confirm('This posts the issue AGAIN on this channel, as a second post. Continue?')) {
                    t.disabled = true;                                                                      // one click, one request
                    action('/again', {channels: [t.getAttribute('data-share-again')], confirm: true, share_version: (panel.issue && panel.issue.share_version) || 1}, 'Posting again...').then(function () { t.disabled = false; });
                }
            }
            if (t.hasAttribute('data-share-retry')) { rowAction(t.getAttribute('data-share-retry'), 'retry'); }
            if (t.hasAttribute('data-share-skip')) { rowAction(t.getAttribute('data-share-skip'), 'skip'); }
            if (t.hasAttribute('data-share-posted')) { rowAction(t.getAttribute('data-share-posted'), 'mark-posted'); }
        });

        document.addEventListener('lumina:newsletter-item', function () { (dirty ? save() : Promise.resolve()).then(function () { return load(true); }).then(function () { poll(true); }); });

        // The publish dialog. The list is built from the LIVE state of the boxes, then checked again against the server: the choices are saved first (and the answer of that
        // save is read: a refused caption is said so), every typed caption is counted again, and only then does the list say what will really be posted. The newsletter editor
        // waits for `detail.ready` before it opens the dialog; the list also refreshes itself in place when it was shown earlier.
        document.addEventListener('lumina:newsletter-publish-dialog', function (ev) {
            if (!panel || !ev.detail || !ev.detail.body) { return; }
            if (!panel.configured) {
                return;                                                   // nothing is set up: do not add noise to the dialog
            }
            var box = h('div', {class: 'asoc-share__publish', role: 'group', 'aria-label': 'Social media'});
            ev.detail.body.appendChild(box);

            function draw(checking) {
                var chosen = collect();
                var on = panel.channels.filter(function (c) { return c.available && chosen[c.id] && chosen[c.id].on && !c.status.post_id; });
                var already = panel.channels.filter(function (c) { return c.status.post_id; });
                var bad = {};
                body.querySelectorAll('[data-share-channel]').forEach(function (b) {          // the LIVE state of every caption box, not the stale server panel
                    var id = b.getAttribute('data-share-channel');
                    if (b.getAttribute('data-over') === '1' || lastErrors[id]) { bad[id] = lastErrors[id] || 'The caption is too long.'; }
                });
                box.textContent = '';
                if (!panel.global_on) {
                    box.appendChild(h('span', {text: 'Social media: auto-share is switched off for the whole site, so nothing will be posted.'}));
                } else if (on.length) {
                    box.appendChild(h('span', {text: 'Social media: it will also be posted to ' + on.length + ' channel' + (on.length === 1 ? '' : 's') + ' as soon as it is public:'}));
                    var ul = h('ul', {});
                    on.forEach(function (c) {
                        var why = bad[c.id];
                        ul.appendChild(h('li', {class: why ? 'asoc-share__publish-bad' : null, text: c.label + (why ? ' - the caption you typed is too long and was NOT saved: the last saved caption, or the standard text, will be posted instead. (' + why + ')' : '')}));
                    });
                    box.appendChild(ul);
                } else {
                    box.appendChild(h('span', {text: already.length ? 'Social media: this issue was already posted; nothing new will be posted.' : 'Social media: no channel is ticked, so it will not be posted anywhere.'}));
                }
                if (lastErrors['_']) {
                    box.appendChild(h('p', {class: 'asoc-share__warn', text: lastErrors['_'] + ' The choices saved earlier are used.'}));
                } else if (Object.keys(lastErrors).length) {
                    box.appendChild(h('p', {class: 'asoc-share__warn', text: 'Some choices could not be saved, so none of your latest changes are used: the choices saved earlier apply. Cancel this box and fix the highlighted caption first.'}));
                }
                box.appendChild(h('p', {class: 'asoc-share__hint', text: checking ? 'Checking the captions...' : 'To change the channels or the captions, cancel this box and use "Share on social media" on the page.'}));
            }

            draw(true);
            var work = [];
            if (dirty) { work.push(save()); }                                 // the choices are used when the issue goes public: never leave one unsaved, and read the answer
            body.querySelectorAll('[data-share-channel]').forEach(function (b) {
                var cap = b.querySelector('textarea');
                if (cap && !cap.readOnly && b.getAttribute('data-locked') !== '1') { work.push(count(b, b.getAttribute('data-share-channel'))); }
            });
            var finished = Promise.all(work).then(function () { draw(false); }, function () { draw(false); });
            if (Array.isArray(ev.detail.ready)) {
                ev.detail.ready.push(Promise.race([finished, new Promise(function (res) { setTimeout(res, 6000); })]));
            }
        });
        window.addEventListener('focus', function () { if (panel && !dirty) { load(true); } });
        load();
    }

    document.querySelectorAll('[data-social-panel]').forEach(initPanel);
})();
