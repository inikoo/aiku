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


