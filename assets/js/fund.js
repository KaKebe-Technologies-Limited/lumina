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
  var amountInput = form.querySelector('#fund-amount');
  var giveLabel = form.querySelector('[data-fund-give]');
  var bigNote = form.querySelector('[data-fund-big]');
  var MAX_UGX = 5000000;
  var autoFilled = false;
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
    var total = null;      // the amount in the chosen currency
    if (free) {
      var min = parseFloat(r.getAttribute('data-min-usd')) || 0;
      totalBox.hidden = !min;
      if (min) {
        var shown = cur === 'UGX' ? min * rate : (cur === 'USD' ? min : null);
        totalVal.textContent = shown === null ? 'from USD ' + min.toLocaleString('en-US') : 'from ' + fmt(shown, cur);
        // the amount is picked for you: the smallest share, which you can raise
        if (shown !== null && amountInput && (amountInput.value === '' || autoFilled)) { amountInput.value = String(Math.round(shown)); autoFilled = true; }
      } else if (autoFilled && amountInput) {
        amountInput.value = ''; autoFilled = false;
      }
      var typed = amountInput ? parseFloat(String(amountInput.value).replace(/[^0-9.]/g, '')) : NaN;
      total = isNaN(typed) ? null : typed;
    } else {
      if (autoFilled && amountInput) { amountInput.value = ''; autoFilled = false; }
      var n = isItem ? Math.max(1, Math.min(500, parseInt(qty.value, 10) || 1)) : 1;
      var unit = parseFloat(r.getAttribute(cur === 'USD' ? 'data-usd' : 'data-ugx')) || 0;
      totalBox.hidden = false;
      totalVal.textContent = fmt(unit * n, cur) + (cur === 'UGX' ? ' (about USD ' + Math.round(unit * n / rate).toLocaleString('en-US') + ')' : '');
      total = unit * n;
    }
    // the button says what will be paid; a gift above one mobile-money payment is flagged
    if (giveLabel) giveLabel.textContent = total ? 'Give ' + fmt(total, cur) + ' now' : 'Give now';
    if (bigNote) {
      var ugx = cur === 'UGX' ? total : (cur === 'USD' && total ? total * rate : null);
      bigNote.hidden = !(ugx && ugx > MAX_UGX);
    }
  }

  Array.prototype.forEach.call(radios, function (r) { r.addEventListener('change', update); });
  qty.addEventListener('input', update);
  currency.addEventListener('change', update);
  if (amountInput) amountInput.addEventListener('input', function () { autoFilled = false; update(); });
  update();
  void allCurrencies;
})();
