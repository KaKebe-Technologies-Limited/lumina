/* PUBLIC SHOP behaviour (STOREFRONT; docs/SHOP.md). Progressive enhancement only: every page works without JavaScript (plain forms, plain links).
 * No inline JS (CSP script-src 'self'). Hooks (data attributes):
 *   form[data-shop-add]        add to cart over fetch (POST shop/cart/add, Accept: application/json). "Book" buttons and any failure fall back to the normal form post.
 *   [data-shop-count]          cart count (header link + catalogue button) updated after an add / cart change
 *   [data-shop-live]           polite live region for "Added to your cart" / "Cart updated"
 *   [data-shop-gallery]        thumbnails switch the large picture; the zoom button opens the shared lightbox (main.js) on the current picture
 *   [data-shop-qty]            - / + buttons around a number input (min 1 or 0, max from data-max)
 *   [data-shop-cart]           quantity change, remove and delivery choice are sent in the background, then the fresh cart replaces the old one
 *   [data-shop-checkout]       address only for delivery, totals follow the delivery choice, button text for an online payment, focus on the error summary
 *   [data-shop-copy]           copy a link / number to the clipboard      [data-shop-print]  print button on the receipt
 */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------------------------------------------------------------- live region + cart count
  function announce(text) {
    var live = $('[data-shop-live]');
    if (!live) {
      live = document.createElement('div');
      live.className = 'visually-hidden';
      live.setAttribute('role', 'status');
      live.setAttribute('aria-live', 'polite');
      live.setAttribute('data-shop-live', '');
      document.body.appendChild(live);
    }
    live.textContent = '';
    window.setTimeout(function () { live.textContent = text; }, 40);
  }

  function setCount(n) {
    n = Math.max(0, parseInt(n, 10) || 0);
    $$('[data-shop-count]').forEach(function (el) { el.textContent = String(n); });
    var link = $('[data-shop-cart-link]');
    if (link) {
      link.hidden = n === 0;
      var tpl = (n === 1 && link.getAttribute('data-aria-one')) ? link.getAttribute('data-aria-one') : (link.getAttribute('data-aria') || '');
      if (tpl) { link.setAttribute('aria-label', tpl.replace('{n}', String(n))); }
    }
  }

  // ---------------------------------------------------------------- quantity steppers
  function initQty(root) {
    $$('[data-shop-qty]', root).forEach(function (box) {
      if (box.getAttribute('data-ready')) { return; }
      box.setAttribute('data-ready', '1');
      var input = $('input[type="number"]', box);
      var minus = $('[data-qty-minus]', box);
      var plus = $('[data-qty-plus]', box);
      if (!input || !minus || !plus) { return; }
      var min = parseInt(input.getAttribute('min') || '1', 10);
      var max = parseInt(box.getAttribute('data-max') || input.getAttribute('max') || '99', 10);
      minus.hidden = false;
      plus.hidden = false;
      function clamp() {
        var v = parseInt(input.value, 10);
        if (isNaN(v)) { v = min < 1 ? 1 : min; }
        v = Math.min(max, Math.max(min, v));
        return v;
      }
      function sync() {
        var v = parseInt(input.value, 10);
        minus.disabled = !isNaN(v) && v <= min;
        plus.disabled = !isNaN(v) && v >= max;
      }
      function step(d) {
        var v = clamp() + d;
        input.value = String(Math.min(max, Math.max(min, v)));
        sync();
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
      minus.addEventListener('click', function () { step(-1); });
      plus.addEventListener('click', function () { step(1); });
      input.addEventListener('input', sync);
      input.addEventListener('blur', function () { if (input.value !== '') { input.value = String(clamp()); sync(); } });
      sync();
    });
  }

  // ---------------------------------------------------------------- add to cart
  function clearErrors(form) {
    var box = $('[data-shop-error]', form);
    $$('.sh-opt', form).forEach(function (g) { g.classList.remove('has-error'); g.removeAttribute('aria-describedby'); });
    if (box) { box.hidden = true; }
  }

  function showError(form, message, errors) {
    var box = $('[data-shop-error]', form);
    $$('.sh-opt', form).forEach(function (g) { g.classList.remove('has-error'); g.removeAttribute('aria-describedby'); });
    if (errors) {
      Object.keys(errors).forEach(function (k) {
        var g = $('.sh-opt[data-opt-group="' + k + '"]', form);
        if (g) {
          g.classList.add('has-error');
          if (box && box.id) { g.setAttribute('aria-describedby', box.id); }
        }
      });
    }
    if (box) {
      box.textContent = message;
      box.hidden = false;
      box.setAttribute('tabindex', '-1');
      box.focus();
    } else {
      announce(message);
    }
  }

  // "Size, Colour and Placement" (the word "and" is editable wording: data-msg-and)
  function joinNames(names, and) {
    if (names.length < 2) { return names.join(''); }
    return names.slice(0, -1).join(', ') + ' ' + (and || '&') + ' ' + names[names.length - 1];
  }

  function initAdd() {
    $$('form[data-shop-add]').forEach(function (form) {
      // choosing the missing option clears its error at once (the message would be wrong otherwise)
      form.addEventListener('change', function (ev) {
        var g = ev.target.closest ? ev.target.closest('.sh-opt') : null;
        if (!g || !g.classList.contains('has-error')) { return; }
        g.classList.remove('has-error');
        g.removeAttribute('aria-describedby');
        if (!$('.sh-opt.has-error', form)) { clearErrors(form); }
      });
      form.addEventListener('submit', function (e) {
        var submitter = e.submitter || null;
        if (submitter && submitter.name === 'mode' && submitter.value === 'book') { return; }          // booking goes through the normal post (and on to the checkout)
        if (!window.fetch || !window.FormData) { return; }
        var isProduct = form.hasAttribute('data-shop-product');
        // required option groups: say ALL the missing ones at once, before anything is sent
        if (isProduct) {
          var tpl = form.getAttribute('data-msg-option') || 'Please choose {option}.';
          var groups = $$('.sh-opt', form);
          var missing = [];
          var names = [];
          for (var i = 0; i < groups.length; i++) {
            var g = groups[i];
            if ($('input[required]', g) && !$('input:checked', g)) {
              missing.push(g);
              names.push(g.getAttribute('data-opt-label') || '');
            }
          }
          if (missing.length) {
            e.preventDefault();
            var errs = {};
            missing.forEach(function (m) { errs[m.getAttribute('data-opt-group')] = 'missing'; });
            showError(form, tpl.replace('{option}', joinNames(names, form.getAttribute('data-msg-and'))), errs);
            var first = $('input:not([disabled])', missing[0]);
            if (first) { first.focus(); }
            return;
          }
        }
        e.preventDefault();
        var btn = submitter || $('button[type="submit"]', form);
        // aria-disabled, not disabled: a disabled button that has focus drops the keyboard user to the top of the page
        if (btn && btn.getAttribute('aria-disabled') === 'true') { return; }
        var lab = btn ? $('span:not(.visually-hidden)', btn) : null;           // the visible words of a catalogue button (the icon and the hidden product name stay)
        var oldText = lab ? lab.textContent : '';
        if (btn) { btn.setAttribute('aria-disabled', 'true'); btn.setAttribute('aria-busy', 'true'); }
        var data = new FormData(form);
        if (!data.has('mode')) { data.append('mode', 'cart'); }
        fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
          .then(function (res) {
            if (btn && !(res.status === 200 && res.json && res.json.ok && lab)) { btn.removeAttribute('aria-disabled'); }
            if (btn) { btn.removeAttribute('aria-busy'); }
            var j = res.json || {};
            if (res.status === 200 && j.ok) {
              setCount(j.units);
              var name = form.getAttribute('data-name') || '';
              var msg = (j.message || '').replace('{name}', name);
              announce(msg);
              var added = $('[data-shop-added]', form);
              if (added) {
                if (btn) { btn.removeAttribute('aria-disabled'); }
                $('[data-shop-added-text]', added).textContent = msg;
                added.hidden = false;
                var errBox = $('[data-shop-error]', form);
                if (errBox) { errBox.hidden = true; }
              } else if (btn && lab) {
                // a short fixed confirmation (the product name is already in the live message): the button keeps its height, so the grid does not jump
                btn.classList.add('is-added');
                btn.style.minHeight = btn.offsetHeight + 'px';                // keep the height while the words change
                lab.textContent = '\u2713 ' + (form.getAttribute('data-added-label') || '');
                window.setTimeout(function () { lab.textContent = oldText; btn.style.minHeight = ''; btn.classList.remove('is-added'); btn.removeAttribute('aria-disabled'); }, 2200);
              }
              return;
            }
            if (res.status === 422 && j.message) {
              showError(form, j.message, j.errors);
              return;
            }
            form.submit();                    // anything unexpected (expired token, server error): let the normal post show the proper page
          })
          .catch(function () {
            if (btn) { btn.removeAttribute('aria-disabled'); btn.removeAttribute('aria-busy'); }
            form.submit();
          });
      });
    });
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
  }

  // ---------------------------------------------------------------- gallery
  function initGallery() {
    var g = $('[data-shop-gallery]');
    if (!g) { return; }
    var frame = $('[data-shop-stage]', g);
    var thumbs = $$('[data-shop-thumb]', g);
    var zoom = $('[data-shop-zoom]', g);
    if (!frame || !thumbs.length) { return; }
    var current = thumbs[0];
    var passThrough = false;
    function show(t) {
      var img = $('img', frame);
      var src = $('source', frame);
      if (!img) { return; }
      if (t.getAttribute('data-src')) { img.src = t.getAttribute('data-src'); }
      if (t.getAttribute('data-srcset')) { img.srcset = t.getAttribute('data-srcset'); } else { img.removeAttribute('srcset'); }
      img.alt = t.getAttribute('data-alt') || '';
      if (src) { if (t.getAttribute('data-webp')) { src.srcset = t.getAttribute('data-webp'); } }
      thumbs.forEach(function (x) { x.classList.toggle('is-current', x === t); if (x === t) { x.setAttribute('aria-current', 'true'); } else { x.removeAttribute('aria-current'); } });
      current = t;
    }
    // capture phase: a thumbnail click switches the picture instead of opening the lightbox (main.js listens on the link itself)
    g.addEventListener('click', function (e) {
      var t = e.target.closest('[data-shop-thumb]');
      if (!t || passThrough || t.hasAttribute('data-shop-single')) { return; }
      e.preventDefault();
      e.stopPropagation();
      show(t);
    }, true);
    if (zoom) {
      zoom.hidden = false;
      zoom.addEventListener('click', function () {
        passThrough = true;
        current.click();                    // the shared lightbox (main.js) opens on the current picture and offers previous / next
        passThrough = false;
      });
    }
    frame.addEventListener('click', function () { if (zoom) { zoom.click(); } });
    frame.style.cursor = 'zoom-in';
  }

  // ---------------------------------------------------------------- copy + print + share
  function initCopy() {
    var can = !!(navigator.clipboard && navigator.clipboard.writeText) || !!document.queryCommandSupported;
    $$('[data-shop-copy]').forEach(function (btn) {
      if (can) { btn.hidden = false; }
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-shop-copy') || '';
        var done = function () {
          var label = $('[data-copy-text]', btn);
          var done = btn.getAttribute('data-copied') || '';
          var wrap = btn.closest('[data-shop-share]') || btn.parentNode;
          var status = $('[data-copy-status]', wrap) || null;
          if (label && done) {
            var orig = label.textContent;
            label.textContent = done;
            window.setTimeout(function () { label.textContent = orig; }, 2000);
          }
          if (status) { status.textContent = done; } else { announce(done); }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done, function () { legacy(text, done); });
        } else { legacy(text, done); }
      });
    });
    var pr = $('[data-shop-print]');
    if (pr) { pr.hidden = false; pr.addEventListener('click', function () { window.print(); }); }
  }
  function legacy(text, done) {
    var ta = document.createElement('textarea');
    ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select();
    try { if (document.execCommand('copy')) { done(); } } catch (err) { /* nothing to do */ }
    document.body.removeChild(ta);
  }

  // ---------------------------------------------------------------- cart page
  function initCart() {
    var root = $('[data-shop-cart]');
    if (!root || !window.fetch) { return; }
    var updatedText = root.getAttribute('data-updated') || '';
    var timer = null;

    function swap(html, focusSel, altSel) {
      var doc = new DOMParser().parseFromString(html, 'text/html');
      var fresh = $('[data-shop-cart]', doc);
      if (!fresh) { window.location.reload(); return; }
      root.innerHTML = fresh.innerHTML;
      var n = $('[data-shop-count]', doc);
      var hdr = doc.querySelector('[data-shop-cart-link] [data-shop-count]');
      setCount(hdr ? hdr.textContent : (n ? n.textContent : 0));
      initQty(root);
      announce(updatedText);
      var f = focusSel ? $(focusSel, root) : null;
      if (f && (f.disabled || f.offsetParent === null) && altSel) { f = $(altSel, root); }                       // the button reached its limit: the number field instead
      if (f && f.offsetParent === null) { f = null; }                                                           // never aim at a hidden control (it would silently keep nothing focused)
      if (f) { f.focus(); if (f.select && f.tagName === 'INPUT') { try { f.select(); } catch (e2) { /* ignore */ } } }
      else {
        var es = $('.empty-state', root);
        var h = es || $('.sh-line__name', root) || $('.sh-summary__title', root);
        if (h) { h.setAttribute('tabindex', '-1'); h.focus(); }
      }
    }
    function send(form, submitter, focusSel, altSel) {
      var data = new FormData(form, submitter || undefined);
      return fetch(submitter && submitter.getAttribute('formaction') ? submitter.getAttribute('formaction') : form.action, { method: 'POST', body: data, credentials: 'same-origin' })          // no JSON header: the controller redirects back to the cart page, whose HTML replaces the old cart
        .then(function (r) { if (!r.ok) { throw new Error('bad'); } return r.text(); })
        .then(function (html) { swap(html, focusSel, altSel); })
        .catch(function (err) { if (window.console) { console.error('shop cart update failed', err); } form.submit(); });
    }

    var stepper = null;                                          // which + / - button the last quantity change came from (focus goes back to it)
    root.addEventListener('change', function (e) {
      var input = e.target.closest('[data-shop-cart-qty]');
      if (input) {
        window.clearTimeout(timer);
        timer = window.setTimeout(function () {
          var line = input.closest('[data-line]');
          var sel = stepper && line ? '[data-line="' + line.getAttribute('data-line') + '"] [data-qty-' + stepper + ']' : '#' + input.id;
          stepper = null;
          send(input.form, null, sel, '#' + input.id);
        }, 450);
        return;
      }
      var radio = e.target.closest('[data-shop-delivery-form] input[type="radio"]');
      if (radio) { send(radio.form, null, 'input[name="delivery"]:checked'); }
    });
    root.addEventListener('click', function (e) {
      var st = e.target.closest('[data-qty-plus], [data-qty-minus]');
      if (st) { stepper = st.hasAttribute('data-qty-plus') ? 'plus' : 'minus'; return; }
      var rm = e.target.closest('.sh-line__remove, .sh-line--problem button[formaction]');
      if (rm && rm.form) {
        e.preventDefault();
        // after a removal the focus goes to the first remaining remove button, else the "continue shopping" link (the empty cart gets its own focus target)
        send(rm.form, rm, '.sh-line__remove, .sh-line--problem button[formaction], .sh-cart__continue a', null);
      }
    });
    root.addEventListener('submit', function (e) {
      var f = e.target.closest('[data-shop-cart-form]');
      if (f && !e.defaultPrevented) { e.preventDefault(); send(f, e.submitter || null, null); }
    });
  }

  // ---------------------------------------------------------------- checkout
  function initCheckout() {
    var form = $('[data-shop-checkout]');
    if (!form) { return; }
    var address = $('[data-shop-address]', form);
    var fee = $('[data-shop-fee]');
    var total = $('[data-shop-total]');
    var submit = $('[data-shop-submit]', form);
    var text = submit ? $('[data-shop-submit-text]', submit) : null;

    function syncDelivery() {
      var r = $('input[data-shop-delivery]:checked', form);
      $$('input[data-shop-delivery]', form).forEach(function (x) { var lab = x.closest('.sh-pick'); if (lab) { lab.classList.toggle('is-on', x.checked); } });
      if (address && r) { address.hidden = r.value !== 'delivery'; }
      if (r && fee && r.hasAttribute('data-fee')) { fee.textContent = r.getAttribute('data-fee'); }
      if (r && total && r.hasAttribute('data-total')) { total.textContent = r.getAttribute('data-total'); }
    }
    function syncPay() {
      var r = $('input[data-shop-pay]:checked', form);
      $$('input[data-shop-pay]', form).forEach(function (x) { var wrap = x.closest('.sh-pay'); if (wrap) { wrap.classList.toggle('is-on', x.checked); } });
      if (text && submit) { text.textContent = r && r.value === 'flutterwave' ? submit.getAttribute('data-label-pay') : submit.getAttribute('data-label'); }
    }
    form.addEventListener('change', function (e) {
      if (e.target.matches('input[data-shop-delivery]')) { syncDelivery(); }
      if (e.target.matches('input[data-shop-pay]')) { syncPay(); }
    });
    syncDelivery();
    syncPay();

    var summary = $('[data-shop-errors]', form);
    if (summary) { summary.focus(); if (!reduce && summary.scrollIntoView) { summary.scrollIntoView({ block: 'center' }); } }

    form.addEventListener('submit', function () {
      if (!submit || submit.disabled) { return; }
      window.setTimeout(function () { submit.disabled = true; submit.setAttribute('aria-busy', 'true'); }, 0);      // one click, one order
    });
    window.addEventListener('pageshow', function (e) { if (e.persisted && submit) { submit.disabled = false; submit.removeAttribute('aria-busy'); } });
  }

  function init() {
    initQty(document);
    initAdd();
    initGallery();
    initCopy();
    initCart();
    initCheckout();
  }
  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
}());
