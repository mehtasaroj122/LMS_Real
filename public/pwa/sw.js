const CACHE_VERSION = 'lms-pwa-v1';
const PRECACHE = `${CACHE_VERSION}-precache`;
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DATA_CACHE = `${CACHE_VERSION}-data`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/manifest.json',
    '/sw.js',
    '/favicon.ico',
    '/LMS logo.png',
    '/pwa/pwa.css',
    '/pwa/pwa.js',
    '/pwa/icons/icon-72x72.png',
    '/pwa/icons/icon-96x96.png',
    '/pwa/icons/icon-128x128.png',
    '/pwa/icons/icon-144x144.png',
    '/pwa/icons/icon-152x152.png',
    '/pwa/icons/icon-192x192.png',
    '/pwa/icons/icon-384x384.png',
    '/pwa/icons/icon-512x512.png',
    '/pwa/icons/maskable-192x192.png',
    '/pwa/icons/maskable-512x512.png',
    '/admin/CSS/admin-appLayout.css',
    '/admin/JS/admin-appLayout.js',
    '/staff/CSS/staff-appLayout.css',
    '/staff/JS/staff-appLayout.js',
    '/student/CSS/student-appLayout.css',
    '/student/JS/student-appLayout.js',
    '/shared/CSS/notification-list-animations.css',
    '/shared/JS/components/notification-list-animator.js'
];

const STATIC_EXTENSIONS = [
    '.css',
    '.js',
    '.mjs',
    '.png',
    '.jpg',
    '.jpeg',
    '.gif',
    '.webp',
    '.svg',
    '.ico',
    '.woff',
    '.woff2',
    '.ttf'
];

const SENSITIVE_PATH_PREFIXES = [
    '/admin',
    '/staff',
    '/student',
    '/dashboard',
    '/profile',
    '/change-password',
    '/profile-photos',
    '/login',
    '/logout',
    '/register',
    '/password',
    '/email',
    '/sanctum',
    '/notifications'
];

const CACHEABLE_DATA_PREFIXES = [
    '/api/books',
    '/api/categories',
    '/api/library'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PRECACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
    );
});

self.addEventListener('activate', (event) => {
    const expectedCaches = new Set([PRECACHE, STATIC_CACHE, DATA_CACHE, DYNAMIC_CACHE]);

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => Promise.all(
                cacheNames
                    .filter((cacheName) => !expectedCaches.has(cacheName))
                    .map((cacheName) => caches.delete(cacheName))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (!['http:', 'https:'].includes(url.protocol)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigation(request));
        return;
    }

    if (isStaticAsset(request, url)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    if (isDataRequest(url)) {
        event.respondWith(networkFirst(request, DATA_CACHE, { cacheResponse: isCacheableDataRequest(url) }));
        return;
    }

    if (url.origin === self.location.origin && !isSensitivePath(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request, DYNAMIC_CACHE));
    }
});

async function handleNavigation(request) {
    const url = new URL(request.url);

    try {
        const response = await fetch(request);

        if (response.ok && url.origin === self.location.origin && !isSensitivePath(url.pathname)) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached && !isSensitivePath(url.pathname)) {
            return cached;
        }

        return caches.match(OFFLINE_URL);
    }
}

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (isCacheableResponse(response)) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
    }

    return response;
}

async function networkFirst(request, cacheName, options = {}) {
    try {
        const response = await fetch(request);
        if (options.cacheResponse && isCacheableResponse(response)) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        throw error;
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);
    const networkFetch = fetch(request)
        .then((response) => {
            if (isCacheableResponse(response)) {
                cache.put(request, response.clone());
            }

            return response;
        })
        .catch(() => cached);

    return cached || networkFetch;
}

function isStaticAsset(request, url) {
    if (request.destination && ['style', 'script', 'worker', 'image', 'font'].includes(request.destination)) {
        return !url.pathname.startsWith('/profile-photos');
    }

    return STATIC_EXTENSIONS.some((extension) => url.pathname.toLowerCase().endsWith(extension)) &&
        !url.pathname.startsWith('/profile-photos');
}

function isDataRequest(url) {
    if (url.origin !== self.location.origin) {
        return false;
    }

    return url.pathname.startsWith('/api/') ||
        url.pathname.endsWith('/data') ||
        url.pathname.includes('/data/') ||
        url.pathname.endsWith('/stats') ||
        url.pathname.includes('/stats/');
}

function isCacheableDataRequest(url) {
    return CACHEABLE_DATA_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
}

function isSensitivePath(pathname) {
    return SENSITIVE_PATH_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));
}

function isCacheableResponse(response) {
    return response && response.ok && ['basic', 'cors', 'opaque'].includes(response.type);
}
