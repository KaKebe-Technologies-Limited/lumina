/*!
 * admin-members.js - ADMIN agent (membership). Progressive enhancement only: every screen works without it, the server validates everything.
 * Hooks (all data attributes, no inline JS):
 *   form[data-members-bulk]      bulk bar of the members list: [data-select-all] [data-select-row] [data-bulk-action] [data-bulk-for="approve reject"] [data-selected-count]
 *   form[data-members-status]    review panel of a member: radios [data-status-to], blocks [data-for="approved rejected"], [data-reason], [data-notify],
 *                                dates [data-start-date] [data-expiry-date] + [data-expiry-preview], button [data-status-submit]; data-from, data-reason-for, data-period-months
 *   form[data-members-resend]    [data-resend-template] shows the [data-for="payment"] block only for the payment e-mail
 *   form[data-members-delete]    [data-delete-submit] stays disabled until the application reference is typed
 *   [data-pay-status]            payment status select (the "e-mail a confirmation" tick box only makes sense for received payments)
 *   [data-pay-receive="ID"]      "Mark received" shortcut: opens the edit block of that payment with the status set to Received
 *   [data-use-type-fee='{json}'] copies the current fee of the membership type into the member's own fee fields
 *   [data-dob]                   date of birth input of the edit screen: keeps the age line current
 *   [data-print]                 window.print() on the receipt page
 *   form[data-fee-form]          membership type form: live preview of how the fee reads on the website (data-fee-config, data-period-hints)
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var plural = function (n, one, many) { return n + ' ' + (n === 1 ? one : many); };

    // ------------------------------------------------------------------ dates
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var iso = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };
    var parseIso = function (s) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s || '');
        if (!m) return null;
        var d = new Date(+m[1], +m[2] - 1, +m[3]);
        return d.getFullYear() === +m[1] && d.getMonth() === +m[2] - 1 && d.getDate() === +m[3] ? d : null;
    };
    // same rule as MembershipService::computeExpiry: add months, a month end is clamped (31 Jan + 1 month = 28 Feb)
    var addMonths = function (d, months) {
        var y = d.getFullYear(), m = d.getMonth() + months, day = d.getDate();
        var last = new Date(y, m + 1, 0).getDate();
        return new Date(y, m, Math.min(day, last));
    };
    var longDate = function (d) {
        try { return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }); } catch (e) { return iso(d); }
    };

    // ------------------------------------------------------------------ members list: selection + bulk bar
    $$('form[data-members-bulk]').forEach(function (form) {
        var select = $('[data-bulk-action]', form);
        var all = $('[data-select-all]');
        var rows = $$('[data-select-row]');
        var count = $('[data-selected-count]', form);
        var reason = $('#bulk-reason', form);
        var selected = function () { return rows.filter(function (r) { return r.checked; }); };

        var refresh = function () {
            var n = selected().length;
            rows.forEach(function (r) { var tr = r.closest('tr'); if (tr) tr.classList.toggle('is-selected', r.checked); });
            if (all) { all.checked = n > 0 && n === rows.length; all.indeterminate = n > 0 && n < rows.length; }
            if (count) count.textContent = n > 0 ? plural(n, 'member selected', 'members selected') : '';
            var action = select ? select.value : '';
            $$('[data-bulk-for]', form).forEach(function (el) {
                el.hidden = (' ' + el.getAttribute('data-bulk-for') + ' ').indexOf(' ' + action + ' ') === -1;
            });
            if (reason) reason.required = action === 'reject';
        };
        if (all) all.addEventListener('change', function () { rows.forEach(function (r) { r.checked = all.checked; }); refresh(); });
        rows.forEach(function (r) { r.addEventListener('change', refresh); });
        if (select) select.addEventListener('change', refresh);

        form.addEventListener('submit', function (e) {
            var n = selected().length;
            var action = select ? select.value : '';
            var stop = function (msg, focusEl) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (count) count.textContent = msg;
                if (focusEl) focusEl.focus();
            };
            if (n === 0) { stop('Tick at least one member first.', rows[0]); return; }
            if (!action) { stop('Choose what to do with the selected members.', select); return; }
            if (action === 'reject' && reason && reason.value.trim() === '') { stop('Give a reason before rejecting.', reason); return; }
            var text = {
                approve: 'Approve ' + plural(n, 'application', 'applications') + ' and issue membership numbers?',
                reject: 'Reject ' + plural(n, 'application', 'applications') + '? The reason is kept on each record.',
                export: 'Download ' + plural(n, 'record', 'records') + ' as CSV? The file contains personal data: keep it somewhere safe and delete it when you are done.'
            }[action];
            if (text && !window.confirm(text)) e.preventDefault();
        });
        refresh();
    });

    // ------------------------------------------------------------------ member screen: review and status
    $$('form[data-members-status]').forEach(function (form) {
        var radios = $$('[data-status-to]', form);
        var submit = $('[data-status-submit]', form);
        var reason = $('[data-reason]', form);
        var start = $('[data-start-date]', form);
        var expiry = $('[data-expiry-date]', form);
        var preview = $('[data-expiry-preview]', form);
        var months = parseInt(form.getAttribute('data-period-months') || '0', 10);
        var needReason = (form.getAttribute('data-reason-for') || '').split(/\s+/).filter(Boolean);
        var from = form.getAttribute('data-from') || '';
        var danger = ['rejected', 'suspended', 'cancelled'];

        var chosen = function () { var r = radios.filter(function (x) { return x.checked; })[0]; return r ? r.value : ''; };
        var updatePreview = function () {
            if (!preview) return;
            var s = start && start.value ? parseIso(start.value) : new Date();
            var e = expiry && expiry.value ? parseIso(expiry.value) : null;
            var text = '';
            if (e && s) text = e > s ? 'The membership runs from ' + longDate(s) + ' until ' + longDate(e) + '.' : 'The expiry date must come after the start date.';
            else if (s && months > 0) text = 'The membership will run from ' + longDate(s) + ' until ' + longDate(addMonths(s, months)) + '.';
            else if (s) text = 'This membership has no expiry date.';
            preview.textContent = text;
        };
        var apply = function () {
            var to = chosen();
            $$('[data-for]', form).forEach(function (el) {
                el.hidden = to === '' || (' ' + el.getAttribute('data-for') + ' ').indexOf(' ' + to + ' ') === -1;
            });
            if (reason) reason.required = needReason.indexOf(to) !== -1;
            if (submit) {
                var label = $('span', submit);
                var picked = radios.filter(function (x) { return x.checked; })[0];
                var text = picked ? $('.members-choice__label', picked.closest('label')).textContent.trim() : submit.getAttribute('data-default-label');
                if (label) label.textContent = text;
                submit.disabled = to === '';
                submit.classList.toggle('btn--danger', danger.indexOf(to) !== -1);
                submit.classList.toggle('btn--primary', danger.indexOf(to) === -1);
            }
            // the shared admin.js asks for confirmation before a form with data-confirm is sent
            if (to === 'approved' && from === 'pending') form.setAttribute('data-confirm', 'Approve this application and issue a membership number?');
            else if (danger.indexOf(to) !== -1) form.setAttribute('data-confirm', 'Are you sure? The reason is kept on the record.');
            else form.removeAttribute('data-confirm');
            updatePreview();
        };
        radios.forEach(function (r) { r.addEventListener('change', apply); });
        if (start) start.addEventListener('input', updatePreview);
        if (expiry) expiry.addEventListener('input', updatePreview);
        apply();
    });

    // ------------------------------------------------------------------ member screen: resend e-mail
    $$('form[data-members-resend]').forEach(function (form) {
        var select = $('[data-resend-template]', form);
        var apply = function () {
            $$('[data-for]', form).forEach(function (el) {
                el.hidden = (' ' + el.getAttribute('data-for') + ' ').indexOf(' ' + (select ? select.value : '') + ' ') === -1;
            });
        };
        if (select) { select.addEventListener('change', apply); apply(); }
    });

    // ------------------------------------------------------------------ member screen: delete only after typing the reference
    $$('form[data-members-delete]').forEach(function (form) {
        var ref = (form.getAttribute('data-members-delete') || '').toLowerCase();
        var input = $('input[name="confirm_ref"]', form);
        var btn = $('[data-delete-submit]', form);
        if (!input || !btn || !ref) return;
        var apply = function () { btn.disabled = input.value.trim().toLowerCase() !== ref; };
        input.addEventListener('input', apply);
        apply();
    });

    // ------------------------------------------------------------------ payments
    $$('[data-pay-status]').forEach(function (sel) {
        var form = sel.closest('form');
        var notify = form ? $('input[type="checkbox"][name$="[notify]"]', form) : null;
        var box = notify ? notify.closest('.field') : null;
        if (!box) return;
        var apply = function () { box.hidden = sel.value !== 'received'; };
        sel.addEventListener('change', apply);
        apply();
    });
    $$('[data-pay-receive]').forEach(function (btn) {
        btn.hidden = false;
        btn.addEventListener('click', function () {
            var pid = btn.getAttribute('data-pay-receive');
            var details = document.getElementById('pay-edit-' + pid);
            var status = document.getElementById('pe' + pid + '-status');
            if (!details || !status) return;
            details.open = true;
            status.value = 'received';
            status.dispatchEvent(new Event('change', { bubbles: true }));
            details.scrollIntoView({ block: 'center', behavior: reduced ? 'auto' : 'smooth' });
            status.focus({ preventScroll: true });
        });
    });

    // ------------------------------------------------------------------ edit details
    $$('[data-use-type-fee]').forEach(function (btn) {
        var fee;
        try { fee = JSON.parse(btn.getAttribute('data-use-type-fee') || '{}'); } catch (e) { return; }
        btn.hidden = false;
        btn.addEventListener('click', function () {
            ['fee_amount', 'joining_fee', 'fee_currency', 'fee_period'].forEach(function (k) {
                var el = document.getElementById('f-' + k);
                if (el && typeof fee[k] === 'string') { el.value = fee[k]; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); }
            });
        });
    });
    $$('[data-dob]').forEach(function (input) {
        var help = document.getElementById(input.id + '-help');
        if (!help) return;
        var apply = function () {
            var d = parseIso(input.value);
            if (!d) return;
            var now = new Date();
            var age = now.getFullYear() - d.getFullYear() - ((now.getMonth() < d.getMonth() || (now.getMonth() === d.getMonth() && now.getDate() < d.getDate())) ? 1 : 0);
            if (age < 0 || age > 120) { help.textContent = 'Check the date of birth.'; return; }
            help.textContent = 'Age ' + age + (age < 18 ? ': under 18, so the parent or guardian details below are required.' : '.');
        };
        input.addEventListener('input', apply);
    });

    // ------------------------------------------------------------------ receipt
    document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('[data-print]')) window.print();
    });

    // ------------------------------------------------------------------ membership type: live fee preview
    $$('form[data-fee-form]').forEach(function (form) {
        var cfg = {}, hints = {};
        try { cfg = JSON.parse(form.getAttribute('data-fee-config') || '{}'); } catch (e) { cfg = {}; }
        try { hints = JSON.parse(form.getAttribute('data-period-hints') || '{}'); } catch (e) { hints = {}; }
        var modes = $$('[data-fee-mode]', form);
        var amount = $('[data-fee-amount]', form);
        var currency = $('[data-fee-currency]', form);
        var joining = $('[data-fee-joining]', form);
        var period = $('[data-fee-period]', form);
        var name = $('[data-fee-name]', form);
        var elig = $('[data-fee-eligibility]', form);
        var hint = $('[data-period-hint]', form);
        var out = {
            name: $('[data-preview-name]', form), fee: $('[data-preview-fee]', form), joining: $('[data-preview-joining]', form),
            elig: $('[data-preview-eligibility]', form), term: $('[data-preview-term]', form)
        };
        var amountField = amount ? amount.closest('.field') : null;
        var zero = cfg.zero || [];

        // same rules as MembershipService::parseMoney / formatAmount / feeLabel
        var parseMoney = function (raw) {
            var s = String(raw || '').replace(/[,\s ]/g, '');
            return /^\d{1,10}(\.\d{1,2})?$/.test(s) ? parseFloat(s) : null;
        };
        var money = function (n, cur) {
            var whole = zero.indexOf(cur) !== -1 && Math.abs(n - Math.round(n)) < 0.005;
            var txt = n.toLocaleString('en-US', { minimumFractionDigits: whole ? 0 : 2, maximumFractionDigits: whole ? 0 : 2 });
            return (cur + ' ' + txt).trim();
        };
        var mode = function () { var r = modes.filter(function (x) { return x.checked; })[0]; return r ? r.value : 'tba'; };

        var update = function () {
            var m = mode();
            var cur = ((currency && currency.value) || 'UGX').trim().toUpperCase();
            var per = period ? period.value : 'annual';
            var n = amount ? parseMoney(amount.value) : null;
            var fee;
            if (m === 'tba') fee = cfg.tba;
            else if (m === 'free') fee = cfg.free;
            else if (n === null) fee = (amount && amount.value.trim() !== '') ? 'Check the amount: numbers only, for example 50000' : 'Enter the fee amount';
            else if (n <= 0) fee = cfg.free;
            else fee = money(n, cur) + ((cfg.periods && cfg.periods[per]) ? ' ' + cfg.periods[per] : '');
            if (out.fee) { out.fee.textContent = fee; out.fee.classList.toggle('is-tba', fee === cfg.tba || (m === 'amount' && n === null)); }
            var j = joining ? parseMoney(joining.value) : null;
            if (out.joining) {
                var jt = j !== null && j > 0 ? String(cfg.joining || '{amount}').replace('{amount}', money(j, cur)) : '';
                out.joining.textContent = jt;
                out.joining.hidden = jt === '';
            }
            if (out.name) out.name.textContent = (name && name.value.trim()) || 'Membership type';
            if (out.elig) { var et = elig ? elig.value.trim() : ''; out.elig.textContent = et; out.elig.hidden = et === ''; }
            var term = hints[per] || '';
            if (out.term) out.term.textContent = term;
            if (hint) hint.textContent = term;
            if (amountField) amountField.classList.toggle('is-off', m !== 'amount');
        };

        modes.forEach(function (r) { r.addEventListener('change', update); });
        [amount, currency, joining, name, elig].forEach(function (el) { if (el) el.addEventListener('input', update); });
        if (period) period.addEventListener('change', update);
        if (amount) {
            // typing an amount means "a set amount"
            amount.addEventListener('input', function () {
                var set = modes.filter(function (x) { return x.value === 'amount'; })[0];
                if (set && !set.checked && amount.value.trim() !== '') { set.checked = true; update(); }
            });
        }
        if (currency) currency.addEventListener('blur', function () { currency.value = currency.value.trim().toUpperCase(); update(); });
        update();
    });
})();
