import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inertia from './resources/scripts/vite/inertia-layout';
import i18n from 'laravel-vue-i18n/vite';
import { fileURLToPath, URL } from "node:url";

export default defineConfig(
    {
        plugins: [
            inertia(),
            laravel({
                        input  : 'resources/js/app.js',
                        ssr    : 'resources/js/ssr.js',
                        refresh: true,
                    }),
            vue({
                    template: {
                        transformAssetUrls: {
                            base           : null,
                            includeAbsolute: false,
                        },
                    },
                }),
            i18n(),


        ],
        ssr    : {
            noExternal: ['@inertiajs/server'],
        },
      resolve: {
        alias: {
          '@fad': fileURLToPath(
            new URL('./private/fa/pro-duotone-svg-icons', import.meta.url)),
          '@fal': fileURLToPath(
            new URL('./private/fa/pro-light-svg-icons', import.meta.url)),
          '@far': fileURLToPath(
            new URL('./private/fa/pro-regular-svg-icons', import.meta.url)),
          '@fas': fileURLToPath(
            new URL('./private/fa/pro-solid-svg-icons', import.meta.url)),
        },
      },
        build: {
          sourcemap: true,
        }
    });
