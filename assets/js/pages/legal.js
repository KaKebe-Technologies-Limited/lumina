/*!
 * Legal / error / unsubscribe pages (OWNER: B2). Progressive enhancement only.
 *   [data-history-back]   "Go back" button on error pages; shown only when there is somewhere to go back to.
 */
(() => {
    'use strict';

    document.querySelectorAll('[data-history-back]').forEach((btn) => {
        if (window.history.length <= 1) return;
        btn.hidden = false;
        btn.addEventListener('click', () => window.history.back());
    });
})();
