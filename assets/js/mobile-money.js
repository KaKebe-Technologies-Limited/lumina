/* Pay with mobile money (Daraza): sends the phone number, then WAITS for the answer (the request is held open until the payer approves on the phone: up to 5 minutes) while
   asking the server every few seconds where the payment stands, so the page also moves on if the long request is cut off. Nothing the browser says decides the result: the server does. */
(function () {
  'use strict';
  var box = document.querySelector('[data-mm]');
  if (!box) return;
  var form = box.querySelector('[data-mm-form]');
  var wait = box.querySelector('[data-mm-wait]');
  var done = box.querySelector('[data-mm-done]');
  var err = box.querySelector('[data-mm-error]');
  var timerEl = box.querySelector('[data-mm-timer]');
  var phone = box.querySelector('[data-mm-phone]');
  var nets = box.querySelectorAll('[data-mm-net]');
  var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var timer = null, poll = null, started = 0, finished = false;

  function networkOf(v) {
    var d = String(v || '').replace(/[^0-9+]/g, '').replace(/^\+?256/, '0');
    var p = d.slice(0, 3);
    if (['077', '078', '076', '039'].indexOf(p) >= 0) return '1';
    if (['070', '075', '074', '020'].indexOf(p) >= 0) return '2';
    return '';
  }
  function syncNet() {
    var n = networkOf(phone && phone.value);
    nets.forEach(function (r) { if (n && r.value === n) r.checked = true; });
  }
  if (phone) { phone.addEventListener('input', syncNet); syncNet(); }

  function showError(msg) {
    if (!err) return;
    err.textContent = msg; err.hidden = !msg;
  }
  function stopWaiting() {
    clearInterval(timer); clearInterval(poll);
    if (wait) wait.hidden = true;
  }
  function paid() {
    if (finished) return;
    finished = true;
    stopWaiting();
    if (form) form.hidden = true;
    if (done) done.hidden = false;
    var next = box.getAttribute('data-next');
    setTimeout(function () { if (next) window.location.href = next; }, 2500);
  }
  function tick() {
    var s = Math.floor((Date.now() - started) / 1000);
    if (timerEl) timerEl.textContent = 'Waiting for ' + Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
  }
  function startPolling() {
    poll = setInterval(function () {
      fetch(box.getAttribute('data-status'), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (j) { if (j.state === 'paid') paid(); })
        .catch(function () { /* the next try will do */ });
    }, 5000);
  }

  if (box.getAttribute('data-paid') === '1') { paid(); return; }
  if (!form) return;
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    showError('');
    var method = '';
    nets.forEach(function (r) { if (r.checked) method = r.value; });
    if (!method) method = networkOf(phone.value);
    if (!phone.value.trim() || !method) { showError('Type your MTN or Airtel number and choose the network.'); return; }
    form.hidden = true;
    wait.hidden = false;
    started = Date.now();
    tick(); timer = setInterval(tick, 1000);
    startPolling();
    var body = new URLSearchParams();
    body.set('phone', phone.value.trim());
    body.set('method', method);
    body.set('_token', token);
    fetch(box.getAttribute('data-run'), { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j.state === 'paid') { paid(); return; }
        if (j.state === 'busy') return;                                   // another request of the same payment is waiting: the polling will see the result
        stopWaiting();
        form.hidden = false;
        showError(j.state === 'unknown'
          ? 'We did not get an answer in time. If you approved the payment on your phone, please wait a minute and refresh this page: it will show as paid.'
          : (j.message || 'The payment did not go through. You were not charged.'));
      })
      .catch(function () {
        // the connection was cut while waiting: the server keeps going, the polling shows the result
        showError('');
      });
  });
})();
