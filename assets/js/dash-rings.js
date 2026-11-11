/* Admin dashboard: the rings (hover or focus a category to see its amount in the middle) and the payment-activity filter. Works without JavaScript: every item is also a link. */
(function () {
  'use strict';
  var root = document.querySelector('[data-dash-pay]');
  if (!root) return;

  var ring = root.querySelector('[data-ring]');
  if (ring) {
    var label = ring.querySelector('[data-ring-label]');
    var value = ring.querySelector('[data-ring-value]');
    var sub = ring.querySelector('[data-ring-sub]');
    var segs = ring.querySelectorAll('[data-ring-seg]');
    var items = root.querySelectorAll('[data-ring-item]');
    var defLabel = label ? label.textContent : '';

    function show(key) {
      var item = root.querySelector('[data-ring-item="' + key + '"]');
      if (!item) return;
      label.textContent = item.getAttribute('data-label');
      value.textContent = item.getAttribute('data-value');
      sub.innerHTML = item.getAttribute('data-sub');
      Array.prototype.forEach.call(segs, function (s) { var on = s.getAttribute('data-ring-seg') === key; s.classList.toggle('is-active', on); s.classList.toggle('is-dim', !on); });
      Array.prototype.forEach.call(items, function (i) { i.classList.toggle('is-active', i === item); });
    }
    function reset() {
      label.textContent = defLabel;
      value.textContent = value.getAttribute('data-default');
      sub.textContent = sub.getAttribute('data-default');
      Array.prototype.forEach.call(segs, function (s) { s.classList.remove('is-active'); s.classList.remove('is-dim'); });
      Array.prototype.forEach.call(items, function (i) { i.classList.remove('is-active'); });
    }
    Array.prototype.forEach.call(items, function (i) {
      var k = i.getAttribute('data-ring-item');
      i.addEventListener('mouseenter', function () { show(k); });
      i.addEventListener('focus', function () { show(k); });
      i.addEventListener('mouseleave', reset);
      i.addEventListener('blur', reset);
    });
    Array.prototype.forEach.call(segs, function (s) {
      var k = s.getAttribute('data-ring-seg');
      function go() { var a = root.querySelector('[data-ring-item="' + k + '"]'); if (a && a.href) window.location.href = a.href; }
      s.addEventListener('mouseenter', function () { show(k); });
      s.addEventListener('focus', function () { show(k); });
      s.addEventListener('mouseleave', reset);
      s.addEventListener('blur', reset);
      s.addEventListener('click', go);
      s.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); } });
    });
  }

  var chips = root.querySelectorAll('[data-feed-filter]');
  var rows = root.querySelectorAll('[data-feed-group]');
  Array.prototype.forEach.call(chips, function (c) {
    c.addEventListener('click', function () {
      var f = c.getAttribute('data-feed-filter');
      Array.prototype.forEach.call(chips, function (x) { x.classList.toggle('is-on', x === c); });
      Array.prototype.forEach.call(rows, function (r) { r.hidden = !(f === 'all' || r.getAttribute('data-feed-group') === f); });
    });
  });
  var dz = document.querySelector('[data-dash-dz]');
  if (dz) {
    var dzChips = dz.querySelectorAll('[data-dz-filter]');
    var dzRows = dz.querySelectorAll('[data-dz-group]');
    Array.prototype.forEach.call(dzChips, function (c) {
      c.addEventListener('click', function () {
        var f = c.getAttribute('data-dz-filter');
        Array.prototype.forEach.call(dzChips, function (x) { x.classList.toggle('is-on', x === c); });
        Array.prototype.forEach.call(dzRows, function (r) { r.hidden = !(f === 'all' || r.getAttribute('data-dz-group') === f); });
      });
    });
  }
})();
