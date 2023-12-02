<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 08:47:34 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>

import {useLayoutStore} from '@/Stores/layout';
import {usePage} from '@inertiajs/vue3';
import {loadLanguageAsync} from 'laravel-vue-i18n';
import {watchEffect} from 'vue';

const layout = useLayoutStore();
if (usePage().props.language) {
    loadLanguageAsync(usePage().props.language);
}
watchEffect(() => {

    if (usePage().props.group) {
        layout.group = usePage().props.group ?? null;
    }
});

</script>

<template>

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-16 -mb-3 w-auto" src="/art/logo-color-trimmed.png" alt="Aiku" />
            <h2 class="font-logo mt-6 text-center text-3xl text-purple-600">@{{layout.group.name}} 😉</h2>

        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <slot></slot>
            </div>
        </div>
    </div>
</template>
