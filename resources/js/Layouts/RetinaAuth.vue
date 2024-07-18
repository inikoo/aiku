<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 08:47:34 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { loadLanguageAsync } from 'laravel-vue-i18n'
import { breakpointType } from '@/Composables/useWindowSize'
import { provide } from "vue"
import { useLayoutStore } from "@/Stores/retinaLayout"
import ScreenWarning from '@/Components/Utils/ScreenWarning.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExclamationTriangle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamationTriangle)


provide('layout', useLayoutStore())

if (usePage().props.language) {
    loadLanguageAsync(usePage().props.language)
}
console.log('environment:', usePage().props?.environment)

const isStaging = usePage().props?.environment === 'staging'

</script>

<template>
    <ScreenWarning v-if="isStaging" />
    <div class="relative h-screen w-screen bg-gradient-to-tr from-slate-950 to-slate-800 pt-64 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center gap-x-2">
            <img class="h-12 w-auto" src="/art/logo-yellow.svg" :alt="usePage().props.iris?.name || 'App'" />
            <span style="font-family: Fira" class="text-4xl text-white leading-none">{{ usePage().props.iris?.name }}</span>
        </div>

        <div class="grid grid-cols-7 mt-8 mx-auto md:w-full max-w-xl shadow-lg rounded-lg overflow-hidden">
            <div v-if="isStaging" class="mt-4 mb-4 col-span-7 w-full bg-red-500 text-white flex items-center justify-center gap-x-2 py-1">
                <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='' fixed-width aria-hidden='true' />
                <span class="text-sm">This is staging and may send real data, pay attention about what you do.</span>
                <FontAwesomeIcon icon='fas fa-exclamation-triangle' class='' fixed-width aria-hidden='true' />  
            </div>
            <!-- <div class="col-span-3 bg-[url('/art/backgroundWarehouse.jpg')] bg-cover bg-center">
                
            </div> -->
            <div class="col-span-7 backdrop-blur-sm relative bg-white py-8 px-4 md:px-10">
                <slot />
            </div>
        </div>
    </div>
</template>
