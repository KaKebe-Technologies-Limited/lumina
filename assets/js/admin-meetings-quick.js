/* Admin > Meetings > Schedule a meeting: the quick helpers of the redesigned page (date and time chips, "use my details", the live summary and the ready-to-send checklist,
   and the highlight of the part of the form being read). It only fills the same fields the form already has, and fires their input / change events so that
   admin-meetings.js (the "when" sentence, the platform fields) reacts exactly as if the person had typed. */
(function () {
  'use strict';
  var form = document.querySelector('[data-meeting-form]');
  if (!form) return;
  var $ = function (sel, root) { return (root || form).querySelector(sel); };

  function fire(el) {
    ['input', 'change'].forEach(function (t) { el.dispatchEvent(new Event(t, { bubbles: true })); });
  }
  function setValue(el, v) { if (el) { el.value = v; fire(el); } }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function ymd(d) { return d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate()); }

  // ---- date chips: counted from "today" in East Africa Time (given by the server), never from the browser's own zone
  var today = form.getAttribute('data-today') || '';
  function addDays(base, n) {
    var p = base.split('-');
    var d = new Date(Date.UTC(+p[0], +p[1] - 1, +p[2] + n));
    return d;
  }
  var dateInput = $('[data-ma-date]');
  var startInput = $('[data-ma-start]');
  form.querySelectorAll('[data-mf-day]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!today || !dateInput) return;
      var v = b.getAttribute('data-mf-day');
      var d;
      if (v === 'next-mon') {
        d = addDays(today, 0);
        var add = (8 - d.getUTCDay()) % 7;
        d = addDays(today, add === 0 ? 7 : add);
      } else {
        d = addDays(today, parseInt(v, 10));
      }
      setValue(dateInput, ymd(d));
      summary();
    });
  });
  form.querySelectorAll('[data-mf-time]').forEach(function (b) {
    b.addEventListener('click', function () { setValue(startInput, b.getAttribute('data-mf-time')); summary(); });
  });

  // ---- organiser: the signed-in person's own details
  var me = $('[data-mf-me]');
  if (me) {
    me.addEventListener('click', function () {
      setValue($('[name="organiser_name"]'), form.getAttribute('data-user-name') || '');
      setValue($('[name="organiser_email"]'), form.getAttribute('data-user-email') || '');
      summary();
    });
  }

  // ---- live summary and checklist
  var sum = $('[data-mf-summary]');
  function txt(sel, v) { var el = sum && sum.querySelector(sel); if (el) el.textContent = v; }
  function checked(key, ok) { var li = sum && sum.querySelector('[data-mf-c="' + key + '"]'); if (li) li.classList.toggle('is-ok', !!ok); }
  function platformLabel() {
    var r = $('[name="platform"]:checked');
    if (!r) return '';
    var span = r.parentNode.querySelector('.ma-platform__name');
    return span ? span.textContent.trim() : '';
  }
  function summary() {
    if (!sum) return;
    var title = ($('[name="title"]') || {}).value || '';
    var date = ($('[name="date"]') || {}).value || '';
    var start = ($('[name="start_time"]') || {}).value || '';
    var link = (($('[name="join_url"]') || {}).value || '').trim();
    var dial = (($('[name="dial_in"]') || {}).value || '').trim();
    var place = (($('[name="location"]') || {}).value || '').trim();
    var oname = (($('[name="organiser_name"]') || {}).value || '').trim();
    var omail = (($('[name="organiser_email"]') || {}).value || '').trim();
    var whenEl = $('[data-ma-when]');
    var whenText = whenEl ? whenEl.textContent.replace(/\s+/g, ' ').trim() : '';
    txt('[data-mf-s-title]', title.trim() || 'Untitled meeting');
    txt('[data-mf-s-when]', whenText || (date && start ? date + ' at ' + start + ' (EAT)' : 'Choose the date and time'));
    txt('[data-mf-s-where]', (platformLabel() || 'How people meet') + (link || place || dial ? ': ' + (link || place || dial) : ''));
    txt('[data-mf-s-org]', oname ? oname + (omail ? ' (' + omail + ')' : '') : 'Organiser');
    checked('title', title.trim() !== '');
    checked('date', date !== '');
    checked('start', start !== '');
    checked('where', link !== '' || place !== '' || dial !== '');
    checked('org', oname !== '' && omail !== '');
  }
  form.addEventListener('input', summary);
  form.addEventListener('change', summary);
  setTimeout(summary, 50);
  // the "when" sentence is written by admin-meetings.js after a moment: refresh the summary once it exists
  var whenNode = $('[data-ma-when]');
  if (whenNode && 'MutationObserver' in window) {
    new MutationObserver(summary).observe(whenNode, { childList: true, characterData: true, subtree: true });
  }

  // ---- the part of the form being read is highlighted in the "Jump to" list
  var links = Array.prototype.slice.call(document.querySelectorAll('[data-mf-nav]'));
  if (links.length && 'IntersectionObserver' in window) {
    var map = {};
    links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting && map[en.target.id]) {
          links.forEach(function (a) { a.classList.remove('is-active'); });
          map[en.target.id].classList.add('is-active');
        }
      });
    }, { rootMargin: '-20% 0px -65% 0px' });
    Object.keys(map).forEach(function (id) { var s = document.getElementById(id); if (s) io.observe(s); });
  }
})();
