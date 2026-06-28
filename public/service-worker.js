const CACHE_NAME = 'kirada-v5';
const OFFLINE_URL = '/offline';

// Assets to cache on install (app shell only — no dynamic/auth pages)
const APP_SHELL = [
    '/offline',
    '/manifest.json',
];

// Static asset patterns (cache-first)
const STATIC_ASSET_PATTERNS = [
    /\/build\//,       // Vite build assets (CSS, JS)
    /\/icons\//,       // PWA icons
    /\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|webp|ico)$/i,
];

// Auth/session routes that should NEVER be cached
const NO_CACHE_ROUTES = [
    /\/login/,
    /\/logout/,
    /\/register/,
    /\/password/,
    /\/forgot-password/,
    /\/reset-password/,
    /\/two-factor/,
    /\/user\//,        // profile/settings
    /\/livewire\//,    // Livewire internal requests
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Only handle same-origin requests
    if (url.origin !== self.location.origin) return;

    // Never cache auth/session routes
    if (NO_CACHE_ROUTES.some((pattern) => pattern.test(url.pathname))) {
        return;
    }

    // Check if this is a static asset
    const isStaticAsset = STATIC_ASSET_PATTERNS.some((pattern) =>
        pattern.test(url.pathname)
    );

    if (isStaticAsset) {
        // Cache-first for static assets
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    // Update cache in background
                    fetch(event.request).then((response) => {
                        if (response.ok) {
                            caches.open(CACHE_NAME).then((cache) =>
                                cache.put(event.request, response)
                            );
                        }
                    }).catch(() => {});
                    return cached;
                }

                // Not cached — fetch and cache
                return fetch(event.request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) =>
                            cache.put(event.request, clone)
                        );
                    }
                    return response;
                }).catch(() => caches.match(OFFLINE_URL));
            })
        );
        return;
    }

    // Network-first for app routes (HTML pages)
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Only cache successful HTML responses
                if (response.ok && response.headers.get('content-type')?.includes('text/html')) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) =>
                        cache.put(event.request, clone)
                    );
                }
                return response;
            })
            .catch(() => {
                // Try cache first, then offline page
                return caches.match(event.request).then((cached) => {
                    if (cached) return cached;
                    return caches.match(OFFLINE_URL);
                });
            })
    );
});
