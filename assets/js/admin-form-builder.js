/*
 * Lumina form builder (admin). ES2020, no dependencies, CSP-safe (no inline code). Contract: docs/FORMS.md sections 16 (JSON API) and 17 (builder behaviour).
 * Owner: BUILDER agent. Loaded by views/admin/forms/builder.php (the builder), index.php (copy buttons) and templates.php (template picker).
 *
 * Sections of this file:
 *   1. helpers (DOM builder, icons, toasts, dialogs, copy)         5. canvas: cards, drag and drop, keyboard, structure actions
 *   2. list page + template picker behaviour                        6. the settings panel of a question / page, options, rules, conditions
 *   3. builder state, history (undo / redo), API, save, checklist   7. form settings workspace (tabs)
 *   4. layout shell: bar, palette, tabs, responsive modes           8. live preview, test submit, publish flow, dialogs, init
 *
 * Data model = docs/FORMS.md section 3 (definition v1) and 4 (settings). The browser never trusts itself: every save is normalised again by
 * FormDefinition::normalise / FormSettings::normalise on the server, and the checklist comes from POST /validate.
 */
(() => {
  'use strict';

  const doc = document;
  const $ = (sel, r = doc) => r.querySelector(sel);
  const $$ = (sel, r = doc) => Array.from(r.querySelectorAll(sel));
  const csrfToken = () => (window.LuminaAdmin && window.LuminaAdmin.csrf ? window.LuminaAdmin.csrf() : ((doc.querySelector('meta[name="csrf-token"]') || {}).content || ''));

  /* ================================================================== 1. helpers */

  const flat = (kids) => kids.flat(Infinity);

  /** h('div', {class:'x', onclick: fn, dataset:{a:1}}, 'text', childNode, [more]) -> element. false / null / undefined attributes and children are skipped. */
  function h(tag, attrs, ...kids) {
    const el = doc.createElement(tag);
    if (attrs) {
      for (const [k, v] of Object.entries(attrs)) {
        if (v === null || v === undefined || v === false) continue;
        if (k === 'class') el.className = v;
        else if (k === 'dataset') Object.assign(el.dataset, v);
        else if (k.startsWith('on') && typeof v === 'function') el.addEventListener(k.slice(2), v);
        else if (k === 'value' || k === 'checked' || k === 'selected') el[k] = v;
        else el.setAttribute(k, v === true ? '' : String(v));
      }
    }
    for (const kid of flat(kids)) {
      if (kid === null || kid === undefined || kid === false) continue;
      el.append(kid instanceof Node ? kid : doc.createTextNode(String(kid)));
    }
    return el;
  }

  /** Clone an icon from the sprite printed by builder.php (same SVGs as the rest of the admin). */
  function ic(name, cls) {
    const src = $('#fb-icons [data-icon="' + name + '"] svg');
    if (!src) return h('span', { class: 'icon', 'aria-hidden': 'true' });
    const svg = src.cloneNode(true);
    svg.setAttribute('width', '18');
    svg.setAttribute('height', '18');
    svg.setAttribute('class', 'icon' + (cls ? ' ' + cls : ''));
    return svg;
  }

  const clone = (o) => JSON.parse(JSON.stringify(o));
  const clamp = (n, lo, hi) => Math.max(lo, Math.min(hi, n));
  const pad2 = (n) => String(n).padStart(2, '0');
  const isObj = (v) => v !== null && typeof v === 'object' && !Array.isArray(v);
  let uidCounter = 0;
  const uid = (p = 'fb') => p + '-' + (++uidCounter).toString(36);

  function debounce(fn, ms) {
    let t = null;
    const wrapped = (...a) => { clearTimeout(t); t = setTimeout(() => { t = null; fn(...a); }, ms); };
    wrapped.cancel = () => { clearTimeout(t); t = null; };
    wrapped.flush = (...a) => { if (t !== null) { clearTimeout(t); t = null; fn(...a); } };
    return wrapped;
  }

  function slugify(text, sep = '-', max = 60) {
    const s = String(text || '').normalize('NFKD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, sep);
    const trim = sep === '_' ? /^_+|_+$/g : /^-+|-+$/g;
    return s.replace(trim, '').slice(0, max).replace(trim, '');
  }

  const ALPHA = 'abcdefghijklmnopqrstuvwxyz0123456789';
  function randomChars(n) {
    const a = new Uint8Array(n);
    (window.crypto || window.msCrypto).getRandomValues(a);
    return Array.from(a, (x) => ALPHA[x % 36]).join('');
  }

  async function copyText(text) {
    try { await navigator.clipboard.writeText(text); return true; } catch (e) { /* fall through to the old way */ }
    const ta = h('textarea', { style: 'position:fixed;top:0;left:0;opacity:0', 'aria-hidden': 'true' });
    ta.value = text;
    doc.body.append(ta);
    ta.select();
    let ok = false;
    try { ok = doc.execCommand('copy'); } catch (e) { ok = false; }
    ta.remove();
    return ok;
  }

  /* ---- toasts (polite live region) */
  let toastBox = null;
  function toast(content, kind = 'info', ms = 4800) {
    if (!toastBox) {
      toastBox = h('div', { class: 'fb-toasts', role: 'status', 'aria-live': 'polite' });
      doc.body.append(toastBox);
    }
    const t = h('div', { class: 'fb-toast fb-toast--' + kind }, content);
    toastBox.append(t);
    setTimeout(() => t.remove(), ms);
    return t;
  }

  /* ---- dialogs: a promise that resolves with the value of the pressed action (or null when dismissed) */
  function openDialog({ title, body, actions = [], wide = false, className = '', initialFocus = null, onOpen = null }) {
    return new Promise((resolve) => {
      const titleId = uid('fb-dt');
      let done = false;
      const dlg = h('dialog', { class: 'fb-dialog' + (wide ? ' fb-dialog--wide' : '') + (className ? ' ' + className : ''), 'aria-labelledby': titleId });
      const close = (value) => {
        if (done) return;
        done = true;
        try { dlg.close(); } catch (e) { /* already closed */ }
        dlg.remove();
        resolve(value === undefined ? null : value);
      };
      dlg.append(
        h('div', { class: 'fb-dialog__head' },
          h('h2', { class: 'fb-dialog__title', id: titleId }, title),
          h('button', { type: 'button', class: 'fb-iconbtn', 'aria-label': 'Close', onclick: () => close(null) }, ic('x'))),
        h('div', { class: 'fb-dialog__body' }, body),
        actions.length ? h('div', { class: 'fb-dialog__foot' }, actions.map((a) => h('button', {
          type: 'button', class: 'btn ' + (a.class || 'btn--outline'), 'data-primary': a.primary ? '1' : null, onclick: () => close(a.value),
        }, a.icon ? [ic(a.icon, 'icon--sm'), ' '] : null, a.label))) : null,
      );
      dlg.addEventListener('cancel', (e) => { e.preventDefault(); close(null); });
      dlg.addEventListener('mousedown', (e) => { if (e.target === dlg) close(null); });
      doc.body.append(dlg);
      if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
      const first = (initialFocus && dlg.querySelector(initialFocus)) || dlg.querySelector('[data-primary="1"]') || dlg.querySelector('.fb-dialog__foot .btn:last-child') || dlg.querySelector('.fb-iconbtn');
      if (first) first.focus();
      dlg.fbClose = close;
      if (onOpen) onOpen(dlg);
    });
  }

  const confirmBox = ({ title, text, confirm = 'OK', cancel = 'Cancel', danger = false }) => openDialog({
    title,
    body: h('div', { class: 'fb-dialog__text' }, flat([text]).map((t) => (t instanceof Node ? t : h('p', null, t)))),
    actions: [{ label: cancel, value: false }, { label: confirm, value: true, class: danger ? 'btn--danger' : 'btn--primary', primary: true }],
  }).then((v) => v === true);

  /* ---- global behaviours on every forms screen */
  doc.addEventListener('click', async (e) => {
    const copyBtn = e.target.closest('[data-copy]');
    if (copyBtn) {
      e.preventDefault();
      const ok = await copyText(copyBtn.dataset.copy || '');
      toast(ok ? (copyBtn.dataset.copyLabel || 'Copied') : 'Could not copy. Select the text and copy it by hand.', ok ? 'success' : 'error', 2600);
      const det = copyBtn.closest('details');
      if (det) det.removeAttribute('open');
      return;
    }
    $$('details[data-menu][open]').forEach((d) => { if (!d.contains(e.target)) d.removeAttribute('open'); });
  });
  doc.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    $$('details[data-menu][open]').forEach((d) => { d.removeAttribute('open'); const s = d.querySelector('summary'); if (s) s.focus(); });
  });

  /* ================================================================== 2. template picker */

  const picker = $('[data-template-picker]');
  if (picker) {
    const title = $('[data-template-title]', picker);
    const copySel = $('[data-copy-select]', picker);
    let touched = title.value !== '';
    title.addEventListener('input', () => { touched = true; });
    const suggest = () => {
      const r = $('.tp__radio:checked', picker);
      if (!r || touched) return;
      title.value = r.dataset.title || '';
    };
    picker.addEventListener('change', (e) => {
      if (e.target.matches('.tp__radio')) { touched = title.value !== '' && touched; suggest(); }
      if (e.target === copySel) {
        const radio = $('[data-copy-of]', picker);
        if (radio) { radio.checked = true; radio.dataset.title = copySel.selectedOptions[0].dataset.title || ''; if (!touched || title.value.startsWith('Copy of')) { touched = false; suggest(); } }
      }
    });
    suggest();
  }

  /* ================================================================== the builder starts here */

  const root = $('#form-builder');
  if (!root) return;

  const API = root.dataset.api;
  const FORM_ID = Number(root.dataset.formId);
  const LIST_URL = root.dataset.listUrl || '';

  /** JSON call. Resolves { ok, status, data } and NEVER rejects on an HTTP error (409 conflict, 422 validation / not_ready are normal answers). */
  async function api(method, path, body) {
    const isForm = typeof FormData !== 'undefined' && body instanceof FormData;
    try {
      const res = await fetch(API + path, {
        method,
        credentials: 'same-origin',
        headers: Object.assign({ Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, body === undefined || isForm ? {} : { 'Content-Type': 'application/json' }),
        body: body === undefined ? undefined : (isForm ? body : JSON.stringify(body)),
      });
      let data = null;
      try { data = await res.json(); } catch (e) { data = null; }
      return { ok: res.ok, status: res.status, data };
    } catch (e) {
      return { ok: false, status: 0, data: null, network: true };
    }
  }
  const errMsg = (res, fallback) => {
    if (res.network) return 'No connection to the server.';
    if (res.status === 401) return 'You have been signed out. Sign in again in another tab, then press Save.';
    if (res.status === 403 && res.data && /csrf|expired|token/i.test(String(res.data.error || res.data.message || ''))) return 'This page expired. Reload it to continue.';
    if (res.status === 403 && res.data && res.data.error === 'forbidden' && res.data.message) return res.data.message;
    if (res.status === 403) return 'You are not allowed to do this.';
    return (res.data && (res.data.message || res.data.error)) || fallback;
  };

  /* ---- constants for wording */
  const PALETTE_HELP = {
    text: 'A short answer, for example a name.',
    textarea: 'A longer answer or a message.',
    email: 'Checks that it looks like an e-mail address.',
    phone: 'Ugandan or international numbers.',
    number: 'Digits only, with an optional smallest and largest.',
    date: 'A calendar date.',
    time: 'A time of day.',
    select: 'Pick one from a drop-down list.',
    radio: 'Pick one; all choices are visible.',
    checkboxes: 'Pick as many as apply.',
    yes_no: 'Two buttons: Yes or No.',
    rating: 'One to five stars.',
    file: 'Upload a CV, photo or document.',
    consent: 'A tick box people must agree to.',
    heading: 'A title that splits the form into sections.',
    paragraph: 'Instructions or an explanation.',
    divider: 'A thin line between questions.',
    page_break: 'Start a new step (a new page).',
  };
  const PALETTE_LABEL = { textarea: 'Long text', select: 'Dropdown list', radio: 'Single choice', checkboxes: 'Multiple choice', rating: 'Rating (1 to 5)', paragraph: 'Help text', consent: 'Consent box', yes_no: 'Yes / No', file: 'File upload', email: 'E-mail address', phone: 'Phone number', text: 'Short text' };
  const GROUPS = [['text', 'Text and numbers'], ['choice', 'Choices'], ['special', 'Files and consent'], ['layout', 'Layout']];
  const STATUS_TEXT = { draft: 'Draft', published: 'Live', closed: 'Closed', archived: 'Archived' };
  const KIND_TEXT = { general: 'General form', job: 'Job application', intake: 'Private intake form', survey: 'Survey' };
  const KIND_HELP = {
    general: 'Any other use. Search engines may list it unless you change that.',
    job: 'Applicants are "Hired" instead of "Accepted" in the answers, and search engines are asked to ignore the form.',
    intake: 'Sensitive details about people. Hidden from search engines by default.',
    survey: 'Public feedback. Search engines may list it.',
  };
  const RESERVED_KEYS = ['form_title', 'site', 'date', 'reference', 'link', 'submission_id'];
  const OTHER = '__other';
  const DEVICES = { phone: { w: 375, label: 'Phone', icon: 'smartphone' }, tablet: { w: 768, label: 'Tablet', icon: 'tablet' }, desktop: { w: 1200, label: 'Desktop', icon: 'monitor' } };

  /* ================================================================== 3. state */

  const S = {
    catalog: null,
    form: null,           // server facts: id, status, version, slug, urls, embed, can, availability, submissions_count, unread, jobs ...
    doc: null,            // { meta:{title,description,slug,listed,opens_at,closes_at,max_submissions}, def:{version,pages[]}, cfg:{...} }
    base: null,           // serialised copy of what the server has (see baselineOf)
    sel: null,            // { k:'field'|'page', id }
    ins: null,            // insertion point { pageId, index } chosen with "Add a question here"
    ws: 'questions',      // questions | settings
    side: 'field',        // field | preview  (right panel tab)
    sec: 'basics',        // settings section
    device: 'phone',
    drawer: false,        // compact layout: the right panel is open
    paletteOpen: false,   // compact layout: the palette strip is open
    layout: 'wide',
    checklist: [],
    issues: { field: {}, page: {}, global: [] },
    errs: {},             // path => message (server validation of the settings / dates / slug)
    save: { state: 'idle', at: null, msg: '', failSnap: null, inflight: false, again: false },
    undo: [], redo: [], lastSnap: '', lastKey: null, lastKeyAt: 0,
    pvSeq: 0, valSeq: 0, previewStale: true,
    leaving: false,
    dom: {},
  };

  const lim = () => (S.catalog ? S.catalog.limits : {});
  const snap = () => JSON.stringify(S.doc);
  const pagesOf = () => S.doc.def.pages;

  /* ---- catalogue helpers */
  const typeInfo = (t) => (S.catalog.types.find((x) => x.type === t)) || { type: t, label: t, flags: [], input: false, icon: 'circle', group: 'text' };
  const isInput = (t) => !!typeInfo(t).input;
  const hasFlag = (t, f) => typeInfo(t).flags.includes(f);
  const isChoice = (t) => t === 'select' || t === 'radio' || t === 'checkboxes';
  const typeName = (t) => PALETTE_LABEL[t] || typeInfo(t).label;

  /* ---- finding things */
  function locate(id) {
    const pages = pagesOf();
    for (let pi = 0; pi < pages.length; pi++) {
      const fi = pages[pi].fields.findIndex((f) => f.id === id);
      if (fi >= 0) return { page: pages[pi], pi, field: pages[pi].fields[fi], fi };
    }
    return null;
  }
  const findPage = (id) => { const pi = pagesOf().findIndex((p) => p.id === id); return pi < 0 ? null : { page: pagesOf()[pi], pi }; };
  /** Every field in document order with its place and its number among the questions. */
  function allFields() {
    const out = [];
    let n = 0;
    pagesOf().forEach((page, pi) => page.fields.forEach((field, fi) => {
      out.push({ field, page, pi, fi, n: isInput(field.type) ? ++n : 0 });
    }));
    return out;
  }
  const questionCount = () => allFields().filter((x) => x.n > 0).length;
  const fieldTotal = () => allFields().length;
  function usedIds() {
    const s = new Set();
    pagesOf().forEach((p) => { s.add(p.id); p.fields.forEach((f) => s.add(f.id)); });
    return s;
  }
  function newId(prefix) {
    const used = usedIds();
    let id;
    do { id = prefix + randomChars(prefix === 'p' ? 6 : 8); } while (used.has(id));
    return id;
  }
  const fieldTitle = (f) => (f.label && f.label.trim() ? f.label.trim() : 'Untitled ' + typeName(f.type).toLowerCase());

  /* ---- keys (the {placeholder} names used in e-mails) */
  function keyFromLabel(label) {
    let k = slugify(label, '_', 36);
    if (!k || !/^[a-z]/.test(k)) k = 'field' + (k ? '_' + k : '');
    return k.slice(0, 36).replace(/_+$/, '');
  }
  function uniqueKey(base, ownId) {
    const used = new Set(allFields().filter((x) => x.field.id !== ownId && x.field.key).map((x) => x.field.key));
    let k = RESERVED_KEYS.includes(base) ? base + '_field' : base;
    const root0 = k;
    for (let n = 2; used.has(k); n++) k = root0.slice(0, 36) + '_' + n;
    return k;
  }
  /** Keys follow the label while the form is brand new; once it was live or has answers they stay put (e-mail templates may use them). */
  const canRekey = () => S.form.status === 'draft' && !S.form.published_at && Number(S.form.submissions_count) === 0;
  function onLabelChanged(f, prev) {
    if (!isInput(f.type)) return;
    const label = (f.label || '').trim();
    if (!label) return;
    const wasAuto = f.key === '' || f.key === uniqueKey(keyFromLabel(prev || ''), f.id);
    if (wasAuto && (f.key === '' || canRekey())) f.key = uniqueKey(keyFromLabel(label), f.id);
  }
  function ensureKeys() {
    allFields().forEach(({ field }) => { if (isInput(field.type) && !field.key) field.key = uniqueKey(keyFromLabel(field.label || 'field'), field.id); });
  }

  /* ---- new things */
  function makeField(type) {
    const f = { id: newId('f'), key: '', type, label: '', help: '', width: 'full', required: false, logic: null };
    if (hasFlag(type, 'placeholder')) f.placeholder = '';
    if (hasFlag(type, 'default')) f.default = type === 'checkboxes' ? [] : '';
    if (hasFlag(type, 'text')) f.text = '';
    if (hasFlag(type, 'options')) {
      f.options = [{ value: 'option-1', label: 'Option 1' }, { value: 'option-2', label: 'Option 2' }];
      if (hasFlag(type, 'other')) { f.allow_other = false; f.other_label = ''; }
    }
    if (hasFlag(type, 'file')) f.file = { types: ['pdf'], max_mb: Math.min(5, lim().file_max_mb || 5), max_files: 1 };
    if (['length', 'pattern', 'range', 'count', 'date_range', 'time_range'].some((x) => hasFlag(type, x)) || type === 'email') f.validation = {};
    if (type === 'email') f.placeholder = 'name@example.com';
    if (type === 'phone') f.placeholder = 'For example 0700 123456';
    if (type === 'consent') { f.label = 'I agree that my information may be stored and used to deal with this form.'; f.required = true; }
    if (!isInput(type)) f.width = 'full';
    if (isInput(type) && f.label) f.key = uniqueKey(keyFromLabel(f.label), f.id);
    return f;
  }
  function makePage() {
    return { id: newId('p'), title: '', description: '', logic: null, fields: [] };
  }

  /* ================================================================== history (undo / redo, last 30 actions) */

  function updateHistoryButtons() {
    const d = S.dom;
    if (d.undo) d.undo.disabled = S.undo.length === 0;
    if (d.redo) d.redo.disabled = S.redo.length === 0;
  }
  /**
   * Called AFTER every mutation of S.doc. Pushes the previous state on the undo stack. Typing in one box (same `key`) within 1.5 s is ONE step.
   * Returns false when nothing changed.
   */
  function pushHistory(key) {
    const cur = snap();
    if (cur === S.lastSnap) return false;
    const now = Date.now();
    if (!(key && key === S.lastKey && now - S.lastKeyAt < 1500)) {
      S.undo.push(S.lastSnap);
      if (S.undo.length > 30) S.undo.shift();
      S.redo.length = 0;
    }
    S.lastSnap = cur;
    S.lastKey = key || null;
    S.lastKeyAt = now;
    updateHistoryButtons();
    return true;
  }
  function applySnapshot(text) {
    S.doc = JSON.parse(text);
    S.lastSnap = text;
    S.lastKey = null;
    if (S.sel && S.sel.k === 'field' && !locate(S.sel.id)) S.sel = null;
    if (S.sel && S.sel.k === 'page' && !findPage(S.sel.id)) S.sel = null;
    renderAll();
    markDirty();
    schedulePreview();
    scheduleValidate();
    updateHistoryButtons();
  }
  function undo() {
    if (!S.undo.length) return;
    S.redo.push(S.lastSnap);
    applySnapshot(S.undo.pop());
    say('Undone.');
  }
  function redo() {
    if (!S.redo.length) return;
    S.undo.push(S.lastSnap);
    applySnapshot(S.redo.pop());
    say('Done again.');
  }

  /**
   * One call after any change. opts: key (typing coalescing), card (id: redraw only that card), full (redraw the canvas), inspector (redraw the
   * right panel), settings (a form setting changed: only history / save / preview), say (announcement).
   */
  function changed(opts = {}) {
    if (!pushHistory(opts.key)) return;
    markDirty();
    if (opts.full) renderCanvas();
    else if (opts.card) refreshCard(opts.card);
    if (opts.inspector) renderInspector();
    if (opts.bar) updateBar();
    schedulePreview();
    scheduleValidate();
    if (opts.say) say(opts.say);
  }

  /* ---- live region for screen readers */
  function say(text) {
    const box = S.dom.live;
    if (!box) return;
    box.textContent = '';
    setTimeout(() => { box.textContent = text; }, 30);
  }

  /* ================================================================== save, with optimistic locking */

  const META_KEYS = ['title', 'description', 'slug', 'listed', 'opens_at', 'closes_at', 'max_submissions'];
  function baselineOf(d) {
    const b = { definition: JSON.stringify(d.def), settings: JSON.stringify(d.cfg) };
    META_KEYS.forEach((k) => { b[k] = JSON.stringify(d.meta[k] === undefined ? null : d.meta[k]); });
    return b;
  }
  function dirtyKeys() {
    if (!S.base) return ['all'];
    const cur = baselineOf(S.doc);
    return Object.keys(cur).filter((k) => cur[k] !== S.base[k]);
  }
  const isDirty = () => dirtyKeys().length > 0;
  function payloadFor(keys) {
    const p = {};
    const m = S.doc.meta;
    for (const k of keys) {
      if (k === 'definition') p.definition = S.doc.def;
      else if (k === 'settings') p.settings = S.doc.cfg;
      else if (k === 'opens_at' || k === 'closes_at') p[k] = m[k] || '';
      else if (k === 'max_submissions') p[k] = m[k] === '' || m[k] === null || m[k] === undefined ? null : Number(m[k]);
      else if (k === 'listed') p[k] = !!m[k];
      else p[k] = m[k];
    }
    return p;
  }
  function markDirty() {
    if (S.save.state === 'conflict' || S.save.state === 'saving') { updateSaveUI(); return; }
    S.save.state = isDirty() ? 'dirty' : (S.save.at ? 'saved' : 'idle');
    updateSaveUI();
  }

  function saveText() {
    const s = S.save;
    const clock = s.at ? pad2(s.at.getHours()) + ':' + pad2(s.at.getMinutes()) : '';
    switch (s.state) {
      case 'saving': return { text: 'Saving...', tone: 'busy' };
      case 'dirty': return { text: 'Unsaved changes', tone: 'warn' };
      case 'error': return { text: 'Not saved: ' + s.msg, tone: 'error' };
      case 'conflict': return { text: 'Conflict: someone else saved this form', tone: 'error' };
      case 'saved': return { text: 'Saved ' + clock, tone: 'ok' };
      default: return { text: 'Saved', tone: 'ok' };
    }
  }
  function updateSaveUI() {
    const d = S.dom;
    if (!d.saveText) return;
    const t = saveText();
    d.saveText.textContent = t.text;
    d.saveWrap.dataset.tone = t.tone;
    d.saveWrap.title = t.text;
    d.saveBtn.hidden = !(S.save.state === 'dirty' || S.save.state === 'error' || S.save.state === 'conflict');
    d.saveBtn.textContent = S.save.state === 'conflict' ? 'Resolve' : 'Save now';
  }

  /** Save the changes. Resolves true when the server has everything (or there was nothing to save). */
  async function save({ autosave = false, force = false } = {}) {
    if (S.save.state === 'conflict' && !force) { openConflict(S.conflictData); return false; }
    if (S.save.inflight) { S.save.again = true; return false; }
    const keys = force ? ['definition', 'settings', ...META_KEYS] : dirtyKeys();
    if (keys.length === 0) { markDirty(); return true; }
    ensureKeys();
    const sentSnap = snap();
    S.save.inflight = true;
    S.save.state = 'saving';
    updateSaveUI();
    const res = await api('PUT', '/' + FORM_ID, Object.assign({ version: S.form.version, autosave }, payloadFor(keys)));
    S.save.inflight = false;
    let ok = false;
    if (res.ok && res.data && res.data.data) {
      ok = true;
      const f = res.data.data;
      applyServerFacts(f);
      const sent = JSON.parse(sentSnap);       // what was SENT (the editor may have changed meanwhile: those edits stay dirty)
      // the baseline is what we sent; if nothing was edited meanwhile adopt the server's normalised text so both sides read the same
      const sentBase = baselineOf(sent);
      keys.forEach((k) => { S.base[k] = sentBase[k]; });
      if (snap() === sentSnap) adoptNormalised(f);
      S.save.state = isDirty() ? 'dirty' : 'saved';
      S.save.at = new Date();
      S.save.msg = '';
      S.save.failSnap = null;
      if (Array.isArray(res.data.warnings)) res.data.warnings.forEach((w) => toast(w, 'warn', 7000));
      if (Array.isArray(res.data.checklist)) { setChecklist(res.data.checklist); }
      S.errs = {};
      showErrors();
      if (!autosave && res.data.changed) toast('Saved.', 'success', 1800);
      scheduleValidate();
    } else if (res.status === 409) {
      S.save.state = 'conflict';
      S.conflictData = res.data && res.data.data ? res.data : null;
      updateSaveUI();
      openConflict(S.conflictData);
    } else if (res.status === 422 && res.data && res.data.error === 'not_ready') {
      S.save.state = 'error';
      S.save.msg = 'this form is live, so it has to stay complete. ' + firstMessage(res.data.errors);
      S.save.failSnap = snap();
      if (Array.isArray(res.data.checklist)) setChecklist(res.data.checklist);
      if (!autosave) openChecklist({ blocked: true });
    } else if (res.status === 422 && res.data) {
      S.save.state = 'error';
      S.save.msg = firstMessage(res.data.errors) || errMsg(res, 'please check the highlighted settings.');
      S.save.failSnap = snap();
      S.errs = res.data.errors && isObj(res.data.errors) ? res.data.errors : {};
      showErrors();
      if (!autosave) toast('Not saved: ' + S.save.msg, 'error', 7000);
    } else {
      S.save.state = 'error';
      S.save.msg = errMsg(res, 'the server did not answer. Your changes are still here; we will try again.');
      S.save.failSnap = snap();
      if (!autosave) toast('Not saved: ' + S.save.msg, 'error', 7000);
    }
    updateSaveUI();
    updateBar();
    if (S.save.again) { S.save.again = false; if (isDirty() && S.save.state !== 'conflict') save({ autosave }); }
    return ok;
  }
  const firstMessage = (errors) => {
    if (!errors) return '';
    const v = Array.isArray(errors) ? errors[0] : Object.values(errors)[0];
    return typeof v === 'string' ? v : '';
  };

  /** Server facts that change when we save (version, address, ...) */
  function applyServerFacts(f) {
    Object.assign(S.form, {
      version: f.version, updated_at: f.updated_at, status: f.status, slug: f.slug, urls: f.urls, embed: f.embed, availability: f.availability,
      submissions_count: f.submissions_count, unread: f.unread, published_at: f.published_at, jobs: f.jobs || S.form.jobs,
    });
  }
  /** After a save with no edits meanwhile: take the server's (normalised) texts, redraw only when something differs. */
  function adoptNormalised(f) {
    const next = { meta: Object.assign({}, S.doc.meta, { title: f.title, description: f.description || '', slug: f.slug, listed: !!f.listed, opens_at: f.opens_at || '', closes_at: f.closes_at || '', max_submissions: f.max_submissions === null ? '' : f.max_submissions }), def: f.definition, cfg: f.settings };
    const a = JSON.stringify(next);
    if (a === snap()) { S.base = baselineOf(next); return; }
    const hadFocus = doc.activeElement;
    S.doc = next;
    S.lastSnap = a;
    S.base = baselineOf(next);
    if (!hadFocus || !root.contains(hadFocus) || hadFocus === doc.body) { renderAll(); } else { renderCanvas(); }
  }

  /* autosave every 30 seconds, and a warning before leaving with unsaved work */
  function startTimers() {
    setInterval(() => {
      if (S.leaving || S.save.inflight || S.save.state === 'conflict') return;
      if (!isDirty()) return;
      if (S.save.state === 'error' && S.save.failSnap === snap()) return;       // do not hammer the server with the same refused state
      save({ autosave: true });
    }, 30000);
    window.addEventListener('beforeunload', (e) => {
      if (S.leaving) return;
      if (isDirty() || S.save.inflight) { e.preventDefault(); e.returnValue = ''; }
    });
  }

  /* ================================================================== checklist (POST /validate) */

  const scheduleValidate = debounce(() => runValidate(), 900);
  async function runValidate() {
    const seq = ++S.valSeq;
    const m = S.doc.meta;
    const res = await api('POST', '/validate', {
      id: FORM_ID, definition: S.doc.def, settings: S.doc.cfg, slug: m.slug, opens_at: m.opens_at || '', closes_at: m.closes_at || '',
      max_submissions: m.max_submissions === '' || m.max_submissions === null ? '' : Number(m.max_submissions),
    });
    if (seq !== S.valSeq || !res.ok || !res.data) return;
    setChecklist(res.data.checklist || []);
    const e = res.data.errors;
    S.errs = e && isObj(e) ? e : {};
    showErrors();
  }
  function setChecklist(list) {
    S.checklist = list;
    const issues = { field: {}, page: {}, global: [] };
    list.forEach((c) => {
      if (c.field) (issues.field[c.field] = issues.field[c.field] || []).push(c);
      else if (c.page) (issues.page[c.page] = issues.page[c.page] || []).push(c);
      else issues.global.push(c);
    });
    S.issues = issues;
    updateBar();
    applyIssues();
  }

  /* ================================================================== 4. shell: bar, palette, columns, responsive modes */

  /** A small popup menu anchored to a button (status menu, more actions). */
  let openMenuEl = null;
  function closeMenu(returnFocus = false) {
    if (!openMenuEl) return;
    const { el, anchor } = openMenuEl;
    el.remove();
    anchor.setAttribute('aria-expanded', 'false');
    openMenuEl = null;
    if (returnFocus) anchor.focus();
  }
  function openMenu(anchor, items) {
    if (openMenuEl && openMenuEl.anchor === anchor) { closeMenu(true); return; }
    closeMenu();
    const el = h('div', { class: 'fb-menu', role: 'menu' }, items.map((it) => {
      if (it === '-') return h('div', { class: 'fb-menu__sep', role: 'separator' });
      if (it.href) {
        return h('a', { class: 'fb-menu__item', role: 'menuitem', href: it.href, target: it.target || null, rel: it.target ? 'noopener' : null, onclick: () => closeMenu() }, ic(it.icon || 'external-link', 'icon--sm'), it.label);
      }
      return h('button', {
        type: 'button', role: 'menuitem', class: 'fb-menu__item' + (it.danger ? ' fb-menu__item--danger' : ''), disabled: it.disabled || null, title: it.title || null,
        onclick: () => { closeMenu(); if (it.action) it.action(); },
      }, ic(it.icon || 'check', 'icon--sm'), it.label);
    }));
    anchor.after(el);
    anchor.setAttribute('aria-expanded', 'true');
    openMenuEl = { el, anchor };
    const first = $('.fb-menu__item:not([disabled])', el);
    if (first) first.focus();
    el.addEventListener('keydown', (e) => {
      const list = $$('.fb-menu__item:not([disabled])', el);
      const i = list.indexOf(doc.activeElement);
      if (e.key === 'ArrowDown') { e.preventDefault(); list[(i + 1) % list.length].focus(); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); list[(i - 1 + list.length) % list.length].focus(); }
      else if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); closeMenu(true); }
      else if (e.key === 'Tab') closeMenu();
    });
  }
  doc.addEventListener('mousedown', (e) => { if (openMenuEl && !openMenuEl.el.contains(e.target) && !openMenuEl.anchor.contains(e.target)) closeMenu(); });

  function iconBtn(icon, label, onclick, extra = {}) {
    return h('button', Object.assign({ type: 'button', class: 'fb-iconbtn', 'aria-label': label, title: label, onclick }, extra), ic(icon));
  }

  function buildShell() {
    const d = S.dom;
    const loading = $('[data-builder-loading]', root);
    if (loading) loading.remove();

    d.live = h('div', { class: 'visually-hidden', role: 'status', 'aria-live': 'polite' });

    /* ---- top bar */
    d.h1 = h('h1', { class: 'visually-hidden' }, 'Edit form: ' + (S.doc.meta.title || 'Untitled form'));
    d.title = h('input', {
      class: 'fb-title__input', type: 'text', maxlength: lim().title || 190, value: S.doc.meta.title, placeholder: 'Name of the form', 'aria-label': 'Form name',
      oninput: () => { S.doc.meta.title = d.title.value; d.h1.textContent = 'Edit form: ' + (d.title.value.trim() || 'Untitled form'); changed({ key: 'meta:title', bar: false }); },
    });
    d.statusBtn = h('button', { type: 'button', class: 'fb-statusbtn', 'aria-haspopup': 'menu', 'aria-expanded': 'false', onclick: () => openStatusMenu() });
    d.tabQ = h('button', { type: 'button', role: 'tab', class: 'fb-tab', id: 'fb-tab-q', 'aria-controls': 'fb-ws-q', onclick: () => setWs('questions') }, ic('list', 'icon--sm'), ' Questions ', h('span', { class: 'fb-tab__count', 'data-qcount': '' }));
    d.tabS = h('button', { type: 'button', role: 'tab', class: 'fb-tab', id: 'fb-tab-s', 'aria-controls': 'fb-ws-s', onclick: () => setWs('settings') }, ic('settings', 'icon--sm'), ' Settings ', h('span', { class: 'fb-tab__count fb-tab__count--bad', 'data-scount': '', hidden: true }));
    d.saveText = h('span', { class: 'fb-save__text' });
    d.saveWrap = h('span', { class: 'fb-save', 'data-tone': 'ok' }, h('span', { class: 'fb-save__dot', 'aria-hidden': 'true' }), d.saveText);
    d.saveBtn = h('button', { type: 'button', class: 'btn btn--sm btn--outline fb-savebtn', hidden: true, onclick: () => save({ autosave: false }) }, 'Save now');
    d.undo = iconBtn('undo', 'Undo (last 30 changes)', () => undo(), { disabled: true });
    d.redo = iconBtn('redo', 'Redo', () => redo(), { disabled: true });
    d.checkBtn = h('button', { type: 'button', class: 'btn btn--sm btn--outline fb-checkbtn', onclick: () => openChecklist({}) }, ic('check-circle', 'icon--sm'), h('span', { class: 'fb-btn-text' }, ' Checklist'), h('span', { class: 'fb-count', hidden: true }));
    d.previewBtn = h('button', { type: 'button', class: 'btn btn--sm btn--outline fb-only-compact', onclick: () => showPreview() }, ic('eye', 'icon--sm'), h('span', { class: 'fb-btn-text' }, ' Preview'));
    d.primary = h('span', { class: 'fb-primary' });
    d.moreBtn = iconBtn('more', 'More actions', () => openMoreMenu(), { 'aria-haspopup': 'menu', 'aria-expanded': 'false' });

    d.bar = h('div', { class: 'fb-bar' },
      h('a', { class: 'fb-back', href: LIST_URL, 'aria-label': 'Back to all forms' }, ic('arrow-left', 'icon--sm'), h('span', { class: 'fb-back__text' }, 'Forms')),
      h('div', { class: 'fb-title' }, d.title, d.statusBtn),
      h('div', { class: 'fb-tabs', role: 'tablist', 'aria-label': 'Builder sections' }, d.tabQ, d.tabS),
      h('div', { class: 'fb-bar__right' },
        d.saveWrap, d.saveBtn,
        h('span', { class: 'fb-bar__tools' }, d.undo, d.redo),
        d.checkBtn, d.previewBtn, d.primary, d.moreBtn));

    /* ---- left column: palette (questions) / section list (settings) */
    d.palette = buildPalette();
    d.snav = h('nav', { class: 'fb-snav', 'aria-label': 'Form settings sections' });

    /* ---- centre: canvas / settings */
    d.canvas = h('div', { class: 'fb-canvas', id: 'fb-ws-q', role: 'tabpanel', 'aria-labelledby': 'fb-tab-q' });
    d.settings = h('div', { class: 'fb-settings', id: 'fb-ws-s', role: 'tabpanel', 'aria-labelledby': 'fb-tab-s' });

    /* ---- right column: question settings + preview */
    d.sideTabField = h('button', { type: 'button', role: 'tab', class: 'fb-stab', id: 'fb-stab-field', 'aria-controls': 'fb-side-field', onclick: () => setSide('field') }, ic('edit', 'icon--sm'), ' Question');
    d.sideTabPreview = h('button', { type: 'button', role: 'tab', class: 'fb-stab', id: 'fb-stab-preview', 'aria-controls': 'fb-side-preview', onclick: () => setSide('preview') }, ic('eye', 'icon--sm'), ' Live preview');
    d.drawerClose = h('button', { type: 'button', class: 'btn btn--sm btn--primary fb-drawer-close', onclick: () => closeDrawer(true) }, 'Done');
    d.inspector = h('div', { class: 'fb-inspector', id: 'fb-side-field', role: 'tabpanel', 'aria-labelledby': 'fb-stab-field', 'data-panel': 'field' });
    d.previewPane = h('div', { class: 'fb-previewpane', id: 'fb-side-preview', role: 'tabpanel', 'aria-labelledby': 'fb-stab-preview', 'data-panel': 'preview' });
    d.sideTabs = h('div', { class: 'fb-side__tablist', role: 'tablist', 'aria-label': 'Question settings or live preview' }, d.sideTabField, d.sideTabPreview);
    d.side = h('aside', { class: 'fb-side', 'aria-label': 'Question settings and preview' },
      h('div', { class: 'fb-side__tabs' }, d.sideTabs, d.drawerClose),
      d.inspector, d.previewPane);
    d.backdrop = h('div', { class: 'fb-drawer-backdrop', onclick: () => closeDrawer(true) });

    d.left = h('div', { class: 'fb-left' }, d.palette, d.snav);
    d.main = h('div', { class: 'fb-main' }, d.canvas, d.settings);
    d.body = h('div', { class: 'fb-body' }, d.left, d.main, d.backdrop, d.side);

    root.append(d.live, d.h1, d.bar, d.body);
    buildPreviewPane();
    wireCanvasEvents();
    wireKeyboard();

    const ro = new ResizeObserver(() => { applyLayout(); root.style.setProperty('--fb-bar-h', Math.ceil(d.bar.getBoundingClientRect().height) + 'px'); });
    ro.observe(root);
    ro.observe(d.bar);
    applyLayout();
  }

  function applyLayout() {
    const w = root.clientWidth;
    // wide = the question settings / preview are docked on the right (3 columns from 1040 px, 2 columns with a palette strip from 860 px); compact = drawer
    const layout = w >= 860 ? 'wide' : 'compact';
    const cols = w >= 1040 ? '3' : (w >= 860 ? '2' : '1');
    if (root.dataset.cols !== cols) root.dataset.cols = cols;
    if (layout !== S.layout || !root.dataset.layout) {
      S.layout = layout;
      root.dataset.layout = layout;
      if (layout === 'wide') { S.drawer = false; }
      applyView();
    }
    if (S.dom.pvView) S.dom.pvView.fit();
  }

  /* ---- views: workspace (questions / settings), right tab (question / preview), drawer on small screens */
  function setWs(ws) {
    S.ws = ws;
    if (ws === 'settings') { S.side = 'preview'; renderSettings(); schedulePreview(true); } else { S.side = S.sel ? 'field' : 'preview'; }
    applyView();
    if (ws === 'questions' && S.side === 'preview') schedulePreview(true);
  }
  function setSide(side) {
    S.side = side;
    if (side === 'preview') schedulePreview(true);
    applyView();
  }
  function showPreview() {
    if (S.layout === 'compact') S.drawer = true;
    if (S.ws === 'settings') S.side = 'preview'; else S.side = 'preview';
    applyView();
    schedulePreview(true);
  }
  function openDrawer(tab) {
    if (S.layout !== 'compact') { if (tab) setSide(tab); return; }
    S.drawer = true;
    if (tab) S.side = tab;
    applyView();
    if (S.side === 'preview') schedulePreview(true);
  }
  function closeDrawer(focusBack) {
    if (!S.drawer) return;
    S.drawer = false;
    applyView();
    if (focusBack && S.sel && S.sel.k === 'field') { const c = cardEl(S.sel.id); if (c) c.focus(); }
  }
  function applyView() {
    const d = S.dom;
    if (!d.body) return;
    if (S.ws === 'settings') S.side = 'preview';
    root.dataset.ws = S.ws;
    root.dataset.side = S.side;
    root.dataset.drawer = S.drawer ? 'open' : 'closed';
    d.tabQ.setAttribute('aria-selected', String(S.ws === 'questions'));
    d.tabS.setAttribute('aria-selected', String(S.ws === 'settings'));
    d.tabQ.tabIndex = S.ws === 'questions' ? 0 : -1;
    d.tabS.tabIndex = S.ws === 'settings' ? 0 : -1;
    d.sideTabField.setAttribute('aria-selected', String(S.side === 'field'));
    d.sideTabPreview.setAttribute('aria-selected', String(S.side === 'preview'));
    d.sideTabField.tabIndex = S.side === 'field' ? 0 : -1;
    d.sideTabPreview.tabIndex = S.side === 'preview' ? 0 : -1;
    d.sideTabField.hidden = S.ws === 'settings';
    d.canvas.hidden = S.ws !== 'questions';
    d.settings.hidden = S.ws !== 'settings';
    d.palette.hidden = S.ws !== 'questions';
    d.snav.hidden = S.ws !== 'settings';
    d.inspector.hidden = S.side !== 'field';
    d.previewPane.hidden = S.side !== 'preview';
    const modal = S.layout === 'compact' && S.drawer;
    d.side.inert = S.layout === 'compact' && !S.drawer;
    d.backdrop.hidden = !modal;
    // an open drawer is a modal dialog: named, aria-modal, and the rest of the builder is inert behind it
    if (modal) { d.side.setAttribute('role', 'dialog'); d.side.setAttribute('aria-modal', 'true'); } else { d.side.removeAttribute('role'); d.side.removeAttribute('aria-modal'); }
    d.bar.inert = modal;
    d.left.inert = modal;
    d.main.inert = modal;
    if (modal && !S.wasModal) requestAnimationFrame(() => { if (S.drawer && !d.side.contains(doc.activeElement)) (S.side === 'preview' || !S.sel ? d.sideTabPreview : d.sideTabField).focus(); });
    S.wasModal = modal;
    d.palette.dataset.open = S.paletteOpen ? '1' : '0';
    if (S.dom.palToggle) S.dom.palToggle.setAttribute('aria-expanded', String(S.paletteOpen));
    if (S.dom.pvView) requestAnimationFrame(() => S.dom.pvView.fit());
  }

  /* ---- palette */
  function buildPalette() {
    const d = S.dom;
    d.palToggle = h('button', { type: 'button', class: 'fb-palette__toggle', 'aria-expanded': 'false', onclick: () => { S.paletteOpen = !S.paletteOpen; applyView(); } },
      ic('plus', 'icon--sm'), ' Add a question ', ic('chevron-down', 'icon--sm fb-palette__chev'));
    const groups = GROUPS.map(([key, label]) => {
      const items = S.catalog.types.filter((t) => t.group === key).map((t) => palItem(t.type, t.icon));
      if (key === 'layout') items.push(palItem('page_break', 'page-break'));
      return h('div', { class: 'fb-palette__group' },
        h('h3', { class: 'fb-palette__label' }, label),
        h('ul', { class: 'fb-palette__list' }, items.map((b) => h('li', null, b))));
    });
    const aside = h('aside', { class: 'fb-palette', 'aria-label': 'Add a question' },
      d.palToggle,
      h('div', { class: 'fb-palette__inner' },
        h('h2', { class: 'fb-palette__title' }, 'Add a question'),
        h('p', { class: 'fb-palette__hint' }, 'Click one, or drag it into the form. A new question goes below the one you selected.'),
        groups));
    return aside;
  }
  function palItem(type, icon) {
    const label = type === 'page_break' ? 'Page break (new step)' : typeName(type);
    const b = h('button', {
      type: 'button', class: 'fb-pal', draggable: 'true', 'data-type': type, title: PALETTE_HELP[type] || '',
      onclick: () => addFromPalette(type),
      ondragstart: (e) => {
        S.drag = { kind: 'new', type };
        e.dataTransfer.effectAllowed = 'copy';
        try { e.dataTransfer.setData('text/plain', 'fb-new:' + type); } catch (err) { /* ignore */ }
        root.classList.add('is-dragging-new');
      },
      ondragend: () => endDrag(),
    }, h('span', { class: 'fb-pal__icon' }, ic(icon, 'icon--sm')), h('span', { class: 'fb-pal__text' }, h('span', { class: 'fb-pal__name' }, label), h('span', { class: 'fb-pal__help' }, PALETTE_HELP[type] || '')));
    return b;
  }

  /* ---- selection */
  function select(kind, id, { open = true, focusLabel = false } = {}) {
    S.sel = kind ? { k: kind, id } : null;
    S.ins = null;
    markSelected();
    S.side = kind ? 'field' : 'preview';
    if (S.ws !== 'questions') { S.ws = 'questions'; }
    renderInspector();
    if (kind && open && S.layout === 'compact') S.drawer = true;
    applyView();
    renderInsertHint();
    if (focusLabel) {
      const inp = $('[data-autofocus]', S.dom.inspector);
      if (inp) setTimeout(() => inp.focus(), 40);
    }
  }
  function markSelected() {
    $$('.fb-card.is-selected, .fb-page.is-selected', S.dom.canvas).forEach((el) => el.classList.remove('is-selected'));
    if (!S.sel) return;
    const el = S.sel.k === 'field' ? cardEl(S.sel.id) : $('.fb-page[data-page-id="' + S.sel.id + '"]', S.dom.canvas);
    if (el) { el.classList.add('is-selected'); if (S.sel.k === 'field') el.setAttribute('aria-current', 'true'); }
    $$('.fb-card[aria-current]', S.dom.canvas).forEach((c) => { if (!c.classList.contains('is-selected')) c.removeAttribute('aria-current'); });
  }
  const cardEl = (id) => $('.fb-card[data-id="' + id + '"]', S.dom.canvas);
  function scrollToCard(id) {
    const el = cardEl(id);
    if (!el) return;
    const r = el.getBoundingClientRect();
    const top = 130;
    if (r.top < top || r.bottom > window.innerHeight - 20) el.scrollIntoView({ block: 'center', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
  }

  /* ---- adding things */
  function insertionPoint() {
    if (S.ins && findPage(S.ins.pageId)) return S.ins;
    if (S.sel && S.sel.k === 'field') { const l = locate(S.sel.id); if (l) return { pageId: l.page.id, index: l.fi + 1 }; }
    if (S.sel && S.sel.k === 'page') { const p = findPage(S.sel.id); if (p) return { pageId: p.page.id, index: p.page.fields.length }; }
    const last = pagesOf()[pagesOf().length - 1];
    return { pageId: last.id, index: last.fields.length };
  }
  function addFromPalette(type, at) {
    if (type === 'page_break') { addPageBreak(at); return; }
    if (fieldTotal() >= (lim().fields || 150)) { toast('A form can have at most ' + lim().fields + ' items. Remove something first.', 'warn'); return; }
    const f = makeField(type);
    const pt = at || insertionPoint();
    const pg = (findPage(pt.pageId) || { page: pagesOf()[pagesOf().length - 1] }).page;
    const idx = clamp(pt.index, 0, pg.fields.length);
    pg.fields.splice(idx, 0, f);
    S.paletteOpen = false;
    changed({ full: true, say: 'Added a ' + typeName(type).toLowerCase() + ' as item ' + (allFields().findIndex((x) => x.field.id === f.id) + 1) + ' of ' + fieldTotal() + '.' });
    select('field', f.id, { focusLabel: true });
    scrollToCard(f.id);
  }
  function addPageBreak(at) {
    if (pagesOf().length >= (lim().pages || 12)) { toast('A form can have at most ' + lim().pages + ' pages.', 'warn'); return; }
    const pt = at || insertionPoint();
    const found = findPage(pt.pageId) || { page: pagesOf()[pagesOf().length - 1], pi: pagesOf().length - 1 };
    const idx = clamp(pt.index, 0, found.page.fields.length);
    const np = makePage();
    np.fields = found.page.fields.splice(idx);
    pagesOf().splice(found.pi + 1, 0, np);
    S.paletteOpen = false;
    changed({ full: true, say: 'Page break added. This is now page ' + (found.pi + 2) + ' of ' + pagesOf().length + '.' });
    select('page', np.id, { focusLabel: true });
    const el = $('.fb-page[data-page-id="' + np.id + '"]', S.dom.canvas);
    if (el) el.scrollIntoView({ block: 'center', behavior: 'smooth' });
  }
  function renderInsertHint() {
    $$('.fb-page__add', S.dom.canvas).forEach((b) => b.classList.toggle('is-active', !!S.ins && b.dataset.page === S.ins.pageId));
  }

  /* ---- the bar (status, save, checklist count, primary button) */
  function availabilityText() {
    const st = S.form.status;
    const av = (S.form.availability && S.form.availability.state) || st;
    if (st === 'published') return { open: 'Live', not_open: 'Opens later', closed: 'Closed', full: 'Full' }[av] || 'Live';
    return STATUS_TEXT[st] || st;
  }
  function updateBar() {
    const d = S.dom;
    if (!d.bar) return;
    const st = S.form.status;
    const label = availabilityText();
    d.statusBtn.replaceChildren(h('span', { class: 'fb-statusbtn__dot', 'aria-hidden': 'true' }), label, ic('chevron-down', 'icon--sm'));
    d.statusBtn.dataset.status = st;
    d.statusBtn.setAttribute('aria-label', 'Status: ' + label + '. Change status');
    const qc = $('[data-qcount]', d.tabQ);
    if (qc) qc.textContent = String(questionCount());
    const errors = S.checklist.filter((c) => c.level === 'error').length;
    const warns = S.checklist.filter((c) => c.level !== 'error').length;
    const cnt = $('.fb-count', d.checkBtn);
    cnt.hidden = errors + warns === 0;
    cnt.textContent = String(errors + warns);
    cnt.dataset.level = errors ? 'error' : 'warn';
    d.checkBtn.dataset.level = errors ? 'error' : (warns ? 'warn' : 'ok');
    d.checkBtn.title = errors ? errors + ' thing' + (errors === 1 ? '' : 's') + ' to fix before publishing' : (warns ? warns + ' suggestion' + (warns === 1 ? '' : 's') : 'Ready to publish');
    const sc = $('[data-scount]', d.tabS);
    const sErr = Object.keys(S.errs || {}).filter((k) => !/^(pages|definition)/.test(k)).length;
    sc.hidden = sErr === 0;
    sc.textContent = String(sErr);
    if (doc.activeElement !== d.title && d.title.value !== S.doc.meta.title) d.title.value = S.doc.meta.title;
    // primary action
    d.primary.replaceChildren();
    if (st === 'draft') d.primary.append(h('button', { type: 'button', class: 'btn btn--sm btn--cta', onclick: () => publishFlow() }, ic('send', 'icon--sm'), ' Publish'));
    else if (st === 'published') d.primary.append(h('a', { class: 'btn btn--sm btn--primary', href: S.form.urls.public, target: '_blank', rel: 'noopener' }, ic('external-link', 'icon--sm'), ' View form'));
    else if (st === 'closed') d.primary.append(h('button', { type: 'button', class: 'btn btn--sm btn--cta', onclick: () => doStatus('reopen') }, ic('check-circle', 'icon--sm'), ' Reopen'));
    else d.primary.append(h('button', { type: 'button', class: 'btn btn--sm btn--cta', onclick: () => doStatus('restore') }, ic('refresh', 'icon--sm'), ' Restore'));
    updateHistoryButtons();
    updateSaveUI();
  }

  function openStatusMenu() {
    const st = S.form.status;
    const items = [];
    if (st === 'draft') items.push({ label: 'Publish...', icon: 'send', action: () => publishFlow() });
    if (st === 'published') {
      items.push({ label: 'View the live form', icon: 'external-link', href: S.form.urls.public, target: '_blank' });
      items.push({ label: 'Close (stop taking answers)', icon: 'lock', action: () => doStatus('close') });
    }
    if (st === 'closed') items.push({ label: 'Reopen the form', icon: 'check-circle', action: () => doStatus('reopen') });
    if (st === 'published' || st === 'closed') items.push({ label: 'Unpublish (back to draft)', icon: 'eye-off', action: () => doStatus('unpublish') });
    if (st === 'archived') items.push({ label: 'Restore as draft', icon: 'refresh', action: () => doStatus('restore') });
    if (st !== 'archived') { items.push('-'); items.push({ label: 'Archive this form', icon: 'folder', action: () => doStatus('archive') }); }
    openMenu(S.dom.statusBtn, items);
  }
  function openMoreMenu() {
    const items = [
      { label: 'Copy the link', icon: 'link', action: () => copyText(S.form.embed.link).then((ok) => toast(ok ? 'Link copied' : 'Could not copy', ok ? 'success' : 'error', 2200)) },
      { label: 'Copy the page shortcode', icon: 'code', action: () => copyText(S.form.embed.shortcode).then((ok) => toast(ok ? 'Shortcode copied' : 'Could not copy', ok ? 'success' : 'error', 2200)) },
      { label: 'Share and embed...', icon: 'external-link', action: () => { setWs('settings'); openSection('share'); } },
      '-',
      { label: 'Duplicate this form', icon: 'copy', action: () => duplicateForm() },
    ];
    if (root.dataset.submissionsUrl) items.push({ label: 'See the answers', icon: 'inbox', href: root.dataset.submissionsUrl });
    items.push({ label: 'Keyboard shortcuts', icon: 'help-circle', action: () => showShortcuts() });
    if (root.dataset.canDelete === '1') { items.push('-'); items.push({ label: 'Delete this form...', icon: 'trash', danger: true, action: () => deleteForm() }); }
    openMenu(S.dom.moreBtn, items);
  }

  /* ================================================================== 5. canvas */

  const WIDTH_TEXT = { full: 'Full width', half: 'Half width', third: 'One third' };

  function mockFor(f) {
    const t = f.type;
    const ph = (txt) => h('span', { class: 'fb-mock__ph' }, txt);
    switch (t) {
      case 'text': return h('div', { class: 'fb-mock fb-mock--line' }, ph(f.placeholder || ''));
      case 'email': return h('div', { class: 'fb-mock fb-mock--line' }, ic('mail', 'icon--sm'), ph(f.placeholder || ''));
      case 'phone': return h('div', { class: 'fb-mock fb-mock--line' }, ic('phone', 'icon--sm'), ph(f.placeholder || ''));
      case 'number': return h('div', { class: 'fb-mock fb-mock--line' }, ph(f.placeholder || '0'));
      case 'date': return h('div', { class: 'fb-mock fb-mock--line' }, ic('calendar', 'icon--sm'), ph('Pick a date'));
      case 'time': return h('div', { class: 'fb-mock fb-mock--line' }, ic('clock', 'icon--sm'), ph('Pick a time'));
      case 'textarea': return h('div', { class: 'fb-mock fb-mock--area' }, ph(f.placeholder || ''));
      case 'select': return h('div', { class: 'fb-mock fb-mock--line fb-mock--select' }, ph(f.placeholder || 'Choose...'), ic('chevron-down', 'icon--sm'));
      case 'radio':
      case 'checkboxes': {
        const opts = (f.options || []).slice(0, 4);
        const more = (f.options || []).length - opts.length;
        return h('ul', { class: 'fb-mock fb-mock--opts fb-mock--' + t },
          opts.map((o) => h('li', null, h('span', { class: 'fb-mock__box', 'aria-hidden': 'true' }), o.label || 'Option')),
          more > 0 ? h('li', { class: 'fb-mock__more' }, '+ ' + more + ' more') : null,
          f.allow_other ? h('li', null, h('span', { class: 'fb-mock__box', 'aria-hidden': 'true' }), (f.other_label || 'Other') + ': please specify') : null,
          (f.options || []).length === 0 && !f.allow_other ? h('li', { class: 'fb-mock__more' }, 'No options yet') : null);
      }
      case 'yes_no': return h('div', { class: 'fb-mock fb-mock--yn' }, h('span', null, 'Yes'), h('span', null, 'No'));
      case 'rating': return h('div', { class: 'fb-mock fb-mock--stars', 'aria-hidden': 'true' }, [1, 2, 3, 4, 5].map(() => ic('star', 'icon--sm')));
      case 'file': {
        const fr = f.file || {};
        return h('div', { class: 'fb-mock fb-mock--file' }, ic('upload', 'icon--sm'), ' ', (fr.types || ['pdf']).map((x) => x.toUpperCase()).join(', '), ' up to ', String(fr.max_mb || 5), ' MB', (fr.max_files || 1) > 1 ? ', ' + fr.max_files + ' files' : '');
      }
      default: return null;
    }
  }

  function renderCard(entry) {
    const f = entry.field;
    const info = typeInfo(f.type);
    const input = isInput(f.type);
    const issues = S.issues.field[f.id] || [];
    const lvl = issues.some((i) => i.level === 'error') ? 'error' : (issues.length ? 'warning' : '');
    const label = (f.label || '').trim();
    const empty = f.type === 'heading' ? 'Click to write the heading' : (f.type === 'paragraph' ? 'Click to write the text' : 'Click to write your question');
    let main;
    if (f.type === 'heading') main = h('div', { class: 'fb-card__heading' }, label || h('span', { class: 'fb-card__empty' }, empty));
    else if (f.type === 'paragraph') main = h('div', { class: 'fb-card__para' }, (f.text || '').trim() ? (f.text.length > 220 ? f.text.slice(0, 220) + '...' : f.text) : h('span', { class: 'fb-card__empty' }, empty));
    else if (f.type === 'divider') main = h('hr', { class: 'fb-card__hr' });
    else if (f.type === 'consent') main = h('div', { class: 'fb-mock fb-mock--consent' }, h('span', { class: 'fb-mock__box', 'aria-hidden': 'true' }), h('span', { class: 'fb-card__label' }, label || h('span', { class: 'fb-card__empty' }, 'Click to write what people agree to'), f.required ? h('span', { class: 'fb-req', 'aria-hidden': 'true' }, ' *') : null));
    else main = h('div', { class: 'fb-card__label' }, label || h('span', { class: 'fb-card__empty' }, empty), f.required ? h('span', { class: 'fb-req', 'aria-hidden': 'true' }, ' *') : null);

    const badges = [];
    if (input && f.required) badges.push(h('span', { class: 'fb-chip fb-chip--req', title: 'Visitors must answer this' }, 'Required'));
    if (f.logic) badges.push(h('span', { class: 'fb-chip fb-chip--logic', title: 'Shown only when its conditions are met' }, ic('layers', 'icon--sm'), 'Conditional'));
    if (input && f.width && f.width !== 'full') badges.push(h('span', { class: 'fb-chip fb-chip--w', title: WIDTH_TEXT[f.width] }, f.width === 'half' ? '1/2' : '1/3'));

    const act = (icon, name, text, extra = {}) => h('button', Object.assign({ type: 'button', class: 'fb-iconbtn fb-iconbtn--sm', 'data-action': name, 'aria-label': text + ': ' + fieldTitle(f), title: text }, extra), ic(icon, 'icon--sm'));
    const card = h('div', {
      class: 'fb-card fb-card--' + (f.width || 'full') + (input ? '' : ' fb-card--layout') + ' fb-card--t-' + f.type + (lvl ? ' has-' + lvl : '') + (S.sel && S.sel.k === 'field' && S.sel.id === f.id ? ' is-selected' : ''),
      role: 'listitem', tabindex: '0', 'data-id': f.id, 'data-type': f.type,
      'aria-label': (entry.n ? 'Question ' + entry.n + ': ' : '') + fieldTitle(f) + ', ' + typeName(f.type) + (f.required ? ', required' : ''),
    },
    h('div', { class: 'fb-card__bar' },
      h('button', { type: 'button', class: 'fb-handle', tabindex: '-1', 'aria-hidden': 'true', title: 'Drag to move' }, ic('grip', 'icon--sm')),
      h('span', { class: 'fb-card__type' }, ic(info.icon, 'icon--sm'), h('span', null, typeName(f.type))),
      entry.n ? h('span', { class: 'fb-card__num' }, 'Q' + entry.n) : null,
      badges,
      h('span', { class: 'fb-card__actions' },
        act('arrow-up', 'up', 'Move up'), act('arrow-down', 'down', 'Move down'), act('copy', 'dup', 'Duplicate'), act('trash', 'del', 'Delete'))),
    h('div', { class: 'fb-card__body' },
      main,
      f.help && input ? h('div', { class: 'fb-card__help' }, f.help) : null,
      input && f.type !== 'consent' ? mockFor(f) : null),
    h('div', { class: 'fb-card__issues' }, issueNodes(issues)));
    return card;
  }
  function issueNodes(issues) {
    return issues.slice(0, 2).map((i) => h('p', { class: 'fb-issue fb-issue--' + i.level }, ic(i.level === 'error' ? 'alert-circle' : 'alert-triangle', 'icon--sm'), h('span', null, i.message)));
  }
  function applyIssues() {
    if (!S.dom.canvas) return;
    allFields().forEach(({ field }) => {
      const el = cardEl(field.id);
      if (!el) return;
      const issues = S.issues.field[field.id] || [];
      el.classList.toggle('has-error', issues.some((i) => i.level === 'error'));
      el.classList.toggle('has-warning', issues.length > 0 && !issues.some((i) => i.level === 'error'));
      const box = $('.fb-card__issues', el);
      if (box) box.replaceChildren(...issueNodes(issues));
    });
    pagesOf().forEach((p) => {
      const box = $('.fb-page[data-page-id="' + p.id + '"] .fb-page__issues', S.dom.canvas);
      if (box) box.replaceChildren(...issueNodes(S.issues.page[p.id] || []));
    });
    const g = $('.fb-global-issues', S.dom.canvas);
    if (g) g.replaceChildren(...issueNodes(S.issues.global.filter((c) => c.level === 'error')));
  }

  function refreshCard(id) {
    const old = cardEl(id);
    const e = allFields().find((x) => x.field.id === id);
    if (!old || !e) return;
    old.replaceWith(renderCard(e));
  }

  function renderPage(page, pi) {
    const total = pagesOf().length;
    const sel = S.sel && S.sel.k === 'page' && S.sel.id === page.id;
    const pageBtn = (icon, name, text, extra) => h('button', Object.assign({ type: 'button', class: 'fb-iconbtn fb-iconbtn--sm', 'data-page-action': name, 'data-page': page.id, 'aria-label': text + ' (page ' + (pi + 1) + ')', title: text }, extra || {}), ic(icon, 'icon--sm'));
    const head = h('div', { class: 'fb-page__head' },
      h('span', { class: 'fb-page__num' }, total > 1 ? 'Page ' + (pi + 1) + ' of ' + total : 'Your questions'),
      h('input', {
        class: 'fb-page__title', type: 'text', value: page.title, maxlength: lim().label || 200, 'data-page-title': page.id,
        placeholder: total > 1 ? 'Page title (optional)' : 'Heading above the questions (optional)', 'aria-label': 'Title of page ' + (pi + 1),
        oninput: (e) => { page.title = e.target.value; changed({ key: 'pt:' + page.id }); },
      }),
      page.logic ? h('span', { class: 'fb-chip fb-chip--logic', title: 'This whole page is shown only when its conditions are met' }, ic('layers', 'icon--sm'), 'Conditional page') : null,
      h('span', { class: 'fb-page__actions' },
        pageBtn('settings', 'settings', 'Page settings and conditions'),
        total > 1 ? pageBtn('arrow-up', 'up', 'Move page up', pi === 0 ? { disabled: true } : {}) : null,
        total > 1 ? pageBtn('arrow-down', 'down', 'Move page down', pi === total - 1 ? { disabled: true } : {}) : null,
        total > 1 ? pageBtn('minus', 'merge', pi === 0 ? 'Remove the page break after this page' : 'Remove the page break (join with the previous page)') : null,
        total > 1 ? pageBtn('trash', 'delete', 'Delete this page') : null));
    const list = h('div', { class: 'fb-fields', role: 'list', 'aria-label': 'Questions on page ' + (pi + 1), 'data-page': page.id });
    const entries = allFields().filter((x) => x.page.id === page.id);
    entries.forEach((e) => list.append(renderCard(e)));
    if (entries.length === 0) {
      list.append(h('div', { class: 'fb-empty' }, ic('plus', 'icon--sm'), h('p', null, pi === 0 && total === 1 ? 'Nothing here yet. Click a question type on the left (or "Add a question" below), or drag one into this space.' : 'This page is empty. Add a question or drag one here.')));
    }
    return h('section', { class: 'fb-page' + (sel ? ' is-selected' : ''), 'data-page-id': page.id, 'aria-label': total > 1 ? 'Page ' + (pi + 1) : 'Questions' },
      head, h('div', { class: 'fb-page__issues' }, issueNodes(S.issues.page[page.id] || [])), list,
      h('div', { class: 'fb-page__foot' },
        h('button', { type: 'button', class: 'fb-page__add' + (S.ins && S.ins.pageId === page.id ? ' is-active' : ''), 'data-page': page.id, onclick: () => chooseInsertion(page.id) }, ic('plus', 'icon--sm'), ' Add a question here')));
  }
  function chooseInsertion(pageId) {
    const p = findPage(pageId);
    if (!p) return;
    S.ins = { pageId, index: p.page.fields.length };
    S.paletteOpen = true;
    applyView();
    renderInsertHint();
    const first = $('.fb-pal', S.dom.palette);
    if (first) first.focus();
    say('New questions will be added at the end of page ' + (p.pi + 1) + '. Choose a question type.');
  }

  function renderCanvas() {
    const c = S.dom.canvas;
    if (!c) return;
    const active = doc.activeElement && c.contains(doc.activeElement) ? doc.activeElement : null;
    const keepId = active && active.closest ? (active.closest('.fb-card') || {}).dataset : null;
    const keepAction = active && active.dataset ? active.dataset.action : null;
    const pageTitleId = active && active.dataset ? active.dataset.pageTitle : null;
    const caret = pageTitleId && active.selectionStart !== undefined ? [active.selectionStart, active.selectionEnd] : null;
    c.replaceChildren();
    c.append(h('div', { class: 'fb-global-issues' }, issueNodes(S.issues.global.filter((x) => x.level === 'error'))));
    pagesOf().forEach((p, pi) => c.append(renderPage(p, pi)));
    c.append(h('div', { class: 'fb-canvas__foot' },
      h('button', { type: 'button', class: 'btn btn--outline btn--sm', onclick: () => addFromPalette('page_break', lastInsertion()) }, ic('page-break', 'icon--sm'), ' Add a page (step)'),
      h('p', { class: 'fb-canvas__hint' }, 'Tip: drag a card by its handle, or use the arrow buttons. Press Alt + Up or Down arrow on a card to move it.')));
    markSelected();
    if (keepId && keepId.id) {
      const target = keepAction ? $('.fb-card[data-id="' + keepId.id + '"] [data-action="' + keepAction + '"]', c) : cardEl(keepId.id);
      if (target) target.focus({ preventScroll: true });
    } else if (pageTitleId) {
      const t = $('[data-page-title="' + pageTitleId + '"]', c);
      if (t) { t.focus({ preventScroll: true }); if (caret) { try { t.setSelectionRange(caret[0], caret[1]); } catch (e) { /* ignore */ } } }
    }
  }
  function lastInsertion() {
    const last = pagesOf()[pagesOf().length - 1];
    return { pageId: last.id, index: last.fields.length };
  }

  /* ---- events: clicks, drag and drop, keyboard */
  function wireCanvasEvents() {
    const c = S.dom.canvas;

    c.addEventListener('click', (e) => {
      const pageBtn = e.target.closest('[data-page-action]');
      if (pageBtn) { pageAction(pageBtn.dataset.pageAction, pageBtn.dataset.page); return; }
      const btn = e.target.closest('.fb-card [data-action]');
      const card = e.target.closest('.fb-card');
      if (btn && card) {
        e.stopPropagation();
        const id = card.dataset.id;
        const act = btn.dataset.action;
        if (act === 'up') moveBy(id, -1, 'up');
        else if (act === 'down') moveBy(id, 1, 'down');
        else if (act === 'dup') duplicateField(id);
        else if (act === 'del') deleteField(id);
        return;
      }
      if (e.target.closest('.fb-page__head input, .fb-page__add, .fb-canvas__foot')) return;
      if (card) { select('field', card.dataset.id); return; }
      const pageEl = e.target.closest('.fb-page');
      if (pageEl && e.target.closest('.fb-page__head, .fb-page__issues')) select('page', pageEl.dataset.pageId);
    });

    c.addEventListener('focusin', (e) => {
      const t = e.target.closest('.fb-page__head input');
      if (t) { const pg = t.closest('.fb-page'); if (pg && !(S.sel && S.sel.k === 'page' && S.sel.id === pg.dataset.pageId)) { S.sel = { k: 'page', id: pg.dataset.pageId }; S.ins = null; markSelected(); renderInspector(); if (S.layout === 'wide') { S.side = 'field'; applyView(); } } }
    });

    /* drag and drop (HTML5). Cards only drag when the handle was pressed, so text selection inside the page title still works. */
    c.addEventListener('mousedown', (e) => {
      const hnd = e.target.closest('.fb-handle');
      if (hnd) hnd.closest('.fb-card').draggable = true;
    });
    c.addEventListener('mouseup', () => { $$('.fb-card[draggable="true"]', c).forEach((x) => { if (!S.drag) x.draggable = false; }); });
    c.addEventListener('dragstart', (e) => {
      const card = e.target.closest ? e.target.closest('.fb-card') : null;
      if (!card || !card.draggable) return;
      S.drag = { kind: 'field', id: card.dataset.id, el: card };
      e.dataTransfer.effectAllowed = 'move';
      try { e.dataTransfer.setData('text/plain', 'fb-field:' + card.dataset.id); } catch (err) { /* ignore */ }
      root.classList.add('is-dragging-field');
      setTimeout(() => card.classList.add('is-dragging'), 0);
    });
    c.addEventListener('dragover', (e) => {
      if (!S.drag) return;
      e.preventDefault();
      e.dataTransfer.dropEffect = S.drag.kind === 'new' ? 'copy' : 'move';
      placeDropMarker(e);
    });
    c.addEventListener('dragleave', (e) => {
      if (!S.drag) return;
      if (!e.relatedTarget || !c.contains(e.relatedTarget)) removeDropMarker();
    });
    c.addEventListener('drop', (e) => {
      if (!S.drag) return;
      e.preventDefault();
      const mark = S.dropMark;
      if (!mark || !mark.parentNode) { endDrag(); return; }
      const list = mark.parentNode;
      const pageId = list.dataset.page;
      let index = 0;
      for (const n of list.children) {
        if (n === mark) break;
        if (n.classList.contains('fb-card') && n !== (S.drag.el || null)) index++;
      }
      const drag = S.drag;
      endDrag();
      if (drag.kind === 'new') addFromPalette(drag.type, { pageId, index });
      else moveField(drag.id, pageId, index);
    });
    c.addEventListener('dragend', () => endDrag());
  }

  function nearestPage(y) {
    const pages = $$('.fb-page', S.dom.canvas);
    let best = null;
    let bestD = Infinity;
    for (const p of pages) {
      const r = p.getBoundingClientRect();
      const d = y < r.top ? r.top - y : (y > r.bottom ? y - r.bottom : 0);
      if (d < bestD) { bestD = d; best = p; }
    }
    return best;
  }
  function placeDropMarker(e) {
    const pageEl = e.target.closest('.fb-page') || nearestPage(e.clientY);
    if (!pageEl) return;
    const list = $('.fb-fields', pageEl);
    if (!S.dropMark) S.dropMark = h('div', { class: 'fb-drop', 'aria-hidden': 'true' });
    const cards = $$(':scope > .fb-card', list).filter((x) => x !== (S.drag && S.drag.el));
    const lw = list.getBoundingClientRect().width;
    let before = null;
    for (const c of cards) {
      const r = c.getBoundingClientRect();
      if (e.clientY < r.top) { before = c; break; }
      if (e.clientY <= r.bottom) {
        const wide = r.width > lw * 0.75;
        if (wide ? e.clientY < r.top + r.height / 2 : e.clientX < r.left + r.width / 2) { before = c; break; }
      }
    }
    const empty = $(':scope > .fb-empty', list);
    if (empty) list.insertBefore(S.dropMark, empty); else if (before) list.insertBefore(S.dropMark, before); else list.append(S.dropMark);
    S.dropMark.className = 'fb-drop' + (S.drag && S.drag.el && !before ? ' fb-drop--end' : '');
  }
  function removeDropMarker() { if (S.dropMark && S.dropMark.parentNode) S.dropMark.remove(); }
  function endDrag() {
    removeDropMarker();
    if (S.drag && S.drag.el) { S.drag.el.classList.remove('is-dragging'); S.drag.el.draggable = false; }
    S.drag = null;
    root.classList.remove('is-dragging-field', 'is-dragging-new');
    $$('.fb-card[draggable="true"]', S.dom.canvas).forEach((x) => { x.draggable = false; });
  }

  /** WAI-ARIA tabs: Left / Right / Home / End move focus AND select (roving tabindex), for a list of [button, action] pairs. */
  function tabKeys(list, items) {
    list.addEventListener('keydown', (e) => {
      const live = items.filter(([b]) => !b.hidden);
      const i = live.findIndex(([b]) => b === doc.activeElement);
      if (i < 0) return;
      let n = i;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') n = (i + 1) % live.length;
      else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') n = (i - 1 + live.length) % live.length;
      else if (e.key === 'Home') n = 0;
      else if (e.key === 'End') n = live.length - 1;
      else return;
      e.preventDefault();
      live[n][1]();
      live[n][0].focus();
    });
  }

  function wireKeyboard() {
    const c = S.dom.canvas;
    tabKeys($('.fb-tabs', S.dom.bar), [[S.dom.tabQ, () => setWs('questions')], [S.dom.tabS, () => setWs('settings')]]);
    tabKeys(S.dom.sideTabs, [[S.dom.sideTabField, () => setSide('field')], [S.dom.sideTabPreview, () => setSide('preview')]]);
    // Escape closes the drawer from anywhere inside it, also from a text box (the document handler skips typing); a native dropdown keeps its own Escape
    S.dom.side.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape' || S.layout !== 'compact' || !S.drawer || e.defaultPrevented) return;
      if (e.target.tagName === 'SELECT' || doc.querySelector('dialog[open]') || e.target.closest('[role=menu]')) return;
      e.preventDefault();
      e.stopPropagation();
      closeDrawer(true);
    });
    c.addEventListener('keydown', (e) => {
      const card = e.target.closest('.fb-card');
      if (!card || e.target.closest('input, textarea, select')) return;
      const id = card.dataset.id;
      const onCard = e.target === card;
      const mod = e.altKey || e.ctrlKey || e.metaKey;
      if (e.key === 'ArrowUp' && mod) { e.preventDefault(); moveBy(id, -1); }
      else if (e.key === 'ArrowDown' && mod) { e.preventDefault(); moveBy(id, 1); }
      else if (onCard && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); select('field', id, { focusLabel: true }); }
      else if (onCard && (e.key === 'Delete' || e.key === 'Backspace')) { e.preventDefault(); deleteField(id); }
      else if (onCard && (e.key === 'd' || e.key === 'D') && (e.ctrlKey || e.metaKey)) { e.preventDefault(); duplicateField(id); }
      else if (onCard && e.key === 'ArrowDown') { e.preventDefault(); const n = $$('.fb-card', c); const i = n.indexOf(card); if (n[i + 1]) n[i + 1].focus(); }
      else if (onCard && e.key === 'ArrowUp') { e.preventDefault(); const n = $$('.fb-card', c); const i = n.indexOf(card); if (n[i - 1]) n[i - 1].focus(); }
      else if (e.key === 'Escape' && S.layout === 'compact' && S.drawer) { closeDrawer(true); }
    });
    doc.addEventListener('keydown', (e) => {
      if (!doc.body.contains(root)) return;
      const mod = e.ctrlKey || e.metaKey;
      const typing = e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"], dialog');
      if (mod && (e.key === 's' || e.key === 'S')) { e.preventDefault(); save({ autosave: false }); return; }
      if (typing) return;
      if (mod && (e.key === 'z' || e.key === 'Z') && !e.shiftKey) { e.preventDefault(); undo(); }
      else if (mod && (e.key === 'y' || e.key === 'Y' || ((e.key === 'z' || e.key === 'Z') && e.shiftKey))) { e.preventDefault(); redo(); }
      else if (e.key === 'Escape' && S.layout === 'compact' && S.drawer && !doc.querySelector('dialog[open]')) closeDrawer(true);
    });
  }

  /* ---- structure actions */
  function refsTo(id) {
    let n = 0;
    allFields().forEach(({ field }) => { if (field.logic && field.logic.rules.some((r) => r.field === id)) n++; });
    pagesOf().forEach((p) => { if (p.logic && p.logic.rules.some((r) => r.field === id)) n++; });
    return n;
  }
  function focusCard(id, action) {
    const el = cardEl(id);
    if (!el) return;
    const t = action ? $('[data-action="' + action + '"]', el) : null;
    (t || el).focus({ preventScroll: false });
  }
  function positionText(id) {
    const all = allFields();
    const i = all.findIndex((x) => x.field.id === id);
    return 'position ' + (i + 1) + ' of ' + all.length;
  }
  function moveField(id, toPageId, index) {
    const l = locate(id);
    const target = findPage(toPageId);
    if (!l || !target) return;
    l.page.fields.splice(l.fi, 1);
    target.page.fields.splice(clamp(index, 0, target.page.fields.length), 0, l.field);
    changed({ full: true, say: '"' + fieldTitle(l.field) + '" moved to ' + positionText(id) + '.' });
    focusCard(id);
  }
  function moveBy(id, delta, action) {
    const l = locate(id);
    if (!l) return;
    const pages = pagesOf();
    let pi = l.pi;
    let fi = l.fi + delta;
    if (fi < 0) {
      if (pi === 0) { say('"' + fieldTitle(l.field) + '" is already first.'); return; }
      pi -= 1;
      fi = pages[pi].fields.length;
    } else if (fi > l.page.fields.length - 1) {
      if (pi === pages.length - 1) { say('"' + fieldTitle(l.field) + '" is already last.'); return; }
      pi += 1;
      fi = 0;
    }
    l.page.fields.splice(l.fi, 1);
    pages[pi].fields.splice(fi, 0, l.field);
    changed({ full: true, say: '"' + fieldTitle(l.field) + '" moved to ' + positionText(id) + (pi !== l.pi ? ', on page ' + (pi + 1) : '') + '.' });
    focusCard(id, action);
    scrollToCard(id);
  }
  function duplicateField(id) {
    const l = locate(id);
    if (!l) return;
    if (fieldTotal() >= (lim().fields || 150)) { toast('A form can have at most ' + lim().fields + ' items.', 'warn'); return; }
    const copy = clone(l.field);
    copy.id = newId('f');
    if (copy.label) copy.label = (copy.label + ' (copy)').slice(0, lim().label || 200);
    copy.key = isInput(copy.type) ? uniqueKey(keyFromLabel(copy.label || copy.key || 'field'), copy.id) : '';
    l.page.fields.splice(l.fi + 1, 0, copy);
    changed({ full: true, say: 'Duplicated "' + fieldTitle(l.field) + '".' });
    select('field', copy.id);
    focusCard(copy.id);
  }
  async function deleteField(id) {
    const l = locate(id);
    if (!l) return;
    const refs = refsTo(id);
    const ok = await confirmBox({
      title: 'Delete this ' + (isInput(l.field.type) ? 'question' : 'item') + '?',
      text: ['"' + fieldTitle(l.field) + '" will be removed from the form.' + (refs ? ' ' + refs + ' condition' + (refs === 1 ? '' : 's') + ' in other places depend on it and will need fixing.' : ''),
        isInput(l.field.type) && S.form.submissions_count > 0 ? 'Answers already received keep what people typed for it.' : 'You can undo this with the Undo button.'],
      confirm: 'Delete', danger: true,
    });
    if (!ok) return;
    const l2 = locate(id);
    if (!l2) return;
    const all = allFields();
    const idx = all.findIndex((x) => x.field.id === id);
    l2.page.fields.splice(l2.fi, 1);
    const neighbour = all[idx + 1] || all[idx - 1];
    if (S.sel && S.sel.id === id) { S.sel = null; renderInspector(); if (S.layout === 'compact') S.drawer = false; if (S.layout === 'wide') S.side = 'preview'; applyView(); }
    changed({ full: true, say: 'Deleted "' + fieldTitle(l2.field) + '".' });
    if (neighbour) focusCard(neighbour.field.id);
  }

  function pageAction(action, pageId) {
    const f = findPage(pageId);
    if (!f) return;
    if (action === 'settings') { select('page', pageId, { focusLabel: true }); return; }
    if (action === 'up' || action === 'down') {
      const to = f.pi + (action === 'up' ? -1 : 1);
      if (to < 0 || to >= pagesOf().length) return;
      const [pg] = pagesOf().splice(f.pi, 1);
      pagesOf().splice(to, 0, pg);
      changed({ full: true, say: 'Page moved. It is now page ' + (to + 1) + ' of ' + pagesOf().length + '.' });
      const b = $('.fb-page[data-page-id="' + pageId + '"] [data-page-action="' + action + '"]', S.dom.canvas);
      if (b && !b.disabled) b.focus(); else { const t = $('.fb-page[data-page-id="' + pageId + '"] [data-page-title]', S.dom.canvas); if (t) t.focus(); }
    } else if (action === 'merge') mergePage(pageId);
    else if (action === 'delete') deletePage(pageId);
  }
  function mergePage(pageId) {
    const f = findPage(pageId);
    if (!f || pagesOf().length < 2) return;
    if (f.pi === 0) {
      const next = pagesOf()[1];
      next.fields = f.page.fields.concat(next.fields);
      pagesOf().splice(0, 1);
    } else {
      const prev = pagesOf()[f.pi - 1];
      prev.fields = prev.fields.concat(f.page.fields);
      pagesOf().splice(f.pi, 1);
    }
    if (S.sel && S.sel.k === 'page' && S.sel.id === pageId) { S.sel = null; renderInspector(); }
    changed({ full: true, say: 'The page break was removed. The pages were joined.' });
  }
  async function deletePage(pageId) {
    const f = findPage(pageId);
    if (!f || pagesOf().length < 2) return;
    const n = f.page.fields.length;
    if (n === 0) { mergePage(pageId); return; }
    const choice = await openDialog({
      title: 'Delete page ' + (f.pi + 1) + '?',
      body: h('div', { class: 'fb-dialog__text' }, h('p', null, 'This page has ' + n + ' item' + (n === 1 ? '' : 's') + '. What should happen to ' + (n === 1 ? 'it' : 'them') + '?'), h('p', { class: 'fb-muted' }, 'You can undo this with the Undo button.')),
      actions: [{ label: 'Cancel', value: null }, { label: 'Delete the page and its items', value: 'delete', class: 'btn--danger' }, { label: 'Keep the items (join the pages)', value: 'merge', class: 'btn--primary', primary: true }],
    });
    if (choice === 'merge') mergePage(pageId);
    else if (choice === 'delete') {
      pagesOf().splice(f.pi, 1);
      if (S.sel && (S.sel.id === pageId || f.page.fields.some((x) => x.id === S.sel.id))) { S.sel = null; renderInspector(); }
      changed({ full: true, say: 'Page deleted.' });
    }
  }

  /* ================================================================== 6. the settings panel of a question / page */

  /* ---- little form controls (also used by the form settings) */
  function fld(label, control, o = {}) {
    const id = o.forId || control.id || (control.id = uid('fb-c'));
    if (!o.forId && !control.id) control.id = id;
    const helpId = o.help ? id + '-h' : null;
    if (helpId && control.setAttribute) control.setAttribute('aria-describedby', helpId);
    return h('div', { class: 'fb-field' + (o.class ? ' ' + o.class : '') },
      h('label', { class: 'fb-field__label', for: id }, label, o.optional ? h('span', { class: 'fb-opt' }, ' (optional)') : null),
      control,
      o.help ? h('p', { class: 'fb-field__help', id: helpId }, o.help) : null,
      o.err ? h('p', { class: 'fb-field__error', 'data-err': o.err, role: 'alert' }) : null);
  }
  function tInput(o) {
    const el = h('input', {
      class: 'input', type: o.type || 'text', value: o.value === undefined || o.value === null ? '' : o.value, maxlength: o.max || null, placeholder: o.placeholder || null,
      min: o.min === undefined ? null : o.min, step: o.step || null, inputmode: o.inputmode || null, 'data-autofocus': o.autofocus ? '' : null, autocomplete: 'off', spellcheck: o.spellcheck === false ? 'false' : null, readonly: o.readonly || null,
      'aria-label': o.label || null,
    });
    if (o.onInput) el.addEventListener('input', () => o.onInput(el.value, el));
    if (o.onChange) el.addEventListener('change', () => o.onChange(el.value, el));
    return el;
  }
  function tArea(o) {
    const el = h('textarea', { class: 'textarea', rows: o.rows || 3, maxlength: o.max || null, placeholder: o.placeholder || null, 'data-autofocus': o.autofocus ? '' : null, spellcheck: o.spellcheck === false ? 'false' : null });
    el.value = o.value === undefined || o.value === null ? '' : o.value;
    if (o.onInput) el.addEventListener('input', () => o.onInput(el.value, el));
    if (o.onChange) el.addEventListener('change', () => o.onChange(el.value, el));
    return el;
  }
  function tSelect(options, value, onChange, o = {}) {
    const el = h('select', { class: 'select' }, options.map(([v, l, dis]) => h('option', { value: v, disabled: dis || null }, l)));
    el.value = value === undefined || value === null ? '' : String(value);
    if (el.value !== String(value ?? '') && options.length) el.value = options[0][0];
    el.addEventListener('change', () => onChange(el.value, el));
    if (o['aria-label']) el.setAttribute('aria-label', o['aria-label']);
    return el;
  }
  function tSwitch(label, checked, onChange, help) {
    const id = uid('fb-sw');
    const input = h('input', { type: 'checkbox', id, checked: !!checked, 'aria-describedby': help ? id + '-h' : null });
    input.addEventListener('change', () => onChange(input.checked, input));
    return h('div', { class: 'fb-switch' },
      h('label', { class: 'switch', for: id }, input, h('span', { class: 'switch__track', 'aria-hidden': 'true' }), h('span', { class: 'switch__text' }, label)),
      help ? h('p', { class: 'fb-field__help', id: id + '-h' }, help) : null);
  }
  function tSeg(name, options, value, onChange, label) {
    const gid = uid('fb-seg');
    return h('fieldset', { class: 'fb-seg' },
      h('legend', { class: 'fb-field__label' }, label),
      h('div', { class: 'fb-seg__row' }, options.map(([v, text, title]) => {
        const id = gid + '-' + v;
        const r = h('input', { type: 'radio', name: gid, id, value: v, checked: v === value });
        r.addEventListener('change', () => { if (r.checked) onChange(v); });
        return h('span', { class: 'fb-seg__opt' }, r, h('label', { for: id, title: title || null }, text));
      })));
  }
  const grid2 = (...kids) => h('div', { class: 'fb-grid2' }, kids);

  /** Collapsible group; its open / closed state survives redraws. */
  S.secOpen = {};
  function section(name, title, nodes, defOpen = true, badge = null) {
    const open = name in S.secOpen ? S.secOpen[name] : defOpen;
    const d = h('details', { class: 'fb-sec', open: open ? true : null }, h('summary', { class: 'fb-sec__title' }, h('span', null, title), badge ? h('span', { class: 'fb-sec__badge' }, badge) : null, ic('chevron-down', 'icon--sm fb-sec__chev')), h('div', { class: 'fb-sec__body' }, nodes));
    d.addEventListener('toggle', () => { S.secOpen[name] = d.open; });
    return d;
  }

  /* ---- inspector */
  function renderInspector() {
    const box = S.dom.inspector;
    if (!box) return;
    const key = S.sel ? S.sel.k + ':' + S.sel.id : '';
    const keep = S.inspFor === key ? box.scrollTop : 0;
    S.inspFor = key;
    box.replaceChildren();
    if (!S.sel) {
      box.append(h('div', { class: 'fb-insp-empty' },
        h('span', { class: 'fb-insp-empty__icon' }, ic('edit')),
        h('h2', null, 'Select a question'),
        h('p', null, 'Click any question in your form to change its wording, choices, rules and conditions here.'),
        h('button', { type: 'button', class: 'btn btn--outline btn--sm fb-only-compact', onclick: () => { closeDrawer(false); S.paletteOpen = true; applyView(); const p = $('.fb-pal', S.dom.palette); if (p) p.focus(); } }, ic('plus', 'icon--sm'), ' Add a question')));
      return;
    }
    if (S.sel.k === 'page') {
      const f = findPage(S.sel.id);
      if (f) box.append(pageInspector(f));
    } else {
      const l = locate(S.sel.id);
      if (l) box.append(fieldInspector(l));
    }
    box.scrollTop = keep;
  }

  function fieldInspector(l) {
    const f = l.field;
    const info = typeInfo(f.type);
    const input = isInput(f.type);
    const all = allFields();
    const idx = all.findIndex((x) => x.field.id === f.id);
    const wrap = h('div', { class: 'fb-insp' });
    wrap.append(h('div', { class: 'fb-insp__head' },
      h('div', { class: 'fb-insp__type' }, h('span', { class: 'fb-insp__icon' }, ic(info.icon)), h('div', null, h('h2', { class: 'fb-insp__name' }, typeName(f.type)), h('span', { class: 'fb-insp__pos' }, 'Page ' + (l.pi + 1) + ' - item ' + (idx + 1) + ' of ' + all.length))),
      h('div', { class: 'fb-insp__actions' }, iconBtn('copy', 'Duplicate this item', () => duplicateField(f.id)), iconBtn('trash', 'Delete this item', () => deleteField(f.id)))));

    /* --- the wording */
    const q = [];
    if (f.type === 'divider') {
      q.push(h('p', { class: 'fb-field__help' }, 'A thin line between two parts of the form. There is nothing to set.'));
    } else if (f.type === 'paragraph') {
      q.push(fld('Text shown to visitors', tArea({ value: f.text, rows: 6, max: lim().text || 2000, autofocus: true, onInput: (v) => { f.text = v; changed({ key: 'text:' + f.id, card: f.id }); } }),
        { help: 'Plain text. Leave an empty line between paragraphs.' }));
    } else if (f.type === 'heading') {
      q.push(fld('Heading', tInput({ value: f.label, max: lim().label || 200, autofocus: true, onInput: (v) => { f.label = v; changed({ key: 'label:' + f.id, card: f.id }); } }), { help: 'A title that starts a new part of the form.' }));
    } else if (f.type === 'consent') {
      q.push(fld('What people agree to', tArea({ value: f.label, rows: 3, max: lim().label || 200, autofocus: true, onInput: (v) => { const prev = f.label; f.label = v; onLabelChanged(f, prev); changed({ key: 'label:' + f.id, card: f.id }); } }),
        { help: 'Shown next to the tick box, for example "I agree that Lumina may store my details to process this application."' }));
      q.push(fld('Help text', tArea({ value: f.help, rows: 2, max: lim().help || 600, onInput: (v) => { f.help = v; changed({ key: 'help:' + f.id, card: f.id }); } }), { optional: true }));
      q.push(tSwitch('People must tick this box to send the form', f.required, (on) => { f.required = on; changed({ card: f.id }); }));
    } else {
      q.push(fld('Your question', tInput({
        value: f.label, max: lim().label || 200, autofocus: true, placeholder: questionExample(f.type),
        onInput: (v) => { const prev = f.label; f.label = v; onLabelChanged(f, prev); changed({ key: 'label:' + f.id, card: f.id }); },
      }), { help: 'What visitors read above the answer box.' }));
      q.push(fld('Help text', tArea({ value: f.help, rows: 2, max: lim().help || 600, onInput: (v) => { f.help = v; changed({ key: 'help:' + f.id, card: f.id }); } }), { optional: true, help: 'A short hint shown under the question.' }));
      if (hasFlag(f.type, 'placeholder')) {
        q.push(fld('Example inside the box', tInput({ value: f.placeholder, max: lim().placeholder || 160, onInput: (v) => { f.placeholder = v; changed({ key: 'ph:' + f.id, card: f.id }); } }), { optional: true, help: 'Grey text that disappears when people start typing.' }));
      }
      q.push(tSwitch('People must answer this question', f.required, (on) => { f.required = on; changed({ card: f.id }); }));
    }
    if (input) {
      q.push(tSeg('width', [['full', 'Full width'], ['half', 'Half'], ['third', 'One third']], f.width || 'full', (v) => { f.width = v; changed({ card: f.id, full: true }); }, 'How wide on a computer'));
      q.push(h('p', { class: 'fb-field__help' }, 'Half and one-third questions sit side by side. On phones every question is full width.'));
    }
    wrap.append(section('q', f.type === 'paragraph' || f.type === 'heading' || f.type === 'divider' ? typeName(f.type) : 'The question', q, true));

    /* --- choices */
    if (hasFlag(f.type, 'options')) wrap.append(section('opts', 'Answer choices', [optionsEditor(f)], true, String((f.options || []).length)));
    /* --- pre-filled answer */
    if (hasFlag(f.type, 'default')) wrap.append(section('default', 'Pre-filled answer', [defaultEditor(f)], false));
    /* --- rules */
    const rules = rulesEditor(f);
    if (rules) wrap.append(section('rules', 'Rules for the answer', rules, false));
    /* --- files */
    if (hasFlag(f.type, 'file')) wrap.append(section('files', 'File rules', [fileEditor(f)], true));
    /* --- conditions */
    wrap.append(section('cond', 'Show only if...', [conditionsEditor(f, { ownerId: f.id, ownerPi: l.pi, pageLevel: false })], !!f.logic, f.logic ? 'On' : null));
    /* --- advanced */
    if (input) {
      wrap.append(section('adv', 'Advanced', [fld('Internal name', tInput({
        value: f.key, max: lim().key || 40, spellcheck: false,
        onInput: (v) => { f.key = v.toLowerCase().replace(/[^a-z0-9_]/g, '_'); changed({ key: 'key:' + f.id }); },
        onChange: (v, el) => { const k = uniqueKey(keyFromLabel(f.key || f.label || 'field'), f.id); if (f.key !== k) { f.key = k; el.value = k; changed({}); } },
      }), { help: 'Used in e-mail messages as {' + (f.key || 'name') + '}. It follows the question text until the form has been published; after that it stays the same so your e-mails keep working.' })], false));
    }
    return wrap;
  }
  function questionExample(t) {
    return ({ text: 'For example: Full name', textarea: 'For example: Tell us about yourself', email: 'For example: Your e-mail address', phone: 'For example: Phone number (WhatsApp if possible)', number: 'For example: How many people are coming?', date: 'For example: Date of birth', time: 'For example: Preferred time', select: 'For example: Which district do you live in?', radio: 'For example: How did you hear about us?', checkboxes: 'For example: Which days can you help?', yes_no: 'For example: Can you work weekends?', rating: 'For example: How was the training?', file: 'For example: Upload your CV' })[t] || '';
  }

  /* ---- options */
  function optionValueFor(f, label, ownIndex) {
    const used = new Set((f.options || []).filter((o, i) => i !== ownIndex).map((o) => o.value));
    const base = slugify(label, '-', 40) || 'option';
    let v = base === OTHER ? 'option' : base;
    for (let n = 2; used.has(v); n++) v = base.slice(0, 36) + '-' + n;
    return v;
  }
  function optionReferenced(f, value) {
    const d = f.default;
    if (Array.isArray(d) ? d.includes(value) : d === value) return true;
    let hit = false;
    const scan = (logic) => { if (logic && logic.rules.some((r) => r.field === f.id && r.value === value)) hit = true; };
    allFields().forEach((x) => scan(x.field.logic));
    pagesOf().forEach((p) => scan(p.logic));
    return hit;
  }
  function optionsEditor(f) {
    const wrap = h('div', { class: 'fb-opts' });
    const list = h('ul', { class: 'fb-opts__list' });
    const otherBox = h('div', { class: 'fb-opts__other' });
    const pasteBox = h('div', { class: 'fb-opts__paste', hidden: true });
    const count = () => {
      const b = list.closest('details') ? $('.fb-sec__badge', list.closest('details')) : null;
      if (b) b.textContent = String(f.options.length);
    };
    const touch = (o = {}) => { changed(Object.assign({ card: f.id }, o)); count(); };
    function addAt(i, focus) {
      if (f.options.length >= (lim().options || 200)) { toast('A choice list can have at most ' + lim().options + ' options.', 'warn'); return; }
      const n = f.options.length + 1;
      const o = { value: optionValueFor(f, 'option ' + n, -1), label: '' };
      f.options.splice(i, 0, o);
      draw();
      touch();
      if (focus) { const inp = $('[data-opt="' + i + '"]', list); if (inp) inp.focus(); }
    }
    function removeAt(i, focusPrev) {
      f.options.splice(i, 1);
      draw();
      touch();
      if (focusPrev) { const inp = $('[data-opt="' + Math.max(0, i - 1) + '"]', list); if (inp) inp.focus(); }
    }
    function moveOpt(i, d) {
      const j = i + d;
      if (j < 0 || j >= f.options.length) return;
      const [o] = f.options.splice(i, 1);
      f.options.splice(j, 0, o);
      draw();
      touch();
      const b = $('[data-opt-row="' + j + '"] [data-opt-' + (d < 0 ? 'up' : 'down') + ']', list);
      if (b && !b.disabled) b.focus(); else { const inp = $('[data-opt="' + j + '"]', list); if (inp) inp.focus(); }
    }
    function draw() {
      list.replaceChildren(...f.options.map((o, i) => {
        const inp = h('input', {
          class: 'input', type: 'text', value: o.label, maxlength: lim().option_label || 120, 'data-opt': i, 'aria-label': 'Choice ' + (i + 1), placeholder: 'Type a choice',
          oninput: () => { o.label = inp.value; touch({ key: 'opt:' + f.id + ':' + i }); },
          onchange: () => {
            if (/^option(-\d+)?$/.test(o.value) && o.label.trim() && canRekey() && !optionReferenced(f, o.value)) { o.value = optionValueFor(f, o.label, i); changed({ card: f.id }); }
          },
          onkeydown: (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addAt(i + 1, true); }
            else if (e.key === 'Backspace' && inp.value === '' && f.options.length > 1) { e.preventDefault(); removeAt(i, true); }
            else if ((e.key === 'ArrowUp' || e.key === 'ArrowDown') && (e.altKey || e.ctrlKey)) { e.preventDefault(); moveOpt(i, e.key === 'ArrowUp' ? -1 : 1); }
          },
        });
        return h('li', { class: 'fb-opts__row', 'data-opt-row': i },
          h('span', { class: 'fb-opts__order' },
            h('button', { type: 'button', class: 'fb-iconbtn fb-iconbtn--xs', 'data-opt-up': '', 'aria-label': 'Move choice ' + (i + 1) + ' up', disabled: i === 0 || null, onclick: () => moveOpt(i, -1) }, ic('chevron-up', 'icon--sm')),
            h('button', { type: 'button', class: 'fb-iconbtn fb-iconbtn--xs', 'data-opt-down': '', 'aria-label': 'Move choice ' + (i + 1) + ' down', disabled: i === f.options.length - 1 || null, onclick: () => moveOpt(i, 1) }, ic('chevron-down', 'icon--sm'))),
          inp,
          h('button', { type: 'button', class: 'fb-iconbtn fb-iconbtn--sm', 'aria-label': 'Remove choice ' + (i + 1), title: 'Remove', onclick: () => removeAt(i, false) }, ic('x', 'icon--sm')));
      }));
      if (f.options.length === 0) list.append(h('li', { class: 'fb-muted' }, 'No choices yet.'));
    }
    function drawOther() {
      otherBox.replaceChildren();
      if (!hasFlag(f.type, 'other')) return;
      otherBox.append(tSwitch('Add an "Other: please specify" choice', !!f.allow_other, (on) => { f.allow_other = on; drawOther(); touch(); }, 'People who pick it type their own answer in a box.'));
      if (f.allow_other) otherBox.append(fld('Wording of that choice', tInput({ value: f.other_label || '', max: lim().option_label || 120, placeholder: 'Other', onInput: (v) => { f.other_label = v; touch({ key: 'other:' + f.id }); } }), { optional: true }));
    }
    function drawPaste() {
      pasteBox.replaceChildren();
      const ta = tArea({ rows: 6, placeholder: 'One choice per line:\nKampala\nGulu\nJinja', spellcheck: false });
      const parse = () => {
        const seen = new Set();
        return ta.value.split(/\r?\n/).map((s) => s.replace(/^[\s\-*•\d.)]+/, '').trim()).filter((s) => { const k = s.toLowerCase(); if (!s || seen.has(k)) return false; seen.add(k); return true; }).map((s) => s.slice(0, lim().option_label || 120));
      };
      const apply = (replace) => {
        const labels = parse();
        if (!labels.length) { toast('Type or paste at least one choice, one per line.', 'warn'); return; }
        const base = replace ? [] : f.options.slice();
        const have = new Set(base.map((o) => o.label.toLowerCase()));
        let added = 0;
        let dup = 0;
        let over = 0;
        const max = lim().options || 200;
        for (const label of labels) {
          if (have.has(label.toLowerCase())) { dup++; continue; }
          if (base.length >= max) { over++; continue; }
          f.options = base;
          base.push({ value: optionValueFor({ options: base }, label, -1), label });
          have.add(label.toLowerCase());
          added++;
        }
        f.options = base;
        pasteBox.hidden = true;
        draw();
        let msg = added + ' choice' + (added === 1 ? '' : 's') + ' added.';
        if (dup) msg += ' ' + dup + ' skipped because ' + (dup === 1 ? 'it is' : 'they are') + ' already in the list.';
        if (over) msg += ' ' + over + ' not added: a question can have at most ' + max + ' choices.';
        if (dup || over) toast(msg, 'warn');
        touch({ say: msg });
      };
      pasteBox.append(
        h('label', { class: 'fb-field__label', for: (ta.id = uid('fb-paste')) }, 'Paste a list'),
        ta,
        h('div', { class: 'fb-btnrow' },
          h('button', { type: 'button', class: 'btn btn--sm btn--primary', onclick: () => apply(false) }, 'Add to the list'),
          h('button', { type: 'button', class: 'btn btn--sm btn--outline', onclick: () => apply(true) }, 'Replace the whole list'),
          h('button', { type: 'button', class: 'btn btn--sm btn--ghost', onclick: () => { pasteBox.hidden = true; } }, 'Cancel')));
    }
    drawPaste();
    wrap.append(list,
      h('div', { class: 'fb-btnrow' },
        h('button', { type: 'button', class: 'btn btn--sm btn--outline', onclick: () => addAt(f.options.length, true) }, ic('plus', 'icon--sm'), ' Add a choice'),
        h('button', { type: 'button', class: 'btn btn--sm btn--ghost', onclick: () => { pasteBox.hidden = !pasteBox.hidden; if (!pasteBox.hidden) { const t = $('textarea', pasteBox); if (t) t.focus(); } } }, ic('list', 'icon--sm'), ' Paste a list')),
      pasteBox, otherBox);
    draw();
    drawOther();
    return wrap;
  }

  /* ---- pre-filled answer */
  function defaultEditor(f) {
    const set = (v) => { f.default = v; changed({ key: 'def:' + f.id, card: f.id }); };
    const t = f.type;
    if (t === 'select' || t === 'radio') {
      return fld('Selected when the form opens', tSelect([['', 'Nothing selected']].concat((f.options || []).map((o) => [o.value, o.label || o.value])), f.default || '', (v) => set(v)), { optional: true });
    }
    if (t === 'checkboxes') {
      const cur = new Set(Array.isArray(f.default) ? f.default : []);
      const box = h('div', { class: 'fb-checks', role: 'group', 'aria-label': 'Choices ticked when the form opens' }, (f.options || []).map((o) => {
        const id = uid('fb-dc');
        const cb = h('input', { type: 'checkbox', id, checked: cur.has(o.value) });
        cb.addEventListener('change', () => { const next = (f.options || []).filter((x) => (x.value === o.value ? cb.checked : cur.has(x.value))).map((x) => x.value); cur.clear(); next.forEach((x) => cur.add(x)); set(next); });
        return h('label', { class: 'fb-check', for: id }, cb, ' ', o.label || o.value);
      }));
      return h('div', { class: 'fb-field' }, h('p', { class: 'fb-field__label' }, 'Ticked when the form opens', h('span', { class: 'fb-opt' }, ' (optional)')), box);
    }
    if (t === 'yes_no') return fld('Selected when the form opens', tSelect([['', 'Nothing selected'], ['yes', 'Yes'], ['no', 'No']], f.default || '', (v) => set(v)), { optional: true });
    if (t === 'date') {
      const today = f.default === 'today';
      const d = tInput({ type: 'date', label: 'Fixed pre-filled date', value: today ? '' : (f.default || ''), onInput: (v) => set(v) });
      const sel = tSelect([['', 'Empty'], ['today', "Today's date"], ['date', 'A fixed date']], today ? 'today' : (f.default ? 'date' : ''), (v) => { d.hidden = v !== 'date'; if (v === 'today') set('today'); else if (v === '') set(''); else set(d.value || ''); }, { 'aria-label': 'Pre-filled date' });
      d.hidden = !(f.default && f.default !== 'today');
      return h('div', { class: 'fb-field' }, h('p', { class: 'fb-field__label' }, 'Pre-filled date', h('span', { class: 'fb-opt' }, ' (optional)')), sel, d);
    }
    if (t === 'time') return fld('Pre-filled time', tInput({ type: 'time', value: f.default || '', onInput: (v) => set(v) }), { optional: true });
    if (t === 'number') return fld('Pre-filled number', tInput({ type: 'number', value: f.default || '', onInput: (v) => set(v) }), { optional: true });
    if (t === 'textarea') return fld('Pre-filled text', tArea({ value: f.default || '', rows: 3, max: 500, onInput: (v) => set(v) }), { optional: true });
    return fld('Pre-filled answer', tInput({ value: f.default || '', max: lim().default || 500, onInput: (v) => set(v) }), { optional: true, help: 'People can change it.' });
  }

  /* ---- validation rules (presets only: no free-form patterns) */
  function rulesEditor(f) {
    const t = f.type;
    const nodes = [];
    const has = (x) => hasFlag(t, x);
    if (!(has('length') || has('pattern') || has('range') || has('count') || has('date_range') || has('time_range'))) return null;
    const v = f.validation || (f.validation = {});
    const setNum = (key, val, int) => {
      if (val === '' || val === null || Number.isNaN(Number(val))) delete v[key]; else v[key] = int ? Math.round(Number(val)) : Number(val);
      changed({ key: 'v:' + f.id + key, card: f.id });
    };
    if (has('length')) {
      nodes.push(grid2(
        fld('Fewest characters', tInput({ type: 'number', min: 0, value: v.min_length ?? '', placeholder: 'No minimum', onInput: (x) => setNum('min_length', x, true) }), { optional: true }),
        fld('Most characters', tInput({ type: 'number', min: 1, value: v.max_length ?? '', placeholder: 'No limit', onInput: (x) => setNum('max_length', x, true) }), { optional: true })));
    }
    if (has('pattern')) {
      const hint = h('p', { class: 'fb-field__help' });
      const upd = () => { const p = S.catalog.patterns.find((x) => x.name === v.pattern); hint.textContent = p ? 'Visitors will see: ' + p.hint : 'Anything is accepted.'; };
      nodes.push(fld('Only accept...', tSelect([['', 'Anything']].concat(S.catalog.patterns.map((p) => [p.name, p.label])), v.pattern || '', (x) => { if (x) v.pattern = x; else delete v.pattern; upd(); changed({ card: f.id }); }), { optional: true }));
      nodes.push(hint);
      upd();
    }
    if (has('range')) {
      nodes.push(grid2(
        fld('Smallest number', tInput({ type: 'number', step: 'any', value: v.min ?? '', placeholder: 'No minimum', onInput: (x) => setNum('min', x, false) }), { optional: true }),
        fld('Largest number', tInput({ type: 'number', step: 'any', value: v.max ?? '', placeholder: 'No limit', onInput: (x) => setNum('max', x, false) }), { optional: true })));
    }
    if (has('count')) {
      nodes.push(grid2(
        fld('Choose at least', tInput({ type: 'number', min: 0, value: v.min ?? '', placeholder: 'No minimum', onInput: (x) => setNum('min', x, true) }), { optional: true }),
        fld('Choose at most', tInput({ type: 'number', min: 1, value: v.max ?? '', placeholder: 'No limit', onInput: (x) => setNum('max', x, true) }), { optional: true })));
    }
    if (has('date_range')) {
      const bound = (key, label) => {
        const box = h('div', { class: 'fb-field' });
        let mode = v[key] === 'today' ? 'today' : (v[key] ? 'date' : '');
        const draw = () => {
          box.replaceChildren();
          const sel = tSelect([['', 'No limit'], ['today', 'Today'], ['date', 'A fixed date']], mode, (m) => {
            mode = m;
            if (m === '') delete v[key]; else if (m === 'today') v[key] = 'today'; else if (v[key] === 'today') delete v[key];
            draw();
            changed({ card: f.id });
          }, { 'aria-label': label });
          box.append(h('p', { class: 'fb-field__label' }, label), sel);
          if (mode === 'date') {
            box.append(tInput({ type: 'date', label: label + ': fixed date', value: v[key] && v[key] !== 'today' ? v[key] : '', onInput: (x) => { if (x) v[key] = x; else delete v[key]; changed({ key: 'v:' + f.id + key, card: f.id }); } }));
          }
        };
        draw();
        return box;
      };
      nodes.push(grid2(bound('min', 'Earliest date'), bound('max', 'Latest date')));
      nodes.push(h('p', { class: 'fb-field__help' }, 'For example "Latest date: Today" stops people entering a date of birth in the future.'));
    }
    if (has('time_range')) {
      nodes.push(grid2(
        fld('Earliest time', tInput({ type: 'time', value: v.min || '', onInput: (x) => { if (x) v.min = x; else delete v.min; changed({ key: 'v:' + f.id + 'min', card: f.id }); } }), { optional: true }),
        fld('Latest time', tInput({ type: 'time', value: v.max || '', onInput: (x) => { if (x) v.max = x; else delete v.max; changed({ key: 'v:' + f.id + 'max', card: f.id }); } }), { optional: true })));
    }
    return nodes;
  }

  /* ---- file rules */
  function fileEditor(f) {
    const fr = f.file || (f.file = { types: ['pdf'], max_mb: 5, max_files: 1 });
    const maxMb = lim().file_max_mb || 5;
    const maxFiles = lim().file_max_files || 5;
    const checks = h('div', { class: 'fb-checks', role: 'group', 'aria-label': 'Allowed file types' }, S.catalog.file_types.map((t) => {
      const id = uid('fb-ft');
      const cb = h('input', { type: 'checkbox', id, checked: fr.types.includes(t.ext) });
      cb.addEventListener('change', () => {
        const set = new Set(fr.types);
        if (cb.checked) set.add(t.ext); else set.delete(t.ext);
        if (set.size === 0) { cb.checked = true; toast('Choose at least one file type.', 'warn', 2500); return; }
        fr.types = S.catalog.file_types.map((x) => x.ext).filter((x) => set.has(x));
        changed({ card: f.id });
      });
      return h('label', { class: 'fb-check', for: id }, cb, ' ', t.label);
    }));
    return h('div', null,
      h('p', { class: 'fb-field__label' }, 'People may upload'),
      checks,
      grid2(
        fld('Largest file (MB)', tInput({ type: 'number', min: 1, value: fr.max_mb, onInput: (v) => { fr.max_mb = clamp(Math.round(Number(v)) || 1, 1, maxMb); changed({ key: 'fm:' + f.id, card: f.id }); } }), { help: 'Your server allows up to ' + maxMb + ' MB.' }),
        fld('Most files', tSelect(Array.from({ length: maxFiles }, (_, i) => [String(i + 1), String(i + 1)]), String(fr.max_files || 1), (v) => { fr.max_files = Number(v); changed({ card: f.id }); }))),
      h('p', { class: 'fb-field__help' }, 'Files are stored privately and checked for safety. Only people who may read the answers can open them.'));
  }

  /* ---- conditions: "show only if [question] [is / is not / ...] [value]" with one or two rules and AND / OR */
  function opsFor(type) {
    const labels = S.catalog.ops.reduce((m, o) => { m[o.op] = o.label; return m; }, {});
    const pick = (list, overrides = {}) => list.map((op) => [op, overrides[op] || labels[op] || op]);
    switch (type) {
      case 'select': case 'radio': return pick(['equals', 'not_equals', 'filled', 'empty']);
      case 'checkboxes': return pick(['contains', 'filled', 'empty'], { contains: 'includes' });
      case 'yes_no': return pick(['equals', 'not_equals', 'filled', 'empty']);
      case 'consent': return pick(['filled', 'empty'], { filled: 'is ticked', empty: 'is not ticked' });
      case 'file': return pick(['filled', 'empty'], { filled: 'has a file', empty: 'has no file' });
      case 'rating': case 'number': return pick(['equals', 'not_equals', 'gt', 'lt', 'filled', 'empty']);
      case 'date': case 'time': return pick(['equals', 'not_equals', 'gt', 'lt', 'filled', 'empty'], { gt: 'is after', lt: 'is before' });
      default: return pick(['equals', 'not_equals', 'contains', 'filled', 'empty']);
    }
  }
  function conditionsEditor(owner, ctx) {
    const wrap = h('div', { class: 'fb-cond' });
    const sources = () => allFields().filter((x) => x.n > 0 && x.field.id !== ctx.ownerId && (!ctx.pageLevel || x.pi < ctx.ownerPi));
    const refresh = () => (ctx.pageLevel ? changed({ full: true }) : changed({ card: ctx.ownerId }));
    /** The question a NEW condition starts with: the nearest one BEFORE this question (answers are read in order); else the first available. */
    const nearestSource = (list, skipId) => {
      const me = allFields().find((x) => x.field.id === ctx.ownerId);
      const usable = list.filter((x) => x.field.id !== skipId);
      const before = me ? usable.filter((x) => x.n < me.n) : usable;
      return before[before.length - 1] || usable[0] || null;
    };
    function valueControl(rule, src, n) {
      const vlabel = 'Condition ' + n + ': value';
      const set = (v) => { rule.value = v; changed({ key: 'rv:' + ctx.ownerId + rule.field, card: ctx.pageLevel ? null : ctx.ownerId, full: ctx.pageLevel }); };
      if (!src) return tInput({ label: vlabel, value: rule.value, max: 200, placeholder: 'Value', onInput: set });
      const t = src.field.type;
      if (t === 'select' || t === 'radio' || t === 'checkboxes') {
        const opts = (src.field.options || []).map((o) => [o.value, o.label || o.value]);
        if (src.field.allow_other) opts.push([OTHER, (src.field.other_label || 'Other') + ' (typed answer)']);
        if (!opts.some(([v]) => v === rule.value)) rule.value = opts.length ? opts[0][0] : '';
        return tSelect(opts, rule.value, set, { 'aria-label': 'Choice' });
      }
      if (t === 'yes_no') { if (!['yes', 'no'].includes(rule.value)) rule.value = 'yes'; return tSelect([['yes', 'Yes'], ['no', 'No']], rule.value, set, { 'aria-label': 'Answer' }); }
      if (t === 'rating') { if (!/^[1-5]$/.test(rule.value)) rule.value = '3'; return tSelect([1, 2, 3, 4, 5].map((n) => [String(n), String(n)]), rule.value, set, { 'aria-label': 'Stars' }); }
      if (t === 'date') return tInput({ type: 'date', label: vlabel, value: rule.value, onInput: set });
      if (t === 'time') return tInput({ type: 'time', label: vlabel, value: rule.value, onInput: set });
      if (t === 'number') return tInput({ type: 'number', step: 'any', label: vlabel, value: rule.value, onInput: set });
      return tInput({ label: vlabel, value: rule.value, max: lim().rule_value || 200, placeholder: 'Type the value', onInput: set });
    }
    function ruleRow(rule, i) {
      const srcs = sources();
      const src = srcs.find((x) => x.field.id === rule.field) || null;
      const qOpts = [['', 'Choose a question...']].concat(srcs.map((x) => [x.field.id, x.n + '. ' + fieldTitle(x.field)]));
      if (rule.field && !src) qOpts.push([rule.field, '(not available - choose another question)']);
      const qSel = tSelect(qOpts, rule.field, (v) => {
        rule.field = v;
        const sf = allFields().find((x) => x.field.id === v);
        const allowed = sf ? opsFor(sf.field.type).map((o) => o[0]) : [];
        if (sf && !allowed.includes(rule.op)) rule.op = allowed[0] || 'equals';
        rule.value = '';
        draw();
        refresh();
      }, { 'aria-label': 'Condition ' + (i + 1) + ': question' });
      const ops = src ? opsFor(src.field.type) : S.catalog.ops.map((o) => [o.op, o.label]);
      if (!ops.some((o) => o[0] === rule.op)) rule.op = ops[0][0];
      const opSel = tSelect(ops, rule.op, (v) => { rule.op = v; if (v === 'filled' || v === 'empty') rule.value = ''; draw(); refresh(); }, { 'aria-label': 'Condition ' + (i + 1) + ': comparison' });
      const needsValue = !['filled', 'empty'].includes(rule.op);
      const row = h('div', { class: 'fb-rule' },
        h('div', { class: 'fb-rule__fields' }, qSel, opSel, needsValue ? valueControl(rule, src, i + 1) : null),
        h('button', { type: 'button', class: 'fb-iconbtn fb-iconbtn--sm', 'aria-label': 'Remove condition ' + (i + 1), title: 'Remove this condition', onclick: () => {
          owner.logic.rules.splice(i, 1);
          if (owner.logic.rules.length === 0) owner.logic = null;
          draw();
          refresh();
        } }, ic('x', 'icon--sm')));
      return row;
    }
    function draw() {
      wrap.replaceChildren();
      const logic = owner.logic;
      const srcs = sources();
      wrap.append(tSwitch(ctx.pageLevel ? 'Only show this page in some cases' : 'Only show this question in some cases', !!logic, (on) => {
        if (on) {
          const first = nearestSource(srcs, null);
          if (!first) { toast(ctx.pageLevel ? 'Add questions on an earlier page first.' : 'Add another question first, then use its answer here.', 'warn'); draw(); return; }
          const ops = opsFor(first.field.type);
          owner.logic = { match: 'all', rules: [{ field: first.field.id, op: ops[0][0], value: '' }] };
        } else owner.logic = null;
        draw();
        refresh();
      }, ctx.pageLevel ? 'Visitors answering earlier pages decide whether this page appears.' : 'Hidden questions are not asked, not checked and not saved.'));
      if (!logic) return;
      if (!srcs.length) wrap.append(h('p', { class: 'fb-field__error-static' }, ctx.pageLevel ? 'There are no questions on earlier pages to use.' : 'There are no other questions to use yet.'));
      const lead = h('p', { class: 'fb-cond__lead' }, ctx.pageLevel ? 'Show this page when' : 'Show this question when');
      wrap.append(lead);
      logic.rules.forEach((rule, i) => {
        if (i === 1) wrap.append(h('p', { class: 'fb-cond__join' }, logic.match === 'any' ? 'or' : 'and'));
        wrap.append(ruleRow(rule, i));
      });
      if (logic.rules.length < (lim().rules || 2)) {
        wrap.append(h('button', { type: 'button', class: 'btn btn--sm btn--outline', onclick: () => {
          const used = logic.rules[0];
          const src = nearestSource(srcs, used.field) || srcs[0];
          if (!src) return;
          logic.rules.push({ field: src.field.id, op: opsFor(src.field.type)[0][0], value: '' });
          draw();
          refresh();
        } }, ic('plus', 'icon--sm'), ' Add a second condition'));
      } else {
        wrap.append(tSelect([['all', 'ALL of these are true (and)'], ['any', 'ANY of these is true (or)']], logic.match, (v) => { logic.match = v; draw(); refresh(); }, { 'aria-label': 'How the two conditions combine' }));
      }
    }
    draw();
    return wrap;
  }

  /* ---- page inspector */
  function pageInspector(found) {
    const { page, pi } = found;
    const total = pagesOf().length;
    const wrap = h('div', { class: 'fb-insp' });
    wrap.append(h('div', { class: 'fb-insp__head' },
      h('div', { class: 'fb-insp__type' }, h('span', { class: 'fb-insp__icon' }, ic('page-break')), h('div', null, h('h2', { class: 'fb-insp__name' }, total > 1 ? 'Page ' + (pi + 1) + ' of ' + total : 'The page'), h('span', { class: 'fb-insp__pos' }, page.fields.length + ' item' + (page.fields.length === 1 ? '' : 's')))),
      total > 1 ? h('div', { class: 'fb-insp__actions' }, iconBtn('trash', 'Delete this page', () => deletePage(page.id))) : null));
    wrap.append(section('pq', 'This page', [
      fld('Page title', tInput({ value: page.title, max: lim().label || 200, autofocus: true, placeholder: 'For example: About you', onInput: (v) => { page.title = v; changed({ key: 'pt:' + page.id }); const t = $('[data-page-title="' + page.id + '"]', S.dom.canvas); if (t && t !== doc.activeElement) t.value = v; } }), { optional: true, help: 'Shown above the questions. In a step-by-step form it names the step.' }),
      fld('Short introduction', tArea({ value: page.description, rows: 3, max: lim().help || 600, onInput: (v) => { page.description = v; changed({ key: 'pd:' + page.id }); } }), { optional: true }),
    ], true));
    if (total > 1) {
      wrap.append(section('pcond', 'Show only if...', [conditionsEditor(page, { ownerId: null, ownerPi: pi, pageLevel: true })], !!page.logic, page.logic ? 'On' : null));
      wrap.append(section('pmove', 'Order', [h('div', { class: 'fb-btnrow' },
        h('button', { type: 'button', class: 'btn btn--sm btn--outline', disabled: pi === 0 || null, onclick: () => { pageAction('up', page.id); renderInspector(); } }, ic('arrow-up', 'icon--sm'), ' Move page up'),
        h('button', { type: 'button', class: 'btn btn--sm btn--outline', disabled: pi === total - 1 || null, onclick: () => { pageAction('down', page.id); renderInspector(); } }, ic('arrow-down', 'icon--sm'), ' Move page down'),
        h('button', { type: 'button', class: 'btn btn--sm btn--ghost', onclick: () => mergePage(page.id) }, 'Remove this page break'))], false));
    } else {
      wrap.append(h('p', { class: 'fb-field__help' }, 'To split the form into steps, use "Page break (new step)" from the list on the left, or the "Add a page" button below the form.'));
    }
    return wrap;
  }

  /* ================================================================== 7. form settings workspace */

  const SECTIONS = [
    { key: 'basics', label: 'Basics', icon: 'file-text', help: 'Name, address and introduction' },
    { key: 'when', label: 'Open, close and limits', icon: 'calendar', help: 'Dates, number of answers, messages' },
    { key: 'after', label: 'After sending', icon: 'send', help: 'Thank-you message, button wording' },
    { key: 'consent', label: 'Consent and spam protection', icon: 'shield', help: 'Tick box, one per e-mail, spam level' },
    { key: 'emails', label: 'E-mails', icon: 'mail', help: 'Tell your team and thank the sender' },
    { key: 'look', label: 'Look and steps', icon: 'layout', help: 'Colour, one page or steps' },
    { key: 'privacy', label: 'Privacy and search', icon: 'lock', help: 'Keeping data, search engines' },
    { key: 'labels', label: 'Answer labels', icon: 'tag', help: 'Words for the answer statuses' },
    { key: 'share', label: 'Share and embed', icon: 'link', help: 'Link, page shortcode, button' },
  ];
  const CODE_SECTION = { confirmation_field: 'emails', one_per_email: 'consent', redirect: 'after', dates: 'when', max: 'when', no_consent: 'consent', no_recipient: 'emails', no_retention: 'privacy' };
  function sectionOfPath(p) {
    if (/^(title|description|slug)$/.test(p) || /^settings\.(kind|intro)/.test(p)) return 'basics';
    if (/^(opens_at|closes_at|max_submissions)$/.test(p) || /^settings\.[a-z_]*message/.test(p)) return 'when';
    if (/^settings\.(success|submit_label|privacy_note)/.test(p)) return 'after';
    if (/^settings\.(consent|one_per_email|spam|throttle)/.test(p)) return 'consent';
    if (/^settings\.(notify|confirmation)/.test(p)) return 'emails';
    if (/^settings\.theme/.test(p)) return 'look';
    if (/^settings\.(retention|seo)/.test(p)) return 'privacy';
    if (/^settings\.status_labels/.test(p)) return 'labels';
    return 'basics';
  }
  function sectionCounts() {
    const counts = {};
    Object.keys(S.errs || {}).forEach((p) => { if (/^(pages|definition)/.test(p)) return; const k = sectionOfPath(p); counts[k] = (counts[k] || 0) + 1; });
    S.checklist.forEach((c) => { if (!c.field && !c.page && CODE_SECTION[c.code]) counts[CODE_SECTION[c.code]] = (counts[CODE_SECTION[c.code]] || 0) + (c.level === 'error' ? 1 : 0); });
    return counts;
  }

  const getP = (obj, path) => path.split('.').reduce((o, k) => (o === null || o === undefined ? undefined : o[k]), obj);
  function setP(obj, path, v) {
    const ks = path.split('.');
    const last = ks.pop();
    let o = obj;
    for (const k of ks) { if (!isObj(o[k])) o[k] = {}; o = o[k]; }
    o[last] = v;
  }
  const errPathOf = (root0, path) => (root0 === 'cfg' ? 'settings.' + path : path);

  /* bound controls: a control reads and writes S.doc[root][path] */
  function sInput(r, path, label, o = {}) {
    const obj = S.doc[r];
    const ctl = (o.area ? tArea : tInput)({
      value: getP(obj, path) ?? '', max: o.max, placeholder: o.placeholder, type: o.type, min: o.min, step: o.step, rows: o.rows, inputmode: o.inputmode,
      onInput: (v, el) => {
        let val = v;
        if (o.num) val = v === '' ? (o.numEmpty === undefined ? 0 : o.numEmpty) : Math.max(0, Math.round(Number(v)) || 0);
        if (o.clean) { val = o.clean(v); if (val !== v) { const pos = el.selectionStart; el.value = val; try { el.setSelectionRange(pos, pos); } catch (e) { /* ignore */ } } }
        setP(obj, path, val);
        changed({ key: 'set:' + r + path, settings: true });
        if (o.after) o.after(val);
      },
    });
    return fld(label, ctl, { help: o.help, optional: o.optional, err: o.err || errPathOf(r, path) });
  }
  function sSwitch(r, path, label, help, after) {
    const obj = S.doc[r];
    return h('div', { class: 'fb-field' }, tSwitch(label, !!getP(obj, path), (on) => { setP(obj, path, on); changed({ settings: true }); if (after) after(on); }, help), h('p', { class: 'fb-field__error', 'data-err': errPathOf(r, path), role: 'alert', hidden: true }));
  }
  function sSelect(r, path, label, options, o = {}) {
    const obj = S.doc[r];
    return fld(label, tSelect(options, getP(obj, path) ?? '', (v) => { setP(obj, path, v); changed({ settings: true }); if (o.after) o.after(v); }), { help: o.help, optional: o.optional, err: errPathOf(r, path) });
  }
  function sRich(r, path, label, help, placeholder) {
    const obj = S.doc[r];
    const ta = h('textarea', { rows: 8, 'data-richtext': '', 'data-placeholder': placeholder || '' });
    ta.value = getP(obj, path) || '';
    const take = () => {
      const ed = window.LuminaEditor;
      if (ed && ed.sync) ed.sync(ta);
      let v = ta.value;
      if (ed && ed.get) { const inst = ed.get(ta); if (inst && inst.isEmpty && inst.isEmpty()) v = ''; }
      if (v === (getP(obj, path) || '')) return;
      setP(obj, path, v);
      changed({ key: 'set:' + r + path, settings: true });
    };
    ta.addEventListener('lumina:editor-change', take);
    ta.addEventListener('change', take);
    ta.addEventListener('input', take);
    return fld(label, ta, { help, err: errPathOf(r, path) });
  }

  function renderSettings() {
    renderSnav();
    renderSection();
  }
  function renderSnav() {
    const nav = S.dom.snav;
    const counts = sectionCounts();
    nav.replaceChildren(h('h2', { class: 'fb-snav__title' }, 'Form settings'), h('ul', { class: 'fb-snav__list' }, SECTIONS.map((s) => h('li', null,
      h('button', { type: 'button', class: 'fb-snav__item' + (S.sec === s.key ? ' is-active' : ''), 'aria-current': S.sec === s.key ? 'true' : null, 'data-sec': s.key, onclick: () => openSection(s.key) },
        ic(s.icon, 'icon--sm'), h('span', { class: 'fb-snav__text' }, h('span', { class: 'fb-snav__label' }, s.label), h('span', { class: 'fb-snav__help' }, s.help)),
        counts[s.key] ? h('span', { class: 'fb-count', 'data-level': 'error', title: counts[s.key] + ' to fix' }, String(counts[s.key])) : null)))));
  }
  function updateSnavBadges() {
    if (!S.dom.snav || S.dom.snav.hidden) return;
    const counts = sectionCounts();
    $$('.fb-snav__item', S.dom.snav).forEach((b) => {
      const n = counts[b.dataset.sec] || 0;
      let c = $('.fb-count', b);
      if (n && !c) { c = h('span', { class: 'fb-count', 'data-level': 'error' }); b.append(c); }
      if (c) { c.textContent = String(n); c.hidden = n === 0; }
    });
  }
  function openSection(key) {
    S.sec = key;
    if (S.ws !== 'settings') { setWs('settings'); return; }
    renderSettings();
    const h2 = $('.fb-set__title', S.dom.settings);
    if (h2) { h2.setAttribute('tabindex', '-1'); h2.focus({ preventScroll: true }); }
    window.scrollTo({ top: 0 });
  }
  function renderSection() {
    const box = S.dom.settings;
    const sec = SECTIONS.find((s) => s.key === S.sec) || SECTIONS[0];
    const body = ({ basics: secBasics, when: secWhen, after: secAfter, consent: secConsent, emails: secEmails, look: secLook, privacy: secPrivacy, labels: secLabels, share: secShare })[sec.key]();
    box.replaceChildren(h('div', { class: 'fb-set' }, h('h2', { class: 'fb-set__title' }, sec.label), h('p', { class: 'fb-set__lead' }, sec.help), body));
    showErrors();
  }

  function showErrors() {
    const box = S.dom.settings;
    if (box) {
      $$('[data-err]', box).forEach((el) => {
        const p = el.dataset.err;
        const keys = Object.keys(S.errs || {}).filter((k) => k === p || k.startsWith(p + '.'));
        const msg = keys.length ? String(S.errs[keys[0]]) : '';
        el.textContent = msg;
        el.hidden = msg === '';
        const f = el.closest('.fb-field');
        const ctl = f ? $('input:not([type=radio]):not([type=checkbox]), select, textarea', f) : null;
        if (ctl) { if (msg) ctl.setAttribute('aria-invalid', 'true'); else ctl.removeAttribute('aria-invalid'); }
      });
    }
    updateSnavBadges();
    updateBar();
  }

  /* ---- 1. basics */
  function secBasics() {
    const m = S.doc.meta;
    const slugPreview = h('code', { class: 'fb-url' });
    const base = (S.form.urls.public || '').replace(/[^/]*$/, '');
    const upd = () => { slugPreview.textContent = base + m.slug; };
    const slugInput = tInput({
      value: m.slug, max: 90, spellcheck: false,
      onInput: (v, el) => {
        const clean = v.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-{2,}/g, '-');
        if (clean !== v) el.value = clean;
        m.slug = clean;
        upd();
        changed({ key: 'meta:slug', settings: true });
      },
      onChange: (v, el) => { const c = v.replace(/^-+|-+$/g, ''); if (c !== v) { el.value = c; m.slug = c; upd(); changed({}); } },
    });
    upd();
    const kind = S.doc.cfg.kind || 'general';
    const kindHelp = h('p', { class: 'fb-field__help' }, KIND_HELP[kind]);
    return h('div', { class: 'fb-stack' },
      sInput('meta', 'title', 'Form name', { max: lim().title || 190, help: 'Visitors see this as the big title.', after: () => { if (doc.activeElement !== S.dom.title) S.dom.title.value = S.doc.meta.title; } }),
      sInput('meta', 'description', 'Short description', { area: true, rows: 2, max: 600, optional: true, help: 'One or two sentences. Shown in search results and in the public list of forms.' }),
      sRich('cfg', 'intro', 'Introduction above the questions', 'Explain what the form is for, who should fill it in and what happens next.', 'Write a short welcome...'),
      h('div', { class: 'fb-field' },
        h('label', { class: 'fb-field__label', for: (slugInput.id = uid('fb-slug')) }, 'Web address'),
        h('div', { class: 'fb-prefix' }, h('span', { class: 'fb-prefix__text' }, '/forms/'), slugInput),
        h('p', { class: 'fb-field__help' }, 'Visitors open: ', slugPreview),
        S.form.status === 'published' ? h('p', { class: 'fb-field__help fb-field__help--warn' }, 'The form is live. If you change the address, links you already shared will stop working.') : null,
        h('p', { class: 'fb-field__error', 'data-err': 'slug', role: 'alert', hidden: true })),
      sSelect('cfg', 'kind', 'What kind of form is this?', Object.entries(KIND_TEXT), {
        help: null,
        after: (v) => {
          kindHelp.textContent = KIND_HELP[v];
          if ((v === 'job' || v === 'intake') && !S.doc.cfg.seo.noindex) { S.doc.cfg.seo.noindex = true; changed({ settings: true }); toast('Search engines will be asked to ignore this form (private by default).', 'info', 3500); }
        },
      }),
      kindHelp,
      sSwitch('meta', 'listed', 'Show in the public list of forms', 'Only used when the public list is switched on in Settings > Forms. The form must also be live.'));
  }

  /* ---- 2. open, close and limits */
  function secWhen() {
    const m = S.doc.meta;
    const av = S.form.availability || {};
    const stateText = { open: 'open: visitors can fill it in', draft: 'a draft: not public yet', archived: 'archived', not_open: 'live but not open yet', closed: 'closed', full: 'full: all places are taken' }[av.state] || av.state;
    const clear = (key, input) => h('button', { type: 'button', class: 'btn btn--sm btn--ghost', onclick: () => { m[key] = ''; input.value = ''; changed({ settings: true }); } }, 'Clear');
    const dt = (key, label, help) => {
      const ctl = tInput({ type: 'datetime-local', value: m[key] || '', onInput: (v) => { m[key] = v; changed({ key: 'meta:' + key, settings: true }); } });
      const f = fld(label, ctl, { help, optional: true, err: key });
      f.append(h('div', { class: 'fb-btnrow' }, clear(key, ctl)));
      return f;
    };
    return h('div', { class: 'fb-stack' },
      h('div', { class: 'fb-note' }, ic('info', 'icon--sm'), h('p', null, 'Right now this form is ', h('strong', null, stateText), '. Change that with the status button at the top. Dates below use Uganda time (EAT).')),
      grid2(dt('opens_at', 'Opens on', 'Before this the form shows "not open yet".'), dt('closes_at', 'Closes on', 'After this the form shows the "closed" message.')),
      sInput('meta', 'max_submissions', 'Stop after this many answers', { type: 'number', min: 1, step: 1, inputmode: 'numeric', optional: true, placeholder: 'No limit', help: 'Useful for places on a workshop. Spam is not counted.',
        clean: (v) => v.replace(/[^0-9]/g, '') }),
      h('h3', { class: 'fb-set__sub' }, 'Messages visitors may see'),
      h('p', { class: 'fb-field__help' }, 'Leave a box empty to use the standard wording (you can change the standard wording for every form in Pages > Forms).'),
      sInput('cfg', 'not_open_message', 'Before it opens', { area: true, rows: 2, max: 600, optional: true, placeholder: 'This form is not open yet.' }),
      sInput('cfg', 'closed_message', 'After it closes', { area: true, rows: 2, max: 600, optional: true, placeholder: 'This form is closed. Thank you for your interest.' }),
      sInput('cfg', 'full_message', 'When all places are taken', { area: true, rows: 2, max: 600, optional: true, placeholder: 'All places have been taken.' }),
      sInput('cfg', 'unavailable_message', 'When it cannot be used (for example a closed vacancy)', { area: true, rows: 2, max: 600, optional: true }));
  }

  /* ---- 3. after sending */
  function secAfter() {
    const c = S.doc.cfg;
    c.success = c.success || { mode: 'message', message_html: '', redirect_path: '' };
    const holder = h('div', { class: 'fb-stack' });
    const draw = () => {
      holder.replaceChildren();
      if (c.success.mode === 'redirect') {
        holder.append(sInput('cfg', 'success.redirect_path', 'Send people to this page', {
          max: 300, placeholder: '/thank-you', spellcheck: false,
          help: 'A page on this website, starting with "/". They are taken there as soon as the form is sent.',
        }));
      } else {
        holder.append(sRich('cfg', 'success.message_html', 'Thank-you message', 'Shown after the form is sent. Leave empty for the standard thank-you text.', 'Thank you! We will be in touch soon.'));
      }
      showErrors();
    };
    const out = h('div', { class: 'fb-stack' },
      sInput('cfg', 'submit_label', 'Text on the send button', { max: 60, optional: true, placeholder: 'Send', help: 'For example "Apply now" or "Register".' }),
      tSeg('mode', [['message', 'Show a thank-you message'], ['redirect', 'Go to another page']], c.success.mode, (v) => { c.success.mode = v; changed({ settings: true }); draw(); }, 'After the form is sent'),
      holder,
      sInput('cfg', 'privacy_note', 'Privacy note under the button', { area: true, rows: 2, max: 600, optional: true, help: 'Leave empty to use the standard note, which links to your privacy page.' }),
      sSwitch('cfg', 'allow_print', 'Let visitors print a blank copy', 'Adds a "Print a blank copy" button, handy for people without internet at home.'));
    draw();
    return out;
  }

  /* ---- 4. consent and spam */
  function secConsent() {
    const c = S.doc.cfg;
    c.consent = c.consent || { required: false, text: '' };
    const consentText = h('div', { class: 'fb-stack' });
    const draw = () => {
      consentText.replaceChildren();
      if (c.consent.required) consentText.append(sInput('cfg', 'consent.text', 'Wording of the tick box', { area: true, rows: 3, max: 500, optional: true, help: 'Leave empty for the standard wording. Visitors cannot send the form without ticking it.' }));
      showErrors();
    };
    const spamHelp = { light: 'Fewer checks. Fine for a small private link.', standard: 'Recommended. Hidden trap field, minimum time to fill in, limit per visitor.', strict: 'Slower for bots and for very fast typists. Also ignores answers with many web links.' };
    const spamNote = h('p', { class: 'fb-field__help' }, spamHelp[c.spam || 'standard']);
    const out = h('div', { class: 'fb-stack' },
      sSwitch('cfg', 'consent.required', 'Ask people to tick a consent box', 'A form-level tick box. You can also add a "Consent box" question inside the form.', (on) => { c.consent.required = on; draw(); }),
      consentText,
      sSwitch('cfg', 'one_per_email', 'Allow only one answer per e-mail address', 'The form needs an "E-mail address" question. A second try with the same address is refused politely.'),
      sSelect('cfg', 'spam', 'Spam protection', [['light', 'Light'], ['standard', 'Standard (recommended)'], ['strict', 'Strict']], { after: (v) => { spamNote.textContent = spamHelp[v]; } }),
      spamNote,
      sInput('cfg', 'throttle_form', 'Extra limit for this form', { type: 'number', min: 0, step: 1, num: true, inputmode: 'numeric', optional: true, help: 'At most this many answers every 10 minutes from everybody together. 0 means no extra limit.' }));
    draw();
    return out;
  }

  /* ---- 5. e-mails */
  function secEmails() {
    const c = S.doc.cfg;
    c.notify = c.notify || { enabled: true, emails: [], include_answers: false };
    c.confirmation = c.confirmation || { enabled: false, email_field: '', subject: '', body: '' };
    const staff = h('div', { class: 'fb-stack' });
    const confirm = h('div', { class: 'fb-stack' });
    let lastCtl = null;
    const drawStaff = () => {
      staff.replaceChildren();
      if (!c.notify.enabled) return;
      const ta = tArea({
        value: (c.notify.emails || []).join('\n'), rows: 3, max: 1200, spellcheck: false, placeholder: 'name@example.org',
        onInput: (v) => { c.notify.emails = v.split(/[\s,;]+/).map((x) => x.trim().toLowerCase()).filter(Boolean); changed({ key: 'set:emails', settings: true }); },
      });
      staff.append(
        fld('Who should be told? (one e-mail address per line)', ta, { optional: true, err: 'settings.notify.emails', help: 'Leave empty to use the addresses in Settings > Forms (or the website notification address).' }),
        sSwitch('cfg', 'notify.include_answers', 'Include the answers in the e-mail', S.form.can && S.form.can.submissions === false ? 'Switching this on, or changing the addresses while it is on, needs the permission to read form answers.' : null),
        h('p', { class: 'fb-field__help fb-field__help--warn' }, 'Safer left OFF: the e-mail then contains only a link, and the answers (personal data) stay in the admin where only authorised people can read them.'));
    };
    const drawConfirm = () => {
      confirm.replaceChildren();
      if (!c.confirmation.enabled) return;
      const emailFields = allFields().filter((x) => x.field.type === 'email');
      if (!emailFields.length) {
        confirm.append(h('div', { class: 'fb-note fb-note--warn' }, ic('alert-triangle', 'icon--sm'), h('p', null, 'Add an "E-mail address" question to the form first. The thank-you message is sent to the address people type there.')));
        return;
      }
      if (!emailFields.some((x) => x.field.id === c.confirmation.email_field)) { c.confirmation.email_field = emailFields[0].field.id; changed({ settings: true }); }
      const sub = tInput({ value: c.confirmation.subject, max: 150, placeholder: 'Thank you for contacting {site}', onInput: (v) => { c.confirmation.subject = v; changed({ key: 'set:csub', settings: true }); } });
      const body = tArea({ value: c.confirmation.body, rows: 7, max: 4000, placeholder: 'Dear {full_name},\n\nThank you. We received your form (reference {reference}).\n\nKind regards', onInput: (v) => { c.confirmation.body = v; changed({ key: 'set:cbody', settings: true }); } });
      sub.addEventListener('focus', () => { lastCtl = sub; });
      body.addEventListener('focus', () => { lastCtl = body; });
      const tokens = ['form_title', 'site', 'date', 'reference'].concat(allFields().filter((x) => x.n > 0 && x.field.key).map((x) => x.field.key));
      const chips = h('div', { class: 'fb-tokens' }, tokens.map((t) => h('button', {
        type: 'button', class: 'fb-token', title: 'Insert {' + t + '} where your cursor is', onclick: () => {
          const el = lastCtl || body;
          const pos = el.selectionStart ?? el.value.length;
          const end = el.selectionEnd ?? pos;
          el.focus();
          el.setRangeText('{' + t + '}', pos, end, 'end');
          el.dispatchEvent(new Event('input', { bubbles: true }));
        },
      }, '{' + t + '}')));
      confirm.append(
        fld('Send it to the address typed in', tSelect(emailFields.map((x) => [x.field.id, x.n + '. ' + fieldTitle(x.field)]), c.confirmation.email_field, (v) => { c.confirmation.email_field = v; changed({ settings: true }); }), { err: 'settings.confirmation.email_field' }),
        fld('Subject', sub, { optional: true, help: 'Leave empty for the standard subject.' }),
        fld('Message', body, { optional: true, help: 'Plain text. Leave empty for the standard message. Click a name below to insert what the person typed.' }),
        chips,
        h('p', { class: 'fb-field__help' }, 'Each address can receive only a few of these messages per hour, so the form cannot be used to send spam.'));
    };
    const out = h('div', { class: 'fb-stack' },
      h('h3', { class: 'fb-set__sub' }, 'Tell your team'),
      sSwitch('cfg', 'notify.enabled', 'E-mail my team when someone sends the form', null, (on) => { c.notify.enabled = on; drawStaff(); }),
      staff,
      h('h3', { class: 'fb-set__sub' }, 'Thank the sender'),
      sSwitch('cfg', 'confirmation.enabled', 'Send a confirmation e-mail to the person', null, (on) => { c.confirmation.enabled = on; drawConfirm(); }),
      confirm);
    drawStaff();
    drawConfirm();
    return out;
  }

  /* ---- 6. look */
  function secLook() {
    const c = S.doc.cfg;
    c.theme = c.theme || { accent: 'orange', layout: 'stepped', progress: true };
    const swatches = { orange: ['Orange', '#B85A14'], navy: ['Navy', '#2C3950'], teal: ['Teal', '#3D6870'], gold: ['Gold', '#8A5A06'] };
    const gid = uid('fb-acc');
    const accent = h('fieldset', { class: 'fb-seg' }, h('legend', { class: 'fb-field__label' }, 'Accent colour of the form'),
      h('div', { class: 'fb-swatches' }, (S.catalog.accents || Object.keys(swatches)).map((a) => {
        const id = gid + a;
        const r = h('input', { type: 'radio', name: gid, id, value: a, checked: c.theme.accent === a });
        r.addEventListener('change', () => { c.theme.accent = a; changed({ settings: true }); });
        return h('span', { class: 'fb-swatch' }, r, h('label', { for: id }, h('span', { class: 'fb-swatch__dot', style: 'background:' + (swatches[a] ? swatches[a][1] : '#888') }), swatches[a] ? swatches[a][0] : a));
      })));
    return h('div', { class: 'fb-stack' },
      accent,
      tSeg('layout', [['stepped', 'Step by step', 'One page at a time with Back and Next buttons'], ['single', 'Everything on one page']], c.theme.layout, (v) => { c.theme.layout = v; changed({ settings: true }); }, 'Layout'),
      h('p', { class: 'fb-field__help' }, 'Step by step only changes the form when it has more than one page. Add a page with "Page break (new step)" in the Questions tab.'),
      sSwitch('cfg', 'theme.progress', 'Show a progress bar in a step-by-step form', null));
  }

  /* ---- 7. privacy and search */
  function secPrivacy() {
    return h('div', { class: 'fb-stack' },
      h('div', { class: 'fb-note' }, ic('info', 'icon--sm'), h('p', null, 'Answers can contain personal data. Under Uganda\'s Data Protection and Privacy Act, 2019 you should keep it only as long as you need it. Ask your legal adviser to confirm the retention period and the wording of the consent text.')),
      sInput('cfg', 'retention_days', 'Delete answers automatically after (days)', {
        type: 'number', min: 0, step: 1, num: true, inputmode: 'numeric', optional: true,
        help: '0 keeps answers until you delete them. Deletion is permanent and includes uploaded files. It runs once a day.' + (S.form.can && S.form.can.delete === false ? ' Setting or shortening this period needs the permission to delete forms and answers (you can still lengthen it or switch it off).' : ''),
      }),
      h('h3', { class: 'fb-set__sub' }, 'Search engines'),
      sSwitch('cfg', 'seo.noindex', 'Ask search engines not to list this form', 'Recommended for job applications and private forms. Public surveys can be listed.'),
      sInput('cfg', 'seo.title', 'Title in search results', { max: 120, optional: true, placeholder: S.doc.meta.title }),
      sInput('cfg', 'seo.description', 'Description in search results', { area: true, rows: 2, max: 300, optional: true }));
  }

  /* ---- 8. answer labels */
  function secLabels() {
    const c = S.doc.cfg;
    c.status_labels = isObj(c.status_labels) ? c.status_labels : {};
    const defaults = c.kind === 'job' ? S.catalog.job_status_labels : S.catalog.status_labels;
    const order = ['new', 'read', 'starred', 'shortlisted', 'rejected', 'accepted', 'archived', 'spam'];
    return h('div', { class: 'fb-stack' },
      h('p', { class: 'fb-field__help' }, 'The words your team sees when they sort answers. Leave a box empty to keep the standard word. Job application forms already say "Hired" instead of "Accepted".'),
      h('div', { class: 'fb-labels' }, order.filter((k) => defaults[k] !== undefined).map((k) => {
        const inp = tInput({ value: c.status_labels[k] || '', max: 30, placeholder: defaults[k], onInput: (v) => { if (v.trim()) c.status_labels[k] = v; else delete c.status_labels[k]; changed({ key: 'set:sl' + k, settings: true }); } });
        return h('div', { class: 'fb-labels__row' }, h('label', { class: 'fb-labels__name', for: (inp.id = uid('fb-sl')) }, k.charAt(0).toUpperCase() + k.slice(1)), inp);
      })));
  }

  /* ---- 9. share and embed */
  function secShare() {
    const e = S.form.embed;
    const row = (label, value, copied, help) => {
      const inp = tInput({ value, readonly: true, spellcheck: false });
      inp.addEventListener('focus', () => inp.select());
      return h('div', { class: 'fb-field' },
        h('label', { class: 'fb-field__label', for: (inp.id = uid('fb-sh')) }, label),
        h('div', { class: 'fb-copyrow' }, inp, h('button', { type: 'button', class: 'btn btn--sm btn--outline', 'data-copy': value, 'data-copy-label': copied }, ic('copy', 'icon--sm'), ' Copy')),
        help ? h('p', { class: 'fb-field__help' }, help) : null);
    };
    const live = S.form.status === 'published';
    return h('div', { class: 'fb-stack' },
      live ? null : h('div', { class: 'fb-note fb-note--warn' }, ic('alert-triangle', 'icon--sm'), h('p', null, 'This form is not live yet, so visitors cannot open these yet. Staff signed in to the admin can preview it.')),
      row('Link to the form', e.link, 'Link copied', 'Put it in an e-mail, a WhatsApp message or a poster QR code.'),
      row('Shortcode for a page', e.shortcode, 'Shortcode copied', 'Paste this on its own line in the text of a page (Admin > Pages) to show the form right there.'),
      row('Button code (HTML)', e.button_html, 'Button code copied', 'A ready-made button that opens the form.'),
      h('div', { class: 'fb-note' }, ic('briefcase', 'icon--sm'), h('p', null, 'Collecting job applications? Open the vacancy under Careers > Jobs and pick this form as its "Application form". The Apply button then opens this form and remembers which job it was for.')),
      root.dataset.submissionsUrl ? h('p', null, h('a', { class: 'btn btn--outline btn--sm', href: root.dataset.submissionsUrl }, ic('inbox', 'icon--sm'), ' See the answers received')) : null);
  }

  /* ================================================================== 8. live preview, test submit */

  /** A preview iframe scaled to the chosen device width. `big` = used inside the large preview dialog. */
  function makePreviewView() {
    const iframe = h('iframe', { class: 'fb-pv__frame', title: 'Live preview of the form (nothing is sent from here)' });
    const stage = h('div', { class: 'fb-pv__stage' }, iframe);
    const el = h('div', { class: 'fb-pv' }, stage);
    const view = {
      el, iframe, scrollY: 0,
      fit() {
        const dev = DEVICES[S.device];
        const w = el.clientWidth;
        const ht = el.clientHeight;
        if (!w || !ht) return;
        const scale = Math.min(1, (w - 4) / dev.w);
        iframe.style.width = dev.w + 'px';
        iframe.style.height = Math.round(ht / scale) + 'px';
        iframe.style.transform = 'scale(' + scale + ')';
        stage.style.width = Math.round(dev.w * scale) + 'px';
        stage.style.height = ht + 'px';
        el.dataset.device = S.device;
      },
      set(html) {
        try { view.scrollY = iframe.contentWindow ? iframe.contentWindow.scrollY : 0; } catch (e) { view.scrollY = 0; }
        iframe.onload = () => { try { iframe.contentWindow.scrollTo(0, view.scrollY); } catch (e) { /* ignore */ } };
        iframe.srcdoc = html;
      },
    };
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(() => view.fit()).observe(el);
    return view;
  }
  const pvViews = () => [S.dom.pvView, S.bigView].filter(Boolean);

  function deviceButtons() {
    return h('div', { class: 'fb-devs', role: 'group', 'aria-label': 'Preview size' }, Object.entries(DEVICES).map(([k, v]) => h('button', {
      type: 'button', class: 'fb-devbtn', 'data-device': k, 'aria-pressed': String(S.device === k), title: v.label + ' (' + v.w + ' px wide)', onclick: () => setDevice(k),
    }, ic(v.icon, 'icon--sm'), h('span', { class: 'fb-devbtn__text' }, v.label))));
  }
  function setDevice(k) {
    S.device = k;
    $$('.fb-devbtn').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.device === k)));
    pvViews().forEach((v) => v.fit());
  }

  function buildPreviewPane() {
    const d = S.dom;
    d.pvView = makePreviewView();
    d.pvMsg = h('p', { class: 'fb-pv__msg', hidden: true });
    d.pvBusy = h('span', { class: 'fb-pv__busy', hidden: true }, 'Updating...');
    d.previewPane.append(
      h('div', { class: 'fb-pv__head' }, deviceButtons(), d.pvBusy, h('span', { class: 'fb-pv__tools' }, iconBtn('refresh', 'Refresh the preview', () => schedulePreview(true)), iconBtn('expand', 'Open a large preview', () => openLargePreview()))),
      d.pvMsg, d.pvView.el, buildTestBox(() => d.pvView));
  }

  const doPreview = debounce(() => refreshPreview(), 700);
  function previewVisible() {
    if (S.bigView) return true;
    return S.side === 'preview' && (S.layout === 'wide' || S.drawer);
  }
  function schedulePreview(now) {
    S.previewStale = true;
    if (!previewVisible()) return;
    if (now) { doPreview.cancel(); refreshPreview(); } else doPreview();
  }
  async function refreshPreview() {
    const seq = ++S.pvSeq;
    if (S.dom.pvBusy) S.dom.pvBusy.hidden = false;
    const res = await api('POST', '/preview', { definition: S.doc.def, settings: S.doc.cfg, title: S.doc.meta.title, description: S.doc.meta.description, id: FORM_ID });
    if (seq !== S.pvSeq) return;
    if (S.dom.pvBusy) S.dom.pvBusy.hidden = true;
    if (res.ok && res.data && res.data.document) {
      S.pvDoc = res.data.document;
      S.previewStale = false;
      if (S.dom.pvMsg) S.dom.pvMsg.hidden = true;
      pvViews().forEach((v) => { v.set(S.pvDoc); v.fit(); });
    } else if (S.dom.pvMsg) {
      S.dom.pvMsg.hidden = false;
      S.dom.pvMsg.textContent = 'The preview cannot be shown right now: ' + errMsg(res, 'please check the form.');
    }
  }

  function openLargePreview() {
    const view = makePreviewView();
    S.bigView = view;
    const box = buildTestBox(() => view);
    openDialog({
      title: 'Preview: ' + (S.doc.meta.title || 'your form'),
      className: 'fb-dialog--preview', wide: true,
      body: h('div', { class: 'fb-bigpv' }, h('div', { class: 'fb-pv__head' }, deviceButtons()), view.el, box),
      actions: [{ label: 'Close', value: null, primary: true }],
      onOpen: () => { view.fit(); if (S.pvDoc && !S.previewStale) { view.set(S.pvDoc); } else refreshPreview(); },
    }).then(() => { S.bigView = null; });
  }

  /* ---- test submit: reads the answers typed into the preview and checks them on the server. Nothing is stored, nothing is e-mailed. */
  function buildTestBox(getView) {
    const out = h('div', { class: 'fb-test__out', 'aria-live': 'polite' });
    const btn = h('button', { type: 'button', class: 'btn btn--sm btn--primary', onclick: () => runTest(getView(), out, btn) }, ic('check-circle', 'icon--sm'), ' Run a test submit');
    return h('div', { class: 'fb-test' },
      h('div', { class: 'fb-test__head' }, h('strong', null, 'Try it like a visitor'), h('p', null, 'Fill in the form above, then press the button. Nothing is saved and no e-mail is sent.')),
      btn, out);
  }
  async function runTest(view, out, btn) {
    let form = null;
    try { form = view.iframe.contentDocument && view.iframe.contentDocument.querySelector('form[data-lf]'); } catch (e) { form = null; }
    if (!form) { out.replaceChildren(h('p', { class: 'fb-issue fb-issue--warning' }, ic('alert-triangle', 'icon--sm'), h('span', null, 'The preview has not loaded yet. Wait a moment and try again.'))); return; }
    btn.disabled = true;
    out.replaceChildren(h('p', { class: 'fb-muted' }, 'Checking...'));
    const fd = new FormData(form);
    ['_token', 'website', '_hp_ts', 'job'].forEach((k) => fd.delete(k));
    fd.append('definition', JSON.stringify(S.doc.def));
    fd.append('settings', JSON.stringify(S.doc.cfg));
    const res = await api('POST', '/' + FORM_ID + '/test-submit', fd);
    btn.disabled = false;
    if (!res.data || (!res.ok && res.status !== 200)) {
      out.replaceChildren(h('p', { class: 'fb-issue fb-issue--error' }, ic('alert-circle', 'icon--sm'), h('span', null, 'The test could not run: ' + errMsg(res, 'please try again.'))));
      return;
    }
    const d = res.data;
    if (d.ok) {
      const hidden = Object.values(d.visible || {}).filter((v) => v === false).length;
      out.replaceChildren(
        h('div', { class: 'fb-test__ok' }, ic('check-circle'), h('div', null, h('strong', null, 'Test passed'), h('p', null, 'These ' + (d.answers || []).length + ' answers would have been recorded' + (hidden ? ' (' + hidden + ' hidden by conditions were skipped)' : '') + '. Nothing was stored.'))),
        h('dl', { class: 'fb-test__answers' }, (d.answers || []).map((a) => [h('dt', null, a.label || a.key || a.id), h('dd', null, String(a.text === undefined || a.text === '' ? '-' : a.text))])));
      return;
    }
    const items = [];
    Object.entries(d.errors || {}).forEach(([id, msg]) => {
      const l = locate(id);
      items.push(h('li', null, h('span', null, h('strong', null, l ? fieldTitle(l.field) : (id === '_consent' ? 'Consent' : 'Form')), ': ', String(msg)),
        l ? h('button', { type: 'button', class: 'btn btn--sm btn--ghost', onclick: () => { setWs('questions'); select('field', id); scrollToCard(id); } }, 'Go to it') : null));
    });
    out.replaceChildren(
      h('div', { class: 'fb-test__bad' }, ic('alert-circle'), h('div', null, h('strong', null, items.length + ' problem' + (items.length === 1 ? '' : 's') + ' found'), h('p', null, 'A visitor would see these messages. Nothing was stored.'))),
      h('ul', { class: 'fb-test__errors' }, items));
  }

  /* ================================================================== publish flow, status changes, dialogs */

  function jumpTo(c) {
    if (c.field && locate(c.field)) { setWs('questions'); select('field', c.field, { focusLabel: true }); scrollToCard(c.field); return; }
    if (c.page && findPage(c.page)) {
      setWs('questions');
      select('page', c.page, { focusLabel: true });
      const el = $('.fb-page[data-page-id="' + c.page + '"]', S.dom.canvas);
      if (el) el.scrollIntoView({ block: 'start', behavior: 'smooth' });
      return;
    }
    if (CODE_SECTION[c.code]) { openSection(CODE_SECTION[c.code]); return; }
    if (c.code === 'no_fields') { setWs('questions'); const p = $('.fb-pal', S.dom.palette); S.paletteOpen = true; applyView(); if (p) p.focus(); }
  }

  /** The checklist dialog. opts.publish = it was opened by "Publish": warnings can be accepted. Resolves 'publish' when the person wants to go on. */
  async function openChecklist(opts = {}) {
    const errs = S.checklist.filter((c) => c.level === 'error');
    const warns = S.checklist.filter((c) => c.level !== 'error');
    let dlgRef = null;
    const item = (c) => h('li', { class: 'fb-ck fb-ck--' + (c.level === 'error' ? 'error' : 'warn') },
      ic(c.level === 'error' ? 'alert-circle' : 'alert-triangle'),
      h('span', { class: 'fb-ck__text' }, c.message),
      h('button', { type: 'button', class: 'btn btn--sm btn--outline', onclick: () => { if (dlgRef) dlgRef.fbClose('jump'); jumpTo(c); } }, 'Go to it'));
    const body = h('div', { class: 'fb-checklist' });
    if (!errs.length && !warns.length) {
      body.append(h('div', { class: 'fb-test__ok' }, ic('check-circle'), h('div', null, h('strong', null, 'Everything looks good'), h('p', null, 'The form is complete and ready to publish.'))));
    }
    if (errs.length) body.append(h('h3', { class: 'fb-set__sub' }, 'Fix these before publishing (' + errs.length + ')'), h('ul', { class: 'fb-ck__list' }, errs.map(item)));
    if (warns.length) body.append(h('h3', { class: 'fb-set__sub' }, 'Suggestions (' + warns.length + ')'), h('p', { class: 'fb-field__help' }, 'These do not stop you from publishing, but they are worth a look.'), h('ul', { class: 'fb-ck__list' }, warns.map(item)));
    const actions = [];
    if (opts.publish && !errs.length) {
      actions.push({ label: 'Review the suggestions', value: null }, { label: 'Publish anyway', value: 'publish', class: 'btn--cta', primary: true, icon: 'send' });
    } else {
      actions.push({ label: 'Close', value: null, primary: true });
    }
    const v = await openDialog({ title: opts.blocked || errs.length ? 'This form needs a little more work' : 'Ready to publish?', body, actions, wide: true, onOpen: (dlg) => { dlgRef = dlg; } });
    return v;
  }

  async function publishFlow() {
    const ok = await save({ autosave: false });
    if (!ok && (isDirty() || S.save.state === 'conflict')) return;
    // fresh checklist from the server before deciding
    const m = S.doc.meta;
    const res = await api('POST', '/validate', { id: FORM_ID, definition: S.doc.def, settings: S.doc.cfg, slug: m.slug, opens_at: m.opens_at || '', closes_at: m.closes_at || '', max_submissions: m.max_submissions === '' ? '' : Number(m.max_submissions) });
    if (res.ok && res.data) setChecklist(res.data.checklist || []);
    const errs = S.checklist.filter((c) => c.level === 'error');
    if (errs.length) { await openChecklist({ blocked: true }); return; }
    const warns = S.checklist.filter((c) => c.level !== 'error');
    if (warns.length) {
      const v = await openChecklist({ publish: true });
      if (v !== 'publish') return;
    } else {
      const go = await confirmBox({
        title: 'Publish this form?',
        text: ['It will be public at ' + S.form.urls.public + ' and people can start sending answers' + (S.doc.meta.opens_at ? ' (after the opening date you set).' : '.'), 'You can still edit it afterwards; changes apply straight away.'],
        confirm: 'Publish now',
      });
      if (!go) return;
    }
    await doStatus('publish', true);
  }

  const STATUS_CONFIRM = {
    unpublish: { title: 'Take this form offline?', text: ['It goes back to draft. Anyone opening the link will see "not found". Answers already received are kept.'], confirm: 'Unpublish' },
    close: { title: 'Close this form?', text: ['Visitors can still open the page but are told that it is closed. You can reopen it any time.'], confirm: 'Close the form' },
    archive: { title: 'Archive this form?', text: ['It stops being public and disappears from the main list. Answers are kept, and you can restore the form later.'], confirm: 'Archive' },
  };
  async function doStatus(action, confirmed) {
    const c = STATUS_CONFIRM[action];
    if (c && !confirmed) {
      const text = c.text.slice();
      if (S.form.jobs && S.form.jobs.length && (action === 'archive' || action === 'unpublish' || action === 'close')) text.push('These vacancies use this form: ' + S.form.jobs.join(', ') + '. They show the built-in application form while this form is not open.');
      if (!(await confirmBox({ title: c.title, text, confirm: c.confirm, danger: action === 'archive' }))) return;
    }
    if (isDirty() && S.save.state !== 'conflict') { const ok = await save({ autosave: false }); if (!ok && isDirty()) return; }
    const res = await api('POST', '/' + FORM_ID + '/status', { action, version: S.form.version });
    if (res.ok && res.data && res.data.data) {
      applyServerFacts(res.data.data);
      S.base = S.base || baselineOf(S.doc);
      if (Array.isArray(res.data.checklist) && res.data.checklist.length) setChecklist(res.data.checklist);
      updateBar();
      renderCanvas();
      if (S.ws === 'settings') renderSettings();
      if (action === 'publish' || action === 'reopen') showPublished();
      else toast({ unpublish: 'The form is back to draft.', close: 'The form is closed.', archive: 'The form was archived.', restore: 'The form was restored as a draft.' }[action] || 'Done.', 'success');
    } else if (res.status === 409) {
      S.save.state = 'conflict';
      S.conflictData = res.data;
      updateSaveUI();
      openConflict(res.data);
    } else if (res.status === 422 && res.data) {
      if (Array.isArray(res.data.checklist)) setChecklist(res.data.checklist);
      await openChecklist({ blocked: true });
    } else {
      toast('Could not change the status: ' + errMsg(res, 'please try again.'), 'error', 7000);
    }
  }
  function showPublished() {
    const e = S.form.embed;
    openDialog({
      title: 'Your form is live',
      body: h('div', { class: 'fb-dialog__text' },
        h('div', { class: 'fb-test__ok' }, ic('check-circle'), h('div', null, h('strong', null, S.doc.meta.title), h('p', null, 'People can open it with this link:'))),
        h('div', { class: 'fb-copyrow' }, h('input', { class: 'input', readonly: true, value: e.link, 'aria-label': 'Link to the form', onfocus: (ev) => ev.target.select() }), h('button', { type: 'button', class: 'btn btn--sm btn--outline', 'data-copy': e.link, 'data-copy-label': 'Link copied' }, ic('copy', 'icon--sm'), ' Copy')),
        h('p', { class: 'fb-field__help' }, 'You can also paste ', h('code', null, e.shortcode), ' into a page, or pick the form as the "Application form" of a vacancy.')),
      actions: [{ label: 'Done', value: null }, { label: 'Open the form', value: 'open', class: 'btn--primary', primary: true, icon: 'external-link' }],
    }).then((v) => { if (v === 'open') window.open(S.form.urls.public, '_blank', 'noopener'); });
  }

  /** Conflict (someone saved a newer version): reload theirs, or keep mine and overwrite. body = the 409 response. */
  async function openConflict(body) {
    if (S.conflictOpen) return;
    S.conflictOpen = true;
    const cur = (body && body.current) || {};
    const when = cur.updated_at ? ' at ' + String(cur.updated_at).slice(11, 16) : '';
    const choice = await openDialog({
      title: 'Somebody else changed this form',
      body: h('div', { class: 'fb-dialog__text' },
        h('p', null, 'A newer version of this form was saved' + when + ' (by you in another window, or by a colleague). If you carry on, one version replaces the other.'),
        h('ul', { class: 'fb-bullets' },
          h('li', null, h('strong', null, 'Load the newest version'), ': you see what was saved last. Your changes made since your last save are lost.'),
          h('li', null, h('strong', null, 'Keep my changes'), ': what you see now replaces the saved version.'))),
      actions: [{ label: 'Decide later', value: null }, { label: 'Keep my changes and overwrite', value: 'mine', class: 'btn--danger' }, { label: 'Load the newest version', value: 'theirs', class: 'btn--primary', primary: true }],
    });
    S.conflictOpen = false;
    if (choice === 'theirs') {
      let f = body && body.data;
      if (!f) { const r = await api('GET', '/' + FORM_ID); f = r.ok ? r.data.data : null; }
      if (!f) { toast('Could not load the newest version. Reload the page.', 'error'); return; }
      setFromServer(f);
      S.save.state = 'saved';
      S.save.at = new Date();
      renderAll();
      schedulePreview();
      scheduleValidate();
      toast('The newest version was loaded.', 'success');
    } else if (choice === 'mine') {
      S.form.version = Number(cur.version) || S.form.version;
      S.save.state = 'dirty';
      await save({ autosave: false, force: true });
    }
  }

  async function duplicateForm() {
    if (isDirty()) { const ok = await save({ autosave: false }); if (!ok && isDirty()) return; }
    const res = await api('POST', '/' + FORM_ID + '/duplicate');
    if (res.ok && res.data && res.data.data) { S.leaving = true; window.location.href = res.data.data.urls.edit; } else toast('Could not duplicate: ' + errMsg(res, 'please try again.'), 'error');
  }
  async function deleteForm() {
    const n = Number(S.form.submissions_count) || 0;
    const jobs = S.form.jobs || [];
    const text = ['"' + S.doc.meta.title + '" will be deleted for good.'];
    if (n > 0) text.push('Its ' + n + ' answer' + (n === 1 ? '' : 's') + ' and every uploaded file will be deleted too. This cannot be undone.');
    else text.push('This cannot be undone.');
    if (jobs.length) text.push('These vacancies will go back to the built-in application form: ' + jobs.join(', ') + '.');
    if (!(await confirmBox({ title: 'Delete this form?', text, confirm: 'Delete the form', danger: true }))) return;
    const res = await api('DELETE', '/' + FORM_ID);
    if (res.ok) { S.leaving = true; window.location.href = LIST_URL; } else toast('Could not delete: ' + errMsg(res, 'you may not be allowed to delete forms.'), 'error', 7000);
  }

  function showShortcuts() {
    const rows = [
      ['Ctrl + S', 'Save now'],
      ['Ctrl + Z / Ctrl + Y', 'Undo / redo (when you are not typing in a box)'],
      ['Enter on a question', 'Open its settings'],
      ['Alt + Up / Down arrow', 'Move the selected question up or down'],
      ['Up / Down arrow', 'Move between questions'],
      ['Delete', 'Delete the selected question (asks first)'],
      ['Ctrl + D', 'Duplicate the selected question'],
      ['Enter in a choice', 'Add the next choice'],
      ['Esc', 'Close a panel or dialog'],
    ];
    openDialog({
      title: 'Keyboard shortcuts',
      body: h('table', { class: 'fb-keys' }, h('tbody', null, rows.map(([k, t]) => h('tr', null, h('th', { scope: 'row' }, h('kbd', null, k)), h('td', null, t))))),
      actions: [{ label: 'Close', value: null, primary: true }],
    });
  }

  /* ================================================================== init */

  function setFromServer(f) {
    S.form = {
      id: f.id, status: f.status, version: f.version, slug: f.slug, urls: f.urls, embed: f.embed, availability: f.availability, submissions_count: f.submissions_count,
      unread: f.unread, published_at: f.published_at || null, updated_at: f.updated_at, jobs: f.jobs || [], can: f.can || {}, template_key: f.template_key || null,
    };
    const def = f.definition && Array.isArray(f.definition.pages) ? f.definition : { version: 1, pages: [{ id: newIdRaw('p'), title: '', description: '', logic: null, fields: [] }] };
    def.pages.forEach((p) => { p.fields = p.fields || []; });
    S.doc = {
      meta: { title: f.title, description: f.description || '', slug: f.slug, listed: !!f.listed, opens_at: f.opens_at || '', closes_at: f.closes_at || '', max_submissions: f.max_submissions === null || f.max_submissions === undefined ? '' : f.max_submissions },
      def, cfg: f.settings,
    };
    S.base = baselineOf(S.doc);
    S.lastSnap = snap();
    S.lastKey = null;
    S.undo = [];
    S.redo = [];
    S.sel = null;
    S.ins = null;
    S.errs = {};
    S.save = { state: 'idle', at: null, msg: '', failSnap: null, inflight: false, again: false };
  }
  const newIdRaw = (prefix) => prefix + randomChars(prefix === 'p' ? 6 : 8);

  function renderAll() {
    renderCanvas();
    renderInspector();
    if (S.ws === 'settings') renderSettings();
    updateBar();
    applyView();
  }

  async function init() {
    const note = $('[data-builder-status]', root);
    const say0 = (t) => { if (note) note.textContent = t; };
    const [cat, form] = await Promise.all([api('GET', '/catalog'), api('GET', '/' + FORM_ID)]);
    if (!cat.ok || !form.ok || !form.data || !form.data.data) {
      const box = $('[data-builder-loading]', root);
      if (box) {
        box.replaceChildren(h('p', { class: 'fb-loading__title' }, 'The form builder could not open'), h('p', { class: 'fb-loading__text' }, errMsg(form.ok ? cat : form, 'Check your connection and try again.')),
          h('p', null, h('button', { type: 'button', class: 'btn btn--primary', onclick: () => window.location.reload() }, 'Try again'), ' ', h('a', { class: 'btn btn--outline', href: LIST_URL }, 'Back to forms')));
      } else say0('The form could not be loaded.');
      return;
    }
    S.catalog = cat.data.data;
    setFromServer(form.data.data);
    const wide = $('.admin-content');
    if (wide) wide.classList.add('admin-content--wide');
    buildShell();
    setChecklist(form.data.checklist || []);
    S.side = 'preview';
    S.sec = 'basics';
    if (window.location.hash === '#settings') { S.ws = 'settings'; renderSettings(); }
    renderAll();
    schedulePreview(true);
    startTimers();
    runValidate();
    updateHistoryButtons();
    window.LuminaFormBuilder = { api, state: S };
  }

  init();
})();
