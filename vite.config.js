import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inertia from './resources/scripts/vite/inertia-layout';
import i18n from 'laravel-vue-i18n/vite';

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
        build: {

            rollupOptions: {
                output:{
                    manualChunks(id) {
                        if (id.includes('node_modules')) {
                            return id.toString().split('node_modules/')[1].split('/')[0].toString();
                        }
                    }
                }
            }
        }
    });
