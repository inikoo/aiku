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
provide('layout', useLayoutStore())

if (usePage().props.language) {
    loadLanguageAsync(usePage().props.language)
}

</script>

<template>
    <div :style="{ 'background-image': `${breakpointType() !== 'xs' ? 'url(/art/backgroundWarehouse.jpg)' : false}`, 'background-repeat': 'no-repeat', 'background-size': 'cover', 'background-position': 'center' }"
        class="relative h-screen w-screen bg-gradient-to-bl from-indigo-400 to-indigo-600 pt-64 sm:px-6 lg:px-8">
        <div class="absolute bottom-5 left-10 flex items-center justify-center gap-x-2">
            <img class="h-12 w-auto" src="/art/logo-yellow.svg" alt="Aiku" />
            <span style="font-family: Fira" class="text-4xl text-white leading-none">{{ usePage().props.iris?.name }}</span>
        </div>

        <div class="mt-8 mx-auto md:w-full max-w-md">
            <div class="bg-white/65 py-8 px-4 shadow rounded-lg md:px-10">
                <slot />
            </div>
        </div>
    </div>
</template>
