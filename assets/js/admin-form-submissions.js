/*
 * Lumina form submissions (admin: inbox of one form, one answer, print view, summary). ES2020, no dependencies, CSP-safe (no inline handlers).
 * Contract: docs/FORMS.md sections 7 and 16. Owner: SUBMISSIONS agent. Every feature is progressive: without JavaScript the bulk form, the links and the
 * table of numbers under the chart still work.
 *
 * Hooks
 *   [data-fs-select-all]          header checkbox: ticks / unticks every input[data-fs-row] on the page
 *   input[data-fs-row]            row checkbox (name="ids[]" form="fs-bulk"); Shift+click ticks the whole range since the last click
 *   form[data-fs-bulk]            bulk bar: needs at least one ticked row and an action; asks to confirm the actions listed in data-fs-danger
 *                                 (comma separated); data-fs-noun names the items ("answers"); [data-fs-count] receives "3 selected" (aria-live)
 *   tr[data-fs-row-href]          a click on the row (outside links, buttons and fields) opens the answer
 *   [data-fs-cols="#table"]       button (hidden until this script runs): shows / hides the question columns that the table drops on narrower
 *                                 screens (class .is-wide on the table; remembered in localStorage when it is available)
 *   [data-fs-print]               button: window.print()
 *   [data-fs-chart] [data-fs-point] [data-fs-readout]   summary chart: the day under the pointer / focus is spelled out in the readout
 */
(() => {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // ------------------------------------------------------------------ selection + bulk bar
  const rows = () => $$('input[data-fs-row]');
  const ticked = () => rows().filter((c) => c.checked);
  let lastClicked = null;

  const refresh = () => {
    const all = rows();
    const on = ticked();
    $$('[data-fs-select-all]').forEach((master) => {
      master.checked = all.length > 0 && on.length === all.length;
      master.indeterminate = on.length > 0 && on.length < all.length;
    });
    $$('[data-fs-count]').forEach((out) => {
      out.textContent = on.length > 0 ? `${on.length} selected` : '';
    });
    all.forEach((box) => {
      const tr = box.closest('tr');
      if (tr) tr.classList.toggle('is-selected', box.checked);
    });
  };

  document.addEventListener('click', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement) || !t.matches('input[data-fs-row]')) return;
    // Shift+click: tick (or untick) everything between this box and the previous one
    if (e.shiftKey && lastClicked && lastClicked !== t) {
      const list = rows();
      const a = list.indexOf(lastClicked);
      const b = list.indexOf(t);
      if (a !== -1 && b !== -1) {
        const [from, to] = a < b ? [a, b] : [b, a];
        for (let i = from; i <= to; i += 1) list[i].checked = t.checked;
      }
    }
    lastClicked = t;
  });

  document.addEventListener('change', (e) => {
    const t = e.target;
    if (!(t instanceof HTMLInputElement)) return;
    if (t.matches('[data-fs-select-all]')) {
      rows().forEach((c) => { c.checked = t.checked; });
      lastClicked = null;
      refresh();
    } else if (t.matches('input[data-fs-row]')) {
      refresh();
    }
  });

  $$('form[data-fs-bulk]').forEach((form) => {
    const danger = (form.dataset.fsDanger || '').split(',').filter(Boolean);
    const noun = form.dataset.fsNoun || 'items';
    form.addEventListener('submit', (e) => {
      const action = form.querySelector('select[name="action"]');
      const count = ticked().length;
      if (count === 0) {
        e.preventDefault();
        window.alert(`Tick at least one of the ${noun} first.`);
        return;
      }
      if (!action || action.value === '') {
        e.preventDefault();
        window.alert('Choose what to do with the selected answers first.');
        if (action) action.focus();
        return;
      }
      if (danger.includes(action.value)) {
        const label = action.options[action.selectedIndex].text;
        const permanent = action.value === 'delete'
          ? ' This removes them and their files for good.'
          : '';
        const what = count === 1 ? noun.replace(/s$/, '') : noun;
        if (!window.confirm(`${label}: ${count} ${what}?${permanent}`)) e.preventDefault();
      }
    });
  });

  // ------------------------------------------------------------------ whole-row click opens the answer
  document.addEventListener('click', (e) => {
    const tr = e.target instanceof Element ? e.target.closest('tr[data-fs-row-href]') : null;
    if (!tr || e.defaultPrevented || e.button !== 0) return;
    if (e.target.closest('a, button, input, select, textarea, label, summary')) return;
    const selection = window.getSelection ? String(window.getSelection()) : '';
    if (selection !== '') return;                       // somebody is selecting text to copy it
    const href = tr.getAttribute('data-fs-row-href');
    if (!href) return;
    if (e.metaKey || e.ctrlKey) {
      window.open(href, '_blank', 'noopener');
    } else {
      window.location.href = href;
    }
  });

  // ------------------------------------------------------------------ all question columns (the table hides the later ones on narrower screens)
  const KEY = 'lumina.fs.allcols';
  const store = {
    get() { try { return window.localStorage.getItem(KEY) === '1'; } catch (err) { return false; } },
    set(on) { try { window.localStorage.setItem(KEY, on ? '1' : '0'); } catch (err) { /* private mode: the choice just is not remembered */ } },
  };
  $$('[data-fs-cols]').forEach((btn) => {
    const table = $(btn.getAttribute('data-fs-cols') || '');
    if (!table) return;
    const label = $('span', btn);
    const apply = (on) => {
      table.classList.toggle('is-wide', on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (label) label.textContent = on ? 'Show fewer columns' : 'Show all columns';
    };
    const roomy = window.matchMedia ? window.matchMedia('(min-width: 900px)') : { matches: true };
    btn.hidden = false;
    apply(roomy.matches && store.get());           // a choice made on a laptop is not forced onto a phone (stacked cards get very long)
    btn.addEventListener('click', () => {
      const on = !table.classList.contains('is-wide');
      apply(on);
      if (roomy.matches) store.set(on);
    });
  });

  // ------------------------------------------------------------------ print
  document.addEventListener('click', (e) => {
    if (e.target instanceof Element && e.target.closest('[data-fs-print]')) {
      e.preventDefault();
      window.print();
    }
  });

  // ------------------------------------------------------------------ chart readout (the table under the chart is the accessible version)
  const chart = $('[data-fs-chart]');
  const readout = $('[data-fs-readout]');
  if (chart && readout) {
    const idle = readout.textContent;
    const show = (e) => {
      const col = e.target instanceof Element ? e.target.closest('[data-fs-point]') : null;
      if (!col) return;
      $$('.is-active', chart).forEach((x) => x.classList.remove('is-active'));
      col.classList.add('is-active');
      readout.textContent = col.getAttribute('data-fs-point') || idle;
    };
    chart.addEventListener('mouseover', show);
    chart.addEventListener('pointerdown', show);
    chart.addEventListener('mouseleave', () => {
      $$('.is-active', chart).forEach((x) => x.classList.remove('is-active'));
      readout.textContent = idle;
    });
  }

  refresh();
})();
