/*!
 * calendar.js - the public calendar (/calendar), the compact calendar on the Contact page and the entry detail page. OWNER: WEB agent.
 *
 * PROGRESSIVE ENHANCEMENT ONLY: the server renders the month (?month=YYYY-MM), the filters (a GET form), the list and every link, so all of it
 * works without JavaScript. This script adds, on top of that:
 *   - month changes in place from the JSON feed (GET /calendar/feed?month=&types=): no reload, the address bar follows (pushState / replaceState,
 *     back and forward work), an aria-live announcement ("October 2026: 12 dates"), the controls keep keyboard focus, swipe on touch screens
 *   - type filters (the chips) applied at once, the selection kept in the URL (?types=)
 *   - a keyboard grid: role="grid", one tab stop, arrow keys between days (also across months), Home / End, PageUp / PageDown (Shift = year),
 *     Enter or Space opens the day
 *   - a day panel ("+N more", click or Enter on a day) and an entry dialog (native <dialog>: focus trapped, Esc closes, background inert), with
 *     Add to my calendar (.ics), Share and Copy link
 *   - Share / Copy link / Print buttons (hidden in the markup until they can work)
 * Everything shown comes from the registry through data-cal-config (see partials/calendar-config.php); nothing user-made is inserted as HTML
 * except the entry description, which the server sanitised and which is filtered again here. No inline script, no external library.
 * Docs: docs/CALENDAR.md. The markup builders below mirror partials/calendar-grid, -pill, -item and -agenda: keep them in step.
 */
(function () {
    'use strict';

    var doc = document;
    var html = doc.documentElement;
    var SVG_NS = 'http://www.w3.org/2000/svg';
    var CAN_FETCH = typeof window.fetch === 'function' && typeof window.Promise === 'function';
    var CAN_DIALOG = typeof window.HTMLDialogElement === 'function' && typeof doc.createElement('dialog').showModal === 'function';
    var CACHE_MS = 5 * 60 * 1000;

    // inner markup of the few icons this script draws itself (same paths as App\Core\Icons; the type icons are cloned from the key)
    var UI_ICONS = {
        'x': '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'chevron-left': '<polyline points="15 18 9 12 15 6"/>',
        'calendar': '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'clock': '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'map-pin': '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'info': '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'external-link': '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'copy': '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'send': '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
        'arrow-right': '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>'
    };

    // ================================================================================================ small helpers
    function $(sel, root) { return (root || doc).querySelector(sel); }
    function $$(sel, root) { return Array.prototype.slice.call((root || doc).querySelectorAll(sel)); }

    /** el('div', {class: 'x', 'data-a': '1'}, [child, 'text']) */
    function el(tag, attrs, kids) {
        var n = doc.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                if (attrs[k] !== null && attrs[k] !== undefined && attrs[k] !== false) { n.setAttribute(k, attrs[k] === true ? '' : attrs[k]); }
            });
        }
        (kids || []).forEach(function (c) {
            if (c === null || c === undefined || c === false) { return; }
            n.appendChild(typeof c === 'string' ? doc.createTextNode(c) : c);
        });
        return n;
    }
    function icon(name, cls) {
        var s = doc.createElementNS(SVG_NS, 'svg');
        s.setAttribute('class', 'icon icon--' + name + (cls ? ' ' + cls : ''));
        s.setAttribute('viewBox', '0 0 24 24');
        s.setAttribute('width', '24');
        s.setAttribute('height', '24');
        s.setAttribute('fill', 'none');
        s.setAttribute('stroke', 'currentColor');
        s.setAttribute('stroke-width', '2');
        s.setAttribute('stroke-linecap', 'round');
        s.setAttribute('stroke-linejoin', 'round');
        s.setAttribute('aria-hidden', 'true');
        s.setAttribute('focusable', 'false');
        s.innerHTML = UI_ICONS[name] || '<circle cx="12" cy="12" r="10"/>';      // static markup from this file, never user input
        return s;
    }
    /** The icon of a calendar type: a copy of the one printed in the key (the server knows the icon names, this script does not). */
    function typeIcon(slug, cls) {
        var src = $('.cal-legend__item[data-type="' + String(slug).replace(/[^a-z0-9-]/gi, '') + '"] .cal-chip__icon');
        if (!src) { return icon('calendar', cls); }
        var c = src.cloneNode(true);
        c.setAttribute('class', 'icon icon--sm ' + (cls || ''));
        return c;
    }
    function tpl(str, vars) {
        return String(str || '').replace(/\{(\w+)\}/g, function (m, k) { return vars && vars[k] !== undefined ? vars[k] : m; });
    }
    function pad(n) { return n < 10 ? '0' + n : String(n); }
    function safeUrl(u) { return /^https?:\/\//i.test(u || '') ? u : ''; }
    function sameSet(a, b) {
        return a.length === b.length && a.every(function (x) { return b.indexOf(x) !== -1; });
    }

    // ---- dates (civil dates 'YYYY-MM-DD': no time zone, no daylight saving; the server's own arithmetic)
    function parseYmd(s) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s || '');
        return m ? { y: +m[1], m: +m[2], d: +m[3] } : null;
    }
    function toDays(s) { var p = parseYmd(s); return Math.floor(Date.UTC(p.y, p.m - 1, p.d) / 86400000); }
    function fromDays(n) { var x = new Date(n * 86400000); return x.getUTCFullYear() + '-' + pad(x.getUTCMonth() + 1) + '-' + pad(x.getUTCDate()); }
    function addDays(s, n) { return fromDays(toDays(s) + n); }
    function isoWeekday(s) { var w = new Date(toDays(s) * 86400000).getUTCDay(); return w === 0 ? 7 : w; }
    function monthOf(s) { return s.slice(0, 7); }
    function addMonths(m, n) {
        var y = +m.slice(0, 4), mo = +m.slice(5, 7) - 1 + n;
        y += Math.floor(mo / 12);
        mo = ((mo % 12) + 12) % 12;
        return y + '-' + pad(mo + 1);
    }
    function validMonth(m) {
        var p = /^(\d{4})-(\d{2})$/.exec(m || '');
        return !!p && +p[1] >= 1901 && +p[1] <= 2199 && +p[2] >= 1 && +p[2] <= 12;
    }
    function monthEnd(m) { return addDays(addMonths(m, 1) + '-01', -1); }

    // ================================================================================================ shared state
    var feedCache = {};                   // 'month|types' or 'a..b|types' -> { at, data }
    var dlg = null;                       // the one dialog
    var dlgCtx = null;                    // the calendar instance that opened it

    // ================================================================================================ a calendar on the page
    function Calendar(root, cfg) {
        var self = this;
        this.root = root;
        this.cfg = cfg;
        this.L = cfg.labels || {};
        this.kind = cfg.kind;                                   // 'page' | 'embed'
        this.view = cfg.view || 'month';
        this.month = cfg.month;
        this.checked = (cfg.checked || []).slice();
        this.today = cfg.today;
        this.todayMonth = monthOf(cfg.today);
        this.data = null;                                       // the feed response of the month on screen
        this.token = 0;
        this.abort = null;
        this.grid = $('[data-cal-grid]', root);
        this.titleEl = $('[data-cal-title]', root);
        this.prev = $('[data-cal-prev]', root);
        this.next = $('[data-cal-next]', root);
        this.todayBtn = $('[data-cal-today]', root);
        this.status = $('[data-cal-status]', root);
        this.agenda = $('[data-cal-agenda]', root);
        this.empty = $('[data-cal-empty]', root);
        this.monthEl = $('[data-cal-month]', root) || $('.cal-embed__main', root) || $('[data-cal-list]', root);
        this.jump = $('[data-cal-jump]', root);
        this.filters = $('[data-cal-filters]', root);
        this.focusDate = null;
        this.cur = null;                                        // roving-tabindex date

        root.classList.add('cal-ready');
        this.bindClicks();
        if (this.view === 'upcoming' || this.view === 'podcast') {
            this.bindFilters(false);                            // the list is server-made: a change reloads the page
            return;
        }
        this.initGrid();
        if (CAN_FETCH) {
            this.bindNav();
            this.bindFilters(true);
            this.bindSwipe();
            if (this.kind === 'page') {
                window.addEventListener('popstate', function (e) {
                    if (e.state && e.state.calMonth) {
                        self.checked = e.state.calChecked.slice();
                        self.syncChips();
                        self.go(e.state.calMonth, { history: false });
                    }
                });
                try { history.replaceState({ calMonth: this.month, calChecked: this.checked.slice() }, '', location.href); } catch (e) { /* ignore */ }
            }
            // warm the cache when the visitor first reaches for the calendar (pointer, touch or keyboard), so the first click on a date opens its
            // panel at once; a visitor who only reads the page costs no extra request (one cached GET, the same the next month change would make)
            var warm = function () {
                ['pointerover', 'touchstart', 'focusin'].forEach(function (t) { root.removeEventListener(t, warm); });
                self.load(self.month).then(function (d) { if (!self.data) { self.data = d; } }, function () { /* the links still work */ });
            };
            ['pointerover', 'touchstart', 'focusin'].forEach(function (t) { root.addEventListener(t, warm, { passive: true }); });
        }
    }

    // ------------------------------------------------------------------ feed
    Calendar.prototype.feedTypes = function () {
        var all = this.cfg.allTypes || [];
        if (this.kind === 'embed') { return this.checked.length ? this.checked : null; }
        if (!this.checked.length) { return ['-none-']; }
        return all.length && this.checked.length === all.length ? null : this.checked;
    };
    Calendar.prototype.load = function (month) {
        var self = this;
        var types = this.feedTypes();
        var key = month + '|' + (types ? types.join(',') : '');
        var hit = feedCache[key];
        if (hit && Date.now() - hit.at < CACHE_MS) { return Promise.resolve(hit.data); }
        if (this.abort && this.abort.abort) { this.abort.abort(); }
        this.abort = typeof AbortController === 'function' ? new AbortController() : null;
        // ask for exactly the days of the grid on screen (not ?month=, whose span follows the server's week-start setting at the time of the answer)
        var range = this.gridRange(month);
        var url = this.cfg.feed + '?start=' + range.start + '&end=' + range.end + (types ? '&types=' + encodeURIComponent(types.join(',')) : '');
        return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: this.abort ? this.abort.signal : undefined })
            .then(function (r) { if (!r.ok) { throw new Error('http ' + r.status); } return r.json(); })
            .then(function (data) {
                if (!data || !Array.isArray(data.items)) { throw new Error('bad feed'); }
                feedCache[key] = { at: Date.now(), data: data };
                return data;
            });
    };

    // ------------------------------------------------------------------ changing month / filter
    /**
     * opts: history (true = pushState, false = none, 'replace'), focus ('title' | 'date' | undefined = leave focus where it is), date.
     */
    Calendar.prototype.go = function (month, opts) {
        var self = this;
        opts = opts || {};
        if (!validMonth(month)) { return; }
        var token = ++this.token;
        var previous = this.month;
        this.month = month;
        this.busy(true);
        this.announce(this.L.loading);
        var types = this.feedTypes();
        var p = types && types[0] === '-none-' ? Promise.resolve({ range: this.gridRange(month), items: [], types: [] }) : this.load(month);
        return p.then(function (data) {
            if (token !== self.token) { return; }
            self.data = data;
            self.render(data);
            self.setTitle();
            self.busy(false);
            self.clearError();
            if (self.kind === 'page' && opts.history !== false) { self.pushState(opts.history === 'replace'); }
            var count = self.monthItems(data).length;
            self.announce(tpl(count === 1 && self.L.announce_one ? self.L.announce_one : self.L.announce, { month: self.monthLabel(month), count: count }));
            if (opts.focus === 'title' && self.titleEl) { self.titleEl.focus(); }
            if (opts.focus === 'date') { self.focusOn(opts.date); }
            self.focusDate = null;
        }).catch(function (err) {
            if (token !== self.token || (err && err.name === 'AbortError')) { return; }
            self.month = previous;
            self.busy(false);
            self.showError();
        });
    };
    /** First and last day of the grid of a month (the same span the feed returns for ?month=). */
    Calendar.prototype.gridRange = function (month) {
        var ws = this.cfg.weekStart, first = month + '-01', last = monthEnd(month);
        return { start: addDays(first, -((isoWeekday(first) - ws + 7) % 7)), end: addDays(last, 6 - ((isoWeekday(last) - ws + 7) % 7)) };
    };
    Calendar.prototype.busy = function (on) {
        if (this.monthEl) { this.monthEl.classList.toggle('is-loading', on); if (on) { this.monthEl.setAttribute('aria-busy', 'true'); } else { this.monthEl.removeAttribute('aria-busy'); } }
    };
    Calendar.prototype.announce = function (text) {
        var s = this.status;
        if (!s || !text) { return; }
        s.textContent = '';
        setTimeout(function () { s.textContent = text; }, 40);          // an identical text is announced again
    };
    Calendar.prototype.showError = function () {
        this.clearError();
        var box = el('p', { class: 'cal-empty cal-empty--grid cal-error', role: 'alert' }, [this.L.error || '']);
        var host = this.monthEl;
        if (host && host.parentNode) { host.parentNode.insertBefore(box, host); }
    };
    Calendar.prototype.clearError = function () {
        $$('.cal-error', this.root).forEach(function (n) { n.parentNode.removeChild(n); });
    };
    Calendar.prototype.monthLabel = function (m) { return this.cfg.months[+m.slice(5, 7) - 1] + ' ' + m.slice(0, 4); };
    Calendar.prototype.monthItems = function (data) {
        var first = this.month + '-01', last = monthEnd(this.month);
        return data.items.filter(function (it) { return it.start_date <= last && it.end_date >= first; });
    };

    // ------------------------------------------------------------------ URL of the page (mirrors CalendarController / index.php)
    Calendar.prototype.typesParam = function () {
        var all = this.cfg.allTypes || [], def = this.cfg.defaults || [];
        if (sameSet(this.checked, def)) { return null; }
        if (!this.checked.length) { return 'none'; }
        if (this.checked.length === all.length) { return 'all'; }
        return this.checked.join(',');
    };
    Calendar.prototype.pageUrl = function (over) {
        over = over || {};
        var m = over.month || this.month;
        var v = over.view || this.view;
        var t = over.types === undefined ? this.typesParam() : over.types;
        var q = [];
        if (m !== this.todayMonth) { q.push('month=' + m); }
        if (v !== 'month') { q.push('view=' + v); }
        if (t !== null) { q.push('types=' + encodeURIComponent(t)); }
        return this.cfg.base + (q.length ? '?' + q.join('&') : '');
    };
    // the tab title and the history entries name the month shown (the same words as the month pages the server renders: seo.month_title)
    Calendar.prototype.setTitle = function () {
        if (this.kind !== 'page' || !this.L.title_month || this.view === 'upcoming' || this.view === 'podcast') { return; }
        if (this.titleSuffix === undefined) {
            var m = /\s\|\s[^|]*$/.exec(document.title);
            this.titleSuffix = m ? m[0] : '';
        }
        document.title = tpl(this.L.title_month, { month: this.monthLabel(this.month) }) + this.titleSuffix;
    };
    Calendar.prototype.pushState = function (replace) {
        try {
            var st = { calMonth: this.month, calChecked: this.checked.slice() };
            if (replace) { history.replaceState(st, '', this.pageUrl()); } else { history.pushState(st, '', this.pageUrl()); }
        } catch (e) { /* ignore: the address bar just stays as it was */ }
    };

    // ------------------------------------------------------------------ render the month into the existing markup
    Calendar.prototype.render = function (data) {
        var self = this;
        var m = this.month;
        var label = this.monthLabel(m);
        if (this.titleEl) { this.titleEl.textContent = label; }
        if (this.prev) { this.prev.href = this.navHref(addMonths(m, -1)); }
        if (this.next) { this.next.href = this.navHref(addMonths(m, 1)); }
        if (this.todayBtn) { this.todayBtn.href = this.pageUrl({ month: this.todayMonth }); }
        if (this.kind === 'page') {
            $$('[data-cal-tab]', this.root).forEach(function (a) { a.href = self.pageUrl({ view: a.getAttribute('data-cal-tab') }); });
            var all = $('[data-cal-all]', this.root);
            if (all) { all.href = this.pageUrl({ types: 'all' }); all.hidden = this.checked.length === (this.cfg.allTypes || []).length; }
            if (this.jump) {
                var sel = $('[data-cal-jump-month]', this.jump), yr = $('[data-cal-jump-year]', this.jump);
                if (sel) { sel.value = String(+m.slice(5, 7)); }
                if (yr) { yr.value = m.slice(0, 4); }
                var t = $('input[name="types"]', this.jump), tp = this.typesParam();
                if (tp === null && t) { t.parentNode.removeChild(t); } else if (tp !== null) {
                    if (!t) { t = el('input', { type: 'hidden', name: 'types' }); this.jump.insertBefore(t, this.jump.firstChild); }
                    t.value = tp;
                }
            }
            if (this.filters) {                                                // the no-JS filter form keeps month / types in step too
                var hm = $('input[name="month"]', this.filters);
                if (m !== this.todayMonth) {
                    if (!hm) { hm = el('input', { type: 'hidden', name: 'month' }); this.filters.insertBefore(hm, this.filters.firstChild); }
                    hm.value = m;
                } else if (hm) { hm.parentNode.removeChild(hm); }
            }
        }
        if (this.grid) { this.renderGrid(data); }
        if (this.agenda) { this.renderAgenda(data); }
        if (this.empty) {
            var n = this.monthItems(data).length;
            this.empty.hidden = n > 0;
            if (n === 0) {
                var allOn = this.checked.length === (this.cfg.allTypes || []).length;
                this.empty.innerHTML = '';
                this.empty.appendChild(el('strong', null, [tpl(this.L.empty_month, { month: label })]));
                this.empty.appendChild(el('span', null, [allOn ? this.L.month_empty_text : this.L.filtered]));
            }
        }
    };
    Calendar.prototype.navHref = function (month) {
        if (this.kind === 'embed') {
            var a = this.prev || this.next;
            return a ? a.getAttribute('href').replace(/cal=\d{4}-\d{2}/, 'cal=' + month) : '#';
        }
        return this.pageUrl({ month: month });
    };

    // ---- the grid (same markup as partials/calendar-grid + calendar-pill)
    function kindOf(it) { return it.is_holiday ? 'holiday' : (it.type === 'programme' || it.type === 'event' ? 'own' : 'obs'); }
    function setVars(node, it) {
        node.style.setProperty('--cal-solid', it.color);
        node.style.setProperty('--cal-tint', it.tint);
        node.style.setProperty('--cal-text', it.text_color);
        node.style.setProperty('--cal-on', it.on_color);
    }
    Calendar.prototype.pill = function (it, compact) {
        var L = this.L;
        var a = el('a', {
            class: 'cal-pill cal-pill--' + kindOf(it) + (it.needs_confirmation ? ' cal-pill--tbc' : ''),
            href: it.url, 'data-cal-key': it.key, 'data-cal-date': it.start_date, 'data-cal-type': it.type, title: it.title
        });
        setVars(a, it);
        a.appendChild(typeIcon(it.type, 'icon--sm cal-pill__icon'));
        var text = el('span', { class: 'cal-pill__text' });
        if (!compact && !it.all_day && it.start_time) { text.appendChild(el('span', { class: 'cal-pill__time' }, [it.start_time])); text.appendChild(doc.createTextNode(' ')); }
        text.appendChild(doc.createTextNode(it.title));
        a.appendChild(text);
        if (it.is_holiday) { a.appendChild(el('span', { class: 'visually-hidden' }, [' (' + L.holiday + ')'])); }
        if (it.needs_confirmation) {
            a.appendChild(compact ? el('span', { class: 'visually-hidden' }, [' (' + L.tbc + ')']) : el('span', { class: 'cal-pill__flag' }, [L.tbc]));
        }
        return a;
    };
    Calendar.prototype.moreHref = function (date) {
        return this.kind === 'embed'
            ? this.cfg.base + '?month=' + monthOf(date) + '&view=list#d-' + date
            : this.pageUrl({ month: monthOf(date), view: 'list' }) + '#d-' + date;
    };
    Calendar.prototype.renderGrid = function (data) {
        var self = this, L = this.L, g = this.grid;
        var compact = g.classList.contains('cal-grid--compact');
        var max = parseInt(g.getAttribute('data-max'), 10) || 3;
        var gr = this.gridRange(this.month), start = gr.start, end = gr.end;
        var byDay = {};
        data.items.forEach(function (it) {
            for (var d = toDays(it.start_date < start ? start : it.start_date), e = toDays(it.end_date > end ? end : it.end_date); d <= e; d++) {
                var k = fromDays(d);
                (byDay[k] = byDay[k] || []).push(it);
            }
        });
        var label = this.monthLabel(this.month);
        g.setAttribute('data-month', this.month);
        g.setAttribute('aria-label', tpl(L.grid, { month: label }));
        var cap = $('caption', g);
        if (cap) { cap.textContent = label + '. ' + (cap.getAttribute('data-hint') || ''); }
        var body = $('tbody', g);
        body.innerHTML = '';
        var total = toDays(end) - toDays(start) + 1;
        var tr = null;
        for (var i = 0; i < total; i++) {
            var date = addDays(start, i);
            if (i % 7 === 0) { tr = el('tr'); body.appendChild(tr); }
            var list = byDay[date] || [];
            var wd = isoWeekday(date);
            var inMonth = monthOf(date) === this.month;
            var isToday = date === this.today;
            var hol = list.some(function (x) { return x.is_holiday; });
            var cls = 'cal-day' + (inMonth ? '' : ' cal-day--out') + (isToday ? ' is-today' : '') + (wd >= 6 ? ' cal-day--weekend' : '') + (hol ? ' cal-day--holiday' : '') + (list.length ? ' has-items' : '');
            var lab = this.longDate(date);
            var td = el('td', { class: cls, 'data-date': date, 'aria-current': isToday ? 'date' : null });
            td.appendChild(el('span', { class: 'cal-day__num' }, [
                el('span', { 'aria-hidden': 'true' }, [String(+date.slice(8, 10))]),
                el('span', { class: 'visually-hidden' }, [list.length ? tpl(list.length === 1 && L.day_one ? L.day_one : L.day_items, { date: lab, count: list.length }) : lab])
            ]));
            if (list.length) {
                var ul = el('ul', { class: 'cal-day__list' });
                list.slice(0, max).forEach(function (it) { ul.appendChild(el('li', null, [self.pill(it, compact)])); });
                if (list.length > max) {
                    var more = tpl(L.more, { n: list.length - max });
                    ul.appendChild(el('li', null, [el('a', { class: 'cal-more', href: this.moreHref(date), 'data-cal-more': '' },
                        compact ? [el('span', { 'aria-hidden': 'true' }, ['+' + (list.length - max)]), el('span', { class: 'visually-hidden' }, [more])] : [more])]));
                }
                td.appendChild(ul);
            }
            tr.appendChild(td);
        }
        this.initGrid(true);
    };

    // ---- the agenda list (same markup as partials/calendar-agenda + calendar-item)
    Calendar.prototype.timeLabel = function (it) {
        if (it.all_day || !it.start_time) { return ''; }
        return it.start_time + (it.end_time ? ' ' + this.L.to + ' ' + it.end_time : '');
    };
    Calendar.prototype.longDate = function (s) {
        var p = parseYmd(s);
        return this.cfg.weekdays[isoWeekday(s) - 1] + ' ' + p.d + ' ' + this.cfg.months[p.m - 1] + ' ' + p.y;
    };
    Calendar.prototype.rangeLabel = function (it) {
        if (it.start_date === it.end_date) { return this.longDate(it.start_date); }
        var a = parseYmd(it.start_date), b = parseYmd(it.end_date), M = this.cfg.months;
        return a.d + ' ' + M[a.m - 1] + ' ' + a.y + ' ' + this.L.to + ' ' + b.d + ' ' + M[b.m - 1] + ' ' + b.y;
    };
    Calendar.prototype.itemRow = function (it, withSummary) {
        var L = this.L;
        var multi = it.start_date !== it.end_date;
        var time = this.timeLabel(it);
        var row = el('div', { class: 'cal-item cal-item--' + kindOf(it) + (it.needs_confirmation ? ' cal-item--tbc' : '') });
        setVars(row, it);
        row.appendChild(el('span', { class: 'cal-item__chip', 'aria-hidden': 'true' }, [typeIcon(it.type, 'icon--sm')]));
        var body = el('div', { class: 'cal-item__body' });
        body.appendChild(el('p', { class: 'cal-item__title' }, [el('a', { class: 'cal-item__link', href: it.url, 'data-cal-key': it.key, 'data-cal-date': it.start_date, 'data-cal-type': it.type }, [it.title])]));
        var meta = el('p', { class: 'cal-item__meta' }, [el('span', { class: 'cal-item__type' }, [it.type_name]), el('span', null, [multi ? this.rangeLabel(it) : (time || L.all_day)])]);
        if (it.location) { meta.appendChild(el('span', null, [it.location])); }
        body.appendChild(meta);
        if (it.is_holiday || it.needs_confirmation || it.moved) {
            var f = el('p', { class: 'cal-item__flags' });
            if (it.is_holiday) { f.appendChild(el('span', { class: 'badge badge--primary' }, [L.holiday])); }
            if (it.needs_confirmation) { f.appendChild(el('span', { class: 'badge badge--warning' }, [L.tbc])); }
            if (it.moved) { f.appendChild(el('span', { class: 'badge badge--info' }, [L.moved])); }
            body.appendChild(f);
        }
        if (withSummary && it.summary) {
            var t = it.summary.length > 150 ? it.summary.slice(0, 149).replace(/\s+$/, '') + '…' : it.summary;
            body.appendChild(el('p', { class: 'cal-item__summary' }, [t]));
        }
        row.appendChild(body);
        return row;
    };
    Calendar.prototype.groupByDay = function (items, from, to) {
        var days = {};
        items.forEach(function (it) {
            if (it.start_date > to || it.end_date < from) { return; }
            var d = it.start_date < from ? from : it.start_date;
            (days[d] = days[d] || []).push(it);
        });
        return Object.keys(days).sort().map(function (d) { return { date: d, items: days[d] }; });
    };
    Calendar.prototype.renderAgenda = function (data) {
        var self = this;
        var withSummary = this.agenda.hasAttribute('data-cal-list');
        var days = this.groupByDay(data.items, this.month + '-01', monthEnd(this.month));
        this.agenda.innerHTML = '';
        if (!days.length) {
            var allOn = this.checked.length === (this.cfg.allTypes || []).length;
            this.agenda.appendChild(el('p', { class: 'cal-empty' }, [tpl(this.L.empty_month, { month: this.monthLabel(this.month) }) + (withSummary ? ' ' + (allOn ? this.L.month_empty_text : this.L.filtered) : '')]));
            return;
        }
        var ol = el('ol', { class: 'cal-agenda' });
        days.forEach(function (g) {
            var p = parseYmd(g.date);
            var li = el('li', { class: 'cal-agenda__day' + (g.date === self.today ? ' is-today' : ''), id: 'd-' + g.date, 'aria-current': g.date === self.today ? 'date' : null });
            li.appendChild(el('h3', { class: 'cal-agenda__date' }, [
                el('span', { class: 'cal-agenda__badge', 'aria-hidden': 'true' }, [
                    el('span', { class: 'cal-agenda__dow' }, [self.cfg.weekdays[isoWeekday(g.date) - 1].slice(0, 3)]),
                    el('span', { class: 'cal-agenda__num' }, [String(p.d)]),
                    el('span', { class: 'cal-agenda__mon' }, [self.cfg.months[p.m - 1].slice(0, 3)])
                ]),
                el('span', { class: 'visually-hidden' }, [(g.date === self.today && self.L.today ? self.L.today + ': ' : '') + self.longDate(g.date)])
            ]));
            var ul = el('ul', { class: 'cal-agenda__list' });
            g.items.forEach(function (it) { ul.appendChild(el('li', null, [self.itemRow(it, withSummary)])); });
            li.appendChild(ul);
            ol.appendChild(li);
        });
        this.agenda.appendChild(ol);
    };

    // ------------------------------------------------------------------ controls
    Calendar.prototype.bindNav = function () {
        var self = this;
        function plain(e) { return e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey; }
        if (this.prev) { this.prev.addEventListener('click', function (e) { if (plain(e)) { e.preventDefault(); self.go(addMonths(self.month, -1)); } }); }
        if (this.next) { this.next.addEventListener('click', function (e) { if (plain(e)) { e.preventDefault(); self.go(addMonths(self.month, 1)); } }); }
        if (this.todayBtn) { this.todayBtn.addEventListener('click', function (e) { if (plain(e)) { e.preventDefault(); self.go(self.todayMonth); } }); }
        if (this.jump) {
            this.jump.addEventListener('submit', function (e) {
                var mo = $('[data-cal-jump-month]', self.jump), yr = $('[data-cal-jump-year]', self.jump);
                var y = parseInt(yr && yr.value, 10), n = parseInt(mo && mo.value, 10);
                var target = y + '-' + pad(n);
                if (!y || !n || !validMonth(target)) { return; }          // let the server answer
                e.preventDefault();
                self.go(target, { focus: 'title' });
            });
        }
    };
    Calendar.prototype.bindFilters = function (inPlace) {
        var self = this;
        if (!this.filters) { return; }
        this.filters.addEventListener('change', function (e) {
            if (!e.target || !e.target.matches('[data-cal-type-toggle]')) { return; }
            if (!inPlace) { self.filters.submit(); return; }
            self.checked = $$('[data-cal-type-toggle]', self.filters).filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
            self.go(self.month, { history: 'replace' });
        });
        var all = $('[data-cal-all]', this.filters);
        if (all && inPlace) {
            all.addEventListener('click', function (e) {
                e.preventDefault();
                self.checked = (self.cfg.allTypes || []).slice();
                self.syncChips();
                self.go(self.month, { history: 'replace' });
            });
        }
    };
    Calendar.prototype.syncChips = function () {
        var set = this.checked;
        $$('[data-cal-type-toggle]', this.root).forEach(function (c) { c.checked = set.indexOf(c.value) !== -1; });
    };
    Calendar.prototype.bindSwipe = function () {
        var self = this, host = this.monthEl, x0 = 0, y0 = 0, t0 = 0, on = false;
        if (!host || !('ontouchstart' in window)) { return; }
        host.addEventListener('touchstart', function (e) {
            on = e.touches.length === 1;
            if (on) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; t0 = Date.now(); }
        }, { passive: true });
        host.addEventListener('touchend', function (e) {
            if (!on || !e.changedTouches.length) { return; }
            on = false;
            var dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0;
            if (Math.abs(dx) > 70 && Math.abs(dx) > 1.8 * Math.abs(dy) && Date.now() - t0 < 700 && !(dlg && dlg.open)) {
                self.go(addMonths(self.month, dx < 0 ? 1 : -1));
            }
        }, { passive: true });
    };

    // ------------------------------------------------------------------ keyboard grid
    Calendar.prototype.cells = function () { return this.grid ? $$('td[data-date]', this.grid) : []; };
    Calendar.prototype.initGrid = function (rebuilt) {
        var self = this;
        var g = this.grid;
        if (!g) { return; }
        g.setAttribute('role', 'grid');
        $$('tr', g).forEach(function (r) { r.setAttribute('role', 'row'); });
        $$('thead th', g).forEach(function (h) { h.setAttribute('role', 'columnheader'); });
        var cells = this.cells();
        if (!cells.length) { return; }
        var want = this.focusDate && cells.some(function (c) { return c.getAttribute('data-date') === self.focusDate; }) ? this.focusDate : null;
        if (!want) {
            var t = cells.filter(function (c) { return c.getAttribute('aria-current') === 'date'; })[0];
            if (!t) { t = cells.filter(function (c) { return !c.classList.contains('cal-day--out'); })[0]; }
            want = t.getAttribute('data-date');
        }
        this.cur = want;
        cells.forEach(function (c) {
            c.setAttribute('role', 'gridcell');
            c.tabIndex = c.getAttribute('data-date') === want ? 0 : -1;
            if (CAN_DIALOG) { $$('a', c).forEach(function (a) { a.tabIndex = -1; }); }   // one tab stop: Enter opens the day, whose list holds every link
        });
        if (!rebuilt && !this.gridBound) {
            this.gridBound = true;
            g.addEventListener('focusin', function (e) {
                var td = e.target.closest ? e.target.closest('td[data-date]') : null;
                if (td && td.getAttribute('data-date') !== self.cur) { self.setCur(td.getAttribute('data-date')); }
            });
            g.addEventListener('keydown', function (e) { self.onKey(e); });
        }
    };
    Calendar.prototype.setCur = function (date) {
        this.cells().forEach(function (c) { c.tabIndex = c.getAttribute('data-date') === date ? 0 : -1; });
        this.cur = date;
    };
    Calendar.prototype.cellFor = function (date) {
        return this.cells().filter(function (c) { return c.getAttribute('data-date') === date; })[0] || null;
    };
    /** Focus a day: a cell of the grid on screen, or another month (loaded first, then the day is focused). */
    Calendar.prototype.focusOn = function (date) {
        var c = this.cellFor(date);
        if (!c) { return; }
        this.setCur(date);
        c.focus();
    };
    Calendar.prototype.moveTo = function (date) {
        if (this.cellFor(date)) { this.focusOn(date); return; }
        if (!CAN_FETCH) { return; }
        this.focusDate = date;
        this.go(monthOf(date), { focus: 'date', date: date });
    };
    Calendar.prototype.onKey = function (e) {
        var td = e.target && e.target.closest ? e.target.closest('td[data-date]') : null;
        if (!td || e.target !== td || e.altKey || e.ctrlKey || e.metaKey) { return; }
        var date = td.getAttribute('data-date');
        var ws = this.cfg.weekStart;
        var off = (isoWeekday(date) - ws + 7) % 7;
        var target = null;
        switch (e.key) {
            case 'ArrowLeft': target = addDays(date, -1); break;
            case 'ArrowRight': target = addDays(date, 1); break;
            case 'ArrowUp': target = addDays(date, -7); break;
            case 'ArrowDown': target = addDays(date, 7); break;
            case 'Home': target = addDays(date, -off); break;
            case 'End': target = addDays(date, 6 - off); break;
            case 'PageUp': case 'PageDown':
                var step = (e.key === 'PageUp' ? -1 : 1) * (e.shiftKey ? 12 : 1);
                var nm = addMonths(monthOf(date), step), p = parseYmd(date);
                var dim = +monthEnd(nm).slice(8, 10);
                target = nm + '-' + pad(Math.min(p.d, dim));
                break;
            case 'Enter': case ' ':
                e.preventDefault();
                if (CAN_DIALOG) { this.openDay(date, td); } else { var first = $('a', td); if (first) { first.click(); } }
                return;
            default: return;
        }
        e.preventDefault();
        if (validMonth(monthOf(target))) { this.moveTo(target); }
    };

    // ------------------------------------------------------------------ clicks: entries, "+N more", days
    Calendar.prototype.bindClicks = function () {
        var self = this;
        if (!CAN_DIALOG) { return; }
        this.root.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
            var t = e.target;
            var a = t.closest ? t.closest('a[data-cal-key]') : null;
            if (a && self.root.contains(a)) {
                e.preventDefault();
                self.openItem(a.getAttribute('data-cal-key'), a.getAttribute('data-cal-date'), a, null);
                return;
            }
            var more = t.closest ? t.closest('a[data-cal-more]') : null;
            var td = t.closest ? t.closest('td[data-date]') : null;
            if (more && td) {
                e.preventDefault();
                self.openDay(td.getAttribute('data-date'), more);
                return;
            }
            if (td && td.classList.contains('has-items') && !(t.closest && t.closest('a'))) {
                self.openDay(td.getAttribute('data-date'), td);
            }
        });
    };
    /** The data of the month on screen (loads it when the first click comes before the warm-up finished). */
    Calendar.prototype.current = function () {
        var self = this;
        if (this.data) { return Promise.resolve(this.data); }
        return this.load(this.month).then(function (d) { self.data = d; return d; });
    };
    Calendar.prototype.itemsOn = function (date) {
        var data = this.data;
        var order = {};
        (data.types || []).forEach(function (t, i) { order[t.slug] = i; });
        return data.items.filter(function (it) { return it.start_date <= date && it.end_date >= date; }).sort(function (a, b) {
            return (a.all_day ? 0 : 1) - (b.all_day ? 0 : 1) || String(a.start_time || '').localeCompare(String(b.start_time || '')) ||
                (order[a.type] || 0) - (order[b.type] || 0) || a.title.localeCompare(b.title);
        });
    };
    Calendar.prototype.openDay = function (date, opener) {
        var self = this;
        this.current().then(function () { openDialog(self, 'day', { date: date, items: self.itemsOn(date) }, opener); },
            function () { var l = $('a[data-cal-more]', self.cellFor(date) || self.root); if (l) { location.href = l.href; } });
    };
    Calendar.prototype.openItem = function (key, date, opener, back) {
        var self = this;
        var fromData = function () {
            var hit = null;
            [self.data].forEach(function (d) { if (d) { d.items.forEach(function (it) { if (it.key === key) { hit = it; } }); } });
            return hit;
        };
        var found = fromData();
        var fallback = function () { if (opener && opener.href) { location.href = opener.href; } };
        if (found) { openDialog(self, 'item', { item: found, back: back }, opener); return; }
        // an entry that is not in the month on screen (the coming-up lists): fetch its day, from the same feed
        if (!CAN_FETCH || !date) { fallback(); return; }
        fetch(this.cfg.feed + '?start=' + date + '&end=' + date, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) { throw new Error('http'); } return r.json(); })
            .then(function (d) {
                var it = (d.items || []).filter(function (x) { return x.key === key; })[0];
                if (!it) { throw new Error('missing'); }
                openDialog(self, 'item', { item: it, back: back }, opener);
            })
            .catch(fallback);
    };

    // ================================================================================================ the dialog (day panel + one entry)
    var safeTags = { P: 1, BR: 1, STRONG: 1, B: 1, EM: 1, I: 1, U: 1, UL: 1, OL: 1, LI: 1, A: 1, H2: 1, H3: 1, H4: 1, BLOCKQUOTE: 1, SPAN: 1, DIV: 1, SMALL: 1, SUB: 1, SUP: 1, HR: 1, IMG: 1, CODE: 1 };
    /** The description is already sanitised by the server; filter it once more (allow-list of tags and attributes) before it goes into the page. */
    function safeFragment(markup) {
        var frag = doc.createDocumentFragment();
        var parsed = new DOMParser().parseFromString('<div>' + markup + '</div>', 'text/html');
        function walk(src, out) {
            Array.prototype.slice.call(src.childNodes).forEach(function (n) {
                if (n.nodeType === 3) { out.appendChild(doc.createTextNode(n.nodeValue)); return; }
                if (n.nodeType !== 1) { return; }
                if (!safeTags[n.tagName]) {
                    if (/^(SCRIPT|STYLE|IFRAME|OBJECT|EMBED|SVG|MATH|FORM|INPUT|BUTTON|TEXTAREA|SELECT|LINK|META)$/.test(n.tagName)) { return; }
                    walk(n, out);                                     // unknown element: keep its text
                    return;
                }
                var c = doc.createElement(n.tagName.toLowerCase());
                if (n.tagName === 'A') {
                    var h = n.getAttribute('href') || '';
                    if (/^(https?:|mailto:|tel:|\/|#)/i.test(h) && !/^\s*javascript:/i.test(h)) {
                        c.setAttribute('href', h);
                        if (/^https?:/i.test(h)) { c.setAttribute('target', '_blank'); c.setAttribute('rel', 'noopener noreferrer'); }
                    }
                }
                if (n.tagName === 'IMG') {
                    var s = n.getAttribute('src') || '';
                    if (!/^(https?:\/\/|\/)/i.test(s)) { return; }
                    c.setAttribute('src', s);
                    c.setAttribute('alt', n.getAttribute('alt') || '');
                    c.setAttribute('loading', 'lazy');
                }
                walk(n, c);
                out.appendChild(c);
            });
        }
        walk(parsed.body.firstChild, frag);
        return frag;
    }

    /** Release the page after the dialog closed: unlock the scroll, give focus back to what opened it (idempotent). */
    function afterClose() {
        if (dlg && dlg.open) { return; }                          // opened again before a late close event arrived
        html.classList.remove('cal-lock');
        var o = dlg ? dlg.calOpener : null;
        if (dlg) { dlg.calOpener = null; }
        if (o && doc.contains(o) && o.focus) { try { o.focus(); } catch (e) { /* ignore */ } }
        else if (o !== null && dlg && dlg.calDate && dlgCtx) { var c = dlgCtx.cellFor(dlg.calDate); if (c) { c.focus(); } }
    }
    function closeDialog() {
        if (dlg && dlg.open) { dlg.close(); }
        afterClose();
    }

    function ensureDialog() {
        if (dlg) { return dlg; }
        dlg = el('dialog', { class: 'cal-dialog', 'aria-labelledby': 'cal-dialog-title' });
        var back = el('button', { type: 'button', class: 'cal-dialog__back', hidden: true, 'data-cal-back': '' });
        var title = el('h2', { class: 'cal-dialog__title', id: 'cal-dialog-title', tabindex: '-1' });
        var close = el('button', { type: 'button', class: 'cal-dialog__close', 'data-cal-close': '' });
        close.appendChild(icon('x'));
        close.appendChild(el('span', { class: 'visually-hidden' }));
        dlg.appendChild(el('div', { class: 'cal-dialog__head' }, [back, title, close]));
        dlg.appendChild(el('div', { class: 'cal-dialog__body', 'data-cal-body': '' }));
        doc.body.appendChild(dlg);
        dlg.addEventListener('click', function (e) {
            if (e.target === dlg || (e.target.closest && e.target.closest('[data-cal-close]'))) { closeDialog(); return; }
            var b = e.target.closest ? e.target.closest('[data-cal-back]') : null;
            if (b && dlg.calBack) { openDialog(dlgCtx, 'day', dlg.calBack, null, true); return; }
            // an entry chosen from the day panel
            var a = e.target.closest ? e.target.closest('a[data-cal-key]') : null;
            if (a && !(e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) && dlgCtx && dlg.calView === 'day') {
                e.preventDefault();
                var ctx = dlgCtx;
                var back2 = dlg.calDay;
                var key = a.getAttribute('data-cal-key');
                var hit = null;
                (back2.items || []).forEach(function (it) { if (it.key === key) { hit = it; } });
                if (hit) { openDialog(ctx, 'item', { item: hit, back: back2 }, null, true); }
                else { location.href = a.href; }
                return;
            }
            var cp = e.target.closest ? e.target.closest('[data-cal-dlg-copy]') : null;
            if (cp) { copyText(cp.getAttribute('data-cal-dlg-copy'), $('[data-cal-dlg-status]', dlg), dlgCtx ? dlgCtx.L.copied : ''); return; }
            var sh = e.target.closest ? e.target.closest('[data-cal-dlg-share]') : null;
            if (sh && navigator.share) { navigator.share({ title: sh.getAttribute('data-title') || '', url: sh.getAttribute('data-cal-dlg-share') }).catch(function () { /* cancelled */ }); }
        });
        // the `close` event can arrive late (a page that is not in front); closing through closeDialog() cleans up at once, the event only catches Esc
        dlg.addEventListener('close', afterClose);
        return dlg;
    }

    /** kind 'day' {date, items} | 'item' {item, back}. keepOpener = switching inside the open dialog. */
    function openDialog(cal, kind, payload, opener, keepOpener) {
        var d = ensureDialog();
        dlgCtx = cal;
        var L = cal.L;
        var title = $('.cal-dialog__title', d), body = $('[data-cal-body]', d), backBtn = $('.cal-dialog__back', d);
        $('.cal-dialog__close .visually-hidden', d).textContent = L.close;
        body.innerHTML = '';
        backBtn.hidden = true;
        d.calView = kind;
        d.calBack = null;
        if (kind === 'day') {
            var label = tpl(L.panel_title, { date: cal.longDate(payload.date) });
            title.textContent = label;
            d.calDay = payload;
            d.calDate = payload.date;
            if (!payload.items.length) { body.appendChild(el('p', { class: 'cal-dialog__empty' }, [L.panel_empty])); }
            else {
                var ul = el('ul', { class: 'cal-dialog__list' });
                payload.items.forEach(function (it) { ul.appendChild(el('li', null, [cal.itemRow(it, false)])); });
                body.appendChild(ul);
            }
        } else {
            var it = payload.item;
            title.textContent = it.title;
            d.calDate = it.start_date;
            if (payload.back) {
                d.calBack = payload.back;
                backBtn.textContent = '';
                backBtn.appendChild(icon('chevron-left', 'icon--sm'));
                backBtn.appendChild(el('span', null, [tpl(L.panel_title, { date: cal.longDate(payload.back.date) })]));
                backBtn.hidden = false;
            }
            fillItem(body, cal, it);
        }
        if (!keepOpener || !d.calOpener) { d.calOpener = opener || doc.activeElement; }
        if (!d.open) {
            html.classList.add('cal-lock');
            d.showModal();
        }
        body.scrollTop = 0;
        title.focus();
    }

    function fact(label, kids) {
        return el('div', null, [el('dt', null, [label]), el('dd', null, kids)]);
    }
    function fillItem(body, cal, it) {
        var L = cal.L;
        var kind = kindOf(it);
        var badges = el('p', { class: 'cal-dialog__badges' });
        var tp = el('span', { class: 'cal-pill cal-pill--' + kind });
        setVars(tp, it);
        tp.appendChild(typeIcon(it.type, 'icon--sm cal-pill__icon'));
        tp.appendChild(el('span', { class: 'cal-pill__text' }, [it.type_name]));
        badges.appendChild(tp);
        if (it.is_holiday) { badges.appendChild(el('span', { class: 'badge badge--primary' }, [L.holiday])); }
        if (it.needs_confirmation) { badges.appendChild(el('span', { class: 'badge badge--warning' }, [L.tbc])); }
        if (it.moved) { badges.appendChild(el('span', { class: 'badge badge--info' }, [L.moved])); }
        body.appendChild(badges);

        var time = cal.timeLabel(it);
        var facts = el('dl', { class: 'cal-dialog__facts' });
        var when = [doc.createTextNode(cal.rangeLabel(it))];
        when.push(el('span', { class: 'cal-dialog__sub' }, [time ? time + ' · ' + L.time_note : L.all_day]));
        facts.appendChild(fact(L.when, when));
        if (it.location) { facts.appendChild(fact(L.where, [it.location])); }
        if (it.source_label) {
            var su = safeUrl(it.source_url);
            facts.appendChild(fact(L.source, [su ? el('a', { href: su, target: '_blank', rel: 'noopener noreferrer' }, [it.source_label]) : it.source_label]));
        }
        body.appendChild(facts);
        if (it.note) { body.appendChild(el('p', { class: 'cal-dialog__note' }, [it.note])); }
        if (it.description_html) {
            var prose = el('div', { class: 'prose cal-dialog__text' });
            prose.appendChild(safeFragment(it.description_html));
            body.appendChild(prose);
        }
        var acts = el('p', { class: 'cal-dialog__actions' });
        var lu = safeUrl(it.link_url);
        if (lu) { acts.appendChild(el('a', { class: 'btn btn--primary btn--sm', href: lu, target: '_blank', rel: 'noopener noreferrer' }, [(it.link_label || L.link) + ' ', icon('external-link', 'icon--sm')])); }
        acts.appendChild(el('a', { class: 'btn btn--outline btn--sm', href: it.url }, [L.about + ' ', icon('arrow-right', 'icon--sm')]));
        if (cal.cfg.share) {
            if (it.ics_url) { acts.appendChild(el('a', { class: 'btn btn--outline btn--sm', href: it.ics_url }, [icon('calendar', 'icon--sm'), ' ' + L.add])); }
            var abs = location.origin + it.url;
            if (navigator.share) { acts.appendChild(el('button', { type: 'button', class: 'btn btn--ghost btn--sm', 'data-cal-dlg-share': abs, 'data-title': it.title }, [icon('send', 'icon--sm'), ' ' + L.share])); }
            acts.appendChild(el('button', { type: 'button', class: 'btn btn--ghost btn--sm', 'data-cal-dlg-copy': abs }, [icon('copy', 'icon--sm'), ' ' + L.copy]));
        }
        body.appendChild(acts);
        body.appendChild(el('p', { class: 'cal-dialog__status', role: 'status', 'aria-live': 'polite', 'data-cal-dlg-status': '' }));
    }

    // ================================================================================================ share / copy / print (any page)
    function copyText(text, statusEl, doneMsg) {
        function say(msg) { if (statusEl) { statusEl.textContent = ''; setTimeout(function () { statusEl.textContent = msg; }, 40); } }
        function done() { say(doneMsg || ''); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done, function () { legacy(); });
        } else { legacy(); }
        function legacy() {
            var ok = false;
            var ta = el('textarea', { readonly: '', 'aria-hidden': 'true' });
            ta.style.cssText = 'position:fixed;left:-9999px;top:0';
            ta.value = text;
            doc.body.appendChild(ta);
            ta.select();
            try { ok = doc.execCommand('copy'); } catch (e) { ok = false; }
            doc.body.removeChild(ta);
            if (ok) { done(); } else { say(text); }                  // the browser refused: show the address so it can be copied by hand
        }
    }
    function bindUtilities() {
        $$('[data-cal-print]').forEach(function (b) { b.hidden = false; b.addEventListener('click', function () { window.print(); }); });
        var canCopy = (navigator.clipboard && navigator.clipboard.writeText) || (doc.queryCommandSupported && doc.queryCommandSupported('copy'));
        var msg = '';
        var cfgNode = $('[data-cal-config]');
        if (cfgNode) { try { msg = (JSON.parse(cfgNode.getAttribute('data-cal-config')).labels || {}).copied || ''; } catch (e) { /* ignore */ } }
        $$('[data-cal-copy]').forEach(function (b) {
            if (!canCopy) { return; }
            b.hidden = false;
            b.addEventListener('click', function () {
                var host = b.closest('.cal-facts, .cal-subscribe') || doc;
                copyText(b.getAttribute('data-cal-copy'), $('[data-cal-copied]', host), msg);
            });
        });
        $$('[data-cal-share]').forEach(function (b) {
            if (!navigator.share) { return; }
            b.hidden = false;
            b.addEventListener('click', function () {
                navigator.share({ title: b.getAttribute('data-title') || '', url: b.getAttribute('data-cal-share') }).catch(function () { /* cancelled */ });
            });
        });
    }

    // ================================================================================================ start
    function init() {
        bindUtilities();
        $$('[data-cal-page], [data-cal-embed]').forEach(function (root) {
            var cfg = null;
            try { cfg = JSON.parse(root.getAttribute('data-cal-config') || ''); } catch (e) { cfg = null; }
            if (cfg && cfg.labels) { new Calendar(root, cfg); }
        });
    }
    if (doc.readyState === 'loading') { doc.addEventListener('DOMContentLoaded', init); } else { init(); }
}());
