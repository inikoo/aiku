/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:29:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';

const appName = window.document.getElementsByTagName('title')[0]?.innerText ||
    'pika';
import {createPinia} from 'pinia';
import {library} from '@fortawesome/fontawesome-svg-core';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {faSearch, faBell} from '../private/pro-regular-svg-icons';
import {
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
    faUserHardHat,

} from '../private/pro-light-svg-icons';

library.add(faSearch,
            faBell,
            faHome,
            faDollyFlatbedAlt,
            faConveyorBeltAlt,
            faUsers,
            faUserHardHat
            );

createInertiaApp({
                     title  : (title) => `${title} - ${appName}`,
                     resolve: (name) => resolvePageComponent(
                         `./Pages/${name}.vue`,
                         import.meta.glob('./Pages/**/*.vue')),
                     setup({el, app, props, plugin}) {
                         return createApp({render: () => h(app, props)}).
                             use(plugin).
                             use(createPinia()).
                             use(ZiggyVue, Ziggy).
                             component('font-awesome-icon', FontAwesomeIcon).
                             mount(el);
                     },
                 });

InertiaProgress.init({color: '#4B5563'});
