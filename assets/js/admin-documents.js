/* Admin > Documents > Signatories: "select all" for the document picker (which documents a person signs, and in which role). */
(function () {
  'use strict';

  document.addEventListener('change', function (e) {
    var all = e.target.closest ? e.target.closest('[data-sig-all]') : null;
    if (!all) return;
    var scope = all.closest('[data-sig-picker]');
    if (!scope) return;
    var only = all.getAttribute('data-sig-all');
    scope.querySelectorAll('input[type=checkbox][name="documents[]"]').forEach(function (box) {
      if (only === 'policy' && box.getAttribute('data-category') !== 'policy') return;
      box.checked = all.checked;
    });
  });
})();
