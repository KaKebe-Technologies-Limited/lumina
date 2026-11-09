/* Admin > Academy > Statistics > "Share these numbers on social media": each network button opens that network's own share window with the post (as edited in the box) and the
   dashboard link filled in; the copy button puts the post on the clipboard. Nothing is sent from here and nothing is posted until the person confirms in the network's window. */
(function () {
  'use strict';
  var box = document.querySelector('[data-share-box]');
  if (!box) return;
  var area = box.querySelector('[data-share-caption]');
  if (!area) return;
  var link = area.getAttribute('data-share-link') || '';
  var counter = box.querySelector('[data-share-count]');

  function xLength(text) {                     // X counts every address as 23 characters
    return text.replace(/https?:\/\/\S+/g, 'x'.repeat(23)).length;
  }
  function update() {
    if (!counter) return;
    var n = xLength(area.value);
    counter.textContent = String(area.value.length);
    counter.classList.toggle('is-over', n > 280);
  }
  area.addEventListener('input', update);
  update();

  box.querySelectorAll('[data-share-to]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tpl = btn.getAttribute('data-share-to') || '';
      var text = area.value;
      // facebook / telegram / linkedin take the link separately; x and whatsapp need it inside the text (it already is)
      var url = tpl.replace('{url}', encodeURIComponent(link)).replace('{text}', encodeURIComponent(text));
      window.open(url, '_blank', 'noopener,noreferrer,width=640,height=720');
    });
  });

  var copy = box.querySelector('[data-share-copy]');
  if (copy) {
    var label = copy.querySelector('[data-share-copy-label]');
    copy.addEventListener('click', function () {
      var done = function () {
        if (!label) return;
        label.textContent = 'Copied';
        setTimeout(function () { label.textContent = 'Copy the post'; }, 2000);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(area.value).then(done, function () { area.select(); document.execCommand('copy'); done(); });
      } else {
        area.select();
        document.execCommand('copy');
        done();
      }
    });
  }
})();
