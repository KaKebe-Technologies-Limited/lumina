/*!
 * Error summary above the public forms (involvement, donate pledge, careers, contact). Progressive enhancement only:
 * without JS the summary still lists the problems and its links still jump to the fields.
 *   [data-error-summary]       focusable block, focused once after a failed submission (the page was reloaded after the redirect)
 *   [data-error-link]          each link focuses its field instead of only scrolling to it
 */
(() => {
    'use strict';

    const summary = document.querySelector('[data-error-summary]');
    if (!summary) return;

    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // focus the summary once the page (and the hash jump to #join / #pledge / #contact-form) has settled
    const focusSummary = () => {
        try {
            summary.focus({ preventScroll: true });
        } catch (e) {
            summary.focus();
        }
        summary.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
    };
    window.setTimeout(focusSummary, 60);

    summary.querySelectorAll('[data-error-link]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const id = (link.getAttribute('href') || '').replace(/^#/, '');
            const field = id ? document.getElementById(id) : null;
            if (!field) return;
            e.preventDefault();
            field.focus({ preventScroll: true });
            field.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    });
})();
