/**
 * Team page (/team) - OWNED BY B7 "org-public".
 * Progressive enhancement: long biographies are cut to a short excerpt in the card (CSS, via the .has-bio-dialog class set here)
 * and the full text opens in ONE shared modal <dialog>. Without JavaScript (or without <dialog> support) the cards simply show
 * the full biography and no button, so nothing is lost.
 *
 * Hooks (see app/views/web/team/*): [data-team-groups][data-close-label], .team-card--long, [data-bio-open], [data-bio-name],
 * [data-bio-role], [data-bio-photo], [data-bio-full], [data-bio-links].
 */
(() => {
    'use strict';

    const root = document.querySelector('[data-team-groups]');
    if (!root || typeof HTMLDialogElement === 'undefined' || typeof document.createElement('dialog').showModal !== 'function') {
        return;
    }
    const buttons = root.querySelectorAll('[data-bio-open]');
    if (!buttons.length) {
        return;
    }

    const closeLabel = root.getAttribute('data-close-label') || 'Close';
    let dialog = null;
    let nodes = null;

    function build() {
        dialog = document.createElement('dialog');
        dialog.className = 'bio-dialog';
        dialog.setAttribute('aria-labelledby', 'bio-dialog-title');

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'bio-dialog__close';
        closeBtn.setAttribute('aria-label', closeLabel);
        closeBtn.innerHTML = '<svg class="icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        closeBtn.addEventListener('click', () => dialog.close());

        const inner = document.createElement('div');
        inner.className = 'bio-dialog__inner';
        const media = document.createElement('div');
        media.className = 'bio-dialog__media';
        const body = document.createElement('div');
        body.className = 'bio-dialog__body';
        const title = document.createElement('h2');
        title.id = 'bio-dialog-title';
        title.className = 'bio-dialog__name';
        const role = document.createElement('p');
        role.className = 'bio-dialog__role';
        const text = document.createElement('div');
        text.className = 'bio-dialog__text prose';
        const links = document.createElement('div');
        links.className = 'bio-dialog__links';
        body.append(title, role, text, links);
        inner.append(media, body);
        dialog.append(closeBtn, inner);
        document.body.append(dialog);

        // click on the dark backdrop (outside the panel) closes the dialog
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
        dialog.addEventListener('close', () => document.body.classList.remove('is-dialog-open'));

        nodes = { inner, media, title, role, text, links };
    }

    function fill(card) {
        const name = card.querySelector('[data-bio-name]');
        const role = card.querySelector('[data-bio-role]');
        const photo = card.querySelector('[data-bio-photo]');
        const full = card.querySelector('[data-bio-full]');
        const links = card.querySelector('[data-bio-links]');

        nodes.title.textContent = name ? name.textContent : '';
        nodes.role.textContent = role ? role.textContent : '';
        nodes.role.hidden = !nodes.role.textContent;

        nodes.media.textContent = '';
        if (photo) {
            const img = document.createElement('img');
            img.src = photo.currentSrc || photo.src;
            img.alt = photo.alt || '';
            nodes.media.append(img);
            nodes.media.hidden = false;
        } else {
            nodes.media.hidden = true;
        }
        nodes.inner.classList.toggle('has-media', !nodes.media.hidden);

        nodes.text.textContent = '';
        if (full) {
            Array.from(full.children).forEach((child) => nodes.text.append(child.cloneNode(true)));
        }

        nodes.links.textContent = '';
        if (links) {
            Array.from(links.children).forEach((a) => nodes.links.append(a.cloneNode(true)));
        }
        nodes.links.hidden = !nodes.links.children.length;
    }

    document.documentElement.classList.add('has-bio-dialog');
    buttons.forEach((btn) => {
        btn.hidden = false;
        btn.addEventListener('click', () => {
            const card = btn.closest('.team-card');
            if (!card) return;
            if (!dialog) build();
            fill(card);
            document.body.classList.add('is-dialog-open');
            dialog.showModal();
        });
    });
})();
