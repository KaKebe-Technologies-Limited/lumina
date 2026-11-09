/* Admin > Official letters: show only the options that belong to the chosen signature mode / sending mode. */
(function () {
  'use strict';
  var pick = document.querySelector('[data-sig-pick]');
  if (pick) {
    var radios = document.querySelectorAll('[data-sig-mode]');
    var sync = function () {
      var on = false;
      radios.forEach(function (r) { if (r.checked && r.value === 'image') on = true; });
      pick.hidden = !on;
    };
    radios.forEach(function (r) { r.addEventListener('change', sync); });
    sync();
  }
  var modes = document.querySelectorAll('[data-send-mode]');
  if (modes.length) {
    var each = document.querySelector('[data-opt-each]');
    var one = document.querySelector('[data-opt-one]');
    var s2 = function () {
      var m = 'each';
      modes.forEach(function (r) { if (r.checked) m = r.value; });
      if (each) each.hidden = m !== 'each';
      if (one) one.hidden = m !== 'one';
    };
    modes.forEach(function (r) { r.addEventListener('change', s2); });
    s2();
  }
})();
