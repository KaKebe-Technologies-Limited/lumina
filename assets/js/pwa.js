/* Install the website as an app: registers the service worker, shows the "Install" button/banner, iPhone help. Settings: Admin > Settings > Install as an app. */
(function () {
  'use strict';
  var root = document.querySelector('[data-pwa]');
  if (!root) return;

  var swUrl = root.getAttribute('data-sw');
  var scope = root.getAttribute('data-scope') || '/';
  var KEY = 'lumina_pwa_banner_closed';
  var buttons = document.querySelectorAll('[data-pwa-install]');
  var banner = document.querySelector('[data-pwa-banner]');
  var dialog = document.querySelector('[data-pwa-ios]');
  var deferred = null;

  function standalone() {
    return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
  }
  function isIos() {
    var ua = window.navigator.userAgent || '';
    return /iphone|ipad|ipod/i.test(ua) || (ua.indexOf('Mac') > -1 && 'ontouchend' in document);
  }
  function show(list, on) { Array.prototype.forEach.call(list, function (el) { el.hidden = !on; }); }
  function closedRecently() {
    try { var t = parseInt(localStorage.getItem(KEY) || '0', 10); return t && Date.now() - t < 30 * 864e5; } catch (e) { return false; }
  }

  if ('serviceWorker' in navigator && swUrl) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register(swUrl, { scope: scope }).catch(function () { /* the site works without it */ });
    });
  }

  if (standalone()) return;

  function update() {
    var can = deferred !== null || isIos();
    show(buttons, can);
    if (banner) banner.hidden = !(can && !closedRecently());
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    update();
  });
  window.addEventListener('appinstalled', function () {
    deferred = null;
    show(buttons, false);
    if (banner) banner.hidden = true;
  });

  function install() {
    if (deferred) {
      var d = deferred;
      deferred = null;
      d.prompt();
      d.userChoice.then(update, update);
    } else if (isIos() && dialog && typeof dialog.showModal === 'function') {
      dialog.showModal();
    }
  }
  Array.prototype.forEach.call(document.querySelectorAll('[data-pwa-install], [data-pwa-go]'), function (b) { b.addEventListener('click', install); });

  var x = document.querySelector('[data-pwa-dismiss]');
  if (x) x.addEventListener('click', function () {
    try { localStorage.setItem(KEY, String(Date.now())); } catch (e) { /* ignore */ }
    banner.hidden = true;
  });
  var close = document.querySelector('[data-pwa-ios-close]');
  if (close && dialog) close.addEventListener('click', function () { dialog.close(); });

  update();
})();
