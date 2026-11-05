/* Fund a project: the pledge form. Picking a card shows what was chosen, asks "how many?" for a single contribution, shows the amount box for a co-funding share or any amount,
   and totals the price in UGX or USD. The server calculates the amount again from its own price list, so this is only a preview. */
(function () {
  'use strict';
  var form = document.querySelector('[data-fund-form]');
  if (!form) return;
  var rate = parseFloat(form.getAttribute('data-rate')) || 3750;
  var chosen = form.querySelector('[data-fund-chosen]');
  var qtyRow = form.querySelector('[data-fund-qty-row]');
  var qty = form.querySelector('#fund-qty');
  var amountRow = form.querySelector('[data-fund-amount-row]');
  var currency = form.querySelector('[data-fund-currency]');
  var totalBox = form.querySelector('[data-fund-total]');
  var totalVal = form.querySelector('[data-fund-total-value]');
  var radios = form.querySelectorAll('input[name="choice"]');
  var allCurrencies = Array.prototype.map.call(currency.options, function (o) { return o.value; });

  function current() {
    for (var i = 0; i < radios.length; i++) if (radios[i].checked) return radios[i];
    return null;
  }
  function fmt(n, cur) { return cur + ' ' + Math.round(n).toLocaleString('en-US'); }

  function update() {
    var r = current();
    form.querySelectorAll('.fund-card').forEach(function (c) { c.classList.toggle('is-picked', !!c.querySelector('input:checked')); });
    if (!r) { totalBox.hidden = true; return; }
    var free = r.hasAttribute('data-free');
    var isItem = r.hasAttribute('data-item');
    chosen.textContent = r.getAttribute('data-title');
    qtyRow.hidden = !isItem;
    amountRow.hidden = !free;
    // fixed prices exist in UGX and USD only
    Array.prototype.forEach.call(currency.options, function (o) { o.disabled = !free && o.value !== 'UGX' && o.value !== 'USD'; });
    if (currency.options[currency.selectedIndex].disabled) currency.value = 'UGX';
    var cur = currency.value;
    if (free) {
      var min = parseFloat(r.getAttribute('data-min-usd')) || 0;
      totalBox.hidden = !min;
      if (min) {
        var shown = cur === 'UGX' ? min * rate : (cur === 'USD' ? min : null);
        totalVal.textContent = shown === null ? 'from USD ' + min.toLocaleString('en-US') : 'from ' + fmt(shown, cur);
      }
      return;
    }
    var n = isItem ? Math.max(1, Math.min(500, parseInt(qty.value, 10) || 1)) : 1;
    var unit = parseFloat(r.getAttribute(cur === 'USD' ? 'data-usd' : 'data-ugx')) || 0;
    totalBox.hidden = false;
    totalVal.textContent = fmt(unit * n, cur) + (cur === 'UGX' ? ' (about USD ' + Math.round(unit * n / rate).toLocaleString('en-US') + ')' : '');
  }

  Array.prototype.forEach.call(radios, function (r) { r.addEventListener('change', update); });
  qty.addEventListener('input', update);
  currency.addEventListener('change', update);
  update();
  void allCurrencies;
})();
