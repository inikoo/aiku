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
import firebaseConfig from "../private/firebase/aiku-firebase.json";
import firebaseCredential from "../private/firebase/aiku-firebase.json";

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

if(process.env.NODE_ENV !== "development") {
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging();

    onMessage(messaging, (payload) => {
        console.log('Message received. ', payload);
        // ...
    });

    getToken(messaging, { vapidKey: firebaseCredential.vapidKey }).then((currentToken) => {
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
}

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


