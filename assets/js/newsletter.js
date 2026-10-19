/*!
 * newsletter.js - the public newsletter pages (/newsletter, /newsletter/archive, /newsletter/{slug}). OWNER: WEB / NEWSLETTER agent.
 * Progressive enhancement ONLY: every page works without JavaScript. The "copy link" button of the share row is handled by
 * assets/js/pages/blog.js ([data-share]); the scroll-reveal of blocks and cards by assets/js/main.js ([data-reveal]).
 *
 * What this file adds:
 *  - a picture whose file has gone missing (an upload deleted after the issue was published) is taken out of the layout instead of leaving a
 *    broken-image icon with an empty frame: its <figure> (or the image itself) gets `hidden` and the class `is-broken`.
 *  - PDF issues: when the browser has no built-in PDF viewer (most phone browsers; navigator.pdfViewerEnabled === false) the embedded viewer is
 *    removed and the sentence "Your browser cannot show this PDF ..." is shown above the Download / Open buttons, which always work.
 */
(function () {
    'use strict';

    var issue = document.querySelector('[data-nl-issue]');
    if (!issue) {
        return;
    }

    function hideBroken(img) {
        var holder = img.closest('figure') || img;
        holder.classList.add('is-broken');
        holder.hidden = true;
        if (holder.classList.contains('nl-issue__cover')) {
            // no cover to overlap: the masthead gives back the room it had reserved
            var hero = issue.querySelector('.nl-hero--cover');
            if (hero) {
                hero.classList.remove('nl-hero--cover');
            }
        }
    }

    function watch(img) {
        // eager pictures can already have failed before this script ran; lazy ones are covered by the error event (a lazy picture that has not
        // started loading is not "broken", whatever `complete` says in some browsers)
        if (img.loading !== 'lazy' && img.complete && img.naturalWidth === 0 && (img.currentSrc || img.getAttribute('src'))) {
            hideBroken(img);
            return;
        }
        img.addEventListener('error', function () { hideBroken(img); }, { once: true });
    }

    Array.prototype.forEach.call(issue.querySelectorAll('img'), watch);

    // ---- PDF viewer: only where the browser can really show a PDF inside the page ---------------------------------------------------------
    var pdf = issue.querySelector('[data-nl-pdf]');
    if (pdf && 'pdfViewerEnabled' in navigator && navigator.pdfViewerEnabled === false) {
        pdf.classList.add('no-pdf-viewer');
        var viewer = pdf.querySelector('[data-nl-pdf-viewer]');
        if (viewer && !viewer.classList.contains('nl-pdf__viewer--placeholder')) {
            viewer.remove();                         // the iframe would only show an empty box or start a download
        }
    }

    // Printing: a button or link inside the issue prints WHERE it goes. The stylesheet spells out external addresses on its own; site-relative
    // links ("/programs") need the full address, which only the browser knows.
    window.addEventListener('beforeprint', function () {
        Array.prototype.forEach.call(issue.querySelectorAll('.nl-prose a[href], .nl-block__link a[href], .nl-featured__link a[href], .nl-block--button a[href]'), function (a) {
            var raw = a.getAttribute('href') || '';
            if (raw.charAt(0) === '#' || /^https?:/i.test(raw)) {
                return;
            }
            a.setAttribute('data-print-url', a.href);
        });
    });
}());
