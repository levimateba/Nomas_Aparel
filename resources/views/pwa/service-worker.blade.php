const CACHE = 'carenels-pwa-v{{ config('pwa.cache_version', '1') }}';

const PRECACHE = [
    '/',
    '/css/app.css',
    '/pwa/icon-192.png',
    '/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) =>
            Promise.allSettled(
                PRECACHE.map((path) => cache.add(new Request(path, { cache: 'reload' })))
            )
        )
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok && response.type === 'basic') {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(event.request, copy));
                }
                return response;
            })
            .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/')))
    );
});
