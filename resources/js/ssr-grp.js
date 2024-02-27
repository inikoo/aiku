import { createSSRApp, h } from "vue";
import { renderToString } from "@vue/server-renderer";
import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import Layout from "@/Layouts/Grp.vue";

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
                        "./Pages/Grp/**/*.vue",
                        { eager: true });
                    let page = pages[`./Pages/Grp/${name}.vue`];
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
