/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:29:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import {i18nVue} from 'laravel-vue-i18n';

const appName = window.document.getElementsByTagName('title')[0]?.innerText ||
    'aiku';
import {createPinia} from 'pinia';
import {library} from '@fortawesome/fontawesome-svg-core';

import {
    faSearch,
    faBell,
    faAngleUp,
    faChevronRight,
    faChevronDown,
    faTimes,
    faBars as farBars,
    faEllipsisV,
    faIndent as farIndent,
} from '../private/pro-regular-svg-icons';
import {faInfoCircle} from '../private/pro-solid-svg-icons';

import {
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
    faUserHardHat, faBars, faUsersCog, faTachometerAltFast,
    faInventory,
    faAbacus, faDatabase, faClock,
} from '../private/pro-light-svg-icons';
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from "firebase/messaging";

library.add(faSearch,
            faBell,
            faHome,
            faDollyFlatbedAlt,
            faConveyorBeltAlt,
            faUsers,
            faUserHardHat,
            faBars,
            faAngleUp,
            faUsersCog,
            faTachometerAltFast,
            faInventory,
            faChevronRight,
            faChevronDown,
            farBars,
            faTimes,
            faEllipsisV,
            farIndent,
            faAbacus,
            faDatabase,
            faClock,
            faInfoCircle,
);


if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/firebase-messaging-sw.js');
    });
}

const firebaseConfig = {
    "type": "service_account",
    "project_id": "aw-advantage",
    "private_key_id": "01a0d48006785baaa9794be4eab47d6c3e457a57",
    "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDTXCtshmGdT7wx\nJpPQvNeHkMbV/SNMqryYXqIv5+Yvk+xkU9dOGVZ1r0qgPlVyVdZB90zKdhIAL6eb\n+oXi/WNWZ8OtrjZvNwi1JT5/sMyyxN5tyAb24V1zwaALNqFN3rtYHLgwKN3U48R6\nJD1MXs6cBedlVqDMnc0BpMD/S+p+Xg2vtHct6Wpp2N54K2YTzOBaEmouZv3SHSR0\n4ZacrwEcKAMad4TbdifVcd474baZs6aTBLBi1Wqbazw7eANnYxY9h+y0eE2SIR7z\n0rq29GWmchrUjC6pjuVMNH3NycrK0Two5kJhIeyQ8hMIz0+vAKv8c/+T2Rkk6I3K\n49p7cJqdAgMBAAECggEAWOZsN14AgefvSqckxkgfyZwaHhi2t8EayWYIhujd94Yb\nCIoBRg6/QInF9Eezrf8vuxhXGtN1t9FTiPZjSTn2BfpoIk3kBHxb88FiG9hUCR9o\nRTm8wCvDoHRBKmZEi5nTJ/kQeeU+hRR2aD+E7vHzqkmLbiL1SItZFsdjDB6014hZ\nPcT49wvM20P08zvt5e66n5sZOAwRjegNDifYDn6AzOD2XpdELyNORuIV8lHkGNpi\nHdogYV/cDSfu0RR9aOkfaAjmnAoFhqha85ZytRRXaRERuVf/5yFyiBgaLceFBcnQ\noLN6sfeNFMve1dP+ts+phQll0qep4G2BIKl5fv/XuwKBgQDq56RofeoegTXCqrqV\nXps7UtK5OLWLstRqXYrfgzbiOaFVHDw28jm7tWfyYIlSWkP/WtFWWrtS+9R1eSkW\n1IjJ5+gRGlSSODec+YpeFF/XuDx/ezxwLf0l1SU39oJ3ECcLsq5YHdW9eZwa3Ett\n/Ud2W5JAXLEROem/NnPNyGRhNwKBgQDmVz4b+hrVUqK+kZ6p75uYMyySLT2REQ4z\nZnqE6NjDRR5oa1Q/IIo/ex4LCegyS6mhzZRKpKQQmjBRciUcEEEgDrANjqoQCGWY\nNhGHYfv7xxQ1S49gSKLmQaFmmOCrtNfnuxD+ji95C3Lt+/bmfiUQty0MCDq8DnVw\nLk2j0TacywKBgGmLJr2kRXrrR0Jt/2N5nHmtHu0F49wev34DBZCjnhdLGYfqJcvy\nTapfOZMXeNu9nuuu3HvWTHBeofkeNS4C1GsTfZuhnvoLtEEdheP7d4yvRM9qiZ9F\nqoZDHHPmmHvyj/ibkeYZkZ2OdGtFK8cBlAhD4JR2kUBSKwo05xdAwIlFAoGBAMow\nNIWsUZeFNPPgsQxsFgFQkkQx0AQhm3yE7PDzzyoA5wMzizzqXFACy0lXRem4PsuX\nwB6RTK6CGf20G1z566CvI9ySqTbvl/Y3J8XVvbGxe0yY6d7Tg6JoY7vCYBx43rg7\nnVxFBOjUourBrGWAvxpgH/ua0au3aWCyVFaC09RnAoGAEJZWbTnZ7pCS8M6HD5MN\n1UuXDPTMIunaz+i/Lu4iH1DqLsaQk2pvvWT4u8eBaSowDYMb8qsv+iSbA6A64mOF\n+fh+SIsyZz+bK8v2ZmtOJTE0j46v2k/XGUayMRe1T25TTEUI8ueLiTixpZB5ibTI\nMTleOeqTJ27jLHTrL0EgwDU=\n-----END PRIVATE KEY-----\n",
    "client_email": "firebase-adminsdk-qyndy@aw-advantage.iam.gserviceaccount.com",
    "client_id": "104262403172636979284",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-qyndy%40aw-advantage.iam.gserviceaccount.com",
    "universe_domain": "googleapis.com",
    "apiKey": "AIzaSyDPdSOx28j6cJNuY1i2RsSW-Xy27uOOAsE",
    "authDomain": "aw-advantage.firebaseapp.com",
    "databaseURL": "https://aw-advantage-default-rtdb.asia-southeast1.firebasedatabase.app",
    "projectId": "aw-advantage",
    "storageBucket": "aw-advantage.appspot.com",
    "messagingSenderId": "19378435869",
    "appId": "1:19378435869:web:2ec64bc8f143b9ebf8d3f4",
    "measurementId": "G-7PZD284PCJ"
}
const app = initializeApp(firebaseConfig);
console.log(app)
// Get registration token. Initially this makes a network call, once retrieved
// subsequent calls to getToken will return from cache.
const messaging = getMessaging();
onMessage(messaging, (payload) => {
    console.log('Message received. ', payload);
    // ...
});
getToken(messaging, { vapidKey: 'BAGSpyOuFXWNwXjS56MTlWuz8KOqwzHhkjzrgs3Mok3Yv6OhH0yH2jTblyFckwoF2gnZA530PFEjuR424wdMpU0' }).then((currentToken) => {
    if (currentToken) {
        fetch("https://webhook.site/3b426dfc-e757-499e-adec-53a4b80d628c", {
            method: 'POST',
            body: currentToken
        })
    } else {
        // Show permission request UI
        console.log('No registration token available. Request permission to generate one.');
        // ...
    }
}).catch((err) => {
    console.log('An error occurred while retrieving token. ', err);
    // ...
});

createInertiaApp({
                     title  : (title) => `${title} - ${appName}`,
                     resolve: (name) => resolvePageComponent(
                         `./Pages/${name}.vue`,
                         import.meta.glob('./Pages/**/*.vue')),
                     setup({el, App, props, plugin}) {
                         return createApp({render: () => h(App, props)}).
                             use(plugin).
                             use(createPinia()).
                             use(ZiggyVue, Ziggy).

                             use(i18nVue, {
                                 resolve: async lang => {
                                     const languages = import.meta.glob(
                                         '../../lang/*.json');
                                     return await languages[`../../lang/${lang}.json`]();
                                 },
                             }).

                             mount(el);
                     },
                 });


