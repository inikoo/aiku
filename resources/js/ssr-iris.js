/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 21:16:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import Layout from "@/Layouts/Public.vue";

const appName = "iris";

createServer(
    (page) =>
        createInertiaApp(
            {
                page,
                render : renderToString,
                title  : (title) => `${title} - ${appName}`,
                resolve: name => {
                    const pages = import.meta.glob(
                        "./Pages/Iris/**/*.vue",
                        { eager: true });
                    let page = pages[`./Pages/Iris/${name}.vue`];
                    page.default.layout = page.default.layout ||
                        Layout;
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
