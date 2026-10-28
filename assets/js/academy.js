/* LAF Academy (public): quiz progress + timer, confirm on unanswered questions, print the certificate page. */
(function () {
  'use strict';

  // on phones and tablets the course menu starts closed so the lesson is the first thing on screen
  var side = document.querySelector('.ac-side__box');
  if (side && window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) side.removeAttribute('open');

  var form = document.querySelector('[data-ac-quiz]');
  if (form) {
    var questions = form.querySelectorAll('[data-ac-question]');
    var counter = form.querySelector('[data-ac-answered]');
    var submitting = false;

    var answered = function () {
      var n = 0;
      questions.forEach(function (q) { if (q.querySelector('input:checked')) n++; });
      return n;
    };
    var update = function () { if (counter) counter.textContent = String(answered()); };
    form.addEventListener('change', update);
    update();

    form.addEventListener('submit', function (e) {
      if (submitting) return;
      var missing = questions.length - answered();
      if (missing > 0 && form.hasAttribute('data-confirm-unanswered') && !form.hasAttribute('data-ac-timeup')) {
        if (!window.confirm(missing + (missing === 1 ? ' question is' : ' questions are') + ' not answered. Submit anyway?')) {
          e.preventDefault();
          var first = null;
          questions.forEach(function (q) { if (!first && !q.querySelector('input:checked')) first = q; });
          if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }
      }
      submitting = true;
    });

    var timer = document.querySelector('[data-ac-timer]');
    if (timer) {
      var left = parseInt(timer.getAttribute('data-ac-timer'), 10) || 0;
      var out = timer.querySelector('[data-ac-time]');
      var pad = function (n) { return (n < 10 ? '0' : '') + n; };
      var tick = function () {
        if (out) out.textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
        if (left <= 60) timer.classList.add('is-low');
        if (left <= 0) {
          window.clearInterval(handle);
          form.setAttribute('data-ac-timeup', '1');
          if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
          return;
        }
        left--;
      };
      var handle = window.setInterval(tick, 1000);
      tick();
    }
  }

  Array.prototype.forEach.call(document.querySelectorAll('form[data-confirm]'), function (f) {
    f.addEventListener('submit', function (e) { if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
  });

  var print = document.querySelector('[data-ac-print]');
  if (print) print.addEventListener('click', function () { window.print(); });
})();

/* LAF Academy sign-up: show the chosen picture, or take a selfie with the camera (the picture is saved with the account, private). */
(function () {
  'use strict';
  var box = document.querySelector('[data-selfie]');
  if (!box) return;
  var input = box.querySelector('input[type=file]');
  var open = box.querySelector('[data-selfie-open]');
  var cam = box.querySelector('[data-selfie-cam]');
  var video = cam ? cam.querySelector('video') : null;
  var snap = box.querySelector('[data-selfie-snap]');
  var cancel = box.querySelector('[data-selfie-cancel]');
  var preview = box.querySelector('[data-selfie-preview]');
  var msg = box.querySelector('[data-selfie-msg]');
  var stream = null;
  var say = function (t) { if (msg) msg.textContent = t; };
  var show = function (file) {
    if (!preview || !file || !window.URL || !URL.createObjectURL) return;
    preview.src = URL.createObjectURL(file);
    preview.hidden = false;
  };
  var stop = function () {
    if (stream) stream.getTracks().forEach(function (t) { t.stop(); });
    stream = null;
    if (video) video.srcObject = null;
    if (cam) cam.hidden = true;
  };
  input.addEventListener('change', function () { if (input.files && input.files[0]) show(input.files[0]); });
  if (!open || !video || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.DataTransfer || !window.File) return;
  open.hidden = false;
  open.addEventListener('click', function () {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 720 } }, audio: false }).then(function (s) {
      stream = s;
      video.srcObject = s;
      cam.hidden = false;
      say('Look at the camera and press "Take the photo".');
    }).catch(function () {
      say('The camera could not be opened (allow it in the browser, or choose a picture from your device instead).');
    });
  });
  if (cancel) cancel.addEventListener('click', function () { stop(); say('You can still attach a picture from your device.'); });
  if (snap) snap.addEventListener('click', function () {
    var w = video.videoWidth, h = video.videoHeight;
    if (!w || !h) return;
    var side = Math.min(w, h);
    var out = Math.min(side, 720);
    var canvas = document.createElement('canvas');
    canvas.width = out;
    canvas.height = out;
    canvas.getContext('2d').drawImage(video, (w - side) / 2, (h - side) / 2, side, side, 0, 0, out, out);
    canvas.toBlob(function (blob) {
      if (!blob) { say('The photo could not be taken. Please choose a picture from your device.'); return; }
      var file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
      var dt = new DataTransfer();
      dt.items.add(file);
      input.files = dt.files;
      show(file);
      stop();
      say('Your selfie is ready. It will be saved with your account.');
    }, 'image/jpeg', 0.92);
  });
  window.addEventListener('pagehide', stop);
})();
