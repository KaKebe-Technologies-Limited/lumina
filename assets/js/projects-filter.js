/*!
 * Projects list filter (progressive enhancement). Without this file the filter buttons are plain links (?pillar=slug&status=planned) and the
 * server renders the matching cards. With it, a click filters in place: no reload, same URL scheme, Back/Forward work.
 *
 * Markup contract (app/views/web/projects/index.php), all copy is rendered by the server:
 *   [data-pi-filter]  root; data-base (list URL), data-label (text before the count), data-empty-text (text when nothing matches)
 *   a[data-pi-chip]   filter buttons; data-kind="pillar|status", data-value ('' = all); aria-current="true" marks the active one
 *   [data-pi-chip-count]   number inside a button (cards that button would show)
 *   [data-pi-group]   one pillar panel;  [data-pi-card][data-pillar][data-status]   one card (li)
 *   [data-pi-live]    polite status region (visually hidden) that announces the result;  [data-pi-empty]   "nothing here" block
 *
 * Every card stays in the DOM (SEO); filtering only toggles the `hidden` attribute. Focus stays on the button that was activated.
 */
(() => {
    'use strict';

    const root = document.querySelector('[data-pi-filter]');
    if (!root) return;

    const chips = Array.from(root.querySelectorAll('[data-pi-chip]'));
    const cards = Array.from(root.querySelectorAll('[data-pi-card]'));
    const groups = Array.from(root.querySelectorAll('[data-pi-group]'));
    const live = root.querySelector('[data-pi-live]');
    const empty = root.querySelector('[data-pi-empty]');
    if (!chips.length || !cards.length) return;

    const base = root.getAttribute('data-base') || window.location.pathname;
    const label = root.getAttribute('data-label') || '';
    const emptyText = root.getAttribute('data-empty-text') || '';
    const valid = { pillar: new Set(), status: new Set() };
    chips.forEach((chip) => {
        const kind = chip.dataset.kind;
        if (valid[kind] && chip.dataset.value) valid[kind].add(chip.dataset.value);
    });

    const state = { pillar: '', status: '' };

    const readUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const pillar = params.get('pillar') || '';
        const status = params.get('status') || '';
        state.pillar = valid.pillar.has(pillar) ? pillar : '';
        state.status = valid.status.has(status) ? status : '';
    };

    const buildUrl = (next) => {
        const params = new URLSearchParams();
        if (next.pillar) params.set('pillar', next.pillar);
        if (next.status) params.set('status', next.status);
        const qs = params.toString();
        return base + (qs ? '?' + qs : '');
    };

    const matches = (el) =>
        (!state.pillar || el.dataset.pillar === state.pillar) &&
        (!state.status || el.dataset.status === state.status);

    // number of cards a button shows: it keeps the OTHER active filter
    const countFor = (kind, value) => cards.filter((el) => {
        const pillar = kind === 'pillar' ? value : state.pillar;
        const status = kind === 'status' ? value : state.status;
        return (!pillar || el.dataset.pillar === pillar) && (!status || el.dataset.status === status);
    }).length;

    const apply = (announce) => {
        let visible = 0;
        const perGroup = new Map(groups.map((g) => [g, 0]));
        cards.forEach((el) => {
            const show = matches(el);
            el.hidden = !show;
            if (show) {
                visible += 1;
                const group = el.closest('[data-pi-group]');
                if (group) perGroup.set(group, (perGroup.get(group) || 0) + 1);
            }
        });
        groups.forEach((g) => { g.hidden = !perGroup.get(g); });
        if (empty) empty.hidden = visible > 0;

        chips.forEach((chip) => {
            const kind = chip.dataset.kind;
            const value = chip.dataset.value || '';
            const active = state[kind] === value;
            chip.classList.toggle('is-active', active);
            if (active) chip.setAttribute('aria-current', 'true'); else chip.removeAttribute('aria-current');
            // the link keeps working without JS (new tab, copy link): it always points at the state this click would produce
            chip.href = buildUrl(Object.assign({}, state, { [kind]: value })) + '#projects-list';
            const count = chip.querySelector('[data-pi-chip-count]');
            if (count) count.textContent = String(countFor(kind, value));
        });

        if (announce && live) {
            live.textContent = visible > 0 ? (label ? label + ': ' : '') + visible : emptyText;
        }
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', (event) => {
            // let the browser handle new-tab / download / middle-click gestures
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            const kind = chip.dataset.kind;
            const value = chip.dataset.value || '';
            if (state[kind] === value) return;
            state[kind] = value;
            apply(true);
            try {
                window.history.pushState({ pi: true }, '', buildUrl(state));
            } catch (err) { /* history unavailable: the filter still works */ }
        });
    });

    window.addEventListener('popstate', () => {
        readUrl();
        apply(true);
        // the focused element may have been hidden by the change: hand focus to the active pillar button
        const active = document.activeElement;
        if (active && active !== document.body && active.closest && active.closest('[hidden]')) {
            const current = chips.find((c) => c.getAttribute('aria-current') === 'true' && c.dataset.kind === 'pillar');
            if (current) current.focus({ preventScroll: true });
        }
    });

    readUrl();
    apply(false);
})();
