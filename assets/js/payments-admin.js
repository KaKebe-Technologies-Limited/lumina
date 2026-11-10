/* Admin > Payments: "Send money" helpers (amount chips, network detection, a clear summary before sending). Optional: the form works without it. */
(function () {
  'use strict';
  var form = document.querySelector('[data-pd-send]');
  if (!form) return;
  var phone = form.querySelector('[data-pd-phone]');
  var amount = form.querySelector('[data-pd-amount]');
  var method = form.querySelector('[data-pd-method]');
  var hint = form.querySelector('[data-pd-net]');
  var sum = form.querySelector('[data-pd-sum]');
  var names = { '1': 'MTN', '2': 'Airtel' };

  function local(v) {
    var d = String(v || '').replace(/[^\d]/g, '');
    if (d.indexOf('256') === 0) d = '0' + d.slice(3);
    if (d.length === 9) d = '0' + d;
    return d;
  }
  function detect(v) {
    var p = local(v).slice(0, 3);
    if (['077', '078', '076', '039', '031'].indexOf(p) >= 0) return '1';
    if (['070', '075', '074', '020'].indexOf(p) >= 0) return '2';
    return '';
  }
  function refresh() {
    var net = method.value !== '0' ? method.value : detect(phone.value);
    var ok = /^0\d{9}$/.test(local(phone.value));
    if (hint) hint.textContent = !phone.value ? 'Any MTN or Airtel number. The network is detected from the number.' : (ok ? (net ? 'Detected: ' + names[net] + ' number.' : 'Number accepted: choose the network below.') : 'Enter a Ugandan number such as 0772 123 456.');
    var amt = parseInt(amount.value, 10);
    if (sum) sum.textContent = (ok && amt >= 500) ? 'Yes, send UGX ' + amt.toLocaleString('en-US') + ' to ' + local(phone.value) + (net ? ' (' + names[net] + ')' : '') + ' now' : 'Yes, send this money now';
  }
  Array.prototype.forEach.call(form.querySelectorAll('[data-pd-fill]'), function (b) {
    b.addEventListener('click', function () { amount.value = b.getAttribute('data-pd-fill'); refresh(); amount.focus(); });
  });
  [phone, amount, method].forEach(function (el) { el.addEventListener('input', refresh); el.addEventListener('change', refresh); });
  form.addEventListener('submit', function (e) {
    var amt = parseInt(amount.value, 10);
    if (!window.confirm('Send UGX ' + (isNaN(amt) ? '?' : amt.toLocaleString('en-US')) + ' to ' + phone.value + '? This cannot be undone.')) e.preventDefault();
  });
  refresh();
})();
