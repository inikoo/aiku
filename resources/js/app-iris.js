/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 20:51:16 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { BrowserAgent } from "@newrelic/browser-agent/loaders/browser-agent";
import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import {i18nVue} from 'laravel-vue-i18n';
import Notifications from '@kyvg/vue3-notification';
import {createPinia} from 'pinia';
import * as Sentry from '@sentry/vue';
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import IrisLayout from '@/Layouts/Iris.vue'
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import { definePreset } from '@primevue/themes';

if (import.meta.env.VITE_NEW_RELIC_BROWSER_IRIS_AGENT_ID) {
  const options = {
    init         : {
      distributed_tracing: { enabled: true },
      privacy            : { cookies_enabled: true },
      ajax               : { deny_list: ["bam.nr-data.net"] }
    },
    info         : {
      beacon       : "bam.nr-data.net",
      errorBeacon  : "bam.nr-data.net",
      licenseKey   : import.meta.env.VITE_NEW_RELIC_BROWSER_LICENCE_KEY,
      applicationID: import.meta.env.VITE_NEW_RELIC_BROWSER_IRIS_AGENT_ID,
      sa           : 1
    },
    loader_config: {
      accountID    : import.meta.env.VITE_NEW_RELIC_BROWSER_ACCOUNT_ID,
      trustKey     : import.meta.env.VITE_NEW_RELIC_BROWSER_ACCOUNT_ID,
      agentID      : import.meta.env.VITE_NEW_RELIC_BROWSER_IRIS_AGENT_ID,
      licenseKey   : import.meta.env.VITE_NEW_RELIC_BROWSER_LICENCE_KEY,
      applicationID: import.meta.env.VITE_NEW_RELIC_BROWSER_IRIS_AGENT_ID
    }
  };

  new BrowserAgent(options);
}

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

createInertiaApp(
    {

        resolve: name => {
            const irisPages = import.meta.glob('./Pages/Iris/**/*.vue', { eager: true })
            const retinaPages = import.meta.glob('./Pages/Retina/**/*.vue', { eager: true }) // need improvement in the future
            let page = irisPages[`./Pages/Iris/${name}.vue`] ? irisPages[`./Pages/Iris/${name}.vue`] : retinaPages[`./Pages/Retina/${name}.vue`] // need improvement in the future
            console.log('page', page)
            if(!page) console.error(`File './Pages/Iris/${name}.vue' is not exist`)
            page.default.layout = page.default.layout || IrisLayout
            return page
        },
      setup({el, App, props, plugin}) {
        const app = createApp({render: () => h(App, props)});
        if (import.meta.env.VITE_SENTRY_DSN) {
          Sentry.init({
                        app,
                        dsn                     : import.meta.env.VITE_SENTRY_DSN,
                        environment             : import.meta.env.VITE_APP_ENV,
                          release: import.meta.env.VITE_RELEASE,
                        replaysSessionSampleRate: 0.1,
                        replaysOnErrorSampleRate: 1.0,
                        integrations: [new Sentry.Replay()]
                      });
        }


        app.use(plugin)
            .use(createPinia())
            .use(ZiggyVue, Ziggy)
            .use(Notifications)
            .use(FloatingVue)
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
                    '../../lang/*.json');
                return await languages[`../../lang/${lang}.json`]();
              },
            }).
            mount(el);

      },
      progress: {
        color: '#4B5563',
      },
    });
