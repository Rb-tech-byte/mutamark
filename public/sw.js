// Mutamark ERP Service Worker Uninstaller & Cache Cleanup
self.addEventListener('install', event => {
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(keys.map(key => caches.delete(key))))
            .then(() => self.registration.unregister())
            .then(() => self.clients.claim())
    );
});

// Pass-through: Do not intercept any network traffic or offline responses
self.addEventListener('fetch', () => {
    // Let browser handle all requests directly
});
