/* LAF Academy: exam rules in the browser (docs/ACADEMY.md, "Exam integrity").
   The SERVER is the authority: it stores the deadline, the saved answers, the leave log and the one-window lock, and grades what it has.
   This script adds what only the browser can do:
   - a countdown (resynced from the server on every call), announced to screen readers at 5 minutes and 1 minute, and an automatic submit at zero;
   - autosave of the answers every few seconds and a heartbeat that keeps the "one window only" lock alive;
   - copy, cut, paste, select-all, drag, right-click, print and the usual shortcuts are blocked with a friendly notice;
   - leaving the page (another tab, window or app, or leaving full screen) is reported to the server: warning, final warning, automatic submit.
   A browser cannot be made tamper-proof (a learner can switch scripts off, use a second device, or photograph the screen); these rules deter and record. */
(function () {
  'use strict';

  var root = document.querySelector('[data-ex]');
  if (!root) return;
  var form = root.querySelector('[data-ex-form]');
  if (!form) return;

  var syncUrl = root.getAttribute('data-ex-sync');
  var token = root.getAttribute('data-ex-token');
  var csrf = root.getAttribute('data-ex-csrf') || '';
  var maxLeaves = parseInt(root.getAttribute('data-ex-max'), 10) || 3;
  var leaves = parseInt(root.getAttribute('data-ex-leaves'), 10) || 0;
  var resultUrl = form.getAttribute('action');

  var timerEl = root.querySelector('[data-ex-timer]');
  var timeEl = root.querySelector('[data-ex-time]');
  var announceEl = root.querySelector('[data-ex-announce]');
  var savedEl = root.querySelector('[data-ex-saved]');
  var toastEl = root.querySelector('[data-ex-toast]');
  var warnEl = root.querySelector('[data-ex-warn]');
  var lockedEl = root.querySelector('[data-ex-locked]');
  var confirmEl = root.querySelector('[data-ex-confirm]');
  var reasonEl = form.querySelector('[data-ex-reason]');
  var fsBtn = root.querySelector('[data-ex-fs]');
  var questions = form.querySelectorAll('[data-ac-question]');

  var submitted = false;      // the form is being sent: stop watching
  var locked = false;         // another window holds the exam
  var inLeave = false;
  var leaveStart = 0;
  var dirty = false;
  var fsWas = false;
  var announced = {};
  var endAt = null;
  var pending = [];           // events that could not be sent yet (offline)
  var lastCopyEvent = 0;
  var toastTimer = null;

  if (timerEl) endAt = Date.now() + (parseInt(timerEl.getAttribute('data-ex-timer'), 10) || 0) * 1000;

  // ---------------------------------------------------------------------------------------------------------- helpers
  var pad = function (n) { return (n < 10 ? '0' : '') + n; };
  var fmt = function (s) { return pad(Math.floor(s / 60)) + ':' + pad(s % 60); };
  var remaining = function () { return endAt === null ? null : Math.max(0, Math.ceil((endAt - Date.now()) / 1000)); };

  var toast = function (msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.hidden = false;
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(function () { toastEl.hidden = true; }, 4000);
  };

  var collect = function () {
    var out = {};
    questions.forEach(function (q) {
      Array.prototype.forEach.call(q.querySelectorAll('input:checked'), function (inp) {
        var m = /^answer\[(\d+)\]/.exec(inp.name);
        if (m) { (out[m[1]] = out[m[1]] || []).push(parseInt(inp.value, 10)); }
      });
    });
    return out;
  };

  var answeredCount = function () {
    var n = 0;
    questions.forEach(function (q) { if (q.querySelector('input:checked')) n++; });
    return n;
  };

  var show = function (el) { if (el) { el.hidden = false; var b = el.querySelector('button, a'); if (b) b.focus(); } };
  var hide = function (el) { if (el) el.hidden = true; };

  // ---------------------------------------------------------------------------------------------------------- talking to the server
  var send = function (fields, keepalive) {
    if (locked && !fields.event) return Promise.resolve(null);
    var body = new URLSearchParams();
    body.append('_token', csrf);
    body.append('token', token);
    Object.keys(fields).forEach(function (k) { body.append(k, fields[k]); });
    return fetch(syncUrl, {
      method: 'POST', body: body, credentials: 'same-origin', keepalive: !!keepalive,
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) { return r.json().catch(function () { return null; }); }).then(function (res) {
      if (res) handle(res);
      return res;
    }).catch(function () { if (savedEl) savedEl.textContent = 'Not saved yet - reconnecting...'; return null; });
  };

  var handle = function (res) {
    if (res.status === 'submitted') {
      submitted = true;
      window.location.assign(resultUrl);
      return;
    }
    if (res.status === 'locked') {
      locked = true;
      show(lockedEl);
      return;
    }
    if (typeof res.left === 'number' && endAt !== null) endAt = Date.now() + res.left * 1000;     // the server clock wins
    if (typeof res.leaves === 'number') leaves = res.leaves;          // the server count is the real one
    if (savedEl) { var d = new Date(); savedEl.textContent = 'Saved ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds()); }
  };

  var flush = function () {
    var batch = pending.splice(0, pending.length);
    batch.forEach(function (f) { send(f, true); });
  };

  var sendEvent = function (event, extra) {
    var f = { event: event };
    Object.keys(extra || {}).forEach(function (k) { f[k] = extra[k]; });
    send(f, true).then(function (res) { if (res === null && !submitted && !locked) pending.push(f); });
  };

  var save = function () {
    var f = {};
    if (dirty) { f.answers = JSON.stringify(collect()); dirty = false; }
    send(f, false).then(function (res) { if (res === null && f.answers) dirty = true; });
  };

  // ---------------------------------------------------------------------------------------------------------- the countdown
  var announce = function (msg) { if (announceEl) { announceEl.textContent = ''; window.setTimeout(function () { announceEl.textContent = msg; }, 50); } };

  var autoSubmit = function (reason) {
    if (submitted) return;
    submitted = true;
    if (reasonEl) reasonEl.value = reason;
    form.setAttribute('data-ac-timeup', '1');
    hide(warnEl); hide(confirmEl);
    form.submit();
  };

  var tick = function () {
    var r = remaining();
    if (r === null || submitted) return;
    if (timeEl) timeEl.textContent = fmt(r);
    if (timerEl) timerEl.classList.toggle('is-low', r <= 60);
    if (r <= 300 && r > 60 && !announced.five) { announced.five = true; announce('5 minutes left.'); }
    if (r <= 60 && r > 0 && !announced.one) { announced.one = true; announced.five = true; announce('1 minute left. Your answers will be sent automatically when the time is over.'); }
    if (r <= 0) { announce('Time is over. Sending your answers.'); autoSubmit('time'); }
    var wt = warnEl && warnEl.querySelector('[data-ex-warn-time]');
    if (wt) wt.textContent = fmt(r);
  };

  // ---------------------------------------------------------------------------------------------------------- leaving the page
  var warn = function () {
    if (!warnEl || submitted || locked) return;
    var left = Math.max(0, maxLeaves - leaves);
    var title = warnEl.querySelector('[data-ex-warn-title]');
    var text = warnEl.querySelector('[data-ex-warn-text]');
    var final = left <= 1;
    if (title) title.textContent = final ? 'Final warning' : 'Warning: you left the exam page';
    if (text) {
      text.textContent = 'You left the exam page (' + leaves + ' of ' + maxLeaves + '). The clock kept running. ' +
        (left <= 1 ? 'If you leave once more, the exam is submitted automatically with your answers so far and flagged for review.' :
          'If you leave ' + left + ' more times, the exam is submitted automatically with your answers so far and flagged for review.');
    }
    tick();
    show(warnEl);
  };

  var onLeave = function (kind, event) {
    if (submitted || locked || inLeave) return;
    inLeave = true;
    leaveStart = Date.now();
    leaves += 1;
    sendEvent(event || 'leave', { detail: kind });
  };

  var onReturn = function () {
    if (!inLeave) return;
    inLeave = false;
    sendEvent('return', { away: Math.round((Date.now() - leaveStart) / 1000) });
    if (!submitted) warn();
  };

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') onLeave('Switched to another tab or window'); else onReturn();
  });
  window.addEventListener('blur', function () { onLeave('The exam window lost focus'); });
  window.addEventListener('focus', function () { if (document.visibilityState !== 'hidden') onReturn(); });

  window.addEventListener('pagehide', function () {
    if (submitted || locked) return;
    var fd = new FormData();
    fd.append('_token', csrf); fd.append('token', token); fd.append('event', 'release');
    if (navigator.sendBeacon) navigator.sendBeacon(syncUrl, fd);     // lets a refresh take the exam again at once
  });
  window.addEventListener('pageshow', function (e) { if (e.persisted) window.location.reload(); });

  // full screen (optional)
  var fsEl = function () { return document.fullscreenElement || document.webkitFullscreenElement || null; };
  if (fsBtn && (document.documentElement.requestFullscreen || document.documentElement.webkitRequestFullscreen)) {
    fsBtn.hidden = false;
    fsBtn.addEventListener('click', function () {
      var el = document.documentElement;
      var p = (el.requestFullscreen || el.webkitRequestFullscreen).call(el);
      if (p && p.catch) p.catch(function () { toast('Full screen is not available here. You can continue without it.'); });
    });
  }
  var fsChange = function () {
    var on = !!fsEl();
    if (fsBtn) fsBtn.hidden = on;
    if (fsWas && !on && !submitted) onLeave('Left full screen', 'fullscreen_exit');
    if (on && fsWas === false && inLeave) onReturn();
    fsWas = on;
  };
  document.addEventListener('fullscreenchange', fsChange);
  document.addEventListener('webkitfullscreenchange', fsChange);

  // ---------------------------------------------------------------------------------------------------------- no copy and paste
  var blocked = function (e, what) {
    e.preventDefault();
    toast(what + ' is switched off during the exam.');
    var now = Date.now();
    if (now - lastCopyEvent > 30000) { lastCopyEvent = now; sendEvent('copy_blocked', { detail: what }); }
  };
  ['copy', 'cut', 'paste', 'drop'].forEach(function (t) {
    document.addEventListener(t, function (e) { blocked(e, t === 'drop' ? 'Dropping text' : (t.charAt(0).toUpperCase() + t.slice(1))); }, true);
  });
  document.addEventListener('contextmenu', function (e) { blocked(e, 'The right-click menu'); }, true);
  document.addEventListener('dragstart', function (e) { e.preventDefault(); }, true);
  document.addEventListener('selectstart', function (e) {
    var t = e.target && e.target.nodeType === 3 ? e.target.parentNode : e.target;
    if (t && t.closest && t.closest('input, textarea')) return;
    e.preventDefault();
  }, true);
  window.addEventListener('beforeprint', function () { toast('Printing is switched off during the exam.'); });
  document.addEventListener('keydown', function (e) {
    var k = (e.key || '').toLowerCase();
    var mod = e.ctrlKey || e.metaKey;
    if (mod && !e.shiftKey && !e.altKey && ['c', 'x', 'v', 'a', 'p', 'u', 's'].indexOf(k) !== -1) { blocked(e, 'That shortcut'); return; }
    if (e.key === 'F12' || (mod && e.shiftKey && ['i', 'j', 'c'].indexOf(k) !== -1)) { blocked(e, 'The developer tools'); return; }
    if (e.key === 'Insert' && (e.ctrlKey || e.shiftKey)) { blocked(e, 'Copy and paste'); }
  }, true);
  document.addEventListener('keyup', function (e) {
    if (e.key === 'PrintScreen' && navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText('').catch(function () {}); toast('Screenshots are not allowed during the exam.'); }
  });

  // ---------------------------------------------------------------------------------------------------------- answers, submit
  form.addEventListener('change', function () { dirty = true; window.clearTimeout(form._exSave); form._exSave = window.setTimeout(save, 1500); });

  form.addEventListener('submit', function (e) {
    if (submitted) return;
    e.preventDefault();
    var missing = questions.length - answeredCount();
    if (missing > 0 && confirmEl) {
      var t = confirmEl.querySelector('[data-ex-confirm-text]');
      if (t) t.textContent = missing + (missing === 1 ? ' question is' : ' questions are') + ' not answered. You cannot change your answers after you submit.';
      show(confirmEl);
      return;
    }
    autoSubmit('manual');
  });
  if (confirmEl) {
    confirmEl.querySelector('[data-ex-confirm-yes]').addEventListener('click', function () { autoSubmit('manual'); });
    confirmEl.querySelector('[data-ex-confirm-no]').addEventListener('click', function () {
      hide(confirmEl);
      var first = null;
      questions.forEach(function (q) { if (!first && !q.querySelector('input:checked')) first = q; });
      if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }
  if (warnEl) warnEl.querySelector('[data-ex-warn-ok]').addEventListener('click', function () { hide(warnEl); });

  // ---------------------------------------------------------------------------------------------------------- go
  window.setInterval(tick, 500);
  tick();
  var beat = 0;
  window.setInterval(function () {
    if (submitted || locked) return;
    beat++;
    if (pending.length) flush();
    if (dirty || beat % 2 === 0) save();      // answers every 5 s when changed; a heartbeat every 10 s
  }, 5000);
  // a page opened again after leaving shows the warning once more if leaves are already recorded
  if (leaves > 0) warn();
  send({}, false);
})();
