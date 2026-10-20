/* ADMIN SHOP behaviour (docs/SHOP.md). Progressive enhancement: every action is also a plain form post. No inline JS (CSP script-src 'self').
 * Hooks:
 *   [data-autosubmit-check]          a filter checkbox submits its form when changed
 *   a[href="#print"]                 print button on the packing slip / receipt
 *   [data-track-stock] + [data-stock-fields]     show the stock fields only when stock is counted
 *   [data-opts] ...                  option groups editor of the product form (add / remove option, add / remove choice, swatch colour)
 *   [data-ash-pics] [data-ash-upload]   pictures of a product: upload at once with progress, drag or arrows to re-order, main picture, delete
 *   [data-status-select]             order page: explains the chosen status, offers "needs no payment" only for Completed
 */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var csrf = function () { var m = $('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : ''; };
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  function say(text) {
    var live = $('[data-ash-live]');
    if (!live) {
      live = document.createElement('div');
      live.className = 'visually-hidden';
      live.setAttribute('role', 'status');
      live.setAttribute('aria-live', 'polite');
      live.setAttribute('data-ash-live', '');
      document.body.appendChild(live);
    }
    live.textContent = '';
    window.setTimeout(function () { live.textContent = text; }, 30);
  }

  // ---------------------------------------------------------------- small things
  document.addEventListener('change', function (e) {
    var c = e.target.closest ? e.target.closest('[data-autosubmit-check]') : null;
    if (c && c.form) { c.form.submit(); }
  });
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href="#print"]');
    if (a) { e.preventDefault(); window.print(); }
  });

  // ---------------------------------------------------------------- order page
  (function () {
    var sel = $('[data-status-select]');
    if (!sel) { return; }
    var unpaid = $('[data-unpaid-only]');
    var hint = $('[data-status-hint]');
    var base = hint ? hint.textContent : '';
    var notes = {
      confirmed: 'The customer is e-mailed that the order is confirmed.',
      processing: 'Use this while you prepare the order. The customer is not e-mailed.',
      ready: 'The customer is e-mailed that the order is ready to collect.',
      shipped: 'The customer is e-mailed that the order is on its way.',
      completed: 'The order is finished. Only a PAID order can be completed.',
      cancelled: 'The order is cancelled and the reserved items go back on sale. The customer is e-mailed. If they already paid, you must refund them yourself.',
      refunded: 'Records a refund of the whole paid amount. Send the money back from your gateway, mobile-money or bank account yourself.'
    };
    function sync() {
      if (unpaid) { unpaid.hidden = sel.value !== 'completed'; }
      if (hint) { hint.textContent = notes[sel.value] || base; }
    }
    sel.addEventListener('change', sync);
    sync();
  }());

  // ---------------------------------------------------------------- product form
  var form = $('form[data-ash-product]');
  if (!form) { return; }

  var track = $('[data-track-stock]', form);
  var stockFields = $('[data-stock-fields]', form);
  if (track && stockFields) {
    var syncStock = function () { stockFields.hidden = !track.checked; };
    track.addEventListener('change', syncStock);
    syncStock();
  }

  // ---- options editor
  var opts = $('[data-opts]', form);
  if (opts) {
    var maxGroups = parseInt(opts.getAttribute('data-max-groups') || '4', 10);
    var maxValues = parseInt(opts.getAttribute('data-max-values') || '30', 10);
    var tplGroup = $('template[data-tpl-group]', form);
    var tplValue = $('template[data-tpl-value]', form);
    var addGroup = $('[data-add-group]', form);
    var nextGroup = parseInt(addGroup ? addGroup.getAttribute('data-next') : '0', 10);

    var groupCount = function () { return $$('[data-opt-group]', opts).length; };
    var syncAdd = function () { if (addGroup) { addGroup.hidden = groupCount() >= maxGroups; } };
    syncAdd();

    form.addEventListener('click', function (e) {
      var t = e.target.closest('button');
      if (!t) { return; }
      if (t.hasAttribute('data-add-group')) {
        if (!tplGroup || groupCount() >= maxGroups) { return; }
        var html = tplGroup.innerHTML.split('__G__').join(String(nextGroup));
        nextGroup += 1;
        var holder = document.createElement('div');
        holder.innerHTML = html.trim();
        var node = holder.firstElementChild;
        opts.appendChild(node);
        syncAdd();
        var first = $('input[type="text"], input:not([type])', node);
        if (first) { first.focus(); }
        form.dispatchEvent(new Event('change', { bubbles: true }));
      } else if (t.hasAttribute('data-remove-group')) {
        var g = t.closest('[data-opt-group]');
        if (g) { g.remove(); syncAdd(); form.dispatchEvent(new Event('change', { bubbles: true })); say('Option removed.'); }
      } else if (t.hasAttribute('data-add-value')) {
        var grp = t.closest('[data-opt-group]');
        if (!grp || !tplValue) { return; }
        var list = $('[data-opt-values]', grp);
        if ($$('[data-opt-value]', grp).length >= maxValues) { say('At most ' + maxValues + ' choices.'); return; }
        var n = parseInt(t.getAttribute('data-next') || '0', 10);
        t.setAttribute('data-next', String(n + 1));
        var h = tplValue.innerHTML.split('__G__').join(grp.getAttribute('data-index')).split('__V__').join(String(n));
        var box = document.createElement('div');
        box.innerHTML = h.trim();
        var row = box.firstElementChild;
        list.appendChild(row);
        var inp = $('input.input', row);
        if (inp) { inp.focus(); }
      } else if (t.hasAttribute('data-remove-value')) {
        var r = t.closest('[data-opt-value]');
        var grp2 = t.closest('[data-opt-group]');
        if (!r || !grp2) { return; }
        if ($$('[data-opt-value]', grp2).length <= 1) {
          $$('input.input', r).forEach(function (i) { i.value = ''; });
          var sv = $('[data-swatch-value]', r); if (sv) { sv.value = ''; }
        } else { r.remove(); }
        form.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
    form.addEventListener('input', function (e) {
      // the (visually hidden) legend of an option group names the group, so a screen reader can tell Size from Colour
      var nameInput = e.target.closest('[data-opt-group] input[id^="og-"]');
      if (nameInput) {
        var lg = nameInput.closest('[data-opt-group]').querySelector('legend');
        if (lg) { lg.textContent = nameInput.value.trim() !== '' ? 'Option group: ' + nameInput.value.trim() : 'Option group'; }
      }
      var p = e.target.closest('[data-swatch-picker]');
      if (p) { var hv = p.parentNode.querySelector('[data-swatch-value]'); if (hv) { hv.value = p.value.toUpperCase(); } }
    });
  }

  // ---- pictures (edit mode only: a new product has no id to upload to yet)
  var list = $('[data-ash-pics]', form);
  var upload = $('[data-ash-upload]', form);
  var imagesUrl = form.getAttribute('data-images-url');
  if (!list || !upload || !imagesUrl) { return; }
  var reorderUrl = list.getAttribute('data-reorder-url');

  function post(url, fields) {
    var fd = new FormData();
    Object.keys(fields).forEach(function (k) {
      var v = fields[k];
      if (Array.isArray(v)) { v.forEach(function (x) { fd.append(k + '[]', x); }); } else { fd.append(k, v); }
    });
    fd.append('_token', csrf());
    return fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() } })
      .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); });
  }

  function items() { return $$('.ash-pic', list); }
  function syncOrder() {
    items().forEach(function (li, i) {
      var h = $('input[name="image_order[]"]', li); if (h) { h.value = li.getAttribute('data-image-id'); }
      var up = $('[data-pic-up]', li), down = $('[data-pic-down]', li);
      if (up) { up.hidden = false; up.disabled = i === 0; up.setAttribute('aria-label', 'Move picture ' + (i + 1) + ' earlier'); }
      if (down) { down.hidden = false; down.disabled = i === items().length - 1; down.setAttribute('aria-label', 'Move picture ' + (i + 1) + ' later'); }
    });
    var empty = $('[data-pics-empty]', form);
    if (empty) { empty.hidden = items().length > 0; }
  }
  function markCover(id) {
    items().forEach(function (li) {
      var yes = String(li.getAttribute('data-image-id')) === String(id);
      var radio = $('[data-pic-cover]', li); if (radio) { radio.checked = yes; }
      var badge = $('[data-cover-badge]', li); if (badge) { badge.classList.toggle('is-hidden', !yes); }
    });
  }
  function saveOrder() {
    var ids = items().map(function (li) { return li.getAttribute('data-image-id'); });
    syncOrder();
    if (!reorderUrl || !ids.length) { return; }
    post(reorderUrl, { ids: ids }).then(function (res) {
      if (res.json && res.json.ok) { markCover(res.json.cover_id); say('Picture order saved.'); } else { say((res.json && res.json.error) || 'The order could not be saved.'); }
    }).catch(function () { say('The order could not be saved.'); });
  }
  syncOrder();

  // arrows
  list.addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (!b) { return; }
    var li = b.closest('.ash-pic');
    if (!li) { return; }
    if (b.hasAttribute('data-pic-up') && li.previousElementSibling) { list.insertBefore(li, li.previousElementSibling); saveOrder(); b.focus(); }
    else if (b.hasAttribute('data-pic-down') && li.nextElementSibling) { list.insertBefore(li.nextElementSibling, li); saveOrder(); b.focus(); }
    else if (b.hasAttribute('data-pic-delete')) {
      e.preventDefault();
      e.stopImmediatePropagation();
      if (!window.confirm('Delete this picture?')) { return; }
      post(li.getAttribute('data-delete-url'), {}).then(function (res) {
        if (res.json && res.json.ok) {
          li.remove(); syncOrder(); markCover(res.json.cover_id); say('Picture deleted.');
        } else { say((res.json && res.json.error) || 'The picture could not be deleted.'); }
      }).catch(function () { form.submit(); });
    }
  }, true);

  // main picture
  list.addEventListener('change', function (e) {
    var r = e.target.closest('[data-pic-cover]');
    if (!r) { return; }
    var li = r.closest('.ash-pic');
    post(li.getAttribute('data-update-url'), { cover: '1' }).then(function (res) {
      if (res.json && res.json.ok) { list.insertBefore(li, list.firstElementChild); syncOrder(); markCover(res.json.cover_id); say('Main picture changed.'); }
    });
  });

  // drag and drop
  var dragging = null;
  list.addEventListener('dragstart', function (e) {
    var li = e.target.closest('.ash-pic');
    if (!li || e.target.closest('input, button, label')) { if (e.target.closest && e.target.closest('input')) { e.preventDefault(); } return; }
    dragging = li; li.classList.add('is-dragging');
    if (e.dataTransfer) { e.dataTransfer.effectAllowed = 'move'; try { e.dataTransfer.setData('text/plain', li.getAttribute('data-image-id')); } catch (err) { /* ignore */ } }
  });
  list.addEventListener('dragover', function (e) {
    if (!dragging) { return; }
    e.preventDefault();
    var over = e.target.closest('.ash-pic');
    if (!over || over === dragging) { return; }
    var rect = over.getBoundingClientRect();
    var after = (e.clientY - rect.top) > rect.height / 2 && (e.clientX - rect.left) > rect.width / 2 ? true : (e.clientY - rect.top) > rect.height / 2;
    list.insertBefore(dragging, after ? over.nextSibling : over);
  });
  list.addEventListener('dragend', function () { if (dragging) { dragging.classList.remove('is-dragging'); dragging = null; saveOrder(); } });

  // upload with progress
  var fileInput = $('[data-pic-files]', upload);
  var crop = $('[data-pic-crop]', upload);
  var newAlt = $('[data-pic-newalt]', upload);
  var uploadList = $('[data-upload-list]', upload);
  function addPic(img) {
    var iid = img.id;
    var base = imagesUrl.replace(/\/images$/, '/images/') + iid;
    var li = document.createElement('li');
    li.className = 'ash-pic';
    li.setAttribute('draggable', 'true');
    li.setAttribute('data-image-id', iid);
    li.setAttribute('data-update-url', base);
    li.setAttribute('data-delete-url', base + '/delete');
    li.innerHTML = '<div class="ash-pic__media"><img src="' + esc(img.src) + '" alt="" width="120" height="120"><span class="ash-pic__cover is-hidden" data-cover-badge>Main picture</span></div>'
      + '<input type="hidden" name="image_order[]" value="' + iid + '">'
      + '<div class="field"><label class="field__label" for="alt-' + iid + '">Describe the picture</label><input class="input" id="alt-' + iid + '" name="image_alt[' + iid + ']" value="' + esc(img.alt || '') + '" maxlength="200" data-pic-alt><div class="field__help">Read out for people who cannot see it.</div></div>'
      + '<div class="ash-pic__tools"><label class="check ash-check"><input type="radio" name="cover_id" value="' + iid + '" data-pic-cover> <span>Main picture</span></label>'
      + '<span class="ash-pic__move"><button class="repeater__tool" type="button" data-pic-up>&#8593;<span class="visually-hidden">Move earlier</span></button><button class="repeater__tool" type="button" data-pic-down>&#8595;<span class="visually-hidden">Move later</span></button></span>'
      + '<button class="btn btn--ghost btn--sm ash-danger" type="submit" formaction="' + esc(base + '/delete') + '" data-pic-delete>Delete</button></div>';
    list.appendChild(li);
    syncOrder();
    return li;
  }
  function sendFile(file) {
    var row = document.createElement('li');
    row.className = 'ash-upload__item';
    row.innerHTML = '<span class="ash-upload__name">' + esc(file.name) + '</span><span class="ash-upload__bar"><span style="width:0%"></span></span><span class="ash-upload__state">Uploading</span>';
    uploadList.appendChild(row);
    var bar = $('.ash-upload__bar span', row), state = $('.ash-upload__state', row);
    return new Promise(function (resolve) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', imagesUrl);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
      xhr.upload.onprogress = function (ev) { if (ev.lengthComputable) { bar.style.width = Math.round(ev.loaded / ev.total * 100) + '%'; } };
      xhr.onload = function () {
        var j = null; try { j = JSON.parse(xhr.responseText); } catch (err) { j = null; }
        if (xhr.status === 200 && j && j.ok) {
          bar.style.width = '100%'; state.textContent = 'Added'; row.classList.add('is-done');
          var li = addPic(j.image);
          if (items().length === 1 && li) { markCover(j.image.id); }
          if (j.notice) { say(j.notice); }
          window.setTimeout(function () { row.remove(); }, 2500);
        } else {
          state.textContent = (j && j.error) ? j.error : 'Could not upload this picture.'; row.classList.add('is-error');
        }
        resolve();
      };
      xhr.onerror = function () { state.textContent = 'The connection failed. Try again.'; row.classList.add('is-error'); resolve(); };
      var fd = new FormData();
      fd.append('file', file);
      fd.append('crop', crop ? crop.value : 'square');
      fd.append('alt', newAlt ? newAlt.value : '');
      fd.append('_token', csrf());
      xhr.send(fd);
    });
  }
  if (fileInput) {
    fileInput.addEventListener('change', function () {
      var files = Array.prototype.slice.call(fileInput.files || []);
      if (!files.length || !window.FormData || !window.XMLHttpRequest) { return; }
      fileInput.value = '';             // uploaded here: do not post them again with the form
      var chain = Promise.resolve();
      files.forEach(function (f) { chain = chain.then(function () { return sendFile(f); }); });
      chain.then(function () { say('Pictures uploaded.'); });
    });
  }
}());
