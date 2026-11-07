/* Focus / program wheels (partials/focus-wheel.php): hovering, focusing or tapping a node shows its panel beside the wheel and lights its spoke; clicking a node follows its link.
   On touch screens the first tap only selects the area (so its projects can be read), the second tap opens the link. Any number of wheels on a page, each on its own.
   Without this file every panel is shown. */
(function () {
  'use strict';
  if (window.__lafWheels) return;
  window.__lafWheels = true;
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var touch = window.matchMedia && window.matchMedia('(hover: none)').matches;

  document.querySelectorAll('[data-fw]').forEach(function (wheel) {
    var nodes = wheel.querySelectorAll('[data-fw-node]');
    var panels = wheel.querySelectorAll('[data-fw-panel]');
    if (!nodes.length || !panels.length) return;
    wheel.classList.add('is-ready');
    var current = 0, timer = null, hovering = false;

    function show(n) {
      current = n;
      wheel.setAttribute('data-active', String(n));
      nodes.forEach(function (el, i) { el.classList.toggle('is-active', i === n); });
      panels.forEach(function (el, i) { el.classList.toggle('is-active', i === n); });
    }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function play() {
      if (calm || timer) return;
      timer = setInterval(function () { if (!hovering && !document.hidden) show((current + 1) % nodes.length); }, 6000);
    }

    nodes.forEach(function (el, i) {
      el.addEventListener('mouseenter', function () { hovering = true; show(i); });
      el.addEventListener('mouseleave', function () { hovering = false; });
      el.addEventListener('focus', function () { show(i); stop(); });
      el.addEventListener('click', function (e) {
        if (touch && !el.classList.contains('is-active')) { e.preventDefault(); show(i); stop(); }      // touch: first tap selects, second follows
      });
    });
    wheel.addEventListener('mouseenter', function () { hovering = true; stop(); });
    wheel.addEventListener('mouseleave', function () { hovering = false; });
    show(0);
    play();
  });
})();
