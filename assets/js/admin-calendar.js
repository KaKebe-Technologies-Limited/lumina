/*!
 * admin-calendar.js - the Calendar admin screens. OWNER: ADMIN agent. Progressive enhancement only: every form posts normally and the server
 * validates everything; this script only makes the screens easier. No inline JS (CSP), no dependencies.
 * Contract: docs/CALENDAR.md. Endpoint used: POST /admin/api/calendar/preview (header X-CSRF-Token, LuminaAdmin.csrf()).
 * Hooks (all data attributes):
 *   dialog[data-cal-quick]            the quick-add dialog; [data-cal-open-quick] opens it, [data-cal-add="YYYY-MM-DD"][data-cal-label] opens it for a day,
 *                                     [data-cal-close] closes it, td[data-cal-day] (month grid): a click on an empty part of a day adds a date on it;
 *                                     data-open: opens by itself after a rejected submit; [data-cal-full-form] link to the full form keeps the date
 *   [data-cal-allday] + [data-cal-times]            "All day" tick box hides the time fields
 *   [data-cal-quick-repeat] + [data-cal-quick-until]  the quick form shows "Repeat until" only when it repeats
 *   form[data-cal-bulk]               bulk bar of the list: [data-select-all] [data-select-row] [data-bulk-action] [data-selected-count]; [data-can-delete]
 *   input[data-autosubmit-check]      submits its form on change
 *   form[data-cal-form]               the entry editor: [data-cal-repeat] (select) + [data-cal-rule="types"] panels, [data-cal-unit], [data-cal-nth-box],
 *                                     [data-cal-monthly-mode], [data-cal-easter] + [data-cal-easter-custom], [data-cal-tbc] / [data-cal-tbc-note],
 *                                     [data-cal-weekday], [data-cal-end], live "Next dates": [data-cal-preview] [data-cal-preview-rule/-list/-msg] and
 *                                     [data-cal-pv] on every input that changes the schedule (data-preview-url, data-entry-id on the form)
 *   [data-cal-color] [data-cal-color-pick] [data-cal-color-clear]   colour text field, colour picker, "use the type's colour"
 *   form[data-cal-occ-form]           one-date change form: radios [data-cal-action], fields [data-cal-for="move confirm"]
 *   [data-cal-type]                   a card of Types & colours: [data-cal-name] [data-cal-color] [data-cal-color-pick] [data-cal-icon] [data-cal-chip]
 *                                     [data-cal-chip-name] [data-cal-chip-icon] [data-cal-contrast] ([data-check] rows with [data-cal-ratio] [data-cal-verdict]
 *                                     [data-cal-contrast-msg]) and [data-cal-icon-bank] > [data-icon]
 *   form[data-cal-upload] / [data-cal-file]    CSV import: checks the size and the file type before upload
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var csrf = function () {
        if (window.LuminaAdmin && window.LuminaAdmin.csrf) return window.LuminaAdmin.csrf();
        var m = $('meta[name="csrf-token"]');
        return m ? m.content : '';
    };
    var plural = function (n, one, many) { return n + ' ' + (n === 1 ? one : many); };
    var fire = function (el, type) { el.dispatchEvent(new Event(type, { bubbles: true })); };

    // ------------------------------------------------------------------ "All day" hides the times, "Repeat" shows "until" (quick form and editor)
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (t.matches && t.matches('[data-cal-allday]')) {
            var scope = t.closest('form') || document;
            $$('[data-cal-times]', scope).forEach(function (box) { box.hidden = t.checked; });
        }
        if (t.matches && t.matches('[data-cal-quick-repeat]')) {
            var form = t.closest('form');
            $$('[data-cal-quick-until]', form || document).forEach(function (box) { box.hidden = t.value === 'none'; });
        }
        if (t.matches && t.matches('input[data-autosubmit-check]') && t.form) t.form.submit();
    });

    // ------------------------------------------------------------------ quick-add dialog
    (function quickAdd() {
        var dlg = $('dialog[data-cal-quick]');
        if (!dlg || typeof dlg.showModal !== 'function') return;          // no dialog support: the form stays an ordinary card on the page
        var form = $('[data-cal-quick-form]', dlg);
        if (!form) return;
        var dateInput = $('input[name="date"]', form);
        var title = $('input[name="title"]', form);
        var sub = $('[data-cal-quick-sub]', dlg);
        var full = $('[data-cal-full-form]', dlg);
        var defaultSub = sub ? sub.textContent : '';
        var fullBase = full ? (full.getAttribute('href') || '').split('?')[0] : '';
        var syncFull = function () {
            if (full && dateInput && dateInput.value) full.setAttribute('href', fullBase + '?date=' + encodeURIComponent(dateInput.value));
        };
        var syncToggles = function () {
            $$('[data-cal-allday]', form).forEach(function (c) { fire(c, 'change'); });
            $$('[data-cal-quick-repeat]', form).forEach(function (c) { fire(c, 'change'); });
        };
        var open = function (date, label, keep) {
            if (!keep) {
                form.reset();
                var more = $('details.cal-more', form);
                if (more) more.open = false;
                $$('.field--error', form).forEach(function (f) { f.classList.remove('field--error'); });
                $$('.field__error', form).forEach(function (f) { f.remove(); });
                if (date && dateInput) dateInput.value = date;
                syncToggles();
            }
            if (sub) sub.textContent = label ? 'Adding a date on ' + label + '.' : defaultSub;
            syncFull();
            if (!dlg.open) dlg.showModal();
            if (title) title.focus();
        };
        var close = function () { if (dlg.open) dlg.close(); };

        var downOnBackdrop = false;
        dlg.addEventListener('mousedown', function (e) { downOnBackdrop = e.target === dlg; });
        dlg.addEventListener('click', function (e) { if (e.target === dlg && downOnBackdrop) close(); });
        if (dateInput) dateInput.addEventListener('change', syncFull);

        document.addEventListener('click', function (e) {
            var closer = e.target.closest('[data-cal-close]');
            if (closer && dlg.contains(closer)) { e.preventDefault(); close(); return; }
            var opener = e.target.closest('[data-cal-open-quick]');
            if (opener) { e.preventDefault(); open(null, '', false); return; }
            var add = e.target.closest('[data-cal-add]');
            if (add) { e.preventDefault(); open(add.getAttribute('data-cal-add'), add.getAttribute('data-cal-label') || '', false); return; }
            // a click on an empty part of a day in the month grid = add a date on that day (links and buttons keep their own job)
            var cell = e.target.closest('td[data-cal-day]');
            if (cell && !e.target.closest('a, button, input, select, textarea, label, summary')) {
                var link = $('[data-cal-add]', cell);
                open(cell.getAttribute('data-cal-day'), link ? link.getAttribute('data-cal-label') || '' : '', false);
            }
        });

        if (dlg.hasAttribute('data-open')) open(null, '', true);              // the server rejected the form: show it again with the messages
        else if (window.location.hash === '#quick-add') open(null, '', true); // the no-JavaScript link of "Add a date" / a day's plus button
        syncToggles();
    }());

    // ------------------------------------------------------------------ list: bulk bar
    $$('form[data-cal-bulk]').forEach(function (form) {
        var select = $('[data-bulk-action]', form);
        var all = $('[data-select-all]');
        var rows = $$('[data-select-row]');
        var count = $('[data-selected-count]', form);
        var selected = function () { return rows.filter(function (r) { return r.checked; }); };
        var refresh = function () {
            var n = selected().length;
            rows.forEach(function (r) { var tr = r.closest('tr'); if (tr) tr.classList.toggle('is-selected', r.checked); });
            if (all) { all.checked = n > 0 && n === rows.length; all.indeterminate = n > 0 && n < rows.length; }
            if (count) count.textContent = n > 0 ? plural(n, 'entry ticked', 'entries ticked') : '';
        };
        if (all) all.addEventListener('change', function () { rows.forEach(function (r) { r.checked = all.checked; }); refresh(); });
        rows.forEach(function (r) { r.addEventListener('change', refresh); });
        form.addEventListener('submit', function (e) {
            var n = selected().length;
            var action = select ? select.value : '';
            var stop = function (msg, el) { e.preventDefault(); e.stopImmediatePropagation(); if (count) count.textContent = msg; if (el) el.focus(); };
            if (n === 0) { stop('Tick at least one entry first.', rows[0]); return; }
            if (!action) { stop('Choose what to do with the ticked entries.', select); return; }
            if (action === 'delete' && !window.confirm('Delete ' + plural(n, 'entry', 'entries') + ' for good? Holidays and days that came with the site are kept (hide them instead). This cannot be undone.')) e.preventDefault();
        });
        refresh();
    });

    // ------------------------------------------------------------------ colour helpers (the same maths as App\Services\Calendar\ColorContrast)
    var WHITE = '#ffffff';
    var INK = '#1d1e20';
    var colour = {
        norm: function (hex) {
            var h = String(hex == null ? '' : hex).trim().replace(/^#/, '');
            if (/^[0-9a-f]{3}$/i.test(h)) h = h.replace(/./g, function (c) { return c + c; });
            return /^[0-9a-f]{6}$/i.test(h) ? '#' + h.toLowerCase() : null;
        },
        rgb: function (hex) {
            var h = (colour.norm(hex) || '#000000').slice(1);
            return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)];
        },
        hex: function (r, g, b) {
            var c = function (v) { v = Math.max(0, Math.min(255, v)); return (v < 16 ? '0' : '') + v.toString(16); };
            return '#' + c(r) + c(g) + c(b);
        },
        lum: function (hex) {
            var c = colour.rgb(hex).map(function (v) { v = v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
            return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
        },
        ratio: function (a, b) {
            var la = colour.lum(a), lb = colour.lum(b);
            return Math.round(((Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05)) * 100) / 100;
        },
        textOn: function (bg) {
            var list = [WHITE, INK, '#000000'];
            for (var i = 0; i < list.length; i++) if (colour.ratio(list[i], bg) >= 4.5) return list[i];
            return colour.ratio(WHITE, bg) >= colour.ratio('#000000', bg) ? WHITE : '#000000';
        },
        tint: function (hex, amount) {
            var c = colour.rgb(hex);
            var a = amount == null ? 0.88 : amount;
            return colour.hex(Math.round(c[0] + (255 - c[0]) * a), Math.round(c[1] + (255 - c[1]) * a), Math.round(c[2] + (255 - c[2]) * a));
        },
        darken: function (hex, bg, min) {
            var h = colour.norm(hex) || '#000000';
            for (var i = 0; i < 40 && colour.ratio(h, bg) < (min || 4.5); i++) {
                var c = colour.rgb(h);
                h = colour.hex(Math.round(c[0] * 0.92), Math.round(c[1] * 0.92), Math.round(c[2] * 0.92));
            }
            return h;
        },
        pair: function (color) {
            var solid = colour.norm(color) || '#5a6470';
            var on = colour.textOn(solid);
            var tint = colour.tint(solid);
            var text = colour.darken(solid, tint);
            return { solid: solid, on_solid: on, tint: tint, text: text, ratio_solid: colour.ratio(on, solid), ratio_text: colour.ratio(text, tint), marker: colour.ratio(solid, WHITE) };
        }
    };

    // colour text field <-> colour picker (editor override and types)
    var bindColour = function (root, onChange) {
        var text = $('[data-cal-color]', root);
        var pick = $('[data-cal-color-pick]', root);
        var clear = $('[data-cal-color-clear]', root);
        if (!text) return;
        if (pick) pick.addEventListener('input', function () { text.value = pick.value; fire(text, 'input'); });
        text.addEventListener('input', function () {
            var n = colour.norm(text.value);
            if (n && pick) pick.value = n;
            if (onChange) onChange(n);
        });
        if (clear) clear.addEventListener('click', function () { text.value = ''; fire(text, 'input'); text.focus(); });
    };

    // ------------------------------------------------------------------ the entry editor
    $$('form[data-cal-form]').forEach(function (f) {
        var rec = $('[data-cal-repeat]', f);
        var panels = $$('[data-cal-rule]', f);
        var unit = $('[data-cal-unit]', f);
        var units = { daily: 'day(s)', weekly: 'week(s)', monthly: 'month(s)', yearly: 'year(s)', nth_weekday: 'year(s)' };
        var start = $('input[name="start_date"]', f);
        var end = $('[data-cal-end]', f);
        var weekdayBoxes = $$('[data-cal-weekday]', f);
        var weekdayTouched = false;

        var apply = function () {
            var v = rec ? rec.value : 'none';
            panels.forEach(function (p) { p.hidden = p.getAttribute('data-cal-rule').split(' ').indexOf(v) === -1; });
            var nth = $('[data-cal-nth-box]', f);
            if (nth && v === 'monthly') {
                var mode = $('[data-cal-monthly-mode]:checked', f);
                nth.hidden = !(mode && mode.value === 'nth_weekday');
            }
            if (unit) unit.textContent = units[v] || 'time(s)';
            $$('[data-cal-tbc]', f).forEach(function (b) { b.hidden = v === 'islamic'; });
            $$('[data-cal-tbc-note]', f).forEach(function (b) { b.hidden = v !== 'islamic'; });
            var custom = $('[data-cal-easter-custom]', f);
            var preset = $('[data-cal-easter]', f);
            if (custom && preset) custom.hidden = preset.value !== 'custom' || v !== 'easter';
        };

        // weekly: until the person chooses days, the weekday of the start date is ticked
        var isoWeekday = function (iso) {
            var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
            if (!m) return 0;
            var d = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3])).getUTCDay();
            return d === 0 ? 7 : d;
        };
        weekdayBoxes.forEach(function (b) { b.addEventListener('click', function () { weekdayTouched = true; }); });
        var followStart = function () {
            if (weekdayTouched || !start || !weekdayBoxes.length) return;
            var wd = isoWeekday(start.value);
            if (!wd) return;
            weekdayBoxes.forEach(function (b) { b.checked = parseInt(b.value, 10) === wd; });
        };
        if (end && start) {
            var syncEnd = function () { end.min = start.value || ''; };
            start.addEventListener('change', syncEnd);
            syncEnd();
        }
        if (start) start.addEventListener('change', followStart);

        // live preview ("Next dates"): the same engine as the public calendar, nothing is saved
        var url = f.getAttribute('data-preview-url');
        var entryId = f.getAttribute('data-entry-id');
        var ruleEl = $('[data-cal-preview-rule]', f);
        var listEl = $('[data-cal-preview-list]', f);
        var msgEl = $('[data-cal-preview-msg]', f);
        var timer = null;
        var seq = 0;
        var payload = function () {
            var p = new URLSearchParams();
            var val = function (name) { var el = $('[name="' + name + '"]', f); return el ? el.value : ''; };
            ['start_date', 'end_date', 'recurrence', 'repeat_until', 'rule_interval', 'rule_nth', 'rule_weekday', 'rule_month', 'rule_easter', 'rule_easter_custom', 'rule_islamic'].forEach(function (n) {
                var v = val(n);
                if (v !== '') p.append(n, v);
            });
            var mode = $('[name="rule_monthly_mode"]:checked', f);
            if (mode) p.append('rule_monthly_mode', mode.value);
            $$('[name="rule_weekdays[]"]:checked', f).forEach(function (c) { p.append('rule_weekdays[]', c.value); });
            if (entryId) p.append('entry_id', entryId);
            return p;
        };
        var render = function (j) {
            if (!ruleEl || !listEl || !msgEl) return;
            listEl.textContent = '';
            if (j && j.ok && j.dates && j.dates.length) {
                ruleEl.textContent = j.description || '';
                j.dates.forEach(function (d) { var li = document.createElement('li'); li.textContent = d.label; listEl.appendChild(li); });
                msgEl.hidden = true;
                return;
            }
            ruleEl.textContent = '';
            var errs = j && j.errors ? Object.keys(j.errors).map(function (k) { return j.errors[k]; }) : [];
            msgEl.textContent = errs.length ? errs[0] : (j && j.ok ? 'No dates left: the repeat has ended.' : 'Choose a date to see the next dates.');
            msgEl.hidden = false;
        };
        var run = function () {
            if (!url || f.getAttribute('data-locked') === '1') return;
            var mine = ++seq;
            fetch(url, {
                method: 'POST', credentials: 'same-origin', body: payload(),
                headers: { 'X-CSRF-Token': csrf(), 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
            }).then(function (r) { return r.json().catch(function () { return null; }); }).then(function (j) {
                if (mine !== seq) return;
                if (j === null) { render({ ok: false, errors: { _: 'The preview is not available right now. Your dates are still saved when you press Save.' } }); return; }
                render(j);
            }).catch(function () {
                if (mine === seq) render({ ok: false, errors: { _: 'The preview is not available right now. Your dates are still saved when you press Save.' } });
            });
        };
        var schedule = function () { window.clearTimeout(timer); timer = window.setTimeout(run, 250); };
        f.addEventListener('input', function (e) { if (e.target.matches && e.target.matches('[data-cal-pv]')) schedule(); });
        f.addEventListener('change', function (e) {
            var t = e.target;
            if (t.matches && t.matches('[data-cal-repeat], [data-cal-monthly-mode], [data-cal-easter]')) apply();
            if (t.matches && t.matches('[data-cal-pv]')) schedule();
        });
        apply();

        bindColour(f);
    });

    // ------------------------------------------------------------------ one-date change forms
    $$('form[data-cal-occ-form]').forEach(function (form) {
        var radios = $$('[data-cal-action]', form);
        var fields = $$('[data-cal-for]', form);
        var chosen = function () { var r = radios.filter(function (x) { return x.checked; })[0]; return r ? r.value : ''; };
        var apply = function () {
            var a = chosen();
            fields.forEach(function (el) { el.hidden = el.getAttribute('data-cal-for').split(' ').indexOf(a) === -1; });
        };
        radios.forEach(function (r) { r.addEventListener('change', apply); });
        form.addEventListener('submit', function (e) {
            var a = chosen();
            if (a === 'cancel' && !window.confirm('Cancel this date? Visitors will no longer see it on the calendar.')) e.preventDefault();
            if (a === 'reset' && !window.confirm('Go back to the usual date? The change you made to this date will be forgotten.')) e.preventDefault();
        });
        apply();
    });

    // ------------------------------------------------------------------ types & colours: live readability check
    $$('[data-cal-type]').forEach(function (card) {
        var name = $('[data-cal-name]', card);
        var icon = $('[data-cal-icon]', card);
        var chipName = $('[data-cal-chip-name]', card);
        var chipIcon = $('[data-cal-chip-icon]', card);
        var bank = $('[data-cal-icon-bank]');
        var box = $('[data-cal-contrast]', card);
        var msg = $('[data-cal-contrast-msg]', card);
        var rows = {};
        $$('[data-check]', card).forEach(function (li) { rows[li.getAttribute('data-check')] = li; });
        var verdict = function (li, ratio, need) {
            if (!li) return;
            var r = $('[data-cal-ratio]', li);
            var b = $('[data-cal-verdict]', li);
            if (r) r.textContent = ratio.toFixed(2) + ':1';
            if (b) {
                var ok = ratio >= need;
                b.textContent = ok ? 'Good' : 'Too low';
                b.className = 'badge ' + (ok ? 'badge--success' : 'badge--warning');
            }
        };
        var update = function (n) {
            var textEl = $('[data-cal-color]', card);
            var hex = n || colour.norm(textEl ? textEl.value : '');
            if (!hex) {
                if (msg) { msg.textContent = 'Enter a colour code such as #2f6f7a.'; msg.classList.add('is-warn'); }
                return;
            }
            var p = colour.pair(hex);
            card.style.setProperty('--cal-solid', p.solid);
            card.style.setProperty('--cal-tint', p.tint);
            card.style.setProperty('--cal-text', p.text);
            card.style.setProperty('--cal-on', p.on_solid);
            verdict(rows.solid, p.ratio_solid, 4.5);
            verdict(rows.text, p.ratio_text, 4.5);
            verdict(rows.marker, p.marker, 3);
            if (msg) {
                msg.classList.toggle('is-warn', p.marker < 3);
                msg.textContent = p.marker >= 3
                    ? 'The calendar picks readable text colours for you. This colour is fine.'
                    : 'This colour is very light: its dots and borders may be hard to see on a white page. Pick a darker colour.';
            }
        };
        bindColour(card, update);
        if (name && chipName) name.addEventListener('input', function () { chipName.textContent = name.value.trim() || 'New type'; });
        if (icon && chipIcon && bank) {
            icon.addEventListener('change', function () {
                var src = $('[data-icon="' + icon.value + '"]', bank);
                if (src) chipIcon.innerHTML = src.innerHTML;
            });
        }
        if (box) update(null);
    });

    // ------------------------------------------------------------------ CSV import: check the file before it is uploaded
    $$('form[data-cal-upload]').forEach(function (form) {
        var file = $('[data-cal-file]', form);
        if (!file) return;
        var note = document.createElement('div');
        note.className = 'field__error';
        note.setAttribute('role', 'alert');
        note.hidden = true;
        file.insertAdjacentElement('afterend', note);
        file.addEventListener('change', function () {
            var f = file.files && file.files[0];
            note.hidden = true;
            if (!f) return;
            if (!/\.(csv|txt)$/i.test(f.name)) { note.textContent = 'That is not a CSV file. In your spreadsheet choose Save as / Download > CSV.'; note.hidden = false; file.value = ''; }
            else if (f.size > 1048576) { note.textContent = 'That file is larger than 1 MB. Split it into smaller files.'; note.hidden = false; file.value = ''; }
        });
    });
    var report = $('#report');
    if (report) { report.scrollIntoView(); report.focus({ preventScroll: true }); }
}());
