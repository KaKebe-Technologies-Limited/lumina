/* Website launch pop-up (partials/launch-popup.php, config app/config/launch.php).
   - Opens on EVERY page load until the server stops printing it (ends_at in the config).
   - A big countdown to data-launch-at (a Unix time): each number flips when it changes; in the last ten seconds a giant 10 ... 1 takes over; at zero the pop-up says "We are live!"
     and balloons and confetti burst (three waves).
   - If the visitor arrives after the launch moment, it opens straight into "We are live!" with the burst.
   - With "reduce motion": no flipping, no giant numbers, no balloons; the words and numbers are still there.
   - GATE mode (data-launch-gate, the pre-launch page): the pop-up is the whole page, it cannot be closed, and a few seconds after "We are live!" the page reloads so the site opens.
   - Otherwise it closes with the buttons, the backdrop, the X or Escape; focus goes back to where it was. */
(function () {
  'use strict';
  var box = document.querySelector('[data-launch]');
  if (!box) return;
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var launchAt = parseInt(box.getAttribute('data-launch-at'), 10) * 1000;
  var endsAt = parseInt(box.getAttribute('data-launch-ends'), 10) * 1000;
  var isGate = box.hasAttribute('data-launch-gate');
  if (!launchAt || (!isGate && Date.now() >= endsAt)) return;

  var gate = box.hasAttribute('data-launch-gate');
  var card = box.querySelector('.launch__card');
  var earlier = null, timer = null, live = false, lastFinal = -1;
  var units = { d: null, h: null, m: null, s: null };
  var pad = function (n) { return (n < 10 ? '0' : '') + n; };
  var q = function (s) { return box.querySelector(s); };
  ['d', 'h', 'm', 's'].forEach(function (k) { units[k] = q('[data-launch-' + k + ']'); });

  function setUnit(k, value) {
    var el = units[k];
    if (!el || el.textContent === value) return;
    el.textContent = value;
    if (calm) return;
    el.classList.remove('is-flip');
    void el.offsetWidth;                       // restart the animation
    el.classList.add('is-flip');
  }

  function goLive(first) {
    live = true;
    var badge = q('[data-launch-badge]');
    if (badge) badge.textContent = 'Officially launched';
    var kicker = q('[data-launch-kicker]');
    if (kicker) kicker.hidden = true;
    q('[data-launch-count]').hidden = true;
    q('[data-launch-final]').hidden = true;
    q('[data-launch-live]').hidden = false;
    box.classList.remove('is-final');
    box.classList.add('is-live');
    if (!box.hidden) { burst(0); setTimeout(function () { burst(1); }, 1100); setTimeout(function () { burst(2); }, 2300); }
    if (gate) setTimeout(function () { window.location.reload(); }, 9000);          // the site opens: reload into it
  }

  function tick() {
    var left = launchAt - Date.now();
    if (left <= 0) { if (!live) goLive(); return; }
    var s = Math.floor(left / 1000);
    setUnit('d', pad(Math.floor(s / 86400)));
    setUnit('h', pad(Math.floor((s % 86400) / 3600)));
    setUnit('m', pad(Math.floor((s % 3600) / 60)));
    setUnit('s', pad(s % 60));
    // the last ten seconds: one giant number
    if (left <= 10000) {
      var n = Math.ceil(left / 1000);
      var big = q('[data-launch-final]');
      box.classList.add('is-final');
      if (n !== lastFinal) {
        lastFinal = n;
        big.hidden = false;
        big.textContent = String(n);
        if (!calm) { big.classList.remove('is-hit'); void big.offsetWidth; big.classList.add('is-hit'); }
      }
    }
  }

  var COLOURS = ['#D9A441', '#C96A22', '#142B4A', '#FFFFFF', '#F2C46B', '#E8833A', '#3B82A6', '#E24B6A'];
  function burst(wave) {
    if (calm) return;
    var layer = document.createElement('div');
    layer.className = 'launch-fx';
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);
    var r = card.getBoundingClientRect();
    var cx = r.left + r.width / 2 + (wave === 1 ? -r.width * 0.25 : wave === 2 ? r.width * 0.25 : 0);
    var cy = r.top + Math.min(r.height / 3, 260);
    var i, el, a, d;
    for (i = 0; i < 30; i++) {                  // balloons: pop out of the card, then float up
      el = document.createElement('span');
      el.className = 'launch-balloon';
      a = Math.random() * Math.PI * 2;
      d = 120 + Math.random() * Math.max(window.innerWidth, 500) * 0.5;
      el.style.left = cx + 'px';
      el.style.top = cy + 'px';
      el.style.setProperty('--c', COLOURS[i % COLOURS.length]);
      el.style.setProperty('--s', (0.7 + Math.random() * 0.8).toFixed(2));
      el.style.setProperty('--dx', Math.round(Math.cos(a) * d) + 'px');
      el.style.setProperty('--dy', Math.round(Math.sin(a) * d * 0.6) + 'px');
      el.style.setProperty('--rise', '-' + Math.round(window.innerHeight * (0.9 + Math.random() * 0.5)) + 'px');
      el.style.animationDelay = (Math.random() * 0.35).toFixed(2) + 's';
      el.style.animationDuration = (3.6 + Math.random() * 2.4).toFixed(2) + 's';
      layer.appendChild(el);
    }
    for (i = 0; i < 60; i++) {                  // confetti
      el = document.createElement('i');
      el.className = 'launch-confetti';
      a = Math.random() * Math.PI * 2;
      d = 80 + Math.random() * 380;
      el.style.left = cx + 'px';
      el.style.top = cy + 'px';
      el.style.background = COLOURS[i % COLOURS.length];
      el.style.setProperty('--dx', Math.round(Math.cos(a) * d) + 'px');
      el.style.setProperty('--dy', Math.round(Math.sin(a) * d * 0.7) + 'px');
      el.style.setProperty('--rot', Math.round(Math.random() * 720 - 360) + 'deg');
      el.style.animationDelay = (Math.random() * 0.25).toFixed(2) + 's';
      layer.appendChild(el);
    }
    setTimeout(function () { if (layer.parentNode) layer.parentNode.removeChild(layer); }, 8000);
  }

  function open() {
    earlier = document.activeElement;
    box.hidden = false;
    document.documentElement.classList.add('launch-open');
    card.focus({ preventScroll: true });
    tick();
    timer = setInterval(tick, 1000);
    if (!live) setTimeout(function () { burst(0); }, 450);
  }

  function close() {
    if (gate) return;                                         // the pre-launch page cannot be closed
    box.hidden = true;
    document.documentElement.classList.remove('launch-open');
    if (timer) { clearInterval(timer); timer = null; }
    var fx = document.querySelectorAll('.launch-fx');
    for (var i = 0; i < fx.length; i++) if (fx[i].parentNode) fx[i].parentNode.removeChild(fx[i]);
    if (earlier && earlier.focus) { try { earlier.focus({ preventScroll: true }); } catch (e) { /* ignore */ } }
  }

  box.addEventListener('click', function (e) {
    var t = e.target;
    while (t && t !== box) {
      if (t.hasAttribute && (t.hasAttribute('data-launch-close') || t.hasAttribute('data-launch-close-link'))) {
        if (!t.hasAttribute('data-launch-close-link')) e.preventDefault();
        close();
        return;
      }
      t = t.parentNode;
    }
  });
  document.addEventListener('keydown', function (e) {
    if (box.hidden) return;
    if (e.key === 'Escape') { if (!gate) close(); return; }
    if (e.key === 'Tab') {                                    // keep the focus inside the pop-up
      var f = card.querySelectorAll('a[href], button:not([disabled])');
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && (document.activeElement === first || document.activeElement === card)) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  // let the page paint first, then open (at once on the pre-launch page)
  if (gate) { box.hidden = false; open(); } else { setTimeout(open, 1200); }
})();
