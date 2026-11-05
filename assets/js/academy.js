/* LAF Academy (public): quiz progress + timer, confirm on unanswered questions, print the certificate page. */
(function () {
  'use strict';

  // follow and subscribe: the "I follow" tick unlocks when every page was opened from the form
  var follow = document.querySelector('[data-follow][data-follow-required]');
  if (follow) {
    var links = follow.querySelectorAll('[data-follow-link]');
    var tick = follow.querySelector('[data-follow-tick]');
    var hint = follow.querySelector('[data-follow-hint]');
    var openedKeys = {};
    links.forEach(function (a) { if (a.classList.contains('is-opened')) openedKeys[a.getAttribute('data-follow-link')] = true; });
    var update = function () {
      var all = true;
      links.forEach(function (a) { if (!openedKeys[a.getAttribute('data-follow-link')]) all = false; });
      if (tick) { tick.disabled = !all; if (!all) tick.checked = false; }
      if (hint) hint.hidden = all;
    };
    links.forEach(function (a) {
      a.addEventListener('click', function () {
        openedKeys[a.getAttribute('data-follow-link')] = true;
        a.classList.add('is-opened');
        var mark = a.querySelector('.ac-follow__done');
        if (mark) mark.hidden = false;
        update();
      });
    });
    update();
  }

  // registration number box: copy button, print button, and the "I have saved it" tick that unlocks Continue
  var regBox = document.querySelector('.ac-regwarn');
  if (regBox) {
    var numEl = regBox.querySelector('[data-regno]');
    var copyBtn = regBox.querySelector('[data-copy-regno]');
    var printBtn = regBox.querySelector('[data-print-regno]');
    var label = regBox.querySelector('[data-copy-label]');
    if (copyBtn && numEl) {
      copyBtn.hidden = false;
      copyBtn.addEventListener('click', function () {
        var text = numEl.textContent.trim();
        var done = function () { if (label) { label.textContent = 'Copied!'; setTimeout(function () { label.textContent = 'Copy the number'; }, 2500); } };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text, done); });
        } else {
          fallbackCopy(text, done);
        }
      });
    }
    if (printBtn) { printBtn.hidden = false; printBtn.addEventListener('click', function () { window.print(); }); }
    var tick = document.querySelector('[data-regno-saved]');
    var go = document.querySelector('[data-regno-continue]');
    if (tick && go) {
      var sync = function () { go.classList.toggle('is-disabled', !tick.checked); go.setAttribute('aria-disabled', tick.checked ? 'false' : 'true'); };
      go.addEventListener('click', function (e) { if (!tick.checked) { e.preventDefault(); tick.focus(); } });
      tick.addEventListener('change', sync);
      sync();
    }
  }
  function fallbackCopy(text, done) {
    var t = document.createElement('textarea');
    t.value = text; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
    document.body.appendChild(t); t.select();
    try { document.execCommand('copy'); done(); } catch (e) { /* the number stays selectable on screen */ }
    document.body.removeChild(t);
  }

  // on phones and tablets the course menu starts closed so the lesson is the first thing on screen
  var side = document.querySelector('.ac-side__box');
  if (side && window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) side.removeAttribute('open');

  var form = document.querySelector('[data-ac-quiz]');
  if (form) {
    var questions = form.querySelectorAll('[data-ac-question]');
    var counter = form.querySelector('[data-ac-answered]');
    var submitting = false;

    var answered = function () {
      var n = 0;
      questions.forEach(function (q) { if (q.querySelector('input:checked')) n++; });
      return n;
    };
    var update = function () { if (counter) counter.textContent = String(answered()); };
    form.addEventListener('change', update);
    update();

    form.addEventListener('submit', function (e) {
      if (submitting) return;
      var missing = questions.length - answered();
      if (missing > 0 && form.hasAttribute('data-confirm-unanswered') && !form.hasAttribute('data-ac-timeup')) {
        if (!window.confirm(missing + (missing === 1 ? ' question is' : ' questions are') + ' not answered. Submit anyway?')) {
          e.preventDefault();
          var first = null;
          questions.forEach(function (q) { if (!first && !q.querySelector('input:checked')) first = q; });
          if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }
      }
      submitting = true;
    });

    var timer = document.querySelector('[data-ac-timer]');
    if (timer) {
      var left = parseInt(timer.getAttribute('data-ac-timer'), 10) || 0;
      var out = timer.querySelector('[data-ac-time]');
      var pad = function (n) { return (n < 10 ? '0' : '') + n; };
      var tick = function () {
        if (out) out.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        if (left <= 60) timer.classList.add('is-low');
        if (left <= 0) {
          window.clearInterval(handle);
          form.setAttribute('data-ac-timeup', '1');
          if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
          return;
        }
        left--;
      };
      var handle = window.setInterval(tick, 1000);
      tick();
    }
  }

  Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });

  var print = document.querySelector('[data-ac-print]');
  if (print) print.addEventListener('click', function () { window.print(); });
})();

/* LAF Academy lessons: interactive extras added to the lesson text in the browser (the text itself stays plain HTML, so it reads fine without JavaScript).
   - A blockquote that starts with "Quick check:" becomes a click-to-reveal question card (reveal the answer, then say "I knew it" or "Review this").
   - The numbered list under the "Try it yourself" heading becomes a tick-off checklist.
   - A small tracker shows how many checks and practice steps are done; ticks are remembered on this device only (localStorage). */
(function () {
  'use strict';
  var prose = document.querySelector('.ac-prose');
  if (!prose) return;
  var key = 'laf-lesson:' + location.pathname;
  var saved = {};
  try { saved = JSON.parse(window.localStorage.getItem(key) || '{}') || {}; } catch (e) { saved = {}; }
  var store = function () { try { window.localStorage.setItem(key, JSON.stringify(saved)); } catch (e) { /* private mode: ticks last only for this visit */ } };
  var el = function (tag, cls, text) { var n = document.createElement(tag); if (cls) n.className = cls; if (text) n.textContent = text; return n; };

  var checks = [];
  var steps = [];

  Array.prototype.forEach.call(prose.querySelectorAll('blockquote'), function (bq, i) {
    var ps = bq.querySelectorAll('p');
    var head = ps[0] ? ps[0].querySelector('strong') : null;
    if (!head || !/^quick check/i.test(head.textContent.trim()) || ps.length < 2) return;
    var id = 'q' + i;
    var answer = [];
    for (var k = 1; k < ps.length; k++) answer.push(ps[k]);
    var box = el('div', 'ac-qc');
    box.setAttribute('role', 'group');
    var q = ps[0].cloneNode(true);
    q.className = 'ac-qc__q';
    var panel = el('div', 'ac-qc__a');
    panel.hidden = true;
    answer.forEach(function (p) { panel.appendChild(p.cloneNode(true)); });
    var bar = el('div', 'ac-qc__bar');
    var show = el('button', 'btn btn--outline btn--sm', 'Think, then show the answer');
    show.type = 'button';
    show.setAttribute('aria-expanded', 'false');
    var knew = el('button', 'btn btn--ghost btn--sm', 'I knew it');
    var again = el('button', 'btn btn--ghost btn--sm', 'Review this');
    knew.type = again.type = 'button';
    var mark = el('span', 'ac-qc__mark');
    var verdict = function (v) {
      saved[id] = v;
      store();
      box.classList.toggle('is-known', v === 'knew');
      box.classList.toggle('is-review', v === 'review');
      mark.textContent = v === 'knew' ? 'Got it' : 'To review';
      count();
    };
    show.addEventListener('click', function () {
      var open = panel.hidden;
      panel.hidden = !open;
      show.setAttribute('aria-expanded', open ? 'true' : 'false');
      show.textContent = open ? 'Hide the answer' : 'Think, then show the answer';
      bar.classList.toggle('is-open', open);
      if (open && !saved[id]) { saved[id] = 'seen'; store(); count(); }
    });
    knew.addEventListener('click', function () { verdict('knew'); });
    again.addEventListener('click', function () { verdict('review'); });
    bar.appendChild(show);
    bar.appendChild(knew);
    bar.appendChild(again);
    bar.appendChild(mark);
    box.appendChild(el('span', 'ac-qc__tag', 'Quick check'));
    box.appendChild(q);
    box.appendChild(bar);
    box.appendChild(panel);
    bq.parentNode.replaceChild(box, bq);
    if (saved[id] === 'knew' || saved[id] === 'review') verdict(saved[id]);
    checks.push(id);
  });

  Array.prototype.forEach.call(prose.querySelectorAll('h3'), function (h) {
    if (!/^try it yourself/i.test(h.textContent.trim())) return;
    var list = h.nextElementSibling;
    if (!list || list.tagName !== 'OL') return;
    list.classList.add('ac-steps');
    Array.prototype.forEach.call(list.children, function (li, n) {
      var id = 's' + n;
      var cb = el('input');
      cb.type = 'checkbox';
      cb.className = 'ac-steps__box';
      cb.setAttribute('aria-label', 'Step ' + (n + 1) + ' done');
      cb.checked = saved[id] === 1;
      li.classList.toggle('is-done', cb.checked);
      cb.addEventListener('change', function () {
        saved[id] = cb.checked ? 1 : 0;
        li.classList.toggle('is-done', cb.checked);
        store();
        count();
      });
      li.insertBefore(cb, li.firstChild);
      steps.push(id);
    });
  });

  if (!checks.length && !steps.length) return;
  var tracker = el('div', 'ac-track');
  tracker.setAttribute('role', 'status');
  tracker.setAttribute('aria-live', 'polite');
  var fill = el('span', 'ac-track__fill');
  var label = el('span', 'ac-track__label');
  var barOuter = el('span', 'ac-track__bar');
  barOuter.appendChild(fill);
  tracker.appendChild(label);
  tracker.appendChild(barOuter);
  prose.insertBefore(tracker, prose.firstChild);

  function count() {
    var c = checks.filter(function (id) { return saved[id] === 'knew' || saved[id] === 'review' || saved[id] === 'seen'; }).length;
    var s = steps.filter(function (id) { return saved[id] === 1; }).length;
    var total = checks.length + steps.length;
    var pct = total ? Math.round((c + s) * 100 / total) : 0;
    var parts = [];
    if (checks.length) parts.push(c + ' of ' + checks.length + ' quick checks');
    if (steps.length) parts.push(s + ' of ' + steps.length + ' practice steps');
    label.textContent = 'Your activity: ' + parts.join(', ') + (pct === 100 ? ' - well done!' : '');
    fill.style.width = pct + '%';
    tracker.classList.toggle('is-complete', pct === 100);
  }
  count();
})();

/* LAF Academy virtual classes: a live countdown on every upcoming class ("Starts in 2 days 3 h", "Starting soon", "Live now - join"), refreshed every 30 seconds. */
(function () {
  'use strict';
  var items = document.querySelectorAll('[data-ac-live]');
  if (!items.length) return;
  var plural = function (n, w) { return n + ' ' + w + (n === 1 ? '' : 's'); };
  var tick = function () {
    var now = Math.floor(Date.now() / 1000);
    Array.prototype.forEach.call(items, function (li) {
      var out = li.querySelector('[data-ac-count]');
      if (!out) return;
      var start = parseInt(li.getAttribute('data-start'), 10);
      var end = parseInt(li.getAttribute('data-end'), 10);
      var left = start - now;
      var text = '';
      var state = '';
      if (now >= end) { text = 'This class has ended.'; state = 'ended'; }
      else if (now >= start) { text = 'Live now: join the class'; state = 'live'; }
      else if (left <= 900) { text = 'Starting in ' + plural(Math.max(1, Math.ceil(left / 60)), 'minute'); state = 'soon'; }
      else if (left < 86400) { text = 'Starts in ' + plural(Math.floor(left / 3600), 'hour') + ' ' + plural(Math.floor((left % 3600) / 60), 'minute'); state = 'today'; }
      else { text = 'Starts in ' + plural(Math.floor(left / 86400), 'day') + ' ' + plural(Math.floor((left % 86400) / 3600), 'hour'); state = 'later'; }
      out.textContent = text;
      out.setAttribute('data-state', state);
    });
  };
  tick();
  window.setInterval(tick, 30000);
})();

/* LAF Academy sign-up: show the chosen picture, or take a selfie with the camera (the picture is saved with the account, private). */
(function () {
  'use strict';
  var box = document.querySelector('[data-selfie]');
  if (!box) return;
  var input = box.querySelector('input[type=file]');
  var open = box.querySelector('[data-selfie-open]');
  var cam = box.querySelector('[data-selfie-cam]');
  var video = cam ? cam.querySelector('video') : null;
  var snap = box.querySelector('[data-selfie-snap]');
  var cancel = box.querySelector('[data-selfie-cancel]');
  var preview = box.querySelector('[data-selfie-preview]');
  var msg = box.querySelector('[data-selfie-msg]');
  var stream = null;
  var say = function (t) { if (msg) msg.textContent = t; };
  var show = function (file) {
    if (!preview || !file || !window.URL || !URL.createObjectURL) return;
    preview.src = URL.createObjectURL(file);
    preview.hidden = false;
  };
  var stop = function () {
    if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
    stream = null;
    if (video) video.srcObject = null;
    if (cam) cam.hidden = true;
  };
  input.addEventListener('change', function () { if (input.files && input.files[0]) show(input.files[0]); });
  if (!open || !video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.DataTransfer || !window.File) return;
  open.hidden = false;
  open.addEventListener('click', function () {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 720 } }, audio: false }).then(function (s) {
      stream = s;
      video.srcObject = s;
      cam.hidden = false;
      say('Look at the camera and press "Take the photo".');
    }).catch(function () {
      say('The camera could not be opened (allow it in the browser, or choose a picture from your device instead).');
    });
  });
  if (cancel) cancel.addEventListener('click', function () { stop(); say('You can still attach a picture from your device.'); });
  if (snap) snap.addEventListener('click', function () {
    var w = video.videoWidth, h = video.videoHeight;
    if (!w || !h) return;
    var side = Math.min(w, h);
    var out = Math.min(side, 720);
    var canvas = document.createElement('canvas');
    canvas.width = out;
    canvas.height = out;
    canvas.getContext('2d').drawImage(video, (w - side) / 2, (h - side) / 2, side, side, 0, 0, out, out);
    canvas.toBlob(function (blob) {
      if (!blob) { say('The photo could not be taken. Please choose a picture from your device.'); return; }
      var file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
      var dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      show(file);
      stop();
      say('Your selfie is ready. It will be saved with your account.');
    }, 'image/jpeg', 0.92);
  });
  window.addEventListener('pagehide', stop);
})();

/* LAF Academy portal: choosing a file in an input marked data-autosubmit sends its form at once (the "Change photo" button on the dashboard). */
(function () {
  'use strict';
  document.addEventListener('change', function (e) {
    var t = e.target;
    if (t && t.matches && t.matches('input[type=file][data-autosubmit]') && t.files && t.files.length && t.form) {
      var btn = t.closest('.pt-avatar__btn');
      if (btn) btn.textContent = 'Saving...';
      t.form.submit();
    }
  });
})();

/* LAF Academy sign-up and account: choosing the country fills in its phone code ("+234 ") in the phone and WhatsApp boxes, so nothing defaults to +256. */
(function () {
  'use strict';
  var sel = document.querySelector('[data-country-dial]');
  if (!sel) return;
  var dials = {};
  try { dials = JSON.parse(sel.getAttribute('data-country-dial') || '{}'); } catch (e) { return; }
  var inputs = Array.prototype.slice.call(document.querySelectorAll('[data-dial-input]'));
  var prev = '';
  function plain(v) { return v.replace(/\s+/g, ''); }
  function apply() {
    var d = dials[sel.value];
    if (!d) return;
    inputs.forEach(function (inp) {
      var v = inp.value.trim();
      if (v === '' || v === '+' || (prev && plain(v) === '+' + prev)) {
        inp.value = '+' + d + ' ';
      } else if (prev && v.indexOf('+' + prev) === 0) {
        inp.value = '+' + d + v.slice(('+' + prev).length);
      }
    });
    prev = d;
  }
  sel.addEventListener('change', apply);
  if (sel.value && dials[sel.value]) {               // already chosen when the page opens (a form sent back with errors, the account page): fill the empty boxes only
    prev = dials[sel.value];
    inputs.forEach(function (inp) { if (inp.value.trim() === '') inp.value = '+' + prev + ' '; });
  }
})();
