/*!
 * admin-members-card.js - the member screen's proof-of-payment viewer, photo framing tool and card preview, plus a few helpers of the members list.
 * Progressive enhancement only: every screen works without it (the picture is shown at the width of the panel with links to the full-size file, the photo is
 * replaced by uploading a file, the PDF is a download). The server validates everything again. OWNER: STAFF agent. No inline JS (CSP).
 *
 *   [data-mviewer]            proof viewer: [data-mviewer-bar] [data-mviewer-stage] [data-mviewer-canvas] [data-mviewer-img] [data-mv="in|out|left|right|reset"] [data-mv-zoom]
 *                             mouse drag moves the picture, Ctrl + wheel zooms; with the stage focused: + - zoom, R / L rotate, 0 resets, arrows scroll
 *   [data-photo-form]         staff photo form: [data-photo-input] [data-photo-preview] (picture of the chosen file); [data-photo-frame] opens the framing dialog
 *   [data-photo-dialog]       <dialog> with [data-crop-stage] [data-crop-canvas] [data-crop-zoom] [data-crop-rotate="-1|1"] [data-crop-save] [data-crop-cancel] [data-crop-error]
 *                             the framed square is put into the file input and the ordinary form is submitted (CSRF, flash messages and rules stay the server's)
 *   details[data-card-pdf]    the issued card's PDF is loaded into an <iframe> only when the section is opened
 *   [data-open-payment]       link that opens the "Record a payment" block before jumping to it
 *   form[data-members-bulk]   asks before the bulk action "Issue membership cards"
 *   .flash-stack              pinned under the top bar when the page was opened at an anchor (#photo ...), so the result message is not scrolled away
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var clamp = function (n, lo, hi) { return Math.max(lo, Math.min(hi, n)); };

    // ------------------------------------------------------------------ the result message stays in view
    // The actions redirect to the panel they changed (#photo, #proof, #card, #payments), which scrolls past the message at the top of the page: pin it under the top bar.
    var flash = $('.flash-stack');
    if (flash && window.location.hash) { flash.classList.add('is-pinned'); }

    // ------------------------------------------------------------------ proof viewer (zoom, rotate, drag)
    $$('[data-mviewer]').forEach(function (root) {
        var bar = $('[data-mviewer-bar]', root);
        var stage = $('[data-mviewer-stage]', root);
        var canvas = $('[data-mviewer-canvas]', root);
        var img = $('[data-mviewer-img]', root);
        var label = $('[data-mv-zoom]', root);
        if (!bar || !stage || !canvas || !img) { return; }
        var zoom = 1;
        var turns = 0;
        var MIN = 0.5, MAX = 5;

        function layout(keepCentre) {
            var nw = img.naturalWidth, nh = img.naturalHeight;
            if (!nw || !nh) { return; }
            var fx = 0.5, fy = 0.5;
            if (keepCentre && canvas.offsetWidth > 0 && canvas.offsetHeight > 0) {
                fx = (stage.scrollLeft + stage.clientWidth / 2) / canvas.offsetWidth;
                fy = (stage.scrollTop + stage.clientHeight / 2) / canvas.offsetHeight;
            }
            var rotated = turns % 2 === 1;
            var vw = rotated ? nh : nw;                                       // size of the picture as it is shown (after the rotation)
            var fit = Math.min(Math.max(stage.clientWidth, 120) / vw, 2);       // fits the width of the panel, enlarged at most twice
            var s = fit * zoom;
            var w = nw * s, h = nh * s;
            canvas.style.width = Math.round(rotated ? h : w) + 'px';
            canvas.style.height = Math.round(rotated ? w : h) + 'px';
            img.style.width = Math.round(w) + 'px';
            img.style.height = Math.round(h) + 'px';
            img.style.transform = 'translate(-50%, -50%) rotate(' + (turns * 90) + 'deg)';
            if (label) { label.textContent = Math.round(zoom * 100) + '%'; }
            if (keepCentre) {
                stage.scrollLeft = fx * canvas.offsetWidth - stage.clientWidth / 2;
                stage.scrollTop = fy * canvas.offsetHeight - stage.clientHeight / 2;
            }
        }
        function setZoom(z) { zoom = clamp(Math.round(z * 100) / 100, MIN, MAX); layout(true); }
        function act(name) {
            if (name === 'in') { setZoom(zoom * 1.25); }
            else if (name === 'out') { setZoom(zoom / 1.25); }
            else if (name === 'left') { turns = (turns + 3) % 4; layout(false); }
            else if (name === 'right') { turns = (turns + 1) % 4; layout(false); }
            else if (name === 'reset') { zoom = 1; turns = 0; layout(false); stage.scrollLeft = 0; stage.scrollTop = 0; }
        }
        function start() {
            root.classList.add('is-enhanced');
            bar.hidden = false;
            layout(false);
        }
        $$('[data-mv]', bar).forEach(function (b) { b.addEventListener('click', function () { act(b.getAttribute('data-mv')); }); });
        stage.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey || e.altKey) { return; }
            var k = e.key;
            if (k === '+' || k === '=') { act('in'); }
            else if (k === '-' || k === '_') { act('out'); }
            else if (k === 'r' || k === 'R') { act('right'); }
            else if (k === 'l' || k === 'L') { act('left'); }
            else if (k === '0') { act('reset'); }
            else { return; }
            e.preventDefault();
        });
        stage.addEventListener('wheel', function (e) {
            if (!e.ctrlKey) { return; }
            e.preventDefault();
            act(e.deltaY < 0 ? 'in' : 'out');
        }, { passive: false });
        // drag with the mouse (touch screens scroll natively)
        var drag = null;
        stage.addEventListener('pointerdown', function (e) {
            if (e.pointerType !== 'mouse' || e.button !== 0 || !root.classList.contains('is-enhanced')) { return; }
            if (e.offsetX > stage.clientWidth || e.offsetY > stage.clientHeight) { return; }      // the scroll bars
            drag = { x: e.clientX, y: e.clientY, l: stage.scrollLeft, t: stage.scrollTop };
            stage.classList.add('is-dragging');
            try { stage.setPointerCapture(e.pointerId); } catch (err) { /* not important */ }
        });
        stage.addEventListener('pointermove', function (e) {
            if (!drag) { return; }
            stage.scrollLeft = drag.l - (e.clientX - drag.x);
            stage.scrollTop = drag.t - (e.clientY - drag.y);
        });
        var endDrag = function () { drag = null; stage.classList.remove('is-dragging'); };
        stage.addEventListener('pointerup', endDrag);
        stage.addEventListener('pointercancel', endDrag);
        var resizeTimer = null;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () { if (root.classList.contains('is-enhanced')) { layout(true); } }, 120);
        });
        if (img.complete && img.naturalWidth) { start(); } else { img.addEventListener('load', start, { once: true }); }
    });

    // ------------------------------------------------------------------ photo: preview of the chosen file + framing dialog
    $$('[data-photo-form]').forEach(function (form) {
        var input = $('[data-photo-input]', form);
        var preview = $('[data-photo-preview]', form);
        var previewImg = preview ? $('img', preview) : null;
        var previewName = preview ? $('[data-photo-preview-name]', preview) : null;
        var frameBtn = $('[data-photo-frame]');
        var dialog = $('[data-photo-dialog]');
        var current = $('[data-photo-current]');
        var objectUrl = null;
        var canDialog = dialog && typeof dialog.showModal === 'function';
        var canCrop = canDialog && typeof HTMLCanvasElement !== 'undefined' && typeof DataTransfer === 'function';
        if (!input) { return; }

        function chosen() { return input.files && input.files[0] ? input.files[0] : null; }
        function refreshFrameButton() {
            if (!frameBtn) { return; }
            frameBtn.hidden = !(canCrop && (chosen() || current));
        }
        input.addEventListener('change', function () {
            var f = chosen();
            if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
            if (f && /^image\//.test(f.type) && preview && previewImg) {
                objectUrl = URL.createObjectURL(f);
                previewImg.src = objectUrl;
                if (previewName) { previewName.textContent = f.name; }
                preview.hidden = false;
            } else if (preview) {
                preview.hidden = true;
            }
            refreshFrameButton();
        });
        refreshFrameButton();
        if (!canCrop || !frameBtn || !dialog) { return; }

        var stage = $('[data-crop-stage]', dialog);
        var cv = $('[data-crop-canvas]', dialog);
        var zoomInput = $('[data-crop-zoom]', dialog);
        var errorBox = $('[data-crop-error]', dialog);
        var saveBtn = $('[data-crop-save]', dialog);
        var ctx = cv.getContext('2d');
        var C = cv.width;                         // the square, in canvas pixels
        var src = null;                           // the picture being framed
        var turns = 0, z = 1, ox = 0, oy = 0;     // rotation (quarter turns), zoom, offset of the picture centre from the centre of the square
        var srcUrl = null;

        function geometry() {
            var rotated = turns % 2 === 1;
            var vw = rotated ? src.naturalHeight : src.naturalWidth;
            var vh = rotated ? src.naturalWidth : src.naturalHeight;
            var base = C / Math.min(vw, vh);       // the picture covers the square at zoom 1
            var s = base * z;
            return { s: s, hw: vw * s / 2, hh: vh * s / 2 };
        }
        function limit() {
            var g = geometry();
            ox = clamp(ox, -(g.hw - C / 2), g.hw - C / 2);
            oy = clamp(oy, -(g.hh - C / 2), g.hh - C / 2);
        }
        function paint(target, size) {
            var k = size / C;
            var g = geometry();
            var t = target.getContext('2d');
            t.fillStyle = '#ffffff';
            t.fillRect(0, 0, size, size);
            t.save();
            t.translate(size / 2 + ox * k, size / 2 + oy * k);
            t.rotate(turns * Math.PI / 2);
            t.scale(g.s * k, g.s * k);
            t.drawImage(src, -src.naturalWidth / 2, -src.naturalHeight / 2);
            t.restore();
        }
        function draw() { if (src) { limit(); paint(cv, C); } }
        function showError(msg) { if (errorBox) { errorBox.textContent = msg; errorBox.hidden = !msg; } }
        function reset(fromFile) {
            turns = 0; z = 1; ox = 0; oy = 0;
            if (zoomInput) { zoomInput.value = '1'; }
            if (fromFile && src) {
                // a picture picked from a disk is rarely square: show the upper part first, where the face usually is
                var g = geometry();
                oy = (g.hh - C / 2) * 0.55;
            }
            draw();
        }
        function open(url, fromFile) {
            showError('');
            var im = new Image();
            im.onload = function () {
                src = im;
                reset(fromFile);
                if (!dialog.open) { dialog.showModal(); }
                if (stage) { stage.focus(); }
            };
            im.onerror = function () { src = null; showError('That picture could not be opened here. Crop it on your computer and upload it instead.'); if (!dialog.open) { dialog.showModal(); } };
            im.src = url;
        }
        frameBtn.addEventListener('click', function () {
            var f = chosen();
            if (f) {
                if (srcUrl) { URL.revokeObjectURL(srcUrl); }
                srcUrl = URL.createObjectURL(f);
                open(srcUrl, true);
            } else if (current) {
                open(current.currentSrc || current.src, false);
            }
        });
        if (zoomInput) { zoomInput.addEventListener('input', function () { z = clamp(parseFloat(zoomInput.value) || 1, 1, 3); draw(); }); }
        $$('[data-crop-rotate]', dialog).forEach(function (b) {
            b.addEventListener('click', function () {
                turns = (turns + (parseInt(b.getAttribute('data-crop-rotate'), 10) > 0 ? 1 : 3)) % 4;
                ox = 0; oy = 0;
                draw();
            });
        });
        var drag = null;
        if (stage) {
            stage.addEventListener('pointerdown', function (e) {
                drag = { x: e.clientX, y: e.clientY, ox: ox, oy: oy };
                stage.classList.add('is-dragging');
                try { stage.setPointerCapture(e.pointerId); } catch (err) { /* not important */ }
            });
            stage.addEventListener('pointermove', function (e) {
                if (!drag) { return; }
                var k = C / stage.clientWidth;      // screen pixels to canvas pixels
                ox = drag.ox + (e.clientX - drag.x) * k;
                oy = drag.oy + (e.clientY - drag.y) * k;
                draw();
            });
            var end = function () { drag = null; stage.classList.remove('is-dragging'); };
            stage.addEventListener('pointerup', end);
            stage.addEventListener('pointercancel', end);
            stage.addEventListener('keydown', function (e) {
                var step = e.shiftKey ? 40 : 10;
                if (e.key === 'ArrowLeft') { ox -= step; }
                else if (e.key === 'ArrowRight') { ox += step; }
                else if (e.key === 'ArrowUp') { oy -= step; }
                else if (e.key === 'ArrowDown') { oy += step; }
                else if (e.key === '+' || e.key === '=') { z = clamp(z + 0.1, 1, 3); if (zoomInput) { zoomInput.value = String(z); } }
                else if (e.key === '-' || e.key === '_') { z = clamp(z - 0.1, 1, 3); if (zoomInput) { zoomInput.value = String(z); } }
                else if (e.key === 'r' || e.key === 'R') { turns = (turns + 1) % 4; ox = 0; oy = 0; }
                else if (e.key === 'l' || e.key === 'L') { turns = (turns + 3) % 4; ox = 0; oy = 0; }
                else { return; }
                e.preventDefault();
                draw();
            });
        }
        var closeDialog = function () { dialog.close(); frameBtn.focus(); };
        var cancel = $('[data-crop-cancel]', dialog);
        if (cancel) { cancel.addEventListener('click', closeDialog); }
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                if (!src) { return; }
                var out = document.createElement('canvas');
                out.width = out.height = 600;
                paint(out, 600);
                var finish = function (blob) {
                    if (!blob) { showError('The framed picture could not be prepared by this browser. Crop it on your computer and upload it instead.'); return; }
                    try {
                        var file = new File([blob], 'photo.jpg', { type: 'image/jpeg' });
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                    } catch (err) {
                        showError('This browser cannot attach the framed picture. Crop it on your computer and upload it instead.');
                        return;
                    }
                    dialog.close();
                    if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
                };
                if (out.toBlob) { out.toBlob(finish, 'image/jpeg', 0.92); } else { finish(null); }
            });
        }
        dialog.addEventListener('close', function () { if (srcUrl) { URL.revokeObjectURL(srcUrl); srcUrl = null; } });
    });

    // ------------------------------------------------------------------ the card as a PDF, loaded only when asked for
    $$('details[data-card-pdf]').forEach(function (d) {
        var holder = $('[data-card-frame]', d);
        var url = d.getAttribute('data-card-pdf');
        var loaded = false;
        d.hidden = false;                       // without JavaScript the section stays hidden (the PDF is a download)
        function load() {
            if (loaded || !holder || !url || !d.open) { return; }
            loaded = true;
            var f = document.createElement('iframe');
            f.src = url + '#toolbar=0&navpanes=0&view=Fit';
            f.title = 'The membership card as a PDF';
            f.loading = 'lazy';
            f.setAttribute('referrerpolicy', 'no-referrer');
            holder.appendChild(f);
        }
        d.addEventListener('toggle', load);
        load();
    });

    // ------------------------------------------------------------------ "record the payment" link in the proof panel
    document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('[data-open-payment]') : null;
        if (!a) { return; }
        var d = document.getElementById('add-payment');
        if (d) { d.open = true; }
    });

    // ------------------------------------------------------------------ members list: confirm "Issue membership cards"
    $$('form[data-members-bulk]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var action = $('[data-bulk-action]', form);
            if (e.defaultPrevented || !action || action.value !== 'card') { return; }
            var n = $$('[data-select-row]').filter(function (r) { return r.checked; }).length;
            if (n === 0) { return; }
            var text = 'Issue membership cards for ' + n + (n === 1 ? ' selected member' : ' selected members') + '? Only approved members whose balance is cleared (as set in the card settings) get one, and each of them is e-mailed their card.';
            if (!window.confirm(text)) { e.preventDefault(); }
        });
    });
})();
