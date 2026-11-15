/* Admin > LAF Academy > Fees & payments: live previews (the learner's payment details, the message wording) and placeholder buttons. Optional: the forms work without it. */
(function () {
  'use strict';

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
  }

  // --- payment details preview (the same markup idea as the learner portal: paragraphs under a heading)
  var mobile = document.querySelector('[data-af-source="mobile"]');
  var bank = document.querySelector('[data-af-source="bank"]');
  function paras(text) {
    return String(text || '').split(/\n+/).map(function (l) { return l.trim(); }).filter(Boolean).map(function (l) { return '<p>' + esc(l) + '</p>'; }).join('');
  }
  function details() {
    var m = mobile ? mobile.value.trim() : '', b = bank ? bank.value.trim() : '';
    var outM = document.querySelector('[data-af-out="mobile"]'), outB = document.querySelector('[data-af-out="bank"]');
    var blockM = document.querySelector('[data-af-block="mobile"]'), blockB = document.querySelector('[data-af-block="bank"]');
    var empty = document.querySelector('[data-af-empty]');
    if (!outM || !outB) return;
    outM.innerHTML = paras(m);
    outB.innerHTML = paras(b);
    blockM.hidden = m === '';
    blockB.hidden = b === '';
    if (empty) empty.hidden = !(m === '' && b === '');
  }
  [mobile, bank].forEach(function (el) { if (el) el.addEventListener('input', details); });
  details();

  // --- message preview with example values
  var form = document.querySelector('[data-af-messages]');
  if (!form) return;
  var sample = {};
  try { sample = JSON.parse(form.getAttribute('data-af-sample') || '{}'); } catch (e) { sample = {}; }
  function fill(text, key) {
    var vars = sample[key] || {};
    var out = String(text).replace(/\{([a-z_]+)\}/g, function (all, k) { return Object.prototype.hasOwnProperty.call(vars, k) ? vars[k] : all; });
    out = out.replace(/\s*\(\s*\)/g, '').replace(/[ \t]{2,}/g, ' ').replace(/ +([,.;:!?])/g, '$1');
    return out.trim();
  }
  function show(area) {
    var key = area.getAttribute('data-af-msg');
    var box = form.querySelector('[data-af-preview="' + key + '"]');
    var reset = form.querySelector('[name="reset_' + key + '"]');
    var text = area.value.trim() === '' || (reset && reset.checked) ? area.getAttribute('data-af-default') : area.value;
    if (box) box.textContent = fill(text, key);
  }
  form.querySelectorAll('[data-af-msg]').forEach(function (area) {
    area.addEventListener('input', function () { show(area); });
    show(area);
  });
  form.querySelectorAll('[name^="reset_"]').forEach(function (cb) {
    cb.addEventListener('change', function () { var a = form.querySelector('[data-af-msg="' + cb.name.slice(6) + '"]'); if (a) show(a); });
  });
  form.querySelectorAll('[data-af-insert]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var a = form.querySelector('[data-af-msg="' + btn.getAttribute('data-af-for') + '"]');
      if (!a) return;
      if (a.value.trim() === '') a.value = a.getAttribute('data-af-default');   // start from the built-in wording, then add
      var s = a.selectionStart || a.value.length, e = a.selectionEnd || s, t = btn.getAttribute('data-af-insert');
      a.value = a.value.slice(0, s) + t + a.value.slice(e);
      a.focus();
      a.selectionStart = a.selectionEnd = s + t.length;
      show(a);
    });
  });
})();
