/*!
 * Lumina admin - gallery manager  (OWNER: B4 "admin-content"). Vanilla ES2020, no dependencies, no inline code. Loaded by admin/gallery/index.
 *   form[data-gallery-upload]   drag-and-drop / multi-file upload with a progress bar per file (one request per file, 3 at a time).
 *       data-upload-url, data-quick-url (".../__ID__/quick"), data-max-bytes, data-list-url; children [data-dropzone] [data-dropzone-input]
 *       [data-upload-queue] [data-upload-summary]. Each finished file shows description / caption fields that save themselves on change.
 *   [data-gallery-grid]         pictures; data-reorder-url, data-reorderable="1": drag by [data-drag-handle] or press [data-move="-1|1"]; saved at once.
 *   form[data-bulk-form]        selection ([data-bulk-item], [data-select-all]) enabling the bulk buttons [data-bulk-btn]; [data-bulk-count] shows the number.
 * Without JavaScript the plain multi-file form and the bulk form still work (server-side fallbacks).
 */
(() => {
    'use strict';
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const csrf = () => { const m = $('meta[name="csrf-token"]'); return m ? m.content : ''; };
    const el = (tag, attrs = {}, ...kids) => {
        const n = document.createElement(tag);
        for (const [k, v] of Object.entries(attrs)) { if (v === false || v === null || v === undefined) continue; n.setAttribute(k, v === true ? '' : String(v)); }
        kids.flat().forEach((k) => { if (k !== null && k !== undefined && k !== false) n.appendChild(typeof k === 'string' ? document.createTextNode(k) : k); });
        return n;
    };
    const TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    let uid = 0;

    // ------------------------------------------------------------------ upload queue
    const form = $('[data-gallery-upload]');
    if (form) {
        const zone = $('[data-dropzone]', form);
        const input = $('[data-dropzone-input]', form);
        const queue = $('[data-upload-queue]', form);
        const summary = $('[data-upload-summary]', form);
        const album = $('#up-album', form);
        const maxBytes = parseInt(form.dataset.maxBytes || '5242880', 10);
        const uploadUrl = form.dataset.uploadUrl || form.action;
        const quickUrl = form.dataset.quickUrl || '';
        let total = 0; let finished = 0; let okCount = 0; let active = 0;
        const waiting = [];

        const say = () => {
            if (!summary) return;
            summary.hidden = total === 0;
            if (finished < total) { summary.textContent = `Uploading ${finished + 1} of ${total}...`; return; }
            summary.textContent = `${okCount} of ${total} picture${total === 1 ? '' : 's'} uploaded.` + (okCount ? ' Add a description to each one below (it is saved automatically).' : '');
            if (okCount && form.dataset.listUrl) {
                const a = el('a', { href: form.dataset.listUrl, class: 'btn btn--outline btn--sm' }, 'Show the pictures that still need a description');
                summary.append(' ', a);
            }
        };

        const makeItem = (file) => {
            const n = ++uid;
            const thumb = el('span', { class: 'upload-item__thumb', 'aria-hidden': 'true' });
            if (TYPES.includes(file.type)) { const img = el('img', { alt: '' }); img.src = URL.createObjectURL(file); img.addEventListener('load', () => URL.revokeObjectURL(img.src)); thumb.appendChild(img); }
            const progress = el('progress', { max: '100', value: '0', 'aria-label': `Upload progress for ${file.name}` });
            const status = el('span', { class: 'upload-item__status', role: 'status' }, 'Waiting...');
            const body = el('div', { class: 'upload-item__body' }, el('span', { class: 'upload-item__name' }, file.name), progress, status);
            const li = el('li', { class: 'upload-item' }, thumb, body);
            queue.appendChild(li);
            return { n, file, li, body, progress, status, thumb };
        };

        const fail = (item, msg) => {
            item.li.classList.add('is-error');
            item.status.textContent = msg;
            item.progress.remove();
            finished++; say(); next();
        };

        const showFields = (item, data) => {
            item.li.classList.add('is-done');
            item.progress.remove();
            item.status.textContent = 'Uploaded. Describe the picture so everyone can understand it:';
            const id = data.id;
            const mk = (name, label, ph, req) => {
                const iid = `up-${item.n}-${name}`;
                const inp = el('input', { class: 'input', type: 'text', id: iid, name, maxlength: '250', placeholder: ph, autocomplete: 'off', 'aria-describedby': `${iid}-st` });
                const st = el('span', { class: 'field__help', id: `${iid}-st`, role: 'status' }, req ? 'Required for people who use screen readers.' : '');
                inp.addEventListener('change', async () => {
                    st.textContent = 'Saving...';
                    try {
                        const body = new URLSearchParams({ _token: csrf(), [name]: inp.value });
                        const res = await fetch(quickUrl.replace('__ID__', String(id)), { method: 'POST', body, credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } });
                        const out = await res.json();
                        if (!res.ok || !out.ok) throw new Error((out.errors && Object.values(out.errors)[0]) || 'Could not save.');
                        st.textContent = 'Saved.';
                    } catch (err) { st.textContent = err.message || 'Could not save. Try again.'; }
                });
                return el('div', { class: 'field' }, el('label', { class: 'field__label', for: iid }, label), inp, st);
            };
            item.body.appendChild(el('div', { class: 'upload-item__fields' }, mk('alt_text', 'Description (alt text)', 'For example: Students learning to type at a computer', true), mk('caption', 'Caption (optional)', 'Shown under the picture', false)));
        };

        const send = (item) => {
            active++;
            item.status.textContent = 'Uploading... 0%';
            const fd = new FormData();
            fd.append('file', item.file);
            fd.append('album', album ? album.value : '');
            fd.append('_token', csrf());
            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', (e) => {
                if (!e.lengthComputable) return;
                const pct = Math.round((e.loaded / e.total) * 100);
                item.progress.value = pct;
                item.status.textContent = pct >= 100 ? 'Processing...' : `Uploading... ${pct}%`;
            });
            xhr.addEventListener('load', () => {
                active--;
                let data = {};
                try { data = JSON.parse(xhr.responseText); } catch (_) { /* not json */ }
                if (xhr.status >= 200 && xhr.status < 300 && data.ok && data.items && data.items[0]) { okCount++; finished++; showFields(item, data.items[0]); say(); next(); return; }
                const msg = (data.errors && data.errors[0] && data.errors[0].error) || (xhr.status === 403 ? 'Your session has expired. Reload the page and try again.' : 'The upload failed. Please try again.');
                fail(item, msg);
            });
            xhr.addEventListener('error', () => { active--; fail(item, 'The connection was lost. Please try again.'); });
            xhr.send(fd);
        };

        function next() {
            while (active < 3 && waiting.length) send(waiting.shift());
        }

        const enqueue = (files) => {
            const list = Array.from(files || []);
            if (!list.length) return;
            if (finished >= total) { total = 0; finished = 0; okCount = 0; }       // a new batch
            list.forEach((file) => {
                total++;
                const item = makeItem(file);
                if (!TYPES.includes(file.type)) { fail(item, 'Only JPG, PNG, GIF or WebP pictures can be uploaded.'); return; }
                if (file.size > maxBytes) { fail(item, `That picture is larger than ${Math.round(maxBytes / 1048576)} MB.`); return; }
                waiting.push(item);
            });
            say();
            next();
        };

        if (input) input.addEventListener('change', () => { enqueue(input.files); input.value = ''; });
        if (zone) {
            ['dragenter', 'dragover'].forEach((t) => zone.addEventListener(t, (e) => { e.preventDefault(); zone.classList.add('is-dragover'); }));
            ['dragleave', 'dragend', 'drop'].forEach((t) => zone.addEventListener(t, () => zone.classList.remove('is-dragover')));
            zone.addEventListener('drop', (e) => { e.preventDefault(); enqueue(e.dataTransfer ? e.dataTransfer.files : []); });
        }
        // dropping a file anywhere else on the page must not navigate away from the admin
        ['dragover', 'drop'].forEach((t) => window.addEventListener(t, (e) => { if (e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files') && !(zone && zone.contains(e.target))) e.preventDefault(); }));
        form.addEventListener('submit', (e) => e.preventDefault());                 // with JavaScript the files go through the queue above (Enter in the album box must not post an empty form)
    }

    // ------------------------------------------------------------------ ordering
    const grid = $('[data-gallery-grid]');
    if (grid && grid.dataset.reorderable === '1') {
        const status = $('[data-order-status]');
        const url = grid.dataset.reorderUrl;
        const items = () => $$(':scope > [data-gallery-item]', grid);
        let timer = 0;
        const save = () => {
            clearTimeout(timer);
            timer = setTimeout(async () => {
                const body = new URLSearchParams({ _token: csrf() });
                items().forEach((li) => body.append('ids[]', li.dataset.id));
                try {
                    const res = await fetch(url, { method: 'POST', body, credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), Accept: 'application/json' } });
                    const out = await res.json();
                    if (!res.ok || !out.ok) throw new Error('failed');
                    if (status) status.textContent = 'Order saved.';
                } catch (_) { if (status) status.textContent = 'The order could not be saved. Reload the page and try again.'; }
            }, 350);
        };

        grid.addEventListener('click', (e) => {
            const b = e.target.closest('[data-move]');
            if (!b) return;
            e.preventDefault();
            const li = b.closest('[data-gallery-item]');
            const dir = parseInt(b.dataset.move, 10);
            if (dir < 0 && li.previousElementSibling) grid.insertBefore(li, li.previousElementSibling);
            else if (dir > 0 && li.nextElementSibling) grid.insertBefore(li.nextElementSibling, li);
            else { if (status) status.textContent = dir < 0 ? 'Already first on this page.' : 'Already last on this page.'; return; }
            b.focus();
            if (status) status.textContent = `Moved to position ${items().indexOf(li) + 1} of ${items().length} on this page. Saving...`;
            save();
        });

        let dragging = null;
        grid.addEventListener('mousedown', (e) => { const h = e.target.closest('[data-drag-handle]'); if (h) h.closest('[data-gallery-item]').draggable = true; });
        grid.addEventListener('dragstart', (e) => {
            const li = e.target.closest ? e.target.closest('[data-gallery-item]') : null;
            if (!li || !li.draggable) { return; }
            dragging = li; li.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', li.dataset.id); } catch (_) { /* old browsers */ }
        });
        grid.addEventListener('dragover', (e) => {
            if (!dragging) return;
            e.preventDefault();
            const over = e.target.closest('[data-gallery-item]');
            if (!over || over === dragging) return;
            const r = over.getBoundingClientRect();
            const before = e.clientX < r.left + r.width / 2;
            grid.insertBefore(dragging, before ? over : over.nextSibling);
        });
        grid.addEventListener('dragend', () => {
            if (!dragging) return;
            dragging.classList.remove('is-dragging'); dragging.draggable = false; dragging = null;
            if (status) status.textContent = 'Saving the new order...';
            save();
        });
    }

    // ------------------------------------------------------------------ bulk selection
    const bulk = $('form[data-bulk-form]');
    if (bulk) {
        const all = $('[data-select-all]', bulk);
        const count = $('[data-bulk-count]', bulk);
        const buttons = $$('[data-bulk-btn]', bulk);
        const albumBtn = $('[data-bulk-btn][value="album"]', bulk);
        const albumInput = $('input[name="album"]', bulk);
        const checks = () => $$('[data-bulk-item]');
        const refresh = () => {
            const sel = checks().filter((c) => c.checked);
            if (count) count.textContent = `${sel.length} selected`;
            buttons.forEach((b) => { b.disabled = sel.length === 0; });
            checks().forEach((c) => { const li = c.closest('[data-gallery-item]'); if (li) li.classList.toggle('is-selected', c.checked); });
            if (all) { all.checked = sel.length > 0 && sel.length === checks().length; all.indeterminate = sel.length > 0 && sel.length < checks().length; }
        };
        if (albumBtn && albumInput) {
            const label = () => { albumBtn.textContent = albumInput.value.trim() ? 'Move to album' : 'Remove from album'; };
            albumInput.addEventListener('input', label); label();
        }
        document.addEventListener('change', (e) => { if (e.target.matches && e.target.matches('[data-bulk-item]')) refresh(); });
        if (all) all.addEventListener('change', () => { checks().forEach((c) => { c.checked = all.checked; }); refresh(); });
        refresh();
    }
})();
