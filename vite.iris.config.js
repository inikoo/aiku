/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 20:46:11 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import { fileURLToPath, URL } from "node:url";
import {codecovVitePlugin} from "@codecov/vite-plugin";

export default defineConfig(
    {
        plugins: [
            laravel({
                        hotFile       : "public/iris.hot",
                        buildDirectory: "iris",
                        input         : "resources/js/app-iris.js",
                        ssr           : "resources/js/ssr-iris.js",
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
            i18n(),
            codecovVitePlugin({
                enableBundleAnalysis: process.env.CODECOV_TOKEN !== undefined,
                bundleName: "iris",
                uploadToken: process.env.CODECOV_TOKEN,
            })
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
