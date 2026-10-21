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
