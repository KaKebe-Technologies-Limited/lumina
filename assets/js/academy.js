/* LAF Academy (public): quiz progress + timer, confirm on unanswered questions, print the certificate page. */
(function () {
  'use strict';

  // on phones and tablets the course menu starts closed so the lesson is the first thing on screen
  var side = document.querySelector('.ac-side__box');
  if (side && window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) side.removeAttribute('open');

  var form = document.querySelector('[data-ac-quiz]');
  if (form) {
    var questions = form.querySelectorAll('[data-ac-question]');
    var counter = form.querySelector('[data-ac-answered]');
    var submitting = false;

    var answered = function () {
      var n = 0;
      questions.forEach(function (q) { if (q.querySelector('input:checked')) n++; });
      return n;
    };
    var update = function () { if (counter) counter.textContent = String(answered()); };
    form.addEventListener('change', update);
    update();

    form.addEventListener('submit', function (e) {
      if (submitting) return;
      var missing = questions.length - answered();
      if (missing > 0 && form.hasAttribute('data-confirm-unanswered') && !form.hasAttribute('data-ac-timeup')) {
        if (!window.confirm(missing + (missing === 1 ? ' question is' : ' questions are') + ' not answered. Submit anyway?')) {
          e.preventDefault();
          var first = null;
          questions.forEach(function (q) { if (!first && !q.querySelector('input:checked')) first = q; });
          if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }
      }
      submitting = true;
    });

    var timer = document.querySelector('[data-ac-timer]');
    if (timer) {
      var left = parseInt(timer.getAttribute('data-ac-timer'), 10) || 0;
      var out = timer.querySelector('[data-ac-time]');
      var pad = function (n) { return (n < 10 ? '0' : '') + n; };
      var tick = function () {
        if (out) out.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        if (left <= 60) timer.classList.add('is-low');
        if (left <= 0) {
          window.clearInterval(handle);
          form.setAttribute('data-ac-timeup', '1');
          if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
          return;
        }
        left--;
      };
      var handle = window.setInterval(tick, 1000);
      tick();
    }
  }

  Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });

  var print = document.querySelector('[data-ac-print]');
  if (print) print.addEventListener('click', function () { window.print(); });
})();
