window.isUpdateAvailable = new Promise(function(resolve, reject) {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker
        // .register('/sw.js')
        .register('/service-worker.js')
        .then(reg => {
          reg.onupdatefound = () => {
            const installingWorker = reg.installing;
            installingWorker.onstatechange = () => {
              switch (installingWorker.state) {
                case 'installed':
                  if (navigator.serviceWorker.controller) {
                    resolve(true);
                  } else {
                    resolve(false);
                  }
                  break;
              }
            };
          };
          // console.log('ServiceWorker registration successful with scope: ', reg.scope);
        })
        .catch(err => window.console.error('[SW ERROR]', err));
    });
  }
});

var deferredPrompt;
window.addEventListener('beforeinstallprompt', function(event) {
  event.preventDefault();
  deferredPrompt = event;
  return false;
});

function addToHomeScreen() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(function(choiceResult) {
      console.log(choiceResult.outcome);
      if (choiceResult.outcome === 'dismissed') {
        console.log('User canceled installation');
      } else {
        console.log('User added to home screen');
      }
    });
    deferredPrompt = null;
  }
}
