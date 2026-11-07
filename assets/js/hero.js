/* Home hero (partials/hero-photo.php): "Read more / Show less", a highlighted word that cycles, a photo slideshow (arrows, dots, autoplay that pauses on hover or focus,
   keyboard arrows), a gentle tilt that follows the pointer. Everything is optional: without this file the hero shows the first photo and the full text.
   With "reduce motion" nothing moves by itself (no autoplay, no cycling words, no tilt). */
(function () {
  'use strict';
  var hero = document.querySelector('[data-hero]');
  if (!hero) return;
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---- read more / show less */
  var btn = hero.querySelector('[data-hero-toggle]');
  var more = hero.querySelector('[data-hero-more]');
  if (btn && more) {
    var label = btn.querySelector('[data-hero-toggle-text]');
    more.hidden = true;
    btn.addEventListener('click', function () {
      var open = more.hidden;
      more.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (label) label.textContent = btn.getAttribute(open ? 'data-less' : 'data-more') || label.textContent;
    });
  }

  /* ---- the highlighted word takes turns */
  var hl = hero.querySelector('[data-hero-words]');
  if (hl && !calm) {
    var words = [];
    try { words = JSON.parse(hl.getAttribute('data-hero-words')) || []; } catch (e) { words = []; }
    if (words.length > 1) {
      var w = 0;
      hl.classList.add('is-cycling');
      setInterval(function () {
        if (document.hidden) return;
        hl.classList.add('is-out');
        setTimeout(function () {
          w = (w + 1) % words.length;
          hl.textContent = words[w];
          hl.classList.remove('is-out');
        }, 380);
      }, 3200);
    }
  }

  /* ---- the slideshow */
  var stage = hero.querySelector('[data-hero-stage]');
  if (stage) {
    var slides = stage.querySelectorAll('[data-hero-slide]');
    var dots = stage.querySelectorAll('[data-hero-dot]');
    var cur = 0, timer = null;
    var show = function (n) {
      if (!slides.length) return;
      cur = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        s.classList.toggle('is-active', i === cur);
        if (i === cur) s.removeAttribute('aria-hidden'); else s.setAttribute('aria-hidden', 'true');
      });
      dots.forEach(function (d, i) {
        d.classList.toggle('is-active', i === cur);
        d.setAttribute('aria-selected', i === cur ? 'true' : 'false');
      });
    };
    var stop = function () { if (timer) { clearInterval(timer); timer = null; } };
    var play = function () {
      if (calm || slides.length < 2 || timer) return;
      timer = setInterval(function () { if (!document.hidden) show(cur + 1); }, 5500);
    };
    var prev = stage.querySelector('[data-hero-prev]');
    var next = stage.querySelector('[data-hero-next]');
    if (prev) prev.addEventListener('click', function () { show(cur - 1); stop(); });
    if (next) next.addEventListener('click', function () { show(cur + 1); stop(); });
    dots.forEach(function (d) { d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-hero-dot'), 10) || 0); stop(); }); });
    stage.addEventListener('mouseenter', stop);
    stage.addEventListener('mouseleave', play);
    stage.addEventListener('focusin', stop);
    stage.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { show(cur + 1); stop(); } else if (e.key === 'ArrowLeft') { show(cur - 1); stop(); }
    });
    play();

    /* ---- tilt toward the pointer (mouse only, wide screens) */
    var frame = stage.querySelector('[data-hero-tilt]');
    if (frame && !calm && window.matchMedia('(hover: hover) and (min-width: 960px)').matches) {
      stage.addEventListener('mousemove', function (e) {
        var r = stage.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        frame.style.transform = 'perspective(900px) rotateY(' + (x * 7).toFixed(2) + 'deg) rotateX(' + (-y * 6).toFixed(2) + 'deg)';
      });
      stage.addEventListener('mouseleave', function () { frame.style.transform = ''; });
    }
  }
})();
