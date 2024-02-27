/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 05 Feb 2024 10:38:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import { fileURLToPath, URL } from "node:url";

export default defineConfig(
    {
        plugins: [
            laravel({
                        hotFile       : "public/retina.hot",
                        buildDirectory: "retina",
                        input         : "resources/js/app-retina.js",
                        ssr           : "resources/js/ssr-retina.js",
                        refresh       : true
                    }),
            vue({
                    template: {
                        transformAssetUrls: {
                            base           : null,
                            includeAbsolute: false
                        }
                    }
                }),
            i18n()
        ],
        ssr    : {
            noExternal: ["@inertiajs/server"]
        },
        resolve: {
            alias: {
                "@fad": fileURLToPath(
                    new URL("./private/fa/pro-duotone-svg-icons",
                            import.meta.url)),
                "@fal": fileURLToPath(
                    new URL("./private/fa/pro-light-svg-icons",
                            import.meta.url)),
                "@far": fileURLToPath(
                    new URL("./private/fa/pro-regular-svg-icons",
                            import.meta.url)),
                "@fas": fileURLToPath(
                    new URL("./private/fa/pro-solid-svg-icons",
                            import.meta.url))
            }
        },
        build  : {
            sourcemap    : true,
            devSourcemap : true,
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes("node_modules") &&
                            !id.includes("sentry")) {
                            return id.toString().
                                split("node_modules/")[1].split(
                                "/")[0].toString();
                        }
                    }
                }
            }
        }
    });
