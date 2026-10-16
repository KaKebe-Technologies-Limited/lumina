/*!
 * Home page behaviour (B1 web-core). Vanilla ES2020.
 * [data-video-facade]: the video poster is a plain link to YouTube (works without JS). With JS the click swaps it for a
 * privacy-friendly youtube-nocookie.com iframe - nothing is requested from YouTube until the visitor presses play.
 */
(() => {
    'use strict';

    document.querySelectorAll('[data-video-facade]').forEach((box) => {
        const id = box.dataset.videoId || '';
        const play = box.querySelector('[data-video-play]');
        if (!play || !/^[A-Za-z0-9_-]{11}$/.test(id)) return;

        play.addEventListener('click', (e) => {
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
            e.preventDefault();
            const frame = document.createElement('iframe');
            frame.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0`;
            frame.title = box.dataset.videoTitle || '';
            frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            frame.allowFullscreen = true;
            frame.referrerPolicy = 'strict-origin-when-cross-origin';
            box.classList.add('is-playing');
            box.replaceChildren(frame);
            frame.focus();
        });
    });
})();
