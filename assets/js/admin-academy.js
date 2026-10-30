/* Admin > LAF Academy: a free Jitsi room link for a virtual class, question counter of the quiz editor. */
(function () {
  'use strict';

  var make = document.querySelector('[data-aac-jitsi]');
  if (make) {
    make.addEventListener('click', function () {
      var target = document.querySelector(make.getAttribute('data-aac-jitsi'));
      if (!target) return;
      var bytes = new Uint8Array(6);
      (window.crypto || window.msCrypto).getRandomValues(bytes);
      var hex = Array.prototype.map.call(bytes, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
      target.value = 'https://meet.jit.si/LAF-Academy-' + hex;
      var platform = document.querySelector('[name="platform"]');
      if (platform) platform.value = 'jitsi';
      target.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }

  var script = document.querySelector('[data-aac-script]');
  var counter = document.querySelector('[data-aac-qcount]');
  if (script && counter) {
    var update = function () {
      var blocks = script.value.replace(/\r/g, '').split(/\n\s*\n/).filter(function (b) { return /^\s*Q\s*[:.)]/i.test(b); });
      counter.textContent = String(blocks.length);
    };
    script.addEventListener('input', update);
    update();
  }
  // a button inside a form can ask for confirmation too (the shared admin.js only handles form[data-confirm])
  document.addEventListener("click", function (e) {
    var b = e.target.closest ? e.target.closest("button[data-confirm]") : null;
    if (b && !window.confirm(b.getAttribute("data-confirm"))) e.preventDefault();
  });
})();

/* Admin > Academy > Learners > WhatsApp payment reminders: one button opens the next unpaid learner's chat, remembers which were opened, shows progress. */
(function () {
  var box = document.querySelector('[data-wa-queue]');
  if (!box) return;
  var links = Array.prototype.slice.call(document.querySelectorAll('[data-wa-link]'));
  var key = box.getAttribute('data-wa-key') || 'laf-wa-fee-opened';
  var done = {};
  try { done = JSON.parse(window.localStorage.getItem(key) || '{}') || {}; } catch (e) { done = {}; }
  var btn = box.querySelector('[data-wa-next]'), label = btn.querySelector('span'), status = box.querySelector('[data-wa-status]');
  var fill = box.querySelector('[data-wa-fill]'), reset = box.querySelector('[data-wa-reset]');
  var mark = function (a) {
    var id = a.getAttribute('data-wa-link');
    done[id] = 1;
    try { window.localStorage.setItem(key, JSON.stringify(done)); } catch (e) { /* the marks last only for this visit */ }
    var tag = a.parentNode.querySelector('[data-wa-sent]');
    if (tag) tag.hidden = false;
  };
  var pending = function () { return links.filter(function (a) { return !done[a.getAttribute('data-wa-link')]; }); };
  var paint = function () {
    links.forEach(function (a) { var tag = a.parentNode.querySelector('[data-wa-sent]'); if (tag) tag.hidden = !done[a.getAttribute('data-wa-link')]; });
    var left = pending(), n = links.length, d = n - left.length;
    fill.style.width = (n ? Math.round(d * 100 / n) : 0) + '%';
    reset.hidden = d === 0;
    if (!left.length) { label.textContent = 'All ' + n + ' chats opened'; status.textContent = 'Every chat was opened. If you pressed Send in each one, all learners have been reminded.'; btn.disabled = true; return; }
    btn.disabled = false;
    label.textContent = (d === 0 ? 'Start sending to all (' : 'Next: chat ' + (d + 1) + ' of ' + n + ' (') + (d === 0 ? n : left.length + ' left') + ')';
  };
  btn.addEventListener('click', function () {
    var left = pending();
    if (!left.length) return;
    var a = left[0];
    window.open(a.href, '_blank', 'noopener');
    mark(a);
    paint();
  });
  reset.addEventListener('click', function () { done = {}; try { window.localStorage.removeItem(key); } catch (e) { /* ignore */ } btn.disabled = false; paint(); });
  paint();
})();
