/*!
 * admin-newsletter-mail.js - the e-mail panel of an issue (ADMIN ONLY): settings, test send, confirm dialog, batch sending loop,
 * pause / resume / cancel / retry, live progress, per-address results, e-mail preview.
 * OWNER: MAILING agent. Contract: docs/NEWSLETTER.md sections 7 and 10 (endpoints under /admin/api/newsletters/{id}/email).
 *
 * How sending works: "Send" asks for confirmation (the exact number of recipients), POSTs /queue {confirm_count}, then this script calls POST /batch
 * again and again; every call sends one batch on the server (size from the settings) and returns the progress. The server holds a lock, so a second
 * browser tab, a second administrator or the optional cron job can never send at the same time; a "busy" answer just means somebody else is sending.
 * Closing the page stops this loop only; the campaign stays queued/sending and can be continued here or by bin/newsletter-send.php.
 *
 * Hooks: [data-nl-mail] (data-api, data-state), [data-nl-act="..."] buttons, [data-nl-*] elements. No inline JS (CSP). Personal data (addresses) is only
 * ever written with textContent.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-nl-mail]');
    if (!root) { return; }

    var $ = function (sel, ctx) { return (ctx || root).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); };
    var base = (root.getAttribute('data-api') || '').replace(/\/$/, '');
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var PER_PAGE = 25;
    var state = {};
    try { state = JSON.parse(root.getAttribute('data-state') || '{}') || {}; } catch (e) { state = {}; }
    if (!state.email) { state.email = { status: 'none' }; }

    var running = false;          // the sending loop is active in THIS page
    var stopRequested = false;
    var noteText = '';            // persistent message under the progress bar
    var noteKind = '';
    var busyAct = {};             // per action double-click guard
    var results = { status: '', q: '', page: 1, pages: 1, timer: 0, loading: false, last: 0 };
    var settingsDirty = false;

    // ------------------------------------------------------------------ helpers
    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function plural(n, one, many) { return (n === 1 ? one : many); }
    function num(n) { try { return Number(n || 0).toLocaleString('en-GB'); } catch (e) { return String(n || 0); } }
    function sleep(ms) { return new Promise(function (res) { window.setTimeout(res, ms); }); }
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    // 'YYYY-MM-DD HH:MM:SS' (server time) -> '17 Oct 2026, 14:05'
    function when(s, short) {
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(s || '');
        if (!m) { return ''; }
        return (+m[3]) + ' ' + MONTHS[+m[2] - 1] + (short ? '' : ' ' + m[1]) + ', ' + m[4] + ':' + m[5];
    }
    function say(msg) {
        var live = $('[data-nl-live]');
        if (live) { live.textContent = ''; window.setTimeout(function () { live.textContent = msg; }, 30); }
    }
    function setBusy(btn, on) {
        if (!btn) { return; }
        btn.disabled = on;
        if (on) { btn.setAttribute('aria-busy', 'true'); } else { btn.removeAttribute('aria-busy'); }
    }
    function toneFor(status) {
        return { queued: 'info', sending: 'info', paused: 'warning', sent: 'success', cancelled: 'danger', none: 'neutral' }[status] || 'neutral';
    }

    // ------------------------------------------------------------------ API
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
        if (r.status === 404) { return d.message || 'That issue no longer exists.'; }
        if (r.status >= 500) { return 'The server had a problem (error ' + r.status + '). Nothing is lost: wait a moment and try again.'; }
        return d.message || 'That did not work (error ' + r.status + ').';
    }

    function setState(next) {
        if (!next || !next.email) { return; }
        state = Object.assign({}, state, next);
        render();
    }
    function refreshStatus() {
        return api('GET', '').then(function (r) { if (r.ok) { setState(r.data); } return r; }).catch(function () { return null; });
    }
    function applyBatch(d) {
        if (!d || !d.totals) { return; }
        var e = Object.assign({}, state.email);
        e.status = d.status || e.status;
        e.status_label = { none: 'Not sent', queued: 'Queued', sending: 'Sending', paused: 'Paused', sent: 'Sent', cancelled: 'Cancelled' }[e.status] || e.status;
        e.total = d.totals.total; e.sent = d.totals.sent; e.failed = d.totals.failed; e.queued = d.totals.queued; e.skipped = d.totals.skipped;
        state = Object.assign({}, state, { email: e });
        render();
    }
    function note(msg, kind) { noteText = msg || ''; noteKind = kind || ''; renderNote(); }

    // ------------------------------------------------------------------ rendering
    var els = {
        campaign: $('[data-nl-campaign]'), badge: $('[data-nl-state]'), headline: $('[data-nl-headline]'), progress: $('[data-nl-progress]'),
        bar: $('[data-nl-progress-bar]'), note: $('[data-nl-note]'), times: $('[data-nl-times]'),
        send: $('[data-nl-send]'), sendBtn: $('[data-nl-act="send"]'), sendLabel: $('[data-nl-send-label]'), why: $('[data-nl-why]'),
        settings: $('[data-nl-settings]'), fields: $('[data-nl-settings-fields]'), locked: $('[data-nl-locked]'), settingsMsg: $('[data-nl-settings-msg]'),
        transport: $('[data-nl-transport]'), transportText: $('[data-nl-transport-text]'),
        results: $('[data-nl-results]')
    };

    function renderNote() {
        if (!els.note) { return; }
        els.note.textContent = noteText;
        els.note.hidden = noteText === '';
        els.note.className = 'nl-campaign__note' + (noteKind ? ' is-' + noteKind : '');
    }

    function render() {
        var e = state.email || {};
        var st = e.status || 'none';
        var total = +e.total || 0, sent = +e.sent || 0, failed = +e.failed || 0, queued = +e.queued || 0, skipped = +e.skipped || 0;
        var active = st === 'queued' || st === 'sending';

        // campaign card
        if (els.campaign) {
            els.campaign.hidden = st === 'none';
            if (els.badge) {
                els.badge.className = 'badge badge--' + toneFor(st) + ' badge--dot';
                els.badge.textContent = running ? 'Sending' : (e.status_label || st);
            }
            var head = '';
            if (active && running) { head = 'Sending: ' + num(sent) + ' of ' + num(total) + ' sent' + (failed ? ', ' + num(failed) + ' failed' : '') + '.'; }
            else if (active) { head = 'Not sending from this page right now: ' + num(sent) + ' of ' + num(total) + ' sent so far. Press "Continue sending" to go on (the scheduled job, if you set it up, also continues).'; }
            else if (st === 'paused') { head = 'Paused: ' + num(sent) + ' of ' + num(total) + ' sent so far.'; }
            else if (st === 'sent') { head = 'Finished: ' + num(sent) + ' of ' + num(total) + ' ' + plural(total, 'e-mail was', 'e-mails were') + ' sent' + (failed ? ', ' + num(failed) + ' failed' : '') + (skipped ? ', ' + num(skipped) + ' skipped' : '') + '.'; }
            else if (st === 'cancelled') { head = 'Cancelled: ' + num(sent) + ' of ' + num(total) + ' had been sent.'; }
            if (els.headline) { els.headline.textContent = head; }
            var pct = total > 0 ? Math.round(((total - queued) / total) * 100) : 0;
            if (st === 'sent') { pct = 100; }
            if (els.progress) { els.progress.setAttribute('aria-valuenow', String(pct)); els.progress.setAttribute('aria-valuetext', pct + ' percent done'); }
            if (els.bar) { els.bar.style.width = pct + '%'; els.bar.className = 'nl-progress__bar' + (st === 'sent' ? ' is-done' : '') + (failed ? ' has-failed' : ''); }
            $$('[data-nl-count]').forEach(function (n) { n.textContent = num(e[n.getAttribute('data-nl-count')] || 0); });
            var rc = $('[data-nl-retry-count]'); if (rc) { rc.textContent = num(failed); }
            if (els.times) {
                var bits = [];
                if (e.started_at) { bits.push('Started ' + when(e.started_at)); }
                if (e.completed_at) { bits.push((st === 'cancelled' ? 'Cancelled ' : 'Finished ') + when(e.completed_at)); }
                els.times.textContent = bits.join(' - ');
            }
            // buttons
            var show = {
                'continue': active && !running,
                'pause': active,
                'resume': st === 'paused',
                'retry': failed > 0 && (st === 'sent' || st === 'paused' || active),
                'cancel': active || st === 'paused'
            };
            Object.keys(show).forEach(function (k) {
                var b = $('[data-nl-act="' + k + '"]'); if (b) { b.hidden = !show[k]; }
            });
        }
        renderNote();

        // settings
        if (els.fields) {
            var canEdit = state.can_edit !== false;
            els.fields.disabled = !canEdit;
            var save = $('[data-nl-act="save-settings"]'); if (save) { save.disabled = !canEdit || !!busyAct.save; }
            if (els.locked) { els.locked.hidden = canEdit; }
            var def = $('[data-nl-default-subject]'); if (def && e.default_subject) { def.textContent = e.default_subject; }
            var subj = $('#nl-subject'); if (subj && e.default_subject) { subj.placeholder = e.default_subject; }
        }

        // transport notice
        if (els.transport && state.transport) {
            var t = state.transport;
            els.transport.hidden = t.level === 'ok';
            els.transport.className = 'alert alert--' + (t.level === 'error' ? 'error' : (t.level === 'warning' ? 'warning' : 'info'));
            if (els.transportText) { els.transportText.textContent = t.message; }
        }

        // send card
        if (els.send) {
            els.send.hidden = !(st === 'none' || st === 'cancelled');
            var checks = { live: !!state.live, enabled: !!e.enabled, transport: !(state.transport && state.transport.blocking), recipients: (state.recipients || 0) > 0 };
            $$('[data-nl-check-item]').forEach(function (li) {
                var ok = !!checks[li.getAttribute('data-nl-check-item')];
                li.className = ok ? 'is-ok' : 'is-todo';
                var ic = $('.nl-check__icon', li);
                if (ic) { ic.textContent = ok ? '✓' : '!'; }
                var sr = $('.visually-hidden', li);
                if (!sr) { sr = document.createElement('span'); sr.className = 'visually-hidden'; li.insertBefore(sr, li.firstChild); }
                sr.textContent = ok ? 'Done: ' : 'To do: ';
            });
            var rt = $('[data-nl-recipients-text]');
            if (rt) {
                var n = +state.recipients || 0;
                rt.textContent = n > 0
                    ? (st === 'cancelled' ? num(n) + ' ' + plural(n, 'subscriber has', 'subscribers have') + ' not received it yet' : num(n) + ' ' + plural(n, 'subscriber', 'subscribers') + ' will receive it')
                    : (st === 'cancelled' ? 'Nobody is left to send to' : 'There are no subscribers to send to yet');
            }
            if (els.sendLabel) {
                var rn = +state.recipients || 0;
                els.sendLabel.textContent = rn > 0 ? 'Send to ' + num(rn) + ' ' + plural(rn, 'subscriber', 'subscribers') + '...' : 'Send to the subscribers';
            }
            if (els.sendBtn) { els.sendBtn.disabled = !state.can_start || !!busyAct.queue; }
            if (els.why) {
                var reason = state.can_start ? '' : (state.cannot_start_reason || '');
                els.why.textContent = reason; els.why.hidden = reason === '';
            }
        }

        // results card
        if (els.results) {
            els.results.hidden = !(+e.total > 0);
            var counts = { all: total, sent: sent, failed: failed, queued: queued, skipped: skipped };
            $$('[data-nl-filter-count]').forEach(function (s) { s.textContent = num(counts[s.getAttribute('data-nl-filter-count')] || 0); });
        }
        var sb = $('[data-nl-batch-size]'); if (sb && state.batch_size) { sb.textContent = String(state.batch_size); }
    }

    // ------------------------------------------------------------------ the sending loop
    async function loop(startNote) {
        if (running) { return; }
        running = true; stopRequested = false; note(startNote || '', startNote ? 'info' : '');
        render();
        say('Sending started.');
        var netFails = 0, busyWaits = 0, idleSpins = 0;
        try {
            while (!stopRequested) {
                var r;
                try {
                    r = await api('POST', '/batch');
                } catch (err) {
                    netFails++;
                    if (netFails >= 5) { note('The connection was lost. Nothing is lost: press "Continue sending" when you are online again.', 'error'); break; }
                    note('Connection problem, trying again...', 'warn');
                    await sleep(Math.min(30000, 2000 * Math.pow(2, netFails - 1)));
                    continue;
                }
                netFails = 0;
                if (!r.ok) { note(problem(r), 'error'); break; }
                var d = r.data || {};
                applyBatch(d);
                scheduleResults(false);
                if (d.halted === 'mail_server') {
                    note(d.halt_reason
                        ? 'The mail server settings need attention, so the campaign was paused before anyone else was affected: ' + d.halt_reason + ' Fix the settings in the .env file, then press Resume.'
                        : 'The mail server kept refusing messages for the moment (it may be down or limiting how fast you can send), so the campaign was paused. Check the results below, then press Resume.', 'error');
                    say('Sending paused because the mail server is not accepting messages.');
                    break;
                }
                if (d.halted === 'not_live') {
                    note('This issue is not published any more, so sending was paused. Publish it again, then press Resume.', 'error');
                    break;
                }
                if (d.status !== 'queued' && d.status !== 'sending') { break; }        // finished, paused or cancelled
                if (d.busy) {
                    busyWaits++;
                    if (busyWaits >= 10) { note('Another sender (a second page, another administrator or the scheduled job) is sending right now. It will carry on without this page.', 'warn'); break; }
                    note('Another sender is working on this campaign, waiting...', 'warn');
                    await sleep(3000);
                    continue;
                }
                busyWaits = 0;
                if (+d.processed === 0) {
                    idleSpins++;
                    if (idleSpins > 40) { note('Nothing could be sent for a while. Check the results below, then press "Continue sending".', 'warn'); break; }
                    note('Waiting to try the temporary failures again...', 'warn');
                    await sleep(Math.max(2000, Math.min(30, +d.retry_in || 5) * 1000));
                    continue;
                }
                idleSpins = 0;
                if (noteKind === 'warn') { note(''); }
                await sleep(reduced ? 400 : 250);
            }
        } finally {
            running = false;
            await refreshStatus();
            scheduleResults(true);
            render();
            var st = (state.email && state.email.status) || '';
            if ((st === 'sent' || st === 'cancelled') && noteKind !== 'error') { note(''); }
            if (st === 'sent') { say('Finished. ' + num(state.email.sent) + ' e-mails sent.'); }
            else if (st === 'paused') { say('Sending paused.'); }
            else if (st === 'cancelled') { say('Sending cancelled.'); }
        }
    }

    window.addEventListener('beforeunload', function (e) {
        if (running) { e.preventDefault(); e.returnValue = ''; }
    });

    // ------------------------------------------------------------------ results table
    function scheduleResults(now) {
        window.clearTimeout(results.timer);
        var wait = now ? 0 : Math.max(0, 2000 - (Date.now() - results.last));
        results.timer = window.setTimeout(loadResults, wait);
    }
    async function loadResults() {
        if (!els.results || els.results.hidden && !(state.email && +state.email.total > 0)) { return; }
        if (results.loading) { scheduleResults(false); return; }
        results.loading = true;
        try {
            var qs = '?page=' + results.page + '&per_page=' + PER_PAGE + (results.status ? '&status=' + encodeURIComponent(results.status) : '') + (results.q ? '&q=' + encodeURIComponent(results.q) : '');
            var r = await api('GET', '/deliveries' + qs);
            results.last = Date.now();
            if (!r.ok) { return; }
            var d = r.data;
            results.pages = d.meta.pages; results.page = d.meta.page;
            var body = $('[data-nl-rows]');
            body.textContent = '';
            d.data.forEach(function (row) {
                var tr = document.createElement('tr');
                var cell = function (label, text, cls) {
                    var td = document.createElement('td');
                    td.setAttribute('data-label', label);
                    if (cls) { td.className = cls; }
                    td.textContent = text;
                    tr.appendChild(td);
                    return td;
                };
                cell('Address', row.email, 'cell-title nl-addr');
                var st = document.createElement('td');
                st.setAttribute('data-label', 'Result');
                var b = document.createElement('span');
                var tone = { sent: 'success', failed: 'danger', queued: 'info', skipped: 'neutral' }[row.status] || 'neutral';
                b.className = 'badge badge--' + tone + ' badge--dot';
                b.textContent = { sent: 'Sent', failed: 'Failed', queued: 'Waiting', skipped: 'Skipped' }[row.status] || row.status;
                st.appendChild(b); tr.appendChild(st);
                cell('Tries', String(row.attempts), 'col-num');
                cell('Details', row.last_error || '', 'nl-detail');
                cell('Time', row.sent_at ? when(row.sent_at, true) : '', 'nl-time');
                body.appendChild(tr);
            });
            var empty = $('[data-nl-results-empty]'); if (empty) { empty.hidden = d.data.length > 0; }
            var cnt = $('[data-nl-results-count]'); if (cnt) { cnt.textContent = num(d.meta.total) + ' ' + plural(d.meta.total, 'address', 'addresses'); }
            var pager = $('[data-nl-pager]');
            if (pager) {
                pager.hidden = d.meta.pages <= 1;
                $('[data-nl-page-text]').textContent = 'Page ' + d.meta.page + ' of ' + d.meta.pages;
                $('[data-nl-act="page-prev"]').disabled = d.meta.page <= 1;
                $('[data-nl-act="page-next"]').disabled = d.meta.page >= d.meta.pages;
            }
            // keep the filter counts exact even for rows outside the current page
            var c = d.meta.counts || {};
            $$('[data-nl-filter-count]').forEach(function (s) { var k = s.getAttribute('data-nl-filter-count'); if (c[k] !== undefined) { s.textContent = num(c[k]); } });
        } catch (err) {
            /* the table is a convenience; the next refresh will try again */
        } finally {
            results.loading = false;
        }
    }

    // ------------------------------------------------------------------ dialogs
    function dialog(name) { return $('[data-nl-dialog="' + name + '"]'); }
    function openDialog(name) {
        var d = dialog(name);
        if (!d) { return false; }
        if (typeof d.showModal === 'function') { if (!d.open) { d.showModal(); } return true; }
        return false;
    }
    function closeDialog(name) {
        var d = dialog(name);
        if (d && d.open && typeof d.close === 'function') { d.close(); }
    }
    $$('[data-nl-dialog]').forEach(function (d) {
        d.addEventListener('click', function (ev) {
            if (ev.target === d) { d.close(); }                                   // click on the backdrop
            if (ev.target.closest && ev.target.closest('[data-nl-dialog-close]')) { d.close(); }
        });
    });
    function fill(name, text) { $$('[data-nl-fill="' + name + '"]').forEach(function (n) { n.textContent = text; }); }

    // ------------------------------------------------------------------ actions
    var shownCount = 0;

    function openSend() {
        var n = +state.recipients || 0;
        shownCount = n;
        fillSendDialog(n);
        var warn = $('[data-nl-fill-warn]');
        if (warn) {
            var drv = state.driver;
            warn.hidden = drv === 'smtp';
            warn.textContent = drv === 'file' ? 'Test mode: the mail driver is "file", so nothing leaves this server. The messages are saved in storage/mail.' : (drv === 'mail' ? 'The mail driver is PHP mail(); delivery to inboxes is less reliable than with SMTP.' : '');
        }
        var ok = $('[data-nl-dialog-ok="send"]'); setBusy(ok, false);
        if (!openDialog('send')) {
            if (window.confirm('Send this issue to ' + n + ' subscribers? This cannot be undone.')) { doSend(); }
        }
    }
    function fillSendDialog(n) {
        fill('count', num(n));
        fill('people', plural(n, 'subscriber', 'subscribers'));
        fill('issue', (document.querySelector('.page-header__subtitle') || {}).textContent || '');
        fill('subject', (state.email && state.email.subject_used) || '');
        var s = state.sender || {};
        fill('from', (s.from_name ? s.from_name + ' ' : '') + '<' + (s.from || '') + '>');
    }

    async function doSend() {
        if (busyAct.queue) { return; }
        busyAct.queue = true;
        var ok = $('[data-nl-dialog-ok="send"]'); setBusy(ok, true);
        render();
        try {
            var r = await api('POST', '/queue', { confirm_count: shownCount });
            if (r.ok) {
                closeDialog('send');
                setState(r.data);
                results.page = 1; results.status = ''; results.q = '';
                busyAct.queue = false;
                scheduleResults(true);
                loop();
                return;
            }
            var warn = $('[data-nl-fill-warn]');
            if (r.status === 409 && r.data && r.data.reason === 'count_changed') {
                shownCount = +r.data.recipients || 0;
                state.recipients = shownCount;
                fillSendDialog(shownCount);
                if (warn) { warn.hidden = false; warn.textContent = r.data.message; }
                setBusy(ok, false);
            } else {
                if (warn) { warn.hidden = false; warn.textContent = problem(r); }
                setBusy(ok, false);
                await refreshStatus();
                if (state.email && state.email.status !== 'none' && state.email.status !== 'cancelled') { closeDialog('send'); }
            }
        } catch (err) {
            var w = $('[data-nl-fill-warn]');
            if (w) { w.hidden = false; w.textContent = 'The connection was lost before the server answered. Check the progress above before trying again.'; }
            setBusy(ok, false);
        } finally {
            busyAct.queue = false;
            render();
        }
    }

    async function control(action, okMessage) {
        if (busyAct[action]) { return; }
        busyAct[action] = true;
        var btn = $('[data-nl-act="' + action + '"]'); setBusy(btn, true);
        try {
            if (action === 'pause' || action === 'cancel') { stopRequested = true; }
            var r = await api('POST', '/' + action);
            if (!r.ok) {
                note(problem(r), 'error');
                stopRequested = false;
                await refreshStatus();
                return;
            }
            setState(r.data);
            note('');
            say(okMessage);
            scheduleResults(true);
            if (action === 'resume') { busyAct[action] = false; setBusy(btn, false); loop(); return; }
            if (action === 'retry') {
                var again = 'Trying the ' + num(r.data.requeued || 0) + ' failed ' + plural(+r.data.requeued || 0, 'e-mail', 'e-mails') + ' again.';
                busyAct[action] = false; setBusy(btn, false);
                if (state.email.status === 'sending' || state.email.status === 'queued') { loop(again); } else { note(again + ' Press Resume to send them.', 'info'); }
            }
        } catch (err) {
            note('The connection was lost. Reload the page to see where the campaign stands.', 'error');
            stopRequested = false;
        } finally {
            busyAct[action] = false;
            setBusy(btn, false);
            render();
        }
    }

    async function saveSettings(ev) {
        ev.preventDefault();
        if (busyAct.save || !els.settings) { return; }
        busyAct.save = true;
        var btn = $('[data-nl-act="save-settings"]'); setBusy(btn, true);
        $$('[data-nl-error]', els.settings).forEach(function (n) { n.hidden = true; n.textContent = ''; });
        var f = els.settings.elements;
        try {
            var r = await api('PUT', '', { email_enabled: !!f.email_enabled.checked, email_subject: f.email_subject.value, email_preheader: f.email_preheader.value });
            if (r.ok) {
                setState(r.data);
                settingsDirty = false;
                var d = new Date();
                els.settingsMsg.textContent = 'Saved at ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
                say('E-mail settings saved.');
            } else if (r.status === 422 && r.data && r.data.errors) {
                Object.keys(r.data.errors).forEach(function (k) {
                    var box = $('[data-nl-error="' + k + '"]', els.settings);
                    if (box) { box.textContent = r.data.errors[k]; box.hidden = false; }
                });
                els.settingsMsg.textContent = 'Please check the highlighted fields.';
            } else {
                els.settingsMsg.textContent = problem(r);
                await refreshStatus();
            }
        } catch (err) {
            els.settingsMsg.textContent = 'The connection was lost. Your changes are still in the form: try Save again.';
        } finally {
            busyAct.save = false;
            setBusy(btn, false);
            render();
        }
    }

    async function sendTest(ev) {
        ev.preventDefault();
        var form = ev.currentTarget;
        if (busyAct.test) { return; }
        busyAct.test = true;
        var btn = $('[data-nl-act="send-test"]'); setBusy(btn, true);
        var err = $('[data-nl-error="emails"]', form); err.hidden = true; err.textContent = '';
        var list = $('[data-nl-test-results]', form); list.textContent = '';
        try {
            var r = await api('POST', '/test', { emails: form.elements.emails.value });
            if (r.ok) {
                r.data.results.forEach(function (x) {
                    var li = document.createElement('li');
                    li.className = x.ok ? 'is-ok' : 'is-fail';
                    var strong = document.createElement('strong');
                    strong.textContent = x.email;
                    li.appendChild(strong);
                    li.appendChild(document.createTextNode(x.ok ? ': test e-mail sent.' : ': not sent. ' + (x.error || '')));
                    list.appendChild(li);
                });
                say(r.data.sent + ' of ' + r.data.results.length + ' test e-mails sent.');
            } else {
                err.textContent = (r.data && r.data.errors && r.data.errors.emails) || problem(r);
                err.hidden = false;
            }
        } catch (e) {
            err.textContent = 'The connection was lost. Try again.';
            err.hidden = false;
        } finally {
            busyAct.test = false;
            setBusy(btn, false);
        }
    }

    async function openPreview() {
        var frame = $('[data-nl-frame]');
        var stage = $('[data-nl-stage]');
        if (!frame) { return; }
        fill('p-subject', 'Loading...'); fill('p-preheader', '');
        var warn = $('[data-nl-fill="p-warn"]'); warn.hidden = true;
        frame.srcdoc = '';
        if (!openDialog('preview')) { window.open(base.replace(/\/api\/.*$/, '/') , '_self'); return; }
        try {
            var r = await api('GET', '?preview=1');
            if (!r.ok || !r.data.preview) { fill('p-subject', problem(r)); return; }
            var p = r.data.preview;
            fill('p-subject', p.subject);
            fill('p-preheader', p.preheader || '(none)');
            frame.srcdoc = p.html;
            var msgs = [];
            if (p.bytes > 102400) { msgs.push('This e-mail is ' + Math.round(p.bytes / 1024) + ' KB. Gmail cuts off the end of messages larger than 102 KB ("Message clipped"), so the unsubscribe link may be hidden. Shorten the issue or use smaller text blocks.'); }
            if (settingsDirty) { msgs.push('You have unsaved changes in the settings; this preview shows the saved version.'); }
            warn.hidden = msgs.length === 0; warn.textContent = msgs.join(' ');
        } catch (e) {
            fill('p-subject', 'The preview could not be loaded.');
        }
        if (stage) { stage.scrollTop = 0; }
    }

    // ------------------------------------------------------------------ wiring
    root.addEventListener('click', function (ev) {
        var t = ev.target.closest ? ev.target.closest('[data-nl-act], [data-nl-filter], [data-nl-dialog-ok], [data-nl-width]') : null;
        if (!t || !root.contains(t)) { return; }
        var act = t.getAttribute('data-nl-act');
        if (act === 'send') { openSend(); }
        else if (act === 'continue') { loop(); }
        else if (act === 'pause') { control('pause', 'Sending paused.'); }
        else if (act === 'resume') { control('resume', 'Sending resumed.'); }
        else if (act === 'retry') { control('retry', 'Failed e-mails queued again.'); }
        else if (act === 'cancel') {
            fill('waiting', num((state.email && state.email.queued) || 0));
            if (!openDialog('cancel') && window.confirm('Cancel sending? The e-mails that are still waiting will not be sent.')) { control('cancel', 'Sending cancelled.'); }
        }
        else if (act === 'preview') { openPreview(); }
        else if (act === 'page-prev') { results.page = Math.max(1, results.page - 1); scheduleResults(true); }
        else if (act === 'page-next') { results.page = Math.min(results.pages, results.page + 1); scheduleResults(true); }
        else if (t.hasAttribute('data-nl-filter')) {
            results.status = t.getAttribute('data-nl-filter'); results.page = 1;
            $$('[data-nl-filter]').forEach(function (b) { var on = b === t; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
            scheduleResults(true);
        }
        else if (t.getAttribute('data-nl-dialog-ok') === 'send') { doSend(); }
        else if (t.getAttribute('data-nl-dialog-ok') === 'cancel') { closeDialog('cancel'); control('cancel', 'Sending cancelled.'); }
        else if (t.hasAttribute('data-nl-width')) {
            var mobile = t.getAttribute('data-nl-width') === 'mobile';
            var st = $('[data-nl-stage]'); if (st) { st.classList.toggle('is-mobile', mobile); }
            $$('[data-nl-width]').forEach(function (b) { var on = b === t; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        }
    });

    if (els.settings) {
        els.settings.addEventListener('submit', saveSettings);
        var markDirty = function () { settingsDirty = true; if (els.settingsMsg) { els.settingsMsg.textContent = 'Unsaved changes'; } };
        els.settings.addEventListener('input', markDirty);
        els.settings.addEventListener('change', markDirty);
    }
    var testForm = $('[data-nl-test]');
    if (testForm) { testForm.addEventListener('submit', sendTest); }

    var searchBox = $('[data-nl-search]');
    if (searchBox) {
        var searchTimer = 0;
        searchBox.addEventListener('input', function () {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function () { results.q = searchBox.value.trim(); results.page = 1; scheduleResults(true); }, 300);
        });
    }

    render();
    refreshStatus().then(function () { scheduleResults(true); });
}());
