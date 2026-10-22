/* Ambassadors sign-up (progressive enhancement): the country list follows the chosen region, and the money / in-kind / other parts of the pledge show only when ticked.
   Without JavaScript the whole country list (grouped by region) and all three pledge parts are shown. The server checks both anyway. */
(function () {
  'use strict';
  var form = document.querySelector('[data-amb-form]');
  if (!form) return;

  var region = form.querySelector('[data-amb-region]');
  var country = form.querySelector('[data-amb-country]');
  if (region && country) {
    var options = country.querySelectorAll('option[data-region]');
    var groups = country.querySelectorAll('optgroup[data-region]');
    var filter = function () {
      var r = region.value;
      Array.prototype.forEach.call(options, function (o) {
        var show = r === '' || o.getAttribute('data-region') === r;
        o.hidden = !show;
        o.disabled = !show;
      });
      Array.prototype.forEach.call(groups, function (g) {
        var show = r === '' || g.getAttribute('data-region') === r;
        g.hidden = !show;
        g.disabled = !show;
      });
      var sel = country.options[country.selectedIndex];
      if (sel && sel.disabled) country.value = '';
    };
    region.addEventListener('change', filter);
    country.addEventListener('change', function () {
      var o = country.options[country.selectedIndex];
      if (o && o.getAttribute('data-region') && region.value === '') {
        region.value = o.getAttribute('data-region');
        filter();
      }
    });
    filter();
  }

  var boxes = form.querySelectorAll('[data-amb-pledge]');
  var panels = form.querySelectorAll('[data-amb-panel]');
  var sync = function () {
    Array.prototype.forEach.call(panels, function (p) {
      var key = p.getAttribute('data-amb-panel');
      var cb = form.querySelector('[data-amb-pledge="' + key + '"]');
      p.hidden = !(cb && cb.checked);
    });
  };
  Array.prototype.forEach.call(boxes, function (b) { b.addEventListener('change', sync); });
  sync();
})();
