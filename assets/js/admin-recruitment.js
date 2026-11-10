/* Admin > recruitment panel: shows the percentage while the judge clicks the scores (the server works it out again when saving). */
(function () {
  'use strict';
  var form = document.querySelector('[data-rc-judge]');
  if (!form) return;
  var live = form.querySelector('[data-rc-live]');
  var level = parseInt(form.getAttribute('data-rc-level') || '70', 10);
  var groups = {};
  form.querySelectorAll('input[type=radio]').forEach(function (r) { groups[r.name] = parseInt(r.getAttribute('data-w') || '1', 10); });
  function update() {
    var sum = 0, max = 0, done = 0, total = 0;
    Object.keys(groups).forEach(function (name) {
      total++;
      var c = form.querySelector('input[name="' + name.replace(/"/g, '') + '"]:checked');
      if (c) { sum += parseInt(c.value, 10) * groups[name]; max += 5 * groups[name]; done++; }
    });
    if (!live) return;
    if (!max) { live.textContent = ''; return; }
    var pct = Math.round(sum * 1000 / max) / 10;
    live.textContent = pct + '% (' + done + ' of ' + total + ' criteria scored)' + (done === total ? (pct >= level ? ' - reaches the interview level' : ' - below the interview level') : '');
    live.classList.toggle('is-pass', done === total && pct >= level);
  }
  form.addEventListener('change', update);
  update();
})();
