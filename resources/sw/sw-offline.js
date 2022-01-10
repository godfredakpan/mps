importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.0.0/workbox-sw.js');

// workbox.setConfig({ debug: true });
// workbox.setConfig({ mode: 'production' });
workbox.core.skipWaiting();
workbox.core.clientsClaim();

// workbox.routing.registerRoute(
//     new RegExp('https://fonts.*|.(?:ttf|woff|woff2)$'),
//     new workbox.strategies.CacheFirst({ cacheName: 'fonts' })
// );

workbox.routing.registerRoute(new RegExp('/(?:icon-|js|css|img)'), new workbox.strategies.CacheFirst());
workbox.routing.registerRoute(new RegExp('.(?:jpg|jpeg|png|gif|svg|ttf|woff|woff2)$'), new workbox.strategies.CacheFirst());
workbox.routing.registerRoute(new RegExp('/'), new workbox.strategies.NetworkFirst({ cacheName: 'pages' }));
workbox.routing.registerRoute(new RegExp('.*(?:googleapis|gstatic).com.*$'), new workbox.strategies.NetworkFirst({ cacheName: 'google' }));

workbox.precaching.precacheAndRoute([{ url: 'offline', revision: Date.now() }].concat(self.__WB_MANIFEST));

const customHandler = async args => {
  try {
    return (
      (await workbox.strategies
        .NetworkFirst({
          cacheName: 'pages',
          plugins: [new workbox.expiration.ExpirationPlugin({ maxEntries: 200 })],
        })
        .handle(args)) || caches.match('offline')
    );
  } catch (error) {
    return caches.match('offline');
  }
};

const navigationRoute = new workbox.routing.NavigationRoute(customHandler, {
  blacklist: [new RegExp('/(login|password)')],
});

workbox.routing.registerRoute(navigationRoute);
