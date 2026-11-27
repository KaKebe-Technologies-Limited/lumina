/* The bank-details card (app/views/partials/bank-card.php): a "Copy" button on every row. The buttons are hidden in the HTML and shown only when this script runs, so nothing is dead
   without JavaScript (the values are plain, selectable text). It holds no account detail itself: it copies what the button carries. */
(function () {
  'use strict';

  function fallbackCopy(text) {
    var t = document.createElement('textarea');
    t.value = text;
    t.setAttribute('readonly', '');
    t.style.position = 'fixed';
    t.style.opacity = '0';
    document.body.appendChild(t);
    t.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
    document.body.removeChild(t);
    return ok;
  }

  function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text).then(function () { return true; }, function () { return fallbackCopy(text); });
    }
    return Promise.resolve(fallbackCopy(text));
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-bankcard]'), function (card) {
    var live = card.querySelector('[data-bankcard-live]');
    Array.prototype.forEach.call(card.querySelectorAll('[data-bankcard-copy]'), function (btn) {
      btn.hidden = false;
      var label = btn.querySelector('span');
      var original = label ? label.textContent : '';
      var timer = null;
      btn.addEventListener('click', function () {
        copyText(btn.getAttribute('data-bankcard-copy') || '').then(function (ok) {
          if (!ok) {
            if (live) live.textContent = 'Could not copy. Select the number and copy it.';
            return;
          }
          btn.classList.add('is-copied');
          if (label) label.textContent = btn.getAttribute('data-copied') || 'Copied';
          if (live) live.textContent = (btn.getAttribute('data-copied') || 'Copied') + ': ' + (btn.getAttribute('aria-label') || '');
          window.clearTimeout(timer);
          timer = window.setTimeout(function () {
            btn.classList.remove('is-copied');
            if (label) label.textContent = original;
          }, 1800);
        });
      });
    });
  });
})();
