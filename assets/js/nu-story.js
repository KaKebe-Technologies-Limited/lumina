/* Northern Uganda story page: a reading-progress bar and an "Open all details" toggle for the "Go deeper" expanders. Everything works without it (plain <details>). */
(function () {
  'use strict';
  var bar = document.querySelector('[data-nu-progress]');
  if (bar) {
    var tick = function () {
      var h = document.documentElement;
      var max = h.scrollHeight - h.clientHeight;
      bar.style.width = (max > 0 ? Math.min(100, Math.max(0, (h.scrollTop / max) * 100)) : 0) + '%';
    };
    window.addEventListener('scroll', tick, { passive: true });
    tick();
  }
  var toggle = document.querySelector('[data-nu-toggle]');
  if (toggle) {
    var label = toggle.querySelector('[data-nu-toggle-label]');
    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-pressed') !== 'true';
      toggle.setAttribute('aria-pressed', open ? 'true' : 'false');
      if (label) label.textContent = open ? 'Close all details' : 'Open all details';
      document.querySelectorAll('[data-nu-details]').forEach(function (d) { d.open = open; });
    });
  }
})();
