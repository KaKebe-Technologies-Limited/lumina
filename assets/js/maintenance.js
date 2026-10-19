/* Maintenance page: live countdown to the expected-back time (data-mt-countdown = unix seconds) and a reload when it is reached. */
(() => {
    'use strict';

    // Live date and time (Africa/Kampala), updated every second.
    const clock = document.querySelector('[data-mt-clock]');
    if (clock) {
        const tz = clock.getAttribute('data-tz') || 'Africa/Kampala';
        const dEl = clock.querySelector('[data-mt-date]');
        const tEl = clock.querySelector('[data-mt-time]');
        let dFmt; let tFmt;
        try {
            dFmt = new Intl.DateTimeFormat('en-GB', { timeZone: tz, weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            tFmt = new Intl.DateTimeFormat('en-GB', { timeZone: tz, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        } catch (e) { dFmt = null; }
        if (dFmt && dEl && tEl) {
            const paint = () => { const n = new Date(); dEl.textContent = dFmt.format(n); tEl.textContent = tFmt.format(n); };
            paint();
            const loop = () => { paint(); setTimeout(loop, 1000 - (Date.now() % 1000) + 5); };
            setTimeout(loop, 1000 - (Date.now() % 1000) + 5);
        }
    }

    const box = document.querySelector('[data-mt-countdown]');
    if (!box) { return; }
    const target = parseInt(box.getAttribute('data-mt-countdown'), 10) * 1000;
    const cells = {};
    ['d', 'h', 'm', 's'].forEach((k) => { cells[k] = box.querySelector('[data-u="' + k + '"]'); });
    const pad = (n) => String(n).padStart(2, '0');
    let timer;
    const tick = () => {
        let left = Math.max(0, Math.floor((target - Date.now()) / 1000));
        const d = Math.floor(left / 86400); left -= d * 86400;
        const h = Math.floor(left / 3600); left -= h * 3600;
        const m = Math.floor(left / 60); const s = left - m * 60;
        if (cells.d) { cells.d.textContent = String(d); }
        if (cells.h) { cells.h.textContent = pad(h); }
        if (cells.m) { cells.m.textContent = pad(m); }
        if (cells.s) { cells.s.textContent = pad(s); }
        if (target - Date.now() <= 0) {
            clearInterval(timer);
            setTimeout(() => location.reload(), 4000);      // the site may have reopened (auto-open) - check
        }
    };
    tick();
    timer = setInterval(tick, 1000);
})();
