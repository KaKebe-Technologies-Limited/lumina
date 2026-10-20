/* Lumina service worker body (CFG is prepended by PwaController). Keeps only public static files; pages are always fetched live. */
const STATIC = CFG.version + '-static';
const PRIVATE = CFG.private.map(function (p) { return CFG.base + p; });

function isPrivate(path) {
  return PRIVATE.some(function (p) { return path.indexOf(p) === 0; });
}

self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(STATIC).then(function (c) { return c.addAll(CFG.precache); }).then(function () { return self.skipWaiting(); }));
});

self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (keys) {
    return Promise.all(keys.filter(function (k) { return k.indexOf('lumina-') === 0 && k !== STATIC; }).map(function (k) { return caches.delete(k); }));
  }).then(function () { return self.clients.claim(); }));
});

self.addEventListener('fetch', function (e) {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (isPrivate(url.pathname)) return;

  if (req.mode === 'navigate') {
    // pages are never stored: always live, and the offline page only when the network fails
    e.respondWith(fetch(req).catch(function () { return caches.match(CFG.offline).then(function (r) { return r || Response.error(); }); }));
    return;
  }

  if (url.pathname.indexOf(CFG.base + '/assets/') === 0) {
    // stale-while-revalidate for public static files
    e.respondWith(caches.open(STATIC).then(function (cache) {
      return cache.match(req).then(function (hit) {
        const net = fetch(req).then(function (res) {
          const cc = res.headers.get('Cache-Control') || '';
          if (res.ok && res.type === 'basic' && !/no-store|private/i.test(cc)) cache.put(req, res.clone());
          return res;
        }).catch(function () { return hit; });
        return hit || net;
      });
    }));
  }
});
