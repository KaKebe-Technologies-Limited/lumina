/*!
 * Vision & Mission explorer (partials/vision-mission.php; styles in scss/components/_vision-mission.scss).
 * Progressive enhancement: without this script both statements are shown one under the other. With it:
 *   - the two switches become tabs (click, arrow keys, Home / End) and only the chosen statement shows;
 *   - a swipe sideways on the stage changes the statement on touch screens;
 *   - the explorer advances by itself every few seconds while it is on screen, pauses under the pointer or keyboard focus, and stops for good once the visitor chooses a tab.
 *     (No auto-advance at all for visitors who ask for reduced motion.)
 */
(() => {
    'use strict';

    const boxes = document.querySelectorAll('[data-vm]');
    if (!boxes.length) return;
    const calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const MS = 9000;

    boxes.forEach((box) => {
        const tabsEl = box.querySelector('[role="tablist"]');
        const tabs = Array.from(box.querySelectorAll('[role="tab"]'));
        const panels = tabs.map((t) => document.getElementById(t.getAttribute('aria-controls')));
        if (!tabsEl || tabs.length < 2 || panels.some((p) => !p)) return;

        tabsEl.hidden = false;
        box.classList.add('is-ready');
        box.style.setProperty('--vm-ms', MS + 'ms');
        let current = Math.max(0, tabs.findIndex((t) => t.classList.contains('is-active')));
        let auto = !calm;
        let visible = false;
        let timer = 0;

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
            current = i;
            tabs.forEach((t, k) => {
                const on = k === i;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                panels[k].classList.toggle('is-active', on);
            });
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

        // pause while the pointer or the keyboard is inside
        const pause = () => { box.classList.add('is-paused'); window.clearTimeout(timer); };
        const resume = () => { box.classList.remove('is-paused'); play(); };
        box.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') pause(); });
        box.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') resume(); });
        box.addEventListener('focusin', pause);
        box.addEventListener('focusout', () => { if (!box.contains(document.activeElement)) resume(); });

        // swipe on touch screens
        const stage = box.querySelector('.vm__stage');
        let x0 = null;
        let y0 = 0;
        if (stage) {
            stage.addEventListener('touchstart', (e) => { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
            stage.addEventListener('touchend', (e) => {
                if (x0 === null) return;
                const dx = e.changedTouches[0].clientX - x0;
                const dy = e.changedTouches[0].clientY - y0;
                x0 = null;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) show((current + (dx < 0 ? 1 : -1) + tabs.length) % tabs.length, true);
            }, { passive: true });
        }

        // only run while on screen and while the tab of the browser is in front
        const onView = (on) => { visible = on; on ? play() : window.clearTimeout(timer); };
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((es) => es.forEach((en) => onView(en.isIntersecting)), { threshold: 0.4 }).observe(box);
        } else {
            onView(true);
        }
        document.addEventListener('visibilitychange', () => { if (document.hidden) window.clearTimeout(timer); else play(); });
        play();
    });
})();
