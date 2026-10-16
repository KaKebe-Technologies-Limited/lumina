/*!
 * Careers page (OWNER: B2). Progressive enhancement only: the file is validated again on the server (Uploader::privateFile).
 *   form[data-apply-form] input[type=file][data-max-bytes][data-exts][data-error-size][data-error-type]
 *   Checks the CV size and extension BEFORE the upload starts and explains the problem next to the field.
 *   After a failed submission, focus moves to the error summary (form-summary.js) or, without one, to the first field with an error.
 */
(() => {
    'use strict';

    document.querySelectorAll('form[data-apply-form]').forEach((form) => {
        const bad = form.querySelector('.field--error input, .field--error textarea, .field--error select');
        // the error summary above the form (form-summary.js) takes the focus when there is one
        if (bad && !document.querySelector('[data-error-summary]') && !document.querySelector('form[data-apply-form][data-focused]')) {
            form.dataset.focused = '1';
            window.requestAnimationFrame(() => bad.focus());
        }

        const input = form.querySelector('input[type="file"][data-max-bytes]');
        if (!input) return;
        const max = parseInt(input.dataset.maxBytes || '0', 10);
        const exts = (input.dataset.exts || '').split(',').map((s) => s.trim().toLowerCase()).filter(Boolean);
        const field = input.closest('.field');
        let note = null;

        const clear = () => {
            if (note) { note.remove(); note = null; }
            if (field) field.classList.remove('field--error');
            input.removeAttribute('aria-invalid');
        };
        const show = (message) => {
            clear();
            note = document.createElement('div');
            note.className = 'field__error';
            note.setAttribute('role', 'alert');
            note.textContent = message;
            if (field) { field.classList.add('field--error'); field.appendChild(note); }
            input.setAttribute('aria-invalid', 'true');
        };
        const problem = () => {
            const file = input.files && input.files[0];
            if (!file) return '';
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            if (exts.length && !exts.includes(ext)) return input.dataset.errorType || '';
            if (max > 0 && file.size > max) return input.dataset.errorSize || '';
            return '';
        };

        input.addEventListener('change', () => {
            const msg = problem();
            if (msg) { show(msg); input.value = ''; } else { clear(); }
        });
        form.addEventListener('submit', (e) => {
            const msg = problem();
            if (msg) { e.preventDefault(); show(msg); input.focus(); }
        });
    });
})();
