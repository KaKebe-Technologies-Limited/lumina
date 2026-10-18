/*!
 * meetings.js - public meeting pages. Progressive enhancement only: every page works without it (the server prints the answer buttons, the times in
 * East Africa Time and the request form's messages). No inline JS (CSP).
 *
 * INVITEE PAGE /meeting/{token}   (body.is-meeting; included in <head> with defer, so it runs BEFORE main.js)
 *   - switches the analytics script off: the address of this page contains the invitee's personal link and must not be sent to a third party
 *   - [data-meeting-local]        "In your time zone": the meeting time in the visitor's own zone, shown only when it differs from East Africa Time
 *                                 (reads <time data-meeting-start data-meeting-end> = UTC ISO strings)
 *   - [data-meeting-answer]       when the page was opened from an e-mail button (?r= = data-suggest) the matching answer button gets the focus;
 *                                 after saving, the confirmation in [data-meeting-status] gets the focus so screen readers read it
 * CONTACT PAGE   (the "Request a meeting" section)
 *   - [data-meeting-request-done] the "thank you" box gets the focus after the redirect
 */
(() => {
    'use strict';

    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const EAT_OFFSET_MIN = 180;

    const focusSoft = (el) => {
        if (!el) return;
        try {
            el.focus({ preventScroll: true });
        } catch (e) {
            el.focus();
        }
    };

    // ------------------------------------------------------------------ invitee page
    if (document.body && document.body.classList.contains('is-meeting')) {
        // the token is in the address bar: never hand it to the analytics script (main.js reads this attribute after this script has run)
        document.body.removeAttribute('data-ga-id');
    }

    const startEl = document.querySelector('time[data-meeting-start]');
    const localBox = document.querySelector('[data-meeting-local]');
    if (startEl && localBox) {
        try {
            const start = new Date(startEl.getAttribute('data-meeting-start') || '');
            const endRaw = startEl.getAttribute('data-meeting-end') || '';
            const end = endRaw ? new Date(endRaw) : null;
            const textEl = localBox.querySelector('[data-meeting-local-text]');
            // getTimezoneOffset() is minutes WEST of UTC; East Africa Time is +180 east, with no daylight saving
            if (textEl && !Number.isNaN(start.getTime()) && -start.getTimezoneOffset() !== EAT_OFFSET_MIN) {
                const dateFmt = new Intl.DateTimeFormat(undefined, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
                const timeFmt = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' });
                const zoneFmt = new Intl.DateTimeFormat(undefined, { timeZoneName: 'short' });
                const zone = (d) => {
                    const part = zoneFmt.formatToParts(d).find((p) => p.type === 'timeZoneName');
                    return part ? part.value : '';
                };
                let text = dateFmt.format(start) + ', ' + timeFmt.format(start);
                if (end && !Number.isNaN(end.getTime())) {
                    const sameDay = dateFmt.format(start) === dateFmt.format(end);
                    text += '–' + (sameDay ? '' : dateFmt.format(end) + ', ') + timeFmt.format(end);
                }
                const z = zone(start);
                textEl.textContent = text + (z ? ' ' + z : '');
                localBox.hidden = false;
            }
        } catch (e) {
            /* an old browser without Intl: the East Africa Time above is enough */
        }
    }

    const answer = document.querySelector('[data-meeting-answer]');
    if (answer) {
        const status = answer.querySelector('[data-meeting-status]');
        const suggest = answer.getAttribute('data-suggest') || '';
        const hasMessage = status && status.textContent.trim() !== '';
        if (hasMessage) {
            window.setTimeout(() => {
                focusSoft(status);
                if (!reduceMotion) status.scrollIntoView({ block: 'nearest' });
            }, 80);
        } else if (suggest) {
            const btn = answer.querySelector('button[data-answer="' + suggest.replace(/[^a-z]/g, '') + '"]');
            if (btn) window.setTimeout(() => focusSoft(btn), 80);
        }
    }

    // ------------------------------------------------------------------ Contact page: request form
    const done = document.querySelector('[data-meeting-request-done]');
    if (done) {
        window.setTimeout(() => {
            focusSoft(done);
            done.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
        }, 80);
    }
})();
