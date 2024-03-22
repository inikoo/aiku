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
    <div class="relative h-screen w-screen bg-gradient-to-bl from-sky-100 to-indigo-200 pt-64 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center gap-x-2">
            <img class="h-12 w-auto" src="/art/logo-yellow.svg" alt="Aiku" />
            <span style="font-family: Fira" class="text-4xl text-white leading-none">{{ usePage().props.iris?.name }}</span>
        </div>

        <div class="grid grid-cols-7 mt-8 mx-auto md:w-full max-w-4xl shadow-lg rounded-lg overflow-hidden">
            <div class="col-span-3 bg-[url('/art/backgroundWarehouse.jpg')] bg-cover bg-center">
                
            </div>
            <div class="col-span-4 backdrop-blur-sm relative bg-white/65 py-8 px-4 md:px-10">
                <slot />
            </div>
        </div>
    </div>
</template>
