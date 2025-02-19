/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 21:16:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { createSSRApp, h } from "vue"
import { renderToString } from "@vue/server-renderer"
import { createInertiaApp } from "@inertiajs/vue3"
import createServer from "@inertiajs/vue3/server"
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers"
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m"
import IrisLayout from "@/Layouts/Iris.vue"
import {createPinia} from 'pinia';
import Notifications from '@kyvg/vue3-notification';
// import FloatingVue from 'floating-vue'
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';
import {i18nVue} from 'laravel-vue-i18n';

const appName = "iris"

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{gray.50}',
            100: '{gray.100}',
            200: '{gray.200}',
            300: '{gray.300}',
            400: '{gray.400}',
            500: '{gray.500}',
            600: '{gray.600}',
            700: '{gray.700}',
            800: '{gray.800}',
            900: '{gray.900}',
            950: '{gray.950}'
        }
    }
});

createServer(
    (page) =>
        createInertiaApp({
            page,
            render : renderToString,
            title  : (title) => `${title} - ${appName}`,
            resolve: name => {
                const irisPages = import.meta.glob('./Pages/Iris/**/*.vue', { eager: true })
                // const retinaPages = import.meta.glob('./Pages/Retina/**/*.vue', { eager: true }) // need improvement in the future
                let page = irisPages[`./Pages/Iris/${name}.vue`]
                console.log('page', page)
                if (!page) console.error(`File './Pages/Iris/${name}.vue' is not exist`)
                page.default.layout = page.default.layout || IrisLayout
                return page
            },
            setup({ el, App, props, plugin }) {
                const app = createSSRApp({ render: () => h(App, props)})

                if (import.meta.env.VITE_SENTRY_DSN) {
                    Sentry.init({
                        app,
                        dsn: import.meta.env.VITE_SENTRY_DSN,
                        environment: import.meta.env.VITE_APP_ENV,
                        release: import.meta.env.VITE_RELEASE,
                        replaysSessionSampleRate: 0.1,
                        replaysOnErrorSampleRate: 1.0,
                        integrations: [new Sentry.Replay()]
                    })
                }

                app.use(plugin)
                    .use(createPinia())
                    .use(ZiggyVue, {
                        ...page.props.ziggy,
                        location: new URL(
                            page.props.ziggy.location)
                    })
                    .use(Notifications)
                    // .use(FloatingVue)
                    .use(PrimeVue, {
                        theme: {
                            preset: MyPreset,
                            options: {
                                darkModeSelector: '.my-app-dark',  // dark mode of Primevue depends .my-add-dark in <html>
                            }
                        }
                    })
                    .use(i18nVue, {
                        resolve: async (lang) => {
                            const languages = import.meta.glob(
                                '../../lang/*.json')
                            return await languages[`../../lang/${lang}.json`]()
                        },
                    })

                return app

            },
            progress: {
                color: '#4B5563',
            },
        })
)
