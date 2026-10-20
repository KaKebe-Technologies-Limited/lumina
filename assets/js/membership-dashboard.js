/*!
 * membership-dashboard.js - the photo and proof-of-payment FILE FIELDS of the membership registration form (/membership/register) and of the member's private
 * page (/membership/me/{token}). Progressive enhancement ONLY: the server validates every upload (MemberFiles) and both forms work without JavaScript
 * (the native file input is then shown instead of the styled button). OWNER: DASHBOARD agent. No inline JS (CSP), no network requests: nothing is
 * uploaded by script, the browser submits the form as usual.
 *
 *   [data-upload="photo|proof"]        one file field (partial web/membership/_upload.php); data-max-bytes, data-min-side, data-msg-none|size|type|small|invalid|missing,
 *                                      data-chosen (text before the file name), data-alt-preview, data-initials-from (css selector of the name field)
 *     [data-upload-input]              the file input (visually hidden, its label is the button)
 *     [data-upload-img] [data-upload-ph] [data-upload-initials] [data-upload-icon]   preview image, placeholder, letters on the placeholder, icon on the placeholder
 *     [data-upload-status]             live region: "Chosen file: name (size)"
 *     [data-upload-clear]              button: forget the chosen file
 *     [data-upload-camera]             button (shown on touch devices): opens the camera picker; capture="user" is only a HINT and is removed again afterwards
 *   form[data-me-upload]               (private page) checks the file and the consent box before sending
 *   [data-me-status]                   the answer to the last action: moved into view and announced
 *
 * On the registration form assets/js/membership.js shows the messages: this script only records the problem on the input (data-upload-problem, read by
 * membership.js) and fires "upload:checked". Everywhere else it writes the .field__error itself.
 * The checks mirror the server (type, 5 MB, at least 300 x 300 pixels for the photo): they save a round trip, the server has the last word.
 */
(function () {
    'use strict';

    var roots = document.querySelectorAll('[data-upload]');
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    var IMAGE_EXT = /\.(jpe?g|png|webp)$/i;
    var PDF_EXT = /\.pdf$/i;

    var size = function (n) {
        return n >= 1048576 ? (n / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB';
    };

    // "Amina Akello" -> "AA", "Amina" -> "AM" (the same rule as the card: first letter of the first and the last word, two letters of a single word)
    var initialsOf = function (name) {
        var words = (name || '').split(/[\s-]+/).filter(function (w) { return w !== ''; });
        var letter = /[A-Za-zÀ-ɏͰ-ϿЀ-ӿ]/;
        try { letter = new RegExp('\\p{L}', 'u'); } catch (e) { /* older browsers keep the Latin range */ }
        words = words.filter(function (w) { return letter.test(w); });
        if (!words.length) { return ''; }
        var first = function (w) { var chars = Array.from(w.replace(/^[^A-Za-zÀ-ɏͰ-ϿЀ-ӿ]+/, '')); return (chars[0] || '').toUpperCase(); };
        if (words.length === 1) { return Array.from(words[0]).filter(function (ch) { return letter.test(ch); }).slice(0, 2).join('').toUpperCase(); }
        return first(words[0]) + first(words[words.length - 1]);
    };

    var ownError = function (root, input, message) {
        var box = root.querySelector('.field__error');
        if (!message) {
            if (box) { box.parentNode.removeChild(box); }
            root.classList.remove('field--error');
            input.removeAttribute('aria-invalid');
            var rest = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(function (id) { return id && id !== input.id + '-error'; });
            if (rest.length) { input.setAttribute('aria-describedby', rest.join(' ')); }
            return;
        }
        if (!box) {
            box = document.createElement('div');
            box.className = 'field__error';
            box.id = input.id + '-error';
            box.setAttribute('role', 'alert');
            root.appendChild(box);
        }
        box.textContent = message;
        root.classList.add('field--error');
        input.setAttribute('aria-invalid', 'true');
        var ids = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);
        if (ids.indexOf(box.id) === -1) { ids.unshift(box.id); }
        input.setAttribute('aria-describedby', ids.join(' '));
    };

    roots.forEach(function (root) {
        var kind = root.getAttribute('data-upload');
        var input = root.querySelector('[data-upload-input]');
        if (!input) { return; }
        var img = root.querySelector('[data-upload-img]');
        var ph = root.querySelector('[data-upload-ph]');
        var letters = root.querySelector('[data-upload-initials]');
        var iconBox = root.querySelector('[data-upload-icon]');
        var status = root.querySelector('[data-upload-status]');
        var clear = root.querySelector('[data-upload-clear]');
        var camera = root.querySelector('[data-upload-camera]');
        var max = parseInt(root.getAttribute('data-max-bytes'), 10) || 5242880;
        var minSide = parseInt(root.getAttribute('data-min-side'), 10) || 0;
        var inRegistration = !!input.closest('[data-mb-form]');
        var original = img ? { src: img.getAttribute('src') || '', hidden: img.hidden, alt: img.getAttribute('alt') || '' } : null;
        var objectUrl = '';
        var run = 0;

        var msg = function (key) { return root.getAttribute('data-msg-' + key) || ''; };

        // ---- the problem of the chosen file ------------------------------------------------------------------------------------
        var setProblem = function (message) {
            if (message) { input.setAttribute('data-upload-problem', message); } else { input.removeAttribute('data-upload-problem'); }
            if (inRegistration) {
                input.dispatchEvent(new CustomEvent('upload:checked', { bubbles: true }));       // membership.js shows (or clears) the message and the summary
            } else {
                ownError(root, input, message);
            }
        };

        // ---- preview ------------------------------------------------------------------------------------------------------------
        var releaseUrl = function () {
            if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = ''; }
        };
        var showOriginal = function () {
            releaseUrl();
            if (!img || !original) { return; }
            var has = original.src !== '' && !original.hidden;
            if (has) { img.setAttribute('src', original.src); } else { img.removeAttribute('src'); }
            img.hidden = !has;
            img.setAttribute('alt', original.alt);
            if (ph) { ph.classList.toggle('is-covered', has); }
        };
        var showPreview = function (url) {
            if (!img) { return; }
            img.setAttribute('src', url);
            img.hidden = false;
            img.setAttribute('alt', root.getAttribute('data-alt-preview') || '');
            if (ph) { ph.classList.add('is-covered'); }
        };
        var reset = function () {
            run++;
            setProblem('');
            showOriginal();
            if (status) { status.textContent = ''; }
            if (clear) { clear.hidden = true; }
            root.classList.remove('has-file');
        };

        var check = function (file) {
            var type = (file.type || '').toLowerCase();
            var okImage = IMAGE_TYPES.indexOf(type) !== -1 || (type === '' && IMAGE_EXT.test(file.name));
            var okPdf = kind === 'proof' && (type === 'application/pdf' || (type === '' && PDF_EXT.test(file.name)));
            if (!okImage && !okPdf) { return { text: msg('type'), pdf: false }; }
            if (file.size <= 0) { return { text: msg('invalid'), pdf: false }; }
            if (file.size > max) { return { text: msg('size'), pdf: false }; }
            return { text: '', pdf: okPdf };
        };

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) { reset(); return; }
            var mine = ++run;
            releaseUrl();
            if (status) { status.textContent = ((root.getAttribute('data-chosen') || '') + ' ' + file.name + ' (' + size(file.size) + ')').trim(); }
            if (clear) { clear.hidden = false; }
            root.classList.add('has-file');
            var verdict = check(file);
            if (verdict.text) { showOriginal(); setProblem(verdict.text); return; }
            if (verdict.pdf) { showOriginal(); setProblem(''); return; }       // a PDF has no picture: the document icon stays
            // a picture: show it and, for the headshot, make sure it is big enough (the server does the same with the real pixels)
            var url = URL.createObjectURL(file);
            var probe = new Image();
            probe.onload = function () {
                if (mine !== run) { URL.revokeObjectURL(url); return; }
                if (minSide && Math.min(probe.naturalWidth, probe.naturalHeight) < minSide) {
                    URL.revokeObjectURL(url);
                    showOriginal();
                    setProblem(msg('small'));
                    return;
                }
                objectUrl = url;
                showPreview(url);
                setProblem('');
            };
            probe.onerror = function () {
                URL.revokeObjectURL(url);
                if (mine !== run) { return; }
                showOriginal();
                setProblem(msg('invalid') || msg('type'));
            };
            probe.src = url;
        });

        if (clear) {
            clear.addEventListener('click', function () {
                input.value = '';
                reset();
                input.dispatchEvent(new Event('change', { bubbles: true }));       // lets the form re-check a field that is required
                input.focus();
            });
        }

        // ---- camera hint (phones and tablets) -----------------------------------------------------------------------------
        if (camera) {
            var coarse = !!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches) || (navigator.maxTouchPoints > 0 && /Android|iPhone|iPad|Mobile/i.test(navigator.userAgent || ''));
            camera.hidden = !coarse;
            var accept = input.getAttribute('accept');
            var restore = function () {
                input.removeAttribute('capture');
                if (accept !== null) { input.setAttribute('accept', accept); }
            };
            camera.addEventListener('click', function () {
                input.setAttribute('accept', 'image/*');
                input.setAttribute('capture', 'user');                  // a hint: the phone may offer the camera, the person can still pick a file
                input.addEventListener('change', restore, { once: true });
                window.addEventListener('focus', function () { window.setTimeout(restore, 600); }, { once: true });
                input.click();
            });
        }

        // ---- letters on the placeholder follow the name field (registration form) ------------------------------------------
        var from = root.getAttribute('data-initials-from');
        var source = from ? document.querySelector(from) : null;
        if (source && letters && iconBox) {
            var paint = function () {
                var s = initialsOf(source.value);
                letters.textContent = s;
                letters.hidden = s === '';
                iconBox.hidden = s !== '';
            };
            source.addEventListener('input', paint);
            paint();
        }
    });

    // ---- the private page: check before sending ----------------------------------------------------------------------------
    document.querySelectorAll('form[data-me-upload]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var bad = null;
            form.querySelectorAll('[data-upload]').forEach(function (root) {
                var input = root.querySelector('[data-upload-input]');
                if (!input) { return; }
                var problem = input.getAttribute('data-upload-problem') || '';
                if (!problem && !(input.files && input.files.length)) { problem = root.getAttribute('data-msg-none') || ''; }
                ownError(root, input, problem);
                if (problem && !bad) { bad = input; }
            });
            var consent = form.querySelector('input[name="photo_consent"]');
            if (consent) {
                var holder = consent.closest('.field');
                var missing = !consent.checked ? (consent.getAttribute('data-msg-consent') || '') : '';
                if (holder) { ownError(holder, consent, missing); }
                if (missing && !bad) { bad = consent; }
            }
            if (bad) {
                e.preventDefault();                                     // the shared double-submit guard ignores a prevented submit
                bad.focus();
                var field = bad.closest('.field');
                if (field) { field.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' }); }
            }
        });
        form.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'photo_consent' && e.target.checked) {
                var holder = e.target.closest('.field');
                if (holder) { ownError(holder, e.target, ''); }
            }
        });
    });

    // ---- the answer to the last action -------------------------------------------------------------------------------------------
    var answer = document.querySelector('[data-me-status]');
    var alertBox = answer ? answer.querySelector('.alert') : null;
    if (alertBox) {
        alertBox.setAttribute('tabindex', '-1');
        window.setTimeout(function () {
            try { alertBox.focus({ preventScroll: false }); } catch (err) { alertBox.focus(); }
        }, 60);
    }
})();
