/**
 * Firebase Cloud Messaging service worker.
 *
 * Required at the site root for web push: the dashboard pages call
 * messaging.getToken(), which registers /firebase-messaging-sw.js. Without
 * this file token registration fails and background notifications never show.
 */
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: 'AIzaSyB-pDw6ncq9BnPTUfgSBTOZ8qTzmu46_Wk',
    authDomain: 'ultt-ce8f2.firebaseapp.com',
    databaseURL: 'https://ultt-ce8f2.firebaseio.com',
    projectId: 'ultt-ce8f2',
    storageBucket: 'ultt-ce8f2.appspot.com',
    messagingSenderId: '1027654555881',
    appId: '1:1027654555881:web:646826f82459642ab5f878'
});

const messaging = firebase.messaging();

// Background data messages (notification messages are displayed by the
// browser automatically from the webpush.notification payload).
messaging.onBackgroundMessage(function (payload) {
    const title = (payload.notification && payload.notification.title) || 'CargoTaxi';
    const options = {
        body: (payload.notification && payload.notification.body) || '',
        icon: '/images/logo.png',
        data: payload.data || {}
    };
    self.registration.showNotification(title, options);
});
