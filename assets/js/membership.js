/*!
 * membership.js - the membership registration form (/membership/register). Progressive enhancement ONLY: the server validates everything
 * (MembershipApplication::process) and without JavaScript every conditional field stays visible.
 *
 *   form[data-mb-form]            data-adult-age, data-msg-required|email|phone|min|accept|dob|date (the editable messages of Pages > Membership)
 *   select#f-type_id              option[data-fee|data-joining|data-elig] -> [data-fee-line] [data-joining-line] [data-elig-line]/[data-elig-text]
 *   input[data-dob]               shows [data-age-line]; under 18 opens the guardian block ([data-minor-group], its aside link [data-minor-link])
 *   select[data-other-trigger]    the answer "other" opens [data-cond="<field>"] (free text)
 *   [data-count-for]              "n / max" counter of a textarea
 *   [data-mb-summary]             error summary: filled by the checks below, or rendered by the server after a failed submission
 *   input[type=file][data-upload-input]   headshot / proof of payment: the file checks live in membership-dashboard.js (it records data-upload-problem and fires
 *                                 "upload:checked"); here: required photo, the photo consent box that is required only while a photo is chosen
 * Inline checks mirror the server rules (required, e-mail, phone, minimum length, date of birth, tick boxes) so a typo does not cost a round trip.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-mb-form]');
    if (!form) {
        return;
    }

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $ = function (sel, root) { return (root || form).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || form).querySelectorAll(sel)); };
    var OTHER = 'other';
    var adultAge = parseInt(form.dataset.adultAge, 10) || 18;
    var attempted = false;

    var MSG = {
        required: form.dataset.msgRequired || '',
        email: form.dataset.msgEmail || '',
        phone: form.dataset.msgPhone || '',
        min: form.dataset.msgMin || '',
        accept: form.dataset.msgAccept || '',
        dob: form.dataset.msgDob || '',
        date: form.dataset.msgDate || ''
    };
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var PHONE_RE = /^\+?[0-9][0-9\s().\-]{5,24}$/;

    // ------------------------------------------------------------------------------------------------ small helpers

    var chars = function (s) { return Array.from(s).length; };

    var setMark = function (container, on) {
        $$('[data-req-mark]', container).forEach(function (m) { m.hidden = !on; });
    };

    // ------------------------------------------------------------------------------------------------ fee reminder of the chosen type

    var typeSelect = $('#f-type_id');
    var feeBox = $('[data-feebox]');
    var updateFee = function () {
        var opt = typeSelect && typeSelect.selectedIndex >= 0 ? typeSelect.options[typeSelect.selectedIndex] : null;
        var has = !!(opt && opt.value !== '');
        var fee = has ? (opt.dataset.fee || '') : '';
        $$('[data-fee-line]').forEach(function (el) {
            el.textContent = has && fee ? (el.dataset.template || '{fee}').replace('{fee}', fee) : '';
            el.hidden = !(has && fee);
        });
        var joining = $('[data-joining-line]');
        if (joining) {
            joining.textContent = has ? (opt.dataset.joining || '') : '';
            joining.hidden = joining.textContent === '';
        }
        var elig = $('[data-elig-line]');
        if (elig) {
            var text = has ? (opt.dataset.elig || '') : '';
            var holder = $('[data-elig-text]', elig);
            if (holder) { holder.textContent = text; }
            elig.hidden = text === '';
        }
        if (feeBox) { feeBox.hidden = !has; }
    };
    if (typeSelect) {
        typeSelect.addEventListener('change', updateFee);
        updateFee();
    }

    // ------------------------------------------------------------------------------------------------ date of birth: age line + guardian block

    var dob = $('[data-dob]');
    var ageLine = $('[data-age-line]');
    var guardian = $('[data-minor-group]');
    var minorLinks = $$('[data-minor-link]', document);

    var parseDate = function (v) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v || '');
        if (!m) { return null; }
        var y = +m[1], mo = +m[2] - 1, d = +m[3];
        var dt = new Date(y, mo, d);
        return dt.getFullYear() === y && dt.getMonth() === mo && dt.getDate() === d ? dt : null;
    };
    var ageOf = function (dt) {
        var t = new Date();
        if (dt > t) { return -1; }
        var a = t.getFullYear() - dt.getFullYear();
        var md = t.getMonth() - dt.getMonth();
        if (md < 0 || (md === 0 && t.getDate() < dt.getDate())) { a--; }
        return a;
    };
    var currentAge = function () {
        var dt = dob ? parseDate(dob.value) : null;
        return dt ? ageOf(dt) : -1;
    };

    var setMinor = function (minor) {
        if (!guardian) { return; }
        guardian.classList.toggle('is-active', minor);
        guardian.disabled = !minor;                         // a disabled fieldset is not submitted: nothing about a parent is sent for an adult
        minorLinks.forEach(function (li) { li.classList.toggle('is-active', minor); });
        $$('[data-minor-required], #f-consent_guardian', guardian).forEach(function (el) {
            el.required = minor;
            var wrap = el.closest('.field');
            if (wrap) { setMark(wrap, minor); }
        });
    };
    var updateAge = function () {
        var age = currentAge();
        var valid = age >= 0 && age <= 120;
        if (ageLine) {
            ageLine.textContent = valid ? (ageLine.dataset.template || '{age}').replace('{age}', String(age)) : '';
            ageLine.hidden = !valid;
        }
        setMinor(valid && age < adultAge);
    };
    if (dob) {
        dob.addEventListener('input', updateAge);
        dob.addEventListener('change', updateAge);
    }
    updateAge();

    // ------------------------------------------------------------------------------------------------ "other" answers

    $$('[data-other-trigger]').forEach(function (sel) {
        var wrap = $('[data-cond="' + sel.getAttribute('data-other-trigger') + '"]');
        var input = wrap ? $('input', wrap) : null;
        if (!wrap || !input) { return; }
        var apply = function () {
            var on = sel.value === OTHER;
            wrap.classList.toggle('is-active', on);
            input.disabled = !on;
            input.required = on;
            setMark(wrap, on);
        };
        sel.addEventListener('change', apply);
        apply();
    });

    // ------------------------------------------------------------------------------------------------ character counters

    $$('[data-count-for]').forEach(function (out) {
        var area = document.getElementById(out.dataset.countFor);
        var max = parseInt(out.dataset.max, 10) || 0;
        if (!area) { return; }
        var paint = function () {
            var n = chars(area.value);
            out.textContent = n + ' / ' + max;
            out.classList.toggle('is-near', max > 0 && n >= max * 0.9);
        };
        area.addEventListener('input', paint);
        paint();
    });

    // ------------------------------------------------------------------------------------------------ inline checks + error summary

    var summary = $('[data-mb-summary]');
    var summaryList = summary ? $('[data-mb-summary-list]', summary) : null;

    var checkedControls = function () {
        return $$('input, select, textarea').filter(function (el) {
            return !el.disabled && el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' && !el.closest('.hp-field');
        });
    };

    // the message for one control (or its radio group), or '' when it is fine
    var problem = function (el) {
        if (el.type === 'file') {
            // the photo / proof fields (assets/js/membership-dashboard.js records its own checks of the chosen file: type, size, pixels)
            var own = el.getAttribute('data-upload-problem');
            if (own) { return own; }
            return el.required && !(el.files && el.files.length) ? (el.closest('[data-upload]') || el).getAttribute('data-msg-missing') || MSG.required : '';
        }
        var v = el.value.trim();
        if (el.type === 'radio') {
            if (!el.required) { return ''; }
            var group = $$('input[type="radio"][name="' + el.name + '"]');
            return group.some(function (r) { return r.checked; }) ? '' : MSG.required;
        }
        if (el.type === 'checkbox') {
            return el.required && !el.checked ? MSG.accept : '';
        }
        if (v === '') {
            return el.required ? MSG.required : '';
        }
        if (el.type === 'email' && !EMAIL_RE.test(v)) { return MSG.email; }
        if (el.dataset.kind === 'phone' && !PHONE_RE.test(v)) { return MSG.phone; }
        if (el.hasAttribute('minlength') && chars(v) < parseInt(el.getAttribute('minlength'), 10)) { return MSG.min; }
        if (el.type === 'date') {
            var dt = parseDate(v);
            if (!dt) { return MSG.date; }
            var age = ageOf(dt);
            if (age < 0 || age > 120) { return MSG.dob; }
        }
        return '';
    };

    var fieldOf = function (el) { return el.closest('.field'); };

    var clearError = function (el) {
        var field = fieldOf(el);
        if (!field) { return; }
        field.classList.remove('field--error');
        $$('.field__error', field).forEach(function (box) {
            $$('[aria-describedby]', field).forEach(function (c) {
                var ids = (c.getAttribute('aria-describedby') || '').split(/\s+/).filter(function (id) { return id && id !== box.id; });
                if (ids.length) { c.setAttribute('aria-describedby', ids.join(' ')); } else { c.removeAttribute('aria-describedby'); }
            });
            box.parentNode.removeChild(box);
        });
        $$('[aria-invalid]', field).forEach(function (c) { c.removeAttribute('aria-invalid'); });
    };

    // set while a rejected file is reported BEFORE the first submit: no summary exists yet, so the message itself must be announced (role="alert")
    var announceNext = false;

    var showError = function (el, message) {
        var field = fieldOf(el);
        if (!field) { return; }
        clearError(el);
        field.classList.add('field--error');
        var box = document.createElement('div');
        box.className = 'field__error';
        box.id = (el.type === 'radio' ? 'f-' + el.name : el.id) + '-error';
        // no role="alert" here after a submit: the summary (focused after a failed submit) is the one announcement; aria-describedby ties each message to its field.
        // A file rejected on choosing it (too small, wrong type, too big) has no summary yet, so that one message is announced as an alert
        if (announceNext) { box.setAttribute('role', 'alert'); }
        box.textContent = message;
        field.appendChild(box);
        el.setAttribute('aria-invalid', 'true');
        // a radio group is described as a whole (like the server-rendered page): the message hangs on the fieldset, so every choice announces it
        var host = el.type === 'radio' ? (el.closest('fieldset') || el) : el;
        var ids = (host.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        ids.unshift(box.id);
        host.setAttribute('aria-describedby', ids.join(' '));
    };

    var labelOf = function (field) {
        if (field.dataset.summaryLabel) { return field.dataset.summaryLabel.replace(/\*/g, '').replace(/\s+/g, ' ').trim(); }
        var lab = $('label.field__label, legend.field__label', field);
        var text = lab ? lab.textContent : ($('.check span', field) || {}).textContent || '';
        return text.replace(/\*/g, '').replace(/\s+/g, ' ').trim().replace(/[.:]+$/, '');
    };

    // rebuilt from the fields that currently show an error, so the list never goes stale
    var renderSummary = function (focus) {
        if (!summary || !summaryList) { return; }
        summaryList.textContent = '';
        $$('.field--error').forEach(function (field) {
            var control = $('input, select, textarea', field);
            var box = $('.field__error', field);
            if (!control || !box) { return; }
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#' + control.id;
            a.setAttribute('data-error-link', '');
            var label = labelOf(field);
            a.textContent = (label ? label + ': ' : '') + box.textContent;
            li.appendChild(a);
            summaryList.appendChild(li);
        });
        var any = summaryList.children.length > 0;
        summary.hidden = !any;
        if (any && focus) {
            summary.focus({ preventScroll: true });
            // the top of the summary (its heading), not its middle: with 20+ messages the list is taller than the screen
            summary.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
        }
    };

    var validate = function (el) {
        var message = problem(el);
        if (message) { showError(el, message); } else { clearError(el); }
        return message;
    };

    var validateAll = function () {
        var seen = {};
        var firstBad = null;
        checkedControls().forEach(function (el) {
            if (el.type === 'radio') {
                if (seen[el.name]) { return; }
                seen[el.name] = true;
            }
            if (el.type === 'checkbox' && !el.required) { return; }
            if (validate(el) && !firstBad) { firstBad = el; }
        });
        return firstBad;
    };

    form.addEventListener('submit', function (e) {
        attempted = true;
        if (validateAll()) {
            e.preventDefault();                              // the shared double-submit guard ignores a prevented submit
            renderSummary(true);
        }
    });

    // after the first attempt (or on a field that shows an error) every change re-checks only that field
    var recheck = function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches('input, select, textarea') || el.closest('.hp-field')) { return; }
        var field = fieldOf(el);
        if (!field) { return; }
        if (el.type === 'file' && !attempted && !field.classList.contains('field--error')) {
            // a chosen picture that is too big / the wrong type / too small is reported at once, next to the field (the summary waits for the first submit)
            if (problem(el)) {
                announceNext = true;
                try { validate(el); } finally { announceNext = false; }
            }
            return;
        }
        if (attempted || field.classList.contains('field--error')) {
            if (el.type === 'checkbox' && !el.required) { return; }
            validate(el);
            renderSummary(false);
        }
    };
    form.addEventListener('change', recheck);
    form.addEventListener('input', recheck);
    form.addEventListener('upload:checked', recheck);                // fired by membership-dashboard.js when an asynchronous check of a picture is done

    // the photo consent box is required only while a photo is chosen (the server applies the same rule); the star follows
    var photoInput = $('#f-photo');
    var photoConsent = $('#f-photo_consent');
    var syncPhotoConsent = function () {
        if (!photoConsent) { return; }
        var has = !!(photoInput && photoInput.files && photoInput.files.length);
        photoConsent.required = has;
        var wrap = photoConsent.closest('.field');
        if (wrap) {
            setMark(wrap, has);
            if (!has) { clearError(photoConsent); }
        }
    };
    if (photoInput && photoConsent) {
        photoInput.addEventListener('change', syncPhotoConsent);
        syncPhotoConsent();
    }

    // links of the summary focus the field itself (the first radio / check box of a group)
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('[data-error-link]') : null;
        if (!a || !form.contains(a)) { return; }
        var target = document.getElementById((a.getAttribute('href') || '').replace(/^#/, ''));
        if (!target) { return; }
        e.preventDefault();
        target.focus({ preventScroll: true });
        target.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
    });

    // coming back with the browser's Back button restores the typed values after the first run: bring the conditional parts in step again
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) { return; }
        updateFee();
        updateAge();
        $$('[data-other-trigger]').forEach(function (sel) { sel.dispatchEvent(new Event('change')); });
        syncPhotoConsent();
    });

    // a failed submission rendered by the server: put the focus on the summary once the page has settled
    if (summary && summary.hasAttribute('data-server') && !summary.hidden) {
        attempted = true;
        window.setTimeout(function () {
            try { summary.focus({ preventScroll: true }); } catch (err) { summary.focus(); }
            summary.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
        }, 60);
    }
})();
