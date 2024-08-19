/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 04 Feb 2024 09:46:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import Layout from "@/Layouts/AikuPublic.vue";

const appName = "aiku";

createServer(
  (page) =>
    createInertiaApp(
      {
        page,
        render : renderToString,
        title  : (title) => `${title} - ${appName}`,
        resolve: name => {
          const pages = import.meta.glob(
            "./Pages/AikuPublic/**/*.vue",
            { eager: true });
          let page = pages[`./Pages/AikuPublic/${name}.vue`];
          page.default.layout = page.default.layout || Layout;
          return page;
        },

        setup({ App, props, plugin }) {
          return createSSRApp(
            { render: () => h(App, props) }).
            use(plugin).
            use(ZiggyVue, {
              ...page.props.ziggy,
              location: new URL(
                page.props.ziggy.location)
            });
        }
      })
);
