/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 04 Jul 2023 15:35:53 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.
importScripts("https://www.gstatic.com/firebasejs/8.2.5/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.2.5/firebase-messaging.js");

// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
// https://firebase.google.com/docs/web/setup#config-object
firebase.initializeApp({
    "apiKey": "AIzaSyDPdSOx28j6cJNuY1i2RsSW-Xy27uOOAsE",
    "authDomain": "aw-advantage.firebaseapp.com",
    "databaseURL": "https://aw-advantage-default-rtdb.asia-southeast1.firebasedatabase.app",
    "projectId": "aw-advantage",
    "storageBucket": "aw-advantage.appspot.com",
    "messagingSenderId": "19378435869",
    "appId": "1:19378435869:web:2ec64bc8f143b9ebf8d3f4",
    "measurementId": "G-7PZD284PCJ"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();
messaging.getToken({ vapidKey: 'BAGSpyOuFXWNwXjS56MTlWuz8KOqwzHhkjzrgs3Mok3Yv6OhH0yH2jTblyFckwoF2gnZA530PFEjuR424wdMpU0' }).then((currentToken) => {
    if (currentToken) {
        fetch("api/firebase/token", {
            method: 'POST',
            body: {
                token: currentToken
            }
        })
    } else {
        console.log('No registration token available. Request permission to generate one.');
    }
}).catch((err) => {
    console.log('An error occurred while retrieving token. ', err);
});

messaging.onBackgroundMessage((payload) => {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload
    );
    // Customize notification here
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/firebase-logo.png",
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
