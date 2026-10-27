// Extend the existing PWA without caching authenticated pages or uploads.
const CACHE_NAME = 'mutamark-erp-static-v2';
const base = new URL(self.registration.scope);
const buildPath = new URL('build/', base).pathname;
const publicPaths = new Set(['favicon.png', 'manifest.json'].map(path => new URL(path, base).pathname));
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME));
    self.skipWaiting();
});
self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys
        .filter(key => key.startsWith('mutamark-erp-') && key !== CACHE_NAME)
        .map(key => caches.delete(key)))));
    self.clients.claim();
});
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    const url = new URL(event.request.url);
    if (url.origin !== base.origin) return;
    const isPublicAsset = url.pathname.startsWith(buildPath) || publicPaths.has(url.pathname);
    if (isPublicAsset) {
        event.respondWith(caches.match(event.request).then(cached => cached || fetch(event.request).then(response => {
            if (response.ok && !response.redirected && !response.headers.get('content-type')?.includes('text/html')) {
                const copy = response.clone();
                event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy)));
            }
            return response;
        })));
    } else if (event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request).catch(() => new Response(
            'You are offline. Reconnect to access Mutamark ERP. No transaction has been submitted.',
            { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8', 'Cache-Control': 'no-store' } }
        )));
    }
});
