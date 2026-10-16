/* Runs synchronously in <head> (CSP forbids inline scripts): marks the page as JS-enabled so CSS can enable
   progressive-enhancement states (scroll-reveal etc.) without a flash of unstyled content. */
document.documentElement.classList.add('js');
