/*!
 * Contact page (OWNER: B2). Progressive enhancement only: the form is validated again on the server.
 *   - When the page is shown again after a failed submission, focus moves to the first field with an error.
 *   - Topic buttons fill the subject in one click (the visitor can still type their own); the pressed state follows what is typed.
 *   - A live "characters left" counter with a thin progress line under the message box.
 *   - A small green tick on the e-mail field once the address looks right.
 */
(() => {
    'use strict';

    const form = document.querySelector('[data-contact-form]');
    if (!form) return;

    const bad = form.querySelector('.field--error input, .field--error textarea, .field--error select');
    if (bad && !document.querySelector('[data-error-summary]')) {      // the error summary (form-summary.js) takes the focus when there is one
        window.requestAnimationFrame(() => bad.focus());
    }

    // ---- topics -> subject
    const subject = form.querySelector('[data-contact-subject]');
    const chips = Array.from(form.querySelectorAll('[data-topic]'));
    if (subject && chips.length) {
        const mark = () => {
            const v = subject.value.trim().toLowerCase();
            chips.forEach((c) => c.setAttribute('aria-pressed', v !== '' && c.getAttribute('data-topic').toLowerCase() === v ? 'true' : 'false'));
        };
        chips.forEach((c) => c.addEventListener('click', () => {
            subject.value = c.getAttribute('data-topic') || '';
            subject.dispatchEvent(new Event('input', { bubbles: true }));
            const msg = form.querySelector('[data-contact-message]');
            if (msg && msg.value.trim() === '') msg.focus();
        }));
        subject.addEventListener('input', mark);
        mark();
    }

    // ---- characters left
    const message = form.querySelector('[data-contact-message]');
    if (message) {
        const max = parseInt(message.getAttribute('maxlength') || '0', 10);
        if (max > 0) {
            const word = message.getAttribute('data-counter-label') || 'characters left';
            const box = document.createElement('div');
            box.className = 'contact-counter';
            box.innerHTML = '<span class="contact-counter__bar" aria-hidden="true"><i></i></span><span class="contact-counter__text" aria-live="polite"></span>';
            const host = message.closest('.field') || message.parentNode;
            host.appendChild(box);
            const bar = box.querySelector('i');
            const text = box.querySelector('.contact-counter__text');
            const update = () => {
                const used = message.value.length;
                const left = Math.max(0, max - used);
                bar.style.width = Math.min(100, (used / max) * 100) + '%';
                box.classList.toggle('is-low', left < max * 0.1);
                text.textContent = left.toLocaleString('en-US') + ' ' + word;
            };
            message.addEventListener('input', update);
            update();
        }
    }

    // ---- e-mail looks right
    const email = form.querySelector('input[type="email"]');
    if (email) {
        const holder = email.closest('.field');
        const check = () => holder && holder.classList.toggle('is-valid', email.value.trim() !== '' && email.validity.valid);
        email.addEventListener('input', check);
        check();
    }
})();
