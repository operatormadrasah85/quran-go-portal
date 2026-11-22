const SHELL_CACHE = 'alquran-go-shell-v6';
const FONT_CACHE = 'alquran-go-fonts-v5';
const AUDIO_CACHE = 'alquran-go-audio-v5';

const SHELL_ASSETS = [
  './',
  './index.html',
  './app.js',
  './manifest.json',
  './icon.svg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then((cache) => cache.addAll(SHELL_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  const allowed = [SHELL_CACHE, FONT_CACHE, AUDIO_CACHE];
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((key) => {
          if (!allowed.includes(key)) return caches.delete(key);
        })
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // 1. Proxy PHP lokal & API Teks: lewatkan langsung ke jaringan (disimpan di IndexedDB)
  if (
    url.pathname.endsWith('audio_proxy.php') ||
    url.hostname.includes('api.alquran.cloud') ||
    url.hostname.includes('api.quran.com')
  ) {
    event.respondWith(fetch(req));
    return;
  }

  // 2. CDN Audio Eksternal (fallback untuk static hosting tanpa PHP)
  if (
    url.hostname.includes('cdn.islamic.network') ||
    url.hostname.includes('verses.quran.com') ||
    url.hostname.includes('audio.qurancdn.com') ||
    url.hostname.includes('everyayah.com')
  ) {
    event.respondWith(
      caches.open(AUDIO_CACHE).then(async (cache) => {
        const cached = await cache.match(req.url);
        if (cached) return cached;
        return fetch(req);
      })
    );
    return;
  }

  // 3. Google Fonts (Stale-While-Revalidate)
  if (url.hostname.includes('fonts.googleapis.com') || url.hostname.includes('fonts.gstatic.com')) {
    event.respondWith(
      caches.open(FONT_CACHE).then(async (cache) => {
        const cached = await cache.match(req);
        const fetchPromise = fetch(req)
          .then((res) => {
            if (res && (res.status === 200 || res.type === 'opaque')) {
              cache.put(req, res.clone());
            }
            return res;
          })
          .catch(() => cached);
        return cached || fetchPromise;
      })
    );
    return;
  }

  // 4. App Shell Lokal (Network-First)
  if (url.origin === self.location.origin) {
    event.respondWith(
      fetch(req)
        .then((res) => {
          if (res && res.status === 200) {
            const copy = res.clone();
            caches.open(SHELL_CACHE).then((cache) => cache.put(req, copy));
          }
          return res;
        })
        .catch(() =>
          caches.match(req, { ignoreSearch: true }).then((c) => c || caches.match('./index.html'))
        )
    );
  }
});
