/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 12 Sep 2023 20:46:11 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import i18n from "laravel-vue-i18n/vite";
import inertia from "./resources/scripts/vite/inertia-layout";
import { fileURLToPath, URL } from "node:url";

export default defineConfig(
  {
    plugins: [
      inertia(),
      laravel({
                hotFile       : "public/public.hot",
                buildDirectory: "public",
                input         : "resources/js/app-public.js",
                ssr           : "resources/js/ssr-public.js",
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
          new URL("./private/fa/pro-duotone-svg-icons", import.meta.url)),
        "@fal": fileURLToPath(
          new URL("./private/fa/pro-light-svg-icons", import.meta.url)),
        "@far": fileURLToPath(
          new URL("./private/fa/pro-regular-svg-icons", import.meta.url)),
        "@fas": fileURLToPath(
          new URL("./private/fa/pro-solid-svg-icons", import.meta.url))
      }
    },
    build  : {
      sourcemap: true
    }
  });
