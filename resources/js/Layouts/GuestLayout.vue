<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 08:47:34 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>


import {usePage} from '@inertiajs/vue3';
import {loadLanguageAsync} from 'laravel-vue-i18n';


if (usePage().props.language) {
    loadLanguageAsync(usePage().props.language);
}


</script>

<template>

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md flex flex-col gap-y-4 items-center justify-center">
            <img class="mx-auto h-16 -mb-3 w-auto" src="/art/logo.svg" alt="Aiku" />
            <span class="mx-auto text-2xl font-bold text-purple-500">aiku</span>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <slot></slot>
            </div>
        </div>
    </div>
</template>
