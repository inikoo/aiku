/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:29:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

import './bootstrap';
import '../css/app.css';

import {createApp, h, watchEffect} from 'vue';
import {createInertiaApp, usePage} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import {i18nVue, loadLanguageAsync} from 'laravel-vue-i18n';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'pika';
import {createPinia} from 'pinia';
import {library} from '@fortawesome/fontawesome-svg-core';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {faSearch, faBell, faAngleUp} from '../private/pro-regular-svg-icons';
import {
    faHome,
    faDollyFlatbedAlt,
    faConveyorBeltAlt,
    faUsers,
    faUserHardHat, faBars,faUsersCog,faTachometerAltFast

} from '../private/pro-light-svg-icons';
import {useLayoutStore} from '@/Stores/layout';

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
            faTachometerAltFast
);

const initialiseApp = () => {
    const layout = useLayoutStore();
    if (usePage().props.value.language) {
        loadLanguageAsync(usePage().props.value.language);
    }
    watchEffect(() => {
        if (usePage().props.value.layout) {
            layout.navigation = usePage().props.value.layout.navigation ?? null;
        }
        if (usePage().props.value.organisation) {
            layout.organisation = usePage().props.value.organisation ?? null;
        }
    });
    return layout;
};

createInertiaApp({
                     title: (title) => `${title} - ${appName}`,
                     resolve: (name) => resolvePageComponent(
                         `./Pages/${name}.vue`,
                         import.meta.glob('./Pages/**/*.vue')),
                     setup({el, app, props, plugin}) {
                         return createApp({render: () => h(app, props)}).
                             use(plugin).
                             use(createPinia()).
                             use(ZiggyVue, Ziggy).
                             component('font-awesome-icon', FontAwesomeIcon).
                             use(i18nVue, {
                                 resolve: async lang => {
                                     const languages = import.meta.glob(
                                         '../../lang/*.json');
                                     return await languages[`../../lang/${lang}.json`]();
                                 },
                             }).
                             provide(
                                 'initialiseApp', initialiseApp,
                             ).
                             mount(el);
                     },
                 });

InertiaProgress.init({color: '#4B5563'});

