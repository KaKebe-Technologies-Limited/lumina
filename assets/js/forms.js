/*
 * Lumina public forms - renderer behaviour (ES2020, no dependencies, CSP-safe: no inline code).
 * Contract: docs/FORMS.md sections 5 (conditional logic) and 8 (public forms). Owner: PUBLIC agent.
 *
 * PART 1 (final, written by the architect): the conditional-logic engine. It is a line-by-line mirror of App\Services\Forms\ConditionEvaluator
 * (PHP); bin/selftest-forms.php holds the truth table and tests/browser checks must give the same answers. Do not change the semantics.
 * PART 2 (reference): wiring that shows / hides questions and pages as answers change. The PUBLIC agent adds: multi-step navigation with a progress
 * bar, inline validation on blur + error summary focus on submit, the sessionStorage draft, the print-a-blank-copy button, AJAX submit.
 */
(() => {
  'use strict';

  const OTHER = '__other';

  /* ------------------------------------------------------------------ PART 1: logic engine (mirror of ConditionEvaluator.php) */

  const fold = (s) => String(s ?? '').trim().toLowerCase();
  const isNumber = (s) => /^-?[0-9]+(\.[0-9]+)?$/.test(s);
  const isDate = (s) => /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/.test(s);
  const isTime = (s) => /^([01][0-9]|2[0-3]):[0-5][0-9]$/.test(s);

  /** value: string | string[] | null */
  function isFilled(value) {
    if (value === null || value === undefined) return false;
    if (Array.isArray(value)) return value.some((x) => String(x).trim() !== '');
    return String(value).trim() !== '';
  }

  function equals(value, expected) {
    if (Array.isArray(value)) {
      const items = value.map(String).filter((x) => x.trim() !== '');
      if (items.length === 0) return expected.trim() === '';
      return items.length === 1 && fold(items[0]) === fold(expected);
    }
    const v = value === null || value === undefined ? '' : String(value).trim();
    const e = String(expected).trim();
    if (isNumber(v) && isNumber(e)) return Number(v) === Number(e);
    return fold(v) === fold(e);
  }

  function ruleMatches(rule, value) {
    const op = String(rule.op || 'equals');
    const expected = String(rule.value ?? '');
    switch (op) {
      case 'filled': return isFilled(value);
      case 'empty': return !isFilled(value);
      case 'equals': return equals(value, expected);
      case 'not_equals': return !equals(value, expected);
      case 'contains':
        if (Array.isArray(value)) return value.some((x) => fold(x) === fold(expected));
        return value !== null && value !== undefined && expected.trim() !== '' && String(value).toLowerCase().includes(expected.trim().toLowerCase());
      case 'gt':
      case 'lt': {
        if (value === null || value === undefined || Array.isArray(value)) return false;
        const a = String(value).trim();
        const b = expected.trim();
        if (isNumber(a) && isNumber(b)) return op === 'gt' ? Number(a) > Number(b) : Number(a) < Number(b);
        if ((isDate(a) && isDate(b)) || (isTime(a) && isTime(b))) return op === 'gt' ? a > b : a < b;
        return false;
      }
      default: return false;
    }
  }

  function passes(logic, valueOf) {
    if (!logic || !Array.isArray(logic.rules) || logic.rules.length === 0) return true;
    const any = logic.match === 'any';
    for (const rule of logic.rules) {
      const hit = ruleMatches(rule, valueOf(String(rule.field || '')));
      if (any && hit) return true;
      if (!any && !hit) return false;
    }
    return !any;
  }

  const INPUT_TYPES = new Set(['text', 'textarea', 'email', 'phone', 'number', 'date', 'time', 'select', 'radio', 'checkboxes', 'yes_no', 'rating', 'file', 'consent']);

  /**
   * pages: the definition pages (config.pages); raw: { fieldId: string | string[] } as entered.
   * Returns { fields: {id: bool}, pages: {id: bool}, values: {id: effective value | null} } - same algorithm and the same cycle behaviour as PHP.
   */
  function resolve(pages, raw) {
    // prototype-less maps: a question id such as "constructor" must not be found on Object.prototype
    const fields = Object.create(null);
    const pageOf = Object.create(null);
    const pageMap = Object.create(null);
    const rawOf = (id) => (raw && Object.prototype.hasOwnProperty.call(raw, id) ? raw[id] : null);
    for (const page of pages || []) {
      pageMap[page.id] = page;
      for (const f of page.fields || []) { fields[f.id] = f; pageOf[f.id] = page.id; }
    }
    const fieldVisible = Object.create(null);
    const pageVisible = Object.create(null);
    const busy = Object.create(null);
    const valueOf = (id) => {
      if (!fields[id] || !INPUT_TYPES.has(fields[id].type)) return null;
      return isFieldVisible(id) ? (rawOf(id) ?? null) : null;
    };
    const isPageVisible = (pid) => {
      if (pid in pageVisible) return pageVisible[pid];
      const key = 'p:' + pid;
      if (busy[key]) return false;
      busy[key] = true;
      const ok = passes(pageMap[pid].logic, valueOf);
      delete busy[key];
      return (pageVisible[pid] = ok);
    };
    const isFieldVisible = (id) => {
      if (id in fieldVisible) return fieldVisible[id];
      const key = 'f:' + id;
      if (busy[key]) return false;
      busy[key] = true;
      const ok = isPageVisible(pageOf[id]) && passes(fields[id].logic, valueOf);
      delete busy[key];
      return (fieldVisible[id] = ok);
    };
    const outFields = Object.create(null);
    const outValues = Object.create(null);
    for (const id of Object.keys(fields)) {
      outFields[id] = isFieldVisible(id);
      outValues[id] = outFields[id] && INPUT_TYPES.has(fields[id].type) ? (rawOf(id) ?? null) : null;
    }
    const outPages = {};
    for (const pid of Object.keys(pageMap)) outPages[pid] = isPageVisible(pid);
    return { fields: outFields, pages: outPages, values: outValues };
  }

  window.LuminaFormsLogic = { ruleMatches, passes, resolve, isFilled, OTHER };

  /* ------------------------------------------------------------------ PART 2: the form (PUBLIC agent) */
  /*
   * Progressive enhancement of <form data-lf> (markup: app/views/web/forms/_form.php + _field.php). Without this script the form still works: every page
   * is shown and the server validates. With it: conditional questions, multi-step with progress, inline validation (on blur and on Next / Submit),
   * an error summary that takes focus, "Other" boxes, a character counter, file hints and a removable file list, the sessionStorage draft, AJAX submit
   * with upload progress, and "print a blank copy". The server is authoritative: everything here is for comfort and never trusted.
   */

  const SKIP_NAMES = new Set(['_token', '_hp_ts', 'job']);
  const reduceMotion = () => window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fill = (tpl, vars) => String(tpl).replace(/\{(\w+)\}/g, (m, k) => (vars && Object.prototype.hasOwnProperty.call(vars, k) ? String(vars[k]) : m));
  const humanSize = (b) => (b >= 1048576 ? (b / 1048576).toFixed(b >= 10485760 ? 0 : 1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB');
  const prettyDate = (iso) => {
    const d = new Date(iso + 'T00:00:00');
    return Number.isNaN(d.getTime()) ? iso : d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
  };

  /** The value of one question as entered, in the shape the logic compares (docs/FORMS.md section 5.2). */
  function readValue(wrap) {
    const type = wrap.dataset.lfType;
    const id = wrap.dataset.lfField;
    const name = 'f_' + id;
    switch (type) {
      case 'checkboxes':
        return Array.from(wrap.querySelectorAll('input[type="checkbox"]:checked')).map((i) => i.value);
      case 'radio':
      case 'yes_no':
      case 'rating': {
        const c = wrap.querySelector('input[type="radio"]:checked');
        return c ? c.value : '';
      }
      case 'consent': {
        const c = wrap.querySelector('input[type="checkbox"]');
        return c && c.checked ? 'yes' : '';
      }
      case 'file': {
        const f = wrap.querySelector('input[type="file"]');
        return f && f.files ? Array.from(f.files).map((x) => x.name) : [];
      }
      default: {
        const el = wrap.querySelector('[name="' + name + '"]');
        return el ? String(el.value).trim() : '';
      }
    }
  }

  /* ---- validation helpers (mirror of FormValidator.php / FormDefinition.php; the server decides) */

  /** Ugandan-friendly phone check, a port of FormDefinition::normalisePhone(). */
  function phoneOk(raw) {
    const s = String(raw).trim();
    if (s === '' || s.length > 40 || !/^[+0-9\s().-]+$/.test(s)) return false;
    let plus = s.startsWith('+');
    let digits = s.replace(/\D+/g, '');
    if (digits === '') return false;
    if (!plus && digits.startsWith('00')) { plus = true; digits = digits.slice(2); }
    if (plus) return /^[1-9][0-9]{6,14}$/.test(digits);
    if (digits.startsWith('256') && digits.length === 12) return true;
    if (digits.startsWith('0') && digits.length === 10) return true;
    return false;
  }

  function patternOk(patterns, name, value) {
    const p = patterns && patterns[name];
    if (!p) return true;
    let v = String(value);
    if (p.prepare === 'strip_sep') v = v.replace(/[\s().-]+/g, '');
    else if (p.prepare === 'upper') v = v.replace(/\s+/g, '').toUpperCase();
    else v = v.trim();
    let hit;
    try { hit = new RegExp(p.regex, 'u' + (p.flags || '')).test(v); } catch (e) { return true; }
    return p.negate ? !hit : hit;
  }

  const fileExt = (name) => { const m = /\.([A-Za-z0-9]+)$/.exec(name); const e = m ? m[1].toLowerCase() : ''; return e === 'jpeg' ? 'jpg' : e; };

  function init(form) {
    let config;
    let text;
    try { config = JSON.parse(form.dataset.lfConfig || '{}'); } catch (e) { config = {}; }
    try { text = JSON.parse(form.dataset.lfText || '{}'); } catch (e) { text = {}; }
    const T = (group, key, vars) => fill((text[group] && text[group][key]) || (group === 'errors' ? (text.errors && text.errors.generic) : '') || '', vars);

    const mode = form.dataset.lfMode || 'live';
    const pages = config.pages || [];
    const fieldDefs = {};
    for (const pg of pages) for (const f of pg.fields || []) fieldDefs[f.id] = f;
    const wraps = Array.from(form.querySelectorAll('[data-lf-field]'));
    const wrapOf = {};
    for (const w of wraps) wrapOf[w.dataset.lfField] = w;
    const pageEls = Array.from(form.querySelectorAll('[data-lf-page]'));
    const summary = form.querySelector('[data-lf-summary]');
    const summaryList = form.querySelector('[data-lf-summary-list]');
    const live = form.querySelector('[data-lf-live]');
    const btnPrev = form.querySelector('[data-lf-prev]');
    const btnNext = form.querySelector('[data-lf-next]');
    const btnSubmit = form.querySelector('[data-lf-submit]');
    const btnPrint = form.querySelector('[data-lf-print]');
    const progress = form.querySelector('[data-lf-progress-bar]');
    const stepLabel = form.querySelector('[data-lf-step-label]');
    const pctLabel = form.querySelector('[data-lf-pct]');
    const bar = form.querySelector('[role="progressbar"]');
    const barFill = form.querySelector('.lf__bar-fill');
    const draftNote = form.querySelector('[data-lf-draft]');
    const stepped = form.dataset.lfLayout === 'stepped' && pageEls.length > 1;
    const formConsent = form.querySelector('[data-lf-formconsent]');
    const announce = (msg) => { if (live) { live.textContent = ''; window.setTimeout(() => { live.textContent = msg; }, 30); } };

    /* a blank copy for the printer: everything shown, no logic, nothing to send */
    if (mode === 'print') {
      form.classList.add('is-enhanced');
      return;
    }

    let res = { fields: {}, pages: {}, values: {} };
    let current = 0;               // index in visiblePages()
    let firstRun = true;
    let busy = false;

    /* ------------------------------------------------ conditional logic */
    const apply = () => {
      const raw = {};
      for (const w of wraps) if (INPUT_TYPES.has(w.dataset.lfType)) raw[w.dataset.lfField] = readValue(w);
      res = resolve(pages, raw);
      for (const w of wraps) {
        const show = res.fields[w.dataset.lfField] !== false;
        const was = w.hidden;
        w.hidden = !show;
        // hidden questions are not sent and never block the form (the server ignores them as well)
        w.querySelectorAll('input, select, textarea').forEach((el) => { el.disabled = !show; });
        if (!show && !was) clearError(w);
        if (show && was && !firstRun) {
          w.classList.add('is-revealed');
          w.addEventListener('animationend', () => w.classList.remove('is-revealed'), { once: true });
        }
      }
      pageEls.forEach((p) => { p.hidden = res.pages[p.dataset.lfPage] === false; });
      syncChoices();
      if (stepped) refreshSteps();
      form.dispatchEvent(new CustomEvent('lf:visibility', { detail: res }));
    };

    /* ------------------------------------------------ "Other" boxes and checked tiles */
    const syncChoices = () => {
      for (const w of wraps) {
        const type = w.dataset.lfType;
        if (type === 'select') {
          const sel = w.querySelector('select');
          const other = w.querySelector('[data-lf-other]');
          if (sel && other) other.hidden = sel.value !== OTHER;
        } else if (type === 'radio' || type === 'checkboxes') {
          w.querySelectorAll('.lf__option').forEach((o) => {
            const inp = o.querySelector('input[type="radio"], input[type="checkbox"]');
            const on = !!(inp && inp.checked);
            o.classList.toggle('is-checked', on);
            const other = o.querySelector('[data-lf-other]');
            if (other) other.hidden = !on;
          });
        } else if (type === 'yes_no' || type === 'rating') {
          w.querySelectorAll('.lf__option').forEach((o) => {
            const inp = o.querySelector('input');
            o.classList.toggle('is-checked', !!(inp && inp.checked));
          });
        } else if (type === 'consent') {
          const c = w.querySelector('.lf__consent');
          const inp = w.querySelector('input[type="checkbox"]');
          if (c && inp) c.classList.toggle('is-checked', inp.checked);
        }
      }
      if (formConsent) {
        const c = formConsent.querySelector('.lf__consent');
        const inp = formConsent.querySelector('input[type="checkbox"]');
        if (c && inp) c.classList.toggle('is-checked', inp.checked);
      }
    };

    /* ------------------------------------------------ errors */
    const controlsOf = (wrap) => Array.from(wrap.querySelectorAll('input, select, textarea')).filter((el) => el.type !== 'hidden');
    const mainControl = (wrap) => wrap.querySelector('#q-' + wrap.dataset.lfField) || controlsOf(wrap)[0];

    function setError(wrap, msg, announceIt) {
      const p = wrap.querySelector('[data-lf-error]');
      const on = !!msg;
      wrap.classList.toggle('has-error', on);
      if (p) {
        p.setAttribute('aria-live', announceIt ? 'polite' : 'off');
        p.removeAttribute('role');
        p.textContent = msg || '';
        p.hidden = !on;
      }
      const main = mainControl(wrap);
      const errId = p ? p.id : '';
      const targets = [];
      if (main) targets.push(main);
      const group = wrap.querySelector('fieldset');
      if (group) targets.push(group);
      for (const el of targets) {
        if (on) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid');
        if (errId) {
          const ids = (el.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean).filter((x) => x !== errId);
          if (on) ids.push(errId);
          if (ids.length) el.setAttribute('aria-describedby', ids.join(' ')); else el.removeAttribute('aria-describedby');
        }
      }
    }
    const clearError = (wrap) => setError(wrap, '', false);

    function setFormConsentError(msg, announceIt) {
      if (!formConsent) return;
      const p = formConsent.querySelector('[data-lf-error]');
      const inp = formConsent.querySelector('input[type="checkbox"]');
      formConsent.classList.toggle('has-error', !!msg);
      if (p) { p.setAttribute('aria-live', announceIt ? 'polite' : 'off'); p.removeAttribute('role'); p.textContent = msg || ''; p.hidden = !msg; }
      if (inp) { if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid'); }
    }

    /** Returns an error message or null for one VISIBLE question. */
    function validate(wrap) {
      const id = wrap.dataset.lfField;
      const f = fieldDefs[id];
      const type = wrap.dataset.lfType;
      if (!f || !INPUT_TYPES.has(type)) return null;
      const label = f.label || '';
      const required = !!f.required;
      const val = f.validation || {};
      const req = () => (required ? T('errors', 'required', { label }) : null);
      const v = readValue(wrap);
      const s = Array.isArray(v) ? '' : String(v);

      switch (type) {
        case 'text':
        case 'textarea': {
          if (s === '') return req();
          const len = Array.from(s).length;
          const min = parseInt(val.min_length || 0, 10);
          const max = parseInt(val.max_length || 0, 10);
          if (min > 0 && len < min) return T('errors', 'min_length', { label, min });
          if (max > 0 && len > max) return T('errors', 'max_length', { label, max });
          if (val.pattern && !patternOk(config.patterns, val.pattern, s)) {
            const hint = config.patterns && config.patterns[val.pattern] ? config.patterns[val.pattern].hint : '';
            return T('errors', 'pattern', { label, hint });
          }
          return null;
        }
        case 'email':
          if (s === '') return req();
          return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(s) && s.length <= 190 ? null : T('errors', 'email', { label });
        case 'phone':
          if (s === '') return req();
          return phoneOk(s) ? null : T('errors', 'phone', { label });
        case 'number': {
          if (s === '') return req();
          const n = /^-?[0-9]+,[0-9]+$/.test(s) ? s.replace(',', '.') : s;
          if (!/^-?[0-9]{1,15}(\.[0-9]{1,6})?$/.test(n)) return T('errors', 'number', { label });
          if (val.min !== undefined && val.min !== null && val.min !== '' && parseFloat(n) < parseFloat(val.min)) return T('errors', 'min_number', { label, min: val.min });
          if (val.max !== undefined && val.max !== null && val.max !== '' && parseFloat(n) > parseFloat(val.max)) return T('errors', 'max_number', { label, max: val.max });
          return null;
        }
        case 'date': {
          if (s === '') return req();
          const d = /^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/.exec(s);
          const dt = d ? new Date(Date.UTC(+d[1], +d[2] - 1, +d[3])) : null;
          if (!dt || dt.getUTCDate() !== +d[3] || +d[1] < 1900 || +d[1] > 2200) return T('errors', 'date', { label });
          const el = wrap.querySelector('input');
          const min = el && el.getAttribute('min');
          const max = el && el.getAttribute('max');
          if (min && s < min) return T('errors', 'min_date', { label, min: prettyDate(min) });
          if (max && s > max) return T('errors', 'max_date', { label, max: prettyDate(max) });
          return null;
        }
        case 'time': {
          if (s === '') return req();
          if (!/^([01][0-9]|2[0-3]):[0-5][0-9]$/.test(s)) return T('errors', 'time', { label });
          const el = wrap.querySelector('input');
          const min = el && el.getAttribute('min');
          const max = el && el.getAttribute('max');
          if (min && s < min) return T('errors', 'min_time', { label, min });
          if (max && s > max) return T('errors', 'max_time', { label, max });
          return null;
        }
        case 'select':
        case 'radio': {
          if (s === '') return req();
          if (s === OTHER) {
            const o = wrap.querySelector('[data-lf-other]');
            if (!o || o.value.trim() === '') return T('errors', 'other_required', { label });
          }
          return null;
        }
        case 'checkboxes': {
          const list = Array.isArray(v) ? v : [];
          if (list.length === 0) return req();
          const min = parseInt(val.min || 0, 10);
          const max = parseInt(val.max || 0, 10);
          if (min > 0 && list.length < min) return T('errors', 'min_select', { label, min });
          if (max > 0 && list.length > max) return T('errors', 'max_select', { label, max });
          if (list.includes(OTHER)) {
            const o = wrap.querySelector('[data-lf-other]');
            if (!o || o.value.trim() === '') return T('errors', 'other_required', { label });
          }
          return null;
        }
        case 'yes_no':
          return s === '' ? req() : null;
        case 'rating':
          return s === '' ? req() : null;
        case 'consent':
          return s === 'yes' ? null : (required ? T('errors', 'consent', { label }) : null);
        case 'file': {
          const input = wrap.querySelector('input[type="file"]');
          const n = input && input.files ? input.files.length : 0;
          if (n === 0) return required ? T('errors', 'file_required', { label }) : null;
          return fileProblem(input, Array.from(input.files));
        }
        default:
          return null;
      }
    }

    /** Client hints for files: type, size, count. Returns a message or null. */
    function fileProblem(input, files) {
      const types = (input.dataset.lfTypes || '').split(',').filter(Boolean);
      const maxMb = parseInt(input.dataset.lfMaxMb || '0', 10);
      const maxFiles = parseInt(input.dataset.lfMaxFiles || '1', 10);
      if (files.length > maxFiles) return T('errors', 'file_too_many', { count: maxFiles });
      for (const f of files) {
        if (types.length && !types.includes(fileExt(f.name))) return T('errors', 'file_type', { types: types.map((x) => x.toUpperCase()).join(', ') });
        if (f.size === 0) return T('errors', 'file_empty');
        if (maxMb > 0 && f.size > maxMb * 1048576) return T('errors', 'file_size', { max: maxMb });
      }
      return null;
    }

    const isActive = (wrap) => !wrap.hidden && !wrap.closest('[data-lf-page][hidden]') && res.fields[wrap.dataset.lfField] !== false;

    /** Validate the given wraps; shows each error, returns the list of {id, wrap, msg}. */
    function validateWraps(list, announceIt) {
      const out = [];
      for (const w of list) {
        if (!isActive(w) || !INPUT_TYPES.has(w.dataset.lfType)) { clearError(w); continue; }
        const msg = validate(w);
        setError(w, msg, announceIt);
        if (msg) out.push({ id: w.dataset.lfField, wrap: w, msg });
      }
      return out;
    }

    function validateFormConsent() {
      if (!formConsent) return null;
      const inp = formConsent.querySelector('input[type="checkbox"]');
      if (inp && !inp.checked) {
        const msg = T('errors', 'consent', { label: '' });
        setFormConsentError(msg, false);
        return { id: '_consent', wrap: formConsent, msg };
      }
      setFormConsentError('', false);
      return null;
    }

    /* ------------------------------------------------ error summary */
    function showSummary(items, extra) {
      if (!summary || !summaryList) return;
      summaryList.textContent = '';
      const head = summary.querySelector('.lf__summary-title');
      const intro = summary.querySelector('p');
      if (head) head.hidden = items.length === 0;
      if (intro) intro.hidden = items.length === 0;
      for (const it of items) {
        const li = document.createElement('li');
        const label = it.id === '_consent' ? '' : (fieldDefs[it.id] ? fieldDefs[it.id].label : '');
        if (label || it.id === '_consent') {
          const a = document.createElement('a');
          a.href = '#' + (it.id === '_consent' ? 'q-form-consent' : 'q-' + it.id);
          a.setAttribute('data-lf-goto', it.id);
          const name = it.id === '_consent' ? (formConsent.querySelector('label') ? formConsent.querySelector('label').textContent.replace(/\s*\*\s*$/, '').trim() : '') : label;
          // "Full name is required." already names the question: link the whole sentence; otherwise "Full name: Please use at least 2 characters."
          if (!name || it.msg.toLowerCase().includes(name.toLowerCase())) {
            a.textContent = it.msg;
            li.appendChild(a);
          } else {
            a.textContent = name;
            li.appendChild(a);
            li.appendChild(document.createTextNode(': ' + it.msg));
          }
        } else {
          li.textContent = it.msg;
        }
        summaryList.appendChild(li);
      }
      if (extra) {
        const li = document.createElement('li');
        if (extra.reload) {
          const a = document.createElement('a');
          a.href = '#';
          a.setAttribute('data-lf-reload', '1');
          a.textContent = extra.text;
          li.appendChild(a);
        } else {
          li.textContent = extra.text;
        }
        summaryList.appendChild(li);
      }
      summary.hidden = false;
      focusSoon(summary);
    }
    const hideSummary = () => { if (summary) { summary.hidden = true; if (summaryList) summaryList.textContent = ''; } };

    function focusSoon(el) {
      window.requestAnimationFrame(() => {
        try { el.focus({ preventScroll: true }); } catch (e) { el.focus(); }
        scrollTo(el);
      });
    }
    function scrollTo(el) {
      const r = el.getBoundingClientRect();
      const margin = 96;
      if (r.top < margin || r.bottom > window.innerHeight - 24) {
        window.scrollTo({ top: Math.max(0, window.scrollY + r.top - margin), behavior: reduceMotion() ? 'auto' : 'smooth' });
      }
    }

    /* ------------------------------------------------ steps */
    const visiblePages = () => pageEls.filter((p) => {
      if (p.hidden) return false;
      const ws = Array.from(p.querySelectorAll('[data-lf-field]'));
      return ws.length === 0 || ws.some((w) => !w.hidden);
    });

    function refreshSteps(opts) {
      const vp = visiblePages();
      if (vp.length === 0) return;
      const keep = (opts && opts.page) || pageEls.find((p) => p.classList.contains('is-current'));
      let idx = keep ? vp.indexOf(keep) : -1;
      if (idx < 0) {
        // the current page disappeared (a condition changed): move to the nearest page that still exists
        const was = keep ? pageEls.indexOf(keep) : 0;
        idx = vp.findIndex((p) => pageEls.indexOf(p) >= was);
        if (idx < 0) idx = vp.length - 1;
      }
      current = idx;
      pageEls.forEach((p) => p.classList.toggle('is-current', p === vp[idx]));
      const last = idx === vp.length - 1;
      if (btnPrev) btnPrev.hidden = idx === 0;
      if (btnNext) btnNext.hidden = last;
      if (btnSubmit) btnSubmit.hidden = !last;
      if (progress) {
        progress.hidden = false;
        const title = vp[idx].querySelector('[data-lf-page-title]');
        const t = title && !title.hasAttribute('data-lf-auto') ? title.textContent.trim() : '';
        const stepText = fill((text.ui && text.ui.step_of) || '{current} / {total}', { current: idx + 1, total: vp.length });
        if (stepLabel) {
          const sig = stepText + '|' + t;
          if (stepLabel.dataset.lfSig !== sig) {
            stepLabel.dataset.lfSig = sig;
            stepLabel.textContent = '';
            const label = document.createElement('span');
            label.textContent = stepText;
            stepLabel.appendChild(label);
            if (t) {
              stepLabel.appendChild(document.createTextNode(' · '));
              const strong = document.createElement('strong');
              strong.textContent = t;
              stepLabel.appendChild(strong);
            }
          }
        }
        const pct = Math.round(((idx + 1) / vp.length) * 100);
        if (pctLabel) pctLabel.textContent = pct + '%';
        if (bar) { bar.setAttribute('aria-valuemax', String(vp.length)); bar.setAttribute('aria-valuenow', String(idx + 1)); bar.setAttribute('aria-valuetext', stepText); }
        if (barFill) barFill.style.width = pct + '%';
      }
    }

    function showPage(page, focus) {
      refreshSteps({ page });
      if (focus) {
        const t = page.querySelector('[data-lf-page-title]');
        // focus the step title (screen readers) but scroll to the progress block so "Step 2 of 3" stays visible under the sticky header
        const head = form.querySelector('[data-lf-progress-bar]') || t || page;
        if (t) {
          window.requestAnimationFrame(() => {
            try { t.focus({ preventScroll: true }); } catch (e) { t.focus(); }
            scrollTo(head);
          });
        } else { scrollTo(head); }
      }
    }

    function goStep(delta) {
      const vp = visiblePages();
      const target = vp[Math.min(vp.length - 1, Math.max(0, current + delta))];
      if (!target) return;
      showPage(target, true);
      saveDraftSoon();                      // the draft remembers the step too, so a reload returns to this page
      if (mode === 'live' && delta > 0) { try { window.history.pushState({ lf: form.id, step: pageEls.indexOf(target) }, '', window.location.href); } catch (e) { /* ignore */ } }
    }

    const pageWraps = (page) => Array.from(page.querySelectorAll('[data-lf-field]'));

    function next() {
      const vp = visiblePages();
      const page = vp[current];
      if (!page) return;
      const errs = validateWraps(pageWraps(page), false);
      if (errs.length) { showSummary(errs); return; }
      hideSummary();
      goStep(1);
    }

    /** Move to the page that holds a question and focus it. */
    function goToField(id) {
      if (id === '_consent') { const c = formConsent && formConsent.querySelector('input'); if (c) focusSoon(c); return; }
      const w = wrapOf[id];
      if (!w) return;
      const page = w.closest('[data-lf-page]');
      if (stepped && page && !page.classList.contains('is-current') && !page.hidden) showPage(page, false);
      window.requestAnimationFrame(() => {
        const ctl = controlsOf(w).find((el) => !el.disabled) || mainControl(w);
        if (ctl) { try { ctl.focus({ preventScroll: true }); } catch (e) { ctl.focus(); } scrollTo(w); }
      });
    }

    if (summary) {
      summary.addEventListener('click', (e) => {
        const a = e.target.closest('a');
        if (!a) return;
        if (a.hasAttribute('data-lf-reload')) { e.preventDefault(); window.location.reload(); return; }
        const id = a.getAttribute('data-lf-goto') || (a.getAttribute('href') || '').replace(/^#q-/, '');
        e.preventDefault();
        goToField(id === 'form-consent' ? '_consent' : id);
      });
    }

    /* ------------------------------------------------ inline validation (on blur, then live while the error shows) */
    form.addEventListener('focusout', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement) || !t.matches('input, select, textarea')) return;
      const wrap = t.closest('[data-lf-field]');
      if (wrap && INPUT_TYPES.has(wrap.dataset.lfType) && isActive(wrap)) {
        if (t.type === 'file') return;                       // files are checked when they are chosen
        // radio / checkbox groups and "Other" boxes: only when focus leaves the whole question
        if (e.relatedTarget instanceof Node && wrap.contains(e.relatedTarget) && (t.closest('fieldset') || t.hasAttribute('data-lf-other'))) return;
        setError(wrap, validate(wrap), true);
      } else if (formConsent && formConsent.contains(t) && t.type === 'checkbox' && !t.checked && t.dataset.lfSeen) {
        validateFormConsent();
      }
    });
    form.addEventListener('focusin', (e) => { const t = e.target; if (t instanceof HTMLElement && formConsent && formConsent.contains(t)) t.dataset.lfSeen = '1'; });

    const relive = (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement) || t.type === 'file') return;       // files are checked when they are chosen
      const wrap = t.closest('[data-lf-field]');
      if (wrap && wrap.classList.contains('has-error') && isActive(wrap)) {
        const msg = validate(wrap);
        if (!msg) setError(wrap, '', false);
      }
      if (formConsent && formConsent.contains(t) && formConsent.classList.contains('has-error') && t.checked) setFormConsentError('', false);
    };

    /* ------------------------------------------------ character counter */
    wraps.filter((w) => w.dataset.lfType === 'textarea').forEach((w) => {
      const ta = w.querySelector('textarea');
      if (!ta) return;
      const max = parseInt(ta.getAttribute('maxlength') || '0', 10);
      let counter = null;
      if (max > 0) {
        counter = document.createElement('p');
        counter.className = 'lf__counter';
        counter.setAttribute('aria-hidden', 'true');
        ta.insertAdjacentElement('afterend', counter);
      }
      const sync = () => {
        if (counter) {
          const n = Array.from(ta.value).length;
          counter.textContent = fill((text.ui && text.ui.char_count) || '{count} / {max}', { count: n, max });
          counter.classList.toggle('is-near', n >= max * 0.9);
        }
      };
      ta.addEventListener('input', sync);
      sync();
    });

    /* ------------------------------------------------ files: hints, removable list, drag and drop */
    const picked = new WeakMap();   // input -> File[]
    function canSetFiles() { try { return typeof DataTransfer === 'function' && !!new DataTransfer().files; } catch (e) { return false; } }
    const setFiles = (input, list) => {
      if (canSetFiles()) {
        const dt = new DataTransfer();
        list.forEach((f) => dt.items.add(f));
        input.files = dt.files;
      }
      picked.set(input, list);
    };
    function renderFiles(wrap, input) {
      const ul = wrap.querySelector('[data-lf-files]');
      if (!ul) return;
      ul.textContent = '';
      const list = Array.from(input.files || []);
      list.forEach((f, i) => {
        const li = document.createElement('li');
        li.className = 'lf__file-item';
        const name = document.createElement('span');
        name.className = 'lf__file-name';
        name.textContent = f.name;
        name.title = f.name;
        const size = document.createElement('span');
        size.className = 'lf__file-size';
        size.textContent = humanSize(f.size);
        const rm = document.createElement('button');
        rm.type = 'button';
        rm.className = 'lf__file-remove';
        rm.textContent = (text.ui && text.ui.file_remove) || 'Remove';
        rm.setAttribute('aria-label', ((text.ui && text.ui.file_remove) || 'Remove') + ': ' + f.name);
        rm.addEventListener('click', () => {
          const rest = Array.from(input.files || []).filter((_, k) => k !== i);
          if (canSetFiles()) setFiles(input, rest); else { input.value = ''; picked.set(input, []); }
          renderFiles(wrap, input);
          clearError(wrap);
          apply();
          saveDraftSoon();
          try { input.focus({ preventScroll: true }); } catch (e) { /* ignore */ }
          announce(((text.ui && text.ui.file_remove) || 'Remove') + ': ' + f.name);
        });
        li.append(name, size, rm);
        ul.appendChild(li);
      });
    }
    wraps.filter((w) => w.dataset.lfType === 'file').forEach((w) => {
      const input = w.querySelector('input[type="file"]');
      const drop = w.querySelector('[data-lf-drop]');
      if (!input) return;
      const multi = parseInt(input.dataset.lfMaxFiles || '1', 10) > 1;
      const accept = (incoming) => {
        let list = incoming;
        if (multi) {
          const have = picked.get(input) || [];
          const seen = new Set(have.map((f) => f.name + ':' + f.size));
          list = have.concat(incoming.filter((f) => !seen.has(f.name + ':' + f.size)));
        } else {
          list = incoming.slice(0, 1);
        }
        const msg = fileProblem(input, list);
        if (msg) {
          // refuse the whole pick and keep what was there before
          const before = picked.get(input) || [];
          if (canSetFiles()) setFiles(input, before); else input.value = '';
          setError(w, msg, true);
          renderFiles(w, input);
          apply();
          return;
        }
        setFiles(input, list);
        clearError(w);
        renderFiles(w, input);
        apply();
        saveDraftSoon();
      };
      input.addEventListener('change', () => accept(Array.from(input.files || [])));
      if (drop) {
        ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
        ['dragleave', 'dragend'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('is-over')));
        drop.addEventListener('drop', (e) => {
          drop.classList.remove('is-over');
          if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) { e.preventDefault(); accept(Array.from(e.dataTransfer.files)); }
        });
      }
    });

    /* ------------------------------------------------ the draft in this browser (sessionStorage, never files, cleared on send) */
    const draftKey = config.draftKey || '';
    const canDraft = mode === 'live' || mode === 'embed';
    let draftTimer = 0;
    const store = {
      get() { try { return window.sessionStorage.getItem(draftKey); } catch (e) { return null; } },
      set(v) { try { window.sessionStorage.setItem(draftKey, v); return true; } catch (e) { return false; } },
      del() { try { window.sessionStorage.removeItem(draftKey); } catch (e) { /* ignore */ } },
    };
    function collectDraft() {
      const data = {};
      form.querySelectorAll('input, select, textarea').forEach((el) => {
        const n = el.name;
        if (!n || SKIP_NAMES.has(n) || el.type === 'file' || el.type === 'hidden' || el.type === 'password') return;
        if (!(n.startsWith('f_') || n === 'form_consent')) return;
        if (el.type === 'checkbox' || el.type === 'radio') {
          if (el.checked) (data[n] = data[n] || []).push(el.value);
        } else if (el.value !== '') {
          data[n] = [el.value];
        }
      });
      return data;
    }
    function saveDraft() {
      if (!canDraft || !draftKey || busy) return;
      const data = collectDraft();
      if (Object.keys(data).length === 0) { store.del(); return; }
      const vp = visiblePages();
      const ok = store.set(JSON.stringify({ t: Date.now(), step: stepped && vp[current] ? vp[current].dataset.lfPage : '', data }));
      if (ok && draftNote) draftNote.hidden = false;
    }
    function saveDraftSoon() { window.clearTimeout(draftTimer); draftTimer = window.setTimeout(saveDraft, 400); }
    function restoreDraft() {
      if (!canDraft || !draftKey || form.dataset.lfRestore !== '1') return;
      const raw = store.get();
      if (!raw) return;
      let d;
      try { d = JSON.parse(raw); } catch (e) { store.del(); return; }
      if (!d || typeof d.data !== 'object') return;
      let any = false;
      form.querySelectorAll('input, select, textarea').forEach((el) => {
        const n = el.name;
        if (!n || SKIP_NAMES.has(n) || el.type === 'file' || el.type === 'hidden') return;
        const vals = d.data[n];
        if (!Array.isArray(vals)) return;
        if (el.type === 'checkbox' || el.type === 'radio') {
          const on = vals.includes(el.value);
          if (el.checked !== on) { el.checked = on; any = true; }
        } else if (el.tagName === 'SELECT') {
          if (Array.from(el.options).some((o) => o.value === vals[0])) { el.value = vals[0]; any = true; }
        } else if (el.value !== vals[0]) {
          el.value = vals[0];
          any = true;
        }
      });
      if (!any) return;
      apply();
      wraps.filter((w) => w.dataset.lfType === 'textarea').forEach((w) => { const ta = w.querySelector('textarea'); if (ta) ta.dispatchEvent(new Event('input')); });
      if (stepped && d.step) {
        const page = pageEls.find((p) => p.dataset.lfPage === d.step);
        if (page && visiblePages().includes(page)) refreshSteps({ page });
      }
      if (draftNote) draftNote.hidden = false;
    }
    const draftClear = form.querySelector('[data-lf-draft-clear]');
    if (draftClear) {
      draftClear.addEventListener('click', () => {
        window.clearTimeout(draftTimer);
        store.del();
        form.reset();
        wraps.filter((w) => w.dataset.lfType === 'file').forEach((w) => { const i = w.querySelector('input[type="file"]'); if (i) { picked.set(i, []); renderFiles(w, i); } });
        wraps.forEach(clearError);
        setFormConsentError('', false);
        hideSummary();
        apply();
        wraps.filter((w) => w.dataset.lfType === 'textarea').forEach((w) => { const ta = w.querySelector('textarea'); if (ta) ta.dispatchEvent(new Event('input')); });
        if (stepped) { const vp = visiblePages(); if (vp[0]) showPage(vp[0], false); }
        if (draftNote) draftNote.hidden = true;
        announce(draftClear.textContent.trim());
      });
    }

    /* ------------------------------------------------ events */
    form.addEventListener('input', (e) => { relive(e); apply(); saveDraftSoon(); });
    form.addEventListener('change', (e) => { relive(e); apply(); saveDraftSoon(); });

    if (btnPrev) {
      btnPrev.addEventListener('click', () => {
        hideSummary();
        const vp = visiblePages();
        const st = window.history.state;
        // the Back button of the form and the browser's back button walk the same steps: use the history entry when there is one
        if (mode === 'live' && st && st.lf === form.id && st.step === pageEls.indexOf(vp[current]) && st.step > 0) window.history.back(); else goStep(-1);
      });
    }
    if (btnNext) btnNext.addEventListener('click', next);

    // Enter inside a one-line field moves on instead of sending half a form
    form.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' || e.isComposing) return;
      const t = e.target;
      if (!(t instanceof HTMLElement) || !t.matches('input:not([type="submit"]):not([type="button"]):not([type="file"])')) return;
      if (stepped && btnNext && !btnNext.hidden) { e.preventDefault(); next(); }
    });

    window.addEventListener('popstate', (e) => {
      if (mode !== 'live' || !stepped) return;
      const vp = visiblePages();
      const was = vp[current];
      const idx = e.state && e.state.lf === form.id && typeof e.state.step === 'number' ? e.state.step : 0;
      const page = pageEls[idx];
      if (page && vp.includes(page) && page !== was) showPage(page, true);
      else if (!e.state && vp[0] && was !== vp[0]) showPage(vp[0], true);
      saveDraftSoon();
    });

    /* ------------------------------------------------ sending */
    function setBusy(on, label) {
      busy = on;
      form.setAttribute('aria-busy', on ? 'true' : 'false');
      if (!btnSubmit) return;
      if (on) {
        if (!btnSubmit.dataset.lfLabel) btnSubmit.dataset.lfLabel = btnSubmit.textContent;
        btnSubmit.classList.add('is-busy');
        btnSubmit.setAttribute('aria-disabled', 'true');
        btnSubmit.textContent = label || (text.ui && text.ui.submitting) || '...';
      } else {
        btnSubmit.classList.remove('is-busy');
        btnSubmit.removeAttribute('aria-disabled');
        if (btnSubmit.dataset.lfLabel) btnSubmit.textContent = btnSubmit.dataset.lfLabel;
      }
    }

    function sameOrigin(u) { try { return new URL(u, window.location.href).origin === window.location.origin; } catch (e) { return false; } }

    function applyServerErrors(errors, errorPages) {
      const items = [];
      let general = null;
      Object.keys(errors || {}).forEach((k) => {
        const msg = String(errors[k]);
        if (k === '_consent') { setFormConsentError(msg, false); items.push({ id: '_consent', msg }); return; }
        if (k === '_form') { general = msg; return; }
        const w = wrapOf[k];
        if (w) { setError(w, msg, false); items.push({ id: k, wrap: w, msg }); } else { general = general || msg; }
      });
      // document order, like the server's list
      items.sort((a, b) => {
        const pos = (x) => (x.id === '_consent' ? 1e6 : wraps.indexOf(wrapOf[x.id]));
        return pos(a) - pos(b);
      });
      showSummary(items, general ? { text: general } : null);
      const first = items[0];
      if (first && first.wrap && stepped) {
        const page = first.wrap.closest('[data-lf-page]');
        if (page && !page.hidden && !page.classList.contains('is-current')) refreshSteps({ page });
      }
      return items;
    }

    function send() {
      const data = new FormData(form);
      const xhr = new XMLHttpRequest();
      xhr.open('POST', form.getAttribute('action') || window.location.href, true);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.timeout = 180000;
      setBusy(true);
      let big = false;
      data.forEach((v) => { if (v instanceof File && v.size > 300 * 1024) big = true; });
      if (big && xhr.upload) {
        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable && e.total > 0) setBusy(true, ((text.ui && text.ui.submitting) || '') + ' ' + Math.min(99, Math.round((e.loaded / e.total) * 100)) + '%');
        });
      }
      const fail = (msg, reload) => { setBusy(false); showSummary([], { text: msg, reload: !!reload }); };
      xhr.addEventListener('load', () => {
        let body = null;
        try { body = JSON.parse(xhr.responseText); } catch (e) { body = null; }
        if (xhr.status === 200 && body && body.ok) {
          window.clearTimeout(draftTimer);
          store.del();
          if (body.redirect && sameOrigin(body.redirect)) { window.location.assign(body.redirect); return; }
          window.location.reload();
          return;
        }
        setBusy(false);
        if (body && body.error === 'validation') {
          applyServerErrors(body.errors, body.error_pages);
          return;
        }
        if (body && (body.error === 'closed' || body.error === 'full' || body.error === 'not_open' || body.error === 'job')) { window.location.reload(); return; }
        if (xhr.status === 419 || xhr.status === 403 || (body && body.error === 'expired')) { fail(T('errors', 'expired'), true); return; }
        if (xhr.status === 429 || (body && body.error === 'throttled')) { fail((body && body.message) || T('errors', 'throttled')); return; }
        if (body && body.errors && typeof body.errors === 'object') { applyServerErrors(body.errors, []); return; }
        fail((body && body.message) || T('errors', 'generic'));
      });
      xhr.addEventListener('error', () => fail(T('errors', 'generic')));
      xhr.addEventListener('timeout', () => fail(T('errors', 'generic')));
      xhr.send(data);
    }

    form.addEventListener('submit', (e) => {
      if (mode === 'preview' || (btnSubmit && btnSubmit.disabled)) { e.preventDefault(); return; }
      if (busy) { e.preventDefault(); return; }
      if (typeof XMLHttpRequest === 'undefined' || typeof FormData === 'undefined') return;       // plain post
      e.preventDefault();
      const errs = validateWraps(wraps, false);
      const consent = validateFormConsent();
      if (consent) errs.push(consent);
      if (errs.length) {
        showSummary(errs);
        const first = errs[0];
        if (stepped && first.wrap && first.id !== '_consent') {
          const page = first.wrap.closest('[data-lf-page]');
          if (page && !page.classList.contains('is-current')) refreshSteps({ page });
        }
        return;
      }
      hideSummary();
      send();
    });

    /* ------------------------------------------------ print a blank copy */
    if (btnPrint && (mode === 'live' || mode === 'embed') && form.getAttribute('action')) {
      btnPrint.hidden = false;
      btnPrint.addEventListener('click', () => {
        let u;
        try { u = new URL(form.getAttribute('action'), window.location.href); } catch (e) { return; }
        u.search = '';
        u.searchParams.set('print', '1');
        const frame = document.createElement('iframe');
        frame.setAttribute('aria-hidden', 'true');
        frame.tabIndex = -1;
        // off-screen but with a real size: browsers print an iframe that is display:none / zero-sized as an empty page
        frame.style.cssText = 'position:fixed;left:-10000px;top:0;width:820px;height:900px;border:0';
        frame.src = u.toString();
        frame.addEventListener('load', () => {
          try { frame.contentWindow.focus(); frame.contentWindow.print(); } catch (err) { window.open(u.toString(), '_blank', 'noopener'); }
          window.setTimeout(() => frame.remove(), 120000);
        }, { once: true });
        document.body.appendChild(frame);
      });
    }

    /* ------------------------------------------------ start */
    if (stepped) form.classList.add('is-stepped');
    apply();
    firstRun = false;
    restoreDraft();
    if (stepped) {
      // after a server round trip with errors: open the first page that has one
      const bad = wraps.find((w) => w.classList.contains('has-error') && isActive(w));
      const page = bad ? bad.closest('[data-lf-page]') : null;
      refreshSteps(page ? { page } : undefined);
      if (!page && !pageEls.some((p) => p.classList.contains('is-current'))) { const vp = visiblePages(); if (vp[0]) refreshSteps({ page: vp[0] }); }
      if (mode === 'live') { try { window.history.replaceState({ lf: form.id, step: 0 }, '', window.location.href); } catch (e) { /* ignore */ } }
    }
    syncChoices();
    form.classList.add('is-enhanced');
    // a server-rendered error summary (no-JS round trip) keeps the focus where a screen reader expects it
    if (summary && !summary.hidden) { focusSoon(summary); }
  }

  /* ------------------------------------------------------------------ the thank-you page, the blank-copy sheet, boot */

  function boot() {
    document.querySelectorAll('form[data-lf]').forEach((f) => { if (!f.dataset.lfReady) { f.dataset.lfReady = '1'; init(f); } });
    // thank-you page: make sure no draft of this form stays in the browser
    document.querySelectorAll('[data-lf-done]').forEach((el) => {
      const key = el.dataset.lfDraftKey;
      if (key) { try { window.sessionStorage.removeItem(key); } catch (e) { /* ignore */ } }
    });
    document.querySelectorAll('[data-lf-print-now]').forEach((b) => b.addEventListener('click', () => window.print()));
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
