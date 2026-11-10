/* Admin: "Choose from the website gallery" - one dialog shared by every picture field (the picture box of the forms, the Pages editor, the newsletter editor and the
   rich-text editor). window.LuminaGalleryPicker.open() resolves to {path, url, alt, caption} of the picture the person chose, or null when they close the dialog.
   The pictures come from GET admin/media/gallery (search + "Show more"). The classic picture box ([data-image-field]) is wired here; the other editors call open() themselves. */
(function () {
  'use strict';
  var dlg = null, grid, search, more, msg, resolveFn = null, page = 1, query = '', loading = false, timer = null;

  function el(tag, cls, text) { var n = document.createElement(tag); if (cls) n.className = cls; if (text) n.textContent = text; return n; }

  function endpoint(p, q) {
    var root = ((document.querySelector('meta[name="base-url"]') || {}).content || '').replace(/\/$/, '');
    return root + '/admin/media/gallery?page=' + p + '&q=' + encodeURIComponent(q);
  }

  function build() {
    dlg = el('dialog', 'gp');
    dlg.setAttribute('aria-labelledby', 'gp-title');
    var head = el('div', 'gp__head');
    var h = el('h2', 'gp__title', 'Choose a picture from the gallery');
    h.id = 'gp-title';
    var close = el('button', 'btn btn--ghost btn--sm', 'Close');
    close.type = 'button';
    close.addEventListener('click', function () { finish(null); });
    head.appendChild(h); head.appendChild(close);
    var bar = el('div', 'gp__bar');
    search = el('input', 'input');
    search.type = 'search'; search.placeholder = 'Search by description, caption or album'; search.setAttribute('aria-label', 'Search the gallery'); search.autocomplete = 'off';
    search.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () { query = search.value.trim(); load(true); }, 250);
    });
    bar.appendChild(search);
    msg = el('p', 'gp__msg'); msg.setAttribute('role', 'status'); msg.setAttribute('aria-live', 'polite');
    grid = el('ul', 'gp__grid');
    more = el('button', 'btn btn--outline btn--sm gp__more', 'Show more pictures');
    more.type = 'button'; more.hidden = true;
    more.addEventListener('click', function () { page++; load(false); });
    dlg.appendChild(head); dlg.appendChild(bar); dlg.appendChild(msg); dlg.appendChild(grid); dlg.appendChild(more);
    dlg.addEventListener('cancel', function (e) { e.preventDefault(); finish(null); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) finish(null); });
    document.body.appendChild(dlg);
  }

  function finish(item) {
    if (dlg && dlg.open) dlg.close();
    var r = resolveFn; resolveFn = null;
    if (r) r(item);
  }

  async function load(reset) {
    if (loading) return;
    loading = true;
    if (reset) { page = 1; grid.textContent = ''; }
    msg.textContent = reset ? 'Loading...' : '';
    try {
      var res = await fetch(endpoint(page, query), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      var data = await res.json();
      if (!res.ok) throw new Error(data.error || 'The gallery could not be loaded.');
      (data.items || []).forEach(function (it) {
        var li = el('li', 'gp__item');
        var b = el('button', 'gp__pick');
        b.type = 'button';
        b.title = it.alt || it.caption || 'Choose this picture';
        var img = el('img');
        img.src = it.url; img.alt = it.alt || it.caption || ''; img.loading = 'lazy'; img.decoding = 'async';
        b.appendChild(img);
        if (it.album) b.appendChild(el('span', 'gp__album', it.album));
        b.addEventListener('click', function () { finish(it); });
        li.appendChild(b);
        grid.appendChild(li);
      });
      more.hidden = !data.more;
      msg.textContent = grid.children.length === 0 ? (query ? 'No picture matches your search.' : 'The gallery has no pictures yet. Add some in Admin > Gallery.') : '';
    } catch (e) {
      msg.textContent = e && e.message ? e.message : 'The gallery could not be loaded.';
    } finally {
      loading = false;
    }
  }

  function open() {
    if (!dlg) build();
    if (resolveFn) resolveFn(null);
    return new Promise(function (resolve) {
      resolveFn = resolve;
      query = ''; search.value = '';
      if (!dlg.open) dlg.showModal();
      load(true);
      search.focus();
    });
  }
  window.LuminaGalleryPicker = { open: open };

  // ---- the classic picture box of the forms
  document.addEventListener('click', async function (e) {
    var btn = e.target.closest && e.target.closest('[data-image-gallery]');
    if (!btn) return;
    var box = btn.closest('[data-image-field]');
    if (!box) return;              // other editors (newsletter, ...) have their own handler
    var pathInput = box.querySelector('[data-image-path]');
    if (!pathInput) return;
    var picked = await open();
    if (!picked) return;
    pathInput.value = picked.path;
    pathInput.dispatchEvent(new Event('input', { bubbles: true }));
    var prev = box.querySelector('[data-image-preview]');
    var img = prev && prev.querySelector('img');
    if (img) { img.src = picked.url; if (prev) prev.classList.remove('is-empty'); }
    var file = box.querySelector('[data-image-input]'); if (file) file.value = '';
    var rm = box.querySelector('[data-image-remove]'); if (rm) rm.value = '';
    var alt = box.querySelector('input[type="text"]');
    if (alt && alt.value.trim() === '' && picked.alt) alt.value = picked.alt;
    var status = box.querySelector('[data-image-status]');
    if (status) status.textContent = 'Picture chosen from the gallery.';
  });
})();
