/*
 * Admin > Social media > Share kit (docs/SOCIAL.md section 8). OWNER: IMAGES agent. Progressive enhancement: the page works without it (captions are plain textareas, the
 * pictures are links with the download attribute).
 *   [data-skit-copy="<id>"]        copies the value of that textarea / input
 *   [data-skit-copy-value="..."]   copies the value of the attribute (a picture address)
 *   [data-skit-label="..."]        what the confirmation says ("Facebook caption copied")
 *   [data-skit-status]             aria-live region that receives the confirmation
 *   textarea[data-skit-channel]    live counter: POST {channel, text} to data-count-url (the platform's own rule: X counts a link as 23), shown in [data-skit-count-for="<id>"]
 * navigator.clipboard when the page is secure, else a hidden textarea + execCommand('copy'). No inline JavaScript (CSP script-src 'self'); text is set with textContent only.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-skit]');
    if (!root) {
        return;
    }
    var status = root.querySelector('[data-skit-status]');

    function say(text) {
        if (!status) {
            return;
        }
        status.textContent = '';
        window.setTimeout(function () { status.textContent = text; }, 30);   // re-announces the same text
    }

    function legacyCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-1000px';
        document.body.appendChild(ta);
        ta.select();
        ta.setSelectionRange(0, text.length);
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
        document.body.removeChild(ta);
        return ok;
    }

    function copy(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(function () { return true; }, function () { return legacyCopy(text); });
        }
        return Promise.resolve(legacyCopy(text));
    }

    function flash(btn, ok, label) {
        var original = btn.getAttribute('data-skit-original');
        if (original === null) {
            original = btn.innerHTML;
            btn.setAttribute('data-skit-original', original);
        }
        say(ok ? (label || 'Text') + ' copied.' : 'Copying is not available here: select the text and copy it by hand.');
        btn.classList.toggle('is-copied', ok);
        window.clearTimeout(btn._skitTimer);
        btn._skitTimer = window.setTimeout(function () {
            btn.classList.remove('is-copied');
        }, 1800);
    }

    root.addEventListener('click', function (ev) {
        var btn = ev.target.closest ? ev.target.closest('[data-skit-copy], [data-skit-copy-value]') : null;
        if (!btn || !root.contains(btn)) {
            return;
        }
        var text = '';
        if (btn.hasAttribute('data-skit-copy')) {
            var src = document.getElementById(btn.getAttribute('data-skit-copy'));
            text = src ? src.value : '';
        } else {
            text = btn.getAttribute('data-skit-copy-value') || '';
        }
        if (text === '') {
            return;
        }
        ev.preventDefault();
        copy(text).then(function (ok) { flash(btn, ok, btn.getAttribute('data-skit-label')); });
    });

    // read-only fields select all on focus: one tap selects the link on a phone
    root.querySelectorAll('input[readonly]').forEach(function (inp) {
        inp.addEventListener('focus', function () { inp.select(); });
    });

    // ---- live counters ----------------------------------------------------------------------------------------------------
    var countUrl = root.getAttribute('data-count-url') || '';
    var csrf = (function () {
        if (window.LuminaAdmin && typeof window.LuminaAdmin.csrf === 'function') {
            return window.LuminaAdmin.csrf();
        }
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }());

    function show(area, length, limit, over) {
        var out = root.querySelector('[data-skit-count-for="' + area.id + '"]');
        if (!out) {
            return;
        }
        out.textContent = length + ' / ' + limit;
        out.classList.toggle('is-over', !!over);
        area.setAttribute('aria-invalid', over ? 'true' : 'false');
        var was = area._skitOver;
        area._skitOver = !!over;
        if (was !== undefined && was !== !!over) {            // ONE short message when the state changes (not one per keystroke), through the page's single status region
            window.clearTimeout(area._skitTell);
            var name = area.getAttribute('data-skit-name') || 'This caption';
            area._skitTell = window.setTimeout(function () {
                say(over ? name + ': ' + (length - limit) + ' character' + (length - limit === 1 ? '' : 's') + ' over the limit.' : name + ': back within the limit.');
            }, 700);
        }
    }

    function wireCounter(area) {
        var limit = parseInt(area.getAttribute('data-skit-limit') || '0', 10);
        var channel = area.getAttribute('data-skit-channel') || '';
        var timer = null;
        var seq = 0;
        area._skitOver = area.getAttribute('aria-invalid') === 'true';        // the state the server rendered: the first change is announced too
        function plain() {
            var n = area.value.length;
            show(area, n, limit, limit > 0 && n > limit);
        }
        area.addEventListener('input', function () {
            plain();
            if (!window.fetch || countUrl === '' || channel === '') {
                return;
            }
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                var mine = ++seq;
                fetch(countUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                    body: JSON.stringify({channel: channel === 'webhook' ? 'webhook' : channel, text: area.value})
                }).then(function (r) { return r.ok ? r.json() : (r.status === 422 ? r.json().catch(function () { return null; }) : null); }).then(function (d) {
                    if (d && mine === seq && typeof d.length === 'number') {
                        show(area, d.length, d.limit || limit, !!d.over);
                    } else if (d && mine === seq && d.over) {              // a text far too long for any platform is refused by the server: it is over
                        show(area, area.value.length, d.limit || limit, true);
                    }
                }).catch(function () { /* the plain count stays */ });
            }, 300);
        });
    }
    root.querySelectorAll('textarea[data-skit-channel]').forEach(wireCounter);
}());
