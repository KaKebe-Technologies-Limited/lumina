/* Admin > recruitment panel: shows the percentage while the judge clicks the scores, and leaves out the criteria that are switched off for this applicant
   (the server works everything out again when saving). */
(function () {
  'use strict';
  var form = document.querySelector('[data-rc-judge]');
  if (!form) return;
  var live = form.querySelector('[data-rc-live]');
  var level = parseInt(form.getAttribute('data-rc-level') || '70', 10);

  function update() {
    var sum = 0, max = 0, done = 0, total = 0;
    form.querySelectorAll('[data-rc-kpi]').forEach(function (box) {
      var use = box.querySelector('[data-rc-use]');
      var on = !use || use.checked;
      box.classList.toggle('is-off', !on);
      box.querySelectorAll('input[type=radio]').forEach(function (r) { r.disabled = !on; });
      if (!on) return;
      total++;
      var c = box.querySelector('input[type=radio]:checked');
      if (c) {
        var w = parseInt(c.getAttribute('data-w') || '1', 10);
        sum += parseInt(c.value, 10) * w;
        max += 5 * w;
        done++;
      }
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
