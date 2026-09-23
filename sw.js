// Service Worker for Academic Management App
const CACHE_NAME = 'academic-app-v2';
const ASSETS_TO_CACHE = [
  'index.php',
  'dashboard.php',
  'manifest.php'
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      // Use addAll but catch individual failures if needed, 
      // but for simplicity we'll just try to cache these
      return cache.addAll(ASSETS_TO_CACHE).catch(err => console.log('Caching failed', err));
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  // For navigating actions (HTML pages), prefer network first
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match(event.request);
      })
    );
    return;
  }

  // For other assets, use cache first
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});
