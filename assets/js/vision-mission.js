/*!
 * Vision & Mission explorer (partials/vision-mission.php; styles in scss/components/_vision-mission.scss).
 * Progressive enhancement: without this script both statements (each with its photo) are shown one under the other. With it:
 *   - the two switches become tabs (click, arrow keys, Home / End) and the photo changes with the statement;
 *   - a swipe sideways on the photo or the card changes the statement on touch screens;
 *   - the key points become chips: tap one to highlight it and read its one-line explanation (when the editors wrote one);
 *   - the explorer advances by itself while it is on screen, pauses under the pointer, keyboard focus or a hidden browser tab, and stops for good once the visitor chooses;
 *   - the photo tilts a little under a mouse, and the heading underline draws itself once.
 * Visitors who ask for reduced motion get no auto-advance, no tilt and no drawing.
 */
(() => {
    'use strict';

    const boxes = document.querySelectorAll('[data-vm]');
    if (!boxes.length) return;
    const mq = (q) => window.matchMedia && window.matchMedia(q).matches;
    const calm = mq('(prefers-reduced-motion: reduce)');
    const mouse = mq('(hover: hover) and (pointer: fine)');
    const MS = 9000;

    boxes.forEach((box) => {
        const tabsEl = box.querySelector('[role="tablist"]');
        const tabs = Array.from(box.querySelectorAll('[role="tab"]'));
        const panels = tabs.map((t) => document.getElementById(t.getAttribute('aria-controls')));
        const photos = Array.from(box.querySelectorAll('[data-vm-photo]'));
        if (!tabsEl || tabs.length < 2 || panels.some((p) => !p)) return;

        tabsEl.hidden = false;
        box.classList.add('is-ready');
        box.style.setProperty('--vm-ms', MS + 'ms');
        const section = box.closest('section');
        let current = Math.max(0, tabs.findIndex((t) => t.classList.contains('is-active')));
        let auto = !calm;
        let visible = false;
        let timer = 0;

        // ---- chips: the key points become buttons that reveal a one-line explanation -------------------------------------
        panels.forEach((panel) => {
            const explain = panel.querySelector('.vm__explain');
            const items = Array.from(panel.querySelectorAll('.vm__points li'));
            const hasNotes = items.some((li) => li.querySelector('.vm__note'));
            if (explain && hasNotes) explain.hidden = false;
            else if (explain) explain.hidden = true;
            let open = null;
            const clear = () => {
                items.forEach((li) => { const b = li.querySelector('.vm__chip'); if (b) b.setAttribute('aria-pressed', 'false'); });
                if (explain) { explain.classList.remove('is-on'); explain.textContent = ''; }
                open = null;
            };
            panel.addEventListener('vm:reset', clear);
            items.forEach((li) => {
                const label = li.querySelector('.vm__chip-label');
                if (!label) return;
                const note = li.querySelector('.vm__note');
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'vm__chip';
                b.setAttribute('aria-pressed', 'false');
                while (label.firstChild) b.appendChild(label.firstChild);
                label.replaceWith(b);
                b.addEventListener('click', () => {
                    const was = open === b;
                    clear();
                    if (was) return;
                    open = b;
                    b.setAttribute('aria-pressed', 'true');
                    if (explain && note) { explain.textContent = note.textContent; explain.classList.add('is-on'); }
                    auto = false;                         // the visitor is reading: no more auto-advance
                    play();
                });
            });
        });

        // ---- tabs, photo, auto-advance -------------------------------------------------------------------------------------
        const play = () => {
            tabs.forEach((t) => t.classList.remove('is-playing'));
            window.clearTimeout(timer);
            if (!auto) return;
            const t = tabs[current];
            void t.offsetWidth;                       // restart the bar animation
            t.classList.add('is-playing');
            if (visible && !box.classList.contains('is-paused')) timer = window.setTimeout(() => show((current + 1) % tabs.length, false), MS);
        };

        const show = (i, user) => {
            const changed = i !== current;
            current = i;
            tabsEl.setAttribute('data-active', String(i));
            tabs.forEach((t, k) => {
                const on = k === i;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                panels[k].classList.toggle('is-active', on);
                if (photos[k]) photos[k].classList.toggle('is-active', on);
            });
            if (changed) panels.forEach((p) => p.dispatchEvent(new Event('vm:reset')));
            if (user) auto = false;                   // the visitor took over: no more auto-advance
            play();
        };

        tabs.forEach((t, i) => {
            t.addEventListener('click', () => show(i, true));
            t.addEventListener('keydown', (e) => {
                const key = e.key;
                let to = -1;
                if (key === 'ArrowRight' || key === 'ArrowDown') to = (i + 1) % tabs.length;
                else if (key === 'ArrowLeft' || key === 'ArrowUp') to = (i - 1 + tabs.length) % tabs.length;
                else if (key === 'Home') to = 0;
                else if (key === 'End') to = tabs.length - 1;
                if (to >= 0) { e.preventDefault(); show(to, true); tabs[to].focus(); }
            });
        });
        tabsEl.setAttribute('data-active', String(current));

        // pause while the pointer or the keyboard is inside
        const pause = () => { box.classList.add('is-paused'); window.clearTimeout(timer); };
        const resume = () => { box.classList.remove('is-paused'); play(); };
        box.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') pause(); });
        box.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') resume(); });
        box.addEventListener('focusin', pause);
        box.addEventListener('focusout', () => { if (!box.contains(document.activeElement)) resume(); });

        // swipe on touch screens (photo and card)
        let x0 = null;
        let y0 = 0;
        box.querySelectorAll('[data-vm-media], [data-vm-stage]').forEach((zone) => {
            zone.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
            zone.addEventListener('touchend', (e) => {
                if (x0 === null) return;
                const dx = e.changedTouches[0].clientX - x0;
                const dy = e.changedTouches[0].clientY - y0;
                x0 = null;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) show((current + (dx < 0 ? 1 : -1) + tabs.length) % tabs.length, true);
            }, { passive: true });
        });

        // a soft tilt of the photo under a mouse
        const frame = box.querySelector('[data-vm-frame]');
        if (frame && mouse && !calm) {
            let raf = 0;
            frame.addEventListener('pointermove', (e) => {
                if (raf) return;
                raf = window.requestAnimationFrame(() => {
                    raf = 0;
                    const r = frame.getBoundingClientRect();
                    const px = (e.clientX - r.left) / r.width - 0.5;
                    const py = (e.clientY - r.top) / r.height - 0.5;
                    frame.style.setProperty('--vm-ry', (px * 6).toFixed(2) + 'deg');
                    frame.style.setProperty('--vm-rx', (-py * 5).toFixed(2) + 'deg');
                });
            });
            frame.addEventListener('pointerleave', () => { frame.style.removeProperty('--vm-rx'); frame.style.removeProperty('--vm-ry'); });
        }

        // only run while on screen and while the tab of the browser is in front; the heading underline draws itself on first sight
        if (section && !calm) section.classList.add('vm-live');
        const head = section && section.querySelector('.hl');
        if (head && 'IntersectionObserver' in window) {
            const io = new IntersectionObserver((es) => { if (es.some((en) => en.isIntersecting)) { section.classList.add('vm-seen'); io.disconnect(); } }, { threshold: 0.6 });
            io.observe(head);
        } else if (section) {
            section.classList.add('vm-seen');
        }
        const onView = (on) => {
            visible = on;
            on ? play() : window.clearTimeout(timer);
        };
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((es) => es.forEach((en) => onView(en.isIntersecting)), { threshold: 0.4 }).observe(box);
        } else {
            onView(true);
        }
        document.addEventListener('visibilitychange', () => { if (document.hidden) window.clearTimeout(timer); else play(); });
        play();
    });
})();
