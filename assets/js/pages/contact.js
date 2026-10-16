/*!
 * Contact page (OWNER: B2). Progressive enhancement only: the form is validated again on the server.
 *   When the page is shown again after a failed submission, focus moves to the first field with an error.
 */
(() => {
    'use strict';

    const form = document.querySelector('[data-contact-form]');
    if (!form) return;
    const bad = form.querySelector('.field--error input, .field--error textarea, .field--error select');
    if (bad && !document.querySelector('[data-error-summary]')) {      // the error summary (form-summary.js) takes the focus when there is one
        window.requestAnimationFrame(() => bad.focus());
    }
})();
