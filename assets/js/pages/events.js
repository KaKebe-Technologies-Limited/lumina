/**
 * Event page (/events/{slug}) - OWNED BY B7 "org-public".
 * After the registration form was sent (or rejected) the server redirects to #register. Move keyboard / screen-reader focus to the
 * confirmation or the error summary so the result is announced; otherwise to the first invalid field.
 * Hooks: [data-focus-on-load] (inside #register), [aria-invalid="true"].
 */
(() => {
    'use strict';

    const box = document.getElementById('register');
    if (!box) return;

    const target = box.querySelector('[data-focus-on-load]') || box.querySelector('[aria-invalid="true"]');
    if (!target) return;

    try {
        target.focus({ preventScroll: true });
        target.scrollIntoView({ block: 'center' });
    } catch (e) {
        /* focus is a nicety; never break the page */
    }
})();
