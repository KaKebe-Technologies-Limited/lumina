/* Language switcher. The site is written in English; any other language is produced by Google's website translator, which is
   loaded only after a visitor picks a language (nothing is sent to Google before that). The choice lives in the "googtrans"
   cookie that the translator itself reads, so it follows the visitor from page to page. */
(function () {
  'use strict';
  var root = document.querySelector('[data-translate]');
  if (!root) return;
  var btn = root.querySelector('[data-translate-btn]');
  var menu = root.querySelector('[data-translate-menu]');
  var label = root.querySelector('[data-translate-current]');
  var items = Array.prototype.slice.call(menu.querySelectorAll('[data-lang]'));
  var LANGS = items.map(function (b) { return b.getAttribute('data-lang'); });

  function readCookie() {
    var m = document.cookie.match(/(?:^|;\s*)googtrans=\/[^/;]*\/([^;]+)/);
    var code = m ? decodeURIComponent(m[1]) : 'en';
    return LANGS.indexOf(code) > -1 ? code : 'en';
  }

  function writeCookie(code) {
    var host = location.hostname;
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    // the translator reads the cookie on the host and on the bare domain, so both copies are set or cleared together
    var domains = ['', host, '.' + host.replace(/^www\./, '')];
    domains.forEach(function (d) {
      var base = 'googtrans=' + (code === 'en' ? '' : '/en/' + code) + '; path=/' + (d ? '; domain=' + d : '') + '; SameSite=Lax' + secure;
      document.cookie = base + (code === 'en' ? '; expires=Thu, 01 Jan 1970 00:00:00 GMT' : '; max-age=31536000');
    });
  }

  function loadTranslator() {
    window.luminaTranslateInit = function () {
      /* global google */
      new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'sv,es,zh-CN,fr,ru',
        autoDisplay: false
      }, 'google_translate_element');
    };
    var s = document.createElement('script');
    s.src = 'https://translate.google.com/translate_a/element.js?cb=luminaTranslateInit';
    s.async = true;
    document.head.appendChild(s);
  }

  function setOpen(open) {
    menu.hidden = !open;
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  var current = readCookie();
  function mark() {
    items.forEach(function (b) {
      var on = b.getAttribute('data-lang') === current;
      b.setAttribute('aria-checked', on ? 'true' : 'false');
      b.classList.toggle('is-active', on);
      if (on) label.textContent = b.textContent;
    });
  }
  mark();
  if (current !== 'en') {
    document.documentElement.classList.add('is-translated');
    loadTranslator();
  }

  btn.addEventListener('click', function () { setOpen(menu.hidden); });
  document.addEventListener('click', function (e) { if (!root.contains(e.target)) setOpen(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !menu.hidden) { setOpen(false); btn.focus(); }
  });
  menu.addEventListener('click', function (e) {
    var b = e.target.closest('[data-lang]');
    if (!b) return;
    var code = b.getAttribute('data-lang');
    setOpen(false);
    if (code === current) return;
    writeCookie(code);
    location.reload();
  });
})();
