/*!
 * Lumina Africa Foundation - public site behaviour. Vanilla ES2020, no dependencies. FOUNDATION-OWNED.
 * Hooks are data-attributes (documented in docs/ARCHITECTURE.md "JS hooks"):
 *   [data-site-header]                        sticky header: .is-scrolled after 8px
 *   [data-nav-toggle] / #site-nav [data-site-nav]   mobile menu; [data-submenu-toggle] expands a dropdown
 *   [data-accordion] > .accordion__item > [data-accordion-trigger] + [data-accordion-panel]   (data-accordion="single" = one open)
 *   [data-reveal]                             fade/slide in on scroll
 *   [data-count-to="150"] (+ data-count-prefix / data-count-suffix)   animated counter (text already holds the final value)
 *   form (all)                                double-submit guard (opt-out: data-no-guard). Button text while sending: data-loading-text
 *   a[data-lightbox="group"]                  image lightbox with prev/next/Esc
 *   [data-back-to-top]                        scroll-to-top button, appears after 600px
 *   select[data-autosubmit]                   submits its form on change (filters)
 *   [data-dismiss]                            closes the nearest .alert
 *   body[data-ga-id="G-XXXX"]                 loads Google Analytics (gtag) when set
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ------------------------------------------------------------------ sticky header
    const header = $('[data-site-header]');
    if (header) {
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // ------------------------------------------------------------------ mobile navigation + dropdowns
    const toggle = $('[data-nav-toggle]');
    const nav = $('[data-site-nav]');
    const closeNav = () => {
        if (!toggle || !nav) return;
        nav.classList.remove('is-open');
        document.body.classList.remove('nav-open');
        toggle.setAttribute('aria-expanded', 'false');
    };
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = !nav.classList.contains('is-open');
            nav.classList.toggle('is-open', open);
            document.body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeNav();
                $$('[data-submenu-toggle][aria-expanded="true"]').forEach((b) => closeSub(b));
            }
        });
        window.matchMedia('(min-width: 1280px)').addEventListener('change', closeNav);   // keep in step with the 'nav' breakpoint in scss/abstracts/_tokens.scss
        nav.addEventListener('click', (e) => {
            const a = e.target.closest('a');
            if (a && !a.hasAttribute('data-submenu-toggle')) closeNav();
        });
    }
    function closeSub(btn) {
        btn.setAttribute('aria-expanded', 'false');
        const sub = btn.parentElement.querySelector('.site-nav__sub');
        if (sub) sub.classList.remove('is-open');
    }
    $$('[data-submenu-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const open = btn.getAttribute('aria-expanded') !== 'true';
            $$('[data-submenu-toggle][aria-expanded="true"]').forEach((other) => other !== btn && closeSub(other));
            btn.setAttribute('aria-expanded', String(open));
            const sub = btn.parentElement.querySelector('.site-nav__sub');
            if (sub) sub.classList.toggle('is-open', open);
        });
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.site-nav__item.has-children')) {
            $$('[data-submenu-toggle][aria-expanded="true"]').forEach(closeSub);
        }
    });

    // ------------------------------------------------------------------ accordion
    $$('[data-accordion]').forEach((acc) => {
        const single = acc.dataset.accordion === 'single';
        const items = $$('.accordion__item', acc);
        const setOpen = (item, open) => {
            const btn = $('[data-accordion-trigger]', item);
            const panel = $('[data-accordion-panel]', item);
            if (!btn || !panel) return;
            btn.setAttribute('aria-expanded', String(open));
            panel.hidden = !open;
            item.classList.toggle('is-open', open);
        };
        items.forEach((item) => {
            const btn = $('[data-accordion-trigger]', item);
            if (!btn) return;
            btn.addEventListener('click', () => {
                const open = btn.getAttribute('aria-expanded') !== 'true';
                if (single && open) items.forEach((other) => other !== item && setOpen(other, false));
                setOpen(item, open);
            });
        });
    });

    // ------------------------------------------------------------------ reveal on scroll + counters
    const reveals = $$('[data-reveal]');
    const counters = $$('[data-count-to]');
    const formatCount = (n, el) => (el.dataset.countPrefix || '') + Math.round(n).toLocaleString('en') + (el.dataset.countSuffix || '');
    const runCounter = (el) => {
        const target = parseFloat(el.dataset.countTo);
        if (Number.isNaN(target)) return;
        if (reduceMotion) { el.textContent = formatCount(target, el); return; }
        const duration = 1400;
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = formatCount(target * eased, el);
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };
    if ('IntersectionObserver' in window && !reduceMotion) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                if (entry.target.hasAttribute('data-count-to')) runCounter(entry.target);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach((el, i) => { el.style.transitionDelay = `${Math.min(i % 4, 3) * 70}ms`; io.observe(el); });
        counters.forEach((el) => io.observe(el));
    } else {
        reveals.forEach((el) => el.classList.add('is-visible'));
        // counters keep their server-rendered final value
    }

    // ------------------------------------------------------------------ double-submit guard
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-guard') || e.defaultPrevented) return;
        if (form.dataset.submitting === '1') { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        $$('button[type="submit"], input[type="submit"]', form).forEach((btn) => {
            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');
            if (btn.dataset.loadingText && btn.tagName === 'BUTTON') btn.textContent = btn.dataset.loadingText;
        });
    });
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        $$('form[data-submitting]').forEach((f) => {
            delete f.dataset.submitting;
            $$('.is-loading', f).forEach((b) => { b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); });
        });
    });

    // ------------------------------------------------------------------ lightbox
    const lightboxLinks = $$('a[data-lightbox]');
    if (lightboxLinks.length) {
        let overlay = null; let group = []; let index = 0; let lastFocus = null;
        const icon = (d) => `<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
        const show = (i) => {
            index = (i + group.length) % group.length;
            const a = group[index];
            const img = $('.lightbox__img', overlay);
            img.src = a.href;
            img.alt = (a.querySelector('img') || {}).alt || '';
            $('.lightbox__caption', overlay).textContent = a.dataset.caption || img.alt;
            const multi = group.length > 1;
            $$('.lightbox__btn--prev, .lightbox__btn--next', overlay).forEach((b) => { b.hidden = !multi; });
        };
        let inerted = [];
        const setBackgroundInert = (on) => {
            if (on) {
                inerted = Array.from(document.body.children).filter((n) => n !== overlay && !n.hasAttribute('inert') && n.tagName !== 'SCRIPT');
                inerted.forEach((n) => n.setAttribute('inert', ''));
            } else {
                inerted.forEach((n) => n.removeAttribute('inert'));
                inerted = [];
            }
        };
        const close = () => {
            if (!overlay) return;
            setBackgroundInert(false);
            overlay.remove(); overlay = null;
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
            if (lastFocus) lastFocus.focus();
        };
        const onKey = (e) => {
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowRight') show(index + 1);
            else if (e.key === 'ArrowLeft') show(index - 1);
            else if (e.key === 'Tab' && overlay) {
                // focus trap: Tab / Shift+Tab cycle through the visible buttons of the dialog only
                const items = $$('.lightbox__btn', overlay).filter((b) => !b.hidden);
                if (!items.length) { e.preventDefault(); return; }
                const first = items[0]; const last = items[items.length - 1];
                if (!overlay.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
                else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        };
        const open = (a) => {
            lastFocus = a;
            group = lightboxLinks.filter((l) => l.dataset.lightbox === a.dataset.lightbox);
            overlay = document.createElement('div');
            overlay.className = 'lightbox';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-label', 'Image viewer');
            overlay.innerHTML = '<figure class="lightbox__figure"><img class="lightbox__img" alt=""><figcaption class="lightbox__caption"></figcaption></figure>'
                + `<button type="button" class="lightbox__btn lightbox__btn--close" aria-label="Close">${icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>')}</button>`
                + `<button type="button" class="lightbox__btn lightbox__btn--prev" aria-label="Previous image">${icon('<polyline points="15 18 9 12 15 6"/>')}</button>`
                + `<button type="button" class="lightbox__btn lightbox__btn--next" aria-label="Next image">${icon('<polyline points="9 18 15 12 9 6"/>')}</button>`;
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
            setBackgroundInert(true);
            show(group.indexOf(a));
            $('.lightbox__btn--close', overlay).focus();
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay || e.target.closest('.lightbox__btn--close')) close();
                else if (e.target.closest('.lightbox__btn--prev')) show(index - 1);
                else if (e.target.closest('.lightbox__btn--next')) show(index + 1);
            });
            document.addEventListener('keydown', onKey);
        };
        lightboxLinks.forEach((a) => a.addEventListener('click', (e) => {
            if (e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();
            open(a);
        }));
    }

    // ------------------------------------------------------------------ back to top
    const topBtn = $('[data-back-to-top]');
    if (topBtn) {
        const onScroll = () => topBtn.classList.toggle('is-visible', window.scrollY > 600);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        topBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));
    }

    // ------------------------------------------------------------------ misc
    document.addEventListener('change', (e) => {
        const el = e.target;
        if (el instanceof HTMLSelectElement && el.hasAttribute('data-autosubmit') && el.form) el.form.submit();
    });
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-dismiss]');
        if (btn) { const box = btn.closest('.alert'); if (box) box.remove(); }
    });

    // Google Analytics (only when an ID is configured in Settings)
    const ga = document.body.dataset.gaId;
    if (ga && /^G-[A-Z0-9]+$/.test(ga)) {
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', ga, { anonymize_ip: true });
        const s = document.createElement('script');
        s.async = true;
        s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga)}`;
        document.head.appendChild(s);
    }
})();
