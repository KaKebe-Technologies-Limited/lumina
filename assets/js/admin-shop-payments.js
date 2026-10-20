/* ADMIN SHOP PAYMENTS panel behaviour (docs/SHOP.md). Progressive enhancement: without JavaScript every box is shown and every button is a plain form post.
 * Hooks: [data-method-toggle] shows / hides the details of a way to pay - [data-make-hash] fills a random webhook hash - [data-secret-toggle] shows / hides what was typed in a secret box -
 * [data-copy] copies the webhook address - [data-test-btn] "Test connection" (POST, JSON). A secret is never sent back to the browser: the page only says "Set (hidden)".
 */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var csrf = function () { var m = $('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; };

  // ---- ways to pay: details only while the method is on
  $$('[data-method]').forEach(function (box) {
    var toggle = $('[data-method-toggle]', box);
    var body = $('[data-method-body]', box);
    if (!toggle || !body) { return; }
    function sync() { body.hidden = !toggle.checked; box.classList.toggle('is-on', toggle.checked); }
    toggle.addEventListener('change', sync);
    sync();
  });

  // ---- random hash
  $$('[data-make-hash]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = $(btn.getAttribute('data-make-hash'));
      if (!input) { return; }
      var bytes = new Uint8Array(24);
      (window.crypto || window.msCrypto).getRandomValues(bytes);
      input.value = Array.prototype.map.call(bytes, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
      input.type = 'text';
      var rev = input.parentNode.querySelector('[data-secret-toggle]');
      if (rev) { rev.textContent = 'Hide'; rev.setAttribute('aria-pressed', 'true'); }
      input.focus();
      input.select();
    });
  });

  // ---- show / hide a typed secret
  $$('[data-secret-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = $(btn.getAttribute('data-secret-toggle'));
      if (!input) { return; }
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Hide' : 'Show';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    });
  });

  // ---- copy
  $$('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy') || '';
      var label = btn.lastChild;
      function done() {
        var old = btn.innerHTML;
        btn.textContent = btn.getAttribute('data-copied') || 'Copied';
        window.setTimeout(function () { btn.innerHTML = old; }, 1800);
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done, function () { var i = $('[data-webhook-url]'); if (i) { i.select(); } });
      } else { var i2 = $('[data-webhook-url]'); if (i2) { i2.select(); try { document.execCommand('copy'); done(); } catch (e) { /* ignore */ } } }
      void label;
    });
  });

  // ---- test connection
  var testBtn = $('[data-test-btn]');
  var result = $('[data-test-result]');
  if (testBtn && result && window.fetch) {
    testBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var formEl = document.getElementById(testBtn.getAttribute('form'));
      if (!formEl) { return; }
      if (testBtn.getAttribute('aria-disabled') === 'true') { return; }
      testBtn.setAttribute('aria-disabled', 'true');                 // not `disabled`: a disabled button that has focus drops the keyboard user to the top of the page
      testBtn.setAttribute('aria-busy', 'true');
      result.className = 'ash-test__result';
      result.textContent = 'Checking with Flutterwave...';
      fetch(formEl.action, { method: 'POST', body: new FormData(formEl), credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() } })
        .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
        .then(function (res) {
          var j = res.json || {};
          var ok = !!j.ok;
          result.className = 'ash-test__result ' + (ok ? 'is-ok' : 'is-error');
          result.textContent = (j.message || (ok ? 'Connected.' : 'The connection test failed.')) + (j.warning ? ' ' + j.warning : '');
        })
        .catch(function () {
          result.className = 'ash-test__result is-error';
          result.textContent = 'The test could not be run (the page lost its connection). Try again.';
        })
        .then(function () { testBtn.removeAttribute('aria-disabled'); testBtn.removeAttribute('aria-busy'); });
    });
  }
}());
