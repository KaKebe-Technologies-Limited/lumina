/* Involvement form: the CV and portrait fields appear (and are required by the server) only when "volunteer" is chosen. */
(function () {
  'use strict';
  var form = document.querySelector('[data-inv-form]');
  if (!form) return;
  var box = form.querySelector('[data-inv-volunteer]');
  var type = form.querySelector('[name="involvement_type"]');
  if (!box || !type) return;
  function sync() { box.hidden = type.value !== 'volunteer'; }
  type.addEventListener('change', sync);
  sync();
})();
