/*!
 * admin-meetings-send.js - the sending side of the meeting page (ADMIN AREA ONLY): confirm dialogs with the exact number of people, the queue + batch loop with a
 * progress bar, pause / resume, per-person results, the test invitation, the update message (chunks of 30) and "send reminder now".
 * OWNER: MAIL agent. Contract: docs/MEETINGS.md sections 7, 8, 10 and 12.3 (endpoints under /admin/api/meetings/{id}/mail).
 *
 * How sending works: a button asks for confirmation (the server's own count, read just before), POSTs /send {scope, confirm_count}, then this script calls POST /batch
 * again and again; each call sends one batch on the server (size from Settings) and returns the progress. The server holds a lock, so a second tab, a second
 * administrator or the cron job (bin/meetings-send.php) can never send at the same time; "busy" just means somebody else is sending. Closing the page stops this
 * loop only: the messages stay queued and the cron job (or "Continue sending") finishes them. Nothing is ever sent twice.
 *
 * HOOKS (the page is rendered by app/views/admin/meetings/show.php; everything below is looked up, nothing is required except the root):
 *   [data-meeting-send]            ROOT (data-api = .../admin/api/meetings/{id}/mail, data-id, data-auto="1" = start the loop at once, e.g. after ?send=1,
 *                                  data-state = JSON of the counts at render time). The panel (status, progress bar, buttons, results) is built inside its .card__body.
 *   [data-ma-send-summary]         paragraph inside the root: the counts line ("N sent, N waiting to be sent, N failed, N not invited yet."), kept up to date.
 *   [data-ma-queue="SCOPE"]        any button, anywhere: invite_new | invite_pending | update_all | cancel_all | retry_failed | one (+ data-attendee-id, data-person,
 *                                  data-label). Opens the confirm dialog, queues and runs the loop. Panel buttons appear only for scopes that have no such trigger.
 *   [data-ma-test] / [data-ma-test-status]            "Send me a test invitation" and its status line.
 *   form[data-ma-message] with [data-ma-msg-to] (all | accepted | tentative | pending), [data-ma-msg-subject], [data-ma-msg-text], [data-ma-msg-send],
 *                                  [data-ma-msg-status]   the free-text update to the attendees, sent in chunks of 30.
 *   [data-ma-reminder="24h|1h"] / [data-ma-reminder-status]   "Send reminder now".
 *   [data-ma-live]                 aria-live region (the panel also has its own).
 * These hooks are handled here in the CAPTURE phase and the event is stopped, so they work exactly once even if another script also knows them.
 * Programmatic: window.LuminaMeetingSend.start() (refresh + run the loop), .refresh(), .status(); or dispatch a "meeting-send:start" event on document.
 * After a finished sending the note offers an "Update the list" button (the page is never reloaded by itself: a reload would lose what somebody is typing).
 *
 * No inline JS (CSP). Names and addresses are only ever written with textContent.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-meeting-send]');
    if (!root) { return; }

    var me = document.currentScript;
    var base = (root.getAttribute('data-api') || '').replace(/\/$/, '');
    if (base === '') { return; }
    var reduced = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var KIND_LABEL = { invite: 'invitation', update: 'updated invitation', cancel: 'cancellation', removed: 'removal notice' };
    var MAX_RESULTS = 150;
    var CHUNK = 30;

    var st = null;                 // last status from the server (GET .../mail)
    var running = false;           // the batch loop is active in THIS page
    var stopRequested = false;
    var busy = {};                 // double-click guards
    var run = { total: 0, sent: 0, failed: 0, dropped: 0, retried: 0, started: false };
    var noteState = { text: '', kind: '', list: null, reload: false };
    var initial = {};
    try { initial = JSON.parse(root.getAttribute('data-state') || '{}') || {}; } catch (e) { initial = {}; }

    // ------------------------------------------------------------------------------------------------ helpers
    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function num(n) { try { return Number(n || 0).toLocaleString('en-GB'); } catch (e) { return String(n || 0); } }
    function plural(n, one, many) { return n === 1 ? one : many; }
    function people(n) { return num(n) + ' ' + plural(n, 'person', 'people'); }
    function sleep(ms) { return new Promise(function (res) { window.setTimeout(res, ms); }); }
    /** Waits like sleep() but ends early when the administrator pressed Pause (the loop checks stopRequested right after). */
    function nap(ms) {
        return new Promise(function (res) {
            var end = Date.now() + ms;
            (function tick() {
                if (stopRequested || Date.now() >= end) { res(); return; }
                window.setTimeout(tick, Math.max(20, Math.min(250, end - Date.now())));
            }());
        });
    }
    function h(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) { n.className = cls; }
        if (text !== undefined && text !== null) { n.textContent = text; }
        return n;
    }
    function button(label, cls, onClick) {
        var b = h('button', 'btn btn--sm ' + (cls || 'btn--outline'), label);
        b.type = 'button';
        if (onClick) { b.addEventListener('click', onClick); }
        return b;
    }
    function setBusy(btn, on) {
        if (!btn) { return; }
        btn.disabled = !!on;
        if (on) { btn.setAttribute('aria-busy', 'true'); } else { btn.removeAttribute('aria-busy'); }
    }
    function $(sel) { return document.querySelector(sel); }
    function hostOf(url) {
        var m = /^https?:\/\/([^\/?#]+)/i.exec(url || '');
        return m ? m[1] : '';
    }

    // ------------------------------------------------------------------------------------------------ the API
    function api(method, path, body) {
        var opts = {
            method: method,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-Token': csrf(), 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(base + path, opts).then(function (res) {
            return res.text().then(function (t) {
                var data = {};
                try { data = t ? JSON.parse(t) : {}; } catch (e) { data = {}; }
                return { ok: res.ok, status: res.status, data: data };
            });
        });
    }
    // a human sentence for a failed response
    function problem(r) {
        var d = r.data || {};
        if (r.status === 401) { return 'You have been signed out. Sign in again in another tab, then come back here and continue.'; }
        if (r.status === 403) { return d.message || 'This page has expired or you are not allowed to do that. Reload the page and try again.'; }
        if (r.status === 404) { return d.message || 'That meeting no longer exists.'; }
        if (r.status === 429) { return d.message || 'Too many requests. Please wait a moment.'; }
        if (r.status >= 500) { return 'The server had a problem (error ' + r.status + '). Nothing is lost: wait a moment and try again.'; }
        return d.message || 'That did not work (error ' + r.status + ').';
    }

    // ------------------------------------------------------------------------------------------------ the panel
    var body = root.querySelector('.card__body') || root;
    var els = {};
    (function build() {
        els.panel = h('div', 'ms-panel');
        els.panel.setAttribute('data-ms-panel', '');
        els.transport = h('div', 'ms-transport');
        els.transport.hidden = true;
        var status = h('div', 'ms-status');
        els.text = h('p', 'ms-status__text');
        els.progress = h('div', 'ms-progress');
        els.progress.setAttribute('role', 'progressbar');
        els.progress.setAttribute('aria-label', 'Sending progress');
        els.progress.setAttribute('aria-valuemin', '0');
        els.progress.setAttribute('aria-valuemax', '100');
        els.progress.setAttribute('aria-valuenow', '0');
        els.bar = h('span', 'ms-progress__bar');
        els.progress.appendChild(els.bar);
        els.progress.hidden = true;
        els.sub = h('p', 'ms-status__sub');
        els.sub.hidden = true;
        status.appendChild(els.text);
        status.appendChild(els.progress);
        status.appendChild(els.sub);
        els.note = h('div', 'ms-note');
        // announced through say() (ONE live region); only an error is a live alert of its own (see note())
        els.note.hidden = true;
        els.actions = h('div', 'ms-actions');
        els.btnContinue = button('Continue sending', 'btn--primary', function () { start(); });
        els.btnPause = button('Pause', 'btn--outline', pauseSending);
        els.btnResume = button('Resume', 'btn--primary', resumeSending);
        els.dyn = h('span', 'ms-actions');
        els.actions.appendChild(els.btnContinue);
        els.actions.appendChild(els.btnPause);
        els.actions.appendChild(els.btnResume);
        els.actions.appendChild(els.dyn);
        els.results = h('details', 'ms-results');
        els.resultsSummary = h('summary', '', 'Who was e-mailed');
        els.resultsList = h('ul', 'ms-results__list');
        els.results.appendChild(els.resultsSummary);
        els.results.appendChild(els.resultsList);
        els.results.hidden = true;
        els.live = h('div', 'ms-live');
        els.live.setAttribute('aria-live', 'polite');
        els.live.setAttribute('role', 'status');
        els.panel.appendChild(els.transport);
        els.panel.appendChild(status);
        els.panel.appendChild(els.note);
        els.panel.appendChild(els.actions);
        els.panel.appendChild(els.results);
        els.panel.appendChild(els.live);
        body.appendChild(els.panel);
        // the stylesheet of the panel lives next to this script (the SCSS entry files are frozen for this feature)
        var href = root.getAttribute('data-css') || (me && me.src ? me.src.replace(/\/js\/admin-meetings-send\.js(\?.*)?$/, '/css/admin-meetings-send.css$1') : '');
        if (href && !document.querySelector('link[data-ms-css]')) {
            var link = document.createElement('link');
            // keep the panel invisible until its stylesheet is there (no flash of unstyled buttons); a timer shows it anyway if the file never arrives
            var reveal = function () { els.panel.style.visibility = ''; };
            els.panel.style.visibility = 'hidden';
            link.onload = reveal;
            link.onerror = reveal;
            window.setTimeout(reveal, 1500);
            link.rel = 'stylesheet';
            link.href = href;
            link.setAttribute('data-ms-css', '');
            document.head.appendChild(link);
        }
    }());

    /** Announce a message ONCE: the page's own live bar when it has one, else the panel's hidden region (never both: a screen reader would read it twice). */
    function say(msg) {
        var live = $('[data-ma-live]') || els.live;
        if (live) { live.textContent = ''; window.setTimeout(function () { live.textContent = msg; }, 30); }
    }

    /** kind: '' | info | ok | warn | error. list = extra lines. */
    function note(msg, kind, list, reloadButton) {
        noteState = { text: msg || '', kind: kind || '', list: list || null, reload: !!reloadButton };
        els.note.textContent = '';
        els.note.hidden = noteState.text === '';
        els.note.className = 'ms-note' + (kind ? ' is-' + kind : '');
        if (kind === 'error') { els.note.setAttribute('role', 'alert'); } else { els.note.removeAttribute('role'); }
        if (noteState.text === '') { return; }
        els.note.appendChild(document.createTextNode(noteState.text));
        if (list && list.length) {
            var ul = h('ul');
            list.forEach(function (t) { ul.appendChild(h('li', '', t)); });
            els.note.appendChild(ul);
        }
        if (reloadButton) {
            els.note.appendChild(document.createElement('br'));
            els.note.appendChild(button('Update the list', 'btn--outline', function () { reloadPage(); }));
        }
    }

    function haltNote(why) {
        return 'Sending was paused so that nobody is marked as failed by mistake: ' + why + (/resume/i.test(why) ? '' : ' Press Resume when it is fixed.');
    }

    function view() {
        var s = st || {};
        var m = s.meeting || {};
        var q = s.queue || {};
        return { waiting: +q.waiting || 0, failedQ: +q.failed || 0, paused: !!m.send_paused, halt: m.halt_reason || '', counts: s.counts || {}, status: m.status || '', avail: s.available || {} };
    }

    function render() {
        var v = view();
        var c = v.counts;
        // the counts line the page already has
        var p = root.querySelector('[data-ma-send-summary]');
        if (p && st) {
            p.textContent = '';
            [[c.sent, ' sent, '], [v.waiting, ' waiting to be sent, '], [c.failed, ' failed, '], [c.not_sent, ' not invited yet.']].forEach(function (pair) {
                p.appendChild(h('strong', '', String(+pair[0] || 0)));
                p.appendChild(document.createTextNode(pair[1]));
            });
        }
        // the "Paused" badge and the stale halt reason of the server-rendered card
        var header = root.querySelector('.card__header');
        if (header) {
            var badge = header.querySelector('.badge--warning');
            if (!badge && v.paused) {
                badge = h('span', 'badge badge--warning', 'Paused');
                header.appendChild(badge);
            }
            if (badge) { badge.hidden = !v.paused; }
        }
        var haltP = root.querySelector('.ma-send__halt');
        if (haltP) { haltP.style.display = 'none'; }                // the reason is shown by the panel's own note below
        if (v.paused && v.halt && noteState.text === '') { note(haltNote(v.halt), 'error'); }

        // transport notice
        var t = st && st.transport;
        if (t && t.level !== 'ok') {
            els.transport.hidden = false;
            els.transport.className = 'ms-transport ' + (t.level === 'error' ? 'is-error' : (t.level === 'info' ? 'is-info' : ''));
            els.transport.textContent = t.message;
        } else {
            els.transport.hidden = true;
        }

        // status text, progress
        var text;
        var total = run.total || 0;
        var done = Math.max(0, total - v.waiting);
        if (v.paused) {
            text = 'Sending is paused. ' + num(v.waiting) + ' ' + plural(v.waiting, 'message is', 'messages are') + ' still waiting' + (v.halt ? '' : '. Press Resume to continue') + '.';
        } else if (running) {
            text = 'Sending: ' + num(done) + ' of ' + num(total) + ' done' + (run.failed ? ', ' + num(run.failed) + ' could not be sent' : '') + '.';
        } else if (v.waiting > 0) {
            text = num(v.waiting) + ' ' + plural(v.waiting, 'message is', 'messages are') + ' waiting to be sent. Press "Continue sending", or leave it: the scheduled job (if it is set up) sends them.';
        } else if (run.started && (run.sent + run.failed + run.dropped) > 0) {
            text = 'Finished: ' + num(run.sent) + ' sent' + (run.failed ? ', ' + num(run.failed) + ' could not be sent' : '') + (run.dropped ? ', ' + num(run.dropped) + ' not needed any more' : '') + '.';
        } else {
            text = 'Nothing is waiting to be sent.';
        }
        els.text.textContent = text;
        var showBar = running || v.waiting > 0 || (run.started && (run.sent + run.failed + run.dropped) > 0);
        els.progress.hidden = !showBar;
        if (showBar) {
            var pct = total > 0 ? Math.round((done / total) * 100) : (v.waiting > 0 ? Math.round(((+c.sent || 0) / Math.max(1, (+c.sent || 0) + v.waiting)) * 100) : 100);
            pct = Math.max(0, Math.min(100, pct));
            if (!running && v.waiting === 0) { pct = 100; }
            els.progress.setAttribute('aria-valuenow', String(pct));
            els.progress.setAttribute('aria-valuetext', pct + ' percent done');
            els.bar.style.width = pct + '%';
            els.bar.className = 'ms-progress__bar' + (!running && v.waiting === 0 ? ' is-done' : '') + (run.failed ? ' has-failed' : '');
        }
        if (st && st.meeting && st.meeting.when) {
            els.sub.hidden = false;
            els.sub.textContent = st.meeting.when + ' (East Africa Time). Each person gets their own e-mail. Nobody sees anybody else\'s address.';
        }

        // buttons
        els.btnContinue.hidden = !(v.waiting > 0 && !running && !v.paused);
        els.btnPause.hidden = !((running || v.waiting > 0) && !v.paused);
        els.btnResume.hidden = !v.paused;
        renderFallbackActions(v);
    }

    /** Buttons for what can be sent, only for scopes the page has no trigger of its own for. */
    function renderFallbackActions(v) {
        els.dyn.textContent = '';
        if (!st || running) { return; }
        var defs = [
            ['invite_new', function (n) { return 'Send invitations to ' + people(n); }, 'btn--primary'],
            ['update_all', function (n) { return 'Send the updated invitation to ' + people(n); }, 'btn--primary'],
            ['cancel_all', function (n) { return 'Send the cancellation to ' + people(n); }, 'btn--primary'],
            ['retry_failed', function (n) { return 'Try the ' + num(n) + ' failed ' + plural(n, 'message', 'messages') + ' again'; }, 'btn--outline']
        ];
        var draftNotReady = v.status === 'draft' && st.readiness && Object.keys(st.readiness).length > 0;
        defs.forEach(function (d) {
            var n = +v.avail[d[0]] || 0;
            if (n < 1 || document.querySelector('[data-ma-queue="' + d[0] + '"]')) { return; }
            if (d[0] === 'invite_new' && draftNotReady) { return; }
            if (d[0] === 'update_all' && !(+st.stale > 0)) { return; }          // nobody holds an old version: the other buttons cover first invitations and failures
            var b = button(d[1](n), d[2]);
            b.setAttribute('data-ms-fallback', d[0]);
            b.addEventListener('click', function () { queueAction(d[0], b); });
            els.dyn.appendChild(b);
        });
    }

    function renderResults() {
        els.results.hidden = els.resultsList.children.length === 0;
        els.resultsSummary.textContent = 'Who was e-mailed (' + els.resultsList.children.length + (els.resultsList.children.length >= MAX_RESULTS ? '+' : '') + ')';
    }
    function addResult(r) {
        var li = h('li', 'is-' + r.status);
        li.appendChild(h('span', 'ms-r__what', { sent: 'Sent', failed: 'Failed', retry: 'Will try again', dropped: 'Not needed' }[r.status] || r.status));
        li.appendChild(h('span', 'ms-r__name', r.name || r.email));
        if (r.name) { li.appendChild(h('span', 'ms-r__mail', r.email)); }
        li.appendChild(h('span', 'ms-r__mail', '(' + (KIND_LABEL[r.kind] || 'message') + ')'));
        if (r.error) { li.appendChild(h('span', 'ms-r__why', r.error)); }
        els.resultsList.insertBefore(li, els.resultsList.firstChild);
        while (els.resultsList.children.length > MAX_RESULTS) { els.resultsList.removeChild(els.resultsList.lastChild); }
    }

    // ------------------------------------------------------------------------------------------------ state
    function refreshStatus(withAttendees) {
        return api('GET', withAttendees ? '?attendees=1' : '').then(function (r) {
            if (r.ok && r.data && r.data.meeting) { st = r.data; render(); }
            return r.ok ? r.data : null;
        }).catch(function () { return null; });
    }
    function applyBatch(d) {
        if (!st || !d) { return; }
        st.queue = st.queue || {};
        st.queue.waiting = d.remaining;
        if (d.counts && d.counts.total !== undefined) { st.counts = d.counts; }
        st.meeting = st.meeting || {};
        st.meeting.send_paused = !!d.paused;
        if (d.halted) { st.meeting.halt_reason = d.halted.reason || st.meeting.halt_reason; }
        render();
    }

    // ------------------------------------------------------------------------------------------------ the batch loop
    function stripSendParam() {
        try {
            if (/[?&]send=1(&|$)/.test(location.search) && window.history && history.replaceState) {
                var q = location.search.replace(/([?&])send=1(&|$)/, function (m, a, b) { return b ? a : ''; }).replace(/[?&]$/, '');
                history.replaceState(null, '', location.pathname + q + location.hash);
            }
        } catch (e) { /* cosmetic */ }
    }

    async function start() {
        var s = await refreshStatus();
        if (!s) { note('The server could not be reached. Nothing is lost: try again in a moment.', 'error'); return; }
        var v = view();
        if (v.paused) { note(v.halt ? haltNote(v.halt) : 'Sending is paused. Press Resume to continue.', v.halt ? 'error' : 'warn'); return; }
        if (v.waiting < 1) { note('', ''); render(); return; }
        return loop();
    }

    async function loop() {
        if (running) { return; }
        running = true;
        stopRequested = false;
        var v0 = view();
        run = { total: Math.max(v0.waiting, 0), sent: 0, failed: 0, dropped: 0, retried: 0, started: true };
        stripSendParam();
        note('', '');
        render();
        say('Sending started.');
        var netFails = 0, busyWaits = 0, idleSpins = 0, finished = false, halted = false;
        try {
            while (!stopRequested) {
                var r;
                try {
                    r = await api('POST', '/batch');
                } catch (err) {
                    netFails++;
                    if (netFails >= 5) { note('The connection was lost. Nothing is lost: press "Continue sending" when you are online again.', 'error'); break; }
                    note('Connection problem, trying again...', 'warn');
                    await nap(Math.min(30000, 2000 * Math.pow(2, netFails - 1)));
                    continue;
                }
                netFails = 0;
                if (!r.ok) { note(problem(r), 'error'); break; }
                var d = r.data || {};
                if (d.busy) {
                    busyWaits++;
                    if (busyWaits >= 10) { note('Another sender (a second page, another person or the scheduled job) is sending right now. It carries on without this page.', 'warn'); break; }
                    note('Another sender is working on this meeting, waiting...', 'warn');
                    await nap(3000);
                    continue;
                }
                busyWaits = 0;
                run.sent += +d.sent || 0;
                run.failed += +d.failed || 0;
                run.dropped += +d.dropped || 0;
                run.retried += +d.retried || 0;
                if (d.remaining > run.total - (run.sent + run.failed + run.dropped)) { run.total = (run.sent + run.failed + run.dropped) + d.remaining; }
                (d.results || []).forEach(addResult);
                renderResults();
                applyBatch(d);
                if (d.halted) {
                    halted = true;
                    var why = d.halted.reason || 'The mail server is not accepting messages.';
                    note(haltNote(why), 'error');
                    say('Sending paused because of a mail server problem.');
                    break;
                }
                if (d.paused) { note('Sending is paused.', 'warn'); break; }
                if (d.remaining === 0) { finished = true; break; }
                if (+d.processed === 0) {
                    idleSpins++;
                    if (idleSpins > 40) { note('Nothing could be sent for a while. Check the results below, then press "Continue sending".', 'warn'); break; }
                    note('Waiting to try the temporary failures again...', 'warn');
                    await nap(Math.max(2000, Math.min(30, +d.retry_in || 5) * 1000));
                    continue;
                }
                idleSpins = 0;
                if (noteState.kind === 'warn') { note('', ''); }
                await nap(reduced ? 400 : 250);
            }
        } finally {
            running = false;
            await refreshStatus();
            render();
            if (finished && !halted) {
                var msg = 'Finished: ' + num(run.sent) + ' sent' + (run.failed ? ', ' + num(run.failed) + ' could not be sent (the reason is beside each name below)' : '') + '.';
                say(msg);
                if (run.sent + run.failed + run.dropped > 0) { finishedNote(msg); } else { note('', ''); }
            } else if (stopRequested && !halted) {
                say('Sending paused.');
            }
        }
    }

    window.addEventListener('beforeunload', function (e) {
        if (running) { e.preventDefault(); e.returnValue = ''; }
    });

    // ------------------------------------------------------------------------------------------------ refreshing the people table
    // The page is NEVER reloaded by itself: a reload would throw away what somebody is typing (the "Add people" box, the update subject, the filter, a dialog)
    // and the list of who was e-mailed. The note says the table is out of date and offers the button.
    function reloadPage() {
        stripSendParam();
        window.location.reload();
    }
    function finishedNote(msg) {
        note(msg + ' The people table below shows the old state until you press the button.', 'ok', null, true);
    }

    // ------------------------------------------------------------------------------------------------ pause / resume
    async function pauseSending() {
        if (busy.pause) { return; }
        busy.pause = true;
        setBusy(els.btnPause, true);
        stopRequested = true;
        try {
            var r = await api('POST', '/pause');
            if (!r.ok) { note(problem(r), 'error'); stopRequested = false; await refreshStatus(); return; }
            st = r.data;
            note('Sending is paused. Nothing more is sent until you press Resume.', 'warn');
            say('Sending paused.');
        } catch (e) {
            note('The connection was lost. Reload the page to see where the sending stands.', 'error');
            stopRequested = false;
        } finally {
            busy.pause = false;
            setBusy(els.btnPause, false);
            render();
        }
    }
    async function resumeSending() {
        if (busy.resume) { return; }
        busy.resume = true;
        setBusy(els.btnResume, true);
        try {
            var r = await api('POST', '/resume');
            if (!r.ok) { note(problem(r), 'error'); await refreshStatus(); return; }
            st = r.data;
            note('', '');
            say('Sending resumed.');
            busy.resume = false;
            setBusy(els.btnResume, false);
            render();
            await loop();
            return;
        } catch (e) {
            note('The connection was lost. Reload the page to see where the sending stands.', 'error');
        } finally {
            busy.resume = false;
            setBusy(els.btnResume, false);
            render();
        }
    }

    // ------------------------------------------------------------------------------------------------ confirm dialog
    var dlg = null, dlgEls = {};
    function buildDialog() {
        dlg = document.createElement('dialog');
        dlg.className = 'ms-dialog';
        dlg.setAttribute('aria-labelledby', 'ms-d-title');
        dlg.setAttribute('aria-describedby', 'ms-d-text');
        var form = h('form', 'ms-dialog__form');
        form.method = 'dialog';
        dlgEls.title = h('h2', 'ms-dialog__title');
        dlgEls.title.id = 'ms-d-title';
        var bodyEl = h('div', 'ms-dialog__body');
        dlgEls.text = h('p');
        dlgEls.text.id = 'ms-d-text';
        dlgEls.facts = h('dl', 'ms-dialog__facts');
        dlgEls.warn = h('p', 'ms-dialog__warn');
        bodyEl.appendChild(dlgEls.text);
        bodyEl.appendChild(dlgEls.facts);
        bodyEl.appendChild(dlgEls.warn);
        var actions = h('div', 'ms-dialog__actions');
        dlgEls.cancel = button('Not yet', 'btn--outline');
        dlgEls.cancel.classList.remove('btn--sm');
        dlgEls.ok = button('OK', 'btn--primary');
        dlgEls.ok.classList.remove('btn--sm');
        actions.appendChild(dlgEls.cancel);
        actions.appendChild(dlgEls.ok);
        form.appendChild(dlgEls.title);
        form.appendChild(bodyEl);
        form.appendChild(actions);
        dlg.appendChild(form);
        document.body.appendChild(dlg);
    }
    /** o: {title, text, facts:[[label,value]], warn, ok, cancel}. Resolves true (OK) or false (cancelled / closed). */
    function confirmDialog(o) {
        return new Promise(function (resolve) {
            if (typeof HTMLDialogElement === 'undefined' || !document.createElement('dialog').showModal) {
                resolve(window.confirm(o.title + '\n\n' + (o.text || '')));
                return;
            }
            if (!dlg) { buildDialog(); }
            dlgEls.title.textContent = o.title;
            dlgEls.text.textContent = o.text || '';
            dlgEls.text.hidden = !o.text;
            dlgEls.facts.textContent = '';
            (o.facts || []).forEach(function (f) {
                if (f[1] === '' || f[1] === null || f[1] === undefined) { return; }
                var row = h('div');
                row.appendChild(h('dt', '', f[0]));
                row.appendChild(h('dd', '', String(f[1])));
                dlgEls.facts.appendChild(row);
            });
            dlgEls.facts.hidden = dlgEls.facts.children.length === 0;
            dlgEls.warn.textContent = o.warn || '';
            dlgEls.warn.hidden = !o.warn;
            dlgEls.ok.textContent = o.ok || 'OK';
            dlgEls.cancel.textContent = o.cancel || 'Not yet';
            var settled = false;
            var finish = function (v) {
                if (settled) { return; }
                settled = true;
                dlg.onclose = null;
                dlg.oncancel = null;
                dlg.onclick = null;
                if (dlg.open) { dlg.close(); }
                resolve(v);
            };
            dlgEls.ok.onclick = function () { finish(true); };
            dlgEls.cancel.onclick = function () { finish(false); };
            dlg.oncancel = function () { finish(false); };
            dlg.onclose = function () { finish(false); };
            dlg.onclick = function (ev) { if (ev.target === dlg) { finish(false); } };
            dlg.showModal();
            dlgEls.cancel.focus();
        });
    }

    // ------------------------------------------------------------------------------------------------ queue actions (confirm, send, run)
    function factsFor(scope, personName) {
        var m = (st && st.meeting) || {};
        var s = (st && st.sender) || {};
        var facts = [['Meeting', m.title], ['When', m.when]];
        if (scope === 'one' && personName) { facts.unshift(['To', personName]); }
        if (scope !== 'cancel_all') {
            facts.push(['Organiser', m.organiser_name ? m.organiser_name + ' <' + m.organiser_email + '>' : '']);
            facts.push(['Join link', m.join_url ? m.join_url : 'No link (' + (m.platform || 'see details') + ')']);
        }
        facts.push(['Replies go to', s.reply_to]);
        facts.push(['Sent from', s.from ? (s.from_name ? s.from_name + ' <' + s.from + '>' : s.from) : '']);
        return facts;
    }

    function dialogFor(scope, n, label, personName) {
        var d = { facts: factsFor(scope, personName), ok: 'Send', cancel: 'Not yet' };
        var m = (st && st.meeting) || {};
        if (scope === 'invite_new') {
            d.title = 'Send the invitation to ' + people(n) + '?';
            d.text = 'Each person gets their own e-mail with a Join button, Accept / Maybe / Decline buttons and a calendar file. Nobody sees anybody else\'s address.';
            if (m.status === 'draft') { d.warn = 'This also schedules the meeting.'; }
            d.ok = 'Send to ' + num(n);
        } else if (scope === 'update_all') {
            d.title = 'Send the updated invitation to ' + people(n) + '?';
            d.text = 'It is the same meeting with the new details: their calendar entry changes instead of a second one being added. People who declined are not e-mailed.'
                + (st && st.queue && +st.queue.failed > 0 ? ' Anybody whose earlier message failed is tried again too.' : '');
            d.ok = 'Send to ' + num(n);
        } else if (scope === 'invite_pending') {
            d.title = 'Send to the ' + people(n) + ' who have not been reached?';
            d.text = 'Only people who never received this version, or whose earlier message failed, are e-mailed.';
            d.ok = 'Send to ' + num(n);
        } else if (scope === 'cancel_all') {
            d.title = 'Tell ' + people(n) + ' the meeting is cancelled?';
            d.text = 'They get a cancellation that also removes the meeting from their calendar. People who already got it are not e-mailed again.';
            d.warn = 'A cancellation cannot be taken back.';
            d.ok = 'Send to ' + num(n);
        } else if (scope === 'retry_failed') {
            d.title = 'Try the ' + num(n) + ' failed ' + plural(n, 'message', 'messages') + ' again?';
            d.text = 'Only the messages that could not be sent are tried again. Check the reason beside each name first: a wrong address will fail again.';
            d.ok = 'Try again';
        } else {
            d.title = (label || 'Send the invitation again') + (personName ? ' to ' + personName : '') + '?';
            d.text = 'Only this person is e-mailed. Nobody else gets anything.';
            d.ok = 'Send';
        }
        return d;
    }

    function nothingToSend(scope) {
        return scope === 'cancel_all' ? 'Everyone has already been told about the cancellation.'
            : (scope === 'retry_failed' ? 'There are no failed messages to try again.' : 'Nobody needs this message right now (it was probably sent a moment ago).');
    }

    async function queueAction(scope, trigger) {
        if (busy.queue) { return; }
        busy.queue = true;
        setBusy(trigger, true);
        try {
            if (running) { note('Sending is in progress. Wait until it has finished, or pause it first.', 'warn'); return; }
            var s = await refreshStatus();
            if (!s) { note('The server could not be reached. Nothing was sent: try again in a moment.', 'error'); return; }
            if (st.transport && st.transport.blocking) { note(st.transport.message, 'error'); return; }
            if (st.meeting.status === 'draft' && scope === 'invite_new' && st.readiness && Object.keys(st.readiness).length) {
                note('The meeting is not ready to be sent yet:', 'error', Object.keys(st.readiness).map(function (k) { return st.readiness[k]; }));
                return;
            }
            var attendeeId = scope === 'one' ? parseInt(trigger.getAttribute('data-attendee-id') || '0', 10) : null;
            var person = trigger.getAttribute('data-person') || '';
            var n = scope === 'one' ? 1 : (+(st.available || {})[scope] || 0);
            if (n < 1) { note(nothingToSend(scope) + ' The people table below may be out of date.', 'info', null, true); return; }
            var ok = await confirmDialog(dialogFor(scope, n, trigger.getAttribute('data-label') || '', person));
            if (!ok) { return; }
            var body = { scope: scope };
            if (scope === 'one') { body.attendee_id = attendeeId; } else { body.confirm_count = n; }
            var r = await api('POST', '/send', body);
            if (r.ok) {
                st = r.data;
                els.resultsList.textContent = '';
                renderResults();
                busy.queue = false;
                setBusy(trigger, false);
                say(num(r.data.queued || 0) + ' ' + plural(r.data.queued, 'message is', 'messages are') + ' queued. Sending now.');
                await loop();
                return;
            }
            var d = r.data || {};
            if (r.status === 409 && d.error === 'count_changed') {
                note(d.message || 'The number of people changed. Check it and press the button again.', 'warn');
                await refreshStatus();
            } else if (r.status === 422 && d.error === 'not_ready') {
                var errs = d.errors ? Object.keys(d.errors).map(function (k) { return d.errors[k]; }) : [];
                note(errs.length ? 'The meeting is not ready to be sent yet:' : (d.message || 'It cannot be sent yet.'), 'error', errs);
            } else {
                note(problem(r), 'error');
                await refreshStatus();
            }
        } catch (err) {
            note('The connection was lost before the server answered. Check the numbers above before trying again.', 'error');
        } finally {
            busy.queue = false;
            setBusy(trigger, false);
            render();
        }
    }
    // ------------------------------------------------------------------------------------------------ test invitation
    async function sendTest(btn) {
        if (busy.test) { return; }
        busy.test = true;
        setBusy(btn, true);
        var out = $('[data-ma-test-status]');
        var show = function (t) { if (out) { out.textContent = t; } else { note(t, 'info'); } };
        show('Sending...');
        try {
            var r = await api('POST', '/test', { kind: 'invite' });
            if (!r.ok) { show(problem(r)); return; }
            var res = (r.data.results || [])[0] || {};
            if (res.ok) {
                var file = st && st.transport && st.transport.driver === 'file';
                show('Sent to ' + (r.data.to || 'you') + '. Check your inbox (and the spam folder).' + (file ? ' (Test mode: the mail driver is "file", so it was saved in storage/mail and not sent.)' : ''));
                say('Test invitation sent.');
            } else {
                show('Not sent: ' + (res.error || 'the mail server refused it.'));
            }
        } catch (e) {
            show('The connection was lost. Try again.');
        } finally {
            busy.test = false;
            setBusy(btn, false);
        }
    }

    // ------------------------------------------------------------------------------------------------ "send reminder now"
    async function sendReminder(btn) {
        if (busy.reminder) { return; }
        busy.reminder = true;
        setBusy(btn, true);
        var which = btn.getAttribute('data-ma-reminder') === '1h' ? '1h' : '24h';
        var out = $('[data-ma-reminder-status]');
        var show = function (t) { if (out) { out.textContent = t; } else { note(t, 'info'); } };
        try {
            var s = await refreshStatus();
            if (!s) { show('The server could not be reached. Try again in a moment.'); return; }
            var n = +((st.reminders || {})[which === '1h' ? 'h1' : 'h24']) || 0;
            if (st.meeting.status !== 'scheduled') { show('Reminders are only sent for a scheduled meeting.'); return; }
            if (n < 1) { show('Nobody needs this reminder: everyone who was invited and has not declined already has it.'); return; }
            var label = which === '1h' ? '1-hour' : '24-hour';
            var ok = await confirmDialog({
                title: 'Send the ' + label + ' reminder to ' + people(n) + ' now?',
                text: 'Each person who was invited, has not declined and has not had this reminder gets a short e-mail with the Join button. It says the meeting starts in ' + (which === '1h' ? '1 hour' : '24 hours') + ', and it is sent right now whatever the clock says.',
                facts: factsFor('reminder'), ok: 'Send to ' + num(n), cancel: 'Not yet'
            });
            if (!ok) { return; }
            var sent = 0, failed = 0, netFails = 0, busyWaits = 0;
            show('Sending...');
            for (var guard = 0; guard < 60; guard++) {
                var r;
                try {
                    r = await api('POST', '/reminders', { which: which });
                } catch (e) {
                    netFails++;
                    if (netFails >= 3) { show('The connection was lost after ' + num(sent) + ' were sent. Press the button again to send the rest.'); return; }
                    await sleep(2000);
                    continue;
                }
                if (!r.ok) { show(problem(r)); return; }
                var d = r.data;
                if (d.busy) {
                    busyWaits++;
                    if (busyWaits >= 6) { show('Another sender is working on this meeting. Try again in a minute.'); return; }
                    await sleep(3000);
                    continue;
                }
                sent += +d.sent || 0;
                failed += +d.failed || 0;
                if (d.halted) { show('Stopped: ' + d.halted + ' ' + num(sent) + ' reminders were sent.'); return; }
                show('Sent ' + num(sent) + (d.remaining > 0 ? ', ' + num(d.remaining) + ' to go...' : '.'));
                if (!(d.remaining > 0) || (+d.sent === 0 && +d.failed === 0 && +d.skipped === 0)) { break; }
            }
            show('Sent ' + num(sent) + ' ' + plural(sent, 'reminder', 'reminders') + (failed ? '; ' + num(failed) + ' could not be sent.' : '.'));
            say('Reminders sent.');
            await refreshStatus();
        } catch (e) {
            show('The connection was lost. Check the people list before trying again.');
        } finally {
            busy.reminder = false;
            setBusy(btn, false);
        }
    }

    // ------------------------------------------------------------------------------------------------ message to the attendees (chunks of 30)
    async function sendMessage(form) {
        if (busy.message) { return; }
        var text = form.querySelector('[data-ma-msg-text]');
        var subject = form.querySelector('[data-ma-msg-subject]');
        var to = form.querySelector('[data-ma-msg-to]');
        var btn = form.querySelector('[data-ma-msg-send]');
        var out = form.querySelector('[data-ma-msg-status]');
        var show = function (t) { if (out) { out.textContent = t; } else { note(t, 'info'); } };
        if (!text || text.value.trim() === '') { show('Write the message first.'); if (text) { text.focus(); } return; }
        busy.message = true;
        setBusy(btn, true);
        try {
            var s = await refreshStatus(true);
            if (!s) { show('The server could not be reached. Try again in a moment.'); return; }
            if (st.transport && st.transport.blocking) { show(st.transport.message); return; }
            var choice = to ? to.value : 'all';
            // only people who hold an invitation: somebody whose invitation failed has never heard of the meeting
            var list = (st.attendees || []).filter(function (a) {
                if (!a.invite_sent_at) { return false; }
                if (choice === 'all') { return a.rsvp !== 'declined'; }
                return a.rsvp === choice;
            });
            if (list.length === 0) { show('Nobody matches that choice.'); return; }
            var ok = await confirmDialog({
                title: 'Send this message to ' + people(list.length) + '?',
                text: 'Each person gets their own e-mail. Nobody sees anybody else\'s address, and replies go to ' + ((st.sender || {}).reply_to || 'the organiser') + '.',
                facts: [['Meeting', st.meeting.title], ['Subject', subject && subject.value.trim() !== '' ? subject.value.trim() : 'About ' + st.meeting.title], ['Message', text.value.trim().length > 160 ? text.value.trim().slice(0, 157) + '...' : text.value.trim()]],
                ok: 'Send to ' + num(list.length), cancel: 'Not yet'
            });
            if (!ok) { return; }
            var sent = 0, failed = 0, failedNames = [], halted = '';
            for (var i = 0; i < list.length; i += CHUNK) {
                var chunk = list.slice(i, i + CHUNK);
                show('Sending... ' + num(Math.min(i, list.length)) + ' of ' + num(list.length));
                var r;
                try {
                    r = await api('POST', '/message', { subject: subject ? subject.value : '', message: text.value, attendee_ids: chunk.map(function (a) { return a.id; }) });
                } catch (e) {
                    show('The connection was lost after ' + num(sent) + ' were sent. Do not press Send again for them: choose only the people who are left.');
                    return;
                }
                if (!r.ok) {
                    var errs = r.data && r.data.errors ? Object.keys(r.data.errors).map(function (k) { return r.data.errors[k]; }).join(' ') : '';
                    show((sent ? num(sent) + ' were sent. ' : '') + (errs || problem(r)));
                    return;
                }
                (r.data.results || []).forEach(function (x) {
                    if (x.ok) { sent++; } else {
                        failed++;
                        var a = chunk.filter(function (c) { return c.id === x.id; })[0];
                        failedNames.push((a ? (a.name || a.email) : '#' + x.id) + (x.error ? ' (' + x.error + ')' : ''));
                    }
                });
                if (r.data.halted) { halted = r.data.halted; break; }
            }
            var msg = 'Sent to ' + num(sent) + ' ' + plural(sent, 'person', 'people') + '.' + (failed ? ' ' + num(failed) + ' could not be reached: ' + failedNames.slice(0, 5).join('; ') + (failedNames.length > 5 ? '...' : '') : '');
            if (halted) { msg += ' Stopped because of a mail server problem: ' + halted; }
            show(msg);
            say(msg);
            if (failed === 0 && !halted) { text.value = ''; }
        } catch (e) {
            show('The connection was lost. Check the people list before trying again.');
        } finally {
            busy.message = false;
            setBusy(btn, false);
        }
    }

    // ------------------------------------------------------------------------------------------------ wiring (capture phase: handled once, even if another script knows these hooks)
    document.addEventListener('click', function (ev) {
        var t = ev.target && ev.target.closest ? ev.target.closest('[data-ma-queue], [data-ma-test], [data-ma-reminder]') : null;
        if (!t) { return; }
        ev.preventDefault();
        ev.stopImmediatePropagation();
        if (t.hasAttribute('data-ma-queue')) { queueAction(t.getAttribute('data-ma-queue'), t); }
        else if (t.hasAttribute('data-ma-test')) { sendTest(t); }
        else if (t.hasAttribute('data-ma-reminder')) { sendReminder(t); }
    }, true);
    document.addEventListener('submit', function (ev) {
        var f = ev.target;
        if (!f || !f.matches || !f.matches('form[data-ma-message]')) { return; }
        ev.preventDefault();
        ev.stopImmediatePropagation();
        sendMessage(f);
    }, true);
    document.addEventListener('meeting-send:start', function () { start(); });

    window.LuminaMeetingSend = {
        start: start,
        refresh: function () { return refreshStatus(); },
        status: function () { return st; }
    };

    // ------------------------------------------------------------------------------------------------ go
    if (initial && initial.counts) {
        st = { meeting: { status: initial.status || '', send_paused: !!initial.paused }, counts: initial.counts, queue: { waiting: +(initial.counts.queued || 0), failed: +(initial.counts.failed || 0) }, available: {} };
    }
    render();
    var auto = root.getAttribute('data-auto') === '1' || /[?&]send=1(&|$)/.test(location.search);
    refreshStatus().then(function (s) {
        if (!s) { note('The sending status could not be loaded. Reload the page.', 'error'); return; }
        if (auto) { return start(); }
    });
}());
